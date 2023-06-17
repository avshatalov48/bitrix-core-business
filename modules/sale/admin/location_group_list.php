<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\GroupHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

/** @global CMain $APPLICATION */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('sale');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php';

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

if ($APPLICATION->GetGroupRight("sale") < "W")
{
	$APPLICATION->AuthForm(Loc::getMessage('SALE_MODULE_ACCES_DENIED'));
}

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";

#####################################
#### Data prepare
#####################################

$fatal = '';
$columns = [];
$filterFields = [];
$sTableID = 'tbl_entity';
$oSort = new CAdminUiSorting($sTableID, 'SORT', 'asc');
$lAdmin = new CAdminUiList($sTableID, $oSort);

try
{
	// get entity fields for columns & filter

	$langObject = new \CLanguage();
	$langQueryObject = $langObject->getList();
	$quickSearchLangId = "EN";
	$quickSearchAlreadyUse = false;
	while($lang = $langQueryObject->fetch())
	{
		if ($lang["DEF"] == "Y")
		{
			$quickSearchLangId = mb_strtoupper($lang["LANGUAGE_ID"]);
		}
	}

	$columns = Helper::getColumns('list');
	$listDefaultFields = array("ID");
	foreach ($columns as $code => $fld)
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
			"name" => $fld["title"]
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
		$match = [];
		if (preg_match("/^NAME_/", $code, $match))
		{
			$langId = str_replace($match[0], "", $code);
			$filterField["id"] = "NAME__".$langId.".".rtrim($match[0], "_");
			if ($quickSearchLangId == $langId && !$quickSearchAlreadyUse)
			{
				$filterField["quickSearch"] = "%";
				$quickSearchAlreadyUse = true;
			}
		}
		$filterFields[] = $filterField;
	}

	// order, select and filter for the list
	$listParams = Helper::proxyListRequest('list');

	$lAdmin->AddFilter($filterFields, $listParams['filter']);

	#####################################
	#### ACTIONS
	#####################################

	global $DB;

	if ($lAdmin->EditAction() && $userIsAdmin)
	{
		foreach ($lAdmin->GetEditFields() as $id => $arFields)
		{
			if (!$lAdmin->IsUpdated($id)) // if there were no data change on this row - do nothing with it
				continue;

			$DB->StartTransaction();

			try
			{
				$res = Helper::updateFields($id, $arFields);

				if(!empty($res['errors']))
				{
					foreach($res['errors'] as &$error)
						$error = '&nbsp;&nbsp;'.$error;
					unset($error);

					throw new Main\SystemException(implode(',<br />', $res['errors']));
				}
				$DB->Commit();
			}
			catch(Main\SystemException $e)
			{
				// todo: do smth
				$lAdmin->AddUpdateError(Loc::getMessage('SALE_LOCATION_L_ITEM_SAVE_ERROR', array('#ITEM#' => $id)).": <br />".$e->getMessage().'<br />', $id);
				$DB->Rollback();
			}
		}
	}

	$ids = $lAdmin->GroupAction();
	if ($ids && $userIsAdmin)
	{
		if ($lAdmin->IsGroupActionToAll())
		{
			// get all ids if they were not specified (user choice was "for all")
			$ids = Helper::getIdsByFilter($listParams['filter']);
		}

		@set_time_limit(0);

		$action = $lAdmin->GetAction();
		foreach($ids as $id)
		{
			if(!($id = intval($id)))
				continue;

			if ($action === 'delete')
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
	$lAdmin->SetNavigationParams($adminResult, array("BASE_LINK" => $selfFolderUrl."sale_location_group_list.php"));
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
				"ACTION" => "if(confirm('".CUtil::JSEscape(Loc::getMessage('SALE_LOCATION_L_CONFIRM_DELETE_ITEM')).
					"')) ".$lAdmin->ActionDoGroup($elem["ID"], "delete")
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
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_location_group_list.php"));
	$lAdmin->AddAdminContextMenu($aContext);
	$lAdmin->CheckListMode();

} // empty($fatal)

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_L_EDIT_PAGE_TITLE'));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

#####################################
#### Data output
#####################################

//temporal code
if (!CSaleLocation::locationProCheckEnabled())
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
}
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	SearchHelper::checkIndexesValid();

	if($fatal <> '')
	{
		$messageParams = array('MESSAGE' => $fatal, 'type' => 'ERROR');
		if($publicMode)
		{
			$messageParams["SKIP_PUBLIC_MODE"] = true;
		}
		?>
		<div class="error-message">
			<?php
			CAdminMessage::ShowMessage($messageParams);
			?>
		</div>
		<?php
	}
	else
	{
		$lAdmin->DisplayFilter($filterFields);
		$lAdmin->DisplayList();
	}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
