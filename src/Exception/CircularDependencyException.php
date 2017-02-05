<?php

namespace ArekX\MiniDI\Exception;

/**
 * Class CircularDependencyException
 * @package ArekX\MiniDI\Exception
 *
 * Circular dependency exception which happens when 2 classes depend on each other and neither of them is shared.
 */
class CircularDependencyException extends InjectorException 
{
    /**
     * @var array Stack path which happened to lead to this exception.
     */
	protected $stackPathArray;

    /**
     * @var string Last class which caused this exception.
     */
	protected $finalClass;

    /**
     * CircularDependencyException constructor.
     * @param array $stackPath
     * @param string $finalClass
     */
	public function __construct($stackPath, $finalClass) 
	{
		$this->stackPathArray = $stackPath;
		$this->finalClass = $finalClass;

		$path = [];
		foreach ($stackPath as $stackItem) {
			$path[] = implode('@', $stackItem);
		}

		return parent::__construct("Circular path: " . implode(' --> ', $path) . " --> {$finalClass}");
	}

    /**
     * @return array Returns stack path which happened to lead to this exception.
     */
	public function getStackPathArray()
	{
		return $this->stackPathArray;
	}

    /**
     * @return string Returns class name which caused this exception.
     */
	public function getFinalClass()
	{
		return $this->finalClass;
	}
}