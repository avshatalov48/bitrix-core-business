<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!\Bitrix\Main\Loader::includeModule('fileman'))
{
	return false;
}

class CBitrixPlayer extends CBitrixComponent
{
	protected const PLAYER_JS_PATH = '/bitrix/js/fileman/player';

	protected $playerType;
	protected $jwConfig;
	protected $path;
	protected $warning = '';
	protected $conf;

	protected $jwFlashPath;

	public static function escapeFlashVar($str)
	{
		$str = str_replace(['?', '=', '&'], ['%3F', '%3D', '%26'], $str);
		return $str;
	}

	public static function isAndroid(): bool
	{
		return false !== strpos($_SERVER['HTTP_USER_AGENT'], "Android");
	}

	public static function isIOS(): bool
	{
		return (bool) preg_match('#\biPhone.*Mobile|\biPod|\biPad#', $_SERVER['HTTP_USER_AGENT']);
	}

	public static function isMobile(): bool
	{
		return ( self::isAndroid() || self::isIOS() );
	}

	protected function addFlashVar(&$jwConfig, $key, $value, $default = false)
	{
		if (!isset($value) || $value == '' || $value == $default)
			return;
		$jwConfig[$key] = self::escapeFlashVar($value);
	}

	protected function addJsVar(&$wmvConfig, $key, $value, $default = false)
	{
		if (!isset($value) || $value == '' || $value === $default)
			return;
		$wmvConfig[$key] = CUtil::JSEscape($value);
	}

	protected static function getFileExtension ($path)
	{
		return mb_strtolower(GetFileExtension($path));
	}

	public function getComponentId ()
	{
		return mb_substr(md5(serialize($this->arParams)), 10).$this->randString();
	}

	// fix 0084070
	public function GetContentType ($src)
	{
		static $arTypes = array(
			"jpeg" => "image/jpeg",
			"jpe" => "image/jpeg",
			"jpg" => "image/jpeg",
			"png" => "image/png",
			"gif" => "image/gif",
			"bmp" => "image/bmp",
			"xla" => "application/vnd.ms-excel",
			"xlb" => "application/vnd.ms-excel",
			"xlc" => "application/vnd.ms-excel",
			"xll" => "application/vnd.ms-excel",
			"xlm" => "application/vnd.ms-excel",
			"xls" => "application/vnd.ms-excel",
			"xlsx" => "application/vnd.ms-excel",
			"xlt" => "application/vnd.ms-excel",
			"xlw" => "application/vnd.ms-excel",
			"dbf" => "application/vnd.ms-excel",
			"csv" => "application/vnd.ms-excel",
			"doc" => "application/msword",
			"docx" => "application/msword",
			"dot" => "application/msword",
			"rtf" => "application/msword",
			"rar" => "application/x-rar-compressed",
			"zip" => "application/zip",
			"ogv" => "video/ogg",
			"mp4" => "video/mp4",
			"mp4v" => "video/mp4",
			"mpg4" => "video/mp4",
			"mpeg" => "video/mpeg",
			"mpg" => "video/mpeg",
			"mpe" => "video/mpeg",
			"m1v" => "video/mpeg",
			"m2v" => "video/mpeg",
			"webm" => "video/webm",
			"3gp" => "video/3gpp",
			"3g2" => "video/3gpp2",
			"h264" => "video/h264",
			"jpgv" => "video/jpeg",
			"qt" => "video/quicktime",
			"mov" => "video/quicktime",
			"dvb" => "video/vnd.dvb.file",
			"fvt" => "video/vnd.fvt",
			"mxu" => "video/vnd.mpegurl",
			"m4u" => "video/vnd.mpegurl",
			"pyv" => "video/vnd.ms-playready.media.pyv",
			"uvu" => "video/vnd.uvvu.mp4",
			"uvvu" => "video/vnd.uvvu.mp4",
			"viv" => "video/vnd.vivo",
			"f4v" => "video/x-f4v",
			"fli" => "video/x-fli",
			"flv" => "video/x-flv",
			"m4v" => "video/x-m4v",
			"mkv" => "video/x-matroska",
			"mk3d" => "video/x-matroska",
			"mks" => "video/x-matroska",
			"mng" => "video/x-mng",
			"asf" => "video/x-ms-asf",
			"asx" => "video/x-ms-asf",
			"vob" => "video/x-ms-vob",
			"wm" => "video/x-ms-wm",
			"wmv" => "video/x-ms-wmv",
			"wmx" => "video/x-ms-wmx",
			"wvx" => "video/x-ms-wvx",
			"avi" => "video/x-msvideo",
			"movie" => "video/x-sgi-movie",
			"smv" => "video/x-smv",
			"mpga" => "audio/mpeg",
			"mp2" => "audio/mpeg",
			"mp2a" => "audio/mpeg",
			"mp3" => "audio/mpeg",
			"m2a" => "audio/mpeg",
			"m3a" => "audio/mpeg",
			"ogg" => "audio/ogg",
			"oga" => "audio/ogg",
			"spx" => "audio/ogg",
			"weba" => "audio/webm",
			"aac" => "audio/aacp",
			"flac" => "audio/x-flac",
			"m3u" => "audio/x-mpegurl",
			"m3u8" => "application/vnd.apple.mpegurl",
			"ts" => "video/MP2T",
			"wav" => "audio/x-wav",
			"m4a" => "audio/mp4",
			"xml" => "application/xml",
		);
		$ext = self::getFileExtension($src);
		if (empty($ext) || mb_strpos($ext, 'php') === 0)
		{
			$type = 'application/octet-stream';
		}
		else
		{
			$type = $arTypes[$ext];
			if (!$type)
			{
				$uri = new \Bitrix\Main\Web\Uri($src);
				if (empty($uri->getHost()))
				{
					$type = @\CFile::GetContentType($src);
				}
			}
		}
		return $type;
	}

	/**
	 * Find an absolute path for html, set mime-type
	 *
	 * @param $path
	 * @param null $type
	 * @param bool $warning
	 * @return string
	 */
	protected function findCorrectFile($path, &$type = null, $warning = false)
	{
		// skip search if type is correct
		if ((mb_strpos($type, 'video') === 0) || (mb_strpos($type, 'audio') === 0) || (mb_strpos($type, 'rtmp') === 0))
			return $path;
		static $rewriteCondition = '';
		if(empty($path))
		{
			$type = $this->GetContentType($path);
			return $path;
		}
		$uri = new \Bitrix\Main\Web\Uri($path);
		if ($rewriteCondition === '')
		{
			if(\Bitrix\Main\Loader::includeModule('disk') && method_exists(\Bitrix\Disk\Driver::getInstance()->getUrlManager(), 'getUrlToDownloadByExternalLink'))
			{
				$extLinksAccessPoints = \CUrlRewriter::GetList(array('ID' => 'bitrix:disk.external.link'));
				if(empty($extLinksAccessPoints))
				{
					$rewriteCondition = "#^/docs/pub/(?<hash>[0-9a-f]{32})/(?<action>.*)\$#";
				}
				else
				{
					$rewrite = reset($extLinksAccessPoints);
					$rewriteCondition = $rewrite['CONDITION'];
				}
			}
			else
				$rewriteCondition = false;
		}
		// try to find on the disk
		if ($rewriteCondition)
		{
			// remove host
			$discPath = $uri->getPathQuery();
			// is it short uri ?
			$shortUri = \CBXShortUri::GetUri(mb_substr($discPath, 1));
			if($shortUri)
			{
				$discPath = $shortUri['URI'];
			}
			$hash = '';
			if (preg_match($rewriteCondition, $discPath, $matches))
			{
				if (isset($matches['hash']) && !empty($matches['hash']))
					$hash = $matches['hash'];
			}
			if ($hash != '')
			{
				$driver = \Bitrix\Disk\Driver::getInstance();
				$ext = \Bitrix\Disk\ExternalLink::load(array(
					'HASH' => $hash,
				));
				if ($ext)
				{
					$file = $ext->getFile()->getFile();
					$type = $file['CONTENT_TYPE'];
					return $driver->getUrlManager()->getUrlToDownloadByExternalLink($hash);
				}
			}
		}
		if ($uri->getHost() <> '')
		{
			if (mb_strpos($uri->getHost(), 'xn--') === false)
			{
				$arErrors = array();
				$punicodedPath = CBXPunycode::ToUnicode($uri->getHost(), $arErrors);

				if ($punicodedPath != $uri->getHost())
					$uri->setHost($punicodedPath);

				$path = $uri->getLocator();
			}
		}
		else // relative path
		{
			$documentRoot = $_SERVER['DOCUMENT_ROOT'];
			$path = Rel2Abs("/", $path);
			$pathOrig = $path;

			$io = CBXVirtualIo::GetInstance();

			if (!$io->FileExists($documentRoot.$path))
			{
				if(\Bitrix\Main\Loader::includeModule('clouds'))
				{
					$path = CCloudStorage::FindFileURIByURN($path, "component:player");
					if($path == "")
					{
						if ($warning)
							$this->warning .= $warning."<br />";
						$path = $pathOrig;
					}
				}
				else
				{
					if ($warning)
						$this->warning .= $warning."<br />";
					$path = $pathOrig;
				}
			}
		}
		if($uri->getScheme() == 'rtmp')
		{
			$type = self::GetRtmpContentType($path);
		}
		else
		{
			$type = self::GetContentType($path);
		}
		return $path;
	}

	protected static function GetRtmpContentType($src)
	{
		$type = "rtmp/mp4";
		static $arTypes = array(
			"mp4" => "rtmp/mp4",
			"mov" => "rtmp/mp4",
			"flv" => "rtmp/flv",
		);
		$ext = self::getFileExtension($src);
		if(!empty($ext) && isset($arTypes[$ext]))
		{
			$type = $arTypes[$ext];
		}
		return $type;
	}

	public static function isYoutubeSource ($path)
	{
		$arYoutubeDomains = array('youtube.com', 'youtu.be', 'www.youtube.com', 'www.youtu.be');
		$uri = new \Bitrix\Main\Web\Uri($path);
		if (in_array($uri->getHost(), $arYoutubeDomains))
			return true;
		return false;
	}

	public static function isVimeoSource ($path)
	{
		$arVimeoDomains = array('vimeo.com');
		$uri = new \Bitrix\Main\Web\Uri($path);
		if (in_array($uri->getHost(), $arVimeoDomains))
			return true;
		return false;
	}

	public static function isStreamingSource ($path)
	{
		$arStreamingExts = array('m3u8', 'ts');
		$ext = self::getFileExtension($path);
		if (in_array($ext, $arStreamingExts))
			return true;
		return false;
	}

	public static function isFlashSource ($path)
	{
		$arFlashExts = array('flv');
		$ext = self::getFileExtension($path);
		if (in_array($ext, $arFlashExts))
			return true;
		return false;
	}

	public static function isAudioSource ($path)
	{
		$arAudioExt = array('mp3', 'ogg', 'wav', 'weba');
		$ext = self::getFileExtension($path);
		if (in_array($ext, $arAudioExt))
			return true;
		return false;
	}

	public static function isWmvSource ($path, $type)
	{
		$arWmvExt = array('wmv', 'wma');
		$ext = self::getFileExtension($path);
		if (in_array($ext, $arWmvExt))
			return true;
		$arWmvTypes = array ('video/x-ms-wmv', 'audio/x-ms-wma');
		if (in_array($type, $arWmvTypes))
			return true;
		return false;
	}

	/**
	 * Parse xml playlist. Return array of tracks or false on wrong format
	 *
	 * @param $path
	 * @return array|bool
	 */
	public function parsePlaylist ($path)
	{
		$path = Rel2Abs("/", $path);
		$documentRoot = CSite::GetSiteDocRoot($this->getSiteId());
		$absPath = $documentRoot.$path;
		$objXML = new CDataXML();
		$objXML->Load($absPath);
		$arTree = $objXML->GetTree();
		$arTracks = false;

		$ch = $arTree->children;
		if(is_array($ch) && count($ch) > 0 && mb_strtolower($ch[0]->name) == 'playlist')
		{
			$pl = $ch[0];
			$tracklist = $pl->children;
			for ($i = 0, $l = count($tracklist); $i < $l; $i++)
			{
				if (mb_strtolower($tracklist[$i]->name) != 'tracklist')
					continue;
				$arTracks = array();
				$tracks = $tracklist[$i]->children;
				for ($i = 0, $l = count($tracks); $i < $l; $i++)
				{
					$track = $tracks[$i];
					if (mb_strtolower($track->name) == 'track')
					{
						$arTrack = array();
						for ($j = 0, $n = count($track->children); $j < $n; $j++)
						{
							$prop = $track->children[$j];
							if (mb_strtolower($prop->name) == 'location')
							{
								$arTrack['src'] = $objXML->xmlspecialcharsback($prop->content);
							}
							if (mb_strtolower($prop->name) == 'image')
							{
								if ($objXML->xmlspecialcharsback($prop->content) != '-')
									$arTrack['thumbnail'] = self::findCorrectFile($objXML->xmlspecialcharsback($prop->content));
							}
							if (mb_strtolower($prop->name) == 'title')
								$arTrack['title'] = $objXML->xmlspecialcharsback($prop->content);
						}
						if (!empty($arTrack['src']))
							$arTracks[] = $arTrack;
					}
				}
				break;
			}
		}
		return $arTracks;
	}

	public function onPrepareComponentParams($arParams)
	{
		if ($arParams['PLAYER_TYPE'] != 'videojs' && $arParams['PLAYER_TYPE'] != 'auto')
			$arParams['SIZE_TYPE'] = 'absolute';

		$arParams['SIZE_TYPE'] ??= null;
		if ($arParams['SIZE_TYPE'] == 'auto')
		{
			$arParams['WIDTH'] = $arParams['HEIGHT'] = '';
		}
		else
		{
			$arParams['WIDTH'] = intval($arParams['WIDTH']);
			if ($arParams['WIDTH'] <= 0)
				$arParams['WIDTH'] = 400;

			$arParams['HEIGHT'] = intval($arParams['HEIGHT']);
			if ($arParams['HEIGHT'] <= 0)
				$arParams['HEIGHT'] = 300;
		}

		if (($arParams['USE_PLAYLIST'] ?? null) == "Y")
		{
			$arParams['PLAYLIST_SIZE'] = intval($arParams['PLAYLIST_SIZE'] ?? 0);
			if ($arParams['PLAYLIST_SIZE'] <= 0)
				$arParams['PLAYLIST_SIZE'] = 150;

			$arParams['PLAYLIST_NUMBER'] = intval($arParams['PLAYLIST_NUMBER'] ?? 0);
			if ($arParams['PLAYLIST_NUMBER'] <= 0)
				$arParams['PLAYLIST_NUMBER'] = 3;
		}

		$arParams['VOLUME'] = intval($arParams['VOLUME'] ?? null);
		$arParams['PREVIEW'] ??= '';
		$arParams['PREVIEW'] = (mb_strlen($arParams['PREVIEW'])) ? $this->findCorrectFile($arParams['PREVIEW']) : '';
		$arParams['LOGO'] ??= null;
		$arParams['LOGO'] = ($arParams['LOGO'] <> '') ? $this->findCorrectFile($arParams['LOGO']) : '';
		$arParams['LOGO_LINK'] ??= null;
		$arParams['LOGO_LINK'] = trim($arParams['LOGO_LINK'] ?? '') != "" ? $arParams['LOGO_LINK'] : GetMessage("ABOUT_LINK");
		$arParams['CONTROLBAR'] ??= null;
		$arParams['CONTROLBAR'] = !empty($arParams['CONTROLBAR']) ? $arParams['CONTROLBAR'] : 'bottom';
		$arParams['SKIN_PATH'] ??= '';
		$arParams['SKIN_PATH'] = rtrim($arParams['SKIN_PATH'], "/")."/";
		$arParams['PLAYER_ID'] ??= '';
		$arParams['PLAYER_ID'] = htmlspecialcharsbx ($arParams['PLAYER_ID']);
		$arParams["START_TIME"] ??= null;
		$arParams["START_TIME"] = intval($arParams["START_TIME"]);

		if (empty($arParams['VOLUME']))
			$arParams['VOLUME'] = 90;

		if (intval($arParams['VOLUME']) > 100)
			$arParams['VOLUME'] = 100;

		if (intval($arParams['VOLUME']) < 0)
			$arParams['VOLUME'] = 0;

		if ($arParams["START_TIME"] <= 0)
			$arParams["START_TIME"] = 0;

		$arParams["PLAYBACK_RATE"] ??= null;
		$arParams["PLAYBACK_RATE"] = round($arParams["PLAYBACK_RATE"] ?? 0, 2);
		if ($arParams["PLAYBACK_RATE"] <= 0)
			$arParams["PLAYBACK_RATE"] = 1;

		if ($arParams["PLAYBACK_RATE"] > 3)
			$arParams["PLAYBACK_RATE"] = 3;

		if(($arParams['AUTOSTART'] ?? null) === 'Y')
		{
			$arParams['AUTOSTART_ON_SCROLL'] = 'N';
		}

		if(($arParams['USE_PLAYLIST_AS_SOURCES'] ?? null) !== 'Y')
		{
			$arParams['USE_PLAYLIST_AS_SOURCES'] = 'N';
		}

		return $arParams;
	}

	public function executeComponent()
	{
		global $APPLICATION, $USER;
		$this->jwFlashPath = $this->__path."/mediaplayer/player";
		$this->playerType = $this->arParams['PLAYER_TYPE'];
		$this->arResult['PATH'] ??= null;
		if (isset($this->arParams['TYPE']))
			$this->arResult['FILE_TYPE'] = $this->arParams['TYPE'];

		$type = $this->arResult['FILE_TYPE'] ?? '';
		$this->path = $this->findCorrectFile($this->arParams['PATH'] ?? '', $type);
		$this->arResult['FILE_TYPE'] = $type;

		$ext = self::getFileExtension($this->path);
		if (empty($this->playerType) || $this->playerType == 'auto')
		{
			if (self::isWmvSource($this->arResult['PATH'], $this->arResult['FILE_TYPE']))
				$this->playerType = 'wmv';
			else
				$this->playerType = 'videojs';
		}

		if ($this->playerType == 'flv' && $ext == 'swf' && $this->arParams['ALLOW_SWF'] != 'Y')
		{
			CComponentUtil::__ShowError(GetMessage("SWF_DENIED"));
			return false;
		}
		CJSCore::Init(['ajax']);
		if (isset($this->arParams['PLAYER_ID']) && $this->arParams['PLAYER_ID'] <> '')
			$this->arResult['ID'] = $this->arParams['PLAYER_ID'];
		else
			$this->arResult['ID'] = "bx_".$this->playerType."_player_".$this->getComponentId();

		$this->arResult['CSS_FILES'] = $this->arResult['JS_FILES'] = array();
		if ($this->playerType == 'wmv')
		{
			$this->processWmv();
		}
		elseif ($this->playerType == 'flv')
		{
			$this->processJwPlayer();
		}
		else
		{
			// process video.js
			// flags for used technologies
			$this->arResult['WMV'] = $this->arResult['FLASH'] = $this->arResult['STREAM'] = $this->arResult['COMMON_VIDEO'] = $this->arResult['YOUTUBE'] = $this->arResult['AUDIO_FILE'] = false;
			$this->arResult['VIDEOJS_PARAMS'] = array(
				'autoplay' => false,
				'preload' => false,
				'controls' => true,
				'height' => $this->arParams['HEIGHT'],
				'width' => $this->arParams['WIDTH'],
				'techOrder' => array('html5', 'flash'),
				'fluid' => false,
				'notSupportedMessage' => GetMessage('VIDEOJS_NOT_SUPPORTED_MESSAGE'),
				'errorMessages' => array(
					4 => GetMessage('VIDEOJS_ERROR_MESSAGE_4'),
				),
			);
			if ($this->arParams['SIZE_TYPE'] == 'fluid')
				$this->arResult['VIDEOJS_PARAMS']['fluid'] = true;
			$this->arResult['PATH'] = $this->path;
			$this->arParams['USE_PLAYLIST'] ??= null;
			if ($this->arParams['USE_PLAYLIST'] == 'Y')
			{
				if (!empty($this->arParams['TRACKS']))
					$this->arResult['TRACKS'] = $this->arParams['TRACKS'];
				else
					$this->arResult['TRACKS'] = self::parsePlaylist($this->arResult['PATH']);
				if ($this->arResult['TRACKS'] === false)
				{
					CComponentUtil::__ShowError(GetMessage("INCORRECT_PLAYLIST_FORMAT"));
					return false;
				}
				elseif (empty($this->arResult['TRACKS']))
				{
					CComponentUtil::__ShowError(GetMessage("NO_SUPPORTED_FILES"));
					return false;
				}
				foreach ($this->arResult['TRACKS'] as $key => &$arTrack)
				{
					if($this->processTrack ($arTrack) === false)
					{
						unset($this->arResult['TRACKS'][$key]);
					}
				}
			}
			else
			{
				$this->arResult['TRACKS'] = array();
				$arTrack = array(
					'src' => $this->arResult['PATH'],
					'type' => $this->arResult['FILE_TYPE'],
				);
				if ($this->processTrack ($arTrack))
					$this->arResult['TRACKS'] = array($arTrack);
			}
			if (count($this->arResult['TRACKS']) == 0)
			{
				CComponentUtil::__ShowError(GetMessage("NO_SUPPORTED_FILES"));
				return false;
			}
			elseif (count ($this->arResult['TRACKS']) == 1)
			{
				$this->arResult['TRACKS'] = array (reset ($this->arResult['TRACKS']));
				$this->arResult['PATH'] = $this->arResult['TRACKS'][0]['src'];
				$this->arResult['FILE_TYPE'] = $this->arResult['TRACKS'][0]['type'];
			}
			else
			{
				$this->arResult['TRACKS'] = array_values($this->arResult['TRACKS']);
			}
			CJSCore::Init(['player']);
			$playerScripts = CJSCore::getExtInfo('player');
			foreach($playerScripts['js'] as $script)
			{
				$this->arResult['JS_FILES'][] = $script;
			}
			foreach($playerScripts['css'] as $script)
			{
				$this->arResult['CSS_FILES'][] = $script;
			}
			$this->arResult['VIDEOJS_PARAMS']['sources'] = $this->arResult['TRACKS'];
			$this->processSkin();
			if($this->arResult['SKIN_NAME'])
			{
				$this->arResult['VIDEOJS_PARAMS']['skin'] = $this->arResult['SKIN_NAME'];
			}
			$this->arResult['VIMEO'] ??= null;
			if ($this->arResult['VIMEO'])
			{
				array_unshift($this->arResult['VIDEOJS_PARAMS']['techOrder'], 'vimeo');
				\Bitrix\Main\Page\Asset::getInstance()->addJs(static::PLAYER_JS_PATH.'/videojs/vimeo.js');
				$this->arResult['JS_FILES'][] = static::PLAYER_JS_PATH.'/videojs/vimeo.js';
			}
			$this->arParams['AUTOSTART'] ??= null;
			$this->arParams['REPEAT'] ??= null;
			if ($this->arResult['YOUTUBE'])
			{
				array_unshift($this->arResult['VIDEOJS_PARAMS']['techOrder'], 'youtube');
				if ($this->arParams['AUTOSTART'] === "Y" && !self::isMobile())
					$this->arResult['VIDEOJS_PARAMS']['youtube']['autoplay'] = 1;
				if ($this->arParams['SHOW_CONTROLS'] === "N")
					$this->arResult['VIDEOJS_PARAMS']['youtube']['controls'] = 0;
				if ($this->arParams['REPEAT'] == "always")
					$this->arResult['VIDEOJS_PARAMS']['youtube']['loop'] = 1;

				\Bitrix\Main\Page\Asset::getInstance()->addJs(static::PLAYER_JS_PATH.'/videojs/youtube.js');
				$this->arResult['JS_FILES'][] = static::PLAYER_JS_PATH.'/videojs/youtube.js';
			}
			if ($this->arResult['COMMON_VIDEO'] || $this->arResult['AUDIO_FILE'])
			{
				if ($this->arParams['AUTOSTART'] === "Y")
					$this->arResult['VIDEOJS_PARAMS']['autoplay'] = true;
				if (self::isMobile() && $this->arResult['YOUTUBE'])
					$this->arResult['VIDEOJS_PARAMS']['autoplay'] = false;
				if (($this->arParams['PRELOAD'] ?? null) === "Y")
					$this->arResult['VIDEOJS_PARAMS']['preload'] = true;
				if ($this->arParams['REPEAT'] == "always")
					$this->arResult['VIDEOJS_PARAMS']['loop'] = true;
			}
			if ($this->arResult['STREAM'])
			{
				$this->arParams['START_TIME'] = 0;
				\Bitrix\Main\Page\Asset::getInstance()->addJs(static::PLAYER_JS_PATH.'/videojs/videojs-contrib-hls.js');
				$this->arResult['JS_FILES'][] = static::PLAYER_JS_PATH.'/videojs/videojs-contrib-hls.js';
			}
			if ($this->arResult['WMV'] && count ($this->arResult['TRACKS']) > 1)
			{
				$this->processWmv();
				$this->arResult['WMV_CONFIG']['width'] = ($this->arParams['WIDTH'] - $this->arParams['PLAYLIST_SIZE'] > 0 ? $this->arParams['WIDTH'] - $this->arParams['PLAYLIST_SIZE'] : 0);
				$this->arResult['VIDEOJS_PARAMS']['wmv'] = $this->arResult['WMV_CONFIG'];
				$this->arResult['PLAYLIST_CONFIG'] = array();
				\Bitrix\Main\Page\Asset::getInstance()->addJs($this->__path.'/wmvplayer/silverlight.js');
				\Bitrix\Main\Page\Asset::getInstance()->addJs($this->__path.'/wmvplayer/wmvplayer.js');
				\Bitrix\Main\Page\Asset::getInstance()->addJs(static::PLAYER_JS_PATH.'/videojs/wmv.js');
				$this->arResult['JS_FILES'][] = $this->__path.'/wmvplayer/silverlight.js';
				$this->arResult['JS_FILES'][] = $this->__path.'/wmvplayer/wmvplayer.js';
				$this->arResult['JS_FILES'][] = static::PLAYER_JS_PATH.'/videojs/wmv.js';
				array_unshift($this->arResult['VIDEOJS_PARAMS']['techOrder'], 'wmv');
			}
			if($this->arResult['FLASH'])
			{
				$this->arResult['VIDEOJS_PARAMS']['hasFlash'] = true;
			}
			// flash and vimeo techs doesn't support playbackRate and currentTime properties
			if ($this->arResult['FLASH'] || $this->arResult['VIMEO'])
			{
				$this->arParams['PLAYBACK_RATE'] = 1;
				$this->arParams['START_TIME'] = 0;
				$this->arResult['VIDEOJS_PARAMS']['flash']['swf'] = static::PLAYER_JS_PATH.'/videojs/video-js.swf';
			}
			if(($this->arParams['AUTOSTART_ON_SCROLL'] ?? null) === 'Y')
			{
				$this->arResult['AUTOSTART_ON_SCROLL'] = 'Y';
				$this->arResult['VIDEOJS_PARAMS']['autostart'] = true;
				$this->arParams['START_TIME'] = 0;
			}
			$this->arResult['VOLUME'] = $this->arParams['VOLUME'] / 100;
			if (($this->arParams['MUTE'] ?? null) === "Y")
				$this->arResult['VIDEOJS_PARAMS']['muted'] = true;
			if (($this->arParams['SHOW_CONTROLS'] ?? null) === "N")
				$this->arResult['VIDEOJS_PARAMS']['controls'] = false;
			if (isset($this->arParams['PREVIEW']) && !empty($this->arParams['PREVIEW']) && !self::isIOS())
				$this->arResult['VIDEOJS_PARAMS']['poster'] = $this->arParams['PREVIEW'];
			elseif ($this->arResult['VIMEO'])
			{
				// a strange bug - vimeo doesn't play without a poster
				$this->arResult['VIDEOJS_PARAMS']['poster'] = $this->__path.'/images/black.png';
			}
			if($this->arParams['AUTOSTART'] === "Y")
				$this->arParams['START_TIME'] = 0;

			if($this->arParams['START_TIME'] > 0)
			{
				$this->arResult['VIDEOJS_PARAMS']['startTime'] = $this->arParams['START_TIME'];
			}

			if(($this->arParams['LAZYLOAD'] ?? null) === 'Y')
			{
				$this->arResult['LAZYLOAD'] = true;
				$this->arResult['VIDEOJS_PARAMS']['lazyload'] = true;
			}
		}

		$this->arResult['PLAYER_TYPE'] = $this->playerType;

		if($this->arParams['USE_PLAYLIST'] == 'Y' && $this->arParams['USE_PLAYLIST_AS_SOURCES'] !== 'Y')
		{
			$this->processPlaylist();
			if(!empty($this->arResult['VIDEOJS_PLAYLIST_PARAMS']))
			{
				$this->arResult['VIDEOJS_PARAMS']['playlistParams'] = $this->arResult['VIDEOJS_PLAYLIST_PARAMS'];
			}
		}

		if (!empty($this->warning) && $USER->IsAdmin() && !(defined ('ADMIN_SECTION') && ADMIN_SECTION === true) && $this->arParams['HIDE_ERRORS'] !== 'Y')
		{
			CComponentUtil::__ShowError($this->warning);
		}

		foreach($this->arResult['JS_FILES'] as $key => $file)
		{
			$this->arResult['JS_FILES'][$key] = CUtil::GetAdditionalFileURL($file);
		}
		foreach($this->arResult['CSS_FILES'] as $key => $file)
		{
			$this->arResult['CSS_FILES'][$key] = CUtil::GetAdditionalFileURL($file);
		}

		$this->includeComponentTemplate();

		return true;
	}

	/**
	 * Generate configs for wmv-player
	 */
	protected function processWmv()
	{
		$this->conf = array();
		$this->addJsVar($this->conf, 'file', $this->path, '');
		$this->addJsVar($this->conf, 'image', $this->arParams['PREVIEW'], '');

		$this->addJsVar($this->conf, 'width', $this->arParams['WIDTH']);
		$this->addJsVar($this->conf, 'height', $this->arParams['HEIGHT']);
		$this->addJsVar($this->conf, 'backcolor', $this->arParams['CONTROLS_BGCOLOR'], 'FFFFFF');
		$this->addJsVar($this->conf, 'frontcolor', $this->arParams['CONTROLS_COLOR'], '000000');
		$this->addJsVar($this->conf, 'lightcolor', $this->arParams['CONTROLS_OVER_COLOR'], '000000');
		$this->addJsVar($this->conf, 'screencolor', $this->arParams['SCREEN_COLOR'], '000000');

		$this->addJsVar($this->conf, 'shownavigation', ($this->arParams['SHOW_CONTROLS'] == 'Y'), true);
		$this->addJsVar($this->conf, 'showdigits', ($this->arParams['SHOW_DIGITS'] == 'Y'), true);

		$this->addJsVar($this->conf, 'autostart', ($this->arParams['AUTOSTART'] == 'Y'), false);
		$this->addJsVar($this->conf, 'repeat', $this->arParams['REPEAT'] != "none", 'false');
		$this->addJsVar($this->conf, 'volume', $this->arParams['VOLUME'], 80);
		$this->addJsVar($this->conf, 'bufferlength', $this->arParams['BUFFER_LENGTH'], 3);
		$this->addJsVar($this->conf, 'link', $this->arParams['DOWNLOAD_LINK'], '');
		$this->addJsVar($this->conf, 'linktarget', $this->arParams['DOWNLOAD_LINK_TARGET'], '_self');

		$this->addJsVar($this->conf, 'title', $this->arParams['FILE_TITLE']);
		$this->addJsVar($this->conf, 'duration', $this->arParams['FILE_DURATION']);
		$this->addJsVar($this->conf, 'author', $this->arParams['FILE_AUTHOR']);
		$this->addJsVar($this->conf, 'date', $this->arParams['FILE_DATE']);
		$this->addJsVar($this->conf, 'description', $this->arParams['FILE_DESCRIPTION']);

		// Append additional js vars
		$arWmvVars = explode("\n", trim($this->arParams['ADDITIONAL_WMVVARS']));
		for ($j = 0, $n = count($arWmvVars); $j < $n; $j++)
		{
			$pair = explode("=", trim($arWmvVars[$j]));
			if (count($pair) == 2 && $pair[0] <> '' && $pair[1] <> '')
				$this->addJsVar($this->conf, $pair[0], $pair[1]);
		}
		if ($this->arParams['WMODE_WMV'] == 'windowless')
			$this->addJsVar($this->conf, 'windowless', 'true', '');

		$this->conf['xaml'] = $this->__path.'/wmvplayer/wmvplayer.xaml';
		$this->arResult['WMV_CONFIG'] = $this->conf;
		if ($this->arParams['SHOW_CONTROLS'] == 'Y')
			$this->arResult['HEIGHT'] += 20;

		$this->arResult['USE_JS_PLAYLIST'] = ($this->arParams['USE_PLAYLIST'] == 'Y');
		$playlistConf = false;
		if ($this->arResult['USE_JS_PLAYLIST'])
		{
			$playlistConf = array();
			$this->addJsVar($playlistConf, 'format', $this->arParams['PLAYLIST_TYPE'], 'xspf');
			$this->addJsVar($playlistConf, 'size', $this->arParams['PLAYLIST_SIZE'], '180');
			$this->addJsVar($playlistConf, 'image_height', $this->arParams['PLAYLIST_PREVIEW_HEIGHT']);
			$this->addJsVar($playlistConf, 'image_width', $this->arParams['PLAYLIST_PREVIEW_WIDTH']);
			$this->addJsVar($playlistConf, 'position', $this->arParams['PLAYLIST'] == 'right' ? 'right' : 'bottom', 'right');
			$this->addJsVar($playlistConf, 'path', $this->path, '');
		}
		$this->arResult['PLAYLIST_CONFIG'] = $playlistConf;
	}

	/**
	 * Generate configs for jwplayer
	 */
	protected function processJwPlayer()
	{
		\Bitrix\Main\Page\Asset::getInstance()->addJs($this->__path . '/mediaplayer/jwplayer.js');
		$this->arResult['JS_FILES'][] = $this->__path.'/mediaplayer/jwplayer.js';
		$this->jwConfig = array(
			'file' => $this->path,
			'height' => $this->arParams['HEIGHT'],
			'width' => $this->arParams['WIDTH'],
			'dock' => true,
			'id' => $this->arResult['ID'],
			'controlbar' => $this->arParams['CONTROLBAR']
		);

		if ($this->arParams['USE_PLAYLIST'] == 'Y')
		{
			$this->jwConfig['players'] = array(
				array('type' => 'flash', 'src' => $this->jwFlashPath)
			);
			$this->addFlashVar($this->jwConfig, 'playlist', $this->arParams['PLAYLIST'], 'none');
			$this->addFlashVar($this->jwConfig, 'playlistsize', $this->arParams['PLAYLIST_SIZE'], '180');
		}
		else
		{
			if(mb_strpos($this->path, "youtu"))
			{
				$this->jwConfig['flashplayer'] = $this->jwFlashPath;
			}
			else
			{
				$this->jwConfig['players'] = array(
					array('type' => 'html5'),
					array('type' => 'flash', 'src' => $this->jwFlashPath)
				);
			}
		}

		$this->addFlashVar($this->jwConfig, 'image', $this->arParams['PREVIEW'], '');

		// Logo
		if ($this->arParams['LOGO'] != '' && $this->arParams['LOGO_POSITION'] != "none")
		{
			$this->addFlashVar($this->jwConfig, 'logo.position', $this->arParams['LOGO_POSITION']);
			$this->addFlashVar($this->jwConfig, 'logo.file', $this->arParams['LOGO']);
			$this->addFlashVar($this->jwConfig, 'logo.link', $this->arParams['LOGO_LINK']);
			$this->addFlashVar($this->jwConfig, 'logo.hide', 'false');
		}
		else
		{
			$this->addFlashVar($this->jwConfig, 'logo.hide', 'true');
		}

		// Skining
		$skinExt = self::getFileExtension($this->arParams['SKIN']);
		$skinName = mb_substr($this->arParams['SKIN'], 0, -mb_strlen($skinExt) - 1);

		if ($this->arParams['SKIN'] != '' && $this->arParams['SKIN'] != 'default')
		{
			if ($skinExt == 'swf' || $skinExt == 'zip')
			{
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $this->arParams['SKIN']))
				{
					$skin = $this->arParams['SKIN_PATH'] . $this->arParams['SKIN'];
				}
				elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $skinName . '/' . $this->arParams['SKIN']))
				{
					$skin = $this->arParams['SKIN_PATH'] . $skinName . '/' . $this->arParams['SKIN'];
				}
				else
				{
					$fname = mb_substr($this->arParams['SKIN'], 0, mb_strrpos($this->arParams['SKIN'], '.'));
					if ($skinExt == 'swf' && file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $fname . '.zip'))
						$skin = $this->arParams['SKIN_PATH'] . $fname . '.zip';
					else
						$skin = '';
				}
				$this->addFlashVar($this->jwConfig, 'skin', $skin);
			}
		}

		$this->addFlashVar($this->jwConfig, 'autostart', ($this->arParams['AUTOSTART'] == 'Y'? 'true' : ''), false);
		$this->addFlashVar($this->jwConfig, 'repeat', $this->arParams['REPEAT'], 'none');
		$this->addFlashVar($this->jwConfig, 'volume', $this->arParams['VOLUME'], 90);
		$this->addFlashVar($this->jwConfig, 'mute', (($this->arParams['MUTE'] ?? null) == 'Y'), false);
		$this->addFlashVar($this->jwConfig, 'shuffle', ($this->arParams['SHUFFLE'] == 'Y'), false);
		$this->addFlashVar($this->jwConfig, 'item', $this->arParams['START_ITEM'], '0');
		$this->addFlashVar($this->jwConfig, 'bufferlength', $this->arParams['BUFFER_LENGTH'], '1');

		// File info
		$this->addFlashVar($this->jwConfig, 'title', $this->arParams['FILE_TITLE']);
		$this->addFlashVar($this->jwConfig, 'duration', $this->arParams['FILE_DURATION']);
		$this->addFlashVar($this->jwConfig, 'author', $this->arParams['FILE_AUTHOR']);
		$this->addFlashVar($this->jwConfig, 'date', $this->arParams['FILE_DATE']);
		$this->addFlashVar($this->jwConfig, 'description', $this->arParams['FILE_DESCRIPTION']);

		// Append plugins
		if (is_array($this->arParams['PLUGINS']) && count($this->arParams['PLUGINS']) > 0)
		{
			$this->jwConfig['plugins'] = array();

			// Append plugins vars
			for ($i = 0, $l = count($this->arParams['PLUGINS']); $i < $l; $i++)
			{
				if ($this->arParams['PLUGINS'][$i] == '')
					continue;

				$plArray = array();
				$pluginName = preg_replace("/[^a-zA-Z0-9_-]/i", "_", trim($this->arParams['PLUGINS'][$i]));

				if (isset($this->arParams['PLUGINS_'.mb_strtoupper($pluginName)]))
				{
					$arFlashVars = explode("\n", trim($this->arParams['PLUGINS_'.mb_strtoupper($pluginName)]));
					for ($j = 0, $n = count($arFlashVars); $j < $n; $j++)
					{
						$pair = explode("=", trim($arFlashVars[$j]));
						if (count($pair) < 2 || $pair[0] == '' || $pair[1] == '')
							continue;
						$this->addFlashVar($plArray, $pair[0], $pair[1]);
					}
				}
				$this->jwConfig['plugins'][$this->arParams['PLUGINS'][$i]] = $plArray;
			}
		}
		// Append additional flashvars
		$arFlashVars = explode("\n", trim($this->arParams['ADDITIONAL_FLASHVARS']));
		for ($j = 0, $n = count($arFlashVars); $j < $n; $j++)
		{
			$pair = explode("=", trim($arFlashVars[$j]));
			if (count($pair) < 2 || $pair[0] == '' || $pair[1] == '')
				continue;
			$this->addFlashVar($this->jwConfig, $pair[0], $pair[1]);
		}

		/*if (strpos($path, "youtube.") !== false || strpos($path, "y2u.be") !== false)
			$this->arParams['PROVIDER'] = "youtube";*/

		if ($this->arParams['USE_PLAYLIST'] !== 'Y')
			$this->addFlashVar($this->jwConfig, 'provider', $this->arParams['PROVIDER']);

		if ($this->arParams['STREAMER'] <> '')
			$this->addFlashVar($this->jwConfig, 'streamer', $this->arParams['STREAMER']);

		$this->addFlashVar($this->jwConfig, 'abouttext', GetMessage('ABOUT_TEXT'), '');
		$this->addFlashVar($this->jwConfig, 'aboutlink', GetMessage('ABOUT_LINK'), '');
		if ($this->arParams['CONTENT_TYPE'])
			$this->addFlashVar($this->jwConfig, 'type', $this->arParams['CONTENT_TYPE'], '');

		$this->arResult['jwConfig'] = CUtil::PhpToJSObject($this->jwConfig);
		/*if ($USER->IsAdmin() && !(defined ('ADMIN_SECTION') && ADMIN_SECTION === true))
			CComponentUtil::__ShowError(GetMessage("JWPLAYER_DEPRECATED"));*/
	}

	/**
	 * Process one file. Return array on success or false if track should be removed from playlist
	 *
	 * @param $arTrack
	 * @return array|bool
	 */
	protected function processTrack (&$arTrack)
	{
		if ($this->arResult['STREAM'])
		{
			$this->warning .= GetMessage("PLAYLIST_STREAMING_VIDEO_NOT_SUPPORTED")."<br />";
			return false;
		}
		if (self::isYoutubeSource($arTrack['src']))
		{
			if($this->arParams['USE_PLAYLIST_AS_SOURCES'] === 'Y')
			{
				return false;
			}
			if ($this->arResult['AUDIO_FILE'])
			{
				$this->warning .= GetMessage("PLAYLIST_AUDIO_AND_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}
			$this->arResult['YOUTUBE'] = true;
			$arTrack['type'] = 'video/youtube';
			return $arTrack;
		}
		elseif (self::isVimeoSource($arTrack['src']))
		{
			if($this->arParams['USE_PLAYLIST_AS_SOURCES'] === 'Y')
			{
				return false;
			}
			if ($this->arResult['AUDIO_FILE'])
			{
				$this->warning .= GetMessage("PLAYLIST_AUDIO_AND_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}
			$this->arResult['VIMEO'] = true;
			$arTrack['type'] = 'video/vimeo';
			return $arTrack;
		}
		elseif (self::isStreamingSource($arTrack['src']))
		{
			if ($this->arResult['AUDIO_FILE'] || $this->arResult['YOUTUBE'] || $this->arResult['COMMON_VIDEO'])
			{
				$this->warning .= GetMessage("PLAYLIST_STREAMING_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}
			$arTrack['type'] = self::GetContentType($arTrack['src']);
			$this->arResult['STREAM'] = true;
			return $arTrack;
		}
		$arTrack['src'] = $this->findCorrectFile($arTrack['src'], $arTrack['type']);
		if (self::isAudioSource($arTrack['src']) || (mb_strpos($arTrack['type'], 'audio') === 0))
		{
			if ($this->arResult['YOUTUBE'] || $this->arResult['COMMON_VIDEO'])
			{
				$this->warning .= GetMessage("PLAYLIST_AUDIO_AND_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}

			if (mb_strpos($arTrack['type'], 'audio') !== 0)
				$arTrack['type'] = self::GetContentType($arTrack['src']);

			$this->arResult['AUDIO_FILE'] = true;
		}
		elseif (self::isWmvSource($arTrack['src'], $arTrack['type']))
		{
			if($this->arParams['USE_PLAYLIST_AS_SOURCES'] === 'Y')
			{
				return false;
			}
			$this->arResult['WMV'] = true;
		}
		elseif (mb_strpos($arTrack['type'], 'rtmp') === 0)
		{
			$this->arResult['FLASH'] = $this->arResult['STREAM'] = true;
		}
		elseif (mb_strpos($arTrack['type'], 'video') === 0)
		{
			if ($this->arResult['AUDIO_FILE'])
			{
				$this->warning .= GetMessage("PLAYLIST_AUDIO_AND_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}
			$this->arResult['COMMON_VIDEO'] = true;
			if (self::isFlashSource ($arTrack['src']) || $arTrack['type'] == 'video/x-flv')
				$this->arResult['FLASH'] = true;
		}
		else
		{
			$this->warning .= htmlspecialcharsbx($arTrack['src']).": ".GetMessage("PLAYLIST_FILE_NOT_FOUND")."<br />";
			return false;
		}
		// a dirty hack to make player play .mov files
		if ($arTrack['type'] == 'video/quicktime')
			$arTrack['type'] = 'video/mp4';
		return $arTrack;
	}

	/**
	 * Find skin and apply it
	 */
	protected function processSkin()
	{
		global $APPLICATION;
		$this->arResult['SKIN_JS'] = $this->arResult['SKIN_CSS'] = '';
		$this->arResult['SKIN_NAME'] = 'vjs-default-skin';
		$this->arParams['SKIN'] ??= null;
		if($this->arParams['SKIN'] != '' && $this->arParams['SKIN'] != 'default')
		{
			$skinExt = self::getFileExtension($this->arParams['SKIN']);
			$this->arResult['SKIN_NAME'] = mb_substr($this->arParams['SKIN'], 0, -mb_strlen($skinExt) - 1);

			if ($skinExt == 'css')
			{
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $this->arParams['SKIN']))
				{
					$this->arResult['SKIN_CSS'] = $this->arParams['SKIN_PATH'] . $this->arParams['SKIN'];
				}
				elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '/' . $this->arParams['SKIN']))
				{
					$this->arResult['SKIN_CSS'] = $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '/' . $this->arParams['SKIN'];
				}
				if ($this->arResult['SKIN_CSS'] != '')
				{
					$APPLICATION->setAdditionalCss($this->arResult['SKIN_CSS']);
					$this->arResult['CSS_FILES'][] = $this->arResult['SKIN_CSS'];
					if(file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '.js'))
					{
						$this->arResult['SKIN_JS'] = $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '.js';
					}
					elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '/' . $this->arParams['SKIN_NAME'] . '.js'))
					{
						$this->arResult['SKIN_JS'] = $this->arParams['SKIN_PATH'] . $this->arResult['SKIN_NAME'] . '/' . $this->arParams['SKIN_NAME'] . '.js';
					}
					if($this->arResult['SKIN_JS'] != '')
					{
						\Bitrix\Main\Page\Asset::getInstance()->addJs($this->arResult['SKIN_JS']);
						$this->arResult['JS_FILES'][] = $this->arResult['SKIN_JS'];
					}
					$this->arResult['SKIN_NAME'] = 'vjs-' . $this->arResult['SKIN_NAME'] . '-skin';
				}
			}
		}
	}

	/**
	 * Process playlist. Fill config, show popup for playlist edit
	 */
	protected function processPlaylist()
	{
		global $USER, $APPLICATION;
		\Bitrix\Main\Page\Asset::getInstance()->addJs(self::PLAYER_JS_PATH.'/videojs/videojs-playlist-dev.js');
		$this->arResult['JS_FILES'][] = self::PLAYER_JS_PATH.'/videojs/videojs-playlist-dev.js';
		$APPLICATION->setAdditionalCss(self::PLAYER_JS_PATH.'/videojs/videojs-playlist.css');
		$this->arResult['CSS_FILES'][] = self::PLAYER_JS_PATH.'/videojs/videojs-playlist.css';
		if (count($this->arResult['TRACKS']) > 1)
		{
			$this->arResult['VIDEOJS_PLAYLIST_PARAMS'] = array(
				'videos' => $this->arResult['TRACKS'],
				'playlist' => array(
					'hideSidebar' => false,
					'upNext' => false,
					'hideIcons' => false,
					'thumbnailSize' => $this->arParams['PLAYLIST_SIZE'],
					'items' => $this->arParams['PLAYLIST_NUMBER']
				)
			);
			if ($this->arParams['PLAYLIST_HIDE'] == 'Y')
				$this->arResult['VIDEOJS_PLAYLIST_PARAMS']['playlist']['hideSidebar'] = true;
			if (self::isMobile())
			{
				$this->arResult['VIDEOJS_PLAYLIST_PARAMS']['playlist']['mobile'] = true;
				if (self::isIOS())
				{
					foreach ($this->arResult['VIDEOJS_PLAYLIST_PARAMS']['videos'] as &$arTrack)
					{
						unset ($arTrack['thumbnail']);
					}
				}
			}
		}

		$playlistExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $this->path);
		if (!$playlistExists)
			$this->warning = GetMessage('INCORRECT_PLAYLIST');

		//Icons
		$bShowIcon = $USER->IsAuthorized();
		if ($bShowIcon && $this->path <> '')
		{
			$playlist_edit_url = $APPLICATION->GetPopupLink(
				array(
					"URL" => $this->__path . "/player_playlist_edit.php?lang=" . LANGUAGE_ID .
						"&site=" . $this->getSiteId() . "&back_url=" . urlencode($_SERVER['REQUEST_URI']) .
						"&path=" . urlencode($this->path) . "&contID=" . urlencode($this->arResult['ID']),
					"PARAMS" => array(
						'width' => '850',
						'height' => '400'
					)
				)
			);

			if (!$playlistExists)
				$this->warning .= '<br><a href="javascript:' . $playlist_edit_url . '">' . GetMessage("PLAYER_PLAYLIST_ADD") . '</a>';
			$arIcons = array(array(
				"URL" => 'javascript:' . $playlist_edit_url,
				"ICON" => "bx-context-toolbar-edit-icon",
				"TITLE" => ($playlistExists ? GetMessage("PLAYER_PLAYLIST_EDIT") : GetMessage("PLAYER_PLAYLIST_ADD")),
			));
			echo '<script>BX.ready(function(){if (typeof JCPopup === \'object\') {window.jsPopup_playlist = new JCPopup({suffix: "playlist", zIndex: 3000});}});</script>';
			$this->AddIncludeAreaIcons($arIcons);
		}
	}
}
