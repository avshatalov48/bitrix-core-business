<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

$arFieldsShop = Array(
	"COMPANY_NAME" => Array("NAME" => GetMessage("SRE_COMPANY_NAME"), "TYPE" => "", "VALUE" => ""),
	"ADDRESS" => Array("NAME" => GetMessage("SRE_ADDRESS"), "TYPE" => "", "VALUE" => ""),
	"CITY" => Array("NAME" => GetMessage("SRE_CITY"), "TYPE" => "", "VALUE" => ""),
	"COUNTRY" => Array("NAME" => GetMessage("SRE_COUNTRY"), "TYPE" => "", "VALUE" => ""),
	"INDEX" => Array("NAME" => GetMessage("SRE_INDEX"), "TYPE" => "", "VALUE" => ""),
	"INN" => Array("NAME" => GetMessage("SRE_INN"), "TYPE" => "", "VALUE" => ""),
	"KPP" => Array("NAME" => GetMessage("SRE_KPP"), "TYPE" => "", "VALUE" => ""),
	"BIK" => Array("NAME" => GetMessage("SRE_BIK"), "TYPE" => "", "VALUE" => ""),
	"RSCH" => Array("NAME" => GetMessage("SRE_RSCH"), "TYPE" => "", "VALUE" => ""),
	"RSCH_BANK" => Array("NAME" => GetMessage("SRE_RSCH_BANK"), "TYPE" => "", "VALUE" => ""),
	"RSCH_CITY" => Array("NAME" => GetMessage("SRE_RSCH_CITY"), "TYPE" => "", "VALUE" => ""),
	"KSCH" => Array("NAME" => GetMessage("SRE_KSCH"), "TYPE" => "", "VALUE" => ""),
	"PHONE" => Array("NAME" => GetMessage("SRE_PHONE"), "TYPE" => "", "VALUE" => ""),
	"DIRECTOR" => Array("NAME" => GetMessage("SRE_DIRECTOR"), "TYPE" => "", "VALUE" => ""),
	"BUHG" => Array("NAME" => GetMessage("SRE_BUHG"), "TYPE" => "", "VALUE" => ""),
);

$arFieldsBuyer = Array(
	"BUYER_COMPANY_NAME" => Array("NAME" => GetMessage("SRE_BUYER_COMPANY_NAME"), "TYPE" => "", "VALUE" => ""),
	"BUYER_FIRST_NAME" => Array("NAME" => GetMessage("SRE_BUYER_FIRST_NAME"), "TYPE" => "", "VALUE" => ""),
	"BUYER_SECOND_NAME" => Array("NAME" => GetMessage("SRE_BUYER_SECOND_NAME"), "TYPE" => "", "VALUE" => ""),
	"BUYER_LAST_NAME" => Array("NAME" => GetMessage("SRE_BUYER_LAST_NAME"), "TYPE" => "", "VALUE" => ""),
	"BUYER_ADDRESS" => Array("NAME" => GetMessage("SRE_BUYER_ADDRESS"), "TYPE" => "", "VALUE" => ""),
	"BUYER_CITY" => Array("NAME" => GetMessage("SRE_BUYER_CITY"), "TYPE" => "", "VALUE" => ""),
	"BUYER_COUNTRY" => Array("NAME" => GetMessage("SRE_BUYER_COUNTRY"), "TYPE" => "", "VALUE" => ""),
	"BUYER_INDEX" => Array("NAME" => GetMessage("SRE_BUYER_INDEX"), "TYPE" => "", "VALUE" => ""),
	"BUYER_CONTACT" => Array("NAME" => GetMessage("SRE_BUYER_CONTACT"), "TYPE" => "", "VALUE" => ""),
	"BUYER_PHONE" => Array("NAME" => GetMessage("SRE_BUYER_PHONE"), "TYPE" => "", "VALUE" => ""),
	"BUYER_INN" => Array("NAME" => GetMessage("SRE_INN"), "TYPE" => "", "VALUE" => ""),
	"BUYER_KPP" => Array("NAME" => GetMessage("SRE_KPP"), "TYPE" => "", "VALUE" => ""),
	"BUYER_BIK" => Array("NAME" => GetMessage("SRE_BIK"), "TYPE" => "", "VALUE" => ""),
	"BUYER_RSCH" => Array("NAME" => GetMessage("SRE_RSCH"), "TYPE" => "", "VALUE" => ""),
	"BUYER_RSCH_BANK" => Array("NAME" => GetMessage("SRE_RSCH_BANK"), "TYPE" => "", "VALUE" => ""),
	"BUYER_RSCH_CITY" => Array("NAME" => GetMessage("SRE_RSCH_CITY"), "TYPE" => "", "VALUE" => ""),
	"BUYER_KSCH" => Array("NAME" => GetMessage("SRE_KSCH"), "TYPE" => "", "VALUE" => ""),

);

$errorMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST"
	&& ($save <> '' || $apply <> '')
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid())
{
	$arOpt = Array();
	foreach($arFieldsShop as $key => $val)
	{
		$arOpt[$key] = Array("TYPE" => $_POST["TYPE_".$key], "VALUE" => ($_POST["TYPE_".$key] <> '' ? $_POST["VALUE_".$key] : trim($_POST["VALUE2_".$key])));
	}

	foreach($arFieldsBuyer as $key => $val)
	{
		$arOpt[$key] = Array("TYPE" => $_POST["TYPE_".$key], "VALUE" => ($_POST["TYPE_".$key] <> '' ? $_POST["VALUE_".$key] : trim($_POST["VALUE2_".$key])));
	}

	$serResult = serialize($arOpt);
	$lenght = mb_strlen($serResult);
	if(intval($lenght) > 2000)
	{
		for($i=1; $i <= ceil($lenght/2000); $i++)
		{
			COption::SetOptionString("sale", "reports".$i, mb_substr($serResult, ($i - 1) * 2000, $i * 2000));
		}
		COption::SetOptionInt("sale", "reports_count", $i);

	}
	else
	{
		COption::SetOptionString("sale", "reports", serialize($arOpt));
		COption::RemoveOption("sale", "reports_count");
	}

	LocalRedirect("sale_report_edit.php?lang=".LANG);
}

$report = "";
$serCount = intval(COption::GetOptionInt("sale", "reports_count"));
if($serCount > 0)
{
	for($i=1; $i <= $serCount; $i++)
	{
		$report .= COption::GetOptionString("sale", "reports".$i);
	}
}
else
	$report = COption::GetOptionString("sale", "reports");

$arOptions = unserialize($report, ['allowed_classes' => false]);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SALE_REPORT"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>


<script language="JavaScript">
<!--

	var arUserFieldsList = new Array("ID", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME", "EMAIL", "LID", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_ICQ", "PERSONAL_GENDER", "PERSONAL_FAX", "PERSONAL_MOBILE", "PERSONAL_STREET", "PERSONAL_MAILBOX", "PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY", "WORK_COMPANY", "WORK_DEPARTMENT", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_STREET", "WORK_MAILBOX", "WORK_CITY", "WORK_STATE", "WORK_ZIP", "WORK_COUNTRY");
	var arUserFieldsNameList = new Array("<?= GetMessage("SPS_USER_ID") ?>", "<?= GetMessage("SPS_USER_LOGIN") ?>", "<?= GetMessage("SPS_USER_NAME") ?>", "<?= GetMessage("SPS_USER_SECOND_NAME") ?>", "<?= GetMessage("SPS_USER_LAST_NAME") ?>", "EMail", "<?= GetMessage("SPS_USER_SITE") ?>", "<?= GetMessage("SPS_USER_PROF") ?>", "<?= GetMessage("SPS_USER_WEB") ?>", "<?= GetMessage("SPS_USER_ICQ") ?>", "<?= GetMessage("SPS_USER_SEX") ?>", "<?= GetMessage("SPS_USER_FAX") ?>", "<?= GetMessage("SPS_USER_PHONE") ?>", "<?= GetMessage("SPS_USER_ADDRESS") ?>", "<?= GetMessage("SPS_USER_POST") ?>", "<?= GetMessage("SPS_USER_CITY") ?>", "<?= GetMessage("SPS_USER_STATE") ?>", "<?= GetMessage("SPS_USER_ZIP") ?>", "<?= GetMessage("SPS_USER_COUNTRY") ?>", "<?= GetMessage("SPS_USER_COMPANY") ?>", "<?= GetMessage("SPS_USER_DEPT") ?>", "<?= GetMessage("SPS_USER_DOL") ?>", "<?= GetMessage("SPS_USER_COM_WEB") ?>", "<?= GetMessage("SPS_USER_COM_PHONE") ?>", "<?= GetMessage("SPS_USER_COM_FAX") ?>", "<?= GetMessage("SPS_USER_COM_ADDRESS") ?>", "<?= GetMessage("SPS_USER_COM_POST") ?>", "<?= GetMessage("SPS_USER_COM_CITY") ?>", "<?= GetMessage("SPS_USER_COM_STATE") ?>", "<?= GetMessage("SPS_USER_COM_ZIP") ?>", "<?= GetMessage("SPS_USER_COM_COUNTRY") ?>");

	var arOrderFieldsList = new Array("ID", "DATE_INSERT", "DATE_INSERT_DATE", "SHOULD_PAY", "CURRENCY", "PRICE", "LID", "PRICE_DELIVERY", "DISCOUNT_VALUE", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "TAX_VALUE");
	var arOrderFieldsNameList = new Array("<?= GetMessage("SPS_ORDER_ID") ?>", "<?= GetMessage("SPS_ORDER_DATETIME") ?>", "<?= GetMessage("SPS_ORDER_DATE") ?>", "<?= GetMessage("SPS_ORDER_PRICE") ?>", "<?= GetMessage("SPS_ORDER_CURRENCY") ?>", "<?= GetMessage("SPS_ORDER_SUM") ?>", "<?= GetMessage("SPS_ORDER_SITE") ?>", "<?= GetMessage("SPS_ORDER_PRICE_DELIV") ?>", "<?= GetMessage("SPS_ORDER_DESCOUNT") ?>", "<?= GetMessage("SPS_ORDER_USER_ID") ?>", "<?= GetMessage("SPS_ORDER_PS") ?>", "<?= GetMessage("SPS_ORDER_DELIV") ?>", "<?= GetMessage("SPS_ORDER_TAX") ?>");

	var arPropFieldsList = new Array();
	var arPropFieldsNameList = new Array();

	function PropertyTypeChange(pkey, cVal)
	{
		var oType = document.getElementById("TYPE_" + pkey);
		var oValue = document.getElementById("VALUE_" + pkey);
		var oValue2 = document.getElementById("VALUE2_" + pkey);

		var value_length = oValue.length;
		while (value_length > 0)
		{
			value_length--;
			oValue.options[value_length] = null;
		}
		value_length = 0;

		var typeVal = oType[oType.selectedIndex].value;
		if (typeVal == "USER")
		{
			oValue2.style["display"] = "none";
			oValue.style["display"] = "block";

			for (i = 0; i < arUserFieldsList.length; i++)
			{
				var newoption = new Option(arUserFieldsNameList[i], arUserFieldsList[i], false, false);
				oValue.options[value_length] = newoption;

				if (cVal == arUserFieldsList[i])
					oValue.selectedIndex = value_length;

				value_length++;
			}
		}
		else
		{
			if (typeVal == "ORDER")
			{
				oValue2.style["display"] = "none";
				oValue.style["display"] = "block";

				for (i = 0; i < arOrderFieldsList.length; i++)
				{
					var newoption = new Option(arOrderFieldsNameList[i], arOrderFieldsList[i], false, false);
					oValue.options[value_length] = newoption;

					if (cVal == arOrderFieldsList[i])
						oValue.selectedIndex = value_length;

					value_length++;
				}
			}
			else
			{
				if (typeVal == "PROPERTY")
				{
					oValue2.style["display"] = "none";
					oValue.style["display"] = "block";

					for (i = 0; i < arPropFieldsList.length; i++)
					{
						var newoption = new Option(arPropFieldsNameList[i], arPropFieldsList[i], false, false);
						oValue.options[value_length] = newoption;

						if (cVal == arPropFieldsList[i])
							oValue.selectedIndex = value_length;

						value_length++;
					}
				}
				else
				{
					oValue.style["display"] = "none";
					oValue2.style["display"] = "block";

					if(cVal)
						oValue2.value = cVal;
				}
			}
		}
	}

	arPropFieldsList = new Array();
	arPropFieldsNameList = new Array();
	<?
	$dbOrderProps = CSaleOrderProps::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array(),
			false,
			false,
			array("ID", "CODE", "NAME", "TYPE", "SORT")
		);
	$i = -1;
	$arOrderSel = Array();
	while ($arOrderProps = $dbOrderProps->GetNext())
	{
		$orderID = (($arOrderProps["CODE"] <> '') ? $arOrderProps["CODE"] : $arOrderProps["ID"]);
		if(!in_array($orderID, $arOrderSel))
		{
			$arOrderSel[] = $orderID;
			$i++;
			?>
			arPropFieldsList[<?= $i ?>] = '<?= CUtil::JSEscape($orderID) ?>';
			arPropFieldsNameList[<?= $i ?>] = '<?= CUtil::JSEscape("[".$orderID."] ".$arOrderProps["NAME"]) ?>';
			<?
			if ($arOrderProps["TYPE"] == "LOCATION")
			{
				$i++;
				?>
				arPropFieldsList[<?= $i ?>] = '<?= CUtil::JSEscape($orderID."_COUNTRY") ?>';
				arPropFieldsNameList[<?= $i ?>] = '<?= CUtil::JSEscape("[".$orderID."] ".$arOrderProps["NAME"]." (".GetMessage("SPS_JCOUNTRY").")") ?>';
				<?

				$i++;
				?>
				arPropFieldsList[<?= $i ?>] = '<?= CUtil::JSEscape($orderID."_CITY") ?>';
				arPropFieldsNameList[<?= $i ?>] = '<?= CUtil::JSEscape("[".$orderID."] ".$arOrderProps["NAME"]." (".GetMessage("SPS_JCITY").")") ?>';
				<?
			}
		}
	}
	?>
//-->
</script>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="report_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SRE_TAB_NAME"), "ICON" => "sale")
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" class="internal">
				<tr class="heading">
					<td align="center"><?=GetMessage("SRE_PARAM_NAME")?></td>
					<td align="center"><?=GetMessage("SRE_PARAM_TYPE")?></td>
					<td align="center"><?=GetMessage("SRE_PARAM_VALUE")?></td>
				</tr>
				<tr class="heading">
					<td colspan="3" align="center"><?=GetMessage("SRE_PARAM_SHOP")?></td>
				</tr>
				<?
				foreach($arFieldsShop as $key => $val)
				{
					if(!empty($arOptions))
					{
						$val["TYPE"] = $arOptions[$key]["TYPE"];
						$val["VALUE"] = $arOptions[$key]["VALUE"];
					}
					?>
					<tr>
						<td><?=$val["NAME"]?></td>
						<td><select name="TYPE_<?=$key?>" id="TYPE_<?=$key?>" onchange="PropertyTypeChange('<?=$key?>');">
								<option value=""><?=GetMessage("SRE_PARAM_PROP_TYPE_1")?></option>
								<option value="USER"<?if($val["TYPE"] == "USER") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_2")?></option>
								<option value="ORDER"<?if($val["TYPE"] == "ORDER") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_3")?></option>
								<option value="PROPERTY"<?if($val["TYPE"] == "PROPERTY") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_4")?></option>
							</select>
						</td>
						<td>
							<select name="VALUE_<?=$key?>" id="VALUE_<?=$key?>" style="display:none;">
								<option value="">--</option>
							</select>
							<input type="text" name="VALUE2_<?=$key?>" id="VALUE2_<?=$key?>" value="<?if($val["TYPE"] == '') echo htmlspecialcharsbx($val["VALUE"])?>"  size="40">
							<?if($val["VALUE"] <> '' && $val["TYPE"] <> '')
							{
								?>
								<script>
									PropertyTypeChange('<?=CUtil::JSEscape($key)?>', '<?=CUtil::JSEscape($val["VALUE"])?>');
								</script>
								<?
							}
							?>
						</td>
					</tr>
					<?
				}
				?>
<tr class="heading">
					<td colspan="3" align="center"><?=GetMessage("SRE_PARAM_USER")?></td>
				</tr>
				<?
				foreach($arFieldsBuyer as $key => $val)
				{
					if(!empty($arOptions))
					{
						$val["TYPE"] = ($arOptions[$key]["TYPE"]);
						$val["VALUE"] = ($arOptions[$key]["VALUE"]);
					}
					?>
					<tr>
						<td><?=$val["NAME"]?></td>
						<td><select name="TYPE_<?=$key?>" id="TYPE_<?=$key?>" onchange="PropertyTypeChange('<?=$key?>');">
								<option value=""><?=GetMessage("SRE_PARAM_PROP_TYPE_1")?></option>
								<option value="USER"<?if($val["TYPE"] == "USER") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_2")?></option>
								<option value="ORDER"<?if($val["TYPE"] == "ORDER") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_3")?></option>
								<option value="PROPERTY"<?if($val["TYPE"] == "PROPERTY") echo " selected";?>><?=GetMessage("SRE_PARAM_PROP_TYPE_4")?></option>
							</select>
						</td>
						<td>
							<select name="VALUE_<?=$key?>" id="VALUE_<?=$key?>" style="display:none;">
								<option value="">--</option>
							</select>
							<input type="text" name="VALUE2_<?=$key?>" id="VALUE2_<?=$key?>" value=""  size="40">
							<?if($val["VALUE"] <> '')
							{
								?>
								<script>
									PropertyTypeChange('<?=CUtil::JSEscape($key)?>', '<?=CUtil::JSEscape($val["VALUE"])?>');
								</script>
								<?
							}
							?>
						</td>
					</tr>
					<?
				}
				?>
			</table>
		</td>
	</tr>


<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_pay_system.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");?>