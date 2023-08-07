<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript">
	jsBXMI.Init(
		{
			'STEP': '<?echo CUtil::JSEscape($arResult["informer"]["step"]);?>',		
			'STEPS': '<?echo CUtil::JSEscape($arResult["informer"]["steps"]);?>',
			'ID': '<?echo CUtil::JSEscape($arParams["ID"])?>',
			'TEXT': <?echo CUtil::PhpToJSObject($arResult["text"])?>			
		}
	);
</script>
		
<div class="wd-infobox wd-info-banner"><?
	?><div class="wd-infobox-inner"><?
		?><div class="wd-info-banner-head"><?
			?><a href="#banner" class="btn-close" onclick="BXWdCloseBnr(this.parentNode.parentNode.parentNode);return false;" <?
				?>title="<?=GetMessage('WD_BANNER_CLOSE')?>"></a></div>
		<div class="wd-info-banner-body">
			<table cellpadding="0" border="0" class="wd-info-banner-body">
				<tr>
					<th class="wd-info-banner-icon" rowspan="2">
						<a class="wd-info-banner-icon"></a>
					</th>
					<td class="wd-info-banner-content">
						<div class="wd-info-banner-content" id="wd_informer_text">
							<?=$arResult["text"][$arResult["informer"]["step"]-1]?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="wd-info-banner-buttons"><?
					?><a href="#next" onclick="BXWdStepBnr(document.getElementById('wd_informer_text'), this.nextSibling, this, 'next'); <?
						?> return false;" class="bx-bnr-button" <?
						?><?=($arResult["informer"]["step"] >= $arResult["informer"]["steps"] ? "style='display:none;'" : "")?><?
						?>><?=GetMessage("WD_NEXT_ADVICE")?></a><?
					?><a href="#prev" onclick="BXWdStepBnr(document.getElementById('wd_informer_text'), this, this.previousSibling, 'prev'); <?
						?>return false;" class="bx-bnr-button" <?
						?><?=($arResult["informer"]["step"] <= 1 ? "style='display:none;'" : "")?><?
						?>><?=GetMessage("WD_PREV_ADVICE")?></a><?
					?>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>