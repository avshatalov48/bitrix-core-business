<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Cashbox\Internals;
use Bitrix\Main\Page;
use Bitrix\Main\Type\Date;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
CUtil::InitJSCore();
Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/cashbox_zreport.js");
\Bitrix\Main\Loader::includeModule('sale');

$tableId = "tbl_sale_cashbox_zreport";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
	'filter_cashbox_id'
);

$lAdmin->InitFilter($arFilterFields);
$filter = array();

if (strlen($filter_cashbox_id) > 0 && $filter_cashbox_id != "NOT_REF")
{
	$filter["CASHBOX_ID"] = trim($filter_cashbox_id);
}

$cashboxList = array();

if ((int)($filter["CASHBOX_ID"]) > 0)
{
	$cashboxData = Internals\CashboxTable::getById((int)$filter["CASHBOX_ID"]);
}
else
{
	$cashboxData = Internals\CashboxTable::getList(
		array(
			'limit' => 1,
			'filter' => array('USE_OFFLINE' => 'N', '%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\CashboxBitrix')
		)
	);
}

$cashboxList = $cashboxData->fetch();

if (empty($filter["CASHBOX_ID"]))
{
	$filter["CASHBOX_ID"] = (int)$cashboxList['ID'];
}

if (!empty($cashboxList))
{
	$cashboxId = $cashboxList['ID'];

	$today = new Date();

	$checkData = Internals\CashboxCheckTable::getList(
		array(
			'select' => array('CURRENCY','CHECK_SUM', 'TYPE'),
			'filter' => array(
				'PAYMENT.PAY_SYSTEM.IS_CASH' => 'N',
				'>DATE_CREATE' => $today,
				'CASHBOX_ID' => $cashboxId
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
				'CASHBOX_ID' => $cashboxId
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

	$zreportData = Internals\CashboxZReportTable::getList(
		array(
			'limit' => 1,
			'select' => array('CUMULATIVE_SUM', 'CURRENCY'),
			'filter' => array('CASHBOX_ID' => $cashboxId),
			'order'=> array('DATE_CREATE' => 'DESC')
		)
	);
	$data = $zreportData->fetch();

	if ($data)
	{
		$blockData['CUMULATIVE_SUM'] = $data['CUMULATIVE_SUM'];
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

if (strlen($filter_date_create_from) > 0)
{
	$filter[">=DATE_CREATE"] = trim($filter_date_create_from);
}
elseif($set_filter!="Y" && $del_filter != "Y")
{
	$filter_date_create_from_FILTER_PERIOD = 'day';
	$filter_date_create_from_FILTER_DIRECTION = 'current';
	$filter[">=DATE_CREATE"] = new \Bitrix\Main\Type\Date();
}

if (strlen($filter_date_create_to)>0)
{
	if($arDate = ParseDateTime($filter_date_create_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(strlen($filter_date_create_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_create_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$filter["<=DATE_CREATE"] = $filter_date_create_to;
	}
	else
	{
		$filter_date_create_to = "";
	}
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
				'order' => array(ToUpper($by) => ToUpper($order))
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
}

$navyParams = array();

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
	$params['order'] = array(ToUpper($by) => ToUpper($order));
}

$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));

if ($navyParams['SHOW_ALL'])
{
	$usePageNavigation = false;
}
else
{
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}



if ($usePageNavigation)
{
	$params['limit'] = $navyParams['SIZEN'];
	$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$totalPages = 0;

if ($usePageNavigation)
{
	$countQuery = new \Bitrix\Main\Entity\Query(Internals\CashboxZReportTable::getEntity());
	$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($params['filter']);

	foreach ($params['runtime'] as $key => $field)
		$countQuery->registerRuntimeField($key, clone $field);

	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];

	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;

		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = 0;
	}
}

$dbResultList = new CAdminResult(Internals\CashboxZReportTable::getList($params), $tableId);

if ($usePageNavigation)
{
	$dbResultList->NavStart($params['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$dbResultList->NavRecordCount = $totalCount;
	$dbResultList->NavPageCount = $totalPages;
	$dbResultList->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$dbResultList->NavStart();
}

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

$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("group_admin_nav")));

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
			"LINK" => "#",
			"ICON" => "btn_new",
			'ONCLICK' => "BX.Sale.CashboxReport.createZReport()"
		);
	}

	$lAdmin->AddAdminContextMenu($aContext);
}
	
	
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SALE_CASHBOX_ZREPORT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<?
		$oFilter = new CAdminFilter(
			$tableId."_filter",
			array(
				Loc::getMessage("SALE_Z_REPORT_CREATE")
			)
		);

		$oFilter->Begin();
	?>
	<tr id="filter_cashbox_id_row">
		<td><?echo Loc::getMessage("SALE_F_CASHBOX")?>:</td>
		<td>
			<select name="filter_cashbox_id" id="filter_cashbox_id" <?=empty($cashboxList) ?"disabled": ""?>>
				<?
				$dbRes = Internals\CashboxTable::getList(
					array(
						'filter' => array('USE_OFFLINE' => 'N', '%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\CashboxBitrix')
					)
				);
				while ($item = $dbRes->fetch())
				{
					?>
					<option value="<?=$item['ID']?>"
						<?=$cashboxList['ID'] == $item['ID'] ? "selected" : ""?>>
						<?= htmlspecialcharsbx($item['NAME']);?>
					</option>
					<?
				}
				if (empty($cashboxList))
				{
					?>
					<option><?=Loc::getMessage("SALE_CASHBOX_NOT_CONNECTED")?></option>
					<?
				}
				?>				
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_Z_REPORT_CREATE");?>:</td>
		<td>
			<?=CalendarPeriod("filter_date_create_from", htmlspecialcharsbx($filter_date_create_from), "filter_date_create_to", htmlspecialcharsbx($filter_date_create_to), "find_form", "Y")?>
		</td>
	</tr>
	<?
		$oFilter->Buttons(
			array(
				"table_id" => $tableId,
				"url" => $APPLICATION->GetCurPage(),
				"form" => "find_form"
			)
		);
		$oFilter->End();
	?>
</form>

<?
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
<script language="JavaScript">
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