<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
							</div>
						</div>
					</div>
					
				</div>
				
				<div id="sidebar">
					<div id="sidebar-inner">
					
						<div id="telephone"><nobr><?$APPLICATION->IncludeFile(
									SITE_DIR."include/phone.php",
									Array(),
									Array("MODE"=>"html")
								);?></nobr></div>
						
						<div id="schedule"><div class="schedule">
						<?$APPLICATION->IncludeFile(
									SITE_DIR."include/shedule.php",
									Array(),
									Array("MODE"=>"html")
								);?>
						</div></div>
						
						<div id="support">
							<div id="support-question"></div>
							<b class="r0"></b>
							<div id="support-text"><a href="<?=SITE_DIR?>contacts/feedback.php"><?=GetMessage("HDR_ASK")?></a></div>	
							<b class="r0"></b>
						</div>

					<?$APPLICATION->IncludeComponent(
						"bitrix:main.include",
						".default",
						Array(
							"AREA_FILE_SHOW" => "page", 
							"AREA_FILE_SUFFIX" => "inc", 
							"AREA_FILE_RECURSIVE" => "N", 
							"EDIT_MODE" => "html", 
							"EDIT_TEMPLATE" => "page_inc.php" 
							)
					);?><?$APPLICATION->IncludeComponent(
						"bitrix:main.include",
						".default",
						Array(
							"AREA_FILE_SHOW" => "sect", 
							"AREA_FILE_SUFFIX" => "inc", 
							"AREA_FILE_RECURSIVE" => "Y", 
							"EDIT_MODE" => "html", 
							"EDIT_TEMPLATE" => "sect_inc.php" 
						)
					);?>
					</div>
				</div>
			</div>
	
			<div id="space-for-footer"></div>
			
		</div>
		
		<div id="footer">
		
			<div id="copyright">
			<?$APPLICATION->IncludeFile(
									SITE_DIR."include/copyright.php",
									Array(),
									Array("MODE"=>"html")
								);?>
			</div>
			<div id="bottom-menu">			
			<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom", array(
				"ROOT_MENU_TYPE" => "bottom",
				"MENU_CACHE_TYPE" => "Y",
				"MENU_CACHE_TIME" => "36000000",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "bottom",
				"USE_EXT" => "N",
				"ALLOW_MULTI_SELECT" => "N"
				),
				false
			);?>
			</div>
		</div>	
</body>
</html>