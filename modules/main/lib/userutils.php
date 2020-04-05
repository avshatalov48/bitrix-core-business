<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main;

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
	 * @return bool|mixed
	 */
	public static function getDepartmentName($departmentId)
	{
		$result = self::getDepartmentNames(Array($departmentId));
		return isset($result[0])? $result[0]: false;
	}

	/**
	 * @param array $departmentIds
	 * @return array
	 */
	public static function getDepartmentNames($departmentIds)
	{
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
}
