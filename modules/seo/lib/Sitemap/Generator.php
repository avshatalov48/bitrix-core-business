<?php

namespace Bitrix\Seo\Sitemap;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Loader;
use Bitrix\Main\IO;
use Bitrix\Main\Type\DateTime;
use Bitrix\Seo\Sitemap\Internals\SitemapTable;
use Bitrix\Seo\Sitemap\Internals\RuntimeTable;
use Bitrix\Seo\Sitemap\Type\Step;
use Bitrix\Seo\RobotsFile;

Loc::loadMessages(__DIR__ . '/../../admin/seo_sitemap.php');

/**
 * Class for create sitemap files
 */
class Generator
{
	/**
	 * Max time of run one step, in seconds
	 */
	protected const STEP_DURATION = 13;

	/**
	 * Current sitemap
	 * @var int
	 */
	protected int $sitemapId;

	/**
	 * Data of current sitemap
	 * @var array
	 */
	protected array $sitemapData = [];

	/**
	 * Current Can be init by not first step
	 * @var int - @see Bitrix\Seo\Sitemap\Type\Step
	 */
	protected int $step;

	/**
	 * To control step duration
	 * @var int
	 */
	protected int $stepStartTime;

	/**
	 * State params for current step. Can be init by not start state
	 * @var array
	 */
	protected array $state;

	/**
	 * Text message about current state
	 * @var string
	 */
	protected string $statusMessage = '';

	/**
	 * File object
	 * @var File\Base|null
	 */
	protected ?File\Base $sitemapFile = null;

	/**
	 * If iblock step - save current runtime iblock data
	 * @var array|null
	 */
	protected ?array $currentRuntimeIBlock = null;

	/**
	 * If iblock step - save current iblock data
	 * @var array|null
	 */
	protected ?array $currentIBlock = null;

	public function __construct(int $sitemapId)
	{
		if ($sitemapId <= 0)
		{
			// todo: err
		}
		$this->sitemapId = $sitemapId;

		$this->sitemapData = (SitemapTable::getById($this->sitemapId))->fetch();
		if (empty($this->sitemapData))
		{
			// todo: error
		}
		$this->sitemapData['SETTINGS'] = unserialize($this->sitemapData['SETTINGS'], ['allowed_classes' => false]);
		$this->sitemapData['SITE'] = (SiteTable::getByPrimary($this->sitemapData['SITE_ID']))->fetch();

		$this->init(Step::getFirstStep(), []);
	}

	/**
	 * Init current parameter for generator run
	 * @param int $step
	 * @param array $state
	 * @return Generator
	 */
	public function init(int $step, array $state): self
	{
		// todo: check state by whitelist
		$this->step = $step;
		$this->state = $state;

		$this->statusMessage = Loc::getMessage('SEO_SITEMAP_RUN_INIT');

		return $this;
	}

	/**
	 * Return current step (int value, not name)
	 * @return int
	 */
	public function getStep(): int
	{
		return $this->step;
	}

	/**
	 * Return current state
	 * @return array
	 */
	public function getState(): array
	{
		return $this->state;
	}

	/**
	 * Text message about current progress
	 * @return string
	 */
	public function getStatusMessage(): string
	{
		return $this->statusMessage;
	}

	/**
	 * @return bool - false if error in process
	 */
	public function run(): bool
	{
		$result = false;
		$this->startStep();

		if ($this->step === Step::STEPS[Step::STEP_INIT])
		{
			$result = $this->runInit();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_FILES])
		{
			$result = $this->runFiles();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_IBLOCK_INDEX])
		{
			$result = $this->runIblockIndex();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_IBLOCK])
		{
			$result = $this->runIblock();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_FORUM_INDEX])
		{
			$result = $this->runForumIndex();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_FORUM])
		{
			$result = $this->runForum();
		}

		elseif ($this->step < Step::STEPS[Step::STEP_INDEX])
		{
			if ($this->runIndex())
			{
				$result = $this->finish();
			}
		}

		return $result;
	}

	/**
	 * Clear previously, and init new Runtime
	 * @return bool
	 */
	protected function runInit(): bool
	{
		RuntimeTable::clearByPid($this->sitemapId);

		$isRootChecked =
			isset($this->sitemapData['SETTINGS']['DIR']['/'])
			&& $this->sitemapData['SETTINGS']['DIR']['/'] == 'Y';

		$runtimeData = [
			'PID' => $this->sitemapId,
			'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_DIR,
			'ITEM_PATH' => '/',
			'PROCESSED' => RuntimeTable::UNPROCESSED,
			'ACTIVE' => $isRootChecked ? RuntimeTable::ACTIVE : RuntimeTable::INACTIVE,
		];

		try
		{
			$resAdd = RuntimeTable::add($runtimeData);
		}
		catch (\Exception $e)
		{
			return false;
		}

		if ($resAdd->isSuccess())
		{
			$this->step++;
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FILES', ['#PATH#' => '/']);
		}

		return $resAdd->isSuccess();
	}

	protected function runFiles(): bool
	{
		$this->sitemapFile =
			new File\Runtime(
				$this->sitemapId,
				$this->sitemapData['SETTINGS']['FILENAME_FILES'],
				$this->getSitemapSettings()
			);

		$isFinished = false;
		$isCheckFinished = false;
		$dbRes = null;

		while (!$isFinished && !self::isStepTimeOver())
		{
			if (!$dbRes)
			{
				$dbRes = RuntimeTable::getList([
					'order' => ['ITEM_PATH' => 'ASC'],
					'filter' => [
						'PID' => $this->sitemapId,
						'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_DIR,
						'PROCESSED' => RuntimeTable::UNPROCESSED,
					],
					'limit' => 1000,
				]);
			}

			if ($dirData = $dbRes->Fetch())
			{
				$this->processDirectory($dirData);
				$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FILES', ['#PATH#' => $dirData['ITEM_PATH']]);
				$isCheckFinished = false;
			}
			elseif (!$isCheckFinished)
			{
				$dbRes = null;
				$isCheckFinished = true;
			}
			else
			{
				$isFinished = true;
			}
		}

		if (!$isFinished)
		{
			if ($this->step < Step::STEPS[Step::STEP_FILES] - 1)
			{
				$this->step++;
			}
		}
		else
		{
			// todo: check state by whitelist
			if (!is_array($this->state['XML_FILES']))
			{
				$this->state['XML_FILES'] = [];
			}

			$this->finishSitemapFile();

			$this->step = Step::STEPS[Step::STEP_FILES];
			$this->statusMessage = Loc::getMessage(
				'SITEMAP_RUN_FILE_COMPLETE',
				['#FILE#' => $this->sitemapData['SETTINGS']['FILENAME_FILES']]
			);
		}

		return true;
	}

	/**
	 * Add files from directory to sitemap
	 * @param $dirData
	 * @return void
	 */
	protected function processDirectory($dirData): void
	{
		$processedDirs = [];

		if ($dirData['ACTIVE'] == RuntimeTable::ACTIVE)
		{
			$directories = \CSeoUtils::getDirStructure(
				$this->sitemapData['SETTINGS']['logical'] == 'Y',
				$this->sitemapData['SITE_ID'],
				$dirData['ITEM_PATH']
			);

			foreach ($directories as $dir)
			{
				$dirKey = "/" . ltrim($dir['DATA']['ABS_PATH'], "/");

				if ($dir['TYPE'] == 'F')
				{
					if (
						!isset($this->sitemapData['SETTINGS']['FILE'][$dirKey])
						|| $this->sitemapData['SETTINGS']['FILE'][$dirKey] == 'Y'
					)
					{
						if (preg_match($this->sitemapData['SETTINGS']['FILE_MASK_REGEXP'], $dir['FILE']))
						{
							$f = new IO\File($dir['DATA']['PATH'], $this->sitemapData['SITE_ID']);
							$this->sitemapFile->addFileEntry($f);
						}
					}
				}
				else
				{
					if (!isset($this->sitemapData['SETTINGS']['DIR'][$dirKey])
						|| $this->sitemapData['SETTINGS']['DIR'][$dirKey] == 'Y')
					{
						$processedDirs[] = $dirKey;
					}
				}
			}
		}
		else
		{
			$len = mb_strlen($dirData['ITEM_PATH']);
			if (!empty($this->sitemapData['SETTINGS']['DIR']))
			{
				foreach ($this->sitemapData['SETTINGS']['DIR'] as $dirKey => $checked)
				{
					if ($checked == 'Y')
					{
						if (strncmp($dirData['ITEM_PATH'], $dirKey, $len) === 0)
						{
							$processedDirs[] = $dirKey;
						}
					}
				}
			}

			if (!empty($this->sitemapData['SETTINGS']['FILE']))
			{
				foreach ($this->sitemapData['SETTINGS']['FILE'] as $dirKey => $checked)
				{
					if ($checked == 'Y')
					{
						if (strncmp($dirData['ITEM_PATH'], $dirKey, $len) === 0)
						{
							$fileName = IO\Path::combine(
								SiteTable::getDocumentRoot($this->sitemapData['SITE_ID']),
								$dirKey
							);

							if (!is_dir($fileName))
							{
								$f = new IO\File($fileName, $this->sitemapData['SITE_ID']);
								if (
									$f->isExists()
									&& !$f->isSystem()
									&& preg_match($this->sitemapData['SETTINGS']['FILE_MASK_REGEXP'], $f->getName())
								)
								{
									$this->sitemapFile->addFileEntry($f);
								}
							}
						}
					}
				}
			}
		}

		if (is_array($processedDirs) && !empty($processedDirs))
		{
			foreach ($processedDirs as $dirKey)
			{
				$runtimeData = [
					'PID' => $this->sitemapId,
					'ITEM_PATH' => $dirKey,
					'PROCESSED' => RuntimeTable::UNPROCESSED,
					'ACTIVE' => RuntimeTable::ACTIVE,
					'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_DIR,
				];
				RuntimeTable::add($runtimeData);
			}
		}

		RuntimeTable::update($dirData['ID'], [
			'PROCESSED' => RuntimeTable::PROCESSED,
		]);
	}

	protected function runIblockIndex(): bool
	{
		$result = true;

		$iblockToProcess = [];
		if (Loader::includeModule('iblock'))
		{
			$iblockToProcess = $this->sitemapData['SETTINGS']['IBLOCK_ACTIVE'];
			if (is_array($iblockToProcess) && !empty($iblockToProcess))
			{
				$arIBlocks = [];
				$dbIBlock = \CIBlock::GetList([], ['ID' => array_keys($iblockToProcess)]);
				while ($arIBlock = $dbIBlock->Fetch())
				{
					$arIBlocks[$arIBlock['ID']] = $arIBlock;
				}

				foreach ($iblockToProcess as $iblockId => $iblockActive)
				{
					if ($iblockActive !== 'Y' || !array_key_exists($iblockId, $arIBlocks))
					{
						unset($iblockToProcess[$iblockId]);
					}
					else
					{
						RuntimeTable::add([
							'PID' => $this->sitemapId,
							'PROCESSED' => RuntimeTable::UNPROCESSED,
							'ITEM_ID' => $iblockId,
							'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_IBLOCK,
						]);
					}
				}
			}
		}

		$this->state['LEFT_MARGIN'] = 0;
		$this->state['IBLOCK_LASTMOD'] = 0;

		$this->state['IBLOCK'] = [];
		$this->state['IBLOCK_MAP'] = [];

		if (is_array($iblockToProcess) && !empty($iblockToProcess))
		{
			$this->step = Step::STEPS[Step::STEP_IBLOCK_INDEX];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_IBLOCK');
		}
		else
		{
			$this->step = Step::STEPS[Step::STEP_IBLOCK];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_IBLOCK_EMPTY');
		}

		return $result;
	}

	protected function runIblock(): bool
	{
		$result = true;
		$isFinished = false;

		$this->currentRuntimeIBlock = null;
		$this->currentIBlock = null;
		$this->sitemapFile = null;

		if (!Loader::includeModule('iblock'))
		{
			$isFinished = true;
		}

		if (isset($_SESSION["SEO_SITEMAP_" . $this->sitemapId]))
		{
			$this->state['IBLOCK_MAP'] = $_SESSION["SEO_SITEMAP_" . $this->sitemapId];
			unset($_SESSION["SEO_SITEMAP_" . $this->sitemapId]);
		}

		while (!$isFinished)
		{
			if (self::isStepTimeOver())
			{
				$isFinished = false;

				break;
			}

			if (!$this->currentRuntimeIBlock)
			{
				$dbRes = RuntimeTable::getList([
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'PID' => $this->sitemapId,
						'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_IBLOCK,
						'PROCESSED' => RuntimeTable::UNPROCESSED,
					],
					'limit' => 1,
				]);
				$runtimeIblock = $dbRes->fetch();
				$this->currentRuntimeIBlock = $runtimeIblock !== false ? $runtimeIblock : null;

				if ($this->currentRuntimeIBlock)
				{
					$iblockId = (int)$this->currentRuntimeIBlock['ITEM_ID'];

					$dbIBlock = \CIBlock::GetByID($iblockId);
					$this->currentIBlock = $dbIBlock->Fetch();

					if (!$this->currentIBlock)
					{
						$this->state['LEFT_MARGIN'] = 0;
						$this->state['IBLOCK_LASTMOD'] = 0;
						$this->state['LAST_ELEMENT_ID'] = 0;
						unset($this->state['CURRENT_SECTION']);
						unset($this->currentRuntimeIBlock);
					}
					else
					{
						$this->statusMessage = Loc::getMessage(
							'SITEMAP_RUN_IBLOCK_NAME',
							['#IBLOCK_NAME#' => $this->currentIBlock['NAME']]
						);

						if ($this->currentIBlock['LIST_PAGE_URL'] == '')
						{
							$this->sitemapData['SETTINGS']['IBLOCK_LIST'][$iblockId] = 'N';
						}
						if ($this->currentIBlock['SECTION_PAGE_URL'] == '')
						{
							$this->sitemapData['SETTINGS']['IBLOCK_SECTION'][$iblockId] = 'N';
						}
						if ($this->currentIBlock['DETAIL_PAGE_URL'] == '')
						{
							$this->sitemapData['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] = 'N';
						}

						$this->state['IBLOCK_LASTMOD'] =
							max($this->state['IBLOCK_LASTMOD'], MakeTimeStamp($this->currentIBlock['TIMESTAMP_X']));

						if (
							$this->state['LEFT_MARGIN'] <= 0
							&& $this->sitemapData['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] !== 'N'
						)
						{
							$this->state['CURRENT_SECTION'] = 0;
						}

						$fileName = str_replace(
							['#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'],
							[$iblockId, $this->currentIBlock['CODE'], $this->currentIBlock['XML_ID']],
							$this->sitemapData['SETTINGS']['FILENAME_IBLOCK']
						);

						$this->sitemapFile =
							new File\Runtime(
							$this->sitemapId,
							$fileName,
							$this->getSitemapSettings()
						);
					}
				}
			}

			if (!$this->currentRuntimeIBlock || !$this->sitemapFile)
			{
				$isFinished = true;
			}
			elseif (is_array($this->currentIBlock))
			{
				$isFinishedSections = false;
				if (!isset($this->state['CURRENT_SECTION']))
				{
					$isFinishedSections = self::processIblockSections();
				}

				if ($isFinishedSections && !isset($this->state['CURRENT_SECTION']))
				{
					$isFinishedElements = true;
				}
				else
				{
					$isFinishedElements = self::processIblockElements();
				}

				if ($isFinishedSections && $isFinishedElements)
				{
					$this->finishIblockProcess();
				}
			}
		}

		if ($this->step < Step::STEPS[Step::STEP_IBLOCK] - 1)
		{
			$this->step++;
			$this->step = min($this->step, Step::STEPS[Step::STEP_IBLOCK] -  1);
		}

		if ($isFinished)
		{
			$this->step = Step::STEPS[Step::STEP_IBLOCK];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_IBLOCK');
		}

		return $result;
	}

	/**
	 * Process sections
	 *
	 * @return bool - true if finished all sections, false if process stopped by timeout
	 */
	protected function processIblockSections(): bool
	{
		$iblockId = (int)$this->currentRuntimeIBlock['ITEM_ID'];

		$this->state['LAST_ELEMENT_ID'] = 0;
		$dbIblockResult = \CIBlockSection::GetList(
			['LEFT_MARGIN' => 'ASC'],
			[
				'IBLOCK_ID' => $iblockId,
				'GLOBAL_ACTIVE' => 'Y',
				'>LEFT_BORDER' => intval($this->state['LEFT_MARGIN']),
			],
			false,
			['ID', 'TIMESTAMP_X', 'SECTION_PAGE_URL', 'LEFT_MARGIN', 'IBLOCK_SECTION_ID'],
			['nTopCount' => 100]
		);
		$processedSections = 0;

		while ($arSection = $dbIblockResult->fetch())
		{
			if ($this->isStepTimeOver())
			{
				break;
			}

			$sectionLastmod = MakeTimeStamp($arSection['TIMESTAMP_X']);
			$this->state['LEFT_MARGIN'] = $arSection['LEFT_MARGIN'];
			$this->state['IBLOCK_LASTMOD'] = max($this->state['IBLOCK_LASTMOD'], $sectionLastmod);

			$isProcessSections = false;
			$isProcessElements = false;

			if (isset($this->sitemapData['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblockId][$arSection['ID']]))
			{
				$isProcessSections =
					$this->sitemapData['SETTINGS']['IBLOCK_SECTION_SECTION'][$iblockId][$arSection['ID']] == 'Y';
				$isProcessElements =
					$this->sitemapData['SETTINGS']['IBLOCK_SECTION_ELEMENT'][$iblockId][$arSection['ID']] == 'Y';
			}
			elseif ($arSection['IBLOCK_SECTION_ID'] > 0)
			{
				$dbRes = RuntimeTable::getList([
					'filter' => [
						'PID' => $this->sitemapId,
						'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_SECTION,
						'ITEM_ID' => $arSection['IBLOCK_SECTION_ID'],
						'PROCESSED' => RuntimeTable::PROCESSED,
					],
					'select' => ['ACTIVE', 'ACTIVE_ELEMENT'],
					'limit' => 1,
				]);

				$parentSection = $dbRes->fetch();
				if ($parentSection)
				{
					$isProcessSections = $parentSection['ACTIVE'] === RuntimeTable::ACTIVE;
					$isProcessElements = $parentSection['ACTIVE_ELEMENT'] === RuntimeTable::ACTIVE;
				}
			}
			else
			{
				$isProcessSections = $this->sitemapData['SETTINGS']['IBLOCK_SECTION'][$iblockId] == 'Y';
				$isProcessElements = $this->sitemapData['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] == 'Y';
			}

			$arRuntimeData = [
				'PID' => $this->sitemapId,
				'ITEM_ID' => $arSection['ID'],
				'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_SECTION,
				'ACTIVE' => $isProcessSections ? RuntimeTable::ACTIVE : RuntimeTable::INACTIVE,
				'ACTIVE_ELEMENT' => $isProcessElements ? RuntimeTable::ACTIVE : RuntimeTable::INACTIVE,
				'PROCESSED' => RuntimeTable::PROCESSED,
			];

			if ($isProcessSections)
			{
				$this->state['IBLOCK'][$iblockId]['S']++;
				$arSection['LANG_DIR'] = $this->sitemapData['SITE']['DIR'];

				// remove or replace SERVER_NAME
				$url =
					Source\Iblock::prepareUrlToReplace(
						$arSection['SECTION_PAGE_URL'],
						$this->sitemapData['SITE_ID']
					);
				$url = \CIBlock::ReplaceDetailUrl($url, $arSection, false, "S");

				$this->sitemapFile->addIBlockEntry($url, $sectionLastmod);
			}

			RuntimeTable::add($arRuntimeData);

			if ($isProcessElements)
			{
				$this->state['CURRENT_SECTION'] = (int)$arSection['ID'];
				$this->state['LAST_ELEMENT_ID'] = 0;

				return false;
			}

			$processedSections++;
		}

		if ($processedSections === 0)
		{
			self::finishIblockSectionsProcess();

			return true;
		}

		// not finished yet
		return false;
	}

	protected function finishIblockSectionsProcess(): void
	{
		unset($this->state['CURRENT_SECTION']);
		$this->state['LEFT_MARGIN'] = 0;
		$this->state['LAST_ELEMENT_ID'] = 0;
	}

	/**
	 * Process elements
	 * @return bool - true if finished all sections, false if process stopped by timeout
	 */
	protected function processIblockElements(): bool
	{
		if (!isset($this->state['CURRENT_SECTION']))
		{
			self::finishIblockElementsProcess();

			return true;
		}

		$iblockId = (int)$this->currentRuntimeIBlock['ITEM_ID'];
		$dbIblockResult = \CIBlockElement::GetList(
			['ID' => 'ASC'],
			[
				'IBLOCK_ID' => $iblockId,
				'ACTIVE' => 'Y',
				'SECTION_ID' => $this->state['CURRENT_SECTION'],
				'>ID' => $this->state['LAST_ELEMENT_ID'],
				'SITE_ID' => $this->sitemapData['SITE_ID'],
				"ACTIVE_DATE" => "Y",
			],
			false,
			['nTopCount' => 100],
			['ID', 'TIMESTAMP_X', 'DETAIL_PAGE_URL']
		);
		$processedElements = 0;

		while ($arElement = $dbIblockResult->fetch())
		{
			if ($this->isStepTimeOver())
			{
				break;
			}

			if (!is_array($this->state['IBLOCK_MAP'][$iblockId]))
			{
				$this->state['IBLOCK_MAP'][$iblockId] = [];
			}
			if (!array_key_exists($arElement['ID'], $this->state['IBLOCK_MAP'][$iblockId]))
			{
				$arElement['LANG_DIR'] = $this->sitemapData['SITE']['DIR'];

				$elementLastmod = MakeTimeStamp($arElement['TIMESTAMP_X']);
				$this->state['IBLOCK_LASTMOD'] = max($this->state['IBLOCK_LASTMOD'], $elementLastmod);
				$this->state['LAST_ELEMENT_ID'] = $arElement['ID'];

				$this->state['IBLOCK'][$iblockId]['E']++;
				$this->state['IBLOCK_MAP'][$iblockId][$arElement["ID"]] = 1;

				// remove or replace SERVER_NAME
				$url =
					Source\Iblock::prepareUrlToReplace(
						$arElement['DETAIL_PAGE_URL'],
						$this->sitemapData['SITE_ID']
					);
				$url = \CIBlock::ReplaceDetailUrl($url, $arElement, false, "E");

				$this->sitemapFile->addIBlockEntry($url, $elementLastmod);

				$processedElements++;
			}
		}

		if ($processedElements === 0)
		{
			self::finishIblockElementsProcess();

			return true;
		}

		// not finished yet
		return false;
	}

	protected function finishIblockElementsProcess(): void
	{
		unset($this->state['CURRENT_SECTION']);
		$this->state['LAST_ELEMENT_ID'] = 0;
	}

	protected function finishIblockProcess(): void
	{
		$iblockId = (int)$this->currentRuntimeIBlock['ID'];
		if (!$iblockId)
		{
			return;
		}

		RuntimeTable::update($iblockId, [
			'PROCESSED' => RuntimeTable::PROCESSED,
		]);

		if (
			$this->sitemapData['SETTINGS']['IBLOCK_LIST'][$iblockId] == 'Y'
			&& $this->currentIBlock['LIST_PAGE_URL'] <> ''
		)
		{
			$this->state['IBLOCK'][$iblockId]['I']++;

			$this->currentIBlock['IBLOCK_ID'] = $this->currentIBlock['ID'];
			$this->currentIBlock['LANG_DIR'] = $this->sitemapData['SITE']['DIR'];

			// remove or replace SERVER_NAME
			$url =
				Source\Iblock::prepareUrlToReplace(
					$this->currentIBlock['LIST_PAGE_URL'],
					$this->sitemapData['SITE_ID']
				);
			$url = \CIBlock::ReplaceDetailUrl($url, $this->currentIBlock, false, "");

			$this->sitemapFile->addIBlockEntry($url, $this->state['IBLOCK_LASTMOD']);
		}

		self::finishSitemapFile();

		$this->currentRuntimeIBlock = null;
		$this->state['IBLOCK_LASTMOD'] = 0;
		unset($this->state['CURRENT_SECTION']);
	}

	protected function runForumIndex(): bool
	{
		$result = true;

		$forumToProcess = [];

		if (Loader::includeModule('forum'))
		{
			if (!empty($this->sitemapData['SETTINGS']['FORUM_ACTIVE']))
			{
				foreach ($this->sitemapData['SETTINGS']['FORUM_ACTIVE'] as $forumId => $active)
				{
					if ($active == "Y")
					{
						$forumToProcess[$forumId] = "Y";
					}
				}
			}
			if (!empty($forumToProcess))
			{
				$arForums = [];
				$db_res = \CForumNew::GetListEx(
					[],
					[
						'@ID' => array_keys($forumToProcess),
						"ACTIVE" => "Y",
						"SITE_ID" => $this->sitemapData['SITE_ID'],
						"!TOPICS" => 0,
					]
				);
				while ($res = $db_res->Fetch())
				{
					$arForums[$res['ID']] = $res;
				}
				$forumToProcess = array_intersect_key($arForums, $forumToProcess);

				foreach ($forumToProcess as $id => $forum)
				{
					RuntimeTable::add([
							'PID' => $this->sitemapId,
							'PROCESSED' => RuntimeTable::UNPROCESSED,
							'ITEM_ID' => $id,
							'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_FORUM,
						]
					);
				}
			}
		}

		$this->state['FORUM_CURRENT_TOPIC'] = 0;

		if (is_array($forumToProcess) && !empty($forumToProcess))
		{
			$this->step = Step::STEPS[Step::STEP_FORUM_INDEX];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FORUM');
		}
		else
		{
			$this->step = Step::STEPS[Step::STEP_FORUM];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FORUM_EMPTY');
		}

		return $result;
	}

	protected function runForum(): bool
	{
		$result = true;

		$isFinished = false;
		$runtimeForum = false;
		$currentForum = null;
		$forumId = 0;
		$this->sitemapFile = null;
		$dbTopicResult = null;
		$arTopic = null;

		if (!Loader::includeModule('forum'))
		{
			$isFinished = true;
		}

		while (!$isFinished && !self::isStepTimeOver())
		{
			if (!$runtimeForum)
			{
				$dbRes = RuntimeTable::getList([
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'PID' => $this->sitemapId,
						'ITEM_TYPE' => RuntimeTable::ITEM_TYPE_FORUM,
						'PROCESSED' => RuntimeTable::UNPROCESSED,
					],
					'limit' => 1,
				]);
				$runtimeForum = $dbRes->fetch();

				if ($runtimeForum)
				{
					$forumId = intval($runtimeForum['ITEM_ID']);

					$db_res = \CForumNew::GetListEx(
						[],
						[
							'ID' => $forumId,
							"ACTIVE" => "Y",
							"SITE_ID" => $this->sitemapData['SITE_ID'],
							"!TOPICS" => 0,
						]
					);
					$currentForum = $db_res->Fetch();
					if ($currentForum)
					{
						$this->statusMessage = Loc::getMessage(
							'SITEMAP_RUN_FORUM_NAME',
							['#FORUM_NAME#' => $currentForum['NAME']]
						);

						$fileName = str_replace('#FORUM_ID#', $forumId, $this->sitemapData['SETTINGS']['FILENAME_FORUM']);
						$this->sitemapFile = new File\Runtime($this->sitemapId, $fileName, $this->getSitemapSettings());
					}

					RuntimeTable::update($runtimeForum['ID'], [
						'PROCESSED' => RuntimeTable::PROCESSED,
					]);
				}
			}

			if (!$runtimeForum || !$this->sitemapFile)
			{
				$isFinished = true;
			}
			elseif (is_array($currentForum))
			{
				$isActive =
					array_key_exists($forumId, $this->sitemapData['SETTINGS']['FORUM_TOPIC'])
					&& $this->sitemapData['SETTINGS']['FORUM_TOPIC'][$forumId] == "Y"
				;
				if ($isActive)
				{
					if ($dbTopicResult == null)
					{
						$dbTopicResult = \CForumTopic::GetList(
							["LAST_POST_DATE" => "DESC"],
							array_merge(
								[
									"FORUM_ID" => $forumId,
									"APPROVED" => "Y",
								],
								(
								$this->state['FORUM_CURRENT_TOPIC'] > 0
									? [">ID" => $this->state["FORUM_CURRENT_TOPIC"]]
									: []
								)
							),
							false,
							0,
							['nTopCount' => 100]
						);
					}
					if (($arTopic = $dbTopicResult->fetch()) && $arTopic)
					{
						$this->state["FORUM_CURRENT_TOPIC"] = $arTopic["ID"];
						$url = \CForumNew::PreparePath2Message(
							$currentForum["PATH2FORUM_MESSAGE"],
							[
								"FORUM_ID" => $currentForum["ID"],
								"TOPIC_ID" => $arTopic["ID"],
								"TITLE_SEO" => $arTopic["TITLE_SEO"],
								"MESSAGE_ID" => "s",
								"SOCNET_GROUP_ID" => $arTopic["SOCNET_GROUP_ID"],
								"OWNER_ID" => $arTopic["OWNER_ID"],
								"PARAM1" => $arTopic["PARAM1"],
								"PARAM2" => $arTopic["PARAM2"],
							]
						);
						$this->sitemapFile->addIBlockEntry($url, MakeTimeStamp($arTopic['LAST_POST_DATE']));
					}
				}
				else
				{
					$url = \CForumNew::PreparePath2Message(
						$currentForum["PATH2FORUM_MESSAGE"],
						[
							"FORUM_ID" => $currentForum["ID"],
							"TOPIC_ID" => $currentForum["TID"],
							"TITLE_SEO" => $currentForum["TITLE_SEO"],
							"MESSAGE_ID" => "s",
							"SOCNET_GROUP_ID" => $currentForum["SOCNET_GROUP_ID"],
							"OWNER_ID" => $currentForum["OWNER_ID"],
							"PARAM1" => $currentForum["PARAM1"],
							"PARAM2" => $currentForum["PARAM2"],
						]
					);
					$this->sitemapFile->addIBlockEntry($url, MakeTimeStamp($currentForum['LAST_POST_DATE']));
				}
				if (empty($arTopic))
				{
					RuntimeTable::update($runtimeForum['ID'], [
						'PROCESSED' => RuntimeTable::PROCESSED,
					]);

					self::finishSitemapFile();

					$runtimeForum = false;
					$dbTopicResult = null;
					$this->state['FORUM_CURRENT_TOPIC'] = 0;
				}
			}
		}
		if ($this->step < Step::STEPS[Step::STEP_FORUM] - 1)
		{
			$this->step++;
		}

		if ($isFinished)
		{
			$this->step = Step::STEPS[Step::STEP_FORUM];
			$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FINALIZE');
		}

		return $result;
	}

	protected function finishSitemapFile(): void
	{
		if (!$this->sitemapFile)
		{
			return;
		}

		if ($this->sitemapFile->isNotEmpty())
		{
			if ($this->sitemapFile->isCurrentPartNotEmpty())
			{
				$this->sitemapFile->finish();
			}
			else
			{
				$this->sitemapFile->delete();
			}

			if (!is_array($this->state['XML_FILES']))
			{
				$this->state['XML_FILES'] = [];
			}

			$xmlFiles = $this->sitemapFile->getNameList();
			$directory = $this->sitemapFile->getPathDirectory();
			foreach ($xmlFiles as &$xmlFile)
			{
				$xmlFile = $directory . $xmlFile;
			}
			$this->state['XML_FILES'] = array_unique(array_merge($this->state['XML_FILES'], $xmlFiles));
		}
		else
		{
			$this->sitemapFile->delete();
		}
	}

	protected function runIndex(): bool
	{
		$result = true;

		RuntimeTable::clearByPid($this->sitemapId);

		$this->sitemapFile = new File\Index($this->sitemapData['SETTINGS']['FILENAME_INDEX'], $this->getSitemapSettings());
		$xmlFiles = [];
		if (is_array($this->state['XML_FILES']) && !empty($this->state['XML_FILES']))
		{
			foreach ($this->state['XML_FILES'] as $xmlFile)
			{
				$xmlFiles[] = new IO\File(
					IO\Path::combine(
						$this->sitemapFile->getSiteRoot(),
						$xmlFile
					), $this->sitemapData['SITE_ID']
				);
			}
		}
		$this->sitemapFile->createIndex($xmlFiles);

		$existedSitemaps = [];
		if ($this->sitemapData['SETTINGS']['ROBOTS'] == 'Y')
		{
			$sitemapUrl = $this->sitemapFile->getUrl();

			$robotsFile = new RobotsFile($this->sitemapData['SITE_ID']);
			$robotsFile->addRule([
				RobotsFile::SITEMAP_RULE, $sitemapUrl,
			]);

			$sitemapLinks = $robotsFile->getRules(RobotsFile::SITEMAP_RULE);
			if (count($sitemapLinks) > 1) // 1 - just added rule
			{
				foreach ($sitemapLinks as $rule)
				{
					if ($rule[1] != $sitemapUrl)
					{
						$existedSitemaps[] = $rule[1];
					}
				}
			}
		}
		// todo: need show message about robots.txt
		// if (isset($arExistedSitemaps) && count($arExistedSitemaps) > 0)
		// {
		// 	echo BeginNote(), Loc::getMessage('SEO_SITEMAP_RUN_ROBOTS_WARNING', array(
		// 		"#SITEMAPS#" => "<li>" . implode("</li><li>", $arExistedSitemaps) . "</li>",
		// 		"#LANGUAGE_ID#" => LANGUAGE_ID,
		// 		"#SITE_ID#" => $arSitemap['SITE_ID'],
		// 	));
		// }

		$this->step = Step::STEPS[Step::STEP_INDEX];
		$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FINISH');

		return $result;
	}

	protected function finish(): bool
	{
		$this->statusMessage = Loc::getMessage('SITEMAP_RUN_FINISH');
		SitemapTable::update($this->sitemapId, ['DATE_RUN' => new DateTime()]);

		return true;
	}


	/**
	 * For save in Sitemap table
	 * @return array
	 */
	protected function getSitemapSettings(): array
	{
		return [
			'SITE_ID' => $this->sitemapData['SITE_ID'],
			'PROTOCOL' => $this->sitemapData['SETTINGS']['PROTO'] == 1 ? 'https' : 'http',
			'DOMAIN' => $this->sitemapData['SETTINGS']['DOMAIN'],
		];
	}

	protected function startStep(): void
	{
		$this->stepStartTime = microtime(true);
	}

	/**
	 * Return true if time is over
	 * @return bool
	 */
	protected function isStepTimeOver(): bool
	{
		return microtime(true) > ($this->stepStartTime + self::STEP_DURATION * 0.95);
	}
}
