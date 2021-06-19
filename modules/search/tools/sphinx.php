<?
IncludeModuleLangFile(__FILE__);

class CSearchSphinx extends CSearchFullText
{
	public $arForumTopics = array();
	public $db = false;
	private static $fields = array(
		"title" => "field",
		"body" => "field",
		"module_id" => "uint",
		"module" => "string",
		"item_id" => "uint",
		"item" => "string",
		"param1_id" => "uint",
		"param1" => "string",
		"param2_id" => "uint",
		"param2" => "string",
		"date_change" => "timestamp",
		"date_from" => "timestamp",
		"date_to" => "timestamp",
		"custom_rank" => "uint",
		"tags" => "mva",
		"right" => "mva",
		"site" => "mva",
		"param" => "mva",
	);
	private static $typesMap = array(
		"timestamp" => "rt_attr_timestamp",
		"string" => "rt_attr_string",
		"bigint" => "rt_attr_bigint",
		"uint" => "rt_attr_uint",
		"field" => "rt_field",
		"mva" => "rt_attr_multi",
	);
	private $errorText = "";
	private $errorNumber = 0;
	private $recodeToUtf = false;
	public $tags = "";
	public $query = "";
	public $SITE_ID = "";
	public $connectionIndex = "";
	public $indexName = "";

	public function connect($connectionIndex, $indexName = "", $ignoreErrors = false)
	{
		global $APPLICATION;

		if (!preg_match("/^[a-zA-Z0-9_]+\$/", $indexName))
		{
			if ($ignoreErrors)
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_ERROR_INDEX_NAME"));
			else
				throw new \Bitrix\Main\Db\ConnectionException('Sphinx connect error', GetMessage("SEARCH_SPHINX_CONN_ERROR_INDEX_NAME"));
			return false;
		}

		if (!$this->canConnect())
		{
			if ($ignoreErrors)
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_EXT_IS_MISSING"));
			else
				throw new \Bitrix\Main\Db\ConnectionException('Sphinx connect error', GetMessage("SEARCH_SPHINX_CONN_EXT_IS_MISSING"));
			return false;
		}

		$error = "";
		$this->db = $this->internalConnect($connectionIndex, $error);
		if (!$this->db)
		{
			if ($ignoreErrors)
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_ERROR", array("#ERRSTR#" => $error)));
			else
				throw new \Bitrix\Main\Db\ConnectionException('Sphinx connect error', GetMessage("SEARCH_SPHINX_CONN_ERROR", array("#ERRSTR#" => $error)));
			return false;
		}

		if ($ignoreErrors)
		{
			$result = $this->query("SHOW TABLES");
			if (!$result)
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_ERROR", array("#ERRSTR#" => $this->getError())));
				return false;
			}

			if ($indexName == "")
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_NO_INDEX"));
				return false;
			}

			$indexType = "";
			while($res = $this->fetch($result))
			{
				if ($indexName === $res["Index"])
					$indexType = $res["Type"];
			}

			if ($indexType == "")
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_INDEX_NOT_FOUND"));
				return false;
			}

			if ($indexType != "rt")
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_CONN_INDEX_WRONG_TYPE"));
				return false;
			}

			$indexColumns = array();
			$result = $this->query("DESCRIBE `".$indexName."`");
			if (!$result)
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_DESCR_ERROR", array("#ERRSTR#" => $this->getError())));
				return false;
			}

			while($res = $this->fetch($result))
			{
				$indexColumns[$res["Field"]] = $res["Type"];
			}

			$missed = array();
			foreach (self::$fields as $name => $type)
			{
				if (!isset($indexColumns[$name]) || $indexColumns[$name] !== $type)
				{
					$missed[] = self::$typesMap[$type]." = ".$name;
				}
			}

			if (!empty($missed))
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_SPHINX_NO_FIELDS", array("#FIELD_LIST#" => implode(", ", $missed))));
				return false;
			}
		}

		$this->indexName = $indexName;
		$this->connectionIndex = $connectionIndex;

		//2.2.1 version test (they added HAVING support and moved to UTF8)
		if (
			!defined("BX_UTF")
			&& $this->query("select id from ".$this->indexName." where id=1 group by id having count(*) = 1")
		)
		{
			$this->recodeToUtf = true;
		}

		return true;
	}

	public function truncate()
	{
		$this->query("truncate rtindex ".$this->indexName);
		$this->connect($this->connectionIndex, $this->indexName);
	}

	public function deleteById($ID)
	{
		$this->query("delete from ".$this->indexName." where id = ".intval($ID));
	}

	public function recodeTo($text)
	{
		if ($this->recodeToUtf)
		{
			$error = "";
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($text, SITE_CHARSET, "UTF-8", $error);
			if (!$result && !empty($error))
				#$this->ThrowException($error, "ERR_CHAR_BX_CONVERT");
				return $text;

			return $result;
		}
		else
		{
			return $text;
		}
	}

	public function recodeFrom($text)
	{
		if ($this->recodeToUtf)
		{
			$error = "";
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($text, "UTF-8", SITE_CHARSET, $error);
			if (!$result && !empty($error))
				#$this->ThrowException($error, "ERR_CHAR_BX_CONVERT");
				return $text;

			return $result;
		}
		else
		{
			return $text;
		}
	}

	public function replace($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if(array_key_exists("~DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
			unset($arFields["~DATE_CHANGE"]);
		}
		elseif(array_key_exists("LAST_MODIFIED", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
			unset($arFields["LAST_MODIFIED"]);
		}
		elseif(array_key_exists("DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
		}

		$DATE_FROM = intval(MakeTimeStamp($arFields["DATE_FROM"]));
		if ($DATE_FROM > 0)
			$DATE_FROM -= CTimeZone::GetOffset();
		$DATE_TO = intval(MakeTimeStamp($arFields["DATE_TO"]));
		if($DATE_TO > 0)
			$DATE_TO -= CTimeZone::GetOffset();
		$DATE_CHANGE = intval(MakeTimeStamp($arFields["DATE_CHANGE"]));
		if($DATE_CHANGE > 0)
			$DATE_CHANGE -= CTimeZone::GetOffset();

		$BODY = CSearch::KillEntities($arFields["BODY"])."\r\n".$arFields["TAGS"];

		$sql = "
			REPLACE INTO ".$this->indexName." (
				id
				,module_id
				,module
				,item_id
				,item
				,param1_id
				,param1
				,param2_id
				,param2
				,date_change
				,date_from
				,date_to
				,custom_rank
				,tags
				,right
				,site
				,param
				,title
				,body
			) VALUES (
				$ID
				,".sprintf("%u", crc32($arFields["MODULE_ID"]))."
				,'".$this->Escape($arFields["MODULE_ID"])."'
				,".sprintf("%u", crc32($arFields["ITEM_ID"]))."
				,'".$this->Escape($arFields["ITEM_ID"])."'
				,".sprintf("%u", crc32($arFields["PARAM1"]))."
				,'".$this->Escape($arFields["PARAM1"])."'
				,".sprintf("%u", crc32($arFields["PARAM2"]))."
				,'".$this->Escape($arFields["PARAM2"])."'
				,".$DATE_CHANGE."
				,".$DATE_FROM."
				,".$DATE_TO."
				,".intval($arFields["CUSTOM_RANK"])."
				,(".$this->tags($arFields["SITE_ID"], $arFields["TAGS"]).")
				,(".$this->rights($arFields["PERMISSIONS"]).")
				,(".$this->sites($arFields["SITE_ID"]).")
				,(".$this->params($arFields["PARAMS"]).")
				,'".$this->recodeTo($this->Escape($arFields["TITLE"]))."'
				,'".$this->recodeTo($this->Escape($BODY))."'
			)
		";
		$result = $this->query($sql);
		if ($result)
		{
			$this->tagsRegister($arFields["SITE_ID"], $arFields["TAGS"]);
		}
		else
		{
			throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', $this->getError(), $sql);
		}
	}

	public function update($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		$arUpdate = array();
		$bReplace = false;

		if (array_key_exists("TITLE", $arFields))
		{
			$bReplace = true;
		}

		if (array_key_exists("BODY", $arFields))
		{
			$bReplace = true;
		}

		if (array_key_exists("MODULE_ID", $arFields))
		{
			$bReplace = true;
		}

		if (array_key_exists("ITEM_ID", $arFields))
		{
			$bReplace = true;
		}

		if (array_key_exists("PARAM1", $arFields))
		{
			$bReplace = true;
		}

		if (array_key_exists("PARAM2", $arFields))
		{
			$bReplace = true;
		}

		if(array_key_exists("~DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
			unset($arFields["~DATE_CHANGE"]);
		}
		elseif(array_key_exists("LAST_MODIFIED", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
			unset($arFields["LAST_MODIFIED"]);
		}
		elseif(array_key_exists("DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
		}

		if (array_key_exists("DATE_CHANGE", $arFields))
		{
			$DATE_CHANGE = intval(MakeTimeStamp($arFields["DATE_CHANGE"]));
			if($DATE_CHANGE > 0)
				$DATE_CHANGE -= CTimeZone::GetOffset();
			$arUpdate["date_change"] = $DATE_CHANGE;
		}

		if (array_key_exists("DATE_FROM", $arFields))
		{
			$DATE_FROM = intval(MakeTimeStamp($arFields["DATE_FROM"]));
			if ($DATE_FROM > 0)
				$DATE_FROM -= CTimeZone::GetOffset();
			$arUpdate["date_from"] = $DATE_FROM;
		}

		if (array_key_exists("DATE_TO", $arFields))
		{
			$DATE_TO = intval(MakeTimeStamp($arFields["DATE_TO"]));
			if($DATE_TO > 0)
				$DATE_TO -= CTimeZone::GetOffset();
			$arUpdate["date_to"] = $DATE_TO;
		}

		if (array_key_exists("CUSTOM_RANK", $arFields))
		{
			$arUpdate["custom_rank"] = "".intval($arFields["CUSTOM_RANK"])."";
		}

		if (array_key_exists("TAGS", $arFields))
		{
			$arUpdate["tags"] = "(".$this->tags($arFields["SITE_ID"], $arFields["TAGS"]).")";
		}

		if (array_key_exists("PERMISSIONS", $arFields))
		{
			$arUpdate["right"] = "(".$this->rights($arFields["PERMISSIONS"]).")";
		}

		if (array_key_exists("SITE_ID", $arFields))
		{
			$arUpdate["site"] = "(".$this->sites($arFields["SITE_ID"]).")";
		}

		if (array_key_exists("PARAMS", $arFields))
		{
			$arUpdate["param"] = "(".$this->params($arFields["PARAMS"]).")";
		}

		if (!empty($arUpdate) && !$bReplace)
		{
			foreach ($arUpdate as $columnName => $sqlValue)
				$arUpdate[$columnName] = $columnName."=".$sqlValue;

			$this->query("
				UPDATE ".$this->indexName." SET
				".implode(", ", $arUpdate)."
				WHERE id = $ID
				"
			);
		}
		elseif ($bReplace)
		{
			$dbItem = $DB->Query("SELECT * FROM b_search_content WHERE ID = ".$ID);
			$searchItem = $dbItem->fetch();
			if ($searchItem)
			{
				$dbTags = $DB->Query("SELECT * from b_search_tags WHERE SEARCH_CONTENT_ID=".$ID);
				while($tag = $dbTags->fetch())
					$searchItem["TAGS"] .= $tag["NAME"].",";

				$dbRights = $DB->Query("SELECT * from b_search_content_right WHERE SEARCH_CONTENT_ID=".$ID);
				while($right = $dbRights->fetch())
					$searchItem["PERMISSIONS"][] = $right["GROUP_CODE"];

				$dbSites = $DB->Query("SELECT * from b_search_content_site WHERE SEARCH_CONTENT_ID=".$ID);
				while($site = $dbSites->fetch())
					$searchItem["SITE_ID"][$site["SITE_ID"]] = $site["URL"];

				$dbParams = $DB->Query("SELECT * from b_search_content_param WHERE SEARCH_CONTENT_ID=".$ID);
				while($param = $dbParams->fetch())
					$searchItem["PARAMS"][$param["PARAM_NAME"]][] = $param["PARAM_VALUE"];

				$this->replace($ID, $searchItem);
			}
		}
	}

	function tags($arLID, $sContent)
	{
		$tags = array();
		if(is_array($arLID))
		{
			foreach($arLID as $site_id => $url)
			{
				$arTags = tags_prepare($sContent, $site_id);
				foreach($arTags as $tag)
				{
					$tags[] = sprintf("%u", crc32($tag));
				}
			}
		}
		return implode(",", $tags);
	}

	function tagsRegister($arLID, $sContent)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $tagMap = array();

		if(is_array($arLID))
		{
			foreach($arLID as $site_id => $url)
			{
				$arTags = tags_prepare($sContent, $site_id);
				foreach($arTags as $tag)
				{
					$tag_id = sprintf("%u", crc32($tag));
					if($tag_id > 0x7FFFFFFF)
						$tag_id = -(0xFFFFFFFF - $tag_id + 1);

					if (!isset($tagMap[$tag_id]))
					{
						$rs = $DB->Query("select * from b_search_tags where SEARCH_CONTENT_ID=".$tag_id." AND SITE_ID='??'");
						$tagMap[$tag_id] = $rs->fetch();
						if (!$tagMap[$tag_id])
						{
							$DB->Query("insert into b_search_tags values (
								$tag_id, '??', '".$DB->ForSql($tag)."'
							)"
							);
						}
					}
				}
			}
		}
	}

	function tagsFromArray($arTags)
	{
		$tags = array();
		if(is_array($arTags))
		{
			foreach($arTags as $tag)
			{
				$tags[] = sprintf("%u", crc32($tag));
			}
		}
		return implode(",", $tags);
	}

	function rights($arRights)
	{
		$rights = array();
		if (is_array($arRights))
		{
			foreach ($arRights as $group_id)
			{
				if(is_numeric($group_id))
					$rights[$group_id] = sprintf("%u", crc32("G".intval($group_id)));
				else
					$rights[$group_id] = sprintf("%u", crc32($group_id));
			}
		}
		return implode(",", $rights);
	}

	function sites($arSites)
	{
		$sites = array();
		if (is_array($arSites))
		{
			foreach ($arSites as $site_id => $url)
			{
				$sites[$site_id] = sprintf("%u", crc32($site_id));
			}
		}
		else
		{
			$sites[$arSites] = sprintf("%u", crc32($arSites));
		}
		return implode(",", $sites);
	}

	function params($arParams)
	{
		$params = array();
		if(is_array($arParams))
		{
			foreach($arParams as $k1 => $v1)
			{
				$name = trim($k1);
				if($name != "")
				{
					if(!is_array($v1))
						$v1 = array($v1);

					foreach($v1 as $v2)
					{
						$value = trim($v2);
						if($value != "")
						{
							$params[] = sprintf("%u", crc32(urlencode($name)."=".urlencode($value)));
						}
					}
				}
			}
		}
		return implode(",", $params);
	}

	public function getErrorText()
	{
		return $this->errorText;
	}
	public function getErrorNumber()
	{
		return $this->errorNumber;
	}

	public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
	{
		$result = array();
		$this->errorText = "";
		$this->errorNumber = 0;

		$this->tags = trim($arParams["TAGS"]);

		$limit = 0;
		if (is_array($aParamsEx) && isset($aParamsEx["LIMIT"]))
		{
			$limit = intval($aParamsEx["LIMIT"]);
			unset($aParamsEx["LIMIT"]);
		}

		$offset = 0;
		if (is_array($aParamsEx) && isset($aParamsEx["OFFSET"]))
		{
			$offset = intval($aParamsEx["OFFSET"]);
			unset($aParamsEx["OFFSET"]);
		}

		if (is_array($aParamsEx) && !empty($aParamsEx))
		{
			$aParamsEx["LOGIC"] = "OR";
			$arParams[] = $aParamsEx;
		}

		$this->SITE_ID = $arParams["SITE_ID"];

		$arWhere = array();
		$cond1 = implode("\n\t\t\t\t\t\tand ", $this->prepareFilter($arParams, true));

		$rights = $this->CheckPermissions();
		if ($rights)
			$arWhere[] = "right in (".$rights.")";

		$strQuery = trim($arParams["QUERY"]);
		if ($strQuery != "")
		{
			$arWhere[] = "MATCH('".$this->recodeTo($this->Escape($strQuery))."')";
			$this->query = $strQuery;
		}

		if ($cond1 != "")
			$arWhere[] = "cond1 = 1";

		if ($strQuery || $this->tags || $bTagsCloud)
		{
			if ($limit <= 0)
			{
				$limit = intval(COption::GetOptionInt("search", "max_result_size"));
			}

			if ($limit <= 0)
			{
				$limit = 500;
			}

			$ts = time()-CTimeZone::GetOffset();
			if ($bTagsCloud)
			{
				$sql = "
					select groupby() tag_id
					,count(*) cnt
					,max(date_change) dc_tmp
					,if(date_to, date_to, ".$ts.") date_to_nvl
					,if(date_from, date_from, ".$ts.") date_from_nvl
					".($cond1 != ""? ",$cond1 as cond1": "")."
					from ".$this->indexName."
					where ".implode("\nand\t", $arWhere)."
					group by tags
					order by cnt desc
					limit 0, ".$limit."
					option max_matches = ".$limit."
				";

				$DB = CDatabase::GetModuleConnection('search');
				$startTime = microtime(true);

				$r =  $this->query($sql);

				if($DB->ShowSqlStat)
					$DB->addDebugQuery($sql, microtime(true)-$startTime);

				if (!$r)
				{
					throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', $this->getError(), $sql);
				}
				else
				{
					while($res = $this->fetch($r))
						$result[] = $res;
				}
			}
			else
			{
				$sql = "
					select id
					,item
					,param1
					,param2
					,module_id
					,param2_id
					,date_change
					,custom_rank
					,weight() as rank
					".($cond1 != ""? ",$cond1 as cond1": "")."
					,if(date_to, date_to, ".$ts.") date_to_nvl
					,if(date_from, date_from, ".$ts.") date_from_nvl
					from ".$this->indexName."
					where ".implode("\nand\t", $arWhere)."
					".$this->__PrepareSort($aSort)."
					limit ".$offset.", ".$limit."
					option max_matches = ".($offset + $limit)."
				";

				$DB = CDatabase::GetModuleConnection('search');
				$startTime = microtime(true);

				$r =  $this->query($sql);

				if($DB->ShowSqlStat)
					$DB->addDebugQuery($sql, microtime(true)-$startTime);

				if (!$r)
				{
					throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', $this->getError(), $sql);
				}
				else
				{
					$forum = sprintf("%u", crc32("forum"));
					while($res = $this->fetch($r))
					{
						if($res["module_id"] == $forum)
						{
							if (array_key_exists($res["param2_id"], $this->arForumTopics))
								continue;
							$this->arForumTopics[$res["param2_id"]] = true;
						}
						$result[] = $res;
					}
				}
			}
		}
		else
		{
			$this->errorText = GetMessage("SEARCH_ERROR3");
			$this->errorNumber = 3;
		}

		return $result;
	}

	function searchTitle($phrase = "", $arPhrase = array(), $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
		$sqlWords = array();
		foreach(array_reverse($arPhrase, true) as $word => $pos)
		{
			$word = $this->Escape($word);
			if(empty($sqlWords) && !preg_match("/[\\n\\r \\t]$/", $phrase))
				$sqlWords[] = $word."*";
			else
				$sqlWords[] = $word;
		}
		$match = '@title '.implode(' ', array_reverse($sqlWords));

		$checkDates = false;
		if (array_key_exists("CHECK_DATES", $arParams))
		{
			if($arParams["CHECK_DATES"] == "Y")
			{
				$checkDates = true;
			}
			unset($arParams["CHECK_DATES"]);
		}

		$arWhere = $this->prepareFilter($arParams);

		$cond1 = "";
		if (isset($arWhere["cond1"]))
		{
			$cond1 = $arWhere["cond1"];
			unset($arWhere["cond1"]);
		}

		$ts = time()-CTimeZone::GetOffset();
		if ($checkDates)
		{
			$arWhere[] = "date_from_nvl <= ".$ts;
			$arWhere[] = "date_to_nvl >= ".$ts;
		}

		$rights = $this->CheckPermissions();
		if ($rights)
			$arWhere[] = "right in (".$rights.")";

		$arWhere[] = "site = ".sprintf("%u", crc32(SITE_ID));
		$arWhere[] = "match('".$this->recodeTo($match)."')";

		$sql = "
			select id
			,weight() as rank
			".($cond1 != ""? ",$cond1 as cond1": "")."
			,if(date_to, date_to, ".$ts.") date_to_nvl
			,if(date_from, date_from, ".$ts.") date_from_nvl
			from ".$this->indexName."
			where ".implode("\nand\t", $arWhere)."
			".($cond1 != ""? " and cond1 = ".intval(!$bNotFilter): "")."
			".$this->__PrepareSort($order)."
			limit 0, ".$nTopCount."
			option max_matches = ".$nTopCount."
		";

		$DB = CDatabase::GetModuleConnection('search');
		$startTime = microtime(true);

		$r =  $this->query($sql);

		if($DB->ShowSqlStat)
			$DB->addDebugQuery($sql, microtime(true)-$startTime);

		if (!$r)
		{
			throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', $this->getError(), $sql);
		}
		else
		{
			$result = array();
			while($res = $this->fetch($r))
			{
				$result[] = $res["id"];
			}
			return $result;
		}
	}

	function getRowFormatter()
	{
		return new CSearchSphinxFormatter($this);
	}

	function filterField($field, $value, $inSelect)
	{
		$arWhere = array();
		if(is_array($value))
		{
			if (!empty($value))
			{
				$s = "";
				if ($inSelect)
				{
					foreach ($value as $i => $v)
						$s .= ",".sprintf("%u", crc32($v));
					$arWhere[] = "in($field $s)";
				}
				else
				{
					foreach ($value as $i => $v)
						$s .= ($s? " or ": "")."$field = ".sprintf("%u", crc32($v));
					$arWhere[] = "($s)";
				}
			}
		}
		else
		{
			if($value !== false)
				$arWhere[] = "$field = ".sprintf("%u", crc32($value));
		}
		return $arWhere;
	}

	function prepareFilter($arFilter, $inSelect = false)
	{
		$arWhere = array();
		if (!is_array($arFilter))
			$arFilter = array();

		$orLogic = false;
		if (array_key_exists("LOGIC", $arFilter))
		{
			$orLogic = ($arFilter["LOGIC"] == "OR");
			unset($arFilter["LOGIC"]);
		}

		foreach($arFilter as $field=>$val)
		{
			$field = mb_strtoupper($field);
			if(
				is_array($val)
				&& count($val) == 1
				&& $field !== "URL"
				&& $field !== "PARAMS"
				&& !is_numeric($field)
			)
				$val = $val[0];

			switch($field)
			{
			case "ITEM_ID":
			case "=ITEM_ID":
				$arWhere = array_merge($arWhere, $this->filterField("item_id", $val, $inSelect));
				break;
			case "!ITEM_ID":
				if($val !== false)
					$arWhere[] = "item_id <> ".sprintf("%u", crc32($val));
				break;
			case "MODULE_ID":
			case "=MODULE_ID":
				if($val !== false && $val !== "no")
					$arWhere[] = "module_id = ".sprintf("%u", crc32($val));
				break;
			case "PARAM1":
			case "=PARAM1":
				$arWhere = array_merge($arWhere, $this->filterField("param1_id", $val, $inSelect));
				break;
			case "!PARAM1":
			case "!=PARAM1":
				if($val !== false)
					$arWhere[] = "param1_id <> ".sprintf("%u", crc32($val));
				break;
			case "PARAM2":
			case "=PARAM2":
				$arWhere = array_merge($arWhere, $this->filterField("param2_id", $val, $inSelect));
				break;
			case "!PARAM2":
			case "!=PARAM2":
				if($val !== false)
					$arWhere[] = "param2_id <> ".sprintf("%u", crc32($val));
				break;
			case "DATE_CHANGE":
				if($val <> '')
					$arWhere[] = "date_change >= ".intval(MakeTimeStamp($val)-CTimeZone::GetOffset());
				break;
			case "<=DATE_CHANGE":
				if($val <> '')
					$arWhere[] = "date_change <= ".intval(MakeTimeStamp($val)-CTimeZone::GetOffset());
				break;
			case ">=DATE_CHANGE":
				if($val <> '')
					$arWhere[] = "date_change >= ".intval(MakeTimeStamp($val)-CTimeZone::GetOffset());
				break;
			case "SITE_ID":
				if($val !== false)
				{
					if ($inSelect)
						$arWhere[] = "in(site, ".sprintf("%u", crc32($val)).")";
					else
						$arWhere[] = "site = ".sprintf("%u", crc32($val));
				}
				break;
			case "CHECK_DATES":
				if($val == "Y")
				{
					$ts = time()-CTimeZone::GetOffset();
					if ($inSelect)
					{
						$arWhere[] = "if(date_from, date_from, ".$ts.") <= ".$ts;
						$arWhere[] = "if(date_to, date_to, ".$ts.") >= ".$ts;
					}
					else
					{
						$arWhere[] = "date_from_nvl <= ".$ts;
						$arWhere[] = "date_to_nvl >= ".$ts;
					}
				}
				break;
			case "TAGS":
				$arTags = explode(",", $val);
				foreach($arTags as $i => &$strTag)
				{
					$strTag = trim($strTag, " \n\r\t\"");
					if ($strTag == "")
						unset($arTags[$i]);
				}
				unset($strTag);

				$arWhere = array_merge($arWhere, $this->filterField("tags", $arTags, $inSelect));
				break;
			case "PARAMS":
				if (is_array($val))
				{
					$params = $this->params($val);
					if ($params != "")
					{
						if ($inSelect)
						{
							$arWhere[] = "in(param, ".$params.")";
						}
						else
						{
							foreach(explode(",", $params) as $param)
								$arWhere[] = "param = ".$param;
						}
					}
				}
				break;
			case "URL": //TODO
			case "QUERY":
			case "LIMIT":
			case "USE_TF_FILTER":
				break;
			default:
				if (is_numeric($field) && is_array($val))
				{
					$subFilter = $this->prepareFilter($val, true);
					if (!empty($subFilter))
					{
						if (isset($subFilter["cond1"]))
							$arWhere["cond1"][] = "(".implode(")and(", $subFilter).")";
						else
							$arWhere[] = "(".implode(")and(", $subFilter).")";
					}
				}
				else
				{
					//AddMessage2Log("field: $field; val: ".print_r($val, 1));
				}
				break;
			}
		}

		if (isset($arWhere["cond1"]))
			$arWhere["cond1"] = "(".implode(")and(", $arWhere["cond1"]).")";

		if ($orLogic && !empty($arWhere))
		{
			$arWhere = array(
				"cond1" => "(".implode(")or(", $arWhere).")"
			);
		}

		return $arWhere;
	}

	function CheckPermissions()
	{
		global $USER;
		$DB = CDatabase::GetModuleConnection('search');

		$arResult = array();

		if(!$USER->IsAdmin())
		{
			if($USER->GetID() > 0)
			{
				CSearchUser::CheckCurrentUserGroups();
				$rs = $DB->Query("SELECT GROUP_CODE FROM b_search_user_right WHERE USER_ID = ".$USER->GetID());
				while ($ar = $rs->Fetch())
					$arResult[] = $ar["GROUP_CODE"];
			}
			else
			{
				$arResult[] = "G2";
			}
		}

		return $this->rights($arResult);
	}

	function __PrepareSort($aSort = array())
	{
		$arOrder = array();
		if(!is_array($aSort))
			$aSort = array($aSort => "ASC");

		$this->flagsUseRatingSort = 0;
		foreach($aSort as $key => $ord)
		{
			$ord = mb_strtoupper($ord) <> "ASC"? "DESC": "ASC";
			$key = mb_strtolower($key);
			switch($key)
			{
				case "date_change":
				case "custom_rank":
				case "id":
				case "param1":
				case "param2":
				case "date_from":
				case "date_to":
					$arOrder[] = $key." ".$ord;
					break;
				case "item_id":
					$arOrder[] = "item ".$ord;
					break;
				case "module_id":
					$arOrder[] = "module ".$ord;
					break;
				case "rank":
					$arOrder[] = "rank ".$ord;
					break;
			}
		}

		if(count($arOrder) == 0)
		{
			$arOrder[]= "custom_rank DESC";
			$arOrder[]= "rank DESC";
			$arOrder[]= "date_change DESC";
		}

		return " ORDER BY ".implode(", ",$arOrder);
	}

	public function Escape($str)
	{
		static $search = array(
			"\\",
			"'",
			"/",
			")",
			"(",
			"$",
			"~",
			"!",
			"@",
			"^",
			"-",
			"|",
			"<",
			"\x0",
			"=",
		);
		static $replace = array(
			"\\\\",
			"\\'",
			"\\\\/",
			"\\\\)",
			"\\\\(",
			"\\\\\$",
			"\\\\~",
			"\\\\!",
			"\\\\@",
			"\\\\^",
			"\\\\-",
			"\\\\|",
			"\\\\<",
			" ",
			" ",
		);

		$str = str_replace($search, $replace, $str);

		$stat = count_chars($str, 1);
		if (isset($stat[ord('"')]) && $stat[ord('"')] % 2 === 1)
			$str = str_replace('"', '\\\"', $str);

		return $str;
	}

	public function Escape2($str)
	{
		static $search = array(
			"\\",
			"'",
			"\"",
			"\x0",
		);
		static $replace = array(
			"\\\\",
			"\\'",
			"\\\\\"",
			" ",
		);
		return str_replace($search, $replace, $str);
	}

	protected function canConnect()
	{
		return function_exists("mysqli_connect");
	}

	protected function internalConnect($connectionIndex, &$error)
	{
		$error = "";
		if (function_exists("mysqli_connect"))
		{
			$result = mysqli_init();

			if (mb_strpos($connectionIndex, ":") !== false)
			{
				list($host, $port) = explode(":", $connectionIndex, 2);
				$port = intval($port);
			}
			else
			{
				$host = $connectionIndex;
				$port = 0;
			}

			if ($port > 0)
			{
				if (!$result->real_connect($host, '', '', '', $port))
				{
					$error = mysqli_connect_error();
					$result = false;
				}
			}
			else
			{
				if (!$result->real_connect($host, '', '', ''))
				{
					$error = mysqli_connect_error();
					$result = false;
				}
			}
		}
		else
		{
			$result = false;
			$error = 'No MySql connection function has been found.';
		}

		return $result;
	}

	public function query($query)
	{
		if (is_object($this->db))
		{
			$result = $this->db->query($query);
		}
		else
		{
			$result = false;
		}
		return $result;
	}

	public function fetch($queryResult)
	{
		if (is_object($this->db))
		{
			$result = mysqli_fetch_assoc($queryResult);
		}
		else
		{
			$result = false;
		}
		return $result;
	}

	public function getError()
	{
		if (is_object($this->db))
		{
			$result = "[".$this->db->errno."] ".$this->db->error;
		}
		else
		{
			$result = '';
		}
		return $result;
	}
}

class CSearchSphinxFormatter extends CSearchFormatter
{
	/** @var CSearchSphinx */
	private $sphinx = null;
	function __construct($sphinx)
	{
		$this->sphinx = $sphinx;
	}

	function format($r)
	{
		if ($r)
		{
			if (array_key_exists("tag_id", $r))
				return $this->formatTagsRow($r);
			elseif  (array_key_exists("id", $r))
				return $this->formatRow($r);
		}
	}

	function formatTagsRow($r)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$tag_id = $r["tag_id"];
		if($tag_id > 0x7FFFFFFF)
			$tag_id = -(0xFFFFFFFF - $tag_id + 1);

		$rs = $DB->Query("
			select
				st.NAME
			from b_search_tags st
			where st.SEARCH_CONTENT_ID = ".$tag_id."
			and st.SITE_ID = '??'
		");

		$rt = $rs->Fetch();
		if ($rt)
		{
			$rt["NAME"] = htmlspecialcharsex($rt["NAME"]);
			$rt["CNT"] = $r["cnt"];
			$rt["FULL_DATE_CHANGE"] = ConvertTimeStamp($r["dc_tmp"]+CTimeZone::GetOffset(), "FULL");
			$rt["DATE_CHANGE"] = ConvertTimeStamp($r["dc_tmp"]+CTimeZone::GetOffset(), "SHORT");
		}
		return $rt;
	}

	function formatRow($r)
	{
		$DB = CDatabase::GetModuleConnection('search');
		if ($this->sphinx->SITE_ID)
		{
			$rs = $DB->Query("
				select
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TITLE
					,sc.TAGS
					,sc.BODY
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL
					,sc.CUSTOM_RANK
					,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
					,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
					,scsite.SITE_ID
					,scsite.URL SITE_URL
					".(BX_SEARCH_VERSION > 1? ",sc.USER_ID": "")."
				from b_search_content sc
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				where ID = ".$r["id"]."
				and scsite.SITE_ID = '".$DB->ForSql($this->sphinx->SITE_ID)."'
			");
		}
		else
		{
			$rs = $DB->Query("
				select
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TITLE
					,sc.TAGS
					,sc.BODY
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL
					,sc.CUSTOM_RANK
					,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
					,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
					".(BX_SEARCH_VERSION < 1? ",sc.LID as SITE_ID": "")."
				from b_search_content sc
				where ID = ".$r["id"]."
			");
		}
		$r = $rs->Fetch();
		if ($r)
		{
			$r["TITLE_FORMATED"] = $this->buildExcerpts(htmlspecialcharsex($r["TITLE"]));
			$r["TITLE_FORMATED_TYPE"] = "html";
			$r["TAGS_FORMATED"] = tags_prepare($r["TAGS"], SITE_ID);
			$r["BODY_FORMATED"] = $this->buildExcerpts(htmlspecialcharsex($r["BODY"]));
			$r["BODY_FORMATED_TYPE"] = "html";
		}
		return $r;
	}

	public function buildExcerpts($str)
	{
		$sql = "CALL SNIPPETS(
			'".$this->sphinx->Escape2($this->sphinx->recodeTo($str))."'
			,'".$this->sphinx->Escape($this->sphinx->indexName)."'
			,'".$this->sphinx->Escape($this->sphinx->recodeTo($this->sphinx->query." ".$this->sphinx->tags))."'
			,500 as limit
			,1 as query_mode
		)";
		$result = $this->sphinx->query($sql);

		if ($result)
		{
			$res = $this->sphinx->fetch($result);
			if ($res)
			{
				return $this->sphinx->recodeFrom($res["snippet"]);
			}
			else
			{
				return "";
			}
		}
		else
		{
			throw new \Bitrix\Main\Db\SqlQueryException('Sphinx select error', $this->sphinx->getError(), $sql);
		}
	}
}
