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
use ShineUnited\Contextual\Definition\AliasDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use Psr\Container\ContainerExceptionInterface;
use Generator;

/**
 * Alias Definition Test
 */
class AliasDefinitionTest extends TestCase {


	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testConstructor(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertInstanceOf(AliasDefinition::class, $definition);
		$this->assertInstanceOf(DefinitionInterface::class, $definition);
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testGetIdentifier(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertSame($id, $definition->getIdentifier());
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testIsDecorator(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertFalse($definition->isDecorator());
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testIsProtected(string $id, string $alias): void {
		$defaultDefinition = new AliasDefinition($id, $alias);
		$this->assertFalse($defaultDefinition->isProtected());

		$protectedDefinition = new AliasDefinition($id, $alias, true);
		$this->assertTrue($protectedDefinition->isProtected());

		$unprotectedDefinition = new AliasDefinition($id, $alias, false);
		$this->assertFalse($unprotectedDefinition->isProtected());
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testIsAlias(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertTrue($definition->isAlias());
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testIsResolvable(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertTrue(
			$definition->isResolvable(
				$this->mockContainer([
					$alias => 'value'
				])
			)
		);

		$this->assertFalse(
			$definition->isResolvable(
				$this->mockContainer([])
			)
		);
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @throws ContainerExceptionInterface On error.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testResolve(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		// test with string value
		$value = 'value';

		$this->assertSame(
			$value,
			$definition->resolve(
				$this->mockContainer([
					$alias => $value
				])
			)
		);

		// test with object value
		$value = new \stdClass();

		$this->assertSame(
			$value,
			$definition->resolve(
				$this->mockContainer([
					$alias => $value
				])
			)
		);
	}

	/**
	 * @param string $id    The identifier.
	 * @param string $alias The alias.
	 *
	 * @return void
	 *
	 * @dataProvider aliasDefinitionDataProvider
	 */
	public function testToString(string $id, string $alias): void {
		$definition = new AliasDefinition($id, $alias);

		$this->assertIsString($definition->__toString());
	}

	/**
	 * @return Generator
	 */
	public function aliasDefinitionDataProvider(): Generator {
		yield 'id1 -> alias1' => [
			'id1',
			'alias1'
		];

		yield 'id2 : alias2' => [
			'id2',
			'alias2'
		];

		yield 'id3 : alias3' => [
			'id3',
			'alias3'
		];
	}
}
