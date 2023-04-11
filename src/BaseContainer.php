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
use ShineUnited\Contextual\Exception\ContainerException;
use Psr\Container\ContainerInterface;

/**
 * Abstract Base Container
 */
abstract class BaseContainer implements ContainerInterface {

	/**
	 * {@inheritDoc}
	 */
	public function has(string $id): bool {
		if ($id == ContainerInterface::class) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id): mixed {
		if ($id == ContainerInterface::class) {
			return $this;
		}

		throw new EntryNotFoundException(sprintf(
			'Entry for identifier "%s" not found in container.',
			$id
		));
	}
}
