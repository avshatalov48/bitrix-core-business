<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

use Bitrix\Main\Type\Date;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/admin_informer.php");

class CMpNotifications
{
	public static function OnAdminInformerInsertItemsHandlerMP() {
		global $USER;
		if(LICENSE_KEY == "DEMO")
		{
			return false;
		}
		if(!$USER->CanDoOperation('install_updates'))
		{
			return false;
		}
		$daysCheck = intval(COption::GetOptionString('main', 'update_autocheck', '1'));
		if($daysCheck > 0)
		{
			$arModulesResult = unserialize(COption::GetOptionString("main", "last_mp_modules_result"), ['allowed_classes' => false]);
			if(!is_array($arModulesResult))
			{
				$arModulesResult = array("check_date" => 0);
			}

			if ($arModulesResult["check_date"] + 86400*$daysCheck < time())
			{
				$arInstalledModules = self::getClientInstalledModules();
				$arModulesUpdates = ($arInstalledModules ? self::checkUpdates($arInstalledModules, 2, $daysCheck) : array());
				$arDateEndModules = ($arInstalledModules ? self::checkModulesEndDate($arInstalledModules, $daysCheck) : array());
				$arNewPartnersModules = ($arInstalledModules ? self::checkUpdates($arInstalledModules, 1, $daysCheck) : array());
				$arModulesResult = array(
					"check_date" => time(),
					'update_module' => $arModulesUpdates,
					'end_update' => $arDateEndModules,
					'new_module' => $arNewPartnersModules,
				);
				COption::SetOptionString(
					'main',
					'last_mp_modules_result',
					serialize($arModulesResult)
				);
			}
			self::addMpNotifications($arModulesResult);
		}else{
			return false;
		}
	}

	//checks for modules the end of the update period
	public static function checkModulesEndDate($arRequestedModules, $daysCheck)
	{
		$errorMessage = "";
		$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
		$arUpdateList = CUpdateClientPartner::GetUpdatesList(
			$errorMessage,
			LANGUAGE_ID,
			$stableVersionsOnly,
			$arRequestedModules,
			Array("fullmoduleinfo" => "Y")
		);
		$arEndUpdateModules = array();
		if ($arUpdateList)
		{
			if (isset($arUpdateList["MODULE"]) && is_array($arUpdateList["MODULE"]))
			{
				$daysCheck += 30;
				$curDateFrom = new Date;
				$curDateTo = new Date;
				$curDateFrom = $curDateFrom->add("30 days");
				$curDateTo = $curDateTo->add(strval($daysCheck)." days");

				for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
				{
					if ($arUpdateList["MODULE"][$i]['@']['DATE_TO'] <> '' && Date::isCorrect($arUpdateList["MODULE"][$i]['@']['DATE_TO']))
					{
						$dateTo = new Date($arUpdateList["MODULE"][$i]['@']['DATE_TO']);
						$ID = $arUpdateList["MODULE"][$i]["@"]["ID"];
						if ($dateTo >= $curDateFrom && $dateTo < $curDateTo)
						{
							$arEndUpdateModules[$ID] = array(
								'ID' => $arUpdateList["MODULE"][$i]["@"]["ID"],
								'NAME' => $arUpdateList["MODULE"][$i]["@"]["NAME"],
								'VERSION' => $arUpdateList["MODULE"][$i]["@"]["DATE_TO"],
								'DATE_TO' => $arUpdateList["MODULE"][$i]["@"]["DATE_TO"],
							);
						}
					}
				}
			}
		}
		return $arEndUpdateModules;
	}

	//check updates and new modules
	public static function checkUpdates($arModules, $searchType, $daysCheck){
		$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
		$strError_tmp = "";
		$strQuery = CUpdateClientPartner::__CollectRequestData(
			$strError_tmp,
			LANGUAGE_ID,
			$stableVersionsOnly,
			array(),
			array(
				"search_module_id" => (is_array($arModules) ? implode(",", $arModules) : $arModules),
				"search_page" => "SEARCH_NEW",
				"search_category" => $daysCheck,
				"search_type" => $searchType,
			)
		);
		$content = CUpdateClientPartner::__GetHTTPPage("SEARCH_NEW", $strQuery, $strError_tmp);
		$arResult = Array();
		$arResultModules = array();

		if ($strError_tmp == '')
		{
			CUpdateClientPartner::__ParseServerData($content, $arResult, $strError_tmp);
			if (is_array($arResult['DATA']['#']['MODULE']) && count($arResult['DATA']['#']['MODULE']) > 0)
			{
				foreach ($arResult['DATA']['#']['MODULE'] as $arModule)
				{
					if ($searchType == 1)
					{
						$arResultModules[$arModule['@']['PARTNER_ID']][] = $arModule['@'];
					}
					else
					{
						$arResultModules[$arModule['@']['ID']] = $arModule['@'];
					}
				}
			}
		}
		return $arResultModules;
	}

	//add notifications to admin informer
	public static function addNotificationsToInformer($arModules, $arNotifierText, $arrayId, $serverName){
		foreach ($arModules as $arModule)
		{
			$moduleLink = (($arrayId == 'end_update') ? '/bitrix/admin/partner_modules.php' : '/bitrix/admin/update_system_market.php?module='.$arModule['ID']);
			$arParams = array(
				'TITLE' => GetMessage($arNotifierText['TITLE']),
				'COLOR' => 'green',
				'FOOTER' => "<a href=\"javascript:void(0)\" onclick=\"hideMpNotification(this, '".
							CUtil::JSEscape($arModule['ID']).
							"', '".
							CUtil::JSEscape($arrayId).
							"')\" ".
							"style=\"float: right !important; font-size: 0.8em !important;\">".
							GetMessage('TOP_PANEL_AI_MODULE_UPDATE_BUTTON_HIDE').
							"</a>".
							"<a href=\"".
							$serverName.
							$moduleLink.
							"\" target=\"_blank\" ".
							"onclick=\"hideMpNotification(this, '".
							CUtil::JSEscape($arModule['ID']).
							"', '".
							CUtil::JSEscape($arrayId).
							"')\">".
							GetMessage('TOP_PANEL_AI_MODULE_UPDATE_BUTTON_VIEW').
							"</a>",
				'ALERT' => true,
				'HTML' => GetMessage($arNotifierText['HTML'], array("#NAME#" => $arModule["NAME"], "#PARTNER#" => $arModule["PARTNER"])).self::addJsToInformer(),
			);
			CAdminInformer::AddItem($arParams);
		}

	}

	//get installed mp modules
	public static function getClientInstalledModules(){
		$strError_tmp = "";
		$arRequestedModules = array();
		$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);
		if ($strError_tmp == '')
		{
			if (count($arClientModules) > 0)
			{
				foreach ($arClientModules as $key => $value)
				{
					if (mb_strpos($key, ".") !== false)
					{
						$arRequestedModules[] = $key;
					}
				}
				return $arRequestedModules;
			}
		}
		return false;
	}

	//check notification's type to add
	public static function addMpNotifications($arModulesResult)
	{
		$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
		if (count($arModulesResult['update_module']) <= 0 && count($arModulesResult['end_update']) <= 0 && ($arModulesResult['new_module']) <= 0)
		{
			return false;
		}
		if (count($arModulesResult['update_module']) > 0)
		{
			self::addNotificationsToInformer($arModulesResult['update_module'], array('TITLE' => 'TOP_PANEL_AI_MODULE_UPDATE', 'HTML' => 'TOP_PANEL_AI_MODULE_UPDATE_DESC'), 'update_module', $serverName);
		}
		if (count($arModulesResult['end_update']) > 0)
		{
			self::addNotificationsToInformer($arModulesResult['end_update'], array('TITLE' => 'TOP_PANEL_AI_MODULE_END_UPDATE', 'HTML' => 'TOP_PANEL_AI_MODULE_END_UPDATE_DESC'), 'end_update', $serverName);
		}
		if (count($arModulesResult['new_module']) > 0)
		{
			self::addNotificationsPartnersNewModulesToInformer($arModulesResult['new_module'], $serverName);
		}
	}

	//add notifications about new partner modules
	public static function addNotificationsPartnersNewModulesToInformer($arModules, $serverName) {
		foreach ($arModules as $partnerID => $arPartnerModules)
		{
			$arParams = array(
				'TITLE' => GetMessage("TOP_PANEL_AI_NEW_MODULE_TITLE"),
				'COLOR' => 'green',
				'FOOTER' => "<a href=\"javascript:void(0)\" onclick=\"hideMpNotification(this, '".
							CUtil::JSEscape($partnerID).
							"', '".
							CUtil::JSEscape('new_module').
							"')\" ".
							"style=\"float: right !important; font-size: 0.8em !important;\">".
							GetMessage('TOP_PANEL_AI_MODULE_UPDATE_BUTTON_HIDE').
							"</a>",
				'ALERT' => true,
				'HTML' => GetMessage('TOP_PANEL_AI_NEW_MODULE_DESC', array("#PARTNER#" => $arPartnerModules[0]['PARTNER'])),
			);
			foreach ($arPartnerModules as $arModule)
			{
				$arParams['HTML'] .= '<a href="'.$serverName.'/bitrix/admin/update_system_market.php?module='.$arModule['ID'].'" target="_blank">'.$arModule['NAME'].'</a><br>';
			}
			$arParams['HTML'] .= self::addJsToInformer();
			CAdminInformer::AddItem($arParams);
		}
	}

	public static function addJsToInformer()
	{
		return $script = '
						<script type="text/javascript">
						function hideMpNotification(el, module, array_id)
						{
							if(el.parentNode.parentNode.parentNode)
								BX.hide(el.parentNode.parentNode.parentNode);
								BX.ajax({
									"method": "POST",
									"dataType": "json",
									"url": "/bitrix/admin/partner_modules.php",
									"data": "module="+module+"&'.bitrix_sessid_get().'&act=unnotify_mp&array_id="+array_id,
									"async": true,
									"processData": false,
									"cache": false,							
	 						 	}); 
						}
						</script>';
	}

}

?>