<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Response\Zip;
use Bitrix\Main\Engine\Response\Zip\Archive;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Response;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Setting;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\Manifest;

Loc::loadLanguageFile(__FILE__);

class Configuration extends Controller implements Errorable
{
	/**
	 * Download zip export.
	 * @return Archive
	 */
	public function downloadAction()
	{
		if (Helper::getInstance()->enabledZipMod())
		{
			$postfix = $this->getRequest()->getQuery('postfix');
			if (!empty($postfix))
			{
				$context = Helper::getInstance()->getContextUser($postfix);
				$setting = new Setting($context);
				$access = Manifest::checkAccess(Manifest::ACCESS_TYPE_EXPORT, $setting->get(Setting::MANIFEST_CODE));
				if ($access['result'] === true)
				{
					$structure = new Structure($context);

					$name = $structure->getArchiveName();
					if(empty($name))
					{
						$name = Helper::DEFAULT_ARCHIVE_NAME;
					}
					$name .= '.' . Helper::DEFAULT_ARCHIVE_FILE_EXTENSIONS;

					$archive = new Archive($name);

					$files = [];
					$fileList = $structure->getFileList();
					$archiveEntryBuilder = new Zip\EntryBuilder();
					if (is_array($fileList))
					{
						$folderName = Helper::STRUCTURE_FILES_NAME;
						foreach ($fileList as $file)
						{
							$id = (int)$file['ID'];
							$fileArray = \CFile::getFileArray($id);
							if ($fileArray)
							{
								$entry = $archiveEntryBuilder->createFromFileArray($fileArray, $folderName . '/' . $id);
								$files[$id] = array_merge(
									[
										'NAME' => $fileArray['ORIGINAL_NAME'],
									],
									$file
								);
								$archive->addEntry($entry);
							}
						}
					}

					if ($files)
					{
						$structure->saveContent(false, Helper::STRUCTURE_FILES_NAME, $files);
					}

					$smallFilesList = $structure->listSmallFile();
					if ($smallFilesList)
					{
						$structure->saveContent(false, Helper::STRUCTURE_SMALL_FILES_NAME, $smallFilesList);
					}

					$folderFiles = $structure->getConfigurationFileList();
					foreach ($folderFiles as $file)
					{
						$entry = $archiveEntryBuilder->createFromFileId((int)$file['ID'], $file['NAME']);
						if ($entry)
						{
							$archive->addEntry($entry);
						}
					}

					return $archive;
				}
			}
		}

		return null;
	}

	public function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\Scope(ActionFilter\Scope::NOT_REST),
		];
	}
}