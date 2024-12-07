<?

IncludeModuleLangFile(__FILE__);

class CWikiParser
{
	public $arNowiki = array();
	public $arCode = array();
	public $arLink = array();
	public $arLinkExists = array();
	public $arFile = array();
	public $arVersionFile = array();

	private $postUrl = "";
	private $textType = "";

	function __construct()
	{

	}

	function Parse($text, $type = 'text', $arFile = array(), $arParams = array())
	{
		$type = $this->textType = ($type == 'html' ? 'html' : 'text');
		$this->arNowiki = array();
		$this->arLink = array();
		$this->arLinkExists = array();
		$this->arFile = $arFile;
		$this->arVersionFile = array();
		// An array can be either array (23,45,67), and array ('file_name' => 'file_path'), if this version of the document in the history of
		if (!is_array($this->arFile))
			$this->arFile = array();
		foreach ($this->arFile as $_k => $file)
		{
			if (!is_numeric($file) && !is_numeric($_k))
			{
				$this->arVersionFile[$_k] = $file;
				unset($this->arFile[$_k]);
			}
		}
		reset($this->arFile);

		if(isset($arParams["POST_URL"]))
			$this->postUrl = (CMain::IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"].$arParams["POST_URL"];

		// cut code
		$text = preg_replace_callback("/(\{\{\{(.*)\}\}\})/imsUu", array(&$this, '_codeCallback'), $text);
		$text = preg_replace_callback("/(\[CODE\](.*)\[\/CODE\])/imsUu", array(&$this, '_codeCallback'), $text);

		// cut nowiki
		$text = preg_replace_callback('/(<nowiki>(.*)<\/nowiki>)/isUu', array(&$this, '_noWikiCallback'), $text);

		#if($this->textType == "html")
		#	$text = CWikiUtils::htmlspecialchars_decode($text);

		// bi
		$text = str_replace("&#039;&#039;&#039;", "'''", $text);
		$text = str_replace("&#039;&#039;", "''", $text);
		$text = preg_replace(
			array(
				'/\'{3}(.*)\'{2}(.+)\'{2}(.*)\'{3}/imUu',
				'/\'{3}(.+)\'{3}/imUu',
				'/\'{2}(.+)\'{2}/imUu'
			),
			array(
				'<b>\\1<i>\\2</i>\\3</b>',
				'<b>\\1</b>',
				'<i>\\1</i>'
			),
			$text);

		// hr
		$text = preg_replace( '/-----*/u', '\\1<hr />', $text );

		// Header
		for($i = 6; $i >= 1; $i--)
		{
			$_H = str_repeat('=', $i);
			$text = preg_replace('/^\s*'.$_H.'(.+?)'.$_H.'\s*$/miUu', '<H'.$i.'>\\1</H'.$i.'>', $text);
		}


		// Internal link & categories
		$text = $this->processInternalLink($text);

		// External link
		$text = preg_replace_callback('/\[((http|https|ftp)(.+))( (.+))?\]/iUu', array(&$this, '_processExternalLinkCallback'), $text);


		// images and other files
		$text = preg_replace_callback('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):(.+)\]\]?/iUu', array(&$this, '_processFileCallback'), $text);

		// TOC
		$text = $this->processToc($text);

		// Paste nowiki
		if (!empty($this->arNowiki))
			$text = preg_replace_callback('/(##NOWIKI(\d+)##)/isUu', array(&$this, '_noWikiReturnCallback'), $text);


		$text = preg_replace_callback('/(##NOWIKI(\d+)##)/isUu', array(&$this, '_noWikiReturn2Callback'), $text);

		if ($type == 'text')
		{
			$text = preg_replace("/(<\s*\/?(h(\d+)|li|ul|ol|p|table|tbody|td|tr|hr|div|span)\s*>)\s*(<\s*br\s*\/*\s*>){0,1}(\s*(\r*\n)\s*){1,2}/ism", "$1##NN##", $text);
			$text = preg_replace("/(<\s*(ul)\s*>)\s*(<\s*br\s*\/*\s*>){0,1}(\s*(\r*\n)\s*){1,2}/ism", "$1##NN##", $text);
			$text = preg_replace("/<\s*br\s*\/*\s*>\s*(\r*\n)/ismU", "##BR##", $text);
			$text = self::NToBr($text);
			$text = preg_replace("/##NN##/ismU","\n", $text);
			$text = preg_replace("/##BR##/ismU","<br />\n", $text);
		}

		// Paste code
		$text = preg_replace_callback('/(##CODE(\d+)##)/isUu', array(&$this, '_codeReturnCallback'), $text);

		//		$text .= '<div style="clear:both"></div>';

		return $text;
	}

	static function NToBr($text)
	{
		$ret = preg_replace("/(<\s*br\s*\/*\s*>)*\s*(\r)*\n/ism", "<br />\n",$text);
		return $ret;
	}

	static function BrToN($text)
	{
		$ret = preg_replace("/<\s*br\s*\/*>((\r)*\n)*/ism", "\n",$text);
		return $ret;
	}

	static function Clear($text)
	{
		$arWhiteTags = array(
			'a'			=> array('href', 'title','name','style','id','class','shape','coords','alt','target'),
			'b'			=> array('style','id','class'),
			'br'		=> array('style','id','class'),
			'big'		=> array('style','id','class'),
			'caption'	=> array('style','id','class'),
			'code'		=> array('style','id','class'),
			'color'		=> array(),
			'del'		=> array('title','style','id','class'),
			'div'		=> array('title','style','id','class','align'),
			'dt'		=> array('style','id','class'),
			'dd'		=> array('style','id','class'),
			'font'		=> array('color','size','face','style','id','class'),
			'h1'		=> array('id','class','align'),
			'h2'		=> array('id','class','align'),
			'h3'		=> array('id','class','align'),
			'h4'		=> array('id','class','align'),
			'h5'		=> array('id','class','align'),
			'h6'		=> array('id','class','align'),
			'hr'		=> array('style','id','class'),
			'i'			=> array('style','id','class'),
			'img'		=> array('src','alt','height','width','title'),
			'ins'		=> array('title','style','id','class'),
			'li'		=> array('style','id','class'),
			'list'		=> array(),
			'map'		=> array('shape','coords','href','alt','title','style','id','class','name'),
			'nowiki'	=> array(),
			'ol'		=> array('style','id','class'),
			'p'			=> array('style','id','class','align'),
			'pre'		=> array('style','id','class'),
			's'			=> array('style','id','class'),
			'small'		=> array('style','id','class'),
			'strong'	=> array('style','id','class'),
			'span'		=> array('title','style','id','class','align'),
			'sub'		=>array('style','id','class'),
			'sup'		=>array('style','id','class'),
			'table'		=> array('border','width','style','id','class','cellspacing','cellpadding'),
			'tbody'		=> array('align','valign','style','id','class'),
			'td'		=> array('width','height','style','id','class','align','valign','colspan','rowspan'),
			'tfoot'		=> array('align','valign','style','id','class','align','valign'),
			'th'		=> array('width','height','style','id','class','colspan','rowspan'),
			'thead'		=> array('align','valign','style','id','class'),
			'tr'		=> array('align','valign','style','id','class'),
			'ul'		=> array('style','id','class'),
			'blockquote'	=> array(),
			'u'			=> array('style','id','class')
			);

		/* TODO:erase CBXSanitizer::SetTags($arWhiteTags);
		$text=CBXSanitizer::Sanitize($text,'CUSTOM',true,true); */

		$Sanitizer = new CBXSanitizer;
		$Sanitizer->AddTags($arWhiteTags);

		//TODO: delete condition, after main update
		if(method_exists($Sanitizer,"ApplyDoubleEncode"))
			$Sanitizer->ApplyDoubleEncode(false);

		$text = $Sanitizer->SanitizeHtml($text);

		return $text;
	}

	function _processFileCallback($matches)
	{
		static $sImageAlign = '';
		$bLink = false;
		if ($matches[1] == ':')
			$bLink = true;

		// if the internal file then get it
		$sFile = $sFileName = $sPath = CWikiUtils::htmlspecialcharsback(trim($matches[3]));
		$bOur = false;

		if (is_numeric($sFile) && in_array($sFile, $this->arFile))
		{
			$arFile = CFile::GetFileArray($sFile);
			if ($arFile != false)
			{
				$bOur = true;
				$sPath = $arFile['SRC'];
				$sFileName = $arFile['ORIGINAL_NAME'];
			}
		}
		else if (isset($this->arVersionFile[mb_strtolower($sFile)]))
		{
			$sPath = $this->arVersionFile[mb_strtolower($sFile)];
			$sFileName = $sFile;
		}
		else if (!empty($this->arFile))
		{
			$arFilter = array(
				'@ID' => implode(',', $this->arFile)
			);

			$rsFile = CFile::GetList(array(), $arFilter);
			while($arFile = $rsFile->Fetch())
			{
				if ($arFile['ORIGINAL_NAME'] == $sFile)
				{
					$bOur = true;
					$sFile = $arFile['ID'];

					$sPath = CFile::GetFileSRC($arFile);
					$sFileName = $arFile['ORIGINAL_NAME'];
					break;
				}
			}
		}

		// if the image is processed as a picture
		$sName = bx_basename($sPath);

		if (CFile::IsImage($sName))
		{
			if ($bOur)
			{
				$imageFile = CFile::MakeFileArray($sPath);
				$checkRes = CFile::CheckImageFile($imageFile);

				if($checkRes != null)
					return $checkRes;

				if ($bLink)
					$sReturn = '<a href="'.htmlspecialcharsbx($sPath).'" title="'.($s = htmlspecialcharsbx($sFileName)).'">'.$s.'</a>';
				else
				{
					$sReturn  = CFile::ShowImage($sFile,
						COption::GetOptionString('wiki', 'image_max_width', 600),
						COption::GetOptionString('wiki', 'image_max_height', 600),
						'border="0" align="'.$sImageAlign.'"'
					);
				}
			}
			else
			{
				if ($bLink)
					$sReturn = '<a href="'.htmlspecialcharsbx($sPath).'" title="'.($s = htmlspecialcharsbx($sName)).'">'.$s.'</a>';
				else
					$sReturn = '<img src="'.htmlspecialcharsbx($sPath).'" alt="'.htmlspecialcharsbx($sFileName).'"/>';
			}
		}
		else if (mb_strpos($sPath, 'http://') === 0)
			$sReturn = ' [ <a href="'.htmlspecialcharsbx($sFile).'" title="'.GetMessage('FILE_FILE_DOWNLOAD').'">'.GetMessage('FILE_DOWNLOAD').'</a> ] ';
		// otherwise the file
		else
			$sReturn = '['.GetMessage('FILE_NAME').':'.htmlspecialcharsbx((is_numeric($sFile)  || empty($sFileName) ? $sFile : $sFileName)).']';

		return $sReturn;
	}

	function _processExternalLinkCallback($matches)
	{
		$sLink = trim($matches[1]);
		$sName = $sTitle = $sLink;

		$matches[5] = isset($matches[5]) ? trim($matches[5]) : '';
		if (!empty($matches[5]))
			$sTitle = trim($matches[5]);
		$sTitle = strip_tags($sTitle);

		$sReturn = '<a href="'.htmlspecialcharsbx($sLink).'" title="'.htmlspecialcharsbx($sName).'">'.$sTitle.'</a>';
		return $sReturn;
	}

	function processInternalLink($text)
	{
		global $APPLICATION, $arParams;
		$text = preg_replace_callback('/\[\[(.+)(\|(.*))?\]\]/iUu', array(&$this, '_processInternalLinkPrepareCallback'), $text);
		$text = preg_replace('/(##Category##)(\s)*((\r*)\n)*/',"", $text);
		// check pages for exists
		if (!empty($this->arLink))
		{
			$arFilter = array();
			$arFilter['=NAME'] = $this->arLink;
			$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
			$arFilter['ACTIVE'] = 'Y';
			$arFilter['CHECK_PERMISSIONS'] = 'N';
			if (CWikiSocnet::IsSocNet())
				$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;
			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
			while($obElement = $rsElement->GetNextElement())
			{
				$arFields = $obElement->GetFields();
				$this->arLinkExists[] = mb_strtolower(CWikiUtils::htmlspecialcharsback($arFields['NAME'], true));
			}
		}

		$text = preg_replace_callback('/(##LINK(\d+)##)/isUu', array(&$this, '_processInternalLinkCallback'), $text);

		return $text;
	}

	function _processInternalLinkPrepareCallback($matches)
	{
		$sLink = trim($matches[1]);
		$sName = $sTitle = $sLink;
		$sCatName = '';
		$matches[3] = isset($matches[3]) ? trim($matches[3]) : '';

		if (!empty($matches[3]))
			$sName = $sTitle = $matches[3];
		else
		{
			if (CWikiUtils::IsCategoryPage($sName, $sCatName))
				return '##Category##';
		}

		$sTitle = strip_tags($sTitle);
		$i = count($this->arLink);
		$this->arLink[] = CWikiUtils::htmlspecialcharsback($matches[1], true);
		$sReturn = '<a ##LINK'.$i.'## title="'.$sTitle.'">'.$sName.'</a>';
		return $sReturn;
	}

	function _processInternalLinkCallback($matches)
	{
		global $arParams;

		$sReturn = '';
		if (in_array(mb_strtolower($this->arLink[$matches[2]]), $this->arLinkExists))
		{
			$sURL = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'],
				array(
					'wiki_name' => rawurlencode($this->arLink[$matches[2]]),
					'group_id' => CWikiSocnet::$iSocNetId
				)
			);
			$sReturn = 'href="'.$sURL.'"';
		}
		else
		{
			$sURL = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_POST_EDIT'],
					array(
						'wiki_name' => rawurlencode($this->arLink[$matches[2]]),
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'edit') : array()
			);

			$sReturn = 'href="'.$sURL.'" class="wiki_red"';
		}

		return $sReturn;
	}

	function processToc($text)
	{
		$matches = array();
		$sToc = '';
		if (preg_match_all('/<H(?<level>\d)(?<parameters>.*)>(?<innerHtml>.*)<\/H\g<level>>/isUu', $text, $matches, PREG_SET_ORDER))
		{
			if (count($matches) > 4)
			{
				$iCurrentToc = 1;
				// work level TOC
				$iCurrentTocLevel = 0;
				// previous user defined level of TOC
				$iRealPrevItemTocLevel = 1;
				// previous working level of TOC
				$iPrevItemTocLevel = 0;
				// current user defined the level of TOC
				$iRealItemTocLevel = 1;

				$bfirst = true;
				$aNumToc = array();
				foreach ($matches as $_m =>  $arMatch)
				{
					$iRealItemTocLevel = (int)$arMatch['level'];
					$sItemToc = trim($arMatch['innerHtml']);
					// normalize levels
					if ($bfirst && $iRealPrevItemTocLevel < $iRealItemTocLevel)
						$iItemTocLevel = 1;
					else if ($iCurrentTocLevel == 1 && $iRealItemTocLevel < $iRealPrevItemTocLevel)
						$iItemTocLevel = $iCurrentTocLevel;
					else if ($iRealItemTocLevel > $iRealPrevItemTocLevel)
						$iItemTocLevel = $iCurrentTocLevel + 1;
					else if ($iRealItemTocLevel < $iRealPrevItemTocLevel)
					{
						$_delta = $iRealPrevItemTocLevel - $iRealItemTocLevel;
						$iItemTocLevel = $iCurrentTocLevel - $_delta;
						if ($iItemTocLevel < 1)
							$iItemTocLevel = 1;
					}
					else
						$iItemTocLevel = $iCurrentTocLevel;

					// create a numbering of TOC
					$iCurrentNumTocLevel = $bfirst ? 1 : $iItemTocLevel;
					$aNumToc[$iCurrentNumTocLevel] =  !isset($aNumToc[$iCurrentNumTocLevel]) ? 1 : $aNumToc[$iCurrentNumTocLevel] + 1;
					if ($iItemTocLevel < $iPrevItemTocLevel)
					{
						for ($i = $iItemTocLevel + 1; $i <= $iPrevItemTocLevel; $i++)
							unset($aNumToc[$i]);
					}

					// build a TOC
					if ($iItemTocLevel > $iCurrentTocLevel || empty($sToc))
					{
						$iCurrentTocLevel++;
						$sToc .= '<ul>';
					}
					else if ($iItemTocLevel < $iCurrentTocLevel)
					{
						if ($iItemTocLevel <= 0)
							$iItemTocLevel = 1;

						if ($iCurrentTocLevel > 1)
						{
							for ($i = 0; $i < ($iCurrentTocLevel - $iItemTocLevel); $i++)
								$sToc .= '</ul>';
						}
						else
							$sToc .= '</ul>';

						if ($iCurrentTocLevel > 1)
							$iCurrentTocLevel = $iItemTocLevel;

					}

					$iRealPrevItemTocLevel = $iRealItemTocLevel;
					$iPrevItemTocLevel = $iItemTocLevel;
					$bfirst = false;
					$sNumToc = implode('.', $aNumToc);
					$sItemTocId = str_replace(array('%', '+', '.F2', '..'), array('.', '.', '_', '.'), rawurlencode($sItemToc.$sNumToc));
					$sToc .= '<li><a href="';

					if($this->postUrl) //http://jabber.bx/view.php?id=28203
						$sToc.= $this->postUrl;

					$sToc .= '#'.$sItemTocId.'">'.$sNumToc.' '.strip_tags($sItemToc).'</a></li>';
					$matches[$_m]['innerHtml'] = $sItemToc;
					$matches[$_m]['tocId'] = $sItemTocId;
				}

				for ($i = $iCurrentTocLevel; $i > 0; $i--)
					$sToc .= '</ul>';

				$bfirst = true;
				foreach ($matches as $arMatch)
				{
					$sReplase = '<H'.$arMatch['level'].$arMatch['parameters'].'><span id="'.$arMatch['tocId'].'">'.$arMatch['innerHtml'].'</span></H'.$arMatch['level'].'>';
					if ($bfirst)
						$sReplase = $sToc.'<br/>'.$sReplase;
					// so as not to replace all of the same titles
					$text = preg_replace('/'.preg_quote($arMatch[0], '/').'/u', $sReplase, $text, 1);
					$bfirst = false;
				}
			}
		}

		return $text;
	}

	function _codeCallback($matches)
	{
		$codeText = "";
		$i = count($this->arCode);
		$codeText = $matches[2];

		if($this->textType == "html")
			$codeText = CWikiUtils::htmlspecialchars_decode($codeText);

		$this->arCode[] = $codeText;

		return '##CODE'.$i.'##';
	}

	function _noWikiCallback($matches)
	{
		$i = count($this->arNowiki);
		$this->arNowiki[] = $matches[2];

		return '##NOWIKI'.$i.'##';
	}

	function _codeReturnCallback($matches)
	{
		return '<pre><code>'.$this->arCode[$matches[2]].'</code></pre>';
	}

	function _noWikiReturnCallback($matches)
	{
		return $this->arNowiki[$matches[2]];
	}

	function _noWikiReturn2Callback($matches)
	{
		return '<nowiki>'.htmlspecialcharsbx($this->arNowiki[$matches[2]]).'</nowiki>';

	}

	function parseBeforeSave($text, &$arCat = array(), $nameTemplate = "")
	{
		$userLogin = CWikiUtils::GetUserLogin(array(), $nameTemplate);

		//$text = preg_replace_callback('/(<nowiki>(.*)<\/nowiki>)/isUu', array(&$this, '_noWikiCallback'), $text);

		// Subscribe
		$text = preg_replace( '/--~~~~*/u', '\\1--'.$userLogin.' '.ConvertTimeStamp(false, 'FULL'), $text );

		// Category
		$matches = array();
		$textWithoutNowiki = preg_replace('/(<nowiki>(.*)<\/nowiki>)/isUu', '', $text);
		if (preg_match_all('/\[\[(Category|'.GetMessage('CATEGORY_NAME').'):(.+)\]\]/iUu', $textWithoutNowiki, $matches))
			$arCat = array_unique($matches[2]);

		//$text = preg_replace_callback('/(##NOWIKI(\d+)##)/isUu', array(&$this, '_noWikiReturn2Callback'), $text);

		return $text;
	}

	function parseForSearch($text)
	{
		// delete Category
		$text = preg_replace('/\[\[(Category|'.GetMessage('CATEGORY_NAME').'):(.+)\]\]/iUu', '', $text);
		// delete Files
		$text = preg_replace('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):(.+)\]\]?/iUu', '', $text);
		// delete External Links
		$text = preg_replace('/\[((http|https|ftp)(.+))( (.+))?\]/iUu', '\\1\\2 \\5', $text);
		// delete Internal Links
		$text = preg_replace('/\[\[(.+(?!:))(\|(.*))?\]\]/iUu', '\\1\\2', $text);

		// delete Headers
		for($i = 6; $i >= 1; $i--)
		{
			$_H = str_repeat('=', $i);
			$text = preg_replace('/'.$_H.'(.*?)'.$_H.'/miUu', '\\1', $text);
		}

		return $text;
	}

}

?>
