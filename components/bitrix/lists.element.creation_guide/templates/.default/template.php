<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")." list-el-cg__slider");

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');

\Bitrix\Main\UI\Extension::load([
	'lists',
	'date',
	'main.date',
	'ui.alerts',
	'ui.buttons',
	'ui.icon-set.main',
	'ui.icon-set.actions',
	'ui.forms',
	'ui.tooltip',
	"ui.dialogs.messagebox",
	'ui.icons.b24',
]);

$htmlId = 'lists-element-creation-guide';

/** @var array $arResult */
$info = $arResult['iBlockInfo'];
/** @var \Bitrix\Lists\UI\Fields\Field[] $fields */
$fields = $arResult['fields'];
$data = $arResult['elementData'];

$isBpEnabled = \Bitrix\Main\Loader::includeModule('bizproc');

$statesOnStartUp = $isBpEnabled ? $arResult['bizproc']['statesOnStartUp'] : [];
$statesToTuning = $isBpEnabled ? $arResult['bizproc']['statesToTuning'] : [];
$canUserTuningStates = $isBpEnabled ? $arResult['bizproc']['canUserTuningStates'] : true;

$canShowFields = !$statesToTuning || $canUserTuningStates;
$hasFieldsToShow = $fields || $statesOnStartUp;

$tabElement = [];
$tabSection = [];
$tabStatesOnStartUp = [];
$templateIds = [];
if ($canShowFields)
{
	if (isset($fields['IBLOCK_SECTION_ID']))
	{
		$sectionField = $fields['IBLOCK_SECTION_ID'];
		$tabSection = [
			'formId' => 'lists_element_creation_guide_section',
			'id' => 'tab_section',
			'name' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SECTION_SECTION_TITLE'),
			'fields' => [[
				'id' => 'IBLOCK_SECTION_ID',
				'name' => $sectionField->getName(),
				'type' => 'list',
				'items' => $sectionField->getProperty()['ENUM_VALUES'],
				'params' => ['size' => 15],
			]],
			'data' => [],
		];

		unset($fields['IBLOCK_SECTION_ID']);
	}

	$elementFields = [];
	foreach ($fields as $field)
	{
		$property = $field->getProperty();
		$property['ELEMENT_ID'] = 0;
		$property['VALUE'] = $data[$field->getId()];
		$property['LIST_ELEMENT_URL'] = ''; // todo
		$property['COPY_ID'] = 0;
		$preparedData = \Bitrix\Lists\Field::prepareFieldDataForEditForm($property);
		if ($preparedData)
		{
			$elementFields[] = $preparedData;
		}
	}
	if ($elementFields)
	{
		$tabElement = [
			'formId' => 'lists_element_creation_guide_element',
			'id' => 'tab_element',
			'name' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_MAIN_SECTION_TITLE'),
			'fields' => $elementFields,
			'data' => $data,
		];
	}

	if ($statesOnStartUp)
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		foreach ($statesOnStartUp as $state)
		{
			$templateIds[$state['templateId']] = true;

			$parameters = [];
			foreach ($state['fields'] as $parameterId => $property)
			{
				$parameterKey = 'bizproc' . $state['templateId'] . '_' . $parameterId;
				$parameters[] = [
					'id' => $parameterKey,
					'name' => $property['Name'],
					'required' => CBPHelper::getBool($property['Required']) === true,
					'type' => 'custom',
					'value' => $documentService->getFieldInputControl(
						$arResult['bizproc']['parameterDocumentType'],
						$property,
						['Field' => $parameterKey],
						$property['Default'] ?? null,
						false,
						true
					),
					'show' => 'Y',
				];
			}

			$tabStatesOnStartUp[] = [
				'id' => 'tab_bp_' . $state['templateId'],
				'name' => $state['name'],
				'formId' => 'lists_element_creation_guide_bp_' . $state['templateId'],
				'fields' => $parameters,
			];
		}
	}
}

$tabStatesToTuning = [];
if ($statesToTuning && $canUserTuningStates)
{
	$documentService = CBPRuntime::getRuntime()->getDocumentService();
	foreach ($statesToTuning as $state)
	{
		$templateId = $state['templateId'];
		$templateIds[$templateId] = true;

		$constants = [];
		foreach ($state['fields'] as $constantId => $property)
		{
			$constantKey = 'bizproc' . $templateId . '_' . $constantId;
			$constants[] = [
				'id' => $constantKey,
				'name' => $property['Name'],
				'required' => CBPHelper::getBool($property['Required']) === true,
				'type' => 'custom',
				'value' => $documentService->getFieldInputControl(
					$arResult['bizproc']['parameterDocumentType'],
					$property,
					['Field' => $constantKey],
					$property['Default'] ?? null,
					false,
					true
				),
				'show' => 'Y',
			];
		}

		$tabStatesToTuning[] = [
			'id' => 'tab_bp_constants_' . $templateId,
			'name' => $state['name'],
			'formId' => 'lists_element_creation_guide_bp_constants_' . $templateId,
			'fields' => $constants,
			'templateId' => $templateId,
		];
	}
}

$includeFormComponent = static function(array $tab) {
	global $APPLICATION;

	$APPLICATION->IncludeComponent(
	'bitrix:main.interface.form',
	'',
	[
		'FORM_ID' => $tab['formId'],
		'TABS' => [[
			'id' => $tab['id'],
			'name' => htmlspecialcharsbx($tab['name']),
			'fields' => $tab['fields'] ?? [],
		]],
		'DATA' => $tab['data'] ?? [],
		'SHOW_SETTINGS' => false,
	]
	);
};
?>

<div class="list-el-cg">
	<div class="list-el-cg__header">
		<div class="list-el-cg__header-icon">
			<div class="ui-icon-set --bp" style="--ui-icon-set__icon-size: 48px; --ui-icon-set__icon-color: #fff;"></div>
		</div>
		<div class="list-el-cg__header-content">
			<div class="list-el-cg__header__title"><?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_TITLE')) ?></div>
			<div class="list-el-cg__header__info"><?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_DESCRIPTION')) ?></div>
		</div>
	</div>
	<div class="list-el-cg__body">
		<div class="list-el-cg__breadcrumbs" id="<?= htmlspecialcharsbx($htmlId . '-breadcrumbs') ?>"></div>
		<div class="list-el-cg__container" id="<?= htmlspecialcharsbx($htmlId . '-container') ?>">
			<div class="list-el-cg__content">
				<div class="list-el-cg__content-head">
					<div class="list-el-cg__content-title"><?= htmlspecialcharsbx($info['name']) ?></div>
					<div class="list-el-cg__content-config" data-role="list-el-cg__content-config" style="display: none">
						<div class="ui-icon-set --settings-4"></div>
					</div>
				</div>
				<div id="<?= htmlspecialcharsbx($htmlId . '-errors') ?>"></div>
				<div class="list-el-cg__content-body --border"></div>
				<div class="list-el-cg__content-body --border --hidden">
					<?php if ($statesToTuning): ?>
						<?php if ($canUserTuningStates): ?>
							<?php foreach ($tabStatesToTuning as $tab): ?>
								<div class="list-el-cg__content-form">
									<div class="list-el-cg__content-form-title"><?= htmlspecialcharsbx($tab['name']) ?></div>
									<?php $includeFormComponent($tab) ?>
									<div>
										<div id="<?= htmlspecialcharsbx($htmlId . '-constants-' . $tab['templateId'] . '-errors')?>"></div>
										<button
											class="ui-btn ui-btn-xs ui-btn-secondary"
											onclick="BX.Lists.Component.ElementCreationGuide.Instance.saveConstants(<?= (int)$tab['templateId'] ?>, this)"
										>
											<?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_BUTTON_SAVE')) ?>
										</button>
									</div>
								</div>
							<?php endforeach ?>
						<?php else: ?>
							<div class="ui-alert ui-alert-warning ui-alert-icon-info">
								<span class="ui-alert-message"><?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS')) ?></span>
							</div>
						<?php endif ?>
					<?php endif ?>
				</div>
				<div class="list-el-cg__content-body --border --hidden">
					<?php if ($tabElement): ?>
						<div class="list-el-cg__content-form">
							<div class="list-el-cg__content-form-title"><?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_MAIN_SECTION_TITLE')) ?></div>
							<?php $includeFormComponent($tabElement) ?>
						</div>
					<?php endif; ?>
					<?php if ($tabSection): ?>
						<div class="list-el-cg__content-form">
							<div class="list-el-cg__content-form-title"><?= htmlspecialcharsbx(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SECTION_SECTION_TITLE')) ?></div>
							<?php $includeFormComponent($tabSection) ?>
						</div>
					<?php endif ?>
					<?php foreach ($tabStatesOnStartUp as $tab): ?>
						<div class="list-el-cg__content-form">
							<div class="list-el-cg__content-form-title"><?= htmlspecialcharsbx($tab['name']) ?></div>
							<?php $includeFormComponent($tab); ?>
						</div>
					<?php endforeach ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		'ID' => $htmlId . '-buttons',
		'STICKY_CONTAINER' => '#' . $htmlId . '-sticky-buttons',
		'BUTTONS' => [
			[
				'ID' => $htmlId . '-back-button',
				'TYPE' => 'button',
				'CAPTION' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_BUTTON_BACK'),
				'ONCLICK' => 'BX.Lists.Component.ElementCreationGuide.Instance.back()',
			],
			[
				'ID' => $htmlId . '-next-button',
				'TYPE' => 'apply',
				'CAPTION' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_BUTTON_NEXT'),
				'ONCLICK' => 'BX.Lists.Component.ElementCreationGuide.Instance.next()',
			],
			[
				'ID' => $htmlId . '-create-button',
				'TYPE' => 'apply',
				'CAPTION' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_BUTTON_CREATE'),
				'ONCLICK' => 'BX.Lists.Component.ElementCreationGuide.Instance.create()',
			],
		],
	],
) ?>
<div id="<?= $htmlId . '-sticky-buttons' ?>"></div>
<div class="list-el-cg-background"></div>

<script>
	BX.Event.ready(() => {
		BX.message(<?= Json::encode(Loc::loadLanguageFile(__FILE__)) ?>);

		BX.Lists.Component.ElementCreationGuide.Instance = new BX.Lists.Component.ElementCreationGuide(
			<?= Json::encode([
				'name' => trim((string)$info['name']),
				'description' => trim((string)$info['description']),
				'duration' => $arResult['bizproc']['averageDuration'] ?? null,
				'signedParameters' => $arResult['signedParameters'],
				'bpTemplateIds' => array_keys($templateIds),
				'hasFieldsToShow' => $hasFieldsToShow,
				'hasStatesToTuning' => (bool)$statesToTuning,
				'canUserTuningStates' => $canUserTuningStates,
			]) ?>
		);

		document.querySelectorAll('.bx-edit-tabs').forEach((element) => {
			BX.Dom.remove(element);
		})
		document.querySelectorAll('.bx-form-notes').forEach((element) => {
			BX.Dom.remove(element);
		});
	});
</script>
