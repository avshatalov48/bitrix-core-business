<?php
namespace Bitrix\Socialnetwork\Copy\Implement;

use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\UserToGroupTable;

class UserToGroup extends CopyImplementer
{
	const GROUP_USER_COPY_ERROR = "GROUP_USER_COPY_ERROR";

	/**
	 * @var UserGroupHelper|null
	 */
	private $userGroupHelper = null;

	/**
	 * Record helper object to update the list of moderators when copying.
	 *
	 * @param UserGroupHelper $userGroupHelper Helper object.
	 */
	public function setUserGroupHelper(UserGroupHelper $userGroupHelper)
	{
		$this->userGroupHelper = $userGroupHelper;
	}

	/**
	 * Adds entity.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool Added entity id or false.
	 */
	public function add(Container $container, array $fields)
	{
		global $APPLICATION;

		foreach ($fields as $field)
		{
			if (!\CSocNetUserToGroup::add($field))
			{
				$errorMessage = "";
				if ($exception = $APPLICATION->getException())
				{
					$errorMessage = $exception->getString();
					$this->result->addError(new Error($errorMessage, self::GROUP_USER_COPY_ERROR));
				}
				if ($errorMessage == '')
				{
					$this->result->addError(new Error("Error adding a user to the group", self::GROUP_USER_COPY_ERROR));
				}
			}
		}

		if ($this->userGroupHelper)
		{
			$this->userGroupHelper->changeModerators($container->getCopiedEntityId());
		}

		return true;
	}

	/**
	 * Returns entity fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$fields = [];

		$filter = [
			"GROUP_ID" => $entityId,
			"!=ROLE" => UserToGroupTable::ROLE_OWNER
		];
		if (in_array("UF_SG_DEPT", $this->ufIgnoreList))
		{
			$filter["AUTO_MEMBER"] = "N";
		}

		$dictionary = $container->getDictionary();

		$queryObject = \CSocNetUserToGroup::getList(["ID" => "DESC"], $filter);
		while ($userToGroup = $queryObject->fetch())
		{
			if (
				isset($dictionary["NEW_OWNER_ID"])
				&& $dictionary["NEW_OWNER_ID"] == $userToGroup["USER_ID"]
			)
			{
				continue;
			}

			$fields[] = $userToGroup;
		}

		return $fields;
	}

	/**
	 * Preparing data before creating a new entity.
	 *
	 * @param Container $container
	 * @param array $fields List entity fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		global $DB;

		foreach ($fields as &$field)
		{
			unset($field["ID"]);
			unset($field["DATE_CREATE"]);
			unset($field["DATE_UPDATE"]);

			$field["SEND_MAIL"] = "N";
			$field["=DATE_CREATE"] = $DB->currentTimeFunction();
			$field["=DATE_UPDATE"] = $DB->currentTimeFunction();

			$field["GROUP_ID"] = $container->getCopiedEntityId();
		}

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId Entity id.
	 * @param int $copiedEntityId Copied entity id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		return new Result();
	}
}