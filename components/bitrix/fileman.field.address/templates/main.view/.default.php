<?php

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Text\HtmlFilter;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */

$isLocationIncluded = \Bitrix\Main\Loader::includeModule('location');

if (
	($arResult['additionalParameters']['printable'] ?? false)
	|| !$isLocationIncluded
)
{
	?>
	<span class="fields address field-wrap">
	<?php
	foreach ($arResult['value'] as $value)
	{
		?>
		<span class="fields address field-item">
			<?php
			$parsedValue = AddressType::parseValue($value);
			print HtmlFilter::encode($parsedValue[0]);
			?>
		</span>
		<?php
	}
	?>
	</span>
	<?php

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
	BX.ready(function(){
		var addressData = <?= CUtil::PhpToJSObject($arResult['value']) ?>;
		var wrapperId = <?= CUtil::PhpToJSObject($wrapperId) ?>;

		BX.Runtime.loadExtension('fileman.userfield.address_widget').then(function (){
			BX.Fileman.UserField.AddressField.init({
				wrapperId: wrapperId,
				addressData: addressData,
				mode: BX.Fileman.UserField.AddressField.VIEW_MODE,
			});
		});
	});
</script>
