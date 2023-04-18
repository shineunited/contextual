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

use ShineUnited\Contextual\Exception\EntryNotFoundException;
use ShineUnited\Contextual\Exception\DependencyException;
use Psr\Container\ContainerExceptionInterface;
use WeakReference;

/**
 * Value Container
 */
class ValueContainer extends BaseContainer {
	private array $values;

	/**
	 * Create a new container.
	 *
	 * @param array ...$arrays Key/value pairs to add to container.
	 */
	public function __construct(array ...$arrays) {
		$this->values = array_merge(...$arrays);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $id): bool {
		if (parent::has($id)) {
			return true;
		}

		if (isset($this->values[$id])) {
			return true;
		}

		if (array_key_exists($id, $this->values)) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id): mixed {
		if (parent::has($id)) {
			return parent::get($id);
		}

		if (!isset($this->values[$id]) && !array_key_exists($id, $this->values)) {
			throw new EntryNotFoundException(sprintf(
				'Identifier "%s" not found in container.',
				$id
			));
		}

		return $this->resolveReference($this->values[$id]);
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
	protected function resolveReference(mixed $reference): mixed {
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
