<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleCCards'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = intval($ID);

if ($REQUEST_METHOD=="POST" && $Update <> '' && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$USER_ID = intval($USER_ID);
	if ($USER_ID <= 0)
		$errorMessage .= GetMessage("SCE_EMPTY_USER").".<br>";

	$PAY_SYSTEM_ACTION_ID = intval($PAY_SYSTEM_ACTION_ID);
	if ($PAY_SYSTEM_ACTION_ID <= 0)
		$errorMessage .= GetMessage("SCE_EMPTY_PAY_SYS").".<br>";

	$CARD_TYPE = Trim($CARD_TYPE);
	$CARD_TYPE = ToUpper($CARD_TYPE);
	if ($CARD_TYPE == '')
		$errorMessage .= GetMessage("SCE_EMPTY_CARD_TYPE").".<br>";

	$CARD_NUM = preg_replace("/[\D]+/", "", $CARD_NUM);
	if ($CARD_NUM == '')
	{
		$errorMessage .= GetMessage("SCE_EMPTY_CARD_NUM").".<br>";
	}
	else
	{
		$cardType = CSaleUserCards::IdentifyCardType($CARD_NUM);
		if ($cardType != $CARD_TYPE)
			$errorMessage .= GetMessage("SCE_WRONG_CARD_NUM").".<br>";
	}

	$CARD_EXP_MONTH = intval($CARD_EXP_MONTH);
	if ($CARD_EXP_MONTH < 1 || $CARD_EXP_MONTH > 12)
		$errorMessage .= GetMessage("SCE_WRONG_MONTH").".<br>";

	$CARD_EXP_YEAR = intval($CARD_EXP_YEAR);
	if ($CARD_EXP_YEAR < 2000 || $CARD_EXP_YEAR > 2100)
		$errorMessage .= GetMessage("SCE_WRONG_YEAR").".<br>";

	if ($errorMessage == '')
	{
		$CURRENT_BUDGET = str_replace(",", ".", $CURRENT_BUDGET);
		$CURRENT_BUDGET = DoubleVal($CURRENT_BUDGET);
		$SUM_MIN = str_replace(",", ".", $SUM_MIN);
		$SUM_MIN = DoubleVal($SUM_MIN);
		$SUM_MAX = str_replace(",", ".", $SUM_MAX);
		$SUM_MAX = DoubleVal($SUM_MAX);
		$ACTIVE = (($ACTIVE == "Y") ? "Y" : "N");
		$SORT = ((intval($SORT) > 0) ? intval($SORT) : 100);
		$CURRENCY = Trim($CURRENCY);
		$SUM_CURRENCY = Trim($SUM_CURRENCY);

		if (($SUM_MIN > 0 || $SUM_MAX > 0) && $SUM_CURRENCY == '')
			$errorMessage .= GetMessage("SCE_EMPTY_CURRENCY").".<br>";
	}

	if ($errorMessage == '')
	{
		$arFields = array(
				"USER_ID" => $USER_ID,
				"ACTIVE" => $ACTIVE,
				"SORT" => $SORT,
				"PAY_SYSTEM_ACTION_ID" => $PAY_SYSTEM_ACTION_ID,
				"CURRENCY" => (($CURRENCY <> '') ? $CURRENCY : False),
				"CARD_TYPE" => $CARD_TYPE,
				"CARD_NUM" => CSaleUserCards::CryptData($CARD_NUM, "E"),
				"CARD_EXP_MONTH" => $CARD_EXP_MONTH,
				"CARD_EXP_YEAR" => $CARD_EXP_YEAR,
				"DESCRIPTION" => (($DESCRIPTION <> '') ? $DESCRIPTION : False),
				"CARD_CODE" => $CARD_CODE,
				"SUM_MIN" => (($SUM_MIN > 0) ? $SUM_MIN : False),
				"SUM_MAX" => (($SUM_MAX > 0) ? $SUM_MAX : False),
				"SUM_CURRENCY" => (($SUM_CURRENCY <> '') ? $SUM_CURRENCY : False)
			);

		if ($ID > 0)
		{
			$res = CSaleUserCards::Update($ID, $arFields);
		}
		else
		{
			$ID = CSaleUserCards::Add($arFields);
			$res = ($ID > 0);
		}

		if (!$res)
		{
			$bVarsFromForm = true;
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("SCE_SAVING").".<br>";
		}
		else
		{
			if ($apply == '')
				LocalRedirect("/bitrix/admin/sale_ccards_admin.php?lang=".LANG.GetFilterParams("filter_", false));
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SCE_ERROR_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SCE_ADD_NEW"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$dbCCard = CSaleUserCards::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array("ID", "USER_ID", "ACTIVE", "SORT", "PAY_SYSTEM_ACTION_ID", "CURRENCY", "CARD_TYPE", "CARD_NUM", "CARD_CODE", "CARD_EXP_MONTH", "CARD_EXP_YEAR", "DESCRIPTION", "SUM_MIN", "SUM_MAX", "SUM_CURRENCY", "TIMESTAMP_X", "LAST_STATUS", "LAST_STATUS_CODE", "LAST_STATUS_DESCRIPTION", "LAST_STATUS_MESSAGE", "LAST_SUM", "LAST_CURRENCY", "LAST_DATE", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
	);
if (!$dbCCard->ExtractFields("str_"))
{
	$ID = 0;
	$str_ACTIVE = "Y";
	$str_SORT = 100;
}
else
	$str_CARD_NUM = CSaleUserCards::CryptData($str_CARD_NUM, "D");

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_user_cards", "", "str_");

?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SCEN_2FLIST"),
				"TITLE" => GetMessage("SCEN_2FLIST_TITLE"),
				"LINK" => "/bitrix/admin/sale_ccards_admin.php?lang=".LANG.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

if ($ID > 0 && $saleModulePermissions >= "U")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SCEN_NEW_CCARD"),
			"TITLE" => GetMessage("SCEN_NEW_CCARD_TITLE"),
			"LINK" => "/bitrix/admin/sale_ccards_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SCEN_DELETE_CCARD"), 
				"LINK" => "javascript:if(confirm('".GetMessage("SCEN_DELETE_CCARD_CONFIRM")."')) window.location='/bitrix/admin/sale_ccards_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON" => "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if (!CSaleUserCards::CheckPassword())
	CAdminMessage::ShowMessage(Array("DETAILS"=>GetMessage("SCE_NO_VALID_PASSWORD"), "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SCE_ATTENTION")));
?>
<?if($errorMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SCE_ERROR"), "HTML"=>true));?>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fccards_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SCEN_TAB_CCARD"), "ICON" => "sale", "TITLE" => GetMessage("SCEN_TAB_CCARD_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?= $ID ?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_TIMESTAMP")?></td>
			<td><?= $str_TIMESTAMP_X ?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SCE_USER")?></td>
		<td width="60%"><?
			$user_name = "";
			if ($ID>0)
				$user_name = "[<a title=\"".GetMessage("SCE_USER_PROFILE")."\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$str_USER_ID."\">".$str_USER_ID."</a>] (".$str_USER_LOGIN.") ".$str_USER_NAME." ".$str_USER_LAST_NAME;

			echo FindUserID("USER_ID", $str_USER_ID, $user_name, "fccards_edit");
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_ACTIVE")?></td>
		<td>
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_SORT")?></td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="20" value="<?= $str_SORT ?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SCE_PAY_SYSTEM")?></td>
		<td>
			<select name="PAY_SYSTEM_ACTION_ID">
				<?
				$dbPaySysActions = CSalePaySystemAction::GetList(
						array("NAME" => "ASC", "PT_NAME" => "ASC", "PS_NAME" => "ASC"),
						array("HAVE_ACTION" => "Y"),
						false,
						false,
						array("*")
					);
				while ($arPaySysActions = $dbPaySysActions->Fetch())
				{
					?><option value="<?= $arPaySysActions["ID"] ?>"<?if (intval($str_PAY_SYSTEM_ACTION_ID) == intval($arPaySysActions["ID"])) echo " selected";?>><?= htmlspecialcharsEx($arPaySysActions["NAME"]." [".$arPaySysActions["PS_NAME"]." / ".$arPaySysActions["PT_NAME"]."]") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_CURRENCY")?></td>
		<td>
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, GetMessage("SCE_ANY"), false, "", "")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_CARD_TYPE")?></td>
		<td>
			<select name="CARD_TYPE">
				<option value="VISA"<?if ($str_CARD_TYPE == "VISA") echo " selected";?>>Visa</option>
				<option value="MASTERCARD"<?if ($str_CARD_TYPE == "MASTERCARD") echo " selected";?>>MasterCard</option>
				<option value="AMEX"<?if ($str_CARD_TYPE == "AMEX") echo " selected";?>>Amex</option>
				<option value="DINERS"<?if ($str_CARD_TYPE == "DINERS") echo " selected";?>>Diners</option>
				<option value="DISCOVER"<?if ($str_CARD_TYPE == "DISCOVER") echo " selected";?>>Discover</option>
				<option value="JCB"<?if ($str_CARD_TYPE == "JCB") echo " selected";?>>JCB</option>
				<option value="ENROUTE"<?if ($str_CARD_TYPE == "ENROUTE") echo " selected";?>>Enroute</option>
			</select>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("SCE_CARD_NUM")?></td>
		<td>
			<input type="text" name="CARD_NUM" size="30" maxlength="30" value="<?= (($saleModulePermissions == "W") ? $str_CARD_NUM : "XXXXXXXXXXX".mb_substr($str_CARD_NUM, mb_strlen($str_CARD_NUM) - 4, 4)); ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_CARD_EXP")?></td>
		<td>
			<select name="CARD_EXP_MONTH">
				<?
				for ($i = 1; $i <= 12; $i++)
				{
					?><option value="<?= $i ?>"<?if (intval($str_CARD_EXP_MONTH) == $i) echo " selected";?>><?= ((mb_strlen($i) < 2) ? "0".$i : $i) ?></option><?
				}
				?>
			</select>
			<select name="CARD_EXP_YEAR">
				<?
				for ($i = 2005; $i <= 2100; $i++)
				{
					?><option value="<?= $i ?>"<?if (intval($str_CARD_EXP_YEAR) == $i) echo " selected";?>><?= $i ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td>CVC2:</td>
		<td>
			<input type="text" name="CARD_CODE" size="10" maxlength="10" value="<?= $str_CARD_CODE ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_MIN_SUM")?></td>
		<td>
			<input type="text" name="SUM_MIN" size="10" maxlength="10" value="<?= ((DoubleVal($str_SUM_MIN) > 0) ? roundEx($str_SUM_MIN, SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_MAX_SUM")?></td>
		<td>
			<input type="text" name="SUM_MAX" size="10" maxlength="10" value="<?= ((DoubleVal($str_SUM_MAX) > 0) ? roundEx($str_SUM_MAX, SALE_VALUE_PRECISION) : "") ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_SUM_CURRENCY")?></td>
		<td>
			<?echo CCurrency::SelectBox("SUM_CURRENCY", $str_SUM_CURRENCY, "", false, "", "class='typeselect'")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SCE_DESCRIPTION")?></td>
		<td>
			<input type="text" name="DESCRIPTION" size="40" maxlength="250" value="<?= $str_DESCRIPTION ?>">
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("SCE_LAST_ACTIV")?></td>
	</tr>
	<?
	if ($str_LAST_STATUS == "Y" || $str_LAST_STATUS == "N")
	{
		?>
		<tr>
			<td><?echo GetMessage("SCE_STATUS")?></td>
			<td>
				<?= (($str_LAST_STATUS == "Y") ? GetMessage("SCE_SUCCESS") : GetMessage("SCE_ERROR")) ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_STATUS_CODE")?></td>
			<td>
				<?= $str_LAST_STATUS_CODE ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_DESCRIPTION")?></td>
			<td>
				<?= $str_LAST_STATUS_DESCRIPTION ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_MESSAGE")?></td>
			<td>
				<?= $str_LAST_STATUS_MESSAGE ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_SUM")?></td>
			<td>
				<?= $str_LAST_SUM ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_CUR")?></td>
			<td>
				<?= $str_LAST_CURRENCY ?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("SCE_DATE")?></td>
			<td>
				<?= $str_LAST_DATE ?>
			</td>
		</tr>
		<?
	}
	else
	{
		?>
		<tr>
			<td colspan="2" align="center"><?echo GetMessage("SCE_NONE")?></td>
		</tr>
		<?
	}
	?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_account_admin.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>