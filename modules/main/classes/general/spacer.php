<?php

class CSpacer
{
	var $iMaxChar;
	var $symbol;

	function __construct($iMaxChar, $symbol)
	{
		$this->iMaxChar = $iMaxChar;
		$this->symbol = $symbol;
	}

	function InsertSpaces($string)
	{
		return preg_replace_callback('/(^|>)([^<>]+)(<|$)/', array($this, "__InsertSpacesCallback"), $string);
	}

	function __InsertSpacesCallback($arMatch)
	{
		return $arMatch[1].preg_replace("/([^() \\n\\r\\t%!?{}\\][-]{".$this->iMaxChar."})/".BX_UTF_PCRE_MODIFIER,"\\1".$this->symbol, $arMatch[2]).$arMatch[3];
	}
}
