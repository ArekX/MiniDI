<?php
namespace InjectorMappingTest;

class TestClassParamSetterCombination extends \ArekX\MiniDI\InjectableObject {
	public $differentParam;
	public $directMapParam;
	public $differentSetterParam;
	public $notDependentParam = "I AM NOT SET";

	protected $injectables = [
		'differentParam' => 'dependentParam',
		'directMapParam' => 'dependentParam',
		'setterParam' => 'dependentParam'
	];

	public function setSetterParam($value)
	{
		$this->differentSetterParam = $value;
	}
}