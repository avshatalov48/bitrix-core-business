<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Rest\Url\DevOps;
LocalRedirect(DevOps::getInstance()->getIndexUrl());
?><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>