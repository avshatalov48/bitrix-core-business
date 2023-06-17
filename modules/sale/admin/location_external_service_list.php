<?php

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

/** @global CMain $APPLICATION */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("sale") < "W")
{
	$APPLICATION->AuthForm(Loc::getMessage('SALE_MODULE_ACCES_DENIED'));
}

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";

#####################################
#### Data prepare
#####################################

$request = Context::getCurrent()->getRequest();

$itemId = (int)$request->get('id');
if ($itemId <= 0)
{
	$itemId = false;
}

$fatal = '';
$columns = [];

$sTableID = "tbl_external_service_list";
$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

try
{
	// get entity fields for columns & filter
	$columns = Helper::getColumns('list');

	$arFilterFields = array();
	$arFilterTitles = array();
	foreach($columns as $code => $fld)
	{
		$arFilterFields[] = 'find_'.$code;
		$arFilterTitles[] = $fld['title'];
	}

	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		$arFilterTitles
	);

	$lAdmin->InitFilter($arFilterFields);

	// order, select and filter for the list
	$listParams = Helper::proxyListRequest('list');

	#####################################
	#### ACTIONS
	#####################################

	global $DB;

	if ($lAdmin->EditAction() && $userIsAdmin)
	{
		foreach ($lAdmin->GetEditFields() as $id => $arFields)
		{
			if (!$lAdmin->IsUpdated($id))
			{
				// if there were no data change on this row - do nothing with it
				continue;
			}

			$DB->StartTransaction();

			try
			{
				$res = Helper::update($id, $arFields);
				if (!empty($res['errors']))
				{
					foreach ($res['errors'] as &$error)
					{
						$error = '&nbsp;&nbsp;' . $error;
					}
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
		foreach ($ids as $id)
		{
			$id = (int)$id;
			if ($id <= 0)
			{
				continue;
			}

			if ($action === 'delete')
			{
				$DB->StartTransaction();

				try
				{
					$res = Helper::delete($id);
					if (!$res['success'])
					{
						throw new Main\SystemException(
							Loc::getMessage('SALE_LOCATION_L_ITEM')
							. ' ' . $id . ' : '
							. implode('<br />', $res['errors'])
						);
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
	}

	$adminResult = Helper::getList($listParams, $sTableID);
	$adminResult->NavStart();
	$lAdmin->NavText($adminResult->GetNavPrint(Loc::getMessage('SALE_LOCATION_L_PAGES'), true)); // do not relocate the call relative to DisplayList(), or you`ll catch a strange nav bar disapper bug
}
catch(Main\SystemException $e)
{
	$code = $e->getCode();
	$fatal = $e->getMessage() . (!empty($code) ? ' (' . $code . ')' : '');
}

#####################################
#### PAGE INTERFACE GENERATION
#####################################

if  (empty($fatal))
{
	$headers = [];
	foreach($columns as $code => $fld)
	{
		$headers[] = [
			'id' => $code,
			'content' => $fld['title'],
			'sort' => $code,
			'default' => true,
		];
	}

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
		$editUrl = Helper::getEditUrl(['id' => $elem['ID']]);
		$copyUrl = Helper::getEditUrl(['copy_id' => $elem['ID']]);

		$row =& $lAdmin->AddRow($elem['ID'], $elem, $editUrl, Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'));

		foreach($columns as $code => $fld)
		{
			if ($code === 'ID')
			{
				$row->AddViewField($code, '<a href="'
					. $editUrl
					. '" title="'
					. Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM')
					. '">'
					. $elem['ID']
					. '</a>'
				);
			}
			else
			{
				$row->AddInputField($code);
			}
		}

		$arActions = [];

		$arActions[] = [
			"ICON" => "edit",
			"TEXT" => Loc::getMessage('SALE_LOCATION_L_EDIT_ITEM'),
			"ACTION" => $lAdmin->ActionRedirect($editUrl),
			"DEFAULT" => true,
		];

		if ($userIsAdmin)
		{
			$arActions[] = [
				"ICON" => "copy",
				"TEXT" => Loc::getMessage('SALE_LOCATION_L_COPY_ITEM'),
				"ACTION" => $lAdmin->ActionRedirect($copyUrl),
			];
			$arActions[] = [
				"SEPARATOR" => true
			];
			$arActions[] = [
				"ICON" => "delete",
				"TEXT" => Loc::getMessage('SALE_LOCATION_L_DELETE_ITEM'),
				"ACTION" =>
					"if(confirm('"
					. CUtil::JSEscape(Loc::getMessage('SALE_LOCATION_L_CONFIRM_DELETE_ITEM'))
					. "')) "
					. $lAdmin->ActionDoGroup($elem['ID'], "delete")
			];
		}

		$row->AddActions($arActions);
	}

	$lAdmin->AddGroupActionTable([
		"delete" => true,
	]);

	$aContext = [
		[
			'TEXT' => Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			'LINK' => Helper::getEditUrl(),
			'TITLE' => Loc::getMessage('SALE_LOCATION_L_ADD_ITEM'),
			'ICON' => 'btn_new',
		],
	];
	$lAdmin->AddAdminContextMenu($aContext);
	$lAdmin->CheckListMode();

} // empty($fatal)

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_L_EDIT_PAGE_TITLE'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

#####################################
#### Data output
#####################################

//temporal code
if (!CSaleLocation::locationProCheckEnabled())
{
	require($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");
}

SearchHelper::checkIndexesValid();

if ($fatal !== ''):
?>
	<div class="error-message">
		<?php
		CAdminMessage::ShowMessage([
			'MESSAGE' => $fatal,
			'type' => 'ERROR',
		]);
		?>
	</div>

<?php
else:
?>
	<form method="GET" action="<?= Helper::getListUrl($itemId? array('id' => $itemId) : array()) ?>" name="filter_form">
		<input type="hidden" name="filter" value="Y"/>
		<?php
		if ($itemId):
			?>
			<input type="hidden" name="id" value="<?= $itemId ?>"/>
			<?php
		endif;

		$oFilter->Begin();
		foreach ($columns as $code => $fld):
			//if(!in_array($code, $excludedColumns)):
			?>
			<tr>
				<td><?= htmlspecialcharsbx($fld['title']) ?><? if($fld['data_type'] == 'integer'): ?> (<?= Loc::getMessage('SALE_LOCATION_L_FROM_AND_TO') ?>)<? endif ?>
					:
				</td>
				<td>

					<? if($fld['data_type'] == 'integer'): ?>
						<input type="text" name="find_<?= $code ?>_1" value="<?= htmlspecialcharsbx($GLOBALS['find_'.$code.'_1'] ?? '') ?>"/>
						...
						<input type="text" name="find_<?= $code ?>_2" value="<?= htmlspecialcharsbx($GLOBALS['find_'.$code.'_2'] ?? '') ?>"/>
					<? else: ?>
						<input type="text" name="find_<?= $code ?>" value="<?= htmlspecialcharsbx($GLOBALS['find_'.$code] ?? '') ?>"/>
					<? endif ?>

				</td>

			</tr>
			<?php
			//endif
		endforeach;

		$oFilter->Buttons([
			'table_id' => $sTableID,
			'url' => $APPLICATION->GetCurPageParam(),
			'form' => 'filter_form',
		]);
		$oFilter->End();
		?>
	</form>
	<?php
	$lAdmin->DisplayList();
endif;

require ($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");
