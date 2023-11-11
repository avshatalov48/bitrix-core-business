import { ControlButton } from 'intranet.control-button';
import { Event, Loc, Type } from 'main.core';
import { Util } from 'calendar.util';

export class IntranetButton
{
	constructor(params = {})
	{
		this.intranetControllButton = new ControlButton(params.intranetControlButtonParams);
		this.hasChat = params.callbacks.hasChat;
		this.getUsersCount = params.callbacks.getUsersCount;

		if (Type.isElementNode(this.intranetControllButton.button))
		{
			this.openChat = this.intranetControllButton.openChat.bind(this.intranetControllButton);
			this.intranetControllButton.openChat = this.openChatWithConfirm.bind(this);

			this.startVideoCall = this.intranetControllButton.startVideoCall.bind(this.intranetControllButton);
			this.intranetControllButton.startVideoCall = this.startVideoCallWithConfirm.bind(this);

			const chatButton = this.intranetControllButton.button.querySelector('button.ui-btn-main');

			if (params.intranetControlButtonParams.mainItem === 'chat')
			{
				this.setClickListener(chatButton, this.openChatWithConfirm.bind(this));
			}
			else
			{
				this.setClickListener(chatButton, this.startVideoCallWithConfirm.bind(this));
			}

			// For testing purposes
			this.intranetControllButton.button.setAttribute('data-role', 'videocallButton');
		}
	}

	openChatWithConfirm()
	{
		if (this.shouldNotConfirmOpenChat())
		{
			this.openChat();
			return;
		}

		Util.showConfirmPopup(this.openChat, Loc.getMessage('EC_CREATE_CHAT_CONFIRM_QUESTION'), {
			okCaption: Loc.getMessage('EC_CREATE_CHAT_OK'),
			minWidth: 350,
			maxWidth: 350,
		});
	}

	startVideoCallWithConfirm()
	{
		if (this.shouldNotConfirmOpenChat())
		{
			this.startVideoCall();
			return;
		}

		Util.showConfirmPopup(this.startVideoCall, Loc.getMessage('EC_START_VIDEOCONFERENCE_CONFIRM_QUESTION'), {
			okCaption: Loc.getMessage('EC_START_VIDEOCONFERENCE_OK'),
			minWidth: 350,
			maxWidth: 350,
		});
	}

	shouldNotConfirmOpenChat()
	{
		return this.hasChat() || this.getUsersCount() < 10;
	}

	setClickListener(element, handler)
	{
		const clonedNode = element.cloneNode(true);
		Event.bind(clonedNode, 'click', handler);
		element.parentNode.replaceChild(clonedNode, element);
	}

	destroy()
	{
		if (this.intranetControllButton && this.intranetControllButton.destroy)
		{
			this.intranetControllButton.destroy();
			this.intranetControllButton = null;
		}
	}
}