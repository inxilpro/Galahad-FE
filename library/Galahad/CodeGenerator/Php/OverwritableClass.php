<?php 

class Galahad_CodeGenerator_Php_OverwritableClass extends Zend_CodeGenerator_Php_Class
{
	protected $_class;
	
	public function unsetMethod($methodName)
    {
        foreach ($this->_class->_methods as $method) {
            if ($method->getName() == $methodName) {
                unset($method);
                return true;
            }
        }
        return false;
    }
    
    /**/
    public function getChildClass()
    {
    	return $this->_class;
    }
    /**/
	
	public function __construct(Zend_CodeGenerator_Php_Class $class, $options = array())
	{
		$this->_class = $class;
		parent::__construct($options);
	}
	
	public function __set($name, $value)
	{
		$this->_class->$name = $value;
	}
	
	public function __get($name)
	{
		return $this->_class->$name;
	}
	
	public function __isset($name)
	{
		return isset($this->_class->$name);
	}
	
	public function __unset($name)
	{
		unset($this->_class->$name);
	}
}