<?php
if (!defined('IM_AJAX_INIT'))
{
	define("IM_AJAX_INIT", true);
	define("PUBLIC_AJAX_MODE", true);
	define("NO_KEEP_STATISTIC", "Y");
	define("NO_AGENT_STATISTIC","Y");
	define("NO_AGENT_CHECK", true);
	define("DisableEventsCheck", true);

	if (isset($_GET['action']))
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		/** @global \CUser $USER */
		if ((int)$USER->GetID() <= 0)
		{
			echo CUtil::PhpToJsObject([
				'ERROR' => 'AUTHORIZE_ERROR',
				'BITRIX_SESSID' => bitrix_sessid()
			]);
			\CMain::FinalActions();
			die();
		}

		if (\Bitrix\Main\Loader::includeModule('disk'))
		{
			$ufController = new Bitrix\Disk\Uf\Controller();
			$ufController->setActionName($_GET['action'])->exec();
		}

		\CMain::FinalActions();
		die();
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}

if (\Bitrix\Main\Loader::includeModule("im"))
{
	echo \Bitrix\Im\Common::objectEncode([
		'BITRIX_SESSID' => bitrix_sessid(),
		'ERROR' => 'FILE_ERROR'
	]);
}

\CMain::FinalActions();
die();