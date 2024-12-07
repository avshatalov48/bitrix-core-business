<?php

namespace Bitrix\Main\Text;

/**
 * @deprecated Does nothing.
 */
class UtfConverter extends Converter
{
	public function encode($text, $textType = "")
	{
		return $text;
	}

	public function decode($text, $textType = "")
	{
		return $text;
	}
}
