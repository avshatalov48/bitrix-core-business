<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\EO_WorkgroupFavorites;
use Bitrix\Socialnetwork\EO_WorkgroupPin;
use Bitrix\Socialnetwork\EO_WorkgroupView;

class EntityManager
{
	private string $queryInitAlias = '';

	public function __construct(array $params = [])
	{
		$this->queryInitAlias = (string)($params['queryInitAlias'] ?? 'SOCIALNETWORK_WORKGROUP');
	}

	public function wakeUpWorkgroupEntityObject(array $groupFields = []): ?EO_Workgroup
	{
		$whiteList = [
			'ID',
			'ACTIVE',
			'NAME',
			'DESCRIPTION',
			'KEYWORDS',
			'CLOSED',
			'VISIBLE',
			'OPENED',
			'DATE_CREATE',
			'DATE_UPDATE',
			'DATE_ACTIVITY',
			'IMAGE_ID',
			'AVATAR_TYPE',
			'OWNER_ID',
			'INITIATE_PERMS',
			'NUMBER_OF_MEMBERS',
			'NUMBER_OF_MODERATORS',
			'PROJECT',
			'PROJECT_DATE_START',
			'PROJECT_DATE_FINISH',
			'SEARCH_INDEX',
			'LANDING',
			'SCRUM_OWNER_ID',
			'SCRUM_MASTER_ID',
			'SCRUM_SPRINT_DURATION',
			'SCRUM_TASK_RESPONSIBLE',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList);

		return (!empty($entityFields['ID']) ? EO_Workgroup::wakeUp($entityFields) : null);
	}

	public function wakeUpContextRelationEntityObject(array $groupFields = []): ?EO_UserToGroup
	{
		$whiteList = [
			'ID',
			'USER_ID',
			'GROUP_ID',
			'ROLE',
			'INITIATED_BY_TYPE',
			'INITIATED_BY_USER_ID',
			'DATE_UPDATE',
			'AUTO_MEMBER',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList, $this->queryInitAlias . '_CONTEXT_RELATION_');

		return (!empty($entityFields['ID']) ? EO_UserToGroup::wakeUp($entityFields) : null);
	}

	public function wakeUpCurrentRelationEntityObject(array $groupFields = []): ?EO_UserToGroup
	{
		$whiteList = [
			'ID',
			'USER_ID',
			'GROUP_ID',
			'ROLE',
			'INITIATED_BY_TYPE',
			'INITIATED_BY_USER_ID',
			'AUTO_MEMBER',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList, $this->queryInitAlias . '_CURRENT_RELATION_');

		return (!empty($entityFields['ID']) ? EO_UserToGroup::wakeUp($entityFields) : null);
	}

	public function wakeUpFavoritesEntityObject(array $groupFields = []): ?EO_WorkgroupFavorites
	{
		$whiteList = [
			'USER_ID',
			'GROUP_ID',
			'DATE_ADD',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList, $this->queryInitAlias . '_FAVORITES_');

		return (
			!empty($entityFields['USER_ID'])
			&& !empty($entityFields['GROUP_ID'])
				? EO_WorkgroupFavorites::wakeUp($entityFields)
				: null
		);
	}

	public function wakeUpPinEntityObject(array $groupFields = []): ?EO_WorkgroupPin
	{
		$whiteList = [
			'ID',
			'USER_ID',
			'GROUP_ID',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList, $this->queryInitAlias . '_PIN_');

		return (!empty($entityFields['ID']) ? EO_WorkgroupPin::wakeUp($entityFields) : null);
	}

	public function wakeUpViewEntityObject(array $groupFields = []): ?EO_WorkgroupView
	{
		$whiteList = [
			'USER_ID',
			'GROUP_ID',
			'DATE_VIEW',
		];
		$entityFields = $this->getEntityFields($groupFields, $whiteList, $this->queryInitAlias . '_VIEW_');

		return (
			!empty($entityFields['USER_ID'])
			&& !empty($entityFields['GROUP_ID'])
				? EO_WorkgroupView::wakeUp($entityFields)
				: null
		);
	}

	protected function getEntityFields($groupFields, array $whiteList = [], string $entityAlias = ''): array
	{
		$entityFields = [];

		$map = [];
		array_walk($whiteList, static function($fieldName) use ($entityAlias, &$map) {
			$map[$entityAlias . $fieldName] = $fieldName;
		});

		$groupFields = array_filter($groupFields, static function ($key) use ($map) {
			return (array_key_exists($key, $map));
		}, ARRAY_FILTER_USE_KEY);

		foreach ($groupFields as $key => $value)
		{
			$entityFields[$map[$key]] = $value;
		}

		return $entityFields;
	}
}
