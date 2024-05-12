<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
CModule::includeModule("vote");
/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
global $USER;
/********************************************************************
				Actions
********************************************************************/
$voteId = intval($request->getQuery("VOTE_ID"));
if ($voteId <= 0)
	$voteId = intval($request->getQuery("PUBLIC_VOTE_ID"));
try
{
	$vote = \Bitrix\Vote\Vote::loadFromId($voteId);
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

$channel = \Bitrix\Vote\Channel::loadFromId($vote->get("CHANNEL_ID"));
/********************************************************************
				Form
********************************************************************/
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE", array("#ID#" => $voteId)));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();
if ($vote->canEdit($USER->GetID()))
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_BACK_TO_VOTE"),
		"ICON"	=> "btn_list",
		"LINK"	=> "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteId
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

$APPLICATION->IncludeComponent("bitrix:voting.form", "with_description", array(
	"VOTE_ID" => $voteId,
	"VOTE_RESULT_TEMPLATE" => "vote_results.php?VOTE_ID=".$voteId,
	"CACHE_TYPE" => "N"
));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>