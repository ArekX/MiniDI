<?php

use ArekX\MiniDI\Injector;

class InjectorMappingTest extends \InjectorTest\TestCase
{
   public function testOneInjectableParam()
   {
        $injector = Injector::create([
            'testObject' => ['class' => '\InjectorMappingTest\TestClassOneParam', 'dependencies' => ['dependentParam']],
            'dependentParam' => ['class' => '\InjectorMappingTest\TestClassNoParams', 'dependencies' => []]
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->dependentParam);
   }

   public function testOneInjectableRemappedParam()
   {
        $injector = Injector::create([
            'testObject' => ['class' => '\InjectorMappingTest\TestClassOneParamRemap', 'dependencies' => 'getInjectables'],
            'dependentParam' => ['class' => '\InjectorMappingTest\TestClassNoParams', 'dependencies' => []]
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentParam);
    }

   public function testOneInjectableSetterParam()
   {
        $injector = Injector::create([
            'testObject' => ['class' => '\InjectorMappingTest\TestClassOneParamSetter', 'dependencies' => 'getInjectables'],
            'dependentParam' => ['class' => '\InjectorMappingTest\TestClassNoParams', 'dependencies' => []]
        ]);

        $testObject = $injector->get('testObject');
        $this->assertEquals($testObject->notDependentParam, "I AM NOT SET");
        $this->assertInstanceOf(\InjectorMappingTest\TestClassNoParams::class, $testObject->differentParam);
    }

   public function testOneInjectableParamMappingCombination()
   {
        $injector = Injector::create([
            'testObject' =>  ['class' => '\InjectorMappingTest\TestClassParamSetterCombination', 'dependencies' => 'getInjectables'],
            'dependentParam' => ['class' => '\InjectorMappingTest\TestClassNoParams', 'dependencies' => []]
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