<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class MailUserSignatureListComponent extends CBitrixComponent
{
	protected $gridId = 'mail-usersignature-grid';
	protected $filterId = 'mail-usersignature-filter';
	protected $navParamName = 'page';

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams = parent::onPrepareComponentParams($arParams);
		if(!$arParams['USER_ID'])
		{
			$arParams['USER_ID'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
		}

		return $arParams;
	}

	/**
	 * @return mixed|void
	 */
	public function executeComponent()
	{
		if(!Loader::includeModule('mail'))
		{
			$this->showError(Loc::getMessage('MAIL_USERSIGNATURE_MODULE_ERROR'));
			return;
		}

		$this->arResult = [];

		$this->arResult['addUrl'] = new \Bitrix\Main\Web\Uri(\CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_MAIL_SIGNATURE'], ['id' => "0"]));
		$this->arResult['IFRAME'] = $this->arParams['IFRAME'] == 'Y' || $this->request->get('IFRAME') == 'Y' ? 'Y' : 'N';
		$this->arResult['FILTER'] = $this->prepareFilter();
		$this->arResult['GRID'] = $this->prepareGrid();
		$this->arResult['TITLE'] = Loc::getMessage('MAIL_USERSIGNATURE_LIST_TITLE');

		global $APPLICATION;
		$APPLICATION->SetTitle($this->arResult['TITLE']);

		$this->includeComponentTemplate();
	}

	protected function showError($error)
	{
		ShowError($error);
		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	protected function prepareGrid()
	{
		$grid = [];
		$grid["ROWS"] = [];
		$grid['GRID_ID'] = $this->gridId;
		$grid['COLUMNS'] = [
			[
				'id' => 'ID',
				'name' => 'ID',
				'default' => false,
			],
			[
				'id' => 'SENDER',
				'name' => Loc::getMessage('MAIL_USERSIGNATURE_LIST_SENDER'),
				'default' => true,
			],
			[
				'id' => 'SIGNATURE',
				'name' => Loc::getMessage('MAIL_USERSIGNATURE_LIST_SIGNATURE'),
				'default' => true,
			],
		];

		$gridOptions = new Bitrix\Main\Grid\Options($this->gridId);
		$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
		$pageSize = (int)$navParams['nPageSize'];
		$pageNavigation = new \Bitrix\Main\UI\PageNavigation($this->navParamName);
		$pageNavigation->allowAllRecords(false)->setPageSize($pageSize)->initFromUri();

		$signatureList = \Bitrix\Mail\Internals\UserSignatureTable::getList([
			'order' => ['ID' => 'desc'],
			'filter' => $this->getListFilter(),
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'count_total' => true,
		]);
		$signatures = $signatureList->fetchCollection();
		foreach($signatures as $signature)
		{
			/** @var \Bitrix\Main\Web\Uri $editUrl */
			$editUrl = new \Bitrix\Main\Web\Uri(\CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_MAIL_SIGNATURE'], ['id' => $signature->getId()]));
			$grid['ROWS'][] = [
				'id' => $signature->getId(),
				'data' => $signature->collectValues(),
				'columns' => [
					'ID' => $signature->getId(),
					'SENDER' => !empty($signature->getSender()) ? htmlspecialcharsbx($signature->getSender()) : Loc::getMessage('MAIL_USERSIGNATURE_LIST_DEFAULT'),
					'SIGNATURE' => htmlspecialcharsbx(mb_substr(strip_tags($signature->getSignature()), 0, 100), ENT_COMPAT, false),
				],
				'actions' => [
					[
						'TEXT' => Loc::getMessage('MAIL_USERSIGNATURE_EDIT_ACTION'),
						'ONCLICK' => 'BX.Mail.UserSignature.List.openUrl(\''.$editUrl->getLocator().'\')',
					],
					[
						'TEXT' => Loc::getMessage('MAIL_USERSIGNATURE_DELETE_ACTION'),
						'ONCLICK' => 'BX.Mail.UserSignature.List.delete(\''.$signature->getId().'\')',
					],
				],
			];
		}

		$fullCount = $signatureList->getCount();
		$pageNavigation->setRecordCount($fullCount);
		$grid['TOTAL_ROWS_COUNT'] = $fullCount;
		$grid['NAV_OBJECT'] = $pageNavigation;
		$grid['AJAX_MODE'] = 'Y';
		$grid['ALLOW_ROWS_SORT'] = false;
		$grid['AJAX_OPTION_JUMP'] = "N";
		$grid['AJAX_OPTION_STYLE'] = "N";
		$grid['AJAX_OPTION_HISTORY'] = "N";
		$grid['SHOW_PAGESIZE'] = false;
		$grid['AJAX_ID'] = \CAjax::GetComponentID("bitrix:main.ui.grid", '', '');
		$grid['SHOW_ROW_CHECKBOXES'] = false;
		$grid['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$grid['SHOW_ACTION_PANEL'] = false;

		return $grid;
	}

	/**
	 * @return array
	 */
	protected function prepareFilter()
	{
		$filter = [
			'FILTER_ID' => $this->filterId,
			'GRID_ID' => $this->gridId,
			'FILTER' => $this->getDefaultFilterFields(),
			'DISABLE_SEARCH' => false,
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => false,
			'ENABLE_LIVE_SEARCH' => true,
		];

		return $filter;
	}

	/**
	 * @return array
	 */
	protected function getDefaultFilterFields()
	{
		return [
			[
				'id' => 'SENDER',
				'name' => Loc::getMessage('MAIL_USERSIGNATURE_LIST_SENDER'),
				'default' => true,
			],
		];
	}

	/**
	 * @return array
	 */
	protected function getListFilter()
	{
		$filter = ['USER_ID' => $this->arParams['USER_ID']];

		$filterOptions = new Bitrix\Main\UI\Filter\Options($this->filterId);
		$requestFilter = $filterOptions->getFilter($this->getDefaultFilterFields());

		if(isset($requestFilter['SENDER']) && !empty($requestFilter['SENDER']))
		{
			$filter['SENDER'] = '%' . $requestFilter['SENDER'] . '%';
		}
		elseif(isset($requestFilter['FIND']) && !empty($requestFilter['FIND']))
		{
			$filter[] = [
				'LOGIC' => 'OR',
				'SENDER' => '%' . $requestFilter['FIND'] . '%',
				'SIGNATURE' => '%' . $requestFilter['FIND'] . '%',
			];
		}

		return $filter;
	}
}