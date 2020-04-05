<?
/*
This class is used to parse and load an xml file into database table.
*/
class CIBlockXMLFile
{
	var $_table_name = "";
	var $_sessid = "";

	var $charset = false;
	var $element_stack = false;
	var $file_position = 0;

	var $read_size = 10240;
	var $buf = "";
	var $buf_position = 0;
	var $buf_len = 0;

	private $_get_xml_chunk_function = "_get_xml_chunk";

	function __construct($table_name = "b_xml_tree")
	{
		$this->_table_name = strtolower($table_name);
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_strpos") && function_exists("mb_orig_strlen") && function_exists("mb_orig_substr"))
				$this->_get_xml_chunk_function = "_get_xml_chunk_mb_orig";
			else
				$this->_get_xml_chunk_function = "_get_xml_chunk_mb";
		}
		else
		{
			$this->_get_xml_chunk_function = "_get_xml_chunk";
		}
	}

	function StartSession($sess_id)
	{
		global $DB;

		if(!$DB->TableExists($this->_table_name))
		{
			$res = $this->CreateTemporaryTables(true);
			if($res)
				$res = $this->IndexTemporaryTables(true);
		}
		else
		{
			$res = true;
		}

		if($res)
		{
			$this->_sessid = substr($sess_id, 0, 32);

			$rs = $this->GetList(array(), array("PARENT_ID" => -1), array("ID", "NAME"));
			if(!$rs->Fetch())
			{
				$this->Add(array(
					"PARENT_ID" => -1,
					"LEFT_MARGIN" => 0,
					"NAME" => "SESS_ID",
					"VALUE" => ConvertDateTime(ConvertTimeStamp(false, "FULL"), "YYYY-MM-DD HH:MI:SS"),
				));
			}
		}

		return $res;
	}

	function GetSessionRoot()
	{
		global $DB;
		$rs = $DB->Query("SELECT ID MID from ".$this->_table_name." WHERE SESS_ID = '".$DB->ForSQL($this->_sessid)."' AND PARENT_ID = 0");
		$ar = $rs->Fetch();
		return $ar["MID"];
	}

	function EndSession()
	{
		global $DB;

		//Delete "expired" sessions
		$expired = ConvertDateTime(ConvertTimeStamp(time()-3600, "FULL"), "YYYY-MM-DD HH:MI:SS");

		$rs = $DB->Query("select ID, SESS_ID, VALUE from ".$this->_table_name." where PARENT_ID = -1 AND NAME = 'SESS_ID' ORDER BY ID");
		while($ar = $rs->Fetch())
		{
			if($ar["SESS_ID"] == $this->_sessid || $ar["VALUE"] < $expired)
			{
				$DB->Query("DELETE from ".$this->_table_name." WHERE SESS_ID = '".$DB->ForSQL($ar["SESS_ID"])."'");
			}
		}
		return true;
	}

	public function GetRoot()
	{
		global $DB;
		$rs = $DB->Query("SELECT ID MID from ".$this->_table_name." WHERE PARENT_ID = 0");
		$ar = $rs->Fetch();
		return $ar["MID"];
	}

	/*
	This function have to called once at the import start.

	return : result of the CDatabase::Query method
	We use drop due to mysql innodb slow truncate bug.
	*/
	function DropTemporaryTables()
	{
		if(!isset($this) || !is_object($this) || strlen($this->_table_name) <= 0)
		{
			$ob = new CIBlockXMLFile;
			return $ob->DropTemporaryTables();
		}
		else
		{
			global $DB;
			if($DB->TableExists($this->_table_name))
				return $DB->DDL("drop table ".$this->_table_name);
			else
				return true;
		}
	}

	function CreateTemporaryTables($with_sess_id = false)
	{
		if(!isset($this) || !is_object($this) || strlen($this->_table_name) <= 0)
		{
			$ob = new CIBlockXMLFile;
			return $ob->CreateTemporaryTables();
		}
		else
		{
			global $DB;

			if(defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE) > 0)
				$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);

			$res = $DB->DDL("create table ".$this->_table_name."
				(
					ID int(11) not null auto_increment,
					".($with_sess_id? "SESS_ID varchar(32),": "")."
					PARENT_ID int(11),
					LEFT_MARGIN int(11),
					RIGHT_MARGIN int(11),
					DEPTH_LEVEL int(11),
					NAME varchar(255),
					VALUE longtext,
					ATTRIBUTES text,
					PRIMARY KEY (ID)
				)
			");

			if ($res && defined("BX_XML_CREATE_INDEXES_IMMEDIATELY"))
				$res = $this->IndexTemporaryTables($with_sess_id);

			return $res;
		}
	}

	function IsExistTemporaryTable()
	{
		global $DB;

		if (!isset($this) || !is_object($this) || strlen($this->_table_name) <= 0)
		{
			$ob = new CIBlockXMLFile;
			return $ob->IsExistTemporaryTable();
		}
		else
		{
			return $DB->TableExists($this->_table_name);
		}
	}

	function GetCountItemsWithParent($parentID)
	{
		global $DB;

		$parentID = (int)$parentID;

		if (!isset($this) || !is_object($this) || strlen($this->_table_name) <= 0)
		{
			$ob = new CIBlockXMLFile;
			return $ob->GetCountItemsWithParent($parentID);
		}
		else
		{
			$parentID = (int)$parentID;
			$rs = $DB->Query("select count(*) C from ".$this->_table_name." where PARENT_ID = ".$parentID);
			$ar = $rs->Fetch();
			return $ar['C'];
		}
	}

	/*
	This function indexes contents of the loaded data for future lookups.
	May be called after tables creation and loading will perform slowly.
	But it is recommented to call this function after all data load.
	This is much faster.

	return : result of the CDatabase::Query method
	*/
	function IndexTemporaryTables($with_sess_id = false)
	{
		if(!isset($this) || !is_object($this) || strlen($this->_table_name) <= 0)
		{
			$ob = new CIBlockXMLFile;
			return $ob->IndexTemporaryTables();
		}
		else
		{
			global $DB;
			$res = true;

			if($with_sess_id)
			{
				if(!$DB->IndexExists($this->_table_name, array("SESS_ID", "PARENT_ID")))
					$res = $DB->DDL("CREATE INDEX ix_".$this->_table_name."_parent on ".$this->_table_name."(SESS_ID, PARENT_ID)");
				if($res && !$DB->IndexExists($this->_table_name, array("SESS_ID", "LEFT_MARGIN")))
					$res = $DB->DDL("CREATE INDEX ix_".$this->_table_name."_left on ".$this->_table_name."(SESS_ID, LEFT_MARGIN)");
			}
			else
			{
				if(!$DB->IndexExists($this->_table_name, array("PARENT_ID")))
					$res = $DB->DDL("CREATE INDEX ix_".$this->_table_name."_parent on ".$this->_table_name."(PARENT_ID)");
				if($res && !$DB->IndexExists($this->_table_name, array("LEFT_MARGIN")))
					$res = $DB->DDL("CREATE INDEX ix_".$this->_table_name."_left on ".$this->_table_name."(LEFT_MARGIN)");
			}

			return $res;
		}
	}

	function Add($arFields)
	{
		global $DB;

		$strSql1 = "PARENT_ID, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, NAME";
		$strSql2 = intval($arFields["PARENT_ID"]).", ".intval($arFields["LEFT_MARGIN"]).", ".intval($arFields["RIGHT_MARGIN"]).", ".intval($arFields["DEPTH_LEVEL"]).", '".$DB->ForSQL($arFields["NAME"], 255)."'";

		if(array_key_exists("ATTRIBUTES", $arFields))
		{
			$strSql1 .= ", ATTRIBUTES";
			$strSql2 .= ", '".$DB->ForSQL($arFields["ATTRIBUTES"])."'";
		}

		if(array_key_exists("VALUE", $arFields))
		{
			$strSql1 .= ", VALUE";
			$strSql2 .= ", '".$DB->ForSQL($arFields["VALUE"])."'";
		}

		if($this->_sessid)
		{
			$strSql1 .= ", SESS_ID";
			$strSql2 .= ", '".$DB->ForSQL($this->_sessid)."'";
		}

		$strSql = "INSERT INTO ".$this->_table_name." (".$strSql1.") VALUES (".$strSql2.")";

		$rs = $DB->Query($strSql);

		return $DB->LastID();
	}

	function GetFilePosition()
	{
		return $this->file_position;
	}

	/*
	Reads portion of xml data.

	hFile - file handle opened with fopen function for reading
	NS - will be populated with to members
		charset parameter is used to recode file contents if needed.
		element_stack parameters save parsing stack of xml tree parents.
		file_position parameters marks current file position.
	time_limit - duration of one step in seconds.

	NS have to be preserved between steps.
	They automatically extracted from xml file and should not be modified!
	*/
	function ReadXMLToDatabase($fp, &$NS, $time_limit=0, $read_size = 1024)
	{
		global $APPLICATION;

		//Initialize object
		if(!array_key_exists("charset", $NS))
			$NS["charset"] = false;
		$this->charset = &$NS["charset"];

		if(!array_key_exists("element_stack", $NS))
			$NS["element_stack"] = array();
		$this->element_stack = &$NS["element_stack"];

		if(!array_key_exists("file_position", $NS))
			$NS["file_position"] = 0;
		$this->file_position = &$NS["file_position"];

		$this->read_size = $read_size;
		$this->buf = "";
		$this->buf_position = 0;
		$this->buf_len = 0;

		//This is an optimization. We assume than no step can take more than one year.
		if($time_limit > 0)
			$end_time = time() + $time_limit;
		else
			$end_time = time() + 365*24*3600; // One year

		$cs = $this->charset;
		$_get_xml_chunk = array($this, $this->_get_xml_chunk_function);
		fseek($fp, $this->file_position);
		while(($xmlChunk = call_user_func_array($_get_xml_chunk, array($fp))) !== false)
		{
			if($cs)
			{
				$xmlChunk = $APPLICATION->ConvertCharset($xmlChunk, $cs, LANG_CHARSET);
			}

			if($xmlChunk[0] == "/")
			{
				$this->_end_element($xmlChunk);
				if(time() > $end_time)
					break;
			}
			elseif($xmlChunk[0] == "!" || $xmlChunk[0] == "?")
			{
				if(substr($xmlChunk, 0, 4) === "?xml")
				{
					if(preg_match('#encoding[\s]*=[\s]*"(.*?)"#i', $xmlChunk, $arMatch))
					{
						$this->charset = $arMatch[1];
						if(strtoupper($this->charset) === strtoupper(LANG_CHARSET))
							$this->charset = false;
						$cs = $this->charset;
					}
				}
			}
			else
			{
				$this->_start_element($xmlChunk);
			}

		}

		return feof($fp);
	}

	/*
	Internal function.
	Used to read an xml by chunks started with "<" and endex with "<"
	*/
	function _get_xml_chunk($fp)
	{
		if($this->buf_position >= $this->buf_len)
		{
			if(!feof($fp))
			{
				$this->buf = fread($fp, $this->read_size);
				$this->buf_position = 0;
				$this->buf_len = strlen($this->buf);
			}
			else
				return false;
		}

		//Skip line delimiters (ltrim)
		$xml_position = strpos($this->buf, "<", $this->buf_position);
		while($xml_position === $this->buf_position)
		{
			$this->buf_position++;
			$this->file_position++;
			//Buffer ended with white space so we can refill it
			if($this->buf_position >= $this->buf_len)
			{
				if(!feof($fp))
				{
					$this->buf = fread($fp, $this->read_size);
					$this->buf_position = 0;
					$this->buf_len = strlen($this->buf);
				}
				else
					return false;
			}
			$xml_position = strpos($this->buf, "<", $this->buf_position);
		}

		//Let's find next line delimiter
		while($xml_position===false)
		{
			$next_search = $this->buf_len;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($fp))
			{
				$this->buf .= fread($fp, $this->read_size);
				$this->buf_len = strlen($this->buf);
			}
			else
				break;

			//Let's find xml tag start
			$xml_position = strpos($this->buf, "<", $next_search);
		}
		if($xml_position===false)
			$xml_position = $this->buf_len+1;

		$len = $xml_position-$this->buf_position;
		$this->file_position += $len;
		$result = substr($this->buf, $this->buf_position, $len);
		$this->buf_position = $xml_position;

		return $result;
	}

	/*
	Internal function.
	Used to read an xml by chunks started with "<" and endex with "<"
	*/
	function _get_xml_chunk_mb_orig($fp)
	{
		if($this->buf_position >= $this->buf_len)
		{
			if(!feof($fp))
			{
				$this->buf = fread($fp, $this->read_size);
				$this->buf_position = 0;
				$this->buf_len = mb_orig_strlen($this->buf);
			}
			else
				return false;
		}

		//Skip line delimiters (ltrim)
		$xml_position = mb_orig_strpos($this->buf, "<", $this->buf_position);
		while($xml_position === $this->buf_position)
		{
			$this->buf_position++;
			$this->file_position++;
			//Buffer ended with white space so we can refill it
			if($this->buf_position >= $this->buf_len)
			{
				if(!feof($fp))
				{
					$this->buf = fread($fp, $this->read_size);
					$this->buf_position = 0;
					$this->buf_len = mb_orig_strlen($this->buf);
				}
				else
					return false;
			}
			$xml_position = mb_orig_strpos($this->buf, "<", $this->buf_position);
		}

		//Let's find next line delimiter
		while($xml_position===false)
		{
			$next_search = $this->buf_len;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($fp))
			{
				$this->buf .= fread($fp, $this->read_size);
				$this->buf_len = mb_orig_strlen($this->buf);
			}
			else
				break;

			//Let's find xml tag start
			$xml_position = mb_orig_strpos($this->buf, "<", $next_search);
		}
		if($xml_position===false)
			$xml_position = $this->buf_len+1;

		$len = $xml_position-$this->buf_position;
		$this->file_position += $len;
		$result = mb_orig_substr($this->buf, $this->buf_position, $len);
		$this->buf_position = $xml_position;

		return $result;
	}

	/*
	Internal function.
	Used to read an xml by chunks started with "<" and endex with "<"
	*/
	function _get_xml_chunk_mb($fp)
	{
		if($this->buf_position >= $this->buf_len)
		{
			if(!feof($fp))
			{
				$this->buf = fread($fp, $this->read_size);
				$this->buf_position = 0;
				$this->buf_len = mb_strlen($this->buf);
			}
			else
				return false;
		}

		//Skip line delimiters (ltrim)
		$xml_position = mb_strpos($this->buf, "<", $this->buf_position);
		while($xml_position === $this->buf_position)
		{
			$this->buf_position++;
			$this->file_position++;
			//Buffer ended with white space so we can refill it
			if($this->buf_position >= $this->buf_len)
			{
				if(!feof($fp))
				{
					$this->buf = fread($fp, $this->read_size);
					$this->buf_position = 0;
					$this->buf_len = mb_strlen($this->buf);
				}
				else
					return false;
			}
			$xml_position = mb_strpos($this->buf, "<", $this->buf_position);
		}

		//Let's find next line delimiter
		while($xml_position===false)
		{
			$next_search = $this->buf_len;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($fp))
			{
				$this->buf .= fread($fp, $this->read_size);
				$this->buf_len = mb_strlen($this->buf);
			}
			else
				break;

			//Let's find xml tag start
			$xml_position = mb_strpos($this->buf, "<", $next_search);
		}
		if($xml_position===false)
			$xml_position = $this->buf_len+1;

		$len = $xml_position-$this->buf_position;
		$this->file_position += $len;
		$result = mb_substr($this->buf, $this->buf_position, $len);
		$this->buf_position = $xml_position;

		return $result;
	}

	/*
	Internal function.
	Stores an element into xml database tree.
	*/
	function _start_element($xmlChunk)
	{
		global $DB;
		static $search = array(
				"'&(quot|#34);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(amp|#38);'i",
			);

		static $replace = array(
				"\"",
				"<",
				">",
				"&",
			);

		$p = strpos($xmlChunk, ">");
		if($p !== false)
		{
			if(substr($xmlChunk, $p - 1, 1)=="/")
			{
				$bHaveChildren = false;
				$elementName = substr($xmlChunk, 0, $p-1);
				$DBelementValue = false;
			}
			else
			{
				$bHaveChildren = true;
				$elementName = substr($xmlChunk, 0, $p);
				$elementValue = substr($xmlChunk, $p+1);
				if(preg_match("/^\s*$/", $elementValue))
					$DBelementValue = false;
				elseif(strpos($elementValue, "&")===false)
					$DBelementValue = $elementValue;
				else
					$DBelementValue = preg_replace($search, $replace, $elementValue);
			}

			if(($ps = strpos($elementName, " "))!==false)
			{
				//Let's handle attributes
				$elementAttrs = substr($elementName, $ps+1);
				$elementName = substr($elementName, 0, $ps);
				preg_match_all("/(\\S+)\\s*=\\s*[\"](.*?)[\"]/s".BX_UTF_PCRE_MODIFIER, $elementAttrs, $attrs_tmp);
				$attrs = array();
				if(strpos($elementAttrs, "&")===false)
				{
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1)
						$attrs[$attrs_tmp_1] = $attrs_tmp[2][$i];
				}
				else
				{
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1)
						$attrs[$attrs_tmp_1] = preg_replace($search, $replace, $attrs_tmp[2][$i]);
				}
				$DBelementAttrs = serialize($attrs);
			}
			else
				$DBelementAttrs = false;

			if($c = count($this->element_stack))
				$parent = $this->element_stack[$c-1];
			else
				$parent = array("ID"=>"NULL", "L"=>0, "R"=>1);

			$left = $parent["R"];
			$right = $left+1;

			$arFields = array(
				"PARENT_ID" => $parent["ID"],
				"LEFT_MARGIN" => $left,
				"RIGHT_MARGIN" => $right,
				"DEPTH_LEVEL" => $c,
				"NAME" => $elementName,
			);
			if($DBelementValue !== false)
			{
				$arFields["VALUE"] = $DBelementValue;
			}
			if($DBelementAttrs !== false)
			{
				$arFields["ATTRIBUTES"] = $DBelementAttrs;
			}

			$ID = $this->Add($arFields);

			if($bHaveChildren)
				$this->element_stack[] = array("ID"=>$ID, "L"=>$left, "R"=>$right, "RO"=>$right);
			else
				$this->element_stack[$c-1]["R"] = $right+1;
		}
	}

	/*
	Internal function.
	Winds tree stack back. Modifies (if neccessary) internal tree structure.
	*/
	function _end_element($xmlChunk)
	{
		global $DB;

		$child = array_pop($this->element_stack);
		$this->element_stack[count($this->element_stack)-1]["R"] = $child["R"]+1;
		if($child["R"] != $child["RO"])
			$DB->Query("UPDATE ".$this->_table_name." SET RIGHT_MARGIN = ".intval($child["R"])." WHERE ID = ".intval($child["ID"]));
	}

	/*
	Returns an associative array of the part of xml tree.
	Elements with same name on the same level gets an additional suffix.
	For example
		<a>
			<b>123</b>
			<b>456</b>
		<a>
	will return
		array(
			"a => array(
				"b" => "123",
				"b1" => "456",
			),
		);
	*/
	function GetAllChildrenArray($arParent, $handleAttributes = false)
	{
		//We will return
		$arResult = array();

		//So we get not parent itself but xml_id
		if(!is_array($arParent))
		{
			$rs = $this->GetList(
				array(),
				array("ID" => $arParent),
				array("ID", "LEFT_MARGIN", "RIGHT_MARGIN"),
				$handleAttributes
			);
			$arParent = $rs->Fetch();
			if(!$arParent)
				return $arResult;
		}

		//Array of the references to the arResult array members with xml_id as index.
		$arSalt = array();
		$arIndex = array();
		$rs = $this->GetList(
			array("ID" => "asc"),
			array("><LEFT_MARGIN" => array($arParent["LEFT_MARGIN"]+1, $arParent["RIGHT_MARGIN"]-1)),
			array(),
			$handleAttributes
		);
		while($ar = $rs->Fetch())
		{
			if(isset($ar["VALUE_CLOB"]))
				$ar["VALUE"] = $ar["VALUE_CLOB"];

			if(isset($arSalt[$ar["PARENT_ID"]][$ar["NAME"]]))
			{
				$salt = ++$arSalt[$ar["PARENT_ID"]][$ar["NAME"]];
				$ar["NAME"] .= $salt;
			}
			else
			{
				$arSalt[$ar["PARENT_ID"]][$ar["NAME"]] = 0;
			}

			if($ar["PARENT_ID"] == $arParent["ID"])
			{
				$arResult[$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arResult[$ar["NAME"]];
			}
			else
			{
				$parent_id = $ar["PARENT_ID"];
				if(!is_array($arIndex[$parent_id]))
					$arIndex[$parent_id] = array();
				$arIndex[$parent_id][$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arIndex[$parent_id][$ar["NAME"]];
			}
		}

		return $arResult;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arSelect = array(), $handleAttributes = false)
	{
		global $DB;

		static $arFields = array(
			"ID" => "ID",
			"ATTRIBUTES" => "ATTRIBUTES",
			"LEFT_MARGIN" => "LEFT_MARGIN",
			"RIGHT_MARGIN" => "RIGHT_MARGIN",
			"NAME" => "NAME",
			"VALUE" => "VALUE",
		);
		foreach($arSelect as $i => $field)
			if(!array_key_exists($field, $arFields))
				unset($arSelect[$i]);
		if(count($arSelect) <= 0)
			$arSelect[] = "*";

		$arSQLWhere = array();
		foreach($arFilter as $field => $value)
		{
			if($field == "ID" && is_array($value) && !empty($value))
				$arSQLWhere[$field] = $field." in (".implode(",", array_map("intval", $value)).")";
			elseif($field == "ID" || $field == "LEFT_MARGIN")
				$arSQLWhere[$field] = $field." = ".intval($value);
			elseif($field == "PARENT_ID" || $field == "PARENT_ID+0")
				$arSQLWhere[$field] = $field." = ".intval($value);
			elseif($field == ">ID")
				$arSQLWhere[$field] = "ID > ".intval($value);
			elseif($field == "><LEFT_MARGIN")
				$arSQLWhere[$field] = "LEFT_MARGIN between ".intval($value[0])." AND ".intval($value[1]);
			elseif($field == "NAME")
				$arSQLWhere[$field] = $field." = "."'".$DB->ForSQL($value)."'";
		}
		if($this->_sessid)
			$arSQLWhere[] = "SESS_ID = '".$DB->ForSQL($this->_sessid)."'";

		foreach($arOrder as $field => $by)
		{
			if(!array_key_exists($field, $arFields))
				unset($arSelect[$field]);
			else
				$arOrder[$field] = $field." ".($by=="desc"? "desc": "asc");
		}

		$strSql = "
			select
				".implode(", ", $arSelect)."
			from
				".$this->_table_name."
			".(count($arSQLWhere)? "where (".implode(") and (", $arSQLWhere).")": "")."
			".(count($arOrder)? "order by  ".implode(", ", $arOrder): "")."
		";

		if ($handleAttributes)
		{
			$result = new CCMLResult($DB->Query($strSql));
		}
		else
		{
			$result = $DB->Query($strSql);
		}
		return $result;
	}

	function Delete($ID)
	{
		global $DB;
		return $DB->Query("delete from ".$this->_table_name." where ID = ".intval($ID));
	}

	public static function UnZip($file_name, $last_zip_entry = "", $start_time = 0, $interval = 0)
	{
		global $APPLICATION;

		//Function and securioty checks
		if(!function_exists("zip_open"))
			return false;
		$dir_name = substr($file_name, 0, strrpos($file_name, "/")+1);
		if(strlen($dir_name) <= strlen($_SERVER["DOCUMENT_ROOT"]))
			return false;

		$hZip = zip_open($file_name);
		if(!is_resource($hZip))
			return false;
		//Skip from last step
		if($last_zip_entry)
		{
			while($entry = zip_read($hZip))
				if(zip_entry_name($entry) == $last_zip_entry)
					break;
		}

		$io = CBXVirtualIo::GetInstance();
		//Continue unzip
		while($entry = zip_read($hZip))
		{
			$entry_name = zip_entry_name($entry);
			//Check for directory
			zip_entry_open($hZip, $entry);
			if(zip_entry_filesize($entry))
			{

				$file_name = trim(str_replace("\\", "/", trim($entry_name)), "/");
				$file_name = $APPLICATION->ConvertCharset($file_name, "cp866", LANG_CHARSET);
				$file_name = preg_replace("#^import_files/tmp/webdata/\\d+/\\d+/import_files/#", "import_files/", $file_name);

				$bBadFile = HasScriptExtension($file_name)
					|| IsFileUnsafe($file_name)
					|| !$io->ValidatePathString("/".$file_name)
				;

				if(!$bBadFile)
				{
					$file_name =  $io->GetPhysicalName($dir_name.Rel2Abs("/", $file_name));
					CheckDirPath($file_name);
					$fout = fopen($file_name, "wb");
					if(!$fout)
						return false;
					while($data = zip_entry_read($entry, 102400))
					{
						$data_len = function_exists('mb_strlen') ? mb_strlen($data, 'latin1') : strlen($data);
						$result = fwrite($fout, $data);
						if($result !== $data_len)
							return false;
					}
				}
			}
			zip_entry_close($entry);

			//Jump to next step
			if($interval > 0 && (time()-$start_time) > ($interval))
			{
				zip_close($hZip);
				return $entry_name;
			}
		}
		zip_close($hZip);
		return true;
	}
}