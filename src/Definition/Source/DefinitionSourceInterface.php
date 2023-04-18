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

use ShineUnited\Contextual\Definition\DefinitionInterface;
use ShineUnited\Contextual\Exception\DefinitionNotFoundException;
use ShineUnited\Contextual\Exception\InvalidDefinitionException;

/**
 * Definition Source Interface
 */
interface DefinitionSourceInterface {

	/**
	 * Add multiple definitions.
	 *
	 * @param DefinitionInterface ...$definitions Definitions to add.
	 *
	 * @throws InvalidDefinitionException Error while adding definitions.
	 *
	 * @return void
	 */
	public function addDefinitions(DefinitionInterface ...$definitions): void;

	/**
	 * Add a definition.
	 *
	 * @param DefinitionInterface $definition The definition.
	 *
	 * @throws InvalidDefinitionException Error while adding definition.
	 *
	 * @return void
	 */
	public function addDefinition(DefinitionInterface $definition): void;

	/**
	 * Check if definition exists for id.
	 *
	 * @param string $id The id to check.
	 *
	 * @return boolean True if definition exists.
	 */
	public function hasDefinition(string $id): bool;

	/**
	 * Get the defintion for an id.
	 *
	 * @param string $id The definition id.
	 *
	 * @throws DefinitionNotFoundException No definition found for identifier.
	 * @throws InvalidDefinitionException  Error while retrieving definition.
	 *
	 * @return DefinitionInterface The definition.
	 */
	public function getDefinition(string $id): DefinitionInterface;
}
