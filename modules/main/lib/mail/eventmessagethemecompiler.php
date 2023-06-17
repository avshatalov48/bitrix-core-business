<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main\Mail;

use Bitrix\Main\Mail\Internal as MailInternal;
use Bitrix\Main\Config as Config;
use Bitrix\Main\IO as IO;
use Bitrix\Main\ObjectNotFoundException as ObjectNotFoundException;

class EventMessageThemeCompiler
{
	/**
	 * @var EventMessageThemeCompiler
	 */
	protected static $instance = null;

	protected $siteTemplateId;
	protected $siteId;
	protected $languageId;

	protected $themePath = '';
	protected $themeProlog;
	protected $themeEpilog;
	protected $themeStylesString = '';
	protected $resultString = '';
	protected $body;
	protected $contentTypeHtml = false;

	protected $params = array();
	protected $arStyle = array();
	protected $replaceCallback = array();
	protected $currentResourceOrder = 100;

	/**
	 * Constructor.
	 *
	 * @param string|null $siteTemplateId
	 * @param string $body
	 * @param bool $isHtml
	 * @return EventMessageThemeCompiler
	 */
	public function __construct($siteTemplateId = null, $body, $isHtml = true)
	{
		$this->contentTypeHtml = $isHtml;
		$this->siteTemplateId = $siteTemplateId;
		$this->setTheme($siteTemplateId);
		$this->setBody($body);
	}

	/**
	 * Create instance.
	 *
	 * @param string|null $siteTemplateId
	 * @param string $body
	 * @param bool $isHtml
	 * @return EventMessageThemeCompiler
	 */
	public static function createInstance($siteTemplateId = null, $body, $isHtml = true)
	{
		static::$instance = new static($siteTemplateId, $body, $isHtml);

		return static::$instance;
	}

	/**
	 * Returns current instance of the EventMessageThemeCompiler.
	 *
	 * @return EventMessageThemeCompiler
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
			throw new ObjectNotFoundException('createInstance() should be called before getInstance()');

		return static::$instance;
	}

	/**
	 * Unset current instance of the EventMessageThemeCompiler.
	 *
	 * @return void
	 */
	public static function unsetInstance()
	{
		if (isset(static::$instance))
			static::$instance = null;
	}

	/**
	 * Set site template id.
	 *
	 * @param mixed $siteTemplateId
	 */
	public function setSiteTemplateId($siteTemplateId)
	{
		$this->siteTemplateId = $siteTemplateId;
	}

	/**
	 * Get site template id.
	 *
	 * @return mixed
	 */
	public function getSiteTemplateId()
	{
		return $this->siteTemplateId;
	}

	/**
	 * Set language id.
	 *
	 * @param mixed $languageId
	 */
	public function setLanguageId($languageId)
	{
		$this->languageId = $languageId;
	}

	/**
	 * Get language id.
	 * @return mixed
	 */
	public function getLanguageId()
	{
		return $this->languageId;
	}

	/**
	 * Set site id.
	 *
	 * @param mixed $siteId
	 * @return void
	 */
	public function setSiteId($siteId)
	{
		$this->siteId = $siteId;
	}

	/**
	 * Return site id.
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * Return result.
	 *
	 * @return string
	 */
	public function getResult()
	{
		return $this->resultString;
	}

	/**
	 * Set params that will be used for replacing placeholders.
	 *
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Set theme prolog.
	 *
	 * @param mixed $themeProlog
	 */
	public function setThemeProlog($themeProlog)
	{
		$this->themeProlog = $themeProlog;
	}

	/**
	 * Return theme prolog.
	 *
	 * @return mixed
	 */
	public function getThemeProlog()
	{
		return $this->themeProlog;
	}

	/**
	 * Set theme epilog.
	 *
	 * @param mixed $themeEpilog
	 */
	public function setThemeEpilog($themeEpilog)
	{
		$this->themeEpilog = $themeEpilog;
	}

	/**
	 * Return theme epilog.
	 *
	 * @return mixed
	 */
	public function getThemeEpilog()
	{
		return $this->themeEpilog;
	}

	/**
	 * Set style.
	 *
	 * @param array $arPaths
	 * @param bool $sort
	 * @return void
	 */
	public function setStyle($path, $sort = false)
	{
		$sort = ($sort === false ? $this->currentResourceOrder : $sort);
		$this->arStyle[$path] = $sort;
	}

	/**
	 * Set style list.
	 *
	 * @param array $arPaths
	 * @param bool $sort
	 * @return void
	 */
	public function setStyleArray(array $arPaths, $sort = false)
	{
		foreach($arPaths as $path)
			$this->setStyle($path, $sort);
	}

	/**
	 * Return style list that will be added by template.
	 *
	 * @return array
	 */
	public function getStyles()
	{
		return $this->arStyle;
	}

	/**
	 * Return styles as string that will be added by template.
	 *
	 * @return string
	 */
	public function getStylesString()
	{
		$returnStylesString = $this->themeStylesString;
		$arStyle = $this->arStyle;
		asort($arStyle);
		foreach($arStyle as $path=>$sort)
		{
			$pathFull = \Bitrix\Main\Application::getDocumentRoot().$path;
			if(IO\File::isFileExists($pathFull))
			{
				$content = "/* $path */ \r\n" . IO\File::getFileContents($pathFull);
				$returnStylesString .= $content . "\r\n";
			}
		}

		if($returnStylesString <> '')
		{
			$returnStylesString = '<style type="text/css">'."\r\n".$returnStylesString."\r\n".'</style>';
		}

		return $returnStylesString;
	}

	/**
	 * Show styles that will be added by template.
	 *
	 * @return string
	 */
	public function showStyles()
	{
		if($this->contentTypeHtml)
		{
			$identificator = '%BITRIX_MAIL_EVENT_TEMPLATE_THEME_CALLBACK_STYLE%';
			$this->addReplaceCallback($identificator, array($this, 'getStylesString'));
		}
		else
		{
			$identificator = '';
		}

		return $identificator;
	}

	protected function setTheme($site_template_id)
	{
		if($site_template_id <> '')
		{
			$result = \CSiteTemplate::GetByID($site_template_id);
			if($templateFields = $result->Fetch())
			{
				$this->themePath = $templateFields['PATH'];
				$template_path_header = \Bitrix\Main\Application::getDocumentRoot().$templateFields['PATH'].'/header.php';
				$template_path_footer = \Bitrix\Main\Application::getDocumentRoot().$templateFields['PATH'].'/footer.php';
				if($templateFields['PATH']!='' && IO\File::isFileExists($template_path_footer)  && IO\File::isFileExists($template_path_header))
				{
					$this->themeStylesString .= $templateFields['TEMPLATE_STYLES']."\r\n";
					$this->themeStylesString .= $templateFields['STYLES']."\r\n";

					$this->setThemeProlog(IO\File::getFileContents($template_path_header));
					$this->setThemeEpilog(IO\File::getFileContents($template_path_footer));
				}
			}
		}
	}

	protected function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Function includes language files from within the theme directory.
	 *
	 * <p>For example: $this->includeThemeLang("header.php") will include "lang/en/header.php" file. </p>
	 * <p>Note: theme must be inited by setTheme method.</p>
	 * @param string $relativePath
	 * @return void
	 *
	 */
	final public function includeThemeLang($relativePath = "")
	{
		if ($relativePath == "")
		{
			$relativePath = ".description.php";
		}

		$path = $_SERVER["DOCUMENT_ROOT"].$this->themePath."/".$relativePath;
		\Bitrix\Main\Localization\Loc::loadMessages($path);
	}

	/**
	 * Execute prolog, body and epilog.
	 *
	 * @param
	 */
	public function execute()
	{
		$resultThemeProlog = '';
		$resultThemeEpilog = '';

		if(!$this->themeProlog && $this->contentTypeHtml)
			$this->body = '<?=$this->showStyles()?>' . $this->body;

		$resultBody = $this->executePhp($this->body, 100);
		if($this->themeProlog)
		{
			$this->includeThemeLang('header.php');
			$resultThemeProlog = $this->executePhp($this->themeProlog, 50);
		}

		if($this->themeEpilog)
		{
			$this->includeThemeLang('footer.php');
			$resultThemeEpilog = $this->executePhp($this->themeEpilog, 150);
		}

		$this->resultString = $resultThemeProlog . $resultBody . $resultThemeEpilog;
		$this->executeReplaceCallback();
	}


	protected function executePhp($template, $resourceOrder = 100)
	{
		$this->currentResourceOrder = $resourceOrder;

		try
		{
			$arParams = $this->params;
			$result = eval('use \Bitrix\Main\Mail\EventMessageThemeCompiler; ob_start();?>' . $template . '<? return ob_get_clean();');
		}
		catch(StopException $e)
		{
			ob_clean();
			throw $e;
		}

		return $result;
	}

	protected function addReplaceCallback($identificator, $callback)
	{
		$this->replaceCallback[$identificator] = $callback;
	}

	protected function executeReplaceCallback()
	{
		$arReplaceIdentificators = array();
		$arReplaceStrings = array();
		foreach($this->replaceCallback as $identificator => $callback)
		{
			$result = call_user_func_array($callback, array());
			if($result === false)
				$result = '';

			$arReplaceIdentificators[] = $identificator;
			$arReplaceStrings[] = $result;
		}

		$this->resultString = str_replace($arReplaceIdentificators, $arReplaceStrings, $this->resultString);
	}

	/**
	 * Include mail component.
	 *
	 * @return mixed
	 */
	public static function includeComponent($componentName, $componentTemplate, $arParams = array(), $parentComponent = null, $arFunctionParams = array())
	{
		$componentRelativePath = \CComponentEngine::MakeComponentPath($componentName);
		if ($componentRelativePath == '')
			return false;

		if (is_object($parentComponent))
		{
			if (!($parentComponent instanceof \cbitrixcomponent))
				$parentComponent = null;
		}

		$result = null;
		$bComponentEnabled = (!isset($arFunctionParams["ACTIVE_COMPONENT"]) || $arFunctionParams["ACTIVE_COMPONENT"] <> "N");

		$component = new \CBitrixComponent();
		if($component->InitComponent($componentName))
		{
			$obAjax = null;
			if($bComponentEnabled)
			{
				$component->setSiteId(static::getInstance()->getSiteId());
				$component->setLanguageId(static::getInstance()->getLanguageId());
				$component->setSiteTemplateId(static::getInstance()->getSiteTemplateId());

				try
				{
					$result = $component->IncludeComponent($componentTemplate, $arParams, $parentComponent);
				}
				catch(StopException $e)
				{
					$component->AbortResultCache();
					throw $e;
				}

				$arThemeCss = array(); // TODO: use styles array from $component
				foreach($arThemeCss as $cssPath)
					static::getInstance()->setStyle($cssPath);
			}
		}

		return $result;
	}

	/**
	 * Stop execution of template. Throws an exception if instance is exists.
	 *
	 * @return void
	 * @throws \Bitrix\Main\Mail\StopException
	 */
	public static function stop()
	{
		if (static::$instance)
		{
			throw new StopException;
		}
	}
}
