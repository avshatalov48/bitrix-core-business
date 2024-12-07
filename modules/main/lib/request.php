<?php

namespace Bitrix\Main;

/**
 * Class Request contains current request
 * @package Bitrix\Main
 */
abstract class Request extends Type\ParameterDictionary
{
	/**
	 * @var Server
	 */
	protected $server;
	protected $requestedPage = null;
	protected $requestedPageDirectory = null;

	public function __construct(Server $server, array $request)
	{
		parent::__construct($request);

		$this->server = $server;
	}

	public function addFilter(Type\IRequestFilter $filter)
	{
		$filteredValues = $filter->filter($this->values);

		if ($filteredValues != null)
		{
			$this->setValuesNoDemand($filteredValues);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

	public function getPhpSelf()
	{
		return $this->server->getPhpSelf();
	}

	public function getScriptName()
	{
		return $this->server->getScriptName();
	}

	public function getRequestedPage()
	{
		if ($this->requestedPage === null)
		{
			$page = $this->getScriptName();
			if (!empty($page))
			{
				$page = IO\Path::normalize($page);

				if (!str_starts_with($page, "/") && !preg_match("#^[a-z]:[/\\\\]#i", $page))
				{
					$page = "/" . $page;
				}
			}
			$this->requestedPage = $page;
		}

		return $this->requestedPage;
	}

	/**
	 * Retuns the current directory with a trailing slash (/).
	 * @return string
	 */
	public function getRequestedPageDirectory()
	{
		if ($this->requestedPageDirectory === null)
		{
			$requestedPage = $this->getRequestedPage();
			$this->requestedPageDirectory = IO\Path::getDirectory($requestedPage) . '/';
		}
		return $this->requestedPageDirectory;
	}

	public function isAdminSection()
	{
		$requestedDir = $this->getRequestedPageDirectory();
		return (str_starts_with($requestedDir, "/bitrix/admin/")
			|| str_starts_with($requestedDir, "/bitrix/updates/")
			|| (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
			|| (defined("BX_PUBLIC_TOOLS") && BX_PUBLIC_TOOLS === true)
		);
	}

	/**
	 * Returns true if current request is AJAX
	 * @return bool
	 */
	public function isAjaxRequest()
	{
		return
			$this->server->get("HTTP_BX_AJAX") !== null ||
			$this->server->get("HTTP_X_REQUESTED_WITH") === "XMLHttpRequest";
	}
}
