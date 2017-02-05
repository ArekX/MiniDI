<?php
namespace InjectorMappingTest;

class TestClassOneParamRemap
{
    public $differentParam;
    public $notDependentParam = "I AM NOT SET";

    public function getInjectables()
    {
        return [
            'differentParam' => 'dependentParam'
        ];
    }
}