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
				this.parent.applyFilter(false, filter.preset_id);
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