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

if(typeof BX.UI.EntityEditorControlOptions === "undefined")
{
	BX.UI.EntityEditorControlOptions =
		{
			none: 0,
			showAlways: 1,
			check: function(options, option)
			{
				return((options & option) === option);
			}
		};
}

if(typeof BX.UI.EntityEditorPriority === "undefined")
{
	BX.UI.EntityEditorPriority =
		{
			undefined: 0,
			normal: 1,
			high: 2
		};
}

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

//region EDITOR ACTIONS
if(typeof BX.UI.EntityEditorActionIds === "undefined")
{
	BX.UI.EntityEditorActionIds =
		{
			defaultActionId: 'DEFAULT',
			cancelActionId: 'CANCEL',
		};
}

if(typeof BX.UI.EntityEditorActionTypes === "undefined")
{
	BX.UI.EntityEditorActionTypes =
		{
			save: 'save',
			direct: 'direct',
		};
}
//endregion

if(typeof BX.UI.EditorFileStorageType === "undefined")
{
	BX.UI.EditorFileStorageType =
		{
			undefined: 0,
			file: 1,
			webdav: 2,
			diskfile: 3
		};
}
if(typeof BX.UI.EntityFieldAttributeType === "undefined")
{
	BX.UI.EntityFieldAttributeType =
		{
			undefined:  0,
			hidden:     1,
			readonly:   2,
			required:   3
		};
}
if(typeof BX.UI.EntityFieldAttributePhaseGroupType === "undefined")
{
	BX.UI.EntityFieldAttributePhaseGroupType =
		{
			undefined:  0,
			general:    1,
			pipeline:   2,
			junk:       3
		};
}