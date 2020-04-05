<?

namespace Bitrix\Main\UI\Selector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\FinderDestTable;
use Bitrix\Main\Loader;

class Entities
{
	const CODE_USER_REGEX = '/^U(\d+)$/i';
	const CODE_USERALL_REGEX = '/^UA$/i';
	const CODE_SONETGROUP_REGEX = '/^SG(\d+)$/i';
	const CODE_GROUP_REGEX = '/^G(\d+)$/i';
	const CODE_DEPT_REGEX = '/^D(\d+)$/i';
	const CODE_DEPTR_REGEX = '/^DR(\d+)$/i';
	const CODE_CRMCONTACT_REGEX = '/^CRMCONTACT(\d+)$/i';
	const CODE_CRMCOMPANY_REGEX = '/^CRMCOMPANY(\d+)$/i';
	const CODE_CRMLEAD_REGEX = '/^CRMLEAD(\d+)$/i';
	const CODE_CRMDEAL_REGEX = '/^CRMDEAL(\d+)$/i';

	const ENTITY_TYPE_DEPARTMENTS = 'DEPARTMENTS';

	const LIST_USER_LIMIT = 11;

	public static function getList($params = array())
	{
		$result = array();

		if (empty($params['context']))
		{
			return $result;
		}

		if (empty($params['itemsSelected']))
		{
			return $result;
		}

		$event = new Event("main", "OnUISelectorEntitiesGetList", $params);
		$event->send();
		$eventResultList = $event->getResults();

		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}

		return $result;
	}

	public static function getEntityType($params)
	{
		$result = false;

		if (
			empty($params)
			|| empty($params['itemCode'])
		)
		{
			return $result;
		}

		$itemCode = $params['itemCode'];

		if (preg_match(self::CODE_USER_REGEX, $itemCode, $matches))
		{
			$result = 'users';
		}
		elseif (preg_match(self::CODE_SONETGROUP_REGEX, $itemCode, $matches))
		{
			$result = 'sonetgroups';
		}
		elseif (
			preg_match(self::CODE_DEPT_REGEX, $itemCode, $matches)
			|| preg_match(self::CODE_DEPTR_REGEX, $itemCode, $matches)
		)
		{
			$result = 'department';
		}
		elseif (
			preg_match(self::CODE_USERALL_REGEX, $itemCode, $matches)
			|| preg_match(self::CODE_GROUP_REGEX, $itemCode, $matches)
		)
		{
			$result = 'groups';
		}

		return $result;
	}

	public static function getData($options = array(), $entityTypes = array(), $selectedItems = array())
	{
		$result = array(
			'ENTITIES' => array(),
			'SORT' => array()
		);

		$context = (!empty($options['context']) ? $options['context'] : false);

		if (
			empty($context)
			|| empty($entityTypes)
		)
		{
			return $result;
		}

		$filterParams = array(
			"DEST_CONTEXT" => $context,
			"ALLOW_EMAIL_INVITATION" => (
				(isset($options["allowEmailInvitation"]) && $options["allowEmailInvitation"] == "Y")
				|| (isset($options["allowSearchCrmEmailUsers"]) && $options["allowSearchCrmEmailUsers"] == "Y")
			)
		);

		if (!empty($options['contextCode']))
		{
			$filterParams["CODE_TYPE"] = $options['contextCode'];
		}

		$res = self::getLastSort($filterParams);
		$destSortData = $res['DATA'];
		$dataAdditional = $res['DATA_ADDITIONAL'];

		$res = self::fillLastDestination(
			$destSortData,
			array(
				"EMAILS" => (
					(
						isset($options["allowAddUser"])
						&& $options["allowAddUser"] == 'Y'
					)
					|| (
						isset($options["allowSearchEmailUsers"])
						&& $options["allowSearchEmailUsers"] == 'Y'
					)
					|| (
						isset($options["allowEmailInvitation"])
						&& $options["allowEmailInvitation"] == 'Y'
					)
						? 'Y'
						: 'N'
				),
				"CRMEMAILS" => (
					isset($options["allowSearchCrmEmailUsers"])
					&& $options["allowSearchCrmEmailUsers"] == 'Y'
						? 'Y'
						: 'N'
				),
				"DATA_ADDITIONAL" => $dataAdditional
			)
		);

		$destSortData['UA'] = array(
			'Y' => 9999999999,
			'N' => 9999999999
		);
		$destSortData['EMPTY'] = array(
			'Y' => 9999999998,
			'N' => 9999999998
		);

		$lastItems = $res['LAST_DESTINATIONS'];
		$result['SORT'] = $destSortData;

		$result['TABS'] = array();

		if (
			!isset($options["disableLast"])
			|| $options["disableLast"] != 'Y'
		)
		{
			$result['TABS']['last'] = array(
				'id' => 'last',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_LAST'),
				'sort' => 10
			);
		}

		$selectedItemsByEntityType = array();
		if (!empty($selectedItems))
		{
			foreach($selectedItems as $key => $entityType)
			{
				$entityType = strtoupper($entityType);
				if (!isset($selectedItemsByEntityType[$entityType]))
				{
					$selectedItemsByEntityType[$entityType] = array();
				}
				$selectedItemsByEntityType[$entityType][] = $key;
			}
		}

		foreach($entityTypes as $entityType => $description)
		{
			$provider = self::getProviderByEntityType($entityType);
			if ($provider !== false)
			{
				$result['ENTITIES'][$entityType] = $provider->getData(array(
					'options' => (!empty($description['options']) ? $description['options'] : array()),
					'lastItems' => $lastItems,
					'selectedItems' => $selectedItemsByEntityType
				));

				$tabList = $provider->getTabList(array(
					'options' => (!empty($description['options']) ? $description['options'] : array())
				));
				if (!empty($tabList))
				{
					foreach($tabList as $tab)
					{
						$result['TABS'][$tab['id']] = $tab;
					}
				}
			}
		}

		return $result;
	}

	public static function getProviderByEntityType($entityType)
	{
		$result = false;

		$event = new Event("main", "OnUISelectorGetProviderByEntityType", array(
			'entityType' => $entityType
		));
		$event->send();
		$eventResultList = $event->getResults();
		if (is_array($eventResultList) && !empty($eventResultList))
		{
			foreach ($eventResultList as $eventResult)
			{
				if ($eventResult->getType() == EventResult::SUCCESS)
				{
					$resultParams = $eventResult->getParameters();
					$result = $resultParams['result'];
					break;
				}
			}
		}
		return $result;
	}

	public static function getLastSort($params = array())
	{
		global $USER;

		$result = array(
			'DATA' => array(),
			'DATA_ADDITIONAL' => array()
		);

		$userId = (
			isset($params["USER_ID"])
			&& intval($params["USER_ID"]) > 0
				? intval($params["USER_ID"])
				: false
		);

		$contextFilter = (
			isset($params["CONTEXT_FILTER"])
			&& is_array($params["CONTEXT_FILTER"])
				? $params["CONTEXT_FILTER"]
				: false
		);

		$codeFilter = (
			isset($params["CODE_FILTER"])
				? $params["CODE_FILTER"]
				: false
		);

		if (
			$codeFilter
			&& !is_array($codeFilter)
		)
		{
			$codeFilter = array($codeFilter);
		}

		if (!$userId)
		{
			if ($USER->IsAuthorized())
			{
				$userId = $USER->getId();
			}
			else
			{
				return $result;
			}
		}

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'dest_sort_2'.$userId.serialize($params);
		$cacheDir = '/ui_selector/dest_sort/'.intval($userId / 100);

		$cache = new \CPHPCache;
		if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$cacheData = $cache->GetVars();
			$destAll = isset($cacheData['DEST_ALL']) ? $cacheData['DEST_ALL'] : array();
			$dataAdditionalUsers = isset($cacheData['DATA_ADDITIONAL_USERS']) ? $cacheData['DATA_ADDITIONAL_USERS'] : array();
		}
		else
		{
			$dataAdditionalUsers = array();

			$cache->startDataCache();
			$filter = array(
				"USER_ID" => $USER->getId()
			);

			if (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& (
					!isset($params["ALLOW_EMAIL_INVITATION"])
					|| !$params["ALLOW_EMAIL_INVITATION"]
				)
			)
			{
				$filter["!=CODE_USER.EXTERNAL_AUTH_ID"] = 'email';
			}

			if (!empty($params["CODE_TYPE"]))
			{
				$filter["=CODE_TYPE"] = strtoupper($params["CODE_TYPE"]);
			}
			elseif (
				!empty($params["DEST_CONTEXT"])
				&& strtoupper($params["DEST_CONTEXT"]) != 'CRM_POST'
			)
			{
				$filter["!=CODE_TYPE"] = "CRM";
			}

			if (
				is_array($contextFilter)
				&& !empty($contextFilter)
			)
			{
				$filter["CONTEXT"] = $contextFilter;
			}

			if (
				is_array($codeFilter)
				&& !empty($codeFilter)
			)
			{
				$filter["CODE"] = $codeFilter;
			}

			$runtime = array();
			$order = array();

			if (!empty($params["DEST_CONTEXT"]))
			{
				$conn = \Bitrix\Main\Application::getConnection();
				$helper = $conn->getSqlHelper();

				$runtime = array(
					new \Bitrix\Main\Entity\ExpressionField('CONTEXT_SORT', "CASE WHEN CONTEXT = '".$helper->forSql($params["DEST_CONTEXT"])."' THEN 1 ELSE 0 END")
				);

				$order = array(
					'CONTEXT_SORT' => 'DESC'
				);
			}

			$order['LAST_USE_DATE'] = 'DESC';

			$emailUserCodeList = $emailCrmUserCodeList = array();

			if (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& isset($params["ALLOW_EMAIL_INVITATION"])
				&& $params["ALLOW_EMAIL_INVITATION"]
			)
			{
				$res = FinderDestTable::getList(array(
					'order' => $order,
					'filter' => array(
						"USER_ID" => $USER->getId(),
						"=CODE_USER.EXTERNAL_AUTH_ID" => 'email',
						"=CODE_TYPE" => 'U'
					),
					'select' => array('CODE'),
					'runtime' => $runtime,
					'limit' => self::LIST_USER_LIMIT
				));
				while($dest = $res->fetch())
				{
					$emailUserCodeList[] = $dest['CODE'];
				}
				$dataAdditionalUsers['UE'] = $emailUserCodeList;
			}

			if (
				!empty($params["DEST_CONTEXT"])
				&& $params["DEST_CONTEXT"] == "CRM_POST"
			)
			{
				$res = FinderDestTable::getList(array(
					'order' => $order,
					'filter' => array(
						"USER_ID" => $USER->getId(),
						"!=CODE_USER.UF_USER_CRM_ENTITY" => false,
						"=CODE_TYPE" => 'U'
					),
					'select' => array('CODE'),
					'runtime' => $runtime,
					'limit' => self::LIST_USER_LIMIT
				));
				while($dest = $res->fetch())
				{
					$emailCrmUserCodeList[] = $dest['CODE'];
				}
				$dataAdditionalUsers['UCRM'] = $emailCrmUserCodeList;
			}

			$res = FinderDestTable::getList(array(
				'order' => $order,
				'filter' => $filter,
				'select' => array(
					'CONTEXT',
					'CODE',
					'LAST_USE_DATE'
				),
				'runtime' => $runtime
			));

			$destAll = array();

			while($dest = $res->fetch())
			{
				$dest["LAST_USE_DATE"] = MakeTimeStamp($dest["LAST_USE_DATE"]->toString());
				$destAll[] = $dest;
			}

			$cache->endDataCache(array(
				"DEST_ALL" => $destAll,
				"DATA_ADDITIONAL_USERS" => $dataAdditionalUsers
			));
		}

		$resultData = array();

		foreach ($destAll as $dest)
		{
			if(!isset($resultData[$dest["CODE"]]))
			{
				$resultData[$dest["CODE"]] = array();
			}

			$contextType = (
				isset($params["DEST_CONTEXT"])
				&& $params["DEST_CONTEXT"] == $dest["CONTEXT"]
					? "Y"
					: "N"
			);

			if (
				$contextType == "Y"
				|| !isset($resultData[$dest["CODE"]]["N"])
				|| $dest["LAST_USE_DATE"] > $resultData[$dest["CODE"]]["N"]
			)
			{
				$resultData[$dest["CODE"]][$contextType] = $dest["LAST_USE_DATE"];
			}
		}

		$result['DATA'] = $resultData;
		$result['DATA_ADDITIONAL'] = $dataAdditionalUsers;

		return $result;
	}

	public static function fillLastDestination($destSortData, $params = array())
	{
		$result = array(
			'DATA' => array(),
			'LAST_DESTINATIONS' => array()
		);

		global $USER;

		$resultData = array();

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		// initialize keys for compatibility
		$lastDestinationList = array(
			'USERS' => array(),
			'SONETGROUPS' => array(),
			'DEPARTMENT' => array(),
			'CONTACTS' => array(),
			'COMPANIES' => array(),
			'DEALS' => array(),
			'LEADS' => array(),
		);

		$iUCounter = $iSGCounter = $iDCounter = 0;
		$iCRMContactCounter = $iCRMCompanyCounter = $iCRMDealCounter = $iCRMLeadCounter = 0;
		$bCrm = (
			is_array($params)
			&& isset($params["CRM"])
			&& $params["CRM"] == "Y"
		);
		$bAllowEmail = (
			is_array($params)
			&& isset($params["EMAILS"])
			&& $params["EMAILS"] == "Y"
		);
		$bAllowCrmEmail = (
			is_array($params)
			&& isset($params["CRMEMAILS"])
			&& $params["CRMEMAILS"] == "Y"
			&& ModuleManager::isModuleInstalled('crm')
		);
		$bAllowProject = (
			is_array($params)
			&& isset($params["PROJECTS"])
			&& $params["PROJECTS"] == "Y"
		);
		$dataAdditional = (
			is_array($params)
			&& isset($params["DATA_ADDITIONAL"])
			&& is_array($params["DATA_ADDITIONAL"])
				? $params["DATA_ADDITIONAL"]
				: array()
		);

		if (is_array($destSortData))
		{
			$userIdList = $sonetGroupIdList = array();
			$userLimit = self::LIST_USER_LIMIT;
			$sonetGroupLimit = 6;
			$departmentLimit = 6;
			$crmContactLimit = $crmCompanyLimit = $crmDealLimit = $crmLeadLimit = 6;

			foreach ($destSortData as $code => $sortInfo)
			{
				if (
					!$bAllowEmail
					&& !$bAllowCrmEmail
					&& !$bAllowProject
					&& ($iUCounter >= $userLimit)
					&& $iSGCounter >= $sonetGroupLimit
					&& $iDCounter >= $departmentLimit
					&& $iCRMContactCounter >= $crmContactLimit
					&& $iCRMCompanyCounter >= $crmCompanyLimit
					&& $iCRMDealCounter >= $crmDealLimit
					&& $iCRMLeadCounter >= $crmLeadLimit
				)
				{
					break;
				}

				if (preg_match('/^U(\d+)$/i', $code, $matches))
				{
					if (
						!$bAllowEmail
						&& !$bAllowCrmEmail
						&& $iUCounter >= $userLimit
					)
					{
						continue;
					}
					if (!isset($lastDestinationList['USERS']))
					{
						$lastDestinationList['USERS'] = array();
					}
					$lastDestinationList['USERS'][$code] = $code;
					$userIdList[] = intval($matches[1]);
					$iUCounter++;
				}
				elseif (preg_match('/^SG(\d+)$/i', $code, $matches))
				{
					if (
						!$bAllowProject
						&& $iSGCounter >= $sonetGroupLimit
					)
					{
						continue;
					}
					if (!isset($lastDestinationList['SONETGROUPS']))
					{
						$lastDestinationList['SONETGROUPS'] = array();
					}
					$lastDestinationList['SONETGROUPS'][$code] = $code;
					$sonetGroupIdList[] = intval($matches[1]);
					$iSGCounter++;
				}
				elseif (
					preg_match('/^D(\d+)$/i', $code, $matches)
					|| preg_match('/^DR(\d+)$/i', $code, $matches)
				)
				{
					if ($iDCounter >= $departmentLimit)
					{
						continue;
					}
					if (!isset($lastDestinationList['DEPARTMENT']))
					{
						$lastDestinationList['DEPARTMENT'] = array();
					}
					$lastDestinationList['DEPARTMENT'][$code] = $code;
					$iDCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCONTACT(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMContactCounter >= $crmContactLimit)
					{
						continue;
					}
					if (!isset($lastDestinationList['CONTACTS']))
					{
						$lastDestinationList['CONTACTS'] = array();
					}
					$lastDestinationList['CONTACTS'][$code] = $code;
					$iCRMContactCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCOMPANY(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMCompanyCounter >= $crmCompanyLimit)
					{
						continue;
					}
					if (!isset($lastDestinationList['COMPANIES']))
					{
						$lastDestinationList['COMPANIES'] = array();
					}
					$lastDestinationList['COMPANIES'][$code] = $code;
					$iCRMCompanyCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMDEAL(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMDealCounter >= $crmDealLimit)
					{
						continue;
					}
					if (!isset($lastDestinationList['DEALS']))
					{
						$lastDestinationList['DEALS'] = array();
					}
					$lastDestinationList['DEALS'][$code] = $code;
					$iCRMDealCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMLEAD(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMLeadCounter >= $crmLeadLimit)
					{
						continue;
					}
					if (!isset($lastDestinationList['LEADS']))
					{
						$lastDestinationList['LEADS'] = array();
					}
					$lastDestinationList['LEADS'][$code] = $code;
					$iCRMLeadCounter++;
				}
			}

			if (
				(
					$bAllowEmail
					|| $bAllowCrmEmail
				)
				&& !empty($userIdList)
			)
			{
				$iUCounter = $iUECounter = $iUCRMCounter = 0;
				$emailLimit = $crmLimit = 10;
				$userId = $USER->getId();
				$destUList = $destUEList = $destUCRMList = array();

				if (
					(
						isset($dataAdditional['UE'])
						&& is_array($dataAdditional['UE'])
					)
					|| (
						isset($dataAdditional['UCRM'])
						&& is_array($dataAdditional['UCRM'])
					)
				)
				{
					if (
						empty($dataAdditional['UE'])
						&& empty($dataAdditional['UCRM'])
					)
					{
						foreach($userIdList as $uId)
						{
							$code = 'U'.$uId;
							$destUList[$code] = $code;
						}
					}
					else
					{
						foreach($userIdList as $uId)
						{
							if (
								$iUCounter >= $userLimit
								&& $iUECounter >= $emailLimit
								&& $iUCRMCounter >= $crmLimit
							)
							{
								break;
							}

							$code = 'U'.$uId;

							if (
								$bAllowEmail
								&& in_array($code, $dataAdditional['UE'])
							)
							{
								if ($iUECounter >= $emailLimit)
								{
									continue;
								}
								$destUEList[$code] = $code;
								$iUECounter++;
							}
							elseif (
								$bAllowCrmEmail
								&& in_array($code, $dataAdditional['UCRM'])
							)
							{
								if ($iUCRMCounter >= $crmLimit)
								{
									continue;
								}
								$destUCRMList[$code] = $code;
								$iUCRMCounter++;
							}
							else
							{
								if ($iUCounter >= $userLimit)
								{
									continue;
								}
								$destUList[$code] = $code;
								$iUCounter++;
							}
						}
					}
				}
				else // old method
				{
					$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
					$cacheId = 'dest_sort_users'.$userId.serialize($params).intval($bAllowCrmEmail);
					$cacheDir = '/ui_selector/dest_sort/'.intval($userId / 100);
					$cache = new \CPHPCache;

					if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
					{
						$cacheVars = $cache->getVars();
						$destUList = $cacheVars['U'];
						$destUEList = $cacheVars['UE'];
						$destUCRMList = $cacheVars['UCRM'];
					}
					else
					{
						$cache->startDataCache();

						$selectList = array('ID', 'EXTERNAL_AUTH_ID');
						if ($bAllowCrmEmail)
						{
							$selectList[] = 'UF_USER_CRM_ENTITY';
						}
						$selectList[] = new \Bitrix\Main\Entity\ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', array('\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE'));

						$res = \Bitrix\Main\UserTable::getList(array(
							'order' => array(
								"MAX_LAST_USE_DATE" => 'DESC',
							),
							'filter' => array(
								'@ID' => $userIdList
							),
							'select' => $selectList
						));

						while ($destUser = $res->fetch())
						{
							if (
								$iUCounter >= $userLimit
								&& $iUECounter >= $emailLimit
								&& $iUCRMCounter >= $crmLimit
							)
							{
								break;
							}

							$code = 'U'.$destUser['ID'];

							if ($bAllowEmail && $destUser['EXTERNAL_AUTH_ID'] == 'email')
							{
								if ($iUECounter >= $emailLimit)
								{
									continue;
								}
								$destUEList[$code] = $code;
								$iUECounter++;
							}
							elseif (
								$bAllowCrmEmail
								&& !empty($destUser['UF_USER_CRM_ENTITY'])
							)
							{
								if ($iUCRMCounter >= $crmLimit)
								{
									continue;
								}
								$destUCRMList[$code] = $code;
								$iUCRMCounter++;
							}
							else
							{
								if ($iUCounter >= $userLimit)
								{
									continue;
								}
								$destUList[$code] = $code;
								$iUCounter++;
							}
						}

						$cache->endDataCache(array(
							'U' => $destUList,
							'UE' => $destUEList,
							'UCRM' => $destUCRMList
						));
					}
				}

				$lastDestinationList['USERS'] = array_merge($destUList, $destUEList, $destUCRMList);
				$tmp = array('USERS' => $lastDestinationList['USERS']);
				self::sortDestinations($tmp, $destSortData);
				$lastDestinationList['USERS'] = $tmp['USERS'];
			}

			if (
				$bAllowProject
				&& !empty($sonetGroupIdList)
			)
			{
				$iSGCounter = $iSGPCounter = 0;
				$projectLimit = 10;
				$userId = $USER->getId();

				$destSGList = $destSGPList = array();

				$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
				$cacheId = 'dest_sort_sonetgroups'.$userId.serialize($params);
				$cacheDir = '/ui_selector/dest_sort/'.intval($userId / 100);
				$cache = new \CPHPCache;

				if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
				{
					$cacheVars = $cache->getVars();
					$destSGList = $cacheVars['SG'];
					$destSGPList = $cacheVars['SGP'];
				}
				else
				{
					$cache->startDataCache();

					$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
						'filter' => array(
							'@ID' => $sonetGroupIdList
						),
						'select' => array('ID', 'PROJECT')
					));

					while($destSonetGroup = $res->fetch())
					{
						if (
							$iSGCounter >= $sonetGroupLimit
							&& $iSGPCounter >= $projectLimit
						)
						{
							break;
						}

						$code = 'SG'.$destSonetGroup['ID'];

						if ($destSonetGroup['PROJECT'] == 'Y')
						{
							if ($iSGPCounter >= $projectLimit)
							{
								continue;
							}
							$destSGPList[$code] = $code;
							$iSGPCounter++;
						}
						else
						{
							if ($iSGCounter >= $sonetGroupLimit)
							{
								continue;
							}
							$destSGList[$code] = $code;
							$iSGCounter++;
						}
					}

					$cache->endDataCache(array(
						'SG' => $destSGList,
						'SGP' => $destSGPList
					));
				}

				$tmp = array(
					'SONETGROUPS' => $destSGList,
					'PROJECTS' => $destSGPList
				);
				self::sortDestinations($tmp, $destSortData);
				$lastDestinationList['SONETGROUPS'] = $tmp['SONETGROUPS'];
				$lastDestinationList['PROJECTS'] = $tmp['PROJECTS'];
			}
		}

		foreach($lastDestinationList as $groupKey => $entitiesList)
		{
			$result[$groupKey] = array();

			if (is_array($entitiesList))
			{
				$tmp = array();
				$sort = 0;
				foreach($entitiesList as $key => $value)
				{
					$tmp[$key] = $sort++;
				}
				$result[$groupKey] = $tmp;
			}
		}

		$result['DATA'] = $resultData;
		$result['LAST_DESTINATIONS'] = $lastDestinationList;

		return $result;
	}

	private static function compareDestinations($a, $b)
	{
		if(!is_array($a) && !is_array($b))
		{
			return 0;
		}
		elseif(is_array($a) && !is_array($b))
		{
			return -1;
		}
		elseif(!is_array($a) && is_array($b))
		{
			return 1;
		}
		else
		{
			if(isset($a["SORT"]["Y"]) && !isset($b["SORT"]["Y"]))
			{
				return -1;
			}
			elseif(!isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				return 1;
			}
			elseif(isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				if(intval($a["SORT"]["Y"]) > intval($b["SORT"]["Y"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["Y"]) < intval($b["SORT"]["Y"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				if(intval($a["SORT"]["N"]) > intval($b["SORT"]["N"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["N"]) < intval($b["SORT"]["N"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
		}
	}

	private static function sortDestinations(&$destinationList, $destSortData)
	{
		foreach($destinationList as $type => $dest)
		{
			if (is_array($dest))
			{
				foreach($dest as $key => $value)
				{
					if (isset($destSortData[$key]))
					{
						$destinationList[$type][$key] = array(
							"VALUE" => $value,
							"SORT" => $destSortData[$key]
						);
					}
				}

				uasort($destinationList[$type], array(__CLASS__, 'compareDestinations'));
			}
		}

		foreach($destinationList as $type => $dest)
		{
			if (is_array($dest))
			{
				foreach($dest as $key => $val)
				{
					if (is_array($val))
					{
						$destinationList[$type][$key] = $val["VALUE"];
					}
				}
			}
		}
	}

	public static function search($options = array(), $entityTypes = array(), $requestFields = array())
	{
		$result = array(
			'ENTITIES' => array()
		);

		foreach($entityTypes as $entityType => $description)
		{
			$provider = self::getProviderByEntityType($entityType);
			if ($provider !== false)
			{
				$options = (!empty($description['options']) ? $description['options'] : array());
				if (
					!empty($requestFields['additionalData'])
					&& !empty($requestFields['additionalData'][$entityType])
				)
				{
					$options['additionalData'] = $requestFields['additionalData'][$entityType];
				}

				$result['ENTITIES'][$entityType] = $provider->search(array(
					'options' => $options,
					'requestFields' => $requestFields
				));
			}
		}

		return $result;
	}

	public static function loadAll($entityType)
	{
		$result = array();

		if (empty($entityType))
		{
			$entityType = 'USERS';
		}

		$provider = self::getProviderByEntityType($entityType);
		if($provider !== false)
		{
			$result[$entityType] = $provider->loadAll();
		}

		return $result;
	}


}