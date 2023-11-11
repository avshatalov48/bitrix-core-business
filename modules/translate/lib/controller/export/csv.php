<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Csv
	extends Translate\Controller\Controller
	implements Translate\Controller\IProcessParameters
{
	use Translate\Controller\ProcessParams;

	public const SETTING_ID = 'TRANSLATE_EXPORT';

	public const ACTION_EXPORT = 'export';
	public const ACTION_EXPORT_PATH = 'exportPath';
	public const ACTION_EXPORT_FILE = 'exportFile';
	public const ACTION_EXPORT_FILE_LIST = 'exportFileList';
	public const ACTION_EXPORT_FILE_SEARCH = 'exportFileSearch';
	public const ACTION_EXPORT_PHRASE_SEARCH = 'exportPhraseSearch';
	public const ACTION_PURGE = 'purge';
	public const ACTION_CANCEL = 'cancel';
	public const ACTION_DOWNLOAD = 'download';
	public const ACTION_CLEAR = 'clear';

	/** @var int Session tab counter. */
	private int $tabId = 0;

	private Translate\Filter $filter;

	private bool $convertEncoding;

	private string $encodingOut;

	private bool $collectUntranslated;

	private bool $appendSamples;
	private int $samplesCount = 10;
	private array $samplesRestriction = [];

	/** @var string[] */
	private array $languages;

	/** @var array Fields to keep download file attributes */
	private array $export;
	private array $samples;

	/**
	 * Configures actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$permission = new Translate\Controller\CheckPermission(Translate\Permission::READ);

		$configureActions[self::ACTION_EXPORT] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_EXPORT_PATH] = [
			'class' => Translate\Controller\Export\ExportPath::class,
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_EXPORT_FILE] = [
			'class' => Translate\Controller\Export\ExportFile::class,
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_EXPORT_FILE_LIST] = [
			'class' => Translate\Controller\Export\ExportFileList::class,
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_EXPORT_FILE_SEARCH] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_EXPORT_PHRASE_SEARCH] = [
			'+prefilters' => [
				$permission
			],
		];

		$configureActions[self::ACTION_PURGE] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_CANCEL] = [
			'+prefilters' => [
				$permission
			],
		];
		$configureActions[self::ACTION_CLEAR] = [
			'+prefilters' => [
				$permission
			],
		];

		$configureActions[self::ACTION_DOWNLOAD] = [
			'-prefilters' => [
				Main\Engine\ActionFilter\Csrf::class,
			],
			'+prefilters' => [
				$permission
			],
		];

		return $configureActions;
	}


	/**
	 * Initializes controller.
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		if ($this->request->get('tabId') !== null)
		{
			$this->tabId = (int)$this->request->get('tabId');
			$this->filter = new Translate\Filter($this->tabId);
		}

		// untranslated only
		$this->collectUntranslated = ($this->request->get('collectUntranslated') === 'Y');

		// with samples
		$this->appendSamples = ($this->request->get('appendSamples') === 'Y');
		$this->samplesCount = (int)$this->request->get('samplesCount') ?: 10;

		$this->samplesRestriction = [];
		if (!empty($this->request->get('samplesRestriction')))
		{
			$this->samplesRestriction = array_filter($this->request->get('samplesRestriction'), 'intVal');
		}

		//  encoding
		$this->convertEncoding = ($this->request->get('convertEncoding') === 'Y');

		$this->encodingOut = '';
		if ($this->convertEncoding)
		{
			$this->encodingOut = 'utf-8';
		}

		// languages
		$enabledLanguages = Translate\Config::getEnabledLanguages();
		$languages = $this->request->get('languages');
		if (\is_array($languages) && !\in_array('all', $languages))
		{
			$languages = \array_intersect($languages, $enabledLanguages);
			$sortLang = \array_flip($enabledLanguages);
			\usort(
				$languages,
				function ($a, $b) use ($sortLang)
				{
					$a = $sortLang[$a];
					$b = $sortLang[$b];
					return (($a == $b) ? 1 : ($a < $b ? -1 : 1));
				}
			);
		}
		else
		{
			$languages = $enabledLanguages;
		}
		$this->languages = $languages;
	}


	/**
	 * Runs controller action.
	 *
	 * @param int $tabId Id of session storage.
	 * @param string $path Path to indexing.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function exportAction($tabId, $path = ''): array
	{
		if (empty($tabId) || (int)$tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		/** @var ExportAction|ExportFileList $action */
		$action = $this->detectAction($path);

		$result = $action->run($path, true);

		if (\count($action->getErrors()) > 0)
		{
			$this->addErrors($action->getErrors());
		}

		if ($action->hasProcessCompleted() && $result['TOTAL_ITEMS'] == 0)
		{
			$result['SUMMARY'] = Loc::getMessage('TR_EXPORT_VOID');
		}
		elseif ($action->hasProcessCompleted())
		{
			$fileProperties = $action->getDownloadingParameters();
			$result['FILE_NAME'] = $fileProperties['fileName'];
			$result['DOWNLOAD_LINK'] = $this->generateDownloadLink($fileProperties, 'export');

			$messagePlaceholders = [
				'#TOTAL_PHRASES#' => $result['TOTAL_PHRASES'],
				'#FILE_SIZE_FORMAT#' => \CFile::formatSize($fileProperties['fileSize']),
				'#FILE_NAME#' => $result['FILE_NAME'],
				'#FILE_LINK#' => $result['DOWNLOAD_LINK'],
			];
			$result['SUMMARY'] =
				Loc::getMessage('TR_EXPORT_COMPLETED')
				. "\n". Loc::getMessage('TR_EXPORT_ACTION_EXPORT', $messagePlaceholders)
				. " ". Loc::getMessage('TR_EXPORT_DOWNLOAD', $messagePlaceholders)
			;

			if ($this->appendSamples)
			{
				if ($result['TOTAL_SAMPLES'] > 0)
				{
					$fileSamplesProperties = $action->getDownloadingSamplesParameters();
					$result['SAMPLES_LINK'] = $this->generateDownloadLink($fileSamplesProperties, 'samples');
					$result['SAMPLES_FILE'] = $fileSamplesProperties['fileName'];

					$messagePlaceholders = [
						'#TOTAL_SAMPLES#' => $result['TOTAL_SAMPLES'],
						'#FILE_SIZE_FORMAT#' => \CFile::formatSize($fileSamplesProperties['fileSize']),
						'#FILE_NAME#' => $result['SAMPLES_FILE'],
						'#FILE_LINK#' => $result['SAMPLES_LINK'],
					];
					$result['SUMMARY'] .= "\n" . Loc::getMessage('TR_EXPORT_SAMPLES', $messagePlaceholders);
					$result['SUMMARY'] .= " " . Loc::getMessage('TR_EXPORT_DOWNLOAD', $messagePlaceholders);
				}
				else
				{
					$result['SUMMARY'] .= "\n" . Loc::getMessage('TR_EXPORT_SAMPLES_NOT_FOUND');
				}
			}
		}

		return $result;
	}


	/**
	 * Resolves action type.
	 *
	 * @param string $path Path to indexing.
	 *
	 * @return ExportAction
	 */
	private function detectAction($path): ExportAction
	{
		// I. Based on pure file list.
		$nextAction = self::ACTION_EXPORT_FILE_LIST;
		$exporterClass = ExportFileList::class;

		// II. Based on file search.
		if (
			!empty($this->filter['FILE_NAME'])
			|| !empty($this->filter['FOLDER_NAME'])
			|| !empty($this->filter['INCLUDE_PATHS'])
			|| !empty($this->filter['EXCLUDE_PATHS'])
		)
		{
			$nextAction = self::ACTION_EXPORT_FILE_SEARCH;
			$exporterClass = ExportFileSearch::class;
		}

		// III. List of files and folders
		$pathList = $this->request->get('pathList');
		if (!empty($pathList))
		{
			$nextAction = self::ACTION_EXPORT_PATH;
			$exporterClass = ExportPath::class;
		}

		// IV. Based on phrase search.
		if (
			!empty($this->filter['PHRASE_CODE'])
			|| !empty($this->filter['INCLUDE_PHRASE_CODES'])
			|| !empty($this->filter['EXCLUDE_PHRASE_CODES'])
			|| !empty($this->filter['PHRASE_TEXT'])
		)
		{
			$nextAction = self::ACTION_EXPORT_PHRASE_SEARCH;
			$exporterClass = ExportPhraseSearch::class;

			// V. List of files with codes
			$codeList = $this->request->get('codeList');
			if (!empty($pathList) && !empty($codeList))
			{
				$nextAction = self::ACTION_EXPORT_PATH;
				$exporterClass = ExportPath::class;
			}
		}

		// VI. Single file
		if (\preg_match("/\.php$/", $path))
		{
			$nextAction = self::ACTION_EXPORT_FILE;
			$exporterClass = ExportFile::class;
		}


		/** @var ExportAction $action */
		$action = new $exporterClass(
			$nextAction,
			$this,
			[
				'tabId' => $this->tabId,
				'collectUntranslated' => $this->collectUntranslated,
				'appendSamples' => $this->appendSamples,
				'samplesCount' => $this->samplesCount,
				'samplesRestriction' => $this->samplesRestriction,
				'convertEncoding' => $this->convertEncoding,
				'encodingOut' => $this->encodingOut,
				'languages' => $this->languages,
				'filter' => $this->filter,
			]
		);

		return $action;
	}


	/**
	 * Deletes generated file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function clearAction($tabId): array
	{
		return $this->purgeAction($tabId);
	}


	/**
	 * Deletes generated file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function purgeAction($tabId): array
	{
		$result = $this->cancelAction($tabId);

		$result['SUMMARY'] = Loc::getMessage('TR_EXPORT_FILE_DROPPED');

		return $result;
	}


	/**
	 * Deletes generated file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function cancelAction($tabId): array
	{
		if (empty($tabId))
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}

		$this
			->keepField('export')
			->keepField('samples')
			->restoreProgressParameters();

		foreach (['export', 'samples'] as $type)
		{
			if (!empty($this->{$type}['filePath']))
			{
				$path = new Main\IO\File($this->{$type}['filePath']);
				if ($path->isExists())
				{
					$path->delete();
				}
			}
		}

		$this->clearProgressParameters();

		return [
			'SUMMARY' => Loc::getMessage('TR_EXPORT_ACTION_CANCEL'),
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		];
	}


	/**
	 * Generate link to download local exported temporally file.
	 *
	 * @param array $params Parameters for download link.
	 *
	 * @return string
	 */
	private function generateDownloadLink(array $params, string $type): string
	{
		$this->{$type} = $params;
		$this->keepField($type)->saveProgressParameters();

		return $this->getActionUri(self::ACTION_DOWNLOAD, ['tabId' => $this->tabId, 'type' => $type])->getUri();
	}

	/**
	 * Starts downloading genereted file.
	 *
	 * @param int $tabId Id of session storage.
	 * @param string $type
	 * @return HttpResponse|void
	 * @throws ArgumentException
	 */
	public function downloadAction(int $tabId, string $type)
	{
		if ($tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}
		if (empty($type) || !in_array($type, ['export', 'samples'], true))
		{
			throw new Main\ArgumentException("Missing 'type' parameter");
		}

		$this->keepField($type)->restoreProgressParameters();

		if (!empty($this->{$type}['filePath']) && !empty($this->{$type}['fileName']))
		{
			$path = new Main\IO\File($this->{$type}['filePath']);
			if ($path->isExists())
			{
				$response = new Main\Engine\Response\File(
					$path->getPath(),
					$this->{$type}['fileName'],
					$this->{$type}['fileType']
				);

				return $response;
			}
		}

		$this->addError(new Error('File not found'));
	}
}
