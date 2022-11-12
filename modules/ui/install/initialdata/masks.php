<?php

use Bitrix\Main;
use Bitrix\UI;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$fillMasks = function($path, ?int $groupId) use (&$fillMasks) {
	foreach ((new Main\IO\Directory($path))->getChildren() as $mask)
	{
		if ($mask instanceof Main\IO\Directory)
		{
			if (strlen($mask->getName()) > 2)
			{
				$directory = UI\Avatar\Mask\Helper::setSystemGroup(
					Loc::getMessage('UI_INSTALL_MASK_TITLE_' . strtoupper($mask->getName())),
					Loc::getMessage('UI_INSTALL_MASK_DESCRIPTION_' . strtoupper($mask->getName()))
				);
				if ($directory instanceof UI\Avatar\Mask\Group)
				{
					$fillMasks($mask->getPhysicalPath(), $directory->getId());
				}
			}
			else
			{
				$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
				if (
					$mask->getName() === $region
					||
					!(new Main\IO\Directory(Main\IO\Path::combine($path, $region)))->isExists()
					&&
					(
						$mask->getName() === 'ru' && in_array($region, ['ru', 'kz', 'by'])
						||
						$mask->getName() === 'en' && !in_array($region, ['ru', 'kz', 'by'])
					)
				)
				{
					$fillMasks($mask->getPhysicalPath(), $groupId);
				}
			}
			continue;
		}
		/**
		 * @var Main\IO\File $mask
		 */
		if (Loc::getMessage('UI_INSTALL_MASK_TITLE_' . strtoupper($mask->getName())) !== null)
		{
			UI\Avatar\Mask\Helper::addSystemMask([
				'name' => $mask->getName(),
				'tmp_name' => $mask->getPhysicalPath(),
				'size' => $mask->getSize(),
				'type' => $mask->getContentType()
			], [
				'GROUP_ID' => $groupId,
				'TITLE' => Loc::getMessage('UI_INSTALL_MASK_TITLE_' . strtoupper($mask->getName())),
				'DESCRIPTION' => Loc::getMessage('UI_INSTALL_MASK_DESCRIPTION_' . strtoupper($mask->getName())),
			]);
		}
	}
};
$fillMasks(__DIR__ . '/masks/', 0);
