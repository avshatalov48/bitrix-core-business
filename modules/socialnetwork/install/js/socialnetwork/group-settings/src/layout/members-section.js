import { Event, Loc, Tag, Text, Type } from 'main.core';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';
import 'ui.icon-set.main';

type Params = {
	listOfMembers: Array,
	onShowMembers: void,
}

export class MembersSection
{
	#params: Params;

	#layout: {
		wrap: HTMLElement,
	};

	constructor(params: Params)
	{
		this.#params = params;

		this.#layout = {};
	}

	setMembers(listOfMembers: Array): void
	{
		this.#params.listOfMembers = listOfMembers;

		this.render();
	}

	render(): HTMLElement
	{
		const owner = this.#params.listOfMembers.find((member) => member.isOwner);
		const moderators = this.#params.listOfMembers.filter((member) => member.isModerator);
		const members = this.#params.listOfMembers.filter((member) => !member.isOwner && !member.isModerator);

		const wrap = Tag.render`
			<div class="sn-group-settings__members-section">
				${this.#renderRoleLine(Loc.getMessage('SN_GROUP_SETTINGS_OWNER'), this.#renderOwner(owner))}
				${this.#renderModeratorsLine(moderators)}
				${this.#renderRoleLine(Loc.getMessage('SN_GROUP_SETTINGS_MEMBERS'), this.#renderMembers(members))}
			</div>
		`;

		this.#layout.wrap?.replaceWith(wrap);
		this.#layout.wrap = wrap;

		return this.#layout.wrap;
	}

	#renderOwner(owner): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__owner">
				${this.#renderAvatar(owner.photo)}
				<div class="sn-group-settings__owner-info">
					<a
						class="sn-group-settings__member-name"
						href="${this.#getUserProfileUrl(owner.id)}"
					>
						${Text.encode(`${owner.name} ${owner.lastName}`)}
					</a>
					<div class="sn-group-settings__owner-position">
						${Text.encode(owner.position)}
					</div>
				</div>
			</div>
		`;
	}

	#renderModeratorsLine(moderators: Array): HTMLElement
	{
		return this.#renderRoleLine(
			Loc.getMessage('SN_GROUP_SETTINGS_MODERATORS'),
			this.#renderModerators(moderators),
			'--moderators',
		);
	}

	#renderModerators(moderators: Array): HTMLElement
	{
		if (moderators.length === 0)
		{
			return this.#renderModeratorsEmptyState();
		}

		const maxModerators = 4;
		const moreModerators = moderators.length - maxModerators;

		return Tag.render`
			<div class="sn-group-settings__moderator-list">
				${moderators.slice(0, maxModerators).map((moderator) => this.#renderModerator(moderator))}
				${this.#renderMoreModerators(moreModerators)}
			</div>
		`;
	}

	#renderMoreModerators(count: number): HTMLElement|string
	{
		if (count <= 0)
		{
			return '';
		}

		const moreModeratorsNode = Tag.render`
			<div class="sn-group-settings__more-moderators">
				${Loc.getMessage('SN_GROUP_SETTINGS_MORE_MODERATORS', { '#COUNT#': count })}
			</div>
		`;

		Event.bind(moreModeratorsNode, 'click', this.#params.onShowMembers);

		return moreModeratorsNode;
	}

	#renderModeratorsEmptyState(): HTMLElement
	{
		return this.#renderMembersLineEmptyState(
			'--person',
			Loc.getMessage('SN_GROUP_SETTINGS_EMPTY_STATE_MODERATORS'),
		);
	}

	#renderModerator(moderator: any): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__moderator">
				<div class="sn-group-settings__moderator-avatar-container">
					${this.#renderAvatar(moderator.photo)}
				</div>
				<a
					class="sn-group-settings__member-name"
					href="${this.#getUserProfileUrl(moderator.id)}"
				>
					${Text.encode(`${moderator.name} ${moderator.lastName}`)}
				</a>
			</div>
		`;
	}

	#getUserProfileUrl(userId: number): string
	{
		return `/company/personal/user/${userId}/`;
	}

	#renderMembers(members: Array): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__members">
				${members.length > 0 ? this.#renderAvatarsLine(members) : this.#renderMembersEmptyState()}
				${this.#renderMembersButton()}
			</div>
		`;
	}

	#renderAvatarsLine(members: Array): HTMLElement
	{
		const maxMembersCount = 5;

		return Tag.render`
			<div class="sn-group-settings__members-line">
				${members.slice(0, maxMembersCount).map((member) => this.#renderMember(member))}
				${this.#renderPlusMembers(members.length - maxMembersCount)}
			</div>
		`;
	}

	#renderMembersEmptyState(): HTMLElement
	{
		return this.#renderMembersLineEmptyState(
			'--persons-3',
			Loc.getMessage('SN_GROUP_SETTINGS_EMPTY_STATE_MEMBERS'),
		);
	}

	#renderMembersLineEmptyState(iconClass: string, text: string): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__members-empty-state">
				<div class="sn-group-settings__members-empty-state-icon">
					<div class="ui-icon-set ${iconClass}"></div>
				</div>
				<div class="sn-group-settings__members-empty-state-text">${text}</div>
			</div>
		`;
	}

	#renderMember(member: any): HTMLElement
	{
		return this.#renderAvatar(member.photo);
	}

	#renderPlusMembers(count: number): HTMLElement|string
	{
		if (count <= 0)
		{
			return '';
		}

		return Tag.render`
			<div class="sn-group-settings__plus-members">+ ${count}</div>
		`;
	}

	#renderAvatar(avatar: string, className = ''): HTMLElement
	{
		return Tag.render`
			<span class="ui-icon ui-icon-common-user ${className}">
				<i style="${this.#isAvatar(avatar) ? `background-image: url('${avatar}')` : ''}"></i>
			</span>
		`;
	}

	#renderMembersButton(): HTMLElement
	{
		return new Button({
			className: 'sn-group-settings__members-button',
			text: Loc.getMessage('SN_GROUP_SETTINGS_MEMBERS'),
			color: ButtonColor.LIGHT_BORDER,
			size: ButtonSize.EXTRA_SMALL,
			round: true,
			onclick: this.#params.onShowMembers,
		}).render();
	}

	#renderRoleLine(role: string, usersNode: HTMLElement, className: string): HTMLElement
	{
		return Tag.render`
			<div class="sn-group-settings__role-line ${className}">
				<div class="sn-group-settings__role">${role}</div>
				${usersNode}
			</div>
		`;
	}

	#isAvatar(avatar: string): boolean
	{
		return Type.isStringFilled(avatar) && avatar !== '/bitrix/images/1.gif';
	}
}