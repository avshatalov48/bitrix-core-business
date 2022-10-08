<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeTemplateLangFile(__FILE__);
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?$APPLICATION->ShowHead();?>
<?if (!isset($_GET["print_course"])):?>
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH."/print_style.css"?>" type="text/css" media="print" />
<?else:?>
	<meta name="robots" content="noindex, follow" />
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH."/print_style.css"?>" type="text/css" />
<?endif?>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH."/js/imgshw.js"?>"></script>
<title><?$APPLICATION->ShowTitle()?></title>
</head>

<body>

<table id="outer" cellspacing="0" cellpadding="0">
	<tr>
		<td id="header-row">
			<div id="panel"><?$APPLICATION->ShowPanel();?></div>
			<table id="header">
				<tr>
					<td id="logo"><?=GetMessage("LEARNING_LOGO_TEXT")?></td>
					<td id="logotext"><?$APPLICATION->ShowProperty("learning_course_name")?>&nbsp;</td>
				</tr>
			</table>

			<table id="toolbar">
				<tr>
					<td id="toolbar_icons">
						<a href="<?$APPLICATION->ShowProperty("learning_test_list_url")?>"><img src="<?=SITE_TEMPLATE_PATH."/icons/tests.gif"?>" width="25" height="25" border="0" title="<?=GetMessage("LEARNING_PASS_TEST")?>"></a><img src="<?=SITE_TEMPLATE_PATH."/icons/line.gif"?>" width="11" height="25" border="0"><a href="<?$APPLICATION->ShowProperty("learning_gradebook_url")?>" title="<?=GetMessage("LEARNING_GRADEBOOK")?>"><img src="<?=SITE_TEMPLATE_PATH."/icons/gradebook.gif"?>" width="25" height="25" border="0"></a><img src="<?=SITE_TEMPLATE_PATH."/icons/line.gif"?>" width="11" height="25" border="0"><a href="<?$APPLICATION->ShowProperty("learning_course_contents_url")?>" title="<?=GetMessage("LEARNING_ALL_COURSE_CONTENTS")?>"><img src="<?=SITE_TEMPLATE_PATH."/icons/materials.gif"?>" width="25" height="25" border="0"></a><img src="<?=SITE_TEMPLATE_PATH."/icons/line.gif"?>" width="11" height="25" border="0"><a href="<?=htmlspecialcharsbx($APPLICATION->GetCurPageParam("print_course=Y", array("print_course")), false)?>" rel="nofollow" title="<?=GetMessage("LEARNING_PRINT_PAGE")?>"><img src="<?=SITE_TEMPLATE_PATH."/icons/printer_b_b.gif"?>" width="25" height="25" border="0"></a>
					</td>
					<td id="toolbar_title">
						<div id="container">
							<div id="title"><?$APPLICATION->ShowTitle()?></div>
							<div id="complete">
								<span title="<?=GetMessage("LEARNING_CURRENT_LESSON")?>"><?$APPLICATION->ShowProperty("learning_lesson_current")?></span>&nbsp;/&nbsp;<span title="<?=GetMessage("LEARNING_ALL_LESSONS")?>"><?$APPLICATION->ShowProperty("learning_lesson_count")?></span>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td id="workarea-row">