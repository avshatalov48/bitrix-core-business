<?php

define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

$siteId = (isset($_REQUEST["siteId"]) && is_string($_REQUEST["siteId"])) ? trim($_REQUEST["siteId"]): "";
$siteId = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $siteId), 0, 2);
if ($siteId)
{
	define("SITE_ID", $siteId);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (!CModule::IncludeModule("socialnetwork"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'MODULE_NOT_INSTALLED'));
	die();
}
if (check_bitrix_sessid())
{
	if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
		echo CUtil::PhpToJsObject(Array('ERROR' => 'EXTRANET_USER'));
	else
	{
		if (isset($_POST["nt"]))
		{
			preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($_REQUEST["nt"]), $matches);
			$nameTemplate = implode("", $matches[0]);
		}
		else
			$nameTemplate = CSite::GetNameFormat(false);

		if (isset($_POST['LD_SEARCH']) && $_POST['LD_SEARCH'] == 'Y')
		{
			echo CUtil::PhpToJsObject(Array(
				'USERS' => CSocNetLogDestination::SearchUsers($_POST['SEARCH'], $nameTemplate, false, IsModuleInstalled("extranet")),
			));
		}
		elseif (
			($_POST['LD_DEPARTMENT_RELATION'] ?? null) == 'Y'
			&& IsModuleInstalled("intranet")
		)
			echo CUtil::PhpToJsObject(Array(
				'USERS' => CSocNetLogDestination::GetUsers(Array(
					'deportament_id' => $_POST['DEPARTMENT_ID'],
					"NAME_TEMPLATE" => $nameTemplate
				),
				false),
			));
		elseif(isset($_POST["bitrix_processes"]))
		{
			if(CModule::IncludeModule('lists'))
			{
				IncludeModuleLangFile(__FILE__);
				global $USER;

				$service = \Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory::getServiceByIBlockTypeId(
					\Bitrix\Lists\Api\Service\ServiceFactory\ProcessService::getIBlockTypeId(),
					$USER->GetID() ?? 0
				);
				if (!$service)
				{
					echo \Bitrix\Main\Web\Json::encode(['success' => false, 'error' => \Bitrix\Main\Localization\Loc::getMessage('CC_BLL_WRONG_IBLOCK_TYPE')]);
					die();
				}

				$checkAccessResponse = $service->checkIBlockTypePermission();
				$listsPerm = $checkAccessResponse->getPermission();
				if (!$checkAccessResponse->isSuccess())
				{
					echo \Bitrix\Main\Web\Json::encode(['success' => false, 'error' => $checkAccessResponse->getErrorMessages()[0]]);
					die();
				}

				if ($listsPerm <= CListPermissions::ACCESS_DENIED)
				{
					echo \Bitrix\Main\Web\Json::encode(['success' => false, 'error' => \Bitrix\Main\Localization\Loc::getMessage('CC_BLL_ACCESS_DENIED')]);
					die();
				}

				$permissions = [];
				$admin = false;
				if($listsPerm >= CListPermissions::IS_ADMIN)
				{
					$permissions['new'] = GetMessage("CC_BLL_TITLE_NEW_LIST");
					$permissions['market'] = GetMessage("CC_BLL_TITLE_MARKETPLACE_NEW");
					$permissions['settings'] = GetMessage("CC_BLL_TITLE_SETTINGS");
					$admin = true;
				}
				elseif($listsPerm >= CListPermissions::CAN_READ)
				{
					$permissions['market'] = GetMessage("CC_BLL_TITLE_MARKETPLACE_NEW");
					$permissions['settings'] = GetMessage("CC_BLL_TITLE_SETTINGS");
				}

				$listData = [];
				$canOpenInSlider = method_exists($service, 'getAddElementLiveFeedCatalog');
				$catalogResponse = ($canOpenInSlider ? $service->getAddElementLiveFeedCatalog() : $service->getCatalog());
				if ($catalogResponse->isSuccess())
				{
					foreach ($catalogResponse->getCatalog() as $list)
					{
						if (!$canOpenInSlider && !CLists::getLiveFeed($list['ID']))
						{
							continue;
						}

						$data = [
							'ID' => $list['ID'],
							'NAME' => $list['NAME'],
							'DESCRIPTION' => $list['DESCRIPTION'],
							'CODE' => $list['CODE'],
							'PICTURE' => '<img src="/bitrix/images/lists/default.png" width="36" height="30" border="0" />',
							'PICTURE_SMALL' => '<img src="/bitrix/images/lists/default.png" width="19" height="16" border="0" />',
							'IBLOCK_TYPE_ID' => \Bitrix\Lists\Api\Service\ServiceFactory\ProcessService::getIBlockTypeId(),
						];

						$shortName = mb_substr($list['NAME'], 0, 50);
						if ($shortName !== $list['NAME'])
						{
							$data['NAME'] = $shortName . '...';
						}

						if($list['PICTURE'] > 0)
						{
							$imageFile = CFile::GetFileArray($list['PICTURE']);
							if($imageFile !== false)
							{
								$imageFile = CFile::ResizeImageGet(
									$imageFile,
									['width' => 36, 'height' => 30],
									BX_RESIZE_IMAGE_PROPORTIONAL,
									false
								);

								$data['PICTURE'] = '<img src="' . $imageFile["src"] . '" width="36" height="30" border="0" />';
								$data['PICTURE_SMALL'] = '<img src="' . $imageFile["src"] . '" width="19" height="16" border="0" />';
							}
						}

						$listData[$list['ID']] = $data;
					}
				}

				$listData = array_values($listData);

				echo \Bitrix\Main\Web\Json::encode([
					'success' => true,
					'lists' => $listData,
					'permissions' => $permissions,
					'admin' => $admin,
					'canOpenInSlider' => $canOpenInSlider,
				]);
			}
			else
			{
				echo CUtil::PhpToJsObject(Array('success' => false,'error' => 'Lists module not installed!'));
			}
		}
		else
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => 'UNKNOWN_ERROR'
			));
	}
}
else
	echo CUtil::PhpToJsObject(Array(
		'ERROR' => 'SESSION_ERROR'
	));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
