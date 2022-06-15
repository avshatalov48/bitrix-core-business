<?
namespace Bitrix\Lists\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;

class IblockRight implements RightEntity, Errorable
{
	use ErrorableImplementation;

	const ACCESS_DENIED = "ACCESS_DENIED";

	const READ = "canRead";
	const EDIT = "canEdit";

	private $listsPermission;
	private $rightParam;

	public function __construct(RightParam $rightParam)
	{
		$this->rightParam = $rightParam;

		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Sets the access label that is needed to verify the rights of the entity.
	 *
	 * @param string $listsPermission Access label.
	 */
	public function setListsPermission($listsPermission)
	{
		$this->listsPermission = $listsPermission;
	}

	/**
	 * Checks the read access to the iblock.
	 *
	 * @return bool
	 */
	public function canRead()
	{
		if (
			$this->listsPermission < \CListPermissions::CAN_READ
			&& !(
				\CIBlockRights::userHasRightTo(
					$this->rightParam->getIblockId(),
					$this->rightParam->getIblockId(),
					'element_read'
				)
			)
		)
		{
			$this->errorCollection->setError(new Error('Access denied', self::ACCESS_DENIED));

			return false;
		}

		return true;
	}

	/**
	 * Checks the edit access to the iblock.
	 *
	 * @return bool
	 */
	public function canEdit()
	{
		if(
			(
				$this->rightParam->getIblockId() &&
				$this->listsPermission < \CListPermissions::IS_ADMIN &&
				!\CIBlockRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getIblockId(), "iblock_edit")
			) ||
			(
				!$this->rightParam->getIblockId() && $this->listsPermission < \CListPermissions::IS_ADMIN
			)
		)
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
		return true;
	}
}