<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["FILE"] <> ''):

	if (!$GLOBALS["bSidebarSearchShown"]):
		?>
		<div class="rounded-block">
			<div class="corner left-top"></div><div class="corner right-top"></div>
			<div class="block-content">
				<?$APPLICATION->IncludeComponent("bitrix:search.form", "main", Array(
					"PAGE"	=>	SITE_DIR."search/"
					)
				);?>
			</div>
			<div class="corner left-bottom"></div><div class="corner right-bottom"></div>
		</div>
		<?
	endif;

	include($arResult["FILE"]);
	$GLOBALS["bSidebarSearchShown"] = true;
endif;
?>