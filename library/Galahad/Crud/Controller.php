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
	const INSERT = 'insert';
	const UPDATE = 'update';
	
	/**
	 * Class names for models
	 * @var string
	 */
	protected $_entityClass, $_dataMapperClass;
	
	/**
	 * Shared mapper
	 * 
	 * @var $_dataMapper Galahad_Model_DataMapper
	 */
	protected $_dataMapper;
	
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
	 * Form to be used to manipulate entity
	 * @var Zend_Form
	 */
	private $_form = null;
    
    /**
     * Lists all entities
     */
	public function indexAction()
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

    	$primaryKey = $this->_primaryKey;
    	if (!is_array($primaryKey)) {
    		$primaryKey = (array) $primaryKey;
    	}
    	foreach ($primaryKey as $key) {
			$form->removeElement($key);
    	}
		
		$this->_save($form, self::INSERT);
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
		if (!$entity = $dm->fetchById($primaryKey)) {
			$this->_helper->flashMessenger("No Such {$this->_singular} Exists."); // TODO: Translate
			$this->_helper->redirector('insert');
			return;
		}
		
		$this->view->placeholder('title')->set("Update {$this->_singular}");
    	
    	$form = $this->_getForm();
    	$form->addElement('submit', 'submit', array('label' => 'Save Changes', 'class' => 'large button'))
    		 ->setAction($this->_helper->url('edit'))
    		 ->setMethod('post');
		
    	$defaults = $entity->toArray();   
    	$defaults = $this->_processDefaults($defaults, $form, $entity);
    		 
    	$form->setDefaults($defaults);
    	$this->_save($form, self::UPDATE, $entity);
    	
    	$this->view->form = $form;
    	$this->view->entity = $entity;
    }
    
	public function deleteAction()
    {
    	$entity = $this->_getEntity();
    	if (!$entity) {
    		return;
    	}
		
    	$title = "Update {$this->_singular}";
    	$this->view->headTitle($title);
		$this->view->placeholder('title')->set($title);
    	
    	$session = new Zend_Session_Namespace(__CLASS__);
    	if (!isset($session->nonce)) {
    		$session->nonce = rand(0, PHP_INT_MAX);
    	}
    	
    	$nonce = $this->_request->getPost('nonce');
    	$confirmation = $this->_request->getPost('confirm');
    	if ('yes' == $confirmation && $nonce == $session->nonce) {
    		unset($session->nonce);
    		$dm = $this->_getDataMapper();
    		$dm->delete($entity);
    		$this->_helper->flashMessenger("{$this->_singular} Deleted.");
    		$this->_helper->redirector('index');
			return;
    	}
    	
    	$form = new Zend_Form();
    	$form->addElement('hidden', 'nonce', array(
    		'value' => $session->nonce,
		));
		
		$form->addElement('checkbox', 'confirm', array(
			'checkedValue' => 'yes',
			'label' => 'Check to confirm: ',
		));
		
		$form->addElement('submit', 'submit', array(
			'required' => false,
			'ignore' => true,
			'label' => 'Delete', // TODO: translate
		));
		
		$this->view->form = $form;
		$this->view->entity = $entity;
    }
    
    /**
     * Gets an entity or redirects to _insert
     * 
     * @return Galahad_Model_Entity
     */
    protected function _getEntity()
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
		
		$dm = $this->_getDataMapper();
		if (!$entity = $dm->fetchById($primaryKey)) {
			$this->_helper->flashMessenger("No Such {$this->_singular} Exists."); // TODO: Translate
			$this->_helper->redirector('insert');
			return;
		}
		
		return $entity;
    }
    
    /**
     * Save data
     * 
     * @param Zend_Form $form
     * @param Galahad_Model_Entity $entity
     */
	protected function _save(Zend_Form $form, $action, Galahad_Model_Entity $entity = null)
    {
    	if ($action != self::INSERT && $action != self::UPDATE) {
    		throw new Galahad_Exception('You must either save or update an entry.');
    	}
    	
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
			$data = $this->_processData($data, $form);
			if (!$data) {
				return false;
			}
			
			$entity->reset($data);
			
	    	// Save
	    	if (self::INSERT == $action) {
	    		if ($entity->getDataMapper()->insert($entity)) {
	    			$this->_helper->flashMessenger("{$this->_singular} Added!");
	    		} else {
	    			throw new Galahad_Exception('There was an error adding the ' . $this->_singular);
	    		}
	    	} else if (self::UPDATE == $action) {
	    		if ($entity->getDataMapper()->update($entity)) {
	    			$this->_helper->flashMessenger("{$this->_singular} Updated!");
	    		}
	    	}
	    	
    		// Build ID
    		require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';
			$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
			
	    	$id = array();
	    	foreach ((array) $this->_primaryKey as $key) {
	    		$getter = 'get' . $filter->filter($key);
	    		$id[$key] = $entity->$getter();
	    	}
	    	
			$this->_helper->redirector->gotoSimple('edit', null, null, $id);
		} else {
			$this->view->errors = true;
			return false;
		}
    }
    
    /**
     * Override this if you need to process the form defaults before displaying
     * @param array $defaults
     */
    protected function _processDefaults($defaults, Zend_Form $form, Galahad_Model_Entity $entity)
    {
    	return $defaults;
    }
    
    /**
     * Override this if you need to process submitted data before saving
     * 
     * @param array $data
     * @return array|false
     */
    protected function _processData(array $data, Zend_Form $form)
    {
    	return $data;
    }
    
    /**
     * Gets a form object associated with model
     */
	protected function _getForm()
    {
    	if (null == $this->_form) {
    		$entity = new $this->_entityClass;
    		$this->_form = $entity->getForm();
    	}
    	
    	return $this->_form;
    }
    
    public function setForm(Zend_Form $form)
    {
    	$this->_form = $form;
    	return $this;
    }
    
    protected function _getDataMapper()
    {
    	if (null == $this->_dataMapper) {
    		$this->_dataMapper = new $this->_dataMapperClass();
    	}
    	
    	return $this->_dataMapper;
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