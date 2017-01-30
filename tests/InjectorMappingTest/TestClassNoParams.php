<?php
namespace InjectorMappingTest;

class TestClassNoParams implements \ArekX\MiniDI\Injectable {
	public $notDependencyParam = "test";
	
	public function getInjectables() {
		return [];
	}
}