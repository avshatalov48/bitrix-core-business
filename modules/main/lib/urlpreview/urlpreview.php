<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\HttpHeaders;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\IpAddress;
use Bitrix\Main\Web\Http\Response;

class UrlPreview
{
	const SIGN_SALT = 'url_preview';
	const USER_AGENT = 'Bitrix link preview';
	/** @var int Maximum allowed length of the description. */
	const MAX_DESCRIPTION = 500;

	const IFRAME_MAX_WIDTH = 640;
	const IFRAME_MAX_HEIGHT = 340;

	protected static $trustedHosts = [
		'youtube.com' => 'youtube.com',
		'youtu.be' => 'youtu.be',
		'vimeo.com' => 'vimeo.com',
		'rutube.ru' => 'rutube.ru',
		'facebook.com' => 'facebook.com',
		'fb.watch' => 'fb.watch',
		'vk.com' => 'vk.com',
		'instagram.com' => 'instagram.com',
	];

	/**
	 * Returns associated metadata for the specified URL
	 *
	 * @param string $url URL.
	 * @param bool $addIfNew Should metadata be fetched and saved, if not found in database.
	 * @param bool $reuseExistingMetadata Allow reading of the cached metadata.
	 * @return array|false Metadata for the URL if found, or false otherwise.
	 */
	public static function getMetadataByUrl($url, $addIfNew = true, $reuseExistingMetadata = true)
	{
		if (!static::isEnabled())
		{
			return false;
		}

		$url = static::normalizeUrl($url);
		if ($url == '')
		{
			return false;
		}

		if ($reuseExistingMetadata)
		{
			if ($metadata = UrlMetadataTable::getByUrl($url))
			{
				if ($metadata['TYPE'] === UrlMetadataTable::TYPE_TEMPORARY && $addIfNew)
				{
					$metadata = static::resolveTemporaryMetadata($metadata['ID']);
					return $metadata;
				}
				if ($metadata['TYPE'] !== UrlMetadataTable::TYPE_STATIC
					|| !isset($metadata['DATE_EXPIRE'])
					|| $metadata['DATE_EXPIRE']->getTimestamp() > time()
				)
				{
					return $metadata;
				}
				if (static::refreshMetadata($metadata))
				{
					return $metadata;
				}
			}
		}

		if (!$addIfNew)
		{
			return false;
		}

		$metadataId = static::reserveIdForUrl($url);
		$metadata = static::fetchUrlMetadata($url);
		if (is_array($metadata) && !empty($metadata))
		{
			$result = UrlMetadataTable::update($metadataId, $metadata);
			$metadata['ID'] = $result->getId();
			return $metadata;
		}

		return false;
	}

	/**
	 * Returns html code for url preview
	 *
	 * @param array $userField Userfield's value.
	 * @param array $userFieldParams Userfield's parameters.
	 * @param string $cacheTag Cache tag for returned preview (out param).
	 * @param bool $edit Show method build preview for editing the userfield.
	 * @return string HTML code for the preview.
	 */
	public static function showView($userField, $userFieldParams, &$cacheTag, $edit = false)
	{
		global $APPLICATION;
		$edit = !!$edit;
		$cacheTag = '';

		if (!static::isEnabled())
		{
			return null;
		}

		$metadataId = (int)$userField['VALUE'][0];
		$metadata = false;
		if ($metadataId > 0)
		{
			$metadata = UrlMetadataTable::getById($metadataId)->fetch();
			if (isset($metadata['TYPE']) && $metadata['TYPE'] == UrlMetadataTable::TYPE_TEMPORARY)
			{
				$metadata = static::resolveTemporaryMetadata($metadata['ID']);
			}
		}

		if (is_array($metadata))
		{
			$fullUrl = static::unfoldShortLink($metadata['URL']);
			if ($metadata['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC)
			{
				$routeRecord = Router::dispatch(new Uri($fullUrl));

				if (isset($routeRecord['MODULE']) && Loader::includeModule($routeRecord['MODULE']))
				{
					$className = $routeRecord['CLASS'];
					$routeRecord['PARAMETERS']['URL'] = $metadata['URL'];
					$parameters = $routeRecord['PARAMETERS'];

					if ($edit && (!method_exists($className, 'checkUserReadAccess') || !$className::checkUserReadAccess($parameters, static::getCurrentUserId())))
					{
						return null;
					}

					if (method_exists($className, 'buildPreview'))
					{
						$metadata['HANDLER'] = $routeRecord;
						$metadata['HANDLER']['BUILD_METHOD'] = 'buildPreview';
					}

					if (method_exists($className, 'getCacheTag'))
					{
						$cacheTag = $className::getCacheTag();
					}
				}
				elseif (!$edit)
				{
					return null;
				}
			}
		}
		elseif (!$edit)
		{
			return null;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:main.urlpreview',
			'',
			array(
				'USER_FIELD' => $userField,
				'METADATA' => is_array($metadata) ? $metadata : [],
				'PARAMS' => $userFieldParams,
				'EDIT' => ($edit ? 'Y' : 'N'),
				'CHECK_ACCESS' => ($edit ? 'Y' : 'N'),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Returns html code for url preview edit form
	 *
	 * @param array $userField Userfield's value.
	 * @param array $userFieldParams Userfield's parameters.
	 * @return string HTML code for the preview.
	 */
	public static function showEdit($userField, $userFieldParams)
	{
		return static::showView($userField, $userFieldParams, $cacheTag, true);
	}

	/**
	 * Checks if metadata for the provided url is already fetched and cached.
	 *
	 * @param string $url Document's URL.
	 * @return bool True if metadata for the url is located in database, false otherwise.
	 */
	public static function isUrlCached($url)
	{
		$url = static::normalizeUrl($url);
		if ($url == '')
		{
			return false;
		}

		return (static::isUrlLocal(new Uri($url)) || !!UrlMetadataTable::getByUrl($url));
	}

	/**
	 * If url is remote - returns metadata for this url. If url is local - checks current user access to the entity
	 * behind the url, and returns html preview for this entity.
	 *
	 * @param string $url Document's URL.
	 * @param bool $addIfNew Should method fetch and store metadata for the document, if it is not found in database.
	 * @params bool $reuseExistingMetadata Allow reading of the cached metadata.
	 * @return array|false Metadata for the document, or false if metadata could not be fetched/parsed.
	 */
	public static function getMetadataAndHtmlByUrl($url, $addIfNew = true, $reuseExistingMetadata = true)
	{
		$metadata = static::getMetadataByUrl($url, $addIfNew, $reuseExistingMetadata);
		if ($metadata === false)
		{
			return false;
		}

		if ($metadata['TYPE'] == UrlMetadataTable::TYPE_STATIC || $metadata['TYPE'] == UrlMetadataTable::TYPE_FILE)
		{
			return $metadata;
		}
		elseif ($metadata['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC)
		{
			if ($preview = static::getDynamicPreview($url))
			{
				$metadata['HTML'] = $preview;
				return $metadata;
			}

		}

		return false;
	}

	/**
	 * Returns stored metadata for array of IDs
	 *
	 * @param array $ids Array of record's IDs.
	 * @param bool $checkAccess Should method check current user's access to the internal entities, or not.
	 * @params int $userId. Id of the users to check access. If == 0, will check access for current user.
	 * @return array|false Array with provided IDs as the keys.
	 */
	public static function getMetadataAndHtmlByIds(array $ids, $checkAccess = true, $userId = 0)
	{
		if (!static::isEnabled())
		{
			return false;
		}

		$result = [];

		$queryResult = UrlMetadataTable::getList([
			'filter' => [
				'ID' => $ids,
				'!=TYPE' => UrlMetadataTable::TYPE_TEMPORARY,
			]
		]);

		while ($metadata = $queryResult->fetch())
		{
			if ($metadata['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC)
			{
				$metadata['HTML'] = static::getDynamicPreview($metadata['URL'], $checkAccess, $userId);
				if ($metadata['HTML'] === false)
				{
					continue;
				}
			}
			if ($metadata['TYPE'] == UrlMetadataTable::TYPE_STATIC
				&& isset($metadata['DATE_EXPIRE'])
				&& $metadata['DATE_EXPIRE']->getTimestamp() <= time()
			)
			{
				$refreshResult = static::refreshMetadata($metadata);
				if (!$refreshResult)
				{
					continue;
				}
			}
			$result[$metadata['ID']] = $metadata;
		}

		return $result;
	}

	public static function getMetadataByIds(array $ids)
	{
		if (!static::isEnabled())
		{
			return false;
		}

		$result = [];

		$queryResult = UrlMetadataTable::getList([
			'filter' => [
				'ID' => $ids,
				'!=TYPE' => UrlMetadataTable::TYPE_TEMPORARY,
			]
		]);

		while ($metadata = $queryResult->fetch())
		{
			if ($metadata['TYPE'] == UrlMetadataTable::TYPE_STATIC
				&& isset($metadata['DATE_EXPIRE'])
				&& $metadata['DATE_EXPIRE']->getTimestamp() <= time()
			)
			{
				$refreshResult = static::refreshMetadata($metadata);
				if (!$refreshResult)
				{
					continue;
				}
			}
			$result[$metadata['ID']] = $metadata;
		}

		return $result;
	}

	/**
	 * Creates temporary record for url
	 *
	 * @param string $url URL for which temporary record should be created.
	 * @return int Temporary record's id.
	 */
	public static function reserveIdForUrl($url)
	{
		if ($metadata = UrlMetadataTable::getByUrl($url))
		{
			$id = $metadata['ID'];
		}
		else
		{
			$result = UrlMetadataTable::add(array(
					'URL' => $url,
					'TYPE' => UrlMetadataTable::TYPE_TEMPORARY
			));
			$id = $result->getId();
		}

		return $id;
	}

	/**
	 * Fetches and stores metadata for temporary record, created by UrlPreview::reserveIdForUrl. If metadata could
	 * not be fetched, deletes record.
	 * @param int $id Metadata record's id.
	 * @param bool $checkAccess Should method check current user's access to the entity, or not.
	 * @params int $userId. Id of the users to check access. If == 0, will check access for current user.
	 * @return array|false Metadata if fetched, false otherwise.
	 */
	public static function resolveTemporaryMetadata($id, $checkAccess = true, $userId = 0)
	{
		$metadata = UrlMetadataTable::getRowById($id);
		if (!is_array($metadata))
		{
			return false;
		}

		if ($metadata['TYPE'] == UrlMetadataTable::TYPE_TEMPORARY)
		{
			$metadata['URL'] = static::normalizeUrl($metadata['URL']);
			$metadata = static::fetchUrlMetadata($metadata['URL']);
			if ($metadata === false)
			{
				UrlMetadataTable::delete($id);
				return false;
			}

			UrlMetadataTable::update($id, $metadata);
			return $metadata;
		}
		elseif ($metadata['TYPE'] == UrlMetadataTable::TYPE_STATIC || $metadata['TYPE'] == UrlMetadataTable::TYPE_FILE)
		{
			return $metadata;
		}
		elseif ($metadata['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC)
		{
			if ($preview = static::getDynamicPreview($metadata['URL'], $checkAccess, $userId))
			{
				$metadata['HTML'] = $preview;
				return $metadata;
			}
		}

		return false;
	}

	protected static function refreshMetadata(array &$metadata): bool
	{
		if ($metadata['TYPE'] !== UrlMetadataTable::TYPE_STATIC)
		{
			return false;
		}
		$url = static::normalizeUrl($metadata['URL']);
		$refreshedMetadata = static::fetchUrlMetadata($url);
		if (!$refreshedMetadata)
		{
			return false;
		}
		if ($metadata['ID'])
		{
			UrlMetadataTable::update($metadata['ID'], $refreshedMetadata);
			$refreshedMetadata['ID'] = $metadata['ID'];
		}
		$metadata = $refreshedMetadata;

		return true;
	}

	/**
	 * Returns HTML code for the dynamic (internal url) preview.
	 * @param string $url URL of the internal document.
	 * @param bool $checkAccess Should method check current user's access to the entity, or not.
	 * @params int $userId. Id of the users to check access. If userId == 0, will check access for current user.
	 * @return string|false HTML code of the preview, or false if case of any errors (including access denied)/
	 */
	public static function getDynamicPreview($url, $checkAccess = true, $userId = 0)
	{
		$routeRecord = Router::dispatch(new Uri(static::unfoldShortLink($url)));
		if ($routeRecord === false)
		{
			return false;
		}

		if (isset($routeRecord['MODULE']) && Loader::includeModule($routeRecord['MODULE']))
		{
			$className = $routeRecord['CLASS'];
			$parameters = $routeRecord['PARAMETERS'];
			$parameters['URL'] = $url;

			if ($userId == 0)
			{
				$userId = static::getCurrentUserId();
			}

			if ($checkAccess && (!method_exists($className, 'checkUserReadAccess') || $userId == 0 || !$className::checkUserReadAccess($parameters, $userId)))
				return false;

			if (method_exists($className, 'buildPreview'))
			{
				$preview = $className::buildPreview($parameters);
				return ($preview <> '' ? $preview : false);
			}
		}
		return false;
	}

	/**
	 * Returns attach for the IM message with the requested internal entity content.
	 * @param string $url URL of the internal document.
	 * @param bool $checkAccess Should method check current user's access to the entity, or not.
	 * @params int $userId. Id of the users to check access. If userId == 0, will check access for current user.
	 * @return \CIMMessageParamAttach | false
	 */
	public static function getImAttach($url, $checkAccess = true, $userId = 0)
	{
		return self::getUrlInfoFromExternal($url, 'getImAttach', $checkAccess, $userId);
	}

	/**
	 * @param $url
	 * @param bool $checkAccess
	 * @param int $userId
	 * @return \Bitrix\Im\V2\Entity\Url\RichData | false
	 */
	public static function getImRich($url, $checkAccess = true, $userId = 0)
	{
		return self::getUrlInfoFromExternal($url, 'getImRich', $checkAccess, $userId);
	}

	/**
	 * Returns true if current user has read access to the content behind internal url.
	 * @param string $url URL of the internal document.
	 * @params int $userId. Id of the users to check access. If userId == 0, will check access for current user.
	 * @return bool True if current user has read access to the main entity of the document, or false otherwise.
	 */
	public static function checkDynamicPreviewAccess($url, $userId = 0)
	{
		$routeRecord = Router::dispatch(new Uri(static::unfoldShortLink($url)));
		if ($routeRecord === false)
		{
			return false;
		}

		if (isset($routeRecord['MODULE']) && Loader::includeModule($routeRecord['MODULE']))
		{
			$className = $routeRecord['CLASS'];
			$parameters = $routeRecord['PARAMETERS'];

			if ($userId == 0)
			{
				$userId = static::getCurrentUserId();
			}

			return (method_exists($className, 'checkUserReadAccess') && $userId > 0 && $className::checkUserReadAccess($parameters, $userId));
		}
		return false;
	}

	/**
	 * Sets main image url for the metadata with given id.
	 * @param int $id Id of the metadata to set image url.
	 * @param string $imageUrl Url of the image.
	 * @return bool Returns true in case of successful update, or false otherwise.
	 * @throws ArgumentException
	 */
	public static function setMetadataImage($id, $imageUrl)
	{
		if (!is_int($id))
		{
			throw new ArgumentException("Id of the metadata must be an integer", "id");
		}
		if (!is_string($imageUrl) && !is_null($imageUrl))
		{
			throw new ArgumentException("Url of the image must be a string", "imageUrl");
		}

		$metadata = UrlMetadataTable::getList(array(
			'select' => array('IMAGE', 'IMAGE_ID', 'EXTRA'),
			'filter' => array('=ID' => $id)
		))->fetch();

		if (isset($metadata['EXTRA']['IMAGES']))
		{
			$imageIndex = array_search($imageUrl, $metadata['EXTRA']['IMAGES']);
			if ($imageIndex === false)
			{
				unset($metadata['EXTRA']['SELECTED_IMAGE']);
			}
			else
			{
				$metadata['EXTRA']['SELECTED_IMAGE'] = $imageIndex;
			}
		}

		if (static::getOptionSaveImages())
		{
			$metadata['IMAGE_ID'] = static::saveImage($imageUrl);
			$metadata['IMAGE'] = null;
		}
		else
		{
			$metadata['IMAGE'] = $imageUrl;
			$metadata['IMAGE_ID'] = null;
		}

		return UrlMetadataTable::update($id, $metadata)->isSuccess();
	}

	/**
	 * Checks if UrlPreview is enabled in module option
	 * @return bool True if UrlPreview is enabled in module options.
	 */
	public static function isEnabled()
	{
		static $result = null;
		if (is_null($result))
		{
			$result = Option::get('main', 'url_preview_enable', 'N') === 'Y';
		}
		return $result;
	}

	/**
	 * Signs value using UrlPreview salt
	 * @param string $id Unsigned value.
	 * @return string Signed value.
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function sign($id)
	{
		$signer = new Signer();
		return $signer->sign((string)$id, static::SIGN_SALT);
	}

	protected static function getUrlInfoFromExternal($url, $method, $checkAccess = true, $userId = 0)
	{
		//todo: caching
		$routeRecord = Router::dispatch(new Uri(static::unfoldShortLink($url)));
		if ($routeRecord === false)
		{
			return false;
		}

		if ($userId == 0)
		{
			$userId = static::getCurrentUserId();
		}

		if (isset($routeRecord['MODULE']) && Loader::includeModule($routeRecord['MODULE']))
		{
			$className = $routeRecord['CLASS'];
			$parameters = $routeRecord['PARAMETERS'];
			$parameters['URL'] = $url;

			if ($checkAccess && (!method_exists($className, 'checkUserReadAccess') || $userId == 0 || !$className::checkUserReadAccess($parameters, $userId)))
				return false;

			if (method_exists($className, $method))
			{
				return $className::$method($parameters);
			}
		}
		return false;
	}

	/**
	 * @param string $url URL of the document.
	 * @return array|false Fetched metadata or false if metadata was not found, or was invalid.
	 */
	protected static function fetchUrlMetadata($url)
	{
		$fullUrl = static::unfoldShortLink($url);
		$uriParser = new Uri($fullUrl);
		if (static::isUrlLocal($uriParser))
		{
			if ($routeRecord = Router::dispatch($uriParser))
			{
				$metadata = array(
					'URL' => $url,
					'TYPE' => UrlMetadataTable::TYPE_DYNAMIC,
				);
			}
		}
		else
		{
			$metadataRemote = static::getRemoteUrlMetadata($uriParser);
			if (is_array($metadataRemote) && !empty($metadataRemote))
			{
				$metadata = array(
					'URL' => $url,
					'TYPE' => $metadataRemote['TYPE'] ?? UrlMetadataTable::TYPE_STATIC,
					'TITLE' => $metadataRemote['TITLE'] ?? '',
					'DESCRIPTION' => $metadataRemote['DESCRIPTION'] ?? '',
					'IMAGE_ID' => $metadataRemote['IMAGE_ID'] ?? null,
					'IMAGE' => $metadataRemote['IMAGE'] ?? null,
					'EMBED' => $metadataRemote['EMBED'] ?? null,
					'EXTRA' => $metadataRemote['EXTRA'] ?? null,
					'DATE_EXPIRE' => $metadataRemote['DATE_EXPIRE'] ?? null,
				);
			}
		}

		if (isset($metadata['TYPE']))
		{
			return $metadata;
		}
		return false;
	}

	/**
	 * Returns true if given URL is local
	 *
	 * @param Uri $uri Absolute URL to be checked.
	 * @return bool
	 */
	protected static function isUrlLocal(Uri $uri)
	{
		if ($uri->getHost() == '')
		{
			return true;
		}

		$host = \Bitrix\Main\Context::getCurrent()->getRequest()->getHttpHost();
		return $uri->getHost() === $host;
	}

	/**
	 * @param Uri $uri Absolute URL to get metadata for.
	 * @return array|false
	 */
	protected static function getRemoteUrlMetadata(Uri $uri)
	{
		$httpClient = new HttpClient();
		//prevents proxy to LAN
		$httpClient->setPrivateIp(false);
		$httpClient->setTimeout(5);
		$httpClient->setStreamTimeout(5);
		$httpClient->setHeader('User-Agent', self::USER_AGENT);

		$httpClient->shouldFetchBody(function (Response $response) {
			return ($response->getHeadersCollection()->getContentType() === 'text/html');
		});

		try
		{
			if (!$httpClient->query('GET', $uri->getUri()))
			{
				return false;
			}
		}
		catch (\ErrorException $exception)
		{
			return false;
		}

		if ($httpClient->getStatus() !== 200)
		{
			return false;
		}

		$peerIpAddress = $httpClient->getPeerAddress();

		if ($httpClient->getHeaders()->getContentType() !== 'text/html')
		{
			$metadata = static::getFileMetadata($httpClient->getEffectiveUrl(), $httpClient->getHeaders());
			$metadata['EXTRA']['PEER_IP_ADDRESS'] = $peerIpAddress;
			$metadata['EXTRA']['PEER_IP_PRIVATE'] = (new IpAddress($peerIpAddress))->isPrivate();
			return $metadata;
		}

		$html = $httpClient->getResult();

		$htmlDocument = new HtmlDocument($html, $uri);
		$htmlDocument->setEncoding($httpClient->getCharset());
		ParserChain::extractMetadata($htmlDocument);
		$metadata = $htmlDocument->getMetadata();

		if (is_array($metadata) && static::validateRemoteMetadata($metadata))
		{
			if (isset($metadata['IMAGE']) && static::getOptionSaveImages())
			{
				$metadata['IMAGE_ID'] = static::saveImage($metadata['IMAGE']);
				unset($metadata['IMAGE']);
			}

			if (isset($metadata['DESCRIPTION']) && mb_strlen($metadata['DESCRIPTION']) > static::MAX_DESCRIPTION)
			{
				$metadata['DESCRIPTION'] = mb_substr($metadata['DESCRIPTION'], 0, static::MAX_DESCRIPTION);
			}

			if (!is_array($metadata['EXTRA']))
			{
				$metadata['EXTRA'] = array();
			}

			$metadata['EXTRA'] = array_merge($metadata['EXTRA'], array(
				'PEER_IP_ADDRESS' => $peerIpAddress,
				'PEER_IP_PRIVATE' => (new IpAddress($peerIpAddress))->isPrivate(),
				'X_FRAME_OPTIONS' => $httpClient->getHeaders()->get('X-Frame-Options', true),
				'EFFECTIVE_URL' => $httpClient->getEffectiveUrl(),
			));

			return $metadata;
		}

		return false;
	}

	/**
	 * @param string $url Image's URL.
	 * @return integer Saved file identifier
	 */
	protected static function saveImage($url)
	{
		$fileId = false;
		$httpClient = new HttpClient();
		$httpClient->setTimeout(5);
		$httpClient->setStreamTimeout(5);

		$urlComponents = parse_url($url);
		$fileName = ($urlComponents && $urlComponents["path"] <> '')
			? bx_basename($urlComponents["path"])
			: bx_basename($url)
		;

		$tempFileName = Random::getString(32) . '.' . GetFileExtension($fileName);
		$tempPath = \CFile::GetTempName('', $tempFileName);

		try
		{
			$httpClient->download($url, $tempPath);
		}
		catch (\ErrorException $exception)
		{
			return null;
		}
		$fileName = $httpClient->getHeaders()->getFilename();
		$localFile = \CFile::MakeFileArray($tempPath);
		$localFile['MODULE_ID'] = 'main';

		if (is_array($localFile))
		{
			if ($fileName <> '')
			{
				$localFile['name'] = $fileName;
			}
			if (\CFile::CheckImageFile($localFile, 0, 0, 0, array("IMAGE")) === null)
			{
				$fileId = \CFile::SaveFile($localFile, 'urlpreview', true);
			}
		}

		return ($fileId === false ? null : $fileId);
	}

	/**
	 * If provided url does not contain scheme part, tries to add it
	 *
	 * @param string $url URL to be fixed.
	 * @return string Fixed URL.
	 */
	protected static function normalizeUrl($url)
	{
		if (strpos($url, 'https://') === 0 || strpos($url, 'http://') === 0)
		{
			//nop
		}
		elseif (strpos($url, '//') === 0)
		{
			$url = 'http:'.$url;
		}
		elseif (strpos($url, '/') === 0)
		{
			//nop
		}
		else
		{
			$url = 'http://'.$url;
		}

		$parsedUrl = new Uri($url);
		$parsedUrl->setHost(mb_strtolower($parsedUrl->getHost()));

		return $parsedUrl->getUri();
	}

	/**
	 * Returns value of the option for saving images locally.
	 * @return bool True if images should be saved locally.
	 */
	protected static function getOptionSaveImages()
	{
		static $result = null;
		if (is_null($result))
		{
			$result = Option::get('main', 'url_preview_save_images', 'N') === 'Y';
		}
		return $result;
	}

	/**
	 * Checks if metadata is complete.
	 * @param array $metadata HTML document metadata.
	 * @return bool True if metadata is complete, false otherwise.
	 */
	protected static function validateRemoteMetadata(array $metadata)
	{
		$result = ((isset($metadata['TITLE']) && isset($metadata['IMAGE'])) || (isset($metadata['TITLE']) && isset($metadata['DESCRIPTION'])) || isset($metadata['EMBED']));
		return $result;
	}

	/**
	 * Returns id of currently logged user.
	 * @return int User's id.
	 */
	public static function getCurrentUserId()
	{
		return ($GLOBALS['USER'] instanceof \CUser) ? (int)$GLOBALS['USER']->getId() : 0;
	}

	/**
	 * Unfolds internal short url. If url is not classified as a short link, returns input $url.
	 * @param string $shortUrl Short URL.
	 * @return string Full URL.
	 */
	protected static function unfoldShortLink($shortUrl)
	{
		static $cache = [];
		if (isset($cache[$shortUrl]))
		{
			return $cache[$shortUrl];
		}

		$result = $shortUrl;
		if ($shortUri = \CBXShortUri::GetUri($shortUrl))
		{
			$result = $shortUri['URI'];
		}
		$cache[$shortUrl] = $result;
		return $result;
	}

	/**
	 * Returns metadata for downloadable file.
	 * @param string $path Path part of the URL.
	 * @param HttpHeaders $httpHeaders Server's response headers.
	 * @return array|bool Metadata record if mime type and filename were detected, or false otherwise.
	 */
	protected static function getFileMetadata($path, HttpHeaders $httpHeaders)
	{
		$mimeType = $httpHeaders->getContentType();
		$filename = $httpHeaders->getFilename() ?: bx_basename($path);
		$result = false;
		if ($mimeType && $filename)
		{
			$result = array(
				'TYPE' => UrlMetadataTable::TYPE_FILE,
				'EXTRA' => array(
					'ATTACHMENT' => mb_strtolower($httpHeaders->getContentDisposition()) === 'attachment' ? 'Y' : 'N',
					'MIME_TYPE' => $mimeType,
					'FILENAME' => $filename,
					'SIZE' => $httpHeaders->get('Content-Length')
				)
			);
		}
		return $result;
	}

	/**
	 * @deprecated Will be removed.
	 * @param string $ipAddress
	 * @return bool
	 */
	public static function isIpAddressPrivate($ipAddress)
	{
		return (new IpAddress($ipAddress))->isPrivate();
	}

	/**
	 * Returns true if host of $uri is in $trustedHosts list.
	 *
	 * @param Uri $uri
	 * @return bool
	 */
	public static function isHostTrusted(Uri $uri)
	{
		$result = false;
		$domainNameParts = explode('.', $uri->getHost());
		if (is_array($domainNameParts) && ($partsCount = count($domainNameParts)) >= 2)
		{
			$domainName = $domainNameParts[$partsCount-2] . '.' . $domainNameParts[$partsCount-1];
			$result = isset(static::$trustedHosts[$domainName]);
		}
		return $result;
	}

	/**
	 * Returns video metaData for $url if its host is trusted.
	 *
	 * @param string $url
	 * @return array|false
	 */
	public static function fetchVideoMetaData($url)
	{
		$url = static::unfoldShortLink($url);
		$uri = new Uri($url);
		if (static::isHostTrusted($uri) || static::isEnabled())
		{
			$url = static::normalizeUrl($url);
			$metadataId = static::reserveIdForUrl($url);
			$metadata = static::fetchUrlMetadata($url);
			if (is_array($metadata) && !empty($metadata))
			{
				$result = UrlMetadataTable::update($metadataId, $metadata);
				$metadata['ID'] = $result->getId();
			}
			else
			{
				return false;
			}
			if (isset($metadata['EMBED']) && !empty($metadata['EMBED']) && strpos($metadata['EMBED'], '<iframe') === false)
			{
				$url = static::getInnerFrameUrl($metadata['ID'], $metadata['EXTRA']['PROVIDER_NAME']);
				if (intval($metadata['EXTRA']['VIDEO_WIDTH']) <= 0)
				{
					$metadata['EXTRA']['VIDEO_WIDTH'] = self::IFRAME_MAX_WIDTH;
				}
				if (intval($metadata['EXTRA']['VIDEO_HEIGHT']) <= 0)
				{
					$metadata['EXTRA']['VIDEO_HEIGHT'] = self::IFRAME_MAX_HEIGHT;
				}
				$metadata['EMBED'] = '<iframe src="'.$url.'" allowfullscreen="" width="'.$metadata['EXTRA']['VIDEO_WIDTH'].'" height="'.$metadata['EXTRA']['VIDEO_HEIGHT'].'" frameborder="0"></iframe>';
			}

			if ($metadata['EMBED'] || $metadata['EXTRA']['VIDEO'])
			{
				return $metadata;
			}
		}

		return false;
	}

	/**
	 * Returns inner frame url to embed third parties html video players.
	 *
	 * @param int $id
	 * @param string $provider
	 * @return bool|string
	 */
	public static function getInnerFrameUrl($id, $provider = '')
	{
		$result = false;

		$componentPath = \CComponentEngine::makeComponentPath('bitrix:main.urlpreview');
		if (!empty($componentPath))
		{
			$componentPath = getLocalPath('components'.$componentPath.'/frame.php');
			$uri = new Uri($componentPath);
			$uri->addParams(array('id' => $id, 'provider' => $provider));
			$result = static::normalizeUrl($uri->getLocator());
		}

		return $result;
	}
}