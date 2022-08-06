<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load(['fileman.userfield.address_widget', 'userfield_address']);

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */
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
