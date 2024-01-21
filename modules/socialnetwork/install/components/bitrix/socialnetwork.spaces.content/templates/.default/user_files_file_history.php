<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */
?>

<div class="sn-spaces__group">
<?php
	$APPLICATION->includeComponent(
		'bitrix:disk.file.history',
		'',
		[
			'STORAGE' => $arParams['STORAGE'],
			'FILE' => $arParams['FILE'],
			'FILE_ID' => $arParams['FILE_ID'],
		],
		$component
	);
?>
</div>
