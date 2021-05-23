<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var array $arParams */
Extension::load(
	[
		"ui.hint",
		"seo.ads.client_selector",
		"ui.icons",
		"ui.buttons",
		"ui.buttons.icons",
		"ui.notification",
		"ui.sidepanel-content"
	]
);
$containerNodeId = $arParams['CONTAINER_NODE_ID'];
$destroyEventName = $arParams['JS_DESTROY_EVENT_NAME'];
$accountId = $arParams['ACCOUNT_ID'];
$clientId = $arParams['CLIENT_ID'];
$creativeId = $arParams['CREATIVE_ID']??'';
$provider = $arParams['PROVIDER'];
$titleNodeSelector = $arParams['~TITLE_NODE_SELECTOR'];
$type = htmlspecialcharsbx($arParams['SUBTYPE']);
$typeUpped = mb_strtoupper($type);

$namePrefix = htmlspecialcharsbx($arParams['INPUT_NAME_PREFIX']);

$multiClients = array_key_exists('CLIENTS', $arParams['PROVIDER']);
?>
<script id="template-crm-ads-account-settings" type="text/html">
	<div class="crm-ads-rtg-popup-settings">
		<div class="crm-ads-rtg-popup-settings-title"><?=Loc::getMessage('CRM_ADS_RTG_TITLE')?>:</div>
	</div>
	<div data-bx-ads-block="loading" style="display: none;" class="crm-ads-rtg-popup-settings">
		<div class="crm-ads-rtg-user-loader-item">
			<div class="crm-ads-rtg-loader">
				<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
					<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
				</svg>
			</div>
		</div>
	</div>

	<div data-bx-ads-block="login" style="display: none;" class="crm-ads-rtg-popup-settings">
		<div class="crm-ads-new-campaign-item-block">
			<div class="crm-ads-new-campaign-item-content">
				<div class="crm-ads-new-campaign-item-content-inner">
					<div class="ui-slider-section ui-slider-section-icon">
						<div class="ui-icon ui-slider-icon crm-ads-new-campaign-item-instagram">
							<i></i>
						</div>
						<div class="ui-slider-content-box">
							<div class="ui-slider-heading-3"><?=Loc::getMessage('CRM_ADS_RTG_TITLE_'.mb_strtoupper($type))?></div>
							<p class="ui-slider-paragraph-2"><?=Loc::getMessage('CRM_ADS_RTG_CONNECT_DESCRIPTION')?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?=$type?>">
			<span
				id="seo-ads-login-btn"
				onclick="BX.util.popup('<?=htmlspecialcharsbx($provider['AUTH_URL'])?>', 800, 600);"
				class="webform-small-button webform-small-button-transparent">
				<?=Loc::getMessage('CRM_ADS_RTG_LOGIN')?>
			</span>
		</div>
	</div>


	<div data-bx-ads-block="auth" style="display: none;">
		<div class="crm-ads-rtg-popup-settings">
			<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?=$type?>">
				<?if ($multiClients):?>
					<div data-bx-ads-client="" class="crm-ads-rtg-popup-client"></div>
				<?else:?>
					<div class="crm-ads-rtg-popup-social-avatar">
						<div data-bx-ads-auth-avatar="" class="crm-ads-rtg-popup-social-avatar-icon"></div>
					</div>
					<div class="crm-ads-rtg-popup-social-user">
						<a target="_top" data-bx-ads-auth-link="" data-bx-ads-auth-name="" class="crm-ads-rtg-popup-social-user-link" title=""></a>
					</div>
					<div class="crm-ads-rtg-popup-social-shutoff">
						<span data-bx-ads-auth-logout="" class="crm-ads-rtg-popup-social-shutoff-link"><?=Loc::getMessage('CRM_ADS_RTG_LOGOUT')?></span>
					</div>
				<?endif?>
				<input type="hidden" data-bx-ads-client-input="" name="<?=$namePrefix?>CLIENT_ID" value="<?=$clientId?>">
			</div>
		</div>
	</div>

	<div data-bx-ads-block="main" style="display: none;">
		<div class="crm-ads-rtg-popup-settings crm-ads-rtg-popup-settings-wrapper">
			<div class="crm-ads-rtg-popup-settings" style="<?=(!$provider['IS_SUPPORT_ACCOUNT'] ? 'display: none;' : '')?>">
				<div class="crm-ads-rtg-popup-settings-title-full"><?=Loc::getMessage('CRM_ADS_RTG_SELECT_ACCOUNT')?>:</div>

				<table class="crm-ads-rtg-table">
					<tr>
						<td>
							<select disabled name="<?=$namePrefix?>ACCOUNT_ID" data-bx-ads-account="" class="crm-ads-rtg-popup-settings-dropdown<?
							if ($provider['IS_SUPPORT_ADD_AUDIENCE']):?> crm-ads-rtg-popup-settings-dropdown-narrow<?endif?>">
							</select>
						</td>
						<td>
							<div data-bx-ads-account-loader="" class="crm-ads-rtg-loader-sm" style="display: none;">
								<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
									<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="crm-ads-rtg-popup-settings crm-ads-rtg-popup-settings-wrapper">
			<div class="crm-ads-rtg-popup-settings"
				style="<?=(!$provider['IS_SUPPORT_ACCOUNT'] ? 'display: none;' : '')?>">
				<div class="crm-ads-rtg-popup-settings-title-full">
					<?=Loc::getMessage('CRM_ADS_RTG_SELECT_INSTAGRAM_ACCOUNT')?>:
				</div>

				<table class="crm-ads-rtg-table">
					<tr>
						<td>
							<select disabled name="<?=$namePrefix?>INSTAGRAM_ACCOUNT_ID"
								data-bx-ads-instagram-account=""
								class="crm-ads-rtg-popup-settings-dropdown
							crm-ads-rtg-popup-settings-dropdown-narrow">
							</select>
						</td>
						<td>
							<div
								data-bx-ads-instagram-account-loader=""
								class="crm-ads-rtg-loader-sm" style="display: none;">
								<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
									<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none"
										stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?if ($multiClients):?>
			<span class="ui-btn ui-btn-light-border ui-btn-xs" data-bx-ads-client-add-btn=""><?= Loc::getMessage("CRM_ADS_RTG_ADD_CLIENT_BTN") ?></span>
			<br><br>
		<?endif?>
	</div>

</script>

<script>
	BX.ready(function () {

		var r = (Date.now()/1000|0);
		BX.loadCSS('<?=$this->GetFolder()?>/configurator.css?' + r);
		BX.loadScript('<?=$this->GetFolder()?>/configurator.js?' + r, function()
		{
			var params = <?=\Bitrix\Main\Web\Json::encode(array(
				'provider' => $provider,
				'multiClients' => $multiClients,
				'clientId' => $clientId,
				'accountId' => $accountId,
				'containerId' => $containerNodeId,
				'destroyEventName' => $destroyEventName,
				'signedParameters' => $this->getComponent()->getSignedParameters(),
				'componentName' => $this->getComponent()->getName(),
				'type' => $type,
				'campaign_url' => $arResult['CAMPAIGN_URL'],
				'mess' => array(
					'errorAction' => Loc::getMessage('CRM_ADS_RTG_ERROR_ACTION'),
					'dlgBtnClose' => Loc::getMessage('CRM_ADS_RTG_CLOSE'),
					'dlgBtnCreate' => Loc::getMessage('CRM_ADS_RTG_CREATE'),
					'dlgBtnApply' => Loc::getMessage('CRM_ADS_RTG_APPLY'),
					'dlgBtnCancel' => Loc::getMessage('CRM_ADS_RTG_CANCEL_ALT'),
					'newAudiencePopupTitle' => Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD'),
					'newAudienceNameLabel' => Loc::getMessage('CRM_ADS_RTG_NEW_AUDIENCE_NAME_LABEL'),
				)
			))?>;
			new CrmAdsConfigurator(params);
		});

	});
</script>
