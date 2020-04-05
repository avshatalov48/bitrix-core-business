<?php
namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Socialnetwork\WorkgroupTable,
	Bitrix\Socialnetwork\UserToGroupTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

abstract class EntitySelector extends \CBitrixComponent implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	/** @var  Main\ErrorCollection */
	protected $errorCollection = null;

	protected $defaultSettings = [];
	protected $filter = [];
	protected $filterId = false;
	protected $filterPresets = [];
	protected $requestData = [];

	/**
	 * Base constructor.
	 * @param \CBitrixComponent|null $component		Component object if exists.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new Main\ErrorCollection();
	}

	/**
	 * @return void
	 */
	public function onIncludeComponentLang()
	{
		Loc::loadMessages(__FILE__);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		if (
			!isset($params['SITE_ID'])
			|| !is_string($params['SITE_ID'])
		)
		{
			$params['SITE_ID'] = \CSite::getDefSite();
		}

		return $params;
	}

	/**
	 * @return void
	 */
	public function executeComponent()
	{
		Main\Loader::includeModule('intranet');

		if (!Main\Loader::includeModule('socialnetwork'))
		{
			showError(Loc::getMessage('SONET_ENTITY_SELECTOR_ERR_SONET_MODULE_NOT_INSTALLED'));
			return;
		}

		$this->prepareRequest();
		$this->initDefaultSettings();
		$this->prepareFilter();
		$this->prepareResult();
		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @param string $code
	 * @return Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * @return array|Main\Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @return void
	 */
	protected function prepareRequest()
	{

	}

	/**
	 * @return void
	 */
	protected function prepareFilter()
	{
		$context = 'SONET_LANDING_ENTITY_SELECTOR_GROUP';

		$this->filter = [
			[
				'id' => 'GROUP_ID',
				'name' => Loc::getMessage('SONET_ENTITY_SELECTOR_FILTER_FIELD_GROUP_TITLE'),
				'default' => true,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => '3',
					'context' => $context,
					'multiple' => 'N',
					'contextCode' => 'SG',
					'enableAll' => 'N',
					'enableUsers' => 'N',
					'enableSonetgroups' => 'Y',
					'enableDepartments' => 'N',
					'landing' => 'Y',
					'useClientDatabase' => 'N'
				],
			],
			[
				'id' => 'AUTHOR_ID',
				'name' => Loc::getMessage('SONET_ENTITY_SELECTOR_FILTER_FIELD_AUTHOR_TITLE'),
				'default' => false,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => '3',
					'context' => $context,
					'multiple' => 'N',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableUsers' => 'Y',
					'enableSonetgroups' => 'N',
					'enableDepartments' => 'Y',
					'departmentSelectDisable' => 'Y',
					'landing' => 'Y',
					'useClientDatabase' => 'Y'
				],
			]
		];
	}

	protected function getFilter()
	{
		return $this->filter;
	}

	/**
	 * @return void
	 */
	protected function initDefaultSettings()
	{
		$this->defaultSettings = [
		];
	}


	protected function setFilterId($filterId = false)
	{
		$this->filterId = $filterId;
	}

	protected function getFilterId()
	{
		return $this->filterId;
	}

	protected function setFilterPresets($filterPresets = [])
	{
		$this->filterPresets = $filterPresets;
	}

	protected function getFilterPresets()
	{
		return $this->filterPresets;
	}

	protected function getCurrentUserId()
	{
		global $USER;

		return $USER->getId();
	}

	protected function getWorkgroups()
	{
		$result = [];

		if (\CSocNetUser::isCurrentUserModuleAdmin($this->arParams['SITE_ID']))
		{
			$filter = [
				'=LANDING' => 'Y',
				'=ACTIVE' => 'Y'
			];
			if (!empty($this->arParams['SITE_ID']))
			{
				$filter["=WorkgroupSite:GROUP.SITE_ID"] = $this->arParams['SITE_ID'];
			}

			$res = WorkgroupTable::getList([
				'filter' => $filter,
				'limit' => 100,
				'select' => [
					'GROUP_ID' => 'ID',
					'GROUP_NAME' => 'NAME'
				]
			]);
		}
		else
		{
			$filter = [
				'=GROUP.LANDING' => 'Y',
				'=GROUP.ACTIVE' => 'Y',
				'=USER_ID' => $this->getCurrentUserId(),
				'@ROLE' => UserToGroupTable::getRolesMember()
			];
			if (!empty($this->arParams['SITE_ID']))
			{
				$filter["=GROUP.WorkgroupSite:GROUP.SITE_ID"] = $this->arParams['SITE_ID'];
			}

			$res = UserToGroupTable::getList([
				'filter' => $filter,
				'limit' => 100,
				'select' => [
					'GROUP_ID' => 'GROUP_ID',
					'GROUP_NAME' => 'GROUP.NAME'
				]
			]);
		}

		while($workgroupFields = $res->fetch())
		{
			$result[] = [
				'ID' => $workgroupFields['GROUP_ID'],
				'CODE' => 'SG'.$workgroupFields['GROUP_ID'],
				'NAME' => $workgroupFields['GROUP_NAME'],
			];
		}

		return $result;
	}

	protected function setDefaultFilter($value = [])
	{
		if (
			empty($value)
			|| !is_array($value)
			|| empty($value['GROUP_ID'])
		)
		{
			return;
		}

		$options = new \Bitrix\Main\UI\Filter\Options($this->getFilterId(), $this->getFilterPresets());
		$options->setupDefaultFilter(
			$value,
			[ 'GROUP_ID', 'AUTHOR_ID' ]
		);
	}

	/**
	 * @return void
	 */
	protected function prepareResult()
	{
		global $DB;
		$this->getData();

		$intranetInstalled = Main\Loader::includeModule('intranet');
		$dateTimeformat = $DB->dateFormatToPHP(FORMAT_DATETIME);

		$this->arResult = [
			'FILTER_ID' => $this->getFilterId(),
			'FILTER' => $this->getFilter(),
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'CURRENT_DATETIME_FORMAT' => ($intranetInstalled ? \CIntranetUtils::getCurrentDateTimeFormat() : preg_replace('/[\/.,\s:][s]/', '', $dateTimeformat)),
			'CURRENT_DATETIME_FORMAT_WOYEAR' => ($intranetInstalled ? \CIntranetUtils::getCurrentDateTimeFormat([
				'woYear' => true
			]) : preg_replace('/[\/.,\s-][Yyo]/', '', $dateTimeformat)),
			'CURRENT_USER_ID' => $this->getCurrentUserId()
		];
	}

	/**
	 * @return array
	 */
	protected function getDataFilter()
	{
		return [];
	}

	/**
	 * @return void
	 */
	abstract protected function getData();

	/**
	 * @return string
	 */
	protected function getNavigationTitle()
	{
		return '';
	}
}