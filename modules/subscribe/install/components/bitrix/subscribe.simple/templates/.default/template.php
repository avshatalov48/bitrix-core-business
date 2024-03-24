<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>

<?php if ($arResult['ERRORS']):?>
	<?php foreach ($arResult['ERRORS'] as $strError):?>
		<p class="errortext"><?php echo $strError?></p>
	<?php endforeach?>
	<?php $this->setFrameMode(false);?>
<?php elseif (!$arResult['RUBRICS']):?>
	<p class="errortext"><?php echo GetMessage('CT_BSS_NO_RUBRICS_FOUND')?></p>
	<?php $this->setFrameMode(false);?>
<?php else:?>
	<?php $frame = $this->createFrame()->begin();?>
	<?php if ($arResult['MESSAGE']):?>
		<p class="notetext"><?php echo $arResult['MESSAGE']?></p>
	<?php endif?>
	<form method="POST" action="<?php echo $arResult['FORM_ACTION']?>">
		<table class="data-table">
			<tbody>
			<tr>
				<td>
					<?php foreach ($arResult['RUBRICS'] as $arRubric):?>
						<input name="RUB_ID[]" value="<?php echo $arRubric['ID']?>" id="RUB_<?php echo $arRubric['ID']?>" type="checkbox" <?php echo ($arRubric['CHECKED']) ? 'checked' : '';?>><label for="RUB_<?php echo $arRubric['ID']?>"><?php echo $arRubric['NAME']?></label><br>
					<?php endforeach?>
					<br>
					<input name="FORMAT" value="text" id="FORMAT_text" type="radio" <?php echo ($arResult['FORMAT'] === 'text') ? 'checked' : '';?>><label for="FORMAT_text"><?php echo GetMessage('CT_BSS_TEXT')?></label>
					&nbsp;/&nbsp;
					<input name="FORMAT" value="html" id="FORMAT_html" type="radio" <?php echo ($arResult['FORMAT'] === 'html') ? 'checked' : '';?>><label for="FORMAT_html"><?php echo GetMessage('CT_BSS_HTML')?></label>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td>
					<?php echo bitrix_sessid_post();?>
					<input type="submit" name="Update" value="<?php echo GetMessage('CT_BSS_FORM_BUTTON')?>">
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
	<?php $frame->beginStub();?>
	<form method="POST" action="<?php echo $arResult['FORM_ACTION']?>">
		<table class="data-table">
			<tbody>
			<tr>
				<td>
					<?php foreach ($arResult['RUBRICS'] as $arRubric):?>
						<input name="RUB_ID[]" value="<?php echo $arRubric['ID']?>" id="RUB_<?php echo $arRubric['ID']?>" type="checkbox"><label for="RUB_<?php echo $arRubric['ID']?>"><?php echo $arRubric['NAME']?></label><br>
					<?php endforeach?>
					<br>
					<input name="FORMAT" value="text" id="FORMAT_text" type="radio"><label for="FORMAT_text"><?php echo GetMessage('CT_BSS_TEXT')?></label>
					&nbsp;/&nbsp;
					<input name="FORMAT" value="html" id="FORMAT_html" type="radio"><label for="FORMAT_html"><?php echo GetMessage('CT_BSS_HTML')?></label>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td>
					<input type="submit" name="Update" value="<?php echo GetMessage('CT_BSS_FORM_BUTTON')?>">
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
	<?php $frame->end();?>
<?php endif;
