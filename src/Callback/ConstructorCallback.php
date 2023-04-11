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

use ShineUnited\Contextual\Exception\InvalidCallbackException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Constructor Callback
 */
class ConstructorCallback extends ReflectionCallback {
	private string $class;

	/**
	 * Create a new callback.
	 *
	 * @param string $class The classname.
	 *
	 * @throws InvalidCallbackException Invalid class.
	 */
	public function __construct(string $class) {
		$this->class = $class;

		try {
			$reflection = new ReflectionClass($class);
		} catch (ReflectionException $exception) {
			throw new InvalidCallbackException(sprintf(
				'Invalid constructor callback: {class: "%s"}.',
				$class
			), 0, $exception);
		}

		parent::__construct($reflection);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(ContainerInterface $container, array $arguments = []): mixed {
		$parameters = $this->resolveParameters($container, $arguments);

		return new $this->class(...$parameters);
	}
}
