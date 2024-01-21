import { ajax, AjaxError, AjaxResponse, Cache } from 'main.core';
import { MenuAjax } from './menu-ajax';
import { BaseEvent } from 'main.core.events';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { Chat } from './chat';
import { Logo } from './logo';
import { MenuRouter } from './menu-router';
import { ChatAction } from './settings-elements/chat-action';
import { Follow } from './settings-elements/follow';
import { Info } from './settings-elements/info';
import { Member, Members } from './settings-elements/members';
import { Pin } from './settings-elements/pin';
import { Roles } from './settings-elements/roles';

import type { LogoData } from './logo';

type Params = {
	bindElement: HTMLElement,
	groupId: number,
	logo: LogoData,
	chat: Chat,
	router: MenuRouter,
}

type GroupData = {
	name: string,
	isPin: boolean,
	privacyCode: string,
	isSubscribed: boolean,
	numberOfMembers: number,
	listOfMembers: Array<Member>,
	actions: Perms,
	counters: { [key: string]: number },
}

export type Perms = {
	canEdit: boolean,
	canInvite: boolean,
}

export class GroupSettings
{
	#cache = new Cache.MemoryCache();

	#menu: PopupComponentsMaker;

	#layout = {
		members: null,
	};

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#menu = this.#createMenu();
	}

	show()
	{
		if (this.#menu.isShown())
		{
			this.#menu.close();
		}
		else
		{
			this.#menu.show();
		}
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#createMenu(): PopupComponentsMaker
	{
		const groupDataPromise = MenuAjax.getGroupData(this.#getParam('groupId'));

		return new PopupComponentsMaker({
			id: 'spaces-settings-menu',
			target: this.#getParam('bindElement'),
			content: [
				{
					html: [
						{
							html: this.#renderInfo(groupDataPromise),
							backgroundColor: '#fafafa',
						},
					],
				},
				{
					html: [
						{
							html: this.#renderChat(),
							withoutBackground: true,
						},
					],
				},
				{
					html: [
						{
							html: this.#renderMembers(groupDataPromise),
							withoutBackground: true,
						},
					],
				},
				{
					html: [
						{
							html: this.#renderFollow(groupDataPromise),
							backgroundColor: '#fafafa',
						},
						{
							html: this.#renderPin(groupDataPromise),
							backgroundColor: '#fafafa',
						},
						{
							html: this.#renderRoles(),
							backgroundColor: '#fafafa',
						},
					],
				},
			],
		});
	}

	#renderInfo(groupDataPromise: Promise): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			groupDataPromise
				.then((groupData: GroupData) => {
					const info = new Info({
						title: groupData.name,
						logo: new Logo(this.#getParam('logo')),
						privacyCode: groupData.privacyCode,
						actions: groupData.actions,
					});
					info.subscribe('changePrivacy', this.#changePrivacy.bind(this));
					info.subscribe('changeTitle', this.#changeTitle.bind(this));
					info.subscribe('setAutoHide', (baseEvent: BaseEvent) => {
						this.#menu.getPopup().setAutoHide(baseEvent.getData());
					});
					info.subscribe('more', () => {
						console.log('more');
					});

					resolve(info.render());
				})
			;
		});
	}

	#renderChat(): HTMLElement
	{
		const chat = new ChatAction();

		chat.subscribe('videoCall', () => {
			this.#menu.close();
			this.#getParam('chat').startVideoCall();
		});
		chat.subscribe('openChat', () => {
			this.#menu.close();
			this.#getParam('chat').openChat();
		});
		chat.subscribe('createChat', () => {
			this.#menu.close();
			this.#getParam('chat').createChat(this.#getParam('bindElement'));
		});

		return chat.render();
	}

	update(groupDataPromise: Promise)
	{
		this.#renderMembers(groupDataPromise);
	}

	#renderMembers(groupDataPromise: Promise): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			groupDataPromise
				.then((groupData: GroupData) => {
					const members = new Members({
						amount: groupData.numberOfMembers,
						list: groupData.listOfMembers,
						counters: groupData.counters,
						actions: groupData.actions,
					});
					members.subscribe('showUsers', (baseEvent: BaseEvent<'all' | 'in' | 'out'>) => {
						this.#menu.close();
						this.#getParam('router').openGroupUsers(baseEvent.getData());
					});
					members.subscribe('invite', () => {
						this.#menu.close();
						this.#getParam('router').openGroupInvite();
					});

					const layoutMembers = members.render();
					if (this.#layout.members)
					{
						this.#layout.members.replaceWith(layoutMembers);
					}

					this.#layout.members = layoutMembers;

					resolve(this.#layout.members);
				})
			;
		});
	}

	#renderFollow(groupDataPromise: Promise): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			groupDataPromise
				.then((groupData: GroupData) => {
					const follow = new Follow({
						follow: groupData.isSubscribed,
					});

					follow.subscribe('update', (baseEvent: BaseEvent) => {
						this.#changeSubscribe(
							this.#getParam('groupId'),
							baseEvent.getData() === true ? 'Y' : 'N',
							follow,
						);
					});

					resolve(follow.render());
				})
			;
		});
	}

	#renderPin(groupDataPromise: Promise): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			groupDataPromise
				.then((groupData: GroupData) => {
					const pin = new Pin({
						pin: groupData.isPin,
					});

					pin.subscribe('update', (baseEvent: BaseEvent) => {
						this.#changePin(
							this.#getParam('groupId'),
							baseEvent.getData() === true ? 'pin' : 'unpin',
							pin,
						);
					});

					resolve(pin.render());
				})
			;
		});
	}

	#renderRoles(): HTMLElement
	{
		const roles = new Roles();

		roles.subscribe('click', () => {
			this.#menu.close();
			this.#getParam('router').openGroupFeatures();
		});

		return roles.render();
	}

	#changeSubscribe(groupId: number, value: 'Y' | 'N', follow: Follow)
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
			data: {
				params: {
					groupId,
					value,
				},
			},
		})
			.then((response: AjaxResponse) => {
				follow.unDisable();
			})
			.catch((error: AjaxError) => {
				follow.unDisable();

				this.#consoleError('changeSubscribe', error);
			})
		;
	}

	#changePin(groupId: number, action: 'pin' | 'unpin', pin: Pin)
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('socialnetwork.api.workgroup.changePin', {
			data: {
				groupIdList: [groupId],
				action: action,
			},
		})
			.then((response: AjaxResponse) => {
				pin.unDisable();
			})
			.catch((error: AjaxError) => {
				pin.unDisable();

				this.#consoleError('changePin', error);
			})
		;
	}

	#changePrivacy(baseEvent: BaseEvent)
	{
		const privacyCode: 'open' | 'closed' | 'secret' = baseEvent.getData();

		const fields = {};
		if (privacyCode === 'open')
		{
			fields.VISIBLE = 'Y';
			fields.OPENED = 'Y';
			fields.EXTERNAL = 'N';
		}

		if (privacyCode === 'closed')
		{
			fields.VISIBLE = 'Y';
			fields.OPENED = 'N';
			fields.EXTERNAL = 'N';
		}

		if (privacyCode === 'secret')
		{
			fields.VISIBLE = 'N';
			fields.OPENED = 'N';
			fields.EXTERNAL = 'N';
		}

		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId: this.#getParam('groupId'),
				fields: fields,
			},
		})
			.then((response: AjaxResponse) => {})
			.catch((error: AjaxError) => {
				this.#consoleError('changePrivacy', error);
			})
		;
	}

	#changeTitle(baseEvent: BaseEvent)
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId: this.#getParam('groupId'),
				fields: {
					NAME: baseEvent.getData(),
				},
			},
		})
			.then((response: AjaxResponse) => {})
			.catch((error: AjaxError) => {
				this.#consoleError('changeTitle', error);
			})
		;
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`GroupSettings: ${action} error`, error);
	}
}