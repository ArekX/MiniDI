<?php

namespace ArekX\MiniDI;

use ArekX\MiniDI\Exception\CircularDependencyException;
use ArekX\MiniDI\Exception\InjectableNotFoundException;
use ArekX\MiniDI\Exception\InjectablePropertyException;
use ArekX\MiniDI\Exception\InvalidConfigurationException;
use ArekX\MiniDI\Exception\StackUnderflowException;

/**
 * Class Injector
 * @package ArekX\MiniDI
 */
class Injector
{
    /**
     * @var array Injection stack for circular dependency detection.
     */
    protected $dependencyStack = [];

    /**
     * @var array List of shared instances
     */
    protected $sharedObjects = [];

    /**
     * @var array Assignment configuration for this injector.
     */
    protected $assignments = [];

    /**
     * @var Injector|null Parent injector for dependency resolution.
     */
    protected $parent = null;

    /**
     * Injector constructor.
     * @param array|Injector $config Injector configuration.
     * @see Injector::merge() for configuration parameters.
     */
    public function __construct($config = [])
    {
        $this->merge($config);
    }

    /**
     * Merges configuration of this injector with configuration array or configuration from other Injector
     *
     * Configuration is in following array format:
     * ```php
     * [
     *    'dependencyKey' => []
     * ]
     * ```
     *
     * Configuration is normalized and assigned using Injector::assign.
     *
     * After merging instance of an object can be retrieved by calling Injector::get($key). All dependencies
     * of that retrieved instance are automatically resolved.
     *
     * @param $config array|Injector Configuration for this injector.
     * @param bool $isolate
     * @return $this
     * @throws InvalidConfigurationException
     * @see Injector::makeInstance()
     * @see Injector::createInjectorFromConfig()
     * @see Injector::assign()
     */
    public function merge($config, $isolate = false)
    {
        if (empty($config)) {
            return $this;
        }

        $assignments = $config instanceof Injector ? $config->assignments : $config;
        $newInjector = $config instanceof Injector ? $this->createInjectorFromObject($config, $isolate) : null;

        foreach ($assignments as $key => $config) {
            $config = is_string($config) ? ['class' => $config, 'config' => [], 'injector' => null] : $config;

            if (is_callable($config)) {
                $config = ['closure' => $config];
            }

            if (empty($config['injector'])) {
                $config['injector'] = $newInjector;
            }

            $this->assign($key, $config);
        }

        return $this;
    }

    /**
     * Returns class instance or a value, which is resolved by $key parameter.
     *
     * If nothing is found for $key parameters this injector will check the keys of parent injector (if it has one),
     * and will throw InjectableNotFoundException if no results are found.
     *
     * If a class does not have ['shared' => true] in its configuration this injector will always create, a new
     * instance of this class.
     *
     * If a class has circular dependencies (e.g. Class1 depends on Class2 which depends on Class1)
     * CircularDependencyException will be thrown.
     *
     * @see Injector::merge()
     * @param $key string Class key
     * @return mixed
     * @throws CircularDependencyException
     * @throws InjectableNotFoundException
     */
    public function get($key)
    {
        if (!isset($this->assignments[$key])) {
            if ($this->parent !== null && $this->parent !== $this) {
                return $this->parent->get($key);
            }

            throw new InjectableNotFoundException($key);
        }

        if (array_key_exists('value', $this->assignments[$key])) {
            return $this->assignments[$key]['value'];
        }

        if (array_key_exists($key, $this->sharedObjects)) {
            return $this->sharedObjects[$key];
        }

        $class = !empty($this->assignments[$key]['class']) ? $this->assignments[$key]['class'] : null;
        $injector = $this->assignments[$key]['injector'];

        if ($injector === null) {
            $injector = $this;
        }

        foreach ($this->dependencyStack as $parentClass) {
            if ($class == $parentClass[0] && $key == $parentClass[1]) {
                throw new CircularDependencyException($this->dependencyStack, $class);
            }
        }

        $object = $this->makeInstance($key, $injector);


        return $object;

    }

    /**
     * Makes a new instance of $class.
     *
     *
     * @param $class string Class name which will be created.
     * @param Injector $dependencyInjector Injector which will be used to handle dependencies of this instance.
     * @return mixed Instance of class defined by $class parameter.
     * @throws InjectablePropertyException
     * @throws InvalidConfigurationException
     * @see Injector::resolveInstanceDependencies()
     */
    protected function makeInstance($key, Injector $dependencyInjector)
    {
        $class = !empty($this->assignments[$key]['class']) ? $this->assignments[$key]['class'] : $this->assignments[$key]['closure'];

        $config = $this->assignments[$key]['config'];
        $dependencies = $this->assignments[$key]['dependencies'];

        if (is_callable($class)) {
            $instance = $class($config, $dependencies, $dependencyInjector);
        } else {
            $instance = new $class($config);
        }

        $this->pushDependency([get_class($instance), $key]);

        /** @var object $instance */

        if (!empty($this->assignments[$key]['shared'])) {
            $this->sharedObjects[$key] = $instance;
        }

        $this->resolveInstanceDependencies($instance, $dependencies, $dependencyInjector);

        if (!empty($this->assignments[$key]['runAfterInit'])) {
            $instance->{$this->assignments[$key]['runAfterInit']}();
        }

        return $instance;
    }

    /**
     * Marks specific key as shared.
     *
     *
     * Shared classes will only be created once and they will be a shared dependency
     * across all classes which require them.
     *
     * Marking value as shared will do nothing as values are automatically shared.
     *
     * @param $key string ke
     * @return $this
     */
    public function share($key)
    {
        $this->assignments[$key]['shared'] = true;
        return $this;
    }

    /**
     * Set specific value to this injector.
     *
     * Value can by any kind of value, classes which have $key as their
     * dependency will have this value.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function assignValue($key, $value)
    {
        return $this->assign($key, ['value' => $value]);
    }

    /**
     * Set specific class name to be resolved when calling Injector::get($key).
     *
     * Dependencies of this are resolved by this injector unless $injector is passed
     *
     * @param $key string Key which will be resolved to class.
     * @param $class string Class name
     * @param $dependencies null|array|string|callable Dependencies of this class
     * @param array $config Configuration which will be passed to created $class.
     * @param null|Injector|array $injector
     * @see Injector::makeInstance()
     *
     * @return $this
     */
    public function assignClass($key, $class, $dependencies = null, $config = [], $injector = null)
    {
        return $this->assign($key, [
            'class' => $class,
            'config' => $config,
            'injector' => $injector,
            'dependencies' => $dependencies
        ]);
    }

    /**
     * Assigns closure for instance resolution.
     *
     * @param $key string
     * @param $closure callable
     * @param null|array|string|callable $dependencies
     * @param array $config
     * @param null|array|Injector $injector
     * @return Injector
     */
    public function assignClosure($key, $closure, $dependencies = null, $config = [], $injector = null)
    {
        return $this->assign($key, [
            'closure' => $closure,
            'config' => $config,
            'injector' => $injector,
            'dependencies' => $dependencies
        ]);
    }

    /**
     * Assigns specific configuration for a $key.
     *
     * If configuration is passed as string then that string is used as class name when creating an instance
     * of this class.
     *
     * Configuration can be passed as array. Following format is processed::
     * ```php
     * [
     *    'class' => 'ClassName1',
     *    'shared' => false, // Whether or not this class is shared. Shared classes have only one instance.
     *    'config' => [
     *        'key1' => 'value1',
     *        'key2' => 'value2',
     *        // ...
     *    ] // Optional configuration array will be passed to ClassName1 constructor as first parameter.
     *    'injector' => [] // Optional inner injector configuration.
     *    'dependencies' => null // Optional dependencies, see Injector::makeInstance() for more info.
     *    'runAfterInit' => 'methodName' // Optional method which will be run after class initialization.
     * ]
     * ```
     *
     * Value configuration can also be set in following array format::
     * ```php
     * [
     *    'value' => "specificValue"  // When calling Injector::get('key') it will return "specificValue".
     * ]
     * ```
     *
     *
     * When passing specific injector for a class, that injector will be used to handle that class dependencies.
     *
     * Injector configurations can be passed as array configuration, or as another injector instance:
     *
     * As configuration:
     * ```php
     * [
     *     'class' => 'ClassName1',
     *     'injector' => [
     *          'class' => 'InjectorClassName',
     *          'config' => [], // Injector config.
     *          'isolate' => false // Whether or not this injector will be isolated
     *     ]
     * ]
     * ```
     *
     * As instance:
     * ```php
     * [
     *     'class' => 'ClassName1',
     *     'injector' => [
     *          'instance' => $injectorObject,
     *          'config' => [], // Injector config which will be merged to that injector.
     *          'isolate' => false // Whether or not this injector will be isolated
     *     ]
     * ]
     *
     * Isolated injectors do not have access to parent injector configuration.
     *
     *
     * @param $key
     * @param array|string|callable $config
     * @throws InvalidConfigurationException
     * @return $this
     */
    public function assign($key, $config)
    {
        if (is_callable($config)) {
            $this->assignments[$key] = $config;
            return $this;
        }

        $config = is_string($config) ? ['class' => $config, 'config' => [], 'injector' => null] : $config;

        if (array_key_exists('value', $config)) {
            $this->assignments[$key] = $config;
            return $this;
        }

        if (empty($config['class']) && empty($config['closure'])) {
            throw new InvalidConfigurationException($config, "Class or closure must be set for key {$key}.");
        }

        if (isset($config['class'])) {
            $config['class'] = (new \ReflectionClass($config['class']))->getName();
        }

        if (!isset($config['config'])) {
            $config['config'] = [];
        }

        if (!isset($config['dependencies'])) {
            $config['dependencies'] = null;
        }

        if (!isset($config['injector'])) {
            $config['injector'] = null;
        }

        if (!isset($config['runAfterInit'])) {
            $config['runAfterInit'] = null;
        }

        if (is_array($config['injector'])) {
            $config['injector'] = $this->createInjectorFromConfig($config['injector']);
        }

        if (!isset($config['shared'])) {
            $config['shared'] = false;
        }

        $this->assignments[$key] = $config;

        return $this;
    }

    /**
     * Sets another injector instance as a parent to this injector.
     *
     * When resolving dependencies, if a dependency cannot be found in this injector
     * it will call Injector::get() of its parent.
     *
     * @param Injector $parent
     * @return $this
     */
    public function setParent(Injector $parent)
    {
        if ($parent !== $this) {
            $this->parent = $parent;
        }

        return $this;
    }


    /**
     * @return Injector|null Returns parent injector class or null if it has no parent.
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * Creates new instance of this injector.
     *
     * This is a helper method for use in functional style and method chaining.
     *
     * @param array $config Injector configuration
     * @return static
     * @see Injector::merge()
     */
    public static function create($config = [])
    {
        return new static($config);
    }

    /**
     * Checks this injector and its parent if it has a specific dependency
     * defined by $key.
     *
     * @param $key string name of the dependency.
     * @return bool
     */
    public function has($key)
    {
        $result = !empty($this->assignments[$key]);

        if (!$result && $this->parent !== null) {
            return $this->parent->has($key);
        }

        return $result;
    }

    protected function createInjectorFromObject($injector, $isolate = false)
    {
        $newInjector = Injector::create($injector->assignments);

        if (!$isolate && $newInjector !== null) {
            $newInjector->setParent($this);
        }

        return $newInjector;
    }

    /**
     * Creates new injector instance from configuration array.
     *
     * Configuration array is in format:
     *
     *
     * ```php
     * [
     *    'class' => 'InjectorClassName',
     *    'config' => [
     *       'dependency' => 'ClassName1',
     *       ...
     *    ],
     *    'isolate' => false // Optional parameter whether or not this injector instance is isolated (no parent).
     * ]
     * ``
     *
     * @param $array
     * @return Injector
     * @throws InvalidConfigurationException
     * @see Injector::assign()
     */
    protected function createInjectorFromConfig($array)
    {
        $injectorConfig = isset($array['config']) ? $array['config'] : [];

        if (isset($array['class'])) {
            $injectorClass = $array['class'];
            $injector = new $injectorClass();
        } elseif (isset($array['instance'])) {
            $injector = $array['instance'];
        } else {
            throw new InvalidConfigurationException($array, "Injector configuration must have either class or instance property set.");
        }

        /** @var $injector Injector */

        $injector->merge($injectorConfig);

        if (empty($array['isolate'])) {
            $injector->setParent($this);
        }

        return $injector;
    }

    /**
     * Pushes dependency information to stack
     *
     * This function is used for detecting circular dependencies.
     *
     * @param $classInfo array Information about class in format: ['className', 'dependencyKey']
     */
    protected function pushDependency($classInfo)
    {
        $this->dependencyStack[] = $classInfo;
    }

    /**
     * Pops dependency from stack.
     *
     * @return array $class infromation
     * @throws \Exception
     * @see Injector::pushDependency()
     */
    protected function popDependency()
    {
        if (empty($this->dependencyStack)) {
            throw new StackUnderflowException();
        }

        return array_pop($this->dependencyStack);
    }

    /**
     * Resolves dependencies of one instance.
     *
     * If $dependencies is null, injector will consider all public properties of this class to be dependency keys
     * which will be filled by classes.
     *
     * If $dependencies is array, injector will fill only those public properties. Array can be in following formats:
     * As simple array:
     * ```php
     *  // This will set class property or call setter of the same name to defined dependency.
     * ['dependency1', 'dependency2', ... ]
     * ```
     *
     * Or as map array:
     * ```php
     * // This will set class property or call setter of name specified by key to a dependency specified by value.
     * ['classProperty1' => 'dependency1', 'classProperty2' => 'dependency2', ...]
     * ```
     *
     * Or combined:
     * ```php
     * ['dependency1', 'classProperty' => 'dependency2']
     * ```
     *
     * If $dependencies is string, then a method of that name will be called from class instance, that method
     * should return an array in format explained above, and injector will use that array to resolve dependencies.
     *
     * If $dependencies is callable, then injector will call that callable passing instance and itself. That
     * callable needs to return an array in format explained above and injector will use that array to resolve
     * dependencies.
     *
     * Callable is in following format:
     * ```php
     * function($instance, $injector) {
     *    return ['dependency1', 'classParam' => 'dependency2'] // Array format as defined above.
     * }
     * ```
     *
     * @param $instance
     * @param $dependencies
     * @param Injector $dependencyInjector
     * @throws InjectablePropertyException
     */
    protected function resolveInstanceDependencies($instance, $dependencies, Injector $dependencyInjector)
    {
        $oldStack = $dependencyInjector->dependencyStack;
        $dependencyInjector->dependencyStack = $this->dependencyStack;

        if ($dependencies === null) {
            $dependencies = array_keys(get_object_vars($instance));
        } elseif (is_string($dependencies)) {
            $dependencies = $instance->{$dependencies}();
        } elseif (is_callable($dependencies)) {
            $dependencies = $dependencies($instance, $dependencyInjector);
        }

        foreach ($dependencies as $property => $injectorProperty) {
            if (is_numeric($property)) {
                $property = $injectorProperty;
            }

            $methodName = 'set' . ucfirst($property);

            if (method_exists($instance, $methodName)) {
                $instance->{$methodName}($dependencyInjector->get($injectorProperty));
            } elseif (property_exists($instance, $property)) {
                $instance->{$property} = $dependencyInjector->get($injectorProperty);
            } else {
                throw new InjectablePropertyException($property, get_class($instance));
            }
        }

        $dependencyInjector->dependencyStack = $oldStack;
        $this->popDependency();
    }
}