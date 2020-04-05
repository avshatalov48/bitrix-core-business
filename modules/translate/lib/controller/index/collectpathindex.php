<?php
namespace Bitrix\Translate\Controller\Index;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * Harvester of the lang files disposition.
 */
class CollectPathIndex
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var int */
	private $seekPathLangId;

	/** @var string[] */
	private $languages;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField(['seekPathLangId', 'languages']);

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @param string $path Path to indexing.
	 * @return array
	 */
	public function run($path = '')
	{
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		if (preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$path = $matches[1];
		}

		$path = '/'. trim($path, '/.\\');

		// skip indexing if index exists
		if (Main\Context::getCurrent()->getRequest()->get('checkIndexExists') === 'Y')
		{
			$indexPath = Translate\Index\PathIndex::loadByPath($path);
			if ($indexPath instanceof Translate\Index\PathIndex)
			{
				if ($indexPath->getIndexed())
				{
					return array(
						'STATUS' => Translate\Controller\STATUS_COMPLETED
					);
				}
			}
		}

		if ($this->isNewProcess)
		{
			$languages = $this->controller->getRequest()->get('languages');
			if (!empty($languages) && $this->languages !== 'all')
			{
				$languages = explode(',', $languages);
				if (is_array($languages))
				{
					$languages = array_intersect($languages, Translate\Config::getEnabledLanguages());
					if (!empty($languages))
					{
						$this->languages = $languages;
					}
				}
			}

			$indexer = new Index\PathIndexCollection();
			$filter = new Translate\Filter(['path' => $path]);
			if (!empty($this->languages))
			{
				$filter->langId = $this->languages;
			}

			$this->totalItems = $indexer->countItemsToProcess($filter);
			$this->processedItems = 0;

			$this->saveProgressParameters();

			$indexer->purge($filter);

					/*
				->unvalidate($filter);
					'indexedTime' => new Main\Type\DateTime(
						date('Y-m-d H:i:s', strtotime('-'.self::EXPIRATION_DEPTH)),
						'Y-m-d H:i:s'
					)*/

			$this->instanceTimer()->setTimeLimit(5);
			$this->isNewProcess = false;
		}
		else
		{
			$progressParams = $this->getProgressParameters();

			if (isset($progressParams['totalItems']) && (int)$progressParams['totalItems'] > 0)
			{
				$this->totalItems = (int)$progressParams['totalItems'];
				$this->processedItems = (int)$progressParams['processedItems'];
			}

			if (isset($progressParams['seekPathLangId']))
			{
				$this->seekPathLangId = $progressParams['seekPathLangId'];
			}
		}

		return $this->performStep('runIndexing', ['path' => $path]);
	}

	/**
	 * Collects lang files paths.
	 *
	 * @param array $params Path to indexing.
	 *
	 * @return array
	 */
	private function runIndexing(array $params)
	{
		$path = rtrim($params['path'], '/');

		$seek = new Translate\Filter();
		if (!empty($this->seekPathLangId))
		{
			$seek->pathLangId = $this->seekPathLangId;
		}

		$indexer = new Index\PathIndexCollection();

		$filter = new Translate\Filter(['path' => $path]);

		if ($this->processedItems >= $this->totalItems)
		{
			$indexer->validate($filter, false);
		}
		else
		{
			if (!empty($this->languages))
			{
				$filter->langId = $this->languages;
			}

			$processedItemCount = $indexer->collect($filter, $this->instanceTimer(), $seek);

			$this->processedItems += $processedItemCount;
		}

		if ($this->processedItems >= $this->totalItems && $this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}
		else
		{
			$this->seekPathLangId = $seek->nextLangPathId;
		}

		return array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		);
	}
}