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

use ShineUnited\Contextual\Definition\DefinitionSourceInterface;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use ShineUnited\Contextual\Exception\EntryNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Hybrid Container
 */
class HybridContainer extends DefinitionContainer {
	private CompositeContainer $parents;

	/**
	 * Create a new container.
	 *
	 * @param ContainerInterface|DefinitionSourceInterface|DefinitionInterface|array ...$inherits Containers and definitions to add to container.
	 *
	 * @throws ContainerExceptionInterface Error occurred during creation.
	 */
	public function __construct(ContainerInterface|DefinitionSourceInterface|DefinitionInterface|array ...$inherits) {
		parent::__construct();

		$containers = [];
		$definitions = [];

		foreach ($inherits as $inherit) {
			if ($inherit instanceof ContainerInterface) {
				$containers[] = $inherit;
			} else {
				$definitions[] = $inherit;
			}
		}

		$this->parents = new CompositeContainer(...$containers);

		parent::__construct(...$definitions);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function hasParentContainer(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getParentContainer(): ContainerInterface {
		return $this->parents;
	}
}
