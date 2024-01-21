import { Event, Loc, Tag, Text, Type } from 'main.core';
import 'ui.icon-set.actions';
import { MenuManager, Menu } from 'main.popup';

export type Member = {
	name: string,
	lastName: string,
	avatar: string,
}

type MembersListParams = {
	members: Member[],
	avatarSize: number,
	className: string,
	textClassName: string,
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

	render(): HTMLElement
	{
		if (!Type.isArrayFilled(this.#members))
		{
			return '';
		}

		this.#layout.wrap = Tag.render`
			<div class="${this.#params.className}">
				<div class="${this.#params.textClassName}">
					${Loc.getMessage('CALENDAR_SHARING_MEETING_HAS_MORE_USERS')}
				</div>
				<div class="calendar-pub-line-avatar-container" style="--ui-icon-size: ${this.#params.avatarSize}px">
					${this.#renderAvatarItems()}
				</div>
			</div>
		`;

		let handleScroll;
		const menu = MenuManager.create({
			id: 'calendar-pub-welcome-more-avatar-popup' + Date.now(),
			bindElement: this.#layout.avatarItems,
			className: 'calendar-pub-users-popup',
			items: this.#members.map((member) => ({
				html: Tag.render`
					<div class="calendar-pub-users-popup-avatar-container">
						${this.#renderAvatar(member, 'calendar-pub-users-popup-avatar')}
						<div class="calendar-pub-users-popup-avatar-text">
							${Text.encode(`${member.name} ${member.lastName}`)}
						</div>
					</div>
				`,
			})),
			autoHide: false,
			maxHeight: 500,
			maxWidth: 300,
			offsetLeft: - 2 * this.#layout.avatarItems.offsetWidth,
			events: {
				onShow: () => {
					const menuWidth = menu.getPopupWindow().getPopupContainer().offsetWidth;
					menu.getPopupWindow().setOffset({
						offsetLeft: this.#layout.avatarItems.offsetWidth / 2 - menuWidth / 2,
					});
					menu.getPopupWindow().adjustPosition();

					document.addEventListener('scroll', handleScroll, true);
				},
				onClose: () => {
					document.removeEventListener('scroll', handleScroll, true);
				},
			}
		});
		handleScroll = () => menu.getPopupWindow().adjustPosition();

		this.#bindShowOnHover(menu);

		return this.#layout.wrap;
	}

	#renderAvatarItems()
	{
		const maxAvatarsCount = 4;
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

	#bindShowOnHover(menu: Menu): void
	{
		const bindElement = menu.bindElement;
		const menuContainer = menu.getMenuContainer();

		let hoverElement = null;

		const closeMenuHandler = () => {
			setTimeout(() => {
				if (!menuContainer.contains(hoverElement) && !bindElement.contains(hoverElement))
				{
					menu.close();
				}
			}, 100);
		};
		const showMenuHandler = () => {
			setTimeout(() => {
				if (bindElement.contains(hoverElement))
				{
					menu.show();
				}
			}, 300);
		};

		Event.bind(document, 'mouseover', (event) => {
			hoverElement = event.target;
		});
		Event.bind(bindElement, 'mouseenter', showMenuHandler);
		Event.bind(bindElement, 'mouseleave', closeMenuHandler);
		Event.bind(menuContainer, 'mouseleave', closeMenuHandler);
	}

	#renderAvatar(member, className = ''): HTMLElement
	{
		return Tag.render`
			<span class="ui-icon ui-icon-common-user ${className}">
				<i style="${this.#hasAvatar(member) ? `background-image: url('${member.avatar}')` : ''}"></i>
			</span>
		`;
	}

	#hasAvatar(member)
	{
		return Type.isStringFilled(member.avatar) && member.avatar !== '/bitrix/images/1.gif';
	}
}