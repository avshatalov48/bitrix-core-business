<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists("ui"))
{
	return;
}

class UI extends \CModule
{
	public $MODULE_ID = 'ui';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("UI_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("UI_INSTALL_DESCRIPTION");
	}

	function doInstall()
	{
		$this->installDB();
		$this->installFiles();
		$this->installEvents();
	}

	function doUninstall()
	{

	}

	function installFiles()
	{
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true
		);

		return true;
	}

	function installDB()
	{
		ModuleManager::registerModule($this->MODULE_ID);
		return true;
	}

	function installEvents()
	{
		return true;
	}

	function uninstallDB()
	{
		return true;
	}

	function uninstallEvents()
	{
		return true;
	}

	function uninstallFiles()
	{
		return true;
	}

}