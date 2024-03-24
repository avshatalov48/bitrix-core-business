<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
define('HELP_FILE', 'add_issue.php');
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$rsFile = CPosting::GetFileList($request['POSTING_ID'], $request['FILE_ID']);
if ($arFile = $rsFile->Fetch())
{
	CFile::ViewByUser($arFile, ['force_download' => true]);
}
else
{
	require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';
	echo ShowError(GetMessage('POST_ERROR_ATTACH_NOT_FOUND'));
	require $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin.php';
}
