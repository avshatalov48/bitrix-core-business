;(function ()
{

	BX.namespace('BX.Sender.Letter');
	if (BX.Sender.Letter.Time)
	{
		return;
	}

	var Page = BX.Sender.Page;
	var Helper = BX.Sender.Helper;

	/**
	 * Letter.
	 *
	 */
	function Time()
	{
		this.context = null;
	}
	Time.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.isFrame = params.isFrame || false;
		this.isSaved = params.isSaved || false;
		this.isOutside = params.isOutside || false;
		this.canEdit = params.canEdit || false;
		this.isSupportReiterate = params.isSupportReiterate || false;
		this.prettyDateFormat = params.prettyDateFormat;
		this.mess = params.mess || {atTime: '', defered: ''};

		this.selectorNode = Helper.getNode('time-selector', this.context);
		this.inputNode = Helper.getNode('time-input', this.context);
		if (this.canEdit)
		{
			BX.bind(this.selectorNode, 'click', this.showMenu.bind(this));
		}

		if (this.isFrame && this.isSaved)
		{
			BX.Sender.Page.slider.close();

			if (this.isOutside && parent.BX)
			{
				if (!parent.BX.UI || !parent.BX.UI.Notification)
				{
					parent.BX.namespace('BX.UI');
					parent.BX.UI.Notification = BX.UI.Notification;
				}

				parent.BX.UI.Notification.Center.notify({
					content: this.mess.outsideSaveSuccess,
					autoHideDelay: 5000
				});
			}
		}

		this.scheduleNodes = {
			daysOfMonth: Helper.getNode('time-reiterate-days-of-month', this.context),
			daysOfWeek: Helper.getNode('time-reiterate-days-of-week', this.context),
			timesOfDay: Helper.getNode('time-reiterate-times-of-day', this.context),
			monthsOfYear: Helper.getNode('time-reiterate-months-of-year', this.context)
		};
		this.schedule = new Schedule({
			caller: this,
			context: Helper.getNode('time-reiterate', this.context)
		});

		var value = this.inputNode.value;
		var date = BX.parseDate(this.inputNode.value);
		if (value && date)
		{
			this.setFormattedDate(date);
		}
		else if (this.scheduleNodes.timesOfDay.value)
		{
			this.schedule.setText();
		}
		else
		{
			this.selectorNode.textContent = this.mess.defered;
		}

		Page.initButtons();
	};
	Time.prototype.onPopupClose = function ()
	{

	};
	Time.prototype.onClick = function (id)
	{
		this.popupMenu.close();

		if (id === 'time')
		{
			this.showCalendar(this.selectorNode);
			return;
		}

		if (id === 'schedule')
		{
			this.schedule.show();
			return;
		}

		var value = null;
		var item = this.popupMenu.getMenuItem(id);
		if (!item)
		{
			return;
		}
		else if (id === 'defered')
		{
			value = null;
		}
		else if (id === 'now')
		{
			value = 'now';
		}

		this.selectorNode.textContent = item.text;
		this.inputNode.value = value;
	};
	Time.prototype.onTimeSet = function (value)
	{
		if (!value)
		{
			return;
		}

		var now = new Date();
		if (value < now)
		{
			value = now;
		}

		this.setFormattedDate(value);
		this.inputNode.value = BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')), value);
	};
	Time.prototype.setFormattedDate = function (value)
	{
		var ampm = BX.isAmPmMode();
		var format = this.prettyDateFormat + ' ';
		format += this.mess.atTime;
		format += ' ' + (ampm ? "g:i a" : "H:i");
		this.selectorNode.textContent = BX.date.format(format, value);
	};
	Time.prototype.onPopupItemEnter = function (id)
	{
		var item = this.popupMenu.getMenuItem(id);
		if (!item)
		{
			return;
		}

		if (id === 'time')
		{
			//this.showCalendar(item.getLayout().item);
		}
		else
		{
			BX.calendar.get().Close();
		}
	};
	Time.prototype.showCalendar = function (node)
	{
		var value = this.inputNode.value;
		if (value)
		{
			value = BX.parseDate(value, true);
		}
		BX.calendar({
			'node': node,
			'value': value,
			'bTime': true,
			'bHideTime': false,
			'callback': function () {
				return true;
			},
			'callback_after': this.onTimeSet.bind(this)
		});
	};
	Time.prototype.showMenu = function ()
	{
		if (this.popupMenu)
		{
			this.popupMenu.show();
			return;
		}

		var items = [
			{
				id: 'now',
				text: this.mess.now
			},
			{
				id: 'defered',
				text: this.mess.defered
			},
			{
				id: 'time',
				text: this.mess.time
			}
		];

		if (this.isSupportReiterate)
		{
			items.push({
				id: 'schedule',
				text: this.mess.schedule
			});
		}

		items.forEach(function (item) {
			item.onclick = this.onClick.bind(this, item.id);
			item.events = {
				onMouseEnter: this.onPopupItemEnter.bind(this, item.id)
			};
		}, this);

		this.popupMenu = BX.PopupMenu.create(
			'sender-letter-time',
			this.selectorNode,
			items,
			{
				autoHide: true,
				offsetLeft: 40,
				//offsetTop: params.offsetTop ? params.offsetTop : -3,
				angle:
				{
					position: "top",
					offset: 42
				},
				events:
				{
					onPopupClose : this.onPopupClose.bind(this)
				}
			}
		);

		this.popupMenu.show();
	};

	function Schedule(params)
	{
		this.init(params);
	}
	Schedule.prototype = {
		popup: null,
		activeClassName: 'sender-letter-time-popup-date-item-current',
		init: function (params)
		{
			this.caller = params.caller;
			this.context = params.context;

			this.timesOfDayNode = Helper.getNode('reiterate-times-of-day', this.context);
			if (this.caller.scheduleNodes.timesOfDay.value)
			{
				this.timesOfDayNode.value = this.caller.scheduleNodes.timesOfDay.value;
			}

			this.daysOfMonthNode = Helper.getNode('reiterate-days-of-month', this.context);
			if (this.caller.scheduleNodes.daysOfMonth.value)
			{
				this.daysOfMonthNode.value = this.caller.scheduleNodes.daysOfMonth.value;
			}

			this.daysOfWeekNodes = Helper.getNodes('reiterate-days-of-week', this.context);
			var daysOfWeek = this.caller.scheduleNodes.daysOfWeek.value;
			this.daysOfWeekNodes.forEach(function (node) {
				BX.bind(node, 'click', this.selectWeekDay.bind(this, node));
				if (daysOfWeek)
				{
					var isAdd = daysOfWeek.indexOf(node.getAttribute('data-value')) >= 0;
					Helper.changeClass(node, this.activeClassName, isAdd);
				}
			}, this);


			this.daysOfMonthNodes = Helper.getNodes('reiterate-days-of-month', this.context);
			var daysOfMonth = this.caller.scheduleNodes.daysOfMonth.value.split(',');
			this.daysOfMonthNodes.forEach(function (node) {
				BX.bind(node, 'click', this.selectWeekDay.bind(this, node));
				var value = node.getAttribute('data-value');
				if (daysOfMonth && value)
				{
					var isAdd = BX.util.in_array(value, daysOfMonth);
					Helper.changeClass(node, this.activeClassName, isAdd);
				}
			}, this);

			this.monthsOfYearNodes = Helper.getNodes('reiterate-months-of-year', this.context);
			var monthsOfYear = this.caller.scheduleNodes.monthsOfYear.value.split(',');
			this.monthsOfYearNodes.forEach(function (node) {
				BX.bind(node, 'click', this.selectWeekDay.bind(this, node));
				var value = node.getAttribute('data-value');
				if (monthsOfYear && value)
				{
					var isAdd = BX.util.in_array(value, monthsOfYear);
					Helper.changeClass(node, this.activeClassName, isAdd);
				}
			}, this);

			this.additionalNode = Helper.getNode('reiterate-additional', this.context);
			this.additionalBtnNode = Helper.getNode('reiterate-additional-btn', this.context);
			if (this.additionalBtnNode && this.additionalNode)
			{
				BX.bind(this.additionalBtnNode, 'click', this.showAdditional.bind(this));
				if (daysOfMonth.length || monthsOfYear.length)
				{
					this.showAdditional();
				}
			}
		},
		showAdditional: function ()
		{
			Helper.display.change(this.additionalNode, true);
			Helper.display.change(this.additionalBtnNode, false);
		},
		show: function ()
		{
			if (!this.popup)
			{
				this.popup = BX.PopupWindowManager.create(
					'sender-letter-time-schedule',
					this.caller.selectorNode,
					{
						content: this.context,
						autoHide: true,
						lightShadow: false,
						width: 270,
						closeByEsc: true,
						contentColor: 'white',
						angle: true,
						buttons: [
							new BX.PopupWindowButton({
								text: this.caller.mess.accept,
								className: "popup-window-button-accept",
								events: {
									click: this.onApply.bind(this)
								}
							})
						]
					}
				);
			}

			if (this.popup.isShown())
			{
				return;
			}

			this.popup.show();
		},
		selectWeekDay: function (node)
		{
			var value = node.getAttribute('data-value');
			if (!value)
			{
				return;
			}

			BX.toggleClass(node, this.activeClassName);
		},
		setText: function ()
		{
			var time = this.getTime();
			var days = this.getSelectedNames(this.daysOfWeekNodes);
			var daysOfMonth = this.getSelectedNames(this.daysOfMonthNodes);
			var monthsOfYear = this.getSelectedNames(this.monthsOfYearNodes);
			var additional = [];
			if (daysOfMonth.length && monthsOfYear.length)
			{
				monthsOfYear.forEach(function (month) {
					daysOfMonth.forEach(function (day) {
						additional.push(day + ' ' + month);
					});
				});
			}
			else if (daysOfMonth.length)
			{
				additional = daysOfMonth;
			}
			else if (monthsOfYear.length)
			{
				additional = monthsOfYear;
			}
			additional = this.getString(additional);

			var text = this.caller.mess.scheduleText;
			text = text.replace('%time%', time);

			if ((days.length === 0 || days.length === 7) && additional)
			{
				text = text.replace('%days%', additional);
			}
			else
			{
				text = text.replace('%days%', this.getString(days));
				if (additional)
				{
					text = text + ". " + this.caller.mess.scheduleTextMo.replace('%days%', additional);
				}
			}

			this.caller.selectorNode.textContent = text;
		},
		getTime: function ()
		{
			return this.timesOfDayNode.value;
		},
		getSelectedNodes: function (list)
		{
			return list.filter(function (node)
			{
				return BX.hasClass(node, this.activeClassName);
			}, this);
		},
		getSelectedValues: function (list)
		{
			return this.getSelectedNodes(list).map(function (node) {
				return node.getAttribute('data-value');
			}, this);
		},
		getSelectedValuesString: function (list)
		{
			return this.getSelectedValues(list).join(',');
		},
		getSelectedNames: function (list)
		{
			return this.getSelectedNodes(list).map(function (node) {
				return node.textContent.trim();
			}, this);
		},
		getString: function (list)
		{
			return list.join(', ');
		},
		onApply: function ()
		{
			this.caller.scheduleNodes.timesOfDay.value = this.getTime();
			this.caller.scheduleNodes.daysOfWeek.value = this.getSelectedValuesString(this.daysOfWeekNodes);
			this.caller.scheduleNodes.daysOfMonth.value = this.getSelectedValuesString(this.daysOfMonthNodes);
			this.caller.scheduleNodes.monthsOfYear.value = this.getSelectedValuesString(this.monthsOfYearNodes);
			this.caller.inputNode.value = 'schedule';

			this.setText();
			this.popup.close();
		}
	};


	BX.Sender.Letter.Time = new Time();

})(window);