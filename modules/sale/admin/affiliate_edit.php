<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = intval($ID);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SAE_AFF_TAB"), "ICON" => "sale", "TITLE" => GetMessage("SAE_AFF_TAB_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($REQUEST_METHOD=="POST" && $Update <> '' && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	if ($SITE_ID == '')
		$errorMessage .= GetMessage("SAE_NO_SITE_PLAN").".<br>";
	if (intval($USER_ID) <= 0)
		$errorMessage .= GetMessage("SAE_NO_USER").".<br>";
	if (intval($PLAN_ID) <= 0)
		$errorMessage .= GetMessage("SAE_NO_PLAN").".<br>";
	if ($DATE_CREATE == '')
		$errorMessage .= GetMessage("SAE_NO_DATE_CREATE").".<br>";

	$ACTIVE = (($ACTIVE == "Y") ? "Y" : "N");
	$FIX_PLAN = (($FIX_PLAN == "Y") ? "Y" : "N");

	$PAID_SUM = str_replace(",", ".", $PAID_SUM);
	$PAID_SUM = DoubleVal($PAID_SUM);

	$APPROVED_SUM = str_replace(",", ".", $APPROVED_SUM);
	$APPROVED_SUM = DoubleVal($APPROVED_SUM);

	$PENDING_SUM = str_replace(",", ".", $PENDING_SUM);
	$PENDING_SUM = DoubleVal($PENDING_SUM);

	if ($errorMessage == '')
	{
		$dbAffiliate = CSaleAffiliate::GetList(array(), array("USER_ID" => $USER_ID, "SITE_ID" => $SITE_ID, "!ID" => $ID));
		if ($dbAffiliate->Fetch())
			$errorMessage .= str_replace("#USER_ID#", $USER_ID, str_replace("#SITE_ID#", $SITE_ID, GetMessage("SAE_AFFILIATE_ALREADY_EXISTS"))).".<br>";
	}

	if ($errorMessage == '')
	{
		$arFields = array(
			"SITE_ID" => $SITE_ID,
			"USER_ID" => $USER_ID,
			"AFFILIATE_ID" => (intval($AFFILIATE_ID) > 0 ? $AFFILIATE_ID : false),
			"PLAN_ID" => $PLAN_ID,
			"ACTIVE" => $ACTIVE,
			"DATE_CREATE" => $DATE_CREATE,
			"PAID_SUM" => $PAID_SUM,
			"APPROVED_SUM" => $APPROVED_SUM,
			"PENDING_SUM" => $PENDING_SUM,
			"LAST_CALCULATE" => ($LAST_CALCULATE <> '' ? $LAST_CALCULATE : false),
			"AFF_SITE" => $AFF_SITE,
			"AFF_DESCRIPTION" => $AFF_DESCRIPTION,
			"FIX_PLAN" => $FIX_PLAN
		);

		if ($ID > 0)
		{
			if (!CSaleAffiliate::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString().".<br>";
				else
					$errorMessage .= GetMessage("SAE_ERROR_SAVE_AFF").".<br>";
			}
		}
		else
		{
			$ID = CSaleAffiliate::Add($arFields);
			$ID = intval($ID);
			if ($ID <= 0)
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString().".<br>";
				else
					$errorMessage .= GetMessage("SAE_ERROR_SAVE_AFF").".<br>";
			}
		}
	}

	if ($errorMessage == '')
	{
		if ($apply == '')
			LocalRedirect("/bitrix/admin/sale_affiliate.php?lang=".LANG.GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/sale_affiliate_edit.php?lang=".LANG."&ID=".$ID.GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("SAE_TITLE_UPDATE_AFF")));
else
	$APPLICATION->SetTitle(GetMessage("SAE_TITLE_ADD_AFF"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$dbAffiliate = CSaleAffiliate::GetList(array(), array("ID" => $ID));
if (!$dbAffiliate->ExtractFields("str_"))
	$ID = 0;

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_affiliate", "", "str_");
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SAE_AFF_LIST"),
				"LINK" => "/bitrix/admin/sale_affiliate.php?lang=".LANG.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SAE_AFF_ADD"),
			"LINK" => "/bitrix/admin/sale_affiliate_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SAE_AFF_DELETE"),
				"LINK" => "javascript:if(confirm('".GetMessage("SAE_AFF_DELETE_CONF")."')) window.location='/bitrix/admin/sale_affiliate.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON" => "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if($errorMessage <> '')
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SAE_ERROR_SAVE_AFF"), "HTML"=>true));?>

<script language="JavaScript">
<!--
var arSitesArray = new Array();
var arCurrenciesArray = new Array();
<?
$arBaseLangCurrencies = array();
$i = -1;
$dbSiteList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arSite = $dbSiteList->Fetch())
{
	$i++;
	?>
	arSitesArray[<?= $i ?>] = '<?= CUtil::JSEscape($arSite["LID"]) ?>';
	arCurrenciesArray[<?= $i ?>] = '<?= CUtil::JSEscape(CSaleLang::GetLangCurrency($arSite["LID"])) ?>';
	<?
}
?>
//-->
</script>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
		<tr>
			<td width="40%"><?echo GetMessage("SAE_DATE_UPDATE")?></td>
			<td width="60%"><?=$str_TIMESTAMP_X?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SAE_SITE")?></td>
		<td width="60%">
			<script language="JavaScript">
			<!--
			function OnChangeSite(val)
			{
				var currency = "";
				for (var i = 0; i < arSitesArray.length; i++)
				{
					if (arSitesArray[i] == val)
					{
						currency = arCurrenciesArray[i];
						break;
					}
				}

				document.getElementById('DIV_PAID_SUM_CURRENCY').innerHTML = currency;
				document.getElementById('DIV_PENDING_SUM_CURRENCY').innerHTML = currency;
			}
			//-->
			</script>
			<?echo CSite::SelectBox("SITE_ID", $str_SITE_ID, "", "OnChangeSite(this[this.selectedIndex].value)");?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SAE_USER")?></td>
		<td>
			<?
			$userName = "";
			if ($str_USER_ID > 0)
			{
				$dbUser = CUser::GetByID($str_USER_ID);
				if ($arUser = $dbUser->Fetch())
					$userName = "[<a class=\"tablebodylink\" title=\"".GetMessage("SAE_PROFILE")."\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$str_USER_ID."\">".$str_USER_ID."</a>] (".htmlspecialcharsex($arUser["LOGIN"]).") ".htmlspecialcharsex($arUser["NAME"])." ".htmlspecialcharsex($arUser["LAST_NAME"]);
			}

			echo FindUserID("USER_ID", $str_USER_ID, $userName, "form1");
			?>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("SAE_AFFILIATE_REG")?></td>
		<td valign="top">
			<input type="text" name="AFFILIATE_ID" value="<?= $str_AFFILIATE_ID ?>" size="10" maxlength="10">
			<IFRAME name="hiddenframe_affiliate" id="id_hiddenframe_affiliate" src="" width="0" height="0" style="width:0px; height:0px; border: 0px"></IFRAME>
			<input type="button" class="button" name="FindAffiliate" OnClick="window.open('/bitrix/admin/sale_affiliate_search.php?func_name=SetAffiliateID', '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 400)/2-5));" value="...">
			<span id="div_affiliate_name"></span>
			<SCRIPT LANGUAGE=javascript>
			<!--
			function SetAffiliateID(id)
			{
				document.form1.AFFILIATE_ID.value = id;
				BX.fireEvent(document.form1.AFFILIATE_ID, 'change');
			}

			function SetAffiliateName(val)
			{
				if (val != "NA")
					document.getElementById('div_affiliate_name').innerHTML = val;
				else
					document.getElementById('div_affiliate_name').innerHTML = '<?= GetMessage("SAE_NO_AFFILIATE") ?>';
			}

			var affiliateID = '';
			function ChangeAffiliateName()
			{
				if (affiliateID != document.form1.AFFILIATE_ID.value)
				{
					affiliateID = document.form1.AFFILIATE_ID.value;
					if (affiliateID != '' && !isNaN(parseInt(affiliateID, 10)))
					{
						document.getElementById('div_affiliate_name').innerHTML = '<i><?= GetMessage("SAE_WAIT") ?></i>';
						window.frames["hiddenframe_affiliate"].location.replace('/bitrix/admin/sale_affiliate_get.php?ID=' + affiliateID + '&func_name=SetAffiliateName');
					}
					else
						document.getElementById('div_affiliate_name').innerHTML = '';
				}
				timerID = setTimeout('ChangeAffiliateName()',2000);
			}
			ChangeAffiliateName();
			//-->
			</SCRIPT>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SAE_PLAN")?></td>
		<td>
			<select name="PLAN_ID">
				<?
				$dbPlan = CSaleAffiliatePlan::GetList(array("NAME" => "ASC"), array(), false, false, array("ID", "NAME", "SITE_ID"));
				while ($arPlan = $dbPlan->Fetch())
				{
					?><option value="<?= $arPlan["ID"] ?>"<?if ($str_PLAN_ID == $arPlan["ID"]) echo " selected"?>><?= htmlspecialcharsex("[".$arPlan["ID"]."] ".$arPlan["NAME"]." (".$arPlan["SITE_ID"].")") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_FIX_PLAN")?>:</td>
		<td>
			<input type="checkbox" name="FIX_PLAN" value="Y"<?if ($str_FIX_PLAN == "Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_ACTIVE")?></td>
		<td>
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE == "Y") echo " checked"?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SAE_DATE_REG")?>:</td>
		<td>
			<?= CalendarDate("DATE_CREATE", $str_DATE_CREATE, "form1", "20", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_PAYED_SUM")?></td>
		<td>
			<input type="text" name="PAID_SUM" size="10" maxlength="15" value="<?= $str_PAID_SUM ?>">
			<span id="DIV_PAID_SUM_CURRENCY"></span>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_PENDING_SUM")?></td>
		<td>
			<input type="text" name="PENDING_SUM" size="10" maxlength="15" value="<?= $str_PENDING_SUM ?>">
			<span id="DIV_PENDING_SUM_CURRENCY"></span>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_LAST_CALC")?>:</td>
		<td>
			<?= CalendarDate("LAST_CALCULATE", $str_LAST_CALCULATE, "form1", "20", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAE_AFF_SITE")?>:</td>
		<td>
			<input type="text" name="AFF_SITE" size="60" maxlength="200" value="<?= $str_AFF_SITE ?>">
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("SAE_AFF_DESCRIPTION")?>:</td>
		<td>
			<textarea name="AFF_DESCRIPTION" rows="3" cols="50"><?= $str_AFF_DESCRIPTION ?></textarea>
		</td>
	</tr>
	<script language="JavaScript">
	<!--
	OnChangeSite(document.form1.SITE_ID[document.form1.SITE_ID.selectedIndex].value);
	//-->
	</script>
<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
	array(
		"disabled" => ($saleModulePermissions < "W"),
		"back_url" => "/bitrix/admin/sale_affiliate_plan.php?lang=".LANG.GetFilterParams("filter_")
	)
);
?>

<?
$tabControl->End();
?>

</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>