<?php

namespace Bitrix\Bizproc\Api\Response\UserService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\UI\UserView;

class GetUsersViewResponse extends Result
{
	public function __construct()
	{
		parent::__construct();

		$this->data['userViews'] = [];
	}

	public function addUserView(UserView $user): void
	{
		$this->data['userViews'][] = $user;
	}

	/**
	 * @return UserView[]
	 */
	public function getUserViews(): array
	{
		return $this->data['userViews'];
	}
}