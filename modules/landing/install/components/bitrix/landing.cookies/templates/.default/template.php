<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\Bitrix\Main\UI\Extension::load(
	['ui.buttons', 'ui.link', 'ui.switcher']
);
?>

<?if ($arParams['USE'] == 'Y'):?>
	<div class="bx-landing-cookies-popup" id="bx-landing-cookies-popup">
		<div class="bx-landing-cookies-popup-title"><?= Loc::getMessage('LANDING_TPL_COOKIES_HEADER');?></div>
		<div class="bx-landing-cookies-popup-content">
			<div class="bx-landing-cookies-main-agreement"></div>
			<div class="bx-landing-cookies-popup-subtitle"><?= Loc::getMessage('LANDING_TPL_COOKIES_AGREEMENTS_HEADER');?></div>
			<div class="bx-landing-cookies-popup-subtitle-detail"><?= Loc::getMessage('LANDING_TPL_COOKIES_AGREEMENTS_LABEL');?></div>

			<div>
				<div class="bx-landing-cookies-main-agreement-block">
					<span class="bx-landing-cookies-main-agreement-block-name"><?= Loc::getMessage('LANDING_TPL_COOKIES_ANALYTIC_AGREEMENTS');?></span>
					<span class="bx-landing-cookies-switcher" data-type="analytic"><?= Loc::getMessage('LANDING_TPL_COOKIES_SWITCHER_OFF');?></span>
				</div>
				<div class="bx-landing-cookies-analytic-agreements"></div>
			</div>

			<div>
				<div class="bx-landing-cookies-main-agreement-block">
					<span class="bx-landing-cookies-main-agreement-block-name"><?= Loc::getMessage('LANDING_TPL_COOKIES_TECHNICAL_AGREEMENTS');?></span>
				</div>
				<div class="bx-landing-cookies-technical-agreements" data-type="technical"></div>
			</div>

			<div>
				<div class="bx-landing-cookies-main-agreement-block">
					<span class="bx-landing-cookies-main-agreement-block-name"><?= Loc::getMessage('LANDING_TPL_COOKIES_OTHER_AGREEMENTS');?></span>
				</div>
				<div class="bx-landing-cookies-other-agreements" data-type="other"></div>
			</div>

		</div>
		<div class="bx-landing-cookies-popup-footer">
			<button class="ui-btn ui-btn-lg ui-btn-primary ui-btn-round bx-landing-cookies-button-save">
				<?= Loc::getMessage('LANDING_TPL_COOKIES_ACCEPT');?>
			</button>
			<button class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round bx-landing-cookies-button-cancel">
				<?= Loc::getMessage('LANDING_TPL_COOKIES_DECLINE');?>
			</button>
		</div>
		<span class="bx-landing-cookies-button-close"></span>
	</div>
	<div class="bx-landing-cookies-popup-warning" id="bx-landing-cookies-popup-warning">
		<div class="bx-landing-cookies-popup-warning-inner">
			<div class="bx-landing-cookies-popup-warning-left">
				<span class="bx-landing-cookies-popup-warning-text"><?= $arResult['AGREEMENT']['LABEL_TEXT'];?></span>
				<span class="bx-landing-cookies-popup-warning-link" id="bx-landing-cookies-opt-link"><?= Loc::getMessage('LANDING_TPL_COOKIES_DETAIL_LINK');?></span>
			</div>
			<div class="bx-landing-cookies-popup-warning-right">
				<span class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round" id="bx-landing-cookies-opt"><?= Loc::getMessage('LANDING_TPL_COOKIES_OPT');?></span>
				<span class="ui-btn ui-btn-lg ui-btn-primary ui-btn-round" id="bx-landing-cookies-accept"><?= Loc::getMessage('LANDING_TPL_COOKIES_ACCEPT');?></span>
			</div>
		</div>
	</div>
	<div class="bx-landing-cookies-popup-notice" id="bx-landing-cookies-popup-notice"
		 style="<?= ($arParams['POSITION'] == 'bottom_right') ? 'right: 15px;' : 'left: 75px; bottom: 23px;';?>
				 background:<?= $arParams['COLOR_BG'];?>;
				 color:<?= $arParams['COLOR_TEXT'];?>;">
		<div class="bx-landing-cookies-popup-notice-svg-wrap">
			<svg style="fill:<?= $arParams['COLOR_TEXT'];?>;" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="#FFF" class="bx-landing-cookies-popup-notice-svg">
				<path fill-rule="evenodd" d="M7.328.07c.463 0 .917.043 1.356.125.21.04.3.289.228.49a1.5 1.5 0 001.27 1.99h.001a.22.22 0 01.213.243 3.218 3.218 0 003.837 3.453c.18-.035.365.078.384.26A7.328 7.328 0 117.329.07zm.263 10.054a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854zM3.697 7.792a.884.884 0 100 1.769.884.884 0 000-1.769zm5.476-.488a.884.884 0 100 1.768.884.884 0 000-1.768zM5.806 3.628a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854z"></path>
			</svg>
		</div>
		<span class="bx-landing-cookies-popup-notice-text-wrap">
			<span class="bx-landing-cookies-popup-notice-text">Cookies</span>
		</span>

	</div>
<?endif;?>

<script>
	// don't use BX.ready here
	window.addEventListener('load', function()
	{
		new BX.Landing.Cookies({
			enable: <?= $arParams['USE'] == 'Y' ? 'true' : 'false';?>,
			siteId: <?= $arParams['SITE_ID'];?>,
			availableCodes: <?= json_encode($arResult['AVAILABLE_AGREEMENTS']);?>,
			idButtonOpt: 'bx-landing-cookies-opt',
			idButtonOptLink: 'bx-landing-cookies-opt-link',
			idButtonAccept: 'bx-landing-cookies-accept',
			idAgreementPopup: 'bx-landing-cookies-popup',
			idAgreementSmallPopup: 'bx-landing-cookies-popup-warning',
			idCookiesNotice: 'bx-landing-cookies-popup-notice',
			classNameMainAgreement: 'bx-landing-cookies-main-agreement',
			classNameAnalyticAgreements: 'bx-landing-cookies-analytic-agreements',
			classNameTechnicalAgreements: 'bx-landing-cookies-technical-agreements',
			classNameOtherAgreements: 'bx-landing-cookies-other-agreements',
			classNameButtonSave: 'bx-landing-cookies-button-save',
			classNameButtonCancel: 'bx-landing-cookies-button-cancel',
			classNameButtonClose: 'bx-landing-cookies-button-close',
			classNameCookiesSwitcher: 'bx-landing-cookies-switcher',
			agreementAjaxPath: '/bitrix/services/main/ajax.php',
			messages: {
				acceptAll: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_ACCEPT'))?>',
				acceptModified: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_ACCEPT_MODIFIED'))?>',
				declineAll: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_DECLINE'))?>',
				declineModified: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_DECLINE_MODIFIED'))?>',
				switcherOn: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_SWITCHER_ON'))?>',
				switcherOff: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COOKIES_SWITCHER_OFF'))?>'
			}
		});
	});
</script>
