<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$salePermissions = $APPLICATION->GetGroupRight('sale');

if ($salePermissions < 'R')
	return;

include_once($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

use	Bitrix\Sale\Helpers\Admin\BusinessValueControl;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/helpers/admin/businessvalue.php');

$errors = array();

$businessValueControl = new BusinessValueControl('bizval');

if($REQUEST_METHOD == 'POST' && strlen($Update) > 0 && $salePermissions >= 'W' && check_bitrix_sessid())
{
	if ($isSuccess = $businessValueControl->setMapFromPost())
		$businessValueControl->saveMap();
}

?>
	<tr>
		<td colspan="2">
			<a href="sale_business_value_ptypes.php?lang=<?=LANGUAGE_ID?>"><?=Loc::getMessage('BIZVAL_PAGE_LINK_PTYPES')?></a>
			<br>&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?$businessValueControl->renderMap(array('CONSUMER_KEY' => '1C', 'HIDE_FILLED_CODES' => false))?>
		</td>
	</tr>
<?


