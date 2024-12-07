<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

trait OptionsHelperTrait
{
	protected static function setLastUpdate(): void
	{
		COption::SetOptionString(
			'main',
			'smile_last_update',
			(new DateTime())->getTimestamp()
		);
	}

	/**
	 * @return DateTime
	 */
	public static function getLastUpdate(): DateTime
	{
		$lastUpdateTimestamp = COption::GetOptionInt(
			'main',
			'smile_last_update',
		);

		if (!$lastUpdateTimestamp)
		{
			self::setLastUpdate();
		}

		return DateTime::createFromTimestamp($lastUpdateTimestamp);
	}
}

class CSmile
{
	use OptionsHelperTrait;

	const TYPE_ALL = '';
	const TYPE_SMILE = 'S';
	const TYPE_ICON = 'I';
	const PATH_TO_SMILE = "/upload/main/smiles/";
	const PATH_TO_ICON = "/upload/main/icons/";
	const CHECK_TYPE_ADD = 1;
	const CHECK_TYPE_UPDATE = 2;
	const GET_ALL_LANGUAGE = false;
	const IMAGE_SD = 'SD';
	const IMAGE_HD = 'HD';
	const IMAGE_UHD = 'UHD';

	private static function checkFields(&$arFields, $actionType = self::CHECK_TYPE_ADD)
	{
		global $APPLICATION;

		$aMsg = array();

		if(isset($arFields['TYPE']) && (!in_array($arFields['TYPE'], array(self::TYPE_SMILE, self::TYPE_ICON))))
			$aMsg[] = array("id"=>"TYPE", "text"=> GetMessage("MAIN_SMILE_TYPE_ERROR"));
		else if($actionType == self::CHECK_TYPE_ADD && !isset($arFields['TYPE']))
			$arFields['TYPE'] = self::TYPE_SMILE;

		if($actionType == self::CHECK_TYPE_ADD && (!isset($arFields['SET_ID']) || intval($arFields['SET_ID']) <= 0))
			$aMsg[] = array("id"=>"SET_ID", "text"=> GetMessage("MAIN_SMILE_SET_ID_ERROR"));

		if($actionType == self::CHECK_TYPE_ADD && (!isset($arFields['SORT']) || intval($arFields['SORT']) <= 0))
			$arFields['SORT'] = 300;

		if($actionType == self::CHECK_TYPE_ADD && $arFields['TYPE'] == self::TYPE_SMILE && (!isset($arFields['TYPING']) || $arFields['TYPING'] == ''))
			$aMsg[] = array("id"=>"TYPING", "text"=> GetMessage("MAIN_SMILE_TYPING_ERROR"));

		if($actionType == self::CHECK_TYPE_UPDATE && $arFields['TYPE'] == self::TYPE_SMILE && (isset($arFields['TYPING']) && $arFields['TYPING'] == ''))
			$aMsg[] = array("id"=>"TYPING", "text"=> GetMessage("MAIN_SMILE_TYPING_ERROR"));

		if(isset($arFields['CLICKABLE']) && $arFields['CLICKABLE'] != 'N')
			$arFields['CLICKABLE'] = 'Y';

		if(isset($arFields['IMAGE_DEFINITION']) && !in_array($arFields['IMAGE_DEFINITION'], Array(self::IMAGE_SD, self::IMAGE_HD, self::IMAGE_UHD)))
			$arFields['IMAGE_DEFINITION'] = self::IMAGE_SD;

		if(isset($arFields['HIDDEN']) && $arFields['HIDDEN'] != 'Y')
			$arFields['HIDDEN'] = 'N';

		if($actionType == self::CHECK_TYPE_ADD && (!isset($arFields['IMAGE']) || $arFields['IMAGE'] == ''))
			$aMsg[] = array("id"=>"IMAGE", "text"=> GetMessage("MAIN_SMILE_IMAGE_ERROR"));

		if (isset($arFields['IMAGE']) && (!in_array(mb_strtolower(GetFileExtension($arFields['IMAGE'])), Array('png', 'jpg', 'gif')) || !CBXVirtualIo::GetInstance()->ValidateFilenameString($arFields['IMAGE'])))
			$aMsg[] = array("id"=>"IMAGE", "text"=> GetMessage("MAIN_SMILE_IMAGE_ERROR"));

		if(isset($arFields['IMAGE']) && (!isset($arFields['IMAGE_WIDTH']) || intval($arFields['IMAGE_WIDTH']) <= 0))
			$aMsg["IMAGE_XY"] = array("id"=>"IMAGE_XY", "text"=> GetMessage("MAIN_SMILE_IMAGE_XY_ERROR"));

		if(isset($arFields['IMAGE']) && (!isset($arFields['IMAGE_HEIGHT']) || intval($arFields['IMAGE_HEIGHT']) <= 0))
			$aMsg["IMAGE_XY"] = array("id"=>"IMAGE_XY", "text"=> GetMessage("MAIN_SMILE_IMAGE_XY_ERROR"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		return true;
	}

	public static function add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (!self::checkFields($arFields, self::CHECK_TYPE_ADD))
			return false;

		$arInsert = array(
			'TYPE' => $arFields['TYPE'],
			'SET_ID' => intval($arFields['SET_ID']),
			'SORT' => intval($arFields['SORT']),
			'IMAGE' => $arFields['IMAGE'],
			'IMAGE_WIDTH' => intval($arFields['IMAGE_WIDTH']),
			'IMAGE_HEIGHT' => intval($arFields['IMAGE_HEIGHT']),
		);

		if (isset($arFields['IMAGE_DEFINITION']))
		{
			$arInsert['IMAGE_DEFINITION'] = $arFields['IMAGE_DEFINITION'];
			if ($arInsert['IMAGE_DEFINITION'] == self::IMAGE_UHD)
			{
				$arInsert['IMAGE_WIDTH'] = $arInsert['IMAGE_WIDTH']/4;
				$arInsert['IMAGE_HEIGHT'] = $arInsert['IMAGE_HEIGHT']/4;
			}
			else if ($arInsert['IMAGE_DEFINITION'] == self::IMAGE_HD)
			{
				$arInsert['IMAGE_WIDTH'] = $arInsert['IMAGE_WIDTH']/2;
				$arInsert['IMAGE_HEIGHT'] = $arInsert['IMAGE_HEIGHT']/2;
			}
		}

		if (isset($arFields['TYPING']))
			$arInsert['TYPING'] = $arFields['TYPING'];

		if (isset($arFields['CLICKABLE']))
			$arInsert['CLICKABLE'] = $arFields['CLICKABLE'];

		if (isset($arFields['HIDDEN']))
			$arInsert['HIDDEN'] = $arFields['HIDDEN'];

		$setId = intval($DB->Add("b_smile", $arInsert));

		if ($setId && isset($arFields['LANG']))
		{
			$arLang = Array();
			if (is_array($arFields['LANG']))
				$arLang = $arFields['LANG'];
			else
				$arLang[LANG] = $arFields['LANG'];

			foreach ($arLang as $lang => $name)
			{
				if (trim($name) <> '')
				{
					$arInsert = array(
						'TYPE' => self::TYPE_SMILE,
						'SID' => $setId,
						'LID' => htmlspecialcharsbx($lang),
						'NAME' => trim($name),
					);
					$DB->Add("b_smile_lang", $arInsert);
				}
			}
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return $setId;
	}

	public static function update($id, $arFields)
	{
		// TODO
		global $DB, $CACHE_MANAGER;

		$id = intval($id);
		if (!self::checkFields($arFields, self::CHECK_TYPE_UPDATE))
			return false;

		$arUpdate = Array();

		if (isset($arFields['TYPE']))
			$arUpdate['TYPE'] = "'".$arFields['TYPE']."'";

		if (isset($arFields['SET_ID']))
			$arUpdate['SET_ID'] = intval($arFields['SET_ID']);

		if (isset($arFields['SORT']))
			$arUpdate['SORT'] = intval($arFields['SORT']);

		if (isset($arFields['IMAGE']))
		{
			$arUpdate['IMAGE'] = "'".$DB->ForSql($arFields['IMAGE'])."'";
			$arUpdate['IMAGE_WIDTH'] = intval($arFields['IMAGE_WIDTH']);
			$arUpdate['IMAGE_HEIGHT'] = intval($arFields['IMAGE_HEIGHT']);

			if (isset($arFields['IMAGE_DEFINITION']))
			{
				$arUpdate['IMAGE_DEFINITION'] = "'".$DB->ForSql($arFields['IMAGE_DEFINITION'])."'";
				if ($arFields['IMAGE_DEFINITION'] == self::IMAGE_UHD)
				{
					$arUpdate['IMAGE_WIDTH'] = $arUpdate['IMAGE_WIDTH']/4;
					$arUpdate['IMAGE_HEIGHT'] = $arUpdate['IMAGE_HEIGHT']/4;
				}
				else if ($arFields['IMAGE_DEFINITION'] == self::IMAGE_HD)
				{
					$arUpdate['IMAGE_WIDTH'] = $arUpdate['IMAGE_WIDTH']/2;
					$arUpdate['IMAGE_HEIGHT'] = $arUpdate['IMAGE_HEIGHT']/2;
				}
			}
		}

		if (isset($arFields['TYPING']))
			$arUpdate['TYPING'] = "'".$DB->ForSql($arFields['TYPING'])."'";

		if (isset($arFields['CLICKABLE']))
			$arUpdate['CLICKABLE'] = "'".$arFields['CLICKABLE']."'";

		if (isset($arFields['HIDDEN']))
			$arUpdate['HIDDEN'] = "'".$arFields['HIDDEN']."'";

		if (!empty($arUpdate))
			$DB->Update("b_smile", $arUpdate, "WHERE ID = ".intval($id));

		if (isset($arFields['LANG']))
		{
			$arLang = Array();
			if (is_array($arFields['LANG']))
				$arLang = $arFields['LANG'];
			else
				$arLang[LANG] = $arFields['LANG'];

			foreach ($arLang as $lang => $name)
			{
				if (trim($name) <> '')
				{
					$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".self::TYPE_SMILE."' AND SID = ".$id." AND LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'", true);
					$arInsert = array(
						'TYPE' => self::TYPE_SMILE,
						'SID' => $id,
						'LID' => htmlspecialcharsbx($lang),
						'NAME' => trim($name),
					);
					$DB->Add("b_smile_lang", $arInsert);
				}
			}
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return true;
	}

	public static function delete($id, $removeFile = true)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);
		if ($id <= 0)
			return false;

		if ($removeFile)
		{
			$arSmile = CSmile::getByID($id);
			@unlink($_SERVER["DOCUMENT_ROOT"].($arSmile['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).$arSmile['SET_ID'].'/'.$arSmile['IMAGE']);
		}

		$DB->Query("DELETE FROM b_smile WHERE ID = ".$id, true);
		$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".self::TYPE_SMILE."' AND SID = ".$id, true);

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return true;
	}

	public static function deleteBySet($id, $removeFile = true)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);
		if ($id <= 0)
			return false;

		$arDelete = Array();
		$arSmiles = self::getList(Array(
			'SELECT' => Array('ID', 'SET_ID', 'TYPE', 'IMAGE'),
			'FILTER' => Array('SET_ID' => $id),
		));
		foreach ($arSmiles as $key => $arSmile)
		{
			$arDelete[] = intval($key);
		}

		if ($removeFile)
		{
			DeleteDirFilesEx(CSmile::PATH_TO_ICON.$id.'/');
			DeleteDirFilesEx(CSmile::PATH_TO_SMILE.$id.'/');
		}

		if (!empty($arDelete))
		{
			$DB->Query("DELETE FROM b_smile WHERE ID IN (".implode(',', $arDelete).")", true);
			$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".self::TYPE_SMILE."' AND SID IN (".implode(',', $arDelete).")", true);

			self::setLastUpdate();
			$CACHE_MANAGER->CleanDir("b_smile");
		}

		return true;
	}

	public static function deleteByGallery($id, $removeFile = true)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);
		if ($id <= 0)
			return false;

		$arDelete = Array();
		$arDir = Array();
		$arSmiles = self::getList(Array(
			'SELECT' => Array('ID', 'SET_ID', 'TYPE', 'IMAGE'),
			'FILTER' => Array('PARENT_ID' => $id),
		));

		foreach ($arSmiles as $key => $arSmile)
		{
			$arDelete[] = intval($key);
			$arDir[$arSmile['SET_ID']] = ($arSmile['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).$arSmile['SET_ID'].'/';

		}
		if ($removeFile)
		{
			foreach ($arDir as $path)
			{
				DeleteDirFilesEx($path);
			}
		}

		if (!empty($arDelete))
		{
			$DB->Query("DELETE FROM b_smile WHERE ID IN (".implode(',', $arDelete).")", true);
			$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".self::TYPE_SMILE."' AND SID IN (".implode(',', $arDelete).")", true);

			self::setLastUpdate();
			$CACHE_MANAGER->CleanDir("b_smile");
		}

		return true;
	}

	public static function getById($id, $lang = LANGUAGE_ID)
	{
		global $DB;

		$id = intval($id);
		$arResult = Array();

		$strSql = "
			SELECT s.*, sl.NAME, sl.LID
			FROM b_smile s
			LEFT JOIN b_smile_lang sl ON sl.TYPE = '".self::TYPE_SMILE."' AND sl.SID = s.ID".($lang !== false? " AND sl.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'": "")."
			WHERE s.ID = ".$id."";
		$res = $DB->Query($strSql);

		if ($lang !== self::GET_ALL_LANGUAGE)
		{
			$arResult = $res->GetNext(true, false);
			unset($arResult['LID']);
		}
		else
		{
			while ($row = $res->GetNext(true, false))
			{
				if (empty($arResult))
				{
					$arResult = $row;
					$arResult['NAME'] = Array();
					unset($arResult['LID']);
				}
				$arResult['NAME'][$row['LID']] = $row['NAME'];
			}
		}
		return $arResult;
	}

	public static function getList($arParams = Array(), $lang = LANGUAGE_ID)
	{
		global $DB;

		$arResult = $arSelect = $arOrder = $arFilter = $arJoin = Array();
		if (!isset($arParams['SELECT']) || !is_array($arParams['SELECT']))
			$arParams['SELECT'] = Array('ID', 'SET_ID',  'TYPE', 'NAME', 'SORT', 'TYPING', 'CLICKABLE', 'HIDDEN', 'IMAGE', 'IMAGE_DEFINITION', 'IMAGE_WIDTH', 'IMAGE_HEIGHT');

		// select block
		foreach ($arParams['SELECT'] as $fieldName)
		{
			if ($fieldName == 'NAME')
			{
				$arSelect['NAME'] = 'sl.'.$fieldName;
				$arJoin['LANG'] = "LEFT JOIN b_smile_lang sl ON sl.TYPE = '".self::TYPE_SMILE."' AND sl.SID = s.ID AND sl.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'";
			}
			elseif ($fieldName == 'SET_NAME')
			{
				$arSelect['SET_ID'] = 's.SET_ID';
				$arSelect['SET_NAME'] = 'sl2.NAME as SET_NAME';
				$arJoin['LANG2'] = "LEFT JOIN b_smile_lang sl2 ON sl2.TYPE = '".CSmileSet::TYPE_SET."' AND sl2.SID = s.SET_ID AND sl2.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'";
			}
			else
			{
				$arSelect[$fieldName] = 's.'.$fieldName;
			}
		}
		$arSelect['ID'] = 's.ID';

		// filter block
		if (isset($arParams['FILTER']['ID']))
		{
			if (is_array($arParams['FILTER']['ID']))
			{
				$ID = Array();
				foreach ($arParams['FILTER']['ID'] as $key => $value)
					$ID[$key] = intval($value);

				if (!empty($ID))
					$arFilter[] = "s.ID IN (".implode(',', $ID).')';
			}
			else
			{
				$arFilter[] = "s.ID = ".intval($arParams['FILTER']['ID']);
			}
		}
		if (isset($arParams['FILTER']['SET_ID']))
		{
			if (is_array($arParams['FILTER']['SET_ID']))
			{
				$ID = Array();
				foreach ($arParams['FILTER']['SET_ID'] as $key => $value)
					$ID[$key] = intval($value);

				if (!empty($ID))
					$arFilter[] = "s.SET_ID IN ('".implode("','", $ID)."')";
			}
			else
			{
				$arFilter[] = "s.SET_ID = ".intval($arParams['FILTER']['SET_ID']);
			}
		}
		if (isset($arParams['FILTER']['TYPE']) && in_array($arParams['FILTER']['TYPE'], Array(self::TYPE_SMILE, self::TYPE_ICON)))
		{
			$arFilter[] = "s.TYPE = '".$arParams['FILTER']['TYPE']."'";
		}
		if (isset($arParams['FILTER']['PARENT_ID']))
		{
			$arFilter[] = "ss2.PARENT_ID = ".intval($arParams['FILTER']['PARENT_ID']);
			$arJoin['PARENT'] = "LEFT JOIN b_smile_set ss2 ON ss2.ID = s.SET_ID";
		}

		// order block
		if (isset($arParams['ORDER']) && is_array($arParams['ORDER']))
		{
			foreach ($arParams['ORDER'] as $by => $order)
			{
				$order = mb_strtoupper($order) == 'ASC'? 'ASC': 'DESC';
				$by = mb_strtoupper($by);
				if (in_array($by, Array('ID', 'SET_ID', 'SORT', 'IMAGE_DEFINITION', 'HIDDEN')))
				{
					$arOrder[$by] = 's.'.$by.' '.$order;
				}
			}
		}
		else
		{
			$arOrder['ID'] = 's.ID DESC';
		}

		$strSelect = "SELECT ".implode(', ', $arSelect);
		$strSql = "
			FROM b_smile s
			".(!empty($arJoin)? implode(' ', $arJoin): "")."
			".(!empty($arFilter)? "WHERE ".implode(' AND ', $arFilter): "")."
			".(!empty($arOrder)? "ORDER BY ".implode(', ', $arOrder): "")."
		";

		if (isset($arParams['RETURN_SQL']) && $arParams['RETURN_SQL'] == 'Y')
		{
			return $strSelect.$strSql;
		}

		if(array_key_exists("NAV_PARAMS", $arParams) && is_array($arParams["NAV_PARAMS"]))
		{
			$nTopCount = intval($arParams['NAV_PARAMS']['nTopCount'] ?? 0);
			if($nTopCount > 0)
			{
				$strSql = $DB->TopSql($strSelect.$strSql, $nTopCount);
				$res = $DB->Query($strSql);
			}
			else
			{
				$res_cnt = $DB->Query("
					SELECT COUNT(s.ID) as CNT
					FROM b_smile s
					".(!empty($arFilter)? "WHERE ".implode(' AND ', $arFilter): "")
				);
				$arCount = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->NavQuery($strSelect.$strSql, $arCount["CNT"], $arParams["NAV_PARAMS"]);
			}
		}
		else
		{
			$res = $DB->Query($strSelect.$strSql);
		}

		if (isset($arParams['RETURN_RES']) && $arParams['RETURN_RES'] == 'Y')
		{
			return $res;
		}
		else
		{
			while ($row = $res->GetNext(true, false))
				$arResult[$row['ID']] = $row;

			return $arResult;
		}
	}

	/**
	 * @deprecated Use CSmile::getBySetId
	 */
	public static function getByType($type = self::TYPE_ALL, $setId = CSmileSet::SET_ID_BY_CONFIG, $lang = LANGUAGE_ID)
	{
		return self::getBySetId($type, $setId, $lang);
	}

	public static function getBySetId($type = self::TYPE_ALL, $setId = CSmileSet::SET_ID_BY_CONFIG, $lang = LANGUAGE_ID)
	{
		$arFilter = array();
		if (in_array($type, array(self::TYPE_SMILE, self::TYPE_ICON)))
			$arFilter["TYPE"] = $type;

		$setId = intval($setId);

		if ($setId == CSmileSet::SET_ID_BY_CONFIG)
		{
			$arFilter['PARENT_ID'] = CSmileGallery::getDefaultId();
			$cacheSetId = 'p'.$arFilter['PARENT_ID'];
		}
		else
		{
			$cacheSetId = $setId;
			if ($setId != CSmileSet::SET_ID_ALL)
				$arFilter['SET_ID'] = $setId;
		}

		if ($lang <> '')
			$arFilter["LID"] = htmlspecialcharsbx($lang);

		global $CACHE_MANAGER;
		$cache_id = "b_smile_set_2_".$arFilter["TYPE"]."_".$cacheSetId."_".$lang;

		if (CACHED_b_smile !== false && $CACHE_MANAGER->Read(CACHED_b_smile, $cache_id, "b_smile"))
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$arResult = self::getList(Array(
				'ORDER' => Array('SORT' => 'ASC'),
				'FILTER' => $arFilter,
			));

			if (CACHED_b_smile !== false)
				$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		return $arResult;

	}

	public static function getByGalleryId($type = self::TYPE_ALL, $galleryId = CSmileGallery::GALLERY_DEFAULT, $lang = LANGUAGE_ID)
	{
		$arFilter = array();
		if (in_array($type, array(self::TYPE_SMILE, self::TYPE_ICON)))
			$arFilter["TYPE"] = $type;

		$galleryId = intval($galleryId);
		if ($galleryId == CSmileGallery::GALLERY_DEFAULT)
			$galleryId = CSmileGallery::getDefaultId();

		if ($lang <> '')
			$arFilter["LID"] = htmlspecialcharsbx($lang);

		global $CACHE_MANAGER;
		$cache_id = "b_smile_gallery_".$arFilter["TYPE"]."_".$galleryId."_".$arFilter["LID"];

		if (CACHED_b_smile !== false && $CACHE_MANAGER->Read(CACHED_b_smile, $cache_id, "b_smile"))
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$arSets = CSmileSet::getList(Array(
				'FILTER' => Array('PARENT_ID' => $galleryId)
			));
			foreach ($arSets as $set)
			{
				$arFilter['SET_ID'][] = $set['ID'];
			}

			$arResult = self::getList(Array(
				'ORDER' => Array('SORT' => 'ASC'),
				'FILTER' => $arFilter,
			));

			if (CACHED_b_smile !== false)
				$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		return $arResult;
	}


	public static function import($arParams)
	{
		global $APPLICATION;

		// check fields
		$aMsg = array();
		$arParams['SET_ID'] = intval($arParams['SET_ID']);
		$arParams['IMPORT_IF_FILE_EXISTS'] = isset($arParams['IMPORT_IF_FILE_EXISTS']) && $arParams['IMPORT_IF_FILE_EXISTS'] == 'Y'? true: false;
		if(isset($arParams['FILE']) && GetFileExtension($arParams['FILE']) != 'zip')
		{
			$aMsg["FILE_EXT"] = array("id"=>"FILE_EXT", "text"=> GetMessage("MAIN_SMILE_IMPORT_FILE_EXT_ERROR"));
		}
		else if (!isset($arParams['FILE']) || !file_exists($arParams['FILE']))
		{
			$aMsg["FILE"] = array("id"=>"FILE", "text"=> GetMessage("MAIN_SMILE_IMPORT_FILE_ERROR"));
		}
		else if($arParams['SET_ID'] <= 0)
		{
			$aMsg["SET_ID"] = array("id"=>"SET_ID", "text"=> GetMessage("MAIN_SMILE_IMPORT_SET_ID_ERROR"));
		}
		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		$sUnpackDir = CTempFile::GetDirectoryName(1);
		CheckDirPath($sUnpackDir);

		/** @var IBXArchive $oArchiver */
		$oArchiver = CBXArchive::GetArchive($arParams['FILE'], "ZIP");
		$oArchiver->SetOptions(array("STEP_TIME" => 300));

		if (!$oArchiver->Unpack($sUnpackDir))
		{
			$aMsg["UNPACK"] = array("id"=>"UNPACK", "text"=> GetMessage("MAIN_SMILE_IMPORT_UNPACK_ERROR"));
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		$arSmiles = Array();
		if (file_exists($sUnpackDir.'install.csv'))
		{
			$arLang = Array();
			$db_res = CLanguage::GetList();
			while ($res = $db_res->Fetch())
			{
				if (file_exists($sUnpackDir.'install_lang_'. $res["LID"].'.csv'))
				{
					$csvFile = new CCSVData();
					$csvFile->LoadFile($sUnpackDir.'install_lang_'.$res["LID"].'.csv');
					$csvFile->SetFieldsType("R");
					$csvFile->SetFirstHeader(false);
					while($smile = $csvFile->Fetch())
					{
						$arLang[$smile[0]][$res["LID"]] = $smile[1];
					}
				}
			}

			$csvFile = new CCSVData();
			$csvFile->LoadFile($sUnpackDir.'install.csv');
			$csvFile->SetFieldsType("R");
			$csvFile->SetFirstHeader(false);
			while($smileRes = $csvFile->Fetch())
			{
				$smile = Array(
					'TYPE' => $smileRes[0],
					'CLICKABLE' => $smileRes[1] == 'Y'? 'Y': 'N',
					'SORT' => intval($smileRes[2]),
					'IMAGE' => $smileRes[3],
					'IMAGE_WIDTH' => intval($smileRes[4]),
					'IMAGE_HEIGHT' => intval($smileRes[5]),
					'IMAGE_DEFINITION' => in_array($smileRes[6], Array(self::IMAGE_SD, self::IMAGE_HD, self::IMAGE_UHD))? $smileRes[6]: ($smileRes[6] == 'Y'? self::IMAGE_HD: self::IMAGE_SD),
					'HIDDEN' => in_array($smileRes[7], Array('Y', 'N'))? $smileRes[7]: 'N',
					'IMAGE_LANG' => in_array($smileRes[7], Array('Y', 'N'))? $smileRes[8]: $smileRes[7], // for legacy
					'TYPING' => in_array($smileRes[7], Array('Y', 'N'))? $smileRes[9]: $smileRes[8]
				);

				if (!in_array($smile['TYPE'], Array(CSmile::TYPE_SMILE, CSmile::TYPE_ICON)))
					continue;

				$smile['IMAGE'] = GetFileName($smile['IMAGE']);

				$info = (new \Bitrix\Main\File\Image($sUnpackDir.$smile['IMAGE']))->getInfo();
				if (!$info)
					continue;

				$arInsert = Array(
					'TYPE' => $smile['TYPE'],
					'SET_ID' => $arParams['SET_ID'],
					'CLICKABLE' => $smile['CLICKABLE'],
					'SORT' => $smile['SORT'],
					'IMAGE' => $smile['IMAGE'],
					'IMAGE_WIDTH' => $smile['IMAGE_WIDTH'],
					'IMAGE_HEIGHT' => $smile['IMAGE_HEIGHT'],
					'IMAGE_DEFINITION' => $smile['IMAGE_DEFINITION'],
					'HIDDEN' => $smile['HIDDEN'],
					'TYPING' => $smile['TYPING'],
				);

				if (isset($arLang[$smile['IMAGE_LANG']]))
					$arInsert['LANG'] = $arLang[$smile['IMAGE_LANG']];

				$arSmiles[] = $arInsert;
			}
		}
		else
		{
			$smileSet = CSmileSet::getById($arParams['SET_ID']);
			if ($handle = @opendir($sUnpackDir))
			{
				$sort = 300;
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "." || $file == "..")
						continue;

					if (is_file($sUnpackDir.$file))
					{
						$info = (new \Bitrix\Main\File\Image($sUnpackDir.$file))->getInfo();
						if ($info)
						{
							$smileHR = self::IMAGE_SD;
							$smileType = CSmile::TYPE_SMILE;
							$smileCode = GetFileNameWithoutExtension($file);
							if (str_starts_with($file, 'smile_'))
							{
								$smileCode = mb_substr($smileCode, 6);
							}
							elseif (str_starts_with($file, 'smile'))
							{
								$smileCode = mb_substr($smileCode, 5);
							}
							elseif (str_starts_with($file, 'icon_'))
							{
								$smileType = CSmile::TYPE_ICON;
								$smileCode = mb_substr($smileCode, 5);
							}
							else if (str_starts_with($file, 'icon'))
							{
								$smileType = CSmile::TYPE_ICON;
								$smileCode = mb_substr($smileCode, 4);
							}
							if (mb_strrpos($smileCode, '_hr') !== false && mb_strrpos($smileCode, '_hr') == mb_strlen($smileCode) - 3)
							{
								$smileHR = self::IMAGE_HD;
								$smileCode = mb_substr($smileCode, 0, mb_strrpos($smileCode, '_hr'));
							}
							else if (($pos = mb_strpos($smileCode, '_hr_')))
							{
								$smileHR = self::IMAGE_HD;
								$smileCode = mb_substr($smileCode, 0, $pos).'_'.mb_substr($smileCode, $pos + 4);
							}
							else if (mb_strrpos($smileCode, '_uhd') !== false && mb_strrpos($smileCode, '_uhd') == mb_strlen($smileCode) - 4)
							{
								$smileHR = self::IMAGE_UHD;
								$smileCode = mb_substr($smileCode, 0, mb_strrpos($smileCode, '_uhd'));
							}

							$arSmiles[] = Array(
								'TYPE' => $smileType,
								'SET_ID' => $arParams['SET_ID'],
								'CLICKABLE' => 'Y',
								'SORT' => $sort,
								'IMAGE' => $file,
								'IMAGE_WIDTH' => intval($info->getWidth()),
								'IMAGE_HEIGHT' => intval($info->getHeight()),
								'IMAGE_DEFINITION' => $smileHR,
								'TYPING' => ':'.($smileSet['STRING_ID'] ?? $smileSet['ID']).'/'.$smileCode.':',
							);
							$sort = $sort+5;
						}
					}
				}
				@closedir($handle);
			}
		}
		$importSmile = 0;
		foreach ($arSmiles as $smile)
		{
			$sUploadDir = ($smile['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).intval($smile["SET_ID"]).'/';
			if (file_exists($sUnpackDir.$smile['IMAGE']) && ($arParams['IMPORT_IF_FILE_EXISTS'] || !file_exists($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$smile['IMAGE'])))
			{
				if (CheckDirPath($_SERVER["DOCUMENT_ROOT"].$sUploadDir))
				{
					$insertId = CSmile::add($smile);
					if ($insertId)
					{
						if ($arParams['IMPORT_IF_FILE_EXISTS'] && file_exists($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$smile['IMAGE']))
						{
							$importSmile++;
						}
						else if (copy($sUnpackDir.$smile['IMAGE'], $_SERVER["DOCUMENT_ROOT"].$sUploadDir.$smile['IMAGE']))
						{
							@chmod($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$smile['IMAGE'], BX_FILE_PERMISSIONS);
							$importSmile++;
						}
						else
						{
							CSmile::delete($insertId);
						}
					}

					$APPLICATION->ResetException();
				}
			}
		}

		self::setLastUpdate();
		return $importSmile;
	}

	/**
	 * Onetime command for copy smiles from bitrix/images
	 *
	 * @return void
	 */
	public static function moveSmilesToUploadAgent(): string
	{
		$paths = [
			'smiles' => [
				'old' => '/bitrix/images/main/smiles/',
				'new' => self::PATH_TO_SMILE
			],
			'icons' => [
				'old' => '/bitrix/images/main/icons/',
				'new' => self::PATH_TO_ICON
			]
		];

		$returnValue = '';
		foreach ($paths as $path)
		{
			$oldPath = $_SERVER["DOCUMENT_ROOT"] . $path['old'];
			$newPath = $_SERVER["DOCUMENT_ROOT"] . $path['new'];

			$directory = new \Bitrix\Main\IO\Directory($oldPath);
			if ($directory->isExists())
			{
				CopyDirFiles($directory->getPhysicalPath(), $newPath, true, true);
				self::setLastUpdate();
			}
			else
			{
				$returnValue = __METHOD__ . '();';
			}
		}

		return $returnValue;
	}
}

class CSmileGallery
{
	use OptionsHelperTrait;

	const GALLERY_DEFAULT = 0;
	const GET_ALL_LANGUAGE = false;

	public static function add($arFields)
	{
		$arFields['TYPE'] = CSmileSet::TYPE_GALLERY;
		return CSmileSet::add($arFields);
	}

	public static function update($id, $arFields)
	{
		return CSmileSet::update($id, $arFields);
	}

	public static function delete($id)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);

		$res = $DB->Query("SELECT ID, TYPE FROM b_smile_set WHERE ID = ".$id);
		if ($smileGallery = $res->Fetch())
		{
			CSmile::deleteByGallery($smileGallery['ID']);

			$DB->Query("DELETE FROM b_smile_set WHERE ID = ".$smileGallery['ID'], true);
			$DB->Query("DELETE FROM b_smile_set WHERE PARENT_ID = ".$smileGallery['ID'], true);
			$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".$smileGallery['TYPE']."' AND SID = ".$smileGallery['ID'], true);
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

	}

	public static function getById($id, $lang = LANGUAGE_ID)
	{
		return CSmileSet::getById($id, $lang);
	}

	public static function getByStringId($stringId, $lang = LANGUAGE_ID)
	{
		return CSmileSet::getByStringId($stringId, CSmileSet::TYPE_GALLERY, $lang);
	}

	public static function getList($arParams = Array(), $lang = LANGUAGE_ID)
	{
		$arParams['FILTER']['TYPE'] = CSmileSet::TYPE_GALLERY;
		return CSmileSet::getList($arParams, $lang);
	}

	public static function getListCache($lang = LANGUAGE_ID)
	{
		if ($lang <> '')
			$lang = htmlspecialcharsbx($lang);

		global $CACHE_MANAGER;
		$cache_id = "b_smile_gallery_".$lang;

		if (CACHED_b_smile !== false && $CACHE_MANAGER->Read(CACHED_b_smile, $cache_id, "b_smile"))
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$arResult = self::getList(Array('ORDER' => Array('SORT' => 'ASC')), $lang);
			if (CACHED_b_smile !== false)
				$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		return $arResult;
	}

	public static function getListForForm($lang = LANGUAGE_ID)
	{
		$arSetList = Array();
		foreach (self::getListCache($lang) as $key => $value)
			$arSetList[$key] = !empty($value['NAME'])? $value['NAME']: GetMessage('MAIN_SMILE_GALLERY_NAME', Array('#ID#' => $key));

		return $arSetList;
	}

	public static function getDefaultId()
	{
		$galleryId = COption::GetOptionString("main", "smile_gallery_id", self::GALLERY_DEFAULT);
		if ($galleryId == 0)
		{
			$gallery = CSmileGallery::getByStringId('bitrix');

			if ($gallery)
			{
				$galleryId = $gallery['ID'];
				self::setDefaultId($galleryId);
			}
		}

		$eventGalleryId = -1;
		foreach(GetModuleEvents("main", "OnBeforeSmileGalleryGetDefaultId", true) as $arEvent)
			$eventGalleryId = intval(ExecuteModuleEventEx($arEvent, array($galleryId)));

		return $eventGalleryId > 0 && $eventGalleryId != $galleryId? $eventGalleryId: $galleryId;
	}

	public static function setDefaultId($id)
	{
		return COption::SetOptionString("main", "smile_gallery_id", $id);
	}

	public static function getSmilesWithSets($galleryId = self::GALLERY_DEFAULT, $options = [])
	{
		if ($galleryId == self::GALLERY_DEFAULT)
		{
			$galleryId = self::getDefaultId();
		}

		$result = array('SMILE' => Array(), 'SMILE_SET' => Array());

		$smiles = CSmile::getByGalleryId(CSmile::TYPE_SMILE, $galleryId);

		$smilesSet = CSmileSet::getListCache();

		$fullTypings = isset($options['FULL_TYPINGS']) && $options['FULL_TYPINGS'] === 'Y';

		$userSets = Array();
		foreach ($smiles as $smile)
		{
			if ($smile['HIDDEN'] == 'Y')
				continue;

			$typing = explode(" ", $smile['TYPING']);
			if (isset($result['SMILE'][$typing[0]]))
				continue;

			$result['SMILE'][] = Array(
				'ID' => (int)$smile['ID'],
				'SET_ID' => (int)$smile['SET_ID'],
				'NAME' => $smile['NAME'],
				'IMAGE' => CSmile::PATH_TO_SMILE.$smile["SET_ID"]."/".$smile["IMAGE"],
				'TYPING' => $fullTypings? $smile['TYPING']: $typing[0],
				'WIDTH' => (int)$smile['IMAGE_WIDTH'],
				'HEIGHT' => (int)$smile['IMAGE_HEIGHT'],
				'DEFINITION' => $smile['IMAGE_DEFINITION'],
			);
			$userSets[$smile['SET_ID']] = true;
		}
		foreach ($smilesSet as $key => $value)
		{
			if (!$userSets[$value['ID']])
				continue;

			if (empty($value['NAME']))
				$value['NAME'] = GetMessage('MAIN_SMILE_SET_NAME', Array('#ID#' => $key));

			$result['SMILE_SET'][] = Array(
				'ID' => (int)$value['ID'],
				'PARENT_ID' => (int)$value['PARENT_ID'],
				'NAME' => $value['NAME'],
				'TYPE' => $value['TYPE'],
			);
		}

		return $result;
	}

	public static function installGallery()
	{
		$smileGalleryId = 0;

		$arLang = Array();
		$arLang2 = Array();
		$langs = CLanguage::GetList();
		while($language = $langs->Fetch())
		{
			$lid = $language["LID"];
			$MESS = IncludeModuleLangFile(__FILE__, $lid, true);
			if ($MESS && isset($MESS['MAIN_SMILE_DEF_GALLERY_NAME']))
				$arLang[$lid] = $MESS['MAIN_SMILE_DEF_GALLERY_NAME'];
			if ($MESS && isset($MESS['MAIN_SMILE_DEF_SET_NAME']))
				$arLang2[$lid] = $MESS['MAIN_SMILE_DEF_SET_NAME'];
		}

		$gallery = CSmileGallery::getByStringId('bitrix');
		if (!$gallery)
		{

			$smileGalleryId = CSmileGallery::add(Array(
				'STRING_ID' => 'bitrix',
				'LANG' => $arLang,
			));
		}
		else
		{
			$smileGalleryId = $gallery['ID'];
		}

		if ($smileGalleryId)
		{
			$smileSet = CSmileSet::getByStringId('bitrix_main');
			if ($smileSet)
			{
				$smileSetId = $smileSet['ID'];
				CSmile::deleteBySet($smileSet['ID']);
			}
			else
			{
				$smileSetId = CSmileSet::add(Array(
					'STRING_ID' => 'bitrix_main',
					'PARENT_ID' => $smileGalleryId,
					'LANG' => $arLang2,
				));
			}

			CSmile::import(array('FILE' => $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/install/smiles/smiles_default.zip', 'SET_ID' => $smileSetId, 'IMPORT_IF_FILE_EXISTS' => 'Y'));
		}
	}

	public static function convertGallery()
	{
		global $DB;

		$arLang = Array();
		$arLang2 = Array();
		$arLang3 = Array();
		$langs = CLanguage::GetList();
		while($language = $langs->Fetch())
		{
			$lid = $language["LID"];
			$MESS = IncludeModuleLangFile(__FILE__, $lid, true);
			if ($MESS && isset($MESS['MAIN_SMILE_DEF_GALLERY_NAME']))
				$arLang[$lid] = $MESS['MAIN_SMILE_DEF_GALLERY_NAME'];
			if ($MESS && isset($MESS['MAIN_SMILE_DEF_SET_NAME']))
				$arLang2[$lid] = $MESS['MAIN_SMILE_DEF_SET_NAME'];
			if ($MESS && isset($MESS['MAIN_SMILE_USER_GALLERY_NAME']))
				$arLang3[$lid] = $MESS['MAIN_SMILE_USER_GALLERY_NAME'];
		}

		$smileGalleryId = 0;

		$gallery = CSmileGallery::getByStringId('bitrix');
		if (!$gallery)
		{
			$smileGalleryId = CSmileGallery::add(Array(
				'STRING_ID' => 'bitrix',
				'LANG' => $arLang,
			));
		}
		else
		{
			$smileGalleryId = $gallery['ID'];
		}

		if (COption::GetOptionInt("main", "smile_gallery_converted", 0) == 0)
		{
			$res = $DB->Query('SELECT * FROM b_smile');

			$smileOriginalSet = Array(
				'smile_smile.png' => array('TYPING' => ':) :-)', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_wink.png' => array('TYPING' => ';) ;-)', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_biggrin.png' => array('TYPING' => ':D :-D', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_cool.png' => array('TYPING' => '8) 8-)', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_sad.png' => array('TYPING' => ':( :-(', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_neutral.png' => array('TYPING' => ':| :-|', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_redface.png' => array('TYPING' => ':oops:', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_cry.png' => array('TYPING' => ':cry: :~(', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_evil.png' => array('TYPING' => ':evil: >:-<', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_eek.png' => array('TYPING' => ':o :-o :shock:', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_confuse.png' => array('TYPING' => ':/ :-/', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_kiss.png' => array('TYPING' => ':{} :-{}', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_idea.png' => array('TYPING' => ':idea:', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_question.png' => array('TYPING' => ':?:', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
				'smile_exclaim.png' => array('TYPING' => ':!:', 'IMAGE_WIDTH' => '16', 'IMAGE_HEIGHT' => '16'),
			);
			$smileCount = 0;
			$smileOriginalCount = 0;
			while ($smile = $res->fetch())
			{
				if ($smile['TYPE'] != CSmile::TYPE_SMILE)
				{
					continue;
				}

				if (
					$smileOriginalSet[$smile['IMAGE']]
					&& $smileOriginalSet[$smile['IMAGE']]['IMAGE_WIDTH'] == $smile['IMAGE_WIDTH']
					&& $smileOriginalSet[$smile['IMAGE']]['IMAGE_HEIGHT'] == $smile['IMAGE_HEIGHT']
					&& $smileOriginalSet[$smile['IMAGE']]['TYPING'] == $smile['TYPING']
				)
				{
					$smileOriginalCount++;
				}

				$smileCount++;
			}

			if (!(($smileCount == 0 || $smileCount == 15) && $smileCount == $smileOriginalCount))
			{
				$smileCustomGalleryId = 0;
				$smileSet = CSmileGallery::getByStringId('bitrix_convert');
				if (!$smileSet)
				{
					$smileCustomGalleryId = CSmileGallery::add(Array(
						'STRING_ID' => 'bitrix_convert',
						'SORT' => 300,
						'LANG' => $arLang3,
					));

				}
				else
				{
					$smileCustomGalleryId = $smileSet['ID'];
				}
				CSmileGallery::setDefaultId($smileCustomGalleryId);
				$DB->Query("UPDATE b_smile_set SET PARENT_ID = ".$smileCustomGalleryId." WHERE TYPE = 'G' AND PARENT_ID = 0");
			}
			else
			{
				$smileSet = CSmileSet::getByStringId('main');
				if ($smileSet)
				{
					CSmileSet::delete($smileSet['ID']);
				}
			}
			COption::SetOptionInt("main", "smile_gallery_converted", 1);
		}

		if ($smileGalleryId)
		{
			$smileSet = CSmileSet::getByStringId('bitrix_main');
			if ($smileSet)
			{
				$smileSetId = $smileSet['ID'];
				CSmile::deleteBySet($smileSet['ID']);
			}
			else
			{
				$smileSetId = CSmileSet::add(Array(
					'STRING_ID' => 'bitrix_main',
					'PARENT_ID' => $smileGalleryId,
					'LANG' => $arLang2,
				));
			}

			CSmile::import(array('FILE' => $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/install/smiles/smiles_default.zip', 'SET_ID' => $smileSetId, 'IMPORT_IF_FILE_EXISTS' => 'Y'));
		}

		return false;
	}
}

class CSmileSet
{
	use OptionsHelperTrait;

	const TYPE_SET = 'G';
	const TYPE_GALLERY = 'P';

	const SET_ID_ALL = 0;
	const SET_ID_BY_CONFIG = -1;
	const GET_ALL_LANGUAGE = false;

	public static function add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		$arInsert = array();

		$arFields['PARENT_ID'] = intval($arFields['PARENT_ID']);
		$arInsert['PARENT_ID'] = $arFields['PARENT_ID'];

		$arFields['TYPE'] = isset($arFields['TYPE']) && $arFields['TYPE'] == self::TYPE_GALLERY? self::TYPE_GALLERY: self::TYPE_SET;

		if ($arFields['TYPE'] != self::TYPE_GALLERY && !$arFields['PARENT_ID'] )
			return false;

		if (isset($arFields['STRING_ID']))
			$arInsert['STRING_ID'] = $arFields['STRING_ID'];

		if (isset($arFields['SORT']))
			$arInsert['SORT'] = intval($arFields['SORT']);

		$arInsert['TYPE'] = $arFields['TYPE'];

		$setId = intval($DB->Add("b_smile_set", $arInsert));

		if ($setId && isset($arFields['LANG']))
		{
			$arLang = Array();
			if (is_array($arFields['LANG']))
				$arLang = $arFields['LANG'];
			else
				$arLang[LANG] = $arFields['LANG'];

			foreach ($arLang as $lang => $name)
			{
				$arInsert = array(
					'TYPE' => $arFields['TYPE'],
					'SID' => $setId,
					'LID' => htmlspecialcharsbx($lang),
					'NAME' => $name,
				);
				$DB->Add("b_smile_lang", $arInsert);
			}
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return $setId;
	}

	public static function update($id, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);

		$arUpdate = Array();

		if (isset($arFields['STRING_ID']))
			$arUpdate['STRING_ID'] = "'".$DB->ForSql($arFields['STRING_ID'])."'";

		if (isset($arFields['SORT']))
			$arUpdate['SORT'] = intval($arFields['SORT']);

		if (!empty($arUpdate))
			$DB->Update("b_smile_set", $arUpdate, "WHERE ID = ".$id);

		if (isset($arFields['LANG']))
		{
			$arLang = Array();
			if (is_array($arFields['LANG']))
				$arLang = $arFields['LANG'];
			else
				$arLang[LANG] = $arFields['LANG'];

			$res = $DB->Query("SELECT TYPE FROM b_smile_set WHERE ID = ".$id);
			$smileSet = $res->Fetch();

			foreach ($arLang as $lang => $name)
			{
				$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".$smileSet['TYPE']."' AND SID = ".$id." AND LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'", true);
				$arInsert = array(
					'TYPE' => $smileSet['TYPE'],
					'SID' => $id,
					'LID' => htmlspecialcharsbx($lang),
					'NAME' => $name,
				);
				$DB->Add("b_smile_lang", $arInsert);
			}
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return true;
	}

	public static function delete($id)
	{
		global $DB, $CACHE_MANAGER;

		$id = intval($id);

		$res = $DB->Query("SELECT ID, TYPE FROM b_smile_set WHERE ID = ".$id);
		if ($smileSet = $res->Fetch())
		{
			$DB->Query("DELETE FROM b_smile_set WHERE ID = ".$smileSet['ID'], true);
			$DB->Query("DELETE FROM b_smile_lang WHERE TYPE = '".$smileSet['TYPE']."' AND SID = ".$smileSet['ID'], true);

			CSmile::deleteBySet($smileSet['ID']);
		}

		self::setLastUpdate();
		$CACHE_MANAGER->CleanDir("b_smile");

		return true;
	}

	public static function getById($id, $lang = LANGUAGE_ID)
	{
		global $DB;

		$id = intval($id);
		$arResult = Array();

		$strSql = "
			SELECT ss.*, sl.NAME, sl.LID
			FROM b_smile_set ss
			LEFT JOIN b_smile_lang sl ON sl.TYPE = ss.TYPE AND sl.SID = ss.ID".($lang !== false? " AND sl.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'": "")."
			WHERE ss.ID = ".$id."";
		$res = $DB->Query($strSql);

		if ($lang !== self::GET_ALL_LANGUAGE)
		{
			$arResult = $res->GetNext(true, false);
			unset($arResult['LID']);
		}
		else
		{
			while ($row = $res->GetNext(true, false))
			{
				if (empty($arResult))
				{
					$arResult = $row;
					$arResult['NAME'] = Array();
					unset($arResult['LID']);
				}
				$arResult['NAME'][$row['LID']] = $row['NAME'];
			}
		}
		return $arResult;
	}

	public static function getByStringId($stringId, $type = self::TYPE_SET, $lang = LANGUAGE_ID)
	{
		global $DB;

		$arResult = Array();

		if (!in_array($type, Array(self::TYPE_SET, self::TYPE_GALLERY))) // for legacy
		{
			$lang = $type;
			$type = self::TYPE_SET;
		}

		$strSql = "
			SELECT ss.*, sl.NAME, sl.LID
			FROM b_smile_set ss
			LEFT JOIN b_smile_lang sl ON sl.TYPE = ss.TYPE AND sl.SID = ss.ID".($lang !== false? " AND sl.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'": "")."
			WHERE ss.STRING_ID = '".$DB->ForSql($stringId)."' AND ss.TYPE = '".$DB->ForSql($type)."'";
		$res = $DB->Query($strSql);

		if ($lang !== false)
		{
			$arResult = $res->GetNext(true, false);
			unset($arResult['LID']);
		}
		else
		{
			while ($row = $res->GetNext(true, false))
			{
				if (empty($arResult))
				{
					$arResult = $row;
					$arResult['NAME'] = Array();
					unset($arResult['LID']);
				}
				$arResult['NAME'][$row['LID']] = $row['NAME'];
			}
		}
		return $arResult;
	}

	public static function getBySmiles($arSmiles)
	{
		$arResult = Array();
		$arSets = self::getListCache();

		foreach ($arSmiles as $smile)
		{
			if (isset($arSets[$smile['SET_ID']]))
				$arResult[$smile['SET_ID']] = $arSets[$smile['SET_ID']];
		}

		return $arResult;
	}

	public static function getList($arParams = Array(), $lang = LANGUAGE_ID)
	{
		global $DB;

		$arResult = $arSelect = $arOrder = $arFilter = $arJoin = Array();
		if (!isset($arParams['SELECT']) || !is_array($arParams['SELECT']))
			$arParams['SELECT'] = Array('ID', 'STRING_ID', 'SORT', 'NAME', 'TYPE', 'PARENT_ID');

		if (isset($arParams['ORDER']['SMILE_COUNT']))
			$arParams['SELECT'][] = 'SMILE_COUNT';

		// select block

		$type = $arParams['FILTER']['TYPE'] ?? '';
		if (!in_array($type, Array(CSmileSet::TYPE_SET, CSmileSet::TYPE_GALLERY)))
		{
			$arParams['FILTER']['TYPE'] = CSmileSet::TYPE_SET;
		}

		foreach ($arParams['SELECT'] as $fieldName)
		{
			if ($fieldName == 'NAME')
			{
				$arSelect['NAME'] = 'sl.'.$fieldName;
				$arJoin['LANG'] = "LEFT JOIN b_smile_lang sl ON sl.TYPE = ss.TYPE AND sl.SID = ss.ID AND sl.LID = '".$DB->ForSql(htmlspecialcharsbx($lang))."'";
			}
			elseif ($fieldName == 'SMILE_COUNT')
			{
				if ($arParams['FILTER']['TYPE'] == CSmileSet::TYPE_SET)
				{
					$arSelect['SMILE_COUNT'] = '(SELECT COUNT(s.ID) FROM b_smile s WHERE s.SET_ID = ss.ID) as SMILE_COUNT';
				}
				else
				{
					$arSelect['SMILE_COUNT'] = '(SELECT COUNT(s.ID) FROM b_smile_set ss1 LEFT JOIN b_smile s ON ss1.ID = s.SET_ID WHERE ss1.PARENT_ID = ss.ID) as SMILE_COUNT';
				}
			}
			else
			{
				$arSelect[$fieldName] = 'ss.'.$fieldName;
			}
		}
		$arSelect['ID'] = 'ss.ID';

		// filter block
		if (isset($arParams['FILTER']['ID']))
		{
			if (is_array($arParams['FILTER']['ID']))
			{
				$ID = Array();
				foreach ($arParams['FILTER']['ID'] as $key => $value)
					$ID[$key] = intval($value);

				if (!empty($ID))
					$arFilter[] = "ss.ID IN (".implode(',', $ID).')';
			}
			else
			{
				$arFilter[] = "ss.ID = ".intval($arParams['FILTER']['ID']);
			}
		}
		if (isset($arParams['FILTER']['PARENT_ID']))
		{
			$arFilter[] = "ss.PARENT_ID = ".intval($arParams['FILTER']['PARENT_ID']);
		}
		if (isset($arParams['FILTER']['STRING_ID']))
		{
			if (is_array($arParams['FILTER']['STRING_ID']))
			{
				$ID = Array();
				foreach ($arParams['FILTER']['STRING_ID'] as $key => $value)
					$ID[$key] = intval($value);

				if (!empty($ID))
					$arFilter[] = "ss.STRING_ID IN ('".implode("','", $ID)."')";
			}
			else
			{
				$arFilter[] = "ss.STRING_ID = ".intval($arParams['FILTER']['STRING_ID']);
			}
		}

		$arFilter[] = "ss.TYPE = '".$arParams['FILTER']['TYPE']."'";

		// order block
		if (isset($arParams['ORDER']) && is_array($arParams['ORDER']))
		{
			foreach ($arParams['ORDER'] as $by => $order)
			{
				$order = mb_strtoupper($order) == 'ASC'? 'ASC': 'DESC';
				$by = mb_strtoupper($by);
				if (in_array($by, Array('ID', 'SORT')))
				{
					$arOrder[$by] = 'ss.'.$by.' '.$order;
				}
				else if ($by == 'SMILE_COUNT')
					$arOrder[$by] = $by.' '.$order;
			}
		}
		else
		{
			$arOrder['ID'] = 'ss.ID DESC';
		}

		$strSelect = "SELECT ".implode(', ', $arSelect);
		$strSql = "
			FROM b_smile_set ss
			".(!empty($arJoin)? implode(' ', $arJoin): "")."
			".(!empty($arFilter)? "WHERE ".implode(' AND ', $arFilter): "")."
			".(!empty($arOrder)? "ORDER BY ".implode(', ', $arOrder): "")."
		";

		if (isset($arParams['RETURN_SQL']) && $arParams['RETURN_SQL'] == 'Y')
		{
			return $strSelect.$strSql;
		}

		if(array_key_exists("NAV_PARAMS", $arParams) && is_array($arParams["NAV_PARAMS"]))
		{
			$nTopCount = intval($arParams['NAV_PARAMS']['nTopCount'] ?? 0);
			if($nTopCount > 0)
			{
				$strSql = $DB->TopSql($strSelect.$strSql, $nTopCount);
				$res = $DB->Query($strSql);
			}
			else
			{
				$res_cnt = $DB->Query("
					SELECT COUNT(ss.ID) as CNT
					FROM b_smile_set ss
					".(!empty($arFilter)? "WHERE ".implode(' AND ', $arFilter): "")
				);
				$arCount = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->NavQuery($strSelect.$strSql, $arCount["CNT"], $arParams["NAV_PARAMS"]);
			}
		}
		else
		{
			$res = $DB->Query($strSelect.$strSql);
		}

		if (isset($arParams['RETURN_RES']) && $arParams['RETURN_RES'] == 'Y')
		{
			return $res;
		}
		else
		{
			while ($row = $res->GetNext(true, false))
				$arResult[$row['ID']] = $row;

			return $arResult;
		}
	}

	public static function getListCache($lang = LANGUAGE_ID)
	{
		if ($lang <> '')
			$lang = htmlspecialcharsbx($lang);

		global $CACHE_MANAGER;
		$cache_id = "b_smile_set_2_".$lang;

		if (CACHED_b_smile !== false && $CACHE_MANAGER->Read(CACHED_b_smile, $cache_id, "b_smile"))
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$arResult = self::getList(Array('ORDER' => Array('SORT' => 'ASC')), $lang);
			if (CACHED_b_smile !== false)
				$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		return $arResult;
	}

	/**
	 * @deprecated Use CSmileSet::getListForForm
	 */
	public static function getFormList($bWithOptionAll = false, $lang = LANGUAGE_ID)
	{
		return self::getListForForm(0, $lang);
	}

	public static function getListForForm($galleryId = 0, $lang = LANGUAGE_ID)
	{
		$arGalleryList = Array();
		if (!$galleryId)
		{
			$arGalleryList = CSmileGallery::getListForForm($lang);
		}

		$arSetList = Array();
		foreach (CSmileSet::getListCache($lang) as $key => $value)
		{
			if ($galleryId > 0 && $value['PARENT_ID'] != $galleryId)
				continue;

			$arSetList[$key] = !empty($value['NAME'])? $value['NAME']: GetMessage('MAIN_SMILE_SET_NAME', Array('#ID#' => $key));
			if (count($arGalleryList) > 1)
			{
				$arSetList[$key] = $arGalleryList[$value['PARENT_ID']].' > '.$arSetList[$key];
			}
		}

		return $arSetList;
	}

	/**
	 * @deprecated Use CSmileGallery::getDefaultId()
	 */
	public static function getConfigSetId()
	{
		$setId = COption::GetOptionString("main", "smile_set_id", self::SET_ID_ALL);

		return $setId;
	}
}

