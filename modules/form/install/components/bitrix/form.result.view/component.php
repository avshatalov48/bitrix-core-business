<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (CModule::IncludeModule("form"))
{
	$arDefaultComponentParameters = array(
		"RESULT_ID" => $_REQUEST["RESULT_ID"],
		"SHOW_ADDITIONAL" => "N",
		"SHOW_ANSWER_VALUE" => "N",
		"SHOW_STATUS" => "Y",
		"EDIT_URL" => $arParams["SEF_MODE"] == "Y" ? "edit/#RESULT_ID#/" : "result_edit.php",
	);

	foreach ($arDefaultComponentParameters as $key => $value) if (!is_set($arParams, $key)) $arParams[$key] = $value;

	$arDefaultUrl = array(
		'EDIT_URL' => $arParams["SEF_MODE"] == "Y" ? "edit/#RESULT_ID#/" : "result_edit.php",
	);

	foreach ($arDefaultUrl as $action => $url)
	{
		if (strlen($arParams[$action.'_URL']) <= 0)
		{
			if (!is_set($arParams, 'SHOW_'.$action.'_PAGE') || $arParams['SHOW_'.$action.'_PAGE'] == 'Y')
				$arParams[$action.'_URL'] = $url;
		}
	}

	if ($arParams["SEF_MODE"] == "Y" && empty($arParams["RESULT_ID"]))
	{
		$arDefaultUrlTemplates404 = array(
			"view" => "#RESULT_ID#/",
		);

		$arDefaultVariableAliases404 = array(
		);

		$arDefaultVariableAliases = array();

		$arComponentVariables = array("RESULT_ID");

		$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
		CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

		$arParams["WEB_FORM_ID"] = intval($arVariables["WEB_FORM_ID"]);
		$arParams["RESULT_ID"] = intval($arVariables["RESULT_ID"]);
	}

	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE'])
		? (method_exists('CSite', 'GetNameFormat') ? CSite::GetNameFormat() : "#NAME# #LAST_NAME#")
		: $arParams["NAME_TEMPLATE"];

	$arResult["FORM_SIMPLE"] = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;
	$arResult["bAdmin"] = defined("ADMIN_SECTION") && ADMIN_SECTION===true ? "Y" : "N";

	if ($arResult["bAdmin"] == "Y")
	{
		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}

	// if there's result ID try to get form ID
	if (intval($arParams["RESULT_ID"]) > 0)
	{
		$DBRes = CFormResult::GetByID($arParams["RESULT_ID"]);

		if ($arResultData = $DBRes->Fetch())
		{
			$arParams["WEB_FORM_ID"] = intval($arResultData["FORM_ID"]);
		}
	}

	// if there's no WEB_FORM_ID, try to get it from $_REQUEST;
	if (intval($arParams["WEB_FORM_ID"]) <= 0)
		$arParams["WEB_FORM_ID"] = intval($_REQUEST["WEB_FORM_ID"]);

	// check WEB_FORM_ID and get web form data
	$arParams["WEB_FORM_ID"] = CForm::GetDataByID($arParams["WEB_FORM_ID"], $arResult["arForm"], $arResult["arQuestions"], $arResult["arAnswers"], $arResult["arDropDown"], $arResult["arMultiSelect"], $arResult["bAdmin"] == "Y" || $arParams["SHOW_ADDITIONAL"] == "Y" || $arParams["EDIT_ADDITIONAL"] == "Y" ? "ALL" : "N");

	$arResult["WEB_FORM_NAME"] = $arResult["arForm"]["SID"];

		// if wrong WEB_FORM_ID return error;
	if ($arParams["WEB_FORM_ID"] > 0)
	{
	//  insert chain item
		if (strlen($arParams["CHAIN_ITEM_TEXT"]) > 0)
		{
			$APPLICATION->AddChainItem($arParams["CHAIN_ITEM_TEXT"], $arParams["CHAIN_ITEM_LINK"]);
		}

		// check web form rights;
		$arResult["F_RIGHT"] = intval(CForm::GetPermission($arParams["WEB_FORM_ID"]));

		// in no form access - return error
		if ($arResult["F_RIGHT"] >= 15)
		{
			if ($arParams["RESULT_ID"])
			{
				if ($arResult["F_RIGHT"]>=20 || ($arResult["F_RIGHT"]>=15 && $USER->GetID()==$arResultData["USER_ID"]))
				{
					$arResult["arrRESULT_PERMISSION"] = CFormResult::GetPermissions($arParams["RESULT_ID"], $v);

					// check result rights
					if (!in_array("VIEW",$arResult["arrRESULT_PERMISSION"]))
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
							CForm::GetResultAnswerArray($arParams["WEB_FORM_ID"], $arResult["arrResultColumns"], $arResult["arrVALUES"], $arResult["arrResultAnswersSID"], array("RESULT_ID" => $arParams["RESULT_ID"]));

							$arResult["arrVALUES"] = $arResult["arrVALUES"][$arParams["RESULT_ID"]];
						}
						else
						{
							$arResult["arrVALUES"] = CFormResult::GetDataByIDForHTML($arResult["RESULT_ID"], $arParams["SHOW_ADDITIONAL"]);
						}
					}
				}
				else
				{
					$arResult["ERROR"] = "FORM_ACCESS_DENIED";
				}
			}
			else
			{
				$arResult["ERROR"] = "FORM_RECORD_NOT_FOUND";
			}

		}
		else
		{
			$arResult["ERROR"] = "FORM_ACCESS_DENIED";
		}
	}
	else
	{
		$arResult["ERROR"] = "FORM_NOT_FOUND";
	} // endif ($WEB_FORM_ID>0);

	if (strlen($arResult["ERROR"]) <= 0)
	{
		$arParams["SHOW_STATUS"] = ($arParams["SHOW_STATUS"]=="Y" && !$arResult["FORM_SIMPLE"] == "Y") ? "Y" : "N";

		// append view data to arResult
		$arResult = array_merge(
			$arResult,
			array(
				"RESULT_ID" => $arParams["RESULT_ID"], // web form id
				"WEB_FORM_ID" => $arParams["WEB_FORM_ID"], // web form id

				"isAccessFormParams" => $arResult["F_RIGHT"] >= 25 ? "Y" : "N",
				"isAccessFormResultEdit" => in_array("EDIT", $arResult["arrRESULT_PERMISSION"]) ? "Y" : "N",
				"isStatisticIncluded" => CModule::IncludeModule("statistic") ? "Y" : "N",

				"FORM_TITLE" => trim(htmlspecialcharsbx($arResult["arForm"]["NAME"])),
				"FORM_DESCRIPTION" => $arResult["arForm"]["DESCRIPTION_TYPE"] == "html" ? trim($arParams["arForm"]["DESCRIPTION"]) : nl2br(htmlspecialcharsbx(trim($arParams["arForm"]["DESCRIPTION"]))),

				"isFormImage" => intval($arResult["arForm"]["IMAGE_ID"]) > 0 ? "Y" : "N",
				"REQUIRED_SIGN" => CForm::ShowRequired("Y"), // "required" sign - for manual template customization

				"RESULT_STATUS" => "<span class='".$arResult["arResultData"]["STATUS_CSS"]."'>".$arResult["arResultData"]["STATUS_TITLE"]."</span>", // formatted result status
				"RESULT_STATUS_CSS" => $arResult["arResultData"]["STATUS_CSS"],
				"RESULT_STATUS_TITLE" => $arResult["arResultData"]["STATUS_TITLE"],

				"RESULT_USER_AUTH" => $arResult["arResultData"]["USER_AUTH"] == "Y" ? "Y" : "N",

				"RESULT_DATE_CREATE" => $arResult["arResultData"]["DATE_CREATE"],
				"RESULT_TIMESTAMP_X" => $arResult["arResultData"]["TIMESTAMP_X"],

				"RESULT_STAT_GUEST_ID" => $arResult["arResultData"]["STAT_GUEST_ID"],
				"RESULT_STAT_SESSION_ID" => $arResult["arResultData"]["STAT_SESSION_ID"],
			)
		);

		$arResult["isFormTitle"] = strlen($arResult["FORM_TITLE"]) > 0 ? "Y" : "N";
		$arResult["isFormDescription"] = strlen($arResult["FORM_DESCRIPTION"]) > 0 ? "Y" : "N";

		//append user data to arResult
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

		// append result data to arResult
		$arResult["RESULT"] = array();
		foreach ($arResult["arQuestions"] as $arQuestion)
		{
			$FIELD_SID = $arQuestion["SID"];

			$arResult["RESULT"][$FIELD_SID] = array(
				"CAPTION" => // field caption
					$arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ?
					$arResult["arQuestions"][$FIELD_SID]["TITLE"] :
					nl2br(htmlspecialcharsbx($arResult["arQuestions"][$FIELD_SID]["TITLE"])),

				"IS_HTML_CAPTION"			=> $arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ? "Y" : "N",
				"REQUIRED"					=> $arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? "Y" : "N",
				"IS_INPUT_CAPTION_IMAGE"	=> intval($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]) > 0 ? "Y" : "N",
				"ANSWER_VALUE" 				=> $arResult["arrVALUES"][$arQuestion["ID"]], // answer data - for manual customization
			);

			$out = "";
			$arResultAnswers = array();
			if (is_array($arResult["RESULT"][$FIELD_SID]["ANSWER_VALUE"]))
			{
				foreach ($arResult["RESULT"][$FIELD_SID]["ANSWER_VALUE"] as $key => $arrA)
				{
					$arResultAnswer = array();

					if (strlen(trim($arrA["USER_TEXT"]))>0)
					{
						if (intval($arrA["USER_FILE_ID"])>0)
						{
							if ($arrA["USER_FILE_IS_IMAGE"]=="Y" && $USER->IsAdmin())
							{
								$arResultAnswer["USER_TEXT"] = htmlspecialcharsbx($arrA["USER_TEXT"]);
							}
						}
						else
						{
							$arResultAnswer["USER_TEXT"] = TxtToHTML(trim($arrA["USER_TEXT"]),true,50);
						}
					}

					if (strlen(trim($arrA["ANSWER_TEXT"]))>0)
					{
						$arResultAnswer["ANSWER_TEXT"] = TxtToHTML(trim($arrA["ANSWER_TEXT"]),true,50);
					}

					if (strlen(trim($arrA["USER_DATE"]))>0)
					{
						$arResultAnswer["USER_TEXT"] = $DB->FormatDate(
							$arrA["USER_DATE"],
							FORMAT_DATETIME,
							(MakeTimeStamp($arrA["USER_TEXT"])+date('Z'))%86400 == 0 ? FORMAT_DATE : FORMAT_DATETIME
						);
					}

					if ($arParams["SHOW_ANSWER_VALUE"]=="Y")
					{
						$arResultAnswer["ANSWER_VALUE"] = TxtToHTML(trim($arrA["ANSWER_VALUE"]),true,50);
					}

					if (intval($arrA["USER_FILE_ID"])>0)
					{
						if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
						{
							$arResultAnswer["ANSWER_IMAGE"] = array();
							$arResultAnswer["ANSWER_IMAGE"]["ID"] = $arrA["USER_FILE_ID"];
							$arImage = CFile::GetFileArray($arrA["USER_FILE_ID"]);
							$arResultAnswer["ANSWER_IMAGE"]["URL"] = $arImage["SRC"];

							if(substr($arImage["SRC"], 0, 1) == "/")
							{
								$arSize = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"].$arImage["SRC"]);
								if (is_array($arSize))
								{
									list(
										$arResultAnswer["ANSWER_IMAGE"]["WIDTH"],
										$arResultAnswer["ANSWER_IMAGE"]["HEIGHT"],
										$arResultAnswer["ANSWER_IMAGE"]["TYPE"],
										$arResultAnswer["ANSWER_IMAGE"]["ATTR"]
									) = $arSize;
								}
							}
							else
							{
								$arResultAnswer["ANSWER_IMAGE"]["WIDTH"] = $arImage["WIDTH"];
								$arResultAnswer["ANSWER_IMAGE"]["HEIGHT"] = $arImage["HEIGHT"];
								$arResultAnswer["ANSWER_IMAGE"]["TYPE"] = false;
								$arResultAnswer["ANSWER_IMAGE"]["ATTR"] = false;
							}

							$out = "";

							$arQuestion = $arResult["arQuestions"][$FIELD_SID];
							$arrResultAnswer = $arResult["arrVALUES"][$arQuestion["ID"]];
							if (is_array($arrResultAnswer))
							{
								foreach ($arrResultAnswer as $key => $arrAns)
								{
									if (strlen(trim($arrAns["USER_TEXT"]))>0)
									{
										if (intval($arrAns["USER_FILE_ID"])>0)
										{
											if ($arrAns["USER_FILE_IS_IMAGE"]=="Y" && $USER->IsAdmin())
												$out .= htmlspecialcharsbx($arrAns["USER_TEXT"])."<br />";
										}
										else $out .= TxtToHTML($arrAns["USER_TEXT"],true,50)."<br />";
									}

									if (strlen(trim($arrAns["ANSWER_TEXT"]))>0)
									{
										$answer = "[<span class='form-anstext'>".TxtToHTML($arrAns["ANSWER_TEXT"],true,50)."</span>]";
										if (strlen(trim($arrAns["ANSWER_VALUE"]))>0) $answer .= "&nbsp;"; else $answer .= "<br />";
										$out .= $answer;
									}

									if ($arParams["SHOW_ANSWER_VALUE"]=="Y")
									{
										if (strlen(trim($arrAns["ANSWER_VALUE"]))>0)
											$out .= "(<span class='form-ansvalue'>".TxtToHTML($arrAns["ANSWER_VALUE"],true,50)."</span>)<br />";
									}

									if (intval($arrAns["USER_FILE_ID"])>0)
									{
										if ($arrAns["USER_FILE_IS_IMAGE"]=="Y")
										{
											$out .= CFile::ShowImage($arrAns["USER_FILE_ID"], 0, 0, "border=0", "", true);
										}
										else
										{
											$file_link = "/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arrAns["USER_FILE_HASH"]."&lang=".LANGUAGE_ID;

											$out .= "<a title=\"".GetMessage("FORM_VIEW_FILE")."\" target=\"_blank\" href=\"".$file_link."\">".htmlspecialcharsbx($arrAns["USER_FILE_NAME"])."</a><br />(";
											$out .= CFile::FormatSize($arrAns["USER_FILE_SIZE"]);
											$out .= ")<br />[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arrAns["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" href=\"".$file_link."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]";
										} //endif;
									} //endif;
								} //endforeach;
							} //endif;

							$arResultAnswer["ANSWER_IMAGE"]["HTML_CODE"] = $out;
						}
						else
						{
							$arResultAnswer["ANSWER_FILE"] = array();
							$arResultAnswer["ANSWER_FILE"]["URL"] = "/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arrA["USER_FILE_HASH"]."&lang=".LANGUAGE_ID;
							$arResultAnswer["ANSWER_FILE"]["NAME"] = htmlspecialcharsbx($arrA["USER_FILE_NAME"]);
							$arResultAnswer["ANSWER_FILE"]["SIZE"] = $arrA["USER_FILE_SIZE"];
							$arResultAnswer["ANSWER_FILE"]["SIZE_FORMATTED"] = CFile::FormatSize($arrA["USER_FILE_SIZE"]);
						} //endif;
					} //endif;

					$arResultAnswers[] = $arResultAnswer;

				} //endforeach;
			} //endif (is_array());

			$arResult["RESULT"][$FIELD_SID]["ANSWER_VALUE"] = $arResultAnswers;//count($arResultAnswers) > 1 ? $arResultAnswers : $arResultAnswers[0];

			// field image. not used in default template, but may be inserted in custom
			if (intval($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"])>0)
			{
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ID"] = $arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]; // image id
				$arImage = CFile::GetFileArray($arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ID"]);
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"] = $arImage["SRC"]; // image url

				// image params
				if (substr($arImage["SRC"], 0, 1) == "/")
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

				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HTML_CODE"] = CFile::ShowImage($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]); //  formatted image code
			}
		}

		// go
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