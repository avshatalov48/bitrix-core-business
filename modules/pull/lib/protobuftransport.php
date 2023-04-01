<?php

namespace Bitrix\Pull;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\BinaryString;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Pull\Protobuf;
use Protobuf\MessageCollection;

class ProtobufTransport
{
	protected $hits = 0;
	protected $bytes = 0;

	/**
	 * @param array $messages Messages to send to the pull server.
	 */
	public static function sendMessages(array $messages, array $options = []): Result
	{
		$result = new Result();
		if(!Config::isProtobufUsed())
		{
			throw new SystemException("Sending messages in protobuf format is not supported by queue server");
		}

		$protobufMessages = static::convertMessages($messages);
		$requests = static::createRequests($protobufMessages);
		$requestBatches = static::createRequestBatches($requests);

		$queueServerUrl = $options['serverUrl'] ?? Config::getPublishUrl();
		$queueServerUrl = \CHTTP::urlAddParams($queueServerUrl, ["binaryMode" => "true"]);
		foreach ($requestBatches as $requestBatch)
		{
			$urlWithSignature = $queueServerUrl;
			$httpClient = new HttpClient(["streamTimeout" => 1]);
			$bodyStream = $requestBatch->toStream();
			if(\CPullOptions::IsServerShared())
			{
				$signature = \CPullChannel::GetSignature($bodyStream->getContents());
				$urlWithSignature = \CHTTP::urlAddParams($urlWithSignature, ["signature" => $signature]);
			}

			$httpClient->disableSslVerification();
			$sendResult = $httpClient->query(HttpClient::HTTP_POST, $urlWithSignature, $bodyStream);
			if (!$sendResult)
			{
				$errorCode = array_key_first($httpClient->getError());
				$errorMsg = $httpClient->getError()[$errorCode];
				$result->addError(new \Bitrix\Main\Error($errorMsg, $errorCode));
			}
		}

		return $result;
	}

	/**
	 * Returns online status for each known channel in the list of private channel ids.
	 * @param array $channels Array of private channel ids.
	 * @return array Return online status for known channels in format [channelId => bool].
	 */
	public static function getOnlineChannels(array $channels)
	{
		$result = [];
		$maxChannelsPerRequest = \CPullOptions::GetMaxChannelsPerRequest();
		$channelBatches = [];
		$currentChannelBatch = 0;
		$requestsInChannelBatch = 0;
		foreach ($channels as $channelId)
		{
			$channel = new Protobuf\ChannelId();
			$channel->setId(hex2bin($channelId));
			$channel->setIsPrivate(true);

			$requestsInChannelBatch++;

			if($requestsInChannelBatch >= $maxChannelsPerRequest)
			{
				$currentChannelBatch++;
				$requestsInChannelBatch = 1;
			}
			$channelBatches[$currentChannelBatch][] = $channel;
		}

		$requests = [];
		foreach ($channelBatches as $channelBatchNumber => $channelBatch)
		{
			$channelsStatsRequest = new Protobuf\ChannelStatsRequest();
			$channelsStatsRequest->setChannelsList(new MessageCollection($channelBatch));

			$request = new Protobuf\Request();
			$request->setChannelStats($channelsStatsRequest);
			$requests[] = $request;
		}

		$queueServerUrl = \CHTTP::urlAddParams(Config::getPublishUrl(), ["binaryMode" => "true"]);

		$requestBatches = static::createRequestBatches($requests);
		foreach ($requestBatches as $requestBatch)
		{
			$http = new HttpClient();
			$http->disableSslVerification();

			$urlWithSignature = $queueServerUrl;
			$bodyStream = $requestBatch->toStream();
			if(\CPullOptions::IsServerShared())
			{
				$signature = \CPullChannel::GetSignature($bodyStream->getContents());
				$urlWithSignature = \CHTTP::urlAddParams($urlWithSignature, ["signature" => $signature]);
			}

			$binaryResponse = $http->post($urlWithSignature, $bodyStream);

			if($http->getStatus() != 200)
			{
				return [];
			}
			if(strlen($binaryResponse) == 0)
			{
				return [];
			}

			try
			{
				$responseBatch = Protobuf\ResponseBatch::fromStream($binaryResponse);
			}
			catch (\Exception $e)
			{
				return [];
			}
			$responses = $responseBatch->getResponsesList();

			$response = $responses[0];
			if(!($response instanceof Protobuf\Response))
			{
				return[];
			}

			if ($response->hasChannelStats())
			{
				$stats = $response->getChannelStats();
				/** @var Protobuf\ChannelStats $channel */
				foreach ($stats->getChannelsList() as $channel)
				{
					if($channel->getIsOnline())
					{
						$channelId = bin2hex($channel->getId());
						$result[$channelId] = true;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $messages
	 * @return Protobuf\IncomingMessage[]
	 */
	protected static function convertMessages(array $messages)
	{
		$result = [];

		foreach ($messages as $message)
		{
			$event = $message['event'] ?? null;
			if(!is_array($message['channels']) || count($message['channels']) == 0 || !isset($event['module_id']) || !isset($event['command']))
			{
				continue;
			}

			$result = array_merge($result, static::convertMessage($message['channels'], $event));
		}

		return $result;
	}

	/**
	 * @param array $channels
	 * @param array $event
	 *
	 * @return Protobuf\IncomingMessage[]
	 */
	protected static function convertMessage(array $channels, array $event)
	{
		$result = [];

		$extra = is_array($event['extra']) ? $event['extra'] : [];

		$body = Common::jsonEncode(array(
			'module_id' => $event['module_id'],
			'command' => $event['command'],
			'params' => $event['params'] ?: [],
			'extra' => $extra
		));

		// for statistics
		$messageType = "{$event['module_id']}_{$event['command']}";
		$messageType = preg_replace("/[^\w]/", "", $messageType);

		$maxChannelsPerRequest = \CPullOptions::GetMaxChannelsPerRequest();
		$receivers = [];
		foreach ($channels as $channel)
		{
			$receiver = new Protobuf\Receiver();
			$receiver->setIsPrivate(true);
			$receiver->setId(hex2bin($channel));
			$receivers[] = $receiver;

			if(count($receivers) === $maxChannelsPerRequest)
			{
				$message = new Protobuf\IncomingMessage();
				$message->setReceiversList(new MessageCollection($receivers));
				$message->setExpiry($event['expiry']);
				$message->setBody($body);
				$message->setType($messageType); // for statistics

				$result[] = $message;
				$receivers = [];
			}
		}

		if(count($receivers) > 0)
		{
			$message = new Protobuf\IncomingMessage();
			$message->setReceiversList(new MessageCollection($receivers));
			$message->setExpiry($event['expiry']);
			$message->setBody($body);
			$message->setType($messageType); // for statistics

			$result[] = $message;
		}

		return $result;
	}

	/**
	 * @param Protobuf\Request[] $requests
	 * @return Protobuf\RequestBatch[]
	 */
	protected static function createRequestBatches(array $requests)
	{
		$result = [];
		foreach ($requests as $request)
		{
			$batch = new Protobuf\RequestBatch();
			$batch->addRequests($request);
			$result[] = $batch;
		}

		return $result;
	}

	/**
	 * @param Protobuf\IncomingMessage[] $messages
	 * @return Protobuf\Request[]
	 */
	protected static function createRequests(array $messages)
	{
		$result = [];

		$maxPayload = \CPullOptions::GetMaxPayload() - 200;
		$maxMessages = \CPullOptions::GetMaxMessagesPerRequest();

		$currentMessageBatch = [];
		$currentBatchSize = 0;

		foreach ($messages as $message)
		{
			$messageSize = static::getMessageSize($message);
			if($currentBatchSize + $messageSize >= $maxPayload || count($currentMessageBatch) >= $maxMessages)
			{
				// finalize current request and start a new one
				$incomingMessagesRequest = new Protobuf\IncomingMessagesRequest();
				$incomingMessagesRequest->setMessagesList(new MessageCollection($currentMessageBatch));
				$request = new Protobuf\Request();
				$request->setIncomingMessages($incomingMessagesRequest);
				$result[] = $request;

				$currentMessageBatch = [];
				$messageSize = 0;
			}

			// add the request to the current batch
			$currentMessageBatch[] = $message;
			$currentBatchSize += $messageSize;
		}

		if(!empty($currentMessageBatch))
		{
			$incomingMessagesRequest = new Protobuf\IncomingMessagesRequest();
			$incomingMessagesRequest->setMessagesList(new MessageCollection($currentMessageBatch));
			$request = new Protobuf\Request();
			$request->setIncomingMessages($incomingMessagesRequest);
			$result[] = $request;
		}

		return $result;
	}

	/**
	 * @param Protobuf\IncomingMessage $message
	 * @param $maxReceivers
	 * @return Protobuf\IncomingMessage[]
	 */
	protected static function splitReceivers(Protobuf\IncomingMessage $message, $maxReceivers)
	{
		$receivers = $message->getReceiversList();
		if(count($receivers) <= $maxReceivers)
		{
			return [$message];
		}

		$result = [];
		$currentReceivers = [];

		foreach ($receivers as $receiver)
		{
			if(count($currentReceivers) == $maxReceivers)
			{
				$subMessage = new Protobuf\IncomingMessage();
				$subMessage->setBody($message->getBody());
				$subMessage->setExpiry($message->getExpiry());
				$subMessage->setReceiversList(new MessageCollection($currentReceivers));
				$result[] = $subMessage;
				$currentReceivers = [];
			}

			$currentReceivers[] = $receiver;
		}

		if(count($currentReceivers) > 0)
		{
			$subMessage = new Protobuf\IncomingMessage();
			$subMessage->setBody($message->getBody());
			$subMessage->setExpiry($message->getExpiry());
			$subMessage->setReceiversList(new MessageCollection($currentReceivers));
			$result[] = $subMessage;
		}

		return $result;
	}

	protected static function getMessageSize(Protobuf\IncomingMessage $message)
	{
		$config = \Protobuf\Configuration::getInstance();
		return $message->serializedSize($config->createComputeSizeContext());
	}
}