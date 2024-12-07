<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER, $APPLICATION;

$bannerOption = COption::GetOptionString("eshop", "eshop_banner", "", SITE_ID);
if ($bannerOption == "Y")
	return;

if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["action"]) && $_POST["action"] == "eshopBannerClose" && check_bitrix_sessid()  && $USER->IsAdmin())
{
	COption::SetOptionString("eshop", "eshop_banner", "Y", false, SITE_ID);
	$APPLICATION->RestartBuffer();
	CMain::FinalActions();
}

$this->IncludeComponentTemplate();
