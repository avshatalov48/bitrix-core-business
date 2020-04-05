<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
if (!$templateData['showSubscribe'])
	return;

$templateData['alreadySubscribed'] = false;
if(!empty($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']))
{
	if(array_key_exists($templateData['productId'], $_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']))
		$templateData['alreadySubscribed'] = true;
}

if($templateData['jsObject']): ?>
<script type="text/javascript">
	BX.ready(BX.defer(function(){
		if (!!window.<?= $templateData['jsObject']; ?>)
		{
			window.<?= $templateData['jsObject']; ?>.setButton(<?=($templateData['alreadySubscribed'] ? 'true' : 'false'); ?>);
		}
	}));
</script>
<? endif;