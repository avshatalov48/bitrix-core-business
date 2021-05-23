<?php
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Socialnetwork\Component;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class LivefeedSelector extends Component\EntitySelector
{
	protected $workgroupsList = [];

	public function __construct($component = null)
	{
		parent::__construct($component);
	}

	/**
	* @return void
	*/
	protected function setWorkgroupsList($value = [])
	{
		$this->workgroupsList = $value;
	}

	protected function getWorkgroupsList()
	{
		return $this->workgroupsList;
	}

	/**
	 * @return void
	 */
	protected function getData()
	{

	}

	/**
	 * @return void
	 */
	protected function prepareRequest()
	{
		$this->requestData = [
			'useBXMainFilter' => $this->request->get('useBXMainFilter'),
			'filter' => $this->request->get('filter')
		];
	}

	private function setFilter(array $filterFields)
	{
		$defaultFilter = [];

		foreach($filterFields as $fieldName => $params)
		{
			$code = (isset($params['code']) ? $params['code'] : false);
			$name = (isset($params['name']) ? $params['name'] : false);

			if (
				$code == ''
				|| $name == ''
			)
			{
				continue;
			}

			if (
				$fieldName == 'GROUP_ID'
				&& ($workGroupData = $this->getWorkgroupDataByCode($code))
			)
			{
				$defaultFilter['GROUP_ID'] = $code;
				$defaultFilter['GROUP_ID_label'] = $workGroupData['NAME'];
			}
			elseif ($fieldName == 'AUTHOR_ID')
			{
				$defaultFilter['AUTHOR_ID'] = $code;
				$defaultFilter['AUTHOR_ID_label'] = $name;
			}
		}

		if (empty($defaultFilter['GROUP_ID']))
		{
			return;
		}

		$this->setDefaultFilter($defaultFilter);

		$this->arResult['FILTER_VALUE'] = [ $defaultFilter['GROUP_ID'] ];
		$this->arResult['FILTER_AUTHOR_VALUE'] = (!empty($defaultFilter['AUTHOR_ID']) ? $defaultFilter['AUTHOR_ID'] : false);
		$this->arResult['FILTER_INIT_VALUE'] = [
			[
				'name' => $defaultFilter['GROUP_ID_label'],
				'value' => $defaultFilter['GROUP_ID'],
				'key' => 'GROUP_ID'
			]
		];
		if (!empty($defaultFilter['AUTHOR_ID']))
		{
			$this->arResult['FILTER_INIT_VALUE'][] = [
				'name' => $defaultFilter['AUTHOR_ID_label'],
				'value' => $defaultFilter['AUTHOR_ID'],
				'key' => 'AUTHOR_ID'
			];
		}
	}

	public function prepareResult()
	{
		$this->setFilterId('landing_livefeed_list');
		$this->setFilterPresets([]);

		parent::prepareResult();

		$filterFromRequest = false;
		if (
			!empty($this->requestData['filter'])
			&& is_array($this->requestData['filter'])
		)
		{
			$groupFilterField = $authorFilterField = false;
			foreach($this->requestData['filter'] as $filterField)
			{
				if (!empty($filterField['key']))
				{
					if (
						$filterField['key'] == 'GROUP_ID'
						&& ($workGroupData = $this->getWorkgroupDataByCode($filterField['value']))
					)
					{
						$groupFilterField = [
							'code' => $filterField['value'],
							'name' => $workGroupData['NAME']
						];
					}
					elseif ($filterField['key'] == 'AUTHOR_ID')
					{
						$authorFilterField = [
							'code' => $filterField['value'],
							'name' => $filterField['name']
						];
					}
				}
			}

			if (
				!empty($groupFilterField)
				|| !empty($authorFilterField)
			)
			{
				$filterFromRequest = true;
				$this->setFilter([
					'GROUP_ID' => $groupFilterField,
					'AUTHOR_ID' => $authorFilterField
				]);
			}
		}

		if (!$filterFromRequest)
		{
			if ($this->requestData['useBXMainFilter'] != 'Y')
			{
				$workgroupsList = $this->getWorkgroups();
				if (count($workgroupsList) == 0)
				{
					$this->arResult['EMPTY_NOWORKGROUPS'] = 'Y';
				}
				elseif (count($workgroupsList) == 1)
				{
					$defaultWorkgroup = array_pop($workgroupsList);
					$this->setFilter([
						'GROUP_ID' => [
							'code' => $defaultWorkgroup['CODE'],
							'name' => $defaultWorkgroup['NAME']
						]
					]);
				}
				else
				{
					$res = Main\FinderDestTable::getList([
						'order' => [
							'LAST_USE_DATE' => 'DESC'
						],
						'filter' => [
							'=USER_ID' => $this->arResult["CURRENT_USER_ID"],
							'=CONTEXT' => 'SONET_LANDING_ENTITY_SELECTOR_GROUP'
						],
						'select' => [ 'CODE' ],
						'limit' => 1
					]);

					if (
						($destData = $res->fetch())
						&& !empty($destData['CODE'])
						&& ($workGroupData = $this->getWorkgroupDataByCode($filterField['value']))
					)
					{
						$this->setFilter([
							'GROUP_ID' => [
								'code' => $destData['CODE'],
								'name' => $workGroupData['NAME']
							]
						]);
					}

					if (empty($this->arResult['FILTER_VALUE']))
					{
						$this->arResult['EMPTY_EXPLICIT'] = 'Y';
					}
				}
			}
			else
			{
				$filterOption = new \Bitrix\Main\UI\Filter\Options($this->getFilterId());
				$filterData = $filterOption->getFilter();

				if (
					empty($filterData)
					|| empty($filterData['GROUP_ID'])
				)
				{
					$this->arResult['EMPTY_EXPLICIT'] = 'Y';
				}
				else
				{
					$this->arResult['FILTER_VALUE'] = [ $filterData['GROUP_ID'] ];
					$this->arResult['FILTER_AUTHOR_VALUE'] = (!empty($filterData['AUTHOR_ID']) ? $filterData['AUTHOR_ID'] : false);
				}
			}
		}

		$this->arResult["URL_GROUP_CREATE"] = '';

		$createPageSiteId = (\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->isAdminSection() ? \CSite::getDefSite() : $this->arParams["SITE_ID"]);
		$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', '', $createPageSiteId);

		if (!empty($userPage))
		{
			$uri = new Main\Web\Uri(\CComponentEngine::makePathFromTemplate(
				$userPage.'user/#user_id#/groups/create/',
				[
					"user_id" => $this->arResult["CURRENT_USER_ID"]
				]
			));
			$uri->addParams([
				'preset' => 'group-landing',
				'refresh' => 'N',
				'lid' => $this->arParams["SITE_ID"]
			]);
			$this->arResult["URL_GROUP_CREATE"] = $uri->getUri();
		}
	}

	protected function getWorkgroupDataByCode($code = '')
	{
		$result = false;

		if (preg_match('/^SG(\d+)$/', $code, $matches))
		{
			$groupId = intval($matches[1]);
			if (
				$groupId > 0
				&& ($res = \Bitrix\Socialnetwork\WorkgroupTable::getList([
					'filter' => [
						'=ID' => $groupId,
						'=LANDING' => 'Y'
					],
					'select' => [ 'ID', 'NAME']
				]))
				&& ($workGroupData = $res->fetch())
			)
			{
				$result = $workGroupData;
			}
		}

		return $result;
	}
}