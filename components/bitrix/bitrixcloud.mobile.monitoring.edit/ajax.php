<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CUser $USER */
CComponentUtil::__IncludeLang(dirname($_SERVER['SCRIPT_NAME']), '/ajax.php');

$arResult = [];

if (!CModule::IncludeModule('bitrixcloud'))
{
	$arResult['ERROR'] = GetMessage('BCLMMD_BC_NOT_INSTALLED');
}

if (!$USER->CanDoOperation('bitrixcloud_monitoring') || !check_bitrix_sessid())
{
	$arResult['ERROR'] = GetMessage('BCLMMD_ACCESS_DENIED');
}

if (!isset($arResult['ERROR']))
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
	$domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : '';
	$monitoring = CBitrixCloudMonitoring::getInstance();

	if ($action === 'update')
	{
		try
		{
			$result = $monitoring->startMonitoring(
				$domain,
				$_REQUEST['IS_HTTPS'] === 'Y',
				$_REQUEST['LANG'],
				$_REQUEST['EMAILS'],
				$_REQUEST['TESTS']
			);

			if ($result !== '')
			{
				$arResult['ERROR'] = $result;
			}
		}
		catch (Exception $e)
		{
			$arResult['ERROR'] = $e->getMessage();
		}
	}

	if (isset($arResult['ERROR']))
	{
		$arResult['RESULT'] = 'ERROR';
	}
	else
	{
		$arResult['RESULT'] = 'OK';
	}
}

die(json_encode($arResult));
