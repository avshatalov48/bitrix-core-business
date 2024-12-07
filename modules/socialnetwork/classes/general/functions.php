<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CSocNetTextParser
{
	var $smiles = array();
	var $allow_img_ext = "gif|jpg|jpeg|png";
	var $image_params = array(
		"width" => 300,
		"height" => 300,
		"template" => "popup_image");
	var $LAST_ERROR  = "";
	var $path_to_smile  = false;
	var $quote_error = 0;
	var $quote_open = 0;
	var $quote_closed = 0;
	var $MaxStringLen = 125;
	var $code_error = 0;
	var $code_open = 0;
	var $code_closed = 0;
	var $CacheTime = false;
	var $arFontSize = array(
		0 => 40, //"xx-small"
		1 => 60, //"x-small"
		2 => 80, //"small"
		3 => 100, //"medium"
		4 => 120, //"large"
		5 => 140, //"x-large"
		7 => 160); //"xx-large"
	var $word_separator = "\s.,;:!?\#\-\*\|\[\]\(\)\{\}";

	var $matchNum = 0;
	var $matchNum2 = 0;
	var $matchType = "html";
	var $matchType2 = "";
	var $matchType3 = "";
	var $matchType4 = "";

	function sonet_sortlen($a, $b)
	{
		if (mb_strlen($a["TYPING"]) == mb_strlen($b["TYPING"]))
		{
			return 0;
		}
		return (mb_strlen($a["TYPING"]) > mb_strlen($b["TYPING"])) ? -1 : 1;
	}

	public function __construct($strLang = False, $pathToSmile = false)
	{
		global $DB, $CACHE_MANAGER;
		static $arSmiles = array();

		$this->smiles = array();
		if ($strLang === False)
			$strLang = LANGUAGE_ID;
		$this->path_to_smile = $pathToSmile;

		if($CACHE_MANAGER->Read(604800, "b_sonet_smile"))
		{
			$arSmiles = $CACHE_MANAGER->Get("b_sonet_smile");
		}
		else
		{
			$db_res = CSocNetSmile::GetList(array("SORT" => "ASC"), array("SMILE_TYPE" => "S"/*, "LANG_LID" => $strLang*/), false, false, Array("LANG_LID", "ID", "IMAGE", "DESCRIPTION", "TYPING", "SMILE_TYPE", "SORT"));
			while ($res = $db_res->Fetch())
			{
				$tok = strtok($res['TYPING'], " ");
				while ($tok !== false)
				{
					$arSmiles[$res['LANG_LID']][] = array(
						'TYPING' => $tok,
						'IMAGE'  => stripslashes($res['IMAGE']), // stripslashes is not needed here
						'DESCRIPTION' => stripslashes($res['NAME']) // stripslashes is not needed here
					);
					$tok = strtok(" ");
				}
			}

			foreach ($arSmiles as $LID => $arSmilesLID)
			{
				uasort($arSmilesLID, array('CSocNetTextParser', 'sonet_sortlen'));
				$arSmiles[$LID] = $arSmilesLID;
			}

			$CACHE_MANAGER->Set("b_sonet_smile", $arSmiles);
		}
		$this->smiles = $arSmiles[$strLang] ?? null;
	}

	function convert($text, $bPreview = True, $arImages = array(), $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y"), $type = "html")	//, "KEEP_AMP" => "N"
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);
		$type = ($type == "rss" ? "rss" : "html");

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		if ($allow["HTML"] != "Y")
		{
			if ($allow["CODE"]=="Y")
			{
				$text = str_replace(array("\001", "\002", chr(5), chr(6), "'", "\""), array("", "", "", "", chr(5), chr(6)), $text);
				$text = preg_replace(
					array(
						"#<code(\s+[^>]*>|>)(.+?)</code(\s+[^>]*>|>)#isu",
						"/\[code([^\]])*\]/isu",
						"/\[\/code([^\]])*\]/isu"),
					array(
						"[code]\\2[/code]",
						"\001",
						"\002",
					),
					$text
				);
				$this->matchNum = 2;
				$text = preg_replace_callback(
					"/(?<=[\001])(([^\002]+))(?=([\002]))/isu", 
					array($this, "pre_convert_code_tag_callback"), 
					$text
				);
				$text = preg_replace(
					array(
						"/\001/",
						"/\002/"),
					array(
						"[code]",
						"[/code]"
					), $text
				);
				$text = str_replace(array(chr(5), chr(6)), array("'", "\""), $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"#<a[^>]+href\s*=\s*[\"]+(([^\"])+)[\"]+[^>]*>(.+?)</a[^>]*>#isu",
						"#<a[^>]+href\s*=\s*[\']+(([^\'])+)[\']+[^>]*>(.+?)</a[^>]*>#isu",
						"#<a[^>]+href\s*=\s*(([^\'\"\>])+)>(.+?)</a[^>]*>#isu"),
					"[url=\\1]\\3[/url]", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<b([^>]*)\>(.+?)\<\/b([^>]*)>/isu",
						"/\<u([^>]*)\>(.+?)\<\/u([^>]*)>/isu",
						"/\<s([^>a-z]*)\>(.+?)\<\/s([^>a-z]*)>/isu",
						"/\<i([^>]*)\>(.+?)\<\/i([^>]*)>/isu"),
					array(
						"[b]\\2[/b]",
						"[u]\\2[/u]",
						"[s]\\2[/s]",
						"[i]\\2[/i]"),
					$text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace(
					"#<img[^>]+src\s*=[\s\"']*(((http|https|ftp)://[.-_:a-z0-9@]+)*(\/[-_/=:.a-z0-9@{}&?%]+)+)[\s\"']*[^>]*>#isu",
					"[img]\\1[/img]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				//$text = preg_replace("#(<quote(.*?)>(.*)</quote(.*?)>)#is", "[quote]\\3[/quote]", $text);
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<font[^>]+size\s*=[\s\"']*([0-9]+)[\s\"']*[^>]*\>(.+?)\<\/font[^>]*\>/isu",
						"/\<font[^>]+color\s*=[\s\"']*(\#[a-f0-9]{6})[^>]*\>(.+?)\<\/font[^>]*>/isu",
						"/\<font[^>]+face\s*=[\s\"']*([a-z\s\-]+)[\s\"']*[^>]*>(.+?)\<\/font[^>]*>/isu"),
					array(
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]"),
					$text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<ul((\s[^>]*)|(\s*))\>(.+?)<\/ul([^>]*)\>/isu",
						"/\<li((\s[^>]*)|(\s*))\>/isu"),
					array(
						"[list]\\4[/list]",
						"[*]"),
					$text);
			}
			if ($text <> '')
			{
				$text = str_replace(
					array("<", ">", "\""),
					array("&lt;", "&gt;", "&quot;"),
					$text);
			}
		}
		elseif ($allow["NL2BR"]=="Y")
		{
			$text = str_replace("\n", "<br />", $text);
		}


		if ($allow["ANCHOR"]=="Y")
		{
			$word_separator = str_replace("\]", "", $this->word_separator);
			$text = preg_replace("'(?<=^|[".$word_separator."]|\s)((http|https|news|ftp|aim|mailto)://[\.\-\_\:a-z0-9\@]([^\"\s\'\[\]\{\}\(\)])*)'is",
				"[url]\\1[/url]", $text);
		}
		if ($allow["CODE"] == "Y")
		{
			$text = preg_replace(
				array(
					"/\[code([^\]])*\]/isu",
					"/\[\/code([^\]])*\]/isu"
				),
				array(
					"\001",
					"\002"
				), 
				$text
			);
			$this->matchNum = 2;
			$this->matchType = $type;
			$text = preg_replace_callback(
				"/(\001)([^\002]+)(\002)/isu", 
				array($this, "convert_code_tag_callback"), 
				$text
			);
			$text = preg_replace(
				array(
					"/\001/",
					"/\002/"
				),
				array(
					"[code]",
					"[/code]"
				), 
				$text
			);			
		}
		if ($allow["QUOTE"] == "Y")
		{
			$this->matchNum = 1;
			$this->matchType = $type;
			$text = preg_replace_callback(
				"#(\[quote([^\]])*\](.*)\[/quote([^\]])*\])#is", 
				array($this, "convert_quote_tag_callback"),
				$text
			);			
		}
		if ($allow["IMG"]=="Y")
		{
			$this->matchNum = 1;
			$this->matchType = $type;
			$text = preg_replace_callback(
				"#\[img\](.+?)\[/img\]#i",
				array($this, "convert_image_tag_callback"),
				$text
			);
		}
		if ($allow["ANCHOR"] == "Y")
		{
			$this->matchNum = 1;
			$this->matchNum2 = 1;
			$text = preg_replace_callback(
				"/\[url\]([^\]]+?)\[\/url\]/iu",
				array($this, "convert_anchor_tag_callback"),
				$text
			);
			$this->matchNum = 1;
			$this->matchNum2 = 2;
			$text = preg_replace_callback(
				"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/iu",
				array($this, "convert_anchor_tag_callback"),
				$text
			);
		}
		if ($allow["BIU"]=="Y")
		{
			$text = preg_replace(
				array(
					"/\[b\](.+?)\[\/b\]/isu",
					"/\[i\](.+?)\[\/i\]/isu",
					"/\[s\](.+?)\[\/s\]/isu",
					"/\[u\](.+?)\[\/u\]/isu"),
				array(
					"<b>\\1</b>",
					"<i>\\1</i>",
					"<s>\\1</s>",
					"<u>\\1</u>"), $text);
		}
		if ($allow["LIST"]=="Y")
		{
			$text = preg_replace(
				array(
					"/\[list\](.+?)\[\/list\]/isu",
					"/\[\*\]/u"),
				array(
					"<ul>\\1</ul>",
					"<li>"),
				$text);
		}
		if ($allow["FONT"]=="Y")
		{
			while (preg_match("/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/isu", $text))
			{
				$this->matchNum = 1;
				$this->matchNum2 = 2;
				$this->matchType = 'size';
				$text = preg_replace_callback(
					"/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/isu", 
					array($this, "convert_font_attr_callback"),
					$text
				);				
			}
			while (preg_match("/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/isu", $text))
			{
				$this->matchNum = 1;
				$this->matchNum2 = 2;
				$this->matchType = 'font';
				$text = preg_replace_callback(
					"/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/isu",
					array($this, "convert_font_attr_callback"),
					$text
				);
			}
			while (preg_match("/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/isu", $text))
			{
				$this->matchNum = 1;
				$this->matchNum2 = 2;
				$this->matchType = 'color';
				$text = preg_replace_callback(
					"/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/isu", 
					array($this, "convert_font_attr_callback"),
					$text
				);
			}
		}

		$text = str_replace(
			array(
				"(c)", "(C)",
				"(tm)", "(TM)", "(Tm)", "(tM)",
				"(r)", "(R)",
				"\n"
			),
			array(
				"&copy;", "&copy;",
				"&#153;", "&#153;", "&#153;", "&#153;",
				"&reg;", "&reg;",
				"<br />"
			), 
			$text
		);
		if ($this->MaxStringLen > 0)
		{
			$this->matchNum = 1;
			$text = preg_replace_callback(
				"/(?<=^|\>)([^\<]+)(?=\<|$)/isu", 
				array($this, "part_long_words_callback"),
				$text
			);
		}
		if ($allow["SMILES"]=="Y")
		{
			if (
				is_array($this->smiles)
				&& !empty($this->smiles)
			)
			{
				if ($this->path_to_smile !== false)
				{
					$path_to_smile = $this->path_to_smile;
				}
				else
				{
					$path_to_smile = "/bitrix/images/socialnetwork/smile/";
				}

				$arSmiles = array();
				$arQuoted = array();
				foreach ($this->smiles as $a_id => $row)
				{
					if($row["TYPING"] == '' || $row["IMAGE"] == '')
						continue;
					$typing = htmlspecialcharsbx($row["TYPING"]);
					$arSmiles[$typing] = '<img src="'.$path_to_smile.$row["IMAGE"].'" border="0" alt="smile'.$typing.'" title="'.htmlspecialcharsbx($row["DESCRIPTION"]).'" />';
					$arQuoted[] = preg_quote($typing, "/");
				}
				$ar = preg_split("/(?<=[\s>])(".implode("|", $arQuoted).")/u", " ".$text, -1, PREG_SPLIT_DELIM_CAPTURE);
				$text = "";
				foreach($ar as $piece)
				{
					if(array_key_exists($piece, $arSmiles))
						$text .= $arSmiles[$piece];
					else
						$text .= $piece;
				}
			}
		}
		if (($allow["VIDEO"] ?? null) === "Y")
		{
			while (preg_match("/\[video(.+?)\](.+?)\[\/video[\s]*\]/isu", $text))
			{
				$this->matchNum = 1;
				$this->matchNum2 = 2;
				$text = preg_replace_callback(
					"/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/isu",
					array($this, "convert_video_callback"),
					$text
				);
			}
		}
		return trim($text);
	}

	public static function killAllTags($text)
	{
		if (method_exists("CTextParser", "clearAllTags"))
			return CTextParser::clearAllTags($text);
		$text = strip_tags($text);
		$text = preg_replace(
			array(
				"/\<(\/?)(quote|code|font|color)([^\>]*)\>/isu",
				"/\[(\/?)(b|u|i|list|code|quote|font|color|url|img)([^\]]*)\]/isu"),
			"",
			$text);
		return $text;
	}

	function convert4mail($text)
	{
		$text = Trim($text);
		if ($text == '') return "";
		$arPattern = array();
		$arReplace = array();

		$arPattern[] = "/\[(code|quote)(.*?)\]/isu";
		$arReplace[] = "\n>================== \\1 ===================\n";

		$arPattern[] = "/\[\/(code|quote)(.*?)\]/isu";
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\<WBR[\s\/]?\>/isu";
		$arReplace[] = "";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\[b\](.+?)\[\/b\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\[i\](.+?)\[\/i\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\[u\](.+?)\[\/u\]/isu";
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\[(\/?)(color|font|size)([^\]]*)\]/isu";
		$arReplace[] = "";

		$arPattern[] = "/\[url\](\S+?)\[\/url\]/isu";
		$arReplace[] = "(URL: \\1 )";

		$arPattern[] = "/\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]/isu";
		$arReplace[] = "\\2 (URL: \\1 )";

		$arPattern[] = "/\[img\](.+?)\[\/img\]/isu";
		$arReplace[] = "(IMAGE: \\1)";

		$arPattern[] = "/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/isu";
		$arReplace[] = "(VIDEO: \\2)";

		$arPattern[] = "/\[(\/?)list\]/isu";
		$arReplace[] = "\n";
		$text = preg_replace($arPattern, $arReplace, $text);
		$text = str_replace("&shy;", "", $text);

		return $text;
	}

	function convert_video($params, $path)
	{
		global $APPLICATION;

		if ($path == '')
			return "";

		preg_match("/width\=([0-9]+)/is", $params, $width);
		preg_match("/height\=([0-9]+)/is", $params, $height);
		$width = intval($width[1]);
		$width = ($width > 0 ? $width : 400);
		$height = intval($height[1]);
		$height = ($height > 0 ? $height : 300);

		ob_start();
		$APPLICATION->IncludeComponent(
			"bitrix:player", "",
			Array(
				"PLAYER_TYPE" => "auto",
				"USE_PLAYLIST" => "N",
				"PATH" => $path,
				"WIDTH" => $width,
				"HEIGHT" => $height,
				"PREVIEW" => "",
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
				"DOWNLOAD_LINK_TARGET" => "_self"));
		$video = ob_get_contents();
		ob_end_clean();
		return $video;
	}

	private function convert_video_callback($m)
	{
		return $this->convert_video($m[$this->matchNum], $m[$this->matchNum2]);	
	}

	function convert_emoticon($code = "", $image = "", $description = "", $servername = "")
	{
		if ($code == '' || $image == '') return;
		$code = stripslashes($code);
		$description = stripslashes($description);
		$image = stripslashes($image);
		if ($this->path_to_smile !== false)
			return '<img src="'.$servername.$this->path_to_smile.$image.'" border="0" alt="smile'.$code.'" title="'.$description.'" />';
		return '<img src="'.$servername.'/bitrix/images/socialnetwork/smile/'.$image.'" border="0" alt="smile'.$code.'" title="'.$description.'" />';
	}

	private function convert_emoticon_callback($m)
	{
		return $this->convert_emoticon($this->matchType, $this->matchType2, $this->matchType3, $this->matchType4);	
	}

	function pre_convert_code_tag ($text = "")
	{
		if ($text == '')
		{
			return;
		}
		$text = str_replace(
			array("&", "<", ">", "[", "]"), 
			array("&amp;", "&lt;", "&gt;", "&#91;", "&#93;"), 
			$text
		);
		return $text;
	}

	private function pre_convert_code_tag_callback($m)
	{
		return $this->pre_convert_code_tag($m[$this->matchNum]);
	}	

	function convert_code_tag($text = "", $type = "html")
	{
		if ($text == '') return;
		$type = ($type == "rss" ? "rss" : "html");
		$text = str_replace(array("<", ">", "\\r", "\\n", "\\"), array("&lt;", "&gt;", "&#92;r", "&#92;n", "&#92;"), $text);
		$text = stripslashes($text);
		$text = str_replace(array("  ", "\t", ), array("&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"), $text);
		$txt = $text;

		$this->matchType = 'code';
		$this->matchType2 = $type;
		$txt = preg_replace_callback(
			"/\[code\]/iu",
			array($this, "convert_open_tag_callback"),
			$txt
		);
		$this->matchType = 'code';
		$txt = preg_replace_callback(
			"/\[\/code\]/iu",
			array($this, "convert_close_tag_callback"),
			$txt
		);			

		if (
			($this->code_open == $this->code_closed) 
			&& ($this->code_error == 0)
		)
		{
			return $txt;
		}
		return $text;
	}
	
	private function convert_code_tag_callback($m)
	{
		return $this->convert_code_tag('[code]'.$m[$this->matchNum].'[/code]', $this->matchType);
	}

	function convert_quote_tag($text = "", $type = "html")
	{
		if ($text == '') return;
		$txt = $text;
		$type = ($type == "rss" ? "rss" : "html");

		$this->matchType = 'quote';
		$this->matchType2 = $type;
		$txt = preg_replace_callback(
			"/\[quote([^\]])*\]/iu",
			array($this, "convert_open_tag_callback"),
			$txt
		);
		$this->matchType = 'quote';
		$txt = preg_replace_callback(
			"/\[\/quote([^\]])*\]/iu",
			array($this, "convert_close_tag_callback"),
			$txt
		);			

		if (
			($this->quote_open == $this->quote_closed) 
			&& ($this->quote_error == 0)
		)
		{
			return $txt;
		}
		return $text;
	}
	
	private function convert_quote_tag_callback($m)
	{
		return $this->convert_quote_tag($m[$this->matchNum], $this->matchType);
	}

	function convert_open_tag($marker = "quote", $type = "html")
	{
		$marker = (mb_strtolower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");

		$this->{$marker."_open"}++;
		if ($type == "rss")
			return "\n====".$marker."====\n";
		return "<table class='sonet-".$marker."'><thead><tr><th>".($marker == "quote" ? GetMessage("SONET_QUOTE") : GetMessage("SONET_CODE"))."</th></tr></thead><tbody><tr><td>";
	}

	private function convert_open_tag_callback($m)
	{
		return $this->convert_open_tag($this->matchType, $this->matchType2);
	}

	function convert_close_tag($marker = "quote")
	{
		$marker = (mb_strtolower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");

		if ($this->{$marker."_open"} == 0)
		{
			$this->{$marker."_error"}++;
			return;
		}
		$this->{$marker."_closed"}++;
		if ($type == "rss")
			return "\n=============\n";
		return "</td></tr></tbody></table>";
	}

	private function convert_close_tag_callback($m)
	{
		return $this->convert_close_tag($this->matchType);
	}

	function convert_image_tag($url = "", $type = "html")
	{
		static $bShowedScript = false;
		if ($url == '') return;
		$url = trim($url);
		$type = (mb_strtolower($type) == "rss" ? "rss" : "html");
		$extension = preg_replace("/^.*\.(\S+)$/u", "\\1", $url);
		$extension = mb_strtolower($extension);
		$extension = preg_quote($extension, "/");

		$bErrorIMG = False;
		if (preg_match("/[?&;]/u", $url)) $bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/$extension(\||\$)/u", $this->allow_img_ext)) $bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/^(http|https|ftp|\/)/iu", $url)) $bErrorIMG = True;

		if ($bErrorIMG)
		{
			return "[img]".$url."[/img]";
		}

		return '<img src="'.$url.'" alt="'.GetMessage("FRM_IMAGE_ALT").'" border="0" />';
	}

	private function convert_image_tag_callback($m)
	{
		return $this->convert_image_tag($m[$this->matchNum], $this->matchType);
	}

	function convert_font_attr($attr, $value = "", $text = "")
	{
		if ($text == '') return "";
		if ($value == '') return $text;

		if ($attr == "size")
		{
			$count = count($this->arFontSize);
			if ($count <= 0)
				return $text;
			$value = intval($value >= $count ? ($count - 1) : $value);
			return "<span style='font-size:".$this->arFontSize[$value]."%;'>".$text."</span>";
		}
		else if ($attr == 'color')
		{
			$value = preg_replace("/[^\w#]/", "" , $value);
			return "<font color='".$value."'>".$text."</font>";
		}
		else if ($attr == 'font')
		{
			$value = preg_replace("/[^\w]/", "" , $value);
			return "<font face='".$value."'>".$text."</font>";
		}
	}
	
	private function convert_font_attr_callback($m)
	{
		return $this->convert_font_attr($this->matchType, $m[$this->matchNum], $m[$this->matchNum2]);
	}

	// Only for public using
	function wrap_long_words($text="")
	{
		if (
			$this->MaxStringLen > 0 
			&& !empty($text)
		)
		{
			$text = str_replace(array(chr(7), chr(8), chr(34), chr(39)), array("", "", chr(7), chr(8)), $text);
			$this->matchNum = 1;
			$text = preg_replace_callback(
				"/(?<=^|\>)([^\<]+)(?=\<|$)/isu", 
				array($this, "part_long_words_callback"),
				$text
			);
			$text = str_replace(array(chr(7), chr(8)), array(chr(34), chr(39)), $text);
		}
		return $text;
	}

	function part_long_words($str)
	{
		$word_separator = $this->word_separator;
		if (($this->MaxStringLen > 0) && (trim($str) <> ''))
		{
			$str = str_replace(
				array(chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), "&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;"),
				array("", "", "", "", "", "", chr(5), "<", ">", chr(6), chr(1), chr(2), chr(3), chr(4)),
				$str
			);
			$this->matchNum = 2;
			$str = preg_replace_callback(
				"/(?<=[".$word_separator."]|^)(([^".$word_separator."]+))(?=[".$word_separator."]|$)/isu", 
				array($this, "cut_long_words_callback"),
				$str
			);
			$str = str_replace(
				array(chr(5), "<", ">", chr(6), chr(1), chr(2), chr(3), chr(4), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
				array("&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", "<WBR/>", "<WBR/>", "&shy;"),
				$str
			);
		}
		return $str;
	}
	
	private function part_long_words_callback($m)
	{
		return $this->cut_long_words($m[$this->matchNum]);
	}

	function cut_long_words($str)
	{
		if (
			($this->MaxStringLen > 0) 
			&& ($str <> '')
		)
		{
			$str = preg_replace(
				"/([^ \n\r\t\x01]{".$this->MaxStringLen."})/isu", 
				"\\1<WBR/>&shy;", 
				$str
			);
		}
		return $str;
	}
	
	function cut_long_words_callback($m)
	{
		return $this->cut_long_words($m[$this->matchNum]);
	}	

	function convert_anchor_tag($url, $text, $pref="")
	{
		$bCutUrl = True;
		$text = str_replace("\\\"", "\"", $text);
		$end = "";
		if (preg_match("/([\.,\?]|&#33;)$/u", $url, $match))
		{
			$end = $match[1];
			$url = preg_replace("/([\.,\?]|&#33;)$/u", "", $url);
			$text = preg_replace("/([\.,\?]|&#33;)$/u", "", $text);
		}
		if (preg_match("/\[\/(quote|code)/i", $url))
			return $url;
		$url = preg_replace(
			array("/&amp;/u", "/javascript:/iu"),
			array("&", "java script&#58; ") , $url);
		if (mb_substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//iu", $url))
			$url = 'http://'.$url;
		if (!preg_match("/^((http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+)*([^\"\'])+$/iu", $url))
			return $pref.$text." (".$url.")".$end;

		if (preg_match("/^<img\s+src/iu", $text))
			$bCutUrl = False;
		$text = preg_replace(
			array("/&amp;/iu", "/javascript:/iu"),
			array("&", "javascript&#58; "), $text);
		if ($bCutUrl && mb_strlen($text) < 55)
			$bCutUrl = False;
		if ($bCutUrl && !preg_match("/^(http|ftp|https|news):\/\//iu", $text))
			$bCutUrl = False;

		if ($bCutUrl)
		{
			$stripped = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/iu", "\\2", $text);
			$uri_type = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/iu", "\\1", $text);
			$text = $uri_type.'://'.mb_substr($stripped, 0, 30).'...'.mb_substr($stripped, -10);
		}

		return $pref."<a href='".$url."' target='_blank'>".$text."</a>".$end;
	}

	function convert_anchor_tag_callback($m)
	{
		return $this->convert_anchor_tag($m[$this->matchNum], $m[$this->matchNum2], '');
	}

	function convert_to_rss($text, $arImages = Array(), $arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N"), $arParams = array())
	{
		global $DB;
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
				"NL2BR" => "N");

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		$bAllowSmiles = $arAllow["SMILES"];
		if ($arAllow["HTML"]!="Y")
		{
			$text = preg_replace(
				array(
					"#^(.+?)<cut[\s]*(/>|>).*?$#isu",
					"#^(.+?)\[cut[\s]*(/\]|\]).*?$#isu"),
				"\\1", $text);
			$arAllow["SMILES"] = "N";
			$text = $this->convert($text, $arAllow, "rss");
		}
		else
		{
			if ($arAllow["NL2BR"]=="Y")
				$text = str_replace("\n", "<br />", $text);
		}

		if ($arParams["SERVER_NAME"] == '')
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$arParams["SERVER_NAME"] = htmlspecialcharsEx($arSite["SERVER_NAME"]);
			if ($arParams["SERVER_NAME"] == '')
			{
				if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
					$arParams["SERVER_NAME"] = SITE_SERVER_NAME;
				else
					$arParams["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
			}
		}

		if ($bAllowSmiles == "Y")
		{
			if (count($this->smiles) > 0)
			{
				foreach ($this->smiles as $a_id => $row)
				{
					$code  = preg_quote(str_replace("'", "\\'", $row["TYPING"]), "/");
					$image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
					$description = preg_quote(htmlspecialcharsbx($row["DESCRIPTION"], ENT_QUOTES), "/");

					$this->matchType = $code;
					$this->matchType2 = $image;
					$this->matchType3 = $description;
					$this->matchType4 = "http://".$arParams["SERVER_NAME"];

					$text = preg_replace_callback(
						"/(?<=[^\w&])$code(?=.\W|\W.|\W$)/i", 
						array($this, "convert_emoticon_callback"), 
						$text
					);

				}
			}
		}
		return trim($text);
	}

	function strip_words($string, $count)
	{
		$result = "";
		$counter_plus  = true;
		$counter = 0;
		$string_len = mb_strlen($string);
		for($i=0; $i<$string_len; ++$i)
		{
			$char = mb_substr($string, $i, 1);
			if($char == '<')
				$counter_plus = false;
			if($char == '>' && mb_substr($string, $i + 1, 1) != '<')
			{
				$counter_plus = true;
				$counter--;
			}
			$result .= $char;
			if ($counter_plus)
				$counter++;
			if($counter >= $count)
			{
				$pos_space = mb_strpos($string, " ", $i);
				$pos_tag = mb_strpos($string, "<", $i);
				if ($pos_space == false)
				{
					$pos = mb_strrpos($result, " ");
					$result = mb_substr($result, 0, mb_strlen($result) - ($i - $pos + 1));
				}
				else
				{
					$pos = min($pos_space, $pos_tag);
					if ($pos != $i)
					{
						$dop_str = mb_substr($string, $i + 1, $pos - $i - 1);
						$result .= $dop_str;
					}
					else
						$result = mb_substr($result, 0, mb_strlen($result) - 1);
				}
				break;
			}
		}
		return $result;
	}

	public static function closetags($html)
	{
		$arNoClose = array('br','hr','img','area','base','basefont','col','frame','input','isindex','link','meta','param');

		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#iu", $html, $result);
		$openedtags = $result[1];

		preg_match_all("#</([a-z0-9]+)>#iu", $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);

		if(count($closedtags) == $len_opened)
			return $html;

		$openedtags = array_reverse($openedtags);

		for($i = 0; $i < $len_opened; $i++)
		{
			if (!in_array($openedtags[$i], $closedtags))
			{
				if (!in_array($openedtags[$i], $arNoClose))
					$html .= '</'.$openedtags[$i].'>';
			}
			else
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}

		return $html;
	}

	function html_cut($html, $size)
	{
		$symbols = strip_tags($html);
		$symbols_len = mb_strlen($symbols);

		if($symbols_len < mb_strlen($html))
		{
			$strip_text = $this->strip_words($html, $size);

			if($symbols_len > $size)
				$strip_text = $strip_text."...";

			$final_text = $this->closetags($strip_text);
		}
		else
			$final_text = mb_substr($html, 0, $size);

		return $final_text;
	}

}

class CSocNetTools
{
	public static function InitImage($imageID, $imageSize, $defaultImage, $defaultImageSize, $imageUrl, $showImageUrl, $urlParams=false)
	{
		$imageFile = false;
		$imageImg = "";

		$imageSize = intval($imageSize);
		if($imageSize <= 0)
			$imageSize = 100;

		$defaultImageSize = intval($defaultImageSize);
		if($defaultImageSize <= 0)
			$defaultImageSize = 100;

		$imageUrl = trim($imageUrl);
		$imageID = intval($imageID);

		if($imageID > 0)
		{
			$imageFile = CFile::GetFileArray($imageID);
			if ($imageFile !== false)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$imageFile,
					array("width" => $imageSize, "height" => $imageSize),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false
				);
				$imageImg = CFile::ShowImage($arFileTmp["src"], $imageSize, $imageSize, "border=0", "", ($imageUrl == ''));
			}
		}
		if($imageImg == '')
			$imageImg = "<img src=\"".$defaultImage."\" width=\"".$defaultImageSize."\" height=\"".$defaultImageSize."\" border=\"0\" alt=\"\" />";

		if($imageUrl <> '' && $showImageUrl)
			$imageImg = "<a href=\"".$imageUrl."\"".($urlParams !== false? ' '.$urlParams:'').">".$imageImg."</a>";

		return array("FILE" => $imageFile, "IMG" => $imageImg);
	}

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
					$res[$k] = htmlspecialcharsex($v);
					$res['~'.$k] = $v;
				}
			}
		}
		return $res;
	}

	public static function ResizeImage($aFile, $sizeX, $sizeY)
	{
		$result = CFile::ResizeImageGet($aFile, array("width" => $sizeX, "height" => $sizeY));
		if(is_array($result))
			return $result["src"];
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

	public static function Birthday($datetime, $gender, $showYear = "N")
	{
		if ($datetime == '')
			return false;

		$arDateTmp = ParseDateTime($datetime, CSite::GetDateFormat('SHORT'));

		$day = intval($arDateTmp["DD"]);
		if (isset($arDateTmp["M"]))
		{
			if (is_numeric($arDateTmp["M"]))
			{
				$month = intval($arDateTmp["M"]);
			}
			else
			{
				$month = GetNumMonth($arDateTmp["M"], true);
				if (!$month)
					$month = intval(date('m', strtotime($arDateTmp["M"])));
			}
		}
		elseif (isset($arDateTmp["MMMM"]))
		{
			if (is_numeric($arDateTmp["MMMM"]))
			{
				$month = intval($arDateTmp["MMMM"]);
			}
			else
			{
				$month = GetNumMonth($arDateTmp["MMMM"]);
				if (!$month)
					$month = intval(date('m', strtotime($arDateTmp["MMMM"])));
			}
		}
		else
		{
			$month = intval($arDateTmp["MM"]);
		}
		$arDateTmp["MM"] = $month;
		
		$year = intval($arDateTmp["YYYY"]);

		if (($showYear == 'Y') || ($showYear == 'M' && $gender == 'M'))
			$date_template = GetMessage("SONET_BIRTHDAY_DAY_TEMPLATE");
		else
			$date_template = GetMessage("SONET_BIRTHDAY_DAY_TEMPLATE_WO_YEAR");

		$val = str_replace(
			array("#DAY#", "#MONTH#", "#MONTH_LOW#", "#YEAR#"),
			array($day, GetMessage("MONTH_".$month."_S"), mb_strtolower(GetMessage("MONTH_".$month."_S")), $year),
			$date_template
		);

		return array(
			"DATE" => $val,
			"MONTH" => Str_Pad(intval($arDateTmp["MM"]), 2, "0", STR_PAD_LEFT),
			"DAY" => Str_Pad(intval($arDateTmp["DD"]), 2, "0", STR_PAD_LEFT)
		);
	}

	public static function GetDefaultNameTemplates()
	{
		return array(
			'#NOBR##LAST_NAME# #NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_JOHN'),
			'#NOBR##LAST_NAME# #NAME##/NOBR# #SECOND_NAME#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_JOHN_LLOYD'),
			'#LAST_NAME#, #NOBR##NAME# #SECOND_NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_COMMA_JOHN_LLOYD'),
			'#NAME# #SECOND_NAME# #LAST_NAME#' => GetMessage('SONET_NAME_TEMPLATE_JOHN_LLOYD_SMITH'),
			'#NOBR##NAME_SHORT# #SECOND_NAME_SHORT# #LAST_NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_J_L_SMITH'),
			'#NOBR##NAME_SHORT# #LAST_NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_J_SMITH'),
			'#NOBR##LAST_NAME# #NAME_SHORT##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_J'),
			'#NOBR##LAST_NAME# #NAME_SHORT# #SECOND_NAME_SHORT##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_J_L'),
			'#NOBR##LAST_NAME#, #NAME_SHORT##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_COMMA_J'),
			'#NOBR##LAST_NAME#, #NAME_SHORT# #SECOND_NAME_SHORT##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_SMITH_COMMA_J_L'),
			'#NOBR##NAME# #LAST_NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_JOHN_SMITH'),
			'#NOBR##NAME# #SECOND_NAME_SHORT# #LAST_NAME##/NOBR#' => GetMessage('SONET_NAME_TEMPLATE_JOHN_L_SMITH'),
		);
	}

	public static function GetMyGroups()
	{
		global $USER;

		$arGroupsMy = array();
		$dbRequests = CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $USER->getId(),
				"<=ROLE" => SONET_ROLES_USER,
				"GROUP_ACTIVE" => "Y"
			),
			false,
			false,
			array("GROUP_ID")
		);
		while ($arRequests = $dbRequests->Fetch())
		{
			$arGroupsMy[] = $arRequests["GROUP_ID"];
		}

		return $arGroupsMy;
	}

	public static function GetGroupUsers($group_id)
	{
		if (intval($group_id) <= 0)
			return false;

		$arGroupUsers = array();
		$dbRequests = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" 		=> $group_id,
				"<=ROLE" 		=> SONET_ROLES_USER,
				"USER_ACTIVE"	=> "Y"
			),
			false,
			false,
			array("USER_ID")
		);
		while ($arRequests = $dbRequests->Fetch())
			$arGroupUsers[] = $arRequests["USER_ID"];

		return $arGroupUsers;
	}

	public static function IsMyGroup($entity_id)
	{
		global $USER;

		$is_my = false;
		$dbRequests = CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $USER->getId(),
				"GROUP_ID" => $entity_id,
				"<=ROLE" => SONET_ROLES_USER,
			)
		);
		if ($arRequests = $dbRequests->Fetch())
			$is_my = true;

		return $is_my;
	}

	public static function GetMyUsers($user_id = false)
	{
		global $USER;

		if (!$user_id)
		{
			$user_id = $USER->getId();
		}

		$arUsersMy = false;
		if (CSocNetUser::IsFriendsAllowed())
		{
			$arUsersMy = array();
			$dbFriends = CSocNetUserRelations::GetRelatedUsers($user_id, SONET_RELATIONS_FRIEND);
			if ($dbFriends)
				while ($arFriends = $dbFriends->Fetch())
				{
					$pref = (($user_id == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");
					$arUsersMy[] = $arFriends[$pref."_USER_ID"];
				}
		}
		return $arUsersMy;
	}

	public static function IsMyUser($entity_id)
	{
		global $USER;

		$is_my = false;
		if (
			CSocNetUser::IsFriendsAllowed()
			&& CSocNetUserRelations::IsFriends($USER->getId(), $entity_id)
		)
			$is_my = true;

		return $is_my;
	}

	public static function HasLogEventCreatedBy($event_id)
	{
		return CSocNetLogTools:: HasLogEventCreatedBy($event_id);
	}

	public static function InitGlobalExtranetArrays($SITE_ID = SITE_ID)
	{
		global $USER;

		if (
			!isset($GLOBALS["arExtranetGroupID"])
			|| !isset($GLOBALS["arExtranetUserID"])
		)
		{
			$GLOBALS["arExtranetGroupID"] = array();
			$GLOBALS["arExtranetUserID"] = array();

			if($USER?->IsAuthorized())
			{
				$GLOBALS["arExtranetGroupID"] = \Bitrix\Socialnetwork\ComponentHelper::getExtranetSonetGroupIdList();
				$GLOBALS["arExtranetUserID"] = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();
			}
		}
	}

	/**
	 * @deprecated Use CUser::GetSubordinateGroups() from main 23.600.0
	 */
	public static function GetSubordinateGroups($userID = false)
	{
		global $USER;
		static $arSubordinateGroupsByUser = array();

		$userID = intval($userID);
		if ($userID <= 0)
		{
			$userID = $USER->getId();
		}

		if ($userID <= 0)
		{
			return array();
		}

		if (isset($arSubordinateGroupsByUser[$userID]))
		{
			$arUserSubordinateGroups = $arSubordinateGroupsByUser[$userID];
		}
		else
		{
			$arUserSubordinateGroups = CGroup::GetSubordinateGroups(CUser::GetUserGroup($userID));

			$arSubordinateGroupsByUser[$userID] = $arUserSubordinateGroups;
		}

		return $arUserSubordinateGroups;
	}
}

class CSocNetAllowed
{
	private static $arAllowedEntityTypes = array();
	private static $arAllowedEntityTypesDesc = array();
	private static $arAllowedFeatures = array();
	private static $arAllowedLogEvents = array();

	/* --- entity types --- */

	public static function RunEventForAllowedEntityType()
	{
		global $arSocNetAllowedSubscribeEntityTypesDesc;
		$arSocNetAllowedSubscribeEntityTypesDesc = array();

		$newAllowedEntityTypes = array();

		$events = GetModuleEvents("socialnetwork", "OnFillSocNetAllowedSubscribeEntityTypes");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array(&$newAllowedEntityTypes));
		}

		foreach($newAllowedEntityTypes as $entityType)
		{
			self::AddAllowedEntityType($entityType);
		}

		foreach($arSocNetAllowedSubscribeEntityTypesDesc as $entityTypeDescCode => $arEntityTypeDesc)
		{
			self::AddAllowedEntityTypeDesc($entityTypeDescCode, $arEntityTypeDesc);
		}

		unset($arSocNetAllowedSubscribeEntityTypesDesc);
	}

	public static function addAllowedEntityType($entityType)
	{
		if (is_array($entityType))
		{
			foreach ($entityType as $tmp)
			{
				self::AddAllowedEntityType($tmp);
			}
			return true;
		}

		$entityType = trim($entityType);
		if (
			$entityType == ''
			|| in_array($entityType, self::$arAllowedEntityTypes)
			|| !preg_match('/^[a-zA-Z0-9]+$/', $entityType)
		)
		{
			return false;
		}

		if (
			$entityType == SONET_SUBSCRIBE_ENTITY_GROUP
			&& !CBXFeatures::IsFeatureEnabled("Workgroups")
		)
		{
			return false;
		}

		self::$arAllowedEntityTypes[] = $entityType;
	}

	public static function getAllowedEntityTypes()
	{
		self::getAllowedFeatures(); // to initialize standard features
		self::runEvents();
		return self::$arAllowedEntityTypes;
	}

	/* --- entity types desc --- */

	public static function addAllowedEntityTypeDesc($entityTypeDescCode, $arEntityTypeDesc)
	{
		$entityTypeDescCode = trim($entityTypeDescCode);

		if (
			$entityTypeDescCode == ''
			|| array_key_exists($entityTypeDescCode, self::$arAllowedEntityTypesDesc)
			|| !is_array($arEntityTypeDesc)
		)
		{
			return false;
		}

		if (
			$entityTypeDescCode == SONET_SUBSCRIBE_ENTITY_GROUP
			&& !CBXFeatures::IsFeatureEnabled("Workgroups")
		)
		{
			return false;
		}

		self::$arAllowedEntityTypesDesc[$entityTypeDescCode] = $arEntityTypeDesc;

		return true;
	}

	public static function getAllowedEntityTypesDesc()
	{
		self::getAllowedFeatures(); // to initialize standard features
		self::runEvents();
		return self::$arAllowedEntityTypesDesc;
	}

	/* --- features --- */

	public static function runEventForAllowedFeature()
	{
		$newAllowedFeatures = [];

		$ignoreList = [];

		$events = GetModuleEvents('socialnetwork', 'OnFillSocNetFeaturesList');
		while ($arEvent = $events->Fetch())
		{
			if ($arEvent['TO_MODULE_ID'] === 'wiki')
			{
				if (
					Loader::includeModule('bitrix24')
					&& !\Bitrix\Bitrix24\Feature::isFeatureEnabled('socialnetwork_wiki')
				)
				{
					$ignoreList[] = $arEvent['TO_MODULE_ID'];
				}
			}

			if (!in_array($arEvent['TO_MODULE_ID'], $ignoreList, true))
			{
				ExecuteModuleEventEx($arEvent, array(&$newAllowedFeatures, SITE_ID));
			}
		}

		foreach($newAllowedFeatures as $strFeatureCode => $arFeature)
		{
			self::addAllowedFeature($strFeatureCode, $arFeature);
		}
	}

	public static function addStandardFeatureList()
	{
		if (ModuleManager::isModuleInstalled('forum'))
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"full" => array(),
					"newtopic" => array(),
					"answer" => array(),
					"view" => array(),
				),
				"minoperation" => array("view"),
				"subscribe_events" => array(
					"forum" =>  array(
						"ENTITIES" => array(),
						"OPERATION" => "view",
						"CLASS_FORMAT" => "CSocNetLogTools",
						"METHOD_FORMAT" => "FormatEvent_Forum",
						"HAS_CB" => "Y",
						"COMMENT_EVENT"	=> array(
							"EVENT_ID" => "forum",
							"OPERATION" => "view",
							"OPERATION_ADD" => "answer",
							"ADD_CALLBACK" => array("CSocNetLogTools", "AddComment_Forum"),
							"UPDATE_CALLBACK" => array("CSocNetLogTools", "UpdateComment_Forum"),
							"DELETE_CALLBACK" => array("CSocNetLogTools", "DeleteComment_Forum"),
							"CLASS_FORMAT" => "CSocNetLogTools",
							"METHOD_FORMAT" => "FormatComment_Forum",
							"RATING_TYPE_ID" => "FORUM_POST"
						)
					)
				)
			);

			if (COption::GetOptionString("socialnetwork", "allow_forum_user", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["forum"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" => GetMessage("SOCNET_LOG_FORUM_USER"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_FORUM_USER_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_FORUM_USER_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_FORUM_USER_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["full"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_forum_operation_full_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["newtopic"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_forum_operation_newtopic_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["answer"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_forum_operation_answer_user", (CSocNetUser::IsFriendsAllowed() ? SONET_RELATIONS_TYPE_FRIENDS : SONET_RELATIONS_TYPE_AUTHORIZED));
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_forum_operation_view_user", SONET_RELATIONS_TYPE_ALL);
			}

			if (COption::GetOptionString("socialnetwork", "allow_forum_group", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["forum"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" => GetMessage("SOCNET_LOG_FORUM_GROUP"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_FORUM_GROUP_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_FORUM_GROUP_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_FORUM_GROUP_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["full"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_forum_operation_full_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["newtopic"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_forum_operation_newtopic_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["answer"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_forum_operation_answer_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_forum_operation_view_group", SONET_ROLES_USER);
			}

			\CSocNetAllowed::addAllowedFeature("forum", $arFeatureTmp);
		}

		if (ModuleManager::isModuleInstalled('photogallery'))
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"write" => array(),
					"view" => array(),
				),
				"minoperation" => array("view"),
				"subscribe_events" => array(
					"photo" =>  array(
						"ENTITIES" => array(),
						"OPERATION" => "view",
						"CLASS_FORMAT" => "CSocNetLogTools",
						"METHOD_FORMAT"	=> "FormatEvent_Photo",
						"HAS_CB" => "Y",
						"FULL_SET" => array("photo", "photo_photo", "photo_comment"),
						"COMMENT_EVENT"	=> array(
							"EVENT_ID" => "photoalbum_comment",
							"OPERATION" => "view",
							"OPERATION_ADD"	=> "view",
							"ADD_CALLBACK" => array("CSocNetPhotoCommentEvent", "AddComment_PhotoAlbum"),
							"UPDATE_CALLBACK" => "NO_SOURCE",
							"DELETE_CALLBACK" => "NO_SOURCE",
							"CLASS_FORMAT" => "CSocNetLogTools",
							"METHOD_FORMAT"	=> "FormatComment_PhotoAlbum",
							"RATING_TYPE_ID" => "LOG_COMMENT"
						)
					),
					"photo_photo" =>  array(
						"OPERATION" => "view",
						"CLASS_FORMAT" => "CSocNetLogTools",
						"METHOD_FORMAT"	=> "FormatEvent_PhotoPhoto",
						"HIDDEN" => true,
						"HAS_CB" => "Y",
						"ENTITIES" => array(
							SONET_SUBSCRIBE_ENTITY_USER => array(),
							SONET_SUBSCRIBE_ENTITY_GROUP => array()
						),
						"COMMENT_EVENT"	=> array(
							"EVENT_ID" => "photo_comment",
							"OPERATION" => "view",
							"OPERATION_ADD"	=> "view",
							"ADD_CALLBACK" => array("CSocNetPhotoCommentEvent", "AddComment_Photo"),
							"UPDATE_CALLBACK" => array("CSocNetPhotoCommentEvent", "UpdateComment_Photo"),
							"DELETE_CALLBACK" => array("CSocNetPhotoCommentEvent", "DeleteComment_Photo"),
							"CLASS_FORMAT" => "CSocNetLogTools",
							"METHOD_FORMAT"	=> "FormatComment_Photo"
						)
					)
				)
			);

			if (COption::GetOptionString("socialnetwork", "allow_photo_user", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["photo"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" => GetMessage("SOCNET_LOG_PHOTO_USER"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_PHOTO_USER_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_PHOTO_USER_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_PHOTO_USER_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_photo_operation_write_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_photo_operation_view_user", SONET_RELATIONS_TYPE_ALL);
			}

			if (COption::GetOptionString("socialnetwork", "allow_photo_group", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["photo"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" 			=> GetMessage("SOCNET_LOG_PHOTO_GROUP"),
					"TITLE_SETTINGS"	=> GetMessage("SOCNET_LOG_PHOTO_GROUP_SETTINGS"),
					"TITLE_SETTINGS_1"	=> GetMessage("SOCNET_LOG_PHOTO_GROUP_SETTINGS_1"),
					"TITLE_SETTINGS_2"	=> GetMessage("SOCNET_LOG_PHOTO_GROUP_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_photo_operation_write_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_photo_operation_view_group", SONET_ROLES_USER);
			}

			\CSocNetAllowed::addAllowedFeature("photo", $arFeatureTmp);
		}

		$bIntranet = ModuleManager::isModuleInstalled('intranet');
		$bCalendar = (
			$bIntranet
			&& ModuleManager::isModuleInstalled('calendar')
			&& CBXFeatures::IsFeatureEditable("calendar")
		);

		if ($bCalendar)
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"write" => array(),
					"view" => array(),
				),
				"minoperation" => array("view"),
			);

			if (COption::GetOptionString("socialnetwork", "allow_calendar_user", "Y") == "Y")
			{
				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_calendar_operation_write_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_calendar_operation_view_user", SONET_RELATIONS_TYPE_ALL);
			}

			if (COption::GetOptionString("socialnetwork", "allow_calendar_group", "Y") == "Y")
			{
				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_calendar_operation_write_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_calendar_operation_view_group", SONET_ROLES_USER);
			}

			\CSocNetAllowed::addAllowedFeature("calendar", $arFeatureTmp);
		}

		if (
			$bIntranet
			&& ModuleManager::isModuleInstalled('tasks')
		)
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"view" => array(),
					"view_all" => array(),
					"sort" => array(),
					"create_tasks" => array(),
					"edit_tasks" => array(),
					"delete_tasks" => array(),
					"modify_folders" => array(),
					"modify_common_views" => array(),
				),
				"minoperation" => array("view_all", "view"),
				"subscribe_events" => array(
					"tasks" =>  array(
						"ENTITIES" => array(),
						'FORUM_COMMENT_ENTITY' => 'TK',
						"OPERATION" => "view",
						"CLASS_FORMAT" => "CSocNetLogTools",
						"METHOD_FORMAT" => "FormatEvent_Task2",
						"HAS_CB" => "Y",
						"FULL_SET" => array("tasks", "tasks_comment", "crm_activity_add"),
						"COMMENT_EVENT" => array(
							"EVENT_ID" => "tasks_comment",
							"OPERATION" => "view",
							"OPERATION_ADD"	=> "log_rights",
							"ADD_CALLBACK" => array("CSocNetLogTools", "AddComment_Tasks"),
							"UPDATE_CALLBACK" => array("CSocNetLogTools", "UpdateComment_Task"),
							"DELETE_CALLBACK" => array("CSocNetLogTools", "DeleteComment_Task"),
							"CLASS_FORMAT" => "CSocNetLogTools",
							"METHOD_FORMAT"	=> "FormatComment_Forum",
							"METHOD_CANEDIT" => array("CSocNetLogTools", "CanEditComment_Task"),
							"METHOD_CANEDITOWN" => array("CSocNetLogTools", "CanEditOwnComment_Task"),
							"METHOD_GET_URL" => array("CSocNetLogTools", "GetCommentUrl_Task"),
							"RATING_TYPE_ID" => "FORUM_POST"
						)
					)
				)
			);

			if (COption::GetOptionString("socialnetwork", "allow_tasks_user", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["tasks"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" => GetMessage("SOCNET_LOG_TASKS_USER"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_TASKS_USER_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_TASKS_USER_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_TASKS_USER_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_view_user", SONET_RELATIONS_TYPE_ALL);
				$arFeatureTmp["operations"]["view_all"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_view_all_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["create_tasks"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_create_tasks_user", SONET_RELATIONS_TYPE_AUTHORIZED);
				$arFeatureTmp["operations"]["edit_tasks"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_edit_tasks_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["delete_tasks"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_delete_tasks_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["modify_folders"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_modify_folders_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["modify_common_views"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_tasks_operation_modify_common_views_user", SONET_RELATIONS_TYPE_NONE);
			}

			if (COption::GetOptionString("socialnetwork", "allow_tasks_group", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["tasks"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" => GetMessage("SOCNET_LOG_TASKS_GROUP"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_TASKS_GROUP_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_TASKS_GROUP_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_TASKS_GROUP_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_view_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["view_all"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_view_all_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["sort"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_sort_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["create_tasks"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_create_tasks_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["edit_tasks"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_edit_tasks_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["delete_tasks"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_delete_tasks_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["modify_folders"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_modify_folders_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["modify_common_views"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_tasks_operation_modify_common_views_group", SONET_ROLES_MODERATOR);
			}

			\CSocNetAllowed::addAllowedFeature("tasks", $arFeatureTmp);
		}

		// files
		if (
			$bIntranet
			&& (
				ModuleManager::isModuleInstalled('webdav')
				|| ModuleManager::isModuleInstalled('disk')
			)
			&& (
				COption::GetOptionString("socialnetwork", "allow_files_user", "Y") == "Y"
				|| COption::GetOptionString("socialnetwork", "allow_files_group", "Y") == "Y"
			)
		)
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"view" => array(),
					"write_limited" => array(),
				),
				"minoperation" => array("view"),
				"subscribe_events" => array(
					"files" => array(
						"ENTITIES" => array(),
						"OPERATION" => "view",
						"CLASS_FORMAT" => "CSocNetLogTools",
						"METHOD_FORMAT" => "FormatEvent_Files",
						"HAS_CB" => "Y",
						"FULL_SET" => array("files", "files_comment"),
						"COMMENT_EVENT" => array(
							"EVENT_ID" => "files_comment",
							"OPERATION" => "view",
							"OPERATION_ADD" => "",
							"ADD_CALLBACK" => array("CSocNetLogTools", "AddComment_Files"),
							"CLASS_FORMAT" => "CSocNetLogTools",
							"METHOD_FORMAT" => "FormatComment_Files"
						)
					)
				)
			);

			if (ModuleManager::isModuleInstalled("bizproc"))
			{
				$arFeatureTmp["operations"]["bizproc"] = array();
			}

			$arFeatureTmp["operations"]["write"] = array();

			if (COption::GetOptionString("socialnetwork", "allow_files_user", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["files"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" => GetMessage("SOCNET_LOG_FILES_USER"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_FILES_USER_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_FILES_USER_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_FILES_USER_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_files_operation_view_user", (CSocNetUser::IsFriendsAllowed() ? SONET_RELATIONS_TYPE_FRIENDS : SONET_RELATIONS_TYPE_ALL));
				$arFeatureTmp["operations"]["write_limited"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_files_operation_write_limited_user", SONET_RELATIONS_TYPE_NONE);
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_files_operation_write_user", SONET_RELATIONS_TYPE_NONE);
			}

			if (COption::GetOptionString("socialnetwork", "allow_files_group", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["files"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" => GetMessage("SOCNET_LOG_FILES_GROUP"),
					"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_FILES_GROUP_SETTINGS"),
					"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_FILES_GROUP_SETTINGS_1"),
					"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_FILES_GROUP_SETTINGS_2"),
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_files_operation_view_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["write_limited"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_files_operation_write_limited_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["write"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_files_operation_write_group", SONET_ROLES_MODERATOR);
			}

			\CSocNetAllowed::addAllowedFeature("files", $arFeatureTmp);
		}

		if (
			ModuleManager::isModuleInstalled('blog')
			&& (
				COption::GetOptionString("socialnetwork", "allow_blog_user", "Y") == "Y"
				|| COption::GetOptionString("socialnetwork", "allow_blog_group", "Y") == "Y"
			)
		)
		{
			$livefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(
					"view_post" => array(),
					"premoderate_post" => array(),
					"write_post" => array(),
					"moderate_post" => array(),
					"full_post" => array(),
					"view_comment" => array(),
					"premoderate_comment" => array(),
					"write_comment" => array(),
					"moderate_comment" => array(),
					"full_comment" => array(),
				),
				"minoperation" => array("view_comment", "view_post"),
				"subscribe_events" => array(
					"blog" =>  array(
						"ENTITIES" => array(),
						"OPERATION" => "",
						"NO_SET" => true,
						"REAL_EVENT_ID" => "blog_post",
						"FULL_SET" => array_unique(array_merge($livefeedProvider->getEventId(), array("blog", "blog_comment")))
					),
					"blog_post" => array(
						"ENTITIES" => array(),
						"OPERATION" => "view_post",
						"HIDDEN" => true,
						"CLASS_FORMAT"	=> "CSocNetLogTools",
						"METHOD_FORMAT" => "FormatEvent_Blog",
						"HAS_CB" => "Y",
						"COMMENT_EVENT" => array(
							"EVENT_ID"	=> "blog_comment",
							"OPERATION" => "view_comment",
							"OPERATION_ADD" => "premoderate_comment",
							"ADD_CALLBACK"	=> array("CSocNetLogTools", "AddComment_Blog"),
							"CLASS_FORMAT"	=> "CSocNetLogTools",
							"METHOD_FORMAT" => "FormatComment_Blog"
						)
					),
					"blog_comment" => array(
						"ENTITIES" => array(),
						"OPERATION" => "view_comment",
						"HIDDEN" => true,
						"CLASS_FORMAT"	=> "CSocNetLogTools",
						"METHOD_FORMAT"	=> "FormatEvent_Blog",
						"HAS_CB" => "Y"
					)
				)
			);

			if (COption::GetOptionString("socialnetwork", "allow_blog_user", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["blog"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" 			=> GetMessage("SOCNET_LOG_BLOG_USER"),
					"TITLE_SETTINGS"	=> GetMessage("SOCNET_LOG_BLOG_USER_SETTINGS"),
					"TITLE_SETTINGS_1"	=> GetMessage("SOCNET_LOG_BLOG_USER_SETTINGS_1"),
					"TITLE_SETTINGS_2"	=> GetMessage("SOCNET_LOG_BLOG_USER_SETTINGS_2"),
				);

				$arFeatureTmp["subscribe_events"]["blog_post"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_USER] = array(
					"TITLE" => GetMessage("SOCNET_LOG_BLOG_POST_USER")
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["view_post"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_blog_operation_view_post_user", SONET_RELATIONS_TYPE_ALL);
				$arFeatureTmp["operations"]["view_comment"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_blog_operation_view_comment_user", SONET_RELATIONS_TYPE_ALL);
				$arFeatureTmp["operations"]["premoderate_comment"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_blog_operation_premoderate_comment_user", SONET_RELATIONS_TYPE_AUTHORIZED);
				$arFeatureTmp["operations"]["write_comment"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_blog_operation_write_comment_user", SONET_RELATIONS_TYPE_AUTHORIZED);
			}

			if (COption::GetOptionString("socialnetwork", "allow_blog_group", "Y") == "Y")
			{
				$arFeatureTmp["subscribe_events"]["blog"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" 			=> GetMessage("SOCNET_LOG_BLOG_GROUP"),
					"TITLE_SETTINGS"	=> GetMessage("SOCNET_LOG_BLOG_GROUP_SETTINGS"),
					"TITLE_SETTINGS_1"	=> GetMessage("SOCNET_LOG_BLOG_GROUP_SETTINGS_1"),
					"TITLE_SETTINGS_2"	=> GetMessage("SOCNET_LOG_BLOG_GROUP_SETTINGS_2"),
				);

				$arFeatureTmp["subscribe_events"]["blog_post"]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP] = array(
					"TITLE" => GetMessage("SOCNET_LOG_BLOG_POST_GROUP")
				);

				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["view_post"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_view_post_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["premoderate_post"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_premoderate_post_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["write_post"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_write_post_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["moderate_post"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_moderate_post_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["full_post"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_full_post_group", SONET_ROLES_OWNER);
				$arFeatureTmp["operations"]["view_comment"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_view_comment_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["premoderate_comment"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_premoderate_comment_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["write_comment"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_write_comment_group", SONET_ROLES_USER);
				$arFeatureTmp["operations"]["moderate_comment"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_moderate_comment_group", SONET_ROLES_MODERATOR);
				$arFeatureTmp["operations"]["full_comment"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_blog_operation_full_comment_group", SONET_ROLES_MODERATOR);

				$arFeatureTmp["operations"]["write_post"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
				$arFeatureTmp["operations"]["premoderate_post"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
				$arFeatureTmp["operations"]["moderate_post"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
				$arFeatureTmp["operations"]["full_post"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
				$arFeatureTmp["operations"]["moderate_comment"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
				$arFeatureTmp["operations"]["full_comment"]["restricted"][SONET_ENTITY_GROUP] = array(SONET_ROLES_ALL);
			}
			$arFeatureTmp["subscribe_events"]["blog_post_important"] = $arFeatureTmp["subscribe_events"]["blog_post"];
			if (ModuleManager::isModuleInstalled('vote'))
			{
				$arFeatureTmp["subscribe_events"]["blog_post_vote"] = $arFeatureTmp["subscribe_events"]["blog_post"];
			}
			if (ModuleManager::isModuleInstalled('intranet'))
			{
				$arFeatureTmp["subscribe_events"]["blog_post_grat"] = $arFeatureTmp["subscribe_events"]["blog_post"];
			}
			\CSocNetAllowed::addAllowedFeature("blog", $arFeatureTmp);
		}

		if (
			ModuleManager::isModuleInstalled('search')
			&& (
				COption::GetOptionString("socialnetwork", "allow_search_user", "N") == "Y"
				|| COption::GetOptionString("socialnetwork", "allow_search_group", "Y") == "Y"
			)
		)
		{
			$arFeatureTmp = array(
				"allowed" => array(),
				"operations" => array(),
				"minoperation" => array(),
			);

			if (COption::GetOptionString("socialnetwork", "allow_search_user", "N") == "Y")
			{
				$arFeatureTmp["allowed"][] = SONET_ENTITY_USER;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_USER] = COption::GetOptionString("socialnetwork", "default_search_operation_view_user", SONET_RELATIONS_TYPE_ALL);
			}

			if (COption::GetOptionString("socialnetwork", "allow_search_group", "Y") == "Y")
			{
				$arFeatureTmp["allowed"][] = SONET_ENTITY_GROUP;
				$arFeatureTmp["operations"]["view"][SONET_ENTITY_GROUP] = COption::GetOptionString("socialnetwork", "default_search_operation_view_group", SONET_ROLES_USER);
			}

			CSocNetAllowed::addAllowedFeature("search", $arFeatureTmp);
		}

		if (
			ModuleManager::isModuleInstalled('sign')
			&& method_exists(\Bitrix\Sign\Config\Storage::class, 'isB2eAvailable')
			&& \Bitrix\Sign\Config\Storage::instance()->isB2eAvailable()
		)
		{
			$arFeatureTmp = [
				'allowed' => [],
				'operations' => [],
				'minoperation' => [],
			];
			$arFeatureTmp['allowed'][] = SONET_ENTITY_USER;
			$arFeatureTmp['operations']['view'][SONET_ENTITY_USER] = SONET_RELATIONS_TYPE_NONE;

			self::addAllowedFeature('sign', $arFeatureTmp);
		}

		// chat
		if (
			ModuleManager::isModuleInstalled('im')
			&& (COption::GetOptionString('socialnetwork', 'use_workgroup_chat', "Y") == "Y")
		)
		{
			$arFeatureTmp = array(
				"allowed" => array(SONET_ENTITY_GROUP),
				"operations" => array(),
				"minoperation" => array(),
			);

			CSocNetAllowed::addAllowedFeature("chat", $arFeatureTmp);
		}

		if (defined("BX_STARTED"))
		{
			self::addRestFeatures();
		}
		else
		{
			AddEventHandler("main", "OnBeforeProlog", array("CSocNetAllowed", "addRestFeatures"));
		}
	}

	public static function addRestFeatures()
	{
		global $USER;

		if (Loader::includeModule('rest'))
		{
			CSocNetAllowed::addAllowedFeature("marketplace", array(
				"allowed" => array(SONET_ENTITY_GROUP),
				"operations" => array(),
				"minoperation" => array(),
			));

			if (
				!isset($USER)
				|| !is_object($USER)
				|| !($USER instanceof \CUser)
			)
			{
				return;
			}

			$placementHandlerList = \Bitrix\Rest\PlacementTable::getHandlersList('SONET_GROUP_DETAIL_TAB');
			if(is_array($placementHandlerList))
			{
				foreach($placementHandlerList as $placementHandler)
				{
					CSocNetAllowed::addAllowedFeature("placement_".$placementHandler['ID'], array(
						"allowed" => array(SONET_ENTITY_GROUP),
						"operations" => array(),
						"minoperation" => array(),
						"title" => $placementHandler['TITLE']
					));
				}
			}
		}
	}

	public static function addAllowedFeature($strFeatureCode, $arFeature)
	{
		$strFeatureCode = trim($strFeatureCode);

		if (
			$strFeatureCode == ''
			|| !is_array($arFeature)
		)
		{
			return false;
		}

		if (
			!CBXFeatures::IsFeatureEnabled("Workgroups")
			&& array_key_exists("subscribe_events", $arFeature)
		)
		{
			foreach ($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
			{
				if (
					array_key_exists("ENTITIES", $arEventTmp)
					&& array_key_exists(SONET_SUBSCRIBE_ENTITY_GROUP, $arEventTmp["ENTITIES"])
				)
				{
					unset($arFeature["subscribe_events"][$event_id_tmp]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP]);
				}
			}
		}

		if (!array_key_exists($strFeatureCode, self::$arAllowedFeatures))
		{
			self::$arAllowedFeatures[$strFeatureCode] = $arFeature;
		}
		else
		{
			if (
				array_key_exists("operations", $arFeature)
				&& is_array($arFeature["operations"])
			)
			{
				if (!array_key_exists("operations", self::$arAllowedFeatures[$strFeatureCode]))
				{
					self::$arAllowedFeatures[$strFeatureCode]["operations"] = array();
				}

				foreach ($arFeature["operations"] as $strOpCode => $arOperation)
				{
					if (is_array($arOperation))
					{
						if (!array_key_exists($strOpCode, self::$arAllowedFeatures[$strFeatureCode]["operations"]))
						{
							self::$arAllowedFeatures[$strFeatureCode]["operations"][$strOpCode] = array();
						}

						foreach ($arOperation as $key => $value)
						{
							self::$arAllowedFeatures[$strFeatureCode]["operations"][$strOpCode][$key] = $value;
						}
					}
				}
			}

			if (
				array_key_exists("subscribe_events", $arFeature)
				&& is_array($arFeature["subscribe_events"])
			)
			{
				if (!array_key_exists("subscribe_events", self::$arAllowedFeatures[$strFeatureCode]))
				{
					self::$arAllowedFeatures[$strFeatureCode]["subscribe_events"] = array();
				}

				foreach ($arFeature["subscribe_events"] as $strEventCode => $arEvent)
				{
					if (is_array($arEvent))
					{
						self::$arAllowedFeatures[$strFeatureCode]["subscribe_events"][$strEventCode] = $arEvent;
					}
				}
			}
		}

		return true;
	}

	public static function updateAllowedFeature($strFeatureCode, $arFeature)
	{
		$strFeatureCode = trim($strFeatureCode);

		if (
			$strFeatureCode == ''
			|| !array_key_exists($strFeatureCode, self::$arAllowedFeatures)
			|| !is_array($arFeature)
		)
		{
			return false;
		}

		if (
			!CBXFeatures::IsFeatureEnabled("Workgroups")
			&& array_key_exists("subscribe_events", $arFeature)
		)
		{
			foreach ($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
			{
				if (
					array_key_exists("ENTITIES", $arEventTmp)
					&& array_key_exists(SONET_SUBSCRIBE_ENTITY_GROUP, $arEventTmp["ENTITIES"])
				)
				{
					unset($arFeature["subscribe_events"][$event_id_tmp]["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP]);
				}
			}
		}

		self::$arAllowedFeatures[$strFeatureCode] = $arFeature;

		return true;
	}

	public static function getAllowedFeatures()
	{
		static $init = false;
		if (!$init)
		{
			\CSocNetAllowed::addStandardFeatureList();
			$init = true;
		}

		self::runEvents();
		return self::$arAllowedFeatures;
	}

	/* --- log events --- */

	public static function runEventForAllowedLogEvent()
	{
		$newAllowedLogEvent = array();

		$events = GetModuleEvents("socialnetwork", "OnFillSocNetLogEvents");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array(&$newAllowedLogEvent));
		}

		foreach($newAllowedLogEvent as $strEventCode => $arLogEvent)
		{
			self::addAllowedLogEvent($strEventCode, $arLogEvent);
		}
	}

	public static function addAllowedLogEvent($strEventCode, $arLogEvent)
	{
		$strEventCode = trim($strEventCode);

		if (
			$strEventCode == ''
			|| array_key_exists($strEventCode, self::$arAllowedLogEvents)
			|| !is_array($arLogEvent)
		)
		{
			return false;
		}
		
		if (!CBXFeatures::IsFeatureEnabled("Workgroups"))
		{
			if ($strEventCode == "system_groups")
			{
				return false;
			}

			if (
				array_key_exists("ENTITIES", $arLogEvent)
				&& array_key_exists(SONET_SUBSCRIBE_ENTITY_GROUP, $arLogEvent["ENTITIES"])
			)
			{
				unset($arLogEvent["ENTITIES"][SONET_SUBSCRIBE_ENTITY_GROUP]);
			}

			if ($strEventCode == "system")
			{
				foreach($arLogEvent["FULL_SET"] as $i => $event_id_tmp)
				{
					if ($event_id_tmp == "system_groups")
					{
						unset($arLogEvent["FULL_SET"][$i]);
					}
				}
			}
		}

		if (!CBXFeatures::IsFeatureEnabled("Friends"))
		{
			if ($strEventCode == "system_friends")
			{
				return false;
			}

			if ($strEventCode == "system")
			{
				foreach($arLogEvent["FULL_SET"] as $i => $event_id_tmp)
				{
					if ($event_id_tmp == "system_friends")
					{
						unset($arLogEvent["FULL_SET"][$i]);
					}
				}
			}
		}

		self::$arAllowedLogEvents[$strEventCode] = $arLogEvent;

		return true;
	}
	
	public static function GetAllowedLogEvents()
	{
		self::getAllowedFeatures(); // to initialize standard features
		self::runEvents();
		return self::$arAllowedLogEvents;
	}

	public static function runEvents()
	{
		static $bAlreadyRun;

		if (!$bAlreadyRun)
		{
			self::runEventForAllowedEntityType();
			self::runEventForAllowedFeature();
			self::runEventForAllowedLogEvent();
			$bAlreadyRun = true;
		}
	}
}
