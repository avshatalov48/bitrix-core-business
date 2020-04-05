<?
/***************************************
			Веб-форма
***************************************/

class CAllForm extends CForm_old
{
	public static function err_mess()
	{
		$module_id = "form";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllForm<br>File: ".__FILE__;
	}

	// true - если текущий пользователь имеет полный доступ к модулю
	// false - в противном случае
	public static function IsAdmin()
	{
		global $USER, $APPLICATION;
		if (!is_object($USER)) $USER = new CUser;
		if ($USER->IsAdmin()) return true;
		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if ($FORM_RIGHT>="W") return true;
	}

	// Функция возвращает массивы, содержащие данные по вопросам и полям формы, а также ответы и их значения.
	public static function GetResultAnswerArray($WEB_FORM_ID, &$arrColumns, &$arrAnswers, &$arrAnswersSID, $arFilter=Array())
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetResultAnswerArray<br>Line: ";
		global $DB, $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			if (strlen($arFilter["FIELD_SID"])>0) $arFilter["FIELD_VARNAME"] = $arFilter["FIELD_SID"];
			elseif (strlen($arFilter["FIELD_VARNAME"])>0) $arFilter["FIELD_SID"] = $arFilter["FIELD_VARNAME"];

			$filter_keys = array_keys($arFilter);
			$cntFilterKeys = count($filter_keys);
			for ($i=0; $i<$cntFilterKeys; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
				if( (strlen($val) <= 0) || ($val === "NOT_REF") )
					continue;
				}
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "FIELD_ID":
					case "RESULT_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("RA.".$key, $val, $match);
						break;
					case "FIELD_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("F.SID", $val, $match);
						break;
					case "IN_RESULTS_TABLE":
					case "IN_EXCEL_TABLE":
						$arSqlSearch[] = ($val=="Y") ? "F.".$key."='Y'" : "F.".$key."='N'";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				RA.RESULT_ID, RA.FIELD_ID, F.SID, F.SID as VARNAME, F.TITLE, F.TITLE_TYPE, F.FILTER_TITLE, F.RESULTS_TABLE_TITLE,
				RA.ANSWER_ID, RA.ANSWER_TEXT, A.MESSAGE, RA.ANSWER_VALUE, A.VALUE, RA.USER_TEXT,
				".$DB->DateToCharFunction("RA.USER_DATE")."	USER_DATE,
				RA.USER_FILE_ID, RA.USER_FILE_NAME, RA.USER_FILE_IS_IMAGE, RA.USER_FILE_HASH, RA.USER_FILE_SUFFIX, RA.USER_FILE_SIZE,
				A.FIELD_TYPE, A.FIELD_WIDTH, A.FIELD_HEIGHT, A.FIELD_PARAM
			FROM
				b_form_result_answer RA
			INNER JOIN b_form_field F ON (F.ID = RA.FIELD_ID and F.ACTIVE='Y')
			LEFT JOIN b_form_answer A ON (A.ID = RA.ANSWER_ID)
			WHERE
			$strSqlSearch
			and RA.FORM_ID = $WEB_FORM_ID
			ORDER BY RA.RESULT_ID, F.C_SORT, A.C_SORT
			";
		//echo "<pre>".$strSql."</pre>";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($zr = $z->Fetch())
		{
			$arrAnswers[$zr["RESULT_ID"]][$zr["FIELD_ID"]][intval($zr["ANSWER_ID"])]=$zr;
			$arrAnswersSID[$zr["RESULT_ID"]][$zr["SID"]][]=$zr;
		}
		$q = CFormField::GetList($WEB_FORM_ID, "", $v1, $v2,
			array(
				"ID"				=> $arFilter["FIELD_ID"],
				"VARNAME"			=> $arFilter["FIELD_SID"],
				"SID"				=> $arFilter["FIELD_SID"],
				"IN_RESULTS_TABLE"	=> $arFilter["IN_RESULTS_TABLE"],
				"IN_EXCEL_TABLE"	=> $arFilter["IN_EXCEL_TABLE"],
				"ACTIVE"			=> "Y"),
			$is_filtered
			);
		while ($qr = $q->Fetch())
		{
			$arrColumns[$qr["ID"]] = $qr;
		}
	}

	// получаем массив почтовых шаблонов связанных с формой
	public static function GetMailTemplateArray($FORM_ID)
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetMailTemplateArray<br>Line: ";
		global $DB, $USER, $strError;
		$FORM_ID = intval($FORM_ID);
		if ($FORM_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT
				FM.MAIL_TEMPLATE_ID
			FROM
				b_form_2_mail_template FM
			WHERE
				FM.FORM_ID = $FORM_ID
			";
		//echo "<pre>".$strSql."</pre>";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["MAIL_TEMPLATE_ID"];
		return $arrRes;
	}

	// получаем массив сайтов связанных с формой
	public static function GetSiteArray($FORM_ID)
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB, $USER, $strError;
		$FORM_ID = intval($FORM_ID);
		if ($FORM_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT
				FS.SITE_ID
			FROM
				b_form_2_site FS
			WHERE
				FS.FORM_ID = $FORM_ID
			";
		//echo "<pre>".$strSql."</pre>";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["SITE_ID"];
		return $arrRes;
	}

	// функция вызывает заданный обработчик до смены статуса
	public static function ExecHandlerBeforeChangeStatus($RESULT_ID, $ACTION, $NEW_STATUS_ID=0)
	{
		global $arrPREV_RESULT_STATUS, $DB, $MESS, $APPLICATION, $USER, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: ExecHandlerBeforeChangeStatus<br>Line: ";
		$RESULT_ID = intval($RESULT_ID);
		if ($RESULT_ID<=0) return;
		else
		{
			$strSql = "
				SELECT
					R.*,
					".$DB->DateToCharFunction("R.DATE_CREATE")."	DATE_CREATE,
					".$DB->DateToCharFunction("R.TIMESTAMP_X")."	TIMESTAMP_X,
					S.TITLE			STATUS_TITLE,
					S.DESCRIPTION	STATUS_DESCRIPTION,
					S.DEFAULT_VALUE	STATUS_DEFAULT_VALUE,
					S.CSS			STATUS_CSS,
					S.HANDLER_IN	STATUS_HANDLER_IN,
					S.HANDLER_OUT	STATUS_HANDLER_OUT
				FROM
					b_form_result R
				INNER JOIN b_form_status S ON (R.STATUS_ID=S.ID)
				WHERE
					R.ID = $RESULT_ID
				";
			//echo "<pre>".$strSql."</pre>";
			$rsResult = $DB->Query($strSql, false, $err_mess.__LINE__);
			if ($arResult = $rsResult->Fetch())
			{
				$arrPREV_RESULT_STATUS[$RESULT_ID] = $arResult["STATUS_ID"];
				$handler = trim($arResult["STATUS_HANDLER_OUT"]);
				if (strlen($handler)>0)
				{
					$fname = $handler;
					$fname = str_replace("\\", "/", $fname);
					$fname = str_replace("//", "/", $fname);
					$fname = TrimEx($fname,"/");
					$CURRENT_STATUS_ID = $arResult["STATUS_ID"];
					$fname = $_SERVER["DOCUMENT_ROOT"]."/".$fname;
					include($fname);
				}
			}
		}
	}

	// функция вызывает заданный обработчик после смены статуса
	public static function ExecHandlerAfterChangeStatus($RESULT_ID, $ACTION)
	{
		global $arrCURRENT_RESULT_STATUS, $arrPREV_RESULT_STATUS, $DB, $MESS, $APPLICATION, $USER, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: ExecHandlerAfterChangeStatus<br>Line: ";
		$RESULT_ID = intval($RESULT_ID);
		if ($RESULT_ID<=0) return;
		else
		{
			$strSql = "
				SELECT
					R.*,
					".$DB->DateToCharFunction("R.DATE_CREATE")."	DATE_CREATE,
					".$DB->DateToCharFunction("R.TIMESTAMP_X")."	TIMESTAMP_X,
					S.TITLE			STATUS_TITLE,
					S.DESCRIPTION	STATUS_DESCRIPTION,
					S.DEFAULT_VALUE	STATUS_DEFAULT_VALUE,
					S.CSS			STATUS_CSS,
					S.HANDLER_IN	STATUS_HANDLER_IN,
					S.HANDLER_OUT	STATUS_HANDLER_OUT
				FROM
					b_form_result R
				INNER JOIN b_form_status S ON (R.STATUS_ID=S.ID)
				WHERE
					R.ID = $RESULT_ID
				";
			//echo "<pre>".$strSql."</pre>";
			$rsResult = $DB->Query($strSql, false, $err_mess.__LINE__);
			if ($arResult = $rsResult->Fetch())
			{
				$arrCURRENT_RESULT_STATUS[$RESULT_ID] = $arResult["STATUS_ID"];
				$handler = trim($arResult["STATUS_HANDLER_IN"]);
				if (strlen($handler)>0)
				{
					$fname = $handler;
					$fname = str_replace("\\", "/", $fname);
					$fname = str_replace("//", "/", $fname);
					$fname = TrimEx($fname,"/");
					$fname = $_SERVER["DOCUMENT_ROOT"]."/".$fname;
					$CURRENT_STATUS_ID = $arResult["STATUS_ID"];
					$PREV_STATUS_ID = $arrPREV_RESULT_STATUS[$RESULT_ID];
					include($fname);
				}
			}
		}
	}

	// права на веб-форму
	public static function GetPermissionList($get_default="Y")
	{
		global $MESS, $strError;
		$ref_id = array(1,10,15,20,25,30);
		$ref = array(
			"[1] ".GetMessage("FORM_DENIED"),
			"[10] ".GetMessage("FORM_FILL"),
			"[15] ".GetMessage("FORM_FILL_EDIT"),
			"[20] ".GetMessage("FORM_VIEW"),
			"[25] ".GetMessage("FORM_VIEW_PARAMS"),
			"[30] ".GetMessage("FORM_WRITE")
			);
		$ref_id_def = array();
		$ref_def = array();
		if ($get_default=="Y")
		{
			$default_perm = COption::GetOptionString("form", "FORM_DEFAULT_PERMISSION");
			$idx = array_search($default_perm, $ref_id);
			$ref_id_def[] = 0;
			$ref_def[] = GetMessage("FORM_DEFAULT")." - ".$ref[$idx];
		}
		$arr = array(
			"reference_id" => array_merge($ref_id_def,$ref_id),
			"reference" => array_merge($ref_def, $ref));
		return $arr;
	}

	public static function GetPermission($form_id, $arGroups=false, $get_from_database="")
	{
		global $DB, $USER, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: GetPermission<br>Line: ";
		$default_right = COption::GetOptionString("form","FORM_DEFAULT_PERMISSION");
		if ($arGroups===false)
		{
			$arGroups = $USER->GetUserGroupArray();
			if (!is_array($arGroups))
				$arGroups = array(2);
		}

		if (CForm::IsAdmin() && $get_from_database!="Y") $right = 30;
		else
		{
			if (is_array($arGroups) && count($arGroups)>0)
			{
				foreach ($arGroups as $k => $g)
					$arGroups[$k] = intval($g);

				$arr = array();
				$groups = implode(',', $arGroups);
				$form_id = intval($form_id);
				$strSql = "
					SELECT
						FG.PERMISSION,
						FG.GROUP_ID
					FROM
						b_form_2_group FG
					WHERE
						FG.FORM_ID = '".$form_id."'
					and FG.GROUP_ID in (".$groups.")
					";
				//echo "<pre>".$strSql."</pre>";
				$t = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($tr = $t->Fetch())
					$arr[$tr["GROUP_ID"]] = $tr["PERMISSION"];

				if ($get_from_database!="Y")
				{
					foreach ($arGroups as $gid)
					{
						if (!array_key_exists($gid, $arr))
							$arr[$gid] = $default_right;
					}
				}

				$arr_values = is_array($arr) ? array_values($arr) : array(0);
				$right = count($arr_values)>0 ? max($arr_values) : 0;
			}
		}
		$right = intval($right);
		if ($right<=0 && $get_from_database!="Y") $right = $default_right;
		//echo "right = ".$right;
		return $right;
	}

	public static function GetTemplateList($type="SHOW", $path="xxx", $WEB_FORM_ID=0)
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetTemplateList<br>Line: ";
		global $DB, $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		if ($type!="MAIL")
		{
			if ($path=="xxx")
			{
				if ($type=="SHOW") $path = COption::GetOptionString("form", "SHOW_TEMPLATE_PATH");
				elseif ($type=="SHOW_RESULT") $path = COption::GetOptionString("form", "SHOW_RESULT_TEMPLATE_PATH");
				elseif ($type=="PRINT_RESULT") $path = COption::GetOptionString("form", "PRINT_RESULT_TEMPLATE_PATH");
				elseif ($type=="EDIT_RESULT") $path = COption::GetOptionString("form", "EDIT_RESULT_TEMPLATE_PATH");
			}
			$arr = array();
			$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
			if($handle)
			{
				while (false!==($fname = readdir($handle)))
				{
					if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$fname) && $fname!="." && $fname!="..")
					{
						$arReferenceId[] = $fname;
						$arReference[] = $fname;
					}
				}
				closedir($handle);
			}
		}
		elseif ($WEB_FORM_ID>0)
		{
			$arrSITE = array();
			$strSql = "
				SELECT
					F.MAIL_EVENT_TYPE,
					FS.SITE_ID
				FROM
					b_form F
				INNER JOIN b_form_2_site FS ON (FS.FORM_ID = F.ID)
				WHERE
					F.ID = $WEB_FORM_ID
				";
			$z = $DB->Query($strSql,false,$err_mess.__LINE__);

			$MAIL_EVENT_TYPE = '';
			$arrSITE = array();
			while ($zr = $z->Fetch())
			{
				$MAIL_EVENT_TYPE = $zr["MAIL_EVENT_TYPE"];
				$arrSITE[] = $zr["SITE_ID"];
			}

			$arReferenceId = array();
			$arReference = array();
			if (strlen($MAIL_EVENT_TYPE) > 0)
			{
				$arFilter = Array(
					"ACTIVE"		=> "Y",
					"SITE_ID"		=> $arrSITE,
					"EVENT_NAME"	=> $MAIL_EVENT_TYPE
					);
				$e = CEventMessage::GetList($by="id", $order="asc", $arFilter);
				while ($er=$e->Fetch())
				{
					if (!in_array($er["ID"], $arReferenceId))
					{
						$arReferenceId[] = $er["ID"];
						$arReference[] = "(".$er["LID"].") ".TruncateText($er["SUBJECT"],50);
					}
				}
			}
		}
		$arr = array("reference"=>$arReference,"reference_id"=>$arReferenceId);
		return $arr;
	}

	public static function GetMenuList($arFilter=Array(), $check_rights="Y")
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetMenuList<br>Line: ";
		global $DB, $USER, $strError;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			$cntFilterKeys = count($filter_keys);
			for ($i=0; $i<$cntFilterKeys; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
				if( (strlen($val) <= 0) || ($val === "NOT_REF") )
					continue;
				}
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "FORM_ID":
					case "LID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("L.".$key,$val,$match);
						break;
					case "MENU":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.MENU", $val, $match);
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($check_rights=="N" || CForm::IsAdmin())
		{
			$strSql = "
				SELECT
					F.ID,
					F.NAME,
					L.LID,
					L.MENU
				FROM
					b_form_menu L,
					b_form F
				WHERE
				$strSqlSearch
				and L.FORM_ID = F.ID
				ORDER BY F.C_SORT
				";
		}
		else
		{
			$arGroups = $USER->GetUserGroupArray();
			if (!is_array($arGroups)) $arGroups[] = 2;
			$groups = implode(",",$arGroups);
			$strSql = "
				SELECT
					F.ID,
					F.NAME,
					L.LID,
					L.MENU
				FROM
					b_form_menu L,
					b_form F,
					b_form_2_group G
				WHERE
				$strSqlSearch
				and L.FORM_ID = F.ID
				and G.FORM_ID = F.ID
				and G.GROUP_ID in ($groups)
				GROUP BY
					L.ID, L.LID, L.MENU, F.NAME, F.ID, F.C_SORT
				HAVING
					max(G.PERMISSION)>=15
				ORDER BY F.C_SORT
				";
		}
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetNextSort()
	{
		global $DB, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: GetNextSort<br>Line: ";
		$strSql = "SELECT max(C_SORT) as MAX_SORT FROM b_form";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return (intval($zr["MAX_SORT"])+100);
	}

	public static function ShowRequired($flag)
	{
		if ($flag=="Y") return "<font color='red'><span class='form-required starrequired'>*</span></font>";
	}

	public static function GetTextFilter($FID, $size="45", $field_text="class=\"inputtext\"", $field_checkbox="class=\"inputcheckbox\"")
	{
		$var = "find_".$FID;
		$var_exec_match = "find_".$FID."_exact_match";
		global ${$var}, ${$var_exec_match};
		$checked = (${$var_exec_match}=="Y") ? "checked" : "";
		return '<input '.$field_text.' type="text" name="'.$var.'" size="'.$size.'" value="'.htmlspecialcharsbx(${$var}).'"><input '.$field_checkbox.' type="checkbox" value="Y" name="'.$var.'_exact_match" title="'.GetMessage("FORM_EXACT_MATCH").'" '.$checked.'>'.ShowFilterLogicHelp();
	}

	public static function GetDateFilter($FID, $form_name="form1", $show_select="Y", $field_select="class=\"inputselect\"", $field_input="class=\"inputtext\"")
	{
		$var1 = "find_".$FID."_1";
		$var2 = "find_".$FID."_2";

		global $APPLICATION, ${$var1}, ${$var2};

		if (!defined('ADMIN_SECTION'))
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'SHOW_INPUT' => 'Y',
					'FORM_NAME' => $form_name,
					'INPUT_NAME' => $var1,
					'INPUT_NAME_FINISH' => $var2,
					'INPUT_VALUE' => ${$var1},
					'INPUT_VALUE_FINISH' => ${$var2},
					'SHOW_TIME' => 'N',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			$res .= ob_get_contents();
			ob_end_clean();

			return $res;
		}
		else
			return CalendarPeriod($var1, htmlspecialcharsbx(${$var1}), $var2, htmlspecialcharsbx(${$var2}), $form_name, $show_select, $field_select, $field_input);
	}

	public static function GetNumberFilter($FID, $size="10", $field="class=\"inputtext\"")
	{
		global $MESS;
		$var1 = "find_".$FID."_1";
		$var2 = "find_".$FID."_2";
		global ${$var1}, ${$var2};
		return '<input '.$field.' type="text" name="'.$var1.'" size="'.$size.'" value="'.htmlspecialcharsbx(${$var1}).'">&nbsp;'.GetMessage("FORM_TILL").'&nbsp;<input '.$field.' type="text" name="'.$var2.'" size="'.$size.'" value="'.htmlspecialcharsbx(${$var2}).'">';
	}

	public static function GetExistFlagFilter($FID, $field="class=\"inputcheckbox\"")
	{
		global $MESS;
		$var = "find_".$FID;
		global ${$var};
		return InputType("checkbox", $var, "Y", ${$var}, false, "", $field);
	}

	public static function GetCrmFlagFilter($FID, $field="class=\"inputselect\"")
	{
		$var = "find_".$FID;
		global ${$var};
		$arr = array("reference_id"=>array('Y', 'N'), "reference"=>array(GetMessage('MAIN_YES'), GetMessage('MAIN_NO')));
		return SelectBoxFromArray($var, $arr, ${$var}, GetMessage("FORM_ALL"), $field);
	}

	public static function GetDropDownFilter($ID, $PARAMETER_NAME, $FID, $field="class=\"inputselect\"")
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetDropDownFilter<br>Line: ";
		global $DB, $MESS, $strError;
		if ($PARAMETER_NAME=="ANSWER_VALUE") $str=", VALUE as REFERENCE"; else $str=", MESSAGE as REFERENCE";
		$ID = intval($ID);
		$strSql = "
			SELECT
				ID as REFERENCE_ID
				$str
			FROM
				b_form_answer
			WHERE
				FIELD_ID = $ID
			ORDER BY
				C_SORT
			";
		$z = $DB->Query($strSql,false,$err_mess.__LINE__);
		$ref = array();
		$ref_id = array();
		while ($zr = $z->Fetch())
		{
			if (strlen(trim($zr["REFERENCE"]))>0)
			{
				$ref[] = TruncateText($zr["REFERENCE"],70);
				$ref_id[] = $zr["REFERENCE_ID"];
			}
		}
		$arr = array("reference_id"=>$ref_id, "reference"=>$ref);
		$var = "find_".$FID;
		global ${$var};
		return SelectBoxFromArray($var, $arr, ${$var}, GetMessage("FORM_ALL"), $field);
	}

	public static function GetTextValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_text_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetHiddenValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_hidden_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetPasswordValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_password_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetEmailValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_email_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetUrlValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_url_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetTextField($FIELD_NAME, $VALUE="", $SIZE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputtext\" ";
		return "<input type=\"text\" ".$PARAM." name=\"form_text_".$FIELD_NAME."\" value=\"".htmlspecialcharsbx($VALUE)."\" size=\"".$SIZE."\" />";
	}

	public static function GetHiddenField($FIELD_NAME, $VALUE="", $PARAM="")
	{
		return "<input type=\"hidden\" ".$PARAM." name=\"form_hidden_".$FIELD_NAME."\" value=\"".htmlspecialcharsbx($VALUE)."\" />";
	}


	public static function GetEmailField($FIELD_NAME, $VALUE="", $SIZE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputtext\" ";
		return "<input type=\"text\" ".$PARAM." name=\"form_email_".$FIELD_NAME."\" value=\"".htmlspecialcharsbx($VALUE)."\" size=\"".$SIZE."\" />";
	}

	public static function GetUrlField($FIELD_NAME, $VALUE="", $SIZE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputtext\" ";
		return "<input type=\"text\" ".$PARAM." name=\"form_url_".$FIELD_NAME."\" value=\"".htmlspecialcharsbx($VALUE)."\" size=\"".$SIZE."\" />";
	}

	public static function GetPasswordField($FIELD_NAME, $VALUE="", $SIZE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputtext\" ";
		return "<input type=\"password\" ".$PARAM." name=\"form_password_".$FIELD_NAME."\" value=\"".htmlspecialcharsbx($VALUE)."\" size=\"".$SIZE."\" />";
	}

	public static function GetDropDownValue($FIELD_NAME, $arDropDown, $arrVALUES=false)
	{
		$fname = "form_dropdown_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname]))
		{
			$value = intval($arrVALUES[$fname]);
		}
		elseif (is_array($arDropDown[$FIELD_NAME]["param"]))
		{
			$c = count($arDropDown[$FIELD_NAME]["param"]);
			if ($c>0)
			{
				for ($i=0; $i<=$c-1; $i++)
				{
					if (strpos(strtolower($arDropDown[$FIELD_NAME]["param"][$i]), "selected")!==false || strpos(strtolower($arDropDown[$FIELD_NAME]["param"][$i]), "checked")!==false)
					{
						$value = $arDropDown[$FIELD_NAME]["reference_id"][$i];
						break;
					}
				}
			}
		}
		return $value;
	}

	public static function GetDropDownField($FIELD_NAME, $arDropDown, $VALUE, $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputselect\" ";
		return SelectBoxFromArray("form_dropdown_".$FIELD_NAME, $arDropDown, $VALUE, "", $PARAM);
	}

	public static function GetMultiSelectValue($FIELD_NAME, $arMultiSelect, $arrVALUES=false)
	{
		$fname = "form_multiselect_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname]))
		{
			$value=$arrVALUES[$fname];
		}
		elseif (is_array($arMultiSelect[$FIELD_NAME]["param"]))
		{
			$c = count($arMultiSelect[$FIELD_NAME]["param"]);
			if ($c>0)
			{
				for ($i=0;$i<=$c-1;$i++)
				{
					if (strpos(strtolower($arMultiSelect[$FIELD_NAME]["param"][$i]), "selected")!==false || strpos(strtolower($arMultiSelect[$FIELD_NAME]["param"][$i]), "checked")!==false)
						$value[] = $arMultiSelect[$FIELD_NAME]["reference_id"][$i];
				}
			}
		}
		return $value;
	}

	public static function GetMultiSelectField($FIELD_NAME, $arMultiSelect, $arSELECTED=array(), $HEIGHT="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputselect\" ";
		return SelectBoxMFromArray("form_multiselect_".$FIELD_NAME."[]", $arMultiSelect, $arSELECTED, "", false, $HEIGHT, $PARAM);
	}

	public static function GetDateValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_date_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else
		{
			if (preg_match("/NOW_DATE/i",$arAnswer["FIELD_PARAM"])) $value = GetTime(time(),"SHORT");
			elseif (preg_match("/NOW_TIME/i",$arAnswer["FIELD_PARAM"])) $value = GetTime(time()+CTimeZone::GetOffset(),"FULL");
			else $value = $arAnswer["VALUE"];
		}
		return $value;
	}

	public static function GetDateField($FIELD_NAME, $FORM_NAME, $VALUE="", $FIELD_WIDTH="", $PARAM="")
	{
		global $APPLICATION;
		//if (strlen($PARAM)<=0) $PARAM = " class=\"inputtext\" ";

		$rid = RandString(8);
		$res = "<input type=\"text\" ".$PARAM." name=\"form_date_".$FIELD_NAME."\" id=\"form_date_".$rid."\" value=\"".htmlspecialcharsbx($VALUE)."\" size=\"".$FIELD_WIDTH."\" />";

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'SHOW_INPUT' => 'N',
				'FORM_NAME' => $FORM_NAME,
				'INPUT_NAME' => "form_date_".$rid,
				'SHOW_TIME' => 'N',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$res .= ob_get_contents();
		ob_end_clean();

		return $res;

		//return CalendarDate("form_date_".$FIELD_NAME, $VALUE, $FORM_NAME, $FIELD_WIDTH, $PARAM);
	}

	public static function GetCheckBoxValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_checkbox_".$FIELD_NAME;

		if (is_array($arrVALUES))
		{
			if(isset($arrVALUES[$fname]))
			{
				$arr = $arrVALUES[$fname];
				if (is_array($arr) && in_array($arAnswer["ID"],$arr))
				{
					$value = $arAnswer["ID"];
				}
			}
		}
		else
		{
			if ($value<=0)
			{
				if (strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false || strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
				{
					$value = $arAnswer["ID"];
				}
			}
		}

		return $value;
	}

	public static function GetCheckBoxField($FIELD_NAME, $FIELD_ID, $VALUE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputcheckbox\" ";
		return InputType("checkbox", "form_checkbox_".$FIELD_NAME."[]", $FIELD_ID, $VALUE, false, "", $PARAM);
	}

	public static function GetRadioValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_radio_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname]))
		{
			$value = intval($arrVALUES[$fname]);
		}
		else
		{
			if (strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false || strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
				$value = $arAnswer["ID"];
		}
		return $value;
	}

	public static function GetRadioField($FIELD_NAME, $FIELD_ID, $VALUE="", $PARAM="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputradio\" ";

		return InputType("radio", "form_radio_".$FIELD_NAME, $FIELD_ID, $VALUE, false, "", $PARAM);
	}

	public static function GetTextAreaValue($FIELD_NAME, $arAnswer, $arrVALUES=false)
	{
		$fname = "form_textarea_".$FIELD_NAME;
		if (is_array($arrVALUES) && isset($arrVALUES[$fname])) $value = $arrVALUES[$fname];
		else $value = $arAnswer["VALUE"];
		return $value;
	}

	public static function GetTextAreaField($FIELD_NAME, $WIDTH="", $HEIGHT="", $PARAM="", $VALUE="")
	{
		if (strlen($PARAM)<=0) $PARAM = " class=\"inputtextarea\" ";
		return "<textarea name=\"form_textarea_".$FIELD_NAME."\" cols=\"".$WIDTH."\" rows=\"".$HEIGHT."\" ".$PARAM.">".htmlspecialcharsbx($VALUE)."</textarea>";
	}

	public static function GetFileField($FIELD_NAME, $WIDTH="", $FILE_TYPE="IMAGE", $MAX_FILE_SIZE=0, $VALUE="", $PARAM_FILE="", $PARAM_CHECKBOX="")
	{
		global $USER;
		if (!is_object($USER)) $USER = new CUser;
		if (strlen($PARAM_FILE)<=0) $PARAM_FILE = " class=\"inputfile\" ";
		if (strlen($PARAM_CHECKBOX)<=0) $PARAM_CHECKBOX = " class=\"inputcheckbox\" ";
		$show_notes = (strtoupper($FILE_TYPE)=="IMAGE" || $USER->isAdmin()) ? true : false;
		return CFile::InputFile("form_".strtolower($FILE_TYPE)."_".$FIELD_NAME, $WIDTH, $VALUE, false, $MAX_FILE_SIZE, $FILE_TYPE, $PARAM_FILE, 0, "", $PARAM_CHECKBOX, $show_notes);
	}

	// возвращает массивы описывающие поля и вопросы формы
	public static function GetDataByID($WEB_FORM_ID, &$arForm, &$arQuestions, &$arAnswers, &$arDropDown, &$arMultiSelect, $additional="N", $active="N")
	{
		global $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$arForm = array();
		$arQuestions = array();
		$arAnswers = array();
		$arDropDown = array();
		$arMultiSelect = array();
		$z = CForm::GetByID($WEB_FORM_ID);
		if ($arForm = $z->Fetch())
		{
			if (!is_set($arForm, "FORM_TEMPLATE")) $arForm["FORM_TEMPLATE"] = CForm::GetFormTemplateByID($WEB_FORM_ID);

			$u = CFormField::GetList($WEB_FORM_ID, $additional, ($by="s_c_sort"), ($order="asc"), $active == "N" ? array("ACTIVE"=>"Y") : array(), $is_filtered);
			while ($ur=$u->Fetch())
			{
				$arQuestions[$ur["SID"]] = $ur;
				$w = CFormAnswer::GetList($ur["ID"], ($by="s_c_sort"), ($order="asc"), $active == "N" ? array("ACTIVE"=>"Y") : array(), $is_filtered);
				while ($wr=$w->Fetch()) $arAnswers[$ur["SID"]][] = $wr;
			}

			// собираем по каждому вопросу все dropdown и multiselect в отдельные массивы
			if (is_array($arQuestions) && is_array($arAnswers))
			{
				foreach ($arQuestions as $arQ)
				{
					$QUESTION_ID = $arQ["SID"];
					$arDropReference = array();
					$arDropReferenceID = array();
					$arDropParam = array();
					$arMultiReference = array();
					$arMultiReferenceID = array();
					$arMultiParam = array();
					if (is_array($arAnswers[$QUESTION_ID]))
					{
						foreach ($arAnswers[$QUESTION_ID] as $arA)
						{
							switch ($arA["FIELD_TYPE"])
							{
								case "dropdown":
									$arDropReference[] = $arA["MESSAGE"];
									$arDropReferenceID[] = $arA["ID"];
									$arDropParam[] = $arA["FIELD_PARAM"];
									break;
								case "multiselect":
									$arMultiReference[] = $arA["MESSAGE"];
									$arMultiReferenceID[] = $arA["ID"];
									$arMultiParam[] = $arA["FIELD_PARAM"];
									break;
							}
						}
					}
					if (count($arDropReference)>0)
						$arDropDown[$QUESTION_ID] = array("reference"=>$arDropReference, "reference_id"=>$arDropReferenceID, "param" => $arDropParam);
					if (count($arMultiReference)>0)
						$arMultiSelect[$QUESTION_ID] = array("reference"=>$arMultiReference, "reference_id"=>$arMultiReferenceID, "param" => $arMultiParam);
				}
			}

			reset($arForm);
			reset($arQuestions);
			reset($arAnswers);
			reset($arDropDown);
			reset($arMultiSelect);

			return $arForm["ID"];
		}
		else return false;

	}

	public static function __check_PushError(&$container, $MESSAGE, $key = false)
	{
		if (is_array($container))
		{
			if ($key !== false) $container[$key] = $MESSAGE;
			else $container[] = $MESSAGE;
		}
		else $container .= (strlen($container) > 0 ? "<br />" : "").$MESSAGE;
	}

	// check form field values for required fields, date format validation, file type validation, additional validators
	public static function Check($WEB_FORM_ID, $arrVALUES=false, $RESULT_ID=false, $CHECK_RIGHTS="Y", $RETURN_ARRAY="N")
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: Check<br>Line: ";
		global $DB, $APPLICATION, $USER;
		if ($arrVALUES===false) $arrVALUES = $_REQUEST;

		$RESULT_ID = intval($RESULT_ID);

		$errors = $RETURN_ARRAY == "Y" ? array() : "";

		$WEB_FORM_ID = intval($WEB_FORM_ID);
		if ($WEB_FORM_ID>0)
		{
			// получаем данные по форме
			$WEB_FORM_ID = CForm::GetDataByID($WEB_FORM_ID, $arForm, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, "ALL");
			$WEB_FORM_ID = intval($WEB_FORM_ID);
			if ($WEB_FORM_ID>0)
			{
				// проверяем права
				$F_RIGHT = ($CHECK_RIGHTS=="Y") ? CForm::GetPermission($WEB_FORM_ID) : 30;

				if ($F_RIGHT<10) CForm::__check_PushError($errors, GetMessage("FORM_ACCESS_DENIED_FOR_FORM_WRITE"));
				else
				{
					$NOT_ANSWER = "NOT_ANSWER";
					// проходим по вопросам
					foreach ($arQuestions as $key => $arQuestion)
					{
						$arAnswerValues = array();

						$FIELD_ID = $arQuestion["ID"];
						if ($arQuestion["TITLE_TYPE"]=="html")
						{
							$FIELD_TITLE = strip_tags($arQuestion["TITLE"]);
						}
						else
						{
							$FIELD_TITLE = $arQuestion["TITLE"];
						}

						if ($arQuestion["ADDITIONAL"]!="Y")
						{
							// проверяем вопросы формы
							$FIELD_SID = $arQuestion["SID"];
							$FIELD_REQUIRED = $arQuestion["REQUIRED"];

							// массив полей: N - поле не отвечено; Y - поле отвечено;
							if ($FIELD_REQUIRED=="Y") $REQUIRED_FIELDS[$FIELD_SID] = "N";

							$startType = "";
							$bCheckValidators = true;
							// проходим по ответам
							if (is_array($arAnswers[$FIELD_SID]))
							{
								foreach ($arAnswers[$FIELD_SID] as $key => $arAnswer)
								{
									$ANSWER_ID = 0;
									$FIELD_TYPE = $arAnswer["FIELD_TYPE"];
									$FIELD_PARAM = $arAnswer["FIELD_PARAM"];

									if ($startType == "")
										$startType = $FIELD_TYPE;
									else
										$bCheckValidators &= $startType == $FIELD_TYPE;

									switch ($FIELD_TYPE) :

										case "radio":
										case "dropdown":

											$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
											$arAnswerValues[] = $arrVALUES[$fname];
											$ANSWER_ID = intval($arrVALUES[$fname]);
											if ($ANSWER_ID>0 && $ANSWER_ID==$arAnswer["ID"])
											{
												if ($FIELD_REQUIRED=="Y" && !preg_match("/".$NOT_ANSWER."/i", $FIELD_PARAM))
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
												}
											}

										break;

										case "checkbox":
										case "multiselect":

											$fname = "form_".$FIELD_TYPE."_".$FIELD_SID;
											if (is_array($arrVALUES[$fname]) && count($arrVALUES[$fname])>0)
											{
												$arAnswerValues = $arrVALUES[$fname];
												reset($arrVALUES[$fname]);
												foreach($arrVALUES[$fname] as $ANSWER_ID)
												{
													$ANSWER_ID = intval($ANSWER_ID);
													if ($ANSWER_ID>0 && $ANSWER_ID==$arAnswer["ID"])
													{
														if ($FIELD_REQUIRED=="Y" && !preg_match("/".$NOT_ANSWER."/i", $FIELD_PARAM))
														{
															$REQUIRED_FIELDS[$FIELD_SID] = "Y";
															break;
														}
													}
												}
											}

										break;

										case "text":
										case "textarea":
										case "password":
										case "hidden":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$USER_TEXT = $arrVALUES[$fname];
											$arAnswerValues[] = $arrVALUES[$fname];
											if (strlen(trim($USER_TEXT))>0)
											{
												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}
										break;

										case "url":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$arAnswerValues[] = $arrVALUES[$fname];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$USER_TEXT = $arrVALUES[$fname];
											if (strlen($USER_TEXT)>0)
											{
												if (!preg_match("/^(http|https|ftp):\/\//i",$USER_TEXT))
												{
													CForm::__check_PushError($errors, GetMessage('FORM_ERROR_BAD_URL'), $FIELD_SID);
												}
												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}

										break;

										case "email":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$arAnswerValues[] = $arrVALUES[$fname];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$USER_TEXT = $arrVALUES[$fname];
											if (strlen($USER_TEXT)>0)
											{
												if (!check_email($USER_TEXT))
												{
													CForm::__check_PushError($errors, GetMessage('FORM_ERROR_BAD_EMAIL'), $FIELD_SID);
												}
												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}

										break;

										case "date":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$arAnswerValues[] = $arrVALUES[$fname];
											$USER_DATE = $arrVALUES[$fname];
											if (strlen($USER_DATE)>0)
											{
												if (!CheckDateTime($USER_DATE))
												{
													CForm::__check_PushError(
														$errors,
														str_replace("#FIELD_NAME#", $FIELD_TITLE, GetMessage("FORM_INCORRECT_DATE_FORMAT")),
														$FIELD_SID
													);
												}
												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}
											break;

										case "image":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$fname_del = $arrVALUES["form_".$FIELD_TYPE."_".$arAnswer["ID"]."_del"];
											$ANSWER_ID = intval($arAnswer["ID"]);
											$arIMAGE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
											if (is_array($arIMAGE) && strlen($arIMAGE["tmp_name"])>0)
											{
												$arIMAGE["MODULE_ID"] = "form";
												if (strlen(CFile::CheckImageFile($arIMAGE))>0)
												{
													CForm::__check_PushError(
														$errors,
														str_replace("#FIELD_NAME#", $FIELD_TITLE, GetMessage("FORM_INCORRECT_FILE_TYPE")),
														$FIELD_SID

													);
												}
												else
												{
													$arAnswerValues[] = $arIMAGE;
												}

												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}
											elseif ($RESULT_ID>0 && $fname_del!="Y")
											{
												$REQUIRED_FIELDS[$FIELD_SID] = "Y";
												break;
											}

										break;

										case "file":

											$fname = "form_".$FIELD_TYPE."_".$arAnswer["ID"];
											$fname_del = $arrVALUES["form_".$FIELD_TYPE."_".$arAnswer["ID"]."_del"];
											$arFILE = isset($arrVALUES[$fname]) ? $arrVALUES[$fname] : $_FILES[$fname];
											if (is_array($arFILE) && strlen($arFILE["tmp_name"])>0)
											{
												$arAnswerValues[] = $arFILE;
												if ($FIELD_REQUIRED=="Y")
												{
													$REQUIRED_FIELDS[$FIELD_SID] = "Y";
													break;
												}
											}
											elseif ($RESULT_ID>0 && $fname_del!="Y")
											{
												$REQUIRED_FIELDS[$FIELD_SID] = "Y";
												break;
											}

										break;

									endswitch;
								}
							}
						}
						else // проверяем дополнительные поля
						{
							$FIELD_TYPE = $arQuestion["FIELD_TYPE"];

							$fname = "form_date_ADDITIONAL_".$arQuestion["ID"];
							$arAnswerValues = array($arrVALUES[$fname]);

							$bCheckValidators = true;
							switch ($FIELD_TYPE) :

								case "date":

									$USER_DATE = $arrVALUES[$fname];
									if (strlen($USER_DATE)>0)
									{
										if (!CheckDateTime($USER_DATE))
										{
											CForm::__check_PushError(
												$errors,
												str_replace("#FIELD_NAME#", $FIELD_TITLE, GetMessage("FORM_INCORRECT_DATE_FORMAT")),
												$FIELD_SID
											);
										}
									}
								break;

							endswitch;
						}

						// check custom validators
						if ($bCheckValidators)
						{
							if ($arQuestion["ADDITIONAL"] == "Y" || is_array($arAnswers[$FIELD_SID]))
							{
								$rsValidatorList = CFormValidator::GetList($FIELD_ID, array("TYPE" => $FIELD_TYPE), $by="C_SORT", $order="ASC");
								while ($arValidator = $rsValidatorList->Fetch())
								{
									if (!CFormValidator::Execute($arValidator, $arQuestion, $arAnswers[$FIELD_SID], $arAnswerValues))
									{
										if ($e = $APPLICATION->GetException())
										{
											CForm::__check_PushError($errors, str_replace("#FIELD_NAME#", $FIELD_TITLE, $e->GetString()), $FIELD_SID);
										}
									}
								}
							}
						}
					}

					if (($arForm["USE_CAPTCHA"] == "Y" && !$RESULT_ID && !defined('ADMIN_SECTION')))
					{
						if (!($GLOBALS["APPLICATION"]->CaptchaCheckCode($arrVALUES["captcha_word"], $arrVALUES["captcha_sid"])))
						{
							CForm::__check_PushError($errors, GetMessage("FORM_WRONG_CAPTCHA"));
						}
					}

					if (is_array($REQUIRED_FIELDS) && count($REQUIRED_FIELDS)>0)
					{
						foreach ($REQUIRED_FIELDS as $key => $value)
						{
							if ($value == "N")
							{
								if (strlen($arQuestions[$key]["RESULTS_TABLE_TITLE"])>0)
								{
									$title = $arQuestions[$key]["RESULTS_TABLE_TITLE"];
								}
								/*elseif (strlen($arQuestions[$key]["FILTER_TITLE"])>0)
								{
									$title = TrimEx($arQuestions[$key]["FILTER_TITLE"],":");
								}*/
								else
								{
									$title = ($arQuestions[$key]["TITLE_TYPE"]=="html") ? strip_tags($arQuestions[$key]["TITLE"]) : $arQuestions[$key]["TITLE"];
								}
								if ($RETURN_ARRAY == 'N')
									$EMPTY_REQUIRED_NAMES[] = $title;
								else
									CForm::__check_PushError($errors, GetMessage("FORM_EMPTY_REQUIRED_FIELDS").' '.$title, $key);
							}
						}
					}

					if ($RETURN_ARRAY == 'N')
					{
						if (is_array($EMPTY_REQUIRED_NAMES) && count($EMPTY_REQUIRED_NAMES)>0)
						{
							$errMsg = "";
							$errMsg .= GetMessage("FORM_EMPTY_REQUIRED_FIELDS")."<br />";
							foreach ($EMPTY_REQUIRED_NAMES as $key => $name) $errMsg .= ($key != 0 ? "<br />" : "")."&nbsp;&nbsp;&raquo;&nbsp;\"".$name."\"";
							CForm::__check_PushError($errors, $errMsg);
						}
					}
				}
			}
			else CForm::__check_PushError($errors, GetMessage("FORM_INCORRECT_FORM_ID"));
		}
		return $errors;
	}

	// проверка формы
	public static function CheckFields($arFields, $FORM_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: CheckFields<br>Line: ";
		global $DB, $strError, $APPLICATION, $USER;
		$str = "";
		$FORM_ID = intval($FORM_ID);
		$RIGHT_OK = "N";
		if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin()) $RIGHT_OK = "Y";
		else
		{
			if ($FORM_ID>0)
			{
				$F_RIGHT = CForm::GetPermission($FORM_ID);
				if ($F_RIGHT>=30) $RIGHT_OK = "Y";
			}
		}

		if ($RIGHT_OK=="Y")
		{

			if (strlen($arFields["SID"])>0) $arFields["VARNAME"] = $arFields["SID"];
			elseif (strlen($arFields["VARNAME"])>0) $arFields["SID"] = $arFields["VARNAME"];

			if ($FORM_ID<=0 || ($FORM_ID>0 && is_set($arFields, "NAME")))
			{
				if (strlen(trim($arFields["NAME"]))<=0) $str .= GetMessage("FORM_ERROR_FORGOT_NAME")."<br>";
			}

			if ($FORM_ID<=0 || ($FORM_ID>0 && is_set($arFields, "SID")))
			{
				if (strlen(trim($arFields["SID"]))<=0) $str .= GetMessage("FORM_ERROR_FORGOT_SID")."<br>";
				if (preg_match("/[^A-Za-z_01-9]/",$arFields["SID"])) $str .= GetMessage("FORM_ERROR_INCORRECT_SID")."<br>";
				else
				{
					$strSql = "SELECT ID FROM b_form WHERE SID='".$DB->ForSql(trim($arFields["SID"]),50)."' and ID<>'$FORM_ID'";
					$z = $DB->Query($strSql, false, $err_mess.__LINE__);
					if ($zr = $z->Fetch())
					{
						$s = str_replace("#TYPE#", GetMessage("FORM_TYPE_FORM"), GetMessage("FORM_ERROR_WRONG_SID"));
						$s = str_replace("#ID#",$zr["ID"],$s);
						$str .= $s."<br>";
					}
					else
					{
						$strSql = "SELECT ID, ADDITIONAL FROM b_form_field WHERE SID='".$DB->ForSql(trim($arFields["SID"]),50)."'";
						$z = $DB->Query($strSql, false, $err_mess.__LINE__);
						if ($zr = $z->Fetch())
						{
							$s = ($zr["ADDITIONAL"]=="Y") ?
								str_replace("#TYPE#", GetMessage("FORM_TYPE_FIELD"), GetMessage("FORM_ERROR_WRONG_SID")) :
								str_replace("#TYPE#", GetMessage("FORM_TYPE_QUESTION"), GetMessage("FORM_ERROR_WRONG_SID"));

							$s = str_replace("#ID#",$zr["ID"],$s);
							$str .= $s."<br>";
						}
					}
				}
			}
			$str .= CFile::CheckImageFile($arFields["arIMAGE"]);
		}
		else $str .= GetMessage("FORM_ERROR_ACCESS_DENIED");

		$strError .= $str;
		if (strlen($str)>0) return false; else return true;
	}

	// добавление/обновление формы
	public static function Set($arFields, $FORM_ID=false, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER, $strError, $APPLICATION;
		$FORM_ID = intval($FORM_ID);
		if (CForm::CheckFields($arFields, $FORM_ID, $CHECK_RIGHTS))
		{
			$arFields_i = array();

			if (strlen(trim($arFields["SID"]))>0) $arFields["VARNAME"] = $arFields["SID"];
			elseif (strlen($arFields["VARNAME"])>0) $arFields["SID"] = $arFields["VARNAME"];

			//$arFields_i["TIMESTAMP_X"] = $DB->GetNowFunction();
			$arFields_i["TIMESTAMP_X"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());

			if (is_set($arFields, "NAME"))
				$arFields_i["NAME"] = $arFields['NAME'];//"'".$DB->ForSql($arFields["NAME"],255)."'";

			if (is_set($arFields, "SID"))
				$arFields_i["SID"] = $arFields['SID'];//"'".$DB->ForSql($arFields["SID"],255)."'";

			if (is_set($arFields, "DESCRIPTION"))
				$arFields_i["DESCRIPTION"] = $arFields['DESCRIPTION'];//"'".$DB->ForSql($arFields["DESCRIPTION"],2000)."'";

			if (is_set($arFields, "C_SORT"))
				$arFields_i["C_SORT"] = intval($arFields["C_SORT"]);//"'".intval($arFields["C_SORT"])."'";

			if (is_array($arrSITE))
			{
				reset($arrSITE);
				list($k, $arFields["FIRST_SITE_ID"]) = each($arrSITE);
			}

			if (is_set($arFields, "BUTTON"))
				$arFields_i["BUTTON"] = $arFields['BUTTON']; //"'".$DB->ForSql($arFields["BUTTON"],255)."'";

			if (is_set($arFields, "USE_CAPTCHA"))
				$arFields_i["USE_CAPTCHA"] = $arFields["USE_CAPTCHA"] == "Y" ? "Y" : "N";// "'Y'" : "'N'";

			if (is_set($arFields, "DESCRIPTION_TYPE"))
				$arFields_i["DESCRIPTION_TYPE"] = ($arFields["DESCRIPTION_TYPE"]=="html") ? "html" : "text";//"'html'" : "'text'";

			if (is_set($arFields, "FORM_TEMPLATE"))
				$arFields_i["FORM_TEMPLATE"] = $arFields['FORM_TEMPLATE'];//"'".$DB->ForSql($arFields["FORM_TEMPLATE"])."'";

			if (is_set($arFields, "USE_DEFAULT_TEMPLATE"))
				$arFields_i["USE_DEFAULT_TEMPLATE"] = $arFields["USE_DEFAULT_TEMPLATE"] == "Y" ? "Y" : "N";//"'Y'" : "'N'";

			if (is_set($arFields, "SHOW_TEMPLATE"))
				$arFields_i["SHOW_TEMPLATE"] = $arFields['SHOW_TEMPLATE'];//"'".$DB->ForSql($arFields["SHOW_TEMPLATE"],255)."'";

			if (is_set($arFields, "SHOW_RESULT_TEMPLATE"))
				$arFields_i["SHOW_RESULT_TEMPLATE"] = $arFields['SHOW_RESULT_TEMPLATE']; //"'".$DB->ForSql($arFields["SHOW_RESULT_TEMPLATE"],255)."'";

			if (is_set($arFields, "PRINT_RESULT_TEMPLATE"))
				$arFields_i["PRINT_RESULT_TEMPLATE"] = $arFields['PRINT_RESULT_TEMPLATE'];//"'".$DB->ForSql($arFields["PRINT_RESULT_TEMPLATE"],255)."'";

			if (is_set($arFields, "EDIT_RESULT_TEMPLATE"))
				$arFields_i["EDIT_RESULT_TEMPLATE"] = $arFields['EDIT_RESULT_TEMPLATE'];//"'".$DB->ForSql($arFields["EDIT_RESULT_TEMPLATE"],255)."'";

			if (is_set($arFields, "FILTER_RESULT_TEMPLATE"))
				$arFields_i["FILTER_RESULT_TEMPLATE"] = $arFields['FILTER_RESULT_TEMPLATE']; //"'".$DB->ForSql($arFields["FILTER_RESULT_TEMPLATE"],255)."'";

			if (is_set($arFields, "TABLE_RESULT_TEMPLATE"))
				$arFields_i["TABLE_RESULT_TEMPLATE"] = $arFields['TABLE_RESULT_TEMPLATE']; //"'".$DB->ForSql($arFields["TABLE_RESULT_TEMPLATE"],255)."'";

			if (is_set($arFields, "USE_RESTRICTIONS"))
				$arFields_i["USE_RESTRICTIONS"] = $arFields["USE_RESTRICTIONS"] == "Y" ? "Y" : "N";//"'Y'" : "'N'";

			if (is_set($arFields, "RESTRICT_USER"))
				$arFields_i["RESTRICT_USER"] = intval($arFields["RESTRICT_USER"]);//"'".intval($arFields["RESTRICT_USER"])."'";

			if (is_set($arFields, "RESTRICT_TIME"))
				$arFields_i["RESTRICT_TIME"] = intval($arFields["RESTRICT_TIME"]);//"'".intval($arFields["RESTRICT_TIME"])."'";

			if (is_set($arFields, "arRESTRICT_STATUS"))
				$arFields_i["RESTRICT_STATUS"] = implode(",", $arFields["arRESTRICT_STATUS"]);//"'".$DB->ForSql(implode(",", $arFields["arRESTRICT_STATUS"]))."'";

			if (is_set($arFields, "STAT_EVENT1"))
				$arFields_i["STAT_EVENT1"] = $arFields['STAT_EVENT1']; //"'".$DB->ForSql($arFields["STAT_EVENT1"],255)."'";

			if (is_set($arFields, "STAT_EVENT2"))
				$arFields_i["STAT_EVENT2"] = $arFields['STAT_EVENT2']; //"'".$DB->ForSql($arFields["STAT_EVENT2"],255)."'";

			if (is_set($arFields, "STAT_EVENT3"))
				$arFields_i["STAT_EVENT3"] = $arFields['STAT_EVENT3']; //"'".$DB->ForSql($arFields["STAT_EVENT3"],255)."'";

			if (CForm::IsOldVersion()!="Y")
			{
				unset($arFields_i["SHOW_TEMPLATE"]);
				unset($arFields_i["SHOW_RESULT_TEMPLATE"]);
				unset($arFields_i["PRINT_RESULT_TEMPLATE"]);
				unset($arFields_i["EDIT_RESULT_TEMPLATE"]);
			}

			$z = $DB->Query("SELECT IMAGE_ID, SID, SID as VARNAME FROM b_form WHERE ID='".$FORM_ID."'", false, $err_mess.__LINE__);
			$zr = $z->Fetch();
			$oldSID = $zr["SID"];
			if (strlen($arFields["arIMAGE"]["name"])>0 || strlen($arFields["arIMAGE"]["del"])>0)
			{
				if(intval($zr["IMAGE_ID"]) > 0)
					$arFields["arIMAGE"]["old_file"] = $zr["IMAGE_ID"];

				if (!array_key_exists("MODULE_ID", $arFields["arIMAGE"]) || strlen($arFields["arIMAGE"]["MODULE_ID"]) <= 0)
					$arFields["arIMAGE"]["MODULE_ID"] = "form";

				$fid = CFile::SaveFile($arFields["arIMAGE"], "form");
				if (intval($fid)>0)	$arFields_i["IMAGE_ID"] = intval($fid);
				else $arFields_i["IMAGE_ID"] = "null";
			}

			if ($arFields['SID'])
				$arFields_i["MAIL_EVENT_TYPE"] = "FORM_FILLING_".$arFields["SID"];
			else
				$arFields_i["MAIL_EVENT_TYPE"] = "FORM_FILLING_".$oldSID;

			if ($FORM_ID>0)
			{
				$strUpdate = $DB->PrepareUpdate('b_form', $arFields_i);
				if ($strUpdate != '')
				{
					$query = 'UPDATE b_form SET '.$strUpdate." WHERE ID='".$FORM_ID."'";
					$arBinds = array('FORM_TEMPLATE' => $arFields_i['FORM_TEMPLATE']);
					$DB->QueryBind($query, $arBinds);
				}

				//$DB->Update("b_form", $arFields_i, "WHERE ID='".$FORM_ID."'", $err_mess.__LINE__);
				CForm::SetMailTemplate($FORM_ID, "N", $oldSID);
			}
			else
			{
				//$FORM_ID = $DB->Insert("b_form", $arFields_i, $err_mess.__LINE__);
				$FORM_ID = $DB->Add("b_form", $arFields_i, array('FORM_TEMPLATE'));
				CForm::SetMailTemplate($FORM_ID, "N");
			}
			$FORM_ID = intval($FORM_ID);

			if ($FORM_ID>0)
			{
				// сайты
				if (is_set($arFields, "arSITE"))
				{
					$DB->Query("DELETE FROM b_form_2_site WHERE FORM_ID='".$FORM_ID."'", false, $err_mess.__LINE__);
					if (is_array($arFields["arSITE"]))
					{
						reset($arFields["arSITE"]);
						foreach($arFields["arSITE"] as $sid)
						{
							$strSql = "
								INSERT INTO b_form_2_site (FORM_ID, SITE_ID) VALUES (
									$FORM_ID,
									'".$DB->ForSql($sid,2)."'
								)
								";
							$DB->Query($strSql, false, $err_mess.__LINE__);
						}
					}
				}

				// меню
				if (is_set($arFields, "arMENU"))
				{
					$DB->Query("DELETE FROM b_form_menu WHERE FORM_ID='".$FORM_ID."'", false, $err_mess.__LINE__);
					if (is_array($arFields["arMENU"]))
					{
						reset($arFields["arMENU"]);
						while(list($lid,$menu)=each($arFields["arMENU"]))
						{
							$arFields_i = array(
								"FORM_ID"	=> $FORM_ID,
								"LID"		=> "'".$DB->ForSql($lid,2)."'",
								"MENU"		=> "'".$DB->ForSql($menu,50)."'"
								);

							$DB->Insert("b_form_menu", $arFields_i, $err_mess.__LINE__);
						}
					}
				}

				// почтовые шаблоны
				if (is_set($arFields, "arMAIL_TEMPLATE"))
				{
					$DB->Query("DELETE FROM b_form_2_mail_template WHERE FORM_ID='".$FORM_ID."'", false, $err_mess.__LINE__);
					if (is_array($arFields["arMAIL_TEMPLATE"]))
					{
						reset($arFields["arMAIL_TEMPLATE"]);
						foreach($arFields["arMAIL_TEMPLATE"] as $mid)
						{
							$strSql = "
								INSERT INTO b_form_2_mail_template (FORM_ID, MAIL_TEMPLATE_ID) VALUES (
									$FORM_ID,
									'".intval($mid)."'
								)
								";
							$DB->Query($strSql, false, $err_mess.__LINE__);
						}
					}
				}

				// группы
				if (is_set($arFields, "arGROUP"))
				{
					$DB->Query("DELETE FROM b_form_2_group WHERE FORM_ID='".$FORM_ID."'", false, $err_mess.__LINE__);
					if (is_array($arFields["arGROUP"]))
					{
						reset($arFields["arGROUP"]);
						while(list($group_id,$perm)=each($arFields["arGROUP"]))
						{
							if (intval($perm)>0)
							{
								$arFields_i = array(
									"FORM_ID"		=> $FORM_ID,
									"GROUP_ID"		=> "'".intval($group_id)."'",
									"PERMISSION"	=> "'".intval($perm)."'"
									);
								$DB->Insert("b_form_2_group", $arFields_i, $err_mess.__LINE__);
							}
						}
					}
				}
			}
			return $FORM_ID;
		}
		return false;
	}

	// копирует веб-форму
	public static function Copy($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $APPLICATION, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: Copy<br>Line: ";
		$ID = intval($ID);
		if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin())
		{
			$rsForm = CForm::GetByID($ID);
			$arForm = $rsForm->Fetch();
			if (!is_set($arForm, "FORM_TEMPLATE")) $arForm["FORM_TEMPLATE"] = CForm::GetFormTemplateByID($ID);

			// символьный код формы
			while(true)
			{
				$SID = $arForm["SID"];
				if (strlen($SID) > 25) $SID = substr($SID, 0, 25);
				$SID .= "_".RandString(5);

				$strSql = "SELECT 'x' FROM b_form WHERE SID='".$DB->ForSql($SID,50)."'";
				$z = $DB->Query($strSql, false, $err_mess.__LINE__);
				if (!($zr = $z->Fetch())) break;
			}

			$arFields = array(
				"NAME"						=> $arForm["NAME"],
				"SID"						=> $SID,
				"C_SORT"					=> $arForm["C_SORT"],
				"FIRST_SITE_ID"				=> $arForm["FIRST_SITE_ID"],
				"BUTTON"					=> $arForm["BUTTON"],
				"USE_CAPTCHA"				=> $arForm["USE_CAPTCHA"],
				"DESCRIPTION"				=> $arForm["DESCRIPTION"],
				"DESCRIPTION_TYPE"			=> $arForm["DESCRIPTION_TYPE"],
				"SHOW_TEMPLATE"				=> $arForm["SHOW_TEMPLATE"],
				"FORM_TEMPLATE"				=> $arForm["FORM_TEMPLATE"],
				"USE_DEFAULT_TEMPLATE"		=> $arForm["USE_DEFAULT_TEMPLATE"],
				"SHOW_RESULT_TEMPLATE"		=> $arForm["SHOW_RESULT_TEMPLATE"],
				"PRINT_RESULT_TEMPLATE"		=> $arForm["PRINT_RESULT_TEMPLATE"],
				"EDIT_RESULT_TEMPLATE"		=> $arForm["EDIT_RESULT_TEMPLATE"],
				"FILTER_RESULT_TEMPLATE"	=> $arForm["FILTER_RESULT_TEMPLATE"],
				"TABLE_RESULT_TEMPLATE"		=> $arForm["TABLE_RESULT_TEMPLATE"],
				"STAT_EVENT1"				=> $arForm["STAT_EVENT1"],
				"STAT_EVENT2"				=> $SID,
				"STAT_EVENT3"				=> $arForm["STAT_EVENT3"],
				"arSITE"					=> CForm::GetSiteArray($ID)
				);
			// пункты меню
			$z = CForm::GetMenuList(array("FORM_ID"=>$ID), "N");
			while ($zr = $z->Fetch()) $arFields["arMENU"][$zr["LID"]] = $zr["MENU"];

			// права групп
			$w = CGroup::GetList($v1="dropdown", $v2="asc", Array("ADMIN"=>"N"), $v3);
			$arGroups = array();
			while ($wr=$w->Fetch()) $arGroups[] = $wr["ID"];
			if (is_array($arGroups))
			{
				foreach($arGroups as $gid)
					$arFields["arGROUP"][$gid] = CForm::GetPermission($ID, array($gid), "Y");
			}

			// картинка
			if (intval($arForm["IMAGE_ID"])>0)
			{
				$arIMAGE = CFile::MakeFileArray(CFile::CopyFile($arForm["IMAGE_ID"]));
				$arIMAGE["MODULE_ID"] = "form";
				$arFields["arIMAGE"] = $arIMAGE;
			}

			$NEW_ID = CForm::Set($arFields, 0);

			if (intval($NEW_ID)>0)
			{
				// статусы
				$rsStatus = CFormStatus::GetList($ID, $by, $order, array(), $is_filtered);
				while ($arStatus = $rsStatus->Fetch()) CFormStatus::Copy($arStatus["ID"], "N", $NEW_ID);

				// вопросы/поля
				$rsField = CFormField::GetList($ID, "ALL", $by, $order, array(), $is_filtered);
				while ($arField = $rsField->Fetch())
				{
					CFormField::Copy($arField["ID"], "N", $NEW_ID);
				}
			}
			return $NEW_ID;
		}
		else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		return false;
	}

	// delete web-form
	public static function Delete($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: Delete<br>Line: ";
		$ID = intval($ID);

		if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin())
		{
			// delete form results
			if (CForm::Reset($ID, "N"))
			{
				// delete temporary template
				$tmp_filename = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/form/form_".$ID.".php";
				if (file_exists($tmp_filename)) @unlink($tmp_filename);

				// delete form statuses
				$rsStatuses = CFormStatus::GetList($ID, $by, $order, $arFilter, $is_filtered);
				while ($arStatus = $rsStatuses->Fetch()) CFormStatus::Delete($arStatus["ID"], "N");

				// delete from fields & questions
				$rsFields = CFormField::GetList($ID, "ALL", $by, $order, array(), $is_filtered);
				while ($arField = $rsFields->Fetch()) CFormField::Delete($arField["ID"], "N");

				// delete form image
				$strSql = "SELECT IMAGE_ID FROM b_form WHERE ID='$ID' and IMAGE_ID>0";
				$z = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($zr = $z->Fetch()) CFile::Delete($zr["IMAGE_ID"]);

				// delete mail event type and mail templates, assigned to the current form
				$q = CForm::GetByID($ID);
				$qr = $q->Fetch();
				if (strlen(trim($qr["MAIL_EVENT_TYPE"]))>0)
				{
					// delete mail templates
					$em = new CEventMessage;
					$e = $em->GetList($by="id",$order="desc",array("EVENT_NAME"=>$qr["MAIL_EVENT_TYPE"], "EVENT_NAME_EXACT_MATCH" => "Y"));
					while ($er=$e->Fetch()) $em->Delete($er["ID"]);

					// delete mail event type
					$et = new CEventType;
					$et->Delete($qr["MAIL_EVENT_TYPE"]);
				}

				// delete site assignment
				$DB->Query("DELETE FROM b_form_2_site WHERE FORM_ID='$ID'", false, $err_mess.__LINE__);

				// delete mail templates assignment
				$DB->Query("DELETE FROM b_form_2_mail_template WHERE FORM_ID='$ID'", false, $err_mess.__LINE__);

				// delete form menu
				$DB->Query("DELETE FROM b_form_menu WHERE FORM_ID='$ID'", false, $err_mess.__LINE__);

				// delete from rights
				$DB->Query("DELETE FROM b_form_2_group WHERE FORM_ID='$ID'", false, $err_mess.__LINE__);

				// and finally delete form
				$DB->Query("DELETE FROM b_form WHERE ID='$ID'", false, $err_mess.__LINE__);

				return true;
			}
		}
		else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		return false;
	}

	// удаляем результаты формы
	public static function Reset($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: Reset<br>Line: ";
		$ID = intval($ID);

		$F_RIGHT = ($CHECK_RIGHTS!="Y") ? 30 : CForm::GetPermission($ID);
		if ($F_RIGHT>=30)
		{
			// обнуляем поля формы
			$rsFields = CFormField::GetList($ID, "ALL", $by, $order, array(), $is_filtered);
			while ($arField = $rsFields->Fetch()) CFormField::Reset($arField["ID"], "N");

			// удаляем результаты данной формы
			$DB->Query("DELETE FROM b_form_result WHERE FORM_ID='$ID'", false, $err_mess.__LINE__);

			return true;
		}
		else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";

		return false;
	}

	// создает тип почтового события и шаблон на языке формы
	public static function SetMailTemplate($WEB_FORM_ID, $ADD_NEW_TEMPLATE="Y", $old_SID="", $bReturnFullInfo = false)
	{
		global $DB, $MESS, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: SetMailTemplates<br>Line: ";
		$arrReturn = array();
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$q = CForm::GetByID($WEB_FORM_ID);
		if ($arrForm = $q->Fetch())
		{
			$MAIL_EVENT_TYPE = "FORM_FILLING_".$arrForm["SID"];
			if (strlen($old_SID)>0) $old_MAIL_EVENT_TYPE = "FORM_FILLING_".$old_SID;

			$et = new CEventType;
			$em = new CEventMessage;

			if (strlen($MAIL_EVENT_TYPE)>0)
				$et->Delete($MAIL_EVENT_TYPE);

			$z = CLanguage::GetList($v1, $v2);
			$OLD_MESS = $MESS;
			while ($arLang = $z->Fetch())
			{
				IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_mail.php", $arLang["LID"]);

				$str = "";
				$str .= "#RS_FORM_ID# - ".GetMessage("FORM_L_FORM_ID")."\n";
				$str .= "#RS_FORM_NAME# - ".GetMessage("FORM_L_NAME")."\n";
				$str .= "#RS_FORM_SID# - ".GetMessage("FORM_L_SID")."\n";
				$str .= "#RS_RESULT_ID# - ".GetMessage("FORM_L_RESULT_ID")."\n";
				$str .= "#RS_DATE_CREATE# - ".GetMessage("FORM_L_DATE_CREATE")."\n";
				$str .= "#RS_USER_ID# - ".GetMessage("FORM_L_USER_ID")."\n";
				$str .= "#RS_USER_EMAIL# - ".GetMessage("FORM_L_USER_EMAIL")."\n";
				$str .= "#RS_USER_NAME# - ".GetMessage("FORM_L_USER_NAME")."\n";
				$str .= "#RS_USER_AUTH# - ".GetMessage("FORM_L_USER_AUTH")."\n";
				$str .= "#RS_STAT_GUEST_ID# - ".GetMessage("FORM_L_STAT_GUEST_ID")."\n";
				$str .= "#RS_STAT_SESSION_ID# - ".GetMessage("FORM_L_STAT_SESSION_ID")."\n";

				$strFIELDS = "";
				$w = CFormField::GetList($WEB_FORM_ID,"ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
				while ($wr=$w->Fetch())
				{
					if (strlen($wr["RESULTS_TABLE_TITLE"])>0)
					{
						$FIELD_TITLE = $wr["RESULTS_TABLE_TITLE"];
					}
					elseif (strlen($wr["TITLE"])>0)
					{
						$FIELD_TITLE = $wr["TITLE_TYPE"]=="html" ? htmlspecialcharsback(strip_tags($wr["TITLE"])) : $wr["TITLE"];
					}
					else
					{
						$FIELD_TITLE = TrimEx($wr["FILTER_TITLE"],":");
					}

					$str .= "#".$wr["SID"]."# - ".$FIELD_TITLE."\n";
					$str .= "#".$wr["SID"]."_RAW# - ".$FIELD_TITLE." (".GetMessage('FORM_L_RAW').")\n";
					$strFIELDS .= $FIELD_TITLE."\n*******************************\n#".$wr["SID"]."#\n\n";
				}

				$et->Add(
						Array(
						"LID"			=> $arLang["LID"],
						"EVENT_NAME"	=> $MAIL_EVENT_TYPE,
						"NAME"			=> GetMessage("FORM_FILLING")." \"".$arrForm["SID"]."\"",
						"DESCRIPTION"	=> $str
						)
					);
			}
			// задаем новый тип события для старых шаблонов
			if (strlen($old_MAIL_EVENT_TYPE)>0 && $old_MAIL_EVENT_TYPE!=$MAIL_EVENT_TYPE)
			{
				$e = $em->GetList($by="id",$order="desc",array("EVENT_NAME"=>$old_MAIL_EVENT_TYPE));
				while ($er=$e->Fetch())
				{
					$em->Update($er["ID"],array("EVENT_NAME"=>$MAIL_EVENT_TYPE));
				}
				if (strlen($old_MAIL_EVENT_TYPE)>0)
					$et->Delete($old_MAIL_EVENT_TYPE);
			}

			if ($ADD_NEW_TEMPLATE=="Y")
			{
				$z = CSite::GetList($v1, $v2);
				while ($arSite = $z->Fetch()) $arrSiteLang[$arSite["ID"]] = $arSite["LANGUAGE_ID"];

				$arrFormSite = CForm::GetSiteArray($WEB_FORM_ID);
				if (is_array($arrFormSite) && count($arrFormSite)>0)
				{
					foreach($arrFormSite as $sid)
					{
						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_mail.php", $arrSiteLang[$sid]);

						$SUBJECT = "#SERVER_NAME#: ".GetMessage("FORM_FILLING_S")." [#RS_FORM_ID#] #RS_FORM_NAME#";
						$MESSAGE = "#SERVER_NAME#

".GetMessage("FORM_FILLING").": [#RS_FORM_ID#] #RS_FORM_NAME#
-------------------------------------------------------

".GetMessage("FORM_DATE_CREATE")."#RS_DATE_CREATE#
".GetMessage("FORM_RESULT_ID")."#RS_RESULT_ID#
".GetMessage("FORM_USER")."[#RS_USER_ID#] #RS_USER_NAME# #RS_USER_AUTH#
".GetMessage("FORM_STAT_GUEST_ID")."#RS_STAT_GUEST_ID#
".GetMessage("FORM_STAT_SESSION_ID")."#RS_STAT_SESSION_ID#


$strFIELDS
".GetMessage("FORM_VIEW")."
http://#SERVER_NAME#/bitrix/admin/form_result_view.php?lang=".$arrSiteLang[$sid]."&WEB_FORM_ID=#RS_FORM_ID#&RESULT_ID=#RS_RESULT_ID#

-------------------------------------------------------
".GetMessage("FORM_GENERATED_AUTOMATICALLY")."
						";
						// добавляем новый шаблон
						$arFields = Array(
							"ACTIVE"		=> "Y",
							"EVENT_NAME"	=> $MAIL_EVENT_TYPE,
							"LID"			=> $sid,
							"EMAIL_FROM"	=> "#DEFAULT_EMAIL_FROM#",
							"EMAIL_TO"		=> "#DEFAULT_EMAIL_FROM#",
							"SUBJECT"		=> $SUBJECT,
							"MESSAGE"		=> $MESSAGE,
							"BODY_TYPE"		=> "text"
							);
						$TEMPLATE_ID = $em->Add($arFields);
						if ($bReturnFullInfo)
							$arrReturn[] = array(
								'ID' => $TEMPLATE_ID,
								'FIELDS' => $arFields,
							);
						else
							$arrReturn[] = $TEMPLATE_ID;
					}
				}
			}
			$MESS = $OLD_MESS;
		}
		return $arrReturn;
	}

	public static function GetBySID($SID)
	{
		return CForm::GetByID($SID, "Y");
	}

	/**
	 * Check whether current field is on template
	 *
	 * @param string $FIELD_SID
	 * @param string $tpl
	 * @return bool
	 */
	public static function isFieldInTemplate($FIELD_SID, $tpl)
	{
		$check_str1 = '$FORM->ShowInput(\''.$FIELD_SID.'\')';
		$check_str2 = '$FORM->ShowInput("'.$FIELD_SID.'")';

		return !((strpos($tpl, $check_str1) === false) && (strpos($tpl, $check_str2) === false));

	}

		/**
	 * Check whether CAPTCHA Fields is on template
	 *
	 * @param string $FIELD_SID
	 * @param string $tpl
	 * @return bool
	 */
	public static function isCAPTCHAInTemplate($tpl)
	{
		$check_str = '$FORM->ShowCaptcha';

		return strpos($tpl, $check_str) !== false;

	}

	public static function GetByID_admin($WEB_FORM_ID, $current_section = false)
	{
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		if ($WEB_FORM_ID <= 0)
			return false;

		$dbForm = CForm::GetByID($WEB_FORM_ID);
		if ($arForm = $dbForm->Fetch())
		{
			if (!$current_section)
			{
				$current_script = basename($GLOBALS['APPLICATION']->GetCurPage());

				switch ($current_script)
				{
					case 'form_edit.php':
						$current_section = 'form';
					break;

					case 'form_field_edit.php':
					case 'form_field_edit_simple.php':
					case 'form_field_list.php':

						if (!$bSimple && $_GET['additional'] == 'Y')
							$current_section = 'field';
						else
							$current_section = 'question';

					break;

					case 'form_result_edit.php':
					case 'form_result_list.php':
					case 'form_result_view.php':
						$current_section = 'result';
					break;

					case 'form_status_edit.php':
					case 'form_status_list.php':
						$current_section = 'status';
					break;
				}
			}

			$bSimple = COption::GetOptionString("form", "SIMPLE", "Y") == "Y";

			$arForm['ADMIN_MENU'] = array();

			$arForm['ADMIN_MENU'][] = array(
				"ICON"	=> $current_section == 'form' ? 'btn_active' : '',
				"TEXT"	=> GetMessage("FORM_MENU_EDIT"),
				"LINK"	=> "/bitrix/admin/form_edit.php?lang=".LANGUAGE_ID."&ID=".$WEB_FORM_ID,
				"TITLE"	=> htmlspecialcharsbx(str_replace("#NAME#", $arForm["NAME"], GetMessage("FORM_MENU_EDIT_TITLE")))
			);

			$arForm['ADMIN_MENU'][] = array(
				"ICON"	=> $current_section == 'result' ? 'btn_active' : '',
				"TEXT"	=> GetMessage("FORM_MENU_RESULTS")
					." (".CFormResult::GetCount($WEB_FORM_ID).")",
				"LINK"	=> "/bitrix/admin/form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
				"TITLE"	=> htmlspecialcharsbx(str_replace("#NAME#", $arForm["NAME"], GetMessage("FORM_MENU_RESULTS_TITLE")))
			);

			$arForm['ADMIN_MENU'][] = array(
				"ICON"	=> $current_section == 'question' ? 'btn_active' : '',
				"TEXT"	=> GetMessage("FORM_MENU_QUESTIONS")
					." (".($bSimple ? $arForm["QUESTIONS"] + $arForm["C_FIELDS"] : $arForm["QUESTIONS"]).")",
				"LINK"	=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
				"TITLE"	=> htmlspecialcharsbx(str_replace("#NAME#", $arForm["NAME"], GetMessage("FORM_MENU_QUESTIONS_TITLE")))
			);

			if (!$bSimple)
			{
				$arForm['ADMIN_MENU'][] = array(
					"ICON"	=> $current_section == 'field' ? 'btn_active' : '',
					"TEXT"	=> GetMessage("FORM_MENU_FIELDS")
						." (".$arForm["C_FIELDS"].")",
					"LINK"	=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y",
					"TITLE"	=> htmlspecialcharsbx(str_replace("#NAME#", $arForm["NAME"], GetMessage("FORM_MENU_FIELDS_TITLE")))
				);

				$arForm['ADMIN_MENU'][] = array(
					"ICON"	=> $current_section == 'status' ? 'btn_active' : '',
					"TEXT"	=> GetMessage("FORM_MENU_STATUSES")
						." (".$arForm["STATUSES"].")",
					"LINK"	=> "/bitrix/admin/form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
					"TITLE"	=> htmlspecialcharsbx(str_replace("#NAME#", $arForm["NAME"], GetMessage("FORM_MENU_STATUSES_TITLE")))
				);
			}

			return $arForm;
		}

		return false;
	}
}
?>