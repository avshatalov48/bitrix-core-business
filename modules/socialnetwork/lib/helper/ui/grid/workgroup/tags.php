<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;

class Tags
{
	public static function getValue(array $tags = [], array $params = []): array
	{
		$result = [
			'items' => [],
		];

		$group = ($params['GROUP'] ?? null);
		if (!$group)
		{
			return $result;
		}

		$groupId = (int)$group->get('ID');
		$gridId = (string)($params['GRID_ID'] ?? '');
		$filterField = (string)($params['FILTER_FIELD'] ?? '');
		$currentUserRelation = ($params['CURRENT_USER_RELATION'] ?? null);

		if (
			$groupId <= 0
			|| $gridId === ''
			|| $filterField === ''
		)
		{
			return $result;
		}

		$accessManager = new AccessManager(
			$group,
			$currentUserRelation,
			$currentUserRelation
		);

		if ($accessManager->canModify())
		{
			$result['addButton'] = [
				'events' => [
					'click' => '
						BX.Socialnetwork.UI.Grid.TagController
							.onTagAddClick.bind(BX.Socialnetwork.UI.Grid.TagController, ' . $groupId . ')',
				],
			];
		}

		foreach ($tags as $tag)
		{
			$encodedData = Json::encode(['TAG' => $tag]);

			$selected = (
				$filterField !== ''
				&& isset($params['FILTER_DATA'][$filterField])
				&& $params['FILTER_DATA'][$filterField] === $tag
			);

			$result['items'][] = [
				'text' => $tag,
				'active' => $selected,
				'events' => [
					'click' => '
						BX.Socialnetwork.UI.Grid.TagController
							.onTagClick.bind(BX.Socialnetwork.UI.Grid.TagController, ' . $encodedData . ')',
				],
			];
		}

		return $result;
	}
}
