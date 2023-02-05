<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".SITE_TEMPLATE_ID."/header.php");
CJSCore::Init(array("fx"));

\Bitrix\Main\UI\Extension::load("ui.bootstrap4");

if (isset($_GET["theme"]) && in_array($_GET["theme"], array("blue", "green", "yellow", "red")))
{
	COption::SetOptionString("main", "wizard_eshop_bootstrap_theme_id", $_GET["theme"], false, SITE_ID);
}
$theme = COption::GetOptionString("main", "wizard_eshop_bootstrap_theme_id", "green", SITE_ID);
$theme = "black";

$curPage = $APPLICATION->GetCurPage(true);

?><!DOCTYPE html>
<html xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
	<title><?$APPLICATION->ShowTitle()?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_DIR?>favicon.ico" />
	<? $APPLICATION->ShowHead(); ?>
</head>
<body class="bx-theme-<?=$theme?>">

<div id="panel" class="d-none d-sm-block"><? $APPLICATION->ShowPanel(); ?></div>

<div>
	<section class="wrapper">
		<header>
			<section class="header-container container-md">
				<!--region breadcrumb-->
				<?if ($curPage != SITE_DIR."index.php"):?>
					<div id="navigation">
						<?$APPLICATION->IncludeComponent(
							"bitrix:breadcrumb",
							"store_v3",
							array(
								"START_FROM" => "0",
								"PATH" => "",
								"SITE_ID" => "-"
							),
							false,
							Array('HIDE_ICONS' => 'Y')
						);?>
					</div>
				<?endif?>
				<!--endregion-->
				<div class="header-logotype">
					<a href="/" <?='class="'.($curPage === SITE_DIR."index.php" ? 'header-logotype-homepage' : 'header-logotype-page').'"';?>>
						<?$APPLICATION->IncludeComponent(
							"bitrix:main.include",
							"",
							array(
								"AREA_FILE_SHOW" => "file",
								"PATH" => SITE_DIR."include/company_logo.php"
							),
							false
						);?>
					</a>
					<!--region PAGETITLE -->
					<?if ($curPage != SITE_DIR."index.php"):?>
						<h1 id="pagetitle"><?$APPLICATION->ShowTitle(false);?></h1>
					<?endif?>
					<!--endregion-->
				</div>
				<div class="header-nav-btn">
					<div class="header-menu-container" id="mainMenu">
						<div class="header-menu-btn" onclick="BX.toggleClass(BX('mainMenu'), ['opened', 'closed'])">
							<svg width="23" height="19" viewBox="0 0 23 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect rx="1" fill="#121212" width="23" height="3"/>
								<rect rx="1" fill="#121212" width="23" height="3" y="8"/>
								<rect rx="1" fill="#121212" width="23" height="3" y="16"/>
							</svg>
						</div>
						<div class="header-menu-overlay" onclick="BX.removeClass(BX('mainMenu'), 'opened')"></div>
						<div class="header-menu-items-container">
							<div class="header-menu-items-scroll-block">
								<?$APPLICATION->IncludeComponent(
									"bitrix:menu",
									"store_v3_main",
									Array(
										"ROOT_MENU_TYPE" => "top",
										"MENU_CACHE_TYPE" => "A",
										"MENU_CACHE_TIME" => "36000000",
										"MENU_CACHE_USE_GROUPS" => "Y",
										"CACHE_SELECTED_ITEMS" => "N",
										"MENU_CACHE_GET_VARS" => "",
										"MAX_LEVEL" => "3",
										"CHILD_MENU_TYPE" => "top",
										"USE_EXT" => "Y",
										"DELAY" => "N",
										"ALLOW_MULTI_SELECT" => "N",
										"COMPONENT_TEMPLATE" => "store_v3_main"
									),
									false
								);?>
							</div>
							<div class="header-menu-close-btn" onclick="BX.removeClass(BX('mainMenu'), 'opened')">
								<svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M12.9165 1.60282L11.3137 0L6.25966 5.05407L1.60282 0.39723L0 2.00005L4.65684 6.65689L2.11e-05 11.3137L1.60284 12.9165L6.25966 8.2597L11.3137 13.3138L12.9165 11.7109L7.86247 6.65689L12.9165 1.60282Z" fill="#fff"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</section>
		</header>
		<section class="header-cover mb-sm-4 d-none d-md-flex align-items-center">
			<div class="container-md container-fluid">
				<div class="row">
					<div class="col">
						<h2><?php echo GetMessage('HEADER_MESS_SHOP'); ?></h2>
						<small><?php echo GetMessage('HEADER_MESS_SHOP_SLOGAN'); ?></small>
					</div>
				</div>
			</div>
		</section>
		<div class="container-md container-fluid workarea">
			<div class="row">
				<?if ($curPage !== "/personal/order/make/index.php"): ?>
				<div class="d-none d-md-block col relative col-md-4 col-lg-3">
					<div class="sticky-top">
						<?$APPLICATION->IncludeComponent(
							"bitrix:menu",
							"store_v3_main",
							Array(
								"ROOT_MENU_TYPE" => "top",
								"MENU_CACHE_TYPE" => "A",
								"MENU_CACHE_TIME" => "36000000",
								"MENU_CACHE_USE_GROUPS" => "Y",
								"CACHE_SELECTED_ITEMS" => "N",
								"MENU_CACHE_GET_VARS" => "",
								"MAX_LEVEL" => "3",
								"CHILD_MENU_TYPE" => "top",
								"USE_EXT" => "Y",
								"DELAY" => "N",
								"ALLOW_MULTI_SELECT" => "N",
								"COMPONENT_TEMPLATE" => "store_v3_main"
							),
							false
						);?>
					</div>
				</div>
				<?endif;?>
				<?if ($curPage !== "/personal/order/make/index.php"):?>
				<div class="col <?if ($curPage !== "/personal/order/make/index.php"): ?> col-md-8 col-lg-9<?endif;?>">
				<?endif?>