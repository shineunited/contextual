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
use ReflectionProperty;
use WeakReference;

/**
 * Property Callback
 */
class PropertyCallback extends ReflectionCallback {
	private WeakReference $reference;
	private string $property;
	private bool $static;

	/**
	 * Create a new callback.
	 *
	 * @param object $object   The object.
	 * @param string $property The property name.
	 * @param string $class    Optional parent class to use for resolution.
	 *
	 * @throws InvalidCallbackException Invalid property.
	 */
	public function __construct(object $object, string $property, ?string $class = null) {
		$this->reference = WeakReference::create($object);
		$this->property = $property;

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
			$reflection = new ReflectionProperty($class, $property);
		} catch (ReflectionException $exception) {
			throw new InvalidCallbackException(sprintf(
				'Invalid property callback: {class: "%s", property: "%s"}.',
				$class,
				$property
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
			return $object::${$this->property};
		} else {
			return $object->{$this->property};
		}
	}
}
