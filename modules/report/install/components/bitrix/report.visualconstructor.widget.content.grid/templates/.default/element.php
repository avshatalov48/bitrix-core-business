<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$reportHandlerResult = $arResult;
$prefix = !empty($reportHandlerResult['prefix']) ? $reportHandlerResult['prefix'] : '';
$postfix = !empty($reportHandlerResult['postfix']) ? $reportHandlerResult['postfix'] : '';
$targetUrl = !empty($reportHandlerResult['targetUrl']) ? $reportHandlerResult['targetUrl'] : '';
$sliderMode = isset($reportHandlerResult['slider']) ? $reportHandlerResult['slider'] : false;
$elementId = 'id_' . randString(25);

$clickableElementClass = '';
if (!empty($reportHandlerResult['slider']))
{
	$prefix .= '';
}

if ($targetUrl)
{
	$clickableElementClass = 'report-widget-grid-value-clickable';
}
?>
<div class="report-widget-grid-value <?=$clickableElementClass?>" id="<?=$elementId?>" ><?=$prefix?><?=$reportHandlerResult['value']?><?=$postfix?></div>


<script>
	<?if($sliderMode && $targetUrl):?>
	//TODO: REFACTORE THIS TO ELEMENT CLASS WITH NORMAL NODES
	var elementNode = document.querySelector('#<?=$elementId?>');
	if (elementNode)
	{
		elementNode.onclick = function(e){
			e.preventDefault();
			BX.SidePanel.Instance.open(<?=CUtil::PhpToJSObject($targetUrl)?>, {
				cacheable: false
			});
		};
	}

	<?endif;?>
</script>