<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$frame = $this->createFrame()->begin(false);
?>
<!-- Bitrix24.LiveChat external config -->
<script type="text/javascript">
	window.BxLiveChatInit = function() {
		var config = <?=CUtil::PhpToJSObject($arResult['CONFIG'])?>;
		config.firstMessage = config.firstMessage.replace('#VAR_HOST#', location.hostname).replace('#VAR_PAGE#', '[url='+location.href+']'+(document.title || location.href)+'[/url]');
		BX.LiveChat.setCookie('LIVECHAT_HASH', '<?=$arResult['HASH']?>', {expires: 600000, path: '/'});
		return config;
	};
	<?if ($arResult['GA_MARK']):?>
	(window.BxLiveChatLoader = window.BxLiveChatLoader || []).push(function() {
		BX.LiveChat.addEventListener(window, 'message', function(event){
			if(event && event.origin == BX.LiveChat.sourceDomain)
			{
				var data = {}; try { data = JSON.parse(event.data); } catch (err){} if(!data.action) return;
				if (data.action == 'sendMessage')
				{
					if (typeof(dataLayer) == 'undefined')
					{
						dataLayer = [];
					}
					dataLayer.push({'event': '<?=$arResult['GA_MARK']?>'});
				}
			}
		});
	});
	<?endif;?>
</script>
<!-- /Bitrix24.LiveChat external config -->
<?
$frame->end();
?>