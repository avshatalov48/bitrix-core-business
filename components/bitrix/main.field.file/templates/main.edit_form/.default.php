<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var FileUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
$name = $arResult['additionalParameters']['NAME'];


if($arResult['userField']['MULTIPLE'] === 'Y')
{
	$values = [];

	$fieldName = $arResult['additionalParameters']['NAME'];
	if(($p = mb_strpos($fieldName, '[')))
	{
		$fieldName = mb_substr($fieldName, 0, $p);
	}

	$result = '';

	foreach($arResult['value'] as $key => $fileId)
	{
		if($fileId)
		{
			$values[$fieldName . '[' . $key . ']'] = $fileId;
		}
	}

	print CFileInput::ShowMultiple(
		$values,
		$fieldName . "[n#IND#]",
		[
			"IMAGE" => "Y",
			"PATH" => "Y",
			"FILE_SIZE" => "Y",
			"DIMENSIONS" => "Y",
			"IMAGE_POPUP" => "Y",
			"MAX_SIZE" => ["W" => 200, "H" => 200]
		],
		false,
		[
			'upload' => ($arResult['userField']['EDIT_IN_LIST'] === 'Y'),
			'medialib' => false,
			'file_dialog' => false,
			'cloud' => false,
			'del' => true,
			'description' => false
		]
	);

	foreach($arResult['value'] as $key => $fileId)
	{
		if($fileId)
		{
			?>
			<input
				type="hidden"
				name="<?= $fieldName ?>_old_id[<?= $key ?>]"
				value="<?= $fileId ?>"
			>
			<?php
		}
	}
}
else
{
	if(($p = mb_strpos($name, '[')))
	{
		$strOldIdName = mb_substr($name, 0, $p).'_old_id'.mb_substr($name, $p);
	}
	else
	{
		$strOldIdName = $name . '_old_id';
	}

	print CFileInput::Show(
		$name,
		$arResult['additionalParameters']['VALUE'],
		[
			'IMAGE' => 'Y',
			'PATH' => 'Y',
			'FILE_SIZE' => 'Y',
			'DIMENSIONS' => 'Y',
			'IMAGE_POPUP' => 'Y',
			'MAX_SIZE' => ['W' => 200, 'H' => 200]
		],
		[
			'upload' => ($arResult['userField']["EDIT_IN_LIST"] === 'Y'),
			'medialib' => false,
			'file_dialog' => false,
			'cloud' => false,
			'del' => true,
			'description' => false
		]
	);
	?>
	<input
		type="hidden"
		name="<?= $strOldIdName ?>"
		value="<?= $arResult['additionalParameters']['VALUE'] ?>"
	>
	<?php
}