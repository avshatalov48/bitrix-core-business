/**
 * Bitrix Messenger
 * Im call pull commands (Pull Command Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {PullClient} from "pull.client";
import {CallErrorCode} from "im.const";

export class ImCallPullHandler
{
	static create(params = {})
	{
		return new this(params);
	}

	constructor(params = {})
	{
		if (typeof params.application === 'object' && params.application)
		{
			this.application = params.application;
		}
		if (typeof params.controller === 'object' && params.controller)
		{
			this.controller = params.controller;
		}
		if (typeof params.store === 'object' && params.store)
		{
			this.store = params.store;
		}

		this.option = typeof params.store === 'object' && params.store? params.store: {};
	}

	getModuleId()
	{
		return 'im';
	}

	getSubscriptionType()
	{
		return PullClient.SubscriptionType.Server;
	}

	handleChatUserAdd(params)
	{
		const users = Object.values(params.users).map(user => {
			return {...user, lastActivityDate: new Date()};
		});
		this.store.commit('callApplication/common', {userCount: params.userCount});
		this.store.commit('users/set', users);
	}

	handleChatUserLeave(params)
	{
		if (params.userId === this.controller.getUserId() && params.dialogId === this.store.state.application.dialog.dialogId)
		{
			this.application.kickFromCall();
		}

		this.store.commit('callApplication/common', {userCount: params.userCount});
	}

	handleCallUserNameUpdate(params)
	{
		this.store.dispatch('users/update', {
			id: params.userId,
			fields: {name: params.name, lastActivityDate: new Date()}
		});
	}

	handleVideoconfShareUpdate(params)
	{
		if (params.dialogId === this.store.state.application.dialog.dialogId)
		{
			this.application.changeVideoconfUrl(params.newLink);
		}
	}

	handleMessageChat(params)
	{
		if (
			params.chatId === this.application.getChatId() &&
			!this.store.state.callApplication.common.showChat &&
			params.message.senderId !== this.controller.getUserId()
		)
		{
			let text = '';

			if (params.message.senderId === 0 || params.message.system === 'Y')
			{
				text = params.message.text;
			}
			else
			{
				const userName = params.users[params.message.senderId].name;

				if (params.message.text === '' && Object.keys(params.files).length > 0)
				{
					text = `${userName}: ${this.controller.localize['BX_IM_COMPONENT_CALL_FILE']}`;
				}
				else if (params.message.text !== '')
				{
					text = `${userName}: ${params.message.text}`;
				}
			}

			this.application.sendNewMessageNotify(text);
		}
	}
}