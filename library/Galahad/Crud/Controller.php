<?php
/**
 * This file is part of the Galahad Framework Extension.
 * 
 * The Galahad Framework Extension is free software: you can redistribute 
 * it and/or modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation, either version 3 of the 
 * License, or (at your option) any later version.
 * 
 * The Galahad Framework Extension is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
 * General Public License for more details.
 * 
 * @category  Galahad
 * @package   Galahad
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Provides common model functionality
 * 
 * @category   Galahad
 * @package    Galahad_Crud
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
abstract class Galahad_Crud_Controller extends Zend_Controller_Action
{
	/**
	 * Class names for models
	 * @var string
	 */
	protected $_entityClass, $_dataMapperClass;
	
	/**
	 * Primary key for models
	 * @var string|array
	 */
	protected $_primaryKey;
	
	/**
	 * Optional "order by" constrain
	 * @var string
	 */
	protected $_orderBy;
	
	/**
	 * Number of items to show per page
	 * @var int
	 */
	protected $_perPage = 20;
	
	/**
	 * Singular and plural versions of CRUD item
	 * Eg. "User" and "Users
	 * @var string
	 */
	protected $_singular, $_plural;
	
	/**
	 * Index just points to listAction
	 */
	public function indexAction()
    {
        $this->_forward('list');
    }
    
    /**
     * Lists all entities
     */
	public function listAction()
    {
    	$dm = new $this->_dataMapperClass();
    	
    	$constaint = $dm->constraint();
    	if ($this->_orderBy) {
    		$constaint->order($this->_orderBy);
    	}
    	
    	$this->view->paginator = $dm->paginator($constaint, $this->_perPage);
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page', 1));
    }
    
    /**
     * Insert a new entity
     */
	public function insertAction()
    {
    	$this->_helper->ViewRenderer->setScriptAction('edit');
    	$this->view->placeholder('title')->set("Create a New {$this->_singular}");
    	
    	$form = $this->_getForm();
    	$form->addElement('submit', 'submit', array('label' => "Add {$this->_singular}", 'class' => 'large button'))
    		 ->setAction($this->_helper->url('insert'))
    		 ->setMethod('post');
    		 
		$form->removeElement('id');

		$form = $this->_tweakInsertForm($form);
		
		$this->_save($form);
		$this->view->form = $form;
    }
    
    /**
     * Edit an entity
     */
	public function editAction()
    {
    	$primaryKey = array();
    	foreach ((array) $this->_primaryKey as $key) {
    		if (!$primaryKey[$key] = $this->getRequest()->getParam($key, false)) {
				$this->_helper->redirector('insert');
			}
    	}
    	if (1 == count($primaryKey)) {
    		$primaryKey = $primaryKey[$key];
    	}
		
		$dm = new $this->_dataMapperClass();
		if (!$entity = $dm->fetchByPrimary($primaryKey)) {
			$this->_helper->flashMessenger("No Such {$this->_singular} Exists."); // TODO: Translate
			$this->_helper->redirector('insert');
		}
		
		$this->view->placeholder('title')->set("Update {$this->_singular}");
    	
    	$form = $this->_getForm();
    	$form->addElement('submit', 'submit', array('label' => 'Save Changes', 'class' => 'large button'))
    		 ->setAction($this->_helper->url('edit'))
    		 ->setMethod('post');
    		 
    	$form = $this->_tweakEditForm($form);
		
    	$defaults = $entity->toArray();   
    	$defaults = $this->_processDefaults($defaults);
    		 
    	$form->setDefaults($defaults);
    	$this->_save($form, $entity);
    	$this->view->form = $form;
    }
    
    /**
     * Save data
     * @param Zend_Form $form
     * @param Default_Model_Entity $entity
     */
	protected function _save(Zend_Form $form, Galahad_Model_Entity $entity = null)
    {
    	$request = $this->getRequest();
    	
    	if (!$request->isPost()) {
    		return false;
    	}
    	
    	if ($form->isValid($request->getParams())) {
			$data = $form->getValues();
			
    		if (null == $entity) {
				$entity = new $this->_entityClass();
			}
			
			// Manipulate Data
			unset($data['submit']);
			$data = $this->_processData($data);
			
			$entity->reset($data);
			
			if ($result = $entity->save()) { // TODO: Move into Service?
				$this->_helper->flashMessenger("{$this->_singular} Saved!");
			} else {
				$this->_helper->flashMessenger('There were no changes to save!');
			}
			
			require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';
			$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
			
	    	$routeOptions = array();
	    	foreach ((array) $this->_primaryKey as $key) {
	    		$getter = 'get' . $filter->filter($key);
	    		$routeOptions[$key] = $entity->$getter();
	    	}
			$this->_helper->redirector->gotoSimple('edit', null, null, $routeOptions);
		} else {
			$this->view->errors = true;
			return false;
		}
    }
    
    /**
     * Override this if you need to process the form defaults before displaying
     * @param array $defaults
     */
    protected function _processDefaults($defaults)
    {
    	return $defaults;
    }
    
    /**
     * Override this if you need to process submitted data before saving
     * @param array $data
     */
    protected function _processData($data)
    {
    	return $data;
    }
    
    /**
     * Gets a form object associated with model
     */
	protected function _getForm()
    {
    	$entity = new $this->_entityClass;
    	return $entity->getForm();
    }
	
	/**
	 * Constructor 
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Zend_Controller_Response_Abstract $response
	 * @param array $invokeArgs
	 */
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		$this->_checkSetup();
		parent::__construct($request, $response, $invokeArgs);
	}
	
	/**
	 * Ensures that controller is properly set up
	 */
	private function _checkSetup()
	{
		$this->_checkMember('_entityClass');
		$this->_checkMember('_dataMapperClass');
		$this->_checkMember('_primaryKey');
		$this->_checkMember('_singular');
		$this->_checkMember('_plural');
	}
	
	/**
	 * Ensures that a class member is set
	 * @param string $name
	 */
	private function _checkMember($name)
	{
		if (null == $this->$name) {
			throw new Exception(get_class($this) . '::$' . $name . ' is not defined!');
		}
	}
}