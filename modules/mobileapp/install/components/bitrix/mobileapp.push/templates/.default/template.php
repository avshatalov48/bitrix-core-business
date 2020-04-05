<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arItems = array();
$arTmp = array();

if($arResult["DATA"]["TYPE"] == "OPTIONS_SECTION")
{
	$optionsData = CAdminMobilePush::getOptions($arResult["PATH"]);

	foreach ($arResult["DATA"]["OPTIONS"] as $option)
	{
		if(isset($optionsData[$option["ID"]]))
			$checked = $optionsData[$option["ID"]] == 'Y';
		elseif(isset($option["DEFAULT"]))
			$checked = $option["DEFAULT"];
		else
			$checked = false;

		$arItems[] = array(
			"TITLE" => $option["TITLE"],
			"VALUE" => $option["ID"],
			"NAME" => "OPTIONS[]",
			"TYPE" => "CHECKBOX",
			"CHECKED" => $checked
			);
	}
}
elseif($arResult["DATA"]["TYPE"] == "SECTIONS_SECTION")
{
	foreach ($arResult["DATA"]["SECTIONS"] as $sectId => $section)
	{
		$path = $sectId;

		if(strlen($arResult["PATH"]) > 0)
			$path = $arResult["PATH"].'/'.$path;

		$path = urlencode($path);

		$sectionLink = CHTTP::urlAddParams(
			$arResult["CURRENT_PAGE"],
				array(
					"path" => $path
				)
		);

		$arItems[] = array(
			"TYPE" => "CUSTOM",
			"HTML_DATA" =>
				'<ul>'.
					'<li id="push_section_'.$sectId.'">'.
						'<a href="?path='.$path.'">'.$section["TITLE"].'</a>'.
					'</li>'.
				'</ul>'.
				'<script type="text/javascript">'.
					'BX.ready(function(){ mappPush.makeFastButton("push_section_'.$sectId.'", "'.$sectionLink.'");});'.
				'</script>'
			);
	}
}

$arData[] = 	array(
	"TYPE" => "BLOCK",
	"TITLE" => $arResult["DATA"]["TITLE"],
	"DATA" => $arItems
);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"TITLE" => GetMessage("MOBILE_APP_PUSH_TITLE"),
		"DATA" => $arData
		),
	false
);

$path = explode("/", $arResult["PATH"]);

?>
<script type="text/javascript">

	BX.message({
		"MOBILE_APP_SAVE_ERROR": "<?=GetMessage("MOBILE_APP_SAVE_ERROR")?>",
		"MOBILE_APP_SAVING": "<?=GetMessage("MOBILE_APP_SAVING")?>"
	});

	var jsParams = {
		path: "<?=CUtil::JSEscape($arResult["PATH"])?>",
		ajaxUrl: "<?=$arResult['AJAX_URL']?>"
	};

	var mappPush = new __mobAppPush(jsParams);

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: 'left',
			name: "<?=GetMessage('MOBILE_APP_PUSH_BACK');?>",
			callback: function()
			{
				mappPush.close();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('MOBILE_APP_PUSH_SAVE');?>",

			callback: function()
			{
				mappPush.save();
			}
		}
	});

</script>

