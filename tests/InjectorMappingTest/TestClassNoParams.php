<?php
namespace InjectorMappingTest;

class TestClassNoParams extends \ArekX\MiniDI\InjectableObject {
	public $notDependencyParam = "test";
	
	public function getInjectables() {
		return [];
	}
}