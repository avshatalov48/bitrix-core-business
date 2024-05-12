import { Text, Dom, Tag, Loc, Event } from 'main.core';
import { Util } from 'calendar.util';
import { Icon, Actions, Main } from 'ui.icon-set.api.core';
import { MenuManager, Menu } from 'main.popup';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';
import { EventEmitter } from 'main.core.events';
import 'ui.icon-set.actions'


const MAX_AVATAR_COUNT = 4;

type ListItemProps = {
	id: number,
	hash: string,
	shortUrl: string,
	members: any,
	frequentUse: number,
	userId: number,
	userIds: any,
	userInfo: any,
	type: string,
	pathToUser: string,
	setListItemPopupState: func,
	dateCreate: string,
};

export default class ListItem
{
	#props: ListItemProps;
	#layout: {
		wrapper: HTMLElement,
		avatarContainer: HTMLElement,
		date: HTMLElement,
		copyButton: HTMLElement,
		deleteButton: HTMLElement,
	};
	#avatarPopup: Menu;
	#deletePopup: MessageBox;

	constructor(props: ListItemProps)
	{
		this.#props = props;
		this.#layout = {};
		this.#avatarPopup = null;
		this.#deletePopup = null;

		this.openAvatarList = this.openAvatarList.bind(this);
		this.onCopyButtonClick = this.onCopyButtonClick.bind(this);
		this.onDeleteButtonClick = this.onDeleteButtonClick.bind(this);
	}

	render(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__dialog-link-list-item">
					${this.renderAvatarContainer()}
					${this.renderDate()}
					${this.renderCopyButton()}
					${this.renderDeleteButton()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	renderAvatarContainer(): HTMLElement
	{
		if (!this.#layout.avatarContainer)
		{
			const showMoreIcon = this.#props.members.length > MAX_AVATAR_COUNT;
			const moreCounter = this.#props.members.length - MAX_AVATAR_COUNT;

			this.#layout.avatarContainer = Tag.render`
				<div class="calendar-sharing__dialog-link-list-item-avatar-container">
					${this.renderAvatar(this.#props.userInfo)}
					${this.#props.members.slice(0, MAX_AVATAR_COUNT).map((member) => this.renderAvatar(member))}
					${showMoreIcon ? this.renderMore(moreCounter) : null}
				</div>
			`;

			Event.bind(this.#layout.avatarContainer, 'click', this.openAvatarList);
		}

		return this.#layout.avatarContainer;
	}

	renderAvatar(user): HTMLElement
	{
		const name = `${user.name} ${user.lastName ?? ''}`.trim();

		if (this.hasAvatar(user.avatar))
		{
			return Tag.render`
				<img class="calendar-sharing__dialog-link-list-item-avatar" title="${Text.encode(name)}" alt="" src="${user.avatar}">
			`;
		}

		return Tag.render`
			<div class="ui-icon ui-icon-common-user calendar-sharing__dialog-link-list-item-avatar" title="${Text.encode(name)}"><i></i></div>
		`;
	}

	hasAvatar(avatar): boolean
	{
		return avatar && avatar !== '/bitrix/images/1.gif';
	}

	renderMore(counter): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__dialog-link-list-item-more">
				<div class="calendar-sharing__dialog-link-list-item-more-text">${'+' + counter}</div>
			</div>
		`;
	}

	openAvatarList(): void
	{
		if (!this.#avatarPopup)
		{
			const uid = BX.util.getRandomString(6);

			this.#avatarPopup = MenuManager.create({
				id: 'calendar-sharing-dialog_' + uid,
				bindElement: this.#layout.avatarContainer,
				bindOptions: {
					position: 'top',
				},
				autoHide: true,
				closeByEsc: true,
				className: 'calendar-sharing__dialog-link-list-user-popup-container',
				items: this.getAvatarPopupItems(),
				maxHeight: 250,
				maxWidth: 300,
			});
			this.#avatarPopup.getPopupWindow().subscribe('onClose', () => {
				this.setPopupState(false);
			});

			const menuContainer = this.#avatarPopup.getMenuContainer();

			let timeout;

			Event.bind(menuContainer, 'mouseleave', () => {
				clearTimeout(timeout);
				timeout = setTimeout(() => {
					this.closeAvatarList();
				}, 500);
			});
			Event.bind(menuContainer, 'mouseenter', () => {
				clearTimeout(timeout);
			});
		}

		this.#avatarPopup.show();
		this.setPopupState(true);
	}

	closeAvatarList(): void
	{
		if (this.#avatarPopup)
		{
			this.#avatarPopup.close();
		}
	}

	getAvatarPopupItems(): any
	{
		const result = [];

		result.push(this.getAvatarPopupItem(this.#props.userInfo));
		this.#props.members.forEach((member) => {
			result.push(this.getAvatarPopupItem(member));
		});

		return result;
	}

	getAvatarPopupItem(user): any
	{
		const avatar = user.avatar;
		const name = `${user.name} ${user.lastName ?? ''}`.trim();
		const userPath = this.#props.pathToUser.replace('#USER_ID#', user.id);

		return {
			html: Tag.render`
				<a href="${userPath}" target="_blank" class="calendar-sharing__dialog-link-list-user-popup-item">
					<span class="ui-icon ui-icon-common-user calendar-sharing__dialog-link-list-user-popup-item-avatar">
						<i style="${this.hasAvatar(avatar) ? `background-image: url('${avatar}')` : ''}"></i>
					</span>
					<div class="calendar-sharing__dialog-link-list-user-popup-item-text">
						${Text.encode(name)}
					</div>
				</a>
			`,
		};
	}

	renderDate(): HTMLElement
	{
		if (!this.#layout.date)
		{
			const date = this.#props.dateCreate
				? new Date(this.#props.dateCreate)
				: new Date()
			;
			const formattedDate = Util.formatDate(date);

			this.#layout.date = Tag.render`
				<div class="calendar-sharing__dialog-link-list-item-date" title="${Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DATE_CREATE')}">${formattedDate}</div>
			`;
		}

		return this.#layout.date;
	}

	renderCopyButton(): HTMLElement
	{
		if (!this.#layout.copyButton)
		{
			const icon = new Icon({
				icon: Main.LINK_3,
				size: 14,
			});

			this.#layout.copyButton = Tag.render`
				<div class="calendar-sharing__dialog-link-list-item-copy-container">
					${icon.render()}
					<div class="calendar-sharing__dialog-link-list-item-copy-text">${Loc.getMessage('CALENDAR_SHARING_LINK_LIST_COPY')}</div>
				</div>
			`;

			Event.bind(this.#layout.copyButton, 'click', this.onCopyButtonClick);
		}

		return this.#layout.copyButton;
	}

	onCopyButtonClick(): void
	{
		EventEmitter.emit('CalendarSharing:onJointLinkCopy', {
			id: this.#props.id,
			shortUrl: this.#props.shortUrl,
			hash: this.#props.hash,
			members: this.#props.members,
		});
	}

	renderDeleteButton(): HTMLElement
	{
		if (!this.#props.members.length)
		{
			return Tag.render`<div class="calendar-sharing__dialog-link-list-item-delete"></div>`;
		}

		if (!this.#layout.deleteButton)
		{
			const icon = new Icon({
				icon: Actions.CROSS_30,
				size: 18,
			});

			this.#layout.deleteButton = Tag.render`
				<div class="calendar-sharing__dialog-link-list-item-delete">
					${icon.render()}
				</div>
			`;

			Event.bind(this.#layout.deleteButton, 'click', this.onDeleteButtonClick);
		}

		return this.#layout.deleteButton;
	}

	onDeleteButtonClick(): void
	{
		if (!this.#deletePopup)
		{
			this.#deletePopup = new MessageBox({
				title: Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DELETE_MESSAGE_TITLE_MSGVER_1'),
				message: Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DELETE_MESSAGE_DESC_MSGVER_1'),
				buttons: this.getDeletePopupButtons(),
				popupOptions: {
					autoHide: true,
					closeByEsc: true,
					draggable: false,
					closeIcon: true,
					minWidth: 365,
					maxWidth: 385,
					minHeight: 180,
				},
			});
		}

		this.#deletePopup.show();
		this.setPopupState(true);
	}

	getDeletePopupButtons(): any
	{
		return [
			new Button({
				size: ButtonSize.MEDIUM,
				color: ButtonColor.DANGER,
				text: Loc.getMessage('SHARING_WARNING_POPUP_DELETE'),
				events: {
					click: () => {
						this.deleteLink();
						this.#deletePopup.close();
						this.setPopupState(false);
					},
				},
			}),
			new Button({
				size: ButtonSize.MEDIUM,
				color: ButtonColor.LIGHT_BORDER,
				text: Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
				events: {
					click: () => {
						this.#deletePopup.close();
						this.setPopupState(false);
					},
				},
			}),
		];
	}

	deleteLink(): void
	{
		if (this.#layout.wrapper)
		{
			BX.ajax.runAction('calendar.api.sharingajax.disableUserLink', {
				data: {
					hash: this.#props.hash,
				},
			});
			Dom.addClass(this.#layout.wrapper, '--animate-delete');
			setTimeout(() => {
				Dom.remove(this.#layout.wrapper);
			}, 300);

			EventEmitter.emit('CalendarSharing:onJointLinkDelete', {
				id: this.#props.id,
			});
		}
	}

	setPopupState(state): void
	{
		this.#props?.setListItemPopupState(state);
	}
}