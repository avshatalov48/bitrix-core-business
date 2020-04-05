<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<?$APPLICATION->ShowMeta("robots")?>
<?$APPLICATION->ShowMeta("keywords")?>
<?$APPLICATION->ShowMeta("description")?>
<title><?$APPLICATION->ShowTitle()?></title>
<?$APPLICATION->ShowHead();?>
<?IncludeTemplateLangFile(__FILE__);?>
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/colors.css" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/print.css" media="print" />
</head>
<body>
	<div id="leaves-left"></div>
	<div id="leaves-right"></div>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
	
		<div id="header-container">
			<div id="header">
				<h1 id="site-name"><?$APPLICATION->IncludeFile(
					SITE_TEMPLATE_PATH."/include_areas/site_name.php",
					Array(),
					Array("MODE"=>"html")
				);?></h1>
			</div>
		</div>

		<div id="sub-header">
			<?$APPLICATION->IncludeComponent(
				"bitrix:menu", 
				"personal_tab", 
				Array(
					"ROOT_MENU_TYPE"	=>	"top",
					"MAX_LEVEL"	=>	"1",
					"USE_EXT"	=>	"N"
				)
			);?>
			<?$APPLICATION->IncludeComponent("bitrix:search.form", "personal", Array(
						"PAGE"	=>	SITE_DIR."search.php"
						)
				);?>

		</div>
		

		<div id="top-corners">
			<div class="workarea-corners"><b class="r1"></b><b class="r0"></b></div>
			<div class="sidebar-corners"><b class="r1"></b><b class="r0"></b></div>
		</div>
			
		<div id="content-wrapper">
			<?if($APPLICATION->GetCurPage(true) == SITE_DIR."index.php"):?>
			<a href="<?=SITE_DIR?>rss/" id="rss-link"><?=GetMessage("TMPL_RSS")?></a>
			<?endif?>
			<div id="content">
				<div id="workarea">
					<div id="workarea-inner">
						<?if($APPLICATION->GetCurPage(true) != SITE_DIR."index.php")
						{
							echo "<h1>";
							$APPLICATION->ShowTitle(false);
							echo "</h1>";
						}
						?>	