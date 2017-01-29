<?php
namespace InjectorMappingTest;

class TestClassOneParamSetter extends \ArekX\MiniDI\InjectableObject {
	public $differentParam;
	public $notDependentParam = "I AM NOT SET";

	protected $injectables = ['dependentParam'];

	public function setDependentParam($value)
	{
		$this->differentParam = $value;
	}
}