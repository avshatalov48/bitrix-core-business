import {Event, Type} from 'main.core';

export class Api
{
	constructor(parent)
	{
		this.parent = parent;
	}

	setFields(fields)
	{
		if (Type.isPlainObject(fields))
		{
			this.parent.getPopup();
			const preset = this.parent.getPreset();
			preset.deactivateAllPresets();
			const data = {preset_id: 'tmp_filter', fields};
			this.parent.updateParams(data);
			preset.applyPreset('tmp_filter');
		}
	}

	setFilter(filter, analyticsLabel = null)
	{
		this.setAnalyticsLabel(analyticsLabel);

		if (Type.isObject(filter))
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
				let newFields = {};

				if (Type.isPlainObject(filter.fields))
				{
					newFields = Object.assign({}, filter.fields);
				}

				if (Type.isPlainObject(filter.additional))
				{
					newFields = Object.assign({}, filter.additional);
				}

				this.parent.getPreset().deactivateAllPresets();
				this.setFields(newFields);
				this.apply();
			}
		}
	}


	/**
	 * Extends current applied filter
	 * @param {Object.<String, *>} fields
	 * @param {boolean} [force = false]
	 */
	extendFilter(fields, force = false, analyticsLabel= null)
	{
		this.setAnalyticsLabel(analyticsLabel);

		if (Type.isObject(fields))
		{
			Object.keys(fields).forEach((key) => {
				if (Type.isNumber(fields[key]))
				{
					fields[key] = String(fields[key]);
				}
			});

			const currentPresetId = this.parent.getPreset().getCurrentPresetId();

			if (
				force
				|| currentPresetId === 'tmp_filter'
				|| currentPresetId === 'default_filter'
			)
			{
				const newFields = Object.assign({}, this.parent.getFilterFieldsValues(), fields);

				this.setFields(newFields);
				this.apply();

				return;
			}

			const previewsAdditionalValues = this.parent.getPreset().getAdditionalValues(currentPresetId);

			if (Type.isPlainObject(previewsAdditionalValues)
				&& Object.keys(previewsAdditionalValues).length)
			{
				fields = Object.assign({}, previewsAdditionalValues, fields);
			}

			this.setFilter({
				preset_id: currentPresetId,
				additional: fields,
				checkFields: true,
			});
		}
	}

	apply(analyticsLabel= null)
	{
		this.setAnalyticsLabel(analyticsLabel);

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

	getEmitter(): Event.EventEmitter
	{
		return this.parent.emitter;
	}

	setAnalyticsLabel(analyticsLabel = null)
	{
		if (Type.isObject(analyticsLabel))
		{
			this.parent.analyticsLabel = analyticsLabel;
		}
	}
}