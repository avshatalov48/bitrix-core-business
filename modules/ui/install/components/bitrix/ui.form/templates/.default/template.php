<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var string $componentPath
 */

\Bitrix\Main\UI\Extension::load([
	'ui.entity-editor',
	'main.core',
]);

$guid = $arResult['GUID'];
$prefix = mb_strtolower($guid);
$containerID = "{$prefix}_container";
$buttonContainerID = "{$prefix}_buttons";
$configMenuButtonID = "{$prefix}_config_menu";
$configIconID = "{$prefix}_config_icon";

$htmlEditorConfigs = [];
$htmlFieldNames = isset($arResult['ENTITY_HTML_FIELD_NAMES']) && is_array($arResult['ENTITY_HTML_FIELD_NAMES'])
	? $arResult['ENTITY_HTML_FIELD_NAMES']
	: []
;
$bbFieldNames = isset($arResult['ENTITY_BB_FIELD_NAMES']) && is_array($arResult['ENTITY_BB_FIELD_NAMES'])
	? $arResult['ENTITY_BB_FIELD_NAMES']
	: []
;

$hasBBCodeFields = isset($arResult['HAS_BBCODE_FIELDS']) && $arResult['HAS_BBCODE_FIELDS'] === true;
if ($hasBBCodeFields)
{
	\Bitrix\Main\UI\Extension::load(['ui.text-editor', 'ui.bbcode.formatter.html-formatter']);
}

foreach ($htmlFieldNames as $fieldName)
{
	$fieldPrefix = $prefix.'_'.strtolower($fieldName);
	$htmlEditorConfigs[$fieldName] = [
		'id' => "{$fieldPrefix}_html_editor",
		'containerId' => "{$fieldPrefix}_html_editor_container",
		'bb' => false,
		'controlsMap' => [
			['id' => 'ChangeView', 'compact' => true, 'sort' => 5],
			['id' => 'Bold', 'compact' => true, 'sort' => 10],
			['id' => 'Italic', 'compact' => true, 'sort' => 20],
			['id' => 'Underline', 'compact' => true, 'sort' => 30],
			['id' => 'Strikeout', 'compact' => true, 'sort' => 40],
			['id' => 'RemoveFormat', 'compact' => false, 'sort' => 50],
			['id' => 'Color', 'compact' => false, 'sort' => 60],
			['id' => 'FontSelector', 'compact' => false, 'sort' => 70],
			['id' => 'FontSize', 'compact' => true, 'sort' => 80],
			['separator' => true, 'compact' => false, 'sort' => 90],
			['id' => 'OrderedList', 'compact' => true, 'sort' => 100],
			['id' => 'UnorderedList', 'compact' => true, 'sort' => 110],
			['id' => 'AlignList', 'compact' => false, 'sort' => 120],
			['separator' => true, 'compact' => false, 'sort' => 130],
			['id' => 'InsertLink', 'compact' => true, 'sort' => 140],
			['id' => 'Code', 'compact' => false, 'sort' => 180],
			['id' => 'Quote', 'compact' => false, 'sort' => 190],
			['separator' => true, 'compact' => false, 'sort' => 200],
			['id' => 'Fullscreen', 'compact' => true, 'sort' => 210],
			['id' => 'More', 'compact' => true, 'sort' => 400],
		],
	];
}
foreach ($bbFieldNames as $fieldName)
{
	$fieldPrefix = $prefix.'_'.strtolower($fieldName);
	$htmlEditorConfigs[$fieldName] = [
		'id' => "{$fieldPrefix}_html_editor",
		'containerId' => "{$fieldPrefix}_html_editor_container",
		'bb' => true,
		// only allow tags that are supported in mobile app
		'controlsMap' => [
			['id' => 'ChangeView', 'compact' => true, 'sort' => 5],
			['id' => 'Bold', 'compact' => true, 'sort' => 10],
			['id' => 'Italic', 'compact' => true, 'sort' => 20],
			['id' => 'Underline', 'compact' => true, 'sort' => 30],
			['id' => 'Strikeout', 'compact' => true, 'sort' => 40],
			['id' => 'RemoveFormat', 'compact' => false, 'sort' => 50],
			['separator' => true, 'compact' => false, 'sort' => 90],
			['id' => 'OrderedList', 'compact' => true, 'sort' => 100],
			['id' => 'UnorderedList', 'compact' => true, 'sort' => 110],
			['separator' => true, 'compact' => false, 'sort' => 130],
			['id' => 'InsertLink', 'compact' => true, 'sort' => 140],
			['separator' => true, 'compact' => false, 'sort' => 200],
			['id' => 'Fullscreen', 'compact' => true, 'sort' => 210],
		],
	];
}

if (!empty($htmlEditorConfigs))
{
	foreach ($htmlEditorConfigs as $fieldName => $htmlEditorConfig)
	{
		$fieldInfo = $arResult['ENTITY_AVAILABLE_FIELDS_INFO'][$fieldName] ?? [];
		?>
		<div id="<?=htmlspecialcharsbx($htmlEditorConfig['containerId'])?>" style="display:none;">
			<?php
			$editorControlsMap = $htmlEditorConfig['controlsMap'];
			$parserList = [];

			if (is_array($arResult['DISABLED_HTML_CONTROLS']))
			{
				$editorControls = [];
				foreach ($editorControlsMap as $item)
				{
					$itemId = $item['id'] ?? false;
					$isSeparator = $item['separator'] ?? false;

					if ($itemId)
					{
						$parserList[] = $itemId;

						if (in_array($itemId, $arResult['DISABLED_HTML_CONTROLS'], true))
						{
							continue;
						}
					}

					if (
						$isSeparator
						&& isset($editorControls[array_key_last($editorControls)]['separator'])
					)
					{
						continue;
					}
					$editorControls[] = $item;
				}
			}
			else
			{
				$editorControls = $editorControlsMap;
			}

			$chtmlEditorParams = [
				'name' => $htmlEditorConfig['id'],
				'id' => $htmlEditorConfig['id'],
				'siteId' => SITE_ID,
				'width' => '100%',
				'minBodyWidth' => 230,
				'normalBodyWidth' => $arResult["CHTML_EDITOR_PARAMS"]["normalBodyWidth"] ?? 530,
				'height' => 200,
				'minBodyHeight' => 200,
				'showTaskbars' => false,
				'showNodeNavi' => false,
				'autoResize' => true,
				'autoResizeOffset' => 10,
				'bbCode' => $htmlEditorConfig['bb'],
				'saveOnBlur' => false,
				'bAllowPhp' => false,
				'lazyLoad' => false,
				'limitPhpAccess' => false,
				'setFocusAfterShow' => false,
				'askBeforeUnloadPage' => false,
				'useFileDialogs' => false,
				'controlsMap' => $editorControls,
				'isMentionUnavailable' => $fieldInfo['copilotIntegrationParams']['isMentionUnavailable'] ?? false,
				'isCopilotTextEnabledBySettings' => $fieldInfo['copilotIntegrationParams']['isCopilotTextEnabledBySettings'] ?? true,
				'copilotParams' => $fieldInfo['copilotIntegrationParams']['copilotParams'] ?? [],
			];

			$APPLICATION->IncludeComponent(
				'bitrix:main.post.form',
				'',
				[
					'PARSER' => $parserList,
					'BUTTONS' => $fieldInfo['buttons'] ?? [],
					'UPLOAD_FILE' => false,
					'LHE' => $chtmlEditorParams,
					'isAiImageEnabled' => $fieldInfo['postFormSettings']['isAiImageEnabled'] ?? false,
					'isDnDEnabled' => $fieldInfo['postFormSettings']['isDnDEnabled'] ?? false,
				],
				false,
				[
					"HIDE_ICONS" => "Y",
				]
			);
			?>
		</div>
		<?php
	}
}
?>
<div class="ui-entity-editor-container" id="<?=htmlspecialcharsbx($containerID)?>"></div>
<div class="ui-entity-editor-section-add-btn-container" id="<?=htmlspecialcharsbx($buttonContainerID)?>"></div>
<script>
	BX.ready(
		function()
		{
			BX.UI.EntityEditorField.messages = {
				add: "<?=GetMessageJS('UI_FORM_ENTITY_FIELD_ADD')?>",
				isEmpty: "<?=GetMessageJS('UI_FORM_ENTITY_FIELD_EMPTY')?>"
			};

			var config = BX.UI.EntityConfig.create(
				"<?=CUtil::JSEscape($arResult['CONFIG_ID'])?>",
				{
					data: <?=CUtil::PhpToJSObject($arResult['ENTITY_CONFIG'])?>,
					scope: "<?=CUtil::JSEscape($arResult['ENTITY_CONFIG_SCOPE'])?>",
					enableScopeToggle: <?=$arResult['ENABLE_CONFIG_SCOPE_TOGGLE'] ? 'true' : 'false'?>,
					canUpdatePersonalConfiguration: <?=$arResult['CAN_UPDATE_PERSONAL_CONFIGURATION'] ? 'true' : 'false'?>,
					canUpdateCommonConfiguration: <?=$arResult['CAN_UPDATE_COMMON_CONFIGURATION'] ? 'true' : 'false'?>,
					options: <?=CUtil::PhpToJSObject($arResult['ENTITY_CONFIG_OPTIONS'])?>,
					signedParams: "<?=CUtil::JSEscape($arResult['ENTITY_CONFIG_SIGNED_PARAMS'])?>"
				}
			);

			var userFieldManager = BX.UI.EntityUserFieldManager.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					entityId: <?=$arResult['ENTITY_ID']?>,
					enableCreation: <?=$arResult['ENABLE_USER_FIELD_CREATION'] ? 'true' : 'false'?>,
					enableMandatoryControl: <?=$arResult['ENABLE_USER_FIELD_MANDATORY_CONTROL'] ? 'true' : 'false'?>,
					fieldEntityId: "<?=CUtil::JSEscape($arResult['USER_FIELD_ENTITY_ID'])?>",
					fieldPrefix: "<?=CUtil::JSEscape($arResult['USER_FIELD_PREFIX'])?>",
					creationSignature: "<?=CUtil::JSEscape($arResult['USER_FIELD_CREATE_SIGNATURE'])?>",
					creationPageUrl: "<?=CUtil::JSEscape($arResult['USER_FIELD_CREATE_PAGE_URL'])?>",
					languages: <?=CUtil::PhpToJSObject($arResult['LANGUAGES'])?>
				}
			);

			var scheme = BX.UI.EntityScheme.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					current: <?=CUtil::PhpToJSObject($arResult['ENTITY_SCHEME'])?>,
					available: <?=CUtil::PhpToJSObject($arResult['ENTITY_AVAILABLE_FIELDS'])?>
				}
			);

			var model = BX.UI.EntityEditorModelFactory.create(
				"<?=CUtil::JSEscape($arResult['ENTITY_TYPE_NAME'])?>",
				"",
				{
					isIdentifiable: <?=$arResult['IS_IDENTIFIABLE_ENTITY'] ? 'true' : 'false'?>,
					data: <?=CUtil::PhpToJSObject($arResult['ENTITY_DATA'])?>
				}
			);

			BX.UI.EntityEditor.setDefault(
				BX.UI.EntityEditor.create(
					"<?=CUtil::JSEscape($guid)?>",
					{
						entityTypeName: "<?=CUtil::JSEscape($arResult['ENTITY_TYPE_NAME'])?>",
						entityId: <?=$arResult['ENTITY_ID']?>,
						model: model,
						config: config,
						scheme: scheme,
						validators: <?=CUtil::PhpToJSObject($arResult['ENTITY_VALIDATORS'])?>,
						controllers: <?=CUtil::PhpToJSObject($arResult['ENTITY_CONTROLLERS'])?>,
						detailManagerId: "<?=CUtil::JSEscape($arResult['DETAIL_MANAGER_ID'])?>",
						fieldCreationPageUrl: "<?=CUtil::JSEscape($arResult['FIELD_CREATION_PAGE_URL'] ?? '')?>",
						userFieldManager: userFieldManager,
						initialMode: "<?=CUtil::JSEscape($arResult['INITIAL_MODE'])?>",
						enableModeToggle: <?=$arResult['ENABLE_MODE_TOGGLE'] ? 'true' : 'false'?>,
						enableConfigControl: <?=$arResult['ENABLE_CONFIG_CONTROL'] ? 'true' : 'false'?>,
						enableShowAlwaysFeauture: <?=$arResult['ENABLE_SHOW_ALWAYS_FEAUTURE'] ? 'true' : 'false'?>,
						enableVisibilityPolicy: <?=$arResult['ENABLE_VISIBILITY_POLICY'] ? 'true' : 'false'?>,
						enableToolPanel: <?=$arResult['ENABLE_TOOL_PANEL'] ? 'true' : 'false'?>,
						isToolPanelAlwaysVisible: <?=$arResult['IS_TOOL_PANEL_ALWAYS_VISIBLE'] ? 'true' : 'false'?>,
						enableBottomPanel: <?=$arResult['ENABLE_BOTTOM_PANEL'] ? 'true' : 'false'?>,
						enableFieldsContextMenu: <?=$arResult['ENABLE_FIELDS_CONTEXT_MENU'] ? 'true' : 'false'?>,
						enablePageTitleControls: <?=$arResult['ENABLE_PAGE_TITLE_CONTROLS'] ? 'true' : 'false'?>,
						readOnly: <?=$arResult['READ_ONLY'] ? 'true' : 'false'?>,
						enableAjaxForm: <?=$arResult['ENABLE_AJAX_FORM'] ? 'true' : 'false'?>,
						enableRequiredUserFieldCheck: <?=$arResult['ENABLE_REQUIRED_USER_FIELD_CHECK'] ? 'true' : 'false'?>,
						enableSectionEdit: <?=$arResult['ENABLE_SECTION_EDIT'] ? 'true' : 'false'?>,
						enableSectionCreation: <?=$arResult['ENABLE_SECTION_CREATION'] ? 'true' : 'false'?>,
						enableSectionDragDrop: <?=$arResult['ENABLE_SECTION_DRAG_DROP'] ? 'true' : 'false'?>,
						enableFieldDragDrop: <?=$arResult['ENABLE_FIELD_DRAG_DROP'] ? 'true' : 'false'?>,
						enableSettingsForAll: <?=$arResult['ENABLE_SETTINGS_FOR_ALL'] ? 'true' : 'false'?>,
						containerId: "<?=CUtil::JSEscape($containerID)?>",
						buttonContainerId: "<?=CUtil::JSEscape($buttonContainerID)?>",
						configMenuButtonId: "<?=CUtil::JSEscape($configMenuButtonID)?>",
						configIconId: "<?=CUtil::JSEscape($configIconID)?>",
						htmlEditorConfigs: <?=CUtil::PhpToJSObject($htmlEditorConfigs)?>,
						serviceUrl: "<?=CUtil::JSEscape($arResult['SERVICE_URL'])?>",
						externalContextId: "<?=CUtil::JSEscape($arResult['EXTERNAL_CONTEXT_ID'])?>",
						contextId: "<?=CUtil::JSEscape($arResult['CONTEXT_ID'])?>",
						context: <?=CUtil::PhpToJSObject($arResult['CONTEXT'])?>,
						options: <?=CUtil::PhpToJSObject($arResult['EDITOR_OPTIONS'])?>,
						ajaxData: <?=CUtil::PhpToJSObject($arResult['COMPONENT_AJAX_DATA'])?>,
						customToolPanelButtons: <?=CUtil::PhpToJSObject($arResult['CUSTOM_TOOL_PANEL_BUTTONS'])?>,
						toolPanelButtonsOrder: <?=CUtil::PhpToJSObject($arResult['TOOL_PANEL_BUTTONS_ORDER'])?>,
						isEmbedded: <?=$arResult['IS_EMBEDDED'] ? 'true' : 'false'?>,
						analyticsConfig: <?= CUtil::PhpToJSObject($arResult['ANALYTICS_CONFIG'] ?? []) ?>,
					}
				)
			);
		}
	);
</script>
