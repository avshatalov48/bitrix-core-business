<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init(array("sender_page", "sender_helper", "ajax_action"));
\Bitrix\Sender\Integration\Bitrix24\Service::initLicensePopup();