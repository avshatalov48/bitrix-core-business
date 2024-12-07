<?php

use Bitrix\Main\Application;

IncludeModuleLangFile(__FILE__);

function CheckFilterDates($date1, $date2, &$date1_wrong, &$date2_wrong, &$date2_less_date1)
{
	global $DB;
	$date1 = trim($date1);
	$date2 = trim($date2);
	$date1_wrong = "N";
	$date2_wrong = "N";
	$date2_less_date1 = "N";
	if ($date1 <> '' && !CheckDateTime($date1)) $date1_wrong = "Y";
	if ($date2 <> '' && !CheckDateTime($date2)) $date2_wrong = "Y";
	if ($date1_wrong!="Y" && $date2_wrong!="Y" && $date1 <> '' && $date2 <> '' && $DB->CompareDates($date2,$date1)<0) $date2_less_date1="Y";
}

function InitFilterEx($arName, $varName, $action="set", $session=true, $FilterLogic="FILTER_logic")
{
	$sessAdmin = Application::getInstance()->getSession()["SESS_ADMIN"];
	if ($session && isset($sessAdmin[$varName]) && is_array($sessAdmin[$varName]))
	{
		$FILTER = $sessAdmin[$varName];
	}
	else
	{
		$FILTER = [];
	}

	global $$FilterLogic;
	if ($action=="set")
	{
		$FILTER[$FilterLogic] = $$FilterLogic;
	}
	else
	{
		$$FilterLogic = ($FILTER[$FilterLogic] ?? '');
	}

	for($i=0, $n=count($arName); $i < $n; $i++)
	{
		$name = $arName[$i];
		$period = $arName[$i]."_FILTER_PERIOD";
		$direction = $arName[$i]."_FILTER_DIRECTION";
		$bdays = $arName[$i]."_DAYS_TO_BACK";

		global $$name, $$direction, $$period, $$bdays;

		if ($action=="set")
		{
			$FILTER[$name] = $$name;
			if(isset($$period) || isset($FILTER[$period]))
				$FILTER[$period] = $$period;

			if(isset($$direction) || isset($FILTER[$direction]))
				$FILTER[$direction] = $$direction;

			if(isset($$bdays) || isset($FILTER[$bdays]))
			{
				$FILTER[$bdays] = $$bdays;
				if ((string)$$bdays <> '' && $$bdays!="NOT_REF")
					$$name = GetTime(time()-86400*intval($FILTER[$bdays]));
			}

		}
		else
		{
			$$name = $FILTER[$name] ?? null;
			if(isset($$period) || isset($FILTER[$period]))
				$$period = $FILTER[$period];

			if(isset($$direction) || isset($FILTER[$direction]))
				$$direction = $FILTER[$direction];

			if (isset($FILTER[$bdays]) && (string)$FILTER[$bdays] <> '' && $FILTER[$bdays]!="NOT_REF")
			{
				$$bdays = $FILTER[$bdays];
				$$name = GetTime(time()-86400*intval($FILTER[$bdays]));
			}
		}
	}

	if($session)
	{
		if(!is_array(Application::getInstance()->getSession()["SESS_ADMIN"]))
			Application::getInstance()->getSession()->set("SESS_ADMIN", []);
		Application::getInstance()->getSession()["SESS_ADMIN"][$varName] = $FILTER;
	}
}

function DelFilterEx($arName, $varName, $session=true, $FilterLogic="FILTER_logic")
{
	global $$FilterLogic;

	if ($session)
		unset(Application::getInstance()->getSession()["SESS_ADMIN"][$varName]);

	foreach ($arName as $name)
	{
		$period = $name."_FILTER_PERIOD";
		$direction = $name."_FILTER_DIRECTION";
		$bdays = $name."_DAYS_TO_BACK";

		global $$name, $$period, $$direction, $$bdays;

		$$name = "";
		$$period ="";
		$$direction = "";
		$$bdays = "";
	}

	$$FilterLogic = "and";
}

function InitFilter($arName)
{
	$md5Path = md5(GetPagePath());
	$FILTER = Application::getInstance()->getSession()->get("SESS_ADMIN")[$md5Path];

	foreach ($arName as $name)
	{
		global $$name;

		if(isset($$name))
			$FILTER[$name] = $$name;
		else
			$$name = $FILTER[$name];
	}

	Application::getInstance()->getSession()->get("SESS_ADMIN")[$md5Path] = $FILTER;
}

function DelFilter($arName)
{
	$md5Path = md5(GetPagePath());
	unset(Application::getInstance()->getSession()->get("SESS_ADMIN")[$md5Path]);

	foreach ($arName as $name)
	{
		global $$name;
		$$name = "";
	}
}

function GetFilterHiddens($var = "filter_", $button = array("filter" => "Y", "set_filter" => "Y"))
{
	$res = '';
	// если поступил не массив имен переменных то
	$arrVars = [];
	if (!is_array($var))
	{
		// получим имена переменных фильтра по префиксу
		$arKeys = @array_merge(array_keys($_GET), array_keys($_POST));
		if (is_array($arKeys) && !empty($arKeys))
		{
			foreach (array_unique($arKeys) as $key)
			{
				if (str_starts_with($key, $var))
				{
					$arrVars[] = $key;
				}
			}
		}
	}
	else
	{
		$arrVars = $var;
	}

	// если получили массив переменных фильтра то
	if (is_array($arrVars) && !empty($arrVars))
	{
		// соберем строку из URL параметров
		foreach ($arrVars as $var_name)
		{
			global $$var_name;
			$value = $$var_name;
			if (is_array($value))
			{
				foreach($value as $v)
				{
					$res .= '<input type="hidden" name="'.htmlspecialcharsbx($var_name).'[]" value="'.htmlspecialcharsbx($v).'">';
				}
			}
			elseif ((string)$value <> '' && $value!="NOT_REF")
			{
				$res .= '<input type="hidden" name="'.htmlspecialcharsbx($var_name).'" value="'.htmlspecialcharsbx($value).'">';
			}
		}
	}

	if(is_array($button))
	{
		foreach($button as $key => $val)
		{
			$res.='<input type="hidden" name="'.htmlspecialcharsbx($key).'" value="'.htmlspecialcharsbx($val).'">';
		}
	}
	else
	{
		$res .= $button;
	}

	return $res;
}

function GetFilterParams($var="filter_", $bDoHtmlEncode=true, $button = array("filter" => "Y", "set_filter" => "Y"))
{
	$arrVars = array(); // массив имен переменных фильтра
	$res=""; // результирующая строка

	// если поступил не массив имен переменных то
	if(!is_array($var))
	{
		// получим имена переменных фильтра по префиксу
		$arKeys = @array_merge(array_keys($_GET), array_keys($_POST));
		if(is_array($arKeys) && !empty($arKeys))
		{
			foreach (array_unique($arKeys) as $key)
				if (str_starts_with($key, $var))
					$arrVars[] = $key;
		}
	}
	else
		$arrVars = $var;

	// если получили массив переменных фильтра то
	if(is_array($arrVars) && !empty($arrVars))
	{
		// соберем строку из URL параметров
		foreach($arrVars as $var_name)
		{
			global $$var_name;
			$value = $$var_name;
			if(is_array($value))
			{
				foreach($value as $v)
					$res .= "&".urlencode($var_name)."[]=".urlencode($v);
			}
			elseif((string)$value <> '' && $value!="NOT_REF")
			{
				$res .= "&".urlencode($var_name)."=".urlencode($value);
			}
		}
	}

	if(is_array($button))
	{
		foreach($button as $key => $val)
		{
			$res .= "&".$key."=".urlencode($val);
		}
	}
	else
	{
		$res .= $button;
	}


	$tmp_phpbug = ($bDoHtmlEncode) ? htmlspecialcharsbx($res) : $res;

	return $tmp_phpbug;
	//return ($bDoHtmlEncode) ? htmlspecialcharsbx($res) : $res;
}

// устаревшая функция, оставлена для совместимости
function GetFilterStr($arr, $button="set_filter")
{
	$str = '';
	foreach ($arr as $var)
	{
		global $$var;
		$value = $$var;
		if (is_array($value))
		{
			if (!empty($value))
			{
				foreach($value as $v)
				{
					$str .= "&".urlencode($var)."[]=".urlencode($v);
				}
			}
		}
		elseif ((string)$value <> '' && $value!="NOT_REF")
		{
			$str .= "&".urlencode($var)."=".urlencode($value);
		}
	}
	return $str."&".$button."=Y";
}

function ShowExactMatchCheckbox($name, $title=false)
{
	$var = $name."_exact_match";
	global $$var;
	if ($title===false) $title=GetMessage("MAIN_EXACT_MATCH");
	return '<input type="hidden" name="'.$name.'_exact_match" value="N">'.InputType("checkbox", $name."_exact_match", "Y", $$var, false, "", "title='".$title."'");
}

function GetUrlFromArray($arr)
{
	if(!is_array($arr))
		return "";
	$str = "";
	foreach($arr as $key => $value)
	{
		if (is_array($value))
		{
			foreach ($value as $a)
			{
				$str .= "&".$key.urlencode("[]")."=".urlencode($a);
			}
		}
		elseif((string)$value <> '' && $value!="NOT_REF")
		{
			$str .= "&".$key."=".urlencode($value);
		}
	}
	return $str;
}

function ShowAddFavorite($filterName=false, $btnName="set_filter", $module="statistic", $alt=false)
{
	global $sFilterID;

	if ($alt===false)
		$alt=GetMessage("MAIN_ADD_TO_FAVORITES");
	if ($filterName===false)
		$filterName = $sFilterID;
	$url = urlencode($_SERVER['SCRIPT_NAME'] . "?" . $_SERVER["QUERY_STRING"] . GetUrlFromArray(Application::getInstance()->getSession()["SESS_ADMIN"][$filterName])."&".$btnName."=Y");
	$str = "<a target='_blank' href='".BX_ROOT."/admin/favorite_edit.php?lang=".LANG."&module=$module&url=$url'><img alt='".$alt."' src='".BX_ROOT."/images/main/add_favorite.gif' width='16' height='16' border=0></a>";
	echo $str;
}

function IsFiltered($strSqlSearch)
{
	return ($strSqlSearch <> '' && $strSqlSearch!="(1=1)" && $strSqlSearch!="(1=2)");
}

function ResetFilterLogic($FilterLogic="FILTER_logic")
{
	$var = $FilterLogic."_reset";
	global $$var;
	$$var = "Y";
}

function ShowFilterLogicHelp()
{
	global $LogicHelp;
	$str = "";
	if(LANGUAGE_ID == "ru")
		$help_link = "https://dev.1c-bitrix.ru/api_help/main/general/filter.php";
	else
		$help_link = "https://www.bitrixsoft.com/help/index.html?page=".urlencode("source/main/help/en/filter.php.html");
	if ($LogicHelp != "Y")
	{
		$str = "<script>
		function LogicHelp() { window.open('".$help_link."', '','scrollbars=yes,resizable=yes,width=780,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 780)/2-5)); }
		</script>";
	}
	$str .= "<a title='".GetMessage("FILTER_LOGIC_HELP")."' class='adm-input-help-icon' href='javascript:LogicHelp()'></a>";
	$LogicHelp = "Y";
	return $str;
}

function ShowLogicRadioBtn($FilterLogic="FILTER_logic")
{
	global $$FilterLogic;

	$s_and = "checked";
	$s_or = '';
	if ($$FilterLogic == "or")
	{
		$s_or = "checked";
		$s_and = "";
	}
	$str = "<tr><td>".GetMessage("FILTER_LOGIC")."</td><td><input type='radio' name='$FilterLogic' value='and' ".$s_and.">".GetMessage("AND")."&nbsp;<input type='radio' name='$FilterLogic' value='or' ".$s_or.">".GetMessage("OR")."</td></tr>";
	return $str;
}

function GetFilterQuery($field, $val, $procent="Y", $ex_sep=array(), $clob="N", $div_fields="Y", $clob_upper="N")
{
	global $strError;
	$f = new CFilterQuery("and", "yes", $procent, $ex_sep, $clob, $div_fields, $clob_upper);
	$query = $f->GetQueryString($field, $val);
	$error = $f->error;
	if (trim($error) <> '')
	{
		$strError .= $error."<br>";
		$query = "0";
	}
	return $query;
}

function GetFilterSqlSearch($arSqlSearch=array(), $FilterLogic="FILTER_logic")
{
	$var = $FilterLogic."_reset";
	global $strError, $$FilterLogic, $$var;
	$ResetFilterLogic = $$var;
	$$FilterLogic = ($$FilterLogic=="or") ? "or" : "and";
	if($ResetFilterLogic=="Y" && $$FilterLogic=="or")
	{
		$$FilterLogic = "and";
		$strError .= GetMessage("FILTER_ERROR_LOGIC")."<br>";
	}
	if($$FilterLogic=="or")
		$strSqlSearch = "1=2";
	else
		$strSqlSearch = "1=1";
	if (is_array($arSqlSearch) && !empty($arSqlSearch))
	{
		foreach ($arSqlSearch as $condition)
		{
			if ($condition <> '' && $condition!="0")
			{
				$strSqlSearch .= "
					".mb_strtoupper($$FilterLogic)."
					(
						".$condition."
					)
					";
			}
		}
	}
	return "($strSqlSearch)";
}

$bFilterScriptShown = false;
$sFilterID = "";
function BeginFilter($sID, $bFilterSet, $bShowStatus=true)
{
	global $bFilterScriptShown, $sFilterID;
	$sFilterID = $sID;
	$s = "";
	if(!$bFilterScriptShown)
	{
		$s .= '
<script>
function showfilter(id)
{
	var div = document.getElementById("flt_div_"+id);
	var tbl = document.getElementById("flt_table_"+id);
	var head = document.getElementById("flt_head_"+id);
	var flts = "", curval = "", oldval="";
	var aCookie = document.cookie.split("; ");
	//document.cookie = "flts=X; expires=Thu, 31 Dec 1999 23:59:59 GMT; path='.BX_ROOT.'/admin/;";return;
	for (var i=0; i < aCookie.length; i++)
	{
		var aCrumb = aCookie[i].split("=");
		if ("flts" == aCrumb[0])
		{
			if(aCrumb.length>1 && aCrumb[1].length>0)
			{
				var val = aCrumb[1];
				var arFVals = val.split("&");
				for (var j=0; j < arFVals.length; j++)
				{
					val = arFVals[j];
					if(val.length>0)
					{
						val = unescape(val);
						val = val.split("=");
						if(val.length>1 && val[1].length>0)
						{
							if(val[0] == id)
								curval = val[1];
							else
								flts = flts + escape(val[0] + "=" + val[1]) + "&";
						}
					}
				}
			}
		}

		if ("flt_"+id == aCrumb[0])
			oldval = aCrumb[1];
	}

	if(div.style.display!="none")
	{
		if(tbl.offsetWidth > 0)
			head.style.width = tbl.offsetWidth;
		if(oldval!="")
			document.cookie = "flt_"+id+"=X; expires=Fri, 31 Dec 1999 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
		document.cookie = "flts="+flts+escape(id+"=N"+(tbl.offsetWidth))+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
		hidefilter(id);
	}
	else
	{
		if(oldval!="")
			document.cookie = "flt_"+id+"=X; expires=Fri, 31 Dec 1999 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
		document.cookie = "flts="+flts+escape(id+"=Y)")+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path='.BX_ROOT.'/admin/;";
		document.getElementById("flt_link_show_"+id).style.display = "none";
		document.getElementById("flt_link_hide_"+id).style.display = "inline";
		document.getElementById("flt_image_"+id).src = "'.BX_ROOT.'/images/admin/line_up.gif";
		document.getElementById("flt_image_"+id).alt = "'.GetMessage("admin_filter_hide").'";
		div.style.display = "block";
		if(tbl.clientWidth > 0)
			head.style.width = tbl.clientWidth+2;
	}
}
function hidefilter(id)
{
	document.getElementById("flt_link_show_"+id).style.display = "inline";
	document.getElementById("flt_link_hide_"+id).style.display = "none";
	document.getElementById("flt_image_"+id).src = "'.BX_ROOT.'/images/admin/line_down.gif";
	document.getElementById("flt_image_"+id).alt = "'.GetMessage("admin_filter_show").'";
	document.getElementById("flt_div_"+id).style.display = "none";
}
tmpImage = new Image();
tmpImage.src = "'.BX_ROOT.'/images/admin/line_down.gif";
tmpImage.src = "'.BX_ROOT.'/images/admin/line_up.gif";
</script>
';
		$bFilterScriptShown = true;
	}

	parse_str($_COOKIE["flts"], $arFlts);
	if(is_set($arFlts, $sID))
	{
		$fltval = $arFlts[$sID];
		if(is_set($_COOKIE, "flt_".$sID))
			unset($_COOKIE["flt_".$sID]);
	}
	else
		$fltval = $_COOKIE["flt_".$sID];

	$s .= '
<table border="0" cellspacing="0" cellpadding="0" width="'.($fltval[0]=="N"? intval(mb_substr($fltval, 1)):'').'"><tr><td>
<table border="0" cellspacing="0" cellpadding="0" width="100%" id="flt_head_'.$sID.'">
<tr>
	<td class="tablefilterhead">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
';
	if($bShowStatus)
	{
		$s .= '
	<td width="0%"><img src="'.BX_ROOT.'/images/admin/'.($bFilterSet? "green.gif":"grey.gif").'" alt="" width="16" height="16" border="0" hspace="1" vspace="1"></td>
	<td width="0%"><font class="tableheadtext">&nbsp;</font></td>
	<td width="100%" nowrap><font class="tableheadtext">'.($bFilterSet? GetMessage("admin_filter_filter_set"):GetMessage("admin_filter_filter_not_set")).'</font></td>
	<td width="0%"><font class="tableheadtext">&nbsp;&nbsp;&nbsp;</font></td>
';
	}
	else
	{
		$s .= '
	<td width="100%"><img src="/bitrix/images/1.gif" width="1" height="18" alt=""></td>
';
	}
	$s .= '
	<td width="0%"><font class="tableheadtext"><div id="flt_link_hide_'.$sID.'" style="display:inline;"><a href="javascript:showfilter(\''.$sID.'\');" title="'.GetMessage("admin_filter_hide").'">'.GetMessage("admin_filter_hide2").'</a></div><div id="flt_link_show_'.$sID.'" style="display:none;"><a href="javascript:showfilter(\''.$sID.'\');" title="'.GetMessage("admin_filter_show").'">'.GetMessage("admin_filter_show2").'</a></div></font></td>
	<td width="0%"><font class="tableheadtext">&nbsp;</font><a href="javascript:showfilter(\''.$sID.'\');"><img id="flt_image_'.$sID.'" src="'.BX_ROOT.'/images/admin/line_up.gif" alt="'.GetMessage("admin_filter_hide").'" width="30" height="11" border="0" hspace="3"></a></td>
</tr>
</table>
	</td>
</tr>
</table>
<div id="flt_div_'.$sID.'">
<table border="0" cellspacing="1" cellpadding="0" class="filter" id="flt_table_'.$sID.'" width="100%">
';
	return $s;
}

function EndFilter($sID="")
{
	global $sFilterID;
	if($sID == "")
		$sID = $sFilterID;
	$s = '
</table>
</div>
</td></tr></table>

';
	parse_str($_COOKIE["flts"], $arFlts);
	if(is_set($arFlts, $sID))
		$fltval = $arFlts[$sID];
	else
		$fltval = $_COOKIE["flt_".$sID];

	if($fltval[0]<>"Y")
		$s .= '<script>hidefilter(\''.CUtil::JSEscape($sID).'\');</script>'."\n";
	return $s;
}

function BeginNote($sParams = '', $sMessParams = '')
{
	if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1)
	{
		\Bitrix\Main\UI\Extension::load("ui.alerts");
		return '<div class="ui-alert ui-alert-warning" '.$sParams.'><div class="ui-btn-message">';
	}
	else
	{
		return '<div class="adm-info-message-wrap" '.$sParams.'><div class="adm-info-message" '.$sMessParams.'>';
	}
}
function EndNote()
{
	return '
	</div>
</div>
';
}
function ShowSubMenu($aMenu)
{
	$s = '
<table cellspacing=0 cellpadding=0 border=0 >
<tr>
<td width="6"><img height=6 alt="" src="/bitrix/images/admin/mn_ltc.gif" width=6></td>
<td background=/bitrix/images/admin/mn_tline.gif><img height=1 alt="" src="/bitrix/images/1.gif" width=1></td>
<td width="6"><img height=6 alt="" src="/bitrix/images/admin/mn_rtc.gif" width=6></td></tr>
<tr>
<td width="6" background=/bitrix/images/admin/mn_lline.gif><img height=1 alt="" src="/bitrix/images/1.gif" width=1></td>
<td valign=top class="submenutable">

<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td><font class="submenutext">
';
foreach($aMenu as $menu)
{
	if($menu["SEPARATOR"]<>"")
	{
		$s .= '
</font></td>
<td width=13 background="/bitrix/images/admin/mn_delim.gif"><img height=1 alt="" src="/bitrix/images/1.gif" width=13></td>
<td><font class="submenutext">
';
		continue;
	}
	$s .= '
<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td width="7"><img src="/bitrix/images/admin/arr_right'.($menu["WARNING"]<>""? "_red":"").'.gif" alt="" width="7" height="7" border="0" vspace="4"></td>
	<td class="submenutext">&nbsp;</td>
	<td><font class="submenutext"><a class="submenutext" title="'.$menu["TITLE"].'" href="'.$menu["LINK"].'" '.$menu["LINK_PARAM"].'>'.$menu["TEXT"].'</a>'.$menu["TEXT_PARAM"].'</font></td>
</tr>
</table>
';
}
$s .= '
</font></td>
</tr>
</table>
<td width="6" background=/bitrix/images/admin/mn_rline.gif><img height=1 alt="" src="/bitrix/images/1.gif" width=1></td></tr>
<tr>
<td width="6"><img height=6 alt="" src="/bitrix/images/admin/mn_lbc.gif" width=6></td>
<td background=/bitrix/images/admin/mn_bline.gif><img height=1 alt="" src="/bitrix/images/1.gif" width=1></td>
<td width="6"><img height=6 alt="" src="/bitrix/images/admin/mn_rbc.gif" width=6></td></tr>
</table>
';
return $s;
}

function InitSorting($Path=false, $sByVar="by", $sOrderVar="order")
{
	global $APPLICATION, $$sByVar, $$sOrderVar;

	if($Path===false)
		$Path = $APPLICATION->GetCurPage();

	$md5Path = md5($Path);

	if ($$sByVar <> '')
		Application::getInstance()->getSession()["SESS_SORT_BY"][$md5Path] = $$sByVar;
	else
		$$sByVar = Application::getInstance()->getSession()["SESS_SORT_BY"][$md5Path] ?? '';

	if($$sOrderVar <> '')
		Application::getInstance()->getSession()["SESS_SORT_ORDER"][$md5Path] = $$sOrderVar;
	else
		$$sOrderVar = Application::getInstance()->getSession()["SESS_SORT_ORDER"][$md5Path] ?? '';

	$$sByVar = strtolower($$sByVar);
	$$sOrderVar = strtolower($$sOrderVar);
}

function SortingEx($By, $Path = false, $sByVar="by", $sOrderVar="order", $Anchor="nav_start")
{
	global $APPLICATION;

	$sImgDown = "<img src=\"".BX_ROOT."/images/icons/up.gif\" width=\"15\" height=\"15\" border=\"0\" alt=\"".GetMessage("ASC_ORDER")."\">";
	$sImgUp = "<img src=\"".BX_ROOT."/images/icons/down.gif\" width=\"15\" height=\"15\" border=\"0\" alt=\"".GetMessage("DESC_ORDER")."\">";

	global $$sByVar, $$sOrderVar;
	$by=$$sByVar;
	$order=$$sOrderVar;

	if(mb_strtoupper($By) == mb_strtoupper($by))
	{
		if(mb_strtoupper($order) == "DESC")
			$sImgUp = "<img src=\"".BX_ROOT."/images/icons/down-$$$.gif\" width=\"15\" height=\"15\" border=\"0\" alt=\"".GetMessage("DESC_ORDER")."\">";
		else
			$sImgDown = "<img src=\"".BX_ROOT."/images/icons/up-$$$.gif\" width=\"15\" height=\"15\" border=\"0\" alt=\"".GetMessage("ASC_ORDER")."\">";
	}

	//Если путь не задан, то будем брать текущий со всеми переменными
	if($Path===false)
		$Path = $APPLICATION->GetCurUri();

	//Если нет переменных, то надо добавлять параметры через ?
	$found = mb_strpos($Path, "?");
	if ($found === false) $strAdd2URL = "?";
	else $strAdd2URL = "&";

	$Path = preg_replace("/([?&])".$sByVar."=[^&]*[&]*/i", "\\1", $Path);
	$Path = preg_replace("/([?&])".$sOrderVar."=[^&]*[&]*/i", "\\1", $Path);

	$strTest = mb_substr($Path, mb_strlen($Path) - 1);
	if($strTest=="&" OR $strTest == "?")
		$strAdd2URL="";

	return "<nobr><a href=\"".htmlspecialcharsbx($Path.$strAdd2URL.$sByVar."=".$By."&".$sOrderVar."=asc#".$Anchor)."\">".$sImgDown."</a>".
			"<a href=\"".htmlspecialcharsbx($Path.$strAdd2URL.$sByVar."=".$By."&".$sOrderVar."=desc#".$Anchor)."\">".$sImgUp."</a></nobr>";
}
