<?
/** @var CMain $APPLICATION */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(Loc::getMessage("SALE_EBAY_TITLE"));
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/ebay_admin.js", true);

require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if(isset($_REQUEST["action"]))
{
	switch($_REQUEST["action"])
	{
		case "SHOW_TOKEN_SFTP":
			$aTabs = array(
				array(
					"DIV" => "edit1",
					"TAB" => Loc::getMessage("SALE_EBAY_TITLE"),
					"ICON" => "sale",
					"TITLE" => Loc::getMessage("SALE_EBAY_TITLE"),
				)
			);
			$tabControl = new CAdminTabControl("tabControl", $aTabs);
			$tabControl->Begin();
			$tabControl->BeginNextTab();
			?>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_SFTP_TOKEN")?>:</td><td><textarea id="SALE_EBAY_SETTINGS_SFTP_TOKEN" value="" readonly rows="10" cols="100"></textarea></td></tr>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_SFTP_TOKEN_EXP")?>:</td><td><input type="text" id="SALE_EBAY_SETTINGS_SFTP_TOKEN_EXP" value="" readonly></td></tr>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_SFTP_USER_NAME")?>:</td><td><input type="text" id="SALE_EBAY_SETTINGS_SFTP_USER_NAME" value="" readonly></td></tr>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_SFTP_ACCOUNT_STATE")?>:</td><td><input type="text" id="SALE_EBAY_SETTINGS_SFTP_ACCOUNT_STATE" value="" readonly></td></tr>
			<?
			$tabControl->EndTab();
			$tabControl->Buttons();
			?>
				<input type="button"  value="<?=Loc::getMessage("SALE_EBAY_CLOSE")?>" onclick="window.close();">
			<?
			$tabControl->End();
			?>

			<script>
				if(BX.Sale.EbayAdmin.setOpenerFieldsFromHash('SFTP_TOKEN'))
					window.close();
			</script>
			<?
			break;

		case "SHOW_TOKEN":
			$aTabs = array(
				array(
					"DIV" => "edit1",
					"TAB" => Loc::getMessage("SALE_EBAY_TITLE"),
					"ICON" => "sale",
					"TITLE" => Loc::getMessage("SALE_EBAY_TITLE"),
				)
			);
			$tabControl = new CAdminTabControl("tabControl", $aTabs);
			$tabControl->Begin();
			$tabControl->BeginNextTab();
			?>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_TOKEN")?>:</td><td><textarea id="SALE_EBAY_SETTINGS_API_TOKEN" value="" readonly rows="10" cols="100"></textarea></td></tr>
				<tr><td><?=Loc::getMessage("SALE_EBAY_YOUR_TOKEN_EXP")?>:</td><td><input type="text" id="SALE_EBAY_SETTINGS_API_TOKEN_EXP" value="" readonly></td></tr>
			<?
			$tabControl->EndTab();
			$tabControl->Buttons();
			?>
				<input type="button"  value="<?=Loc::getMessage("SALE_EBAY_CLOSE")?>" onclick="window.close();">
			<?
			$tabControl->End();
			?>

				<script>
						if(BX.Sale.EbayAdmin.setOpenerFieldsFromHash('API_TOKEN'))
							window.close();
				</script>
			<?
			break;

		case "APP_REJECTED":
			?>
				<script>
					if(BX.Sale.EbayAdmin.showAlertOpener("<?=Loc::getMessage("SALE_EBAY_AUTH_FAIL")?>"))
						window.close();
				</script>
			<?

			break;

		case "GET_TOKEN_ERROR":
			?>
			<script>
				var splitted = window.location.hash.substring(1).split("&"),
					message = "<?=Loc::getMessage("SALE_EBAY_GET_TOKEN_FAIL")?>";

				for(var i in splitted)
				{
					var keyValue = splitted[i].split("=");

					if(!keyValue)
						continue;

					if(keyValue[0] == "ERROR" && keyValue[1])
						message += "\n"+decodeURIComponent(keyValue[1]);
				}

				if(BX.Sale.EbayAdmin.showAlertOpener(message))
					window.close();
				else
					window.location.href = 'sale_ebay_general.php?lang=<?=LANGUAGE_ID?>';
			</script>
			<?

			break;

		default:
			throw new \Bitrix\Main\SystemException("Such action is unknown");
	}
}
else
{
	throw new \Bitrix\Main\SystemException("Action is undefined");
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");