<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Application;
use \Bitrix\Main\Type\DateTime;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingFilterComponent extends LandingBaseComponent
{
	/**
	 * Filter type.
	 */
	const TYPE_SITE = 'SITE';
	const TYPE_LANDING = 'LANDING';

	/**
	 * Entity pseudo status.
	 */
	const STATUS_ACTIVE = 'active';
	const STATUS_NOT_ACTIVE = 'not_active';
	const STATUS_ACTIVE_CHANGED = 'active_changed';

	/**
	 * Some prefix and suffix for filter.
	 */
	const FILTER_PREFIX = 'LANDING_';
	const FILTER_SUFFIX = '';

	/**
	 * Filter id prefix.
	 * @deprecated since 19.500.0
	 * @var string
	 */
	protected static $prefix = 'LANDING_';

	/**
	 * Filter contains deleted items.
	 * @var bool
	 */
	protected static $isDeleted = false;

	/**
	 * External filter.
	 * @var array
	 */
	protected static $externalFilter = [];

	/**
	 * Allowed or not some type.
	 * @param string $type Type.
	 * @return boolean
	 */
	protected static function isTypeAllowed($type)
	{
		return $type == self::TYPE_SITE ||
				$type == self::TYPE_LANDING;
	}

	/**
	 * Get instance of grid.
	 * @param string $type Filter type.
	 * @param string $siteType Site type.
	 * @return \CGridOptions
	 */
	protected static function getGrid($type, $siteType)
	{
		static $grid = array();

		if (!self::isTypeAllowed($type))
		{
			$type = self::TYPE_SITE;
		}

		if (!isset($grid[$type]))
		{
			$grid[$type] = new \Bitrix\Main\UI\Filter\Options(
				self::FILTER_PREFIX . $siteType . '_' . $type . self::FILTER_SUFFIX,
				self::getFilterPresets()
			);
		}
		return $grid[$type];
	}

	/**
	 * Sets external filter.
	 * @param string $key Filter row key.
	 * @param mixed $value Filter row value.
	 * @return void
	 */
	public static function setExternalFilter($key, $value)
	{
		if (is_string($key))
		{
			self::$externalFilter[$key] = $value;
		}
	}

	/**
	 * Returns current raw filter by type.
	 * @param string $type Filter type.
	 * @param string $siteType Site type.
	 * @return array
	 */
	public static function getFilterRaw($type, $siteType = 'PAGE')
	{
		$grid = self::getGrid($type, $siteType);
		$gridFilter = self::getFilterPresets();
		$search = $grid->getFilter($gridFilter);

		if ($search['FILTER_APPLIED'])
		{
			return $search;
		}

		return [];
	}

	/**
	 * Get current filter by type.
	 * @param string $type Filter type.
	 * @param string $siteType Site type.
	 * @return array
	 */
	public static function getFilter($type, $siteType = 'PAGE')
	{
		$filter = self::$externalFilter;

		// in slider filter must be ignored
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		if ($request->get('IFRAME') == 'Y')
		{
			return $filter;
		}
		unset($context, $request);

		// if type correct, detect all filter keys from request
		if (self::isTypeAllowed($type))
		{
			$grid = self::getGrid($type, $siteType);
			$gridFilter = self::getFilterPresets();
			$search = $grid->getFilter($gridFilter);

			if ($search['FILTER_APPLIED'])
			{
				// if user type just in area
				if (isset($search['FIND']) && trim($search['FIND']))
				{
					$findOriginal = trim($search['FIND']);
					$search['FIND'] = '%' . trim($search['FIND']) . '%';
					$flt = array(
						'LOGIC' => 'OR',
						'TITLE' => $search['FIND'],
						'DESCRIPTION' => $search['FIND']
					);
					if ($type == self::TYPE_SITE)
					{
						$puny = new \CBXPunycode;
						$punyDomain = $puny->encode($findOriginal);
						if ($punyDomain)
						{
							$flt['DOMAIN.DOMAIN'] = [
								$search['FIND'],
								'%' . $punyDomain . '%'
							];
						}
						else
						{
							$flt['DOMAIN.DOMAIN'] = $search['FIND'];
						}
					}
					else if ($type == self::TYPE_LANDING)
					{
						$flt['CODE'] = $search['FIND'];
					}
					$filter[] = $flt;
				}
				// simple fields
				if (isset($search['DELETED']))
				{
					$filter['=DELETED'] = $search['DELETED'];
					self::$isDeleted = $search['DELETED'] == 'Y';
				}
				if (isset($search['ID']))
				{
					$filter['ID'] = $search['ID'];
				}
				// user selector fields
				foreach (['CREATED_BY_ID', 'MODIFIED_BY_ID'] as $code)
				{
					if (isset($search[$code]))
					{
						$filter[$code] = [];
						foreach ((array) $search[$code] as $uid)
						{
							$filter[$code][] = (substr($uid, 0, 1) == 'U')
												? substr($uid, 1)
												: $uid;
						}
					}
				}
				unset($code);
				// date fields
				foreach (['DATE_CREATE', 'DATE_MODIFY'] as $code)
				{
					if (isset($search[$code . '_from']))
					{
						$filter['>=' . $code] = new DateTime($search[$code . '_from']);
					}
					if (isset($search[$code . '_to']))
					{
						$filter['<=' . $code] = new DateTime($search[$code . '_to']);
					}
				}
				unset($code);
				if (isset($search['STATUS']))
				{
					if ($search['STATUS'] == self::STATUS_ACTIVE)
					{
						$filter['=ACTIVE'] = 'Y';
					}
					elseif ($search['STATUS'] == self::STATUS_NOT_ACTIVE)
					{
						$filter['=ACTIVE'] = 'N';
					}
					elseif (
						$type == self::TYPE_LANDING &&
						$search['STATUS'] == self::STATUS_ACTIVE_CHANGED
					)
					{
						$filter['=ACTIVE'] = 'Y';
						$filter['=CHANGED'] = 1;
					}
				}
			}
			unset($search);
		}

		return $filter;
	}

	/**
	 * Filter contains deleted items.
	 * @return bool
	 */
	public static function isDeleted()
	{
		return self::$isDeleted;
	}

	/**
	 * Gets filter fields.
	 * @return array
	 */
	protected function getFilterFields()
	{
		// title for field will be to setup in result_modifier
		$return = [
			'STATUS' => [
				'id' => 'STATUS',
				'default' => true,
				'type' => 'list',
				'items' =>
					($this->arParams['FILTER_TYPE'] == self::TYPE_SITE)
					? [
						self::STATUS_ACTIVE,
						self::STATUS_NOT_ACTIVE
					]
					: [
						self::STATUS_ACTIVE,
						self::STATUS_NOT_ACTIVE,
						self::STATUS_ACTIVE_CHANGED
					]
			],
			'DELETED' => [
				'id' => 'DELETED',
				'default' => true,
				'type' => 'checkbox'
			],
			'ID' => [
				'id' => 'ID',
				'default' => false,
				'type' => 'string'
			],
			'CREATED_BY_ID' => [
				'id' => 'CREATED_BY_ID',
				'default' => true,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => 3,
					'multiple' => 'Y',
					'departmentSelectDisable' => 'Y'
				]
			],
			'MODIFIED_BY_ID' => [
				'id' => 'MODIFIED_BY_ID',
				'default' => false,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => 3,
					'multiple' => 'Y',
					'departmentSelectDisable' => 'Y'
				]
			],
			'DATE_CREATE' => [
				'id' => 'DATE_CREATE',
				'default' => false,
				'type' => 'date'
			],
			'DATE_MODIFY' => [
				'id' => 'DATE_MODIFY',
				'default' => false,
				'type' => 'date'
			]
		];

		if ($this->arParams['DRAFT_MODE'] == 'Y')
		{
			unset($return['STATUS']);
		}

		return $return;
	}

	/**
	 * Gets presets for filter.
	 * @return array
	 */
	protected static function getFilterPresets()
	{
		return [
			'my' => [
				'name' => '',
				'fields' => [
					'CREATED_BY_ID' => Manager::getUserId(),
					'CREATED_BY_ID_name' => Manager::getUserFullName()
				]
			],
			'active' => [
				'name' => '',
				'fields' => [
					'STATUS' => self::STATUS_ACTIVE
				]
			]
		];
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('FILTER_TYPE', '');
			$this->checkParam('SETTING_LINK', '');
			$this->checkParam('DRAFT_MODE', 'N');
			$this->checkParam('FOLDER_SITE_ID', 0);

			$this->arParams['TYPE'] = trim($this->arParams['TYPE']);
			$this->arParams['FILTER_TYPE'] = trim($this->arParams['FILTER_TYPE']);
			$this->arParams['FILTER_ID'] = self::FILTER_PREFIX;
			$this->arParams['FILTER_ID'] .= $this->arParams['TYPE'] . '_';
			$this->arParams['FILTER_ID'] .= $this->arParams['FILTER_TYPE'];
			$this->arParams['FILTER_ID'] .= self::FILTER_SUFFIX;

			$this->arResult['NAVIGATION_ID'] = $this::NAVIGATION_ID;
			$this->arResult['CURRENT_PAGE'] = $this->request($this::NAVIGATION_ID);
			$this->arResult['FILTER_FIELDS'] = $this->getFilterFields();
			$this->arResult['FILTER_PRESETS'] = $this->getFilterPresets();

			// check some permissions
			if ($this->arParams['FILTER_TYPE'] == $this::TYPE_LANDING)
			{
				$rights = Rights::getOperationsForSite(
					$this->arParams['FOLDER_SITE_ID']
				);
				// can edit settings
				if (
					!in_array(
						Rights::ACCESS_TYPES['sett'],
						$rights
					)
				)
				{
					$this->arParams['SETTING_LINK'] = '';
				}
				// can create folders in this site
				if (
				!in_array(
					Rights::ACCESS_TYPES['edit'],
					$rights
				)
				)
				{
					$this->arParams['FOLDER_SITE_ID'] = 0;
				}
			}
		}

		parent::executeComponent();
	}
}
