<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main;
use Bitrix\Main\HttpRequest;
use Bitrix\UI\Avatar;

class Helper
{
	public const REQUEST_FIELD_NAME = 'ui_avatar_editor';
	public static function getHTMLAttribute($fileId)
	{
		return ' data-bx-ui-avatar-editor-info="'.htmlspecialcharsbx(self::getJson($fileId)).'" ';
	}

	public static function getJson($fileId): string
	{
		return Main\Web\Json::encode(
			static::getData($fileId)
		);
	}

	public static function getData(?int $fileId): ?array
	{
		if ($fileId > 0 && ($file = \CFile::GetByID($fileId)->Fetch()))
		{
			$result = [
				// 'name' => $file['FILE_NAME'],
				// 'width' => $file['WIDTH'],
				// 'height' => $file['HEIGHT'],
				// 'size' => $file['FILE_SIZE'],
				// 'type' => $file['CONTENT_TYPE'],
				'src' => Main\UI\FileInputUnclouder::getSrc($file),
				// 'meta' => $file['META']
			];

			if ($file['VERSION_ORIGINAL_ID'] == $fileId)
			{
				$originalFile = \CFile::GetByID($fileId, true)->Fetch();
				$maskId = null;
				if (
					($metaData = $file['META'] ? Main\Web\Json::decode($file['META']) : [])
					&& is_array($metaData)
					&& isset($metaData['maskInfo'])
					&& $metaData['maskInfo']['id'] > 0
				)
				{
					$maskId = $metaData['maskInfo']['id'];
				}
				else if ($res = Avatar\Model\ItemToFileTable::getList([
					'select' => ['*'],
					'filter' => ['FILE_ID' => $fileId],
					'limit' => 1
				])->fetch())
				{
					$maskId = $res['ITEM_ID'];
				}

				$result = [
					// 'name' => $originalFile['FILE_NAME'],
					// 'width' => $originalFile['WIDTH'],
					// 'height' => $originalFile['HEIGHT'],
					// 'size' => $originalFile['FILE_SIZE'],
					// 'type' => $originalFile['CONTENT_TYPE'],
					'src' => \CFile::GetFileSRC($originalFile),
					'maskId' => $maskId
				];
			}
			return $result;
		}
		return null;
	}

	public static function save(?int $originalFileId, array $file, ?Main\Engine\CurrentUser $currentUser = null): ?int
	{
		$originalFile = \CFile::GetByID($originalFileId)->Fetch();
		if (!$originalFile)
		{
			return null;
		}
		$currentUser = is_null($currentUser) ? Main\Engine\CurrentUser::get() : $currentUser;
		$consumer = Avatar\Mask\Consumer::createFromId($currentUser->getId());
		while (((int)$originalFile['ID'] !== (int)$originalFileId))
		{
			\CFile::Delete($originalFile['ID']);
			$originalFile = \CFile::GetByID($originalFileId)->Fetch();
		}
		if ($fileIdWithMask = \CFile::SaveFile($file + ['MODULE_ID' => 'ui'], 'ui/masked'))
		{
			$maskId = isset($file['maskInfo']) ? $file['maskInfo']['id'] : null;
			if ($maskId
				&& ($maskItem = Avatar\Mask\Item::getInstance($maskId))
				&& $maskItem->isReadableBy($consumer)
				&& \CFile::AddVersion($originalFileId, $fileIdWithMask, ['maskInfo' => ['id' => $maskId]])->isSuccess()
			)
			{
				$maskItem->applyToFileBy($originalFileId, $fileIdWithMask, $consumer);
				$consumer->useRecentlyMaskId($maskItem->getId());
				return $fileIdWithMask;
			}
			\CFile::Delete($fileIdWithMask);
		}
		return null;
	}

	public static function getMaskedFile(string $fieldName, ?HttpRequest $request = null): ?array
	{
		/** @var HttpRequest $request */
		$request = ($request ?? Main\Application::getInstance()->getContext()->getRequest());
		$mask = null;
		if ($id = $request->getPost(self::REQUEST_FIELD_NAME . $fieldName))
		{
			$mask = self::getMaskFromRequest(
				$id,
				$request->getFile(self::REQUEST_FIELD_NAME),
				$request->getPost(self::REQUEST_FIELD_NAME)
			);
		}
		return $mask;
	}

	/**
	 * @deprecated Delete after intranet 23.100.0 will be released
	 * @param string $fieldName
	 * @param HttpRequest|null $request
	 * @return array|null
	 */
	public static function getDataFromRequest(string $fieldName, ?HttpRequest $request = null): ?array
	{
		return null;
	}
	protected static function getMaskFromRequest($id, ?array $rawFiles, ?array $postData): ?array
	{
		if (!is_array($rawFiles) || !is_array($postData))
		{
			return null;
		}

		$orderedFiles = [];
		array_walk($rawFiles, function($item, $subField) use (&$orderedFiles) {
			foreach ($item as $key => $value)
			{
				$orderedFiles[$key] = $orderedFiles[$key] ?? [];
				$orderedFiles[$key][$subField] = $value;
			}
		});
		$result = null;
		if (isset($orderedFiles[$id]))
		{
			$result = $orderedFiles[$id];
			$maskInfo = ($postData[$id] ?? []);
			if (isset($maskInfo['maskId']))
			{
				$result['maskInfo'] = ['id' => $maskInfo['maskId']];
			}
		}
		return $result;
	}
	/**
	 * @params $file ['name' => 'name.png', 'type' => '', 'tmp_name' => '', 'size' => 124]
	 * @example

	Bitrix\UI\Avatar\Mask\Helper::addSystemMask([
		'name' => 'example.png',
		'type' => 'image/png',
		'tmp_name' => '',
	],
	[
		'TITLE' => 'Flag',
		'DESCRIPTION' => 'This is an example',
		'SORT' => 100,
	]);
	 *
	 *
	 * @return ?Item
	 */
	public static function addSystemMask(array $file, array $descriptionParams): ?Item
	{
		$result =  Item::create(
			new Owner\System(),
			$file,
			[
				'GROUP_ID' => $descriptionParams['GROUP_ID'] ?? null,
				'TITLE' => $descriptionParams['TITLE'] ?? null,
				'DESCRIPTION' => $descriptionParams['DESCRIPTION'] ?? null,
				'SORT' => $descriptionParams['SORT'] ?? 0,
			]
		)->getData();

		return reset($result);
	}

	public static function setSystemGroup(string $title, ?string $description): ?Group
	{
		return Group::createOrGet(new Owner\System(), $title, $description);
	}
}
