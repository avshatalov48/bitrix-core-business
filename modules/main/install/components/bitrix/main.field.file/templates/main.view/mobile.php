<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

?>

<span class="fields file field-wrap">
	<?php
	$i=0;
	$nodes = [];
	foreach($arResult['value'] as $fileInfo)
	{
		$id = $arParams['userField']['~id'] . '_' . $i++;
		$nodes[] = $id;
		?>
		<span class="fields file field-item">
			<?php

			$isImage = false;
			if(\CFile::IsImage($fileInfo['SRC'], $fileInfo['CONTENT_TYPE']))
			{
				$isImage = true;
			}

			$src = \CComponentEngine::MakePathFromTemplate(
				'/bitrix/components/bitrix/crm.deal.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#',
				[
					'file_id' => $fileInfo['ID'],
					'field_name' => $arResult['userField']['FIELD_NAME'],
					'owner_id' => $arResult['userField']['ENTITY_VALUE_ID']
				]
			);
			?>

			<span
				id="<?= $id ?>"
				data-url="<?= HtmlFilter::encode($src) ?>"
				data-is-image="<?= ($isImage) ? 'yes' : 'no' ?>"
			>
				<?= HtmlFilter::encode($fileInfo['ORIGINAL_NAME']) ?>
			</span> ( <?= \CFile::formatSize($fileInfo['FILE_SIZE']) ?>)
		</span>
	<?php } ?>
</span>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.File(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.File',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>