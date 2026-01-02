import { Type, Dom, Tag, Text, Loc } from 'main.core';
import { Popup } from 'main.popup';
import './style.css';
import 'ui.avatar';
import type { User } from './types';

export type UserListPopupType = {
	users: Array<User>,
	targetNode: HTMLElement,
}

export class UserListPopup
{
	#users: Array<User>;
	#popup: Popup;
	#targetNode: HTMLElement;

	constructor(params: UserListPopupType)
	{
		this.#users = params.users;
		this.#targetNode = params.targetNode;
	}

	#renderUser(user: User): HTMLElement
	{
		const userpicSize = 20;

		let avatarNode = null;
		if (Type.isStringFilled(user.avatar?.src))
		{
			const avatar = new BX.UI.AvatarRound({
				size: userpicSize,
				userName: user.name,
				userpicPath: encodeURI(user.avatar.src),
			});

			avatarNode = avatar.getContainer();
		}
		else
		{
			const avatar = new BX.UI.AvatarRound({
				size: userpicSize,
			});

			avatarNode = avatar.getContainer();
		}

		let userNodeTag = 'div';
		let userNodeAttributes = {};
		let userNameClass = 'mailbox-grid_user-list-popup-popup-name';

		if (Type.isStringFilled(user.pathToProfile))
		{
			userNodeTag = 'a';
			userNameClass = 'mailbox-grid_user-list-popup-popup-name-link';
			userNodeAttributes = {
				href: user.pathToProfile,
				target: '_blank',
				title: user.name,
			};
		}

		const attributesString = Object.entries(userNodeAttributes)
			.map(([key, value]) => `${key}="${Text.encode(value)}"`)
			.join(' ');

		return Tag.render`
			<${userNodeTag} ${attributesString} class="mailbox-grid_user-list-popup-popup-img">
				<span class="mailbox-grid_user-list-popup-popup-avatar-new">
					${avatarNode}
				</span>
				<span class="${userNameClass}">
					${Text.encode(user.name)}
				</span>
			</${userNodeTag}>
		`;
	}

	#getContent(): HTMLElement
	{
		const userNodes = document.createDocumentFragment();
		this.#users.forEach((user) => {
			Dom.append(this.#renderUser(user), userNodes);
		});

		return Tag.render`
			<div class="mailbox-grid_user-list-popup-wrap-block">
				<span class="mailbox-grid_user-list-popup-popup-name-link contentview-name">
					${Loc.getMessage('MAIL_MAILBOX_LIST_POPUP_USERS_WITH_AVATARS_TITLE')}
				</span>
				<div class="mailbox-grid_user-list-popup-popup-outer">
					<div class="mailbox-grid_user-list-popup-popup">
						${userNodes}
					</div>
				</div>
			</div>
		`;
	}

	show(): void
	{
		if (this.#popup)
		{
			this.#popup.show();

			return;
		}

		this.#popup = new Popup({
			id: `users-with-avatars-popup-${Text.getRandom()}`,
			bindElement: this.#targetNode,
			content: this.#getContent(),
			lightShadow: true,
			autoHide: true,
			closeByEsc: true,
			className: 'popup-window-mailbox-user-list',
			bindOptions: {
				position: 'top',
			},
			animationOptions: {
				show: {
					type: 'opacity-transform',
				},
				close: {
					type: 'opacity',
				},
			},
		});

		this.#popup.show();
	}
}
