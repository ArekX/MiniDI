<?php
namespace InjectorMappingTest;

class TestClassParamSetterCombination
{
    public $differentParam;
    public $directMapParam;
    public $differentSetterParam;
    public $notDependentParam = "I AM NOT SET";

    public function setSetterParam($value)
    {
        $this->differentSetterParam = $value;
    }

    public function getInjectables()
    {
        return [
            'differentParam' => 'dependentParam',
            'directMapParam' => 'dependentParam',
            'setterParam' => 'dependentParam'
        ];
    }
}