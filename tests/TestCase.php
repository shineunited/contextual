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

 use PHPUnit\Framework\TestCase as BaseTestCase;

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
}
