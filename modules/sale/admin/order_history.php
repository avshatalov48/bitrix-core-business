<?
/**
 * @var CDatabase $DB
 * @var CMain  $APPLICATION
 */
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Exchange\Integration\Admin\Link,
	\Bitrix\Sale\Exchange\Integration\Admin\ModeType;


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

$moduleId = "sale";
Bitrix\Main\Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

$ID = intval($_GET["ID"]);

/** @var \Bitrix\Sale\Order $saleOrder */

if (!isset($saleOrder) || !($saleOrder instanceof \Bitrix\Sale\Order))
{
	$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

	/** @var Sale\Order $orderClass */
	$orderClass = $registry->getOrderClassName();

	$saleOrder = $orderClass::load($ID);
}

$shipmentCollection = $saleOrder->getShipmentCollection();
$paymentCollection = $saleOrder->getPaymentCollection();

$sTableHistory = "table_order_history";
$oSortHistory = new CAdminSorting($sTableHistory);
$lAdminHistory = new CAdminList($sTableHistory, $oSortHistory);
$link = Link::getInstance();

//FILTER ORDER CHANGE HISTORY
$arFilterFieldsHistory = array(
	"filter_user",
	"filter_date_history",
	"filter_type",
	"filter_important"
);

$lAdminHistory->InitFilter($arFilterFieldsHistory);

$by = trim(array_key_exists('by', $_REQUEST) ? $_REQUEST['by'] : '');

if ('' == $by)
	$by = 'DATE_CREATE';

$order = trim(array_key_exists('order', $_REQUEST) ? $_REQUEST['order'] : '');

if (!isset($filter_important))
{
	$filter_important = "Y";
}

if ('' == $order)
	$order = 'DESC';

$arHistSort[$by] = $order;
$arHistSort["ID"] = $order;

$arFilterHistory = array("ORDER_ID" => $ID);

if (isset($historyEntity) && is_array($historyEntity))
{
	$arFilterHistory = array_merge($historyEntity, $arFilterHistory);
}

if (!empty($filter_type) <> '')
{
	$arFilterHistory["TYPE"] = trim($filter_type);
}

if (!empty($filter_user) && intval($filter_user) > 0)
{
	$arFilterHistory["USER_ID"] = intval($filter_user);
}

if (!empty($filters_date_history_from))
{
	$arFilterHistory["DATE_CREATE_FROM"] = trim($filters_date_history_from);
}

if (!empty($filters_date_history_to))
{
	if ($arDate = ParseDateTime($filters_date_history_to, CSite::GetDateFormat("FULL")))
	{
		if (mb_strlen($filters_date_history_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filters_date_history_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilterHistory["DATE_CREATE_TO"] = $filters_date_history_to;
	}
	else
		$filters_date_history_to = "";
}

$arHistoryData = array();
$bUseOldHistory = false;

// collect records from old history to show in the new order changes list
$dbHistory = CSaleOrder::GetHistoryList(
	array("H_DATE_INSERT" => "DESC"),
	array("H_ORDER_ID" => $ID),
	false,
	false,
	array("*")
);

while ($arHistory = $dbHistory->Fetch())
{
	$res = convertHistoryToNewFormat($arHistory);

	if ($res)
	{
		$arHistoryData[] = $res;
		$bUseOldHistory = true;
	}
}

if ($filter_important === 'Y')
{
	$arFilterHistory['@TYPE'] = \Bitrix\Sale\OrderHistory::getManagerLogItems();
}

CTimeZone::Disable();

// new order history data
$dbOrderChange = CSaleOrderChange::GetList(
	$arHistSort,
	$arFilterHistory,
	false,
	false,
	array("*")
);

while ($arChangeRecord = $dbOrderChange->Fetch())
	$arHistoryData[] = $arChangeRecord;

CTimeZone::Enable();

// advancing sorting is necessary if old history results are mixed with new order changes
if ($bUseOldHistory)
{
	$arData = array();
	foreach ($arHistoryData as $index => $arHistoryRecord)
		$arData[$index]  = $arHistoryRecord[$by];

	$arIds = array();
	foreach ($arHistoryData as $index => $arHistoryRecord)
		$arIds[$index]  = $arHistoryRecord["ID"];

	array_multisort($arData, constant("SORT_".ToUpper($order)), $arIds, constant("SORT_".ToUpper($order)), $arHistoryData);
}

$dbRes = new CDBResult;
$dbRes->InitFromArray($arHistoryData);
$dbRecords = new CAdminResult($dbRes, $sTableHistory);
$dbRecords->NavStart();
$lAdminHistory->NavText($dbRecords->GetNavPrint(Loc::getMessage('SOD_HIST_LIST')));

$histdHeader = array(
	array("id"=>"DATE_CREATE", "content"=>Loc::getMessage("SOD_HIST_H_DATE"), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"USER_ID", "content"=>Loc::getMessage("SOD_HIST_H_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"TYPE", "content"=>Loc::getMessage("SOD_HIST_TYPE"), "sort"=>"TYPE", "default"=>true),
	array("id"=>"DATA", "content"=>Loc::getMessage("SOD_HIST_DATA"), "sort"=>"", "default"=>true),
);

if (!isset($entity))
{
	$histdHeader[] = array("id"=>"ENTITY_ID", "content"=>Loc::getMessage("SOD_HIST_ENTITY_ID"), "sort"=>"", "default"=>true);
}

$lAdminHistory->AddHeaders($histdHeader);
$arOperations = array();

while ($arChangeRecord = $dbRecords->Fetch())
{
	$entityName = '';
	$row =& $lAdminHistory->AddRow($arChangeRecord["ID"], $arChangeRecord, '', '');
	if ($arChangeRecord["DATE_CREATE"] instanceof \Bitrix\Main\Type\Date)
	{
		$datetime = $arChangeRecord["DATE_CREATE"];
	}
	else
	{
		$datetime = new \Bitrix\Main\Type\DateTime($arChangeRecord["DATE_CREATE"]);
	}

	$datetime->format(\Bitrix\Main\Type\DateTime::getFormat());
	$row->AddField("DATE_CREATE", $datetime->toString());

	$fieldValue = GetFormatedUserName($arChangeRecord["USER_ID"], false);
	if($link->getType() == ModeType::APP_LAYOUT_TYPE)
	{
		$fieldValue = strip_tags($fieldValue);
	}
	$row->AddField("USER_ID", $fieldValue);


	$arRecord = CSaleOrderChange::GetRecordDescription($arChangeRecord["TYPE"], $arChangeRecord["DATA"]);
	$row->AddField("TYPE", $arRecord["NAME"]);

	$arRecord["INFO"] = str_replace('&nbsp;', ' ', $arRecord["INFO"]);

	$row->AddField("DATA", htmlspecialcharsbx($arRecord["INFO"]));
	if (!isset($entity) && intval($arChangeRecord["ENTITY_ID"]) > 0)
	{
		if ($arChangeRecord["ENTITY"] == 'SHIPMENT')
		{
			$shipmentEntity = $shipmentCollection->getItemById($arChangeRecord["ENTITY_ID"]);
			if ($shipmentEntity)
				$entityName = $shipmentEntity->getField('DELIVERY_NAME');
		}
		else if ($arChangeRecord["ENTITY"] == 'PAYMENT')
		{
			$payment = $paymentCollection->getItemById($arChangeRecord["ENTITY_ID"]);
			if ($payment)
				$entityName = $payment->getField('PAY_SYSTEM_NAME');
		}
	}
	$row->AddField("ENTITY_ID", htmlspecialcharsbx($entityName));
	$arOperations[$arChangeRecord["TYPE"]] = $arRecord["NAME"];
}

if(
	isset($_REQUEST["table_id"])
	&& $_REQUEST["table_id"] == $sTableHistory
)
{
	$lAdminHistory->CheckListMode();
}

?>

<div id="order-history-sourse" style="/*display:none;*/">
	<form name="find_form_history" method="GET" action="<?=$APPLICATION->GetCurPageParam();?>">
	<input type="hidden" name="ID" value="<?=$ID?>">
	<input type="hidden" name="table_id" value="<?=$sTableHistory?>">
	<?
	$arFilterFieldsTmp = array(
		"filter_user" => Loc::getMessage("SOD_HIST_H_USER"),
		"filter_date_history" => Loc::getMessage("SOD_HIST_H_DATE"),
		"filter_type" => Loc::getMessage("SOD_HIST_TYPE"),
		"filter_important" => Loc::getMessage("SOD_HIST_IMPORTANT_TYPES"),
	);

	$oFilter = new CAdminFilter(
		$sTableHistory."_filters",
		$arFilterFieldsTmp
	);

	$oFilter->SetDefaultRows(array("filter_user", 'filter_important'));
	$oFilter->Begin();
	?>
<tr>
	<td><?=Loc::getMessage('SOD_HIST_H_USER')?>:</td>
	<td>
		<?=FindUserID("filter_user", $filter_user, "", "find_form_history");?>
	</td>
</tr>
<tr>
	<td><?=Loc::getMessage('SOD_HIST_H_DATE')?>:</td>
	<td>
		<?echo CalendarPeriod("filters_date_history_from", $filters_date_history_from ?? '', "filters_date_history_to", $filters_date_history_to  ?? '', "find_form_history", "Y")?>
	</td>
</tr>

<tr>
	<td><?=Loc::getMessage('SOD_HIST_TYPE')?>:</td>
	<td>
		<select name="filter_type">
			<option value=""><?echo Loc::getMessage("SOD_HIST_ALL")?></option>
			<?foreach ($arOperations as $type => $name):?>
				<option value="<?=$type?>"<?if ($filter_type== $type) echo " selected"?>><?=htmlspecialcharsbx($name);?></option>
			<?endforeach;?>
		</select>
	</td>
</tr>
<tr>
	<td><?=Loc::getMessage('SOD_HIST_IMPORTANT_TYPES')?>:</td>
	<td>
		<select name="filter_important">
			<option value="Y"<?if ($filter_important === 'Y' || $filter_important === null) echo " selected"?>><?=Loc::getMessage("SOD_HIST_YES");?></option>
			<option value="N"<?if ($filter_important === 'N') echo " selected"?>><?=Loc::getMessage("SOD_HIST_NO");?></option>
		</select>
	</td>
</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableHistory,
		"url" => $APPLICATION->GetCurPageParam(), //"/bitrix/admin/sale_order_history.php?lang=".LANGUAGE_ID,
		"form" => "find_form_history"
	)
);
$oFilter->End();
?>
</form>
<?
	$lAdminHistory->DisplayList(array("FIX_HEADER" => false, "FIX_FOOTER" => false));
?>
</div>