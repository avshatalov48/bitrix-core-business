<?php
/**
 * @var $component \CatalogAgentContractDetail
 * @var $this \CBitrixComponentTemplate
 * @var \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\UI;

Main\UI\Extension::load([
	'ui.entity-editor',
	'catalog.agent-contract',
	'catalog.entity-editor.field.contractor',
	'catalog.entity-editor.field.productset',
	'catalog.entity-editor.field.sectionset',
]);

global $APPLICATION;

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.error',
		'',
		[
			'TITLE' => $arResult['ERROR_MESSAGES'][0],
		]
	);

	return;
}

if ($arResult['ID'] > 0)
{
	$APPLICATION->SetTitle($arResult['TITLE']);
}
else
{
	$APPLICATION->SetTitle(Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_TEMPLATE_TITLE_NEW'));
}

if ($arResult['ID'] > 0)
{
	$this->SetViewTarget('in_pagetitle');
	?>
	<span id="pagetitle_btn_wrapper" class="pagetitile-button-container">
		<span id="pagetitle_edit" class="pagetitle-edit-button"></span>
	</span>
	<?php
	$this->EndViewTarget();
}

UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

$entityEditorControlFactory = 'BX.UI.EntityEditorControlFactory';
$entityEditorControllerFactory = 'BX.UI.EntityEditorControllerFactory';
$componentName = 'bitrix:ui.form';
$componentParams = [
	'GUID' => 'CATALOG_AGENT_CONTRACT_DETAIL',
	'INITIAL_MODE' => $arResult['INITIAL_MODE'],
	'ENTITY_ID' => $arResult['ENTITY_ID'],
	'ENTITY_TYPE_NAME' => 'agent_contract',
	'ENTITY_FIELDS' => $arResult['ENTITY_FIELDS'],
	'ENTITY_CONFIG' => $arResult['ENTITY_CONFIG'],
	'ENTITY_DATA' => $arResult['ENTITY_DATA'],
	'ENTITY_CONTROLLERS' => $arResult['ENTITY_CONTROLLERS'],
	'ENABLE_PAGE_TITLE_CONTROLS' => true,
	'ENABLE_COMMON_CONFIGURATION_UPDATE' => true,
	'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => true,
	'ENABLE_SECTION_DRAG_DROP' => false,
	'ENABLE_CONFIG_CONTROL' => false,
	'ENABLE_FIELD_DRAG_DROP' => false,
	'ENABLE_FIELDS_CONTEXT_MENU' => false,
	'COMPONENT_AJAX_DATA' => [
		'COMPONENT_NAME' => $component->getName(),
		'ACTION_NAME' => 'save',
		'SIGNED_PARAMETERS' => $component->getSignedParameters(),
	],
	'SERVICE_URL' => '/bitrix/components/bitrix/catalog.agent.contract.detail/ajax.php?' . bitrix_sessid_get(),
];

if ($arResult['INCLUDE_CRM_ENTITY_EDITOR'])
{
	$entityEditorControlFactory = 'BX.Crm.EntityEditorControlFactory';
	$entityEditorControllerFactory = 'BX.Crm.EntityEditorControllerFactory';
	$componentName = 'bitrix:crm.entity.editor';
	$componentParams = array_merge(
		$componentParams,
		[
			'MODULE_ID' => 'crm',
			'ENTITY_TYPE_ID' => \CCrmOwnerType::AgentContractDocument,
			'CONFIG_ID' => 'agent_contract_details',
		]
	);

	$prefix = mb_strtolower($componentParams['GUID']);
	$activityEditorId = "{$prefix}_editor";

	$APPLICATION->IncludeComponent(
		'bitrix:crm.activity.editor',
		'',
		array(
			'CONTAINER_ID' => '',
			'EDITOR_ID' => $activityEditorId,
			'PREFIX' => $prefix,
			'ENABLE_UI' => false,
			'ENABLE_TOOLBAR' => false,
			'ENABLE_EMAIL_ADD' => true,
			'ENABLE_TASK_ADD' => false,
			'MARK_AS_COMPLETED_ON_VIEW' => false,
			'SKIP_VISUAL_COMPONENTS' => 'Y'
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}
?>
<script>
	BX.Catalog.Agent.ContractorComponent.Detail.registerControllerFactory('<?= \CUtil::JSEscape($entityEditorControllerFactory) ?>');
	BX.Catalog.Agent.ContractorComponent.Detail.registerFieldFactory('<?= \CUtil::JSEscape($entityEditorControlFactory) ?>');
	BX.Catalog.Agent.ContractorComponent.Detail.registerModelFactory();
</script>
<?php
$APPLICATION->IncludeComponent(
	$componentName,
	'.default',
	$componentParams,
	$component
);
