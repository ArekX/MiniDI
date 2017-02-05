# MiniDI

Minimal PHP Dependency Injector with maximum configurability

Purpose of dependency injection is to have all of the classes and class dependencies resolved without calling for 
classes yourself and resolving them yourself directly.

In standard PHP you might do things like these:

```php
class TestClass1 {
   public $class2;
   
   public function __construct(TestClass2 $class2) {
        $this->class2 = $class2;
   }
}

class TestClass2 {
   public $class3;
   
   public function __construct(TestClass3 $class3) {
        $this->class3 = $class3;
   }
}

class TestClass3 {}

$class3 = new TestClass3();
$class2 = new TestClass2($class3);
$class1 = new TestClass1($class2);
```

Where you would wire classes and dependencies yourself. But with dependency injector, such as `MiniDI` you can do this:

```php
class TestClass1 {
   public $class2;
}

class TestClass2 {
   public $class3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
    'testObject' => 'TestClass1',
    'class2' => 'TestClass2',
    'class3' => 'TestClass3'
]);

$class1 = $injector->get('testObject');
```

`$class1` would have instance of `TestClass1` with `$class2` already populated with instance of `TestClass2`, and
also in that instance `$class3` will be populated with instance of `TestClass3`.
 
 
 This approach to class management allows for easy class switching and makes your code way more testable as you can do
 mocking for your classes without much hassle.

# Usage

## Simple usage

```php
class TestClass1 {
	public $dependsOnTestClass2;
}

class TestClass2 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'dependsOnTestClass2' => 'TestClass2'
]);

$testObject = $injector->get('testObject');

echo $testObject->dependsOnTestClass2 instanceof TestClass2 ? 'Success!' : 'Fail'; // Outputs: Success!
```


## Shared objects

Objects can be shared easily across diffent objects by specifying `'shared' => true`.

```php
class TestClass1 {
	public $testClass2;
	public $sharedClass3;
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');

echo $testObject->sharedClass3 === $testObject->testClass2->sharedClass3 ? 'Same shared classes!' : 'Fail'; // Outputs: Same shared classes!
```

## Custom Config

You can specify which parameters will be injected yourself by setting `'dependencies' => []` configuration. `'dependencies'` can be set in multiple ways, as an array, class method or even callable:

Example using array:
```php
class TestClass1 {
	public $testClass2;
	public $mappedParam;
	public $notInjectedParameter;
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => ['class' => 'TestClass1', 'dependencies' => [
	    'testClass2',
	    'mappedParam' => 'sharedClass3'
	],
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');

echo $testObject->mappedParam === $testObject->testClass2->sharedClass3 ? 'Same shared classes!' : 'Fail'; // Outputs: Same shared classes!
```

Example using class method:
```php
class TestClass1 {
	public $testClass2;
	public $mappedParam;
	public $notInjectedParameter;
	
	public function getInjectables()
	{
	    return [
            'testClass2',
            'mappedParam' => 'sharedClass3'
        ];
	}
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => ['class' => 'TestClass1', 'dependencies' => 'getInjectables',
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');
```

Example using callable:
```php
class TestClass1 {
	public $testClass2;
	public $mappedParam;
	public $notInjectedParameter;
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => ['class' => 'TestClass1', 'dependencies' => function($instance, $injector) {
	    // Do things like checking if $instance needs some additional dependencies,
	    // Or if $injector has those dependencies by calling $injector->has('key');
	    return [
            'testClass2',
            'mappedParam' => 'sharedClass3'
        ];
	},
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');
```

You can also set specific custom configuration for configuring one injected object.

```php
class TestClass {
	public $class2;
	public $customParam;

	public function __construct($config = []) 
	{
		$this->customParam = $config['customParam'];
	}
}

class TestClass2 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => [
		'class' => 'TestClass',
		'config' => [
			'customParam' => "Test String"
		]
	],
	'class2' => 'TestClass2'
]);

echo $injector->get('testObject')->customParam; // Outputs: Test String
```

# Using values

You can use any value in properties of object by setting the key. All classes which are
configured to depend on that value will have it
when they are created.

```php
class TestClass {
    public $paramValue;
}

$injector = \ArekX\MiniDI\Injector::create([
   'testObject' => 'TestClass',
   'paramValue' => ['value' => "Some value"]
]);

echo $injector->get('testObject')->paramValue; // Will output "Some value"
```

# Running stuff after dependency resolution (auto-wiring)

If you need to run functions after class has all needed dependencies you can do so by setting `runAfterInit` property.

```php
class TestClass1 
{
    public $dependency1;
    
    protected $hiddenParam;
    
    public function init() 
    {
        $this->hiddenParam = $dependency1->getValue();
    }
    
    public function getHiddenParam()
    {
        return $this->hiddenParam;
    }
}

class TestClass2 
{
   public function getValue() 
   {
        return "someValue";
   }
}

$injector = \ArekX\MiniDI\Injector::create([
   'testObject' => ['class' => 'TestClass1', 'runAfterInit' => 'init'],
   'dependency1' => 'TestClass2'
]);

$object = $injector->get('testObject');

echo $object->getHiddenParam(); // Outputs "someValue"
```

# Additional configuration

After injector is created you can pass additional configuration to it via methods:

* `Injector::merge(['dependency' => 'Class'])` This will merge current injectors (and overwrite existing) configuration for this key.
* `Injector::assign('key', 'Class')` or `Injector::assign('key', ['class' => 'SomeClass'])` will set the configuration for that specific key with configuration specified in the array.
* `Injector::assignClass('key', 'ClassName', ['classDependency1'], $injector)` assigns class configuration for that key. Dependency and Injector parameters are optional.
* `Injector::assignValue('key', "some value")` assigns key to be a value.
* `Injector::setParent($parentInjector)` sets parent injector of this injector. When this parent is set, Injector will call `Injector::get()` of the parent if it cannot find resolution for specified key.

# Installation

Install via composer

	composer require arekx/mini-di

# Testing

To run tests in injector you will need phpunit.phar in the directory of the project. 
Please run following command in cloned folder:

### Windows

In Command Prompt Run:

	test.bat

### Linux

In Terminal Run:

	./test.sh


# Documentation

To run tests in injector you will need phpDocumentor.phar in the directory of the project. 
Please run following command in cloned folder:

### Windows

In Command Prompt Run:

	document.bat

### Linux

In Terminal Run:

	./document.sh

Documentation will be generated in `doc` folder.

# License

MIT License

Copyright (c) 2017 Aleksandar Panic

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.