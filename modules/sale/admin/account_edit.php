<?
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);

ClearVars();
$errorMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);

if ($_SERVER['REQUEST_METHOD']=="POST" && strlen($Update)>0 && $saleModulePermissions>="U" && check_bitrix_sessid())
{
	if ($ID <= 0)
	{
		if ($saleModulePermissions < "W")
			$errorMessage .= GetMessage("SAE_NO_PERMS2ADD").".<br>";

		$USER_ID = IntVal($USER_ID);
		if ($USER_ID <= 0)
			$errorMessage .= GetMessage("SAE_EMPTY_USER").".<br>";

		$CURRENCY = Trim($CURRENCY);
		if (strlen($CURRENCY) <= 0)
			$errorMessage .= GetMessage("SAE_EMPTY_CURRENCY").".<br>";

		if ($errorMessage == '')
		{
			$arFilter = array(
					"USER_ID" => $USER_ID,
					"CURRENCY" => $CURRENCY
				);

			$num = CSaleUserAccount::GetList(
					array(),
					$arFilter,
					array()
				);
			if (IntVal($num) > 0)
				$errorMessage .= str_replace("#USER#", $USER_ID, str_replace("#CURRENCY#", $CURRENCY, GetMessage("SAE_ALREADY_EXISTS"))).".<br>";
		}

		if ($errorMessage == '')
		{
			$OLD_BUDGET = 0.0;
		}
	}
	else
	{
		if (!($arOldUserAccount = CSaleUserAccount::GetByID($ID)))
			$errorMessage .= str_replace("#ID#", $ID, GetMessage("SAE_NO_ACCOUNT")).".<br>";

		if ($errorMessage == '')
		{
			$USER_ID = $arOldUserAccount["USER_ID"];
			$CURRENCY = $arOldUserAccount["CURRENCY"];
			$OLD_BUDGET = DoubleVal($arOldUserAccount["CURRENT_BUDGET"]);
		}
	}
	
	$currentLocked = "";
	if ($errorMessage == '')
	{
		$dbUserAccount = CSaleUserAccount::GetList(
			array(),
			array("USER_ID" => $USER_ID, "CURRENCY" => $CURRENCY)
		);
		$arUserAccount = $dbUserAccount->Fetch();
		if (is_array($arUserAccount))
			$currentLocked = $arUserAccount["LOCKED"];

		$allowUpdate = false;
		$CURRENT_BUDGET = str_replace(",", ".", $CURRENT_BUDGET);
		$CURRENT_BUDGET = (float)$CURRENT_BUDGET;
		if ($ID > 0)
		{
			$updateSum = $CURRENT_BUDGET - $OLD_BUDGET;

			$allowUpdate = ($updateSum != 0);
		}
		else
		{
			$updateSum = $CURRENT_BUDGET;
			$allowUpdate = true;
		}

		if ($allowUpdate)
		{
			if (!CSaleUserAccount::UpdateAccount($USER_ID, $updateSum, $CURRENCY, "MANUAL", 0, $CHANGE_REASON))
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString().".<br>";
				else
					$errorMessage .= GetMessage("SAE_ERROR_SAVING").".<br>";
			}
		}
	}

	if ($errorMessage == '' AND $currentLocked != "")
	{
		if($_POST["UNLOCK"] == "Y")
			CSaleUserAccount::UnLock($USER_ID, $CURRENCY);
		
		if($_POST["UNLOCK"] == "N" OR ($currentLocked == "Y" AND !isset($_POST["UNLOCK"]))) 
			CSaleUserAccount::Lock($USER_ID, $CURRENCY); 
	}
	
	if ($errorMessage == '')
	{
		$arUserAccount = CSaleUserAccount::GetByUserID($USER_ID, $CURRENCY);
		if (DoubleVal($arUserAccount["CURRENT_BUDGET"]) != $CURRENT_BUDGET)
			$errorMessage .= GetMessage("SAE_ERROR_SAVING_SUM").".<br>";
	}

	if ($errorMessage == '')
	{
		$ID = IntVal($arUserAccount["ID"]);

		$arFields = array(
				"NOTES" => ((strlen($NOTES) > 0) ? $NOTES : False)
			);
		if (!CSaleUserAccount::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("SAE_ERROR_SAVING_COMMENT").".<br>";
		}
	}

	if ($errorMessage == '')
	{
		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/sale_account_admin.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
	}
}

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SAE_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SAE_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$dbAccount = CSaleUserAccount::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array("ID", "USER_ID", "CURRENT_BUDGET", "CURRENCY", "LOCKED", "NOTES", "TIMESTAMP_X", "DATE_LOCKED", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
	);
if (!$dbAccount->ExtractFields("str_"))
{
	if ($saleModulePermissions < "W")
		$errorMessage .= GetMessage("SAE_NO_PERMS2ADD").".<br>";
	$ID = 0;
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_user_account", "", "str_");

$aMenu = array(
		array(
				"TEXT" => GetMessage("SAEN_2FLIST"),
				"LINK" => "/bitrix/admin/sale_account_admin.php?lang=".LANGUAGE_ID.GetFilterParams("filter_"),
				"ICON"	=> "btn_list",
				"TITLE" => GetMessage("SAEN_2FLIST_TITLE"),
			)
	);

if ($ID > 0 && $saleModulePermissions >= "U")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SAEN_NEW_ACCOUNT"),
			"LINK" => "/bitrix/admin/sale_account_edit.php?lang=".LANGUAGE_ID.GetFilterParams("filter_"),
			"ICON"	=> "btn_new",
			"TITLE" => GetMessage("SAEN_NEW_ACCOUNT_TITLE"),
		);

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SAEN_DELETE_ACCOUNT"), 
				"LINK" => "javascript:if(confirm('".GetMessage("SAEN_DELETE_ACCOUNT_CONFIRM")."')) window.location='/bitrix/admin/sale_account_admin.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON"	=> "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errorMessage != '')
	CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SAE_ERROR"), "HTML"=>true));
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID ?>" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<?=bitrix_sessid_post()?><?

$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SAEN_TAB_ACCOUNT"), "ICON" => "sale", "TITLE" => GetMessage("SAEN_TAB_ACCOUNT_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("SAE_TIMESTAMP")?></td>
			<td><?=$str_TIMESTAMP_X?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SAE_USER1")?></td>
		<td width="60%">
			<?if ($ID > 0):?>
				<input type="hidden" name="USER_ID" value="<?=$str_USER_ID?>">
				[<a title="<?echo GetMessage("SAE_USER_PROFILE")?>" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$str_USER_ID?>"><?=$str_USER_ID?></a>] (<?=$str_USER_LOGIN?>) <?=$str_USER_NAME?> <?=$str_USER_LAST_NAME?>
			<?else:?>
			<?echo FindUserID("USER_ID", $str_USER_ID);?>
			<?endif;?>
			</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SAE_SUM")?></td>
		<td>
			<input type="text" name="CURRENT_BUDGET" size="10" maxlength="20" value="<?= roundEx($str_CURRENT_BUDGET, SALE_VALUE_PRECISION) ?>">
			<?
			if ($ID > 0)
			{
				?>
				<input type="hidden" name="CURRENCY" value="<?= $str_CURRENCY ?>">
				<?= $str_CURRENCY ?>
				<?
			}
			else
			{
				echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "", "");
			}
			?>
		</td>
	</tr>
	<?if ($ID > 0 && $str_LOCKED=="Y"):?>
		<tr>
			<td><?echo GetMessage("SAE_UNLOCK")?></td>
			<td>
				<input type="checkbox" name="UNLOCK" value="Y"<?if ($str_LOCKED != "Y") echo " disabled"?>>
				<?
				if ($str_LOCKED=="Y")
					echo GetMessage("SAE_LOCKED").$str_DATE_LOCKED.")";
				?>
			</td>
		</tr>
	<?endif;
		
	if ($ID > 0 && $str_LOCKED=="N"):?>
		<tr>
			<td><?echo GetMessage("SAE_LOCK")?></td>
			<td>
				<input type="checkbox" name="UNLOCK" value="N"<?if ($str_LOCKED != "N") echo " disabled"?>>
			</td>
		</tr>
	<?endif;?>	
	<tr>
		<td valign="top"><?echo GetMessage("SAE_NOTES")?></td>
		<td valign="top">
			<textarea name="NOTES" rows="3" cols="40"><?= $str_NOTES ?></textarea>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("SAE_OSN")?><br><small><?echo GetMessage("SAE_OSN_NOTE")?></small></td>
		<td valign="top">
			<textarea name="CHANGE_REASON" rows="3" cols="40"><?= htmlspecialcharsEx($CHANGE_REASON) ?></textarea>
		</td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "U"),
				"back_url" => "/bitrix/admin/sale_account_admin.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
			)
	);

$tabControl->End();
?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");