<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Global variables
 * @var array $arResult
 */
?>
<div class="urlpreview-mobile">
	<span class="urlpreview-mobile-title">
		<a href="<?= $arResult['METADATA']['URL']?>" target="_blank">
			<span class="urlpreview-mobile-title-name"><?=$arResult['METADATA']['TITLE']?></span>
		</a>
	</span>
	<?if($arResult['METADATA']['DESCRIPTION']):?>
		<span class="urlpreview-mobile-description"><?=$arResult['METADATA']['DESCRIPTION']?></span>
	<?endif?>
	<?if(isset($arResult['METADATA']['EMBED'])):?>
		<div class="urlpreview-mobile-embed">
			<?=$arResult['METADATA']['EMBED']?>
		</div>
	<?elseif($arResult['METADATA']['IMAGE']):?>
		<a href="<?= $arResult['METADATA']['URL']?>" target="_blank">
			<span class="urlpreview-mobile-image">
				<img src="<?= $arResult['METADATA']['IMAGE']?>" onerror="this.style.display='none';" class="urlpreview-mobile-image-img">
			</span>
		</a>
	<?endif?>
</div>
<?if(isset($arResult['METADATA']['EMBED']))
{?>
<script>
if(!window.BXUrlPreview)
{
	window.BXUrlPreview = function(){};
	window.BXUrlPreview.adjustFrameHeight = function(iframe, counter)
	{
		if(BX.hasClass(iframe, 'urlpreview-iframe-html-embed-adjusted'))
		{
			return;
		}
		counter = counter || 0;
		if(counter > 10)
		{
			return;
		}
		var addToHeight = 50;
		if(iframe.contentWindow.document.body.scrollHeight > iframe.height)
		{
			iframe.height = iframe.contentWindow.document.body.scrollHeight + addToHeight + "px";
			BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
			return;
		}
		var videos = iframe.contentWindow.document.getElementsByTagName('video');
		if(videos[0])
		{
			iframe.height = iframe.contentWindow.document.body.scrollHeight + addToHeight + "px";
			BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
			return;
		}
		else
		{
			var iframes = iframe.contentWindow.document.getElementsByTagName('iframe');
			var height = 0;
			for(var i = 0; i < iframes.length; i++)
			{
				if(iframes[i] && iframes[i].height > 0)
				{
					height = parseInt(iframes[i].height);
				}
				else if (iframes[i] && iframes[i].style.height)
				{
					height = parseInt(iframes[i].style.height);
				}
				if(height !== 0)
				{
					iframe.height = height + addToHeight + 'px';
					BX.addClass(iframe, 'urlpreview-iframe-html-embed-adjusted');
				}
			}
			if(height === 0)
			{
				setTimeout(function()
				{
					counter++;
					BXUrlPreview.adjustFrameHeight(iframe, counter);
				}, 500);
			}
		}
	};
}
</script>
<?}?>