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

namespace ShineUnited\Contextual\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 * Callback Not Found Exception
 */
class CallbackNotFoundException extends Exception implements NotFoundExceptionInterface {

}