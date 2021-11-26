<?php
namespace Bitrix\UI\EntitySelector;

class FilterControllerResolver
{
	public static function getModuleId(string $filterId): string
	{
		$isThirdPartyModule = mb_strpos($filterId, ':') !== false;
		if ($isThirdPartyModule)
		{
			$path = explode(
				'.',
				str_replace(':', '.', $filterId)
			);

			return $path[0] . '.' . $path[1];
		}

		return explode('.', $filterId)[0];
	}
}