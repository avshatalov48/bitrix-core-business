<?php
namespace Bitrix\Landing\Connector;

class Iblock
{
	/**
	 * Gets element's url in site context.
	 * @param int $siteId
	 * @param int $elementId
	 * @return string
	 */
	public static function getElementUrl($siteId, $elementId)
	{
		$url = '';
		$syspages = \Bitrix\Landing\Syspage::get($siteId);
		if (isset($syspages['catalog']))
		{
			$landing = \Bitrix\Landing\Landing::createInstance(
				$syspages['catalog']['LANDING_ID'],
				['skip_blocks' => true]
			);
			if ($landing->exist())
			{
				$url = \Bitrix\Landing\PublicAction\Utils::getIblockURL(
					$elementId,
					'detail'
				);
				$url = str_replace(
					'#system_catalog',
					$landing->getPublicUrl(),
					$url
				);
				if (mb_substr($url, 0, 1) == '/')
				{
					$url = \Bitrix\Landing\Site::getPublicUrl(
						$landing->getSiteId()
					) . $url;
				}
			}
		}

		return $url;
	}
}