import {EventEmitter} from "main.core.events";
import {Event, Text, Tag, Loc, Dom} from 'main.core';
import Base from "./base";

import { Menu} from "main.popup";
import Role from "./role";

export default class Controller extends Base
{
	render(): HTMLElement
	{
		if (!this.controller)
		{
			this.controllerLink = Tag.render`
				<div class='ui-access-rights-column-item-controller-link'>
					${Loc.getMessage('JS_UI_ACCESSRIGHTS_CREATE_ROLE')}
				</div>
			`;

			this.controllerMenu = Tag.render`
				<div class='ui-access-rights-column-item-controller-link'>
					${Loc.getMessage('JS_UI_ACCESSRIGHTS_COPY_ROLE')}
				</div>
			`;

			Event.bind(this.controllerMenu, 'click', () => {
				if (this.popupMenu)
				{
					this.popupMenu.close();
				}
				else if (this.grid.getUserGroups().length > 0)
				{
					this.getPopupMenu(this.grid.getUserGroups()).show();
				}
			});

			this.toggleControllerMenu();

			this.controller = Tag.render`
				<div class='ui-access-rights-column-item-controller'>
					${this.controllerLink}
					${this.controllerMenu}
				</div>
			`;

			Event.bind(this.controllerLink, 'click', () => {
				EventEmitter.emit('BX.UI.AccessRights.ColumnItem:addRole', [
					{
						id: '0',
						title: Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
						accessRights: [],
						members: [],
						accessCodes: [],
						type: Role.TYPE
					}
				]);

				EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);

				this.toggleControllerMenu();
				this.grid.lock();
			});

			EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.toggleControllerMenu.bind(this));
		}

		return this.controller;
	}

	getPopupMenu(options): Menu
	{
		if (!options)
		{
			return;
		}

		const menuItems = [];

		options.map(
			(data) => {
				menuItems.push({
					text: Text.encode(data.title),
					onclick: () => {
						const accessRightsCopy = Object.assign([], data.accessRights);
						const accessCodesCopy =  Object.assign([], data.accessCodes);

						EventEmitter.emit(
							'BX.UI.AccessRights.ColumnItem:copyRole',
							[{
								id: '0',
								title: Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
								accessRights: accessRightsCopy,
								accessCodes: accessCodesCopy,
								type: Role.TYPE,
								members: data.members
							}]
						);

						EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
						this.popupMenu.destroy();
					}
				});
			}
		);

		return this.popupMenu = new Menu(
			'ui_accessrights_copy_role_list',
			this.controllerMenu,
			menuItems,
			{
				events: {
					onPopupClose: () => {
						this.popupMenu.destroy();
						this.popupMenu = null;
					}
				}
			}
		);
	}

	toggleControllerMenu()
	{
		if (this.grid.getUserGroups().length === 0)
		{
			Dom.addClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
		}
		else
		{
			Dom.removeClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
		}
	}
}
