<?php

namespace Bitrix\Main\Text;

class UtfConverter extends Converter
{
	public function encode($text, $textType = "")
	{
		return Encoding::convertEncodingToCurrent($text);
	}

	public function decode($text, $textType = "")
	{
		return Encoding::convertToUtf($text);
	}
}
