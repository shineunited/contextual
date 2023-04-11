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

namespace ShineUnited\Contextual\Definition;

use ShineUnited\Contextual\CompositeContainer;
use ShineUnited\Contextual\ValueContainer;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;
use Psr\Container\ContainerInterface;

/**
 * Composite Definition
 */
class CompositeDefinition implements DefinitionInterface {
	private DefinitionInterface $definition;
	private array $decorators = [];

	/**
	 * Create a new definition.
	 *
	 * @param DefinitionInterface $definition    The primary definition.
	 * @param DefinitionInterface ...$decorators Additional decorators.
	 *
	 * @throws InvalidDefinitionException Error occurred during creation.
	 */
	public function __construct(DefinitionInterface $definition, DefinitionInterface ...$decorators) {
		$this->definition = $definition;
		foreach ($decorators as $decorator) {
			$this->addDecorator($decorator);
		}
	}

	/**
	 * Merge 2 or more definitions.
	 *
	 * @param DefinitionInterface $definition1    The first definition.
	 * @param DefinitionInterface $definition2    The second definition.
	 * @param DefinitionInterface ...$definitions Additional definitions.
	 *
	 * @throws InvalidDefinitionException Error while merging definitions.
	 *
	 * @return DefinitionInterface The merged definition.
	 */
	public static function merge(DefinitionInterface $definition1, DefinitionInterface $definition2, DefinitionInterface ...$definitions): DefinitionInterface {
		array_unshift($definitions, $definition1, $definition2);

		$primary = null;
		$decorators = [];
		foreach ($definitions as $definition) {
			if ($definition instanceof self) {
				if (!$definition->definition->isDecorator() && is_null($primary)) {
					$primary = $definition->definition;
				} else {
					$decorators = $definition->definition;
				}

				foreach ($definition->decorators as $decorator) {
					$decorators[] = $decorator;
				}
			} elseif (!$definition->isDecorator() && is_null($primary)) {
				$primary = $definition;
			} else {
				$decorators[] = $definition;
			}
		}

		if (is_null($primary)) {
			$primary = array_shift($decorators);
		}

		return new self($primary, ...$decorators);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string {
		return $this->definition->getIdentifier();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDecorator(): bool {
		return $this->definition->isDecorator();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isProtected(): bool {
		return $this->definition->isProtected();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		if (!$this->definition->isResolvable($container)) {
			return false;
		}

		foreach ($this->decorators as $decorator) {
			if (!$decorator->isResolvable($container)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		$value = $this->definition->resolve($container);

		foreach ($this->decorators as $decorator) {
			$container = new CompositeContainer(
				$container,
				new ValueContainer([
					$this->getIdentifier() => $value
				])
			);

			$value = $decorator->resolve($container);
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return sprintf(
			'composite "%s"',
			$this->getIdentifier()
		);
	}

	/**
	 * Add a decorator.
	 *
	 * @param DefinitionInterface $decorator The decorator to add.
	 *
	 * @throws InvalidDefinitionException If decorator is invalid or ids do not match.
	 *
	 * @return void
	 */
	public function addDecorator(DefinitionInterface $decorator): void {
		if (!$decorator->isDecorator()) {
			throw new InvalidDefinitionException(sprintf(
				'Provided definition "%s" is not a decorator.',
				$decorator->getIdentifier()
			));
		}

		if ($this->getIdentifier() != $decorator->getIdentifier()) {
			throw new InvalidDefinitionException(sprintf(
				'Provided decorator id "%s" does not match "%s"',
				$decorator->getIdentifier(),
				$this->getIdentifier()
			));
		}

		if ($this->isProtected()) {
			throw new InvalidDefinitionException(sprintf(
				'Cannot add decorator "%s" to protected definition.',
				$decorator->getIdentifier()
			));
		}

		if ($decorator->isProtected()) {
			throw new InvalidDefinitionException(sprintf(
				'Protected definition "%s" cannot be added to decorators.',
				$decorator->getIdentifier()
			));
		}

		$this->decorators[] = $decorator;
	}

	/**
	 * Get the primary definition.
	 *
	 * @return DefinitionInterface The primary definition.
	 */
	public function getDefinition(): DefinitionInterface {
		return $this->definition;
	}

	/**
	 * List definition decorators.
	 *
	 * @return iterable A list of decorators.
	 */
	public function listDecorators(): iterable {
		return $this->decorators;
	}
}
