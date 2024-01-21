<?php

namespace Bitrix\Lists\Security;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;

class ElementRight implements RightEntity, Errorable
{
	use ErrorableImplementation;

	const ACCESS_DENIED = "ACCESS_DENIED";

	const ADD = "canAdd";
	const READ = "canRead";
	const EDIT = "canEdit";
	const DELETE = "canDelete";
	const FULL_EDIT = "canFullEdit";
	public const EDIT_RIGHTS = 'canEditRights';

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
	 * Checks the read access to the element.
	 *
	 * @return bool
	 */
	public function canRead()
	{
		if (
			(
				$this->rightParam->getEntityId() &&
				$this->listsPermission < \CListPermissions::CAN_READ &&
				!\CIBlockElementRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "element_read")
			) ||
			(
				!$this->rightParam->getEntityId() &&
				$this->listsPermission < \CListPermissions::CAN_READ &&
				!\CIBlockSectionRights::userHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), "element_read")
			)
		)
		{
			$this->errorCollection->setError(new Error("Access denied", self::ACCESS_DENIED));
			return false;
		}
		return true;
	}

	/**
	 * Checks the edit access to the element.
	 *
	 * @return bool
	 */
	public function canEdit()
	{
		$sectionId = $this->rightParam->getSectionId() ?? $this->rightParam->getEntityId(); // compatibility

		$canEdit = (
			!$this->socnetGroupClosed && ((
				$this->rightParam->getEntityId() > 0 &&
				(
					$this->listsPermission >= \CListPermissions::CAN_WRITE ||
					\CIBlockElementRights::UserHasRightTo(
						$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), 'element_edit')
				)
			)
			|| (
				$this->rightParam->getEntityId() == 0
				&& (
					$this->listsPermission >= \CListPermissions::CAN_WRITE ||
					\CIBlockSectionRights::UserHasRightTo(
						$this->rightParam->getIblockId(), $sectionId, 'section_element_bind')
				)
			))
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
	 * Checks the add access to the element.
	 *
	 * @return bool
	 */
	public function canAdd()
	{
		$sectionId = $this->rightParam->getSectionId() ?? $this->rightParam->getEntityId(); // compatibility

		$canAdd = (
			!$this->socnetGroupClosed &&
			(
				$this->listsPermission > \CListPermissions::CAN_READ
				|| \CIBlockSectionRights::UserHasRightTo(
					$this->rightParam->getIblockId(), $sectionId, 'section_element_bind'
				)
			)
		);

		if ($canAdd)
		{
			return true;
		}
		else
		{
			$this->errorCollection->setError(new Error('Access denied', self::ACCESS_DENIED));

			return false;
		}
	}

	/**
	 * Checks the delete access to the element.
	 *
	 * @return bool
	 */
	public function canDelete()
	{
		$canDelete = (
			!$this->socnetGroupClosed
			&& $this->rightParam->getEntityId()
			&& (
				$this->listsPermission >= \CListPermissions::CAN_WRITE
				|| \CIBlockElementRights::UserHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getEntityId(), 'element_delete'
				)
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

	/**
	 * Checks the full edit access to the element.
	 *
	 * @return bool
	 */
	public function canFullEdit()
	{
		$canFullEdit = (
			!$this->socnetGroupClosed
			&& (
				$this->listsPermission >= \CListPermissions::IS_ADMIN
				|| \CIBlockRights::UserHasRightTo(
					$this->rightParam->getIblockId(), $this->rightParam->getIblockId(), 'iblock_edit'
				)
			)
		);

		if ($canFullEdit)
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
	 * Checks the edit rights access to the element
	 */
	public function canEditRights()
	{
		$canEditRights = (
			!$this->socnetGroupClosed
			&& (
				(
					$this->rightParam->getEntityId() > 0
					&& \CIBlockElementRights::UserHasRightTo(
						$this->rightParam->getIblockId(),
						$this->rightParam->getEntityId(),
						'element_rights_edit'
					)
				)
				|| (
					$this->rightParam->getEntityId() === 0
					&& \CIBlockSectionRights::UserHasRightTo(
						$this->rightParam->getIblockId(),
						$this->rightParam->getSectionId() ?? 0,
						'element_rights_edit'
					)
				)
			)
		);

		if ($canEditRights)
		{
			return true;
		}

		$this->errorCollection->setError(new Error('Access denied', self::ACCESS_DENIED));

		return false;
	}
}
