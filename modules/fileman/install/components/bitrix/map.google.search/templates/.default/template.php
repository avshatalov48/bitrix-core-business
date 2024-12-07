<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$this->setFrameMode(true);

if($arParams['BX_EDITOR_RENDER_MODE'] == 'Y')
{
	echo '<img src="/bitrix/components/bitrix/map.google.search/templates/.default/images/preview.png" border="0" />';
}
else
{
	?>
	<div class="bx-google-search-layout">
		<div class="bx-google-search-form">
			<form name="search_form_<?echo $arParams['MAP_ID']?>" onsubmit="jsGoogleSearch_<?echo $arParams['MAP_ID']?>.searchByAddress(this.address.value); return false;">
				<?echo GetMessage('MYMS_TPL_SEARCH')?>: <input type="text" name="address" value="" style="width: 300px;" /><input type="submit" value="<?echo GetMessage('MYMS_TPL_SUBMIT')?>" />
			</form>
		</div>

		<div class="bx-google-search-results" id="results_<?echo $arParams['MAP_ID']?>"></div>

		<div class="bx-google-search-map">
	<?
	$APPLICATION->IncludeComponent('bitrix:map.google.system', '.default', $arParams, null, array('HIDE_ICONS' => 'Y'));
	?>
		</div>

	</div>
	<script>
	function BXWaitForMap_search<?echo $arParams['MAP_ID']?>()
	{
		if (('\v'=='v') && (null == window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>']))
		{
			setTimeout(BXWaitForMap_search<?echo $arParams['MAP_ID']?>, 300);
		}
		else
		{
			window.jsGoogleSearch_<?echo $arParams['MAP_ID']?> = new JCBXGoogleSearch('<?echo $arParams['MAP_ID']?>', document.getElementById('results_<?echo $arParams['MAP_ID']?>'), {
				mess_error: '<?echo GetMessage('MYMS_TPL_JS_ERROR')?>',
				mess_search: '<?echo GetMessage('MYMS_TPL_JS_SEARCH')?>',
				mess_found: '<?echo GetMessage('MYMS_TPL_JS_RESULTS')?>',
				mess_search_empty: '<?echo GetMessage('MYMS_TPL_JS_RESULTS_EMPTY')?>'
			});
		}
	}

	BX.ready(function () {setTimeout(BXWaitForMap_search<?echo $arParams['MAP_ID']?>, 300)});
	</script>
<?
}
?>