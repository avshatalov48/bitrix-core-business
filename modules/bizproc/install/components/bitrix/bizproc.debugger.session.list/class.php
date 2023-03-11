<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Debugger\Session\Manager;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

class BizprocDebuggerSessionList extends CBitrixComponent implements Errorable, \Bitrix\Main\Engine\Contract\Controllerable
{
	const GRID_ID = 'bizproc_debugger_session_list';
	const FILTER_ID = 'bizproc_debugger_session_list_filter';

	private ErrorCollection $errorCollection;
	private array $documentCategories = [];

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
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

	public function listKeysSignedParameters(): array
	{
		return ['documentSigned'];
	}

	public function deleteSessionsAction($sessionIds): void
	{
		$this->init();

		if ($this->hasErrors())
		{
			return;
		}

		foreach ($sessionIds as $sessionId)
		{
			$deletionResult = Manager::deleteInactiveSession($sessionId);

			$this->addErrors($deletionResult->getErrors());
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['documentSigned'] = htmlspecialcharsback($arParams['documentSigned'] ?? '');

		return $arParams;
	}

	public function executeComponent()
	{
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_TITLE'));

		$this->init();

		if (!$this->hasErrors())
		{
			$this->updateSessions();
		}

		if (!$this->hasErrors())
		{
			$this->fillGridInfo();
			$this->fillGridData();
			$this->fillGridActions();
		}

		if (!$this->hasErrors())
		{
			$this->arResult['documentSigned'] = CBPDocument::signParameters([
				$this->arParams['documentType'],
				$this->arParams['documentCategoryId'],
				0,
			]);
			$this->addToolbar();

			return $this->includeComponentTemplate();
		}
		else
		{
			return $this->includeComponentTemplate('error');
		}
	}

	private function init(): void
	{
		$this->checkModules();

		if (!$this->hasErrors())
		{
			$unsignedDocument = CBPDocument::unSignParameters($this->arParams['~documentSigned']);

			$this->initDocumentType($unsignedDocument[0] ?? []);
			$this->arParams['documentCategoryId'] = $unsignedDocument[1] ?? null;
		}

		if (!$this->hasErrors())
		{
			$this->checkRights();
		}

		if (!$this->hasErrors())
		{
			$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();

			$this->documentCategories = array_column(
				$documentService->getDocumentCategories($this->arParams['documentType']) ?? [],
				'name',
				'id'
			);
		}
	}

	private function checkModules(): void
	{
		if (!Loader::includeModule('bizproc'))
		{
			$errorMsg = Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_MODULE_ERROR', ['#MODULE#' => 'BizProc']);
			$this->setError(new Error($errorMsg));
		}
	}

	private function initDocumentType(array $documentType): void
	{
		try
		{
			$this->arParams['documentType'] = CBPHelper::ParseDocumentId($documentType);
		}
		catch (CBPArgumentException $argumentException)
		{
			$this->setError(new Error(Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_DOCUMENT_TYPE_ERROR')));
		}
	}

	private function checkRights(): void
	{
		$hasRights = Manager::canUserDebugAutomation($this->getCurrentUserId(), $this->arParams['documentType']);

		if (!$hasRights)
		{
			$this->setError(new Error(Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_RIGHTS_ERROR')));
		}
	}

	private function getCurrentUserId(): int
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->getId();
	}

	private function updateSessions(): void
	{
		$updatedSessions = is_array($this->request['FIELDS']) ? $this->request['FIELDS'] : [];
		if ($this->request->isAjaxRequest())
		{
			$updatedSessions = \Bitrix\Main\Text\Encoding::convertEncoding($updatedSessions, 'UTF-8', LANG_CHARSET);
		}

		foreach ($updatedSessions as $sessionId => $updatedFields)
		{
			if (is_string($updatedFields['NAME'] ?? null) && $updatedFields['NAME'] !== '')
			{
				$this->renameSession($sessionId, $updatedFields['NAME']);
			}
		}
	}

	public function renameSession(string $sessionId, string $newName): void
	{
		$session = \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable::query()
			->addFilter('ID', $sessionId)
			->setLimit(1)
			->exec()
			->fetchObject();

		if (!$session)
		{
			return;
		}

		$session->setTitle($newName);
		$result = $session->save();

		$this->addErrors($result->getErrors());
	}

	private function fillGridInfo(): void
	{
		$this->arResult['gridId'] = static::GRID_ID;
		$this->arResult['gridColumns'] = $this->getGridColumns();
		$this->arResult['pageNavigation'] = $this->getPageNavigation();
		$this->arResult['pageSizes'] = $this->getPageSizes();
	}

	private function getGridColumns(): array
	{
		return [
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_NAME'),
				'default' => true,
				'editable' => [
					'TYPE' => \Bitrix\Main\Grid\Editor\Types::TEXT,
				],
			],
			[
				'id' => 'STARTED_BY',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_STARTED_BY'),
				'sort' => 'STARTED_BY',
				'default' => true,
			],
			[
				'id' => 'STARTED_CATEGORY_ID',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_CATEGORY'),
				'sort' => 'STARTED_CATEGORY_ID',
				'default' => true,
			],
			[
				'id' => 'STARTED_DATE',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_STARTED_DATE'),
				'sort' => 'STARTED_DATE',
				'default' => true,
			],
		];
	}

	private function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$options = new \Bitrix\Main\Grid\Options(static::GRID_ID);
		$navParams = $options->GetNavParams();

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation(static::GRID_ID);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		return $pageNavigation;
	}

	protected function getPageSizes(): array
	{
		return [
			['NAME' => '5', 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100']
		];
	}

	private function fillGridData()
	{
		/** @var \Bitrix\Main\UI\PageNavigation $pageNav */
		$pageNav = $this->arResult['pageNavigation'];

		$defaultFilter = [
			'MODULE_ID' => $this->arParams['documentType'][0],
			'ENTITY' => $this->arParams['documentType'][1],
			'DOCUMENT_TYPE' => $this->arParams['documentType'][2],
		];
		$filter = array_merge($this->getUserFilter(), $defaultFilter);

		$sessionListIterator = \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable::query()
			->setSelect(['ID', 'TITLE', 'STARTED_BY', 'DOCUMENT_CATEGORY_ID', 'STARTED_DATE'])
			->setFilter($filter)
			->setOrder($this->getGridOrder())
			->setLimit($pageNav->getLimit())
			->setOffset($pageNav->getOffset())
			->registerRuntimeField(new ExpressionField('STARTED_DAY', 'EXTRACT(DAY FROM %s)', ['STARTED_DATE']))
			->registerRuntimeField(new ExpressionField('STARTED_MONTH', 'EXTRACT(MONTH FROM %s)', ['STARTED_DATE']))
			->countTotal(true)
			->exec()
		;

		$pageNav->setRecordCount($sessionListIterator->getCount());

		$jsHandlerShowSession = "BX.Bizproc.Component.DebuggerSessionList.Instance.showSession('%s');";

		$gridData = [];
		while ($session = $sessionListIterator->fetchObject())
		{
			$sessionName = $session->getTitle();
			if (!is_string($sessionName) || $sessionName === '')
			{
				$sessionLoc = Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_DEBUGGER_SESSION');
				$sessionName = $sessionLoc . ' ' . $session->getStartedDate()->format('d.m');
			}

			$documentCategoryId = $session->getDocumentCategoryId();

			$gridData[] = [
				'id' => $session->getId(),
				'columns' => [
					'NAME' => $this->renderLinkTag($sessionName, sprintf($jsHandlerShowSession, $session->getId())),
					'STARTED_BY' => $this->renderUserName($session->getStartedBy()),
					'STARTED_CATEGORY_ID' => htmlspecialcharsbx($this->getCategoryName($documentCategoryId)),
					'STARTED_DATE' => FormatDate('d M Y H:i', $session->getStartedDate()),
				],
				'data' => [
					'NAME' => $sessionName,
				],
				'actions' => [
					[
						'text' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_ROW_ACTION_RENAME'),
						'onclick' => "BX.Bizproc.Component.DebuggerSessionList.Instance.renameSession('{$session->getId()}');",
					],
					[
						'text' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_ROW_ACTION_DELETE'),
						'onclick' => "BX.Bizproc.Component.DebuggerSessionList.Instance.deleteSessions(['{$session->getId()}']);",
					],
				],
			];
		}

		$this->arResult['gridData'] = $gridData;
	}

	private function getGridOrder(): array
	{
		$gridTableMap = [
			'STARTED_BY' => 'STARTED_BY',
			'STARTED_CATEGORY_ID' => 'DOCUMENT_CATEGORY_ID',
			'STARTED_DATE' => 'STARTED_DATE'
		];

		$orderBy = $gridTableMap[(string)$this->request->get('by')] ?? 'STARTED_DATE';
		$direction = $this->request->get('order') === 'asc' ? 'asc' : 'desc';

		return [$orderBy => $direction];
	}

	private function getUserFilter(): array
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options(static::FILTER_ID);
		$fields = $filterOptions->getFilter();
		$filter = [];

		if (isset($fields['STARTED_BY']))
		{
			$filter['@STARTED_BY'] = $fields['STARTED_BY'];
		}
		if (isset($fields['STARTED_CATEGORY_ID']))
		{
			$filter['DOCUMENT_CATEGORY_ID'] = (int)$fields['STARTED_CATEGORY_ID'];
		}

		if ($filterOptions->getSearchString() !== '')
		{
			$sessionLoc = Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_DEBUGGER_SESSION');
			$sessionNamePattern = '/(\D*)((?<day>\d{1,2})(\.(?<month>\d{1,2}))?)?\s*$/i' . BX_UTF_PCRE_MODIFIER;

			if (preg_match($sessionNamePattern, $filterOptions->getSearchString(), $matches, PREG_UNMATCHED_AS_NULL))
			{
				if (!$matches[1] || mb_stristr($sessionLoc, trim($matches[1])))
				{
					if (isset($matches['day']))
					{
						$filter['STARTED_DAY'] = (int)$matches['day'];
					}
					if (isset($matches['month']))
					{
						$filter['STARTED_MONTH'] = (int)$matches['month'];
					}
				}
				else
				{
					$filter['STARTED_DATE'] = \Bitrix\Main\Type\DateTime::createFromTimestamp(0);
				}
			}
		}
		if (isset($fields['STARTED_DATE_from'], $fields['STARTED_DATE_to']))
		{
			$filter[] = [
				'LOGIC' => 'AND',
				['>=STARTED_DATE' => $fields['STARTED_DATE_from']],
				['<=STARTED_DATE' => $fields['STARTED_DATE_to']],
			];
		}

		return $filter;
	}

	private function renderLinkTag(string $text, string $handler): string
	{
		return sprintf(
			'<a class="ui-btn-link" onclick="%s" href="#">%s</a>',
			htmlspecialcharsbx($handler),
			htmlspecialcharsbx($text)
		);
	}

	private function renderUserName(int $userId): string
	{
		$user = \Bitrix\Main\UserTable::getById($userId)->fetchObject();
		if (!$user)
		{
			return '';
		}

		$userAvatar = $this->getUserAvatarUrl($user->getPersonalPhoto());

		$userAvatarElement = sprintf(
			'<span class="%s"><i style="background-image: %s"></i></span>',
			'bizproc-debugger-session-list-grid-avatar ui-icon ui-icon-common-user',
			$userAvatar ? 'url(\''. Uri::urnEncode($userAvatar) . '\')' : ''
		);

		$userNameElement = sprintf(
			'<span class="%s">%s</span>',
			'bizproc-debugger-session-list-grid-username-inner',
			CUser::FormatName(CSite::GetNameFormat(false), $user, false, true),
		);

		return sprintf(
			'<div class="bizproc-debugger-session-list-grid-username-wrapper">%s</div>',
			$userAvatarElement . $userNameElement,
		);
	}

	private function getUserAvatarUrl(int $fileId): string
	{
		$file = $fileId >= 1 ? \CFile::GetFileArray($fileId) : null;

		$url = '';
		if ($file)
		{
			$fileInfo = \CFile::ResizeImageGet(
				$file,
				[
					'width' => 100,
					'height' => 100,
				],
				BX_RESIZE_IMAGE_EXACT,
			);

			$url = $fileInfo ? $fileInfo['src'] : '';
		}

		return $url;
	}

	private function getCategoryName(?int $categoryId): string
	{
		if (!isset($categoryId))
		{
			return '';
		}

		return $this->documentCategories[$categoryId] ?? '';
	}

	private function fillGridActions()
	{
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
		$deleteBtn = $snippet->getRemoveButton();
		$snippet->setButtonActions($deleteBtn, [
			[
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => 'BX.Bizproc.Component.DebuggerSessionList.Instance.deleteChosenSessions();',
					]
				],
			],
		]);

		$this->arResult['gridActions'] = [
			'GROUPS' => [
				[
					'ITEMS' => [
						$deleteBtn,
						$snippet->getEditButton(),
					],
				],
			],
		];
	}

	private function addToolbar()
	{
		$filterOption = new \Bitrix\Main\UI\Filter\Options(static::FILTER_ID);

		$filterParams = [
			'FILTER_ID' => static::FILTER_ID,
			'GRID_ID' => static::GRID_ID,
			'FILTER' => $this->getFilterFields(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => 'DEFAULT',
		];

		$createButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_ADD_DEBUGGER_SESSION_BUTTON'),
			'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
			'click' => new \Bitrix\UI\Buttons\JsCode(
				'BX.Bizproc.Component.DebuggerSessionList.Instance.createSession();',
			),
		]);

		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);
		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($createButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
		\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
	}

	private function getFilterFields(): array
	{
		return [
			'NAME' => [
				'id' => 'NAME',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_NAME'),
				'type' => 'string',
				'default' => true,
			],
			'STARTED_BY' => [
				'id' => 'STARTED_BY',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_STARTED_BY'),
				'type' => 'entity_selector',
				'default' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'intranetUsersOnly' => true,
									'inviteEmployeeLink' => false
								],
							],
						],
					],
				],
			],
			'STARTED_CATEGORY_ID' => [
				'id' => 'STARTED_CATEGORY_ID',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_CATEGORY'),
				'type' => 'list',
				'items' => $this->getListFilterFieldItems($this->documentCategories),
				'default' => true,
			],
			'STARTED_DATE' => [
				'id' => 'STARTED_DATE',
				'name' => Loc::getMessage('BIZPROC_DEBUGGER_SESSION_LIST_GRID_COLUMN_STARTED_DATE'),
				'type' => 'date',
				'time' => true,
				'default' => true,
			],

		];
	}

	private function getListFilterFieldItems(array $values): array
	{
		$items = ['' => ['NAME' => '']];

		foreach ($values as $itemValue => $itemTitle)
		{
			$items[$itemValue] = ['NAME' => $itemTitle,];
		}

		return $items;
	}
}