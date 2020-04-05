<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="bx-idea-stat">
	<div class="bx-idea-stat-lt-corn">&nbsp;</div>
	<div class="bx-idea-stat-rt-corn">&nbsp;</div>
	<div class="bx-idea-stat-lb-corn">&nbsp;</div>
	<div class="bx-idea-stat-rb-corn">&nbsp;</div>
	<div class="bx-idea-stat-header"><?=GetMessage("IDEA_STATISTIC_TITLE")?></div>
	<div class="bx-idea-stat-cont">
	<?foreach($arResult as $key=>$StatInfo):?>
		<div class="bx-idea-stat-line">
			<div class="bx-idea-stat-doted">
				<span class="bx-idea-stat-line-text"><?=$StatInfo["VALUE"]?></span>
				<span class="bx-idea-stat-line-r"><a href="<?=$StatInfo["URL"]?>"><?=intval($StatInfo["CNT"])?></a></span>
			</div>
		</div>
		<?endforeach;?>
	</div>
</div>