<?php
IncludeModuleLangFile(__FILE__);

class CCloudSecurityService_STS
{
	protected $service_host = '';
	protected $streamTimeout = 5;
	protected $set_headers = [];

	//Sent request parameters
	protected $host = '';
	protected $verb = '';
	protected $url = '';
	protected $headers = [];
	//Recieved response
	protected $errno = 0;
	protected $errstr = '';
	protected $status = 0;
	protected $result = '';

	public function GetLastRequestStatus()
	{
		return $this->status;
	}

	public static function GetObject()
	{
		return new static();
	}

	public function GetID()
	{
		return 's3_sts';
	}

	public function GetName()
	{
		return 'Generic Amazon Security Token Service';
	}

	public function GetDefaultBucketControlPolicy($bucket, $prefix)
	{
		return [
			'Statement' => [
			],
		];
	}

	public function GetFederationToken($arBucket, $Policy, $Name, $DurationSeconds = 129600/*36h*/)
	{
		$params = [
			'Action' => 'GetFederationToken',
			'Version' => '2011-06-15',
			'DurationSeconds' => intval($DurationSeconds),
			'Name' => $Name,
			'Policy' => json_encode($Policy),
		];

		$content = '';
		ksort($params);
		foreach ($params as $name => $value)
		{
			if ($content !== '')
			{
				$content .= '&';
			}
			$content .= urlencode($name) . '=' . urlencode($value);
		}

		$response = $this->SendRequest(
			CCloudStorage::GetServiceByID($arBucket['SERVICE_ID']),
			$arBucket['SETTINGS'],
			'GET',
			$arBucket['BUCKET'],
			'/',
			'?' . $content
		);

		if (
			is_array($response)
			&& isset($response['GetFederationTokenResponse'])
			&& is_array($response['GetFederationTokenResponse'])
			&& isset($response['GetFederationTokenResponse']['#'])
			&& is_array($response['GetFederationTokenResponse']['#'])
			&& isset($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'])
			&& is_array($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'])
			&& isset($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0])
			&& is_array($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0])
			&& isset($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0]['#'])
			&& is_array($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0]['#'])
			&& isset($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0]['#']['Credentials'])
			&& is_array($response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0]['#']['Credentials'])
		)
		{
			$Credentials = $response['GetFederationTokenResponse']['#']['GetFederationTokenResult'][0]['#']['Credentials'];

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['SessionToken'])
				&& is_array($Credentials[0]['#']['SessionToken'])
				&& isset($Credentials[0]['#']['SessionToken'][0])
				&& is_array($Credentials[0]['#']['SessionToken'][0])
				&& isset($Credentials[0]['#']['SessionToken'][0]['#'])
			)
			{
				$SessionToken = $Credentials[0]['#']['SessionToken'][0]['#'];
			}
			else
			{
				return 1;
			}

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['SecretAccessKey'])
				&& is_array($Credentials[0]['#']['SecretAccessKey'])
				&& isset($Credentials[0]['#']['SecretAccessKey'][0])
				&& is_array($Credentials[0]['#']['SecretAccessKey'][0])
				&& isset($Credentials[0]['#']['SecretAccessKey'][0]['#'])
			)
			{
				$SecretAccessKey = $Credentials[0]['#']['SecretAccessKey'][0]['#'];
			}
			else
			{
				return 2;
			}

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['AccessKeyId'])
				&& is_array($Credentials[0]['#']['AccessKeyId'])
				&& isset($Credentials[0]['#']['AccessKeyId'][0])
				&& is_array($Credentials[0]['#']['AccessKeyId'][0])
				&& isset($Credentials[0]['#']['AccessKeyId'][0]['#'])
			)
			{
				$AccessKeyId = $Credentials[0]['#']['AccessKeyId'][0]['#'];
			}
			else
			{
				return 3;
			}

			return [
				'ACCESS_KEY' => $AccessKeyId,
				'SECRET_KEY' => $SecretAccessKey,
				'SESSION_TOKEN' => $SessionToken,
				'EXPIRATION' => $Credentials[0]['#']['Expiration'][0]['#'] ?? '',
			];
		}
		else
		{
			return false;
		}
	}

	public function AssumeRole($arBucket, $Policy, $Name, $DurationSeconds = 43200/*12h*/)
	{
		$params = [
			'Action' => 'AssumeRole',
			'Version' => '2011-06-15',
			'RoleArn' => $Name,
			'RoleSessionName' => 'testexample',
			'Policy' => json_encode($Policy),
			'DurationSeconds' => intval($DurationSeconds),
		];

		$content = '';
		ksort($params);
		foreach ($params as $name => $value)
		{
			if ($content !== '')
			{
				$content .= '&';
			}
			$content .= urlencode($name) . '=' . urlencode($value);
		}

		$response = $this->SendRequest(
			CCloudStorage::GetServiceByID($arBucket['SERVICE_ID']),
			$arBucket['SETTINGS'],
			'POST',
			$arBucket['BUCKET'],
			'/',
			'',
			$content,
			[
				'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
			]
		);

		if (
			is_array($response)
			&& isset($response['AssumeRoleResponse'])
			&& is_array($response['AssumeRoleResponse'])
			&& isset($response['AssumeRoleResponse']['#'])
			&& is_array($response['AssumeRoleResponse']['#'])
			&& isset($response['AssumeRoleResponse']['#']['AssumeRoleResult'])
			&& is_array($response['AssumeRoleResponse']['#']['AssumeRoleResult'])
			&& isset($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0])
			&& is_array($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0])
			&& isset($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0]['#'])
			&& is_array($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0]['#'])
			&& isset($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0]['#']['Credentials'])
			&& is_array($response['AssumeRoleResponse']['#']['AssumeRoleResult'][0]['#']['Credentials'])
		)
		{
			$Credentials = $response['AssumeRoleResponse']['#']['AssumeRoleResult'][0]['#']['Credentials'];

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['SessionToken'])
				&& is_array($Credentials[0]['#']['SessionToken'])
				&& isset($Credentials[0]['#']['SessionToken'][0])
				&& is_array($Credentials[0]['#']['SessionToken'][0])
				&& isset($Credentials[0]['#']['SessionToken'][0]['#'])
			)
			{
				$SessionToken = $Credentials[0]['#']['SessionToken'][0]['#'];
			}
			else
			{
				return 1;
			}

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['SecretAccessKey'])
				&& is_array($Credentials[0]['#']['SecretAccessKey'])
				&& isset($Credentials[0]['#']['SecretAccessKey'][0])
				&& is_array($Credentials[0]['#']['SecretAccessKey'][0])
				&& isset($Credentials[0]['#']['SecretAccessKey'][0]['#'])
			)
			{
				$SecretAccessKey = $Credentials[0]['#']['SecretAccessKey'][0]['#'];
			}
			else
			{
				return 2;
			}

			if (
				isset($Credentials[0])
				&& is_array($Credentials[0])
				&& isset($Credentials[0]['#'])
				&& is_array($Credentials[0]['#'])
				&& isset($Credentials[0]['#']['AccessKeyId'])
				&& is_array($Credentials[0]['#']['AccessKeyId'])
				&& isset($Credentials[0]['#']['AccessKeyId'][0])
				&& is_array($Credentials[0]['#']['AccessKeyId'][0])
				&& isset($Credentials[0]['#']['AccessKeyId'][0]['#'])
			)
			{
				$AccessKeyId = $Credentials[0]['#']['AccessKeyId'][0]['#'];
			}
			else
			{
				return 3;
			}

			return [
				'ACCESS_KEY' => $AccessKeyId,
				'SECRET_KEY' => $SecretAccessKey,
				'SESSION_TOKEN' => $SessionToken,
				'EXPIRATION' => $Credentials[0]['#']['Expiration'][0]['#'] ?? '',
			];
		}
		else
		{
			return false;
		}
	}

	protected function SendRequest($service, $arSettings, $verb, $bucket, $file_name='/', $params='', $content='', $additional_headers=/*.(array[string]string).*/[])
	{
		global $APPLICATION;
		$this->status = 0;

		$request = new Bitrix\Main\Web\HttpClient([
			'redirect' => false,
			'streamTimeout' => $this->streamTimeout,
		]);
		if (isset($additional_headers['option-file-result']))
		{
			$request->setOutputStream($additional_headers['option-file-result']);
		}

		if (isset($additional_headers['Content-Type']))
		{
			$ContentType = $additional_headers['Content-Type'];
		}
		else
		{
			$ContentType = $content !== '' ? 'text/plain' : '';
		}
		unset($additional_headers['Content-Type']);

		foreach ($this->set_headers as $key => $value)
		{
			$additional_headers[$key] = $value;
		}

		if (array_key_exists('SESSION_TOKEN', $arSettings))
		{
			$additional_headers['x-amz-security-token'] = $arSettings['SESSION_TOKEN'];
		}

		$host = $additional_headers['Host'] = $this->service_host;

		foreach ($service->SignRequest($arSettings, $verb, $bucket, $file_name, $ContentType, $additional_headers, $params, $content, 'sts') as $key => $value)
		{
			$request->setHeader($key, $value);
		}

		foreach ($additional_headers as $key => $value)
		{
			if (!preg_match('/^option-/', $key))
			{
				$request->setHeader($key, $value);
			}
		}

		$this->status = 0;
		$this->host = $host;
		$this->verb = $verb;
		$this->url = 'https://' . $host . $file_name . $params;
		$this->headers = [];
		$this->errno = 0;
		$this->errstr = '';
		$this->result = '';

		$stime = 0;
		$logRequest = false;
		if (defined('BX_CLOUDS_TRACE') && $verb !== 'GET' && $verb !== 'HEAD')
		{
			$stime = microtime(1);
			$logRequest = [
				'request_id' => md5((string)mt_rand()),
				'portal' => $_SERVER['HTTP_HOST'],
				'verb' => $this->verb,
				'url' => $this->url,
			];
			if (function_exists('getmypid'))
			{
				$logRequest['pid'] = getmypid();
			}
			AddMessage2Log(json_encode($logRequest), 'clouds', 20);
		}

		$request->setHeader('Content-type', $ContentType);
		$request->query($this->verb, $this->url, $content);

		$this->status = $request->getStatus();
		foreach ($request->getHeaders() as $key => $value)
		{
			$this->headers[$key] = is_array($value) ? $value[0] : $value;
		}
		$this->errstr = implode("\n", $request->getError());
		$this->errno = $this->errstr ? 255 : 0;
		$this->result = $request->getResult();

		if ($logRequest)
		{
			$logRequest['status'] = $this->status;
			$logRequest['time'] = round(microtime(true) - $stime, 6);
			$logRequest['headers'] = $this->headers;
			AddMessage2Log(json_encode($logRequest), 'clouds', 0);
		}

		if ($this->status == 200)
		{
			if (
				isset($additional_headers['option-raw-result'])
				|| isset($additional_headers['option--result'])
			)
			{
				return $this->result;
			}
			elseif ($this->result !== '')
			{
				$obXML = new CDataXML;
				$text = preg_replace('/<' . '\\?XML.*?\\?' . '>/i', '', $this->result);
				if ($obXML->LoadString($text))
				{
					$arXML = $obXML->GetArray();
					if (is_array($arXML))
					{
						return $arXML;
					}
				}
				//XML parse error
				$e = new CApplicationException(GetMessage('CLO_SECSERV_STS_XML_PARSE_ERROR', ['#errno#' => '1']));
				$APPLICATION->ThrowException($e);
				return false;
			}
			else
			{
				//Empty success result
				return [];
			}
		}
		elseif ($this->status > 0)
		{
			if ($this->result)
			{
				$APPLICATION->ThrowException(GetMessage('CLO_SECSERV_STS_XML_ERROR', ['#errmsg#' => $this->result]));
				return false;
			}
			$APPLICATION->ThrowException(GetMessage('CLO_SECSERV_STS_XML_PARSE_ERROR', ['#errno#' => 2]));
			return false;
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage('CLO_SECSERV_STS_XML_PARSE_ERROR', ['#errno#' => 3]));
			return false;
		}
	}
}
