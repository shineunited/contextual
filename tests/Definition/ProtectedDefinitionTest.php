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
use ShineUnited\Contextual\Definition\ProtectedDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use Psr\Container\ContainerExceptionInterface;
use Generator;
use stdClass;

/**
 * Protected Definition Test
 */
class ProtectedDefinitionTest extends TestCase {

	/**
	 * @return void
	 */
	public function testConstructor(): void {
		$definition = new ProtectedDefinition(
			$this->mockDefinition()
		);

		$this->assertInstanceOf(ProtectedDefinition::class, $definition);
		$this->assertInstanceOf(DefinitionInterface::class, $definition);
	}

	/**
	 * @return void
	 */
	public function testGetIdentifier(): void {
		$id = 'identifier';

		$definition = new ProtectedDefinition(
			$this->mockDefinition(
				getIdentifier: $this->returnValue($id)
			)
		);

		$this->assertSame($id, $definition->getIdentifier());
	}

	/**
	 * @return void
	 */
	public function testIsDecorator(): void {
		// test a decorator definition
		$decoratorDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isDecorator: $this->returnValue(true)
			)
		);

		$this->assertTrue($decoratorDefinition->isDecorator());


		// test a non-decorator definition
		$nondecoratorDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isDecorator: $this->returnValue(false)
			)
		);

		$this->assertFalse($nondecoratorDefinition->isDecorator());
	}

	/**
	 * @return void
	 */
	public function testIsProtected(): void {
		// test a decorator definition
		$protectedDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isProtected: $this->returnValue(true)
			)
		);

		$this->assertTrue($protectedDefinition->isProtected());


		// test a unprotected definition
		$unprotectedDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isProtected: $this->returnValue(false)
			)
		);

		$this->assertTrue($unprotectedDefinition->isProtected());
	}

	/**
	 * @return void
	 */
	public function testIsAlias(): void {
		// test a alias definition
		$aliasDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isAlias: $this->returnValue(true)
			)
		);

		$this->assertTrue($aliasDefinition->isAlias());


		// test a non-alias definition
		$nonaliasDefinition = new ProtectedDefinition(
			$this->mockDefinition(
				isAlias: $this->returnValue(false)
			)
		);

		$this->assertFalse($nonaliasDefinition->isAlias());
	}

	/**
	 * @return void
	 */
	public function testIsResolvable(): void {
		$this->toDo();
	}

	/**
	 * @return void
	 */
	public function testResolve(): void {
		$this->toDo();
	}

	/**
	 * @return void
	 */
	public function testToString(): void {
		$this->toDo();
	}
}
