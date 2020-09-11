<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$requestDomainName = $this->getComponent()->request('param');

if ($arResult['IS_FREE_DOMAIN'] == 'Y')
{
	$arResult['~DOMAIN_NAME'] = '';
	$arResult['DOMAIN_NAME'] = '';
}
?>
<div class="landing-domain-block landing-domain-block-private">
	<div class="landing-domain-block-title"><?= Loc::getMessage('LANDING_TPL_PRIVATE_SUBTITLE');?></div>
	<div class="landing-domain-block-label"><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_NAME');?></div>
	<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
		<div class="ui-ctl-ext-after ui-ctl-icon-loader" id="domain-edit-loader" style="display: none;"></div>
		<input type="text" name="param" value="<?= \htmlspecialcharsbx($requestDomainName ? $requestDomainName : $arResult['DOMAIN_NAME']);?>" <?
			?>id="domain-edit-name" class="ui-ctl-element" placeholder="mydomain.tld">
	</div>
	<div class="landing-domain-alert" id="domain-edit-message" style="display: none;"></div>
	<div class="landing-domain-block-guide">
		<div class="landing-domain-block-guide-title"><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_INSTRUCT');?></div>
		<table id="domain-edit-dnsinfo" class="landing-domain-table">
			<tr class="landing-domain-table-header">
				<td>
					<span class="landing-domain-table-header-text"><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_DNS_1');?></span>
				</td>
				<td>
					<span class="landing-domain-table-header-text"><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_DNS_2');?></span>
				</td>
				<td>
					<span class="landing-domain-table-header-text"><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_DNS_3');?></span>
				</td>
			</tr>
			<tr class="landing-domain-table-content">
				<td>
					<?= $arResult['~DOMAIN_NAME'] ? $arResult['~DOMAIN_NAME'] : 'landing.mydomain';?>
				</td>
				<td>CNAME</td>
				<td><?= $arResult['CNAME'];?></td>
			</tr>
			<tr class="landing-domain-table-content">
				<td>
					<?= $arResult['~DOMAIN_NAME'] ? $arResult['~DOMAIN_NAME'] : 'landing.mydomain.ru';?>
				</td>
				<td>A</td>
				<td><?= $arResult['IP_FOR_DNS'];?></td>
			</tr>
		</table>
	</div>
	<div class="ui-alert ui-alert-warning">
		<span class="ui-alert-message">
			<strong><?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_ALERT_AAA_HEADER');?></strong>
			<?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_ALERT_AAA_TEXT');?>
			<?if ($helpUrl = \Bitrix\Landing\Help::getHelpUrl('DOMAIN_EDIT')):?>
				<a href="<?= $helpUrl;?>" target="_blank">
					<?= Loc::getMessage('LANDING_TPL_PRIVATE_DOMAIN_ALERT_AAA_HELP');?>
				</a>
			<?endif;?>
		</span>
	</div>
</div>
<button type="submit" class="ui-btn ui-btn-primary" id="domain-edit-submit">
	<?= Loc::getMessage('LANDING_TPL_SAVE');?>
</button>

<script>
	BX.ready(function()
	{
		new BX.Landing.SiteDomainPrivate({
			domainId: <?= $arResult['DOMAIN_ID'];?>,
			domainName: '<?= \CUtil::jsEscape($arResult['~DOMAIN_NAME']);?>',
			idDomainName: BX('domain-edit-name'),
			idDomainMessage: BX('domain-edit-message'),
			idDomainLoader: BX('domain-edit-loader'),
			idDomainDnsInfo: BX('domain-edit-dnsinfo'),
			idDomainSubmit: BX('domain-edit-submit'),
			idDomainErrorAlert: BX('domain-error-alert')
		});
	});
</script>