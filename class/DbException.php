<?php

/**
 * abstractor for PDOExeption for Db class
 *
 * @author Deroy
 */
class DbException extends Exception
{
	public function __construct($message, $code = 0, $previous = NULL)
	{
		if(!is_long($code)) {
			$message." error code: $code.";
			$code = 0;
		}
		parent::__construct($message, $code, $previous);
	}
}

?>
