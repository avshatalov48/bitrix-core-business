<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('fx', 'ajax', 'dd'));
$APPLICATION->AddHeadScript('/bitrix/js/main/file_upload_agent.js');
$uid = $arParams['CONTROL_ID'];
$controller = "BX('file-selectdialog-".$uid."')";
$controlName = $arParams['INPUT_NAME'];
$controlNameFull = $controlName . (($arParams['MULTIPLE'] == 'Y') ? '[]' : '');
$arValue = $arResult['FILES'];
$addClass = ((strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS') !== false) ? 'file-filemacos' : '');

if (!function_exists('mfi_format_line'))
{
	function mfi_format_line($arValue, $uid, $controlNameFull)
	{
		$result = '';

		if (is_array($arValue) && sizeof($arValue) > 0)
		{
			ob_start();
			foreach ($arValue as $arElement)
			{
				$elementID = intval($arElement['ID']);
?>
				<tr class="file-inline-file" id="wd-doc<?=$elementID?>">
					<td class="files-name">
						<span class="files-text">
							<span class="f-wrap"><?=htmlspecialcharsEx($arElement['ORIGINAL_NAME'])?></span>
						</span>
					</td>
					<td class="files-size"><?=CFile::FormatSize($arElement["FILE_SIZE"])?></td>
					<td class="files-storage">
						<div class="files-storage-block">&nbsp;
							<span class='del-but' onclick="BfileFD<?=$uid?>.agent.StopUpload(BX('wd-doc<?=$elementID?>'));"></span>
							<span class="files-placement"><?/*=htmlspecialcharsEx($title)*/?></span>
							<input id="file-doc<?=$elementID?>" type="hidden" name="<?=htmlspecialcharsbx($controlNameFull)?>" value="<?=$elementID?>" />
						</div>
					</td>
				</tr>
<?
			}
			$result = ob_get_clean();
		}

		return $result;
	}
}

?>
<div id="file-selectdialog-<?=$uid?>" class="file-selectdialog" style="display:none;">
	<table id="file-file-template" style='display:none;'>
		<tr class="file-inline-file" id="file-doc">
			<td class="files-name">
				<span class="files-text">
					<span class="f-wrap" data-role='name'>#name#</span>
				</span>
			</td>
			<td class="files-size" data-role='size'>#size#</td>
			<td class="files-storage">
				<div class="files-storage-block">
					<span class="files-placement">&nbsp;</span>
				</div>
			</td>
		</tr>
	</table>
	<div id="file-image-template" style='display:none;'>
		<span class="feed-add-photo-block">
			<span class="feed-add-img-wrap">
				<img width="90" height="90" border="0" data-role='image'>
			</span>
			<span class="feed-add-img-title" data-role='name'>#name#</span>
			<span class="feed-add-post-del-but"></span>
		</span>
	</div>
	<div class="file-extended">
		<span class="file-label"><?=GetMessage('BFDND_FILES')?></span>
		<div class="file-placeholder">
			<table class="files-list" cellspacing="0">
				<tbody class="file-placeholder-tbody">
					<?=mfi_format_line($arValue, $uid, $controlNameFull);?>
				</tbody>
			</table>
		</div>
		<div class="file-selector">
			<?=GetMessage('BFDND_DROPHERE');?><br />
			<span class="file-uploader"><span class="file-but-text"><?=GetMessage('BFDND_SELECT_EXIST');?></span><input class="file-fileUploader <?=$addClass?>" id="file-fileUploader-<?=$uid?>" type="file" multiple='multiple' size='1' /></span>
			<div class="file-load-img"></div>
		</div>
	</div>
	<div class="file-simple" style='padding:0; margin:0;'>
		<span class="file-label"><?=GetMessage('BFDND_FILES')?></span>
		<div class="file-placeholder">
			<table class="files-list" cellspacing="0">
				<tbody class="file-placeholder-tbody">
					<tr style='display: none;'><td colspan='3'></td></tr>
					<?=mfi_format_line($arValue, $uid, $controlNameFull);?>
				</tbody>
			</table>
		</div>
		<div class="file-selector"><span class="file-uploader"><span class="file-uploader-left"></span><span class="file-but-text"><?=GetMessage('BFDND_SELECT_LOCAL');?></span><span class="file-uploader-right"></span><input class="file-fileUploader <?=$addClass?>" id="file-fileUploader-<?=$uid?>" type="file" <?/*multiple='multiple'*/?> size='1' /></span></div></div>
	<script>
	BX.ready(function(){
		BX.message({
			'loading' : "<?=(GetMessageJS('BFDND_FILE_LOADING'))?>",
			'file_exists':"<?=(GetMessageJS('BFDND_FILE_EXISTS'))?>",
			'upload_error':"<?=(GetMessageJS('BFDND_UPLOAD_ERROR'))?>",
			'access_denied':"<p style='margin-top:0;'><?=(GetMessageJS('BFDND_ACCESS_DENIED'))?></p>"
		});
		BX.addCustomEvent(<?=$controller?>.parentNode, "BFileDLoadFormController", function(status) {
			MFIDD({
					uid : '<?=$uid?>',
					appCode: '<?=$arParams['APP_CODE'];?>',
					controller : <?=$controller?>,
					CID : "<?=$arResult['CONTROL_UID']?>",
					id : "<?=$arParams['CONTROL_ID']?>",
					upload_path : "<?=CUtil::JSEscape(htmlspecialcharsback(POST_FORM_ACTION_URI))?>",
					multiple : <?=( $arParams['MULTIPLE'] == 'N' ? 'false' : 'true' )?>,
					inputName : "<?=CUtil::JSEscape($controlName)?>",
					status : status
			});
		});
		<? if (sizeof($arValue) >= 1) { ?>
		BX.onCustomEvent(<?=$controller?>.parentNode, "BFileDLoadFormController");
		<?}?>
	});
	</script>
</div>
