import { AjaxError, Cache, Dom } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Controller, GroupData } from 'socialnetwork.controller';
import { LogoData } from 'socialnetwork.logo';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { Chat } from './chat';
import { ChatAction } from './settings-elements/chat-action';
import { Follow } from './settings-elements/follow';
import { Info } from './settings-elements/info';
import { Members } from './settings-elements/members';
import { Pin } from './settings-elements/pin';
import { Roles } from './settings-elements/roles';

type Params = {
	bindElement: HTMLElement,
	groupId: number,
	logo: LogoData,
	chat: Chat,
	availableFeatures: { [option: 'discussions' | 'tasks' | 'calendar' | 'files']: boolean },
	isMember?: boolean,
}

export class GroupSettings
{
	#cache = new Cache.MemoryCache();

	#menu: PopupComponentsMaker;
	#info: Info;
	#groupData: GroupData;
	#groupSettings;

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
		this.#menu.show();
		this.#adjustPopup();
	}

	update(groupDataPromise: Promise)
	{
		// eslint-disable-next-line promise/catch-or-return
		groupDataPromise.then((groupData: GroupData) => {
			this.#groupData = groupData;
			this.#info.update(groupData);
		});

		this.#renderMembers(groupDataPromise);
	}

	#adjustPopup()
	{
		const popup = this.#menu.getPopup();
		const popupContainer = popup.getPopupContainer();
		const popupRect = popupContainer.getBoundingClientRect();

		if (Math.abs(popupRect.right - popup.bindElement.getBoundingClientRect().right) >= 2)
		{
			return;
		}

		const left = popupRect.left;
		const leftAdjusted = popupRect.right + 20 - popupRect.width;

		const angleContainer = popup.angle.element;

		Dom.style(popupContainer, 'left', `${leftAdjusted}px`);
		Dom.style(
			angleContainer,
			'left',
			`${parseInt(Dom.style(angleContainer, 'left'), 10) - (leftAdjusted - left)}px`,
		);
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
		const groupDataPromise = Controller.getGroupData(
			this.#getParam('groupId'),
			[
				'AVATAR',
				'ACTIONS',
				'NUMBER_OF_MEMBERS',
				'LIST_OF_MEMBERS',
				'GROUP_MEMBERS_LIST',
				'PRIVACY_TYPE',
				'PIN',
				'USER_DATA',
				'COUNTERS',
				'DESCRIPTION',
				'EFFICIENCY',
				'SUBJECT_DATA',
				'DATE_CREATE',
			],
		);
		// eslint-disable-next-line promise/catch-or-return
		groupDataPromise.then((groupData: GroupData) => {
			this.#groupData = groupData;
		});

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
							html: this.#renderRoles(groupDataPromise),
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
					this.#info = new Info({
						groupId: groupData.id,
						title: groupData.name,
						logo: this.#getParam('logo'),
						privacyCode: groupData.privacyCode,
						actions: groupData.actions,
					});
					this.#info.subscribe('setAutoHide', (baseEvent: BaseEvent) => {
						this.#menu.getPopup().setAutoHide(baseEvent.getData());
					});
					this.#info.subscribe('more', this.#openSettingsSlider.bind(this));

					resolve(this.#info.render());
				})
			;
		});
	}

	#renderChat(): Promise
	{
		return new Promise((resolve) => {
			const chat = new ChatAction({
				canUse: this.#getParam('isMember'),
			});

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

			resolve({
				node: chat.render(),
				options: {
					disabled: !this.#getParam('isMember'),
				},
			});
		});
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
						Controller.openGroupUsers(baseEvent.getData());
					});
					members.subscribe('invite', () => {
						this.#menu.close();
						Controller.openGroupInvite();
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
						canUse: this.#getParam('isMember'),
					});

					follow.subscribe('update', (baseEvent: BaseEvent) => {
						this.#changeSubscribe(this.#groupData.id, baseEvent.getData(), follow);
					});

					resolve({
						node: follow.render(),
						options: {
							disabled: !this.#getParam('isMember'),
						},
					});
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
						canUse: this.#getParam('isMember'),
					});

					pin.subscribe('update', (baseEvent: BaseEvent) => {
						this.#changePin(this.#groupData.id, baseEvent.getData(), pin);
					});

					resolve({
						node: pin.render(),
						options: {
							disabled: !this.#getParam('isMember'),
						},
					});
				})
			;
		});
	}

	#renderRoles(groupDataPromise: Promise): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			groupDataPromise
				.then((groupData: GroupData) => {
					const roles = new Roles({
						canEdit: groupData.actions.canEditFeatures,
					});

					roles.subscribe('click', () => {
						this.#menu.close();
						Controller.openGroupFeatures();
					});

					resolve({
						node: roles.render(),
						options: {
							disabled: !groupData.actions.canEditFeatures,
						},
					});
				})
			;
		});
	}

	#changeSubscribe(groupId: number, isSubscribed: boolean, follow: Follow)
	{
		Controller.setSubscription(groupId, isSubscribed).then(() => {
			follow?.unDisable();
		}).catch((error: AjaxError) => {
			follow?.unDisable();

			this.#consoleError('changeSubscribe', error);
		});
	}

	#changePin(groupId: number, isPinned: boolean, pin: Pin)
	{
		Controller.changePin(groupId, isPinned).then(() => {
			pin?.unDisable();
		}).catch((error: AjaxError) => {
			pin?.unDisable();

			this.#consoleError('changePin', error);
		});
	}

	async #openSettingsSlider(): void
	{
		(await this.#getGroupSettings()).openInSlider();
		this.#menu.close();
	}

	async #getGroupSettings(): Promise
	{
		this.#groupSettings ??= await this.#createGroupSettings();

		return this.#groupSettings;
	}

	async #createGroupSettings(): Promise
	{
		const { GroupSettings } = await top.BX.Runtime.loadExtension('socialnetwork.group-settings');

		return new GroupSettings({
			groupData: this.#groupData,
			logo: this.#getParam('logo'),
		});
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`GroupSettings: ${action} error`, error);
	}
}
