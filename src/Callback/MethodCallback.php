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
use ReflectionMethod;
use WeakReference;

/**
 * Method Callback
 */
class MethodCallback extends ReflectionCallback {
	private WeakReference $reference;
	private string $method;
	private bool $static;

	/**
	 * Create a new callback.
	 *
	 * @param object $object The object.
	 * @param string $method The method name.
	 * @param string $class  Optional parent class to use for resolution.
	 *
	 * @throws InvalidCallbackException Invalid method.
	 */
	public function __construct(object $object, string $method, ?string $class = null) {
		$this->reference = WeakReference::create($object);
		$this->method = $method;

		if (is_null($class)) {
			$class = $object::class;
		} elseif (!is_a($object, $class)) {
			throw new InvalidCallbackException(sprintf(
				'Object class "%s" is not an instance of "%s".',
				$object::class,
				$class
			));
		}

		try {
			$reflection = new ReflectionMethod($class, $method);
		} catch (ReflectionException $exception) {
			throw new InvalidCallbackException(sprintf(
				'Invalid method callback: {class: "%s", method: "%s"}.',
				$class,
				$method
			), 0, $exception);
		}

		$this->static = $reflection->isStatic();

		parent::__construct($reflection);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute(ContainerInterface $container, array $arguments = []): mixed {
		$object = $this->reference->get();

		if ($this->static) {
			return call_user_func_array(
				[$object::class, $this->method],
				$this->resolveParameters($container, $arguments)
			);
		} else {
			return call_user_func_array(
				[$object, $this->method],
				$this->resolveParameters($container, $arguments)
			);
		}
	}
}
