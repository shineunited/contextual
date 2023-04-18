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

use ShineUnited\Contextual\Exception\DependencyException;
use Psr\Container\ContainerInterface;

/**
 * Protected Definition
 */
class ProtectedDefinition implements DefinitionInterface {
	private DefinitionInterface $definition;

	/**
	 * Create a new definition.
	 *
	 * @param DefinitionInterface $definition The definition to protect.
	 */
	public function __construct(DefinitionInterface $definition) {
		$this->definition = $definition;
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
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAlias(): bool {
		return $this->definition->isAlias();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		return $this->definition->isResolvable($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		return $this->definition->resolve($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return sprintf(
			'(protected) %s',
			$this->definition->__toString()
		);
	}
}
