<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\TypeHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage('SALE_MODULE_ACCES_DENIED'));

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";

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

	$sTableID = "tbl_type_list";

	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		$arFilterTitles
	);
	$oSort = new CAdminSorting($sTableID, "SORT", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter($arFilterFields);

	// order, select and filter for the list
	$listParams = Helper::proxyListRequest('list');

	#####################################
	#### ACTIONS
	#####################################

	global $DB;

	if($lAdmin->EditAction() && $userIsAdmin)
	{
		foreach($FIELDS as $id => $arFields)
		{
			$DB->StartTransaction();

			if(!$lAdmin->IsUpdated($id)) // if there were no data change on this row - do nothing with it
				continue;

			try
			{
				$res = Helper::update($id, $arFields);

				if(!empty($res['errors']))
				{
					foreach($res['errors'] as &$error)
						$error = '&nbsp;&nbsp;'.$error;
					unset($error);

					throw new Main\SystemException(implode(',<br />', $res['errors']));
				}
			}
			catch(Main\SystemException $e)
			{
				// todo: do smth
				$lAdmin->AddUpdateError(Loc::getMessage('SALE_LOCATION_L_ITEM_SAVE_ERROR', array('#ITEM#' => $id)).": <br />".$e->getMessage().'<br />', $id);
				$DB->Rollback();
			}

			$DB->Commit();
		}
	}

	if(($ids = $lAdmin->GroupAction()) && $userIsAdmin)
	{
		if($_REQUEST['action_target'] == 'selected') // get all ids if they were not specified (user choice was "for all")
			$ids = Helper::getIdsByFilter($listParams['filter']);

		@set_time_limit(0);

		foreach($ids as $id)
		{
			if(!($id = intval($id)))
				continue;

			if($_REQUEST['action'] == 'delete')
			{
				$DB->StartTransaction();

				try
				{
					$res = Helper::delete($id);
					if(!$res['success'])
						throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_L_ITEM').' '.$id.' : '.implode('<br />', $res['errors']));
					$DB->Commit();
				}
				catch(Main\SystemException $e)
				{
					$lAdmin->AddGroupError(Loc::getMessage('SALE_LOCATION_L_ITEM_DELETE_ERROR').": <br /><br />".$e->getMessage(), $id);
					$DB->Rollback();
				}
			}
		}
	}

	$adminResult = Helper::getList($listParams, $sTableID);
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
			if(isset($elem[$code]))
				Helper::makeSafeDisplay($elem[$code], $code);
		*/

		// urls
		$editUrl = Helper::getEditUrl(array('id' => $elem['ID']));
		$copyUrl = Helper::getEditUrl(array('copy_id' => $elem['ID']));

		$row =& $lAdmin->AddRow($f_ID, $elem, $editUrl, Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'));

		foreach($columns as $code => $fld)
		{
			if($code == 'ID')
				$row->AddViewField($code, '<a href="'.$editUrl.'" title="'.Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM').'">'.$f_ID.'</a>');
			else
				$row->AddInputField($code);
		}

		$arActions = array();

		$arActions[] = array("ICON" => "edit", "TEXT" => Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'), "ACTION" => $lAdmin->ActionRedirect($editUrl), "DEFAULT" => true);

		if($userIsAdmin)
		{
			$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage('SALE_LOCATION_L_COPY_ITEM'), "ACTION"=>$lAdmin->ActionRedirect($copyUrl));
			$arActions[] = array("SEPARATOR"=>true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage('SALE_LOCATION_L_DELETE_ITEM'), "ACTION"=>"if(confirm('".CUtil::JSEscape(Loc::getMessage('SALE_LOCATION_L_CONFIRM_DELETE_ITEM'))."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
		}

		$row->AddActions($arActions);
	}

	$lAdmin->AddGroupActionTable(Array(
		"delete" => true
	));

	$aContext = array(
		array(
			"TEXT"	=> Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			"LINK"	=> Helper::getEditUrl(),
			"TITLE"	=> Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			"ICON"	=> "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
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

<?if(strlen($fatal)):?>

	<div class="error-message">
		<?CAdminMessage::ShowMessage(array('MESSAGE' => $fatal, 'type' => 'ERROR'))?>
	</div>

<?else:?>

	<form method="GET" action="<?=Helper::getListUrl($itemId ? array('id' => $itemId) : array())?>" name="filter_form">

		<input type="hidden" name="filter" value="Y" />
		<?if($itemId):?>
			<input type="hidden" name="id" value="<?=$itemId?>" />
		<?endif?>

		<?$oFilter->Begin();?>
			<?foreach($columns as $code => $fld):?>
				<?//if(!in_array($code, $excludedColumns)):?>
					<tr>

						<td><?=htmlspecialcharsbx($fld['title'])?><?if($fld['data_type'] == 'integer'):?> (<?=Loc::getMessage('SALE_LOCATION_L_FROM_AND_TO')?>)<?endif?>:</td>
						<td>

							<?if($fld['data_type'] == 'integer'):?>
								<input type="text" name="find_<?=$code?>_1" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code.'_1'])?>" />
								...
								<input type="text" name="find_<?=$code?>_2" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code.'_2'])?>" />
							<?else:?>
								<input type="text" name="find_<?=$code?>" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code])?>" />
							<?endif?>

						</td>

					</tr>
				<?//endif?>
			<?endforeach?>
		<?
		$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPageParam(), "form"=>"filter_form"));
		$oFilter->End();
		?>

	</form>

	<?$lAdmin->DisplayList();?>

<?endif?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>