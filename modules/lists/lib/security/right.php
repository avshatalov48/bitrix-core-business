<?php
namespace Bitrix\Lists\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;

class Right implements Errorable
{
	use ErrorableImplementation;

	const ACCESS_DENIED = "ACCESS_DENIED";

	private $entityRight;
	private $rightParam;

	private $listsPermission;

	public function __construct(RightParam $rightParam, RightEntity $entityRight)
	{
		$this->entityRight = $entityRight;
		$this->rightParam = $rightParam;

		$this->listsPermission = $this->getListsPermission();

		$this->entityRight->setListsPermission($this->listsPermission);

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Checks access to the entity.
	 *
	 * @param string $entityMethod Entity method to check access.
	 *
	 * @return bool
	 */
	public function checkPermission($entityMethod = "")
	{
		if ($this->listsPermission < 0)
		{
			switch ($this->listsPermission)
			{
				case \CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->setError(new Error("Invalid iblock type ID"), self::ACCESS_DENIED
					);
					break;
				case \CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->setError(new Error("Invalid list ID"), self::ACCESS_DENIED
					);
					break;
				case \CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->setError(new Error("Lists for this group is disabled", self::ACCESS_DENIED));
					break;
				default:
					$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
					break;
			}
		}

		if ($entityMethod)
		{
			if (method_exists($this->entityRight, $entityMethod))
			{
				if (!$this->entityRight->$entityMethod())
				{
					$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
				}
			}
			else
			{
				$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			}
		}

		if ($this->hasErrors())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	private function getListsPermission()
	{
		return \CListPermissions::checkAccess(
			$this->rightParam->getUser(),
			$this->rightParam->getIblockTypeId(),
			$this->rightParam->getIblockId(),
			$this->rightParam->getSocnetGroupId()
		);
	}
}