import {Type, Loc, Dom, Event, Tag, Browser, BookingUtil} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";

export class DateSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);
		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null
		};
		this.data = params.data || {};
		this.changeValueCallback = params.changeValueCallback;
		this.requestDataCallback = params.requestDataCallback;
		this.previewMode = params.previewMode === undefined;
		this.allowOverbooking = params.allowOverbooking;
		this.setDataConfig();
		this.displayed = true;
	}

	display(params)
	{
		params = params || {};
		this.setDateIndex(params.availableDateIndex);
		this.setCurrentDate(params.selectedValue);

		this.DOM.wrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block"></div>`);

		this.DOM.innerWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-inner"></div>`);
		if (this.data.label)
		{
			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {props : { className : 'calendar-resbook-webform-block-title'}, text: this.data.label + '*'}));
		}
		this.displayControl();
		this.shown = true;
	}

	refresh(data, params)
	{
		params = params || {};
		this.setDateIndex(params.availableDateIndex);
		this.setCurrentDate(params.selectedValue);

		this.data = data;
		Dom.adjust(this.DOM.labelWrap, {text: this.data.label + '*'});

		if (this.setDataConfig())
		{
			Dom.remove(this.DOM.controlWrap);
			this.displayControl();
		}

		if (this.style === 'line')
		{
			this.lineDateControl.refreshDateAvailability();
		}
	}

	setDataConfig()
	{
		let
			style = this.data.style === 'line' ? 'line' : 'popup', // line|popup
			start = this.data.start === 'today' ? 'today' : 'free',
			configWasChanged = this.style !== style || this.start !== start;

		this.style = style;
		this.start = start;

		return configWasChanged;
	}

	hide()
	{
		Dom.remove(this.DOM.innerWrap);
		this.DOM.innerWrap = null;
	}

	displayControl()
	{
		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-date"></div>`);

		if (this.style === 'popup')
		{
			this.DOM.controlWrap.className = 'calendar-resbook-webform-block-calendar';
			this.popupSateControl = new PopupDateSelector(
				{
					wrap: this.DOM.controlWrap,
					isDateAvailable: this.isDateAvailable.bind(this),
					onChange: function(value)
					{
						this.onChange(value);
					}.bind(this)
				});
			this.popupSateControl.build();
			this.popupSateControl.setValue(this.getValue());
		}
		else if (this.style === 'line')
		{
			this.DOM.controlWrap.className = 'calendar-resbook-webform-block-date';
			this.lineDateControl = new LineDateSelector(
				{
					wrap: this.DOM.controlWrap,
					isDateAvailable: this.isDateAvailable.bind(this),
					onChange: this.onChange.bind(this)
				}
			);
			this.lineDateControl.build();
			this.lineDateControl.setValue(this.getValue());
		}
	}

	setCurrentDate(date)
	{
		if (Type.isDate(date))
		{
			this.currentDate = date;
		}
	}

	setDateIndex(availableDateIndex)
	{
		if (Type.isPlainObject(availableDateIndex))
		{
			this.availableDateIndex = availableDateIndex;
		}
	}

	isDateLoaded(date)
	{
		if (Type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex)
		{
			if (this.availableDateIndex[BookingUtil.formatDate(null, date)] !== undefined)
			{
				return true;
			}

			if (Type.isFunction(this.requestDataCallback))
			{
				this.requestDataCallback({date: date});
			}
		}
		return false;
	}

	isDateAvailable(date)
	{
		if (this.previewMode || this.allowOverbooking)
		{
			return true;
		}

		if (Type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex)
		{
			let dateKey = BookingUtil.formatDate(null, date);
			if (this.availableDateIndex[dateKey] === undefined)
			{
				if (Type.isFunction(this.requestDataCallback))
				{
					this.requestDataCallback({date: date});
				}
				return false;
			}
			else
			{
				return this.availableDateIndex[dateKey];
			}
		}
		return false;
	}

	isItPastDate(date)
	{
		if (Type.isDate(date))
		{
			let
				nowDate = new Date(),
				checkDate = new Date(date.getTime());

			nowDate.setHours(0, 0, 0, 0);
			checkDate.setHours(0, 0, 0, 0);

			return checkDate.getTime() < nowDate.getTime();
		}
		return false;
	}

	refreshCurrentValue()
	{
		this.onChange(this.getDisplayedValue());
	}

	getDisplayedValue()
	{
		return this.style === 'popup' ? this.popupSateControl.getValue() : this.lineDateControl.getValue();;
	}

	onChange(date)
	{
		if (Type.isFunction(this.changeValueCallback))
		{
			let realDate = date;
			if (!Type.isDate(realDate))
			{
				realDate = this.getDisplayedValue();
			}
			this.setCurrentDate(date);
			this.changeValueCallback(date, realDate, this.isDateAvailable(realDate));
		}
	}

	getValue()
	{
		if (!this.currentDate)
		{
			this.currentDate = new Date();
		}
		return this.currentDate;
	}
}


class PopupDateSelector
{
	static externalDatePickerIsEnabled = null;

	constructor(params)
	{
		this.DOM = {
			outerWrap: params.wrap,
			wrap: null
		};
		this.value = null;
		this.datePicker = null;
		this.isDateAvailable = Type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function(){return true};
		this.onChange = Type.isFunction(params.onChange) ? params.onChange : function(){};
	}

	build()
	{
		this.DOM.wrap = this.DOM.outerWrap.appendChild(Dom.create("div", {
			props : { className : 'calendar-resbook-webform-block-strip'},
			events: {click: this.handleClick.bind(this)}
		}));

		this.DOM.valueInput = this.DOM.wrap.appendChild(Tag.render`<input type="hidden" 
value=""/>`);

		this.DOM.previousArrow = this.DOM.wrap.appendChild(Tag.render`<span class="calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev" data-bx-resbook-date-meta="previous"/>`);

		this.DOM.stateWrap = this.DOM.wrap.appendChild(Tag.render`<span class="calendar-resbook-webform-block-strip-text" data-bx-resbook-date-meta="calendar"/>`);

		this.DOM.stateWrapDate = this.DOM.stateWrap.appendChild(Tag.render`<span class="calendar-resbook-webform-block-strip-date"/>`);
		this.DOM.stateWrapDay = this.DOM.stateWrap.appendChild(Tag.render`<span class="calendar-resbook-webform-block-strip-day"/>`);

		this.DOM.nextArrow = this.DOM.wrap.appendChild(Tag.render`<span class="calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next" data-bx-resbook-date-meta="next"/>`);
	}

	getValue()
	{
		return this.value;
	}

	setValue(dateValue)
	{
		this.value = dateValue;
		Dom.adjust(this.DOM.stateWrapDate, {text: BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_DATE_LINE'), dateValue)});
		Dom.adjust(this.DOM.stateWrapDay, {text: BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_DAY_LINE'), dateValue)});

		if (!this.isDateAvailable(dateValue) || !Type.isDate(dateValue))
		{
			this.onChange(false);
		}
		else
		{
			this.onChange(this.value);
		}
	}

	handleClick(e)
	{
		let
			dateValue,
			target = e.target || e.srcElement;

		if (target.hasAttribute('data-bx-resbook-date-meta') ||
			(target = target.closest('[data-bx-resbook-date-meta]')))
		{
			let dateMeta = target.getAttribute('data-bx-resbook-date-meta');
			if (dateMeta === 'previous')
			{
				dateValue = this.getValue();
				dateValue.setDate(dateValue.getDate() - 1);
				this.setValue(dateValue);
			}
			else if (dateMeta === 'next')
			{
				dateValue = this.getValue();
				dateValue.setDate(dateValue.getDate() + 1);
				this.setValue(dateValue);
			}
			else if (dateMeta === 'calendar')
			{
				this.openCalendarPopup();
			}
		}
	}

	openCalendarPopup()
	{
		this.DOM.valueInput.value = BookingUtil.formatDate(null, this.getValue().getTime() / 1000);

		if (PopupDateSelector.isExternalDatePickerEnabled())
		{
			this.openExternalDatePicker();
		}
		else
		{
			this.openBxCalendar();
		}
	}

	openBxCalendar()
	{
		BX.calendar({node: this.DOM.stateWrap, field: this.DOM.valueInput, bTime: false});
		if (BX.calendar.get().popup)
		{
			BookingUtil.unbindCustomEvent(BX.calendar.get().popup, 'onPopupClose', this.handleCalendarClose.bind(this));
			BookingUtil.bindCustomEvent(BX.calendar.get().popup, 'onPopupClose', this.handleCalendarClose.bind(this));
		}
	}

	handleCalendarClose()
	{
		this.setValue(BookingUtil.parseDate(this.DOM.valueInput.value));
	}

	static isExternalDatePickerEnabled()
	{
		if (Type.isNull(PopupDateSelector.externalDatePickerIsEnabled))
		{
			PopupDateSelector.externalDatePickerIsEnabled = !!(window.BX && BX.UI && BX.UI.Vue && BX.UI.Vue.Components && BX.UI.Vue.Components.DatePick);
		}

		return PopupDateSelector.externalDatePickerIsEnabled;
	}

	openExternalDatePicker()
	{
		if (Type.isNull(this.datePicker))
		{
			this.datePicker = new BX.UI.Vue.Components.DatePick(
				{
					node: this.DOM.stateWrap,
					hasTime: false,
					events: {
						change: function(value){
							this.DOM.valueInput.value = value;
							this.handleCalendarClose();
						}.bind(this)
					}
				}
			);
		}

		this.datePicker.value = this.DOM.valueInput.value;
		this.datePicker.toggle();
	}
}



class LineDateSelector
{
	constructor(params)
	{
		params = params || {};
		this.DOM = {
			outerWrap: params.wrap,
			wrap: null
		};
		this.value = null;
		this.isDateAvailable = Type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function(){return true};
		this.onChange = Type.isFunction(params.onChange) ? params.onChange : function(){};
		this.DAYS_DISPLAY_SIZE = 30;
		this.DOM.dayNodes = {};
		this.dayNodeIndex = {};
	}

	build()
	{
		this.DOM.monthTitle = this.DOM.outerWrap.appendChild(Dom.create("span", {
			props : { className : 'calendar-resbook-webform-block-date-month'}
		}));

		this.DOM.wrap = this.DOM.outerWrap.appendChild(Dom.create("div", {
			props : { className : 'calendar-resbook-webform-block-date-range'},
			events: {click: this.handleClick.bind(this)}
		}));

		this.DOM.controlStaticWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-date-range-static-wrap" 
></div>`);
		this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-date-range-inner-wrap" 
></div>`);
		this.DOM.valueInput = this.DOM.wrap.appendChild(Tag.render`<input type="hidden" 
value=""/>`);

		this.fillDays();
		this.initCustomScroll();
	}

	fillDays()
	{
		let
			i,
			startDate = this.getStartLoadDate(),
			date = new Date(startDate.getTime());

		for (i = 0; i < this.DAYS_DISPLAY_SIZE; i++)
		{
			this.addDateSlot(date);
			date.setDate(date.getDate() + 1);
		}

		this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);
	}

	addDateSlot(date)
	{
		let dateCode = BookingUtil.formatDate('Y-m-d', date.getTime() / 1000);
		this.dayNodeIndex[dateCode] = new Date(date.getTime());
		this.DOM.dayNodes[dateCode] = this.DOM.controlInnerWrap.appendChild(Dom.create("div", {
			attrs : {
				className : 'calendar-resbook-webform-block-date-item' + (this.isDateAvailable(date) ? '' : ' calendar-resbook-webform-block-date-item-off'),
				'data-bx-resbook-date-meta' : dateCode
			},
			html: '<div class="calendar-resbook-webform-block-date-item-inner">' +
				'<span class="calendar-resbook-webform-block-date-number">' +
				BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_DATE'), date) +
				'</span>' +
				'<span class="calendar-resbook-webform-block-date-day">' +
				BookingUtil.formatDate(Loc.getMessage('WEBF_RES_DATE_FORMAT_DAY_OF_THE_WEEK'), date) +
				'</span>' +
				'</div>'
		}));
	}

	refreshDateAvailability()
	{
		for (let dateCode in this.DOM.dayNodes)
		{
			if (this.DOM.dayNodes.hasOwnProperty(dateCode))
			{
				if (this.isDateAvailable(this.dayNodeIndex[dateCode]))
				{
					Dom.removeClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
				}
				else
				{
					Dom.addClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
				}
			}
		}
	}

	handleClick(e)
	{
		let
			dateValue,
			target = e.target || e.srcElement;

		if (target.hasAttribute('data-bx-resbook-date-meta') ||
			(target = target.closest('[data-bx-resbook-date-meta]')))
		{
			let dateMeta = target.getAttribute('data-bx-resbook-date-meta');
			if (dateMeta && (dateValue = BookingUtil.parseDate(dateMeta, false, 'YYYY-MM-DD')))
			{
				this.setValue(dateValue);
			}
		}
	}

	setValue(dateValue)
	{
		if (Type.isDate(dateValue))
		{
			this.value = dateValue;
			let dayNode = this.getDayNode(dateValue);
			if (dayNode)
			{
				this.setSelected(dayNode);
			}
			this.onChange(this.value);
		}
	}

	getValue()
	{
		return this.value;
	}

	getDayNode(dateValue)
	{
		let dateCode = BookingUtil.formatDate('Y-m-d', dateValue.getTime() / 1000);
		if (this.DOM.dayNodes[dateCode])
		{
			return this.DOM.dayNodes[dateCode];
		}
		else
		{
			this.fillDays(dateValue);
			if (this.DOM.dayNodes[dateCode])
			{
				return this.DOM.dayNodes[dateCode];
			}
		}
		return null;
	}

	setSelected(dayNode)
	{
		if (this.currentSelected)
		{
			Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-date-item-select');
		}
		this.currentSelected = dayNode;
		Dom.addClass(dayNode, 'calendar-resbook-webform-block-date-item-select');
	}

	getStartLoadDate()
	{
		if (!this.startLoadDate)
		{
			this.startLoadDate = new Date();
		}
		else
		{
			this.startLoadDate.setDate(this.startLoadDate.getDate() + this.DAYS_DISPLAY_SIZE);
		}
		return this.startLoadDate;
	}

	initCustomScroll()
	{
		let arrowWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-arrow-container" 
></div>`);

		this.DOM.leftArrow = arrowWrap.appendChild(Dom.create("span",
			{
				props : {className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'},
				events: {click: this.handlePreletrowClick.bind(this)}
			}));
		this.DOM.rightArrow = arrowWrap.appendChild(Dom.create("span",
			{
				props : { className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'},
				events: {click: this.handleNextArrowClick.bind(this)}
			}));

		this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
		this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);

		if ('onwheel' in document)
		{
			Event.bind(this.DOM.controlStaticWrap, "wheel", this.mousewheelScrollHandler.bind(this));
		}
		else
		{
			Event.bind(this.DOM.controlStaticWrap, "mousewheel", this.mousewheelScrollHandler.bind(this));
		}

		this.checkScrollPosition();
	}

	handleNextArrowClick()
	{
		this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
		this.checkScrollPosition();
	}

	handlePreletrowClick()
	{
		this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
		this.checkScrollPosition();
	}

	mousewheelScrollHandler(e)
	{
		e = e || window.event;
		let delta = e.deltaY || e.detail || e.wheelDelta;
		if (Math.abs(delta) > 0)
		{
			if (!Browser.isMac())
			{
				delta = delta * 3;
			}
			this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
			this.checkScrollPosition();

			if(e.stopPropagation)
			{
				e.preventDefault();
				e.stopPropagation();
			}
			return false;
		}
	}

	checkScrollPosition()
	{
		if (this.outerWidth <= this.innerWidth)
		{
			this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft === 0 ? 'none' : '';
			//this.DOM.rightArrow.style.display = (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) ? 'none' : '';
			if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft)
			{
				this.fillDays();
			}
		}

		this.updateMonthTitle();
	}

	updateMonthTitle()
	{
		if (!this.dayNodeOuterWidth)
		{
			this.dayNodeOuterWidth = this.DOM.controlInnerWrap.childNodes[1].offsetLeft - this.DOM.controlInnerWrap.childNodes[0].offsetLeft;
			if (!this.dayNodeOuterWidth)
			{
				return setTimeout(this.updateMonthTitle.bind(this), 100);
			}
		}

		let
			monthFrom, monthTo, dateMeta, dateValue,
			firstDayNodeIndex = Math.floor(this.DOM.controlStaticWrap.scrollLeft / this.dayNodeOuterWidth),
			lastDayNodeIndex = Math.floor((this.DOM.controlStaticWrap.scrollLeft + this.outerWidth) / this.dayNodeOuterWidth);

		if (this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex])
		{
			dateMeta = this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex].getAttribute('data-bx-resbook-date-meta');
			if (dateMeta && (dateValue = BookingUtil.parseDate(dateMeta, false, 'YYYY-MM-DD')))
			{
				monthFrom = monthTo = BookingUtil.formatDate('f', dateValue);
			}
		}

		if (this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex])
		{
			dateMeta = this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex].getAttribute('data-bx-resbook-date-meta');
			if (dateMeta && (dateValue = BookingUtil.parseDate(dateMeta, false, 'YYYY-MM-DD')))
			{
				monthTo = BookingUtil.formatDate('f', dateValue);
			}
		}

		if (monthFrom && monthTo)
		{
			Dom.adjust(this.DOM.monthTitle, {text: monthTo === monthFrom ? monthFrom : monthFrom + ' - ' + monthTo});
		}
	}
}