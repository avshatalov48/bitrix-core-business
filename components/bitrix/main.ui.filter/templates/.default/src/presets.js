/* eslint-disable no-underscore-dangle */
/* eslint-disable class-methods-use-this */
import {Type, Dom, Text, Runtime, Event, Tag} from 'main.core';
import 'ui.icons.service';
import {AdditionalFilter} from './additional-filter';

export class Presets
{
	constructor(parent)
	{
		this.parent = null;
		this.presets = null;
		this.container = null;
		this.init(parent);
	}

	init(parent)
	{
		this.parent = parent;
	}

	bindOnPresetClick()
	{
		(this.getPresets() || []).forEach((current) => {
			Event.bind(current, 'click', BX.delegate(this._onPresetClick, this));
		});
	}

	/**
	 * Gets add preset field
	 * @return {?HTMLElement}
	 */
	getAddPresetField()
	{
		return this.getContainer().querySelector('.main-ui-filter-new-filter');
	}


	/**
	 * Gets add preset name input
	 * @return {?HTMLInputElement}
	 */
	getAddPresetFieldInput()
	{
		return this.getAddPresetField().querySelector('.main-ui-filter-sidebar-edit-control');
	}


	/**
	 * Clears add preset input value
	 */
	clearAddPresetFieldInput()
	{
		const input = this.getAddPresetFieldInput();

		if (Type.isDomNode(input))
		{
			input.value = '';
		}
	}


	/**
	 * Finds preset node by child node
	 * @param {?HTMLElement} node
	 * @return {?HTMLElement}
	 */
	normalizePreset(node)
	{
		return node.closest('.main-ui-filter-sidebar-item');
	}


	/**
	 * Deactivates all presets
	 */
	deactivateAllPresets()
	{
		this.getPresets().forEach((current) => {
			Dom.removeClass(current, 'main-ui-filter-current-item');
		});
	}

	/**
	 * Creates sidebar preset item
	 * @param {string} id - Preset id
	 * @param {string} title - Preset title
	 * @param {boolean} [isPinned] - Pass true is preset pinned
	 */
	createSidebarItem(id, title, isPinned)
	{
		return BX.decl({
			block: 'sidebar-item',
			text: Text.decode(title),
			id,
			pinned: isPinned,
			noEditPinTitle: this.parent.getParam('MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET'),
			editNameTitle: this.parent.getParam('MAIN_UI_FILTER__EDIT_PRESET_TITLE'),
			removeTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_PRESET'),
			editPinTitle: this.parent.getParam('MAIN_UI_FILTER__SET_AS_DEFAULT_PRESET'),
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_TITLE'),
		});
	}


	/**
	 * Highlights preset node as active
	 * @param {?HTMLElement|string} preset - preset node or preset id
	 */
	activatePreset(preset)
	{
		this.deactivateAllPresets();

		const presetNode = (() => {
			if (Type.isString(preset))
			{
				return this.getPresetNodeById(preset);
			}

			return preset;
		})();

		if (Type.isDomNode(presetNode))
		{
			Dom.addClass(presetNode, 'main-ui-filter-current-item');
		}
	}


	/**
	 * Gets preset node by preset id
	 * @param {string} id
	 * @return {?HTMLElement}
	 */
	getPresetNodeById(id)
	{
		return this.getPresets().find((current) => {
			return Dom.attr(current, 'data-id') === id;
		});
	}


	/**
	 * Gets preset id by preset node
	 * @param {?HTMLElement} preset
	 */
	getPresetId(preset)
	{
		return Dom.attr(preset, 'data-id');
	}


	/**
	 * Updates preset name
	 * @param {?HTMLElement} presetNode
	 * @param {string} name
	 */
	updatePresetName(presetNode, name)
	{
		if (Type.isDomNode(presetNode) && Type.isString(name) && name !== '')
		{
			const nameNode = this.getPresetNameNode(presetNode);

			if (Type.isDomNode(nameNode))
			{
				Runtime.html(nameNode, name);
			}
		}
	}


	/**
	 * Removes preset
	 * @param {HTMLElement} presetNode
	 * @param {string} presetId
	 * @param {boolean} isDefault
	 */
	removePreset(presetNode, presetId, isDefault)
	{
		const currentPresetId = this.getCurrentPresetId();
		let newPresets = [];
		const postData = {
			preset_id: presetId,
			is_default: isDefault,
		};

		const getData = {
			FILTER_ID: this.parent.getParam('FILTER_ID'),
			action: 'REMOVE_FILTER',
		};

		this.parent.saveOptions(postData, getData);
		BX.remove(presetNode);

		if (BX.type.isArray(this.parent.params.PRESETS))
		{
			newPresets = this.parent.params.PRESETS.filter((current) => {
				return current.ID !== presetId;
			}, this);

			this.parent.params.PRESETS = newPresets;
		}

		if (BX.type.isArray(this.parent.editablePresets))
		{
			newPresets = this.parent.editablePresets.filter((current) => {
				return current.ID !== presetId;
			}, this);

			this.parent.editablePresets = newPresets;
		}

		if (presetId === currentPresetId)
		{
			this.parent.getSearch().removePreset();
			this.resetPreset();
		}
	}


	/**
	 * Pin preset (Sets as default preset)
	 * @param {string} presetId
	 */
	pinPreset(presetId)
	{
		if (!BX.type.isNotEmptyString(presetId))
		{
			presetId = 'default_filter';
		}

		const presetNode = this.getPresetNodeById(presetId);

		if (this.parent.getParam('VALUE_REQUIRED_MODE'))
		{
			if (presetId === 'default_filter')
			{
				return;
			}
		}

		const params = {FILTER_ID: this.parent.getParam('FILTER_ID'), GRID_ID: this.parent.getParam('GRID_ID'), action: 'PIN_PRESET'};
		const data = {preset_id: presetId};

		this.getPresets().forEach(function(current) {
			Dom.removeClass(current, this.parent.settings.classPinnedPreset);
		}, this);

		BX.addClass(presetNode, this.parent.settings.classPinnedPreset);

		this.parent.saveOptions(data, params);
	}

	_onPresetClick(event) {
		let presetNode; let presetId; let presetData; let isDefault; let target; let settings; let
			parent;

		event.preventDefault();

		parent = this.parent;
		settings = parent.settings;
		target = event.target;
		presetNode = event.currentTarget;
		presetId = this.getPresetId(presetNode);
		presetData = this.getPreset(presetId);

		if (Dom.hasClass(target, settings.classPinButton))
		{
			if (this.parent.isEditEnabled())
			{
				if (Dom.hasClass(presetNode, settings.classPinnedPreset))
				{
					this.pinPreset('default_filter');
				}
				else
				{
					this.pinPreset(presetId);
				}
			}
		}

		if (Dom.hasClass(target, settings.classPresetEditButton))
		{
			this.enableEditPresetName(presetNode);
		}

		if (Dom.hasClass(target, settings.classPresetDeleteButton))
		{
			isDefault = 'IS_DEFAULT' in presetData ? presetData.IS_DEFAULT : false;
			this.removePreset(presetNode, presetId, isDefault);
			return false;
		}

		if (!Dom.hasClass(target, settings.classPresetDragButton)
			&& !Dom.hasClass(target, settings.classAddPresetFieldInput))
		{
			if (this.parent.isEditEnabled())
			{
				this.updateEditablePreset(this.getCurrentPresetId());
			}

			const currentPreset = this.getPreset(this.getCurrentPresetId());
			const preset = this.getPreset(presetId);
			currentPreset.ADDITIONAL = [];
			preset.ADDITIONAL = [];

			this.activatePreset(presetNode);
			this.applyPreset(presetId);

			if (!this.parent.isEditEnabled())
			{
				parent.applyFilter(null, true);

				if (event.isTrusted)
				{
					parent.closePopup();
				}

				if (parent.isAddPresetEnabled())
				{
					parent.disableAddPreset();
				}
			}
		}
	}


	/**
	 * Applies default preset
	 * @return {BX.Promise}
	 */
	applyPinnedPreset()
	{
		const Filter = this.parent;
		const isPinned = this.isPinned(this.getCurrentPresetId());
		let promise;

		if (this.parent.getParam('VALUE_REQUIRED')
			&& this.getPinnedPresetId() === 'default_filter')
		{
			this.applyPreset('default_filter');
			this.deactivateAllPresets();
			promise = this.parent.applyFilter();
		}
		else
		if (!isPinned)
		{
			const pinnedPresetId = this.getPinnedPresetId();
			const presetData = this.getPreset(pinnedPresetId);
			presetData.ADDITIONAL = [];

			const pinnedPresetNode = this.getPinnedPresetNode();
			const clear = false;
			const applyPreset = true;

			this.deactivateAllPresets();
			this.activatePreset(pinnedPresetNode);
			this.applyPreset(pinnedPresetId);
			promise = Filter.applyFilter(clear, applyPreset);
			Filter.closePopup();
		}
		else
		{
			promise = Filter.resetFilter();
		}


		return promise;
	}


	/**
	 * Updates editable presets
	 * @param {string} presetId
	 */
	updateEditablePreset(presetId)
	{
		const fields = this.parent.getFilterFieldsValues();
		const presetRows = this.getFields().map((curr) => { return BX.data(curr, 'name'); });
		const presetFields = this.parent.preparePresetFields(fields, presetRows);
		const preset = this.getPreset(presetId);

		preset.FIELDS = presetFields;
		preset.TITLE = this.getPresetInput(this.getPresetNodeById(presetId)).value;
		preset.ROWS = presetRows;
	}


	/**
	 * Gets preset input node
	 * @param presetNode
	 * @return {?HTMLInputElement}
	 */
	getPresetInput(presetNode)
	{
		return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetEditInput);
	}


	/**
	 * Enable edit preset name
	 * @param {HTMLElement} presetNode
	 */
	enableEditPresetName(presetNode)
	{
		const input = this.getPresetInput(presetNode);

		BX.addClass(presetNode, this.parent.settings.classPresetNameEdit);
		input.select();
		// noinspection SillyAssignmentJS
		input.value = BX.util.htmlspecialcharsback(input.value);
		Event.bind(input, 'input', BX.delegate(this._onPresetNameInput, this));
	}

	_onPresetNameInput(event)
	{
		const Search = this.parent.getSearch();
		const inputValue = event.currentTarget.value;
		const presetNode = BX.findParent(event.currentTarget, {className: this.parent.settings.classPreset}, true, false);
		const presetId = this.getPresetId(presetNode);
		const currentPresetId = this.getCurrentPresetId();
		const data = {ID: presetId, TITLE: inputValue};

		if (presetId === currentPresetId)
		{
			Search.updatePreset(data);
		}
	}


	/**
	 * Gets preset name node element
	 * @param {HTMLElement} presetNode
	 * @return {?HTMLElement}
	 */
	getPresetNameNode(presetNode)
	{
		return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetName);
	}


	/**
	 * Disable edit name for preset
	 * @param {HTMLElement} presetNode
	 */
	disableEditPresetName(presetNode)
	{
		const input = this.getPresetInput(presetNode);

		Dom.removeClass(presetNode, this.parent.settings.classPresetNameEdit);

		if (BX.type.isDomNode(input))
		{
			input.blur();
			BX.unbind(input, 'input', BX.delegate(this._onPresetNameInput, this));
		}
	}


	/**
	 * Gets preset object
	 * @param {string} presetId
	 * @param {boolean} [isDefault = false] - gets from default presets collection
	 * @return {?object}
	 */
	getPreset(presetId, isDefault)
	{
		let presets = this.parent.getParam(isDefault ? 'DEFAULT_PRESETS' : 'PRESETS', []);

		if (this.parent.isEditEnabled() && !isDefault)
		{
			presets = this.parent.editablePresets;
		}

		const filtered = presets.filter((current) => {
			return current.ID === presetId;
		});

		if (presetId === 'tmp_filter' && !filtered.length)
		{
			const tmpPreset = BX.clone(this.getPreset('default_filter'));
			tmpPreset.ID = 'tmp_filter';
			presets.push(tmpPreset);
			filtered.push(tmpPreset);
		}

		return filtered.length !== 0 ? filtered[0] : null;
	}


	/**
	 * Gets preset field by preset name (id)
	 * @param {string} presetId
	 * @param {string} fieldName
	 * @return {?object}
	 */
	getPresetField(presetId, fieldName)
	{
		const preset = this.getPreset(presetId);
		let field = null;

		if (BX.type.isPlainObject(preset) && 'FIELDS' in preset && BX.type.isArray(preset.FIELDS))
		{
			field = preset.FIELDS.filter((current) => {
				return current.NAME === fieldName;
			});

			field = field.length ? field[0] : null;
		}

		return field;
	}


	/**
	 * Applies preset by id
	 * @param {string} presetId
	 * @param {boolean} [noValues = false]
	 */
	applyPreset(presetId, noValues)
	{
		presetId = noValues ? 'default_filter' : presetId || 'default_filter';

		let preset = this.getPreset(presetId);

		if (presetId !== 'default_preset')
		{
			preset = this.extendPreset(preset);
		}

		this.parent.getSearch().updatePreset(preset);
		this.updatePresetFields(preset, noValues);

		BX.onCustomEvent('BX.Main.Filter:onApplyPreset', [presetId]);
	}


	/**
	 * Extends preset
	 * @param {object} preset
	 * @return {object}
	 */
	extendPreset(preset)
	{
		const defaultPreset = BX.clone(this.getPreset('default_filter'));

		if (BX.type.isPlainObject(preset))
		{
			preset = BX.clone(preset);
			preset.FIELDS.forEach(function(curr) {
				let index;
				const someField = defaultPreset.FIELDS.some((defCurr, defIndex) => {
					let result = false;

					if (defCurr.NAME === curr.NAME)
					{
						index = defIndex;
						result = true;
					}

					return result;
				}, this);

				if (someField && index || someField && index === 0)
				{
					defaultPreset.FIELDS[index] = curr;
				}
				else
				if (!this.isEmptyField(curr))
				{
					defaultPreset.FIELDS.push(curr);
				}
			}, this);

			preset.FIELDS = defaultPreset.FIELDS;
		}

		return preset;
	}


	/**
	 * Checks field is empty
	 * @param {object} field
	 * @return {boolean}
	 */
	isEmptyField(field)
	{
		let result = true;

		if (Type.isStringFilled(field.ADDITIONAL_FILTER))
		{
			return false;
		}

		if (field.TYPE === this.parent.types.STRING)
		{
			if (field.VALUE && field.VALUE.length)
			{
				result = false;
			}
		}

		if (field.TYPE === this.parent.types.SELECT)
		{
			if (BX.type.isPlainObject(field.VALUE) && 'VALUE' in field.VALUE && field.VALUE.VALUE)
			{
				result = false;
			}
		}

		if (field.TYPE === this.parent.types.MULTI_SELECT)
		{
			if (BX.type.isArray(field.VALUE) && field.VALUE.length)
			{
				result = false;
			}
		}

		if (field.TYPE === this.parent.types.CUSTOM_DATE)
		{
			if (
				(BX.type.isArray(field.VALUE.days) && field.VALUE.days.length)
				|| (BX.type.isArray(field.VALUE.months) && field.VALUE.months.length)
				|| (BX.type.isArray(field.VALUE.years) && field.VALUE.years.length)
			)
			{
				result = false;
			}
		}

		if (
			field.TYPE === this.parent.types.CUSTOM_ENTITY
			|| field.TYPE === this.parent.types.DEST_SELECTOR
			|| field.TYPE === this.parent.types.ENTITY_SELECTOR
		)
		{
			if (BX.type.isPlainObject(field.VALUES))
			{
				if (BX.type.isNotEmptyString(field.VALUES._label) && BX.type.isNotEmptyString(field.VALUES._value))
				{
					result = false;
				}

				if (BX.type.isPlainObject(field.VALUES._label)
					&& BX.type.isPlainObject(field.VALUES._value)
					&& Object.keys(field.VALUES._label).length
					&& Object.keys(field.VALUES._value).length)
				{
					result = false;
				}

				if (BX.type.isArray(field.VALUES._label)
					&& BX.type.isArray(field.VALUES._value)
					&& field.VALUES._label.length
					&& field.VALUES._value.length)
				{
					result = false;
				}

				if (
					(
						(BX.type.isArray(field.VALUES._label) && field.VALUES._label.length)
						|| (BX.type.isPlainObject(field.VALUES._label) && Object.keys(field.VALUES._label).length)
					)
					&& (
						(BX.type.isArray(field.VALUES._value) && field.VALUES._value.length)
						|| (BX.type.isPlainObject(field.VALUES._value) && Object.keys(field.VALUES._value).length)
					)
				)
				{
					result = false;
				}
			}
		}

		if (field.TYPE === this.parent.types.DATE)
		{
			const datesel = '_datesel' in field.VALUES ? field.VALUES._datesel : field.SUB_TYPE.VALUE;

			if (BX.type.isPlainObject(field.VALUES)
				&& (field.VALUES._from || field.VALUES._to || field.VALUES._quarter
				|| (field.VALUES._month && !BX.type.isArray(field.VALUES._month))
				|| (field.VALUES._year && !BX.type.isArray(field.VALUES._year))
				|| (field.VALUES._days) && !BX.type.isArray(field.VALUES._days))
				|| (BX.type.isArray(field.VALUES._days) && field.VALUES._days.length)
				|| (BX.type.isArray(field.VALUES._month) && field.VALUES._month.length)
				|| (BX.type.isArray(field.VALUES._year) && field.VALUES._year.length)
				|| (
					datesel === this.parent.dateTypes.CURRENT_DAY
					|| datesel === this.parent.dateTypes.CURRENT_WEEK
					|| datesel === this.parent.dateTypes.CURRENT_MONTH
					|| datesel === this.parent.dateTypes.CURRENT_QUARTER
					|| datesel === this.parent.dateTypes.LAST_7_DAYS
					|| datesel === this.parent.dateTypes.LAST_30_DAYS
					|| datesel === this.parent.dateTypes.LAST_60_DAYS
					|| datesel === this.parent.dateTypes.LAST_90_DAYS
					|| datesel === this.parent.dateTypes.LAST_WEEK
					|| datesel === this.parent.dateTypes.LAST_MONTH
					|| datesel === this.parent.dateTypes.TOMORROW
					|| datesel === this.parent.dateTypes.YESTERDAY
					|| datesel === this.parent.dateTypes.NEXT_WEEK
					|| datesel === this.parent.dateTypes.NEXT_MONTH
				)
			)
			{
				result = false;
			}
		}

		if (field.TYPE === this.parent.types.NUMBER)
		{
			if (BX.type.isPlainObject(field.VALUES) && (field.VALUES._from || field.VALUES._to))
			{
				result = false;
			}
		}

		if (field.TYPE === this.parent.types.CHECKBOX)
		{
			if (BX.type.isPlainObject(field.VALUE) && field.VALUE.VALUE)
			{
				result = false;
			}
		}

		return result;
	}


	/**
	 * Resets preset
	 * @param {boolean} [noValues]
	 */
	resetPreset(noValues)
	{
		this.applyPreset('', noValues);
	}


	/**
	 * Gets preset fields elements
	 * @return {?HTMLElement[]}
	 */
	getFields()
	{
		const container = this.parent.getFieldListContainer();
		let fields = null;

		if (BX.type.isDomNode(container))
		{
			fields = BX.Filter.Utils.getBySelector(container.parentNode, `.${this.parent.settings.classFileldControlList} > div`, true);
		}

		return fields;
	}


	/**
	 * Gets field element by field object
	 * @param {object} fieldData
	 * @return {?HTMLElement}
	 */
	getField(fieldData)
	{
		const fields = this.getFields();
		let field = null;
		let tmpName; let
			filtered;

		if (BX.type.isArray(fields) && fields.length)
		{
			filtered = fields.filter((current) => {
				if (BX.type.isDomNode(current))
				{
					tmpName = BX.data(current, 'name');
				}
				return tmpName === fieldData.NAME;
			}, this);

			field = filtered.length > 0 ? filtered[0] : null;
		}

		return field;
	}


	/**
	 * Removes field element by field object
	 * @param {object} field
	 * @param {boolean} disableSaveFieldsSort
	 */
	removeField(field, disableSaveFieldsSort)
	{
		let index; let
			fieldName;
		disableSaveFieldsSort = disableSaveFieldsSort || false;

		if (BX.type.isPlainObject(field))
		{
			fieldName = field.NAME;
			field = this.getField(field);

			if (BX.type.isArray(this.parent.fieldsList))
			{
				index = this.parent.fieldsList.indexOf(field);

				if (index !== -1)
				{
					delete this.parent.fieldsList[index];
				}
			}
			this.parent.unregisterDragItem(field);
		}

		if (BX.type.isDomNode(field))
		{
			fieldName = BX.data(field, 'name');
			this.parent.getFields().deleteField(field);
		}

		if (!this.parent.isEditEnabled() && !this.parent.isAddPresetEnabled())
		{
			const currentPresetId = this.getCurrentPresetId();
			const currentPresetField = this.getPresetField(currentPresetId, fieldName);

			if (currentPresetField && !this.isEmptyField(currentPresetField))
			{
				this.deactivateAllPresets();
				this.parent.applyFilter();
			}
		}

		if (!disableSaveFieldsSort)
		{
			this.parent.saveFieldsSort();
		}
	}

	/**
	 * Removes field elements by field objects.
	 * @param {object[]} fields
	 */
	removeFields(fields)
	{
		fields.forEach(function(field) {
			this.removeField(field, true);
		}, this);

		this.parent.saveFieldsSort();
	}

	/**
	 * Adds field into filter field list by field object
	 * @param {object} fieldData
	 */
	addField(fieldData)
	{
		let container; let control; let
			controls;

		if (BX.type.isPlainObject(fieldData))
		{
			container = this.parent.getFieldListContainer();
			controls = this.parent.getControls();
			control = BX.type.isArray(controls) ? controls[controls.length - 1] : null;

			if (BX.type.isDomNode(control))
			{
				if (control.nodeName !== 'INPUT')
				{
					control = BX.Filter.Utils.getByTag(control, 'input');
				}

				if (BX.type.isDomNode(control))
				{
					fieldData.TABINDEX = parseInt(control.getAttribute('tabindex')) + 1;
				}
			}
			else
			{
				fieldData.TABINDEX = 2;
			}

			if (BX.type.isDomNode(container))
			{
				control = this.createControl(fieldData);

				if (BX.type.isDomNode(control))
				{
					BX.append(control, container);
					if (BX.type.isArray(this.parent.fieldsList))
					{
						this.parent.fieldsList.push(control);
					}

					this.parent.registerDragItem(control);
				}
			}
		}

		if (!this.parent.isEditEnabled() && !this.parent.isAddPresetEnabled())
		{
			const currentPresetId = this.getCurrentPresetId();
			const currentPresetField = this.getPresetField(currentPresetId, fieldData.NAME);

			if (currentPresetField && !this.isEmptyField(currentPresetField))
			{
				this.parent.updatePreset('tmp_filter');
				this.deactivateAllPresets();
				this.parent.getSearch().updatePreset(this.getPreset('tmp_filter'));
			}
		}

		this.parent.saveFieldsSort();
	}


	/**
	 * Creates field control by field object
	 * @param {object} fieldData
	 * @return {?HTMLElement}
	 */
	createControl(fieldData)
	{
		let control;

		switch (fieldData.TYPE)
		{
			case this.parent.types.STRING: {
				control = this.parent.getFields().createInputText(fieldData);
				break;
			}

			case this.parent.types.TEXTAREA: {
				control = this.parent.getFields().createTextarea(fieldData);
				break;
			}

			case this.parent.types.SELECT: {
				control = this.parent.getFields().createSelect(fieldData);
				break;
			}

			case this.parent.types.MULTI_SELECT: {
				control = this.parent.getFields().createMultiSelect(fieldData);
				break;
			}

			case this.parent.types.NUMBER: {
				control = this.parent.getFields().createNumber(fieldData);
				break;
			}

			case this.parent.types.DATE: {
				control = this.parent.getFields().createDate(fieldData);
				break;
			}

			case this.parent.types.CUSTOM_DATE: {
				control = this.parent.getFields().createCustomDate(fieldData);
				break;
			}

			case this.parent.types.DEST_SELECTOR: {
				control = this.parent.getFields().createDestSelector(fieldData);
				break;
			}

			case this.parent.types.ENTITY_SELECTOR: {
				control = this.parent.getFields().createEntitySelector(fieldData);
				break;
			}

			case this.parent.types.CUSTOM: {
				control = this.parent.getFields().createCustom(fieldData);
				break;
			}

			case this.parent.types.CUSTOM_ENTITY: {
				control = this.parent.getFields().createCustomEntity(fieldData);
				break;
			}

			default: {
				break;
			}
		}

		if (this.parent.getParam('ENABLE_ADDITIONAL_FILTERS'))
		{
			const additionalFilterInstance = AdditionalFilter.getInstance();
			const button = additionalFilterInstance.getAdditionalFilterButton({
				fieldId: fieldData.NAME,
				enabled: fieldData.ADDITIONAL_FILTER_ALLOWED,
			});
			Dom.append(button, control);
			if (!fieldData.ADDITIONAL_FILTER_ALLOWED)
			{
				BX.Dom.addClass(control, 'main-ui-filter-additional-filters-hide');
			}

			if (Type.isStringFilled(fieldData.ADDITIONAL_FILTER))
			{
				additionalFilterInstance.initAdditionalFilter(control, fieldData.ADDITIONAL_FILTER);
			}
		}

		if (BX.type.isDomNode(control))
		{
			control.dataset.name = fieldData.NAME;
			control.FieldController = new BX.Filter.FieldController(control, this.parent);

			if (fieldData.REQUIRED)
			{
				const removeButton = control.querySelector('.main-ui-filter-field-delete');

				if (removeButton)
				{
					BX.remove(removeButton);
				}
			}
		}

		return control;
	}


	/**
	 * Removes not compared properties
	 * @param {object} fields
	 * @param {boolean} [noClean]
	 */
	removeNotCompareVariables(fields, noClean)
	{
		if (BX.type.isPlainObject(fields))
		{
			const dateType = this.parent.dateTypes;
			const {additionalDateTypes} = this.parent;

			if ('FIND' in fields)
			{
				delete fields.FIND;
			}

			if (!noClean)
			{
				Object.keys(fields).forEach(function(key) {
					if (key.indexOf('_numsel') !== -1)
					{
						delete fields[key];
					}

					if (key.indexOf('_datesel') !== -1)
					{
						const datesel = fields[key];

						if (datesel === dateType.EXACT
							|| datesel === dateType.RANGE
							|| datesel === additionalDateTypes.PREV_DAY
							|| datesel === additionalDateTypes.NEXT_DAY
							|| datesel === additionalDateTypes.MORE_THAN_DAYS_AGO
							|| datesel === additionalDateTypes.AFTER_DAYS
							|| datesel === dateType.PREV_DAYS
							|| datesel === dateType.NEXT_DAYS
							|| datesel === dateType.YEAR
							|| datesel === dateType.MONTH
							|| datesel === dateType.QUARTER
							|| datesel === dateType.NONE
							|| datesel === dateType.CUSTOM_DATE)
						{
							delete fields[key];
						}
					}

					const field = this.parent.getFieldByName(key);

					if (fields[key] === '' && (!field || !field.STRICT))
					{
						delete fields[key];
					}
				}, this);
			}
		}
	}


	/**
	 * Checks is modified preset field values
	 * @param {string} presetId
	 * @returns {boolean}
	 */
	isPresetValuesModified(presetId)
	{
		const currentPresetData = this.getPreset(presetId);
		const presetFields = this.parent.preparePresetSettingsFields(currentPresetData.FIELDS);
		const currentFields = this.parent.getFilterFieldsValues();

		this.removeNotCompareVariables(presetFields);
		this.removeNotCompareVariables(currentFields);

		const comparedPresetFields = BX.Filter.Utils.sortObject(presetFields);
		const comparedCurrentFields = BX.Filter.Utils.sortObject(currentFields);

		return !Object.keys(comparedPresetFields).every((key) => {
			return (
				comparedPresetFields[key] === comparedCurrentFields[key]
				|| ((BX.type.isPlainObject(comparedPresetFields[key]) || BX.type.isArray(comparedPresetFields[key]))
				 && BX.Filter.Utils.objectsIsEquals(comparedPresetFields[key], comparedCurrentFields[key]))
			);
		});
	}


	/**
	 * Gets additional preset values
	 * @param {string} presetId
	 * @return {?object}
	 */
	getAdditionalValues(presetId)
	{
		const currentPresetData = this.getPreset(presetId);
		const notEmptyFields = currentPresetData.FIELDS.filter(function(field) {
			return !this.isEmptyField(field);
		}, this);
		const presetFields = this.parent.preparePresetSettingsFields(notEmptyFields);
		const currentFields = this.parent.getFilterFieldsValues();

		this.removeNotCompareVariables(presetFields, true);
		this.removeNotCompareVariables(currentFields, true);

		this.removeSameProperties(currentFields, presetFields);

		return currentFields;
	}


	/**
	 * Removes same object properties
	 * @param {object} object1
	 * @param {object} object2
	 */
	removeSameProperties(object1, object2)
	{
		if (BX.type.isPlainObject(object1) && BX.type.isPlainObject(object2))
		{
			Object.keys(object2).forEach((key) => {
				if (key in object1)
				{
					delete object1[key];
				}
			});
		}
	}


	/**
	 * Removes additional field by field name
	 * @param {string} name
	 */
	removeAdditionalField(name)
	{
		const preset = this.getPreset(this.getCurrentPresetId());

		if (BX.type.isArray(preset.ADDITIONAL))
		{
			preset.ADDITIONAL = preset.ADDITIONAL.filter((field) => {
				return field.NAME !== name;
			});
		}
	}


	/**
	 * Updates preset fields list
	 * @param {object} preset
	 * @param {boolean} [noValues = false]
	 */
	updatePresetFields(preset, noValues)
	{
		let fields; let
			fieldListContainer;
		const fieldNodes = [];

		if (BX.type.isPlainObject(preset) && ('FIELDS' in preset))
		{
			fields = preset.FIELDS;

			if (BX.type.isArray(preset.ADDITIONAL))
			{
				preset.ADDITIONAL
					.filter((field) => {
						return this.parent.params.FIELDS.some((currentField) => {
							return field.NAME === currentField.NAME;
						});
					})
					.forEach((field) => {
						let replaced = false;
						field.IS_PRESET_FIELD = true;
						fields.forEach((presetField, index) => {
							if (field.NAME === presetField.NAME)
							{
								fields[index] = field;
								replaced = true;
							}
						});

						if (!replaced)
						{
							fields.push(field);
						}
					});
			}

			(fields || [])
				.filter((field) => {
					return this.parent.params.FIELDS.some((currentField) => {
						return field.NAME === currentField.NAME;
					});
				})
				.forEach(function(fieldData, index) {
					fieldData.TABINDEX = index + 1;
					if (noValues)
					{
						switch (fieldData.TYPE)
						{
							case this.parent.types.SELECT: {
								fieldData.VALUE = fieldData.ITEMS[0];
								break;
							}

							case this.parent.types.MULTI_SELECT: {
								fieldData.VALUE = [];
								break;
							}

							case this.parent.types.DATE: {
								fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
								fieldData.VALUES = {
									_from: '',
									_to: '',
									_days: '',
								};
								break;
							}

							case this.parent.types.CUSTOM_DATE: {
								fieldData.VALUE = {
									days: [],
									months: [],
									years: [],
								};
								break;
							}

							case this.parent.types.NUMBER: {
								fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
								fieldData.VALUES = {
									_from: '',
									_to: '',
								};
								break;
							}

							case this.parent.types.CUSTOM_ENTITY: {
								fieldData.VALUES = {
									_label: '',
									_value: '',
								};
								break;
							}

							case this.parent.types.CUSTOM: {
								fieldData._VALUE = '';
								break;
							}

							default: {
								if ('VALUE' in fieldData)
								{
									if (BX.type.isArray(fieldData.VALUE))
									{
										fieldData.VALUE = [];
									}
									else
									{
										fieldData.VALUE = '';
									}
								}
								break;
							}
						}
					}

					fieldNodes.push(this.createControl(fieldData));
				}, this);

			this.parent.disableFieldsDragAndDrop();
			fieldListContainer = this.parent.getFieldListContainer();
			BX.cleanNode(fieldListContainer);

			if (fieldNodes.length)
			{
				fieldNodes.forEach(function(current, index) {
					if (BX.type.isDomNode(current))
					{
						if (preset.ID !== 'tmp_filter'
							&& preset.ID !== 'default_filter'
							&& !('IS_PRESET_FIELD' in fields[index])
							&& !this.isEmptyField(fields[index]))
						{
							BX.addClass(current, this.parent.settings.classPresetField);
						}

						BX.append(current, fieldListContainer);

						if (BX.type.isString(fields[index].HTML))
						{
							const wrap = BX.create('div');
							this.parent.getHiddenElement().appendChild(wrap);
							BX.html(wrap, fields[index].HTML);
						}
					}
				}, this);

				this.parent.enableFieldsDragAndDrop();
			}
		}
	}


	/**
	 * Shows current preset fields
	 */
	showCurrentPresetFields()
	{
		const preset = this.getCurrentPresetData();
		this.updatePresetFields(preset);
	}


	/**
	 * Gets current preset element
	 * @return {?HTMLElement}
	 */
	getCurrentPreset()
	{
		return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPresetCurrent);
	}


	/**
	 * Gets current preset id
	 * @return {*}
	 */
	getCurrentPresetId()
	{
		const current = this.getCurrentPreset();
		let currentId = null;

		if (BX.type.isDomNode(current))
		{
			currentId = this.getPresetId(current);
		}
		else
		{
			currentId = 'tmp_filter';
		}

		return currentId;
	}


	/**
	 * Gets current preset data
	 * @return {?object}
	 */
	getCurrentPresetData()
	{
		const currentId = this.getCurrentPresetId();
		let currentData = null;

		if (BX.type.isNotEmptyString(currentId))
		{
			currentData = this.getPreset(currentId);
			currentData = this.extendPreset(currentData);
		}

		return currentData;
	}


	/**
	 * Gets presets container element
	 * @return {?HTMLElement}
	 */
	getContainer()
	{
		return BX.Filter.Utils.getByClass(this.parent.getFilter(), this.parent.settings.classPresetsContainer);
	}


	/**
	 * Gets preset nodes
	 * @return {?HTMLElement[]}
	 */
	getPresets()
	{
		return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPreset, true);
	}


	/**
	 * Gets default presets elements
	 * @return {?HTMLElement[]}
	 */
	getDefaultPresets()
	{
		return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classDefaultFilter, true);
	}


	/**
	 * Gets default preset element
	 * @return {?HTMLElement}
	 */
	getPinnedPresetNode()
	{
		return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPinnedPreset);
	}


	/**
	 * Checks preset is pinned (default)
	 * @param presetId
	 * @return {boolean}
	 */
	isPinned(presetId)
	{
		return this.getPinnedPresetId() === presetId;
	}


	/**
	 * Gets pinned (default) preset id
	 * @return {string}
	 */
	getPinnedPresetId()
	{
		const node = this.getPinnedPresetNode();
		let id = 'default_filter';

		if (node)
		{
			const dataId = BX.data(node, 'id');
			id = dataId || id;
		}

		return id;
	}
}