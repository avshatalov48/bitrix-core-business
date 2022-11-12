<?php
namespace Bitrix\Main\Filter;

use Bitrix\Main;

abstract class EntityDataProvider extends DataProvider
{
	/**
	 * Get specified entity field caption.
	 * @param string $fieldID Field ID.
	 * @return string
	 * @throws Main\NotImplementedException
	 */
	protected function getFieldName($fieldID)
	{
		throw new Main\NotImplementedException('Method getFieldName must be overridden');
	}
	/**
	 * Create filter field.
	 * @param string $fieldID Field ID.
	 * @param array|null $params Field parameters (optional).
	 * @return Field
	 * @throws Main\NotImplementedException
	 */
	protected function createField($fieldID, array $params = null)
	{
		if(!is_array($params))
		{
			$params = [];
		}

		if(!isset($params['name']))
		{
			$params['name'] = $this->getFieldName($fieldID);
		}

		return new Field($this, $fieldID, $params);
	}

	protected function getUserEntitySelectorParams(string $context, array $params): array
	{
		$entities = [
			[
				'id' => 'user',
				'options' => [
					'inviteEmployeeLink' => false,
					'intranetUsersOnly' => true,
				]
			],
		];

		if (class_exists(\Bitrix\Socialnetwork\Integration\UI\EntitySelector\FiredUserProvider::class))
		{
			$entities[] = [
				'id' => 'fired-user',
				'options' => [
					'inviteEmployeeLink' => false,
					'intranetUsersOnly' => true,
					'fieldName' => $params['fieldName'],
					'referenceClass'  => ($params['referenceClass'] ?? null),
					'entityTypeId' => ($params['entityTypeId'] ?? null),
					'module' => ($params['module'] ?? null),
				]
			];
		}

		$isEnableAllUsers = isset($params['isEnableAllUsers']) && $params['isEnableAllUsers'] === true;
		$isEnableOtherUsers = isset($params['isEnableOtherUsers']) && $params['isEnableOtherUsers'] === true;

		if ($isEnableAllUsers || $isEnableOtherUsers)
		{
			$metaUser = [
				'id' => 'meta-user',
				'options' => [],
			];

			if ($isEnableAllUsers)
			{
				$metaUser['options']['all-users'] = true;
			}
			if ($isEnableOtherUsers)
			{
				$metaUser['options']['other-users'] = true;
			}

			$entities[] = $metaUser;
		}

		return [
			'params' => [
				'multiple' => 'Y',
				'dialogOptions' => [
					'height' => 200,
					'context' => $context,
					'entities' => $entities,
					'showAvatars' => true,
					'dropdownMode' => false,
				],
			],
		];
	}
}
