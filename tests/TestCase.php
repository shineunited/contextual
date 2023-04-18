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

namespace ShineUnited\Contextual\Tests;

use ShineUnited\Contextual\Callback\CallbackInterface;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\Stub\Stub;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 * Base Test Case
 */
abstract class TestCase extends BaseTestCase {

	/**
	 * @return void
	 */
	protected function toDo(): void {
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
		self::markTestIncomplete(sprintf('To-Do: %s::%s', $caller['class'], $caller['function']));
	}

	/**
	 * Create a mock container.
	 *
	 * @param array $values Key/value pairs for the container.
	 *
	 * @return ContainerInterface The container.
	 */
	protected function mockContainer(array $values = []): ContainerInterface {
		return new class ($values) implements ContainerInterface {
			private array $values;

			/**
			 * Create a new container.
			 *
			 * @param array $values Key/value pairs for container.
			 */
			public function __construct(array $values) {
				$this->values = $values;
			}

			/**
			 * {@inheritDoc}
			 */
			public function has(string $id): bool {
				if (isset($this->values[$id])) {
					return true;
				}

				if (array_key_exists($id, $this->values)) {
					return true;
				}

				return false;
			}

			/**
			 * {@inheritDoc}
			 */
			public function get(string $id): mixed {
				if (!array_key_exists($id, $this->values)) {
					throw new class ($id) extends Exception implements NotFoundExceptionInterface {

						/**
						 * Create new exception.
						 *
						 * @param string $id The missing identifier.
						 */
						public function __construct(string $id) {
							parent::__construct(sprintf(
								'Identifier "%s" not found in container.',
								$id
							));
						}
					};
				}

				return $this->values[$id];
			}
		};
	}

	/**
	 * Create a simple mock object.
	 *
	 * @param string $class        The classname to mock.
	 * @param Stub   ...$functions Function name/stub pairs.
	 *
	 * @return object The object.
	 */
	protected function mockObject(string $class, Stub ...$functions): object {
		$object = $this->createMock($class);

		foreach($functions as $name => $stub) {
			$object
				->expects($this->any())
				->method($name)
				->will($stub)
			;
		}

		return $object;
	}

	/**
	 * Create a mock callback.
	 *
	 * @param Stub ...$functions Function name/stub pairs.
	 *
	 * @return CallbackInterface The callback.
	 */
	protected function mockCallback(Stub ...$functions): CallbackInterface {
		return $this->mockObject(
			CallbackInterface::class,
			...$functions
		);
	}

	/**
	 * Create a mock definition.
	 *
	 * @param Stub ...$functions Function name/stub pairs.
	 *
	 * @return DefinitionInterface The definition.
	 */
	protected function mockDefinition(Stub ...$functions): DefinitionInterface {
		return $this->mockObject(
			DefinitionInterface::class,
			...$functions
		);
	}
}
