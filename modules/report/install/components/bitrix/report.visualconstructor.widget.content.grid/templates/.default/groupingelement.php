<?if(!empty($arResult['link'])):?>
	<a href="<?=$arResult['link']?>" target="_top">
<?endif;?>
		<div class="report-widget-grid-grouping">
			<? if(!empty($arResult['logo'])): ?>
				<div class="ui-icon ui-icon-common-user report-widget-grid-grouping-icon"><i style="background-image: url(<?=$arResult['logo']?>)"></i></div>
			<? elseif(!empty($arResult['defaultUserLogo'])):?>
				<div class="ui-icon ui-icon-common-user report-widget-grid-grouping-icon"><i></i></div>
			<? endif;?>
			<div class="report-widget-grid-grouping-name">
				<?=$arResult['title']?>
			</div>
		</div>
<?if(!empty($arResult['link'])):?>
	</a>
<?endif;?>
