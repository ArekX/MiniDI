<?php

use ArekX\MiniDI\Injector;

class InjectorTest extends \InjectorTest\TestCase
{
    public function testInjectorOne()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClassNoParams'
        ]);

        $this->assertTrue($injector->get('testObject') instanceof \InjectorTest\TestClassNoParams);
    }

    public function testParamInject()
    {
        $injector = Injector::create([
        	'testObject' => '\InjectorTest\TestClass1Param',
        	'injectParam' => '\InjectorTest\TestClassNoParams'
    	]);

    	$this->assertTrue($injector->get('testObject')->injectParam instanceof \InjectorTest\TestClassNoParams);
    }

    public function testNestedInject()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => '\InjectorTest\TestClass1Param',
            'injectParam' => '\InjectorTest\TestClassNoParams'
        ]);

        $this->assertTrue($injector->get('testObject')->nestedParam->injectParam instanceof \InjectorTest\TestClassNoParams);
    }

    public function testDifferentInstances()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => '\InjectorTest\TestClass1Param',
            'injectParam' => '\InjectorTest\TestClassNoParams'
        ]);

        $testObject = $injector->get('testObject');

        $this->assertTrue($testObject->nestedParam->injectParam !== $testObject->injectParam);
    }

    public function testSharedInstance()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => '\InjectorTest\TestClass1Param',
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams', 'shared' => true]
        ]);

        $testObject = $injector->get('testObject');

        $this->assertTrue($testObject->nestedParam->injectParam === $testObject->injectParam);
    }

    public function testDifferentInjector()
    {
        $anotherInjector = Injector::create([
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams']
        ]);

        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => ['class' => '\InjectorTest\TestClass1Param', 'injector' => $anotherInjector],
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams', 'shared' => true]
        ]);

        $testObject = $injector->get('testObject');

        $this->assertFalse($testObject->nestedParam->injectParam === $testObject->injectParam);
    }

    public function testDifferentInjectorViaConfig()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => ['class' => '\InjectorTest\TestClass1Param', 'injector' => [
                'class' => Injector::class,
                'config' => [
                    'injectParam' => ['class' => '\InjectorTest\TestClassNoParams']
                ]
            ]],
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams', 'shared' => true]
        ]);

        $testObject = $injector->get('testObject');

        $this->assertFalse($testObject->nestedParam->injectParam === $testObject->injectParam);
    }

    public function testDifferentIsolatedInjectorViaObject()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectableNotFoundException::class);

        $anotherInjector = Injector::create();

        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => ['class' => '\InjectorTest\TestClass1Param', 'injector' => [
                'object' => $anotherInjector,
                'isolate' => true
            ]],
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams', 'shared' => true]
        ]);

        $testObject = $injector->get('testObject');

        $this->assertFalse($testObject->nestedParam->injectParam === $testObject->injectParam);
    }

    public function testDifferentIsolatedInjectorViaConfig()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectableNotFoundException::class);

        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => ['class' => '\InjectorTest\TestClass1Param', 'injector' => [
                'class' => Injector::class,
                'config' => [],
                'isolate' => true
            ]],
            'injectParam' => ['class' => '\InjectorTest\TestClassNoParams', 'shared' => true]
        ]);

        $testObject = $injector->get('testObject');

        $this->assertFalse($testObject->nestedParam->injectParam === $testObject->injectParam);
    }
}