<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Calendar\ICal\MailInvitation\TopIconForMailTemplate;
use Bitrix\Calendar\Util;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/lib/ical/mailinvitation/senderinvitation.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/lib/ical/mailinvitation/sendercancelinvitation.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');

class CalendarPubEventComponent extends CBitrixComponent
{
	/** @var array */
	protected $data = [];
	/** @var Date */
	protected $dateFrom;

	public function __construct($component = null)
	{
		parent::__construct($component);
		IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT']. BX_ROOT.'/modules/calendar/classes/general/calendar.php');
	}

	/**
	 * @return mixed|void|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function executeComponent()
	{
		$this->arResult['COMPONENT_PATH'] = $this->getPath();
		$this->prepareParams();

		$this->includeComponentTemplate();
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function prepareParams(): void
	{
		$this->arResult['NAME'] = $this->arParams['PARAMS']['NAME'];
		$this->arResult['TITLE'] = \COption::GetOptionString("main", "site_name", '', '-');
		$this->arResult['DETAIL_LINK'] = $this->arParams['PARAMS']['DETAIL_LINK'];
		$this->arResult['FULL_DAY'] = $this->arParams['PARAMS']['FULL_DAY'] === 'Y';
		$this->dateFrom = Util::getDateObject(
			$this->arParams['PARAMS']['DATE_FROM'],
			$this->arResult['FULL_DAY'],
			$this->arParams['PARAMS']['TZ_FROM']
		);
		$this->arResult['DATE_NUMBER'] = $this->dateFrom->format('j');

		$this->prepareTopPartImageTemplate();
		$this->prepareDecisionParams();
		$this->prepareEventDurationParams();
		$this->prepareEditFieldsParams();
	}

	/**
	 * @param Date $date
	 * @return string
	 */
	public function getWeekDayName(Date $date): string
	{
		return Loc::getMessage('EC_'.mb_strtoupper(substr($date->format('l'), 0, 2)).'_F');
	}

	/**
	 * @param Date $date
	 * @return string|null
	 */
	protected function getMonthName(Date $date): ?string
	{
		return mb_strtoupper(Loc::getMessage('EC_CALENDAR_SHORT_MON_'.$date->format('n')));
	}

	/**
	 *
	 */
	protected function prepareDecisionParams(): void
	{
		$this->arResult['IS_SHOW_DECISION_BUTTON'] = false;
		$this->arResult['IS_SHOW_DETAIL_LINK'] = false;
		$this->arResult['IS_SHOW_CHOOSE_DECISION_BUTTON'] = false;
		$this->arResult['IS_SHOW_CANCEL_INVITATION_ALERT'] = false;

		if (
			in_array(
				$this->arParams['PARAMS']['METHOD'],
				[
					'request',
					'edit',
				],
				true
			)
		)
		{
			$this->arResult['IS_SHOW_DECISION_BUTTON'] = true;
			$this->arResult['IS_SHOW_DETAIL_LINK'] = true;


			if (
				$this->arParams['PARAMS']['METHOD'] === 'edit'
				&& $this->arParams['PARAMS']['REQUEST_DECISION'] === 'N'
			)
			{
				$this->arResult['CHANGE_DECISION_LINK'] = $this->arParams['PARAMS']['CHANGE_DECISION_LINK'];
			}
			else
			{
				$this->arResult['IS_SHOW_CHOOSE_DECISION_BUTTON'] = true;
				$this->arResult['DECISION_YES_LINK'] = $this->arParams['PARAMS']['DECISION_YES_LINK'];
				$this->arResult['DECISION_NO_LINK'] = $this->arParams['PARAMS']['DECISION_NO_LINK'];
			}
		}
		elseif ($this->arParams['PARAMS']['METHOD'] === 'cancel')
		{
			$this->arResult['IS_SHOW_CANCEL_INVITATION_ALERT'] = true;
			$this->arResult['CANCEL_INVITATION_ALERT'] = Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL');
		}
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function prepareEventDurationParams(): void
	{
		$this->arResult['IS_LONG_DATETIME_FORMAT'] = false;
		$this->arResult['IS_SHOW_RRULE'] = false;
		$dateTo = Util::getDateObject(
			$this->arParams['PARAMS']['DATE_TO'],
			$this->arResult['FULL_DAY'],
			$this->arParams['PARAMS']['TZ_TO']
		);

		if ($this->dateFrom instanceof Date && $dateTo instanceof Date)
		{
			$this->prepareDateParamsForDateBox($this->dateFrom);
			$this->arResult['IS_SHOW_TIME_OFFSET'] = false;
			$culture = Context::getCurrent()->getCulture();
			$this->arResult['DATE_FROM'] = FormatDate($culture->getFullDateFormat(), $this->dateFrom->getTimestamp());

			if (
				$dateTo->getDiff($this->dateFrom)->format('%a') > 0
				|| $dateTo->format('j') !== $this->dateFrom->format('j')
				|| $dateTo->format('Y') !== $this->dateFrom->format('Y')
				|| $dateTo->format('n') !== $this->dateFrom->format('n')
			)
			{
				$this->arResult['IS_LONG_DATETIME_FORMAT'] = true;
				$this->arResult['DATE_TO'] = FormatDate($culture->getFullDateFormat(), $dateTo->getTimestamp());
			}

			if ($this->arResult['FULL_DAY'])
			{
				if (!isset($this->arResult['DATE_TO']))
				{
					$this->arResult['DATE_TO'] = FormatDate($culture->getFullDateFormat(), $dateTo->getTimestamp());
				}
			}
			else
			{
				$this->arResult['TIME_FROM'] = FormatDate(
					$culture->getShortTimeFormat(),
					$this->dateFrom->getTimestamp() + Util::getTimezoneOffsetFromServer($this->arParams['PARAMS']['TZ_FROM'], $this->dateFrom)
				);
				$this->arResult['TIME_TO'] = FormatDate(
					$culture->getShortTimeFormat(),
					$dateTo->getTimestamp() + Util::getTimezoneOffsetFromServer($this->arParams['PARAMS']['TZ_TO'], $dateTo)
				);
				$this->arResult['OFFSET_FROM'] = $this->dateFrom->format('P');
				$this->arResult['TIMEZONE_NAME_FROM'] = $this->dateFrom->format('e');
				if ($this->arResult['TIMEZONE_NAME_FROM'] !== 'UTC')
				{
					$this->arResult['IS_SHOW_TIME_OFFSET'] = true;
				}
			}

			if ($this->arParams['PARAMS']['RRULE'] !== '')
			{
				$this->arResult['RRULE'] = $this->arParams['PARAMS']['RRULE'];
				$this->arResult['IS_SHOW_RRULE'] = true;
			}
		}
	}

	/**
	 * @param Date $date
	 */
	protected function prepareDateParamsForDateBox(Date $date): void
	{
		$this->arResult['DATE_FROM_NUMBER'] = $date->format('j');
	}

	/**
	 *
	 */
	protected function prepareTopPartImageTemplate(): void
	{
		$this->arResult['IS_SHOW_DATE_ICON'] = false;
		$iconCreator = TopIconForMailTemplate::fromDate($this->dateFrom);
		if($iconCreator->createImage())
		{
			$this->arResult['IS_SHOW_DATE_ICON'] = true;
			$this->arResult['ICON_MONTH_PATH'] = $iconCreator->getPath();
		}
	}

	/**
	 *
	 */
	protected function prepareEditFieldsParams(): void
	{
		$this->arResult['IS_SHOW_EDIT_FIELDS'] = false;
		if ($this->arParams['PARAMS']['METHOD'] === 'edit')
		{
			$this->arResult['IS_SHOW_EDIT_FIELDS'] = true;
			$this->arResult['EDIT_FIELDS'] = $this->arParams['PARAMS']['CHANGE_FIELDS_TITLE'];
		}
	}
}
