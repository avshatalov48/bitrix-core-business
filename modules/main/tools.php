<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main\UI\Extension;

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
		$reference_id = $ar["REFERENCE_ID"];
		$reference = $ar["REFERENCE"];
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
		if (strlen($value)>0 && $value!="NOT_REF")
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
	$year  = intval($ar["YYYY"]);
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
		$middletime = isset($ar['TT']) ? $ar['TT'] : $ar['T'];
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
	$min   = intval($ar["MI"]);
	$sec   = intval($ar["SS"]);

	if (!checkdate($month, $day, $year))
		return false;

	if ($hour>24 || $hour<0 || $min<0 || $min>59 || $sec<0 || $sec>59)
		return false;

	$s1 = preg_replace("~([^:\\\\/\\s.,0-9-]+|[^:\\\\/\\s.,a-z-]+)[\n\r\t ]*~i".BX_UTF_PCRE_MODIFIER, "P", $datetime);
	$s2 = preg_replace("/(DD|MMMM|MM|MI|M|YYYY|HH|H|GG|G|SS|TT|T)[\n\r\t ]*/i".BX_UTF_PCRE_MODIFIER, "P", $format);

	if(strlen($s1) <= strlen($s2))
		return $s1 == substr($s2, 0, strlen($s1));
	else
		return $s2 == substr($s1, 0, strlen($s2));
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
			$month = GetNumMonth($ar["M"], true);
			if (!$month)
				$month = intval(date('m', strtotime($ar["M"])));
		}
	}
	$year  = intval($ar["YYYY"]);
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
		$middletime = isset($ar['TT']) ? $ar['TT'] : $ar['T'];
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
	$min   = intval($ar["MI"]);
	$sec   = intval($ar["SS"]);

	if(!checkdate($month, $day, $year))
		return false;

	if($hour>24 || $hour<0 || $min<0 || $min>59 || $sec<0 || $sec>59)
		return false;

	$ts = mktime($hour, $min, $sec, $month, $day, $year);
	if($ts === false || ($ts == -1 && version_compare(phpversion(), '5.1.0') < 0))
		return false;

	return $ts;
}

/**
 * Parse a date into an array
 */
function ParseDateTime($datetime, $format=false)
{
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
				if (is_numeric($dt_args[0][$i]))
				{
					$arrResult[$v] = sprintf("%0".strlen($v)."d", intval($dt_args[0][$i]));
				}
				elseif(($dt_args[0][$i] == "am" || $dt_args[0][$i] == "pm") && array_search("T", $fm_args[0]) !== false)
				{
					$arrResult["T"] = $dt_args[0][$i];
				}
				elseif(($dt_args[0][$i] == "AM" || $dt_args[0][$i] == "PM") && array_search("TT", $fm_args[0]) !== false)
				{
					$arrResult["TT"] = $dt_args[0][$i];
				}
				elseif(isset($dt_args[0][$i]))
				{
					$arrResult[$v] = $dt_args[0][$i];
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
	if (is_array($arrAdd) && count($arrAdd)>0)
	{
		while(list($key, $value) = each($arrAdd))
		{
			$value = intval($value);
			if (is_int($value))
			{
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
		$middletime = isset($arParsedDate['TT']) ? $arParsedDate['TT'] : $arParsedDate['T'];
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

	$ts = mktime($arParsedDate['HH'], $arParsedDate['MI'], (isset($arParsedDate['SS']) ? $arParsedDate['SS'] : 0), 3, 7, 2012);
	return FormatDate($DB->dateFormatToPHP($toFormat), $ts);
}

/**
 * @param string|array $format
 * @param int|bool|\Bitrix\Main\Type\DateTime $timestamp
 * @param int|bool|\Bitrix\Main\Type\DateTime $now
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
	else if ($timestamp instanceof \Bitrix\Main\Type\DateTime)
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
	else if ($now instanceof \Bitrix\Main\Type\DateTime)
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
	$currentLanguage = \Bitrix\Main\Localization\Loc::getCurrentLang();
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

			if(strlen($time))
				$result .= GetMessage("FD_DAY_AT_TIME", array("#DAY#" => $day, "#TIME#" => $time));
			else
				$result .= $day;
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
		$middletime = isset($arParsedDate['TT']) ? $arParsedDate['TT'] : $arParsedDate['T'];
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
			intval($arParsedDate["HH"]),
			intval($arParsedDate["MI"]),
			intval($arParsedDate["SS"]),
			intval($arParsedDate["MM"]),
			intval($arParsedDate["DD"]),
			intval($arParsedDate["YY"])
		);

		$new_format_l = strlen($new_format);
		for ($i = 0; $i < $new_format_l; $i++)
		{
			$simbol = substr($new_format, $i ,1);
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
					$match = date(substr($new_format, $i ,1), $ux_time);
					$dayPattern = GetMessage("DOM_PATTERN");
					if ($dayPattern)
					{
						$match = str_replace("#DAY#", $match, $dayPattern);
					}
					break;
				default:
					$match = date(substr($new_format, $i ,1), $ux_time);
					break;
			}
			$strResult .= $match;
		}
	}
	else
	{
		if($arParsedDate["MM"]<1 || $arParsedDate["MM"]>12)
			$arParsedDate["MM"] = 1;
		$new_format_l = strlen($new_format);
		for ($i = 0; $i < $new_format_l; $i++)
		{
			$simbol = substr($new_format, $i ,1);
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
					$match = substr($arParsedDate["YY"], 2);
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

					if (substr($new_format, $i, 1) == "a")
						$match = strToLower($match);
					break;
				default:
					$match = substr($new_format, $i ,1);
					break;
			}
			$strResult .= $match;
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

// возвращает врем€ в формате текущего €зыка по заданному Unix Timestamp
function GetTime($timestamp, $type="SHORT", $site=false, $bSearchInSitesOnly = false)
{
	global $DB;
	if($site===false && defined("SITE_ID"))
		$site = SITE_ID;
	return date($DB->DateFormatToPHP(CSite::GetDateFormat($type, $site, $bSearchInSitesOnly)), $timestamp);
}

// устаревша€ функци€
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
	$bound = min(strlen($format), count($args));
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
	$arr = array("d.m.Y","d.m.Y H:i","d.m.Y H:i:s");
	if (!(in_array($format,$arr)))
		return false;

	$strDT = preg_replace("/[\n\r\t ]+/", " ", $strDT);
	list($date,$time) = explode(" ",$strDT);
	$date  = trim($date);
	$time  = trim($time);
	list($day,$month,$year) = explode(".",$date);
	list($hour,$min,$sec)   = explode(":",$time);
	$day   = intval($day);
	$month = intval($month);
	$year  = intval($year);
	$hour  = intval($hour);
	$min   = intval($min);
	$sec   = intval($sec);
	if (!checkdate($month,$day,$year))
		return false;
	if ($hour>24 || $hour<0 || $min<0 || $min>59 || $sec<0 || $sec>59)
		return false;

	$ts = mktime($hour,$min,$sec,$month,$day,$year);
	if($ts <= 0)
		return false;

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
	if (is_array($arSort) && count($arSort)>0)
	{
		$arSort2 = array();
		foreach($arSort as $val)
		{
			$arSort1 = explode(" ", trim($val));
			$order = array_pop($arSort1);
			$order_ = strtoupper(trim($order));
			if (!($order_=="DESC" || $order_=="ASC"))
			{
				$arSort1[] = $order;
				$order_ = "";
			}
			$by = implode(" ", $arSort1);
			if(strlen($by)>0 && !array_key_exists($by, $arSort2))
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
	if (is_array($arr) && count($arr)>0)
	{
		while (list($key, $value)=each($arr))
		{
			global $$value;
			$arr_res[$key] = $$value;
		}
	}
	return $arr_res;
}

function InitBVarFromArr($arr)
{
	if (is_array($arr) && count($arr)>0)
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
	while (list($key,$value)=each($arr))
	{
		if ($trim_value)
		{
			$arr[$key] = trim($value);
		}
		if (strlen(trim($value))<=0)
		{
			unset($arr[$key]);
			$found = true;
		}
	}
	reset($arr);
	return ($found) ? true : false;
}

function is_set(&$a, $k=false)
{
	if ($k===false)
		return isset($a);

	if(is_array($a))
		return array_key_exists($k, $a);

	return false;
}

/*********************************************************************
—троки
*********************************************************************/

function randString($pass_len=10, $pass_chars=false)
{
	static $allchars = "abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789";
	$string = "";
	if(is_array($pass_chars))
	{
		while(strlen($string) < $pass_len)
		{
			if(function_exists('shuffle'))
				shuffle($pass_chars);
			foreach($pass_chars as $chars)
			{
				$n = strlen($chars) - 1;
				$string .= $chars[mt_rand(0, $n)];
			}
		}
		if(strlen($string) > count($pass_chars))
			$string = substr($string, 0, $pass_len);
	}
	else
	{
		if($pass_chars !== false)
		{
			$chars = $pass_chars;
			$n = strlen($pass_chars) - 1;
		}
		else
		{
			$chars = $allchars;
			$n = 61; //strlen($allchars)-1;
		}
		for ($i = 0; $i < $pass_len; $i++)
			$string .= $chars[mt_rand(0, $n)];
	}
	return $string;
}
//alias for randString()
function GetRandomCode($len=8)
{
	return randString($len);
}

function TruncateText($strText, $intLen)
{
	if(strlen($strText) > $intLen)
		return rtrim(substr($strText, 0, $intLen), ".")."...";
	else
		return $strText;
}

function InsertSpaces($sText, $iMaxChar=80, $symbol=" ", $bHTML=false)
{
	$iMaxChar = intval($iMaxChar);
	if ($iMaxChar > 0 && strlen($sText) > $iMaxChar)
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
	while (substr($str,0,1)==$symbol or substr($str,strlen($str)-1,1)==$symbol)
		$str = TrimEx($str,$symbol);

	return $str;
}

function TrimEx($str,$symbol,$side="both")
{
	$str = trim($str);
	if ($side=="both")
	{
		if (substr($str,0,1) == $symbol) $str = substr($str,1,strlen($str));
		if (substr($str,strlen($str)-1,1) == $symbol) $str = substr($str,0,strlen($str)-1);
	}
	elseif ($side=="left")
	{
		if (substr($str,0,1) == $symbol) $str = substr($str,1,strlen($str));
	}
	elseif ($side=="right")
	{
		if (substr($str,strlen($str)-1,1) == $symbol) $str = substr($str,0,strlen($str)-1);
	}
	return $str;
}

function utf8win1251($s)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	return $APPLICATION->ConvertCharset($s, "UTF-8", "Windows-1251");
}

function ToUpper($str, $lang = false)
{
	static $lower = array();
	static $upper = array();
	if(!defined("BX_CUSTOM_TO_UPPER_FUNC"))
	{
		if(defined("BX_UTF"))
		{
			return strtoupper($str);
		}
		else
		{
			if($lang === false)
				$lang = LANGUAGE_ID;
			if(!isset($lower[$lang]))
			{
				$arMsg = IncludeModuleLangFile(__FILE__, $lang, true);
				$lower[$lang] = $arMsg["ABC_LOWER"];
				$upper[$lang] = $arMsg["ABC_UPPER"];
			}
			return strtoupper(strtr($str, $lower[$lang], $upper[$lang]));
		}
	}
	else
	{
		$func = BX_CUSTOM_TO_UPPER_FUNC;
		return $func($str);
	}
}

function ToLower($str, $lang = false)
{
	static $lower = array();
	static $upper = array();
	if(!defined("BX_CUSTOM_TO_LOWER_FUNC"))
	{
		if(defined("BX_UTF"))
		{
			return strtolower($str);
		}
		else
		{
			if($lang === false)
				$lang = LANGUAGE_ID;
			if(!isset($lower[$lang]))
			{
				$arMsg = IncludeModuleLangFile(__FILE__, $lang, true);
				$lower[$lang] = $arMsg["ABC_LOWER"];
				$upper[$lang] = $arMsg["ABC_UPPER"];
			}
			return strtolower(strtr($str, $upper[$lang], $lower[$lang]));
		}
	}
	else
	{
		$func = BX_CUSTOM_TO_LOWER_FUNC;
		return $func($str);
	}
}

/**********************************
 онвертаци€ текста дл€ EMail
**********************************/
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
		$s2 = str_repeat("-", strlen($s1));
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
		return "<table class='".$this->quoteTableClass."' width='95%' border='0' cellpadding='3' cellspacing='1'><tr><td class='".$this->quoteHeadClass."'>".GetMessage("MAIN_QUOTE")."</td></tr><tr><td class='".$this->quoteBodyClass."'>";
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
		$s = "<a class=\"".$this->linkClass."\" href=\"mailto:".delete_special_symbols($s)."\" title=\"".GetMessage("MAIN_MAILTO")."\">".$s."</a>";
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

		$code_mess = GetMessage("MAIN_CODE");
		$text = str_replace("!http!", "http", $text);
		$text = str_replace("!https!", "https", $text);
		$text = str_replace("!ftp!", "ftp", $text);
		$text = str_replace("!@!", "@", $text);

		$return = "<table class='".$this->codeTableClass."'><tr><td class='".$this->codeHeadClass."'>$code_mess</td></tr><tr><td class='".$this->codeBodyClass."'><textarea class='".$this->codeTextClass."' contentEditable=false cols=60 rows=15 wrap=virtual>$text</textarea></td></tr></table>";

		return $return;
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
	$text = Trim($text);
	if(strlen($text)<=0)
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
	$text = preg_replace("#\\[/quote(.*?)\\]#is", "\n>".str_repeat("-", strlen($s))."\n", $text);

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
	while(strpos(",}])>.", substr($s, -1, 1))!==false)
	{
		$s2 = substr($s, -1, 1);
		$s = substr($s, 0, strlen($s)-1);
	}
	$res = chr(1).$s."/".chr(1).$s2;
	return $res;
}

function convert_to_href($url, $link_class="", $event1="", $event2="", $event3="", $script="", $link_target="_self")
{
	$url = stripslashes($url);
	$goto = $url;
	if (strlen($event1)>0 || strlen($event2)>0)
	{
		$script = strlen($script)>0 ? $script : "/bitrix/redirect.php";
		$goto = $script.
			"?event1=".urlencode($event1).
			"&event2=".urlencode($event2).
			"&event3=".urlencode($event3).
			"&goto=".urlencode($goto);
	}
	$target = $link_target == '_self'? '': ' target="'.$link_target.'"';

	$s = "<a class=\"".$link_class."\" href=\"".delete_special_symbols($goto)."\"".$target.">".$url."</a>";
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
	$arUrlEvent            = array(),        // массив в нем если заданы ключи EVENT1, EVENT2, EVENT3 то ссылки будут через $arUrlEvent["SCRIPT"] (по умолчанию равен "/bitrix/redirect.php")
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
		$script = $arUrlEvent["SCRIPT"];
		$helper = new CConvertorsPregReplaceHelper("");
		$helper->setLinkClass($link_class);
		$helper->setLinkTarget($link_target);
		$helper->setEvents($arUrlEvent["EVENT1"], $arUrlEvent["EVENT2"], $arUrlEvent["EVENT3"]);
		if (strlen($script))
			$helper->setScript($script);
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
		"'<select[^>]*?>.*?</select>'si",
		"'&(quot|#34);'i",
		"'&(iexcl|#161);'i",
		"'&(cent|#162);'i",
		"'&(pound|#163);'i",
		"'&(copy|#169);'i",
	);

	static $replace = array(
		"",
		"",
		"",
		"\"",
		"\xa1",
		"\xa2",
		"\xa3",
		"\xa9",
	);

	$str = preg_replace($search, $replace, $str);

	$str = preg_replace("#<[/]{0,1}(b|i|u|em|small|strong)>#i", "", $str);
	$str = preg_replace("#<div[^>]*>#i", "\r\n", $str);
	$str = preg_replace("#<[/]{0,1}(font|div|span)[^>]*>#i", "", $str);

	//ищем списки
	$str = preg_replace("#<ul[^>]*>#i", "\r\n", $str);
	$str = preg_replace("#<li[^>]*>#i", "\r\n  - ", $str);

	//удалим то что заданно
	foreach($aDelete as $del_reg)
		$str = preg_replace($del_reg, "", $str);

	//ищем картинки
	$str = preg_replace("/(<img\\s.*?src\\s*=\\s*)([\"']?)(\\/.*?)(\\2)(\\s.+?>|\\s*>)/is", "[".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
	$str = preg_replace("/(<img\\s.*?src\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is", "[".chr(1)."\\3".chr(1)."] ", $str);

	//ищем ссылки
	$str = preg_replace("/(<a\\s.*?href\\s*=\\s*)([\"']?)(\\/.*?)(\\2)(.*?>)(.*?)<\\/a>/is", "\\6 [".chr(1).$strSiteUrl."\\3".chr(1)."] ", $str);
	$str = preg_replace("/(<a\\s.*?href\\s*=\\s*)([\"']?)(.*?)(\\2)(.*?>)(.*?)<\\/a>/is", "\\6 [".chr(1)."\\3".chr(1)."] ", $str);

	//ищем <br>
	$str = preg_replace("#<br[^>]*>#i", "\r\n", $str);

	//ищем <p>
	$str = preg_replace("#<p[^>]*>#i", "\r\n\r\n", $str);

	//ищем <hr>
	$str = preg_replace("#<hr[^>]*>#i", "\r\n----------------------\r\n", $str);

	//ищем таблицы
	$str = preg_replace("#<[/]{0,1}(thead|tbody)[^>]*>#i", "", $str);
	$str = preg_replace("#<([/]{0,1})th[^>]*>#i", "<\\1td>", $str);

	$str = preg_replace("#</td>#i", "\t", $str);
	$str = preg_replace("#</tr>#i", "\r\n", $str);
	$str = preg_replace("#<table[^>]*>#i", "\r\n", $str);

	$str = preg_replace("#\r\n[ ]+#", "\r\n", $str);

	//мочим вообще все оставшиес€ тэги
	$str = preg_replace("#<[/]{0,1}[^>]+>#i", "", $str);

	$str = preg_replace("#[ ]+ #", " ", $str);
	$str = str_replace("\t", "    ", $str);

	//переносим длинные строки
	if($maxlen > 0)
		$str = preg_replace("#(^|[\\r\\n])([^\\n\\r]{".intval($maxlen)."}[^ \\r\\n]*[\\] ])([^\\r])#", "\\1\\2\r\n\\3", $str);

	$str = str_replace(chr(1), " ",$str);
	return trim($str);
}

function FormatText($strText, $strTextType="text")
{
	if(strtolower($strTextType)=="html")
		return $strText;

	return TxtToHtml($strText);
}

function htmlspecialcharsEx($str)
{
	static $search =  array("&amp;",     "&lt;",     "&gt;",     "&quot;",     "&#34;",     "&#x22;",     "&#39;",     "&#x27;",     "<",    ">",    "\"");
	static $replace = array("&amp;amp;", "&amp;lt;", "&amp;gt;", "&amp;quot;", "&amp;#34;", "&amp;#x22;", "&amp;#39;", "&amp;#x27;", "&lt;", "&gt;", "&quot;");
	return str_replace($search, $replace, $str);
}

function htmlspecialcharsback($str)
{
	static $search =  array("&lt;", "&gt;", "&quot;", "&apos;", "&amp;");
	static $replace = array("<",    ">",    "\"",     "'",      "&");
	return str_replace($search, $replace, $str);
}

function htmlspecialcharsbx($string, $flags = ENT_COMPAT, $doubleEncode = true)
{
	//function for php 5.4 where default encoding is UTF-8
	return htmlspecialchars($string, $flags, (defined("BX_UTF")? "UTF-8" : "ISO-8859-1"), $doubleEncode);
}

function CheckDirPath($path, $bPermission = true)
{
	$path = str_replace(array("\\", "//"), "/", $path);

	//remove file name
	if(substr($path, -1) != "/")
	{
		$p = strrpos($path, "/");
		$path = substr($path, 0, $p);
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

function CopyDirFiles($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "")
{
	if (strpos($path_to."/", $path_from."/")===0 || realpath($path_to) === realpath($path_from))
		return false;

	if (is_dir($path_from))
	{
		CheckDirPath($path_to."/");
	}
	elseif(is_file($path_from))
	{
		$p = bxstrrpos($path_to, "/");
		$path_to_dir = substr($path_to, 0, $p);
		CheckDirPath($path_to_dir."/");

		if (file_exists($path_to) && !$ReWrite)
			return False;

		@copy($path_from, $path_to);
		if(is_file($path_to))
			@chmod($path_to, BX_FILE_PERMISSIONS);

		if ($bDeleteAfterCopy)
			@unlink($path_from);

		return True;
	}
	else
	{
		return True;
	}

	if ($handle = @opendir($path_from))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == "." || $file == "..")
				continue;

			if (strlen($strExclude)>0 && substr($file, 0, strlen($strExclude))==$strExclude)
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
	if(strlen($path) == 0 || $path == '/')
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

	$script_files = COption::GetOptionString("fileman", "~script_files", "php,php3,php4,php5,php6,phtml,pl,asp,aspx,cgi,dll,exe,ico,shtm,shtml,fcg,fcgi,fpl,asmx,pht,py,psp,var");
	$arScriptFiles = array();
	foreach(explode(",", strtolower($script_files)) as $ext)
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
		if($i > 0 && in_array(strtolower(TrimUnsafe($part)), $arExt))
			unset($arParts[$i]);
	}
	$path = substr(TrimUnsafe($check_name), 0, - strlen($name));
	return $path.implode(".", $arParts);
}

function HasScriptExtension($check_name)
{
	$arExt = GetScriptFileExt();

	$check_name = GetFileName($check_name);
	$arParts = explode(".", $check_name);
	foreach($arParts as $i => $part)
	{
		if($i > 0 && in_array(strtolower(TrimUnsafe($part)), $arExt))
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
			return substr($path, $pos+1);
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
			$path = substr($path, 0, $pos);
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
		return substr($path, $p+1);

	return $path;
}

function IsFileUnsafe($name)
{
	static $arFiles = false;
	if($arFiles === false)
	{
		$fileList = COption::GetOptionString("main", "~unsafe_files", ".htaccess,.htpasswd,web.config,global.asax");
		$arFiles = explode(",", strtolower($fileList));
	}
	$name = GetFileName($name);
	return in_array(strtolower(TrimUnsafe($name)), $arFiles);
}

function GetFileType($path)
{
	$extension = GetFileExtension(strtolower($path));
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

	if($page===false && $_SERVER["REQUEST_URI"]<>"")
		$page = $_SERVER["REQUEST_URI"];
	if($page===false)
		$page = $_SERVER["SCRIPT_NAME"];

	$sPath = $page;

	static $terminate = array("?", "#");
	foreach($terminate as $term)
	{
		if(($found = strpos($sPath, $term)) !== false)
		{
			$sPath = substr($sPath, 0, $found);
		}
	}

	//nginx fix
	$sPath = preg_replace("/%+[0-9a-f]{0,1}$/i", "", $sPath);

	$sPath = urldecode($sPath);

	//Decoding UTF uri
	$sPath = CUtil::ConvertToLangCharset($sPath);

	if(substr($sPath, -1, 1) == "/" && $get_index_page)
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
	$uriPath = "/".ltrim($_SERVER["REQUEST_URI"], "/");
	if (($index = strpos($uriPath, "?")) !== false)
	{
		$uriPath = substr($uriPath, 0, $index);
	}

	if (defined("BX_DISABLE_INDEX_PAGE") && BX_DISABLE_INDEX_PAGE === true)
	{
		if (substr($uriPath, -10) === "/index.php")
		{
			$uriPath = substr($uriPath, 0, -9);
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

	$found = strpos($page, "?");
	$sPath = ($found !== false? substr($page, 0, $found) : $page);

	$sPath = urldecode($sPath);

	if(substr($sPath, -1, 1) == "/" && $get_index_page)
		$sPath .= GetDirectoryIndex($sPath);

	return $sPath;
}

function GetDirPath($sPath)
{
	if(strlen($sPath))
	{
		$p = strrpos($sPath, "/");
		if($p === false)
			return '/';
		else
			return substr($sPath, 0, $p+1);
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
		$ext_len = strlen($ext);
		if(strlen($path) > $ext_len && substr($path, -$ext_len) == $ext)
			$path = substr($path, 0, -$ext_len);
	}

	return $path;
}

function bxstrrpos($haystack, $needle)
{
	if(defined("BX_UTF"))
	{
		//mb_strrpos does not work on invalid UTF-8 strings
		$ln = strlen($needle);
		for($i = strlen($haystack)-$ln; $i >= 0; $i--)
			if(substr($haystack, $i, $ln) == $needle)
				return $i;
		return false;
	}
	return strrpos($haystack, $needle);
}

function Rel2Abs($curdir, $relpath)
{
	if($relpath == "")
		return false;

	if(substr($relpath, 0, 1) == "/" || preg_match("#^[a-z]:/#i", $relpath))
	{
		$res = $relpath;
	}
	else
	{
		if(substr($curdir, 0, 1) != "/" && !preg_match("#^[a-z]:/#i", $curdir))
			$curdir = "/".$curdir;
		if(substr($curdir, -1) != "/")
			$curdir .= "/";
		$res = $curdir.$relpath;
	}

	if(($p = strpos($res, "\0")) !== false)
		$res = substr($res, 0, $p);

	$res = _normalizePath($res);

	if(substr($res, 0, 1) !== "/" && !preg_match("#^[a-z]:/#i", $res))
		$res = "/".$res;

	$res = rtrim($res, ".\\+ ");

	return $res;
}

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
	$len = strlen($_SERVER["DOCUMENT_ROOT"]);

	if (substr($path, 0, $len) == $_SERVER["DOCUMENT_ROOT"])
		return "/".ltrim(substr($path, $len), "/");
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

		if ($aReplace !== null && is_array($aReplace))
		{
			foreach($aReplace as $search => $replace)
			{
				$s = str_replace($search, $replace, $s);
			}
		}

		return $s;
	}

	return \Bitrix\Main\Localization\Loc::getMessage($name, $aReplace);
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

	if (\Bitrix\Main\Localization\Translation::allowConvertEncoding())
	{
		$langFile = \Bitrix\Main\Localization\Translation::convertLangPath($before. $lang. $after, $lang);
		if(file_exists($langFile))
		{
			return $langFile;
		}
	}

	if(file_exists($before.$lang.$after))
		return $before.$lang.$after;
	if(file_exists($before."en".$after))
		return $before."en".$after;

	if(strpos($before, "/bitrix/modules/")===false)
		return $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/en/tools.php";

	$old_path = Rtrim($before, "/");
	$old_path = substr($old_path, strlen($_SERVER["DOCUMENT_ROOT"]));
	$path = substr($old_path, 16);
	$module = substr($path, 0, strpos($path, "/"));
	$path = substr($path, strpos($path, "/"));
	if(substr($path, -5)=="/lang")
		$path = substr($path, 0, -5);
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module.$path.$after, $lang);
	return $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module."/lang/".$lang.$path.$after;
}

/**
 * @deprecated Use \Bitrix\Main\Localization\Loc
 */
function __IncludeLang($path, $bReturnArray=false, $bFileChecked=false)
{
	global $ALL_LANG_FILES;
	$ALL_LANG_FILES[] = $path;

	if (\Bitrix\Main\Localization\Translation::allowConvertEncoding())
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
			list($convertEncoding, $targetEncoding, $sourceEncoding) = $encodingCache[$language];
		}
		else
		{
			$convertEncoding = \Bitrix\Main\Localization\Translation::needConvertEncoding($language);
			$targetEncoding = $sourceEncoding = '';
			if ($convertEncoding)
			{
				$targetEncoding = \Bitrix\Main\Localization\Translation::getCurrentEncoding();
				$sourceEncoding = \Bitrix\Main\Localization\Translation::getSourceEncoding($language);
			}

			$encodingCache[$language] = array($convertEncoding, $targetEncoding, $sourceEncoding);
		}

		$path = \Bitrix\Main\Localization\Translation::convertLangPath($path, LANGUAGE_ID);

		$MESS = array();
		if ($bFileChecked)
		{
			include($path);
		}
		elseif (file_exists($path))
		{
			include($path);
		}

		if (!empty($MESS))
		{
			if ($convertEncoding)
			{
				$convertEncoding = \Bitrix\Main\Localization\Translation::checkPathRestrictionConvertEncoding($path);
			}

			foreach ($MESS as $key => $val)
			{
				if ($convertEncoding)
				{
					$val = \Bitrix\Main\Text\Encoding::convertEncoding($val, $sourceEncoding, $targetEncoding);
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
			$path = \Bitrix\Main\Localization\Translation::convertLangPath($path, LANGUAGE_ID);
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
 * @deprecated Use \Bitrix\Main\Localization\Loc
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
		if(strpos($filepath, $dir)!==false)
		{
			$templ_path = $dir;
			$templ_pos = strlen($filepath) - strpos(strrev($filepath), strrev($templ_path));
			$rel_path = substr($filepath, $templ_pos);
			$p = strpos($rel_path, "/");
			if(!$p)
				return null;
			$template_name = substr($rel_path, 0, $p);
			$file_name = substr($rel_path, $p+1);
			$p = strpos($file_name, "/");
			if($p>0)
				$module_name = substr($file_name, 0, $p);
			break;
		}
	}
	if($templ_path == "")
	{
		if(strpos($filepath, $module_path) !== false)
		{
			$templ_pos = strlen($filepath) - strpos(strrev($filepath), strrev($module_path));
			$rel_path = substr($filepath, $templ_pos);
			$p = strpos($rel_path, "/");
			if(!$p)
				return null;
			$module_name = substr($rel_path, 0, $p);
			if(defined("SITE_TEMPLATE_ID"))
				$template_name = SITE_TEMPLATE_ID;
			else
				$template_name = ".default";
			$file_name = substr($rel_path, $p + strlen("/install/templates/"));
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

	if((substr($file_name, -16) == ".description.php") && $module_name!="")
	{
		if ($subst_lang <> $lang)
		{
			$fname = $module_path.$module_name."/install/templates/lang/".$subst_lang."/".$file_name;
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $module_path.$module_name."/install/templates/lang/".$lang."/".$file_name;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
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
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
				$checkDefault = $checkModule = false;
			}
		}

		// required lang
		$fname = $templ_path.$template_name."/lang/".$lang."/".$file_name;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
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
				$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $subst_lang);
				if (file_exists($fname))
				{
					__IncludeLang($fname, false, true);
					$checkModule = false;
				}
			}

			$fname = $templ_path.".default/lang/".$lang."/".$file_name;
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
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
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $subst_lang);
			if (file_exists($fname))
			{
				__IncludeLang($fname, false, true);
			}
		}

		$fname = $module_path.$module_name."/install/templates/lang/".$lang."/".$file_name;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
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
		\Bitrix\Main\Localization\Loc::loadMessages($filepath);
		return true;
	}

	$filepath = rtrim(preg_replace("'[\\\\/]+'", "/", $filepath), "/ ");
	$module_path = "/modules/";
	if(strpos($filepath, $module_path) !== false)
	{
		$pos = strlen($filepath) - strpos(strrev($filepath), strrev($module_path));
		$rel_path = substr($filepath, $pos);
		$p = strpos($rel_path, "/");
		if(!$p)
			return false;

		$module_name = substr($rel_path, 0, $p);
		$rel_path = substr($rel_path, $p+1);
		$BX_DOC_ROOT = rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ ");
		$module_path = $BX_DOC_ROOT.getLocalPath($module_path.$module_name);
	}
	elseif(strpos($filepath, "/.last_version/") !== false)
	{
		$pos = strlen($filepath) - strpos(strrev($filepath), strrev("/.last_version/"));
		$rel_path = substr($filepath, $pos);
		$module_path = substr($filepath, 0, $pos-1);
	}
	else
	{
		return false;
	}

	if($lang === false)
		$lang = LANGUAGE_ID;

	$lang_subst = LangSubst($lang);

	$arMess = array();
	if ($lang_subst <> $lang)
	{
		$fname = $module_path."/lang/".$lang_subst."/".$rel_path;
		$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang_subst);
		if (file_exists($fname))
		{
			$arMess = __IncludeLang($fname, $bReturnArray, true);
		}
	}

	$fname = $module_path."/lang/".$lang."/".$rel_path;
	$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
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
 * @deprecated Use \Bitrix\Main\Localization\Loc
 */
function LangSubst($lang)
{
	static $arSubst = array('ua'=>'ru', 'kz'=>'ru', 'ru'=>'ru');
	if(isset($arSubst[$lang]))
		return $arSubst[$lang];
	return 'en';
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
		$n = strlen($thing);
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

function AddMessage2Log($sText, $sModule = "", $traceDepth = 6, $bShowArgs = false)
{
	if (defined("LOG_FILENAME") && strlen(LOG_FILENAME)>0)
	{
		if(!is_string($sText))
		{
			$sText = var_export($sText, true);
		}
		if (strlen($sText)>0)
		{
			ignore_user_abort(true);
			if ($fp = @fopen(LOG_FILENAME, "ab"))
			{
				if (flock($fp, LOCK_EX))
				{
					@fwrite($fp, "Host: ".$_SERVER["HTTP_HOST"]."\nDate: ".date("Y-m-d H:i:s")."\nModule: ".$sModule."\n".$sText."\n");
					$arBacktrace = Bitrix\Main\Diag\Helper::getBackTrace($traceDepth, ($bShowArgs? null : DEBUG_BACKTRACE_IGNORE_ARGS));
					$strFunctionStack = "";
					$strFilesStack = "";
					$firstFrame = (count($arBacktrace) == 1? 0: 1);
					$iterationsCount = min(count($arBacktrace), $traceDepth);
					for ($i = $firstFrame; $i < $iterationsCount; $i++)
					{
						if (strlen($strFunctionStack)>0)
							$strFunctionStack .= " < ";

						if (isset($arBacktrace[$i]["class"]))
							$strFunctionStack .= $arBacktrace[$i]["class"]."::";

						$strFunctionStack .= $arBacktrace[$i]["function"];

						if(isset($arBacktrace[$i]["file"]))
							$strFilesStack .= "\t".$arBacktrace[$i]["file"].":".$arBacktrace[$i]["line"]."\n";
						if($bShowArgs && isset($arBacktrace[$i]["args"]))
						{
							$strFilesStack .= "\t\t";
							if (isset($arBacktrace[$i]["class"]))
								$strFilesStack .= $arBacktrace[$i]["class"]."::";
							$strFilesStack .= $arBacktrace[$i]["function"];
							$strFilesStack .= "(\n";
							foreach($arBacktrace[$i]["args"] as $value)
								$strFilesStack .= "\t\t\t".$value."\n";
							$strFilesStack .= "\t\t)\n";

						}
					}

					if (strlen($strFunctionStack)>0)
					{
						@fwrite($fp, "    ".$strFunctionStack."\n".$strFilesStack);
					}

					@fwrite($fp, "----------\n");
					@fflush($fp);
					@flock($fp, LOCK_UN);
					@fclose($fp);
				}
			}
			ignore_user_abort(false);
		}
	}
}

/*********************************************************************
	Quoting reverse (to be removed with 5.4.0)
*********************************************************************/

function UnQuote($str, $type, $preserve_nulls = false)
{
	UnQuoteEx($str, "", array("type" => $type, "preserve_nulls" => $preserve_nulls));
	return $str;
}

function UnQuoteEx(&$str, $key, $params)
{
	static $search_gpc  = array("\\'", '\\"', "\\\\");
	static $replace_gpc = array("'",   '"',   "\\");

	if($params["preserve_nulls"])
		$str = str_replace("\\0", "\0", $str);
	else
		$str = str_replace("\0", "", $str);

	if($params["type"] == "gpc")
		$str = str_replace($search_gpc ,$replace_gpc, $str);
	elseif($params["type"] == "syb")
		$str = str_replace("''", "'", $str);
}

function __unquoteitem(&$item, $key, $param = Array())
{
	$register_globals = ($param["first_use"] && ini_get_bool("register_globals"));

	if(is_array($item))
	{
		$param["first_use"] = false;

		foreach($item as $k=>$v)
			__unquoteitem($item[$k], $k, $param);

		if($register_globals)
		{
			global $$key;
			if(isset($$key) && is_array($$key))
			{
				foreach($$key as $k=>$v)
					__unquoteitem($GLOBALS[$key][$k], $k, $param);
			}
		}
	}
	else
	{
		if($register_globals)
		{
			global $$key;
			if(isset($$key) && $$key==$item)
				UnQuoteEx($$key, "", $param);
		}
		UnQuoteEx($item, "", $param);
	}
}

function UnQuoteArr(&$arr, $syb = false, $preserve_nulls = false)
{
	static $params = null;
	if (!isset($params))
	{
		if (get_magic_quotes_gpc())
		{
			//Magic quotes sybase works only when magic_quotes_gpc is turned on
			if (ini_get_bool("magic_quotes_sybase"))
				$params = array("type" => "syb");
			else
				$params = array("type" => "gpc");
		}
		else
		{
			$params = array("type" => "nulls");
		}
	}

	if ($preserve_nulls != false && $params["type"] == "nulls")
		return;

	static $register_globals = null;
	if (!isset($register_globals))
		$register_globals = ini_get_bool("register_globals");

	if (is_array($arr))
	{
		$params["preserve_nulls"] = $preserve_nulls;

		foreach($arr as $key => $value)
		{
			if (is_array($value))
				array_walk_recursive($arr[$key], "UnQuoteEx", $params);
			else
				UnQuoteEx($arr[$key], "", $params);
		}

		if ($register_globals)
		{
			foreach($arr as $key => $value)
			{
				if (isset($GLOBALS[$key]))
				{
					if (is_array($value))
					{
						if (is_array($GLOBALS[$key]))
						{
							foreach($GLOBALS[$key] as $k => $v)
								array_walk_recursive($GLOBALS[$key], "UnQuoteEx", $params);
						}
					}
					else
					{
						if($GLOBALS[$key] == $value)
							UnQuoteEx($GLOBALS[$key], "", $params);
					}
				}
			}
		}
	}
}

function UnQuoteAll()
{
	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $_UNSECURE;
	$superglobals = array('_GET', '_SESSION', '_POST', '_COOKIE', '_REQUEST', '_FILES', '_SERVER', 'GLOBALS', '_ENV');

	foreach($superglobals as $key)
	{
		unset($_REQUEST[$key]);
		unset($_GET[$key]);
		unset($_POST[$key]);
		unset($_COOKIE[$key]);
		unset($HTTP_GET_VARS[$key]);
		unset($HTTP_POST_VARS[$key]);
		unset($HTTP_COOKIE_VARS[$key]);
	}

	UnQuoteArr($_GET);
	if(!defined("BX_SKIP_POST_UNQUOTE") || BX_SKIP_POST_UNQUOTE !== true)
	{
		$_UNSECURE["_POST"] = $_POST;

		UnQuoteArr($_POST);
		UnQuoteArr($_REQUEST);
		UnQuoteArr($HTTP_POST_VARS);
	}
	else
	{
		$_REQUEST = array_merge($_COOKIE, $_GET);
		UnQuoteArr($_REQUEST);
	}
	UnQuoteArr($_COOKIE);
	UnQuoteArr($HTTP_GET_VARS);
	UnQuoteArr($HTTP_COOKIE_VARS);
}

/*********************************************************************
Other functions
*********************************************************************/
function LocalRedirect($url, $skip_security_check=false, $status="302 Found")
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;
	/** @global CDatabase $DB */
	global $DB;

	if(defined("DEMO") && DEMO=="Y" && (!defined("SITEEXPIREDATE") || !defined("OLDSITEEXPIREDATE") || strlen(SITEEXPIREDATE) <= 0 || SITEEXPIREDATE != OLDSITEEXPIREDATE))
		die(GetMessage("TOOLS_TRIAL_EXP"));

	$bExternal = preg_match("'^(http://|https://|ftp://)'i", $url);

	if(!$bExternal && strpos($url, "/") !== 0)
	{
		$url = $APPLICATION->GetCurDir().$url;
	}

	//doubtful
	$url = str_replace("&amp;", "&", $url);
	// http response splitting defence
	$url = str_replace(array("\r", "\n"), "", $url);

	if(!defined("BX_UTF") && defined("LANG_CHARSET"))
	{
		$url = \Bitrix\Main\Text\Encoding::convertEncoding($url, LANG_CHARSET, "UTF-8");
	}
	
	if(function_exists("getmoduleevents"))
	{
		foreach(GetModuleEvents("main", "OnBeforeLocalRedirect", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$url, $skip_security_check, &$bExternal));
		}
	}

	if(!$bExternal)
	{
		//store cookies for next hit (see CMain::GetSpreadCookieHTML())
		$APPLICATION->StoreCookies();

		$host = $_SERVER['HTTP_HOST'];
		if($_SERVER['SERVER_PORT'] <> 80 && $_SERVER['SERVER_PORT'] <> 443 && $_SERVER['SERVER_PORT'] > 0 && strpos($_SERVER['HTTP_HOST'], ":") === false)
		{
			$host .= ":".$_SERVER['SERVER_PORT'];
		}

		$protocol = (CMain::IsHTTPS() ? "https" : "http");

		$url = $protocol."://".$host.$url;
	}

	CHTTP::SetStatus($status);

	header("Location: ".$url);

	if(function_exists("getmoduleevents"))
	{
		foreach(GetModuleEvents("main", "OnLocalRedirect", true) as $arEvent)
			ExecuteModuleEventEx($arEvent);
	}

	$_SESSION["BX_REDIRECT_TIME"] = time();

	\Bitrix\Main\Context::getCurrent()->getResponse()->flush();

	CMain::ForkActions();
	exit;
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
		strpos($HTTP_USER_AGENT, "Opera") == false
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
	return $msg["COUNTRY_".$id];
}

function GetCountryArray($lang=LANGUAGE_ID)
{
	$arMsg = IncludeModuleLangFile(__FILE__, $lang, true);
	$arr = array();
	foreach($arMsg as $id=>$country)
		if(strpos($id, "COUNTRY_") === 0)
			$arr[intval(substr($id, 8))] = $country;
	asort($arr);
	$arCountry = array("reference_id"=>array_keys($arr), "reference"=>array_values($arr));
	return $arCountry;
}

function GetCountries($lang=LANGUAGE_ID)
{
	static $result = null;
	if(!is_null($result))
		return $result;

	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/countries.php");
	$msg = IncludeModuleLangFile(__FILE__, $lang, true);

	$result = array();
	foreach ($arCounries as $country => $countryId)
	{
		$result[] = array(
			'ID' => $countryId,
			'CODE' => $country,
			'NAME' => $msg["COUNTRY_".$countryId]
		);
	}
	return $result;
}

function GetCountryIdByCode($code)
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/countries.php");
	$code = strtoupper($code);
	if(isset($arCounries[$code]))
		return $arCounries[$code];
	return false;
}

function GetCountryCodeById($countryId)
{
	$countryId = (int)$countryId;

	static $countryCodes = null;
	if(is_null($countryCodes))
	{
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/countries.php");
		$countryCodes = array_flip($arCounries);
	}

	return isset($countryCodes[$countryId]) ? $countryCodes[$countryId] : '';
}

function minimumPHPVersion($vercheck)
{
	$minver = explode(".", $vercheck);
	$curver = explode(".", phpversion());
	if ((IntVal($curver[0]) < IntVal($minver[0])) || ((IntVal($curver[0]) == IntVal($minver[0])) && (IntVal($curver[1]) < IntVal($minver[1]))) || ((IntVal($curver[0]) == IntVal($minver[0])) && (IntVal($curver[1]) == IntVal($minver[1])) && (IntVal($curver[2]) < IntVal($minver[2]))))
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

		//$GLOBALS += $toGlobals;
		//PHP7 bug
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
				if (isset($vals[$i]['tag']))
				{
					$tagname = $vals[$i]['tag'];
				}
				else
				{
					$tagname = '';
				}

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
	if (strlen($help_file)<=0) $help_file = basename($APPLICATION->GetCurPage());
	if (strlen($anchor)>0) $anchor = "#".$anchor;

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
	$start = strpos($url, "?");
	if ($start!==false)
	{
		$end = strpos($url, "#");
		$length = ($end>0) ? $end-$start-1 : strlen($url);
		$params = substr($url, $start+1, $length);
		parse_str($params, $_GET);
		parse_str($params, $arr);
		$_REQUEST += $arr;
		$GLOBALS += $arr;
	}
}

function _ShowHtmlspec($str)
{
	$str = str_replace("<br>", "\n", $str);
	$str = str_replace("<br />", "\n", $str);
	$str = htmlspecialcharsbx($str);
	$str = nl2br($str);
	$str = str_replace("&amp;", "&", $str);
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

	if($arMess["MESSAGE"] <> "")
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
	if(count($_GET) < 1)
		return "";

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

function check_email($email, $bStrict=false)
{
	if(!$bStrict)
	{
		$email = trim($email);
		if(preg_match("#.*?[<\\[\\(](.*?)[>\\]\\)].*#i", $email, $arr) && strlen($arr[1])>0)
			$email = $arr[1];
	}

	//http://tools.ietf.org/html/rfc2821#section-4.5.3.1
	//4.5.3.1. Size limits and minimums
	if(strlen($email) > 320)
	{
		return false;
	}

	//http://tools.ietf.org/html/rfc2822#section-3.2.4
	//3.2.4. Atom
	static $atom = "=_0-9a-z+~'!\$&*^`|\\#%/?{}-";

	//"." can't be in the beginning or in the end of local-part
	//dot-atom-text = 1*atext *("." 1*atext)
	if(preg_match("#^[".$atom."]+(\\.[".$atom."]+)*@(([-0-9a-z]+\\.)+)([a-z0-9-]{2,20})$#i", $email))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function initvar($varname, $value='')
{
	global $$varname;
	if(!isset($$varname))
		$$varname=$value;
}

function ClearVars($prefix="str_")
{
	$n = strlen($prefix);
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
	if($len>0 && strlen($value)>$len-$dec)
		$value = trim(substr($value, 0, $len-$dec), ".");
	return $value;
}

function bitrix_sessid()
{
	if(!is_array($_SESSION) || !isset($_SESSION['fixed_session_id']))
		bitrix_sessid_set();
	return $_SESSION["fixed_session_id"];
}

function bitrix_sessid_set($val=false)
{
	if($val === false)
		$val = bitrix_sessid_val();
	$_SESSION["fixed_session_id"] = $val;
}

function bitrix_sessid_val()
{
	return md5(CMain::GetServerUniqID().session_id());
}

function bitrix_sess_sign()
{
	return md5("nobody".CMain::GetServerUniqID()."nowhere");
}

function check_bitrix_sessid($varname='sessid')
{
	return
		$_REQUEST[$varname] === bitrix_sessid() ||
		\Bitrix\Main\Context::getCurrent()->getRequest()->getHeader('X-Bitrix-Csrf-Token') === bitrix_sessid()
	;
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
	return (strlen($strUrl) <= 0? $strText : "<a href=\"".$strUrl."\" ".$sParams.">".$strText."</a>");
}

function IncludeAJAX()
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	$APPLICATION->AddHeadString('<script type="text/javascript">var ajaxMessages = {wait:"'.CUtil::JSEscape(GetMessage('AJAX_WAIT')).'"}</script>', true);
	$APPLICATION->AddHeadScript('/bitrix/js/main/cphttprequest.js', true);
}

class CJSCore
{
	const USE_ADMIN = 'admin';
	const USE_PUBLIC = 'public';

	private static $arRegisteredExt = array();
	private static $arAutoloadQueue = array();
	private static $arCurrentlyLoadedExt = array();

	private static $bInited = false;
	private static $compositeMode = false;

	/*
	ex: CJSCore::RegisterExt('timeman', array(
		'js' => '/bitrix/js/timeman/core_timeman.js',
		'css' => '/bitrix/js/timeman/css/core_timeman.css',
		'lang' => '/bitrix/modules/timeman/js_core_timeman.php',
		'rel' => array(needed extensions for automatic inclusion),
		'use' => CJSCore::USE_ADMIN|CJSCore::USE_PUBLIC
	));
	*/
	public static function RegisterExt($name, $arPaths)
	{
		if(isset($arPaths['use']))
		{
			switch($arPaths['use'])
			{
				case CJSCore::USE_PUBLIC:
					if(defined("ADMIN_SECTION") && ADMIN_SECTION === true)
						return;

				break;
				case CJSCore::USE_ADMIN:
					if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
						return;

				break;
			}
		}

		if (isset($arPaths['lang']))
		{
			$arPaths['lang'] = str_replace("/lang/".LANGUAGE_ID."/", "/", $arPaths['lang']);
		}

		self::$arRegisteredExt[$name] = $arPaths;

		if (isset($arPaths['autoload']))
		{
			self::$arAutoloadQueue[$name] = $arPaths;
		}
	}

	public static function Init($arExt = array(), $bReturn = false)
	{
		if (!self::$bInited)
		{
			self::_RegisterStandardExt();
			self::$bInited = true;
		}

		if (!is_array($arExt) && strlen($arExt) > 0)
			$arExt = array($arExt);

		$bReturn = ($bReturn === true); // prevent syntax mistake

		$bNeedCore = false;
		if (count($arExt) > 0)
		{
			foreach ($arExt as $ext)
			{
				if (
					isset(self::$arRegisteredExt[$ext])
					&& (
						!isset(self::$arRegisteredExt[$ext]['skip_core'])
						|| !self::$arRegisteredExt[$ext]['skip_core']
					)
				)
				{
					$bNeedCore = true;
					break;
				}
			}
		}
		else
		{
			$bNeedCore = true;
		}

		$ret = '';
		if ($bNeedCore && !self::$arCurrentlyLoadedExt['core'])
		{
			$config = self::GetCoreConfig();

			$ret .= self::_loadCSS($config['css'], $bReturn);
			$ret .= self::_loadJS($config['js'], $bReturn);
			$ret .= self::_loadLang($config['lang'], $bReturn);

			self::$arCurrentlyLoadedExt['core'] = true;
		}

		if (self::$arCurrentlyLoadedExt['core'])
		{
			foreach (self::$arAutoloadQueue as $extCode => $extParams)
			{
				$ret .= self::_loadExt($extCode, $bReturn);
				unset(self::$arAutoloadQueue[$extCode]);
			}
		}

		for ($i = 0, $len = count($arExt); $i < $len; $i++)
		{
			$ret .= self::_loadExt($arExt[$i], $bReturn);
		}

		if (!defined('PUBLIC_MODE') && defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
			echo $ret;

		return $bReturn ? $ret : true;
	}

	/**
	 * Returns true if Core JS was inited
	 * @return bool
	 */
	public static function IsCoreLoaded()
	{
		return isset(self::$arCurrentlyLoadedExt["core"]);
	}

	/**
	 * Returns true if JS extension was loaded.
	 * @param string $code Code of JS extension.
	 * @return bool
	 */
	public static function isExtensionLoaded($code)
	{
		return isset(self::$arCurrentlyLoadedExt[$code]);
	}

	public static function GetCoreMessagesScript($compositeMode = false)
	{
		if (!self::IsCoreLoaded())
		{
			return "";
		}

		return self::_loadLang("", true, self::GetCoreMessages($compositeMode));
	}

	public static function GetCoreMessages($compositeMode = false)
	{
		$arMessages = array(
			"LANGUAGE_ID" => LANGUAGE_ID,
			"FORMAT_DATE" => FORMAT_DATE,
			"FORMAT_DATETIME" => FORMAT_DATETIME,
			"COOKIE_PREFIX" => COption::GetOptionString("main", "cookie_name", "BITRIX_SM"),
			"SERVER_TZ_OFFSET" => date("Z"),
		);

		if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
		{
			$arMessages["SITE_ID"] = SITE_ID;
			$arMessages["SITE_DIR"] = SITE_DIR;
		}

		if (!$compositeMode)
		{
			global $USER;
			$userId = "";
			$autoTimeZone = "N";
			if (is_object($USER))
			{
				$autoTimeZone = trim($USER->GetParam("AUTO_TIME_ZONE"));
				if ($USER->GetID() > 0)
				{
					$userId = $USER->GetID();
				}
			}

			$arMessages["USER_ID"] = $userId;
			$arMessages["SERVER_TIME"] = time();
			$arMessages["USER_TZ_OFFSET"] = CTimeZone::GetOffset();
			$arMessages["USER_TZ_AUTO"] = $autoTimeZone == "N" ? "N": "Y";
			$arMessages["bitrix_sessid"] = bitrix_sessid();
		}

		return $arMessages;
	}

	public static function GetHTML($arExt)
	{
		$tmp = self::$arCurrentlyLoadedExt;
		self::$arCurrentlyLoadedExt = array();
		$res = self::Init($arExt, true);
		self::$arCurrentlyLoadedExt = $tmp;
		return $res;
	}

	/**
	 *
	 * When all of scripts are moved to the body, we need this code to add special classes (bx-chrome, bx-ie...) to <html> tag.
	 * @return string
	 */
	public static function GetInlineCoreJs()
	{
		$js = <<<JS
		(function(w, d, n) {

			var cl = "bx-core";
			var ht = d.documentElement;
			var htc = ht ? ht.className : undefined;
			if (htc === undefined || htc.indexOf(cl) !== -1)
			{
				return;
			}

			var ua = n.userAgent;
			if (/(iPad;)|(iPhone;)/i.test(ua))
			{
				cl += " bx-ios";
			}
			else if (/Android/i.test(ua))
			{
				cl += " bx-android";
			}

			cl += (/(ipad|iphone|android|mobile|touch)/i.test(ua) ? " bx-touch" : " bx-no-touch");

			cl += w.devicePixelRatio && w.devicePixelRatio >= 2
				? " bx-retina"
				: " bx-no-retina";

			var ieVersion = -1;
			if (/AppleWebKit/.test(ua))
			{
				cl += " bx-chrome";
			}
			else if ((ieVersion = getIeVersion()) > 0)
			{
				cl += " bx-ie bx-ie" + ieVersion;
				if (ieVersion > 7 && ieVersion < 10 && !isDoctype())
				{
					cl += " bx-quirks";
				}
			}
			else if (/Opera/.test(ua))
			{
				cl += " bx-opera";
			}
			else if (/Gecko/.test(ua))
			{
				cl += " bx-firefox";
			}

			if (/Macintosh/i.test(ua))
			{
				cl += " bx-mac";
			}

			ht.className = htc ? htc + " " + cl : cl;

			function isDoctype()
			{
				if (d.compatMode)
				{
					return d.compatMode == "CSS1Compat";
				}

				return d.documentElement && d.documentElement.clientHeight;
			}

			function getIeVersion()
			{
				if (/Opera/i.test(ua) || /Webkit/i.test(ua) || /Firefox/i.test(ua) || /Chrome/i.test(ua))
				{
					return -1;
				}

				var rv = -1;
				if (!!(w.MSStream) && !(w.ActiveXObject) && ("ActiveXObject" in w))
				{
					rv = 11;
				}
				else if (!!d.documentMode && d.documentMode >= 10)
				{
					rv = 10;
				}
				else if (!!d.documentMode && d.documentMode >= 9)
				{
					rv = 9;
				}
				else if (d.attachEvent && !/Opera/.test(ua))
				{
					rv = 8;
				}

				if (rv == -1 || rv == 8)
				{
					var re;
					if (n.appName == "Microsoft Internet Explorer")
					{
						re = new RegExp("MSIE ([0-9]+[\.0-9]*)");
						if (re.exec(ua) != null)
						{
							rv = parseFloat(RegExp.$1);
						}
					}
					else if (n.appName == "Netscape")
					{
						rv = 11;
						re = new RegExp("Trident/.*rv:([0-9]+[\.0-9]*)");
						if (re.exec(ua) != null)
						{
							rv = parseFloat(RegExp.$1);
						}
					}
				}

				return rv;
			}

		})(window, document, navigator);
JS;
		return '<script type="text/javascript" data-skip-moving="true">'.str_replace(array("\n", "\t"), "", $js)."</script>";
	}

	public static function GetScriptsList()
	{
		$scriptsList = array();
		foreach(self::$arCurrentlyLoadedExt as $ext=>$q)
		{
			if($ext!='core' && isset(self::$arRegisteredExt[$ext]['js']))
			{
				if(is_array(self::$arRegisteredExt[$ext]['js']))
				{
					$scriptsList = array_merge($scriptsList, self::$arRegisteredExt[$ext]['js']);
				}
				else
				{
					$scriptsList[] = self::$arRegisteredExt[$ext]['js'];
				}
			}
		}
		return $scriptsList;
	}

	public static function GetCoreConfig()
	{
		return Array(
			'css' => '/bitrix/js/main/core/css/core.css',
			'js' => '/bitrix/js/main/core/core.js',
			'lang' => BX_ROOT.'/modules/main/js_core.php',
		);
	}

	private static function _loadExt($ext, $bReturn)
	{
		$ret = '';

		if (preg_match("/^((?P<MODULE_ID>[\w\.]+):)?(?P<EXT_NAME>[\w\.\-]+)$/", $ext, $matches))
		{
			if (strlen($matches['MODULE_ID']) > 0 && $matches['MODULE_ID'] !== 'main')
			{
				\Bitrix\Main\Loader::includeModule($matches['MODULE_ID']);
			}
			$ext = $matches['EXT_NAME'];
		}
		else
		{
			$ext = preg_replace('/[^a-z0-9_\.\-]/i', '', $ext);
		}

		if (!self::IsExtRegistered($ext))
		{
			$success = Extension::register($ext);
			if (!$success)
			{
				return "";
			}
		}

		if (isset(self::$arCurrentlyLoadedExt[$ext]) && self::$arCurrentlyLoadedExt[$ext])
		{
			return "";
		}

		if(isset(self::$arRegisteredExt[$ext]['oninit']) && is_callable(self::$arRegisteredExt[$ext]['oninit']))
		{
			$callbackResult = call_user_func_array(
				self::$arRegisteredExt[$ext]['oninit'],
				array(self::$arRegisteredExt[$ext])
			);

			if(is_array($callbackResult))
			{
				foreach($callbackResult as $key => $value)
				{
					if(!is_array($value))
					{
						$value = array($value);
					}

					if(!isset(self::$arRegisteredExt[$ext][$key]))
					{
						self::$arRegisteredExt[$ext][$key] = array();
					}
					elseif(!is_array(self::$arRegisteredExt[$ext][$key]))
					{
						self::$arRegisteredExt[$ext][$key] = array(self::$arRegisteredExt[$ext][$key]);
					}

					self::$arRegisteredExt[$ext][$key] = array_merge(self::$arRegisteredExt[$ext][$key], $value);
				}
			}

			unset(self::$arRegisteredExt[$ext]['oninit']);
		}

		self::$arCurrentlyLoadedExt[$ext] = true;

		if (isset(self::$arRegisteredExt[$ext]['rel']) && is_array(self::$arRegisteredExt[$ext]['rel']))
		{
			foreach (self::$arRegisteredExt[$ext]['rel'] as $rel_ext)
			{
				$ret .= self::_loadExt($rel_ext, $bReturn);
			}
		}

		if (!empty(self::$arRegisteredExt[$ext]['css']))
		{
			if (!empty(self::$arRegisteredExt[$ext]['bundle_css']))
			{
				self::registerCssBundle(
					self::$arRegisteredExt[$ext]['bundle_css'],
					self::$arRegisteredExt[$ext]['css']
				);
			}

			$ret .= self::_loadCSS(self::$arRegisteredExt[$ext]['css'], $bReturn);
		}

		if (isset(self::$arRegisteredExt[$ext]['js']))
		{
			if (!empty(self::$arRegisteredExt[$ext]['bundle_js']))
			{
				self::registerJsBundle(
					self::$arRegisteredExt[$ext]['bundle_js'],
					self::$arRegisteredExt[$ext]['js']
				);
			}

			$ret .= self::_loadJS(self::$arRegisteredExt[$ext]['js'], $bReturn);
		}

		if (isset(self::$arRegisteredExt[$ext]['lang']) || isset(self::$arRegisteredExt[$ext]['lang_additional']))
		{
			$ret .= self::_loadLang(
				isset(self::$arRegisteredExt[$ext]['lang']) ? self::$arRegisteredExt[$ext]['lang'] : null,
				$bReturn,
				!empty(self::$arRegisteredExt[$ext]['lang_additional'])? self::$arRegisteredExt[$ext]['lang_additional']: false
			);
		}

		return $ret;
	}

	public static function ShowTimer($params)
	{
		$id = $params['id'] ? $params['id'] : 'timer_'.RandString(7);

		self::Init(array('timer'));

		$arJSParams = array();
		if ($params['from'])
			$arJSParams['from'] = MakeTimeStamp($params['from']).'000';
		elseif ($params['to'])
			$arJSParams['to'] = MakeTimeStamp($params['to']).'000';

		if ($params['accuracy'])
			$arJSParams['accuracy'] = intval($params['accuracy']).'000';

		$res = '<span id="'.htmlspecialcharsbx($id).'"></span>';
		$res .= '<script type="text/javascript">BX.timer(\''.CUtil::JSEscape($id).'\', '.CUtil::PhpToJSObject($arJSParams).')</script>';

		return $res;
	}

	public static function IsExtRegistered($ext)
	{
		$ext = preg_replace('/[^a-z0-9_\.\-]/i', '', $ext);
		return isset(self::$arRegisteredExt[$ext]) && is_array(self::$arRegisteredExt[$ext]);
	}

	public static function getExtInfo($ext)
	{
		return self::$arRegisteredExt[$ext];
	}

	public static function getAutoloadExtInfo()
	{
		$result = Array();

		foreach(self::$arRegisteredExt as $ext => $info)
		{
			if ($info['autoload'])
			{
				$result[$ext] = $info;
			}
		}

		return $result;
	}

	private static function _RegisterStandardExt()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/jscore.php');
	}

	private static function _loadJS($js, $bReturn)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$js = (is_array($js) ? $js : array($js));
		if ($bReturn)
		{
			$res = '';
			foreach ($js as $val)
			{
				$res .= '<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL($val).'"></script>'."\r\n";
			}
			return $res;
		}
		else
		{
			foreach ($js as $val)
			{
				$APPLICATION->AddHeadScript($val);
			}
		}
		return '';
	}

	private static function _loadLang($lang, $bReturn, $arAdditionalMess = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$jsMsg = '';

		if (is_string($lang))
		{
			$messLang = \Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$lang);

			if (!empty($messLang))
			{
				$jsMsg = '(window.BX||top.BX).message('.CUtil::PhpToJSObject($messLang, false).');';
			}
		}

		if (is_array($arAdditionalMess))
		{
			$jsMsg = '(window.BX||top.BX).message('.CUtil::PhpToJSObject($arAdditionalMess, false).');'.$jsMsg;
		}

		if ($jsMsg !== '')
		{
			$jsMsg = '<script type="text/javascript">'.$jsMsg.'</script>';
			if ($bReturn)
			{
				return $jsMsg."\r\n";
			}
			else
			{
				$APPLICATION->AddLangJS($jsMsg);
			}
		}

		return $jsMsg;
	}

	private static function _loadCSS($css, $bReturn)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (is_array($css))
		{
			$ret = '';
			foreach ($css as $css_file)
				$ret .= self::_loadCSS($css_file, $bReturn);
			return $ret;
		}

		$css_filename = $_SERVER['DOCUMENT_ROOT'].$css;

		if (!file_exists($css_filename))
			return '';

		if ($bReturn)
			return '<link href="'.CUtil::GetAdditionalFileURL($css).'" type="text/css" rel="stylesheet" />'."\r\n";
		else
			$APPLICATION->SetAdditionalCSS($css);

		return '';
	}

	private static function registerJsBundle($bundleName, $files)
	{
		$files = is_array($files) ? $files : array($files);

		\Bitrix\Main\Page\Asset::getInstance()->addJsKernelInfo($bundleName, $files);
	}

	private static function registerCssBundle($bundleName, $files)
	{
		$files = is_array($files) ? $files : array($files);

		\Bitrix\Main\Page\Asset::getInstance()->addCssKernelInfo($bundleName, $files);
	}
}

class CUtil
{
	protected static $alreadyDecodedRequest = false;

	public static function addslashes($s)
	{
		static $aSearch = array("\\", "\"", "'");
		static $aReplace = array("\\\\", '\\"', "\\'");
		return str_replace($aSearch, $aReplace, $s);
	}

	public static function closetags($html)
	{
		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$openedtags = $result[1];

		preg_match_all("#</([a-z0-9]+)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);

		if(count($closedtags) == $len_opened)
			return $html;

		$openedtags = array_reverse($openedtags);

		for($i = 0; $i < $len_opened; $i++)
		{
			if (!in_array($openedtags[$i], $closedtags))
				$html .= '</'.$openedtags[$i].'>';
			else
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}

		return $html;
	}

	public static function JSEscape($s)
	{
		static $aSearch = array("\xe2\x80\xa9", "\\", "'", "\"", "\r\n", "\r", "\n", "\xe2\x80\xa8", "*/", "</");
		static $aReplace = array(" ", "\\\\", "\\'", '\\"', "\n", "\n", "\\n", "\\n", "*\\/", "<\\/");
		$val = str_replace($aSearch, $aReplace, $s);
		return $val;
	}

	public static function JSUrlEscape($s)
	{
		static $aSearch = array("%27", "%5C", "%0A", "%0D", "%", "&#039;", "&#39;", "&#x27;", "&apos;");
		static $aReplace = array("\\'", "\\\\", "\\n", "\\r", "%25", "\\'", "\\'", "\\'", "\\'");
		return str_replace($aSearch, $aReplace, $s);
	}

	public static function PhpToJSObject($arData, $bWS = false, $bSkipTilda = false, $bExtType = false)
	{
		static $use_bx_encode = null;
		if (!isset($use_bx_encode))
			$use_bx_encode = function_exists('bx_js_encode');
		if ($use_bx_encode)
			return bx_js_encode($arData, $bWS, $bSkipTilda, $bExtType);

		switch(gettype($arData))
		{
		case "string":
			if(preg_match("#['\"\\n\\r<\\\\\x80]#", $arData))
				return "'".CUtil::JSEscape($arData)."'";
			else
				return "'".$arData."'";
		case "array":
			$i = -1;
			$j = -1;
			foreach($arData as $j => $temp)
			{
				$i++;
				if ($j !== $i)
					break;
			}

			if($j === $i)
			{
				$res = '[';
				$first = true;
				foreach($arData as $key => $value)
				{
					if($first)
						$first = false;
					else
						$res .= ',';

					switch(gettype($value))
					{
					case "string":
						if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
							$res .= "'".CUtil::JSEscape($value)."'";
						else
							$res .= "'".$value."'";
						break;
					case "array":
						$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
						break;
					case "boolean":
						if($value === true)
							$res .= 'true';
						else
							$res .= 'false';
						break;
					case "integer":
						if ($bExtType)
							$res .= $value;
						else
							$res .= "'".$value."'";
						break;
					case "double":
						if ($bExtType)
							$res .= is_finite($value) ? $value : "Infinity";
						else
							$res .= "'".$value."'";
						break;
					default:
						if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
							$res .= "'".CUtil::JSEscape($value)."'";
						else
							$res .= "'".$value."'";
						break;
					}
				}
				$res .= ']';
				return $res;
			}

			$sWS = ','.($bWS ? "\n" : '');
			$res = ($bWS ? "\n" : '').'{';
			$first = true;
			foreach($arData as $key => $value)
			{
				if ($bSkipTilda && substr($key, 0, 1) == '~')
					continue;

				if($first)
					$first = false;
				else
					$res .= $sWS;

				if(preg_match("#['\"\\n\\r<\\\\\x80]#", $key))
					$res .= "'".CUtil::JSEscape($key)."':";
				else
					$res .= "'".$key."':";

				switch(gettype($value))
				{
				case "string":
					if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
						$res .= "'".CUtil::JSEscape($value)."'";
					else
						$res .= "'".$value."'";
					break;
				case "array":
					$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
					break;
				case "boolean":
					if($value === true)
						$res .= 'true';
					else
						$res .= 'false';
					break;
				case "integer":
					if ($bExtType)
						$res .= $value;
					else
						$res .= "'".$value."'";
					break;
				case "double":
					if ($bExtType)
						$res .= is_finite($value) ? $value : "Infinity";
					else
						$res .= "'".$value."'";
					break;
				default:
					if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
						$res .= "'".CUtil::JSEscape($value)."'";
					else
						$res .= "'".$value."'";
					break;
				}
			}
			$res .= ($bWS ? "\n" : '').'}';
			return $res;
		case "boolean":
			if($arData === true)
				return 'true';
			else
				return 'false';
		case "integer":
			if ($bExtType)
				return $arData;
			else
				return "'".$arData."'";
		case "double":
			if ($bExtType)
				return is_finite($arData) ? $arData : "Infinity";
			else
				return "'".$arData."'";
		default:
			if(preg_match("#['\"\\n\\r<\\\\\x80]#", $arData))
				return "'".CUtil::JSEscape($arData)."'";
			else
				return "'".$arData."'";
		}
	}

	//$data must be in LANG_CHARSET encoding
	public static function JsObjectToPhp($data, $bSkipNative=false)
	{
		$arResult = array();

		$bSkipNative |= !function_exists('json_decode');

		if(!$bSkipNative)
		{
			// php > 5.2.0 + php_json
			/** @global CMain $APPLICATION */
			global $APPLICATION;

			$bUtf = defined("BX_UTF");
			$dataUTF = ($bUtf? $data : $APPLICATION->ConvertCharset($data, LANG_CHARSET, 'UTF-8'));

			// json_decode recognize only UTF strings
			// the name and value must be enclosed in double quotes
			// single quotes are not valid
			$arResult = json_decode($dataUTF, true);

			if($arResult === null)
				$bSkipNative = true;
			elseif(!$bUtf)
				$arResult = $APPLICATION->ConvertCharsetArray($arResult, 'UTF-8', LANG_CHARSET);
		}

		if ($bSkipNative)
		{
			$data = preg_replace('/[\s]*([{}\[\]\"])[\s]*/', '\1', $data);
			$data = trim($data);

			if (substr($data, 0, 1) == '{') // object
			{
				$arResult = array();

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = array();
				$bStringStarted = false;
				$prev_symbol = "";

				$string_delimiter = '';
				for ($i = 1, $len = strlen($data); $i < $len; $i++)
				{
					$cur_symbol = substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
								$string_delimiter = $cur_symbol;
							else
								$string_delimiter = '';

						}
					}

					elseif ($cur_symbol == '{' || $cur_symbol == '[')
						$depth++;
					elseif ($cur_symbol == ']')
						$depth--;
					elseif ($cur_symbol == '}')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
					return false;

				$token = substr($data, 1, $end_pos-1);

				$arTokens = array();
				if (count($arCommaPos) > 0)
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arTokens[] = substr($token, $prev_index, $pos - $prev_index - 1);
						$prev_index = $pos;
					}
					$arTokens[] = substr($token, $prev_index);
				}
				else
				{
					$arTokens[] = $token;
				}

				foreach ($arTokens as $token)
				{
					$arTokenData = explode(":", $token, 2);

					$q = substr($arTokenData[0], 0, 1);
					if ($q == '"' || $q == '"')
						$arTokenData[0] = substr($arTokenData[0], 1, -1);
					$arResult[CUtil::JsObjectToPhp($arTokenData[0], true)] = CUtil::JsObjectToPhp($arTokenData[1], true);
				}
			}
			elseif (substr($data, 0, 1) == '[') // array
			{
				$arResult = array();

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = array();
				$bStringStarted = false;
				$prev_symbol = "";
				$string_delimiter = "";

				for ($i = 1, $len = strlen($data); $i < $len; $i++)
				{
					$cur_symbol = substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
								$string_delimiter = $cur_symbol;
							else
								$string_delimiter = '';

						}
					}
					elseif ($cur_symbol == '{' || $cur_symbol == '[')
						$depth++;
					elseif ($cur_symbol == '}')
						$depth--;
					elseif ($cur_symbol == ']')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
					return false;

				$token = substr($data, 1, $end_pos-1);

				if (count($arCommaPos) > 0)
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arResult[] = CUtil::JsObjectToPhp(substr($token, $prev_index, $pos - $prev_index - 1), true);
						$prev_index = $pos;
					}
					$r = CUtil::JsObjectToPhp(substr($token, $prev_index), true);
					if (isset($r))
						$arResult[] = $r;
				}
				else
				{
					$r = CUtil::JsObjectToPhp($token, true);
					if (isset($r))
						$arResult[] = $r;
				}
			}
			elseif ($data === "")
			{
				return null;
			}
			else // scalar
			{
				$q = substr($data, 0, 1);
				if ($q == '"' || $q == "'")
					$data = substr($data, 1, -1);

				//\u0412\u0430\u0434\u0438\u043c
				if(strpos($data, '\u') !== false)
					$data = preg_replace_callback("/\\\u([0-9A-F]{2})([0-9A-F]{2})/i", array('CUtil', 'DecodeUtf16'), $data);

				$arResult = $data;
			}
		}

		return $arResult;
	}

	public static function DecodeUtf16($ch)
	{
		$res = chr(hexdec($ch[2])).chr(hexdec($ch[1]));
		return \Bitrix\Main\Text\Encoding::convertEncoding($res, "UTF-16", LANG_CHARSET);
	}

	public static function JSPostUnescape()
	{
	    if(!static::$alreadyDecodedRequest)
	    {
		    static::$alreadyDecodedRequest = true;
		    CUtil::decodeURIComponent($_POST);
		    CUtil::decodeURIComponent($_REQUEST);
	    }
	}

	public static function decodeURIComponent(&$item)
	{
		if(defined("BX_UTF"))
		{
			return;
		}
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(is_array($item))
		{
			array_walk($item, array('CUtil', 'decodeURIComponent'));
		}
		else
		{
			$item = $APPLICATION->ConvertCharset($item, "UTF-8", LANG_CHARSET);
		}
	}

	public static function DetectUTF8($string)
	{
		//http://mail.nl.linux.org/linux-utf8/1999-09/msg00110.html

		if(preg_match_all("/(?:%)([0-9A-F]{2})/i", $string, $match))
		{
			$string = pack("H*", strtr(implode('', $match[1]), 'abcdef', 'ABCDEF'));
		}

		//valid UTF-8 octet sequences
		//0xxxxxxx
		//110xxxxx 10xxxxxx
		//1110xxxx 10xxxxxx 10xxxxxx
		//11110xxx 10xxxxxx 10xxxxxx 10xxxxxx

		$prevBits8and7 = 0;
		$is_utf = 0;
		foreach(unpack("C*", $string) as $byte)
		{
			$hiBits8and7 = $byte & 0xC0;
			if ($hiBits8and7 == 0x80)
			{
				if ($prevBits8and7 == 0xC0)
					$is_utf++;
				elseif (($prevBits8and7 & 0x80) == 0x00)
					$is_utf--;
			}
			elseif ($prevBits8and7 == 0xC0)
			{
					$is_utf--;
			}
			$prevBits8and7 = $hiBits8and7;
		}
		return ($is_utf > 0);
	}

	public static function ConvertToLangCharset($string)
	{
		$bUTF = CUtil::DetectUTF8($string);

		$fromCP = $toCP = false;
		if(defined("BX_UTF") && !$bUTF)
		{
			$fromCP = (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "Windows-1251");
			$toCP = "UTF-8";
		}
		elseif(!defined("BX_UTF") && $bUTF)
		{
			$fromCP = "UTF-8";
			$toCP = (defined("LANG_CHARSET")? LANG_CHARSET : (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "Windows-1251"));
		}

		if($fromCP !== false)
			$string = \Bitrix\Main\Text\Encoding::convertEncoding($string, $fromCP, $toCP);

		return $string;
	}

	public static function GetAdditionalFileURL($file, $bSkipCheck=false)
	{
		$filePath = $_SERVER['DOCUMENT_ROOT'].$file;
		if($bSkipCheck || file_exists($filePath))
			return $file.'?'.filemtime($filePath).filesize($filePath);
		else
			return $file;
	}

	public static function InitJSCore($arExt = array(), $bReturn = false)
	{
		
		return CJSCore::Init($arExt, $bReturn);
	}

	public static function GetPopupSize($resize_id, $arDefaults = array())
	{
		if ($resize_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'size_'.$resize_id,
				array(
					'width' => isset($arDefaults['width'])? $arDefaults['width']: null,
					'height' => isset($arDefaults['height'])? $arDefaults['height']: null,
				)
			);
		}
		else
			return false;
	}

	public static function GetPopupOptions($wnd_id)
	{
		if ($wnd_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'options_'.$wnd_id
			);
		}
		else
		{
			return false;
		}
	}

	public static function SetPopupOptions($wnd_id, $arOptions)
	{
		if ($wnd_id)
		{
			CUserOptions::SetOption(
				'BX.WindowManager.9.5',
				'options_'.$wnd_id,
				$arOptions
			);
		}
	}

	public static function translit($str, $lang, $params = array())
	{
		static $search = array();

		if(!isset($search[$lang]))
		{
			$mess = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/js_core_translit.php", $lang, true);
			$trans_from = explode(",", $mess["TRANS_FROM"]);
			$trans_to = explode(",", $mess["TRANS_TO"]);
			foreach($trans_from as $i => $from)
				$search[$lang][$from] = $trans_to[$i];
		}

		$defaultParams = array(
			"max_len" => 100,
			"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
			"safe_chars" => '',
		);
		foreach($defaultParams as $key => $value)
			if(!array_key_exists($key, $params))
				$params[$key] = $value;

		$len = strlen($str);
		$str_new = '';
		$last_chr_new = '';

		for($i = 0; $i < $len; $i++)
		{
			$chr = substr($str, $i, 1);

			if(preg_match("/[a-zA-Z0-9]/".BX_UTF_PCRE_MODIFIER, $chr) || strpos($params["safe_chars"], $chr)!==false)
			{
				$chr_new = $chr;
			}
			elseif(preg_match("/\\s/".BX_UTF_PCRE_MODIFIER, $chr))
			{
				if (
					!$params["delete_repeat_replace"]
					||
					($i > 0 && $last_chr_new != $params["replace_space"])
				)
					$chr_new = $params["replace_space"];
				else
					$chr_new = '';
			}
			else
			{
				if(array_key_exists($chr, $search[$lang]))
				{
					$chr_new = $search[$lang][$chr];
				}
				else
				{
					if (
						!$params["delete_repeat_replace"]
						||
						($i > 0 && $i != $len-1 && $last_chr_new != $params["replace_other"])
					)
						$chr_new = $params["replace_other"];
					else
						$chr_new = '';
				}
			}

			if(strlen($chr_new))
			{
				if($params["change_case"] == "L" || $params["change_case"] == "l")
					$chr_new = ToLower($chr_new);
				elseif($params["change_case"] == "U" || $params["change_case"] == "u")
					$chr_new = ToUpper($chr_new);

				$str_new .= $chr_new;
				$last_chr_new = $chr_new;
			}

			if (strlen($str_new) >= $params["max_len"])
				break;
		}

		return $str_new;
	}

	public static function BinStrlen($buf)
	{
		return (function_exists('mb_strlen')? mb_strlen($buf, 'latin1') : strlen($buf));
	}

	public static function BinSubstr($buf, $start)
	{
		$length = (func_num_args() > 2? func_get_arg(2) : self::BinStrlen($buf));
		return (function_exists('mb_substr')? mb_substr($buf, $start, $length, 'latin1') : substr($buf, $start, $length));
	}

	public static function BinStrpos($haystack, $needle, $offset = 0)
	{
		if (defined("BX_UTF"))
		{
			if (function_exists('mb_orig_strpos'))
			{
				return mb_orig_strpos($haystack, $needle, $offset);
			}
			return mb_strpos($haystack, $needle, $offset, 'latin1');
		}
		return strpos($haystack, $needle, $offset);
	}

	/**
	* Convert shorthand notation to integer equivalent
	* @param string $str
	* @return int
	*
	*/
	public static function Unformat($str)
	{
		$str = strtolower($str);
		$res = intval($str);
		$suffix = substr($str, -1);
		if($suffix == "k")
			$res *= 1024;
		elseif($suffix == "m")
			$res *= 1048576;
		elseif($suffix == "g")
			$res *= 1048576*1024;
		elseif($suffix == "b")
			$res = self::Unformat(substr($str,0,-1));
		return $res;
	}

	/**
	 * Adjust php pcre.backtrack_limit
	 * @param int $val
	 * @return void
	 *
	 */
	public static function AdjustPcreBacktrackLimit($val)
	{
		$val = intval($val);
		if($val <=0 )
			return;

		$pcreBacktrackLimit = self::Unformat(ini_get("pcre.backtrack_limit"));
		if($pcreBacktrackLimit < $val)
			@ini_set("pcre.backtrack_limit", $val);
	}
}

class CHTTP
{
	var $url = '';
	var $status = 0;
	var $result = '';
	var $fp = null;
	var $headers = array();
	var $cookies = array();
	var $http_timeout = 30;
	var $user_agent;
	var $follow_redirect = false;
	var $errno;
	var $errstr;
	var $additional_headers = array();

	private $redirectMax = 5;
	private $redirectsMade = 0;
	private static $lastSetStatus = "";

	public function __construct()
	{
		$defaultOptions = \Bitrix\Main\Config\Configuration::getValue("http_client_options");
		if(isset($defaultOptions["socketTimeout"]))
		{
			$this->http_timeout = intval($defaultOptions["socketTimeout"]);
		}

		$this->user_agent = 'BitrixSM ' . __CLASS__ . ' class';
	}

	public static function URN2URI($urn, $server_name = '')
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(preg_match("/^[a-z]+:\\/\\//", $urn))
		{
			$uri = $urn;
		}
		else
		{
			if($APPLICATION->IsHTTPS())
				$proto = "https://";
			else
				$proto = "http://";

			if($server_name <> '')
				$server_name = preg_replace("/:(443|80)$/", "", $server_name);
			else
				$server_name = preg_replace("/:(443|80)$/", "", $_SERVER["HTTP_HOST"]);

			$uri = $proto.$server_name.$urn;
		}
		return $uri;
	}

	public function Download($url, $file)
	{
		if (is_resource($file))
		{
			$this->fp = $file;
		}
		else
		{
			CheckDirPath($file);
			$this->fp = fopen($file, "wb");
		}

		if(is_resource($this->fp))
		{
			$res = $this->HTTPQuery('GET', $url);

			if (!is_resource($file))
			{
				fclose($this->fp);
				unset($this->fp);
			}

			return $res && ($this->status == 200);
		}
		return false;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Get($url)
	{
		if ($this->HTTPQuery('GET', $url))
		{
			return $this->result;
		}
		return false;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Post($url, $arPostData)
	{
		$postdata = CHTTP::PrepareData($arPostData);

		if($this->HTTPQuery('POST', $url, $postdata))
		{
			return $this->result;
		}
		return false;
	}

	public static function PrepareData($arPostData, $prefix = '')
	{
		$str = '';

		if(!is_array($arPostData))
		{
			$str = $arPostData;
		}
		else
		{
			foreach ($arPostData as $key => $value)
			{
				$name = $prefix == "" ? urlencode($key) : $prefix."[".urlencode($key)."]";

				if(is_array($value))
				{
					$str .= CHTTP::PrepareData($value, $name);
				}
				else
				{
					$str .= '&'.$name.'='.urlencode($value);
				}
			}
		}

		if($prefix == '' && substr($str, 0, 1) == '&')
		{
			$str = substr($str, 1);
		}

		return $str;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function HTTPQuery($method, $url, $postdata = '')
	{
		if(is_resource($this->fp))
			$file_pos = ftell($this->fp);

		$this->redirectsMade = 0;

		while (true)
		{
			$this->url = $url;
			$arUrl = $this->ParseURL($url);
			if (!$this->Query($method, $arUrl['host'], $arUrl['port'], $arUrl['path_query'], $postdata, $arUrl['proto']))
			{
				return false;
			}

			if(
				$this->follow_redirect
				&& isset($this->headers['Location'])
				&& strlen($this->headers['Location']) > 0
			)
			{
				$url = $this->headers['Location'];
				if($this->redirectsMade < $this->redirectMax)
				{
					//When writing to file we have to discard
					//redirect body
					if(is_resource($this->fp))
					{
						/** @noinspection PhpUndefinedVariableInspection */
						ftruncate($this->fp, $file_pos);
						fseek($this->fp, $file_pos, SEEK_SET);
					}
					$this->redirectsMade++;
					continue;
				}
				else
				{
					trigger_error("Maximum number of redirects (".$this->redirectMax.") has been reached at URL ".$url, E_USER_WARNING);
					return false;
				}
			}
			else
			{
				break;
			}
		}
		return true;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public function Query($method, $host, $port, $path, $postdata = false, $proto = '', $post_content_type = 'N', $dont_wait_answer = false)
	{
		$this->status = 0;
		$this->result = '';
		$this->headers = array();
		$this->cookies = array();
		$fp = fsockopen($proto.$host, $port, $this->errno, $this->errstr, $this->http_timeout);
		if ($fp)
		{
			$strRequest = "$method $path HTTP/1.0\r\n";
			$strRequest .= "Connection: close\r\n";
			$strRequest .= "User-Agent: {$this->user_agent}\r\n";
			$strRequest .= "Accept: */*\r\n";
			$strRequest .= "Host: $host\r\n";
			$strRequest .= "Accept-Language: en\r\n";

			foreach ($this->additional_headers as $key => $value)
				$strRequest .= $key.": ".$value."\r\n";

			if ($method == 'POST' || $method == 'PUT')
			{
				if ('N' !== $post_content_type)
					$strRequest .= $post_content_type == '' ? '' : "Content-type: ".$post_content_type."\r\n";
				else
					$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";

				if(!array_key_exists("Content-Length", $this->additional_headers))
					$strRequest.= "Content-Length: ".CUtil::BinStrlen($postdata) . "\r\n";
			}
			$strRequest .= "\r\n";
			fwrite($fp, $strRequest);

			if ($method == 'POST' || $method == 'PUT')
			{
				if(is_resource($postdata))
				{
					while(!feof($postdata))
						fwrite($fp, fread($postdata, 1024*1024));
				}
				else
				{
					fwrite($fp, $postdata);
				}
			}

			if ($dont_wait_answer)
			{
				fclose($fp);
				return true;
			}

			$headers = "";
			while(!feof($fp))
			{
				$line = fgets($fp, 4096);
				if($line == "\r\n" || $line === false)
				{
					//$line = fgets($fp, 4096);
					break;
				}
				$headers .= $line;
			}
			$this->ParseHeaders($headers);

			if(is_resource($this->fp))
			{
				while(!feof($fp))
				{
					$buf = fread($fp, 40960);
					if ($buf === false)
						break;
					fwrite($this->fp, $buf);
					fflush($this->fp);
				}
			}
			else
			{
				$this->result = "";
				while(!feof($fp))
				{
					$buf = fread($fp, 4096);
					if ($buf === false)
						break;
					$this->result .= $buf;
				}
			}

			fclose($fp);

			return true;
		}

		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->ThrowException(
			GetMessage('HTTP_CLIENT_ERROR_CONNECT',
				array(
					'%ERRSTR%' => $this->errstr,
					'%ERRNO%' => $this->errno,
					'%HOST%' => $host,
					'%PORT%' => $port,
				)
			)
		);
		return false;
	}

	public function SetAuthBasic($user, $pass)
	{
		$this->additional_headers['Authorization'] = "Basic ".base64_encode($user.":".$pass);
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\Uri
	 */
	public static function ParseURL($url)
	{
		$arUrl = parse_url($url);

		$arUrl['proto'] = '';
		if (array_key_exists('scheme', $arUrl))
		{
			$arUrl['scheme'] = strtolower($arUrl['scheme']);
		}
		else
		{
			$arUrl['scheme'] = 'http';
		}

		if (!array_key_exists('port', $arUrl))
		{
			if ($arUrl['scheme'] == 'https')
			{
				$arUrl['port'] = 443;
			}
			else
			{
				$arUrl['port'] = 80;
			}
		}

		if ($arUrl['scheme'] == 'https')
		{
			$arUrl['proto'] = 'ssl://';
		}

		$arUrl['path_query'] = array_key_exists('path', $arUrl) ? $arUrl['path'] : '/';
		if (array_key_exists('query', $arUrl) && strlen($arUrl['query']) > 0)
		{
			$arUrl['path_query'] .= '?' . $arUrl['query'];
		}

		return $arUrl;
	}

	public function ParseHeaders($strHeaders)
	{
		$arHeaders = explode("\n", $strHeaders);
		foreach ($arHeaders as $k => $header)
		{
			if ($k == 0)
			{
				if (preg_match(',HTTP\S+ (\d+),', $header, $arFind))
				{
					$this->status = intval($arFind[1]);
				}
			}
			elseif(strpos($header, ':') !== false)
			{
				$arHeader = explode(':', $header, 2);
				if ($arHeader[0] == 'Set-Cookie')
				{
					if (($pos = strpos($arHeader[1], ';')) !== false && $pos > 0)
					{
						$cookie = trim(substr($arHeader[1], 0, $pos));
					}
					else
					{
						$cookie = trim($arHeader[1]);
					}
					$arCookie = explode('=', $cookie, 2);
					$this->cookies[$arCookie[0]] = rawurldecode($arCookie[1]);
				}
				else
				{
					$this->headers[$arHeader[0]] = trim($arHeader[1]);
				}
			}
		}
	}

	public function setFollowRedirect($follow)
	{
		$this->follow_redirect = $follow;
	}

	public function setRedirectMax($n)
	{
		$this->redirectMax = $n;
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public static function sGet($url, $follow_redirect = false) //static get
	{
		$ob = new CHTTP();
		$ob->setFollowRedirect($follow_redirect);
		return $ob->Get($url);
	}

	/**
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 */
	public static function sPost($url, $arPostData, $follow_redirect = false) //static post
	{
		$ob = new CHTTP();
		$ob->setFollowRedirect($follow_redirect);
		return $ob->Post($url, $arPostData);
	}

	public function SetAdditionalHeaders($arHeader=array())
	{
		foreach($arHeader as $name => $value)
		{
			$name = str_replace(array("\r","\n"), "", $name);
			$value = str_replace(array("\r","\n"), "", $value);
			$this->additional_headers[$name] = $value;
		}
	}

	/** Static Get with the ability to add headers and set the http timeout
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 * @static
	 * @param $url
	 * @param array $arHeader
	 * @param int $httpTimeout
	 * @return bool|string
	 */
	public static function sGetHeader($url, $arHeader = array(), $httpTimeout = 0)
	{
		$httpTimeout = intval($httpTimeout);
		$ob = new CHTTP();
		if(!empty($arHeader))
			$ob->SetAdditionalHeaders($arHeader);
		if($httpTimeout > 0)
			$ob->http_timeout = $httpTimeout;

		return $ob->Get($url);
	}

	/** Static Post with the ability to add headers and set the http timeout
	 * @deprecated Use Bitrix\Main\Web\HttpClient
	 * @static
	 * @param $url
	 * @param $arPostData
	 * @param array $arHeader
	 * @param int $http_timeout
	 * @return bool|string
	 */
	public static function sPostHeader($url, $arPostData, $arHeader = array(), $http_timeout = 0)
	{
		$http_timeout = intval($http_timeout);
		$ob = new CHTTP();
		if(!empty($arHeader))
			$ob->SetAdditionalHeaders($arHeader);
		if($http_timeout > 0)
			$ob->http_timeout = $http_timeout;
		return $ob->Post($url, $arPostData);
	}

	public static function SetStatus($status)
	{
		$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
		if($bCgi && (!defined("BX_HTTP_STATUS") || BX_HTTP_STATUS == false))
			header("Status: ".$status);
		else
			header($_SERVER["SERVER_PROTOCOL"]." ".$status);
		self::$lastSetStatus = $status;
	}

	public static function GetLastStatus()
	{
		return self::$lastSetStatus;
	}

	public static function SetAuthHeader($bDigestEnabled=true)
	{
		self::SetStatus('401 Unauthorized');

		if(defined('BX_HTTP_AUTH_REALM'))
			$realm = BX_HTTP_AUTH_REALM;
		else
			$realm = "Bitrix Site Manager";

		header('WWW-Authenticate: Basic realm="'.$realm.'"');

		if($bDigestEnabled !== false && COption::GetOptionString("main", "use_digest_auth", "N") == "Y")
		{
			// On first try we found that we don't know user digest hash. Let ask only Basic auth first.
			if($_SESSION["BX_HTTP_DIGEST_ABSENT"] !== true)
				header('WWW-Authenticate: Digest realm="'.$realm.'", nonce="'.uniqid().'"');
		}
	}

	public static function ParseAuthRequest()
	{
		$sDigest = '';

		if(isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] <> '')
		{
			// Basic Authorization PHP module
			return array("basic"=>array(
				"username"=>CUtil::ConvertToLangCharset($_SERVER['PHP_AUTH_USER']),
				"password"=>CUtil::ConvertToLangCharset($_SERVER['PHP_AUTH_PW']),
			));
		}
		elseif(isset($_SERVER['PHP_AUTH_DIGEST']) && $_SERVER['PHP_AUTH_DIGEST'] <> '')
		{
			// Digest Authorization PHP module
			$sDigest = $_SERVER['PHP_AUTH_DIGEST'];
		}
		else
		{
			if(isset($_SERVER['REDIRECT_REMOTE_USER']) || isset($_SERVER['REMOTE_USER']))
			{
				$res = (isset($_SERVER['REDIRECT_REMOTE_USER'])? $_SERVER['REDIRECT_REMOTE_USER'] : $_SERVER['REMOTE_USER']);
				if($res <> '')
				{
					if(preg_match('/^\x20*Basic\x20+([a-zA-Z0-9+\/=]+)\s*$/D', $res, $matches))
					{
						// Basic Authorization PHP FastCGI (CGI)
						$res = trim($matches[1]);
						$res = base64_decode($res);
						$res = CUtil::ConvertToLangCharset($res);
						list($user, $pass) = explode(':', $res, 2);
						if(strpos($user, $_SERVER['HTTP_HOST']."\\") === 0)
							$user = str_replace($_SERVER['HTTP_HOST']."\\", "", $user);
						elseif(strpos($user, $_SERVER['SERVER_NAME']."\\") === 0)
							$user = str_replace($_SERVER['SERVER_NAME']."\\", "", $user);

						return array("basic"=>array(
							"username"=>$user,
							"password"=>$pass,
						));
					}
					elseif(preg_match('/^\x20*Digest\x20+(.*)$/sD', $res, $matches))
					{
						// Digest Authorization PHP FastCGI (CGI)
						$sDigest = trim($matches[1]);
					}
				}
			}
		}

		if($sDigest <> '' && ($data = self::ParseDigest($sDigest)))
			return array("digest"=>$data);

		return false;
	}

	public static function ParseDigest($sDigest)
	{
		$data = array();
		$needed_parts = array('nonce'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
		$keys = implode('|', array_keys($needed_parts));

		//from php help
		preg_match_all('@('.$keys.')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $sDigest, $matches, PREG_SET_ORDER);

		foreach ($matches as $m)
		{
			$data[$m[1]] = ($m[3]? $m[3] : $m[4]);
			unset($needed_parts[$m[1]]);
		}

		return ($needed_parts? false : $data);
	}

	public static function urlAddParams($url, $add_params, $options = array())
	{
		if(count($add_params))
		{
			$params = array();
			foreach($add_params as $name => $value)
			{
				if($options["skip_empty"] && !strlen($value))
					continue;
				if($options["encode"])
					$params[] = urlencode($name).'='.urlencode($value);
				else
					$params[] = $name.'='.$value;
			}

			if(count($params))
			{
				$p1 = strpos($url, "?");
				if($p1 === false)
					$ch = "?";
				else
					$ch = "&";

				$p2 = strpos($url, "#");
				if($p2===false)
				{
					$url = $url.$ch.implode("&", $params);
				}
				else
				{
					$url = substr($url, 0, $p2).$ch.implode("&", $params).substr($url, $p2);
				}
			}
		}
		return $url;
	}

	public static function urlDeleteParams($url, $delete_params, $options = array())
	{
		$url_parts = explode("?", $url, 2);
		if(count($url_parts) == 2 && strlen($url_parts[1]) > 0)
		{
			if($options["delete_system_params"])
				$delete_params = array_merge($delete_params, \Bitrix\Main\HttpRequest::getSystemParameters());

			$params_pairs = explode("&", $url_parts[1]);
			foreach($params_pairs as $i => $param_pair)
			{
				$name_value_pair = explode("=", $param_pair, 2);
				if(count($name_value_pair) == 2 && in_array($name_value_pair[0], $delete_params))
					unset($params_pairs[$i]);
			}

			if(empty($params_pairs))
				return $url_parts[0];
			else
				return $url_parts[0]."?".implode("&", $params_pairs);
		}

		return $url;
	}

	public static function urnEncode($str, $charset = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$result = '';
		$arParts = preg_split("#(://|:\\d+/|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		if($charset === false)
		{
			foreach($arParts as $i => $part)
			{
				$result .= ($i % 2) ? $part : rawurlencode($part);
			}
		}
		else
		{
			foreach($arParts as $i => $part)
			{
				$result .= ($i % 2)
					? $part
					: rawurlencode($APPLICATION->ConvertCharset($part, LANG_CHARSET, $charset));
			}
		}
		return $result;
	}

	public static function urnDecode($str, $charset = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$result = '';
		$arParts = preg_split("#(://|:\\d+/|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		if($charset === false)
		{
			foreach($arParts as $i => $part)
			{
				$result .= ($i % 2) ? $part : rawurldecode($part);
			}
		}
		else
		{
			foreach($arParts as $i => $part)
			{
				$result .= ($i % 2)
					? $part
					: rawurldecode($APPLICATION->ConvertCharset($part, LANG_CHARSET, $charset));
			}
		}
		return $result;
	}

	// search for /../ and ulrencoded /../
	public static function isPathTraversalUri($uri)
	{
		if (($pos = strpos($uri, "?")) !== false)
			$uri = substr($uri, 0, $pos);

		$uri = trim($uri);
		return preg_match("#(?:/|2f|^|\\\\|5c)(?:(?:%0*(25)*2e)|\\.){2,}(?:/|%0*(25)*2f|\\\\|%0*(25)*5c|$)#i", $uri) ? true : false;
	}
}

function GetMenuTypes($site=false, $default_value=false)
{
	if($default_value === false)
		$default_value = "left=".GetMessage("main_tools_menu_left").",top=".GetMessage("main_tools_menu_top");

	$mt = COption::GetOptionString("fileman", "menutypes", $default_value, $site);
	if (!$mt)
		return Array();

	$armt_ = unserialize(stripslashes($mt));
	$armt = Array();
	if (is_array($armt_))
	{
		foreach($armt_ as $key => $title)
		{
			$key = trim($key);
			if (strlen($key) == 0)
				continue;
			$armt[$key] = trim($title);
		}
		return $armt;
	}

	$armt_ = explode(",", $mt);
	for ($i = 0, $c = count($armt_); $i < $c; $i++)
	{
		$pos = strpos($armt_[$i], '=');
		if ($pos === false)
			continue;
		$key = trim(substr($armt_[$i], 0, $pos));
		if (strlen($key) == 0)
			continue;
		$armt[$key] = trim(substr($armt_[$i], $pos + 1));
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

	if($params["use_php_parser"] && substr($filesrc, 0, 2) == $php_st)
	{
		$phpChunks = PHPParser::getPhpChunks($filesrc);
		if (!empty($phpChunks))
		{
			$prolog = $phpChunks[0];
			$filesrc = substr($filesrc, strlen($prolog));
		}
	}
	elseif(substr($filesrc, 0, 2)==$php_st)
	{
		$fl = strlen($filesrc);
		$p = 2;
		while($p < $fl)
		{
			$ch2 = substr($filesrc, $p, 2);
			$ch1 = substr($ch2, 0, 1);

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
				elseif($php_doubleq && $ch1=='"' && substr($filesrc, $p-1, 1)!='\\')
				{
					$php_doubleq=false;
				}
				elseif(!$php_doubleq)
				{
					if(!$php_singleq && $ch1=="'")
					{
						$php_singleq=true;
					}
					elseif($php_singleq && $ch1=="'" && substr($filesrc, $p-1, 1)!='\\')
					{
						$php_singleq=false;
					}
				}
			}

			$p++;
		}

		$prolog = substr($filesrc, 0, $p);
		$filesrc = substr($filesrc, $p);
	}
	elseif(preg_match("'(.*?<title>.*?</title>)(.*)$'is", $filesrc, $reg))
	{
		$prolog = $reg[1];
		$filesrc= $reg[2];
	}

	$title = PHPParser::getPageTitle($filesrc, $prolog);

	$arPageProps = array();
	if(strlen($prolog))
	{
		if (preg_match_all("'\\\$APPLICATION->SetPageProperty\\(([\"\\'])(.*?)(?<!\\\\)[\"\\'] *, *([\"\\'])(.*?)(?<!\\\\)[\"\\']\\);'i", $prolog, $out))
		{
			foreach ($out[2] as $i => $m1)
			{
				$arPageProps[UnEscapePHPString($m1, $out[1][$i])] = UnEscapePHPString($out[4][$i], $out[3][$i]);
			}
		}
	}

	if(substr($filesrc, -2) == "?".">")
	{
		if (isset($phpChunks) && count($phpChunks) > 1)
		{
			$epilog = $phpChunks[count($phpChunks)-1];
			$filesrc = substr($filesrc, 0, -strlen($epilog));
		}
		else
		{
			$p = strlen($filesrc) - 2;
			$php_start = "<"."?";
			while(($p > 0) && (substr($filesrc, $p, 2) != $php_start))
				$p--;
			$epilog = substr($filesrc, $p);
			$filesrc = substr($filesrc, 0, $p);
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

	// check max depth in PHP 5.3.0 and earlier
	if(!version_compare(phpversion(),"5.3.0",">"))
	{
		$str1 = preg_replace('/[^{}]+/'.BX_UTF_PCRE_MODIFIER, '', $str);
		$cnt = 0;
		for ($i=0,$len=strlen($str1);$i<$len;$i++)
		{
			// we've just cleared all possible utf-symbols, so we can use [] syntax
			if ($str1[$i]=='}')
				$cnt--;
			else
			{
				$cnt++;
				if ($cnt > $max_depth)
					break;
			}
		}

		return $cnt <= $max_depth;
	}
	else
	{
		return true;
	}
}

function NormalizePhone($number, $minLength = 10)
{
	$minLength = intval($minLength);
	if ($minLength <= 0 || strlen($number) < $minLength)
	{
		return false;
	}

	if (strlen($number) >= 10 && substr($number, 0, 2) == '+8')
	{
		$number = '00'.substr($number, 1);
	}

	$number = preg_replace("/[^0-9\#\*,;]/i", "", $number);
	if (strlen($number) >= 10)
	{
		if (substr($number, 0, 2) == '80' || substr($number, 0, 2) == '81' || substr($number, 0, 2) == '82')
		{
		}
		else if (substr($number, 0, 2) == '00')
		{
			$number = substr($number, 2);
		}
		else if (substr($number, 0, 3) == '011')
		{
			$number = substr($number, 3);
		}
		else if (substr($number, 0, 1) == '8')
		{
			$number = '7'.substr($number, 1);
		}
		else if (substr($number, 0, 1) == '0')
		{
			$number = substr($number, 1);
		}
	}

	return $number;
}

function bxmail($to, $subject, $message, $additional_headers="", $additional_parameters="", \Bitrix\Main\Mail\Context $context=null)
{
	if (empty($context))
	{
		$context = new \Bitrix\Main\Mail\Context();
	}

	$event = new \Bitrix\Main\Event(
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

	if(function_exists("custom_mail"))
		return custom_mail($to, $subject, $message, $additional_headers, $additional_parameters, $context);

	if($additional_parameters!="")
		return @mail($to, $subject, $message, $additional_headers, $additional_parameters);

	return @mail($to, $subject, $message, $additional_headers);
}

function bx_accelerator_reset()
{
	if(defined("BX_NO_ACCELERATOR_RESET"))
		return;
	if(function_exists("accelerator_reset"))
		accelerator_reset();
	elseif(function_exists("wincache_refresh_if_changed"))
		wincache_refresh_if_changed();
}

class UpdateTools
{
	public static function CheckUpdates()
	{
		global $USER;

		if(LICENSE_KEY == "DEMO")
			return;

		$days_check = intval(COption::GetOptionString('main', 'update_autocheck'));
		if($days_check > 0)
		{
			CUtil::SetPopupOptions('update_tooltip', array('display'=>'on'));

			$update_res = unserialize(COption::GetOptionString('main', '~update_autocheck_result'));
			if(!is_array($update_res))
				$update_res = array("check_date"=>0, "result"=>false);

			if(time() > $update_res["check_date"]+$days_check*86400)
			{
				if($USER->CanDoOperation('install_updates'))
				{
					require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

					$result = CUpdateClient::IsUpdateAvailable($arModules, $strError);

					$modules = array();
					foreach($arModules as $module)
						$modules[] = $module["@"]["ID"];

					if($strError <> '' && COption::GetOptionString('main', 'update_stop_autocheck', 'N') == 'Y')
						COption::SetOptionString('main', 'update_autocheck', '');

					COption::SetOptionString('main', '~update_autocheck_result', serialize(array(
						"check_date"=>time(),
						"result"=>$result,
						"error"=>$strError,
						"modules"=>$modules,
					)));
				}
			}
		}
	}

	public static function SetUpdateResult()
	{
		COption::SetOptionString('main', '~update_autocheck_result', serialize(array(
			"check_date"=>time(),
			"result"=>false,
			"error"=>"",
			"modules"=>array(),
		)));
	}

	public static function SetUpdateError($strError)
	{
		$update_res = unserialize(COption::GetOptionString('main', '~update_autocheck_result'));
		if(!is_array($update_res))
			$update_res = array("check_date"=>0, "result"=>false);

		if($strError <> '')
			$update_res["result"] = false;
		$update_res["error"] = $strError;

		COption::SetOptionString('main', '~update_autocheck_result', serialize($update_res));
	}

	public static function GetUpdateResult()
	{
		$update_res = false;
		if(intval(COption::GetOptionString('main', 'update_autocheck')) > 0)
			$update_res = unserialize(COption::GetOptionString('main', '~update_autocheck_result'));
		if(!is_array($update_res))
			$update_res = array("result"=>false, "error"=>"", "modules"=>array());

		$update_res['tooltip'] = '';
		if($update_res["result"] == true || $update_res["error"] <> '')
		{
			$updOptions = CUtil::GetPopupOptions('update_tooltip');
			if($updOptions['display'] <> 'off')
			{
				if($update_res["result"] == true)
					$update_res['tooltip'] = GetMessage("top_panel_updates").(($n = count($update_res["modules"])) > 0? GetMessage("top_panel_updates_modules", array("#MODULE_COUNT#"=>$n)) : '');
				elseif($update_res["error"] <> '')
					$update_res['tooltip'] = GetMessage("top_panel_updates_err").' '.$update_res["error"].'<br><a href="/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&amp;mid=main&amp;tabControl_active_tab=edit5">'.GetMessage("top_panel_updates_settings").'</a>';
			}
		}

		return $update_res;
	}
}

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

function ini_get_bool($param)
{
	$val = ini_get($param);
	return ($val == '1' || strtolower($val) == 'on');
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
	\Bitrix\Main\Type\Collection::sortByColumn($array, $columns, $callbacks, $defaultValueIfNotSetValue, $preserveKeys);
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
	return false;
}

/**
 * Set session expired, e.g. if you want to destroy session after this hit
 * @param bool $pIsExpired
 */
function setSessionExpired($pIsExpired = true)
{
	$_SESSION["IS_EXPIRED"] = $pIsExpired;
}

/**
 * @return bool
 */
function isSessionExpired()
{
	return isset($_SESSION["IS_EXPIRED"]) && $_SESSION["IS_EXPIRED"] === true;
}
