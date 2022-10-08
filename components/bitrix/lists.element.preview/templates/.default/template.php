<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);
?>

<div class="list-element-preview">
	<div class="list-element-preview-header">
		<span class="list-element-preview-header-title">
			<?=$arResult['ENTITY_NAME']?>:
			<a href="<?=$arParams['URL']?>" target="_blank"><?=$arResult['ENTITY_TITLE']?></a>
		</span>
	</div>
	<table class="list-element-preview-info">
		<? foreach($arResult['FIELDS'] as $field): ?>
			<? if($field['NAME'] <> ''): ?>
				<tr>
					<td><?=$field['NAME']?>:</td>
					<td><?=$field['HTML']?></td>
				</tr>
			<? endif ?>
		<? endforeach ?>
	</table>
</div>