<?php

use ArekX\MiniDI\Injector;

class InjectorTest extends \InjectorTest\TestCase
{
    public function testInjectorOne()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClassNoParams'
        ]);

        $this->assertInstanceOf(\InjectorTest\TestClassNoParams::class, $injector->get('testObject'));
    }

    public function testParamInject()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param',
            'injectParam' => '\InjectorTest\TestClassNoParams'
        ]);

        $this->assertInstanceOf(\InjectorTest\TestClassNoParams::class, $injector->get('testObject')->injectParam);
    }

    public function testNestedInject()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass2Nested',
            'nestedParam' => '\InjectorTest\TestClass1Param',
            'injectParam' => '\InjectorTest\TestClassNoParams'
        ]);

        $this->assertInstanceOf(\InjectorTest\TestClassNoParams::class, $injector->get('testObject')->nestedParam->injectParam);
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
                'instance' => $anotherInjector,
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

        $injector->get('testObject');
    }

    public function testAfterInitCall()
    {
        $injector = Injector::create([
            'testClass1' => [
                'class' => '\InjectorTest\TestClass1Param',
                'dependencies' => [],
                'shared' => true
            ],
            'testObjectNoAfterInit' => [
                'class' => '\InjectorTest\TestClassAfterInit',
                'dependencies' => ['testClass1']
            ],
            'testObject' => [
                'class' => '\InjectorTest\TestClassAfterInit',
                'runAfterInit' => 'init',
                'dependencies' => ['testClass1']
            ]
        ]);

        /** @var \InjectorTest\TestClass1Param $testClass1 */
        $testClass1 = $injector->get('testClass1');
        $testClass1->injectParam = "I AM SET AFTER INIT.";

        /** @var \InjectorTest\TestClassAfterInit $noAfterInit */
        $noAfterInit = $injector->get('testObjectNoAfterInit');

        /** @var \InjectorTest\TestClassAfterInit $afterInit */
        $afterInit = $injector->get('testObject');

        $this->assertEquals($afterInit->initParam, $testClass1->injectParam);
        $this->assertSame($afterInit->testClass1, $testClass1);
        $this->assertSame($noAfterInit->testClass1, $testClass1);
        $this->assertNotEquals($afterInit->initParam, $noAfterInit->initParam);
        $this->assertNull($noAfterInit->initParam);
    }


    public function testValueConfig()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param',
            'injectParam' => ['value' => "I AM TEST VALUE"]
        ]);

        $this->assertEquals($injector->get('testObject')->injectParam, "I AM TEST VALUE");
    }

    public function testAssignValue()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param'
        ]);

        $injector->assignValue('injectParam', "I AM TEST VALUE");

        $this->assertEquals($injector->get('testObject')->injectParam, "I AM TEST VALUE");
    }

    public function testAssignClassSimple()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param'
        ]);

        $injector->assignClass('injectParam', \InjectorTest\TestClassNoParams::class);

        $this->assertInstanceOf(\InjectorTest\TestClassNoParams::class, $injector->get('testObject')->injectParam);
    }

    public function testAssignClassDependencyValue()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param'
        ]);

        $injector->assignClass('injectParam', \InjectorTest\TestClass1Param::class, ['injectParam' => 'testValue']);
        $injector->assignValue('testValue', "I AM TEST VALUE");

        $object = $injector->get('testObject');
        $this->assertInstanceOf(\InjectorTest\TestClass1Param::class, $object->injectParam);
        $this->assertEquals($object->injectParam->injectParam, "I AM TEST VALUE");
    }

    public function testAssignClassConfig()
    {
        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param'
        ]);

        $injector->assignClass('injectParam', \InjectorTest\TestClassConfig::class, [], [
            'param' => 'test'
        ]);

        $object = $injector->get('testObject');
        $this->assertInstanceOf(\InjectorTest\TestClassConfig::class, $object->injectParam);
        $this->assertEquals($object->injectParam->getParam(), "test");
    }

    public function testAssignClassDependencyConfigAndInjector()
    {
        $this->expectException(\ArekX\MiniDI\Exception\InjectableNotFoundException::class);

        $anotherInjector = Injector::create();

        $injector = Injector::create([
            'testObject' => '\InjectorTest\TestClass1Param'
        ]);

        $injector->assignClass('injectParam', \InjectorTest\TestClassConfig::class, ['param' => 'valueTest'], [
            'param2' => 'test',
        ], $anotherInjector);

        $injector->assignValue('valueTest', "NOPE");

        $injector->get('testObject');
    }

    public function testSetParent()
    {
        $anotherInjector = Injector::create();

        $injector = Injector::create()->setParent($anotherInjector);

        $this->assertEquals($injector->getParent(), $anotherInjector);
    }

    public function testSetParentSelf()
    {
        $injector = Injector::create();

        $injector->setParent($injector);

        $this->assertEquals($injector->getParent(), null);
    }

    public function testInjectorHasMethodWhenExists()
    {
        $injector = Injector::create(['test' => ['value' => "Test Value"]]);

        $this->assertTrue($injector->has('test'));
    }

    public function testInjectorHasMethodWhenNotExists()
    {
        $injector = Injector::create();
        $this->assertFalse($injector->has('test'));
    }

    public function testInjectorHasMethodWhenParentExists()
    {
        $parentInjector = Injector::create(['test' => ['value' => "test"]]);
        $injector = Injector::create()->setParent($parentInjector);
        $this->assertTrue($injector->has('test'));
    }

    public function testInjectorHasMethodWithParentWhenNotExists()
    {
        $parentInjector = Injector::create(['test1' => ['value' => "test"]]);
        $injector = Injector::create()->setParent($parentInjector);
        $this->assertFalse($injector->has('test'));
    }
}