import {Type, Loc, Dom, ajax} from 'main.core';
import {Popup} from 'main.popup';
import {Button} from 'ui.buttons';
import {Waiter} from "./waiter";

export class RecallJoinRequest
{
	constructor(params)
	{
		this.successPopup = null;

		this.groupId = !Type.isUndefined(params.GROUP_ID) ? Number(params.GROUP_ID) : 0;
		this.relationId = !Type.isUndefined(params.RELATION_ID) ? Number(params.RELATION_ID) : 0;

		this.urls = {
			rejectOutgoingRequest: Type.isStringFilled(params.URL_REJECT_OUTGOING_REQUEST) ? params.URL_REJECT_OUTGOING_REQUEST : '',
			groupsList: Type.isStringFilled(params.URL_GROUPS_LIST) ? params.URL_GROUPS_LIST : '',
		};
		this.project = Type.isBoolean(params.PROJECT) ? params.PROJECT : false;
		this.scrum = Type.isBoolean(params.SCRUM) ? params.SCRUM : false;
	}

	showPopup()
	{
		if (
			this.relationId <= 0
			|| !Type.isStringFilled(this.urls.rejectOutgoingRequest)
		)
		{
			return;
		}

		let recallTitle = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2');
		let recallText = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2');

		if (this.scrum)
		{
			recallTitle = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2_SCRUM');
			recallText = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2_SCRUM');
		}
		else if (this.project)
		{
			recallTitle = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE2_PROJECT');
			recallText = Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT2_PROJECT');
		}

		this.successPopup = new Popup('bx-group-join-successfull-request-popup', window, {
			width: 420,
			autoHide: false,
			lightShadow: false,
			zIndex: 1000,
			overlay: true,
			cachable: false,
			content: Dom.create('DIV', {
				children: [
					Dom.create('DIV', {
						text: recallTitle,
						props: {
							className: 'sonet-group-join-successfull-request-popup-title',
						}
					}),
					Dom.create('DIV', {
						text: recallText,
						props: {
							className: 'sonet-group-join-successfull-request-popup-text',
						}
					}),
				]
			}),
			buttons: [
				new Button({
					size: Button.Size.MEDIUM,
					text: Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_CLOSE_BUTTON'),
					events: {
						click: (button) => {
							this.onClose(button.getContainer());
						},
					},
				}),
				new Button({
					size: Button.Size.MEDIUM,
					color: Button.Color.LINK,
					text: Loc.getMessage('SONET_EXT_COMMON_RECALL_JOIN_POPUP_CANCEL_BUTTON'),
					events: {
						click: (button) => {
							this.onCancelRequest(button.getContainer());
						},
					}
				}),
			],
			closeByEsc: false,
			closeIcon: false,
		});

		this.successPopup.show();
	}

	onClose(button)
	{
		if (
			this.groupId <= 0
			|| !Type.isDomNode(button)
		)
		{
			return;
		}

		RecallJoinRequest.showButtonWait(button);

		ajax.runAction('socialnetwork.api.usertogroup.setHideRequestPopup', {
			data: {
				groupId: this.groupId,
			}
		}).then((response) => {
			RecallJoinRequest.hideButtonWait(button);
			this.successPopup.close();
		}, () => {
			RecallJoinRequest.hideButtonWait(button);
		});
	}

	onCancelRequest(button)
	{
		if (
			this.groupId <= 0
			|| !Type.isDomNode(button)
		)
		{
			return;
		}

		const errorNode = document.getElementById('bx-group-delete-request-error');

		RecallJoinRequest.hideError(errorNode);
		RecallJoinRequest.showButtonWait(button);

		ajax.runAction('socialnetwork.api.usertogroup.cancelIncomingRequest', {
			data: {
				groupId: this.groupId,
				userId: parseInt(Loc.getMessage('USER_ID')),
			},
		}).then((response) => {

			RecallJoinRequest.hideButtonWait(button);

			this.successPopup.destroy();
			if (Type.isStringFilled(this.urls.groupsList))
			{
				top.location.href = this.urls.groupsList;
			}

			this.reload();
		}).catch((response) => {
			RecallJoinRequest.showError(Loc.getMessage('SONET_EXT_COMMON_AJAX_ERROR'), errorNode);
//			RecallJoinRequest.showError(deleteResponseData.ERROR_MESSAGE, errorNode);
			RecallJoinRequest.hideButtonWait(button);
		});
	}

	static showButtonWait(buttonNode)
	{
		if (Type.isStringFilled(buttonNode))
		{
			buttonNode = document.getElementById(buttonNode);
		}

		if (!Type.isDomNode(buttonNode))
		{
			return;
		}

		buttonNode.classList.add('ui-btn-clock');
		buttonNode.disabled = true;
		buttonNode.style.cursor = 'auto';
	}

	static hideButtonWait(buttonNode)
	{
		if (Type.isStringFilled(buttonNode))
		{
			buttonNode = document.getElementById(buttonNode);
		}

		if (!Type.isDomNode(buttonNode))
		{
			return;
		}

		buttonNode.classList.remove('ui-btn-clock');
		buttonNode.disabled = false;
		buttonNode.style.cursor = 'cursor';
	}

	static showError(errorText, errorNode)
	{
		if (Type.isStringFilled(errorNode))
		{
			errorNode = document.getElementById(errorNode);
		}

		if (!Type.isDomNode(errorNode))
		{
			return;
		}

		errorNode.innerHTML = errorText;
		errorNode.classList.remove('sonet-ui-form-error-block-invisible');
	}

	static hideError(errorNode)
	{
		if (Type.isStringFilled(errorNode))
		{
			errorNode = document.getElementById(errorNode);
		}

		if (!Type.isDomNode(errorNode))
		{
			return;
		}

		errorNode.classList.add('sonet-ui-form-error-block-invisible');
	}
}
