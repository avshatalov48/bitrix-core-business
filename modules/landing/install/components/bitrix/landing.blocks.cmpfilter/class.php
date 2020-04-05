<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LandingUtilsCmpFilterComponent extends \CBitrixComponent
{
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
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (
			isset($this->arParams['FILTER']) &&
			isset($this->arParams['FILTER_NAME']) &&
			is_array($this->arParams['FILTER']) &&
			trim($this->arParams['FILTER_NAME']) != ''
		)
		{
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
				$filter['?NAME'] = '%' . trim($request->get('q')) . '%';
			}

			if (!empty($filter))
			{
				$GLOBALS[trim($this->arParams['FILTER_NAME'])] = $filter;
			}
		}
	}
}