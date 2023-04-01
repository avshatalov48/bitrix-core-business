import {Type, MenuManager, Loc, Event} from 'main.core';
import {EntitySelector} from 'ui.entity-selector'

declare type PersonalizationOptions = {
	button: Element,
	targetInput: ?Element,
	fields: FieldItem,
	onItemClick: (event: BaseEvent) => void
}

declare type FieldItem = {
	id: string,
	text: string,
	title: string,
	items: FieldItem[],
}

export class PersonalizationSelector
{
	#dialog: EntitySelector.Dialog;
	#menuButton: Element;
	#targetInput: ?Element;
	#onItemClick: (event: BaseEvent) => void;
	#fields: FieldItem[];

	constructor(options: PersonalizationOptions)
	{
		this.#menuButton = options.button;
		this.#targetInput = options.targetInput;
		this.#fields = options.fields;
		this.#onItemClick = options.onItemClick || {};

		Event.bind(this.#menuButton, 'click', this.openMenu.bind(this));
	}

	setName(name)
	{
		if (Type.isString(name))
		{
			this.name = name;
		}
	}

	getName()
	{
		return this.name;
	}

	onKeyDown(container, e)
	{
		if (e.keyCode == 45 && e.altKey === false && e.ctrlKey === false && e.shiftKey === false)
		{
			this.openMenu(e);
			e.preventDefault();
		}
	}

	openMenu(e)
	{
		if (this.#dialog)
		{
			this.#dialog.show();
			return;
		}

		let menuItems = [];
		const menuGroups = {
			'ROOT': {
				title: Loc.getMessage('SENDER_PERSONALIZATION_SELECTOR_ROOT'),
				entityId: 'sender',
				tabs: 'recents',
				id: 'ROOT',
				children: []
			}
		};
		this.#prepareItem(this.#fields, menuGroups);

		if (Object.keys(menuGroups).length < 2)
		{
			if (menuGroups['ROOT']['children'].length > 0)
			{
				menuItems = menuGroups['ROOT']['children'];
			}
		} else
		{
			if (menuGroups['ROOT']['children'].length > 0)
			{
				menuItems.push(menuGroups['ROOT']);
			}
			delete menuGroups['ROOT'];

			for (let groupKey in menuGroups)
			{
				if (menuGroups.hasOwnProperty(groupKey) && menuGroups[groupKey]['children'].length > 0)
				{
					menuItems.push(menuGroups[groupKey])
				}
			}
		}

		this.#dialog = new EntitySelector.Dialog({
			targetNode: this.#menuButton,
			tagSelectorOptions: {textBoxWidth: 500},
			width: 500,
			height: 300,
			multiple: false,
			dropdownMode: true,
			enableSearch: true,
			items: this.injectDialogMenuTitles(menuItems.reverse()),
			showAvatars: false,
			events: {
				'Item:onBeforeSelect': Type.isFunction(this.#onItemClick) ? this.#onItemClick : (event) =>
				{
					event.preventDefault();
					this.onFieldSelect(event.getData().item.getCustomData().get('property'))
				}
			},
			compactView: true
		});

		this.#dialog.show();
	}

	#prepareItem(fields: FieldItem[], menuGroups: Object)
	{
		fields.forEach(field =>
		{
			let groupKey = field.id.indexOf('.') < 0 ?
				(field.items && field.items.length > 0 ? field.id : 'ROOT')
				: field.id.split('.')[0] + '#';
			if (!field.text && !field.title)
			{
				return;
			}

			if (!menuGroups[groupKey])
			{
				menuGroups[groupKey] = {
					title: field.text || field.title,
					entityId: 'sender',
					tabs: 'recents',
					tabId: 'sender',
					id: field.id,
					children: []
				} ;
			}

			if (field.items && field.items.length > 0)
			{
				this.#prepareItem(field.items, menuGroups);
				return;
			}
			menuGroups[groupKey]['children'].push({
				title: field.text || field.title,
				customData: {property: field},
				entityId: 'sender',
				tabs: 'recents',
				id: field.id,
			});
		});
	}

	injectDialogMenuTitles(items)
	{
		items.forEach(parent =>
		{
			if (Type.isArray(parent.children))
			{
				parent.searchable = false;
				this.injectDialogMenuSupertitles(parent.title, parent.children);
			}
		}, this);
		return items;
	}

	injectDialogMenuSupertitles(title, children)
	{
		children.forEach(function (child)
		{
			if (!child.supertitle)
			{
				child.supertitle = title;
			}
			if (Type.isArray(child.children))
			{
				child.searchable = false;
				this.injectDialogMenuSupertitles(child.title, child.children);
			}
		}, this);
	}

	onFieldSelect(field)
	{
		if (!field)
		{
			return;
		}

		this.#targetInput.value = this.#targetInput.value + field.id;
	}

	destroy()
	{
		if (this.#dialog)
		{
			this.#dialog.destroy();
		}
	}
}
