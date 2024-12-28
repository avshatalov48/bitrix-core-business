<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!\Bitrix\Main\Loader::includeModule('fileman'))
{
	return false;
}

class CBitrixPlayer extends CBitrixComponent
{
	protected $path;
	protected $warning = '';
	protected $conf;

	const YOUTUBE_MATCHER = '/^((?:https?:)?\/\/)?((?:www|m)\.)?(youtube(-nocookie)?\.com|youtu\.be)(\/(?:[\w-]+\?v=|embed\/|shorts\/|live\/|v\/)?)(?<id>[\w-]+)(\S+)?$/';
	const YOUTUBE_EMBEDDED = 'https://www.youtube-nocookie.com/embed/<id>';
	const VIMEO_MATCHER = '/^(?:(?:https?:)?\/\/)?(?:www.)?vimeo.com\/(.*\/)?(?<id>\d+)(.*)?/';
	const VIMEO_EMBEDDED = 'https://player.vimeo.com/video/<id>';

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

	public static function isYoutubeSource($path)
	{
		return (bool)preg_match(self::YOUTUBE_MATCHER, $path);
	}

	public static function isVimeoSource($path)
	{
		return (bool)preg_match(self::VIMEO_MATCHER, $path);
	}

	public static function isStreamingSource ($path)
	{
		$arStreamingExts = array('m3u8', 'ts');
		$ext = self::getFileExtension($path);
		if (in_array($ext, $arStreamingExts))
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
		global $USER;
		$this->arResult['PATH'] ??= null;

		if (isset($this->arParams['TYPE']))
		{
			$this->arResult['FILE_TYPE'] = $this->arParams['TYPE'];
		}

		$type = $this->arResult['FILE_TYPE'] ?? '';
		$this->path = $this->findCorrectFile($this->arParams['PATH'] ?? '', $type);
		$this->arResult['FILE_TYPE'] = $type;

		if (isset($this->arParams['PLAYER_ID']) && $this->arParams['PLAYER_ID'] <> '')
		{
			$this->arResult['ID'] = $this->arParams['PLAYER_ID'];
		}
		else
		{
			$this->arResult['ID'] = "bx_videojs_player_" . $this->getComponentId();
		}

		$this->arResult['STREAM'] = false;
		$this->arResult['COMMON_VIDEO'] = false;
		$this->arResult['AUDIO_FILE'] = false;
		$this->arResult['YOUTUBE'] = false;
		$this->arResult['VIMEO'] = false;

		$this->arResult['SKIN_NAME'] = 'vjs-default-skin';
		if (!empty($this->arParams['SKIN']) && $this->arParams['SKIN'] !== 'default')
		{
			if ($this->arParams['SKIN'] === 'timeline_player.css')
			{
				// Compatibility
				$this->arResult['SKIN_NAME'] = 'vjs-audio-wave-skin';
			}
			elseif ($this->arParams['SKIN'] === 'disk_player.css')
			{
				$this->arResult['SKIN_NAME'] = 'vjs-disk_player-skin';
			}
		}
		else if (!empty($this->arParams['SKIN_NAME']) && is_string($this->arParams['SKIN_NAME']))
		{
			$this->arResult['SKIN_NAME'] = $this->arParams['SKIN_NAME'];
		}

		$this->arResult['VIDEOJS_PARAMS'] = array(
			'autoplay' => false,
			'preload' => false,
			'controls' => true,
			'height' => $this->arParams['HEIGHT'],
			'width' => $this->arParams['WIDTH'],
			'fluid' => false,
			'skin' => $this->arResult['SKIN_NAME'],
		);

		if ($this->arParams['SIZE_TYPE'] === 'fluid')
		{
			$this->arResult['VIDEOJS_PARAMS']['fluid'] = true;
		}

		$this->arResult['PATH'] = $this->path;
		$this->arParams['USE_PLAYLIST'] ??= null;

		if ($this->arParams['USE_PLAYLIST'] == 'Y')
		{
			if (!empty($this->arParams['TRACKS']))
			{
				$this->arResult['TRACKS'] = $this->arParams['TRACKS'];
			}
			else
			{
				$this->arResult['TRACKS'] = self::parsePlaylist($this->arResult['PATH']);
			}

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

			if ($this->processTrack($arTrack))
			{
				$this->arResult['TRACKS'] = [$arTrack];
			}
		}

		if (count($this->arResult['TRACKS']) == 0)
		{
			CComponentUtil::__ShowError(GetMessage("NO_SUPPORTED_FILES"));
			return false;
		}
		elseif (count($this->arResult['TRACKS']) == 1)
		{
			$this->arResult['TRACKS'] = array (reset ($this->arResult['TRACKS']));
			$this->arResult['PATH'] = $this->arResult['TRACKS'][0]['src'];
			$this->arResult['FILE_TYPE'] = $this->arResult['TRACKS'][0]['type'];
		}
		else
		{
			$this->arResult['TRACKS'] = array_values($this->arResult['TRACKS']);
		}

		$this->arResult['VIDEOJS_PARAMS']['sources'] = $this->arResult['TRACKS'];

		$this->arParams['AUTOSTART'] ??= null;
		$this->arParams['REPEAT'] ??= null;

		if ($this->arResult['COMMON_VIDEO'] || $this->arResult['AUDIO_FILE'])
		{
			if ($this->arParams['AUTOSTART'] === "Y")
			{
				$this->arResult['VIDEOJS_PARAMS']['autoplay'] = true;
			}

			if (self::isMobile() && $this->arResult['YOUTUBE'])
			{
				$this->arResult['VIDEOJS_PARAMS']['autoplay'] = false;
			}

			if (($this->arParams['PRELOAD'] ?? null) === "Y")
			{
				$this->arResult['VIDEOJS_PARAMS']['preload'] = true;
			}
			if ($this->arParams['REPEAT'] == "always")
			{
				$this->arResult['VIDEOJS_PARAMS']['loop'] = true;
			}
		}

		if ($this->arResult['STREAM'])
		{
			$this->arParams['START_TIME'] = 0;
		}

		if(($this->arParams['AUTOSTART_ON_SCROLL'] ?? null) === 'Y')
		{
			$this->arResult['AUTOSTART_ON_SCROLL'] = 'Y';
			$this->arResult['VIDEOJS_PARAMS']['autostart'] = true;
			$this->arParams['START_TIME'] = 0;
		}

		$this->arResult['VOLUME'] = $this->arParams['VOLUME'] / 100;
		if (($this->arParams['MUTE'] ?? null) === "Y")
		{
			$this->arResult['VIDEOJS_PARAMS']['muted'] = true;
		}

		if (($this->arParams['SHOW_CONTROLS'] ?? null) === "N")
		{
			$this->arResult['VIDEOJS_PARAMS']['controls'] = false;
		}

		if (isset($this->arParams['PREVIEW']) && !empty($this->arParams['PREVIEW']) && !self::isIOS())
		{
			$this->arResult['VIDEOJS_PARAMS']['poster'] = $this->arParams['PREVIEW'];
		}

		if ($this->arParams['AUTOSTART'] === "Y")
		{
			$this->arParams['START_TIME'] = 0;
		}

		if($this->arParams['START_TIME'] > 0)
		{
			$this->arResult['VIDEOJS_PARAMS']['startTime'] = $this->arParams['START_TIME'];
		}

		$this->arResult['LAZYLOAD'] = false;
		if(($this->arParams['LAZYLOAD'] ?? null) === 'Y')
		{
			$this->arResult['LAZYLOAD'] = true;
			$this->arResult['VIDEOJS_PARAMS']['lazyload'] = true;
		}

		$this->arResult['PLAYER_TYPE'] = 'videojs';

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

		if (!empty($this->arResult['VIDEOJS_PLAYLIST_PARAMS']))
		{
			$this->setTemplateName('playlist');
		}
		elseif (empty($this->getTemplateName()) || $this->getTemplateName() === '.default')
		{
			if ($this->arResult['YOUTUBE'])
			{
				$this->includeComponentTemplate('youtube');

				return true;
			}
			else if ($this->arResult['VIMEO'])
			{
				$this->includeComponentTemplate('vimeo');

				return true;
			}
		}

		$this->includeComponentTemplate();

		return true;
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
			$this->arResult['YOUTUBE_EMBEDDED'] = preg_replace_callback(
				self::YOUTUBE_MATCHER,
				function ($matches) {
					return str_replace('<id>', $matches['id'], self::YOUTUBE_EMBEDDED);
				},
				$arTrack['src']
			);
			$arTrack['type'] = 'video/youtube';

			$options = [];
			if ($this->arParams['AUTOSTART'] === "Y" && !self::isMobile())
			{
				$options['autoplay'] = 1;
			}

			if ($this->arParams['SHOW_CONTROLS'] === "N")
			{
				$options['controls'] = 0;
			}

			if ($this->arParams['REPEAT'] == "always")
			{
				$options['loop'] = 1;
			}

			if (count($options) > 0)
			{
				$this->arResult['YOUTUBE_EMBEDDED'] = $this->arResult['YOUTUBE_EMBEDDED'] . '?' . http_build_query($options);
			}

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
			$this->arResult['VIMEO_EMBEDDED'] = preg_replace_callback(
				self::VIMEO_MATCHER,
				function ($matches) {
					return str_replace('<id>', $matches['id'], self::VIMEO_EMBEDDED);
				},
				$arTrack['src']
			);

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
		elseif (mb_strpos($arTrack['type'], 'rtmp') === 0)
		{
			$this->arResult['STREAM'] = true;
		}
		elseif (mb_strpos($arTrack['type'], 'video') === 0)
		{
			if ($this->arResult['AUDIO_FILE'])
			{
				$this->warning .= GetMessage("PLAYLIST_AUDIO_AND_VIDEO_NOT_SUPPORTED")."<br />";
				return false;
			}
			$this->arResult['COMMON_VIDEO'] = true;
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
	 * Process playlist. Fill config, show popup for playlist edit
	 */
	protected function processPlaylist()
	{
		global $USER, $APPLICATION;

		if (count($this->arResult['TRACKS']) > 1)
		{
			$this->arResult['VIDEOJS_PLAYLIST_PARAMS'] = array(
				'videos' => $this->arResult['TRACKS'],
				'playlist' => [],
			);
		}

		$playlistExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $this->path);
		if (!$playlistExists)
		{
			$this->warning = GetMessage('INCORRECT_PLAYLIST');
		}

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

			$this->AddIncludeAreaIcons($arIcons);
		}
	}
}
