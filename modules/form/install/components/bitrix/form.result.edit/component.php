<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (CModule::IncludeModule("form"))
{
	$GLOBALS['strError'] = '';

	$arDefaultComponentParameters = array(
		"RESULT_ID" => $_REQUEST["RESULT_ID"],
		"EDIT_ADDITIONAL" => "N",
		"EDIT_STATUS" => "N",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"USE_EXTENDED_ERRORS" => "N",
	);

	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE'])
		? (method_exists('CSite', 'GetNameFormat') ? CSite::GetNameFormat() : "#NAME# #LAST_NAME#")
		: $arParams["NAME_TEMPLATE"];

	foreach ($arDefaultComponentParameters as $key => $value) if (!is_set($arParams, $key)) $arParams[$key] = $value;

	$arDefaultUrl = array(
		'LIST' => $arParams["SEF_MODE"] == "Y" ? "list/" : "result_list.php",
		'VIEW' => $arParams["SEF_MODE"] == "Y" ? "view/#RESULT_ID#/" : "result_view.php",
	);

	foreach ($arDefaultUrl as $action => $url)
	{
		if ($arParams[$action.'_URL'] == '')
		{
			if (!is_set($arParams, 'SHOW_'.$action.'_PAGE') || $arParams['SHOW_'.$action.'_PAGE'] == 'Y')
				$arParams[$action.'_URL'] = $url;
		}
	}

	if ($arParams["SEF_MODE"] == "Y" && empty($arParams["RESULT_ID"]))
	{
		$arDefaultUrlTemplates404 = array(
			"edit" => "#RESULT_ID#/",
		);

		$arDefaultVariableAliases404 = array(
		);

		$arDefaultVariableAliases = array();

		$arComponentVariables = array("RESULT_ID");

		$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
		CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

		$arParams["RESULT_ID"] = intval($arVariables["RESULT_ID"]);
	}

	$arResult["FORM_SIMPLE"] = COption::GetOptionString("form", "SIMPLE", "N") == "N" ? "N" : "Y";
	$arResult["bAdmin"] = defined("ADMIN_SECTION") && ADMIN_SECTION===true ? "Y" : "N";

	// if form taken from admin interface - check rights to form module
	if ($arResult["bAdmin"] == "Y")
	{
		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}

	/****************************************************************************/

	// if there's result ID try to get form ID
	if (intval($arParams["RESULT_ID"] > 0))
	{
		$DBRes = CFormResult::GetByID($arParams["RESULT_ID"]);

		if ($arResultData = $DBRes->Fetch())
		{
			$arParams["WEB_FORM_ID"] = intval($arResultData["FORM_ID"]);
		}
	}

	if (intval($arParams["RESULT_ID"]) <= 0 || intval($arParams["WEB_FORM_ID"]) <= 0)
	{
		$arResult["ERROR"] = "FORM_RECORD_NOT_FOUND";
	}


	if ($arResult["ERROR"] == '')
	{
		// check WEB_FORM_ID and get web form data
		$arParams["WEB_FORM_ID"] = CForm::GetDataByID($arParams["WEB_FORM_ID"], $arResult["arForm"], $arResult["arQuestions"], $arResult["arAnswers"], $arResult["arDropDown"], $arResult["arMultiSelect"], $arResult["bAdmin"] == 'Y' || $arParams["SHOW_ADDITIONAL"] == "Y" || $arParams["EDIT_ADDITIONAL"] == "Y" ? "ALL" : "N");

		$arResult["WEB_FORM_NAME"] = $arResult["arForm"]["SID"];

		// if wrong WEB_FORM_ID return error;
		if ($arParams["WEB_FORM_ID"] > 0)
		{
			//  insert chain item
			if ($arParams["CHAIN_ITEM_TEXT"] <> '')
			{
				$APPLICATION->AddChainItem($arParams["CHAIN_ITEM_TEXT"], $arParams["CHAIN_ITEM_LINK"]);
			}

			// check web form rights;
			$arResult["F_RIGHT"] = intval(CForm::GetPermission($arParams["WEB_FORM_ID"]));

			// in no form access - return error
			if ($arResult["F_RIGHT"] >= 15)
			{
				//if (!empty($_REQUEST["strFormNote"])) $arResult["FORM_NOTE"] = $_REQUEST["strFormNote"];
				if (!empty($_REQUEST["formresult"]))
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

				if ($arResult["F_RIGHT"]>=20 || ($arResult["F_RIGHT"]>=15 && $USER->GetID()==$arResultData["USER_ID"]))
				{
					$arResult["arrRESULT_PERMISSION"] = CFormResult::GetPermissions($arParams["RESULT_ID"]);

					// check result rights
					if (!in_array("EDIT", $arResult["arrRESULT_PERMISSION"]))
					{
						$arResult["ERROR"] = "FORM_RESULT_ACCESS_DENIED";
					}
					else
					{
						if (!$arResultData)
						{
							$z = CFormResult::GetByID($arParams["RESULT_ID"]);
							$arResult["arResultData"] = $z->Fetch();
						}
						else
						{
							$arResult["arResultData"] = $arResultData;
						}

						if ($arResult["arResultData"])
						{
							$arResult["arrVALUES"] = CFormResult::GetDataByIDForHTML($arParams["RESULT_ID"], $arParams["EDIT_ADDITIONAL"]);
						}
						else
						{
							$arResult["ERROR"] = "FORM_RECORD_NOT_FOUND";
						}
					}
				}
				else
				{
					$arResult["ERROR"] = "FORM_ACCESS_DENIED";
				}

				$arResult["arForm"]["USE_CAPTCHA"] = "N";
			}
			else
			{
				$arResult["ERROR"] = "FORM_RESULT_ACCESS_DENIED";
			}
		}
		else
		{
			$arResult["ERROR"] = "FORM_NOT_FOUND";
		}
	}

	// if there's no error
	if ($arResult["ERROR"] == '')
	{
		// ************************************************************* //
		//                                             get/post processing                                             //
		// ************************************************************* //

		if ($_REQUEST["web_form_submit"] <> '' || $_REQUEST["web_form_apply"] <> '')
		{
			$arResult["arrVALUES"] = $_REQUEST;

			// check errors
			$arResult["FORM_ERRORS"] = CForm::Check($arParams["WEB_FORM_ID"], $arResult["arrVALUES"], $arParams["RESULT_ID"], "Y", $arParams['USE_EXTENDED_ERRORS']);

			if (
				$arParams['USE_EXTENDED_ERRORS'] == 'Y' && (!is_array($arResult["FORM_ERRORS"]) || count($arResult["FORM_ERRORS"]) <= 0)
				||
				$arParams['USE_EXTENDED_ERRORS'] != 'Y' && $arResult["FORM_ERRORS"] == ''
			)
			{
				// check session id
				if (check_bitrix_sessid())
				{
					$return = false;

					if (CFormResult::Update($arParams["RESULT_ID"], $arResult["arrVALUES"], $arParams["EDIT_ADDITIONAL"]))
					{
						$arResult["FORM_RESULT"] = 'editok';

						if ($_REQUEST["web_form_submit"] <> '' && !(defined("ADMIN_SECTION") && ADMIN_SECTION===true))
						{
							if ($arParams["SEF_MODE"] == "Y")
							{
								//LocalRedirect($arParams["LIST_URL"]."?strFormNote=".urlencode($arResult["FORM_NOTE"]));
								LocalRedirect(
									str_replace(
										array('#WEB_FORM_ID#', '#RESULT_ID#'),
										array($arParams['WEB_FORM_ID'], $arParams["RESULT_ID"]),
										$arParams["LIST_URL"]
									)."?formresult=".urlencode($arResult["FORM_RESULT"])
								);
							}
							else
							{
								//LocalRedirect($arParams["LIST_URL"].(strpos($arParams["LIST_URL"], "?") === false ? "?" : "&")."WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&strFormNote=".urlencode($arResult["FORM_NOTE"]));
								LocalRedirect(
									$arParams["LIST_URL"]
									.(mb_strpos($arParams["LIST_URL"], "?") === false ? "?" : "&")
									."WEB_FORM_ID=".$arParams["WEB_FORM_ID"]
									."&RESULT_ID=".$arParams["RESULT_ID"]
									."&formresult=".urlencode($arResult["FORM_RESULT"])
								);
							}

							die();
						}

						if ($_REQUEST["web_form_apply"] <> '' && !(defined("ADMIN_SECTION") && ADMIN_SECTION===true))
						{
							// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
							//LocalRedirect($arParams["EDIT_URL"].(strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")."strFormNote=".urlencode($arResult["FORM_NOTE"]));
							if ($arParams["SEF_MODE"] == "Y")
							{
								//LocalRedirect(str_replace("#RESULT_ID#", $RESULT_ID, $arParams["EDIT_URL"])."?strFormNote=".urlencode($arResult["FORM_NOTE"]));
								/*LocalRedirect(
									str_replace(
										array('#WEB_FORM_ID#', '#RESULT_ID#'),
										array($arParams['WEB_FORM_ID'], $arParams["RESULT_ID"]),
										$arParams["EDIT_URL"]
									)
									.(strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")
									."formresult=".urlencode($arResult["FORM_RESULT"])
								);
								*/
								LocalRedirect(
									$APPLICATION->GetCurPageParam(
										"formresult=".urlencode($arResult["FORM_RESULT"]),
										array('formresult', 'SEF_APPLICATION_CUR_PAGE_URL')
									)
								);
							}
							else
							{
								LocalRedirect(
									$APPLICATION->GetCurPageParam(
										"WEB_FORM_ID=".$arParams["WEB_FORM_ID"]
										."&RESULT_ID=".$arParams["RESULT_ID"]
										."&formresult=".urlencode($arResult["FORM_RESULT"]),
										array('WEB_FORM_ID', 'RESULT_ID', 'formresult')
									)
								);
							}
							die();
						}

						if (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
						{
							if ($_REQUEST["web_form_submit"] <> '')
							{
								LocalRedirect(BX_ROOT."/admin/form_result_list.php?lang=".LANG."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&formresult=".urlencode($arResult["FORM_RESULT"]));
							}
							elseif ($_REQUEST["web_form_apply"] <> '')
							{
								LocalRedirect(BX_ROOT."/admin/form_result_edit.php?lang=".LANG."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&RESULT_ID=".$arParams["RESULT_ID"]."&form_result=".urlencode($arResult["FORM_RESULT"]));
							}
							die();
						}
					}
					else
						$arResult['FORM_ERRORS'] = $GLOBALS['strError'];
				}
			}
		}

		/*
		if (is_array($arResult["FORM_ERRORS"]))
		{
			$arResult["FORM_ERRORS"] = implode("<br />", $arResult["FORM_ERRORS"]);
		}
		*/

		$arResult["isFormErrors"] =
			(
				is_array($arResult["FORM_ERRORS"]) && count($arResult["FORM_ERRORS"]) > 0
				||
				!is_array($arResult['FORM_ERRORS']) && $arResult["FORM_ERRORS"] <> ''
			)
			? "Y" : "N";

		if ($arResult['isFormErrors'] == 'Y')
		{
			unset($arResult['FORM_RESULT']);
			unset($arResult['FORM_NOTE']);
		}

		// ************************************************************* //
		//                                             output                                                                    //
		// ************************************************************* //

		if ($arParams["IGNORE_CUSTOM_TEMPLATE"] == "N" && $arResult["arForm"]["USE_DEFAULT_TEMPLATE"] == "N" && $arResult["arForm"]["FORM_TEMPLATE"] <> '')
		{
			$FORM = new CFormOutput();
			// initialize template
			$FORM->InitializeTemplate($arParams, $arResult);

			// get template
			if ($strReturn = $FORM->IncludeFormCustomTemplate())
			{
				// add icons
				$back_url = $_SERVER['REQUEST_URI'];

				$editor = "/bitrix/admin/fileman_file_edit.php?full_src=Y&site=".SITE_ID."&";
				$href = "javascript:window.location='".$editor."path=".urlencode($path)."&lang=".LANGUAGE_ID."&back_url=".urlencode($back_url)."'";

				if ($arParams['USE_EXTENDED_ERRORS'] == 'Y')
				$APPLICATION->SetAdditionalCSS($this->GetPath()."/error.css");

				// output template
				echo $strReturn;

				return;
			}
		}

		if (intval($arResult["arResultData"]["USER_ID"])>0)
		{
			$rsUser = CUser::GetByID($arResult["arResultData"]["USER_ID"]);
			$arUser = $rsUser->Fetch();

			$arResult["RESULT_USER_ID"] = $arResult["arResultData"]["USER_ID"];
			$arResult["RESULT_USER_LOGIN"] = $arUser["LOGIN"];
			$arResult["RESULT_USER_EMAIL"] = $arUser["USER_EMAIL"];
			$arResult["RESULT_USER_FIRST_NAME"] = $arUser["NAME"];
			$arResult["RESULT_USER_LAST_NAME"] = $arUser["LAST_NAME"];
			$arResult["RESULT_USER_SECOND_NAME"] = $arUser["SECOND_NAME"];
		}

		$arResult["isResultStatusChangeAccess"] = in_array("EDIT", $arResult["arrRESULT_PERMISSION"]) ? "Y" : "N";

		$arResult["RESULT_STATUS_FORM"] = $arResult["isResultStatusChangeAccess"] == "Y" ? SelectBox("status_".$arResult["WEB_FORM_NAME"], CFormStatus::GetDropdown($arParams["WEB_FORM_ID"], array("MOVE"), $arResult["RESULT_USER_ID"]), " ", "", "") : "";

		// define variables to assign
		$arResult = array_merge(
			$arResult,
			array(
				"RESULT_ID" => $arParams["RESULT_ID"],
				"WEB_FORM_ID" => $arParams["WEB_FORM_ID"],

				"RESULT_STATUS" => "<span class='".$arResult["arResultData"]["STATUS_CSS"]."'>".$arResult["arResultData"]["STATUS_TITLE"]."</span>",

				"RESULT_USER_AUTH" => $arResult["arResultData"]["USER_AUTH"] == "Y" ? "Y" : "N",

				"RESULT_DATE_CREATE" => $arResult["arResultData"]["DATE_CREATE"],
				"RESULT_TIMESTAMP_X" => $arResult["arResultData"]["TIMESTAMP_X"],

				"RESULT_STAT_GUEST_ID" => $arResult["arResultData"]["STAT_GUEST_ID"],
				"RESULT_STAT_SESSION_ID" => $arResult["arResultData"]["STAT_SESSION_ID"],

				"isFormNote"			=> $arResult["FORM_NOTE"] <> ''? "Y" : "N", // flag "is there a form note"
				"isAccessFormParams"	=> $arResult["F_RIGHT"] >= 25 ? "Y" : "N", // flag "does current user have access to form params"
				"isStatisticIncluded"	=> CModule::IncludeModule('statistic') ? "Y" : "N", // flag "is statistic module included"

				"FORM_HEADER" => sprintf( // form header (<form> tag and hidden inputs)
					"<form name=\"%s\" action=\"%s\" method=\"%s\" enctype=\"multipart/form-data\">",
					$arResult["arForm"]["SID"], POST_FORM_ACTION_URI, "POST"
				),

				"FORM_TITLE"			=> trim(htmlspecialcharsbx($arResult["arForm"]["NAME"])), // form title

				"FORM_DESCRIPTION" => // form description
					$arResult["arForm"]["DESCRIPTION_TYPE"] == "html" ?
					trim($arResult["arForm"]["DESCRIPTION"]) :
					nl2br(htmlspecialcharsbx(trim($arResult["arForm"]["DESCRIPTION"]))),

				"isFormTitle"			=> $arResult["arForm"]["NAME"] <> '' ? "Y" : "N", // flag "does form have title"
				"isFormDescription"		=> $arResult["arForm"]["DESCRIPTION"] <> '' ? "Y" : "N", // flag "does form have description"
				"isFormImage"			=> intval($arResult["arForm"]["IMAGE_ID"]) > 0 ? "Y" : "N", // flag "does form have image"
				"isUseCaptcha"			=> $arResult["arForm"]["USE_CAPTCHA"] == "Y", // flag "does form use captcha"
				"DATE_FORMAT"			=> CLang::GetDateFormat("SHORT"), // current site date format
				"REQUIRED_SIGN"			=> CForm::ShowRequired("Y"), // "required" sign
				"FORM_FOOTER"			=> "</form>", // form footer (close <form> tag)
			)
		);

		// get template vars for form image
		if ($arResult["isFormImage"] == "Y")
		{
			$arResult["FORM_IMAGE"]["ID"] = $arResult["arForm"]["IMAGE_ID"];
			// assign form image url
			$arImage = CFile::GetFileArray($arResult["arForm"]["IMAGE_ID"]);
			$arResult["FORM_IMAGE"]["URL"] = $arImage["SRC"];

			// check image file existance and assign image data
			if (mb_substr($arImage["SRC"], 0, 1) == "/")
			{
				$arSize = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"].$arImage["SRC"]);
				if (is_array($arSize))
				{
					list(
						$arResult["FORM_IMAGE"]["WIDTH"],
						$arResult["FORM_IMAGE"]["HEIGHT"],
						$arResult["FORM_IMAGE"]["TYPE"],
						$arResult["FORM_IMAGE"]["ATTR"]
					) = $arSize;
				}
			}
			else
			{
				$arResult["FORM_IMAGE"]["WIDTH"] = $arImage["WIDTH"];
				$arResult["FORM_IMAGE"]["HEIGHT"] = $arImage["HEIGHT"];
				$arResult["FORM_IMAGE"]["TYPE"] = false;
				$arResult["FORM_IMAGE"]["ATTR"] = false;
			}

			$arResult["FORM_IMAGE"]["HTML_CODE"] = CFile::ShowImage($arResult["arForm"]["IMAGE_ID"]);
		}

		$arResult["QUESTIONS"] = array();
		reset($arResult["arQuestions"]);

		// assign questions data
		foreach ($arResult["arQuestions"] as $key => $arQuestion)
		{
			$FIELD_SID = $arQuestion["SID"];
			$arResult["QUESTIONS"][$FIELD_SID] = array(
				"CAPTION" => // field caption
					$arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ?
					$arResult["arQuestions"][$FIELD_SID]["TITLE"] :
					nl2br(htmlspecialcharsbx($arResult["arQuestions"][$FIELD_SID]["TITLE"])),

				"IS_HTML_CAPTION"			=> $arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ? "Y" : "N",
				"REQUIRED"					=> $arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? "Y" : "N",
				"IS_INPUT_CAPTION_IMAGE"	=> intval($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]) > 0 ? "Y" : "N",
			);

			// ******************************** customize answers ***************************** //

			$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"] = array();

			if (is_array($arResult["arAnswers"][$FIELD_SID]))
			{
				$res = "";

				reset($arResult["arAnswers"][$FIELD_SID]);
				if (is_array($arResult["arDropDown"][$FIELD_SID])) reset($arResult["arDropDown"][$FIELD_SID]);
				if (is_array($arResult["arMutiselect"][$FIELD_SID])) reset($arResult["arMutiselect"][$FIELD_SID]);

				$show_dropdown = "N";
				$show_multiselect = "N";

				foreach ($arResult["arAnswers"][$FIELD_SID] as $key => $arAnswer)
				{
					if ($arAnswer["FIELD_TYPE"]=="dropdown" && $show_dropdown=="Y") continue;
					if ($arAnswer["FIELD_TYPE"]=="multiselect" && $show_multiselect=="Y") continue;

					$res = "";

					switch ($arAnswer["FIELD_TYPE"])
					{
						case "radio":
							if (mb_strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"".$ans_id."\"";
							}
							else
							{
								$ans_id = "";
							}

							$value = CForm::GetRadioValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);

							if ($arResult["FORM_ERRORS"] <> '' || !$value || $value != $arAnswer["ID"])
							{
								if (
									mb_strpos(mb_strtolower($arAnswer["FIELD_PARAM"]), "selected") !== false
									||
									mb_strpos(mb_strtolower($arAnswer["FIELD_PARAM"]), "checked") !== false)
									{
										$arAnswer["FIELD_PARAM"] = preg_replace("/checked|selected/i", "", $arAnswer["FIELD_PARAM"]);
									}
							}

							$input = CForm::GetRadioField(
								$FIELD_SID,
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);


							if ($ans_id <> '')
							{
								$res .= $input;
								$res .= "<label for=\"".$ans_id."\">".$arAnswer["MESSAGE"]."</label>";
							}
							else
							{
								$res .= "<label>".$input.$arAnswer["MESSAGE"]."</label>";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "checkbox":
							if (mb_strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"".$ans_id."\"";
							}
							else
							{
								$ans_id = "";
							}

							$value = CForm::GetCheckBoxValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);

							if ($arResult["FORM_ERRORS"] <> '' || !$value)
							{
								if (
									mb_strpos(mb_strtolower($arAnswer["FIELD_PARAM"]), "selected") !== false
									||
									mb_strpos(mb_strtolower($arAnswer["FIELD_PARAM"]), "checked") !== false)
									{
										$arAnswer["FIELD_PARAM"] = preg_replace("/checked|selected/i", "", $arAnswer["FIELD_PARAM"]);
									}
							}

							$input = CForm::GetCheckBoxField(
								$FIELD_SID,
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);


							if ($ans_id <> '')
							{
								$res .= $input."<label for=\"".$ans_id."\">".$arAnswer["MESSAGE"]."</label>";
							}
							else
							{
								$res .= "<label>".$input.$arAnswer["MESSAGE"]."</label>";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "dropdown":
							if ($show_dropdown!="Y")
							{
								$value = CForm::GetDropDownValue($FIELD_SID, $arResult["arDropDown"], $arResult["arrVALUES"]);

								if ($arResult["FORM_ERROR"] <> '')
								{
									$c = count($arDropDown[$FIELD_SID]["param"])-1;
									for ($i=0;$i<=$c;$i++)
									{
										$arDropDown[$FIELD_SID]["param"][$i] = preg_replace("/checked|selected/i", "", $arDropDown[$FIELD_SID]["param"][$i]);
									}
								}

								$res .= CForm::GetDropDownField(
									$FIELD_SID,
									$arResult["arDropDown"][$FIELD_SID],
									$value,
									$arAnswer["FIELD_PARAM"]);
								$show_dropdown = "Y";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "multiselect":
							if ($show_multiselect!="Y")
							{
								$value = CForm::GetMultiSelectValue($FIELD_SID, $arResult["arMultiSelect"], $arResult["arrVALUES"]);

								if ($arResult["FORM_ERROR"] <> '')
								{
									$c = count($arMultiSelect[$FIELD_SID]["param"])-1;
									for ($i=0;$i<=$c;$i++)
									{
										$arMultiSelect[$FIELD_SID]["param"][$i] = preg_replace("/checked|selected/i", "", $arMultiSelect[$FIELD_SID]["param"][$i]);
									}
								}
								$res .= CForm::GetMultiSelectField(
									$FIELD_SID,
									$arResult["arMultiSelect"][$FIELD_SID],
									$value,
									$arAnswer["FIELD_HEIGHT"],
									$arAnswer["FIELD_PARAM"]);
								$show_multiselect = "Y";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "text":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}

							$value = CForm::GetTextValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetTextField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;

						case "hidden":

							$value = CForm::GetHiddenValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetHiddenField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;

						case "password":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}

							$value = CForm::GetPasswordValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetPasswordField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "email":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							$value = CForm::GetEmailValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetEmailField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "url":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							$value = CForm::GetUrlValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetUrlField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "textarea":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}

							if (intval($arAnswer["FIELD_WIDTH"]) <= 0) $arAnswer["FIELD_WIDTH"] = "40";
							if (intval($arAnswer["FIELD_HEIGHT"]) <= 0) $arAnswer["FIELD_HEIGHT"] = "5";

							$value = CForm::GetTextAreaValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetTextAreaField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_HEIGHT"],
								$arAnswer["FIELD_PARAM"],
								$value
								);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "date":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$res .= $arAnswer["MESSAGE"];
							}
							$value = CForm::GetDateValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetDateField(
								$arAnswer["ID"],
								$arResult["arForm"]["SID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res." (".CSite::GetDateFormat("SHORT").")";

							break;
						case "image":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$res .= $arAnswer["MESSAGE"];
							}
							if ($arFile = CFormResult::GetFileByAnswerID($arParams["RESULT_ID"], $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									if ($arFile["USER_FILE_IS_IMAGE"]=="Y")
									{
										$res .= CFile::ShowImage($arFile["USER_FILE_ID"], 0, 0, "border=0", "", true);
										$res .= "<br />";
										$res .= '<input type="checkbox" value="Y" name="form_image_'.$arAnswer['ID'].'_del" id="form_image_'.$arAnswer['ID'].'_del" /><label for="form_image_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';
									} //endif;
								} //endif;
							} // endif

							$res .= CForm::GetFileField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								"IMAGE",
								0,
								"",
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "file":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$res .= $arAnswer["MESSAGE"];
							}
							if ($arFile = CFormResult::GetFileByAnswerID($arParams["RESULT_ID"], $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									$res .= "<a title=\"".GetMessage("FORM_VIEW_FILE")."\" target=\"_blank\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."\">".htmlspecialcharsbx($arFile["USER_FILE_NAME"])."</a>&nbsp;(";
									$res .= CFile::FormatSize($arFile["USER_FILE_SIZE"]);
									$res .= ")&nbsp;&nbsp;[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arFile["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]<br />";
									$res .= '<input type="checkbox" value="Y" name="form_file_'.$arAnswer['ID'].'_del" id="form_file_'.$arAnswer['ID'].'_del" /><label for="form_file_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';

									$res .= "<br />";
								} //endif;
							} //endif;


							$res .= CForm::GetFileField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								"FILE",
								0,
								"",
								$arAnswer["FIELD_PARAM"]);

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
					} //endswitch;
				} //endwhile;


			} //endif(is_array($arAnswers[$FIELD_SID]));
			elseif (is_array($arResult["arQuestions"][$FIELD_SID]) && $arResult["arQuestions"][$FIELD_SID]["ADDITIONAL"] == "Y")
			{

				$res = "";

				switch ($arResult["arQuestions"][$FIELD_SID]["FIELD_TYPE"])
				{
					case "text":
						$value = CForm::GetTextAreaValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetTextAreaField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"],
							"60",
							"5",
							"",
							$value
							);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

						break;
					case "integer":
						$value = CForm::GetTextValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetTextField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"],
							$value);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

						break;
					case "date":
						$value = CForm::GetDateValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetDateField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"],
							$arResult["arForm"]["SID"],
							$value);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res." (".CSite::GetDateFormat("SHORT").")";

						break;
				} //endswitch;
			}

			$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"] = implode("<br />", $arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"]);

			// ******************************************************************************* //

			if ($arResult["QUESTIONS"][$FIELD_SID]["IS_INPUT_CAPTION_IMAGE"] == "Y")
			{
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ID"] = $arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"];
				// assign field image path
				$arImage = CFile::GetFileArray($arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ID"]);
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"] = $arImage["SRC"];

				// check image file existance and assign image data
				if (mb_substr($arImage["SRC"], 0, 1) == "/")
				{
					$arSize = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"].$arImage["SRC"]);
					if (is_array($arSize))
					{
						list(
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["WIDTH"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HEIGHT"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["TYPE"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ATTR"]
						) = $arSize;
					}
				}
				else
				{
					$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["WIDTH"] = $arImage["WIDTH"];
					$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HEIGHT"] = $arImage["HEIGHT"];
					$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["TYPE"] = false;
					$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ATTR"] = false;
				}

				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HTML_CODE"] = CFile::ShowImage($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]);
			}

			// get answers raw structure
			$arResult["QUESTIONS"][$FIELD_SID]["STRUCTURE"] = $arResult["arAnswers"][$FIELD_SID];

			// nullify value
			$arResult["QUESTIONS"][$FIELD_SID]["VALUE"] = "";
		}

		if ($arResult["isFormErrors"] == "Y")
		{
			ob_start();
			if ($arParams['USE_EXTENDED_ERRORS'] == 'N' || !is_array($arResult['FORM_ERRORS']))
				ShowError($arResult["FORM_ERRORS"]);
			else
				ShowError(implode('<br />', $arResult["FORM_ERRORS"]));

			$arResult["FORM_ERRORS_TEXT"] = ob_get_contents();
			ob_end_clean();
		}

		$arResult["SUBMIT_BUTTON"] = "<input ".(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "")." type=\"submit\" name=\"web_form_submit\" value=\"".(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"])."\" />";
		$arResult["APPLY_BUTTON"] = "<input type=\"hidden\" name=\"web_form_apply\" value=\"Y\" /><input type=\"submit\" name=\"web_form_apply\" value=\"".GetMessage("FORM_APPLY")."\" />";
		$arResult["RESET_BUTTON"] = "<input type=\"reset\" value=\"".GetMessage("FORM_RESET")."\" />";
		$arResult["REQUIRED_STAR"] = $arResult["REQUIRED_SIGN"];

		// include default template

		$this->IncludeComponentTemplate();


	}
	else
	{
		echo ShowError(GetMessage($arResult["ERROR"]));
	}
}
else
{
	echo ShowError(GetMessage("FORM_MODULE_NOT_INSTALLED"));
}
?>