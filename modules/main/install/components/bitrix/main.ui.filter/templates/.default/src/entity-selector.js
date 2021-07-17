import {Dialog, DialogOptions, Item} from 'ui.entity-selector';
import {Event, Reflection, Type, Runtime, Dom} from "main.core";
import {EventEmitter, BaseEvent} from "main.core.events";

class EntitySelector
{
	static initExtensionPromise = null;
	static items = {};

	id: string = null;
	filter: BX.Main.Filter = null;
	dialog: Dialog = null;
	dialogOptions: DialogOptions = null;
	control: BX.Main.ui.CustomEntity = null;
	isMultiple: boolean = false;
	needAddEntityIdToFilter = false;
	isActive: boolean = false;

	constructor(id: string, settings)
	{
		this.id = id;
		this.settings = settings ? settings : {};
		this.filter = this.getSetting('filter', null);
		if (!this.filter)
		{
			throw new Error('Filter option is required for EntitySelector field');
		}

		this.isMultiple = !!this.getSetting('isMultiple', false);
		this.needAddEntityIdToFilter = this.getSetting('addEntityIdToResult', 'N') === 'Y';

		this.dialogOptions = this.getSetting('dialogOptions', {});
		this.dialog = null;

		EventEmitter.subscribe('BX.Main.Filter:customEntityFocus', this.onCustomEntityFocus.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:customEntityBlur', this.onCustomEntityBlur.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:onGetStopBlur', this.onGetStopBlur.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:move", ', this.onCustomEntityRemove.bind(this));

		this.controlInputChangeHandler = this.onSearchInputChange.bind(this);
	}

	open(): void
	{
		this.isActive = true;
		if (!this.dialog)
		{
			this.initDialog()
				.then(() => {
					if (this.isActive)
					{
						this.openDialog();
					}
				})
			;
		}
		else
		{
			this.openDialog();
		}
	}

	close(): void
	{
		this.isActive = false;
		if (this.dialog && this.dialog.isOpen())
		{
			this.dialog.hide();
		}
		Event.unbind(this.getFilterFieldInput(), 'input', this.controlInputChangeHandler);
	}

	getFilterField(): ?BX.Filter.Field
	{
		return this.filter.getField(this.id);
	}

	getFilterFieldInputWrapper(): ?HTMLElement
	{
		const field = this.getFilterField();
		if (!field)
		{
			return null;
		}

		return BX.Filter.Utils.getBySelector(field.node, '.main-ui-control-entity');
	}

	getFilterFieldInput(): ?HTMLElement
	{
		const field = this.getFilterField();
		if (!field)
		{
			return null;
		}
		return BX.Filter.Utils.getBySelector(field.node, '.' + this.filter.settings.classStringInput + '[type="text"]');
	}

	setControl(control: BX.Main.ui.CustomEntity): void
	{
		this.control = control;
	}

	unsetControl(): void
	{
		this.control = null;
	}

	getSetting(name: string, defaultValue)
	{
		return this.settings.hasOwnProperty(name)
			? this.settings[name]
			: defaultValue
		;
	};

	openDialog(): void
	{
		if (this.dialog.isOpen())
		{
			return;
		}

		const inputWrapper = this.getFilterFieldInputWrapper();
		this.dialog.setTargetNode(inputWrapper);
		this.dialog.setWidth(inputWrapper.offsetWidth);
		this.dialog.show();
		this.updateSelectedItemsInDialog(this.dialog);

		const searchInput = this.getFilterFieldInput();
		const searchQuery = Type.isDomNode(searchInput) ? searchInput.value.trim() : '';
		if (searchQuery.length)
		{
			this.dialog.search(searchQuery);
		}
		Event.bind(searchInput, 'input', this.controlInputChangeHandler);
	}

	initDialog(): Promise
	{
		return EntitySelector.initDialogExtension()
			.then(exports =>
			{
				const {Dialog} = exports;
				this.dialog = new Dialog({
					...this.dialogOptions,
					id: this.getDialogId(),
					multiple: this.isMultiple,
					enableSearch: false,
					hideOnSelect: true,
					autoHide: false,
					hideByEsc: false,
					events: {
						'Item:onSelect': this.onDialogItemSelect.bind(this),
						'Item:onDeselect': this.onDialogItemDeSelect.bind(this),
						'onLoad': this.onDialogLoad.bind(this),
					},
				});
			})
			;
	}

	addItemToFilter(id: string, title: string): void
	{
		if (!this.control)
		{
			return;
		}
		if (this.isMultiple)
		{
			const currentValues = this.control.getCurrentValues();
			if (
				!(currentValues
					.filter(item => (item.value === id))
					.length
				)
			)
			{
				currentValues.push({
					value: id,
					label: title
				});
				this.control.setMultipleData(currentValues);
			}
		}
		else
		{
			this.control.setSingleData(title, id);
		}
	}

	removeItemFromFilter(id: string): void
	{
		if (!this.control)
		{
			return;
		}
		if (this.isMultiple)
		{
			const currentValues = this.control.getCurrentValues();
			this.control.setMultipleData(
				currentValues.filter(item => (item.value !== id))
			);
		}
		else
		{
			this.control.clearValue();
		}
	}

	getDialogId(): string
	{
		return this.id + '_' + this.filter.getParam('FILTER_ID');
	}

	getItemId(item: Item): string
	{
		if (this.needAddEntityIdToFilter)
		{
			return JSON.stringify([item.getEntityId() + '', item.getId() + '']);
		}
		return item.getId() + '';
	}

	updateSelectedItemsInDialog(dialog: Dialog)
	{
		if (!this.control)
		{
			return;
		}

		let currentValues = this.control.getCurrentValues();
		if (!this.isMultiple)
		{
			currentValues = [currentValues];
		}
		const selectedIds = currentValues.map(item => item.value);

		dialog.getItems()
			.forEach((dialogItem) =>
			{
				if (selectedIds.indexOf(this.getItemId(dialogItem)) > -1)
				{
					dialogItem.select(true);
				}
				else
				{
					dialogItem.deselect();
				}
			})
		;
	}

	onCustomEntityFocus(event: BaseEvent): void
	{
		const [control] = event.getData();
		if (this.id !== control.getId())
		{
			return;
		}
		this.setControl(control);
		this.open();
	}

	onCustomEntityBlur(event: BaseEvent): void
	{
		const [control] = event.getData();
		if (this.id !== control.getId())
		{
			return;
		}
		this.close();
		this.unsetControl();
	}

	onGetStopBlur(event: BaseEvent): void
	{
		const [browserEvent, result] = event.getData();
		if (!(this.dialog && this.dialog.isOpen()))
		{
			return; // if dialog wasn't shown, cancel blur is not required
		}
		const field = this.getFilterField();
		if (!field)
		{
			return;
		}

		const target = browserEvent.target;
		if (
			target === field.node
			|| (
				// click on any child except field deletion button
				field.node.contains(target)
				&& !Dom.hasClass(target, this.filter.settings.classFieldDelete)
			)
			|| target === document.body
		)
		{
			result.stopBlur = true;
			return;
		}

		const dialogContainerElement = this.dialog.getPopup().getContentContainer();
		if (target === dialogContainerElement || dialogContainerElement.contains(target))
		{
			result.stopBlur = true;
		}
	}

	onCustomEntityRemove(event: BaseEvent): void
	{
		const [control] = event.getData();
		if (this.id !== control.getId())
		{
			return;
		}
		if (this.dialog)
		{
			this.dialog.destroy();
			this.dialog = null;
		}
		Event.unbind(this.getFilterFieldInput(), 'input', this.controlInputChangeHandler);
		this.unsetControl();
	}

	onSearchInputChange(event): void
	{
		if (this.dialog)
		{
			this.dialog.search(event.target.value);
		}
	}

	onDialogItemSelect(event: BaseEvent): void
	{
		const {item} = event.getData();
		this.addItemToFilter(this.getItemId(item), item.getTitle());
		this.getFilterFieldInput().value = ''; // clear search query
	}

	onDialogItemDeSelect(event: BaseEvent): void
	{
		const {item} = event.getData();
		this.removeItemFromFilter(this.getItemId(item));
	}

	onDialogLoad(event: BaseEvent): void
	{
		const dialog: Dialog = event.getTarget();
		this.updateSelectedItemsInDialog(dialog);
	}

	static initDialogExtension(): Promise
	{
		if (!EntitySelector.initExtensionPromise)
		{
			EntitySelector.initExtensionPromise = Runtime.loadExtension('ui.entity-selector');
		}

		return EntitySelector.initExtensionPromise;
	}

	static create(id, settings): EntitySelector
	{
		if (Type.isObject(this.items[id]))
		{
			return this.items[id];
		}
		const self = new EntitySelector(id, settings);
		this.items[id] = self;
		return self;
	}
}

const namespace = Reflection.namespace('BX.Filter');
namespace.EntitySelector = EntitySelector;
