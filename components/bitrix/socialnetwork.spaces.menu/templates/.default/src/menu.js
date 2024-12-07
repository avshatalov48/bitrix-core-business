import { Loc, Dom, Type, Tag, Event, Cache, Runtime, Uri } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { PULL as Pull } from 'pull.client';
import { Meetings } from 'tasks.scrum.meetings';
import { Methodology } from 'tasks.scrum.methodology';
import { PullRequests } from './pull-requests';
import { Chat } from './chat';
import { Invite } from './invite';
import { Logo, LogoData } from 'socialnetwork.logo';
import { Settings } from './settings';
import { VideoCall } from './video-call';
import { Controller } from 'socialnetwork.controller';

import type { GroupData } from 'socialnetwork.group-settings';

import './css/menu.css';

type Params = {
	type: 'group' | 'user',
	entityId?: number,
	currentUserId: number,
	groupMembersList: any,
	logo?: LogoData,
	pathToFeatures?: string,
	pathToDiscussions?: string,
	pathToUsers?: string,
	pathToInvite?: string,
	pathToScrumTeamSpeed?: string,
	pathToScrumBurnDown?: string,
	pathToGroupTasksTask?: string,
	canInvite: boolean,
	availableFeatures: { [option: 'discussions' | 'tasks' | 'calendar' | 'files']: boolean },
	isNew?: boolean,
	isMember?: boolean,
};

export class Menu
{
	#cache = new Cache.MemoryCache();

	#videoCall: ?VideoCall;
	#scrumMeetings: ?Meetings;
	#scrumMethodology: ?Methodology;
	#invite: ?Invite;
	#layout: {
		avatar: HTMLElement,
		inviteNode: HTMLElement,
	};

	#logo: LogoData;
	#settings: ?Settings;
	#pathToDiscussions: string;

	#chat: Chat;
	#discussionAhaMomentShown = false;

	#groupInvitedList: any = [];

	constructor(params: Params)
	{
		this.#layout = {};

		// eslint-disable-next-line no-param-reassign
		params.entityId = Type.isUndefined(params.entityId) ? 0 : parseInt(params.entityId, 10);

		this.#setParams(params);

		this.#initServices(params);

		this.#subscribeToPull();
	}

	renderLogoTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.Menu: HTMLElement for space not found');
		}

		this.#layout.avatar = container;
		this.#renderSpaceAvatar();
	}

	renderUserLogoTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.Menu: HTMLElement for space not found');
		}

		Dom.addClass(
			container,
			[
				'sonet-common-workgroup-avatar',
				'--common-space',
			],
		);

		Dom.append(Tag.render`<i></i>`, container);
	}

	renderToolbarTo(container: HTMLElement)
	{
		if (this.#getParam('isMember'))
		{
			Dom.append(this.#renderVideoCall(), container);
		}

		if (this.#getParam('canInvite'))
		{
			Dom.append(this.#renderInvite(), container);
		}

		Dom.append(this.#renderSettings(), container);
	}

	renderScrumToolbarTo(container: HTMLElement)
	{
		const availableFeatures = this.#getParam('availableFeatures');

		if (
			this.#getParam('isMember')
			&& availableFeatures.tasks
			&& availableFeatures.calendar
		)
		{
			Dom.append(this.#renderScrumVideoCall(), container);
		}

		if (availableFeatures.tasks)
		{
			Dom.append(this.#renderScrumElements(), container);
		}

		if (this.#getParam('canInvite'))
		{
			Dom.append(this.#renderInvite(), container);
		}

		Dom.append(this.#renderSettings(), container);
	}

	renderUserToolbarTo(container: HTMLElement)
	{
		Dom.append(this.#renderUserVideoCall(), container);
		Dom.append(this.#renderUserSettings(), container);
	}

	#subscribeToPull()
	{
		const pullRequests = new PullRequests(
			this.#getParam('entityId'),
			this.#getParam('currentUserId'),
		);
		pullRequests.subscribe('update', this.#update.bind(this));
		pullRequests.subscribe('updateCounters', this.#updateCounters.bind(this));
		pullRequests.subscribe('updateMenuItem', this.#updateMenuItem.bind(this));

		Pull.subscribe(pullRequests);
	}

	#update()
	{
		const groupDataPromise = Controller.getGroupData(
			this.#getParam('entityId'),
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
		this.#settings?.update(groupDataPromise);
		this.#getInvite().update(groupDataPromise);
		this.#chat.update(groupDataPromise);

		// eslint-disable-next-line promise/catch-or-return
		groupDataPromise.then((groupData: GroupData) => {
			const { avatar } = groupData;

			if (avatar)
			{
				this.#setAvatar(avatar);
			}
		});
	}

	#updateCounters(baseEvent: BaseEvent)
	{
		const data = baseEvent.getData();
		if (Type.isUndefined(data.space))
		{
			return;
		}

		const userId = data.userId;
		const spaceId = parseInt(data.space.id, 10);

		const tasksTotal = data.space.metrics.countersTasksTotal;
		const calendarTotal = data.space.metrics.countersCalendarTotal;
		const discussionsTotal = data.space.metrics.countersLiveFeedTotal;

		const menu = (spaceId === 0)
			? BX.Main.interfaceButtonsManager.getById(`spaces_user_menu_${userId}`)
			: BX.Main.interfaceButtonsManager.getById(`spaces_group_menu_${spaceId}`)
		;

		if (menu)
		{
			const btn = `spaces_top_menu_${userId}_${spaceId}`;
			const tasksBtn = `${btn}_tasks`;
			const calendarBtn = `${btn}_calendar`;
			const discussionBtn = `${btn}_discussions`;

			menu.updateCounter(tasksBtn, tasksTotal);
			menu.updateCounter(calendarBtn, calendarTotal);
			menu.updateCounter(discussionBtn, discussionsTotal);
		}
	}

	#updateMenuItem(baseEvent: BaseEvent)
	{
		const data = baseEvent.getData();
		if (Type.isUndefined(data.FEATURE))
		{
			return;
		}

		const feature = data.FEATURE;
		const spaceId = parseInt(data.GROUP_ID, 10);

		if (!spaceId)
		{
			return;
		}

		const featureName = feature.featureName;
		const featureId = `spaces_group_menu_${spaceId}_${featureName}`;
		const menu = BX.Main.interfaceButtonsManager.getById(`spaces_group_menu_${spaceId}`);
		const menuItem = menu.getItemById(featureId);

		if (data.ACTION === 'add')
		{
			const itemMenuData = this.#prepareData(baseEvent);
			menu.addMenuItem(itemMenuData);
		}

		if (data.ACTION === 'delete')
		{
			const activeItem = menu.getActive();

			if (activeItem.DATA_ID === featureName)
			{
				const uri = new Uri(this.#pathToDiscussions);
				top.BX.Socialnetwork.Spaces.space.reloadPageContent(uri.toString());
			}

			menu.deleteMenuItem(menuItem);
		}

		if (data.ACTION === 'change')
		{
			const featureText = feature.customName ?? feature.name;
			menu.updateMenuItemText(menuItem, featureText);
		}
	}

	#prepareData(baseEvent: BaseEvent): Object
	{
		const data = baseEvent.getData();
		if (Type.isUndefined(data.FEATURE))
		{
			return;
		}

		const feature = data.FEATURE;
		const spaceId = parseInt(data.GROUP_ID, 10);
		const featureName = feature.featureName;
		const featureId = `spaces_group_menu_${spaceId}_${featureName}`;
		let name = feature.name;

		if (feature.customName)
		{
			name = feature.customName.length > 0 ? feature.customName : feature.name;
		}

		return {
			counterId: featureId,
			dataId: featureName,
			id: featureId,
			onClick: `top.BX.Socialnetwork.Spaces.space.reloadPageContent("/spaces/group/${spaceId}/${featureName}/");`,
			text: name,
			url: '',
		};
	}

	#setAvatar(avatar: string)
	{
		this.#logo = {
			id: avatar,
			type: 'image',
		};
		this.#renderSpaceAvatar();
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);

		if (params.groupMembersList)
		{
			this.#groupInvitedList = params.groupMembersList
				.filter((user) => user.invited)
				.map((user) => parseInt(user.id, 10))
			;
		}

		this.#logo = params.logo;
		this.#pathToDiscussions = params.pathToDiscussions;
	}

	#initServices(params: Params)
	{
		this.#chat = new Chat({
			entityType: this.#getParam('type'),
			entityId: this.#getParam('entityId'),
			groupMembersList: this.#getParam('groupMembersList'),
		});
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#renderSpaceAvatar(): HTMLElement
	{
		const logo = new Logo(this.#logo);

		const avatarNode = Tag.render`
			<div class="sn-spaces__space-logo ${logo.getClass() ?? ''}">
				${logo.render()}
			</div>
		`;

		this.#layout.avatar.replaceWith(avatarNode);
		this.#layout.avatar = avatarNode;

		return this.#layout.avatar;
	}

	#renderVideoCall(): HTMLElement
	{
		const { node, chevronDown } = Tag.render`
			<div
				ref="node"
				data-id="spaces-video-call-menu"
				class="sn-spaces__menu-toolbar_btn"
			>
				<div class="ui-icon-set --video-1"></div>
				<div
					ref="chevronDown"
					class="ui-icon-set --chevron-down"
					style="--ui-icon-set__icon-size: 14px;"
				></div>
			</div>
		`;

		Event.bind(node, 'click', this.#videoCallClick.bind(this, chevronDown));

		return node;
	}

	#renderScrumVideoCall(): HTMLElement
	{
		const node = Tag.render`
			<div data-id="spaces-scrum-video-call-menu" class="sn-spaces__menu-toolbar_btn">
				<div class="ui-icon-set --video-1"></div>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 14px;"></div>
			</div>
		`;

		Event.bind(node, 'click', this.#scrumVideoCallClick.bind(this));

		return node;
	}

	#renderScrumElements(): HTMLElement
	{
		const node = Tag.render`
			<div data-id="spaces-scrum-elements-menu" class="sn-spaces__menu-toolbar_btn">
				<div class="ui-icon-set --elements"></div>
			</div>
		`;

		Event.bind(node, 'click', this.#scrumElementsClick.bind(this));

		return node;
	}

	#renderUserVideoCall(): HTMLElement
	{
		const node = Tag.render`
			<div data-id="spaces-video-call-menu" class="sn-spaces__menu-toolbar_btn">
				<div class="ui-icon-set --video-1"></div>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 14px;"></div>
			</div>
		`;

		Event.bind(node, 'click', this.#userVideoCallClick.bind(this));

		return node;
	}

	#renderInvite(): HTMLElement
	{
		if (!this.#layout.inviteNode)
		{
			this.#layout.inviteNode = Tag.render`
				<div data-id="spaces-invite-menu" class="sn-spaces__menu-toolbar_btn">
					<div class="ui-icon-set --person-plus"></div>
				</div>
			`;

			Event.bind(this.#layout.inviteNode, 'click', this.#inviteClick.bind(this));

			if (this.#getParam('isNew'))
			{
				setTimeout(() => this.#showSpotlight(this.#layout.inviteNode), 500);
			}
		}

		return this.#layout.inviteNode;
	}

	#renderSettings(): HTMLElement
	{
		const node = Tag.render`
			<div data-id="spaces-settings-menu" class="sn-spaces__menu-toolbar_btn">
				<div class="ui-icon-set --more"></div>
			</div>
		`;

		Event.bind(node, 'click', this.#settingsClick.bind(this));

		return node;
	}

	#renderUserSettings(): HTMLElement
	{
		const node = Tag.render`
			<div data-id="spaces-settings-menu" class="sn-spaces__menu-toolbar_btn">
				<div class="ui-icon-set --more"></div>
			</div>
		`;

		Event.bind(node, 'click', this.#userSettingsClick.bind(this));

		return node;
	}

	#videoCallClick(bindElement: HTMLElement)
	{
		if (!this.#videoCall)
		{
			this.#videoCall = new VideoCall({
				bindElement: bindElement,
			});
			this.#videoCall.subscribe('hd', () => {
				this.#chat.startVideoCall();
			});
			this.#videoCall.subscribe('chat', () => {
				this.#chat.openChat();
			});
			this.#videoCall.subscribe('createChat', () => {
				this.#chat.createChat(bindElement);
			});
		}

		this.#videoCall.show();
	}

	#scrumVideoCallClick(event)
	{
		if (!this.#scrumMeetings)
		{
			this.#scrumMeetings = new Meetings({
				groupId: this.#getParam('entityId'),
			});
		}

		this.#scrumMeetings.showMenu(event.target);
	}

	#scrumElementsClick(event)
	{
		if (!this.#scrumMethodology)
		{
			this.#scrumMethodology = new Methodology({
				groupId: this.#getParam('entityId'),
				teamSpeedPath: this.#getParam('pathToScrumTeamSpeed'),
				burnDownPath: this.#getParam('pathToScrumBurnDown'),
				pathToTask: this.#getParam('pathToGroupTasksTask'),
			});
		}

		this.#scrumMethodology.showMenu(event.target);
	}

	#userVideoCallClick(event)
	{

	}

	#inviteClick()
	{
		const invite = this.#getInvite();

		if (invite.isShown())
		{
			invite.close();
		}
		else
		{
			invite.show();
		}
	}

	#getInvite(): Invite
	{
		if (!this.#invite)
		{
			this.#invite = new Invite({
				node: this.#layout.inviteNode,
				groupMembersList: this.#getParam('groupMembersList'),
			});

			this.#invite.subscribe('onClose', this.#onInviteClose.bind(this));
			this.#invite.subscribe('usersSelected', this.#onUsersSelected.bind(this));
		}

		return this.#invite;
	}

	#onUsersSelected(event)
	{
		const users = event.data;
		this.#inviteUsers(users);
	}

	#onInviteClose()
	{
		if (this.#getParam('isNew') && !this.#discussionAhaMomentShown)
		{
			const startDiscussionButton = document.querySelector('[data-id=spaces-discussions-add-main-btn]');
			if (!startDiscussionButton)
			{
				return;
			}

			this.#showSpotlight(startDiscussionButton, {
				title: Loc.getMessage('SN_SPACES_DISCUSSION_AHA_MOMENT_TITLE'),
				text: Loc.getMessage('SN_SPACES_DISCUSSION_AHA_MOMENT_TEXT'),
			});

			this.#discussionAhaMomentShown = true;
		}
	}

	#showSpotlight(node, ahaMoment = null)
	{
		// eslint-disable-next-line promise/catch-or-return
		Runtime.loadExtension(['spotlight', 'ui.tour']).then(() => {
			const spotlight = new BX.SpotLight({
				targetElement: node,
				targetVertex: 'middle-center',
			});

			Dom.addClass(node, '--active');
			spotlight.bindEvents({
				onTargetEnter: () => {
					Dom.removeClass(node, '--active');
					spotlight.close();
				},
			});

			spotlight.setColor('#2fc6f6');
			spotlight.show();

			if (ahaMoment)
			{
				this.#showAhaMoment(node, {
					title: ahaMoment.title,
					text: ahaMoment.text,
					spotlight,
				});
			}
		});
	}

	async #showAhaMoment(node, params): void
	{
		const { Guide } = await Runtime.loadExtension('ui.tour');

		const guide = new Guide({
			simpleMode: true,
			onEvents: true,
			steps: [
				{
					target: node,
					title: params.title,
					text: params.text,
					position: 'bottom',
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
				},
			],
		});

		guide.showNextStep();

		const guidePopup = guide.getPopup();
		guidePopup.setWidth(380);
		guidePopup.getContentContainer().style.paddingRight = getComputedStyle(guidePopup.closeIcon)['width'];
		guidePopup.setAngle({ offset: node.offsetWidth / 2 - 5 });
		guidePopup.subscribe('onClose', () => params.spotlight.close());
		guidePopup.subscribe('onDestroy', () => params.spotlight.close());
		guidePopup.setAutoHide(true);
	}

	#inviteUsers(users)
	{
		const invited = users.filter((userId) => !this.#groupInvitedList.includes(userId));
		const removed = this.#groupInvitedList.filter((userId) => !users.includes(userId));
		this.#groupInvitedList = users;

		// eslint-disable-next-line promise/catch-or-return
		Controller.inviteUsers(this.#getParam('entityId'), users).then(
			() => {
				BX.UI.Notification.Center.notify({
					content: this.#getInvitationMessage(invited, removed),
				});
			},
			(error) => console.log(error),
		);
	}

	#getInvitationMessage(invited, removed)
	{
		const hasInvited = invited.length > 0;
		const hasRemoved = removed.length > 0;

		if (hasInvited && !hasRemoved)
		{
			return Loc.getMessage('SN_SPACES_INVITATIONS_SENT');
		}

		if (!hasInvited && hasRemoved)
		{
			return Loc.getMessage('SN_SPACES_INVITATIONS_REMOVED');
		}

		if (hasInvited && hasRemoved)
		{
			return Loc.getMessage('SN_SPACES_INVITATIONS_CHANGED');
		}

		return Loc.getMessage('SN_SPACES_INVITATIONS_SENT');
	}

	#settingsClick(event)
	{
		if (!this.#settings)
		{
			this.#settings = new Settings({
				bindElement: event.target,
				availableFeatures: this.#getParam('availableFeatures'),
				isMember: this.#getParam('isMember'),
				type: this.#getParam('type'),
				entityId: this.#getParam('entityId'),
				logo: this.#logo,
				chat: this.#chat,
			});
		}

		this.#settings.show();
	}

	#userSettingsClick(event)
	{

	}
}
