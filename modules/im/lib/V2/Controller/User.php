<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Entity;
use Bitrix\Main\Engine\AutoWire\ExactParameter;

class User extends BaseController
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Entity\User\User::class,
			'user',
			function ($className, int $id) {
				return $this->getUserById($id);
			}
		);
	}

	/**
	 * @restMethod im.v2.User.getDepartment
	 */
	public function getDepartmentAction(Entity\User\User $user): ?array
	{
		$department = $user->getDepartments()->filterExist()->getDeepest()->getAny();

		if ($department === null)
		{
			return null;
		}

		return $this->toRestFormat($department);
	}

	protected function getUserById(int $id): ?Entity\User\User
	{
		$user = Entity\User\User::getInstance($id);

		if (!$user->isExist())
		{
			$this->addError(new Entity\User\UserError(Entity\User\UserError::NOT_FOUND));

			return null;
		}

		return $user;
	}
}