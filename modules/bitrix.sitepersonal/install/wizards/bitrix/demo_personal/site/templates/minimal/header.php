<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
		<div id="panel"><?$APPLICATION->ShowPanel();?></div>

	
	<table id="grid" align="center" cellspacing="0">
		<tr>
			<td id="header-row" colspan="2">
				<div id="header">
					<h1 id="title"><?$APPLICATION->IncludeFile(
									SITE_TEMPLATE_PATH."/include_areas/site_name.php",
									Array(),
									Array("MODE"=>"html")
								);?></h1>
					<div id="search">
					<?$APPLICATION->IncludeComponent("bitrix:search.form", "personal", Array(
											"PAGE"	=>	SITE_DIR."search.php"
											)
									);?>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td id="menu-row" colspan="2">
				<div id="top-menu">
					<?$APPLICATION->IncludeComponent(
						"bitrix:menu", 
						"personal_tab", 
						Array(
							"ROOT_MENU_TYPE"	=>	"top",
							"MAX_LEVEL"	=>	"1",
							"USE_EXT"	=>	"N"
						)
					);?>
					<b class="r1"></b>
						<?if($APPLICATION->GetCurPage(true) == SITE_DIR."index.php"):?>
							<a href="<?=SITE_DIR?>rss/" id="rss-link"><?=GetMessage("TMPL_RSS")?></a>
						<?endif?>
					<b class="r2"></b>
				</div>
			</td>
		</tr>
		<tr>
			<td id="content">
				<div id="content-wrapper">
					<?if($APPLICATION->GetCurPage(true) != SITE_DIR."index.php")
					{
						echo "<h1>";
						$APPLICATION->ShowTitle(false);
						echo "</h1>";
					}
					?>	