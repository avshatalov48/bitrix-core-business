<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\TypeHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

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
	$filterFields = array();
	$listDefaultFields = array("ID");
	foreach($columns as $code => $fld)
	{
		$fType = "";
		$filterable = "";
		switch ($fld["data_type"])
		{
			case "string":
				$filterable = "?";
				break;
			case "integer":
				$fType = "number";
				$filterable = "=";
				break;
		}
		$filterField = array(
			"id" => $code,
			"name" => $columns[$code]["title"]
		);
		if ($fType)
		{
			$filterField["type"] = $fType;
		}
		$filterField["filterable"] = $filterable;
		if (in_array($code, $listDefaultFields))
		{
			$filterField["default"] = true;
		}
		if ($code == "ID")
		{
			$filterField["quickSearch"] = "=";
		}
		$filterFields[] = $filterField;
	}

	$sTableID = "tbl_type_list";
	$oSort = new CAdminSorting($sTableID, "SORT", "asc");
	$lAdmin = new CAdminUiList($sTableID, $oSort);

	// order, select and filter for the list
	$listParams = Helper::proxyListRequest('list');

	$lAdmin->AddFilter($filterFields, $listParams['filter']);

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
		if ($lAdmin->hasGroupErrors())
		{
			$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
		}
		else
		{
			$adminSidePanelHelper->sendSuccessResponse();
		}
	}

	$adminResult = Helper::getList($listParams, $sTableID, CAdminUiResult::GetNavSize($sTableID), array("uiMode" => true));
	$adminResult = new \CAdminUiResult($adminResult, $sTableID);
	$lAdmin->SetNavigationParams($adminResult, array("BASE_LINK" => $selfFolderUrl."sale_location_type_list.php"));
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
	while($elem = $adminResult->NavNext(false))
	{
		// urls
		$editUrl = Helper::getEditUrl(array('id' => $elem['ID']));
		$copyUrl = Helper::getEditUrl(array('copy_id' => $elem['ID']));
		$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
		$copyUrl = $adminSidePanelHelper->editUrlToPublicPage($copyUrl);

		$row =& $lAdmin->AddRow($elem["ID"], $elem, $editUrl, Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'));

		foreach($columns as $code => $fld)
		{
			if($code == 'ID')
				$row->AddViewField($code, '<a href="'.$editUrl.'" title="'.Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM').'">'.$elem["ID"].'</a>');
			else
				$row->AddInputField($code);
		}

		$arActions = array();

		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'),
			"LINK" => $editUrl,
			"DEFAULT" => true
		);

		if($userIsAdmin)
		{
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => Loc::getMessage('SALE_LOCATION_L_COPY_ITEM'),
				"LINK" => $copyUrl
			);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage('SALE_LOCATION_L_DELETE_ITEM'),
				"ACTION" => "if(confirm('".CUtil::JSEscape(Loc::getMessage('SALE_LOCATION_L_CONFIRM_DELETE_ITEM'))."')) ".$lAdmin->ActionDoGroup($elem["ID"], "delete")
			);
		}

		$row->AddActions($arActions);
	}

	$lAdmin->AddGroupActionTable(Array(
		"delete" => true
	));

	$aContext = array(
		array(
			"TEXT"	=> Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			"LINK"	=> $adminSidePanelHelper->editUrlToPublicPage(Helper::getEditUrl()),
			"TITLE"	=> Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			"ICON"	=> "btn_new"
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_location_type_list.php"));
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
	<?
	$messageParams = array('MESSAGE' => $fatal, 'type' => 'ERROR');
	if ($publicMode)
	{
		$messageParams["SKIP_PUBLIC_MODE"] = true;
	}
	?>
	<div class="error-message">
		<?CAdminMessage::ShowMessage($messageParams)?>
	</div>

<?else:?>

	<?$lAdmin->DisplayFilter($filterFields);?>
	<?$lAdmin->DisplayList();?>

<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>