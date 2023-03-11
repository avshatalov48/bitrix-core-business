import {Type, Loc, Tag, Event, Text, Runtime} from 'main.core';
import {BaseEvent, EventEmitter} from "main.core.events";
import {Dialog} from 'ui.entity-selector';
import {Globals} from 'bizproc.globals';

export type Options = {
	target: HTMLElement,
	showStubs: boolean,
	objectName: string,
	events?: { [eventName: string]: (event: BaseEvent) => void },
	itemCreateContext: {
		index: number,
		visibility: string,
		type: string,
		mode: string,
		objectName: string,
		signedDocumentType: string,
	},
};

export class Selector extends EventEmitter
{
	#options: {} = {
		width: 480,
		height: 300,
		multiple: false,
		dropdownMode: true,
		enableSearch: true,
		showAvatars: false,
		compactView: true,
		tagSelectorOptions: {
			textBoxWidth: 400
		},
		targetNode: null,
		events: {},
		recentTabOptions: {},
		searchTabOptions: {},
		searchOptions: {},
	};
	#extraOptions: {};
	#items: [];
	#itemCreateContext: {
		index: number,
		visibility: string,
		type: string,
		mode: string,
		objectName: string,
		signedDocumentType: string,
	};

	#dialog: Dialog;

	constructor(items: [] = [], options?: Options)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Activity.SetGlobalVariable.Selector');

		this.#items = Type.isArrayFilled(items) ? items : [];

		if (Type.isPlainObject(options))
		{
			if (Type.isElementNode(options.target))
			{
				this.#options.targetNode = options.target;
			}

			if (options.showStubs === true)
			{
				this.#extraOptions = {
					recentTabOptions: {
						stub: true,
						icon: '',
						stubOptions: this.#getRecentTabStubOptions(options.objectName),
					},
					searchTabOptions: {
						stub: true,
						stubOptions: this.#getSearchTabStubOptions(options.objectName),
					},
					searchOptions: {
						allowCreateItem: true,
						footerOptions: this.#getSearchOptions(options.objectName),
					},
				};
			}

			if (Type.isPlainObject(options.itemCreateContext))
			{
				this.#itemCreateContext = options.itemCreateContext;
			}

			if (Type.isPlainObject(options.events) && Object.keys(options.events).length > 0)
			{
				this.subscribeFromOptions(options.events);
			}
		}
	}

	create(): this
	{
		if (Type.isNil(this.#dialog))
		{
			let options = this.#options;
			if (Type.isPlainObject(this.#extraOptions))
			{
				options = Object.assign(options, this.#extraOptions);
			}

			options.items = this.#items;
			options.events = {
				'Item:onBeforeSelect': function (event: BaseEvent)
				{
					const dialogItem = event.data.item;
					this.emit(
						'onBeforeSelect',
						new BaseEvent({
							data: {
								item: dialogItem,
							},
						})
					);
				}.bind(this),
				onHide: () => (this.destroy()),
				'Search:onItemCreateAsync': function (event: BaseEvent): Promise
				{
					return new Promise((resolve) => {
						const query = event.getData().searchQuery.query;

						this.#onCreateGlobalsClick(query, resolve);
					});
				}.bind(this),
			};

			this.#dialog = new Dialog(options);

			if (this.#items.length <= 0)
			{
				const footer = Tag.render`
					<span class="ui-selector-footer-link ui-selector-footer-link-add" style="border: none">
						${Text.encode(options.searchOptions.footerOptions?.label ?? '')}
					</span>
				`;

				Event.bind(footer, 'click', this.#onCreateGlobalsClick.bind(this));

				this.#dialog.setFooter(footer);
			}
		}

		return this;
	}

	show()
	{
		if (Type.isNil(this.#dialog))
		{
			this.create();
		}

		this.#dialog.show();
	}

	close()
	{
		if (Type.isNil(this.#dialog))
		{
			return;
		}

		if (this.#dialog.isOpen())
		{
			this.#dialog.hide();
		}
	}

	destroy()
	{
		if (Type.isNil(this.#dialog))
		{
			return;
		}

		this.#dialog.destroy();
		this.#dialog = null;
	}

	#getRecentTabStubOptions(objectName: string): {}
	{
		if (!Type.isStringFilled(objectName))
		{
			return {};
		}

		if (objectName === 'GlobalVar')
		{
			return {
				title: Loc.getMessage('BPSGVA_GVARIABLE_NO_EXIST'),
				subtitle: Loc.getMessage('BPSGVA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};
		}

		if (objectName === 'GlobalConst')
		{
			return {
				title: Loc.getMessage('BPSGVA_GCONSTANT_NO_EXIST'),
				subtitle: Loc.getMessage('BPSGVA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};
		}

		return {};
	}

	#getSearchTabStubOptions(objectName: string): {}
	{
		if (!Type.isStringFilled(objectName))
		{
			return {};
		}

		if (objectName === 'GlobalVar')
		{
			return {
				title: Loc.getMessage('BPSGVA_GVARIABLE_NOT_FOUND'),
				subtitle: Loc.getMessage('BPSGVA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};
		}

		if (objectName === 'GlobalConst')
		{
			return {
				title: Loc.getMessage('BPSGVA_GCONSTANT_NOT_FOUND'),
				subtitle: Loc.getMessage('BPSGVA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};
		}

		return {};
	}

	#getSearchOptions(objectName: string): {}
	{
		if (!Type.isStringFilled(objectName))
		{
			return {};
		}

		if (objectName === 'GlobalVar')
		{
			return {
				label: Loc.getMessage('BPSGVA_CREATE_GVARIABLE'),
			};
		}

		if (objectName === 'GlobalConst')
		{
			return {
				label: Loc.getMessage('BPSGVA_CREATE_GCONSTANT')
			};
		}

		return {};
	}

	#onCreateGlobalsClick(query?: string, resolve?: Function)
	{
		if (!Type.isStringFilled(query))
		{
			query = '';
		}

		const visibility = this.#itemCreateContext.visibility;
		const context = {
			visibility: visibility.slice(visibility.indexOf(':') + 1),
			availableTypes: this.#getAvailableTypes(this.#itemCreateContext.type),
		};

		Globals.Manager.Instance.createGlobals(
			this.#itemCreateContext.mode,
			this.#itemCreateContext.signedDocumentType,
			query,
			context
		).then((slider) => {
			const newContext = {
				'objectName': this.#itemCreateContext.objectName,
				'visibility': this.#itemCreateContext.visibility,
				'index': this.#itemCreateContext.index
			};

			this.#onAfterCreateGlobals(slider, newContext);

			if (Type.isFunction(resolve))
			{
				resolve();
			}
		});
	}

	#onAfterCreateGlobals(slider, context)
	{
		const info = slider.getData().entries();
		const keys = Object.keys(info);
		if (keys.length <= 0)
		{
			return;
		}

		const id = keys[0];
		const property = Runtime.clone(info[keys[0]]);
		property.Multiple = (property.Multiple === 'Y');

		const newDialogItem = {
			entityId: 'bp',
			tabs: 'recents',
			title: property.Name,
			id: '{=' + context.objectName + ':' + id + '}',
			customData: {
				groupId: context.objectName + ':' + property['Visibility'],
				property: property,
				title: property['Name']
			}
		};

		const availableTypes = this.#getAvailableTypes(this.#itemCreateContext.type);
		if (
			newDialogItem.customData.groupId === context.visibility
			&& availableTypes.includes(property.Type)
		)
		{
			this.#dialog.setFooter(null);
			this.#dialog.addItem(newDialogItem);
		}

		this.emit(
			'onAfterCreate',
			new BaseEvent({
				data: {
					item: newDialogItem,
				}
			})
		);
	}

	#getAvailableTypes(baseType: string): []
	{
		if (baseType === 'double')
		{
			return ['int', 'double'];
		}

		if (baseType === 'datetime')
		{
			return ['date', 'datetime'];
		}

		if (['date', 'int', 'user'].includes(baseType))
		{
			return baseType;
		}

		return ['string', 'text', 'select', 'bool', 'int', 'double', 'date', 'datetime', 'user'];
	}
}