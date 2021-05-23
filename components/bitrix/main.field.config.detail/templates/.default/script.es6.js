import {Tag, Text, Reflection, Runtime, Event, Type, ajax as Ajax, Dom, Loc} from 'main.core';
import {Loader} from "main.loader";
import {MessageBox} from 'ui.dialogs.messagebox';
import {UserField} from 'ui.userfield';

import {ButtonManager, Button} from 'ui.buttons';

const namespace = Reflection.namespace('BX.Main.UserField');

/**
 * @memberOf BX.Main.UserField
 */
class Config
{
	static #instances: Map = new Map();

	id: number = 0;
	inputs: Map = new Map();
	tabs: Map = new Map();
	container: Element = null;
	settingsContainer: ?Element = null;
	settingsTable: ?Element = null;
	errorsContainer: ?Element = null;
	saveButton: ?Button = null;
	cancelButton: ?Button = null;
	deleteButton: ?Button = null;
	moduleId: string;

	constructor(params: {
		id: number,
		container: Element,
		errorsContainer: Element,
		moduleId: string,
	})
	{
		this.tabs = new Map();
		this.inputs = new Map();
		const saveButtonNode = document.getElementById('ui-button-panel-save');
		if(saveButtonNode)
		{
			this.saveButton = ButtonManager.createFromNode(saveButtonNode);
		}
		const cancelButtonNode = document.getElementById('ui-button-panel-cancel');
		if(cancelButtonNode)
		{
			this.cancelButton = ButtonManager.createFromNode(cancelButtonNode);
		}
		const deleteButtonNode = document.getElementById('ui-button-panel-remove');
		if(deleteButtonNode)
		{
			this.deleteButton = ButtonManager.createFromNode(deleteButtonNode);
		}

		if(Type.isPlainObject(params))
		{
			this.id = Text.toInteger(params.id);
			if(Type.isDomNode(params.container))
			{
				this.container = params.container;
			}
			if(Type.isDomNode(params.errorsContainer))
			{
				this.errorsContainer = params.errorsContainer;
			}
			this.moduleId = params.moduleId;
		}

		this.bindEvents();

		this.fillTabs();

		this.constructor.#instances.set(this.id, this);

		this.adjustVisibility();
		this.syncEnumDefaultSelector();
	}

	getBooleanInputNames(): Array
	{
		return [
			'multiple',
			'mandatory',
			'showFilter',
			'isSearchable',
		]
	}

	getSettingsContainer(): ?Element
	{
		if(this.container && !this.settingsContainer)
		{
			this.settingsContainer = this.container.querySelector('[data-role="main-user-field-settings-container"]');
		}

		return this.settingsContainer;
	}

	getSettingsTable(): ?Element
	{
		if(!this.settingsTable)
		{
			const settingsContainer = this.getSettingsContainer();
			if(settingsContainer)
			{
				this.settingsTable = settingsContainer.querySelector('[data-role="main-user-field-settings-table"]');
			}
		}

		return this.settingsTable;
	}

	fillTabs()
	{
		const tabNames = [
			'common', 'labels', 'additional', 'list'
		];
		if(this.container)
		{
			tabNames.forEach((name: string) => {
				const tab = this.container.querySelector('[data-tab="' + name + '"]');
				if(tab)
				{
					this.tabs.set(name, tab);
				}
			});
		}
	}

	showTab(name: string)
	{
		Array.from(this.tabs.keys()).forEach((tabName: string) => {
			if(tabName === name)
			{
				this.tabs.get(tabName).classList.add('main-user-field-edit-tab-current');
			}
			else
			{
				this.tabs.get(tabName).classList.remove('main-user-field-edit-tab-current');
			}
		});
	}

	getInput(name: string): ?Element
	{
		if(this.container && !this.inputs.has(name))
		{
			const input = this.container.querySelector('[data-role="main-user-field-' + name + '"]');
			if(input)
			{
				this.inputs.set(name, input);
			}
		}

		return this.inputs.get(name);
	}

	getInputValue(name: string): ?string
	{
		if(name === 'userTypeId')
		{
			return this.getSelectedUserTypeId();
		}

		const input = this.getInput(name);
		if(input)
		{
			if(this.getBooleanInputNames().includes(name))
			{
				return (input.checked ? 'Y' : 'N');
			}

			return input.value;
		}

		return '';
	}

	bindEvents()
	{
		const userTypeIdSelector = this.getInput('userTypeId');
		if(userTypeIdSelector)
		{
			Event.bind(userTypeIdSelector, 'change', this.handleUserTypeChange.bind(this));
		}

		const commonLabelInput = this.getInput('editFormLabel');
		if(commonLabelInput && commonLabelInput.parentElement && commonLabelInput.parentElement.parentElement)
		{
			const languageId = commonLabelInput.parentElement.parentElement.dataset['language'];
			const currentLanguageLabelInput = this.getInput('editFormLabel-' + languageId);
			if(currentLanguageLabelInput)
			{
				Event.bind(commonLabelInput, 'change', () => {
					this.syncLabelInputs(commonLabelInput, currentLanguageLabelInput);
				});

				Event.bind(currentLanguageLabelInput, 'change', () => {
					this.syncLabelInputs(currentLanguageLabelInput, commonLabelInput);
				});
			}
		}

		const addEnum = this.container.querySelector('[data-role="main-user-field-enum-add"]');
		if(addEnum)
		{
			Event.bind(addEnum, 'click', this.addEnumRow.bind(this));
		}

		const deleteButtons = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-delete"]'));
		deleteButtons.forEach((target) => {
			Event.bind(target, 'click', this.deleteEnumRow.bind(this));
		});

		const enumRows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
		enumRows.forEach((row: Element) => {
			const input = row.querySelector('[data-role="main-user-field-enum-value"]');
			if(input)
			{
				Event.bind(input, 'change', this.syncEnumDefaultSelector.bind(this));
			}
		});

		Event.bind(
			this.saveButton.getContainer(),
			'click',
			(event) => {
				event.preventDefault();
				this.save();
			},
			{
				passive: false
			}
		);

		if(this.deleteButton)
		{
			Event.bind(
				this.deleteButton.getContainer(),
				'click',
				(event) => {
					event.preventDefault();
					this.delete();
				}
			);
		}
	}

	getSelectedUserTypeId(): ?string
	{
		const option = this.getSelectedOption('userTypeId');
		if(option)
		{
			return option.value;
		}

		return null;
	}

	getSelectedOption(inputName: string): ?HTMLOptionElement
	{
		const input = this.getInput(inputName);
		if(input)
		{
			const options = Array.from(input.querySelectorAll('option'));
			const index = input.selectedIndex;
			return options[index];
		}

		return null;
	}

	handleUserTypeChange()
	{
		if(this.isProgress)
		{
			return;
		}

		const settingsTable = this.getSettingsTable();
		if(!settingsTable)
		{
			return;
		}

		const userTypeId = this.getSelectedUserTypeId();
		if(!userTypeId)
		{
			return;
		}

		this.startProgress();
		Ajax.runComponentAction('bitrix:main.field.config.detail', 'getSettings', {
			data: {
				userTypeId,
			},
			analyticsLabel: 'mainUserFieldConfigGetSettings',
			mode: 'class',
		}).then((response) => {
			this.stopProgress();
			let html = '';
			if(response.data.html && response.data.html.length > 0)
			{
				html = response.data.html;
			}
			Runtime.html(settingsTable, html).then(() => {
				this.adjustVisibility();
			});
		}).catch((response) => {
			this.stopProgress();
			this.showErrors(response.errors);
		});
	}

	getLoader()
	{
		if(!this.loader)
		{
			this.loader = new Loader({size: 150});
		}

		return this.loader;
	}

	startProgress()
	{
		this.isProgress = true;
		if(!this.getLoader().isShown())
		{
			this.getLoader().show(this.container);
		}
		this.hideErrors();
	}

	stopProgress()
	{
		this.isProgress = false;
		this.getLoader().hide();
		setTimeout(() => {
			this.saveButton.setWaiting(false);
			if(this.deleteButton)
			{
				this.deleteButton.setWaiting(false);
				Dom.removeClass(this.deleteButton.getContainer(), 'ui-btn-wait');
			}
		}, 200);
	}

	showErrors(errors: string[])
	{
		let text = '';
		errors.forEach((message) => {
			text += message;
		});
		if(Type.isDomNode(this.errorsContainer))
		{
			this.errorsContainer.innerText = text;
			this.errorsContainer.parentElement.style.display = 'block';
		}
		else
		{
			console.error(text);
		}
	}

	hideErrors()
	{
		if(Type.isDomNode(this.errorsContainer))
		{
			this.errorsContainer.innerText = '';
			this.errorsContainer.parentElement.style.display = 'none';
		}
	}

	getSettings(): {}
	{
		const settings = {};

		const settingsForm = this.container.querySelector('[data-role="main-user-field-settings"]');
		if(settingsForm)
		{
			const formData = new FormData(settingsForm);
			for(let pair of formData.entries())
			{
				const name = pair[0].substr(9, pair[0].length - 10);
				settings[name] = pair[1];
			}
		}

		return settings;
	}

	prepareFieldData(): {}
	{
		if(!this.container)
		{
			return {};
		}

		const editFormLabel = {};

		const labelInputs = Array.from(this.container.querySelectorAll('[data-role="main-user-field-label-container"]'));
		labelInputs.forEach((labelContainer) => {
			const languageId = labelContainer.dataset['language'];
			editFormLabel[languageId] = this.getInputValue('editFormLabel-' + languageId);
		});

		const list = [];
		const userTypeId = this.getInputValue('userTypeId');
		if(userTypeId === 'enumeration')
		{
			let selectedDefaultIndex = 0;
			const enumDefault = this.getInput('enumDefault');
			if(enumDefault)
			{
				selectedDefaultIndex = enumDefault.selectedIndex;
			}
			const sortStep = 100;
			let sort = 0;
			let index = 1;
			const rows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
			rows.forEach((row: Element) => {
				let def = 'N';
				if(selectedDefaultIndex === index)
				{
					def = 'Y';
				}
				sort += sortStep;
				const id = Text.toInteger(row.dataset['id']);
				list.push({
					value: row.querySelector('[data-role="main-user-field-enum-value"]').value,
					def,
					sort,
					id,
				});
				index++;
			})
		}

		const id = Text.toInteger(this.getInputValue('id'));
		let fieldName = this.getInputValue('fieldName');
		if(id <= 0)
		{
			fieldName = this.getInputValue('fieldPrefix') + fieldName;
		}

		return {
			id,
			editFormLabel,
			entityId: this.getInputValue('entityId'),
			fieldName: fieldName,
			sort: this.getInputValue('sort'),
			multiple: this.getInputValue('multiple'),
			mandatory: this.getInputValue('mandatory'),
			showFilter: this.getInputValue('showFilter'),
			isSearchable: this.getInputValue('isSearchable'),
			userTypeId,
			settings: this.getSettings(),
			enum: list,
		};
	}

	save()
	{
		if(this.isProgress)
		{
			return;
		}
		if(!this.moduleId)
		{
			return;
		}
		this.startProgress();
		const fieldData = this.prepareFieldData();
		
		let languageId = null;
		const commonLabelInput = this.getInput('editFormLabel');
		if(commonLabelInput && commonLabelInput.parentElement && commonLabelInput.parentElement.parentElement)
		{
			languageId = commonLabelInput.parentElement.parentElement.dataset['language'];
		}
		
		const userField = new UserField(fieldData, {
			languageId,
			moduleId: this.moduleId,
		});
		userField.save().then(() => {
			this.afterSave(userField);
			this.stopProgress();
		}).catch((errors) => {
			this.showErrors(errors);
			this.stopProgress();
		});
	}

	delete()
	{
		if(this.isProgress)
		{
			return;
		}
		if(!this.moduleId)
		{
			return;
		}

		const id = Text.toInteger(this.getInputValue('id'));
		if(id <= 0)
		{
			return;
		}

		MessageBox.confirm(
			Loc.getMessage('MAIN_FIELD_CONFIG_DELETE_CONFIRM'),
			() => {
				return new Promise((resolve) => {
					const userField = new UserField(this.prepareFieldData(), {
						moduleId: this.moduleId,
					});
					this.startProgress();
					userField.delete().then(() => {
						this.stopProgress();
						const slider = this.getSlider();
						if(slider)
						{
							this.addDataToSlider('userFieldData', userField.serialize());
							slider.close();
						}
						else
						{
							MessageBox.alert(Loc.getMessage('MAIN_FIELD_CONFIG_DELETE_SUCCESS'));
						}
						resolve();
					}).catch((errors) => {
						this.stopProgress();
						this.showErrors(errors);
						resolve();
					});
				});
			},
			null,
			(box) => {
				this.stopProgress();
				box.close();
			}
		);
	}

	adjustVisibility()
	{
		const settingsTable = this.getSettingsTable();
		const settingsTab = document.querySelector('[data-role="tab-additional"]');
		const listTab = document.querySelector('[data-role="tab-list"]');
		if(!settingsTable || !settingsTab || !listTab)
		{
			return;
		}
		if(settingsTable.childElementCount <= 0)
		{
			settingsTab.style.display = 'none';
		}
		else
		{
			settingsTab.style.display = 'block';
		}
		const userTypeId = this.getSelectedUserTypeId();
		if(userTypeId === 'enumeration')
		{
			listTab.style.display = 'block';
		}
		else
		{
			listTab.style.display = 'none';
		}
		if(userTypeId === 'boolean')
		{
			this.changeInputVisibility('multiple', 'none');
			this.changeInputVisibility('mandatory', 'none');
		}
		else
		{
			this.changeInputVisibility('multiple', 'block');
			this.changeInputVisibility('mandatory', 'block');
		}
	}

	changeInputVisibility(inputName: string, display: string)
	{
		const input = this.getInput(inputName);
		if(input && input.parentElement && input.parentElement.parentElement)
		{
			input.parentElement.parentElement.style.display = display;
		}
	}

	afterSave(userField: UserField)
	{
		this.addDataToSlider('userFieldData', userField.serialize());
		const slider = this.getSlider();
		if(slider)
		{
			slider.close();
		}
		else
		{
			const id = Text.toInteger(this.getInputValue('id'));
			if(id <= 0)
			{
				if(!!userField.getDetailUrl())
				{
					location.href = userField.getDetailUrl();
					return;
				}
				this.getInput('id').value = userField.getId();
				const prefixInput = this.getInput('fieldPrefix');
				if(prefixInput && prefixInput.parentElement && prefixInput.parentElement.parentElement)
				{
					prefixInput.parentElement.parentElement.classList.remove('main-user-field-name-with-prefix');
					Dom.remove(prefixInput.parentElement);
				}
				this.getInput('fieldName').value = userField.getName();
				this.getInput('fieldName').disabled = true;
				this.getInput('fieldName').parentElement.classList.remove('ui-ctl-inline');
			}
		}
	}

	getSlider()
	{
		if(Reflection.getClass('BX.SidePanel'))
		{
			return BX.SidePanel.Instance.getSliderByWindow(window);
		}

		return null;
	}

	addDataToSlider(key, data)
	{
		if(Type.isString(key))
		{
			let slider = this.getSlider();
			if(slider)
			{
				slider.getData().set(key, data);
				BX.SidePanel.Instance.postMessage(slider, 'userfield-list-update');
			}
		}
	}

	static handleLeftMenuClick(id: number, tabName: string)
	{
		if(Config.#instances)
		{
			const instance = Config.#instances.get(id);
			if(instance)
			{
				instance.showTab(tabName);
			}
		}
	}

	syncLabelInputs(fromLabel: HTMLInputElement, toLabel: HTMLInputElement)
	{
		const tab = fromLabel.closest('.main-user-field-edit-tab');
		if(tab && tab.classList.contains('main-user-field-edit-tab-current'))
		{
			toLabel.value = fromLabel.value;
		}
	}

	addEnumRow()
	{
		const addEnum = this.container.querySelector('[data-role="main-user-field-enum-add"]');
		if (addEnum)
		{
			const row = Tag.render`
					<div class="main-user-field-enum-row" data-role="main-user-field-enum-row">
						<div class="main-user-field-enum-row-inner ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
							<span class="main-user-field-enum-row-draggable" style=""></span>
							<input class="ui-ctl-element" type="text" name="ENUM[][VALUE]" value="" data-role="main-user-field-enum-value" onchange="${this.syncEnumDefaultSelector.bind(this)}">
							<div class="main-user-field-enum-delete" onclick="${this.deleteEnumRow.bind(this)}"></div>
						</div>
					</div>`;
			Dom.append(row, document.querySelector('.main-user-field-enum-row-list'));

			let item = new DragDropItem();
			item.init(row);
		}
	}

	deleteEnumRow({target})
	{
		Dom.remove(target.parentElement);
		this.syncEnumDefaultSelector();
	}

	syncEnumDefaultSelector()
	{
		const userTypeId = this.getInputValue('userTypeId');
		if(userTypeId === 'enumeration')
		{
			const selector = this.getInput('enumDefault');
			if(!selector)
			{
				return;
			}
			let selectedId;
			let selectedValue;
			const selectedDefaultOption = this.getSelectedOption('enumDefault');
			if(selectedDefaultOption)
			{
				if(selectedDefaultOption.dataset['id'])
				{
					selectedId = Text.toInteger(selectedDefaultOption.dataset['id']);
				}
				else
				{
					selectedValue = selectedDefaultOption.value;
				}
			}
			const options = Array.from(selector.querySelectorAll('option'));
			options.forEach((option: HTMLOptionElement) => {
				if(option.value !== 'empty')
				{
					Dom.remove(option);
				}
			});
			const rows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
			rows.forEach((row: Element) => {
				const id = Text.toInteger(row.dataset['id']);
				const value = row.querySelector('[data-role="main-user-field-enum-value"]').value;
				const selected = (
					(id > 0 && id === selectedId)
					|| (value === selectedValue)
				);
				if(value.length > 0)
				{
					selector.appendChild(Tag.render`<option ${selected ? 'selected="selected"' : ''} value="${Text.encode(value)}" data-id="${id}">${Text.encode(value)}</option>`);
				}
			});
		}
	}
}

class DragDropItem
{
	constructor() {
		this.itemContainer = null;
		this.draggableItemContainer = null;
		this.dragElement = null;
	}

	init(item)
	{
		this.itemContainer = item;
		const dragButton = this.itemContainer.querySelector('.main-user-field-enum-row-draggable');

		if (jsDD)
		{
			dragButton.onbxdragstart = this.onDragStart.bind(this);
			dragButton.onbxdrag = this.onDrag.bind(this);
			dragButton.onbxdragstop = this.onDragStop.bind(this);

			jsDD.registerObject(dragButton);

			this.itemContainer.onbxdestdraghover = this.onDragEnter.bind(this);
			this.itemContainer.onbxdestdraghout = this.onDragLeave.bind(this);
			this.itemContainer.onbxdestdragfinish = this.onDragDrop.bind(this);

			jsDD.registerDest(this.itemContainer, 30);
		}
	}

	onDragStart()
	{
		Dom.addClass(this.itemContainer, "main-user-field-enum-row-disabled");

		if (!this.dragElement)
		{
			this.dragElement = this.itemContainer.cloneNode(true);

			this.dragElement.style.position = "absolute";
			this.dragElement.style.width = this.itemContainer.offsetWidth + "px";
			this.dragElement.className = "main-user-field-enum-row-drag";

			Dom.append(this.dragElement, document.body);
		}
	}

	onDrag(x, y)
	{
		if (this.dragElement)
		{
			this.dragElement.style.left = x + "px";
			this.dragElement.style.top = y + "px";
		}
	}

	onDragStop()
	{
		Dom.removeClass(this.itemContainer, "main-user-field-enum-row-disabled");
		Dom.remove(this.dragElement);
		this.dragElement = null;
	}

	onDragEnter(draggableItem)
	{
		this.draggableBtnContainer = draggableItem.closest('.main-user-field-enum-row');

		if (this.draggableBtnContainer !== this.itemContainer)
		{
			this.showDragTarget();
		}
	}

	onDragLeave()
	{
		this.hideDragTarget();
	}

	onDragDrop()
	{
		if (this.draggableBtnContainer !== this.itemContainer)
		{
			this.hideDragTarget();
			Dom.remove(this.draggableBtnContainer);
			Dom.insertBefore(this.draggableBtnContainer, this.itemContainer);
		}
	}

	showDragTarget()
	{
		Dom.addClass(this.itemContainer, 'main-user-field-enum-row-target-shown');
		this.getDragTarget().style.height = this.itemContainer.offsetHeight + "px";
	}

	hideDragTarget()
	{
		Dom.removeClass(this.itemContainer, "main-user-field-enum-row-target-shown");
		this.getDragTarget().style.height = 0;
	}

	getDragTarget()
	{
		if (!this.dragTarget)
		{
			this.dragTarget = Tag.render`<div class="main-user-field-enum-row-drag-target"></div>`;
			Dom.prepend(this.dragTarget, this.itemContainer);
		}

		return this.dragTarget;
	}

}

class DragDropBtnContainer
{
	constructor() {
		this.container = document.querySelector('.main-user-field-enum-row-list');
		this.height = null;
	}

	init()
	{
		this.container.onbxdestdraghover = BX.delegate(this.onDragEnter, this);
		this.container.onbxdestdraghout = BX.delegate(this.onDragLeave, this);
		this.container.onbxdestdragfinish = BX.delegate(this.onDragDrop, this);
		jsDD.registerDest(this.container, 40);
	}

	onDragEnter(draggableItem)
	{
		this.draggableBtnContainer = draggableItem.closest('.main-user-field-enum-row');
		this.height = this.draggableBtnContainer.offsetHeight;
		this.showDragTarget();
	}

	onDragLeave()
	{
		this.hideDragTarget();
	}

	onDragDrop()
	{
		this.hideDragTarget();
		Dom.remove(this.draggableBtnContainer);
		Dom.insertBefore(this.draggableBtnContainer, this.dragTarget);
	}

	showDragTarget()
	{
		Dom.addClass(this.container, 'main-user-field-enum-row-list-target-shown');
		this.getDragTarget().style.height = this.height + "px";
	}

	hideDragTarget()
	{
		Dom.removeClass(this.container, "main-user-field-enum-row-list-target-shown");
		this.getDragTarget().style.height = 0;
	}

	getDragTarget()
	{
		if (!this.dragTarget)
		{
			this.dragTarget = Tag.render`<div class="main-user-field-enum-row-list-target"></div>`;
			Dom.append(this.dragTarget, this.container);
		}

		return this.dragTarget;
	}

}

namespace.Config = Config;
namespace.DragDropItem = DragDropItem;
namespace.DragDropBtnContainer = DragDropBtnContainer;