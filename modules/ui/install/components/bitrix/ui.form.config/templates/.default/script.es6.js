import { Dom, Runtime, Reflection, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

const namespace = Reflection.namespace('BX.Ui.Form');

class Config
{
	isOpen = false;
	#items = [];
	popupContainer = null;

	constructor(options: Object)
	{
		options.scopes.forEach(item => {
			item.config = this;
			this.#items.push(new BX.Ui.Form.ConfigItem(item));
		}, this);
		this.popupContainer = options.componentId;
	}
}

class ConfigItem extends EventEmitter
{
	#scopeId;
	#members;
	#node;
	#selectedItems;
	#moduleId;
	drawingIconsLimit;
	addToAccessCodesHandler;
	removeFromAccessCodesHandler;
	closePopupHandler;
	config;
	#openPopupEvent = 'BX.Ui.Form.ConfigItem:onComponentOpen';
	#reinitDialogEvent = 'BX.Main.SelectorV2:reInitDialog';

	constructor(options: Array)
	{
		super();
		this.setEventNamespace('BX.Ui.Form');

		this.#scopeId = (options['scopeId'] || null);
		this.#members = (options['members'] || null);
		this.#node = BX(`ui-editor-config-${this.#scopeId}`);
		this.#selectedItems = {};
		this.drawingIconsLimit = (options['drawingIconsLimit'] || 10);
		this.#moduleId = (options['moduleId'] || null);
		this.config = (options['config'] || null);

		this.#drawMembers();

		this.addToAccessCodesHandler = BX.delegate(this.onAddToAccessCodes, this);
		this.removeFromAccessCodesHandler = BX.delegate(this.onRemoveFromAccessCodes, this);
		this.closePopupHandler = BX.delegate(this.onClosePopup, this);

		BX.addCustomEvent('Grid::updated', this.onGridUpdate.bind(this));

		setTimeout(() => {
			BX.onCustomEvent('BX.Ui.Form.ConfigItem:onComponentLoad', [{openDialogWhenInit: false}])
		}, 100);
	}

	onGridUpdate(params: Array): void
	{
		this.#adjust();
	}

	#drawMembers(): void
	{
		if (this.#members)
		{
			let i = 0;
			for (let member in this.#members)
			{
				const item = this.#members[member];
				this.#node.appendChild(this.#createMember(item));
				if (i++ > this.drawingIconsLimit)
				{
					break;
				}
			}
		}
		this.#node.appendChild(this.#createPlusButton());
	}

	#createMember(member: Object): HTMLElement
	{
		const children = (member.avatar
			? Tag.render`<a href="${member.url}" class="ui-editor-config-item-avatar"  title="${Text.encode(member.name)}" style="background-image: url('${member.avatar}')"></a>`
			: Tag.render`<a href="${member.url}" class="ui-icon ui-icon-xs ui-icon-common-user" title="${Text.encode(member.name)}"><i></i></a>`
		);

		return Dom.create('div', {
			attrs: {
				class: 'ui-editor-config-item'
			},
			children: [
				children
			],
		});
	}

	#createPlusButton(): HTMLElement
	{
		return Dom.create('div', {
			events: {
				click: event => {
					if (!this.config.isOpen)
					{
						this.#showPopup();
					}
				},
			},
			attrs: {
				class: 'ui-editor-config-item ui-editor-config-item--add'
			},
		});
	}

	#showPopup(): void
	{
		this.config.isOpen = true;

		this.#addEvents();

		const selectorInstance = BX.Main.selectorManagerV2.controls[this.config.popupContainer].selectorInstance;
		selectorInstance.itemsSelected = {};

		BX.onCustomEvent(this.#openPopupEvent, [{
			id: this.config.popupContainer,
			bindNode: this.#node
		}]);

		BX.onCustomEvent(this.#reinitDialogEvent, [{
			selectorId: this.config.popupContainer,
			selectedItems: Runtime.clone(this.#getSelectedItems())
		}]);
	}

	#addEvents(): void
	{
		EventEmitter.subscribe('BX.Ui.Form.ConfigItem:addToAccessCodes', this.addToAccessCodesHandler);
		EventEmitter.subscribe('BX.Ui.Form.ConfigItem:removeFromAccessCodes', this.removeFromAccessCodesHandler);
		EventEmitter.subscribe('BX.Ui.Form.ConfigItem:closePopup', this.closePopupHandler);
	}

	#getSelectedItems(): Array
	{
		if (this.#members && !Type.isArrayFilled(Object.keys(this.#selectedItems)))
		{
			let items = {};
			for (let member in this.#members)
			{
				items[member] = this.#members[member].type.toUpperCase();
			}
			this.#selectedItems = items;
		}

		return (this.#selectedItems || {});
	}

	static onMemberSelect(params: Array): void
	{
		if (params.state === 'select')
		{
			//BX.onCustomEvent('BX.Ui.Form.ConfigItem:addToAccessCodes', params);
			EventEmitter.emit('BX.Ui.Form.ConfigItem:addToAccessCodes', params);
		}
	}

	static onDialogClose(params: Array): void
	{
		//BX.onCustomEvent('BX.Ui.Form.ConfigItem:closePopup', params);
		EventEmitter.emit('BX.Ui.Form.ConfigItem:closePopup', params);
	}

	onClosePopup(event: Object): void
	{
		this.config.isOpen = false;
		this.#removeEvents();
	}

	#removeEvents(): void
	{
		EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:addToAccessCodes', this.addToAccessCodesHandler);
		EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:removeFromAccessCodes', this.removeFromAccessCodesHandler);
		EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:closePopup', this.closePopupHandler);
	}

	onAddToAccessCodes(event: Object): void
	{
		if (event.data.state === 'select')
		{
			const itemId = event.data.item.id;
			this.#selectedItems[itemId] = event.data.entityType;
		}

		BX.ajax.runComponentAction('bitrix:ui.form.config', 'updateScopeAccessCodes', {
			'data': {
				moduleId: this.#moduleId,
				scopeId: this.#scopeId,
				accessCodes: this.#selectedItems
			}
		}).then(result => {
			this.#adjust(result.data);
		});
	}

	#adjust(members: Array): void
	{
		this.#node = BX(`ui-editor-config-${this.#scopeId}`);

		if (members)
		{
			this.#members = members;
		}

		if (this.#node)
		{
			while (this.#node.firstChild)
			{
				this.#node.removeChild(this.#node.firstChild);
			}
			this.#drawMembers();
		}
	}

	static onMemberUnselect(params: Array): void
	{
		EventEmitter.emit('BX.Ui.Form.ConfigItem:removeFromAccessCodes', params);
		//BX.onCustomEvent('BX.Ui.Form.ConfigItem:removeFromAccessCodes', params);
	}

	onRemoveFromAccessCodes(event: Object): void
	{
		const itemId = event.data.item.id;
		delete this.#selectedItems[itemId]
		this.onAddToAccessCodes(event);
	}
}

namespace.Config = Config;
namespace.ConfigItem = ConfigItem;