<?php
namespace InjectorExceptionTest;

class TestClassInvalidMapping extends \ArekX\MiniDI\InjectableObject
{
	protected $injectables = ['nonexistingParam'];
}