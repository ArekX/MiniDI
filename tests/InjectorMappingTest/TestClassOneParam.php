<?php
namespace InjectorMappingTest;

class TestClassOneParam implements \ArekX\MiniDI\Injectable {
	public $dependentParam;
	public $notDependentParam = "I AM NOT SET";

	public function getInjectables()
	{
		return ['dependentParam'];
	}
}