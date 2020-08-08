<?php
namespace Bitrix\Iblock\Url\AdminPage;

use Bitrix\Main;

class BuilderManager
{
	public const EVENT_ID = 'onGetUrlBuilders';

	private static $instance = null;

	protected $mode = null;

	/** @var BaseBuilder[] */
	protected $builders = null;

	/** @var array */
	protected $map = null;

	protected function __construct()
	{
		$this->builders = [];
		$this->map = [];
		$counter = 0;

		$item = new IblockBuilder();
		$id = $item->getId();

		$this->builders[$id] = $item;
		$this->map[] = [
			'ID' => $id,
			'WEIGHT' => (int)$item->getWeight(),
			'COUNTER' => $counter
		];
		$counter++;
		unset($item);

		$event = new Main\Event('iblock', self::EVENT_ID, []);
		$event->send();
		$resultList = $event->getResults();
		if (empty($resultList) || !is_array($resultList))
		{
			return;
		}
		foreach ($resultList as $eventResult)
		{
			if ($eventResult->getType() != Main\EventResult::SUCCESS)
			{
				continue;
			}
			$row = $eventResult->getParameters();
			if (empty($row) || !is_array($row))
			{
				continue;
			}
			foreach ($row as $className)
			{
				/** @var BaseBuilder $item */
				$item = new $className();
				if ($item instanceof BaseBuilder)
				{
					$id = $item->getId();
					if (!isset($this->builders[$id]))
					{
						$this->builders[$id] = $item;
						$this->map[] = [
							'ID' => $id,
							'WEIGHT' => (int)$item->getWeight(),
							'COUNTER' => $counter
						];
						$counter++;
					}
				}
				unset($item);
			}
		}
		unset($eventResult, $resultList);

		if (!empty($this->map))
		{
			Main\Type\Collection::sortByColumn(
				$this->map,
				['WEIGHT' => SORT_DESC, 'COUNTER' => SORT_ASC]
			);
		}
	}

	public static function getInstance(): BuilderManager
	{
		if (self::$instance === null)
		{
			self::$instance = new BuilderManager();
		}
		return self::$instance;
	}

	public function getBuilder(string $builder = BaseBuilder::TYPE_AUTODETECT): ?BaseBuilder
	{
		$result = null;
		if ($builder === BaseBuilder::TYPE_AUTODETECT)
		{
			if (defined('URL_BUILDER_TYPE') && is_string(URL_BUILDER_TYPE))
			{
				if (isset($this->builders[URL_BUILDER_TYPE]))
				{
					$result = $this->builders[URL_BUILDER_TYPE];
				}
			}
			if ($result === null)
			{
				foreach ($this->map as $row)
				{
					if ($this->builders[$row['ID']]->use())
					{
						$result = $this->builders[$row['ID']];
						break;
					}
				}
				unset($row);
			}
		}
		else
		{
			if (isset($this->builders[$builder]))
			{
				$result = $this->builders[$builder];
			}
		}
		return $result;
	}
}