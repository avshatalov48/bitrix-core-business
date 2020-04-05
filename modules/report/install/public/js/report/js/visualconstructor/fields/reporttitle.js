;(function(){
	"use strict";

	BX.namespace('BX.Report.VisualConstructor.FieldEventHandlers');

	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.FieldEventHandlers.Title =  function(options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);
	};

	BX.Report.VisualConstructor.FieldEventHandlers.Title.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.FieldEventHandlers.Title,
		process: function()
		{
			switch (this.action)
			{
				case 'whatWillCalculateChange':
					this.whatWillCalculateFieldChangeHandler();
					break;
				case 'groupByChange':
					this.groupByFieldChange();
					break;
			}
		},
		whatWillCalculateFieldChangeHandler: function()
		{
			this.currentFieldObject.setValue(this.ownerField.options[this.ownerField.selectedIndex].innerText);
		},
		groupByFieldChange: function ()
		{
			//this.currentField.fieldScope.style.backgroundColor = 'red';
		}

	}
})();
