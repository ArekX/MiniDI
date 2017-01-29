<?php
namespace InjectorMappingTest;

class TestClassNoParams extends \ArekX\MiniDI\InjectableObject {
	public $notDependencyParam = "test";
	protected $injectables = [];
}