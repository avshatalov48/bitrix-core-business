<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>


<div class="order_component" id="<?=$arResult['JS_CONTAINER_ID']?>">

	<?
	$strHtml = '';
	if(!is_array($arResult["DATA"]) || empty($arResult["DATA"]))
	{
		$strHtml .= $arResult['INSCRIPTION_FOR_EMPTY'];
	}
	else
	{
		foreach ($arResult["DATA"] as $arSection)
			$strHtml .= getListSectionHtml($arSection);

	}

	echo $strHtml;

?>
</div>
<?if(isset($arParams["TITLE"])):?>
	<script type="text/javascript">
		app.setPageTitle({title: "<?=$arParams["TITLE"]?>"});
	</script>
<?endif;

function getListSectionHtml($arSection, $bBackGround = false, $level = 0)
{
	if(!is_array($arSection) && empty($arSection))
		return '';

	$strHtml  = '';

	$strHtml .= '<div class="order_infoblock';

	if(isset($arSection["CLOSED"]) && $arSection["CLOSED"])
		$strHtml .= ' close';

	if(isset($arSection["HIGHLIGHTED"]) && $arSection["HIGHLIGHTED"])
		$strHtml .= ' highlighted';

	if($bBackGround)
		$strHtml .= ' filled';

	$strHtml .= '"';

	$strHtml .= '>'.PHP_EOL.
	'<div class="order_infoblock_title'.
	'" onclick="BX.toggleClass(this.parentNode,\'close\');">';

	if(isset($arSection["TITLE"]))
		$strHtml .= $arSection["TITLE"];

	$strHtml .= '<span></span></div>'.PHP_EOL.
	'<div class="order_infoblock_content';

	if($level > 0)
		$strHtml .= ' inner';

	$strHtml .= '">'.PHP_EOL;

	if(isset($arSection["CONTENT"]) && !empty($arSection["CONTENT"]))
	{
		$strHtml .= getListContentHtml($arSection["CONTENT"]);
	}

	if(isset($arSection["SECTIONS"]))
	{
		foreach ($arSection["SECTIONS"] as $arSubsection)
		{
			$strHtml .= getListSectionHtml($arSubsection, !$bBackGround, $level+1);
		}
	}

	if(!isset($arSection["SECTIONS"]) && !isset($arSection["CONTENT"]))
	{
		$strHtml .= $arResult['INSCRIPTION_FOR_EMPTY'];
	}

	$strHtml .= '</div>'.PHP_EOL;
	$strHtml .= '</div>'.PHP_EOL;

	return $strHtml;
}

function getListContentHtml($content)
{

	if(!is_array($content))
		return $content;

	$strHtml = '<table class="order_infoblock_content_table">'.PHP_EOL;

	foreach ($content as $arRow)
	{
		$strHtml .= '<tr>'.PHP_EOL.
		'<td class="order_infoblock_content_table_tdtitle">';

		$strHtml .= (isset($arRow["TITLE"]) ? $arRow["TITLE"] : '&nbsp;');

		$strHtml .='</td>'.PHP_EOL.
		'<td class="order_infoblock_content_table_tdvalue">';

		$strHtml .= (isset($arRow["VALUE"]) ? $arRow["VALUE"] : '&nbsp;');

		$strHtml .= '</td>'.PHP_EOL.
		'</tr>'.PHP_EOL;
	}

	$strHtml .= '</table>'.PHP_EOL;

	return $strHtml;
}


