<?php
use Bitrix\Main;
use Bitrix\Main\DB\SqlQueryException;

/*
This class is used to parse and load an xml file into database table.
*/
class CIBlockXMLFile
{
	public const UNPACK_STATUS_ERROR = -1;
	public const UNPACK_STATUS_CONTINUE = 1;
	public const UNPACK_STATUS_FINAL = 2;
	var $_table_name = "";
	var $_sessid = "";

	var $charset = false;
	var $element_stack = [];
	var $file_position = 0;

	var $read_size = 10240;
	var $buf = "";
	var $buf_position = 0;
	var $buf_len = 0;

	function __construct($table_name = "b_xml_tree")
	{
		$this->_table_name = strtolower($table_name);
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

	public function initializeTemporaryTables(): bool
	{
		$initResult = true;
		$isNeedCreate = false;

		if ($this->IsExistTemporaryTable())
		{
			if ($this->isTableStructureCorrect())
			{
				$this->truncateTemporaryTables();
			}
			else
			{
				$this->DropTemporaryTables();
				$isNeedCreate = true;
			}
		}
		else
		{
			$isNeedCreate = true;
		}

		if ($isNeedCreate)
		{
			$initResult = $this->CreateTemporaryTables();
		}

		return $initResult;
	}

	public function truncateTemporaryTables(): bool
	{
		$connection = Main\Application::getConnection();

		if ($connection->isTableExists($this->_table_name))
		{
			$connection->truncateTable($this->_table_name);
		}

		return true;
	}

	/*
	This function have to called once at the import start.

	return : result of the CDatabase::Query method
	We use drop due to mysql innodb slow truncate bug.
	*/
	public function DropTemporaryTables()
	{
		$connection = Main\Application::getConnection();

		if ($connection->isTableExists($this->_table_name))
		{
			$connection->dropTable($this->_table_name);
		}

		return true;
	}

	public function CreateTemporaryTables($with_sess_id = false)
	{
		$connection = Main\Application::getConnection();

		if ($connection->isTableExists($this->_table_name))
		{
			return false;
		}

		if (
			$connection instanceof Main\DB\MysqlCommonConnection
			&& defined('MYSQL_TABLE_TYPE')
			&& MYSQL_TABLE_TYPE !== ''
		)
		{
			// TODO: remove try-catch when mysql 8.0 will be minimal system requirement
			try
			{
				$connection->query('SET default_storage_engine = \'' . MYSQL_TABLE_TYPE . '\'');
			}
			catch (SqlQueryException)
			{
				try
				{
					$connection->query('SET storage_engine = \''.MYSQL_TABLE_TYPE.'\'');
				}
				catch (SqlQueryException)
				{

				}
			}
		}

		$fields = [
			'ID' => (new Main\ORM\Fields\IntegerField('ID'))->configureSize(8),
			'SESS_ID' => (new Main\ORM\Fields\StringField('SESS_ID'))->configureSize(8),
			'PARENT_ID' => (new Main\ORM\Fields\IntegerField('PARENT_ID'))->configureSize(8)->configureNullable(),
			'LEFT_MARGIN' => (new Main\ORM\Fields\IntegerField('LEFT_MARGIN'))->configureNullable(),
			'RIGHT_MARGIN' => (new Main\ORM\Fields\IntegerField('RIGHT_MARGIN'))->configureNullable(),
			'DEPTH_LEVEL' => (new Main\ORM\Fields\IntegerField('DEPTH_LEVEL'))->configureNullable(),
			'NAME' => (new Main\ORM\Fields\StringField('NAME'))->configureSize(255)->configureNullable(),
			'VALUE' => (new Main\ORM\Fields\TextField('VALUE'))->configureLong()->configureNullable(),
			'ATTRIBUTES' => (new Main\ORM\Fields\TextField('ATTRIBUTES'))->configureNullable(),
		];
		if (!$with_sess_id)
		{
			unset($fields['SESS_ID']);
		}

		$connection->createTable($this->_table_name, $fields, ['ID'] ,['ID']);

		if (defined('BX_XML_CREATE_INDEXES_IMMEDIATELY'))
		{
			$this->IndexTemporaryTables($with_sess_id);
		}

		return true;
	}

	function IsExistTemporaryTable()
	{
		if (!isset($this) || !is_object($this) || $this->_table_name == '')
		{
			$ob = new CIBlockXMLFile;

			return $ob->IsExistTemporaryTable();
		}
		else
		{
			$connection = Main\Application::getConnection();

			return $connection->isTableExists($this->_table_name);
		}
	}

	public function isTableStructureCorrect($withSessId = false): bool
	{
		$connection = Main\Application::getConnection();

		$tableFields = $connection->getTableFields($this->_table_name);

		if (
			empty($tableFields['ID'])
			|| ($withSessId && empty($tableFields['SESS_ID']))
			|| empty($tableFields['PARENT_ID'])
			|| empty($tableFields['LEFT_MARGIN'])
			|| empty($tableFields['RIGHT_MARGIN'])
			|| empty($tableFields['DEPTH_LEVEL'])
			|| empty($tableFields['NAME'])
			|| empty($tableFields['VALUE'])
			|| empty($tableFields['ATTRIBUTES'])
		)
		{
			return false;
		}

		return true;
	}

	function GetCountItemsWithParent($parentID)
	{
		global $DB;

		$parentID = (int)$parentID;

		if (!isset($this) || !is_object($this) || $this->_table_name == '')
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
	public function IndexTemporaryTables($with_sess_id = false)
	{
		$connection = \Bitrix\Main\Application::getConnection();

		if($with_sess_id)
		{
			if (!$connection->isIndexExists($this->_table_name, ['SESS_ID', 'PARENT_ID']))
			{
				$connection->createIndex($this->_table_name, 'ix_' . $this->_table_name . '_parent', ['SESS_ID', 'PARENT_ID']);
			}

			if (!$connection->isIndexExists($this->_table_name, ['SESS_ID', 'LEFT_MARGIN']))
			{
				$connection->createIndex($this->_table_name, 'ix_' . $this->_table_name . '_left', ['SESS_ID', 'LEFT_MARGIN']);
			}
		}
		else
		{
			if (!$connection->isIndexExists($this->_table_name, ['PARENT_ID']))
			{
				$connection->createIndex($this->_table_name, 'ix_' . $this->_table_name . '_parent', ['PARENT_ID']);
			}

			if (!$connection->isIndexExists($this->_table_name, ['LEFT_MARGIN']))
			{
				$connection->createIndex($this->_table_name, 'ix_' . $this->_table_name . '_left', ['LEFT_MARGIN']);
			}
		}

		return true;
	}

	function Add($arFields)
	{
		global $DB;

		$strSql1 = "PARENT_ID, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, NAME";
		$strSql2 = (int)$arFields["PARENT_ID"] .", "
			. (int)$arFields["LEFT_MARGIN"] . ", "
			. (int)($arFields["RIGHT_MARGIN"] ?? 0) . ", "
			. (int)($arFields["DEPTH_LEVEL"] ?? 0) .", '"
			. $DB->ForSQL($arFields["NAME"] ?? '', 255)
			."'"
		;

		if (isset($arFields["ATTRIBUTES"]))
		{
			$strSql1 .= ", ATTRIBUTES";
			$strSql2 .= ", '".$DB->ForSQL($arFields["ATTRIBUTES"])."'";
		}

		if (isset($arFields["VALUE"]))
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

		$DB->Query($strSql);

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
		fseek($fp, $this->file_position);
		while(($xmlChunk = $this->_get_xml_chunk($fp)) !== false)
		{
			if($cs)
			{
				$xmlChunk = Main\Text\Encoding::convertEncoding($xmlChunk, $cs, LANG_CHARSET);
			}

			if($xmlChunk[0] == "/")
			{
				$this->_end_element($xmlChunk);
				if(time() > $end_time)
					break;
			}
			elseif($xmlChunk[0] == "!" || $xmlChunk[0] == "?")
			{
				if(strncmp($xmlChunk, "?xml", 4) === 0)
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

	/**
	 * Internal function.
	 * Used to read an xml by chunks started with "<" and endex with "<"
	 *
	 * @param resource $fp
	 * @return bool|string
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

	/**
	 * Internal function.
	 * Used to read an xml by chunks started with "<" and endex with "<"
	 *
	 * @deprecated deprecated since iblock 20.100.0
	 * @param resource $fp
	 * @return bool|string
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

	/**
	 * Internal function.
	 * Used to read an xml by chunks started with "<" and endex with "<"
	 *
	 * @deprecated deprecated since iblock 20.100.0
	 * @param resource $fp
	 * @return bool|string
	 */
	function _get_xml_chunk_mb($fp)
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
	Stores an element into xml database tree.
	*/
	function _start_element($xmlChunk)
	{
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
			if(substr($xmlChunk, $p - 1, 1) == "/")
			{
				$bHaveChildren = false;
				$elementName = substr($xmlChunk, 0, $p - 1);
				$DBelementValue = false;
			}
			else
			{
				$bHaveChildren = true;
				$elementName = substr($xmlChunk, 0, $p);
				$elementValue = substr($xmlChunk, $p + 1);
				if(preg_match("/^\s*$/", $elementValue))
					$DBelementValue = false;
				elseif(strpos($elementValue, "&") === false)
					$DBelementValue = $elementValue;
				else
					$DBelementValue = preg_replace($search, $replace, $elementValue);
			}

			if(($ps = strpos($elementName, " "))!==false)
			{
				//Let's handle attributes
				$elementAttrs = substr($elementName, $ps + 1);
				$elementName = substr($elementName, 0, $ps);
				preg_match_all("/(\\S+)\\s*=\\s*[\"](.*?)[\"]/su", $elementAttrs, $attrs_tmp);
				$attrs = array();
				if(!str_contains($elementAttrs, "&"))
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
			$DB->Query("UPDATE ".$this->_table_name." SET RIGHT_MARGIN = ".(int)$child["R"]." WHERE ID = ".(int)$child["ID"]);
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
			if (
				(int)$ar['PARENT_ID'] === 0
				&& (int)$ar['RIGHT_MARGIN'] === 0
				&& (int)$ar['DEPTH_LEVEL'] === 0
				&& $ar['NAME'] === ''
			)
			{
				continue;
			}
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
				if (!is_array($arIndex[$parent_id]))
				{
					$arIndex[$parent_id] = [];
				}
				$arIndex[$parent_id][$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arIndex[$parent_id][$ar["NAME"]];
			}
		}
		unset($ar);
		unset($rs);
		unset($arIndex);
		unset($arSalt);

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
		{
			if (!isset($arFields[$field]))
			{
				unset($arSelect[$i]);
			}
		}
		if (empty($arSelect))
		{
			$arSelect[] = "*";
		}

		$arSQLWhere = array();
		foreach($arFilter as $field => $value)
		{
			if($field == "ID" && is_array($value) && !empty($value))
			{
				Main\Type\Collection::normalizeArrayValuesByInt($value, false);
				if (!empty($value))
				{
					$arSQLWhere[$field] = $field . " in (" . implode(",", $value) . ")";
				}
			}
			elseif($field == "ID" || $field == "LEFT_MARGIN")
				$arSQLWhere[$field] = $field." = ".(int)$value;
			elseif($field == "PARENT_ID" || $field == "PARENT_ID+0")
				$arSQLWhere[$field] = $field." = ".(int)$value;
			elseif($field == ">ID")
				$arSQLWhere[$field] = "ID > ".(int)$value;
			elseif($field == "><LEFT_MARGIN")
				$arSQLWhere[$field] = "LEFT_MARGIN between ".(int)$value[0]." AND ".(int)$value[1];
			elseif($field == "NAME")
				$arSQLWhere[$field] = $field." = "."'".$DB->ForSQL($value)."'";
		}
		if($this->_sessid)
			$arSQLWhere[] = "SESS_ID = '".$DB->ForSQL($this->_sessid)."'";

		foreach($arOrder as $field => $by)
		{
			if(!isset($arFields[$field]))
			{
				unset($arSelect[$field]);
			}
			else
			{
				$arOrder[$field] = $field . " " . ($by == "desc" ? "desc" : "asc");
			}
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
		return $DB->Query("delete from ".$this->_table_name." where ID = ".(int)$ID);
	}

	/**
	 * @param string $fileName
	 * @param int|null $lastIndex
	 * @param int $interval
	 * @return array
	 */
	public static function safeUnZip(string $fileName, ?int $lastIndex = null, int $interval = 0): array
	{
		$result = [
			'STATUS' => self::UNPACK_STATUS_FINAL,
			'DATA' => []
		];

		$startTime = time();

		$dirName = mb_substr($fileName, 0, mb_strrpos($fileName, '/') + 1);
		if (mb_strlen($dirName) <= mb_strlen($_SERVER['DOCUMENT_ROOT']))
		{
			$result['STATUS'] = self::UNPACK_STATUS_ERROR;

			return $result;
		}

		/** @var CZip $archiver */
		$archiver = CBXArchive::GetArchive($fileName, 'ZIP');
		if (!($archiver instanceof IBXArchive))
		{
			$result['STATUS'] = self::UNPACK_STATUS_ERROR;

			return $result;
		}

		if ($lastIndex !== null && $lastIndex < 0)
		{
			$lastIndex = null;
		}

		$archiveProperties = $archiver->GetProperties();
		if (!is_array($archiveProperties))
		{
			$result['STATUS'] = self::UNPACK_STATUS_ERROR;

			return $result;
		}
		if (!isset($archiveProperties['nb']))
		{
			$result['STATUS'] = self::UNPACK_STATUS_ERROR;

			return $result;
		}
		$entries = (int)$archiveProperties['nb'];
		for ($index = 0; $index < $entries; $index++)
		{
			if ($lastIndex !== null)
			{
				if ($lastIndex >= $index)
				{
					continue;
				}
			}

			$archiver->SetOptions([
				'RULE' => [
					'by_index' => [
						[
							'start' => $index,
							'end' => $index,
						]
					]
				]
			]);

			$stepResult = $archiver->Unpack($dirName);
			if ($stepResult === true)
			{
				return $result;
			}
			if ($stepResult === false)
			{
				$result['STATUS'] = self::UNPACK_STATUS_ERROR;

				return $result;
			}

			if ($interval > 0 && (time() - $startTime) > $interval)
			{
				$result['STATUS'] = self::UNPACK_STATUS_CONTINUE;
				$result['DATA']['LAST_INDEX'] = $index;

				return $result;
			}
		}

		return $result;
	}

	/**
	 * @deprecated deprecated since 23.100.0 - unsecure
	 * @see CIBlockXMLFile::safeUnZip
	 *
	 * @param $file_name
	 * @param $last_zip_entry
	 * @param $start_time
	 * @param $interval
	 * @return bool|string
	 */
	public static function UnZip($file_name, $last_zip_entry = "", $start_time = 0, $interval = 0)
	{
		$last_zip_entry = (string)$last_zip_entry;
		if ($last_zip_entry === '')
		{
			$last_zip_entry = null;
		}
		else
		{
			$last_zip_entry = (int)$last_zip_entry;
		}

		$internalResult = static::safeUnZip((string)$file_name, $last_zip_entry, (int)$interval);

		switch ($internalResult['STATUS'])
		{
			case self::UNPACK_STATUS_ERROR:
				$result = false;
				break;
			case self::UNPACK_STATUS_CONTINUE:
				$result = $internalResult['DATA']['LAST_INDEX'];
				break;
			case self::UNPACK_STATUS_FINAL:
			default:
				$result = true;
				break;
		}

		return $result;
	}
}
