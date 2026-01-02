<?php

namespace Bitrix\Vote\Integration\Main;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\UserTable;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Service\AttachedVoteResultUrlService;
use Bitrix\Vote\Vote\Anonymity;
use Bitrix\Vote\Vote\Option;

final class UrlPreview
{
	public static function checkUserReadAccess(array $params, int $userId): bool
	{
		$signedAttachId = (string)($params['signedAttachId'] ?? '');
		if (!$signedAttachId || !$userId)
		{
			return false;
		}

		try
		{
			(new AttachedVoteResultUrlService())->getAttachByUrlId($signedAttachId);

			return true;
		}
		catch (ArgumentTypeException|BadSignatureException|ObjectNotFoundException)
		{
			return false;
		}
	}

	public static function getImAttach(array $params): \CIMMessageParamAttach|false
	{
		$signedAttachId = (string)($params['signedAttachId'] ?? '');
		if (!$signedAttachId || !Loader::includeModule('im'))
		{
			return false;
		}

		try
		{
			$attach = (new AttachedVoteResultUrlService())->getAttachByUrlId($signedAttachId);

			$messageAttach = new \CIMMessageParamAttach();
			$messageAttach->AddUser([
				'NAME' => Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_TITLE'),
				'LINK' => (new AttachedVoteResultUrlService())->getAbsoluteResultUrl($signedAttachId),
			]);
			$messageAttach->AddDelimiter();
			$messageAttach->AddGrid(self::getAttachGrid($attach));

			return $messageAttach;
		}
		catch (ArgumentTypeException|BadSignatureException|ObjectNotFoundException)
		{
			return false;
		}
	}

	private static function getAttachGrid(Attach $attach): array
	{
		$rows = [];
		$authorName = self::getAuthorFormattedName($attach);
		if ($authorName)
		{
			$rows[] = self::getGridRow(
				name: Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_OWNER'),
				value: $authorName
			);
		}

		$rows[] = self::getGridRow(
			name: Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_QUESTION'),
			value: self::getFirstQuestion($attach),
		);

		$rows[] = self::getGridRow(
			name: Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_INFO'),
			value: self::getInfo($attach),
		);

		return $rows;
	}

	private static function getGridRow(?string $name, ?string $value): array
	{
		return [
			'NAME' => $name,
			'VALUE' => $value,
			'DISPLAY' => 'COLUMN',
			'WIDTH' => 120,
		];
	}

	private static function getAuthorFormattedName(Attach $attach): ?string
	{
		$authorId = (int)($attach['AUTHOR_ID'] ?? 0);
		if ($authorId <= 0)
		{
			return null;
		}

		$userFields = UserTable::query()
			->whereIn('ID', $authorId)
			->setSelect([
			   'ID',
			   'NAME',
			   'LAST_NAME',
			   'SECOND_NAME',
			   'LOGIN',
			])
			->setLimit(1)
			->exec()
			->fetch()
		;

		if (empty($userFields))
		{
			return null;
		}

		return \CUser::FormatName(
			\CSite::GetNameFormat(false),
			$userFields,
			true, false
		);
	}

	private static function getFirstQuestion(Attach $attach): string
	{
		$firstKey = array_key_first($attach['QUESTIONS']);

		return (string)($attach['QUESTIONS'][$firstKey]['QUESTION'] ?? '');
	}

	private static function getInfo(Attach $attach): ?string
	{
		$anonymity = $attach->isPublicVote()
			? Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_PUBLIC')
			: Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_ANONYMOUS')
		;

		if ($attach->isFinished())
		{
			return Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_FINISHED_MGSVER1', ['#ANONYMITY#' => $anonymity]);
		}

		$options = (int)($attach['OPTIONS'] ?? 0);
		$allowRevote = $options & Option::ALLOW_REVOTE;

		return $allowRevote
			? $anonymity
			: Loc::getMessage('VOTE_INTEGRATION_MAIN_URL_PREVIEW_RESTRICT_REVOTE', ['#ANONYMITY#' => $anonymity])
		;
	}
}