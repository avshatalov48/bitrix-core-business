<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Cashbox\Internals;
use Bitrix\Main\Page;
use Bitrix\Main\Type\Date;

$publicMode = $adminPage->publicMode;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
CUtil::InitJSCore();
Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/cashbox_zreport.js");
\Bitrix\Main\Loader::includeModule('sale');
if ($publicMode)
{
	Page\Asset::getInstance()->addCss("/bitrix/themes/.default/sale.css");
}

$tableId = "tbl_sale_cashbox_zreport";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminUiSorting($tableId, "ID", "asc");
$lAdmin = new CAdminUiList($tableId, $oSort);

$cashBoxList = array();
$cashBoxQueryObject = Internals\CashboxTable::getList(
	array('filter' => array('USE_OFFLINE' => 'N', '%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\CashboxBitrix'))
);
while ($cashBox = $cashBoxQueryObject->fetch())
{
	$cashBoxList[$cashBox['ID']] = $cashBox['NAME'];
}

$filterFields = array(
	array(
		"id" => "CASHBOX_ID",
		"name" => GetMessage("SALE_F_CASHBOX"),
		"type" => "list",
		"items" => $cashBoxList,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "DATE_CREATE",
		"name" => GetMessage("SALE_Z_REPORT_CREATE"),
		"type" => "date",
		"default" => true
	),
);


$filter = array();
$lAdmin->AddFilter($filterFields, $filter);

$cashboxList = array();

if ((int)($filter["CASHBOX_ID"]) > 0)
{
	$cashboxData = Internals\CashboxTable::getById((int)$filter["CASHBOX_ID"]);
}
else
{
	$cashboxData = Internals\CashboxTable::getList(
		array(
			'filter' => array('USE_OFFLINE' => 'N', '%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\CashboxBitrix')
		)
	);
}

$cashboxList = $cashboxData->fetchAll();

if (empty($filter["CASHBOX_ID"]))
{
	$filter["CASHBOX_ID"] = array_column($cashboxList, 'ID');
}

if (!empty($cashboxList))
{
	$cashboxIds = array_column($cashboxList, 'ID');

	$today = new Date();

	$checkData = Internals\CashboxCheckTable::getList(
		array(
			'select' => array('CURRENCY','CHECK_SUM', 'TYPE'),
			'filter' => array(
				'PAYMENT.PAY_SYSTEM.IS_CASH' => 'N',
				'>DATE_CREATE' => $today,
				'CASHBOX_ID' => $cashboxIds
			),
			'group' => array('TYPE'),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CHECK_SUM',
					'SUM(%s)',
					array('SUM')
				)
			)
		)
	);
	$blockData = array();
	while($data = $checkData->fetch())
	{
		if ($data['TYPE'] == 'sellreturn' || $data['TYPE'] == 'prepaymentreturn')
		{
			$blockData['CASHLESS']['RETURN_SUM'] += $data['CHECK_SUM'];
		}
		else
		{
			$blockData['CASHLESS']['SUM'] += $data['CHECK_SUM'];
		}
		if (empty($blockData['CURRENCY']))
		{
			$blockData['CURRENCY'] = $data['CURRENCY'];
		}
	}

	$checkData = Internals\CashboxCheckTable::getList(
		array(
			'select' => array('CHECK_SUM', 'CURRENCY', 'TYPE'),
			'filter' => array(
				'PAYMENT.PAY_SYSTEM.IS_CASH' => 'Y',
				'>DATE_CREATE' => $today,
				'CASHBOX_ID' => $cashboxIds
			),
			'group' => array('TYPE'),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CHECK_SUM',
					'SUM(%s)',
					array('SUM')
				)
			)
		)
	);

	$todayCash = array();
	while($data = $checkData->fetch())
	{
		if ($data['TYPE'] == 'sellreturncash' || $data['TYPE'] == 'prepaymentreturn')
		{
			$blockData['CASH']['RETURN_SUM'] += $data['CHECK_SUM'];
		}
		else
		{
			$blockData['CASH']['SUM'] += $data['CHECK_SUM'];
		}
		if (empty($blockData['CURRENCY']))
		{
			$blockData['CURRENCY'] = $data['CURRENCY'];
		}
	}

	$blockData['CUMULATIVE_SUM'] = 0;
	$zreportData = Internals\CashboxZReportTable::getList(
		array(
			'limit' => count($cashboxIds),
			'select' => array('CUMULATIVE_SUM', 'CURRENCY'),
			'filter' => array('CASHBOX_ID' => $cashboxIds),
			'order'=> array('DATE_CREATE' => 'DESC')
		)
	);
	while ($data = $zreportData->fetch())
	{
		$blockData['CUMULATIVE_SUM'] += $data['CUMULATIVE_SUM'];
		if (empty($blockData['CURRENCY']))
		{
			$blockData['CURRENCY'] = $data['CURRENCY'];
		}
	}

	if (empty($blockData['CURRENCY']))
	{
		$blockData['CURRENCY'] = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
	}

	$blockData['CUMULATIVE'] = SaleFormatCurrency(
		isset($blockData['CUMULATIVE_SUM']) ? $blockData['CUMULATIVE_SUM'] : 0,
		$blockData['CURRENCY']
	);
	$blockData['CASH']['FORMATED_RETURN_SUM'] = SaleFormatCurrency(
		isset($blockData['CASH']['RETURN_SUM']) ? $blockData['CASH']['RETURN_SUM'] : 0,
		$blockData['CURRENCY']
	);
	$blockData['CASH']['FORMATED_SUM'] = SaleFormatCurrency(
		isset($blockData['CASH']['SUM']) ? $blockData['CASH']['SUM'] : 0,
		$blockData['CURRENCY']
	);
	$blockData['CASHLESS']['FORMATED_RETURN_SUM'] = SaleFormatCurrency(
		isset($blockData['CASHLESS']['RETURN_SUM']) ? $blockData['CASHLESS']['RETURN_SUM'] : 0,
		$blockData['CURRENCY']
	);
	$blockData['CASHLESS']['FORMATED_SUM'] = SaleFormatCurrency(
		isset($blockData['CASHLESS']['SUM']) ? $blockData['CASHLESS']['SUM'] : 0,
		$blockData['CURRENCY']
	);
}

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($request->get('action_target')=='selected')
	{
		$ids = array();
		$dbRes = Internals\CashboxZReportTable::getList(
			array(
				'select' => array('ID'),
				'filter' => $filter,
				'order' => array(mb_strtoupper($by) => mb_strtoupper($order))
			)
		);

		while ($arResult = $dbRes->fetch())
			$ids[] = $arResult['ID'];
	}

	foreach ($ids as $id)
	{
		if ((int)$id <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$report = Internals\CashboxZReportTable::getRowById($id);
				if ($report)
				{
					if ($report['STATUS'] === 'E' || $report['STATUS'] == 'N')
					{
						Internals\CashboxZReportTable::delete($id);
					}
					else
					{
						$lAdmin->AddGroupError(Loc::getMessage('SALE_REPORT_DELETE_ERR_INCORRECT_STATUS'), $id);
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

$params = array(
	'filter' => $filter,
	'select' => array('*', 'CALCULATE_SUM'),
	'runtime'=> array(
		new Bitrix\Main\Entity\ExpressionField(
			'CALCULATE_SUM',
			'(%s + %s) - %s',
			array('CASH_SUM', 'CASHLESS_SUM', 'RETURNED_SUM')
		)
	)
);

if (isset($by) && (in_array($by, array_keys(Internals\CashboxZReportTable::getMap())) || $by == 'CALCULATE_SUM'))
{
	$params['order'] = array(mb_strtoupper($by) => mb_strtoupper($order));
}

$dbResultList = new CAdminUiResult(Internals\CashboxZReportTable::getList($params), $tableId);
$dbResultList->NavStart();

$headers = array(
	array("id" => "ID", "content" => Loc::getMessage("SALE_CASHBOX_ZREPORT_ID"), "sort" => "ID", "default" => true),
	array("id" => "CASHBOX_ID", "content" => Loc::getMessage("SALE_CASHBOX_ZREPORT_CASHBOX_ID"), "sort" => "CASHBOX_ID", "default" => true),
	array("id" => "DATE_CREATE", "content" => Loc::getMessage("SALE_CASHBOX_ZREPORT_DATE_CREATE"), "sort" => "DATE_CREATE", "default" => true),
	array("id" => "CASH_SUM", "content" => Loc::getMessage("SALE_CASHBOX_ZREPORT_CASH_SUM"), "sort" => "CASH_SUM", "default" => true),
	array("id" => "CASHLESS_SUM", "content" => Loc::getMessage("SALE_CASHBOX_ZREPORT_CASHLESS_SUM"), "sort" => "CASHLESS_SUM", "default" => true),
	array("id" => "RETURNED_SUM", "content" => Loc::getMessage("SALE_CASHBOX_RETURNED_SUM"), "sort" => "RETURNED_SUM", "default" => true),
	array("id" => "CALCULATE_SUM", "content" => Loc::getMessage("SALE_CASHBOX_CALCULATE_SUM"), "sort" => "CALCULATE_SUM", "default" => true),
	array("id" => "CUMULATIVE_SUM", "content" => Loc::getMessage("SALE_CASHBOX_CUMULATIVE_SUM"), "sort" => "CUMULATIVE_SUM", "default" => true),
	array("id" => "STATUS", "content" => Loc::getMessage("SALE_CASHBOX_STATUS"), "default" => true),
);

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => "/bitrix/admin/sale_cashbox_zreport.php"));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($report = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($report['ID'], $report);

	$row->AddField("ID", (int)$report['ID']);
	$row->AddField("DATE_CREATE", htmlspecialcharsbx($report['DATE_CREATE']));
	$row->AddField("CASH_SUM", SaleFormatCurrency($report['CASH_SUM'], $report['CURRENCY']));
	$row->AddField("CASHLESS_SUM", SaleFormatCurrency($report['CASHLESS_SUM'], $report['CURRENCY']));
	$row->AddField("RETURNED_SUM", SaleFormatCurrency($report['RETURNED_SUM'], $report['CURRENCY']));
	$row->AddField("CALCULATE_SUM", SaleFormatCurrency($report['CALCULATE_SUM'], $report['CURRENCY']));
	$row->AddField("CUMULATIVE_SUM", SaleFormatCurrency($report['CUMULATIVE_SUM'], $report['CURRENCY']));
	$row->AddField("CASHBOX_ID", htmlspecialcharsbx($cashboxList['NAME']));
	$row->AddField("STATUS", htmlspecialcharsbx(Loc::getMessage('SALE_CASHBOX_STATUS_'.$report['STATUS'])));

	if ($report['STATUS'] === 'E' || $report['STATUS'] == 'N')
	{
		$arActions = array(
			array(
				"ICON" => "delete",
				"TEXT" => GetMessage("SALE_REPORT_DELETE"),
				"ACTION" => "if(confirm('".Loc::getMessage('SALE_REPORT_DELETE_CONFIRM', array('#REPORT_ID#' => $report['ID']))."')) ".$lAdmin->ActionDoGroup($report["ID"], "delete")
			)
		);
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

if ($saleModulePermissions == "W" )
{
	$aContext = array();

	if (!empty($cashboxList))
	{
		$aContext[] = array(
			"TEXT" => GetMessage("SALE_CASHBOX_ADD_NEW_ZREPORT"),
			"TITLE" => GetMessage("SALE_CASHBOX_ADD_NEW_ZREPORT"),
			"ICON" => "btn_new",
			'ONCLICK' => "BX.Sale.CashboxReport.createZReport()"
		);
	}

	$lAdmin->setContextSettings(array("pagePath" => "/bitrix/admin/sale_cashbox_zreport.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}
	
	
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SALE_CASHBOX_ZREPORT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);

if (isset($blockData))
{
	?>
	<table class="adm-zreport-list-frames">
		<tr>
			<td class="adm-zreport-list-frame-edge">
				<div class="adm-zreport-list-frame-table">
					<table>
						<thead>
							<tr>
								<td class="adm-zreport-list-frames-title"><?=Loc::getMessage("SALE_ZREPORT_FRAME_TITLE_1")?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="adm-zreport-list-frame-td-small-text">
										<?=htmlspecialcharsbx($cashboxList['NAME'])?>
									</div>
									<div class="adm-zreport-list-frame-td-big-text" id="adm-zreport-cumulative">
										<?=$blockData['CUMULATIVE']?>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
			<td class="adm-zreport-list-frame-center">
				<div class="adm-zreport-list-frame-table">
					<table>
						<thead>
							<tr>
								<td class="adm-zreport-list-frames-title"><?=Loc::getMessage("SALE_ZREPORT_FRAME_TITLE_2")?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="adm-zreport-list-frame-td-small-text" >
										<?=Loc::getMessage("SALE_ZREPORT_FRAME_RETURN")?>: <span id="adm-zreport-cash-return"><?=$blockData['CASH']['FORMATED_RETURN_SUM']?></span>
									</div>
									<div class="adm-zreport-list-frame-td-big-text" id="adm-zreport-cash-now">
										<?=$blockData['CASH']['FORMATED_SUM']?>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
			<td class="adm-zreport-list-frame-edge">
				<div class="adm-zreport-list-frame-table">
					<table>
						<thead>
							<tr>
								<td class="adm-zreport-list-frames-title"><?=Loc::getMessage("SALE_ZREPORT_FRAME_TITLE_3")?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="adm-zreport-list-frame-td-small-text">
										<?=Loc::getMessage("SALE_ZREPORT_FRAME_RETURN")?>: <span id="adm-zreport-cashless-return"><?=$blockData['CASHLESS']['FORMATED_RETURN_SUM']?></span>
									</div>
									<div class="adm-zreport-list-frame-td-big-text" id="adm-zreport-cashless-now">
										<?=$blockData['CASHLESS']['FORMATED_SUM']?>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<?
}

$lAdmin->DisplayList();
?>
<select id="filter_cashbox_id" style="display: none;">
	<?
	foreach ($cashBoxList as $cashBoxId => $cashBoxName)
	{
		?>
		<option value="<?=$cashBoxId?>"
			<?=$cashboxList['ID'] == $cashBoxId ? "selected" : ""?>>
			<?= htmlspecialcharsbx($cashBoxName);?>
		</option>
		<?
	}
	?>
</select>
<script>
	BX.message(
		{
			CASHBOX_CREATE_ZREPORT_WINDOW_TITLE: '<?=Loc::getMessage("CASHBOX_CREATE_ZREPORT_WINDOW_TITLE")?>',
			CASHBOX_CREATE_ZREPORT_WINDOW_INFO: '<?=Loc::getMessage("CASHBOX_CREATE_ZREPORT_WINDOW_INFO")?>',
			SALE_F_CASHBOX: '<?=Loc::getMessage("SALE_F_CASHBOX")?>'
		}
	);
</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>