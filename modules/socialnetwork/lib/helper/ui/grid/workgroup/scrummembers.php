<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Main\Web\Uri;

class ScrumMembers
{
	public static function getValue(array $users = [], array $params = []): string
	{
		$visibleMembersCount = 3;

		$totalHeadsCount = (int)($params['NUMBER_OF_MODERATORS'] ?? 0);

		$headsLayout = '';
		$heads = static::fillUsersLayout(($users['HEADS'] ?? []));
		if (!empty($heads))
		{
			$innerLayouts = [];
			foreach ($heads as $head)
			{
				if ($head['IS_OWNER'] === 'Y')
				{
					array_unshift($innerLayouts, $head['LAYOUT']);
					continue;
				}
				$innerLayouts[] = $head['LAYOUT'];
			}

			if (count($innerLayouts) > $visibleMembersCount)
			{
				$innerLayouts = array_slice($innerLayouts, 0, $visibleMembersCount);
			}

			$innerLayouts =	implode("\n", $innerLayouts);

			$headsLayout =
				"<div style='display: inline-block'>"
					. "<div class='sonet-ui-grid-user-list sonet-ui-grid-user-list--green'>"
						. $innerLayouts
						. static::makeOtherCounterLayout($totalHeadsCount - $visibleMembersCount)
					. "</div>"
				. "</div>"
			;
		}

		$usersLayout = '';
		$users = static::fillUsersLayout(($users['MEMBERS'] ?? []));

		if (count($users) > 0)
		{
			$innerLayouts = [];
			foreach ($users as $user)
			{
				$innerLayouts[] = $user['LAYOUT'];
				if (count($innerLayouts) >= $visibleMembersCount)
				{
					break;
				}
			}
			$innerLayouts = implode("\n", $innerLayouts);
			$totalUsersCount = (int)($params['NUMBER_OF_MEMBERS'] ?? 0);

			$usersLayout =
				'<div style="display: inline-block">'
					. '<div class="sonet-ui-grid-user-list">'
						. $innerLayouts
						. static::makeOtherCounterLayout(($totalUsersCount - $totalHeadsCount - $visibleMembersCount))
					. '</div>'
				. '</div>'
			;
		}

		return
			'<div class="sonet-ui-grid-user-list-container" onclick="' . static::getMembersPopupShowFunction(
					(int)$params['GROUP_ID'],
					(string)$params['GROUP_TYPE'],
					(string)$params['GRID_ID']
				) . '">'
				. $headsLayout
				. $usersLayout
			. '</div>'
		;
	}

	private static function fillUsersLayout(array $users): array
	{
		foreach ($users as $id => $user)
		{
			$style = (
				$user['PHOTO']
					? 'style="background-image: url(\'' . Uri::urnEncode($user['PHOTO']) . '\')"'
					: ''
			);

			$users[$id]['LAYOUT'] =
				'<a class="sonet-ui-grid-user-item" ' . $style . '>'
					. '<div class="sonet-ui-grid-user-crown"></div>'
				. '</a>'
			;
		}

		return $users;
	}

	private static function makeOtherCounterLayout(int $otherCount): string
	{
		if ($otherCount <= 0)
		{
			return '';
		}

		return '<div class="sonet-ui-grid-user-count"><span class="sonet-ui-grid-user-plus">+</span>' . $otherCount . '</div>';
	}

	private static function getMembersPopupShowFunction(
		int $groupId = 0,
		string $groupType = '',
		string $gridId = ''
	): string
	{
		if ($groupId <= 0)
		{
			return '';
		}

		$gridId = htmlspecialcharsbx(\CUtil::JSescape($gridId));
		$groupType = htmlspecialcharsbx(\CUtil::JSescape($groupType));

		return '
			BX.Socialnetwork.UI.Grid.Controller.getById(\'' . $gridId . '\')
				.getScrumMembersPopup()
				.showPopup(' . $groupId . ', \'' . $groupType . '\', this); 
			event.stopPropagation();'
		;
	}

	public static function getUserAvatars(array $imageIds = []): array
	{
		$result = [];
		if (empty($imageIds))
		{
			return $result;
		}

		$result = array_fill_keys($imageIds, '');

		$res = \CFile::getList([], ['@ID' => implode(',', $imageIds)]);
		while ($file = $res->fetch())
		{
			$file['SRC'] = \CFile::getFileSRC($file);
			$fileInfo = \CFile::ResizeImageGet(
				$file,
				[
					'width' => 100,
					'height' => 100,
				],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true,
			);

			$result[$file['ID']] = $fileInfo['src'];
		}

		return $result;
	}

}
