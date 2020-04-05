<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingFilterComponent extends LandingBaseComponent
{
	/**
	 * Filter type.
	 */
	const TYPE_SITE = 'SITE';
	const TYPE_LANDING = 'LANDING';

	/**
	 * Filter id prefix.
	 * @var string
	 */
	protected static $prefix = 'LANDING_';

	/**
	 * Filter contains deleted items.
	 * @var bool
	 */
	protected static $isDeleted = false;

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
	 * @return \CGridOptions
	 */
	protected static function getGrid($type)
	{
		static $grid = array();

		if (!isset($grid[$type]) && self::isTypeAllowed($type))
		{
			$grid[$type] = new \Bitrix\Main\UI\Filter\Options(
				self::$prefix . $type,
				array()
			);
		}
		return $grid[$type];
	}

	/**
	 * Get current filter by type.
	 * @param string $type Filter type.
	 * @return array
	 */
	public static function getFilter($type)
	{
		$filter = array();

		// in slider filter is not show
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		if ($request->get('IFRAME') == 'Y')
		{
			return $filter;
		}

		if (self::isTypeAllowed($type))
		{
			$grid = self::getGrid($type);
			$gridFilter = array();
			$search = $grid->getFilter($gridFilter);
			if ($search['FILTER_APPLIED'])
			{
				if (isset($search['FIND']) && trim($search['FIND']))
				{
					$search['FIND'] = '%' . trim($search['FIND']) . '%';
					$flt = array(
						'LOGIC' => 'OR',
						'TITLE' => $search['FIND'],
						'DESCRIPTION' => $search['FIND']
					);
					if ($type == self::TYPE_SITE)
					{
						$flt['DOMAIN.DOMAIN'] = $search['FIND'];
					}
					else if ($type == self::TYPE_LANDING)
					{
						$flt['CODE'] = $search['FIND'];
					}
					$filter[] = $flt;
				}
				if (isset($search['DELETED']))
				{
					$filter['=DELETED'] = $search['DELETED'];
					self::$isDeleted = $search['DELETED'] == 'Y';
				}
				if (isset($search['ID']))
				{
					$filter['ID'] = $search['ID'];
				}
			}
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
		return [
			'ID' => [
				'id' => 'ID',
				'default' => true,
				'type' => 'string'
			],
			'DELETED' => [
				'id' => 'DELETED',
				'default' => true,
				'type' => 'checkbox'
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
			$this->checkParam('FOLDER_SITE_ID', 0);
			$this->arParams['FILTER_TYPE'] = trim($this->arParams['FILTER_TYPE']);
			$this->arParams['FILTER_ID'] = self::$prefix . $this->arParams['FILTER_TYPE'];
			$this->arResult['NAVIGATION_ID'] = $this::NAVIGATION_ID;
			$this->arResult['CURRENT_PAGE'] = $this->request($this::NAVIGATION_ID);
			$this->arResult['FILTER_FIELDS'] = $this->getFilterFields();
		}

		parent::executeComponent();
	}
}