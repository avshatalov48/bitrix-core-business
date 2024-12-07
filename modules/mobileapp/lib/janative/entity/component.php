<?

namespace Bitrix\MobileApp\Janative\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\MobileApp\Janative\Manager;
use Bitrix\MobileApp\Janative\Utils;
use Bitrix\MobileApp\Mobile;
use CExtranet;
use CSite;
use Exception;

class Component extends Base
{
	const VERSION = 2;
	protected static array $modificationDates = [];
	protected static array $dependencies = [];
	private $version = null;
	public $isBundleEnabled = false;

	/**
	 * Component constructor.
	 * @param null $path
	 * @throws Exception
	 */
	public function __construct($path = null, $namespace = "bitrix")
	{
		Mobile::Init();

		$path = Path::normalize($path);
		if (mb_strpos($path, Application::getDocumentRoot()) === 0)
		{
			$this->path = $path;
		}
		else
		{
			$this->path = Application::getDocumentRoot() . $path;
		}

		if (mb_substr($this->path, -1) != '/') //compatibility fix
		{
			$this->path .= '/';
		}

		$directory = new Directory($this->path);
		$this->isBundleEnabled = isset($this->getConfig()["packer"]) ?? false;
		$this->baseFileName = 'component';
		$path = $directory->getPath() . '/'.$this->baseFileName.'.js';
		$file = new File($path);
		$this->name = $directory->getName();
		$this->namespace = $namespace;

		if (!$directory->isExists() || !$file->isExists())
		{
			throw new Exception("Component '{$this->name}' doesn't exists ($path) ");
		}
	}

	public function getPath(): string
	{
		return str_replace(Application::getDocumentRoot(), '', $this->path);
	}

	/**
	 * @param $name
	 * @param string $namespace
	 * @return Component|null
	 * @throws Exception
	 */
	public static function createInstanceByName($name, string $namespace = 'bitrix'): ?Component
    {
		$info = Utils::extractEntityDescription($name, $namespace);
		return Manager::getComponentByName($info['defaultFullname']);
	}

	/**
	 * @param bool $resultOnly
	 * @param bool $loadExtensionsSeparately
	 * @throws ArgumentException
	 * @throws FileNotFoundException
	 * @throws LoaderException
	 */
	public function execute(bool $resultOnly = false)
	{
		header('Content-Type: text/javascript;charset=UTF-8');
		header('BX-Component-Version: ' . $this->getVersion());
		header('BX-Component: true');
		if ($resultOnly)
		{
			echo Utils::jsonEncode($this->getResult());;
		}
		else
		{
			echo $this->getContent();
		}
	}


	public function getResult(): ?array
	{
		$componentFile = new File($this->path . '/component.php');
		if ($componentFile->isExists())
		{
			return include($componentFile->getPath());
		}

		return [];
	}

	public function getContent(): string
	{
		$env = $this->getEnvContent();
		$lang = $this->getLangDefinitionExpression();
		$componentFilePath = "{$this->path}/{$this->baseFileName}.js";
		$extensionContent = "";
		$availableComponents = "";
		if ($this->isBundleEnabled)
		{
			$bundleConfig = new Config("{$this->path}/dist/deps.bundle.php");
			foreach ($bundleConfig->dynamicData as $ext)
			{
				$extension = Extension::getInstance($ext);
				$extensionContent .= $extension->getResultExpression();
			}
			$componentFilePath = "{$this->path}/dist/{$this->baseFileName}.bundle.js";
		}
		else
		{
			$extensionContent = $this->getExtensionsContent();
			$availableComponents = "this.availableComponents = ".Utils::jsonEncode( $this->getComponentListInfo()).";";
		}

		$eventManager = EventManager::getInstance();
		$events = $eventManager->findEventHandlers("mobileapp", "onBeforeComponentContentGet");

		$additionalContent = "";
		if (count($events) > 0)
		{
			foreach ($events as $event)
			{
				$jsCode = ExecuteModuleEventEx($event, [$this]);
				if (is_string($jsCode)) {
					$additionalContent .= $jsCode;
				}

			}
		}

		$content = "
			$env
			$additionalContent
			$lang
			$availableComponents
			$extensionContent
		";

		$file = new File($componentFilePath);
		if ($file->isExists())
		{
			$componentCode = $file->getContents();
			$content .= "\n" . $componentCode;
		}

		return $content;
	}

	public function getEnvContent(): string {
		global $USER;

		$result = Utils::jsonEncode($this->getResult());
		$object = Utils::jsonEncode($this->getInfo());

		$isExtranetModuleInstalled = Loader::includeModule('extranet');

		if ($isExtranetModuleInstalled)
		{
			$extranetSiteId = CExtranet::getExtranetSiteId();
			if (!$extranetSiteId)
			{
				$isExtranetModuleInstalled = false;
			}
		}
		$isExtranetUser = $isExtranetModuleInstalled && !CExtranet::IsIntranetUser();
		$siteId = (
		$isExtranetUser
			? $extranetSiteId
			: SITE_ID
		);


		$siteDir = SITE_DIR;
		if ($isExtranetUser)
		{
			$res = CSite::getById($siteId);
			if (
				($extranetSiteFields = $res->fetch())
				&& ($extranetSiteFields['ACTIVE'] != 'N')
			)
			{
				$siteDir = $extranetSiteFields['DIR'];
			}
		}

		$userId = $USER->GetId();
		$isAdmin = $USER->isAdmin();
		if (!$isAdmin && Loader::includeModule("bitrix24"))
		{
			$isAdmin = \CBitrix24::IsPortalAdmin($userId);
		}
		$env = Utils::jsonEncode([
			'siteId' => $siteId,
			'isAdmin' => $isAdmin,
			'languageId' => LANGUAGE_ID,
			'siteDir' => $siteDir,
			'userId' => $userId,
			'extranet' => $isExtranetUser
		]);
		$file = new File(Application::getDocumentRoot()."/bitrix/js/mobileapp/platform.js");
		$export = $file->getContents();
		$inlineContent = <<<JS
\n\n//-------- component '$this->name' ---------- 
$export
(()=>
{
     this.result = $result;
     this.component = $object;
     this.env = $env;
})();
								
JS;

		return $inlineContent;
	}

	public function getComponentListInfo(): array {
		$relativeComponents = $this->getComponentDependencies();
		$componentScope = Manager::getAvailableComponents();
		if ($relativeComponents !== null) {
			$relativeComponentsScope = [];
			foreach ($relativeComponents as $scope)
			{
				if (isset($componentScope[$scope])) {
					$relativeComponentsScope[$scope] = $componentScope[$scope];
				}
			}

			$componentScope = $relativeComponentsScope;
		}

		return array_map(function ($component) {
			return $component->getInfo();
		}, $componentScope);
	}

	public function getInfo(): array
    {
		return [
			'path' => $this->getPath(),
			'version' => $this->getVersion(),
			'publicUrl' => $this->getPublicPath(),
			'resultUrl' => $this->getPublicPath() . '&get_result=Y'
		];
	}

	protected function onBeforeModificationMarkerSave(array &$value)
	{
		$deps = $this->getDependencies();
		foreach ($deps as $ext)
		{
			$extension = Extension::getInstance($ext);
			$value[] = $extension->getModificationMarker();
		}
	}

	public function getVersion(): string
    {
		if (!$this->version)
		{
			$this->version = "1";
			if ( $this->isBundleEnabled )
			{
				$bundleVersion = new File("{$this->path}/dist/version.bundle.php");
				if ($bundleVersion->isExists())
				{
					$versionDesc = include($bundleVersion->getPath());
					$this->version = $versionDesc['version'];
				}
			}
			else
			{
				$versionFile = new File("{$this->path}/version.php");
				if ($versionFile->isExists())
				{
					$versionDesc = include($versionFile->getPath());
					$this->version = $versionDesc['version'];
					$this->version .= '.' . self::VERSION;
				}

				$this->version .= '_' . $this->getModificationMarker();
			}
		}

		return $this->version;
	}

	public function getPublicPath(): string
    {
		$name = ($this->namespace !== "bitrix" ? $this->namespace . ":" : "") . $this->name;
		$name = urlencode($name);
		return "/mobileapp/jn/$name/?version=" . $this->getVersion();
	}

	public function getLangMessages()
	{
		$langPhrases = parent::getLangMessages();
		$extensions = $this->getDependencies();
		foreach ($extensions as $extension)
		{
			try {
				$instance = Extension::getInstance($extension);
				$extensionPhrases = $instance->getLangMessages();
				$langPhrases = array_merge($langPhrases, $extensionPhrases);
			}
			catch (Exception $e)
			{
				//do nothing
			}
		}

		return $langPhrases;
	}

	public function getDependencies()
	{
		if (!$this->isBundleEnabled ) {
			return parent::getDependencies();
		}
		else
		{
			$bundleConfig = new Config("{$this->path}/dist/deps.bundle.php");
			return $bundleConfig->extensions;
		}
	}

	public function getComponentDependencies(): ?array
	{
		$componentDependencies = parent::getComponentDependencies();
		if (is_array($componentDependencies)) {
			$dependencies = $this->getDependencies();

			foreach ($dependencies as $dependency)
			{
				$list = (Extension::getInstance($dependency))->getComponentDependencies();
				if ($list !== null) {
					$componentDependencies = array_merge($componentDependencies, $list);
				}
			}

			return array_unique($componentDependencies);
		}

		return null;
	}

	/**
	 * @return array|null
	 */
	public function resolveDependencies(): ?array
    {
		$rootDeps = $this->getDependencyList();
		$deps = [];

		array_walk($rootDeps, function ($ext) use (&$deps) {
			$list = (Extension::getInstance($ext))->getDependencies();
			$deps = array_merge($deps, $list);
		});

		return array_unique($deps);
	}

	public function getExtensionsContent($excludeResult = false): string
    {
		$content = "\n//extension '{$this->name}'\n";
		$deps = $this->getDependencies();
		foreach ($deps as $ext)
		{
            try
            {
                $extension = Extension::getInstance($ext);
				$content .= "\n" . $extension->getContent($excludeResult);
            } catch (SystemException $e)
            {
                echo "Janative: error while initialization of '{$ext}' extension\n\n";
                throw $e;
            }
        }
		$loadedExtensions = "this.loadedExtensions = ".Utils::jsonEncode(array_values($deps), true).";\n";
		return $loadedExtensions.$content;
	}

	public function setVersion(string $version = "1")
	{
		$this->version = $version;
	}

	private function isHotreloadEnabled(): Bool {
		return (defined('JN_HOTRELOAD_ENABLED') && defined('JN_HOTRELOAD_HOST'));
	}
}