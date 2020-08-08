<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$publicMode = $adminPage->publicMode;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_status";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = array();
$arFilter["LID"] = LANGUAGE_ID;

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = [];

		$query = \Bitrix\Sale\Internals\StatusTable::query();
		$query->addSelect('ID');
		$query->where(
			\Bitrix\Main\ORM\Query\Query::filter()
				->logic('OR')
				->where('STATUS_LANG.LID', '=', LANGUAGE_ID)
				->where('STATUS_LANG.LID', NULL)
		);
		$query->addOrder($by, $order);

		$dbResultList = $query->exec();
		while ($arResult = $dbResultList->fetch())
		{
			$arID[] = $arResult['ID'];
		}
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				if (\Bitrix\Main\Loader::includeModule('crm'))
				{
					foreach (\Bitrix\Crm\Order\OrderStatus::getDefaultStatuses() as $statusId => $item)
					{
						if ($item['SYSTEM'] === 'Y')
						{
							$lockedStatusList[] = $statusId;
						}
					}

					foreach (\Bitrix\Crm\Order\DeliveryStatus::getDefaultStatuses() as $statusId => $item)
					{
						if ($item['SYSTEM'] === 'Y')
						{
							$lockedStatusList[] = $statusId;
						}
					}
				}
				else
				{
					$lockedStatusList = array(
						\Bitrix\Sale\OrderStatus::getInitialStatus(),
						\Bitrix\Sale\OrderStatus::getFinalStatus(),
						\Bitrix\Sale\DeliveryStatus::getInitialStatus(),
						\Bitrix\Sale\DeliveryStatus::getFinalStatus(),
					);
				}

				if (in_array($ID, $lockedStatusList))
				{
					continue 2;
				}

				$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Order $orderClass */
				$orderClass = $registry->getOrderClassName();

				$dbRes = $orderClass::getList([
					'select' => ['ID'],
					'filter' => ['=STATUS_ID' => $ID],
				]);
				if ($dbRes->fetch())
				{
					$lAdmin->AddGroupError(Loc::getMessage('ERROR_DEL_STATUS_ORDER_USE', ['#STATUS_ID#' => $ID]));
				}

				if (!$lAdmin->hasGroupErrors())
				{
					$dbRes = Sale\Shipment::getList([
						'select' => ['ID'],
						'filter' => ['=STATUS_ID' => $ID],
					]);
					if ($dbRes->fetch())
					{
						$lAdmin->AddGroupError(Loc::getMessage('ERROR_DEL_STATUS_SHIPMENT_USE', ['#STATUS_ID#' => $ID]));
					}
				}

				if (!$lAdmin->hasGroupErrors())
				{
					if (!CSaleStatus::Delete($ID))
					{
						if ($ex = $APPLICATION->GetException())
						{
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						}
						else
						{
							$lAdmin->AddGroupError(GetMessage("ERROR_DEL_STATUS"), $ID);
						}
					}
				}

				break;

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

$query = \Bitrix\Sale\Internals\StatusTable::query();
$query->setSelect([
	'ID', 'SORT', 'TYPE', 'NOTIFY', 'LID' => 'STATUS_LANG.LID',
	'COLOR' ,'NAME' => 'STATUS_LANG.NAME', 'DESCRIPTION' => 'STATUS_LANG.DESCRIPTION'
]);
$query->where(
	\Bitrix\Main\ORM\Query\Query::filter()
		->logic('OR')
		->where('STATUS_LANG.LID', '=', LANGUAGE_ID)
		->where('STATUS_LANG.LID', NULL)
);
$query->addOrder($by, $order);

$dbResultList = new CAdminUiResult($query->exec(), $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => "/bitrix/admin/sale_status.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("STATUS_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("STATUS_SORT"), "sort"=>"SORT", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SALE_NAME"), "sort"=>"", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SSAN_TYPE"), "sort"=>"TYPE", "default"=>true),
	array("id"=>"COLOR", "content"=>GetMessage("SSAN_COLOR"), "sort"=>"", "default"=>true),
	array('id' => 'NOTIFY', 'content' => GetMessage('SSAN_NOTIFY'), 'sort' => 'NOTIFY', 'default' => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbResultList->NavNext(false))
{
	$row =& $lAdmin->AddRow($arCCard["ID"], $arCCard, "sale_status_edit.php?ID=".$arCCard["ID"]."&lang=".LANGUAGE_ID."");

	$row->AddField("ID", "<a href=\"/bitrix/admin/sale_status_edit.php?ID=".$arCCard["ID"]."&lang=".LANGUAGE_ID."\" title=\"".GetMessage("SALE_EDIT_DESCR")."\">".$arCCard["ID"]."</a>");
	$row->AddField("SORT", $arCCard["SORT"]);
	$row->AddField("NAME", htmlspecialcharsbx($arCCard["NAME"])."<br><small>".htmlspecialcharsbx($arCCard["DESCRIPTION"])."</small><br>");
	$row->AddField(
		"COLOR",
		$arCCard["COLOR"] <> ''? "<div style=\"background:".$arCCard["COLOR"]."; width: 23px; border: 1px solid #87919c; border-radius: 4px; height: 23px;\"></div>" : $arCCard["COLOR"]
	);
	$row->AddField("TYPE", (
		$arCCard["TYPE"] == 'O' ? GetMessage('SSEN_TYPE_O') :
		($arCCard["TYPE"] == 'D' ? GetMessage('SSEN_TYPE_D') :
		'Invalid '.$arCCard["TYPE"])));
	$textForNotify = $arCCard["NOTIFY"] == 'Y'
		? '<a href="/bitrix/admin/message_admin.php?find_event_type=SALE_STATUS_CHANGED_'.$arCCard["ID"].
		'" target="_blank">'.GetMessage('SSAN_NOTIFY_Y').'</a>' : GetMessage('SSAN_NOTIFY_N');
	if ($publicMode)
	{
		$textForNotify = ($arCCard["NOTIFY"] == 'Y' ? GetMessage('SSAN_NOTIFY_Y') : GetMessage('SSAN_NOTIFY_N'));
	}
	$row->AddField("NOTIFY", $textForNotify);

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("SALE_EDIT_DESCR"),
		"ACTION" => $lAdmin->ActionRedirect("sale_status_edit.php?ID=".$arCCard["ID"]."&lang=".LANGUAGE_ID.""),
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W" && $arCCard["ID"] != "N" && $arCCard["ID"] != "F")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('STATUS_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($arCCard["ID"], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SSAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_status_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("SSAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => "/bitrix/admin/sale_status.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("STATUS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();
?>
<br>
<?echo BeginNote();?>
	<?echo GetMessage("SALE_NOTES1")?><br>
	<?echo GetMessage("SALE_NOTES2")?><br>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>