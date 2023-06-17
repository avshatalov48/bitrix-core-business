<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @global CMain $APPLICATION */

use	Bitrix\Sale\Helpers\Admin\BusinessValueControl;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$salePermissions = $APPLICATION->GetGroupRight('sale');

if ($salePermissions < 'R')
{
	return;
}

Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

$request = Context::getCurrent()->getRequest();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/lib/helpers/admin/businessvalue.php';

$errors = [];

$businessValueControl = new BusinessValueControl('bizval');

if ($request->isPost() && $request->getPost('Update') !== null && $salePermissions >= 'W' && check_bitrix_sessid())
{
	if ($isSuccess = $businessValueControl->setMapFromPost())
	{
		$businessValueControl->saveMap();
	}
}

?>
	<tr>
		<td colspan="2">
			<?= Loc::getMessage('BIZVAL_PAGE_LINK_PTYPES_V2'); ?>
			<br>&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			$businessValueControl->renderMap([
				'CONSUMER_KEY' => '1C',
				'HIDE_FILLED_CODES' => false,
			]);
			?>
		</td>
	</tr>
<?php
