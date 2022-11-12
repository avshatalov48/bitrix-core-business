<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\Localization;

/**
 * Context of current request.
 */
class Context
{
	/** @var Application */
	protected $application;

	/** @var Response */
	protected $response;

	/** @var Request */
	protected $request;

	/** @var Server */
	protected $server;

	/** @var Localization\EO_Language */
	protected $language;

	/** @var EO_Site */
	protected $site;

	/** @var Environment */
	protected $env;

	/** @var Context\Culture */
	protected $culture;

	/** @var array */
	protected $params;

	/**
	 * Creates new instance of context.
	 *
	 * @param Application $application
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;
	}

	/**
	 * Initializes context by request and response objects.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param Server $server
	 * @param array $params
	 */
	public function initialize(Request $request, Response $response, Server $server, array $params = [])
	{
		$this->request = $request;
		$this->response = $response;
		$this->server = $server;
		$this->params = $params;
	}

	public function getEnvironment()
	{
		if ($this->env === null)
		{
			$this->env = new Environment($this->params['env']);
		}
		return $this->env;
	}

	/**
	 * Returns response object of the context.
	 *
	 * @return Response | HttpResponse
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Sets response of the context.
	 *
	 * @param Response $response Response.
	 * @return $this
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
		return $this;
	}

	/**
	 * Returns request object of the context.
	 *
	 * @return Request | HttpRequest
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Returns server object of the context.
	 *
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Returns backreference to Application.
	 *
	 * @return Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * Returns culture of the context.
	 *
	 * @return Context\Culture | null
	 */
	public function getCulture()
	{
		return $this->culture;
	}

	/**
	 * Returns current language ID (en, ru).
	 *
	 * @return string | null
	 */
	public function getLanguage()
	{
		return $this->language ? $this->language->getLid() : null;
	}

	/**
	 * Returns current language object.
	 *
	 * @return Localization\EO_Language | null
	 */
	public function getLanguageObject(): ?Localization\EO_Language
	{
		return $this->language;
	}

	/**
	 * Returns current site
	 *
	 * @return string
	 */
	public function getSite()
	{
		return $this->site ? $this->site->getLid() : null;
	}

	/**
	 * Returns current language object.
	 *
	 * @return EO_Site | null
	 */
	public function getSiteObject(): ?EO_Site
	{
		return $this->site;
	}

	/**
	 * Sets culture of the context.
	 *
	 * @param \Bitrix\Main\Context\Culture $culture
	 * @return $this
	 */
	public function setCulture(Context\Culture $culture)
	{
		$this->culture = $culture;
		return $this;
	}

	/**
	 * Sets language of the context.
	 *
	 * @param string | Localization\EO_Language $language
	 * @return $this
	 */
	public function setLanguage($language)
	{
		if ($language instanceof Localization\EO_Language)
		{
			$this->language = $language;
		}
		else
		{
			$this->language = Localization\LanguageTable::wakeUpObject($language);
			$this->language->fill(ORM\Fields\FieldTypeMask::SCALAR | ORM\Fields\FieldTypeMask::EXPRESSION);
		}
		return $this;
	}

	/**
	 * Sets site of the context.
	 *
	 * @param string | EO_Site $site
	 * @return $this
	 */
	public function setSite($site)
	{
		if ($site instanceof EO_Site)
		{
			$this->site = $site;
		}
		else
		{
			$this->site = SiteTable::wakeUpObject($site);
			$this->site->fill(ORM\Fields\FieldTypeMask::SCALAR | ORM\Fields\FieldTypeMask::EXPRESSION);
		}
		return $this;
	}

	/**
	 * Static method returns current instance of context.
	 *
	 * @static
	 * @return Context
	 */
	public static function getCurrent()
	{
		if (Application::hasInstance())
		{
			$application = Application::getInstance();
			return $application->getContext();
		}

		return null;
	}
}
