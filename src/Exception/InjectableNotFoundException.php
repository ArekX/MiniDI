<?php

namespace ArekX\MiniDI\Exception;

class InjectableNotFoundException extends InjectorException 
{
	protected $key;

	public function __construct($key)
	{
		$this->key = $key;

		parent::__construct("Injectable resolution for key {$key} not found.");
	}
}