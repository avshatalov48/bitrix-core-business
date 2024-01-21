<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Errorable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\Counter\CounterFilter;
use Bitrix\Socialnetwork\Internals\Counter\Role;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class CSocialnetworkInterfaceCountersComponent extends CBitrixComponent	implements Controllerable, Errorable
{
	protected const ERROR_UNKNOWN_SYSTEM_ERROR = 'SONET_SIC_01';

	private $errorCollection;

	/**
	 * CSocialnetworkGroupCountersComponent constructor.
	 * @param null $component
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
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
	 * @return \Bitrix\Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * @return array|\Bitrix\Main\Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 *
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	/**
	 * @param $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params['ENTITY_TYPE'] = $params['ENTITY_TYPE'] ?? null;
		$params['ENTITY_ID'] = (is_numeric($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0);
		$params['COUNTERS'] = is_array($params['COUNTERS']) ? $params['COUNTERS'] : [];
		$params['CURRENT_COUNTER'] = $params['CURRENT_COUNTER'] ?? '';
		$params['ROLE'] = $params['ROLE'] ?? null;

		return $params;
	}

	/**
	 * @return mixed|void|null
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->init();
			$this->loadData();

			$this->includeComponentTemplate('toolbar');
		}
		catch (SystemException $exception)
		{
			$this->includeErrorTemplate($exception->getMessage());
		}
	}

	protected function listKeysSignedParameters()
	{
		return [];
	}


	/**
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	private function loadData(): void
	{
		$counterProvider = Counter::getInstance($this->arParams['USER_ID']);

		$this->arResult['COUNTERS'] = [];

		foreach ($this->arParams['COUNTERS'] as $counter)
		{
			if (!$this->canView($counter, $this->arResult['ROLE']))
			{
				continue;
			}

			$value = $counterProvider->get($counter, $this->arParams['ENTITY_ID']);

			$this->arResult['COUNTERS'][$counter] = [
				'VALUE' => $value,
				'FILTER_PRESET_ID' => $this->getFilterPresetId($counter),
				'FILTER_FIELDS' => $this->getFilterFields($counter),
				'TITLE' => $this->getCounterTitle($counter),
				'STYLE' => $this->getCounterStyle($counter, $value)
			];
		}

		$this->arResult['ENTITY_TITLE'] = !empty($this->arResult['COUNTERS']) ? $this->getEntityTitle($this->arParams['ENTITY_TYPE']) : '';
	}

	/**
	 * @param string $counter
	 * @param int $value
	 * @return string
	 */
	private function getCounterStyle(string $counter, array $value): string
	{
		$sum = 0;
		foreach ($value as $val)
		{
			$sum += (int)$val;
		}
		switch ($counter)
		{
			case CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT:
				$result = ($sum > 0 ? Counter\CounterStyle::STYLE_GREEN : Counter\CounterStyle::STYLE_GRAY);
				break;
			case CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN:
			case CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED:
			case CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS:
				$result = ($sum > 0 ? Counter\CounterStyle::STYLE_RED : Counter\CounterStyle::STYLE_GRAY);
				break;
			default:
				$result = Counter\CounterStyle::STYLE_GRAY;
		}

		return $result;
	}

	/**
	 * @param string $counter
	 * @param int $value
	 * @return string
	 */
	private function getCounterTitle(string $counter): string
	{
		$map = [
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN => Loc::getMessage('SONET_SIC_COUNTER_TITLE_WORKGROUP_REQUESTS_IN'),
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT => Loc::getMessage('SONET_SIC_COUNTER_TITLE_WORKGROUP_REQUESTS_OUT'),
			CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED => Loc::getMessage('SONET_SIC_COUNTER_TITLE_WORKGROUP_LIST_LIVEFEED'),
			CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS => Loc::getMessage('SONET_SIC_COUNTER_TITLE_WORKGROUP_LIST_TASKS'),
		];

		return ($map[$counter] ?? '');
	}

	private function getFilterPresetId(string $counter): string
	{
		$map = [
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN => CounterFilter::PRESET_REQUESTS_IN,
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT => CounterFilter::PRESET_REQUESTS_OUT,
		];

		return ($map[$counter] ?? '');
	}

	private function getFilterFields(string $counter): ?array
	{
		$map = [
			CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED => [
				'COMMON_COUNTERS' => CounterFilter::VALUE_LIVEFEED,
			],
			CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS => [
				'COMMON_COUNTERS' => CounterFilter::VALUE_TASKS,
			],
		];

		return ($map[$counter] ?? null);
	}

	/**
	 * @throws SystemException
	 */
	private function checkModules(): void
	{
		try
		{
			if (!Loader::includeModule('socialnetwork'))
			{
				throw new SystemException(Loc::getMessage('SONET_SIC_SYSTEM_ERROR_INCLUDE_MODULE'));
			}
		}
		catch (LoaderException $exception)
		{
			throw new SystemException(Loc::getMessage('SONET_SIC_SYSTEM_ERROR_INCLUDE_MODULE'));
		}
	}

	/**
	 * @param string $errorMessage
	 * @param string $code
	 */
	private function includeErrorTemplate(string $errorMessage, string $code = ''): void
	{
		$this->arResult['ERROR'] = $errorMessage;
		$this->arResult['ERROR_CODE'] = ($code ?: self::ERROR_UNKNOWN_SYSTEM_ERROR);

		$this->includeComponentTemplate('error');
	}

	private function init(): void
	{
		global $USER;

		$this->arParams['USER_ID'] = \Bitrix\Socialnetwork\Helper\User::getCurrentUserId();
		if (!$this->arParams['USER_ID'])
		{
			throw new SystemException(Loc::getMessage('SONET_SIC_SYSTEM_ERROR'));
		}

		if (
			empty($this->arParams['COUNTERS'])
			|| !is_array($this->arParams['COUNTERS'])
		)
		{
			throw new SystemException(Loc::getMessage('SONET_SIC_SYSTEM_ERROR'));
		}

		$this->arResult['ROLE'] = Counter\Role::get([
			'entityType' => $this->arParams['ENTITY_TYPE'],
			'role' => $this->arParams['ROLE'],
		]);

		$this->arResult['IS_ADMIN'] = CSocNetUser::isCurrentUserModuleAdmin();

		$this->arResult['IS_SCRUM_MASTER'] = false;
		if ($this->arParams['ENTITY_TYPE'] === CounterDictionary::ENTITY_WORKGROUP_DETAIL)
		{
			$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($this->arParams['ENTITY_ID']);
			if (
				$group
				&& $group->isScrumProject()
				&& $group->getScrumMaster() === (int)$USER->getId()
			)
			{
				$this->arResult['IS_SCRUM_MASTER'] = true;
			}
		}
	}

	/**
	 * @param string $counter
	 * @param string $role
	 * @return bool
	 */
	private function canView(string $counter, string $role): bool
	{
		if (
			$this->arResult['IS_ADMIN']
			|| $this->arResult['IS_SCRUM_MASTER']
		)
		{
			return true;
		}

		$map = [
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN => [
				Role::WORKGROUP_OWNER,
			],
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT => [
				Role::WORKGROUP_OWNER,
			],
			CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED => [
				Role::ALL,
			],
			CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS => [
				Role::ALL,
			],
		];

		return (
			isset($map[$counter])
			&& is_array($map[$counter])
			&& in_array($role, $map[$counter], true)
		);
	}

	/**
	 * @param $entityType $type
	 * @return string
	 */
	private function getEntityTitle(string $entityType): string
	{
		$map = [
			CounterDictionary::ENTITY_WORKGROUP_DETAIL => Loc::getMessage('SONET_SIC_ENTITY_TITLE_WORKGROUP_DETAIL'),
			CounterDictionary::ENTITY_WORKGROUP_LIST => Loc::getMessage('SONET_SIC_ENTITY_TITLE_WORKGROUP_LIST'),
		];

		return ($map[$entityType] ?? '');
	}
}