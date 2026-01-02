<?php

if (!empty($_GET['ts']) && $_GET['ts'] === 'bxviewer')
{
	return;
}

if (!empty($_GET['_gen']))
{
	return;
}

$encryptedData = $_GET['_esd'] ?? null;
if (empty($encryptedData))
{
	return;
}

if (str_starts_with($_SERVER['SCRIPT_NAME'], '/mobile/ajax.php'))
{
	return;
}

function readConfig(): ?array
{
	/** @see \Bitrix\Main\Config\Configuration::CONFIGURATION_FILE_PATH */
	/** @see \Bitrix\Main\Config\Configuration::CONFIGURATION_FILE_EXTRA */
	$settingsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/.settings.php';
	$settingsExtraPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/.settings_extra.php';

	$settings = include($settingsPath);
	if (empty($settings) || (is_array($settings) === false))
	{
		$settings = [];
	}

	if (file_exists($settingsExtraPath))
	{
		$settingsExtra = include($settingsExtraPath);
		if (is_array($settingsExtra) && !empty($settingsExtra))
		{
			foreach ($settingsExtra as $k => $v)
			{
				$settings[$k] = $v;
			}
		}
	}

	/** @see \Bitrix\Disk\QuickAccess\Configuration::CONFIG_SECTION */
	/** @see \Bitrix\Disk\QuickAccess\Configuration::CONFIG_STORAGE */
	if (empty($settings['main.token_service']['value']['storage']))
	{
		return null;
	}

	if (!is_array($settings['main.token_service']['value']['storage']))
	{
		return null;
	}

	if (empty($settings['main.token_service']['value']['key']))
	{
		return null;
	}

	return $settings['main.token_service']['value'];
}

$config = readConfig();
if (!$config)
{
	return null;
}

function isValidSign(string $key, string $signedUserToken): bool
{
	$parts = explode('.', $signedUserToken, 2);
	if (count($parts) !== 2)
	{
		return false;
	}

	[$message, $signature] = $parts;
	if (empty($message) || empty($signature))
	{
		return false;
	}

	$key = hash('sha512', $key);
	$expectedSignature = bin2hex(hash_hmac('sha256', $message, $key, true));

	return hash_equals($expectedSignature, $signature);
}

function getMemcacheStorage(array $config): ?\Memcache
{
	if (!extension_loaded('memcache'))
	{
		return null;
	}

	$connection = new \Memcache();
	$result = $connection->pconnect($config['host'], $config['port'], 3);

	return $result ? $connection : null;
}

function getRedisStorage(array $config): ?\Redis
{
	if (!extension_loaded('redis'))
	{
		return null;
	}

	$connection = new \Redis();

	$params = [
		$config['host'],
		$config['port'],
		$config['timeout'] ?? 0,
		null,
		0,
		$config['readTimeout'] ?? 0
	];

	$result = $connection->pconnect(...$params);

	if ($result)
	{
		if (isset($config['compression']) || defined('\Redis::COMPRESSION_LZ4'))
		{
			$connection->setOption(\Redis::OPT_COMPRESSION, $config['compression'] ?? \Redis::COMPRESSION_LZ4);
			$connection->setOption(\Redis::OPT_COMPRESSION_LEVEL, $config['compression_level'] ?? \Redis::COMPRESSION_ZSTD_MAX);
		}

		if (isset($config['serializer']) || defined('\Redis::SERIALIZER_IGBINARY'))
		{
			$connection->setOption(\Redis::OPT_SERIALIZER, $config['serializer'] ?? \Redis::SERIALIZER_IGBINARY);
		}
	}

	return $result ? $connection : null;
}

function getStorage(array $config): \Memcache|\Redis|null
{
	if ($config['type'] === 'redis')
	{
		return getRedisStorage($config);
	}

	if ($config['type'] === 'memcache')
	{
		return getMemcacheStorage($config);
	}

	return null;
}

/**
 * @see \Bitrix\Disk\QuickAccess\Storage\RedisScopeStorage::hasScope
 * @see \Bitrix\Disk\QuickAccess\Storage\ScopeStorage::hasScope
 */
function hasScope(
	string $prefix,
	string $userToken,
	string $scope,
	\Redis|\Memcache $storage
): bool
{
	if (empty($scope))
	{
		return false;
	}
	$key = "{$prefix}userScopes:{$userToken}";
	if ($storage instanceof \Redis)
	{
		$expirationTime = $storage->zScore($key, $scope);
		if (!$expirationTime)
		{
			return false;
		}

		if ($expirationTime <= time())
		{
			return false;
		}

		return true;
	}
	else
	{
		$storedScopes = $storage->get($key);
		if (empty($storedScopes) || !is_string($storedScopes))
		{
			return false;
		}
		$storedScopes = decodeJson($storedScopes);
		if ($storedScopes[$scope] < time())
		{
			return false;
		}

		return true;
	}
}

function decodeJson(string $data): array
{
	return json_decode($data, true, 64) ?: [];
}

function decryptData(string $data, string $key): ?array
{
	$data = base64_decode($data, true);
	if ($data === false)
	{
		return null;
	}

	$cipherAlgorithm = 'aes-256-ctr';
	$hashAlgorithm = 'sha256';

	$ivLength = openssl_cipher_iv_length($cipherAlgorithm);
	$iv = substr($data, 0, $ivLength);
	$raw = substr($data, $ivLength);

	$keyHash = openssl_digest($iv . $key, $hashAlgorithm, true);
	$decrypted = openssl_decrypt($raw, $cipherAlgorithm, $keyHash, OPENSSL_RAW_DATA, $iv);

	if ($decrypted === false)
	{
		return null;
	}

	$hashPart = substr($decrypted, 0, strlen($keyHash));
	$result = substr($decrypted, strlen($keyHash));
	$checkHash = openssl_digest($result, $hashAlgorithm, true);

	if ($hashPart !== $checkHash)
	{
		return null;
	}

	return decodeJson($result);
}

$cookiePrefix = $config['cookiePrefix'] ?? 'BITRIX_SM';

/** @see \Bitrix\Disk\QuickAccess\ScopeTokenService::COOKIE_NAME */
$signedUserToken = $_COOKIE["{$cookiePrefix}_DTOKEN"] ?? null;
if (empty($signedUserToken))
{
	return;
}

$key = $config['key'];

if (isValidSign($key, $signedUserToken) === false)
{
	return;
}

$decryptedData = decryptData($encryptedData, $key);
if (empty($decryptedData))
{
	return;
}

$storage = getStorage($config['storage']);
if (!$storage)
{
	return;
}

$userToken = explode('.', $signedUserToken, 2)[0];

if (!hasScope($config['storage']['keyPrefix'], $userToken, $decryptedData['scope'], $storage))
{
	return;
}

/** @see \Bitrix\Disk\QuickAccess\Storage\ScopeStorage::getFileKey */
$fileInfoKey = "{$config['storage']['keyPrefix']}file:{$decryptedData['fileId']}";
$storedData = $storage->get($fileInfoKey);
if (empty($storedData))
{
	return;
}

try
{
	$storedData = json_decode($storedData, true, 3, JSON_THROW_ON_ERROR);
}
catch (\JsonException $e)
{
	return;
}

if ($_GET['action'] === 'disk.api.file.showPreview' && isset($storedData['preview']))
{
	$storedData = $storedData['preview'];
}

$accelRedirectPath = $storedData['path'];
$contentType = $storedData['contentType'];
$expirationTime = $storedData['expirationTime'];
$dir = $storedData['dir'];
$bFileId = $storedData['id'];
$fileName = $storedData['filename'];
$attachmentName = $decryptedData['l'] ?? null;

if (time() > $expirationTime)
{
	return;
}

$requestedWidth = isset($_GET['width']) ? (int)$_GET['width'] : 0;
$requestedHeight = isset($_GET['height']) ? (int)$_GET['height'] : 0;
$originalWidth = (int)$storedData['width'] ?: 0;
$originalHeight = (int)$storedData['height'] ?: 0;

// Resize the image only if a smaller version than the original is needed.
if (
    $requestedWidth > 0 && $requestedHeight > 0
	&& ($requestedWidth < $originalWidth || $requestedHeight < $originalHeight)
)
{
	$exact = $_GET['exact'] === 'Y' ? 2 : 1;

	if (empty($storedData['handlerId']))
	{
		$resizeDir = "{$requestedWidth}_{$requestedHeight}_{$exact}";
		$accelRedirectPath = "/upload/resize_cache/x/{$dir}/{$resizeDir}/{$fileName}";
	}
	else
	{
		$encodedDir = rawurlencode($dir);
		$delimiterPosition = strpos($accelRedirectPath, $encodedDir);
		if ($delimiterPosition === false)
		{
			return;
		}

		$partBeforeDir = substr($accelRedirectPath, 0, $delimiterPosition);
		$dirAndRest = substr($accelRedirectPath, $delimiterPosition);
		$defaultResizeFilter = [
			['width' => $requestedWidth, 'height' => $requestedHeight],
			$exact,
			[],
			false,
			[0 => ['name' => 'sharpen', 'precision' => 15]],
			true
		];
		$resizeDir = md5(serialize($defaultResizeFilter));

		$accelRedirectPath = $partBeforeDir . rawurlencode("resize_cache/{$bFileId}/{$resizeDir}/") . $dirAndRest;
		$accelRedirectPath = '/upload/resize_cache/c' . $accelRedirectPath;
	}
}

header('X-Accel-Buffering: no');
header('X-Accel-Redirect: ' . $accelRedirectPath);
header('Content-Type: ' . $contentType);
if ($attachmentName)
{
	$urlEncodedName = rawurlencode($attachmentName);
	header("X-CD-Info: attachment; filename*=utf-8''{$urlEncodedName}");
}
header('X-Gen-Src: ' . $_SERVER['REQUEST_URI'] . '&_gen=1');

die;