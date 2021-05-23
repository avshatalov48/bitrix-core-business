<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

?>

<div class="landing-html-lock landing-html-lock-partner">
	<div class="landing-html-lock-inner">
		<div class="landing-html-lock-title-block">
			<span class="landing-html-lock-title"><?= $arParams['~HEADER'];?></span>
		</div>
		<div class="landing-html-lock-text-block">
			<div class="landing-html-lock-text"><?= $arParams['~MESSAGE'];?></div>
		</div>
		<?if ($arParams['~BUTTON'] && $arParams['~LINK']):?>
			<a href="<?= $arParams['~LINK'];?>" target="_top" class="ui-btn ui-btn-md ui-btn-primary landing-required-link">
				<?= $arParams['~BUTTON'];?>
			</a>
		<?endif;?>
	</div>
</div>