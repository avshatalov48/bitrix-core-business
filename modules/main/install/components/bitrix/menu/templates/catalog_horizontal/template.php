<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

if (file_exists($_SERVER["DOCUMENT_ROOT"].$this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css'))
	$APPLICATION->SetAdditionalCSS($this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css');

$menuBlockId = "catalog_menu_".$this->randString();
?>
<div class="bx-top-nav bx-<?=$arParams["MENU_THEME"]?>" id="<?=$menuBlockId?>">
	<nav class="bx-top-nav-container" id="cont_<?=$menuBlockId?>">
		<ul class="bx-nav-list-1-lvl" id="ul_<?=$menuBlockId?>">
		<?foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns):?>     <!-- first level-->
			<?$existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] || $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;?>
			<li
				class="bx-nav-1-lvl bx-nav-list-<?=($existPictureDescColomn) ? count($arColumns)+1 : count($arColumns)?>-col <?if($arResult["ALL_ITEMS"][$itemID]["SELECTED"]):?>bx-active<?endif?><?if (is_array($arColumns) && count($arColumns) > 0):?> bx-nav-parent<?endif?>"
				onmouseover="BX.CatalogMenu.itemOver(this);"
				onmouseout="BX.CatalogMenu.itemOut(this)"
				<?if (is_array($arColumns) && count($arColumns) > 0):?>
					data-role="bx-menu-item"
				<?endif?>
				onclick="if (BX.hasClass(document.documentElement, 'bx-touch')) obj_<?=$menuBlockId?>.clickInMobile(this, event);"
			>
				<a
					href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>"
					<?if (is_array($arColumns) && count($arColumns) > 0 && $existPictureDescColomn):?>
						onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemID?>');"
					<?endif?>
				>
					<span>
						<?=$arResult["ALL_ITEMS"][$itemID]["TEXT"]?>
						<?if (is_array($arColumns) && count($arColumns) > 0):?><i class="fa fa-angle-down"></i><?endif?>
					</span>
				</a>
			<?if (is_array($arColumns) && count($arColumns) > 0):?>
				<span class="bx-nav-parent-arrow" onclick="obj_<?=$menuBlockId?>.toggleInMobile(this)"><i class="fa fa-angle-left"></i></span> <!-- for mobile -->
				<div class="bx-nav-2-lvl-container">
					<?foreach($arColumns as $key=>$arRow):?>
						<ul class="bx-nav-list-2-lvl">
						<?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?>  <!-- second level-->
							<li class="bx-nav-2-lvl">
								<a
									href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>"
									<?if ($existPictureDescColomn):?>
										onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemIdLevel_2?>');"
									<?endif?>
									data-picture="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["picture_src"]?>"
									<?if($arResult["ALL_ITEMS"][$itemIdLevel_2]["SELECTED"]):?>class="bx-active"<?endif?>
								>
									<span><?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?></span>
								</a>
							<?if (is_array($arLevel_3) && count($arLevel_3) > 0):?>
								<ul class="bx-nav-list-3-lvl">
								<?foreach($arLevel_3 as $itemIdLevel_3):?>	<!-- third level-->
									<li class="bx-nav-3-lvl">
										<a
											href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["LINK"]?>"
											<?if ($existPictureDescColomn):?>
												onmouseover="window.obj_<?=$menuBlockId?> && obj_<?=$menuBlockId?>.changeSectionPicure(this, '<?=$itemIdLevel_3?>');return false;"
											<?endif?>
											data-picture="<?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["PARAMS"]["picture_src"]?>"
											<?if($arResult["ALL_ITEMS"][$itemIdLevel_3]["SELECTED"]):?>class="bx-active"<?endif?>
										>
											<span><?=$arResult["ALL_ITEMS"][$itemIdLevel_3]["TEXT"]?></span>
										</a>
									</li>
								<?endforeach;?>
								</ul>
							<?endif?>
							</li>
						<?endforeach;?>
						</ul>
					<?endforeach;?>
					<?if ($existPictureDescColomn):?>
						<div class="bx-nav-list-2-lvl bx-nav-catinfo dbg" data-role="desc-img-block">
							<a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>">
								<img src="<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"]?>" alt="">
							</a>
							<p><?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]?></p>
						</div>
						<div class="bx-nav-catinfo-back"></div>
					<?endif?>
				</div>
			<?endif?>
			</li>
		<?endforeach;?>
		</ul>
		<div style="clear: both;"></div>
	</nav>
</div>

<script>
	BX.ready(function () {
		window.obj_<?=$menuBlockId?> = new BX.Main.MenuComponent.CatalogHorizontal('<?=CUtil::JSEscape($menuBlockId)?>', <?=CUtil::PhpToJSObject($arResult["ITEMS_IMG_DESC"])?>);
	});
</script>