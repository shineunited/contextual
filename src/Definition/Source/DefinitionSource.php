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

namespace ShineUnited\Contextual\Definition\Source;

use ShineUnited\Contextual\Definition\CompositeDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use ShineUnited\Contextual\Exception\DefinitionNotFoundException;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;

/**
 * Definition Source
 */
class DefinitionSource implements DefinitionSourceInterface {
	private array $definitions = [];

	/**
	 * {@inheritDoc}
	 */
	public function addDefinitions(DefinitionInterface ...$definitions): void {
		foreach ($definitions as $definition) {
			$this->addDefinition($definition);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDefinition(DefinitionInterface $definition): void {
		$id = $definition->getIdentifier();

		if (!$this->hasDefinition($id)) {
			$this->definitions[$id] = $definition;
			return;
		}

		$existingDefinition = $this->definitions[$id];

		if ($existingDefinition->isProtected()) {
			throw new InvalidDefinitionException(sprintf(
				'Protected definition exists for id "%s".',
				$id
			));
		}


		if (!$definition->isDecorator() && !$existingDefinition->isDecorator()) {
			throw new InvalidDefinitionException(sprintf(
				'Definition already exists for id "%s".',
				$id
			));
		}

		$this->definitions[$id] = CompositeDefinition::merge(
			$existingDefinition,
			$definition
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasDefinition(string $id): bool {
		if (isset($this->definitions[$id])) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefinition(string $id): DefinitionInterface {
		if (!$this->hasDefinition($id)) {
			throw new DefinitionNotFoundException(sprintf(
				'No definition found for id "%s".',
				$id
			));
		}

		return $this->definitions[$id];
	}
}
