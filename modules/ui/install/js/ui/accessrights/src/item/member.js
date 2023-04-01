import {Dom, Event, Text, Tag, Type, Loc, Reflection} from 'main.core';
import {BaseEvent, EventEmitter} from "main.core.events";
import {PopupWindowManager} from "main.popup";
import Base from "./base";
import ColumnItemOptions from "../columnitem";

const BX = Reflection.namespace('BX');

export default class Member extends Base
{
	static TYPE = 'members';

	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.openPopupEvent = options.openPopupEvent;
		this.popupContainer = options.popupContainer
		this.accessCodes = options.accessCodes || [];
	}

	bindEvents(): void
	{
		EventEmitter.subscribe('BX.UI.AccessRights:addToAccessCodes', this.addToAccessCodes.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:removeFromAccessCodes', this.removeFromAccessCodes.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:reset', this.resetNewMembers.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetNewMembers.bind(this));
	}

	getMember(): HTMLElement
	{
		if (!this.member)
		{
			const members = this.userGroup.members || {};
			const membersFragment = document.createDocumentFragment();
			let counter = 0;
			this.validateVariables();

			Object.keys(members).reverse().forEach(
				(item) => {
					counter++;
					if (counter < 7)
					{
						const user = members[item];

						const userNode = Tag.render`
							<div class='ui-access-rights-members-item'></div>
						`;

						if (user.new)
						{
							Dom.addClass(userNode, 'ui-access-rights-members-item-new');
						}

						if (user.avatar)
						{
							const userAvatar = Tag.render`<a class='ui-access-rights-members-item-avatar' title="${Text.encode(user.name)}"></a>`;
							Dom.style(userAvatar, 'backgroundImage', 'url(\'' + encodeURI(user.avatar) + '\')');
							Dom.style(userAvatar, 'backgroundSize', 'cover');
							Dom.append(userAvatar, userNode);
						}
						else
						{
							let avatarClass = 'ui-icon-common-user';

							if (user.type === 'groups')
							{
								avatarClass = 'ui-icon-common-user-group';
							}
							else if (user.type === 'sonetgroups')
							{
								avatarClass = 'ui-icon-common-company';
							}
							else if (user.type === 'usergroups')
							{
								avatarClass = 'ui-icon-common-user-group';
							}

							const emptyAvatar = Tag.render`<a class='ui-icon ui-icon-xs' title="${Text.encode(user.name)}"><i></i></a>`;
							Dom.addClass(emptyAvatar, avatarClass);
							Dom.append(emptyAvatar, userNode);
						}

						Dom.append(userNode, membersFragment);
					}
				}
			);

			Dom.append(this.getAddUserToRole(), membersFragment);

			this.member = Tag.render`<div class='ui-access-rights-members'>${membersFragment}</div>`;
			Event.bind(this.member, 'click', this.adjustPopupUserControl.bind(this));
		}

		return this.member;
	}

	render(): HTMLElement
	{
		return this.getMember();
	}

	resetNewMembers(): void
	{
		const newMembers = this.getMember().querySelectorAll('.ui-access-rights-members-item-new');

		newMembers.forEach((item) => {
			Dom.removeClass(item, 'ui-access-rights-members-item-new');
		})
	}

	validateVariables(): void
	{
		if (Type.isUndefined(this.userGroup.accessCodes))
		{
			this.userGroup.accessCodes = [];
		}
	}

	updateMembers(): void
	{
		Dom.remove(this.member);
		this.member = null;

		Dom.append(this.getMember(), this.parentContainer);
		this.grid.getButtonPanel().show();
	}

	addToAccessCodes(event: BaseEvent): void
	{
		const params = event.getData();

		if (params.columnId !== this.getId())
		{
			return;
		}

		const firstKey = Object.keys(params.accessCodes)[0];
		const type = params.accessCodes[firstKey].toUpperCase();
		this.userGroup.accessCodes = Object.keys(this.accessCodes);

		const item = params.item;

		if (!Type.isUndefined(item) && Object.keys(item).length)
		{
			this.userGroup.members[firstKey] = {
				id: item.entityId,
				name: item.name,
				avatar: item.avatar,
				url: '',
				new: true,
				type: type.toLowerCase()
			};

			this.updateMembers();
		}

		this.userGroup.accessCodes = [];

		for (const key in this.userGroup.members)
		{
			this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
		}
	}

	removeFromAccessCodes(event): void
	{
		const params = event.data;

		if (params.columnId !== this.identificator)
		{
			return;
		}

		const firstKey = Object.keys(params.accessCodes)[0];

		delete this.userGroup.members[firstKey];
		this.updateMembers();

		this.userGroup.accessCodes = [];

		for (const key in this.userGroup.members)
		{
			this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
		}
	}

	adjustPopupUserControl(): void
	{
		const users = [];
		const groups = [];
		const departments = [];
		const sonetgroups = [];

		for (const item in this.userGroup.members)
		{
			this.userGroup.members[item].key = item;

			if (this.userGroup.members[item].type === 'users')
			{
				users.push(this.userGroup.members[item]);
			}
			else if (this.userGroup.members[item].type === 'groups')
			{
				groups.push(this.userGroup.members[item]);
			}
			else if (this.userGroup.members[item].type === 'usergroups')
			{
				groups.push(this.userGroup.members[item]);
			}
			else if (this.userGroup.members[item].type === 'departments')
			{
				departments.push(this.userGroup.members[item]);
			}
			else if (this.userGroup.members[item].type === 'sonetgroups')
			{
				sonetgroups.push(this.userGroup.members[item]);
			}
		}

		const counterUsers = [];

		for (const key in this.userGroup.members)
		{
			counterUsers.push(this.userGroup.members[key])
		}

		if (counterUsers.length === 0)
		{
			this.showUserSelectorPopup();
			return;
		}

		this.getUserPopup(users, groups, departments, sonetgroups).show();
	}

	getAddUserToRole(): HTMLElement
	{
		if (!this.addUserToRole)
		{
			this.addUserToRole = Tag.render`
				<span 
					class='ui-access-rights-members-item ui-access-rights-members-item-add'
					bx-data-column-id='${this.getId()}'
				>
				</span>
			`;
		}

		return this.addUserToRole;
	}

	getUserPopup(users, groups, departments, sonetgroups): Popup
	{
		if (!this.popupUsers)
		{
			users = users || [];
			groups = groups || [];
			departments = departments || [];
			sonetgroups = sonetgroups || [];

			const content = Tag.render`<div class='ui-access-rights-popup-toggler'></div>`;

			const contentTitle = Tag.render`<div class='ui-access-rights-popup-toggler-title'></div>`;

			const onTitleClick = (event: BaseEvent) => {
				const node = event.target;
				activate(node);
				adjustSlicker(node);
			};

			if (groups.length > 0)
			{
				const groupTitleItem = Tag.render`
					<div 
						class='ui-access-rights-popup-toggler-title-item ui-access-rights-popup-toggler-title-item-active'
						data-role='ui-access-rights-popup-toggler-content-groups'
					>
						${Loc.getMessage('JS_UI_ACCESSRIGHTS_USER_GROUPS')}
					</div>
				`;
				Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));

				Dom.append(groupTitleItem, contentTitle);
			}

			if (departments.length > 0)
			{
				const groupTitleItem = Tag.render`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-departments'
					>
						${Loc.getMessage('JS_UI_ACCESSRIGHTS_DEPARTMENTS')}
					</div>
				`;
				Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));

				Dom.append(groupTitleItem, contentTitle);
			}

			if (users.length > 0)
			{
				const groupTitleItem = Tag.render`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-users'
					>
						${Loc.getMessage('JS_UI_ACCESSRIGHTS_STAFF')}
					</div>
				`;
				Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));

				Dom.append(groupTitleItem, contentTitle);
			}

			if (sonetgroups.length > 0)
			{
				const groupTitleItem = Tag.render`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-sonetgroups'
					>
						${Loc.getMessage('JS_UI_ACCESSRIGHTS_SOCNETGROUP')}
					</div>
				`;
				Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));

				Dom.append(groupTitleItem, contentTitle);
			}

			Dom.append(Tag.render`<div class='ui-access-rights-popup-toggler-title-slicker'></div>`, contentTitle);

			Dom.append(contentTitle, content);

			if (groups.length > 0)
			{
				Dom.append(this.getUserPopupTogglerGroup(groups, 'groups'), content);
			}

			if (departments.length > 0)
			{
				Dom.append(this.getUserPopupTogglerGroup(departments, 'departments'), content);
			}

			if (users.length > 0)
			{
				Dom.append(this.getUserPopupTogglerGroup(users, 'users'), content);
			}

			if (sonetgroups.length > 0)
			{
				Dom.append(this.getUserPopupTogglerGroup(sonetgroups, 'sonetgroups'), content);
			}

			const footer = Tag.render`<div class='ui-access-rights-popup-toggler-footer'></div>`;

			const footerLink = Tag.render`
				<div class='ui-access-rights-popup-toggler-footer-link'>
					${Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD')}
				</div>
			`;
			Event.bind(footerLink, 'click', (event: Event) => {
				this.popupUsers.close();
				this.showUserSelectorPopup();
				event.preventDefault()
			});

			Dom.append(footerLink, footer);
			Dom.append(footer, content);

			const adjustSlicker = (node) => {
				if (!Type.isDomNode(node))
				{
					node = content.querySelector('.ui-access-rights-popup-toggler-title-item-active');
				}
				const slicker = content.querySelector('.ui-access-rights-popup-toggler-title-slicker');
				Dom.style(slicker, 'left', node.offsetLeft + 'px');
				Dom.style(slicker, 'width', node.offsetWidth + 'px');
			};

			const activate = (node) => {
				const titles = content.querySelectorAll('.ui-access-rights-popup-toggler-title-item');
				const contents = content.querySelectorAll('.ui-access-rights-popup-toggler-content');

				const target = content.querySelector('.' + node.getAttribute('data-role'));

				titles.forEach((item) => {
					Dom.removeClass(item, 'ui-access-rights-popup-toggler-title-item-active');
				});

				contents.forEach((item) => {
					Dom.style(item, 'display', 'none');
				});

				Dom.style(target, 'display', 'block');
				Dom.addClass(node, 'ui-access-rights-popup-toggler-title-item-active');
			};

			this.popupUsers = PopupWindowManager.create(
				null,
				this.getAddUserToRole(),
				{
					contentPadding: 10,
					animation: 'fading-slide',
					content,
					padding: 0,
					offsetTop: 5,
					angle: {
						position: 'top',
						offset: 35,
					},
					autoHide: true,
					closeEsc: true,
					events: {
						onPopupShow: () => {
							setTimeout(() => {
								const firstActiveNode = content.querySelector('.ui-access-rights-popup-toggler-title-item');

								if (!firstActiveNode)
								{
									return;
								}

								Dom.addClass(firstActiveNode, 'ui-access-rights-popup-toggler-title-item-active');
								adjustSlicker(firstActiveNode);
							});
						},
						onPopupClose: () => {
							this.popupUsers.destroy();
							this.popupUsers = null;
						}
					}
				}
			);
		}

		return this.popupUsers;
	}

	getUserPopupTogglerGroup(array, type)
	{
		const node = Tag.render`<div class='ui-access-rights-popup-toggler-content'></div>`;
		Dom.addClass(node, 'ui-access-rights-popup-toggler-content-' + type);

		array.forEach((item) => {
			const toggler = Tag.render`<div class='ui-access-rights-popup-toggler-content-item'></div>`;

			if (item.avatar)
			{
				const avatar = Tag.render`
					<a 
						class='ui-access-rights-popup-toggler-content-item-userpic'
						title="${Text.encode(item.name)}"
					></a>
				`;
				Dom.style(avatar, 'backgroundImage', 'url(\'' + encodeURI(item.avatar) + '\')');
				Dom.style(avatar, 'backgroundSize', 'cover');
				Dom.append(avatar, toggler);
			}
			else
			{
				let iconClass = '';

				if (type === 'users')
				{
					iconClass = 'ui-icon-common-user';
				}
				else if (type === 'groups')
				{
					iconClass = 'ui-icon-common-user-group';
				}
				else if (type === 'sonetgroups' || type === 'departments')
				{
					iconClass = 'ui-icon-common-company';
				}

				const emptyAvatar = Tag.render`<a class='ui-icon ui-icon-sm' title="${Text.encode(item.name)}"><i></i></a>`;
				Dom.addClass(emptyAvatar, iconClass);
				Dom.style(emptyAvatar, 'margin', '5px 10px');
				Dom.append(emptyAvatar, toggler);
			}

			Dom.append(
				Tag.render`<div class='ui-access-rights-popup-toggler-content-item-name'>${Text.encode(item.name)}</div>`,
				toggler
			);

			const removeButton = Tag.render`
				<div class='ui-access-rights-popup-toggler-content-item-remove'>${Loc.getMessage('JS_UI_ACCESSRIGHTS_REMOVE')}</div>
			`;

			Event.bind(removeButton, 'click', () => {
				this.userGroup.accessCodes.splice(this.userGroup.accessCodes.indexOf(item.key), 1);

				delete this.userGroup.accessCodes[item.key];
				delete this.userGroup.members[item.key];

				Dom.remove(toggler);

				this.updateMembers();
				this.adjustPopupUserControl();
				this.grid.getButtonPanel().show();
			});

			Dom.append(removeButton, toggler);

			Dom.append(toggler, node);
		});

		return node;
	}

	showUserSelectorPopup(): void
	{
		const selectorInstance = BX.Main
			.selectorManagerV2.controls[this.popupContainer]
			?.selectorInstance
		;

		if (selectorInstance)
		{
			selectorInstance.itemsSelected = {};
		}

		BX.onCustomEvent(this.openPopupEvent, [{
			id: this.popupContainer,
			bindNode: this.getAddUserToRole()
		}]);

		BX.onCustomEvent('BX.Main.SelectorV2:reInitDialog', [{
			selectorId: this.popupContainer,
			selectedItems: this.userGroup.accessCodes
		}]);
	}
}
