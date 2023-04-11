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

use Psr\Container\ContainerExceptionInterface;

/**
 * Value Container
 */
class ValueContainer extends DefinitionContainer {

	/**
	 * Create a new container.
	 *
	 * @param array ...$values Key/value pairs to add to container.
	 *
	 * @throws ContainerExceptionInterface Error occurred during creation.
	 */
	public function __construct(array ...$values) {
		parent::__construct(...$values);
	}
}
