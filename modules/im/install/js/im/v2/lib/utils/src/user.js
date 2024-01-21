import { Extension, Type, Loc } from 'main.core';
import { DateTimeFormat } from 'main.date';

import { DateFormatter, DateCode } from 'im.v2.lib.date-formatter';
import { UserIdNetworkPrefix } from 'im.v2.const';

const settings = Extension.getSettings('im.v2.lib.utils');

export const UserUtil = {

	getLastDateText(params = {}): string
	{
		if (params.bot || params.network || !params.lastActivityDate)
		{
			return '';
		}

		const isOnline = this.isOnline(params.lastActivityDate);
		const isMobileOnline = this.isMobileOnline(params.lastActivityDate, params.mobileLastDate);

		let text = '';
		const lastSeenText = this.getLastSeenText(params.lastActivityDate);

		// "away for X minutes"
		if (isOnline && params.idle && !isMobileOnline)
		{
			text = Loc.getMessage('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getIdleText(params.idle));
		}
		// truly online, last activity date < 5 minutes ago - show status text
		else if (isOnline && !lastSeenText)
		{
			text = this.getStatusTextForLastDate(params.status);
		}
		// last activity date > 5 minutes ago - "Was online X minutes ago"
		else if (lastSeenText)
		{
			const phraseCode = `IM_LAST_SEEN_${params.gender}`;
			text = Loc.getMessage(phraseCode).replace('#POSITION#. ', '').replace('#LAST_SEEN#', lastSeenText);
		}

		// if on vacation - add postfix with vacation info
		if (params.absent)
		{
			const vacationText = Loc.getMessage('IM_STATUS_VACATION_TITLE').replace('#DATE#',
				DateFormatter.formatByCode(params.absent.getTime() / 1000, DateCode.shortDateFormat)
			);

			text = text ? `${text}. ${vacationText}`: vacationText;
		}

		return text;
	},

	getIdleText(idle = '')
	{
		if (!idle)
		{
			return '';
		}

		return DateTimeFormat.format([
			['s60', 'sdiff'],
			['i60', 'idiff'],
			['H24', 'Hdiff'],
			['', 'ddiff']
		], idle);
	},

	isOnline(lastActivityDate): boolean
	{
		if (!lastActivityDate)
		{
			return false;
		}

		return Date.now() - lastActivityDate.getTime() <= this.getOnlineLimit() * 1000;
	},

	isMobileOnline(lastActivityDate, mobileLastDate): boolean
	{
		if (!lastActivityDate || !mobileLastDate)
		{
			return false;
		}

		const FIVE_MINUTES = 5 * 60 * 1000;
		return (
			Date.now() - mobileLastDate.getTime() < this.getOnlineLimit() * 1000
			&& lastActivityDate - mobileLastDate < FIVE_MINUTES
		);
	},

	getStatusTextForLastDate(status: string): string
	{
		status = status.toUpperCase();
		return Loc.getMessage(`IM_STATUS_${status}`) ?? status;
	},

	getStatusText(status: string): string
	{
		status = status.toUpperCase();
		return Loc.getMessage(`IM_STATUS_TEXT_${status}`) ?? status;
	},

	getLastSeenText(lastActivityDate): string
	{
		if (!lastActivityDate)
		{
			return '';
		}

		const FIVE_MINUTES = 5 * 60 * 1000;
		if (Date.now() - lastActivityDate.getTime() > FIVE_MINUTES)
		{
			return DateTimeFormat.formatLastActivityDate(lastActivityDate);
		}

		return '';
	},

	isBirthdayToday(birthday): boolean
	{
		return birthday === DateTimeFormat.format('d-m', new Date());
	},

	getOnlineLimit(): number
	{
		const limitOnline = settings.get('limitOnline', false);

		const FIFTEEN_MINUTES = 15 * 60;
		return limitOnline? Number.parseInt(limitOnline, 10): FIFTEEN_MINUTES;
	},

	getProfileLink(userId: number | string): string
	{
		if (Type.isString(userId))
		{
			userId = Number.parseInt(userId, 10);
		}

		return `/company/personal/user/${userId}/`;
	},

	getCalendarLink(userId: number | string): string
	{
		if (Type.isString(userId))
		{
			userId = Number.parseInt(userId, 10);
		}

		const path = Extension.getSettings('im.v2.lib.utils').get('pathToUserCalendar');

		return path.replace('#user_id#', userId);
	},

	isNetworkUserId(userId: string): boolean
	{
		if (!Type.isStringFilled(userId))
		{
			return false;
		}

		return userId.startsWith(UserIdNetworkPrefix);
	},
};
