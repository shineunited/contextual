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

use ShineUnited\Contextual\Callback\CallbackInterface;
use Psr\Container\ContainerInterface;

/**
 * Callback Definition
 */
class CallbackDefinition implements DefinitionInterface {
	private string $id;
	private CallbackInterface $callback;
	private bool $protected;

	/**
	 * Create a new definition.
	 *
	 * @param string            $id       The definition identifier.
	 * @param CallbackInterface $callback The callback.
	 * @param boolean           $protect  Protect this definition.
	 */
	public function __construct(string $id, CallbackInterface $callback, bool $protect = false) {
		$this->id = $id;
		$this->callback = $callback;
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
		return $this->callback->isReflexive();
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
	public function isAlias(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		return $this->callback->canResolveParameters($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		return $this->callback->execute($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		return sprintf(
			'callback "%s": %s',
			$this->id,
			(string) $this->callback
		);
	}
}
