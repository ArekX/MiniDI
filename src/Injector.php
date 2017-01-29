<?php

namespace ArekX\MiniDI;

use ArekX\MiniDI\Exception\CircularDependencyException;
use ArekX\MiniDI\Exception\InjectableNotFoundException;

class Injector
{
	protected $injectStack = [];
	protected $previousInjectStacks = [];
	protected $sharedObjects = [];
	protected $assignments = [];

	protected $parent = null;

	public function __construct($config = [])
	{
		$this->merge($config);
	}

	public function merge($config, $isolate = false)
	{
		if (empty($config)) {
			return $this;
		}

		$assignments = $config instanceof Injector ? $config->assignments : $config;
		$newInjector = $config instanceof Injector ? $this->createInjectorFromObject($config, $isolate) : null;

		foreach ($assignments as $key => $config) {
			$config = is_string($config) ? ['class' => $config, 'config' => [], 'injector' => null] : $config;

			if (array_key_exists('value', $config)) {
				$this->assignValue($key, $config);
				continue;
			}

			if (empty($config['class'])) {
				throw new InvalidConfigurationException("Class must be set for key {$key}.");
			}

			if (!isset($config['config'])) {
				$config['config'] = [];
			}

			if (empty($config['injector'])) {
				$config['injector'] = $newInjector;
			}

			$this->assign($key, $config['class'], $config['config'], $config['injector']);

			if (!empty($config['shared'])) {
				$this->assignments[$key]['shared'] = true;

				if ($config instanceof Injector && array_key_exists($key, $config) && !$isolate) {
					$this->sharedObjects[$key] = $config->sharedObjects[$key];
				}
			}

		}

		return $this;
	}

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

		$class = $this->assignments[$key]['class'];
		$injector = $this->assignments[$key]['injector'];

		if ($injector === null) {
			$injector = $this;
		}

		foreach ($this->injectStack as $parentClass) {
			if ($class == $parentClass[0]) {
				throw new CircularDependencyException($this->injectStack, $class);
			}
		}

		$this->pushInjectStack([$class, $key]);
		$oldStack = $injector->injectStack;
		$injector->injectStack = $this->injectStack;
		
		$object = new $class($injector, $this->assignments[$key]['config']); 

		$injector->injectStack = $oldStack;
		$this->popInjectStack();

		if (!empty($this->assignments[$key]['shared'])) {
			$this->sharedObjects[$key] = $object;
		}

		return $object;
	}

	public function share($key)
	{
		$this->assignments[$key]['shared'] = true;
		return $this;
	}

	public function assignValue($key, $value)
	{
		$this->assignments[$key] = ['value' => $value];
		return $this;
	}

	public function assign($key, $class, $config = [], $injector = null)
	{
		if (is_array($injector)) {
			$injector = $this->createInjectorFromConfig($injector);
		}

		$this->assignments[$key] = ['class' => $class, 'config' => $config, 'injector' => $injector];
		return $this;
	}

	public function setParent(Injector $parent)
	{
		$this->parentInjector = $parent;
	}

	protected function createInjectorFromObject($injector, $isolate = false)
	{
		$newInjector = Injector::create($injector->assignments);

		if ($config instanceof Injector) {
			$newInjector->sharedObjects = $config->sharedObjects;
		}

		if (!$isolate && $newInjector !== null) {
			$newInjector->setParent($this);
		}

		return $newInjector;
	}

	protected function createInjectorFromConfig($array)
	{
		$injectorConfig = isset($array['config']) ? $array['config'] : [];

		if (isset($array['class'])) {
			$injectorClass = $array['class'];
        	$injector = new $injectorClass();
		} elseif (isset($array['object'])) {
			$injector = $array['object'];
		} else {
			throw new InvalidConfigurationException("Injector configuration must have either class or object property set.");
		}

		$injector->merge($injectorConfig, !empty($array['isolateConfig']));

        if (empty($array['isolate'])) {
        	$injector->setParent($this);
        }

        return $injector;
	}

	protected function pushInjectStack($className)
	{
		$this->injectStack[] = $className;
	}

	protected function popInjectStack()
	{
		if (empty($this->injectStack)) {
			throw new \Exception("Injection stack is empty!");
		}

		return array_pop($this->injectStack);
	}

	public static function create($config = [])
	{
		return new static($config);
	}
}