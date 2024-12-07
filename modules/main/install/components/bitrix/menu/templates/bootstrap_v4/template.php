<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

if (empty($arResult["ALL_ITEMS"]))
	return;

CUtil::InitJSCore();
\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

$menuBlockId = "catalog_menu_".$this->randString();
?>
<div class="bx-top-nav bx-<?=$arParams["MENU_THEME"]?>" id="<?=$menuBlockId?>">
	<nav class="bx-top-nav-container" id="cont_<?=$menuBlockId?>">
		<ul class="bx-nav-list-1-lvl" id="ul_<?=$menuBlockId?>">
		<?
		foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns)
		{
		    //--first level--
			$existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] || $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;
			$class = "bx-nav-1-lvl bx-nav-list-".(($existPictureDescColomn) ? count($arColumns)+1 : count($arColumns))."-col";
			if($arResult["ALL_ITEMS"][$itemID]["SELECTED"])
			{
				$class .= " bx-active";
			}
			if(is_array($arColumns) && !empty($arColumns))
			{
				$class .= " bx-nav-parent";
			}
		?>
			<li
				class="<?=$class?>"
				onmouseover="BX.CatalogMenu.itemOver(this);"
				onmouseout="BX.CatalogMenu.itemOut(this)"
				<?if (is_array($arColumns) && !empty($arColumns)):?>
					data-role="bx-menu-item"
					onclick="if (BX.hasClass(document.documentElement, 'bx-touch')) obj_<?=$menuBlockId?>.clickInMobile(this, event);"
				<?endif?>
			>
				<a
					class="bx-nav-1-lvl-link"
					href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>"
					<?if (is_array($arColumns) && !empty($arColumns) && $existPictureDescColomn):?>
						onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemID?>');"
					<?endif?>
				>
					<span class="bx-nav-1-lvl-link-text">
						<?=htmlspecialcharsbx($arResult["ALL_ITEMS"][$itemID]["TEXT"], ENT_COMPAT, false)?>
						<?if (is_array($arColumns) && !empty($arColumns)):?> <i class="bx-nav-angle-bottom"></i><?endif?>
					</span>
				</a>
				<?
				if (is_array($arColumns) && !empty($arColumns))
				{
				?>
					<span class="bx-nav-parent-arrow" onclick="obj_<?=$menuBlockId?>.toggleInMobile(this)"><i class="bx-nav-angle-bottom"></i></span> <!-- for mobile -->
					<div class="bx-nav-2-lvl-container">
						<?
						foreach($arColumns as $key=>$arRow)
						{
						?>
							<ul class="bx-nav-list-2-lvl">
							<?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?>  <!-- second level-->
								<li class="bx-nav-2-lvl">
									<a class="bx-nav-2-lvl-link"
										href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>"
										<?if ($existPictureDescColomn):?>
											onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemIdLevel_2?>');"
										<?endif?>
										data-picture="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["picture_src"]?>"
										<?if($arResult["ALL_ITEMS"][$itemIdLevel_2]["SELECTED"]):?>class="bx-active"<?endif?>
									>
										<span class="bx-nav-2-lvl-link-text"><?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?></span>
									</a>
								<?if (is_array($arLevel_3) && !empty($arLevel_3)):?>
									<ul class="bx-nav-list-3-lvl">
									<?foreach($arLevel_3 as $itemIdLevel_3):?>	<!-- third level-->
										<li class="bx-nav-3-lvl">
											<a
												class="bx-nav-3-lvl-link"
												href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["LINK"]?>"
												<?if ($existPictureDescColomn):?>
													onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemIdLevel_3?>');return false;"
												<?endif?>
												data-picture="<?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["PARAMS"]["picture_src"]?>"
												<?if($arResult["ALL_ITEMS"][$itemIdLevel_3]["SELECTED"]):?>class="bx-active"<?endif?>
											>
												<span class="bx-nav-3-lvl-link-text"><?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["TEXT"]?></span>
											</a>
										</li>
									<?endforeach;?>
									</ul>
								<?endif?>
								</li>
							<?endforeach;?>
							</ul>
						<?
						}
						?>
						<?if ($existPictureDescColomn):?>
							<div class="bx-nav-list-2-lvl bx-nav-catinfo dbg" data-role="desc-img-block">
								<a class="bx-nav-2-lvl-link-image" href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>">
									<img src="<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"]?>" alt="">
								</a>
								<p><?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]?></p>
							</div>
						<?endif?>
					</div>
				<?
				}
				?>
			</li>
		<?
		}
		?>
		</ul>
	</nav>
</div>

<script>
	BX.ready(function () {
		window.obj_<?=$menuBlockId?> = new BX.Main.MenuComponent.CatalogHorizontal('<?=CUtil::JSEscape($menuBlockId)?>', <?= Json::encode($arResult["ITEMS_IMG_DESC"]) ?>);
	});
</script>