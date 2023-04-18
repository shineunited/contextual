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
use WeakReference;

/**
 * Reference Definition
 */
class ReferenceDefinition implements DefinitionInterface {
	private string $id;
	private WeakReference $reference;
	private bool $protected;

	/**
	 * Create a new definition.
	 *
	 * @param string  $id      The definition identifier.
	 * @param object  $object  The object to reference.
	 * @param boolean $protect Protect this definition.
	 */
	public function __construct(string $id, object $object, bool $protect = false) {
		$this->id = $id;
		$this->reference = WeakReference::create($object);
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
	public function isAlias(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isResolvable(ContainerInterface $container): bool {
		if (is_null($this->reference->get())) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(ContainerInterface $container): mixed {
		$value = $this->reference->get();
		if (is_null($value)) {
			throw new DependencyException(sprintf(
				'Referenced object "%s" has already been destroyed.',
				$this->getIdentifier()
			));
		}

		return $this->reference->get();
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string {
		$type = gettype($this->reference->get());

		return sprintf(
			'reference "%s", type "%s"',
			$this->id,
			$type
		);
	}
}
