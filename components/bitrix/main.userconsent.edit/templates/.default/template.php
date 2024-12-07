<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

CUtil::InitJSCore(["popup"]);
Extension::load(['sidepanel', 'ui.forms', 'ui.design-tokens']);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background user-consent-content-modifier');

$listDomIds = [
	'formId' => 'user-consent-form',
	'formContainerId' => 'user-consent-content-container-form',
	'listContainerId' => 'user-consent-content-container-list',
	'fieldNameId' => 'user-consent-content-field-name',
	'fieldTypeId' => 'user-consent-content-field-type',
	'fieldProviderId' => 'user-consent-content-field-provider',
];

$activeTab = 'text';
$APPLICATION->IncludeComponent('bitrix:ui.sidepanel.wrappermenu', '', [
	'ITEMS' => $arResult['MENU_ITEMS'],
	'VIEW_TARGET' => 'left-panel-consent-edit'
]);

$formAction = (new Uri($APPLICATION->getCurPageParam()))
	->addParams(['save' => 'y'])
	->getUri()
;
?>

<div class="main-user-consent-errors">
	<?php foreach ($arResult['ERRORS'] as $error): ?>
		<? ShowError($error); ?>
	<?php endforeach; ?>
</div>

<script>
	BX.ready(function () {
		BX.Main.UserConsent.Edit = new BX.Main.UserConsent.Edit(<?=Json::encode([
			'isSaved' => $arResult['IS_SAVED'],
			'listDomIds' => $listDomIds,
			'mess' => [
				'viewTitle' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_POPUP_TITLE'),
				'close' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_CLOSE'),
			]
		])?>);
	});
</script>

<form id="<?=$listDomIds['formId']?>" class="main-user-consent-edit-form" method="post" action="<?=$formAction?>">
	<?=bitrix_sessid_post()?>
<div class="main-user-consent-edit">
<div class="main-user-consent-edit-menu">
	<? $APPLICATION->ShowViewContent('left-panel-consent-edit'); ?>
</div>

<div id="<?=$listDomIds['formContainerId']?>" class="main-user-consent-edit-content">
		<div id="<?=$listDomIds['fieldNameId']?>" class="main-user-consent-edit-raw">
			<div class="main-user-consent-edit-title">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_NAME')?>
			</div>
			<div class="main-user-consent-edit-input-container">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="text" class="ui-ctl-element" name="NAME" value="<?=
						htmlspecialcharsbx($arResult['DATA']['NAME'])?>" placeholder="<?=
						Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_NAME')?>">
				</div>
			</div>
			<br>
		</div>

		<div id="<?=$listDomIds['fieldTypeId']?>" class="main-user-consent-edit-raw" style="<?=
			($arResult['TYPE_LIST_AVAILABLE_COUNT'] <= 1 ? 'display: none;' : '')?>">
			<div class="main-user-consent-edit-title">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_TYPE')?>
			</div>
			<div class="main-user-consent-edit-select-container">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown uce-first-select-container ui-ctl-w100">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select class="ui-ctl-element" data-bx-type-selector="">
						<?
						$isCurrentSupportDataProvider = false;
						foreach ($arResult['TYPE_LIST'] as $type):
							$typeCode = htmlspecialcharsbx($type['TYPE']);
							$lang = htmlspecialcharsbx($type['LANGUAGE_ID']);
							$code = htmlspecialcharsbx($type['CODE']);
							$isSupportDataProv = $type['IS_SUPPORT_DATA_PROVIDERS'];
							if ($type['SELECTED'])
							{
								$isCurrentSupportDataProvider = $isSupportDataProv;
							}
							elseif (!$type['AVAILABLE'])
							{
								continue;
							}
							?>
							<option data-bx-type="<?=$typeCode?>" data-bx-lang="<?=
								$lang?>" data-bx-agreement-text="<?=htmlspecialcharsbx($type['AGREEMENT_TEXT'])
								?>" data-bx-supp-provider="<?=($isSupportDataProv ? 'Y' : 'N')?>" <?=
								($type['SELECTED'] ? 'selected': '')?>>
								<?=htmlspecialcharsbx($type['NAME'])?>
							</option>
						<?endforeach;?>
					</select>
				</div>
				<div class="uce-second-select-container">
					<a data-bx-type-view="" data-bx-text="" class="main-user-consent-edit-link-tune" style="display: none;">
						<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_VIEW')?>
					</a>
					<input type="hidden" data-bx-type-input="" name="TYPE" value="<?=
					htmlspecialcharsbx($arResult['DATA']['TYPE'])?>">
					<input type="hidden" data-bx-lang-input="" name="LANGUAGE_ID" value="<?=
					htmlspecialcharsbx($arResult['DATA']['LANGUAGE_ID'])?>">
				</div>
			</div>
			<br>
		</div>

		<div id="<?=$listDomIds['fieldProviderId']?>" style="<?=
			(empty($arResult['DATA_PROVIDER_LIST']) ? 'display: none;' : '')?>">
			<div data-bx-data-provider="" class="main-user-consent-edit-raw" style="<?=($isCurrentSupportDataProvider ? '' : 'display: none;')?>">
				<div class="main-user-consent-edit-title">
					<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_DATA_PROVIDER')?>:
				</div>
				<div class="main-user-consent-edit-select-container">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 uce-first-select-container">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select class="ui-ctl-element" name="DATA_PROVIDER" data-bx-data-provider-input="">
							<option value="">
								<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_DATA_PROVIDER_DEF')?>
							</option>
							<?foreach ($arResult['DATA_PROVIDER_LIST'] as $provider):
								$data = htmlspecialcharsbx(Json::encode($provider['DATA']));
								?>
								<option value="<?=htmlspecialcharsbx($provider['CODE'])?>" data-bx-edit-url="<?=
								htmlspecialcharsbx($provider['EDIT_URL'])?>" data-bx-data="<?=$data?>" <?=
								($provider['SELECTED'] ? 'selected': '')?>>
									<?=htmlspecialcharsbx($provider['NAME'])?>
								</option>
							<?endforeach;?>
						</select>
					</div>
					<div class="uce-second-select-container">
						<a data-bx-data-provider-url="" target="_blank" class="main-user-consent-edit-link-tune" style="display: none;">
							<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_TUNE')?>
						</a>
					</div>
				</div>
				<br>
			</div>
		</div>

		<div class="main-user-consent-edit-fields" style="">

			<?foreach ($arResult['TYPE_LIST'] as $type):
				$typeCode = htmlspecialcharsbx($type['TYPE']);
				$lang = htmlspecialcharsbx($type['LANGUAGE_ID']);

				if (!$type['SELECTED'] && !$type['AVAILABLE'])
				{
					continue;
				}
				?>
				<div data-bx-fields="" data-bx-type="<?=$typeCode?>" data-bx-lang="<?=$lang?>" style="<?=($type['SELECTED'] ? '' : 'display: none;')?>">
				<?foreach ($type['FIELDS'] as $field):
					$code = htmlspecialcharsbx($field['CODE']);
					$tab = htmlspecialcharsbx($field['TAB']);
					if (isset($field['INPUT_NAME']) && $field['INPUT_NAME'])
					{
						$inputName = htmlspecialcharsbx($field['INPUT_NAME']);
						$inputId = $inputName;
					}
					else
					{
						$inputId = 'FIELDS_' . $lang . '_' . $code . '';
						$inputName = 'FIELDS[' . $lang . '][' . $code . ']';
					}

					$typesWithoutLabel = ['checkbox'];

					$inputValue = htmlspecialcharsbx($field['VALUE']);
					$inputPlaceholder = htmlspecialcharsbx($field['PLACEHOLDER']);
					$inputShowByCheckbox = isset($field['SHOW_BY_CHECKBOX']) && $field['SHOW_BY_CHECKBOX'];
					$checkboxName = (!empty($field['CHECKBOX_NAME']) ? htmlspecialcharsbx($field['CHECKBOX_NAME']) : '');
					?>
					<div data-bx-field="<?=$code?>" data-bx-tab="<?=$tab?>" style="<?=
						($tab !== $activeTab ? 'display: none;' : '') ?>" class="main-user-consent-edit-fields-field">
						<? if (!in_array($field['TYPE'], $typesWithoutLabel)):?>
							<div class="main-user-consent-edit-fields-field-label main-user-consent-edit-title">
								<? if ($inputShowByCheckbox):?>
									<input class="" id="<?=$inputId?>_TOGGLER" data-bx-toggler="" name="<?=$checkboxName?>" type="checkbox" <?=($inputValue ? 'checked' : '')?>>
								<? endif; ?>
								<label class="" for="<?=$inputId?>_TOGGLER">
									<?=htmlspecialcharsbx($field['CAPTION'])?>
								</label>
							</div>
						<? endif; ?>

						<div data-bx-view="" class="main-user-consent-edit-fields-field-view" style="display: none;">
							<span data-bx-view-name="" class="main-user-consent-edit-fields-field-view-name">Hkfdvfjhdvbhjfd</span>
							<span data-bx-view-value="" class="main-user-consent-edit-fields-field-view-value">Fdnvhfjdbvjhfdbvfdbvfd</span>
						</div>

						<div data-bx-toggled="" <?=(($inputShowByCheckbox && !$inputValue) ? 'style="display: none;"' : '')?> class="main-user-consent-edit-fields-field-input">
							<?
							switch ($field['TYPE'])
							{
								case 'enum':
									$input = '<select data-bx-input="" name="' . $inputName . '" class="main-user-consent-edit-input main-user-consent-edit-select">';
									foreach ($field['ITEMS'] as $item)
									{
										$input .= '<option ';
										$input .= 'value="' . htmlspecialcharsbx($item['CODE']) . '" ';
										$input .= ($item['CODE'] == $inputValue ? 'selected' : '') . '>';
										$input .= htmlspecialcharsbx($item['NAME']);
										$input .= '</option>';
									}
									$input .= '</select>';
									break;
								case 'checkbox':
									$input = '<div class="main-user-consent-edit-fields-field-label main-user-consent-edit-title">';
									$input .= '<input id="'.$inputId.'" type="checkbox"';
									$input .= 'name="'.$inputName.'" ';
									$input .= ' '.($inputValue == 'Y' ? 'checked' : '').'>';
									$input .= '<label class="" for="'.$inputId.'">';
									$input .= htmlspecialcharsbx($field['CAPTION']);
									$input .= '</label>';
									$input .= '</div>';
									break;
								case 'text':
									$input = '<div class="ui-ctl ui-ctl-textarea ui-ctl-lg ui-ctl-w100">';
									$input .= '<textarea class="ui-ctl-element ui-ctl-row" data-bx-input="" ';
									$input .= 'name="' . $inputName . '" ';
									$input .= 'placeholder="' . $inputPlaceholder . '">';
									$input .= $inputValue;
									$input .= '</textarea>';
									$input .= '</div>';
									break;
								case 'string':
								default:
									$input = '<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">';
									$input .= '<input class="ui-ctl-element" data-bx-input="" ';
									$input .= 'type="text" ';
									$input .= 'name="' . $inputName . '" ';
									$input .= 'value="' . $inputValue . '"';
									$input .= 'placeholder="' . $inputPlaceholder . '">';
									$input .= '</div>';
									break;
							}

							echo $input;
							?>
						</div>
					</div>
				<?endforeach;?>
				</div>
			<?endforeach;?>
		</div>

		<?if (!$arParams['CAN_EDIT']):?>
			<div class="main-user-consent-edit-alert">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_ERROR_ACCESS_EDIT')?>
			</div>
		<?endif;?>
</div>

<?php if (!$arResult['ERRORS'] && $arParams['ID']): ?>
<div id="<?=$listDomIds['listContainerId']?>" class="main-user-consent-edit-content" style="display: none;">
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.userconsent.consent.list',
		'',
		[
			'GRID_ID' => 'MAIN_USER_CONSENT_GRID',
			'AGREEMENT_ID' => ($arResult['AJAX_REQUEST'] ? '' : $arParams['ID']),
			'SET_TITLE' => 'N',
			'USE_TOOLBAR' => 'N',
			'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER_PROFILE'],
		]
	);
	?>
</div>
<? endif; ?>

</div>

<div class="main-user-consent-edit-button-container">
	<?php
	$buttons = [];
	if ($arParams['CAN_EDIT'])
	{
		$buttons[] = [
			'TYPE' => 'save',
			'ONCLICK' => 'BX.Main.UserConsent.Edit.submit();',
		];
	}
	$buttons[] = [
		'TYPE' => 'cancel',
		'LINK' => $arParams['PATH_TO_LIST'],
		'CAPTION' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_BACK_TO_LIST')
	];
	$APPLICATION->includeComponent('bitrix:ui.button.panel', '', [
		'BUTTONS' => $buttons
	]);
	?>
</div>
</form>