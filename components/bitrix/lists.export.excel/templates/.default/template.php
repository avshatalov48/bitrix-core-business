<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<html>
	<head>
	<title><? $APPLICATION->GetTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset='<?= LANG_CHARSET ?>">
	<style>
		td {mso-number-format:\@;}
		.number0 {mso-number-format:0;}
		.number2 {mso-number-format:Fixed;}
	</style>
	</head>
<body>
	<table border="1">
	<tr>
		<? foreach ($arResult["EXCEL_COLUMN_NAME"] as $value)
		{
		?>
		<td><?= $value; ?></td>
		<?
		}
		?>
	</tr>

	<? foreach ($arResult["EXCEL_CELL_VALUE"] as $array)
	{
	?>
	<tr>
		<? foreach ($array as $value)
		{
		?>
			<td><?= $value; ?></td>
		<?
		}
		?>
	</tr>
	<?
	}
	?>

	</table>
</body>
</html>

