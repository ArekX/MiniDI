<?php
namespace InjectorExceptionTest;

class TestClassInvalidMapping extends \ArekX\MiniDI\InjectableObject
{
	public function getInjectables()
	{
		return ['nonexistingParam'];
	}
}