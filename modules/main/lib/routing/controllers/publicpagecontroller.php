<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Routing\Controllers;

/**
 * @package    bitrix
 * @subpackage main
 */
class PublicPageController
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __invoke()
	{
	}

	/**
	 * @return mixed
	 */
	public function getPath()
	{
		return $this->path;
	}
}
