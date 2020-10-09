<?
use Bitrix\Main\Loader;

Loader::includeModule('form');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_result_list.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

function CheckFilter()
{
	global $strError, $MESS, $arrFORM_FILTER;
	global $find_date_create_1, $find_date_create_2;
	$str = "";
	CheckFilterDates($find_date_create_1, $find_date_create_2, $date1_wrong, $date2_wrong, $date2_less);
	if ($date1_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_FROM")."<br>";
	if ($date2_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_TO")."<br>";
	if ($date2_less=="Y") $str.= GetMessage("FORM_FROM_TILL_DATE_CREATE")."<br>";

	if (is_array($arrFORM_FILTER))
	{
		reset($arrFORM_FILTER);
		foreach ($arrFORM_FILTER as $arrF)
		{
			if (is_array($arrF))
			{
				foreach ($arrF as $arr)
				{
					$title = ($arr["TITLE_TYPE"]=="html") ? strip_tags(htmlspecialcharsback($arr["TITLE"])) : $arr["TITLE"];
					if ($arr["FILTER_TYPE"]=="date")
					{
						$date1 = $_GET["find_".$arr["FID"]."_1"];
						$date2 = $_GET["find_".$arr["FID"]."_2"];
						CheckFilterDates($date1, $date2, $date1_wrong, $date2_wrong, $date2_less);
						if ($date1_wrong=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE1"))."<br>";
						if ($date2_wrong=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE2"))."<br>";
						if ($date2_less=="Y")
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_DATE2_LESS"))."<br>";
					}
					if ($arr["FILTER_TYPE"]=="integer")
					{
						$int1 = intval($_GET["find_".$arr["FID"]."_1"]);
						$int2 = intval($_GET["find_".$arr["FID"]."_2"]);
						if ($int1>0 && $int2>0 && $int2<$int1)
						{
							$str .= str_replace("#TITLE#", $title, GetMessage("FORM_INT2_LESS"))."<br>";
						}
					}
				}
			}
		}
	}
	$strError .= $str;
	if ($str <> '') return false; else return true;
}

if ($FORM_ID>0 && $WEB_FORM_ID<=0) $WEB_FORM_ID = $FORM_ID;
if ($WEB_FORM_ID>0 && $FORM_ID<=0) $FORM_ID = $WEB_FORM_ID;

$USER_ID = $USER->GetID();

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<15) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if (is_array($ARR_RESULT) && count($ARR_RESULT)>0 && $delete <> '' && (check_bitrix_sessid() || defined("FORM_NOT_CHECK_SESSID")))
{
	foreach($ARR_RESULT as $rid) CFormResult::Delete($rid);
}

$del_id = intval($del_id);
if ($del_id>0 && (check_bitrix_sessid() || defined("FORM_NOT_CHECK_SESSID"))) CFormResult::Delete($del_id);

$FilterArr = Array(
	"find_id",
	"find_id_exact_match",
	"find_status",
	"find_status_id",
	"find_status_id_exact_match",
	"find_timestamp_1",
	"find_timestamp_2",
	"find_date_create_2",
	"find_date_create_1",
	"find_date_create_2",
	"find_registered",
	"find_user_auth",
	"find_user_id",
	"find_user_id_exact_match",
	"find_guest_id",
	"find_guest_id_exact_match",
	"find_session_id",
	"find_session_id_exact_match"
	);
$z = CFormField::GetFilterList($WEB_FORM_ID, array("ACTIVE" => "Y"));
while ($zr=$z->Fetch())
{
	$FID = $WEB_FORM_NAME."_".$zr["SID"]."_".$zr["PARAMETER_NAME"]."_".$zr["FILTER_TYPE"];
	$zr["FID"] = $FID;
	$arrFORM_FILTER[$zr["SID"]][] = $zr;
	$fname = "find_".$FID;
	if ($zr["FILTER_TYPE"]=="date" || $zr["FILTER_TYPE"]=="integer")
	{
		$FilterArr[] = $fname."_1";
		$FilterArr[] = $fname."_2";
		$FilterArr[] = $fname."_0";
	}
	elseif ($zr["FILTER_TYPE"]=="text")
	{
		$FilterArr[] = $fname;
		$FilterArr[] = $fname."_exact_match";
	}
	else $FilterArr[] = $fname;
}
$sess_filter = "FORM_RESULT_LIST_".$WEB_FORM_NAME;
if ($set_filter <> '') InitFilterEx($FilterArr,$sess_filter,"set");
else InitFilterEx($FilterArr,$sess_filter,"get");
if ($del_filter <> '') DelFilterEx($FilterArr,$sess_filter);

InitBVar($find_id_exact_match);
InitBVar($find_status_id_exact_match);
InitBVar($find_user_id_exact_match);
InitBVar($find_guest_id_exact_match);
InitBVar($find_session_id_exact_match);
if (CheckFilter())
{
	$arFilter = Array(
		"ID"						=> $find_id,
		"ID_EXACT_MATCH"			=> $find_id_exact_match,
		"STATUS"					=> $find_status,
		"STATUS_ID"					=> $find_status_id,
		"STATUS_ID_EXACT_MATCH"		=> $find_status_id_exact_match,
		"TIMESTAMP_1"				=> $find_timestamp_1,
		"TIMESTAMP_2"				=> $find_timestamp_2,
		"DATE_CREATE_1"				=> $find_date_create_1,
		"DATE_CREATE_2"				=> $find_date_create_2,
		"REGISTERED"				=> $find_registered,
		"USER_AUTH"					=> $find_user_auth,
		"USER_ID"					=> $find_user_id,
		"USER_ID_EXACT_MATCH"		=> $find_user_id_exact_match,
		"GUEST_ID"					=> $find_guest_id,
		"GUEST_ID_EXACT_MATCH"		=> $find_guest_id_exact_match,
		"SESSION_ID"				=> $find_session_id,
		"SESSION_ID_EXACT_MATCH"	=> $find_session_id_exact_match
		);
	if (is_array($arrFORM_FILTER))
	{
		foreach ($arrFORM_FILTER as $arrF)
		{
			foreach ($arrF as $arr)
			{
				if ($arr["FILTER_TYPE"]=="date" || $arr["FILTER_TYPE"]=="integer")
				{
					$arFilter[$arr["FID"]."_1"] = ${"find_".$arr["FID"]."_1"};
					$arFilter[$arr["FID"]."_2"] = ${"find_".$arr["FID"]."_2"};
					$arFilter[$arr["FID"]."_0"] = ${"find_".$arr["FID"]."_0"};
				}
				elseif ($arr["FILTER_TYPE"]=="text")
				{
					$arFilter[$arr["FID"]] = ${"find_".$arr["FID"]};
					$exact_match = (${"find_".$arr["FID"]."_exact_match"}=="Y") ? "Y" : "N";
					$arFilter[$arr["FID"]."_exact_match"] = $exact_match;
				}
				else $arFilter[$arr["FID"]] = ${"find_".$arr["FID"]};
			}
		}
	}
}

if ($save <> '' && $_SERVER['REQUEST_METHOD']=="POST" && (check_bitrix_sessid() || defined("FORM_NOT_CHECK_SESSID")))
{
	if (isset($RESULT_ID) && is_array($RESULT_ID))
	{
		foreach ($RESULT_ID as $rid)
		{
			$rid = intval($rid);
			$var_STATUS_PREV = "STATUS_PREV_".$rid;
			$var_STATUS = "STATUS_".$rid;
			if (intval(${$var_STATUS})>0 && ${$var_STATUS_PREV} != ${$var_STATUS})
			{
				CFormResult::SetStatus($rid, ${$var_STATUS});
			}
		}
	}
}

$result = CFormResult::GetList($WEB_FORM_ID, $by, $order, $arFilter, $is_filtered);

$HELP_FILE_ACCESS = $APPLICATION->GetFileAccessPermission("/bitrix/modules/form/help/".LANGUAGE_ID."/index.php");
$MODULE_RIGHT = $APPLICATION->GetGroupRight("form");
$MAIN_RIGHT = $APPLICATION->GetGroupRight("main");