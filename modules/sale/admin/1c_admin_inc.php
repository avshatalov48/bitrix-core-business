<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\Loader::includeModule('sale');
IncludeModuleLangFile(__FILE__);

$module_id = "sale";
$CAT_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($CAT_RIGHT >= "R") :

$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
$arSites = array("" => GetMessage("SALE_1C_ALL_SITES"));
while ($arSite = $rsSite->GetNext())
{
	$arSites[$arSite["LID"]] = $arSite["NAME"];
}

$arStatuses = Array("" => GetMessage("SALE_1C_NO"));
$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
while ($arStatus = $dbStatus->Fetch())
{
	$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$arPaySystems = array("" => GetMessage("SALE_1C_NO"));
$dbPaySystems = CSalePaySystem::GetList(array("SORT"=>"ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
$arPaySystemsWithoutInner = Array("" => GetMessage("SALE_1C_NO"));
while ($arPaySystem = $dbPaySystems->Fetch())
{
	$arPaySystems[$arPaySystem["ID"]] = "[".$arPaySystem["ID"]."] ".$arPaySystem["NAME"];

	if($arPaySystem["ID"] != Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId())
	{
		$arPaySystemsWithoutInner[$arPaySystem["ID"]] = "[".$arPaySystem["ID"]."] ".$arPaySystem["NAME"];
	}
}

$shipmentServices = array("" => GetMessage("SALE_1C_NO"));
$deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
foreach($deliveryList as $shipmentService)
{
	$shipmentServices[$shipmentService["ID"]] = "[".$shipmentService["ID"]."] ".$shipmentService["NAME"];
}

if(!\Bitrix\Sale\Exchange\Internals\LoggerDiag::isOn())
{
	\Bitrix\Sale\Exchange\Internals\LoggerDiag::disable();
	\Bitrix\Main\Config\Option::delete("sale", array("name" => 'EXCHANGE_DEBUG_DURATION'));
}

$arAllOptions = array(
	array("1C_SALE_SITE_LIST", GetMessage("SALE_1C_SITE_LIST"), "", Array("list", $arSites), array(),""),
	array("1C_IMPORT_NEW_ORDERS", GetMessage("SALE_1C_IMPORT_NEW_ORDERS"), "N", Array("checkbox"), array(),""),
	array("1C_SITE_NEW_ORDERS", GetMessage("SALE_1C_SITE_NEW_ORDERS"), "s1", Array("list", $arSites), array(),""),
	array("1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", GetMessage("SALE_1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX"), "", Array("text"), array("note"=>GetMessage("SALE_1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX_NOTE")),""),
	array("1C_EXPORT_PAYED_ORDERS", GetMessage("SALE_1C_EXPORT_PAYED_ORDERS"), "", Array("checkbox"), array(),""),
	array("1C_EXPORT_ALLOW_DELIVERY_ORDERS", GetMessage("SALE_1C_EXPORT_ALLOW_DELIVERY_ORDERS"), "", Array("checkbox"), array(),""),
	array("1C_CHANGE_STATUS_FROM_1C", GetMessage("SALE_1C_CHANGE_STATUS_FROM_1C"), "", Array("checkbox"), array(),""),
	array("1C_EXPORT_FINAL_ORDERS", GetMessage("SALE_1C_EXPORT_FINAL_ORDERS"), "", Array("list", $arStatuses), array(),""),
	array("1C_FINAL_STATUS_ON_DELIVERY", GetMessage("SALE_1C_FINAL_STATUS_ON_DELIVERY"), "F", Array("list", $arStatuses), array(),""),
	array("1C_REPLACE_CURRENCY", GetMessage("SALE_1C_REPLACE_CURRENCY"), GetMessage("SALE_1C_RUB"), Array("text"), array(),""),
	array("1C_IMPORT_DEFAULT_PS", GetMessage("SALE_1C_IMPORT_DEFAULT_PS_C"), "", Array("list", $arPaySystems), array(),""),
	array("1C_IMPORT_DEFAULT_PS_B", GetMessage("SALE_1C_IMPORT_DEFAULT_PS_B"), "", Array("list", $arPaySystems), array(),""),
	array("1C_IMPORT_DEFAULT_PS_A", GetMessage("SALE_1C_IMPORT_DEFAULT_PS_A"), "", Array("list", $arPaySystems), array(),""),
	array("1C_IMPORT_DEFAULT_PS_ORDER_PAID", GetMessage("SALE_1C_IMPORT_DEFAULT_PS_ORDER_PAID"), "", Array("list", $arPaySystemsWithoutInner), array(),""),
	array("1C_IMPORT_DEFAULT_SHIPMENT_SERVICE", GetMessage("SALE_1C_IMPORT_DEFAULT_SHIPMENT_SERVICE"), "", Array("list", $shipmentServices), array(),""),
	array("1C_IMPORT_UPDATE_BASKET_QUANTITY", GetMessage("SALE_1C_IMPORT_UPDATE_BASKET_QUANTITY"), "", Array("checkbox"), array(),""),
	array("1C_IMPORT_NEW_PAYMENT", GetMessage("SALE_1C_IMPORT_NEW_PAYMENT"), "", Array("checkbox"), array(),""),
	array("1C_IMPORT_NEW_SHIPMENT", GetMessage("SALE_1C_IMPORT_NEW_SHIPMENT"), "", Array("checkbox"), array(),""),
	array("1C_IMPORT_NEW_ORDER_NEW_SHIPMENT", GetMessage("SALE_1C_IMPORT_NEW_ORDER_NEW_SHIPMENT"), "", Array("checkbox"), array(),""),
	array("1C_SALE_GROUP_PERMISSIONS", GetMessage("SALE_1C_GROUP_PERMISSIONS"), "1", Array("mlist", 5, $arUGroupsEx), array(),""),
	array("1C_SALE_USE_ZIP", GetMessage("SALE_1C_USE_ZIP"), "Y", Array("checkbox"), array(),""),
	array("1C_INTERVAL", GetMessage("SALE_1C_INTERVAL"), 30, Array("text", 20), array(),""),
	array("1C_FILE_SIZE_LIMIT", GetMessage("SALE_1C_FILE_SIZE_LIMIT"), 200*1024, Array("text", 20), array(),""),
	array("SALE_EXCHANGE_DEBUG_INTERVAL_DAY", GetMessage("SALE_EXCHANGE_DEBUG_INTERVAL_DAY"), 1, Array("text", 20), array(), function() use(&$val){
	    echo $val = max($val, 1);
    }),
    array("EXCHANGE_DEBUG_DURATION", GetMessage("SALE_EXCHANGE_DEBUG_END_TIME"), 0, Array("text", 20), array(), function($val){
	    \Bitrix\Sale\Exchange\Internals\LoggerDiag::enable(time()+intval($val));
	}),

);

if($REQUEST_METHOD=="POST" && $Update <> '' && $CAT_RIGHT>="W" && check_bitrix_sessid())
{
	$allOptionCount = count($arAllOptions);
	for ($i=0; $i<$allOptionCount; $i++)
	{
		$name = $arAllOptions[$i][0];
		$val = $_REQUEST[$name];
		$callback = $arAllOptions[$i][5];
		if($arAllOptions[$i][3][0]=="checkbox" && $val!="Y")
			$val = "N";
		if($arAllOptions[$i][3][0]=="mlist" && is_array($val))
			$val = implode(",", $val);
		if(is_callable($callback))
			call_user_func_array($callback, array($val));

		COption::SetOptionString("sale", $name, $val, $arAllOptions[$i][1]);
	}
	return;
}

foreach($arAllOptions as $Option):
	$val = COption::GetOptionString("sale", $Option[0], $Option[2]);
	$type = $Option[3];
	$params = $Option[4];
	?>
	<tr>
		<td width="40%"<?if($type[0]=="mlist") echo " valign=\"top\""?>><?	if($type[0]=="checkbox")
						echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
					else
						echo $Option[1];?>:</td>
		<td width="60%">
				<?if($type[0]=="checkbox"):?>
					<input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
				<?elseif($type[0]=="text"):?>
					<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>">
				<?elseif($type[0]=="textarea"):?>
					<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
				<?elseif($type[0]=="list"):?>
					<select name="<?echo htmlspecialcharsbx($Option[0])?>">
					<?foreach($type[1] as $key=>$value):?>
						<option value="<?echo htmlspecialcharsbx($key)?>" <?if($val==$key) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
					<?endforeach?>
					</select>
				<?elseif($type[0]=="mlist"):
					$val = explode(",", $val)?>
					<select multiple name="<?echo htmlspecialcharsbx($Option[0])?>[]" size="<?echo $type[1]?>">
					<?foreach($type[2] as $key=>$value):?>
						<option value="<?echo htmlspecialcharsbx($key)?>" <?if(in_array($key, $val)) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
					<?endforeach?>
					</select>
				<?endif?>
		</td>
	</tr>
	<?
	if(isset($params['note']))
	{
		?>
		<tr>
			<td colspan="2" align="center">
				<?echo BeginNote('align="center"');?>
				<?=$params["note"]?>
				<?echo EndNote();?>
			</td>
		</tr>
		<?
	}
	?>
<?endforeach;
endif;
?>