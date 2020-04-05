<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

CUtil::InitJSCore(array("popup"));
?><div class="main-user-consent-errors"><?
foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}
?></div><?
if ($arResult['IS_SAVED'])
{
	?>
	<script type="text/javascript">
		(function () {
			if (window.top == window)
			{
				return;
			}
			if (!window.top.BX)
			{
				return;
			}

			window.top.BX.onCustomEvent(window.top, 'main-user-consent-saved', []);
		})();
	</script>
	<?
	return;
}
?>
<script type="text/javascript">
	BX.ready(function () {
		new MainUserConsentEditManager(<?=\Bitrix\Main\Web\Json::encode(array(
			'mess' => array(
				'viewTitle' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_POPUP_TITLE'),
				'close' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_CLOSE'),
			)
		))?>);
	});
</script>

<div id="USER_CONSENT_CONTAINER" class="main-user-consent-edit-wrapper">
	<form method="post" action="<?=$APPLICATION->GetCurPageParam()?>">
		<div class="main-user-consent-edit-inner">
		<?=bitrix_sessid_post()?>
		<div class="main-user-consent-edit-raw" style="">
			<div class="main-user-consent-edit-title">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_NAME')?>:
			</div>
			<div class="main-user-consent-edit-input-container">
				<input class="main-user-consent-edit-input main-user-consent-edit-input-text" type="text" name="NAME" value="<?=htmlspecialcharsbx($arResult['DATA']['NAME'])?>">
			</div>
			<br>
		</div>

		<div class="main-user-consent-edit-raw" style="<?=($arResult['TYPE_LIST_AVAILABLE_COUNT'] <= 1 ? 'display: none;' : '')?>">
			<div class="main-user-consent-edit-title">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_TYPE')?>:
			</div>
			<div class="main-user-consent-edit-select-container">
				<select class="main-user-consent-edit-input main-user-consent-edit-select" data-bx-type-selector="" class="">
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
						<option data-bx-type="<?=$typeCode?>" data-bx-lang="<?=$lang?>" data-bx-agreement-text="<?=htmlspecialcharsbx($type['AGREEMENT_TEXT'])?>" data-bx-supp-provider="<?=($isSupportDataProv ? 'Y' : 'N')?>" <?=($type['SELECTED'] ? 'selected': '')?>>
							<?=htmlspecialcharsbx($type['NAME'])?>
						</option>
					<?endforeach;?>
				</select>

				<a data-bx-type-view="" data-bx-text="" class="main-user-consent-edit-link-tune" style="display: none;"><?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_VIEW')?></a>

				<input type="hidden" data-bx-type-input="" name="TYPE" value="<?=htmlspecialcharsbx($arResult['DATA']['TYPE'])?>">
				<input type="hidden" data-bx-lang-input="" name="LANGUAGE_ID" value="<?=htmlspecialcharsbx($arResult['DATA']['LANGUAGE_ID'])?>">
			</div>
			<br>
		</div>

		<div style="<?=(count($arResult['DATA_PROVIDER_LIST']) == 0 ? 'display: none;' : '')?>">
			<div data-bx-data-provider="" class="main-user-consent-edit-raw" style="<?=($isCurrentSupportDataProvider ? '' : 'display: none;')?>">
				<div class="main-user-consent-edit-title">
					<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_DATA_PROVIDER')?>:
				</div>
				<div class="main-user-consent-edit-select-container">
					<select data-bx-data-provider-input="" class="main-user-consent-edit-input main-user-consent-edit-select" name="DATA_PROVIDER" class="">
						<option value=""><?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_DATA_PROVIDER_DEF')?></option>
						<?foreach ($arResult['DATA_PROVIDER_LIST'] as $provider):
							$data = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($provider['DATA']));
							?>
							<option value="<?=htmlspecialcharsbx($provider['CODE'])?>" data-bx-edit-url="<?=htmlspecialcharsbx($provider['EDIT_URL'])?>" data-bx-data="<?=$data?>" <?=($provider['SELECTED'] ? 'selected': '')?>>
								<?=htmlspecialcharsbx($provider['NAME'])?>
							</option>
						<?endforeach;?>
					</select>

					<a data-bx-data-provider-url="" target="_blank" class="main-user-consent-edit-link-tune" style="display: none;"><?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_TUNE')?></a>
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

						$inputValue = htmlspecialcharsbx($field['VALUE']);
						$inputPlaceholder = htmlspecialcharsbx($field['PLACEHOLDER']);
						$inputShowByCheckbox = isset($field['SHOW_BY_CHECKBOX']) && $field['SHOW_BY_CHECKBOX'];
						?>
						<div data-bx-field="<?=$code?>" class="main-user-consent-edit-fields-field">
							<div class="main-user-consent-edit-fields-field-label main-user-consent-edit-title">
								<?
								if ($inputShowByCheckbox)
								{
									?>
									<input class="" id="<?=$inputId?>_TOGGLER" data-bx-toggler="" type="checkbox" <?=($inputValue ? 'checked' : '')?>>
									<?
								}
								?>
								<label class="" for="<?=$inputId?>_TOGGLER">
									<?=htmlspecialcharsbx($field['CAPTION'])?>
								</label>
							</div>

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
									case 'text':
										$input = '<textarea class="main-user-consent-edit-input main-user-consent-edit-textarea" data-bx-input="" ';
										$input .= 'name="' . $inputName . '" ';
										$input .= 'placeholder="' . $inputPlaceholder . '">';
										$input .= $inputValue;
										$input .= '</textarea>';
										break;
									case 'string':
									default:
										$input = '<input class="main-user-consent-edit-input" data-bx-input="" ';
										$input .= 'type="text" ';
										$input .= 'name="' . $inputName . '" ';
										$input .= 'value="' . $inputValue . '"';
										$input .= 'placeholder="' . $inputPlaceholder . '">';
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
		</div>

		<div class="main-user-consent-edit-button-container">
			<?if ($arParams['CAN_EDIT']):?>
				<?if (!$arParams['IFRAME']):?>
					<input name="save" value="<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_SAVE')?>" type="submit" class="webform-small-button webform-small-button-accept">
					<input name="apply" value="<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_APPLY')?>" type="submit" class="webform-small-button webform-small-button-transparent">
				<?else:?>
					<input name="save" value="<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_SAVE')?>" type="submit" class="webform-small-button webform-small-button-accept">
				<?endif;?>
			<?endif;?>
			<a id="MAIN_USER_CONSENT_EDIT_BACK_TO_LIST" href="<?=htmlspecialcharsbx($arParams['PATH_TO_LIST'])?>" class="webform-small-button webform-small-button-transparent">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_BTN_BACK_TO_LIST')?>
			</a>
		</div>

		<?if (!$arParams['CAN_EDIT']):?>
			<div class="main-user-consent-edit-alert">
				<?=Loc::getMessage('MAIN_USER_CONSENT_EDIT_TMPL_ERROR_ACCESS_EDIT')?>
			</div>
		<?endif;?>

	</form>
</div>