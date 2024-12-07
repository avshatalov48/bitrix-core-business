<?php

IncludeModuleLangFile(__FILE__);

class blogTextParser extends CTextParser
{
	public $bPublic = false;
	public $bPreview = false;
	public $pathToUserEntityId = false;
	public $pathToUserEntityType = false;
	public $smilesGallery = 0;
	public $maxStringLen = 100;

	public $blogImageSizeEvents = null;
	public $arUserfields = [];

	private $arImages = array();
	
	public $showedImages = array();

	public $isSonetLog = false;
	public $MaxStringLen = null;

//	max sizes for show image in popup
	const IMAGE_MAX_SHOWING_WIDTH = 1000;
	const IMAGE_MAX_SHOWING_HEIGHT = 1000;

	public function __construct($strLang = False, $pathToSmile = false, $arParams = array())
	{
		parent::__construct();
		global $CACHE_MANAGER;
		if ($strLang===False)
			$strLang = LANGUAGE_ID;

		$this->imageWidth = \Bitrix\Blog\Util::getImageMaxWidth();
		$this->imageHeight = \Bitrix\Blog\Util::getImageMaxHeight();
		$this->showedImages = array();
		$this->ajaxPage = $GLOBALS["APPLICATION"]->GetCurPageParam("", array("bxajaxid", "logout"));
		$this->blogImageSizeEvents = GetModuleEvents("blog", "BlogImageSize", true);
		$this->arUserfields = array();
		$this->bPublic = (is_array($arParams) && ($arParams["bPublic"] ?? false));
		$this->bPreview = (is_array($arParams) && ($arParams["bPreview"] ?? false));
		$this->smilesGallery = \COption::GetOptionInt("blog", "smile_gallery_id", 0);
	}

	public function convert($text, $bPreview = True, $arImages = array(), $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N", "SHORT_ANCHOR" => "N"), $arParams = Array())
	{
		if(!is_array($arParams) && $arParams <> '')
		{
			$type = $arParams;
		}
		elseif(is_array($arParams))
		{
			$type = $arParams["type"] ?? '';
		}
		if (intval($arParams["imageWidth"] ?? 0) > 0)
		{
			$this->imageWidth = intval($arParams["imageWidth"]);
		}
		if (intval($arParams["imageHeight"] ?? 0) > 0)
		{
			$this->imageHeight = intval($arParams["imageHeight"]);
		}
		if (($arParams["pathToUser"] ?? '') <> '')
		{
			$this->pathToUser = $arParams["pathToUser"];
		}
		if (!empty($arParams["pathToUserEntityType"]) && $arParams["pathToUserEntityType"] <> '')
		{
			$this->pathToUserEntityType = $arParams["pathToUserEntityType"];
		}
		if (intval($arParams["pathToUserEntityId"] ?? 0) > 0)
		{
			$this->pathToUserEntityId = intval($arParams["pathToUserEntityId"]);
		}
		$this->parser_nofollow = COption::GetOptionString("blog", "parser_nofollow", "N");

		$this->type = ($type == "rss" ? "rss" : "html");
		$this->isSonetLog = $arParams["isSonetLog"] ?? false;

		$this->allow = array(
			"HTML" => (($allow["HTML"] ?? null) == "Y" ? "Y" : "N"),
			"NL2BR" => (($allow["NL2BR"] ?? null) == "Y" ? "Y" : "N"),
			"CODE" => (($allow["CODE"] ?? null) == "N" ? "N" : "Y"),
			"VIDEO" => (($allow["VIDEO"] ?? null) == "N" ? "N" : "Y"),
			"ANCHOR" => (($allow["ANCHOR"] ?? null) == "N" ? "N" : "Y"),
			"BIU" => (($allow["BIU"] ?? null) == "N" ? "N" : "Y"),
			"IMG" => (($allow["IMG"] ?? null) == "N" ? "N" : "Y"),
			"QUOTE" => (($allow["QUOTE"] ?? null) == "N" ? "N" : "Y"),
			"FONT" => (($allow["FONT"] ?? null) == "N" ? "N" : "Y"),
			"LIST" => (($allow["LIST"] ?? null) == "N" ? "N" : "Y"),
			"SMILES" => (($allow["SMILES"] ?? null) == "N" ? "N" : "Y"),
			"TABLE" => (($allow["TABLE"] ?? null) == "N" ? "N" : "Y"),
			"ALIGN" => (($allow["ALIGN"] ?? null) == "N" ? "N" : "Y"),
			"CUT_ANCHOR" => (($allow["CUT_ANCHOR"] ?? null) == "Y" ? "Y" : "N"),
			"SHORT_ANCHOR" => (($allow["SHORT_ANCHOR"] ?? null) == "Y" ? "Y" : "N"),
			"USER" => (($allow["USER"] ?? null) == "N" ? "N" : "Y"),
			"USER_LINK" => (($allow["USER_LINK"] ?? null) == "N" ? "N" : "Y"),
			"TAG" => (($allow["TAG"] ?? null) == "N" ? "N" : "Y"),
			'SPOILER' => (($allow['SPOILER'] ?? null) === 'N' ? 'N' : 'Y'),
			"USERFIELDS" => (
				(isset($allow["USERFIELDS"]) && is_array($allow["USERFIELDS"]))
				? $allow["USERFIELDS"]
				: []
			)
		);
		if (!empty($this->arUserfields))
			$this->allow["USERFIELDS"] = array_merge($this->allow["USERFIELDS"], $this->arUserfields);

		$this->arImages = $arImages;
		$this->bPreview = $bPreview;

		static $firstCall = true;
		if ($firstCall)
		{
			$firstCall = false;
			AddEventHandler("main", "TextParserBefore", Array("blogTextParser", "ParserCut"));
			AddEventHandler("main", "TextParserBefore", Array("blogTextParser", "ParserBlogImageBefore"));
			AddEventHandler("main", "TextParserAfterTags", Array("blogTextParser", "ParserBlogImage"));
			AddEventHandler("main", "TextParserAfterTags", Array("blogTextParser", "ParserTag"));
			AddEventHandler("main", "TextParserAfter", Array("blogTextParser", "ParserCutAfter"));
			AddEventHandler("main", "TextParserVideoConvert", Array("blogTextParser", "blogConvertVideo"));
		}

		$attributes = !empty($arParams) && is_array($arParams) && isset($arParams["ATTRIBUTES"]) ? $arParams["ATTRIBUTES"] : [];
		$text = $this->convertText($text, $attributes);

		return trim($text);
	}

	public static function ParserCut(&$text, &$obj)
	{
		if (($obj instanceof blogTextParser) && $obj->bPreview)
		{
			$text = preg_replace("#^(.*?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
			$text = preg_replace("#^(.*?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
		}
		else
		{
			$text = preg_replace("#<cut[\s]*(/>|>)#is", "[cut]", $text);
		}
	}
	public static function ParserCutAfter(&$text, &$obj)
	{
		if (!($obj instanceof blogTextParser) || !$obj->bPreview)
		{
			$text = preg_replace("#\[cut[\s]*(/\]|\])#is", "<a name=\"cut\"></a>", $text);
		}
	}
	
	public static function ParserBlogImageBefore(&$text, &$obj = null)
	{
		$text = preg_replace("/\[img([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/isu", "[imag id=\\1 \\2 \\3]", $text);
	}
	
	public static function ParserBlogImage(&$text, &$obj)
	{
		if(is_callable(array($obj, 'convert_blog_image')))
		{
			$text = preg_replace_callback(
				"/\[imag([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/isu",
				array($obj, "convertBlogImage"),
				$text
			);
		}
	}

	private function convertBlogImage($matches)
	{
		return $this->convert_blog_image($matches[1], $matches[2], $matches[3]);
	}

	private function convertBlogImageMail($matches)
	{
		return $this->convert_blog_image('', $matches[2], '', 'mail');
	}

	public static function ParserTag(&$text, &$obj)
	{
		if (
			($obj->allow["TAG"] ?? null) !== "N"
			&& is_callable(array($obj, 'convert_blog_tag'))
		)
		{
			$text = preg_replace_callback(
				"/\[tag(?:[^\]])*\](.+?)\[\/tag\]/isu",
				array($obj, "convertBlogTag"),
				$text
			);
		}
	}

	private function convertBlogTag($matches)
	{
		return $this->convert_blog_tag($matches[1]);
	}

	private function convert_blog_tag($name = "")
	{
		if($name == '')
			return;
		return "TAG [".$name."]";
	}

	function convert4im($text, $arImages = [])
	{
		$text = preg_replace(
			[
				"/\[(\/?)(code|quote)([^\]]*)\]/isu",
				"/\\[url\\s*=\\s*(\\S+?)\\s*\\](.*?)\\[\\/url\\]/isu",
				"/\\[(table)(.*?)\\]/isu",
				"/\\[\\/table(.*?)\\]/isu"
			],
			[
				'',
				"\\2",
				"\n",
				"\n",
			],
			$text
		);

		return $this->convert4mail($text, $arImages);
	}

	public function convert4mail($text, $arImages = Array())
	{
		$text = parent::convert4mail($text);

		$this->arImages = $arImages;

		$text = preg_replace_callback(
			"/\[img([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/isu",
			array($this, "convertBlogImageMail"),
			$text
		);

		return $text;
	}

	private function convert_blog_image($p1 = "", $imageId = "", $p2 = "", $type = "html")
	{
		$imageId = intval($imageId);
		if($imageId <= 0)
			return;
		
		$res = "";
		if(intval($this->arImages[$imageId]) > 0)
		{
			$this->showedImages[] = $imageId;
			if($f = CBlogImage::GetByID($imageId))
			{
				if(COption::GetOptionString("blog", "use_image_perm", "N") == "N")
				{
					if($db_img_arr = CFile::GetFileArray($this->arImages[$imageId]))
					{
						if(mb_substr($db_img_arr["SRC"], 0, 1) == "/")
							$strImage = $this->serverName.$db_img_arr["SRC"];
						else
							$strImage = $db_img_arr["SRC"];
						
						$strPar = "";
						preg_match("/width\=([0-9]+)/isu", $p1, $width);
						preg_match("/height\=([0-9]+)/isu", $p1, $height);
						$width = intval($width[1]);
						$height = intval($height[1]);

						if($width <= 0)
						{
							preg_match("/width\=([0-9]+)/isu", $p2, $width);
							$width = intval($width[1]);
						}
						if($height <= 0)
						{
							preg_match("/height\=([0-9]+)/isu", $p2, $height);
							$height = intval($height[1]);
						}

						if(intval($width) <= 0)
							$width = $db_img_arr["WIDTH"];
						if(intval($height) <= 0)
							$height= $db_img_arr["HEIGHT"];

						if($width > $this->imageWidth || $height > $this->imageHeight)
						{
							$arFileTmp = CFile::ResizeImageGet(
								$db_img_arr,
								array("width" => $this->imageWidth, "height" => $this->imageHeight),
								BX_RESIZE_IMAGE_PROPORTIONAL,
								true
							);
							if(mb_substr($arFileTmp["src"], 0, 1) == "/")
								$strImage = $this->serverName.$arFileTmp["src"];
							else
								$strImage = $arFileTmp["src"];
							$width = $arFileTmp["width"];
							$height = $arFileTmp["height"];
						}
						
						$sourceImage = $this->serverName."/bitrix/components/bitrix/blog/show_file.php?fid=".$imageId;
//						if original size bigger than limits - need resize
						if($db_img_arr["WIDTH"] > blogTextParser::IMAGE_MAX_SHOWING_WIDTH)
							$sourceImage .= "&width=".blogTextParser::IMAGE_MAX_SHOWING_WIDTH;
						if($db_img_arr["HEIGHT"] > blogTextParser::IMAGE_MAX_SHOWING_HEIGHT)
							$sourceImage .= "&height=".blogTextParser::IMAGE_MAX_SHOWING_HEIGHT;

						$strPar = 'style=" width:'.$width.'px; height:'.$height.'px;"';
						$strImage = preg_replace("'(?<!:)/+'s", "/", $strImage);
						$sourceImage = preg_replace("'(?<!:)/+'s", "/", $sourceImage);

						if($this->authorName <> '')
							$strPar .= " data-bx-title=\"".$this->authorName."\"";

						if ($this->isSonetLog)
						{
							$strImage = preg_replace("'(?<!:)/+'s", "/", $strImage);
							$res = '[IMG]'.$strImage.'[/IMG]';
						}
						else
						{

							if($type == "mail")
								$res = htmlspecialcharsbx($f["TITLE"])." (IMAGE: ".$strImage." )";
							else
								$res = '<img src="'.$strImage.'" title="" alt="'.htmlspecialcharsbx($f["TITLE"]).'" border="0"'.$strPar.' data-bx-image="'.$sourceImage.'" />';
						}
					}
				}
				else
				{
					preg_match("/width\=([0-9]+)/isu", $p1, $width);
					preg_match("/height\=([0-9]+)/isu", $p1, $height);
					$width = intval($width[1]);
					$height = intval($height[1]);

					if($width <= 0)
					{
						preg_match("/width\=([0-9]+)/isu", $p2, $width);
						$width = intval($width[1]);
					}
					if($height <= 0)
					{
						preg_match("/height\=([0-9]+)/isu", $p2, $height);
						$height = intval($height[1]);
					}

					if(intval($width) <= 0)
						$width = $this->imageWidth;
					if(intval($height) <= 0)
						$height = $this->imageHeight;

					if($width > $this->imageWidth)
						$width = $this->imageWidth;
					if($height > $this->imageHeight)
						$height = $this->imageHeight;
					
					$db_img_arr = CFile::GetFileArray($this->arImages[$imageId]);
					
					$strImage = $this->serverName."/bitrix/components/bitrix/blog/show_file.php?fid=".$imageId."&width=".$width."&height=".$height;
					$sourceImage = $this->serverName."/bitrix/components/bitrix/blog/show_file.php?fid=".$imageId;
//					if original size bigger than limits - need resize
					if($db_img_arr["WIDTH"] > blogTextParser::IMAGE_MAX_SHOWING_WIDTH)
						$sourceImage .= "&width=".blogTextParser::IMAGE_MAX_SHOWING_WIDTH;
					if($db_img_arr["HEIGHT"] > blogTextParser::IMAGE_MAX_SHOWING_HEIGHT)
						$sourceImage .= "&height=".blogTextParser::IMAGE_MAX_SHOWING_HEIGHT;
					
					CFile::ScaleImage($db_img_arr["WIDTH"], $db_img_arr["HEIGHT"], Array("width" => $width, "height" => $height), BX_RESIZE_IMAGE_PROPORTIONAL, $bNeedCreatePicture, $arSourceSize, $arDestinationSize);

					if ($this->isSonetLog)
					{
						$strImage = preg_replace("'(?<!:)/+'s", "/", $strImage);
						$res = '[IMG]'.$strImage.'[/IMG]';
					}
					else
					{
						if($type == "mail")
							$res = htmlspecialcharsbx($f["TITLE"])." (IMAGE: ".$strImage." )";
						else
						{
							$strPar = ' width="'.$arDestinationSize["width"].'" height="'.$arDestinationSize["height"].'"';
							if($this->authorName <> '')
								$strPar .= " data-bx-title=\"".$this->authorName."\"";

							$res = '<img src="'.$strImage.'" title="" alt="'.htmlspecialcharsbx($f["TITLE"]).'" border="0" data-bx-image="'.$sourceImage.'"'.$strPar.' />';
							if(!empty($this->blogImageSizeEvents))
							{
								foreach($this->blogImageSizeEvents as $arEvent)
									ExecuteModuleEventEx($arEvent, Array(&$res, $strImage, $db_img_arr, $f, $arDestinationSize));
							}
						}
					}
				}
				return $res;
			}
		}
		return $res;
	}

	public function convert_to_rss($text, $arImages = Array(), $arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N"), $bPreview = true, $arParams = Array())
	{
		$arParams["type"] = "rss";
		$text = $this->convert($text, $bPreview, $arImages, $arAllow, $arParams);
		return trim($text);
	}

	function convert_open_tag($marker = "quote")
	{
		$marker = (mb_strtolower($marker) == "code" ? "code" : "quote");
		$this->{$marker."_open"}++;
		if ($this->type == "rss")
			return "\n====".$marker."====\n";
		return "<div class='blog-post-".$marker."' title=\"".GetMessage("BLOG_".mb_strtoupper($marker))."\"><table class='blog".$marker."'><tr><td>";
	}

	public static function blogConvertVideo(&$arParams)
	{
		$video = "";
		$bEvents = false;
		foreach(GetModuleEvents("blog", "videoConvert", true) as $arEvent)
		{
			
			$video = ExecuteModuleEventEx($arEvent, Array(&$arParams));
			$bEvents = true;
		}

		if(!$bEvents)
		{
			ob_start();
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:player", "",
				Array(
					"PLAYER_TYPE" => "auto",
					"USE_PLAYLIST" => "N",
					"PATH" => $arParams["PATH"],
					"WIDTH" => $arParams["WIDTH"],
					"HEIGHT" => $arParams["HEIGHT"],
					"PREVIEW" => $arParams["PREVIEW"],
					"LOGO" => "",
					"FULLSCREEN" => "Y",
					"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
					"SKIN" => "bitrix.swf",
					"CONTROLBAR" => "bottom",
					"WMODE" => "transparent",
					"HIDE_MENU" => "N",
					"SHOW_CONTROLS" => "Y",
					"SHOW_STOP" => "N",
					"SHOW_DIGITS" => "Y",
					"CONTROLS_BGCOLOR" => "FFFFFF",
					"CONTROLS_COLOR" => "000000",
					"CONTROLS_OVER_COLOR" => "000000",
					"SCREEN_COLOR" => "000000",
					"AUTOSTART" => "N",
					"REPEAT" => "N",
					"VOLUME" => "90",
					"DISPLAY_CLICK" => "play",
					"MUTE" => "N",
					"HIGH_QUALITY" => "Y",
					"ADVANCED_MODE_SETTINGS" => "N",
					"BUFFER_LENGTH" => "10",
					"DOWNLOAD_LINK" => "",
					"DOWNLOAD_LINK_TARGET" => "_self"),
					null,
					array(
						"HIDE_ICONS" => "Y"
					)
				);
			$video = ob_get_contents();
			ob_end_clean();
		}
		return $video;
	}

	public static function killAllTags($text)
	{
		if (method_exists("CTextParser", "clearAllTags"))
			return CTextParser::clearAllTags($text);

		$text = strip_tags($text);
		$text = preg_replace(
			array(
				"/\<(\/)(quote|code)([^\>]*)\>/isu",
				"/\[(\/)(code|quote|video|td|tr|th|table|tbody|thead|file|document|disk)([^\]]*)\]/isu",
				"/\[(\/?)(\*)([^\]]*)\]/isu",
				),
			" ",
			$text);
		$text = preg_replace(
			array(
				"/\<(\/?)(quote|code|font|color|video)([^\>]*)\>/isu",
				"/\[(\/?)(b|u|i|s|list|code|quote|font|color|url|img|video|td|tr|th|tbody|thead|table|file|document|disk|user|left|right|center|justify)([^\]]*)\]/isu"
				),
			"",
			$text);
		return $text;
	}

	function render_user($fields)
	{
		$classAdditional = (!empty($fields['CLASS_ADDITIONAL']) ? $fields['CLASS_ADDITIONAL'] : '');
		$pathToUser = (!empty($fields['PATH_TO_USER']) ? $fields['PATH_TO_USER'] : '');
		$userId = (!empty($fields['USER_ID']) ? $fields['USER_ID'] : '');
		$userName = (!empty($fields['USER_NAME']) ? $fields['USER_NAME'] : '');

		$anchorId = RandString(8);
		
		return (
			$this->allow["USER_LINK"] == "N"
				? $userName
				: '<a class="blog-p-user-name' . $classAdditional . '" id="bp_'.$anchorId.'" href="'.CComponentEngine::MakePathFromTemplate($pathToUser, array("user_id" => $userId)).'" bx-tooltip-user-id="'.(!$this->bMobile ? $userId : '').'">'.$userName.'</a>'
		);
	}
	
	private static function getEditorDefaultFeatures()
	{
		return array("Bold","Italic","Underline","SmileList","RemoveFormat","Quote","Code"/*,"Source"*/);
	}
	
	private static function getEditorExtendFeatures()
	{
		return array(
			"EDITOR_USE_FONT" => array("FontList", "FontSizeList","ForeColor"),
			"EDITOR_USE_LINK" => array("CreateLink"),
			"EDITOR_USE_IMAGE" => array("UploadImage","Image"),
			"EDITOR_USE_FORMAT" => array("Strike","Table","Justify","InsertOrderedList","InsertUnorderedList"),
			"EDITOR_USE_VIDEO" => array("InputVideo")
		);
	}
	
	public static function GetEditorToolbar($params, $arResult = null)
	{
		if(isset($params["blog"]))
		{
			$blog = $params["blog"];
		}
		else
		{
			$blog = array();
			$params = array("EDITOR_FULL" => "Y");
		}
		$editorFull = isset($params["EDITOR_FULL"]) && $params["EDITOR_FULL"] == "Y";
		
		$defaultFeatures = self::getEditorDefaultFeatures();
		$extendFeatures = self::getEditorExtendFeatures();
		
//		if set FULL flag - use ALL features. If other - use features by blog settings
		$result = $defaultFeatures;
		if($editorFull)
		{
			foreach($extendFeatures as $key => $feature)
				$result = array_merge($result, $feature);
		}
		else
		{
			foreach($extendFeatures as $key => $feature)
			{
//				use feature name as key to can remove then later
				if(isset($blog[$key]) && $blog[$key] == "Y")
					foreach($feature as $f)
						$result[$f] = $f;
			}
		}
		
//		UNSET not allowed by component settings features
		if(is_array($arResult) && !$arResult["allowVideo"])
			foreach($extendFeatures["EDITOR_USE_VIDEO"] as $f)
				unset($result[$f]);
		
		if(is_array($arResult) && $arResult["NoCommentUrl"])
			foreach($extendFeatures["EDITOR_USE_LINK"] as $f)
				unset($result[$f]);

		if (LANGUAGE_ID == 'ru')
			$result[] = 'Translit';
		
		return $result;
	}
	
	public static function getEditorButtons($blog, $arResult)
	{
		$result = array();
		
		// IMAGES or FILES
		if(
			is_array($arResult["COMMENT_PROPERTIES"]["DATA"])
			&& (
				array_key_exists("UF_BLOG_COMMENT_FILE", $arResult["COMMENT_PROPERTIES"]["DATA"])
				|| array_key_exists("UF_BLOG_COMMENT_DOC", $arResult["COMMENT_PROPERTIES"]["DATA"])
			)
			&& array_key_exists('EDITOR_USE_IMAGE', $blog) && $blog["EDITOR_USE_IMAGE"] === "Y"
		)
		{
			$result[] = "UploadFile";
		}
		
		// VIDEO
		if($arResult["allowVideo"] && (isset($blog["EDITOR_USE_VIDEO"]) && $blog["EDITOR_USE_VIDEO"] === "Y"))
		{
			$result[] = "InputVideo";
		}
		
		// LINK
		if(!$arResult["NoCommentUrl"] && (isset($blog["EDITOR_USE_LINK"]) && $blog["EDITOR_USE_LINK"] === "Y"))
		{
			$result[] = 'CreateLink';
		}
		
		// OTHER for all
		$result[] = "Quote";
		$result[] = "BlogTag";
		
		return $result;
	}
}

class CBlogTools
{
	public static function htmlspecialcharsExArray($array)
	{
		$res = Array();
		if(!empty($array) && is_array($array))
		{
			foreach($array as $k => $v)
			{
				if(is_array($v))
				{
					foreach($v as $k1 => $v1)
					{
						$res[$k1] = htmlspecialcharsex($v1);
						$res['~'.$k1] = $v1;
					}
				}
				else
				{
					if (preg_match("/[;&<>\"]/", ($v ?? '')))
						$res[$k] = htmlspecialcharsex($v);
					else
						$res[$k] = $v;
					$res['~'.$k] = $v;
				}
			}
		}
		return $res;
	}

	public static function ResizeImage($aFile, $sizeX, $sizeY)
	{
		$arFile = CFile::ResizeImageGet($aFile, array("width"=>$sizeX, "height"=>$sizeY));

		if(is_array($arFile))
			return $arFile["src"];
		else
			return false;
	}

	public static function GetDateTimeFormat()
	{
		$timestamp = mktime(7,30,45,2,22,2007);
		return array(
				"d-m-Y H:i:s" => date("d-m-Y H:i:s", $timestamp),//"22-02-2007 7:30",
				"m-d-Y H:i:s" => date("m-d-Y H:i:s", $timestamp),//"02-22-2007 7:30",
				"Y-m-d H:i:s" => date("Y-m-d H:i:s", $timestamp),//"2007-02-22 7:30",
				"d.m.Y H:i:s" => date("d.m.Y H:i:s", $timestamp),//"22.02.2007 7:30",
				"m.d.Y H:i:s" => date("m.d.Y H:i:s", $timestamp),//"02.22.2007 7:30",
				"j M Y H:i:s" => date("j M Y H:i:s", $timestamp),//"22 Feb 2007 7:30",
				"M j, Y H:i:s" => date("M j, Y H:i:s", $timestamp),//"Feb 22, 2007 7:30",
				"j F Y H:i:s" => date("j F Y H:i:s", $timestamp),//"22 February 2007 7:30",
				"F j, Y H:i:s" => date("F j, Y H:i:s", $timestamp),//"February 22, 2007",
				"d.m.y g:i A" => date("d.m.y g:i A", $timestamp),//"22.02.07 1:30 PM",
				"d.m.y G:i" => date("d.m.y G:i", $timestamp),//"22.02.07 7:30",
				"d.m.Y H:i:s" => date("d.m.Y H:i:s", $timestamp),//"22.02.2007 07:30",
			);
	}

	public static function DeleteDoubleBR($text)
	{
		if(mb_strpos($text, "<br />\r<br />") !== false)
		{
			$text = str_replace("<br />\r<br />", "<br />", $text);
			return CBlogTools::DeleteDoubleBR($text);
		}
		if(mb_strpos($text, "<br /><br />") !== false)
		{
			$text = str_replace("<br /><br />", "<br />", $text);
			return CBlogTools::DeleteDoubleBR($text);
		}

		if(mb_strpos($text, "<br />") == 0 && mb_strpos($text, "<br />") !== false)
		{
			$text = mb_substr($text, 6);
		}
		return $text;
	}

	public static function blogUFfileEdit($arResult, $arParams)
	{
		$result = false;
		if (mb_strpos($arParams['arUserField']['FIELD_NAME'], CBlogPost::UF_NAME) === 0 || mb_strpos($arParams['arUserField']['FIELD_NAME'], 'UF_BLOG_COMMENT_DOC') === 0)
		{
			$componentParams = array(
				'INPUT_NAME' => $arParams["arUserField"]["FIELD_NAME"],
				'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
				'INPUT_VALUE' => $arResult["VALUE"],
				'MAX_FILE_SIZE' => intval($arParams['arUserField']['SETTINGS']['MAX_ALLOWED_SIZE']),
				'MULTIPLE' => $arParams['arUserField']['MULTIPLE'],
				'MODULE_ID' => 'uf',
				'ALLOW_UPLOAD' => 'A',
			);

			$GLOBALS["APPLICATION"]->IncludeComponent('bitrix:main.file.input', 'drag_n_drop', $componentParams, false, Array("HIDE_ICONS" => "Y"));

			$result = true;
		}
		return $result;
	}

	public static function blogUFfileShow($arResult, $arParams)
	{
		$result = false;
		if ($arParams['arUserField']['FIELD_NAME'] == CBlogPost::UF_NAME || mb_strpos($arParams['arUserField']['FIELD_NAME'], 'UF_BLOG_COMMENT_DOC') === 0)
		{
			if (sizeof($arResult['VALUE']) > 0)
			{
				?>
					<div class="feed-com-files">
						<div class="feed-com-files-title"><?=GetMessage('BLOG_FILES')?></div>
						<div class="feed-com-files-cont">
				<?
			}
			foreach ($arResult['VALUE'] as $fileID)
			{
				$arFile = CFile::GetFileArray($fileID);
				if($arFile)
				{
					$name = $arFile['ORIGINAL_NAME'];
					$ext = '';
					$dotpos = mb_strrpos($name, ".");
					if (($dotpos !== false) && ($dotpos + 1 < mb_strlen($name)))
						$ext = mb_substr($name, $dotpos + 1);
					if (mb_strlen($ext) < 3 || mb_strlen($ext) > 5)
						$ext = '';
					$arFile['EXTENSION'] = $ext;
					$arFile['LINK'] = "/bitrix/components/bitrix/blog/show_file.php?bp_fid=".$fileID;
					$arFile["FILE_SIZE"] = CFile::FormatSize($arFile["FILE_SIZE"]);
					?>
						<div id="wdif-doc-<?=$arFile['ID']?>" class="feed-com-file-wrap">
							<div class="feed-con-file-name-wrap">
							<div class="feed-con-file-icon feed-file-icon-<?=htmlspecialcharsbx($arFile['EXTENSION'])?>"></div>
							<a target="_blank" href="<?=htmlspecialcharsbx($arFile['LINK'])?>" class="feed-com-file-name"><?=htmlspecialcharsbx($arFile['ORIGINAL_NAME'])?></a>
							<span class="feed-con-file-size">(<?=$arFile['FILE_SIZE']?>)</span>
							</div>
						</div>
					<?
				}
			}
			if (sizeof($arResult['VALUE']) > 0)
			{
				?>
						</div>
					</div>
				<?
			}
			$result = true;
		}
		return $result;
	}
}
