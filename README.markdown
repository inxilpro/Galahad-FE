Galahad Framework Extension
===========================

The Galahad Framework Extension is a library of code that extends the Zend Framework.  Some of the projects in here will eventually be proposed to the Zend Framework (and migrated there if accepted), some will not (the Modeling architecture, for example, because Zend has stated many times that there won't be a Zend_Model class).

Main Components
---------------

Galahad FE has the following packages:

 - Galahad_CodeGenerator
 - Galahad_Controller
 - Galahad_Crud
 - Galahad_Form
 - Galahad_Model
 - Galahad_Paginator
 - Galahad_Tool
 - Galahad_View

### Galahad_CodeGenerator

Galahad_CodeGenerator exists solely to facilitate Galahad_Tool.  Right now Zend_CodeGenerator does not support overwriting functions within classes, which is necessary for modifying existing forms.  Galahad_CodeGenerator_Php_OverwritableClass implements the Decorator pattern allowing Galahad_Tool to overwrite existing generated classes.

### Galahad_Controller

Provides some simple action helpers/plugins.  Right now these are very much in flux—I'm not sure whether they will stay or go.

### Galahad_Crud

Provides simple CRUD functionality.  In very early stages, but allows easy creation of CRUD controllers.  I plan on building generic views as well, and maybe adding CRUD generation to Galahad_Tool.

### Galahad_Form

Provides additional form elements.  Right now the only on is ImageFile.

### Galahad_Model

Provides basic functionality to facilitate modeling.  This includes entities & collections, data mappers, data access objects, and more.  Right now this is the meat of Galahad FE.  [More Info][1]

### Galahad_Paginator

Adds a new paginator for Galahad_Models.

### Galahad_Tool

Provides extensions to Zend_Tool to generate Galahad resources (most notably Models).

### Galahad_View

Contains some simple view helpers for common functionality.

License
-------

The Galahad Framework Extension is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

The Galahad Framework Extension is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

[1]: http://cmorrell.com/web-development/more-php-modelling-383

