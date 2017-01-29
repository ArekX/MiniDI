<?php

use ArekX\MiniDI\Injector;

class InjectorMappingTest extends \InjectorTest\TestCase
{
   public function testOneInjectableParam()
   {
        $injector = Injector::create([
            'testObject' => '\InjectorMappingTest\TestClassOneParam',
            'dependentParam' => '\InjectorMappingTest\TestClassNoParams'
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->dependentParam);
   }

   public function testOneInjectableRemappedParam()
   {
        $injector = Injector::create([
            'testObject' => '\InjectorMappingTest\TestClassOneParamRemap',
            'dependentParam' => '\InjectorMappingTest\TestClassNoParams'
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentParam);
    }

   public function testOneInjectableSetterParam()
   {
        $injector = Injector::create([
            'testObject' => '\InjectorMappingTest\TestClassOneParamSetter',
            'dependentParam' => '\InjectorMappingTest\TestClassNoParams'
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentParam);
    }

   public function testOneInjectableParamMappingCombination()
   {
        $injector = Injector::create([
            'testObject' => '\InjectorMappingTest\TestClassParamSetterCombination',
            'dependentParam' => '\InjectorMappingTest\TestClassNoParams'
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentParam);
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->directMapParam);
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentSetterParam);
        
        $this->assertNotSame($testObject->differentParam, $testObject->directMapParam);
        $this->assertNotSame($testObject->differentParam, $testObject->differentSetterParam);
        $this->assertNotSame($testObject->directMapParam, $testObject->differentSetterParam);
    }
}