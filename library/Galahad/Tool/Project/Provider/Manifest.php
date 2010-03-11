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
 * @package   Galahad_Tool
 * @copyright Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license   GPL <http://www.gnu.org/licenses/>
 * @version   0.3
 */

/**
 * @see Galahad_Tool_Project_Provider_GalahadModel
 */
require_once 'Galahad/Tool/Project/Provider/GalahadModel.php';

/**
 * @see Galahad_Tool_Project_Provider_GalahadModelProperty
 */
require_once 'Galahad/Tool/Project/Provider/GalahadModelProperty.php';

/**
 * @see Galahad_Tool_Project_Provider_GalahadDbTable
 */
require_once 'Galahad/Tool/Project/Provider/GalahadDbTable.php';

/**
 * @see Galahad_Tool_Project_Provider_Form
 */
require_once 'Galahad/Tool/Project/Provider/GalahadFormElement.php';

/**
 * @see Galahad_Tool_Project_Provider_GalahadDataMapper
 */
require_once 'Galahad/Tool/Project/Provider/GalahadDataMapper.php';

/**
 * @see Galahad_Tool_Project_Provider_GalahadDataMapper
 */
require_once 'Galahad/Tool/Project/Provider/GalahadCollection.php';

/**
 * @see Zend_Tool_Framework_Manifest_ProviderManifestable
 */
require_once 'Zend/Tool/Framework/Manifest/ProviderManifestable.php';

/**
 * Manifest of Project Providers
 * 
 * @category   Galahad
 * @package    Galahad_Tool
 * @copyright  Copyright (c) 2010 Chris Morrell <http://cmorrell.com>
 * @license    GPL <http://www.gnu.org/licenses/>
 */
class Galahad_Tool_Project_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable
{
    /**
     * getProviders()
     *
     * @return array Array of Providers
     */
    public function getProviders()
    {
        return array(
            new Galahad_Tool_Project_Provider_GalahadModel(),
            new Galahad_Tool_Project_Provider_GalahadModelProperty(),
            new Galahad_Tool_Project_Provider_GalahadDbTable(),
            new Galahad_Tool_Project_Provider_GalahadFormElement(),
            new Galahad_Tool_Project_Provider_GalahadDataMapper(),
            new Galahad_Tool_Project_Provider_GalahadCollection(),
        );
    }
}