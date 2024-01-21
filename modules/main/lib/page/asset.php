<?php

namespace Bitrix\Main\Page;

use Bitrix\Main;
use Bitrix\Main\IO;
use Bitrix\Main\Config\Option;

class Asset
{
	private static $instance;

	/** @var array Contains target list */
	private $targetList;

	/** @var array pointer to current target */
	private $target;

	/** @var array of css files */
	private $css = [];

	/** @var array of js files */
	private $js = [];

	/** @var array of inline string */
	private $strings = [
		AssetLocation::BEFORE_CSS => [],
		AssetLocation::AFTER_CSS => [],
		AssetLocation::AFTER_JS_KERNEL => [],
		AssetLocation::AFTER_JS => [],
	];

	/** @var array Information about kernel modules */
	private $moduleInfo = ['CSS' => [], 'JS' => []];
	private $kernelAsset = ['CSS' => [], 'JS' => []];
	private $assetList = ['CSS' => [], 'SOURCE_CSS' => [], 'JS' => [], 'SOURCE_JS' => []];
	private $fileList = ['CSS' => [], 'JS' => []];
	private $mode = AssetMode::STANDARD;

	private $ajax;
	private $xhtmlStyle = '/';

	private $optimizeCss = true;
	private $optimizeJs = true;

	private $headString = false;
	private $headScript = false;
	private $bodyScript = false;
	private $moveJsToBody = null;

	private $templateExists = false;
	private $siteTemplateID = '';
	private $templatePath = '';
	private $documentRoot = '';
	private $dbType = 'MYSQL';
	private $assetCSSCnt = 0;
	private $assetJSCnt = 0;

	const SOURCE_MAP_TAG = "\n//# sourceMappingURL=";
	const HEADER_START_TAG = "; /* Start:\"";
	const HEADER_END_TAG = "\"*/";
	const version = 1;

	private function __construct()
	{
		//use self::getInstance()
		$this->targetList['KERNEL'] = [
			'NAME' => 'KERNEL',
			'START' => true,
			'CSS_RES' => [],
			'JS_RES' => [],
			'CSS_LIST' => [],
			'JS_LIST' => [],
			'STRING_LIST' => [],
			'UNIQUE' => true,
			'PREFIX' => 'kernel',
			'BODY' => false,
			'MODE' => AssetMode::ALL
		];

		$this->targetList['BODY'] = $this->targetList['TEMPLATE'] = $this->targetList['PAGE'] = $this->targetList['KERNEL'];
		$this->targetList['PAGE']['NAME'] = 'PAGE';
		$this->targetList['PAGE']['UNIQUE'] = false;
		$this->targetList['PAGE']['PREFIX'] = 'page';
		$this->targetList['TEMPLATE']['NAME'] = 'TEMPLATE';
		$this->targetList['TEMPLATE']['UNIQUE'] = false;
		$this->targetList['TEMPLATE']['PREFIX'] = 'template';
		$this->targetList['BODY']['NAME'] = 'BODY';
		$this->targetList['BODY']['UNIQUE'] = false;
		$this->targetList['BODY']['PREFIX'] = 'body';

		/** fix current order of kernel modules */
		$this->targetList['KERNEL']['CSS_LIST']['KERNEL_main'] = [];
		$this->targetList['KERNEL']['JS_LIST']['KERNEL_main'] = [];

		$this->target = &$this->targetList['TEMPLATE'];
		$this->documentRoot = Main\Loader::getDocumentRoot();
	}

	/**
	 * Can`t clone this object
	 * @return void
	 */
	private function __clone()
	{
		//you can't clone it
	}

	/**
	 * Singleton instance.
	 *
	 * @return Asset
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Asset();
		}

		return self::$instance;
	}

	/**
	 * Set mode for current target.
	 * @param int $mode Set current composite mode.
	 * @return void
	 */
	public function setMode($mode = AssetMode::STANDARD)
	{
		$this->mode = $mode;
	}

	/**
	 * Returns gzip enabled or not.
	 * @return bool|null
	 */
	public static function gzipEnabled()
	{
		static $gzip = null;
		if ($gzip === null)
		{
			$gzip = (
				Option::get('main','compres_css_js_files', 'N') == 'Y'
				&& extension_loaded('zlib')
				&& function_exists('gzopen')
			);
		}
		return $gzip;
	}

	/**
	 * Start optimizing css
	 * @return void
	 */
	public function enableOptimizeCss()
	{
		$this->optimizeCss = true;
	}

	/**
	 * Stop optimizing css
	 * @return void
	 */
	public function disableOptimizeCss()
	{
		$this->optimizeCss = false;
	}

	/**
	 * Start optimizing js
	 * @return void
	 */
	public function enableOptimizeJs()
	{
		$this->optimizeJs = true;
	}

	/**
	 * Stop optimizing js
	 * @return void
	 */
	public function disableOptimizeJs()
	{
		$this->optimizeJs = false;
	}

	/**
	 * @param boolean $value Use xhtml html style.
	 * @return void
	 */
	public function setXhtml($value)
	{
		$this->xhtmlStyle = ($value === true ? '/':'');
	}

	/**
	 * @param integer $value Count of css files showed inline fore IE.
	 * @deprecated
	 * @return void
	 */
	public function setMaxCss($value)
	{

	}

	/**
	 * Set ShowHeadString in page or not.
	 * @param boolean $value Set ShowHeadSting is set on page.
	 * @return void
	 */
	public function setShowHeadString($value = true)
	{
		$this->headString = $value;
	}

	/**
	 * Return true if ShowHeadString exist in page.
	 * @return boolean
	 */
	public function getShowHeadString()
	{
		return $this->headString;
	}

	/**
	 *  Set ShowHeadScript in page or not.
	 * @param boolean $value Set ShowHeadScript is set on page.
	 * @return void
	 */
	public function setShowHeadScript($value = true)
	{
		$this->headScript = $value;
	}

	/**
	 * Return true if ShowHeadBodyScript exist in page.
	 * @param boolean $value Set ShowHeadBodyScript is set on page.
	 * @return void
	 */
	public function setShowBodyScript($value = true)
	{
		$this->bodyScript = $value;
	}

	/**
	 * Set Ajax mode and restart instance
	 * @return Asset
	 */
	public function setAjax()
	{
		$newInstance = self::$instance = new Asset();
		$newInstance->ajax = true;
		return $newInstance;
	}

	/**
	 * @return string - Return current set name
	 */
	public function getTargetName()
	{
		return $this->target['NAME'];
	}

	/**
	 * @return mixed Return current set
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Temporary fix for update system. Need to delete later.
	 * @param string $id Target ID.
	 * @param integer $mode Composite Mode.
	 * @return boolean
	 */
	public function startSet($id = '', $mode = AssetMode::ALL)
	{
		return $this->startTarget($id, $mode);
	}

	/**
	 * Start new target for asset.
	 * @param string $id Target ID.
	 * @param integer $mode Composite mode.
	 * @return boolean
	 */
	public function startTarget($id = '', $mode = AssetMode::ALL)
	{
		$id = strtoupper(trim($id));
		if ($id == '')
		{
			return false;
		}

		if ($id == 'TEMPLATE')
		{
			$this->templateExists = true;
		}

		if (
			($this->target['NAME'] == 'TEMPLATE' || $this->target['NAME'] == 'PAGE')
			&& ($id == 'TEMPLATE' || $id == 'PAGE')
		)
		{
			$this->target['START'] = false;
			$this->targetList[$id]['START'] = true;
			$this->target = &$this->targetList[$id];
		}
		elseif ($id != 'TEMPLATE' && $id != 'PAGE')
		{
			if (isset($this->targetList[$id]))
			{
				return false;
			}

			$this->stopTarget();
			$this->targetList[$id] = [
				'NAME' => $id,
				'START' => true,
				'JS_RES' => [],
				'CSS_RES' => [],
				'JS_LIST' => [],
				'CSS_LIST' => [],
				'STRING_LIST' => [],
				'BODY' => false,
				'UNIQUE' => false,
				'MODE' => $mode
			];
			$this->target = &$this->targetList[$id];
		}
		return true;
	}

	/**
	 * Stop current target.
	 * @param string $id Target ID.
	 * @return bool
	 */
	public function stopTarget($id = '')
	{
		$id = strtoupper(trim($id));
		if ($id == 'TEMPLATE')
		{
			if($this->target['NAME'] == 'TEMPLATE')
			{
				$this->target['START'] = false;
				$this->target = &$this->targetList['PAGE'];
			}
			else
			{
				$this->targetList['TEMPLATE']['START'] = false;
			}
		}
		else
		{
			if ($this->target['NAME'] == 'TEMPLATE')
			{
				return false;
			}
			elseif ($this->targetList['TEMPLATE']['START'])
			{
				$this->target['START'] = false;
				$this->target = &$this->targetList['TEMPLATE'];
			}
			else
			{
				$this->target['START'] = false;
				$this->target = &$this->targetList['PAGE'];
			}
		}

		return true;
	}

	/**
	 * Return information about target assets.
	 * @param string $id Asset ID.
	 * @param mixed $mode Composite mode.
	 * @return array
	 */
	public function getAssetInfo($id, $mode)
	{
		$id = strtoupper(trim($id));
		$emptyData = ['JS' => [], 'BUNDLE_JS' => [], 'CSS' => [], 'BUNDLE_CSS' => [], 'STRINGS' => []];

		if (!isset($this->targetList[$id]))
		{
			return $emptyData;
		}

		static $cacheInfo = [
			AssetMode::STANDARD => null,
			AssetMode::COMPOSITE => null,
			AssetMode::ALL => null,
			AssetMode::SPECIAL => null
		];

		if ($cacheInfo[$mode] === null)
		{
			$cacheInfo[$mode] = $emptyData;

			foreach ($this->strings as $location)
			{
				foreach ($location as $item)
				{
					if ($mode == $item['MODE'])
					{
						$cacheInfo[$mode]['STRINGS'][$item['TARGET'][0]][] = $item['CONTENT'];
					}
				}
			}

			foreach (['JS', 'CSS'] as $type)
			{
				foreach ($this->getTargetList($type) as $set)
				{
					$cache = &$cacheInfo[$mode][$type][$set['NAME']];
					$cacheFull = &$cacheInfo[$mode]['BUNDLE_'.$type][$set['NAME']];

					if (!is_array($cache))
					{
						$cache = [];
					}

					if (!is_array($cacheFull))
					{
						$cacheFull = [];
					}

					$fileList = $this->fileList[$type][$set['NAME']] ?? [];
					$targetList = $this->targetList['KERNEL'][$type.'_LIST'][$set['NAME']] ?? [];

					$items = [];
					if ($mode === $set['MODE'] && isset($fileList['FILES']))
					{
						$items = $fileList['FILES'];
					}
					elseif (isset($fileList['UP_NEW_FILES']))
					{
						$items = $fileList['UP_NEW_FILES'];
					}

					if (empty($items))
					{
						continue;
					}

					foreach ($items as $item)
					{
						$cache[] = $item;

						if (isset($fileList['FULL_FILES'][$item]))
						{
							$cacheFull = array_merge($cacheFull, $fileList['FULL_FILES'][$item]);
						}

						if ($set['PARENT_NAME'] == 'KERNEL')
						{
							foreach ($targetList['WHERE_USED'] as $target => $tmp)
							{
								$cacheInfo[$mode][$type][$target][] = $item;

								if (isset($fileList['FULL_FILES'][$item]))
								{
									if (!isset($cacheInfo[$mode]['BUNDLE_'.$type][$target]))
									{
										$cacheInfo[$mode]['BUNDLE_'.$type][$target] = [];
									}

									$cacheInfo[$mode]['BUNDLE_'.$type][$target] = array_merge(
										$cacheInfo[$mode]['BUNDLE_'.$type][$target],
										$fileList['FULL_FILES'][$item]
									);
								}
							}
						}
					}
				}
			}
		}

		return [
			'JS' => $cacheInfo[$mode]['JS'][$id] ?? [],
			'BUNDLE_JS' => $cacheInfo[$mode]['BUNDLE_JS'][$id] ?? [],
			'CSS' => $cacheInfo[$mode]['CSS'][$id] ?? [],
			'BUNDLE_CSS' => $cacheInfo[$mode]['BUNDLE_CSS'][$id] ?? [],
			'STRINGS' => $cacheInfo[$mode]['STRINGS'][$id] ?? []
		];
	}

	/**
	 * Set composite mode for set.
	 * @param string $id Target ID.
	 * @return boolean
	 */
	public function compositeTarget($id = '')
	{
		$id = strtoupper(trim($id));
		if ($id == '' || !isset($this->targetList[$id]))
		{
			return false;
		}
		else
		{
			$this->targetList[$id]['MODE'] = AssetMode::COMPOSITE;
		}
		return true;
	}

	/**
	 * Return list of all targets on the page.
	 * @param string $type Target type CSS or JS.
	 * @return array Return set list with subsets.
	 */
	public function getTargetList($type = 'CSS')
	{
		static $res = ['CSS_LIST' => null, 'JS_LIST' => null];
		$key = ($type == 'CSS' ? 'CSS_LIST' : 'JS_LIST');

		if ($res[$key] === null)
		{
			foreach ($this->targetList as $targetName => $targetInfo)
			{
				$res[$key][] = [
					'NAME' => $targetName,
					'PARENT_NAME' => $targetName,
					'UNIQUE' => $targetInfo['UNIQUE'],
					'PREFIX' => ($targetInfo['PREFIX'] ?? ''),
					'MODE' => $targetInfo['MODE'],
					'MODULE_NAME' => ($targetInfo['MODULE_NAME'] ?? ''),
				];

				if (!empty($targetInfo[$key]))
				{
					foreach ($targetInfo[$key] as $subSetName => $val)
					{
						$res[$key][] = [
							'NAME' => $subSetName,
							'PARENT_NAME' => $targetName,
							'UNIQUE' => ($val['UNIQUE'] ?? ''),
							'PREFIX' => ($val['PREFIX'] ?? ''),
							'MODE' => ($val['MODE'] ?? 0),
							'MODULE_NAME' => ($val['MODULE_NAME'] ?? ''),
						];
					}
				}
			}
		}
		return $res[$key];
	}

	/**
	 * Add string asset.
	 * @param string $str Added string.
	 * @param bool $unique Check string for unique.
	 * @param string $location Where string wheel be showed.
	 * @param null $mode Composite mode.
	 * @return boolean
	 */
	function addString($str, $unique = false, $location = AssetLocation::AFTER_JS_KERNEL, $mode = null)
	{
		if ($str == '')
		{
			return false;
		}

		if ($unique)
		{
			$chkSum = md5($str);
			$this->strings[$location][$chkSum]['CONTENT'] = $str;
			$this->strings[$location][$chkSum]['TARGET'][] = $this->getTargetName();
			$this->strings[$location][$chkSum]['MODE'] = $mode;
		}
		else
		{
			$this->strings[$location][] = ['CONTENT' => $str, 'MODE' => $mode, 'TARGET' => [$this->getTargetName()]];
		}
		return true;
	}

	/**
	 * Return strings assets.
	 * @param string $location Location.
	 * @return string
	 */
	public function getStrings($location = AssetLocation::AFTER_JS_KERNEL)
	{
		static $firstExec = true;
		if ($firstExec)
		{
			$this->prepareString();
			$firstExec = false;
		}

		$res = '';
		if ($location == AssetLocation::AFTER_CSS && \CJSCore::IsCoreLoaded())
		{
			$res = "<script>if(!window.BX)window.BX={};if(!window.BX.message)window.BX.message=function(mess){if(typeof mess==='object'){for(let i in mess) {BX.message[i]=mess[i];} return true;}};</script>\n";
		}

		if (isset($this->strings[$location]))
		{
			foreach ($this->strings[$location] as $item)
			{
				if ($this->mode & $item['MODE'])
				{
					$res .= $item['CONTENT']."\n";
				}
			}
		}

		return ($res == '') ? '' : $res."\n";
	}

	/**
	 * Add some css to asset.
	 * @param string $path Path to css file.
	 * @param boolean $additional Is additional file.
	 * @return boolean
	 */
	public function addCss($path, $additional = false)
	{
		if ($path == '')
		{
			return false;
		}

		$css = $this->getAssetPath($path);
		$this->css[$css]['TARGET'][] = $this->getTargetName();
		$this->css[$css]['ADDITIONAL'] = (isset($this->css[$css]['ADDITIONAL']) && $this->css[$css]['ADDITIONAL'] ? true : $additional);
		return true;
	}

	/**
	 * Add some js to asset.
	 * @param string $path Path to js file.
	 * @param boolean $additional Is additional file.
	 * @return boolean
	 */
	public function addJs($path, $additional = false)
	{
		if ($path == '')
		{
			return false;
		}

		$js = $this->getAssetPath($path);
		$this->js[$js]['TARGET'][] = $this->getTargetName();
		$this->js[$js]['ADDITIONAL'] = (isset($this->js[$js]['ADDITIONAL']) && $this->js[$js]['ADDITIONAL'] ? true : $additional);
		return true;
	}

	/**
	 * Replace path to includes in css.
	 * @param string $content Content for replacing path.
	 * @param string $path Path to correct.
	 * @return mixed
	 */
	public static function fixCssIncludes($content, $path)
	{
		$path = IO\Path::getDirectory($path);
		$content = preg_replace_callback(
			'#([;\s:]*(?:url|@import)\s*\(\s*)(\'|"|)(.+?)(\2)\s*\)#si',
			function ($matches) use ($path)
			{
				return $matches[1].Asset::replaceUrlCSS($matches[3], $matches[2], addslashes($path)).")";
			},
			$content
		);

		$content = preg_replace_callback(
			'#(\s*@import\s*)([\'"])([^\'"]+)(\2)#si',
			function ($matches) use ($path)
			{
				return $matches[1].Asset::replaceUrlCSS($matches[3], $matches[2], addslashes($path));
			},
			$content
		);

		return $content;
	}

	/**
	 * Group some js modules.
	 * @param string $from Module name for packing.
	 * @param string $to Module name for pack.
	 * @return void
	 */
	public function groupJs($from = '', $to = '')
	{
		if (empty($from) || empty($to))
		{
			return;
		}

		$to = $this->movedJsTo($to);
		if (array_key_exists($from, $this->moduleInfo['JS']))
		{
			$this->moduleInfo['JS'][$from]['MODULE_ID'] = $to;
		}
		else
		{
			$this->moduleInfo['JS'][$from] = ['MODULE_ID' => $to, 'FILES_INFO' => false, 'BODY' => false];
		}

		foreach ($this->moduleInfo['JS'] as $moduleID => $moduleInfo)
		{
			if ($moduleInfo['MODULE_ID'] == $from)
			{
				$this->moduleInfo['JS'][$moduleID]["MODULE_ID"] = $to;
			}
		}
	}

	/**
	 * Group some css modules.
	 * @param string $from Module name for packing.
	 * @param string $to Module name for pack.
	 * @return void
	 */
	public function groupCss($from = '', $to = '')
	{
		if (empty($from) || empty($to))
		{
			return;
		}

		$to = $this->movedCssTo($to);
		if (array_key_exists($from, $this->moduleInfo['CSS']))
		{
			$this->moduleInfo['CSS'][$from]['MODULE_ID'] = $to;
		}
		else
		{
			$this->moduleInfo['CSS'][$from] = ['MODULE_ID' => $to, 'FILES_INFO' => false];
		}

		foreach ($this->moduleInfo['CSS'] as $moduleID => $moduleInfo)
		{
			if($moduleInfo['MODULE_ID'] == $from)
			{
				$this->moduleInfo['CSS'][$moduleID]["MODULE_ID"] = $to;
			}
		}
	}

	/**
	 * @param string $to Module name.
	 * @return string Return module name.
	 */
	private function movedJsTo($to)
	{
		if (isset($this->moduleInfo['JS'][$to]['MODULE_ID']) && $this->moduleInfo['JS'][$to]['MODULE_ID'] != $to)
		{
			$to = $this->movedJsTo($this->moduleInfo['JS'][$to]['MODULE_ID']);
		}

		return $to;
	}

	/**
	 * @param string $to Module name.
	 * @return string Return module name
	 */
	private function movedCssTo($to)
	{
		if (isset($this->moduleInfo['CSS'][$to]['MODULE_ID']) && $this->moduleInfo['CSS'][$to]['MODULE_ID'] != $to)
		{
			$to = $this->movedCssTo($this->moduleInfo['JS'][$to]['MODULE_ID']);
		}

		return $to;
	}

	/**
	 * Move js kernel module to BODY.
	 * @param string $module Module name.
	 * @return void
	 */
	public function moveJs($module = '')
	{
		if (empty($module) || $module === "main")
		{
			return;
		}

		if (array_key_exists($module, $this->moduleInfo['JS']))
		{
			$this->moduleInfo['JS'][$module]['BODY'] = true;
		}
		else
		{
			$this->moduleInfo['JS'][$module] = ['MODULE_ID' => $module, 'FILES_INFO' => false, 'BODY' => true];
		}
	}

	/**
	 * Enables or disables the moving of all scripts to the body.
	 * @param bool $flag True or False.
	 * @return void
	 */
	public function setJsToBody($flag)
	{
		$this->moveJsToBody = (bool)$flag;
	}

	/**
	 * @return bool|null
	 */
	protected function getJsToBody()
	{
		if ($this->moveJsToBody === null)
		{
			$this->moveJsToBody = Option::get("main", "move_js_to_body") === "Y" && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true);
		}
		return $this->moveJsToBody;
	}

	/**
	 * Moves all scripts in front of </body>.
	 * @param string &$content Page content.
	 * @internal
	 * @return void
	 */
	public function moveJsToBody(&$content)
	{
		if (!$this->getJsToBody())
		{
			return;
		}

		$js = "";
		$offset = 0;
		$newContent = "";
		$areas = $this->getScriptAreas($content);
		foreach ($areas as $area)
		{
			if (str_contains($area->attrs, "data-skip-moving") || !self::isValidScriptType($area->attrs))
			{
				continue;
			}

			$js .= substr($content, $area->openTagStart, $area->closingTagEnd - $area->openTagStart);
			$newContent .= substr($content, $offset, $area->openTagStart - $offset);
			$offset = $area->closingTagEnd;
		}

		if ($js === "")
		{
			return;
		}

		$newContent .= substr($content, $offset);
		$bodyEnd = strripos($newContent, "</body>");
		if ($bodyEnd === false)
		{
			$content = $newContent.$js;
		}
		else
		{
			$content = substr_replace($newContent, $js, $bodyEnd, 0);
		}
	}

	/**
	 * Returns positions of <script>...</script> elements.
	 * @param string $content Page content.
	 * @return array
	 */
	private function getScriptAreas($content)
	{
		$openTag = "<script";
		$closingTag = "</script";
		$ending = ">";

		$offset = 0;
		$areas = [];
		$content = strtolower($content);
		while (($openTagStart = strpos($content, $openTag, $offset)) !== false)
		{
			$endingPos = strpos($content, $ending, $openTagStart);
			if ($endingPos === false)
			{
				break;
			}

			$attrsStart = $openTagStart + strlen($openTag);
			$attrs = substr($content, $attrsStart, $endingPos - $attrsStart);
			$openTagEnd = $endingPos + strlen($ending);

			$realClosingTag = $closingTag.$ending;
			$closingTagStart = strpos($content, $realClosingTag, $openTagEnd);
			if ($closingTagStart === false)
			{
				$offset = $openTagEnd;
				continue;
			}

			$closingTagEnd = $closingTagStart + strlen($realClosingTag);
			while (isset($content[$closingTagEnd]) && $content[$closingTagEnd] === "\n")
			{
				$closingTagEnd++;
			}

			$area = new \stdClass();
			$area->attrs = $attrs;
			$area->openTagStart = $openTagStart;
			$area->openTagEnd = $openTagEnd;
			$area->closingTagStart = $closingTagStart;
			$area->closingTagEnd = $closingTagEnd;
			$areas[] = $area;

			$offset = $closingTagEnd;
		}

		return $areas;
	}

	/**
	 * @return bool
	 */
	public function canMoveJsToBody()
	{
		return
			$this->getJsToBody() &&
			!Main\Application::getInstance()->getContext()->getRequest()->isAjaxRequest() &&
			!defined("BX_BUFFER_SHUTDOWN");
	}

	/**
	 *
	 * Returns true if <script> has valid mime type.
	 * @param string $attrs Script attributes.
	 * @return bool
	 */
	private static function isValidScriptType($attrs)
	{
		if ($attrs === "" || !preg_match("/type\\s*=\\s*(['\"]?)(.*?)\\1/i", $attrs, $match))
		{
			return true;
		}

		$type = mb_strtolower($match[2]);
		return $type === "" || $type === "text/javascript" || $type === "application/javascript";
	}


	/**
	 * Replace path to includes in line.
	 * @param string $url Url of css files.
	 * @param string $quote Quote.
	 * @param string $path Path to css.
	 * @return string replaced.
	 */
	public static function replaceUrlCss($url, $quote, $path)
	{
		if (
			str_contains($url, "://")
			|| str_contains($url, "data:")
			|| str_starts_with($url, "#")
		)
		{
			return $quote.$url.$quote;
		}

		$url = trim(stripslashes($url), "'\" \r\n\t");
		if (mb_substr($url, 0, 1) == "/")
		{
			return $quote.$url.$quote;
		}

		return $quote.$path.'/'.$url.$quote;
	}

	/**
	 * Remove from file path any parametrs.
	 * @param string $src Path to asset file.
	 * @return string path whithout ?xxx.
	 */
	public static function getAssetPath($src)
	{
		if (($p = mb_strpos($src, "?")) > 0 && !\CMain::IsExternalLink($src))
		{
			$src = mb_substr($src, 0, $p);
		}
		return $src;
	}

	/**
	 * @return bool
	 */
	public function optimizeCss()
	{
		$optimize = $this->optimizeCss
			&& (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			&& Option::get('main', 'optimize_css_files', 'N') == 'Y'
			&& !$this->ajax;

		return $optimize;
	}

	/**
	 * @return bool
	 */
	public function optimizeJs()
	{
		$optimize = $this->optimizeJs
			&& (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			&& Option::get('main', 'optimize_js_files', 'N') == 'Y'
			&& !$this->ajax;

		return $optimize;
	}

	/**
	 * @return bool|null
	 */
	public static function canUseMinifiedAssets()
	{
		static $canLoad = null;
		if ($canLoad === null)
		{
			$canLoad = Option::get("main","use_minified_assets", "Y") == "Y";
		}

		return $canLoad;
	}

	/**
	 * @return boolean
	 */
	public function sliceKernel()
	{
		return (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true);
	}

	/**
	 * Insert inline css.
	 * @param string $css Content or file name.
	 * @param mixed $label Additional info.
	 * @param boolean $inline Show inline.
	 * @return string
	 */
	public function insertCss($css, $label = false, $inline = false)
	{
		if ($label === true)
		{
			$label = ' data-template-style="true" ';
		}
		elseif ($label === false)
		{
			$label = '';
		}

		if ($inline)
		{
			return "<style type=\"text/css\" {$label}>\n{$css}\n</style>\n";
		}
		else
		{
			return "<link href=\"{$css}\" type=\"text/css\" {$label} rel=\"stylesheet\" {$this->xhtmlStyle}>\n";
		}
	}

	/**
	 * insert inline js.
	 * @param string $js Contet or file path.
	 * @param mixed $label Additional info.
	 * @param boolean $inline Show inline.
	 * @return string
	 */
	public function insertJs($js, $label = '', $inline = false)
	{
		if ($inline)
		{
			return "<script {$label}>\n{$js}\n</script>\n";
		}
		else
		{
			return "<script {$label} src=\"$js\"></script>\n";
		}
	}

	/**
	 * Sets templateID and template path
	 * @return void
	 */
	private function setTemplateID()
	{
		static $firstExec = true;
		if ($firstExec && !$this->ajax && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true))
		{
			if (defined("SITE_TEMPLATE_PREVIEW_MODE"))
			{
				$this->templatePath = BX_PERSONAL_ROOT.'/tmp/templates/__bx_preview';
			}
			elseif (defined('SITE_TEMPLATE_ID'))
			{
				$this->siteTemplateID = SITE_TEMPLATE_ID;
				$this->templatePath = SITE_TEMPLATE_PATH;
			}
			else
			{
				$this->siteTemplateID = '.default';
				$this->templatePath = BX_PERSONAL_ROOT."/templates/.default";
			}
			$firstExec = false;
		}
	}

	/**
	 * Add template css to asset
	 * @return void
	 */
	private function addTemplateCss()
	{
		if (
			!$this->ajax
			&& (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			&& $this->templateExists
		)
		{
			$this->css[$this->templatePath . '/styles.css']['TARGET'][] = 'TEMPLATE';
			$this->css[$this->templatePath . '/styles.css']['ADDITIONAL'] = false;

			$this->css[$this->templatePath . '/template_styles.css']['TARGET'][] = 'TEMPLATE';
			$this->css[$this->templatePath . '/template_styles.css']['ADDITIONAL'] = false;
		}
	}

	/**
	 * Prepare string assets.
	 * @return void
	 */
	private function prepareString()
	{
		foreach ($this->strings as $location => $stringLocation)
		{
			foreach ($stringLocation as $key => $item)
			{
				/** @var  $assetTID - get first target where added asset */
				$this->strings[$location][$key]['MODE'] = ($item['MODE'] === null ? $this->targetList[$item['TARGET'][0]]['MODE'] : $item['MODE']);
			}
		}
	}

	/**
	 * Returns asset's paths.
	 * @param string $assetPath Peth to asset.
	 * @return null|array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function getAssetPaths($assetPath)
	{
		$paths = [$assetPath];
		if (self::canUseMinifiedAssets() && preg_match("/(.+)\\.(js|css)$/i", $assetPath, $matches))
		{
			array_unshift($paths, $matches[1].".min.".$matches[2]);
		}

		$result = null;
		$maxMtime = 0;
		foreach ($paths as $path)
		{
			$filePath = $this->documentRoot.$path;
			if (file_exists($filePath) && ($mtime = filemtime($filePath)) > $maxMtime && filesize($filePath) > 0)
			{
				$maxMtime = $mtime;
				$result = [
					"PATH" => $path,
					"FILE_PATH" => $filePath,
					"FULL_PATH" => \CUtil::GetAdditionalFileURL($path, true),
				];
			}
		}

		return $result;
	}

	/**
	 * Gets asset path.
	 * if allowed use minified assets
	 * @param $sourcePath
	 * @return string|null
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function getFullAssetPath($sourcePath)
	{
		$result = $this->getAssetPaths($sourcePath);

		if (is_array($result))
		{
			return $result["FULL_PATH"];
		}
		if (\CMain::IsExternalLink($sourcePath))
		{
			return $sourcePath;
		}

		return null;
	}

	/**
	 * Prepare css asset to optimize.
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @return void
	 */
	private function prepareCss() : void
	{
		$additional = [];

		foreach ($this->css as $css => $set)
		{
			/** @var  $assetTID - get first target where added asset */
			$assetTID = $set['ADDITIONAL'] ? 'TEMPLATE' : $set['TARGET'][0];
			$cssInfo = [
				'PATH' => $css,
				'FULL_PATH' => false,
				'FILE_PATH' => false,
				'SKIP' => false,
				'TARGET' => $assetTID,
				'EXTERNAL' => \CMain::IsExternalLink($css),
				'ADDITIONAL' => $set['ADDITIONAL']
			];

			if ($cssInfo['EXTERNAL'])
			{
				if ($set['ADDITIONAL'])
				{
					$tmpKey = 'TEMPLATE';
					$tmpPrefix = 'template';
				}
				else
				{
					$tmpKey = 'KERNEL';
					$tmpPrefix = 'kernel';
				}

				$cssInfo['MODULE_ID'] = $this->assetCSSCnt;
				$cssInfo['TARGET'] = $tmpKey.'_'.$this->assetCSSCnt;
				$cssInfo['PREFIX'] = $tmpPrefix.'_'.$this->assetCSSCnt;
				$cssInfo['FULL_PATH'] = $cssInfo['PATH'];
				$cssInfo['SKIP'] = true;
				$this->assetCSSCnt++;

				$this->targetList[$tmpKey]['CSS_LIST'][$cssInfo['TARGET']] = [
					'TARGET' => $cssInfo['TARGET'],
					'PREFIX' => $cssInfo['PREFIX'],
					'MODE' => $this->targetList[$assetTID]['MODE'],
					'UNIQUE' => false,
					'WHERE_USED' => []
				];
			}
			else
			{
				if (($paths = $this->getAssetPaths($css)) !== null)
				{
					$cssInfo["PATH"] = $css;
					$cssInfo["FILE_PATH"] = $paths["FILE_PATH"];
					$cssInfo["FULL_PATH"] = $paths["FULL_PATH"];
				}
				else
				{
					unset($this->css[$css]);
					continue;
				}

				$moduleInfo = $this->isKernelCSS($cssInfo['PATH']);
				if ($moduleInfo)
				{
					if ($this->sliceKernel() && $this->optimizeCss() && is_array($moduleInfo))
					{
						$cssInfo['MODULE_ID'] = $moduleInfo['MODULE_ID'];
						$cssInfo['TARGET'] = 'KERNEL_'.$moduleInfo['MODULE_ID'];
						$cssInfo['PREFIX'] = 'kernel_'.$moduleInfo['MODULE_ID'];
						$cssInfo['SKIP'] = $moduleInfo['SKIP'] ?? false;
					}
					else
					{
						$cssInfo['MODULE_ID'] = $this->assetCSSCnt;
						$cssInfo['TARGET'] = 'KERNEL_'.$this->assetCSSCnt;
						$cssInfo['PREFIX'] = 'kernel_'.$this->assetCSSCnt;
						$cssInfo['SKIP'] = true;
						$this->assetCSSCnt++;
					}

					if (isset($this->targetList['KERNEL']['CSS_LIST'][$cssInfo['TARGET']]['MODE']))
					{
						$this->targetList['KERNEL']['CSS_LIST'][$cssInfo['TARGET']]['MODE'] |= $this->targetList[$assetTID]['MODE'];
					}
					else
					{
						$this->targetList['KERNEL']['CSS_LIST'][$cssInfo['TARGET']] = [
							'TARGET' => $cssInfo['TARGET'],
							'PREFIX' => $cssInfo['PREFIX'],
							'MODE' => $set['ADDITIONAL'] ? $this->targetList[$set['TARGET'][0]]['MODE'] : $this->targetList[$assetTID]['MODE'],
							'UNIQUE' => true,
							'WHERE_USED' => []
						];
					}

					if (is_array($moduleInfo))
					{
						$this->targetList['KERNEL']['CSS_LIST'][$cssInfo['TARGET']]['MODULE_NAME'] = $moduleInfo['MODULE_ID'];
					}

					// Add information about sets where used
					foreach ($set['TARGET'] as $setID)
					{
						$this->targetList['KERNEL']['CSS_LIST'][$cssInfo['TARGET']]['WHERE_USED'][$setID] = true;
					}
				}
				elseif (strncmp($cssInfo['PATH'], '/bitrix/js/', 11) != 0 /*||*/ )
				{
					$cssInfo['SKIP'] = !(
						strncmp($cssInfo['PATH'], '/bitrix/panel/', 14) != 0
						&& strncmp($cssInfo['PATH'], '/bitrix/themes/', 15) != 0
						&& strncmp($cssInfo['PATH'], '/bitrix/modules/', 16) != 0
					);
				}
			}

			if ($cssInfo['ADDITIONAL'])
			{
				$additional[] = $cssInfo;
			}
			else
			{
				$this->css[$cssInfo['TARGET']][] = $cssInfo;
			}

			unset($this->css[$css]);
		}

		foreach ($additional as $cssInfo)
		{
			$this->css[$cssInfo['TARGET']][] = $cssInfo;
		}
	}

	/**
	 * Prepare js asset to optimize
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @return void
	 */
	private function prepareJs()
	{
		$additional = [];
		foreach ($this->js as $js => $set)
		{
			/** @var  $assetTID - get first target where added asset */
			$assetTID = $set['ADDITIONAL'] ? 'TEMPLATE' : $set['TARGET'][0];
			$jsInfo = [
				'PATH' => $js,
				'FULL_PATH' => false,
				'FILE_PATH' => false,
				'SKIP' => false,
				'TARGET' => $assetTID,
				'EXTERNAL' => \CMain::IsExternalLink($js),
				'BODY' => false,
				'ADDITIONAL' => $set['ADDITIONAL']
			];

			if ($jsInfo['EXTERNAL'])
			{
				if ($set['ADDITIONAL'])
				{
					$tmpKey = 'TEMPLATE';
					$tmpPrefix = 'template';
				}
				else
				{
					$tmpKey = 'KERNEL';
					$tmpPrefix = 'kernel';
				}

				$jsInfo['MODULE_ID'] = $this->assetJSCnt;
				$jsInfo['TARGET'] = $tmpKey.'_'.$this->assetJSCnt;
				$jsInfo['PREFIX'] = $tmpPrefix.'_'.$this->assetJSCnt;
				$jsInfo['FULL_PATH'] = $jsInfo['PATH'];
				$jsInfo['SKIP'] = true;
				$this->assetJSCnt++;

				$this->targetList[$tmpKey]['JS_LIST'][$jsInfo['TARGET']] = [
					'TARGET' => $jsInfo['TARGET'],
					'PREFIX' => $jsInfo['PREFIX'],
					'MODE' => $this->targetList[$assetTID]['MODE'],
					'UNIQUE' => false,
					'WHERE_USED' => []
				];
			}
			else
			{
				if (($paths = $this->getAssetPaths($js)) !== null)
				{
					$jsInfo["PATH"] = $js;
					$jsInfo["FILE_PATH"] = $paths["FILE_PATH"];
					$jsInfo["FULL_PATH"] = $paths["FULL_PATH"];
				}
				else
				{
					unset($this->js[$js]);
					continue;
				}

				if ($moduleInfo = $this->isKernelJS($jsInfo['PATH']))
				{
					if ($this->sliceKernel() && $this->optimizeJs())
					{
						$jsInfo['MODULE_ID'] = $moduleInfo['MODULE_ID'];
						$jsInfo['TARGET'] = 'KERNEL_'.$moduleInfo['MODULE_ID'];
						$jsInfo['PREFIX'] = 'kernel_'.$moduleInfo['MODULE_ID'];
						$jsInfo['SKIP'] = $moduleInfo['SKIP'] ?? false;
						$jsInfo['BODY'] = $moduleInfo['BODY'];
					}
					else
					{
						$jsInfo['MODULE_ID'] = $this->assetJSCnt;
						$jsInfo['TARGET'] = 'KERNEL_'.$this->assetJSCnt;
						$jsInfo['PREFIX'] = 'kernel_'.$this->assetJSCnt;
						$jsInfo['SKIP'] = true;
						$this->assetJSCnt++;
					}

					if ($jsInfo['BODY'])
					{
						$this->targetList['BODY']['JS_LIST'][$jsInfo['TARGET']] = [
							'TARGET' => $jsInfo['TARGET'],
							'PREFIX' => $jsInfo['PREFIX'],
							'MODE' => $this->targetList[$assetTID]['MODE'],
							'UNIQUE' => true,
							'WHERE_USED' => []
						];
					}
					else
					{
						if (isset($this->targetList['KERNEL']['JS_LIST'][$jsInfo['TARGET']]['MODE']))
						{
							$this->targetList['KERNEL']['JS_LIST'][$jsInfo['TARGET']]['MODE'] |= $this->targetList[$assetTID]['MODE'];
						}
						else
						{
							$this->targetList['KERNEL']['JS_LIST'][$jsInfo['TARGET']] = [
								'TARGET' => $jsInfo['TARGET'],
								'PREFIX' => $jsInfo['PREFIX'],
								'MODE' => $set['ADDITIONAL'] ? $this->targetList[$set['TARGET'][0]]['MODE'] : $this->targetList[$assetTID]['MODE'],
								'UNIQUE' => true,
								'WHERE_USED' => []
							];
						}
					}

					// Add information about sets where used
					foreach ($set['TARGET'] as $setID)
					{
						$this->targetList['KERNEL']['JS_LIST'][$jsInfo['TARGET']]['WHERE_USED'][$setID] = true;
					}
				}
				elseif (strncmp($jsInfo['PATH'], '/bitrix/js/', 11) != 0)
				{
					$jsInfo['SKIP'] = !(
						strncmp($jsInfo['PATH'], '/bitrix/panel/', 14) != 0
						&& strncmp($jsInfo['PATH'], '/bitrix/themes/', 15) != 0
						&& strncmp($jsInfo['PATH'], '/bitrix/modules/', 16) != 0
					);
				}
			}

			if ($jsInfo['ADDITIONAL'])
			{
				$additional[] = $jsInfo;
			}
			else
			{
				$this->js[$jsInfo['TARGET']][] = $jsInfo;
			}
			unset($this->js[$js]);
		}

		// Clean body scripts
		foreach ($this->targetList['BODY']['JS_LIST'] as $item)
		{
			unset($this->targetList['KERNEL']['JS_LIST'][$item['TARGET']]);
		}

		foreach ($additional as $jsInfo)
		{
			$this->js[$jsInfo['TARGET']][] = $jsInfo;
		}
	}

	/**
	 * Return css or page.
	 * @param int $type Target type.
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function getCss($type = AssetShowTargetType::ALL)
	{
		$res = '';
		$additional = [];
		static $setList = [];
		static $ajaxList = [];

		if (empty($setList))
		{
			$this->setTemplateID();
			$this->addTemplateCss();
			$this->prepareCss();
			$setList = $this->getTargetList();
			$optimizeCss = $this->optimizeCss();

			foreach ($setList as $setInfo)
			{
				if (!isset($this->css[$setInfo['NAME']]))
				{
					continue;
				}

				$data = '';
				if (!empty($this->moduleInfo['CSS'][$setInfo['MODULE_NAME']]['DATA']))
				{
					$data = $this->moduleInfo['CSS'][$setInfo['MODULE_NAME']]['DATA'];
				}

				$location = '';
				if (!empty($this->moduleInfo['CSS'][$setInfo['MODULE_NAME']]['LOCATION']))
				{
					$location = $this->moduleInfo['CSS'][$setInfo['MODULE_NAME']]['LOCATION'];
				}

				$resCss = '';
				$listAsset = [];
				$showLabel = ($setInfo['NAME'] == 'TEMPLATE');

				foreach ($this->css[$setInfo['NAME']] as $cssFile)
				{
					$css = $cssFile['FULL_PATH'];
					if ($this->ajax)
					{
						$this->assetList['CSS'][] = $cssFile['PATH'];
						$ajaxList[] = $css;
					}
					elseif ($cssFile['EXTERNAL'])
					{
						$resCss .= $this->insertCss($css, $showLabel);
						$this->fileList['CSS'][$setInfo['NAME']]['FILES'][] = $css;
					}
					elseif ($optimizeCss)
					{
						if ($cssFile['SKIP'])
						{
							$resCss .= $this->insertCss($css, $showLabel);
							$this->fileList['CSS'][$setInfo['NAME']]['FILES'][] = $css;
						}
						else
						{
							$listAsset[] = $cssFile;
						}
					}
					else
					{
						$resCss .= $this->insertCss($css, $showLabel);
						$this->fileList['CSS'][$setInfo['NAME']]['FILES'][] = $css;
					}
				}

				$optimizedAsset = $this->optimizeAsset($listAsset, $setInfo['UNIQUE'], $setInfo['PREFIX'], $setInfo['NAME'], 'css', $data);

				$resCss = $optimizedAsset['RESULT'].$resCss;
				if ($location == AssetLocation::AFTER_CSS)
				{
					$additional[] = [
						'FILES' => $optimizedAsset['FILES'],
						'SOURCE_FILES' => $optimizedAsset['SOURCE_FILES'],
						'RES' => $resCss
					];
				}
				else
				{
					$this->assetList['CSS'][$setInfo['PARENT_NAME']][$setInfo['NAME']] = $optimizedAsset['FILES'];
					$this->assetList['SOURCE_CSS'][$setInfo['PARENT_NAME']][$setInfo['NAME']] = ($optimizedAsset['SOURCE_FILES'] ?? []);
					$this->targetList[$setInfo['PARENT_NAME']]['CSS_RES'][$setInfo['NAME']][] = $resCss;
				}
			}

			foreach ($additional as $bundle)
			{
				$templateFiles = $this->assetList['CSS']['TEMPLATE']['TEMPLATE'] ?? [];

				$this->assetList['CSS']['TEMPLATE']['TEMPLATE'] = array_merge($templateFiles, $bundle['FILES']);
				$this->assetList['SOURCE_CSS']['TEMPLATE']['TEMPLATE'] = array_merge($templateFiles, $bundle['SOURCE_FILES']);
				$this->targetList['TEMPLATE']['CSS_RES']['TEMPLATE'][] = $bundle['RES'];
			}

			unset($additional, $templateFiles, $bundle);
		}

		if ($this->ajax && !empty($ajaxList))
		{
			$res .= '<script>'."BX.loadCSS(['".implode("','", $ajaxList)."']);".'</script>';
		}

		if ($type == AssetShowTargetType::KERNEL)
		{
			$res .= $this->showAsset($setList, 'css', 'KERNEL');
		}
		elseif ($type == AssetShowTargetType::TEMPLATE_PAGE)
		{
			foreach ($this->targetList as $setName => $set)
			{
				if ($setName != 'TEMPLATE' && $setName != 'KERNEL')
				{
					$res .= $this->showAsset($setList, 'css', $setName);
				}
			}

			$res .= $this->showAsset($setList, 'css', 'TEMPLATE');
		}
		else
		{
			foreach ($this->targetList as $setName => $set)
			{
				if ($setName != 'TEMPLATE')
				{
					$res .= $this->showAsset($setList, 'css', $setName);
				}
			}

			$res .= $this->showAsset($setList, 'css', 'TEMPLATE');
		}

		return $res;
	}

	/**
	 * Return JS page assets.
	 * @param int $type Target type.
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	function getJs($type = AssetShowTargetType::ALL)
	{
		static $setList = [];

		$res = '';
		$type = (int) $type;
		$type = (($type == AssetShowTargetType::KERNEL && $this->headString && !$this->headScript) ? AssetShowTargetType::ALL : $type);
		$optimize = $this->optimizeJs();
		if (empty($setList))
		{
			$this->prepareJs();
			$setList = $this->getTargetList('JS');

			foreach ($setList as $setInfo)
			{
				if (!isset($this->js[$setInfo['NAME']]))
				{
					continue;
				}

				$data = '';
				if (!empty($this->moduleInfo['JS'][$setInfo['MODULE_NAME']]['DATA']))
				{
					$data = $this->moduleInfo['JS'][$setInfo['MODULE_NAME']]['DATA'];
				}

				$resJs = '';
				$listAsset = [];
				foreach ($this->js[$setInfo['NAME']] as $jsFile)
				{
					$js = $jsFile['FULL_PATH'];
					if ($optimize)
					{
						if ($jsFile['SKIP'])
						{
							$this->fileList['JS'][$setInfo['NAME']]['FILES'][] = $js;
							$resJs .= "<script src=\"{$js}\"></script>\n";
						}
						else
						{
							$listAsset[] = $jsFile;
						}
					}
					else
					{
						$this->fileList['JS'][$setInfo['NAME']]['FILES'][] = $js;
						$resJs .= "<script src=\"{$js}\"></script>\n";
					}
				}
				$optAsset = $this->optimizeAsset($listAsset, $setInfo['UNIQUE'], $setInfo['PREFIX'], $setInfo['NAME'], 'js', $data);
				$this->assetList['JS'][$setInfo['PARENT_NAME']][$setInfo['NAME']] = $optAsset['FILES'];
				$this->assetList['SOURCE_JS'][$setInfo['PARENT_NAME']][$setInfo['NAME']] = ($optAsset['SOURCE_FILES'] ?? []);
				$this->targetList[$setInfo['PARENT_NAME']]['JS_RES'][$setInfo['NAME']][] = $optAsset['RESULT'].$resJs;
			}
			unset($optAsset, $resJs, $listAsset);
		}

		if ($type == AssetShowTargetType::KERNEL && ($this->mode & $this->targetList['KERNEL']['MODE']))
		{
			$setName = 'KERNEL';
			$res .= $this->getStrings(AssetLocation::AFTER_CSS);
			$res .= $this->showAsset($setList,'js', $setName);
			$res .= $this->showFilesList();
			$res .= $this->getStrings(AssetLocation::AFTER_JS_KERNEL);

			if (!$this->bodyScript)
			{
				$res .= $this->getStrings(AssetLocation::BODY_END);
				$res .= $this->showAsset($setList,'js', 'BODY');
			}
		}
		elseif ($type == AssetShowTargetType::TEMPLATE_PAGE)
		{
			foreach ($this->targetList as $setName => $set)
			{
				if ($setName != 'KERNEL' && $setName != 'BODY')
				{
					$setName = $this->fixJsSetOrder($setName);
					$res .= $this->showAsset($setList,'js', $setName);
				}
			}
			$res .= $this->getStrings(AssetLocation::AFTER_JS);
		}
		elseif ($type == AssetShowTargetType::BODY && ($this->mode & $this->targetList['BODY']['MODE']))
		{
			$setName = 'BODY';
			$res .= $this->getStrings(AssetLocation::BODY_END);
			$res .= $this->showAsset($setList,'js', $setName);
		}
		else
		{
			foreach ($this->targetList as $setName => $set)
			{
				if ($this->mode & $set['MODE'])
				{
					$setName = $this->fixJsSetOrder($setName);
					if ($setName == 'KERNEL')
					{
						$res .= $this->getStrings(AssetLocation::AFTER_CSS);
						$res .= $this->showAsset($setList, 'js', $setName);
						$res .= $this->showFilesList();
						$res .= $this->getStrings(AssetLocation::AFTER_JS_KERNEL);

						if (!$this->bodyScript)
						{
							$res .= $this->getStrings(AssetLocation::BODY_END);
							$res .= $this->showAsset($setList,'js', 'BODY');
						}
					}
					elseif ($setName != 'BODY')
					{
						$res .= $this->showAsset($setList, 'js', $setName);
					}
				}
			}

			$res .= $this->getStrings(AssetLocation::AFTER_JS);
		}

		return (trim($res) == '' ? $res : $res."\n");
	}

	/**
	 * Convert location for new format.
	 * @param mixed $location AssetLocation.
	 * @return AssetLocation
	 */
	public static function getLocationByName($location)
	{
		if ($location === false || $location === 'DEFAULT')
		{
			$location = AssetLocation::AFTER_JS_KERNEL;
		}
		elseif ($location === true)
		{
			$location = AssetLocation::AFTER_CSS;
		}

		return $location;
	}

	/**
	 * Insert JS code to sets assets included in page.
	 * @return string
	 */
	public function showFilesList()
	{
		$res = '';
		if (!\CJSCore::IsCoreLoaded())
		{
			return $res;
		}

		if (!empty($this->assetList['JS']))
		{
			$assets = [];
			foreach ($this->getTargetList('JS') as $set)
			{
				if ($this->mode & $set['MODE']
					&& isset($this->assetList['SOURCE_JS'][$set['PARENT_NAME']][$set['NAME']])
					&& is_array($this->assetList['SOURCE_JS'][$set['PARENT_NAME']][$set['NAME']]))
				{
					$assets = array_merge($assets, $this->assetList['SOURCE_JS'][$set['PARENT_NAME']][$set['NAME']]);
				}
			}

			if (!empty($assets))
			{
				$res .= '<script>BX.setJSList('.\CUtil::phpToJSObject($assets).');</script>';
				$res .= "\n";
			}
		}

		if (!empty($this->assetList['CSS']))
		{
			$assets = [];
			foreach ($this->getTargetList('CSS') as $set)
			{
				if ($this->mode & $set['MODE']
					&& isset($this->assetList['SOURCE_CSS'][$set['PARENT_NAME']][$set['NAME']])
					&& is_array($this->assetList['SOURCE_CSS'][$set['PARENT_NAME']][$set['NAME']])
				)
				{
					$assets = array_merge($assets, $this->assetList['SOURCE_CSS'][$set['PARENT_NAME']][$set['NAME']]);
				}
			}

			if (!empty($assets))
			{
				$res .= '<script>BX.setCSSList('.\CUtil::phpToJSObject($assets).');</script>';
				$res .= "\n";
			}
		}
		return $res;
	}

	/**
	 * Add information about kernel module css.
	 * @param string $module Module name.
	 * @param array $css Css files.
	 * @param array $settings Settings.
	 * @return void
	 */
	function addCssKernelInfo($module = '', $css = [], $settings = [])
	{
		if (empty($module) || empty($css))
		{
			return;
		}

		if (!array_key_exists($module, $this->moduleInfo['CSS']))
		{
			$this->moduleInfo['CSS'][$module] = [
				'MODULE_ID' => $module,
				'BODY' => false,
				'FILES_INFO' => true,
				'IS_KERNEL' => true,
				'DATA' => '',
				'SKIP' => false
			];
		}

		foreach ($css as $key)
		{
			$key = self::getAssetPath($key);
			$this->kernelAsset['CSS'][$key] = $module;
		}

		$this->moduleInfo['CSS'][$module]['FILES_INFO'] = true;
		if (!empty($settings['DATA']))
		{
			$this->moduleInfo['CSS'][$module]['DATA'] = $settings['DATA'];
		}

		if (!empty($settings['LOCATION']))
		{
			$this->moduleInfo['CSS'][$module]['LOCATION'] = $settings['LOCATION'];
		}
	}

	/**
	 * Add information about kernel js modules.
	 * @param string $module Module name.
	 * @param array $js Js files.
	 * @param array $settings Settings.
	 * @return void
	 */
	function addJsKernelInfo($module = '', $js = [], $settings = [])
	{
		if (empty($module) || empty($js))
		{
			return;
		}

		if (!array_key_exists($module, $this->moduleInfo['JS']))
		{
			$this->moduleInfo['JS'][$module] = [
				'MODULE_ID' => $module,
				'BODY' => false,
				'FILES_INFO' => true,
				'IS_KERNEL' => true,
				'DATA' => '',
				'SKIP' => false
			];
		}

		foreach ($js as $key)
		{
			$key = self::getAssetPath($key);
			$this->kernelAsset['JS'][$key] = $module;
		}

		$this->moduleInfo['JS'][$module]['FILES_INFO'] = true;
		if (!empty($settings['DATA']))
		{
			$this->moduleInfo['JS'][$module]['DATA'] = $settings['DATA'];
		}
	}

	/**
	 * Return information about file and check is it in kernel pack.
	 * @param string $css File path.
	 * @return array|bool
	 */
	function isKernelCSS($css)
	{
		/** If optimisation off */
		if (!($this->sliceKernel() && $this->optimizeCss()))
		{
			return ((strncmp($css, '/bitrix/js/', 11) == 0) || (strncmp($css, '/bitrix/css/', 12) == 0));
		}

		/** If optimization on */
		if (array_key_exists($css, $this->kernelAsset['CSS']))
		{
			return $this->moduleInfo['CSS'][$this->kernelAsset['CSS'][$css]];
		}
		elseif ((strncmp($css, '/bitrix/js/', 11) == 0) || (strncmp($css, '/bitrix/css/', 12) == 0))
		{
			$tmp = explode('/', $css);
			$moduleID = $tmp['3'];
			unset($tmp);

			if (empty($moduleID))
			{
				return false;
			}

			return [
				'MODULE_ID' => $moduleID.'_'.$this->assetCSSCnt++,
				'BODY' => false,
				'FILES_INFO' => false,
				'IS_KERNEL' => true,
				'DATA' => '',
				'SKIP' => true
			];
		}

		return false;
	}

	/**
	 * Return information about file and check is it in kernel pack.
	 * @param string $js File path.
	 * @return array|bool
	 */
	function isKernelJS($js)
	{
		/** If optimisation off */
		if (!($this->sliceKernel() && $this->optimizeJs()))
		{
			return (strncmp($js, '/bitrix/js/', 11) == 0);
		}

		/** If optimization on */
		if (array_key_exists($js, $this->kernelAsset['JS']))
		{
			return $this->moduleInfo['JS'][$this->kernelAsset['JS'][$js]];
		}
		elseif (strncmp($js, '/bitrix/js/', 11) == 0)
		{
			$tmp = explode('/', $js);
			$moduleID = $tmp['3'];
			unset($tmp);

			if (empty($moduleID))
			{
				return false;
			}

			return [
				'MODULE_ID' => $moduleID.'_'.$this->assetJSCnt++,
				'BODY' => false,
				'FILES_INFO' => false,
				'IS_KERNEL' => true,
				'DATA' => '',
				'SKIP' => true
			];
		}

		return false;
	}

	/**
	 * Sets unique mode for set.
	 * @param string $setID Target ID.
	 * @param string $uniqueID Unique type.
	 * @return bool
	 */
	public function setUnique($setID = '', $uniqueID = '')
	{
		$setID = preg_replace('#[^a-z0-9_]#i', '', $setID);
		$uniqueID = preg_replace('#[^a-z0-9_]#i', '', $uniqueID);
		if (!(empty($setID) || empty($uniqueID)) && isset($this->targetList[$setID]))
		{
			$this->targetList[$setID]['UNIQUE'] = true;
			$this->targetList[$setID]['PREFIX'] .= ($uniqueID == '' ? '' : '_'.$uniqueID);
			return true;
		}
		return false;
	}

	/**
	 * Show asset resource.
	 * @param array $setList Set list.
	 * @param string $type Asset type css or js.
	 * @param string $setName Parent set name.
	 * @return string
	 */
	private function showAsset($setList = [], $type = 'css', $setName = '')
	{
		$res = '';
		$type = ($type == 'css' ? 'CSS_RES' : 'JS_RES');
		$skipCheck = ($setName == '');

		foreach ($setList as $setInfo)
		{
			if (
				($skipCheck || $setName == $setInfo['PARENT_NAME'])
				&& $this->mode & $setInfo['MODE']
				&& isset($this->targetList[$setInfo['PARENT_NAME']][$type][$setInfo['NAME']]))
			{
				$res .= implode("\n", $this->targetList[$setInfo['PARENT_NAME']][$type][$setInfo['NAME']]);
			}
		}

		return $res;
	}

	/**
	 * Fix current set order for js.
	 * @param string $setName Set name.
	 * @return string
	 */
	private function fixJsSetOrder($setName = '')
	{
		if ($setName == 'PAGE')
		{
			$setName = 'TEMPLATE';
		}
		elseif ($setName == 'TEMPLATE')
		{
			$setName = 'PAGE';
		}

		return $setName;
	}

	/**
	 * Get time for current asset.
	 * @param string $file File path.
	 * @return bool|string
	 */
	public static function getAssetTime($file = '')
	{
		$qpos = mb_strpos($file, '?');
		if ($qpos === false)
		{
			return false;
		}
		$qpos++;

		return mb_substr($file, $qpos);
	}

	/**
	 * Return md5 for asset.
	 * @param array $assetList Asset list.
	 * @return string
	 */
	private function getAssetChecksum($assetList = [])
	{
		$result = [];
		foreach ($assetList as $asset)
		{
			$result[$asset['PATH']] = $asset['FULL_PATH'];
		}
		ksort($result);

		return md5(implode('_', $result));
	}

	/**
	 * Check assets and return action and files.
	 * @param array $assetList Asset list.
	 * @param string $infoFile Path to metadata file.
	 * @param string $optimFile Path to packed file.
	 * @param bool $unique Unique type.
	 * @return array
	 */
	private function isAssetChanged($assetList = [], $infoFile = '', $optimFile = '', $unique = false)
	{
		$result = [
			'FILE' => [],
			'ACTION' => 'NO',
			'FILE_EXIST' => false,
			'FILES_INFO' => []
		];

		if (file_exists($infoFile) && file_exists($optimFile))
		{
			/** @noinspection PhpIncludeInspection */
			include($infoFile);

			/** @var array $filesInfo - information about files in set */
			$result['FILES_INFO'] = is_array($filesInfo) ? $filesInfo : [];
			$result['FILE_EXIST'] = true;
			if ($unique)
			{
				if (is_array($filesInfo))
				{
					foreach ($assetList as $asset)
					{
						if (isset($filesInfo[$asset['PATH']]))
						{
							if ($this->getAssetTime($asset['FULL_PATH']) != $filesInfo[$asset['PATH']])
							{
								$result = [
									'FILE' => $assetList,
									'ACTION' => 'NEW',
									'FILES_INFO' => []
								];
								break;
							}
						}
						else
						{
							$result['FILE'][] = $asset;
							$result['ACTION'] = 'UP';
						}
					}
				}
				else
				{
					$result = [
						'FILE' => $assetList,
						'ACTION' => 'NEW',
						'FILES_INFO' => []
					];
				}
			}
		}
		else
		{
			$result['FILE'] = $assetList;
			$result['ACTION'] = 'NEW';
		}

		return $result;
	}

	/**
	 * @param array $files Files for optimisation.
	 * @param bool $unique Unique type.
	 * @param string $prefix Prefix for packed file.
	 * @param string $setName Set name.
	 * @param string $type Asset type css or js.
	 * @param string $data Additional info.
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function optimizeAsset($files = [], $unique = false, $prefix = 'default', $setName = '', $type = 'css', $data = '')
	{
		if ((!is_array($files) || empty($files)))
		{
			return ['RESULT' => '', 'FILES' => []];
		}

		$this->setTemplateID();
		$res = $comments = $contents = '';
		$prefix = trim($prefix);
		$prefix = mb_strlen($prefix) < 1 ? 'default' : $prefix;
		$add2End = (strncmp($prefix, 'kernel', 6) == 0);
		$type = ($type == 'js' ? 'js' : 'css');

		// when we can't write files
		$noCheckOnly = !defined('BX_HEADFILES_CACHE_CHECK_ONLY');
		$prefix = ($unique ? $prefix : $prefix.'_'.$this->getAssetChecksum($files));

		$optimPath = BX_PERSONAL_ROOT.'/cache/'.$type.'/'.SITE_ID.'/'.$this->siteTemplateID.'/'.$prefix.'/';

		$infoFile = $this->documentRoot.BX_PERSONAL_ROOT.'/managed_cache/'.$this->dbType.'/'.$type.'/'.SITE_ID.'/'.$this->siteTemplateID.'/'.$prefix.'/info_v'.self::version.'.php';

		$optimFile = $optimPath.$prefix.'_v'.self::version.($type == 'css' ? '.css' : '.js');
		$optimFName = $this->documentRoot.$optimFile;

		$tmpInfo = $this->isAssetChanged($files, $infoFile, $optimFName, $unique);
		$filesInfo = $tmpInfo['FILES_INFO'];
		$action = $tmpInfo['ACTION'];
		$files = $tmpInfo['FILE'];
		$optimFileExist = $tmpInfo['FILE_EXIST'] ?? false;

		$writeResult = ($action != 'NEW');
		$currentFileList = &$this->fileList[strtoupper($type)][$setName];

		if ($action != 'NO')
		{
			foreach ($tmpInfo['FILE'] as $newFile)
			{
				$currentFileList['UP_NEW_FILES'][] = $newFile['FULL_PATH'];
			}

			if ($action == 'UP')
			{
				if ($noCheckOnly)
				{
					$contents .= file_get_contents($optimFName);
				}
				else
				{
					$writeResult = false;
				}
			}

			$needWrite = false;
			if ($noCheckOnly)
			{
				$newContent = '';
				$mapNeeded = false;
				foreach ($files as $file)
				{
					$assetContent = file_get_contents($file['FILE_PATH']);
					if ($type == 'css')
					{
						$comments .= "/* ".$file['FULL_PATH']." */\n";
						$assetContent = $this->fixCSSIncludes($assetContent, $file['PATH']);
						$assetContent = "\n/* Start:".$file['FULL_PATH']."*/\n".$assetContent."\n/* End */\n";
						$newContent .= "\n".$assetContent;
					}
					else
					{
						$info = [
							"full" => $file['FULL_PATH'],
							"source" => $file['PATH'],
							"min" => "",
							"map" => "",
						];

						if (preg_match("/\\.min\\.js$/i", $file['FILE_PATH']))
						{
							$sourceMap = self::cutSourceMap($assetContent);
							if ($sourceMap <> '')
							{
								$dirPath = IO\Path::getDirectory($file['PATH']);
								$info["map"] = $dirPath."/".$sourceMap;
								$info["min"] = self::getAssetPath($file['FULL_PATH']);
								$mapNeeded = true;
							}
						}

						$comments .= "; /* ".$file['FULL_PATH']."*/\n";
						$newContent .= "\n".self::HEADER_START_TAG.serialize($info).self::HEADER_END_TAG."\n".$assetContent."\n/* End */\n;";
					}

					$filesInfo[$file['PATH']] = $this->getAssetTime($file['FULL_PATH']);
					$needWrite = true;
				}

				if ($needWrite)
				{
					$sourceMap = self::cutSourceMap($contents);
					$mapNeeded = $mapNeeded || $sourceMap <> '';

					// Write packed files and meta information
					$contents = ($add2End ? $comments.$contents.$newContent : $newContent.$contents.$comments);
					if ($mapNeeded)
					{
						$contents .= self::SOURCE_MAP_TAG.$prefix.".map.js";
					}

					if ($writeResult = $this->write($optimFName, $contents))
					{
						$cacheInfo = '<?php $filesInfo = [';

						foreach ($filesInfo as $key => $hash)
						{
							$cacheInfo .= '"'.EscapePHPString($key).'" => "'.$hash.'",';
						}

						$cacheInfo .= "]; ?>";
						$this->write($infoFile, $cacheInfo, false);

						if ($mapNeeded)
						{
							$this->write($this->documentRoot.$optimPath.$prefix.".map.js", self::generateSourceMap($prefix.".js", $contents), false);
						}
					}
				}
				elseif ($optimFileExist)
				{
					$writeResult = true;
				}
				unset($contents);
			}
		}

		$label = (($type == 'css') && ($prefix == 'template' || mb_substr($prefix, 0, 9) == 'template_') ? ' data-template-style="true" ' : '');

		$bundleFile = '';
		$extendData = ($data != '' ? ' '.trim($data) : '');
		$extendData .= ($label != '' ? ' '.trim($label) : '');

		if ($writeResult || $unique && $action == 'UP')
		{
			$bundleFile = \CUtil::GetAdditionalFileURL($optimFile);
			$currentFileList['FILES'][] = $bundleFile;

			if ($type == 'css')
			{
				$res .= $this->insertCss($bundleFile, $extendData);
			}
			else
			{
				$res .= $this->insertJs($bundleFile, $extendData);
			}
		}

		if (!$writeResult)
		{
			foreach ($files as $file)
			{
				$currentFileList['FILES'][] = $file['FULL_PATH'];
				if ($type == 'css')
				{
					$res .= $this->insertCss($file['FULL_PATH'], $extendData);
				}
				else
				{
					$res .= $this->insertJs($file['FULL_PATH'], $extendData);
				}
			}
		}

		$resultFiles = [];
		if (is_array($filesInfo))
		{
			foreach ($filesInfo as $key => $hash)
			{
				$resultFiles[] = $key.'?'.$hash;

			}
		}

		unset($files);

		if ($bundleFile != '')
		{
			$currentFileList['FULL_FILES'][$bundleFile] = $resultFiles;
		}
		return ['RESULT' => $res, 'FILES' => $resultFiles, 'SOURCE_FILES' => array_keys($filesInfo)];
	}

	/**
	 * Cuts and returns source map comment.
	 * @param string &$content Asset content.
	 * @return string
	 */
	private static function cutSourceMap(&$content)
	{
		$sourceMapName = "";

		$length = strlen($content);
		$position = $length > 512 ? $length - 512 : 0;
		$lastLine = strpos($content, self::SOURCE_MAP_TAG, $position);
		if ($lastLine !== false)
		{
			$nameStart = $lastLine + strlen(self::SOURCE_MAP_TAG);
			if (($newLinePos = strpos($content, "\n", $nameStart)) !== false)
			{
				$sourceMapName = substr($content, $nameStart, $newLinePos - $nameStart);
			}
			else
			{
				$sourceMapName = substr($content, $nameStart);
			}

			$sourceMapName = trim($sourceMapName);
			$content = substr($content, 0, $lastLine);
		}

		return $sourceMapName;
	}

	/**
	 * Returns array of file data.
	 * @param string $content Content.
	 * @return array
	 */
	private static function getFilesInfo($content)
	{
		$offset = 0;
		$line = 0;

		$result = [];
		while (($newLinePos = strpos($content, "\n", $offset)) !== false)
		{
			$line++;
			$offset = $newLinePos + 1;
			if (substr($content, $offset, strlen(self::HEADER_START_TAG)) === self::HEADER_START_TAG)
			{
				$endingPos = strpos($content, self::HEADER_END_TAG, $offset);
				if ($endingPos === false)
				{
					break;
				}

				$startData = $offset + strlen(self::HEADER_START_TAG);
				$data = unserialize(substr($content, $startData, $endingPos - $startData), ['allowed_classes' => false]);

				if (is_array($data))
				{
					$data["line"] = $line + 1;
					$result[] = $data;
				}

				$offset = $endingPos;
			}
		}

		return $result;
	}

	/**
	 * Generates source map content.
	 * @param string $fileName File name.
	 * @param string $content Content.
	 * @return string
	 */
	private static function generateSourceMap($fileName, $content)
	{
		$files = self::getFilesInfo($content);
		$sections = "";
		foreach ($files as $file)
		{
			if (!isset($file["map"]) || mb_strlen($file["map"]) < 1)
			{
				continue;
			}

			$filePath = Main\Loader::getDocumentRoot().$file["map"];
			if (file_exists($filePath) && ($content = file_get_contents($filePath)) !== false)
			{
				if ($sections !== "")
				{
					$sections .= ",";
				}

				$dirPath = IO\Path::getDirectory($file["source"]);
				$sourceName = IO\Path::getName($file["source"]);
				$minName = IO\Path::getName($file["min"]);

				$sourceMap = str_replace(
					[$sourceName, $minName],
					[$dirPath."/".$sourceName, $dirPath."/".$minName],
					$content
				);
				$sections .= '{"offset": { "line": '.$file["line"].', "column": 0 }, "map": '.$sourceMap.'}';
			}
		}

		return '{"version":3, "file":"'.$fileName.'", "sections": ['.$sections.']}';
	}

	/**
	 * Write optimized css, js files or info file.
	 * @param string $filePath Path for optimized css, js or info file.
	 * @param string $content File contents.
	 * @param bool $gzip For disabled gzip.
	 * @return bool
	 */
	function write($filePath, $content, $gzip = true)
	{
		$fnTmp = $filePath.'.tmp';

		if (!CheckDirPath($filePath) || !$fh = fopen($fnTmp, "wb"))
		{
			return false;
		}

		$written = fwrite($fh, $content);
		$len = strlen($content);
		fclose($fh);

		if (file_exists($filePath))
		{
			@unlink($filePath);
		}

		$result = false;
		if ($written === $len)
		{
			$result = true;
			rename($fnTmp, $filePath);
			@chmod($filePath, BX_FILE_PERMISSIONS);
			if ($gzip && self::gzipEnabled())
			{
				$fnTmpGz = $filePath.'.tmp.gz';
				$fnGz = $filePath.'.gz';

				if ($gz = gzopen($fnTmpGz, 'wb9f'))
				{
					$writtenGz = @gzwrite ($gz, $content);
					gzclose($gz);

					if (file_exists($fnGz))
					{
						@unlink($fnGz);
					}

					if ($writtenGz === $len)
					{
						rename($fnTmpGz, $fnGz);
						@chmod($fnGz, BX_FILE_PERMISSIONS);
					}

					if (file_exists($fnTmpGz))
					{
						@unlink($fnTmpGz);
					}
				}
			}
		}

		if (file_exists($fnTmp))
		{
			@unlink($fnTmp);
		}

		return $result;
	}
}
