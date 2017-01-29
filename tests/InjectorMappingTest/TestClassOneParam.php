<?php
namespace InjectorMappingTest;

class TestClassOneParam extends \ArekX\MiniDI\InjectableObject {
	public $dependentParam;
	public $notDependentParam = "I AM NOT SET";

	protected $injectables = ['dependentParam'];
}