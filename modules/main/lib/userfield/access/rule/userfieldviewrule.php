<?php

namespace Bitrix\Main\UserField\Access\Rule;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\UserField\Access\Model\UserFieldModel;
use Bitrix\Main\UserField\Access\Permission\PermissionDictionary;

class UserFieldViewRule
	extends \Bitrix\Main\Access\Rule\AbstractRule
{
	private
		$userFieldModel = null;

	public function __construct(AccessibleController $controller)
	{
		parent::__construct($controller);
		$this->userFieldModel = UserFieldModel::createNew();
	}

	public function execute(AccessibleItem $userField = null, $params = null): bool
	{
		return true;
	}

	public function executeMass($userFields = null, $params = null): array
	{
		if (!$userFields)
		{
			return [];
		}

		if ($this->user->isAdmin())
		{
			return [];
		}

		/**
		 * @var $userField UserFieldModel
		 */
		return $this->userFieldModel->getPermissions(
			$this->user,
			PermissionDictionary::USER_FIELD_VIEW
		);
	}
}