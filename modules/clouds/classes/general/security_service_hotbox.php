<?php

class CCloudSecurityService_HotBox
{
	/* @var $service CCloudStorageService_HotBox */
	protected $service = null;
	protected $arBucket = false;

	function __construct()
	{
		$this->service = CCloudStorageService_HotBox::GetObjectInstance();
	}

	function GetObject()
	{
		return new CCloudSecurityService_HotBox();
	}

	function GetID()
	{
		return "hotbox_ss";
	}

	function GetName()
	{
		return "HotBox Security Token Service";
	}

	function SetBucketArray($arBucket)
	{
		$this->arBucket = $arBucket;
	}

	function CreatePrefixKey($UserName, $Prefix)
	{
		$response = $this->service->SendRequest(
			$this->arBucket["SETTINGS"],
			'PUT',
			$this->arBucket["BUCKET"],
			'/',
			'?pak=&username='.urlencode($UserName).'&prefix='.urlencode($UserName)
		);

		if (
			is_array($response)
			&& isset($response["CreatePrefixKeyResult"])
			&& is_array($response["CreatePrefixKeyResult"])
			&& isset($response["CreatePrefixKeyResult"]["#"])
			&& is_array($response["CreatePrefixKeyResult"]["#"])
		)
		{
			$Credentials = $response["CreatePrefixKeyResult"]["#"];

			if (
				isset($Credentials["AccessKey"])
				&& is_array($Credentials["AccessKey"])
				&& isset($Credentials["AccessKey"][0])
				&& is_array($Credentials["AccessKey"][0])
				&& isset($Credentials["AccessKey"][0]["#"])
			)
				$AccessKeyId = $Credentials["AccessKey"][0]["#"];
			else
				return 1;

			if (
				isset($Credentials["SecretKey"])
				&& is_array($Credentials["SecretKey"])
				&& isset($Credentials["SecretKey"][0])
				&& is_array($Credentials["SecretKey"][0])
				&& isset($Credentials["SecretKey"][0]["#"])
			)
				$SecretAccessKey = $Credentials["SecretKey"][0]["#"];
			else
				return 2;

			return array(
				"ACCESS_KEY" => $AccessKeyId,
				"SECRET_KEY" => $SecretAccessKey,
			);
		}
		else
		{
			return false;
		}
	}

	function ListPrefixKeys($namePrefix = '')
	{
		$result = array();
		$marker = '';
		while(true)
		{
			$response = $this->service->SendRequest(
				$this->arBucket["SETTINGS"],
				'GET',
				$this->arBucket["BUCKET"],
				'/',
				'?pak=&max-keys=50&marker='.urlencode($marker).'&name-prefix='.urlencode($namePrefix)
			);
			if (
				$this->service->GetLastRequestStatus() == 200
				&& is_array($response)
				&& isset($response["ListPrefixKeysResult"])
				&& is_array($response["ListPrefixKeysResult"])
				&& isset($response["ListPrefixKeysResult"]["#"])
				&& is_array($response["ListPrefixKeysResult"]["#"])
			)
			{
				$lastKey = null;
				if(
					isset($response["ListPrefixKeysResult"]["#"]["Contents"])
					&& is_array($response["ListPrefixKeysResult"]["#"]["Contents"])
				)
				{
					foreach($response["ListPrefixKeysResult"]["#"]["Contents"] as $a)
					{
						$last_key = $user_name = $a["#"]["UserName"][0]["#"];
						$result[$user_name] = $a["#"]["Prefix"][0]["#"];
					}
				}

				if(
					isset($response["ListPrefixKeysResult"]["#"]["IsTruncated"])
					&& is_array($response["ListPrefixKeysResult"]["#"]["IsTruncated"])
					&& $response["ListPrefixKeysResult"]["#"]["IsTruncated"][0]["#"] === "true"
				)
				{
					if (isset($last_key))
					{
						$marker = $last_key;
						continue;
					}
				}
			}
			break;
		}
		return $result;
	}

	function IsUserExists($UserName)
	{
		$users = $this->ListPrefixKeys($UserName);
		return isset($users[$UserName]);
	}

	function DeletePrefixKey($UserName, $prefix)
	{
		$response = $this->service->SendRequest(
			$this->arBucket["SETTINGS"],
			'DELETE',
			$this->arBucket["BUCKET"],
			'/',
			'?pak=&prefix='.urlencode($prefix).'&username='.urlencode($UserName)
		);
		return $this->service->GetLastRequestStatus() === 200;
	}
}
