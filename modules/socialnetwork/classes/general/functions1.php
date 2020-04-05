<?
IncludeModuleLangFile(__FILE__);

class CSocNetTextParser
{
	var $smiles = array();
	var $allow_img_ext = "gif|jpg|jpeg|png";
	var $path_to_smile  = false;
	var $LAST_ERROR  = "";
	var $quote_error = 0;
	var $quote_open = 0;
	var $quote_closed = 0;
	var $MaxStringLen = 60;
	var $arFontSize = array(
		1 => 40, //"xx-small"
		2 => 60, //"x-small"
		3 => 80, //"small"
		4 => 100, //"medium"
		5 => 120, //"large"
		6 => 140, //"x-large"
		7 => 160); //"xx-large"

	function CSocNetTextParser($strLang = False, $pathToSmile = false)
	{
		global $DB, $CACHE_MANAGER;
		if ($strLang===False)
			$strLang = LANGUAGE_ID;
		$this->path_to_smile = $pathToSmile;


		$this->smiles = array();

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
				while ($tok)
				{
					$arSmiles[$res['LANG_LID']][] = array('TYPING' => stripslashes($tok),
										'IMAGE'  => stripslashes($res['IMAGE']),
										'DESCRIPTION'=>stripslashes($res['NAME']));
					$tok = strtok(" ");
				}
			}
			$CACHE_MANAGER->Set("b_sonet_smile", $arSmiles);
		}
		$this->smiles = $arSmiles[$strLang];
	}

	function convert($text, $bPreview = True, $arImages = array(), $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N")) //, "KEEP_AMP" => "N"
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;

		if ($allow["HTML"]!="Y")
		{
			if ($bPreview)
			{
				$text = preg_replace("#^(.+?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
				$text = preg_replace("#^(.+?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
			}
			else
			{
				$text = preg_replace("#<cut[\s]*(/>|>)#is", "[cut]", $text);
			}

			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#<a[^>]+href\s*=[\s\"']*((http|https|mailto|ftp)://[-_:A-Za-z0-9@]+(\.[-~,_/=:~A-Za-z0-9@&?=%]+)+)[\s\"']*[^>]*>(.+?)</a[^>]*>#is", "[url=\\1]\\4[/url]", $text);
				$text = preg_replace("'(^|\s)((http|https|news|ftp)://[-_:A-Za-z0-9@]+(\.[-~,_/=:A-Za-z0-9#@&?=%+]+)+)'is", "[url]\\2[/url]", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace("#<b(\s+[^>]*>|>)(.+?)</b\s*>#is", "[b]\\2[/b]", $text);
				$text = preg_replace("#<u(\s+[^>]*>|>)(.+?)</u\s*>#is", "[u]\\2[/u]", $text);
				$text = preg_replace("#<i(\s+[^>]*>|>)(.+?)</i\s*>#is", "[i]\\2[/i]", $text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#<img[^>]+src\s*=[\s\"']*((http|https|mailto|ftp)://[-_:A-Za-z0-9@]+(\.[-~,_/=:A-Za-z0-9@{}&?%]+)+)[\s\"']*[^>]*>#is", "[img]\\1[/img]", $text);
			}
			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#<(/?)code(.*?)>#is", "[\\1code]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace("#<font[^>]+size\s*=[\s\"']*([0-9]+)[\s\"']*[^>]*>(.+?)</font[^>]*>#is", "[size=\\1]\\2[/size]", $text);
				$text = preg_replace("/<font[^>]+color\s*=[\s\"']*(#[0-9]{6}|[a-zA-Z]+)[\s\"']*[^>]*>(.+?)<\/font[^>]*>/is", "[color=\\1]\\2[/color]", $text);
				$text = preg_replace("/<font[^>]+face\s*=[\s\"']*([a-zA-Z -]+)[\s\"']*[^>]*>(.+?)<\/font[^>]*>/is", "[font=\\1]\\2[/font]", $text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace("#<ul(\s+[^>]*>|>)(.+?)</ul(\s+[^>]*>|>)#is", "[list]\\2[/list]", $text);
				$text = preg_replace("#<li(\s+[^>]*>|>)#is", "[*]", $text);
			}

			if (strlen($text)>0)
			{
				$text = str_replace("<", "&lt;", $text);
				$text = str_replace(">", "&gt;", $text);
				$text = str_replace("\"", "&quot;", $text);
			}

			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#\[code(\s+[^\]]*\]|\])(.+?)\[/code(\s+[^\]]*\]|\])#ies", "\$this->convert_code_tag('\\2')", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#(\[quote(.*?)\](.*)\[/quote(.*?)\])#ies", "\$this->convert_quote_tag('\\1')", $text);
			}

			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->convert_image_tag('\\1')", $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\1', '')", $text);
				$text = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\2', '')", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace("#\[b\](.+?)\[/b\]#is", "<b>\\1</b>", $text);
				$text = preg_replace("#\[i\](.+?)\[/i\]#is", "<i>\\1</i>", $text);
				$text = preg_replace("#\[u\](.+?)\[/u\]#is", "<u>\\1</u>", $text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace("#\[list\](.+?)\[/list\]#is", "<ul>\\1</ul>", $text);
				$text = preg_replace("#\[\*\]#", "<li>", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				while (preg_match("#\[size\s*=\s*([^\]]+)\](.+?)\[/size\]#ies", $text))
				{
					$text = preg_replace("#\[size\s*=\s*([^\]]+)\](.+?)\[/size\]#ies", "\$this->convert_font_attr('size', '\\1', '\\2')", $text);
				}
				while (preg_match("#\[font\s*=\s*([^\]]+)\](.*?)\[/font\]#ies", $text))
				{
					$text = preg_replace("#\[font\s*=\s*([^\]]+)\](.*?)\[/font\]#ies", "\$this->convert_font_attr('font', '\\1', '\\2')", $text);
				}
				while (preg_match("#\[color\s*=\s*([^\]]+)\](.+?)\[/color\]#ies", $text))
				{
					$text = preg_replace("#\[color\s*=\s*([^\]]+)\](.+?)\[/color\]#ies", "\$this->convert_font_attr('color', '\\1', '\\2')", $text);
				}
			}

//			$text = preg_replace("#(^|\s)((http|https|news|ftp)://[-_:A-Za-z0-9@]+(\.[-_/=:A-Za-z0-9@&?=%]+)+)#ie", "\$this->convert_anchor_tag('\\2', '\\2', '\\1')", $text);

			$text = preg_replace("#\(c\)#i", "&copy;", $text);
			$text = preg_replace("#\(tm\)#i", "&#153;", $text);
			$text = preg_replace("#\(r\)#i", "&reg;", $text);

			$text = preg_replace("/\n/", "<br />", $text);

			if (!$bPreview)
			{
				$text = preg_replace("#\[cut[\s]*(/\]|\])#is", "<a name=\"cut\"></a>", $text);
			}

			if ($this->MaxStringLen>0)
			{
				$text = preg_replace("#(^|>)([^<]+)(<|$)#ies", "\$this->part_long_words('\\1', '\\2', '\\3')", $text);
			}
		}
		else
		{
			if ($allow["NL2BR"]=="Y")
			{
				$text = preg_replace("/\n/", "<br />", $text);
			}

			if ($bPreview)
			{
				$text = preg_replace("#^(.+?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
				$text = preg_replace("#^(.+?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
			}
			else
			{
				$text = preg_replace("#<cut[\s]*(/>|>)#is", "[cut]", $text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->convert_image_tag('\\1')", $text);
			}

			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#<(/?)code(.*?)>#is", "[\\1code]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}

			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#\[code(\s+[^\]]*\]|\])(.+?)\[/code(\s+[^\]]*\]|\])#ies", "\$this->convert_code_tag('\\2')", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#(\[quote(.*?)\](.*)\[/quote(.*?)\])#ies", "\$this->convert_quote_tag('\\1')", $text);
			}


			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\1', '')", $text);
				$text = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\2', '')", $text);
			}

			if (!$bPreview)
			{
				$text = preg_replace("#\[cut[\s]*(/\]|\])#is", "<a name=\"cut\"></a>", $text);
			}

		}

		if ($allow["SMILES"]=="Y")
		{
			if (count($this->smiles) > 0)
			{
				$arPattern = array();
				$arReplace = array();
				foreach ($this->smiles as $a_id => $row)
				{
					$code  = preg_quote(str_replace("'", "\\'", $row["TYPING"]), "/");
					$image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
					$description = preg_quote(htmlspecialcharsbx($row["DESCRIPTION"], ENT_QUOTES), "/");

					$arPattern[] = "/(?<=[^\w&])$code(?=.\W|\W.|\W$)/ei".BX_UTF_PCRE_MODIFIER;
					$arReplace[] = "\$this->convert_emoticon('$code', '$image', '$description')";
				}
				if (!empty($arPattern))
					$text = preg_replace($arPattern, $arReplace, ' '.$text.' ');

				//foreach ($this->smiles as $a_id => $row)
				//{
					//$code  = str_replace("'", "\'", $row["TYPING"]);
					//$image = $row["IMAGE"];
					//$description = htmlspecialcharsbx($row["DESCRIPTION"], ENT_QUOTES);
					//$code = preg_quote($code, "/");
					//$description = preg_quote($description, "/");
					//$text = preg_replace("!(?<=[^\w&])$code(?=.\W|\W.|\W$)!ei", "\$this->convert_emoticon('$code', '$image', '$description')", ' '.$text.' ');
				//}
			}
		}

		/*
		while (is_array($arImages) && list($IMAGE_ID, $FILE_ID)=each($arImages))
		{
			$f = CSocNetImage::GetByID($IMAGE_ID);
			$text = str_replace("[IMG ID=$IMAGE_ID]",CFile::ShowImage($FILE_ID,null,null,"title=\"".htmlspecialcharsbx($f['TITLE'])."\""), $text);
			$text = str_replace("[img id=$IMAGE_ID]",CFile::ShowImage($FILE_ID,null,null,"title=\"".htmlspecialcharsbx($f['TITLE'])."\""), $text);
		}
		*/

		return $text;
	}

	function killAllTags($text)
	{
		if (method_exists("CTextParser", "clearAllTags"))
			return CTextParser::clearAllTags($text);
		$text = strip_tags($text);
		$text = preg_replace("#<(/?)quote(.*?)>#is", "", $text);
		$text = preg_replace("#<(/?)code(.*?)>#is", "", $text);
		$text = preg_replace("#\[(/?)(b|u|i|list|code|quote|url|img)(.*?)\]#is", "", $text);
		$text = preg_replace("/^(\r|\n)+?(.*)$/", "\\2", $text);
		$text = preg_replace("/^<br>+?(.*)$/", "\\2", $text);
		return $text;
	}

	function convert4mail($text)
	{
		$text = Trim($text);
		if (strlen($text)<=0) return "";

		$text = preg_replace("#<(/?)code(.*?)>#is", "[\\1code]", $text);
		$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1qoute]", $text);

		$text = preg_replace("#\[code(.*?)\]#is", "\n>================== CODE ===================\n", $text);
		$text = preg_replace("#\[/code(.*?)\]#is", "\n>===========================================\n", $text);

		$text = preg_replace("/^(\r|\n)+?(.*)$/", "\\2", $text);
		$text = preg_replace("#\[b\](.+?)\[/b\]#is", "\\1", $text);
		$text = preg_replace("#\[i\](.+?)\[/i\]#is", "\\1", $text);
		$text = preg_replace("#\[u\](.+?)\[/u\]#is", "_\\1_", $text);
		$text = preg_replace("#\[color(.*?)\](.+?)\[/color\]#is", "\\2", $text);
		$text = preg_replace("#\[font(.*?)\](.+?)\[/font\]#is", "\\2", $text);

		$text = preg_replace("#\[quote(.*?)\]#is", "\n>================== QUOTE ==================\n", $text);
		$text = preg_replace("#\[/quote(.*?)\]#is", "\n>===========================================\n", $text);

		$text = preg_replace("#\[url\](\S+?)\[/url\]#is", "\\1", $text);
		$text = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#is", "\\2 ( \\1 )", $text);

		$text = preg_replace("#\[img\](.+?)\[/img\]#is", "(IMAGE: \\1)", $text);

		$text = preg_replace("#\[list\]#is", "\n", $text);
		$text = preg_replace("#\[/list\]#is", "\n", $text);

		return $text;
	}

	function convert_emoticon($code = "", $image = "", $description = "", $servername = "")
	{
		if (strlen($code)<=0 || strlen($image)<=0) return;
		$code = stripslashes($code);
		$description = stripslashes($description);
		$image = stripslashes($image);
		if ($this->path_to_smile !== false)
			return '<img src="'.$servername.$this->path_to_smile.$image.'" border="0" alt="'.$description.'" />';
		return '<img src="'.$servername.'/bitrix/images/socialnetwork/smile/'.$image.'" border="0" alt="'.$description.'" />';

	}

	function convert_code_tag($text = "")
	{
		if (strlen($text)<=0) return;

		$text = stripslashes($text);
		$text = str_replace(array("<", ">"), array("&lt;", "&gt;"), $text);
		$text = str_replace("&nbsp;", "&amp;nbsp;", $text);

		$text = preg_replace("# {2}#", "&nbsp;&nbsp;", $text);
		$text = preg_replace("#\t#", "&nbsp;&nbsp;&nbsp;", $text);
		$text = preg_replace("#^(.*?)$#", "&nbsp;&nbsp;&nbsp;\\1", $text);

		return "<br /><small><b>".GetMessage("SONET_CODE")."</b></small><table class='sonetcode'><tr><td>".$text."</td></tr></table>";
	}

	function convert_code_tag_rss($text = "")
	{
		if (strlen($text)<=0) return;

		$text = stripslashes($text);
		$text = str_replace(array("<", ">"), array("&lt;", "&gt;"), $text);
		$text = str_replace("&nbsp;", "&amp;nbsp;", $text);

		$text = preg_replace("# {2}#", "&nbsp;&nbsp;", $text);
		$text = preg_replace("#\t#", "&nbsp;&nbsp;&nbsp;", $text);
		$text = preg_replace("#^(.*?)$#", "&nbsp;&nbsp;&nbsp;\\1", $text);

		return "\n====code====\n".$text."\n===========\n";
	}

	function convert_quote_tag($text = "")
	{
		if (strlen($text)<=0) return;
		$txt = $text;

		$txt = preg_replace("#\[quote\]#ie", "\$this->convert_open_quote_tag()", $txt);
		$txt = preg_replace("#\[/quote\]#ie", "\$this->convert_close_quote_tag()", $txt);
		$txt = preg_replace("/\n/", "<br />", $txt);

		if (($this->quote_open==$this->quote_closed) && ($this->quote_error==0))
		{
			return $txt;
		}
		else
		{
			return $text;
		}
	}

	function convert_quote_tag_rss($text = "")
	{
		if (strlen($text)<=0) return;
		$txt = $text;

		$txt = preg_replace("#\[quote\]#ie", "\$this->convert_open_quote_tag_rss()", $txt);
		$txt = preg_replace("#\[/quote\]#ie", "\$this->convert_close_quote_tag_rss()", $txt);
		$txt = preg_replace("/\n/", "<br />", $txt);

		if (($this->quote_open==$this->quote_closed) && ($this->quote_error==0))
		{
			return $txt;
		}
		else
		{
			return $text;
		}
	}

	function convert_open_quote_tag()
	{
		$this->quote_open++;
		return '<br /><div class="socnet-quote"><span class="socnet-quote-title">'.GetMessage("SONET_QUOTE").'<br /></span>';
	}

	function convert_open_3_tag_rss()
	{
		$this->quote_open++;
		return "\n====quote====\n";
	}

	function convert_close_quote_tag()
	{
		if ($this->quote_open == 0)
		{
			$this->quote_error++;
			return;
		}
		$this->quote_closed++;
		return '</div><br style="clear:both" />';
	}

	function convert_close_quote_tag_rss()
	{
		if ($this->quote_open == 0)
		{
			$this->quote_error++;
			return;
		}
		$this->quote_closed++;
		return "\n===========\n";
	}

	function convert_image_tag($url = "")
	{
		if (strlen($url)<=0) return;
		$url = trim($url);

		$extension = preg_replace("/^.*\.(\S+)$/", "\\1", $url);
		$extension = strtolower($extension);
		$extension = preg_quote($extension, "/");

		$bErrorIMG = False;
		if (preg_match("/[?&;]/", $url))
			$bErrorIMG = True;

		if (!$bErrorIMG && !preg_match("/$extension(\||\$)/", $this->allow_img_ext))
			$bErrorIMG = True;

		if (!$bErrorIMG && !preg_match("/^(http|https|ftp|\/)/i", $url))
			$bErrorIMG = True;

		if ($bErrorIMG)
		{
			return "[img]".$url."[/img]";
		}

		return "<img src='$url' border='0'>";
	}

	function convert_font_attr($attr, $value = "", $text = "")
	{
		if (strlen($text)<=0) return "";
		if (strlen($value)<=0) return $text;

		if ($attr == "size")
		{
			$count = count($this->arFontSize);
			if ($count <= 0)
				return $text;
			$value = intVal($value >= $count ? ($count) : $value);
			return "<span style='font-size:".$this->arFontSize[$value]."%;'>".$text."</span>";
		}
		else if ($attr == 'color')
		{
			$value = preg_replace("/[^\w#]/", "" , $value);
			return '<span style="color:'.$value.'">'.$text.'</span>';
		}
		else if ($attr == 'font')
		{
			$value = preg_replace("/[^\w]/", "" , $value);
			return '<span style="font-family:'.$value.'">'.$text.'</span>';
		}
	}

	function part_long_words($str1, $str2, $str3)
	{
		$str2 = str_replace(chr(1), "", $str2);
		$str2 = str_replace(chr(2), "", $str2);
		$str2 = str_replace(chr(3), "", $str2);
		$str2 = str_replace(chr(4), "", $str2);
		$str2 = str_replace(chr(5), "", $str2);
		$str2 = str_replace("&amp;", chr(5), $str2);
		$str2 = str_replace("&lt;", "<", $str2);
		$str2 = str_replace("&gt;", ">", $str2);
		$str2 = str_replace("&quot;", "\"", $str2);
		$str2 = str_replace("&nbsp;", chr(1), $str2);
		$str2 = str_replace("&copy;", chr(2), $str2);
		$str2 = str_replace("&reg;", chr(3), $str2);
		$str2 = str_replace("&trade;", chr(4), $str2);

		$str2 = preg_replace("/[^ \n\r\t\x01]{".$this->MaxStringLen."}/","\\1<WBR>", $str2);

		$str2 = str_replace(chr(5), "&amp;", $str2);
		$str2 = str_replace("<", "&lt;", $str2);
		$str2 = str_replace(">", "&gt;", $str2);
		$str2 = str_replace("\"", "&quot;", $str2);
		$str2 = str_replace(chr(1), "&nbsp;", $str2);
		$str2 = str_replace(chr(2), "&copy;", $str2);
		$str2 = str_replace(chr(3), "&reg;", $str2);
		$str2 = str_replace(chr(4), "&trade;", $str2);
		$str2 = str_replace("&lt;WBR&gt;", "<WBR>", $str2);

		return $str1.$str2.$str3;
	}

	function convert_anchor_tag($url, $text, $pref="")
	{
		$bCutUrl = True;

		$end = "";
		if (preg_match("/([\.,\?]|&#33;)$/", $url, $match))
		{
			$end = $match[1];
			$url = preg_replace("/([\.,\?]|&#33;)$/", "", $url);
			$text = preg_replace("/([\.,\?]|&#33;)$/", "", $text);
		}

		if (preg_match("/\[\/(quote|code)/i", $url)) return $url;
		$url = str_replace("&amp;", "&" , $url);
		$url = preg_replace("/javascript:/i", "java script&#58; ", $url);
		if (!preg_match("#^(http|news|https|ftp|aim)://#", $url))
		{
			$url = 'http://'.$url;
		}

		if (!preg_match("/^(http|https|news|ftp|aim):\/\/[-_:A-Za-z0-9@.~,_\/=:#&?%+]+$/i", $url))
			return $pref.$text." (".$url.")".$end;

		if (preg_match("/^<img\s+src/i", $text)) $bCutUrl = False;
		$text = str_replace("&amp;", "&", $text);
		$text = preg_replace("/javascript:/i", "javascript&#58; ", $text);
		if ($bCutUrl && strlen($text) < 55) $bCutUrl = False;
		if ($bCutUrl && !preg_match("/^(http|ftp|https|news):\/\//i", $text)) $bCutUrl = False;

		if ($bCutUrl)
		{
			$stripped = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\2", $text);
			$uri_type = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\1", $text);

			$text = $uri_type.'://'.substr($stripped, 0, 30).'...'.substr($stripped, -10);
		}

		return $pref."<a href='".$url."' target='_blank'>".$text."</a>".$end;
	}

	function convert_to_rss($text, $arImages = Array(), $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N")) //, "KEEP_AMP" => "N"
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		if ($allow["HTML"]!="Y")
		{
			$text = preg_replace("#^(.+?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
			$text = preg_replace("#^(.+?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);

			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#<img[^>]+src\s*=[\s\"']*((http|https|mailto|ftp)://[-_:A-Za-z0-9@]+(\.[-~,_/=:A-Za-z0-9@{}&?%]+)+)[\s\"']*[^>]*>#is", "[img]\\1[/img]", $text);
			}
			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#<(/?)code(.*?)>#is", "[\\1code]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1qoute]", $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#<a[^>]+href\s*=[\s\"']*((http|https|mailto|ftp)://[-_:A-Za-z0-9@]+(\.[-_/=:A-Za-z0-9@&?=%]+)+)[\s\"']*[^>]*>(.+?)</a[^>]*>#is", "[url=\\1]\\4[/url]", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace("#<b(\s+[^>]*>|>)(.+?)</b\s*>#is", "[b]\\2[/b]", $text);
				$text = preg_replace("#<u(\s+[^>]*>|>)(.+?)</u\s*>#is", "[u]\\2[/u]", $text);
				$text = preg_replace("#<i(\s+[^>]*>|>)(.+?)</i\s*>#is", "[i]\\2[/i]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace("#<font[^>]+size\s*=[\s\"']*([0-9]+)[\s\"']*[^>]*>(.+?)</font[^>]*>#is", "[size=\\1]\\2[/size]", $text);
				$text = preg_replace("/<font[^>]+color\s*=[\s\"']*(#[0-9]{6}|[a-zA-Z]+)[\s\"']*[^>]*>(.+?)<\/font[^>]*>/is", "[color=\\1]\\2[/color]", $text);
				$text = preg_replace("/<font[^>]+face\s*=[\s\"']*([a-zA-Z -]+)[\s\"']*[^>]*>(.+?)<\/font[^>]*>/is", "[font=\\1]\\2[/font]", $text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace("#<ul(\s+[^>]*>|>)(.+?)</ul(\s+[^>]*>|>)#is", "[list]\\2[/list]", $text);
				$text = preg_replace("#<li(\s+[^>]*>|>)#is", "[*]", $text);
			}

			$text = preg_replace("'(^|\s)((http|https|news|ftp)://[-_:A-Za-z0-9@]+(\.[-_/=:A-Za-z0-9#@&?=%+]+)+)'is", "\\1[url]\\2[/url]", $text);

//			$text = htmlspecialcharsEx($text);
			if (strlen($text)>0)
			{
				$text = str_replace("<", "&lt;", $text);
				$text = str_replace(">", "&gt;", $text);
				$text = str_replace("\"", "&quot;", $text);
			}

			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#\[code(\s+[^\]]*\]|\])(.+?)\[/code(\s+[^\]]*\]|\])#ies", "\$this->convert_code_tag_rss('\\2')", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#(\[quote(.*?)\](.*)\[/quote(.*?)\])#ies", "\$this->convert_quote_tag_rss('\\1')", $text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#\[img\](.+?)\[/img\]#ies", "\$this->convert_image_tag('\\1')", $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\1', '')", $text);
				$text = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\2', '')", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace("#\[b\](.+?)\[/b\]#is", "<b>\\1</b>", $text);
				$text = preg_replace("#\[i\](.+?)\[/i\]#is", "<i>\\1</i>", $text);
				$text = preg_replace("#\[u\](.+?)\[/u\]#is", "<u>\\1</u>", $text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace("#\[list\](.+?)\[/list\]#is", "<ul>\\1</ul>", $text);
				$text = preg_replace("#\[\*\]#", "<li>", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				while (preg_match("#\[size\s*=\s*([^\]]+)\](.+?)\[/size\]#ies", $text))
				{
					$text = preg_replace("#\[size\s*=\s*([^\]]+)\](.+?)\[/size\]#ies", "\$this->convert_font_attr('size', '\\1', '\\2')", $text);
				}
				while (preg_match("#\[font\s*=\s*([^\]]+)\](.*?)\[/font\]#ies", $text))
				{
					$text = preg_replace("#\[font\s*=\s*([^\]]+)\](.*?)\[/font\]#ies", "\$this->convert_font_attr('font', '\\1', '\\2')", $text);
				}
				while (preg_match("#\[color\s*=\s*([^\]]+)\](.+?)\[/color\]#ies", $text))
				{
					$text = preg_replace("#\[color\s*=\s*([^\]]+)\](.+?)\[/color\]#ies", "\$this->convert_font_attr('color', '\\1', '\\2')", $text);
				}
			}

//			$text = preg_replace("#(^|\s)((http|https|news|ftp)://[-_:A-Za-z0-9@]+(\.[-_/=:A-Za-z0-9@&?=%]+)+)#ie", "\$this->convert_anchor_tag('\\2', '\\2', '\\1')", $text);

			$text = preg_replace("#\(c\)#i", "&copy;", $text);
			$text = preg_replace("#\(tm\)#i", "&#153;", $text);
			$text = preg_replace("#\(r\)#i", "&reg;", $text);

			$text = str_replace("\n", "<br />", $text);


			if ($this->MaxStringLen>0)
			{
				$text = preg_replace("#(^|>)([^<]+)(<|$)#ies", "\$this->part_long_words('\\1', '\\2', '\\3')", $text);
			}
		}
		else
		{
			if ($allow["NL2BR"]=="Y")
			{
				$text = str_replace("\n", "<br />", $text);
			}
			if ($bPreview)
			{
				$text = preg_replace("#^(.+?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
				$text = preg_replace("#^(.+?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
			}
			else
			{
				$text = preg_replace("#<cut[\s]*(/>|>)#is", "[cut]", $text);
			}
			/*
			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#<(/?)code(.*?)>#is", "[\\1code]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}

			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace("#\[code(\s+[^\]]*\]|\])(.+?)\[/code(\s+[^\]]*\]|\])#ies", "\$this->convert_code_tag('\\2')", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#(\[quote(.*?)\](.*)\[/quote(.*?)\])#ies", "\$this->convert_quote_tag('\\1')", $text);
			}
			*/
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->convert_image_tag('\\1')", $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\1', '')", $text);
				$text = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->convert_anchor_tag('\\1', '\\2', '')", $text);
			}

			if (!$bPreview)
			{
				$text = preg_replace("#\[cut[\s]*(/\]|\])#is", "<a name=\"cut\"></a>", $text);
			}
		}

		$dbSite = CSite::GetByID(SITE_ID);
		$arSite = $dbSite->Fetch();
		$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
		if (strlen($serverName) <=0)
		{
			if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
				$serverName = SITE_SERVER_NAME;
			else
				$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
		}

		if ($allow["SMILES"]=="Y")
		{
			if (count($this->smiles) > 0)
			{
				$siteUrl = "http://".$serverName;

				foreach ($this->smiles as $a_id => $row)
				{
					$code  = str_replace("'", "\'", $row["TYPING"]);
					$image = $row["IMAGE"];
					$description = htmlspecialcharsbx($row["DESCRIPTION"], ENT_QUOTES);
					$code = preg_quote($code, "/");
					$description = preg_quote($description, "/");
					$text = preg_replace("!(?<=[^\w&])$code(?=.\W|\W.|\W$)!ei", "\$this->convert_emoticon('$code', '$image', '$description', '$siteUrl')", ' '.$text.' ');
				}
			}
		}

		return $text;
	}


}

class CSocNetTools
{
	function InitImage($imageID, $imageSize, $defaultImage, $defaultImageSize, $imageUrl, $showImageUrl, $urlParams=false)
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

	function htmlspecialcharsExArray($array)
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

	function ResizeImage($aFile, $sizeX, $sizeY)
	{
		$result = CFile::ResizeImageGet($aFile, array("width" => $sizeX, "height" => $sizeY));
		if(is_array($result))
			return $result["src"];
		else
			return false;
	}

	function GetDateTimeFormat()
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

	function Birthday($datetime, $gender, $showYear = "N")
	{
		if (StrLen($datetime) <= 0)
			return false;

		$arDateTmp = ParseDateTime($datetime, CSite::GetDateFormat('SHORT'));

		$day = IntVal($arDateTmp["DD"]);
		$month = IntVal($arDateTmp["MM"]);
		$year = IntVal($arDateTmp["YYYY"]);

		$val = $day.' '.ToLower(GetMessage('MONTH_'.$month.'_S'));
		if (($showYear == 'Y') || ($showYear == 'M' && $gender == 'M'))
			$val .= ' '.$year;

		return array(
			"DATE" => $val,
			"MONTH" => Str_Pad(IntVal($arDateTmp["MM"]), 2, "0", STR_PAD_LEFT),
			"DAY" => Str_Pad(IntVal($arDateTmp["DD"]), 2, "0", STR_PAD_LEFT)
		);
	}
}
?>
