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

use ShineUnited\Contextual\Exception\DefinitionNotFoundException;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;

/**
 * Composite Definition Source
 */
class CompositeDefinitionSource implements DefinitionSourceInterface {
	private array $sources = [];

	/**
	 * Create a new definition source collection.
	 *
	 * @param DefinitionSourceInterface ...$sources Definition sources to add.
	 */
	public function __construct(DefinitionSourceInterface ...$sources) {
		$this->addDefinitionSource(new DefinitionSource());

		foreach ($sources as $source) {
			$this->addDefinitionSource($source);
		}
	}

	/**
	 * Add a definition source.
	 *
	 * @param DefinitionSourceInterface $source The definition source.
	 *
	 * @return void
	 */
	public function addDefinitionSource(DefinitionSourceInterface $source): void {
		$this->sources[] = $source;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDefinitions(DefinitionInterface ...$definitions): void {
		$this->sources[0]->addDefinitions(...$definitions);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDefinition(DefinitionInterface $definition): void {
		$this->sources[0]->addDefinition($definition);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasDefinition(string $id): bool {
		foreach ($this->sources as $source) {
			if ($source->hasDefinition($id)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefinition(string $id): DefinitionInterface {
		$primary = null;
		$decorators = [];
		foreach ($this->sources as $source) {
			if ($source->hasDefinition($id)) {
				$definition = $source->getDefinition($id);
				if ($definition->isDecorator()) {
					$decorators[] = $definition;
				} elseif (is_null($primary)) {
					$primary = $definition;
				} else {
					throw new InvalidDefinitionException(sprintf(
						'Multiple independent definitions exist for id "%s".',
						$id
					));
				}
			}
		}

		if (!$primary instanceof DefinitionInterface) {
			if (empty($decorators)) {
				throw new DefinitionNotFoundException(sprintf(
					'No definition found for id "%s".',
					$id
				));
			}

			$primary = array_shift($decorators);
		}

		if (empty($decorators)) {
			return $primary;
		}

		return CompositeDefinition::merge($primary, ...$decorators);
	}
}
