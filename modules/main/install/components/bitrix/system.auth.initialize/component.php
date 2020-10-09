<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arParams["USERID_VAR"] = trim($arParams["USERID_VAR"]);
if($arParams["USERID_VAR"] == '')
{
	$arParams["USERID_VAR"] = "user_id";
}

$arParams["CHECKWORD_VAR"] = trim($arParams["CHECKWORD_VAR"]);
if($arParams["CHECKWORD_VAR"] == '')
{
	$arParams["CHECKWORD_VAR"] = "checkword";
}

$arResult["~USER_ID"] = $_REQUEST[$arParams["USERID_VAR"]];
$arResult["USER_ID"] = intval($arResult["~USER_ID"]);

$arResult["~CHECKWORD"] = trim($_REQUEST[$arParams["CHECKWORD_VAR"]]);
$arResult["CHECKWORD"] = htmlspecialcharsbx($arResult["~CHECKWORD"]);

$arResult["MESSAGE_CODE"] = array();
$arResult["SHOW_FORM"] = false;

if($USER->IsAuthorized())
{
	$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_AUTHORIZED")."<br>";
	$arResult["MESSAGE_CODE"][] = "E02";
}
else
{
	$rsUser = false;
	if($arResult["USER_ID"] > 0)
	{
		$rsUser = CUser::GetByID($arResult["~USER_ID"]);
	}

	if($rsUser && $arResult["USER"] = $rsUser->GetNext())
	{
		if($arResult["USER"]["LAST_LOGIN"] <> '')
		{
			$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_AUTH_SUCCESS")."<br>";
			$arResult["MESSAGE_CODE"][] = "E30";
		}
		elseif($arResult["USER"]["ACTIVE"] !== "Y")
		{
			$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_INACTIVE")."<br>";
			$arResult["MESSAGE_CODE"][] = "E03";
		}

		$salt = mb_substr($arResult["USER"]["CHECKWORD"], 0, 8);

		if($arResult["~CHECKWORD"] == '')
		{
			$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_CHECKWORD_EMPTY")."<br>";
			$arResult["MESSAGE_CODE"][] = "E04";
		}
		elseif($arResult["USER"]["CONFIRM_CODE"] != $arResult["~CHECKWORD"] && $arResult["USER"]["CHECKWORD"] != $salt.md5($salt.$arResult["~CHECKWORD"]))
		{
			$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_CHECKWORD_WRONG");
			$arResult["MESSAGE_CODE"][] = "E05";
		}

		if(empty($arResult["MESSAGE_CODE"]) && $_SERVER["REQUEST_METHOD"] == "POST" && $_POST["confirm"] <> '' && check_bitrix_sessid())
		{
			$arResult["USER"]["NAME"] = trim($_POST["NAME"]);
			$arResult["USER"]["LAST_NAME"] = trim($_POST["LAST_NAME"]);
			$arResult["USER"]["WORK_COMPANY"] = trim($_POST["WORK_COMPANY"]);
			$arResult["USER"]["WORK_PHONE"] = trim($_POST["WORK_PHONE"]);

			$arResult["PASSWORD"] = $_POST["PASSWORD"];
			$arResult["CONFIRM_PASSWORD"] = $_POST["CONFIRM_PASSWORD"];

			if($arResult["USER"]["NAME"] == '')
			{
				$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_NAME_EMPTY")."<br>";
				$arResult["MESSAGE_CODE"][] = "E21";
				$arResult["SHOW_FORM"] = true;
			}

			if($arResult["USER"]["LAST_NAME"] == '')
			{
				$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_LAST_NAME_EMPTY")."<br>";
				$arResult["MESSAGE_CODE"][] = "E22";
				$arResult["SHOW_FORM"] = true;
			}

			$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy($arResult["USER"]["ID"]);

			if($_POST["PASSWORD"] == '')
			{
				$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_PASSWORD_EMPTY")."<br>";
				$arResult["MESSAGE_CODE"][] = "E07";
				$arResult["SHOW_FORM"] = true;
			}
			elseif($_POST["PASSWORD"] !== $_POST["CONFIRM_PASSWORD"])
			{
				$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_PASSWORD_NOT_CONFIRMED")."<br>";
				$arResult["MESSAGE_CODE"][] = "E08";
				$arResult["SHOW_FORM"] = true;
			}

			if(empty($arResult["MESSAGE_CODE"]))
			{
				$arFields = array(
					"CONFIRM_CODE" => "",
					"PASSWORD" => $_POST["PASSWORD"],
					"NAME" => $arResult["USER"]["NAME"],
					"LAST_NAME" => $arResult["USER"]["LAST_NAME"]
				);

				if (trim($_POST["WORK_COMPANY"]) <> '')
				{
					$arFields["WORK_COMPANY"] = trim($_POST["WORK_COMPANY"]);
				}

				if (trim($_POST["WORK_PHONE"]) <> '')
				{
					$arFields["WORK_PHONE"] = trim($_POST["WORK_PHONE"]);
				}

				if (is_array($_FILES["PERSONAL_PHOTO"]))
				{
					$arFields["PERSONAL_PHOTO"] = $_FILES["PERSONAL_PHOTO"];
				}

				$obUser = new CUser;
				$obUser->Update($arResult["USER"]["ID"], $arFields);
				$strError = $obUser->LAST_ERROR;

				if ($strError == '')
				{
					$db_events = GetModuleEvents("main", "OnUserInitialize", true);
					foreach($db_events as $arEvent)
					{
						ExecuteModuleEventEx($arEvent, array($arResult["USER"]["ID"], $arFields));
					}

					$obUser->Authorize($arResult["USER"]["ID"], $_POST["USER_REMEMBER"] == "Y");

					$SITE_DIR = SITE_DIR;

					if (!empty($arResult["USER"]["LID"]))
					{
						$rsSite = CSite::GetByID($arResult["USER"]["LID"]);
						if (
							($arSite = $rsSite->Fetch())
							&& !empty($arSite["DIR"])
						)
						{
							$SITE_DIR = $arSite["DIR"];
						}
					}

					LocalRedirect($SITE_DIR);
				}
				else
				{
					$arResult["MESSAGE_TEXT"] .= $strError;
					$arResult["MESSAGE_CODE"][] = "E10";
					$arResult["SHOW_FORM"] = true;
				}
			}
		}

		if(empty($arResult["MESSAGE_CODE"]))
		{
			$arResult["SHOW_FORM"] = true;
		}
	}
	else
	{
		$arResult["MESSAGE_TEXT"] .= GetMessage("CC_MAIN_REG_INIT_MESSAGE_NO_USER");
		$arResult["MESSAGE_CODE"][] = "E01";
	}
}

$arResult["~FORM_ACTION"] = $APPLICATION->GetCurPageParam();
$arResult["FORM_ACTION"] = htmlspecialcharsbx($arResult["~FORM_ACTION"]);

$this->IncludeComponentTemplate();
