<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LandingUtilsCmpFilterComponent extends \CBitrixComponent
{
	/**
	 * Search or not by search module.
	 */
	const ENABLED_SEARCH_MODULE = true;

	/**
	 * Get filter for main.ui.filter used.
	 * @return array
	 */
	public static function getFilterFields()
	{
		return array(
			array(
				'id' => 'NAME',
				'name' => Loc::getMessage('LD_COMP_FILTER_NAME'),
				'type' => 'string',
				'default' => true
			),
			array(
				'id' => 'ID',
				'name' => 'ID',
				'type' => 'number',
				'default' => true
			)
		);
	}

	/**
	 * Get additional filter by query string.
	 * @param string $q Query string.
	 * @return array
	 */
	protected static function search($q)
	{
		$filter = array();
		$q = trim($q);
		if ($q === '')
		{
			return $filter;
		}

		if (
			self::ENABLED_SEARCH_MODULE &&
			Loader::includeModule('search')
		)
		{
			$filter['ID'] = array(-1);
			$obSearch = new \CSearch;
			$obSearch->setOptions(array(
				'ERROR_ON_EMPTY_STEM' => false,
			));
			$obSearch->search(array(
				'QUERY' => $q,
				'SITE_ID' => LANG,
				'MODULE_ID' => 'iblock'
			));
			if (!$obSearch->selectedRowsCount()) {
				$obSearch->search(
					array(
						'QUERY' => $q,
						'SITE_ID' => SITE_ID,
						'MODULE_ID' => 'iblock',
					),
					array(),
					array(
						'STEMMING' => false
					)
				);
			}
			$obSearch->navStart(500);
			$found = false;
			while ($row = $obSearch->fetch())
			{
				$found = true;
				$filter['ID'][] = $row['ITEM_ID'];
			}
			if (!$found)
			{
				unset($filter['ID']);
				$filter['*SEARCHABLE_CONTENT'] = $q;
			}
		}
		else
		{
			$filter['*SEARCHABLE_CONTENT'] = $q;
		}

		return $filter;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (!Loader::includeModule('landing'))
		{
			return;
		}

		if (
			isset($this->arParams['FILTER']) &&
			isset($this->arParams['FILTER_NAME']) &&
			is_array($this->arParams['FILTER']) &&
			trim($this->arParams['FILTER_NAME']) != ''
		)
		{
			$this->arParams['FILTER_NAME'] = trim($this->arParams['FILTER_NAME']);
			$filter = array();
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			foreach ($this->getFilterFields() as $itemFilter)
			{
				$key = $itemFilter['id'];
				switch ($itemFilter['type'])
				{
					case 'number':
						{
							if (
								isset($this->arParams['FILTER'][$key . '_from']) &&
								$this->arParams['FILTER'][$key . '_from']
							)
							{
								$filter['>=' . $key] = $this->arParams['FILTER'][$key . '_from'];
							}
							if (
								isset($this->arParams['FILTER'][$key . '_to']) &&
								$this->arParams['FILTER'][$key . '_to']
							)
							{
								$filter['<=' . $key] = $this->arParams['FILTER'][$key . '_to'];
							}
							break;
						}
					default:
						{
							if (
								isset($this->arParams['FILTER'][$key]) &&
								$this->arParams['FILTER'][$key]
							)
							{
								$filter['?' . $key] = '%' . trim($this->arParams['FILTER'][$key]) . '%';
							}
						}
				}
			}

			if ($request->get('q'))
			{
				$filter = array_merge(
					$filter,
					self::search($request->get('q'))
				);
			}

			if (!empty($filter))
			{
				if (
					isset($GLOBALS[$this->arParams['FILTER_NAME']]) &&
					is_array($GLOBALS[$this->arParams['FILTER_NAME']])
				)
				{
					$filter = array_merge($filter, $GLOBALS[$this->arParams['FILTER_NAME']]);
				}
				$GLOBALS[$this->arParams['FILTER_NAME']] = $filter;
			}
		}
	}
}