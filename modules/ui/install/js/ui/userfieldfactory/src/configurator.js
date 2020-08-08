import {Type, Loc, Tag, Text} from 'main.core';
import {UserField} from 'ui.userfield';
import {FieldTypes} from "./fieldtypes";
import {EnumItem} from './enumitem';

/**
 * @memberof BX.UI.UserFieldFactory
 */
export class Configurator
{
	constructor(params: {
		userField: UserField,
		onSave: Function,
		onCancel: ?Function,
	})
	{
		if(Type.isPlainObject(params))
		{
			if(params.userField)
			{
				this.userField = params.userField;
			}
			if(Type.isFunction(params.onSave))
			{
				this.onSave = params.onSave;
			}
			if(Type.isFunction(params.onCancel))
			{
				this.onCancel = params.onCancel;
			}
		}

		this.enumItems = new Set();
	}

	render(): Element
	{
		this.node = Tag.render`<div class="ui-userfieldfactory-configurator"></div>`;

		this.labelInput = Tag.render`<input class="ui-ctl-element" type="text" value="${Text.encode(this.userField.getTitle())}" />`;

		this.node.appendChild(Tag.render`<div class="ui-userfieldfactory-configurator-block">
			<div class="ui-userfieldfactory-configurator-title">
				<span class="ui-userfieldfactory-configurator-title-text">${Loc.getMessage('UI_USERFIELD_FACTORY_CONFIGURATOR_FIELD_TITLE')}</span>
			</div>
			<div class="ui-userfieldfactory-configurator-content">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${this.labelInput}
				</div>
			</div>
		</div>`);

		if(this.userField.getUserTypeId() === FieldTypes.getTypes().enumeration)
		{
			this.node.appendChild(this.renderEnumeration());
		}

		this.node.appendChild(this.renderOptions());

		const save = (event) =>
		{
			event.preventDefault();
			if(Type.isFunction(this.onSave))
			{
				this.onSave(this.saveField());
			}
		};

		const cancel = (event) =>
		{
			event.preventDefault();
			if(Type.isFunction(this.onCancel))
			{
				this.onCancel();
			}
			else
			{
				this.node.style.display = 'none';
			}
		};

		this.saveButton = Tag.render`<span class="ui-btn ui-btn-primary" onclick="${save.bind(this)}">${Loc.getMessage('UI_USERFIELD_SAVE')}</span>`;
		this.cancelButton = Tag.render`<span class="ui-btn ui-btn-light-border" onclick="${cancel.bind(this)}">${Loc.getMessage('UI_USERFIELD_CANCEL')}</span>`;

		this.node.appendChild(Tag.render`<div class="ui-userfieldfactory-configurator-block">
			${this.saveButton}${this.cancelButton}
		</div>`);

		return this.node;
	}

	saveField(): UserField
	{
		if(this.timeCheckbox)
		{
			if(this.timeCheckbox.checked)
			{
				this.userField.setUserTypeId(FieldTypes.getTypes().datetime);
			}
			else
			{
				this.userField.setUserTypeId(FieldTypes.getTypes().date);
			}
		}
		if(this.multipleCheckbox)
		{
			this.userField.setIsMultiple(this.multipleCheckbox.checked);
		}
		this.userField.setTitle(this.labelInput.value);
		this.userField.setIsMandatory(this.mandatoryCheckbox.checked);
		this.saveEnumeration(this.userField, this.enumItems);

		return this.userField;
	}

	renderEnumeration(): Element
	{
		this.enumItemsContainer = Tag.render`<div class="ui-userfieldfactory-configurator-block"></div>`;

		this.enumAddItemContainer = Tag.render`<div class="ui-userfieldfactory-configurator-block-add-field">
			<span class="ui-userfieldfactory-configurator-add-button" onclick="${() => {this.addEnumInput().focus();}}">${Loc.getMessage('UI_USERFIELD_ADD')}</span>
		</div>`;

		this.enumContainer = Tag.render`<div class="ui-userfieldfactory-configurator-block">
			<div class="ui-userfieldfactory-configurator-title">
				<span class="ui-userfieldfactory-configurator-title-text">${Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUM_ITEMS')}</span>
			</div>
			${this.enumItemsContainer}
			${this.enumAddItemContainer}
		</div>`;

		this.userField.getEnumeration().forEach((item) =>
		{
			this.addEnumInput(item);
		});
		this.addEnumInput();

		return this.enumContainer;
	}

	addEnumInput(item: ?{
		value: string,
		id: ?number,
	}): Element
	{
		let enumItem;
		if(Type.isPlainObject(item))
		{
			enumItem = new EnumItem(item.value, item.id);
		}
		else
		{
			enumItem = new EnumItem();
		}

		const node = Tag.render`<div style="margin-bottom: 10px;" class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
			<input class="ui-ctl-element" type="text" value="${enumItem.getValue()}">
			<div class="ui-userfieldfactory-configurator-remove-enum" onclick="${(event) => {
				event.preventDefault();
				this.deleteEnumItem(enumItem);
			}}"></div>
		</div>`;

		enumItem.setNode(node);

		this.enumItems.add(enumItem);

		this.enumItemsContainer.appendChild(node);

		return node;
	}

	deleteEnumItem(item: EnumItem)
	{
		this.enumItemsContainer.removeChild(item.getNode());
		this.enumItems.delete(item);
	}

	renderOptions(): Element
	{
		this.optionsContainer = Tag.render`<div class="ui-userfieldfactory-configurator-block"></div>`;

		this.mandatoryCheckbox = Tag.render`<input class="ui-ctl-element" type="checkbox">`;
		this.mandatoryCheckbox.checked = (this.userField.isMandatory());
		this.optionsContainer.appendChild(Tag.render`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${this.mandatoryCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_REQUIRED')}</div>
				</label>
			</div>`);

		if(!this.userField.isSaved() && (this.userField.getUserTypeId() === FieldTypes.getTypes().date || this.userField.getUserTypeId() === FieldTypes.getTypes().datetime))
		{
			this.timeCheckbox = Tag.render`<input class="ui-ctl-element" type="checkbox">`;
			this.timeCheckbox.checked = (this.userField.getUserTypeId() === FieldTypes.getTypes().datetime);
			this.optionsContainer.appendChild(Tag.render`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${this.timeCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENABLE_TIME')}</div>
				</label>
			</div>`);
		}

		if(!this.userField.isSaved() && this.userField.getUserTypeId() !== FieldTypes.getTypes().boolean)
		{
			this.multipleCheckbox = Tag.render`<input class="ui-ctl-element" type="checkbox">`;
			this.multipleCheckbox.checked = this.userField.isMultiple();
			this.optionsContainer.appendChild(Tag.render`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${this.multipleCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_MULTIPLE')}</div>
				</label>
			</div>`);
		}

		return this.optionsContainer;
	}

	saveEnumeration(userField: UserField, enumItems: EnumItem[])
	{
		const items = [];
		let sort = 100;

		enumItems.forEach((item) =>
		{
			items.push({
				value: item.getValue(),
				sort: sort,
				id: item.getId(),
			});

			sort += 100;
		});

		userField.setEnumeration(items);
	}
}