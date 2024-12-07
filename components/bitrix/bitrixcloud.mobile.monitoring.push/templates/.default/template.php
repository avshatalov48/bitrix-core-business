<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$converter = CBXPunycode::GetConverter();
$arData = [];

if ($arResult['DOMAIN'] == '')
{
	$arItems  = [];

	foreach ($arResult['DOMAINS_NAMES'] as $domainName)
	{
		$domLink = (new \Bitrix\Main\Web\Uri($arResult['CURRENT_PAGE']))->addParams([
			'domain' => $domainName,
		])->getUri();

		$arItems[] = [
			'TYPE' => 'CUSTOM',
			'HTML_DATA' =>
				'<ul>'
					. '<li id="' . htmlspecialcharsbx('li_id_' . $domainName) . '">'
						. '<a href="javascript:void(0);">' . htmlspecialcharsEx($converter->Decode($domainName)) . '</a>'
					. '</li>'
				. '</ul>'
				. '<script>'
					. 'BX.ready(function(){ bcPush.makeFastButton("' . CUtil::JSEscape('li_id_' . $domainName) . '", "' . CUtil::JSEscape($domLink) . '");});'
				. '</script>'
		];
	}

	if (empty($arItems))
	{
		$arItems = [
			[
				'TYPE' => 'TEXT_RO',
				'VALUE' => GetMessage('BCMMP_NO_DOMAINS')
			]
		];
	}

	$arData[] = [
		'TYPE' => 'BLOCK',
		'TITLE' => GetMessage('BCMMP_DOMAINS_TITLE'),
		'DATA' => $arItems
	];
}
else
{
	$arData[] = [
		'TYPE' => 'BLOCK',
		'TITLE' => htmlspecialcharsbx($converter->Decode($arResult['DOMAIN'])),
		'DATA' => [
			[
				'TITLE' => GetMessage('BCMMP_PUSH_RECIEVE'),
				'VALUE' => $arResult['OPTIONS']['SUBSCRIBE'],
				'TYPE' => '2_RADIO_BUTTONS',
				'NAME' => 'SUBSCRIBE',
				'BUTT_Y' => [
					'TITLE' => GetMessage('BCMMP_ON'),
				],
				'BUTT_N' => [
					'TITLE' => GetMessage('BCMMP_OFF'),
				],
			]
		]
	];
}

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	[
		'HEAD' => GetMessage('BCMMP_TITLE2'),
		'DATA' => $arData
	],
	false
);

?>

<script>

	app.setPageTitle({title: "<?=GetMessage('BCMMP_TITLE')?>"});

	BX.message({
		"BCMMP_JS_SAVING": "<?=GetMessage('BCMMP_JS_SAVING')?>",
		"BCMMP_JS_SAVE_ERROR": "<?=GetMessage('BCMMP_JS_SAVE_ERROR')?>"
	});


	var jsParams = {
		domain: "<?=CUtil::JSEscape($arResult['DOMAIN'])?>",
		ajaxUrl: "<?=$arResult['AJAX_URL']?>"
	};

	var bcPush = new BX.BitrixCloud.MobileMonitorPush(app, jsParams);

	<?php if ($arResult['DOMAIN'] != ''):?>
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
	<?php endif;?>
</script>
