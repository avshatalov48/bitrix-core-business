<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\UI;

use Bitrix\Main\Application;
use Bitrix\Main\UI;

/**
 * Class PageNavigation
 * @package Bitrix\Sender\UI
 */
class PageNavigation extends UI\PageNavigation
{
	protected $sessionKeyName = 'sender_page_navigation';

	/**
	 * Reset session variable.
	 *
	 * @return void
	 */
	public function resetSessionVar()
	{
		if(isset($_SESSION[$this->sessionKeyName]))
		{
			unset($_SESSION[$this->sessionKeyName]);
		}
	}

	protected function setSessionVar($page = 1, $allRecords=false)
	{
		if(!isset($_SESSION[$this->sessionKeyName]))
		{
			$_SESSION[$this->sessionKeyName] = array();
		}

		$_SESSION[$this->sessionKeyName][$this->id] = [
			'page' => $page,
			'allRecords' => $allRecords
		];
	}

	protected function getSessionVar()
	{
		if(!isset($_SESSION[$this->sessionKeyName]))
		{
			return 1;
		}

		if (!isset($_SESSION[$this->sessionKeyName][$this->id]))
		{
			return 1;
		}

		return $_SESSION[$this->sessionKeyName][$this->id];
	}

	/**
	 * Init from uri.
	 */
	public function initFromUri()
	{
		parent::initFromUri();

		$page = $this->currentPage;
		$request = Application::getInstance()->getContext()->getRequest();
		if ($request->get('apply_filter') === 'Y')
		{
			$page = 1;
		}
		if (!$page && $request->get('grid_action') === 'pagination')
		{
			$page = 1;
		}

		if ($page > 0)
		{
			$this->setSessionVar($page, $this->allRecords);
		}
		else
		{
			$page = $this->getSessionVar()['page'];
		}

		$page = $page > 0 ? $page : 1;
		$this->setCurrentPage($page);
		$this->allRecords = $this->getSessionVar()['allRecords'];
	}
}