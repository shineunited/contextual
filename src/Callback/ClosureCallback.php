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
use Closure;
use ReflectionException;
use ReflectionFunction;

/**
 * Closure Callback
 */
class ClosureCallback extends ReflectionCallback {
	private Closure $closure;

	/**
	 * Create a new callback.
	 *
	 * @param Closure $closure The closure.
	 *
	 * @throws InvalidCallbackException Invalid closure.
	 */
	public function __construct(Closure $closure) {
		$this->closure = $closure;

		try {
			$reflection = new ReflectionFunction($closure);
		} catch (ReflectionException $exception) {
			throw new InvalidCallbackException('Invalid closure callback.', 0, $exception);
		}

		parent::__construct($reflection);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(ContainerInterface $container, array $arguments = []): mixed {
		return call_user_func_array(
			$this->closure,
			$this->resolveParameters($container, $arguments)
		);
	}
}
