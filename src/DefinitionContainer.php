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
use ShineUnited\Contextual\Definition\Source\CompositeDefinitionSource;
use ShineUnited\Contextual\Definition\Source\DefinitionSourceInterface;
use ShineUnited\Contextual\Exception\EntryNotFoundException;
use ShineUnited\Contextual\Exception\DependencyException;
use ShineUnited\Contextual\Exception\DefinitionNotFoundException;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Closure;
use WeakReference;

/**
 * Definition Container
 */
class DefinitionContainer extends BaseContainer {
	private CompositeDefinitionSource $definitions;

	private EmptyContainer $parentContainer;

	private array $resolvedValues = [];
	private array $resolvedReferences = [];
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

		$this->parentContainer = new EmptyContainer();

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
	 * {@inheritDoc}
	 */
	public function has(string $id): bool {
		if (parent::has($id)) {
			return true;
		}

		try {
			$definition = $this->definitions->getDefinition($id);

			$container = $this->getDefinitionContainer($definition);

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

		$parent = $this->getParentContainer();
		return $parent->has($id);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id): mixed {
		if (parent::has($id)) {
			return parent::get($id);
		}

		if (isset($this->resolvedValues[$id]) || array_key_exists($id, $this->resolvedValues)) {
			return $this->resolvedValues[$id];
		}

		if (isset($this->resolvedReferences[$id])) {
			$object = $this->resolvedReferences[$id]->get();

			if (is_null($object)) {
				// object has gone away, unset and allow it to regenerate
				// TODO: should this throw an exception?
				unset($this->resolvedReferences[$id]);
			} else {
				return $object;
			}
		}

		if ($this->definitions->hasDefinition($id)) {
			$definition = $this->definitions->getDefinition($id);
			$value = $this->resolveDefinition($definition);

			if (is_object($value) && $definition->isAlias()) {
				$this->resolvedReferences[$id] = WeakReference::create($value);
			} else {
				$this->resolvedValues[$id] = $value;
			}

			return $value;
		}

		$parent = $this->getParentContainer();
		return $parent->get($id);
	}

	/**
	 * Get the parent container. If there is not a parent container return self.
	 *
	 * @return ContainerInterface The parent container.
	 */
	protected function getParentContainer(): ContainerInterface {
		return $this->parentContainer;
	}

	private function getDefinitionContainer(DefinitionInterface $definition): ContainerInterface {
		if (!$definition->isDecorator()) {
			return $this;
		}

		try {
			$parent = $this->getParentContainer();
			$value = $parent->get($definition->getIdentifier());

			return new CompositeContainer(
				new ValueContainer([
					$definition->getIdentifier() => $value
				]),
				$this
			);
		} catch (ContainerExceptionInterface $exception) {
			return $this;
		}
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
	protected function resolveDefinition(DefinitionInterface $definition): mixed {
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
			$container = $this->getDefinitionContainer($definition);

			$value = $definition->resolve($container);
		} finally {
			unset($this->currentlyResolving[$definition->getIdentifier()]);
			array_pop($this->resolverStack);
		}

		return $value;
	}
}
