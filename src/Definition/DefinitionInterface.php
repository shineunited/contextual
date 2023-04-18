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

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Definition Interface
 */
interface DefinitionInterface {

	/**
	 * Get the definition identifier.
	 *
	 * @return string The definition identifier.
	 */
	public function getIdentifier(): string;

	/**
	 * Check if definition is a decorator.
	 *
	 * Decorator definitions modify other definitions and properties.
	 *
	 * @return boolean True if definition is a decorator.
	 */
	public function isDecorator(): bool;

	/**
	 * Check if definition is protected.
	 *
	 * Protected definitions disallow other definitions of the same id.
	 *
	 * @return boolean True if definition is protected.
	 */
	public function isProtected(): bool;

	/**
	 * Check if definition is an alias.
	 *
	 * Used by containers to determine if a WeakReference should be used to avoid unnecessary references.
	 *
	 * @return boolean True if definition returns an alias.
	 */
	public function isAlias(): bool;

	/**
	 * Check if definition is resolvable in provided container.
	 *
	 * @param ContainerInterface $container The container to resolve in.
	 *
	 * @return boolean True if definition is resolvable.
	 */
	public function isResolvable(ContainerInterface $container): bool;

	/**
	 * Resolve the definition value.
	 *
	 * @param ContainerInterface $container The container to resolve in.
	 *
	 * @throws ContainerExceptionInterface Error while resolving definition.
	 *
	 * @return mixed The resolved value.
	 */
	public function resolve(ContainerInterface $container): mixed;

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string;
}
