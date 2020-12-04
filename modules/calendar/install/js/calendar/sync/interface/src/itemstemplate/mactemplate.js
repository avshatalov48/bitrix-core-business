// @flow
'use strict';

import {Loc, Tag} from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";

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
	}

	getPortalAddress()
	{
		return this.portalAddress;
	}

	getContentInfoBody()
	{
		return Tag.render `
			${this.getContentInfoBodyHeader()}
			${this.getContentBodyConnect()}
		`;
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
			${this.getContentBodyConnect()}
		`;
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
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH').replace(/#PORTAL_ADDRESS#/gi, this.provider.getPortalAddress())}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH')}</span>
						</li>
					</ol>
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION')}</span>
				</div>
			</div>
		`;
	}
}
