<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("fileman"))
	return;
/**
 * @var array $arResult
 * @var array $arParams
 */

$possibleButtons = [
	'Copilot' => [
		'HTML' => '<i class="ui-icon-set --copilot-ai" id="bx-b-copilot-'.$arParams['FORM_ID'].'"></i><span class="main-post-form-toolbar-button-copilot">'.GetMessage('MPF_COPILOT')."</span>",
		'ID' => 'copilot',
	],
	'UploadFile' => [ //Custom button
		'aliases' => ['UploadImage', 'UploadFile'],
		// id is here just for compatibility and shoud be deleted at an opportunity
		'HTML' => '<i id="bx-b-uploadfile-'.$arParams['FORM_ID'].'"></i><span class="main-post-form-toolbar-button-file">'.GetMessage('MPF_FILE')."</span>",
		'ID' => 'file',
	],
	'MentionUser' => [//Custom button
		// id is here just for compatibility and shoud be deleted at an opportunity
		'HTML' => '<span id="bx-b-mention-'.$arParams['FORM_ID'].'"><i></i><span>'.GetMessage('MPF_MENTION')."</span></span>",
		'ID' => 'mention',
	],
	'Quote' => [ //LHE Proxy Button
		'HTML' => '<i id="bx-b-quote-'.$arParams['FORM_ID'].'"></i><span>'.GetMessage('MPF_QUOTE')."</span>",
		'LHE_ID' => 'bx-b-quote-'.$arParams['FORM_ID'],
		'ID' => 'quote',
	],
	'SearchTag' => [//Custom button
		'aliases' => ['InputTag', 'SearchTag'],
		'HTML' => '<i></i><span>'.GetMessage('MPF_TAG_TITLE')."</span>",
		'ID' => 'search-tag',
	],
	/*	'CreateLink' => [ //LHE Proxy Button
			'HTML' => '<i id="bx-b-link-'.$arParams['FORM_ID'].'"></i>Link',
			'LHE_ID' => 'bx-b-link-'.$arParams['FORM_ID'],
			'ID' => 'create-link',
		],
		'InputVideo' => [ //LHE Proxy Button
			'HTML' => '<i id="bx-b-video-'.$arParams['FORM_ID'].'"></i>Video',
			'LHE_ID' => 'bx-b-video-'.$arParams['FORM_ID'],
			'ID' => 'video',
		],
	*/

];

$actualButtons = array_filter($possibleButtons, function ($value, $key) use ($arParams) {
	$keys = array_merge([$key], (array_key_exists('aliases', $value) ? $value['aliases'] : []));
	return sizeof(array_intersect($keys, $arParams['BUTTONS'])) > 0;
}, ARRAY_FILTER_USE_BOTH);

if (!$arParams['COPILOT_AVAILABLE'])
{
	unset($actualButtons['Copilot']);
}

if (isset($arParams['~BUTTONS_HTML']) && is_array($arParams['~BUTTONS_HTML']))
{
	foreach ($arParams['~BUTTONS_HTML'] as $key => $val)
	{
		$actualButtons[$key] = [
			'ID' => $key,
			'HTML' => $val,
		];
	}
}

$possibleControls = array(
	"Bold" => array("group" => "bui", 'id' => 'Bold',  'compact' => true, 'sort' => 80),
	"Italic" => array("group" => "bui", 'id' => 'Italic',  'compact' => false, 'sort' => 90),
	"Underline" => array("group" => "bui", 'id' => 'Underline',  'compact' => true, 'sort' => 100),
	"Strike" => array("group" => "bui", 'id' => 'Strikeout',  'compact' => false, 'sort' => 110),
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
	"CreateLink" => array("group" => "insert", 'id' => 'InsertLink',  'compact' => false, 'sort' => 210, 'wrap' => $possibleButtons['CreateLink']['LHE_ID'] ?? ''),
	"InsertLink" => array("group" => "insert", 'id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => $possibleButtons['CreateLink']['LHE_ID'] ?? ''),
	"Image" => array("group" => "insert", 'id' => 'InsertImage',  'compact' => false, 'sort' => 220),
	"InsertImage" => array("group" => "insert", 'id' => 'InsertImage',  'compact' => false, 'sort' => 220),
	"InputVideo" => array("group" => "insert", 'id' => 'InsertVideo',  'compact' => false, 'sort' => 230, 'wrap' => $possibleButtons['InputVideo']['LHE_ID'] ?? ''),
	"InsertVideo" => array("group" => "insert", 'id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => $possibleButtons['InputVideo']['LHE_ID'] ?? ''),
	"Table" => array("group" => "insert", 'id' => 'InsertTable',  'compact' => false, 'sort' => 250),
	"InsertTable" => array("group" => "insert", 'id' => 'InsertTable',  'compact' => false, 'sort' => 250),
	"Code" => array("group" => "insert", 'id' => 'Code',  'compact' => true, 'sort' => 260),
	"Quote" => array("group" => "insert", 'id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => $possibleButtons['Quote']['LHE_ID']),
	"SmileList" => array("group" => "insert", 'id' => 'Smile',  'compact' => false, 'sort' => 280),
	"Smile" => array("group" => "insert", 'id' => 'Smile',  'compact' => false, 'sort' => 280),
);
$actualControls = array_filter($possibleControls, function($key) use ($arParams) {
	return in_array($key, $arParams['PARSER']);
}, ARRAY_FILTER_USE_KEY);
$groupedControls = [];
foreach ($actualControls as $k)
{
	$groupId = $k["group"];
	$groupedControls[$groupId] = ($groupedControls[$groupId] ?? []);
	$groupedControls[$groupId][] = $k;
}
$f = function($max, $item) {
	return max($item["sort"], $max);
};
$finalControls = [];
foreach ($groupedControls as $groupId => $controls)
{
	$finalControls = array_merge(
		$finalControls,
		$controls,
		[['separator' => true, 'compact' => false, 'sort' => (array_reduce($controls, $f) + 10)]]
	);
}
$finalControls = array_merge($finalControls, array(
	array('id' => 'Fullscreen',  'compact' => false, 'sort' => 310),
	array('id' => 'BbCode',  'compact' => true, 'sort' => 340),
	array('id' => 'More',  'compact' => true, 'sort' => 400)
));

$Editor = new CHTMLEditor;
$res = array_merge(
	array(
		'height' => 200,
		'minBodyWidth' => 350,
		'normalBodyWidth' => 740,
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
		'controlsMap' => $finalControls
	),
	(is_array($arParams["LHE"]) ? $arParams["LHE"] : array()),
	array(
		'name' => $arParams["TEXT"]["NAME"],
		'id' => $arParams["LHE"]["id"],
		'width' => '100%',
		'arSmilesSet' => $arResult["SMILES"]["SETS"],
		'arSmiles' => $arResult["SMILES"]["VALUE"],
		'content' => htmlspecialcharsBack($arParams["TEXT"]["VALUE"]),
		'iframeCss' =>
			'.bx-spoiler {border:1px solid #cecece;background-color:#f6f6f6;padding: 8px 8px 8px 24px;color:#373737;border-radius:var(--ui-border-radius-sm, 2px);min-height:1em;margin: 0;}'.
			(is_array($arParams["LHE"]) && isset($arParams["LHE"]["iframeCss"]) ? $arParams["LHE"]["iframeCss"] : ""),
	)
);

if(isset($arParams["TEXT"]["INPUT_NAME"]) && (string)$arParams["TEXT"]["INPUT_NAME"] != '')
{
	$res['inputName'] = $arParams["TEXT"]["INPUT_NAME"];
}

$Editor->Show($res);

return $actualButtons;
