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
	<div class="menu-swipe-container" id="mainMenu">
		<div class="menu-swipe-btn" onclick="BX.toggleClass(BX('mainMenu'), ['opened', 'closed'])">
			<svg width="23" height="19" viewBox="0 0 23 19" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect rx="1" fill="#121212" width="23" height="3"/>
				<rect rx="1" fill="#121212" width="23" height="3" y="8"/>
				<rect rx="1" fill="#121212" width="23" height="3" y="16"/>
			</svg>
		</div>
		<div class="menu-swipe-overlay" onclick="BX.removeClass(BX('mainMenu'), 'opened')"></div>
		<div class="menu-swipe-items-container">
			<div class="menu-swipe-items-scroll-block">
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
				<ul class="menu-swipe-items">
					<?php
					foreach ($arResult as $item)
					{
						if ($arParams['MAX_LEVEL'] === 1 && $item['DEPTH_LEVEL'] > 1)
						{
							continue;
						}

						?>
						<li class="menu-swipe-item<?=($item['SELECTED'] ? ' selected' : '')?>">
							<a href="<?=$item['LINK']?>" class="menu-swipe-item-link">
								<span class="menu-swipe-item-text"><?=$item['TEXT']?></span>
								<span class="menu-swipe-item-angle"></span>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
			<div class="menu-swipe-close-btn" onclick="BX.removeClass(BX('mainMenu'), 'opened')">
				<svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M12.9165 1.60282L11.3137 0L6.25966 5.05407L1.60282 0.39723L0 2.00005L4.65684 6.65689L2.11e-05 11.3137L1.60284 12.9165L6.25966 8.2597L11.3137 13.3138L12.9165 11.7109L7.86247 6.65689L12.9165 1.60282Z" fill="#fff"/>
				</svg>
			</div>

		</div>
	</div>
	<?php
}