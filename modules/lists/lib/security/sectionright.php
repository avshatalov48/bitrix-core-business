<?
namespace Bitrix\Lists\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;

class SectionRight implements RightEntity, Errorable
{
	use ErrorableImplementation;

	const ACCESS_DENIED = "ACCESS_DENIED";

	const ADD = "canAdd";
	const READ = "canRead";
	const EDIT = "canEdit";
	const DELETE = "canDelete";

	private $listsPermission;
	private $rightParam;
	private $socnetGroupClosed = false;

	public function __construct(RightParam $rightParam)
	{
		$this->rightParam = $rightParam;

		$this->socnetGroupClosed = $this->rightParam->getClosedStatusSocnetGroup();

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
	 * Checks the read access to the section.
	 *
	 * @return bool
	 */
	public function canRead()
	{
		if(
			$this->listsPermission < \CListPermissions::CAN_WRITE &&
			!\CIBlockSectionRights::userHasRightTo(
				$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "section_read")
		)
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
		return true;
	}

	/**
	 * Checks the edit access to the section.
	 *
	 * @return bool
	 */
	public function canEdit()
	{
		$canEdit = (
			!$this->socnetGroupClosed &&
			(
				($this->listsPermission >= \CListPermissions::CAN_WRITE) ||
				\CIBlockSectionRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "section_edit")
			)
		);

		if ($canEdit)
		{
			return true;
		}
		else
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
	}

	/**
	 * Checks the add access to the section.
	 *
	 * @return bool
	 */
	public function canAdd()
	{
		$canAdd = (
			!$this->socnetGroupClosed &&
			(
				($this->listsPermission >= \CListPermissions::CAN_WRITE) ||
				\CIBlockSectionRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "section_section_bind")
			)
		);

		if ($canAdd)
		{
			return true;
		}
		else
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
	}

	/**
	 * Checks the delete access to the section.
	 *
	 * @return bool
	 */
	public function canDelete()
	{
		$canDelete = (
			!$this->socnetGroupClosed &&
			(
				($this->listsPermission >= \CListPermissions::CAN_WRITE) ||
				\CIBlockSectionRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "section_delete")
			)
		);

		if ($canDelete)
		{
			return true;
		}
		else
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
	}
}