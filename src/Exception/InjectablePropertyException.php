<?php

namespace ArekX\MiniDI\Exception;

/**
 * Class InjectablePropertyException
 * @package ArekX\MiniDI\Exception
 *
 * Exception thrown when there is no property to set in an instance but that property is defined in configuration.
 */
class InjectablePropertyException extends InjectorException 
{
    /**
     * @var string Property name which caused the error.
     */
	protected $propertyName;

    /**
     * @var string Class name in which property was not found.
     */
	protected $className;

	public function __construct($propertyName, $className)
	{
		$this->propertyName = $propertyName;

		parent::__construct("No property or setter found for {$propertyName} in class {$className}.", 0, null);
	}

    /**
     * @return string Returns property name which caused the error.
     */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

    /**
     * @return string Returns class name in which property was not found.
     */
	public function getClassName()
    {
        return $this->className;
    }
}