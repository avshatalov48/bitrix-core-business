;(function() {
	'use strict';

	BX.namespace('BX.Main');


	/**
	 * General filter class
	 * @param {object} params Component params
	 * @param {object} options Extends BX.Filter.Settings
	 * @param {object} types Field types from Bitrix\Main\UI\Filter\Type
	 * @param types.STRING
	 * @param types.SELECT
	 * @param types.DATE
	 * @param types.CUSTOM_DATE
	 * @param types.MULTI_SELECT
	 * @param types.NUMBER
	 * @param types.DEST_SELECTOR
	 * @param types.ENTITY_SELECTOR
	 * @param types.CUSTOM_ENTITY
	 * @param types.CHECKBOX
	 * @param types.CUSTOM
	 * @param types.ENTITY
	 * @param {object} dateTypes Date field types from Bitrix\Main\UI\Filter\DateType
	 * @param dateTypes.NONE
	 * @param dateTypes.YESTERDAY
	 * @param dateTypes.CURRENT_DAY
	 * @param dateTypes.CURRENT_WEEK
	 * @param dateTypes.CURRENT_MONTH
	 * @param dateTypes.CURRENT_QUARTER
	 * @param dateTypes.LAST_7_DAYS
	 * @param dateTypes.LAST_30_DAYS
	 * @param dateTypes.LAST_60_DAYS
	 * @param dateTypes.LAST_90_DAYS
	 * @param dateTypes.MONTH
	 * @param dateTypes.QUARTER
	 * @param dateTypes.YEAR
	 * @param dateTypes.EXACT
	 * @param dateTypes.LAST_WEEK
	 * @param dateTypes.LAST_MONTH
	 * @param dateTypes.RANGE
	 * @param dateTypes.NEXT_DAYS
	 * @param dateTypes.PREV_DAYS
	 * @param dateTypes.TOMORROW
	 * @param dateTypes.NEXT_MONTH
	 * @param dateTypes.NEXT_WEEK
	 * @param {object} numberTypes Number field types from Bitrix\Main\UI\Filter\NumberType
	 * @memberOf {BX.Main}
	 */
	BX.Main.Filter = function(params, options, types, dateTypes, numberTypes, additionalDateTypes, additionalNumberTypes)
	{
		this.params = params;
		this.search = null;
		this.popup = null;
		this.presets = null;
		this.fields = null;
		this.types = types;
		this.dateTypes = dateTypes;
		this.additionalDateTypes = additionalDateTypes;
		this.additionalNumberTypes = additionalNumberTypes;
		this.numberTypes = numberTypes;
		this.settings = new BX.Filter.Settings(options, this);
		this.filter = null;
		this.api = null;
		this.isAddPresetModeState = false;
		this.firstInit = true;
		this.analyticsLabel = null;
		this.emitter = new BX.Event.EventEmitter();
		this.emitter.setEventNamespace('BX.Filter.Field');
		this.emitter.subscribe = function(eventName, listener) {
			BX.Event.EventEmitter.subscribe(
				this.emitter,
				eventName.replace('BX.Filter.Field:', ''),
				listener
			);
		}.bind(this);
		this.enableFieldsSearch = null;
		this.enableHeadersSections = null;

		this.init();
	};

	/**
	 * Converts string to camel case
	 * @param {string} string
	 * @return {*}
	 */
	function toCamelCase(string)
	{
		if (BX.type.isString(string))
		{
			string = string.toLowerCase();
			string = string.replace(/[\-_\s]+(.)?/g, function(match, chr) {
				return chr ? chr.toUpperCase() : '';
			});
			return string.substr(0, 1).toLowerCase() + string.substr(1);
		}

		return string;
	}

	//noinspection JSUnusedGlobalSymbols
	BX.Main.Filter.prototype = {
		init: function()
		{
			BX.bind(document, 'mousedown', BX.delegate(this._onDocumentClick, this));
			BX.bind(document, 'keydown', BX.delegate(this._onDocumentKeydown, this));
			BX.bind(window, 'load', BX.delegate(this.onWindowLoad, this));
			BX.addCustomEvent('Grid::ready', BX.delegate(this._onGridReady, this));

			this.getSearch().updatePreset(this.getParam('CURRENT_PRESET'));

			this.enableFieldsSearch = this.getParam('ENABLE_FIELDS_SEARCH', false);
			this.enableHeadersSections = this.getParam('HEADERS_SECTIONS', false);

			if (this.isAppliedDefaultPreset())
			{
				this.setDefaultPresetAppliedState(true);
			}
		},

		getEmitter: function()
		{
			return this.emitter;
		},


		onWindowLoad: function()
		{
			this.settings.get('AUTOFOCUS') && this.adjustFocus();
		},


		/**
		 * Removes apply_filter param from url
		 */
		clearGet: function()
		{
			if ('history' in window)
			{
				var url = window.location.toString();
				var clearUrl = BX.util.remove_url_param(url, 'apply_filter');
				window.history.replaceState(null, '', clearUrl);
			}
		},


		/**
		 * Adjusts focus on search field
		 */
		adjustFocus: function()
		{
			this.getSearch().adjustFocus();
		},

		_onAddPresetKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter'))
			{
				this._onSaveButtonClick();
			}
		},

		_onDocumentKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'escape'))
			{
				if (this.getPopup().isShown())
				{
					BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
					this.closePopup();

					if (this.getParam('VALUE_REQUIRED_MODE'))
					{
						this.restoreRemovedPreset();
					}

					if (this.getParam('VALUE_REQUIRED'))
					{
						if (!this.getSearch().getSquares().length)
						{
							this.getPreset().applyPinnedPreset();
						}
					}
				}
			}
		},


		/**
		 * Gets BX.Filter.Api instance
		 * @return {BX.Filter.Api}
		 */
		getApi: function()
		{
			if (!(this.api instanceof BX.Filter.Api))
			{
				this.api = new BX.Filter.Api(this);
			}

			return this.api;
		},


		/**
		 * Adds sidebar item
		 * @param {string} id
		 * @param {string} name
		 * @param {boolean} [pinned = false]
		 */
		addSidebarItem: function(id, name, pinned)
		{
			var Presets = this.getPreset();
			var presetsContainer = Presets.getContainer();
			var sidebarItem = Presets.createSidebarItem(id, name, pinned);
			var preset = Presets.getPresetNodeById(id);

			if (BX.type.isDomNode(preset))
			{
				BX.remove(preset);
				presetsContainer.insertBefore(sidebarItem, Presets.getAddPresetField());

			}
			else
			{
				presetsContainer && presetsContainer.insertBefore(sidebarItem, Presets.getAddPresetField());
			}

			BX.bind(sidebarItem, 'click', BX.delegate(Presets._onPresetClick, Presets));
		},


		/**
		 * Saves user settings
		 * @param {boolean} [forAll = false]
		 */
		saveUserSettings: function(forAll)
		{
			var optionsParams = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER_ARRAY'};
			var Presets = this.getPreset();
			var currentPresetId = Presets.getCurrentPresetId();
			var presetsSettings = {};

			this.params['PRESETS'] = BX.clone(this.editablePresets);
			presetsSettings.current_preset = currentPresetId;

			Presets.getPresets().forEach(function(current, index) {
				var presetId = Presets.getPresetId(current);

				if (presetId && presetId !== 'tmp_filter')
				{
					var presetData = Presets.getPreset(presetId);

					presetData.TITLE = BX.util.htmlspecialchars(BX.util.htmlspecialcharsback(presetData.TITLE));
					presetData.SORT = index;
					Presets.updatePresetName(current, presetData.TITLE);

					presetsSettings[presetId] = {
						sort: index,
						name: presetData.TITLE,
						fields: this.preparePresetSettingsFields(presetData.FIELDS),
						rows: presetData.FIELDS.map((field) => field.NAME),
						for_all: (
							(forAll && !BX.type.isBoolean(presetData.FOR_ALL)) ||
							(forAll && presetData.FOR_ALL === true)
						)
					}
				}
			}, this);

			this.saveOptions(presetsSettings, optionsParams, null, forAll);
		},


		/**
		 * Checks is for all
		 * @return {boolean}
		 */
		isForAll: function(forAll)
		{
			var checkbox = this.getForAllCheckbox();
			return (
				(BX.type.isBoolean(forAll) && forAll) ||
				(!!checkbox && !!checkbox.checked)
			);
		},


		/**
		 * Gets for all checkbox
		 * @return {?HTMLElement}
		 */
		getForAllCheckbox: function()
		{
			if (!this.forAllCheckbox)
			{
				this.forAllCheckbox = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
			}

			return this.forAllCheckbox;
		},


		/**
		 * Prepares preset settings fields
		 * @param fields
		 * @return {?object}
		 */
		preparePresetSettingsFields: function(fields)
		{
			var result = {};
			var valuesKeys;

			(fields || []).forEach(function(current) {
				switch (current.TYPE)
				{
					case this.types.STRING : {
						result[current.NAME] = current.VALUE;
						break;
					}

					case this.types.TEXTAREA : {
						result[current.NAME] = current.VALUE;
						break;
					}

					case this.types.SELECT : {
						result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
						break;
					}

					case this.types.MULTI_SELECT : {
						if (BX.type.isArray(current.VALUE) && current.VALUE.length)
						{
							current.VALUE.forEach(function(curr, index) {
								result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
								result[current.NAME][index] = curr.VALUE;
							}, this);
						}
						break;
					}

					case this.types.CHECKBOX : {
						if (BX.type.isArray(current.VALUE) && current.VALUE.length)
						{
							current.VALUE.forEach(function(curr, index) {
								result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
								result[current.NAME][index] = curr.VALUE;
							}, this);
						}
						break;
					}

					case this.types.DATE : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							valuesKeys = Object.keys(current.VALUES);
							result[current.NAME + '_datesel'] = current.SUB_TYPE.VALUE;
							valuesKeys.forEach(function(curr) {
								result[current.NAME + curr] = current.VALUES[curr];
							}, this);
						}
						break;
					}

					case this.types.NUMBER : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							valuesKeys = Object.keys(current.VALUES);
							result[current.NAME + '_numsel'] = current.SUB_TYPE.VALUE;
							valuesKeys.forEach(function(curr) {
								result[current.NAME + curr] = current.VALUES[curr];
							}, this);
						}
						break;
					}

					case this.types.DEST_SELECTOR : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							result[current.NAME] = current.VALUES._value;
							result[current.NAME + '_label'] = current.VALUES._label;
						}
						break;
					}

					case this.types.DEST_SELECTOR:
					case this.types.ENTITY_SELECTOR:
					case this.types.CUSTOM_ENTITY: {
						if (BX.type.isPlainObject(current.VALUES))
						{
							result[current.NAME] = current.VALUES._value;
							result[current.NAME + '_label'] = current.VALUES._label;
						}
						break;
					}

					default : {
						break;
					}
				}
			}, this);

			return result;
		},


		/**
		 * Saves preset
		 */
		savePreset: function()
		{
			var presetId = 'filter_' + (+new Date());
			var presetName = BX.util.htmlspecialcharsback(this.getPreset().getAddPresetFieldInput().value);

			this.updatePreset(presetId, presetName, null, true, null, null, true);
			this.addSidebarItem(presetId, presetName);
			this.getPreset().applyPreset(presetId);
			this.getPreset().activatePreset(presetId);
			this.applyFilter();
		},


		/**
		 * Updates preset
		 * @param {string} presetId
		 * @param {?string} [presetName]
		 * @param {?boolean} [reset]
		 * @param {?boolean} [sort]
		 * @param {?function} [beforeLoad]
		 * @param {?function} [afterLoad]
		 * @param {boolean} [isNew]
		 * @return {BX.Promise}
		 */
		updatePreset: function(presetId, presetName, reset, sort, beforeLoad, afterLoad, isNew)
		{
			var fields = this.getFilterFieldsValues();
			var sourceFields = this.getPreset().getFields().map(function(curr) { return BX.data(curr, 'name'); });
			var preset = this.getPreset().getCurrentPresetData();
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var rows, value, tmpPresetNode, tmpPresetInput, presets;
			var data = {};

			data.additional = {};

			if (presetId !== 'tmp_filter' && presetId !== 'default_filter' && !isNew)
			{
				var additional = BX.type.isArray(preset.ADDITIONAL) ? preset.ADDITIONAL : [];

				additional.forEach(function(field) {
					Object.keys(fields).forEach(function(key) {
						if (key.indexOf(field.NAME) !== -1)
						{
							data.additional[key] = fields[key];
							delete fields[key];
						}
					});
				});
			}

			rows = Object.keys(fields);

			if (!reset)
			{
				data.apply_filter = 'Y';
			}
			else
			{
				data.clear_filter = 'Y';
			}

			data.save = 'Y';
			data.fields = fields;
			data.rows = sourceFields.join(',');

			data.preset_id = presetId || preset.ID;

			if (BX.type.isNotEmptyString(presetName))
			{
				data.name = BX.util.htmlspecialchars(presetName);
			}
			else
			{
				tmpPresetNode = this.getPreset().getPresetNodeById(data.preset_id);
				tmpPresetInput = this.getPreset().getPresetInput(tmpPresetNode);

				if (BX.type.isDomNode(tmpPresetInput) && BX.type.isNotEmptyString(tmpPresetInput.value))
				{
					data.name = tmpPresetInput.value;
				}
				else
				{
					data.name = preset.TITLE;
				}
			}

			if ((!('sort' in data) || !BX.type.isNumber(data.sort)) && sort)
			{
				presets = this.getParam('PRESETS');
				data.sort = presets.length + 2;
			}

			if (!reset)
			{
				rows.forEach(function(key) {
					if (BX.type.isArray(data.fields[key]))
					{
						value = data.fields[key].length ? {} : '';

						data.fields[key].forEach(function(val, index) {
							value[index] = val;
						}, this);

						if (value || BX.type.isNumber(value) || BX.type.isBoolean(value))
						{
							data.fields[key] = value;
						}
					}
				}, this);
			}

			if (data.preset_id === 'tmp_filter' || this.isAddPresetEnabled() || reset)
			{
				this.updateParams(data);
			}

			if (BX.type.isFunction(beforeLoad))
			{
				beforeLoad();
			}

			var promise = new BX.Promise(null, this);
			promise.setAutoResolve('fulfill', 0);

			promise.then(function() {
				var afterPromise = new BX.Promise(null, this);
				this.saveOptions(data, params, BX.proxy(afterPromise.fulfill, afterPromise));
				return afterPromise;
			})
			.then(function() {
				!!afterLoad && afterLoad();
			});

			return promise;
		},


		/**
		 * Saves fields sort
		 */
		saveFieldsSort: function()
		{
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var fields = this.getPreset().getFields();
			var data = {};

			data.preset_id = 'default_filter';

			if (BX.type.isArray(fields))
			{
				data.rows = fields.map(function(current) {
					return BX.data(current, 'name');
				});
				data.rows = data.rows.join(',');
			}

			this.updateParams(data);
			this.saveOptions(data, params);
		},


		/**
		 * Updates params
		 * @param {object} data
		 */
		updateParams: function(data)
		{
			var preset, presets;
			var fields = [];

			if (BX.type.isPlainObject(data) && 'preset_id' in data)
			{
				preset = this.getPreset().getPreset(data.preset_id);

				if (BX.type.isPlainObject(preset))
				{
					if ('name' in data && BX.type.isNotEmptyString(data.name))
					{
						preset.TITLE = data.name;
					}

					if ('rows' in data && !('fields' in data))
					{
						data.fields = {};

						data.rows.split(',').forEach(function(curr) {
							data.fields[curr] = '';
						});
					}

					if ('fields' in data)
					{
						preset.FIELDS = this.preparePresetFields(data.fields, data.rows);
					}

					if ('additional' in data && preset.ID !== 'tmp_filter')
					{
						preset.ADDITIONAL = this.preparePresetFields(data.additional, data.rows);
					}
				}
				else
				{
					presets = this.getParam('PRESETS');
					preset = {
						ID: data.preset_id,
						TITLE: data.name,
						SORT: (presets.length + 2),
						FIELDS: this.preparePresetFields(data.fields, data.rows)
					};

					presets.push(preset);
				}
			}
		},


		/**
		 * Prepares preset fields
		 * @param {object[]} dataFields
		 * @param rows
		 * @return {object[]}
		 */
		preparePresetFields: function(dataFields, rows)
		{
			var fieldKeys, field;
			var fields = [];

			if (BX.type.isPlainObject(dataFields))
			{
				rows = BX.type.isNotEmptyString(rows) ? rows.split(',') : [];
				fieldKeys = rows.length ? rows : Object.keys(dataFields);
				fieldKeys.forEach(function(current) {
					current = current
						.replace('_datesel', '')
						.replace('_numsel', '')
						.replace('_' + BX.Filter.AdditionalFilter.Type.IS_EMPTY, '')
						.replace('_' + BX.Filter.AdditionalFilter.Type.HAS_ANY_VALUE, '');
					field = BX.clone(this.getFieldByName(current));

					if (BX.type.isPlainObject(field))
					{
						field.ADDITIONAL_FILTER = BX.Filter.AdditionalFilter.fetchAdditionalFilter(current, dataFields);
						if (!BX.Type.isStringFilled(field.ADDITIONAL_FILTER))
						{
							if (field.TYPE === this.types.STRING)
							{
								field.VALUE = dataFields[current];
							}

							if (field.TYPE === this.types.TEXTAREA)
							{
								field.VALUE = dataFields[current];
							}

							if (field.TYPE === this.types.MULTI_SELECT)
							{
								field.VALUE = this.prepareMultiSelectValue(dataFields[current], field.ITEMS);
							}

							if (field.TYPE === this.types.SELECT || field.TYPE === this.types.CHECKBOX)
							{
								field.VALUE = this.prepareSelectValue(dataFields[current], field.ITEMS);
							}

							if (field.TYPE === this.types.DATE)
							{
								field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_datesel'], field.SUB_TYPES);

								field.VALUES = {
									'_from': dataFields[current + '_from'],
									'_to': dataFields[current + '_to'],
									'_days': dataFields[current + '_days'],
									'_month': dataFields[current + '_month'],
									'_quarter': dataFields[current + '_quarter'],
									'_year': dataFields[current + '_year'],
									'_allow_year': dataFields[current + '_allow_year']
								};
							}

							if (field.TYPE === this.types.CUSTOM_DATE)
							{
								field.VALUE = {
									'days': Object.keys(dataFields[current + '_days'] || {}).map(function(index) {
										return dataFields[current + '_days'][index];
									}),
									'months': Object.keys(dataFields[current + '_months'] || {}).map(function(index) {
										return dataFields[current + '_months'][index];
									}),
									'years': Object.keys(dataFields[current + '_years'] || {}).map(function(index) {
										return dataFields[current + '_years'][index];
									})
								};
							}

							if (field.TYPE === this.types.NUMBER)
							{
								field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_numsel'], field.SUB_TYPES);
								field.VALUES = {
									'_from': dataFields[current + '_from'],
									'_to': dataFields[current + '_to']
								};
							}

							if (
								field.TYPE === this.types.DEST_SELECTOR
								|| field.TYPE === this.types.ENTITY_SELECTOR
								||field.TYPE === this.types.CUSTOM_ENTITY
							)
							{
								if (typeof dataFields[current + '_label'] !== 'undefined')
								{
									field.VALUES._label = dataFields[current + '_label'];
								}

								if (typeof dataFields[current] !== 'undefined')
								{
									field.VALUES._value = dataFields[current];
								}
							}

							if (field.TYPE === this.types.CUSTOM)
							{
								field._VALUE = dataFields[current];
							}
						}

						fields.push(field);
					}
				}, this);
			}

			return fields;
		},


		/**
		 * Prepares select values
		 * @param value
		 * @param items
		 * @return {object}
		 */
		prepareSelectValue: function(value, items)
		{
			var result = {};
			var tmpResult;

			if (BX.type.isNotEmptyString(value) && BX.type.isArray(items))
			{
				tmpResult = this.prepareMultiSelectValue({0: value}, items);
				result = tmpResult.length > 0 ? tmpResult[0] : {};
			}
			else
			{
				result = items[0];
			}

			return result;
		},


		/**
		 * Prepares multiselect value
		 * @param values
		 * @param items
		 * @return {Array}
		 */
		prepareMultiSelectValue: function(values, items)
		{
			var result = [];

			if (BX.type.isPlainObject(values) && BX.type.isArray(items))
			{
				var valuesKeys = Object.keys(values);
				var valuesValues = valuesKeys.map(function(curr) { return values[curr]; });

				result = items.filter(function(current) {
					return valuesValues.some(function(val) { return val === current.VALUE});
				}, this);
			}

			return result;
		},


		/**
		 * Get field by name
		 * @param {string} name
		 * @return {?object}
		 */
		getFieldByName: function(name)
		{
			var fields = this.getParam('FIELDS');

			var field = fields.find(function(current) {
				return current.NAME === name;
			});

			if (field)
			{
				return field;
			}

			var node = this.getFieldListContainer()
				.querySelector('[data-name="' + name + '"]');

			field = BX.Filter.Field.instances.get(node);

			if (field)
			{
				return field.options;
			}

			return null;
		},


		/**
		 * @private
		 * @return {Promise}
		 */
		confirmSaveForAll: function()
		{
			return new Promise(function(resolve) {
				var action = {
					CONFIRM: true,
					CONFIRM_MESSAGE: this.getParam('MAIN_UI_FILTER__CONFIRM_MESSAGE_FOR_ALL'),
					CONFIRM_APPLY_BUTTON: this.getParam('MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL'),
					CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
				};
				this.confirmDialog(action, resolve);
			}.bind(this));
		},


		/**
		 * Save options
		 * @param {object} data
		 * @param {object} [params]
		 * @param {function} [callback]
		 * @param {boolean} [forAll = false]
		 */
		saveOptions: function(data, params, callback, forAll)
		{
			params.action = toCamelCase(params.action);
			params.forAll = this.isForAll(forAll);
			params.commonPresetsId = this.getParam('COMMON_PRESETS_ID');
			params.apply_filter = data.apply_filter || "N";
			params.clear_filter = data.clear_filter || "N";
			params.with_preset = data.with_preset || "N";
			params.save = data.save || "N";
			params.isSetOutside = this.isSetOutside();

			var requestData = {
				params: params,
				data: data
			};

			delete data.apply_filter;
			delete data.save;
			delete data.clear_filter;
			delete data.with_preset;

			if (params.forAll && params.action === 'setFilterArray')
			{
				return this.confirmSaveForAll()
					.then(function() {
						return this.backend(params.action, requestData);
					}.bind(this))
					.then(function() {
						this.disableEdit();
						this.disableAddPreset();
					}.bind(this))
			}

			return this.backend(params.action, requestData)
				.then(function() {
					BX.removeClass(this.getFindButton(), this.settings.classWaitButtonClass);
					BX.type.isFunction(callback) && callback();
				}.bind(this));
		},


		/**
		 *
		 * @param {string} action
		 * @param data
		 */
		backend: function(action, data)
		{
			const analyticsLabel = this.analyticsLabel || {};
			this.analyticsLabel = {};

			return BX.ajax.runComponentAction(
				'bitrix:main.ui.filter',
				action,
				{
					mode: 'ajax',
					data: data,
					analyticsLabel: {
						FILTER_ID: this.getParam('FILTER_ID'),
						GRID_ID: this.getParam('GRID_ID'),
						PRESET_ID: data['data']['preset_id'],
						FIND: data['data'].hasOwnProperty('fields')
							&& data['data']['fields'].hasOwnProperty('FIND')
							&& !!data['data']['fields']['FIND'] ? "Y" : "N",
						ROWS: BX.Type.isObject(data['data']['additional'])
							&& Object.keys(data['data']['additional']).length == 0 ? "N" : "Y",
						...analyticsLabel
					}
				}
			);
		},

		/**
		 * Sends analytics when limit is enabled
		 */
		limitAnalyticsSend: function ()
		{
			BX.ajax.runComponentAction(
				'bitrix:main.ui.filter',
				'limitAnalytics',
				{
					mode: 'ajax',
					data: {},
					analyticsLabel: {
						FILTER_ID: this.getParam('FILTER_ID'),
						LIMIT: this.getParam('FILTER_ID')
					}
				}
			);
		},

		/**
		 * Prepares event.path
		 * @param event
		 * @return {*}
		 */
		prepareEvent: function(event)
		{
			var i, x;

			if (!('path' in event) || !event.path.length)
			{
				event.path = [event.target];
				i = 0;

				while ((x = event.path[i++].parentNode) !== null)
				{
					event.path.push(x);
				}
			}

			return event;
		},


		/**
		 * Restores removed preset values
		 * VALUE_REQUIRED_MODE = true only
		 */
		restoreRemovedPreset: function()
		{
			if (this.getParam('VALUE_REQUIRED_MODE'))
			{
				var currentPreset = this.getParam('CURRENT_PRESET');
				if (BX.type.isPlainObject(currentPreset))
				{
					var currentPresetId = currentPreset.ID;
					var presetNode = this.getPreset().getPresetNodeById(currentPresetId);
					this.getPreset().applyPreset(currentPresetId);
					this.getPreset().activatePreset(presetNode);
				}
			}
		},


		/**
		 * Checks that the event occurred on the scroll bar
		 * @param {MouseEvent} event
		 * @return {boolean}
		 */
		hasScrollClick: function(event)
		{
			var x = 'clientX' in event ? event.clientX : 'x' in event ? event.x : 0;
			return x >= document.documentElement.offsetWidth;
		},


		/**
		 * Checks whether to use common presets
		 * @return {boolean}
		 */
		isUseCommonPresets: function()
		{
			return !!this.getParam('COMMON_PRESETS_ID');
		},


		/**
		 * Checks whether event is inside filter
		 * @param {MouseEvent} event
		 * @returns {boolean}
		 */
		isInsideFilterEvent: function(event)
		{
			event = this.prepareEvent(event);
			return (event.path || []).some(function(current) {
				return (
					BX.type.isDomNode(current) && (
						BX.hasClass(current, this.settings.classFilterContainer) ||
						BX.hasClass(current, this.settings.classSearchContainer) ||
						BX.hasClass(current, this.settings.classDefaultPopup) ||
						BX.hasClass(current, this.settings.classPopupOverlay) ||
						BX.hasClass(current, this.settings.classSidePanelContainer)
					)
				);
			}, this);
		},

		_onDocumentClick: function(event)
		{
			var popup = this.getPopup();

			if (!this.isInsideFilterEvent(event) && !this.hasScrollClick(event))
			{
				if (popup && popup.isShown())
				{
					this.closePopup();

					if (this.getParam('VALUE_REQUIRED_MODE'))
					{
						this.restoreRemovedPreset();
					}

					if (this.getParam('VALUE_REQUIRED'))
					{
						if (!this.getSearch().getSquares().length)
						{
							this.getPreset().applyPinnedPreset();
						}
					}
				}

				BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
			}
		},

		_onAddFieldClick: function(event)
		{
			var popup = this.getFieldsPopup();
			event.stopPropagation();
			event.preventDefault();

			if (popup && !popup.isShown())
			{
				this.showFieldsPopup();
				this.syncFields();
			}
			else
			{
				this.closeFieldListPopup();
			}
		},


		/**
		 * Synchronizes field list in popup and filter field list
		 * @param {?{cache: boolean}} [options]
		 */
		syncFields: function(options)
		{
			if (BX.type.isPlainObject(options))
			{
				if (options.cache === false)
				{
					this.fieldsPopupItems = null;
				}
			}

			var fields = this.getPreset().getFields();
			var items = this.getFieldsPopupItems();
			var currentId, isNeedCheck;

			if (BX.type.isArray(items) && items.length)
			{
				items.forEach(function(current) {
					currentId = BX.data(current, 'name').replace('_datesel', '').replace('_numsel', '');
					isNeedCheck = fields.some(function(field) {
						return BX.data(field, 'name') === currentId;
					});
					if (isNeedCheck)
					{
						BX.addClass(current, this.settings.classMenuItemChecked);
					}
					else
					{
						BX.removeClass(current, this.settings.classMenuItemChecked);
					}
				}, this);
			}
		},


		/**
		 * Gets items of popup window with a list of available fields
		 * @return {?HTMLElement[]}
		 */
		getFieldsPopupItems: function()
		{
			if (!BX.type.isArray(this.fieldsPopupItems))
			{
				var popup = this.getFieldsPopup();

				if ('contentContainer' in popup && BX.type.isDomNode(popup.contentContainer))
				{
					this.fieldsPopupItems = BX.Filter.Utils.getByClass(popup.contentContainer, this.settings.classMenuItem, true);
				}

				this.prepareAnimation();
			}

			return this.fieldsPopupItems;
		},


		/**
		 * Gets popup container class name by popup items count
		 * @param {int|string} itemsCount
		 * @return {string}
		 */
		getFieldListContainerClassName: function(itemsCount)
		{
			var popupColumnsCount = parseInt(this.settings.get('popupColumnsCount', 0), 10);
			if (popupColumnsCount > 0 && popupColumnsCount <= this.settings.maxPopupColumnCount)
			{
				return this.settings.get('classPopupFieldList' + popupColumnsCount + 'Column');
			}

			var containerClass = this.settings.classPopupFieldList1Column;

			if (itemsCount > 6 && itemsCount < 12)
			{
				containerClass = this.settings.classPopupFieldList2Column;
			}

			if (itemsCount > 12)
			{
				containerClass = this.settings.classPopupFieldList3Column;
			}

			return containerClass;
		},


		/**
		 * Prepares fields declarations
		 * @param {object[]} fields
		 * @return {object[]}
		 */
		prepareFieldsDecl: function(fields)
		{
			return (fields || []).map(function(item) {
				return {
					block: 'main-ui-filter-field-list-item',
					label: 'LABEL' in item ? item.LABEL : '',
					id: 'ID' in item ? item.ID : '',
					name: 'NAME' in item ? item.NAME : '',
					item: item,
					sectionId: 'SECTION_ID' in item ? item.SECTION_ID : '',
					onClick: BX.delegate(this._clickOnFieldListItem, this)
				};
			}, this);
		},


		/**
		 * Gets lazy load field list
		 * @return {BX.Promise}
		 */
		getLazyLoadFields: function()
		{
			var p = new BX.Promise();

			BX.ajax({
				method: 'GET',
				url: this.getParam("LAZY_LOAD")["GET_LIST"],
				dataType: 'json',
				onsuccess: function(response) {
					p.fulfill(response);
				}
			});

			return p;
		},


		/**
		 * Gets fields list popup content
		 * @return {BX.Promise}
		 */
		getFieldsListPopupContent: function()
		{
			var p = new BX.Promise();
			var fields = this.getParam('FIELDS');
			var fieldsCount = BX.type.isArray(fields) ? fields.length : 0;

			if (this.getParam('LAZY_LOAD'))
			{
				const callback = function(response) {
					p.fulfill(this.getPopupContent(
						this.settings.classPopupFieldList,
						this.getFieldListContainerClassName(response.length),
						this.prepareFieldsDecl(response)
					));
				}.bind(this);

				if (BX.type.isNotEmptyObject(this.getParam('LAZY_LOAD')['CONTROLLER']))
				{
					var sourceComponentName = this.getParam('LAZY_LOAD')['CONTROLLER']['componentName'];
					var sourceComponentSignedParameters = this.getParam('LAZY_LOAD')['CONTROLLER']['signedParameters'];

					BX.ajax.runAction(this.getParam('LAZY_LOAD')['CONTROLLER']['getList'], {
						data: {
							filterId: this.getParam('FILTER_ID'),
							componentName: (BX.type.isNotEmptyString(sourceComponentName) ? sourceComponentName : ''),
							signedParameters: (BX.type.isNotEmptyString(sourceComponentSignedParameters) ? sourceComponentSignedParameters : '')
						}
					}).then(function(response) {
						callback(response.data);
					}.bind(this), function (response) {
					});
				}
				else
				{
					this.getLazyLoadFields().then(callback);
				}

				return p;
			}

			p.fulfill(this.getPopupContent(
				this.settings.classPopupFieldList,
				this.getFieldListContainerClassName(fieldsCount),
				this.prepareFieldsDecl(fields)
			));
			return p;
		},

		getPopupContent: function(block: string, mix: string, content: Object[]): HTMLElement
		{
			const wrapper = BX.Tag.render`<div></div>`;
			if (!this.enableHeadersSections)
			{
				const fieldsContent = BX.decl({
					content: content,
					block: block,
					mix: mix,
				});
				this.setPopupElementWidthFromSettings(fieldsContent);
				wrapper.appendChild(fieldsContent);

				if (this.enableFieldsSearch)
				{
					this.preparePopupContentHeader(wrapper);
				}

				return wrapper;
			}

			const defaultHeaderSection = this.getDefaultHeaderSection();
			const sections = {};

			content.forEach((item: Object) => {
				const sectionId = (item.sectionId.length ? item.sectionId : defaultHeaderSection.id);
				if (sections[sectionId] === undefined)
				{
					sections[sectionId] = [];
				}
				sections[sectionId].push(item);
			});

			this.preparePopupContentHeader(wrapper);
			this.preparePopupContentFields(wrapper, sections, block, mix);

			return wrapper;
		},

		preparePopupContentHeader: function(wrapper: HTMLElement): void
		{
			const headerWrapper = BX.Tag.render`
				<div class="main-ui-filter-popup-search-header-wrapper">
					<div class="ui-form-row-inline"></div>
				</div>
			`;

			wrapper.prepend(headerWrapper);

			this.preparePopupContentHeaderSections(headerWrapper);
			this.preparePopupContentHeaderSearch(headerWrapper);
		},

		preparePopupContentHeaderSections: function(headerWrapper): void
		{
			if (!this.enableHeadersSections)
			{
				return;
			}

			const headerSectionsWrapper = BX.Tag.render`
				<div class="ui-form-row">
					<div class="ui-form-content main-ui-filter-popup-search-section-wrapper"></div>
				</div>
			`;

			headerWrapper.firstElementChild.appendChild(headerSectionsWrapper);

			const headersSections = this.getHeadersSections();
			for (let key in headersSections)
			{
				const itemClass = this.settings.classPopupSearchSectionItemIcon
				 + (headersSections[key].selected ? ` ${this.settings.classPopupSearchSectionItemIconActive}` : '');

				const headerSectionItem = BX.Tag.render`
					<div class="main-ui-filter-popup-search-section-item" data-ui-popup-filter-section-button="${key}">
						<div class="${itemClass}">
							<div>
								${BX.Text.encode(headersSections[key].name)}
							</div>
						</div>
					</div>
				`;
				BX.bind(headerSectionItem, 'click', this.onFilterSectionClick.bind(this, headerSectionItem));

				headerSectionsWrapper.firstElementChild.appendChild(headerSectionItem);
			}
		},

		onFilterSectionClick: function(item: HTMLElement): void
		{
			const activeClass = this.settings.classPopupSearchSectionItemIconActive;
			const sectionId = item.dataset.uiPopupFilterSectionButton;
			const section = document.querySelectorAll("[data-ui-popup-filter-section='"+sectionId+"']");
			if (BX.Dom.hasClass(item.firstElementChild, activeClass))
			{
				BX.Dom.removeClass(item.firstElementChild, activeClass);
				BX.Dom.hide(section[0]);
			}
			else
			{
				BX.Dom.addClass(item.firstElementChild, activeClass);
				BX.Dom.show(section[0]);
			}
		},

		preparePopupContentHeaderSearch: function(headerWrapper: HTMLElement): void
		{
			if (!this.enableFieldsSearch)
			{
				return;
			}

			const searchForm = BX.Tag.render`
				<div class="ui-form-row">
					<div class="ui-form-content main-ui-filter-popup-search-input-wrapper">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon">
							<div class="ui-ctl-before ui-ctl-icon-search"></div>
							<button class="ui-ctl-after ui-ctl-icon-clear"></button>
							<input type="text" class="ui-ctl-element ${this.settings.classPopupSearchSectionItem}">
						</div>
					</div>
				</div>
			`;
			headerWrapper.firstElementChild.appendChild(searchForm);
			const inputs = searchForm.getElementsByClassName(this.settings.classPopupSearchSectionItem);
			if (inputs.length)
			{
				const input = inputs[0];
				BX.bind(input, 'input', this.onFilterSectionSearchInput.bind(this, input));
				BX.bind(input.previousElementSibling, 'click', this.onFilterSectionSearchInputClear.bind(this, input));
			}
		},

		preparePopupContentFields: function(wrapper: HTMLElement, sections, block: string, mix): void
		{
			if (!this.enableHeadersSections)
			{
				return;
			}

			const sectionsWrapper = BX.Tag.render`<div class="main-ui-filter-popup-search-sections-wrapper"></div>`;
			wrapper.appendChild(sectionsWrapper);

			for (let key in sections)
			{
				const sectionWrapper = BX.Tag.render`
					<div class="main-ui-filter-popup-section-wrapper" data-ui-popup-filter-section="${key}"></div>
				`;
				this.setPopupElementWidthFromSettings(sectionWrapper);

				if (!this.getHeadersSectionParam(key, 'selected'))
				{
					sectionWrapper.setAttribute('hidden', '');
				}

				const sectionTitle = BX.Tag.render`
					<h3 class="main-ui-filter-popup-title">
						${BX.Text.encode(this.getHeadersSectionParam(key, 'name'))}
					</h3>
				`;

				const fieldsBlock = BX.decl({
					block: block,
					mix: mix,
					content: sections[key]
				});

				sectionWrapper.appendChild(sectionTitle);
				sectionWrapper.appendChild(fieldsBlock);

				sectionsWrapper.appendChild(sectionWrapper);
			}
		},

		prepareAnimation: function(): void
		{
			if (this.enableFieldsSearch)
			{
				this.fieldsPopupItems.forEach(item =>
				{
					BX.bind(item, 'animationend', this.onAnimationEnd.bind(this, item));
				});
			}
		},

		onAnimationEnd: function(item: HTMLElement): void
		{
			item.style.display = (
				BX.Dom.hasClass(item, this.settings.classPopupSearchFieldListItemHidden)
				? 'none'
				: 'inline-block'
			);
		},

		onFilterSectionSearchInput: function(input: HTMLElement): void
		{
			let search = input.value;
			if (search.length)
			{
				search = search.toLowerCase();
			}

			this.getFieldsPopupItems().forEach(function (item){
				const title = item.innerText.toLowerCase();

				if (search.length && title.indexOf(search) === -1)
				{
					BX.Dom.removeClass(item,this.settings.classPopupSearchFieldListItemVisible);
					BX.Dom.addClass(item,this.settings.classPopupSearchFieldListItemHidden);
				}
				else
				{
					BX.Dom.removeClass(item, this.settings.classPopupSearchFieldListItemHidden);
					BX.Dom.addClass(item, this.settings.classPopupSearchFieldListItemVisible);
					item.style.display = 'inline-block';
				}
			}.bind(this));
		},

		onFilterSectionSearchInputClear: function(input: HTMLElement): void
		{
			if (input.value.length)
			{
				input.value = '';
				this.onFilterSectionSearchInput(input);
			}
		},

		getDefaultHeaderSection: function(): Object|null
		{
			const headersSections = this.getHeadersSections();

			for (let key in headersSections)
			{
				if ('selected' in headersSections[key] && headersSections[key].selected)
				{
					return headersSections[key];
				}
			}

			return null;
		},

		getHeadersSections: function(): Array
		{
			return this.getParam('HEADERS_SECTIONS');
		},

		getHeadersSectionParam: function(sectionId: string, paramName: string, defaultValue: any): any
		{
			if (
				this.getHeadersSections()[sectionId] !== undefined
				&& this.getHeadersSections()[sectionId][paramName] !== undefined
			)
			{
				return this.getHeadersSections()[sectionId][paramName];
			}
			return defaultValue;
		},

		/**
		 * Gets field loader
		 * @return {BX.Loader}
		 */
		getFieldLoader: function()
		{
			if (!this.fieldLoader)
			{
				this.fieldLoader = new BX.Loader({mode: "custom", size: 18, offset: {left: "5px", top: "5px"}});
			}

			return this.fieldLoader;
		},

		_clickOnFieldListItem: function(event)
		{
			var target = event.target;
			var data;

			if (!BX.hasClass(target, this.settings.classFieldListItem))
			{
				target = BX.findParent(target, {className: this.settings.classFieldListItem}, true, false);
			}

			if (BX.type.isDomNode(target))
			{
				try {
					data = JSON.parse(BX.data(target, 'item'));
				} catch (err) {}

				let isChecked = BX.hasClass(target, this.settings.classMenuItemChecked);
				let event = new BX.Event.BaseEvent({
					data
				});
				this.emitter.emit(
					isChecked
						? 'onBeforeRemoveFilterItem'
						: 'onBeforeAddFilterItem'
					,
					event
				);

				if (event.isDefaultPrevented())
				{
					return;
				}

				var p = new BX.Promise();

				if (this.getParam("LAZY_LOAD"))
				{
					this.getFieldLoader().show(target);
					var label = target.querySelector(".main-ui-select-inner-label");

					if (label)
					{
						label.classList.add("main-ui-no-before");
					}

					var callback = function(response) {
						p.fulfill(response);
						this.getFieldLoader().hide();
						if (label)
						{
							label.classList.remove("main-ui-no-before");
						}
					}.bind(this);

					if (BX.type.isNotEmptyObject(this.getParam('LAZY_LOAD')['CONTROLLER']))
					{
						var sourceComponentName = this.getParam('LAZY_LOAD')['CONTROLLER']['componentName'];
						var sourceComponentSignedParameters = this.getParam('LAZY_LOAD')['CONTROLLER']['signedParameters'];

						BX.ajax.runAction(this.getParam('LAZY_LOAD')['CONTROLLER']['getField'], {
							data: {
								filterId: this.getParam('FILTER_ID'),
								id: data.NAME,
								componentName: (BX.type.isNotEmptyString(sourceComponentName) ? sourceComponentName : ''),
								signedParameters: (BX.type.isNotEmptyString(sourceComponentSignedParameters) ? sourceComponentSignedParameters : '')
							}
						}).then(function(response) {
							callback(response.data);
						}.bind(this), function (response) {
						});
					}
					else
					{
						this.getLazyLoadField(data.NAME).then(callback);
					}
				}
				else
				{
					p.fulfill(data);
				}

				p.then(function(response) {
					this.params.FIELDS.push(response);

					if (BX.hasClass(target, this.settings.classMenuItemChecked))
					{
						BX.removeClass(target, this.settings.classMenuItemChecked);
						this.getPreset().removeField(response);
					}
					else
					{
						if (BX.type.isPlainObject(response))
						{
							this.getPreset().addField(response);
							BX.addClass(target, this.settings.classMenuItemChecked);

							if (BX.type.isString(response.HTML))
							{
								var wrap = BX.create("div");
								this.getHiddenElement().appendChild(wrap);
								BX.html(wrap, response.HTML);
							}
						}
					}

					this.syncFields();
				}.bind(this));
			}
		},


		getHiddenElement: function()
		{
			if (!this.hiddenElement)
			{
				this.hiddenElement = BX.create("div");
				document.body.appendChild(this.hiddenElement);
			}

			return this.hiddenElement;
		},


		/**
		 * Gets lazy load fields
		 * @param id
		 * @return {BX.Promise}
		 */
		getLazyLoadField: function(id)
		{
			var p = new BX.Promise();

			BX.ajax({
				method: 'get',
				url: BX.util.add_url_param(this.getParam("LAZY_LOAD")["GET_FIELD"], {id: id}),
				dataType: 'json',
				onsuccess: function(response) {
					p.fulfill(response);
				}
			});

			return p;
		},


		/**
		 * Shows fields list popup
		 */
		showFieldsPopup: function()
		{
			var popup = this.getFieldsPopup();
			this.adjustFieldListPopupPosition();
			popup.show();
		},


		/**
		 * Closes fields list popup
		 */
		closeFieldListPopup: function()
		{
			var popup = this.getFieldsPopup();
			popup.close();
		},


		/**
		 * Adjusts field list popup position
		 */
		adjustFieldListPopupPosition: function()
		{
			var popup = this.getFieldsPopup();
			var pos = BX.pos(this.getAddField());
			pos.forceBindPosition = true;
			popup.adjustPosition(pos);
		},


		/**
		 * Gets field list popup instance
		 * @return {BX.PopupWindow}
		 */
		getFieldsPopup: function()
		{
			var bindElement = (this.settings.get('showPopupInCenter', false) ? null : this.getAddField());

			if (!this.fieldsPopup)
			{
				this.fieldsPopup = new BX.PopupWindow(
					this.getParam('FILTER_ID') + '_fields_popup',
					bindElement,
					{
						autoHide : true,
						offsetTop : 4,
						offsetLeft : 0,
						lightShadow : true,
						closeIcon : (bindElement === null),
						closeByEsc : (bindElement === null),
						noAllPaddings: true,
						zIndex: 13
					}
				);

				this.fieldsPopupLoader = new BX.Loader({target: this.fieldsPopup.contentContainer});
				this.fieldsPopupLoader.show();
				this.setPopupElementWidthFromSettings(this.fieldsPopup.contentContainer);
				this.fieldsPopup.contentContainer.style.height = "330px";
				this.getFieldsListPopupContent().then(function(res) {
					this.fieldsPopup.contentContainer.removeAttribute("style");
					this.fieldsPopupLoader.hide();
					this.fieldsPopup.setContent(res);
					this.syncFields({cache: false});
					this.adjustFieldListPopupPosition();
				}.bind(this));
			}

			return this.fieldsPopup;
		},

		setPopupElementWidthFromSettings: function(element: HTMLElement): void
		{
			element.style.width = this.settings.popupWidth + 'px';
		},

		_onAddPresetClick: function()
		{
			this.enableAddPreset();
		},


		/**
		 * Enables shows wait spinner for button
		 * @param {HTMLElement} button
		 */
		enableWaitSate: function(button)
		{
			!!button && BX.addClass(button, this.settings.classWaitButtonClass);
		},


		/**
		 * Disables shows wait spinner for button
		 * @param {HTMLElement} button
		 */
		disableWaitState: function(button)
		{
			!!button && BX.removeClass(button, this.settings.classWaitButtonClass);
		},

		_onSaveButtonClick: function()
		{
			var forAll = !!this.getSaveForAllCheckbox() && this.getSaveForAllCheckbox().checked;
			var input = this.getPreset().getAddPresetFieldInput();
			var mask = input.parentNode.querySelector(".main-ui-filter-edit-mask");
			var presetName;

			function onAnimationEnd(event)
			{
				if (event.animationName === "fieldError")
				{
					event.currentTarget.removeEventListener("animationend", onAnimationEnd);
					event.currentTarget.removeEventListener("oAnimationEnd", onAnimationEnd);
					event.currentTarget.removeEventListener("webkitAnimationEnd", onAnimationEnd);
					event.currentTarget.classList.remove("main-ui-filter-error");
				}
			}

			function showLengthError(mask)
			{
				mask.addEventListener("animationend", onAnimationEnd);
				mask.addEventListener("oAnimationEnd", onAnimationEnd);
				mask.addEventListener("webkitAnimationEnd", onAnimationEnd);
				mask.classList.add("main-ui-filter-error");
				var promise = new BX.Promise();
				promise.fulfill(true);
				return promise;
			}

			this.enableWaitSate(this.getFindButton());

			if (this.isAddPresetEnabled() && !forAll)
			{
				presetName = input.value;

				if (presetName.length)
				{
					this.savePreset();
					this.disableAddPreset();
				}
				else
				{
					showLengthError(mask).then(function() {
						input.focus();
					});
				}
			}

			if (this.isEditEnabled())
			{
				var preset = this.getPreset();
				var presetNode = preset.getPresetNodeById(preset.getCurrentPresetId());
				var presetNameInput = preset.getPresetInput(presetNode);

				if (presetNameInput.value.length)
				{
					preset.updateEditablePreset(preset.getCurrentPresetId());
					this.saveUserSettings(forAll);

					if (!forAll)
					{
						this.disableEdit();
					}
				}
				else
				{
					var presetMask = presetNode.querySelector(".main-ui-filter-edit-mask");
					showLengthError(presetMask).then(function() {
						presetNameInput.focus();
					});
				}
			}
		},

		_onCancelButtonClick: function()
		{
			this.setIsSetOutsideState(false);
			this.disableAddPreset();
			this.getPreset().clearAddPresetFieldInput();
			this.disableEdit();
			!!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
		},

		_onGridReady: function(grid)
		{
			if (!this.grid && grid.getContainerId() === this.getParam('GRID_ID'))
			{
				this.grid = grid;
			}
		},

		_onFilterMousedown: function(event)
		{
			var target = event.target;

			if (this.getFields().isDragButton(target))
			{
				var inputs = BX.Filter.Utils.getByTag(target.parentNode, 'input', true);

				(inputs || []).forEach(function(item) {
					BX.fireEvent(item, 'blur');
				});

				BX.fireEvent(this.getFilter(), 'click');
			}
		},

		_onFilterClick: function(event)
		{
			var Fields = this.getFields();
			var Presets = this.getPreset();
			var field;

			if (Fields.isFieldDelete(event.target))
			{
				field = Fields.getField(event.target);
				Presets.removeField(field);
			}

			if (Fields.isFieldValueDelete(event.target))
			{
				field = Fields.getField(event.target);
				Fields.clearFieldValue(field);
			}
		},


		/**
		 * Gets filter buttons container
		 * @return {?HTMLElement}
		 */
		getButtonsContainer: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classButtonsContainer);
		},


		/**
		 * Gets save button element
		 * @return {?HTMLElement}
		 */
		getSaveButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSaveButton);
		},


		/**
		 * Gets cancel element
		 * @return {?HTMLElement}
		 */
		getCancelButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classCancelButton);
		},


		/**
		 * Gets find button element
		 * @return {?HTMLElement}
		 */
		getFindButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFindButton);
		},


		/**
		 * Gets reset button element
		 * @return {?HTMLElement}
		 */
		getResetButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classResetButton);
		},


		/**
		 * Gets add preset button
		 * @return {?HTMLElement}
		 */
		getAddPresetButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddPresetButton);
		},


		/**
		 * Checks that add preset mode enabled
		 * @return {boolean}
		 */
		isAddPresetEnabled: function()
		{
			return this.isAddPresetModeState;
		},


		/**
		 * Enables add preset mode
		 */
		enableAddPreset: function()
		{
			var Preset = this.getPreset();
			var addPresetField = Preset.getAddPresetField();
			var addPresetFieldInput = Preset.getAddPresetFieldInput();
			var buttonsContainer = this.getButtonsContainer();

			BX.show(addPresetField);
			BX.show(buttonsContainer);
			BX.hide(this.getPresetButtonsContainer());
			this.hideForAllCheckbox();

			if (BX.type.isDomNode(addPresetFieldInput))
			{
				addPresetFieldInput.focus();
			}

			BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);

			this.isAddPresetModeState = true;
		},


		/**
		 * Disables add preset mode
		 */
		disableAddPreset: function()
		{
			var Preset = this.getPreset();
			var addPresetField = Preset.getAddPresetField();
			var buttonsContainer = this.getButtonsContainer();

			BX.hide(addPresetField);
			BX.hide(buttonsContainer);
			BX.show(this.getPresetButtonsContainer());
			this.showForAllCheckbox();

			Preset.getAddPresetFieldInput().value = '';

			BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);

			this.isAddPresetModeState = false;
		},


		/**
		 * Gets control from field list
		 * @return {?HTMLElement[]}
		 */
		getControls: function()
		{
			var container = this.getFieldListContainer();
			var controls = null;

			if (BX.type.isDomNode(container))
			{
				controls = BX.Filter.Utils.getByClass(container, this.settings.classControl, true);
			}

			return controls;
		},


		/**
		 * Gets filter fields
		 * @return {?HTMLElement[]}
		 */
		getFilterFields: function()
		{
			var container = this.getFieldListContainer();
			var fields = [];
			var groups = [];

			if (BX.type.isDomNode(container))
			{
				fields = BX.Filter.Utils.getByClass(container, this.settings.classField, true);
				groups = BX.Filter.Utils.getByClass(container, this.settings.classFieldGroup, true);

				if (!BX.type.isArray(fields))
				{
					fields = [];
				}

				if (BX.type.isArray(groups))
				{
					groups.forEach(function(current) {
						fields.push(current);
					});
				}
			}

			return fields;
		},


		/**
		 * Gets filter fields values
		 * @return {object}
		 */
		getFilterFieldsValues: function()
		{
			var fields = this.getPreset().getFields();
			var Search = this.getSearch();
			var values = {};
			var type, name;

			values['FIND'] = Search.getInput().value;

			if (BX.type.isArray(fields) && fields.length)
			{
				fields.forEach(function(current) {
					var additionalFilter = BX.Filter.AdditionalFilter.getInstance().getFilter(current);
					if (additionalFilter)
					{
						Object.assign(values, additionalFilter);
						return;
					}

					type = BX.data(current, 'type');
					name = BX.data(current, 'name');

					switch (type) {
						case this.types.STRING : {
							this.prepareControlStringValue(values, current);
							break;
						}

						case this.types.TEXTAREA : {
							this.prepareControlTextareaValue(values, current);
							break;
						}

						case this.types.NUMBER : {
							this.prepareControlNumberValue(values, name, current);
							break;
						}

						case this.types.DATE : {
							this.prepareControlDateValue(values, name, current);
							break;
						}

						case this.types.CUSTOM_DATE : {
							this.prepareControlCustomDateValue(values, name, current);
							break;
						}

						case this.types.SELECT : {
							this.prepareControlSelectValue(values, name, current);
							break;
						}

						case this.types.MULTI_SELECT : {
							this.prepareControlMultiselectValue(values, name, current);
							break;
						}

						case this.types.DEST_SELECTOR:
						case this.types.CUSTOM_ENTITY:
						case this.types.ENTITY_SELECTOR: {
							this.prepareControlCustomEntityValue(values, name, current);
							break;
						}

						case this.types.CUSTOM : {
							this.prepareControlCustomValue(values, name, current);
							break;
						}

						default : {
							break;
						}
					}
				}, this);
			}

			return values;
		},


		/**
		 * @param values
		 * @param name
		 * @param field
		 */
		prepareControlCustomEntityValue: function(values, name, field)
		{
			var squares = this.fetchSquares(field);
			var squaresData = this.fetchSquaresData(squares);
			var isMultiple = BX.Main.ui.CustomEntity.isMultiple(field);

			values[name] = '';
			values[name + '_label'] = '';

			if (isMultiple)
			{
				values[name] = [];
				values[name + '_label'] = [];

				!!squaresData && squaresData.forEach(function(item) {
					values[name].push(item._value.toString());
					values[name + '_label'].push(item._label.toString());
				});
			}
			else
			{
				if (squaresData.length)
				{
					values[name] = squaresData[0]._value.toString();
					values[name + '_label'] = squaresData[0]._label.toString();
				}
			}
		},


		/**
		 * @param {HTMLElement} field
		 * @return {HTMLElement[]}
		 */
		fetchSquares: function(field)
		{
			return !!field ? BX.Filter.Utils.getByClass(field, this.settings.classSquare, true) : [];
		},


		/**
		 * @param {HTMLElement[]} squares
		 * @return {object[]}
		 */
		fetchSquaresData: function(squares)
		{
			return squares.map(function(square) {
				return JSON.parse(BX.data(square, 'item'));
			}, this);
		},


		/**
		 * @param {object} values
		 * @param {string} name
		 * @param {HTMLElement} field
		 */
		prepareControlCustomValue: function(values, name, field)
		{
			var stringFields = BX.Filter.Utils.getByTag(field, 'input', true);

			values[name] = '';

			if (BX.type.isArray(stringFields))
			{
				stringFields.forEach(function(current) {
					if (BX.type.isNotEmptyString(current.name))
					{
						values[current.name] = current.value;
					}
				});
			}
		},

		prepareControlMultiselectValue: function(values, name, field)
		{
			var select = BX.Filter.Utils.getByClass(field, this.settings.classMultiSelect);
			var value = JSON.parse(BX.data(select, 'value'));

			values[name] = '';

			if (BX.type.isArray(value) && value.length)
			{
				values[name] = {};
				value.forEach(function(current, index) {
					values[name][index] = current.VALUE;
				});
			}
		},

		prepareControlSelectValue: function(values, name, field)
		{
			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var value = JSON.parse(BX.data(select, 'value'));

			values[name] = value.VALUE;
		},

		prepareControlCustomDateValue: function(values, name, field)
		{
			var daysControl = field.querySelector("[data-name=\""+name + '_days'+"\"]");

			if (daysControl)
			{
				var daysValue = JSON.parse(daysControl.dataset.value);

				values[name + '_days'] = daysValue.map(function(item) {
					return item.VALUE;
				});
			}

			var monthsControl = field.querySelector("[data-name=\""+name + '_months'+"\"]");

			if (monthsControl)
			{
				var monthsValue = JSON.parse(monthsControl.dataset.value);

				values[name + '_months'] = monthsValue.map(function(item) {
					return item.VALUE;
				});
			}

			var yearsControl = field.querySelector("[data-name=\""+name + '_years'+"\"]");

			if (yearsControl)
			{
				var yearsValue = JSON.parse(yearsControl.dataset.value);

				values[name + '_years'] = yearsValue.map(function(item) {
					return item.VALUE;
				});
			}
		},

		prepareControlDateValue: function(values, name, field, withAdditional)
		{
			var additionalFieldsContainer = field.querySelector('.main-ui-filter-additional-fields-container');

			if (additionalFieldsContainer && !withAdditional)
			{
				BX.remove(additionalFieldsContainer);
			}

			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var yearsSwitcher = field.querySelector(".main-ui-select[data-name*=\"_allow_year\"]");
			var selectName = name + this.settings.datePostfix;
			var fromName = name + this.settings.fromPostfix;
			var toName = name + this.settings.toPostfix;
			var daysName = name + this.settings.daysPostfix;
			var monthName = name + this.settings.monthPostfix;
			var quarterName = name + this.settings.quarterPostfix;
			var yearName = name + this.settings.yearPostfix;
			var yearsSwitcherName = name + "_allow_year";
			var selectValue, stringFields, controls, controlName, yearsSwitcherValue;

			values[selectName] = '';
			values[fromName] = '';
			values[toName] = '';
			values[daysName] = '';
			values[monthName] = '';
			values[quarterName] = '';
			values[yearName] = '';

			var input = field.querySelector(".main-ui-date-input");

			if (input && input.dataset.isValid === "false")
			{
				return;
			}

			selectValue = JSON.parse(BX.data(select, 'value'));
			values[selectName] = selectValue.VALUE;

			if (yearsSwitcher)
			{
				yearsSwitcherValue = JSON.parse(BX.data(yearsSwitcher, 'value'));
				values[yearsSwitcherName] = yearsSwitcherValue.VALUE;
			}

			switch (selectValue.VALUE) {
				case this.dateTypes.EXACT : {
					stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput);
					values[fromName] = stringFields.value;
					values[toName] = stringFields.value;
					break;
				}

				case this.dateTypes.QUARTER : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (controlName && controlName.indexOf('_quarter') !== -1)
							{
								values[quarterName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}

							if (
								controlName
								&& controlName.endsWith('_year')
								&& !controlName.endsWith('_allow_year')
							)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.dateTypes.YEAR : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (
								controlName
								&& controlName.endsWith('_year')
								&& !controlName.endsWith('_allow_year')
							)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.dateTypes.MONTH : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (controlName && controlName.indexOf('_month') !== -1)
							{
								values[monthName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}

							if (
								controlName
								&& controlName.endsWith('_year')
								&& !controlName.endsWith('_allow_year')
							)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.additionalDateTypes.PREV_DAY :
				case this.additionalDateTypes.NEXT_DAY :
				case this.additionalDateTypes.MORE_THAN_DAYS_AGO :
				case this.additionalDateTypes.AFTER_DAYS :
				case this.dateTypes.NEXT_DAYS :
				case this.dateTypes.PREV_DAYS : {
					var control = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput);

					if (!!control && control.name === daysName)
					{
						values[daysName] = control.value;
					}

					break;
				}

				case this.dateTypes.RANGE : {
					stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput, true);
					stringFields.forEach(function(current) {
						if (current.name === fromName)
						{
							values[fromName] = current.value;
						}
						else if (current.name === toName)
						{
							values[toName] = current.value;
						}
					}, this);
					break;
				}

				case "CUSTOM_DATE" : {
					var customValues = {};
					this.prepareControlCustomDateValue(customValues, name, field);
					values[name + '_days'] = customValues[name + '_days'];
					values[monthName] = customValues[name + '_months'];
					values[yearName] = customValues[name + '_years'];
					break;
				}

				default : {
					break;
				}
			}

			if (additionalFieldsContainer && !withAdditional)
			{
				BX.append(additionalFieldsContainer, field);
			}

			var additionalFields = Array.from(
				field.querySelectorAll(
					'.main-ui-filter-additional-fields-container > [data-type="DATE"]',
				)
			);

			if (additionalFields)
			{
				additionalFields.forEach(function(additionalField) {
					var name = additionalField.dataset.name;
					this.prepareControlDateValue(values, name, additionalField, true);
				}, this);
			}
		},

		prepareControlNumberValue: function(values, name, field)
		{
			var stringFields = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput, true);
			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var selectName = name + this.settings.numberPostfix;
			var fromName = name + this.settings.fromPostfix;
			var toName = name + this.settings.toPostfix;
			var selectValue;

			values[fromName] = '';
			values[toName] = '';

			selectValue = JSON.parse(BX.data(select, 'value'));
			values[selectName] = selectValue.VALUE;

			stringFields.forEach(function(current) {
				if (current.name.indexOf(this.settings.fromPostfix) !== -1)
				{
					values[fromName] = current.value || '';

					if (values[selectName] === 'exact')
					{
						values[toName] = current.value || '';
					}
				}
				else if (current.name.indexOf(this.settings.toPostfix) !== -1)
				{
					values[toName] = current.value || '';
				}
			}, this);
		},

		prepareControlStringValue: function(values, field)
		{
			var control = BX.Filter.Utils.getByClass(field, this.settings.classStringInput);
			var name;

			if (BX.type.isDomNode(control))
			{
				name = control.name;
				values[name] = control.value;
			}
		},

		prepareControlTextareaValue: function(values, field)
		{
			var control = BX.Filter.Utils.getByClass(field, this.settings.classStringInput);
			var name;

			if (BX.type.isDomNode(control))
			{
				name = control.name;
				values[name] = control.value;
			}
		},


		/**
		 * Shows grid animation
		 */
		showGridAnimation: function()
		{
			this.grid && this.grid.tableFade();
		},


		/**
		 * Hides grid animations
		 */
		hideGridAnimation: function()
		{
			this.grid && this.grid.tableUnfade();
		},

		/**
		 * @private
		 * @param {?Boolean} clear - is need reset filter
		 * @param {?Boolean} applyPreset - is need apply preset
		 * @return {String}
		 */
		getPresetId: function(clear, applyPreset)
		{
			var presetId = this.getPreset().getCurrentPresetId();

			if ((!this.isEditEnabled() && !this.isAddPresetEnabled() && !applyPreset) ||
				(presetId === 'default_filter' && !clear))
			{
				presetId = 'tmp_filter';
			}

			return presetId;
		},

		isAppliedUserFilter: function()
		{
			const presetOptions = this.getPreset().getCurrentPresetData();
			if (BX.Type.isPlainObject(presetOptions))
			{
				const hasFields = (
					BX.Type.isArrayFilled(presetOptions.FIELDS)
					&& presetOptions.FIELDS.some((field) => {
						return !this.getPreset().isEmptyField(field);
					})
				);

				const hasAdditional = (
					BX.Type.isArrayFilled(presetOptions.ADDITIONAL)
					&& presetOptions.ADDITIONAL.some((field) => {
						return !this.getPreset().isEmptyField(field);
					})
				);

				return (
					(
						!presetOptions.IS_PINNED
						&& (
							hasFields
							|| hasAdditional
						)
					)
					|| (
						presetOptions.IS_PINNED
						&& BX.Type.isArrayFilled(presetOptions.ADDITIONAL)
					)
					|| BX.Type.isStringFilled(this.getSearch().getSearchString())
				);
			}

			return false;
		},

		isAppliedDefaultPreset: function()
		{
			const presetData = this.getPreset().getCurrentPresetData();
			if (!presetData.IS_PINNED)
			{
				return false;
			}

			if (BX.Type.isArrayFilled(presetData.ADDITIONAL))
			{
				const hasAdditional = presetData.ADDITIONAL.some((field) => {
					return !this.getPreset().isEmptyField(field);
				});

				if (hasAdditional)
				{
					return false;
				}
			}

			if (BX.Type.isStringFilled(this.getSearch().getSearchString()))
			{
				return false;
			}

			return true;
		},

		/**
		 * Applies filter
		 * @param {?Boolean} [clear] - is need reset filter
		 * @param {?Boolean} [applyPreset] - is need apply preset
		 * @param {?Boolean} [isSetOutside] - is filter sets from outside
		 * @return {BX.Promise}
		 */
		applyFilter: function(clear, applyPreset, isSetOutside)
		{
			this.setIsSetOutsideState(isSetOutside);

			var presetId = this.getPresetId(clear, applyPreset);
			var filterId = this.getParam('FILTER_ID');
			var promise = new BX.Promise(null, this);
			var Preset = this.getPreset();
			var Search = this.getSearch();
			var applyParams = {autoResolve: !this.grid};
			var self = this;

			this.setDefaultPresetAppliedState(this.isAppliedDefaultPreset());

			if (this.isAppliedUserFilter())
			{
				BX.Dom.addClass(this.getSearch().container, 'main-ui-filter-search--active');
			}
			else
			{
				BX.Dom.removeClass(this.getSearch().container, 'main-ui-filter-search--active');
			}

			this.clearGet();
			this.showGridAnimation();

			var action = clear ? "clear" : "apply";

			BX.onCustomEvent(window, 'BX.Main.Filter:beforeApply', [filterId, {action: action}, this, promise]);

			this.updatePreset(presetId, null, clear, null).then(function() {
				Search.updatePreset(Preset.getPreset(presetId));

				if (self.getParam('VALUE_REQUIRED'))
				{
					if (!Search.getSquares().length)
					{
						self.lastPromise = Preset.applyPinnedPreset();
					}
				}
			}).then(function() {
				var params = {apply_filter: 'Y', clear_nav: 'Y'};
				var fulfill = BX.delegate(promise.fulfill, promise);
				var reject = BX.delegate(promise.reject, promise);
				self.grid && self.grid.reloadTable('POST', params, fulfill, reject);
				BX.onCustomEvent(window, 'BX.Main.Filter:apply', [filterId, {action: action}, self, promise, applyParams]);
				applyParams.autoResolve && promise.fulfill();
			});

			return promise;
		},


		/**
		 * Gets add field buttons
		 * @return {?HTMLElement}
		 */
		getAddField: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddField);
		},


		/**
		 * Gets fields list container
		 * @return {?HTMLElement}
		 */
		getFieldListContainer: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFileldControlList);
		},


		/**
		 * @return {BX.Filter.Fields}
		 */
		getFields: function()
		{
			if (!(this.fields instanceof BX.Filter.Fields))
			{
				this.fields = new BX.Filter.Fields(this);
			}

			return this.fields;
		},


		/**
		 * @return {BX.Filter.Presets}
		 */
		getPreset: function()
		{
			if (!(this.presets instanceof BX.Filter.Presets))
			{
				this.presets = new BX.Filter.Presets(this);
			}

			return this.presets;
		},


		/**
		 * @param controlData
		 * @return {*}
		 */
		resetControlData: function(controlData)
		{
			if (BX.type.isPlainObject(controlData))
			{
				switch (controlData.TYPE)
				{
					case this.types.MULTI_SELECT : {
						controlData.VALUE = [];
						break;
					}

					case this.types.SELECT : {
						controlData.VALUE = controlData.ITEMS[0];
						break;
					}

					case this.types.DATE : {
						controlData.SUB_TYPE = controlData.SUB_TYPES[0];
						controlData.VALUES = {
							'_from': '',
							'_to': '',
							'_days': '',
							'_quarter': '',
							'_year': ''
						};
						break;
					}

					case this.types.CUSTOM_DATE : {
						controlData.VALUES = {
							'days': [],
							'months': [],
							'years': []
						};
						break;
					}

					case this.types.NUMBER : {
						controlData.SUB_TYPE = controlData.SUB_TYPES[0];
						controlData.VALUES = {
							'_from': '',
							'_to': ''
						};
						break;
					}

					case this.types.DEST_SELECTOR:
					case this.types.ENTITY_SELECTOR:
					case this.types.CUSTOM_ENTITY: {
						controlData.VALUES = {
							'_label': '',
							'_value': ''
						};
						break;
					}

					case this.types.CUSTOM : {
						controlData._VALUE = '';
						break;
					}

					default : {
						controlData.VALUE = '';
					}
				}
			}

			return controlData;
		},


		clearControl: function(name)
		{
			var control = this.getPreset().getField({NAME: name});
			var controlData, newControl;

			if (BX.type.isDomNode(control))
			{
				controlData = this.getFieldByName(name);
				controlData = this.resetControlData(controlData);

				newControl = this.getPreset().createControl(controlData);
				BX.insertAfter(newControl, control);
				BX.remove(control);
			}
		},

		clearControls: function(squareData)
		{
			if (BX.type.isArray(squareData))
			{
				squareData.forEach(function(item) {
					'name' in item && this.clearControl(item.name);
				}, this);
			}

			else if (BX.type.isPlainObject(squareData) && 'name' in squareData)
			{
				this.clearControl(squareData.name);
			}
		},


		/**
		 * Gets filter popup template
		 * @return {?string}
		 */
		getTemplate: function()
		{
			return BX.html(BX(this.settings.generalTemplateId));
		},

		isIe: function()
		{
			if (!BX.type.isBoolean(this.ie))
			{
				this.ie = BX.hasClass(document.documentElement, 'bx-ie');
			}

			return this.ie;
		},


		/**
		 * Closes filter popup
		 */
		closePopup: function()
		{
			var popup = this.getPopup();
			var popupContainer = popup.popupContainer;
			var configCloseDelay = this.settings.get('FILTER_CLOSE_DELAY');
			var closeDelay;

			BX.Dom.removeClass(this.getSearch().container, 'main-ui-filter-search--showed');

			setTimeout(BX.delegate(function() {

				if (!this.isIe())
				{
					BX.removeClass(popupContainer, this.settings.classAnimationShow);
					BX.addClass(popupContainer, this.settings.classAnimationClose);

					closeDelay = parseFloat(BX.style(popupContainer, 'animation-duration'));

					if (BX.type.isNumber(closeDelay))
					{
						closeDelay = closeDelay * 1000;
					}

					setTimeout(function() {
						popup.close();
					}, closeDelay);
				}
				else
				{
					popup.close();
				}
			}, this), configCloseDelay);

			if (this.getParam("LIMITS_ENABLED"))
			{
				BX.removeClass(this.getFilter(), this.settings.classLimitsAnimation);
			}

			this.closeFieldListPopup();
			this.adjustFocus();
		},


		/**
		 * Shows filter popup
		 */
		showPopup: function()
		{
			var popup = this.getPopup();
			var popupContainer;

			if (!popup.isShown())
			{
				BX.Dom.addClass(this.getSearch().container, 'main-ui-filter-search--showed');

				this.isOpened = true;
				var showDelay = this.settings.get('FILTER_SHOW_DELAY');

				if (this.getParam('LIMITS_ENABLED') === true)
				{
					this.limitAnalyticsSend();
				}

				setTimeout(BX.delegate(function() {
					popup.show();

					if (!this.isIe())
					{
						popupContainer = popup.popupContainer;
						BX.removeClass(popupContainer, this.settings.classAnimationClose);
						BX.addClass(popupContainer, this.settings.classAnimationShow);
						BX.onCustomEvent(window, "BX.Main.Filter:show", [this]);
					}

					var textareas = [].slice.call(
						this.getFieldListContainer().querySelectorAll('textarea')
					);

					textareas.forEach(function(item) {
						BX.style(item, 'height', item.scrollHeight + 'px');
					});
				}, this), showDelay);
			}
		},


		/**
		 * Gets save for all checkbox element
		 * @return {?HTMLInputElement}
		 */
		getSaveForAllCheckbox: function()
		{
			if (!this.saveForAllCheckbox && !!this.getSaveForAllCheckboxContainer())
			{
				this.saveForAllCheckbox = BX.Filter.Utils.getBySelector(this.getSaveForAllCheckboxContainer(), 'input[type="checkbox"]');
			}

			return this.saveForAllCheckbox;
		},


		/**
		 * Gets save for all checkbox container
		 * @return {?HTMLElement}
		 */
		getSaveForAllCheckboxContainer: function()
		{
			if (!this.saveForAllCheckboxContainer)
			{
				this.saveForAllCheckboxContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
			}

			return this.saveForAllCheckboxContainer;
		},


		/**
		 * Shows for all checkbox
		 */
		showForAllCheckbox: function()
		{
			!!this.getSaveForAllCheckboxContainer() &&
				BX.removeClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
		},


		/**
		 * Hides for all checkbox
		 */
		hideForAllCheckbox: function()
		{
			!!this.getSaveForAllCheckboxContainer() &&
				BX.addClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
		},


		/**
		 * Gets popup bind element
		 * @return {?HTMLElement}
		 */
		getPopupBindElement: function()
		{
			if (!this.popupBindElement)
			{
				var selector = this.settings.get('POPUP_BIND_ELEMENT_SELECTOR');
				var result = null;

				if (BX.type.isNotEmptyString(selector))
				{
					result = BX.Filter.Utils.getBySelector(document, selector);
				}

				this.popupBindElement = !!result ? result : this.getSearch().getContainer();
			}

			return this.popupBindElement;
		},


		/**
		 * Gets filter popup window instance
		 * @return {BX.PopupWindow}
		 */
		getPopup: function()
		{
			if (!(this.popup instanceof BX.PopupWindow))
			{
				this.popup =  new BX.PopupWindow(
					this.getParam('FILTER_ID') + this.settings.searchContainerPostfix,
					this.getPopupBindElement(),
					{
						autoHide : false,
						offsetTop : parseInt(this.settings.get('POPUP_OFFSET_TOP')),
						offsetLeft : parseInt(this.settings.get('POPUP_OFFSET_LEFT')),
						lightShadow : true,
						closeIcon : false,
						closeByEsc : false,
						noAllPaddings: true,
						zIndex: 12
					}
				);

				this.popup.setContent(this.getTemplate());
				BX.bind(this.getFieldListContainer(), 'keydown', BX.delegate(this._onFieldsContainerKeydown, this));
				BX.bind(this.getFilter(), 'click', BX.delegate(this._onFilterClick, this));
				BX.bind(this.getAddPresetButton(), 'click', BX.delegate(this._onAddPresetClick, this));
				BX.bind(this.getPreset().getAddPresetFieldInput(), 'keydown', BX.delegate(this._onAddPresetKeydown, this));
				BX.bind(this.getPreset().getContainer(), 'keydown', BX.delegate(this._onPresetInputKeydown, this));
				BX.bind(this.getSaveButton(), 'click', BX.delegate(this._onSaveButtonClick, this));
				BX.bind(this.getCancelButton(), 'click', BX.delegate(this._onCancelButtonClick, this));
				BX.bind(this.getFindButton(), 'click', BX.delegate(this._onFindButtonClick, this));
				BX.bind(this.getResetButton(), 'click', BX.delegate(this._onResetButtonClick, this));
				BX.bind(this.getAddField(), 'click', BX.delegate(this._onAddFieldClick, this));
				BX.bind(this.getEditButton(), 'click', BX.delegate(this._onEditButtonClick, this));
				BX.bind(this.getRestoreButton(), 'click', BX.delegate(this._onRestoreButtonClick, this));
				BX.bind(this.getRestoreFieldsButton(), 'click', BX.delegate(this._onRestoreFieldsButtonClick, this));
				this.getFilter().addEventListener('mousedown', BX.delegate(this._onFilterMousedown, this), true);
				this.getPreset().showCurrentPresetFields();
				this.getPreset().bindOnPresetClick();
			}

			return this.popup;
		},

		_onRestoreFieldsButtonClick: function()
		{
			this.restoreDefaultFields();
		},


		/**
		 * Restores default fields list
		 */
		restoreDefaultFields: function()
		{
			var defaultPreset = this.getPreset().getPreset('default_filter', true);
			var presets = this.getParam('PRESETS');
			var currentPresetId = this.getPreset().getCurrentPresetId();
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var fields = defaultPreset.FIELDS.map(function(curr) { return curr.NAME; });
			var rows = fields.join(',');

			presets.forEach(function(current, index) {
				if (current.ID === 'default_filter')
				{
					presets[index] = BX.clone(defaultPreset);
				}
			}, this);

			if (BX.type.isArray(this.editablePresets))
			{
				this.editablePresets.forEach(function(current, index) {
					if (current.ID === 'default_filter')
					{
						this.editablePresets[index] = BX.clone(defaultPreset);
					}
				}, this);
			}

			this.getPreset().applyPreset(currentPresetId);
			this.updatePreset(currentPresetId);
			this.saveOptions({preset_id: "default_filter", rows: rows, save: "Y", apply_filter: "N"}, params);
		},


		/**
		 * Gets restore default fields button
		 * @return {?HTMLElement}
		 */
		getRestoreFieldsButton: function()
		{
			if (!this.restoreFieldsButton)
			{
				this.restoreFieldsButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreFieldsButton);
			}

			return this.restoreFieldsButton;
		},


		/**
		 * Restores filter
		 */
		restoreFilter: function()
		{
			var defaultPresets = this.getParam('DEFAULT_PRESETS');
			var allPresets = this.getParam('PRESETS');
			var isReplace = false;
			var replaceIndex, applyPresetId, presetNode;

			if (BX.type.isArray(defaultPresets))
			{
				defaultPresets.sort(function(a, b) {
					return a.SORT - b.SORT;
				});

				defaultPresets.forEach(function(defPreset) {
					isReplace = allPresets.some(function(current, index) {
						if (current.ID === defPreset.ID)
						{
							replaceIndex = index;
							return true;
						}
					});

					if (isReplace)
					{
						allPresets[replaceIndex] = BX.clone(defPreset);
					}
					else
					{
						allPresets.push(BX.clone(defPreset));
					}

					if (defPreset.ID !== 'default_filter')
					{
						this.addSidebarItem(defPreset.ID, defPreset.TITLE, defPreset.IS_PINNED);

						if (defPreset.IS_PINNED)
						{
							applyPresetId = defPreset.ID;
						}
					}
				}, this);
			}

			this.saveRestoreFilter();
			this.disableAddPreset();
			this.disableEdit();

			if (!applyPresetId)
			{
				applyPresetId = "default_filter";
			}

			presetNode = this.getPreset().getPresetNodeById(applyPresetId);

			if (presetNode)
			{
				BX.fireEvent(presetNode, 'click');
			}
		},

		saveRestoreFilter: function()
		{
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'RESTORE_FILTER'};
			var presets = this.getParam('PRESETS');
			var data = {};
			var rows;

			if (BX.type.isArray(presets))
			{
				presets.forEach(function(current) {
					rows = current.FIELDS.map(function(field) {
						return field.NAME;
					});
					rows = rows.join(',');
					data[current.ID] = {
						name: current.TITLE || null,
						sort: current.SORT,
						preset_id: current.ID,
						fields:  this.prepareFields(current.FIELDS),
						rows: rows,
						for_all: current.FOR_ALL
					};
				}, this);

				this.saveOptions(data, params);
			}
		},


		/**
		 * Prepares fields
		 * @param {object[]} fields
		 * @return {object}
		 */
		prepareFields: function(fields)
		{
			var result = {};
			var valuesKeys;

			if (BX.type.isArray(fields))
			{
				fields.forEach(function(current) {
					if (current.TYPE === this.types.SELECT)
					{
						result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
					}

					if (current.TYPE === this.types.MULTI_SELECT)
					{
						current.VALUE.forEach(function(val, i) {
							result[current.NAME] = result[current.NAME] || {};
							result[current.NAME][i] = val.VALUE;
						});

						result[current.NAME] = result[current.NAME] || '';
					}

					if (current.TYPE === this.types.DATE ||
						current.TYPE === this.types.NUMBER)
					{
						valuesKeys = Object.keys(current.VALUES);

						valuesKeys.forEach(function(key) {
							result[current.NAME + key] = current.VALUES[key];
						});

						if (current.TYPE === this.types.DATE)
						{
							result[current.NAME + '_datesel'] = 'VALUE' in current.SUB_TYPE ?
								current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
						}

						if (current.TYPE === this.types.NUMBER)
						{
							result[current.NAME + '_numsel'] = 'VALUE' in current.SUB_TYPE ?
								current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
						}
					}

					if (
						current.TYPE === this.types.DEST_SELECTOR
						|| current.TYPE === this.types.ENTITY_SELECTOR
						|| current.TYPE === this.types.CUSTOM_ENTITY
					)
					{
						result[current.NAME + '_label'] = current.VALUES._label;
						result[current.NAME + '_value'] = current.VALUES._value;
					}
				}, this);
			}

			return result;
		},


		/**
		 * Gets restore button
		 * @return {?HTMLElement}
		 */
		getRestoreButton: function()
		{
			if (!BX.type.isDomNode(this.restoreButton))
			{
				this.restoreButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreButton);
			}

			return this.restoreButton;
		},

		_onPresetInputKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT')
			{
				BX.fireEvent(this.getSaveButton(), 'click');
			}
		},

		_onFieldsContainerKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT')
			{
				BX.fireEvent(this.getFindButton(), 'click');
			}
		},

		_onFindButtonClick: function()
		{
			this.setIsSetOutsideState(false);
			var presets = this.getPreset();
			var currentPresetId = presets.getCurrentPresetId();
			var promise;

			if (
				currentPresetId !== 'tmp_filter'
				&& currentPresetId !== 'default_filter'
				&& !presets.isPresetValuesModified(currentPresetId)
			)
			{
				var preset = presets.getPreset(currentPresetId);
				var additional = presets.getAdditionalValues(currentPresetId);
				var rows = presets.getFields().map(function(current) {
					return BX.data(current, 'name');
				});

				preset.ADDITIONAL = this.preparePresetFields(additional, rows);
				preset.ADDITIONAL = preset.ADDITIONAL.filter(function(field) {
					return !this.getPreset().isEmptyField(field);
				}, this);

				promise = this.applyFilter(false, currentPresetId);
				this.closePopup();
			}
			else
			{
				presets.deactivateAllPresets();
				promise = this.applyFilter();
				this.closePopup();
			}

			return promise;
		},

		_onResetButtonClick: function()
		{
			if (this.getParam('VALUE_REQUIRED'))
			{
				var preset = this.getPreset().getCurrentPresetData();

				if (preset.ADDITIONAL.length)
				{
					this.closePopup();
				}

				BX.fireEvent(this.getSearch().getClearButton(), 'click');
			}
			else
			{
				if (this.getParam('RESET_TO_DEFAULT_MODE'))
				{
					this.getSearch().clearInput();
					this.getPreset().applyPinnedPreset();
				}
				else
				{
					this.resetFilter();
				}

				this.closePopup();
			}
		},


		/**
		 * @param withoutSearch
		 * @return {BX.Promise}
		 */
		resetFilter: function(withoutSearch)
		{
			var Search = this.getSearch();
			var Presets = this.getPreset();

			if (!withoutSearch)
			{
				Search.clearInput();
			}

			Search.removePreset();
			Presets.deactivateAllPresets();
			Presets.resetPreset(true);
			Search.hideClearButton();
			Search.adjustPlaceholder();
			return this.applyFilter(true, true);
		},

		_onEditButtonClick: function()
		{
			if (!this.isEditEnabled())
			{
				this.enableEdit();
			}
			else
			{
				this.disableEdit();
			}
		},


		/**
		 * Enables fields drag and drop
		 */
		enableFieldsDragAndDrop: function()
		{
			var fields = this.getPreset().getFields();

			this.fieldsList = [];

			if (BX.type.isArray(fields))
			{
				this.fieldsList = fields.map(this.registerDragItem, this);
			}
		},


		/**
		 * Register drag item
		 * @param {HTMLElement} item
		 * @return {HTMLElement}
		 */
		registerDragItem: function(item)
		{
			var dragButton = this.getDragButton(item);

			if (dragButton)
			{
				dragButton.onbxdragstart = BX.delegate(this._onFieldDragStart, this);
				dragButton.onbxdragstop = BX.delegate(this._onFieldDragStop, this);
				dragButton.onbxdrag = BX.delegate(this._onFieldDrag, this);
				jsDD.registerObject(dragButton);
				jsDD.registerDest(dragButton);
			}

			return item;
		},


		/**
		 * Unregister drag item
		 * @param {HTMLElement} item
		 */
		unregisterDragItem: function(item)
		{
			var dragButton = this.getDragButton(item);

			if (dragButton)
			{
				jsDD.unregisterObject(dragButton);
				jsDD.unregisterDest(dragButton);
			}
		},

		_onFieldDragStart: function()
		{
			this.dragItem = this.getFields().getField(jsDD.current_node);
			this.dragIndex = BX.Filter.Utils.getIndex(this.fieldsList, this.dragItem);
			this.dragRect = this.dragItem.getBoundingClientRect();
			this.offset = this.dragRect.height;
			this.dragStartOffset = (jsDD.start_y - (this.dragRect.top + BX.scrollTop(window)));

			BX.Filter.Utils.styleForEach(this.fieldsList, {'transition': '100ms'});
			BX.addClass(this.dragItem, this.settings.classPresetOndrag);
			BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
		},

		_onFieldDragStop: function()
		{
			BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
			BX.removeClass(this.dragItem, this.settings.classPresetOndrag);

			BX.Filter.Utils.styleForEach(this.fieldsList, {'transition': '', 'transform': ''});
			BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);

			this.fieldsList = this.getPreset().getFields();

			this.saveFieldsSort();
		},

		_onFieldDrag: function()
		{
			var self = this;
			var currentRect, currentMiddle;

			this.dragOffset = (this.realY - this.dragRect.top - this.dragStartOffset);
			this.sortOffset = self.realY + BX.scrollTop(window);

			BX.Filter.Utils.styleForEach([this.dragItem], {
				'transition': '0ms',
				'transform': 'translate3d(0px, '+this.dragOffset+'px, 0px)'
			});

			this.fieldsList.forEach(function(current, index) {
				if (current)
				{
					currentRect = current.getBoundingClientRect();
					currentMiddle = currentRect.top + BX.scrollTop(window) + (currentRect.height / 2);

					if (index > self.dragIndex && self.sortOffset > currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(-self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(-self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (index < self.dragIndex && self.sortOffset < currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (((index < self.dragIndex && self.sortOffset > currentMiddle) ||
						(index > self.dragIndex && self.sortOffset < currentMiddle)) &&
						current.style.transform !== 'translate3d(0px, 0px, 0px)')
					{
						if (current.style.transform !== '')
						{
							self.targetItem = current;
						}

						BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
						BX.style(current, 'transition', '300ms');
					}
				}
			});
		},


		/**
		 * Disables fields drag and drop
		 */
		disableFieldsDragAndDrop: function()
		{
			if (BX.type.isArray(this.fieldsList) && this.fieldsList.length)
			{
				this.fieldsList.map(this.unregisterDragItem, this);
			}
		},


		/**
		 * Enables presets drag and drop
		 */
		enablePresetsDragAndDrop: function()
		{
			var Preset, presets, dragButton, presetId;

			Preset = this.getPreset();
			presets = Preset.getPresets();
			this.presetsList = [];

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					presetId = Preset.getPresetId(current);

					if (!BX.hasClass(current, this.settings.classAddPresetField) &&
						presetId !== 'default_filter' &&
						!BX.hasClass(current, this.settings.classDefaultFilter))
					{
						dragButton = this.getDragButton(current);
						dragButton.onbxdragstart = BX.delegate(this._onDragStart, this);
						dragButton.onbxdragstop = BX.delegate(this._onDragStop, this);
						dragButton.onbxdrag = BX.delegate(this._onDrag, this);
						jsDD.registerObject(dragButton);
						jsDD.registerDest(dragButton);
						this.presetsList.push(current);
					}
				}, this);
			}
		},


		/**
		 * Gets drag button
		 * @param {HTMLElement} presetNode
		 * @return {?HTMLElement}
		 */
		getDragButton: function(presetNode)
		{
			return BX.Filter.Utils.getByClass(presetNode, this.settings.classPresetDragButton);
		},


		/**
		 * Disables presets drag and drop
		 */
		disablePresetsDragAndDrop: function()
		{
			if (BX.type.isArray(this.presetsList) && this.presetsList.length)
			{
				this.presetsList.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField))
					{
						jsDD.unregisterObject(current);
						jsDD.unregisterDest(current);
					}
				}, this);
			}
		},

		_onDragStart: function()
		{
			this.dragItem = this.getPreset().normalizePreset(jsDD.current_node);
			this.dragIndex = BX.Filter.Utils.getIndex(this.presetsList, this.dragItem);
			this.dragRect = this.dragItem.getBoundingClientRect();
			this.offset = this.dragRect.height;
			this.dragStartOffset = (jsDD.start_y - (this.dragRect.top + BX.scrollTop(window)));

			BX.Filter.Utils.styleForEach(this.list, {'transition': '100ms'});
			BX.addClass(this.dragItem, this.settings.classPresetOndrag);
			BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
		},

		_onMouseMove: function(event)
		{
			this.realX = event.clientX;
			this.realY = event.clientY;
		},


		/**
		 * Gets drag offset
		 * @return {number}
		 */
		getDragOffset: function()
		{
			return (jsDD.x - this.startDragOffset - this.dragRect.left);
		},

		_onDragStop: function()
		{
			var Preset, presets;

			BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
			BX.removeClass(this.dragItem, this.settings.classPresetOndrag);

			BX.Filter.Utils.styleForEach(this.presetsList, {'transition': '', 'transform': ''});
			BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);

			Preset = this.getPreset();
			presets = Preset.getPresets();
			this.presetsList = [];

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField) &&
						!BX.hasClass(current, this.settings.classDefaultFilter))
					{
						this.presetsList.push(current);
					}
				}, this);
			}

		},

		_onDrag: function()
		{
			var self = this;
			var currentRect, currentMiddle;

			this.dragOffset = (this.realY - this.dragRect.top - this.dragStartOffset);
			this.sortOffset = self.realY + BX.scrollTop(window);

			BX.Filter.Utils.styleForEach([this.dragItem], {
				'transition': '0ms',
				'transform': 'translate3d(0px, '+this.dragOffset+'px, 0px)'
			});

			this.presetsList.forEach(function(current, index) {
				if (current)
				{
					currentRect = current.getBoundingClientRect();
					currentMiddle = currentRect.top + BX.scrollTop(window) + (currentRect.height / 2);

					if (index > self.dragIndex && self.sortOffset > currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(-self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(-self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (index < self.dragIndex && self.sortOffset < currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (((index < self.dragIndex && self.sortOffset > currentMiddle) ||
						(index > self.dragIndex && self.sortOffset < currentMiddle)) &&
						current.style.transform !== 'translate3d(0px, 0px, 0px)')
					{
						if (current.style.transform !== '')
						{
							self.targetItem = current;
						}

						BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
						BX.style(current, 'transition', '300ms');
					}
				}
			});
		},


		/**
		 * Gets sidebar controls container
		 * @return {?HTMLElement}
		 */
		getSidebarControlsContainer: function()
		{
			if (!BX.type.isDomNode(this.sidebarControlsContainer))
			{
				this.sidebarControlsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSidebarControlsContainer);
			}

			return this.sidebarControlsContainer;
		},


		/**
		 * Enables edit mode
		 */
		enableEdit: function()
		{
			var Preset = this.getPreset();
			var presets = Preset.getPresets();
			var presetId;

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					presetId = Preset.getPresetId(current);
					if (!BX.hasClass(current, this.settings.classAddPresetField) && presetId !== 'default_filter')
					{
						BX.addClass(current, this.settings.classPresetEdit);
					}
				}, this);
			}

			this.enablePresetsDragAndDrop();
			BX.show(this.getButtonsContainer());
			BX.hide(this.getPresetButtonsContainer());
			BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
			this.editablePresets = BX.clone(this.getParam('PRESETS'));
			this.isEditEnabledState = true;
		},


		/**
		 * Disables edit mode
		 */
		disableEdit: function()
		{
			var Preset = this.getPreset();
			var presets = Preset.getPresets();

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField))
					{
						BX.removeClass(current, this.settings.classPresetEdit);
						this.getPreset().disableEditPresetName(current);
					}
				}, this);
			}

			this.disablePresetsDragAndDrop();

			if (!this.isAddPresetEnabled())
			{
				BX.style(this.getButtonsContainer(), 'display', '');
			}

			BX.show(this.getPresetButtonsContainer());
			BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
			this.editablePresets = null;
			this.isEditEnabledState = false;
			this.applyFilter(null, true);
		},


		/**
		 * Get preset buttons container
		 * @return {?HTMLElement}
		 */
		getPresetButtonsContainer: function()
		{
			if (!BX.type.isDomNode(this.presetButtonsContainer))
			{
				this.presetButtonsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classPresetButtonsContainer);
			}

			return this.presetButtonsContainer;
		},


		/**
		 * Checks is edit mode enabled
		 * @return {boolean}
		 */
		isEditEnabled: function()
		{
			return this.isEditEnabledState;
		},


		/**
		 * Gets edit button element
		 * @return {?HTMLElement}
		 */
		getEditButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classEditButton);
		},


		/**
		 * Gets component param by param name
		 * @param {string} paramName
		 * @param {*} [defaultValue] - Be returns if param with paramName not set
		 * @returns {*}
		 */
		getParam: function(paramName, defaultValue)
		{
			return paramName in this.params ? this.params[paramName] : defaultValue;
		},


		/**
		 * Gets container of filter popup
		 * @returns {HTMLElement|null}
		 */
		getFilter: function()
		{
			return BX.Filter.Utils.getByClass(this.getPopup().contentContainer, this.settings.classFilterContainer);
		},


		/**
		 * @returns {BX.Filter.Search}
		 */
		getSearch: function()
		{
			if (!(this.search instanceof BX.Filter.Search))
			{
				this.search = new BX.Filter.Search(this);
			}

			return this.search;
		},

		_onRestoreButtonClick: function()
		{
			var action = {
				CONFIRM: true,
				CONFIRM_MESSAGE: this.getParam('CONFIRM_MESSAGE'),
				CONFIRM_APPLY_BUTTON: this.getParam('CONFIRM_APPLY'),
				CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
			};

			this.confirmDialog(action, BX.delegate(this.restoreFilter, this));
		},


		/**
		 * Shows confirmation popup
		 * @param {object} action - Popup properties
		 * @param {boolean} action.CONFIRM - true If the user must confirm the action
		 * @param {string} action.CONFIRM_MESSAGE - Message of confirm popup
		 * @param {string} action.CONFIRM_APPLY_BUTTON - Text of apply button
		 * @param {string} action.CONFIRM_CANCEL_BUTTON - Text of cancel button
		 * @param {string} [action.CONFIRM_TITLE] - Title of confirm popup
		 * @param {function} then - Callback after a successful confirmation
		 * @param {function} [cancel] - callback after cancel confirmation
		 */
		confirmDialog: function(action, then, cancel)
		{
			if ('CONFIRM' in action && action.CONFIRM)
			{
				var dialogId = this.getParam('FILTER_ID') + '-confirm-dialog';
				var popupMessage = '<div class="main-ui-filter-confirm-content">'+action.CONFIRM_MESSAGE+'</div>';
				var popupTitle = 'CONFIRM_TITLE' in action ? action.CONFIRM_TITLE : '';

				var applyButton = new BX.PopupWindowButton({
					text: action.CONFIRM_APPLY_BUTTON,
					events: {
						click: function()
						{
							BX.type.isFunction(then) ? then() : null;
							this.popupWindow.close();
							this.popupWindow.destroy();
						}
					}
				});

				var cancelButton = new BX.PopupWindowButtonLink({
					text: action.CONFIRM_CANCEL_BUTTON,
					events: {
						click: function()
						{
							BX.type.isFunction(cancel) ? cancel() : null;
							this.popupWindow.close();
							this.popupWindow.destroy();
						}
					}
				});

				var dialog = new BX.PopupWindow(
					dialogId,
					null,
					{
						content: popupMessage,
						titleBar: popupTitle,
						autoHide: false,
						zIndex: 9999,
						overlay: 0.4,
						offsetTop: -100,
						closeIcon : true,
						closeByEsc : true,
						buttons: [applyButton, cancelButton]
					}
				);

				BX.addCustomEvent(dialog, 'onPopupClose', BX.delegate(function() {
					!!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
				}, this));

				if (!dialog.isShown())
				{
					dialog.show();
					var popupContainer = dialog.popupContainer;
					BX.removeClass(popupContainer, this.settings.classAnimationShow);
					BX.addClass(popupContainer, this.settings.classAnimationShow);
				}
			}
			else
			{
				BX.type.isFunction(then) ? then() : null;
			}
		},

		getInitialValue: function(name)
		{
			if (BX.type.isString(name))
			{
				var values = this.params.INITIAL_FILTER;

				if (BX.type.isPlainObject(values))
				{
					var filteredEntries = Object.entries(values).reduce(function(acc, item) {
						if (item[0].startsWith(name))
						{
							acc.push(item);
						}

						return acc;
					}, []);

					if (filteredEntries.length === 1)
					{
						return filteredEntries[0][1];
					}

					if (filteredEntries.length > 1)
					{
						return filteredEntries.reduce(function(acc, item) {
							acc[item[0].replace(name, '')] = item[1];
							return acc;
						}, {});
					}
				}
			}

			return '';
		},

		getField: function(name)
		{
			var node = this.getFieldListContainer()
				.querySelector('[data-name="' + name + '"]');

			return BX.Filter.Field.instances.get(node);
		},

		isSetOutside: function()
		{
			return BX.Text.toBoolean(this.isSetOutsideState);
		},

		setIsSetOutsideState: function(state)
		{
			this.isSetOutsideState = BX.Text.toBoolean(state);
			const searchContainer = this.getSearch().getContainer();
			if (this.isSetOutsideState)
			{
				BX.Dom.addClass(searchContainer, 'main-ui-filter-set-outside');
				BX.Dom.removeClass(searchContainer, 'main-ui-filter-set-inside');
			}
			else
			{
				BX.Dom.addClass(searchContainer, 'main-ui-filter-set-inside');
				BX.Dom.removeClass(searchContainer, 'main-ui-filter-set-outside');
			}
		},

		setDefaultPresetAppliedState: function(state)
		{
			this.isDefaultPresetAppliedState = BX.Text.toBoolean(state);
			const searchContainer = this.getSearch().getContainer();
			if (this.isDefaultPresetAppliedState)
			{
				BX.Dom.addClass(searchContainer, 'main-ui-filter-default-applied');
			}
			else
			{
				BX.Dom.removeClass(searchContainer, 'main-ui-filter-default-applied');
			}
		}
	};
})();


(function() {
	BX.Main.filterManager = {
		data: {},

		push: function(id, instance)
		{
			if (BX.type.isNotEmptyString(id) && instance)
			{
				this.data[id] = instance;
			}
		},

		getById: function(id)
		{
			var result = null;

			if (id in this.data)
			{
				result = this.data[id];
			}

			return result;
		},

		getList: function()
		{
			return Object.values(this.data);
		}
	};
})();
