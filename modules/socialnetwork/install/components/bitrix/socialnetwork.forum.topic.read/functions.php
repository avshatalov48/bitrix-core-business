<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
	return 0;

$this->IncludeComponentLang("functions.php");
if (!function_exists("WrapLongWord")) // Need for custom templates. Do not delete.
{
	function WrapLongWord(&$value, $WordLength=false, $WordBoundary=false, $WordSeparator=false)
	{
		if ($WordBoundary === false)
			$WordBoundary = " \s\n\r\t\x01";
		if ($WordSeparator === false)
			$WordSeparator = '<WBR/>&shy;';
		if (intVal($WordLength) <= 0)
			$WordLength = 20;
		if (is_array($value))
		{
			foreach ($value as $key => $val)
				WrapLongWord($value[$key], $WordLength, $WordBoundary);
		}
		else 
		{
			$value = str_replace(
				array(chr(1), chr(2), chr(3), chr(4), chr(5), "&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;"), 
				array("", "", "", "", "", chr(5), "<", ">", "\"", chr(1), chr(2), chr(3), chr(4)), 
				$value);
			$value = trim(preg_replace("/([^".$WordBoundary."]{".$WordLength."})/", '\\1<WBR/>&shy;', " ".$value." "));
			$value = str_replace(
				array(chr(5), "<", ">", "\"", chr(1), chr(2), chr(3), chr(4), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
				array("&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", "<WBR/>", "<WBR/>", "&shy;"),
				$value);
		}
		$value = 
			str_replace(
				array("&lt;WBR/&gt;","&amp;shy;"),
				array("<WBR/>", "&shy;"), 
				$value);
		return $value;
	}
}
if (!function_exists("ForumNumberEnding"))
{
	function ForumNumberEnding($num)
	{
		if (LANGUAGE_ID=="ru")
		{
			if (strLen($num)>1 && substr($num, strLen($num)-2, 1)=="1")
			{
				return GetMessage("F_END_OV");
			}
			else
			{
				$c = intVal(substr($num, strLen($num)-1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return GetMessage("F_END_OV");
				elseif ($c==1)
					return "";
				else
					return GetMessage("F_END_A");
			}
		}
		else
		{
			if (intVal($num)>1)
				return "s";
			return "";
		}
	}
}

if (!function_exists("endingTopicsPosts"))
{
	function endingTopicsPosts($mark, $count)
	{
		$text = "";
		$count = intVal($count%10);
			
		if ($count==1)
			$text = "_1";
		elseif (($count>2) && ($count<5))
			$text = "_2_4";
		// GetMessage("F_STAT_TOPICS");
		// GetMessage("F_STAT_TOPICS_1");
		// GetMessage("F_STAT_TOPICS_2_4");
		// GetMessage("F_STAT_POSTS");
		// GetMessage("F_STAT_POSTS_1");
		// GetMessage("F_STAT_POSTS_2_4");
		return GetMessage("F_STAT_".strToUpper($mark).$text);
	}
}
?>