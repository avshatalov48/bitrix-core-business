<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Socialnetwork\UserTagTable;

class UserUtils
{
	/**
	 * @param array $fields
	 * @return array
	 */
	public static function getUserSearchFilter(array $fields)
	{
		$result = array();

		if (UserIndexTable::getEntity()->fullTextIndexEnabled('SEARCH_USER_CONTENT'))
		{
			$find = '';
			$findDepartmentOnly = false;

			if (array_key_exists('FIND', $fields))
			{
				$find = trim($fields['FIND']);

				if (Search\Content::isIntegerToken($find))
				{
					$find = Search\Content::prepareIntegerToken($find);
				}
				else
				{
					$find = Search\Content::prepareStringToken($find);
				}
			}
			else
			{
				$validFields = Array('ID' => 1, 'NAME' => 1, 'LAST_NAME' => 1, 'SECOND_NAME' => 1, 'WORK_POSITION' => 1);
				foreach ($fields as $key => $value)
				{
					if (isset($validFields[$key]) && $validFields[$key])
					{
						if (Search\Content::isIntegerToken($value))
						{
							$find .= ' '.Search\Content::prepareIntegerToken($value);
						}
						else
						{
							$find .= ' '.Search\Content::prepareStringToken($value);
						}
						$find = trim($find);
					}
				}

				if (array_key_exists('UF_DEPARTMENT_NAME', $fields))
				{
					if (!$find)
					{
						$findDepartmentOnly = true;
					}
					if (Search\Content::isIntegerToken($fields['UF_DEPARTMENT_NAME']))
					{
						$find .= ' '.Search\Content::prepareIntegerToken($fields['UF_DEPARTMENT_NAME']);
					}
					else
					{
						$find .= ' '.Search\Content::prepareStringToken($fields['UF_DEPARTMENT_NAME']);
					}
					$find = trim($find);
				}
			}

			if (Search\Content::canUseFulltextSearch($find, Search\Content::TYPE_MIXED))
			{
				$fiendField = $findDepartmentOnly? '*INDEX.SEARCH_DEPARTMENT_CONTENT': '*INDEX.SEARCH_USER_CONTENT';
				$result[$fiendField] = $find;
			}
		}
		else
		{
			$helper = Application::getConnection()->getSqlHelper();
			if (array_key_exists('FIND', $fields))
			{
				$find = trim($fields['FIND']);
				$find = explode(' ', $find);
				foreach ($find as $findWord)
				{
					if (!$findWord)
					{
						continue;
					}

					$intResult = Array('LOGIC' => 'OR');
					$validFields = Array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'WORK_POSITION', 'UF_DEPARTMENT_NAME');
					foreach ($validFields as $key)
					{
						if ($key == 'ID')
						{
							$intResult['=ID'] = intval($findWord);
						}
						else
						{
							$intResult['%=INDEX.'.$key] = $helper->forSql($findWord).'%';
						}
					}
					$result[] = $intResult;
				}
				if (!empty($result))
				{
					$result['LOGIC'] = 'AND';
					$result = Array($result);
				}
			}
			else
			{
				$validFields = Array('ID' => 1, 'NAME' => 1, 'LAST_NAME' => 1, 'SECOND_NAME' => 1, 'WORK_POSITION' => 1, 'UF_DEPARTMENT_NAME' => 1);
				foreach ($fields as $key => $value)
				{
					if (!$value)
					{
						continue;
					}
					if (isset($validFields[$key]))
					{
						if ($key == 'ID')
						{
							$result['=ID'] = intval($value);
						}
						else
						{
							$result['%=INDEX.'.$key] = $helper->forSql($value).'%';
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	public static function getAdminSearchFilter(array $fields)
	{
		$result = array();

		if (UserIndexTable::getEntity()->fullTextIndexEnabled('SEARCH_ADMIN_CONTENT'))
		{
			$find = '';
			if (array_key_exists('FIND', $fields))
			{
				$find = trim($fields['FIND']);

				if (Search\Content::isIntegerToken($find))
				{
					$find = Search\Content::prepareIntegerToken($find);
				}
				else
				{
					$find = Search\Content::prepareStringToken($find);
				}
			}
			else
			{
				$validFields = Array('ID' => 1, 'NAME' => 1, 'LAST_NAME' => 1, 'SECOND_NAME' => 1, 'WORK_POSITION' => 1, 'EMAIL' => 1, 'LOGIN' => 1);
				foreach ($fields as $key => $value)
				{
					if (isset($validFields[$key]) && $validFields[$key])
					{
						if (Search\Content::isIntegerToken($value))
						{
							$find .= ' '.Search\Content::prepareIntegerToken($value);
						}
						else
						{
							$find .= ' '.Search\Content::prepareStringToken($value);
						}
						$find = trim($find);
					}
				}
			}

			if (Search\Content::canUseFulltextSearch($find, Search\Content::TYPE_MIXED))
			{
				$result['*INDEX.SEARCH_ADMIN_CONTENT'] = $find;
			}
		}
		else
		{
			$helper = Application::getConnection()->getSqlHelper();
			if (array_key_exists('FIND', $fields))
			{
				$find = trim($fields['FIND']);
				$find = explode(' ', $find);
				foreach ($find as $findWord)
				{
					if (!$findWord)
					{
						continue;
					}

					$intResult = Array('LOGIC' => 'OR');
					$validFields = Array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'WORK_POSITION', 'LOGIN', 'EMAIL');
					foreach ($validFields as $key)
					{
						if ($key == 'ID')
						{
							$intResult['=ID'] = intval($findWord);
						}
						else if ($key == 'LOGIN' || $key == 'EMAIL')
						{
							$intResult['%='.$key] = $helper->forSql($findWord).'%';
						}
						else
						{
							$intResult['%=INDEX.'.$key] = $helper->forSql($findWord).'%';
						}
					}
					$result[] = $intResult;
				}
				if (!empty($result))
				{
					$result['LOGIC'] = 'AND';
					$result = Array($result);
				}
			}
			else
			{
				$validFields = Array('ID' => 1, 'NAME' => 1, 'LAST_NAME' => 1, 'SECOND_NAME' => 1, 'WORK_POSITION' => 1, 'LOGIN' => 1, 'EMAIL' => 1);
				foreach ($fields as $key => $value)
				{
					if (!$value)
					{
						continue;
					}
					if (isset($validFields[$key]))
					{
						if ($key == 'ID')
						{
							$result['=ID'] = intval($value);
						}
						else if ($key == 'LOGIN' || $key == 'EMAIL')
						{
							$result['%='.$key] = $helper->forSql($value).'%';
						}
						else
						{
							$result['%=INDEX.'.$key] = $helper->forSql($value).'%';
						}
					}
				}
			}
		}

		return $result;
	}

	public static function getGroupIds($userId)
	{
		return UserTable::getUserGroupIds($userId);
	}

	/**
	 * @param $departmentId
	 *
	 * @return bool|mixed
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @deprecated Use \Bitrix\HumanResources\Service\Container::getNodeRepository()->findAllByAccessCodes($accessCodes)
	 * instead. $accessCodes need to be an array of strings like ['D1', 'D2', ...].
	 */
	public static function getDepartmentName($departmentId)
	{
		$result = self::getDepartmentNames(Array($departmentId));
		return $result[0] ?? false;
	}

	/**
	 * @param array $departmentIds
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @deprecated Use \Bitrix\HumanResources\Service\Container::getNodeRepository()->findAllByAccessCodes($accessCodes)
	 * instead. $accessCodes need to be an array of strings like ['D1', 'D2', ...].
	 */
	public static function getDepartmentNames($departmentIds)
	{
		// todo метод на новый апи
		$result = Array();
		if (!ModuleManager::isModuleInstalled('intranet'))
		{
			return $result;
		}

		$cacheTtl = 2592000;
		$cacheName = 'iblock_structure';
		$cachePath = '/bx/user/company/structure';
		$iblockStructureId = Config\Option::get('intranet', 'iblock_structure', 0);

		$taggedCache = Application::getInstance()->getTaggedCache();

		$companyStructure = Array();
		$cache = Data\Cache::createInstance();
		if ($cache->initCache($cacheTtl, $cacheName, $cachePath) && false)
		{
			$companyStructure = $cache->getVars();
		}
		else if ($iblockStructureId <= 0 || !Loader::includeModule('iblock'))
		{
			return $result;
		}
		else
		{
			$orm = \Bitrix\Iblock\SectionTable::getList(Array('select' => Array('ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'), 'filter' => Array('=IBLOCK_ID' => $iblockStructureId, '=ACTIVE' => 'Y',),));
			while ($department = $orm->fetch())
			{
				$companyStructure[$department['ID']] = $department;
			}

			$taggedCache->startTagCache($cachePath);
			$taggedCache->registerTag('iblock_id_'.$iblockStructureId);
			$taggedCache->endTagCache();

			if ($cache->startDataCache())
			{
				$cache->endDataCache($companyStructure);
			}
		}

		if (is_array($departmentIds) && !empty($departmentIds))
		{
			foreach ($departmentIds as $id)
			{
				if (!array_key_exists($id, $companyStructure))
				{
					continue;
				}

				$result[] = $companyStructure[$id];
			}
		}

		return $result;
	}

	public static function getCountryValue(array $params = [])
	{
		static $countriesList = null;
		$result = '';

		$value = (isset($params['VALUE']) ? intval($params['VALUE']) : 0);
		if ($value <= 0)
		{
			return $result;
		}

		if ($countriesList === null)
		{
			$countriesList = [];
			$countries = getCountryArray();
			foreach($countries['reference_id'] as $key => $countryId)
			{
				$countriesList[$countryId] = $countries['reference'][$key];
			}
		}

		if (isset($countriesList[$value]))
		{
			$result = $countriesList[$value];
		}

		return $result;
	}

	public static function getUFContent($userId)
	{
		global $USER_FIELD_MANAGER;

		static $supportedUserFieldTypeIDs = [
			'address',
			'string',
			'integer',
			'double',
			'boolean',
//			'date',
//			'datetime',
			'enumeration',
			'employee',
			'file',
			'url',
			'crm',
			'crm_status',
			'iblock_element',
			'iblock_section'
		];

		$ufList = $USER_FIELD_MANAGER->getUserFields(UserTable::getUfId(), $userId, LANGUAGE_ID, false);

		$userTypeMap = array_fill_keys($supportedUserFieldTypeIDs, true);
		foreach($ufList as $key => $userField)
		{
			if(
				!isset($userTypeMap[$userField['USER_TYPE_ID']])
				|| $userField['EDIT_IN_LIST'] === "N"
				|| $userField['IS_SEARCHABLE'] !== 'Y'
			)
			{
				unset($ufList[$key]);
			}
		}
		$ufList = self::postFilterFields($ufList);

		$ufValuesList = [];
		foreach($ufList as $userField)
		{
			$ufValuesList[] = self::getUserFieldValue($userField);
		}

		return implode(' ', $ufValuesList);
	}

	private static function postFilterFields(array $fields)
	{
		static $ufReserved = [
			'UF_DEPARTMENT',
			'UF_USER_CRM_ENTITY',
			'UF_PUBLIC',
			'UF_TIMEMAN',
			'UF_TM_REPORT_REQ',
			'UF_TM_FREE',
			'UF_REPORT_PERIOD',
			'UF_1C',
			'UF_TM_ALLOWED_DELTA',
			'UF_SETTING_DATE',
			'UF_LAST_REPORT_DATE',
			'UF_DELAY_TIME',
			'UF_TM_REPORT_DATE',
			'UF_TM_DAY',
			'UF_TM_TIME',
			'UF_TM_REPORT_TPL',
			'UF_TM_MIN_DURATION',
			'UF_TM_MIN_FINISH',
			'UF_TM_MAX_START',
			'UF_CONNECTOR_MD5',
			'UF_WORK_BINDING',
			'UF_IM_SEARCH',
			'UF_BXDAVEX_CALSYNC',
			'UF_BXDAVEX_MLSYNC',
			'UF_UNREAD_MAIL_COUNT',
			'UF_BXDAVEX_CNTSYNC',
			'UF_BXDAVEX_MAILBOX',
			'UF_VI_PASSWORD',
			'UF_VI_BACKPHONE',
			'UF_VI_PHONE',
			'UF_VI_PHONE_PASSWORD'
		];

		foreach ($ufReserved as $ufId)
		{
			if (isset($fields[$ufId]))
			{
				unset($fields[$ufId]);
			}
		}

		return $fields;
	}

	private static function getUserFieldValue(array $userField)
	{
		global $USER_FIELD_MANAGER;

		$userTypeID = $userField['USER_TYPE_ID'] ?? '';
		if($userTypeID === 'boolean')
		{
			$values = [];
			if(isset($userField['VALUE']) && (bool)$userField['VALUE'] && isset($userField['EDIT_FORM_LABEL']))
			{
				$values[] = $userField['EDIT_FORM_LABEL'];
			}
		}
		else
		{
			$values = explode(',', $USER_FIELD_MANAGER->getPublicText($userField));
		}

		return implode(' ', $values);
	}

	public static function getTagsContent($userId)
	{
		$result = '';

		if (Loader::includeModule('socialnetwork'))
		{
			$tagsList = [];

			$res = UserTagTable::getList([
				'filter' => [
					'USER_ID' => $userId
				],
				'select' => [ 'NAME' ]
			]);
			while($tagFields = $res->fetch())
			{
				$tagsList[] = $tagFields['NAME'];
			}

			$result = implode(' ', $tagsList);
		}

		return $result;
	}
}
