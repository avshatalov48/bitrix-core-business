<?
	IncludeModuleLangFile(__FILE__);
	/**
	* CBXSanitizer
	* Class to cut all tags and attributies from html not contained in white list
	*
	* Example to use:
	* <code>
	* $Sanitizer = new CBXSanitizer;
	*
	* $Sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);
	* or
	* $Sanitizer->AddTags( array (
	* 								'a' = > array('href','id','style','alt'...),
	* 								'br' => array(),
	* 								.... ));
	*
	* $Sanitizer->SanitizeHtml($html);
	* </code>
	*
	*/
	class CBXSanitizer
	{
		/**
		 * Security levels
		 */
		const SECURE_LEVEL_CUSTOM	= 0;
		const SECURE_LEVEL_HIGH		= 1;
		const SECURE_LEVEL_MIDDLE	= 2;
		const SECURE_LEVEL_LOW		= 3;

		const TABLE_TOP 	= 0;
		const TABLE_CAPT 	= 1;
		const TABLE_GROUP 	= 2;
		const TABLE_ROWS 	= 3;
		const TABLE_COLS 	= 4;

		const ACTION_DEL = 'del';
		const ACTION_ADD = 'add';
		const ACTION_DEL_WITH_CONTENT = 'del_with_content';

		/**
		 * @deprecated For compability only will be erased next versions
		 * @var mixed
		 */
		protected static $arOldTags = array();

		protected $arHtmlTags = array();
		protected $bHtmlSpecChars = true;
		protected $bDelSanitizedTags = true;
		protected $bDoubleEncode = true;
		protected $secLevel = self::SECURE_LEVEL_HIGH;
		protected $additionalAttrs = array();
		protected $arNoClose = array(
			'br','hr','img','area','base',
			'basefont','col','frame','input',
			'isindex','link','meta','param'
		);
		protected $localAlph;

		protected $arTableTags = array(
			'table' 	=> self::TABLE_TOP,
			'caption'	=> self::TABLE_CAPT,
			'thead' 	=> self::TABLE_GROUP,
			'tfoot' 	=> self::TABLE_GROUP,
			'tbody' 	=> self::TABLE_GROUP,
			'tr'		=> self::TABLE_ROWS,
			'th'		=> self::TABLE_COLS,
			'td'		=> self::TABLE_COLS
		);

		/**
		 * Tags witch will be cut with their content
		 * @var array
		 */
		protected $delTagsWithContent = ['script', 'style'];

		/**
		 * CBXSanitizer constructor.
		 */
		public function __construct()
		{
			if(SITE_CHARSET == "UTF-8")
			{
				$this->localAlph="\p{L}".GetMessage("SNT_SYMB_NONE_LETTERS");
			}
			elseif(LANGUAGE_ID != "en")
			{
				$this->localAlph=GetMessage("SNT_SYMB");
			}
			else
			{
				$this->localAlph="";
			}

			$this->localAlph .= '\\x80-\\xFF';
		}

		/**
		 * Allow additional attributes in html.
		 * @param array $attrs Additional attrs
		 * Example:
			$sanitizer->allowAttributes(array(
				'aria-label' => array(
						'tag' => function($tag)
						{
							return ($tag == 'div');
						},
						'content' => function($value)
						{
							return !preg_match("#[^\\s\\w\\-\\#\\.;]#i" . BX_UTF_PCRE_MODIFIER, $value);
						}
					)
			));
		 * @return void
		 */
		public function allowAttributes(array $attrs)
		{
			foreach ($attrs as $code => $item)
			{
				if (
					isset($item['tag']) && is_callable($item['tag']) &&
					isset($item['content']) && is_callable($item['content'])
				)
				{
					$this->additionalAttrs[$code] = $item;
				}
			}
		}

		/**
		 * Adds HTML tags and attributes to white list
		 * @param mixed $arTags array('tagName1' = > array('attribute1','attribute2',...), 'tagName2' => ........)
		 * @return int count of added tags
		 */
		public function AddTags($arTags)
		{
			if(!is_array($arTags))
				return false;

			$counter = 0;
			$this->secLevel = self::SECURE_LEVEL_CUSTOM;

			foreach($arTags as $tagName => $arAttrs)
			{
				$tagName = mb_strtolower($tagName);
				$arAttrs = array_change_key_case($arAttrs, CASE_LOWER);
				$this->arHtmlTags[$tagName] = $arAttrs;
				$counter++;
			}

			return $counter;
		}

		/**
		 * @see AddTags()
		 */
		public function UpdateTags($arTags)
		{
			return $this->AddTags($arTags);
		}

		/**
		 * Deletes tags from white list
		 * @param mixed $arTagNames array('tagName1','tagname2',...)
		 * @return int count of deleted tags
		 */
		public function DelTags($arTagNames)
		{
			if(!is_array($arTagNames))
				return false;

			$this->secLevel = self::SECURE_LEVEL_CUSTOM;
			$arTmp = array();
			$counter = 0;

			foreach ($this->arHtmlTags as $tagName => $arAttrs)
				foreach ($arTagNames as $delTagName)
					if(mb_strtolower($delTagName) != $tagName)
						$arTmp[$tagName] = $arAttrs;
					else
						$counter++;

			$this->arHtmlTags = $arTmp;
			return $counter;
		}

		/**
		 * @param array $arDeleteAttrs
		 */
		public function DeleteAttributes(array $arDeleteAttrs)
		{
			$this->secLevel = self::SECURE_LEVEL_CUSTOM;
			$arResultTags = array();
			foreach ($this->arHtmlTags as $tagName => $arAttrs)
			{
				$arResultTags[$tagName] = array_diff($arAttrs, $arDeleteAttrs);
			}
			$this->arHtmlTags = $arResultTags;
		}

		/**
		 * Deletes all tags from white list
		 */
		public function DelAllTags()
		{
			$this->secLevel = self::SECURE_LEVEL_CUSTOM;
			$this->arHtmlTags = array();
		}

		/**
		 *  If is turned off Sanitizer will not encode existing html entities,
		 *  in text blocks.
		 *  The default is to convert everything.
		 *	http://php.net/manual/ru/function.htmlspecialchars.php (double_encode)
		 * @param bool $bApply true|false
		 */
		public function ApplyDoubleEncode($bApply=true)
		{
			if($bApply)
				$this->bDoubleEncode = true;
			else
				$this->bDoubleEncode = false;
		}

		/**
		 * Apply or not function htmlspecialchars to filtered tags and text
		 * !WARNING! if DeleteSanitizedTags = false and ApplyHtmlSpecChars = false
		 * html will not be sanitized!
		 * @param bool $bApply true|false
		 * @deprecated
		 */
		public function ApplyHtmlSpecChars($bApply=true)
		{
			if($bApply)
			{
				$this->bHtmlSpecChars = true;
			}
			else
			{
				$this->bHtmlSpecChars = false;
				trigger_error('It is strongly not recommended to use \CBXSanitizer::ApplyHtmlSpecChars(false)', E_USER_WARNING);
			}
		}

		/**
		 * Delete or not filtered tags
		 * !WARNING! if DeleteSanitizedTags = false and ApplyHtmlSpecChars = false
		 * html will not be sanitized!
		 * @param bool $bApply true|false
		 */
		public function DeleteSanitizedTags($bApply=true)
		{
			if($bApply)
				$this->bDelSanitizedTags = true;
			else
				$this->bDelSanitizedTags = false;
		}

		/**
		 * Sets security level from predefined
		 * @param int $secLevel { 	CBXSanitizer::SECURE_LEVEL_HIGH
		 *							| CBXSanitizer::SECURE_LEVEL_MIDDLE
		 *							| CBXSanitizer::SECURE_LEVEL_LOW }
		 */
		public function SetLevel($secLevel)
		{
			if($secLevel!=self::SECURE_LEVEL_HIGH && $secLevel!=self::SECURE_LEVEL_MIDDLE && $secLevel!=self::SECURE_LEVEL_LOW)
				$secLevel=self::SECURE_LEVEL_HIGH;

			switch ($secLevel)
			{
				case self::SECURE_LEVEL_HIGH:
					$arTags = array(
						'b'		=> array(),
						'br'		=> array(),
						'big'		=> array(),
						'blockquote'	=> array(),
						'code'		=> array(),
						'del'		=> array(),
						'dt'		=> array(),
						'dd'		=> array(),
						'font'		=> array(),
						'h1'		=> array(),
						'h2'		=> array(),
						'h3'		=> array(),
						'h4'		=> array(),
						'h5'		=> array(),
						'h6'		=> array(),
						'hr'		=> array(),
						'i'		=> array(),
						'ins'		=> array(),
						'li'		=> array(),
						'ol'		=> array(),
						'p'		=> array(),
						'small'		=> array(),
						's'		=> array(),
						'sub'		=> array(),
						'sup'		=> array(),
						'strong'	=> array(),
						'pre'		=> array(),
						'u'		=> array(),
						'ul'		=> array()
					);

					break;

				case self::SECURE_LEVEL_MIDDLE:
					$arTags = array(
						'a'		=> array('href', 'title','name','alt'),
						'b'		=> array(),
						'br'		=> array(),
						'big'		=> array(),
						'blockquote'	=> array('title'),
						'code'		=> array(),
						'caption'	=> array(),
						'del'		=> array('title'),
						'dt'		=> array(),
						'dd'		=> array(),
						'font'		=> array('color','size'),
						'color'		=> array(),
						'h1'		=> array(),
						'h2'		=> array(),
						'h3'		=> array(),
						'h4'		=> array(),
						'h5'		=> array(),
						'h6'		=> array(),
						'hr'		=> array(),
						'i'		=> array(),
						'img'		=> array('src','alt','height','width','title'),
						'ins'		=> array('title'),
						'li'		=> array(),
						'ol'		=> array(),
						'p'		=> array(),
						'pre'		=> array(),
						's'		=> array(),
						'small'		=> array(),
						'strong'	=> array(),
						'sub'		=> array(),
						'sup'		=> array(),
						'table'		=> array('border','width'),
						'tbody'		=> array('align','valign'),
						'td'		=> array('width','height','align','valign'),
						'tfoot'		=> array('align','valign'),
						'th'		=> array('width','height'),
						'thead'		=> array('align','valign'),
						'tr'		=> array('align','valign'),
						'u'		=> array(),
						'ul'		=> array()
					);
					break;

				case self::SECURE_LEVEL_LOW:
					$arTags = array(
						'a'		=> array('href', 'title','name','style','id','class','shape','coords','alt','target'),
						'b'		=> array('style','id','class'),
						'br'		=> array('style','id','class'),
						'big'		=> array('style','id','class'),
						'blockquote'	=> array('title','style','id','class'),
						'caption'	=> array('style','id','class'),
						'code'		=> array('style','id','class'),
						'del'		=> array('title','style','id','class'),
						'div'		=> array('title','style','id','class','align'),
						'dt'		=> array('style','id','class'),
						'dd'		=> array('style','id','class'),
						'font'		=> array('color','size','face','style','id','class'),
						'h1'		=> array('style','id','class','align'),
						'h2'		=> array('style','id','class','align'),
						'h3'		=> array('style','id','class','align'),
						'h4'		=> array('style','id','class','align'),
						'h5'		=> array('style','id','class','align'),
						'h6'		=> array('style','id','class','align'),
						'hr'		=> array('style','id','class'),
						'i'		=> array('style','id','class'),
						'img'		=> array('style','id','class','src','alt','height','width','title','align'),
						'ins'		=> array('title','style','id','class'),
						'li'		=> array('style','id','class'),
						'map'		=> array('shape','coords','href','alt','title','style','id','class','name'),
						'ol'		=> array('style','id','class'),
						'p'		=> array('style','id','class','align'),
						'pre'		=> array('style','id','class'),
						's'		=> array('style','id','class'),
						'small'		=> array('style','id','class'),
						'strong'	=> array('style','id','class'),
						'span'		=> array('title','style','id','class','align'),
						'sub'		=> array('style','id','class'),
						'sup'		=> array('style','id','class'),
						'table'		=> array('border','width','style','id','class','cellspacing','cellpadding'),
						'tbody'		=> array('align','valign','style','id','class'),
						'td'		=> array('width','height','style','id','class','align','valign','colspan','rowspan'),
						'tfoot'		=> array('align','valign','style','id','class','align','valign'),
						'th'		=> array('width','height','style','id','class','colspan','rowspan'),
						'thead'		=> array('align','valign','style','id','class'),
						'tr'		=> array('align','valign','style','id','class'),
						'u'		=> array('style','id','class'),
						'ul'		=> array('style','id','class')
					);
					break;
				default:
					$arTags = array();
					break;
			}

			$this->DelAllTags();
			$this->AddTags($arTags);
			$this->secLevel = $secLevel;
		}

		// Checks if tag's attributes are in white list ($this->arHtmlTags)
		protected function IsValidAttr(&$arAttr)
		{
			if (!isset($arAttr[1]) || !isset($arAttr[3]))
			{
				return false;
			}

			$attr = mb_strtolower($arAttr[1]);
			$attrValue = $this->Decode($arAttr[3]);

			switch ($attr)
			{
				case 'src':
				case 'href':
				case 'data-url':
					if(!preg_match("#^(http://|https://|ftp://|file://|mailto:|callto:|skype:|tel:|sms:|\\#|/)#i".BX_UTF_PCRE_MODIFIER, $attrValue))
					{
						$arAttr[3] = 'http://' . $arAttr[3];
					}
					$valid = (!preg_match("#javascript:|data:|[^\\w".$this->localAlph."a-zA-Z:/\\.=@;,!~\\*\\&\\#\\)(%\\s\\+\$\\?\\-\\[\\]]#i".BX_UTF_PCRE_MODIFIER, $attrValue))
							? true : false;
					break;

				case 'height':
				case 'width':
				case 'cellpadding':
				case 'cellspacing':
					$valid = !preg_match("#^[^0-9\\-]+(px|%|\\*)*#i".BX_UTF_PCRE_MODIFIER, $attrValue)
							? true : false;
					break;

				case 'title':
				case 'alt':
					$valid = !preg_match("#[^\\w".$this->localAlph."\\.\\?!,:;\\s\\-]#i".BX_UTF_PCRE_MODIFIER, $attrValue)
							? true : false;
					break;

				case 'style':
					$attrValue = str_replace('&quot;', '',  $attrValue);
					$valid = !preg_match("#(behavior|expression|javascript)#i".BX_UTF_PCRE_MODIFIER, $attrValue) && !preg_match("#[^\\/\\w\\s)(!%,:\\.;\\-\\#\\']#i".BX_UTF_PCRE_MODIFIER, $attrValue)
							? true : false;
					break;

				case 'coords':
					$valid = !preg_match("#[^0-9\\s,\\-]#i".BX_UTF_PCRE_MODIFIER, $attrValue)
							? true : false;
					break;

				default:
					if (array_key_exists($attr, $this->additionalAttrs))
					{
						$valid = true === call_user_func_array(
							$this->additionalAttrs[$attr]['content'],
							array($attrValue)
						);
					}
					else
					{
						$valid = !preg_match("#[^\\s\\w" . $this->localAlph . "\\-\\#\\.\/;]#i" . BX_UTF_PCRE_MODIFIER, $attrValue)
								? true : false;
					}
					break;
			}

			return $valid;
		}

		protected function encodeAttributeValue(array $attr)
		{
			if (!$this->bHtmlSpecChars)
			{
				return $attr[3];
			}

			$result = $attr[3];
			$flags = ENT_QUOTES;

			if ($attr[1] === 'style')
			{
				$flags = ENT_COMPAT;
			}
			elseif ($attr[1] === 'href')
			{
				$result = str_replace('&', '##AMP##', $result);
			}

			$result = htmlspecialchars($result, $flags, LANG_CHARSET, $this->bDoubleEncode);

			if ($attr[1] === 'href')
			{
				$result = str_replace('##AMP##', '&', $result);
			}

			return $result;
		}

		/**
		 * Returns allowed tags and attributies
		 * @return string
		 */
		public function GetTags()
		{
			if(!is_array($this->arHtmlTags))
				return false;

			$confStr="";

			foreach ($this->arHtmlTags as $tag => $arAttrs)
			{
				$confStr.=$tag." (";
				foreach ($arAttrs as $attr)
					if($attr)
						$confStr.=" ".$attr." ";
				$confStr.=")<br>";
			}

			return $confStr;
		}

		/**
		 * @deprecated For compability only will be erased next versions
		 */
		public static function SetTags($arTags)
		{
			self::$arOldTags = $arTags;

			/* for next version
			$this->DelAllTags();

			return $this->AddTags($arTags);
			*/
		}

		/**
		 * @deprecated For compability only will be erased next versions
		 */
		public static function Sanitize($html, $secLevel='HIGH', $htmlspecialchars=true, $delTags=true)
		{
			$Sanitizer = new self;

			if(empty(self::$arOldTags))
				$Sanitizer->SetLevel(self::SECURE_LEVEL_HIGH);
			else
			{
				$Sanitizer->DelAllTags();
				$Sanitizer->AddTags(self::$arOldTags);
			}

			$Sanitizer->ApplyHtmlSpecChars($htmlspecialchars);
			$Sanitizer->DeleteSanitizedTags($delTags);
			$Sanitizer->ApplyDoubleEncode();

			return $Sanitizer->SanitizeHtml($html);
		}

		/**
		 * Split html to tags and simple text chunks
		 * @param string $html
		 * @return array
		 */
		protected function splitHtml($html)
		{
			$result = [];
			$arData = preg_split('/(<[^<>]+>)/si'.BX_UTF_PCRE_MODIFIER, $html, -1, PREG_SPLIT_DELIM_CAPTURE);

			foreach($arData as $i => $chunk)
			{
				$isTag = $i % 2 || (mb_substr($chunk, 0, 1) == '<' && mb_substr($chunk, -1) == '>');

				if ($isTag)
				{
					$result[] = array('segType'=>'tag', 'value'=>$chunk);
				}
				elseif ($chunk != "")
				{
					$result[]=array('segType'=>'text', 'value'=> $chunk);
				}
			}

			return $result;
		}

		/**
		 * Erases, or HtmlSpecChares Tags and attributies wich not contained in white list
		 * from inputted HTML
		 * @param string $html Dirty HTML
		 * @return string filtered HTML
		 */
		public function SanitizeHtml($html)
		{
			if(empty($this->arHtmlTags))
				$this->SetLevel(self::SECURE_LEVEL_HIGH);

			$openTagsStack = array();
			$isCode = false;

			$seg = $this->splitHtml($html);

			//process segments
			$segCount = count($seg);
			for($i=0; $i<$segCount; $i++)
			{
				if($seg[$i]['segType'] == 'text')
				{
					if (trim($seg[$i]['value']) && ($tp = array_search('table', $openTagsStack)) !== false)
					{
						$cellTags = array_intersect(array('td', 'th'), array_keys($this->arHtmlTags));
						if ($cellTags && !array_intersect($cellTags, array_slice($openTagsStack, $tp+1)))
						{
							array_splice($seg, $i, 0, array(array('segType' => 'tag', 'value' => sprintf('<%s>', reset($cellTags)))));
							$i--; $segCount++;

							continue;
						}
					}

					if ($this->bHtmlSpecChars)
					{
						$openTagsStackSize = count($openTagsStack);
						$entQuotes = ($openTagsStackSize && $openTagsStack[$openTagsStackSize-1] === 'style' ? ENT_NOQUOTES : ENT_QUOTES);

						$seg[$i]['value'] = htmlspecialchars(
							$seg[$i]['value'],
							$entQuotes,
							LANG_CHARSET,
							$this->bDoubleEncode
						);
					}
				}
				elseif(
					$seg[$i]['segType'] == 'tag'
					&& (
						preg_match('/^<!--\\[if\\s+((?:mso|gt|lt|gte|lte|\\||!|[0-9]+|\\(|\\))\\s*)+\\]>$/', $seg[$i]['value'])
						|| preg_match('/^<!\\[endif\\]-->$/', $seg[$i]['value'])
					)
				)
				{
					//Keep ms html comments https://stackoverflow.design/email/base/mso/
					$seg[$i]['segType'] = 'text';
				}
				elseif($seg[$i]['segType'] == 'tag')
				{
					//find tag type (open/close), tag name, attributies
					preg_match('#^<\s*(/)?\s*([a-z0-9]+)(.*?)>$#si'.BX_UTF_PCRE_MODIFIER, $seg[$i]['value'], $matches);
					$seg[$i]['tagType'] = !empty($matches[1]) ? 'close' : 'open';
					$seg[$i]['tagName'] = mb_strtolower($matches[2] ?? '');

					if(($seg[$i]['tagName']=='code') && ($seg[$i]['tagType']=='close'))
						$isCode = false;

					//if tag founded inside  <code></code>  it is simple text
					if($isCode)
					{
						$seg[$i]['segType'] = 'text';
						$i--;
						continue;
					}

					if($seg[$i]['tagType'] == 'open')
					{
						// if tag unallowed screen it, or erase
						if(!array_key_exists($seg[$i]['tagName'], $this->arHtmlTags))
						{
							if($this->bDelSanitizedTags)
							{
								$seg[$i]['action'] = self::ACTION_DEL;
							}
							else
							{
								$seg[$i]['segType'] = 'text';
								$i--;
								continue;
							}
						}
						//if allowed
						else
						{
							if (in_array('table', $openTagsStack))
							{
								if ($openTagsStack[count($openTagsStack)-1] == 'table')
								{
									if (array_key_exists('tr', $this->arHtmlTags) && !in_array($seg[$i]['tagName'], array('thead', 'tfoot', 'tbody', 'tr')))
									{
										array_splice($seg, $i, 0, array(array('segType' => 'tag', 'tagType' => 'open', 'tagName' => 'tr', 'action' => self::ACTION_ADD)));
										$i++; $segCount++;

										$openTagsStack[] = 'tr';
									}
								}

								if (in_array($openTagsStack[count($openTagsStack)-1], array('thead', 'tfoot', 'tbody')))
								{
									if (array_key_exists('tr', $this->arHtmlTags) && $seg[$i]['tagName'] != 'tr')
									{
										array_splice($seg, $i, 0, array(array('segType' => 'tag', 'tagType' => 'open', 'tagName' => 'tr', 'action' => self::ACTION_ADD)));
										$i++; $segCount++;

										$openTagsStack[] = 'tr';
									}
								}

								if ($seg[$i]['tagName'] == 'tr')
								{
									for ($j = count($openTagsStack)-1; $j >= 0; $j--)
									{
										if (in_array($openTagsStack[$j], array('table', 'thead', 'tfoot', 'tbody')))
											break;

										array_splice($seg, $i, 0, array(array('segType' => 'tag', 'tagType' => 'close', 'tagName' => $openTagsStack[$j], 'action' => self::ACTION_ADD)));
										$i++; $segCount++;

										array_splice($openTagsStack, $j, 1);
									}
								}

								if ($openTagsStack[count($openTagsStack)-1] == 'tr')
								{
									$cellTags = array_intersect(array('td', 'th'), array_keys($this->arHtmlTags));
									if ($cellTags && !in_array($seg[$i]['tagName'], $cellTags))
									{
										array_splice($seg, $i, 0, array(array('segType' => 'tag', 'tagType' => 'open', 'tagName' => reset($cellTags), 'action' => self::ACTION_ADD)));
										$i++; $segCount++;

										$openTagsStack[] = 'td';
									}
								}

								if (in_array($seg[$i]['tagName'], array('td', 'th')))
								{
									for ($j = count($openTagsStack)-1; $j >= 0; $j--)
									{
										if ($openTagsStack[$j] == 'tr')
											break;

										array_splice($seg, $i, 0, array(array('segType' => 'tag', 'tagType' => 'close', 'tagName' => $openTagsStack[$j], 'action' => self::ACTION_ADD)));
										$i++; $segCount++;

										array_splice($openTagsStack, $j, 1);
									}
								}
							}

							//Processing valid tables
							//if find 'tr','td', etc...
							if(array_key_exists($seg[$i]['tagName'], $this->arTableTags))
							{
								$this->CleanTable($seg, $openTagsStack, $i, false);

								if(isset($seg[$i]['action']) && $seg[$i]['action'] == self::ACTION_DEL)
									continue;
							}

							$seg[$i]['attr'] = $this->processAttributes(
								(string)$matches[3], //attributes string
								(string)$seg[$i]['tagName']
							);

							if($seg[$i]['tagName'] === 'code')
							{
								$isCode = true;
							}

							//if tag need close tag add it to stack opened tags
							if(!in_array($seg[$i]['tagName'], $this->arNoClose)) //!count($this->arHtmlTags[$seg[$i]['tagName']]) || fix: </br>
							{
								$openTagsStack[] = $seg[$i]['tagName'];
								$seg[$i]['closeIndex'] = count($openTagsStack)-1;
							}
						}
					}
					//if closing tag
					else
					{	//if tag allowed
						if(array_key_exists($seg[$i]['tagName'], $this->arHtmlTags) && (!count($this->arHtmlTags[$seg[$i]['tagName']]) || ($this->arHtmlTags[$seg[$i]['tagName']][count($this->arHtmlTags[$seg[$i]['tagName']])-1] != false)))
						{
							if($seg[$i]['tagName'] == 'code')
							{
								$isCode = false;
							}
							//if open tags stack is empty, or not include it's name lets screen/erase it
							if((empty($openTagsStack)) || (!in_array($seg[$i]['tagName'], $openTagsStack)))
							{
								if($this->bDelSanitizedTags || $this->arNoClose)
								{
									$seg[$i]['action'] = self::ACTION_DEL;
								}
								else
								{
									$seg[$i]['segType'] = 'text';
									$i--;
									continue;
								}
							}
							else
							{
								//if this tag don't match last from open tags stack , adding right close tag
								$tagName = array_pop($openTagsStack);
								if($seg[$i]['tagName'] != $tagName)
								{
									array_splice($seg, $i, 0, array(array('segType'=>'tag', 'tagType'=>'close', 'tagName'=>$tagName, 'action'=>self::ACTION_ADD)));
									$segCount++;
								}
							}
						}
						//if tag unallowed erase it
						else
						{
							if($this->bDelSanitizedTags)
							{
								$seg[$i]['action'] = self::ACTION_DEL;
							}
							else
							{
								$seg[$i]['segType'] = 'text';
								$i--;
								continue;
							}
						}
					}
				}
			}

			//close tags stayed in stack
			foreach(array_reverse($openTagsStack) as $val)
				array_push($seg, array('segType'=>'tag', 'tagType'=>'close', 'tagName'=>$val, 'action'=>self::ACTION_ADD));

			//build filtered code and return it
			$filteredHTML = '';
			$flagDeleteContent = false;

			foreach($seg as $segt)
			{
				if(($segt['action'] ?? '') != self::ACTION_DEL && !$flagDeleteContent)
				{
					if($segt['segType'] == 'text')
					{
						$filteredHTML .= $segt['value'];
					}
					elseif($segt['segType'] == 'tag')
					{
						if($segt['tagType'] == 'open')
						{
							$filteredHTML .= '<'.$segt['tagName'];

							if(isset($segt['attr']) && is_array($segt['attr']))
								foreach($segt['attr'] as $attr_key => $attr_val)
									$filteredHTML .= ' '.$attr_key.'="'.$attr_val.'"';

							if (count($this->arHtmlTags[$segt['tagName']]) && ($this->arHtmlTags[$segt['tagName']][count($this->arHtmlTags[$segt['tagName']])-1] == false))
								$filteredHTML .= " /";

							$filteredHTML .= '>';
						}
						elseif($segt['tagType'] == 'close')
							$filteredHTML .= '</'.$segt['tagName'].'>';
					}
				}
				else
				{
					if(in_array($segt['tagName'], $this->delTagsWithContent))
					{
						$flagDeleteContent = $segt['tagType'] == 'open';
					}
				}
			}

			if(!$this->bHtmlSpecChars && $html != $filteredHTML)
			{
				$filteredHTML = $this->SanitizeHtml($filteredHTML);
			}

			return $filteredHTML;
		}

		protected function extractAttributes(string $attrData): array
		{
			$result = [];

			preg_match_all(
				'#([a-z0-9_-]+)\s*=\s*([\'\"]?)(?:\s*)(.*?)(?:\s*)\2(\s|$|(?:\/\s*$))+#is'.BX_UTF_PCRE_MODIFIER,
				$attrData,
				$result,
				PREG_SET_ORDER
			);

			return $result;
		}

		protected function processAttributes(string $attrData, string $currTag): array
		{
			$attr = [];
			$arTagAttrs = $this->extractAttributes($attrData);

			foreach($arTagAttrs as $arTagAttr)
			{
				// Attribute name
				$arTagAttr[1] = mb_strtolower($arTagAttr[1]);
				$attrAllowed = in_array($arTagAttr[1], $this->arHtmlTags[$currTag], true);

				if (!$attrAllowed && array_key_exists($arTagAttr[1], $this->additionalAttrs))
				{
					$attrAllowed = true === call_user_func($this->additionalAttrs[$arTagAttr[1]]['tag'], $currTag);
				}

				if ($attrAllowed)
				{
					// Attribute value. Wrap attribute by "
					$arTagAttr[3] = str_replace('"', "'", $arTagAttr[3]);

					if($this->IsValidAttr($arTagAttr))
					{
						$attr[$arTagAttr[1]] = $this->encodeAttributeValue($arTagAttr);
					}
				}
			}

			return $attr;
		}

		/**
		 * function CleanTable
		 * Check if table code is valid, and corrects. If need
		 * deletes all text and tags between diferent table tags if $delTextBetweenTags=true.
		 * Checks if where are open tags from upper level if not - self-distructs.
		 */
		protected function CleanTable(&$seg, &$openTagsStack, $segIndex, $delTextBetweenTags=true)
		{
			//if we found up level or not
			$bFindUp = false;
			//count open & close tags
			$arOpenClose = array();

			for ($tElCategory=self::TABLE_COLS;$tElCategory>self::TABLE_TOP;$tElCategory--)
			{
				if($this->arTableTags[$seg[$segIndex]['tagName']] != $tElCategory)
					continue;

				//find back upper level
				for($j=$segIndex-1;$j>=0;$j--)
				{
					if ($seg[$j]['segType'] != 'tag' || !array_key_exists($seg[$j]['tagName'], $this->arTableTags))
						continue;

					if(isset($seg[$j]['action']) && $seg[$j]['action'] == self::ACTION_DEL)
						continue;

					if($tElCategory == self::TABLE_COLS)
					{
						if($this->arTableTags[$seg[$j]['tagName']] == self::TABLE_COLS || $this->arTableTags[$seg[$j]['tagName']] == self::TABLE_ROWS)
							$bFindUp = true;
					}
					else
						if($this->arTableTags[$seg[$j]['tagName']] <= $tElCategory)
							$bFindUp = true;

					if(!$bFindUp)
						continue;

					//count opened and closed tags
					if (!isset($arOpenClose[$seg[$j]['tagName']][$seg[$j]['tagType']]))
					{
						$arOpenClose[$seg[$j]['tagName']][$seg[$j]['tagType']] = 0;
					}
					$arOpenClose[$seg[$j]['tagName']][$seg[$j]['tagType']]++;

					//if opened tag not found yet, searching for more
					$openCount = $arOpenClose[$seg[$j]['tagName']]['open'] ?? 0;
					$closeCount = $arOpenClose[$seg[$j]['tagName']]['close'] ?? 0;
					if($openCount <= $closeCount)
					{
						$bFindUp = false;
						continue;
					}


					if(!$delTextBetweenTags)
						break;

					//if find up level let's mark all middle text and tags for del-action
					for($k=$segIndex-1;$k>$j;$k--)
					{
						//lt's save text-format
						if($seg[$k]['segType'] == 'text' && !preg_match("#[^\n\r\s]#i".BX_UTF_PCRE_MODIFIER, $seg[$k]['value']))
							continue;

						$seg[$k]['action'] = self::ACTION_DEL;
						if(isset($seg[$k]['closeIndex']))
							unset($openTagsStack[$seg[$k]['closeIndex']]);
					}

					break;

				}
				//if we didn't find up levels,lets mark this block as del
				if(!$bFindUp)
					$seg[$segIndex]['action'] = self::ACTION_DEL;

				break;

			}
			return $bFindUp;
		}

		/**
		 * Decodes text from codes like &#***, html-entities wich may be coded several times;
		 * @param string $str
		 * @return string decoded
		 * */
		public function Decode($str)
		{
			$str1="";

			while($str1 <> $str)
			{
				$str1 = $str;
				$str = $this->_decode($str);
				$str = str_replace("\x00", "", $str);
				$str = preg_replace("/\&\#0+(;|([^\d;]))/is", "\\2", $str);
				$str = preg_replace("/\&\#x0+(;|([^\da-f;]))/is", "\\2", $str);
			}

			return $str1;
		}

		/*
		Function is used in regular expressions in order to decode characters presented as &#123;
		*/
		protected function _decode_cb($in)
		{
			$ad = $in[2];
			if($ad == ';')
				$ad="";
			$num = intval($in[1]);
			return chr($num).$ad;
		}

		/*
		Function is used in regular expressions in order to decode characters presented as  &#xAB;
		*/
		protected function _decode_cb_hex($in)
		{
			$ad = $in[2];
			if($ad==';')
				$ad="";
			$num = intval(hexdec($in[1]));
			return chr($num).$ad;
		}

		/*
		Decodes string from html codes &#***;
		One pass!
		-- Decode only a-zA-Z:().=, because only theese are used in filters
		*/
		protected function _decode($str)
		{
			$str = preg_replace_callback("/\&\#(\d+)([^\d])/is", array("CBXSanitizer", "_decode_cb"), $str);
			$str = preg_replace_callback("/\&\#x([\da-f]+)([^\da-f])/is", array("CBXSanitizer", "_decode_cb_hex"), $str);
			return str_replace(array("&colon;","&tab;","&newline;"), array(":","\t","\n"), $str);
		}

		/**
		 * @param array $tags
		 */
		public function setDelTagsWithContent(array $tags)
		{
			$this->delTagsWithContent = $tags;
		}

		/**
		 * @return array
		 */
		public function getDelTagsWithContent()
		{
			return $this->delTagsWithContent;
		}
	};
