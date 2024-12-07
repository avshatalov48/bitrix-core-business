<?php
namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\Constants;
use Bitrix\MessageService\Internal\Entity\ChannelTable;

abstract class EdnaUtils implements EdnaRu
{
	protected string $providerId;
	protected Providers\ExternalSender $externalSender;
	protected Providers\OptionManager $optionManager;

	abstract public function getMessageTemplates(string $subject = ''): Result;
	abstract protected function initializeDefaultExternalSender(): Providers\ExternalSender;

	public function __construct(string $providerId, Providers\OptionManager $optionManager)
	{
		$this->providerId = $providerId;
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
			if (is_array($cascade))
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
		}

		$result->addError(new Error('Not cascade'));

		return $result;
	}


	private function gelAllChannelList(): Result
	{
		$this->externalSender->setApiKey($this->optionManager->getOption(InternalOption::API_KEY));
		return $this->externalSender->callExternalMethod(Constants\Method::GET_CHANNELS);
	}

	/**
	 * Loads channels from provider.
	 *
	 * @param string $channelType
	 * @return array
	 */
	public function updateSavedChannelList(string $channelType): array
	{
		$fromList = [];
		$activeChannelListResult = $this->getActiveChannelList($channelType);
		if ($activeChannelListResult->isSuccess())
		{
			$registeredSubjectIdList = $this->optionManager->getOption(Providers\Constants\InternalOption::SENDER_ID, []);
			$channels = [];
			foreach ($activeChannelListResult->getData() as $channel)
			{
				if (in_array((int)$channel['subjectId'], $registeredSubjectIdList, true))
				{
					$fromList[] = [
						'id' => $channel['subjectId'],
						'name' => $channel['name'],
						'channelPhone' => $channel['channelAttribute'] ?? '',
					];
					$channels[] = [
						'SENDER_ID' => $this->providerId,
						'EXTERNAL_ID' => $channel['subjectId'],
						'TYPE' => $channelType,
						'NAME' => $channel['name'] ?? '',
						'ADDITIONAL_PARAMS' => [
							'channelAttribute' => $channel['channelAttribute'] ?? ''
						],
					];
				}
			}

			if (count($channels) > 0)
			{
				ChannelTable::reloadChannels($this->providerId, $channelType, $channels);
			}
			else
			{
				ChannelTable::deleteByFilter([
					'=SENDER_ID' => $this->providerId,
					'=TYPE' => $channelType,
				]);
			}
		}

		return $fromList;
	}

	public function sendTemplate(string $name, string $text, array $examples = [], ?string $langCode = null): Result
	{
		return (new Result())->addError(new Error('This provider does not support template creation'));
	}

	protected function validateLanguage(string $langCode): bool
	{
		$langs = [
			'af', 'sq', 'ar', 'az', 'bn',
			'bg', 'ca','zh_CN', 'zh_HK', 'zh_TW',
			'hr', 'cs', 'da', 'nl', 'en',
			'en_GB', 'en_US', 'et', 'fil', 'fi',
			'fr', 'ka', 'de', 'el', 'gu',
			'ha', 'he', 'hi', 'hu', 'id',
			'ga', 'it', 'ja', 'kn', 'kk',
			'rw_RW', 'ko', 'ky_KG', 'lo', 'lv',
			'lt', 'mk', 'ms', 'ml', 'mr',
			'nb', 'fa', 'pl', 'pt_BR', 'pt_PT',
			'pa', 'ro', 'ru', 'sr', 'sk',
			'sl', 'es', 'es_AR', 'es_ES', 'es_MX',
			'sw', 'sv', 'ta', 'te', 'th',
			'tr', 'uk', 'ur', 'uz', 'vi', 'zu',
		];

		if (in_array($langCode, $langs, true))
		{
			return true;
		}

		return false;
	}

	protected function validateTemplateName(string $name): Result
	{
		$result = new Result();

		if (!preg_match('/^[0-9a-z_]{1,60}$/i', $name))
		{
			return $result->addError(new Error('The template name can only contain Latin letters, numbers and underscore (_). The maximum number of characters is 60'));
		}

		return $result;
	}

	public function clearCache(string $key): void
	{
		$cacheManager = new Providers\CacheManager($this->providerId);
		$cacheManager->deleteValue($key);
	}
}