<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//Lang phrases
__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php");

//Hide menu
$_MenuClass = $GLOBALS["APPLICATION"]->GetPageProperty("BodyClass"); //chk
$_MenuClass = ($_MenuClass?' ':'')."no-left-menu page-section-menu"; //chk
$APPLICATION->SetPageProperty("BodyClass", $_MenuClass); //chk

$arResult["VARIABLES"]["blog"] = $arParams["BLOG_URL"];
//Page navigation template
$arParams["NAV_TEMPLATE"] = trim($arParams["NAV_TEMPLATE"]);
$arParams["NAV_TEMPLATE"] = (empty($arParams["NAV_TEMPLATE"]) ? "arrows" : $arParams["NAV_TEMPLATE"]);

//Search

if($this->__page == 'index')
{
	ob_start();
	$APPLICATION->IncludeComponent(
		"bitrix:idea.search",
		"",
		Array(),
		$component
	);
	$arResult["ACTIONS"]["SEARCH"] = array("HTML" => ob_get_contents());
	ob_end_clean();
}

//Can Add Idea
if($USER->IsAuthorized())
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
	if(CBlog::GetBlogUserPostPerms($arBlog["ID"], $USER->GetID()) >= BLOG_PERMS_PREMODERATE)
		$arResult["ACTIONS"]["ADD_IDEA"] = array(
			"ICON" => "btn-new section-add",
			"TEXT" => GetMessage("IDEA_ADD_IDEA_TITLE"),
			"LINK" => $arResult["~PATH_TO_POST_ADD"],
		);
}

//Can Add category
if($USER->IsAuthorized() && $arParams["IBLOCK_CATEGORIES"]>0 && CIBlock::GetPermission($arParams["IBLOCK_CATEGORIES"], $USER->GetID())>="W")
{
	$arButtons = CIBlock::GetPanelButtons(
			$arParams["IBLOCK_CATEGORIES"],
			0,
			0,
			array(
				"SESSID"=>false
			)
	);

	$arResult["ACTIONS"]["ADD_IDEA_CATEGORY"] = array(
		"ICON" => "btn-new section-add",
		"TEXT" => GetMessage("IDEA_ADD_IDEA_CATEGORY_TITLE"),
		"LINK" => $arButtons["edit"]["add_section"]["ACTION"] //ACTION_URL"
	);
}

//Menu
if($USER->IsAuthorized())
{   
	$arResult["ACTIONS"]["MENU"] = array(
		"TEXT" => GetMessage("IDEA_MENU_TITLE"),
		"MENU" => array(),
	);

	//Own ideas
	$arResult["ACTIONS"]["MENU"]["MENU"][] = array(
		"TEXT" => GetMessage("IDEA_MY_IDEA_TITLE"),
		"ONCLICK" => "top.window.location.href='".$arResult["~PATH_TO_USER_IDEAS"]."';",
	);
	$arResult["ACTIONS"]["MENU"]["MENU"][] = array(
		"SEPARATOR" => true,
	);
	//Own subscribes
	$arResult["ACTIONS"]["MENU"]["MENU"][] = array(
		"TEXT" => GetMessage("IDEA_MY_SUBSCRIBE_TITLE"),
		"ONCLICK" => "top.window.location.href='".$arResult["~PATH_TO_USER_SUBSCRIBE"]."';",
	);
}

//Top part of sidebar Wrapper
$this->SetViewTarget("sidebar", 1);
	echo '<div class="sidebar-block idea-detail-info">
	<b class="r2"></b>
	<b class="r1"></b>
	<b class="r0"></b>
	<div class="sidebar-block-inner">';
$this->EndViewTarget();
//Bottom part of sidebar Wrapper
$this->SetViewTarget("sidebar", 100000);
	echo '</div>
	<i class="r0"></i>
	<i class="r1"></i>
	<i class="r2"></i>
	</div>';
$this->EndViewTarget();
?>