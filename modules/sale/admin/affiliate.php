<?php

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
if ($saleModulePermissions == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
if (!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

	ShowError(GetMessage('SALE_FEATURE_NOT_ALLOW'));

	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

IncludeModuleLangFile(__FILE__);
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php';
set_time_limit(0);

$sTableID = 'tbl_sale_affiliate';

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$by = $oSort->getField();
$order = $oSort->getOrder();

$arFilterFields = array(
	"filter_site_id",
	"filter_user",
	"filter_plan_id",
	"filter_active",
	"filter_last_calculate_from",
	"filter_last_calculate_to",
	"filter_date_create_from",
	"filter_date_create_to",
	"filter_affiliate_id",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_site_id != "NOT_REF" && $filter_site_id <> '')
	$arFilter["SITE_ID"] = $filter_site_id;
else
	Unset($arFilter["SITE_ID"]);

if ($filter_user <> '')
	$arFilter["%USER_USER"] = $filter_user;
if (intval($filter_plan_id) > 0)
	$arFilter["PLAN_ID"] = intval($filter_plan_id);
if ($filter_active <> '')
	$arFilter["ACTIVE"] = (($filter_active == "Y") ? "Y" : "N");
if ($filter_last_calculate_from <> '')
	$arFilter[">=LAST_CALCULATE"] = $filter_last_calculate_from;
if ($filter_last_calculate_to <> '')
	$arFilter["<=LAST_CALCULATE"] = $filter_last_calculate_to;
if ($filter_date_create_from <> '')
	$arFilter[">=DATE_CREATE"] = $filter_date_create_from;
if ($filter_date_create_to <> '')
	$arFilter["<=DATE_CREATE"] = $filter_date_create_to;
if (intval($filter_affiliate_id) > 0)
	$arFilter["AFFILIATE_ID"] = intval($filter_affiliate_id);

$arBaseLangCurrencies = array();

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($lAdmin->GetEditFields() as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CSaleAffiliate::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SAA_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

$arID = $lAdmin->GroupAction();
if ($arID && $saleModulePermissions >= "W")
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$arID = [];
		$dbResultList = CSaleAffiliate::GetList(array(), $arFilter, false, false, array("ID"));
		while ($arResult = $dbResultList->Fetch())
		{
			$arID[] = $arResult['ID'];
		}
		unset($dbResultList);
	}

	$action = $lAdmin->GetAction();
	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($action)
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleAffiliate::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_DELETE"), $ID);
				}
				else
				{
					$DB->Commit();
				}

				break;

			case "activate":
			case "deactivate":

				$arFields = [
					'ACTIVE' => ($action === 'activate' ? 'Y' : 'N'),
				];

				if (!CSaleAffiliate::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_ACTIVATE"), $ID);
				}

				break;

			case "calculate":
				if (!CSaleAffiliate::CalculateAffiliate($ID, false, false, false, false))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_CALCULATE"), $ID);
				}

				break;

			case "pay_affiliate":
				$sum = 0;
				if (!CSaleAffiliate::PayAffiliate($ID, "P", $sum))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_PAY"), $ID);
				}

				break;

			case "pay_affiliate_account":
				$sum = 0;
				if (!CSaleAffiliate::PayAffiliate($ID, "U", $sum))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_PAY"), $ID);
				}

				break;

			case "affiliate_0":
				if (!CSaleAffiliate::ClearAffiliateSum($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAA_ERROR_CLEAR"), $ID);
				}

				break;
		}
	}
}

$dbResultList = CSaleAffiliate::GetList(
	[$by => $order],
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
	array("ID", "SITE_ID", "USER_ID", "AFFILIATE_ID", "PLAN_ID", "ACTIVE", "TIMESTAMP_X", "DATE_CREATE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "ITEMS_NUMBER", "ITEMS_SUM", "LAST_CALCULATE", "FIX_PLAN", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAA_AFFILIATES")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("SAA_SITE"), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"USER_ID", "content" => GetMessage("SAA_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"PLAN_ID", "content"=>GetMessage("SAA_PLAN"), "sort"=>"PLAN_ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SAA_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("SAA_DATE_CREATE"), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"PAID_SUM", "content"=>GetMessage("SAA_PAYED_SUM"), "sort"=>"PAID_SUM", "default"=>true),
	array("id"=>"PENDING_SUM", "content"=>GetMessage("SAA_PENDING_SUM"), "sort"=>"PENDING_SUM", "default"=>true),
	array("id"=>"LAST_CALCULATE", "content"=>GetMessage("SAA_LAST_CALCULATE"), "sort"=>"LAST_CALCULATE", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSites = array();
$dbSiteList = CSite::GetList();
while ($arSite = $dbSiteList->Fetch())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."]&nbsp;".$arSite["NAME"];

$arPlans = array();
$dbPlanList = CSaleAffiliatePlan::GetList(array("NAME" => "ASC"), array(), false, false, array("ID", "NAME"));
while ($arPlan = $dbPlanList->Fetch())
	$arPlans[$arPlan["ID"]] = "[".$arPlan["ID"]."]&nbsp;".$arPlan["NAME"];

while ($arAffiliate = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arAffiliate, "sale_affiliate_edit.php?ID=".$f_ID."&lang=" . LANGUAGE_ID . GetFilterParams("filter_"), GetMessage("SAA_UPDATE_AFFILIATE"));

	$row->AddField("ID", $f_ID);
	$row->AddSelectField("SITE_ID", $arSites, array());

	$fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=" . LANGUAGE_ID . "\" title=\"".GetMessage("SAA_GOTO_USER")."\">".$f_USER_ID."</a>] ";
	$fieldValue .= $f_USER_NAME.(($f_USER_NAME == '' || $f_USER_LAST_NAME == '') ? "" : " ").$f_USER_LAST_NAME."<br>";
	$fieldValue .= $f_USER_LOGIN."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".$f_USER_EMAIL."\" title=\"".GetMessage("SAA_USER_EMAIL")."\">".$f_USER_EMAIL."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddSelectField("PLAN_ID", $arPlans, array());
	$row->AddCheckField("ACTIVE");
	$row->AddCalendarField("DATE_CREATE", array("size" => "10"));

	if (!array_key_exists($f_SITE_ID, $arBaseLangCurrencies))
		$arBaseLangCurrencies[$f_SITE_ID] = CSaleLang::GetLangCurrency($f_SITE_ID);

	$fieldValue = SaleFormatCurrency($f_PAID_SUM, $arBaseLangCurrencies[$f_SITE_ID]);

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_ID]["PAID_SUM"];
	else
		$val = $f_PAID_SUM;

	$fieldEdit = "<input type=\"text\" name=\"FIELDS[".$f_ID."][PAID_SUM]\" value=\"".htmlspecialcharsbx($val)."\" size=\"7\">";

	$row->AddField("PAID_SUM", $fieldValue, $fieldEdit);

	$fieldValue = SaleFormatCurrency($f_PENDING_SUM, $arBaseLangCurrencies[$f_SITE_ID]);

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_ID]["PENDING_SUM"];
	else
		$val = $f_PENDING_SUM;

	$fieldEdit = "<input type=\"text\" name=\"FIELDS[".$f_ID."][PENDING_SUM]\" value=\"".htmlspecialcharsbx($val)."\" size=\"7\">";

	$row->AddField("PENDING_SUM", $fieldValue, $fieldEdit);

	$row->AddCalendarField("LAST_CALCULATE", array("size" => "10"));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAA_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("sale_affiliate_edit.php?ID=".$f_ID."&lang=" . LANGUAGE_ID . GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAA_DELETE"), "ACTION"=>"if(confirm('".GetMessage("SAA_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter([
	[
		'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
		'value' => $dbResultList->SelectedRowsCount(),
	],
	[
		'counter' => true,
		'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'),
		'value' => '0',
	],
]);

$lAdmin->AddGroupActionTable([
	'delete' => GetMessage('MAIN_ADMIN_LIST_DELETE'),
	'calculate' => GetMessage('SAA_CALCULATE_AFF'),
	'calculate_ex' => [
		'action' => 'exportData()',
		'value' => 'calculate_ex',
		'name' => GetMessage('SAA_CALCULATE_AFF_EXT'),
	],
	'pay_affiliate' => GetMessage('SAA_PAY_AFF_EXT'),
	'pay_affiliate_account' => GetMessage('SAA_INNER_PAY_AFF_EXT'),
	'affiliate_0' => GetMessage('SAA_0_SUM'),
	'activate' => GetMessage('MAIN_ADMIN_LIST_ACTIVATE'),
	'deactivate' => GetMessage('MAIN_ADMIN_LIST_DEACTIVATE'),
]);

if ($saleModulePermissions >= "W")
{
	$aContext = [
		[
			'TEXT' => GetMessage('SAA_ADD_AFFILIATE'),
			'LINK' => 'sale_affiliate_edit.php?lang=' . LANGUAGE_ID,
			'TITLE' => GetMessage('SAA_ADD_AFFILIATE_DESCR'),
			'ICON' => 'btn_new',
		],
	];
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage('SAA_AFFILIATES'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<script>
function exportData()
{
	var oForm = document.form_<?= $sTableID ?>;
	var expType = oForm.action_target.checked;

	var par = "";
	if (!expType)
	{
		var num = oForm.elements.length;
		for (var i = 0; i < num; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				if (par.length > 0)
					par = par + "&";

				par = par + "OID[]=" + oForm.elements[i].value;
			}
		}
	}

	if (expType)
	{
		par = "<?= GetFilterParams("filter_") ?>";
	}

	if (par.length > 0)
	{
		window.open("sale_affiliate_calc.php?"+par, "vvvvv");
	}
}
</script>

<form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage(); ?>?">
<?php
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	[
		'filter_site_id' => GetMessage('SAA_SITE1'),
		'filter_user' => GetMessage('SAA_USER'),
		'filter_plan_id' => GetMessage('SAA_PLAN'),
		'filter_active' => GetMessage('SAA_ACTIVE'),
		'filter_last_calculate_from' => GetMessage('SAA_LAST_CALCULATE'),
		'filter_date_create_from' => GetMessage('SAA_REG_DATE'),
		'filter_affiliate_id' => GetMessage('SAA_PAR_AFFILIATE'),
	]
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("SAA_SITE1")?></td>
		<td><?= CSite::SelectBox("filter_site_id", $filter_site_id, GetMessage("SAA_ALL")) ?></td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_USER1"); ?></td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?= ShowFilterLogicHelp(); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_PLAN1"); ?></td>
		<td>
			<select name="filter_plan_id">
				<option value=""><?= htmlspecialcharsex(GetMessage("SAA_ALL")); ?></option>
				<?
				$dbPlan = CSaleAffiliatePlan::GetList(array("NAME" => "ASC"), array(), false, false, array("ID", "NAME", "SITE_ID"));
				while ($arPlan = $dbPlan->Fetch())
				{
					?><option value="<?= $arPlan["ID"] ?>"<?if ($filter_plan_id == $arPlan["ID"]) echo " selected"?>><?= htmlspecialcharsex("[".$arPlan["ID"]."] ".$arPlan["NAME"]." (".$arPlan["SITE_ID"].")") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_ACTIVE1"); ?></td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex(GetMessage("SAA_ALL")); ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAA_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAA_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_LAST_CALCULATE1"); ?></td>
		<td>
			<?= CalendarPeriod("filter_last_calculate_from", $filter_last_calculate_from, "filter_last_calculate_to", $filter_last_calculate_to, "find_form", "Y"); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_REG_DATE1"); ?></td>
		<td>
			<?= CalendarPeriod("filter_date_create_from", $filter_date_create_from, "filter_date_create_to", $filter_date_create_to, "find_form", "Y"); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAA_PAR_AFFILIATE"); ?>:</td>
		<td>
			<input type="text" name="filter_affiliate_id" value="<?= intval($filter_affiliate_id); ?>" size="10" maxlength="10">
			<iframe name="hiddenframe_affiliate" id="id_hiddenframe_affiliate" src="" width="0" height="0" style="width:0px; height:0px; border: 0px"></iframe>
			<input type="button" class="button" name="FindAffiliate" OnClick="window.open('/bitrix/admin/sale_affiliate_search.php?func_name=SetAffiliateID', '', 'scrollbars=yes,resizable=yes,width=800,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 400)/2-5));" value="...">
			<span id="div_affiliate_name"></span>
			<script>
			function SetAffiliateID(id)
			{
				document.find_form.filter_affiliate_id.value = id;
			}

			function SetAffiliateName(val)
			{
				if (val != "NA")
					document.getElementById('div_affiliate_name').innerHTML = val;
				else
					document.getElementById('div_affiliate_name').innerHTML = '<?= GetMessage("SAA_NO_AFFILIATE") ?>';
			}

			var affiliateID = '';
			function ChangeAffiliateName()
			{
				if (affiliateID != document.find_form.filter_affiliate_id.value)
				{
					affiliateID = document.find_form.filter_affiliate_id.value;
					if (affiliateID != '' && !isNaN(parseInt(affiliateID, 10)))
					{
						document.getElementById('div_affiliate_name').innerHTML = '<i><?= GetMessage("SAA_WAIT") ?></i>';
						window.frames["hiddenframe_affiliate"].location.replace('/bitrix/admin/sale_affiliate_get.php?ID=' + affiliateID + '&func_name=SetAffiliateName');
					}
					else
						document.getElementById('div_affiliate_name').innerHTML = '';
				}
				timerID = setTimeout('ChangeAffiliateName()',2000);
			}
			ChangeAffiliateName();
			</script>
		</td>
	</tr>
	<?php
	$oFilter->Buttons([
		'table_id' => $sTableID,
		'url' => $APPLICATION->GetCurPage(),
		'form' => 'find_form',
	]);
	$oFilter->End();
	?>
</form>
<?php
$lAdmin->DisplayList();

echo BeginNote();
?>
<b><?= GetMessage('SAA_NOTE_NOTE1'); ?></b><br>
<i><?= GetMessage('SAA_CALCULATE_AFF'); ?></i> <?= GetMessage('SAA_NOTE_NOTE2'); ?><br>
<i><?= GetMessage('SAA_NOTE_NOTE3'); ?></i> <?= GetMessage('SAA_NOTE_NOTE4'); ?><br>
<i><?= GetMessage('SAA_PAY_AFF_EXT')?></i> <?= GetMessage('SAA_NOTE_NOTE5'); ?><br>
<i><?= GetMessage('SAA_INNER_PAY_AFF_EXT')?></i> <?= GetMessage('SAA_NOTE_NOTE6'); ?><br>
<?php
echo EndNote();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
