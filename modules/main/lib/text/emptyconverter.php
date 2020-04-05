<?php
namespace Bitrix\Main\Text;

class EmptyConverter
	extends Converter
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
