<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class UserSignature extends Base
{
	const USER_SIGNATURES_LIMIT = 100;

	/**
	 * @param array $fields
	 * @return array|false
	 */
	public function addAction(array $fields)
	{
		$unsafeFields = (array) $this->getRequest()->getPostList()->getRaw('fields');

		if (($limit = Main\Config\Option::get('mail', 'user_signatures_limit', static::USER_SIGNATURES_LIMIT)) > 0)
		{
			$count = UserSignatureTable::getCount(array(
				'USER_ID' => CurrentUser::get()->getId(),
			));
			if ($count >= $limit)
			{
				Loc::loadMessages(__FILE__);
				$this->errorCollection[] = new Error(Loc::getMessage('MAIL_USER_SIGNATURE_LIMIT'));
				return false;
			}
		}

		$userSignature = new \Bitrix\Mail\Internals\Entity\UserSignature;

		$userSignature->set('USER_ID', CurrentUser::get()->getId());
		$userSignature->set('SENDER', $fields['sender']);
		$userSignature->set('SIGNATURE', $this->sanitize($unsafeFields['signature']));

		$result = $userSignature->save();

		if($result->isSuccess())
		{
			$userSignature = UserSignatureTable::getById($result->getId())->fetchObject();
			return $this->getAction($userSignature);
		}
		else
		{
			$this->errorCollection = $result->getErrors();
			return false;
		}
	}

	protected function checkAccess(\Bitrix\Mail\Internals\Entity\UserSignature $userSignature): bool
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		if (!is_null($currentUserId) && (int)$userSignature->getUserId() === (int)$currentUserId)
		{
			return true;
		}

		$this->addError(new Error(Loc::getMessage('MAIL_USER_SIGNATURE_ACCESS_DENIED')));
		return false;
	}

	/**
	 * @param \Bitrix\Mail\Internals\Entity\UserSignature $userSignature
	 */
	public function deleteAction(\Bitrix\Mail\Internals\Entity\UserSignature $userSignature): bool
	{
		if (!$this->checkAccess($userSignature))
		{
			return false;
		}

		$userSignature->delete();
		return true;
	}

	public function getAction(\Bitrix\Mail\Internals\Entity\UserSignature $userSignature): bool|array
	{
		if (!$this->checkAccess($userSignature))
		{
			return false;
		}

		return [
			'userSignature' => $this->convertArrayKeysToCamel($userSignature->collectValues(), 1),
		];
	}

	/**
	 * @param \Bitrix\Mail\Internals\Entity\UserSignature $userSignature
	 * @param array $fields
	 * @return array|false
	 */
	public function updateAction(\Bitrix\Mail\Internals\Entity\UserSignature $userSignature, array $fields): bool|array
	{
		if (!$this->checkAccess($userSignature))
		{
			return false;
		}

		$unsafeFields = (array) $this->getRequest()->getPostList()->getRaw('fields');

		$userSignature->set('SENDER', $fields['sender']);
		$userSignature->set('SIGNATURE', $this->sanitize($unsafeFields['signature']));

		$result = $userSignature->save();
		if($result->isSuccess())
		{
			return $this->getAction($userSignature);
		}
		else
		{
			$this->errorCollection = $result->getErrors();
			return false;
		}
	}
}
