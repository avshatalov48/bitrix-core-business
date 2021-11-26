<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Sender\Integration;

use Bitrix\Main;
use Bitrix\Main\Entity as MainEntity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Internals\Model\LetterTable;
use Bitrix\Sender\Message;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Security\Agreement;
use Bitrix\Sender\Security\User;
use Bitrix\Sender\Templates;

Loc::loadMessages(__FILE__);

/**
 * Class EventHandler
 * @package Bitrix\Sender\Integration\Crm
 */
class EventHandler
{
	/**
	 * Handler of event sender/onSenderPresetList.
	 *
	 * @return array
	 */
	public static function onSenderPresetList()
	{
		$list = array();

		if (ModuleManager::isModuleInstalled('crm'))
		{
			$list[] = 'Bitrix\Sender\Integration\Crm\Preset\Segment';
		}

		return $list;
	}

	/**
	 * Handler of event sender/onSenderPresetList.
	 *
	 * @return array
	 */
	public static function onSenderCompanyCard()
	{
		$list = array();

		if (Loader::includeModule('crm'))
		{
			$list[] = Crm\CompanyCard::getArray();
		}

		return $list;
	}

	/**
	 * Handler of event sender/OnAfterPostingSendRecipient.
	 *
	 * @param array $eventData Event.
	 * @param Entity\Letter $letter Letter.
	 * @return void
	 */
	public static function onAfterPostingSendRecipient(array $eventData, Entity\Letter $letter)
	{
		if (ModuleManager::isModuleInstalled('crm'))
		{
			Crm\EventHandler::onAfterPostingSendRecipient($eventData, $letter);
		}

		if (Bitrix24\Service::isCloud() && $eventData['SEND_RESULT'] && $letter->getMessage()->getCode() === Message\iBase::CODE_MAIL)
		{
			Bitrix24\Limitation\DailyLimit::increment();
		}
	}

	/**
	 * Handler of event sender/OnAfterPostingSendRecipientMultiple.
	 *
	 * @param array $eventDataArray Event[].
	 * @param Entity\Letter $letter Letter.
	 * @return void
	 */
	public static function onAfterPostingSendRecipientMultiple(array $eventDataArray, Entity\Letter $letter)
	{
		if (ModuleManager::isModuleInstalled('crm'))
		{
			Crm\EventHandler::onAfterPostingSendRecipientMultiple($eventDataArray, $letter);
		}
	}

	/**
	 * Handler of event sender/onAfterPostingRecipientUnsubscribe.
	 *
	 * @param array $eventData Event.
	 * @return void
	 */
	public static function onAfterPostingRecipientUnsubscribe(array $eventData)
	{
		if (Bitrix24\Service::isCloud() && is_array($eventData))
		{
			Bitrix24\Limitation\Rating::regulate();
		}
	}

	/**
	 * Handler of event sender/onConstantList.
	 *
	 * @param string $className Class name.
	 * @return array
	 */
	public static function onConstantList($className)
	{
		// sale
		if (Loader::includeModule('sale') && !Bitrix24\Service::isCloud())
		{
			if (Templates\Category::class === $className)
			{
				return Sale\Preset\TriggerCampaign::getTemplateCategories();
			}
		}

		return [];
	}

	/**
	 * Handler of event sender/onTemplateList.
	 *
	 * @param string $messageType Message type.
	 * @return array
	 */
	public static function onTemplateList($messageType)
	{
		$list = [
			Message\iBase::CODE_MAIL => []
		];

		// sale
		if (Loader::includeModule('sale') && !Bitrix24\Service::isCloud())
		{
			foreach (Sale\Preset\TriggerCampaign::getAll() as $item)
			{
				foreach ($item['CHAIN'] as $letter)
				{
					$list[Message\iBase::CODE_MAIL][] = array(
						'ID' => $letter['TEMPLATE_ID'],
						'TYPE' => $letter['TEMPLATE_TYPE'],
						'CATEGORY' => mb_strtoupper($item['CODE']),
						'MESSAGE_CODE' => Message\iBase::CODE_MAIL,
						'VERSION' => 2,
						'IS_TRIGGER' => true,
						'HOT' => false,
						'ICON' => false,

						'NAME' => $letter['SUBJECT'],
						'DESC' => '',
						'FIELDS' => array(
							'SUBJECT' => array(
								'CODE' => 'SUBJECT',
								'VALUE' => $letter['SUBJECT'],
							),
							'MESSAGE' => array(
								'CODE' => 'MESSAGE',
								'VALUE' => $letter['MESSAGE'],
								'ON_DEMAND' => true
							),
						),
					);
				}
			}
		}

		return isset($list[$messageType]) ? $list[$messageType] : [];
	}

	/**
	 * Handler of event sender/onConnectorList.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function onConnectorList(array $data = array())
	{
		// sender
		$data['CONNECTOR'][] = 'Bitrix\Sender\Integration\Sender\Connectors\Recipient';
		$data['CONNECTOR'][] = 'Bitrix\Sender\Integration\Sender\Connectors\Contact';
		$data['CONNECTOR'][] = 'Bitrix\Sender\Integration\Sender\Connectors\UnSubscribers';

		// crm
		if (Loader::includeModule('crm'))
		{
			$data['CONNECTOR'][] = 'Bitrix\Sender\Integration\Crm\Connectors\Lead';
			$data['CONNECTOR'][] = 'Bitrix\Sender\Integration\Crm\Connectors\Client';
		}

		return $data;
	}

	/**
	 * Handler of event sender/onTriggerList.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function onTriggerList(array $data = [])
	{
		$data['TRIGGER'] = [
			'Bitrix\Sender\Integration\Main\Triggers\UserAuth',
			'Bitrix\Sender\Integration\Main\Triggers\UserDontAuth',
		];

		return $data;
	}

	/**
	 * Handler of event sender/onSenderMessageList.
	 *
	 * @return array
	 */
	public static function onSenderMessageList()
	{
		$list = array();

		// mail
		$list[] = 'Bitrix\Sender\Integration\Sender\Mail\MessageMail';

		// sms
		if (MessageService\Sms\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\MessageService\Sms\MessageSms';
		}

		// im
		if (Im\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\Im\MessageIm';
		}

		// call
		if (VoxImplant\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\VoxImplant\MessageCall';
			$list[] = 'Bitrix\Sender\Integration\VoxImplant\MessageAudioCall';
		}

		// web_hook
		//$list[] = 'Bitrix\Sender\Integration\Sender\WebHook\MessageWebHook';

		// Ads
		if (Seo\Ads\Service::canUse())
		{
			$adsList = array(
				'Bitrix\Sender\Integration\Seo\Ads\MessageYa',
				'Bitrix\Sender\Integration\Seo\Ads\MessageGa',
				'Bitrix\Sender\Integration\Seo\Ads\MessageVk',
				'Bitrix\Sender\Integration\Seo\Ads\MessageFb',
				'Bitrix\Sender\Integration\Seo\Ads\MessageMarketingFb',
				'Bitrix\Sender\Integration\Seo\Ads\MessageMarketingInstagram',
				'Bitrix\Sender\Integration\Seo\Ads\MessageLookalikeVk',
				'Bitrix\Sender\Integration\Seo\Ads\MessageLookalikeFb',
			);
			foreach ($adsList as $adsClass)
			{
				if (!Bitrix24\Service::isAdVisibleInRegion($adsClass::CODE))
				{
					continue;
				}

				$list[] = $adsClass;
			}
		}

		// Return Customer
		if (Crm\ReturnCustomer\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\Crm\ReturnCustomer\MessageLead';
			$list[] = 'Bitrix\Sender\Integration\Crm\ReturnCustomer\MessageDeal';
		}

		if(Bitrix24\Service::isTolokaVisibleInRegion())
		{
			$list[] = 'Bitrix\Sender\Integration\Yandex\Toloka\MessageToloka';
		}

		return $list;
	}

	/**
	 * Handler of event sender/OnPresetMailingList.
	 *
	 * @return array
	 */
	public static function onSenderTriggerCampaignPreset()
	{
		$list = [];

		// sale
		if (Loader::includeModule('sale') && !Bitrix24\Service::isCloud())
		{
			$list = array_merge($list, Sale\Preset\TriggerCampaign::getAll());
		}


		return $list;
	}
	public static function onSenderConsentMessageBuildersList(): array
	{
		$list = [];
		$list[] = 'Bitrix\Sender\Integration\Sender\Mail\ConsentBuilderMail';
		$list[] = 'Bitrix\Sender\Integration\Sender\Mail\TestConsentBuilderMail';
		return $list;
	}
	public static function onSenderConsentResponseList(): array
	{
		$list = [];
		$list[] = 'Bitrix\Sender\Integration\Sender\Mail\ConsentResponseMail';
		return $list;
	}
	/**
	 * Handler of event sender/onSenderTransportList.
	 *
	 * @return array
	 */
	public static function onSenderTransportList()
	{
		$list = array();

		// mail
		$list[] = 'Bitrix\Sender\Integration\Sender\Mail\TransportMail';

		// sms
		if (MessageService\Sms\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\MessageService\Sms\TransportSms';
		}

		// im
		if (Im\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\Im\TransportIm';
		}

		// call
		if (VoxImplant\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\VoxImplant\TransportCall';
			$list[] = 'Bitrix\Sender\Integration\VoxImplant\TransportAudioCall';
		}

		// web_hook
		//$list[] = 'Bitrix\Sender\Integration\Sender\WebHook\TransportWebHook';

		// Ads
		if (Seo\Ads\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportYa';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportGa';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportVk';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportFb';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportMarketingFb';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportMarketingInstagram';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportLookalikeVk';
			$list[] = 'Bitrix\Sender\Integration\Seo\Ads\TransportLookalikeFb';
		}

		// Return Customer
		if (Crm\ReturnCustomer\Service::canUse())
		{
			$list[] = 'Bitrix\Sender\Integration\Crm\ReturnCustomer\TransportLead';
			$list[] = 'Bitrix\Sender\Integration\Crm\ReturnCustomer\TransportDeal';
		}

		$list[] = 'Bitrix\Sender\Integration\Yandex\Toloka\TransportToloka';

		return $list;
	}

	/**
	 * On before update LetterTable.
	 *
	 * @param MainEntity\Event $event Event.
	 * @param MainEntity\EventResult $result Result.
	 * @return void
	 */
	public static function onBeforeUpdateLetterTable(MainEntity\Event $event, MainEntity\EventResult $result)
	{
		$data = $event->getParameters();

		if (Bitrix24\Service::isCloud() && isset($data['fields']['STATUS']))
		{
			$oldRow = LetterTable::getRowById($data['primary']['ID']);
			$updatedBy = $data['fields']['UPDATED_BY'] ?? $oldRow['UPDATED_BY'];

			if (in_array($data['fields']['STATUS'], Dispatch\Semantics::getWorkStates()))
			{
				$user = new User($updatedBy);
				if (!$user->isAgreementAccepted())
				{
					$result->addError(
						new MainEntity\EntityError(Agreement::getErrorText(), 'NEED_ACCEPT_AGREEMENT')
					);
					return;
				}

				$letter = Entity\Letter::createInstanceById($data['primary']['ID']);

				if (is_null($letter))
				{
					$result->addError(
						new MainEntity\EntityError(
							Loc::getMessage("SENDER_LETTER_ONBEFOREUPDATE_ERROR_LETTER_NOT_AVAILABLE"), 'FEATURE_NOT_AVAILABLE'
						)
					);
					return;
				}

				if (!$letter->getMessage()->isAvailable())
				{
					if ($letter->getState()->isWaiting() || $letter->getState()->isSending())
					{
						$letter->stop();
					}

					$result->addError(
						new MainEntity\EntityError(
							Loc::getMessage("SENDER_LETTER_ONBEFOREUPDATE_ERROR_FEATURE_NOT_AVAILABLE"), 'FEATURE_NOT_AVAILABLE'
						)
					);
					return;
				}

				$isEmail = ($oldRow['MESSAGE_CODE'] === Message\iBase::CODE_MAIL);
				$isEmailBlocked = Bitrix24\Limitation\Rating::isBlocked();
				if ($isEmail && $isEmailBlocked)
				{
					$result->addError(
						new MainEntity\EntityError(
							Bitrix24\Limitation\Rating::getNotifyText('blocked')
						)
					);
				}

				if ($isEmail)
				{
					// check sender email:
					$emailFrom = $letter->getMessage()->getConfiguration()->getOption('EMAIL_FROM')->getValue();
					if (!Sender\AllowedSender::isAllowed($emailFrom, $updatedBy))
					{
						$result->addError(
							new MainEntity\EntityError(
								Loc::getMessage("SENDER_LETTER_ONBEFOREUPDATE_ERROR_INVALID_FROM_EMAIL"), 'WRONG_EMAIL_FROM'
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Handler of event main/onMailEventMailChangeStatus.
	 *
	 * @param Main\Mail\Callback\Result $result Callback result instance.
	 * @return void
	 */
	public static function onMailEventMailChangeStatus($result)
	{
		if (!$result->isBelongTo('sender', 'rcpt'))
		{
			return;
		}

		// return if status already updated
		$row = PostingRecipientTable::getRow([
			'select' => [
				'STATUS', 'POSTING_ID', 'CONTACT_ID',
				'CONTACT_IS_SEND_SUCCESS' => 'CONTACT.IS_SEND_SUCCESS'
			],
			'filter' => ['=ID' => $result->getEntityId()]
		]);
		if (!$row)
		{
			return;
		}

		if (!$result->isError())
		{
			// update contact send_success flag
			if ($row['CONTACT_IS_SEND_SUCCESS'] !== 'Y')
			{
				ContactTable::update($row['CONTACT_ID'], ['IS_SEND_SUCCESS' => 'Y']);
			}
		}
		elseif ($result->isPermanentError())
		{
			// return if status already updated
			if ($row['STATUS'] === PostingRecipientTable::SEND_RESULT_ERROR)
			{
				return;
			}

			// update recipient status
			Model\Posting\RecipientTable::update(
				$result->getEntityId(),
				['STATUS' => PostingRecipientTable::SEND_RESULT_ERROR]
			);

			// update posting counters
			Model\PostingTable::update(
				$row['POSTING_ID'],
				[
					'COUNT_SEND_ERROR' => new Main\DB\SqlExpression('?# + 1', 'COUNT_SEND_ERROR'),
					'COUNT_SEND_SUCCESS' => new Main\DB\SqlExpression('?# - 1', 'COUNT_SEND_SUCCESS')
				]
			);

			// update daily limit counters
			if (Bitrix24\Service::isCloud())
			{
				Bitrix24\Limitation\DailyLimit::incrementError();
			}
		}
	}
}