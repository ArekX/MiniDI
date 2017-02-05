<?php

namespace ArekX\MiniDI\Exception;

/**
 * Class InjectableNotFoundException
 * @package ArekX\MiniDI\Exception
 *
 * Exception when dependency is not found for key called using Injector::get().
 */
class InjectableNotFoundException extends InjectorException 
{
    /**
     * @var string Key which caused the exception.
     */
	protected $key;

    /**
     * InjectableNotFoundException constructor.
     * @param string $key
     */
	public function __construct($key)
	{
		$this->key = $key;
		parent::__construct("Injectable resolution for key {$key} not found.", 0, null);
	}

    /**
     * @return string Retuns key which caused the exception.
     */
	public function getKey()
    {
        return $this->key;
    }
}