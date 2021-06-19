/**
 * Bitrix Messenger
 * Im call pull commands (Pull Command Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {PullClient} from "pull.client";
import {ConferenceRightPanelMode as RightPanelMode} from 'im.const';

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
		this.store.commit('conference/common', {userCount: params.userCount});
		this.store.dispatch('users/set', users);
		this.store.dispatch('conference/setUsers', {users: users.map(user => user.id)});
	}

	handleChatUserLeave(params)
	{
		if (params.userId === this.controller.getUserId() && params.dialogId === this.store.state.application.dialog.dialogId)
		{
			this.application.kickFromCall();
		}

		this.store.commit('conference/common', {userCount: params.userCount});
		this.store.dispatch('conference/removeUsers', {users: [params.userId]});
	}

	handleCallUserNameUpdate(params)
	{
		const currentUser = this.store.getters['users/get'](params.userId);
		if (!currentUser)
		{
			this.store.dispatch('users/set', {
				id: params.userId,
				lastActivityDate: new Date()
			});
		}
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
		const rightPanelMode = this.store.state.conference.common.rightPanelMode;
		if (
			params.chatId === this.application.getChatId() &&
			(rightPanelMode !== RightPanelMode.chat && rightPanelMode !== RightPanelMode.split) &&
			params.message.senderId !== this.controller.getUserId() &&
			!this.store.state.conference.common.error
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

	handleChatRename(params)
	{
		if (params.chatId !== this.application.getChatId())
		{
			return false;
		}

		this.store.dispatch('conference/setConferenceTitle', {conferenceTitle: params.name});
	}

	handleConferenceUpdate(params)
	{
		if (params.chatId !== this.application.getChatId())
		{
			return false;
		}

		if (params.isBroadcast !== '')
		{
			this.store.dispatch('conference/setBroadcastMode', {broadcastMode: params.isBroadcast});
		}

		if (params.presenters.length > 0)
		{
			this.store.dispatch('conference/setPresenters', {presenters: params.presenters, replace: true});
		}
	}
}