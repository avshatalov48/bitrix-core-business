// @flow
'use strict';

import {InterfaceTemplate} from './interfacetemplate';
import {Dom, Loc, Tag} from "main.core";
import ConnectionControls from "../controls/connectioncontrols";

export class CaldavInterfaceTemplate extends InterfaceTemplate
{
	constructor(options)
	{
		super(options);
	}

	getContentInfoBody()
	{
		const formObject = new ConnectionControls();
		const formBlock = formObject.getWrapper();
		const form = formObject.getForm();
		const button = formObject.getAddButton();
		const buttonWrapper = formObject.getButtonWrapper();
		const bodyHeader = this.getContentInfoBodyHeader();

		button.addEventListener('click', (event) => {
			BX.ajax.runAction('calendar.api.calendarajax.analytical', {
				analyticsLabel: {
					click_to_connection_button: 'Y',
					connection_type: this.provider.getType(),
				}
			});

			Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
			event.preventDefault();
			this.sendRequestAddConnection(form);
		});

		Dom.append(button, buttonWrapper);
		Dom.append(buttonWrapper, form);
		Dom.append(form, formBlock);

		return Tag.render`
			${bodyHeader}
			${formBlock}
		`;
	}

	getContentActiveBody()
	{
		const formObject = new ConnectionControls({
			server: this.connection.addParams.server,
			userName: this.connection.addParams.userName,
			connectionName: this.connection.connectionName,
		});
		const formBlock = formObject.getWrapper();
		const form = formObject.getForm();
		const bodyHeader = this.getContentActiveBodyHeader();
		Dom.append(form, formBlock);

		return Tag.render`
			${bodyHeader}
			${formBlock}
		`;
	}

	sendRequestAddConnection(form)
	{
		const fd = new FormData(form);
		BX.ajax.runAction('calendar.api.syncajax.addConnection', {
			data: {
				name: fd.get('name'),
				server: fd.get('server'),
				userName: fd.get('user_name'),
				pass: fd.get('password'),
			}
		}).then((response) => {
			BX.reload();
		}, response => {
			const button = form.querySelector('#connect-button');
			this.showAlertPopup(response.errors[0], button);
		});
	}

	showAlertPopup(alert, button)
	{
		let message = '';
		if (alert.code === 'incorrect_parameters')
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_INCORRECT_PARAMETERS');
		}
		else if (alert.code === 'tech_problem')
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_TECH_PROBLEM');
		}
		else
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_DEFAULT');
		}

		const messageBox = new BX.UI.Dialogs.MessageBox({
			message: message,
			title: alert.message,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
			okCaption: Loc.getMessage('CAL_TEXT_BUTTON_RETURN_TO_SETTINGS'),
			minWidth: 358,
			mediumButtonSize: false,
			popupOptions: {
				zIndex: 3021,
				height: 166,
				width: 358,
				className: 'calendar-alert-popup-connection'
			},
			onOk: () => {
				Dom.removeClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
				return true;
			}
		});

		messageBox.show();
	}
}