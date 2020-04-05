;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.TimePeriod = function(options)
	{
		this.fieldScope = options.fieldScope;
		this.typeControl = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-type"]');
		this.typeControlValue = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-type-value"]');
		this.yearControl = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-year"]');
		this.yearControlValue = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-year-value"]');
		this.monthControl = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-month"]');
		this.monthControlValue = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-month-value"]');
		this.quarterControl = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-quarter"]');
		this.quarterControlValue = this.fieldScope.querySelector('[data-role="visualconstructor-field-time-period-quarter-value"]');
		this.typeList = options.typeList;
		this.yearList = options.yearList;
		this.monthList = options.monthList;
		this.quarterList = options.quarterList;
		this.id = this.fieldScope.id;
		this.init();
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.TimePeriod.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.TimePeriod,
		init: function()
		{
			BX.bind(this.typeControl, "click", BX.delegate(this.openTypeSelectPopup, this));
			BX.bind(this.yearControl, "click", BX.delegate(this.openYearSelectPopup, this));
			BX.bind(this.monthControl, "click", BX.delegate(this.openMonthSelectPopup, this));
			BX.bind(this.quarterControl, "click", BX.delegate(this.openQuarterSelectPopup, this));
		},
		openTypeSelectPopup: function()
		{
			this.getTypeSelectPopup().show();
		},
		openYearSelectPopup: function()
		{
			this.getYearSelectPopup().show();
		},
		openMonthSelectPopup: function()
		{
			this.getMonthSelectPopup().show();
		},
		openQuarterSelectPopup: function()
		{
			this.getQuarterSelectPopup().show();
		},
		getTypeSelectPopup: function()
		{
			if (!this.typeSelectPopup)
			{
				var popupMenuId = this.id + 'type_select_popup';
				var content = this.getSelectableTypeListContainer(this.typeList, this.handlerTypeChange);
				this.typeSelectPopup = this.createPopup(popupMenuId, this.typeControl, content, {
					offsetLeft: 10
				});
			}
			return this.typeSelectPopup;
		},
		getYearSelectPopup: function()
		{
			if (!this.yearSelectPopup)
			{
				var popupMenuId = this.id + 'year_select_popup';
				var content = this.getSelectableTypeListContainer(this.yearList, this.handlerYearChange);
				this.yearSelectPopup = this.createPopup(popupMenuId, this.yearControl, content, {
					offsetLeft: 10
				});
			}
			return this.yearSelectPopup;
		},
		getMonthSelectPopup: function()
		{
			if (!this.monthSelectPopup)
			{
				var popupMenuId = this.id + 'month_select_popup';
				var content = this.getSelectableTypeListContainer(this.monthList, this.handlerMonthChange);
				this.monthSelectPopup = this.createPopup(popupMenuId, this.monthControl, content, {
					offsetLeft: 10
				});
			}
			return this.monthSelectPopup;
		},
		getQuarterSelectPopup: function()
		{
			if (!this.quarterSelectPopup)
			{
				var popupMenuId = this.id + 'quarter_select_popup';
				var content = this.getSelectableTypeListContainer(this.quarterList, this.handlerQuarterChange);
				this.quarterSelectPopup = this.createPopup(popupMenuId, this.quarterControl, content, {
					offsetLeft: 3
				});
			}
			return this.quarterSelectPopup;
		},
		createPopup: function(popupMenuId, control, content, options)
		{
			options = options || {};
			return new BX.PopupWindow(popupMenuId, control, {
				noAllPaddings: true,
				closeByEsc: true,
				angle: true,
				autoHide: true,
				zIndex: 9999,
				offsetTop: 0,
				offsetLeft: options.offsetLeft || 0,
				content: content
			});
		},
		getSelectableTypeListContainer: function(items, clickCallback)
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
								clickCallback.call(this, e.srcElement.dataset.selectionValue, e.srcElement.dataset.selectionLabel);
							}.bind(this)
						}
					}));
				}
			}

			return container;
		},
		handlerTypeChange: function(selectionValue, selectionLabel)
		{
			var visibleClass = 'report-field-time-period-sub-field-visible';

			switch (selectionValue)
			{
				case 'YEAR':
					this.monthControl.classList.remove(visibleClass);
					this.quarterControl.classList.remove(visibleClass);
					this.yearControl.classList.add(visibleClass);
					break;
				case 'MONTH':
					this.quarterControl.classList.remove(visibleClass);
					this.monthControl.classList.add(visibleClass);
					this.yearControl.classList.add(visibleClass);
					break;
				case 'QUARTER':
					this.monthControl.classList.remove(visibleClass);
					this.quarterControl.classList.add(visibleClass);
					this.yearControl.classList.add(visibleClass);
					break;
				default:
					this.yearControl.classList.remove(visibleClass);
					this.monthControl.classList.remove(visibleClass);
					this.quarterControl.classList.remove(visibleClass);
					break;
			}
			this.typeSelectPopup.close();
			this.typeControl.innerHTML = selectionLabel;
			this.typeControlValue.value = selectionValue;
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this]);
		},
		handlerYearChange: function(selectionValue, selectionLabel)
		{
			this.yearSelectPopup.close();
			this.yearControl.innerHTML = selectionLabel;
			this.yearControlValue.value = selectionValue;
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this]);
		},
		handlerMonthChange: function(selectionValue, selectionLabel)
		{
			this.monthSelectPopup.close();
			this.monthControl.innerHTML = selectionLabel;
			this.monthControlValue.value = selectionValue;
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this]);
		},
		handlerQuarterChange: function(selectionValue, selectionLabel)
		{
			this.quarterSelectPopup.close();
			this.quarterControl.innerHTML = selectionLabel;
			this.quarterControlValue.value = selectionValue;
			BX.onCustomEvent(this.fieldScope, this.id + '_onSelect', [this]);
		}

	}
})();