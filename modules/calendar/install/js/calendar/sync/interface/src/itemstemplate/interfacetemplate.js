// @flow
'use strict';

import {ajax, Loc, Tag, Dom} from "main.core";
import StatusBlock from "../controls/statusblock"
import {EventEmitter} from "main.core.events";

export class InterfaceTemplate extends EventEmitter
{
	static SLIDER_WIDTH = 606;
	sliderWidth = 840;
	static SLIDER_PREFIX = 'calendar:connection-sync-';

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Interface.InterfaceTemplate');

		this.title = options.title;
		this.helpdeskCode = options.helpDeskCode;
		this.titleInfoHeader = options.titleInfoHeader;
		this.descriptionInfoHeader = options.descriptionInfoHeader;
		this.titleActiveHeader = options.titleActiveHeader;
		this.descriptionActiveHeader = options.descriptionActiveHeader;
		this.sliderIconClass = options.sliderIconClass;
		this.iconPath = options.iconPath;
		this.color = options.color;
		this.provider = options.provider;
		this.connection = options.connection;
		this.popupWithUpdateButton = options.popupWithUpdateButton;
	}

	static createInstance(provider, connection = null)
	{
		return new this(provider, connection);
	}

	getInfoConnectionContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				${this.getContentInfoHeader()}
				${this.getContentInfoBody()}
			</div>
		`
	}

	getActiveConnectionContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				${this.getContentActiveHeader()}
				${this.getContentActiveBody()}
			</div>
		`
	}

	getContentInfoHeader()
	{
		this.statusBlock = StatusBlock.createInstance({
			status: "not_connected",
			connections: [this.connection],
			withStatusLabel: false,
			popupWithUpdateButton: this.popupWithUpdateButton,
			popupId: 'calendar-interfaceTemplate-status',
		});

		return Tag.render`
			<div class="calendar-sync-header">
				<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
				${this.statusBlock.getContent()}
			</div>
		`;
	}

	getContentInfoBody()
	{
		return Tag.render`
			${this.getContentInfoBodyHeader()}
		`;
	}

	getContentActiveHeader()
	{
		this.statusBlock = StatusBlock.createInstance({
			status: this.connection.getStatus(),
			connections: [this.connection],
			withStatusLabel: false,
			popupWithUpdateButton: this.popupWithUpdateButton,
			popupId: 'calendar-interfaceTemplate-status',
		});

		return Tag.render`
			<div class="calendar-sync-header">
				<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
				${this.statusBlock.getContent()}
			</div>
		`;
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
		`;
	}

	showHelp()
	{
		if (BX.Helper)
		{
			BX.Helper.show("redirect=detail&code=" + this.helpdeskCode);
			event.preventDefault();
		}
	}

	getHelpdeskLink()
	{
		return 'https://helpdesk.bitrix24.ru/open/' + this.helpdeskCode;
	}

	getHeaderTitle()
	{
		return this.title;
	}

	getContentInfoBodyHeader()
	{
		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">
					${this.titleInfoHeader}
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						${this.descriptionInfoHeader}
					</span>
				</div>
				<div class="calendar-sync-slider-info">
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

	getContentActiveBodyHeader()
	{
		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">
					${this.titleActiveHeader}
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						${this.descriptionActiveHeader}
					</span>
				</div>
				<div class="calendar-sync-slider-info">
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

	setProvider(provider)
	{
		this.provider = provider;
	}

	sendRequestRemoveConnection(id)
	{
		BX.ajax.runAction('calendar.api.calendarajax.removeConnection', {
			data: {
				connectionId: id,
			}
		}).then(() => {
			BX.reload();
		});
	}

	runUpdateInfo()
	{
		ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
			data: {
				sectionStatus: this.sectionStatusObject,
			},
		}).then(response => {
			this.emit('reDrawCalendarGrid', {});
		})
	}

	refresh(connection)
	{
		this.connection = connection;
		this.statusBlock
			.setStatus(this.connection.getStatus())
			.setConnections([this.connection]);
		Dom.replace(document.getElementById('status-info-block'), this.statusBlock.getContent());
	}
}