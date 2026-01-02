<?php

namespace Bitrix\Vote\Integration\Im;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\Attachment\UfCompatibleChannelCreator;
use Bitrix\Vote\Config\Feature;
use Bitrix\Vote\Integration\Im\Result\ImVoteSendResult;
use Bitrix\Vote\Integration\Pull\VoteChangesSender;
use Bitrix\Vote\Vote;
use Bitrix\Vote\Vote\EventLimits;

class ImVote
{
	public const MESSAGE_COMPONENT_ID = 'VoteMessage';
	public const MESSAGE_COMPONENT_PARAM_VOTE_ID = 'id';

	public static function isAvailable(): bool
	{
		return Feature::instance()->isImIntegrationEnabled() && Loader::includeModule('im');
	}

	public static function getMinAnswersCount(): int
	{
		return 2;
	}

	public static function getMaxQuestionsCount(): int
	{
		return 1;
	}

	public static function getMaxAnswersCount(): int
	{
		return 10;
	}

	public static function sendVote(
		int $chatId,
		int $ownerUserId,
		array $voteFields,
		?string $templateId = null,
	): ImVoteSendResult|Result
	{
		if (!static::isAvailable())
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_NOT_AVAILABLE')));
		}

		$channelId = self::getOrCreateImMessageChannel();
		if ($channelId <= 0)
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_CHANNEL_FAILED')));
		}

		$voteFields = self::prepareVoteData($channelId, $voteFields, $ownerUserId);
		$validateResult = self::validateVoteData($voteFields); // check modified $voteFields
		if (!$validateResult->isSuccess())
		{
			return $validateResult;
		}

		try
		{
			$voteId = Vote::saveData(0, $voteFields);
		}
		catch (ArgumentException $exception)
		{
			return (new Result())->addError(new Error($exception->getMessage()));
		}

		if ($voteId <= 0)
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_SAVE_ERROR')));
		}

		$savedVoteData = Vote::getData($voteId);
		if (empty($savedVoteData))
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_SAVE_ERROR')));
		}

		$messageId = \CIMMessenger::Add([
			'MESSAGE_TYPE' => IM_MESSAGE_CHAT,
			'TO_CHAT_ID' => $chatId,
			'FROM_USER_ID' => $ownerUserId,
			'MESSAGE' => self::getFallbackText($savedVoteData),
			'TEMPLATE_ID' => $templateId,
			'PARAMS' => [
				'COMPONENT_ID' => self::MESSAGE_COMPONENT_ID,
				'COMPONENT_PARAMS' => [
					self::MESSAGE_COMPONENT_PARAM_VOTE_ID => $voteId,
					'data' => self::formatVoteData($savedVoteData),
				],
			],
		]);

		if (!$messageId)
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_MESSAGE_FAILED')));
		}

		(new VoteChangesSender())->addUserWatch($ownerUserId, $voteId);

		return new ImVoteSendResult($messageId, $voteId);
	}

	public static function getOrCreateImMessageChannel(): int
	{
		return UfCompatibleChannelCreator::getOrCreateChannel('IM_MESSAGE_CHANNEL');
	}

	private static function getFallbackText(array $voteFields): ?string
	{
		if (isset($voteFields['QUESTIONS']) && is_array($voteFields['QUESTIONS']))
		{
			$firstKey = array_key_first($voteFields['QUESTIONS']);

			return trim($voteFields['QUESTIONS'][$firstKey]['QUESTION'] ?? '');
		}

		return null;
	}

	private static function prepareVoteData(int $channelId, array $voteFields, int $ownerId): array
	{
		$voteFields['CHANNEL_ID'] = $channelId;
		$voteFields['DATE_START'] = new DateTime();
		$voteFields['DATE_END'] = (new DateTime())->add("10Y");
		$voteFields['UNIQUE_TYPE'] = EventLimits::BY_USER_ID | EventLimits::BY_USER_AUTH;
		$voteFields['AUTHOR_ID'] = $ownerId;
		$voteFields['OPTIONS'] = $voteFields['OPTIONS'] ?? 0;

		return $voteFields;
	}

	private static function validateVoteData(array &$voteFields): Result
	{
		try
		{
			$check = Vote::checkData($voteFields);
		}
		catch (ArgumentException $exception)
		{
			return (new Result())->addError(new Error($exception->getMessage()));
		}

		if (empty($voteFields['QUESTIONS']) || !is_array($voteFields['QUESTIONS']))
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_QUESTIONS_EMPTY')));
		}

		if (!$check)
		{
			return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_NOT_VALID')));
		}

		if (count($voteFields['QUESTIONS']) > self::getMaxQuestionsCount())
		{
			return (new Result())->addError(new Error(
				Loc::getMessage('VOTE_INTEGRATION_IM_TO_MANY_QUESTIONS', [
					'#COUNT#' => self::getMaxQuestionsCount(),
				])
			));
		}

		foreach ($voteFields['QUESTIONS'] as $question)
		{
			if (
				empty($question['ANSWERS'])
				|| !is_array($question)
				|| count($question['ANSWERS']) < self::getMinAnswersCount()
			)
			{
				return (new Result())->addError(new Error(Loc::getMessage('VOTE_INTEGRATION_IM_MIN_ANSWERS', [
					'#MIN_ANSWERS_COUNT#' => self::getMinAnswersCount(),
				])));
			}

			if (
				!empty($question['ANSWERS'])
				&& is_array($question['ANSWERS'])
				&& count($question['ANSWERS']) > self::getMaxAnswersCount()
			)
			{
				return (new Result())->addError(new Error(
					Loc::getMessage('VOTE_INTEGRATION_IM_TO_MANY_ANSWERS', [
						'#COUNT#' => self::getMaxAnswersCount(),
					])
				));
			}
		}

		return new Result();
	}

	private static function formatVoteData(array $fields): array
	{
		return [
			'ANONYMITY' => (int)$fields['ANONYMITY'],
			'OPTIONS' => (int)$fields['OPTIONS'],
			'QUESTIONS' => array_map(
				fn(array $question) => self::formatVoteQuestion($question),
				$fields['QUESTIONS'],
			),
		];
	}

	private static function formatVoteQuestion(array $question): array
	{
		return [
			'ID' => (int)$question['ID'],
			'QUESTION' => $question['QUESTION'],
			'FIELD_TYPE' => (int)$question['FIELD_TYPE'],
			'ANSWERS' => array_map(
				fn(array $answer) => self::formatVoteAnswer($answer),
				$question['ANSWERS'],
			),
		];
	}

	private static function formatVoteAnswer(array $answer): array
	{
		return [
			'ID' => (int)$answer['ID'],
			'MESSAGE' => $answer['MESSAGE'],
			'REACTION' => $answer['REACTION'] ?? null,
		];
	}
}