<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\FileInputUtility;

global $APPLICATION;

/**
 * @var $arResult array
 */
?>

<span class='field-wrap'>
	<span class='field-item'>
	 	<?php
		$fileInputUtility = FileInputUtility::instance();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'.default',
			[
				'CONTROL_ID' => $fileInputUtility->getUserFieldCid($arResult['userField']),
				'INPUT_NAME' => $arResult['fieldName'],
				'INPUT_NAME_UNSAVED' => 'tmp_' . $arResult['fieldName'],
				'INPUT_VALUE' => $arResult['value'],
				'MULTIPLE' => ($arResult['userField']['MULTIPLE'] === 'Y' ? 'Y' : 'N'),
				'MODULE_ID' => 'uf',
				'ALLOW_UPLOAD' => 'A',
			]
		);
		?>
	</span>
</span>
