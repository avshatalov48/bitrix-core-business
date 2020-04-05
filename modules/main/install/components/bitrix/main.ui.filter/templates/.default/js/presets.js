;(function() {
	'use strict';

	BX.namespace('BX.Filter');


	/**
	 * Filter presets class
	 * @param parent
	 * @constructor
	 */
	BX.Filter.Presets = function(parent)
	{
		this.parent = null;
		this.presets = null;
		this.container = null;
		this.init(parent);
	};

	BX.Filter.Presets.prototype = {
		init: function(parent)
		{
			this.parent = parent;
		},

		bindOnPresetClick: function()
		{
			(this.getPresets() || []).forEach(function(current) {
				BX.bind(current, 'click', BX.delegate(this._onPresetClick, this));
			}, this);
		},

		/**
		 * Gets add preset field
		 * @return {?HTMLElement}
		 */
		getAddPresetField: function()
		{
			return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classAddPresetField);
		},


		/**
		 * Gets add preset name input
		 * @return {?HTMLInputElement}
		 */
		getAddPresetFieldInput: function()
		{
			return BX.Filter.Utils.getByClass(this.getAddPresetField(), this.parent.settings.classAddPresetFieldInput);
		},


		/**
		 * Clears add preset input value
		 */
		clearAddPresetFieldInput: function()
		{
			var input = this.getAddPresetFieldInput();
			input && (input.value = '');
		},


		/**
		 * Finds preset node by child node
		 * @param {?HTMLElement} node
		 * @return {?HTMLElement}
		 */
		normalizePreset: function(node)
		{
			if (!BX.hasClass(node, this.parent.settings.classPreset))
			{
				node = BX.findParent(node, {className: this.parent.settings.classPreset}, true, false);
			}

			return node;
		},


		/**
		 * Deactivates all presets
		 */
		deactivateAllPresets: function()
		{
			var presets = this.getPresets();
			var self = this;

			(presets || []).forEach(function(current) {
				if (BX.hasClass(current, self.parent.settings.classPresetCurrent))
				{
					BX.removeClass(current, self.parent.settings.classPresetCurrent)
				}
			});
		},

		/**
		 * Creates sidebar preset item
		 * @param {string} id - Preset id
		 * @param {string} title - Preset title
		 * @param {boolean} [isPinned] - Pass true is preset pinned
		 */
		createSidebarItem: function(id, title, isPinned)
		{
			return BX.decl({
				block: 'sidebar-item',
				text: BX.util.htmlspecialcharsback(title),
				id: id,
				pinned: isPinned,
				noEditPinTitle: this.parent.getParam('MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET'),
				editNameTitle: this.parent.getParam('MAIN_UI_FILTER__EDIT_PRESET_TITLE'),
				removeTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_PRESET'),
				editPinTitle: this.parent.getParam('MAIN_UI_FILTER__SET_AS_DEFAULT_PRESET'),
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_TITLE')
			});
		},


		/**
		 * Highlights preset node as active
		 * @param {?HTMLElement|string} preset - preset node or preset id
		 */
		activatePreset: function(preset)
		{
			this.deactivateAllPresets();

			if (BX.type.isNotEmptyString(preset))
			{
				preset = this.getPresetNodeById(preset);
			}

			if (preset && !BX.hasClass(preset, this.parent.settings.classPresetCurrent))
			{
				BX.addClass(preset, this.parent.settings.classPresetCurrent);
			}
		},


		/**
		 * Gets preset node by preset id
		 * @param {string} id
		 * @return {?HTMLElement}
		 */
		getPresetNodeById: function(id)
		{
			var presets = this.getPresets();
			var result = presets.filter(function(current) {
				return BX.data(current, 'id') === id;
			}, this);

			return result.length > 0 ? result[0] : null;
		},


		/**
		 * Gets preset id by preset node
		 * @param {?HTMLElement} preset
		 */
		getPresetId: function(preset)
		{
			return BX.data(preset, 'id');
		},


		/**
		 * Updates preset name
		 * @param {?HTMLElement} presetNode
		 * @param {string} name
		 */
		updatePresetName: function(presetNode, name)
		{
			var nameNode;

			if (BX.type.isDomNode(presetNode) && BX.type.isNotEmptyString(name))
			{
				nameNode = this.getPresetNameNode(presetNode);

				if (BX.type.isDomNode(nameNode))
				{
					BX.html(nameNode, name);
				}
			}
		},


		/**
		 * Removes preset
		 * @param {HTMLElement} presetNode
		 * @param {string} presetId
		 * @param {boolean} isDefault
		 */
		removePreset: function(presetNode, presetId, isDefault)
		{
			var currentPresetId = this.getCurrentPresetId();
			var newPresets = [];
			var postData = {
				'preset_id': presetId,
				'is_default': isDefault
			};

			var getData = {
				'FILTER_ID': this.parent.getParam('FILTER_ID'),
				'action': 'REMOVE_FILTER'
			};

			this.parent.saveOptions(postData, getData);
			BX.remove(presetNode);

			if (BX.type.isArray(this.parent.params['PRESETS']))
			{
				newPresets = this.parent.params['PRESETS'].filter(function(current) {
					return current.ID !== presetId;
				}, this);

				this.parent.params['PRESETS'] = newPresets;
			}

			if (BX.type.isArray(this.parent.editablePresets))
			{
				newPresets = this.parent.editablePresets.filter(function(current) {
					return current.ID !== presetId;
				}, this);

				this.parent.editablePresets = newPresets;
			}

			if (presetId === currentPresetId)
			{
				this.parent.getSearch().removePreset();
				this.resetPreset();
			}
		},


		/**
		 * Pin preset (Sets as default preset)
		 * @param {string} presetId
		 */
		pinPreset: function(presetId)
		{
			if (!BX.type.isNotEmptyString(presetId))
			{
				presetId = 'default_filter';
			}

			var presetNode = this.getPresetNodeById(presetId);

			if (this.parent.getParam('VALUE_REQUIRED_MODE'))
			{
				if (presetId === 'default_filter')
				{
					return;
				}
			}

			var params = {'FILTER_ID': this.parent.getParam('FILTER_ID'), 'GRID_ID': this.parent.getParam('GRID_ID'), 'action': 'PIN_PRESET'};
			var data = {preset_id: presetId};

			this.getPresets().forEach(function(current) {
				BX.removeClass(current, this.parent.settings.classPinnedPreset);
			}, this);

			BX.addClass(presetNode, this.parent.settings.classPinnedPreset);

			this.parent.saveOptions(data, params);
		},

		_onPresetClick: function(event) {
			var presetNode, presetId, presetData, isDefault, target, settings, parent;

			event.preventDefault();

			parent = this.parent;
			settings = parent.settings;
			target = event.target;
			presetNode = event.currentTarget;
			presetId = this.getPresetId(presetNode);
			presetData = this.getPreset(presetId);

			if (BX.hasClass(target, settings.classPinButton))
			{
				if (this.parent.isEditEnabled())
				{
					if (BX.hasClass(presetNode, settings.classPinnedPreset))
					{
						this.pinPreset("default_filter");
					}
					else
					{
						this.pinPreset(presetId)
					}
				}
			}

			if (BX.hasClass(target, settings.classPresetEditButton))
			{
				this.enableEditPresetName(presetNode);
			}

			if (BX.hasClass(target, settings.classPresetDeleteButton))
			{
				isDefault = 'IS_DEFAULT' in presetData ? presetData.IS_DEFAULT : false;
				this.removePreset(presetNode, presetId, isDefault);
				return false;
			}

			if (!BX.hasClass(target, settings.classPresetDragButton) &&
				!BX.hasClass(target, settings.classAddPresetFieldInput))
			{
				if (this.parent.isEditEnabled())
				{
					this.updateEditablePreset(this.getCurrentPresetId());
				}

				var currentPreset = this.getPreset(this.getCurrentPresetId());
				var preset = this.getPreset(presetId);
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
		},


		/**
		 * Applies default preset
		 * @return {BX.Promise}
		 */
		applyPinnedPreset: function()
		{
			var Filter = this.parent;
			var isPinned = this.isPinned(this.getCurrentPresetId());
			var promise;

			if (this.parent.getParam('VALUE_REQUIRED') &&
				this.getPinnedPresetId() === 'default_filter')
			{
				this.applyPreset('default_filter');
				this.deactivateAllPresets();
				promise = this.parent.applyFilter();
			}
			else
			{
				if (!isPinned)
				{
					var pinnedPresetId = this.getPinnedPresetId();
					var pinnedPresetNode = this.getPinnedPresetNode();
					var clear = false;
					var applyPreset = true;

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
			}



			return promise;
		},


		/**
		 * Updates editable presets
		 * @param {string} presetId
		 */
		updateEditablePreset: function(presetId)
		{
			var fields = this.parent.getFilterFieldsValues();
			var presetRows = this.getFields().map(function(curr) { return BX.data(curr, 'name'); });
			var presetFields = this.parent.preparePresetFields(fields, presetRows);
			var preset = this.getPreset(presetId);

			preset.FIELDS = presetFields;
			preset.TITLE = this.getPresetInput(this.getPresetNodeById(presetId)).value;
			preset.ROWS = presetRows;
		},


		/**
		 * Gets preset input node
		 * @param presetNode
		 * @return {?HTMLInputElement}
		 */
		getPresetInput: function(presetNode)
		{
			return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetEditInput);
		},


		/**
		 * Enable edit preset name
		 * @param {HTMLElement} presetNode
		 */
		enableEditPresetName: function(presetNode)
		{
			var input = this.getPresetInput(presetNode);

			BX.addClass(presetNode, this.parent.settings.classPresetNameEdit);
			input.focus();
			//noinspection SillyAssignmentJS
			input.value = BX.util.htmlspecialcharsback(input.value);
			BX.bind(input, 'input', BX.delegate(this._onPresetNameInput, this));
		},

		_onPresetNameInput: function(event)
		{
			var Search = this.parent.getSearch();
			var inputValue = event.currentTarget.value;
			var presetNode = BX.findParent(event.currentTarget, {className: this.parent.settings.classPreset}, true, false);
			var presetId = this.getPresetId(presetNode);
			var currentPresetId = this.getCurrentPresetId();
			var data = {ID: presetId, TITLE: inputValue};

			if (presetId === currentPresetId)
			{
				Search.updatePreset(data);
			}
		},


		/**
		 * Gets preset name node element
		 * @param {HTMLElement} presetNode
		 * @return {?HTMLElement}
		 */
		getPresetNameNode: function(presetNode)
		{
			return BX.Filter.Utils.getByClass(presetNode, this.parent.settings.classPresetName);
		},


		/**
		 * Disable edit name for preset
		 * @param {HTMLElement} presetNode
		 */
		disableEditPresetName: function(presetNode)
		{
			var input = this.getPresetInput(presetNode);

			BX.removeClass(presetNode, this.parent.settings.classPresetNameEdit);

			if (BX.type.isDomNode(input))
			{
				input.blur();
				BX.unbind(input, 'input', BX.delegate(this._onPresetNameInput, this));
			}
		},


		/**
		 * Gets preset object
		 * @param {string} presetId
		 * @param {boolean} [isDefault = false] - gets from default presets collection
 		 * @return {?object}
		 */
		getPreset: function(presetId, isDefault)
		{
			var presets = this.parent.getParam(isDefault ? 'DEFAULT_PRESETS' : 'PRESETS', []);

			if (this.parent.isEditEnabled() && !isDefault)
			{
				presets = this.parent.editablePresets;
			}

			var filtered = presets.filter(function(current) {
				return current.ID === presetId;
			});

			if (presetId === 'tmp_filter' && !filtered.length)
			{
				var tmpPreset = BX.clone(this.getPreset('default_filter'));
				tmpPreset.ID = 'tmp_filter';
				presets.push(tmpPreset);
				filtered.push(tmpPreset);
			}

			return filtered.length !== 0 ? filtered[0] : null;
		},


		/**
		 * Gets preset field by preset name (id)
		 * @param {string} presetId
		 * @param {string} fieldName
		 * @return {?object}
		 */
		getPresetField: function(presetId, fieldName)
		{
			var preset = this.getPreset(presetId);
			var field = null;

			if (BX.type.isPlainObject(preset) && 'FIELDS' in preset && BX.type.isArray(preset.FIELDS))
			{
				field = preset.FIELDS.filter(function(current) {
					return current.NAME === fieldName;
				});

				field = field.length ? field[0] : null;
			}

			return field;
		},


		/**
		 * Applies preset by id
		 * @param {string} presetId
		 * @param {boolean} [noValues = false]
		 */
		applyPreset: function(presetId, noValues)
		{
			presetId = noValues ? 'default_filter' : presetId || 'default_filter';

			var preset = this.getPreset(presetId);

			if (presetId !== 'default_preset')
			{
				preset = this.extendPreset(preset);
			}

			this.parent.getSearch().updatePreset(preset);
			this.updatePresetFields(preset, noValues);
		},


		/**
		 * Extends preset
		 * @param {object} preset
		 * @return {object}
		 */
		extendPreset: function(preset)
		{
			var defaultPreset = BX.clone(this.getPreset('default_filter'));

			if (BX.type.isPlainObject(preset))
			{
				preset = BX.clone(preset);
				preset.FIELDS.forEach(function(curr) {
					var index;
					var someField = defaultPreset.FIELDS.some(function(defCurr, defIndex) {
						var result = false;

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
					{
						if (!this.isEmptyField(curr))
						{
							defaultPreset.FIELDS.push(curr);
						}
					}
				}, this);

				preset.FIELDS = defaultPreset.FIELDS;
			}

			return preset;
		},


		/**
		 * Checks field is empty
		 * @param {object} field
		 * @return {boolean}
		 */
		isEmptyField: function(field)
		{
			var result = true;

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
					(BX.type.isArray(field.VALUE.days) && field.VALUE.days.length) ||
					(BX.type.isArray(field.VALUE.months) && field.VALUE.months.length) ||
					(BX.type.isArray(field.VALUE.years) && field.VALUE.years.length)
				)
				{
					result = false;
				}
			}

			if (
				field.TYPE === this.parent.types.CUSTOM_ENTITY
				|| field.TYPE === this.parent.types.DEST_SELECTOR
			)
			{
				if (BX.type.isPlainObject(field.VALUES))
				{
					if (BX.type.isNotEmptyString(field.VALUES._label) && BX.type.isNotEmptyString(field.VALUES._value))
					{
						result = false;
					}

					if (BX.type.isPlainObject(field.VALUES._label) &&
						BX.type.isPlainObject(field.VALUES._value) &&
						Object.keys(field.VALUES._label).length &&
						Object.keys(field.VALUES._value).length)
					{
						result = false;
					}

					if (BX.type.isArray(field.VALUES._label) &&
						BX.type.isArray(field.VALUES._value) &&
						field.VALUES._label.length &&
						field.VALUES._value.length)
					{
						result = false;
					}

					if (
						(
							(BX.type.isArray(field.VALUES._label) && field.VALUES._label.length) ||
							(BX.type.isPlainObject(field.VALUES._label) && Object.keys(field.VALUES._label).length)
						) &&
						(
							(BX.type.isArray(field.VALUES._value) && field.VALUES._value.length) ||
							(BX.type.isPlainObject(field.VALUES._value) && Object.keys(field.VALUES._value).length)
						)
					)
					{
						result = false;
					}
				}
			}

			if (field.TYPE === this.parent.types.DATE)
			{
				var datesel = '_datesel' in field.VALUES ? field.VALUES._datesel : field.SUB_TYPE.VALUE;

				if (BX.type.isPlainObject(field.VALUES) &&
					(field.VALUES._from || field.VALUES._to || field.VALUES._quarter ||
					(field.VALUES._month && !BX.type.isArray(field.VALUES._month)) ||
					(field.VALUES._year && !BX.type.isArray(field.VALUES._year)) ||
					(field.VALUES._days) && !BX.type.isArray(field.VALUES._days)) ||
					(BX.type.isArray(field.VALUES._days) && field.VALUES._days.length) ||
					(BX.type.isArray(field.VALUES._month) && field.VALUES._month.length) ||
					(BX.type.isArray(field.VALUES._year) && field.VALUES._year.length) ||
					(
						datesel === this.parent.dateTypes.CURRENT_DAY ||
						datesel === this.parent.dateTypes.CURRENT_WEEK ||
						datesel === this.parent.dateTypes.CURRENT_MONTH ||
						datesel === this.parent.dateTypes.CURRENT_QUARTER ||
						datesel === this.parent.dateTypes.LAST_7_DAYS ||
						datesel === this.parent.dateTypes.LAST_30_DAYS ||
						datesel === this.parent.dateTypes.LAST_60_DAYS ||
						datesel === this.parent.dateTypes.LAST_90_DAYS ||
						datesel === this.parent.dateTypes.LAST_WEEK ||
						datesel === this.parent.dateTypes.LAST_MONTH ||
						datesel === this.parent.dateTypes.TOMORROW ||
						datesel === this.parent.dateTypes.YESTERDAY ||
						datesel === this.parent.dateTypes.NEXT_WEEK ||
						datesel === this.parent.dateTypes.NEXT_MONTH
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
		},


		/**
		 * Resets preset
		 * @param {boolean} [noValues]
		 */
		resetPreset: function(noValues)
		{
			this.applyPreset('', noValues);
		},


		/**
		 * Gets preset fields elements
		 * @return {?HTMLElement[]}
		 */
		getFields: function()
		{
			var container = this.parent.getFieldListContainer();
			var fields = null;

			if (BX.type.isDomNode(container))
			{
				fields = BX.Filter.Utils.getBySelector(container.parentNode, '.'+this.parent.settings.classFileldControlList+' > div', true);
			}

			return fields;
		},


		/**
		 * Gets field element by field object
		 * @param {object} fieldData
		 * @return {?HTMLElement}
		 */
		getField: function(fieldData)
		{
			var fields = this.getFields();
			var field = null;
			var tmpName, filtered;

			if (BX.type.isArray(fields) && fields.length)
			{
				filtered = fields.filter(function(current) {
					if (BX.type.isDomNode(current))
					{
						tmpName = BX.data(current, 'name');
					}
					return tmpName === fieldData.NAME;
				}, this);

				field = filtered.length > 0 ? filtered[0] : null;
			}

			return field;
		},


		/**
		 * Removes field element by field object
		 * @param {object} field
		 * @param {boolean} disableSaveFieldsSort
		 */
		removeField: function(field, disableSaveFieldsSort)
		{
			var index, fieldName;
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
				var currentPresetId = this.getCurrentPresetId();
				var currentPresetField = this.getPresetField(currentPresetId, fieldName);

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
		},

		/**
		 * Removes field elements by field objects.
		 * @param {object[]} fields
		 */
		removeFields: function(fields)
		{
			fields.forEach(function (field) {
				this.removeField(field, true);
			}, this);

			this.parent.saveFieldsSort();
		},

		/**
		 * Adds field into filter field list by field object
		 * @param {object} fieldData
		 */
		addField: function(fieldData)
		{
			var container, control, controls;

			if (BX.type.isPlainObject(fieldData))
			{
				container = this.parent.getFieldListContainer();
				controls = this.parent.getControls();
				control = BX.type.isArray(controls) ? controls[controls.length-1] : null;

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
				var currentPresetId = this.getCurrentPresetId();
				var currentPresetField = this.getPresetField(currentPresetId, fieldData.NAME);

				if (currentPresetField && !this.isEmptyField(currentPresetField))
				{
					this.parent.updatePreset('tmp_filter');
					this.deactivateAllPresets();
					this.parent.getSearch().updatePreset(this.getPreset('tmp_filter'));
				}
			}

			this.parent.saveFieldsSort();
		},


		/**
		 * Creates field control by field object
		 * @param {object} fieldData
		 * @return {?HTMLElement}
		 */
		createControl: function(fieldData)
		{
			var control;

			switch (fieldData.TYPE)
			{
				case this.parent.types.STRING : {
					control = this.parent.getFields().createInputText(fieldData);
					break;
				}

				case this.parent.types.SELECT : {
					control = this.parent.getFields().createSelect(fieldData);
					break;
				}

				case this.parent.types.MULTI_SELECT : {
					control = this.parent.getFields().createMultiSelect(fieldData);
					break;
				}

				case this.parent.types.NUMBER : {
					control = this.parent.getFields().createNumber(fieldData);
					break;
				}

				case this.parent.types.DATE : {
					control = this.parent.getFields().createDate(fieldData);
					break;
				}

				case this.parent.types.CUSTOM_DATE : {
					control = this.parent.getFields().createCustomDate(fieldData);
					break;
				}

				case this.parent.types.DEST_SELECTOR : {
					control = this.parent.getFields().createDestSelector(fieldData);
					break;
				}

				case this.parent.types.CUSTOM : {
					control = this.parent.getFields().createCustom(fieldData);
					break;
				}

				case this.parent.types.CUSTOM_ENTITY : {
					control = this.parent.getFields().createCustomEntity(fieldData);
					break;
				}

				default : {
					break;
				}
			}

			if (BX.type.isDomNode(control))
			{
				control.dataset.name = fieldData.NAME;
				control.FieldController = new BX.Filter.FieldController(control, this.parent);
			}

			return control;
		},


		/**
		 * Removes not compared properties
		 * @param {object} fields
		 * @param {boolean} [noClean]
		 */
		removeNotCompareVariables: function(fields, noClean)
		{
			if (BX.type.isPlainObject(fields))
			{
				var dateType = this.parent.dateTypes;
				var additionalDateTypes = this.parent.additionalDateTypes;

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
							var datesel = fields[key];

							if (datesel === dateType.EXACT ||
								datesel === dateType.RANGE ||
								datesel === additionalDateTypes.PREV_DAY ||
								datesel === additionalDateTypes.NEXT_DAY ||
								datesel === additionalDateTypes.MORE_THAN_DAYS_AGO ||
								datesel === additionalDateTypes.AFTER_DAYS ||
								datesel === dateType.PREV_DAYS ||
								datesel === dateType.NEXT_DAYS ||
								datesel === dateType.YEAR ||
								datesel === dateType.MONTH ||
								datesel === dateType.QUARTER ||
								datesel === dateType.NONE ||
								datesel === dateType.CUSTOM_DATE)
							{
								delete fields[key];
							}
						}

						var field = this.parent.getFieldByName(key);

						if (fields[key] === '' && (!field || !field["STRICT"]))
						{
							delete fields[key];
						}
					}, this);
				}
			}
		},


		/**
		 * Checks is modified preset field values
		 * @param {string} presetId
		 * @returns {boolean}
		 */
		isPresetValuesModified: function(presetId)
		{
			var currentPresetData = this.getPreset(presetId);
			var presetFields = this.parent.preparePresetSettingsFields(currentPresetData.FIELDS);
			var currentFields = this.parent.getFilterFieldsValues();

			this.removeNotCompareVariables(presetFields);
			this.removeNotCompareVariables(currentFields);

			var comparedPresetFields = BX.Filter.Utils.sortObject(presetFields);
			var comparedCurrentFields = BX.Filter.Utils.sortObject(currentFields);

			return !Object.keys(comparedPresetFields).every(function(key) {
				return (
					comparedPresetFields[key] === comparedCurrentFields[key] ||
					((BX.type.isPlainObject(comparedPresetFields[key]) || BX.type.isArray(comparedPresetFields[key])) &&
					 BX.Filter.Utils.objectsIsEquals(comparedPresetFields[key], comparedCurrentFields[key]))
				);
			});
		},


		/**
		 * Gets additional preset values
		 * @param {string} presetId
		 * @return {?object}
		 */
		getAdditionalValues: function(presetId)
		{
			var currentPresetData = this.getPreset(presetId);
			var notEmptyFields = currentPresetData.FIELDS.filter(function(field) {
				return !this.isEmptyField(field);
			}, this);
			var presetFields = this.parent.preparePresetSettingsFields(notEmptyFields);
			var currentFields = this.parent.getFilterFieldsValues();

			this.removeNotCompareVariables(presetFields, true);
			this.removeNotCompareVariables(currentFields, true);

			this.removeSameProperties(currentFields, presetFields);

			return currentFields;
		},


		/**
		 * Removes same object properties
		 * @param {object} object1
		 * @param {object} object2
		 */
		removeSameProperties: function(object1, object2)
		{
			if (BX.type.isPlainObject(object1) && BX.type.isPlainObject(object2))
			{
				Object.keys(object2).forEach(function(key) {
					if (key in object1)
					{
						delete object1[key];
					}
				});
			}
		},


		/**
		 * Removes additional field by field name
		 * @param {string} name
		 */
		removeAdditionalField: function(name)
		{
			var preset = this.getPreset(this.getCurrentPresetId());

			if (BX.type.isArray(preset.ADDITIONAL))
			{
				preset.ADDITIONAL = preset.ADDITIONAL.filter(function(field) {
					return field.NAME !== name;
				});
			}
		},


		/**
		 * Updates preset fields list
		 * @param {object} preset
		 * @param {boolean} [noValues = false]
		 */
		updatePresetFields: function(preset, noValues)
		{
			var fields, fieldListContainer;
			var fieldNodes = [];

			if (BX.type.isPlainObject(preset) && ('FIELDS' in preset))
			{
				fields = preset.FIELDS;

				if (BX.type.isArray(preset.ADDITIONAL))
				{
					preset.ADDITIONAL.forEach(function(field) {
						var replaced = false;
						field.IS_PRESET_FIELD = true;
						fields.forEach(function(presetField, index) {
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

				(fields || []).forEach(function(fieldData, index) {
					fieldData.TABINDEX = index+1;
					if (noValues)
					{
						switch (fieldData.TYPE)
						{
							case this.parent.types.SELECT : {
								fieldData.VALUE = fieldData.ITEMS[0];
								break;
							}

							case this.parent.types.MULTI_SELECT : {
								fieldData.VALUE = [];
								break;
							}

							case this.parent.types.DATE : {
								fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
								fieldData.VALUES = {
									'_from': '',
									'_to': '',
									'_days': ''
								};
								break;
							}

							case this.parent.types.CUSTOM_DATE : {
								fieldData.VALUE = {
									'days': [],
									'months': [],
									'years': []
								};
								break;
							}

							case this.parent.types.NUMBER : {
								fieldData.SUB_TYPE = fieldData.SUB_TYPES[0];
								fieldData.VALUES = {
									'_from': '',
									'_to': ''
								};
								break;
							}

							case this.parent.types.CUSTOM_ENTITY : {
								fieldData.VALUES = {
									'_label': '',
									'_value': ''
								};
								break;
							}

							case this.parent.types.CUSTOM : {
								fieldData._VALUE = '';
								break;
							}

							default : {
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
							if (preset.ID !== 'tmp_filter' &&
								preset.ID !== 'default_filter' &&
								!('IS_PRESET_FIELD' in fields[index]) &&
								!this.isEmptyField(fields[index]))
							{
								BX.addClass(current, this.parent.settings.classPresetField);
							}

							BX.append(current, fieldListContainer);

							if (BX.type.isString(fields[index].HTML))
							{
								var wrap = BX.create("div");
								this.parent.getHiddenElement().appendChild(wrap);
								BX.html(wrap, fields[index].HTML);
							}
						}
					}, this);

					this.parent.enableFieldsDragAndDrop();
				}
			}
		},


		/**
		 * Shows current preset fields
		 */
		showCurrentPresetFields: function()
		{
			var preset = this.getCurrentPresetData();
			this.updatePresetFields(preset);
		},


		/**
		 * Gets current preset element
		 * @return {?HTMLElement}
		 */
		getCurrentPreset: function()
		{
			return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPresetCurrent);
		},


		/**
		 * Gets current preset id
		 * @return {*}
		 */
		getCurrentPresetId: function()
		{
			var current = this.getCurrentPreset();
			var currentId = null;

			if (BX.type.isDomNode(current))
			{
				currentId = this.getPresetId(current);
			}
			else
			{
				currentId = "tmp_filter";
			}

			return currentId;
		},


		/**
		 * Gets current preset data
		 * @return {?object}
		 */
		getCurrentPresetData: function()
		{
			var currentId = this.getCurrentPresetId();
			var currentData = null;

			if (BX.type.isNotEmptyString(currentId))
			{
				currentData = this.getPreset(currentId);
				currentData = this.extendPreset(currentData);
			}

			return currentData;
		},


		/**
		 * Gets presets container element
		 * @return {?HTMLElement}
		 */
		getContainer: function()
		{
			return BX.Filter.Utils.getByClass(this.parent.getFilter(), this.parent.settings.classPresetsContainer);
		},


		/**
		 * Gets preset nodes
		 * @return {?HTMLElement[]}
		 */
		getPresets: function()
		{
			return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPreset, true);
		},


		/**
		 * Gets default presets elements
		 * @return {?HTMLElement[]}
		 */
		getDefaultPresets: function()
		{
			return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classDefaultFilter, true);
		},


		/**
		 * Gets default preset element
		 * @return {?HTMLElement}
		 */
		getPinnedPresetNode: function()
		{
			return BX.Filter.Utils.getByClass(this.getContainer(), this.parent.settings.classPinnedPreset);
		},


		/**
		 * Checks preset is pinned (default)
		 * @param presetId
		 * @return {boolean}
		 */
		isPinned: function(presetId)
		{
			return this.getPinnedPresetId() === presetId;
		},


		/**
		 * Gets pinned (default) preset id
		 * @return {string}
		 */
		getPinnedPresetId: function()
		{
			var node = this.getPinnedPresetNode();
			var id = 'default_filter';

			if (!!node)
			{
				var dataId = BX.data(node, 'id');
				id = !!dataId ? dataId : id;
			}

			return id;
		}
	};
})();