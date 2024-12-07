<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$APPLICATION->SetAdditionalCSS(CUtil::GetAdditionalFileURL('/bitrix/js/mobileapp/interface.css'));

if($arResult['GET_JS'])
{
	echo '<script>'.
			file_get_contents($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/script.js').
		'</script>';
}
?>
<script>
	topSwichControl = new __MATopSwitchersControl({
							itemSelectedId: "<?=$arResult['SELECTED']?>",
							callbackFunc: "<?=$arResult['JS_CALLBACK_FUNC']?>"
	});
</script>
<div class="order_nav">
	<ul>
		<?foreach ($arParams["ITEMS"] as $key => $text):?>
			<li id="top_sw_<?=$key?>" <?=$arResult["SELECTED"] == $key ? 'class="current"' : ''?>>
				<a href="javascript:void(0);"><?=$text?></a>
				<script>
					topSwichControl.setFastButton("<?=$key?>");
				</script>
			</li>
		<?endforeach;?>
	</ul>
	<div class="clb"></div>
</div>
