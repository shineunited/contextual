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
use ShineUnited\Contextual\Definition\ValueDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use Psr\Container\ContainerExceptionInterface;
use Generator;
use stdClass;

/**
 * Value Definition Test
 */
class ValueDefinitionTest extends TestCase {


	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testConstructor(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertInstanceOf(ValueDefinition::class, $definition);
		$this->assertInstanceOf(DefinitionInterface::class, $definition);
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testGetIdentifier(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertSame($id, $definition->getIdentifier());
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testIsDecorator(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertFalse($definition->isDecorator());
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testIsProtected(string $id, mixed $value): void {
		$defaultDefinition = new ValueDefinition($id, $value);
		$this->assertFalse($defaultDefinition->isProtected());

		$protectedDefinition = new ValueDefinition($id, $value, true);
		$this->assertTrue($protectedDefinition->isProtected());

		$unprotectedDefinition = new ValueDefinition($id, $value, false);
		$this->assertFalse($unprotectedDefinition->isProtected());
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testIsAlias(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertTrue($definition->isAlias());
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testIsResolvable(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertTrue(
			$definition->isResolvable(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @throws ContainerExceptionInterface On error.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testResolve(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertSame(
			$value,
			$definition->resolve(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @param string $id    The identifier.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 *
	 * @dataProvider valueDefinitionDataProvider
	 */
	public function testToString(string $id, mixed $value): void {
		$definition = new ValueDefinition($id, $value);

		$this->assertIsString($definition->__toString());
	}

	/**
	 * @return Generator
	 */
	public function valueDefinitionDataProvider(): Generator {
		yield '(value) string' => [
			'string',
			'string value'
		];

		yield '(value) zero' => [
			'zero',
			0
		];

		yield '(value) boolean true' => [
			'true',
			true
		];

		yield '(value) boolean false' => [
			'false',
			false
		];

		yield '(value) null' => [
			'null',
			null
		];

		yield '(value) positive integer' => [
			'positive',
			12345
		];

		yield '(value) negative integer' => [
			'negative',
			-12345
		];

		yield '(value) object' => [
			'object',
			new stdClass()
		];
	}
}
