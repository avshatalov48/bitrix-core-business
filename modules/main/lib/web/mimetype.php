<?php
namespace Bitrix\Main\Web;

use Bitrix\Main\IO;

final class MimeType
{
	protected static $mimes = [
		'hqx' => 'application/mac-binhex40',
		'cpt' => 'application/mac-compactpro',
		'csv' => 'text/x-comma-separated-values',
		'bin' => 'application/octet-stream',
		'dms' => 'application/octet-stream',
		'lha' => 'application/octet-stream',
		'lzh' => 'application/octet-stream',
		'exe' => 'application/octet-stream',
		'class' => 'application/octet-stream',
		'psd' => 'application/x-photoshop',
		'so' => 'application/octet-stream',
		'sea' => 'application/octet-stream',
		'dll' => 'application/octet-stream',
		'oda' => 'application/oda',
		'pdf' => 'application/pdf',
		'ai' => 'application/pdf',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',
		'smi' => 'application/smil',
		'smil' => 'application/smil',
		'mif' => 'application/vnd.mif',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'wbxml' => 'application/wbxml',
		'wmlc' => 'application/wmlc',
		'dcr' => 'application/x-director',
		'dir' => 'application/x-director',
		'dxr' => 'application/x-director',
		'dvi' => 'application/x-dvi',
		'gtar' => 'application/x-gtar',
		'gz' => 'application/x-gzip',
		'gzip' => 'application/x-gzip',
		'php' => 'application/x-httpd-php',
		'php4' => 'application/x-httpd-php',
		'php3' => 'application/x-httpd-php',
		'phtml' => 'application/x-httpd-php',
		'phps' => 'application/x-httpd-php-source',
		'js' => 'application/javascript',
		'swf' => 'application/x-shockwave-flash',
		'sit' => 'application/x-stuffit',
		'tar' => 'application/x-tar',
		'tgz' => 'application/x-tar',
		'z' => 'application/x-compress',
		'xhtml' => 'application/xhtml+xml',
		'xht' => 'application/xhtml+xml',
		'zip' => 'application/x-zip',
		'rar' => 'application/x-rar',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'mpga' => 'audio/mpeg',
		'mp2' => 'audio/mpeg',
		'mp3' => 'audio/mpeg',
		'aif' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'aifc' => 'audio/x-aiff',
		'ram' => 'audio/x-pn-realaudio',
		'rm' => 'audio/x-pn-realaudio',
		'rpm' => 'audio/x-pn-realaudio-plugin',
		'ra' => 'audio/x-realaudio',
		'rv' => 'video/vnd.rn-realvideo',
		'wav' => 'audio/x-wav',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpe' => 'image/jpeg',
		'png' => 'image/png',
		'webp' => 'image/webp',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'css' => 'text/css',
		'html' => 'text/html',
		'htm' => 'text/html',
		'shtml' => 'text/html',
		'txt' => 'text/plain',
		'text' => 'text/plain',
		'log' => 'text/plain',
		'rtx' => 'text/richtext',
		'rtf' => 'text/rtf',
		'xml' => 'application/xml',
		'xsl' => 'application/xml',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',
		'avi' => 'video/x-msvideo',
		'movie' => 'video/x-sgi-movie',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dot' => 'application/msword',
		'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'word' => 'application/msword',
		'xl' => 'application/excel',
		'eml' => 'message/rfc822',
		'json' => 'application/json',
		'pem' => 'application/x-x509-user-cert',
		'p10' => 'application/x-pkcs10',
		'p12' => 'application/x-pkcs12',
		'p7a' => 'application/x-pkcs7-signature',
		'p7c' => 'application/pkcs7-mime',
		'p7m' => 'application/pkcs7-mime',
		'p7r' => 'application/x-pkcs7-certreqresp',
		'p7s' => 'application/pkcs7-signature',
		'crt' => 'application/x-x509-ca-cert',
		'crl' => 'application/pkix-crl',
		'der' => 'application/x-x509-ca-cert',
		'kdb' => 'application/octet-stream',
		'pgp' => 'application/pgp',
		'gpg' => 'application/gpg-keys',
		'sst' => 'application/octet-stream',
		'csr' => 'application/octet-stream',
		'rsa' => 'application/x-pkcs7',
		'cer' => 'application/pkix-cert',
		'3g2' => 'video/3gpp2',
		'3gp' => 'video/3gp',
		'mp4' => 'video/mp4',
		'm4a' => 'audio/x-m4a',
		'f4v' => 'video/mp4',
		'webm' => 'video/webm',
		'aac' => 'audio/x-acc',
		'm4u' => 'application/vnd.mpegurl',
		'm3u' => 'text/plain',
		'xspf' => 'application/xspf+xml',
		'vlc' => 'application/videolan',
		'wmv' => 'video/x-ms-wmv',
		'au' => 'audio/x-au',
		'ac3' => 'audio/ac3',
		'flac' => 'audio/x-flac',
		'ogg' => 'audio/ogg',
		'kmz' => 'application/vnd.google-earth.kmz',
		'kml' => 'application/vnd.google-earth.kml+xml',
		'ics' => 'text/calendar',
		'zsh' => 'text/x-scriptzsh',
		'7zip' => 'application/x-7z-compressed',
		'cdr' => 'application/cdr',
		'wma' => 'audio/x-ms-wma',
		'jar' => 'application/java-archive',
		'sketch' => 'application/octet-stream',
		'vsd' => 'application/vnd.ms-visio',
		'vsdx' => 'application/vnd.ms-visio.drawing',
		'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
		'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
		'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
		'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
		'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
		'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
		'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
		'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
		'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
		'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
		'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'fb2' => 'application/xml',
		'djvu' => 'image/vnd.djvu',
		'epub' => 'application/epub+zip',
		'msg' => 'message/rfc822',
		'ott' => 'application/vnd.oasis.opendocument.text-template',
		'otp' => 'application/vnd.oasis.opendocument.presentation-template',
		'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
		'odt' => 'application/vnd.oasis.opendocument.text',
		'odp' => 'application/vnd.oasis.opendocument.presentation',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'xodt' => 'application/vnd.collabio.xodocuments.document',
		'7z' => 'application/x-7z-compressed',
		'bz2' => 'application/x-bzip2',
		'mp4v' => 'video/mp4',
		'mpg4' => 'video/mp4',
		'ogv' => 'video/ogg',
		'flv' => 'video/x-flv',
		'mkv' => 'video/x-matroska',
		'm4v' => 'video/x-m4v',
		'h264' => 'video/h264',
		'sql' => 'text/plain',
		'pl' => 'text/plain',
		'sh' => 'text/plain',
		'ttf' => 'application/x-font-ttf',
		'otf' => 'application/vnd.ms-opentype',
		'eot' => 'application/vnd.ms-fontobject',
		'woff' => 'application/font-woff',
		'pfa' => 'application/x-font-type1',
		'xla' => "application/vnd.ms-excel",
		'xlb' => 'application/vnd.ms-excel',
		'xlc' => 'application/vnd.ms-excel',
		'xll' => 'application/vnd.ms-excel',
		'xlm' => 'application/vnd.ms-excel',
		'xlt' => 'application/vnd.ms-excel',
		'xlw' => 'application/vnd.ms-excel',
		'dbf' => 'application/vnd.ms-excel',
		'm1v' => 'video/mpeg',
		'm2v' => 'video/mpeg',
		'jpgv' => 'video/jpeg',
		'dvb' => 'video/vnd.dvb.file',
		'fvt' => 'video/vnd.fvt',
		'mxu' => 'video/vnd.mpegurl',
		'pyv' => 'video/vnd.ms-playready.media.pyv',
		'uvu' => 'video/vnd.uvvu.mp4',
		'uvvu' => 'video/vnd.uvvu.mp4',
		'viv' => 'video/vnd.vivo',
		'fli' => 'video/x-fli',
		'mk3d' => 'video/x-matroska',
		'mks' => 'video/x-matroska',
		'mng' => 'video/x-mng',
		'asf' => 'video/x-ms-asf',
		'asx' => 'video/x-ms-asf',
		'vob' => 'video/x-ms-vob',
		'wm' => 'video/x-ms-wm',
		'wmx' => 'video/x-ms-wmx',
		'wvx' => 'video/x-ms-wvx',
		'smv' => 'video/x-smv',
		'mp2a' => 'audio/mpeg',
		'm2a' => 'audio/mpeg',
		'm3a' => 'audio/mpeg',
		'oga' => 'audio/ogg',
		'spx' => 'audio/ogg',
		'weba' => 'audio/webm',
		'm3u8' => 'application/vnd.apple.mpegurl',
		'ts' => 'video/MP2T',
	];

	public static function getMimeTypeList()
	{
		return self::$mimes;
	}

	public static function getExtensionByMimeType(string $mimeType): ?string
	{
		$extension = array_search($mimeType, self::$mimes, true);

		return $extension ?: null;
	}

	public static function getByFileExtension($extension)
	{
		$extension = strtolower($extension);
		if (isset(self::$mimes[$extension]))
		{
			return self::$mimes[$extension];
		}

		return 'application/octet-stream';
	}

	public static function getByFilename($filename)
	{
		return self::getByFileExtension(getFileExtension($filename));
	}

	public static function getByFilePath($filePath)
	{
		$file = new IO\File($filePath);

		return $file->getContentType();
	}

	public static function getByContent($content)
	{
		$fileInfo = new \Finfo(FILEINFO_MIME_TYPE);
		$mimeType = $fileInfo->buffer($content);

		return $mimeType?: 'application/octet-stream';
	}

	public static function isImage($mime)
	{
		// Attributes are possible: image/jpeg; charset=ISO-8859-1
		$parts = explode(';', (string)$mime);
		$mime = trim($parts[0]);

		return preg_match('#^image/[a-z0-9.-]+$#i', $mime);
	}

	public static function normalize($contentType)
	{
		if (!is_string($contentType))
		{
			return 'application/octet-stream';
		}

		$ct = strtolower($contentType);
		$ct = str_replace(array("\r", "\n", "\0"), "", $ct);

		// We don't need attributes: image/jpeg; charset=ISO-8859-1
		$parts = explode(';', $ct);
		$ct = trim($parts[0]);

		if ($ct == '')
		{
			$ct = 'application/octet-stream';
		}
		elseif (str_contains($ct, "excel"))
		{
			$ct = "application/vnd.ms-excel";
		}
		elseif (str_contains($ct, "word") && !str_contains($ct, "vnd.openxmlformats"))
		{
			$ct = "application/msword";
		}
		elseif ($ct == 'image/pjpeg' || $ct == 'image/jpg')
		{
			$ct = 'image/jpeg';
		}

		return $ct;
	}
}
