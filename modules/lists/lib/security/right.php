<?php
namespace Bitrix\Lists\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Localization\Loc;

class Right implements Errorable
{
	use ErrorableImplementation;

	const ACCESS_DENIED = 'ACCESS_DENIED';

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
	public function checkPermission($entityMethod = '')
	{
		if ($this->listsPermission < 0)
		{
			switch ($this->listsPermission)
			{
				case \CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errorCollection->setError(
						new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_WRONG_IBLOCK_TYPE')),
						self::ACCESS_DENIED
					);
					break;
				case \CListPermissions::WRONG_IBLOCK:
					$this->errorCollection->setError(
						new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_WRONG_IBLOCK')),
						self::ACCESS_DENIED
					);
					break;
				case \CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errorCollection->setError(
						new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_SONET_GROUP_DISABLED'),
							self::ACCESS_DENIED)
					);
					break;
				default:
					$this->errorCollection->setError(
						new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_ACCESS_DENIED'),
							self::ACCESS_DENIED)
					);
					break;
			}
		}

		if ($entityMethod)
		{
			if (method_exists($this->entityRight, $entityMethod))
			{
				if (!$this->entityRight->$entityMethod())
				{
					$this->errorCollection->setError(
						new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_ACCESS_DENIED'),
							self::ACCESS_DENIED)
					);
				}
			}
			else
			{
				$this->errorCollection->setError(
					new Error(Loc::getMessage('LISTS_LIB_SECURITY_RIGHT_ERROR_ACCESS_DENIED'),
						self::ACCESS_DENIED)
				);
			}
		}

		if ($this->hasErrors())
		{
			return false;
		}

		return true;
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

	public function getPermission(): int|string
	{
		return $this->listsPermission;
	}
}
