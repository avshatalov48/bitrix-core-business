<?php
namespace Bitrix\Main\Text;

class XmlConverter extends Converter
{
	public function encode($text, $textType = "")
	{
		if (is_object($text))
			return $text;

		return HtmlFilter::encode($text);
	}

	public function decode($text, $textType = "")
	{
		if (is_object($text))
			return $text;

		return htmlspecialchars_decode($text);
	}
}
