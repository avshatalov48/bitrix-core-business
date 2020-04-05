<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */

global $APPLICATION;

CUtil::InitJSCore(array('ajax', 'popup'));

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/crm.js');

$fieldName = $arParams['arUserField']['~FIELD_NAME'];
$formName = isset($arParams['form_name']) ? strval($arParams['form_name']) : '';
$fieldUID = strtolower(str_replace('_', '-', $fieldName));
if($formName !== '')
{
	$fieldUID = strtolower(str_replace('_', '-', $formName)).'-'.$fieldUID;
}
$randString = $this->randString();
$jsObject = 'CrmEntitySelector_'.$randString;
$listPrefix = array('DEAL' => 'D', 'CONTACT' => 'C', 'COMPANY' => 'CO', 'LEAD' => 'L');
?>
<div id="crm-<?=$fieldUID?>-box">
	<div class="crm-button-open">
		<span id="crm-<?=$fieldUID?>-open" onclick="obCrm[this.id].Open()">
			<?=GetMessage('CRM_FF_CHOISE');?>
		</span>
		<?if(!empty($arParams['createNewEntity'])):?>
			<span onclick="BX['<?=$jsObject?>'].createNewEntity(event);">
				<?=GetMessage('CRM_CES_CREATE');?>
			</span>
		<?endif;?>
	</div>
</div>
<script type="text/javascript">
	BX.ready(function() {
		BX['<?=$jsObject?>'] = new BX.CrmEntitySelector({
			randomString: '<?=$randString?>',
			jsObject: '<?=$jsObject?>',
			fieldUid: '<?=$fieldUID?>',
			fieldName: '<?=$fieldName?>',
			usePrefix: '<?=$arResult['PREFIX']?>',
			multiple: '<?=$arResult['MULTIPLE']?>',
			context: '<?=!empty($arParams['CONTEXT']) ? $arParams['CONTEXT'] : 'crmEntityCreate'?>',
			listPrefix: <?=\Bitrix\Main\Web\Json::encode($listPrefix)?>,
			listElement: <?=\Bitrix\Main\Web\Json::encode($arResult['ELEMENT'])?>,
			listEntityType: <?=\Bitrix\Main\Web\Json::encode($arResult['ENTITY_TYPE'])?>,
			listEntityCreateUrl: <?=\Bitrix\Main\Web\Json::encode($arResult['LIST_ENTITY_CREATE_URL'])?>,
			pluralCreation: '<?=!empty($arResult['PLURAL_CREATION']) ? 'true' : '' ?>',
			currentEntityType: '<?=!empty($arResult['CURRENT_ENTITY_TYPE']) ? $arResult['CURRENT_ENTITY_TYPE'] : null?>'
		});

		BX.message({
			CRM_FF_LEAD: '<?=GetMessageJS("CRM_FF_LEAD")?>',
			CRM_FF_CONTACT: '<?=GetMessageJS("CRM_FF_CONTACT")?>',
			CRM_FF_COMPANY: '<?=GetMessageJS("CRM_FF_COMPANY")?>',
			CRM_FF_DEAL: '<?=GetMessageJS("CRM_FF_DEAL")?>',
			CRM_FF_QUOTE: '<?=GetMessageJS("CRM_FF_QUOTE")?>',
			CRM_FF_OK: '<?=GetMessageJS("CRM_FF_OK")?>',
			CRM_FF_CANCEL: '<?=GetMessageJS("CRM_FF_CANCEL")?>',
			CRM_FF_CLOSE: '<?=GetMessageJS("CRM_FF_CLOSE")?>',
			CRM_FF_SEARCH: '<?=GetMessageJS("CRM_FF_SEARCH")?>',
			CRM_FF_NO_RESULT: '<?=GetMessageJS("CRM_FF_NO_RESULT")?>',
			CRM_FF_CHOISE: '<?=GetMessageJS("CRM_FF_CHOISE")?>',
			CRM_FF_CHANGE: '<?=GetMessageJS("CRM_FF_CHANGE")?>',
			CRM_FF_LAST: '<?=GetMessageJS("CRM_FF_LAST")?>',
			CRM_CES_CREATE_LEAD: '<?=GetMessageJS("CRM_CES_CREATE_LEAD")?>',
			CRM_CES_CREATE_CONTACT: '<?=GetMessageJS("CRM_CES_CREATE_CONTACT")?>',
			CRM_CES_CREATE_COMPANY: '<?=GetMessageJS("CRM_CES_CREATE_COMPANY")?>',
			CRM_CES_CREATE_DEAL: '<?=GetMessageJS("CRM_CES_CREATE_DEAL")?>'
		});

		window.setTimeout(BX['<?=$jsObject?>'].initWidgetEntitySelection(), 100);
	});
</script>