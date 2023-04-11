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
use WeakReference;

/**
 * Value Definition
 */
class ValueDefinition implements DefinitionInterface {
	private string $id;
	private mixed $value;
	private bool $protected;

	/**
	 * Create a new definition.
	 *
	 * @param string  $id      The definition identifier.
	 * @param mixed   $value   The value.
	 * @param boolean $protect Protect this definition.
	 */
	public function __construct(string $id, mixed $value, bool $protect = false) {
		$this->id = $id;
		$this->value = $value;
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
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		if (!is_object($this->value)) {
			return $this->value;
		}

		if ($this->value instanceof WeakReference) {
			return $this->value;
		}

		return WeakReference::create($this->value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		$type = gettype($this->value);
		if ($this->value instanceof WeakReference) {
			$type = gettype($this->value->get());
		}

		return sprintf(
			'value "%s", type "%s"',
			$this->id,
			$type
		);
	}
}
