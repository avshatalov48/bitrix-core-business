<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var array $arParams */

Extension::load([
	'ui.design-tokens',
	'ui.fonts.roboto',
	'ui.hint',
	'seo.ads.client_selector',
	'seo.ads.login',
	'ui.forms'
]);

$containerNodeId = $arParams['CONTAINER_NODE_ID'];
$destroyEventName = $arParams['JS_DESTROY_EVENT_NAME'];
$accountId = $arParams['ACCOUNT_ID'];
$audienceId = $arParams['AUDIENCE_ID'];
$clientId = $arParams['CLIENT_ID'];
$autoRemoveDayNumber = $arParams['AUTO_REMOVE_DAY_NUMBER'];
$provider = $arParams['PROVIDER'];
$titleNodeSelector = $arParams['~TITLE_NODE_SELECTOR'];
$type = htmlspecialcharsbx($provider['TYPE']);
$typeUpped = mb_strtoupper($type);

$namePrefix = htmlspecialcharsbx($arParams['INPUT_NAME_PREFIX']);

$multiClients = array_key_exists('CLIENTS', $arParams['PROVIDER']);
?>
<script id="template-crm-ads-dlg-settings" type="text/html">
	<div class="crm-ads-rtg-popup-settings">
		<div class="crm-ads-rtg-popup-settings-title"><?= Loc::getMessage('CRM_ADS_RTG_TITLE') ?>:</div>
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
		<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?= $type ?>">

			<?php
			if ($type === 'google'): ?>
				<div class="crm-ads-goo-btn-container">
					<div
						id="seo-ads-login-btn"
						class="crm-ads-goo-btn"
					>
						<div class="crm-ads-goo-btn-icon"></div>
						<div class="crm-ads-goo-btn-text"><?php
							echo Loc::getMessage('CRM_ADS_RTG_LOGIN_GOOGLE') ?></div>
					</div>
				</div>
			<?php
			else: ?>
				<span
					id="seo-ads-login-btn"
					class="webform-small-button webform-small-button-transparent"
				>
					<?= Loc::getMessage('CRM_ADS_RTG_LOGIN') ?>
				</span>
			<?php
			endif; ?>
		</div>
	</div>


	<div data-bx-ads-block="auth" style="display: none;">
		<div class="crm-ads-rtg-popup-settings">
			<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?= $type ?>">
				<?php
				if ($multiClients):?>
					<div data-bx-ads-client="" class="crm-ads-rtg-popup-client"></div>
				<?php
				else:?>
					<div class="crm-ads-rtg-popup-social-avatar">
						<div data-bx-ads-auth-avatar="" class="crm-ads-rtg-popup-social-avatar-icon"></div>
					</div>
					<div class="crm-ads-rtg-popup-social-user">
						<a target="_top" data-bx-ads-auth-link="" data-bx-ads-auth-name="" class="crm-ads-rtg-popup-social-user-link" title=""></a>
					</div>
					<div class="crm-ads-rtg-popup-social-shutoff">
						<span data-bx-ads-auth-logout="" class="crm-ads-rtg-popup-social-shutoff-link"><?= Loc::getMessage('CRM_ADS_RTG_LOGOUT') ?></span>
					</div>
				<?php
				endif ?>
				<input type="hidden" data-bx-ads-client-input="" name="<?= $namePrefix ?>CLIENT_ID" value="<?= $clientId ?>">
			</div>
		</div>
	</div>


	<div data-bx-ads-block="refresh" style="display: none;">
		<div class="crm-ads-rtg-popup-settings crm-ads-rtg-popup-settings-wrapper crm-ads-rtg-popup-settings-wrapper-center">
			<?if ($type == 'yandex'):?>
				<?=Loc::getMessage('CRM_ADS_RTG_REFRESH_TEXT_' . $typeUpped)?>
			<?else:?>
				<?=Loc::getMessage('CRM_ADS_RTG_REFRESH_TEXT')?>
			<?endif;?>
			<br>
			<br>
			<span data-bx-ads-refresh-btn="" class="webform-small-button webform-small-button-transparent">
				<?= Loc::getMessage('CRM_ADS_RTG_REFRESH') ?>
			</span>
		</div>
	</div>


	<div data-bx-ads-block="main" style="display: none;">
		<div class="crm-ads-rtg-popup-settings crm-ads-rtg-popup-settings-wrapper">
			<div class="crm-ads-rtg-popup-settings" style="<?= (!$provider['IS_SUPPORT_ACCOUNT'] ? 'display: none;'
				: '') ?>">
				<div class="crm-ads-rtg-popup-settings-title-full"><?= Loc::getMessage('CRM_ADS_RTG_SELECT_ACCOUNT') ?>:</div>

				<table class="crm-ads-rtg-table">
					<tr>
						<td>
							<select disabled name="<?= $namePrefix ?>ACCOUNT_ID" data-bx-ads-account="" class="crm-ads-rtg-popup-settings-dropdown <?php
							if ($provider['IS_SUPPORT_ADD_AUDIENCE'])
								echo "crm-ads-rtg-popup-settings-dropdown-narrow"
							?>">
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

			<?php
			if ($arParams['AUDIENCE_LOOKALIKE_MODE']):?>
				<?php if (!$provider['IS_SUPPORT_CREATE_LOOKALIKE_FROM_SEGMENTS']): ?>
					<div class="crm-ads-rtg-popup-settings">
						<div class="crm-ads-rtg-popup-settings-title-full crm-ads-rtg-popup-settings-title-with-hint">
							<?= Loc::getMessage('CRM_ADS_RTG_SELECT_EXISTING_AUDIENCE') ?>:
							<span data-hint="<?= Loc::getMessage('CRM_ADS_RTG_SELECT_EXISTING_AUDIENCE_HINT') ?>" data-hint-html=""></span>
						</div>
						<table class="crm-ads-rtg-table">
							<tr>
								<td>
									<div id="audience-selector-btn"></div>
									<input type="hidden" id="audience-selector-value" name="<?= $namePrefix ?>AUDIENCE_ID" value="<?= $audienceId ?>">
								</td>
								<td>
									<div data-bx-ads-audience-loader="" class="crm-ads-rtg-loader-sm" style="display: none;">
										<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
											<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
										</svg>
									</div>
									<?php if ($provider['IS_SUPPORT_ADD_AUDIENCE']):?>
										<div>
											<span style="display: none;" class="ui-btn ui-btn-link" data-bx-ads-audience-add=""><?= Loc::getMessage(htmlspecialcharsbx("CRM_ADS_RTG_AUDIENCE_ADD")) ?></span>
										</div>
									<?php endif ?>
								</td>
							</tr>
						</table>
					</div>
				<?php endif; ?>
				<?php
				if (in_Array('AUDIENCE_SIZE', $provider['LOOKALIKE_AUDIENCE_PARAMS']['FIELDS'])):?>
					<div class="crm-ads-rtg-popup-settings">
						<div class="crm-ads-rtg-popup-settings-title-full crm-ads-rtg-popup-settings-title-with-hint">
							<?= Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_SIZE') ?>:
							<span data-hint="<?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_SIZE_HINT')) ?>" data-hint-html=""></span>
						</div>
						<br>

						<select name="<?= $namePrefix ?>AUDIENCE_SIZE" class="crm-ads-rtg-popup-settings-dropdown">
							<?php
							foreach ($provider['LOOKALIKE_AUDIENCE_PARAMS']['SIZES'] as $sizeId => $sizeName):?>
								<option value="<?= $sizeId ?>" <?php
								if ($sizeId == $arParams['AUDIENCE_SIZE']):?> selected<?php
								endif ?>><?= htmlspecialcharsbx($sizeName) ?></option>
							<?php
							endforeach; ?>
						</select>
					</div>
				<?php
				endif ?>
				<?php
				if (in_array('AUDIENCE_LOOKALIKE', $provider['LOOKALIKE_AUDIENCE_PARAMS']['FIELDS'], true)): ?>
					<div class="crm-ads-rtg-popup-settings">
						<div class="crm-ads-rtg-popup-settings-title-full crm-ads-rtg-popup-settings-title-with-hint">
							<?= Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_AUDIENCE_LOOKALIKE') ?>:
							<span data-hint="<?= Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_AUDIENCE_LOOKALIKE_HINT') ?>" data-hint-html=""></span>
						</div>
						<div class="crm-ads-rtg-range-input-wrapper">
							<input
								class="crm-ads-rtg-range-input" name="<?= $namePrefix ?>AUDIENCE_LOOKALIKE" type="range"
								min="<?= (int)$provider['LOOKALIKE_AUDIENCE_PARAMS']['AUDIENCE_LOOKALIKE']['MIN'] ?>"
								max="<?= (int)$provider['LOOKALIKE_AUDIENCE_PARAMS']['AUDIENCE_LOOKALIKE']['MAX'] ?>"
								step="1"
								<?php if ($arParams['AUDIENCE_LOOKALIKE'] !== null): ?>
								value="<?= (int)$arParams['AUDIENCE_LOOKALIKE'] ?>"
								<?php endif; ?>
							>
							<div class="crm-ads-rtg-range-input-desc">
								<div><?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_AUDIENCE_LOOKALIKE_MORE_ACCURACY')) ?></div>
								<div><?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_AUDIENCE_LOOKALIKE_MORE_COVERAGE')) ?></div>
							</div>
						</div>
					</div>
				<?php
				endif ?>
				<?php
				if (in_array('DEVICE_DISTRIBUTION', $provider['LOOKALIKE_AUDIENCE_PARAMS']['FIELDS'], true)): ?>
					<div class="crm-ads-rtg-popup-settings">
						<label class="ui-ctl ui-ctl-checkbox" style="width: max-content;">
							<input
								name="<?= $namePrefix ?>DEVICE_DISTRIBUTION" type="checkbox" class="ui-ctl-element"
								<?php if ((bool)$arParams['DEVICE_DISTRIBUTION'] === true): ?>
									checked
								<?php endif; ?>
							>
							<div class="ui-ctl-label-text"><?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_DEVICE_DISTRIBUTION')) ?></div>
							<span data-hint="<?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_DEVICE_DISTRIBUTION_HINT')) ?>" data-hint-html=""></span>
						</label>
					</div>
				<?php
				endif ?>
				<?php
				if (in_array('GEO_DISTRIBUTION', $provider['LOOKALIKE_AUDIENCE_PARAMS']['FIELDS'], true)): ?>
					<div class="crm-ads-rtg-popup-settings">
						<label class="ui-ctl ui-ctl-checkbox">
							<input name="<?= $namePrefix ?>GEO_DISTRIBUTION" type="checkbox" class="ui-ctl-element"
								<?php if ((bool)$arParams['GEO_DISTRIBUTION'] === true): ?>
									checked
								<?php endif; ?>
							>
							<div class="ui-ctl-label-text"><?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_GEO_DISTRIBUTION')) ?></div>
							<span data-hint="<?= htmlspecialcharsbx(Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_GEO_DISTRIBUTION_HINT')) ?>" data-hint-html=""></span>
						</label>
					</div>
				<?php
				endif ?>
				<?php
				if (in_Array('AUDIENCE_REGION', $provider['LOOKALIKE_AUDIENCE_PARAMS']['FIELDS'])):?>
					<div class="crm-ads-rtg-popup-settings">
						<div class="crm-ads-rtg-popup-settings-title-full">
							<?= Loc::getMessage('CRM_ADS_RTG_CREATE_LOOKALIKE_REGION') ?>:
						</div>
						<br>
						<table class="crm-ads-rtg-table">
							<tr>
								<td>
									<select disabled name="<?= $namePrefix ?>AUDIENCE_REGION" data-bx-ads-region="" class="crm-ads-rtg-popup-settings-dropdown">
									</select>
								</td>
								<td>
									<div data-bx-ads-region-loader="" class="crm-ads-rtg-loader-sm" style="display: none;">
										<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
											<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
										</svg>
									</div>
								</td>
							</tr>
						</table>
					</div>
				<?php
				endif ?>
			<?php
			else:?>
				<?php
				if ($provider['IS_SUPPORT_MULTI_TYPE_CONTACTS']):?>
					<div class="crm-ads-rtg-popup-settings">
						<div class="crm-ads-rtg-popup-settings-title-full crm-ads-rtg-popup-settings-title-with-hint">
							<?= (($type === 'vkontakte')
								? Loc::getMessage('CRM_ADS_RTG_SELECT_USER_LIST')
								: Loc::getMessage('CRM_ADS_RTG_SELECT_AUDIENCE'))
							?>:
							<?php if ($type === 'vkontakte'): ?>
								<span data-hint="<?= htmlspecialcharsbx(
									Loc::getMessage('CRM_ADS_RTG_AUDIENCE_TYPE_HINT_' . $typeUpped . '_1')
									. ' ' . Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD_HINT_' . $typeUpped . '_1',
										['#BR#' => '<br>'])
								) ?>" data-hint-html=""></span>
							<?php else: ?>
								<span data-hint="<?= htmlspecialcharsbx(
									Loc::getMessage('CRM_ADS_RTG_AUDIENCE_TYPE_HINT_' . $typeUpped)
									. ' ' . Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD_HINT_' . $typeUpped,
										['#BR#' => '<br>'])
								) ?>" data-hint-html=""></span>
							<?php endif; ?>
						</div>

					<table class="crm-ads-rtg-table">
						<tr>
							<td>
								<div id="audience-selector-btn"></div>
								<input type="hidden" id="audience-selector-value" name="<?= $namePrefix ?>AUDIENCE_ID" value="<?= $audienceId ?>">
							</td>
							<td>
								<?if ($provider['IS_SUPPORT_ADD_AUDIENCE']):?>
								<div>
									<span  style="display: none;" class="ui-btn ui-btn-link" data-bx-ads-audience-add="">
										<?= (($type === 'vkontakte')
											? Loc::getMessage("CRM_ADS_RTG_USER_LIST_ADD")
											: Loc::getMessage("CRM_ADS_RTG_AUDIENCE_ADD"))
										?>
									</span>
								</div>
								<?endif?>
							</td>
							<?if(false && !$provider['IS_ADDING_REQUIRE_CONTACTS']):?>
							<td>
								<a data-role="audience-add" class="crm-ads-rtg-popup-link" style="display: none;">
									<?=Loc::getMessage('CRM_ADS_RTG_ADD_AUDIENCE')?>
								</a>
							</td>
							<?endif;?>
						</tr>
					</table>
				</div>
				<?else:?>
				<div class="crm-ads-rtg-popup-settings">
					<div class="crm-ads-rtg-popup-settings-title-full">
						<?=Loc::getMessage('CRM_ADS_RTG_SELECT_CONTACT_DATA')?>:
						<span data-hint="<?=htmlspecialcharsbx(
							Loc::getMessage('CRM_ADS_RTG_AUDIENCE_TYPE_HINT_' . $typeUpped)
							. ' ' . Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD_HINT_' . $typeUpped, ['#BR#' => '<br>'])
						)?>" data-hint-html=""></span>
					</div>
					<table class="crm-ads-rtg-table">
						<tr>
							<td>
								<div class="crm-ads-rtg-popup-chk">
									<input id="crm_ads_checker_email" data-bx-ads-audience-checker="email" type="checkbox" class="crm-ads-rtg-popup-chk">
									<label for="crm_ads_checker_email" class="crm-ads-rtg-popup-chk-label">
										<?=Loc::getMessage('CRM_ADS_RTG_SELECT_CONTACT_DATA_EMAIL')?>
									</label>
								</div>
							</td>
							<td>
								<select name="<?=$namePrefix?>AUDIENCE_EMAIL_ID" data-bx-ads-audience="email" class="crm-ads-rtg-popup-settings-dropdown">
								</select>
							</td>
							<td>
								<div data-bx-ads-audience-loader="email" class="crm-ads-rtg-loader-sm">
									<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
										<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
									</svg>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div class="crm-ads-rtg-popup-chk">
									<input id="crm_ads_checker_phone" data-bx-ads-audience-checker="phone" type="checkbox" class="crm-ads-rtg-popup-chk">
									<label for="crm_ads_checker_phone" class="crm-ads-rtg-popup-chk-label">
										<?=Loc::getMessage('CRM_ADS_RTG_SELECT_CONTACT_DATA_PHONE')?>
									</label>
								</div>
							</td>
							<td>
								<select name="<?=$namePrefix?>AUDIENCE_PHONE_ID" data-bx-ads-audience="phone" class="crm-ads-rtg-popup-settings-dropdown">
								</select>
							</td>
							<td>
								<div data-bx-ads-audience-loader="phone" class="crm-ads-rtg-loader-sm">
									<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
										<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
									</svg>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<?endif?>
				<?if($provider['IS_SUPPORT_REMOVE_CONTACTS']):?>
				<?
				$isSelectedOnce = false;
				$sDayValues = '';
				$dayValues = array(
					'1', '2', '3', '4', '5', '6', '7',
					'10', '14', '17', '21', '28',
					'30', '45', '60', '75', '90', '180',
				);
				foreach ($dayValues as $dayValue)
				{
					$dayValue = htmlspecialcharsbx($dayValue);
					$isSelected = $autoRemoveDayNumber == $dayValue;
					$sDayValues .= '<option value="' . $dayValue .'" '
						. ($isSelected ? 'selected' : '')
						. '>' . $dayValue
						. '</option>';

					if ($isSelected)
					{
						$isSelectedOnce = true;
					}
				}
				?>
				<div data-bx-ads-audience-auto-remove="" class="crm-ads-rtg-popup-settings">
					<div class="crm-ads-rtg-popup-chk">
						<input data-bx-ads-audience-auto-remove-checker="" <?=($isSelectedOnce ? 'checked' : '')?> type="checkbox" class="crm-ads-rtg-popup-chk" id="crm_ads_checker_autorem">
						<label for="crm_ads_checker_autorem" class="crm-ads-rtg-popup-chk-label">
								<?if ($type == 'yandex'):?>
									<?=Loc::getMessage('CRM_ADS_RTG_AUTO_REMOVE_TITLE_' . $typeUpped)?>
								<?else:?>
									<?=Loc::getMessage('CRM_ADS_RTG_AUTO_REMOVE_TITLE')?>
								<?endif;?>
						</label>
					</div>
					<div class="crm-ads-rtg-popup-chk-label">
							<select data-bx-ads-audience-auto-remove-select="" name="<?=$namePrefix?>AUTO_REMOVE_DAY_NUMBER" <?=($isSelectedOnce ? '' : 'disabled')?> data-bx-ads-audience-auto-remove-select="" class="crm-ads-rtg-popup-settings-dropdown crm-ads-rtg-popup-settings-dropdown-sm">
								<?=$sDayValues?>
							</select>
							<?= Loc::getMessage('CRM_ADS_RTG_AUTO_REMOVE_DAYS') ?>
						</div>
					</div>
				<?php
				endif ?>
			<?php endif; ?>
		</div>

		<div class="crm-ads-rtg-popup-settings">
			<a data-bx-ads-audience-create-link="" class="crm-ads-rtg-popup-link" href="<?= htmlspecialcharsbx($provider['URL_AUDIENCE_LIST']) ?>" target="_blank">
				<?php if (in_array($type, [
					\Bitrix\Seo\Retargeting\Service::TYPE_GOOGLE,
					\Bitrix\Seo\Retargeting\Service::TYPE_VKONTAKTE,
				], true)): ?>
					<?= Loc::getMessage('CRM_ADS_RTG_CABINET_' . $typeUpped . '_1') ?>
				<?php else: ?>
					<?= Loc::getMessage('CRM_ADS_RTG_CABINET_' . $typeUpped) ?>
				<?php endif ?>
			</a>
		</div>
	</div>

</script>

<script>
	BX.ready(function() {
		var r = (Date.now() / 1000 | 0);
		BX.loadCSS('<?=$this->GetFolder()?>/configurator.css?' + r);
		BX.loadScript('<?=$this->GetFolder()?>/configurator.js?' + r, function() {
			var params = <?=\Bitrix\Main\Web\Json::encode([
				'provider' => $provider,
				'multiClients' => $multiClients,
				'clientId' => $clientId,
				'accountId' => $accountId,
				'audienceId' => $audienceId,
				'audienceRegion' => $arParams['AUDIENCE_LOOKALIKE_MODE'] ? $arParams['AUDIENCE_REGION'] : null,
				'audienceLookalikeMode' => $arParams['AUDIENCE_LOOKALIKE_MODE'],
				'isSupportCreateLookalikeFromSegments' => $provider['IS_SUPPORT_CREATE_LOOKALIKE_FROM_SEGMENTS'],
				'containerId' => $containerNodeId,
				'destroyEventName' => $destroyEventName,
				'signedParameters' => $this->getComponent()->getSignedParameters(),
				'componentName' => $this->getComponent()->getName(),
				'titleNodeSelector' => $titleNodeSelector,
				'messageCode' => $arParams['MESSAGE_CODE'],
				'mess' => [
					'errorAction' => Loc::getMessage('CRM_ADS_RTG_ERROR_ACTION'),
					'dlgBtnClose' => Loc::getMessage('CRM_ADS_RTG_CLOSE'),
					'dlgBtnCreate' => Loc::getMessage('CRM_ADS_RTG_CREATE'),
					'dlgBtnApply' => Loc::getMessage('CRM_ADS_RTG_APPLY'),
					'dlgBtnCancel' => Loc::getMessage('CRM_ADS_RTG_CANCEL_ALT'),
					'newAudiencePopupTitle' => Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD'),
					'newAudienceNameLabel' => (($type === 'vkontakte')
						? Loc::getMessage('CRM_ADS_RTG_NEW_USER_LIST_NAME_LABEL')
						: Loc::getMessage('CRM_ADS_RTG_NEW_AUDIENCE_NAME_LABEL')),
					'chooseAudience' => (($type === 'vkontakte')
						? Loc::getMessage('CRM_ADS_RTG_CHOOSE_USER_LIST')
						: Loc::getMessage('CRM_ADS_RTG_CHOOSE_AUDIENCE')),
				],
			])?>;
			new CrmAdsRetargeting(params);
		});

	});
</script>
