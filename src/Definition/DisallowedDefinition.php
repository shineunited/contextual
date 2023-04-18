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
 * Disallowed Definition
 */
class DisallowedDefinition implements DefinitionInterface {
	private string $id;

	/**
	 * Create a new definition.
	 *
	 * @param string $id The definition identifier.
	 */
	public function __construct(string $id) {
		$this->id = $id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string {
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDecorator(): bool {
		return false;
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
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		throw new DependencyException(sprintf(
			'Identifier "%s" is disallowed in this container.',
			$this->getIdentifier()
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return sprintf(
			'disallow "%s"',
			$this->id
		);
	}
}
