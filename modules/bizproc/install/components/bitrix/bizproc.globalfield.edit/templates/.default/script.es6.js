import { Reflection, Type, Loc, Event, Tag, Runtime, Dom } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';
import 'bp_field_type';
import { Globals } from 'bizproc.globals';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class GlobalFieldEditComponent
{
	oldProperty;
	documentType;
	signedDocumentType: string;
	mode: string;
	availableTypes: {};

	inputValueId: string;

	multipleNode: HTMLSelectElement;
	saveButtonNode: HTMLButtonElement;
	form: HTMLFormElement;

	slider: BX.SidePanel.Slider | null;

	sliderDict: BX.SidePanel.Dictionary | null;

	correspondenceModeToIdName = {
		constant: 'Constant',
		variable: 'Variable'
	};

	constructor(options)
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		this.oldProperty = options.property;
		this.documentType = options.documentType;
		this.signedDocumentType = options.signedDocumentType;
		this.mode = options.mode;
		this.availableTypes = options.types;
		this.visibilityNames = options.visibilityNames;

		this.inputValueId = options.inputValueId;

		this.multipleNode = options.multipleNode;
		this.saveButtonNode = options.saveButtonNode;
		this.form = options.form;

		this.slider = options.slider;
	}

	init()
	{
		this.sliderDict = this.slider ? this.slider.getData() : null;
		this.editInputValue(this.oldProperty['Type'], this.oldProperty);

		Event.bind(this.saveButtonNode, 'click', this.saveHandler.bind(this));
	}

	editInputValue(type, property)
	{
		let prop = Runtime.clone(property);
		const defaultProperty = {
			Type: type ?? 'string',
			Multiple: false,
			Default: '',
			Placeholder: Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY'),
		};

		if (this.availableTypes[defaultProperty.Type] === undefined)
		{
			defaultProperty.Type = Object.keys(this.availableTypes)[0];
		}

		if (!Type.isPlainObject(property))
		{
			prop = defaultProperty;
		}
		else
		{
			if (Type.isString(property['Multiple']))
			{
				prop['Multiple'] = (property['Multiple'] === 'Y');
			}
			prop = {...defaultProperty, ...prop};
		}

		this.multipleNode.value = prop['Multiple'] ? 'Y' : 'N';

		if (prop.Type === 'select' && property.Options === undefined)
		{
			prop.Options = this.oldProperty.Options ?? {};
		}

		const control = BX.Bizproc.FieldType.renderControl(
			this.documentType,
			prop,
			'VALUE',
			prop['Default'],
			'public'
		);
		control.className = 'ui-ctl ui-ctl-textbox ui-ctl-w100';
		control.id = this.inputValueId;

		const inputValueNode = document.getElementById(this.inputValueId);
		if (inputValueNode)
		{
			inputValueNode.replaceWith(control);
		}

		if (prop.Type !== 'select' && document.getElementById('bizproc_globEditComponent'))
		{
			document.getElementById('bizproc_globEditComponent').remove();
			this.oldProperty.Options = {};
		}
		else if (prop.Type === 'select' && !document.getElementById('bizproc_globEditComponent'))
		{
			const wrapper = Tag.render`<div class="ui-ctl ui-ctl-w100" id="bizproc_globEditComponent"></div>`;
			const optionControl = BX.Bizproc.FieldType.createControlOptions(
				prop,
				this.setSelectOptionFromForm.bind(this)
			);
			optionControl.className = 'ui-form-label';
			Dom.append(optionControl, wrapper);
			wrapper.getElementsByTagName('textarea')[0].className = 'ui-ctl-element ui-ctl-textarea ui-ctl-resize-y ui-ctl-w100';
			Dom.style(wrapper.getElementsByTagName('textarea')[0], 'paddingTop', '6px');
			wrapper.getElementsByTagName('button')[0].className = 'ui-btn ui-btn-xs ui-btn-light-border';

			control.before(wrapper);
		}

		if (prop['Type'] === 'user')
		{
			return;
		}

		if (control.getElementsByTagName('a').length > 0)
		{
			const buttonAdd = control.getElementsByTagName('a')[0];
			Event.bind(buttonAdd, 'click', () => {
				const values = document.getElementsByName('VALUE[]');
				const value = Runtime.clone(values[values.length - 1]);

				if (prop['Type'] !== 'date' && prop['Type'] !== 'datetime')
				{
					// remove wrapper div
					const parent = values[values.length - 1].parentNode;
					if (parent)
					{
						parent.remove();
					}
					Dom.insertBefore(value, buttonAdd.parentNode);
				}
			});
		}
	}

	setSelectOptionFromForm(options)
	{
		this.oldProperty.Options = options;

		this.editInputValue('select', {
			Options: options,
			Type: 'select',
			Multiple : this.multipleNode.value
		});
	}

	saveHandler(): boolean
	{
		const formElements = this.form.elements;

		let id: string = Type.isStringFilled(this.oldProperty['id']) ? this.oldProperty['id'] : '';
		const property = {
			Name: formElements['NAME'].value,
			Description: formElements['DESCRIPTION'].value,
			Type: formElements['TYPE'].value,
			Options: '',
			Default: this.getValues(formElements),
			Visibility: formElements['VISIBILITY'].value,
			Multiple: formElements['MULTIPLE'].value,
			Required: 'N',
		};

		if (!this.validateName(property.Name))
		{
			return true;
		}

		if (!Type.isStringFilled(id))
		{
			const date = new Date();
			id = this.correspondenceModeToIdName[this.mode] + date.getTime().toString();
		}

		if (this.oldProperty.Options)
		{
			property.Options = this.oldProperty.Options;
		}

		const me = this;
		Globals.Manager.Instance.upsertGlobalsAction(id, property, this.signedDocumentType, this.mode).then((response) => {
			if (response.data && response.data.error)
			{
				MessageBox.alert(
					response.data.error,
					() => {
						Dom.removeClass(me.saveButtonNode, 'ui-btn-wait');

						return true;
					});
			}
			else
			{
				me.sliderDict.set(id, {...property, VisibilityName: this.visibilityNames[property.Visibility]});
				me.slider.close();
			}
		});

		return true;
	}

	getValues(formElements): any
	{
		if (formElements['VALUE'])
		{
			return formElements['VALUE'].value;
		}

		if (formElements['VALUE[]'])
		{
			const radioNodeList = formElements['VALUE[]'];
			const values = [];
			if (Type.isElementNode(radioNodeList))
			{
				if (radioNodeList.tagName !== 'SELECT')
				{
					return radioNodeList.value;
				}

				for (const i in Object.keys(radioNodeList.selectedOptions))
				{
					values.push(radioNodeList.selectedOptions[i].value);
				}

				return values;
			}

			for (const i in radioNodeList)
			{
				if (radioNodeList.hasOwnProperty(i))
				{
					values.push(radioNodeList[i].value);
				}
			}

			return values;
		}

	}

	validateName(name): boolean
	{
		const me = this;
		if (!name)
		{
			MessageBox.alert(
				BX.Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY_NAME'),
				() => {
					Dom.removeClass(me.saveButtonNode, 'ui-btn-wait');

					return true;
				}
			);

			return false;
		}

		return true;

	}
}

namespace.GlobalFieldEditComponent = GlobalFieldEditComponent;