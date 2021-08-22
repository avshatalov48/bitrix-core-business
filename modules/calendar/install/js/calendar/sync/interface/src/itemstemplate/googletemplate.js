// @flow
'use strict';

import {ajax, Dom, Loc, Tag} from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";
import ConnectionControls from "../controls/connectioncontrols";
import {Popup} from "main.popup";
import { MessageBox } from 'ui.dialogs.messagebox';

export default class GoogleTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_GOOGLE"),
			helpDeskCode: '6030429',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_GOOGLE_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_GOOGLE_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_GOOGLE_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-google',
			iconPath: '/bitrix/images/calendar/sync/google.svg',
			color: '#387ced',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});

		this.sectionStatusObject = {};
	}

	createConnection()
	{
		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				click_to_connection_button: 'Y',
				connection_type: 'google',
			}
		});

		BX.util.popup(this.provider.getSyncLink(), 500, 600);
	}

	getContentInfoBody()
	{
		const formObject = new ConnectionControls();
		const button = formObject.getAddButton();
		const buttonWrapper = formObject.getButtonWrapper();
		const bodyHeader = this.getContentInfoBodyHeader();
		const content = bodyHeader.querySelector('.calendar-sync-slider-header');

		if (this.provider.hasSetSyncCaldavSettings())
		{
			button.addEventListener('click', () =>
			{
				this.createConnection();
			});
		}
		else
		{
			button.addEventListener('click', () =>
			{
				this.showAlertPopup();
			});
		}


		Dom.append(button, buttonWrapper);
		Dom.append(buttonWrapper, content);

		return Tag.render`
			${bodyHeader}
		`;
	}

	getContentActiveBody(): *
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}
			${this.getContentActiveBodySectionsManager()}
		`;
	}

	getContentActiveBodyHeader()
	{
		const formObject = new ConnectionControls();
		const disconnectButton = formObject.getDisconnectButton();
		disconnectButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.sendRequestRemoveConnection(this.connection.getId())
		});

		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon calendar-sync-slider-header-icon-google"></div>
				<div class="calendar-sync-slider-header">
					<div class="calendar-sync-slider-title">${Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT')}</div>
					<span class="calendar-sync-slider-account">
						<span class="calendar-sync-slider-account-avatar"></span>
						<span class="calendar-sync-slider-account-email">
							${BX.util.htmlspecialchars(this.connection.getConnectionName())}
						</span>
					</span>
					<div class="calendar-sync-slider-info">
						<span class="calendar-sync-slider-info-text">
							<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
								${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
							</a>
						</span>
					</div>
					${disconnectButton}
				</div>
			</div>
			`;
	}

	getContentActiveBodySectionsManager()
	{
		return Tag.render`
			<div class="calendar-sync-slider-section calendar-sync-slider-section-col">
				<div class="calendar-sync-slider-header">
					<div class="calendar-sync-slider-subtitle">${Loc.getMessage('CAL_AVAILABLE_CALENDAR')}</div>
				</div>
				<ul class="calendar-sync-slider-list">
					${this.getContentActiveBodySections(this.connection.getId())}
				</ul>
			</div>
		`;
	}

	getContentActiveBodySections(connectionId)
	{
		const sectionList = [];
		this.provider.getConnection().getSections().forEach(section => {
			sectionList.push(Tag.render`
				<li class="calendar-sync-slider-item">
					<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
						<input type="checkbox" class="ui-ctl-element" value="${BX.util.htmlspecialchars(section['ID'])}" onclick="${this.onClickCheckSection.bind(this)}" ${section['ACTIVE'] === 'Y' ? 'checked' : ''}>
						<div class="ui-ctl-label-text">${BX.util.htmlspecialchars(section['NAME'])}</div>
					</label>
				</li>
			`);
		});

		return sectionList;
	}

	onClickCheckSection(event)
	{
		this.sectionStatusObject[event.target.value] = event.target.checked;

		this.runUpdateInfo();
	}

	showAlertPopup()
	{
		const messageBox = new MessageBox({
			className: this.id,
			message: Loc.getMessage('GOOGLE_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
			width: 500,
			offsetLeft: 60,
			offsetTop: 5,
			padding: 7,
			onOk: () => {
				messageBox.close();
			},
			okCaption: 'OK',
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
			popupOptions: {
				zIndexAbsolute: 4020,
				autoHide: true,
			},
		});
		messageBox.show();
	}
}
