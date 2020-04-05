<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location;
use Bitrix\Sale\Location\Admin\LocationHelper as Helper;
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
	$itemId = intval($_REQUEST[Helper::URL_PARAM_PARENT_ID]) ? intval($_REQUEST[Helper::URL_PARAM_PARENT_ID]) : false;
	$nameToDisplay = Helper::getNameToDisplay($itemId);

	// get entity fields for columns & filter
	$columns = Helper::getColumns('list');

	$arFilterFields = array();
	$arFilterTitles = array();
	foreach($columns as $code => $fld)
	{
		$arFilterFields[] = 'find_'.$code;
		$arFilterTitles[] = $fld['title'];
	}

	$arFilterFields[] = 'find_level';
	$arFilterTitles[] = GetMessage('SALE_LOCATION_L_LEVEL');

	$sTableID = "tbl_location_node_list";

	// spike for filter
	if($_REQUEST['del_filter'] != 'Y')
	{
		if(isset($_SESSION['SESS_ADMIN'][$sTableID][Helper::URL_PARAM_PARENT_ID]) && isset($_REQUEST[Helper::URL_PARAM_PARENT_ID]) && ($_SESSION['SESS_ADMIN'][$sTableID][Helper::URL_PARAM_PARENT_ID] != $_REQUEST[Helper::URL_PARAM_PARENT_ID]))
			$_SESSION['SESS_ADMIN'][$sTableID][Helper::URL_PARAM_PARENT_ID] = $_REQUEST[Helper::URL_PARAM_PARENT_ID];
	}

	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		$arFilterTitles
	);
	$oSort = new CAdminSorting($sTableID, "SORT", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter($arFilterFields);

	// order, select and filter for the list
	$listParams = Helper::proxyListRequest('list');

	if(strlen($find_level) > 0)
	{
		if($find_level == 'ANY')
		{
			unset($listParams['filter']['=PARENT_ID']);
		}
		elseif($find_level == 'CURRENT_AND_LOWER')
		{
			if(intval($listParams['filter']['=PARENT_ID']) > 0)
			{
				$res = Location\LocationTable::getList(array(
					'filter' => array(
						'ID' => intval($listParams['filter']['=PARENT_ID'])
					),
					'select' => array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')
				));

				if($loc = $res->fetch())
				{
					$listParams['filter']['>LEFT_MARGIN'] = $loc['LEFT_MARGIN'];
					$listParams['filter']['<RIGHT_MARGIN'] = $loc['RIGHT_MARGIN'];
				}
			}

			unset($listParams['filter']['=PARENT_ID']);
		}
	}

	#####################################
	#### ACTIONS
	#####################################

	global $DB;

	// group UPDATE
	if($lAdmin->EditAction() && $userIsAdmin)
	{
		foreach($FIELDS as $id => $arFields)
		{
			$DB->StartTransaction();

			if(!$lAdmin->IsUpdated($id)) // if there were no data change on this row - do nothing with it
				continue;

			try
			{
				$res = Helper::update($id, $arFields, true);

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

		Location\LocationTable::resetLegacyPath();
	}

	// group DELETE
	if(($ids = $lAdmin->GroupAction()) && $userIsAdmin)
	{
		// all by filter or certain ids
		if($_REQUEST['action_target'] == 'selected') // get all ids if they were not specified (user choice was "for all")
		{
			$ids = Helper::getIdsByFilter($listParams['filter']);
		}

		@set_time_limit(0);

		foreach($ids as $id)
		{
			if(!($id = intval($id)))
			{
				continue;
			}

			if($_REQUEST['action'] == 'delete')
			{
				$DB->StartTransaction();

				try
				{
					$res = Helper::delete($id, true);
					if(!$res['success'])
					{
						throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_L_ITEM').' '.$id.' : '.implode('<br />', $res['errors']));
					}
					$DB->Commit();
				}
				catch(Main\SystemException $e)
				{
					$lAdmin->AddGroupError(Loc::getMessage('SALE_LOCATION_L_ITEM_DELETE_ERROR').": <br /><br />".$e->getMessage(), $id);
					$DB->Rollback();
				}
			}
		}

		Location\LocationTable::resetLegacyPath();
	}

	$adminResult = Helper::getList($listParams, $sTableID);
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
	foreach(Helper::getListGridColumns() as $code => $fld)
		$headers[] = array("id" => $code, "content" => $columns[$code]['title'], "default" => $fld['DEFAULT'], "sort" => $code);

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
		$editUrl = Helper::getEditUrl($elem['ID']);
		$copyUrl = Helper::getEditUrl(false, array('copy_id' => $elem['ID']));
		$listUrl = Helper::getListUrl($elem['ID'], array());

		$row =& $lAdmin->AddRow($f_ID, $elem, $listUrl, Loc::getMessage('SALE_LOCATION_L_VIEW_CHILDREN'));

		foreach($columns as $code => $fld)
		{
			if($code == 'ID')
				$row->AddViewField($code, '<a href="'.$editUrl.'" title="'.Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM').'">'.$f_ID.'</a>');
			elseif($code == 'TYPE_ID')
				$row->AddSelectField($code, Helper::getTypeList());
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
			"LINK"	=> Helper::getEditUrl(false, array('parent_id' => $itemId)),
			"TITLE"	=> Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			"ICON"	=> "btn_new"
		)
	);

	if($_REQUEST[Helper::URL_PARAM_PARENT_ID] > 0)
	{
		$aContext[] = array(
			"TEXT"	=> GetMessage('SALE_LOCATION_L_EDIT_CURRENT'),
			"LINK"	=> Helper::getEditUrl(false, array('id' => $_REQUEST[Helper::URL_PARAM_PARENT_ID])),
			"TITLE"	=> GetMessage('SALE_LOCATION_L_EDIT_CURRENT')
		);
	};
	$lAdmin->AddAdminContextMenu($aContext);
	$lAdmin->CheckListMode();

} // empty($fatal)
?>

<?$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_L_EDIT_PAGE_TITLE').($nameToDisplay ? ': '.$nameToDisplay : ''))?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<?
#####################################
#### Data output
#####################################
?>

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
				<tr>

					<td><?=htmlspecialcharsEx($fld['title'])?><?if($fld['data_type'] == 'integer' && $code != 'PARENT_ID'):?> (<?=Loc::getMessage('SALE_LOCATION_L_FROM_AND_TO')?>)<?endif?>:</td>
					<td>

					<?if($code == 'TYPE_ID'):?>

						<select name="find_<?=$code?>">
							<option value="">(<?=Loc::getMessage('SALE_LOCATION_L_ANY')?>)</option>
							<?foreach(Helper::getTypeList() as $tId => $tName):?>
								<option value="<?=intval($tId)?>"<?=($tId == $GLOBALS['find_'.$code] ? ' selected' : '')?>><?=htmlspecialcharsbx($tName)?></option>
							<?endforeach?>
						</select>

					<?elseif($code == 'PARENT_ID'):?>

						<div style="width: 100%; margin-left: 12px">

							<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
								"ID" => intval($GLOBALS['find_'.$code]) ? intval($GLOBALS['find_'.$code]) : $listParams['filter']['=PARENT_ID'],
								"CODE" => "",
								"INPUT_NAME" => 'find_'.$code,
								"PROVIDE_LINK_BY" => "id",
								"SHOW_ADMIN_CONTROLS" => 'N',
								"SELECT_WHEN_SINGLE" => 'N',
								"FILTER_BY_SITE" => 'N',
								"SHOW_DEFAULT_LOCATIONS" => 'N',
								"SEARCH_BY_PRIMARY" => 'Y',
								"INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
								"GLOBAL_EVENT_SCOPE" => 'window'
								),
								false
							);?>

						</div>

						<style>
							.adm-filter-item-center,
							.adm-filter-content {
								overflow: visible !important;
							}
						</style>

					<?else:?>

						<?if($fld['data_type'] == 'integer'):?>
							<input type="text" name="find_<?=$code?>_1" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code.'_1'])?>" />
							...
							<input type="text" name="find_<?=$code?>_2" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code.'_2'])?>" />
						<?else:?>
							<input type="text" name="find_<?=$code?>" value="<?=htmlspecialcharsbx($GLOBALS['find_'.$code])?>" />
						<?endif?>

					<?endif?>

					</td>
				</tr>
			<?endforeach?>
		<tr>
			<td><?=GetMessage('SALE_LOCATION_L_LEVEL')?>:</td>
			<td>
				<select name="find_level">
					<option value="ANY"<?if($find_level == "ANY") echo " selected"?>><?=GetMessage('SALE_LOCATION_L_ANY')?></option>
					<option value="CURRENT_AND_LOWER"<?if($find_level == "CURRENT_AND_LOWER") echo " selected"?>><?=GetMessage('SALE_LOCATION_L_CURRENT_AND_LOWER')?></option>
					<option value="CURRENT"<?if($find_level == "CURRENT") echo " selected"?>><?=GetMessage('SALE_LOCATION_L_CURRENT')?></option>
				</select>
		</tr>
		<?

		$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPageParam(), "form" => "filter_form"));
		$oFilter->End();
		?>

	</form>

	<?$lAdmin->DisplayList();?>

<?endif?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>