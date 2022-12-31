<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Catalog\Helpers\Admin\CatalogEdit,
	Bitrix\Catalog\Access\ActionDictionary,
	Bitrix\Catalog\Access\AccessController;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

Loader::includeModule('catalog');
$readOnly = !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS);
if ($readOnly && !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('BX_CATALOG_SETTINGS_ACCESS_DENIED'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

$request = Main\Context::getCurrent()->getRequest();

$iblockId = (isset($request['IBLOCK_ID']) ? (int)$request['IBLOCK_ID'] : 0);
$catalogEdit = new CatalogEdit($iblockId);
if (!$catalogEdit->isSuccess())
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(implode(' ', $catalogEdit->getErrors()));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

$iblock = $catalogEdit->getIblock();
$enableSaleRecurring = $catalogEdit->isEnableRecurring();

$tabList = array(
	array(
		'ICON' => 'catalog',
		'DIV' => 'iblockCatalogEdit01',
		'TAB' => Loc::getMessage('BX_CAT_IBLOCK_CATALOG_EDIT_TAB_NAME_COMMON'),
		'TITLE' => Loc::getMessage('BX_CAT_IBLOCK_CATALOG_EDIT_TAB_TITLE_COMMON')
	)
);

$postParams = array(
	'bxpublic' => 'Y',
	'sessid' => bitrix_sessid()
);
$listUrl = array(
	'LINK' => $APPLICATION->GetCurPageParam(),
	'POST_PARAMS' => $postParams,
);
unset($postParams);

$iblockCatalogFormID = 'iblockCatalogControl';
$control = new CAdminSubForm($iblockCatalogFormID, $tabList, false, true, $listUrl, false);
$iblockCatalogFormID .= '_form';
unset($tabList);

$vatList = array(0 => Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_NOT_SELECT'));
$vatIterator = Catalog\VatTable::getList(array(
	'select' => array('ID', 'NAME', 'SORT'),
	'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
));
while ($vat = $vatIterator->fetch())
	$vatList[$vat['ID']] = $vat['NAME'];
unset($vat, $vatIterator);

$errors = array();
$fields = array();

if ($request->isPost() && $request['save'] != '')
{
	if (!check_bitrix_sessid())
	{
		$errors[] = Loc::getMessage('BX_CAT_IBLOCK_CATALOG_IBLOCK_BAD_SESSION');
	}
	if (empty($errors))
	{
		$post = $request->getPostList()->toArray();
		$catalogEdit->saveCatalog($post);
		if (!$catalogEdit->isSuccess())
		{
			$errors = $catalogEdit->getErrors();
		}
	}
	if (empty($errors))
	{

	}
	if (!empty($errors))
	{
		$errorMessage = new CAdminMessage(
			array(
				'DETAILS' => implode('<br>', $errors),
				'TYPE' => 'ERROR',
				'MESSAGE' => Loc::getMessage('BX_CAT_IBLOCK_CATALOG_ERR_SAVE'),
				'HTML' => true
			)
		);
		echo $errorMessage->Show();
	}
	else
	{
		CAdminSubForm::closeSubForm();
	}
}
elseif ($request['dontsave'] != '')
{
	CAdminSubForm::closeSubForm(false);
}

$APPLICATION->SetTitle(Loc::getMessage('BX_CAT_IBLOCK_CATALOG_EDIT_TITLE_EDIT'));

Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/iblock_catalog.js');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$defaultValues = array(
	'IBLOCK_ID' => $iblockId,
	'PRODUCT_IBLOCK_ID' => 0,
	'SKU_PROPERTY_ID' => 0,
	'YANDEX_EXPORT' => 'N',
	'SUBSCRIPTION' => 'N',
	'VAT_ID' => 0,
	'CATALOG' => 'N',
	'CATALOG_TYPE' => ''
);

$catalog = $catalogEdit->getCatalog();
if (empty($catalog))
	$catalog = $defaultValues;

if (!empty($errors))
	$catalog = array_merge($catalog, $fields);

$offerList = array();
$productIblock = array();
if ($catalog['CATALOG_TYPE'] != CCatalogSKU::TYPE_OFFERS)
{
	$iblockList = array();
	$productList = array();
	$iblockIterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array('PRODUCT_IBLOCK_ID'),
		'filter' => array('!=PRODUCT_IBLOCK_ID' => 0),
	));
	while ($product = $iblockIterator->fetch())
	{
		$product['PRODUCT_IBLOCK_ID'] = (int)$product['PRODUCT_IBLOCK_ID'];
		$productList[$product['PRODUCT_IBLOCK_ID']] = $product['PRODUCT_IBLOCK_ID'];
	}
	unset($product, $iblockIterator);
	$iblockIterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID'),
		'filter' => array('!=IBLOCK_ID' => $iblockId, '=PRODUCT_IBLOCK_ID' => 0),
		'order' => array('IBLOCK_ID' => 'ASC')
	));
	while ($offer = $iblockIterator->fetch())
	{
		$offer['IBLOCK_ID'] = (int)$offer['IBLOCK_ID'];
		if (!isset($productList[$offer['IBLOCK_ID']]) && $offer['IBLOCK_ID'] != $iblockId)
			$iblockList[$offer['IBLOCK_ID']] = $offer['IBLOCK_ID'];
	}
	unset($offer, $iblockIterator);
	unset($productList);
	if ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_PRODUCT || $catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_FULL)
		$iblockList[$catalog['IBLOCK_ID']] = $catalog['IBLOCK_ID'];
	if (!empty($iblockList))
	{
		$siteList = array_fill_keys($iblockList, array());
		$sitesIterator = Iblock\IblockSiteTable::getList(array(
			'select' => array('IBLOCK_ID', 'SITE_ID'),
			'filter' => array('@IBLOCK_ID' => $iblockList),
			'order' => array('IBLOCK_ID' => 'ASC', 'SITE_ID' => 'ASC')
		));
		while ($site = $sitesIterator->fetch())
			$siteList[$site['IBLOCK_ID']][] = $site['SITE_ID'];
		unset($site, $sitesIterator);
		foreach ($siteList as $siteIblock => $sites)
		{
			if ($iblock['SITES'] == implode('|', $sites))
				$offerList[] = $siteIblock;
		}
		unset($siteIblock, $sites);
		unset($siteList);
	}
	unset($iblockList);
}
else
{
	$productIblock = Iblock\IblockTable::getList(array(
		'select' => array('ID', 'NAME', 'IBLOCK_TYPE_ID', 'ACTIVE', 'PROPERTY_INDEX'),
		'filter' => array('=ID' => $catalog['PRODUCT_IBLOCK_ID'])
	))->fetch();
}
$showSubscription = ($enableSaleRecurring || $catalog['SUBSCRIPTION'] == 'Y');
$rowDisplay = ($catalog['CATALOG'] == 'Y' ? 'table-row' : 'none');

$control->BeginPrologContent();
$control->EndPrologContent();
$control->BeginEpilogContent();
?>
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
	<input type="hidden" name="IBLOCK_ID" value="<? echo $iblockId; ?>">
<?
echo bitrix_sessid_post();
$control->EndEpilogContent();
$control->Begin(array(
	'FORM_ACTION' => 'cat_iblock_catalog_edit.php?lang='.LANGUAGE_ID
));
$control->BeginNextFormTab();
$control->AddViewField('IBLOCK_ID', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_IBLOCK_ID'), $iblockId);
$control->AddViewField('IBLOCK_NAME', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_IBLOCK_NAME'), $iblock['NAME']);
$control->AddViewField('IBLOCK_TYPE', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_IBLOCK_TYPE'), $iblock['TYPE']);
$control->AddViewField('IBLOCK_SITE', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_IBLOCK_SITES'), $iblock['SITES']);
$control->AddViewField(
	'IBLOCK_ACTIVE',
	Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_IBLOCK_ACTIVE'),
	($iblock['ACTIVE'] == 'Y' ? Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_YES') : Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_NO'))
);
$control->BeginCustomField('CATALOG', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_CATALOG'), true);
?><tr id="tr_CATALOG">
	<td style="width: 40%;"><? echo $control->GetCustomLabelHTML(); ?></td>
	<td style="width: 60%;">
		<input type="hidden" name="CATALOG" value="N" id="CATALOG_N">
		<input data-checkbox="Y" type="checkbox" name="CATALOG" value="Y" id="CATALOG_Y"<?
			echo ($catalog['CATALOG'] == 'Y' ? ' checked' : '').($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_OFFERS ? ' disabled' : '');
		?>>
	</td>
</tr><?
$control->EndCustomField('CATALOG', '<input type="hidden" name="CATALOG" value="'.$catalog['CATALOG'].'">');
$hiddenValue = '';
$control->BeginCustomField('SKU', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_SKU'));
?><tr id="tr_SKU">
	<td style="width: 40%; vertical-align: top;"><? echo $control->GetCustomLabelHTML(); ?></td>
	<td style="width: 60%;"><?
		$productIblockId = 0;
		if ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_OFFERS)
		{
			?><input type="hidden" name="USE_SKU" value="N" id="USE_SKU_N"><?
			$hiddenValue = '<input type="hidden" name="USE_SKU" value="N">';
			if (empty($productIblock))
			{
				echo Loc::getMessage('BX_CAT_IBLOCK_CATALOG_ERR_BAD_PRODUCT_IBLOCK', array('#ID#' => $catalog['PRODUCT_IBLOCK_ID']));
			}
			else
			{
				echo Loc::getMessage(
					'BX_CAT_IBLOCK_CATALOG_MESS_PRODUCT_IBLOCK',
					array(
						'#LINK#' => '',
						'#TITLE#' => '['.$productIblock['ID'].'] '.htmlspecialcharsEx($productIblock['NAME'])
					)
				);
			}
			$productIblockId = $catalog['PRODUCT_IBLOCK_ID'];
			?><input type="hidden" name="SKU" value="<? echo $productIblockId; ?>"><?
		}
		else
		{
			if ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_PRODUCT || $catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_FULL)
				$productIblockId = $catalog['IBLOCK_ID'];
			$showSku = ($productIblockId > 0);
			$hiddenValue = '<input type="hidden" name="USE_SKU" value="'.($showSku ? 'Y' : 'N').'">';
			?><input type="hidden" name="USE_SKU" value="N" id="USE_SKU_N">
			<input data-checkbox="Y" type="checkbox" name="USE_SKU" value="Y" id="USE_SKU_Y"<? echo ($showSku ? ' checked' : ''); ?>>
			<div id="sku_data" style="display: <? echo ($showSku ? 'block' : 'none'); ?>;">
			<?
			if (!empty($offerList))
			{
				echo GetIBlockDropDownListEx(
					$productIblockId,
					'SKU_TYPE',
					'SKU',
					array(
						'ID' => $offerList,
						'MIN_PERMISSION' => 'R'
					)
				);
			}
			else
			{
				echo Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_EMPTY_OFFERS_LIST');
			}
			?></div><?
		}
	?></td>
</tr><?
$hiddenValue .= '<input type="hidden" name="SKU" value="'.$productIblockId.'">';
$control->EndCustomField('SKU', $hiddenValue);
unset($hiddenValue);
if ($showSubscription)
{
	$hiddenValue = $catalog['SUBSCRIPTION'];
	$control->BeginCustomField('SUBSCRIPTION', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_SUBSCRIPTION'));
	?>
	<tr id="tr_SUBSCRIPTION" style="display: <? echo $rowDisplay; ?>;">
	<td style="width: 40%;"><? echo $control->GetCustomLabelHTML(); ?></td>
	<td style="width: 60%;"><?
	if ($enableSaleRecurring)
	{
		$subscriptionWithSku = ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_PRODUCT || $catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_FULL);
		?><input type="hidden" name="SUBSCRIPTION" value="N" id="SUBSCRIPTION_N">
		<input data-checkbox="Y" type="checkbox" name="SUBSCRIPTION" value="Y" id="SUBSCRIPTION_Y"<?
			echo ($catalog['SUBSCRIPTION'] == 'Y' ? ' checked' : '').(
				$subscriptionWithSku ? ' disabled' : ''
			);
		?>><?
		if ($subscriptionWithSku && $catalog['SUBSCRIPTION'] == 'Y')
		{
			$hiddenValue = 'N';
			?><br><? echo Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_SUBSCRIPTION_WITH_SKU');
		}
		unset($subscriptionWithSku);
	}
	else
	{
		?><input data-checkbox="Y" type="checkbox" name="SUBSCRIPTION" value="Y" id="SUBSCRIPTION_Y" checked disabled>
		<input type="hidden" name="SUBSCRIPTION" value="N" id="SUBSCRIPTION_N"><br><?
		echo Loc::getMessage('BX_CAT_IBLOCK_CATALOG_MESS_SUBSCRIPTION_RESET');
		$hiddenValue = 'N';
	}
	?></td>
	</tr><?
	$control->EndCustomField('SUBSCRIPTION', '<input type="hidden" name="SUBSCRIPTION" value="'.$hiddenValue.'">');
	unset($hiddenValue);
}
$control->BeginCustomField('YANDEX_EXPORT', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_YANDEX_EXPORT'));
$hiddenValue = $catalog['YANDEX_EXPORT'];
?>
<tr id="tr_YANDEX_EXPORT" style="display: <? echo $rowDisplay; ?>;">
	<td style="width: 40%;"><? echo $control->GetCustomLabelHTML(); ?></td>
	<td style="width: 60%;">
		<input type="hidden" id="YANDEX_EXPORT_N" name="YANDEX_EXPORT" value="N">
		<input type="checkbox" id="YANDEX_EXPORT_Y" name="YANDEX_EXPORT" value="Y"<? echo($catalog['YANDEX_EXPORT'] == 'Y' ? ' checked' : '');?>>
	</td>
</tr><?
$control->EndCustomField('YANDEX_EXPORT', $hiddenValue);
unset($hiddenValue);

$control->BeginCustomField('VAT_ID', Loc::getMessage('BX_CAT_IBLOCK_CATALOG_FIELD_VAT_ID'));
$hiddenValue = $catalog['VAT_ID'];
?><tr id="tr_VAT_ID" style="display: <? echo $rowDisplay; ?>;">
	<td style="width: 40%;"><? echo $control->GetCustomLabelHTML(); ?></td>
	<td style="width: 60%;">
		<select name="VAT_ID">
		<?
		foreach ($vatList as $vatId => $vatName)
		{
			?><option value="<? echo $vatId; ?>"<? echo ($catalog['VAT_ID'] == $vatId ? ' selected' : ''); ?>><? echo htmlspecialcharsEx($vatName); ?></option><?
		}
		unset($vatId, $vatName);
		?>
		</select>
	</td>
</tr><?
$control->EndCustomField('VAT_ID', $hiddenValue);

$save = "{
	title: '".CUtil::JSEscape(Loc::getMessage('BX_CAT_IBLOCK_CATALOG_BTN_SAVE'))."',
	id: 'saveCatalogBtn',
	name: 'saveCatalogBtn',
	className: 'adm-btn-save',
}";
$cancel = "{
	title: '".CUtil::JSEscape(Loc::getMessage('BX_CAT_IBLOCK_CATALOG_BTN_CANCEL'))."',
	name: 'cancelCatalogBtn',
	id: 'cancelCatalogBtn',
	action: function() {
		top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
		if (!!top.ReloadSubList)
			top.ReloadSubList();
	}
}";
$control->ButtonsPublic(array(
	$save,
	$cancel
));
unset($cancel, $save);

$control->Show();

unset($rowDisplay);

echo BeginNote('id="'.$iblockCatalogFormID.'_process" style="display: none"');

if ($enableSaleRecurring)
{

}
echo EndNote();
$ajaxSteps = array();
if ($enableSaleRecurring)
{

}
$jsParams = array(
	'containerId' => $iblockCatalogFormID,
	'enableSaleRecurring' => $enableSaleRecurring,
	'isSku' => ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_OFFERS),
	'processBlockId' => $iblockCatalogFormID.'_process',
	'buttons' => array(
		'save' => 'saveCatalogBtn',
		'cancel' => 'cancelCatalogBtn'
	),
	'ajaxSteps' => $ajaxSteps
);
?><script type="text/javascript">
var iblockCatalogControl = new BX.Catalog.Admin.IblockCatalog(<? echo CUtil::PhpToJSObject($jsParams, false, false, true); ?>);
BX.ready(function()
{
	top.BX.WindowManager.Get().adjustSizeEx();
});
</script><?
unset($jsParams);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');