<?

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;

class Component extends Base
{
	const VERSION = 2;
	protected static $modificationDates = [];
	protected static $dependencies = [];
	private $version;

	/**
	 * Component constructor.
	 * @param null $path
	 * @throws \Exception
	 */
	public function __construct($path = null)
	{
		\Bitrix\MobileApp\Mobile::Init();
		if (mb_strpos($path, Application::getDocumentRoot()) === 0)
		{
			$this->path = $path;
		}
		else
		{
			$this->path = Application::getDocumentRoot() . $path;
		}

		if (mb_substr($this->path, -1) != "/") //compatibility fix
		{
			$this->path .= "/";
		}

		$directory = new Directory($this->path);
		$this->baseFileName = "component";
		$file = new File($directory->getPath() . "/component.js");
		$this->name = $directory->getName();

		if (!$directory->isExists() || !$file->isExists())
		{
			throw new \Exception("Component '{$this->name}' doesn't exists ($this->path) ");
		}
	}

	public function getPath()
	{
		return str_replace(Application::getDocumentRoot(), "", $this->path);
	}

	/**
	 * @param $name
	 * @param string $namespace
	 * @return Component|null
	 * @throws \Exception
	 */
	public static function createInstanceByName($name, $namespace = "bitrix")
	{
		$info = Utils::extractEntityDescription($name, $namespace);
		return Manager::getComponentByName($info["defaultFullname"]);
	}

	public function getResult()
	{
		$componentFile = new File($this->path . "/component.php");
		if ($componentFile->isExists())
		{
			return include($componentFile->getPath());
		}

		return "{}";
	}

	/**
	 * @param bool $resultOnly
	 * @param bool $loadExtensionsSeparately
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function execute($resultOnly = false, $loadExtensionsSeparately = false)
	{
		global $USER;

		$result = Utils::jsonEncode($this->getResult());
		if ($resultOnly)
		{
			header('Content-Type: application/json;charset=UTF-8');
			header("BX-Component-Version: " . $this->getVersion());
			header("BX-Component: true");
			echo $result;
		}
		else
		{
			$extensionContent = $this->getExtensionsContent($loadExtensionsSeparately);
			$lang = $this->getLangDefinitionExpression();
			$object = Utils::jsonEncode($this->getInfo());
			$componentList = Utils::jsonEncode(Manager::getAvailableComponents());
			$isExtranetModuleInstalled = \Bitrix\Main\Loader::includeModule("extranet");

			if ($isExtranetModuleInstalled)
			{
				$extranetSiteId = \CExtranet::getExtranetSiteId();
				if (!$extranetSiteId)
				{
					$isExtranetModuleInstalled = false;
				}
			}
			$isExtranetUser = $isExtranetModuleInstalled && !\CExtranet::IsIntranetUser();
			$siteId = (
			$isExtranetUser
				? $extranetSiteId
				: SITE_ID
			);


			$siteDir = SITE_DIR;
			if ($isExtranetUser)
			{
				$res = \CSite::getById($siteId);
				if (
					($extranetSiteFields = $res->fetch())
					&& ($extranetSiteFields["ACTIVE"] != "N")
				)
				{
					$siteDir = $extranetSiteFields["DIR"];
				}
			}


			$env = Utils::jsonEncode([
				'siteId' => $siteId,
				'languageId' => LANGUAGE_ID,
				'siteDir' => $siteDir,
				'userId' => $USER->GetId(),
				'extranet' => $isExtranetUser
			]);
			$export = <<<JS
		this.jnexport = (...exportData) => {
			exportData.forEach(exportItem=>{
				if(exportItem instanceof Array)
				{
					if(exportItem.length === 2)
					{
						this[exportItem[1]] = exportItem[0]
					}
				}
				else
				{
					this[exportItem.name] = exportItem
				}
			})
		};

JS;

			$inlineContent = <<<JS
\n\n//-------- component '$this->name' ---------- 
								
$lang
(()=>
{
     this.result = $result;
     this.component = $object;
     this.env = $env;
     this.availableComponents = $componentList;
})();
								
JS;

			$content = $export. $extensionContent . $inlineContent;
			$file = new File("{$this->path}/{$this->baseFileName}.js");
			$componentCode = $file->getContents();

			header('Content-Type: text/javascript;charset=UTF-8');
			header("BX-Component-Version: " . $this->getVersion());
			header("BX-Component: true");

			if ($loadExtensionsSeparately)
			{
				$content .= <<<JS
let loadComponent = ()=>{$componentCode}
JS;
				$content = "(()=>{{$content}})();";
			}
			else
			{
				$content .= "\n" . $componentCode;
			}

			echo $content;
		}


	}

	public function getInfo()
	{
		return [
			'path' => $this->getPath(),
			'version' => $this->getVersion(),
			'publicUrl' => $this->getPublicPath(),
			'resultUrl' => $this->getPublicPath() . "&get_result=Y"
		];
	}

	protected function onBeforeModificationDateSave(&$value)
	{
		$file = new File("{$this->path}/{$this->baseFileName}.js");
		$componentFile = new File("{$this->path}/{$this->baseFileName}.js");
		$dates = [$value, $file->getModificationTime()];
		if ($componentFile->isExists())
		{
			$dates[] = $componentFile->getModificationTime();
		}

		$deps = $this->getDependencies();
		foreach ($deps as $ext)
		{
			$extension = new Extension($ext);
			$dates[] = $extension->getModificationTime();
		}

		$value = max($dates);

	}

	public function getVersion()
	{
		if (!$this->version)
		{
			$versionFile = new File("{$this->path}/version.php");
			$this->version = 1;

			if ($versionFile->isExists())
			{
				$versionDesc = include($versionFile->getPath());
				$this->version = $versionDesc["version"];
				$this->version .= "." . self::VERSION;
			}

			$this->version .= "_" . $this->getModificationTime();
		}


		return $this->version;
	}

	public function getPublicPath()
	{
		return "/mobileapp/jn/{$this->name}/?version=" . $this->getVersion();
	}

	/**
	 * @return array|null
	 */
	public function resolveDependencies()
	{
		$rootDeps = $this->getDependencyList();
		$deps = [];

		array_walk($rootDeps, function ($ext) use (&$deps) {
			$list = (new Extension($ext))->getDependencies();
			$deps = array_merge($deps, $list);
		});

		return array_unique($deps);
	}

	private function getExtensionsContent($lazyLoad = false)
	{
		$content = "";
		$deps = $this->getDependencies();
		if ($lazyLoad)
		{
			$count = count($deps);
			$content .=
				<<<JS
	let extensionCount = {$count} 
	let extensionLoaded = 0;
	// noinspection JSUnusedLocalSymbols
	let onExtensionsLoaded = ()=>{
		extensionLoaded++;
		if(extensionLoaded >= extensionCount)
		{
			// noinspection JSUnresolvedFunction
			loadComponent();
		}
	}
JS;
		}

		foreach ($deps as $ext)
		{
			try
			{
				$extension = new Extension($ext);
				if (!$lazyLoad)
				{
					$content .= "\n" . $extension->getContent();
				}
				else
				{
					$content .= "\n" . $extension->getIncludeExpression();
				}
			} catch (\Bitrix\Main\ArgumentException $e)
			{
				echo "Janative: error while initialization of '{$extension->name}' extension\n\n";
				throw $e;
			}
		}

		return $content;
	}
}