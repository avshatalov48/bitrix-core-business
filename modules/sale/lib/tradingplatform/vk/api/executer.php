<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Api;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Web\Json;
use Bitrix\Main\IO;


class Executer
{
	private $api;
	private $scriptPath;

	public function __construct($api)
	{
		if (empty($api))
			throw new ArgumentNullException('api');

		$this->scriptPath = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/sale/lib/tradingplatform/vk/api/scripts";
		$this->api = $api;
	}


	/**
	 * Load .vks script from file
	 *
	 * @param $name
	 * @return bool|null|string
	 */
	private function getScript($name)
	{
		$filePath = $this->scriptPath . '/' . $name . '.vks';
		if (IO\File::isFileExists($filePath))
		{
			$script = IO\File::getFileContents($filePath);
//			$script = file_get_contents($filePath);

			return $script;
		}

		return NULL;
	}


	/**
	 * Main method to call vk-script from .vks files
	 *
	 * @param $methodName - must be string in format "execute" + name of script file
	 * @param $arguments - various array of scripts arguments
	 * @return mixed response from VK
	 */
	public function __call($methodName, $arguments)
	{
//		prepare METHOD name
		$methodName = strtolower($methodName);
		if (strpos($methodName, 'execute') == 0)
		{
			$methodName = str_replace("execute", "", $methodName);
		}

		$script = $this->getScript($methodName);
		if (count($arguments))
		{
			$script = $this->prepareParams($script, $arguments[0]);
		}
		$response = $this->api->run('execute', array('code' => $script));

		return $response;
	}


	/**
	 * Replace params names to params values from in script string.
	 * Return encoded script string in JSON
	 *
	 * @param $script
	 * @param $params
	 * @return mixed
	 */
	private function prepareParams($script, $params)
	{
		foreach ($params as $key => $value)
		{
			if (is_array($value))
			{
				$value = Json::encode($value);
				$value = $this->decodeMultibyteUnicode($value);    //vkscript dont understand \uXXXX format, decoding
			}
			$script = str_replace('%' . strtoupper($key) . '%', $value, $script);
		}

		return $script;
	}

	/**
	 * Decode \uXXXX from JSON-converted string, because VK has lenght limit for values.
	 *
	 * @param $str
	 * @return mixed
	 */
	private function decodeMultibyteUnicode($str)
	{
		$str = preg_replace_callback('/\\\\u(\w{4})/', function ($matches)
		{
			return html_entity_decode('&#x' . $matches[1] . ';', null, 'UTF-8');
		}, $str);

		return $str;
	}
}