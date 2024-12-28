<?php

namespace Bitrix\Main;

class UrlRewriter
{
	const DEFAULT_SORT = 100;

	protected static function loadRules($siteId)
	{
		$site = SiteTable::getRow(["filter" => ["=LID" => $siteId], "cache" => ["ttl" => 3600]]);
		$docRoot = $site["DOC_ROOT"];

		if (!empty($docRoot))
		{
			$docRoot = IO\Path::normalize($docRoot);
		}
		else
		{
			$docRoot = Application::getDocumentRoot();
		}

		$arUrlRewrite = [];

		if (IO\File::isFileExists($docRoot . "/urlrewrite.php"))
		{
			include($docRoot . "/urlrewrite.php");
		}

		foreach ($arUrlRewrite as &$rule)
		{
			if (!isset($rule["SORT"]))
			{
				$rule["SORT"] = self::DEFAULT_SORT;
			}
		}

		return $arUrlRewrite;
	}

	protected static function saveRules($siteId, array $urlRewrite)
	{
		$site = SiteTable::getRow(["filter" => ["=LID" => $siteId], "cache" => ["ttl" => 3600]]);
		$docRoot = $site["DOC_ROOT"];

		if (!empty($docRoot))
		{
			$docRoot = IO\Path::normalize($docRoot);
		}
		else
		{
			$docRoot = Application::getDocumentRoot();
		}

		$data = var_export($urlRewrite, true);

		$event = new Event("main", "onUrlRewriteSaveRules", [
			$siteId,
			$docRoot,
			$urlRewrite,
		]);
		$event->send();

		$filename = $docRoot . "/urlrewrite.php";
		IO\File::putFileContents($filename, "<" . "?php\n\$arUrlRewrite=" . $data . ";\n");
		Application::resetAccelerator($filename);
	}

	public static function getList($siteId, $filter = [], $order = [])
	{
		if (empty($siteId))
		{
			throw new ArgumentNullException("siteId");
		}

		$urlRewrite = static::loadRules($siteId);

		$result = [];
		$resultKeys = self::filterRules($urlRewrite, $filter);
		foreach ($resultKeys as $key)
		{
			$result[] = $urlRewrite[$key];
		}

		if (!empty($order) && !empty($result))
		{
			$orderKeys = array_keys($order);
			$orderBy = array_shift($orderKeys);
			$orderDir = $order[$orderBy];

			$orderBy = mb_strtoupper($orderBy);
			$orderDir = mb_strtoupper($orderDir);

			$orderDir = (($orderDir == "DESC") ? SORT_DESC : SORT_ASC);

			$ar = [];
			foreach ($result as $key => $row)
			{
				$ar[$key] = $row[$orderBy];
			}

			array_multisort($ar, $orderDir, $result);
		}

		return $result;
	}

	protected static function filterRules(array $urlRewrite, array $filter)
	{
		$resultKeys = [];

		foreach ($urlRewrite as $keyRule => $rule)
		{
			$isMatched = true;
			foreach ($filter as $keyFilter => $valueFilter)
			{
				$isNegative = false;
				if (str_starts_with($keyFilter, "!"))
				{
					$isNegative = true;
					$keyFilter = mb_substr($keyFilter, 1);
				}

				if ($keyFilter === 'QUERY')
				{
					$isMatchedTmp = preg_match($rule["CONDITION"], $valueFilter);
				}
				elseif ($keyFilter === 'CONDITION')
				{
					$isMatchedTmp = ($rule["CONDITION"] == $valueFilter);
				}
				elseif ($keyFilter === 'ID')
				{
					$isMatchedTmp = (isset($rule["ID"]) && ($rule["ID"] == $valueFilter));
				}
				elseif ($keyFilter === 'PATH')
				{
					$isMatchedTmp = ($rule["PATH"] == $valueFilter);
				}
				else
				{
					throw new ArgumentException("arFilter");
				}

				$isMatched = ($isNegative xor $isMatchedTmp);

				if (!$isMatched)
				{
					break;
				}
			}

			if ($isMatched)
			{
				$resultKeys[] = $keyRule;
			}
		}

		return $resultKeys;
	}

	protected static function recordsCompare($a, $b)
	{
		$sortA = isset($a["SORT"]) ? intval($a["SORT"]) : self::DEFAULT_SORT;
		$sortB = isset($b["SORT"]) ? intval($b["SORT"]) : self::DEFAULT_SORT;

		if ($sortA > $sortB)
		{
			return 1;
		}
		elseif ($sortA < $sortB)
		{
			return -1;
		}

		$lenA = mb_strlen($a["CONDITION"]);
		$lenB = mb_strlen($b["CONDITION"]);
		if ($lenA < $lenB)
		{
			return 1;
		}
		elseif ($lenA > $lenB)
		{
			return -1;
		}
		else
		{
			return 0;
		}
	}

	public static function add($siteId, $fields)
	{
		if (empty($siteId))
		{
			throw new ArgumentNullException("siteId");
		}

		$urlRewrite = static::loadRules($siteId);

		// if rule is exist – return
		foreach ($urlRewrite as $rule)
		{
			if ($fields["CONDITION"] == $rule["CONDITION"])
			{
				return;
			}
		}

		$urlRewrite[] = [
			"CONDITION" => $fields["CONDITION"],
			"RULE" => $fields["RULE"],
			"ID" => $fields["ID"],
			"PATH" => $fields["PATH"],
			"SORT" => isset($fields["SORT"]) ? intval($fields["SORT"]) : self::DEFAULT_SORT,
		];

		uasort($urlRewrite, ['\Bitrix\Main\UrlRewriter', "recordsCompare"]);

		static::saveRules($siteId, $urlRewrite);
	}

	public static function update($siteId, $filter, $fields)
	{
		if (empty($siteId))
		{
			throw new ArgumentNullException("siteId");
		}

		$urlRewrite = static::loadRules($siteId);

		$resultKeys = self::filterRules($urlRewrite, $filter);
		foreach ($resultKeys as $key)
		{
			if (array_key_exists("CONDITION", $fields))
			{
				$urlRewrite[$key]["CONDITION"] = $fields["CONDITION"];
			}
			if (array_key_exists("RULE", $fields))
			{
				$urlRewrite[$key]["RULE"] = $fields["RULE"];
			}
			if (array_key_exists("ID", $fields))
			{
				$urlRewrite[$key]["ID"] = $fields["ID"];
			}
			if (array_key_exists("PATH", $fields))
			{
				$urlRewrite[$key]["PATH"] = $fields["PATH"];
			}
			if (array_key_exists("SORT", $fields))
			{
				$urlRewrite[$key]["SORT"] = intval($fields["SORT"]);
			}
		}

		uasort($urlRewrite, ['\Bitrix\Main\UrlRewriter', "recordsCompare"]);

		static::saveRules($siteId, $urlRewrite);
	}

	public static function delete($siteId, $filter)
	{
		if (empty($siteId))
		{
			throw new ArgumentNullException("siteId");
		}

		$urlRewrite = static::loadRules($siteId);

		$resultKeys = self::filterRules($urlRewrite, $filter);
		foreach ($resultKeys as $key)
		{
			unset($urlRewrite[$key]);
		}

		uasort($urlRewrite, ['\Bitrix\Main\UrlRewriter', "recordsCompare"]);

		static::saveRules($siteId, $urlRewrite);
	}

	public static function reindexAll($maxExecutionTime = 0, $ns = [])
	{
		@set_time_limit(0);
		if (!is_array($ns))
		{
			$ns = [];
		}

		if ($maxExecutionTime <= 0)
		{
			$nsOld = $ns;
			$ns = [
				"CLEAR" => "N",
				"ID" => "",
				"FLG" => "",
				"SESS_ID" => md5(uniqid()),
				"max_execution_time" => $nsOld["max_execution_time"],
				"stepped" => $nsOld["stepped"],
				"max_file_size" => $nsOld["max_file_size"],
			];

			if (!empty($nsOld["SITE_ID"]))
			{
				$ns["SITE_ID"] = $nsOld["SITE_ID"];
			}
		}
		$ns["CNT"] = intval($ns["CNT"] ?? 0);

		$sites = [];
		$filterRootPath = "";

		$db = SiteTable::getList(
			[
				"select" => ["LID", "DOC_ROOT", "DIR"],
				"filter" => ["=ACTIVE" => "Y"],
			]
		);
		while ($ar = $db->fetch())
		{
			if (empty($ar["DOC_ROOT"]))
			{
				$ar["DOC_ROOT"] = Application::getDocumentRoot();
			}

			$sites[] = [
				"site_id" => $ar["LID"],
				"root" => $ar["DOC_ROOT"],
				"path" => IO\Path::combine($ar["DOC_ROOT"], $ar["DIR"]),
			];

			if (!empty($ns["SITE_ID"]) && $ns["SITE_ID"] == $ar["LID"])
			{
				$filterRootPath = $ar["DOC_ROOT"];
			}
		}

		if (!empty($ns["SITE_ID"]) && !empty($filterRootPath))
		{
			$sitesTmp = [];
			$keys = array_keys($sites);
			foreach ($keys as $key)
			{
				if ($sites[$key]["root"] == $filterRootPath)
				{
					$sitesTmp[] = $sites[$key];
				}
			}
			$sites = $sitesTmp;
		}

		uasort($sites,
			function ($a, $b) {
				$la = mb_strlen($a["path"]);
				$lb = mb_strlen($b["path"]);
				if ($la == $lb)
				{
					if ($a["site_id"] == $b["site_id"])
					{
						return 0;
					}
					else
					{
						return ($a["site_id"] > $b["site_id"]) ? -1 : 1;
					}
				}
				return ($la > $lb) ? -1 : 1;
			}
		);

		if ($ns["CLEAR"] != "Y")
		{
			$alreadyDeleted = [];
			foreach ($sites as $site)
			{
				Component\ParametersTable::deleteBySiteId($site["site_id"]);
				if (!in_array($site["root"], $alreadyDeleted))
				{
					static::delete(
						$site["site_id"],
						["!ID" => ""]
					);
					$alreadyDeleted[] = $site["root"];
				}
			}
		}
		$ns["CLEAR"] = "Y";

		clearstatcache();

		$alreadyParsed = [];
		foreach ($sites as $site)
		{
			if (in_array($site["root"], $alreadyParsed))
			{
				continue;
			}
			$alreadyParsed[] = $site["root"];

			if ($maxExecutionTime > 0 && !empty($ns["FLG"])
				&& mb_substr($ns["ID"] . "/", 0, mb_strlen($site["root"] . "/")) != $site["root"] . "/")
			{
				continue;
			}

			static::recursiveReindex($site["root"], "/", $sites, $maxExecutionTime, $ns);

			if ($maxExecutionTime > 0 && !empty($ns["FLG"]))
			{
				return $ns;
			}
		}

		return $ns["CNT"];
	}

	protected static function recursiveReindex($rootPath, $path, $sites, $maxExecutionTime, &$ns)
	{
		$pathAbs = IO\Path::combine($rootPath, $path);

		$siteId = "";
		foreach ($sites as $site)
		{
			if (str_starts_with($pathAbs . "/", $site["path"] . "/"))
			{
				$siteId = $site["site_id"];
				break;
			}
		}
		if (empty($siteId))
		{
			return 0;
		}

		$dir = new IO\Directory($pathAbs, $siteId);
		if (!$dir->isExists())
		{
			return 0;
		}

		$children = $dir->getChildren();
		foreach ($children as $child)
		{
			if ($child->isDirectory())
			{
				if ($child->isSystem())
				{
					continue;
				}

				//this is not first step, and we had stopped here, so go on to reindex
				if ($maxExecutionTime <= 0
					|| $ns["FLG"] == ''
					|| str_starts_with($ns["ID"] . "/", $child->getPath() . "/")
				)
				{
					if (static::recursiveReindex($rootPath, mb_substr($child->getPath(), mb_strlen($rootPath)), $sites, $maxExecutionTime, $ns) === false)
					{
						return false;
					}
				}
			}
			else
			{
				//not the first step and we found last file from previos one
				if ($maxExecutionTime > 0 && $ns["FLG"] <> ''
					&& $ns["ID"] == $child->getPath())
				{
					$ns["FLG"] = "";
				}
				elseif (empty($ns["FLG"]))
				{
					$ID = static::reindexFile($siteId, $rootPath, mb_substr($child->getPath(), mb_strlen($rootPath)), $ns["max_file_size"]);
					if ($ID)
					{
						$ns["CNT"] = intval($ns["CNT"]) + 1;
					}
				}

				if ($maxExecutionTime > 0
					&& (microtime(true) - START_EXEC_TIME > $maxExecutionTime))
				{
					$ns["FLG"] = "Y";
					$ns["ID"] = $child->getPath();
					return false;
				}
			}
		}
		return true;
	}

	public static function reindexFile($siteId, $rootPath, $path, $maxFileSize = 0)
	{
		$pathAbs = IO\Path::combine($rootPath, $path);

		if (!static::checkPath($pathAbs))
		{
			return 0;
		}

		$file = new IO\File($pathAbs);
		if ($maxFileSize > 0 && $file->getSize() > $maxFileSize * 1024)
		{
			return 0;
		}

		$fileSrc = $file->getContents();

		if (!$fileSrc || $fileSrc == "")
		{
			return 0;
		}

		$components = \PHPParser::parseScript($fileSrc);

		for ($i = 0, $cnt = count($components); $i < $cnt; $i++)
		{
			$sef = (isset($components[$i]["DATA"]["PARAMS"]["SEF_MODE"]) && $components[$i]["DATA"]["PARAMS"]["SEF_MODE"] == "Y");

			Component\ParametersTable::add(
				[
					'SITE_ID' => $siteId,
					'COMPONENT_NAME' => $components[$i]["DATA"]["COMPONENT_NAME"],
					'TEMPLATE_NAME' => $components[$i]["DATA"]["TEMPLATE_NAME"],
					'REAL_PATH' => $path,
					'SEF_MODE' => ($sef ? Component\ParametersTable::SEF_MODE : Component\ParametersTable::NOT_SEF_MODE),
					'SEF_FOLDER' => ($sef ? $components[$i]["DATA"]["PARAMS"]["SEF_FOLDER"] : null),
					'START_CHAR' => $components[$i]["START"],
					'END_CHAR' => $components[$i]["END"],
					'PARAMETERS' => serialize($components[$i]["DATA"]["PARAMS"]),
				]
			);

			if ($sef)
			{
				if (array_key_exists("SEF_RULE", $components[$i]["DATA"]["PARAMS"]))
				{
					$ruleMaker = new UrlRewriterRuleMaker;
					$ruleMaker->process($components[$i]["DATA"]["PARAMS"]["SEF_RULE"]);

					$fields = [
						"CONDITION" => $ruleMaker->getCondition(),
						"RULE" => $ruleMaker->getRule(),
						"ID" => $components[$i]["DATA"]["COMPONENT_NAME"],
						"PATH" => $path,
						"SORT" => self::DEFAULT_SORT,
					];
				}
				else
				{
					$fields = [
						"CONDITION" => "#^" . $components[$i]["DATA"]["PARAMS"]["SEF_FOLDER"] . "#",
						"RULE" => "",
						"ID" => $components[$i]["DATA"]["COMPONENT_NAME"],
						"PATH" => $path,
						"SORT" => self::DEFAULT_SORT,
					];
				}

				static::add($siteId, $fields);
			}
		}

		return true;
	}

	public static function checkPath($path)
	{
		static $searchMasksCache = false;

		if (is_array($searchMasksCache))
		{
			$exclude = $searchMasksCache["exc"];
			$include = $searchMasksCache["inc"];
		}
		else
		{
			$exclude = [];
			$include = [];

			$inc = Config\Option::get("main", "urlrewrite_include_mask", "*.php");
			$inc = str_replace("'", "\\'", str_replace("*", ".*?", str_replace("?", ".", str_replace(".", "\\.", str_replace("\\", "/", $inc)))));
			$incTmp = explode(";", $inc);
			foreach ($incTmp as $pregMask)
			{
				if (trim($pregMask) <> '')
				{
					$include[] = "'^" . trim($pregMask) . "$'";
				}
			}

			$exc = Config\Option::get("main", "urlrewrite_exclude_mask", "/bitrix/*;");
			$exc = str_replace("'", "\\'", str_replace("*", ".*?", str_replace("?", ".", str_replace(".", "\\.", str_replace("\\", "/", $exc)))));
			$excTmp = explode(";", $exc);
			foreach ($excTmp as $pregMask)
			{
				if (trim($pregMask) <> '')
				{
					$exclude[] = "'^" . trim($pregMask) . "$'";
				}
			}

			$searchMasksCache = ["exc" => $exclude, "inc" => $include];
		}

		$file = IO\Path::getName($path);
		if (str_starts_with($file, "."))
		{
			return 0;
		}

		foreach ($exclude as $pregMask)
		{
			if (preg_match($pregMask, $path))
			{
				return false;
			}
		}

		foreach ($include as $pregMask)
		{
			if (preg_match($pregMask, $path))
			{
				return true;
			}
		}

		return false;
	}
}

/**
 * Class UrlRewriterRuleMaker
 *
 * Helper used for sef rules creation.
 *
 * @package Bitrix\Main
 */
class UrlRewriterRuleMaker
{
	protected $condition = "";
	protected $variables = [];
	protected $rule = "";

	/**
	 * @param string $sefRule SEF_RULE component parameter value.
	 *
	 * @return void
	 */
	public function process($sefRule)
	{
		$this->rule = "";
		$this->variables = [];
		$this->condition = "#^" . preg_replace_callback("/(#[a-zA-Z0-9_]+#)/", [$this, "_callback"], $sefRule) . "\\??(.*)#";
		$i = 0;
		foreach ($this->variables as $variableName)
		{
			$i++;
			if ($this->rule)
			{
				$this->rule .= "&";
			}
			$this->rule .= $variableName . "=\$" . $i;
		}
		$i++;
		$this->rule .= "&\$" . $i;
	}

	/**
	 * Returns CONDITION field of the sef rule based on what was processed.
	 *
	 * @return string
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * Returns RULE field of the sef rule based on what was processed.
	 *
	 * @return string
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * Internal method used for preg_replace processing.
	 *
	 * @param array $match match array.
	 *
	 * @return string
	 */
	protected function _callback(array $match)
	{
		$this->variables[] = trim($match[0], "#");
		if (str_ends_with($match[0], "_PATH#"))
		{
			return "(.+?)";
		}
		return "([^/]+?)";
	}
}
