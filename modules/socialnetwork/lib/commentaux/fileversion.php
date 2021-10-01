<?php

namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Disk\Configuration;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class FileVersion extends Base
{
	public const TYPE = 'FILEVERSION';
	public const POST_TEXT = 'commentAuxFileVersion';

	public function getParamsFromFields($fields = []): array
	{
		$params = [];

		if (!empty($fields['AUTHOR_ID']))
		{
			$params['userId'] = (int)$fields['AUTHOR_ID'];
		}

		return $params;
	}

	public function getText(): string
	{
		static $userCache = [];

		$params = $this->params;

		$gender = '';

		if (
			!empty($params['userId'])
			&& (int)$params['userId'] > 0
		)
		{
			if (isset($userCache[(int)$params['userId']]['PERSONAL_GENDER']))
			{
				$gender = $userCache[(int)$params['userId']]['PERSONAL_GENDER'];
			}
			else
			{
				$res = UserTable::getList([
					'filter' => [
						'=ID' => (int)$params['userId'],
					],
					'select' => [ 'ID', 'PERSONAL_GENDER' ],
				]);

				if ($user = $res->fetch())
				{
					$userCache[$user['ID']] = $user;
					$gender = $user['PERSONAL_GENDER'];
				}
			}
		}

		if (Loader::includeModule('disk') && !Configuration::isEnabledKeepVersion())
		{
			return Loc::getMessage('SONET_COMMENTAUX_HEAD_FILEVERSION_TEXT' . (!empty($gender) ? '_' . $gender : ''));
		}

		return Loc::getMessage('SONET_COMMENTAUX_FILEVERSION_TEXT' . (!empty($gender) ? '_' . $gender : ''));
	}

	protected function getRatingNotificationFollowValue(int $userId = 0, array $ratingVoteParams = [], array $fields = [])
	{
		return \CSocNetLogFollow::getExactValueByRating(
			$userId,
			'BLOG_COMMENT',
			$fields['ID']
		);
	}

	protected function getRatingNotificationNotigyTag(array $ratingVoteParams = [], array $fields = []): string
	{
		return 'RATING|' . ($ratingVoteParams['VALUE'] >= 0 ? '' : 'DL|') . 'BLOG_COMMENT|' . $fields['ID'];
	}
}
