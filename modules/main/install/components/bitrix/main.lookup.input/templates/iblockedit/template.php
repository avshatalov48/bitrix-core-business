<?
/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global array $arParams
 * @global array $arResult
 * @global CBitrixComponentTemplate $this
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;

if ((defined('BX_PUBLIC_MODE')) && (1 == BX_PUBLIC_MODE))
{
	$APPLICATION->SetAdditionalCSS($this->GetFolder().'/style.css');
	$APPLICATION->AddHeadScript("/bitrix/js/main/ajax.js");
	$APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");
	$APPLICATION->AddHeadScript("/bitrix/js/main/public_tools.js");
	$APPLICATION->AddHeadScript($this->__component->getPath().'/script.js');
}
$APPLICATION->AddHeadScript($this->GetFolder().'/script2.js');

$control_id = $arParams['CONTROL_ID'];
$textarea_id = (!empty($arParams['INPUT_NAME_STRING']) ? $arParams['INPUT_NAME_STRING'] : 'visual_'.$control_id);
$boolStringValue = (isset($arParams['INPUT_VALUE_STRING']) && $arParams['INPUT_VALUE_STRING'] != '');
$INPUT_VALUE = array();

$mliFieldClass = '';
if ($arParams['MAIN_UI_FILTER'] == 'Y')
{
	$mliLayoutClass = 'mli-layout-ui-filter';
	$mliFieldClass = 'mli-field-ui-filter';
}

if ($boolStringValue)
{
	$arTokens = preg_split('/(?<=])[\n;,]+/', $arParams['~INPUT_VALUE_STRING']);
	foreach($arTokens as $key => $token)
	{
		if(preg_match("/^(.*) \\[(\\d+)\\]/", $token, $match))
		{
			$match[2] = intval($match[2]);
			if (0 < $match[2])
				$INPUT_VALUE[] = array(
					"ID" => $match[2],
					"NAME" => $match[1],
				);
		}
	}
}
?><div class="mli-layout <?=$mliLayoutClass?>" id="layout_<?=$control_id?>">
<div style="display:none" id="value_container_<?=$control_id?>">
<?if ($INPUT_VALUE):?>
	<?foreach ($INPUT_VALUE as $value):?>
		<input type="hidden" name="<?echo $arParams['~INPUT_NAME']; ?>" value="<?echo $value["ID"]?>">
	<?endforeach;?>
<?else:?>
	<input type="hidden" name="<?echo $arParams['~INPUT_NAME']; ?>" value="">
<?endif;?>
</div>
<?
if($arParams["MULTIPLE"]=="Y" && $arParams['MAIN_UI_FILTER'] !== 'Y')
{
	?><textarea name="<?=$textarea_id?>" id="<?=$textarea_id?>" class="mli-field"><? echo ($boolStringValue ? htmlspecialcharsbx($arParams['INPUT_VALUE_STRING']) : '');?></textarea><?
}
else
{
	?><input autocomplete="off" type="text" name="<?=$textarea_id?>" id="<?=$textarea_id?>" value="<? echo ($boolStringValue ? htmlspecialcharsbx($arParams['INPUT_VALUE_STRING']) : '');?>" class="mli-field <?=$mliFieldClass?>" /><?
}
?></div><?

$request = Context::getCurrent()->getRequest();

$arAjaxParams = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	'WITHOUT_IBLOCK' => $arParams['WITHOUT_IBLOCK'],
	"lang" => LANGUAGE_ID,
	"admin" => ($request->isAdminSection() ? 'Y' : 'N'),
	'TYPE' => $arParams['TYPE'],
	'RESULT_COUNT' => $arParams['RESULT_COUNT']
);
if (!$request->isAdminSection())
{
	$arAjaxParams["site"] = SITE_ID;
}
if ($arParams['BAN_SYM'] != '')
{
	$arAjaxParams['BAN_SYM'] = $arParams['BAN_SYM'];
	$arAjaxParams['REP_SYM'] = $arParams['REP_SYM'];
}

$arSelectorParams = array(
	'AJAX_PAGE' => $this->GetFolder()."/ajax.php",
	'AJAX_PARAMS' => $arAjaxParams,
	'CONTROL_ID' => $control_id,
	'LAYOUT_ID' => 'layout_'.$control_id,
	'INPUT_NAME' => $arParams['~INPUT_NAME'],
	'PROACTIVE' => 'MESSAGE',
	'VALUE' => $INPUT_VALUE,
	'VISUAL' => array(
		'ID' => $textarea_id,
		'MAIN_UI_FILTER' => $arParams['MAIN_UI_FILTER'],
		'MULTIPLE' => $arParams['MULTIPLE'],
		'MAX_HEIGHT' => $arParams['MAX_HEIGHT'],
		'MIN_HEIGHT' => $arParams['MIN_HEIGHT'],
		'START_TEXT' => $arParams['START_TEXT'],
		'SEARCH_POSITION' => ($arParams['FILTER'] == 'Y' ? 'absolute' : ''),
		'SEARCH_ZINDEX' => 4000,
	),
);

if (!empty($arParams['INPUT_NAME_SUSPICIOUS']))
{
	$arSelectorParams['INPUT_NAME_SUSPICIOUS'] = $arParams['INPUT_NAME_SUSPICIOUS'];
}
if ($arParams['MAX_WIDTH'] > 0)
{
	$arSelectorParams['VISUAL']['MAX_WIDTH'] = $arParams['MAX_WIDTH'];
}

?>
<script type="text/javascript">
BX.ready(
	BX.defer(function(){
		window.jsMLI_<?=$control_id?> = new JCMainLookupAdminSelector(<? echo CUtil::PhpToJSObject($arSelectorParams); ?>);
		<? if ((defined('BX_PUBLIC_MODE')) && (1 == BX_PUBLIC_MODE))
		{
			?>window.jsMLI_<?=$control_id?>.Init();
			BX.addCustomEvent(BX.WindowManager.Get(), 'onWindowClose', function() {window.jsMLI_<?=$control_id?>.Clear(); window.jsMLI_<?=$control_id?> = null; });
			<?
		}
		if (array_key_exists('RESET', $arParams) && 'Y' == $arParams['RESET'])
		{
			?>window.jsMLI_<?=$control_id?>.Reset(true, false);<?
		}
		?>
	})
);
</script>