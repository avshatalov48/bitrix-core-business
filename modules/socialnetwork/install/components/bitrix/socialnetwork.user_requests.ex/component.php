<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!function_exists('getRelatedUser'))
{
	function getRelatedUser($firstUserID, $relationID)
	{
		$arRel = CSocNetUserRelations::GetByID($relationID);

		if ($arRel)
		{
			$secondUserID = ($firstUserID == $arRel["FIRST_USER_ID"]) ? $arRel["SECOND_USER_ID"] : $arRel["FIRST_USER_ID"];

			$dbUser = CUser::GetByID($secondUserID);
			if ($arUser = $dbUser->Fetch())
				return CUser::FormatName(CSite::GetNameFormat(false), $arUser, true);
			else
				return false;
		}
		else
			return false;
	}
}

if (!function_exists('getRelatedGroup'))
{
	function getRelatedGroup($relationID)
	{
		$arRel = CSocNetUserToGroup::GetByID($relationID);

		if ($arRel)
			return $arRel["GROUP_NAME"];
		else
			return false;
	}
}

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");
$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] === "N" ? false : true);

$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] < 0)
	return false;

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"] ?? '');
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"] ?? 0);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["THUMBNAIL_LIST_SIZE"] = intval($arParams["THUMBNAIL_LIST_SIZE"] ?? 0);
if ($arParams["THUMBNAIL_LIST_SIZE"] <= 0)
	$arParams["THUMBNAIL_LIST_SIZE"] = 30;

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"] ?? '');

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] !== "N" ? true : false;

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (!$USER->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	/***********************  ACTIONS  *******************************/
	if (
		$_SERVER["REQUEST_METHOD"] === "POST"
		&& (in_array($_POST["action"], array("accept", "reject"))) 
		&& check_bitrix_sessid()
	)
	{
		$errorMessage = "";

		$arUserRelationIDs = array();
		$arGroupRelationIDs = array();
		if ($errorMessage == '')
		{
			for ($i = 0; $i <= intval($_POST["max_count"]); $i++)
			{
				if (($_POST["checked_".$i] ?? null) === "Y")
				{
					if ($_POST["type_".$i] === "INVITE_GROUP")
					{
						$arGroupRelationIDs[] = intval($_POST["id_".$i]);
					}
					else
					{
						$arUserRelationIDs[] = intval($_POST["id_".$i]);
					}
				}
			}
		}

		if (count($arUserRelationIDs) <= 0 && count($arGroupRelationIDs) <= 0)
		{
			$errorMessage .= GetMessage("SONET_URE_NOT_SELECTED").". ";
		}

		if ($errorMessage == '')
		{
			$type = ($_POST["type"] === "out" ? "out" : "in");

			if ($type === "in")
			{
				if (count($arGroupRelationIDs) > 0)
				{
					foreach ($arGroupRelationIDs as $relationID)
					{
						$errorMessage = "";
						if ($_POST["action"] === "accept")
						{
							if (!CSocNetUserToGroup::UserConfirmRequestToBeMember($arParams["USER_ID"], $relationID, $bAutoSubscribe))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}
							}
						}
						elseif ($_POST["action"] === "reject")
						{
							if (!CSocNetUserToGroup::UserRejectRequestToBeMember($arParams["USER_ID"], $relationID))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}
							}
						}
					}
				}

				if (count($arUserRelationIDs) > 0)
				{
					$errorMessage = "";
					foreach ($arUserRelationIDs as $relationID)
					{
						if ($_POST["action"] === "accept")
						{
							if (!CSocNetUserRelations::ConfirmRequestToBeFriend($arParams["USER_ID"], $relationID, $bAutoSubscribe))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}
							}
						}
						elseif ($_POST["action"] === "reject")
						{
							$arRelation = CSocNetUserRelations::GetByID($relationID);

							if (!$arRelation)
							{
								continue;
							}

							if (!CSocNetUserRelations::RejectRequestToBeFriend($arParams["USER_ID"], $relationID))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}
							}
						}
					}
				}
			}
			else //outgoing
			{
				if ($_POST["action"] === "reject")
				{
					// groups
					if (count($arGroupRelationIDs) > 0)
					{
						$errorMessage = "";
						foreach($arGroupRelationIDs as $relationID)
						{
							$arRelation = CSocNetUserToGroup::GetByID($relationID);
							if (!$arRelation)
							{
								continue;
							}

							if (!CSocNetUserToGroup::Delete($arRelation["ID"]))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}

								if ($errorMessage == '')
								{
									$errorMessage .= GetMessage("SONET_GRE_CANT_DELETE_INVITATION", array("#RELATION_ID#" => $arRelation["ID"]));
								}
							}
						}
					}

					// users
					if (count($arUserRelationIDs) > 0)
					{
						$errorMessage = "";
						foreach($arUserRelationIDs as $relationID)
						{
							$arRelation = CSocNetUserRelations::GetByID($relationID);

							if (!$arRelation)
							{
								continue;
							}

							if (!CSocNetUserRelations::Delete($arRelation["ID"]))
							{
								if ($e = $APPLICATION->GetException())
								{
									$errorMessage .= $e->GetString();
								}

								if ($errorMessage == '')
								{
									$errorMessage .= GetMessage("SONET_GRE_CANT_DELETE_INVITATION", array("#RELATION_ID#" => $arRelation["ID"]));
								}
							}
						}
					}
				}
			}
		}

		if (($_POST["ajax_request"] ?? null) === "Y")
		{
			$APPLICATION->RestartBuffer();
			echo CUtil::PhpToJsObject(array(
				'MESSAGE' => ($errorMessage <> '' ? 'ERROR' : 'SUCCESS'),
				'ERROR_MESSAGE' => ($errorMessage <> '' ? $errorMessage : ''),
			));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
			die();
		}
	}

	//Handling confirmation with e-mail links
	if (
		$_SERVER["REQUEST_METHOD"] === "GET"
		&& isset($_GET["CONFIRM"])
	)
	{
		$errorMessage = "";

		//ACCESS CHECK: only user himself and socnet admin are allowed to confirm
		if (
			$USER->GetID() != $arParams["USER_ID"]
			&& !CSocNetUser::IsCurrentUserModuleAdmin()
		)
		{
			$errorMessage = GetMessage("SONET_URE_NO_PERMS");
		}

		if (isset($_GET["INVITE_GROUP"]))
		{
			$relationID = intval($_GET["INVITE_GROUP"]);

			if ($_GET["CONFIRM"] === "Y")
			{
				if (CSocNetUserToGroup::UserConfirmRequestToBeMember($arParams["USER_ID"], $relationID, $bAutoSubscribe))
				{
					$infoMessage = GetMessage("SONET_URE_GROUP_CONFIRM", array('#GROUP#' => getRelatedGroup($relationID)));
				}
				else
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
			elseif ($_GET["CONFIRM"] === "N")
			{
				$group = getRelatedGroup($relationID);
				
				if ($group && CSocNetUserToGroup::UserRejectRequestToBeMember($arParams["USER_ID"], $relationID))
				{
					$infoMessage = GetMessage("SONET_URE_GROUP_REJECT", array("#GROUP#" => $group));
				}
				else
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
		}

		//friendship
		if (isset($_GET["INVITE_USER"]))
		{
			$relationID = intval($_GET["INVITE_USER"]);

			if ($_GET["CONFIRM"] === "Y")
			{
				if (CSocNetUserRelations::ConfirmRequestToBeFriend($arParams["USER_ID"], $relationID, $bAutoSubscribe))
				{
					$infoMessage = GetMessage("SONET_URE_FRIEND_CONFIRM", array("#USER#" => getRelatedUser($arParams["USER_ID"], $relationID)));
				}
				else
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
			elseif ($_GET["CONFIRM"] === "N")
			{
				$secondUser = getRelatedUser($arParams["USER_ID"], $relationID);
				if ($secondUser && CSocNetUserRelations::RejectRequestToBeFriend($arParams["USER_ID"], $relationID))
				{
					$infoMessage = GetMessage("SONET_URE_FRIEND_REJECT", array("#USER#" => $secondUser));
				}
				else
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}
		}
	}

	/*********************  END ACTIONS  *****************************/

	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arResult["User"] = $dbUser->GetNext();

	$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult['User'], $bUseLogin);

	if ($arParams["SET_TITLE"] === "Y")
		$APPLICATION->SetTitle(htmlspecialcharsback($arResult["User"]["NAME_FORMATTED"]).": ".GetMessage("SONET_URE_PAGE_TITLE"));

	if ($arParams["SET_NAV_CHAIN"] !== "N")
		$APPLICATION->AddChainItem(GetMessage("SONET_URE_PAGE_TITLE"));

	if (is_array($arResult["User"]))
	{
		$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($USER->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

		if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser"])
		{
			$parser = new CSocNetTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

			/*********************  Incoming Requests  ***********************/

			/* Friends Incoming */

			$arResult["RequestsIn"] = array();

			$arTmpResult = [
				'RequestsIn' => [],
				'RequestsOut' => [],
			];

			$dbUserRequests = CSocNetUserRelations::GetList(
				array("DATE_UPDATE" => "ASC"),
				array(
					"SECOND_USER_ID" => $arParams["USER_ID"],
					"RELATION" => SONET_RELATIONS_REQUEST
				),
				false,
				false,
				array("ID", "FIRST_USER_ID", "MESSAGE", "FIRST_USER_NAME", "DATE_UPDATE", "FIRST_USER_LAST_NAME", "FIRST_USER_FIRST_NAME", "FIRST_USER_LOGIN", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER", "FIRST_USER_IS_ONLINE")
			);

			while ($arUserRequest = $dbUserRequests->GetNext())
			{
				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequest["FIRST_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($arParams["USER_ID"], $arUserRequest["FIRST_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
				{
					if (intval($arUserRequest["FIRST_USER_PERSONAL_PHOTO"]) <= 0)
					{
						switch ($arUserRequest["FIRST_USER_PERSONAL_GENDER"])
						{
							case "M":
								$suffix = "male";
								break;
							case "F":
								$suffix = "female";
								break;
							default:
								$suffix = "unknown";
						}

						$arUserRequest["FIRST_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
					}

					$arImage = CFile::ResizeImageGet(
						$arUserRequest["FIRST_USER_PERSONAL_PHOTO"],
						array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
						BX_RESIZE_IMAGE_EXACT,
						false
					);

				}

				$arTmpUser = array(
					"NAME" => $arUserRequest["FIRST_USER_NAME"],
					"LAST_NAME" => $arUserRequest["FIRST_USER_LAST_NAME"],
					"FIRST_NAME" => $arUserRequest["FIRST_USER_FIRST_NAME"],
					"LOGIN" => $arUserRequest["FIRST_USER_LOGIN"],
				);

				$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);		
				
				$arEventTmp = array(
					"EVENT_TYPE" => "INVITE_USER",
					"ID" => $arUserRequest["ID"],
					"USER_ID" => $arUserRequest["FIRST_USER_ID"],
					"USER_NAME" => $arUserRequest["FIRST_USER_NAME"],
					"USER_LAST_NAME" => $arUserRequest["FIRST_USER_LAST_NAME"],
					"USER_FIRST_NAME" => $arUserRequest["FIRST_USER_FIRST_NAME"],
					"USER_LOGIN" => $arUserRequest["FIRST_USER_LOGIN"],
					"USER_NAME_FORMATTED" => $strNameFormatted,
					"USER_PERSONAL_PHOTO" => $arUserRequest["FIRST_USER_PERSONAL_PHOTO"],
					"USER_PERSONAL_PHOTO_IMG" => $arImage,
					"USER_PROFILE_URL" => $pu,
					"SHOW_PROFILE_LINK" => $canViewProfile,
					"IS_ONLINE" => ($arUserRequest["FIRST_USER_IS_ONLINE"] === "Y"),
					"DATE_UPDATE" => $arUserRequest["DATE_UPDATE"],
					"MESSAGE" => $parser->convert(
						$arUserRequest["~MESSAGE"],
						false,
						array(),
						array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"BIU" => "Y",
							"IMG" => "Y",
							"LIST" => "Y",
							"QUOTE" => "Y",
							"CODE" => "Y",
							"FONT" => "Y",
							"SMILES" => "Y",
							"UPLOAD" => "N",
							"NL2BR" => "N"
						)
					),
				);

				$arTmpResult["RequestsIn"][] = $arEventTmp;
			}

			/* Groups Incoming */

			$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);

			$dbRequests = CSocNetUserToGroup::GetList(
				array("DATE_CREATE" => "ASC"),
				array(
					"ROLE" => SONET_ROLES_REQUEST,
					"USER_ID" => $arParams["USER_ID"],
					"!INITIATED_BY_USER_ID" => $arParams["USER_ID"]
				),
				false,
				$arNavParams,
				array("ID", "GROUP_ID", "USER_ID", "DATE_CREATE", "DATE_UPDATE", "MESSAGE", "INITIATED_BY_USER_ID", "INITIATED_BY_USER_NAME", "INITIATED_BY_USER_LAST_NAME", "INITIATED_BY_USER_SECOND_NAME", "INITIATED_BY_USER_LOGIN", "INITIATED_BY_USER_PHOTO", "INITIATED_BY_USER_GENDER")
			);

			if ($dbRequests)
			{
				while ($arRequest = $dbRequests->GetNext())
				{
					$gu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arRequest["GROUP_ID"]));

					$arGroup = CSocNetGroup::GetByID($arRequest["GROUP_ID"]);

					$arImage = array();
					if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
					{
						if (intval($arGroup["IMAGE_ID"]) <= 0)
							$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

						$arImage = CFile::ResizeImageGet(
							$arGroup["IMAGE_ID"],
							array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
					}

					$arEventTmp = array(
						"EVENT_TYPE" => "INVITE_GROUP",	                    
						"ID" => $arRequest["ID"],
						"USER_ID" => $arRequest["USER_ID"],
						"GROUP_ID" => $arRequest["GROUP_ID"],
						"GROUP_URL" => $gu,
						"GROUP_NAME" => $arGroup["NAME"],
						"GROUP_IMG" => $arImage,
						"DATE_CREATE" => $arRequest["DATE_CREATE"],
						"MESSAGE" => $parser->convert(
							$arRequest["~MESSAGE"],
							false,
							array(),
							array(
								"HTML" => "N",
								"ANCHOR" => "Y",
								"BIU" => "Y",
								"IMG" => "Y",
								"LIST" => "Y",
								"QUOTE" => "Y",
								"CODE" => "Y",
								"FONT" => "Y",
								"SMILES" => "Y",
								"UPLOAD" => "N",
								"NL2BR" => "N"
							)
						),
					);

					$arTmpResult["RequestsIn"][] = $arEventTmp;
				}
			}

			$rsRequestsIn = new CDBResult;
			$rsRequestsIn->InitFromArray($arTmpResult["RequestsIn"]);
			$rsRequestsIn->NavStart();
			while($arRecord = $rsRequestsIn->GetNext())
				$arResult["RequestsIn"]["List"][] = $arRecord;

			$arResult["RequestsIn"]["NAV_STRING"] = $rsRequestsIn->GetPageNavStringEx($navComponentObject, GetMessage("SONET_URE_NAV"), "", false);

			/*********************  Outogoing Requests  ***********************/

			/* Friends Outgoing */

			$arResult["RequestsOut"] = array();

			$dbUserRequests = CSocNetUserRelations::GetList(
				array("DATE_UPDATE" => "ASC"),
				array(
					"FIRST_USER_ID" => $arParams["USER_ID"],
					"RELATION" => SONET_RELATIONS_REQUEST
				),
				false,
				false,
				array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "MESSAGE", "SECOND_USER_NAME", "DATE_UPDATE", "SECOND_USER_LAST_NAME", "SECOND_USER_SECOND_NAME", "SECOND_USER_LOGIN", "SECOND_USER_PERSONAL_PHOTO", "SECOND_USER_PERSONAL_GENDER", "SECOND_USER_IS_ONLINE")
			);

			while ($arUserRequest = $dbUserRequests->GetNext())
			{
				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequest["SECOND_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($arParams["USER_ID"], $arUserRequest["SECOND_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
				{
					if (intval($arUserRequest["SECOND_USER_PERSONAL_PHOTO"]) <= 0)
					{
						switch ($arUserRequest["SECOND_USER_PERSONAL_GENDER"])
						{
							case "M":
								$suffix = "male";
								break;
							case "F":
								$suffix = "female";
								break;
							default:
								$suffix = "unknown";
						}

						$arUserRequest["SECOND_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
					}

					$arImage = CFile::ResizeImageGet(
						$arUserRequest["SECOND_USER_PERSONAL_PHOTO"],
						array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
				}

				$arTmpUser = array(
					"NAME" => $arUserRequest["SECOND_USER_NAME"],
					"LAST_NAME" => $arUserRequest["SECOND_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequest["SECOND_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequest["SECOND_USER_LOGIN"],
				);

				$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);		
				
				$arEventTmp = array(
					"EVENT_TYPE" => "INVITE_USER",
					"ID" => $arUserRequest["ID"],
					"USER_ID" => $arUserRequest["SECOND_USER_ID"],
					"USER_NAME" => $arUserRequest["SECOND_USER_NAME"],
					"USER_LAST_NAME" => $arUserRequest["SECOND_USER_LAST_NAME"],
					"USER_SECOND_NAME" => $arUserRequest["SECOND_USER_SECOND_NAME"],
					"USER_LOGIN" => $arUserRequest["SECOND_USER_LOGIN"],
					"USER_NAME_FORMATTED" => $strNameFormatted,
					"USER_PERSONAL_PHOTO" => $arUserRequest["SECOND_USER_PERSONAL_PHOTO"],
					"USER_PERSONAL_PHOTO_IMG" => $arImage,
					"USER_PROFILE_URL" => $pu,
					"SHOW_PROFILE_LINK" => $canViewProfile,
					"IS_ONLINE" => ($arUserRequest["SECOND_USER_IS_ONLINE"] === "Y"),
					"DATE_UPDATE" => $arUserRequest["DATE_UPDATE"],
					"MESSAGE" => $parser->convert(
						$arUserRequest["~MESSAGE"],
						false,
						array(),
						array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"BIU" => "Y",
							"IMG" => "Y",
							"LIST" => "Y",
							"QUOTE" => "Y",
							"CODE" => "Y",
							"FONT" => "Y",
							"SMILES" => "Y",
							"UPLOAD" => "N",
							"NL2BR" => "N"
						)
					),
				);

				$arTmpResult["RequestsOut"][] = $arEventTmp;
			}

			/* Groups Outgoing */

			$dbRequests = CSocNetUserToGroup::GetList(
				array("DATE_CREATE" => "ASC"),
				array(
					"ROLE" => SONET_ROLES_REQUEST,
					"USER_ID" => $arParams["USER_ID"],
					"INITIATED_BY_TYPE" => "U"
				),
				false,
				$arNavParams,
				array("ID", "GROUP_ID", "USER_ID", "DATE_CREATE", "DATE_UPDATE", "MESSAGE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER")
			);

			if ($dbRequests)
			{
				while ($arRequest = $dbRequests->GetNext())
				{
					$gu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arRequest["GROUP_ID"]));

					$arGroup = CSocNetGroup::GetByID($arRequest["GROUP_ID"]);

					$arImage = array();
					if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
					{
						if (intval($arGroup["IMAGE_ID"]) <= 0)
							$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

						$arImage = CFile::ResizeImageGet(
							$arGroup["IMAGE_ID"],
							array("width" => $arParams["THUMBNAIL_LIST_SIZE"], "height" => $arParams["THUMBNAIL_LIST_SIZE"]),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
					}

					$arEventTmp = array(
						"EVENT_TYPE" => "INVITE_GROUP",	                    
						"ID" => $arRequest["ID"],
						"USER_ID" => $arRequest["USER_ID"],
						"GROUP_ID" => $arRequest["GROUP_ID"],
						"GROUP_URL" => $gu,
						"GROUP_NAME" => $arGroup["NAME"],
						"GROUP_IMG" => $arImage,
						"DATE_CREATE" => $arRequest["DATE_CREATE"],
						"MESSAGE" => $parser->convert(
							$arRequest["~MESSAGE"],
							false,
							array(),
							array(
								"HTML" => "N",
								"ANCHOR" => "Y",
								"BIU" => "Y",
								"IMG" => "Y",
								"LIST" => "Y",
								"QUOTE" => "Y",
								"CODE" => "Y",
								"FONT" => "Y",
								"SMILES" => "Y",
								"UPLOAD" => "N",
								"NL2BR" => "N"
							)
						),
					);

					$arTmpResult["RequestsOut"][] = $arEventTmp;
				}
			}

			$rsRequestsOut = new CDBResult;
			$rsRequestsOut->InitFromArray($arTmpResult["RequestsOut"]);
			$rsRequestsOut->NavStart();
			while($arRecord = $rsRequestsOut->GetNext())
				$arResult["RequestsOut"]["List"][] = $arRecord;

			$arResult["RequestsOut"]["NAV_STRING"] = $rsRequestsOut->GetPageNavStringEx($navComponentObject, GetMessage("SONET_URE_NAV"), "", false);
		}
		else
			$arResult["FatalError"] = GetMessage("SONET_URE_NO_PERMS");
	}
	else
		$arResult["FatalError"] = GetMessage("SONET_URE_NO_USER");

	$arResult["InfoMessage"] = $infoMessage ?? '';
	$arResult["ErrorMessage"] = $errorMessage ?? '';
}

$this->IncludeComponentTemplate();
