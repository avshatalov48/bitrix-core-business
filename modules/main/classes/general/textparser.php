<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

class CTextParser
{
	public $type = "html";
	public $serverName = "";
	public $preg;

	public $imageWidth = 800;
	public $imageHeight = 800;
	public $maxStringLen = 0;
	public $maxAnchorLength = 40;
	//https://www.w3.org/TR/CSS2/fonts.html#propdef-font-size
	public $arFontSize = array(
		1 => "xx-small",
		2 => "small",
		3 => "medium",
		4 => "large",
		5 => "x-large",
		6 => "xx-large",
	);
	public $allow = array(
		"HTML" => "N",
		"ANCHOR" => "Y",
		"BIU" => "Y",
		"IMG" => "Y",
		"QUOTE" => "Y",
		"CODE" => "Y",
		"FONT" => "Y",
		"LIST" => "Y",
		"EMOJI" => "Y",
		"SMILES" => "Y",
		"CLEAR_SMILES" => "N",
		"NL2BR" => "N",
		"VIDEO" => "Y",
		"TABLE" => "Y",
		"CUT_ANCHOR" => "N",
		"SHORT_ANCHOR" => "N",
		"ALIGN" => "Y",
		"USERFIELDS" => "N",
		"USER" => "Y",
		"P" => "Y",
		"TAG" => "N"
	);
	public $smiles = null;
	protected $wordSeparator = "\\s.,;:!?\\#\\-\\*\\|\\[\\]\\(\\)\\{\\}";
	protected $smilePatterns = null;
	protected $smileReplaces = null;
	protected static $repoSmiles = array();
	public $smilesGallery = CSmileGallery::GALLERY_DEFAULT;
	protected $defended_urls = array();
	protected $anchorSchemes = null;
	protected $userField;
	public $bMobile = false;
	public $LAZYLOAD = "N";
	protected $tagPattern = "/([\s]+|^)#([^\s,\.\[\]<>]+)/is";

	/* @deprecated */
	public $allowImgExt = "gif|jpg|jpeg|png";

	public function __construct()
	{
		global $APPLICATION;

		$this->pathToSmile = '';
		$this->parser_nofollow = "N";
		$this->link_target = "_blank";
		$this->authorName = '';

		$this->pathToUser = '';
		$this->pathToUserEntityType = false;
		$this->pathToUserEntityId = false;
		$this->ajaxPage = $APPLICATION->GetCurPageParam("", array("bxajaxid", "logout"));
		$this->bPublic = false;
	}

	/** @deprecated */
	public function CTextParser()
	{
		self::__construct();
	}

	public function getAnchorSchemes()
	{
		if($this->anchorSchemes === null)
		{
			static $schemes = null;
			if($schemes === null)
			{
				$schemes = \Bitrix\Main\Config\Option::get("main", "~parser_anchor_schemes", "http|https|news|ftp|aim|mailto|file|tel|callto|skype|viber");
			}
			$this->anchorSchemes = $schemes;
		}
		return $this->anchorSchemes;
	}

	public function setAnchorSchemes($schemes)
	{
		$this->anchorSchemes = $schemes;
	}

	protected function initSmiles()
	{
		if (!array_key_exists($this->smilesGallery, self::$repoSmiles))
		{
			$smiles = CSmile::getByGalleryId(CSmile::TYPE_SMILE, $this->smilesGallery);
			$arSmiles = array();
			foreach($smiles as $smile)
			{
				$arTypings = explode(" ", $smile["TYPING"]);
				foreach ($arTypings as $typing)
				{
					$arSmiles[] = array_merge($smile, array(
						'TYPING' => $typing,
						'IMAGE'  => CSmile::PATH_TO_SMILE.$smile["SET_ID"]."/".$smile["IMAGE"],
						'DESCRIPTION' => $smile["NAME"],
						'DESCRIPTION_DECODE' => 'Y',
					));
				}
			}
			self::$repoSmiles[$this->smilesGallery] = $arSmiles;
		}
		$this->smiles = self::$repoSmiles[$this->smilesGallery];
	}

	protected function initSmilePatterns()
	{
		$this->smilePatterns = array();
		$this->smileReplaces = array();

		$pre = "";
		foreach ($this->smiles as $row)
		{
			if(preg_match("/\\w\$/", $row["TYPING"]))
				$pre .= "|".preg_quote($row["TYPING"], "/");
		}

		foreach ($this->smiles as $row)
		{
			if($row["TYPING"] <> '' && $row["IMAGE"] <> '')
			{
				$code = str_replace(array("'", "<", ">"), array("\\'", "&lt;", "&gt;"), $row["TYPING"]);
				$patt = preg_quote($code, "/");
				$code = preg_quote(str_replace(array("\x5C"), array("&#092;"), $code));
				$image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
				$description = preg_quote(htmlspecialcharsbx(str_replace(array("\x5C"), array("&#092;"), $row["DESCRIPTION"]), ENT_QUOTES), "/");

				$patternName = "pattern".count($this->smilePatterns);

				$this->smilePatterns[] = "/(?<=^|\\>|[".$this->wordSeparator."\\&]".$pre.")(?P<".$patternName.">$patt)(?=$|\\<|[".$this->wordSeparator."\\&])/s".BX_UTF_PCRE_MODIFIER;

				$this->smileReplaces[$patternName] = array(
					"code" => $code,
					"image" => $image,
					"description" => $description,
					"width" => intval($row["IMAGE_WIDTH"]),
					"height" => intval($row["IMAGE_HEIGHT"]),
					"descriptionDecode" => $row["DESCRIPTION_DECODE"] == 'Y'? true: false,
					"imageDefinition" => $row["IMAGE_DEFINITION"]? $row["IMAGE_DEFINITION"]: CSmile::IMAGE_SD
				);
			}
		}
		usort($this->smilePatterns, function($a, $b) { return (strlen($a) > strlen($b) ? -1 : 1); });
	}
	protected static function chr($a)
	{
		return \Bitrix\Main\Text\Encoding::convertEncoding($a, 'cp1251', SITE_CHARSET);
	}
	protected static function strpos($s, $a)
	{
		$a = self::chr($a);
		if (function_exists('mb_orig_strpos'))
			return mb_orig_strpos($s, $a);
		return strpos($s, $a);
	}

	public function convertText($text)
	{
		if (!is_string($text) || strlen($text) <= 0)
			return "";

		$text = preg_replace(array("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "/\\x{00A0}/".BX_UTF_PCRE_MODIFIER), array("\\1PHPSESSID1=", " "), $text);

		$this->defended_urls = array();

		if($this->serverName == '' && $this->type == "rss")
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$serverName = $arSite["SERVER_NAME"];
			if ($serverName == '')
			{
				if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
					$serverName = SITE_SERVER_NAME;
				else
					$serverName = COption::GetOptionString("main", "server_name");
			}
			if ($serverName <> '')
			{
				$this->serverName = "http://".$serverName;
			}
		}

		$this->preg = array("counter" => 0, "pattern" => array(), "replace" => array(), "cache" => array());

		foreach(GetModuleEvents("main", "TextParserBefore", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		if ($this->allow["CODE"]=="Y")
		{
			$text = preg_replace_callback(
				array(
					"#(\\[code(?:\\s+[^\\]]*\\]|\\]))(.+?)(\\[/code(?:\\s+[^\\]]*\\]|\\]))#is".BX_UTF_PCRE_MODIFIER,
					"#(<code(?:\\s+[^>]*>|>))(.+?)(</code(?:\\s+[^>]*>|>))#is".BX_UTF_PCRE_MODIFIER),
				array($this, "convertCode"),
				$text
			);
		}
		if ($this->allow["HTML"] != "Y" && $this->allow['NL2BR'] == 'Y')
		{
			$text = preg_replace("#<br(.*?)>#is", "\n", $text);
		}
		if ($this->allow["HTML"] != "Y")
		{
			// тут она превращаетс€!
			if ($this->allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"#<a[^>]+href\\s*=\\s*('|\")(.+?)(?:\\1)[^>]*>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href(\\s*=\\s*)([^'\">]+)>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER),
					"[url=\\2]\\3[/url]", $text
				);
			}
			if ($this->allow["BIU"]=="Y")
			{
				$replaced = 0;
				do
				{
					$text = preg_replace(
						"/<([busi])[^>a-z]*>(.+?)<\\/(\\1)[^>a-z]*>/is".BX_UTF_PCRE_MODIFIER,
						"[\\1]\\2[/\\1]",
					$text, -1, $replaced);
				}
				while($replaced > 0);
			}
			if ($this->allow["P"]=="Y")
			{
				$replaced = 0;
				do
				{
					$text = preg_replace(
						"/<p[^>a-z]*>(.+?)<\\/p[^>a-z]*>/is".BX_UTF_PCRE_MODIFIER,
						"[p]\\1[/p]",
						$text, -1, $replaced);
				}
				while($replaced > 0);
			}
			if ($this->allow["IMG"]=="Y")
			{
				$text = preg_replace(
					"#<img[^>]+src\\s*=[\\s'\"]*(((http|https|ftp)://[.\\-_:a-z0-9@]+)*(\\/[-_/=:.a-z0-9@{}&?%]+)+)[\\s'\"]*[^>]*>#is".BX_UTF_PCRE_MODIFIER,
					"[img]\\1[/img]", $text
				);
			}
			if ($this->allow["FONT"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\\<font[^>]+size\\s*=[\\s'\"]*([0-9]+)[\\s'\"]*[^>]*\\>(.+?)\\<\\/font[^>]*\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<font[^>]+color\\s*=[\\s'\"]*(\\#[a-f0-9]{6})[^>]*\\>(.+?)\\<\\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<font[^>]+face\\s*=[\\s'\"]*([a-z\\s\\-]+)[\\s'\"]*[^>]*>(.+?)\\<\\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER
					),
					array(
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]"
					),
					$text
				);
			}
			if ($this->allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\\<ul((\\s[^>]*)|(\\s*))\\>(.+?)<\\/ul([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<ol((\\s[^>]*)|(\\s*))\\>(.+?)<\\/ol([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<li((\\s[^>]*)|(\\s*))\\>(.+?)<\\/li([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<li((\\s[^>]*)|(\\s*))\\>/is".BX_UTF_PCRE_MODIFIER,
					),
					array(
						"[list]\\4[/list]",
						"[list=1]\\4[/list]",
						"[*]\\4",
						"[*]",
					),
					$text
				);
			}
			if ($this->allow["TABLE"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\\<table((\\s[^>]*)|(\\s*))\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<\\/table([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<tr((\\s[^>]*)|(\\s*))\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<\\/tr([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<td((\\s[^>]*)|(\\s*))\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<\\/td([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<th((\\s[^>]*)|(\\s*))\\>/is".BX_UTF_PCRE_MODIFIER,
						"/\\<\\/th([^>]*)\\>/is".BX_UTF_PCRE_MODIFIER,
					),
					array(
						"[table]",
						"[/table]",
						"[tr]",
						"[/tr]",
						"[td]",
						"[/td]",
						"[th]",
						"[/th]",
					),
					$text
				);
			}
			if ($this->allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}
			if (strlen($text)>0)
			{
				if ($this->preg["counter"] > 0)
				{
					$res = strlen((string)$this->preg["counter"]);
					$p = array('\d');
					while (($res--) > 1) $p[] = '\d{'.($res + 1).'}';
					$text = preg_replace(
						array("/\\<(?!\017\\#(".implode(")|(", $p).")\\>)/", "/(?<!\\<\017\\#(".implode(")|(", $p)."))\\>/", "/\"/"),
						array("&lt;", "&gt;", "&quot;"),
						$text
					);
				}
				else
				{
					$text = str_replace(
						array("<", ">", "/\"/"),
						array("&lt;", "&gt;", "&quot;"),
						$text
					);

				}
			}
		}
		$patt = array();
		if ($this->allow["VIDEO"] == "Y")
			$patt[] = "/\\[video([^\\]]*)\\](.+?)\\[\\/video[\\s]*\\]/is".BX_UTF_PCRE_MODIFIER;
		if ($this->allow["IMG"] == "Y")
			$patt[] = "/\\[img([^\\]]*)\\](.+?)\\[\\/img\\]/is".BX_UTF_PCRE_MODIFIER;

		foreach(GetModuleEvents("main", "TextParserBeforeAnchorTags", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		if ($this->allow["ANCHOR"]=="Y")
		{
			$patt[] = "/\\[url\\](.*?)\\[\\/url\\]/i".BX_UTF_PCRE_MODIFIER;
			$patt[] = "/\\[url\\s*=\\s*(
			(?:
				[^\\[\\]]++
				|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\]
			)+
			)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER;

			$text = preg_replace_callback($patt, array($this, "preconvertAnchor"), $text);


			$patt = array();
			if (strpos($text, "&lt;") !== false)
				$patt[] = "/(?<=\\&lt\\;)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=\\&gt\\;)/is".BX_UTF_PCRE_MODIFIER;

			$word_separator = str_replace("?", "", $this->wordSeparator);
			if (self::strpos($text, "(") !== false)
				$patt[] = "/(?<=\\()(?<!\\[nomodify\\]|<nomodify>)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=\\))/is".BX_UTF_PCRE_MODIFIER;
			if (self::strpos($text, "У") !== false)
				$patt[] = "/(?<=[".self::chr("У")."])(?<!\\[nomodify\\]|<nomodify>)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=[".self::chr("Ф")."])/is".BX_UTF_PCRE_MODIFIER;
			if (self::strpos($text, "С") !== false)
				$patt[] = "/(?<=[".self::chr("С")."])(?<!\\[nomodify\\]|<nomodify>)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=[".self::chr("Т")."])/is".BX_UTF_PCRE_MODIFIER;
			if (self::strpos($text, "Ђ") !== false)
				$patt[] = "/(?<=[".self::chr("Ђ")."])(?<!\\[nomodify\\]|<nomodify>)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=[".self::chr("ї")."])/is".BX_UTF_PCRE_MODIFIER;

			$patt[] = "/(?<=^|[".$word_separator."]|\\s)(?<!\\[nomodify\\]|<nomodify>)((((".$this->getAnchorSchemes()."):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=[\\s'\"{}\\[\\]]|&quot;|\$)/is".BX_UTF_PCRE_MODIFIER;
			$text = preg_replace_callback($patt, array($this, "preconvertUrl"), $text);
		}
		else if (!empty($patt))
		{
			$text = preg_replace_callback($patt, array($this, "preconvertAnchor"), $text);
		}

		$text = preg_replace("/<\\/?nomodify>/i".BX_UTF_PCRE_MODIFIER, "", $text);

		foreach(GetModuleEvents("main", "TextParserBeforeTags", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		if (
			$this->allow["SMILES"] == "Y"
			|| $this->allow["CLEAR_SMILES"] == "Y"
		)
		{
			if (strpos($text, "<nosmile>") !== false)
			{
				$text = preg_replace_callback(
					"/<nosmile>(.*?)<\\/nosmile>/is".BX_UTF_PCRE_MODIFIER,
					array($this, "defendTags"),
					$text
				);
			}
			if($this->smiles === null)
			{
				$this->initSmiles();
			}
			if(!empty($this->smiles))
			{
				if($this->smilePatterns === null)
				{
					$this->initSmilePatterns();
				}

				if (!empty($this->smilePatterns))
				{
					$text = preg_replace_callback($this->smilePatterns, array($this, "convertEmoticon"), ' '.$text.' ');
				}
			}
		}

		$text = $this->post_convert_anchor_tag($text);


		if ($this->allow["EMOJI"] != "N")
		{
			$text = \Bitrix\Main\Text\Emoji::decode($text);
		}

		$res = array_merge(
			array(
				"VIDEO" => "N",
				"IMG" => "N",
				"ANCHOR" => "N",
				"BIU" => "N",
				"LIST" => "N",
				"FONT" => "N",
				"TABLE" => "N",
				"ALIGN" => "N",
				"QUOTE" => "N",
				"P" => "Y"
			), $this->allow
		);
		foreach ($res as $tag => $val)
		{
			if ($val != "Y")
				continue;

			if (strpos($text, "<nomodify>") !== false)
			{
				$text = preg_replace_callback(
					"/<nomodify>(.*?)<\\/nomodify>/is".BX_UTF_PCRE_MODIFIER,
					array($this, "defendTags"),
					$text
				);
			}

			switch ($tag)
			{
				case "VIDEO":
					$text = preg_replace_callback(
						"/\\[video([^\\]]*)\\](.+?)\\[\\/video[\\s]*\\]/is".BX_UTF_PCRE_MODIFIER,
						array($this, "convertVideo"),
						$text
					);
					break;
				case "IMG":
					$text = preg_replace_callback(
						"/\\[img([^\\]]*)\\](.+?)\\[\\/img\\]/is".BX_UTF_PCRE_MODIFIER,
						array($this, "convertImage"),
						$text
					);
					break;
				case "ANCHOR":
					$arUrlPatterns = array(
						"/\\[url\\](.*?)\\[\\/url\\]/i".BX_UTF_PCRE_MODIFIER,
						"/\\[url\\s*=\\s*(
							(?:
								[^\\[\\]]++
								|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\]
							)+
							)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER,
					);

					if($this->allow["CUT_ANCHOR"] != "Y")
					{
						$text = preg_replace_callback(
							$arUrlPatterns,
							array($this, "convertAnchor"),
							$text
						);
					}
					else
					{
						$text = preg_replace($arUrlPatterns, "", $text);
					}
					break;
				case "BIU":
					$replaced  = 0;
					do
					{
						$text = preg_replace(
							"/\\[([busi])\\](.*?)\\[\\/(\\1)\\]/is".BX_UTF_PCRE_MODIFIER,
							"<\\1>\\2</\\1>",
						$text, -1, $replaced);
					}
					while($replaced > 0);
					break;
				case "P":
					$replaced  = 0;
					do
					{
						$text = preg_replace(
							"/\\[p\\](.*?)\\[\\/p\\](([ \r\t]*)\n?)/is".BX_UTF_PCRE_MODIFIER,
							"<p>\\1</p>",
						$text, -1, $replaced);
					}
					while($replaced > 0);
					break;
				case "LIST":
					while (preg_match("/\\[list\\s*=\\s*(1|a)\\s*\\](.+?)\\[\\/list\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace(
							array(
								"/\\[list\\s*=\\s*1\\s*\\](\\s*)(.+?)\\[\\/list\\](([\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[list\\s*=\\s*a\\s*\\](\\s*)(.+?)\\[\\/list\\](([\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\*\\]/".BX_UTF_PCRE_MODIFIER,
							),
							array(
								"<ol>\\2</ol>",
								"<ol type=\"a\">\\2</ol>",
								"<li>",
							),
							$text
						);
					}
					while (preg_match("/\\[list\\](.+?)\\[\\/list\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace(
							array(
								"/\\[list\\](\\s*)(.+?)\\[\\/list\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\*\\]/".BX_UTF_PCRE_MODIFIER,
								),
							array(
								"<ul>\\2</ul>",
								"<li>",
								),
							$text
						);
					}
					break;
				case "FONT":
					while (preg_match("/\\[size\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/size\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace_callback(
							"/\\[size\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/size\\]/is".BX_UTF_PCRE_MODIFIER,
							array($this, "convertFontSize"),
							$text
						);
					}
					while (preg_match("/\\[font\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/font\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace_callback(
							"/\\[font\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/font\\]/is".BX_UTF_PCRE_MODIFIER,
							array($this, "convertFont"),
							$text
						);
					}
					while (preg_match("/\\[color\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/color\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace_callback(
							"/\\[color\\s*=\\s*([^\\]]+)\\](.*?)\\[\\/color\\]/is".BX_UTF_PCRE_MODIFIER,
							array($this, "convertFontColor"),
							$text
						);
					}
					break;
				case "TABLE":
					while (preg_match("/\\[table\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$tagToCheckList = array("td", "th", "tr", "table");
						foreach($tagToCheckList as $tagToCheck)
						{
							preg_match_all("/\\[".$tagToCheck."\\]/is".BX_UTF_PCRE_MODIFIER, $text, $matches);
							$opentags = count($matches['0']);

							preg_match_all("/\\[\\/".$tagToCheck."\\]/is", $text, $matches);
							$closetags = count($matches['0']);

							$unclosed = $opentags - $closetags;
							for ($i = 0; $i < $unclosed; $i++)
							{
								$text .= '[/'.$tagToCheck.']';
							}
						}

						$openTableTag = (
							$this->bMobile
								? "<div style=\"overflow-x: auto;\"><table class=\"data-table\">"
								: "<table class=\"data-table\">"
							);
							$closeTableTag = (
							$this->bMobile
								? "</table></div>"
								: "</table>"
						);

						$text = preg_replace(
							array(
								"/\\[table\\]/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\/table\\](?:(?:[\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/(\\s*?)\\[tr\\]/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\/tr\\](\\s*?)/is".BX_UTF_PCRE_MODIFIER,
								"/(\\s*?)\\[td\\]/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\/td\\](\\s*?)/is".BX_UTF_PCRE_MODIFIER,
								"/(\\s*?)\\[th\\]/is".BX_UTF_PCRE_MODIFIER,
								"/\\[\\/th\\](\\s*?)/is".BX_UTF_PCRE_MODIFIER,
							),
							array(
								$openTableTag,
								$closeTableTag,
								"<tr>",
								"</tr>",
								"<td>",
								"</td>",
								"<th>",
								"</th>",
							),
							$text
						);
					}
					break;
				case "ALIGN":
					$replaced  = 0;
					do
					{
						$text = preg_replace(
							array(
								"/\\[left\\](.*?)\\[\\/left\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[right\\](.*?)\\[\\/right\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[center\\](.*?)\\[\\/center\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
								"/\\[justify\\](.*?)\\[\\/justify\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
							),
							array(
								"<div align=\"left\">\\1</div>",
								"<div align=\"right\">\\1</div>",
								"<div align=\"center\">\\1</div>",
								"<div align=\"justify\">\\1</div>",
							),
							$text, -1, $replaced);
					}
					while($replaced > 0);
					break;
				case "QUOTE":
					while (preg_match("/\\[quote[^\\]]*\\](.*?)\\[\\/quote[^\\]]*\\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace_callback(
							"/\\[quote[^\\]]*\\](.*?)\\[\\/quote[^\\]]*\\](([\\040\\r\\t]*)\\n?)/is".BX_UTF_PCRE_MODIFIER,
							array($this, "convertQuote"),
							$text
						);
					}
					break;
			}
		}

		if (strpos($text, "<nomodify>") !== false)
		{
			$text = preg_replace_callback(
				"/<nomodify>(.*?)<\\/nomodify>/is".BX_UTF_PCRE_MODIFIER,
				array($this, "defendTags"),
				$text
			);
		}
		if (is_array($this->allow["USERFIELDS"]))
		{
			foreach($this->allow["USERFIELDS"] as $userField)
			{
				if (is_array($userField["USER_TYPE"]) && array_key_exists("TAG", $userField["USER_TYPE"]) )
				{
					$userField["TAG"] = $userField["USER_TYPE"]["TAG"];
				}
				if (empty($userField["TAG"]))
				{
					switch($userField["USER_TYPE_ID"])
					{
						case "webdav_element" :
							$userField["TAG"] = "DOCUMENT ID";
							break;
						case "vote" :
							$userField["TAG"] = "VOTE ID";
							break;
					}
				}

				if (!empty($userField["TAG"]) && array_key_exists("VALUE", $userField) && !empty($userField["VALUE"]) &&
					method_exists($userField["USER_TYPE"]["CLASS_NAME"], "GetPublicViewHTML") )
				{
					$userField["VALUE"] = (is_array($userField["VALUE"]) ? $userField["VALUE"] : array($userField["VALUE"]));
					$this->userField = $userField;
					$text = preg_replace_callback(
						"/\\[(".(is_array($userField["TAG"]) ? implode("|", $userField["TAG"]) : $userField["TAG"]).")\\s*=\\s*([a-z0-9]+)([^\\]]*)\\]/is".BX_UTF_PCRE_MODIFIER,
						array($this, "convert_userfields"),
						$text
					);
				}
			}
		}
		if ($this->allow["USER"] != "N")
		{
			$text = preg_replace_callback(
				"/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER,
				array($this, "convert_user"),
				$text
			);
		}

		if ($this->allow["TAG"] == "Y")
		{
			$text = preg_replace_callback(
				$this->getTagPattern(),
				array($this, "convert_tag"),
				$text
			);
		}

		foreach(GetModuleEvents("main", "TextParserAfterTags", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		if ($this->allow["HTML"] != "Y" || $this->allow['NL2BR'] == 'Y')
		{
			$text = str_replace(array("\r\n", "\n"), "<br />", $text);
			$text = preg_replace(array(
				"/\\<br \\/\\>(\\<\\/table[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<thead[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<\\/thead[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<tfoot[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<\\/tfoot[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<tbody[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<\\/tbody[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<tr[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<\\/tr[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<td[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
				"/\\<br \\/\\>(\\<\\/td[^>]*\\>)/is".BX_UTF_PCRE_MODIFIER,
			),
			"\\1", $text);
		}

		$text = str_replace(
			array(
				"(c)", "(C)",
				"(tm)", "(TM)", "(Tm)", "(tM)",
				"(r)", "(R)"),
			array(
				"&#169;", "&#169;",
				"&#153;", "&#153;", "&#153;", "&#153;",
				"&#174;", "&#174;"),
			$text);

		if ($this->allow["HTML"] != "Y")
		{
			if ($this->maxStringLen > 0)
			{
				$text = preg_replace("/(\\&\\#\\d{1,3}\\;)/is".BX_UTF_PCRE_MODIFIER, "<\019\\1>", $text);
				$text = preg_replace_callback("/(?<=^|\\>)([^\\<\\>\\[]+?)(?=\\<|\\[|$)/is".BX_UTF_PCRE_MODIFIER, array($this, "partWords"), $text);
				$text = preg_replace("/(\\<\019((\\&\\#\\d{1,3}\\;))\\>)/is".BX_UTF_PCRE_MODIFIER, "\\2", $text);
			}
			$text = preg_replace_callback("/(?<=^|\\>)([^\\<\\>\\[]+?)(?=\\<|\\[|$)/is".BX_UTF_PCRE_MODIFIER, array($this, "parseSpaces"), $text);
		}

		foreach(GetModuleEvents("main", "TextParserBeforePattern", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		if ($this->preg["counter"] > 0)
			$text = str_replace($this->preg["pattern"], $this->preg["replace"], $text);

		foreach(GetModuleEvents("main", "TextParserAfter", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$text, &$this));

		return trim($text);
	}

	public function defendTags($matches)
	{
		return $this->defended_tags($matches[1], 'replace');
	}

	function defended_tags($text, $tag = 'replace')
	{
		$text = str_replace("\\\"", "\"", $text);
		switch ($tag)
		{
			case "replace":
				if (($k = array_search($text, $this->preg['replace'])) !== false)
				{
					$text = "<\017#".$k.">";
					break;
				}
				$this->preg["pattern"][] = "<\017#".$this->preg["counter"].">";
				$this->preg["replace"][] = $text;
				$text = "<\017#".$this->preg["counter"].">";
				$this->preg["counter"]++;
				break;
		}
		return $text;
	}

	function convert4mail($text)
	{
		$text = Trim($text);
		if (strlen($text)<=0) return "";

		$arPattern = array();
		$arReplace = array();

		$arPattern[] = "/\\[(code|quote)(.*?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>================== \\1 ===================\n";

		$arPattern[] = "/\\[\\/(code|quote)(.*?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\\<WBR[\\s\\/]?\\>/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/\\[\\*\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "- ";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[b\\](.+?)\\[\\/b\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[p\\](.*?)\\[\\/p\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[i\\](.+?)\\[\\/i\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[u\\](.+?)\\[\\/u\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\\[s\\](.+?)\\[\\/s\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\\[(\\/?)(color|font|size|left|right|center)([^\\]]*)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/\\[url\\](\\S+?)\\[\\/url\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(URL: \\1 )";

		$arPattern[] = "/\\[url\\s*=\\s*(\\S+?)\\s*\\](.*?)\\[\\/url\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\2 (URL: \\1 )";

		$arPattern[] = "/\\[img([^\\]]*)\\](.+?)\\[\\/img\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(IMAGE: \\2)";

		$arPattern[] = "/\\[video([^\\]]*)\\](.+?)\\[\\/video[\\s]*\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(VIDEO: \\2)";

		$arPattern[] = "/\\[(\\/?)list\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n";

		$arPattern[] = "/\\[user([^\\]]*)\\](.+?)\\[\\/user\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[DOCUMENT([^\\]]*)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/\\[DISK(.+?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/\\[(table)(.*?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>================== \\1 ===================\n";

		$arPattern[] = "/\\[\\/table(.*?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\\[tr\\]\\s+/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/\\[(\\/?)(tr|td)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$text = preg_replace($arPattern, $arReplace, $text);

		$text = str_replace("&shy;", "", $text);
		$text = str_replace("&nbsp;", " ", $text);
		$text = str_replace("&quot;", "\"", $text);
		$text = str_replace("&#092;", "\\", $text);
		$text = str_replace("&#036;", "\$", $text);
		$text = str_replace("&#33;", "!", $text);
		$text = str_replace("&#91;", "[", $text);
		$text = str_replace("&#93;", "]", $text);
		$text = str_replace("&#39;", "'", $text);
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&nbsp;", " ", $text);
		$text = str_replace("&#124;", '|', $text);
		$text = str_replace("&amp;", "&", $text);

		return $text;
	}

	public function convertVideo($matches)
	{
		$params = $matches[1];
		$path = $matches[2];

		if (strlen($path) <= 0)
			return "";

		$width = "";
		$height = "";
		$preview = "";
		$provider = "";
		$type = "";
		preg_match("/width\\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $width);
		preg_match("/height\\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $height);

		preg_match("/preview\\='([^']+)'/is".BX_UTF_PCRE_MODIFIER, $params, $preview);
		if (empty($preview))
			preg_match("/preview\\=\"([^\"]+)\"/is".BX_UTF_PCRE_MODIFIER, $params, $preview);

		preg_match("/type\\=(YOUTUBE|RUTUBE|VIMEO|VK|FACEBOOK|INSTAGRAM)/is".BX_UTF_PCRE_MODIFIER, $params, $provider);
		preg_match("/mimetype\\='([^']+)'/is".BX_UTF_PCRE_MODIFIER, $params, $type);


		$width = intval($width[1]);
		$width = ($width > 0 ? $width : 400);
		$height = intval($height[1]);
		$height = ($height > 0 ? $height : 300);
		$preview = trim($preview[1]);
		$preview = (strlen($preview) > 0 ? $preview : "");
		$provider = isset($provider[1]) ? strtoupper(trim($provider[1])) : '';
		$type = trim($type[1]);

		$arFields = array(
			"PATH" => $path,
			"WIDTH" => $width,
			"HEIGHT" => $height,
			"PREVIEW" => $preview,
			"TYPE" => $provider,
			"MIME_TYPE" => $type,
			"PARSER_OBJECT" => $this
		);

		foreach(GetModuleEvents("main", "TextParserVideoConvert", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields, &$this));

		$video = $this->convert_video($arFields);

		return $this->defended_tags($video, 'replace');
	}

	protected function convert_video($arParams)
	{
		global $APPLICATION;

		if(
			!is_array($arParams)
			|| strlen($arParams["PATH"]) <= 0
		)
		{
			return false;
		}
		$trustedProviders = array(
			'YOUTUBE',
			'RUTUBE',
			'VIMEO',
			'VK',
			'FACEBOOK',
			'INSTAGRAM',
		);

		ob_start();

		if ($this->type == "mail")
		{
			$pathEncoded = htmlspecialcharsbx($arParams["PATH"]);

			?><a href="<?=$pathEncoded?>"><?=$pathEncoded?></a><?
		}
		elseif (in_array($arParams["TYPE"], $trustedProviders))
		{
			// add missing protocol to get host
			if(substr($arParams['PATH'], 0, 2) == '//')
			{
				$arParams['PATH'] = 'http:'.$arParams['PATH'];
			}
			$uri = new \Bitrix\Main\Web\Uri($arParams["PATH"]);
			if(\Bitrix\Main\UrlPreview\UrlPreview::isHostTrusted($uri) || $uri->getHost() == \Bitrix\Main\Application::getInstance()->getContext()->getServer()->getServerName())
			{
				// Replace http://someurl, https://someurl by //someurl
				$arParams["PATH"] = preg_replace("/https?:\\/\\//is", '//', $arParams["PATH"]);

				$pathEncoded = htmlspecialcharsbx($arParams["PATH"]);

				if ($this->bMobile)
				{
					?><iframe class="bx-mobile-video-frame" src="<?=$pathEncoded?>" allowfullscreen="" frameborder="0" height="100%" width="100%" style="max-width: 600px; min-height: 300px;"></iframe><?
				}
				else
				{
					?><iframe src="<?=$pathEncoded?>" allowfullscreen="" frameborder="0" height="<?=intval($arParams["HEIGHT"])?>" width="<?=intval($arParams["WIDTH"])?>" style="max-width: 100%;"></iframe><?
				}
			}
		}
		else
		{
			$playerParams = $arParams;
			$playerParams['TYPE'] = $arParams['MIME_TYPE'];
			$playerComponent = 'bitrix:player';
			if ($this->bMobile)
			{
				$playerComponent = 'bitrix:mobile.player';
			}

			$APPLICATION->IncludeComponent(
				$playerComponent, "", $playerParams,
				null,
				array(
					"HIDE_ICONS" => "Y"
				)
			);
		}

		return ob_get_clean();
	}

	function convertEmoticon($matches)
	{
		$replacement = reset(array_intersect_key($this->smileReplaces, $matches));
		if (!empty($replacement))
		{
			if ($this->allow['CLEAR_SMILES'] == 'Y')
			{
				return $this->convert_emoticon(
					$replacement["code"],
					''
				);
			}
			else
			{
				return $this->convert_emoticon(
					$replacement["code"],
					$replacement["image"],
					$replacement["description"],
					$replacement["width"],
					$replacement["height"],
					$replacement["descriptionDecode"],
					$replacement["imageDefinition"]
				);
			}
		}
		return $matches[0];
	}

	function convert_emoticon($code = "", $image = "", $description = "", $width = "", $height = "", $descriptionDecode = false, $imageDefinition = CSmile::IMAGE_SD)
	{
		if ($code == '' || $image == '')
			return '';
		$code = stripslashes($code);
		$description = stripslashes($description);
		$image = stripslashes($image);
		$width = intval($width);
		$height = intval($height);
		if ($descriptionDecode)
			$description = htmlspecialcharsback($description);

		$html = '<img src="'.htmlspecialcharsbx($this->serverName).$this->pathToSmile.$image.'" border="0" data-code="'.$code.'" data-definition="'.$imageDefinition.'" alt="'.$code.'"'.' style="'.($width > 0 ? 'width:'.$width.'px;' : '').($height > 0 ? 'height:'.$height.'px;' : '').'"'.' title="'.$description.'" class="bx-smile" />';
		$cacheKey = md5($html);
		if (!isset($this->preg["cache"][$cacheKey]))
			$this->preg["cache"][$cacheKey] = $this->defended_tags($html, 'replace');

		return $this->preg["cache"][$cacheKey];
	}

	public function convertCode($matches)
	{
		$text = $matches[2];

		if (strlen($text)<=0)
			return '';

		$text = str_replace(
			array("[nomodify]", "[/nomodify]", "&#91;", "&#93;", "&", "<", ">", "\\r", "\\n", "\\\"", "\\", "[", "]", "  ", "\t"),
			array("", "", "[", "]", "&#38;", "&#60;", "&#62;", "&#92;r", "&#92;n", '&#92;"', "&#92;", "&#91;", "&#93;", "&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"),
			$text
		);

		$text = stripslashes($text);

		return $this->defended_tags($this->convert_open_tag('code')."<pre>".$text."</pre>".$this->convert_close_tag('code'), 'replace');
	}

	public function convertQuote($matches)
	{
		return $this->convert_quote_tag($matches[1]);
	}

	function convert_quote_tag($text = "")
	{
		if (strlen($text)<=0)
			return '';

		$text = str_replace("\\\"", "\"", $text);

		return $this->convert_open_tag('quote').$text.$this->convert_close_tag('quote');
	}

	function convert_open_tag($marker = "quote")
	{
		$marker = (strtolower($marker) == "code" ? "code" : "quote");

		$this->{$marker."_open"}++;
		if ($this->type == "rss")
			return "\n====".$marker."====\n";
		return "<div class='".$marker."'><table class='".$marker."'><tr><td>";
	}

	function convert_close_tag($marker = "quote")
	{
		$marker = (strtolower($marker) == "code" ? "code" : "quote");

		if ($this->{$marker."_open"} == 0)
		{
			$this->{$marker."_error"}++;
			return '';
		}
		$this->{$marker."_closed"}++;

		if ($this->type == "rss")
			return "\n=============\n";
		return "</td></tr></table></div>";
	}

	public function convertImage($matches)
	{
		return $this->convert_image_tag($matches[2], $matches[1]);
	}

	function convert_image_tag($url = "", $params = "")
	{
		$url = trim($url);
		if (strlen($url)<=0)
			return '';

		preg_match("/width\\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $width);
		preg_match("/height\\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $height);
		$width = intval($width[1]);
		$height = intval($height[1]);

		$bErrorIMG = false;
		if (!$bErrorIMG && !preg_match("/^(http|https|ftp|\\/)/i".BX_UTF_PCRE_MODIFIER, $url))
			$bErrorIMG = true;

		$url = htmlspecialcharsbx($url);
		if ($bErrorIMG)
			return "[img]".$url."[/img]";

		$strPar = "";
		if($width > 0)
		{
			if($width > $this->imageWidth)
			{
				$height = intval($height * ($this->imageWidth / $width));
				$width = $this->imageWidth;
			}
		}
		if($height > 0)
		{
			if($height > $this->imageHeight)
			{
				$width = intval($width * ($this->imageHeight / $height));
				$height = $this->imageHeight;
			}
		}
		if($width > 0)
			$strPar = " width=\"".$width."\"";
		if($height > 0)
			$strPar .= " height=\"".$height."\"";

		$serverName = htmlspecialcharsbx($this->serverName);
		$image = '<img src="'.$serverName.$url.'" border="0"'.$strPar.' data-bx-image="'.$serverName.$url.'" data-bx-onload="Y" />';
		if($this->serverName == '' || preg_match("/^(http|https|ftp)\\:\\/\\//i".BX_UTF_PCRE_MODIFIER, $url))
			$image = '<img src="'.$url.'" border="0"'.$strPar.' data-bx-image="'.$url.'" data-bx-onload="Y" />';
		return $this->defended_tags($image, 'replace');
	}

	public function convertFont($matches)
	{
		return $this->convert_font_attr('font', $matches[1], $matches[2]);
	}

	public function convertFontSize($matches)
	{
		return $this->convert_font_attr('size', $matches[1], $matches[2]);
	}

	public function convertFontColor($matches)
	{
		return $this->convert_font_attr('color', $matches[1], $matches[2]);
	}

	public function stripAllTags($text)
	{
		return preg_replace('|[[\\/\\!]*?[^\\[\\]]*?]|si', '', $text);
	}

	function convert_font_attr($attr, $value = "", $text = "")
	{
		if (strlen($text)<=0)
			return "";

		$text = str_replace("\\\"", "\"", $text);

		if (strlen($value) <= 0)
			return $text;

		if ($attr == "size")
		{
			if (strlen($value) > 2 && substr($value, -2) == 'pt')
			{
				$value = intVal(substr($value, 0, -2));
				if ($value <= 0)
					return $text;
				return '<span class="bx-font" style="font-size:'.$value.'pt; line-height: normal;">'.$text.'</span>';
			}

			$count = count($this->arFontSize);
			if ($count <= 0)
				return $text;
			$value = intval($value > $count ? ($count - 1) : $value);
			//compatibility with old percent values
			$size = (is_numeric($this->arFontSize[$value])? $this->arFontSize[$value].'%' : $this->arFontSize[$value]);
			return '<span class="bx-font" style="font-size:'.$size.';">'.$text.'</span>';
		}
		elseif ($attr == 'color')
		{
			$value = preg_replace("/[^\\w#]/", "" , $value);
			return '<span class="bx-font" style="color:'.$value.'">'.$text.'</span>';
		}
		elseif ($attr == 'font')
		{
			$value = preg_replace("/[^\\w\\s\\-\\,]/", "" , $value);
			return '<span class="bx-font" style="font-family:'.$value.'">'.$text.'</span>';
		}
		return '';
	}

	function convert_userfields($matches)
	{
		$vars = get_object_vars($this);
		$vars["TEMPLATE"] = ($this->bMobile ? "mobile" : $this->type);
		$vars["LAZYLOAD"] = $this->LAZYLOAD;

		$userField = $this->userField;

		$id = $matches[2];

		foreach(GetModuleEvents("main", "TextParserUserField", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id, &$userField, &$vars,  &$this));

		if ($userField["USER_TYPE"]["USER_TYPE_ID"] == "disk_file" || in_array($id, $userField["VALUE"]))
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->RegisterTag("webdav_element_internal_".$id);
			}

			return call_user_func_array(
				array($userField["USER_TYPE"]["CLASS_NAME"], "GetPublicViewHTML"),
				array($userField, $id, $matches[3], $vars, $matches)
			);
		}
		return $matches[0];
	}

	function convert_user($userId = 0, $userName = "")
	{
		static $arExtranetUser = false;
		static $arEmailUser = false;

		if (\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			if ($arExtranetUser === false)
			{
				$arExtranetUser = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();
			}

			if ($arEmailUser === false)
			{
				$arEmailUser = \Bitrix\Socialnetwork\ComponentHelper::getEmailUserIdList();
			}
		}

		if (is_array($userId))
		{
			$userName = $userId[2];
			$userId = $userId[1];
		}

		$userId = intval($userId);
		$res = "";

		if($userId > 0)
		{
			$type = false;
			if (
				!empty($arExtranetUser)
				&& in_array($userId, $arExtranetUser)
			)
			{
				$type = 'extranet';
			}
			elseif (
				!empty($arEmailUser)
				&& in_array($userId, $arEmailUser)
			)
			{
				$type = 'email';
			}

			$pathToUser = (
				!empty($this->userPath)
					? $this->userPath  // forum
					: $this->pathToUser
			);

			if (empty($pathToUser))
			{
				$pathToUser = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", '', SITE_ID);
			}

			switch($type)
			{
				case 'extranet':
					$classAdditional = ' blog-p-user-name-extranet';
					break;
				case 'email':
					$classAdditional = ' blog-p-user-name-email';
					$pathToUser .= '';
					if (
						$this->pathToUserEntityType && strlen($this->pathToUserEntityType) > 0
						&& intval($this->pathToUserEntityId) > 0
					)
					{
						$pathToUser .= (strpos($pathToUser, '?') === false ? '?' : '&').'entityType='.$this->pathToUserEntityType.'&entityId='.intval($this->pathToUserEntityId);
					}
					break;
				default:
					$classAdditional = '';
			}

			$renderParams = array(
				'CLASS_ADDITIONAL' => $classAdditional,
				'PATH_TO_USER' => $pathToUser,
				'USER_ID' => $userId,
				'USER_NAME' => $userName
			);

			if (
				$type == 'email'
				&& !empty($this->pathToUserEntityType)
				&& !empty($this->pathToUserEntityId)
			)
			{
				$renderParams['TOOLTIP_PARAMS'] = \Bitrix\Main\Web\Json::encode(array(
					'entityType' => $this->pathToUserEntityType,
					'entityId' => intval($this->pathToUserEntityId)
				));
			}

			$res = $this->render_user($renderParams);
		}

		return $this->defended_tags($res, "replace");
	}

	protected function render_user($fields)
	{
		$classAdditional = (!empty($fields['CLASS_ADDITIONAL']) ? $fields['CLASS_ADDITIONAL'] : '');
		$pathToUser = (!empty($fields['PATH_TO_USER']) ? $fields['PATH_TO_USER'] : '');
		$userId = (!empty($fields['USER_ID']) ? $fields['USER_ID'] : '');
		$userName = (!empty($fields['USER_NAME']) ? $fields['USER_NAME'] : '');

		$res = (
			!$this->bPublic
				? '<a class="blog-p-user-name'.$classAdditional.'" href="'.CComponentEngine::MakePathFromTemplate($pathToUser, array("user_id" => $userId)).'" bx-tooltip-user-id="'.(!$this->bMobile ? $userId : '').'"'.(!empty($fields['TOOLTIP_PARAMS']) ? ' bx-tooltip-params="'.htmlspecialcharsbx($fields['TOOLTIP_PARAMS']).'"' : '').'>'.$userName.'</a>'
				: $userName
		);

		return $res;
	}

	public function getTagPattern()
	{
		return $this->tagPattern.BX_UTF_PCRE_MODIFIER;
	}

	function cleanTag($tag)
	{
		return trim(html_entity_decode(str_replace("&nbsp;", " ", $tag), (ENT_COMPAT | ENT_HTML401), SITE_CHARSET));
	}

	function detectTags($text)
	{
		$result = array();

		$text = str_replace("\xC2\xA0", ' ', $text);
		$text = str_replace("\xA0", " ", $text);

		if (preg_match_all($this->getTagPattern(), ' '.$text, $matches))
		{
			$result = array_unique($matches[2]);
		}

		$result = array_map(
			function($tag) { return CTextParser::cleanTag($tag); },
			$result
		);

		$result = array_filter(
			$result,
			function($tag) { return strlen($tag) > 0; }
		);

		return $result;
	}

	function convert_tag($tag = array())
	{
		$res = '';

		if (
			!is_array($tag)
			|| strlen($tag[2]) <= 0
		)
		{
			return $res;
		}

		$tagText = self::cleanTag($tag[2]);

		if (strlen($tagText) <= 0)
		{
			return $tag[0];
		}

		$res = htmlentities($tagText, (ENT_COMPAT | ENT_HTML401), SITE_CHARSET);
		$res = '<span class="bx-inline-tag" bx-tag-value="'.$res.'">#'.$res.'</span>';

		return $tag[1].$this->defended_tags($res, "replace");
	}

	// Only for public using
	function wrap_long_words($text="")
	{
		if ($this->maxStringLen > 0 && !empty($text))
		{
			$text = str_replace(array(chr(11), chr(12), chr(34), chr(39)), array("", "", chr(11), chr(12)), $text);
			$text = preg_replace_callback("/(?<=^|\\>)([^\\<]+)(?=\\<|$)/is".BX_UTF_PCRE_MODIFIER, array($this, "partWords"), $text);
			$text = str_replace(array(chr(11), chr(12)), array(chr(34), chr(39)), $text);
		}
		return $text;
	}

	public function partWords($matches)
	{
		return $this->part_long_words($matches[1]);
	}

	function part_long_words($str)
	{
		$word_separator = $this->wordSeparator;
		if (($this->maxStringLen > 0) && (strlen(trim($str)) > 0))
		{
			$str = str_replace(
				array(chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8),
					"&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;",
					chr(34), chr(39)),
				array("", "", "", "", "", "", "", "",
					chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6),
					chr(7), chr(8)),
				$str
			);
			$str = preg_replace_callback(
				"/(?<=[".$word_separator."]|^)(([^".$word_separator."]+))(?=[".$word_separator."]|$)/is".BX_UTF_PCRE_MODIFIER,
				array($this, "cutWords"),
				$str
			);
			$str = str_replace(
				array(chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
				array("&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", chr(34), chr(39), "<WBR/>", "<WBR/>", "&shy;"),
				$str
			);
		}
		return $str;
	}

	public function cutWords($matches)
	{
		return $this->cut_long_words($matches[2]);
	}

	function cut_long_words($str)
	{
		if (($this->maxStringLen > 0) && (strlen($str) > 0))
			$str = preg_replace("/([^ \n\r\t\x01]{".$this->maxStringLen."})/is".BX_UTF_PCRE_MODIFIER, "\\1<WBR/>&shy;", $str);
		return $str;
	}

	protected function parseSpaces($matches)
	{
		if (strlen($matches[1]) > 0)
			return preg_replace("/\x20{2}/", "\x20&nbsp;", $matches[1]);
		return $matches[1];
	}

	public function convertAnchor($matches)
	{
		return $this->convert_anchor_tag($matches[1], ($matches[2] <> ''? $matches[2] : $matches[1]), '');
	}

	function convert_anchor_tag($url, $text, $pref="")
	{
		$url = trim(str_replace(array("[nomodify]", "[/nomodify]"), "", $url));
		$text = trim(str_replace(array("[nomodify]", "[/nomodify]"), "", $text));
		$text = (strlen($text) <= 0 ? $url : $text);

		$bTextUrl = ($text == $url);
		$bShortUrl = ($this->allow["SHORT_ANCHOR"] == "Y");

		$text = str_replace("\\\"", "\"", $text);
		$end = "";
		$pattern = "/([\\.,\\?\\!\\;]|&#33;)$/".BX_UTF_PCRE_MODIFIER;
		if ($bTextUrl && preg_match($pattern, $url, $match))
		{
			$end = $match[1];
			$url = preg_replace($pattern, "", $url);
			$text = preg_replace($pattern, "", $text);
		}

		$url = preg_replace(
			array(
				"/&amp;/".BX_UTF_PCRE_MODIFIER,
				"/javascript:/i".BX_UTF_PCRE_MODIFIER,
				"/[".chr(12)."\\']/".BX_UTF_PCRE_MODIFIER,
				"/&#91;/".BX_UTF_PCRE_MODIFIER,
				"/&#93;/".BX_UTF_PCRE_MODIFIER
			),
			array(
				"&",
				"java script&#58; ",
				"%27",
				"[",
				"]"
			),
			$url
		);

		if (substr($url, 0, 1) != "/" && !preg_match("/^(".$this->getAnchorSchemes().")\\:/i".BX_UTF_PCRE_MODIFIER, $url))
			$url = "http://".$url;
		$text = preg_replace(
			array("/&amp;/i".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER),
			array("&", "javascript&#58; "),
			$text
		);

		if ($bShortUrl &&
			strlen($text) > $this->maxAnchorLength &&
			preg_match("/^(".$this->getAnchorSchemes()."):\\/\\/(\\S+)$/i".BX_UTF_PCRE_MODIFIER, $text, $matches))
		{
			$uri_type = $matches[1];
			$stripped = $matches[2];
			$text = $uri_type.'://'.(strlen($stripped) > $this->maxAnchorLength ?
					substr($stripped, 0, $this->maxAnchorLength-10).'...'.substr($stripped, -10) :
					$stripped
				);
		}

		$url = $this->defended_tags(htmlspecialcharsbx(htmlspecialcharsback($url)), 'replace');
		return $pref.($this->parser_nofollow == "Y" ? '<noindex>' : '').'<a href="'.$url.'" target="'.$this->link_target.'"'.($this->parser_nofollow == "Y" ? ' rel="nofollow"' : '').'>'.$text.'</a>'.($this->parser_nofollow == "Y" ? '</noindex>' : '').$end;
	}

	private function preconvertUrl($matches)
	{
		return $this->pre_convert_anchor_tag($matches[0], $matches[0], "[url]".$matches[0]."[/url]");
	}

	public function preconvertAnchor($matches)
	{
		return $this->pre_convert_anchor_tag($matches[1], $matches[2], $matches[0]);
	}

	function pre_convert_anchor_tag($url, $text = "", $str = "")
	{
		if (stripos($str, "[url") !== 0)
		{
			$url = $str;
		}
		else if(strlen($text) > 0)
		{
			$word_separator = str_replace(array("\\]", "\\[", "?"), "", $this->wordSeparator);
			$text = preg_replace(
				"/(?<=^|[".$word_separator."]|\\s)(?<!\\[nomodify\\]|<nomodify>)((".$this->getAnchorSchemes()."):\\/\\/[._:a-z0-9@-].*?)(?=[\\s'\"{}\\[\\]]|&quot;|\$)/is".BX_UTF_PCRE_MODIFIER,
				"\\1", $text
			);
			$url = "[url=".$url."]".$text."[/url]";
		}
		else
		{
			$url = "[url]".$url."[/url]";
		}

		if(isset($this->defended_urls[$url]))
		{
			return $this->defended_urls[$url];
		}
		else
		{
			$tag = "<\x18#".count($this->defended_urls).">";
			$this->defended_urls[$url] = $tag;

			return $tag;
		}
	}

	function post_convert_anchor_tag($str)
	{
		if (!empty($this->defended_urls))
			return str_replace(array_reverse(array_values($this->defended_urls)), array_reverse(array_keys($this->defended_urls)), $str);
		else
			return $str;
	}

	function strip_words($string, $count)
	{
		$splice_pos = null;

		$ar = preg_split("/(<.*?>|\\s+)/s", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach($ar as $i => $s)
		{
			if(substr($s, 0, 1) != "<")
			{
				$count -= strlen($s);
				if($count <= 0)
				{
					$splice_pos = $i;
					break;
				}
			}
		}

		if(isset($splice_pos))
		{
			array_splice($ar, $splice_pos+1);
			return implode('', $ar);
		}
		else
		{
			return $string;
		}
	}

	function closeTags($html)
	{
		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$openedtags = array_map("strtolower", $result[1]);

		preg_match_all("#</([a-z0-9]+)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$closedtags = array_map("strtolower", $result[1]);

		$len_opened = count($openedtags);

		if(count($closedtags) == $len_opened)
		{
			return $html;
		}

		$openedtags = array_reverse($openedtags);

		static $tagsWithoutClose = array('input'=>1, 'img'=>1, 'br'=>1, 'hr'=>1, 'meta'=>1, 'area'=>1, 'base'=>1, 'col'=>1, 'embed'=>1, 'keygen'=>1, 'link'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1);

		for($i = 0; $i < $len_opened; $i++)
		{
			if(isset($tagsWithoutClose[$openedtags[$i]]))
			{
				continue;
			}
			if(!in_array($openedtags[$i], $closedtags))
			{
				$html .= '</'.$openedtags[$i].'>';
			}
			else
			{
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}

		return $html;
	}

	public static function clearAllTags($text)
	{
		$text = strip_tags(Trim($text));
		if (strlen($text)<=0) return "";

		if (stripos($text, "<cut") !== false || stripos($text, "[cut") !== false)
		{
			$text = preg_replace(array(
				"/^(.+?)<cut(.*?)>/is".BX_UTF_PCRE_MODIFIER,
				"/^(.+?)\\[cut(.*?)\\]/is".BX_UTF_PCRE_MODIFIER
			), "\\1", $text);
		}
		if (stripos($text, "[quote") !== false)
		{
			while (preg_match("/\\[(?:quote)(?:.*?)\\](.*?)\\[\\/quote(.*?)\\]/is".BX_UTF_PCRE_MODIFIER, $text))
			{
				$text = preg_replace(
					array(
						"/\\[quote(?:.*?)\\](.*?)\\[\\/quote(.*?)\\]/is".BX_UTF_PCRE_MODIFIER,
						"/<quote(?:.*?)>(.*?)<\\/quote(.*?)>/is".BX_UTF_PCRE_MODIFIER
					),
					"\"\\1\"",
					$text
				);
			}
		}

		$arPattern = array();
		$arReplace = array();

		$arPattern[] = "/\\<WBR[\\s\\/]?\\>/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[url\\s*=\\s*(\\S+?)\\s*\\](.*?)\\[\\/url\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\2 (\\1)";

		$arPattern[] = "/\\<(\\/?)(code|font|color|video)(.*?)\\>/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";
		$arPattern[] = "/\\[(\\/?)(p|b|i|u|s|list|code|quote|size|font|color|url|img|video|td|tr|table|file|document id|disk file id|user|left|right|center|justify|\\*)(.*?)\\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";

		return preg_replace($arPattern, $arReplace, $text);
	}

	function html_cut($html, $size)
	{
		$symbols = strip_tags($html);
		$symbols_len = strlen($symbols);

		if($symbols_len < strlen($html))
		{
			$strip_text = $this->strip_words($html, $size);

			if($symbols_len > $size)
				$strip_text = $strip_text."...";

			$final_text = $this->closetags($strip_text);
		}
		elseif($symbols_len > $size)
			$final_text = substr($html, 0, $size)."...";
		else
			$final_text = $html;

		return $final_text;
	}

	function convertHTMLToBB($html = "", $allow = null)
	{
		if (empty($html))
		{
			return $html;
		}

		$handler = AddEventHandler("main", "TextParserBeforeTags", Array("CTextParser", "TextParserHTMLToBBHack"));

		$this->allow = array_merge(
			is_array($allow) ? $allow : array(
				'ANCHOR' => 'Y',
				'BIU'    => 'Y',
				'IMG'    => 'Y',
				'QUOTE'  => 'Y',
				'CODE'   => 'Y',
				'FONT'   => 'Y',
				'LIST'   => 'Y',
				'SMILES' => 'Y',
				'NL2BR'  => 'Y',
				'VIDEO'  => 'Y',
				'TABLE'  => 'Y',
				'ALIGN'  => 'Y',
				'P'      => 'Y'
			),
			array('HTML' => 'N')
		);

		$html = $this->convertText($html);

		$html = preg_replace("/\\<br\s*\\/*\\>/is".BX_UTF_PCRE_MODIFIER,"\n", $html);
		$html = preg_replace("/&nbsp;/is".BX_UTF_PCRE_MODIFIER,"", $html);

		RemoveEventHandler("main", "TextParserBeforeTags", $handler);

		return $html;
	}

	public static function TextParserHTMLToBBHack(&$text, &$TextParser)
	{
		$TextParser->allow = array();
		return true;
	}
}
