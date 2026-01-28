<?php

declare(strict_types=1);

namespace Lmc\Api\Rest\Exception;

use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
}
