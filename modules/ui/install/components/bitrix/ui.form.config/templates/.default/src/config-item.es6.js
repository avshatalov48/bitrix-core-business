import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Button } from 'ui.buttons';
import { Dialog, type Item, type ItemId } from 'ui.entity-selector';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';
import { Config } from './config.es6';

/**
 * @bxjs_lang_path template.php
 */

type Member = {
	id: string,
	accessCode: ?string,
	type: string,
	name: string,
	avatar: ?string,
};

const MAX_SHOWN_MEMBERS = 3;

export class ConfigItem extends EventEmitter
{
	#scopeId;
	#members;
	#node;
	#selectedItems: ItemId[] = [];
	#moduleId;
	#config: Config;
	#entityTypeId: ?number;
	#memberSelector: Dialog;
	#useHumanResourcesModule;

	constructor(options: Object)
	{
		super();
		this.setEventNamespace('BX.Ui.Form');

		this.#scopeId = (options.scopeId || null);
		this.#members = (options.members || null);
		this.#node = BX(`ui-editor-config-${this.#scopeId}`);
		this.#moduleId = (options.moduleId || null);
		this.#config = Config;
		this.#entityTypeId = (options.entityTypeId || null);
		this.#useHumanResourcesModule = BX.prop.getBoolean(options, 'useHumanResourcesModule', false);

		this.#drawMembers();
		this.#memberSelector = this.#getMemberSelector();

		this.#subscribeEvents();
	}

	#subscribeEvents()
	{
		if (!this.#config.isSubscribed(this.#scopeId))
		{
			EventEmitter.subscribe('BX.Ui.Form.ConfigItem:copyContextAction', this.#copyContextAction);
			EventEmitter.subscribe('BX.Ui.Form.ConfigItem:deleteContextAction', this.#deleteContextAction);
			this.#config.addSubscribed(this.#scopeId);
		}
	}

	#getMemberSelector(): Dialog
	{
		return new Dialog({
			targetNode: this.#node,
			enableSearch: true,
			context: 'EDITOR_CONFIG_USER_CONTEXT',
			preselectedItems: this.#getSelectedItems(),
			items: [],
			entities: [
				{
					id: 'user',
				},
				{
					id: 'project',
				},
				{
					id: this.#useHumanResourcesModule ? 'structure-node' : 'department',
					options: {
						selectMode: 'usersAndDepartments',
					},
				},
			],
			events: {
				'Item:onSelect': this.#onMemberAdd,
				'Item:onDeselect': this.#onMemberRemove,
				onHide: this.#onClosePopup,
			},
		});
	}

	#getSelectedItems(): Array
	{
		if (this.#members && !Type.isArrayFilled(Object.keys(this.#selectedItems)))
		{
			const items = [];
			const members = this.#members;
			Object.entries(members).forEach(([key: string, value: string]): void => {
				items.push(this.#getItemIdByAccessCode(key));
			});

			this.#selectedItems = items;
		}

		return (this.#selectedItems || []);
	}

	#drawMembers(withClean: boolean): void
	{
		if (!this.#members)
		{
			return;
		}

		if (withClean)
		{
			Dom.clean(this.#node);
		}

		let shownMembers = this.#members;
		let notShownMembersCount = 0;
		if (Object.values(this.#members).length > MAX_SHOWN_MEMBERS)
		{
			shownMembers = Object.values(this.#members).slice(0, MAX_SHOWN_MEMBERS);

			notShownMembersCount = Object.values(this.#members).length - MAX_SHOWN_MEMBERS;
		}

		Object.values(shownMembers).forEach((item) => {
			Dom.append(this.#createMemberElement(item), this.#node);
		});

		if (notShownMembersCount > 0)
		{
			Dom.append(this.#createNumberButton(notShownMembersCount), this.#node);
		}

		Dom.append(this.#createPlusButton(), this.#node);
	}

	#createMemberElement(member: Object): HTMLElement
	{
		const children = (member.avatar
			? Tag.render`<a href="${member.url}" class="ui-editor-config-item-avatar"  title="${Text.encode(member.name)}" style="background-image: url('${encodeURI(Text.encode(member.avatar))}')"></a>`
			: Tag.render`<a href="${member.url}" class="ui-icon ui-icon-xs ui-icon-common-user" title="${Text.encode(member.name)}"><i></i></a>`
		);

		return Dom.create('div', {
			attrs: {
				class: 'ui-editor-config-item',
			},
			children: [
				children,
			],
		});
	}

	#createPlusButton(): HTMLElement
	{
		return Dom.create('div', {
			events: {
				click: (event) => {
					if (!this.#config.popupIsOpen())
					{
						this.#showPopup();
					}
				},
			},
			attrs: {
				class: 'ui-editor-config-item ui-editor-config-item--add',
			},
		});
	}

	#createNumberButton(number: number): HTMLElement
	{
		return Tag.render`<div class="ui-editor-config-item"><span href="#" class="ui-editor-config-item--number">+${number}</span></div>`;
	}

	#showPopup(): void
	{
		this.#config.setPopupOpen();
		this.#memberSelector.show();
	}

	#onMemberAdd = (event: BaseEvent): void => {
		const member = this.#getMemberFromEvent(event);

		this.#selectedItems.push([member.type, member.id]);
		this.#members[member.accessCode] = member;

		this.#drawMembers(true);
	};

	#onMemberRemove = (event: BaseEvent): void => {
		const member = this.#getMemberFromEvent(event);

		this.#selectedItems = this.#memberSelector.getSelectedItems();
		delete this.#members[member.accessCode];

		this.#drawMembers(true);
	};

	#getMemberFromEvent = (event: BaseEvent): ?Member => {
		const { item } = event.getData();

		return {
			id: item.id,
			accessCode: this.#getAccessCodeByItem(item),
			type: item.entityId,
			name: item.title.text,
			avatar: Type.isStringFilled(item.avatar) ? item.avatar : null,
		};
	};

	#onClosePopup = (): void => {
		if (BX.type.isNotEmptyObject(this.#members))
		{
			this.#updateScopeAccessCodes();
			this.#config.setPopupClose();
		}
		else
		{
			this.#showAlertDialog();
		}
	};

	#showAlertDialog(): void
	{
		const alert = MessageBox.create({
			message: Loc.getMessage('UI_SCOPE_LIST_ALERT_EMPTY_CODES'),
			useAirDesign: true,
			buttons: MessageBoxButtons.OK,
			onOk: (messagebox) => {
				messagebox.close();
				this.#memberSelector.show();
			},
		});

		alert.show();
	}

	#getItemIdByAccessCode(accessCode: string): ItemId
	{
		if (/^I?U(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^I?U(\d+)$/) || null;
			const userId = match ? match[1] : null;

			return ['user', userId];
		}

		if (/^DR(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^DR(\d+)$/) || null;
			const departmentId = match ? match[1] : null;

			return ['department', departmentId];
		}

		if (/^D(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^D(\d+)$/) || null;
			const departmentId = match ? match[1] : null;

			return ['department', `${departmentId}:F`];
		}

		if (/^G(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^G(\d+)$/) || null;
			const groupId = match ? match[1] : null;

			return ['site-groups', groupId];
		}

		if (accessCode.at(0) === 'A')
		{
			return ['user-groups', accessCode];
		}

		if (/^SG(\d+)_([AEK])$/.test(accessCode))
		{
			return ['project-access-codes', accessCode];
		}

		if (/^SND(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SND(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', `${structureNodeId}:F`];
		}

		if (/^SNDR(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SNDR(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', structureNodeId];
		}

		return ['unknown', accessCode];
	}

	#getAccessCodeByItem(item: Item): string
	{
		const entityId = item.entityId;

		if (entityId === 'user')
		{
			return `U${item.id}`;
		}

		if (entityId === 'department')
		{
			if (Type.isString(item.id) && item.id.endsWith(':F'))
			{
				const match = item.id.match(/^(\d+):F$/);
				const originalId = match ? match[1] : null;

				// only members of the department itself
				return `D${originalId}`;
			}

			// whole department recursively
			return `DR${item.id}`;
		}

		if (entityId === 'structure-node')
		{
			if (Type.isString(item.id) && item.id.endsWith(':F'))
			{
				const match = item.id.match(/^(\d+):F$/);
				const originalId = match ? match[1] : null;

				return `SND${originalId}`;
			}

			return `SNDR${item.id}`;
		}

		if (entityId === 'site-groups')
		{
			return `G${item.id}`;
		}

		if (entityId === 'user-groups')
		{
			return item.id;
		}

		if (entityId === 'project-access-codes')
		{
			return item.id;
		}

		return '';
	}

	#onError(response): void
	{
		if (response.status === 'error')
		{
			this.#notifyStatus(response.data[0]);
		}
	}

	#updateScopeAccessCodes = (): void => {
		BX.ajax.runComponentAction('bitrix:ui.form.config', 'updateScopeAccessCodes', {
			data: {
				moduleId: this.#moduleId,
				scopeId: this.#scopeId,
				accessCodes: this.#members,
			},
		}).then((result) => {
			this.#responseAction();
		}).catch(this.#onError.bind(this));
	};

	#copyContextAction = (event: Object): void => {
		if (event.data.scopeId !== Text.toInteger(this.#scopeId))
		{
			return;
		}

		this.#config.showConfirmDialog(
			Loc.getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_COPY'),
			Loc.getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_COPY'),
			() => {
				BX.ajax.runComponentAction('bitrix:ui.form.config', 'copyScope', {
					data: {
						moduleId: this.#moduleId,
						entityTypeId: this.#entityTypeId,
						scopeId: this.#scopeId,
					},
				}).then((result) => {
					this.#responseAction();
				}).catch(this.#onError.bind(this));

				return true;
			},
		);
	};

	#deleteContextAction = (event: Object): void => {
		if (event.data.scopeId !== Text.toInteger(this.#scopeId))
		{
			return;
		}

		this.#config.showConfirmDialog(
			Loc.getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_DELETE'),
			Loc.getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_DELETE'),
			() => {
				BX.ajax.runComponentAction('bitrix:ui.form.config', 'removeScope', {
					data: {
						moduleId: this.#moduleId,
						scopeId: this.#scopeId,
					},
				}).then((result) => {
					this.#responseAction();
				}).catch(this.#onError.bind(this));

				return true;
			},
		);
	};

	#responseAction(result)
	{
		BX.Main.gridManager.getInstanceById('editor_scopes').reload();
	}

	#notifyStatus(err: { code: string, message: string, customData: any }): void
	{
		BX.UI.Notification.Center.notify({
			content: err?.message,
			autoHideDelay: 5000,
		});
	}
}
