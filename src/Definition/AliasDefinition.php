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

use Psr\Container\ContainerInterface;

/**
 * Alias Definition
 */
class AliasDefinition implements DefinitionInterface {
	private string $id;
	private string $alias;
	private bool $protected;

	/**
	 * Create a new definition.
	 *
	 * @param string  $id      The definition identifier.
	 * @param string  $alias   The aliased identifier.
	 * @param boolean $protect Protect this definition.
	 */
	public function __construct(string $id, string $alias, bool $protect = false) {
		$this->id = $id;
		$this->alias = $alias;
		$this->protected = $protect;
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
		return $this->protected;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		if ($container->has($this->alias)) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		return $container->get($this->alias);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return sprintf(
			'alias "%s" to "%s".',
			$this->id,
			$this->alias
		);
	}
}
