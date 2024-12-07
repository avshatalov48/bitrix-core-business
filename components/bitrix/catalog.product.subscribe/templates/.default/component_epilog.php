<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
if (!$templateData['showSubscribe'])
	return;

if($templateData['jsObject']): ?>
<script>
	BX.ready(BX.defer(function(){
		if (!!window.<?= $templateData['jsObject']; ?>)
		{
			window.<?= $templateData['jsObject']; ?>.setIdAlreadySubscribed(<?=Bitrix\Main\Web\Json::encode($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID'] ?? [])?>);
		}
	}));
</script>
<? endif;