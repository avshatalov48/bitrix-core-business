<?php
namespace Bitrix\Sale\BsmSiteMaster\Tools;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PersonTypePreparer
 * @package Bitrix\Sale\BsmSiteMaster\Tools
 */
class PersonTypePreparer
{
	/** @var array $errors */
	private $errors = [];

	/**
	 * @param string $error
	 */
	private function setError($error)
	{
		$this->errors[] = $error;
	}

	/**
	 * @return array errors
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Set business values for person types
	 *
	 * @param $siteId
	 * @param $personTypeList
	 * @return bool
	 */
	public function preparePersonType($siteId, $personTypeList)
	{
		try
		{
			$this->addSitesToPersonType($personTypeList, $siteId);
		}
		catch (\Exception $ex)
		{
			$this->setError($ex->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPersonTypeList()
	{
		$personTypeList = Sale\PersonType::getList([
			"select" => ["ID"]
		])->fetchAll();

		$result = [];
		foreach ($personTypeList as $personType)
		{
			$result[] = $personType["ID"];
		}

		return $result;
	}

	/**
	 * @param $personTypeId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCurrentPersonTypeSite($personTypeId)
	{
		$site = array();

		$res = Sale\PersonType::getList(array(
			'select' => array("SITE_ID" => "PERSON_TYPE_SITE.SITE_ID"),
			'filter' => array("=ID" => $personTypeId)
		));

		while ($personType = $res->fetch())
		{
			$site[] = $personType["SITE_ID"];
		}

		return $site;
	}

	/**
	 * @param $personTypeId
	 * @param $siteId
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	private function addSiteToPersonType($personTypeId, $siteId)
	{
		$personTypeId = intval($personTypeId);
		if ($personTypeId <= 0)
		{
			throw new Main\ArgumentNullException("personTypeId");
		}

		$siteId = trim($siteId);
		if (mb_strlen($siteId) !== 2)
		{
			throw new Main\ArgumentException();
		}

		$currentSite = $this->getCurrentPersonTypeSite($personTypeId);

		if (!in_array($siteId, $currentSite))
		{
			$newSiteCollection = Main\SiteTable::getList([
				"select" => ["LID"],
				"filter" => [
					"LID" => array_merge($currentSite, array($siteId))
				]
			])->fetchCollection();

			$newSite = $newSiteCollection->getLidList();
			if ($newSite)
			{
				$this->deleteSitesFromPersonType($personTypeId);
				foreach ($newSite as $site)
				{
					Sale\Internals\PersonTypeSiteTable::add([
						"PERSON_TYPE_ID" => $personTypeId,
						"SITE_ID" => $site
					]);
				}
			}
		}
	}

	/**
	 * @param array $personTypes
	 * @param $siteId
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function addSitesToPersonType(array $personTypes, $siteId)
	{
		foreach ($personTypes as $personTypeId)
		{
			$this->addSiteToPersonType($personTypeId, $siteId);
		}
	}

	/**
	 * @param $personTypeId
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	private function deleteSitesFromPersonType($personTypeId)
	{
		$personTypeId = intval($personTypeId);
		if ($personTypeId <= 0)
		{
			throw new Main\ArgumentNullException("personTypeId");
		}

		$personTypeSites = Sale\Internals\PersonTypeSiteTable::getList([
			"select" => ["SITE_ID"],
			"filter" => ["PERSON_TYPE_ID" => $personTypeId]
		])->fetchAll();

		if ($personTypeSites)
		{
			foreach ($personTypeSites as $personTypeSite)
			{
				Sale\Internals\PersonTypeSiteTable::delete(
					[
						"PERSON_TYPE_ID" => $personTypeId,
						"SITE_ID" => $personTypeSite["SITE_ID"],
					]
				);
			}
		}
	}
}