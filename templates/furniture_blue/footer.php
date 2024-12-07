<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
			</div>
		</div>
		<div id="space-for-footer"></div>
	</div>
	
	<div id="footer">
	
		<div id="copyright">
<?
$APPLICATION->IncludeFile(
	SITE_DIR."include/copyright.php",
	Array(),
	Array("MODE"=>"html")
);
?>
		</div>
		<div class="footer-links">	
<?
$APPLICATION->IncludeComponent("bitrix:menu", "bottom", array(
	"ROOT_MENU_TYPE" => "bottom",
	"MENU_CACHE_TYPE" => "N",
	"MENU_CACHE_TIME" => "36000000",
	"MENU_CACHE_USE_GROUPS" => "Y",
	"MENU_CACHE_GET_VARS" => array(
	),
	"MAX_LEVEL" => "1",
	"CHILD_MENU_TYPE" => "left",
	"USE_EXT" => "N",
	"ALLOW_MULTI_SELECT" => "N"
	),
	false
);
?>
		</div>
		<div id="footer-design"><?=GetMessage("FOOTER_DISIGN")?></div>
	</div>
</body>
</html>