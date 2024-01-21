import { Loc, Dom, Type, Tag, Event, Cache, Runtime } from 'main.core';
import { PULL as Pull } from 'pull.client';
import { Meetings } from 'tasks.scrum.meetings';
import { Methodology } from 'tasks.scrum.methodology';
import { Guide } from 'ui.tour';
import { PullRequests } from './pull-requests';
import { MenuAjax } from './menu-ajax';
import { Chat } from './chat';
import { Invite } from './invite';
import { Logo } from './logo';
import { MenuRouter } from './menu-router';
import { Settings } from './settings';
import { VideoCall } from './video-call';

import type { LogoData } from './logo';

import './css/menu.css';

type Params = {
	type: 'group' | 'user',
	entityId: number,
	groupMembersList: any,
	logo?: LogoData,
	pathToFeatures?: string,
	pathToUsers?: string,
	pathToInvite?: string,
	pathToScrumTeamSpeed?: string,
	pathToScrumBurnDown?: string,
	pathToGroupTasksTask?: string,
	canInvite: boolean,
	isNew?: boolean,
};

export class Menu
{
	#cache = new Cache.MemoryCache();

	#videoCall: ?VideoCall;
	#scrumMeetings: ?Meetings;
	#scrumMethodology: ?Methodology;
	#invite: ?Invite;
	#inviteNode: HTMLElement;
	#settings: ?Settings;

	#chat: Chat;
	#router: MenuRouter;
	#discussionAhaMomentShown = false;

	#groupInvitedList: any = [];

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#initServices(params);

		this.#subscribeToPull();
	}

	#subscribeToPull()
	{
		const pullRequests = new PullRequests(this.#getParam('entityId'));
		pullRequests.subscribe('update', this.#update.bind(this));

		Pull.subscribe(pullRequests);
	}

	#update()
	{
		const groupDataPromise = MenuAjax.getGroupData(this.#getParam('entityId'));
		this.#settings?.update(groupDataPromise);
		this.#getInvite().update(groupDataPromise);
		this.#chat.update(groupDataPromise);
	}

	renderLogoTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.Menu: HTMLElement for space not found');
		}

		const logo = new Logo(this.#getParam('logo'));

		const logoClass = logo.getClass();
		if (logoClass)
		{
			Dom.addClass(container, logoClass);
		}

		Dom.append(
			logo.render(),
			container,
		);
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
		Dom.append(this.#renderVideoCall(), container);

		if (this.#getParam('canInvite'))
		{
			Dom.append(this.#renderInvite(), container);
		}

		Dom.append(this.#renderSettings(), container);
	}

	renderScrumToolbarTo(container: HTMLElement)
	{
		Dom.append(this.#renderScrumVideoCall(), container);
		Dom.append(this.#renderScrumElements(), container);

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
	}

	#initServices(params: Params)
	{
		this.#chat = new Chat({
			entityType: this.#getParam('type'),
			entityId: this.#getParam('entityId'),
			groupMembersList: this.#getParam('groupMembersList'),
		});

		this.#router = new MenuRouter({
			pathToFeatures: params.pathToFeatures,
			pathToUsers: params.pathToUsers,
			pathToInvite: params.pathToInvite,
		});
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
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
		if (!this.#inviteNode)
		{
			this.#inviteNode = Tag.render`
				<div data-id="spaces-invite-menu" class="sn-spaces__menu-toolbar_btn">
					<div class="ui-icon-set --person-plus"></div>
				</div>
			`;

			Event.bind(this.#inviteNode, 'click', this.#inviteClick.bind(this));

			if (this.#getParam('isNew'))
			{
				setTimeout(() => this.#showSpotlight(this.#inviteNode), 500);
			}
		}

		return this.#inviteNode;
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
				node: this.#inviteNode,
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
		Runtime.loadExtension('spotlight').then(() => {
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

	async #showAhaMoment(node, params)
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
		guidePopup.setAutoHide(true);
	}

	#inviteUsers(users)
	{
		const invited = users.filter((userId) => !this.#groupInvitedList.includes(userId));
		const removed = this.#groupInvitedList.filter((userId) => !users.includes(userId));
		this.#groupInvitedList = users;

		// eslint-disable-next-line promise/catch-or-return
		MenuAjax.inviteUsers(this.#getParam('entityId'), users).then(
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
				type: this.#getParam('type'),
				entityId: this.#getParam('entityId'),
				logo: this.#getParam('logo'),
				chat: this.#chat,
				router: this.#router,
			});
		}

		this.#settings.show();
	}

	#userSettingsClick(event)
	{

	}
}
