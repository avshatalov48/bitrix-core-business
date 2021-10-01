<?php

namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class Share extends Base
{
	public const TYPE = 'SHARE';
	public const POST_TEXT = 'commentAuxShare';

	public function getParamsFromFields($fields = []): array
	{
		$params = [];

		if (!empty($fields['SHARE_DEST']))
		{
			$params['mention'] = $shareDestValue = false;
			$valuesList = explode('|', $fields['SHARE_DEST']);
			foreach ($valuesList as $value)
			{
				if ($value !== 'mention')
				{
					$shareDestValue = $value;
				}
				else
				{
					$params['mention'] = true;
				}
			}

			if ($shareDestValue)
			{
				$destinationList = explode(',', $shareDestValue);
				if (!empty($destinationList))
				{
					foreach ($destinationList as $key => $value)
					{
						$destinationList[$key] = trim($value);
					}
					$params['destinationList'] = $destinationList;
				}
			}
		}

		if (!empty($fields['HIDDEN_DEST']))
		{
			$params['hiddenDestinationList'] = $fields['HIDDEN_DEST'];
		}

		if (
			!empty($fields['PATH_ENTITY_TYPE'])
			&& !empty($fields['PATH_ENTITY_ID'])
		)
		{
			$params['pathEntityType'] = $fields['PATH_ENTITY_TYPE'];
			$params['pathEntityId'] = (int)$fields['PATH_ENTITY_ID'];
		}

		return $params;
	}

	public function getText(): string
	{
		static $parser = null;
		static $availableUsersList = null;

		$result = '';
		$params = $this->params;
		$options = $this->options;
		$newRightsNameList = [];

		if (
			empty($params['destinationList'])
			|| !is_array($params['destinationList'])
		)
		{
			return $result;
		}

		$currentUserExtranet = (
			(!isset($options['bPublicPage']) || !$options['bPublicPage'])
			&& ComponentHelper::isCurrentUserExtranet()
		);
		if (
			$availableUsersList === null
			&& Loader::includeModule('extranet')
		)
		{
			$availableUsersList = ($currentUserExtranet ? \CExtranet::getMyGroupsUsers(SITE_ID) : []);
		}

		foreach ($params['destinationList'] as $destinationCode)
		{
			$hiddenDestination = (
				isset($params['hiddenDestinationList'])
				&& is_array($params['hiddenDestinationList'])
				&& in_array($destinationCode, $params['hiddenDestinationList'])
			);

			if (
				!$hiddenDestination
				|| (
					isset($params['mention'])
					&& $params['mention']
				)
			)
			{
				if (preg_match('/^(SG|U||UA|DR)(\d*)$/', $destinationCode, $matches))
				{
					$entityType = $matches[1];
					$entityId = ($matches[2] ?? false);
					$hiddenEntity = $renderParts = false;

					switch($entityType)
					{
						case 'SG':
							$renderParts = new Livefeed\RenderParts\SonetGroup($options);
							break;
						case 'U':
						case 'UA':
							if (
								$currentUserExtranet
								&& $entityType === 'U'
								&& (
									!isset($params['mention'])
									|| !$params['mention']
								)
								&& !in_array($entityId, $availableUsersList)
							)
							{
								$hiddenEntity = true;
							}
							else
							{
								$renderParts = new Livefeed\RenderParts\User(array_merge($options, [ 'skipLink' => $hiddenDestination ]));
							}
							break;
						case 'DR':
							$renderParts = new Livefeed\RenderParts\Department($options);
							break;
						default:
							$renderParts = false;
					}

					$entityDataFormatted = ($renderParts ? $renderParts->getData((int)$entityId) : false);

					if (
						$entityDataFormatted
						&& isset($entityDataFormatted['name'])
						&& $entityDataFormatted['name'] <> ''
					)
					{
						$newRightsNameList[] = (
							isset($entityDataFormatted['link'])
							&& $entityDataFormatted['link'] <> ''
							&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
							&& (!isset($options['mail']) || !$options['mail'])
								? (
									$entityType === 'U'
									&& (int)$entityId > 0
										? '[USER=' . $entityId . ']' . htmlspecialcharsback($entityDataFormatted['name']) . '[/USER]'
										: '[URL=' . $entityDataFormatted['link'] . ']' . htmlspecialcharsback($entityDataFormatted['name']) . '[/URL]'
								)
								: htmlspecialcharsback($entityDataFormatted['name'])
						);
					}
					elseif ($hiddenEntity)
					{
						$newRightsNameList[] = Loc::getMessage('SONET_COMMENTAUX_SHARE_HIDDEN');
					}
				}
			}
			else
			{
				$newRightsNameList[] = Loc::getMessage('SONET_COMMENTAUX_SHARE_HIDDEN');
			}
		}

		if (empty($newRightsNameList))
		{
			return $result;
		}

		$result .= Loc::getMessage(count($params['destinationList']) > 1 ? 'SONET_COMMENTAUX_SHARE_TEXT_1' : 'SONET_COMMENTAUX_SHARE_TEXT', [
			'#SHARE_LIST#' => implode(', ', $newRightsNameList)
		]);

		if ($parser === null)
		{
			$parser = new \CTextParser();
			$parser->allow = [
				'HTML' => 'N',
				'ANCHOR' => 'Y',
				'USER' => 'Y'
			];
		}

		if (
			!empty($params['pathEntityType'])
			&& !empty($params['pathEntityId'])
		)
		{
			$parser->pathToUserEntityType = $params['pathEntityType'];
			$parser->pathToUserEntityId = (int)$params['pathEntityId'];
		}
		else
		{
			$parser->pathToUserEntityType = false;
			$parser->pathToUserEntityId = false;
		}

		return $parser->convertText($result);
	}

	protected function setRatingNotificationParams(array $fields = []): bool
	{
		$dest = explode('|', $fields['SHARE_DEST']);
		$dest = array_values(array_filter($dest, static function ($item) { return ($item !== 'mention'); }));
		$dest = explode(',', $dest[0]);

		if (empty($dest))
		{
			return false;
		}

		$this->setParams([
			'destinationList' => $dest,
			'hiddenDestinationList' => []
		]);

		return true;
	}

	protected function checkRatingNotificationData(int $userId = 0, array $fields = []): bool
	{
		return (
			$userId > 0
			&& is_array($fields)
			&& isset($fields['SHARE_DEST'])
			&& Loader::includeModule('im')
		);
	}

	protected function getRatingNotificationFollowValue(int $userId = 0, array $ratingVoteParams = [], array $fields = [])
	{
		return \CSocNetLogFollow::getExactValueByRating(
			$userId,
			((!empty($fields['BLOG_ID'])) ? 'BLOG_COMMENT' : 'LOG_COMMENT'),
			$fields['ID']
		);
	}

	protected function getRatingNotificationNotigyTag(array $ratingVoteParams = [], array $fields = []): string
	{
		return 'RATING|' . ($ratingVoteParams['VALUE'] >= 0 ? '' : 'DL|') . (!empty($fields['BLOG_ID']) ? 'BLOG_COMMENT|' : 'LOG_COMMENT|') . $fields['ID'];
	}

	public function checkRecalcNeeded($fields, $params): bool
	{
		$result = false;

		if (!empty($fields['SHARE_DEST']))
		{
			if (ComponentHelper::isCurrentUserExtranet())
			{
				$result = true;
			}
			elseif (
				!empty($params['POST_DATA'])
				&& !empty($params['POST_DATA']['SPERM_HIDDEN'])
			)
			{
				$shareDestValue = false;
				$valuesList = explode('|', $fields['SHARE_DEST']);
				foreach ($valuesList as $value)
				{
					if ($value !== 'mention')
					{
						$shareDestValue = $value;
						break;
					}
				}

				if ($shareDestValue)
				{
					$dest = explode(',', $shareDestValue);
					if (!empty($dest))
					{
						foreach ($dest as $destId)
						{
							if (in_array($destId, $params['POST_DATA']['SPERM_HIDDEN']))
							{
								$result = true;
								break;
							}
						}
					}
				}
			}
		}

		return $result;
	}
}
