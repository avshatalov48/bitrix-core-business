;(function() {
	'use strict';

	BX.namespace('BX.Filter');

	BX.Filter.Api = function(parent)
	{
		this.parent = parent;
	};

	//noinspection JSUnusedGlobalSymbols
	BX.Filter.Api.prototype = {
		setFields: function(fields)
		{
			var Preset, data;

			if (BX.type.isPlainObject(fields))
			{
				this.parent.getPopup();
				Preset = this.parent.getPreset();
				Preset.deactivateAllPresets();
				data = {preset_id: 'tmp_filter', fields: fields};
				this.parent.updateParams(data);
				Preset.applyPreset('tmp_filter');
			}
		},

		setFilter: function(filter)
		{
			if (typeof filter === "object")
			{
				this.parent.updateParams(filter);
				this.parent.getPreset().deactivateAllPresets();
				this.parent.getPreset().activatePreset(filter.preset_id);
				this.parent.getPreset().applyPreset(filter.preset_id);

				if (!filter.checkFields || !this.parent.getPreset().isPresetValuesModified(filter.preset_id))
				{
					this.parent.applyFilter(false, filter.preset_id);
				}
				else
				{
					var newFields = {};

					if (BX.type.isPlainObject(filter.fields))
					{
						newFields = Object.assign({}, filter.fields);
					}

					if (BX.type.isPlainObject(filter.additional))
					{
						newFields = Object.assign({}, filter.additional);
					}

					this.parent.getPreset().deactivateAllPresets();
					this.setFields(newFields);
					this.apply();
				}
			}
		},


		/**
		 * Extends current applied filter
		 * @param {Object.<String, *>} fields
		 */
		extendFilter: function(fields)
		{
			if (!!fields && typeof fields === "object")
			{
				Object.keys(fields).forEach(function(key) {
					if (BX.type.isNumber(fields[key]))
					{
						fields[key] = "" + fields[key];
					}
				});

				var currentPresetId = this.parent.getPreset().getCurrentPresetId();

				if (currentPresetId === "tmp_filter" ||
					currentPresetId === "default_filter")
				{
					var newFields = Object.assign({}, this.parent.getFilterFieldsValues(), fields);

					this.setFields(newFields);
					this.apply();

					return;
				}

				var previewsAdditionalValues = this.parent.getPreset().getAdditionalValues(currentPresetId);

				if (BX.type.isPlainObject(previewsAdditionalValues) &&
					Object.keys(previewsAdditionalValues).length)
				{
					fields = Object.assign({}, previewsAdditionalValues, fields);
				}

				this.setFilter({
					preset_id: currentPresetId,
					additional: fields,
					checkFields: true
				});
			}
		},

		apply: function()
		{
			if (!this.parent.isEditEnabled())
			{
				if (!this.parent.isEditEnabled())
				{
					this.parent.applyFilter();
				}

				this.parent.closePopup();

				if (this.parent.isAddPresetEnabled())
				{
					this.parent.disableAddPreset();
				}
			}
		}
	};
})();