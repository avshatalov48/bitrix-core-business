<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponent $this
 * @var string $componentName
 * @var array $arParams
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if (CModule::IncludeModule("form"))
{
	$GLOBALS['strError'] = '';

	$arDefaultComponentParameters = [
		"WEB_FORM_ID" => $_REQUEST["WEB_FORM_ID"] ?? '',
		"SEF_MODE" => "N",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"USE_EXTENDED_ERRORS" => "N",
		"CACHE_TIME" => "3600",
	];

	foreach ($arDefaultComponentParameters as $key => $value)
	{
		if (!isset($arParams[$key]))
		{
			$arParams[$key] = $value;
		}
	}

	$arDefaultUrl = [
		'LIST' => $arParams["SEF_MODE"] == "Y" ? "list/" : "result_list.php",
		'EDIT' => $arParams["SEF_MODE"] == "Y" ? "edit/#RESULT_ID#/" : "result_edit.php",
	];

	foreach ($arDefaultUrl as $action => $url)
	{
		if (!is_set($arParams, $action . '_URL'))
		{
			if (!is_set($arParams, 'SHOW_' . $action . '_PAGE') || $arParams['SHOW_' . $action . '_PAGE'] == 'Y')
			{
				$arParams[$action . '_URL'] = $url;
			}
		}
	}

	if (isset($arParams['RESULT_ID']))
	{
		unset($arParams['RESULT_ID']);
	}

	//  insert chain item
	if ($arParams["CHAIN_ITEM_TEXT"] <> '')
	{
		$APPLICATION->AddChainItem($arParams["CHAIN_ITEM_TEXT"], $arParams["CHAIN_ITEM_LINK"]);
	}

	// check whether cache using needed
	$bCache = !(
			$_SERVER["REQUEST_METHOD"] == "POST"
			&&
			(
				!empty($_REQUEST["web_form_submit"])
				||
				!empty($_REQUEST["web_form_apply"])
			)
			||
			isset($_REQUEST['formresult']) && $_REQUEST['formresult'] == 'ADDOK'
		)
		&&
		!(
			$arParams["CACHE_TYPE"] == "N"
			||
			(
				$arParams["CACHE_TYPE"] == "A"
				&&
				COption::GetOptionString("main", "component_cache_on", "Y") == "N"
			)
			||
			(
				$arParams["CACHE_TYPE"] == "Y"
				&&
				intval($arParams["CACHE_TIME"]) <= 0
			)
		);

	// start caching
	if ($bCache)
	{
		// append arParams to cache ID;
		$arCacheParams = [];
		foreach ($arParams as $key => $value)
		{
			if ($key !== "NEW_URL" && mb_substr($key, 0, 1) != "~")
			{
				$arCacheParams[$key] = $value;
			}
		}
		// create CPHPCache class instance
		$obFormCache = new CPHPCache;
		// create cache ID and path
		$CACHE_ID = SITE_ID . "|" . $componentName . "|" . md5(serialize($arCacheParams)) . "|" . $USER->GetGroups();
		if (($tzOffset = CTimeZone::GetOffset()) <> 0)
		{
			$CACHE_ID .= "|" . $tzOffset;
		}
		$CACHE_PATH = "/" . SITE_ID . CComponentEngine::MakeComponentPath($componentName);
	}

	// initialize cache
	if ($bCache && $obFormCache->InitCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_PATH))
	{
		// if cache already exists - get vars
		$arCacheVars = $obFormCache->GetVars();
		$bVarsFromCache = true;

		$arResult = $arCacheVars["arResult"];

		if ($arParams["IGNORE_CUSTOM_TEMPLATE"] == "N"
			&& $arResult["arForm"]["USE_DEFAULT_TEMPLATE"] == "N"
			&& $arResult["arForm"]["FORM_TEMPLATE"] <> '')
		{
			$FORM = $arCacheVars["FORM"] ?? null;
			if (!$FORM)
			{
				$bVarsFromCache = false;
			}
		}
		$arResult['FORM_NOTE'] = '';
		$arResult['isFormNote'] = 'N';

		$arParams['WEB_FORM_ID'] = $arResult['arForm']['ID'];
	}
	else
	{
		/*************************************************************************************************/
		$bVarsFromCache = false;

		$arResult["bSimple"] = COption::GetOptionString("form", "SIMPLE", "Y") == "N" ? "N" : "Y";
		$arResult["bAdmin"] = defined("ADMIN_SECTION") && ADMIN_SECTION === true ? "Y" : "N";

		// if form taken from admin interface - check rights to form module
		if ($arResult["bAdmin"] == "Y")
		{
			$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
			if ($FORM_RIGHT <= "D")
			{
				$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
			}
		}

		if (intval($arParams['WEB_FORM_ID']) <= 0 && $arParams['WEB_FORM_ID'] <> '')
		{
			$obForm = CForm::GetBySID($arParams['WEB_FORM_ID']);
			if ($arForm = $obForm->Fetch())
			{
				$arParams['WEB_FORM_ID'] = $arForm['ID'];
			}
		}

		// check WEB_FORM_ID and get web form data
		$arParams["WEB_FORM_ID"] = CForm::GetDataByID(
			$arParams["WEB_FORM_ID"],
			$arResult["arForm"],
			$arResult["arQuestions"],
			$arResult["arAnswers"],
			$arResult["arDropDown"],
			$arResult["arMultiSelect"],
			$arResult["bAdmin"] == "Y" || ($arParams["SHOW_ADDITIONAL"] ?? null) == "Y" || ($arParams["EDIT_ADDITIONAL"] ?? null) == "Y" ? "ALL" : "N"
		);

		$arResult["WEB_FORM_NAME"] = $arResult["arForm"]["SID"];

		// if wrong WEB_FORM_ID return error;
		if ($arParams["WEB_FORM_ID"] > 0)
		{
			// check web form rights;
			$arResult["F_RIGHT"] = intval(CForm::GetPermission($arParams["WEB_FORM_ID"]));

			// in no form access - return error
			if ($arResult["F_RIGHT"] < 10)
			{
				$arResult["ERROR"] = "FORM_ACCESS_DENIED";
			}
		}
		else
		{
			$arResult["ERROR"] = "FORM_NOT_FOUND";
		}

		if ($bCache)
		{
			$obFormCache->StartDataCache();
			$GLOBALS['CACHE_MANAGER']->StartTagCache($CACHE_PATH);
			$GLOBALS['CACHE_MANAGER']->RegisterTag('forms');
			$GLOBALS['CACHE_MANAGER']->RegisterTag('form_' . $arParams['WEB_FORM_ID']);
			$GLOBALS['CACHE_MANAGER']->EndTagCache();
			$obFormCache->EndDataCache(
				[
					"arResult" => $arResult,
				]
			);
		}
	}

	if (empty($arResult["ERROR"]))
	{
		// ************************************************************* //
		// ****************** get/post processing ********************** //
		// ************************************************************* //

		$arResult["arrVALUES"] = [];

		if (
			isset($_POST['WEB_FORM_ID'])
			&& (
				$_POST['WEB_FORM_ID'] == $arParams['WEB_FORM_ID']
				|| $_POST['WEB_FORM_ID'] == $arResult['arForm']['SID']
			)
			&& (
				!empty($_REQUEST["web_form_submit"])
				|| !empty($_REQUEST["web_form_apply"])
			)
		)
		{
			$arResult["arrVALUES"] = $_REQUEST;

			// check errors
			$arResult["FORM_ERRORS"] = CForm::Check($arParams["WEB_FORM_ID"], $arResult["arrVALUES"], false, "Y", $arParams['USE_EXTENDED_ERRORS']);

			if (
				$arParams['USE_EXTENDED_ERRORS'] == 'Y' && (!is_array($arResult["FORM_ERRORS"]) || count($arResult["FORM_ERRORS"]) <= 0)
				||
				$arParams['USE_EXTENDED_ERRORS'] != 'Y' && $arResult["FORM_ERRORS"] == ''
			)
			{
				// check user session
				if (check_bitrix_sessid())
				{
					$return = false;

					// add result
					if ($RESULT_ID = CFormResult::Add($arParams["WEB_FORM_ID"], $arResult["arrVALUES"]))
					{
						//$arResult["FORM_NOTE"] = GetMessage("FORM_DATA_SAVED1").$RESULT_ID.GetMessage("FORM_DATA_SAVED2");
						$arResult["FORM_RESULT"] = 'addok';

						// send email notifications
						CFormCRM::onResultAdded($arParams["WEB_FORM_ID"], $RESULT_ID);
						CFormResult::SetEvent($RESULT_ID);
						CFormResult::Mail($RESULT_ID);

						// choose type of user redirect and do it

						if ($arResult["F_RIGHT"] >= 15)
						{
							if (!empty($_REQUEST["web_form_submit"]) && $arParams["LIST_URL"] <> '')
							{
								if ($arParams["SEF_MODE"] == "Y")
								{
									//LocalRedirect($arParams["LIST_URL"]."?strFormNote=".urlencode($arResult["FORM_NOTE"]));
									LocalRedirect(
										str_replace(
											['#WEB_FORM_ID#', '#RESULT_ID#'],
											[$arParams['WEB_FORM_ID'], $RESULT_ID],
											$arParams["LIST_URL"]
										) . "?formresult=" . urlencode($arResult["FORM_RESULT"])
									);
								}
								else
								{
									LocalRedirect(
										$arParams["LIST_URL"]
										. (mb_strpos($arParams["LIST_URL"], "?") === false ? "?" : "&")
										. "WEB_FORM_ID=" . $arParams["WEB_FORM_ID"]
										. "&RESULT_ID=" . $RESULT_ID
										. "&formresult=" . urlencode($arResult["FORM_RESULT"])
									);
								}
							}
							elseif ($_REQUEST["web_form_apply"] <> '' && $arParams["EDIT_URL"] <> '')
							{
								if ($arParams["SEF_MODE"] == "Y")
								{
									LocalRedirect(
										str_replace(
											['#WEB_FORM_ID#', '#RESULT_ID#'],
											[$arParams['WEB_FORM_ID'], $RESULT_ID],
											$arParams["EDIT_URL"]
										)
										. (mb_strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")
										. "formresult=" . urlencode($arResult["FORM_RESULT"])
									);
								}
								else
								{
									LocalRedirect(
										$arParams["EDIT_URL"]
										. (mb_strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")
										. "WEB_FORM_ID=" . $arParams["WEB_FORM_ID"]
										. "&RESULT_ID=" . $RESULT_ID
										. "&formresult=" . urlencode($arResult["FORM_RESULT"])
									);
								}
							}

							$arResult["return"] = true;
						}

						if ($arParams["SUCCESS_URL"] <> '')
						{
							if ($arParams['SEF_MODE'] == 'Y')
							{
								LocalRedirect(
									str_replace(
										['#WEB_FORM_ID#', '#RESULT_ID#'],
										[$arParams['WEB_FORM_ID'], $RESULT_ID],
										$arParams["SUCCESS_URL"]
									)
									. (mb_strpos($arParams["SUCCESS_URL"], "?") === false ? "?" : "&")
									. "formresult=" . urlencode($arResult["FORM_RESULT"])
								);
							}
							else
							{
								LocalRedirect(
									$arParams["SUCCESS_URL"]
									. (mb_strpos($arParams["SUCCESS_URL"], "?") === false ? "?" : "&")
									. "WEB_FORM_ID=" . $arParams["WEB_FORM_ID"]
									. "&RESULT_ID=" . $RESULT_ID
									. "&formresult=" . urlencode($arResult["FORM_RESULT"])
								);
							}
						}
						elseif ($arParams["SEF_MODE"] == "Y")
						{
							LocalRedirect(
								$APPLICATION->GetCurPageParam(
									"formresult=" . urlencode($arResult["FORM_RESULT"]),
									['formresult', 'strFormNote', 'SEF_APPLICATION_CUR_PAGE_URL']
								)
							);
						}
						else
						{
							LocalRedirect(
								$APPLICATION->GetCurPageParam(
									"WEB_FORM_ID=" . $arParams["WEB_FORM_ID"]
									. "&RESULT_ID=" . $RESULT_ID
									. "&formresult=" . urlencode($arResult["FORM_RESULT"]),
									['formresult', 'strFormNote', 'WEB_FORM_ID', 'RESULT_ID']
								)
							);
						}
					}
					else
					{
						if ($arParams['USE_EXTENDED_ERRORS'] == 'Y')
						{
							$arResult["FORM_ERRORS"] = [$GLOBALS["strError"]];
						}
						else
						{
							$arResult["FORM_ERRORS"] = $GLOBALS["strError"];
						}
					}
				}
			}
		}

		if (!empty($_REQUEST["formresult"]) && $_REQUEST['WEB_FORM_ID'] == $arParams['WEB_FORM_ID'])
		{
			$formResult = strtoupper($_REQUEST['formresult']);
			if ($formResult == 'ADDOK')
			{
				$arResult['FORM_NOTE'] = str_replace("#RESULT_ID#", $_REQUEST['RESULT_ID'] ?? '', GetMessage('FORM_NOTE_ADDOK'));
			}
		}

		$arResult["isFormErrors"] =
			(
				$arParams['USE_EXTENDED_ERRORS'] == 'Y' && !empty($arResult["FORM_ERRORS"]) && is_array($arResult["FORM_ERRORS"])
				||
				$arParams['USE_EXTENDED_ERRORS'] != 'Y' && !empty($arResult["FORM_ERRORS"])
			)
			? "Y" : "N";

		// ************************************************************* //
		//                                             output                                                                    //
		// ************************************************************* //

		if ($arParams["IGNORE_CUSTOM_TEMPLATE"] == "N" && $arResult["arForm"]["USE_DEFAULT_TEMPLATE"] == "N" && $arResult["arForm"]["FORM_TEMPLATE"] <> '')
		{
			// use visual template
			if (!$bCache || $bCache && !$bVarsFromCache)
			{
				if ($bCache)
				{
					$obFormCache->StartDataCache();
					$GLOBALS['CACHE_MANAGER']->StartTagCache($CACHE_PATH);
					$GLOBALS['CACHE_MANAGER']->RegisterTag('forms');
					$GLOBALS['CACHE_MANAGER']->RegisterTag('form_' . $arParams['WEB_FORM_ID']);
				}

				// initialize template
				$FORM = new CFormOutput();

				$FORM->InitializeTemplate($arParams, $arResult);

				// cache image files paths
				$FORM->ShowFormImage();
				$FORM->getFormImagePath();

				if ($bCache)
				{
					$GLOBALS['CACHE_MANAGER']->EndTagCache();
					$obFormCache->EndDataCache(
						[
							"arResult" => $arResult,
							"FORM" => $FORM,
						]
					);
				}
			}
			else
			{
				$FORM->strFormNote = $arResult['FORM_NOTE'];
				$FORM->isFormNote = (bool)$arResult['FORM_NOTE'];
			}

			// if form uses CAPCHA initialize it
			if ($arResult["arForm"]["USE_CAPTCHA"] == "Y")
			{
				$FORM->CAPTCHACode = $arResult["CAPTCHACode"] = $APPLICATION->CaptchaGetCode();
			}

			// get template
			if ($strReturn = $FORM->IncludeFormCustomTemplate())
			{
				if ($arParams['USE_EXTENDED_ERRORS'] == 'Y')
				{
					$APPLICATION->SetAdditionalCSS($this->GetPath() . "/error.css");
				}

				// output template
				echo $strReturn;

				return;
			}
		}

		if ($arResult["arForm"]["USE_CAPTCHA"] == "Y")
		{
			$arResult["CAPTCHACode"] = $APPLICATION->CaptchaGetCode();
		}

		// define variables to assign
		$arResult = array_merge(
			$arResult,
			[
				"isFormNote" => !empty($arResult["FORM_NOTE"]) ? "Y" : "N", // flag "is there a form note"
				"isAccessFormParams" => $arResult["F_RIGHT"] >= 25 ? "Y" : "N", // flag "does current user have access to form params"
				"isStatisticIncluded" => CModule::IncludeModule('statistic') ? "Y" : "N", // flag "is statistic module included"

				"FORM_HEADER" => sprintf( // form header (<form> tag and hidden inputs)
						"<form name=\"%s\" action=\"%s\" method=\"%s\" enctype=\"multipart/form-data\">",
						$arResult["arForm"]["SID"], POST_FORM_ACTION_URI, "POST"
					) . bitrix_sessid_post() . '<input type="hidden" name="WEB_FORM_ID" value="' . $arParams['WEB_FORM_ID'] . '" />',

				"FORM_TITLE" => trim(htmlspecialcharsbx($arResult["arForm"]["NAME"])), // form title

				"FORM_DESCRIPTION" => // form description
					$arResult["arForm"]["DESCRIPTION_TYPE"] == "html" ?
						trim($arResult["arForm"]["DESCRIPTION"]) :
						nl2br(htmlspecialcharsbx(trim($arResult["arForm"]["DESCRIPTION"]))),

				"isFormTitle" => $arResult["arForm"]["NAME"] <> '' ? "Y" : "N", // flag "does form have title"
				"isFormDescription" => $arResult["arForm"]["DESCRIPTION"] <> '' ? "Y" : "N", // flag "does form have description"
				"isFormImage" => intval($arResult["arForm"]["IMAGE_ID"]) > 0 ? "Y" : "N", // flag "does form have image"
				"isUseCaptcha" => $arResult["arForm"]["USE_CAPTCHA"] == "Y", // flag "does form use captcha"
				"DATE_FORMAT" => CLang::GetDateFormat("SHORT"), // current site date format
				"REQUIRED_SIGN" => CForm::ShowRequired("Y"), // "required" sign
				"FORM_FOOTER" => "</form>", // form footer (close <form> tag)
			]
		);

		/*
		if ($arResult["isFormNote"] == "Y")
		{
			ob_start();
			ShowMessage($arResult["FORM_NOTE"]);
			$arResult["FORM_NOTE"] = ob_get_contents();
			ob_end_clean();
		}
		*/

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
				$arSize = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"] . $arImage["SRC"]);
				if (is_array($arSize))
				{
					[
						$arResult["FORM_IMAGE"]["WIDTH"],
						$arResult["FORM_IMAGE"]["HEIGHT"],
						$arResult["FORM_IMAGE"]["TYPE"],
						$arResult["FORM_IMAGE"]["ATTR"],
					] = $arSize;
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

		$arResult["QUESTIONS"] = [];
		reset($arResult["arQuestions"]);

		// assign questions data
		foreach ($arResult["arQuestions"] as $arQuestion)
		{
			$FIELD_SID = $arQuestion["SID"];
			$arResult["QUESTIONS"][$FIELD_SID] = [
				"CAPTION" => // field caption
					$arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ?
						$arResult["arQuestions"][$FIELD_SID]["TITLE"] :
						nl2br(htmlspecialcharsbx($arResult["arQuestions"][$FIELD_SID]["TITLE"])),

				"IS_HTML_CAPTION" => $arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ? "Y" : "N",
				"REQUIRED" => $arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? "Y" : "N",
				"IS_INPUT_CAPTION_IMAGE" => intval($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]) > 0 ? "Y" : "N",
			];

			// ******************************** customize answers ***************************** //

			$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"] = [];

			if (is_array($arResult["arAnswers"][$FIELD_SID]))
			{
				$res = "";

				$show_dropdown = "N";
				$show_multiselect = "N";

				foreach ($arResult["arAnswers"][$FIELD_SID] as $arAnswer)
				{
					if ($arAnswer["FIELD_TYPE"] == "dropdown" && $show_dropdown == "Y")
					{
						continue;
					}
					if ($arAnswer["FIELD_TYPE"] == "multiselect" && $show_multiselect == "Y")
					{
						continue;
					}

					$res = "";

					switch ($arAnswer["FIELD_TYPE"])
					{
						case "radio":
							if (mb_strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"" . $ans_id . "\"";
							}
							else
							{
								$ans_id = "";
							}

							$value = CForm::GetRadioValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);

							if ($arResult["isFormErrors"] == 'Y')
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
								$res .= "<label for=\"" . $ans_id . "\">" . $arAnswer["MESSAGE"] . "</label>";
							}
							else
							{
								$res .= "<label>" . $input . $arAnswer["MESSAGE"] . "</label>";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "checkbox":
							if (mb_strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"" . $ans_id . "\"";
							}
							else
							{
								$ans_id = "";
							}

							$value = CForm::GetCheckBoxValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);

							if ($arResult['isFormErrors'] == 'Y')
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
								$res .= $input . "<label for=\"" . $ans_id . "\">" . $arAnswer["MESSAGE"] . "</label>";
							}
							else
							{
								$res .= "<label>" . $input . $arAnswer["MESSAGE"] . "</label>";
							}

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

							break;
						case "dropdown":
							if ($show_dropdown != "Y")
							{
								$value = CForm::GetDropDownValue($FIELD_SID, $arResult["arDropDown"], $arResult["arrVALUES"]);

								if ($arResult["FORM_ERROR"] <> '')
								{
									$c = count($arDropDown[$FIELD_SID]["param"]) - 1;
									for ($i = 0; $i <= $c; $i++)
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
							if ($show_multiselect != "Y")
							{
								$value = CForm::GetMultiSelectValue($FIELD_SID, $arResult["arMultiSelect"], $arResult["arrVALUES"]);

								if ($arResult["FORM_ERROR"] <> '')
								{
									$c = count($arResult["arMultiSelect"][$FIELD_SID]["param"]) - 1;
									for ($i = 0; $i <= $c; $i++)
									{
										$arResult["arMultiSelect"][$FIELD_SID]["param"][$i] = preg_replace("/checked|selected/i", "", $arResult["arMultiSelect"][$FIELD_SID]["param"][$i]);
									}
								}

								$res .= CForm::GetMultiSelectField(
									$FIELD_SID,
									$arResult["arMultiSelect"][$FIELD_SID],
									$value,
									$arAnswer["FIELD_HEIGHT"],
									$arAnswer["FIELD_PARAM"]
								);

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

							if (intval($arAnswer["FIELD_WIDTH"]) <= 0)
							{
								$arAnswer["FIELD_WIDTH"] = "40";
							}
							if (intval($arAnswer["FIELD_HEIGHT"]) <= 0)
							{
								$arAnswer["FIELD_HEIGHT"] = "5";
							}

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

							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res . " (" . CSite::GetDateFormat("SHORT") . ")";

							break;
						case "image":
							if (trim($arAnswer["MESSAGE"]) <> '')
							{
								$res .= $arAnswer["MESSAGE"];
							}
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
						$value = CForm::GetTextAreaValue("ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"], [], $arResult["arrVALUES"]);
						$res .= CForm::GetTextAreaField(
							"ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"],
							"60",
							"5",
							"",
							$value
						);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

						break;
					case "integer":
						$value = CForm::GetTextValue("ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"], [], $arResult["arrVALUES"]);
						$res .= CForm::GetTextField(
							"ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"],
							$value);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;

						break;
					case "date":
						$value = CForm::GetDateValue("ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"], [], $arResult["arrVALUES"]);
						$res .= CForm::GetDateField(
							"ADDITIONAL_" . $arResult["arQuestions"][$FIELD_SID]["ID"],
							$arResult["arForm"]["SID"],
							$value);

						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res . " (" . CSite::GetDateFormat("SHORT") . ")";

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
					$arSize = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"] . $arImage["SRC"]);
					if (is_array($arSize))
					{
						[
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["WIDTH"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HEIGHT"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["TYPE"],
							$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ATTR"],
						] = $arSize;
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

		// compability:

		if ($arResult["isFormErrors"] == "Y")
		{
			ob_start();
			if ($arParams['USE_EXTENDED_ERRORS'] == 'N')
			{
				ShowError($arResult["FORM_ERRORS"]);
			}
			else
			{
				ShowError(implode('<br />', $arResult["FORM_ERRORS"]));
			}

			$arResult["FORM_ERRORS_TEXT"] = ob_get_contents();
			ob_end_clean();
		}

		$arResult["SUBMIT_BUTTON"] = "<input " . (intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "") . " type=\"submit\" name=\"web_form_submit\" value=\"" . (trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]) . "\" />";
		$arResult["APPLY_BUTTON"] = "<input type=\"hidden\" name=\"web_form_apply\" value=\"Y\" /><input type=\"submit\" name=\"web_form_apply\" value=\"" . GetMessage("FORM_APPLY") . "\" />";
		$arResult["RESET_BUTTON"] = "<input type=\"reset\" value=\"" . GetMessage("FORM_RESET") . "\" />";
		$arResult["REQUIRED_STAR"] = $arResult["REQUIRED_SIGN"];
		$arResult["CAPTCHA_IMAGE"] = "<input type=\"hidden\" name=\"captcha_sid\" value=\"" . htmlspecialcharsbx($arResult["CAPTCHACode"] ?? '') . "\" /><img src=\"/bitrix/tools/captcha.php?captcha_sid=" . htmlspecialcharsbx($arResult["CAPTCHACode"] ?? '') . "\" width=\"180\" height=\"40\" />";
		$arResult["CAPTCHA_FIELD"] = "<input type=\"text\" name=\"captcha_word\" size=\"30\" maxlength=\"50\" value=\"\" class=\"inputtext\" />";
		$arResult["CAPTCHA"] = $arResult["CAPTCHA_IMAGE"] . "<br />" . $arResult["CAPTCHA_FIELD"];

		// include default template
		$this->IncludeComponentTemplate();
	}
	else
	{
		ShowError(GetMessage($arResult["ERROR"]));
	}
}
else
{
	ShowError(GetMessage("FORM_MODULE_NOT_INSTALLED"));
}
