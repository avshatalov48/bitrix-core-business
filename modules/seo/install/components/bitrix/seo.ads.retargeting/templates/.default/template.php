<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;

/** @var array $arParams */

Extension::load('ui.hint');

$containerNodeId = $arParams['CONTAINER_NODE_ID'];
$destroyEventName = $arParams['JS_DESTROY_EVENT_NAME'];
$accountId = $arParams['ACCOUNT_ID'];
$audienceId = $arParams['AUDIENCE_ID'];
$autoRemoveDayNumber = $arParams['AUTO_REMOVE_DAY_NUMBER'];
$provider = $arParams['PROVIDER'];
$type = htmlspecialcharsbx($provider['TYPE']);
$typeUpped = strtoupper($type);

$namePrefix = htmlspecialcharsbx($arParams['INPUT_NAME_PREFIX']);
?>
<script id="template-crm-ads-dlg-settings" type="text/html">
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
		<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?=$type?>">
			<a
				id="seo-ads-login-btn"
				target="_blank"
				href="javascript: void(0);"
				onclick="BX.util.popup('<?=htmlspecialcharsbx($provider['AUTH_URL'])?>', 800, 600);"
				class="webform-small-button webform-small-button-transparent">
				<?=Loc::getMessage('CRM_ADS_RTG_LOGIN')?>
			</a>
		</div>
	</div>


	<div data-bx-ads-block="auth" style="display: none;">
		<div class="crm-ads-rtg-popup-settings">
			<div class="crm-ads-rtg-popup-social crm-ads-rtg-popup-social-<?=$type?>">
				<div class="crm-ads-rtg-popup-social-avatar">
					<div data-bx-ads-auth-avatar="" class="crm-ads-rtg-popup-social-avatar-icon"></div>
				</div>
				<div class="crm-ads-rtg-popup-social-user">
					<a target="_top" data-bx-ads-auth-link="" data-bx-ads-auth-name="" class="crm-ads-rtg-popup-social-user-link" title=""></a>
				</div>
				<div class="crm-ads-rtg-popup-social-shutoff">
					<span data-bx-ads-auth-logout="" class="crm-ads-rtg-popup-social-shutoff-link"><?=Loc::getMessage('CRM_ADS_RTG_LOGOUT')?></span>
				</div>
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
				<?=Loc::getMessage('CRM_ADS_RTG_REFRESH')?>
			</span>
		</div>
	</div>


	<div data-bx-ads-block="main" style="display: none;">
		<div class="crm-ads-rtg-popup-settings crm-ads-rtg-popup-settings-wrapper">
			<div class="crm-ads-rtg-popup-settings" style="<?=(!$provider['IS_SUPPORT_ACCOUNT'] ? 'display: none;' : '')?>">
				<div class="crm-ads-rtg-popup-settings-title-full"><?=Loc::getMessage('CRM_ADS_RTG_SELECT_ACCOUNT')?>:</div>

				<table class="crm-ads-rtg-table">
					<tr>
						<td>
							<select disabled name="<?=$namePrefix?>ACCOUNT_ID" data-bx-ads-account="" class="crm-ads-rtg-popup-settings-dropdown">
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

			<?if($provider['IS_SUPPORT_MULTI_TYPE_CONTACTS']):?>
			<div class="crm-ads-rtg-popup-settings">
				<div class="crm-ads-rtg-popup-settings-title-full">
					<?=Loc::getMessage('CRM_ADS_RTG_SELECT_AUDIENCE')?>:
					<span data-hint="<?=htmlspecialcharsbx(
						Loc::getMessage('CRM_ADS_RTG_AUDIENCE_TYPE_HINT_' . $typeUpped)
						. ' ' . Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD_HINT_' . $typeUpped)
					)?>"></span>
				</div>

				<table class="crm-ads-rtg-table">
					<tr>
						<td>
							<select disabled name="<?=$namePrefix?>AUDIENCE_ID" data-bx-ads-audience="" class="crm-ads-rtg-popup-settings-dropdown">
							</select>
						</td>
						<td>
							<div data-bx-ads-audience-loader="" class="crm-ads-rtg-loader-sm" style="display: none;">
								<svg class="crm-ads-rtg-circular" viewBox="25 25 50 50">
									<circle class="crm-ads-rtg-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
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
						. ' ' . Loc::getMessage('CRM_ADS_RTG_AUDIENCE_ADD_HINT_' . $typeUpped)
					)?>"></span>
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
					<?=Loc::getMessage('CRM_ADS_RTG_AUTO_REMOVE_DAYS')?>
				</div>
			</div>
			<?endif?>

			<div data-bx-ads-audience-not-found="" class="crm-ads-rtg-popup-settings" style="display: none;">
				<div class="crm-ads-rtg-popup-settings-alert">
					<?=Loc::getMessage(
						'CRM_ADS_RTG_ERROR_NO_AUDIENCES',
						array(
							'%name%' => '<a data-bx-ads-audience-create-link="" href="' . htmlspecialcharsbx($provider['URL_AUDIENCE_LIST']) . '" '
							. 'target="_blank">'
							. Loc::getMessage('CRM_ADS_RTG_CABINET_' . $typeUpped)
							.'</a>'
						)
					)?>
				</div>
			</div>
		</div>

		<div class="crm-ads-rtg-popup-settings">
			<a data-bx-ads-audience-create-link="" class="crm-ads-rtg-popup-link" href="<?=htmlspecialcharsbx($provider['URL_AUDIENCE_LIST'])?>" target="_blank">
				<?=Loc::getMessage('CRM_ADS_RTG_CABINET_' . $typeUpped)?>
			</a>
		</div>
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
				'accountId' => $accountId,
				'audienceId' => $audienceId,
				'containerId' => $containerNodeId,
				'destroyEventName' => $destroyEventName,
				'signedParameters' => $this->getComponent()->getSignedParameters(),
				'componentName' => $this->getComponent()->getName(),
				'mess' => array(
					'errorAction' => Loc::getMessage('CRM_ADS_RTG_ERROR_ACTION'),
					'dlgBtnClose' => Loc::getMessage('CRM_ADS_RTG_CLOSE'),
					'dlgBtnCancel' => Loc::getMessage('CRM_ADS_RTG_APPLY'),
				)
			))?>;
			new CrmAdsRetargeting(params);
		});

	});
</script>