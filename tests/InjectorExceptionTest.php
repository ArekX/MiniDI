<?php

use ArekX\MiniDI\Injector;

class InjectorExceptionTest extends \InjectorTest\TestCase
{
    public function testCircularDependencyException()
    {
        $this->expectException(\ArekX\MiniDI\Exception\CircularDependencyException::class);

        Injector::create([
            'testObject' => '\InjectorExceptionTest\TestClassCircularException',
            'circularParam' => '\InjectorExceptionTest\TestClassCircularException'
        ])->get('testObject');
    }

    public function testNotFoundInjectable()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectableNotFoundException::class);

        Injector::create([])->get('nonExistingObject');
    }

    public function testInvalidInjectableProperty()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectablePropertyException::class);

        Injector::create(['testObject' => '\InjectorExceptionTest\TestClassInvalidMapping'])->get('testObject');
    }

    public function testInvalidConfiguationException()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InvalidConfigurationException::class);

        Injector::create([
        	'testObject' => ['class' => '\InjectorExceptionTest\TestClassNoParams', 'config' => ['invalidParam' => 10]]
    	])->get('testObject');
    }
}