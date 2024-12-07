<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
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

$arData = [
	[
		'TYPE' => 'BLOCK',
		'TITLE' => GetMessage('BCLMME_DOMAIN_TITLE'),
		'DATA' => [
			[
				'TYPE' => 'TEXT_RO',
				'VALUE' => htmlspecialcharsEx($arResult['DOMAIN_CONVERTED']),
			]
		]
	],
	[
		'TYPE' => 'BLOCK',
		'TITLE' => GetMessage('BCLMME_HEAD'),
		'DATA' => [
			[
				'TITLE' => GetMessage('BCLMME_HTTP_RESPONSE_TIME_TITLE'),
				'CHECKED' => in_array('test_http_response_time', $arResult['DOMAIN_PARAMS']['TESTS'], true),
				'VALUE' => 'test_http_response_time',
				'NAME' => 'TESTS[]',
				'TYPE' => 'CHECKBOX',
			],
			[
				'TITLE' => GetMessage('BCLMME_TEST_DOMAIN_REGISTRATION_TITLE'),
				'CHECKED' => in_array('test_domain_registration', $arResult['DOMAIN_PARAMS']['TESTS'], true),
				'VALUE' => 'test_domain_registration',
				'NAME' => 'TESTS[]',
				'TYPE' => 'CHECKBOX',
			],
			[
				'TITLE' => GetMessage('BCLMME_TEST_LICENSE_TITLE'),
				'CHECKED' => in_array('test_lic', $arResult['DOMAIN_PARAMS']['TESTS'], true),
				'VALUE' => 'test_lic',
				'NAME' => 'TESTS[]',
				'TYPE' => 'CHECKBOX',
			],
			[
				'TITLE' => GetMessage('BCLMME_IS_HTTPS_TITLE'),
				'CHECKED' => $arResult['DOMAIN_PARAMS']['IS_HTTPS'] === 'Y',
				'VALUE' => 'Y',
				'NAME' => 'IS_HTTPS',
				'TYPE' => 'CHECKBOX',
			]
		],
	],
	[
		'TYPE' => 'HIDDEN',
		'VALUE' => $arResult['LANG'],
		'NAME' => 'LANG',
	],
	[
		'TYPE' => 'BLOCK',
		'TITLE' => GetMessage('BCLMME_EMAILS_TITLE'),
		'DATA' => [
			[
				'TYPE' => 'TEXT',
				'VALUES' => $arResult['DOMAIN_PARAMS']['EMAILS'],
				'NAME' => 'EMAILS[]',
			],
		],
	],
];

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	[
		'TITLE' => GetMessage('BCLMME_TITLE'),
		'DATA' => $arData,
		'ON_JS_CLICK_SUBMIT_BUTTON' => 'OnBCMMSiteSubmit',
		'BUTTONS' => ['SAVE'],
	],
	false
);
?>

<script>
	var listParams  = {
		ajaxUrl: "<?=$arResult['AJAX_PATH']?>"
	};

	var bcmme = new BX.BitrixCloud.MobileMonitorEdit();
	var bcmm = new BX.BitrixCloud.MobileMonitor(app, listParams);

	function OnBCMMSiteSubmit(form)
	{
		var fields = bcmme.getFields(form);

		if(fields)
			bcmm.updateSite('<?=CUtil::JSEscape($arResult['DOMAIN_PARAMS']['DOMAIN'])?>', fields);
	}

	BX.addCustomEvent('onAfterBCMMSiteDelete', function (params)
	{
		if(params.domain == '<?=CUtil::JSEscape($arResult['DOMAIN_PARAMS']['DOMAIN'])?>')
		{
			app.checkOpenStatus({callback:function(response)
				{
					if(response)
					{
						if(response.status == "visible")
						{
							app.closeController({drop:true});
						}
						else
						{
							BX.addCustomEvent( "onOpenPageAfter", function() {
									app.closeController({drop:true});
								}
							);
						}
					}
				}
			});
		}
	});

	BX.addCustomEvent('onAfterBCMMSiteUpdate', function (params)
	{
		if(params.domain == '<?=CUtil::JSEscape($arResult['DOMAIN_PARAMS']['DOMAIN'])?>')
		{
			app.checkOpenStatus({callback:function(response)
				{
					if(response)
					{
						if(response.status == "visible")
						{
							app.closeController({drop:true});
						}
						else
						{
							bcmm.showRefreshing();
							location.reload(true);
						}
					}
				}
			});
		}
	});

app.hidePopupLoader();
</script>
