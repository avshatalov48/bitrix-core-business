// @flow

'use strict';

import { Loc, Tag, Dom, Event, Type, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Util } from 'calendar.util';

export class InterfaceTemplate extends EventEmitter
{
	COUNTER_FAILED = 1;
	static SLIDER_WIDTH = 606;
	sliderWidth = 840;
	static SLIDER_PREFIX = 'calendar:connection-sync-';
	IS_UPDATING = false;

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
		this.iconLogoClass = options.iconLogoClass || '';
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
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
				</div>
				${this.getContentInfoBody()}
			</div>
		`;
	}

	getContentActiveBodyHeader()
	{
		const timestamp = this.connection.getSyncDate().getTime() / 1000;
		const syncTime = timestamp
			? `${Util.formatDateUsable(timestamp)} ${BX.date.format(Util.getTimeFormatShort(), timestamp)}`
			: '';

		return Tag.render`
			<div class="calendar-sync__account ${this.getSyncStatusClassName()}">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${this.getLogoIconClass()}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${BX.util.htmlspecialchars(this.connection.getConnectionName())}
					${this.getAccountInfo(syncTime)}
				</div>
				${this.getActionButton()}
			</div>
		`;
	}

	getAccountInfo(syncTime: string): HTMLElement
	{
		if (this.connection.status === false && this.provider.getStatus() === 'failed' && this.provider.doSupportReconnectionScenario())
		{
			const connectionType = Text.encode(this.provider.getFailedConnectionName());

			return Tag.render`
				<div class="calendar-sync__account-info calendar-sync__account-info-template-reconnection">
					<div class="calendar-sync__account-info--icon --animate"></div>
					${Loc.getMessage(
				'CAL_SYNC_INFO_STATUS_ERROR_RECONNECT',
				{ '#TYPE#': connectionType === 'iCloud' ? 'iCloud' : Text.capitalize(connectionType) },
					)}
				</div>
			`;
		}

		return Tag.render`
			<div class="calendar-sync__account-info">
				<div class="calendar-sync__account-info--icon --animate"></div>
				${syncTime}
			</div>
		`;
	}

	getActiveConnectionContent()
	{
		this.disconnectButton = this.getDisconnectButton();

		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
				</div>
				<div class="calendar-sync__scope">
					<div class="calendar-sync__content --border-radius">
						<div class="calendar-sync__content-block --space-bottom">
							${this.getContentActiveBody()}
						</div>
					</div>
				</div>
				<div class="calendar-sync__disconnect-button-container">
					${this.disconnectButton}
				</div>
			</div>
		`;
	}

	getActionButton(): HTMLElement
	{
		if (this.connection.status === false && this.provider.getStatus() === 'failed' && this.provider.doSupportReconnectionScenario())
		{
			this.getReconnectActionButton();
		}
		else if (this.provider.getStatus() === 'pending')
		{
			this.getPendingActionButton();
		}
		else
		{
			this.getRefreshActionButton();
		}

		return this.actionButton;
	}

	getReconnectActionButton(): void
	{
		this.actionButton = Tag.render`
			<button class="ui-btn ui-btn-primary ui-btn-round calendar-sync__account-btn">
				<div class="ui-icon-set --refresh-4"></div>
				${Loc.getMessage('CAL_BUTTON_STATUS_FAILED_RECONNECT')}
				<div class="calendar-sync__account-counter">${this.COUNTER_FAILED}</div>
			</button>
		`;

		Event.bind(this.actionButton, 'click', () => this.reconnect());
	}

	getPendingActionButton(): void
	{
		this.actionButton = Tag.render`
			<button class="ui-btn ui-btn-primary ui-btn-clock ui-btn-round calendar-sync__account-btn">
				<div class="calendar-sync__account-counter">${this.COUNTER_FAILED}</div>
			</button>
		`;
	}

	getRefreshActionButton(): void
	{
		const { root, icon } = Tag.render`
			<button class="ui-btn ui-btn-primary ui-btn-round calendar-sync__account-btn">
				<div ref="icon" class="ui-icon-set --refresh-4"></div>
				${Loc.getMessage('CAL_REFRESH')}
			</button>
		`;
		this.actionButton = root;
		this.actionButtonIcon = icon;

		Event.bind(this.actionButton, 'click', () => this.updateConnection());
	}

	updateConnection()
	{
		if (this.IS_UPDATING)
		{
			return;
		}

		this.onUpdateConnectionStart();

		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.updateConnection', {
				data: {
					type: 'user',
					requestUid: Util.registerRequestId(),
				},
			}).then((response) => {
				EventEmitter.emit('BX.Calendar.Sync.Interface.InterfaceTemplate:onRefresh', {
					data: response.data,
					event: { doRefreshMainSlider: true },
				});
				this.onUpdateConnectionEnd();
				resolve();
			});
		});
	}

	onUpdateConnectionStart()
	{
		this.IS_UPDATING = true;
		Dom.addClass(this.actionButtonIcon, '--hidden');
		Dom.addClass(this.actionButton, 'ui-btn-clock');
		Dom.addClass(this.disconnectButton, 'ui-btn-disabled');
		Dom.addClass(this.sectionListNode, '--disabled');
	}

	onUpdateConnectionEnd()
	{
		this.IS_UPDATING = false;
		Dom.removeClass(this.actionButtonIcon, '--hidden');
		Dom.removeClass(this.actionButton, 'ui-btn-clock');
		Dom.removeClass(this.disconnectButton, 'ui-btn-disabled');
		Dom.removeClass(this.sectionListNode, '--disabled');
		this.provider.closeSlider();
	}

	getContentInfoBody()
	{
		return Tag.render`
			${this.getContentInfoBodyHeader()}
		`;
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
			${this.getContentActiveBodySectionsHeader()}
			${this.getContentActiveBodySectionsManager()}
		`;
	}

	showHelp(event)
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show(`redirect=detail&code=${this.helpdeskCode}`);
			event.preventDefault();
		}
	}

	getHelpdeskLink()
	{
		return `https://helpdesk.bitrix24.ru/open/${this.helpdeskCode}`;
	}

	getHeaderTitle()
	{
		return this.title;
	}

	getLogoIconClass()
	{
		return this.iconLogoClass;
	}

	getContentInfoBodyHeader()
	{
		if (!this.infoBodyHeader)
		{
			this.infoBodyHeader = Tag.render`
				<div class="calendar-sync-slider-section calendar-sync-slider-section-flex-wrap">
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
						${this.getContentInfoBodyHeaderHelper()}
					</div>
				</div>
			`;
		}

		return this.infoBodyHeader;
	}

	getContentInfoBodyHeaderHelper()
	{
		return Tag.render`
			<div class="calendar-sync-slider-info">
				<span class="calendar-sync-slider-info-text">
					<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
						${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
					</a>
				</span>
			</div>
		`;
	}

	getContentInfoWarning()
	{
		const mobileSyncButton = this.getMobileSyncControlButton();
		if (this.alreadyConnectedToNew)
		{
			Event.bind(mobileSyncButton, 'click', this.handleMobileButtonOtherSyncInfo.bind(this));
		}
		else
		{
			Event.bind(mobileSyncButton, 'click', this.handleMobileButtonConnectClick.bind(this));
		}

		return Tag.render`
			<div class="calendar-sync-slider-section-warning calendar-sync-slider-section-col">
				<div class="ui-alert ui-alert-warning ui-alert-icon-info">
					<span class="ui-alert-message">${this.warningText}
					</span>
				</div>
				<div class="calendar-sync-button-warning">${mobileSyncButton}</div>
			</div>
		`;
	}

	getMobileSyncControlButton()
	{
		return Tag.render`
			<button class="ui-btn ui-btn-success ui-btn-sm ui-btn-round">
				${this.mobileSyncButtonText}
			</button>
		`;
	}

	setProvider(provider)
	{
		this.provider = provider;
	}

	// TODO: move logic to provider
	sendRequestRemoveConnection(id)
	{
		BX.ajax.runAction('calendar.api.syncajax.removeConnection', {
			data: {
				connectionId: id,
				removeCalendars: 'Y', //by default
			}
		}).then(() => {
			BX.reload();
		});
	}

	runUpdateInfo()
	{
		BX.ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
			data: {
				sectionStatus: this.sectionStatusObject,
			},
		}).then((response) => {
			this.emit('reDrawCalendarGrid', {});
		});
	}

	refresh(connection)
	{
		this.connection = connection;
		if (this.connection)
		{
			this.statusBlock
				?.setStatus(this.connection.getStatus())
				.setConnections([this.connection])
			;
		}

		Dom.replace(document.getElementById('status-info-block'), this.statusBlock?.getContent());
	}

	reconnect()
	{
		if (!this.provider.doSupportReconnectionScenario())
		{
			return;
		}

		this.provider.startReconnecting();
		this.handleConnectButton();
		this.provider.closeSlider();
	}

	handleConnectButton()
	{
	}

	getDisconnectButton()
	{
		// <button class="ui-btn ui-btn-primary ui-btn-round calendar-sync__account-btn">
		// 	<div class="ui-icon-set --refresh-4"></div>
		// 	${Loc.getMessage('CAL_SYNC_DISCONNECT_BUTTON')}
		// 	<div class="calendar-sync__account-counter">${this.COUNTER_FAILED}</div>
		// </button>
		//
		// <button class="ui-btn ui-btn-primary ui-btn-clock ui-btn-round">${Loc.getMessage('CAL_SYNC_DISCONNECT_BUTTON')}</button>
		const button = Tag.render`
			<button class="ui-btn ui-btn-light-border ui-btn-round calendar-sync__account-btn">
				${Loc.getMessage('CAL_SYNC_DISCONNECT_BUTTON')}
			</button>
		`;

		Event.bind(button, 'click', this.handleDisconnectButton.bind(this));

		return button;
	}

	getSyncStatusClassName(): string
	{
		return this.provider.getStatus() === 'success' || this.connection.status === true
			? '--complete'
			: (this.provider.doSupportReconnectionScenario() ? '--error-reconnect' : '--error')
		;
	}

	getContentActiveBodySectionsHeader()
	{
		return Tag.render`
			<div class="calendar-sync__account-desc">${Loc.getMessage('CAL_SYNC_SELECTED_LIST_TITLE')}</div>
		`;
	}

	getContentActiveBodySectionsManager()
	{
		this.sectionListNode = Tag.render`
			<div class="calendar-sync__account-check-list">
				${this.getContentActiveBodySections()}
			</div>
		`;

		return this.sectionListNode;
	}

	getContentActiveBodySections()
	{
		const sectionList = [];
		this.sectionList.forEach((section) => {
			sectionList.push(Tag.render`
				<label class="calendar-sync__account-check-list-label">
					<input type="checkbox" class="calendar-sync__account-check-list-input"
						value="${BX.util.htmlspecialchars(section.ID)}" 
						onclick="${this.onClickCheckSection.bind(this)}" ${section.ACTIVE === 'Y' ? 'checked' : ''}/>
					<span class="calendar-sync__account-check-list-text">${BX.util.htmlspecialchars(section.NAME)}</span>
				</label>
			`);
		});

		return sectionList;
	}

	showUpdateSectionListNotification()
	{
		Util.showNotification(
			Loc.getMessage('CAL_SYNC_CALENDAR_LIST_UPDATED'),
		);
	}

	handleDisconnectButton(event)
	{
		if (Type.isElementNode(this.disconnectButton))
		{
			Dom.addClass(this.disconnectButton, ['ui-btn-clock', 'ui-btn-disabled']);
		}
		event.preventDefault();
		// this.provider.removeConnection();
		this.sendRequestRemoveConnection(this.connection.getId());
	}

	deactivateConnection(id)
	{
		BX.ajax.runAction('calendar.api.syncajax.deactivateConnection', {
			data: {
				connectionId: id,
				removeCalendars: 'N', //by default
			},
		}).then(() => {
			this.provider.closeSlider();
			this.provider.setStatus(this.provider.STATUS_NOT_CONNECTED);
			this.provider.getInterfaceUnit().refreshButton();
			this.provider.getInterfaceUnit().setSyncStatus(this.provider.STATUS_NOT_CONNECTED);

			this.emit('reDrawCalendarGrid', {});
		});
	}
}
