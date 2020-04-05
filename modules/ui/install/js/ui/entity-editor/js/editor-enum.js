BX.namespace("BX.UI");

//region ENTITY EDITOR MODE
if(typeof BX.UI.EntityEditorMode === "undefined")
{
	BX.UI.EntityEditorMode =
		{
			intermediate: 0,
			edit: 1,
			view: 2,
			names: { view: "view",  edit: "edit" },
			getName: function(id)
			{
				if(id === this.edit)
				{
					return this.names.edit;
				}
				else if(id === this.view)
				{
					return this.names.view;
				}
				return "";
			},
			parse: function(str)
			{
				str = str.toLowerCase();
				if(str === this.names.edit)
				{
					return this.edit;
				}
				else if(str === this.names.view)
				{
					return this.view;
				}
				return this.intermediate;
			}
		};
}
//endregion

//region ENTITY EDITOR VISIBILITY POLICY
if(typeof BX.UI.EntityEditorVisibilityPolicy === "undefined")
{
	BX.UI.EntityEditorVisibilityPolicy =
		{
			always: 0,
			view: 1,
			edit: 2,
			parse: function(str)
			{
				str = str.toLowerCase();
				if(str === "view")
				{
					return this.view;
				}
				else if(str === "edit")
				{
					return this.edit;
				}
				return this.always;
			},
			checkVisibility: function(control)
			{
				var mode = control.getMode();
				var policy = control.getVisibilityPolicy();

				if(policy === this.view)
				{
					return mode === BX.UI.EntityEditorMode.view;
				}
				else if(policy === this.edit)
				{
					return mode === BX.UI.EntityEditorMode.edit;
				}
				return true;
			}
		};
}
//endregion

//region ENTITY EDITOR MODE OPTIONS
if(typeof BX.UI.EntityEditorModeOptions === "undefined")
{
	BX.UI.EntityEditorModeOptions =
		{
			none:       0x0,
			exclusive:  0x1,
			individual: 0x2,
			saveOnExit: 0x40,
			check: function(options, option)
			{
				return((options & option) === option);
			}
		};
}
//endregion

//region EDITOR MODE SWITCH TYPE
if(typeof BX.UI.EntityEditorModeSwitchType === "undefined")
{
	BX.UI.EntityEditorModeSwitchType =
		{
			none:       0x0,
			common:     0x1,
			button:     0x2,
			content:    0x4,
			check: function(options, option)
			{
				return((options & option) === option);
			}
		};
}
//endregion