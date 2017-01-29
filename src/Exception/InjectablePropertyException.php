<?php

namespace ArekX\MiniDI\Exception;

class InjectablePropertyException extends InjectorException 
{
	protected $propertyName;

	public function __construct($propertyName) 
	{
		$this->propertyName = $propertyName;
		parent::__construct("No property or setter found for {$propertyName}.");
	}

	public function getPropertyName()
	{
		return $this->propertyName;
	}
}