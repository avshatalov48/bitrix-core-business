<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\DocumentType\DocumentTypeUserOptionTable;
use Bitrix\Bizproc\UI\Helpers\DurationFormatter;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;
use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Lists\Api\Service\ServiceFactory\ProcessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\Uri;

class BizprocUserProcessesStart
	extends CBitrixComponent
	implements \Bitrix\Main\Errorable, \Bitrix\Main\Engine\Contract\Controllerable
{
	private string $gridId = 'bizproc_user_processes_start';
	private string $filterId = 'bizproc_user_processes_start_filter';
	protected const NAVIGATION_ID = 'page';
	private const TITLE_WIDTH = 360;

	protected array $listIBlockBpTemplates = [];
	protected array $filterPresets = [];

	private ErrorCollection $errorCollection;
	private static array $usersData = [];

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	public static function pinAction(int $iblockId, CurrentUser $user): bool
	{
		if (!$iblockId)
		{
			return false;
		}

		$userId = (int)$user->getId();
		if (!$userId)
		{
			return false;
		}

		$documentType = self::getDocumentType($iblockId);
		if ($documentType === null)
		{
			return false;
		}

		$result = DocumentTypeUserOptionTable::addOption($documentType, $userId, DocumentTypeUserOptionTable::PINNED);

		return $result->isSuccess();
	}

	public static function unpinAction(int $iblockId, CurrentUser $user): bool
	{
		if (!$iblockId)
		{
			return false;
		}

		$userId = (int)$user->getId();
		if (!$userId)
		{
			return false;
		}

		$documentType = self::getDocumentType($iblockId);
		if ($documentType === null)
		{
			return false;
		}

		$result = DocumentTypeUserOptionTable::deleteOption($documentType, $userId, DocumentTypeUserOptionTable::PINNED);

		return $result->isSuccess();
	}

	protected static function getDocumentType(int $iblockId): ?array
	{
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			return null;
		}
		$iBlockTypeId = ProcessService::getIBlockTypeId();

		return BizprocDocument::generateDocumentComplexType($iBlockTypeId, $iblockId);
	}

	public function configureActions(): array
	{
		return [];
	}

	public function addErrors(array $errors): static
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

	public function setError(Error $error): static
	{
		$this->errorCollection->setError($error);

		return $this;
	}

	public function hasErrors(): bool
	{
		return !$this->errorCollection->isEmpty();
	}

	public function onPrepareComponentParams($params): array
	{
		$params['isShowLiveFeed'] = $this->request->get('LIVEFEED_PRESET') ? 'Y' : 'N';

		return $params;
	}

	public function executeComponent(): void
	{
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('BIZPROC_USER_PROCESSES_START_TITLE'));

		$this->init();
		$this->fillGridInfo();

		if (!$this->hasErrors())
		{
			$this->addToolbar();
			$this->fillGridData();

			$this->includeComponentTemplate();

			return;
		}

		$this->includeComponentTemplate('error');
	}

	private function init(): void
	{
		$this->gridId .= $this->arParams['isShowLiveFeed'] === 'Y' ? '_livefeed' : '';
		$this->filterId .= $this->arParams['isShowLiveFeed'] === 'Y' ? '_livefeed' : '';
		$this->checkModules();
		$this->arResult['gridData'] = [];
	}

	private function checkModules(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			$errorMessage = Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ERROR', ['#MODULE#' => 'bizproc']);
			$this->setError(new Error($errorMessage));
		}
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			$errorMessage = Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ERROR', ['#MODULE#' => 'lists']);
			$this->setError(new Error($errorMessage));
		}
	}

	private function fillGridInfo(): void
	{
		$this->arResult['gridId'] = $this->gridId;
		$this->arResult['filterId'] = $this->filterId;
		$this->arResult['navigationId'] = static::NAVIGATION_ID;
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
				'class' => 'bizproc-user-processes-start-grid-header-pin',
				'resizeable' => false,
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_DOCUMENT_NAME'),
				'default' => true,
				'width' => 400,
			],
			[
				'id' => 'START',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_ACTION'),
				'default' => true,
			],
			[
				'id' => 'IN_PROGRESS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_IN_PROGRESS'),
				'default' => true,
			],
			[
				'id' => 'LAST_ACTION',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_LAST_ACTION'),
				'default' => true,
			],
			[
				'id' => 'MODIFIED_BY',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_MODIFIED_BY'),
				'default' => true,
			],
		];
	}

	private function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$options = new \Bitrix\Main\Grid\Options($this->gridId);
		$navParams = $options->GetNavParams();

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation(static::NAVIGATION_ID);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		$currentPage = $this->request->getQuery(static::NAVIGATION_ID);
		if (is_numeric($currentPage))
		{
			$pageNavigation->setCurrentPage((int)$currentPage);
		}

		return $pageNavigation;
	}

	private function fillGridData()
	{
		$currentUserId = $this->getCurrentUserId();
		$iBlockTypeId = ProcessService::getIBlockTypeId();

		$service = ServiceFactory::getServiceByIBlockTypeId($iBlockTypeId, $currentUserId);
		if (!$service)
		{
			return;
		}

		$checkPermissionResult = $service->checkIBlockTypePermission();
		$lists_perm = $checkPermissionResult->getPermission();

		if (!$checkPermissionResult->isSuccess())
		{
			return;
		}

		if ($lists_perm <= CListPermissions::ACCESS_DENIED)
		{
			return;
		}

		$gridData = [];
		$filter = $this->getUserFilter();

		$getCatalogResult = $service->getFilteredCatalog($filter);
		$jsHandlerStartTemplate = "BX.Bizproc.Component.UserProcessesStart.Instance.startWorkflow(event, '%s', %d, '%s');";

		if ($getCatalogResult->isSuccess())
		{

			$iblocks = $this->getOrderedCatalog($this->addPins($getCatalogResult->getCatalog()));
			$this->warmUpCaches($iblocks);

			foreach ($iblocks as $iBlock)
			{
				$jsHandlerStart = sprintf($jsHandlerStartTemplate, $iBlockTypeId, $iBlock['ID'], CUtil::JSEscape($iBlock['NAME']));
				if (isset($this->filterPresets['LIVEFEED_PRESET']) && !\CLists::getLiveFeed($iBlock['ID']))
				{
					continue;
				}

				$data = [
					'id' => $iBlock['ID'],
					'columns' => [
						'PIN' => '',
						'NAME' => $this->renderIBlockName($service, $iBlock, $jsHandlerStart),
						'START' => $this->renderStartButton($jsHandlerStart, $iBlock['ID']),
						'IN_PROGRESS' => $this->renderInProgress($iBlock['ID']),
						'LAST_ACTION' => \CBPViewHelper::formatDateTime($this->getLastActivity((int)$iBlock['ID'])),
						'MODIFIED_BY' => $this->renderModifiedBy($iBlock['ID']),
					],
					'data' => [
						'NAME' => $iBlock['NAME'],
					],
					'actions' => [
						[
							'TEXT' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_ROW_ACTION_EDIT'),
							'MENU' => $this->getEditActions($iBlock['ID']),
						],
					],
					'cellActions' => [
						'PIN' => $this->getPinAction($iBlock['ID'], $iBlock['PIN'] ?? false),
					],
				];

				$constantActions = $this->getEditConstantsActions($iBlock['ID']);
				if ($constantActions)
				{
					$data['actions'][] =[
						'TEXT' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_ROW_ACTION_EDIT_CONSTANTS'),
						'MENU' => $constantActions,
					];
				}


				$gridData[] = $data;
			}
		}

		$this->arResult['gridData'] = $gridData;
	}

	private function renderModifiedBy(int $iblockId): string
	{
		$templates = $this->getIBlockBpTemplates($iblockId);
		if (empty($templates))
		{
			return '';
		}

		$template = array_shift($templates);
		$workflowTemplate = WorkflowTemplateTable::getById($template['id'])->fetchObject();
		if (!$workflowTemplate)
		{
			return '';
		}

		if (isset(self::$usersData[$workflowTemplate->getUserId()]))
		{
			$userData = self::$usersData[$workflowTemplate->getUserId()];

			$emptyAvatar = (empty($userData['avatar']) ? 'empty' : '');
			$avatarStyle = (empty($userData['avatar']) ? '' : ' style="background-image: url(\''. Uri::urnEncode($userData['avatar']).'\')"');
			$fullName = htmlspecialcharsbx($userData['full_name']);

			return
				'<div class="bizproc-user-processes-start-grid-username-wrapper">'
				. '<a class="bizproc-user-processes-start-grid-username">'
				. "<span class=\"ui-icon ui-icon-common-user bizproc-user-processes-start-grid-avatar {$emptyAvatar}\"><i $avatarStyle></i></span>"
				. "<span bx-tooltip-user-id=\"{$userData['id']}\" class=\"bizproc-user-processes-start-grid-username-inner\">$fullName</span>"
				. '</a>'
				. '</div>'
				;
		}

		return '';
	}

	private function warmUpCaches(array $iblocks): void
	{
		$userIds = [];
		foreach ($iblocks as $iblock)
		{
			$templates = $this->getIBlockBpTemplates($iblock["ID"]);
			if (!empty($templates))
			{
				$template = array_shift($templates);
				$workflowTemplate = WorkflowTemplateTable::getById($template['id'])->fetchObject();
				if ($workflowTemplate)
				{
					$userIds[] = $workflowTemplate->getUserId();
				}
			}
		}

		$userService = new UserService();
		$response = $userService->getUsersView(new UsersToGet($userIds));

		if ($response->isSuccess())
		{
			$userViews = $response->getUserViews();
			foreach ($userViews as $userView)
			{
				$userData = [
					'id' => $userView->getUserId(),
					'full_name' => $userView->getFullName(),
					'avatar' => $userView->getUserAvatar(),
				];
				if (!isset(self::$usersData[$userView->getUserId()]))
				{
					self::$usersData[$userView->getUserId()] = $userData;
				}
			}
		}
	}

	private function renderInProgress(int $iblockId): string
	{
		$templates = $this->getIBlockBpTemplates($iblockId);
		if (empty($templates))
		{
			return '';
		}

		$template = array_shift($templates);

		$instancesView = new \Bitrix\Bizproc\UI\WorkflowTemplateInstancesView($template['id'], true);
		$viewParam = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($instancesView));

		return <<<HTML
			<div data-role="wt-progress-{$instancesView->getTplId()}" data-widget="{$viewParam}">
				<script>
					BX.ready(() => {
						BX.Bizproc.Workflow.Instances.Widget.renderTo(
							document.querySelector('[data-role="wt-progress-{$instancesView->getTplId()}"]')
						)
					})
				</script>
			</div>
		HTML;
	}

	private function getLastActivity(int $iblockId): ?\Bitrix\Main\Type\DateTime
	{
		$templates = $this->getIBlockBpTemplates($iblockId);
		if (empty($templates))
		{
			return null;
		}

		$lastWorkflow = WorkflowStateTable::getList([
			'filter' => [
				'@WORKFLOW_TEMPLATE_ID' => array_column($templates, 'id'),
			],
			'select' => ['STARTED'],
			'limit' => 1,
			'order' => ['STARTED' => 'DESC'],
		])->fetch();

		if ($lastWorkflow)
		{
			return $lastWorkflow['STARTED'];
		}

		return null;
	}

	private function addPins(array $iblocks): array
	{
		$iblockTypeId = ProcessService::getIBlockTypeId();
		$documentComplexTypeTree = [];

		foreach ($iblocks as $iblock)
		{
			$documentComplexType = BizprocDocument::generateDocumentComplexType($iblockTypeId, $iblock['ID']);
			$documentComplexTypeTree[$documentComplexType[0]][$documentComplexType[1]][] = $documentComplexType[2];
		}

		$pinned = [];
		foreach ($documentComplexTypeTree as $moduleId => $entities)
		{
			foreach ($entities as $entity => $types)
			{
				$documentTypes = DocumentTypeUserOptionTable::getList([
					'select' => ['DOCUMENT_TYPE'],
					'filter' => [
						'=MODULE_ID' => $moduleId,
						'=ENTITY' => $entity,
						'=USER_ID' => $this->getCurrentUserId(),
						'=OPTION_CODE' => DocumentTypeUserOptionTable::PINNED,
					],
				])->fetchAll();
				$pinned[$moduleId][$entity] = array_column($documentTypes, 'DOCUMENT_TYPE');
			}
		}

		foreach ($iblocks as &$iblock)
		{
			$documentComplexType = BizprocDocument::generateDocumentComplexType($iblockTypeId, $iblock['ID']);
			$iblock['PIN'] =
				isset($pinned[$documentComplexType[0]][$documentComplexType[1]])
				&& (in_array($documentComplexType[2], $pinned[$documentComplexType[0]][$documentComplexType[1]], true));
		}

		return $iblocks;
	}

	private function getOrderedCatalog(array $catalog): array
	{
		usort($catalog,
			fn($a, $b) => $a['SORT'] <=> $b['SORT']
		);

		usort($catalog,
			fn($a, $b) => $b['PIN'] <=> $a['PIN']
		);

		return $catalog;
	}

	private function getUserFilter(): IBlockListFilter
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId);
		$this->filterPresets = $filterOptions->getFilter($this->getFilterFields());
		$filter = new IBlockListFilter();

		if (isset($this->filterPresets['NAME']))
		{
			$filter->setField('NAME', $this->filterPresets['NAME'], '%');
		}

		if ($filterOptions->getSearchString())
		{
			$filter->setField('NAME', $filterOptions->getSearchString(), '%');
		}

		return $filter;
	}

	private function getFilterFields(): array
	{
		$filterFields = [
			'NAME' => [
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_DOCUMENT_NAME'),
				'type' => 'string',
				'default' => true,
			],
		];

		if ($this->arParams['isShowLiveFeed'] === 'Y')
		{
			$filterFields['LIVEFEED_PRESET'] = [
				'id' => 'LIVEFEED_PRESET',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_LIVEFEED_PRESET_NAME'),
				'type' => 'list',
				'items' => [
					'' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_LIVEFEED_PRESET_ALL'),
					'show_livefeed' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_LIVEFEED_PRESET_YES'),
				],
			];
		}

		return $filterFields;
	}

	private function getFilterPresets(): array
	{
		$filterPresets = [
			'filter_default' => [
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_SYSTEM_PRESET_NAME'),
				'fields' => [
					'NAME' => '',
				],
				'default' => false,
			],
		];

		if ($this->arParams['isShowLiveFeed'] === 'Y')
		{
			$filterPresets['show_livefeed'] = [
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_START_LIVEFEED_PRESET_NAME'),
				'fields' => ['LIVEFEED_PRESET' => 'show_livefeed'],
				'default' => false,
			];
		}

		return $filterPresets;
	}

	private function getPinAction(int $id, bool $pinned): array
	{
		$actionClass = [
			\Bitrix\Main\Grid\CellActions::PIN,
		];
		if ($pinned)
		{
			$actionClass[] = \Bitrix\Main\Grid\CellActionState::ACTIVE;
		}

		return [
			[
				'class' => $actionClass,
				'events' => [
					'click' => "BX.Bizproc.Component.UserProcessesStart.changePin.bind(BX.Bizproc.Component.UserProcessesStart, {$id}, '$this->gridId')",
				],
			],
		];
	}

	private function renderIBlockName(ServiceFactory $service, array $iBlock, string $handler): string
	{
		$description = $iBlock['DESCRIPTION'] ?? '';
		if ($description === '')
		{
			$description = Loc::getMessage('BIZPROC_USER_PROCESSES_START_EMPTY_DESCRIPTION');
		}

		$description = \CBPViewHelper::prepareTaskDescription(
			\CBPHelper::convertBBtoText(
				preg_replace('|\n+|', "\n", trim($description)),
			)
		);

		$templateDescriptionElement = sprintf(
			'<span data-hint="%s" data-hint-html class="ui-hint"></span>',
			htmlspecialcharsbx($description)
		);

		$templateNameElement = sprintf(
			'<a class="ui-btn-link ui-typography-text-lg" onclick="%s" href="#">%s</a>',
			htmlspecialcharsbx($handler),
			htmlspecialcharsbx($iBlock['NAME']) . $templateDescriptionElement
		);

		$durationResponse = $service->getAverageIBlockTemplateDuration(
			new GetAverageIBlockTemplateDurationRequest(
				$iBlock['ID'],
				CBPDocumentEventType::Create,
				false,
				false,
			)
		);
		$duration = null;
		if ($durationResponse->isSuccess())
		{
			$duration = $durationResponse->getAverageDuration();
		}

		$durationText = $duration !== null
			? DurationFormatter::format($duration)
			: Loc::getMessage('BIZPROC_USER_PROCESSES_START_NO_DATA')
		;

		$averageTimeElement = sprintf(
			'<div class="%s">%s</div>',
			'bizproc-workflow-start-list-grid-template-average-time',
			Loc::getMessage(
				'BIZPROC_USER_PROCESSES_START_AVERAGE_WAITING_TIME',
				[
					'#TIME#' => '<b>' . $durationText . '</b>',
				]
			),
		);

		return sprintf(
			'<div class="bizproc-user-processes-start-grid-document-name-wrapper">%s</div>',
			$templateNameElement . $averageTimeElement,
		);
	}

	private function renderStartButton(string $handler, int $iblockId): string
	{
		return sprintf(
			'
				<div class="bizproc-user-processes-start-column-start">
					<button
						class="ui-btn ui-btn-success ui-btn-round ui-btn-xs ui-btn ui-btn-no-caps"
						onclick="%s"
					>%s</button>
					<div
						class="bizproc-user-processes-start-column-start-counter-wrapper"
						data-role="iblock-%s-counter"
					></div>
				</div>
			',
			htmlspecialcharsbx($handler),
			Loc::getMessage('BIZPROC_USER_PROCESSES_START_GRID_COLUMN_START_BUTTON'),
			$iblockId,
		);
	}


	private function getCurrentUserId(): int
	{
		return (int)CurrentUser::get()->getId();
	}

	private function addToolbar(): void
	{
		$filterParams = [
			'FILTER_ID' => $this->filterId,
			'GRID_ID' => $this->gridId,
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::LIGHT,
		];

		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);
	}

	protected function getEditActions(int $iblockId): array
	{
		$editActions = [];
		$templates = $this->getIBlockBpTemplates($iblockId);
		foreach ($templates as $template)
		{
			$bizprocEditorUrl = str_replace('#ID#', $template['id'], $template['bizprocEditorUrl']);
			$canEdit = $template['canEdit'] ?? false;
			$editActions[] = [
				'TITLE' => $template['description'],
				'TEXT' => $template['name'],
				'ONCLICK' => "BX.Bizproc.Component.UserProcessesStart.Instance.editTemplate(event, '$bizprocEditorUrl', $canEdit);",
			];
		}

		return $editActions;
	}

	private function getEditConstantsActions(int $iblockId): array
	{
		$documentType = self::getDocumentType($iblockId);
		if (
			!CBPDocument::canUserOperateDocumentType(
				CBPCanUserOperateOperation::CreateWorkflow,
				$this->getCurrentUserId(),
				$documentType,
			)
		)
		{
			return [];
		}

		$editConstantsActions = [];
		$templates = $this->getIBlockBpTemplates($iblockId);
		foreach ($templates as $template)
		{
			$templateId = $template['id'];
			$constants = CBPWorkflowTemplateLoader::getTemplateConstants($templateId);
			if ($constants)
			{
				$signedDocumentType = CBPDocument::signDocumentType($documentType);

				$editConstantsActions[] = [
					'TEXT' => $template['name'],
					'ONCLICK' => "BX.Bizproc.Component.UserProcessesStart.Instance.editTemplateConstants('$templateId', '$signedDocumentType');"
				];
			}

		}

		return $editConstantsActions;
	}

	protected function getIBlockBpTemplates(int $iblockId): array
	{
		if (!isset($this->listIBlockBpTemplates[$iblockId]))
		{
			$this->listIBlockBpTemplates[$iblockId] = [];
			$iBlockTypeId = ProcessService::getIBlockTypeId();

			if (CLists::isBpFeatureEnabled($iBlockTypeId))
			{
				$documentType = BizprocDocument::generateDocumentComplexType($iBlockTypeId, $iblockId);
				$this->listIBlockBpTemplates[$iblockId] = CBPDocument::getTemplatesForStart(
					$this->getCurrentUserId(),
					$documentType,
				);

				foreach ($this->listIBlockBpTemplates[$iblockId] as &$template)
				{
					$template['bizprocEditorUrl'] = CBPDocumentService::getBizprocEditorUrl($documentType);
					$template['canEdit'] = CBPDocument::CanUserOperateDocumentType(
						CBPCanUserOperateOperation::CreateWorkflow,
						$this->getCurrentUserId(),
						$documentType,
					);
				}
			}
		}

		return $this->listIBlockBpTemplates[$iblockId];
	}

}
