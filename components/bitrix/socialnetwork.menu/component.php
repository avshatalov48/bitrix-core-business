<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/socialnetwork.menu/include.php');

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

$arStaticTabs = array("general", "friends", "groups", "users", "messages_users", "messages_input", "messages_output", "user_ban", "log", "subscribe", "bizproc");

$arResult = array();

$arParams["ID"] = (isset($arParams["ID"]) ? $arParams["ID"] : "sonetmenuholder1");

$arParams["ENTITY_TYPE"] = ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? SONET_ENTITY_GROUP : ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER ? SONET_ENTITY_USER : "M"));

$arParams["ENTITY_ID"] = (intval($arParams["ENTITY_ID"]) > 0 ? intval($arParams["ENTITY_ID"]) : false);
$arParams["MAX_ITEMS"] = (intval($arParams["MAX_ITEMS"])>0 && intval($arParams["MAX_ITEMS"])<10 ? intval($arParams["MAX_ITEMS"]) : 6);
$arParams["UPD_URL"] = $arResult["UPD_URL"] = POST_FORM_ACTION_URI;

$parts = explode("?", $arResult['UPD_URL'], 2);
if (count($parts) == 2)
{
	$string = $parts[0]."?";
	$arTmp = array();
	$params = explode("&", $parts[1]);
	foreach ($params as $param)
	{
		$tmp = explode("=", $param);
		if (count($tmp) == 2)
		{
			if ($tmp[0] != "logout")
				$arTmp[] = $param;
		}
		else
			$arTmp[] = $param;
	}
	$string .= implode("&", $arTmp);
	$arParams["UPD_URL"] = $arResult["UPD_URL"] = $string;
}

$arParams["LogName"] = ($arParams["LogName"] <> '' ? $arParams["LogName"] : GetMessage(\Bitrix\Main\ModuleManager::isModuleInstalled('intranet' ? "SONET_SM_M_LOG2" : "SONET_SM_M_LOG")));
if (in_array($arParams["ENTITY_TYPE"], array(SONET_ENTITY_GROUP, SONET_ENTITY_USER)))
	$arParams["GeneralName"] = ($arParams["GeneralName"] <> '' ? $arParams["GeneralName"] : GetMessage("SONET_SM_GENERAL_".$arParams["ENTITY_TYPE"]));
$arParams["FriendsName"] = ($arParams["FriendsName"] <> '' ? $arParams["FriendsName"] : GetMessage("SONET_SM_U_FRIENDS"));
$arParams["GroupsName"] = ($arParams["GroupsName"] <> '' ? $arParams["GroupsName"] : GetMessage("SONET_SM_U_GROUPS"));
$arParams["UsersName"] = ($arParams["UsersName"] <> '' ? $arParams["UsersName"] : GetMessage("SONET_SM_G_USERS"));

if (
	isset($arParams["arResult"])
	&& isset($arParams["arResult"]["Urls"])
	&& isset($arParams["arResult"]["Urls"]["content_search"])
)
{
	$arParams["arResult"]["Urls"]["search"] = $arParams["arResult"]["Urls"]["content_search"];
}

if (
	isset($arParams["arResult"])
	&& isset($arParams["arResult"]["CanView"])
	&& isset($arParams["arResult"]["CanView"]["content_search"])
)
{
	$arParams["arResult"]["CanView"]["search"] = $arParams["arResult"]["CanView"]["content_search"];
}

if (
	isset($arParams["arResult"])
	&& isset($arParams["arResult"]["Title"])
	&& isset($arParams["arResult"]["Title"]["content_search"])
)
{
	$arParams["arResult"]["Title"]["search"] = $arParams["arResult"]["Title"]["content_search"];
}

if ($arParams["PAGE_ID"] == "group_content_search")
{
	$arParams["PAGE_ID"] = "group_search";
}
elseif ($arParams["PAGE_ID"] == "user_content_search")
{
	$arParams["PAGE_ID"] = "user_search";
}
	
$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) ? $arParams["USE_MAIN_MENU"] : false);

if (
	$arParams["USE_MAIN_MENU"] == "Y" 
	&& !array_key_exists("MAIN_MENU_TYPE", $arParams)
)
{
	$arParams["MAIN_MENU_TYPE"] = "left";
}

$arResult["ID"] = $arParams["ID"];

$errorMessage = false;

if (!$arParams["ENTITY_ID"])
	$errorMessage = GetMessage("SONET_SM_ENTITY_ID_EMPTY");

if (!$errorMessage)
{
	if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
	{
		$arGroup = CSocNetGroup::GetByID($arParams["ENTITY_ID"]);
		if (!$arGroup)
			$errorMessage = GetMessage("SONET_SM_ENTITY_ID_INCORRECT");
	}
	elseif (
		!array_key_exists("arResult", $arParams) 
		|| !array_key_exists("User", $arParams["arResult"]) 
		|| !is_array($arParams["arResult"]["User"])
	)
	{
		$rsUser = CUser::GetByID($arParams["ENTITY_ID"]);
		$arUser = $rsUser->Fetch();
		if (!$arUser)
			$errorMessage = GetMessage("SONET_SM_ENTITY_ID_INCORRECT");
	}
}

if (!$errorMessage)
{
	if(
		$USER->IsAuthorized() 
		&& (
			$GLOBALS["USER"]->IsAdmin() 
			|| (
				CModule::IncludeModule('socialnetwork') 
				&& CSocNetUser::IsCurrentUserModuleAdmin()
			)
		)
	)
		$arResult["PERMISSION"] = "X";
	elseif($USER->IsAuthorized())
	{
		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
		{
			if($arGroup["OWNER_ID"] == $USER->GetID())
				$arResult["PERMISSION"] = "W";
			else
				$arResult["PERMISSION"] = "R";
		}
		elseif ($USER->GetID() == $arParams["ENTITY_ID"])
			$arResult["PERMISSION"] = "W";
		else
			$arResult["PERMISSION"] = "R";
	}
	else
		$arResult["PERMISSION"] = "R";
}	

if ($arParams["arResult"]["HideArchiveLinks"])
	$arResult["PERMISSION"] = "R";

if(!$errorMessage && $USER->IsAuthorized() && $arResult["PERMISSION"] > "R")
{
	if($_SERVER['REQUEST_METHOD'] == 'POST' 
		&& array_key_exists("sm_action", $_REQUEST) 
		&& $_REQUEST["sm_action"] <> ''
		&& $_REQUEST["feature"] <> '' 
		&& !in_array($_REQUEST["feature"], $arStaticTabs)
	)
	{
		if (!array_key_exists($_REQUEST["feature"], $arSocNetFeaturesSettings))
		{
			$errorMessage = GetMessage("SONET_SM_FEATURE_INCORRECT");
		}
		elseif (!in_array($arParams["ENTITY_TYPE"], $arSocNetFeaturesSettings[$_REQUEST["feature"]]["allowed"]))
		{
			$errorMessage = GetMessage("SONET_SM_FEATURE_INACTIVE");
		}
	}

	if($_SERVER['REQUEST_METHOD'] == 'POST' 
		&& array_key_exists("sm_action", $_REQUEST) 
		&& $_REQUEST["sm_action"] == "update" 
		&& $_REQUEST["feature"] <> '' 
		&& check_bitrix_sessid()
		)
	{
		if (!$errorMessage)
		{
			$idTmp = CSocNetFeatures::SetFeature(
				$arParams["ENTITY_TYPE"],
				$arParams["ENTITY_ID"],
				$_REQUEST["feature"],
				true,
				($_REQUEST[$_REQUEST["feature"]."_name"] <> '') ? $_REQUEST[$_REQUEST["feature"]."_name"] : false
			);
			if ($idTmp && (!array_key_exists("hide_operations_settings", $arSocNetFeaturesSettings[$_REQUEST["feature"]]) || !$arSocNetFeaturesSettings[$_REQUEST["feature"]]["hide_operations_settings"]))
			{
				foreach ($arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"] as $operation => $perm)
				{
					if (
						!array_key_exists("restricted", $arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"][$operation]) 
						|| !in_array($key, $arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"][$operation]["restricted"][$arParams["ENTITY_TYPE"]])
					):
						$id1Tmp = CSocNetFeaturesPerms::SetPerm(
									$idTmp,
									$operation,
									$_REQUEST[$_REQUEST["feature"]."_".$operation."_perm"]
								);
						if (!$id1Tmp && $e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					endif;
				}
			}
			elseif ($e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();
		}

		$APPLICATION->RestartBuffer();
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
		$obJSPopup = new CJSPopup();

		if(!$errorMessage)
			$obJSPopup->Close();
		else
		{
			$obJSPopup->ShowValidationError($errorMessage);
			die();
		}
	}
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' 
		&& array_key_exists("sm_action", $_REQUEST) 
		&& $_REQUEST["sm_action"] == "update_menu" 
		&& check_bitrix_sessid()
		)
	{
		if (
			isset($_REQUEST["max_items"])
			&& intval($_REQUEST["max_items"]) > 0
		)
		{
			$arUserOptions = CUserOptions::GetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);

			$arNewUserOptions = array();
			
			if(!is_array($arUserOptions))
				$arUserOptions["FEATURES"] = Array();

			$arNewUserOptions = Array("FEATURES"=>$arUserOptions["FEATURES"], "MAX_ITEMS"=>intval($_REQUEST["max_items"]));
		
			CUserOptions::SetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], $arNewUserOptions, false, 0);

			$APPLICATION->RestartBuffer();
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
			$obJSPopup = new CJSPopup();
		}
		else
		{
			$errorMessage = GetMessage("SONET_SM_MAX_ITEMS_INCORRECT");		
			$APPLICATION->RestartBuffer();
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
			$obJSPopup = new CJSPopup();
		}
		
		if(!$errorMessage)
			$obJSPopup->Close();
		else
		{
			$obJSPopup->ShowValidationError($errorMessage);
			$obJSPopup->Close(false);			
			die();
		}
	}
	
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' 
		&& array_key_exists("sm_action", $_REQUEST) 
		&& $_REQUEST["sm_action"] == "clear_settings" 
		&& check_bitrix_sessid()
		)
	{
		CUserOptions::DeleteOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);
		$APPLICATION->RestartBuffer();
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
		$obJSPopup = new CJSPopup();
		
		if(!$errorMessage)
			$obJSPopup->Close();
		else
		{
			$obJSPopup->ShowValidationError($errorMessage);
			die();
		}
	
	}
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' 
		&& array_key_exists("sm_action", $_REQUEST) 
		&& $_REQUEST["sm_action"] == "delete" 
		&& $_REQUEST["feature"] <> ''
		&& check_bitrix_sessid()
		)
	{
		if (!$errorMessage)
		{
			$dbResultTmp = CSocNetFeatures::GetList(
					array(),
					array(
						"ENTITY_ID" => $arParams['ENTITY_ID'], 
						"ENTITY_TYPE" => $arParams['ENTITY_TYPE'],
						"FEATURE" => $_REQUEST['feature']
					)
				);
						
			$arResultTmp = $dbResultTmp->Fetch();

			if ($arResultTmp)
				$FeatureName = $arResultTmp["FEATURE_NAME"];
			else
				$FeatureName = '';
				
			
			$idTmp = CSocNetFeatures::SetFeature(
						$arParams['ENTITY_TYPE'],
						$arParams['ENTITY_ID'],
						$_REQUEST["feature"],
						false,
						$FeatureName
					);
			if (!$idTmp && $e = $APPLICATION->GetException())
				$errorMessage = $e->GetString();						

		}
		
		if(!$errorMessage)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.reload();
			</script><?
			die();
		}
		else
		{
			echo $errorMessage;
			die();
		}
	}
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists("sm_action", $_REQUEST) && $_REQUEST["sm_action"] == "add" && check_bitrix_sessid())
	{
		if ((array_key_exists($_REQUEST["feature"], $arSocNetFeaturesSettings) && in_array($arParams["ENTITY_TYPE"], $arSocNetFeaturesSettings[$_REQUEST["feature"]]["allowed"])) || in_array($_REQUEST['feature'], $arStaticTabs))
		{
			$dbResultTmp = CSocNetFeatures::GetList(
				array(),
				array(
					"ENTITY_ID" => $arParams['ENTITY_ID'], 
					"ENTITY_TYPE" => $arParams['ENTITY_TYPE'],
					"FEATURE" => $_REQUEST['feature']
				)
			);
					
			$arResultTmp = $dbResultTmp->Fetch();

			if ($arResultTmp)
				CSocNetFeatures::SetFeature(
					($arParams['ENTITY_TYPE'] == SONET_ENTITY_GROUP) ? SONET_ENTITY_GROUP : SONET_ENTITY_USER,
					$arParams['ENTITY_ID'],
					$_REQUEST["feature"],
					true,
					$arResultTmp["FEATURE_NAME"]
				);

			$arUserOptions = CUserOptions::GetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);

			if(is_array($arUserOptions))
				$arUserOptions["FEATURES"][$_REQUEST["feature"]]["INDEX"] = count($arUserOptions["FEATURES"]);

			CUserOptions::SetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], $arUserOptions, false, 0);
		}
		else
			$errorMessage = GetMessage("SONET_SM_FEATURE_INCORRECT");

		if(!$errorMessage)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.reload();
			</script><?
			die();
		}
		else
		{
			echo $errorMessage;
			die();
		}
	}
}

if(!$errorMessage && $_REQUEST['menu_ajax'] == $arParams["ID"])
{
	if($USER->IsAuthorized() && $arResult["PERMISSION"] > "R")
	{
		$APPLICATION->RestartBuffer();
		switch($_REQUEST['menu_ajax_action'])
		{
			case 'get_settings':
			
				if ($_REQUEST['feature'] == '')
					$errorMessage = GetMessage("SONET_SM_FEATURE_INCORRECT");
				elseif (!array_key_exists($_REQUEST["feature"], $arSocNetFeaturesSettings))
					$errorMessage = GetMessage("SONET_SM_FEATURE_INCORRECT");
				elseif (!in_array($arParams["ENTITY_TYPE"], $arSocNetFeaturesSettings[$_REQUEST["feature"]]["allowed"]))
					$errorMessage = GetMessage("SONET_SM_FEATURE_INACTIVE");
			
				if (!$errorMessage)
				{
					$arFeatureTmp = array();
					$dbResultTmp = CSocNetFeatures::GetList(
							array(),
							array(
								"ENTITY_ID" => $arParams["ENTITY_ID"], 
								"ENTITY_TYPE" => $arParams["ENTITY_TYPE"],
								"FEATURE" => $_REQUEST["feature"]
							)
						);
						
					$arResultTmp = $dbResultTmp->GetNext();
					
					if ($arResultTmp)
					{
						$FeatureName = $arResultTmp["FEATURE_NAME"];
						$FeatureActive = (array_key_exists("ACTIVE", $arResultTmp) ? ($arResultTmp["ACTIVE"] == "Y") : true);
					}
					else
					{
						$FeatureName = '';
						$FeatureActive = true;
					}
					
					$arFeature = $arSocNetFeaturesSettings[$_REQUEST['feature']];
						
					$arFeatureTmp = array(
							"FeatureName" => $FeatureName,
							"Active" => $FeatureActive,
							"Operations" => array(),
						);

					foreach ($arFeature["operations"] as $op => $arOp)
						$arFeatureTmp["Operations"][$op] = CSocNetFeaturesPerms::GetOperationPerm($arParams["ENTITY_TYPE"], $arParams["ENTITY_ID"], $_REQUEST['feature'], $op);

					$strResult .= '<input type="hidden" name="feature" value="'.$_REQUEST['feature'].'">';
					$strResult .= '<input type="hidden" name="sm_action" value="update">';
					$strResult .= '<table cellspacing="0" cellpadding="2">';
					$strResult .= '<tr>';
					$strResult .= '<td valign="top" align="right" width="50%">'.GetMessage("SONET_SM_FEATURES_NAME").':</td>';
					$strResult .= '<td valign="top" width="50%"><input type="text" name="'.$_REQUEST['feature'].'_name" value="'.$arFeatureTmp["FeatureName"].'" size="30"></td>';
					$strResult .= '</tr>';

					if (!array_key_exists("hide_operations_settings", $arSocNetFeaturesSettings[$_REQUEST['feature']]) || !$arSocNetFeaturesSettings[$_REQUEST['feature']]["hide_operations_settings"])
					{

						if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
							$arFeatureTmp["PermsVar"] = array(
								SONET_ROLES_OWNER => GetMessage("SONET_SM_PVG_OWNER"),
								SONET_ROLES_MODERATOR => GetMessage("SONET_SM_PVG_MOD"),
								SONET_ROLES_USER => GetMessage("SONET_SM_PVG_USER"),
								SONET_ROLES_AUTHORIZED => GetMessage("SONET_SM_PVG_AUTHORIZED"),
								SONET_ROLES_ALL => GetMessage("SONET_SM_PVG_ALL"),
							);
						else
						{
							if (CSocNetUser::IsFriendsAllowed())
								$arFeatureTmp["PermsVar"] = array(
									SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_SM_PVU_NONE"),
									SONET_RELATIONS_TYPE_FRIENDS => GetMessage("SONET_SM_PVU_FR"),
									SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_SM_PVU_AUTHORIZED"),
									SONET_RELATIONS_TYPE_ALL => GetMessage("SONET_SM_PVU_ALL"),
								);
							else
								$arFeatureTmp["PermsVar"] = array(
									SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_SM_PVU_NONE"),
									SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_SM_PVU_AUTHORIZED"),
									SONET_RELATIONS_TYPE_ALL => GetMessage("SONET_SM_PVU_ALL"),
								);
						}

						foreach ($arFeatureTmp["Operations"] as $operation => $perm):
					
							$strResult .= '<tr>';
							$strResult .= '<td valign="top" align="right" width="50%">'.(array_key_exists("operation_titles", $arSocNetFeaturesSettings[$_REQUEST["feature"]]) ? $arSocNetFeaturesSettings[$_REQUEST["feature"]]["operation_titles"][$operation] : GetMessage("SONET_FEATURES_".$_REQUEST["feature"]."_".$operation)).':</td>';
							$strResult .= '<td valign="top" width="50%">';
							$strResult .= '<select name="'.$_REQUEST['feature'].'_'.$operation.'_perm">';
									
							foreach ($arFeatureTmp["PermsVar"] as $key => $value)
							{
								if (
									!array_key_exists("restricted", $arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"][$operation]) 
									|| !isset($arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"][$operation]["restricted"][$arParams["ENTITY_TYPE"]])
									|| !in_array($key, $arSocNetFeaturesSettings[$_REQUEST["feature"]]["operations"][$operation]["restricted"][$arParams["ENTITY_TYPE"]])
								)
								{
									$strResult .= '<option value="'.$key.'"'.(($key == $perm) ? ' selected' : '').'>'.$value.'</option>';
								}
							}
									
							$strResult .= '</select>';
							$strResult .= '</td>';						
							$strResult .= '</tr>';
									
						endforeach;
					}
							
					$strResult .= '</table>';

					require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
					$obJSPopup = new CJSPopup();
					$obJSPopup->StartContent();
					echo $strResult;
					$obJSPopup->EndContent();
					$obJSPopup->ShowStandardButtons(array('save', 'cancel'));
				}
				
				break;
				
			case 'get_menu_settings':

				$arUserOptions = CUserOptions::GetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);
				$MaxItems = (
					isset($arUserOptions["MAX_ITEMS"])
					&& intval($arUserOptions["MAX_ITEMS"]) > 0
						? intval($arUserOptions["MAX_ITEMS"])
						: $arParams["MAX_ITEMS"]
				);

				$strResult = '<input type="hidden" name="sm_action" value="update_menu" id="bx_sm_menusettings_action">';
				$strResult .= '<table cellspacing="0" cellpadding="2">';
				$strResult .= '<tr>';
				$strResult .= '<td valign="top" align="right" width="50%">'.GetMessage("SONET_SM_MAX_ITEMS").':</td>';
				$strResult .= '<td valign="top" width="50%"><input type="text" name="max_items" value="'.$MaxItems.'" size="30"></td>';
				$strResult .= '</tr>';
				$strResult .= '</table>';

				require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/interface/admin_lib.php');
				$obJSPopup = new CJSPopup();
				$obJSPopup->StartContent();
				echo $strResult;
				$obJSPopup->EndContent();
				?>
				<script>
				<?=$obJSPopup->jsPopup?>.SetButtons([
					BX.CDialog.btnSave, BX.CDialog.btnCancel, {
						title: '<?=CUtil::JSEscape(GetMessage("SONET_SM_CLEAR"))?>',
						id: 'resetbtn',
						name: 'resetbtn',
						action: function () {
							if(!confirm(langMenuConfirm1))
								return;
							BX('bx_sm_menusettings_action').value = 'clear_settings';
							this.parentWindow.PostParameters();
						}
					}
				]);
				</script>
				<?
				break;
				
			case 'update_position':
				MenuSaveSettings($arParams, $_REQUEST['POS']);
				break;
		}
	}
	else
		echo GetMessage("SONET_SM_AUTH_ERR");
		
	die();
}


$arThemes = array();
$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/socialnetwork.menu/templates/.default/themes/");
$dir = $sTemplateDirFull;
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$parent = & $this->GetParent();
if (is_object($parent) && $parent->__name <> '')
{
	$parent = & $parent->GetParent();

	if (
		is_object($parent)
		&& isset($parent->arParams["SM_THEME"])
		&& $parent->arParams["SM_THEME"] <> ''
	)
	{
		$arParams["SM_THEME"] = $parent->arParams["SM_THEME"];
	}
	else
	{
		$site_template = CSite::GetCurTemplate();

		if (mb_strpos($site_template, "bright") === 0)
			$arParams["SM_THEME"] = "grey";
		else
		{
			$theme_tmp_id = COption::GetOptionString("main", "wizard_".$site_template."_sm_theme_id");
			if ($theme_tmp_id <> '')
				$theme_id = $theme_tmp_id;
			elseif (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
				$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id_extranet");
			else
				$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id");

			if ($theme_id <> '')
				$arParams["SM_THEME"] = $theme_id;
			else
				$arParams["SM_THEME"] = "grey";
		}
	}
}

if (!in_array($arParams["SM_THEME"], $arThemes))
{
	$arParams["SM_THEME"] = (in_array("grey", $arThemes) ? "grey" : $arThemes[0]);
}

if (in_array($arParams["SM_THEME"], $arThemes))
{
	$this->InitComponentTemplate();
	$obTemplate = & $this->GetTemplate();
	$GLOBALS['APPLICATION']->SetAdditionalCSS($obTemplate->GetFolder()."/themes/".$arParams["SM_THEME"]."/style.css");
}
	
$arUserOptions = CUserOptions::GetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);
$arResult["ALL_FEATURES"] = Array();

$arFeaturesTmp = array();
$dbResultTmp = CSocNetFeatures::GetList(
	array(),
	array("ENTITY_ID" => $arParams["ENTITY_ID"], "ENTITY_TYPE" => $arParams["ENTITY_TYPE"])
);
while ($arResultTmp = $dbResultTmp->GetNext())
{
	$arFeaturesTmp[$arResultTmp["FEATURE"]] = $arResultTmp;
}

$arCustomFeatures = array();
$events = GetModuleEvents("socialnetwork", "OnFillSocNetMenu");
while ($arEvent = $events->Fetch())
{
	ExecuteModuleEventEx($arEvent, array(&$arCustomFeatures, $arParams));
}

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER && $USER->IsAuthorized() && $USER->GetID() ==  $arParams["ENTITY_ID"])
	$arResult["ALL_FEATURES"]["log"] = array(
		"FeatureName" => $arParams["LogName"],
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["Log"]
	);
	
if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER || $arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
	$arResult["ALL_FEATURES"]["general"] = array(
		"FeatureName" => $arParams["GeneralName"],
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["arResult"]["Urls"]["View"] : $arParams["arResult"]["Urls"]["Main"])
	);

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER && CSocNetUser::IsFriendsAllowed() && $arParams["arResult"]["CurrentUserPerms"]["Operations"]["viewfriends"])
	$arResult["ALL_FEATURES"]["friends"] = array(
		"FeatureName" => $arParams["FriendsName"],
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["Friends"]
	);

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER && $arParams["arResult"]["CurrentUserPerms"]["Operations"]["viewgroups"])
	$arResult["ALL_FEATURES"]["groups"] = array(
		"FeatureName" => $arParams["GroupsName"],
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["Groups"]
	);

foreach ($arSocNetFeaturesSettings as $feature => $arFeature)
{
	if (
		!array_key_exists("allowed", $arFeature)
		|| !in_array($arParams["ENTITY_TYPE"], $arFeature["allowed"])
	)
	{
		continue;
	}

	if (
		is_array($arCustomFeatures)
		&& isset($arCustomFeatures["CanView"])
		&& array_key_exists($feature, $arCustomFeatures["CanView"])
		&& !$arCustomFeatures["CanView"][$feature]
	)
	{
		continue;
	}
	elseif (
		is_array($arCustomFeatures)
		&& isset($arCustomFeatures["CanView"])
		&& array_key_exists($feature, $arCustomFeatures["CanView"])
	)
	{
		if (
			!array_key_exists($feature, $arFeaturesTmp)
			|| !array_key_exists("FEATURE_NAME", $arFeaturesTmp[$feature])
			|| $arFeaturesTmp[$feature]["FEATURE_NAME"] == ''
		)
		{
			$arFeaturesTmp[$feature]["FEATURE_NAME"] =  $arCustomFeatures["Title"][$feature];
		}

		if (!array_key_exists($feature, $arFeaturesTmp) || !array_key_exists("ACTIVE", $arFeaturesTmp[$feature]) || $arFeaturesTmp[$feature]["ACTIVE"] != "N")
			$arFeaturesTmp[$feature]["ACTIVE"] =  "Y";

		$arFeaturesTmp[$feature]["URL"] = (array_key_exists("Urls", $arParams["arResult"]) && array_key_exists($feature, $arParams["arResult"]["Urls"]) && $arParams["arResult"]["Urls"][$feature] <> '' ? $arParams["arResult"]["Urls"][$feature] : $arCustomFeatures["Urls"][$feature]);

		$arFeaturesTmp[$feature]["NOPARAMS"] =  true;
		
		if (array_key_exists("AllowSettings", $arCustomFeatures) && array_key_exists($feature, $arCustomFeatures["AllowSettings"]) && $arCustomFeatures["AllowSettings"][$feature])	
			$arFeaturesTmp[$feature]["ALLOW_SETTINGS"] =  true;
	}

	$arResult["ALL_FEATURES"][$feature] = array(
		"FeatureName" => (
				isset($arFeaturesTmp[$feature]["FEATURE_NAME"])
				&& $arFeaturesTmp[$feature]["FEATURE_NAME"] <> ''
					? $arFeaturesTmp[$feature]["FEATURE_NAME"]
					: (
						isset($arParams["arResult"]["Title"][$feature])
						&& $arParams["arResult"]["Title"][$feature] <> ''
							? $arParams["arResult"]["Title"][$feature]
							: GetMessage("SONET_SM_".$arParams["ENTITY_TYPE"]."_".mb_strtoupper($feature))
					)
		),
		"Active" => (array_key_exists($feature, $arFeaturesTmp) ? ($arFeaturesTmp[$feature]["ACTIVE"] == "Y") : true),
		"Operations" => array(),
		"NOPARAMS" => (isset($arFeaturesTmp[$feature]["NOPARAMS"]) ? $arFeaturesTmp[$feature]["NOPARAMS"] : empty($arFeature['operations'])),
		"Url" => (
				$arFeaturesTmp[$feature]["URL"] <> ''
					? $arFeaturesTmp[$feature]["URL"]
					: $arParams["arResult"]["Urls"][$feature]
		),
		"ALLOW_SETTINGS" => (array_key_exists($feature, $arFeaturesTmp) && array_key_exists("ALLOW_SETTINGS", $arFeaturesTmp[$feature]) && $arFeaturesTmp[$feature]["ALLOW_SETTINGS"] ? $arFeaturesTmp[$feature]["ALLOW_SETTINGS"] : false)
	);

	if (array_key_exists("operations", $arFeature))
		foreach ($arFeature["operations"] as $op => $arOp)
			$arResult["ALL_FEATURES"][$feature]["Operations"][$op] = CSocNetFeaturesPerms::GetOperationPerm($arParams["ENTITY_TYPE"], $arParams["ENTITY_ID"], $feature, $op);
		
}

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
{
	$arResult["ALL_FEATURES"]["users"] = array(
		"FeatureName" => $arParams["UsersName"],
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["GroupUsers"]
	);
}

if ($arParams["ENTITY_TYPE"] == "M")
{
	$arResult["ALL_FEATURES"]["messages_users"] = array(
		"FeatureName" => GetMessage("SONET_SM_M_USERS"),
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["MessagesUsers"]
	);
	
	$arResult["ALL_FEATURES"]["messages_input"] = array(
		"FeatureName" => GetMessage("SONET_SM_M_INPUT"),
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["MessagesInput"]
	);

	$arResult["ALL_FEATURES"]["messages_output"] = array(
		"FeatureName" => GetMessage("SONET_SM_M_OUTPUT"),
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["MessagesOutput"]
	);

	$arResult["ALL_FEATURES"]["user_ban"] = array(
		"FeatureName" => GetMessage("SONET_SM_M_BAN"),
		"Active" => true,
		"Operations" => array(),
		"NOPARAMS" => true,
		"Url" => $arParams["arResult"]["Urls"]["UserBan"]
	);

	if (
		array_key_exists("arResult", $arParams)
		&& isset($arParams["arResult"]["Urls"])
		&& isset($arParams["arResult"]["Urls"]["BizProc"])
	)
	{
		$arResult["ALL_FEATURES"]["bizproc"] = array(
			"FeatureName" => GetMessage("SONET_SM_M_BIZPROC"),
			"Active" => true,
			"Operations" => array(),
			"NOPARAMS" => true,
			"Url" => $arParams["arResult"]["Urls"]["BizProc"]
		);
	}
}


$arResult["FEATURES"] = Array();
$arResult["FEATURES_CODES"] = Array();

if (
	isset($arUserOptions["FEATURES"])
	&& count($arUserOptions["FEATURES"]) > 0
)
{
	foreach($arUserOptions["FEATURES"] as $feature_id => $featureUserSettings)
	{
		if(
			$arResult["ALL_FEATURES"][$feature_id] 
			&& array_key_exists("Active", $arResult["ALL_FEATURES"][$feature_id]) 
			&& $arResult["ALL_FEATURES"][$feature_id]["Active"] == true 
			&& (
				(
					array_key_exists("arResult", $arParams) 
					&& array_key_exists("CanView", $arParams["arResult"]) 
					&& array_key_exists($feature_id, $arParams["arResult"]["CanView"]) 
					&& $arParams["arResult"]["CanView"][$feature_id]
				)
				|| 
				in_array($feature_id, $arStaticTabs)
			)
		)
		{
			$arFeature = $arResult["ALL_FEATURES"][$feature_id];

			if(intval($featureUserSettings["INDEX"])<=0)
				$arUserOptions["FEATURES"][$feature_id]["INDEX"] = 0;

			$arFeature["feature"] = $feature_id;
			$temp_id = $featureUserSettings["INDEX"];

			while(array_key_exists($temp_id, $arResult["FEATURES"]))
				$temp_id++;

			$arResult["FEATURES"][$temp_id] = $arFeature;
			$arResult["FEATURES_CODES"][] = $feature_id;
		}
		else
			unset($arUserOptions["FEATURES"][$feature_id]);
	}
	ksort($arResult["FEATURES"], SORT_NUMERIC);
	$arResult["FEATURES"] = array_values($arResult["FEATURES"]);	
}
else
{
	$arFeature = array();

	foreach($arResult["ALL_FEATURES"] as $feature_id => $arFeature)
	{
		$arFeature["feature"] = $feature_id;
		if (array_key_exists("ALLOW_SETTINGS", $arResult["ALL_FEATURES"][$feature_id]) && $arResult["ALL_FEATURES"][$feature_id]["ALLOW_SETTINGS"])
		{
			if ($arResult["ALL_FEATURES"][$feature_id]["Active"])
			{
				$arResult["FEATURES"][] = $arFeature;
				$arResult["FEATURES_CODES"][] = $feature_id;
			}
		}
		elseif ($arParams["arResult"]["CanView"][$feature_id] || in_array($feature_id, $arStaticTabs))
		{
			$arResult["FEATURES"][] = $arFeature;
			$arResult["FEATURES_CODES"][] = $feature_id;
		}
	}
}

if ($arParams["PAGE_ID"] == "group" || $arParams["PAGE_ID"] == "user")
	$page_id = "general";
elseif (mb_strpos($arParams["PAGE_ID"], "user_") === 0)
	$page_id = mb_substr($arParams["PAGE_ID"], 5);
elseif (mb_strpos($arParams["PAGE_ID"], "group_") === 0)
	$page_id = mb_substr($arParams["PAGE_ID"], 6);

if (array_key_exists("log", $arResult["ALL_FEATURES"]) && !in_array("log", $arResult["FEATURES_CODES"]))
{
	$arResult["FEATURES"] = array_merge(
		array(
			array(
				"FeatureName" => $arResult["ALL_FEATURES"]["log"]["FeatureName"],
				"Active" => $arResult["ALL_FEATURES"]["log"]["Active"],
				"Operations" => $arResult["ALL_FEATURES"]["log"]["Operations"],
				"NOPARAMS" => $arResult["ALL_FEATURES"]["log"]["NOPARAMS"],
				"Url" => $arResult["ALL_FEATURES"]["log"]["Url"],
				"feature" =>"log"
			)
		), 
		$arResult["FEATURES"]
	);
	$arResult["FEATURES_CODES"] = array_merge(array("log"), $arResult["FEATURES_CODES"]);
}

if ($arParams["USE_MAIN_MENU"] == "Y")
{
	$arResult["PAGE_ID"] = $page_id;

	$customMenu = CMenuCustom::getInstance();

	foreach ($arResult["FEATURES"] as $arFeature)
	{
		$customMenu->AddItem($arParams["MAIN_MENU_TYPE"], array(
			"TEXT" => $arFeature["FeatureName"],
			"LINK" => $arFeature["Url"],
			"SELECTED" => ($arResult["PAGE_ID"] == $arFeature["feature"]),
			"PERMISSION" => "R",
			"DEPTH_LEVEL" => 1,
			"IS_PARENT" => false,
		));
	}
}
else
{
	$arResult["MAX_ITEMS"] = (
		isset($arUserOptions["MAX_ITEMS"])
		&& intval($arUserOptions["MAX_ITEMS"]) > 0
			? $arUserOptions["MAX_ITEMS"]
			: $arParams["MAX_ITEMS"]
	);

	foreach ($arResult["FEATURES"] as $i => $arFeature)
	{
		if ($arFeature["feature"] == $page_id && $i >= $arResult["MAX_ITEMS"])
		{
			$tmp = $arFeature;
			$arr1 = array_slice($arResult["FEATURES"], 0, ($arResult["MAX_ITEMS"]-1));
			$arr2 = array_slice($arResult["FEATURES"], ($arResult["MAX_ITEMS"]-1), ($i-($arResult["MAX_ITEMS"]-1)));
			$arr3 = array_slice($arResult["FEATURES"], ($i+1));		
			
			$arResult["FEATURES"] = array_merge($arr1, array($tmp), $arr2, $arr3);
			ksort($arResult["FEATURES"], SORT_NUMERIC);
			
			break;
		}
	}
	
	foreach ($arResult["FEATURES"] as $i => $arFeature)
		if ($arFeature["ALLOW_SETTINGS"])
			$arResult["CustomFeaturesTitle"][$arFeature["feature"]] = $arFeature["FeatureName"];

	CUtil::InitJSCore(array("window", "ajax"));

	$arResult["ErrorMessage"] = $errorMessage;

	$this->IncludeComponentTemplate();
}
?>