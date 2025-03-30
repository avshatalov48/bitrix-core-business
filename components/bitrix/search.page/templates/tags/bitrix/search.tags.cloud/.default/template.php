<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if ($arParams['SHOW_CHAIN'] != 'N' && !empty($arResult['TAGS_CHAIN'])):
?>
<noindex>
	<div class="search-tags-chain" <?=$arParams['WIDTH']?>><?php
		foreach ($arResult['TAGS_CHAIN'] as $tags):
			?><a href="<?=$tags['TAG_PATH']?>" rel="nofollow"><?=$tags['TAG_NAME']?></a> <?php
			?>[<a href="<?=$tags['TAG_WITHOUT']?>" class="search-tags-link" rel="nofollow">x</a>]  <?php
		endforeach;?>
	</div>
</noindex>
<?php
endif;

if (is_array($arResult['SEARCH']) && !empty($arResult['SEARCH'])):
?>
<noindex>
	<div class="search-tags-cloud" <?=$arParams['WIDTH']?>><?php
		foreach ($arResult['SEARCH'] as $key => $res)
		{
		?><a href="<?=$res['URL']?>" style="font-size: <?=$res['FONT_SIZE']?>px; color: #<?=$res['COLOR']?>;px" rel="nofollow"><?=$res['NAME']?></a> <?php
		}
	?></div>
</noindex>
<?php
endif;
