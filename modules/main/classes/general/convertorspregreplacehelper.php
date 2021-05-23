<?php

IncludeModuleLangFile(__FILE__);

class CConvertorsPregReplaceHelper
{
	private $codeMessage = "";

	function __construct($codeMessage = "")
	{
		$this->codeMessage = $codeMessage;
	}

	public function convertCodeTagForEmail($match)
	{
		$text = is_array($match)? $match[2]: $match;
		if ($text == '')
			return '';

		$text = str_replace(array("<",">"), array("&lt;","&gt;"), $text);
		$text = preg_replace("#^(.*?)$#", "   \\1", $text);

		$s1 = "--------------- ".$this->codeMessage." -------------------";
		$s2 = str_repeat("-", mb_strlen($s1));
		$text = "\n\n>".$s1."\n".$text."\n>".$s2."\n\n";

		return $text;
	}

	private $quoteOpened = 0;
	private $quoteClosed = 0;
	private $quoteError  = 0;
	public function checkQuoteError()
	{
		return (($this->quoteOpened == $this->quoteClosed) && ($this->quoteError == 0));
	}

	private $quoteTableClass = "";
	private $quoteHeadClass  = "";
	private $quoteBodyClass  = "";
	public function setQuoteClasses($tableClass, $headClass, $bodyClass)
	{
		$this->quoteTableClass = $tableClass;
		$this->quoteHeadClass  = $headClass;
		$this->quoteBodyClass  = $bodyClass;
	}

	public function convertOpenQuoteTag($match)
	{
		$this->quoteOpened++;
		return "<table class='".$this->quoteTableClass."' width='95%' border='0' cellpadding='3' cellspacing='1'><tr><td class='".$this->quoteHeadClass."'>".GetMessage("CONV_MAIN_QUOTE")."</td></tr><tr><td class='".$this->quoteBodyClass."'>";
	}

	public function convertCloseQuoteTag()
	{
		if ($this->quoteOpened == 0)
		{
			$this->quoteError++;
			return '';
		}
		$this->quoteClosed++;
		return "</td></tr></table>";
	}

	public function convertQuoteTag($match)
	{
		$this->quoteOpened = 0;
		$this->quoteClosed = 0;
		$this->quoteError  = 0;

		$str = $match[0];
		$str = preg_replace_callback("#\\[quote\\]#i",  array($this, "convertOpenQuoteTag"),  $str);
		$str = preg_replace_callback("#\\[/quote\\]#i", array($this, "convertCloseQuoteTag"), $str);

		if ($this->checkQuoteError())
			return $str;
		else
			return $match[0];
	}

	public static function extractUrl($match)
	{
		return extract_url(str_replace('@', chr(11), $match[1]));
	}

	private $linkClass  = "";
	public function setLinkClass($linkClass)
	{
		$this->linkClass = $linkClass;
	}

	private $linkTarget  = "_self";
	public function setLinkTarget($linkTarget)
	{
		$this->linkTarget = $linkTarget;
	}

	private $event1 = "";
	private $event2 = "";
	private $event3 = "";
	public function setEvents($event1="", $event2="", $event3="")
	{
		$this->event1 = $event1;
		$this->event2 = $event2;
		$this->event3 = $event3;
	}

	private $script  = "/bitrix/redirect.php";
	public function setScript($script)
	{
		$this->script = $script;
	}

	function convertToMailTo($match)
	{
		$s = $match[1];
		$s = "<a class=\"".$this->linkClass."\" href=\"mailto:".delete_special_symbols($s)."\" title=\"".GetMessage("CONV_MAIN_MAILTO")."\">".$s."</a>";
		return $s;
	}

	function convertToHref($match)
	{
		$url = $match[1];
		$goto = $url;
		if ($this->event1 != "" || $this->event2 != "")
		{
			$goto = $this->script.
				"?event1=".urlencode($this->event1).
				"&event2=".urlencode($this->event2).
				"&event3=".urlencode($this->event3).
				"&goto=".urlencode($this->goto);
		}
		$target = $this->linkTarget == '_self'? '': ' target="'.$this->linkTarget.'"';

		$s = "<a class=\"".$this->linkClass."\" href=\"".delete_special_symbols($goto)."\"".$target.">".$url."</a>";
		return $s;
	}

	private $codeTableClass = "";
	private $codeHeadClass  = "";
	private $codeBodyClass  = "";
	private $codeTextClass  = "";
	public function setCodeClasses($tableClass, $headClass, $bodyClass, $textAreaClass)
	{
		$this->codeTableClass = $tableClass;
		$this->codeHeadClass  = $headClass;
		$this->codeBodyClass  = $bodyClass;
		$this->codeTextClass  = $textAreaClass;
	}

	function convertCodeTagForHtmlBefore($text = "")
	{
		if (is_array($text))
			$text = $text[2];
		if ($text == '')
			return '';

		$text = str_replace(chr(2), "", $text);
		$text = str_replace("\n", chr(4), $text);
		$text = str_replace("\r", chr(5), $text);
		$text = str_replace(" ", chr(6), $text);
		$text = str_replace("\t", chr(7), $text);
		$text = str_replace("http", "!http!", $text);
		$text = str_replace("https", "!https!", $text);
		$text = str_replace("ftp", "!ftp!", $text);
		$text = str_replace("@", "!@!", $text);

		$text = str_replace(Array("[","]"), array(chr(16), chr(17)), $text);

		$return = "[code]".$text."[/code]";

		return $return;
	}

	function convertCodeTagForHtmlAfter($text = "")
	{
		if (is_array($text))
			$text = $text[1];
		if ($text == '')
			return '';

		$code_mess = GetMessage("CONV_MAIN_CODE");
		$text = str_replace("!http!", "http", $text);
		$text = str_replace("!https!", "https", $text);
		$text = str_replace("!ftp!", "ftp", $text);
		$text = str_replace("!@!", "@", $text);

		$return = "<table class='".$this->codeTableClass."'><tr><td class='".$this->codeHeadClass."'>$code_mess</td></tr><tr><td class='".$this->codeBodyClass."'><textarea class='".$this->codeTextClass."' contentEditable=false cols=60 rows=15 wrap=virtual>$text</textarea></td></tr></table>";

		return $return;
	}
}
