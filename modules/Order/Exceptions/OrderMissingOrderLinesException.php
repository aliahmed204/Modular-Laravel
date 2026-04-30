<?php 

namespace Modules\Order\Exceptions;

use RuntimeException;

class OrderMissingOrderLinesException extends RuntimeException
{
    protected $message = 'An order must have at least one order line.';
}