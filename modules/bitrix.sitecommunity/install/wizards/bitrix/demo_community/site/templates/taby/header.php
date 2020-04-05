<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (strpos($GLOBALS["APPLICATION"]->GetCurPage(true), SITE_DIR."people/index.php") === 0 || strpos($GLOBALS["APPLICATION"]->GetCurPage(true), SITE_DIR."groups/index.php") === 0)
	$GLOBALS["bRightColumnVisible"] = true;
else
	$GLOBALS["bRightColumnVisible"] = ($GLOBALS["APPLICATION"]->GetProperty("hide_sidebar") == "Y" ? false : true);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head id="Head">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<?$APPLICATION->ShowHead()?>
	<title><?$APPLICATION->ShowTitle()?></title>
	<link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/blog.css" />	
	<link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/common.css" />
	<link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/colors.css" />
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" /> 
</head>	
<body>
<?if (IsModuleInstalled("im")) $APPLICATION->IncludeComponent("bitrix:im.messenger", "", Array(), null, array("HIDE_ICONS" => "Y")); ?>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
	<div id="page-wrapper">
		<div id="header">
			<div class="main-column">
				<table cellpadding="0" id="logo">
					<tr>
						<td id="logo-image">
							<a href="<?= SITE_DIR ?>"><?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath(SITE_DIR."include/company_logo.php"),
					Array(),
					Array("MODE"=>"html")
				);?></a>
						</td>
						<td id="logo-text">
							<a href="<?= SITE_DIR ?>">
								<span class="h1"><?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath(SITE_DIR."include/company_name.php"),
					Array(),
					Array("MODE"=>"html")
				);?></span>
								<span><?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath(SITE_DIR."include/company_description.php"),
					Array(),
					Array("MODE"=>"html")
				);?></span>
							</a>
						</td>
					</tr>
				</table>
				
				<?$APPLICATION->IncludeComponent(
					"bitrix:menu", 
					"main", 
					Array(
						"ROOT_MENU_TYPE"	=>	"top",
						"MAX_LEVEL"	=>	"1",
						"USE_EXT"	=>	"N",
						"MENU_CACHE_TYPE" => "A",
						"MENU_CACHE_TIME" => "36000000",
						"MENU_CACHE_USE_GROUPS" => "N",
						"MENU_CACHE_GET_VARS" => Array()
					)
				);?>				
				
				<div id="top-menu-border"></div>
			</div>
		
			<div class="right-column">
				<div class="rounded-block auth-block">
					<div class="block-content">
						<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "auth", array(
							"REGISTER_URL" => SITE_DIR."auth/",
							"PROFILE_URL" => SITE_DIR."people/user/#user_id#/",
							"SHOW_ERRORS" => "N",
							"PATH_TO_BLOG" => SITE_DIR."people/user/#user_id#/blog/",
							"PATH_TO_BLOG_NEW_POST" => SITE_DIR."people/user/#user_id#/blog/edit/new/",
							"PATH_TO_NEW_BLOG" => SITE_DIR."people/user/#user_id#/blog/",
							"PATH_TO_SONET_MESSAGES" => SITE_DIR."people/messages/",
							"PATH_TO_SONET_LOG" => SITE_DIR."people/log/"
							),
							false
						);?>
					</div>
					<div class="corner left-bottom"></div>
					<div class="corner right-bottom"></div>
				</div>
			</div>
		</div>

		<div id="content">
			<div id="workarea<?= ($GLOBALS["bRightColumnVisible"] ? "" : "-single") ?>" class="main-column"> 
			
				<?$APPLICATION->IncludeComponent("bitrix:menu", "submenu", Array(
					"ROOT_MENU_TYPE"	=>	"left",
					"MAX_LEVEL"	=>	"1",
					"CHILD_MENU_TYPE"	=>	"left",
					"USE_EXT"	=>	"Y",
					"MENU_CACHE_TYPE" => "A",
					"MENU_CACHE_TIME" => "36000000",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => array(
						0 => "SECTION_ID",
						1 => "page",
					),
					)
				);?>					
				
				
				<div id="workarea-content">
					<?if($APPLICATION->GetCurDir() != SITE_DIR):?>
					<div id="page-title">
						<?if (!$GLOBALS["bRightColumnVisible"]){ ?>
						<div id="search">
							<?$APPLICATION->IncludeComponent("bitrix:search.form", "main", Array(
								"PAGE"	=>	SITE_DIR."search/"
								)
							);?>
						</div>
						<?}?>
						<h1><?$APPLICATION->ShowTitle(false)?></h1>
					</div>
					<?endif;?>