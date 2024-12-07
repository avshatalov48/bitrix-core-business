<?php

namespace Bitrix\Main;

class UrlRewriter
{
	const DEFAULT_SORT = 100;

	protected static function loadRules($siteId)
	{
		$site = SiteTable::getRow(array("filter" => array("=LID" => $siteId), "cache" => array("ttl" => 3600)));
		$docRoot = $site["DOC_ROOT"];

		if (!empty($docRoot))
			$docRoot = IO\Path::normalize($docRoot);
		else
			$docRoot = Application::getDocumentRoot();

		$arUrlRewrite = array();

		if (IO\File::isFileExists($docRoot."/urlrewrite.php"))
			include($docRoot."/urlrewrite.php");

		foreach ($arUrlRewrite as &$rule)
		{
			if (!isset($rule["SORT"]))
				$rule["SORT"] = self::DEFAULT_SORT;
		}

		return $arUrlRewrite;
	}

	protected static function saveRules($siteId, array $arUrlRewrite)
	{
		$site = SiteTable::getRow(array("filter" => array("=LID" => $siteId), "cache" => array("ttl" => 3600)));
		$docRoot = $site["DOC_ROOT"];

		if (!empty($docRoot))
			$docRoot = IO\Path::normalize($docRoot);
		else
			$docRoot = Application::getDocumentRoot();

		$data = var_export($arUrlRewrite, true);

		$event = new Event("main", "onUrlRewriteSaveRules", [
			$siteId,
			$docRoot,
			$arUrlRewrite
		]);
		$event->send();

		$filename = $docRoot . "/urlrewrite.php";
		IO\File::putFileContents($filename, "<"."?php\n\$arUrlRewrite=".$data.";\n");
		Application::resetAccelerator($filename);
	}

	public static function getList($siteId, $arFilter = array(), $arOrder = array())
	{
		if (empty($siteId))
			throw new ArgumentNullException("siteId");

		$arUrlRewrite = static::loadRules($siteId);

		$arResult = array();
		$arResultKeys = self::filterRules($arUrlRewrite, $arFilter);
		foreach ($arResultKeys as $key)
			$arResult[] = $arUrlRewrite[$key];

		if (!empty($arOrder) && !empty($arResult))
		{
			$arOrderKeys = array_keys($arOrder);
			$orderBy = array_shift($arOrderKeys);
			$orderDir = $arOrder[$orderBy];

			$orderBy = mb_strtoupper($orderBy);
			$orderDir = mb_strtoupper($orderDir);

			$orderDir = (($orderDir == "DESC") ? SORT_DESC : SORT_ASC);

			$ar = array();
			foreach ($arResult as $key => $row)
				$ar[$key] = $row[$orderBy];

			array_multisort($ar, $orderDir, $arResult);
		}

		return $arResult;
	}

	protected static function filterRules(array $arUrlRewrite, array $arFilter)
	{
		$arResultKeys = array();

		foreach ($arUrlRewrite as $keyRule => $arRule)
		{
			$isMatched = true;
			foreach ($arFilter as $keyFilter => $valueFilter)
			{
				$isNegative = false;
				if (str_starts_with($keyFilter, "!"))
				{
					$isNegative = true;
					$keyFilter = mb_substr($keyFilter, 1);
				}

				if ($keyFilter === 'QUERY')
					$isMatchedTmp = preg_match($arRule["CONDITION"], $valueFilter);
				elseif ($keyFilter === 'CONDITION')
					$isMatchedTmp = ($arRule["CONDITION"] == $valueFilter);
				elseif ($keyFilter === 'ID')
					$isMatchedTmp = (isset($arRule["ID"]) && ($arRule["ID"] == $valueFilter));
				elseif ($keyFilter === 'PATH')
					$isMatchedTmp = ($arRule["PATH"] == $valueFilter);
				else
					throw new ArgumentException("arFilter");

				$isMatched = ($isNegative xor $isMatchedTmp);

				if (!$isMatched)
					break;
			}

			if ($isMatched)
				$arResultKeys[] = $keyRule;
		}

		return $arResultKeys;
	}

	protected static function recordsCompare($a, $b)
	{
		$sortA = isset($a["SORT"]) ? intval($a["SORT"]) : self::DEFAULT_SORT;
		$sortB = isset($b["SORT"]) ? intval($b["SORT"]) : self::DEFAULT_SORT;

		if ($sortA > $sortB)
			return 1;
		elseif ($sortA < $sortB)
			return -1;

		$lenA = mb_strlen($a["CONDITION"]);
		$lenB = mb_strlen($b["CONDITION"]);
		if ($lenA < $lenB)
			return 1;
		elseif ($lenA > $lenB)
			return -1;
		else
			return 0;
	}

	public static function add($siteId, $arFields)
	{
		if (empty($siteId))
			throw new ArgumentNullException("siteId");

		$arUrlRewrite = static::loadRules($siteId);

		// if rule is exist – return
		foreach ($arUrlRewrite as $rule)
		{
			if ($arFields["CONDITION"] == $rule["CONDITION"])
			{
				return;
			}
		}

		$arUrlRewrite[] = array(
			"CONDITION" => $arFields["CONDITION"],
			"RULE" => $arFields["RULE"],
			"ID" => $arFields["ID"],
			"PATH" => $arFields["PATH"],
			"SORT" => isset($arFields["SORT"]) ? intval($arFields["SORT"]) : self::DEFAULT_SORT,
		);

		uasort($arUrlRewrite, array('\Bitrix\Main\UrlRewriter', "recordsCompare"));

		static::saveRules($siteId, $arUrlRewrite);
	}

	public static function update($siteId, $arFilter, $arFields)
	{
		if (empty($siteId))
			throw new ArgumentNullException("siteId");

		$arUrlRewrite = static::loadRules($siteId);

		$arResultKeys = self::filterRules($arUrlRewrite, $arFilter);
		foreach ($arResultKeys as $key)
		{
			if (array_key_exists("CONDITION", $arFields))
				$arUrlRewrite[$key]["CONDITION"] = $arFields["CONDITION"];
			if (array_key_exists("RULE", $arFields))
				$arUrlRewrite[$key]["RULE"] = $arFields["RULE"];
			if (array_key_exists("ID", $arFields))
				$arUrlRewrite[$key]["ID"] = $arFields["ID"];
			if (array_key_exists("PATH", $arFields))
				$arUrlRewrite[$key]["PATH"] = $arFields["PATH"];
			if (array_key_exists("SORT", $arFields))
				$arUrlRewrite[$key]["SORT"] = intval($arFields["SORT"]);
		}

		uasort($arUrlRewrite, array('\Bitrix\Main\UrlRewriter', "recordsCompare"));

		static::saveRules($siteId, $arUrlRewrite);
	}

	public static function delete($siteId, $arFilter)
	{
		if (empty($siteId))
			throw new ArgumentNullException("siteId");

		$arUrlRewrite = static::loadRules($siteId);

		$arResultKeys = self::filterRules($arUrlRewrite, $arFilter);
		foreach ($arResultKeys as $key)
			unset($arUrlRewrite[$key]);

		uasort($arUrlRewrite, array('\Bitrix\Main\UrlRewriter', "recordsCompare"));

		static::saveRules($siteId, $arUrlRewrite);
	}

	public static function reindexAll($maxExecutionTime = 0, $ns = array())
	{
		@set_time_limit(0);
		if (!is_array($ns))
			$ns = array();

		if ($maxExecutionTime <= 0)
		{
			$nsOld = $ns;
			$ns = array(
				"CLEAR" => "N",
				"ID" => "",
				"FLG" => "",
				"SESS_ID" => md5(uniqid("")),
				"max_execution_time" => $nsOld["max_execution_time"],
				"stepped" => $nsOld["stepped"],
				"max_file_size" => $nsOld["max_file_size"]
			);

			if (!empty($nsOld["SITE_ID"]))
				$ns["SITE_ID"] = $nsOld["SITE_ID"];
		}
		$ns["CNT"] = intval($ns["CNT"] ?? 0);

		$arSites = array();
		$filterRootPath = "";

		$db = SiteTable::getList(
			array(
				"select" => array("LID", "DOC_ROOT", "DIR"),
				"filter" => array("=ACTIVE" => "Y"),
			)
		);
		while ($ar = $db->fetch())
		{
			if (empty($ar["DOC_ROOT"]))
				$ar["DOC_ROOT"] = Application::getDocumentRoot();

			$arSites[] = array(
				"site_id" => $ar["LID"],
				"root" => $ar["DOC_ROOT"],
				"path" => IO\Path::combine($ar["DOC_ROOT"], $ar["DIR"])
			);

			if (!empty($ns["SITE_ID"]) && $ns["SITE_ID"] == $ar["LID"])
				$filterRootPath = $ar["DOC_ROOT"];
		}

		if (!empty($ns["SITE_ID"]) && !empty($filterRootPath))
		{
			$arSitesTmp = array();
			$arKeys = array_keys($arSites);
			foreach ($arKeys as $key)
			{
				if ($arSites[$key]["root"] == $filterRootPath)
					$arSitesTmp[] = $arSites[$key];
			}
			$arSites = $arSitesTmp;
		}

		uasort($arSites,
			function($a, $b)
			{
				$la = mb_strlen($a["path"]);
				$lb = mb_strlen($b["path"]);
				if ($la == $lb)
				{
					if ($a["site_id"] == $b["site_id"])
						return 0;
					else
						return ($a["site_id"] > $b["site_id"]) ? -1 : 1;
				}
				return ($la > $lb) ? -1 : 1;
			}
		);

		if ($ns["CLEAR"] != "Y")
		{
			$arAlreadyDeleted = array();
			foreach ($arSites as $site)
			{
				Component\ParametersTable::deleteBySiteId($site["site_id"]);
				if (!in_array($site["root"], $arAlreadyDeleted))
				{
					static::delete(
						$site["site_id"],
						array("!ID" => "")
					);
					$arAlreadyDeleted[] = $site["root"];
				}
			}
		}
		$ns["CLEAR"] = "Y";

		clearstatcache();

		$arAlreadyParsed = array();
		foreach ($arSites as $site)
		{
			if (in_array($site["root"], $arAlreadyParsed))
				continue;
			$arAlreadyParsed[] = $site["root"];

			if ($maxExecutionTime > 0 && !empty($ns["FLG"])
				&& mb_substr($ns["ID"]."/", 0, mb_strlen($site["root"]."/")) != $site["root"]."/")
			{
				continue;
			}

			static::recursiveReindex($site["root"], "/", $arSites, $maxExecutionTime, $ns);

			if ($maxExecutionTime > 0 && !empty($ns["FLG"]))
				return $ns;
		}

		return $ns["CNT"];
	}

	protected static function recursiveReindex($rootPath, $path, $arSites, $maxExecutionTime, &$ns)
	{
		$pathAbs = IO\Path::combine($rootPath, $path);

		$siteId = "";
		foreach ($arSites as $site)
		{
			if (str_starts_with($pathAbs . "/", $site["path"] . "/"))
			{
				$siteId = $site["site_id"];
				break;
			}
		}
		if (empty($siteId))
			return 0;

		$dir = new IO\Directory($pathAbs, $siteId);
		if (!$dir->isExists())
			return 0;

		$arChildren = $dir->getChildren();
		foreach ($arChildren as $child)
		{
			if ($child->isDirectory())
			{
				if ($child->isSystem())
					continue;

				//this is not first step and we had stopped here, so go on to reindex
				if ($maxExecutionTime <= 0
					|| $ns["FLG"] == ''
					|| str_starts_with($ns["ID"] . "/", $child->getPath() . "/")
				)
				{
					if (static::recursiveReindex($rootPath, mb_substr($child->getPath(), mb_strlen($rootPath)), $arSites, $maxExecutionTime, $ns) === false)
						return false;
				}
				else //all done
				{
					continue;
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
						$ns["CNT"] = intval($ns["CNT"]) + 1;
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
			return 0;

		$file = new IO\File($pathAbs);
		if ($maxFileSize > 0 && $file->getSize() > $maxFileSize * 1024)
			return 0;

		$fileSrc = $file->getContents();

		if (!$fileSrc || $fileSrc == "")
			return 0;

		$arComponents = \PHPParser::parseScript($fileSrc);

		for ($i = 0, $cnt = count($arComponents); $i < $cnt; $i++)
		{
			$sef = (is_array($arComponents[$i]["DATA"]["PARAMS"]) && $arComponents[$i]["DATA"]["PARAMS"]["SEF_MODE"] == "Y");

			Component\ParametersTable::add(
				array(
					'SITE_ID' => $siteId,
					'COMPONENT_NAME' => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
					'TEMPLATE_NAME' => $arComponents[$i]["DATA"]["TEMPLATE_NAME"],
					'REAL_PATH' => $path,
					'SEF_MODE' => ($sef? Component\ParametersTable::SEF_MODE : Component\ParametersTable::NOT_SEF_MODE),
					'SEF_FOLDER' => ($sef? $arComponents[$i]["DATA"]["PARAMS"]["SEF_FOLDER"] : null),
					'START_CHAR' => $arComponents[$i]["START"],
					'END_CHAR' => $arComponents[$i]["END"],
					'PARAMETERS' => serialize($arComponents[$i]["DATA"]["PARAMS"]),
				)
			);

			if ($sef)
			{
				if (array_key_exists("SEF_RULE", $arComponents[$i]["DATA"]["PARAMS"]))
				{
					$ruleMaker = new UrlRewriterRuleMaker;
					$ruleMaker->process($arComponents[$i]["DATA"]["PARAMS"]["SEF_RULE"]);

					$arFields = array(
						"CONDITION" => $ruleMaker->getCondition(),
						"RULE" => $ruleMaker->getRule(),
						"ID" => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
						"PATH" => $path,
						"SORT" => self::DEFAULT_SORT,
					);
				}
				else
				{
					$arFields = array(
						"CONDITION" => "#^".$arComponents[$i]["DATA"]["PARAMS"]["SEF_FOLDER"]."#",
						"RULE" => "",
						"ID" => $arComponents[$i]["DATA"]["COMPONENT_NAME"],
						"PATH" => $path,
						"SORT" => self::DEFAULT_SORT,
					);
				}

				static::add($siteId, $arFields);
			}
		}

		return true;
	}

	public static function checkPath($path)
	{
		static $searchMasksCache = false;
		if (is_array($searchMasksCache))
		{
			$arExc = $searchMasksCache["exc"];
			$arInc = $searchMasksCache["inc"];
		}
		else
		{
			$arExc = array();
			$arInc = array();

			$inc = Config\Option::get("main", "urlrewrite_include_mask", "*.php");
			$inc = str_replace("'", "\\'", str_replace("*", ".*?", str_replace("?", ".", str_replace(".", "\\.", str_replace("\\", "/", $inc)))));
			$arIncTmp = explode(";", $inc);
			foreach ($arIncTmp as $preg_mask)
				if (trim($preg_mask) <> '')
					$arInc[] = "'^".trim($preg_mask)."$'";

			$exc = Config\Option::get("main", "urlrewrite_exclude_mask", "/bitrix/*;");
			$exc = str_replace("'", "\\'", str_replace("*", ".*?", str_replace("?", ".", str_replace(".", "\\.", str_replace("\\", "/", $exc)))));
			$arExcTmp = explode(";", $exc);
			foreach ($arExcTmp as $preg_mask)
				if (trim($preg_mask) <> '')
					$arExc[] = "'^".trim($preg_mask)."$'";

			$searchMasksCache = array("exc" => $arExc, "inc" => $arInc);
		}

		$file = IO\Path::getName($path);
		if (str_starts_with($file, "."))
			return 0;

		foreach ($arExc as $preg_mask)
			if (preg_match($preg_mask, $path))
				return false;

		foreach ($arInc as $preg_mask)
			if (preg_match($preg_mask, $path))
				return true;

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
	protected $variables = array();
	protected $rule = "";

	/**
	 * @param string $sefRule SEF_RULE component parameter value.
	 *
	 * @return void
	 */
	public function process($sefRule)
	{
		$this->rule = "";
		$this->variables = array();
		$this->condition = "#^".preg_replace_callback("/(#[a-zA-Z0-9_]+#)/", array($this, "_callback"), $sefRule)."\\??(.*)#";
		$i = 0;
		foreach ($this->variables as $variableName)
		{
			$i++;
			if ($this->rule)
				$this->rule .= "&";
			$this->rule .= $variableName."=\$".$i;
		}
		$i++;
		$this->rule .= "&\$".$i;
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