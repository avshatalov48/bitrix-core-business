<?php
namespace Bitrix\Translate\Controller\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * Harvester of the lang folder disposition.
 */
class CollectLangPath
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $seekPath = '';

	/** @var int */
	private $seekOffset = 0;

	/** @var string[] */
	private $pathList = [];

	/** @var string[] */
	private $languages = [];


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, array $config = [])
	{
		$this->keepField(['seekPath', 'pathList', 'seekOffset', 'languages']);

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @param string $path Path to indexing.
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 * @return array
	 */
	public function run($path = '', $runBefore = false)
	{
		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		$path = '/'. \trim($path, '/.\\');

		// List of files and folders
		if ($this->isNewProcess)
		{
			$pathList = $this->controller->getRequest()->get('pathList');

			if (!empty($pathList))
			{
				$pathList = \preg_split("/[\r\n]+/", $pathList);
				\array_walk($pathList, 'trim');
				$pathList = \array_unique(\array_filter($pathList));
			}

			if (empty($pathList))
			{
				$pathList = [$path];
			}

			$checkIndexExists = $this->controller->getRequest()->get('checkIndexExists') === 'Y';

			foreach ($pathList as $testPath)
			{
				if ($checkIndexExists)
				{
					$indexPath = Index\PathIndex::loadByPath($testPath);
					if ($indexPath instanceof Index\PathIndex)
					{
						if ($indexPath->getIndexed())
						{
							continue;// skip indexing if index exists
						}
					}
				}

				if (Translate\IO\Path::isPhpFile($testPath))
				{
					if (!Translate\IO\Path::isLangDir($testPath))
					{
						continue;// skip non lang files
					}
				}

				$this->pathList[] = $testPath;
			}

			if (empty($this->pathList))
			{
				return [
					'STATUS' => Translate\Controller\STATUS_COMPLETED,
				];
			}

			$languages = $this->controller->getRequest()->get('languages');
			if (\is_array($languages) && !\in_array('all', $languages))
			{
				$languages = \array_intersect($languages, Translate\Config::getEnabledLanguages());
				if (!empty($languages))
				{
					$this->languages = $languages;
				}
			}

			$this->isNewProcess = false;
			$this->instanceTimer()->setTimeLimit(5);
		}

		return $this->performStep('runIndexing');
	}

	/**
	 * Collects lang folder paths.
	 *
	 * @return array
	 */
	private function runIndexing(): array
	{
		$indexer = new Index\PathLangCollection();

		$processedItemCount = 0;

		for ($pos = ((int)$this->seekOffset > 0 ? (int)$this->seekOffset : 0), $total = \count($this->pathList); $pos < $total; $pos ++)
		{
			$filter = new Translate\Filter();

			if (!empty($this->languages))
			{
				$filter->langId = $this->languages;
			}

			$testPath = $this->pathList[$pos];
			if (\preg_match("#(.+/lang)(/?\w*)#", $testPath, $matches))
			{
				$filter->path = $matches[1];
				$indexer->purge($filter);

				$processedItemCount += $indexer->collect($filter);//++1
			}
			else
			{
				$filter->path = $testPath;

				$seek = new Translate\Filter();
				$seek->lookForSeek = false;

				if (!empty($this->seekPath))
				{
					$seek->path = $this->seekPath;
					$seek->lookForSeek = true;
				}
				else
				{
					$indexer->purge($filter);
				}

				$processedItemCount += $indexer->collect($filter, $this->instanceTimer(), $seek);

				if ($this->instanceTimer()->hasTimeLimitReached())
				{
					if (isset($seek->nextPath))
					{
						$this->seekPath = $seek->nextPath;
					}
					break;
				}

				$this->seekPath = null;
			}

			// check user abortion
			if (\connection_status() !== \CONNECTION_NORMAL)
			{
				throw new Main\SystemException('Process has been broken course user aborted connection.');
			}

			if (isset($this->pathList[$pos + 1]))
			{
				$this->seekOffset = $pos + 1;//next
			}
			else
			{
				$this->seekOffset = null;
				$this->declareAccomplishment();
				$this->clearProgressParameters();
			}

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				break;
			}
		}

		$this->processedItems += $processedItemCount;
		$this->totalItems += $processedItemCount;

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->processedItems = $this->totalItems = (new Index\PathIndexCollection())->countItemsToProcess($filter);

			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		return [
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		];
	}
}