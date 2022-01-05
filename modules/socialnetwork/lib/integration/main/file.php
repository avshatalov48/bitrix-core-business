<?php

namespace Bitrix\Socialnetwork\Integration\Main;

class File
{
	public static function getFileSource(
		int $fileId,
		int $width = 50,
		int $height = 50,
		bool $immediate = false
	): string
	{
		if ($fileId <= 0)
		{
			return '';
		}

		if ($file = \CFile::GetFileArray($fileId))
		{
			$fileInfo = \CFile::ResizeImageGet(
				$file,
				[
					'width' => $width,
					'height' => $height,
				],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				$immediate
			);

			return $fileInfo['src'];
		}

		return '';
	}

	public static function getFilesSources(
		array $fileIds,
		int $width = 50,
		int $height = 50,
		bool $immediate = false
	): array
	{
		if (empty($fileIds))
		{
			return [];
		}

		$filesSources = array_fill_keys($fileIds, '');

		$res = \CFile::GetList([], ['@ID' => implode(',', $fileIds)]);
		while ($file = $res->Fetch())
		{
			$fileInfo = \CFile::ResizeImageGet(
				$file,
				[
					'width' => $width,
					'height' => $height,
				],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				$immediate
			);

			$filesSources[$file['ID']] = $fileInfo['src'];
		}

		return $filesSources;
	}
}