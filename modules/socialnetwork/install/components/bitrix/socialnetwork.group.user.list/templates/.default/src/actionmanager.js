import {ajax, Type, Loc, Tag} from 'main.core';
import {PopupManager} from 'main.popup';
import {SendButton, CancelButton} from 'ui.buttons';

import Manager from './manager'

export default class ActionManager
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

	viewProfile(params)
	{
		const userId = parseInt(!Type.isUndefined(params.userId) ? params.userId : 0);
		let pathToUser = (Type.isStringFilled(params.pathToUser) ? params.pathToUser : '');

		if (
			userId <= 0
			|| !Type.isStringFilled(pathToUser)
		)
		{
			return;
		}

		pathToUser = pathToUser.replace('#ID#', userId)
			.replace('#USER_ID#', userId)
			.replace('#user_id#', userId);

		if (this.useSlider)
		{
			BX.SidePanel.Instance.open(
				pathToUser,
				{
					cacheable: false,
					allowChangeHistory: true,
					contentClassName: 'bitrix24-profile-slider-content',
					loader: 'intranet:profile',
					width: 1100,
				}
			);
		}
		else
		{
			window.location.href = pathToUser;
		}
	}

	act(
		params: {
			action: string,
			userId: number,
		}
	)
	{
		return new Promise((resolve, reject) => {
			return ajax.runComponentAction(this.componentName, 'act', {
				mode: 'class',
				signedParameters: this.signedParameters,
				data: {
					action: params.action,
					fields: {
						userId: params.userId,
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
		});
	}

	processActionSuccess(
		params: {
			action: string,
			userId: number,
		}
	)
	{
		let eventCode = null;

		switch (params.action)
		{
			case 'exclude':
				eventCode = 'afterUserExclude';
				break;
			case 'setOwner':
				eventCode = 'afterOwnerSet';
				break;
			case 'setScrumMaster':
				eventCode = 'afterSetScrumMaster';
				break;
			case 'setModerator':
				eventCode = 'afterModeratorAdd';
				break;
			case 'removeModerator':
				eventCode = 'afterModeratorRemove';
				break;
			case 'acceptIncomingRequest':
			case 'rejectIncomingRequest':
				eventCode = 'afterRequestDelete';
				break;
			case 'deleteOutgoingRequest':
				eventCode = 'afterRequestOutDelete';
				break;
			default:
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

		if (params.action === 'reinvite')
		{
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_REINVITE_SUCCESS'),
			});
		}
		else
		{
			this.parent.reload();
		}
	}

	processActionFailure(
		params: {
			action: string,
			userId: number,
			event: PointerEvent,
		},
		errorMessage: ?string
	)
	{
		if (!Type.isStringFilled(errorMessage))
		{
			errorMessage = Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_FAILURE');
		}

		BX.UI.Notification.Center.notify({
			content: errorMessage,
		});
	}

	disconnectDepartment(params)
	{
		const id = parseInt(!Type.isUndefined(params.id) ? params.id : 0);

		if (id <= 0)
		{
			return;
		}

		ajax.runComponentAction(this.componentName, 'disconnectDepartment', {
			mode: 'class',
			signedParameters: this.signedParameters,
			data: {
				fields: {
					id: id,
				},
			},
		}).then((response) => {
			if (response.data.success)
			{
				if (
					top.BX.SidePanel
					&& window !== top.window
				)
				{
					top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
						code: 'afterDeptUnconnect',
					});
				}

				this.parent.reload();
			}
		});
	}

	groupDelete()
	{
		const buttons = [
			new SendButton({
				text: Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_BUTTON_DELETE'),
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

						data[gridInstance.getActionKey()] = 'delete';
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
			id: 'bx-sgul-group-delete-confirm',
			autoHide: false,
			closeByEsc: true,
			buttons: buttons,
			events: {
				onPopupClose: (popup) => {
					popup.destroy();
				},
			},
			content: Tag.render`<div>${Loc.getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_CONFIRM_TEXT')}</div>`,
			padding: 20,
		});

		confirmPopup.show();
	}
}
