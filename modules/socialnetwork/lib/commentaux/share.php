<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class Share extends Base
{
	const TYPE = 'SHARE';
	const POST_TEXT = 'commentAuxShare';

	public function getParamsFromFields($fields = array())
	{
		$params = array();

		if (!empty($fields['SHARE_DEST']))
		{
			$params['mention'] = $shareDestValue = false;
			$valuesList = explode("|", $fields["SHARE_DEST"]);
			foreach($valuesList as $value)
			{
				if ($value != 'mention')
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
					foreach($destinationList as $key => $value)
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

		return $params;
	}

	public function getText()
	{
		static $userNameTemplate = null;
		static $extranet = null;
		static $extranetSite = null;
		static $userPath = null;
		static $groupPath = null;
		static $departmentPath = null;
		static $parser = null;

		$result = '';
		$params = $this->params;
		$options = $this->options;
		$newRightsNameList = array();

		if (
			!empty($params['destinationList'])
			&& is_array($params['destinationList'])
		)
		{
			foreach($params['destinationList'] as $destinationCode)
			{
				$hiddenDestination = (
					isset($params['hiddenDestinationList'])
					&& is_array($params['hiddenDestinationList'])
					&& in_array($destinationCode, $params['hiddenDestinationList'])
				);

				if(
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
						$entityId = (isset($matches[2]) ? $matches[2] : false);

						switch($entityType)
						{
							case 'SG':
								$renderParts = new Livefeed\RenderParts\SonetGroup($options);
								break;
							case 'U':
							case 'UA':
								$renderParts = new Livefeed\RenderParts\User(array_merge($options, array('skipLink' => $hiddenDestination)));
								break;
							case 'DR':
								$renderParts = new Livefeed\RenderParts\Department($options);
								break;
							default:
								$renderParts = false;
						}

						$entityDataFormatted = ($renderParts ? $renderParts->getData(intval($entityId)) : false);

						if (
							$entityDataFormatted
							&& isset($entityDataFormatted['name'])
							&& strlen($entityDataFormatted['name']) > 0
						)
						{
							$newRightsNameList[] = (
								isset($entityDataFormatted['link'])
								&& strlen($entityDataFormatted['link']) > 0
								&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
								&& (!isset($options['mail']) || !$options['mail'])
									? (
										$entityType == "U"
										&& intval($entityId) > 0
											? "[USER=".$entityId."]".htmlspecialcharsback($entityDataFormatted['name'])."[/USER]"
											: "[URL=".$entityDataFormatted['link']."]".htmlspecialcharsback($entityDataFormatted['name'])."[/URL]"
									)
									: htmlspecialcharsback($entityDataFormatted['name'])
							);
						}
					}
				}
				else
				{
					$newRightsNameList[] = Loc::getMessage("SONET_COMMENTAUX_SHARE_HIDDEN");
				}
			}

			if (!empty($newRightsNameList))
			{
				$result .= Loc::getMessage(count($params['destinationList']) > 1 ? "SONET_COMMENTAUX_SHARE_TEXT_1" : "SONET_COMMENTAUX_SHARE_TEXT", array(
					"#SHARE_LIST#" => implode(", ", $newRightsNameList)
				));

				if ($parser === null)
				{
					$parser = new \CTextParser();
					$parser->allow = array("HTML" => "N", "ANCHOR" => "Y", "USER" => "Y");
				}
				$result = $parser->convertText($result);
			}
		}

		return $result;
	}

	public function sendRatingNotification($fields = array(), $ratingVoteParams = array())
	{
		$userId = (
			is_array($ratingVoteParams)
			&& isset($ratingVoteParams['OWNER_ID'])
				? intval($ratingVoteParams['OWNER_ID'])
				: 0
		);

		if (
			$userId > 0
			&& is_array($fields)
			&& isset($fields["SHARE_DEST"])
			&& Loader::includeModule('im')
		)
		{
			$dest = explode(",", $fields["SHARE_DEST"]);

			if (!empty($dest))
			{
				$this->setParams(array(
					'destinationList' => $dest,
					'hiddenDestinationList' => array()
				));

				$followValue = \CSocNetLogFollow::getExactValueByRating(
					$userId,
					'BLOG_COMMENT',
					$fields['ID']
				);

				if ($followValue != "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';
					$ratingVoteParams["ENTITY_MESSAGE"] = $this->getText();

					$messageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => intval($ratingVoteParams['USER_ID']),
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "main",
						"NOTIFY_EVENT" => "rating_vote",
						"NOTIFY_TAG" => "RATING|".($ratingVoteParams['VALUE'] >= 0 ? "" : "DL|")."BLOG_COMMENT|".$fields['ID'],
						"NOTIFY_MESSAGE" => \CIMEvent::getMessageRatingVote($ratingVoteParams),
						"NOTIFY_MESSAGE_OUT" => \CIMEvent::getMessageRatingVote($ratingVoteParams, true)
					);

					\CIMNotify::add($messageFields);
				}
			}
		}
	}

	public function checkRecalcNeeded($fields, $params)
	{
		$result = false;

		if (
			!empty($fields["SHARE_DEST"])
			&& !empty($params["POST_DATA"])
			&& !empty($params["POST_DATA"]["SPERM_HIDDEN"])
		)
		{
			$shareDestValue = false;
			$valuesList = explode("|", $fields["SHARE_DEST"]);
			foreach($valuesList as $value)
			{
				if ($value != 'mention')
				{
					$shareDestValue = $value;
					break;
				}
			}

			if ($shareDestValue)
			{
				$dest = explode(",", $shareDestValue);
				if(!empty($dest))
				{
					foreach($dest as $destId)
					{
						if(in_array($destId, $params["POST_DATA"]["SPERM_HIDDEN"]))
						{
							$result = true;
							break;
						}
					}
				}
			}
		}

		return $result;
	}
}