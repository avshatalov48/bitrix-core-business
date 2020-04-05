<?php

namespace Bitrix\Blog\Integration\Disk;

use Bitrix\Main\Loader;
use Bitrix\Disk\AttachedObject;

class Transformation
{
	public static function getStatus($params = array())
	{
		$attachedIdList = (
			is_array($params)
			&& !empty($params['attachedIdList'])
			&& is_array($params['attachedIdList'])
				? $params['attachedIdList']
				: array()
		);

		if (
			empty($params['attachedIdList'])
			|| !Loader::includeModule('disk')
			|| !method_exists('Bitrix\Disk\View\Video', 'isNeededLimitRightsOnTransformTime')
		)
		{
			return false;
		}

		foreach($attachedIdList as $attachedId)
		{
			$attach = AttachedObject::getById($attachedId);
			if ($attach->getFile()->getView()->isNeededLimitRightsOnTransformTime())
			{
				return true;
			}
		}

		return false;
	}
}
