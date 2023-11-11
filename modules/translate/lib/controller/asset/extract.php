<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


/**
 * Harvester of the lang folder disposition.
 */
class Extract
	extends Translate\Controller\Action
	implements Translate\Controller\IProcessParameters
{
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $archiveFilePath;

	/** @var string */
	private $archiveFileType;

	/** @var Translate\IO\Archiver */
	private $archiveFile;

	/** @var string */
	private $tmpFolderPath;

	/** @var Translate\IO\Directory */
	private $tmpFolder;

	/** @var int */
	private $totalFileCount;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		$this->keepField(['archiveFilePath', 'archiveFileType', 'tmpFolderPath', 'totalFileCount']);

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @return array
	 */
	public function run()
	{
		// continue previous process
		$progressParams = $this->getProgressParameters();

		$this->archiveFilePath = $progressParams['archiveFilePath'];
		$this->archiveFileType = $progressParams['archiveFileType'];

		$this->totalFileCount = 0;


		$this->tmpFolder = Translate\IO\Directory::generateTemporalDirectory('translate');
		if (!$this->tmpFolder->isExists() || !$this->tmpFolder->isDirectory())
		{
			$this->addError(new Error(
				Loc::getMessage('TR_ERROR_CREATE_TEMP_FOLDER', ['#PATH#' => $this->tmpFolder->getPhysicalPath()])
			));
		}
		else
		{
			$this->tmpFolderPath = $this->tmpFolder->getPhysicalPath();
		}

		$this->archiveFile = new Translate\IO\Archiver($this->archiveFilePath);
		if (!$this->archiveFile->isExists() || !$this->archiveFile->isFile())
		{
			$this->addError(
				new Error(Loc::getMessage('TR_ERROR_OPEN_FILE', ['#FILE#' => $this->archiveFilePath]))
			);
		}
		elseif ($this->archiveFileType !== '.tar.gz' && $this->archiveFileType !== '.tar')
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_TARFILE_EXTENTION')));
		}

		if (!$this->hasErrors())
		{
			if ($this->archiveFile->extract($this->tmpFolder) !== true)
			{
				if ($this->archiveFile->hasErrors())
				{
					$this->addErrors($this->archiveFile->getErrors());
				}
				else
				{
					$this->addError(
						new Main\Error(Loc::getMessage('TR_ERROR_ARCHIVE'))
					);
				}
			}
			else
			{
				$this->totalFileCount = $this->archiveFile->getProcessedFileCount();
			}

			// we have to continue process in next action
			$this->processToken = null;

			$this->saveProgressParameters();
		}

		return [
			'STATUS' => Translate\Controller\STATUS_COMPLETED,
			'PROCESSED_ITEMS' => $this->totalFileCount,
			'TOTAL_ITEMS' => $this->totalFileCount,
		];
	}


	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	public function getProgressParameterOptionName()
	{
		$controller = $this->getController();
		return $controller::SETTING_ID;
	}
}