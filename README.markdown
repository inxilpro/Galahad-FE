Galahad Framework Extension
===========================

The Galahad Framework Extension is a library of code that extends the Zend Framework.  Some of the projects in here will eventually be proposed to the Zend Framework (and migrated there if accepted), some will not (the Modeling architecture, for example, because Zend has stated many times that there won't be a Zend_Model class).

Main Components
---------------

Galahad FE has the following packages:

 - Galahad_Acl
 - Galahad_CodeGenerator
 - Galahad_Controller
 - Galahad_Crud
 - Galahad_Form
 - Galahad_Model
 - Galahad_Paginator
 - Galahad_Payment
 - Galahad_Tool
 - Galahad_View

### Galahad_Acl

If you choose to use Galahad_Acl's resource naming scheme it will automatically generate resource chains for you.  For example, if you do the following:

    <?php
    $acl = new Galahad_Acl();
    $acl->addResource('mvc:default.index.index');
    ?>

Galahad_Acl will generate the following ACL chain for you:

 - mvc:
 - mvc:default (parent = mvc:)
 - mvc:default.index (parent = mvc:default)
 - mvc:default.index.index (parent = mvc:default.index)
   
Then you can easily set ACL restrictions that will run down this chain.  For example:

    <?php
    $acl->deny('guest', 'mvc:default.account');
    ?>

Will deny access to the role "guest" to "mvc:default.account.update"

This is particularly useful when used with either Galahad_Model_Entity or Galahad_Controller_Plugin_Acl, both of which are Galahad_Acl-aware.  Galahad_Model_Entity automatically registers itself as a resource in your ACL with the name model:module.modelName, so for example Admin_Model_User would have a resource ID of "model:admin.user".  You can then set permissions on models (or add custom resources for actions performed on models) and handle most of your ACL within your domain logic.  Here's a simple example:

    <?php
    class Default_Model_Post 
      extends Galahad_Model_Entity
    {
    	protected function _initAcl($acl)
    	{
    		// Deny permissions to anything on this model unless explicity allowed
    		$acl->deny(null, 'model:default.post');
    		
    		// Allow guests to fetch the content of posts
    		$acl->allow('guest', 'model:default.post.fetch')
		
    		// Allow admins to save changes to posts
    		$acl->allow('admin', 'model:default.post.save')
    	}

    	public function save()
    	{
    		if (!$this->getAcl()->isAllowed($this->getRole(), 'model:default.post.save')) {
    			throw new Exception('Current user is not allowed to save posts');
    		}

    		$dataMapper = $this->getDataMapper();
    		return $dataMapper->save($this);
    	}
    }
    ?>

If you have relatively simple permissions (and an application that is MVC-only [doesn't offer something like a REST API or a command line interface]) it may be easier to handle permissions at the MVC level.  That's what Galahad_Controller_Plugin_Acl is for.  Before any request is dispatched, this plugin checks the ACL for a resource named "mvc:module.controller.action" (and creates that resource if it does not exist).  This means that you can restrict permissions to entire modules or controllers with something like:

    <?php
    $acl->deny('guest', 'mvc:admin');
    ?>

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

### Galahad_Payment

Gateway-independent payment processing.  Example code:

    <?php
    $options = array(
    	'loginId' => 'Authorize.net Login ID',
    	'transactionKey' => 'Authorize.net Transaction Key',
    	'mode' => 'test'
    );
    $gateway = Galahad_Payment::factory('AuthorizeNet', $options);
    
    $customer = new Galahad_Payment_Customer(array(
    	'firstName' => 'John',
    	'lastName' => 'Smith',
    	'postalCode' => '19106',
    ));
    
    $card = new Galahad_Payment_Method_CreditCard('4111111111111111', 11, 2012);
    
    $transaction = new Galahad_Payment_Transaction();
    $transaction->setBillingCustomer($customer);
    $transaction->setPaymentMethod($card);
    $transaction->setAmount(3.50);
    $transaction->setComments("I need about three-fiddy.");
    
    $response = $gateway->process($transaction);
    if ($response->isApproved()) {
    	echo "Your transaction was approved!";
    }
    ?>

### Galahad_Tool

Provides extensions to Zend_Tool to generate Galahad resources (most notably Models).

### Galahad_View

Contains some simple view helpers for common functionality.

License
-------

The Galahad Framework Extension is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

The Galahad Framework Extension is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

[1]: http://cmorrell.com/web-development/more-php-modelling-383

