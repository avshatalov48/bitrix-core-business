<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateSettingsTable;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateUserOptionTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Type\DateTime;

class BizprocWorkflowStartList extends CBitrixComponent implements Errorable, Controllerable
{
	private string $gridId = 'bizproc_workflow_start_list';
	private string $filterId = 'bizproc_workflow_start_list_filter';

	private ErrorCollection $errorCollection;

	private ?int $categoryId;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	public static function pinAction(int $templateId, CurrentUser $user): bool
	{
		if (!$templateId)
		{
			return false;
		}

		$userId = $user->getId();
		if (!$userId)
		{
			return false;
		}

		$result = WorkflowTemplateUserOptionTable::addOption($templateId, $userId, WorkflowTemplateUserOptionTable::PINNED);

		return $result->isSuccess();
	}

	public static function unpinAction(int $templateId, CurrentUser $user): bool
	{
		if (!$templateId)
		{
			return false;
		}

		$userId = $user->getId();
		if (!$userId)
		{
			return false;
		}

		$result = WorkflowTemplateUserOptionTable::deleteOption($templateId, $userId, WorkflowTemplateUserOptionTable::PINNED);

		return $result->isSuccess();
	}

	public function addErrors(array $errors): self
	{
		$this->errorCollection->add($errors);

		return $this;
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function setError(Error $error): self
	{
		$this->errorCollection->setError($error);

		return $this;
	}

	public function hasErrors(): bool
	{
		return !$this->errorCollection->isEmpty();
	}

	public function configureActions(): array
	{
		return [];
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['signedDocumentType'] = htmlspecialcharsback($arParams['signedDocumentType'] ?? '');
		$arParams['signedDocumentId'] = htmlspecialcharsback($arParams['signedDocumentId'] ?? '');

		return $arParams;
	}

	public function executeComponent()
	{
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_TITLE'));

		$this->init();

		if ($this->hasErrors())
		{
			return $this->includeComponentTemplate('error');
		}

		$this->fillGridInfo();
		$this->fillGridData();

		$this->arResult['canEdit'] = $this->checkRightsEdit();
		$this->arResult['bizprocEditorUrl'] = CBPDocumentService::getBizprocEditorUrl($this->getComplexDocumentType()) ?? '';

		$this->arResult['signedDocumentType'] = CBPDocument::signDocumentType($this->getComplexDocumentType());
		$this->arResult['signedDocumentId'] = CBPDocument::signDocumentType($this->getComplexDocumentId());

		$this->addToolbar();

		return $this->includeComponentTemplate();
	}

	private function init(): void
	{
		$this->checkModules();

		if (!$this->hasErrors())
		{
			$unsignedDocumentType = $this->getComplexDocumentType();
			$unsignedDocumentId = $this->getComplexDocumentId();
			$this->initDocument($unsignedDocumentType ?? [], $unsignedDocumentId ?? []);
		}

		if (!$this->hasErrors())
		{
			$this->checkRights();
		}
	}

	private function checkModules(): void
	{
		if (!Loader::includeModule('bizproc'))
		{
			$errorMsg = Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_MODULE_ERROR', ['#MODULE#' => 'BizProc']);
			$this->setError(new Error($errorMsg));
		}
	}

	private function initDocument(array $documentType, array $documentId): void
	{
		try
		{
			$this->arResult['documentType'] = CBPHelper::ParseDocumentId($documentType);
			$this->arResult['documentId'] = CBPHelper::ParseDocumentId($documentId);

			[$moduleId, $entity, $documentType] = $this->arResult['documentType'];
			$this->gridId = 'bizproc_workflow_start_list_' . $documentType;
			$this->filterId = 'bizproc_workflow_start_list_filter_' . $documentType;

			[$moduleId] = $this->arResult['documentId'];

			if ($moduleId === 'crm')
			{
				$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
				$this->categoryId = $documentService->getDocumentCategoryId($this->arResult['documentId']);
			}
		}
		catch (CBPArgumentException $argumentException)
		{
			$this->setError(new Error(Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_DOCUMENT_TYPE_ERROR')));
		}
	}

	private function checkRights(): void
	{
		$permission = CBPDocument::canUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$this->getCurrentUserId(),
			$this->arResult['documentId'],
		);

		if (!$permission)
		{
			$this->setError(new Error(Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_RIGHTS_ERROR')));
		}
	}

	private function checkRightsEdit(): bool
	{
		return CBPDocument::canUserOperateDocument(
			CBPCanUserOperateOperation::CreateWorkflow,
			$this->getCurrentUserId(),
			$this->arResult['documentId'],
		);
	}

	private function getCurrentUserId(): int
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->getId();
	}

	private function fillGridInfo(): void
	{
		$this->arResult['gridId'] = $this->gridId;
		$this->arResult['gridColumns'] = $this->getGridColumns();
		$this->arResult['pageNavigation'] = $this->getPageNavigation();
	}

	private function getGridColumns(): array
	{
		return [
			[
				'id' => 'PIN',
				'name' => '',
				'default' => true,
				'class' => 'bizproc-workflow-start-list-grid-header-pin',
				'resizeable' => false,
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_NAME'),
				'default' => true,
			],
			[
				'id' => 'START',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_START'),
				'default' => true,
			],
			[
				'id' => 'IN_PROGRESS',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_IN_PROGRESS'),
				'default' => true,
			],
			[
				'id' => 'LAST_ACTION',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_LAST_ACTION'),
				'default' => true,
			],
		];
	}

	private function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$options = new \Bitrix\Main\Grid\Options($this->gridId);
		$navParams = $options->GetNavParams();

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->gridId);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		return $pageNavigation;
	}

	private function fillGridData()
	{
		$defaultFilter = [
			'=MODULE_ID' => $this->arResult['documentType'][0],
			'=ENTITY' => $this->arResult['documentType'][1],
			'=DOCUMENT_TYPE' => $this->arResult['documentType'][2],
			'=ACTIVE' => 'Y',
			'=IS_SYSTEM' => 'N',
			'<AUTO_EXECUTE' => CBPDocumentEventType::Automation,
		];
		$filter = array_merge($this->getUserFilter(), $defaultFilter);

		$order = array_merge(['PIN.ID' => 'DESC'], $this->getGridOrder());

		$result = \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::query()
			->setSelect(['ID', 'NAME', 'DESCRIPTION'])
			->setFilter($filter)
		;

		$result->registerRuntimeField(
			'PIN',
			new Reference(
				'PIN',
				WorkflowTemplateUserOptionTable::class,
				Join::on('this.ID', 'ref.TEMPLATE_ID')
					->where('ref.OPTION_CODE', WorkflowTemplateUserOptionTable::PINNED)
					->where('ref.USER_ID', $this->getCurrentUserId()),
				['join_type' => 'LEFT']
			)
		);

		$result->setOrder($order);

		if (isset($this->categoryId))
		{
			$result->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField(
				'SETTINGS',
				WorkflowTemplateSettingsTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.TEMPLATE_ID')
					->where('ref.NAME', WorkflowTemplateSettingsTable::SHOW_CATEGORY_PREFIX . $this->categoryId)
					->whereIn('ref.VALUE', ['N', null])
				,
				['join_type' => 'LEFT']
			));
		}

		$resultCollection = $result->fetchCollection();
		$jsHandlerStart = "BX.Bizproc.Component.WorkflowStartList.Instance.startWorkflow(event, '%s');";

		$gridData = [];
		foreach ($resultCollection as $template)
		{
			$instancesView = new \Bitrix\Bizproc\UI\WorkflowTemplateInstancesView($template->getId());
			$gridData[] = [
				'id' => $template->getId(),
				'columns' => [
					'PIN' => '',
					'NAME' => $this->renderTemplateName($template, sprintf($jsHandlerStart, $template->getId())),
					'START' => $this->renderStartButton(sprintf($jsHandlerStart, $template->getId()), $template->getId()),
					'IN_PROGRESS' => $this->renderInProgress($instancesView),
					'LAST_ACTION' => \CBPViewHelper::formatDateTime($instancesView->getLastActivity()),
				],
				'data' => [
					'NAME' => $template->getName(),
				],
				'actions' => [
					[
						'text' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_ROW_ACTION_EDIT'),
						'onclick' => "BX.Bizproc.Component.WorkflowStartList.Instance.editTemplate(event, {$template->getId()});",
					],
				],
				'cellActions' => [
					'PIN' => $this->getPinAction($template),
				],
			];
		}

		$this->arResult['gridData'] = $gridData;
	}

	private function getPinAction($template): array
	{
		$actionClass = [
			\Bitrix\Main\Grid\CellActions::PIN,
		];
		$pin = $template->sysGetRuntime('PIN');
		if ($pin)
		{
			$actionClass[] = \Bitrix\Main\Grid\CellActionState::ACTIVE;
		}
		$gridId = $this->gridId;

		return [
			[
				'class' => $actionClass,
				'events' => [
					'click' => "BX.Bizproc.Component.WorkflowStartList.changePin.bind(BX.Bizproc.Component.WorkflowStartList, {$template->getId()}, '$gridId')",
				],
			],
		];
	}

	private function renderTemplateName(\Bitrix\Bizproc\Workflow\Template\Tpl $template, $handler): string
	{
		static $workflowStateService;
		$workflowStateService ??= new WorkflowStateService();

		$description = trim((string)$template->getDescription()); // description can be null
		if ($description === '')
		{
			$description = Loc::getMessage('BIZPROC_WORKFLOW_START_EMPTY_DESCRIPTION');
		}

		$templateDescriptionElement = sprintf(
			'<span data-hint="%s" class="ui-hint"></span>',
			htmlspecialcharsbx($description)
		);

		$templateNameElement = sprintf(
			'<a class="ui-btn-link" onclick="%s" href="#">%s</a>',
			htmlspecialcharsbx($handler),
			htmlspecialcharsbx($template->getName()) . $templateDescriptionElement
		);

		$duration = $workflowStateService->getAverageWorkflowDuration(
			new GetAverageWorkflowDurationRequest($template->getId())
		)->getRoundedAverageDuration();

		$durationText = $duration !== null
			? \Bitrix\Bizproc\UI\Helpers\DurationFormatter::format($duration)
			: Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_NO_DATA')
		;

		$averageTimeElement = sprintf(
			'<div class="%s">%s</div>',
			'bizproc-workflow-start-list-grid-template-average-time',
			Loc::getMessage(
				'BIZPROC_WORKFLOW_START_LIST_AVERAGE_WAITING_TIME',
				[
					'#TIME#' => '<b>' . $durationText . '</b>'
				]
			),
		);

		return sprintf(
			'<div class="bizproc-workflow-start-list-grid-template-name-wrapper">%s</div>',
			$templateNameElement . $averageTimeElement,
		);
	}

	private function renderStartButton(string $handler, int $templateId): string
	{
		return sprintf(
			'
				<div class="bizproc-workflow-start-list-column-start">
					<button
						class="ui-btn ui-btn-success ui-btn-round ui-btn-xs ui-btn ui-btn-no-caps"
						onclick="%s"
					>%s</button>
					<div
						class="bizproc-workflow-start-list-column-start-counter-wrapper"
						data-role="template-%s-counter"
					></div>
				</div>
			',
			htmlspecialcharsbx($handler),
			Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_START_BUTTON'),
			$templateId,
		);
	}

	private function renderInProgress(\Bitrix\Bizproc\UI\WorkflowTemplateInstancesView $view): string
	{
		$viewParam = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($view));

		return <<<HTML
			<div data-role="wt-progress-{$view->getTplId()}" data-widget="{$viewParam}">
				<script>
					BX.ready(() => {
						BX.Bizproc.Workflow.Instances.Widget.renderTo(
							document.querySelector('[data-role="wt-progress-{$view->getTplId()}"]')
						)
					})
				</script>
			</div>
		HTML;
	}

	private function getGridOrder(): array
	{
		return ['SORT' => 'ASC'];
	}

	private function getUserFilter(): array
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId);
		$fields = $filterOptions->getFilter($this->getFilterFields());
		$filter = [];

		if (isset($this->categoryId))
		{
			if (empty($fields) && $filterOptions->getCurrentFilterId() === 'default_filter')
			{
				$fields['SYSTEM_PRESET'] = 'show_in_starter';
			}

			if (isset($fields['SYSTEM_PRESET']))
			{
				$filter['!=SETTINGS.VALUE'] = 'N';
			}
		}

		if (isset($fields['NAME']))
		{
			$filter['%NAME'] = $fields['NAME'];
		}

		if ($filterOptions->getSearchString())
		{
			$filter[] = ['%NAME' => $filterOptions->getSearchString()];
		}

		return $filter;
	}

	private function addToolbar(): void
	{
		$filterParams = [
			'FILTER_ID' => $this->filterId,
			'GRID_ID' => $this->gridId,
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::LIGHT,
		];

		$createButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_ADD_TEMPLATE_BUTTON'),
			'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
			'className' => 'ui-btn-no-caps',
			'click' => new \Bitrix\UI\Buttons\JsCode(
			"BX.Bizproc.Component.WorkflowStartList.Instance.editTemplate(event, 0)"
			),
		]);

		$feedbackParams = \Bitrix\Main\Web\Json::encode($this->getFeedbackParams());
		$feedbackButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_FEEDBACK_BUTTON'),
			'size'  => \Bitrix\UI\Buttons\Size::MEDIUM,
			'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
			'click' => new \Bitrix\UI\Buttons\JsCode(
				"BX.UI.Feedback.Form.open({$feedbackParams});"
			),
		]);

		Toolbar::addFilter($filterParams);
		Toolbar::addButton($createButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
		Toolbar::addButton($feedbackButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_FILTER);
		Toolbar::deleteFavoriteStar();
	}

	private function getFeedbackParams(): array
	{
		return [
			'id' => 'bizproc-workflow-start',
			'forms' => [
				[
					'zones' => ['ru', 'by', 'kz'],
					'id' => 786,
					'lang' => 'ru',
					'sec' => 'ys36he',
				],
				[
					'zones' => ['com.br'],
					'id' => 788,
					'lang' => 'br',
					'sec' => 'bdooui',
				],
				[
					'zones' => ['es'],
					'id' => 790,
					'lang' => 'la',
					'sec' => 'ofv5ky',
				],
				[
					'zones' => ['de'],
					'id' => 792,
					'lang' => 'de',
					'sec' => 'sepygg',
				],
				[
					'zones' => ['en'],
					'id' => 794,
					'lang' => 'en',
					'sec' => '32uhqp',
				],
			],
			'presets' => [],
		];
	}

	private function getFilterFields(): array
	{
		$filterFields = [
			'NAME' => [
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_GRID_COLUMN_NAME'),
				'type' => 'string',
				'default' => true,
			],
		];

		if (isset($this->categoryId))
		{
			$filterFields['SYSTEM_PRESET'] = [
				'id' => 'SYSTEM_PRESET',
				'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_FILTER_FIELD_SYSTEM_PRESET') ?? '',
				'type' => 'list',
				'items' => [
					'' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_SYSTEM_PRESET_ITEM') ?? '',
					'show_in_starter' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_SYSTEM_PRESET_NAME') ?? '',
				],
			];
		}

		return $filterFields;
	}

	private function getFilterPresets(): array
	{
		if (isset($this->categoryId))
		{
			return [
				'show_in_starter' => [
					'name' => Loc::getMessage('BIZPROC_WORKFLOW_START_LIST_SYSTEM_PRESET_NAME') ?? '',
					'fields' => ['SYSTEM_PRESET' => 'show_in_starter'],
					'default' => true,
				],
			];
		}

		return [];
	}

	private function getComplexDocumentType(): ?array
	{
		return CBPDocument::unSignDocumentType($this->arParams['~signedDocumentType']);
	}

	private function getComplexDocumentId(): ?array
	{
		return CBPDocument::unSignDocumentType($this->arParams['~signedDocumentId']);
	}
}
