// @flow
'use strict';

import {Loc, Tag} from "main.core";

export default class ConnectionControls
{
	userName = null;
	server = null;
	connectionName = null;

	constructor(options = null)
	{
		this.addButtonText = Loc.getMessage('CAL_UPPER_CONNECT');
		this.removeButtonText = Loc.getMessage('CAL_UPPER_DISCONNECT');
		this.saveButtonText = Loc.getMessage('CAL_UPPER_SAVE');

		if (options !== null)
		{
			this.userName = BX.util.htmlspecialchars(options.userName);
			this.server = BX.util.htmlspecialchars(options.server);
			this.connectionName = BX.util.htmlspecialchars(options.connectionName);
		}
	}

	getWrapper()
	{
		return Tag.render `
			<div class="calendar-sync-slider-section calendar-sync-slider-section-form"></div>
		`;
	}

	getForm()
	{
		return Tag.render`
			<form class="calendar-sync-slider-form" action="">
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${Loc.getMessage('CAL_TEXT_NAME')}\" name="name" value="${this.connectionName || ''}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${Loc.getMessage('CAL_TEXT_SERVER_ADDRESS')}\" name="server" value="${this.server || ''}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${Loc.getMessage('CAL_TEXT_USER_NAME')}\" name="user_name" value="${this.userName || ''}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="password" class="ui-ctl-element" name="password" placeholder=\"${Loc.getMessage('CAL_TEXT_PASSWORD')}\">
					</div>
				</div>
			</form>
		`;
	}

	getAddButton()
	{
		return Tag.render `
			<button id="connect-button" class="ui-btn ui-btn-light-border">${this.addButtonText}</button>
		`;
	}

	getDisconnectButton()
	{
		return Tag.render`
			<button id="disconnect-button" class="calendar-sync-slider-btn ui-btn ui-btn-light-border">${this.removeButtonText}</button>
		`;
	}

	getSaveButton()
	{
		return Tag.render`
			<button id="edit-connect-button" class="calendar-sync-slider-btn ui-btn ui-btn-light-border">${this.saveButtonText}</button>
		`;
	}

	getButtonWrapper()
	{
		return Tag.render`
			<div class="calendar-sync-slider-form-btn"></div>
		`;
	}
}