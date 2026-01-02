import { Type, Dom, Tag, Text, Event } from 'main.core';
import { BaseField } from './base-field';
import { UserListPopup } from './component/user-list-popup';
import { AvatarRound } from 'ui.avatar';
import type { User } from './component/types';

export type UsersWithAvatarsFieldType = {
	users: Array<User>,
}

export class UsersWithAvatarsField extends BaseField
{
	#users: Array;
	#popup: UserListPopup;

	render(params: UsersWithAvatarsFieldType): void
	{
		this.#users = Type.isArray(params.users) ? params.users : [];

		if (this.#users.length === 0)
		{
			this.#renderEmpty();

			return;
		}

		this.#renderUsers();
	}

	#renderEmpty(): void
	{
		const emptyContainer = Tag.render`
			<div class="mailbox-grid_list-members --empty"></div>
		`;

		this.appendToFieldNode(emptyContainer);
	}

	#renderUsers(): void
	{
		if (this.#users.length === 1)
		{
			const userNode = this.#renderSingleUserLayout();
			this.appendToFieldNode(userNode);
		}
		else
		{
			const usersNode = this.#renderMultipleUsersLayout();
			Event.bind(usersNode, 'click', () => this.#showUsersPopup(usersNode));
			this.appendToFieldNode(usersNode);
		}
	}

	#renderSingleUserLayout(): HTMLElement
	{
		const user = this.#users[0];
		const container = Tag.render`
			<a href="${user.pathToProfile}" class="mailbox-grid_list-members --single-user --link"></a>
		`;

		const avatar = this.#renderUserAvatar(user);
		Dom.append(avatar, container);

		const userName = Text.encode(user.name) || '';
		const nameNode = Tag.render`<span class="mailbox-grid_list-members-name">${userName}</span>`;
		Dom.append(nameNode, container);

		return container;
	}

	#renderMultipleUsersLayout(): HTMLElement
	{
		const maxVisibleAvatars = 3;
		const visibleUsers = this.#users.slice(0, maxVisibleAvatars);
		const remainingCount = this.#users.length - visibleUsers.length;

		const avatarsContainer = Tag.render`<div class="mailbox-grid_list-members"></div>`;

		visibleUsers.forEach((user) => {
			const avatar = this.#renderUserAvatar(user);
			Dom.append(avatar, avatarsContainer);
		});

		if (remainingCount > 0)
		{
			Dom.append(this.#renderCounter(remainingCount), avatarsContainer);
		}

		return avatarsContainer;
	}

	#showUsersPopup(targetElement: HTMLElement): void
	{
		if (!this.#popup)
		{
			this.#popup = new UserListPopup({
				users: this.#users,
				targetNode: targetElement,
			});
		}

		this.#popup.show();
	}

	#renderUserAvatar(user: User): HTMLElement
	{
		const avatarSrc = encodeURI(user.avatar?.src) || '';
		const userName = Text.encode(user.name) || '';
		const userpicSize = 28;

		let avatar = null;
		if (Type.isStringFilled(user.avatar?.src))
		{
			avatar = new AvatarRound({
				size: userpicSize,
				userName,
				userpicPath: avatarSrc,
			});
		}
		else
		{
			avatar = new AvatarRound({
				size: userpicSize,
			});
		}

		const avatarNode = avatar.getContainer();
		Dom.addClass(avatarNode, 'mailbox-grid_list-members-icon_element');

		return avatarNode;
	}

	#renderCounter(count: number): HTMLElement
	{
		return Tag.render`
			<div class="mailbox-grid_list-members-icon_element --count">
				<span class="mailbox-grid_warning-icon_element-plus">+</span>
				<span class="mailbox-grid_warning-icon_element-number">${count}</span>
			</div>
		`;
	}
}
