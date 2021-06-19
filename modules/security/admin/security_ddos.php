<?php
define('ADMIN_MODULE_NAME', 'security');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_client.php");

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/desktop/templates/admin/style.css");
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/security_ddos.css");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


if (LANGUAGE_ID !== 'ru')
{
	$APPLICATION->SetTitle(GetMessage('MFD_TITLE'));
	CAdminMessage::ShowMessage(array(
		'MESSAGE' => GetMessage('MFD_ERR_RUS_ONLY'),
		'TYPE' => 'error'
	));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
}

$lkeySign = md5(CUpdateClient::GetLicenseKey());

$supportFinishDate = COption::GetOptionString("main", "~support_finish_date");
$aSupportFinishDate=ParseDate($supportFinishDate, 'ymd');
$supportFinishStamp = mktime(0,0,0, $aSupportFinishDate[1], $aSupportFinishDate[0], $aSupportFinishDate[2]);

$errorMessage = "";
if(!IsModuleInstalled("intranet"))
{
	if ($supportFinishStamp > time() || $supportFinishDate == '')
	{

		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			if (check_bitrix_sessid() && $_POST["DD"] == "Y")
			{
				$ht = new Bitrix\Main\Web\HttpClient(array("socketTimeout" => 30));
				$arFields = array(
					"lkc"            => $lkeySign,
					"DD"             => "Y",
					"FCS"            => "Y",
					"SGN"            => md5($_POST["DOMAIN"]."|".$_POST["IP"]."|".$lkeySign),
					"DOMAIN"         => $_POST["DOMAIN"],
					"IP"             => $_POST["IP"],
					"NAME"           => $_POST["NAME"],
					"CONTACT_PERSON" => $_POST["CONTACT_PERSON"],
					"EMAIL"          => $_POST["EMAIL"],
					"PHONE"          => $_POST["PHONE"],
				);
				$arFields = $APPLICATION->ConvertCharsetArray($arFields, LANG_CHARSET, "windows-1251");

				if ($arFields["DOMAIN"] == '')
					$errorMessage .= GetMessage("MFD_ER_DOMAIN")."<Br>";
				if ($arFields["IP"] == '')
					$errorMessage .= GetMessage("MFD_ER_IP")."<Br>";
				if ($arFields["NAME"] == '')
					$errorMessage .= GetMessage("MFD_ER_NAME")."<Br>";
				if ($arFields["EMAIL"] == '')
					$errorMessage .= GetMessage("MFD_ER_EMAIL")."<Br>";
				if ($arFields["EMAIL"] <> '' && !check_email($arFields["EMAIL"], true))
					$errorMessage .= GetMessage("MFD_ER_EMAIL2")."<Br>";
				if ($res = $ht->post("https://www.1c-bitrix.ru/buy_tmp/ddos.php", $arFields))
				{
					if ($ht->getStatus() == "200")
					{
						$res = Bitrix\Main\Web\Json::decode($res);
						if ($res["status"] == "ok")
						{
							COption::SetOptionString("main", "~ddos_date", date("Y-m-d"));
							LocalRedirect($APPLICATION->GetCurPageParam("ok=y", array("ok"))."#ddf");
						}
						elseif ($res["status"] == "error")
						{
							$errorMessage = htmlspecialcharsEx($res["text"]);
						}
					}

					if ($errorMessage == '')
						$errorMessage = GetMessage("MFD_ER_ER");
				}
			}
			else
			{
				$errorMessage = GetMessage("MFD_ER_SESS");
			}
		}
	}
	else
	{
		$errorMessage = GetMessage("MFD_ER_DATE");
	}
}
else
{
	$errorMessage = GetMessage("MFD_ER_BUS")."<br />";
}

$host = CSecuritySystemInformation::getCurrentHostName();
$ip = gethostbyname($host);
if (!CSecuritySystemInformation::isIpValid($ip))
	$ip = "";

$APPLICATION->SetTitle(GetMessage("MFD_TITLE"));
?>
<div  class="bx-gadgetsadm-list-table-layout">
	<div class="bx-gadgets-content">
		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content">
				<div class="adm-detail-title adm-detail-title-colored"><?=GetMessage("MFD_1");?></div>
				<div class="ddos-info-container">
					<img src="/bitrix/images/security/ddos-img.png" alt="">
					<div class="ddos-info">
						<div class="ddos-info-container-title"><?=GetMessage("MFD_2");?></div>
						<?=GetMessage("MFD_3");?>
					</div>
				</div>
				<div class="adm-detail-content-item-block">
					<div class="ddos-title-ribbon">
						<span><?=GetMessage("MFD_4");?></span>
					</div>
					<span class="ddos-action"><?=GetMessage("MFD_5");?></span>
					<div class="ddos-conditions">
						<div class="ddos-conditions-item">
							<div class="ddos-icon-container">
								<img src="/bitrix/images/security/ddos-img2.png" alt="">
							</div>
							<span><?=GetMessage("MFD_8");?></span>
						</div>
						<div class="ddos-conditions-item">
							<div class="ddos-icon-container">
								<img src="/bitrix/images/security/ddos-img3.png" alt="">
							</div>
							<span><?=GetMessage("MFD_9");?></span>
						</div>
						<div class="ddos-footnote"><?=GetMessage("MFD_10");?></div>
					</div>
					<div class="ddos-info-container ddos-info-form-container">
						<div class="ddos-info-container-title"><?=GetMessage("MFD_11");?></div>
						<?=GetMessage("MFD_12");?>
						<a name="ddf"></a>
						<?if($errorMessage <> '')
						{
							?><div id="error" style="color:red; font-weight: bold;"><?=htmlspecialcharsBack($errorMessage)?></div><?
						}

						if($_REQUEST["ok"] == "y")
						{
							?><div id="ok" style="color: #859f4a; font-weight: bold;"><br /><br /><?=GetMessage("MFD_OK");?></div><?
						}
						else
						{
							?>
							<div class="ddos-form-container">
								<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>#ddf" id="ddos">
									<?=bitrix_sessid_post()?>
									<input type="hidden" name="DD" value="Y">
									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="DOMAIN"><?=GetMessage("MFD_13");?><span class="required">*</span></label></span>
									<input type="text" placeholder="www.site.ru" name="DOMAIN" id="DOMAIN" value="<?=htmlspecialcharsBx($_POST["DOMAIN"] <> '' ? $_POST["DOMAIN"] : $host)?>"></span></div>

									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="IP"><?=GetMessage("MFD_14");?><span class="required">*</span></label></span>
									<input type="text" placeholder="0.0.0.0" name="IP" id="IP" value="<?=htmlspecialcharsBx($_POST["IP"] <> '' ? $_POST["IP"] : $ip)?>"></span></div>

									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="NAME"><?=GetMessage("MFD_15");?><span class="required">*</span></label></span>
									<input type="text" name="NAME" id="NAME" value="<?=htmlspecialcharsBx($_POST["NAME"] <> '' ? $_POST["NAME"] : "")?>"></span></div>

									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="CONTACT_PERSON"><?=GetMessage("MFD_16");?></label></span>
									<input type="text" name="CONTACT_PERSON" id="CONTACT_PERSON" value="<?=htmlspecialcharsBx($_POST["CONTACT_PERSON"] <> '' ? $_POST["CONTACT_PERSON"] : $USER->GetFullName())?>"></span></div>

									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="EMAIL"><?=GetMessage("MFD_17");?><span class="required">*</span></label></span>
									<input type="text" placeholder="email@site.ru" name="EMAIL" id="EMAIL" value="<?=htmlspecialcharsBx($_POST["EMAIL"] <> '' ? $_POST["EMAIL"] : $USER->GetEmail())?>"></span></div>

									<div class="ddos-form-field-container"><span class="ddos-input-container"><span class="ddos-label-container"><label for="PHONE"><?=GetMessage("MFD_18");?></label></span>
									<input type="text" placeholder="+7 " name="PHONE" id="PHONE" value="<?=htmlspecialcharsBx($_POST["PHONE"] <> '' ? $_POST["PHONE"] : "")?>"></span></div>

									<a href="javascript:void(0)" onclick="BX('ddos').submit();" class="ddos-btn"><span class="ddos-btn-main"><?=GetMessage("MFD_19");?></span><span class="ddos-btn-arrow"><span></span></span></a>
								</form>
							</div>
							<?
						}
						?>
					</div>
					<div class="ddos-footnote"><?=GetMessage("MFD_20");?></div>
					<?=GetMessage("MFD_21");?>
				</div>
			</div>
			<div class="adm-detail-content-btns-wrap">
				<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>

			</div>
		</div>

	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>