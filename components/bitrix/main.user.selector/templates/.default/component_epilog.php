<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

if(\Bitrix\Main\Loader::includeModule("socialnetwork"))
{
	CUtil::InitJSCore(["socnetlogdest"]);
}