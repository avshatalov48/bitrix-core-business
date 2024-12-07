<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

$module_id = "sale";
$salePermissions = $APPLICATION->GetGroupRight($module_id);
if ($salePermissions >= "R") :

Loader::includeModule('sale');
IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	array("1C_SALE_SITE_LIST", GetMessage("SALE_1C_SITE_LIST"), "", Array("list", $arSites)),
	array("1C_EXPORT_PAYED_ORDERS", GetMessage("SALE_1C_EXPORT_PAYED_ORDERS"), "", Array("checkbox")),
	array("1C_EXPORT_ALLOW_DELIVERY_ORDERS", GetMessage("SALE_1C_EXPORT_ALLOW_DELIVERY_ORDERS"), "", Array("checkbox")),
	array("1C_EXPORT_FINAL_ORDERS", GetMessage("SALE_1C_EXPORT_FINAL_ORDERS"), "", Array("list", $arStatuses)),
	array("1C_FINAL_STATUS_ON_DELIVERY", GetMessage("SALE_1C_FINAL_STATUS_ON_DELIVERY"), "F", Array("list", $arStatuses)),
	array("1C_REPLACE_CURRENCY", GetMessage("SALE_1C_REPLACE_CURRENCY"), GetMessage("SALE_1C_RUB"), Array("text")),
	array("1C_SALE_GROUP_PERMISSIONS", GetMessage("SALE_1C_GROUP_PERMISSIONS"), "-", Array("mlist", 5, $arUGroupsEx)),
	array("1C_SALE_USE_ZIP", GetMessage("SALE_1C_USE_ZIP"), "Y", Array("checkbox")),
);

$arPersonType = array();

if($_SERVER['REQUEST_METHOD']=="POST" && $Update <> '' && $salePermissions>="W" && check_bitrix_sessid())
{

	$dbExport = CSaleExport::GetList();
	while($arExport = $dbExport->Fetch())
	{
		$arExportProfile[$arExport["PERSON_TYPE_ID"]] = $arExport["ID"];
	}

	$dbPersonType = CSalePersonType::GetList(
			array("SORT" => "ASC"),
			array("ACTIVE" => "Y")
		);
	while ($arPersonType = $dbPersonType->GetNext())
	{
		$arParams = array();

		if (${"export_fields_".$arPersonType["ID"]} <> '')
		{
			$arActFields = explode(",", ${"export_fields_".$arPersonType["ID"]});
			$actFieldsCount = count($arActFields);
			for ($i = 0; $i < $actFieldsCount; $i++)
			{
				$arActFields[$i] = Trim($arActFields[$i]);

				$typeTmp = ${"TYPE_".$arActFields[$i]."_".$arPersonType["ID"]};
				$valueTmp = ${"VALUE1_".$arActFields[$i]."_".$arPersonType["ID"]};
				if ($typeTmp == '')
					$valueTmp = ${"VALUE2_".$arActFields[$i]."_".$arPersonType["ID"]};

				$arParams[$arActFields[$i]] = array(
						"TYPE" => $typeTmp,
						"VALUE" => $valueTmp
					);
			}
			$arParams["IS_FIZ"] = ((${"person_type_1c_".$arPersonType["ID"]}=="FIZ")?"Y":"N");

			$i = 0;
			foreach($_POST as $k => $v)
			{
				if(mb_strpos($k, "REKV_".$arPersonType["ID"]."_") !== false && $v <> '')
				{
					$ind = mb_substr($k, mb_strrpos($k, "_") + 1);

					$typeTmp = ${"TYPE_REKV_".$ind."_".$arPersonType["ID"]};
					$valueTmp = ${"VALUE1_REKV_".$ind."_".$arPersonType["ID"]};
					if ($valueTmp == '')
						$valueTmp = ${"VALUE2_REKV_".$ind."_".$arPersonType["ID"]};

					if($v <> '' && $valueTmp <> '')
					{
						$arParams["REKV_".$i] = array(
								"TYPE" => $typeTmp,
								"VALUE" => $valueTmp,
								"NAME" => $v,
							);
						$i++;
					}
				}
			}
		}
		if(intval($arExportProfile[$arPersonType["ID"]])>0)
			$res = CSaleExport::Update($arExportProfile[$arPersonType["ID"]], Array("PERSON_TYPE_ID" => $arPersonType["ID"], "VARS" => serialize($arParams)));
		else
			$res = CSaleExport::Add(Array("PERSON_TYPE_ID" => $arPersonType["ID"], "VARS" => serialize($arParams)));
	}
}
?>
<script>
<!--
var arUserFieldsList = new Array("ID", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME", "EMAIL", "LID", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_ICQ", "PERSONAL_GENDER", "PERSONAL_FAX", "PERSONAL_PHONE", "PERSONAL_MOBILE", "PERSONAL_STREET", "PERSONAL_MAILBOX", "PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY", "WORK_COMPANY", "WORK_DEPARTMENT", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_STREET", "WORK_MAILBOX", "WORK_CITY", "WORK_STATE", "WORK_ZIP", "WORK_COUNTRY", "PERSONAL_NOTES");
var arUserFieldsNameList = new Array("<?=GetMessage("SPS_USER_ID")?>", "<?=GetMessage("SPS_USER_LOGIN")?>", "<?=GetMessage("SPS_USER_NAME")?>", "<?=GetMessage("SPS_USER_SECOND_NAME")?>", "<?=GetMessage("SPS_USER_LAST_NAME")?>", "EMail", "<?=GetMessage("SPS_USER_SITE")?>", "<?=GetMessage("SPS_USER_PROF")?>", "<?=GetMessage("SPS_USER_WEB")?>", "<?=GetMessage("SPS_USER_ICQ")?>", "<?=GetMessage("SPS_USER_SEX")?>", "<?=GetMessage("SPS_USER_FAX")?>", "<?=GetMessage("SPS_USER_PHONE")?>", "<?=GetMessage("SPS_USER_PHONE_MOBILE")?>", "<?=GetMessage("SPS_USER_ADDRESS")?>", "<?=GetMessage("SPS_USER_POST")?>", "<?=GetMessage("SPS_USER_CITY")?>", "<?=GetMessage("SPS_USER_STATE")?>", "<?=GetMessage("SPS_USER_ZIP")?>", "<?=GetMessage("SPS_USER_COUNTRY")?>", "<?=GetMessage("SPS_USER_COMPANY")?>", "<?=GetMessage("SPS_USER_DEPT")?>", "<?=GetMessage("SPS_USER_DOL")?>", "<?=GetMessage("SPS_USER_COM_WEB")?>", "<?=GetMessage("SPS_USER_COM_PHONE")?>", "<?=GetMessage("SPS_USER_COM_FAX")?>", "<?=GetMessage("SPS_USER_COM_ADDRESS")?>", "<?=GetMessage("SPS_USER_COM_POST")?>", "<?=GetMessage("SPS_USER_COM_CITY")?>", "<?=GetMessage("SPS_USER_COM_STATE")?>", "<?=GetMessage("SPS_USER_COM_ZIP")?>", "<?=GetMessage("SPS_USER_COM_COUNTRY")?>", "<?=GetMessage("SPS_USER_COM_NOTES")?>");

var arOrderFieldsList = new Array("ID", "DATE_INSERT", "DATE_INSERT_DATE", "SHOULD_PAY", "CURRENCY", "PRICE", "LID", "PRICE_DELIVERY", "DISCOUNT_VALUE", "USER_ID", "PAY_SYSTEM_ID", "PAY_SYSTEM_NAME", "DELIVERY_ID", "DELIVERY_NAME", "TAX_VALUE", "TRACKING_NUMBER", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "SUM_PAID", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "COMMENTS", "USER_DESCRIPTION");
var arOrderFieldsNameList = new Array("<?=GetMessage("SPS_ORDER_ID")?>", "<?=GetMessage("SPS_ORDER_DATETIME")?>", "<?=GetMessage("SPS_ORDER_DATE")?>", "<?=GetMessage("SPS_ORDER_PRICE")?>", "<?=GetMessage("SPS_ORDER_CURRENCY")?>", "<?=GetMessage("SPS_ORDER_SUM")?>", "<?=GetMessage("SPS_ORDER_SITE")?>", "<?=GetMessage("SPS_ORDER_PRICE_DELIV")?>", "<?=GetMessage("SPS_ORDER_DESCOUNT")?>", "<?=GetMessage("SPS_ORDER_USER_ID")?>", "<?=GetMessage("SPS_ORDER_PS")?>","<?=GetMessage("SPS_ORDER_PS_NAME")?>", "<?=GetMessage("SPS_ORDER_DELIV")?>", "<?=GetMessage("SPS_ORDER_DELIV_NAME")?>", "<?=GetMessage("SPS_ORDER_TAX")?>", "<?=GetMessage("SPS_ORDER_TRACKING_NUMBER")?>", "<?=GetMessage("SPS_ORDER_PAY_VOUCHER_NUM")?>", "<?=GetMessage("SPS_ORDER_PAY_VOUCHER_DATE")?>", "<?=GetMessage("SPS_ORDER_SUM_PAID")?>", "<?=GetMessage("SPS_ORDER_DELIVERY_DOC_NUM")?>", "<?=GetMessage("SPS_ORDER_DELIVERY_DOC_DATE")?>", "<?=GetMessage("SPS_ORDER_COMMENTS")?>", "<?=GetMessage("SPS_ORDER_USER_DESCRIPTION")?>");

var arPropFieldsList = new Array();
var arPropFieldsNameList = new Array();


function PropertyTypeChange(pkey, ind)
{
	var oType = BX("TYPE_" + pkey + "_" + ind);
	var oValue1 = BX("VALUE1_" + pkey + "_" + ind);
	var oValue2 = BX("VALUE2_" + pkey + "_" + ind);

	eval("var cur_type = ''; if (typeof(param_" + pkey + "_type_" + ind + ") == 'string') cur_type = param_" + pkey + "_type_" + ind + ";");
	eval("var cur_val = ''; if (typeof(param_" + pkey + "_value_" + ind + ") == 'string') cur_val = param_" + pkey + "_value_" + ind + ";");
	eval("var cur_name = ''; if (typeof(param_" + pkey + "_name_" + ind + ") == 'string') cur_name = param_" + pkey + "_name_" + ind + ";");

	if(cur_name.length > 0)
	{
		num = pkey.substr(pkey.lastIndexOf('_')+1);
		src = BX("REKV_" + ind + "_" + num);
		if(src.value.length <= 0)
			src.value = cur_name;
	}

	var value1_length = oValue1.length;
	while (value1_length > 0)
	{
		value1_length--;
		oValue1.options[value1_length] = null;
	}
	value1_length = 0;

	var typeVal = oType[oType.selectedIndex].value;
	if (typeVal == "USER")
	{
		oValue2.style["display"] = "none";
		oValue1.style["display"] = "block";

		for (i = 0; i < arUserFieldsList.length; i++)
		{
			var newoption = new Option(arUserFieldsNameList[i], arUserFieldsList[i], false, false);
			oValue1.options[value1_length] = newoption;

			if (typeVal == cur_type && cur_val == arUserFieldsList[i])
				oValue1.selectedIndex = value1_length;

			value1_length++;
		}
	}
	else
	{
		if (typeVal == "ORDER")
		{
			oValue2.style["display"] = "none";
			oValue1.style["display"] = "block";

			for (i = 0; i < arOrderFieldsList.length; i++)
			{
				var newoption = new Option(arOrderFieldsNameList[i], arOrderFieldsList[i], false, false);
				oValue1.options[value1_length] = newoption;

				if (typeVal == cur_type && cur_val == arOrderFieldsList[i])
					oValue1.selectedIndex = value1_length;

				value1_length++;
			}
		}
		else
		{
			if (typeVal == "PROPERTY")
			{
				oValue2.style["display"] = "none";
				oValue1.style["display"] = "block";

				for (i = 0; i < arPropFieldsList[ind].length; i++)
				{
					var newoption = new Option(arPropFieldsNameList[ind][i], arPropFieldsList[ind][i], false, false);
					oValue1.options[value1_length] = newoption;

					if (typeVal == cur_type && cur_val == arPropFieldsList[ind][i])
						oValue1.selectedIndex = value1_length;

					value1_length++;
				}
			}
			else
			{
				oValue1.style["display"] = "none";
				oValue2.style["display"] = "block";

				oValue2.value = cur_val;
			}
		}
	}
}

function InitActionProps(pkey, ind)
{
	if(pkey == 'REKV_n0')
	{

		eval("var rekv_cnt = 0; if (typeof(param_person_type_rekv_" + ind + ") == 'string') rekv_cnt = param_person_type_rekv_" + ind + ";");

		rekv_cnt1 = parseInt(rekv_cnt);
		if(rekv_cnt1 > 0)
			rekv_cnt1 += 3;
		else
			rekv_cnt1 = 2;

		for (j = 1; j <= rekv_cnt1; j++)
		{
			AddRekv(ind, j);

			pkey1 = 'REKV_n' + j;

			var oType = BX("TYPE_" + pkey1 + "_" + ind);
			var cur_type = '';
			eval("if (typeof(param_" + pkey1 + "_type_" + ind + ") == 'string') cur_type = param_" + pkey1 + "_type_" + ind + ";");
			for (i = 0; i < oType.options.length; i++)
			{
				if (oType.options[i].value == cur_type)
				{
					oType.selectedIndex = i;
					break;
				}
			}

			PropertyTypeChange(pkey1, ind);
		}
	}

	var oType = BX("TYPE_" + pkey + "_" + ind);
	var cur_type = '';
	eval("if (typeof(param_" + pkey + "_type_" + ind + ") == 'string') cur_type = param_" + pkey + "_type_" + ind + ";");
	for (i = 0; i < oType.options.length; i++)
	{
		if (oType.options[i].value == cur_type)
		{
			oType.selectedIndex = i;
			break;
		}
	}
	PropertyTypeChange(pkey, ind);
}

function ActionFileChange(ind, type)
{
	ind = parseInt(ind);
	var cur_type_1c = '';
	eval("if (typeof(param_person_type_1c_" + ind + ") == 'string') cur_type_1c = param_person_type_1c_" + ind + ";");
	if(cur_type_1c != "" && type == "")
	{
		type = cur_type_1c;
		BX("person_type_1c_"+ind).value = type;
	}
	window.frames["hidden_action_frame_" + ind].location.replace('/bitrix/admin/sale_options_get.php?lang=<?= htmlspecialcharsbx($lang) ?>&type='+type+'&divInd='+ind);
}

function AddRekv(ind, j)
{
	var tbl = BX('rekv-table-'+ind);
	var newRow = tbl.insertRow(tbl.rows.length);
	var oCell = newRow.insertCell(-1);
	var oCellHtml = BX.clone(BX('REKV_' + ind + '_n0'), true);
	oCellHtml.id = 'REKV_' + ind + '_n' + j;
	oCellHtml.name = 'REKV_' + ind + '_n' + j;
	oCellHtml.value = '';
	oCell.appendChild(oCellHtml);

	var oCell = newRow.insertCell(-1);
	var oCellHtml = BX.clone(BX('TYPE_REKV_n0_' + ind), true);
	oCellHtml.id = 'TYPE_REKV_n' + j +'_' + ind;
	oCellHtml.name = 'TYPE_REKV_n' + j +'_' + ind;
	oCellHtml.onchange = null;
	oCell.appendChild(oCellHtml);

	BX.bind(BX('TYPE_REKV_n' + j +'_' + ind), 'change', function() {PropertyTypeChange('REKV_n'+j, ind);});

	var oCell = newRow.insertCell(-1);
	var oCellHtml = BX.clone(BX('VALUE1_REKV_n0_' + ind), true);
	oCellHtml.id = 'VALUE1_REKV_n' + j +'_' + ind;
	oCellHtml.name = 'VALUE1_REKV_n' + j +'_' + ind;
	oCellHtml.style.display = 'none';
	oCell.appendChild(oCellHtml);

	var oCellHtml = BX.clone(BX('VALUE2_REKV_n0_' + ind), true);
	oCellHtml.id = 'VALUE2_REKV_n' + j +'_' + ind;
	oCellHtml.name = 'VALUE2_REKV_n' + j +'_' + ind;
	oCellHtml.value = '';
	oCellHtml.style.display = 'block';
	oCell.appendChild(oCellHtml);
	eval('param_person_type_rekv_' + ind + ' = ' + j + ';');
}

function AddRekvMore(ind)
{
	eval('curV = param_person_type_rekv_' + ind + ';');
	curV = parseInt(curV);
	AddRekv(ind, curV+1);
}
//-->
</script>

	<tr>
		<td colspan="2">
			<script>
			<!--
			var paySysActVisible_<?= $arPersonType["ID"] ?> = true;
			<?
			$dbExport = CSaleExport::GetList();
			while($arExport = $dbExport->Fetch())
			{
				$arExpParams = unserialize($arExport["VARS"], ['allowed_classes' => false]);
				$i = 0;
				foreach($arExpParams as $k => $v)
				{
					if(!is_array($v))
					{
						$v = array("NAME" => "", "VALUE" => "", "TYPE" => "");
					}
					if($v["NAME"] <> '')
					{
						$k = str_replace("REKV_", "REKV_n", $k);
						?>
						var param_<?=$k?>_name_<?= $arExport["PERSON_TYPE_ID"] ?> = '<?= CUtil::JSEscape($v["NAME"]) ?>';
						<?
					}

					if(mb_strpos($k, "REKV_") !== false)
						$i++;
					?>
					var param_<?= $k ?>_type_<?= $arExport["PERSON_TYPE_ID"] ?> = '<?= CUtil::JSEscape($v["TYPE"]) ?>';
					var param_<?= $k ?>_value_<?= $arExport["PERSON_TYPE_ID"] ?> = '<?= CUtil::JSEscape($v["VALUE"]) ?>';
					<?
				}
				?>
				var param_person_type_1c_<?=$arExport["PERSON_TYPE_ID"]?> = '<?=(($arExpParams["IS_FIZ"]=="Y")?"FIZ":"UR")?>';
				var param_person_type_rekv_<?=$arExport["PERSON_TYPE_ID"]?> = '<?=intval($i)?>';
				<?
			}
			?>
			//-->
			</script>
			<?
			$aTabs1 = array();
			$personType = Array();
			$dbPersonType = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME"=>"ASC"), Array("ACTIVE"=>"Y"));
			while($arPersonType = $dbPersonType -> GetNext())
			{
				$aTabs1[] = Array("DIV"=>"oedit".$arPersonType["ID"], "TAB" => $arPersonType["NAME"]." (".htmlspecialcharsbx(implode(", ", $arPersonType["LIDS"])).")", "TITLE" => $arPersonType["NAME"]." (".htmlspecialcharsbx(implode(", ", $arPersonType["LIDS"])).")");
				$personType[$arPersonType["ID"]] = $arPersonType;
				?>
					<script>
					<!--
					arPropFieldsList[<?= $arPersonType["ID"] ?>] = new Array();
					arPropFieldsNameList[<?= $arPersonType["ID"] ?>] = new Array();
					<?
					$dbOrderProps = CSaleOrderProps::GetList(
							array("SORT" => "ASC", "NAME" => "ASC"),
							array("PERSON_TYPE_ID" => $arPersonType["ID"]),
							false,
							false,
							array("ID", "CODE", "NAME", "TYPE", "SORT")
						);
					$i = -1;
					while ($arOrderProps = $dbOrderProps->Fetch())
					{
						$i++;
						?>
						arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["ID"]) ?>';
						arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["NAME"]) ?>';
						<?
						if ($arOrderProps["TYPE"] == "LOCATION")
						{
							$i++;
							?>
							arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["ID"]."_COUNTRY") ?>';
							arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["NAME"]." (".GetMessage("SPS_JCOUNTRY").")") ?>';
							<?
							$i++;
							?>
							arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["ID"]."_REGION") ?>';
							arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["NAME"]." (".GetMessage("SPS_JREGION").")") ?>';
							<?

							$i++;
							?>
							arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["ID"]."_CITY") ?>';
							arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= CUtil::JSEscape($arOrderProps["NAME"]." (".GetMessage("SPS_JCITY").")") ?>';
							<?
						}
					}
					?>
					//-->
					</script>
				<?
			}
			$tabControl1 = new CAdminViewTabControl("tabControl1", $aTabs1);
			$tabControl1->Begin();
			foreach($personType as $val)
			{
				$tabControl1->BeginNextTab();
				?>
				<table cellspacing="5" cellpadding="0" border="0" width="0%">
				<tr>
					<td><?=GetMessage("SO_CHOOSE_1C_PERSON_TYPE")?></td>
					<td><select name="person_type_1c_<?=$val["ID"]?>" id="person_type_1c_<?=$val["ID"]?>" onchange="ActionFileChange(<?=CUtil::JSEscape($val["ID"])?>, this.value)">
							<option value="FIZ"><?=GetMessage("SO_PERSON_TYPE_FIZ")?></option>
							<option value="UR"><?=GetMessage("SO_PERSON_TYPE_UR")?></option>
						</select>
					</td>
				</tr>
				</table>
				<br />
				<div id="export_<?= $val["ID"] ?>" style="display: block;"></div>
				<iframe style="width:0px; height:0px; border: 0px" name="hidden_action_frame_<?= $val["ID"] ?>" src="" width="0" height="0"></iframe>
				<input type="hidden" name="export_fields_<?=$val["ID"]?>" id="export_fields_<?=$val["ID"]?>" value="">
				<script>
				<!--
				BX.ready(function(){ActionFileChange(<?=CUtil::JSEscape($val["ID"])?>, '');});
				//-->
				</script>
				<?
			}
			$tabControl1->End();
			?>
			<?echo BeginNote();?>
			<font class="legendtext">
			<?=GetMessage("SO_1C_COMMENT")?>
			</font>
			<?echo EndNote(); ?>

		</td>
	</tr>
	<?
endif;