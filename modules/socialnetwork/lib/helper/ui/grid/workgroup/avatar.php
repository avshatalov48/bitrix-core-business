<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\Helper;

class Avatar
{
	public static function getValue(?EO_Workgroup $group): string
	{
		$classList = [
			'sonet-ui-grid-group-avatar',
			'ui-icon-common-user-group',
		];

		$avatar = '<i></i>';

		$imageId = (
			$group
				? $group->getImageId()
				: 0
		);

		if ($imageId > 0)
		{
			$file = \CFile::getFileArray($imageId);
			if (!empty($file))
			{
				$fileResized = \CFile::resizeImageGet(
					$file,
					[
						'width' => 100,
						'height' => 100,
					]
				);

				$classList[] = 'ui-icon';
				$avatar = "<i style=\"background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($fileResized['src'])) . "'); background-size: cover\"></i>";
			}
		}
		else
		{
			$avatarType = (string)$group->get('AVATAR_TYPE');
			if ($avatarType !== '')
			{
				$classList[] = 'sonet-common-workgroup-avatar';
				$classList[] = '--' . htmlspecialcharsbx(Helper\Workgroup::getAvatarTypeWebCssClass($avatarType));
			}
			else
			{
				$classList[] = 'ui-icon';
			}
		}

		return '<div class="' . implode(' ', $classList) . '">' . $avatar . '</div>';
	}
}
