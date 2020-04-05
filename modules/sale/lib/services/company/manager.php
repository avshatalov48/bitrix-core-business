<?php
namespace Bitrix\Sale\Services\Company;

use Bitrix\Main;
use Bitrix\Sale\Internals;

class Manager
{
	/**
	 * @param $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList($parameters)
	{
		return Internals\CompanyTable::getList($parameters);
	}

	/**
	 * @param $id
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getById($id)
	{
		return Internals\CompanyTable::getById($id);
	}

	/**
	 * @param Internals\Entity $entity
	 * @param int $mode
	 * @return array
	 */
	public static function getListWithRestrictions(Internals\Entity $entity, $mode = Restrictions\Manager::MODE_CLIENT)
	{
		$result = array();

		$dbRes = self::getList(array(
			'filter' => array('ACTIVE' => 'Y')
		));

		while ($company = $dbRes->fetch())
		{
			if ($mode == Restrictions\Manager::MODE_MANAGER)
			{
				$checkServiceResult = Restrictions\Manager::checkService($company['ID'], $entity, $mode);
				if ($checkServiceResult != Restrictions\Manager::SEVERITY_STRICT)
				{
					if ($checkServiceResult == Restrictions\Manager::SEVERITY_SOFT)
						$company['RESTRICTED'] = $checkServiceResult;
					$result[$company['ID']] = $company;
				}
			}
			else if ($mode == Restrictions\Manager::MODE_CLIENT)
			{
				if (Restrictions\Manager::checkService($company['ID'], $entity, $mode) === Restrictions\Manager::SEVERITY_NONE)
					$result[$company['ID']] = $company;
			}
		}

		return $result;
	}

	/**
	 * @param Internals\Entity $entity
	 * @param int $mode
	 * @return int
	 */
	public static function getAvailableCompanyIdByEntity(Internals\Entity $entity, $mode = Restrictions\Manager::MODE_CLIENT)
	{
		$dbRes = self::getList(array(
			'select' => array('ID'),
			'filter' => array('=ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC')
		));

		while ($company = $dbRes->fetch())
		{
			$result = Restrictions\Manager::checkService($company['ID'], $entity, $mode);
			if ($mode == Restrictions\Manager::MODE_CLIENT)
			{
				if ($result == Restrictions\Manager::SEVERITY_NONE)
					return $company['ID'];
			}
			else
			{
				if ($result != Restrictions\Manager::SEVERITY_STRICT)
					return $company['ID'];
			}
		}

		return 0;
	}

	/**
	 * Returns entity link name for connection with Locations
	 * @return string
	 */
	public static function getLocationConnectorEntityName()
	{
		return	'Bitrix\Sale\Internals\CompanyLocation';
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	public static function getUserCompanyList($id)
	{
		static $list = array();

		if (empty($list[$id]))
		{
			$list[$id] = array();

			$groups = \CUser::GetUserGroup($id);

			$filterCompany = array(
				'select' => array(
					'ID',
				),
				'filter' => array(
					'=GROUP.GROUP_ID' => $groups
				),
				'runtime' => array(
					new Main\Entity\ReferenceField(
						'GROUP',
						'\Bitrix\Sale\Internals\CompanyGroupTable',
						array(
							'=this.ID' => 'ref.COMPANY_ID',
						)
					)
				),
				'order'  => array('ID'),
			);

			$resCompany = Internals\CompanyTable::getList($filterCompany);
			while($companyData = $resCompany->fetch())
			{
				$list[$id][] = $companyData['ID'];
			}
		}

		return $list[$id];
	}
}