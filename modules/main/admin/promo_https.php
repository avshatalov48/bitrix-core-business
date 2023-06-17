<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/promo_https.css">');
Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("HTTPS_PROMO__ADMIN_TITLE"));
require_once ($_SERVER['DOCUMENT_ROOT'].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

	<div class="adm-promo-https-wrapper">

		<div class="adm-promo-https-container">
			<div class="adm-promo-https-description">
				<div class="adm-promo-https-description-item adm-promo-https-subtitle"><?=Loc::getMessage("HTTPS_PROMO__SUBTITLE")?></div>
				<div class="adm-promo-https-description-item adm-promo-https-title"><?=Loc::getMessage("HTTPS_PROMO__TITLE")?></div>
				<div class="adm-promo-https-description-logo"></div>
			</div>
		</div><!--adm-promo-https-container-->

		<div class="adm-promo-https-container">
			<div class="adm-promo-https-content-block adm-promo-https-block-1">
				<div class="adm-promo-https-content-title"><?=Loc::getMessage("HTTPS_PROMO__PREPARE_SITE")?></div>
				<ul class="adm-promo-https-content-list">
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__CHANGE_LINKS_NEW")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__CHECK_PROTOCOL_NEW")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__EXTERNAL_SCRIPTS")?></li>
				</ul>
			</div>
		</div><!--adm-promo-https-container-->

		<div class="adm-promo-https-container">
			<div class="adm-promo-https-content-block adm-promo-https-block-2">
				<div class="adm-promo-https-content-title"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_INSTALL_NEW")?></div>
				<ul class="adm-promo-https-content-list">
					<li class="adm-promo-https-content-list-item">
						<?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_CHOICE_NEW")?>
						<div class="adm-promo-https-content-info">
							<div class="adm-promo-https-content-info-description"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATES_FEATURES")?>:</div>
							<ul class="adm-promo-https-content-info-list">
								<li class="adm-promo-https-content-info-list-item">
									<span class="adm-promo-https-content-info-text-bold"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_REGULAR")?></span>
									<span class="adm-promo-https-content-info-text-nobold"> (<?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_REGULAR_ALT")?>)</span>
									<span class="span adm-promo-https-content-info-text-regular"><?=Loc::getMessage("HTTPS_PROMO__TO_MOST_SITES")?></span>
								</li>
								<li class="adm-promo-https-content-info-list-item">
									<span class="adm-promo-https-content-info-text-bold"><?=Loc::getMessage("HTTPS_PROMO__WILDCART")?></span>
									<span class="adm-promo-https-content-info-text-nobold">(<?=Loc::getMessage("HTTPS_PROMO__TO_SEVERAL_DOMAINS")?>)</span>
									<span class="adm-promo-https-content-info-text-regular"><?=Loc::getMessage("HTTPS_PROMO__TO_SEVERAL_DOMAINS_ALT")?></span>
								</li>
								<li class="adm-promo-https-content-info-list-item">
									<span class="adm-promo-https-content-info-text-bold"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_IDN")?></span>
									<span class="adm-promo-https-content-info-text-regular"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_IDN_ALT")?></span>
								</li>
								<li class="adm-promo-https-content-info-list-item">
									<span class="adm-promo-https-content-info-text-bold"><?=Loc::getMessage("HTTPS_PROMO__MULTIDOMAINS_CERTIFICATE")?></span>
									<span class="adm-promo-https-content-info-text-regular"><?=Loc::getMessage("HTTPS_PROMO__MULTIDOMAINS_CERTIFICATE_ALT")?></span>
								</li>
							</ul>
							<div class="adm-promo-https-content-info-description"><?=Loc::getMessage("HTTPS_PROMO__CERTIFICATE_VENDORS")?>:</div>
							<div class="adm-promo-https-content-info-advertising">
								<a href="https://www.globalsign.com/ru-ru/" class="adm-promo-https-content-info-advertising-item icon-global-sign"></a>
								<a href="https://ru.comodo.com/" class="adm-promo-https-content-info-advertising-item icon-comodo"></a>
								<a href="https://www.geotrust.com/" class="adm-promo-https-content-info-advertising-item icon-geotrust"></a>
								<a href="https://www.thawte.com/ssl/" class="adm-promo-https-content-info-advertising-item icon-thawte"></a>
								<a href="https://www.symantec.com/ru/ru/website-security/" class="adm-promo-https-content-info-advertising-item icon-symantec"></a>
								<a href="https://ssl.trustwave.com/" class="adm-promo-https-content-info-advertising-item icon-trustwave"></a>
							</div>
						</div>
					</li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__INSTALL_CERTIFICATE_NEW")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__CHECK_ACTIVE_NEW")?></li>
				</ul>
			</div>
		</div><!--adm-promo-https-container-->

		<div class="adm-promo-https-container">
			<div class="adm-promo-https-content-block adm-promo-https-block-3">
				<div class="adm-promo-https-content-title"><?=Loc::getMessage("HTTPS_PROMO__SITE_SETTINGS_NEW")?></div>
				<ul class="adm-promo-https-content-list">
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__HOST_AND_ROBOTS_NEW")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__REDIRECT_NEW")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__SITEMAP_NEW")?></li>
				</ul>
			</div>
		</div><!--adm-promo-https-container-->

		<div class="adm-promo-https-container">
			<div class="adm-promo-https-content-block adm-promo-https-block-4">
				<div class="adm-promo-https-content-title"><?=Loc::getMessage("HTTPS_PROMO__SEARCHERS_NEW")?></div>
				<ul class="adm-promo-https-content-list">
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__GOOGLE")?></li>
					<li class="adm-promo-https-content-list-item"><?=Loc::getMessage("HTTPS_PROMO__YANDEX_NEW")?></li>
				</ul>
			</div>
		</div><!--adm-promo-https-container-->

	</div><!--bx-gadgetsadm-list-table-layout-->

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");