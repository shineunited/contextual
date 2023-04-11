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
use Psr\Container\ContainerInterface;

/**
 * Composite Container
 */
class CompositeContainer extends BaseContainer {
	private array $containers = [];

	/**
	 * Create a new container.
	 *
	 * @param ContainerInterface ...$containers Containers to add to container.
	 */
	public function __construct(ContainerInterface ...$containers) {
		$this->containers = $containers;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $id): bool {
		if (parent::has($id)) {
			return true;
		}

		if (!isset($this->containers)) {
			return false;
		}

		foreach ($this->containers as $container) {
			if (!$container instanceof ContainerInterface) {
				continue;
			}

			if ($container->has($id)) {
				return true;
			}
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

		foreach ($this->containers as $container) {
			if (!$container instanceof ContainerInterface) {
				continue;
			}

			if ($container->has($id)) {
				return $container->get($id);
			}
		}

		throw new EntryNotFoundException(sprintf(
			'Identifier "%s" not found in container.',
			$id
		));
	}
}
