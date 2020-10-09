<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arParams['WEB_FORM_ID'] = intval($arParams['WEB_FORM_ID']);
$arParams['RESULT_ID'] = intval($arParams['RESULT_ID']);
if (!$arParams['RESULT_ID']) $arParams['RESULT_ID'] = '';

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE'])
	? (method_exists('CSite', 'GetNameFormat') ? CSite::GetNameFormat() : "#NAME# #LAST_NAME#")
	: $arParams["NAME_TEMPLATE"];

if (!function_exists("__FormResultListCheckFilter"))
{
	function __FormResultListCheckFilter(&$str_error, &$arrFORM_FILTER) // check of filter values
	{
		global $strError, $_GET;
		global $find_date_create_1, $find_date_create_2;
		$str = "";

		CheckFilterDates($find_date_create_1, $find_date_create_2, $date1_wrong, $date2_wrong, $date2_less);
		if ($date1_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_FROM")."<br />";
		if ($date2_wrong=="Y") $str.= GetMessage("FORM_WRONG_DATE_CREATE_TO")."<br />";
		if ($date2_less=="Y") $str.= GetMessage("FORM_FROM_TILL_DATE_CREATE")."<br />";

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
								$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE1"))."<br />";
							if ($date2_wrong=="Y")
								$str .= str_replace("#TITLE#", $title, GetMessage("FORM_WRONG_DATE2"))."<br />";
							if ($date2_less=="Y")
								$str .= str_replace("#TITLE#", $title, GetMessage("FORM_DATE2_LESS"))."<br />";
						}
						if ($arr["FILTER_TYPE"]=="integer")
						{
							$int1 = intval($_GET["find_".$arr["FID"]."_1"]);
							$int2 = intval($_GET["find_".$arr["FID"]."_2"]);
							if ($int1>0 && $int2>0 && $int2<$int1)
							{
								$str .= str_replace("#TITLE#", $title, GetMessage("FORM_INT2_LESS"))."<br />";
							}
						}
					}
				}
			}
		}

		$strError .= $str;
		$str_error .= $str;

		return $str == '';
	}
}

if (CModule::IncludeModule("form"))
{
	$GLOBALS['strError'] = '';
	//  insert chain item
	if ($arParams["CHAIN_ITEM_TEXT"] <> '')
	{
		$APPLICATION->AddChainItem($arParams["CHAIN_ITEM_TEXT"], $arParams["CHAIN_ITEM_LINK"]);
	}

	// preparing additional parameters
	$arResult["FORM_ERROR"] = '';
	if (isset($_REQUEST["strError"]) && is_string($_REQUEST["strError"]))
	{
		$arResult["FORM_ERROR"] = $_REQUEST["strError"];
	}
	//$arResult["FORM_NOTE"] = $_REQUEST["strFormNote"];
	if (!empty($_REQUEST["formresult"]) && $_SERVER['REQUEST_METHOD'] != 'POST')
	{
		$formResult = mb_strtoupper($_REQUEST['formresult']);
		switch ($formResult)
		{
			case 'ADDOK':
				$arResult['FORM_NOTE'] = str_replace("#RESULT_ID#", $arParams["RESULT_ID"], GetMessage('FORM_NOTE_ADDOK'));
			break;
			default:
				$arResult['FORM_NOTE'] = str_replace("#RESULT_ID#", $arParams["RESULT_ID"], GetMessage('FORM_NOTE_EDITOK'));
		}
	}

	$arParams["F_RIGHT"] = CForm::GetPermission($arParams["WEB_FORM_ID"]);

	if($arParams["F_RIGHT"] < 15) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

	$arParams["isStatisticIncluded"] = CModule::IncludeModule("statistic");

	if (is_array($arParams["NOT_SHOW_FILTER"]))
	{
		$arParams["arrNOT_SHOW_FILTER"] = $arParams["NOT_SHOW_FILTER"];
	}
	else
	{
		$arParams["arrNOT_SHOW_FILTER"] = explode(",",$arParams["NOT_SHOW_FILTER"]);
	}

	if (is_array($arParams["arrNOT_SHOW_FILTER"])) //array_walk($arParams["arrNOT_SHOW_FILTER"], create_function("&\$item", "\$item=trim(\$item);"));
		TrimArr($arParams["arrNOT_SHOW_FILTER"]);

	else $arParams["arrNOT_SHOW_FILTER"]=array();

	if (is_array($arParams["NOT_SHOW_TABLE"]))
	{
		$arParams["arrNOT_SHOW_TABLE"] = $arParams["NOT_SHOW_TABLE"];
	}
	else
	{
		$arParams["arrNOT_SHOW_TABLE"] = explode(",",$arParams["NOT_SHOW_TABLE"]);
	}
	if (is_array($arParams["arrNOT_SHOW_TABLE"])) //array_walk($arParams["arrNOT_SHOW_TABLE"], create_function("&\$item", "\$item=trim(\$item);"));
		TrimArr($arParams["arrNOT_SHOW_TABLE"]);

	else $arParams["arrNOT_SHOW_TABLE"]=array();

	// deleting single form result
	$del_id = intval($_REQUEST["del_id"]);

	if ($del_id > 0 && check_bitrix_sessid())
	{
		$GLOBALS['strError'] = '';
		CFormResult::Delete($del_id);

		if ($GLOBALS['strError'] == '')
		{
			LocalRedirect($APPLICATION->GetCurPageParam("", array("del_id", "sessid", 'formresult'), false));
			exit();
		}
	}

	// deleting multiple form results
	if ($_REQUEST["delete"] && check_bitrix_sessid())
	{
		$ARR_RESULT = $_REQUEST["ARR_RESULT"];
		if (is_array($ARR_RESULT) && count($ARR_RESULT) > 0 && check_bitrix_sessid())
		{
			$GLOBALS['strError'] = '';
			foreach ($ARR_RESULT as $del_id)
			{
				$del_id = intval($del_id);
				if ($del_id > 0) CFormResult::Delete($del_id); // rights check inside
			}

			if ($GLOBALS['strError'] == '')
			{
				LocalRedirect($APPLICATION->GetCurPageParam("", array("delete", "sessid", 'formresult')));
				exit();
			}
		}
	}

	if ($GLOBALS['strError'] <> '')
		$arResult["FORM_ERROR"] .= $GLOBALS['strError'];

	if (intval($arParams["WEB_FORM_ID"])>0)
		$dbres = CForm::GetByID($arParams["WEB_FORM_ID"]);
	else
		$dbres = CForm::GetBySID($arParams["WEB_FORM_NAME"]);

	// get form info
	if ($arParams["arFormInfo"] = $dbres->Fetch())
	{
		$GLOBALS["WEB_FORM_ID"] = $arParams["WEB_FORM_ID"] = $arParams["arFormInfo"]["ID"];
		$GLOBALS["WEB_FORM_NAME"] = $arParams["WEB_FORM_NAME"] = $arParams["arFormInfo"]["SID"];

		// check form params
		$arParams["USER_ID"] = $USER->GetID();

		// prepare filter
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

		$arResult["arrFORM_FILTER"] = array();

		$arListFilter = array("ACTIVE" => "Y");
		if (count($arParams["arrNOT_SHOW_FILTER"]) > 0)
		{
			$arListFilter["FIELD_SID"] = "~'".implode("' & ~'", $arParams["arrNOT_SHOW_FILTER"])."'";
		}

		$z = CFormField::GetFilterList($arParams["WEB_FORM_ID"], $arListFilter);
		while ($zr=$z->Fetch())
		{
			$FID = $arParams["WEB_FORM_NAME"]."_".$zr["SID"]."_".$zr["PARAMETER_NAME"]."_".$zr["FILTER_TYPE"];
			$zr["FID"] = $FID;
			if (!is_set($arResult["arrFORM_FILTER"][$zr["SID"]])) $arResult["arrFORM_FILTER"][$zr["SID"]] = array();
			$arResult["arrFORM_FILTER"][$zr["SID"]][] = $zr;
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

		//fix minor bug with CFormField::GetFilterList and filter list logic - without it "exist" checkbox will be before main search field for date fields in filter
		foreach ($arResult["arrFORM_FILTER"] as $q_sid => $arFilterFields)
		{
			$cntFF = count($arFilterFields);
			if (is_array($arFilterFields) && $cntFF > 0)
			{
				$change = false;
				for($i = 0; $i < $cntFF; $i++)
				{
					if ($arFilterFields[$i]["FILTER_TYPE"] == "date")
					{
						$tmp = $arFilterFields[$i];
						$arFilterFields[$i] = $arFilterFields[$i-1];
						$arFilterFields[$i-1] = $tmp;
						$change = true;
					}
				}

				if ($change) $arResult["arrFORM_FILTER"][$q_sid] = $arFilterFields;
			}
		}

		$arParams["sess_filter"] = "FORM_RESULT_LIST_".$arParams["WEB_FORM_NAME"];
		if ($_REQUEST["set_filter"] <> '')
			InitFilterEx($FilterArr,$arParams["sess_filter"],"set");
		else
			InitFilterEx($FilterArr,$arParams["sess_filter"],"get");

		if ($_REQUEST["del_filter"] <> '')
		{
			DelFilterEx($FilterArr,$arParams["sess_filter"]);
		}
		else
		{
			InitBVar($GLOBALS["find_id_exact_match"]);
			InitBVar($GLOBALS["find_status_id_exact_match"]);
			InitBVar($GLOBALS["find_user_id_exact_match"]);
			InitBVar($GLOBALS["find_guest_id_exact_match"]);
			InitBVar($GLOBALS["find_session_id_exact_match"]);

			$arResult["ERROR_MESSAGE"] = "";
			if (__FormResultListCheckFilter($arResult["ERROR_MESSAGE"], $arResult["arrFORM_FILTER"]))
			{
				$arFilter = Array(
					"ID"						=> $GLOBALS["find_id"],
					"ID_EXACT_MATCH"			=> $GLOBALS["find_id_exact_match"],
					"STATUS"					=> $GLOBALS["find_status"],
					"STATUS_ID"					=> $GLOBALS["find_status_id"],
					"STATUS_ID_EXACT_MATCH"		=> $GLOBALS["find_status_id_exact_match"],
					"TIMESTAMP_1"				=> $GLOBALS["find_timestamp_1"],
					"TIMESTAMP_2"				=> $GLOBALS["find_timestamp_2"],
					"DATE_CREATE_1"				=> $GLOBALS["find_date_create_1"],
					"DATE_CREATE_2"				=> $GLOBALS["find_date_create_2"],
					"REGISTERED"				=> $GLOBALS["find_registered"],
					"USER_AUTH"					=> $GLOBALS["find_user_auth"],
					"USER_ID"					=> $GLOBALS["find_user_id"],
					"USER_ID_EXACT_MATCH"		=> $GLOBALS["find_user_id_exact_match"],
					"GUEST_ID"					=> $GLOBALS["find_guest_id"],
					"GUEST_ID_EXACT_MATCH"		=> $GLOBALS["find_guest_id_exact_match"],
					"SESSION_ID"				=> $GLOBALS["find_session_id"],
					"SESSION_ID_EXACT_MATCH"	=> $GLOBALS["find_session_id_exact_match"]
					);
				if (is_array($arResult["arrFORM_FILTER"]))
				{
					foreach ($arResult["arrFORM_FILTER"] as $arrF)
					{
						foreach ($arrF as $arr)
						{
							if ($arr["FILTER_TYPE"]=="date" || $arr["FILTER_TYPE"]=="integer")
							{
								$arFilter[$arr["FID"]."_1"] = $GLOBALS["find_".$arr["FID"]."_1"];
								$arFilter[$arr["FID"]."_2"] = $GLOBALS["find_".$arr["FID"]."_2"];
								$arFilter[$arr["FID"]."_0"] = $GLOBALS["find_".$arr["FID"]."_0"];
							}
							elseif ($arr["FILTER_TYPE"]=="text")
							{
								$arFilter[$arr["FID"]] = $GLOBALS["find_".$arr["FID"]];
								$exact_match = ($GLOBALS["find_".$arr["FID"]."_exact_match"]=="Y") ? "Y" : "N";
								$arFilter[$arr["FID"]."_exact_match"] = $exact_match;
							}
							else $arFilter[$arr["FID"]] = $GLOBALS["find_".$arr["FID"]];
						}
					}
				}
			}
		}

		if ($_POST['save'] <> '' && $_SERVER['REQUEST_METHOD']=="POST" && check_bitrix_sessid())
		{
			// update results
			if (isset($_POST["RESULT_ID"]) && is_array($_POST["RESULT_ID"]))
			{
				$RESULT_ID = $_POST["RESULT_ID"];
				foreach ($RESULT_ID as $rid)
				{
					$rid = intval($rid);
					$var_STATUS_PREV = "STATUS_PREV_".$rid;
					$var_STATUS = "STATUS_".$rid;
					if (intval($_REQUEST[$var_STATUS])>0 && $_REQUEST[$var_STATUS_PREV]!=$_REQUEST[$var_STATUS])
					{
						CFormResult::SetStatus($rid, $_REQUEST[$var_STATUS]); // rights and status check inside
					}
				}
			}
		}

		// get results list
		$arParams["by"] = $_REQUEST["by"];
		$arParams["order"] = $_REQUEST["order"];
		$arResult["is_filtered"] = false;

		$rsResults = CFormResult::GetList($arParams["WEB_FORM_ID"], $arParams["by"], $arParams["order"], $arFilter, $arResult["is_filtered"]);

		$arResult["res_counter"] = 0;
		$arParams["can_delete_some"] = false;
		$arResult["arRID"] = array();
		$arResults = array();
		while ($arR = $rsResults->Fetch())
		{
			$arResult["res_counter"]++;
			$arResults[] = $arR;
			$arResult["arRID"][] = $arR["ID"]; // array of IDs of all results

			if (!$arParams["can_delete_some"])
			{
				if ($arParams["F_RIGHT"]>=20 || ($arParams["F_RIGHT"]>=15 && $arParams["USER_ID"]==$arR["USER_ID"]))
				{
					$arrRESULT_PERMISSION = CFormResult::GetPermissions($arR["ID"], $v);
					if (in_array("DELETE",$arrRESULT_PERMISSION)) $arParams["can_delete_some"] = true;
				}
			}
		}

		$rsResults = new CDBResult;
		$rsResults->InitFromArray($arResults);

		$page_split = intval(COption::GetOptionString("form", "RESULTS_PAGEN"));

		$rsResults->NavStart($page_split);
		$arResult["pager"] = $rsResults->GetNavPrint(GetMessage("FORM_PAGES"), false, 'text', false, array('formresult', 'RESULT_ID'));

		if (!$rsResults->NavShowAll)
		{
			$pagen_from = (intval($rsResults->NavPageNomer)-1)*intval($rsResults->NavPageSize);
			$arRID_tmp = array();
			if (is_array($arResult["arRID"]) && count($arResult["arRID"])>0)
			{
				$i=0;
				foreach($arResult["arRID"] as $rid)
				{
					if ($i>=$pagen_from && $i<$pagen_from+$page_split)
					{
						$arRID_tmp[] = $rid; // array of IDs of results for the page
					}
					$i++;
				}
			}
			$arResult["arRID"] = $arRID_tmp;
		}

		$arResult["arrResults"] = array();
		$arrUsers = array();
		while ($arRes = $rsResults->NavNext(false))
		{
			$arRes["arrRESULT_PERMISSION"] = CFormResult::GetPermissions($arRes["ID"], $v);

			$arRes["can_view"] = false;
			$arRes["can_edit"] = false;
			$arRes["can_delete"] = false;

			if ($arParams["F_RIGHT"]>=20 || ($arParams["F_RIGHT"]>=15 && $arParams["USER_ID"]==$arRes["USER_ID"]))
			{
				if (in_array("VIEW",$arRes["arrRESULT_PERMISSION"])) $arRes["can_view"] = true;
				if (in_array("EDIT",$arRes["arrRESULT_PERMISSION"])) $arRes["can_edit"] = true;
				if (in_array("DELETE",$arRes["arrRESULT_PERMISSION"])) $arRes["can_delete"] = true;
			}

			$arr = explode(" ",$arRes["TIMESTAMP_X"]);
			$arRes["TSX_0"] = $arr[0];
			$arRes["TSX_1"] = $arr[1];

			if ($arRes["USER_ID"]>0)
			{
				if (!in_array($arRes["USER_ID"], array_keys($arrUsers)))
				{
					$rsU = CUser::GetByID($arRes["USER_ID"]);
					$arU = $rsU->Fetch();
					$arRes["LOGIN"] = $arU["LOGIN"];
					$arRes["USER_FIRST_NAME"] = $arU["NAME"];
					$arRes["USER_LAST_NAME"] = $arU["LAST_NAME"];
					$arRes["USER_SECOND_NAME"] = $arU["SECOND_NAME"];
					$arrUsers[$arRes["USER_ID"]]["USER_FIRST_NAME"] = $arRes["USER_FIRST_NAME"];
					$arrUsers[$arRes["USER_ID"]]["USER_LAST_NAME"] = $arRes["USER_LAST_NAME"];
					$arrUsers[$arRes["USER_ID"]]["USER_SECOND_NAME"] = $arRes["USER_SECOND_NAME"];
					$arrUsers[$arRes["USER_ID"]]["LOGIN"] = $arRes["LOGIN"];
				}
				else
				{
					$arRes["USER_FIRST_NAME"] = $arrUsers[$arRes["USER_ID"]]["USER_FIRST_NAME"];
					$arRes["USER_LAST_NAME"] = $arrUsers[$arRes["USER_ID"]]["USER_LAST_NAME"];
					$arRes["USER_SECOND_NAME"] = $arrUsers[$arRes["USER_ID"]]["USER_SECOND_NAME"];
					$arRes["LOGIN"] = $arrUsers[$arRes["USER_ID"]]["LOGIN"];
				}
			}

			$arResult["arrResults"][] = $arRes;
		}

		// get columns titles
		if ($arResult["res_counter"] > 0)
		{
			$arFilter = array(
				"IN_RESULTS_TABLE"	=> "Y",
				"RESULT_ID"			=> implode(" | ", $arResult["arRID"])
				);
			CForm::GetResultAnswerArray($arParams["WEB_FORM_ID"], $arResult["arrColumns"], $arResult["arrAnswers"], $arResult["arrAnswersSID"], $arFilter);
		}
		else
		{
			$arFilter = array("IN_RESULTS_TABLE" => "Y");

			$v1="s_c_sort";
			$v2="asc";
			$v3 = false;
			$rsFields = CFormField::GetList($arParams["WEB_FORM_ID"], "ALL", $v1, $v2, $arFilter, $v3);
			while ($arField = $rsFields->Fetch())
			{
				$arResult["arrColumns"][$arField["ID"]] = $arField;
			}
		}

		if (is_array($arResult["arrAnswers"]))
		{
			foreach ($arResult["arrAnswers"] as $res_key => $arrResult)
			{
				foreach ($arrResult as $q_key => $arAnswers)
				{
					foreach ($arAnswers as $a_key => $arrA)
					{
						if (trim($arrA["USER_TEXT"]) <> '')
							$arrA["USER_TEXT"] = intval($arrA["USER_FILE_ID"])>0 ? htmlspecialcharsbx($arrA["USER_TEXT"]) : TxtToHTML($arrA["USER_TEXT"], true, 100);

						if (trim($arrA["USER_DATE"]) <> '')
						{
							$arrA["USER_TEXT"] = $DB->FormatDate($arrA["USER_DATE"], FORMAT_DATETIME, FORMAT_DATE);
						}

						if (trim($arrA["ANSWER_TEXT"]) <> '')
							$arrA["ANSWER_TEXT"] = TxtToHTML($arrA["ANSWER_TEXT"],true,100);

						if (trim($arrA["ANSWER_VALUE"]) <> '')
							$arrA["ANSWER_VALUE"] = TxtToHTML($arrA["ANSWER_VALUE"],true,100);

						if (intval($arrA["USER_FILE_ID"])>0)
						{
							if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
							{
								$arrA["USER_FILE_IMAGE_CODE"] = CFile::ShowImage($arrA["USER_FILE_ID"], 0, 0, "border=0", "", true);
							}
							else
							{
								$arrA["USER_FILE_NAME"] = htmlspecialcharsbx($arrA["USER_FILE_NAME"]);
								$arrA["USER_FILE_SIZE_TEXT"] = CFile::FormatSize($arrA["USER_FILE_SIZE"]);
							}
						}

						$arResult["arrAnswers"][$res_key][$q_key][$a_key] = $arrA;
					}
				}
			}
		}
		else
		{
			$arResult["arrAnswers"] = array();
		}

		if (!is_array($arResult["arrColumns"])) $arResult["arrColumns"] = array();

		foreach ($arResult["arrColumns"] as $key => $arrCol)
		{
			if ($arrCol["RESULTS_TABLE_TITLE"] == '')
			{
				$title = ($arrCol["TITLE_TYPE"]=="html") ? strip_tags($arrCol["TITLE"]) : htmlspecialcharsbx($arrCol["TITLE"]);
				$title = TruncateText($title,100);
			}
			else $title = htmlspecialcharsbx($arrCol["RESULTS_TABLE_TITLE"]);

			$arResult["arrColumns"][$key]["RESULTS_TABLE_TITLE"] = $title;
		}

		$arResult["filter_id"] = rand(0, 10000);
		$arResult["tf_name"] = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORM_RESULT_FILTER";
		if ($arResult["tf"] == '') $arResult["tf"] = $_REQUEST[$arResult["tf_name"]];
		if ($arResult["tf"] == '') $arResult["tf"] = "none";
		$arResult["is_ie"] = IsIE();

		$arResult["__find"] = array();
		foreach ($GLOBALS as $key => $value)
		{
			if (mb_substr($key, 0, 5) == "find_") $arResult["__find"][$key] = $value;
		}

		reset($arResult["arrFORM_FILTER"]);

		foreach ($arResult["arrFORM_FILTER"] as $f_sid => $arrF)
		{
			foreach ($arrF as $key => $arr)
			{
				if ($arrF["FILTER_TITLE"] == '')
				{
					$title = ($arrF["TITLE_TYPE"]=="html" ? strip_tags($arrF["TITLE"]) : htmlspecialcharsbx($arrF["TITLE"]));
					$arrResult["arrFORM_FILTER"][$f_sid][$key]["FILTER_TITLE"] = TruncateText($title, 100);
				}
				else
				{
					$arrResult["arrFORM_FILTER"][$f_sid][$key]["FILTER_TITLE"] = htmlspecialcharsbx($arrF["FILTER_TITLE"]);
				}
			}
		}

		$arParams["by"] = htmlspecialcharsbx($arParams["by"]);
		$arParams["order"] = htmlspecialcharsbx($arParams["order"]);

		$arResult["res_counter"] = intval($arResult["res_counter"]);

		$arrPermissions = array("MOVE", "VIEW");

		foreach($arrPermissions as $perm)
		{
			$rsStatuses = CFormStatus::GetDropdown($arParams["WEB_FORM_ID"], array($perm));
			$arResult["arStatuses_".$perm] = array();
			while ($arStatus = $rsStatuses->Fetch())
			{
				$arResult["arStatuses_".$perm][] = array("REFERENCE_ID" => htmlspecialcharsbx($arStatus["REFERENCE_ID"]), "REFERENCE" => htmlspecialcharsbx($arStatus["REFERENCE"]));
			}
		}

		$this->IncludeComponentTemplate();
	}
	else
	{
		ShowError(GetMessage("FORM_INCORRECT_FORM_ID"));
	}
}
else
{
	ShowError(GetMessage("FORM_MODULE_NOT_INSTALLED"));
}