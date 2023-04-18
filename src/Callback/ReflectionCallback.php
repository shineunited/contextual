<?php

/**
 * This file is part of Contextual.
 *
 * (c) Shine United LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ShineUnited\Contextual\Callback;

use ShineUnited\Contextual\EmptyContainer;
use ShineUnited\Contextual\Exception\DependencyException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionException;
use Exception;

/**
 * Abstract Reflection Callback
 */
abstract class ReflectionCallback implements CallbackInterface {
	private string $propertyName;
	private string $functionName;
	private string $class;

	private string $realName;
	private string $typeLabel = 'unknown';
	private string $fileName;
	private int $startLine;
	private string $declaringClass;
	private bool $isReflexive = false;
	private array $parameters = [];
	private array $attributes = [];

	/**
	 * Create a new callback.
	 *
	 * @param ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $reflection The reflection object.
	 */
	public function __construct(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $reflection) {
		$this->realName = $reflection->getName();

		if ($reflection instanceof ReflectionProperty) {
			$this->processReflectionProperty($reflection);
		}

		if ($reflection instanceof ReflectionMethod) {
			$this->processReflectionMethod($reflection);
		}

		if ($reflection instanceof ReflectionFunction) {
			$this->processReflectionFunction($reflection);
		}

		if ($reflection instanceof ReflectionFunctionAbstract) {
			$this->processReflectionFunctionAbstract($reflection);
		}

		if ($reflection instanceof ReflectionClass) {
			$this->processReflectionClass($reflection);
		}

		$this->buildAttributes($reflection);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isProperty(): bool {
		if (isset($this->propertyName)) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyName(): ?string {
		if (!$this->isProperty()) {
			return null;
		}

		return $this->propertyName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFunction(): bool {
		if (isset($this->functionName)) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFunctionName(): ?string {
		if (!$this->isFunction()) {
			return null;
		}

		return $this->functionName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasClass(): bool {
		if (isset($this->class)) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClass(): ?string {
		if (!$this->hasClass()) {
			return null;
		}

		return $this->class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isReflexive(): bool {
		if ($this->isReflexive) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttributes(?string $class = null): iterable {
		foreach ($this->attributes as $attribute) {
			if (is_null($class) || is_a($attribute, $class)) {
				yield $attribute;
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function canResolveParameters(ContainerInterface $container, array $arguments = []): bool {

		for ($index = 0; $index < count($this->parameters); $index++) {
			$parameter = $this->parameters[$index];
			if (isset($arguments[$index])) {
				continue;
			}

			if (isset($arguments[$parameter->getName()])) {
				continue;
			}

			$type = $parameter->getType();
			if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
				if ($container->has($type->getName())) {
					continue;
				}
			}

			if ($container->has($parameter->getName())) {
				continue;
			}

			if ($parameter->isDefaultValueAvailable()) {
				continue;
			}

			if ($parameter->allowsNull()) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolveParameters(ContainerInterface $container, array $arguments = []): array {
		$parameters = [];

		for ($index = 0; $index < count($this->parameters); $index++) {
			$parameter = $this->parameters[$index];

			if (!$parameter instanceof ReflectionParameter) {
				throw new DependencyException(sprintf(
					'Unable to resolve parameter "%s", invalid ReflectionParameter: %s.',
					$index,
					(string) $this
				));
			}

			if (isset($arguments[$index])) {
				$parameters[] = $arguments[$index];
				continue;
			}

			if (isset($arguments[$parameter->getName()])) {
				$parameters[] = $arguments[$parameter->getName()];
				continue;
			}

			// check type hint
			$type = $parameter->getType();
			if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
				$class = $type->getName();

				try {
					$parameters[] = $container->get($class);
					continue;
				} catch (NotFoundExceptionInterface $exception) {
					// ignore and continue checking
				} catch (ContainerExceptionInterface $exception) {
					// other container exceptions should be passed on
					throw new DependencyException(sprintf(
						'Error resolving class "%s" for %s: %s',
						$class,
						(string) $this,
						$exception->getMessage()
					), 0, $exception);
				}
			}

			// check name next
			try {
				$parameters[] = $container->get($parameter->getName());
				continue;
			} catch (NotFoundExceptionInterface $exception) {
				// ignore and continue checking
			} catch (ContainerExceptionInterface $exception) {
				// other container exceptions should be passed on
				throw new DependencyException(sprintf(
					'Error resolving name "%s" for %s: %s',
					$parameter->getName(),
					(string) $this,
					$exception->getMessage()
				), 0, $exception);
			}

			// check for default value
			try {
				$parameters[] = $parameter->getDefaultValue();
				continue;
			} catch (ReflectionException $exception) {
				// ignore and continue checking
			}

			// check for allows null
			if ($parameter->allowsNull()) {
				$parameters[] = null;
				continue;
			}

			// nothing found
			throw new DependencyException(sprintf(
				'Unable to resolve parameter "%s" for %s.',
				$parameter->getName(),
				(string) $this
			));
		}

		return $parameters;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(mixed ...$args): mixed {
		if (!empty($args) && reset($args) instanceof ContainerInterface) {
			// if the first argument is a container use that

			$container = array_shift($args);

			return $this->execute($container, $args);
		}

		// otherwise use an empty container
		$container = new EmptyContainer();

		return $this->execute($container, $args);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		$pattern = [];
		$variables = [];

		$pattern[] = '%s';
		$variables[] = $this->typeLabel;

		$pattern[] = '"%s"';
		$variables[] = $this->realName;

		if ($this->declaringClass) {
			$pattern[] = 'of "%s"';
			$variables[] = $this->declaringClass;
		}

		if ($this->fileName) {
			$variables[] = $this->fileName;
			if ($this->startLine) {
				$pattern[] = '(%s @ %d)';
				$variables[] = $this->startLine;
			} else {
				$pattern[] = '(%s)';
			}
		}

		return sprintf(implode(' ', $pattern), ...$variables);
	}

	/**
	 * Process a ReflectionProperty
	 *
	 * @param ReflectionProperty $property The property.
	 *
	 * @return void
	 */
	private function processReflectionProperty(ReflectionProperty $property): void {
		$this->typeLabel = 'property';

		$this->propertyName = $property->getName();

		$this->declaringClass = $property->getDeclaringClass()->getName();

		if ($property->hasType()) {
			$type = $property->getType();

			if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
				$this->class = $type->getName();
			}
		}
	}

	private function processReflectionMethod(ReflectionMethod $method): void {
		$this->typeLabel = 'method';

		$this->declaringClass = $method->getDeclaringClass()->getName();
	}

	private function processReflectionFunction(ReflectionFunction $function): void {
		$this->typeLabel = 'function';

		if ($function->isClosure()) {
			$class = $function->getClosureScopeClass();
			if ($class instanceof ReflectionClass) {
				$this->declaringClass = $class->getName();
			}
		}
	}

	private function processReflectionFunctionAbstract(ReflectionFunctionAbstract $function): void {
		$this->functionName = $this->realName;

		$this->parameters = $function->getParameters();

		$isBoolean = false;
		if ($function->hasReturnType()) {
			$type = $function->getReturnType();

			if ($type instanceof ReflectionNamedType) {
				if (!$type->isBuiltin()) {
					$this->class = $type->getName();
				} elseif ($type->getName() == 'bool') {
					$isBoolean = true;
				}
			}
		}

		if (strtolower(substr($this->realName, 0, 3)) == 'get') {
			// 'get' functions for any return type
			$this->propertyName = lcfirst(substr($this->realName, 3));
		} elseif ($isBoolean && strtolower(substr($this->realName, 0, 2)) == 'is') {
			// 'is' functions are allowed for boolean returns
			$this->propertyName = lcfirst(substr($this->realName, 2));
		}

		if ($function->getFileName()) {
			$this->fileName = $function->getFileName();
		}

		if ($function->getStartLine()) {
			$this->startLine = $function->getStartLine();
		}

		// check for reflexivity
		if (isset($this->class)) {
			foreach ($this->parameters as $parameter) {
				if (!$parameter->hasType()) {
					continue;
				}

				$type = $parameter->getType();
				if (!$type instanceof ReflectionNamedType) {
					continue;
				}

				if ($type->isBuiltin()) {
					continue;
				}

				if ($this->class == $type->getName()) {
					$this->isReflexive = true;
					break;
				}
			}
		}
	}

	private function processReflectionClass(ReflectionClass $class): void {
		$this->typeLabel = 'class';

		$this->class = $class->getName();

		if ($class->getFileName()) {
			$this->fileName = $class->getFileName();
		}

		if ($class->getConstructor()) {
			$this->processReflectionFunctionAbstract($class->getConstructor());
		}
	}

	private function buildAttributes(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $reflection): void {
		$attributes = $reflection->getAttributes();
		foreach ($attributes as $attribute) {
			$instance = $attribute->newInstance();

			$this->attributes[] = $instance;
		}
	}
}
