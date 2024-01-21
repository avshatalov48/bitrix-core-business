<?php

namespace Bitrix\Lists\Security;

use Bitrix\Lists\Entity\Utils;
use Bitrix\Lists\Service\Param;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

class RightParam
{
	private $user = null;
	private $iblockTypeId = '';
	private $iblockId = false;
	private $socnetGroupId = 0;
	private $entityId = 0;

	private ?int $sectionId = null;

	public function __construct(Param $param)
	{
		$params = $param->getParams();

		$this->setIblockTypeId($params["IBLOCK_TYPE_ID"]);
		$this->setIblockId(Utils::getIblockId($params));
		$this->setSocnetGroupId($params["SOCNET_GROUP_ID"]);
	}

	/**
	 * @return \CUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param \CUser $user
	 */
	public function setUser(\CUser $user)
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function getIblockTypeId()
	{
		return $this->iblockTypeId;
	}

	/**
	 * @param string $iblockTypeId
	 */
	public function setIblockTypeId($iblockTypeId)
	{
		$this->iblockTypeId = ($iblockTypeId ? $iblockTypeId : "");
	}

	/**
	 * @return bool|int
	 */
	public function getIblockId()
	{
		return $this->iblockId;
	}

	/**
	 * @param bool|int $iblockId
	 */
	public function setIblockId($iblockId)
	{
		$this->iblockId = ($iblockId ? (int)$iblockId : false);
	}

	/**
	 * @return int
	 */
	public function getSocnetGroupId()
	{
		return $this->socnetGroupId;
	}

	/**
	 * @param int $socnetGroupId
	 */
	public function setSocnetGroupId($socnetGroupId)
	{
		$this->socnetGroupId = (int)$socnetGroupId;
	}

	/**
	 * @return int
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}

	/**
	 * @param int $entityId
	 */
	public function setEntityId($entityId)
	{
		$this->entityId = (int)$entityId;
	}

	/***
	 * @return int|null
	 */
	public function getSectionId(): ?int
	{
		return $this->sectionId;
	}

	/***
	 * @param int $sectionId
	 */
	public function setSectionId(int $sectionId)
	{
		if ($sectionId >= 0)
		{
			$this->sectionId = $sectionId;
		}
	}

	/**
	 * Returns the closing status of the socnet group.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getClosedStatusSocnetGroup()
	{
		$socnetGroupId = intval($this->socnetGroupId);
		if ($socnetGroupId && Loader::includeModule("socialnetwork"))
		{
			$socnetGroup = \CSocNetGroup::getByID($socnetGroupId);
			if (
				is_array($socnetGroup) &&
				$socnetGroup["CLOSED"] == "Y" &&
				!\CSocNetUser::isCurrentUserModuleAdmin() &&
				($socnetGroup["OWNER_ID"] != $this->getUser()->getID() ||
					Option::get("socialnetwork", "work_with_closed_groups", "N") != "Y")
			)
			{
				return true;
			}
		}
		return false;
	}
}
