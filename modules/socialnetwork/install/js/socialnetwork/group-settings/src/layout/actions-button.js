import { Loc, Tag, Text } from 'main.core';
import { Menu, Popup } from 'main.popup';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';
import type { Perms } from '../type';

type Params = {
	isPin: boolean,
	isSubscribed: boolean,
	actions: Perms,
	pinChanged: void,
	followChanged: void,
	leave: void,
	delete: void,
}

export class ActionsButton
{
	#params: Params;
	#layout: {
		button: HTMLElement,
	};

	#empty: boolean;

	#actionsMenu: Menu;

	constructor(params: Params)
	{
		this.#layout = {};
		this.#params = params;

		this.#empty = this.#getActionsMenu().getMenuItems().length === 0;
	}

	render(): HTMLElement
	{
		if (this.#empty)
		{
			return Tag.render`<div></div>`;
		}

		this.#layout.button = new Button({
			text: Loc.getMessage('SN_GROUP_SETTINGS_ACTIONS'),
			color: ButtonColor.SUCCESS,
			size: ButtonSize.MEDIUM,
			dropdown: true,
			round: true,
			onclick: this.#showActionsMenu.bind(this),
		}).render();

		return this.#layout.button;
	}

	#showActionsMenu(): void
	{
		this.#getActionsMenu().show();
	}

	#getActionsMenu(): Menu
	{
		this.#actionsMenu = this.#createActionsMenu();

		return this.#actionsMenu;
	}

	#createActionsMenu(): Menu
	{
		const menu = new Menu({
			id: `sn-group-settings__actions-menu-${Text.getRandom()}`,
			bindElement: this.#layout.button,
			closeByEsc: true,
		});

		if (this.#params.actions.canPin)
		{
			menu.addMenuItem({
				text: this.#getPinText(),
				dataset: { id: 'sn-group-settings__actions-pin' },
				onclick: (event, item) => {
					this.#params.isPin = !this.#params.isPin;
					item.setText(this.#getPinText());
					this.#params.pinChanged(this.#params.isPin);
					menu.close();
				},
			});
		}

		if (this.#params.actions.canFollow)
		{
			menu.addMenuItem({
				text: this.#getFollowText(),
				dataset: { id: 'sn-group-settings__actions-follow' },
				onclick: (event, item) => {
					this.#params.isSubscribed = !this.#params.isSubscribed;
					item.setText(this.#getFollowText());
					this.#params.followChanged(this.#params.isSubscribed);
					menu.close();
				},
			});
		}

		if (this.#params.actions.canLeave)
		{
			menu.addMenuItem({
				text: Loc.getMessage('SN_GROUP_SETTINGS_LEAVE_SPACE'),
				dataset: { id: 'sn-group-settings__actions-leave' },
				onclick: () => {
					this.#showDangerPopup(
						() => this.#params.leave(),
						Loc.getMessage('SN_GROUP_SETTINGS_CONFIRM_LEAVE'),
						Loc.getMessage('SN_GROUP_SETTINGS_LEAVE'),
					);
					menu.close();
				},
			});
		}

		if (this.#params.actions.canEdit)
		{
			menu.addMenuItem({
				text: Loc.getMessage('SN_GROUP_SETTINGS_DELETE_SPACE'),
				dataset: { id: 'sn-group-settings__actions-delete' },
				onclick: () => {
					this.#showDangerPopup(
						() => this.#params.delete(),
						Loc.getMessage('SN_GROUP_SETTINGS_CONFIRM_DELETE'),
						Loc.getMessage('SN_GROUP_SETTINGS_DELETE'),
					);
					menu.close();
				},
			});
		}

		return menu;
	}

	#showDangerPopup(action, message, okCaption)
	{
		const popup = new Popup({
			bindElement: null,
			content: Tag.render`
				<div class="socialnetwork-danger-popup">
					${message}
				</div>
			`,
			buttons: [
				new Button({
					id: 'socialnetwork-danger-popup-btn-action',
					size: ButtonSize.SMALL,
					color: ButtonColor.DANGER,
					text: okCaption,
					events: {
						click: () => {
							action();
							popup.close();
						},
					},
				}),
				new Button({
					id: 'socialnetwork-danger-popup-btn-cancel',
					size: ButtonSize.SMALL,
					color: ButtonColor.LIGHT_BORDER,
					text: Loc.getMessage('SN_GROUP_SETTINGS_CANCEL'),
					events: {
						click: () => popup.close(),
					},
				}),
			],
			minHeight: 120,
			minWidth: 350,
			maxWidth: 350,
			animation: 'fading-slide',
		});

		popup.show();
	}

	#getPinText(): string
	{
		return Loc.getMessage(`SN_GROUP_SETTINGS_MENU_PIN_${this.#params.isPin ? 'N' : 'Y'}`);
	}

	#getFollowText(): string
	{
		return Loc.getMessage(`SN_GROUP_SETTINGS_MENU_FOLLOW_${this.#params.isSubscribed ? 'N' : 'Y'}`);
	}
}
