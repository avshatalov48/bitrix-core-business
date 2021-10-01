;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.SelectWithPopup = function(options)
	{
		console.trace("new SelectWithPopup");
		this.fieldScope = options.fieldScope;
		this.selectControl = this.fieldScope.querySelector('[data-role="visualconstructor-field-custom-select"]');
		this.selectControlValue = this.fieldScope.querySelector('[data-role="visualconstructor-field-custom-select-value"]');
		this.optionList = options.optionList;
		this.value = options.value;
		this.id = this.fieldScope.id;
		this.init();
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.SelectWithPopup.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.SelectWithPopup,
		init: function()
		{
			BX.bind(this.selectControl, "click", BX.delegate(this.openSelectPopup, this));
		},
		openSelectPopup: function()
		{
			this.getSelectPopup().show();
		},
		getSelectPopup: function()
		{
			if (this.selectPopup)
			{
				this.selectPopup.destroy();

			}
			var popupMenuId = this.id + '_select_popup';

			this.popupContent = this.getSelectableOptionListContainer(this.optionList, this.handlerOptionChange);


			this.selectPopup = this.createPopup(popupMenuId, this.selectControl, this.popupContent);
			return this.selectPopup;
		},
		createPopup: function(popupMenuId, control, content)
		{
			return new BX.PopupWindow(popupMenuId, control, {
				noAllPaddings: true,
				closeByEsc: true,
				angle: true,
				autoHide: true,
				zIndex: 9999,
				offsetTop: 0,
				content: content,
				targetContainer: document.querySelector('[data-role="report-configuration-page-wrapper"]'),
			});
		},
		getSelectableOptionListContainer: function(items, clickCallback)
		{
			var container = BX.create('div', {
				attrs: {
					className: 'visualconstructor-select-popup-wrapper'
				}
			});
			for (var i in items)
			{
				if (items.hasOwnProperty(i))
				{
					container.appendChild(BX.create('div', {
						attrs: {
							className: 'visualconstructor-select-item-wrapper'
						},
						dataset: {
							selectionValue: i,
							selectionLabel: items[i]
						},
						text: items[i],
						events: {
							click: function (e)
							{
								clickCallback.call(this, e.currentTarget.dataset.selectionValue, e.currentTarget.dataset.selectionLabel);
							}.bind(this)
						}
					}));
				}
			}

			return container;
		},
		handlerOptionChange: function(selectionValue, selectionLabel)
		{
			if (this.selectPopup)
			{
				this.selectPopup.close();
			}
			this.selectControl.innerHTML = selectionLabel;
			this.selectControlValue.value = selectionValue;
			this.value = selectionValue;
			BX.onCustomEvent(this.fieldScope, this.id + '_onChange', [this]);
		},
		getValue: function()
		{
			return this.value;
		},
		setOptions: function(options)
		{
			this.optionList = options;
			var firstOptionName = Object.keys(this.optionList)[0];
			if (firstOptionName)
			{
				this.handlerOptionChange(firstOptionName, this.optionList[firstOptionName]);
				/*this.selectControl.innerHTML = this.optionList[firstOptionName];
				this.selectControlValue.value = firstOptionName;
				this.value = firstOptionName;*/
			}
			else
			{
				this.selectControl.innerHTML = '';
				this.selectControlValue.value = '';
				this.value = null;
			}

		}

	}
})();