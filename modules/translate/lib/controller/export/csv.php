<?php
namespace Bitrix\Translate\Controller\Export;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Csv
	extends Translate\Controller\Controller
	implements Translate\Controller\IProcessParameters
{
	use Translate\Controller\ProcessParams;

	const SETTING_ID = 'TRANSLATE_EXPORT';

	const ACTION_EXPORT = 'export';
	const ACTION_EXPORT_PATH = 'exportPath';
	const ACTION_EXPORT_FILE = 'exportFile';
	const ACTION_EXPORT_FILE_LIST = 'exportFileList';
	const ACTION_EXPORT_FILE_SEARCH = 'exportFileSearch';
	const ACTION_EXPORT_PHRASE_SEARCH = 'exportPhraseSearch';
	const ACTION_PURGE = 'purge';
	const ACTION_CANCEL = 'cancel';
	const ACTION_DOWNLOAD = 'download';
	const ACTION_CLEAR = 'clear';

	/** @var int Session tab counter. */
	private $tabId = 0;

	/** @var Translate\Filter */
	private $filter;

	/** @var bool */
	private $convertEncoding;

	/** @var string */
	private $encodingOut;

	/** @var bool */
	private $collectUntranslated;

	/** @var string[] */
	private $languages;

	/** @var array */
	private $downloadParams;

	/**
	 * Configures actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$permission = new Translate\Controller\CheckPermission(Translate\Permission::READ);

		$configureActions[self::ACTION_EXPORT] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXPORT_PATH] = array(
			'class' => Translate\Controller\Export\ExportPath::class,
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXPORT_FILE] = array(
			'class' => Translate\Controller\Export\ExportFile::class,
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXPORT_FILE_LIST] = array(
			'class' => Translate\Controller\Export\ExportFileList::class,
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXPORT_FILE_SEARCH] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_EXPORT_PHRASE_SEARCH] = array(
			'+prefilters' => array(
				$permission
			),
		);

		$configureActions[self::ACTION_PURGE] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_CANCEL] = array(
			'+prefilters' => array(
				$permission
			),
		);
		$configureActions[self::ACTION_CLEAR] = array(
			'+prefilters' => array(
				$permission
			),
		);

		$configureActions[self::ACTION_DOWNLOAD] = array(
			'-prefilters' => array(
				Main\Engine\ActionFilter\Csrf::class,
			),
			'+prefilters' => array(
				$permission
			),
		);

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
		if (is_array($languages) && !in_array('all', $languages))
		{
			$languages = array_intersect($languages, $enabledLanguages);
			$sortLang = array_flip($enabledLanguages);
			usort(
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
	 */
	public function exportAction($tabId, $path = '')
	{
		if (empty($tabId) || (int)$tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		/** @var Translate\Controller\Export\ExportAction | Translate\Controller\Export\ExportFileList $action */
		$action = $this->detectAction($path);

		$result = $action->run($path, true);

		if (count($action->getErrors()) > 0)
		{
			$this->addErrors($action->getErrors());
		}

		if ($action instanceof Translate\Controller\ITimeLimit)
		{
			if ($action->hasProcessCompleted() && $result['TOTAL_ITEMS'] == 0)
			{
				$result['SUMMARY'] = Loc::getMessage('TR_EXPORT_VOID');
			}
			else
			{
				$fileProperties = $action->getDownloadingParameters();

				$messagePlaceholders = array(
					'#TOTAL_PHRASES#' => $result['TOTAL_PHRASES'],
					'#FILE_SIZE_FORMAT#' => \CFile::FormatSize($fileProperties['fileSize']),
				);

				if ($action->hasProcessCompleted())
				{
					$result['SUMMARY'] =
						Loc::getMessage('TR_EXPORT_COMPLETED')."\n".
						Loc::getMessage('TR_EXPORT_ACTION_EXPORT', $messagePlaceholders);

					$result['FILE_NAME'] = $fileProperties['fileName'];
					$result['DOWNLOAD_LINK'] = $this->generateDownloadLink($fileProperties);
				}
				else
				{
					$result['SUMMARY'] = Loc::getMessage('TR_EXPORT_ACTION_EXPORT', $messagePlaceholders);
				}
			}
		}
		else
		{
			$fileProperties = $action->getDownloadingParameters();

			$messagePlaceholders = array(
				'#TOTAL_PHRASES#' => $result['TOTAL_PHRASES'],
				'#FILE_SIZE_FORMAT#' => \CFile::FormatSize($fileProperties['fileSize']),
			);

			$result['SUMMARY'] =
				Loc::getMessage('TR_EXPORT_COMPLETED')."\n".
				Loc::getMessage('TR_EXPORT_ACTION_EXPORT', $messagePlaceholders);

			$result['FILE_NAME'] = $fileProperties['fileName'];
			$result['DOWNLOAD_LINK'] = $this->generateDownloadLink($fileProperties);
		}

		return $result;
	}


	/**
	 * Resolves action type.
	 *
	 * @param string $path Path to indexing.
	 *
	 * @return Translate\Controller\Export\ExportAction
	 */
	private function detectAction($path)
	{
		// I. Based on pure file list.
		$nextAction = self::ACTION_EXPORT_FILE_LIST;
		$exporterClass = ExportFileList::class;

		// II. Based on file search.
		if (
			!empty($this->filter['FILE_NAME']) ||
			!empty($this->filter['FOLDER_NAME']) ||
			!empty($this->filter['INCLUDE_PATHS']) ||
			!empty($this->filter['EXCLUDE_PATHS'])
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
			!empty($this->filter['PHRASE_CODE']) ||
			!empty($this->filter['INCLUDE_PHRASE_CODES']) ||
			!empty($this->filter['EXCLUDE_PHRASE_CODES']) ||
			!empty($this->filter['PHRASE_TEXT'])
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
		if (preg_match("/\.php$/", $path))
		{
			$nextAction = self::ACTION_EXPORT_FILE;
			$exporterClass = ExportFile::class;
		}


		/** @var Translate\Controller\Export\ExportAction $action */
		$action = new $exporterClass(
			$nextAction,
			$this,
			[
				'tabId' => $this->tabId,
				'collectUntranslated' => $this->collectUntranslated,
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
	 */
	public function clearAction($tabId)
	{
		return $this->purgeAction($tabId);
	}


	/**
	 * Deletes generated file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return array
	 */
	public function purgeAction($tabId)
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
	 */
	public function cancelAction($tabId)
	{
		if (empty($tabId))
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}

		$this->keepField('downloadParams')->restoreProgressParameters();

		if (!empty($this->downloadParams['filePath']))
		{
			$path = new Main\IO\File($this->downloadParams['filePath']);
			if ($path->isExists())
			{
				$path->delete();
			}
		}
		$this->clearProgressParameters();

		return array(
			'SUMMARY' => Loc::getMessage('TR_EXPORT_ACTION_CANCEL'),
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		);
	}


	/**
	 * Generate link to download local exported temporally file.
	 *
	 * @param array $params Parameters for download link.
	 *
	 * @return string
	 */
	private function generateDownloadLink($params)
	{
		$this->downloadParams = $params;
		$this->keepField('downloadParams')->saveProgressParameters();

		return $this->getActionUri(self::ACTION_DOWNLOAD, ['tabId' => $this->tabId])->getUri();
	}

	/**
	 * Starts downloading genereted file.
	 *
	 * @param int $tabId Id of session storage.
	 *
	 * @return \Bitrix\Main\HttpResponse|void
	 */
	public function downloadAction($tabId)
	{
		if (empty($tabId) || (int)$tabId <= 0)
		{
			throw new Main\ArgumentException("Missing 'tabId' parameter");
		}

		$this->keepField('downloadParams')->restoreProgressParameters();

		if (!empty($this->downloadParams['filePath']) && !empty($this->downloadParams['fileName']))
		{
			$path = new Main\IO\File($this->downloadParams['filePath']);
			if ($path->isExists())
			{
				$response = new Main\Engine\Response\File(
					$path->getPath(),
					$this->downloadParams['fileName'],
					$this->downloadParams['fileType']
				);

				return $response;
			}
		}

		$this->addError(new Error('File not found'));
	}
}
