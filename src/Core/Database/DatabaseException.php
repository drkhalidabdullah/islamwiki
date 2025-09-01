<?php

namespace IslamWiki\Core\Database;

use Exception;

/**
 * Database Exception - Custom exception for database operations
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class DatabaseException extends Exception
{
    /**
     * Constructor
     */
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 