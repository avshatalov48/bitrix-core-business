<?

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;

class Component
{

	const VERSION = 2;

	protected $path;
	protected $jsfile;
	protected $name;
	protected $namespace;
	protected $directory;

	/**
	 * Component constructor.
	 * @param null $path
	 * @throws \Exception
	 */
	public function __construct($path = null)
	{
		\Bitrix\MobileApp\Mobile::Init();
		if (strpos($path, Application::getDocumentRoot()) === 0)
		{
			$this->path = $path;
		}
		else
		{
			$this->path = Application::getDocumentRoot() . $path;
		}

		if (substr($this->path, -1) != "/") //compatibility fix
		{
			$this->path .= "/";
		}

		$this->directory = new Directory($this->path);
		$this->jsfile = new File($this->directory->getPath() . "/component.js");
		$this->name = basename($this->directory->getPath());

		if (!$this->directory->isExists() || !$this->jsfile->isExists())
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
		$componentData = Manager::getInstance()->availableComponents[$info["defaultFullname"]];
		if (Manager::getInstance()->availableComponents[$info["defaultFullname"]])
		{
			return new Component($componentData["path"]);
		}

		return null;
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
			$langPhrases = Localization\Loc::loadLanguageFile($this->path . "/component.php");
			$lang = Utils::jsonEncode($langPhrases, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
			$object = Utils::jsonEncode($this->getInfo());
			$componentList = Utils::jsonEncode(Manager::getInstance()->availableComponents);


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

			$inlineContent = <<<JS
\n\n//-------- component '$this->name' ---------- 
								
BX.message($lang);
(()=>
{
     this.result = $result;
     this.component = $object;
     this.env = $env;
     this.availableComponents = $componentList;
})();
								
JS;
			$content = $extensionContent . $inlineContent;
			$componentCode = $this->jsfile->getContents();

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

	public function getVersion()
	{
		$versionFile = new File($this->directory->getPath() . "/version.php");
		$componentPhpFile = new File($this->directory->getPath() . "/component.php");
		$version = 1;

		if ($versionFile->isExists())
		{
			$versionDesc = include($versionFile->getPath());
			$version = $versionDesc["version"];
			$version .= "." . self::VERSION;
		}

		$version .= "_" . $this->jsfile->getModificationTime();
		if ($componentPhpFile->isExists())
		{
			$version .= "_" . $componentPhpFile->getModificationTime();
		}


		return $version;
	}

	public function getPublicPath()
	{
		return "/mobileapp/jn/{$this->name}/?version=" . $this->getVersion();
	}

	public function getDependencies()
	{
		$file = new File($this->directory->getPath() . "/deps.php");
		$rootDeps = include($file->getPath());
		$deps = [];

		array_walk($rootDeps, function ($ext) use (&$deps) {
			$list = Extension::getResolvedDependencyList($ext);
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