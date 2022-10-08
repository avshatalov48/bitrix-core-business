<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var string $componentPath
 * @var string $templateName
 */

$cartId = 'bx_basket'.$this->randString();
$arParams['cartId'] = $cartId;

?>
<div id="<?=$cartId?>">
	<?php
	/** @var \Bitrix\Main\Page\FrameBuffered $frame */
	$frame = $this->createFrame($cartId, false)->begin();
	require(realpath(__DIR__).'/ajax_template.php');
	$frame->beginStub();
	$arResult['COMPOSITE_STUB'] = 'Y';
	require(realpath(__DIR__).'/top_template.php');
	unset($arResult['COMPOSITE_STUB']);
	$frame->end();
	?>
</div>
<script>
	var <?=$cartId?> = new BitrixSmallCart;
	<?=$cartId?>.siteId = '<?=SITE_ID?>';
	<?=$cartId?>.cartId = '<?=$cartId?>';
	<?=$cartId?>.ajaxPath = '<?=$componentPath?>/ajax.php';
	<?=$cartId?>.templateName = '<?=$templateName?>';
	<?=$cartId?>.arParams = <?=CUtil::PhpToJSObject($arParams)?>;
	<?=$cartId?>.closeMessage = '<?=Loc::getMessage('TSB1_COLLAPSE')?>';
	<?=$cartId?>.openMessage = '<?=Loc::getMessage('TSB1_EXPAND')?>';
	<?=$cartId?>.activate();
</script>