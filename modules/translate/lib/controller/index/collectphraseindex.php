<?php
namespace Bitrix\Translate\Controller\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * The phrases index harvester.
 */
class CollectPhraseIndex
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $seekPathId = '';

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
		$this->keepField(['seekPathId', 'languages']);

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Runs controller action.
	 *
	 * @param string $path Lang folder path to index.
	 *
	 * @return array
	 */
	public function run($path = '')
	{
		if (empty($path))
		{
			$path = Translate\Config::getDefaultPath();
		}

		if (\preg_match("#(.+\/lang)(\/?\w*)#", $path, $matches))
		{
			$path = $matches[1];
		}

		$path = '/'. \trim($path, '/.\\');

		if ($this->isNewProcess)
		{
			Index\Internals\PhraseFts::checkTables();

			$languages = $this->controller->getRequest()->get('languages');
			if (\is_array($languages) && !\in_array('all', $languages))
			{
				$languages = \array_intersect($languages, Translate\Config::getEnabledLanguages());
				if (!empty($languages))
				{
					$this->languages = $languages;
				}
			}

			$filter = new Translate\Filter(['path' => $path]);
			if (!empty($this->languages))
			{
				$filter->langId = $this->languages;
			}

			$this->totalItems = (new Index\PhraseIndexCollection())->countItemsToProcess($filter);

			$this->saveProgressParameters();

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

			if (isset($progressParams['seekPathId']))
			{
				$this->seekPathId = $progressParams['seekPathId'];
			}
		}

		return $this->performStep('runIndexing', ['path' => $path]);
	}

	/**
	 * Collects phrases.
	 *
	 * @param array $params Path to indexing.
	 *
	 * @return array
	 */
	private function runIndexing(array $params): array
	{
		$path = \rtrim($params['path'], '/');

		$seek = new Translate\Filter();
		if (!empty($this->seekPathId))
		{
			$seek->pathId = $this->seekPathId;
		}

		$filter = new Translate\Filter(['path' => $path]);
		if (!empty($this->languages))
		{
			$filter->langId = $this->languages;
		}

		$indexer = new Index\PhraseIndexCollection();

		$processedItemCount = $indexer->collect($filter, $this->instanceTimer(), $seek);

		$this->processedItems += $processedItemCount;

		if ($this->processedItems >= $this->totalItems)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}
		else
		{
			$this->seekPathId = $seek->nextPathId;
		}

		return [
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		];
	}
}