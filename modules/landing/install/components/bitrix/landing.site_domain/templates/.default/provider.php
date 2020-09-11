<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

$requestDomainName = $this->getComponent()->request('param');

if ($arResult['IS_FREE_DOMAIN'] != 'Y')
{
	$arResult['~DOMAIN_NAME'] = '';
	$arResult['DOMAIN_NAME'] = '';
}
?>

<div class="landing-domain-block">
	<div class="landing-domain-block-title"><?= Loc::getMessage('LANDING_TPL_FREE_SUBTITLE');?></div>
	<div class="landing-domain-block-content">
		<div class="landing-domain-block-info" id="landing-domain-block-info">
			<div class="landing-domain-block-info-title"><?= Loc::getMessage('LANDING_TPL_FREE_INFO_TITLE');?></div>
			<div class="landing-domain-block-info-text"><?= Loc::getMessage('LANDING_TPL_FREE_INFO_TEXT');?></div>
			<a href="#" class="ui-link ui-link-secondary ui-link-dashed" id="landing-domain-block-info-close-link"><?= Loc::getMessage('LANDING_TPL_LINK_HIDE');?></a>
			<span class="landing-domain-block-info-close" id="landing-domain-block-info-close-icon"></span>
		</div>
		<div class="landing-domain-block-select">
			<div class="landing-domain-block-input">
				<div class="landing-domain-block-input-inner">
					<span class="landing-domain-block-label">
					<?= Loc::getMessage('LANDING_TPL_FREE_TITLE_SELECT1', ['#TLD#' => '.' . mb_strtoupper(implode(', .', $arResult['TLD']))]);?>
					</span>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<div class="ui-ctl-ext-after ui-ctl-icon-loader" id="domain-edit-loader" style="display: none;"></div>
						<input type="text" name="param" value="<?= \htmlspecialcharsbx($requestDomainName ? $requestDomainName : $arResult['DOMAIN_NAME']);?>" <?
							?>id="domain-edit-name" class="ui-ctl-element" placeholder="mysite.<?= $arResult['TLD'][0];?>">
					</div>
				</div>
				<button class="ui-btn ui-btn-light-border landing-domain-edit-check-btn" id="domain-edit-check">
					<?= Loc::getMessage('LANDING_TPL_CHECK');?>
				</button>
			</div>
			<div class="landing-domain-alert" id="domain-edit-message" style="display: none;"></div>
			<div class="landing-domain-block-available" style="display: none;">
				<div class="landing-domain-block-available-title"><?= Loc::getMessage('LANDING_TPL_FREE_CHOOSE_ANOTHER_NAME');?></div>
				<div id="domain-edit-another" class="landing-domain-block-available-list-wrap">
					...domains...
				</div>
				<div class="landing-domain-block-available-btn-wrap">
					<button class="ui-btn ui-btn-light-border landing-domain-block-available-btn" id="domain-edit-another-more" type="button" style="display: none;">
						<?= Loc::getMessage('LANDING_TPL_FREE_CHOOSE_ANOTHER_NAME_MORE');?>
					</button>
				</div>
			</div>
		</div>
		<div class="landing-domain-edit-agreement">
			<?if (Manager::getZone() != 'ua'):?>
			<?= Loc::getMessage('LANDING_TPL_AGREE_BY_SUBMIT', [
				'#LINK1#' => '<a href="https://www.bitrix24.ru/about/domainfree.php" target="_blank">',
				'#LINK2#' => '</a>'
			]);?>
			<?endif;?>
		</div>
	</div>
</div>
<button type="submit" class="ui-btn ui-btn-primary" id="domain-edit-submit">
	<?= Loc::getMessage('LANDING_TPL_GET_FREE');?>
</button>

<script>
	BX.ready(function()
	{
		new BX.Landing.SiteDomainFree({
			idDomainName: BX('domain-edit-name'),
			idDomainCheck: BX('domain-edit-check'),
			idDomainSubmit: BX('domain-edit-submit'),
			idDomainAnother: BX('domain-edit-another'),
			idDomainAnotherMore: BX('domain-edit-another-more'),
			idDomainMessage: BX('domain-edit-message'),
			idDomainLoader: BX('domain-edit-loader'),
			idDomainErrorAlert: BX('domain-error-alert'),
			saveBlocker: <?= !$arResult['FEATURE_FREE_AVAILABLE'] ? 'true' : 'false';?>,
			saveBlockerCallback: function() {
				<?= \Bitrix\Landing\Restriction\Manager::getActionCode('limit_free_domen');?>
			},
			maxVisibleSuggested: 10,
			tld: <?= \CUtil::PhpToJSObject($arResult['TLD'])?>,
			promoBlock: BX('landing-domain-block-info'),
			promoCloseIcon: BX('landing-domain-block-info-close-icon'),
			promoCloseLink: BX('landing-domain-block-info-close-link')
		});
	});
</script>