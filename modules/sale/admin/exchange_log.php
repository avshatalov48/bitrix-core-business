<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\Logger;

\Bitrix\Main\Loader::includeModule('sale');
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

function getAdminUrlByType($typeId, $entityId, $ownerEntityId = null)
{
	$r = '';
	if(\Bitrix\Sale\Exchange\EntityType::isDefined($typeId))
	{
		$urlAdmin = array(
			\Bitrix\Sale\Exchange\EntityType::ORDER=>"/bitrix/admin/sale_order_view.php?ID=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::SHIPMENT=>"/bitrix/admin/sale_order_shipment_edit.php?order_id=#OWNER_ENTITY_ID#&shipment_id=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::PAYMENT_CASH=>"/bitrix/admin/sale_order_payment_edit.php?order_id=#OWNER_ENTITY_ID#&payment_id=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::PAYMENT_CASH_LESS=>"/bitrix/admin/sale_order_payment_edit.php?order_id=#OWNER_ENTITY_ID#&payment_id=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::PAYMENT_CARD_TRANSACTION=>"/bitrix/admin/sale_order_payment_edit.php?order_id=#OWNER_ENTITY_ID#&payment_id=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::PAYMENT_CARD_TRANSACTION=>"/bitrix/admin/sale_order_payment_edit.php?order_id=#OWNER_ENTITY_ID#&payment_id=#ENTITY_ID#",
			\Bitrix\Sale\Exchange\EntityType::USER_PROFILE=>"/bitrix/admin/user_edit.php?ID=#ENTITY_ID#",
		);

		$r = str_replace(array("#ENTITY_ID#", "#OWNER_ENTITY_ID#"),array($entityId, $ownerEntityId), $urlAdmin[$typeId]);
	}
	return $r;
}

$tableId = "tbl_sale_exchange_log";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
    'filter_entity_type_id',
    'filter_entity_id_from',
    'filter_entity_id_to',
    'filter_parent_id_from',
    'filter_parent_id_to',
	'filter_order_id_from',
    'filter_order_id_to',
    'filter_direction_id',
    'filter_xml_id',
    'filter_date_insert_from',
    'filter_date_insert_to',
);

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if(isset($filter_entity_type_id) && is_array($filter_entity_type_id) && count($filter_entity_type_id) > 0)
{
	$countFilter = count($filter_entity_type_id);
	for ($i = 0; $i < $countFilter; $i++)
	{
		$filter_entity_type_id[$i] = trim($filter_entity_type_id[$i]);
		if($filter_entity_type_id[$i] <> '')
			$filter["=ENTITY_TYPE_ID"][] = $filter_entity_type_id[$i];
	}
}


if ($filter_date_insert_from <> '')
{
	$filter[">=DATE_INSERT"] = trim($filter_date_insert_from);
}
elseif($set_filter!="Y" && $del_filter != "Y")
{
	$filter_date_insert_from_FILTER_PERIOD = 'day';
	$filter_date_insert_from_FILTER_DIRECTION = 'current';
	$filter[">=DATE_INSERT"] = new \Bitrix\Main\Type\Date();
}

if ($filter_date_insert_to <> '')
{
	if($arDate = ParseDateTime($filter_date_insert_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(mb_strlen($filter_date_insert_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_insert_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$filter["<=DATE_INSERT"] = $filter_date_insert_to;
	}
	else
	{
		$filter_date_insert_to = "";
	}
}

if((int)($filter_entity_id_from)>0) $filter[">=ENTITY_ID"] = (int)($filter_entity_id_from);
if((int)($filter_entity_id_to)>0) $filter["<=ENTITY_ID"] = (int)($filter_entity_id_to);
if((int)($filter_parent_id_from)>0) $filter[">=PARENT_ID"] = (int)($filter_parent_id_from);
if((int)($filter_parent_id_to)>0) $filter["<=PARENT_ID"] = (int)($filter_parent_id_to);

if ($filter_xml_id <> '') $filter["=XML_ID"] = trim($filter_xml_id);
if ($filter_direction_id <> '')
    $filter["=DIRECTION"] = trim($filter_direction_id);

if ($del_filter !== 'Y')
{
	$params = array(
		'filter' => $filter
	);
}

if (isset($by))
{
	$order = isset($order) ? $order : "ASC";
	$params['order'] = array($by => $order);
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

$totalPages = 0;

$params['select']=array('*');

$headers = array(
	array("id"=>"ID", "content"=>Loc::getMessage("LOG_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"ENTITY_ID", "content"=>Loc::getMessage("LOG_ENTITY_ID"), "sort"=>"ENTITY_ID", "default"=>true),
	array("id"=>"ENTITY_TYPE_ID", "content"=>Loc::getMessage("LOG_ENTITY_TYPE_ID"), "sort"=>"ENTITY_TYPE_ID", "default"=>true),
	array("id"=>"PARENT_ID", "content"=>Loc::getMessage("LOG_PARENT_ID"), "sort"=>"PARENT_ID", "default"=>true),
	array("id"=>"OWNER_ENTITY_ID", "content"=>Loc::getMessage("LOG_OWNER_ENTITY_ID"), "sort"=>"OWNER_ENTITY_ID", "default"=>true),
	array("id"=>"ENTITY_DATE_UPDATE", "content"=>Loc::getMessage("LOG_ENTITY_DATE_UPDATE"), "sort"=>"ENTITY_DATE_UPDATE", "default"=>true),
	array("id"=>"XML_ID", "content"=>Loc::getMessage("LOG_XML_ID"), "sort"=>"XML_ID", "default"=>true),
	array("id"=>"MARKED", "content"=>Loc::getMessage("LOG_MARKED"), "sort"=>"MARKED", "default"=>true),
	array("id"=>"MESSAGE", "content"=>Loc::getMessage("LOG_MESSAGE"), "sort"=>"MESSAGE", "default"=>true),
	array("id"=>"DATE_INSERT", "content"=>Loc::getMessage("LOG_DATE_INSERT"), "sort"=>"DATE_INSERT", "default"=>true),
);

$dbResultList = new CAdminResult((new Logger\Exchange(Logger\ProviderType::ONEC_NAME))->getList($params), $tableId);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("group_admin_nav")));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($report = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($report['ID'], $report);

	$message = '';
	if($report['MESSAGE']<>'')
	{
		$message .= '<div>';
	    $message .= '<div class="set-link-block">';
		$message .= '<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_'.$report["ID"].'" onclick="fToggleMessage('.$report["ID"].')">'.Loc::getMessage("REPORT_SHOW_SET")."</a>";
		$message .= '</div>';
		$message .= '<div style="display:none" class="item_'.$report["ID"].'">';
    	$message .= '<hr size="1" width="90%">';
		$message .= htmlspecialcharsbx($report['MESSAGE']);
		$message .= '</div>';
    }

	$row->AddField("ID", (int)$report['ID']);
	$row->AddField("ENTITY_ID", "<a href=\"".getAdminUrlByType($report['ENTITY_TYPE_ID'], $report['ENTITY_ID'], $report['OWNER_ENTITY_ID'])."&lang=".LANG."\" title=\"".GetMessage("SALE_EDIT_DESCR")."\">".$report['ENTITY_ID']."</a>");
	$row->AddField("ENTITY_TYPE_ID", htmlspecialcharsbx(EntityType::getDescription($report['ENTITY_TYPE_ID'])));
	$row->AddField("PARENT_ID", $report['PARENT_ID']>0? "<a href=\"".getAdminUrlByType(EntityType::ORDER, $report['PARENT_ID'])."&lang=".LANG."\" title=\"".GetMessage("SALE_EDIT_DESCR")."\">".$report['PARENT_ID']."</a>":'');
	$row->AddField("OWNER_ENTITY_ID", $report['OWNER_ENTITY_ID']>0? "<a href=\"".getAdminUrlByType(EntityType::ORDER, $report['OWNER_ENTITY_ID'])."&lang=".LANG."\" title=\"".GetMessage("SALE_EDIT_DESCR")."\">".$report['OWNER_ENTITY_ID']."</a>":'');
	$row->AddField("ENTITY_DATE_UPDATE", htmlspecialcharsbx($report['ENTITY_DATE_UPDATE']));
	$row->AddField("XML_ID", htmlspecialcharsbx($report['XML_ID']));
	$row->AddField("MARKED", $report['MARKED']=='Y'? Loc::getMessage("LOG_MARKED_Y"):'');
	$row->AddField("MESSAGE", $message);
	$row->AddField("DATE_INSERT", htmlspecialcharsbx($report['DATE_INSERT']));
}

$lAdmin->CheckListMode();
/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(Loc::getMessage("LOG_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
    <script type="text/javascript">
        function fToggleMessage(setParentId)
        {
            var elements = document.getElementsByClassName('item_' + setParentId);
            var hide = false;

            for (var i = 0; i < elements.length; ++i)
            {
                if(elements[i].style.display == 'none' || elements[i].style.display == '')
                {
                    elements[i].style.display = 'table-row';
                    hide = true;
                }
                else
                    elements[i].style.display = 'none';
            }

            if(hide)
                BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("REPORT_HIDE_SET")?>';
            else
                BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("REPORT_SHOW_SET")?>';
        }
    </script>

    <form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
		<?
		$oFilter = new CAdminFilter(
			$tableId."_filter",
			array(
                    'filter_entity_type_id'=>Loc::getMessage('LOG_REPORT_TYPES'),
				    'filter_entity_id'=>Loc::getMessage("LOG_REPORT_ENTITY_ID"),
				    'filter_parent_id'=>Loc::getMessage("LOG_REPORT_PARENT_ENTITY_ID"),
				    'filter_xml_id'=>Loc::getMessage("LOG_REPORT_XML_ID"),
				    'filter_date_insert'=>Loc::getMessage("LOG_REPORT_DATE_INSERT")
            )
		);

		$oFilter->Begin();
		?>
        <tr id="filter_exchange_log_id_row">
            <td><?echo Loc::getMessage("LOG_REPORT_DIRECTION_ID")?>:</td>
            <td>
                <select name="filter_direction_id" id="filter_direction_id" >
                    <option <?($filter_direction_id==\Bitrix\Sale\Exchange\ManagerExport::getDirectionType()?'selected':'')?> value="<?=\Bitrix\Sale\Exchange\ManagerExport::getDirectionType()?>"><?echo Loc::getMessage("LOG_REPORT_DIRECTION_EXPORT")?></option>
                    <option <?($filter_direction_id==\Bitrix\Sale\Exchange\ManagerImport::getDirectionType()?'selected':'')?> value="<?=\Bitrix\Sale\Exchange\ManagerImport::getDirectionType()?>"><?echo Loc::getMessage("LOG_REPORT_DIRECTION_IMPORT")?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td><?echo Loc::getMessage("LOG_REPORT_TYPES")?>:</td>
            <td>
                <select name="filter_entity_type_id[]" id="filter_entity_type_id" multiple size="5">
                    <?
					$types = EntityType::getAllDescriptions();
					foreach ($types as $typeId=>$name)
					{
						if($typeId == EntityType::PROFILE)
							continue;
						?>
                        <option value="<?=$typeId?>"
							<?=is_array($filter_entity_type_id) && in_array($typeId, $filter_entity_type_id) ? "selected" : ""?>>
							<?= htmlspecialcharsbx($name);?>
                        </option>
						<?
					}
					?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?echo Loc::getMessage("LOG_REPORT_ENTITY_ID");?>:</td>
            <td>
                <script type="text/javascript">
                    function filter_entity_id_from_change()
                    {
                        if(document.find_form.filter_entity_id_to.value.length<=0)
                        {
                            document.find_form.filter_entity_id_to.value = document.find_form.filter_entity_id_from.value;
                        }
                    }
                </script>
				<?echo Loc::getMessage("LOG_REPORT_FROM");?>
                <input type="text" name="filter_entity_id_from" onchange="filter_entity_id_from_change()" value="<?echo ((int)($filter_entity_id_from)>0)?(int)($filter_entity_id_from):""?>" size="10">
				<?echo Loc::getMessage("LOG_REPORT_TO");?>
                <input type="text" name="filter_entity_id_to" value="<?echo ((int)($filter_entity_id_to)>0)?(int)($filter_entity_id_to):""?>" size="10">
            </td>
        </tr>
        <tr>
            <td><?echo Loc::getMessage("LOG_REPORT_PARENT_ENTITY_ID");?>:</td>
            <td>
                <script type="text/javascript">
                    function filter_parent_id_from_change()
                    {
                        if(document.find_form.filter_parent_id_to.value.length<=0)
                        {
                            document.find_form.filter_parent_id_to.value = document.find_form.filter_parent_id_from.value;
                        }
                    }
                </script>
				<?echo Loc::getMessage("LOG_REPORT_FROM");?>
                <input type="text" name="filter_parent_id_from" onchange="filter_parent_id_from_change()" value="<?echo ((int)($filter_parent_id_from)>0)?(int)($filter_parent_id_from):""?>" size="10">
				<?echo Loc::getMessage("LOG_REPORT_TO");?>
                <input type="text" name="filter_parent_id_to" value="<?echo ((int)($filter_parent_id_to)>0)?(int)($filter_parent_id_to):""?>" size="10">
            </td>
        </tr>
        <tr>
            <td><?echo Loc::getMessage("LOG_REPORT_XML_ID");?>:</td>
            <td><input name="filter_xml_id" value="<?= htmlspecialcharsbx($filter_xml_id) ?>" size="40" type="text"></td>
        </tr>
        <tr>
            <td><?echo Loc::getMessage("LOG_REPORT_DATE_INSERT");?>:</td>
            <td>
				<?=CalendarPeriod("filter_date_insert_from", htmlspecialcharsbx($filter_date_insert_from), "filter_date_insert_to", htmlspecialcharsbx($filter_date_insert_to), "find_form", "Y")?>
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
$lAdmin->DisplayList();
?>
<br>
<?echo BeginNote();?>
	<?echo Loc::getMessage("LOG_NOTES1")?><br>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>