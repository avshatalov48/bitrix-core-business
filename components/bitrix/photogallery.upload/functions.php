<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main;

class CPhotoUploader
{
	protected $iblockId;
	protected $gallery;
	protected $arParams;
	protected $arResult; //just for event

	protected $watermark = null;
	var $post = [];

	function __construct(&$arParams, &$gallery, &$arResult)
	{
		$this->iblockId = $arParams["IBLOCK_ID"];
		$this->gallery = $gallery;

		$this->arParams = &$arParams;
		$this->arResult = &$arResult;
	}

	private function getWatermarkRules()
	{
		if ($this->watermark)
		{
			return $this->watermark;
		}

		$watermark = [
			'type' => mb_strtolower($this->arParams['WATERMARK_TYPE']),
			'text' => trim($this->arParams['WATERMARK_TEXT']),
			'font' => trim($this->arParams['PATH_TO_FONT']),
			'position' => $this->arParams['WATERMARK_POSITION'],
			'color' => trim($this->arParams['WATERMARK_COLOR']),
			'size' => $this->arParams['WATERMARK_SIZE'],
			'fill' => $this->arParams['WATERMARK_FILE_ORDER'] == 'usual' ? 'exact' : $this->arParams['WATERMARK_FILE_ORDER'], // resize | exact | repeat
			'file' => $this->arParams['WATERMARK_FILE'], // file on the server
			'alpha_level' => $this->arParams['WATERMARK_TRANSPARENCY'],
			'use_copyright' => 'N', // Y | N
		];
		if ($this->arParams['WATERMARK_RULES'] == 'ALL')
		{
			if($this->arParams['WATERMARK_TYPE'] == 'TEXT')
			{
				$watermark['use_copyright'] = "N";
			}
			else
			{
				$watermark['size'] = 'real';
			}
		}
		elseif (($this->post['photo_watermark_use'] ?? null) == 'Y')
		{
			$watermark = array_merge($watermark, array(
				'type' => $this->post['photo_watermark_type'],
				'text' => $this->post['photo_watermark_text'],
				'position' => $this->post['photo_watermark_position'],
				'color' => $this->post['photo_watermark_color'],
				'size' => $this->post['photo_watermark_size'],
				'fill' => 'resize', // resize | exact | repeat
				'file' => $this->post['photo_watermark_file'],
				'alpha_level' => $this->post['photo_watermark_opacity'],
				'use_copyright' => $this->post["photo_watermark_copyright"] == "Y" ? "Y" : "N"
			));
		}

		// We have ugly default font but it's better than no font at all
		if ($watermark['font'] <> '')
		{
			$documentRoot = Main\Application::getDocumentRoot();
			$paths = [
				[
					$documentRoot,
					$watermark['font']
				],
				[
					$documentRoot,
					BX_ROOT,
					'modules',
					'photogallery',
					'fonts',
					$watermark['font']
				]
			];
			$watermark['font'] = '';
		}
		foreach ($paths as $path)
		{
			$file = new Main\IO\File(implode(Main\IO\Path::DIRECTORY_SEPARATOR, $path));
			if ($file->isExists())
			{
				$watermark['font'] = $file->getPhysicalPath();
				break;
			}
		}
		if ($watermark['file'] <> '')
		{
			$file = new Main\IO\File(Main\Application::getDocumentRoot().Main\IO\Path::DIRECTORY_SEPARATOR.$watermark['file']);
			$watermark['file'] = $file->isExists() ? $file->getPhysicalPath() : '';
		}
		if (
			$watermark['type'] == 'text' && $watermark['font'] <> '' && $watermark['text'] <> '' ||
			$watermark['type'] !== 'text' && $watermark['file'] <> '')
		{
			$this->watermark = $watermark;
		}
		else
		{
			$this->watermark = [];
		}
		return $this->watermark;
	}
	private function adjustIBlock($arParams)
	{
		//region File properties
		$properties = [];
		foreach ($arParams['converters'] as $key => $val)
		{
			if ($val['code'] == "thumbnail")
				continue;
			$code = mb_strtoupper($val['code']);
			if (!($res = CIBlock::GetProperties($this->iblockId, [], ["CODE" => $code])->fetch()))
			{
				$properties[] = $code;
			}
		}

		if (!empty($properties))
		{
			$obProperty = new CIBlockProperty;
			foreach ($properties as $code)
			{
				$obProperty->Add(array(
					"IBLOCK_ID" => $this->iblockId,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "F",
					"MULTIPLE" => "N",
					"NAME" => (GetMessage("P_".$code) <> '' ? GetMessage("P_".$code) : $code),
					"CODE" => $code,
					"FILE_TYPE" => "jpg, gif, bmp, png, jpeg"
				));
			}
		}
		//endregion
		//region Moderation properties
		$properties = [];
		foreach (["PUBLIC_ELEMENT", "APPROVE_ELEMENT"] as $code)
		{
			if (!($res = CIBlock::GetProperties($this->iblockId, [], ["CODE" => $code])->fetch()))
			{
				$properties[] = $code;
			}
		}

		if (count($properties) > 0)
		{
			$obProperty = new CIBlockProperty;
			foreach ($properties as $code)
			{
				$obProperty->Add(array(
					"IBLOCK_ID" => $this->iblockId,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "S",
					"MULTIPLE" => "N",
					"NAME" => (GetMessage("P_".$code) <> '' ? GetMessage("P_".$code) : $code),
					"DEFAULT_VALUE" => "N",
					"CODE" => $code
				));
			}
		}
		//endregion
		return true;
	}

	public function onBeforeUpload(&$package, &$upload, $post, $files, &$error)
	{
		$this->post = $post;
		return true;
	}

	public function onAfterUpload($packageLog, &$uploadLog, $files)
	{
		$sectionIds = [0, $uploadLog['SECTION_ID']];
		$galleryIds = [0];
		$userIds = [];
		if ($this->gallery)
		{
			$sectionIds[] = $this->gallery["ID"];
			$galleryIds[] = $this->gallery["CODE"];
			$userIds[] = $this->gallery["CREATED_BY"];
		}
		PClearComponentCacheEx($this->iblockId, $sectionIds, $galleryIds, $userIds);

		$uploadLog["redirectUrl"] = CComponentEngine::MakePathFromTemplate(
			$this->arParams["~SECTION_URL"],
			[
				"USER_ALIAS" => $this->gallery ? $this->gallery['CODE'] : '',
				"SECTION_ID" => $uploadLog['SECTION_ID']
			]
		);
		return true;
	}

	private function getSectionId(&$upload)
	{
		if ($upload["SECTION_ID"] > 0)
		{
			return $upload["SECTION_ID"];
		}

		if ($this->post["photo_album_id"] === 'new')
		{
			$upload["SECTION_ID"] = $this->createAlbum($this->post["new_album_name"]);
			$upload["NEW_SECTION_ID"] = $upload["SECTION_ID"];
		}
		else
		{
			$upload["SECTION_ID"] = $this->post["photo_album_id"];
		}

		if ($upload["SECTION_ID"] <= 0)
		{
			throw new Main\ArgumentNullException('Album is not created or does not exist.');
		}

		return $upload["SECTION_ID"];
	}

	protected function createAlbum($name)
	{
		$name = trim($name);
		$name = ($name <> '' ? $name : GetMessage("P_NEW_ALBUM"));
		$name = ($name <> '' ? $name : "New album");

		$arFields = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $this->iblockId,
			"IBLOCK_SECTION_ID" => $this->gallery ? $this->gallery['ID'] : 0,
			"DATE" => ConvertTimeStamp(time()+CTimeZone::GetOffset()),
			"UF_DATE" => ConvertTimeStamp(time()+CTimeZone::GetOffset()),
			"NAME" => $name
		);
		$GLOBALS["UF_DATE"] = $arFields["UF_DATE"];

		$bs = new CIBlockSection;
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_{$this->iblockId}_SECTION", $arFields);

		if ($ID = $bs->Add($arFields))
		{
			CIBlockSection::ReSort($this->iblockId);
		}
		return ($ID > 0 ? $ID : false);
	}

	protected function createCoverForNewAlbum(&$upload, $photo)
	{
		if ($upload["NEW_SECTION_ID"] > 0)
		{
			$file = $photo["files"]["default"]; // Big picture
			$file['~tmp_name'] = $file['tmp_name'];
			$file['tmp_name'] .= "_album_cover.tmp";

			if (CFile::ResizeImageFile($file['tmp_name'], $file['tmp_name_1'],
				[
					'width' => $this->arParams["ALBUM_PHOTO_THUMBS"]["SIZE"] ?? null,
					'height' => $this->arParams["ALBUM_PHOTO_THUMBS"]["SIZE"] ?? null
				],
				BX_RESIZE_IMAGE_PROPORTIONAL)
			)
			{
				$bs = new CIBlockSection;
				if ($bs->Update($upload["SECTION_ID"], ["PICTURE" => $file], false, false))
				{
					unset($upload["NEW_SECTION_ID"]);
				}
			}
		}
	}

	function handleFile($hash, $photo, &$package, &$upload, &$error)
	{
		try
		{
			$arParams = $this->arParams;

			if (!Main\Loader::includeModule('iblock'))
			{
				throw new main\NotSupportedException('Iblock module is not included.');
			}

			$sectionId = $this->getSectionId($upload);
			$this->arParams["SECTION_ID"] = $sectionId;
			$this->adjustIBlock($this->arParams);

			$this->arParams["bxu"]->checkCanvases(
				$hash,
				$photo,
				$this->arParams['converters'],
				$this->getWatermarkRules()
			);

			// Props
			$publish = (($this->post["Public"] ?? null) == "N" ? "N" : "Y");
			$approve = ($publish === "Y" && ($arParams["ABS_PERMISSION"] >= "U" || $arParams["APPROVE_BY_DEFAULT"] == "Y") ? "Y" : "X");
			$Prop = [
				"REAL_PICTURE" => ["n0" => $photo["files"]["default"]],
				"PUBLIC_ELEMENT" => ["n0" => $publish],
				"APPROVE_ELEMENT" => ["n0" => $approve]];

			foreach ($arParams['converters'] as $val)
			{
				if ($val['code'] != "default" && $val['code'] != "thumbnail")
				{
					$code = mb_strtoupper($val['code']);
					$Prop[$code] = ["n0" => $photo["files"][$val['code']]];
				}
			}
			global $USER;
			// Real photo
			$arFields = Array(
				"ACTIVE" => (($arParams["MODERATION"] == "Y" && $arParams["ABS_PERMISSION"] < "U") ? "N" : "Y"),
				"MODIFIED_BY" => $USER->GetID(),
				"IBLOCK_SECTION" => $upload['SECTION_ID'],
				"IBLOCK_ID" => $this->iblockId,
				"NAME" => $photo['name'],
				"CODE" => $photo['name'],
				"TAGS" => $photo['tags'] ?? null,
				"DETAIL_TEXT" => $photo['description'],
				"DETAIL_TEXT_TYPE" => "text",
				"PREVIEW_PICTURE" => $photo["files"]["thumbnail"],
				"PREVIEW_TEXT" => $photo['description'],
				"PREVIEW_TEXT_TYPE" => "text",
				"PROPERTY_VALUES" => $Prop
			);

			$bs = new CIBlockElement;

			$id = $bs->Add($arFields);
			if ($id <= 0)
			{
				throw new Main\NotImplementedException($bs->LAST_ERROR);
			}
			$arFields['ID'] = $id;
			$_SESSION['arUploadedPhotos'][] = $id;
			CIBlockElement::RecalcSections($id);

			foreach(GetModuleEvents("photogallery", "OnAfterUpload", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(&$arFields, $this->arParams, $this->arResult));
			}

			$this->createCoverForNewAlbum($upload, $photo);
		}
		catch(\Throwable $exception)
		{
			$error = $exception->getMessage();
			return false;
		}
		return $id;
	}
}
function getImageUploaderId($str = 'bx_img_upl_')
{
	static $iIndexOnPage = 0;
	$iIndexOnPage++;
	return $str . $iIndexOnPage;
}

if (!function_exists("_get_size"))
{
	function _get_size($v)
	{
		$l = mb_substr($v, -1);
		$ret = mb_substr($v, 0, -1);
		switch(mb_strtoupper($l))
		{
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'K':
				$ret /= 1024;
				break;
		}
		return $ret;
	}
}
?>