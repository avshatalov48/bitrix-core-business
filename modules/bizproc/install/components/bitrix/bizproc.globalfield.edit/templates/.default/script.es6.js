import {Reflection, Type, Loc, Event, Tag} from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';
import 'bp_field_type';
import {Globals} from 'bizproc.globals';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class GlobalFieldEditComponent
{
	oldProperty;
	documentType;
	signedDocumentType: string;
	mode: string;
	availableTypes: object;

	inputValueId: string;

	multipleNode: HTMLSelectElement;
	saveButtonNode: HTMLButtonElement;
	form: HTMLFormElement;

	slider: BX.SidePanel.Slider | null;

	sliderDict: BX.SidePanel.Dictionary | null;

	correspondenceModeToIdName: object;

	constructor(options)
	{
		if (Type.isPlainObject(options))
		{
			this.oldProperty = options.property;
			this.documentType = options.documentType;
			this.signedDocumentType = options.signedDocumentType;
			this.mode = options.mode;
			this.availableTypes = options.types;

			this.inputValueId = options.inputValueId;

			this.multipleNode = options.multipleNode;
			this.saveButtonNode = options.saveButtonNode;
			this.form = options.form;

			this.slider = options.slider;
		}
	}

	init()
	{
		this.sliderDict = this.slider ? this.slider.getData() : null;
		this.correspondenceModeToIdName =  {
			constant: 'Constant',
			variable: 'Variable',
		};

		this.editInputValue(this.oldProperty['Type'], this.oldProperty);


		Event.bind(this.saveButtonNode, 'click', this.saveHandler.bind(this));
	}

	editInputValue(type, property)
	{
		let prop = BX.clone(property);
		let defaultProperty = {
			Type: type ?? 'string',
			Multiple: false,
			Default: ''
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

		let control = BX.Bizproc.FieldType.renderControl(
			this.documentType,
			prop,
			'VALUE',
			prop['Default'],
			'public'
		);
		control.className = 'ui-ctl ui-ctl-textbox ui-ctl-w100 global-fields-max-width';
		control.id = this.inputValueId;

		let inputValueNode = document.getElementById(this.inputValueId);
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
			let wrapper = Tag.render`<div class="ui-ctl ui-ctl-w100" id="bizproc_globEditComponent"></div>`;
			let optionControl = BX.Bizproc.FieldType.createControlOptions(
				prop,
				this.setSelectOptionFromForm.bind(this)
			);
			optionControl.className = 'ui-form-label';
			wrapper.appendChild(optionControl);
			wrapper.getElementsByTagName('textarea')[0].className = 'ui-ctl-element ui-ctl-textarea ui-ctl-resize-y ui-ctl-w100';
			wrapper.getElementsByTagName('textarea')[0].style.paddingTop = '6px';
			wrapper.getElementsByTagName('button')[0].className = 'ui-btn ui-btn-xs ui-btn-light-border';

			control.before(wrapper);
		}

		if (!prop['Multiple'])
		{
			document.getElementsByName('VALUE')[0].placeholder = Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY');

			return;
		}

		if (prop['Type'] === 'user')
		{
			return;
		}

		let values = document.getElementsByName('VALUE[]');
		for (let i in values)
		{
			if (values.hasOwnProperty(i))
			{
				values[i].placeholder = Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY');
			}
		}

		if (control.getElementsByTagName('a').length > 0)
		{
			let buttonAdd = control.getElementsByTagName('a')[0];
			BX.bind(buttonAdd, 'click', () => {
				let values = document.getElementsByName('VALUE[]');
				let value = BX.clone(values[values.length - 1]);

				if (prop['Type'] !== 'date' && prop['Type'] !== 'datetime')
				{
					// remove wrapper div
					let parent = values[values.length - 1].parentNode;
					if (parent)
					{
						parent.remove();
					}
					control.insertBefore(value, buttonAdd.parentNode);
				}

				values[values.length - 1].placeholder = Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY');
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

	saveHandler()
	{
		let formElements = this.form.elements;
		let id = formElements['ID'].value;
		let property = {
			Name: formElements['NAME'].value,
			Description: formElements['DESCRIPTION'].value,
			Type: formElements['TYPE'].value,
			Options: '',
			Default: this.getValues(formElements),
			Visibility: formElements['VISIBILITY'].value,
			Multiple: formElements['MULTIPLE'].value,
			Required: 'N',
			CreatedBy: this.oldProperty['CreatedBy'] ? this.oldProperty['CreatedBy'] : null,
			CreatedDate: this.oldProperty['CreatedDate'] ? this.oldProperty['CreatedDate'] : null
		};

		if (!this.validateName(property.Name))
		{
			return true;
		}

		if (!id)
		{
			let date = new Date();
			id = this.correspondenceModeToIdName[this.mode] + date.getTime().toString();
		}

		if (this.oldProperty.Options)
		{
			property.Options = this.oldProperty.Options;
		}

		let me = this;
		Globals.Manager.Instance.upsertGlobalsAction(id, property, this.signedDocumentType, this.mode).then((response) => {
			if (response.data && response.data.error)
			{
				MessageBox.alert(
					response.data.error,
					() => {
						BX.removeClass(me.saveButtonNode, 'ui-btn-wait');

						return true;
					});
			}
			else
			{
				me.sliderDict.set(id, property);
				me.slider.close();
			}
		});

		return true;
	}

	getValues(formElements)
	{
		if (formElements['VALUE'])
		{
			return formElements['VALUE'].value;
		}

		if (formElements['VALUE[]'])
		{
			let radioNodeList = formElements['VALUE[]'];
			let values = [];
			if (Type.isElementNode(radioNodeList))
			{
				if (radioNodeList.tagName !== 'SELECT')
				{
					return radioNodeList.value;
				}

				for (let i in Object.keys(radioNodeList.selectedOptions))
				{
					values.push(radioNodeList.selectedOptions[i].value);
				}

				return values;
			}

			for (let i in radioNodeList)
			{
				if (radioNodeList.hasOwnProperty(i))
				{
					values.push(radioNodeList[i].value);
				}
			}

			return values;
		}

	}

	validateName(name)
	{
		let me = this;
		if (!name)
		{
			MessageBox.alert(
				BX.Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY_NAME'),
				() => {
					BX.removeClass(me.saveButtonNode, 'ui-btn-wait');

					return true;
				}
			);

			return false;
		}

		return true;

	}
}

namespace.GlobalFieldEditComponent = GlobalFieldEditComponent;