<?php

namespace ArekX\MiniDI\Exception;

class InvalidConfigurationException extends InjectorException 
{
	protected $config;

	public function __construct($config, $message)
	{
		$this->config = $config;
		parent::__construct($message);
	}

	public function getConfig()
	{
		return $this->config;
	}
}