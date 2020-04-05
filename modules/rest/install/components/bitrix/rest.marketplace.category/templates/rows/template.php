<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load("ui.buttons");

if (!is_array($arResult["ITEMS"]))
	return;
?>
<div class="mp-apps-wrapper">
	<div class="mp-apps-title"><?=$arParams["TITLE"]?></div>
	<ul class="mp-apps-list">
		<?
		foreach($arResult["ITEMS"] as $app)
		{
			$appUrl = str_replace(
				array("#app#"),
				array(urlencode($app['CODE'])),
				$arParams['DETAIL_URL_TPL']
			);
			$appInstalled = in_array($app['CODE'], $arResult['ITEMS_INSTALLED']);
			?>
			<li class="mp-apps-item">
				<a href="javascript:void(0)" class="mp-apps-icon mp-apps-icon--grey">
					<?
					if($app["ICON"]):
						?>
						<img src="<?=htmlspecialcharsbx($app["ICON"])?>" alt="">
						<?php
					else:
						?>
						<span class="mp_empty_icon"></span>
						<?php
					endif;
					?>
				</a>
				<div class="mp-apps-info-container">
					<span class="mp-apps-subtitle"><?=htmlspecialcharsbx($app["NAME"])?></span>
					<div class="mp-apps-desc">
						<?
						if (isset($app["SHORT_DESC"]))
							echo ($app["SHORT_DESC"]);
						?>
					</div>
					<?if ($appInstalled):?>
						<a href="<?=$appUrl?>" class="ui-btn ui-btn-light-border" onclick="BX.Rest.Markeplace.CategoryRows.setCurrentApp('<?=CUtil::JSEscape($app['CODE'])?>')"><?=GetMessage("MARKETPLACE_CATEGORY_ROWS_DEINSTALL")?></a>
					<?else:?>
						<a href="<?=$appUrl?>" class="ui-btn ui-btn-success" data-id="btn-<?=$app['CODE']?>" onclick="BX.Rest.Markeplace.CategoryRows.setCurrentApp('<?=CUtil::JSEscape($app['CODE'])?>')"><?=GetMessage("MARKETPLACE_CATEGORY_ROWS_INSTALL")?></a>
					<?endif?>
				</div>
			</li>
			<?
		}
		?>
	</ul>
</div>

<script>
	(function(){
		BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: false});

		BX.message({
			"MARKETPLACE_CATEGORY_ROWS_INSTALL" : "<?=GetMessageJS("MARKETPLACE_CATEGORY_ROWS_INSTALL")?>",
			"MARKETPLACE_CATEGORY_ROWS_DEINSTALL" : "<?=GetMessageJS("MARKETPLACE_CATEGORY_ROWS_DEINSTALL")?>"
		});

		BX.ready(function ()
		{
			BX.Rest.Markeplace.CategoryRows.init();
		});
	})();
</script>