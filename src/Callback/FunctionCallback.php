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
use ReflectionException;
use ReflectionFunction;

/**
 * Function Callback
 */
class FunctionCallback extends ReflectionCallback {
	private string $function;

	/**
	 * Create a new callback.
	 *
	 * @param string $function The function name.
	 *
	 * @throws InvalidCallbackException Invalid function.
	 */
	public function __construct(string $function) {
		$this->function = $function;

		try {
			$reflection = new ReflectionFunction($function);
		} catch (ReflectionException $exception) {
			throw new InvalidCallbackException(sprintf(
				'Invalid function callback: {function: "%s"}.',
				$function
			), 0, $exception);
		}

		parent::__construct($reflection);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(ContainerInterface $container, array $arguments = []): mixed {
		return call_user_func_array(
			$this->function,
			$this->resolveParameters($container, $arguments)
		);
	}
}
