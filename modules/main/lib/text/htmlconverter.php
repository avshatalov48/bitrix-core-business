<?php
namespace Bitrix\Main\Text;

class HtmlConverter	extends Converter
{
	public function encode($text, $textType = '')
	{
		if (is_object($text))
		{
			return $text;
		}

		$textType = Converter::initTextType($textType);
		if ($textType === Converter::HTML)
		{
			return $text;
		}

		if ($text === null)
		{
			return '';
		}

		return HtmlFilter::encode($text);
	}

	public function decode($text, $textType = '')
	{
		if (is_object($text))
		{
			return $text;
		}

		$textType = Converter::initTextType($textType);
		if ($textType === Converter::HTML)
		{
			return $text;
		}

		if ($text === null)
		{
			return '';
		}

		return htmlspecialchars_decode($text);
	}
}
