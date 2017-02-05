<?php

namespace ArekX\MiniDI\Exception;

/**
 * Class InvalidConfigurationException
 * @package ArekX\MiniDI\Exception
 *
 * Exception which is thrown when there is invalid configuration in injector.
 */
class InvalidConfigurationException extends InjectorException 
{
    /**
     * @var array Configuration which caused the error.
     */
	protected $config;

    /**
     * InvalidConfigurationException constructor.
     * @param string $config
     * @param int $message
     */

	public function __construct($config, $message)
    {
        $this->config = $config;
        parent::__construct($message, 0, null);
    }

    /**
     * Returns config which caused the error.
     *
     * @return array
     */
	public function getConfig()
	{
		return $this->config;
	}
}