<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!empty($arResult["SEARCH_ITEMS"])):
?>
	<ul class="mp_search_list_solutions">
<?php
	foreach($arResult["SEARCH_ITEMS"] as $key => $app):
		$appUrl = str_replace(
			array("#app#"),
			array(urlencode($app['CODE'])),
			$arParams['DETAIL_URL_TPL']
		);
?>
		<li>
			<span class="mp_search_ls_img" href="/marketplace/?app=<?=htmlspecialcharsbx($app["CODE"])?>"><span><?if ($app["ICON"]):?><img src="<?=htmlspecialcharsbx($app["ICON"])?>" alt=""/><?endif?></span></span>
			<a href="<?=$appUrl;?>" class="mp_search_ls_shadow"></a>
			<a href="<?=$appUrl;?>" class="mp_search_ls_title"><?=htmlspecialcharsbx($app["NAME"])?></a>
		</li>
<?php
	endforeach;
?>
	</ul>
<?php
endif;
