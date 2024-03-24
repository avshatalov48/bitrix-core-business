import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Perms, Member } from 'socialnetwork.controller';

type Params = {
	amount: number,
	list: Array<Member>,
	counters: { [key: string]: number },
	actions: Perms,
}

type GroupedMembers = {
	owner: Member,
	moderators: Array<Member>,
	users: Array<Member>,
}

export class Members extends EventEmitter
{
	#amount: number;
	#list: Array<Member>;
	#counters: { [key: string]: number };
	#actions: Perms;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Members');

		this.#amount = params.amount;
		this.#list = params.list;
		this.#counters = params.counters;
		this.#actions = params.actions;
	}

	render(): HTMLElement
	{
		const inviteBtnId = 'spaces-settings-create-members-btn';

		const node = Tag.render`
			<div class="sn-spaces__popup-item_expandable">
				<div class="sn-spaces__popup-item --expandable">
					<div class="sn-spaces__popup-item_icon-block">
						<div class="sn-spaces__popup-item_icon">
							<div
								class="ui-icon-set --persons-3"
								style="--ui-icon-set__icon-size: 27px;"
							></div>
						</div>
					</div>
					<div class="sn-spaces__popup-item_main-info">
						<div class="sn-spaces__popup-item_members">
							<div class="sn-spaces__popup-item_members-info">
								${Loc.getMessage('SN_SPACES_MENU_MEMBERS_LABEL')}: ${parseInt(this.#amount, 10)}
							</div>
							${this.#canInvite() ? this.#renderInviteBtn(inviteBtnId) : ''}
						</div>
						${this.#renderList()}
					</div>
				</div>
				${this.#canInvite() && this.#hasCounters() ? this.#renderCounters() : ''}
			</div>
		`;

		const inviteNode = node.querySelector(`[data-id='${inviteBtnId}']`);

		Event.bind(
			node.querySelector('.--expandable'),
			'click',
			(event) => {
				if (!inviteNode || !event.target.isEqualNode(inviteNode))
				{
					this.emit('showUsers', 'all');
				}
			},
		);

		return node;
	}

	#renderInviteBtn(inviteBtnId: string): HTMLElement
	{
		const node = Tag.render`
			<div 
				data-id="${inviteBtnId}" 
				class="ui-popupcomponentmaker__btn --border sn----spaces__popup-settings_btn"
			>
				${Loc.getMessage('SN_SPACES_MENU_MEMBERS_INVITE_BTN')}
			</div>
		`;

		Event.bind(node, 'click', () => this.emit('invite'));

		return node;
	}

	#renderList(): HTMLElement
	{
		const groupedMembers = this.#prepareList();

		const visibleAmount = 3;
		const moderators = groupedMembers.moderators.slice(0, visibleAmount);
		const users = groupedMembers.users.slice(0, visibleAmount);

		const amount = this.#amount - (1 + moderators.length + users.length);

		return Tag.render`
			<div class="sn-spaces__popup-item_lists">
				<div class="sn-spaces__popup-item_list">
					${this.#renderOwner(groupedMembers.owner)}
				</div>
				<div
					class="sn-spaces__popup-item_list"
					style="${moderators.length > 0 ? '' : 'display: none;'}"
				>
					${moderators.map((member: Member) => {
						return this.#renderModerator(member);
					})}
				</div>
				<div
					class="sn-spaces__popup-item_list"
					style="${users.length > 0 ? '' : 'display: none;'}"
				>
					${users.map((member: Member) => {
						return this.#renderUser(member);
					})}
				</div>
				<div
					class="sn-spaces__popup-item_list-quantity"
					style="${amount > 0 ? '' : 'display: none;'}"
				>+${amount}</div>
			</div>
		`;
	}

	#renderCounters(): HTMLElement
	{
		const outCounter = parseInt(this.#counters.workgroup_requests_out, 10);
		const inCounter = parseInt(this.#counters.workgroup_requests_in, 10);

		const outId = 'spaces-settings-members-counter-out';
		const inId = 'spaces-settings-members-counter-in';

		const renderOut = () => {
			if (!this.#hasOutCounters())
			{
				return '';
			}

			return Tag.render`
				<div data-id="${outId}" class="sn-spaces__popup-item --primary">
					<div class="sn-spaces__popup-item_counter">${outCounter}</div>
					<span class="">${Loc.getMessage('SN_SPACES_MENU_MEMBERS_GREEN_LABEL')}</span>
					<div
						class="ui-icon-set --chevron-right"
						style="--ui-icon-set__icon-size: 14px;"
					></div>
				</div>
			`;
		};

		const renderIn = () => {
			if (!this.#hasInCounters())
			{
				return '';
			}

			return Tag.render`
				<div data-id="${inId}" class="sn-spaces__popup-item --warning">
					<div class="sn-spaces__popup-item_counter">${inCounter}</div>
					<span class="">${Loc.getMessage('SN_SPACES_MENU_MEMBERS_RED_LABEL')}</span>
					<div
						class="ui-icon-set --chevron-right"
						style="--ui-icon-set__icon-size: 14px;"
					></div>
				</div>
			`;
		};

		const node = Tag.render`
			<div>
				${renderOut()}
				${renderIn()}
			</div>
		`;

		Event.bind(
			node.querySelector(`[data-id='${outId}']`),
			'click',
			() => this.emit('showUsers', 'out'),
		);

		Event.bind(
			node.querySelector(`[data-id='${inId}']`),
			'click',
			() => this.emit('showUsers', 'in'),
		);

		return node;
	}

	#renderOwner(member: Member): HTMLElement
	{
		const crownIconSrc = '/bitrix/components/bitrix/socialnetwork.spaces.menu/'
			+ 'templates/.default/images/sn-spaces__popup-icon_super-admin.svg'
		;

		const uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';

		return Tag.render`
			<div class="sn-spaces__popup-item_list-item --super-admin ${uiClasses}">
				${this.#renderAvatar(member.photo)}
				<img src="${crownIconSrc}" class="sn-spaces__popup-icon_svg" alt="crown">
			</div>
		`;
	}

	#renderModerator(member: Member): HTMLElement
	{
		const crownIconSrc = '/bitrix/components/bitrix/socialnetwork.spaces.menu/'
			+ 'templates/.default/images/sn-spaces__popup-icon_admin.svg'
		;

		const uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';

		return Tag.render`
			<div class="sn-spaces__popup-item_list-item --admin ${uiClasses}">
				${this.#renderAvatar(member.photo)}
				<img src="${crownIconSrc}" class="sn-spaces__popup-icon_svg" alt="crown">
			</div>
		`;
	}

	#renderUser(member: Member): HTMLElement
	{
		const uiClasses = member.photo ? '' : 'ui-icon ui-icon-common-user ui-icon-xs';

		return Tag.render`
			<div class="sn-spaces__popup-item_list-item ${uiClasses}">
				${this.#renderAvatar(member.photo)}
			</div>
		`;
	}

	#renderAvatar(photo: string): HTMLElement
	{
		if (photo)
		{
			return Tag.render`
				<i
					class="sn-spaces__popup-item_list-item-img"
					style="background-image: url('${encodeURI(photo)}');"
				></i>
			`;
		}

		return Tag.render`<i></i>`;
	}

	#prepareList(): GroupedMembers
	{
		const members = {
			owner: null,
			moderators: [],
			users: [],
		};

		this.#list.forEach((member: Member) => {
			if (member.isOwner)
			{
				members.owner = member;
			}
			else if (member.isModerator)
			{
				members.moderators.push(member);
			}
			else
			{
				members.users.push(member);
			}
		});

		return members;
	}

	#canInvite(): boolean
	{
		return this.#actions.canEdit || this.#actions.canInvite;
	}

	#hasCounters(): boolean
	{
		const outCounter = parseInt(this.#counters.workgroup_requests_out, 10);
		const inCounter = parseInt(this.#counters.workgroup_requests_in, 10);

		return outCounter > 0 || inCounter > 0;
	}

	#hasOutCounters(): boolean
	{
		return parseInt(this.#counters.workgroup_requests_out, 10) > 0;
	}

	#hasInCounters(): boolean
	{
		return parseInt(this.#counters.workgroup_requests_in, 10) > 0;
	}
}
