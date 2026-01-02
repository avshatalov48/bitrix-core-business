<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Entity;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type;

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
		$nodeRepository = Container::getNodeRepository();
		$userNodes = $nodeRepository->findAllByUserId($user->getId())->getItemMap();
		$userDepartments = array_filter($userNodes, function($node) {
			return $node->type === Type\NodeEntityType::DEPARTMENT;
		});
		usort($userDepartments, function($a, $b) {
			return $a->depth <=> $b->depth;
		});
		$department = array_pop($userDepartments);

		return [
			'id' => $department->id,
			'name' => $department->name,
		];
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