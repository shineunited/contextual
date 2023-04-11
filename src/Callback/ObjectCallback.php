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

/**
 * Object Callback
 */
class ObjectCallback extends MethodCallback {

	/**
	 * Create a new callback.
	 *
	 * @param object $object The invokable object.
	 *
	 * @throws InvalidCallbackException Invalid invokable object.
	 */
	public function __construct(object $object) {
		parent::__construct($object, '__invoke');
	}
}
