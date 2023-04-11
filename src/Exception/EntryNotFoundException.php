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

/**
 * Entry Not Found Exception
 */
class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface {

}
