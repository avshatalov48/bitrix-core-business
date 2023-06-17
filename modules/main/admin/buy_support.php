<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use Bitrix\Main\Application;

require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_client.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("BUY_SUP_TITLE"));
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/desktop/templates/admin/style.css");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$license = Application::getInstance()->getLicense();
$lkeySign = $license->getHashLicenseKey();
$domain = $license->getDomainStoreLicense();
$partner_id = $license->getPartnerId();

if($partner_id <= 0)
{
	LocalRedirect($license->getBuyLink(), true);
}
else
{
	$lkid = 0;
	?>
	<div  class="bx-gadgetsadm-list-table-layout">
		<div class="bx-gadgets-top-wrap" style="cursor: auto !important;">
			<div class="bx-gadgets-top-center">
				<div class="bx-gadgets-top-title"><?=GetMessage("BUY_SUP_PARTNER_TITLE")?></div>
			</div>
		</div>
		<div class="bx-gadgets-content" style="padding: 25px;">
			<?=GetMessage("BUY_SUP_BUY_3")?>
			<?
			$ht = new Bitrix\Main\Web\HttpClient(array("socketTimeout" => 30));
			$arF = array(
				"license_key" => $lkeySign,
				"action" => "get_partner",
				"partner_id" => $partner_id,
				"lang" => LANGUAGE_ID,
			);
			if($res = $ht->post($domain."/key_update.php", $arF))
			{
			if ($ht->getStatus() == "200")
			{
			$res = Bitrix\Main\Web\Json::decode($res);
			if ($res["result"] == "ok")
			{
			?><div style="background-color: #f2f6f7; padding: 10px 20px 15px 20px; font-size: 15px; border-radius: 4px;">
				<div style="font-weight: bold; line-height: 2em;"><span style="color: #798284;"><?=GetMessage("BUY_SUP_PARTNER")?></span>&nbsp;
					<?
					if($res["link"] <> '')
					{
						?><a href="<?=$res["link"]?>" target="_blank" style="color: #000; text-decoration: none;"><?=$res["name"]?></a><?
					}
					else
					{
						echo $res["name"];
					}
					echo "</div>";

					if($res["phone"] <> '')
					{
						?><div style="display: inline-block;"><span style="color: #798284;"><?=GetMessage("BUY_SUP_PHONE")?></span>&nbsp;<?=$res["phone"]?></div><?
					}
					if($res["email"] <> '')
					{
						?><div style="display: inline-block; padding-left: 40px;"><span style="color: #798284;"><?=GetMessage("BUY_SUP_EMAIL")?></span>&nbsp;<a href="mailto:<?=$res["email"]?>" style="color: #000; text-decoration: none;"><?=$res["email"]?></a></div><?

					}
					?>
				</div>
				<?
				}
				}
				}
				?>

				<div style="position: relative; padding: 25px; border: 1px solid #859f4a; border-radius: 4px; margin-top: 40px;">
					<div style="font-size: 18px; line-height: 28px; position: absolute; top: -14px; left: 14px; display: block; height: 28px; margin: 0; padding: 0; padding: 0 10px; vertical-align: middle; color: #859f4a; background: #fff; font-weight: bold;"><?=GetMessage("BUY_SUP_TOBUY")?></div>
					<?
					$ht = new Bitrix\Main\Web\HttpClient(array("socketTimeout" => 30));
					$arF = array(
						"license_key" => $lkeySign,
						"lang" => LANGUAGE_ID,
					);
					$buyUrl = "";
					if($res = $ht->post($domain."/key_update.php", $arF))
					{
						if($ht->getStatus() == "200")
						{
							$res = Bitrix\Main\Web\Json::decode($res);
							if($res["result"] == "ok")
							{
								?>
								<div style="font-size: 16px; padding-top: 10px;"><?
								$lkid = $res["lkid"];
								if(count($res["toBuy"]) == 1)
								{
									foreach($res["toBuy"] as $v)
									{
										echo $v["NAME"]." &mdash; <b>".$res["price"]."</b>";
									}
								}
								else
								{
									foreach($res["toBuy"] as $v)
									{
										echo $v["NAME"];
										if(intval($v["CNT"]) > 0)
											echo " - ".$v["CNT"]." ".GetMessage("BUY_SUP_SHT");
										echo "<br />";
									}
									echo GetMessage("BUY_SUP_AMOUNT", array("#AMOUNT#" => $res["price"]));
								}
								$buyUrl = $res["toBasket"];
								?></div><?
							}
						}
					}
					?>
					<script>
						function sendRequest()
						{
							var nm = BX('name').value;
							var em = BX('email').value;
							var pn = BX('phone').value;
							BX('error').innerHTML = '';

							if(em.length > 0 || pn.length > 0)
							{
								BX.ajax.post(
									'<?=$domain?>/key_update.php',
									{"action": "send_partner_info", "partner_id": "<?=$partner_id?>", "phone": pn, "email": em, "name": nm, "license_key": "<?=CUtil::JSEscape($lkeySign)?>", "site" : "<?=CUtil::JSEscape($_SERVER["HTTP_HOST"])?>"}
								);
								BX.show(BX('ok'));
								BX.hide(BX('req'));
							}
							else
							{
								BX('error').innerHTML = '<?=GetMessageJS("BUY_SUP_CONTACT")?>';
							}
						}

					</script>
					<div id="error"></div>
					<div id="ok" style="display: none; color: #859f4a; font-weight: bold;"><br /><br /><?=GetMessage("BUY_SUP_CONTACT_OK2")?></div>
					<div id="req">
						<br /><br/>
						<table style="border-bottom: 1px solid #dbdbda;">
							<tr>
								<td nowrap valign="middle"><?=GetMessage("BUY_SUP_NAME")?></td>
								<td style="padding: 5px 0 5px 15px;"><input type="text" name="name" value="<?=htmlspecialcharsbx($USER->GetFullName())?>" id="name"></td>
								<td rowspan="3" style="color:#788186; padding-left: 25px;" valign="top"><?=GetMessage("BUY_SUP_PREQUEST1")?></td>
							</tr>
							<tr>
								<td nowrap valign="middle"><?=GetMessage("BUY_SUP_YPHONE")?></td>
								<td style="padding: 5px 0 5px 15px;"><input type="text" name="phone" value="" id="phone"></td>
							</tr>
							<tr>
								<td nowrap valign="middle"><?=GetMessage("BUY_SUP_YEMAIL")?></td>
								<td style="padding: 5px 0 5px 15px;"><input type="text" name="email" value="<?=htmlspecialcharsbx($USER->GetEmail())?>" id="email"></td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
						</table>
						<br />
						<a href="javascript:void(0)" onclick="sendRequest();" class="adm-btn adm-btn-save"><?=GetMessage("BUY_SUP_BUTTON")?></a>
					</div>
				</div>
				<br /><br />
				<div style="color:#464f57;">
					<a href="<?=$res["toBasket"]?>" target="_blank"><?= GetMessage("BUY_SUP_BUY_SELF") ?></a><br /><br />
				</div>
			</div>
		</div>
	</div>
<?}?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>