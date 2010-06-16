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
 * @package   Galahad_Mail
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * Provides additional mail functionality
 * 
 * Most notably, it allows you to use Zend_View and Zend_Layout in
 * your mail.
 * 
 * @category   Galahad
 * @package    Galahad_Mail
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Mail extends Zend_Mail
{
	/**
	 * Layout object
	 * @var Zend_Layout
	 */
	protected $_layout = null;
	
	/**
	 * View object
	 * @var Zend_View
	 */
	protected $_view = null;
	
	/**
	 * View script
	 * @var string
	 */
	protected $_viewScript = null;
	
	/**
	 * Encoding for Zend_View rendered messages
	 * @var string
	 */
	protected $_encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;

	/**
	 * Set layout object
	 * @param Zend_Layout $layout
	 * @return Galahad_Mail
	 */
	public function setLayout(Zend_Layout $layout)
	{
		$this->_layout = $layout;
		return $this;
	}
	
	/**
	 * Get current layout object
	 * @return Zend_Layout
	 */
	public function getLayout()
	{
		if (null == $this->_layout) {
			$this->_layout = new Zend_Layout();
			if (defined('APPLICATION_PATH')) {
				$this->_layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts/');
				$this->_layout->setLayout('email');
			}
		}
		
		return $this->_layout;
	}
	
	/**
	 * Sets the view
	 * @param Zend_View $view
	 * @return Galahad_Mail
	 */
	public function setView(Zend_View $view)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Get the view
	 * @return Zend_View
	 */
	public function getView()
	{
		if (null == $this->_view) {
			$this->_view = new Zend_View();
			if (defined('APPLICATION_PATH')) {
				// TODO: Is this the best default?
				$this->_view->setScriptPath(APPLICATION_PATH . '/views/scripts/emails/');
			}
		}
		
		return $this->_view;
	}
	
	/**
	 * Sets the message's view script name
	 * @param string $name
	 * @return Galahad_Mail
	 */
	public function setViewScript($name)
	{
		$this->_viewScript = $name;
		return $this;
	}
	
	/**
	 * Assign a variable to the view
	 * 
	 * @param  string|array The assignment strategy to use.
     * @param  mixed (Optional) If assigning a named variable, use this as the value.
     * @return Galahad_Mail
	 */
	public function assign($spec, $value = null)
	{
		$this->getView()->assign($spec, $value);
		return $this;
	}
	
	/**
     * Return text body Zend_Mime_Part or string
     *
     * @param  bool textOnly Whether to return just the body text content or the MIME part; defaults to false, the MIME part
     * @return false|Zend_Mime_Part|string
     */
    public function getBodyText($textOnly = false)
    {
    	$text = parent::getBodyText($textOnly);
    	if (!$text) {
    		$text = $this->_render($this->_viewScript, 'txt');
    		$text = new Zend_Mime_Part($text);
	        $text->encoding = $this->_encoding;
	        $text->type = Zend_Mime::TYPE_TEXT;
	        $text->disposition = Zend_Mime::DISPOSITION_INLINE;
	        $this->_bodyText = $text;
    	}
    	
        return $text;
    }
    
	/**
     * Return Zend_Mime_Part representing body HTML
     *
     * @param  bool $htmlOnly Whether to return the body HTML only, or the MIME part; defaults to false, the MIME part
     * @return false|Zend_Mime_Part|string
     */
    public function getBodyHtml($htmlOnly = false)
    {
    	$html = parent::getBodyHtml($htmlOnly);
    	if (!$html) {
    		$html = $this->_render($this->_viewScript);
    		$html = new Zend_Mime_Part($html);
	        $html->encoding = $this->_encoding;
	        $html->type = Zend_Mime::TYPE_HTML;
	        $html->disposition = Zend_Mime::DISPOSITION_INLINE;
	        $this->_bodyHtml = $html;
    	}
    	
    	return $html;
    }
    
    protected function _render($viewScript, $type = null)
    {
    	$rendered = null;
    	
    	if ($view = $this->getView()) {
    		$extension = ($type ? "{$type}.phtml" : 'phtml');
    		$rendered = $view->render("{$viewScript}.{$extension}");
    	}
    	
    	// TODO: Allow for text layouts
    	if (null == $type && $layout = $this->getLayout()) {
    		$layout->content = $rendered;
    		$rendered = $layout->render();
    	}
    	
    	return $rendered;
    }
}




