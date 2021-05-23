<?

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class UIToolbarComponent extends CBitrixComponent
{
	private $toolbarId;

	/**
	 * Execute component.
	 *
	 * @return void
	 */
	public function executeComponent()
	{
		Loader::includeModule('ui');
		Extension::load(["ui.buttons", "ui.buttons.icons"]);

		$this->toolbarId = $this->arParams["TOOLBAR_ID"] ?? Toolbar::DEFAULT_ID;

		if($this->toolbarId === Toolbar::DEFAULT_ID)
		{
			$GLOBALS["APPLICATION"]->addBufferContent([$this, "includeTemplate"]);

			$this->initComponentTemplate();
			$GLOBALS["APPLICATION"]->setAdditionalCSS($this->getTemplate()->getFolder()."/style.css");
			$GLOBALS["APPLICATION"]->addHeadScript($this->getTemplate()->getFolder()."/script.js");
		}
		else
		{
			$this->arResult["TOOLBAR_ID"] = $this->toolbarId;
			$this->arResult["CONTAINER_ID"] = "toolbar_" . $this->randString();
			$this->includeComponentTemplate("common");
		}
	}

	public function includeTemplate()
	{
		//it's a dirty hack to prevent showing a white screen when some php error happens.
		if ($this->shouldPreventOutputBuffering())
		{
			return "";
		}

		ob_start();

		$pageTitle = $GLOBALS["APPLICATION"]->getViewContent("pagetitle");
		$insidePageTitle = $GLOBALS["APPLICATION"]->getViewContent("inside_pagetitle");
		$inPageTitle = $GLOBALS["APPLICATION"]->getViewContent("in_pagetitle");

		$isBitrix24Cloud = ModuleManager::isModuleInstalled("bitrix24");
		/** @var \CIntranetToolbar $oldToolbar */
		$oldToolbar = null;
		if (isset($GLOBALS["INTRANET_TOOLBAR"]))
		{
			$oldToolbar = $GLOBALS["INTRANET_TOOLBAR"];
		}
		$oldToolbarButtons = (
			!$isBitrix24Cloud
			&& (
				$oldToolbar instanceof \CIntranetToolbar
				&& $oldToolbar->isEnabled()
				&& count($oldToolbar->getButtons()) > 0
			));

		if ($pageTitle <> '' || $insidePageTitle <> '' || $inPageTitle <> '' || $oldToolbarButtons)
		{
			$this->includeComponentTemplate("old");
		}
		else
		{
			$this->includeComponentTemplate();
		}

		return ob_get_clean();
	}

	private function shouldPreventOutputBuffering()
	{
		if (defined("BX_BUFFER_SHUTDOWN"))
		{
			return true;
		}

		$trace = \Bitrix\Main\Diag\Helper::getBackTrace(0, DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $traceLine)
		{
			if (
				isset($traceLine['function']) &&
				in_array(
					$traceLine['function'],
					['ob_end_flush', 'ob_end_clean', 'LocalRedirect', 'ForkActions', 'fastcgi_finish_request']
				)
			)
			{
				return true;
			}
		}

		return false;
	}
}