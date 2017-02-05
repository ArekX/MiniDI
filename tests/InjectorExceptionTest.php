<?php

use ArekX\MiniDI\Injector;

class InjectorExceptionTest extends \InjectorTest\TestCase
{
    public function testCircularDependencyException()
    {
        $this->expectException(\ArekX\MiniDI\Exception\CircularDependencyException::class);

        Injector::create([
            'testObject' => '\InjectorExceptionTest\TestClassCircularException',
            'circularParam' => '\InjectorExceptionTest\TestClass1Param',
            'param' => '\InjectorExceptionTest\TestClassCircularException'
        ])->get('testObject');
    }

    public function testSelfCircularDependencyException()
    {
        $this->expectException(\ArekX\MiniDI\Exception\CircularDependencyException::class);

        Injector::create([
            'testObject' => '\InjectorExceptionTest\TestClassCircularException',
            'circularParam' => '\InjectorExceptionTest\TestClassCircularException'
        ])->get('testObject');
    }

    public function testNoCircularDependencyExceptionWhenShared()
    {
        try {
            Injector::create([
                'testObject' => '\InjectorExceptionTest\TestClassCircularException',
                'circularParam' => ['class' => '\InjectorExceptionTest\TestClassCircularException', 'shared' => true]
            ])->get('testObject');
        } catch (\Exception $e) {
            throw $e;
        }

        $this->assertTrue(true);
    }

    public function testNotFoundInjectable()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectableNotFoundException::class);

        Injector::create([])->get('nonExistingObject');
    }

    public function testInvalidInjectableProperty()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectablePropertyException::class);

        Injector::create([
            'testObject' => ['class' => '\InjectorExceptionTest\TestClassInvalidMapping', 'dependencies' => ['nonexistingParam']]
        ])->get('testObject');
    }

    public function testInvalidConfiguationException()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InvalidConfigurationException::class);

        Injector::create([
        	'testObject' => ['config' => ['invalidParam' => 10]]
    	])->get('testObject');
    }
}