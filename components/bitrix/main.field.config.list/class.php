<?php

use Bitrix\Main\Grid\Options;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserField\ConfigComponent;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class MainUfListComponent extends ConfigComponent
{
	protected const NAV_PARAM_NAME = 'page';

	protected $gridOptions;
	protected $pageNavigation;
	protected $gridId;

	protected function init(): void
	{
		parent::init();

		if(
			$this->errorCollection->isEmpty()
			&& !$this->access->canReadWithFilter($this->getListFilter())
		)
		{
			$this->errorCollection[] = $this->getAccessDeniedError();
		}
	}

	public function executeComponent()
	{
		$this->setTitle($this->arParams['title'] ?: Loc::getMessage('MAIN_FIELD_CONFIG_LIST_DETAIL_TITLE'));

		$this->init();
		if(!$this->errorCollection->isEmpty())
		{
			$this->arResult['errors'] = $this->errorCollection->toArray();
			$this->includeComponentTemplate();

			return;
		}

		$creationUrl = $this->getUserFieldConfigDetailUrl();
		if(Loader::includeModule('ui'))
		{
			Toolbar::deleteFavoriteStar();
			if($creationUrl)
			{
				$createButton = new Buttons\CreateButton([
					'tag' => Buttons\Tag::LINK,
					'link' => $creationUrl->getLocator(),
					'color' => Buttons\Color::PRIMARY,
					'icon' => Buttons\Icon::ADD,
				]);
				Toolbar::addButton($createButton, ButtonLocation::AFTER_TITLE);
			}
			}

		$filter = $this->access->prepareFilter($this->getListFilter());
		$gridSort = $this->getGridOptions()->GetSorting([
			'sort' => $this->getDefaultSort(),
		]);
		$data = UserFieldTable::getList([
			'select' => array_merge(['*'], UserFieldTable::getLabelsSelect()),
			'filter' => $filter,
			'order' => $gridSort['sort'],
			'offset' => $this->getPageNavigation()->getOffset(),
			'limit' => $this->getPageNavigation()->getLimit(),
			'runtime' => [
				UserFieldTable::getLabelsReference('', $this->arParams['language'] ?: Loc::getCurrentLang()),
			],
		])->fetchAll();
		$this->getPageNavigation()->setRecordCount(UserFieldTable::getCount($filter));

		$this->arResult['grid'] = $this->prepareGrid($data);

		$this->includeComponentTemplate();
	}

	protected function getGridId(): string
	{
		if(!$this->gridId)
		{
			$this->gridId = $this->arParams['gridId'] ?: 'main-user-field-config-list';
		}

		return $this->gridId;
	}

	protected function getGridOptions(): Options
	{
		if(!$this->gridOptions)
		{
			$this->gridOptions = new Bitrix\Main\Grid\Options($this->getGridId());
		}

		return $this->gridOptions;
	}

	protected function getPageNavigation(): PageNavigation
	{
		if(!$this->pageNavigation)
		{
			$gridOptions = $this->getGridOptions();
			$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
			$pageSize = (int)$navParams['nPageSize'];

			$this->pageNavigation = new PageNavigation(static::NAV_PARAM_NAME);
			$this->pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();
		}

		return $this->pageNavigation;
	}

	protected function prepareGrid(array $fields): array
	{
		$grid = [
			'GRID_ID' => $this->getGridId(),
		];

		if(count($fields) > 0)
		{
			foreach($fields as $field)
			{
				$grid['ROWS'][] = [
					'id' => $field['ID'],
					'data' => $field,
					'columns' => $this->getFieldColumns($field),
				];
			}
		}

		$grid['COLUMNS'] = $this->getGridColumns();
		$grid['NAV_PARAM_NAME'] = static::NAV_PARAM_NAME;
		$grid['CURRENT_PAGE'] = $this->getPageNavigation()->getCurrentPage();
		$grid['NAV_OBJECT'] = $this->getPageNavigation();
		$grid['TOTAL_ROWS_COUNT'] = $this->getPageNavigation()->getRecordCount();
		$grid['AJAX_MODE'] = 'Y';
		$grid['ALLOW_ROWS_SORT'] = false;
		$grid['AJAX_OPTION_JUMP'] = 'N';
		$grid['AJAX_OPTION_STYLE'] = 'N';
		$grid['AJAX_OPTION_HISTORY'] = 'N';
		$grid['AJAX_ID'] = \CAjax::GetComponentID('bitrix:main.ui.grid', '', '');
		$grid['SHOW_PAGESIZE'] = true;
		$grid['PAGE_SIZES'] = [['NAME' => 10, 'VALUE' => 10], ['NAME' => 20, 'VALUE' => 20], ['NAME' => 50, 'VALUE' => 50]];
		$grid['SHOW_ROW_CHECKBOXES'] = false;
		$grid['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$grid['SHOW_ACTION_PANEL'] = false;

		return $grid;
	}

	protected function getFieldColumns(array $field): array
	{
		$field = array_map(static function($value) {
			if(is_array($value))
			{
				foreach($value as $key => $item)
				{
					if(is_array($item))
					{
						$value[$key] = null;

						continue;
					}

					$value[$key] = htmlspecialcharsbx($item);
				}

				return $value;
			}

			return htmlspecialcharsbx($value);
		}, $field);

		$columns = $field;
		$detailUrl = $this->getUserFieldConfigDetailUrl($field['ID']);
		if($detailUrl)
		{
			$columns['FIELD_NAME'] = '<a href="'.htmlspecialcharsbx($detailUrl->getLocator()).'">'.$field['FIELD_NAME'].'</a>';
			$columns['EDIT_FORM_LABEL'] = '<a href="'.htmlspecialcharsbx($detailUrl->getLocator()).'">'.$field['EDIT_FORM_LABEL'].'</a>';
		}

		$userTypes = $this->getUserTypes();
		if(isset($userTypes[$field['USER_TYPE_ID']]))
		{
			$columns['USER_TYPE_ID'] = $userTypes[$field['USER_TYPE_ID']]['DESCRIPTION'];
		}

		foreach($this->getBooleanInputNames() as $inputName)
		{
			if($field[$inputName] === 'N')
			{
				$columns[$inputName] = Loc::getMessage('MAIN_FIELD_CONFIG_LIST_BOOLEAN_NO');
			}
			else
			{
				$columns[$inputName] = Loc::getMessage('MAIN_FIELD_CONFIG_LIST_BOOLEAN_YES');
			}
		}

		return $columns;
	}

	protected function getDefaultSort(): array
	{
		return [
			'id' => 'ASC',
		];
	}

	protected function getListFilter(): array
	{
		$filter = [];

		if($this->entityId)
		{
			$filter['ENTITY_ID'] = $this->entityId;
		}

		return $filter;
	}

	protected function getGridColumns(): array
	{
		$entity = UserFieldTable::getEntity();
		$labelsEntity = UserFieldLangTable::getEntity();

		return [
			[
				'id' => 'ID',
				'name' => $entity->getField('ID')->getTitle(),
				'default' => false,
				'sort' => 'ID',
			],
			[
				'id' => 'EDIT_FORM_LABEL',
				'name' => $labelsEntity->getField('EDIT_FORM_LABEL')->getTitle(),
				'default' => true,
				'sort' => 'EDIT_FORM_LABEL',
			],
			[
				'id' => 'FIELD_NAME',
				'name' => $entity->getField('FIELD_NAME')->getTitle(),
				'default' => true,
				'sort' => 'FIELD_NAME',
			],
			[
				'id' => 'ENTITY_ID',
				'name' => $entity->getField('ENTITY_ID')->getTitle(),
				'default' => false,
				'sort' => 'ENTITY_ID',
			],
			[
				'id' => 'USER_TYPE_ID',
				'name' => $entity->getField('USER_TYPE_ID')->getTitle(),
				'default' => true,
				'sort' => 'USER_TYPE_ID',
			],
			[
				'id' => 'XML_ID',
				'name' => $entity->getField('XML_ID')->getTitle(),
				'default' => false,
				'sort' => 'XML_ID',
			],
			[
				'id' => 'SORT',
				'name' => $entity->getField('SORT')->getTitle(),
				'default' => true,
				'sort' => 'SORT',
			],
			[
				'id' => 'MULTIPLE',
				'name' => $entity->getField('MULTIPLE')->getTitle(),
				'default' => true,
				'sort' => 'MULTIPLE',
			],
			[
				'id' => 'MANDATORY',
				'name' => $entity->getField('MANDATORY')->getTitle(),
				'default' => true,
				'sort' => 'MANDATORY',
			],
			[
				'id' => 'SHOW_FILTER',
				'name' => $entity->getField('SHOW_FILTER')->getTitle(),
				'default' => false,
				'sort' => 'SHOW_FILTER',
			],
			[
				'id' => 'IS_SEARCHABLE',
				'name' => $entity->getField('IS_SEARCHABLE')->getTitle(),
				'default' => false,
				'sort' => 'IS_SEARCHABLE',
			],
//			[
//				'id' => 'listColumnLabel',
//				'name' => $labelsEntity->getField('LIST_COLUMN_LABEL')->getTitle(),
//				'default' => false,
//				'sort' => 'listColumnLabel',
//			],
//			[
//				'id' => 'listFilterLabel',
//				'name' => $labelsEntity->getField('LIST_FILTER_LABEL')->getTitle(),
//				'default' => false,
//				'sort' => 'listFilterLabel',
//			],
//			[
//				'id' => 'errorMessage',
//				'name' => $labelsEntity->getField('ERROR_MESSAGE')->getTitle(),
//				'default' => false,
//				'sort' => 'errorMessage',
//			],
//			[
//				'id' => 'helpMessage',
//				'name' => $labelsEntity->getField('HELP_MESSAGE')->getTitle(),
//				'default' => false,
//				'sort' => 'helpMessage',
//			],
		];
	}

	protected function getBooleanInputNames(): array
	{
		return [
			'MULTIPLE',
			'MANDATORY',
			'SHOW_FILTER',
			'IS_SEARCHABLE',
		];
	}

	protected function getUserFieldConfigDetailUrl(int $fieldId = 0): ?Uri
	{
		$detailUrl = $this->arParams['detailUrl'];
		if($detailUrl && $this->entityId)
		{
			$url = new Uri($detailUrl);
			$url->addParams([
				'moduleId' => $this->moduleId,
				'entityId' => $this->entityId,
			]);
			if($fieldId)
			{
				$url->addParams([
					'fieldId' => $fieldId,
				]);
			}

			return $url;
		}

		return null;
	}
}