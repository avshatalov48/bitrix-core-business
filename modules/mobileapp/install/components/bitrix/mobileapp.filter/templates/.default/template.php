<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arResult["SHOW_FIELDS_LIST"])
{
	require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/fields.php');
	return;
}

$arData = array();
$arFilteredFields = array();

foreach ($arParams['FIELDS'] as $fieldID => $arField)
	if(in_array($fieldID, $arResult["VISIBLE_FIELDS"]))
		$arFilteredFields[$fieldID] = $arField;
?>

<div id="mapp_filter_content">
	<?=CAdminMobileFilter::getHtml($arFilteredFields, isset($arParams["GET_JS"]) ? true : false);?>
</div>

<script type="text/javascript">

	app.setPageTitle({title: "<?=GetMessage("MOBILE_APP_FILTER_TITLE")?>"});

	BX.message({
		MOBILE_APP_FILTER_SAVING: "<?=GetMessage("MOBILE_APP_FILTER_SAVING")?>"
	});

	var filterParams = {
							filterFields: <?=CUtil::PhpToJsObject($arParams["FIELDS"])?>,
							filterId: "<?=$arParams["FILTER_ID"]?>",
							applyEvent: "<?=$arParams["JS_EVENT_APPLY"]?>",
							url: "<?=$APPLICATION->GetCurPageParam()?>",
							ajaxUrl: "<?=$arResult['AJAX_URL']?>",
							fieldEditUrl: "<?=$APPLICATION->GetCurPage()?>",
							selectAllConst: "<?=CAdminMobileFilter::SELECT_ALL?>",
							filteredFields: <?=CUtil::PhpToJsObject($arFilteredFields)?>
						};

	maAdminFilter = new __MAAdminFilter (filterParams);

	fltMenuItems = {
	items: [
		{
			name: "<?=GetMessage('MOBILE_APP_FILTER_APPLY');?>",
			action: function(){maAdminFilter.apply();},
			icon: "filter"
		},
		{
			name: "<?=GetMessage('MOBILE_APP_FILTER_FIELDS_LIST');?>",
			arrowFlag: true,
			action: function(){maAdminFilter.showFieldsList();},
			icon: "settings"
		},
		{
			name: "<?=GetMessage('MOBILE_APP_FILTER_FIELDS_RESET');?>",
			action: function(){maAdminFilter.reset();}
		}
		]
	};

	app.menuCreate(fltMenuItems);

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: "left",
			name: "<?=GetMessage('SMOL_BACK');?>",
			callback: function()
			{
				app.closeController();
			}
		},
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

	BX.addCustomEvent("onAfterFilterVisibleFieldsChange", function (params){ maAdminFilter.getHtmlAjax(params); });
	BX.addCustomEvent("onAfterFilterFieldValueChange", function (params){ maAdminFilter.onFieldValueChange(params); });
	BX.addCustomEvent("onMobileAppNeedJSFile", function(params){
		maAdminFilter.loadScript(params.url);
	});
</script>
