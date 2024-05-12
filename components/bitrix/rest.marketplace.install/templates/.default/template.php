<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

Extension::load(
	[
		'ui.design-tokens',
		'ui.fonts.opensans',
		'ui.buttons',
		'ui.alerts',
		'marketplace',
	]
);
if (
	$arResult['APP']['SILENT_INSTALL'] === 'Y'
	&& isset($arResult['INSTALL_FINISH']['success'])
	&& !isset($arResult['INSTALL_FINISH']['error'])
):?>
	<?php
		$APPLICATION->IncludeComponent(
			'bitrix:rest.configuration.import',
			'',
			[
				'MODE' => 'ZIP',
				'SET_TITLE' => 'Y',
				'FROM' => $arParams['FROM'],
				'ADDITIONAL' => $arParams['ADDITIONAL'],
				'ZIP_ID' => $arParams['ZIP_ID'],
			]
		);
	?>
	<script>
		BX.ready(
			function ()
			{
				var result = <?=Json::encode(
					$arResult['INSTALL_FINISH']
				)?>;
				if (result.installed)
				{
					var eventResult = {};
					top.BX.onCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', [true, eventResult], false);
				}

				if (!!result.open)
				{
					top.BX.rest.AppLayout.openApplication(result.id, {});
				}
			}
		);
	</script>
<?php
else:
?>
	<style>#workarea-content {background: transparent !important;} .landing-import-start .landing-popup-import {height: auto}</style>
	<div class="rest-market-app-install-wrapper">
		<form id="restMarketAppInstallForm" method="POST">
			<?=bitrix_sessid_post()?>
			<div class="rest-market-app-install-header">
				<div class="rest-market-app-install-header-image-block">
					<?php if (!empty($arResult['APP']['ICON'])):?>
						<span class="rest-market-app-install-header-image">
							<img src="<?=htmlspecialcharsbx($arResult['APP']['ICON'])?>" alt="">
						</span>
					<?php else:?>
						<span class="rest-market-app-install-header-image">
							<span class="rest-market-app-install-image-empty"></span>
						</span>
					<?php endif;?>
				</div>
				<div class="rest-market-app-install-header-description-block">
					<h2 class="rest-market-app-install-title"><?=htmlspecialcharsbx($arResult['APP']['NAME']);?></h2>
					<div class="rest-market-app-install-header-descriptions">
						<span class="rest-market-app-install-header-descriptions-version"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_APP_VERSION')?> <?=htmlspecialcharsbx($arResult['APP']['VER'])?></span>
						<span class="rest-market-app-install-header-descriptions-author"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_APP_AUTHOR')?> <?=htmlspecialcharsbx($arResult['APP']['PARTNER_NAME'])?></span>
					</div>
				</div>
			</div>

			<div class="rest-market-app-install-inner">
				<div class="ui-alert ui-alert-danger " id="rest_mp_install_error" style="display: none;">
					<div class="ui-alert-message"></div>
				</div>
				<?
				if (!$arResult['IS_HTTPS'])
				{
					?>
					<div class="ui-alert ui-alert-danger ui-alert-icon-warning">
						<div class="ui-alert-message"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_HTTPS_WARNING');?></div>
					</div>
					<?
				}
				?>
				<?
				if (is_array($arResult['APP']['RIGHTS']))
				{
					?>
					<div class="rest-market-app-install-inner-rights">
						<div class="rest-market-app-install-inner-right-list-title"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_REQUIRED_RIGHTS')?></div>
						<div class="rest-market-app-install-inner-rights-list">
							<?php
								if (!empty($arResult['SCOPE_DENIED'])):
									$message = (
									\Bitrix\Main\Loader::includeModule('bitrix24')
										? Loc::getMessage(
										'REST_MARKETPLACE_INSTALL_MODULE_UNINSTALL_BITRIX24',
										[
											'#PATH_CONFIGS#' => CBitrix24::PATH_CONFIGS
										]
									)
										: Loc::getMessage('REST_MARKETPLACE_INSTALL_MODULE_UNINSTALL')
									);
									?>
									<div class="ui-alert ui-alert-danger ui-alert-icon-warning">
										<div class="ui-alert-message"><?=$message?></div>
									</div>
									<?
								endif;

								foreach($arResult['APP']['RIGHTS'] as $key => $scope):
									$scope = is_array($scope) ? $scope : ['TITLE' => $scope, 'DESCRIPTION' => ''];
									?>
									<div class="rest-market-app-install-inner-rights-item"<?=array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' bx-denied="Y" style="color:#d83e3e"' : ''?>>
										<div class="rest-market-app-install-inner-rights-item-header">
											<div class="rest-market-app-install-inner-rights-item-title"><?=$scope['TITLE']?></div>
										</div>
										<div class="rest-market-app-install-inner-rights-item-description"><?=$scope['DESCRIPTION']?></div>
									</div>
									<?php
								endforeach;
							?>
						</div>
					</div>
				<?php };?>
			</div>
			<div class="rest-market-app-install-footer">
				<?php
				$license_link = !empty($arResult['APP']['EULA_LINK']) ? $arResult['APP']['EULA_LINK'] : Loc::getMessage('REST_MARKETPLACE_INSTALL_EULA_LINK', ['#CODE#' => urlencode($arResult['APP']['CODE'])]);
				$privacy_link = !empty($arResult['APP']['PRIVACY_LINK']) ? $arResult['APP']['PRIVACY_LINK'] : Loc::getMessage('REST_MARKETPLACE_INSTALL_PRIVACY_LINK');
				?>
				<div class="rest-market-app-install-confidentiality">
					<?php if ($arResult['TERMS_OF_SERVICE_LINK']):?>
						<div style="margin-bottom: 8px;">
							<input type="checkbox" id="mp_tos_license" value="N">
							<label for="mp_tos_license">
								<?=Loc::getMessage(
									'REST_MARKETPLACE_INSTALL_TERMS_OF_SERVICE_TEXT',
									[
										'#LINK#' => $arResult['TERMS_OF_SERVICE_LINK']
									]
								)?>
							</label>
						</div>
					<?php endif;?>
					<?php if (LANGUAGE_ID === 'ru' || LANGUAGE_ID === 'ua' || $arResult['APP']['EULA_LINK']):?>
						<div style="margin-bottom: 8px;">
							<input type="checkbox" id="mp_detail_license" value="N">
							<label for="mp_detail_license">
								<?=Loc::getMessage('REST_MARKETPLACE_INSTALL_EULA_TEXT', ['#LINK#' => $license_link])?>
							</label>
						</div>
					<?php endif;?>
					<div>
						<input type="checkbox" id="mp_detail_confidentiality" value="N">
						<label for="mp_detail_confidentiality">
							<?=Loc::getMessage(
								'REST_MARKETPLACE_INSTALL_PRIVACY_TEXT',
								[
									'#LINK#' => $privacy_link
								]
							)?>
						</label>
					</div>
				</div>
				<div class="ui-btn-container ui-btn-container-center">
					<button type="submit" class="ui-btn ui-btn-success rest-btn-start-install"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_BTN_INSTALL')?></button>
					<span class="ui-btn ui-btn-link rest-btn-close-install" ><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_BTN_CANCEL')?></span>
				</div>
			</div>
		</form>
	</div>
	<script>
		BX.message({
			"REST_MARKETPLACE_INSTALL_LICENSE_ERROR" : "<?=Loc::getMessage("REST_MARKETPLACE_INSTALL_LICENSE_ERROR")?>",
			"REST_MARKETPLACE_INSTALL_TOS_ERROR" : "<?=Loc::getMessage("REST_MARKETPLACE_INSTALL_TOS_ERROR")?>",
		});
		BX.ready(function () {
			BX.Rest.Marketplace.Install.init(<?=Json::encode(
				[
					'CODE' => $arResult['APP']['CODE'],
					'VERSION' => $arResult['APP']['VER'],
					'CHECK_HASH' => $arParams['CHECK_HASH'],
					'INSTALL_HASH' => $arParams['INSTALL_HASH'],
					'FROM' => $arParams['FROM'],
					'IFRAME' => $arParams['IFRAME'],
				]
			)?>);
		});
	</script>
<?php
endif;
?>