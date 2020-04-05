<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("fileman"))
	return;
/**
 * @var array $arResult
 * @var array $arParams
 */
$controlsInGroup = array();
$controlsLHE = array(
	"Bold" => array("group" => "bui", 'id' => 'Bold',  'compact' => true, 'sort' => 80),
	"Italic" => array("group" => "bui", 'id' => 'Italic',  'compact' => true, 'sort' => 90),
	"Underline" => array("group" => "bui", 'id' => 'Underline',  'compact' => true, 'sort' => 100),
	"Strike" => array("group" => "bui", 'id' => 'Strikeout',  'compact' => true, 'sort' => 110),
	"Strikeout" => array("group" => "bui", 'id' => 'Strikeout',  'compact' => true, 'sort' => 110),
	"RemoveFormat" => array("group" => "bui", 'id' => 'RemoveFormat',  'compact' => true, 'sort' => 120),
	"Color" => array("group" => "bui", 'id' => 'Color',  'compact' => true, 'sort' => 130),
	"ForeColor" => array("group" => "bui", 'id' => 'Color',  'compact' => true, 'sort' => 130),
	"FontList" => array("group" => "bui", 'id' => 'FontSelector',  'compact' => false, 'sort' => 135),
	"FontSelector" => array("group" => "bui", 'id' => 'FontSelector',  'compact' => false, 'sort' => 135),
	"FontSizeList" => array("group" => "bui", 'id' => 'FontSize',  'compact' => false, 'sort' => 140),
	"FontSize" => array("group" => "bui", 'id' => 'FontSize',  'compact' => false, 'sort' => 140),
	"InsertOrderedList" => array("group" => "format", 'id' => 'OrderedList',  'compact' => true, 'sort' => 150),
	"OrderedList" => array("group" => "format", 'id' => 'OrderedList',  'compact' => true, 'sort' => 150),
	"InsertUnorderedList" => array("group" => "format", 'id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
	"UnorderedList" => array("group" => "format", 'id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
	"Justify" => array("group" => "format", 'id' => 'AlignList', 'compact' => false, 'sort' => 190),
	"AlignList" => array("group" => "format", 'id' => 'AlignList', 'compact' => false, 'sort' => 190),
	"CreateLink" => array("group" => "insert", 'id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => 'bx-b-link-'.$arParams["FORM_ID"]),
	"InsertLink" => array("group" => "insert", 'id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => 'bx-b-link-'.$arParams["FORM_ID"]),
	"Image" => array("group" => "insert", 'id' => 'InsertImage',  'compact' => false, 'sort' => 220),
	"InsertImage" => array("group" => "insert", 'id' => 'InsertImage',  'compact' => false, 'sort' => 220),
	"InputVideo" => array("group" => "insert", 'id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => 'bx-b-video-'.$arParams["FORM_ID"]),
	"InsertVideo" => array("group" => "insert", 'id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => 'bx-b-video-'.$arParams["FORM_ID"]),
	"Table" => array("group" => "insert", 'id' => 'InsertTable',  'compact' => false, 'sort' => 250),
	"InsertTable" => array("group" => "insert", 'id' => 'InsertTable',  'compact' => false, 'sort' => 250),
	"Code" => array("group" => "insert", 'id' => 'Code',  'compact' => true, 'sort' => 260),
	"Quote" => array("group" => "insert", 'id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => 'bx-b-quote-'.$arParams["FORM_ID"]),
	"SmileList" => array("group" => "insert", 'id' => 'Smile',  'compact' => false, 'sort' => 280),
	"Smile" => array("group" => "insert", 'id' => 'Smile',  'compact' => false, 'sort' => 280),
);

foreach ($arParams["PARSER"] as $k)
{
	if (is_string($k) && array_key_exists($k, $controlsLHE))
	{
		$k = $controlsLHE[$k];
		if (!isset($controlsInGroup[$k["group"]]))
			$controlsInGroup[$k["group"]] = array();
		$controlsInGroup[$k["group"]][] = $k;
	}
}
$f = function($max, $item) {
	$max = max($item["sort"], $max);
	return $max;
};
$controls = array();
foreach ($controlsInGroup as $group => $c)
{
	$controls = array_merge($controls, $c, array(array('separator' => true, 'compact' => false, 'sort' => array_reduce($c, $f) + 10)));
}
$controls = array_merge($controls, array(
	array('id' => 'Fullscreen',  'compact' => false, 'sort' => 310),
	array('id' => 'BbCode',  'compact' => true, 'sort' => 340),
	array('id' => 'More',  'compact' => true, 'sort' => 400)
));
$Editor = new CHTMLEditor;
$res = array_merge(
	array(
		'height' => 200,
		'minBodyWidth' => 350,
		'normalBodyWidth' => 555,
		'bAllowPhp' => false,
		'limitPhpAccess' => false,
		'showTaskbars' => false,
		'showNodeNavi' => false,
		'askBeforeUnloadPage' => true,
		'bbCode' => true,
		'siteId' => SITE_ID,
		'autoResize' => true,
		'autoResizeOffset' => 40,
		'saveOnBlur' => true,
		'controlsMap' => $controls
	),
	(is_array($arParams["LHE"]) ? $arParams["LHE"] : array()),
	array(
		'name' => $arParams["TEXT"]["NAME"],
		'id' => $arParams["LHE"]["id"],
		'width' => '100%',
		'arSmilesSet' => $arResult["SMILES"]["SETS"],
		'arSmiles' => $arResult["SMILES"]["VALUE"],
		'content' => htmlspecialcharsBack($arParams["TEXT"]["VALUE"]),
		'iframeCss' => 'body{font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; font-size: 13px;}'.
			'.bx-spoiler {border:1px solid #C0C0C0;background-color:#fff4ca;padding: 4px 4px 4px 24px;color:#373737;border-radius:2px;min-height:1em;margin: 0;}'.
			(is_array($arParams["LHE"]) && isset($arParams["LHE"]["iframeCss"]) ? $arParams["LHE"]["iframeCss"] : ""),
	)
);

if((string) $arParams["TEXT"]["INPUT_NAME"] != '')
{
	$res['inputName'] = $arParams["TEXT"]["INPUT_NAME"];
}

$Editor->Show($res);
?>