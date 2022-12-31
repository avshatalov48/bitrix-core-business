<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Landing\Manager;
use Bitrix\Main\UI\Extension;

/** @var array $site */
/** @var array $arResult */

Asset::getInstance()->addJS(
	'/bitrix/components/bitrix/landing.site_domain/templates/.default/script.js'
);
Loc::loadMessages(
	Manager::getDocRoot() . '/bitrix/components/bitrix/landing.site_domain/templates/.default/template.php'
);
Extension::load(['ui.hint']);
?>

<input type="hidden" name="SAVE_SITE" value="Y" />
<table id="landing-sm-content-table" class="landing-sm-content-table">
	<tr>
		<td></td>
		<td colspan="2">
			<div class="ui-ctl-label-text">
				<?= Loc::getMessage('LANDING_TPL_CURRENT_ADDRESS') ?>
				<span data-hint="<?= Loc::getMessage('LANDING_TPL_DOMAIN_RULES_B24') ?>" data-hint-html></span>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="landing-sm-content-table-num">1</div>
		</td>
		<td>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input autocomplete="off" type="text" name="SUBDOMAIN" class="ui-ctl-element" id="domain-edit-name" value="<?= $site['SUBDOMAIN_NAME'] ? $site['SUBDOMAIN_NAME'] : $site['DOMAIN_NAME'];?>" placeholder="<?= Loc::getMessage('LANDING_TPL_PLACEHOLDER_DOMAIN_NAME');?>">
				<div class="ui-ctl-ext-after ui-ctl-icon-loader" id="domain-edit-loader" hidden></div>
				<div class="landing-domain-alert" id="domain-edit-message" hidden></div>
				<div class="domain-edit-length" id="domain-edit-length" hidden></div>
			</div>
		</td>
		<td>
			<div class="landing-sm-content-table-domain"><?= $site['POSTFIX'];?></div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_TPL_FORM_TITLE');?></div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="landing-sm-content-table-num">2</div>
		</td>
		<td colspan="2">
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input autocomplete="off" type="text" name="COMPANY" class="ui-ctl-element" value="<?= \htmlspecialcharsbx($arResult['CRM_CONTACTS']['COMPANY'] ?? '');?>" placeholder="<?= Loc::getMessage('LANDING_TPL_PLACEHOLDER_COMPANY');?>">
			</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<div class="ui-ctl-label-text"><?= Loc::getMessage('LANDING_TPL_FORM_PHONE');?></div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="landing-sm-content-table-num">3</div>
		</td>
		<td colspan=2>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
				<input autocomplete="off" type="text" name="PHONE" class="ui-ctl-element" value="<?= \htmlspecialcharsbx($arResult['CRM_CONTACTS']['PHONE'] ?? '');?>" placeholder="<?= Loc::getMessage('LANDING_TPL_PLACEHOLDER_PHONE');?>">
			</div>
		</td>
	</tr>
</table>

<div class="landing-sm-content-bottom-info"><?= Loc::getMessage('LANDING_TPL_CHANGE_INFO');?></div>

<script>
	BX.ready(function()
	{
		BX.message({
			LANDING_TPL_ERROR_DOMAIN_EXIST: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST'));?>',
			LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST_DELETED'));?>',
			LANDING_TPL_ERROR_DOMAIN_EMPTY: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));?>',
			LANDING_TPL_ERROR_DOMAIN_WRONG_NAME: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_NAME'));?>',
			LANDING_TPL_ERROR_DOMAIN_WRONG_LENGTH: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_LENGTH'));?>',
			LANDING_TPL_ERROR_DOMAIN_WRONG_SYMBOL_COMBINATIONS: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_SYMBOL_COMBINATIONS')) ?>',
			LANDING_TPL_ERROR_DOMAIN_WRONG_DOMAIN_LEVEL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_WRONG_DOMAIN_LEVEL')) ?>',
			LANDING_TPL_DOMAIN_LENGTH_LIMIT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_LENGTH_LIMIT'));?>',
			LANDING_TPL_ALERT_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ALERT_TITLE'));?>',
			LANDING_TPL_DOMAIN_AVAILABLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_AVAILABLE'));?>',
			LANDING_TPL_ERROR_DOMAIN_INCORRECT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_INCORRECT'));?>',
			LANDING_TPL_ERROR_DOMAIN_CHECK_DASH: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH'));?>',
			LANDING_TPL_ERROR_DOMAIN_CHECK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK', ['#TLD#' => strtolower($arResult['TLD'][0])]));?>'
		});
	});
</script>

<script>
	BX.ready(function()
	{
		new BX.Landing.SiteDomain.Bitrix24({
			domainId: <?= $site['DOMAIN_ID'];?>,
			domainName: '<?= \CUtil::jsEscape($site['DOMAIN_NAME']);?>',
			domainPostfix: '<?= $site['POSTFIX']?>',
			idDomainName: BX('domain-edit-name'),
			idDomainMessage: BX('domain-edit-message'),
			idDomainLoader: BX('domain-edit-loader'),
			idDomainLength: BX('domain-edit-length'),
			idDomainSubmit: BX('landing-master-next'),
			tld: <?= \CUtil::phpToJSObject($arResult['TLD'][0])?>,
		});
	});

	BX.UI.Hint.init(BX('landing-sm-content-table'));
</script>