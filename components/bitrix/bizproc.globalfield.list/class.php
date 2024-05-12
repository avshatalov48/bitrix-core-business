<?php

use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load('ui.alerts');

class BizprocGlobalFieldListComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
	private $fieldTypes = [];
	private $usersInfo = [];
	private $visibilityNames = [];

	private const VAR_MODE = 'variable';
	private const CONST_MODE = 'constant';

	private $mode;

	public function configureActions(): array
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return ['DOCUMENT_TYPE_SIGNED', 'MODE'];
	}

	public function onPrepareComponentParams($arParams): array
	{
		if (isset($arParams['DOCUMENT_TYPE_SIGNED']) && \Bitrix\Main\Loader::includeModule('bizproc'))
		{
			$arParams['DOCUMENT_TYPE_SIGNED'] = htmlspecialcharsback($arParams['DOCUMENT_TYPE_SIGNED']);
			$arParams['DOCUMENT_TYPE'] = CBPDocument::unSignDocumentType($arParams['DOCUMENT_TYPE_SIGNED']);
		}
		else
		{
			$arParams['DOCUMENT_TYPE'] = null;
		}

		$arParams['MODE'] = in_array($arParams['MODE'] ?? null, [self::VAR_MODE, self::CONST_MODE]) ? $arParams['MODE'] : null;
		$arParams['SET_TITLE'] = (isset($arParams['SET_TITLE']) && $arParams['SET_TITLE'] === 'N' ? 'N' : 'Y');

		return $arParams;
	}

	private function getGridId(): ?string
	{
		if ($this->mode === self::VAR_MODE)
		{
			return 'bizproc_globalvariable_list';
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return 'bizproc_globalconstant_list';
		}
		else
		{
			return '';
		}
	}

	private function getFilterId(): ?string
	{
		return $this->getGridId() . '_filter';
	}

	private function getTitleComponent(): ?string
	{
		if ($this->mode === self::VAR_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_TITLE_VARIABLE');
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_TITLE_CONSTANT');
		}
		else
		{
			return '';
		}
	}



	private function getGridColumnNameTitle(): ?string
	{
		if ($this->mode === self::VAR_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_NAME_VARIABLE');
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_NAME_CONSTANT');
		}
		else
		{
			return '';
		}
	}

	private function canUserReadFieldList(): bool
	{
		$userId = (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());

		$documentType = $this->arParams['DOCUMENT_TYPE'];
		if ($this->mode === self::VAR_MODE)
		{
			return \Bitrix\Bizproc\Workflow\Type\GlobalVar::canUserRead($documentType, $userId);
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return \Bitrix\Bizproc\Workflow\Type\GlobalConst::canUserRead($documentType, $userId);
		}
		else
		{
			return false;
		}
	}

	private function getCantReadErrorMessage(): ?string
	{
		if ($this->mode === self::VAR_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_ERR_CANT_READ_VARIABLE');
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_ERR_CANT_READ_CONSTANT');
		}
		else
		{
			return '';
		}
	}

	private function getCantDeleteErrorMessage(): ?string
	{
		if ($this->mode === self::VAR_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage(
				'BIZPROC_GLOBALFIELDS_LIST_CANT_DELETE_VARIABLE_RIGHT'
			);
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return \Bitrix\Main\Localization\Loc::getMessage(
				'BIZPROC_GLOBALFIELDS_LIST_CANT_DELETE_CONSTANT_RIGHT'
			);
		}
		else
		{
			return '';
		}
	}

	private static function showError(string $message)
	{
		$message = htmlspecialcharsbx($message);

		echo <<<HTML
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
				<span class="ui-alert-message">{$message}</span>
			</div>
HTML;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			static::showError(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));

			return null;
		}

		$this->initMode();
		if (!$this->mode)
		{
			static::showError(
				\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_ERR_MODE_NOT_DEFINED')
			);

			return null;
		}

		if (!$this->arParams['DOCUMENT_TYPE'])
		{
			static::showError(
				\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_ERR_DOCUMENT_TYPE_MSGVER_1')
			);

			return null;
		}

		$canRead = $this->canUserReadFieldList();
		if (!$canRead)
		{
			static::showError($this->getCantReadErrorMessage());

			return null;
		}

		$title = $this->getTitleComponent();
		if ($title !== '' && $this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle($title);
		}

		$pageNav = $this->getPageNavigation();

		$this->arResult = [
			'GridId' => $this->getGridId(),
			'GridColumns' => $this->getGridColumns(),
			'GridRows' => $this->getGridRows($pageNav),
			'PageNavigation' => $pageNav,
			'Mode' => $this->mode,
			'ActionPanel' => $this->getGridActionPanel(),
		];

		$this->prepareToolBar();

		return $this->includeComponentTemplate();
	}

	private function initMode()
	{
		if ($this->arParams['MODE'] === self::CONST_MODE)
		{
			$this->mode = self::CONST_MODE;
		}
		elseif ($this->arParams['MODE'] === self::VAR_MODE)
		{
			$this->mode = self::VAR_MODE;
		}
		else
		{
			$this->mode = null;
		}
	}

	private function prepareToolBar()
	{
		$filterOption = new \Bitrix\Main\UI\Filter\Options($this->getFilterId());
		$filterOption->reset();

		$filterParams = [
			'FILTER_ID' => $this->getFilterId(),
			'GRID_ID' => $this->getGridId(),
			'FILTER' => $this->getFilterFields(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => 'DEFAULT',
		];
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);

		$createButton = \Bitrix\UI\Buttons\CreateButton::create([
			'text' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_CREATE_BUTTON'),
			'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
			'dataset' => [
				'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
			],
			'click' => new \Bitrix\UI\Buttons\JsCode(
				'BX.Bizproc.Component.GlobalFieldListComponent.Instance.onCreateButtonClick();',
			),
		]);

		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($createButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
	}

	private function getFilterFields(): array
	{
		$filterFields = [];

		$filterFields['NAME'] = [
			'id' => 'NAME',
			'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_NAME'),
			'default' => true,
		];

		return $filterFields;
	}

	private function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$gridId = $this->getGridId();
		$gridOptions = new \Bitrix\Main\Grid\Options($gridId);
		$navParams = $gridOptions->GetNavParams(['nPageSize' => 5]);

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($gridId);
		$pageNavigation->setPageSize($navParams['nPageSize']);
		$pageNavigation->setPageSizes(static::getPageSizes());

		$pageNavigation->initFromUri();

		return $pageNavigation;
	}

	private function getGridColumns(): array
	{
		$columns = [
			[
				'id' => 'NAME',
				'name' => $this->getGridColumnNameTitle(),
				'sort' => 'NAME',
				'default' => true,
			],
			[
				'id' => 'DESCRIPTION',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_DESCRIPTION'),
				'default' => false,
			],
			[
				'id' => 'TYPE',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_TYPE'),
				'sort' => 'TYPE',
				'default' => true,
			],
			[
				'id' => 'MULTIPLE',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_IS_MULTIPLE'),
				'default' => false,
			],
			[
				'id' => 'VISIBILITY',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_VISIBILITY'),
				'sort' => 'VISIBILITY',
				'default' => true,
			],
			[
				'id' => 'CREATED_DATE',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_CREATED_DATE'),
				'sort' => 'CREATED_DATE',
				'default' => true,
			],
			[
				'id' => 'CREATED_BY',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_CREATED_BY'),
				'sort' => 'CREATED_BY',
				'default' => true,
			],
			[
				'id' => 'MODIFIED_DATE',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_MODIFIED_DATE'),
				'sort' => 'MODIFIED_DATE',
				'default' => false,
			],
			[
				'id' => 'MODIFIED_BY',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_MODIFIED_BY'),
				'sort' => 'MODIFIED_BY',
				'default' => false,
			],
		];

		if ($this->mode !== self::VAR_MODE)
		{
			$columns[] = [
				'id' => 'VALUE',
				'name' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_VALUE'),
				'default' => false,
			];
		}

		return $columns;
	}

	private function getGridActionPanel(): array
	{
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
		$deleteBtn = $snippet->getRemoveButton();
		$snippet->setButtonActions($deleteBtn, [
			[
				'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => sprintf(
							"BX.Bizproc.Component.GlobalFieldListComponent.Instance.deleteFieldsAction('%s')",
							$this->mode
						),
					]
				],
			],
		]);

		return [
			'GROUPS' => [
				[
					'ITEMS' => [
						$deleteBtn,
					],
				],
			],
		];
	}

	public function processGridDeleteAction($ids): array
	{
		$documentType = $this->arParams['DOCUMENT_TYPE'];
		$userId = (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());

		if ($this->arParams['MODE'] === self::VAR_MODE)
		{
			$canDelete = \Bitrix\Bizproc\Workflow\Type\GlobalVar::canUserDelete($documentType, $userId);
			if (!$canDelete)
			{
				return ['error' => $this->getCantDeleteErrorMessage()];
			}

			$this->deleteVariables($ids);

			return ['result' => 'success'];
		}
		elseif ($this->arParams['MODE'] === self::CONST_MODE)
		{
			$canDelete = \Bitrix\Bizproc\Workflow\Type\GlobalConst::canUserDelete($documentType, $userId);
			if (!$canDelete)
			{
				return ['error' => $this->getCantDeleteErrorMessage()];
			}

			$this->deleteConstants($ids);

			return ['result' => 'success'];
		}

		return ['error' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_ERR_MODE_NOT_DEFINED')];
	}

	private function deleteVariables($ids)
	{
		foreach ($ids as $id)
		{
			$property = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getById($id);
			if (!$property)
			{
				continue;
			}

			$visibility = $property['Visibility'];
			$availableVisibility = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getAvailableVisibility(
				$this->arParams['DOCUMENT_TYPE']
			);
			if (!in_array($visibility, $availableVisibility))
			{
				continue;
			}

			\Bitrix\Bizproc\Workflow\Type\GlobalVar::delete($id);
		}
	}

	private function deleteConstants($ids)
	{
		foreach ($ids as $id)
		{
			$property = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getById($id);
			if (!$property)
			{
				continue;
			}

			$visibility = $property['Visibility'];
			$availableVisibility = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getAvailableVisibility(
				$this->arParams['DOCUMENT_TYPE']
			);
			if (!in_array($visibility, $availableVisibility))
			{
				continue;
			}

			\Bitrix\Bizproc\Workflow\Type\GlobalConst::delete($id);
		}
	}

	private function getGridRows(\Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$gridTableMapping = [
			'NAME' => 'NAME',
			'TYPE' => 'PROPERTY_TYPE',
			'VISIBILITY' => 'VISIBILITY',
			'CREATED_DATE' => 'CREATED_DATE',
			'CREATED_BY' => 'CREATED_BY',
			'MODIFIED_DATE' => 'MODIFIED_DATE',
			'MODIFIED_BY' => 'MODIFIED_BY',
		];
		$order = ['ID' => 'asc'];
		$request = $this->request;
		if (array_key_exists($request->get('by'), $gridTableMapping))
		{
			$order = [
				$gridTableMapping[$request->get('by')] => $request->get('order'),
			];
		}

		$moduleId = $this->arParams['DOCUMENT_TYPE'][0];
		$documentType = $this->arParams['DOCUMENT_TYPE'][2];

		$filter = $this->getUserFilter($this->getFilterId());
		$filter[] = [
			'LOGIC' => 'OR',
			['=VISIBILITY' => 'GLOBAL'],
			['=VISIBILITY' => mb_strtoupper($moduleId)],
			['=VISIBILITY' => mb_strtoupper($moduleId) . '_' . mb_strtoupper($documentType)],
		];

		$query = [
			'select' => ['*'],
			'filter' => $filter,
			'order' => $order,
			'limit' => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
		];

		if ($this->mode === self::VAR_MODE)
		{
			return $this->getRowsForVariables($query, $pageNavigation);
		}
		elseif ($this->mode === self::CONST_MODE)
		{
			return $this->getRowsForConstants($query, $pageNavigation);
		}
		else
		{
			return [];
		}
	}

	private function getUserFilter($filterId): array
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($filterId);
		$fields = $filterOptions->getFilter();
		$filter = [];

		if (!empty($fields['NAME']))
		{
			$filter['NAME'] = '%' . $fields['NAME'] . '%';
		}

		if ($filterOptions->getSearchString())
		{
			$filter[] = [
				'NAME' => '%' . $filterOptions->getSearchString() . '%'
			];
		}

		return $filter;
	}

	private function getRowsForVariables(array $query, \Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$filter = $query['filter'];
		$dbResult = \Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable::getList($query);

		$pageNavigation->setRecordCount(\Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable::getCount($filter));

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$rows = [];
		$jsHandlerEdit = "BX.Bizproc.Component.GlobalFieldListComponent.Instance.editGlobalFieldAction('%s', '%s');";
		foreach ($dbResult as $row)
		{
			$id = htmlspecialcharsbx(CUtil::JSEscape($row['ID']));
			$property =\Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable::convertToProperty($row);
			$gridRow = [
				'id' => $id,
				'data' =>$this->convertToUserFriendlyStyle($property, $documentService),
				'actions' => $this->getGridRowActions($id),
			];
			$gridRow['data']['NAME'] = static::renderLinkTag(
				$gridRow['data']['NAME'],
				sprintf($jsHandlerEdit, $id, $this->mode)
			);

			$rows[] = $gridRow;
		}

		return $rows;
	}

	private function getRowsForConstants(array $query, \Bitrix\Main\UI\PageNavigation $pageNavigation): array
	{
		$filter = $query['filter'];
		$dbResult = \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable::getList($query);

		$pageNavigation->setRecordCount(\Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable::getCount($filter));

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$rows = [];
		$jsHandlerEdit = "BX.Bizproc.Component.GlobalFieldListComponent.Instance.editGlobalFieldAction('%s', '%s');";
		foreach ($dbResult as $row)
		{
			$id = htmlspecialcharsbx(CUtil::JSEscape($row['ID']));
			$property =\Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable::convertToProperty($row);
			$gridRow = [
				'id' => $id,
				'data' =>$this->convertToUserFriendlyStyle($property, $documentService),
				'actions' => $this->getGridRowActions($id),
			];
			$gridRow['data']['NAME'] = static::renderLinkTag(
				$gridRow['data']['NAME'],
				sprintf($jsHandlerEdit, $id, $this->mode)
			);

			$rows[] = $gridRow;
		}

		return $rows;
	}

	private function getGridRowActions($id): array
	{
		$jsHandlerEdit = "BX.Bizproc.Component.GlobalFieldListComponent.Instance.editGlobalFieldAction('%s', '%s');";
		$jsHandlerDelete = "BX.Bizproc.Component.GlobalFieldListComponent.Instance.deleteGlobalFieldAction('%s', '%s');";

		return [
			[
				'text' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_EDIT'),
				'onclick' => sprintf($jsHandlerEdit, $id, $this->mode),
			],
			[
				'text' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_GLOBALFIELDS_LIST_DELETE'),
				'onclick' => sprintf($jsHandlerDelete, $id, $this->mode),
			],
		];
	}

	private static function getPageSizes(): array
	{
		return [
			['NAME' => '5', 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '15', 'VALUE' => '15'],
			['NAME' => '20', 'VALUE' => '20'],
		];
	}

	private function convertToUserFriendlyStyle(array $property, $documentService): array
	{
		$name = $property['Name'];
		$description = htmlspecialcharsbx($property['Description']);

		$type = $this->convertTypeToUserFriendlyStyle($property['Type'], $documentService);

		$isMultiple = $this->formatValue($property['Multiple'], ['Type' => 'bool'], $documentService);
		$value = htmlspecialcharsbx($this->formatValue($property['Default'], $property, $documentService));

		$createdDate = $this->formatValue($property['CreatedDate'], ['Type' => 'date'], $documentService);
		$createdBy = $this->getCreatedByView((int)$property['CreatedBy']);
		$visibility = $this->convertVisibilityToUserFriendlyStyle($property['Visibility']);

		$modifiedDate = $this->formatValue($property['ModifiedDate'], ['Type' => 'date'], $documentService);
		$modifiedBy = $this->getCreatedByView((int)$property['ModifiedBy']);

		return [
			'NAME' => $name,
			'DESCRIPTION' => $description,
			'TYPE' => $type,
			'MULTIPLE'=> $isMultiple,
			'VALUE' => $value,
			'CREATED_DATE' => $createdDate,
			'CREATED_BY' => $createdBy,
			'VISIBILITY' => htmlspecialcharsbx($visibility),
			'MODIFIED_DATE' => $modifiedDate,
			'MODIFIED_BY' => $modifiedBy,
		];
	}

	private static function renderLinkTag(string $text, string $handler): string
	{
		return sprintf(
			'<a class="ui-btn-link" onclick="%s" href="#">%s</a>',
			htmlspecialcharsbx($handler),
			htmlspecialcharsbx($text)
		);
	}

	private function convertTypeToUserFriendlyStyle(string $type, $documentService): string
	{
		$allTypes = $this->getFieldsTypes($documentService);
		if (array_key_exists($type, $allTypes))
		{
			return $allTypes[$type]['Name'];
		}

		return '';
	}

	private function getFieldsTypes($documentService): array
	{
		if ($this->fieldTypes)
		{
			return $this->fieldTypes;
		}

		$baseTypes = \Bitrix\Bizproc\FieldType::getBaseTypesMap();
		unset($baseTypes[\Bitrix\Bizproc\FieldType::INTERNALSELECT]);
		unset($baseTypes[\Bitrix\Bizproc\FieldType::FILE]);

		$documentType = $this->arParams['DOCUMENT_TYPE'];
		$documentTypes = $documentService->GetDocumentFieldTypes($documentType);

		foreach ($documentTypes as $key => $value)
		{
			if ($key == 'UF:date')
			{
				$key = 'date';
			}
			if (!isset($baseTypes[$key]))
			{
				continue;
			}

			$this->fieldTypes[$key] = [
				'Name' => $value['Name'],
			];
		}

		return $this->fieldTypes;
	}

	private function formatValue($value, array $property, $documentService): string
	{
		$documentType = $this->arParams['DOCUMENT_TYPE'];
		$fieldTypeObject = $documentService->getFieldTypeObject($documentType, $property);
		if ($fieldTypeObject)
		{
			if ($property['Type'] === 'user')
			{
				return $fieldTypeObject->formatValue($value, 'friendly');
			}

			return $fieldTypeObject->formatValue($value);

		}

		return '';
	}

	private function convertVisibilityToUserFriendlyStyle(string $visibility): string
	{
		if (!$this->visibilityNames)
		{
			$documentType = $this->arParams['DOCUMENT_TYPE'];

			if ($this->mode === self::VAR_MODE)
			{
				$visibilityNames = \Bitrix\Bizproc\Workflow\Type\GlobalVar::getVisibilityShortNames($documentType);
			}
			elseif ($this->mode === self::CONST_MODE)
			{
				$visibilityNames = \Bitrix\Bizproc\Workflow\Type\GlobalConst::getVisibilityShortNames($documentType);
			}
			else
			{
				$visibilityNames = [];
			}

			$this->visibilityNames = $visibilityNames;
		}

		return $this->visibilityNames[$visibility] ?? '';
	}

	private function getCreatedByView(int $userId): string
	{
		$userInfo = $this->getUserInfo($userId);
		if ($userInfo['NAME'] === null)
		{
			return '';
		}

		$userName = htmlspecialcharsbx($userInfo['NAME']);
		$userAvatar = '';
		$userUrl = htmlspecialcharsbx($userInfo['URL']);
		$userEmptyAvatarHTMLClass = ' bizproc-grid-avatar-empty';
		if ($userInfo['AVATAR'])
		{
			$userAvatar =' style="background-image: url(\'' . Uri::urnEncode($userInfo['AVATAR']) .'\')"';
			$userEmptyAvatarHTMLClass = '';
		}

		return <<<HTML
			<div class="bizproc-grid-username-wrapper">
				<a class="bizproc-grid-username" href="{$userUrl}">
					<span class="bizproc-grid-avatar ui-icon ui-icon-common-user {$userEmptyAvatarHTMLClass}">
						<i{$userAvatar}></i>
					</span>
					<span class="bizproc-grid-username-inner">{$userName}</span>
				</a>
			</div>
HTML;

	}

	private function getUserInfo(int $userId)
	{
		$user = [
			'NAME' => null,
			'AVATAR' => null,
			'URL' => null,
		];

		$usersInfo = \Bitrix\Bizproc\Automation\Helper::prepareUserSelectorEntities(
			$this->arParams['DOCUMENT_TYPE'],
			'user_' . $userId
		);

		if ($userId <= 0 || !$usersInfo)
		{
			return $user;
		}
		if (isset($this->usersInfo[$userId]))
		{
			return $this->usersInfo[$userId];
		}

		$userInfo = $usersInfo[0];

		$this->usersInfo[$userId] = [
			'NAME' => $userInfo['name'],
			'AVATAR' => $userInfo['photoSrc'],
			'URL' => $userInfo['url'],
		];

		return $this->usersInfo[$userId];
	}
}
