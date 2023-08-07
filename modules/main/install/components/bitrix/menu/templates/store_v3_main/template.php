<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CBitrixMenuComponent $component
 * @var array $arParams
 * @var array $arResult
 */

if (!empty($arResult))
{
	?>
	<div class="menu-main-container" id="mainMenu">
		<div class="menu-main-items-container">
			<div class="menu-main-items-scroll-block">
				<?$APPLICATION->IncludeComponent(
					"bitrix:catalog.section.list",
					"store_v3_menu",
					Array(
						"ADD_SECTIONS_CHAIN" => "Y",
						"CACHE_FILTER" => "N",
						"CACHE_GROUPS" => "N",
						"CACHE_TIME" => "36000000",
						"CACHE_TYPE" => "A",
						"COUNT_ELEMENTS" => "N",
						"COUNT_ELEMENTS_FILTER" => "CNT_AVAILABLE",
						"FILTER_NAME" => "sectionsFilter",
						"HIDE_SECTION_NAME" => "N",
						"IBLOCK_ID" => "5",
						"IBLOCK_TYPE" => "catalog",
						"SECTION_CODE" => "",
						"SECTION_FIELDS" => array("",""),
						"SECTION_ID" => $_REQUEST["SECTION_ID"],
						"SECTION_URL" => "",
						"SECTION_USER_FIELDS" => array("",""),
						"SHOW_PARENT_NAME" => "Y",
						"SHOW_TITLE" => "Y",
						"TOP_DEPTH" => "2",
						"VIEW_MODE" => "TILE"
					)
				);?>
				<ul class="menu-main-items">
					<?php
					foreach ($arResult as $item)
					{
						if ($arParams['MAX_LEVEL'] === 1 && $item['DEPTH_LEVEL'] > 1)
						{
							continue;
						}

						?>
						<li class="menu-main-item<?=($item['SELECTED'] ? ' selected' : '')?>">
							<a href="<?=$item['LINK']?>" class="menu-main-item-link">
								<span class="menu-main-item-text"><?=$item['TEXT']?></span>
								<span class="menu-main-item-angle"></span>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<?php
}