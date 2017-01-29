<?php
namespace InjectorMappingTest;

class TestClassOneParamRemap extends \ArekX\MiniDI\InjectableObject {
	public $differentParam;
	public $notDependentParam = "I AM NOT SET";

	public function getInjectables() {
		return  [
			'differentParam' => 'dependentParam'
		];
	}
}