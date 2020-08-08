<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\Zip\Archive;
use Bitrix\Main\Engine\Response\Zip\ArchiveEntry;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Structure;

Loc::loadLanguageFile(__FILE__);

class Configuration extends Controller
{
	/**
	 * Download zip export.
	 * @return Archive
	 */
	public function downloadAction()
	{
		if (\CRestUtil::isAdmin() && Helper::getInstance()->enabledZipMod())
		{
			$postfix = $this->getRequest()->getQuery('postfix');
			if (!empty($postfix))
			{
				$context = Helper::getInstance()->getContextUser($postfix);
				$structure = new Structure($context);

				$name = $structure->getArchiveName();
				if(empty($name))
				{
					$name = Helper::DEFAULT_ARCHIVE_NAME;
				}
				$name .= '.'.Helper::DEFAULT_ARCHIVE_FILE_EXTENSIONS;

				$archive = new Archive($name);

				$files = [];
				$fileList = $structure->getFileList();
				if(is_array($fileList))
				{
					$folderName = Helper::STRUCTURE_FILES_NAME;
					foreach ($fileList as $id => $file)
					{
						$entry = ArchiveEntry::createFromFileId($id);
						if ($entry)
						{
							$files[$id] = array_merge(
								[
									'NAME' => $entry->getName(),
								],
								$file
							);
							$entry->setName("/{$folderName}/{$id}");
							$archive->addEntry($entry);
						}
					}
				}

				if($files)
				{
					$structure->saveContent(false, Helper::STRUCTURE_FILES_NAME, $files);
				}

				$folderFiles = $structure->getConfigurationFileList();
				foreach ($folderFiles as $id => $name)
				{
					$entry = ArchiveEntry::createFromFileId($id);
					if ($entry)
					{
						$entry->setName($name);
						$archive->addEntry($entry);
					}
				}

				return $archive;
			}
		}

		return null;
	}

	public function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication()
		];
	}
}