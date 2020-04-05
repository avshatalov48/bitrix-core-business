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
			$search = $grid->GetFilter($gridFilter);
			if ($search['FILTER_APPLIED'])
			{
				$filter[] = array(
					'LOGIC' => 'OR',
					'TITLE' => '%' . trim($search['FIND']) . '%',
					'DESCRIPTION' => '%' . trim($search['FIND']) . '%'
				);
			}
		}

		return $filter;
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
			$this->checkParam('FOLDER_LID', 0);
			$this->arParams['FILTER_TYPE'] = trim($this->arParams['FILTER_TYPE']);
			$this->arParams['FILTER_ID'] = self::$prefix . $this->arParams['FILTER_TYPE'];
		}

		parent::executeComponent();
	}
}