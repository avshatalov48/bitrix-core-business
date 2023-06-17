<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var array $arParams */

if (in_array($this->getTemplatePage(), ['site_domain', 'site_domain_switch', 'site_cookies', 'notes']))
{
	\CMain::finalActions();
}

use Bitrix\Landing\Site\Type;
use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

Loc::loadMessages(__DIR__ . '/template.php');

$templatePage = $this->getTemplatePage();
$disableFrame = $templatePage === 'landing_view';

// iframe footer
if ($request->get('IFRAME') == 'Y' && !$disableFrame)
{
	include 'slider_footer.php';
	\CMain::finalActions();
	die();
}
// ajax
elseif ($request->get('IS_AJAX') == 'Y')
{
	\CMain::finalActions();
	die();
}

// below this line only menu
if ($arParams['SHOW_MENU'] != 'Y')
{
	return;
}

if (
	Type::getCurrentScopeId() === null
	&& $templatePage !== 'landing_view'
	&& Loader::includeModule('crm')
	&& CCrmSaleHelper::isShopAccess() //TODO: change this block to new shop menu component
)
{
	$APPLICATION->IncludeComponent('bitrix:crm.shop.page.controller', '', []);
}

