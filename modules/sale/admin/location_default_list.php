<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\DefaultSiteHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage('SALE_MODULE_ACCES_DENIED'));

#####################################
#### Data prepare
#####################################

try
{
	$itemId = intval($_REQUEST['id']) ? intval($_REQUEST['id']) : false;

	// get entity fields for columns & filter
	$columns = Helper::getColumns('list');

	$arFilterFields = array();
	$arFilterTitles = array();
	foreach($columns as $code => $fld)
	{
		$arFilterFields[] = 'find_'.$code;
		$arFilterTitles[] = $fld['title'];
	}

	$sTableID = "tbl_location_default_list";

	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		$arFilterTitles
	);
	$oSort = new CAdminSorting($sTableID, "SORT", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter($arFilterFields);

	// order, select and filter for the list
	$adminResult = Helper::getList(Helper::proxyListRequest('list'), $sTableID);
	$adminResult->NavStart();
	$lAdmin->NavText($adminResult->GetNavPrint(Loc::getMessage('SALE_LOCATION_L_PAGES'), true)); // do not relocate the call relative to DisplayList(), or you`ll catch a strange nav bar disapper bug
}
catch(Main\SystemException $e)
{
	$code = $e->getCode();
	$fatal = $e->getMessage().(!empty($code) ? ' ('.$code.')' : '');
}

#####################################
#### PAGE INTERFACE GENERATION
#####################################

if(empty($fatal))
{
	$headers = array();
	foreach($columns as $code => $fld)
		$headers[] = array("id" => $code, "content" => $fld['title'], "sort" => $code, "default" => true);

	$lAdmin->AddHeaders($headers);
	while($elem = $adminResult->NavNext(true, "f_"))
	{
		// CAdminList will escape values by itself
		/*
		foreach($columns as $code => $fld)
		{
			if(isset($elem[$code]))
				Helper::makeSafeDisplay($elem[$code], $code);
		}
		*/

		// urls
		$editUrl = Helper::getEditUrl(array('id' => $elem['SITE_ID']));

		$row =& $lAdmin->AddRow($elem['SITE_ID'], $elem, $editUrl, Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'));

		foreach($columns as $code => $fld)
		{
			if($code == 'SITE_NAME')
				$row->AddViewField($code, '<a href="'.$editUrl.'" title="'.Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM').'">'.htmlspecialcharsbx($elem['NAME'].' ('.$elem['SITE_ID'].')').'</a>');
			else
				$row->AddViewField($code, $elem[$code]);
		}

		$arActions = array();
		$arActions[] = array("ICON" => "edit", "TEXT" => Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'), "ACTION" => $lAdmin->ActionRedirect($editUrl), "DEFAULT" => true);

		$row->AddActions($arActions);
	}


	$lAdmin->AddAdminContextMenu(array());
	$lAdmin->CheckListMode();

} // empty($fatal)
?>

<?$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_L_EDIT_PAGE_TITLE'))?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<?
#####################################
#### Data output
#####################################
?>

<?//temporal code?>
<?if(!CSaleLocation::locationProCheckEnabled())require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>

<?SearchHelper::checkIndexesValid();?>

<? if($fatal <> ''): ?>

	<div class="error-message">
		<? CAdminMessage::ShowMessage(array('MESSAGE' => $fatal, 'type' => 'ERROR')) ?>
	</div>

<? else: ?>

	<? $lAdmin->DisplayList(); ?>

<? endif?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>