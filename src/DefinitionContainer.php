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

namespace ShineUnited\Contextual;

use ShineUnited\Contextual\Callback\ClosureCallback;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use ShineUnited\Contextual\Definition\CallbackDefinition;
use ShineUnited\Contextual\Definition\ValueDefinition;
use ShineUnited\Contextual\Definition\DefinitionSourceInterface;
use ShineUnited\Contextual\Definition\CompositeDefinitionSource;
use ShineUnited\Contextual\Exception\EntryNotFoundException;
use ShineUnited\Contextual\Exception\DependencyException;
use ShineUnited\Contextual\Exception\DefinitionNotFoundException;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;
use ShineUnited\Contextual\Exception\InvalidCallbackException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Closure;
use WeakReference;

/**
 * Definition Container
 */
class DefinitionContainer extends BaseContainer {
	private CompositeDefinitionSource $definitions;

	private array $resolvedDefinitions = [];
	private array $currentlyResolving = [];
	private array $resolverStack = [];

	/**
	 * Create a new container.
	 *
	 * @param DefinitionSourceInterface|DefinitionInterface|array ...$definitions Definitions to add to container.
	 *
	 * @throws ContainerExceptionInterface Error occurred during creation.
	 */
	public function __construct(DefinitionSourceInterface|DefinitionInterface|array ...$definitions) {
		$this->definitions = new CompositeDefinitionSource();

		foreach ($definitions as $definition) {
			if ($definition instanceof DefinitionSourceInterface) {
				$this->definitions->addDefinitionSource($definition);
			} elseif ($definition instanceof DefinitionInterface) {
				$this->definitions->addDefinition($definition);
			} else {
				foreach ($definition as $id => $value) {
					if ($value instanceof Closure) {
						$this->definitions->addDefinition(new CallbackDefinition(
							$id,
							new ClosureCallback($value)
						));
					} else {
						$this->definitions->addDefinition(new ValueDefinition(
							$id,
							$value
						));
					}
				}
			}
		}
	}

	/**
	 * Check if a distinct parent container exists.
	 *
	 * @return boolean True if parent container exists.
	 */
	protected function hasParentContainer(): bool {
		return false;
	}

	/**
	 * Get the parent container. If there is not a parent container return self.
	 *
	 * @return ContainerInterface The parent container.
	 */
	protected function getParentContainer(): ContainerInterface {
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $id): bool {
		if (parent::has($id)) {
			return true;
		}

		try {
			$definition = $this->definitions->getDefinition($id);

			$container = $this;
			if ($definition->isDecorator() && $this->hasParentContainer()) {
				$container = $this->getParentContainer();
			}

			if ($definition->isResolvable($container)) {
				return true;
			} elseif ($definition->isProtected()) {
				return false;
			}

			// pass to possible parent container
		} catch (DefinitionNotFoundException $exception) {
			// ignore and pass to possible parent container
		} catch (InvalidDefinitionException $exception) {
			return false;
		}

		if ($this->hasParentContainer()) {
			$container = $this->getParentContainer();
			return $container->has($id);
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id): mixed {
		if (parent::has($id)) {
			return parent::get($id);
		}

		if (isset($this->resolvedDefinitions[$id]) || array_key_exists($id, $this->resolvedDefinitions)) {
			return $this->resolveReference($this->resolvedDefinitions[$id]);
		}

		if ($this->definitions->hasDefinition($id)) {
			$definition = $this->definitions->getDefinition($id);
			$value = $this->resolveDefinition($definition);

			$this->resolvedDefinitions[$id] = $value;

			return $this->resolveReference($value);
		}

		if ($this->hasParentContainer()) {
			$container = $this->getParentContainer();
			return $container->get($id);
		}

		throw new EntryNotFoundException(sprintf(
			'Identifier "%s" not found in container.',
			$id
		));
	}

	/**
	 * Resolve definition.
	 *
	 * @param DefinitionInterface $definition The definition to resolve.
	 *
	 * @throws ContainerExceptionInterface Error resolving definition.
	 *
	 * @return mixed The resolved value.
	 */
	private function resolveDefinition(DefinitionInterface $definition): mixed {
		$this->resolverStack[] = $definition->getIdentifier();
		if (isset($this->currentlyResolving[$definition->getIdentifier()])) {
			throw new DependencyException(sprintf(
				'Circular dependency detected while resolving "%s" in container (%s).',
				$definition->getIdentifier(),
				implode(', ', $this->resolverStack)
			));
		}
		$this->currentlyResolving[$definition->getIdentifier()] = true;

		try {
			$container = $this;
			if ($definition->isDecorator() && $this->hasParentContainer()) {
				$container = $this->getParentContainer();
			}

			$value = $definition->resolve($container);
		} finally {
			unset($this->currentlyResolving[$definition->getIdentifier()]);
			array_pop($this->resolverStack);
		}

		return $value;
	}

	/**
	 * Resolve a possible reference.
	 *
	 * @param mixed $reference The reference.
	 *
	 * @throws DependencyException Referenced object has been destroyed.
	 *
	 * @return mixed The referenced value.
	 */
	private function resolveReference(mixed $reference): mixed {
		if ($reference instanceof WeakReference) {
			$object = $reference->get();

			if (is_null($object)) {
				throw new DependencyException('Referenced object has already been destroyed.');
			}

			return $this->resolveReference($object);
		} else {
			return $reference;
		}
	}
}
