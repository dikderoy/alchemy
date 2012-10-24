<?php

abstract class Validate
{
	public static function isValidEmail($text)
	{
		$text = trim($text);
		if(empty($text)) {
			return FALSE;
		} elseif(preg_match("#^[-A-Za-z0-9!\#$%&'*+/=?^_`{|}~]+(\.[-A-Za-z0-9!\#$%&'*+/=?^_`{|}~]+)*@([A-Za-z0-9]([-A-Za-z0-9]{0,61}[A-Za-z0-9])?\.)*(aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[A-Za-z]{2,4})$#", $text)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isValidName($text)
	{
		$text = trim($text);
		if(empty($text)) {
			return FALSE;
		} elseif(preg_match("#^[-\S]{2,}$#", $text)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}