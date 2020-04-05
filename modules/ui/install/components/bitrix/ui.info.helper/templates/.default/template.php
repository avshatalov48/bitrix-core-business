<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? $frame = $this->createFrame()->begin('');

\Bitrix\Main\UI\Extension::load('ui.info-helper');
?>

<script>
	BX.ready(function () {
		BX.UI.InfoHelper.init({
			frameUrlTemplate: '<?=$arResult["NOTIFY_URL"]?>'
		});
	});
</script>

<? $frame->end(); ?>