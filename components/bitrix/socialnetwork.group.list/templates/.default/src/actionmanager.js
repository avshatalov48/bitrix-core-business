import {ajax, Type, Loc, Tag} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {PopupManager} from 'main.popup';
import {SendButton, CancelButton} from 'ui.buttons';
import {Common} from 'socialnetwork.common';

import Manager from './manager'

export class ActionManager
{
	constructor(
		params: {
			parent: Manager,
		}
	)
	{
		this.parent = params.parent;
		this.componentName = (Type.isStringFilled(this.parent.componentName) ? this.parent.componentName : '');
		this.signedParameters = (Type.isStringFilled(this.parent.signedParameters) ? this.parent.signedParameters : '');
		this.gridId = (Type.isStringFilled(this.parent.gridId) ? this.parent.gridId : '');
		this.useSlider = (Type.isBoolean(this.parent.useSlider) ? this.parent.useSlider : false);
	}

	act(
		params: {
			action: string,
			groupId: number,
			value: ?boolean,
		},
		event: ?KeyboardEvent
	)
	{
		if (event)
		{
			event.stopPropagation();
			event.preventDefault();
		}

		return new Promise((resolve, reject) => {

			if (['addToFavorites', 'removeFromFavorites'].includes(params.action))
			{

				return this.setFavorites({
					groupId: params.groupId,
					value: (params.action === 'addToFavorites'),
				}).then((response) => {
					this.processActionSuccess(params);
				}, (response) => {
					this.processActionFailure(params, response.message);
				});
			}
			else
			{
				return ajax.runComponentAction(this.componentName, 'act', {
					mode: 'class',
					signedParameters: this.signedParameters,
					data: {
						action: params.action,
						fields: {
							groupId: params.groupId,
						},
					},
				}).then((response) => {
					if (response.data.success)
					{
						this.processActionSuccess(params);
					}
					else
					{
						this.processActionFailure(params);
					}
					resolve(response);
				}, (response) => {

					if (response.errors)
					{
						this.processActionFailure(params, response.errors[0].message);
					}
					reject(response);
				});
			}
		});
	}

	processActionSuccess(
		params: {
			action: string,
			groupId: number,
		}
	)
	{
		let eventCode = null;
		let message = '';

		switch (params.action)
		{
			case 'addToFavorites':
				message = Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_ADD_TO_FAVORITES');
				break;
			case 'removeFromFavorites':
				message = Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_REMOVE_FROM_FAVORITES');
				break;
			case 'addToArchive':
				message = Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_ADD_TO_ARCHIVE');
				break;
			case 'removeFromArchive':
				message = Loc.getMessage('SGL_GROUP_ACTION_SUCCESS_NOTIFICATION_REMOVE_FROM_ARCHIVE');
				break;
			case 'join':
				eventCode = 'afterJoinRequestSend';
				break;
			case 'setOwner':
				eventCode = 'afterOwnerSet';
				break;
			case 'setScrumMaster':
				eventCode = 'afterSetScrumMaster';
				break;
			case 'deleteOutgoingRequest':
				eventCode = 'afterRequestOutDelete';
				break;
			case 'deleteIncomingRequest':
				eventCode = 'afterRequestInDelete';
				break;
			default:
		}

		if (message !== '')
		{
			BX.UI.Notification.Center.notify({
				content: message,
			});
		}

		if (
			eventCode
			&& top.BX.SidePanel
			&& window !== top.window
		)
		{
			top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
				code: eventCode,
			});
		}

		if (!BX.PULL)
		{
			this.parent.reload();
		}
	}

	processActionFailure(
		params: {
			action: string,
			groupId: number,
			event: PointerEvent,
		},
		errorMessage: ?string
	)
	{
		if (!Type.isStringFilled(errorMessage))
		{
			errorMessage = Loc.getMessage('SOCIALNETWORK_GROUP_LIST_ACTION_FAILURE');
		}

		BX.UI.Notification.Center.notify({
			content: errorMessage,
		});
	}

	setFavorites(
		params: {
			groupId: number,
			value: boolean,
		}
	)
	{
		const newValue = params.value;
		const oldValue = !params.value;

		return new Promise((resolve, reject) => {
			Common.setFavoritesAjax({
				groupId: params.groupId,
				favoritesValue: oldValue,
				callback: {
					success: (data) => {

						const eventData = {
							code: 'afterSetFavorites',
							data: {
								groupId: data.ID,
								value: (data.RESULT === 'Y'),
							}
						};
						window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);

						if (
							Type.isStringFilled(data.NAME)
							&& Type.isStringFilled(data.URL)
						)
						{
							EventEmitter.emit('BX.Socialnetwork.WorkgroupFavorites:onSet', new BaseEvent({
								compatData: [{
									id: this.groupId,
									name: data.NAME,
									url: data.URL,
									extranet: (Type.isStringFilled(data.EXTRANET) ? data.EXTRANET : 'N'),
								}, newValue],
							}));
						}

						resolve();
					},
					failure: (response) => {
						reject({
							message: response.ERROR,
						});
					}
				}
			});
		});
	}

	groupAction(action)
	{
		let buttonTitle = '';
		switch (action)
		{
			case 'addToArchive':
				buttonTitle = Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_ADD')
				break;
			case 'removeFromArchive':
				buttonTitle = Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_RETURN')
				break;
			case 'delete':
				buttonTitle = Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_BUTTON_DELETE')
				break;
			default:
				action = '';
		}

		if (action === '')
		{
			return;
		}

		const buttons = [
			new SendButton({
				text: buttonTitle,
				events: {
					click: () => {
						PopupManager.getCurrentPopup().destroy();

						const gridInstance = BX.Main.gridManager.getInstanceById(this.gridId);
						if (!gridInstance)
						{
							return;
						}

						const data = {
							ID: gridInstance.getRows().getSelectedIds(),
							apply_filter: 'Y',
						};

						data[gridInstance.getActionKey()] = action;
						data[gridInstance.getForAllKey()] = 'N';

						gridInstance.reloadTable('POST', data);
					},
				},
			}),
			new CancelButton({
				events: {
					click: () => {
						PopupManager.getCurrentPopup().destroy();
					},
				},
			})
		];

		const confirmPopup = PopupManager.create({
			id: 'bx-sgl-group-delete-confirm',
			autoHide: false,
			closeByEsc: true,
			buttons: buttons,
			events: {
				onPopupClose: (popup) => {
					popup.destroy();
				},
			},
			content: Tag.render`<div>${Loc.getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_CONFIRM_TEXT')}</div>`,
			padding: 20,
		});

		confirmPopup.show();
	}
}
