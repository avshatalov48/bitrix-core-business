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

$converter = CBXPunycode::GetConverter();

$arParamsDetail = [
	'INSCRIPTION_FOR_EMPTY' => GetMessage('BCLMMD_NO_DATA'),
	'TITLE' => GetMessage('BCLMMD_TITLE'),
];

$arSection = [
	'TITLE' => htmlspecialcharsbx($arResult['DOMAIN_DECODED'])
];

foreach ($arResult['DATA'] as $key => $value)
{
	$data = $value['DATA'];

	if (isset($value['PROBLEM']) && $value['PROBLEM'] === true)
	{
		$data = '<span style="color:red">' . $data . '</span>';
	}

	$arSection['CONTENT'][] = [
		'TITLE' => GetMessage('BCLMMD_PARAM_' . $key),
		'VALUE' => $data
	];
}

$arParamsDetail['DATA'][] = $arSection;

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.list.enclosed',
	'.default',
	$arParamsDetail,
	false
);
?>

<script>
var listParams  = {
	ajaxUrl: "<?=$arResult['AJAX_PATH']?>"
};

var bcmm = new BX.BitrixCloud.MobileMonitor(app, listParams);
<?php
$url = (new \Bitrix\Main\Web\Uri($arParams['EDIT_URL']))->addParams([
	'action' => 'edit',
	'domain' => $arResult['DOMAIN'],
])->getUri();
?>
<?php if (isset($arParams['EDIT_URL'])):?>
	var listMenuItems = { items: [
		{
			name: "<?=GetMessage('BCLMMD_EDIT')?>",
			url: "<?=$url?>",
			icon: "edit"
		},
		{
			name: "<?=GetMessage('BCLMMD_DELETE')?>",
			action: function()
			{
				app.confirm({
					title: "<?=CUtil::JSEscape($converter->Decode($arResult['DOMAIN']))?>",
					text: "<?=GetMessage('BCLMMD_DELETE_CONFIRM')?>",
					buttons: ["<?=GetMessage('BCLMMD_BUTT_CANCEL')?>","<?=GetMessage('BCLMMD_BUTT_OK')?>"],
					callback: function(buttIdx)
					{
						if (buttIdx == 2)
							bcmm.deleteSite("<?=CUtil::JSEscape($arResult['DOMAIN'])?>");
					}
				});
			},
			icon: "delete"
		}
	] };

	app.menuCreate(listMenuItems);

	app.addButtons({
		menuButton:
		{
			type:     'context-menu',
			style:    'custom',

			callback: function()
			{
				app.menuShow();
			}
		}
	});

	BX.addCustomEvent('onAfterBCMMSiteDelete', function (params)
	{
		if(params.domain == "<?=CUtil::JSEscape($arResult['DOMAIN'])?>")
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
						BX.addCustomEvent("onOpenPageAfter", function(){
							app.closeController({drop:true});
						});
					}
				}
			}
			});
		}
	});

	BX.addCustomEvent('onAfterBCMMSiteUpdate', function (params)
	{
		if(params.domain == "<?=CUtil::JSEscape($arResult['DOMAIN'])?>")
		{
			bcmm.showRefreshing();
			location.reload(true);
		}
	});

<?php endif;?>

app.hidePopupLoader();
</script>
