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

namespace ShineUnited\Contextual\Tests\Definition;

use ShineUnited\Contextual\Tests\TestCase;
use ShineUnited\Contextual\Definition\ReferenceDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use Psr\Container\ContainerExceptionInterface;
use Generator;
use stdClass;

/**
 * Reference Definition Test
 */
class ReferenceDefinitionTest extends TestCase {

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testConstructor(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertInstanceOf(ReferenceDefinition::class, $definition);
		$this->assertInstanceOf(DefinitionInterface::class, $definition);
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testGetIdentifier(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertSame($id, $definition->getIdentifier());
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testIsDecorator(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertFalse($definition->isDecorator());
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testIsProtected(string $id, object $object): void {
		$defaultDefinition = new ReferenceDefinition($id, $object);
		$this->assertFalse($defaultDefinition->isProtected());

		$protectedDefinition = new ReferenceDefinition($id, $object, true);
		$this->assertTrue($protectedDefinition->isProtected());

		$unprotectedDefinition = new ReferenceDefinition($id, $object, false);
		$this->assertFalse($unprotectedDefinition->isProtected());
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testIsAlias(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertTrue($definition->isAlias());
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testIsResolvable(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertTrue(
			$definition->isResolvable(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @return void
	 */
	public function testIsResolvablePostDestruction(): void {
		$object = new stdClass();

		$definition = new ReferenceDefinition('reference', $object);

		// destroy original reference
		unset($object);

		$this->assertFalse(
			$definition->isResolvable(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @throws ContainerExceptionInterface On error.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testResolve(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertSame(
			$object,
			$definition->resolve(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @throws ContainerExceptionInterface On error.
	 *
	 * @return void
	 */
	public function testResolvePostDestruction(): void {
		$object = new stdClass();

		$definition = new ReferenceDefinition('reference', $object);

		// destroy original reference
		unset($object);

		$this->expectException(ContainerExceptionInterface::class);
		$definition->resolve(
			$this->mockContainer([])
		);
	}

	/**
	 * @param string $id     The identifier.
	 * @param object $object The object.
	 *
	 * @return void
	 *
	 * @dataProvider referenceDefinitionDataProvider
	 */
	public function testToString(string $id, object $object): void {
		$definition = new ReferenceDefinition($id, $object);

		$this->assertIsString($definition->__toString());
	}

	/**
	 * @return Generator
	 */
	public function referenceDefinitionDataProvider(): Generator {
		yield '(reference) object1' => [
			'object1',
			new stdClass()
		];

		yield '(reference) object2' => [
			'object2',
			new stdClass()
		];
	}
}
