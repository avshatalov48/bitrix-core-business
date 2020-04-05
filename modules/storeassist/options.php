<?
$module_id = "storeassist";

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$mid = $_REQUEST["mid"];

$STAS_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($STAS_RIGHT >= "R")
{
	if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() && $STAS_RIGHT >= "W")
	{
		$errorMessage = "";
		COption::SetOptionString($module_id, "partner_name", (strlen($_POST["partner_name"]) > 0 ? trim($_POST["partner_name"]) : ""));

		if (strlen($_POST["partner_url"])>0)
		{
			if (!preg_match('/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}'.'((:[0-9]{1,5})?\/.*)?$/i', $_POST["partner_url"]))
			{
				$errorMessage = GetMessage("MAIN_TAB_PARTNER_URL_ERROR");
				CAdminMessage::ShowMessage(array(
					"MESSAGE" => $errorMessage,
					"TYPE" => "ERROR",
					"HTML" => true
				));
			}
			else
			{
				COption::SetOptionString($module_id, "partner_url", trim($_POST["partner_url"]));
			}
		}
		else
		{
			COption::SetOptionString($module_id, "partner_url", "");
		}
		if (!$errorMessage)
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&mid=".urlencode($mid));
	}

	$partnerName = COption::GetOptionString($module_id, "partner_name", "");
	$partnerUrl = COption::GetOptionString($module_id, "partner_url", "");

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SETTINGS"), "ICON" => "currency_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SETTINGS")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);

	$tabControl->Begin();
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?echo LANG?>" name="ara">
		<?=bitrix_sessid_post();?>
		<?
		$tabControl->BeginNextTab();
		?>
		<tr>
			<td valign="top" width="50%">
				<label for="partnet_name"><?=GetMessage("MAIN_TAB_PARTNER_NAME")?> </label>
			</td>
			<td valign="middle" width="50%">
				<input type="text" name="partner_name" id="partnet_name" size="35" value="<?=(isset($_POST["partner_name"]) ? htmlspecialcharsbx($_POST["partner_name"]) : htmlspecialcharsbx($partnerName))?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top" width="50%">
				<label for="partnet_url"><?=GetMessage("MAIN_TAB_PARTNER_URL")?> </label>
			</td>
			<td valign="middle" width="50%">
				<input type="text" name="partner_url" id="partnet_url" size="35" value="<?=(isset($_POST["partner_url"]) ? htmlspecialcharsbx($_POST["partner_url"]) : htmlspecialcharsbx($partnerUrl))?>"/>
			</td>
		</tr>
		<?$tabControl->Buttons();?>

		<input type="submit" <?if ($STAS_RIGHT < "W") echo "disabled" ?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
		<input type="hidden" name="Update" value="Y">

		<?$tabControl->End();?>
	</form>
<?
}
?>