<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
if (isset($templateData['JS_OBJ']))
{
	if (!isset($_SESSION["IBLOCK_RATING"][$templateData['ELEMENT_ID']]) && 'Y' != $arParams['READ_ONLY'])
	{
?>
<script type="text/javascript">
BX.ready(
	BX.defer(function(){
		if (!!window.<? echo $templateData['JS_OBJ']; ?>)
		{
			window.<? echo $templateData['JS_OBJ']; ?>.bindEvents();
		}
	})
);
</script>
<?
	}
}
?>