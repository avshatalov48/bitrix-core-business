import { Loc, Tag, Text, Type } from 'main.core';
import 'ui.icon-set.actions';
import { MenuManager } from 'main.popup';
import bindShowOnHover from './bind-show-on-hover';

export type Member = {
	name: string,
	lastName: string,
	avatar: string,
	isOwner: boolean,
}

type MembersListParams = {
	members: Member[],
	avatarSize: number,
	className: string,
	textClassName: string,
	allAttendees: boolean,
	maxAvatarsCount: number,
};

export class MembersList
{
	#layout: {
		wrap: HTMLElement,
		avatarItems: HTMLElement,
	};

	#params: MembersListParams;
	#members: Member[];

	constructor(params: MembersListParams)
	{
		this.#layout = {};

		this.#params = params;
		this.#members = params.members;
	}

	render(): HTMLElement|string
	{
		if (!Type.isArrayFilled(this.#members))
		{
			return '';
		}

		this.#layout.wrap = Tag.render`
			<div class="${this.#params.className}">
				<div class="${this.#params.textClassName}">
					${this.#getMembersTitle()}
				</div>
				<div class="calendar-pub-line-avatar-container" style="--ui-icon-size: ${this.#params.avatarSize}px">
					${this.#renderAvatarItems()}
				</div>
			</div>
		`;

		const menu = MenuManager.create({
			id: 'calendar-pub-welcome-more-avatar-popup' + Date.now(),
			bindElement: this.#layout.avatarItems,
			className: 'calendar-pub-users-popup',
			items: this.#members.map((member) => ({
				html: Tag.render`
					<div class="calendar-pub-users-popup-avatar-container">
						${this.#renderAvatar(member, 'calendar-pub-users-popup-avatar')}
						<div class="calendar-pub-users-popup-avatar-text">
							<span class="calendar-pub-users-popup-avatar-text-name">
								${Text.encode(`${member.name} ${member.lastName}`.trim())}
							</span>
							<span class="calendar-pub-users-popup-avatar-text-you">
								${member.isOwner ? Loc.getMessage('CALENDAR_SHARING_MEETING_YOU_LABEL') : ''}
							</span>
						</div>
					</div>
				`,
			})),
			maxHeight: 300,
			maxWidth: 300,
		});

		bindShowOnHover(menu);

		return this.#layout.wrap;
	}

	#getMembersTitle(): string
	{
		if (this.#params.allAttendees)
		{
			return Loc.getMessage('CALENDAR_SHARING_MEETING_ATTENDEES');
		}

		return Loc.getMessage('CALENDAR_SHARING_MEETING_HAS_MORE_USERS');
	}

	#renderAvatarItems(): HTMLElement
	{
		const maxAvatarsCount = this.#params.maxAvatarsCount ?? 4;
		const showMoreIcon = this.#members.length > maxAvatarsCount;
		const avatarsCount = showMoreIcon ? maxAvatarsCount - 1 : maxAvatarsCount;
		const avatarClassName = 'calendar-pub-line-avatar';

		this.#layout.avatarItems = Tag.render`
			<div class="calendar-pub-line-avatars">
				${this.#members.slice(0, avatarsCount).map((member) => this.#renderAvatar(member, avatarClassName))}
				${showMoreIcon ? this.#renderMoreAvatar() : ''}
			</div>
		`;

		return this.#layout.avatarItems;
	}

	#renderMoreAvatar(): HTMLElement
	{
		return Tag.render`
			<span class="ui-icon ui-icon-common-user calendar-pub-line-avatar calendar-pub-line-avatar-more-container">
				<div class="ui-icon-set --more calendar-pub-line-avatar-more"></div>
			</span>
		`;
	}

	#renderAvatar(member, className = ''): HTMLElement
	{
		return Tag.render`
			<span class="ui-icon ui-icon-common-user ${className}">
				<i style="${this.#hasAvatar(member) ? `background-image: url('${member.avatar}')` : ''}"></i>
			</span>
		`;
	}

	#hasAvatar(member): boolean
	{
		return Type.isStringFilled(member.avatar) && member.avatar !== '/bitrix/images/1.gif';
	}
}