<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$converter = CBXPunycode::GetConverter();
$arData = array();

if($arResult["DOMAIN"] == "")
{
	$arItems  = array();

	foreach ($arResult["DOMAINS_NAMES"] as $domainName)
	{
		$domLink = CHTTP::urlAddParams(
			$arResult["CURRENT_PAGE"],
				array(
					"domain" => urlencode($domainName)
				)
		);

		$arItems[] = array(
			"TYPE" => "CUSTOM",
			"HTML_DATA" =>
				'<ul>'.
					'<li id="li_id_'.$domainName.'">'.
						'<a href="javascript:void(0);">'.htmlspecialcharsEx($converter->Decode($domainName)).'</a>'.
					'</li>'.
				'</ul>'.
				'<script type="text/javascript">'.
					'BX.ready(function(){ bcPush.makeFastButton("li_id_'.$domainName.'", "'.$domLink.'");});'.
				'</script>'
		);
	}

	if(empty($arItems))
	{
		$arItems = array(
			array(
				"TYPE" => "TEXT_RO",
				"VALUE" => GetMessage("BCMMP_NO_DOMAINS")
			)
		);
	}

	$arData[] = array(
		"TYPE" => "BLOCK",
		"TITLE" => GetMessage("BCMMP_DOMAINS_TITLE"),
		"DATA" => $arItems
	);
}
else
{
	$arData[] = array(
		"TYPE" => "BLOCK",
		"TITLE" => htmlspecialcharsbx($converter->Decode($arResult["DOMAIN"])),
		"DATA" => array(
			array(
				"TITLE" => GetMessage("BCMMP_PUSH_RECIEVE"),
				"VALUE" => $arResult["OPTIONS"]["SUBSCRIBE"],
				"TYPE" => "2_RADIO_BUTTONS",
				"NAME" => "SUBSCRIBE",
				"BUTT_Y" => array(
					"TITLE" => GetMessage("BCMMP_ON"),
				),
				"BUTT_N" => array(
					"TITLE" => GetMessage("BCMMP_OFF"),
				),
			)
		)
	);
}

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"HEAD" => GetMessage("BCMMP_TITLE2"),
		"DATA" => $arData
		),
	false
);

?>

<script type="text/javascript">

	app.setPageTitle({title: "<?=GetMessage('BCMMP_TITLE')?>"});

	BX.message({
		"BCMMP_JS_SAVING": "<?=GetMessage("BCMMP_JS_SAVING")?>",
		"BCMMP_JS_SAVE_ERROR": "<?=GetMessage("BCMMP_JS_SAVE_ERROR")?>"
	});


	var jsParams = {
		domain: "<?=CUtil::JSEscape($arResult["DOMAIN"])?>",
		ajaxUrl: "<?=$arResult['AJAX_URL']?>"
	};

	var bcPush = new __bitrixCloudPush(jsParams);

	<?if($arResult["DOMAIN"] != ""):?>
		app.addButtons({
			cancelButton:
			{
				type: "back_text",
				style: "custom",
				position: 'left',
				name: "<?=GetMessage('BCMMP_BACK');?>",
				callback: function()
				{
					bcPush.close();
				}
			},
			saveButton:
			{
				type: "right_text",
				style: "custom",
				name: "<?=GetMessage('BCMMP_SAVE');?>",
				callback: function()
				{
					bcPush.save();
				}
			}
		});
	<?endif;?>
</script>
