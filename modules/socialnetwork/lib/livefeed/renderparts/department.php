<?php
namespace Bitrix\Socialnetwork\Livefeed\RenderParts;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class Department extends Base
{
	public function getData($entityId = 0)
	{
		static $departmentPath = null;

		$result = $this->getMetaResult();
		$options = $this->getOptions();

		if (
			$entityId > 0
			&& Loader::includeModule('iblock')
			&& ($res = \CIBlockSection::getByID($entityId))
			&& ($iblockSection = $res->fetch())
		)
		{
			$result['id'] = $entityId;
			$result['name'] = $iblockSection["NAME"];

			if (
				empty($options['skipLink'])
				|| !$options['skipLink']
			)
			{
				if ($departmentPath === null)
				{
					$departmentPath = (
					(!isset($options['mobile']) || !$options['mobile'])
					&& (!isset($options['im']) || !$options['im'])
						? Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', SITE_DIR."company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#")
						: ''
					);
				}
				if (!empty($departmentPath))
				{
					$result['link'] = \CComponentEngine::makePathFromTemplate(
						$departmentPath,
						array(
							"ID" => $entityId
						)
					);
				}
			}
		}

		return $result;
	}
}