<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Harvester of phrases the single file.
 */
class ExportFile
	extends ExportAction
{
	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		Loc::loadLanguageFile(__DIR__ . '/exportaction.php');

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @param string $path Path to export.
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 *
	 * @return array
	 */
	public function run($path = '', $runBefore = false)
	{
		if (empty($path) || !preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EXPORT_EMPTY_PATH_LIST')));

			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			);
		}
		if (!Translate\IO\Path::isLangDir($path))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EXPORT_FILE_NOT_LANG', array('#FILE#' => $path))));

			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED
			);
		}

		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		$langFilePath = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');

		$this->exportFileName = $this->generateExportFileName($path, $this->languages);

		$csvFile = $this->createExportTempFile($this->exportFileName);
		$csvFile->openWrite( Main\IO\FileStreamOpenMode::APPEND);

		$fullPaths = [];
		foreach ($this->languages as $langId)
		{
			$langRelPath = Translate\IO\Path::replaceLangId($path, $langId);
			$langFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langRelPath);

			if (self::$useTranslationRepository && in_array($langId, self::$translationRepositoryLanguages))
			{
				$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);
			}

			$fullPaths[$langId] = $langFullPath;
		}

		$rows = $this->mergeLangFiles($langFilePath, $fullPaths, $this->collectUntranslated);
		foreach ($rows as $row)
		{
			$csvFile->put(array_values($row));
			$this->exportedPhraseCount ++;
		}

		$this->exportFileSize = $csvFile->getSize();
		$csvFile->close();

		$result = array(
			'STATUS' => Translate\Controller\STATUS_COMPLETED,
			'PROCESSED_ITEMS' => 1,
			'TOTAL_ITEMS' => 1,
			'TOTAL_PHRASES' => $this->exportedPhraseCount,
		);

		if ($csvFile->hasErrors())
		{
			$errors = $csvFile->getErrors();
			foreach ($errors as $err)
			{
				if ($err->getCode() == Translate\IO\CsvFile::ERROR_32K_FIELD_LENGTH)
				{
					$result['WARNING'] = Loc::getMessage('TR_EXPORT_ERROR_32K_LENGTH');
				}
				else
				{
					$this->addError($err);
				}
			}
		}

		return $result;
	}
}