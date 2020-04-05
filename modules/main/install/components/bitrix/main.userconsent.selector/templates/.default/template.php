<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

CJSCore::Init("sidepanel");
/** @var array $arParams */
/** @var array $arResult */
?>
<script type="text/javascript">
	BX.ready(function () {
		new MainUserConsentSelectorManager(<?=\Bitrix\Main\Web\Json::encode(array(
			'actionRequestUrl' => $arParams['ACTION_REQUEST_URL']
		))?>);
	});
</script>
<div data-bx-user-consent-selector="" class="main-user-consent-selector-wrapper">
	<?if ($arResult['DESCRIPTION']):?>
	<div class="main-user-consent-selector-alert">
		<?=$arResult['DESCRIPTION']?>
	</div>
	<?endif;?>
	<div class="main-user-consent-selector-block">
		<div class="main-user-consent-selector-block-name">
			<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_CHOOSE')?>:
		</div>
		<div class="main-user-consent-selector-block-input">
			<select class="main-user-consent-selector-block-input-item" data-bx-selector="" name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>">
				<option value=""><?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_DEF_NOT_SELECTED')?></option>
				<?foreach ($arResult['LIST'] as $item):?>
					<option value="<?=htmlspecialcharsbx($item['ID'])?>" <?=($item['SELECTED'] ? 'selected' : '')?>>
						<?=htmlspecialcharsbx($item['NAME'])?>
					</option>
				<?endforeach;?>
			</select>

			<a class="main-user-consent-selector-block-link main-user-consent-selector-block-link-bold" href="#" data-bx-link-edit="" data-bx-slider-href="" data-bx-link-tmpl="<?=htmlspecialcharsbx($arParams['PATH_TO_EDIT'])?>">
				<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_EDIT')?>
			</a>
		</div>
		<div class="main-user-consent-selector-block-hint">
			<a class="main-user-consent-selector-block-link" href="#" data-bx-link-view="" data-bx-slider-href="" data-bx-link-tmpl="<?=htmlspecialcharsbx($arParams['PATH_TO_CONSENT_LIST'])?>">
				<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CONSENT')?>
			</a>
		</div>
	</div>
	<div class="main-user-consent-selector-footer">
		<a class="main-user-consent-selector-block-link" data-bx-link-add="" data-bx-slider-href="" data-bx-slider-reload="true" href="<?=htmlspecialcharsbx($arParams['PATH_TO_ADD'])?>">
			<span class="main-user-consent-selector-block-plus-icon">&#43;</span>
			<?=Loc::getMessage('MAIN_USER_CONSENT_SELECTOR_BTN_CREATE')?>
		</a>
	</div>
</div>
