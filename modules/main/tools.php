<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Diag;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Text;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Security;
use Bitrix\Main\SystemException;

/**
 * @deprecated Use microtime(true)
 * @return float
 */
function getmicrotime()
{
	return microtime(true);
}

/**
 * HTML form elements
 */

/**
 * Returns HTML "input"
 */
function InputType($strType, $strName, $strValue, $strCmp, $strPrintValue=false, $strPrint="", $field1="", $strId="")
{
	$bCheck = false;
	if($strValue <> '')
	{
		if(is_array($strCmp))
			$bCheck = in_array($strValue, $strCmp);
		elseif($strCmp <> '')
			$bCheck = in_array($strValue, explode(",", $strCmp));
	}
	$bLabel = false;
	if ($strType == 'radio')
		$bLabel = true;

	$bId = true;
	if($strType == 'radio' || $strType == 'checkbox')
	{
		$bId = !preg_match('/^id="/', $field1) && !preg_match('/\sid="/', $field1);
	}

	return ($bLabel? '<label>': '').'<input type="'.$strType.'" '.$field1.' name="'.$strName.'"'.
		($bId ? ' id="'.($strId <> ''? $strId : $strName).'"' : '').
		' value="'.$strValue.'"'.
		($bCheck? ' checked':'').'>'.($strPrintValue? $strValue:$strPrint).($bLabel? '</label>': '');
}

/**
 * Returns HTML "select"
 *
 * @param string $strBoxName Input name
 * @param CDBResult $a DB result with items
 * @param string $strDetText Empty item text
 * @param string $strSelectedVal Selected item value
 * @param string $field1 Additional attributes
 * @return string
 */
function SelectBox($strBoxName, $a,	$strDetText = "", $strSelectedVal = "", $field1="class=\"typeselect\"")
{
	$strReturnBox = "<select ".$field1." name=\"".$strBoxName."\" id=\"".$strBoxName."\">";
	if ($strDetText <> '')
		$strReturnBox = $strReturnBox."<option value=\"NOT_REF\">".$strDetText."</option>";
	while (($ar = $a->Fetch()))
	{
		$reference_id = $ar["REFERENCE_ID"] ?? '';
		$reference = $ar["REFERENCE"] ?? '';
		if ($reference_id == '')
			$reference_id = $ar["reference_id"];
		if ($reference == '')
			$reference = $ar["reference"];

		$strReturnBox = $strReturnBox."<option ";
		if (strcasecmp($reference_id, $strSelectedVal) == 0)
			$strReturnBox = $strReturnBox." selected ";
		$strReturnBox = $strReturnBox."value=\"".htmlspecialcharsbx($reference_id). "\">". htmlspecialcharsbx($reference)."</option>";
	}
	return $strReturnBox."</select>";
}

/**
 * Returns HTML multiple "select"
 *
 * @param string $strBoxName Input name
 * @param CDBResult $a DB result with items
 * @param array $arr Selected values
 * @param string $strDetText Empty item text
 * @param bool $strDetText_selected Allow to choose an empty item
 * @param string $size Size attribute
 * @param string $field1 Additional attributes
 * @return string
 */
function SelectBoxM($strBoxName, $a, $arr, $strDetText = "", $strDetText_selected = false, $size = "5", $field1="class=\"typeselect\"")
{
	$strReturnBox = "<select ".$field1." multiple name=\"".$strBoxName."\" id=\"".$strBoxName."\" size=\"".$size."\">";
	if ($strDetText <> '')
	{
		$strReturnBox = $strReturnBox."<option ";
		if ($strDetText_selected)
			$strReturnBox = $strReturnBox." selected ";
		$strReturnBox = $strReturnBox." value='NOT_REF'>".$strDetText."</option>";
	}
	while ($ar = $a->Fetch())
	{
		$reference_id = $ar["REFERENCE_ID"];
		$reference = $ar["REFERENCE"];
		if ($reference_id == '')
			$reference_id = $ar["reference_id"];
		if ($reference == '')
			$reference = $ar["reference"];

		$sel = (is_array($arr) && in_array($reference_id, $arr)? "selected": "");
		$strReturnBox = $strReturnBox."<option ".$sel;
		$strReturnBox = $strReturnBox." value=\"".htmlspecialcharsbx($reference_id)."\">". htmlspecialcharsbx($reference)."</option>";
	}
	return $strReturnBox."</select>";
}

/**
 * Returns HTML multiple "select" from array
 *
 * @param string $strBoxName Input name
 * @param array $a Array with items
 * @param array|false $arr Selected values
 * @param string $strDetText Empty item text
 * @param bool $strDetText_selected Allow to choose an empty item
 * @param string $size Size attribute
 * @param string $field1 Additional attributes
 * @return string
 */
function SelectBoxMFromArray($strBoxName, $a, $arr, $strDetText = "", $strDetText_selected = false, $size = "5", $field1="class='typeselect'")
{
	$strReturnBox = "<select ".$field1." multiple name=\"".$strBoxName."\" id=\"".$strBoxName."\" size=\"".$size."\">";

	if(array_key_exists("REFERENCE_ID", $a))
		$reference_id = $a["REFERENCE_ID"];
	elseif(array_key_exists("reference_id", $a))
		$reference_id = $a["reference_id"];
	else
		$reference_id = array();

	if(array_key_exists("REFERENCE", $a))
		$reference = $a["REFERENCE"];
	elseif(array_key_exists("reference", $a))
		$reference = $a["reference"];
	else
		$reference = array();

	if($strDetText <> '')
	{
		$strReturnBox .= "<option ";
		if($strDetText_selected)
			$strReturnBox .= " selected ";
		$strReturnBox .= " value='NOT_REF'>".$strDetText."</option>";
	}

	foreach($reference_id as $key => $value)
	{
		$sel = (is_array($arr) && in_array($value, $arr)? "selected" : "");
		$strReturnBox .= "<option value=\"".htmlspecialcharsbx($value)."\" ".$sel.">". htmlspecialcharsbx($reference[$key])."</option>";
	}

	$strReturnBox .= "</select>";
	return $strReturnBox;
}

/**
 * Returns HTML "select" from array data
 */
function SelectBoxFromArray(
	$strBoxName,
	$db_array,
	$strSelectedVal = "",
	$strDetText = "",
	$field1="class='typeselect'",
	$go = false,
	$form="form1"
	)
{
	$boxName = htmlspecialcharsbx($strBoxName);
	if($go)
	{
		$funName = preg_replace("/[^a-z0-9_]/i", "", $strBoxName);
		$jsName = CUtil::JSEscape($strBoxName);

		$strReturnBox = "<script type=\"text/javascript\">\n".
			"function ".$funName."LinkUp()\n".
			"{var number = document.".$form."['".$jsName."'].selectedIndex;\n".
			"if(document.".$form."['".$jsName."'].options[number].value!=\"0\"){ \n".
			"document.".$form."['".$jsName."_SELECTED'].value=\"yes\";\n".
			"document.".$form.".submit();\n".
			"}}\n".
			"</script>\n";
		$strReturnBox .= '<input type="hidden" name="'.$boxName.'_SELECTED" id="'.$boxName.'_SELECTED" value="">';
		$strReturnBox .= '<select '.$field1.' name="'.$boxName.'" id="'.$boxName.'" onchange="'.$funName.'LinkUp()" class="typeselect">';
	}
	else
	{
		$strReturnBox = '<select '.$field1.' name="'.$boxName.'" id="'.$boxName.'">';
	}

	if(isset($db_array["reference"]) && is_array($db_array["reference"]))
		$ref = $db_array["reference"];
	elseif(isset($db_array["REFERENCE"]) && is_array($db_array["REFERENCE"]))
		$ref = $db_array["REFERENCE"];
	else
		$ref = array();

	if(isset($db_array["reference_id"]) && is_array($db_array["reference_id"]))
		$ref_id = $db_array["reference_id"];
	elseif(isset($db_array["REFERENCE_ID"]) && is_array($db_array["REFERENCE_ID"]))
		$ref_id = $db_array["REFERENCE_ID"];
	else
		$ref_id = array();

	if($strDetText <> '')
		$strReturnBox .= '<option value="">'.$strDetText.'</option>';

	foreach($ref as $i => $val)
	{
		$strReturnBox .= '<option';
		if(strcasecmp($ref_id[$i], $strSelectedVal) == 0)
			$strReturnBox .= ' selected';
		$strReturnBox .= ' value="'.htmlspecialcharsbx($ref_id[$i]).'">'.htmlspecialcharsbx($val).'</option>';
	}
	return $strReturnBox.'</select>';
}

/**
 * Date functions
 */

function Calendar($sFieldName, $sFormName="skform", $sFromName="", $sToName="")
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION == true)
		return CAdminCalendar::Calendar($sFieldName, $sFromName, $sToName);

	static $bCalendarCode = false;
	$func = "";
	if(!$bCalendarCode)
	{
		$bCalendarCode = true;
		$func =
			"<script type=\"text/javascript\">\n".
			"<!--\n".
			"window.Calendar = function(params, dateVal)\n".
			"{\n".
			"	var left, top;\n".
			"	var width = 180, height = 160;\n".
			"	if('['+typeof(window.event)+']' == '[object]')\n".
			"	{\n".
			"		top = (window.event.screenY+20+height>screen.height-40? window.event.screenY-45-height:window.event.screenY+20);\n".
			"		left = (window.event.screenX-width/2);\n".
			"	}\n".
			"	else\n".
			"	{\n".
			"		top = Math.floor((screen.height - height)/2-14);\n".
			"		left = Math.floor((screen.width - width)/2-5);\n".
			"	}\n".
			"	window.open('/bitrix/tools/calendar.php?lang=".LANGUAGE_ID.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"&admin_section=N")."&'+params+'&date='+escape(dateVal)+'&initdate='+escape(dateVal),'','scrollbars=no,resizable=yes,width='+width+',height='+height+',left='+left+',top='+top);\n".
			"}\n".
			"//-->\n".
			"</script>\n";
	}
	return $func."<a href=\"javascript:void(0);\" onclick=\"window.Calendar('name=".urlencode($sFieldName)."&amp;from=".urlencode($sFromName)."&amp;to=".urlencode($sToName)."&amp;form=".urlencode($sFormName)."', document['".$sFormName."']['".$sFieldName."'].value);\" title=\"".GetMessage("TOOLS_CALENDAR")."\"><img src=\"".BX_ROOT."/images/icons/calendar.gif\" alt=\"".GetMessage("TOOLS_CALENDAR")."\" width=\"15\" height=\"15\" border=\"0\" /></a>";
}

function CalendarDate($sFromName, $sFromVal, $sFormName="skform", $size="10", $param="class=\"typeinput\"")
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION == true)
		return CAdminCalendar::CalendarDate($sFromName, $sFromVal, $size, ($size > 10));

	return '<input type="text" name="'.$sFromName.'" id="'.$sFromName.'" size="'.$size.'" value="'.htmlspecialcharsbx($sFromVal).'" '.$param.' /> '."\n".Calendar($sFromName, $sFormName)."\n";
}

function CalendarPeriod($sFromName, $sFromVal, $sToName, $sToVal, $sFormName="skform", $show_select="N", $field_select="class=\"typeselect\"", $field_input="class=\"typeinput\"", $size="10")
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION == true)
		return CAdminCalendar::CalendarPeriod($sFromName, $sToName, $sFromVal, $sToVal, ($show_select=="Y"), $size, ($size > 10));

	$arr = array();
	$str = "";
	$ds = "";
	if ($show_select=="Y")
	{
		$sname = $sFromName."_DAYS_TO_BACK";
		$str = "
<script type=\"text/javascript\">
function ".$sFromName."_SetDate()
{
	var number = document.".$sFormName.".".$sname.".selectedIndex-1;
	document.".$sFormName.".".$sFromName.".disabled = false;
	if (number>=0)
	{
		document.".$sFormName.".".$sFromName.".value = dates[number];
		document.".$sFormName.".".$sFromName.".disabled = true;
	}
}
</script>
";
		global $$sname;
		$value = $$sname;
		if ((string)$value <> '' && $value!="NOT_REF")
			$ds = "disabled";

		?><script type="text/javascript">
			var dates = [];
		<?
		for ($i=0; $i<=90; $i++)
		{
			$prev_date = GetTime(time()-86400*$i);
			?>dates[<?=$i?>]="<?=$prev_date?>";<?
			if (!is_array($arr["reference"])) $arr["reference"] = array();
			if (!is_array($arr["reference_id"])) $arr["reference_id"] = array();
			$arr["reference"][] = $i." ".GetMessage("TOOLS_DN");
			$arr["reference_id"][] = $i;
		}
		?></script><?
		$str .= SelectBoxFromArray($sname, $arr, $value , "&nbsp;", "onchange=\"".$sFromName."_SetDate()\" ".$field_select);
		$str .= "&nbsp;";
	}
	$str .=
		'<input '.$ds.' '.$field_input.' type="text" name="'.$sFromName.'" id="'.$sFromName.'" size="'.$size.'" value="'.htmlspecialcharsbx($sFromVal).'" /> '."\n".
		Calendar($sFromName, $sFormName, $sFromName, $sToName).' ... '."\n".
		'<input '.$field_input.' type="text" name="'.$sToName.'" id="'.$sToName.'" size="'.$size.'" value="'.htmlspecialcharsbx($sToVal).'" /> '."\n".
		Calendar($sToName, $sFormName, $sFromName, $sToName)."\n";

	return '<span style="white-space: nowrap;">'.$str.'</span>';
}

/**
 * Checks date by format
 */
function CheckDateTime($datetime, $format=false)
{
	$datetime = strval($datetime);

	if ($format===false && defined("FORMAT_DATETIME"))
		$format = FORMAT_DATETIME;

	$ar = ParseDateTime($datetime, $format);

	if ($ar === false)
	{
		return false;
	}

	$day = intval($ar["DD"]);
	$hour = $month = 0;

	if (isset($ar["MMMM"]))
	{
		if (is_numeric($ar["MMMM"]))
		{
			$month = intval($ar["MMMM"]);
		}
		else
		{
			$month = GetNumMonth($ar["MMMM"]);
			if (!$month)
				$month = intval(date('m', strtotime($ar["MMMM"])));
		}
	}
	elseif (isset($ar["MM"]))
	{
		$month = intval($ar["MM"]);
	}
	elseif (isset($ar["M"]))
	{
		if (is_numeric($ar["M"]))
		{
			$month = intval($ar["M"]);
		}
		else
		{
			$month = GetNumMonth($ar["M"]);
			if (!$month)
				$month = intval(date('m', strtotime($ar["M"])));
		}
	}
	$year  = intval($ar["YYYY"] ?? 0);
	if (isset($ar["HH"]))
	{
		$hour  = intval($ar["HH"]);
	}
	elseif (isset($ar["H"]))
	{
		$hour  = intval($ar["H"]);
	}
	elseif (isset($ar["GG"]))
	{
		$hour  = intval($ar["GG"]);
	}
	elseif (isset($ar["G"]))
	{
		$hour  = intval($ar["G"]);
	}
	if (isset($ar['TT']) || isset($ar['T']))
	{
		$middletime = $ar['TT'] ?? $ar['T'];
		if (strcasecmp('pm', $middletime)===0)
		{
			if ($hour < 12)
				$hour += 12;
		}
		else
		{
			if ($hour == 12)
				$hour = 0;
		}
	}
	$min   = intval($ar["MI"] ?? 0);
	$sec   = intval($ar["SS"] ?? 0);

	if (!checkdate($month, $day, $year))
		return false;

	if ($hour>24 || $hour<0 || $min<0 || $min>59 || $sec<0 || $sec>59)
		return false;

	$s1 = preg_replace("~([^:\\\\/\\s.,0-9-]+|[^:\\\\/\\s.,a-z-]+)[\n\r\t ]*~i".BX_UTF_PCRE_MODIFIER, "P", $datetime);
	$s2 = preg_replace("/(DD|MMMM|MM|MI|M|YYYY|HH|H|GG|G|SS|TT|T)[\n\r\t ]*/i".BX_UTF_PCRE_MODIFIER, "P", $format);

	if(mb_strlen($s1) <= mb_strlen($s2))
		return $s1 == mb_substr($s2, 0, mb_strlen($s1));
	else
		return $s2 == mb_substr($s1, 0, mb_strlen($s2));
}

/**
 * Returns the number of a month
 */
function GetNumMonth ($month)
{
	global $MESS;
	if ($month)
	{
		for ($i = 1; $i <= 12; $i++)
		{
			if (strcasecmp($MESS['MONTH_'.$i.'_S'], $month) === 0 || strcasecmp($MESS['MON_'.$i], $month) === 0 || strcasecmp($MESS['MONTH_'.$i], $month) === 0)
				return $i;
		}
	}
	return false;
}

/**
 * Returns unix timestamp from date string
 */
function MakeTimeStamp($datetime, $format=false)
{
	if($format===false && defined("FORMAT_DATETIME"))
		$format = FORMAT_DATETIME;

	$ar = ParseDateTime($datetime, $format);

	$day = intval($ar["DD"] ?? 0);
	$hour = $month = 0;

	if (isset($ar["MMMM"]))
	{
		if (is_numeric($ar["MMMM"]))
		{
			$month = intval($ar["MMMM"]);
		}
		else
		{
			$month = GetNumMonth($ar["MMMM"]);
			if (!$month)
				$month = intval(date('m', strtotime($ar["MMMM"])));
		}
	}
	elseif (isset($ar["MM"]))
	{
		$month = intval($ar["MM"]);
	}
	elseif (isset($ar["M"]))
	{
		if (is_numeric($ar["M"]))
		{
			$month = intval($ar["M"]);
		}
		else
		{
			$month = GetNumMonth($ar["M"], true);
			if (!$month)
				$month = intval(date('m', strtotime($ar["M"])));
		}
	}
	$year  = intval($ar["YYYY"] ?? 0);
	if (isset($ar["HH"]))
	{
		$hour  = intval($ar["HH"]);
	}
	elseif (isset($ar["H"]))
	{
		$hour  = intval($ar["H"]);
	}
	elseif (isset($ar["GG"]))
	{
		$hour  = intval($ar["GG"]);
	}
	elseif (isset($ar["G"]))
	{
		$hour  = intval($ar["G"]);
	}
	if (isset($ar['TT']) || isset($ar['T']))
	{
		$middletime = $ar['TT'] ?? $ar['T'];
		if (strcasecmp('pm', $middletime)===0)
		{
			if ($hour < 12)
				$hour += 12;
		}
		else
		{
			if ($hour == 12)
				$hour = 0;
		}
	}
	$min = intval($ar["MI"] ?? 0);
	$sec = intval($ar["SS"] ?? 0);

	if(!checkdate($month, $day, $year))
		return false;

	if($hour>24 || $hour<0 || $min<0 || $min>59 || $sec<0 || $sec>59)
		return false;

	return mktime($hour, $min, $sec, $month, $day, $year);
}

/**
 * Parse a date into an array
 */
function ParseDateTime($datetime, $format=false)
{
	if ($datetime === null)
	{
		return false;
	}
	if ($format===false && defined("FORMAT_DATETIME"))
		$format = FORMAT_DATETIME;

	$fm_args = array();
	if(preg_match_all("/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/i", $format , $fm_args))
	{
		$dt_args = array();
		if(preg_match_all("~([^:\\\\/\\s.,0-9-]+|[^:\\\\/\\s.,a-z-]+)~i".BX_UTF_PCRE_MODIFIER, $datetime, $dt_args))
		{
			$arrResult = array();
			foreach($fm_args[0] as $i => $v)
			{
				if (isset($dt_args[0][$i]))
				{
					if (is_numeric($dt_args[0][$i]))
					{
						$arrResult[$v] = sprintf("%0".mb_strlen($v)."d", intval($dt_args[0][$i]));
					}
					elseif(($dt_args[0][$i] == "am" || $dt_args[0][$i] == "pm") && array_search("T", $fm_args[0]) !== false)
					{
						$arrResult["T"] = $dt_args[0][$i];
					}
					elseif(($dt_args[0][$i] == "AM" || $dt_args[0][$i] == "PM") && array_search("TT", $fm_args[0]) !== false)
					{
						$arrResult["TT"] = $dt_args[0][$i];
					}
					else
					{
						$arrResult[$v] = $dt_args[0][$i];
					}
				}
			}
			return $arrResult;
		}
	}
	return false;
}

/**
 * Adds value to the date in timestamp
 */
function AddToTimeStamp($arrAdd, $stmp=false)
{
	if ($stmp === false)
		$stmp = time();
	if (is_array($arrAdd))
	{
		foreach($arrAdd as $key => $value)
		{
			$value = intval($value);
			switch ($key)
			{
				case "DD":
					$stmp = AddTime($stmp, $value, "D");
					break;
				case "MM":
					$stmp = AddTime($stmp, $value, "MN");
					break;
				case "YYYY":
					$stmp = AddTime($stmp, $value, "Y");
					break;
				case "HH":
					$stmp = AddTime($stmp, $value, "H");
					break;
				case "MI":
					$stmp = AddTime($stmp, $value, "M");
					break;
				case "SS":
					$stmp = AddTime($stmp, $value, "S");
					break;
			}
		}
	}
	return $stmp;
}

function ConvertDateTime($datetime, $to_format=false, $from_site=false, $bSearchInSitesOnly = false)
{
	if ($to_format===false && defined("FORMAT_DATETIME")) $to_format = FORMAT_DATETIME;
	return FmtDate($datetime, $to_format, $from_site, false, $bSearchInSitesOnly);
}

function ConvertTimeStamp($timestamp=false, $type="SHORT", $site=false, $bSearchInSitesOnly = false)
{
	if($timestamp === false)
		$timestamp = time();
	return GetTime($timestamp, $type, $site, $bSearchInSitesOnly);
}

/**
 * Converts a date from site format to specified one
 */
function FmtDate($str_date, $format=false, $site=false, $bSearchInSitesOnly = false)
{
	global $DB;
	if ($site===false && defined("SITE_ID")) $site = SITE_ID;
	if ($format===false && defined("FORMAT_DATETIME")) $format = FORMAT_DATETIME;
	return $DB->FormatDate($str_date, CSite::GetDateFormat("FULL", $site, $bSearchInSitesOnly), $format);
}

function _FormatDateMessage($value, $messages)
{
	if($value < 100)
		$val = abs($value);
	else
		$val = abs($value % 100);

	$dec = $val % 10;

	if($val == 0)
		return GetMessage($messages["0"], array("#VALUE#" => $value));
	elseif($val == 1)
		return GetMessage($messages["1"], array("#VALUE#" => $value));
	elseif($val >= 10 && $val <= 20)
		return GetMessage($messages["10_20"], array("#VALUE#" => $value));
	elseif($dec == 1)
		return GetMessage($messages["MOD_1"], array("#VALUE#" => $value));
	elseif(2 <= $dec && $dec <= 4)
		return GetMessage($messages["MOD_2_4"], array("#VALUE#" => $value));
	else
		return GetMessage($messages["MOD_OTHER"], array("#VALUE#" => $value));
}

define("AM_PM_NONE", false);
define("AM_PM_UPPER", 1);
define("AM_PM_LOWER", 2);

function IsAmPmMode($returnConst = false)
{
	if($returnConst)
	{
		if(strpos(FORMAT_DATETIME, 'TT') !== false)
		{
			return AM_PM_UPPER;
		}
		if(strpos(FORMAT_DATETIME, 'T') !== false)
		{
			return AM_PM_LOWER;
		}
		return AM_PM_NONE;
	}
	return strpos(FORMAT_DATETIME, 'T') !== false;
}

function convertTimeToMilitary ($strTime, $fromFormat = 'H:MI T', $toFormat = 'HH:MI')
{
	global $DB;

	$arParsedDate = ParseDateTime($strTime, $fromFormat);

	if (isset($arParsedDate["H"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["H"]);
	}
	elseif (isset($arParsedDate["GG"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["GG"]);
	}
	elseif (isset($arParsedDate["G"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["G"]);
	}

	if (isset($arParsedDate['TT']) || isset($arParsedDate['T']))
	{
		$middletime = $arParsedDate['TT'] ?? $arParsedDate['T'];
		if (strcasecmp('pm', $middletime)===0)
		{
			if ($arParsedDate["HH"] < 12)
				$arParsedDate["HH"] += 12;
			elseif($arParsedDate["HH"] == 12)
				$arParsedDate["HH"] = 12;
			else
				$arParsedDate["HH"] -= 12;
		}
	}

	$ts = mktime($arParsedDate['HH'], $arParsedDate['MI'], ($arParsedDate['SS'] ?? 0), 3, 7, 2012);
	return FormatDate($DB->dateFormatToPHP($toFormat), $ts);
}

/**
 * @param string|array $format
 * @param int|bool|Main\Type\Date $timestamp
 * @param int|bool|Main\Type\Date $now
 *
 * @return string
 */
function FormatDate($format = "", $timestamp = false, $now = false)
{
	global $DB;

	if ($timestamp === false)
	{
		$timestamp = time();
	}
	else if ($timestamp instanceof Main\Type\Date)
	{
		$timestamp = $timestamp->getTimestamp();
	}
	else
	{
		$timestamp = intval($timestamp);
	}

	if ($now === false)
	{
		$now = time();
	}
	else if ($now instanceof Main\Type\Date)
	{
		$now = $now->getTimestamp();
	}
	else
	{
		$now = intval($now);
	}

	switch($format)
	{
		case "SHORT":
			$format = $DB->dateFormatToPHP(FORMAT_DATE);
			break;
		case "FULL":
			$format = $DB->dateFormatToPHP(FORMAT_DATETIME);
	}

	if(is_array($format))
	{
		$seconds_ago = $now - $timestamp;
		foreach($format as $format_interval => $format_value)
		{
			if($format_interval == "s")
			{
				if($seconds_ago < 60)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif(preg_match('/^s(\d+)\>?(\d+)?/', $format_interval, $match))
			{
				if (isset($match[1]) && isset($match[2]))
				{
					if(
						$seconds_ago < intval($match[1])
						&& $seconds_ago > intval($match[2])
					)
					{
						return FormatDate($format_value, $timestamp, $now);
					}
				}
				else if($seconds_ago < intval($match[1]))
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "i")
			{
				if($seconds_ago < 60*60)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif(preg_match('/^i(\d+)\>?(\d+)?/', $format_interval, $match))
			{
				if (isset($match[1]) && isset($match[2]))
				{
					if(
						$seconds_ago < intval($match[1])*60
						&& $seconds_ago > intval($match[2])*60
					)
					{
						return FormatDate($format_value, $timestamp, $now);
					}
				}
				else if($seconds_ago < intval($match[1])*60)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "H")
			{
				if($seconds_ago < 24*60*60)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif(preg_match('/^H(\d+)\>?(\d+)?/', $format_interval, $match))
			{
				if (isset($match[1]) && isset($match[2]))
				{
					if(
						$seconds_ago < intval($match[1])*60*60
						&& $seconds_ago > intval($match[2])*60*60
					)
					{
						return FormatDate($format_value, $timestamp, $now);
					}
				}
				else if($seconds_ago < intval($match[1])*60*60)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "d")
			{
				if($seconds_ago < 31*24*60*60)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif(preg_match('/^d(\d+)\>?(\d+)?/', $format_interval, $match))
			{
				if (isset($match[1]) && isset($match[2]))
				{
					if(
						$seconds_ago < intval($match[1])*24*60*60
						&& $seconds_ago > intval($match[2])*24*60*60
					)
					{
						return FormatDate($format_value, $timestamp, $now);
					}
				}
				else if($seconds_ago < intval($match[1])*24*60*60)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "m")
			{
				if($seconds_ago < 365*24*60*60)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif(preg_match('/^m(\d+)\>?(\d+)?/', $format_interval, $match))
			{
				if (isset($match[1]) && isset($match[2]))
				{
					if(
						$seconds_ago < intval($match[1])*31*24*60*60
						&& $seconds_ago > intval($match[2])*31*24*60*60
					)
					{
						return FormatDate($format_value, $timestamp, $now);
					}
				}
				else if($seconds_ago < intval($match[1])*31*24*60*60)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "now")
			{
				if($timestamp == $now)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "today")
			{
				$arNow = localtime($now);
				$today_1 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3], $arNow[5]+1900);
				$today_2 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]+1, $arNow[5]+1900);
				if($timestamp >= $today_1 && $timestamp < $today_2)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "todayFuture")
			{
				$arNow = localtime($now);
				$today_1 = $now;
				$today_2 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]+1, $arNow[5]+1900);
				if($timestamp >= $today_1 && $timestamp < $today_2)
				{
					return FormatDate($format_value, $timestamp, $now);
				}
			}
			elseif($format_interval == "yesterday")
			{
				$arNow = localtime($now);
				//le = number of seconds scince midnight
				//$le = $arSDate[0]+$arSDate[1]*60+$arSDate[2]*3600;
				//yesterday_1 = truncate(now)-1
				$yesterday_1 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]-1, $arNow[5]+1900);
				//yesterday_2 = truncate(now)
				$yesterday_2 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3], $arNow[5]+1900);

				if($timestamp >= $yesterday_1 && $timestamp < $yesterday_2)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif($format_interval == "tommorow" || $format_interval == "tomorrow")
			{
				$arNow = localtime($now);
				$tomorrow_1 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]+1, $arNow[5]+1900);
				$tomorrow_2 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]+2, $arNow[5]+1900);

				if($timestamp >= $tomorrow_1 && $timestamp < $tomorrow_2)
					return FormatDate($format_value, $timestamp, $now);
			}
			elseif($format_interval == "-")
			{
				if($seconds_ago < 0)
					return FormatDate($format_value, $timestamp, $now);
			}
		}
		return FormatDate(array_pop($format), $timestamp, $now);
	}

	$bCutZeroTime = false;
	if (substr($format, 0, 1) == '^')
	{
		$bCutZeroTime = true;
		$format = substr($format, 1);
	}

	$arFormatParts = preg_split("/(?<!\\\\)(
		sago|iago|isago|Hago|dago|mago|Yago|
		sdiff|idiff|Hdiff|ddiff|mdiff|Ydiff|
		sshort|ishort|Hshort|dshort|mhort|Yshort|
		yesterday|today|tomorrow|tommorow|
		X|x|j|F|f|Y|Q|M|l|D
	)/x", $format, 0, PREG_SPLIT_DELIM_CAPTURE);

	$result = "";
	$currentLanguage = Main\Localization\Loc::getCurrentLang();
	foreach($arFormatParts as $format_part)
	{
		switch($format_part)
		{
		case "":
			break;
		case "sago":
			$seconds_ago = intval($now - $timestamp);
			$result .= _FormatDateMessage($seconds_ago, array(
				"0" => "FD_SECOND_AGO_0",
				"1" => "FD_SECOND_AGO_1",
				"10_20" => "FD_SECOND_AGO_10_20",
				"MOD_1" => "FD_SECOND_AGO_MOD_1",
				"MOD_2_4" => "FD_SECOND_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_SECOND_AGO_MOD_OTHER",
			));
			break;
		case "sdiff":
			$seconds_ago = intval($now - $timestamp);
			$result .= _FormatDateMessage($seconds_ago, array(
				"0" => "FD_SECOND_DIFF_0",
				"1" => "FD_SECOND_DIFF_1",
				"10_20" => "FD_SECOND_DIFF_10_20",
				"MOD_1" => "FD_SECOND_DIFF_MOD_1",
				"MOD_2_4" => "FD_SECOND_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_SECOND_DIFF_MOD_OTHER",
			));
			break;
		case "sshort":
			$seconds_ago = intval($now - $timestamp);
			$result .= GetMessage("FD_SECOND_SHORT", array("#VALUE#" => $seconds_ago));
			break;
		case "iago":
			$minutes_ago = intval(($now - $timestamp) / 60);
			$result .= _FormatDateMessage($minutes_ago, array(
				"0" => "FD_MINUTE_AGO_0",
				"1" => "FD_MINUTE_AGO_1",
				"10_20" => "FD_MINUTE_AGO_10_20",
				"MOD_1" => "FD_MINUTE_AGO_MOD_1",
				"MOD_2_4" => "FD_MINUTE_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_MINUTE_AGO_MOD_OTHER",
			));
			break;
		case "idiff":
			$minutes_ago = intval(($now - $timestamp) / 60);
			$result .= _FormatDateMessage($minutes_ago, array(
				"0" => "FD_MINUTE_DIFF_0",
				"1" => "FD_MINUTE_DIFF_1",
				"10_20" => "FD_MINUTE_DIFF_10_20",
				"MOD_1" => "FD_MINUTE_DIFF_MOD_1",
				"MOD_2_4" => "FD_MINUTE_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_MINUTE_DIFF_MOD_OTHER",
			));
			break;
        case "ishort":
			$minutes_ago = intval(($now - $timestamp) / 60);
			$result .= GetMessage("FD_MINUTE_SHORT", array("#VALUE#" => $minutes_ago));
			break;
		case "isago":
			$minutes_ago = intval(($now - $timestamp) / 60);
			$result .= _FormatDateMessage($minutes_ago, array(
				"0" => "FD_MINUTE_0",
				"1" => "FD_MINUTE_1",
				"10_20" => "FD_MINUTE_10_20",
				"MOD_1" => "FD_MINUTE_MOD_1",
				"MOD_2_4" => "FD_MINUTE_MOD_2_4",
				"MOD_OTHER" => "FD_MINUTE_MOD_OTHER",
			));

			$result .= " ";

			$seconds_ago = intval($now - $timestamp)-($minutes_ago*60);
			$result .= _FormatDateMessage($seconds_ago, array(
				"0" => "FD_SECOND_AGO_0",
				"1" => "FD_SECOND_AGO_1",
				"10_20" => "FD_SECOND_AGO_10_20",
				"MOD_1" => "FD_SECOND_AGO_MOD_1",
				"MOD_2_4" => "FD_SECOND_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_SECOND_AGO_MOD_OTHER",
			));
			break;
		case "Hago":
			$hours_ago = intval(($now - $timestamp) / 60 / 60);
			$result .= _FormatDateMessage($hours_ago, array(
				"0" => "FD_HOUR_AGO_0",
				"1" => "FD_HOUR_AGO_1",
				"10_20" => "FD_HOUR_AGO_10_20",
				"MOD_1" => "FD_HOUR_AGO_MOD_1",
				"MOD_2_4" => "FD_HOUR_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_HOUR_AGO_MOD_OTHER",
			));
			break;
		case "Hdiff":
			$hours_ago = intval(($now - $timestamp) / 60 / 60);
			$result .= _FormatDateMessage($hours_ago, array(
				"0" => "FD_HOUR_DIFF_0",
				"1" => "FD_HOUR_DIFF_1",
				"10_20" => "FD_HOUR_DIFF_10_20",
				"MOD_1" => "FD_HOUR_DIFF_MOD_1",
				"MOD_2_4" => "FD_HOUR_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_HOUR_DIFF_MOD_OTHER",
			));
			break;
		case "Hshort":
			$hours_ago = intval(($now - $timestamp) / 60 / 60);
			$result .= GetMessage("FD_HOUR_SHORT", array("#VALUE#" => $hours_ago));
			break;
		case "yesterday":
			$result .= GetMessage("FD_YESTERDAY");
			break;
		case "today":
			$result .= GetMessage("FD_TODAY");
			break;
		case "tommorow": // grammar error :)
		case "tomorrow":
			$result .= GetMessage("FD_TOMORROW");
			break;
		case "dago":
			$days_ago = intval(($now - $timestamp) / 60 / 60 / 24);
			$result .= _FormatDateMessage($days_ago, array(
				"0" => "FD_DAY_AGO_0",
				"1" => "FD_DAY_AGO_1",
				"10_20" => "FD_DAY_AGO_10_20",
				"MOD_1" => "FD_DAY_AGO_MOD_1",
				"MOD_2_4" => "FD_DAY_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_DAY_AGO_MOD_OTHER",
			));
			break;
		case "ddiff":
			$days_ago = intval(($now - $timestamp) / 60 / 60 / 24);
			$result .= _FormatDateMessage($days_ago, array(
				"0" => "FD_DAY_DIFF_0",
				"1" => "FD_DAY_DIFF_1",
				"10_20" => "FD_DAY_DIFF_10_20",
				"MOD_1" => "FD_DAY_DIFF_MOD_1",
				"MOD_2_4" => "FD_DAY_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_DAY_DIFF_MOD_OTHER",
			));
			break;
		case "dshort":
			$days_ago = intval(($now - $timestamp) / 60 / 60 / 24);
			$result .= GetMessage("FD_DAY_SHORT", array("#VALUE#" => $days_ago));
			break;
		case "mago":
			$months_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 31);
			$result .= _FormatDateMessage($months_ago, array(
				"0" => "FD_MONTH_AGO_0",
				"1" => "FD_MONTH_AGO_1",
				"10_20" => "FD_MONTH_AGO_10_20",
				"MOD_1" => "FD_MONTH_AGO_MOD_1",
				"MOD_2_4" => "FD_MONTH_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_MONTH_AGO_MOD_OTHER",
			));
			break;
		case "mdiff":
			$months_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 31);
			$result .= _FormatDateMessage($months_ago, array(
				"0" => "FD_MONTH_DIFF_0",
				"1" => "FD_MONTH_DIFF_1",
				"10_20" => "FD_MONTH_DIFF_10_20",
				"MOD_1" => "FD_MONTH_DIFF_MOD_1",
				"MOD_2_4" => "FD_MONTH_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_MONTH_DIFF_MOD_OTHER",
			));
			break;
		case "mshort":
			$months_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 31);
			$result .= GetMessage("FD_MONTH_SHORT", array("#VALUE#" => $months_ago));
			break;
		case "Yago":
			$years_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 365);
			$result .= _FormatDateMessage($years_ago, array(
				"0" => "FD_YEARS_AGO_0",
				"1" => "FD_YEARS_AGO_1",
				"10_20" => "FD_YEARS_AGO_10_20",
				"MOD_1" => "FD_YEARS_AGO_MOD_1",
				"MOD_2_4" => "FD_YEARS_AGO_MOD_2_4",
				"MOD_OTHER" => "FD_YEARS_AGO_MOD_OTHER",
			));
			break;
		case "Ydiff":
			$years_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 365);
			$result .= _FormatDateMessage($years_ago, array(
				"0" => "FD_YEARS_DIFF_0",
				"1" => "FD_YEARS_DIFF_1",
				"10_20" => "FD_YEARS_DIFF_10_20",
				"MOD_1" => "FD_YEARS_DIFF_MOD_1",
				"MOD_2_4" => "FD_YEARS_DIFF_MOD_2_4",
				"MOD_OTHER" => "FD_YEARS_DIFF_MOD_OTHER",
			));
			break;
		case "Yshort":
			$years_ago = intval(($now - $timestamp) / 60 / 60 / 24 / 365);
			$result .= _FormatDateMessage($years_ago, array(
				"0" => "FD_YEARS_SHORT_0",
				"1" => "FD_YEARS_SHORT_1",
				"10_20" => "FD_YEARS_SHORT_10_20",
				"MOD_1" => "FD_YEARS_SHORT_MOD_1",
				"MOD_2_4" => "FD_YEARS_SHORT_MOD_2_4",
				"MOD_OTHER" => "FD_YEARS_SHORT_MOD_OTHER",
			));
			break;
		case "F":
			if($currentLanguage == "en")
				$result .= date($format_part, $timestamp);
			else
				$result .= GetMessage("MONTH_".date("n", $timestamp)."_S");
			break;
		case "f":
			if($currentLanguage == "en")
				$result .= date("F", $timestamp);
			else
				$result .= GetMessage("MONTH_".date("n", $timestamp));
			break;
		case "M":
			if($currentLanguage == "en")
				$result .= date($format_part, $timestamp);
			else
				$result .= GetMessage("MON_".date("n", $timestamp));
			break;
		case "l":
			if($currentLanguage == "en")
				$result .= date($format_part, $timestamp);
			else
				$result .= GetMessage("DAY_OF_WEEK_".date("w", $timestamp));
			break;
		case "D":
			if($currentLanguage == "en")
				$result .= date($format_part, $timestamp);
			else
				$result .= GetMessage("DOW_".date("w", $timestamp));
			break;
		case "j":
			$dayOfMonth = date("j", $timestamp);
			$dayPattern = GetMessage("DOM_PATTERN");
			if ($dayPattern)
			{
				$result .= str_replace("#DAY#", $dayOfMonth, $dayPattern);
			}
			else
			{
				$result .= $dayOfMonth;
			}
			break;
		case "Y":
			$year = date("Y", $timestamp);
			$yearPattern = GetMessage("YEAR_PATTERN");
			if ($yearPattern)
			{
				$result .= str_replace("#YEAR#", $year, $yearPattern);
			}
			else
			{
				$result .= $year;
			}
			break;
		case "x":
			$ampm = IsAmPmMode(true);
			$timeFormat = ($ampm === AM_PM_LOWER? "g:i a" : ($ampm === AM_PM_UPPER? "g:i A" : "H:i"));
			$formats = array();
			$formats["tomorrow"] =  "tomorrow, ".$timeFormat;
			$formats["-"] = preg_replace('/:s/', '', $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
			$formats["s"] = "sago";
			$formats["i"] = "iago";
			$formats["today"] = "today, ".$timeFormat;
			$formats["yesterday"] = "yesterday, ".$timeFormat;
			$formats[""] = preg_replace('/:s/', '', $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
			$result .= FormatDate($formats, $timestamp, $now);
			break;
		case "X":
			$day = FormatDate(array(
				"tomorrow" => "tomorrow",
				"-" => $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")),
				"today" => "today",
				"yesterday" => "yesterday",
				"" => $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")),
			), $timestamp, $now);

			$ampm = IsAmPmMode(true);
			$timeFormat = ($ampm === AM_PM_LOWER? "g:i a" : ($ampm === AM_PM_UPPER? "g:i A" : "H:i"));
			$formats = array();
			$formats["tomorrow"] = $timeFormat;
			$formats["today"] = $timeFormat;
			$formats["yesterday"] = $timeFormat;
			$formats[""] = "";
			$time = FormatDate($formats, $timestamp, $now);

			if($time <> '')
			{
				$result .= GetMessage("FD_DAY_AT_TIME", array("#DAY#" => $day, "#TIME#" => $time));
			}
			else
			{
				$result .= $day;
			}
			break;
		case "Q":
			$days_ago = intval(($now - $timestamp) / 60 / 60 / 24);
			if($days_ago == 0)
				$result .= GetMessage("FD_DAY_DIFF_1", array("#VALUE#" => 1));
			else
				$result .= FormatDate(array(
					"d" => "ddiff",
					"m" => "mdiff",
					"" => "Ydiff",
				), $timestamp, $now);
			break;
		default:
			$result .= date($format_part, $timestamp);
			break;
		}
	}

	if ($bCutZeroTime)
		$result = preg_replace(
			array("/\\s*00:00:00\\s*/", "/(\\d\\d:\\d\\d)(:00)/", "/(\\s*00:00\\s*)(?!:)/"),
			array("", "\\1", ""),
			$result
		);

	return $result;
}

function FormatDateEx($strDate, $format=false, $new_format=false)
{
	$strDate = trim($strDate);

	if (false === $new_format) $new_format = CSite::GetDateFormat('FULL');

	$new_format = str_replace("MI","I", $new_format);
	$new_format = preg_replace("/([DMYIHGST])\\1+/is".BX_UTF_PCRE_MODIFIER, "\\1", $new_format);

	$arParsedDate = ParseDateTime($strDate);

	if (isset($arParsedDate["MMMM"]))
	{
		if (is_numeric($arParsedDate["MMMM"]))
		{
			$arParsedDate["MM"] = intval($arParsedDate["MMMM"]);
		}
		else
		{
			$arParsedDate["MM"] = GetNumMonth($arParsedDate["MMMM"]);
			if (!$arParsedDate["MM"])
				$arParsedDate["MM"] = intval(date('m', strtotime($arParsedDate["MMMM"])));
		}
	}
	elseif (isset($arParsedDate["MM"]))
	{
		$arParsedDate["MM"] = intval($arParsedDate["MM"]);
	}
	elseif (isset($arParsedDate["M"]))
	{
		if (is_numeric($arParsedDate["M"]))
		{
			$arParsedDate["MM"] = intval($arParsedDate["M"]);
		}
		else
		{
			$arParsedDate["MM"] = GetNumMonth($arParsedDate["M"], true);
			if (!$arParsedDate["MM"])
				$arParsedDate["MM"] = intval(date('m', strtotime($arParsedDate["M"])));
		}
	}

	if (isset($arParsedDate["H"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["H"]);
	}
	elseif (isset($arParsedDate["GG"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["GG"]);
	}
	elseif (isset($arParsedDate["G"]))
	{
		$arParsedDate["HH"] = intval($arParsedDate["G"]);
	}

	if (isset($arParsedDate['TT']) || isset($arParsedDate['T']))
	{
		$middletime = $arParsedDate['TT'] ?? $arParsedDate['T'];
		if (strcasecmp('pm', $middletime)===0)
		{
			if ($arParsedDate["HH"] < 12)
				$arParsedDate["HH"] += 12;
			else
				$arParsedDate["HH"] -= 12;
		}
	}

	if (isset($arParsedDate["YYYY"]))
		$arParsedDate["YY"] = $arParsedDate["YYYY"];

	if (intval($arParsedDate["DD"])<=0 || intval($arParsedDate["MM"])<=0 || intval($arParsedDate["YY"])<=0)
		return false;

	$strResult = "";

	if(intval($arParsedDate["YY"])>1970 && intval($arParsedDate["YY"])<2038)
	{
		$ux_time = mktime(
			intval($arParsedDate["HH"] ?? 0),
			intval($arParsedDate["MI"] ?? 0),
			intval($arParsedDate["SS"] ?? 0),
			intval($arParsedDate["MM"] ?? 0),
			intval($arParsedDate["DD"] ?? 0),
			intval($arParsedDate["YY"] ?? 0)
		);

		$new_format_l = mb_strlen($new_format);
		$dontChange = false;

		for ($i = 0; $i < $new_format_l; $i++)
		{
			$simbol = mb_substr($new_format, $i, 1);

			if (!$dontChange && $simbol === "\\")
			{
				$dontChange = true;
				continue;
			}

			if ($dontChange)
			{
				$match = $simbol;
			}
			else
			{
				switch ($simbol)
				{
					case "F":
						$match=GetMessage("MONTH_".date("n", $ux_time)."_S");
						break;
					case "f":
						$match=GetMessage("MONTH_".date("n", $ux_time));
						break;
					case "M":
						$match=GetMessage("MON_".date("n", $ux_time));
						break;
					case "l":
						$match=GetMessage("DAY_OF_WEEK_".date("w", $ux_time));
						break;
					case "D":
						$match=GetMessage("DOW_".date("w", $ux_time));
						break;
					case "j":
						$match = date(mb_substr($new_format, $i, 1), $ux_time);
						$dayPattern = GetMessage("DOM_PATTERN");
						if ($dayPattern)
						{
							$match = str_replace("#DAY#", $match, $dayPattern);
						}
						break;
					default:
						$match = date(mb_substr($new_format, $i, 1), $ux_time);
						break;
				}
			}

			$strResult .= $match;
			$dontChange = false;
		}
	}
	else
	{
		if($arParsedDate["MM"]<1 || $arParsedDate["MM"]>12)
			$arParsedDate["MM"] = 1;
		$new_format_l = mb_strlen($new_format);
		$dontChange = false;

		for ($i = 0; $i < $new_format_l; $i++)
		{
			$simbol = mb_substr($new_format, $i, 1);

			if (!$dontChange && $simbol === "\\")
			{
				$dontChange = true;
				continue;
			}

			if ($dontChange)
			{
				$match = $simbol;
			}
			else
			{
				switch ($simbol)
				{
					case "F":
					case "f":
						$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
						if (intval($arParsedDate["MM"]) > 0)
							$match=GetMessage("MONTH_".intval($arParsedDate["MM"]).($simbol == 'F' ? '_S' : ''));
						break;
					case "M":
						$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
						if (intval($arParsedDate["MM"]) > 0)
							$match=GetMessage("MON_".intval($arParsedDate["MM"]));
						break;
					case "l":
						$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
						if (intval($arParsedDate["DD"]) > 0)
							$match = GetMessage("DAY_OF_WEEK_".intval($arParsedDate["DD"]));
						break;
					case "D":
						$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
						if (intval($arParsedDate["DD"]) > 0)
							$match = GetMessage("DOW_".intval($arParsedDate["DD"]));
						break;
					case "d":
						$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
						break;
					case "m":
						$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
						break;
					case "j":
						$match = intval($arParsedDate["DD"]);
						$dayPattern = GetMessage("DOM_PATTERN");
						if ($dayPattern)
						{
							$match = str_replace("#DAY#", $match, $dayPattern);
						}
						break;
					case "Y":
						$match = str_pad($arParsedDate["YY"], 4, "0", STR_PAD_LEFT);
						break;
					case "y":
						$match = mb_substr($arParsedDate["YY"], 2);
						break;
					case "H":
						$match = str_pad($arParsedDate["HH"], 2, "0", STR_PAD_LEFT);
						break;
					case "i":
						$match = str_pad($arParsedDate["MI"], 2, "0", STR_PAD_LEFT);
						break;
					case "s":
						$match = str_pad($arParsedDate["SS"], 2, "0", STR_PAD_LEFT);
						break;
					case "g":
						$match = intval($arParsedDate["HH"]);
						if ($match > 12)
							$match = $match-12;
						break;
					case "a":
					case "A":
						$match = intval($arParsedDate["HH"]);
						if ($match > 12)
							$match = ($match-12)." PM";
						else
							$match .= " AM";

						if (mb_substr($new_format, $i, 1) == "a")
							$match = mb_strtolower($match);
						break;
					default:
						$match = mb_substr($new_format, $i, 1);
						break;
				}
			}

			$strResult .= $match;
			$dontChange = false;
		}
	}
	return $strResult;
}

function FormatDateFromDB ($date, $format = 'FULL', $phpFormat = false)
{
	global $DB;

	if ($format == 'FULL' || $format == 'SHORT')
		return FormatDate($DB->DateFormatToPHP(CSite::GetDateFormat($format)), MakeTimeStamp($date));
	else
		return FormatDate(($phpFormat ? $format : $DB->DateFormatToPHP($format)), MakeTimeStamp($date));
}

/**
 * @deprecated Use Type\DateTime.
 */
function GetTime($timestamp, $type="SHORT", $site=false, $bSearchInSitesOnly = false)
{
	global $DB;

	if ($site === false  && ($context = Context::getCurrent()) !== null && ($culture = $context->getCulture()) !== null)
	{
		$format = ($type == "FULL" ? $culture->getFormatDatetime() : $culture->getFormatDate());
	}
	else
	{
		$format = CSite::GetDateFormat($type, $site, $bSearchInSitesOnly);
	}
	return date($DB->DateFormatToPHP($format), $timestamp);
}

/**
 * @deprecated Use Type\DateTime.
 */
function AddTime($stmp, $add, $type="D")
{
	$ret = $stmp;
	switch ($type)
	{
		case "H":
			$ret = mktime(
				date("H",$stmp)+$add,date("i",$stmp),date("s",$stmp),
				date("m",$stmp),date("d",$stmp),date("Y",$stmp));
			break;
		case "M":
			$ret = mktime(
				date("H",$stmp),date("i",$stmp)+$add,date("s",$stmp),
				date("m",$stmp),date("d",$stmp),date("Y",$stmp));
			break;
		case "S":
			$ret = mktime(
				date("H",$stmp),date("i",$stmp),date("s",$stmp)+$add,
				date("m",$stmp),date("d",$stmp),date("Y",$stmp));
			break;
		case "D":
			$ret = mktime(
				date("H",$stmp),date("i",$stmp),date("s",$stmp),
				date("m",$stmp),date("d",$stmp)+$add,date("Y",$stmp));
			break;
		case "MN":
			$ret = mktime(
				date("H",$stmp),date("i",$stmp),date("s",$stmp),
				date("m",$stmp)+$add,date("d",$stmp),date("Y",$stmp));
			break;
		case "Y":
			$ret = mktime(
				date("H",$stmp),date("i",$stmp),date("s",$stmp),
				date("m",$stmp),date("d",$stmp),date("Y",$stmp)+$add);
			break;
	}
	return $ret;
}

/**
 * @deprecated
 */
function ParseDate($strDate, $format="dmy")
{
	$day = $month = $year = 0;
	$args = preg_split('#[/.-]#', $strDate);
	$bound = min(mb_strlen($format), count($args));
	for($i=0; $i<$bound; $i++)
	{
		if($format[$i] == 'm') $month = intval($args[$i]);
		elseif($format[$i] == 'd') $day = intval($args[$i]);
		elseif($format[$i] == 'y') $year = intval($args[$i]);
	}
	return (checkdate($month, $day, $year) ? array($day, $month, $year) : 0);
}

/**
 * @deprecated
 */
function MkDateTime($strDT, $format="d.m.Y H:i:s")
{
	static $arr = ["d.m.Y", "d.m.Y H:i", "d.m.Y H:i:s"];

	if (!(in_array($format, $arr)))
	{
		return false;
	}

	$strDT = preg_replace("/[\n\r\t ]+/", " ", $strDT);

	$dateTime = explode(" ", $strDT);

	$date  = trim($dateTime[0] ?? '');
	$time  = trim($dateTime[1] ?? '');

	$dayMonthYear = explode(".", $date);

	$day = intval($dayMonthYear[0] ?? 0);
	$month = intval($dayMonthYear[1] ?? 0);
	$year = intval($dayMonthYear[2] ?? 0);

	$hourMinSec = explode(":", $time);

	$hour = intval($hourMinSec[0] ?? 0);
	$min = intval($hourMinSec[1] ?? 0);
	$sec = intval($hourMinSec[2] ?? 0);

	if (!checkdate($month, $day, $year))
	{
		return false;
	}
	if ($hour > 24 || $hour < 0 || $min < 0 || $min > 59 || $sec < 0 || $sec > 59)
	{
		return false;
	}

	$ts = mktime($hour, $min, $sec, $month, $day, $year);

	if($ts <= 0)
	{
		return false;
	}

	return $ts;
}

/**
 * @deprecated
 */
function PHPFormatDateTime($strDateTime, $format="d.m.Y H:i:s")
{
	return date($format, MkDateTime(FmtDate($strDateTime,"D.M.Y H:I:S"), "d.m.Y H:i:s"));
}

/**
 * Array functions
 */

/*
удал€ет дубли в массиве сортировки
массив
Array
(
	[0] => T.NAME DESC
	[1] => T.NAME ASC
	[2] => T.ID ASC
	[3] => T.ID DESC
	[4] => T.DESC
)
преобразует в
Array
(
	[0] => T.NAME DESC
	[1] => T.ID ASC
	[2] => T.DESC ASC
)
*/
function DelDuplicateSort(&$arSort)
{
	if (is_array($arSort) && !empty($arSort))
	{
		$arSort2 = array();
		foreach($arSort as $val)
		{
			$arSort1 = explode(" ", trim($val));
			$order = array_pop($arSort1);
			$order_ = mb_strtoupper(trim($order));
			if (!($order_=="DESC" || $order_=="ASC"))
			{
				$arSort1[] = $order;
				$order_ = "";
			}
			$by = implode(" ", $arSort1);
			if($by <> '' && !array_key_exists($by, $arSort2))
				$arSort2[$by] = $order_;
		}
		$arSort = array();
		foreach($arSort2 as $by=>$order)
			$arSort[] = $by." ".$order;
	}
}

function array_convert_name_2_value($arr)
{
	$arr_res = array();
	if (is_array($arr))
	{
		foreach($arr as $key => $value)
		{
			global $$value;
			$arr_res[$key] = $$value;
		}
	}
	return $arr_res;
}

function InitBVarFromArr($arr)
{
	if (is_array($arr) && !empty($arr))
	{
		foreach($arr as $value)
		{
			global $$value;
			$$value = ($$value=="Y") ? "Y" : "N";
		}
	}
}

function TrimArr(&$arr, $trim_value=false)
{
	if(!is_array($arr))
		return false;

	$found = false;
	foreach($arr as $key => $value)
	{
		if ($trim_value)
		{
			$arr[$key] = trim($value);
		}
		if (trim($value) == '')
		{
			unset($arr[$key]);
			$found = true;
		}
	}
	return ($found) ? true : false;
}

function is_set($a, $k = false)
{
	if ($k === false && func_num_args() == 1)
	{
		return isset($a);
	}

	if (is_array($a))
	{
		return isset($a[$k]) || array_key_exists($k, $a);
	}

	return false;
}

/**
 * @deprecated Use Main\Security\Random
 * @param int $pass_len
 * @param bool $pass_chars
 * @return string
 */
function randString($pass_len=10, $pass_chars=false)
{
	if(is_array($pass_chars))
	{
		return Security\Random::getStringByArray($pass_len, $pass_chars);
	}
	else
	{
		if($pass_chars !== false)
		{
			return Security\Random::getStringByCharsets($pass_len, $pass_chars);
		}
		else
		{
			// Random::ALPHABET_NUM | Random::ALPHABET_ALPHALOWER | Random::ALPHABET_ALPHAUPPER
			return Security\Random::getString($pass_len, true);
		}
	}
}

/**
 * Alias for randString()
 * @deprecated Use Main\Security\Random
 * @param int $len
 * @return string
 */
function GetRandomCode($len=8)
{
	return randString($len);
}

function TruncateText($strText, $intLen)
{
	if(mb_strlen($strText) > $intLen)
		return rtrim(mb_substr($strText, 0, $intLen), ".")."...";
	else
		return $strText;
}

function InsertSpaces($sText, $iMaxChar=80, $symbol=" ", $bHTML=false)
{
	$iMaxChar = intval($iMaxChar);
	if ($iMaxChar > 0 && mb_strlen($sText) > $iMaxChar)
	{
		if ($bHTML)
		{
			$obSpacer = new CSpacer($iMaxChar, $symbol);
			return $obSpacer->InsertSpaces($sText);
		}
		else
		{
			return preg_replace("/([^() \\n\\r\\t%!?{}\\][-]{".$iMaxChar."})/".BX_UTF_PCRE_MODIFIER,"\\1".$symbol, $sText);
		}
	}
	return $sText;
}


function TrimExAll($str,$symbol)
{
	while (mb_substr($str, 0, 1) == $symbol or mb_substr($str, mb_strlen($str) - 1, 1) == $symbol)
		$str = TrimEx($str,$symbol);

	return $str;
}

function TrimEx($str,$symbol,$side="both")
{
	$str = trim($str);
	if ($side=="both")
	{
		if (mb_substr($str, 0, 1) == $symbol) $str = mb_substr($str, 1, mb_strlen($str));
		if (mb_substr($str, mb_strlen($str) - 1, 1) == $symbol) $str = mb_substr($str, 0, mb_strlen($str) - 1);
	}
	elseif ($side=="left")
	{
		if (mb_substr($str, 0, 1) == $symbol) $str = mb_substr($str, 1, mb_strlen($str));
	}
	elseif ($side=="right")
	{
		if (mb_substr($str, mb_strlen($str) - 1, 1) == $symbol) $str = mb_substr($str, 0, mb_strlen($str) - 1);
	}
	return $str;
}

/**
 * @deprecated Use Main\Text\Encoding::convertEncoding()
 * @param $s
 * @return mixed
 */
function utf8win1251($s)
{
	return Main\Text\Encoding::convertEncoding($s, "UTF-8", "Windows-1251");
}

/**
 * @deprecated
 * @param $str
 * @param false $lang
 * @return string
 */
function ToUpper($str, $lang = false)
{
	if(!defined("BX_CUSTOM_TO_UPPER_FUNC"))
	{
		return mb_strtoupper($str);
	}
	else
	{
		$func = BX_CUSTOM_TO_UPPER_FUNC;
		return $func($str);
	}
}

/**
 * @deprecated
 * @param $str
 * @param false $lang
 * @return string
 */
function ToLower($str, $lang = false)
{
	if(!defined("BX_CUSTOM_TO_LOWER_FUNC"))
	{
		return mb_strtolower($str);
	}
	else
	{
		$func = BX_CUSTOM_TO_LOWER_FUNC;
		return $func($str);
	}
}

function convert_code_tag_for_email($text="", $arMsg=array())
{
	if ($text == '')
		return '';

	$helper = new CConvertorsPregReplaceHelper($arMsg["MAIN_CODE_S"]);
	return $helper->convertCodeTagForEmail($text);
}

function PrepareTxtForEmail($text, $lang=false, $convert_url_tag=true, $convert_image_tag=true)
{
	$text = trim($text);
	if($text == '')
		return "";

	if($lang===false)
		$lang = LANGUAGE_ID;

	$arMsg = IncludeModuleLangFile(__FILE__, $lang, true);
	$helper = new CConvertorsPregReplaceHelper($arMsg["MAIN_CODE_S"]);

	$text = preg_replace("#<code(\\s+[^>]*>|>)(.+?)</code(\\s+[^>]*>|>)#is", "[code]\\2[/code]", $text);
	$text = preg_replace_callback("#\\[code(\\s+[^\\]]*\\]|\\])(.+?)\\[/code(\\s+[^\\]]*\\]|\\])#is", array($helper, "convertCodeTagForEmail"), $text);

	$text = preg_replace("/^(\r|\n)+?(.*)$/", "\\2", $text);
	$text = preg_replace("#<b>(.+?)</b>#is", "\\1", $text);
	$text = preg_replace("#<i>(.+?)</i>#is", "\\1", $text);
	$text = preg_replace("#<u>(.+?)</u>#is", "_\\1_", $text);
	$text = preg_replace("#\\[b\\](.+?)\\[/b\\]#is", "\\1", $text);
	$text = preg_replace("#\\[i\\](.+?)\\[/i\\]#is", "\\1", $text);
	$text = preg_replace("#\\[u\\](.+?)\\[/u\\]#is", "_\\1_", $text);

	$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);

	$s = "-------------- ".$arMsg["MAIN_QUOTE_S"]." -----------------";
	$text = preg_replace("#\\[quote(.*?)\\]#is", "\n>".$s."\n", $text);
	$text = preg_replace("#\\[/quote(.*?)\\]#is", "\n>".str_repeat("-", mb_strlen($s))."\n", $text);

	if($convert_url_tag)
	{
		$text = preg_replace("#<a[^>]*href=[\"']?([^>\"' ]+)[\"']?[^>]*>(.+?)</a>#is", "\\2 (URL: \\1)", $text);
		$text = preg_replace("#\\[url\\](\\S+?)\\[/url\\]#is", "(URL: \\1)", $text);
		$text = preg_replace("#\\[url\\s*=\\s*(\\S+?)\\s*\\](.*?)\\[\\/url\\]#is", "\\2 (URL: \\1)", $text);
	}

	if($convert_image_tag)
	{
		$text = preg_replace("#<img[^>]*src=[\"']?([^>\"' ]+)[\"']?[^>]*>#is", " (IMAGE: \\1) ", $text);
		$text = preg_replace("#\\[img\\](.+?)\\[/img\\]#is", " (IMAGE: \\1) ", $text);
	}

	$text = preg_replace("#<ul(\\s+[^>]*>|>)#is", "\n", $text);
	$text = preg_replace("#<ol(\\s+[^>]*>|>)#is", "\n", $text);
	$text = preg_replace("#<li(\\s+[^>]*>|>)#is", " [*] ", $text);
	$text = preg_replace("#</li>#is", "", $text);
	$text = preg_replace("#</ul>#is", "\n\n", $text);
	$text = preg_replace("#</ol>#is", "\n\n", $text);

	$text = preg_replace("#\\[list\\]#is", "\n", $text);
	$text = preg_replace("#\\[/list\\]#is", "\n", $text);

	$text = preg_replace("#<br>#is", "\n", $text);
	$text = preg_replace("#<wbr>#is", "", $text);

	//$text = preg_replace("#<.+?".">#", "", $text);

	$text = str_replace("&quot;", "\"", $text);
	$text = str_replace("&#092;", "\\", $text);
	$text = str_replace("&#036;", "\$", $text);
	$text = str_replace("&#33;", "!", $text);
	$text = str_replace("&#39;", "'", $text);
	$text = str_replace("&lt;", "<", $text);
	$text = str_replace("&gt;", ">", $text);
	$text = str_replace("&nbsp;", " ", $text);
	$text = str_replace("&#124;", '|', $text);
	$text = str_replace("&amp;", "&", $text);

	return $text;
}

function delete_special_symbols($text, $replace="")
{
	static $arr = array(
		"\x1",		// спецсимвол дл€ преобразовани€ URL'ов протокола http, https, ftp
		"\x2",		// спецсимвол дл€ пробела ($iMaxStringLen)
		"\x3",		// спецсимвол дл€ преобразовани€ URL'ов протокола mailto
		"\x4",		// спецсимвол замен€ющий \n (используетс€ дл€ преобразовани€ <code>)
		"\x5",		// спецсимвол замен€ющий \r (используетс€ дл€ преобразовани€ <code>)
		"\x6",		// спецсимвол замен€ющий пробел (используетс€ дл€ преобразовани€ <code>)
		"\x7",		// спецсимвол замен€ющий табул€цию (используетс€ дл€ преобразовани€ <code>)
		"\x8",		// спецсимвол замен€ющий слэш "\"
	);
	return str_replace($arr, $replace, $text);
}

function convert_code_tag_for_html_before($text = "")
{
	$helper = new CConvertorsPregReplaceHelper("");
	return $helper->convertCodeTagForHtmlBefore(stripslashes($text));
}

function convert_code_tag_for_html_after($text = "", $code_table_class, $code_head_class, $code_body_class, $code_textarea_class)
{
	if ($text == '')
		return '';
	$helper = new CConvertorsPregReplaceHelper("");
	$helper->setCodeClasses($code_table_class, $code_head_class, $code_body_class, $code_textarea_class);
	return $helper->convertCodeTagForHtmlAfter(stripslashes($text));
}

function convert_open_quote_tag($quote_table_class, $quote_head_class, $quote_body_class)
{
	global $QUOTE_OPENED;
	$QUOTE_OPENED++;
	return "<table class='$quote_table_class' width='95%' border='0' cellpadding='3' cellspacing='1'><tr><td class='".$quote_head_class."'>".GetMessage("MAIN_QUOTE")."</td></tr><tr><td class='".$quote_body_class."'>";
}

function convert_close_quote_tag()
{
	global $QUOTE_ERROR, $QUOTE_OPENED, $QUOTE_CLOSED;
	if ($QUOTE_OPENED == 0)
	{
		$QUOTE_ERROR++;
		return '';
	}
	$QUOTE_CLOSED++;
	return "</td></tr></table>";
}

function convert_quote_tag($text="", $quote_table_class, $quote_head_class, $quote_body_class)
{
	global $QUOTE_ERROR, $QUOTE_OPENED, $QUOTE_CLOSED;
	if ($text == '')
		return '';
	$text = stripslashes($text);
	$helper = new CConvertorsPregReplaceHelper("");
	$helper->setQuoteClasses($quote_table_class, $quote_head_class, $quote_body_class);
	$txt = $text;
	$txt = preg_replace_callback("#\\[quote\\]#i",  array($helper, "convertOpenQuoteTag"),  $txt);
	$txt = preg_replace_callback("#\\[/quote\\]#i", array($helper, "convertCloseQuoteTag"), $txt);
	if ($helper->checkQuoteError())
	{
		return $txt;
	}
	else
	{
		return $text;
	}
}

function extract_url($s)
{
	$s2 = '';
	while(mb_strpos(",}])>.", mb_substr($s, -1, 1)) !== false)
	{
		$s2 = mb_substr($s, -1, 1);
		$s = mb_substr($s, 0, mb_strlen($s) - 1);
	}
	$res = chr(1).$s."/".chr(1).$s2;
	return $res;
}

function convert_to_href($url, $link_class="", $event1="", $event2="", $event3="", $script="", $link_target="_self")
{
	$url = stripslashes($url);

	$target = $link_target == '_self'? '': ' target="'.$link_target.'"';

	$s = "<a class=\"".$link_class."\" href=\"".delete_special_symbols($url)."\"".$target.">".$url."</a>";

	return $s;
}

// используетс€ как вспомогательна€ функци€ дл€ TxtToHTML
function convert_to_mailto($s, $link_class="")
{
	$s = stripslashes($s);
	$s = "<a class=\"".$link_class."\" href=\"mailto:".delete_special_symbols($s)."\" title=\"".GetMessage("MAIN_MAILTO")."\">".$s."</a>";
	return $s;
}

function TxtToHTML(
	$str,                                    // текст дл€ преобразовани€
	$bMakeUrls             = true,           // true - преобразовавыть URL в <a href="URL">URL</a>
	$iMaxStringLen         = 0,              // максимальна€ длина фразы без пробелов или символов перевода каретки
	$QUOTE_ENABLED         = "N",            // Y - преобразовать <QUOTE>...</QUOTE> в рамку цитаты
	$NOT_CONVERT_AMPERSAND = "Y",            // Y - не преобразовывать символ "&" в "&amp;"
	$CODE_ENABLED          = "N",            // Y - преобразовать <CODE>...</CODE> в readonly textarea
	$BIU_ENABLED           = "N",            // Y - преобразовать <B>...</B> и т.д. в соответствующие HTML тэги
	$quote_table_class     = "quotetable",   // css класс на таблицу цитаты
	$quote_head_class      = "tdquotehead",  // css класс на первую TD таблицы цитаты
	$quote_body_class      = "tdquote",      // css класс на вторую TD таблицы цитаты
	$code_table_class      = "codetable",    // css класс на таблицу кода
	$code_head_class       = "tdcodehead",   // css класс на первую TD таблицы кода
	$code_body_class       = "tdcodebody",   // css класс на вторую TD таблицы кода
	$code_textarea_class   = "codetextarea", // css класс на textarea в таблице кода
	$link_class            = "txttohtmllink",// css класс на ссылках
	$arUrlEvent            = array(),        // deprecated
	$link_target           = "_self"         // tagret открыти€ страницы
)
{
	global $QUOTE_ERROR, $QUOTE_OPENED, $QUOTE_CLOSED;
	$QUOTE_ERROR = $QUOTE_OPENED = $QUOTE_CLOSED = 0;

	$str = delete_special_symbols($str);

	// вставим спецсимвол chr(2) там где в дальнейшем необходимо вставить пробел
	if($iMaxStringLen>0)
		$str = InsertSpaces($str, $iMaxStringLen, chr(2), true);

	// \ => chr(8)
	$str = str_replace("\\", chr(8), $str); // спецсимвол замен€ющий слэш "\"

	// <quote>...</quote> => [quote]...[/quote]
	if ($QUOTE_ENABLED=="Y")
		$str = preg_replace("#(?:<|\\[)(/?)quote(.*?)(?:>|\\])#is", " [\\1quote]", $str);

	// <code>...</code> => [code]...[/code]
	// \n => chr(4)
	// \r => chr(5)
	if ($CODE_ENABLED=="Y")
	{
		$helper = new CConvertorsPregReplaceHelper("");
		$str = preg_replace("#<code(\\s+[^>]*>|>)(.+?)</code(\\s+[^>]*>|>)#is", "[code]\\2[/code]", $str);
		$str = preg_replace_callback("#\\[code(\\s+[^\\]]*\\]|\\])(.+?)\\[/code(\\s+[^\\]]*\\]|\\])#is", array($helper, "convertCodeTagForHtmlBefore"), $str);
	}

	// <b>...</b> => [b]...[/b]
	// <i>...</i> => [i]...[/i]
	// <u>...</u> => [u]...[/u]
	if ($BIU_ENABLED=="Y")
	{
		$str = preg_replace("#<b(\\s+[^>]*>|>)(.+?)</b(\\s+[^>]*>|>)#is", "[b]\\2[/b]", $str);
		$str = preg_replace("#<i(\\s+[^>]*>|>)(.+?)</i(\\s+[^>]*>|>)#is", "[i]\\2[/i]", $str);
		$str = preg_replace("#<u(\\s+[^>]*>|>)(.+?)</u(\\s+[^>]*>|>)#is", "[u]\\2[/u]", $str);
	}

	// URL => chr(1).URL."/".chr(1)
	// EMail => chr(3).E-Mail.chr(3)
	if($bMakeUrls)
	{
		//hide @ from next regexp with chr(11)
		$str = preg_replace_callback("#((http|https|ftp):\\/\\/[a-z:@,.'/\\#\\%=~\\&?*+\\[\\]_0-9\x01-\x08-]+)#is", array("CConvertorsPregReplaceHelper", "extractUrl"), $str);
		$str = preg_replace("#(([=_\\.'0-9a-z+~\x01-\x08-]+)@[_0-9a-z\x01-\x08-.]+\\.[a-z]{2,10})#is", chr(3)."\\1".chr(3), $str);
		//replace back to @
		$str = str_replace(chr(11), '@', $str);
	}

	// конвертаци€ критичных символов
	if ($NOT_CONVERT_AMPERSAND!="Y") $str = str_replace("&", "&amp;", $str);
	static $search=array("<",">","\"","'","%",")","(","+");
	static $replace=array("&lt;","&gt;","&quot;","&#39;","&#37;","&#41;","&#40;","&#43;");
	$str = str_replace($search, $replace, $str);

	// chr(1).URL."/".chr(1) => <a href="URL">URL</a>
	// chr(3).E-Mail.chr(3) => <a href="mailto:E-Mail">E-Mail</a>
	if($bMakeUrls)
	{
		$helper = new CConvertorsPregReplaceHelper("");
		$helper->setLinkClass($link_class);
		$helper->setLinkTarget($link_target);

		$str = preg_replace_callback("#\x01([^\n\x01]+?)/\x01#is", array($helper, "convertToHref"), $str);
		$str = preg_replace_callback("#\x03([^\n\x03]+?)\x03#is", array($helper, "convertToMailTo"), $str);
	}

	$str = str_replace("\r\n", "\n", $str);
	$str = str_replace("\n", "<br />\n", $str);
	$str = preg_replace("# {2}#", "&nbsp;&nbsp;", $str);
	$str = preg_replace("#\t#", "&nbsp;&nbsp;&nbsp;&nbsp;", $str);

	// chr(2) => " "
	if($iMaxStringLen>0)
		$str = str_replace(chr(2), "<wbr>", $str);

	// [quote]...[/quote] => <table>...</table>
	if ($QUOTE_ENABLED=="Y")
	{
		$helper = new CConvertorsPregReplaceHelper("");
		$helper->setQuoteClasses($quote_table_class, $quote_head_class, $quote_body_class);
		$str = preg_replace_callback("#(\\[quote(.*?)\\](.*)\\[/quote(.*?)\\])#is", array($helper, "convertQuoteTag"), $str);
	}

	// [code]...[/code] => <textarea>...</textarea>
	// chr(4) => \n
	// chr(5) => \r
	if ($CODE_ENABLED=="Y")
	{
		$helper = new CConvertorsPregReplaceHelper("");
		$helper->setCodeClasses($code_table_class, $code_head_class, $code_body_class, $code_textarea_class);
		$str = preg_replace_callback("#\\[code\\](.*?)\\[/code\\]#is", array($helper, "convertCodeTagForHtmlAfter"), $str);
		$str = str_replace(chr(4), "\n", $str);
		$str = str_replace(chr(5), "\r", $str);
		$str = str_replace(chr(6), " ", $str);
		$str = str_replace(chr(7), "\t", $str);
		$str = str_replace(chr(16), "[", $str);
		$str = str_replace(chr(17), "]", $str);
	}

	// [b]...[/b] => <b>...</b>
	// [i]...[/i] => <i>...</i>
	// [u]...[/u] => <u>...</u>
	if ($BIU_ENABLED=="Y")
	{
		$str = preg_replace("#\\[b\\](.*?)\\[/b\\]#is", "<b>\\1</b>", $str);
		$str = preg_replace("#\\[i\\](.*?)\\[/i\\]#is", "<i>\\1</i>", $str);
		$str = preg_replace("#\\[u\\](.*?)\\[/u\\]#is", "<u>\\1</u>", $str);
	}

	// chr(8) => \
	$str = str_replace(chr(8), "\\", $str);

	$str = delete_special_symbols($str);

	return $str;
}

/*********************************
Convertation of HTML to text
*********************************/

function HTMLToTxt($str, $strSiteUrl="", $aDelete=array(), $maxlen=70)
{
	//get rid of whitespace
	$str = preg_replace("/[\\t\\n\\r]/", " ", $str);

	//replace tags with placeholders
	static $search = array(
		"'<script[^>]*?>.*?</script>'si",
		"'<style[^>]*?>.*?</style>'si",
		"'<svg[^>]*?>.*?</svg>'si",
		"'<select[^>]*?>.*?</select>'si",
		"'&(quot|#34);'i",
		"'&(iexcl|#161);'i",
		"'&(cent|#162);'i",
		"'&(copy|#169);'i",
	);

	static $replace = array(
		"",
		"",
		"",
		"",
		"\"",
		"!",
		"c",
		"(c)",
	);

	$str = preg_replace($search, $replace, $str);

	$str = preg_replace("#<[/]{0,1}(b|i|u|em|small|strong)>#i", "", $str);
	$str = preg_replace("#<div[^>]*>#i", "\r\n", $str);
	$str = preg_replace("#<[/]{0,1}(font|div|span)[^>]*>#i", "", $str);

	//replace lists
	$str = preg_replace("#<ul[^>]*>#i", "\r\n", $str);
	$str = preg_replace("#<li[^>]*>#i", "\r\n  - ", $str);

	//delete by function parameter
	foreach ($aDelete as $del_reg)
	{
		$str = preg_replace($del_reg, "", $str);
	}

	//replace images
	$str = preg_replace("/(<img\\s[^>]*?src\\s*=\\s*)([\"']?)(\\/.*?)(\\2)(\\s.+?>|\\s*>)/is", "[".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
	$str = preg_replace("/(<img\\s[^>]*?src\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is", "[".chr(1)."\\3".chr(1)."] ", $str);

	//replace links
	$str = preg_replace("/(<a\\s[^>]*?href\\s*=\\s*)([\"']?)(\\/.*?)(\\2)(.*?>)(.*?)<\\/a>/is", "\\6 [".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
	$str = preg_replace("/(<a\\s[^>]*?href\\s*=\\s*)([\"']?)(.*?)(\\2)(.*?>)(.*?)<\\/a>/is", "\\6 [".chr(1)."\\3".chr(1)."] ", $str);

	//replace <br>
	$str = preg_replace("#<br[^>]*>#i", "\r\n", $str);

	//replace <p>
	$str = preg_replace("#<p[^>]*>#i", "\r\n\r\n", $str);

	//replace <hr>
	$str = preg_replace("#<hr[^>]*>#i", "\r\n----------------------\r\n", $str);

	//replace tables
	$str = preg_replace("#<[/]{0,1}(thead|tbody)[^>]*>#i", "", $str);
	$str = preg_replace("#<([/]{0,1})th[^>]*>#i", "<\\1td>", $str);

	$str = preg_replace("#</td>#i", "\t", $str);
	$str = preg_replace("#</tr>#i", "\r\n", $str);
	$str = preg_replace("#<table[^>]*>#i", "\r\n", $str);

	$str = preg_replace("#\r\n[ ]+#", "\r\n", $str);

	//remove all tags
	$str = preg_replace("#<[/]{0,1}[^>]+>#i", "", $str);

	$str = preg_replace("#[ ]+ #", " ", $str);
	$str = str_replace("\t", "    ", $str);

	//wrap long lines
	if ($maxlen > 0)
	{
		$str = preg_replace("#(^|[\\r\\n])([^\\n\\r]{".intval($maxlen)."}[^ \\r\\n]*[\\] ])([^\\r])#", "\\1\\2\r\n\\3", $str);
	}

	$str = str_replace(chr(1), " ", $str);

	return trim($str);
}

function FormatText($strText, $strTextType="text")
{
	if(strtolower($strTextType) == "html")
		return $strText;

	return TxtToHtml($strText);
}

function htmlspecialcharsEx($str)
{
	static $search =  array("&amp;",     "&lt;",     "&gt;",     "&quot;",     "&#34;",     "&#x22;",     "&#39;",     "&#x27;",     "<",    ">",    "\"");
	static $replace = array("&amp;amp;", "&amp;lt;", "&amp;gt;", "&amp;quot;", "&amp;#34;", "&amp;#x22;", "&amp;#39;", "&amp;#x27;", "&lt;", "&gt;", "&quot;");

	return Text\StringHelper::str_replace($search, $replace, $str);
}

function htmlspecialcharsback($str)
{
	static $search =  array("&lt;", "&gt;", "&quot;", "&apos;", "&amp;");
	static $replace = array("<",    ">",    "\"",     "'",      "&");

	return Text\StringHelper::str_replace($search, $replace, $str);
}

function htmlspecialcharsbx($string, $flags = ENT_COMPAT, $doubleEncode = true)
{
	//function for php 5.4 where default encoding is UTF-8
	return htmlspecialchars((string)$string, $flags, (defined("BX_UTF")? "UTF-8" : "ISO-8859-1"), $doubleEncode);
}

function CheckDirPath($path)
{
	//remove file name
	if(mb_substr($path, -1) != "/")
	{
		$p = mb_strrpos($path, "/");
		$path = mb_substr($path, 0, $p);
	}

	$path = rtrim($path, "/");

	if($path == "")
	{
		//current folder always exists
		return true;
	}

	if(!file_exists($path))
	{
		return mkdir($path, BX_DIR_PERMISSIONS, true);
	}

	return is_dir($path);
}

function CopyDirFiles($path_from, $path_to, $ReWrite = true, $Recursive = false, $bDeleteAfterCopy = false, $strExclude = "")
{
	if (mb_strpos($path_to."/", $path_from."/") === 0 || realpath($path_to) === realpath($path_from))
		return false;

	if (is_dir($path_from))
	{
		CheckDirPath($path_to."/");
	}
	elseif(is_file($path_from))
	{
		$p = bxstrrpos($path_to, "/");
		$path_to_dir = mb_substr($path_to, 0, $p);
		CheckDirPath($path_to_dir."/");

		if (file_exists($path_to) && !$ReWrite)
			return false;

		@copy($path_from, $path_to);
		if(is_file($path_to))
			@chmod($path_to, BX_FILE_PERMISSIONS);

		if ($bDeleteAfterCopy)
			@unlink($path_from);

		return true;
	}
	else
	{
		return true;
	}

	if ($handle = @opendir($path_from))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == "." || $file == "..")
				continue;

			if ($strExclude <> '' && mb_substr($file, 0, mb_strlen($strExclude)) == $strExclude)
				continue;

			if (is_dir($path_from."/".$file) && $Recursive)
			{
				CopyDirFiles($path_from."/".$file, $path_to."/".$file, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
				if ($bDeleteAfterCopy)
					@rmdir($path_from."/".$file);
			}
			elseif (is_file($path_from."/".$file))
			{
				if (file_exists($path_to."/".$file) && !$ReWrite)
					continue;

				@copy($path_from."/".$file, $path_to."/".$file);
				@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);

				if($bDeleteAfterCopy)
					@unlink($path_from."/".$file);
			}
		}
		@closedir($handle);

		if ($bDeleteAfterCopy)
			@rmdir($path_from);

		return true;
	}

	return false;
}

function DeleteDirFilesEx($path)
{
	if($path == '' || $path == '/')
		return false;

	$full_path = $_SERVER["DOCUMENT_ROOT"]."/".$path;
	$full_path = preg_replace("#[\\\\\\/]+#", "/", $full_path);

	$f = true;
	if(is_file($full_path) || is_link($full_path))
	{
		if(@unlink($full_path))
			return true;
		return false;
	}
	elseif(is_dir($full_path))
	{
		if($handle = opendir($full_path))
		{
			while(($file = readdir($handle)) !== false)
			{
				if($file == "." || $file == "..")
					continue;

				if(!DeleteDirFilesEx($path."/".$file))
					$f = false;
			}
			closedir($handle);
		}
		if(!@rmdir($full_path))
			return false;
		return $f;
	}
	return false;
}

function DeleteDirFiles($frDir, $toDir, $arExept = array())
{
	if(is_dir($frDir))
	{
		$d = dir($frDir);
		while ($entry = $d->read())
		{
			if ($entry=="." || $entry=="..")
				continue;
			if (in_array($entry, $arExept))
				continue;
			@unlink($toDir."/".$entry);
		}
		$d->close();
	}
}

function RewriteFile($abs_path, $strContent)
{
	CheckDirPath($abs_path);
	if(file_exists($abs_path) && !is_writable($abs_path))
		@chmod($abs_path, BX_FILE_PERMISSIONS);
	$fd = fopen($abs_path, "wb");
	if(!fwrite($fd, $strContent)) return false;
	@chmod($abs_path, BX_FILE_PERMISSIONS);
	fclose($fd);
	return true;
}

function GetScriptFileExt()
{
	static $FILEMAN_SCRIPT_EXT = false;
	if($FILEMAN_SCRIPT_EXT !== false)
		return $FILEMAN_SCRIPT_EXT;

	$script_files = COption::GetOptionString("fileman", "~script_files", "php,php3,php4,php5,php6,php7,php8,phtml,pl,asp,aspx,cgi,dll,exe,ico,shtm,shtml,fcg,fcgi,fpl,asmx,pht,py,psp,var");
	$arScriptFiles = array();
	foreach(explode(",", mb_strtolower($script_files)) as $ext)
		if(($e = trim($ext)) != "")
			$arScriptFiles[] = $e;

	$FILEMAN_SCRIPT_EXT = $arScriptFiles;
	return $arScriptFiles;
}

function TrimUnsafe($path)
{
	return rtrim($path, "\0.\\/+ ");
}

function RemoveScriptExtension($check_name)
{
	$arExt = GetScriptFileExt();

	$name = GetFileName($check_name);
	$arParts = explode(".", $name);
	foreach($arParts as $i => $part)
	{
		if($i > 0 && in_array(mb_strtolower(TrimUnsafe($part)), $arExt))
			unset($arParts[$i]);
	}
	$path = mb_substr(TrimUnsafe($check_name), 0, -mb_strlen($name));
	return $path.implode(".", $arParts);
}

function HasScriptExtension($check_name)
{
	$arExt = GetScriptFileExt();

	$check_name = GetFileName($check_name);
	$arParts = explode(".", $check_name);
	foreach($arParts as $i => $part)
	{
		if($i > 0 && in_array(mb_strtolower(TrimUnsafe($part)), $arExt))
			return true;
	}
	return false;
}

function GetFileExtension($path)
{
	$path = GetFileName($path);
	if($path <> '')
	{
		$pos = bxstrrpos($path, '.');
		if($pos !== false)
			return mb_substr($path, $pos + 1);
	}
	return '';
}

function GetFileNameWithoutExtension($path)
{
	$path = GetFileName($path);
	if($path <> '')
	{
		$pos = bxstrrpos($path, '.');
		if($pos !== false)
			$path = mb_substr($path, 0, $pos);
		return trim($path, '.');
	}
	return '';
}

function GetFileName($path)
{
	$path = TrimUnsafe($path);
	$path = str_replace("\\", "/", $path);
	$path = rtrim($path, "/");

	$p = bxstrrpos($path, "/");
	if($p !== false)
		return mb_substr($path, $p + 1);

	return $path;
}

function IsFileUnsafe($name)
{
	static $arFiles = false;
	if($arFiles === false)
	{
		$fileList = COption::GetOptionString("main", "~unsafe_files", ".htaccess,.htpasswd,web.config,global.asax");
		$arFiles = explode(",", mb_strtolower($fileList));
	}
	$name = GetFileName($name);
	return in_array(mb_strtolower(TrimUnsafe($name)), $arFiles);
}

function GetFileType($path)
{
	$extension = GetFileExtension(mb_strtolower($path));
	switch ($extension)
	{
		case "jpg": case "jpeg": case "gif": case "bmp": case "png":
			$type = "IMAGE";
			break;
		case "swf":
			$type = "FLASH";
			break;
		case "html": case "htm": case "asp": case "aspx":
		case "phtml": case "php": case "php3": case "php4": case "php5": case "php6":
		case "shtml": case "sql": case "txt": case "inc": case "js": case "vbs":
		case "tpl": case "css": case "shtm":
			$type = "SOURCE";
			break;
		default:
			$type = "UNKNOWN";
	}
	return $type;
}

function GetDirectoryIndex($path, $strDirIndex=false)
{
	return GetDirIndex($path, $strDirIndex);
}

function GetDirIndex($path, $strDirIndex=false)
{
	$doc_root = ($_SERVER["DOCUMENT_ROOT"] <> ''? $_SERVER["DOCUMENT_ROOT"] : $GLOBALS["DOCUMENT_ROOT"]);
	$dir = GetDirPath($path);
	$arrDirIndex = GetDirIndexArray($strDirIndex);
	if(is_array($arrDirIndex) && !empty($arrDirIndex))
	{
		foreach($arrDirIndex as $page_index)
			if(file_exists($doc_root.$dir.$page_index))
				return $page_index;
	}
	return "index.php";
}

function GetDirIndexArray($strDirIndex=false)
{
	static $arDefault = array("index.php", "index.html", "index.htm", "index.phtml", "default.html", "index.php3");

	if($strDirIndex === false && !defined("DIRECTORY_INDEX"))
		return $arDefault;

	if($strDirIndex === false && defined("DIRECTORY_INDEX"))
		$strDirIndex = DIRECTORY_INDEX;

	$arrRes = array();
	$arr = explode(" ", $strDirIndex);
	foreach($arr as $page_index)
	{
		$page_index = trim($page_index);
		if($page_index <> '')
			$arrRes[] = $page_index;
	}
	return $arrRes;
}

function GetPagePath($page=false, $get_index_page=null)
{
	if (null === $get_index_page)
	{
		if (defined('BX_DISABLE_INDEX_PAGE'))
			$get_index_page = !BX_DISABLE_INDEX_PAGE;
		else
			$get_index_page = true;
	}

	if($page===false && !empty($_SERVER["REQUEST_URI"]))
		$page = $_SERVER["REQUEST_URI"];
	if($page===false)
		$page = $_SERVER["SCRIPT_NAME"];

	$sPath = $page;

	static $terminate = array("?", "#");
	foreach($terminate as $term)
	{
		if(($found = mb_strpos($sPath, $term)) !== false)
		{
			$sPath = mb_substr($sPath, 0, $found);
		}
	}

	//nginx fix
	$sPath = preg_replace("/%+[0-9a-f]{0,1}$/i", "", $sPath);

	$sPath = urldecode($sPath);

	//Decoding UTF uri
	$sPath = Text\Encoding::convertEncodingToCurrent($sPath);

	if(mb_substr($sPath, -1, 1) == "/" && $get_index_page)
	{
		$sPath .= GetDirectoryIndex($sPath);
	}

	$sPath = Rel2Abs("/", $sPath);

	static $aSearch = array("<", ">", "\"", "'", "%", "\r", "\n", "\t", "\\");
	static $aReplace = array("&lt;", "&gt;", "&quot;", "&#039;", "%25", "%0d", "%0a", "%09", "%5C");
	$sPath = str_replace($aSearch, $aReplace, $sPath);

	return $sPath;
}

function GetRequestUri()
{
	$uriPath = "/".ltrim($_SERVER["REQUEST_URI"] ?? '', "/");
	if (($index = mb_strpos($uriPath, "?")) !== false)
	{
		$uriPath = mb_substr($uriPath, 0, $index);
	}

	if (defined("BX_DISABLE_INDEX_PAGE") && BX_DISABLE_INDEX_PAGE === true)
	{
		if (mb_substr($uriPath, -10) === "/index.php")
		{
			$uriPath = mb_substr($uriPath, 0, -9);
		}
	}

	$queryString = DeleteParam(array("bxrand", "SEF_APPLICATION_CUR_PAGE_URL"));
	if ($queryString != "")
	{
		$uriPath = $uriPath."?".$queryString;
	}

	return $uriPath;
}

//light version of GetPagePath() for menu links
function GetFileFromURL($page, $get_index_page=null)
{
	if (null === $get_index_page)
	{
		if (defined('BX_DISABLE_INDEX_PAGE'))
			$get_index_page = !BX_DISABLE_INDEX_PAGE;
		else
			$get_index_page = true;
	}

	$found = mb_strpos($page, "?");
	$sPath = ($found !== false? mb_substr($page, 0, $found) : $page);

	$sPath = urldecode($sPath);

	if(mb_substr($sPath, -1, 1) == "/" && $get_index_page)
		$sPath .= GetDirectoryIndex($sPath);

	return $sPath;
}

function GetDirPath($sPath)
{
	if($sPath <> '')
	{
		$p = mb_strrpos($sPath, "/");
		if($p === false)
		{
			return '/';
		}
		else
		{
			return mb_substr($sPath, 0, $p + 1);
		}
	}
	else
	{
		return '/';
	}
}

/*
This function emulates php internal function basename
but does not behave badly on broken locale settings
*/
function bx_basename($path, $ext="")
{
	$path = rtrim($path, "\\/");
	if(preg_match("#[^\\\\/]+$#", $path, $match))
		$path = $match[0];

	if($ext)
	{
		$ext_len = mb_strlen($ext);
		if(mb_strlen($path) > $ext_len && mb_substr($path, -$ext_len) == $ext)
			$path = mb_substr($path, 0, -$ext_len);
	}

	return $path;
}

function bxstrrpos($haystack, $needle)
{
	if(defined("BX_UTF"))
	{
		//mb_strrpos does not work on invalid UTF-8 strings
		$ln = mb_strlen($needle);
		for($i = mb_strlen($haystack) - $ln; $i >= 0; $i--)
			if(mb_substr($haystack, $i, $ln) == $needle)
				return $i;
		return false;
	}
	return mb_strrpos($haystack, $needle);
}

function Rel2Abs($curdir, $relpath)
{
	if($relpath == "")
		return false;

	if(mb_substr($relpath, 0, 1) == "/" || preg_match("#^[a-z]:/#i", $relpath))
	{
		$res = $relpath;
	}
	else
	{
		if(mb_substr($curdir, 0, 1) != "/" && !preg_match("#^[a-z]:/#i", $curdir))
			$curdir = "/".$curdir;
		if(mb_substr($curdir, -1) != "/")
			$curdir .= "/";
		$res = $curdir.$relpath;
	}

	if(($p = mb_strpos($res, "\0")) !== false)
	{
		throw new Main\IO\InvalidPathException($res);
	}

	$res = _normalizePath($res);

	if(mb_substr($res, 0, 1) !== "/" && !preg_match("#^[a-z]:/#i", $res))
		$res = "/".$res;

	$res = rtrim($res, ".\\+ ");

	return $res;
}

/**
 * @deprecated Use Main\IO\Path::normalize()
 */
function _normalizePath($strPath)
{
	$strResult = '';
	if($strPath <> '')
	{
		if(strncasecmp(PHP_OS, "WIN", 3) == 0)
		{
			//slashes doesn't matter for Windows
			$strPath = str_replace("\\", "/", $strPath);
		}

		$arPath = explode('/', $strPath);
		$nPath = count($arPath);
		$pathStack = array();

		for ($i = 0; $i < $nPath; $i++)
		{
			if ($arPath[$i] === ".")
				continue;
			if (($arPath[$i] === '') && ($i !== ($nPath - 1)) && ($i !== 0))
				continue;

			if ($arPath[$i] === "..")
				array_pop($pathStack);
			else
				array_push($pathStack, $arPath[$i]);
		}

		$strResult = implode("/", $pathStack);
	}
	return $strResult;
}

function removeDocRoot($path)
{
	$len = mb_strlen($_SERVER["DOCUMENT_ROOT"]);

	if (mb_substr($path, 0, $len) == $_SERVER["DOCUMENT_ROOT"])
		return "/".ltrim(mb_substr($path, $len), "/");
	else
		return $path;
}

/*********************************************************************
Language files
*********************************************************************/

function GetMessageJS($name, $aReplace=false)
{
	return CUtil::JSEscape(GetMessage($name, $aReplace));
}

function GetMessage($name, $aReplace=null)
{
	global $MESS;
	if (isset($MESS[$name]))
	{
		$s = $MESS[$name];

		if (is_array($aReplace))
		{
			$s = strtr($s, $aReplace);
		}

		return $s;
	}

	return Main\Localization\Loc::getMessage($name, $aReplace);
}

/**
 * @deprecated
 */
function HasMessage($name)
{
	global $MESS;
	return isset($MESS[$name]);
}

global $ALL_LANG_FILES;
$ALL_LANG_FILES = array();

/** @deprecated */
function GetLangFileName($before, $after, $lang=false)
{
	if ($lang===false)
		$lang = LANGUAGE_ID;

	global $ALL_LANG_FILES;
	$ALL_LANG_FILES[] = $before.$lang.$after;

	if (Main\Localization\Translation::allowConvertEncoding())
	{
		$langFile = Main\Localization\Translation::convertLangPath($before. $lang. $after, $lang);
		if(file_exists($langFile))
		{
			return $langFile;
		}
	}

	if(file_exists($before.$lang.$after))
		return $before.$lang.$after;
	if(file_exists($before."en".$after))
		return $before."en".$after;

	if(strpos($before, "/bitrix/modules/") === false)
		return $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/en/tools.php";

	$old_path = rtrim($before,"/");
	$old_path = mb_substr($old_path, mb_strlen($_SERVER["DOCUMENT_ROOT"]));
	$path = mb_substr($old_path, 16);
	$module = mb_substr($path, 0, mb_strpos($path, "/"));
	$path = mb_substr($path, mb_strpos($path, "/"));
	if(mb_substr($path, -5) == "/lang")
		$path = mb_substr($path, 0, -5);
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module.$path.$after, $lang);
	return $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module."/lang/".$lang.$path.$after;
}

/**
 * @deprecated Use Main\Localization\Loc
 */
function __IncludeLang($path, $bReturnArray=false, $bFileChecked=false)
{
	global $ALL_LANG_FILES;
	$ALL_LANG_FILES[] = $path;

	if (Main\Localization\Translation::allowConvertEncoding())
	{
		// extract language from path
		$language = '';
		$arr = explode('/', $path);
		$langKey = array_search('lang', $arr);
		if ($langKey !== false && isset($arr[$langKey + 1]))
		{
			$language = $arr[$langKey + 1];
		}

		static $encodingCache = array();
		if (isset($encodingCache[$language]))
		{
			[$convertEncoding, $targetEncoding, $sourceEncoding] = $encodingCache[$language];
		}
		else
		{
			$convertEncoding = Main\Localization\Translation::needConvertEncoding($language);
			$targetEncoding = $sourceEncoding = '';
			if ($convertEncoding)
			{
				$targetEncoding = Main\Localization\Translation::getCurrentEncoding();
				$sourceEncoding = Main\Localization\Translation::getSourceEncoding($language);
			}

			$encodingCache[$language] = array($convertEncoding, $targetEncoding, $sourceEncoding);
		}

		$MESS = array();
		if ($bFileChecked)
		{
			include($path);
		}
		else
		{
			$path = Main\Localization\Translation::convertLangPath($path, LANGUAGE_ID);
			if (file_exists($path))
			{
				include($path);
			}
		}

		if (!empty($MESS))
		{
			if ($convertEncoding)
			{
				$convertEncoding = Main\Localization\Translation::checkPathRestrictionConvertEncoding($path);
			}

			foreach ($MESS as $key => $val)
			{
				if ($convertEncoding)
				{
					$val = Main\Text\Encoding::convertEncoding($val, $sourceEncoding, $targetEncoding);
				}

				$MESS[$key] = $val;

				if (!$bReturnArray)
				{
					$GLOBALS['MESS'][$key] = $val;
				}
			}
		}
	}
	else
	{
		if ($bReturnArray)
		{
			$MESS = array();
		}
		else
		{
			global $MESS;
		}

		if ($bFileChecked)
		{
			include($path);
		}
		else
		{
			$path = Main\Localization\Translation::convertLangPath($path, LANGUAGE_ID);
			if (file_exists($path))
			{
				include($path);
			}
		}
	}

	//read messages from user lang file
	static $bFirstCall = true;
	if($bFirstCall)
	{
		$bFirstCall = false;
		$fname = getLocalPath("php_interface/user_lang/".LANGUAGE_ID."/lang.php");
		if($fname !== false)
		{
			$arMess = __IncludeLang($_SERVER["DOCUMENT_ROOT"].$fname, true, true);
			foreach($arMess as $key=>$val)
				$GLOBALS["MESS"][str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"].$key))] = $val;
		}
	}

	//redefine messages from user lang file
	$path = str_replace("\\", "/", realpath($path));
	if(isset($GLOBALS["MESS"][$path]) && is_array($GLOBALS["MESS"][$path]))
		foreach($GLOBALS["MESS"][$path] as $key=>$val)
			$MESS[$key] = $val;

	if($bReturnArray)
		return $MESS;
	else
		return true;
}

/**
 * @deprecated Use Main\Localization\Loc
 */
function IncludeTemplateLangFile($filepath, $lang=false)
{
	$filepath = rtrim(preg_replace("'[\\\\/]+'", "/", $filepath), "/ ");
	$module_path = "/bitrix/modules/";
	$module_name = $templ_path = $file_name = $template_name = "";

	$dirs = array(
		"/local/templates/",
		BX_PERSONAL_ROOT."/templates/",
	);
	foreach($dirs as $dir)
	{
		if(strpos($filepath, $dir) !== false)
		{
			$templ_path = $dir;
			$templ_pos = mb_strlen($filepath) - mb_strpos(strrev($filepath), strrev($templ_path));
			$rel_path = mb_substr($filepath, $templ_pos);
			$p = mb_strpos($rel_path, "/");
			if(!$p)
				return null;
			$template_name = mb_substr($rel_path, 0, $p);
			$file_name = mb_substr($rel_path, $p + 1);
			$p = mb_strpos($file_name, "/");
			if($p>0)
				$module_name = mb_substr($file_name, 0, $p);
			break;
		}
	}
	if($templ_path == "")
	{
		if(strpos($filepath, $module_path) !== false)
		{
			$templ_pos = mb_strlen($filepath) - mb_strpos(strrev($filepath), strrev($module_path));
			$rel_path = mb_substr($filepath, $templ_pos);
			$p = mb_strpos($rel_path, "/");
			if(!$p)
				return null;
			$module_name = mb_substr($rel_path, 0, $p);
			if(defined("SITE_TEMPLATE_ID"))
				$template_name = SITE_TEMPLATE_ID;
			else
				$template_name = ".default";
			$file_name = mb_substr($rel_path, $p + mb_strlen("/install/templates/"));
		}
		else
		{
			return false;
		}
	}

	$BX_DOC_ROOT = rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ ");
	$module_path = $BX_DOC_ROOT.$module_path;

	if($lang === false)
	{
		$lang = LANGUAGE_ID;
	}

	$subst_lang = LangSubst($lang);

	if((mb_substr($file_name, -16) == ".description.php") && $module_name!="")
	{
		if ($subst_lang <> $lang)
		{
			$fname = $module_path.$module_name."/install/templates/lang/".$subst_lang."/".$file_name;
			$fname = Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $module_path.$module_name."/install/templates/lang/".$lang."/".$file_name;
		$fname = Main\Localization\Translation::convertLangPath($fname, $lang);
		if (file_exists($fname))
		{
			__IncludeLang($fname, false, true);
		}
	}

	$checkModule = true;
	if ($templ_path <> "")
	{
		$templ_path = $BX_DOC_ROOT.$templ_path;
		$checkDefault = true;

		// default
		if ($subst_lang <> $lang)
		{
			$fname = $templ_path.$template_name."/lang/".$subst_lang."/".$file_name;
			$fname = Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
				$checkDefault = $checkModule = false;
			}
		}

		// required lang
		$fname = $templ_path.$template_name."/lang/".$lang."/".$file_name;
		$fname = Main\Localization\Translation::convertLangPath($fname, $lang);
		if (file_exists($fname))
		{
			__IncludeLang($fname, false, true);
			$checkDefault = $checkModule = false;
		}

		// template .default
		if ($checkDefault && $template_name != ".default")
		{
			if ($subst_lang <> $lang)
			{
				$fname = $templ_path.".default/lang/".$subst_lang."/".$file_name;
				$fname = Main\Localization\Translation::convertLangPath($fname, $subst_lang);
				if (file_exists($fname))
				{
					__IncludeLang($fname, false, true);
					$checkModule = false;
				}
			}

			$fname = $templ_path.".default/lang/".$lang."/".$file_name;
			$fname = Main\Localization\Translation::convertLangPath($fname, $lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
				$checkModule = false;
			}
		}
	}
	if ($checkModule && $module_name != "")
	{
		if ($subst_lang <> $lang)
		{
			$fname = $module_path.$module_name."/install/templates/lang/".$subst_lang."/".$file_name;
			$fname = Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $module_path.$module_name."/install/templates/lang/".$lang."/".$file_name;
		$fname = Main\Localization\Translation::convertLangPath($fname, $lang);
		if(file_exists($fname))
		{
			__IncludeLang($fname, false, true);
		}
	}

	return null;
}

function IncludeModuleLangFile($filepath, $lang=false, $bReturnArray=false)
{
	if($lang === false && $bReturnArray === false)
	{
		Main\Localization\Loc::loadMessages($filepath);
		return true;
	}

	$filepath = rtrim(preg_replace("'[\\\\/]+'", "/", $filepath), "/ ");
	$module_path = "/modules/";
	if(strpos($filepath, $module_path) !== false)
	{
		$pos = mb_strlen($filepath) - mb_strpos(strrev($filepath), strrev($module_path));
		$rel_path = mb_substr($filepath, $pos);
		$p = mb_strpos($rel_path, "/");
		if(!$p)
			return false;

		$module_name = mb_substr($rel_path, 0, $p);
		$rel_path = mb_substr($rel_path, $p + 1);
		$BX_DOC_ROOT = rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ ");
		$module_path = $BX_DOC_ROOT.getLocalPath($module_path.$module_name);
	}
	elseif(strpos($filepath, "/.last_version/") !== false)
	{
		$pos = mb_strlen($filepath) - mb_strpos(strrev($filepath), strrev("/.last_version/"));
		$rel_path = mb_substr($filepath, $pos);
		$module_path = mb_substr($filepath, 0, $pos - 1);
	}
	else
	{
		return false;
	}

	if($lang === false)
	{
		$lang = (defined('LANGUAGE_ID') ? LANGUAGE_ID : 'en');
	}

	$lang_subst = LangSubst($lang);

	$arMess = array();
	if ($lang_subst <> $lang)
	{
		$fname = $module_path."/lang/".$lang_subst."/".$rel_path;
		$fname = Main\Localization\Translation::convertLangPath($fname, $lang_subst);
		if (file_exists($fname))
		{
			$arMess = __IncludeLang($fname, $bReturnArray, true);
		}
	}

	$fname = $module_path."/lang/".$lang."/".$rel_path;
	$fname = Main\Localization\Translation::convertLangPath($fname, $lang);
	if (file_exists($fname))
	{
		$msg = __IncludeLang($fname, $bReturnArray, true);
		if(is_array($msg))
		{
			$arMess = array_merge($arMess, $msg);
		}
	}

	if($bReturnArray)
	{
		return $arMess;
	}

	return true;
}

/**
 * @deprecated Use Main\Localization\Loc
 */
function LangSubst($lang)
{
	return Main\Localization\Loc::getDefaultLang($lang);
}

/*********************************************************************
Debugging
*********************************************************************/

function mydump($thing, $maxdepth=-1, $depth=0)
{
	$res="";
	$fmt = sprintf ("%%%ds", 4*$depth);
	$pfx = sprintf ($fmt, "");
	$type = gettype($thing);
	if($type == 'array')
	{
		$n = sizeof($thing);
		$res.="$pfx array($n) => \n";
		foreach(array_keys($thing) as $key)
		{
			$res.=" $pfx"."[".$key."] =>\n";
			$res.=mydump($thing[$key], $maxdepth, $depth+1);
		}
	}
	elseif($type == 'string')
	{
		$n = mb_strlen($thing);
		$res.="$pfx string($n) =>\n";
		$res.="$pfx\"".$thing."\"\n";
	}
	elseif($type == 'object')
	{
		$name = get_class($thing);
		$res.="$pfx object($name) =>\n";
		$methodArray = get_class_methods($name);
		foreach (array_keys($methodArray) as $m)
			$res.=" $pfx method($m) => $methodArray"."[".$m."]\n";
		$classVars = get_class_vars($name);
		foreach(array_keys($classVars) as $v)
		{
			$res.=" $pfx default => $v =>\n";
			$res.=mydump($classVars[$v], $maxdepth, $depth+2);
		}
		$objectVars = get_object_vars($thing);
		foreach (array_keys($objectVars) as $v)
		{
			$res.=" $pfx $v =>\n";
			$res.=mydump($objectVars[$v], $maxdepth, $depth+2);
		}
	}
	elseif ($type == 'boolean')
	{
		if($thing)
			$res.="$pfx boolean(true)\n";
		else
			$res.="$pfx boolean(false)\n";
	}
	else
		$res.="$pfx $type(".$thing.")\n";

	return $res;
}

function SendError($error)
{
	if(defined('ERROR_EMAIL') && ERROR_EMAIL <> '')
	{
		$from = (defined('ERROR_EMAIL_FROM') && ERROR_EMAIL_FROM <> ''? ERROR_EMAIL_FROM : 'error@bitrix.ru');
		$reply_to = (defined('ERROR_EMAIL_REPLY_TO') && ERROR_EMAIL_REPLY_TO <> ''? ERROR_EMAIL_REPLY_TO : 'admin@bitrix.ru');
		bxmail(ERROR_EMAIL, $_SERVER['HTTP_HOST'].": Error!",
			$error.
			"HTTP_GET_VARS:\n".mydump($_GET)."\n\n".
			"HTTP_POST_VARS:\n".mydump($_POST)."\n\n".
			"HTTP_COOKIE_VARS:\n".mydump($_COOKIE)."\n\n".
			"HTTP_SERVER_VARS:\n".mydump($_SERVER)."\n\n",
			"From: ".$from."\r\n".
			"Reply-To: ".$reply_to."\r\n".
			"X-Mailer: PHP/" . phpversion()
		);
	}
}

function AddMessage2Log($text, $module = '', $traceDepth = 6, $showArgs = false)
{
	if (defined('LOG_FILENAME') && LOG_FILENAME <> '')
	{
		$logger = Diag\Logger::create('main.Default', [LOG_FILENAME, $showArgs]);
		if ($logger === null)
		{
			$logger = new Diag\FileLogger(LOG_FILENAME, 0);
			$formatter = new Diag\LogFormatter($showArgs);
			$logger->setFormatter($formatter);
		}

		$trace = '';
		if ($traceDepth > 0)
		{
			$trace = Main\Diag\Helper::getBackTrace($traceDepth, ($showArgs ? null : DEBUG_BACKTRACE_IGNORE_ARGS), 2);
		}

		$context = [
			'module' => $module,
			'message' => $text,
			'trace' => $trace,
		];

		$message = "Host: {host}\n"
			. "Date: {date}\n"
			. ($module != '' ? "Module: {module}\n" : '')
			. "{message}\n"
			. "{trace}"
			. "{delimiter}\n"
		;

		$logger->debug($message, $context);
	}
}

function AddEventToStatFile($module, $action, $tag, $label, $action_type = '', $user_id = null)
{
	global $USER;
	static $search = array("\t", "\n", "\r");
	static $replace = " ";
	if (defined('ANALYTICS_FILENAME') && is_writable(ANALYTICS_FILENAME))
	{
		if ($user_id === null && is_object($USER) && !defined("BX_CHECK_AGENT_START"))
		{
			$user_id = $USER->GetID();
		}
		$content =
			date('Y-m-d H:i:s')
			."\t".str_replace($search, $replace, $_SERVER["HTTP_HOST"])
			."\t".str_replace($search, $replace, $module)
			."\t".str_replace($search, $replace, $action)
			."\t".str_replace($search, $replace, $tag)
			."\t".str_replace($search, $replace, $label)
			."\t".str_replace($search, $replace, $action_type)
			."\t".intval($user_id)
			."\n";
		$fp = @fopen(ANALYTICS_FILENAME, "ab");
		if ($fp)
		{
			if (flock($fp, LOCK_EX))
			{
				@fwrite($fp, $content);
				@fflush($fp);
				@flock($fp, LOCK_UN);
				@fclose($fp);
			}
		}
	}
}

/**
 * @deprecated Will be removed soon
 * @return void
 */
function UnQuoteAll()
{
}

/*********************************************************************
Other functions
*********************************************************************/
function LocalRedirect($url, $skip_security_check=false, $status="302 Found")
{
	$redirectResponse = Context::getCurrent()->getResponse()->redirectTo($url);
	$redirectResponse
        ->setSkipSecurity($skip_security_check)
        ->setStatus($status)
    ;

	Application::getInstance()->end(0, $redirectResponse);
}

function WriteFinalMessage($message = "")
{
	echo $message;
	exit;
}

function FindUserID($tag_name, $tag_value, $user_name="", $form_name = "form1", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton", $search_page="/bitrix/admin/user_search.php")
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
	$search_page = str_replace("/bitrix/admin/", $selfFolderUrl, $search_page);
	$tag_name_x = preg_replace("/([^a-z0-9]|\\[|\\])/is", "x", $tag_name);
	if($APPLICATION->GetGroupRight("main") >= "R")
	{
		$strReturn = "
<input type=\"text\" name=\"".$tag_name."\" id=\"".$tag_name."\" value=\"".htmlspecialcharsbx($tag_value)."\" size=\"".$tag_size."\" maxlength=\"".$tag_maxlength."\" class=\"".$tag_class."\">
<iframe style=\"width:0px; height:0px; border:0px\" src=\"javascript:''\" name=\"hiddenframe".$tag_name."\" id=\"hiddenframe".$tag_name."\"></iframe>
<input class=\"".$button_class."\" type=\"button\" name=\"FindUser\" id=\"FindUser\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=".$form_name."&FC=".$tag_name."', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"".$button_value."\">
<span id=\"div_".$tag_name."\" class=\"adm-filter-text-search\">".$user_name."</span>
<script type=\"text/javascript\">
";
		if($user_name=="")
			$strReturn.= "var tv".$tag_name_x."='';\n";
		else
			$strReturn.= "var tv".$tag_name_x."='".CUtil::JSEscape($tag_value)."';\n";

		$strReturn.= "
function Ch".$tag_name_x."()
{
	var DV_".$tag_name_x.";
	DV_".$tag_name_x." = BX(\"div_".$tag_name."\");
	if (!!DV_".$tag_name_x.")
	{
		if (tv".$tag_name_x."!=document.".$form_name."['".$tag_name."'].value)
		{
			tv".$tag_name_x."=document.".$form_name."['".$tag_name."'].value;
			if (tv".$tag_name_x."!='')
			{
				DV_".$tag_name_x.".innerHTML = '<i>".GetMessage("MAIN_WAIT")."</i>';
				BX(\"hiddenframe".$tag_name."\").src='get_user.php?ID=' + tv".$tag_name_x."+'&strName=".$tag_name."&lang=".LANG.(defined("ADMIN_SECTION") && ADMIN_SECTION===true?"&admin_section=Y":"")."';
			}
			else
			{
				DV_".$tag_name_x.".innerHTML = '';
			}
		}
	}
	setTimeout(function(){Ch".$tag_name_x."()},1000);
}

BX.ready(function(){
	//js error during admin filter initialization, IE9, http://msdn.microsoft.com/en-us/library/gg622929%28v=VS.85%29.aspx?ppud=4, mantis: 33208
	if(BX.browser.IsIE)
	{
		setTimeout(function(){Ch".$tag_name_x."()},3000);
	}
	else
		Ch".$tag_name_x."();

});
//-->
</script>
";
	}
	else
	{
		$strReturn = "
			<input type=\"text\" name=\"$tag_name\" id=\"$tag_name\" value=\"".htmlspecialcharsbx($tag_value)."\" size=\"$tag_size\" maxlength=\"strMaxLenght\">
			<input type=\"button\" name=\"FindUser\" id=\"FindUser\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=$form_name&FC=$tag_name', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"$button_value\">
			$user_name
			";
	}
	return $strReturn;
}

function GetWhoisLink($ip, $class='')
{
	$URL = COption::GetOptionString('main', 'whois_service_url', 'http://whois.domaintools.com/#IP#');
	$URL = str_replace("#IP#", urlencode($ip), $URL);
	return '<a href="'.$URL.'"'.($class <> ''? ' class="'.$class.'"':'').' target="_blank" title="'.GetMessage("WHOIS_SERVICE").'">'.htmlspecialcharsbx($ip).'</a>';
}

function IsIE()
{
	global $HTTP_USER_AGENT;
	if(
		mb_strpos($HTTP_USER_AGENT, "Opera") == false
		&& preg_match('#(MSIE|Internet Explorer) ([0-9]+)\\.([0-9]+)#', $HTTP_USER_AGENT, $version)
	)
	{
		if(intval($version[2]) > 0)
			return doubleval($version[2].".".$version[3]);
		else
			return false;
	}
	else
	{
		return false;
	}
}

function GetCountryByID($id, $lang=LANGUAGE_ID)
{
	$msg = IncludeModuleLangFile(__FILE__, $lang, true);
	return $msg["COUNTRY_".$id] ?? '';
}

function GetCountryArray($lang=LANGUAGE_ID)
{
	$arMsg = IncludeModuleLangFile(__FILE__, $lang, true);
	$arr = array();
	if (is_array($arMsg))
	{
		foreach($arMsg as $id=>$country)
			if(mb_strpos($id, "COUNTRY_") === 0)
				$arr[intval(mb_substr($id, 8))] = $country;
	}
	asort($arr);
	$arCountry = array("reference_id"=>array_keys($arr), "reference"=>array_values($arr));
	return $arCountry;
}

function GetCountries($lang = LANGUAGE_ID)
{
	static $countries = null;
	if (isset($countries[$lang]))
	{
		return $countries[$lang];
	}

	include __DIR__ . '/countries.php';
	$msg = IncludeModuleLangFile(__FILE__, $lang, true);

	$countries[$lang] = [];
	/** @var array $arCounries */
	foreach ($arCounries as $country => $countryId)
	{
		$countries[$lang][$country] = [
			'ID' => $countryId,
			'CODE' => $country,
			'NAME' => $msg['COUNTRY_' . $countryId]
		];
	}
	return $countries[$lang];
}

function GetCountryIdByCode($code)
{
	$code = strtoupper($code);
	$countries = GetCountries();

	return $countries[$code]['ID'] ?? false;
}

function GetCountryCodeById($countryId)
{
	$countryId = (int)$countryId;

	static $countryCodes = null;
	if ($countryCodes === null)
	{
		include __DIR__ . '/countries.php';
		/** @var array $arCounries */
		$countryCodes = array_flip($arCounries);
	}

	return $countryCodes[$countryId] ?? '';
}

function minimumPHPVersion($vercheck)
{
	$minver = explode(".", $vercheck);
	$curver = explode(".", phpversion());
	if ((intval($curver[0]) < intval($minver[0])) || ((intval($curver[0]) == intval($minver[0])) && (intval($curver[1]) < intval($minver[1]))) || ((intval($curver[0]) == intval($minver[0])) && (intval($curver[1]) == intval($minver[1])) && (intval($curver[2]) < intval($minver[2]))))
		return false;
	else
		return true;
}

function FormDecode()
{
	$superglobals = array(
		'_GET'=>1, '_SESSION'=>1, '_POST'=>1, '_COOKIE'=>1, '_REQUEST'=>1, '_FILES'=>1, '_SERVER'=>1, 'GLOBALS'=>1, '_ENV'=>1,
		'DBType'=>1,  'DBDebug'=>1, 'DBDebugToFile'=>1, 'DBHost'=>1, 'DBName'=>1, 'DBLogin'=>1, 'DBPassword'=>1,
		'HTTP_ENV_VARS'=>1, 'HTTP_GET_VARS'=>1, 'HTTP_POST_VARS'=>1, 'HTTP_POST_FILES'=>1, 'HTTP_COOKIE_VARS'=>1, 'HTTP_SERVER_VARS'=>1,
	);

	foreach($superglobals as $gl=>$t)
	{
		unset($_REQUEST[$gl]);
		unset($_GET[$gl]);
		unset($_POST[$gl]);
		unset($_COOKIE[$gl]);
	}

	$register_globals = ini_get_bool("register_globals");
	if (!$register_globals)
	{
		$toGlobals = array();

		foreach($_ENV as $key => $val)
			if(!isset($superglobals[$key]))
				$toGlobals[$key] = $val;

		foreach($_GET as $key => $val)
			if(!isset($superglobals[$key]))
				$toGlobals[$key] = $val;

		foreach($_POST as $key => $val)
			if(!isset($superglobals[$key]))
				$toGlobals[$key] = $val;


		foreach($_COOKIE as $key => $val)
			if(!isset($superglobals[$key]))
				$toGlobals[$key] = $val;

		foreach($_SERVER as $key => $val)
			if(!isset($superglobals[$key]))
				$toGlobals[$key] = $val;

		foreach($toGlobals as $key => $val)
		{
			if(!isset($GLOBALS[$key]))
			{
				$GLOBALS[$key] = $val;
			}
		}
	}
}

/**
 * @deprecated Use Bitrix\Main\Web\HttpClient
 */
function QueryGetData($SITE, $PORT, $PATH, $QUERY_STR, &$errno, &$errstr, $sMethod="GET", $sProto="", $sContentType = 'N')
{
	$ob = new CHTTP();
	$ob->Query(
			$sMethod,
			$SITE,
			$PORT,
			$PATH . ($sMethod == 'GET' ? ((strpos($PATH, '?') === false ? '?' : '&') . $QUERY_STR) : ''),
			$sMethod == 'POST' ? $QUERY_STR : false,
			$sProto,
			$sContentType
		);

	$errno = $ob->errno;
	$errstr = $ob->errstr;

	return $ob->result;
}

function xmlize_xmldata($data)
{
	$data = trim($data);
	$vals = $index = $array = array();
	$parser = xml_parser_create("ISO-8859-1");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $vals, $index);
	xml_parser_free($parser);

	$i = 0;

	$tagname = $vals[$i]['tag'];
	if (isset($vals[$i]['attributes']))
	{
		$array[$tagname]['@'] = $vals[$i]['attributes'];
	}
	else
	{
		$array[$tagname]['@'] = array();
	}

	$array[$tagname]["#"] = xml_depth_xmldata($vals, $i);

	return $array;
}

function xml_depth_xmldata($vals, &$i)
{
	$children = array();

	if (isset($vals[$i]['value']))
	{
		array_push($children, $vals[$i]['value']);
	}

	while (++$i < count($vals))
	{
		switch ($vals[$i]['type'])
		{
			case 'open':
				$tagname = $vals[$i]['tag'] ?? '';

				if (isset($children[$tagname]))
				{
					$size = sizeof($children[$tagname]);
				}
				else
				{
					$size = 0;
				}

				if (isset($vals[$i]['attributes']))
				{
					$children[$tagname][$size]['@'] = $vals[$i]["attributes"];
				}
				$children[$tagname][$size]['#'] = xml_depth_xmldata($vals, $i);
			break;

			case 'cdata':
				array_push($children, $vals[$i]['value']);
			break;

			case 'complete':
				$tagname = $vals[$i]['tag'];

				if(isset($children[$tagname]))
				{
					$size = sizeof($children[$tagname]);
				}
				else
				{
					$size = 0;
				}

				if(isset($vals[$i]['value']))
				{
					$children[$tagname][$size]["#"] = $vals[$i]['value'];
				}
				else
				{
					$children[$tagname][$size]["#"] = '';
				}

				if (isset($vals[$i]['attributes']))
				{
					$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
				}
			break;

			case 'close':
				return $children;
			break;
		}

	}

	return $children;
}

function Help($module="", $anchor="", $help_file="")
{
	/** @global CMain $APPLICATION */
	global $APPLICATION, $IS_HELP;
	if ($help_file == '') $help_file = basename($APPLICATION->GetCurPage());
	if ($anchor <> '') $anchor = "#".$anchor;

	if($IS_HELP!==true)
	{
		$height = "500";
		//$width = "545";
		$width = "780";
		echo "<script type=\"text/javascript\">
			<!--
			function Help(file, module, anchor)
			{
				window.open('".BX_ROOT."/tools/help_view.php?local=Y&file='+file+'&module='+module+'&lang=".LANGUAGE_ID."'+anchor, '','scrollbars=yes,resizable=yes,width=".$width.",height=".$height.",top='+Math.floor((screen.height - ".$height.")/2-14)+',left='+Math.floor((screen.width - ".$width.")/2-5));
			}
			//-->
			</script>";
		$IS_HELP=true;
	}
	echo "<a href=\"javascript:Help('".urlencode($help_file)."','".$module."','".$anchor."')\" title='".GetMessage("TOOLS_HELP")."'><img src='".BX_ROOT."/images/main/show_help.gif' width='16' height='16' border='0' alt='".GetMessage("TOOLS_HELP")."' align='absbottom' vspace='2' hspace='1'></a>";
}

function InitBVar(&$var)
{
	$var = ($var=="Y") ? "Y" : "N";
}

function init_get_params($url)
{
	InitURLParam($url);
}

function InitURLParam($url=false)
{
	if ($url===false) $url = $_SERVER["REQUEST_URI"];
	$start = mb_strpos($url, "?");
	if ($start!==false)
	{
		$end = mb_strpos($url, "#");
		$length = ($end > 0)? $end - $start - 1 : mb_strlen($url);
		$params = mb_substr($url, $start + 1, $length);
		parse_str($params, $_GET);
		parse_str($params, $arr);
		$_REQUEST += $arr;

		foreach ($arr as $key => $val)
		{
			if (!isset($GLOBALS[$key]))
			{
				$GLOBALS[$key] = $val;
			}
		}
	}
}

function _ShowHtmlspec($str)
{
	$str = str_replace(["<br>", "<br />", "<BR>", "<BR />"], "\n", $str);
	$str = htmlspecialcharsbx($str, ENT_COMPAT, false);
	$str = nl2br($str);
	return $str;
}

function ShowNote($strNote, $cls="notetext")
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	if($strNote <> "")
	{
		$APPLICATION->IncludeComponent(
			"bitrix:system.show_message",
			".default",
			Array(
				"MESSAGE"=> $strNote,
				"STYLE" => $cls,
			),
			null,
			array(
				"HIDE_ICONS" => "Y"
			)
		);
	}
}

function ShowError($strError, $cls="errortext")
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	if($strError <> "")
	{
		$APPLICATION->IncludeComponent(
			"bitrix:system.show_message",
			".default",
			Array(
				"MESSAGE"=> $strError,
				"STYLE" => $cls,
			),
			null,
			array(
				"HIDE_ICONS" => "Y"
			)
		);
	}
}

function ShowMessage($arMess)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	if(!is_array($arMess))
		$arMess=Array("MESSAGE" => $arMess, "TYPE" => "ERROR");

	if(!empty($arMess["MESSAGE"]))
	{
		$APPLICATION->IncludeComponent(
			"bitrix:system.show_message",
			".default",
			Array(
				"MESSAGE"=> $arMess["MESSAGE"],
				"STYLE" => ($arMess["TYPE"]=="OK"?"notetext":"errortext"),
			),
			null,
			array(
				"HIDE_ICONS" => "Y"
			)
		);
	}
}

function DeleteParam($ParamNames)
{
	if (empty($_GET))
	{
		return '';
	}

	$aParams = $_GET;
	foreach(array_keys($aParams) as $key)
	{
		foreach($ParamNames as $param)
		{
			if(strcasecmp($param, $key) == 0)
			{
				unset($aParams[$key]);
				break;
			}
		}
	}

	return http_build_query($aParams, "", "&");
}

function check_email($email, $strict = false, $domainCheck = false)
{
	if(!$strict)
	{
		$email = trim($email);
		if(preg_match("#.*?[<\\[\\(](.*?)[>\\]\\)].*#i", $email, $arr) && $arr[1] <> '')
		{
			$email = $arr[1];
		}
	}

	//http://tools.ietf.org/html/rfc2821#section-4.5.3.1
	//4.5.3.1. Size limits and minimums
	if(mb_strlen($email) > 320)
	{
		return false;
	}

	//convert to UTF to use extended regular expressions
	$encodedEmail = $email;
	static $encoding = null;
	if ($encoding === null)
	{
		if (($context = Context::getCurrent()) && ($culture = $context->getCulture()))
		{
			$encoding = strtolower($culture->getCharset());
		}
	}
	if ($encoding !== null && $encoding != "utf-8")
	{
		$encodedEmail = Text\Encoding::convertEncoding($email, $encoding, "UTF-8");
	}

	//http://tools.ietf.org/html/rfc2822#section-3.2.4
	//3.2.4. Atom
	//added \p{L} for international symbols
	static $atom = "\\p{L}=_0-9a-z+~'!\$&*^`|\\#%/?{}-";
	static $domain = "\\p{L}a-z0-9-";

	//"." can't be in the beginning or in the end of local-part
	//dot-atom-text = 1*atext *("." 1*atext)
	if(preg_match("#^[{$atom}]+(\\.[{$atom}]+)*@(([{$domain}]+\\.)+)([{$domain}]{2,20})$#ui", $encodedEmail))
	{
		if ($domainCheck)
		{
			$email = Main\Mail\Mail::toPunycode($email);
			$parts = explode('@', $email);
			$host = $parts[1] . '.';

			return (checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'));
		}

		return true;
	}

	return false;
}

function initvar($varname, $value='')
{
	global $$varname;
	if(!isset($$varname))
		$$varname=$value;
}

function ClearVars($prefix="str_")
{
	$n = mb_strlen($prefix);
	foreach($GLOBALS as $key=>$val)
		if(strncmp($key, $prefix, $n) == 0)
			unset($GLOBALS[$key]);
}

function roundEx($value, $prec=0)
{
	$eps = 1.00/pow(10, $prec+4);
	return round(doubleval($value)+$eps, $prec);
}

function roundDB($value, $len=18, $dec=4)
{
	if($value>=0)
		$value = "0".$value;
	$value = roundEx(DoubleVal($value), $len);
	$value = sprintf("%01.".$dec."f", $value);
	if($len>0 && mb_strlen($value) > $len - $dec)
		$value = trim(mb_substr($value, 0, $len - $dec), ".");
	return $value;
}

function bitrix_sessid()
{
	$kernelSession = Application::getInstance()->getKernelSession();
	if (!$kernelSession->has('fixed_session_id'))
	{
		bitrix_sessid_set();
	}

	return $kernelSession->get('fixed_session_id');
}

function bitrix_sessid_set($val=false)
{
	if($val === false)
		$val = bitrix_sessid_val();
	Application::getInstance()->getKernelSession()->set("fixed_session_id", $val);
}

function bitrix_sessid_val()
{
	return md5(CMain::GetServerUniqID().Application::getInstance()->getKernelSession()->getId());
}

function bitrix_sess_sign()
{
	return md5("nobody".CMain::GetServerUniqID()."nowhere");
}

function check_bitrix_sessid($varname='sessid')
{
	$request = Main\Context::getCurrent()->getRequest();
	return (
		$request[$varname] === bitrix_sessid() ||
		$request->getHeader('X-Bitrix-Csrf-Token') === bitrix_sessid()
	);
}

function bitrix_sessid_get($varname='sessid')
{
	return $varname."=".bitrix_sessid();
}

function bitrix_sessid_post($varname='sessid', $returnInvocations=false)
{
	static $invocations = 0;
	if ($returnInvocations)
	{
		return $invocations;
	}

	$id = $invocations ? $varname.'_'.$invocations : $varname;
	$invocations++;

	return '<input type="hidden" name="'.$varname.'" id="'.$id.'" value="'.bitrix_sessid().'" />';
}

function print_url($strUrl, $strText, $sParams="")
{
	return ($strUrl == ''? $strText : "<a href=\"".$strUrl."\" ".$sParams.">".$strText."</a>");
}

function IncludeAJAX()
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	$APPLICATION->AddHeadString('<script type="text/javascript">var ajaxMessages = {wait:"'.CUtil::JSEscape(GetMessage('AJAX_WAIT')).'"}</script>', true);
	$APPLICATION->AddHeadScript('/bitrix/js/main/cphttprequest.js', true);
}

function GetMenuTypes($site=false, $default_value=false)
{
	if($default_value === false)
		$default_value = "left=".GetMessage("main_tools_menu_left").",top=".GetMessage("main_tools_menu_top");

	$mt = COption::GetOptionString("fileman", "menutypes", $default_value, $site);
	if (!$mt)
		return Array();

	$armt_ = unserialize(stripslashes($mt), ['allowed_classes' => false]);
	$armt = Array();
	if (is_array($armt_))
	{
		foreach($armt_ as $key => $title)
		{
			$key = trim($key);
			if ($key == '')
				continue;
			$armt[$key] = trim($title);
		}
		return $armt;
	}

	$armt_ = explode(",", $mt);
	for ($i = 0, $c = count($armt_); $i < $c; $i++)
	{
		$pos = mb_strpos($armt_[$i], '=');
		if ($pos === false)
			continue;
		$key = trim(mb_substr($armt_[$i], 0, $pos));
		if ($key == '')
			continue;
		$armt[$key] = trim(mb_substr($armt_[$i], $pos + 1));
	}
	return $armt;
}

function SetMenuTypes($armt, $site = '', $description = false)
{
	return COption::SetOptionString('fileman', "menutypes", addslashes(serialize($armt)), $description, $site);
}

function ParseFileContent($filesrc, $params = array())
{
	/////////////////////////////////////
	// Parse prolog, epilog, title
	/////////////////////////////////////
	$filesrc = trim($filesrc);
	$prolog = $epilog = '';

	$php_doubleq = false;
	$php_singleq = false;
	$php_comment = false;
	$php_star_comment = false;
	$php_line_comment = false;

	$php_st = "<"."?";
	$php_ed = "?".">";

	if(!empty($params["use_php_parser"]) && mb_substr($filesrc, 0, 2) == $php_st)
	{
		$phpChunks = PHPParser::getPhpChunks($filesrc);
		if (!empty($phpChunks))
		{
			$prolog = $phpChunks[0];
			$filesrc = mb_substr($filesrc, mb_strlen($prolog));
		}
	}
	elseif(mb_substr($filesrc, 0, 2) == $php_st)
	{
		$fl = mb_strlen($filesrc);
		$p = 2;
		while($p < $fl)
		{
			$ch2 = mb_substr($filesrc, $p, 2);
			$ch1 = mb_substr($ch2, 0, 1);

			if($ch2==$php_ed && !$php_doubleq && !$php_singleq && !$php_star_comment)
			{
				$p+=2;
				break;
			}
			elseif(!$php_comment && $ch2=="//" && !$php_doubleq && !$php_singleq)
			{
				$php_comment = $php_line_comment = true;
				$p++;
			}
			elseif($php_line_comment && ($ch1=="\n" || $ch1=="\r" || $ch2=="?>"))
			{
				$php_comment = $php_line_comment = false;
			}
			elseif(!$php_comment && $ch2=="/*" && !$php_doubleq && !$php_singleq)
			{
				$php_comment = $php_star_comment = true;
				$p++;
			}
			elseif($php_star_comment && $ch2=="*/")
			{
				$php_comment = $php_star_comment = false;
				$p++;
			}
			elseif(!$php_comment)
			{
				if(($php_doubleq || $php_singleq) && $ch2=="\\\\")
				{
					$p++;
				}
				elseif(!$php_doubleq && $ch1=='"')
				{
					$php_doubleq=true;
				}
				elseif($php_doubleq && $ch1=='"' && mb_substr($filesrc, $p - 1, 1) != '\\')
				{
					$php_doubleq=false;
				}
				elseif(!$php_doubleq)
				{
					if(!$php_singleq && $ch1=="'")
					{
						$php_singleq=true;
					}
					elseif($php_singleq && $ch1=="'" && mb_substr($filesrc, $p - 1, 1) != '\\')
					{
						$php_singleq=false;
					}
				}
			}

			$p++;
		}

		$prolog = mb_substr($filesrc, 0, $p);
		$filesrc = mb_substr($filesrc, $p);
	}
	elseif(preg_match("'(.*?<title>.*?</title>)(.*)$'is", $filesrc, $reg))
	{
		$prolog = $reg[1];
		$filesrc= $reg[2];
	}

	$title = PHPParser::getPageTitle($filesrc, $prolog);

	$arPageProps = array();
	if($prolog <> '')
	{
		if(preg_match_all("'\\\$APPLICATION->SetPageProperty\\(([\"\\'])(.*?)(?<!\\\\)[\"\\'] *, *([\"\\'])(.*?)(?<!\\\\)[\"\\']\\);'i", $prolog, $out))
		{
			foreach($out[2] as $i => $m1)
			{
				$arPageProps[UnEscapePHPString($m1, $out[1][$i])] = UnEscapePHPString($out[4][$i], $out[3][$i]);
			}
		}
	}

	if(mb_substr($filesrc, -2) == "?".">")
	{
		if (isset($phpChunks) && count($phpChunks) > 1)
		{
			$epilog = $phpChunks[count($phpChunks)-1];
			$filesrc = mb_substr($filesrc, 0, -mb_strlen($epilog));
		}
		else
		{
			$p = mb_strlen($filesrc) - 2;
			$php_start = "<"."?";
			while(($p > 0) && (mb_substr($filesrc, $p, 2) != $php_start))
				$p--;
			$epilog = mb_substr($filesrc, $p);
			$filesrc = mb_substr($filesrc, 0, $p);
		}
	}

	return array(
		"PROLOG" => $prolog,
		"TITLE" => $title,
		"PROPERTIES" => $arPageProps,
		"CONTENT" => $filesrc,
		"EPILOG" => $epilog,
	);
}

function EscapePHPString($str, $encloser = '"')
{
	if($encloser == "'")
	{
		$from = array("\\", "'");
		$to = array("\\\\", "\\'");
	}
	else
	{
		$from = array("\\", "\$", "\"");
		$to = array("\\\\", "\\\$", "\\\"");
	}

	return str_replace($from, $to, $str);
}

function UnEscapePHPString($str, $encloser = '"')
{
	if($encloser == "'")
	{
		$from = array("\\\\", "\\'");
		$to = array("\\", "'");
	}
	else
	{
		$from = array("\\\\", "\\\$", "\\\"");
		$to = array("\\", "\$", "\"");
	}

	return str_replace($from, $to, $str);
}

function CheckSerializedData($str, $max_depth = 200)
{
	if(preg_match('/(^|;)[OC]\\:\\+{0,1}\\d+:/', $str)) // serialized objects
	{
		return false;
	}

    return true;
}

function NormalizePhone($number, $minLength = 10)
{
	$minLength = intval($minLength);
	if ($minLength <= 0 || mb_strlen($number) < $minLength)
	{
		return false;
	}

	if (mb_strlen($number) >= 10 && mb_substr($number, 0, 2) === '+8')
	{
		$number = '00'.mb_substr($number, 1);
	}

	$number = preg_replace("/[^0-9\#\*,;]/i", "", $number);
	if (mb_strlen($number) >= 10)
	{
		if (mb_substr($number, 0, 2) == '80' || mb_substr($number, 0, 2) == '81' || mb_substr($number, 0, 2) == '82')
		{
		}
		else if (mb_substr($number, 0, 2) == '00')
		{
			$number = mb_substr($number, 2);
		}
		else if (mb_substr($number, 0, 3) == '011')
		{
			$number = mb_substr($number, 3);
		}
		else if (mb_substr($number, 0, 1) == '8')
		{
			$number = '7'.mb_substr($number, 1);
		}
		else if (mb_substr($number, 0, 1) == '0')
		{
			$number = mb_substr($number, 1);
		}
	}

	return $number;
}

function bxmail($to, $subject, $message, $additional_headers="", $additional_parameters="", Main\Mail\Context $context = null)
{
	if (empty($context))
	{
		$context = new Main\Mail\Context();
	}

	$event = new Main\Event(
		'main',
		'OnBeforePhpMail',
		array(
			'arguments' => (object) array(
				'to' => &$to,
				'subject' => &$subject,
				'message' => &$message,
				'additional_headers' => &$additional_headers,
				'additional_parameters' => &$additional_parameters,
				'context' => &$context,
			),
		)
	);
	$event->send();

	$defaultMailConfiguration = Configuration::getValue("smtp");
	$smtpEnabled =
		is_array($defaultMailConfiguration)
		&& isset($defaultMailConfiguration['enabled'])
		&& $defaultMailConfiguration['enabled'] === true
	;

	if (
		$smtpEnabled
		&& (
			$context->getSmtp() !== null
			|| (!empty($defaultMailConfiguration['host']) && !empty($defaultMailConfiguration['login']))
		)
	)
	{
		$mailer = Main\Mail\Smtp\Mailer::getInstance($context);
		return $mailer->sendMailBySmtp($to, $subject, $message, $additional_headers, $additional_parameters);
	}

	//message must not contain any null bytes
	$message = str_replace("\0", ' ', $message);

	if(function_exists("custom_mail"))
	{
		return custom_mail($to, $subject, $message, $additional_headers, $additional_parameters, $context);
	}

	if($additional_parameters!="")
	{
		return @mail($to, $subject, $message, $additional_headers, $additional_parameters);
	}

	return @mail($to, $subject, $message, $additional_headers);
}

/**
 * @deprecated Use \Bitrix\Main\Application::resetAccelerator().
 */
function bx_accelerator_reset()
{
	Application::resetAccelerator();
}

/**
 * @deprecated Use Main\Config\Ini::getBool().
 */
function ini_get_bool($param)
{
	return Main\Config\Ini::getBool((string)$param);
}

/**
 * Sorting array by column.
 * You can use short mode: Collection::sortByColumn($arr, 'value'); This is equal Collection::sortByColumn($arr, array('value' => SORT_ASC))
 *
 * Pay attention: if two members compare as equal, their relative order in the sorted array is undefined. The sorting is not stable.
 *
 * More example:
 * Collection::sortByColumn($arr, array('value' => array(SORT_NUMERIC, SORT_ASC), 'attr' => SORT_DESC), array('attr' => 'strlen'), 'www');
 *
 * @param array        $array
 * @param string|array $columns
 * @param string|array $callbacks
 * @param bool         $preserveKeys If false numeric keys will be re-indexed. If true - preserve.
 * @param null         $defaultValueIfNotSetValue If value not set - use $defaultValueIfNotSetValue (any cols)
 */
function sortByColumn(array &$array, $columns, $callbacks = '', $defaultValueIfNotSetValue = null, $preserveKeys = false)
{
	Main\Type\Collection::sortByColumn($array, $columns, $callbacks, $defaultValueIfNotSetValue, $preserveKeys);
}

function getLocalPath($path, $baseFolder = "/bitrix")
{
	$root = rtrim($_SERVER["DOCUMENT_ROOT"], "\\/");

	static $hasLocalDir = null;
	if($hasLocalDir === null)
	{
		$hasLocalDir = is_dir($root."/local");
	}

	if($hasLocalDir && file_exists($root."/local/".$path))
	{
		return "/local/".$path;
	}
	elseif(file_exists($root.$baseFolder."/".$path))
	{
		return $baseFolder."/".$path;
	}

	// cli repository mode
	if (empty($_SERVER["DOCUMENT_ROOT"]) || defined('REPOSITORY_ROOT'))
	{
		$root = realpath(__DIR__ . '/../../');
		$localPath = $root . '/' . $path;

		if (file_exists($localPath))
		{
			return $localPath;
		}
	}

	return false;
}

/**
 * Set session expired, e.g. if you want to destroy session after this hit
 * @param bool $pIsExpired
 */
function setSessionExpired($pIsExpired = true)
{
	Application::getInstance()->getKernelSession()->set("IS_EXPIRED", $pIsExpired);
}

/**
 * @return bool
 */
function isSessionExpired()
{
	return Application::getInstance()->getKernelSession()->get("IS_EXPIRED") === true;
}

$SHOWIMAGEFIRST = false;

function ShowImage($PICTURE_ID, $iMaxW=0, $iMaxH=0, $sParams=false, $strImageUrl="", $bPopup=false, $strPopupTitle=false,$iSizeWHTTP=0, $iSizeHHTTP=0)
{
	return CFile::ShowImage($PICTURE_ID, $iMaxW, $iMaxH, $sParams, $strImageUrl, $bPopup, $strPopupTitle,$iSizeWHTTP, $iSizeHHTTP);
}

function BXClearCache($full = false, $initdir = '')
{
	return CPHPCache::ClearCache($full, $initdir);
}

function RegisterModule($id)
{
	Main\ModuleManager::registerModule($id);
}

function UnRegisterModule($id)
{
	Main\ModuleManager::unRegisterModule($id);
}

function AddEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $CALLBACK, $SORT=100, $FULL_PATH = false)
{
	$eventManager = Main\EventManager::getInstance();
	return $eventManager->addEventHandlerCompatible($FROM_MODULE_ID, $MESSAGE_ID, $CALLBACK, $FULL_PATH, $SORT);
}

function RemoveEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $iEventHandlerKey)
{
	$eventManager = Main\EventManager::getInstance();
	return $eventManager->removeEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $iEventHandlerKey);
}

function GetModuleEvents($MODULE_ID, $MESSAGE_ID, $bReturnArray = false)
{
	$eventManager = Main\EventManager::getInstance();
	$arrResult = $eventManager->findEventHandlers($MODULE_ID, $MESSAGE_ID);

	foreach($arrResult as $k => $event)
	{
		$arrResult[$k]['FROM_MODULE_ID'] = $MODULE_ID;
		$arrResult[$k]['MESSAGE_ID'] = $MESSAGE_ID;
	}

	if($bReturnArray)
	{
		return $arrResult;
	}
	else
	{
		$resRS = new CDBResult;
		$resRS->InitFromArray($arrResult);
		return $resRS;
	}
}

/**
 * @deprecated
 */
function ExecuteModuleEvent($arEvent)
{
	$args = [];
	for ($i = 1, $nArgs = func_num_args(); $i < $nArgs; $i++)
	{
		$args[] = func_get_arg($i);
	}

	return ExecuteModuleEventEx($arEvent, $args);
}

function ExecuteModuleEventEx($arEvent, $arParams = [])
{
	$result = true;

	if(
		isset($arEvent["TO_MODULE_ID"])
		&& $arEvent["TO_MODULE_ID"]<>""
		&& $arEvent["TO_MODULE_ID"]<>"main"
	)
	{
		if(!CModule::IncludeModule($arEvent["TO_MODULE_ID"]))
		{
			return null;
		}
	}
	elseif(
		isset($arEvent["TO_PATH"])
		&& $arEvent["TO_PATH"]<>""
		&& file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"])
	)
	{
		$result = include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.$arEvent["TO_PATH"]);
	}
	elseif(
		isset($arEvent["FULL_PATH"])
		&& $arEvent["FULL_PATH"]<>""
		&& file_exists($arEvent["FULL_PATH"])
	)
	{
		$result = include_once($arEvent["FULL_PATH"]);
	}

	if ((empty($arEvent["TO_CLASS"]) || empty($arEvent["TO_METHOD"])) && !isset($arEvent["CALLBACK"]))
	{
		return $result;
	}

	if (isset($arEvent["TO_METHOD_ARG"]) && is_array($arEvent["TO_METHOD_ARG"]) && !empty($arEvent["TO_METHOD_ARG"]))
	{
		$args = array_merge($arEvent["TO_METHOD_ARG"], $arParams);
	}
	else
	{
		$args = $arParams;
	}

	//TODO: ¬озможно заменить на EventManager::getInstance()->getLastEvent();
	global $BX_MODULE_EVENT_LAST;
	$BX_MODULE_EVENT_LAST = $arEvent;

	if (isset($arEvent["CALLBACK"]))
	{
		$result = call_user_func_array($arEvent["CALLBACK"], $args);
	}
	else
	{
		//php bug: http://bugs.php.net/bug.php?id=47948
		if (class_exists($arEvent["TO_CLASS"]) && is_callable([$arEvent["TO_CLASS"], $arEvent["TO_METHOD"]]))
		{
			$result =  call_user_func_array([$arEvent["TO_CLASS"], $arEvent["TO_METHOD"]], $args);
		}
		else
		{
			$exception = new SystemException("Event handler error: could not invoke {$arEvent["TO_CLASS"]}::{$arEvent["TO_METHOD"]}. Class or method does not exist.");
			$application = Application::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);

			$result = null;
		}
	}

	return $result;
}

function UnRegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $TO_PATH="", $TO_METHOD_ARG = array())
{
	$eventManager = Main\EventManager::getInstance();
	$eventManager->unRegisterEventHandler($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS, $TO_METHOD, $TO_PATH, $TO_METHOD_ARG);
}

function RegisterModuleDependences($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS="", $TO_METHOD="", $SORT=100, $TO_PATH="", $TO_METHOD_ARG = array())
{
	$eventManager = Main\EventManager::getInstance();
	$eventManager->registerEventHandlerCompatible($FROM_MODULE_ID, $MESSAGE_ID, $TO_MODULE_ID, $TO_CLASS, $TO_METHOD, $SORT, $TO_PATH, $TO_METHOD_ARG);
}

function IsModuleInstalled($module_id)
{
	return Main\ModuleManager::isModuleInstalled($module_id);
}

function GetModuleID($str)
{
	$arr = explode("/",$str);
	$i = array_search("modules",$arr);
	return $arr[$i+1];
}

/**
 * @deprecated Use version_compare()
 * Returns TRUE if version1 >= version2
 * version1 = "XX.XX.XX"
 * version2 = "XX.XX.XX"
 */
function CheckVersion($version1, $version2)
{
	return (version_compare($version1, $version2) >= 0);
}
