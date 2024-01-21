import { ajax, AjaxError, AjaxResponse, Dom, Loc, Tag, Type, Uri, Text, Event } from 'main.core';
import { PULL as Pull } from 'pull.client';
import { MembersList } from './members-list';
import { PullRequests } from './pull.requests';

import './css/panel.css';

type Params = {
	groupId: number,
	pathToUser: string,
	pathToUsers: string,
}

type Data = {
	listAwaitingMembers: Array<Member>,
	counters: {
		workgroup_requests_out: number,
		workgroup_requests_in: number,
	},
}

export type Member = {
	id: number,
	name: string,
	photo: string,
}

export class MembershipRequestPanel
{
	#sidePanelManager: BX.SidePanel.Manager;

	#groupId: number;
	#pathToUser: string;
	#pathToUsers: string;

	#waiting: boolean;

	#membersList: MembersList;

	#container: HTMLElement;
	#node: HTMLElement;

	constructor(params: Params)
	{
		this.#sidePanelManager = BX.SidePanel.Instance;

		this.#groupId = Type.isInteger(parseInt(params.groupId, 10)) ? parseInt(params.groupId, 10) : 0;

		this.#pathToUser = params.pathToUser;
		this.#pathToUsers = params.pathToUsers;

		this.#waiting = false;

		this.#node = null;

		this.#subscribeToPull();
	}

	renderTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.MembershipRequestPanel: HTMLElement for render not found');
		}

		this.#container = container;

		this.#render(this.#container, this.#getData());
	}

	#subscribeToPull()
	{
		const pullRequests = new PullRequests(this.#groupId);
		pullRequests.subscribe('update', this.#update.bind(this));

		Pull.subscribe(pullRequests);
	}

	#getData(): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.get', {
			data: {
				params: {
					select: [
						'LIST_OF_MEMBERS_AWAITING_INVITE',
						'COUNTERS',
					],
					groupId: this.#groupId,
				},
			},
		})
			.then((response: AjaxResponse) => {
				return {
					listAwaitingMembers: response.data.LIST_OF_MEMBERS_AWAITING_INVITE,
					counters: response.data.COUNTERS,
				};
			})
			.catch((error: AjaxError) => {
				this.#consoleError('getData', error);
			})
		;
	}

	#update()
	{
		this.#membersList = null;

		this.#render(this.#container, this.#getData());
	}

	#render(container: HTMLElement, dataPromise: Promise): void
	{
		// eslint-disable-next-line promise/catch-or-return
		dataPromise
			.then((data: Data) => {
				Dom.clean(container);

				const listAwaitingMembers = data.listAwaitingMembers;
				if (listAwaitingMembers.length > 0)
				{
					if (listAwaitingMembers.length > 1)
					{
						const amountRequests = parseInt(data.counters.workgroup_requests_in, 10);

						Dom.append(
							this.#renderMultipleRequest(amountRequests, listAwaitingMembers),
							container,
						);
					}
					else
					{
						Dom.append(
							this.#renderSingleRequest(listAwaitingMembers[0]),
							container,
						);
					}
				}
			})
		;
	}

	#remove()
	{
		Dom.remove(this.#node);
	}

	#renderSingleRequest(member: Member): HTMLElement
	{
		const acceptId = 'sn-mrp-single-accept-btn';
		const rejectId = 'sn-mrp-single-reject-btn';

		this.#node = Tag.render`
			<div class="sn-spaces__warning">
				<div class="sn-spaces__warning-icon">
					${this.#renderPhoto(member.photo)}
				</div>
				<div class="sn-spaces__warning-info">
					<div class="sn-spaces__warning-info_count">1</div>
					<div class="sn-spaces__warning-info_text">
						${
							Loc.getMessage('SN_MRP_SINGLE_LABEL')
								.replace('#id#', 'sn-mrp-member-profile-link')
								.replace('#class#', 'sn-spaces__warning-info_link')
								.replace('#path#', Text.encode(this.#pathToUser.replace('#user_id#', member.id)))
								.replace('#name#', Text.encode(member.name))
						}
					</div>
				</div>
				<div class="sn-spaces__warning-btns">
					<button
						data-id="${acceptId}"
						class="ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round"
					>
						${Loc.getMessage('SN_MRP_SINGLE_ACCEPT_BTN')}
					</button>
					<button
						data-id="${rejectId}"
						class="ui-btn ui-btn-xs ui-btn-light ui-btn-no-caps ui-btn-round"
					>
						${Loc.getMessage('SN_MRP_SINGLE_REJECT_BTN')}
					</button>
				</div>
			</div>
		`;

		const acceptBtn = this.#node.querySelector(`[data-id='${acceptId}']`);
		Event.bind(
			acceptBtn,
			'click',
			this.#acceptIncomingRequest.bind(this, acceptBtn, [member.id]),
		);

		const rejectBtn = this.#node.querySelector(`[data-id='${rejectId}']`);
		Event.bind(
			rejectBtn,
			'click',
			this.#rejectIncomingRequest.bind(this, rejectBtn, [member.id]),
		);

		return this.#node;
	}

	#renderMultipleRequest(amountRequests: number, listAwaitingMembers: Array<Member>): HTMLElement
	{
		const visibleAmount = 5;
		const invisibleAmount = amountRequests - visibleAmount;

		const members = listAwaitingMembers.slice(0, visibleAmount);

		const photosId = 'sn-mrp-multiple-photos';
		const acceptId = 'sn-mrp-multiple-accept-btn';
		const rejectId = 'sn-mrp-multiple-reject-btn';

		this.#node = Tag.render`
			<div class="sn-spaces__warning">
				<div
					data-id="${photosId}"
					class="sn-spaces__warning-icon"
					style="cursor: pointer;"
				>
					${members.map((member: Member) => {
						return this.#renderPhoto(member.photo);
					})}
					<div
						style="${invisibleAmount > 0 ? '' : 'display: none;'}"
						class="sn-spaces__warning-icon_element --count"
					>
						<span class="sn-spaces__warning-icon_element-plus">+</span>
						<span class="sn-spaces__warning-icon_element-number">${invisibleAmount}</span>
					</div>
				</div>
				<div class="sn-spaces__warning-info">
					<div class="sn-spaces__warning-info_count">
						${parseInt(amountRequests, 10)}
					</div>
					<div class="sn-spaces__warning-info_text">
						${Loc.getMessage('SN_MRP_MULTIPLE_LABEL')}
					</div>
				</div>
				<div class="sn-spaces__warning-btns">
					<button
						data-id="${acceptId}"
						class="ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round"
					>
						${Loc.getMessage('SN_MRP_MULTIPLE_ACCEPT_BTN')}
					</button>
					<button
						data-id="${rejectId}"
						class="ui-btn ui-btn-xs ui-btn-light ui-btn-no-caps ui-btn-round"
					>
						${Loc.getMessage('SN_MRP_MULTIPLE_REJECT_BTN')}
					</button>
				</div>
			</div>
		`;

		const photosList = this.#node.querySelector(`[data-id='${photosId}']`);
		Event.bind(
			photosList,
			'click',
			() => this.#showMembers(photosList),
		);

		const acceptBtn = this.#node.querySelector(`[data-id='${acceptId}']`);
		Event.bind(
			acceptBtn,
			'click',
			() => {
				this.#acceptIncomingRequest(
					acceptBtn,
					listAwaitingMembers.map((member: Member) => {
						return member.id;
					}),
				);
			},
		);

		const rejectBtn = this.#node.querySelector(`[data-id='${rejectId}']`);
		Event.bind(
			rejectBtn,
			'click',
			this.#openGroupUsers.bind(this, 'in'),
		);

		return this.#node;
	}

	#renderPhoto(photo: string): HTMLElement
	{
		if (photo)
		{
			return Tag.render`
				<div
					class="sn-spaces__warning-icon_element"
					style="background-image: url('${encodeURI(photo)}');"
				></div>
			`;
		}

		return Tag.render`<div class="sn-spaces__warning-icon_element ui-icon ui-icon-common-user ui-icon-xs"><i></i></div>`;
	}

	#showMembers(bindElement: HTMLElement)
	{
		if (!this.#membersList)
		{
			this.#membersList = new MembersList({
				groupId: this.#groupId,
				bindElement,
				pathToUser: this.#pathToUser,
			});
		}

		this.#membersList.show();
	}

	#acceptIncomingRequest(btn: HTMLElement, userIds: Array): void
	{
		if (this.#isWaiting())
		{
			return;
		}

		this.#activateWaiting(btn);

		ajax.runAction('socialnetwork.api.workgroup.acceptIncomingRequest', {
			data: {
				groupId: this.#groupId,
				userIds: userIds,
			},
		})
			.then((response: AjaxResponse) => {
				this.#remove();

				this.#deactivateWaiting(btn);
			})
			.catch((error: AjaxError) => {
				this.#consoleError('acceptIncomingRequest', error);

				this.#deactivateWaiting(btn);
			})
		;
	}

	#rejectIncomingRequest(btn: HTMLElement, userIds: Array): void
	{
		if (this.#isWaiting())
		{
			return;
		}

		this.#activateWaiting(btn);

		ajax.runAction('socialnetwork.api.workgroup.rejectIncomingRequest', {
			data: {
				groupId: this.#groupId,
				userIds: userIds,
			},
		})
			.then((response: AjaxResponse) => {
				this.#remove();

				this.#deactivateWaiting(btn);
			})
			.catch((error: AjaxError) => {
				this.#consoleError('acceptIncomingRequest', error);

				this.#deactivateWaiting(btn);
			})
		;
	}

	#openGroupUsers(mode: 'all' | 'in' | 'out'): void
	{
		const availableModes = {
			all: 'members',
			in: 'requests_in',
			out: 'requests_out',
		};

		const uri = new Uri(this.#pathToUsers);
		uri.setQueryParams({
			mode: availableModes[mode],
		});

		this.#sidePanelManager.open(uri.toString(), {
			width: 1200,
			cacheable: false,
			loader: 'group-users-loader',
		});
	}

	#isWaiting(): boolean
	{
		return this.#waiting;
	}

	#activateWaiting(btn: HTMLElement): void
	{
		this.#waiting = true;

		Dom.addClass(btn, 'ui-btn-wait');
	}

	#deactivateWaiting(btn: HTMLElement): void
	{
		this.#waiting = false;

		Dom.removeClass(btn, 'ui-btn-wait');
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`MembershipRequestPanel: ${action} error`, error);
	}
}
