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
 * @copyright Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Zend_View_Helper_Abstract
 */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Generates a URL based on action/controller/module/params
 * 
 * @category   Galahad
 * @package    Galahad_View
 * @copyright  Copyright (c) 2009 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_View_Helper_SimpleUrl extends Zend_View_Helper_Abstract
{
    /**
     * Generates URL
     * @see Zend_Controller_Action_Helper_Redirector::setGotoSimple()
     * @return string
     */
    public function simpleUrl($action, $controller = null, $module = null, array $params = array())
    {
    	$front = Zend_Controller_Front::getInstance();
    	$dispatcher = $front->getDispatcher();
        $request    = $front->getRequest();
        $curModule  = $request->getModuleName();
        $useDefaultController = false;

        if (null === $controller && null !== $module) {
            $useDefaultController = true;
        }

        if (null === $module) {
            $module = $curModule;
        }

        if ($module == $dispatcher->getDefaultModule()) {
            $module = '';
        }

        if (null === $controller && !$useDefaultController) {
            $controller = $request->getControllerName();
            if (empty($controller)) {
                $controller = $dispatcher->getDefaultControllerName();
            }
        }

        $params['module']     = $module;
        $params['controller'] = $controller;
        $params['action']     = $action;

        $router = $front->getRouter();
        $url    = $router->assemble($params, 'default', true);
        
        return $url;
    }
}


