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
		'ui.buttons',
		'marketplace',
	]
);

?>
<div class="rest_mp_install_container">
	<form id="rest_mp_install_form" method="POST">
		<?=bitrix_sessid_post()?>
		<div class="rest_mp_install_header">
			<div class="rest_mp_install_title_icon">
				<?php if (!empty($arResult['APP']['ICON'])):?>
					<span class="rest_mp_install_title_img">
						<span><img src="<?=htmlspecialcharsbx($arResult['APP']['ICON'])?>" alt=""></span>
					</span>
				<?php else:?>
					<span class="rest_mp_install_title_img">
						<span class="rest_mp_install_title_icon_empty"></span>
					</span>
					<span class="rest_mp_install_title_icon_shadow"></span>
				<?php endif;?>
			</div>
			<h2 class="rest_mp_install_title_section"><?=htmlspecialcharsbx($arResult['APP']['NAME']);?></h2>
			<p class="rest_mp_install_version_section">
				<?=Loc::getMessage('REST_MARKETPLACE_INSTALL_APP_VERSION')?> <?=htmlspecialcharsbx($arResult['APP']['VER'])?>
			</p>
		</div>
		<div class="rest_mp_install_notify_message" id="rest_mp_install_error" style="display: none;"></div>
		<?php if (!$arResult['IS_HTTPS']):?>
			<div class="rest_mp_install_notify_message">
				<?=Loc::getMessage('REST_MARKETPLACE_INSTALL_HTTPS_WARNING');?>
			</div>
		<?php endif;?>
		<?php if (is_array($arResult['APP']['RIGHTS'])):?>
			<div class="rest_mp_install_content">
				<p><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_REQUIRED_RIGHTS')?></p>
				<?php if (!empty($arResult['SCOPE_DENIED'])):
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
					<div class="rest_mp_install_notify_message"><?=$message?></div>
				<?php endif;?>
				<ul class="rest_mp_install_scope_ul">
					<?php
						foreach($arResult['APP']['RIGHTS'] as $key => $scope):
							$scope = is_array($scope) ? $scope : ['TITLE' => $scope, 'DESCRIPTION' => ''];
							?>
							<li<?=array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' bx-denied="Y" style="color:#d83e3e"' : ''?>>
								<dl>
									<dt><?=$scope['TITLE']?></dt>
									<dd><?=$scope['DESCRIPTION']?></dd>
								</dl>
							</li>
					<?php
						endforeach;
					?>
				</ul>
			</div>
		<?php
		endif;
		$license_link = !empty($arResult['APP']['EULA_LINK']) ? $arResult['APP']['EULA_LINK'] : Loc::getMessage('REST_MARKETPLACE_INSTALL_EULA_LINK', ['#CODE#' => urlencode($arResult['APP']['CODE'])]);
		$privacy_link = !empty($arResult['APP']['PRIVACY_LINK']) ? $arResult['APP']['PRIVACY_LINK'] : Loc::getMessage('REST_MARKETPLACE_INSTALL_PRIVACY_LINK');
		?>
		<div class="rest_mp_install_content rest_mp_install_licebse_block" style="margin-left: 20px;">
			<div id="rest_mp_install_detail_error" style="color: red; margin-bottom: 10px; font-size: 12px;"></div>
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
		<div class="ui-btn-container ui-btn-container-center rest_mp_install_content_controls">
			<button type="submit" class="ui-btn ui-btn-success rest-btn-start-install"><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_BTN_INSTALL')?></button>
			<span class="ui-btn ui-btn-link rest-btn-close-install" ><?=Loc::getMessage('REST_MARKETPLACE_INSTALL_BTN_CANCEL')?></span>
		</div>
	</form>
	<script type="text/javascript">
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
</div>