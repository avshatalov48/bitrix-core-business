<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/* @var CUser $USER */
/* @var CMain $APPLICATION */
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

	if ($action === 'delete')
	{
		$strError = $monitoring->stopMonitoring($domain);
		if ($strError !== '')
		{
			$arResult['ERROR'] = $strError;
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
