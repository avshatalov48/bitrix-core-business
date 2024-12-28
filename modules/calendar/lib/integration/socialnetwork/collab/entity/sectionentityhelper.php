<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\Entity;

use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;

final class SectionEntityHelper
{
	/**
	 * Safe getter for section which connected to collab.
	 */
	public static function getIfCollab(int $id): ?SectionEntity
	{
		try
		{
			if (!Loader::includeModule('socialnetwork'))
			{
				return null;
			}

			return new SectionEntity($id);
		}
		catch (ObjectException)
		{
		}

		return null;
	}
}
