import {Type, Loc, Tag} from 'main.core';
import {Field} from './field';
import {FieldTypes} from "./fieldtypes";
import {EnumItem} from './enumitem';

/**
 * @memberof BX.UI.UserFieldFactory
 */
export class Configurator
{
	constructor(params: {
		field: Field,
		onSave: Function,
		onCancel: ?Function,
	})
	{
		if(Type.isPlainObject(params))
		{
			if(params.field instanceof Field)
			{
				this.field = params.field;
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

		this.labelInput = Tag.render`<input class="ui-ctl-element" type="text" value="${this.field.getTitle()}" />`;

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

		if(this.field.getTypeId() === FieldTypes.enumeration)
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

	saveField(): Field
	{
		if(this.timeCheckbox)
		{
			this.field.setIsShowTime(this.timeCheckbox.checked);
		}
		if(this.multipleCheckbox)
		{
			this.field.setIsMultiple(this.multipleCheckbox.checked);
		}
		this.field.setTitle(this.labelInput.value);
		this.field.saveEnumeration(this.enumItems);

		return this.field;
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

		this.field.getEnumeration().forEach((item) =>
		{
			this.addEnumInput(item);
		});
		this.addEnumInput();

		return this.enumContainer;
	}

	addEnumInput(item: Object|EnumItem = null): Element
	{
		if(!(item instanceof EnumItem))
		{
			if(Type.isPlainObject(item))
			{
				item = new EnumItem(item.VALUE);
			}
			else
			{
				item = new EnumItem();
			}
		}

		const node = Tag.render`<div style="margin-bottom: 10px;" class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
			<input class="ui-ctl-element" type="text" value="${item.getValue()}">
			<div class="ui-userfieldfactory-configurator-remove-enum" onclick="${(event) =>
		{
			event.preventDefault();
			this.deleteEnumItem(item);
		}}"></div>
		</div>`;

		item.setNode(node);

		this.enumItems.add(item);

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

		if(!this.field.isSaved() && this.field.isDateField())
		{
			this.timeCheckbox = Tag.render`<input class="ui-ctl-element" type="checkbox">`;
			this.timeCheckbox.checked = this.field.isShowTime();
			this.optionsContainer.appendChild(Tag.render`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${this.timeCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENABLE_TIME')}</div>
				</label>
			</div>`);
		}

		if(!this.field.isSaved() && this.field.getTypeId() !== FieldTypes.boolean)
		{
			this.multipleCheckbox = Tag.render`<input class="ui-ctl-element" type="checkbox">`;
			this.multipleCheckbox.checked = this.field.isMultiple();
			this.optionsContainer.appendChild(Tag.render`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${this.multipleCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_MULTIPLE')}</div>
				</label>
			</div>`);
		}

		return this.optionsContainer;
	}
}