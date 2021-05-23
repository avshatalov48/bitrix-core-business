<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$frame = $this->createFrame()->begin(false);
?>
<!-- Bitrix24.LiveChat external config -->
<script type="text/javascript">
	window.addEventListener('onBitrixLiveChat', function(event)
	{
		var config = <?=CUtil::PhpToJSObject($arResult['CONFIG'])?>;
		var widget = event.detail.widget;

		widget.setUserRegisterData(
			config.user
		);
		widget.setCustomData(
			config.firstMessage.replace('#VAR_HOST#', location.hostname).replace('#VAR_PAGE#', '[url='+location.href+']'+(document.title || location.href)+'[/url]')
		);

	<?if ($arResult['GA_MARK']):?>
		widget.subscribe({
			type: BX.LiveChatWidget.SubscriptionType.userMessage,
			callback: function(data)
			{
				if (typeof(dataLayer) == 'undefined')
				{
					dataLayer = [];
				}
				dataLayer.push({'event': '<?=$arResult['GA_MARK']?>'});
			}
		});
	<?endif;?>

	});
</script>
<!-- /Bitrix24.LiveChat external config -->
<?
$frame->end();
?>