<?php

class CMailTools
{
	public  $aMatches = [];
	protected  $pcre_backtrack_limit = false;
	protected  $server_name = null;
	protected  $maxFileSize = 0;

	public static function IsEightBit($str)
	{
		$len = mb_strlen($str);
		for ($i = 0; $i < $len; $i++)
		{
			if (ord(mb_substr($str, $i, 1)) >> 7)
			{
				return true;
			}
		}
		return false;
	}

	public static function EncodeMimeString($text, $charset)
	{
		if (!CMailTools::IsEightBit($text))
		{
			return $text;
		}

		$maxl = intval((76 - mb_strlen($charset) + 7) * 0.4);

		$res = '';
		$eol = \Bitrix\Main\Mail\Mail::getMailEol();
		$len = mb_strlen($text);
		for ($i = 0; $i < $len; $i += $maxl)
		{
			if ($i > 0)
			{
				$res .= $eol . "\t";
			}
			$res .= '=?' . $charset . '?B?' . base64_encode(mb_substr($text, $i, $maxl)) . '?=';
		}
		return $res;
	}

	public static function EncodeSubject($text, $charset)
	{
		return '=?' . $charset . '?B?' . base64_encode($text) . '?=';
	}

	public static function EncodeHeaderFrom($text, $charset)
	{
		$i = strlen($text);
		while ($i > 0)
		{
			if (ord($text[$i - 1]) >> 7)
			{
				break;
			}
			$i--;
		}
		if ($i == 0)
		{
			return $text;
		}
		else
		{
			return '=?' . $charset . '?B?' . base64_encode(substr($text, 0, $i)) . '?=' . substr($text, $i);
		}
	}

	protected function __replace_img($matches)
	{
		$io = CBXVirtualIo::GetInstance();
		$src = $matches[3];

		if ($src == '')
		{
			return $matches[0];
		}

		if (array_key_exists($src, $this->aMatches))
		{
			$uid = $this->aMatches[$src]['ID'];
			return $matches[1] . $matches[2] . 'cid:' . $uid . $matches[4] . $matches[5];
		}

		$filePath = $io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'] . $src);
		if (!file_exists($filePath))
		{
			return $matches[0];
		}

		if (
			$this->maxFileSize > 0
			&& filesize($filePath) > $this->maxFileSize
		)
		{
			return $matches[0];
		}

		$image = new \Bitrix\Main\File\Image($filePath);
		$info = $image->getInfo();
		if (!$info)
		{
			return $matches[0];
		}

		if (function_exists('image_type_to_mime_type'))
		{
			$contentType = image_type_to_mime_type($info->getFormat());
		}
		else
		{
			$contentType = CMailTools::ImageTypeToMimeType($info->getFormat());
		}

		$uid = uniqid(md5($src));

		$this->aMatches[$src] = [
			'SRC' => $src,
			'PATH' => $filePath,
			'CONTENT_TYPE' => $contentType,
			'DEST' => bx_basename($src),
			'ID' => $uid,
		];

		return $matches[1] . $matches[2] . 'cid:' . $uid . $matches[4] . $matches[5];
	}

	public function ReplaceHrefs($text)
	{
		if ($this->pcre_backtrack_limit === false)
		{
			$this->pcre_backtrack_limit = intval(ini_get('pcre.backtrack_limit'));
		}
		$text_len = strlen($text);
		$text_len++;
		if ($this->pcre_backtrack_limit < $text_len)
		{
			@ini_set('pcre.backtrack_limit', $text_len);
			$this->pcre_backtrack_limit = intval(ini_get('pcre.backtrack_limit'));
		}

		if (!isset($this->server_name))
		{
			$this->server_name = COption::GetOptionString('main', 'server_name', '');
		}

		if ($this->server_name != '')
		{
			$text = preg_replace(
				"/(<a\\s[^>]*?(?<=\\s)href\\s*=\\s*)([\"'])(\\/.*?)(\\2)(\\s.+?>|\\s*>)/is",
				"\\1\\2http://" . $this->server_name . "\\3\\4\\5",
				$text
			);
		}

		return $text;
	}

	public function ReplaceImages($text)
	{
		if ($this->pcre_backtrack_limit === false)
		{
			$this->pcre_backtrack_limit = intval(ini_get('pcre.backtrack_limit'));
		}
		$text_len = strlen($text);
		$text_len++;
		if ($this->pcre_backtrack_limit < $text_len)
		{
			@ini_set('pcre.backtrack_limit', $text_len);
			$this->pcre_backtrack_limit = intval(ini_get('pcre.backtrack_limit'));
		}
		$this->maxFileSize = intval(COption::GetOptionInt('subscribe', 'max_file_size'));
		$this->aMatches = [];
		$text = preg_replace_callback(
			"/(<img\\s[^>]*?(?<=\\s)src\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			[$this, '__replace_img'],
			$text
		);
		$text = preg_replace_callback(
			"/(background-image\\s*:\\s*url\\s*\\()([\"']?)(.*?)(\\2)(\\s*\\);)/is",
			[$this, '__replace_img'],
			$text
		);
		$text = preg_replace_callback(
			"/(<td\\s[^>]*?(?<=\\s)background\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			[$this, '__replace_img'],
			$text
		);
		$text = preg_replace_callback(
			"/(<table\\s[^>]*?(?<=\\s)background\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			[$this, '__replace_img'],
			$text
		);
		return $text;
	}

	public static function ImageTypeToMimeType($type)
	{
		static $aTypes = [
			1 => 'image/gif',
			2 => 'image/jpeg',
			3 => 'image/png',
			4 => 'application/x-shockwave-flash',
			5 => 'image/psd',
			6 => 'image/bmp',
			7 => 'image/tiff',
			8 => 'image/tiff',
			9 => 'application/octet-stream',
			10 => 'image/jp2',
			11 => 'application/octet-stream',
			12 => 'application/octet-stream',
			13 => 'application/x-shockwave-flash',
			14 => 'image/iff',
			15 => 'image/vnd.wap.wbmp',
			16 => 'image/xbm',
		];
		if (isset($aTypes[$type]))
		{
			return $aTypes[$type];
		}
		else
		{
			return 'application/octet-stream';
		}
	}
}
