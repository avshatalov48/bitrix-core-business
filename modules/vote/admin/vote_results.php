<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2009 Bitrix			 #
# https://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
CModule::includeModule("vote");
IncludeModuleLangFile(__FILE__);

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$voteId = intval($request->getQuery("VOTE_ID"));
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE", array("#ID#" => $voteId)));
try
{
	$vote = \Bitrix\Vote\Vote::loadFromId($voteId);
	global $USER;
	if (!$vote->canRead($USER->GetID()))
		throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
}
catch(Exception $e)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($e->getMessage());
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($vote->canEdit($USER->GetID()))
{
	$context = new CAdminContextMenu(array(
		array(
			"TEXT"	=> GetMessage("VOTE_BACK_TO_VOTE"),
			"ICON"	=> "btn_list",
			"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteId
		)
	));
	$context->Show();
}

$APPLICATION->IncludeComponent("bitrix:voting.result", "with_description", array(
	"VOTE_ID" => $voteId,
	"CACHE_TYPE" => "N",
	"VOTE_ALL_RESULTS" => 'Y'
	)
);

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
