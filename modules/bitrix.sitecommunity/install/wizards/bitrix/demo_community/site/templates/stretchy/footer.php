						<?
						if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
						IncludeTemplateLangFile(__FILE__);
						?>
					</div>
				</div>
				<?if ($GLOBALS["bRightColumnVisible"]) { ?>
					<div id="sidebar" class="right-column"> 
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"sidebar",
							Array(
								"AREA_FILE_SHOW" => "page", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "N", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "page_inc.php" 
								)
						);?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"sidebar",
							Array(
								"AREA_FILE_SHOW" => "sect", 
								"AREA_FILE_SUFFIX" => "inc", 
								"AREA_FILE_RECURSIVE" => "Y", 
								"EDIT_MODE" => "html", 
								"EDIT_TEMPLATE" => "sect_inc.php" 
							)
						);?>
						<?$APPLICATION->ShowViewContent("sidebar")?>
					 </div>
				 <?} ?>
			</div>
			<div id="space-for-footer"></div>
		</div>
		<div id="footer">
			<div id="copyright">
				<?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath(SITE_DIR."include/copyright.php"),
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
			<div class="footer-links">	
			<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom", Array(
				"ROOT_MENU_TYPE"	=>	"bottom",
				"MAX_LEVEL"	=>	"1",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "N",
				"MENU_CACHE_GET_VARS" => Array()
				)
			);?>
			</div>	
		</div>
</body>
</html>