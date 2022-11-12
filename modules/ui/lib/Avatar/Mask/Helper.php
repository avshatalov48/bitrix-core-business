<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main;
use Bitrix\Main\HttpRequest;
use Bitrix\UI\Avatar;

class Helper
{
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
				'src' => \CFile::GetFileSRC($file),
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
				else if ($res = Avatar\Mask\ItemToFileTable::getList([
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

	public static function getDataFromRequest(string $fieldName, ?HttpRequest $request): ?array
	{
		/** @var HttpRequest $request */
		$request = ($request ?? Main\Application::getInstance()->getContext()->getRequest());
		$files = $request->getFile($fieldName);
		if ($files && is_array($files['name']))
		{
			$copyFiles = [];
			array_walk($files, function($item, $subField) use (&$copyFiles) {
				foreach ($item as $key => $value)
				{
					$copyFiles[$key] = $copyFiles[$key] ?? [];
					$copyFiles[$key][$subField] = $value;
				}
			});
			if (array_key_exists('file', $copyFiles))
			{
				$result = [$copyFiles['file']];
				if (array_key_exists('maskedFile', $copyFiles))
				{
					$post = $request->getPost($fieldName);
					$post = is_array($post) ? $post : [];
					$maskInfo = (!isset($post['maskedFile']) ? null : (
						is_array($post['maskedFile']) ?
							$post['maskedFile'] : Main\Web\Json::decode($post['maskedFile'])));
					$result[] = $copyFiles['maskedFile']
						+ (is_array($maskInfo) ? ['maskInfo' => ['id' => $maskInfo['maskId']]] : []);
				}
				return $result;
			}
		}
		return null;
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
