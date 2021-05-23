<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="order_acceptpay_infoblock">

	<?if($arResult["TITLE"]):?>
		<div class="order_acceptpay_infoblock_title"><?=$arResult["TITLE"]?></div>
	<?endif;?>

	<?require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/nowrap.php')?>

</div>