<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/advertising/prolog.php");
Loader::includeModule('advertising');

ClearVars();

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if (!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$err_mess = "FILE: " . __FILE__ . "<br>LINE: ";

$aTabs = [
	[
		"DIV" => "edit1",
		"TAB" => GetMessage("AD_TAB_CONTRACT"),
		"ICON" => "ad_contract_edit",
		"TITLE" => GetMessage("AD_TAB_TITLE_CONTRACT"),
	],
	[
		"DIV" => "edit2",
		"TAB" => GetMessage("AD_TAB_LIMIT"),
		"ICON" => "ad_contract_edit",
		"TITLE" => GetMessage("AD_CONTRACT_LIMITS"),
	],
	[
		"DIV" => "edit3",
		"TAB" => GetMessage("AD_TAB_TARG"),
		"ICON" => "ad_contract_edit",
		"TITLE" => GetMessage("AD_TAB_TITLE_TARG"),
	],
	[
		"DIV" => "edit4",
		"TAB" => GetMessage("AD_TAB_ACCESS"),
		"ICON" => "ad_contract_edit",
		"TITLE" => GetMessage("AD_OWNER_PERMISSIONS"),
	],
];
if ($isAdmin || ($isDemo && !$isOwner))
	$aTabs[] = [
		"DIV" => "edit5",
		"TAB" => GetMessage("AD_TAB_COMMENT"),
		"ICON" => "ad_contract_edit",
		"TITLE" => GetMessage("AD_ADMIN_COMMENTS"),
	];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
/***************************************************************************
 * Обработка GET | POST
 ***************************************************************************/

$strError = '';
$ID = intval($ID);
$arrPERM = CAdvContract::GetUserPermissions($ID);
$arrPERM = is_array($arrPERM[$ID]) ? $arrPERM[$ID] : [];

$isOwner = CAdvContract::IsOwner($ID);

$isEditMode = false;
$isEditRightsMode = false;
if (!$isDemo)
{
	if (count($arrPERM) <= 0)
		$APPLICATION->AuthForm(GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_CONTRACT"));
}
else
{
	if (!$isOwner)
		$isEditMode = true;
}

if ($isAdmin)
	$isEditMode = true;
if (in_array("EDIT", $arrPERM))
	$isEditRightsMode = true;

if ($action == "view")
{
	$isEditMode = false;
	$isEditRightsMode = false;
}

if (($save <> '' || $apply <> '') && check_bitrix_sessid() && $REQUEST_METHOD == "POST")
{
	$arrWEEKDAY = [
		"SUNDAY" => $arrSUNDAY,
		"MONDAY" => $arrMONDAY,
		"TUESDAY" => $arrTUESDAY,
		"WEDNESDAY" => $arrWEDNESDAY,
		"THURSDAY" => $arrTHURSDAY,
		"FRIDAY" => $arrFRIDAY,
		"SATURDAY" => $arrSATURDAY,
	];
	$arFields = [
		"ACTIVE" => ($ACTIVE == "Y" ? "Y" : "N"),
		"NAME" => $NAME,
		"DESCRIPTION" => $DESCRIPTION,
		"KEYWORDS" => $KEYWORDS,
		"ADMIN_COMMENTS" => $ADMIN_COMMENTS,
		"WEIGHT" => $WEIGHT,
		"SORT" => $SORT,
		"MAX_SHOW_COUNT" => $MAX_SHOW_COUNT,
		"MAX_VISITOR_COUNT" => $MAX_VISITOR_COUNT,
		"MAX_CLICK_COUNT" => $MAX_CLICK_COUNT,
		"DATE_SHOW_FROM" => $DATE_SHOW_FROM,
		"DATE_SHOW_TO" => $DATE_SHOW_TO,
		"DEFAULT_STATUS_SID" => $DEFAULT_STATUS_SID,
		"arrSHOW_PAGE" => preg_split('/[\n\r]+/', $SHOW_PAGE),
		"arrNOT_SHOW_PAGE" => preg_split('/[\n\r]+/', $NOT_SHOW_PAGE),
		"arrTYPE" => $arrTYPE,
		"arrWEEKDAY" => $arrWEEKDAY,
		"arrUSER_VIEW" => $arrUSER_VIEW,
		"arrUSER_ADD" => $arrUSER_ADD,
		"arrUSER_EDIT" => $arrUSER_EDIT,
		"arrSITE" => $arrSITE,
	];

	if ($ID = CAdvContract::Set($arFields, $ID))
	{
		if ($strError == '')
		{
			if ($save <> '')
				LocalRedirect("adv_contract_list.php?lang=" . LANGUAGE_ID);
			else
				LocalRedirect("adv_contract_edit.php?ID=" . $ID . "&lang=" . LANGUAGE_ID . "&" . $tabControl->ActiveTabParam());
		}
	}
	$DB->PrepareFields("b_adv_contract");
}

$arrSites = [];
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$arrSites[$ar["ID"]] = $ar;
}

$rsContract = CAdvContract::GetByID($ID);
$arrKEYWORDS = null;
if (!$rsContract || !$rsContract->ExtractFields())
{
	if (!$isAdmin && !$isDemo)
	{
		require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
		echo CAdminMessage::ShowError(GetMessage("AD_ERROR_INCORRECT_CONTRACT_ID"));
		require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	$ID = 0;
	$str_WEIGHT = 100;
	$str_ACTIVE = "Y";
	$str_SORT = CAdvContract::GetNextSort();
	$str_DATE_SHOW_FROM = GetTime(time());
	$str_DEFAULT_STATUS_SID = "READY";
	$arrSITE = array_keys($arrSites);
}
else
{
	if ($strError == '')
	{
		if ($str_KEYWORDS <> '')
		{
			$arrKEYWORDS = preg_split('/[\n\r]+/', $str_KEYWORDS);
			TrimArr($arrKEYWORDS);
		}
		$arrSITE = CAdvContract::GetSiteArray($ID);
		$arrSHOW_PAGE = CAdvContract::GetPageArray($ID, "SHOW");
		$str_SHOW_PAGE = implode("\n", $arrSHOW_PAGE);
		$arrNOT_SHOW_PAGE = CAdvContract::GetPageArray($ID, "NOT_SHOW");
		$str_NOT_SHOW_PAGE = implode("\n", $arrNOT_SHOW_PAGE);

		$arContractTypes = CAdvContract::GetTypeArray($ID);
		$arrTYPE = array_keys($arContractTypes);

		$arrWEEKDAY = CAdvContract::GetWeekdayArray($ID);
		foreach ($arrWEEKDAY as $key => $value)
		{
			${"arr" . $key} = $value;
		}
		$arrP = CAdvContract::GetContractPermissions($ID);
		if (is_array($arrP))
		{
			foreach ($arrP as $key => $arr)
			{
				foreach ($arr as $ar)
				{
					${"arrUSER_" . $key}[] = $ar["USER_ID"];
				}
			}
		}
	}
}

if ($strError <> '')
{
	$DB->InitTableVarsForEdit("b_adv_contract", "", "str_");
	$str_SHOW_PAGE = $SHOW_PAGE;
	$str_NOT_SHOW_PAGE = $NOT_SHOW_PAGE;
}
$str_SHOW_PAGE = htmlspecialcharsbx($str_SHOW_PAGE);
$str_NOT_SHOW_PAGE = htmlspecialcharsbx($str_NOT_SHOW_PAGE);

$sDocTitle = ($ID > 0) ? GetMessage("AD_EDIT_RECORD", ["#ID#" => $ID]) : GetMessage("AD_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

/***************************************************************************
 * HTML форма
 ****************************************************************************/
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
	[
		"TEXT" => GetMessage("AD_BACK_TO_CONTRACT_LIST"),
		"TITLE" => GetMessage("AD_BACK_TO_CONTRACT_LIST_TITLE"),
		"LINK" => "adv_contract_list.php?lang=" . LANGUAGE_ID,
		"ICON" => "btn_list",
	],
];
if (intval($ID) > 0)
{

	$aMenu[] = [
		"TEXT" => GetMessage("AD_CONTRACT_STATISTICS"),
		"TITLE" => GetMessage("AD_CONTRACT_STATISTICS_TITLE"),
		"LINK" => "adv_contract_graph.php?find_contract_id[]=" . $ID . "&find_what_show[]=ctr&set_filter=Y&lang=" . LANGUAGE_ID,
		"ICON" => "btn_adv_graph",
	];

	$arMenuActions = [];

	if (in_array("EDIT", $arrPERM))
	{
		if ($action != "view")
		{
			$arMenuActions[] = [
				"TEXT" => GetMessage("AD_CONTRACT_VIEW_SETTINGS"),
				"TITLE" => GetMessage("AD_CONTRACT_VIEW_SETTINGS_TITLE"),
				"LINK" => "adv_contract_edit.php?ID=" . $ID . "&lang=" . LANGUAGE_ID . "&action=view",
			];
		}
		else
		{
			$arMenuActions[] = [
				"TEXT" => GetMessage("AD_CONTRACT_EDIT"),
				"TITLE" => GetMessage("AD_CONTRACT_EDIT_TITLE"),
				"LINK" => "adv_contract_edit.php?ID=" . $ID . "&lang=" . LANGUAGE_ID,
			];
		}
	}

	if ($isAdmin || $isDemo)
	{
		$arMenuActions[] = [
			"TEXT" => GetMessage("AD_ADD_NEW_CONTRACT"),
			"TITLE" => GetMessage("AD_ADD_NEW_CONTRACT_TITLE"),
			"LINK" => "adv_contract_edit.php?lang=" . LANGUAGE_ID,
		];
		if ($ID > 1)
		{
			$arMenuActions[] = [
				"TEXT" => GetMessage("AD_DELETE_CONTRACT"),
				"TITLE" => GetMessage("AD_DELETE_CONTRACT_TITLE"),
				"LINK" => "javascript:if(confirm('" . GetMessage("AD_DELETE_CONTRACT_CONFIRM") . "'))window.location='adv_contract_list.php?ID=" . $ID . "&lang=" . LANGUAGE_ID . "&action=delete&sessid=" . bitrix_sessid() . "';",
			];
		}
	}

	if (count($arMenuActions) > 0)
	{
		$aMenu[] = [
			"TEXT" => GetMessage("AD_ACTIONS"),
			"TITLE" => GetMessage("AD_ACTIONS"),
			"MENU" => $arMenuActions,
		];
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?= CAdminMessage::ShowMessage($strError) ?>
<form name="form1" method="POST" action="<? echo $APPLICATION->GetCurPage() ?>">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="ID" value="<?= $str_ID ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<?
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	?>
	<?
	if ($ID > 0) :
		$lamp_alt = GetMessage("AD_" . mb_strtoupper($str_LAMP) . "_ALT");
		$lamp = '<div class="lamp-' . $str_LAMP . '" title="' . $lamp_alt . '" style="float:left;"></div>';
		?>
		<tr>
			<td width="40%"><?= GetMessage("AD_STATUS") ?></td>
			<td width="60%"><?= $lamp ?>&nbsp;(<?= $lamp_alt ?>)</td>
		</tr>
	<? endif; ?>

	<? if ($ID > 0): ?>
		<? if ($str_DATE_CREATE <> ''): ?>
			<tr>
				<td width="40%"><?= GetMessage("AD_CREATED") ?></td>
				<td width="60%"><?= $str_DATE_CREATE ?><?
					if (intval($str_CREATED_BY) > 0) :
						$rsUser = CUser::GetByID($str_CREATED_BY);
						$arUser = $rsUser->Fetch();
						echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=" . $str_CREATED_BY . "&lang=" . LANGUAGE_ID . "' title='" . GetMessage("AD_USER_ALT") . "'>" . $str_CREATED_BY . "</a>]&nbsp;(" . htmlspecialcharsbx($arUser["LOGIN"]) . ") " . htmlspecialcharsbx($arUser["NAME"]) . " " . htmlspecialcharsbx($arUser["LAST_NAME"]);
					endif;
					?></td>
			</tr>
		<? endif; ?>
		<? if ($str_DATE_MODIFY <> ''): ?>
			<tr>
				<td><?= GetMessage("AD_MODIFIED") ?></td>
				<td><?= $str_DATE_MODIFY ?><?
					if (intval($str_MODIFIED_BY) > 0) :
						$rsUser = CUser::GetByID($str_MODIFIED_BY);
						$arUser = $rsUser->Fetch();
						echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=" . $str_MODIFIED_BY . "&lang=" . LANGUAGE_ID . "' title='" . GetMessage("AD_USER_ALT") . "'>" . $str_MODIFIED_BY . "</a>]&nbsp;(" . htmlspecialcharsbx($arUser["LOGIN"]) . ") " . htmlspecialcharsbx($arUser["NAME"]) . " " . htmlspecialcharsbx($arUser["LAST_NAME"]);
					endif;
					?></td>
			</tr>
		<? endif; ?>
	<? endif; ?>

	<tr>
		<td width="40%"><label for="active"><?= GetMessage("AD_ACTIVE") ?></label></td>
		<td width="60%"><?
			if ($isEditMode && ($isAdmin || ($isDemo && !$isOwner))) :
				echo InputType("checkbox", "ACTIVE", "Y", $str_ACTIVE, false, "", 'id="active"');
			else:
				?><? echo($str_ACTIVE == "Y" ? GetMessage("AD_SET") : GetMessage("AD_NOT_SET")) ?><?
			endif;
			?></td>
	</tr>
	<? if (!$isEditMode): ?>
		<tr>
			<td><?= GetMessage("AD_SHOW_INTERVAL") . ":" ?></td>
			<td><?
				if ($str_DATE_SHOW_FROM <> '') :
					echo GetMessage("AD_FROM") ?>&nbsp;<b><?= $str_DATE_SHOW_FROM ?></b>&nbsp;<?
				endif;
				if ($str_DATE_SHOW_TO <> '') :
					echo GetMessage("AD_TILL") ?>&nbsp;<b><?= $str_DATE_SHOW_TO ?></b><?
				endif;
				?></td>
		</tr>
	<? else: ?>
		<tr>
			<td><?= GetMessage("AD_SHOW_INTERVAL") . ":" ?></td>
			<td><?
				echo CalendarPeriod("DATE_SHOW_FROM", htmlspecialcharsbx($str_DATE_SHOW_FROM), "DATE_SHOW_TO", htmlspecialcharsbx($str_DATE_SHOW_TO), "form1", "N", "", "", 20); ?></td>
		</tr>
	<? endif; ?>

	<? if ($isAdmin || ($isDemo && !$isOwner)) : ?>
		<tr>
			<td><?= GetMessage("AD_SORT") ?></td>
			<td><?
				if (!$isEditMode):
					?><?= $str_SORT ?><?
				else :
					?><input type="text" name="SORT" size="5" value="<?= $str_SORT ?>"><?
				endif;
				?></td>
		</tr>
	<? endif; ?>

	<? if ($isAdmin || ($isDemo && !$isOwner)) : ?>
		<tr>
			<td><?= GetMessage("AD_WEIGHT") ?></td>
			<td><?
				if (!$isEditMode):
					?><?= $str_WEIGHT ?><?
				else :
					?><input type="text" name="WEIGHT" size="5" value="<?= $str_WEIGHT ?>"><?
				endif;
				?></td>
		</tr>
	<? endif; ?>

	<tr>
		<td width="40%"><?= GetMessage("AD_TITLE") ?></td>
		<td width="60%"><?
			if (($isAdmin || ($isDemo && !$isOwner)) && $isEditMode) :
				?><input type="text" name="NAME" size="40" value="<?= $str_NAME ?>"><?
			else :
				?><?= $str_NAME ?><?
			endif;
			?></td>
	</tr>

	<tr valign="top">
		<td><?= GetMessage("AD_DESCRIPTION") ?></td>
		<td><?
			if (($isAdmin || ($isDemo && !$isOwner)) && $isEditMode) :
				?><textarea cols="45" name="DESCRIPTION" rows="8"><?= $str_DESCRIPTION ?></textarea><?
			else :
				?><?= TxtToHtml($str_DESCRIPTION) ?><?
			endif;
			?></td>
	</tr>

	<?
	$arrStatus = CAdvBanner::GetStatusList();
	if ($isEditMode && ($isAdmin || ($isDemo && !$isOwner))) :
		?>
		<tr>
			<td><?= GetMessage("AD_DEFAULT_STATUS") ?></td>
			<td><?= SelectBoxFromArray("DEFAULT_STATUS_SID", $arrStatus, $str_DEFAULT_STATUS_SID, " "); ?></td>
		</tr>
	<? elseif ($ID > 0) : ?>
		<tr>
			<td><?= GetMessage("AD_DEFAULT_STATUS") ?></td>
			<td><?
				$key = array_search($str_DEFAULT_STATUS_SID, $arrStatus["reference_id"]);
				echo $arrStatus["reference"][$key];
				?></td>
		</tr>
	<? endif; ?>

	<? if ($ID > 0): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_BANNER_COUNT") ?></td>
			<td>
				<table cellspacing=1 cellpadding=0 border=0>
					<?
					foreach ($arrStatus["reference_id"] as $key => $status_sid) :
						$count = 0;
						$arFilter = ["CONTRACT_ID" => $ID, "CONTRACT_EXACT_MATCH" => "Y", "STATUS_SID" => $status_sid];
						if ($rsBanners = CAdvBanner::GetList('', '', $arFilter))
						{
							$rsBanners->NavStart();
							$count = $rsBanners->SelectedRowsCount();
						}
						?>
						<tr>
							<td width="30%"><?= $arrStatus["reference"][$key] ?>:&nbsp;</td>
							<td>
								<a href="/bitrix/admin/adv_banner_list.php?find_contract_id[]=<? echo $ID ?>&find_status_sid[]=<? echo $status_sid ?>&set_filter=Y&lang=<?= LANGUAGE_ID ?>"
									title='<?= GetMessage("AD_BANNER_ALT") ?>'><?= $count ?></a></td>
						</tr>
					<? endforeach; ?>
					<tr>
						<td><b><?= GetMessage("AD_TOTAL") ?>&nbsp;</b></td>
						<td>
							<a href="/bitrix/admin/adv_banner_list.php?find_contract_id[]=<? echo $ID ?>&set_filter=Y&lang=<?= LANGUAGE_ID ?>"
								title='<?= GetMessage("AD_BANNER_ALT") ?>'><? echo $str_BANNER_COUNT ?></a></td>
					</tr>
				</table>
			</td>
		</tr>
	<? endif; ?>

	<? $tabControl->BeginNextTab(); ?>

	<tr valign="top">
		<td width="40%"><?= GetMessage("AD_SITE") ?></td>
		<td width="60%"><?
			if ($isEditMode) :?>
				<div class="adm-list">
					<?
					foreach ($arrSites as $sid => $arrS):
						$checked = (in_array($sid, $arrSITE)) ? "checked" : "";
						/*<?=$disabled?>*/
						?>
						<div class="adm-list-item">
							<div class="adm-list-control"><input type="checkbox"
									name="arrSITE[]"
									value="<?= htmlspecialcharsbx($sid) ?>"
									id="site_<?= htmlspecialcharsbx($sid) ?>" <?= $checked ?>></div>
							<div class="adm-list-label"><? echo '[<a href="/bitrix/admin/site_edit.php?LID=' . urlencode($sid) . '&lang=' . LANGUAGE_ID . '" title="' . GetMessage("AD_SITE_ALT") . '">' . htmlspecialcharsex($sid) . '</a>]&nbsp;<label for="site_' . htmlspecialcharsbx($sid) . '">' . htmlspecialcharsex($arrS["NAME"]) ?></label></div>
						</div>
					<? endforeach; ?>
				</div>
			<?
			else:

				reset($arrSITE);
				if (is_array($arrSITE)):
					foreach ($arrSITE as $sid):
						$ar = $arrSites[$sid];
						echo htmlspecialcharsex($ar["NAME"]) . "<br>";
					endforeach;
				endif;

			endif;
			?></td>
	</tr>

	<? if ($isEditMode): ?>
		<SCRIPT>
			<!--
			function OnSelectAll_typies(all_checked) {
				for (i = 0; i < document.getElementById('count_type').value; i++) {
					name = 'arType_' + i;
					document.getElementById(name).disabled = all_checked;
				}
			}

			//-->
		</SCRIPT>
		<tr valign="top">
			<td width="40%"><?= GetMessage("AD_ADV_TYPE") ?></td>
			<td width="60%">
				<div class="adm-list">
					<div class="adm-list-item">
						<div class="adm-list-control"><input name="arrTYPE[]"
								type="checkbox"
								value="ALL"
								onclick="OnSelectAll_typies(this.checked)" <? if ($ID > 0 && in_array("ALL", $arrTYPE) || $ID <= 0)
								echo 'checked="checked"' ?>
								id="alltypies"></div>
						<div class="adm-list-label"><label for="alltypies"><?= GetMessage("AD_ALL_TYPIES") ?></label>
						</div>
					</div>

					<?
					$rsType = CAdvType::GetList("s_sort", "asc", ["ACTIVE" => "Y"]);
					$i = 0;
					while ($arType = $rsType->GetNext())
					{
						?>
						<div class="adm-list-item">
							<div class="adm-list-control">
								<input <? if ($ID > 0 && in_array($arType["SID"], $arrTYPE))
									echo "checked" ?>
									type="checkbox"
									name="arrTYPE[]"
									value="<?= $arType["SID"] ?>"
									id="arType_<?= $i ?>">
							</div>
							<div class="adm-list-label">
								<?= "[<a href='/bitrix/admin/adv_type_edit.php?lang=" . LANGUAGE_ID . "&SID=" . $arType["SID"] . "&action=view' title='" . GetMessage("AD_TYPE_ALT") . "'>" . $arType["SID"] . "</a>] " . "<label for='arType_" . $i . "'>" . $arType["NAME"] . "</label>" ?>
							</div>
						</div>
						<?
						$i++;
					}
					?></div>
				<input type="hidden" name="count_type" id="count_type" value="<?= $i ?>"></td>
		</tr>
		<SCRIPT>
			<!--
			OnSelectAll_typies(<?echo (($ID > 0 && in_array("ALL", $arrTYPE)) || $ID <= 0) ? "true" : "false"?>);
			//-->
		</SCRIPT>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_ADV_TYPE") ?></td>
			<td><?
				if ($ID > 0 && in_array("ALL", $arrTYPE) || $ID <= 0)
				{
					echo GetMessage("AD_ALL_TYPIES") . "<br>";
				}

				$arContractTypes = CAdvContract::GetTypeArray($ID);
				foreach ($arContractTypes as $sid => $name):
					if ($sid == "ALL")
						continue;
					?>
					[<a href="/bitrix/admin/adv_type_edit.php?lang=<?= LANGUAGE_ID ?>&SID=<?= $sid ?>&action=view"
					title="<?= GetMessage("AD_TYPE_ALT") ?>"><?= $sid ?></a>]
					<?= HtmlFilter::encode($name) ?><br>
				<? endforeach ?>
			</td>
		</tr>
	<? endif; ?>

	<? if (!$isEditMode): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_VISITORS") ?></td>
			<td><b><?= intval($str_VISITOR_COUNT) ?></b>&nbsp;/&nbsp;<?= $str_MAX_VISITOR_COUNT ?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_MAX_VISITOR_COUNT") ?></td>
			<td><input type="text"
					name="MAX_VISITOR_COUNT"
					size="8"
					value="<?= $str_MAX_VISITOR_COUNT ?>"><? if ($ID > 0): ?>&nbsp;<?= GetMessage("AD_VISITORS_2") ?>&nbsp;<?= intval($str_VISITOR_COUNT) ?><? endif; ?>
			</td>
		</tr>
	<? endif; ?>

	<? if (!$isEditMode): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_SHOWN") ?></td>
			<td><b><?= intval($str_SHOW_COUNT) ?></b>&nbsp;/&nbsp;<?= $str_MAX_SHOW_COUNT ?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_MAX_SHOW_COUNT") ?></td>
			<td><input type="text"
					name="MAX_SHOW_COUNT"
					size="8"
					value="<?= $str_MAX_SHOW_COUNT ?>"><? if ($ID > 0): ?>&nbsp;<?= GetMessage("AD_SHOW_COUNT") ?>&nbsp;<?= intval($str_SHOW_COUNT) ?><? endif; ?>
			</td>
		</tr>
	<? endif; ?>

	<? if (!$isEditMode): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_CLICKS") ?></td>
			<td><b><?= intval($str_CLICK_COUNT) ?></b>&nbsp;/&nbsp;<?= $str_MAX_CLICK_COUNT ?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_MAX_CLICK_COUNT") ?></td>
			<td><input type="text"
					name="MAX_CLICK_COUNT"
					size="8"
					value="<?= $str_MAX_CLICK_COUNT ?>"><? if ($ID > 0): ?>&nbsp;<?= GetMessage("AD_CLICKED") ?>&nbsp;<?= intval($str_CLICK_COUNT) ?><? endif; ?>
			</td>
		</tr>
	<? endif; ?>

	<? if ($ID > 0): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_CTR") ?></td>
			<td><b><?= $str_CTR ?></b></td>
		</tr>
	<? endif; ?>
	<tr valign="top">
		<td><?= GetMessage("AD_WEEKDAY"); ?></td>
		<td>
			<SCRIPT>
				<!--
				function OnSelectAll(all_checked, name, vert) {
					if (vert) {
						for (i = 0; i <= 23; i++) {
							name1 = "arr" + name + "_" + i + "[]";
							document.getElementById(name1).checked = all_checked;
						}
					}
					else {
						ar = Array("MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY", "SUNDAY");
						for (i = 0; i < 7; i++) {
							name2 = ar[i];
							name1 = "arr" + name2 + "_" + name + "[]";
							document.getElementById(name1).checked = all_checked;
						}

					}
				}

				//-->
			</SCRIPT>
			<table cellspacing="6" cellpadding="0" border="0">
				<tr>
					<td>&nbsp;</td>
					<?
					$disabled = (!$isEditMode) ? "disabled" : "";
					$arrWDAY = [
						"MONDAY" => GetMessage("AD_MONDAY"),
						"TUESDAY" => GetMessage("AD_TUESDAY"),
						"WEDNESDAY" => GetMessage("AD_WEDNESDAY"),
						"THURSDAY" => GetMessage("AD_THURSDAY"),
						"FRIDAY" => GetMessage("AD_FRIDAY"),
						"SATURDAY" => GetMessage("AD_SATURDAY"),
						"SUNDAY" => GetMessage("AD_SUNDAY"),
					];
					foreach ($arrWDAY as $key => $value):
						?>
						<td><label for="<?= $key ?>"><?= $value ?></label><br><input <?= $disabled ?> type="checkbox"
								onclick="OnSelectAll(this.checked, '<?= $key ?>', true)"
								id="<?= $key ?>"></td>
					<?
					endforeach;
					?>
					<td>&nbsp;</td>
				</tr>
				<?
				$arrCONTRACT_WEEKDAY = CAdvContract::GetWeekdayArray($arContract["ID"]);
				for ($i = 0; $i <= 23; $i++):
					?>
					<tr>
						<td><label for="<?= $i ?>"><? echo $i . "&nbsp;-&nbsp;" . ($i + 1) ?></label></td>
						<?
						foreach ($arrWDAY as $key => $value):
							$checked = "";
							if ($ID <= 0 && $strError == '')
								$checked = "checked";
							if (is_array(${"arr" . $key}) && in_array($i, ${"arr" . $key}))
								$checked = "checked";
							?>
							<td><input id="arr<?= $key ?>_<?= $i ?>[]" <?= $disabled ?>
									name="arr<?= $key ?>[]"
									type="checkbox"
									value="<?= $i ?>" <?= $checked ?>></td>
						<?
						endforeach;

						?>
						<td><input <?= $disabled ?> type="checkbox"
								onclick="OnSelectAll(this.checked, '<?= $i ?>', false)"
								id="<?= $i ?>"></td>
					</tr>
				<?
				endfor;
				?>
				<SCRIPT>
					<!--
					ar = Array("MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY", "SUNDAY");
					for (j = 0; j < 7; j++) {
						name = ar[j];
						for (i = 0; i <= 23; i++) {
							name1 = "arr" + name + "_" + i + "[]";
							valu = true;
							if (document.getElementById(name1).checked == false) {
								valu = false;
								i = 24;
							}
						}

						if (valu)
							document.getElementById(name).checked = true;
						else
							document.getElementById(name).checked = false;
					}
					for (j = 0; j <= 23; j++) {
						valu = true;
						for (i = 0; i < 7; i++) {
							name = ar[i];
							name1 = "arr" + name + "_" + j + "[]";
							if (document.getElementById(name1).checked == false) {
								valu = false;
								i = 7;
							}
						}
						if (valu)
							document.getElementById(j).checked = true;
						else
							document.getElementById(j).checked = false;

					}
					//-->
				</SCRIPT>
			</table>
		</td>
	</tr>

	<? $tabControl->BeginNextTab(); ?>
	<? if (!$isEditMode): ?>
		<tr valign="top">
			<td width="40%"><?= GetMessage("AD_SHOW_PAGES"); ?></td>
			<td width="60%"><?
				$arr = $arrSHOW_PAGE;
				if (is_array($arr) && count($arr) > 0)
				{
					foreach ($arr as $page)
						echo htmlspecialcharsbx($page) . '<br>';
				}
				else
				{
					echo GetMessage("ADV_NO_LIMITS");
				}
				?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td width="40%"><?= GetMessage("AD_SHOW_PAGES"); ?></td>
			<td width="60%"><textarea name="SHOW_PAGE"
					cols="45"
					rows="6"
					wrap="OFF"><?= $str_SHOW_PAGE ?></textarea><br><?= GetMessage("AD_SHOW_PAGES_ALT") ?></td>
		</tr>
	<? endif; ?>

	<? if (!$isEditMode): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_NOT_SHOW_PAGES"); ?></td>
			<td><?
				$arr = $arrNOT_SHOW_PAGE;
				if (is_array($arr) && count($arr) > 0)
				{
					foreach ($arr as $page)
						echo htmlspecialcharsbx($page) . '<br>';
				}
				else
				{
					echo GetMessage("ADV_NO_LIMITS");
				}
				?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_NOT_SHOW_PAGES"); ?></td>
			<td><textarea name="NOT_SHOW_PAGE"
					cols="45"
					rows="6"
					wrap="OFF"><?= $str_NOT_SHOW_PAGE ?></textarea><br><?= GetMessage("AD_NOT_SHOW_PAGES_ALT") ?></td>
		</tr>
	<? endif; ?>

	<tr valign="top">
		<td><?= GetMessage("AD_KEYWORDS"); ?></td>
		<td><?
			if ($isEditMode) :
				?><textarea name="KEYWORDS" cols="45" rows="6" wrap="OFF"><?= $str_KEYWORDS ?></textarea>
				<br><?= GetMessage("AD_KEYWORDS_ALT") ?><?
			else :
				if (is_array($arrKEYWORDS))
					echo implode("<br>", $arrKEYWORDS);
				else
					echo GetMessage("ADV_NOT_SET");
			endif;
			?></td>
	</tr>

	<? $tabControl->BeginNextTab(); ?>
	<?
	$arrUsers = CAdvContract::GetAdvertisersArray();
	$ref = [];
	$ref_id = [];
	if (is_array($arrUsers))
	{
		foreach ($arrUsers as $arUser)
		{
			$ref[] = "[" . $arUser["ID"] . "] (" . $arUser["LOGIN"] . ") " . $arUser["NAME"] . " " . $arUser["LAST_NAME"];
			$ref_id[] = $arUser["ID"];
		}
	}

	if ($isEditRightsMode):
		?>
		<tr valign="top">
			<td width="40%"><?= GetMessage("AD_VIEW_STATISTICS") ?><br><IMG SRC="/bitrix/images/advertising/mouse.gif"
					WIDTH="44"
					HEIGHT="21"
					BORDER=0
					ALT=""></td>
			<td width="60%"><?
				echo SelectBoxMFromArray("arrUSER_VIEW[]", [
					"REFERENCE" => $ref,
					"REFERENCE_ID" => $ref_id,
				], $arrUSER_VIEW, "", true, 15);
				?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td width="40%"><?= GetMessage("AD_VIEW_STATISTICS") ?></td>
			<td width="60%"><?
				foreach ($ref_id as $key => $value)
				{
					if (is_array($arrUSER_VIEW) && in_array($value, $arrUSER_VIEW))
						echo HtmlFilter::encode($ref[$key]) . "<br>";
				}
				if (!is_set($arrUSER_VIEW))
					echo GetMessage("ADV_NOT_SET");
				?></td>
		</tr>
	<? endif; ?>

	<? if ($isEditRightsMode): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_MANAGE_BANNERS") ?><br><IMG SRC="/bitrix/images/advertising/mouse.gif"
					WIDTH="44"
					HEIGHT="21"
					BORDER=0
					ALT=""></td>
			<td><?
				echo SelectBoxMFromArray("arrUSER_ADD[]", [
					"REFERENCE" => $ref,
					"REFERENCE_ID" => $ref_id,
				], $arrUSER_ADD, "", true, 15);
				?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_MANAGE_BANNERS") ?></td>
			<td><?
				foreach ($ref_id as $key => $value)
				{
					if (is_array($arrUSER_ADD) && in_array($value, $arrUSER_ADD))
						echo HtmlFilter::encode($ref[$key]) . "<br>";
				}
				if (!is_set($arrUSER_ADD))
					echo GetMessage("ADV_NOT_SET");
				?></td>
		</tr>
	<? endif; ?>

	<? if ($isEditMode && ($isAdmin || ($isDemo && !$isOwner))): ?>
		<tr valign="top">
			<td><?= GetMessage("AD_EDIT_CONTRACT") ?><br><IMG SRC="/bitrix/images/advertising/mouse.gif"
					WIDTH="44"
					HEIGHT="21"
					BORDER=0
					ALT=""></td>
			<td><? echo SelectBoxMFromArray("arrUSER_EDIT[]", [
					"REFERENCE" => $ref,
					"REFERENCE_ID" => $ref_id,
				], $arrUSER_EDIT, "", true, 15);
				?></td>
		</tr>
	<? else: ?>
		<tr valign="top">
			<td><?= GetMessage("AD_EDIT_CONTRACT") ?></td>
			<td><?
				foreach ($ref_id as $key => $value)
				{
					if (is_array($arrUSER_EDIT) && in_array($value, $arrUSER_EDIT))
						echo HtmlFilter::encode($ref[$key]) . "<br>";
				}
				if (!is_set($arrUSER_EDIT))
					echo GetMessage("ADV_NOT_SET");
				?></td>
		</tr>
	<? endif; ?>

	<? if ($isAdmin || ($isDemo && !$isOwner)): ?>
		<? $tabControl->BeginNextTab(); ?>
		<? if ($isEditMode) : ?>
			<tr>
				<td colspan="2" align="center"><textarea style="width:85%"
						name="ADMIN_COMMENTS"
						rows="7"
						wrap="VIRTUAL"><? echo $str_ADMIN_COMMENTS ?></textarea></td>
			</tr>
		<? else: ?>
			<tr>
				<td colspan="2"><?= TxtToHTML($str_ADMIN_COMMENTS) ?></td>
			</tr>
		<? endif; ?>
	<? endif; ?>

	<?
	$disable = true;
	if ($isEditMode || $isEditRightsMode)
		$disable = false;

	$tabControl->Buttons(["disabled" => $disable, "back_url" => "adv_contract_list.php?lang=" . LANGUAGE_ID]);
	$tabControl->End();
	?>
</form>
<?
if (isset($aTabs[4]) && $str_ADMIN_COMMENTS == '' && !$isEditMode):?>
	<script>
		<!--
		tabControl.DisableTab("edit5");
		//-->
	</script>
<? endif; ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
