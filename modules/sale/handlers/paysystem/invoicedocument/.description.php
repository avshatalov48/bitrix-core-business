<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_INVOICE_DOCUMENT_TITLE'),
	'SORT' => 100,
	'CODES' => []
);

if (!IsModuleInstalled('documentgenerator'))
{
	$data['IS_AVAILABLE'] = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}