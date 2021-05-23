<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(
	!\Bitrix\Main\Loader::includeModule("rest")
	|| !\CRestUtil::isAdmin()
)
{
	return;
}

$arParams['ID'] = intval($arParams['ID']);

$arResult['ALLOW_ZIP'] = \Bitrix\Main\ModuleManager::isModuleInstalled("bitrix24");

if ($arParams['ID'] > 0)
{
	$APPLICATION->SetTitle(GetMessage('MARKETPLACE_LOCAL_EDIT_TITLE'));

	$arResult["APP"] = \Bitrix\Rest\AppTable::getByClientId($arParams['ID']);
	if (is_array($arResult["APP"]) && $arResult['APP']['STATUS'] === \Bitrix\Rest\AppTable::STATUS_LOCAL)
	{
		if(!empty($arResult["APP"]["SCOPE"]))
		{
			$arResult["APP"]["SCOPE"] = explode(",", $arResult["APP"]["SCOPE"]);
		}

		$langNames = \Bitrix\Rest\AppLangTable::getList(array(
			'filter' => array(
				'=APP_ID' => $arResult["APP"]["ID"]
			)
		));

		$arResult['APP']['MENU_NAME'] = array();

		while($langName = $langNames->fetch())
		{
			$arResult['APP']['MENU_NAME'][$langName["LANGUAGE_ID"]] = $langName["MENU_NAME"];
		}
	}
	else
	{
		ShowError(GetMessage('MARKETPLACE_LOCAL_NOT_FOUND'));
		return;
	}
}
else
{
	$APPLICATION->SetTitle(GetMessage('MARKETPLACE_LOCAL_ADD_TITLE'));
	$arResult['APP'] = array(
		'SCOPE' => array(),
	);
}

$dbRes = \Bitrix\Main\Localization\LanguageTable::getList(array(
	'order' => array('DEF' => 'DESC', 'NAME' => 'ASC'),
	'filter' => array('=ACTIVE' => 'Y'),
	'select' => array('LID', 'NAME')
));

$arResult['LANG'] = array();
while($lang = $dbRes->fetch())
{
	$arResult['LANG'][$lang['LID']] = $lang['NAME'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	$arFields = array(
		"URL" => $_POST['APP_URL'],
		"URL_INSTALL" => $_POST['APP_URL_INSTALL'],
		"SCOPE" => is_array($_POST["SCOPE"]) && !empty($_POST["SCOPE"]) ? $_POST["SCOPE"] : array(),
		"APP_NAME" => trim($_POST["APP_NAME"]),
		"ONLY_API" => isset($_POST["APP_ONLY_API"]) ? "Y" : "N",
		"MOBILE" => isset($_POST["MOBILE"]) ? "Y" : "N",
	);

	if($arFields['APP_NAME'] == '')
	{
		$arResult["ERROR"] = \Bitrix\Main\Localization\Loc::getMessage("MP_ERROR_EMPTY_NAME");
	}
	elseif(count($arFields['SCOPE']) <= 0)
	{
		$arResult["ERROR"] = \Bitrix\Main\Localization\Loc::getMessage("MP_ERROR_EMPTY_SCOPE");
	}

	if(empty($arResult['ERROR']))
	{
		foreach(GetModuleEvents('rest', 'OnRestLocalAppSave', true) as $eventHandler)
		{
			$eventResult = ExecuteModuleEventEx($eventHandler, array($arResult['APP'], &$arFields));
			if($eventResult !== null)
			{
				$arResult["ERROR"] = $eventResult;
			}
		}
	}

	if(!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
	{
		try
		{
			\Bitrix\Rest\OAuthService::register();
		}
		catch(\Bitrix\Main\SystemException $e)
		{
			$arResult['ERROR'] = $e->getCode().': '.$e->getMessage();
		}
	}

	if(empty($arResult['ERROR']))
	{
		if($arFields['URL'] == '')
		{
			$arResult["ERROR"] = \Bitrix\Main\Localization\Loc::getMessage("MP_ERROR_INCORRECT_URL");
		}
	}

	if(empty($arResult["ERROR"]))
	{
		try
		{
			$appFields = array(
				'URL' => $arFields['URL'],
				'URL_INSTALL' => $arFields['URL_INSTALL'],
				'SCOPE' => implode(',', $arFields['SCOPE']),
				'STATUS' => \Bitrix\Rest\AppTable::STATUS_LOCAL,
				'APP_NAME' => $arFields['APP_NAME'],
				'MOBILE' => $arFields['MOBILE'],
			);

			if($arResult["APP"]['ID'] > 0)
			{
				$result = \Bitrix\Rest\AppTable::update($arResult['APP']['ID'], $appFields);
			}
			else
			{
				$appFields['INSTALLED'] = (!empty($arFields['URL_INSTALL']) && $arFields['ONLY_API'] !== 'Y')
					? \Bitrix\Rest\AppTable::NOT_INSTALLED
					: \Bitrix\Rest\AppTable::INSTALLED;

				$result = \Bitrix\Rest\AppTable::add($appFields);
			}

			if($result->isSuccess())
			{
				$appId = $result->getId();

				\Bitrix\Rest\AppLangTable::deleteByApp($appId);

				if($arFields['ONLY_API'] === 'N')
				{
					foreach($_POST['APP_MENU_NAME'] as $lang => $name)
					{
						\Bitrix\Rest\AppLangTable::add(array(
							'APP_ID' => $appId,
							'LANGUAGE_ID' => $lang,
							'MENU_NAME' => $name
						));
					}

					$eventFields = [
						"APP_ID" => $appId,
						"IS_NEW_APP" => $arResult["APP"]['ID'] > 0 ? true : false
					];
					foreach(GetModuleEvents("rest", "OnRestAppInstall", true) as $eventHandler)
					{
						ExecuteModuleEventEx($eventHandler, array($eventFields));
					}
				}
				else
				{
					if(
						$arFields["ONLY_API"] === "Y"
						&& !empty($arFields["URL_INSTALL"])
						&& empty($arResult['APP']['URL_INSTALL'])
					)
					{
						// checkCallback is already called inside checkFields
						$result = \Bitrix\Rest\EventTable::add(array(
							"APP_ID" => $appId,
							"EVENT_NAME" => "ONAPPINSTALL",
							"EVENT_HANDLER" => $arFields["URL_INSTALL"],
						));
						if($result->isSuccess())
						{
							\Bitrix\Rest\Event\Sender::bind('rest', 'OnRestAppInstall');
						}
					}

					if($arResult['APP']['ID'] <= 0)
					{
						\Bitrix\Rest\AppTable::install($appId);
					}
				}


				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					global $CACHE_MANAGER;
					$CACHE_MANAGER->ClearByTag('sonet_group');
				}
				if ($arResult['APP']['ID'] > 0)
					$url = $APPLICATION->GetCurPageParam("success=Y", array('success'));
				else if (\CRestUtil::isSlider())
					$url = str_replace("#id#", $appId, $arParams['EDIT_URL_TPL'])."?IFRAME=Y&success=added";
				else if ($arFields["ONLY_API"] === "Y")
					$url = str_replace("#id#", $appId, $arParams['EDIT_URL_TPL']);
				else
					$url = $arParams['LIST_URL'];
				LocalRedirect($url);
			}
			else
			{
				$arResult["ERROR"] = implode('<br />', $result->getErrorMessages());
			}
		}
		catch (\Bitrix\Rest\OAuthException $e)
		{
			$arResult["ERROR"] = $e->getMessage();
		}
	}

	$arResult['APP']['APP_NAME'] = $_POST['APP_NAME'];
	$arResult['APP']['MENU_NAME'] = $_POST['APP_MENU_NAME'];
	$arResult['APP']['SCOPE'] = !empty($_POST['SCOPE']) ? $_POST['SCOPE'] : array();
	$arResult['APP']['URL'] = $_POST['APP_URL'];
	$arResult['APP']['URL_INSTALL'] = $_POST['APP_URL_INSTALL'];
}

$arResult["SCOPE"] = \Bitrix\Rest\AppTable::cleanLocalPermissionList(
	\Bitrix\Rest\Engine\ScopeManager::getInstance()->listScope()
);

$this->IncludeComponentTemplate();
