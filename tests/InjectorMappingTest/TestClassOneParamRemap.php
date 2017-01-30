<?php
namespace InjectorMappingTest;

class TestClassOneParamRemap implements \ArekX\MiniDI\Injectable {
	public $differentParam;
	public $notDependentParam = "I AM NOT SET";

	public function getInjectables() {
		return  [
			'differentParam' => 'dependentParam'
		];
	}
}