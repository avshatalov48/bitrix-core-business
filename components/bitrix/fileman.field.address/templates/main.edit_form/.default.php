<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 * @var array $arParams
 */

$isLocationIncluded = \Bitrix\Main\Loader::includeModule('location');
if (!$isLocationIncluded)
{
	echo '<div>' . $component->getLocationModuleMessage() . '</div>';
	return;
}

\Bitrix\Main\UI\Extension::load('fileman.userfield.address_widget');

$component = $this->getComponent();
$userField = $arResult['userField'];
$additionalParameters = $arResult['additionalParameters'];

$randString = $this->randString();
if ($component->isAjaxRequest())
{
	$randString .= time();
}

$controlId = HtmlFilter::encode($userField['FIELD_NAME']) . '_' . $randString;
$nodeId = $controlId . '_result';
?>
<div id="<?= $controlId ?>"></div>
<span style="display: none;" id="<?= $nodeId ?>"></span>

<script>
	BX.ready(function ()
	{
		var addressData = <?= CUtil::PhpToJSObject($arResult['value']) ?>;
		var wrapperId = <?= CUtil::PhpToJSObject($controlId) ?>;
		var fieldName = <?= CUtil::PhpToJSObject($userField['FIELD_NAME']) ?>;
		var fieldFormName = <?= CUtil::PhpToJSObject($arResult['fieldName']) ?>;
		var isMultiple = <?= $userField['MULTIPLE'] === 'Y' ? 'true' : 'false' ?>;

		BX.Fileman.UserField.AddressField.init({
			wrapperId: wrapperId,
			addressData: addressData,
			mode: BX.Fileman.UserField.AddressField.EDIT_MODE,
			fieldFormName: fieldFormName,
			fieldName: fieldName,
			isMultiple: isMultiple,
			additionalProperties: {
				compactMode: true,
			},
		});
	});
</script>
