<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Rest\RestException;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;


Loc::loadMessages(__FILE__);

class File extends Engine\Controller
{
	private $allowEntityList = [
		'USER'
	];

	private $entityScope = [
		'USER' => 'user'
	];

	public function getAction($entity, $id, $field, $value, \CRestServer $restServer = null)
	{
		$errorCollection = new ErrorCollection();
		if (in_array($entity, $this->allowEntityList, true))
		{
			if ($restServer && $this->entityScope[$entity])
			{
				$authScope = $restServer->getAuthScope();
				if (!in_array($this->entityScope[$entity], $authScope, true))
				{
					$errorCollection->add(
						[
							new Error('Invalid request credentials', 'INVALID_CREDENTIALS')
						]
					);
				}
			}

			if ($errorCollection->isEmpty())
			{
				global $USER_FIELD_MANAGER;
				$userFieldsData = $USER_FIELD_MANAGER->getUserFields($entity, (int) $id, LANGUAGE_ID);

				if (
					$userFieldsData[$field]['USER_TYPE_ID'] === 'file'
					&& (int) $value > 0
					&& !empty($userFieldsData[$field]['VALUE']))
				{
					if (
						(int) $value === (int) $userFieldsData[$field]['VALUE']
						|| (
							is_array($userFieldsData[$field]['VALUE'])
							&& in_array((int) $value, $userFieldsData[$field]['VALUE'], true)
						)

					)
					{
						return BFile::createByFileId((int) $value);
					}

					$errorCollection->add(
						[
							new Error('File not found.', 'FILE_NOT_FOUND')
						]
					);
				}
				else
				{
					$errorCollection->add(
						[
							new Error('Entity not allow.', 'ENTITY_NOT_ALLOW')
						]
					);
				}
			}
		}
		else
		{
			$errorCollection->add(
				[
					new Error('Access denied.', 'ACCESS_DENIED')
				]
			);
		}

		if (!$errorCollection->isEmpty() && Engine\Controller::getScope() === Engine\Controller::SCOPE_REST)
		{
			/** @var  Error $error*/
			$error = $errorCollection->current();
			if ($error)
			{
				return new RestException(
					$error->getMessage(),
					$error->getCode()
				);

			}
		}

		return AjaxJson::createError($errorCollection);
	}

	public function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication()
		];
	}
}