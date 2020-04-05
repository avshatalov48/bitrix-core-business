<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * @see \Bitrix\Main\ORM\Objectify\Collection
 */
class FileDiffCollection
	extends Index\Internals\EO_FileDiff_Collection
{
	/**
	 * @var bool
	 */
	static $verbose = false;

	/** @var string */
	private static $documentRoot;

	/** @var string[] */
	private static $enabledLanguages;


	/**
	 * Sets up configuration.
	 *
	 * @return void
	 */
	private static function configure()
	{
		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$enabledLanguages = Translate\Config::getEnabledLanguages();
	}


	/**
	 * Counts items to process.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return int
	 */
	public function countItemsToProcess(Translate\Filter $filter = null)
	{
		return 0;
	}


	/**
	 * Collect index file.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param Translate\Controller\ITimeLimit $timer Time counter.
	 * @param Translate\Filter $seek Params to seek position.
	 *
	 * @return int
	 */
	public function collect(Translate\Filter $filter = null, Translate\Controller\ITimeLimit $timer = null, Translate\Filter $seek = null)
	{
		self::configure();

		return 0;
	}


	/**
	 * Drop index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function purge(Translate\Filter $filter = null)
	{
		Index\Internals\FileDiffTable::purge($filter);

		return $this;
	}
}

