<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!isset($agreement))
{
	$agreement = [
		'TITLE' => '',
		'CONTENT' => '',
		'ACTIVE' => 'Y'
	];
}
if (!isset($id) || preg_match('/[^a-z0-9_]/i', $id))
{
	$id = strtolower(\randString(5));
}
if (!isset($agreementType))
{
	$agreementType = 'CUSTOM';
}

// main.post.form params
$formParams = function(string $fieldName, string $value): array
{
	$randString = strtolower(\randString(5));
	return [
		'FORM_ID' => 'agreements_form_' . $randString,
		'SHOW_MORE' => 'Y',
		'PARSER' => [
			'Bold', 'Italic', 'Underline', 'Strike',
			'Justify', 'CreateLink', 'Quote',
			'InsertOrderedList', 'InsertUnorderedList'
		],
		'BUTTONS' => [
			'CreateLink',
			'Quote'
		],
		'TEXT' => [
			'ID' => 'field_' . $randString,
			'NAME' => $fieldName,
			'VALUE' => $value,
			'HEIGHT' => '160px',
			'SHOW' => 'Y'
		],
		'LHE' => [
			'height' => 120,
			'documentCSS' => '',
			'fontFamily' => '\'Helvetica Neue\', Helvetica, Arial, sans-serif',
			'fontSize' => '12px',
			'lazyLoad' => false,
			'setFocusAfterShow' => false
		]
	];
};
?>


<div class="landing-agreement-block">
	<?if ($agreementType !== 'CUSTOM'):?>
		<div class="landing-agreement-input-block">
			<input type="hidden" name="<?= 'agreement_active_' . $id;?>"  value="N" >
			<input type="checkbox" name="<?= 'agreement_active_' . $id;?>" class="landing-agreement-input"<?if ($agreement['ACTIVE'] == 'Y'){?> checked="checked"<?}?> id="<?= \htmlspecialcharsbx($agreement['TITLE']);?>" value="Y" >
			<label class="landing-agreement-input-label" for="<?= \htmlspecialcharsbx($agreement['TITLE']);?>"><?= Loc::getMessage('LANDING_TPL_TITLE_SHOW_COOKIES', ['#BLOCK_NAME#' => \htmlspecialcharsbx($agreement['TITLE'])]);?></label>
		</div>
	<?endif;?>
	<div class="landing-agreement-block-inner<?if ($agreement['ACTIVE'] == 'Y'){?> landing-agreement-block-inner-show<?}?>">
		<div class="landing-agreement-block-hidden">
			<div class="landing-agreement-cookies-name-block">
				<?if ($agreementType == 'CUSTOM'):?>
				<span class="landing-agreement-cookies-name">
					<span class="landing-agreement-cookies-name-value"><?= $agreement['TITLE'] ? \htmlspecialcharsbx($agreement['TITLE']) : Loc::getMessage('LANDING_TPL_NEW_COOKIES');?></span>
					<input type="text" class="landing-agreement-cookies-name-input" name="<?= 'agreement_title_' . $id;?>" value="<?= \htmlspecialcharsbx($agreement['TITLE']);?>" size="50"/>
					<span class="landing-agreement-edit"></span>
					<span class="landing-agreement-delete"></span>
				</span>
				<?endif;?>
			</div>
			<div class="landing-agreement-label"><?= Loc::getMessage('LANDING_TPL_LABEL_DESC');?></div>
			<div class="landing-agreement-editor">
				<?$APPLICATION->IncludeComponent(
					'bitrix:main.post.form',
					'',
					$formParams(
						'agreement_text_' . $id,
						$agreement['CONTENT']
					),
					false,
					['HIDE_ICONS' => 'Y']
				);?>
			</div>
		</div>
	</div>
</div>