<?php
namespace Bitrix\Socialnetwork\Livefeed\RenderParts;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class SonetGroup extends Base
{
	public function getData($entityId = 0)
	{
		static $groupPath = null;

		global $USER;

		$result = $this->getMetaResult();
		$options = $this->getOptions();

		if ($fields = \CSocNetGroup::getByID($entityId))
		{
			$result['id'] = $entityId;
			$result['name'] = (
				isset($fields["~NAME"])
					? $fields["~NAME"]
					: ''
			);

			if (
				empty($options['skipLink'])
				|| !$options['skipLink']
			)
			{
				if ($groupPath === null)
				{
					$groupPath = (
					(!isset($options['mobile']) || !$options['mobile'])
					&& (!isset($options['im']) || !$options['im'])
						? Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'company/workgroups/').'group/#group_id#/'
						: ''
					);
				}

				if (!empty($groupPath))
				{
					$extranet = (
					isset($options['extranet'])
						? $options['extranet']
						: false
					);
					$extranetSite = (
					isset($options['extranetSite'])
						? $options['extranetSite']
						: false
					);

					$link = \CComponentEngine::makePathFromTemplate(
						$groupPath,
						array(
							"group_id" => $fields["ID"]
						)
					);

					$groupSiteID = false;

					$res = \CSocNetGroup::getSite($fields["ID"]);
					while ($groupSiteList = $res->fetch())
					{
						if (
							!$groupSiteID
							&& (
								!$extranet
								|| $groupSiteList["LID"] != $extranetSite
							)
						)
						{
							$groupSiteID = $groupSiteList["LID"];
						}
					}

					if ($groupSiteID)
					{
						$tmp = \CSocNetLogTools::processPath(array("GROUP_URL" => $link), $USER->getId(), $groupSiteID);
						$link = (strlen($tmp["URLS"]["GROUP_URL"]) > 0 ? $tmp["SERVER_NAME"].$tmp["URLS"]["GROUP_URL"] : $link);
					}

					$result['link'] = $link;
				}
			}
		}

		return $result;
	}

}