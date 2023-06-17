<?php
namespace Bitrix\Landing\Controller;

use Bitrix\Landing\Connector;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\Error;
use Bitrix\Main\UI\Viewer;

class DiskFile extends Controller
{
	private const FILE_DOWNLOAD_URL = '/bitrix/services/main/ajax.php?' .
										'action=landing.api.diskFile.download&' .
										'fileId=#fileId#&blockId=#blockId#&scope=#scope#';

	public function getDefaultPreFilters(): array
	{
		return [];
	}

	/**
	 * Returns URL for download action.
	 *
	 * @param string $scope Scope code (site type).
	 * @param int $blockId Block id.
	 * @param int|null $fileId File id.
	 * @return string
	 */
	public static function getDownloadLink(string $scope, int $blockId, ?int $fileId = null): string
	{
		return str_replace(
			['#scope#', '#blockId#', '#fileId#'],
			[$scope, $blockId, $fileId ?: '#fileId#'],
			self::FILE_DOWNLOAD_URL . '&ver=' . time()
		);
	}

	/**
	 * Checks that current user has permissions for specified file.
	 *
	 * @param string $scope Scope code (site type).
	 * @param int $blockId Block id.
	 * @param int $fileId File id.
	 * @return bool
	 */
	private function blockContainsFile(string $scope, int $blockId, int $fileId): bool
	{
		if (\Bitrix\Landing\Site\Type::isPublicScope($scope))
		{
			return false;
		}

		\Bitrix\Landing\Site\Type::setScope($scope);
		$needed = Connector\Disk::FILE_PREFIX_HREF . $fileId;
		$needed2 = Connector\Disk::FILE_NEW_PREFIX_HREF . $fileId;

		return (
			\Bitrix\Landing\Block::isContains($blockId, $needed)
			|| \Bitrix\Landing\Block::isContains($blockId, $needed2)
		);
	}

	/**
	 * Downloads file after check permissions.
	 *
	 * @param string $scope Scope code (site type).
	 * @param int $blockId Block id.
	 * @param int $fileId File id.
	 * @return BFile|null
	 */
	public function downloadAction(string $scope, int $blockId, int $fileId): ?BFile
	{
		if ($this->blockContainsFile($scope, $blockId, $fileId))
		{
			$fileInfo = \Bitrix\Landing\Connector\Disk::getFileInfo($fileId, false);
			if ($fileInfo)
			{
				return new BFile(\CFile::getFileArray($fileInfo['ID']), $fileInfo['NAME']);
			}
		}

		$this->addError(new Error('Access denied.'));
		return null;
	}

	/**
	 * Returns file info for viewer after check permissions.
	 *
	 * @param string $scope Scope code (site type).
	 * @param int $blockId Block id.
	 * @param int $fileId File id.
	 * @return array|null
	 */
	public function viewAction(string $scope, int $blockId, int $fileId): ?array
	{
		if ($this->blockContainsFile($scope, $blockId, $fileId))
		{
			$fileInfo = \Bitrix\Landing\Connector\Disk::getFileInfo($fileId, false);
			if ($fileInfo)
			{
				$urlToDownload = $this->getDownloadLink($scope, $blockId, $fileId);
				$attributes = Viewer\ItemAttributes::tryBuildByFileId($fileInfo['ID'], $urlToDownload);
				$attributes->setTitle($fileInfo['NAME']);
				return $attributes->getAttributes();
			}
		}

		$this->addError(new Error('Access denied.'));
		return null;
	}

	/**
	 * Returns raw file info.
	 *
	 * @param int $fileId File id.
	 * @return array|null
	 */
	public function infoAction(int $fileId): ?array
	{
		return \Bitrix\Landing\Connector\Disk::getFileInfo($fileId);
	}
}
