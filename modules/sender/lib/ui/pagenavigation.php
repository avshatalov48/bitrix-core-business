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

	protected function setSessionVar($page)
	{
		if(!isset($_SESSION[$this->sessionKeyName]))
		{
			$_SESSION[$this->sessionKeyName] = array();
		}

		$_SESSION[$this->sessionKeyName][$this->id] = $page;
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
			$this->setSessionVar($page);
		}
		else
		{
			$page = $this->getSessionVar();
		}

		$page = $page > 0 ? $page : 1;
		$this->setCurrentPage($page);
	}
}