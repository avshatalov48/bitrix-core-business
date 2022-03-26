import { Type, Tag, Loc, Dom, Event, Text} from 'main.core';
import { EventEmitter } from 'main.core.events'
import { Menu, MenuManager } from 'main.popup';
import type { BpMixedSelectorOptions } from 'bizproc.mixed-selector';

export class BpMixedSelector extends EventEmitter
{
	targetNode: HTMLElement = null;
	targetTitle: string;
	tabs: Object<string, Object> = null;
	template: Array = [];
	activityName: string = '';
	maxWidth: number = 300;
	maxHeight: number = 500;
	minWidth: number = 100;
	minHeight: number = 60;
	objectName: string = 'mixed_selector[object]';
	fieldName: string = 'mixed_selector[field]';
	checkActivityChildren: boolean = true;

	map: Object<string, Object> = null;
	menuItems: Array = null;
	menuTargetNode: HTMLElement = null;
	menuId: string = null;

	objectInputNode: HTMLInputElement = null;
	fieldInputNode: HTMLInputElement = null;

	constructor(selectorOptions: BpMixedSelectorOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.MixedSelector.BpMixedSelector');

		const options: BpMixedSelectorOptions = Type.isPlainObject(selectorOptions) ? selectorOptions : {};

		this.setTargetNode(options.targetNode);
		this.setObjectTabs(options.objectTabs);
		this.setTemplate(options.template);
		this.setActivityName(options.activityName);
		this.setSize(options.size);
		this.setInputNames(options.inputNames);
		this.setTargetTitle(options.targetTitle);
		this.setCheckActivityChildren(options.checkActivityChildren);
	}

	static getAvailableTabsName(): Array
	{
		return ['Parameter', 'Variable', 'Constant', 'GlobalConst', 'GlobalVar', 'Document', 'Activity'];
	}

	static getAvailableTabsLocMessages(): Object
	{
		return {
			Parameter: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_PARAMETER'),
			Variable: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_VARIABLE'),
			Constant: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_CONSTANT'),
			GlobalConst: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_GLOBAL_CONSTANT'),
			GlobalVar: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_GLOBAL_VARIABLE'),
			Document: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_DOCUMENT_FIELDS'),
			Activity: Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_ADDITIONAL_RESULT'),
		};
	}

	/* region basic SET/GET */

	setTargetNode(node: HTMLElement)
	{
		if (Type.isDomNode(node))
		{
			this.targetNode = node;
		}
	}

	getTargetNode(): HTMLElement | null
	{
		return this.targetNode;
	}

	setObjectTabs(tabs)
	{
		if (Type.isPlainObject(tabs))
		{
			this.tabs = tabs;
		}
	}

	getObjectTabs(): Object | null
	{
		return this.tabs;
	}

	setTemplate(template)
	{
		if (Type.isArrayFilled(template))
		{
			this.template = template;
		}
	}

	getTemplate(): Array
	{
		return this.template;
	}

	setActivityName(name)
	{
		if (Type.isStringFilled(name))
		{
			this.activityName = name;
		}
	}

	getActivityName(): string
	{
		return this.activityName;
	}

	setSize(size)
	{
		if (!Type.isPlainObject(size))
		{
			return;
		}

		if (Type.isNumber(size.maxWidth))
		{
			this.maxWidth = size.maxWidth
		}
		if (Type.isNumber(size.minWidth))
		{
			this.minWidth = size.minWidth
		}
		if (Type.isNumber(size.maxHeight))
		{
			this.maxHeight = size.maxHeight
		}
		if (Type.isNumber(size.minHeight))
		{
			this.minHeight = size.minHeight
		}
	}

	getSize(): Object
	{
		return {
			maxWidth: this.maxWidth,
			minWidth: this.minWidth,
			maxHeight: this.maxHeight,
			minHeight: this.minHeight,
		};
	}

	setInputNames(names)
	{
		if (!Type.isPlainObject(names))
		{
			return;
		}

		if (Type.isStringFilled(names.object))
		{
			this.objectName = names.object;
		}

		if (Type.isStringFilled(names.field))
		{
			this.fieldName = names.field;
		}
	}

	getInputNames(): Object
	{
		return {
			object: this.objectName,
			field: this.fieldName
		};
	}

	setTargetTitle(title)
	{
		if (Type.isStringFilled(title))
		{
			this.targetTitle = title;

			return;
		}

		this.targetTitle = Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_CHOOSE_TARGET');
	}

	getTargetTitle(): string
	{
		return this.targetTitle;
	}

	setCheckActivityChildren(check)
	{
		if (Type.isBoolean(check))
		{
			this.checkActivityChildren = check;
		}
	}

	getCheckActivityChildren(): boolean
	{
		return this.checkActivityChildren;
	}

	/* endregion */

	getMenu(): Menu | null
	{
		const me = this;
		if (this.menuId)
		{
			//todo: modify popup position.
			return MenuManager.getMenuById(this.menuId);
		}
		this.menuId = BX.util.getRandomString();

		const size = this.getSize();

		return MenuManager.create(
			me.menuId,
			me.getMenuTargetNode(),
			me.getMenuItems(),
			{
				zIndex: 200,
				autoHide: true,
				offsetLeft: (Dom.getPosition(me.getMenuTargetNode())['width'] / 2),
				angle: { position: 'top', offset: 0 },
				maxWidth: size.maxWidth,
				maxHeight: size.maxHeight,
				minWidth: size.minWidth,
				minHeight: size.minHeight
			}
		);
	}

	getMenuTargetNode(): HTMLElement | null
	{
		return this.menuTargetNode;
	}

	getMenuItems(): Array
	{
		if (this.menuItems)
		{
			return this.menuItems;
		}
		this.menuItems = [];

		this.#fillMenuItems();

		return this.menuItems;
	}

	getMenuItemsByTabName(tabName: string): Array
	{
		const tabsItems = this.getMenuItems();

		for (const i in tabsItems)
		{
			if (tabsItems[i].tabName === tabName)
			{
				return tabsItems[i].items;
			}
		}

		return [];
	}

	#fillMenuItems()
	{
		const me = this;
		const map = this.#getTabsMap();
		const locMapNames = BpMixedSelector.getAvailableTabsLocMessages();
		const mapKeys = BX.util.object_keys(map);
		for (const i in mapKeys)
		{
			if (mapKeys[i] !== 'Activity')
			{
				this.menuItems.push({
					text: locMapNames[mapKeys[i]],
					items: this.#extractMenuItem(map[mapKeys[i]], mapKeys[i]),
					tabName: mapKeys[i]
				});
			}
			else
			{
				const activitiesItems = this.#getTemplateActivitiesItems(this.template, map[mapKeys[i]]);
				const groupByItemActivitiesItems = [];
				activitiesItems.forEach((activityItem) => {
					if (!Type.isArrayFilled(activityItem))
					{
						return;
					}

					const items = [];
					activityItem.forEach((item) => {
						if (!Type.isStringFilled(item.description))
						{
							return;
						}

						items.push({
							text: Text.encode(item.text + ' (' + item.description + ')'),
							object: item.object,
							field: item.field,
							property: item,
							onclick: me.#onChooseFieldClick.bind(me),
							})
					});

					if (Type.isArrayFilled(items))
					{
						groupByItemActivitiesItems.push({
							text: activityItem[0].description,
							object: activityItem[0].object,
							items: items,
						});
					}
				});

				if (Type.isArrayFilled(groupByItemActivitiesItems))
				{
					this.menuItems.push({
						text: locMapNames[mapKeys[i]],
						items: groupByItemActivitiesItems,
						tabName: 'Activity'
					});
				}
			}
		}
	}

	#getTabsMap(): Object
	{
		if (this.map)
		{
			return this.map;
		}
		this.map = {};

		const availableTabs = BpMixedSelector.getAvailableTabsName();

		const keys = Object.keys(this.tabs);
		for (const i in keys)
		{
			if (availableTabs.includes(keys[i]) && Object.keys(this.tabs[keys[i]]).length > 0)
			{
				this.map[keys[i]] = this.tabs[keys[i]];
			}
		}

		if (this.template.length < 0)
		{
			if (this.map['Activity'])
			{
				delete this.map['Activity'];
			}
		}

		return this.map;
	}

	#extractMenuItem(items, object): Array
	{
		const result = [];
		const itemsKeys = Object.keys(items);
		for (const i in itemsKeys)
		{
			result.push({
				text: BX.util.htmlspecialchars(items[itemsKeys[i]].Name),
				object,
				field: itemsKeys[i],
				property: items[itemsKeys[i]],
				onclick: this.#onChooseFieldClick.bind(this),
			});
		}

		return result;
	}

	#getTemplateActivitiesItems(template, activities): Array
	{
		let result = [];

		for (let i = 0, s = template.length; i < s; ++i)
		{
			if (template[i].Name === this.activityName && !this.checkActivityChildren)
			{
				continue;
			}

			const activityType = template[i].Type.toLowerCase();
			const activityData = activities[activityType] ?? {};

			const returnActivityData = activityData['RETURN'];
			const additionalResult = activityData['ADDITIONAL_RESULT'];

			if (returnActivityData)
			{
				const keys = Object.keys(returnActivityData);
				const activityResult = [];

				for (const j in keys)
				{
					activityResult.push({
						text: returnActivityData[keys[j]].NAME,
						description: template[i].Properties.Title || activityData.NAME,
						value: '{=' + template[i].Name + ':' + keys[j] + '}',
						object: template[i].Name,
						field: keys[j],
						property: {
							Name: returnActivityData[keys[j]].NAME,
							Type: returnActivityData[keys[j]].TYPE,
						}
					});
				}

				if (activityResult.length > 0)
				{
					result.push(activityResult);
				}
			}
			else if (Type.isArray(additionalResult))
			{
				const properties = template[i]['Properties'];
				additionalResult.forEach(function (addProp)
				{
					if (properties[addProp])
					{
						const keys = Object.keys(properties[addProp]);
						const activityResult = [];

						for (const j in keys)
						{
							const field = properties[addProp][keys[j]];
							activityResult.push({
								text: field.Name,
								description: properties['Title'] || activityData['NAME'],
								value: '{=' + template[i]['Name'] + ':' + keys[j] + '}',
								object: template[i]['Name'],
								field: keys[j],
								property: field
							});
						}

						if (activityResult.length > 0)
						{
							result.push(activityResult);
						}
					}
				}, this);
			}

			if (template[i]['Children'] && template[i]['Children'].length > 0)
			{
				const subResult = this.#getTemplateActivitiesItems(template[i]['Children'], activities);
				result = result.concat(subResult);
			}
		}

		return result;
	}

	#onChooseFieldClick(event, item)
	{
		const menu = this.getMenu();
		menu.close();

		// todo: item.text htmlspecialchars applied twice
		this.setSelectedObjectAndField(item.object, item.field, item.text);
		EventEmitter.emit(this, 'onSelect', {item: item});
	}

	renderMixedSelector()
	{
		const link = Tag.render`<a href="#">${BX.util.htmlspecialchars(this.getTargetTitle())}</a>`;
		this.menuTargetNode = link;
		Event.bind(link, 'click', this.#onChooseTargetClick.bind(this));

		const objectInput = Tag.render`
			<input 
				type="hidden" 
				name="${this.objectName}" 
				data-role="mixed-selector-object"
				value=""
			>
		`;
		this.objectInputNode = objectInput;

		const fieldInput = Tag.render`
			<input 
				type="hidden" 
				name="${this.fieldName}" 
				data-role="mixed-selector-field"
				value=""
			>
		`;
		this.fieldInputNode = fieldInput;

		Dom.append(link, this.targetNode);
		Dom.append(objectInput, this.targetNode);
		Dom.append(fieldInput, this.targetNode);
	}

	#onChooseTargetClick(event)
	{
		const menu = this.getMenu();
		menu.show();

		event.preventDefault();
	}

	getSelectedObjectValue(): string | null
	{
		if (this.objectInputNode)
		{
			return this.objectInputNode.value;
		}

		return null;
	}

	getSelectedFieldValue(): string | null
	{
		if (this.fieldInputNode)
		{
			return this.fieldInputNode.value;
		}

		return null;
	}

	setSelectedObjectAndField(object: string, field: string, fieldTitle: string)
	{
		const target = this.getMenuTargetNode();
		const tabsLocMessage = BpMixedSelector.getAvailableTabsLocMessages();

		if (BpMixedSelector.getAvailableTabsName().includes(object))
		{
			target.innerText = tabsLocMessage[object] + ': ' + fieldTitle;
		}
		else
		{
			target.innerText = tabsLocMessage['Activity'] + ': ' + fieldTitle;
		}

		if (Type.isStringFilled(object) && Type.isStringFilled(field))
		{
			this.objectInputNode.value = object;
			this.fieldInputNode.value = field;
		}
	}
}