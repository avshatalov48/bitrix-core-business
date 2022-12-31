// @flow
'use strict';

import { Dom, Event, Loc, Tag } from 'main.core';
import {InterfaceTemplate} from "./interfacetemplate";
import { Util } from 'calendar.util';

export default class MacTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_MAC"),
			helpDeskCode: '5684075',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_MAC_CALENDAR_TITLE'),
			descriptionInfoHeader: Loc.getMessage('CAL_MAC_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_MAC_CALENDAR_IS_CONNECT_TITLE'),
			descriptionActiveHeader: Loc.getMessage('CAL_MAC_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-mac',
			iconPath: '/bitrix/images/calendar/sync/mac.svg',
			color: '#ff5752',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: false,
		});

		this.alreadyConnectedToNew = Util.isIcloudConnected();
		if (this.alreadyConnectedToNew)
		{
			this.warningText = Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC_CONNECTED');
			this.mobileSyncButtonText = Loc.getMessage('CALENDAR_CHECK_ICLOUD_SETTINGS');
		}
		else
		{
			this.warningText = Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC');
			this.mobileSyncButtonText = Loc.getMessage('CALENDAR_CONNECT_ICLOUD');
		}
	}

	getPortalAddress()
	{
		return this.portalAddress;
	}

	getContentInfoBody()
	{
		return Tag.render `
			${this.getContentInfoBodyHeader()}
			${this.getContentInfoWarning()}
		`;
	}


	getActiveConnectionContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
				</div>
				${this.getContentActiveBody()}
			</div>
		`
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
			<div class="calendar-sync-slider-section calendar-sync-slider-section-banner">
				${this.getContentBodyConnect()}
			</div>
		`;
	}

	getContentActiveBodyHeader()
	{
		const timestamp = this.connection.getSyncDate().getTime() / 1000;
		const syncTime = timestamp
			? Util.formatDateUsable(timestamp) + ' ' + BX.date.format(Util.getTimeFormatShort(), timestamp)
			: '';

		return Tag.render `
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">${this.titleActiveHeader}</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE')}</span>
					<span class="calendar-sync-slider-info-time">${syncTime}</span>
				</div>
					<a class="calendar-sync-slider-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}</a>
				</div>
			</div>`;
	}

	getContentInfoBodyHeaderHelper()
	{
		if (!this.headerHelper)
		{
			this.headerHelper = Tag.render`
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						<a class="calendar-sync-slider-info-link">
							${Loc.getMessage('CAL_CONNECT_PC')}
						</a>
					</span>
				</div>
			`;

			Event.bind(this.headerHelper, 'click', this.showExtendedInfoMacOs.bind(this));
		}

		return this.headerHelper;
	}

	showExtendedInfoMacOs()
	{
		this.headerHelper.style.display = 'none';
		Dom.append(this.getContentBodyConnect(), this.infoBodyHeader);
	}

	getContentBodyConnect()
	{
		return Tag.render`
			<div class="calendar-sync-slider-section calendar-sync-slider-section-col">
				<div class="calendar-sync-slider-header calendar-sync-slider-header-divide">
					<div class="calendar-sync-slider-subtitle">${Loc.getMessage('CAL_MAC_INSTRUCTION_HEADER')}</div>
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_DESCRIPTION')}:</span>
					<ol class="calendar-sync-slider-info-list">
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIRST')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SECOND')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_THIRD')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FOURTH')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH', { '#PORTAL_ADDRESS#': this.provider.getPortalAddress() })}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH')}</span>
						</li>
					</ol>
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION')}</span>
					<div class="calendar-sync-slider-info" style="margin-top: 20px">
						<span class="calendar-sync-slider-info-text">
							<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
								${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
							</a>
						</span>
					</div>
				</div>
			</div>
		`;
	}

	handleMobileButtonConnectClick()
	{
		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-mac'].includes(slider.getUrl()))
			{
				slider.close();
			}
		});

		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			calendarContext
				.syncInterface
				.getIcloudProvider()
				.getInterfaceUnit()
				.getConnectionTemplate()
				.handleConnectButton();
		}
	}

	handleMobileButtonOtherSyncInfo()
	{
		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-mac'].includes(slider.getUrl()))
			{
				slider.close();
			}
		});

		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			const connectionProvider = calendarContext
				.syncInterface
				.getIcloudProvider()
				.getInterfaceUnit()
				.connectionProvider
			;

			connectionProvider.openActiveConnectionSlider(connectionProvider.getConnection());
		}
	}
}
