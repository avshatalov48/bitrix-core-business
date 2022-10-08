// @flow
'use strict';

import { Loc, Tag } from 'main.core';
import { InterfaceTemplate } from './interfacetemplate';
import { Util } from 'calendar.util';

export default class ExchangeTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_EXCHANGE"),
			helpDeskCode: '9860971',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_EXCHANGE_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_EXCHANGE_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_EXCHANGE_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-office',
			iconLogoClass: '--exchange',
			iconPath: '/bitrix/images/calendar/sync/exchange.svg',
			color: '#54d0df',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
			${this.getContentBody()}
			${this.getHelpdeskBlock()}
		`;
	}

	getContentActiveBodyHeader()
	{

		const timestamp = this.connection.getSyncDate().getTime() / 1000;
		const syncTime = timestamp
			? Util.formatDateUsable(timestamp) + ' ' + BX.date.format(Util.getTimeFormatShort(), timestamp)
			: '';

		return Tag.render`
			<div class="calendar-sync__account ${this.getSyncStatusClassName()}">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${this.getLogoIconClass()}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${BX.util.htmlspecialchars(this.connection.getConnectionName())}
					<div class="calendar-sync__account-info">
						<div class="calendar-sync__account-info--icon --animate"></div>
						${syncTime}
					</div>
				</div>
			</div>
			`;
	}

	getContentBody()
	{
		return Tag.render`
			<div class="calendar-sync__account-desc">
				${Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION')}
			</div>
		`;
	}

	getHelpdeskBlock()
	{
		return Tag.render`
			<div>
				<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
					${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
				</a>
			</div>
		`;
	}
}