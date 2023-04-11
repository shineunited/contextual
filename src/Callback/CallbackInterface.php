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

namespace ShineUnited\Contextual\Callback;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Exception;

/**
 * Callback Interface
 */
interface CallbackInterface {

	/**
	 * Check if callback is a property.
	 *
	 * @return boolean True if callback is a property.
	 */
	public function isProperty(): bool;

	/**
	 * Get the callback property name.
	 *
	 * @return ?string The property name or null if not defined.
	 */
	public function getPropertyName(): ?string;

	/**
	 * Check if callback is a function.
	 *
	 * @return boolean True if callback is a function.
	 */
	public function isFunction(): bool;

	/**
	 * Get the callback function name.
	 *
	 * @return ?string The function name or null if not defined.
	 */
	public function getFunctionName(): ?string;

	/**
	 * Check if callback has a type class.
	 *
	 * @return boolean True if callback has a type class.
	 */
	public function hasClass(): bool;

	/**
	 * Get the callback type class.
	 *
	 * @return ?string The callback type class or null if not defined.
	 */
	public function getClass(): ?string;

	/**
	 * Check if the callback is reflexive (uses itself).
	 *
	 * @return boolean True if callback is reflexive.
	 */
	public function isReflexive(): bool;

	/**
	 * Get attributes for this callback.
	 *
	 * @param string $class Optional class name to restrict to.
	 *
	 * @return iterable A list of attribute instance.
	 */
	public function getAttributes(?string $class = null): iterable;

	/**
	 * Check if callback parameters are resolvable with provided context.
	 *
	 * @param ContainerInterface $container The container to resolve in.
	 * @param array              $arguments Additional arguments to use.
	 *
	 * @return boolean True if parameters can be resolved.
	 */
	public function canResolveParameters(ContainerInterface $container, array $arguments = []): bool;

	/**
	 * Resolve the callback parameters using the provided container.
	 *
	 * @param ContainerInterface $container The container to resolve in.
	 * @param array              $arguments Additional arguments to use.
	 *
	 * @throws ContainerExceptionInterface If a parameter is unable to be resolved.
	 *
	 * @return array An array of resolved arguments.
	 */
	public function resolveParameters(ContainerInterface $container, array $arguments = []): array;

	/**
	 * Execute the callback.
	 *
	 * @param ContainerInterface $container The container to execute in.
	 * @param array              $arguments Additional arguments to use.
	 *
	 * @throws ContainerExceptionInterface Problem resolving callback dependencies.
	 *
	 * @return mixed The result.
	 */
	public function execute(ContainerInterface $container, array $arguments = []): mixed;

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string;
}
