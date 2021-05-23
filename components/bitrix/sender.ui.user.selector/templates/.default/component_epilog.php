<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

CUtil::InitJSCore(array('ajax_action', 'sender_helper', 'sender_page'));

if(\Bitrix\Main\Loader::includeModule("socialnetwork"))
{
	CUtil::InitJSCore(array("socnetlogdest"));
}