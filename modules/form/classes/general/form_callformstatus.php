<?

/***************************************
	Статус результата веб-формы
***************************************/

class CAllFormStatus
{
	function err_mess()
	{
		$module_id = "form";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CAllFormStatus<br>File: ".__FILE__;
	}

	// права на статус по группам
	function GetPermissionList($STATUS_ID, &$arPERMISSION_VIEW, &$arPERMISSION_MOVE, &$arPERMISSION_EDIT, &$arPERMISSION_DELETE)
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: GetPermissionList<br>Line: ";
		global $DB, $strError;
		$STATUS_ID = intval($STATUS_ID);
		$arPERMISSION_VIEW = $arPERMISSION_MOVE = $arPERMISSION_EDIT = $arPERMISSION_DELETE = array();
		$strSql = "
			SELECT
				GROUP_ID,
				PERMISSION
			FROM
				b_form_status_2_group
			WHERE
				STATUS_ID='$STATUS_ID'
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($zr=$z->Fetch())
		{
			if ($zr["PERMISSION"]=="VIEW")		$arPERMISSION_VIEW[] = $zr["GROUP_ID"];
			if ($zr["PERMISSION"]=="MOVE")		$arPERMISSION_MOVE[] = $zr["GROUP_ID"];
			if ($zr["PERMISSION"]=="EDIT")		$arPERMISSION_EDIT[] = $zr["GROUP_ID"];
			if ($zr["PERMISSION"]=="DELETE")	$arPERMISSION_DELETE[] = $zr["GROUP_ID"];
		}

	}

	// возвращает массив максимальных прав на результат
	function GetMaxPermissions()
	{
		return array("VIEW","MOVE","EDIT","DELETE");
	}

	// права на статус
	function GetPermissions($STATUS_ID)
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: GetPermissions<br>Line: ";

		global $DB, $USER, $strError;

		$USER_ID = $USER->GetID();
		$STATUS_ID = intval($STATUS_ID);
		$arReturn = array();
		$arGroups = $USER->GetUserGroupArray();

		if (!is_array($arGroups) || count($arGroups) <= 0)
			$arGroups = array(2);

		if (CForm::IsAdmin())
		{
			$arReturn = CFormStatus::GetMaxPermissions();
		}
		else
		{
			$groups = implode(",",$arGroups);

			$strSql = "
				SELECT
					G.PERMISSION
				FROM
					b_form_status_2_group G
				WHERE
					G.STATUS_ID = $STATUS_ID
				AND
					G.GROUP_ID IN (0,".$groups.")";

			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch())
				$arReturn[] = $zr["PERMISSION"];
		}

		return $arReturn;
	}

	function GetNextSort($WEB_FORM_ID)
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: GetNextSort<br>Line: ";
		global $DB, $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_form_status WHERE FORM_ID=$WEB_FORM_ID";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	function GetDefault($WEB_FORM_ID)
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: GetDefault<br>Line: ";
		global $DB, $USER, $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$strSql = "SELECT ID FROM b_form_status WHERE FORM_ID=$WEB_FORM_ID and ACTIVE='Y' and DEFAULT_VALUE='Y'";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["ID"]);
	}

	// проверка статуса
	function CheckFields($arFields, $STATUS_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: CheckFields<br>Line: ";
		global $DB, $strError, $APPLICATION, $USER;
		$str = "";
		$STATUS_ID = intval($STATUS_ID);
		$FORM_ID = intval($arFields["FORM_ID"]);
		if ($FORM_ID <= 0) $str .= GetMessage("FORM_ERROR_FORM_ID_NOT_DEFINED")."<br>";
		else
		{
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin()) $RIGHT_OK = "Y";
			else
			{
				$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
				$F_RIGHT = CForm::GetPermission($FORM_ID);
				if ($FORM_RIGHT>"D" && $F_RIGHT>=30) $RIGHT_OK = "Y";
			}
			if ($RIGHT_OK=="Y")
			{
				if ($STATUS_ID<=0 || ($STATUS_ID>0 && is_set($arFields, "TITLE")))
				{
					if (strlen(trim($arFields["TITLE"]))<=0) $str .= GetMessage("FORM_ERROR_FORGOT_TITLE")."<br>";
				}
			}
			else $str .= GetMessage("FORM_ERROR_ACCESS_DENIED");
		}
		$strError .= $str;
		if (strlen($str)>0) return false; else return true;
	}

	// добавление/обновление статуса
	function Set($arFields, $STATUS_ID=false, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER, $strError, $APPLICATION;
		$STATUS_ID = intval($STATUS_ID);
		if (CFormStatus::CheckFields($arFields, $STATUS_ID, $CHECK_RIGHTS))
		{
			$arFields_i = array();

			$arFields_i["TIMESTAMP_X"] = $DB->GetNowFunction();

			if (is_set($arFields, "C_SORT"))
				$arFields_i["C_SORT"] = "'".intval($arFields["C_SORT"])."'";

			if (is_set($arFields, "ACTIVE"))
				$arFields_i["ACTIVE"] = ($arFields["ACTIVE"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "TITLE"))
				$arFields_i["TITLE"] = "'".$DB->ForSql($arFields["TITLE"],255)."'";

			if (is_set($arFields, "DESCRIPTION"))
				$arFields_i["DESCRIPTION"] = "'".$DB->ForSql($arFields["DESCRIPTION"],2000)."'";

			if (is_set($arFields, "CSS"))
				$arFields_i["CSS"] = "'".$DB->ForSql($arFields["CSS"],255)."'";

			if (is_set($arFields, "HANDLER_OUT"))
				$arFields_i["HANDLER_OUT"] = "'".$DB->ForSql($arFields["HANDLER_OUT"],255)."'";

			if (is_set($arFields, "HANDLER_IN"))
				$arFields_i["HANDLER_IN"] = "'".$DB->ForSql($arFields["HANDLER_IN"],255)."'";

			if (is_set($arFields, "MAIL_EVENT_TYPE"))
				$arFields_i["MAIL_EVENT_TYPE"] = "'".$DB->ForSql($arFields["MAIL_EVENT_TYPE"],255)."'";

			$DEFAULT_STATUS_ID = intval(CFormStatus::GetDefault($arFields["FORM_ID"]));
			if ($DEFAULT_STATUS_ID<=0 || $DEFAULT_STATUS_ID==$STATUS_ID)
			{
				if (is_set($arFields, "DEFAULT_VALUE"))
					$arFields_i["DEFAULT_VALUE"] = ($arFields["DEFAULT_VALUE"]=="Y") ? "'Y'" : "'N'";
			}

			//echo '<pre>'; print_r($arFields); echo '</pre>';
			//die();

			if ($STATUS_ID>0)
			{
				$DB->Update("b_form_status", $arFields_i, "WHERE ID='".$STATUS_ID."'", $err_mess.__LINE__);
			}
			else
			{
				$arFields_i["FORM_ID"] = "'".intval($arFields["FORM_ID"])."'";
				$STATUS_ID = $DB->Insert("b_form_status", $arFields_i, $err_mess.__LINE__);
			}

			$STATUS_ID = intval($STATUS_ID);

			if ($STATUS_ID>0)
			{
				// право на просмотр
				if (is_set($arFields, "arPERMISSION_VIEW"))
				{
					$DB->Query("DELETE FROM b_form_status_2_group WHERE STATUS_ID='".$STATUS_ID."' and PERMISSION='VIEW'", false, $err_mess.__LINE__);
					if (is_array($arFields["arPERMISSION_VIEW"]))
					{
						reset($arFields["arPERMISSION_VIEW"]);
						foreach($arFields["arPERMISSION_VIEW"] as $gid)
						{
							$arFields_i = array(
								"STATUS_ID"		=> "'".intval($STATUS_ID)."'",
								"GROUP_ID"		=> "'".intval($gid)."'",
								"PERMISSION"	=> "'VIEW'"
							);
							$DB->Insert("b_form_status_2_group",$arFields_i, $err_mess.__LINE__);
						}
					}
				}

				// право на перевод
				if (is_set($arFields, "arPERMISSION_MOVE"))
				{
					$DB->Query("DELETE FROM b_form_status_2_group WHERE STATUS_ID='".$STATUS_ID."' and PERMISSION='MOVE'", false, $err_mess.__LINE__);
					if (is_array($arFields["arPERMISSION_MOVE"]))
					{
						reset($arFields["arPERMISSION_MOVE"]);
						foreach($arFields["arPERMISSION_MOVE"] as $gid)
						{
							$arFields_i = array(
								"STATUS_ID"		=> "'".intval($STATUS_ID)."'",
								"GROUP_ID"		=> "'".intval($gid)."'",
								"PERMISSION"	=> "'MOVE'"
							);
							$DB->Insert("b_form_status_2_group",$arFields_i, $err_mess.__LINE__);
						}
					}
				}

				// право на редактирование
				if (is_set($arFields, "arPERMISSION_EDIT"))
				{
					$DB->Query("DELETE FROM b_form_status_2_group WHERE STATUS_ID='".$STATUS_ID."' and PERMISSION='EDIT'", false, $err_mess.__LINE__);
					if (is_array($arFields["arPERMISSION_EDIT"]))
					{
						reset($arFields["arPERMISSION_EDIT"]);
						foreach($arFields["arPERMISSION_EDIT"] as $gid)
						{
							$arFields_i = array(
								"STATUS_ID"		=> "'".intval($STATUS_ID)."'",
								"GROUP_ID"		=> "'".intval($gid)."'",
								"PERMISSION"	=> "'EDIT'"
							);
							$DB->Insert("b_form_status_2_group",$arFields_i, $err_mess.__LINE__);
						}
					}
				}

				// право на удаление
				if (is_set($arFields, "arPERMISSION_DELETE"))
				{
					$DB->Query("DELETE FROM b_form_status_2_group WHERE STATUS_ID='".$STATUS_ID."' and PERMISSION='DELETE'", false, $err_mess.__LINE__);
					if (is_array($arFields["arPERMISSION_DELETE"]))
					{
						reset($arFields["arPERMISSION_DELETE"]);
						foreach($arFields["arPERMISSION_DELETE"] as $gid)
						{
							$arFields_i = array(
								"STATUS_ID"		=> "'".intval($STATUS_ID)."'",
								"GROUP_ID"		=> "'".intval($gid)."'",
								"PERMISSION"	=> "'DELETE'"
							);
							$DB->Insert("b_form_status_2_group",$arFields_i, $err_mess.__LINE__);
						}
					}
				}

				if (is_set($arFields, "arMAIL_TEMPLATE"))
				{
					$DB->Query("DELETE FROM b_form_status_2_mail_template WHERE STATUS_ID='".$STATUS_ID."'", false, $err_mess.__LINE__);
					if (is_array($arFields["arMAIL_TEMPLATE"]))
					{
						reset($arFields["arMAIL_TEMPLATE"]);
						foreach($arFields["arMAIL_TEMPLATE"] as $mid)
						{
							$strSql = "
								INSERT INTO b_form_status_2_mail_template (STATUS_ID, MAIL_TEMPLATE_ID) VALUES (
									'".$STATUS_ID."',
									'".intval($mid)."'
								)
								";
							$DB->Query($strSql, false, $err_mess.__LINE__);
						}
					}
				}
			}
			return $STATUS_ID;
		}
		return false;
	}

	// удаляет статус
	function Delete($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $APPLICATION, $strError;
		$ID = intval($ID);
		$rsStatus = CFormStatus::GetByID($ID);
		if ($arStatus = $rsStatus->Fetch())
		{
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin())
				$RIGHT_OK="Y";
			else
			{
				$F_RIGHT = CForm::GetPermission($arStatus["FORM_ID"]);
				if ($F_RIGHT>=30) $RIGHT_OK="Y";
			}
			if ($RIGHT_OK=="Y")
			{
				$strSql = "SELECT 'x' FROM b_form_result WHERE STATUS_ID='$ID'";
				$z = $DB->Query($strSql, false, $err_mess.__LINE__);
				if (!$zr = $z->Fetch())
				{
					if ($DB->Query("DELETE FROM b_form_status WHERE ID='$ID'", false, $err_mess.__LINE__))
					{
						if ($DB->Query("DELETE FROM b_form_status_2_group WHERE STATUS_ID='$ID'", false, $err_mess.__LINE__))
							return true;
					}
				}
				else
					$strError .= GetMessage("FORM_ERROR_CANNOT_DELETE_STATUS")."<br>";
			}
		}
		else
			$strError .= GetMessage("FORM_ERROR_STATUS_NOT_FOUND")."<br>";
		return false;
	}

	// копирует статус
	function Copy($ID, $CHECK_RIGHTS="Y", $NEW_FORM_ID=false)
	{
		global $DB, $APPLICATION, $strError;
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: Copy<br>Line: ";
		$ID = intval($ID);
		$NEW_FORM_ID = intval($NEW_FORM_ID);
		$rsStatus = CFormStatus::GetByID($ID);
		if ($arStatus = $rsStatus->Fetch())
		{
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin()) $RIGHT_OK="Y";
			else
			{
				$F_RIGHT = CForm::GetPermission($arStatus["FORM_ID"]);
				// если имеем право на просмотр параметров формы
				if ($F_RIGHT>=25)
				{
					// если задана новая форма
					if ($NEW_FORM_ID>0)
					{
						$NEW_F_RIGHT = CForm::GetPermission($NEW_FORM_ID);
						// если имеем полный доступ на новую форму
						if ($NEW_F_RIGHT>=30) $RIGHT_OK = "Y";
					}
					elseif ($F_RIGHT>=30) // если имеем полный доступ на исходную форму
					{
						$RIGHT_OK = "Y";
					}
				}
			}

			// если права проверили то
			if ($RIGHT_OK=="Y")
			{
				CFormStatus::GetPermissionList($ID, $arPERMISSION_VIEW, $arPERMISSION_MOVE, $arPERMISSION_EDIT, $arPERMISSION_DELETE);
				// копируем
				$arFields = array(
					"FORM_ID"				=> ($NEW_FORM_ID>0) ? $NEW_FORM_ID : $arStatus["FORM_ID"],
					"C_SORT"				=> $arStatus["C_SORT"],
					"ACTIVE"				=> $arStatus["ACTIVE"],
					"TITLE"					=> $arStatus["TITLE"],
					"DESCRIPTION"			=> $arStatus["DESCRIPTION"],
					"CSS"					=> $arStatus["CSS"],
					"HANDLER_OUT"			=> $arStatus["HANDLER_OUT"],
					"HANDLER_IN"			=> $arStatus["HANDLER_IN"],
					"DEFAULT_VALUE"			=> $arStatus["DEFAULT_VALUE"],
					"arPERMISSION_VIEW"		=> $arPERMISSION_VIEW,
					"arPERMISSION_MOVE"		=> $arPERMISSION_MOVE,
					"arPERMISSION_EDIT"		=> $arPERMISSION_EDIT,
					"arPERMISSION_DELETE"	=> $arPERMISSION_DELETE,
					);
				$NEW_ID = CFormStatus::Set($arFields);
				return $NEW_ID;
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_STATUS_NOT_FOUND")."<br>";
		return false;
	}

	function SetMailTemplate($WEB_FORM_ID, $STATUS_ID, $ADD_NEW_TEMPLATE="Y", $old_SID="", $bReturnFullInfo = false)
	{
		global $DB, $MESS, $strError;
		$err_mess = (CAllForm::err_mess())."<br>Function: SetMailTemplate<br>Line: ";
		$arrReturn = array();
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$q = CForm::GetByID($WEB_FORM_ID);
		if ($arrForm = $q->Fetch())
		{
			$dbRes = CFormStatus::GetByID($STATUS_ID);
			if ($arrStatus = $dbRes->Fetch())
			{
				$MAIL_EVENT_TYPE = "FORM_STATUS_CHANGE_".$arrForm["SID"]."_".$arrStatus['ID'];
				if (strlen($old_SID)>0)
					$old_MAIL_EVENT_TYPE = "FORM_STATUS_CHANGE_".$old_SID."_".$arrStatus['ID'];

				$et = new CEventType;
				$em = new CEventMessage;

				if (strlen($MAIL_EVENT_TYPE)>0)
					$et->Delete($MAIL_EVENT_TYPE);

				$z = CLanguage::GetList($v1, $v2);
				$OLD_MESS = $MESS;
				$MESS = array();
				while ($arLang = $z->Fetch())
				{
					IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_status_mail.php", $arLang["LID"]);

					$str = "";
					$str .= "#EMAIL_TO# - ".GetMessage("FORM_L_EMAIL_TO")."\n";
					$str .= "#RS_FORM_ID# - ".GetMessage("FORM_L_FORM_ID")."\n";
					$str .= "#RS_FORM_NAME# - ".GetMessage("FORM_L_NAME")."\n";
					$str .= "#RS_FORM_SID# - ".GetMessage("FORM_L_SID")."\n";
					$str .= "#RS_RESULT_ID# - ".GetMessage("FORM_L_RESULT_ID")."\n";
					$str .= "#RS_DATE_CREATE# - ".GetMessage("FORM_L_DATE_CREATE")."\n";
					$str .= "#RS_USER_ID# - ".GetMessage("FORM_L_USER_ID")."\n";
					$str .= "#RS_USER_EMAIL# - ".GetMessage("FORM_L_USER_EMAIL")."\n";
					$str .= "#RS_USER_NAME# - ".GetMessage("FORM_L_USER_NAME")."\n";
					$str .= "#RS_STATUS_ID# - ".GetMessage("FORM_L_STATUS_ID")."\n";
					$str .= "#RS_STATUS_NAME# - ".GetMessage("FORM_L_STATUS_NAME")."\n";

					$et->Add(
							Array(
							"LID"			=> $arLang["LID"],
							"EVENT_NAME"	=> $MAIL_EVENT_TYPE,
							"NAME"			=> str_replace(array('#FORM_SID#', '#STATUS_NAME#'), array($arrForm['SID'], $arrStatus['TITLE']), GetMessage("FORM_CHANGE_STATUS")),
							"DESCRIPTION"	=> $str
							)
						);
				}
				// create new event type for old templates
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
							IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_status_mail.php", $arrSiteLang[$sid]);

							$SUBJECT = GetMessage("FORM_CHANGE_STATUS_S");
							$MESSAGE = GetMessage("FORM_CHANGE_STATUS_B");

							// добавляем новый шаблон
							$arFields = Array(
								"ACTIVE"		=> "Y",
								"EVENT_NAME"	=> $MAIL_EVENT_TYPE,
								"LID"			=> $sid,
								"EMAIL_FROM"	=> "#DEFAULT_EMAIL_FROM#",
								"EMAIL_TO"		=> "#EMAIL_TO#",
								"SUBJECT"		=> $SUBJECT,
								"MESSAGE"		=> $MESSAGE,
								"BODY_TYPE"		=> "text"
								);
							//echo '<pre>'; print_r($arFields); echo '</pre>';
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

				CFormStatus::Set(array('FORM_ID' => $WEB_FORM_ID, 'MAIL_EVENT_TYPE' => $MAIL_EVENT_TYPE), $STATUS_ID, 'N');

				$MESS = $OLD_MESS;
			}
		}
		return $arrReturn;
	}

	function GetMailTemplateArray($STATUS_ID)
	{
		$err_mess = (CAllFormStatus::err_mess())."<br>Function: GetMailTemplateArray<br>Line: ";

		global $DB, $USER, $strError;

		$STATUS_ID = intval($STATUS_ID);
		if ($STATUS_ID <= 0) return false;

		$arrRes = array();
		$strSql = "
SELECT
	FM.MAIL_TEMPLATE_ID
FROM
	b_form_status_2_mail_template FM
WHERE
	FM.STATUS_ID='".$STATUS_ID."'
";
		//echo "<pre>".$strSql."</pre>";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["MAIL_TEMPLATE_ID"];
		//echo "<pre>".print_r($arrRes, true)."</pre>";

		return $arrRes;
	}

	function GetTemplateList($STATUS_ID)
	{
		$err_mess = (CAllForm::err_mess())."<br>Function: GetTemplateList<br>Line: ";
		global $DB, $strError;

		$STATUS_ID = intval($STATUS_ID);
		if ($STATUS_ID > 0)
		{
			$arrSITE = array();
			$strSql = "
SELECT
	F.MAIL_EVENT_TYPE,
	FS.SITE_ID
FROM b_form_status F
INNER JOIN b_form_2_site FS ON (FS.FORM_ID = F.FORM_ID)
WHERE
	F.ID='".$STATUS_ID."'
";

			//echo '<pre>',$strSql,'</pre>';
			$z = $DB->Query($strSql,false,$err_mess.__LINE__);
			while ($zr = $z->Fetch())
			{
				$MAIL_EVENT_TYPE = $zr["MAIL_EVENT_TYPE"];
				$arrSITE[] = $zr["SITE_ID"];
			}

			if (strlen($MAIL_EVENT_TYPE) <= 0)
				return false;

			$arReferenceId = array();
			$arReference = array();
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

			$arr = array("reference"=>$arReference,"reference_id"=>$arReferenceId);
			return $arr;
		}
		return false;
	}
}
?>