<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="bx-desktop bx-im-fullscreen">
	<table class="bx-im-fullscreen-table">
		<tr>
			<td class="bx-im-fullscreen-td1" height="15%">
				<div class="bx-im-fullscreen-logo"></div>
				<div class="bx-im-fullscreen-back"><a href="/" class="bx-im-fullscreen-back-link"><?=GetMessage((IsModuleInstalled('intranet')?'IM_FULLSCREEN_BACK': 'IM_FULLSCREEN_BACK_BUS'))?></a></div>
			</td>
		</tr>
		<tr>
			<td class="bx-im-fullscreen-td2" ><div class="bx-desktop-placeholder" id="workarea-content"></div></td>
		</tr>
		<tr>
			<td class="bx-im-fullscreen-td3"><?=GetMessage('IM_FULLSCREEN_COPYRIGHT')?></td>
		</tr>
	</table>
</div>
<script type="text/javascript">
	document.title = '<?=GetMessage('IM_FULLSCREEN_TITLE_2')?>';
	<?=CIMMessenger::GetTemplateJS(Array(), $arResult)?>
</script>