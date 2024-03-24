import { Loc, Tag, Extension } from 'main.core';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { ChatActionType, ChatType, RestMethod } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { BaseMenu } from 'im.v2.lib.menu';
import { CallManager } from 'im.v2.lib.call';
import { runAction } from 'im.v2.lib.rest';

import { CallTypes } from '../types/call-types';

import type { ImModelChat, ImModelUser } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';
import type { PopupOptions } from 'main.popup';

export class CallMenu extends BaseMenu
{
	context: ImModelChat;

	static events = {
		onMenuItemClick: 'onMenuItemClick',
	};

	constructor()
	{
		super();

		this.id = 'bx-im-chat-header-call-menu';
	}

	getMenuOptions(): PopupOptions
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 4,
			offsetTop: 5,
		};
	}

	getMenuClassName(): String
	{
		return 'bx-im-messenger__scope bx-im-chat-header-call-button__scope';
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.#getVideoCallItem(),
			this.#getAudioCallItem(),
			this.#getZoomItem(),
			this.#getDelimiter(),
			this.#getPersonalPhoneItem(),
			this.#getWorkPhoneItem(),
			this.#getInnerPhoneItem(),
		];
	}

	#getDelimiter(): MenuItem
	{
		return { delimiter: true };
	}

	#getVideoCallItem(): MenuItem
	{
		const isAvailable = this.#isCallAvailable(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_VIDEOCALL'),
			onclick: () => {
				if (!isAvailable)
				{
					return;
				}
				CallTypes.video.start(this.context.dialogId);
				this.emit(CallMenu.events.onMenuItemClick, CallTypes.video);
				this.menuInstance.close();
			},
			disabled: !isAvailable,
		};
	}

	#getAudioCallItem(): MenuItem
	{
		const isAvailable = this.#isCallAvailable(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO'),
			onclick: () => {
				if (!isAvailable)
				{
					return;
				}
				CallTypes.audio.start(this.context.dialogId);

				this.emit(CallMenu.events.onMenuItemClick, CallTypes.audio);
				this.menuInstance.close();
			},
			disabled: !isAvailable,
		};
	}

	#getPersonalPhoneItem(): ?MenuItem
	{
		if (!this.#isUser())
		{
			return null;
		}

		const { phones } = this.#getUser();
		if (!phones.personalMobile)
		{
			return null;
		}

		const title = Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_PERSONAL_PHONE');

		return {
			className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
			html: this.#getUserPhoneHtml(title, phones.personalMobile),
			onclick: () => {
				Messenger.startPhoneCall(phones.personalMobile);
				this.menuInstance.close();
			},
		};
	}

	#getWorkPhoneItem(): ?MenuItem
	{
		if (!this.#isUser())
		{
			return null;
		}

		const { phones } = this.#getUser();
		if (!phones.workPhone)
		{
			return null;
		}

		const title = Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_WORK_PHONE');

		return {
			className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
			html: this.#getUserPhoneHtml(title, phones.workPhone),
			onclick: () => {
				Messenger.startPhoneCall(phones.workPhone);
				this.menuInstance.close();
			},
		};
	}

	#getInnerPhoneItem(): ?MenuItem
	{
		if (!this.#isUser())
		{
			return null;
		}

		const { phones } = this.#getUser();
		if (!phones.innerPhone)
		{
			return null;
		}

		const title = Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_INNER_PHONE_MSGVER_1');

		return {
			className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
			html: this.#getUserPhoneHtml(title, phones.innerPhone),
			onclick: () => {
				Messenger.startPhoneCall(phones.innerPhone);
				this.menuInstance.close();
			},
		};
	}

	#getZoomItem(): ?MenuItem
	{
		const settings = Extension.getSettings('im.v2.component.content.chat');
		const isActive = settings.get('isZoomActive', false);
		if (!isActive)
		{
			return null;
		}

		const classNames = ['bx-im-chat-header-call-button-menu__zoom', 'menu-popup-no-icon'];
		const isFeatureAvailable = settings.get('isZoomFeatureAvailable', false);
		if (!isFeatureAvailable)
		{
			classNames.push('--disabled');
		}

		return {
			className: classNames.join(' '),
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM'),
			onclick: () => {
				if (!isFeatureAvailable)
				{
					BX.UI.InfoHelper.show('limit_video_conference_zoom');

					return;
				}

				this.#requestCreateZoomConference(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	#getUserPhoneHtml(title, phoneNumber): HTMLSpanElement
	{
		return Tag.render`
			<span class="bx-im-chat-header-call-button-menu__phone_container">
				<span class="bx-im-chat-header-call-button-menu__phone_title">${title}</span>
				<span class="bx-im-chat-header-call-button-menu__phone_number">${phoneNumber}</span>
			</span>
		`;
	}

	#isCallAvailable(dialogId: String): boolean
	{
		if (
			Core.getStore().getters['recent/calls/hasActiveCall'](dialogId)
			&& CallManager.getInstance().getCurrentCallDialogId() === dialogId
		)
		{
			return true;
		}

		if (Core.getStore().getters['recent/calls/hasActiveCall']())
		{
			return false;
		}

		const chatCanBeCalled = CallManager.getInstance().chatCanBeCalled(dialogId);
		const chatIsAllowedToCall = PermissionManager.getInstance().canPerformAction(ChatActionType.call, dialogId);

		return chatCanBeCalled && chatIsAllowedToCall;
	}

	#getUser(): ?ImModelUser
	{
		if (!this.#isUser())
		{
			return null;
		}

		return Core.getStore().getters['users/get'](this.context.dialogId);
	}

	#isUser(): true
	{
		return this.context.type === ChatType.user;
	}

	#requestCreateZoomConference(dialogId: string)
	{
		runAction(RestMethod.imV2CallZoomCreate, { data: { dialogId } })
			.catch((errors) => {
				let errorText = Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM_CREATE_ERROR');

				const notConnected = errors.some((error) => error.code === 'ZOOM_CONNECTED_ERROR');
				if (notConnected)
				{
					const userProfileUri = `/company/personal/user/${Core.getUserId()}/social_services/`;
					errorText = Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM_CONNECT_ERROR')
						.replace('#HREF_START#', `<a href=${userProfileUri}>`)
						.replace('#HREF_END#', '</>');
				}

				BX.UI.Notification.Center.notify({
					content: errorText,
				});
			});
	}
}
