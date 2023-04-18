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
use ShineUnited\Contextual\Callback\CallbackInterface;
use ShineUnited\Contextual\Definition\CallbackDefinition;
use ShineUnited\Contextual\Definition\DefinitionInterface;
use Psr\Container\ContainerExceptionInterface;
use Generator;

/**
 * Callback Definition Test
 */
class CallbackDefinitionTest extends TestCase {


	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testConstructor(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$definition = new CallbackDefinition($id, $callback);

		$this->assertInstanceOf(CallbackDefinition::class, $definition);
		$this->assertInstanceOf(DefinitionInterface::class, $definition);
	}

	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testGetIdentifier(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$definition = new CallbackDefinition($id, $callback);

		$this->assertSame($id, $definition->getIdentifier());
	}

	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testIsDecorator(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$definition = new CallbackDefinition($id, $callback);

		$this->assertSame($isReflexive, $definition->isDecorator());
	}

	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testIsProtected(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$defaultDefinition = new CallbackDefinition($id, $callback);
		$this->assertFalse($defaultDefinition->isProtected());

		$protectedDefinition = new CallbackDefinition($id, $callback, true);
		$this->assertTrue($protectedDefinition->isProtected());

		$unprotectedDefinition = new CallbackDefinition($id, $callback, false);
		$this->assertFalse($unprotectedDefinition->isProtected());
	}

	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testIsAlias(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$definition = new CallbackDefinition($id, $callback);

		$this->assertFalse($definition->isAlias());
	}

	/**
	 * @return void
	 */
	public function testIsResolvable(): void {
		$container = $this->mockContainer([]);

		// test a resolvable callback
		$resolvableDefinition = new CallbackDefinition(
			'resolvable',
			$this->mockCallback(
				canResolveParameters: $this->returnValue(true)
			)
		);

		$this->assertTrue($resolvableDefinition->isResolvable($container));


		// test an unresolvable callback
		$unresolvableDefinition = new CallbackDefinition(
			'unresolvable',
			$this->mockCallback(
				canResolveParameters: $this->returnValue(false)
			)
		);

		$this->assertFalse($unresolvableDefinition->isResolvable($container));
	}

	/**
	 * @throws ContainerExceptionInterface On error.
	 *
	 * @return void
	 */
	public function testResolve(): void {
		$container = $this->mockContainer([]);

		// test a resolvable callback
		$resolvableDefinition = new CallbackDefinition(
			'resolvable',
			$this->mockCallback(
				execute: $this->returnValue(true)
			)
		);

		$this->assertTrue($resolvableDefinition->resolve($container));


		// test an unresolvable callback
		$unresolvableDefinition = new CallbackDefinition(
			'unresolvable',
			$this->mockCallback(
				execute: $this->throwException($this->createStub(ContainerExceptionInterface::class))
			)
		);

		$this->expectException(ContainerExceptionInterface::class);
		$unresolvableDefinition->resolve($container);
	}

	/**
	 * @param string            $id          The identifier.
	 * @param CallbackInterface $callback    The callback.
	 * @param boolean           $isReflexive Is the callback reflexive.
	 *
	 * @return void
	 *
	 * @dataProvider callbackDefinitionDataProvider
	 */
	public function testToString(string $id, CallbackInterface $callback, bool $isReflexive): void {
		$definition = new CallbackDefinition($id, $callback);

		$this->assertIsString($definition->__toString());
	}

	/**
	 * @return Generator
	 */
	public function callbackDefinitionDataProvider(): Generator {
		$reflexiveCallback = $this->createStub(CallbackInterface::class);
		$reflexiveCallback->method('isReflexive')->willReturn(true);

		$nonreflexiveCallback = $this->createStub(CallbackInterface::class);
		$nonreflexiveCallback->method('isReflexive')->willReturn(false);

		yield 'reflexive callback' => [
			'reflexive',
			$this->mockCallback(
				isReflexive: $this->returnValue(true)
			),
			true
		];

		yield 'non-reflexive callback' => [
			'non-reflexive',
			$this->mockCallback(
				isReflexive: $this->returnValue(false)
			),
			false
		];

		/*
		yield 'reflexive callback' => [
			'reflexive',
			$reflexiveCallback,
			true
		];

		yield 'non-reflexive callback' => [
			'non-reflexive',
			$nonreflexiveCallback,
			false
		];
		*/
	}
}
