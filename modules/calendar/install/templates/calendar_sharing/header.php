<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
	<?$APPLICATION->ShowHead();
	/*
	This is commented to avoid Project Quality Control warning
	$APPLICATION->ShowPanel();
	*/
	?>
	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?=$APPLICATION->ShowProperty("BodyClass");?>">