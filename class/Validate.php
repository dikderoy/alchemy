<?php

abstract class Validate
{

	protected static function isValidPrototype($ereg, $text)
	{
		if (empty($text)) {
			return FALSE;
		} elseif (preg_match($ereg, $text)) {
			return TRUE;
		}
		return FALSE;
	}

	public static function isValidEmail($text)
	{
		$ereg = "#^[-A-Za-z0-9!\#$%&'*+/=?^_`{|}~]+(\.[-A-Za-z0-9!\#$%&'*+/=?^_`{|}~]+)*@([A-Za-z0-9]([-A-Za-z0-9]{0,61}[A-Za-z0-9])?\.)*(museum|travel|[A-Za-z]{2,4})$#";
		$text = trim($text);

		return self::isValidPrototype($ereg, $text);
	}

	public static function isValidName($text)
	{
		$ereg = "#^[-\S\s]{2,}$#";
		$text = trim($text);

		return self::isValidPrototype($ereg, $text);
	}

	public static function isValidObjectId($text)
	{
		$ereg = "#^[0-9]+$#";

		return self::isValidPrototype($ereg, $text);
	}

	public static function isValidObjectName($text)
	{
		$ereg = "#^[A-Za-z]+[-_A-Za-z0-9]*$#";

		return self::isValidPrototype($ereg, $text);
	}

}