<?php
namespace Bitrix\Im\Call;

use Bitrix\Im\Common;
use Bitrix\Main\Localization\Loc;

class Background
{
	private static $path = '/bitrix/js/im/images/background';

	public static function get()
	{
		return array_merge(
			self::getImageFiles(),
			self::getVideoFiles(),
			self::getIntranetFiles()
		);
	}

	private static function getImageFiles()
	{
		$result = [
			[
				'id' => 'apartment',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_APARTMENT'),
			],
			[
				'id' => 'night-office',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_NIGHT_OFFICE'),
			],
			[
				'id' => 'basement',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_BASEMENT'),
			],
			[
				'id' => 'tent',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_TENT'),
			],
			[
				'id' => 'summer-park',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_SUMMER_PARK'),
			],
			[
				'id' => 'winter-forest',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_WINTER_FOREST'),
			],
			[
				'id' => 'botanical-garden',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_BOTANICAL_GARDEN'),
			],
			[
				'id' => 'stadium',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_STADIUM'),
			],
			[
				'id' => 'safari',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_SAFARI'),
			],
			[
				'id' => 'subway',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_SUBWAY'),
			],
			[
				'id' => 'escalator',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_ESCALATOR'),
			],
			[
				'id' => 'space-station',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_SPACE_STATION'),
			],
			[
				'id' => 'moon',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_MOON'),
			],
			[
				'id' => 'fireworks',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_HOLIDAY'),
			],
			[
				'id' => 'hyperspace',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_HYPERSPACE'),
			],
			[
				'id' => 'attractions',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_ATTRACTIONS'),
			],
			[
				'id' => 'street',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_STREET'),
			],
			[
				'id' => 'cathedral',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_CATHEDRAL'),
			],
			[
				'id' => 'abandoned-building',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_ABANDONED_BUILDING'),
			],
			[
				'id' => 'business-quarter',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_BUSINESS_QUARTER'),
			],
			[
				'id' => 'under-water',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_UNDER_WATER'),
			],
			[
				'id' => 'autumn-wall',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_AUTUMN_WALL'),
			],
			[
				'id' => 'wooden-wall',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_WOODEN_WALL'),
			],
			[
				'id' => 'bright-wall',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_BRIGHT_WALL'),
			],
			[
				'id' => 'neon-space',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_NEON_SPACE'),
			],
			[
				'id' => 'skeletons',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_SKELETONS'),
			],
			[
				'id' => 'halloween',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_HALLOWEEN'),
			],
			[
				'id' => 'christmas-tree',
				'title' => Loc::getMessage('IM_CALL_BG_IMAGE_CHRISTMAS_TREE'),
			],
		];

		foreach ($result as &$value)
		{
			$value['preview'] = static::$path."/{$value['id']}-preview.jpg";
			$value['background'] = static::$path."/{$value['id']}.jpg";
			$value['isVideo'] = false;
			$value['isSupported'] = true;
		}

		return $result;
	}

	private static function getVideoFiles()
	{
		$result = [
			[
				'id' => 'star-sky',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_STAR_SKY'),
			],
			[
				'id' => 'waves',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_WAVES'),
			],
			[
				'id' => 'jellyfishes',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_JELLYFISHES'),
			],
			[
				'id' => 'sunset',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_SUNSET'),
			],
			[
				'id' => 'rain',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_RAIN'),
			],
			[
				'id' => 'rain-drops',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_RAIN_DROPS'),
			],
			[
				'id' => 'grass',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_GRASS'),
			],
			[
				'id' => 'stones',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_STONES'),
			],
			[
				'id' => 'waterfall',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_WATERFALL'),
			],
			[
				'id' => 'shining',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_SHINING'),
			],
			[
				'id' => 'beach',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_BEACH'),
			],
			[
				'id' => 'river',
				'title' => Loc::getMessage('IM_CALL_BG_VIDEO_RIVER'),
			],
		];

		foreach ($result as &$value)
		{
			$value['preview'] = "/bitrix/js/im/images/background/video/{$value['id']}-preview.jpg";
			$value['background'] = "/bitrix/js/im/images/background/video/{$value['id']}.mp4";
			$value['isVideo'] = true;
			$value['isSupported'] = true;
			$value['id'] .= ':video';
		}

		return $result;
	}

	private static function getIntranetFiles()
	{
		$result = [];

		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			return $result;
		}

		$themePicker = new \Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker('bitrix24');
		$list = $themePicker->getList();

		foreach ($list as $element)
		{
			if (mb_strpos($element['id'], 'pattern') !== false)
			{
				continue;
			}

			if (mb_strpos($element['id'], 'video') !== false)
			{
				continue;
			}

			if (!$element['prefetchImages'][0])
			{
				continue;
			}

			$result[] = [
				'id' => $element['id'],
				'title' => $element['title'],
				'preview' => $element['previewImage'],
				'background' => $element['prefetchImages'][0],
				'isVideo' => false,
				'isSupported' => true
			];
		}

		return $result;
	}

	public static function getCustom()
	{
		$result = [];

		if (!\CIMDisk::Enabled())
		{
			return $result;
		}

		$folderModel = self::getUploadFolder();
		if (!$folderModel)
		{
			return $result;
		}

		$securityContext = new \Bitrix\Disk\Security\DiskSecurityContext(Common::getUserId());

		$parameters = [
			'filter' => [
				'PARENT_ID' => $folderModel->getId(),
				'STORAGE_ID' => $folderModel->getStorageId()
			],
			'order' => ['UPDATE_TIME' => 'DESC']
		];
		$parameters = \Bitrix\Disk\Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, array('ID', 'CREATED_BY'));

		$fileCollection = \Bitrix\Disk\File::getModelList($parameters);

		foreach ($fileCollection as $fileModel)
		{
			if ($fileModel->getTypeFile() == \Bitrix\Disk\TypeFile::IMAGE)
			{
				$supported = in_array(mb_strtolower($fileModel->getExtension()), ['png', 'jpg', 'jpeg'], true);
			}
			else if ($fileModel->getTypeFile() == \Bitrix\Disk\TypeFile::VIDEO)
			{
				$supported = in_array(mb_strtolower($fileModel->getExtension()), ['mp4', 'mov', 'avi'], true);
			}
			else
			{
				$supported = false;
			}

			$result[] = [
				'id' => (int)$fileModel->getId(),
				'title' => $fileModel->getName(),
				'preview' => $supported? \CIMDisk::GetPublicPath(\CIMDisk::PATH_TYPE_PREVIEW, $fileModel, false): '',
				'background' => $supported? \CIMDisk::GetPublicPath(\CIMDisk::PATH_TYPE_SHOW, $fileModel, false): '',
				'isVideo' => $fileModel->getTypeFile() == \Bitrix\Disk\TypeFile::VIDEO,
				'isSupported' => $supported,
			];
		}

		return $result;
	}

	public static function getUploadFolder($userId = null)
	{
		$folderModel = \CIMDisk::GetBackgroundFolderModel($userId);
		if (!$folderModel)
		{
			return null;
		}

		return $folderModel;
	}

	public static function getLimitForJs()
	{
		$result = [];

		$image = \Bitrix\Im\Limit::getTypeCallBackground();
		$result[] = [
			'id' => $image['ID'],
			'active' => $image['ACTIVE'],
			'articleCode' => $image['ARTICLE_CODE'],
		];

		$blur = \Bitrix\Im\Limit::getTypeCallBlurBackground();
		$result[] = [
			'id' => $blur['ID'],
			'active' => $blur['ACTIVE'],
			'articleCode' => $blur['ARTICLE_CODE'],
		];

		return $result;
	}
}

