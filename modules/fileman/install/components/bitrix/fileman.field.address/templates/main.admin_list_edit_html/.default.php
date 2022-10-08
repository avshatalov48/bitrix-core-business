<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */

$isLocationIncluded = \Bitrix\Main\Loader::includeModule('location');
if (!$isLocationIncluded)
{
	echo '<div>' . $component->getLocationModuleMessage() . '</div>';
	return;
}

\Bitrix\Main\UI\Extension::load(['fileman.userfield.address_widget', 'userfield_address']);

$randString = $this->randString();
if ($component->isAjaxRequest())
{
	$randString .= time();
}

$wrapperId = 'address-wrapper-' . $arResult['userField']['ID'] . '_' . $randString;
?>

<span class="fields address field-wrap" id="<?= $wrapperId ?>">
</span>

<script>
	var addressData = <?= CUtil::PhpToJSObject($arResult['value']) ?>;
	var wrapperId = <?= CUtil::PhpToJSObject($wrapperId) ?>;

	BX.Fileman.UserField.AddressField.init({
		wrapperId: wrapperId,
		addressData: addressData,
		mode: BX.Fileman.UserField.AddressField.VIEW_MODE,
	});
</script>
