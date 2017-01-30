<?php
namespace InjectorExceptionTest;

class TestClassInvalidMapping implements \ArekX\MiniDI\Injectable
{
	public function getInjectables()
	{
		return ['nonexistingParam'];
	}
}