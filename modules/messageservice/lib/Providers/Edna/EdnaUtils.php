<?php
namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\Constants;

abstract class EdnaUtils implements EdnaRu
{
	protected Providers\ExternalSender $externalSender;
	protected Providers\OptionManager $optionManager;

	abstract public function getMessageTemplates(string $subject = ''): Result;
	abstract protected function initializeDefaultExternalSender(): Providers\ExternalSender;

	public function __construct(Providers\OptionManager $optionManager)
	{
		$this->optionManager = $optionManager;
		$this->externalSender = $this->initializeDefaultExternalSender();
	}

	/**
	 * @see https://docs.edna.ru/kb/channel-profile/
	 * @param string $imType
	 * @see \Bitrix\MessageService\Providers\Edna\Constants\ChannelType
	 * @return Result
	 */
	public function getChannelList(string $imType): Result
	{
		if (!in_array($imType, Constants\ChannelType::getAllTypeList(), true))
		{
			return (new Result())->addError(new Error('Incorrect imType'));
		}

		$channelResult = $this->gelAllChannelList();
		if (!$channelResult->isSuccess())
		{
			return (new Result())->addError(new Error('Edna service error'));
		}

		$channelList = [];
		foreach ($channelResult->getData() as $channel)
		{
			if (is_array($channel) && isset($channel['type']) && $channel['type'] === $imType)
			{
				$channelList[] = $channel;
			}
		}
		if (empty($channelList))
		{
			return (new Result())->addError(new Error("There are no $imType channels in your profile"));
		}

		$result = new Result();
		$result->setData($channelList);

		return $result;
	}

	/**
	 * @see https://docs.edna.ru/kb/poluchenie-informacii-o-kaskadah/
	 * @return Result
	 */
	public function getCascadeList(): Result
	{
		$apiResult = $this->externalSender->callExternalMethod(Constants\Method::GET_CASCADES, [
			'offset' => 0,
			'limit' => 0
		]);

		return $apiResult;
	}

	/**
	 * @see https://docs.edna.ru/kb/callback-set/
	 * @param string $callbackUrl
	 * @param array $callbackTypes
	 * @param int|null $subjectId
	 * @return Result
	 */
	public function setCallback(string $callbackUrl, array $callbackTypes, ?int $subjectId = null): Result
	{
		$typeList = Constants\CallbackType::getAllTypeList();

		$requestParams = [];
		foreach ($callbackTypes as $callbackType)
		{
			if (in_array($callbackType, $typeList, true))
			{
				$requestParams[$callbackType] = $callbackUrl;
			}
		}
		if (empty($requestParams))
		{
			return (new Result())->addError(new Error('Invalid callback types passed'));
		}

		if ($subjectId)
		{
			$requestParams['subjectId'] = $subjectId;
		}
		$this->externalSender->setApiKey($this->optionManager->getOption(InternalOption::API_KEY));

		return $this->externalSender->callExternalMethod('callback/set', $requestParams);
	}

	public function getActiveChannelList(string $imType): Result
	{
		$channelListResult = $this->getChannelList($imType);
		if (!$channelListResult->isSuccess())
		{
			return $channelListResult;
		}

		$activeChannelList = [];
		foreach ($channelListResult->getData() as $channel)
		{
			if (isset($channel['active'], $channel['subjectId']) && $channel['active'] === true)
			{
				$activeChannelList[] = $channel;
			}
		}

		if (empty($activeChannelList))
		{
			return (new Result())->addError(new Error('There are no active channels'));
		}

		return (new Result())->setData($activeChannelList);
	}

	public function checkActiveChannelBySubjectIdList(array $subjectIdList, string $imType): bool
	{
		if (empty($subjectIdList))
		{
			return false;
		}

		$channelResult = $this->getChannelList($imType);
		if (!$channelResult->isSuccess())
		{
			return false;
		}

		$checkedChannels = [];
		foreach ($channelResult->getData() as $channel)
		{
			if (
				isset($channel['active'], $channel['subjectId'])
				&& $channel['active'] === true
				&& in_array($channel['subjectId'], $subjectIdList, true)
			)
			{
				$checkedChannels[] = $channel['subjectId'];
			}
		}

		return count($checkedChannels) === count($subjectIdList);
	}

	/**
	 * @param int|string $subject
	 * @param callable $subjectComparator
	 * @return Result
	 */
	public function getCascadeIdFromSubject($subject, callable $subjectComparator): Result
	{
		$apiResult = $this->getCascadeList();
		if (!$apiResult->isSuccess())
		{
			return $apiResult;
		}

		$apiData = $apiResult->getData();
		$result = new Result();
		foreach ($apiData as $cascade)
		{
			if ($cascade['status'] !== 'ACTIVE' || $cascade['stagesCount'] > 1)
			{
				continue;
			}
			if ($subjectComparator($cascade['stages'][0]['subject'], $subject))
			{
				$result->setData(['cascadeId' => $cascade['id']]);

				return $result;
			}
		}

		$result->addError(new Error('Not cascade'));

		return $result;
	}


	private function gelAllChannelList(): Result
	{
		$this->externalSender->setApiKey($this->optionManager->getOption(InternalOption::API_KEY));
		return $this->externalSender->callExternalMethod(Constants\Method::GET_CHANNELS);
	}


}