<?php

namespace ArekX\MiniDI\Exception;

class CircularDependencyException extends InjectorException 
{
	protected $stackPathArray;
	protected $finalClass;

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

	public function getStackPathArray()
	{
		return $this->stackPathArray;
	}

	public function getFinalClass()
	{
		return $this->finalClass;
	}
}