<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CUser $USER */
CComponentUtil::__IncludeLang(dirname($_SERVER['SCRIPT_NAME']), '/ajax.php');

$arResult = [];

if ($USER->IsAuthorized() && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

	if ($action === 'save_options')
	{
		$domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : '';
		$arOptions = isset($_REQUEST['options']) && is_array($_REQUEST['options']) ? $_REQUEST['options'] : [];

		if (!CModule::IncludeModule('bitrixcloud'))
		{
			$arResult['ERROR'] = GetMessage('BCMMP_BC_NOT_INSTALLED');
		}

		if (isset($domain) && !isset($arResult['ERROR']) && !empty($arOptions))
		{
			$monitoring = CBitrixCloudMonitoring::getInstance();
			$arUserDevices = CBitrixCloudMobile::getUserDevices($USER->GetID());
			$arMonDevices = $monitoring->getDevices($domain);
			$bChanged = false;

			foreach ($arUserDevices as $deviceId)
			{
				$bMon = in_array($deviceId, $arMonDevices, true);

				if (!$bMon && $arOptions['SUBSCRIBE'] == 'Y')
				{
					$monitoring->addDevice($domain, $deviceId);
					$bChanged = true;
				}
				elseif ($bMon && $arOptions['SUBSCRIBE'] != 'Y')
				{
					$monitoring->deleteDevice($domain, $deviceId);
					$bChanged = true;
				}
			}

			if ($bChanged)
			{
				$arList = $monitoring->getList();

				foreach ($arList as $arRes)
				{
					if ($arRes['DOMAIN'] === $domain)
					{
						$res = $monitoring->startMonitoring(
							$domain,
							$arRes['IS_HTTPS'],
							$arRes['LANG'],
							$arRes['EMAILS'],
							$arRes['TESTS']
						);

						break;
					}
				}
			}
		}
	}
}
else
{
	$arResult['ERROR'] = GetMessage('BCMMP_ACCESS_DENIED');
}

if (isset($arResult['ERROR']))
{
	$arResult['RESULT'] = 'ERROR';
}
else
{
	$arResult['RESULT'] = 'OK';
}

die(json_encode($arResult));
