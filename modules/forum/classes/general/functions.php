<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);
function Error($error)
{
	global $MESS;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/".LANGUAGE_ID."/errors.php");
	$msg = $MESS[$error["MSG"]];
	echo "Error: ".$msg;
}

class forumTextParser extends CTextParser
{
	/* @deprecated */ var $image_params = array();
	/* @deprecated */ var $pathToUser = "";
	public $imageWidth = 300;
	public $imageHeight = 300;
	public $maxStringLen = 60;
	public $imageHtmlWidth = 0;
	public $imageHtmlHeight = 0;
	public $imageTemplate = "popup_image";
	public $component = null;
	public $smilesGallery = 0;
	public $arFilesIDParsed = array();
	public $MaxStringLen = null;

	function __construct($lang = false, $pathToSmiles = '', $type=false, $mode = 'full')
	{
		parent::__construct();
		$this->arFiles = array();
		$this->arFilesParsed = array();

		$this->arUserfields = array();
		$this->ajaxPage = $GLOBALS["APPLICATION"]->GetCurPageParam("", array("bxajaxid", "logout"));
		$this->userPath = "";
		$this->userNameTemplate = str_replace(array("#NOBR#","#/NOBR#"), "", CSite::GetDefaultNameFormat());
		$this->smilesGallery = \COption::GetOptionInt("forum", "smile_gallery_id", 0);

		if ($mode == 'full')
		{
			AddEventHandler("main", "TextParserAfterTags", Array(&$this, "ParserFile"));
		}
	}

	public static function GetFeatures($arForum)
	{
		static $arFeatures = [
			"HTML", "ANCHOR", "BIU", "IMG",
			"VIDEO", "LIST", "QUOTE", "CODE",
			"FONT", "UPLOAD", "NL2BR", "SMILES",
			"TABLE", "ALIGN"];
		$result = array();
		if (is_array($arForum))
		{
			foreach ($arFeatures as $feature)
			{
				$result[$feature] = ((isset($arForum['ALLOW_'.$feature]) && $arForum['ALLOW_'.$feature] == 'Y') ? 'Y' : 'N');
			}
		}
		return $result;
	}

	public static function GetEditorButtons($arParams)
	{
		$result = array();
		$arEditorFeatures = array(
			"ALLOW_QUOTE" => array('Quote'),
			'ALLOW_ANCHOR' => array('CreateLink'),
			"ALLOW_VIDEO" => array('InputVideo'),
			"ALLOW_UPLOAD" => array('UploadFile'),
			"ALLOW_MENTION" => array('MentionUser')
		);
		if (isset($arParams['forum']) && is_array($arParams['forum']))
		{
			$res = array_intersect_key($arParams['forum'], $arEditorFeatures);
			foreach ($res as $featureName => $val)
			{
				if ($val != 'N')
					$result = array_merge($result, $arEditorFeatures[$featureName]);
			}
		}
		return $result;
	}

	public static function GetEditorToolbar($arParams)
	{
		static $arEditorFeatures = array(
			"ALLOW_BIU" => array('Bold', 'Italic', 'Underline', 'Strike', 'Spoiler'),
			"ALLOW_FONT" => array('ForeColor','FontList', 'FontSizeList'),
			"ALLOW_QUOTE" => array('Quote'),
			"ALLOW_CODE" => array('Code'),
			'ALLOW_ANCHOR' => array('CreateLink', 'DeleteLink'),
			"ALLOW_IMG" => array('Image'),
			"ALLOW_VIDEO" => array('InputVideo'),
			"ALLOW_TABLE" => array('Table'),
			"ALLOW_ALIGN" => array('Justify'),
			"ALLOW_LIST" => array('InsertOrderedList', 'InsertUnorderedList'),
			"ALLOW_SMILES" => array('SmileList'),
			//"ALLOW_UPLOAD" => array('UploadFile'),
			//"ALLOW_NL2BR" => array(''),
		);
		$result = array();

		if (isset($arParams['mode']) && ($arParams['mode'] == 'full'))
		{
			foreach ($arEditorFeatures as $featureName => $toolbarIcons)
			{
				$result = array_merge($result, $toolbarIcons);
			}
		}
		elseif (isset($arParams['forum']))
		{
			foreach ($arEditorFeatures as $featureName => $toolbarIcons)
			{
				if (isset($arParams['forum'][$featureName]) && ($arParams['forum'][$featureName] == 'Y'))
					$result = array_merge($result, $toolbarIcons);
			}
		}

		$result = array_merge($result, array('MentionUser', 'UploadFile', 'RemoveFormat', 'Source'));
		if (LANGUAGE_ID == 'ru')
			$result[] = 'Translit';

		return $result;
	}

	function convert($text, $allow = array(), $type = "html", $arFiles = false)
	{
		$text = str_replace(array("\013", "\014"), "", $text);

		$this->imageWidth = (isset($this->image_params["width"]) && $this->image_params["width"] > 0 ? $this->image_params["width"] : ($this->imageWidth > 0 ? $this->imageWidth : 300));
		$this->imageHeight = (isset($this->image_params["height"]) && $this->image_params["height"] > 0 ? $this->image_params["height"] : ($this->imageHeight > 0 ? $this->imageHeight : 300));

		$this->userPath = str_replace(array("#UID#", "#uid#"), "#user_id#", (empty($this->userPath) && !empty($this->pathToUser) ? $this->pathToUser : $this->userPath));

		$this->type = $type;

		$allow = (is_array($allow) ? $allow : array());
		if (!empty($this->arUserfields))
			$allow["USERFIELDS"] = $this->arUserfields;

		if (sizeof($allow)>0)
		{
			if (!isset($allow['TABLE']))
				$allow['TABLE']=$allow['BIU'];

			$this->allow = array_merge((is_array($this->allow) ? $this->allow : array()), $allow);
		}
		$this->parser_nofollow = COption::GetOptionString("forum", "parser_nofollow", "Y");
		$this->link_target = COption::GetOptionString("forum", "parser_link_target", "_blank");

		if ($arFiles !== false)
			$this->arFiles = is_array($arFiles) ? $arFiles : array($arFiles);
		$this->arFilesIDParsed = array();

		$text = str_replace(array("\013", "\014"), array(chr(34), chr(39)), $this->convertText($text));
		return $text;
	}

	function convert4mail($text, $files = false, $allow = array(), $params = array())
	{
		$this->arFiles = (is_array($files) ? $files : ($files ? array($files) : array()));
		$this->arFilesIDParsed = array();

		if (!empty($params))
		{
			$mail = array(
				"RECIPIENT_ID" => intval($params["RECIPIENT_ID"]),
				"SITE_ID" => ($params["SITE_ID"] ?: SITE_ID)
			);
			$allow = array_merge(((is_array($allow) ? $allow : array()) + array(
				"HTML" => "N",
				"ANCHOR" => "Y",
				"BIU" => "Y",
				"IMG" => "Y",
				"QUOTE" => "Y",
				"CODE" => "Y",
				"FONT" => "Y",
				"LIST" => "Y",
				"NL2BR" => "N",
				"TABLE" => "Y"
			)), array("SMILES" => "N"));

			$this->RECIPIENT_ID = $mail["RECIPIENT_ID"];
			$this->SITE_ID = $mail["SITE_ID"];

			if (is_array($this->arUserfields))
			{
				foreach ($this->arUserfields as &$f)
				{
					$f += $mail;
				}
			}
			return $this->convert($text, $allow, "mail");
		}
		else
		{
			$text = parent::convert4mail($text);
			if (!empty($this->arFiles))
				$this->ParserFile($text, $this, "mail");
			if (preg_match("/\\[cut(([^\\]])*)\\]/is".BX_UTF_PCRE_MODIFIER, $text, $matches))
			{
				$text = preg_replace(
					array("/\\[cut(([^\\]])*)\\]/is".BX_UTF_PCRE_MODIFIER,
						"/\\[\\/cut\\]/is".BX_UTF_PCRE_MODIFIER),
					array("\001\\1\002",
						"\003"),
					$text);
				while (preg_match("/(\001([^\002]*)\002([^\001\002\003]+)\003)/is".BX_UTF_PCRE_MODIFIER, $text, $arMatches))
					$text = preg_replace(
						"/(\001([^\002]*)\002([^\001\002\003]+)\003)/is".BX_UTF_PCRE_MODIFIER,
						"\n>================== CUT ===================\n\\3\n>==========================================\n",
						$text);
				$text = preg_replace(
					array("/\001([^\002]+)\002/",
						"/\001\002/",
						"/\003/"),
					array("[cut\\1]",
						"[cut]",
						"[/cut]"),
					$text);
			}
		}
		return $text;
	}

	function ParserFile(&$text, &$obj, $type="html")
	{
		if (method_exists($obj, "convert_attachment"))
		{
			$tmpType = $obj->type;
			$obj->type = $type;
			$text = preg_replace_callback("/\[file([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER, array($this, "convert_attachment"), $text);
			$obj->type = $tmpType;
		}
	}

	function convert_open_tag($marker = "quote")
	{
		$marker = (mb_strtolower($marker) == "code" ? "code" : "quote");

		$this->{$marker."_open"}++;
		if ($this->type == "rss")
			return "\n====".$marker."====\n";
		else if ($this->type == "mail")
			return ($marker == "code" ? "<code>" : "<blockquote>");
		else if ($this->bMobile)
			return "<div class='blog-post-".$marker."' title=\"".($marker == "quote" ? GetMessage("FRM_QUOTE") : GetMessage("FRM_CODE"))."\"><table class='blog".$marker."'><tr><td>";
		else
			return '<div class="entry-'.$marker.'"><table class="forum-'.$marker.'"><thead><tr><th>'.($marker == "quote" ? GetMessage("FRM_QUOTE") : GetMessage("FRM_CODE")).'</th></tr></thead><tbody><tr><td>';
	}

	function convert_close_tag($marker = "quote")
	{
		$marker = (mb_strtolower($marker) == "code" ? "code" : "quote");

		if ($this->{$marker."_open"} == 0)
		{
			$this->{$marker."_error"}++;
			return "";
		}
		$this->{$marker."_closed"}++;

		if ($this->type == "rss")
			return "\n=============\n";
		else if ($this->type == "mail")
			return ($marker == "code" ? "</code>" : "</blockquote>");
		else if ($this->bMobile)
			return "</td></tr></table></div>";
		else
			return "</td></tr></tbody></table></div>";

	}

	function convert_image_tag($url = "", $params="")
	{
		$url = trim($url);
		if (empty($url)) return "";
		$type = (mb_strtolower($this->type) == "rss" ? "rss" : "html");

		$bErrorIMG = !preg_match("/^(http|https|ftp|\/)/i".BX_UTF_PCRE_MODIFIER, $url);

		$url = str_replace(array("<", ">", "\""), array("%3C", "%3E", "%22"), $url);
		// to secure from XSS [img]http://ya.ru/[url]http://onmouseover=prompt(/XSS/)//[/url].jpg[/img]

		if ($bErrorIMG)
			return "[img]".$url."[/img]";

		if ($type != "html")
			return '<img src="'.$url.'" alt="'.GetMessage("FRM_IMAGE_ALT").'" border="0" />';

		$width = 0; $height = 0;
		if (preg_match_all("/width\=(?P<width>\d+)|height\=(?P<height>\d+)/is".BX_UTF_PCRE_MODIFIER, $params, $matches)):
			$width = intval(!empty($matches["width"][0]) ? $matches["width"][0] : $matches["width"][1]);
			$height = intval(!empty($matches["height"][0]) ? $matches["height"][0] : $matches["height"][1]);
		endif;
		$result = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			$this->imageTemplate,
			Array(
				"URL" => $url,
				"SIZE" => array("width" => $width, "height" => $height),
				"MAX_SIZE" => array("width" => $this->imageWidth, "height" => $this->imageHeight),
				"HTML_SIZE"=> array("width" => $this->imageHtmlWidth, "height" => $this->imageHtmlHeight),
				"CONVERT" => "N",
				"FAMILY" => "FORUM",
				"RETURN" => "Y"
			),
			$this->component,
			array("HIDE_ICONS" => "Y"));
		return $this->defended_tags($result, 'replace');
	}

	function convert_attachment($fileID = "", $p = "", $type = "", $text = "")
	{
		if (is_array($fileID))
		{
			$text = $fileID[0];
			$p = $fileID[3];
			$fileID = $fileID[2];
		}

		$fileID = intval($fileID);
		$type = mb_strtolower(empty($type)? $this->type : $type);
		$type = (in_array($type, array("html", "mail", "bbcode", "rss")) ? $type : "html");

		$this->arFiles = (is_array($this->arFiles) ? $this->arFiles : array($this->arFiles));
		if ($fileID <= 0 || (!array_key_exists($fileID, $this->arFiles) && !in_array($fileID, $this->arFiles)))
			return $text;

		if (!array_key_exists($fileID, $this->arFiles) && in_array($fileID, $this->arFiles)): // array(fileID10, fileID12, fileID14)
			unset($this->arFiles[array_search($fileID, $this->arFiles)]);
			$this->arFiles[$fileID] = $fileID; // array(fileID10 => fileID10, fileID12 => fileID12, fileID14 => fileID14)
		endif;

		if (!is_array($this->arFiles[$fileID]))
			$this->arFiles[$fileID] = CFile::GetFileArray($fileID); // array(fileID10 => array about file, ....)

		if (!is_array($this->arFiles[$fileID])): // if file does not exist
			unset($this->arFiles[$fileID]);
			return $text;
		endif;

		if (!array_key_exists($fileID, $this->arFilesParsed) || empty($this->arFilesParsed[$fileID][$type]))
		{
			$arFile = $this->arFiles[$fileID];
			if ($type == "html" || $type == "rss")
			{
				$width = 0; $height = 0;
				if (preg_match_all("/width\=(?P<width>\d+)|height\=(?P<height>\d+)/is".BX_UTF_PCRE_MODIFIER, $p, $matches)):
					$width = intval(!empty($matches["width"][0]) ? $matches["width"][0] : $matches["width"][1]);
					$height = intval(!empty($matches["height"][0]) ? $matches["height"][0] : $matches["height"][1]);
				endif;
				$arFile[$type] = $GLOBALS["APPLICATION"]->IncludeComponent(
						"bitrix:forum.interface",
						"show_file",
						Array(
							"FILE" => $arFile,
							"SHOW_MODE" => ($type == "html" ? "THUMB" : "RSS"),
							"SIZE" => array("width" => $width, "height" => $height),
							"MAX_SIZE" => array("width" => $this->imageWidth, "height" => $this->imageHeight),
							"HTML_SIZE"=> array("width" => $this->imageHtmlWidth, "height" => $this->imageHtmlHeight),
							"CONVERT" => "N",
							"NAME_TEMPLATE" => $this->userNameTemplate,
							"FAMILY" => "FORUM",
							"SINGLE" => "Y",
							"RETURN" => "Y"),
						$this->component,
						array("HIDE_ICONS" => "Y"));
			}
			else
			{
				$path = '/bitrix/components/bitrix/forum.interface/show_file.php?fid='.$arFile["ID"];
				$bIsImage = (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null);
//				$path = ($bIsImage && !empty($arFile["SRC"]) ? $arFile["SRC"] : !$bIsImage && !empty($arFile["URL"]) ? $arFile["URL"] : $path);
				$path = preg_replace("'(?<!:)/+'s", "/", (mb_substr($path, 0, 1) == "/" ? CHTTP::URN2URI($path, $this->serverName) : $path));
				switch ($type)
				{
					case "bbcode":
							$arFile["bbcode"] = ($bIsImage ? '[IMG]'.$path.'[/IMG]' : '[URL='.$path.']'.$arFile["ORIGINAL_NAME"].'[/URL]');
						break;
					case "mail":
							$arFile["mail"] = $arFile["ORIGINAL_NAME"].($bIsImage ? " (IMAGE: ".$path.")" : " (URL: ".$path.")");
						break;
				}
			}
			$this->arFilesParsed[$fileID] = $arFile;
		}
		$this->arFilesIDParsed[] = $fileID;
		return $this->arFilesParsed[$fileID][$type];
	}

	function convert_to_rss(
		$text,
		$arImages = Array(),
		$arAllow = Array())
	{
		if (empty($arAllow))
			$arAllow = array(
				"HTML" => "N",
				"ANCHOR" => "Y",
				"BIU" => "Y",
				"IMG" => "Y",
				"QUOTE" => "Y",
				"CODE" => "Y",
				"FONT" => "Y",
				"LIST" => "Y",
				"SMILES" => "Y",
				"NL2BR" => "N",
				"TABLE" => "Y"
			);
		$text = preg_replace(
			array(
				"#^(.+?)<cut[\s]*(/>|>).*?$#is".BX_UTF_PCRE_MODIFIER,
				"#^(.+?)\[cut[\s]*(/\]|\]).*?$#is".BX_UTF_PCRE_MODIFIER),
			"\\1", $text);

		return $this->convert($text, $arAllow, "rss", $arImages);
	}

	function render_user($fields)
	{
		$classAdditional = (!empty($fields['CLASS_ADDITIONAL']) ? $fields['CLASS_ADDITIONAL'] : '');
		$pathToUser = (!empty($fields['PATH_TO_USER']) ? $fields['PATH_TO_USER'] : '');
		$userId = (!empty($fields['USER_ID']) ? $fields['USER_ID'] : '');
		$userName = (!empty($fields['USER_NAME']) ? $fields['USER_NAME'] : '');

		if (empty($userId))
		{
			return "<span class=\"blog-p-user-name\">{$userName}</span>";
		}

		$anchorId = RandString(8);

		return '<a class="blog-p-user-name'.$classAdditional.'" id="bp_'.$anchorId.'" href="'.CComponentEngine::MakePathFromTemplate($pathToUser, array("user_id" => $userId)).'" bx-tooltip-user-id="'.(!$this->bMobile ? $userId : '').'">'.$userName.'</a>';
	}
}

/***
 * @deprecated
 */
class textParser extends forumTextParser {
	public function killAllTags($text)
	{
		return parent::clearAllTags($text);
	}
}

class CForumSimpleHTMLParser
{
	private $data;
	private $parse_search_needle = '/([^\[]*)(?:\[(.*)\])*/i'.BX_UTF_PCRE_MODIFIER;
	private $parse_tag = "/<(?<closing>\/?)(?<tag>[a-z]+)(?<params>.*?)(?<selfclosing>\/?)>/ism".BX_UTF_PCRE_MODIFIER;
	private $parse_params = '/([a-z\-]+)\s*=\s*(?:([^\s]*)|(?:[\'"]([^\'"])[\'"]))/im'.BX_UTF_PCRE_MODIFIER;
	private $lastError = '';
	private $preg = array(
			"counter" => 0,
			"pattern" => array(),
			"replace" => array()
		);

	function __construct ($data)
	{
		$this->data = $this->prepare($data);
	}
	/**
	 * @param string $text
	 * @return string
	 */
	private function prepare(string $text): string
	{
		$text = preg_replace_callback(
			"/<pre>(.+?)<\\/pre>/is".BX_UTF_PCRE_MODIFIER,
			[$this, "defendTags"],
			$text
		);
		$text = str_replace(["\r\n", "\n", "\t"], "", $text);
		$text = str_replace($this->preg["pattern"], $this->preg["replace"], $text);
		$this->preg["pattern"] = array();
		$this->preg["replace"] = array();
		return $text;
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	public function defendTags($matches)
	{
		$text = "<\017#".(++$this->preg["counter"]).">";
		$this->preg["pattern"][] = $text;
		$this->preg["replace"][] = $matches[0];
		return $text;
	}

	function findTagStart($needle) // needle = input[name=input;class=red]
	{
		$offset = 0;

		$search = array();
		if (preg_match($this->parse_search_needle, $needle, $matches ) == 0)
			return '';
		if (sizeof($matches) > 1)
		{
			$search['TAG'] = trim($matches[1]);
		}
		if (sizeof($matches) > 2)
		{
			$arAttr = explode(';', $matches[2]);
			foreach($arAttr as $attr)
			{
				list($attr_name, $attr_value) = explode('=', $attr);
				$search[mb_strtoupper(trim($attr_name))] = trim($attr_value);
			}
		}
		$tmp = $this->data;
		// skip special tags
		while ($skip = $this->skipTags($tmp))
		{
			$offset += $skip;
			$tmp = mb_substr($tmp, $skip);
		}
		while ($tmp <> '' && preg_match($this->parse_tag, $tmp, $matches) > 0)
		{
			$tag_name = $matches['tag'];
			$localOffset = mb_strpos($tmp, $matches[0]) + mb_strlen($matches[0]);

			if (mb_strlen($matches['closing']) <= 0 && $tag_name == $search['TAG']) // tag has been found
			{
				// parse params
				$params = $matches['params'];
				if (preg_match_all($this->parse_params, $params, $arParams, PREG_SET_ORDER ) > 0)
				{
					// store tag params
					$arTagParams = array();
					foreach($arParams as $arParam)
						$arTagParams[mb_strtoupper(trim($arParam[1]))] = trim(trim($arParam[2]), '"\'');
					// compare all search params
					$found = true;
					foreach($search as $key => $value)
					{
						if ($key == 'TAG') continue;
						if (!( isset($arTagParams[$key]) && $arTagParams[$key] == $value))
						{
							$found = false;
							break;
						}
					}
					if ($found)
					{
						return $offset;
					}
				}
			}

			$offset += $localOffset;
			$tmp = mb_substr($tmp, $localOffset);

			// skip special tags
			while ($skip = $this->skipTags($tmp))
			{
				$offset += $skip;
				$tmp = mb_substr($tmp, $skip);
			}
		}
		return false;
	}

	function skipTags($tmp)
	{
		static $tags_open = array('<!--', '<script');
		static $tags_close = array('-->', '</script>');
		static $n_tags = 2;
		static $tags_quoted;

		if (!is_array($tags_quoted))
		for ($i=0; $i<$n_tags;$i++)
				$tags_quoted[$i] = array('open' => preg_quote($tags_open[$i]), 'close' => preg_quote($tags_close[$i]));

		for ($i=0; $i<$n_tags;$i++)
		{
			if (preg_match('#^\s*'.$tags_quoted[$i]['open'].'#i'.BX_UTF_PCRE_MODIFIER, $tmp) < 1) continue;
			if (preg_match('#('.$tags_quoted[$i]['close'].'[^<]*)#im'.BX_UTF_PCRE_MODIFIER, $tmp, $matches) > 0)
			{
				$endpos = mb_strpos($tmp, $matches[1]);
				$offset = $endpos + mb_strlen($matches[1]);
				return $offset;
			}
		}
		return false;
	}

	function setError($msg)
	{
		$this->lastError = $msg;
		return false;
	}

	function findTagEnd($startIndex)
	{
		if ($startIndex === false || (intval($startIndex) == 0 && $startIndex !== 0))
			return $this->setError('E_PARSE_INVALID_INDEX');
		$tmp = mb_substr($this->data, $startIndex);

		$this->lastError = '';
		$arStack = [];
		$offset = 0;
		$closeMistmatch = 2;
		$tag_id = 0;

		while ($tmp <> '' && preg_match($this->parse_tag, $tmp, $matches) > 0)
		{
			$tag_id++;
			$tag_name = mb_strtoupper($matches['tag']);
			$localOffset = mb_strpos($tmp, $matches[0]) + mb_strlen($matches[0]);

			if ($matches['closing'] == '/') // close tag
			{
				if (end($arStack) == $tag_name)
				{
					array_pop($arStack);
				}
				else // lost close tag somewhere
				{
					$fixed = false;
					for ($i=2;$i<=$closeMistmatch+1;$i++)
					{
						if (sizeof($arStack) > $i && $arStack[sizeof($arStack)-$i] == $tag_name)
						{
							$arStack = array_slice($arStack, 0, -$i);
							$fixed = true;
						}
					}
					if (!$fixed)
					{
						return $this->setError('E_PARSE_INVALID_DOM_2');
					}
				}
			}
			else if ($matches['selfclosing'] == '/') // self close tag
			{
				// do nothing
			}
			else if ($tag_name == 'LI' && end($arStack) == 'LI') // oh
			{
				// do nothing
			}
			else // open tag
			{
				$arStack[] = $tag_name;
			}
			if (sizeof($arStack) > 300)
			{
				return $this->setError('E_PARSE_TOO_BIG_DOM_3');  // too big DOM
			}
			else if (sizeof($arStack) == 0) // done !
			{
				return $offset + $localOffset;
			}
			else // continue
			{
				$offset += $localOffset;
				$tmp = mb_substr($tmp, $localOffset);
			}
			// skip special tags
			while ($skip = $this->skipTags($tmp))
			{
				$offset += $skip;
				$tmp = mb_substr($tmp, $skip);
			}
		}
		return $this->setError('E_PARSE_INVALID_DOM_4');  // not enough data in $data ?
	}

	function getTagHTML($search)
	{
		$messagePost = '';
		$messageStart = $this->findTagStart($search);
		if ($messageStart === false) return '';
		$messageEnd = $this->findTagEnd($messageStart);
		if ($messageEnd !== false)
			$messagePost = mb_substr($this->data, $messageStart, $messageEnd);
		return trim($messagePost);
	}

	function getInnerHTML($startLabel, $endLabel, $multiple=false)
	{
		$startPos = mb_strpos($this->data, $startLabel);
		if ($startPos === false) return '';
		$startPos += mb_strlen($startLabel);
		$endPos = mb_strpos($this->data, $endLabel, $startPos);
		if ($endPos === false) return '';
		return trim(mb_substr($this->data, $startPos, $endPos - $startPos));
	}
}

class CForumCacheManager
{
	public function __construct()
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			AddEventHandler("forum", "onAfterMessageDelete", array(&$this, "OnMessageDelete"));
			AddEventHandler("forum", "onAfterMessageUpdate", array(&$this, "OnMessageUpdate"));
			AddEventHandler("forum", "onAfterMessageAdd", array(&$this, "OnMessageAdd"));

			AddEventHandler("forum", "onAfterTopicAdd", array(&$this, "OnTopicAdd"));
			AddEventHandler("forum", "onAfterTopicUpdate", array(&$this, "OnTopicUpdate"));
			AddEventHandler("forum", "onAfterTopicDelete", array(&$this, "OnTopicDelete"));

			//AddEventHandler("forum", "onAfterForumAdd", array(&$this, "OnForumAdd"));
			AddEventHandler("forum", "onAfterForumUpdate", array(&$this, "OnForumUpdate"));
			//AddEventHandler("forum", "OnAfterForumDelete", array(&$this, "OnForumDelete"));

			AddEventHandler("main", "OnAddRatingVote", Array(&$this, "OnRate"));
			AddEventHandler("main", "OnCancelRatingVote", Array(&$this, "OnRate"));
		}
	}

	public static function Compress($arDictCollection)
	{
		if (
			is_array($arDictCollection) &&
			(sizeof($arDictCollection) > 9)
		)
		{
			reset($arDictCollection);
			$arFirst = current($arDictCollection);
			$arKeys = array_keys($arFirst);
			$i = 0;

			foreach($arDictCollection as &$arDictionary)
			{
				if ($i++ === 0)
					continue;

				foreach($arKeys as $k)
				{
					if (isset($arDictionary[$k]) && ($arDictionary[$k] === $arFirst[$k]))
						unset($arDictionary[$k]);
				}
			}
		}
		return $arDictCollection;
	}

	public static function Expand($arDictCollection)
	{
		if (
			is_array($arDictCollection) &&
			(sizeof($arDictCollection) > 9) &&
			is_array($arDictCollection[0])
		)
		{

			$arFirst =& $arDictCollection[0];
			$arKeys = array_keys($arFirst);
			$i = 0;

			foreach($arDictCollection as &$arDictionary)
			{
				if ($i++ === 0)
					continue;

				foreach($arKeys as $k)
				{
					if (!isset($arDictionary[$k]))
					{
						$arDictionary[$k] = $arFirst[$k];
					}
				}
			}
		}
		return $arDictCollection;
	}

	public static function SetTag($path, $tags)
	{
		global $CACHE_MANAGER;
		if (! defined("BX_COMP_MANAGED_CACHE"))
			return false;
		$CACHE_MANAGER->StartTagCache($path);
		if (is_array($tags))
		{
			foreach ($tags as $tag)
				$CACHE_MANAGER->RegisterTag($tag);
		}
		else
		{
			$CACHE_MANAGER->RegisterTag($tags);
		}
		$CACHE_MANAGER->EndTagCache();
		return true;
	}

	public static function ClearTag($type, $ID=0)
	{
		global $CACHE_MANAGER;
		static $forum = "forum_";
		static $topic = "forum_topic_";

		if ($type === "F" && $ID > 0)
		{
			$CACHE_MANAGER->ClearByTag($forum.$ID);
		}
		elseif ($type === "T" && $ID > 0)
		{
			$CACHE_MANAGER->ClearByTag($topic.$ID);
		}
		else if ($type !== "F" && $type !== "T")
		{
			$CACHE_MANAGER->ClearByTag($type);
		}
	}

	public function OnRate($rateID, $arData)
	{
		if (!isset($arData['ENTITY_TYPE_ID']) ||
			!isset($arData['ENTITY_ID']) ||
			($arData['ENTITY_TYPE_ID'] !== 'FORUM_POST' && $arData['ENTITY_TYPE_ID'] !== 'FORUM_TOPIC'))
				return false;

		if ($arData['ENTITY_TYPE_ID'] === 'FORUM_POST')
		{
			$arMessage = CForumMessage::GetByID($arData['ENTITY_ID']);
			if ($arMessage)
				$this->ClearTag("T", $arMessage['TOPIC_ID']);
		}
		else if ($arData['ENTITY_TYPE_ID'] === 'FORUM_TOPIC')
		{
			$arTopic = CForumTopic::GetByID($arData['ENTITY_ID']);
			if ($arTopic)
				$this->ClearTag("F", $arTopic['FORUM_ID']);
			$this->ClearTag("T", $arData['ENTITY_ID']);
		}
		return true;
	}

	public function OnMessageAdd($ID, $arFields)
	{
		self::ClearTag("T", isset($arFields["FORUM_TOPIC_ID"]) ? $arFields["FORUM_TOPIC_ID"] : $arFields["TOPIC_ID"]);
		self::ClearTag("forum_msg_count".$arFields["FORUM_ID"]);
	}

	public function OnMessageUpdate($ID, $arFields, $arMessage = array())
	{
		$arMessage = (is_array($arMessage) ? $arMessage : array());
		$topic_id = (isset($arFields["FORUM_TOPIC_ID"]) ? $arFields["FORUM_TOPIC_ID"] : $arFields["TOPIC_ID"]);
		if (isset($arFields["APPROVED"]) && $topic_id <= 0)
			$topic_id = $arMessage["TOPIC_ID"];
		if ($topic_id > 0)
			$this->ClearTag("T", $topic_id);
		$forum_id = (isset($arFields["FORUM_ID"]) ? $arFields["FORUM_ID"] : 0);
		if (isset($arFields["APPROVED"]) && $forum_id <= 0)
			$forum_id = $arMessage["FORUM_ID"];
		if ($forum_id > 0)
			$this->ClearTag("forum_msg_count".$forum_id);
	}

	public function OnMessageDelete($ID, $arMessage)
	{
		self::ClearTag("T", isset($arMessage["FORUM_TOPIC_ID"]) ? $arMessage["FORUM_TOPIC_ID"] : $arMessage["TOPIC_ID"]);
		self::ClearTag("forum_msg_count".$arMessage["FORUM_ID"]);
	}

	public function OnTopicAdd($ID, $arFields)
	{
		self::ClearTag("F", $arFields["FORUM_ID"]);
	}

	public function OnTopicUpdate($ID, $arFields)
	{
		if (count($arFields) == 1 && array_key_exists("VIEWS", $arFields))
		{
			return;
		}
		self::ClearTag("T", $ID);
		self::ClearTag("F", $arFields["FORUM_ID"] ?? 0);
	}

	public function OnTopicDelete(&$ID, $arTopic)
	{
		self::ClearTag("T", $ID);
		self::ClearTag("F", $arTopic["FORUM_ID"]);
	}

	//public function OnForumAdd(&$ID, &$arFields)
	//{
	//}

	public function OnForumUpdate($ID, $arFields)
	{
		self::ClearTag("F", $arFields["FORUM_ID"]);
	}

	//public function OnForumDelete($ID)
	//{
	//}
}

class CForumAutosave
{
	private static $instance;
	private $as;

	public function __construct()
	{
		echo CJSCore::Init(array('autosave'), true);
		$this->as = new CAutoSave();
	}

	public static function GetInstance()
	{
		if (!$GLOBALS['USER']->IsAuthorized())
			return false;
		if (COption::GetOptionString("forum", "USE_AUTOSAVE", "Y") === "N")
			return false;

		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function LoadScript($arParams)
	{
		if (!is_array($arParams))
			$arParams = array("formID" => $arParams);
		if (!isset($arParams['recoverMessage']))
			$arParams['recoverMessage'] = GetMessage('F_MESSAGE_RECOVER');

		$jsParams = CUtil::PhpToJSObject($arParams);
		$id = $this->as->GetID();
		ob_start();
?>
		<script>
		window.autosave_<?=$id?>_func = function() { ForumFormAutosave(<?=$jsParams?>); window.autosave_<?=$id?>.Prepare(); };
		if (!!window["ForumFormAutosave"])
			window.autosave_<?=$id?>_func();
		else
		{
			BX.addCustomEvent(window, 'onScriptForumAutosaveLoaded', window.autosave_<?=$id?>_func);
			BX.loadScript("<?=CUtil::GetAdditionalFileURL("/bitrix/js/forum/autosave.js")?>");
		}
		</script>
<?
		ob_end_flush();
	}

	public function Init()
	{
		return $this->as->Init(false);
	}

	public function Reset()
	{
		return $this->as->Reset();
	}
}
?>