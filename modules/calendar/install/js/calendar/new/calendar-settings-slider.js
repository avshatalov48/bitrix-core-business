;(function(window) {
	function SettingsSlider(params)
	{
		this.calendar = params.calendar;
		this.id = this.calendar.id + '_settings_slider';
		this.uid = this.id + '_' + Math.round(Math.random() * 1000000);
		this.button = params.button;
		this.zIndex = params.zIndex || 3100;
		this.sliderId = "calendar:settings-slider";

		this.inPersonal = this.calendar.util.userIsOwner();
		this.showGeneralSettings = !!(this.calendar.util.config.perm && this.calendar.util.config.perm.access);
		this.settings = this.calendar.util.config.settings;

		this.SLIDER_WIDTH = 500;
		this.SLIDER_DURATION = 80;
		BX.bind(this.button, 'click', BX.delegate(this.show, this));
	}

	SettingsSlider.prototype = {
		show: function ()
		{
			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: BX.delegate(this.create, this),
				width: this.SLIDER_WIDTH,
				animationDuration: this.SLIDER_DURATION
			});

			BX.addCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
			BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
			this.calendar.disableKeyHandler();
		},

		close: function ()
		{
			BX.SidePanel.Instance.close();
		},

		hide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				if (this.denyClose)
				{
					event.denyAction();
				}
				else
				{
					BX.removeCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
				}
			}
		},

		destroy: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
				BX.SidePanel.Instance.destroy(this.sliderId);
				this.calendar.enableKeyHandler();
			}
		},

		create: function ()
		{
			var promise = new BX.Promise();

			BX.ajax.get(this.calendar.util.getActionUrl(), {
				action: 'get_settings_slider',
				is_personal: this.inPersonal ? 'Y' : 'N',
				show_general_settings: this.showGeneralSettings ? 'Y' : 'N',
				unique_id: this.uid,
				sessid: BX.bitrix_sessid(),
				bx_event_calendar_request: 'Y',
				reqId: Math.round(Math.random() * 1000000)
			}, BX.delegate(function (html)
			{
				promise.fulfill(BX.util.trim(html));
				this.initControls();
			}, this));

			return promise;
		},

		initControls: function ()
		{
			BX.bind(BX(this.uid + '_save'), 'click', BX.proxy(this.save, this));
			BX.bind(BX(this.uid + '_close'), 'click', BX.proxy(this.close, this));

			this.DOM = {
				denyBusyInvitation: BX(this.uid + '_deny_busy_invitation'),
				showWeekNumbers: BX(this.uid + '_show_week_numbers')
			};

			if (this.inPersonal)
			{
				this.DOM.sectionSelect = BX(this.uid + '_meet_section');
				this.DOM.crmSelect = BX(this.uid + '_crm_section');
				this.DOM.showDeclined = BX(this.uid + '_show_declined');
				this.DOM.showTasks = BX(this.uid + '_show_tasks');
				this.DOM.showCompletedTasks = BX(this.uid + '_show_completed_tasks');
				this.DOM.timezoneSelect = BX(this.uid + '_set_tz_sel');
			}

			// General settings
			this.DOM.workTimeStart = BX(this.uid + 'work_time_start');
			this.DOM.workTimeEnd = BX(this.uid + 'work_time_end');
			this.DOM.weekHolidays = BX(this.uid + 'week_holidays');
			this.DOM.yearHolidays = BX(this.uid + 'year_holidays');
			this.DOM.yearWorkdays = BX(this.uid + 'year_workdays');

			// Access
			this.typeAccess = false;
			if (this.calendar.util.config.TYPE_ACCESS)
			{
				this.accessWrap = BX(this.uid + 'type-access-values-cont');
				if (this.accessWrap)
				{
					this.initAccessController();
					this.typeAccess = this.calendar.util.config.TYPE_ACCESS || {};
					var code;
					for (code in this.typeAccess)
					{
						if (this.typeAccess.hasOwnProperty(code))
						{
							this.insertAccessRow(this.calendar.util.getAccessName(code), code, this.typeAccess[code]);
						}
					}
				}
			}

			this.DOM.manageCalDav = BX(this.uid + '_manage_caldav');
			if (this.DOM.manageCalDav)
			{
				BX.bind(this.DOM.manageCalDav, 'click', BX.proxy(this.calendar.syncSlider.showCalDavSyncDialog, this.calendar.syncSlider));
			}

			// Set personal user settings
			if (this.inPersonal)
			{
				this.DOM.sectionSelect.options.length = 0;
				var
					sections = this.calendar.sectionController.getSectionList(),
					meetSection = this.calendar.util.getUserOption('meetSection'),
					crmSection = this.calendar.util.getUserOption('crmSection'),
					i, section, selected;

				for (i = 0; i < sections.length; i++)
				{
					section = sections[i];
					if (section.belongToOwner())
					{
						if (!meetSection)
						{
							meetSection = section.id;

						}
						selected = meetSection == section.id;

						this.DOM.sectionSelect.options.add(new Option(section.name, section.id, selected, selected));

						if (!crmSection)
						{
							crmSection = section.id;

						}
						selected = crmSection == section.id;

						this.DOM.crmSelect.options.add(new Option(section.name, section.id, selected, selected));
					}
				}
			}

			if(this.DOM.showDeclined)
			{
				this.DOM.showDeclined.checked = !!this.calendar.util.getUserOption('showDeclined');
			}
			if(this.DOM.showTasks)
			{
				this.DOM.showTasks.checked = this.calendar.util.getUserOption('showTasks') == 'Y';
			}
			if(this.DOM.showCompletedTasks)
			{
				this.DOM.showCompletedTasks.checked = this.calendar.util.getUserOption('showCompletedTasks') == 'Y';
			}
			if (this.DOM.denyBusyInvitation)
			{
				this.DOM.denyBusyInvitation.checked = !!this.calendar.util.getUserOption('denyBusyInvitation');
			}

			if (this.DOM.showWeekNumbers)
			{
				this.DOM.showWeekNumbers.checked = this.calendar.util.showWeekNumber();
			}

			if(this.DOM.timezoneSelect)
			{
				this.DOM.timezoneSelect.value = this.calendar.util.getUserOption('timezoneName') || '';
			}

			if (this.showGeneralSettings)
			{
				// Set access for calendar type
				this.DOM.workTimeStart.value = this.settings.work_time_start;
				this.DOM.workTimeEnd.value = this.settings.work_time_end;

				if (this.DOM.weekHolidays)
				{
					for(i = 0; i < this.DOM.weekHolidays.options.length; i++)
					{
						this.DOM.weekHolidays.options[i].selected = BX.util.in_array(this.DOM.weekHolidays.options[i].value, this.settings.week_holidays);
					}
				}

				this.DOM.yearHolidays.value = this.settings.year_holidays;
				this.DOM.yearWorkdays.value = this.settings.year_workdays;
			}
		},

		save: function ()
		{
			var userSettings = this.calendar.util.config.userSettings;

			// Save user settings
			if (this.DOM.showDeclined)
			{
				userSettings.showDeclined = this.DOM.showDeclined.checked ? 1 : 0;
			}

			if (this.DOM.showWeekNumbers)
			{
				userSettings.showWeekNumbers = this.DOM.showWeekNumbers.checked ? 'Y' : 'N';
			}

			if (this.DOM.showTasks)
			{
				userSettings.showTasks = this.DOM.showTasks.checked ? 'Y' : 'N';
			}
			if (this.DOM.showCompletedTasks)
			{
				userSettings.showCompletedTasks = this.DOM.showCompletedTasks.checked ? 'Y' : 'N';
			}

			if (this.DOM.sectionSelect)
			{
				userSettings.meetSection = this.DOM.sectionSelect.value;
			}
			if (this.DOM.crmSelect)
			{
				userSettings.crmSection = this.DOM.crmSelect.value;
			}

			if (this.DOM.denyBusyInvitation)
			{
				userSettings.denyBusyInvitation = this.DOM.denyBusyInvitation.checked ? 1 : 0;
			}

			if(this.DOM.timezoneSelect)
			{
				userSettings.userTimezoneName = this.DOM.timezoneSelect.value;
			}

			// Save settings
			//var postData = this.GetReqData('save_settings',
			//	{
			//		user_settings: this.calendar.util.config.userSettings,
			//		user_timezone_name: this.arConfig.userTimezoneName
			//	});

			//this.settings.work_time_start = D.CAL.DOM.WorkTimeStart.value;
			var data = {
				action: 'save_settings',
				user_settings: userSettings,
				user_timezone_name: userSettings.userTimezoneName
			};

			if (this.showGeneralSettings && this.DOM.workTimeStart)
			{
				data.settings = {
					work_time_start: this.DOM.workTimeStart.value,
					work_time_end: this.DOM.workTimeEnd.value,
					week_holidays: [],
					year_holidays: this.DOM.yearHolidays.value,
					year_workdays: this.DOM.yearWorkdays.value
				};
				for(var i = 0; i < this.DOM.weekHolidays.options.length; i++)
				{
					if (this.DOM.weekHolidays.options[i].selected)
					{
						data.settings.week_holidays.push(this.DOM.weekHolidays.options[i].value);
					}
				}
			}

			if (this.typeAccess !== false)
			{
				data.type_access = this.typeAccess;
			}

			this.calendar.request({
				type: 'post',
				data: data,
				handler: BX.delegate(function(response)
				{
					BX.reload();
				}, this)
			});

			this.close();
		},

		initAccessController: function()
		{
			this.accessControls = {};
			this.accessTasks = this.calendar.util.getTypeAccessTasks();

			BX.bind(this.accessLink, 'click', BX.delegate(function(){
				if (BX.hasClass(this.accessWrap, 'shown'))
				{
					BX.removeClass(this.accessWrap, 'shown');
				}
				else
				{
					BX.addClass(this.accessWrap, 'shown');
				}
			}, this));

			BX.Access.Init();

			this.accessWrapInner = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-access-inner-wrap'}}));
			this.accessTable = this.accessWrapInner.appendChild(BX.create("TABLE", {props: {className: "calendar-section-slider-access-table"}}));
			this.accessButtonWrap = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-options-container'}}));
			this.accessButton = this.accessButtonWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-list-slider-new-calendar-option-add'}, html: BX.message('EC_SEC_SLIDER_ACCESS_ADD')}));

			BX.bind(this.accessButton, 'click', BX.proxy(function()
			{
				BX.Access.ShowForm({
					callback: BX.proxy(function(selected)
					{
						var provider, code;
						for(provider in selected)
						{
							if (selected.hasOwnProperty(provider))
							{
								for (code in selected[provider])
								{
									if (selected[provider].hasOwnProperty(code))
									{
										this.insertAccessRow(BX.Access.GetProviderName(provider) + ' ' + selected[provider][code].name, code);
									}
								}
							}
						}
					}, this),
					bind: this.accessButton
				});

				if (BX.Access.popup && BX.Access.popup.popupContainer)
				{
					BX.Access.popup.popupContainer.style.zIndex = this.zIndex + 10;
				}
			}, this));


			BX.bind(this.accessWrapInner, 'click', BX.proxy(function(e)
			{
				var
					code,
					target = this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);
				if (target && target.getAttribute)
				{
					if(target.getAttribute('data-bx-calendar-access-selector') !== null)
					{
						// show selector
						code = target.getAttribute('data-bx-calendar-access-selector');
						if (this.accessControls[code])
						{
							this.showAccessSelectorPopup({
									node: this.accessControls[code].removeIcon,
									setValueCallback: BX.delegate(function(value)
									{
										if (this.accessTasks[value] && this.accessControls[code])
										{
											this.accessControls[code].valueNode.innerHTML = BX.util.htmlspecialchars(this.accessTasks[value].title);
											this.typeAccess[code] = value;
										}
									}, this)
								}
							);
						}
					}
					else if(target.getAttribute('data-bx-calendar-access-remove') !== null)
					{
						code = target.getAttribute('data-bx-calendar-access-remove');
						if (this.accessControls[code])
						{
							BX.cleanNode(this.accessControls[code].rowNode, true);
							delete this.typeAccess[code];
						}
					}
				}

			}, this));
		},

		insertAccessRow: function(title, code, value)
		{
			if (value === undefined)
			{
				value = this.calendar.util.getDefaultTypeAccessTask();
				this.typeAccess[code] = value;
			}

			var
				rowNode = BX.adjust(this.accessTable.insertRow(-1), {props : {className: 'calendar-section-slider-access-table-row'}}),
				titleNode = BX.adjust(rowNode.insertCell(-1), {
					props : {className: 'calendar-section-slider-access-table-cell'},
					html: '<span class="calendar-section-slider-access-title">' + BX.util.htmlspecialchars(title) + ':</span>'}),
				valueCell = BX.adjust(rowNode.insertCell(-1), {
					props : {className: 'calendar-section-slider-access-table-cell'},
					attrs: {'data-bx-calendar-access-selector': code}
				}),
				selectNode = valueCell.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-section-slider-access-value'}
				})),
				valueNode = selectNode.appendChild(BX.create('SPAN', {
					text: this.accessTasks[value] ? this.accessTasks[value].title : ''
				})),
				removeIcon = selectNode.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-section-slider-access-remove'},
					attrs: {'data-bx-calendar-access-remove': code}
				}));

			this.accessControls[code] = {
				rowNode: rowNode,
				titleNode: titleNode,
				valueNode: valueNode,
				removeIcon: removeIcon
			};
		},

		showAccessSelectorPopup: function(params)
		{
			if (this.accessPopupMenu && this.accessPopupMenu.popupWindow && this.accessPopupMenu.popupWindow.isShown())
			{
				return this.accessPopupMenu.close();
			}

			var
				menuId = this.calendar.id + '_type_access_popup',
				taskId,
				_this = this,
				menuItems = [];

			for(taskId in this.accessTasks)
			{
				if (this.accessTasks.hasOwnProperty(taskId))
				{
					menuItems.push(
						{
							text: this.accessTasks[taskId].title,
							onclick: (function (value)
							{
								return function ()
								{
									params.setValueCallback(value);
									_this.accessPopupMenu.close();
								}
							})(taskId)
						}
					);
				}
			}

			this.accessPopupMenu = BX.PopupMenu.create(
				menuId,
				params.node,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: -5,
					offsetLeft: 0,
					angle: true
				}
			);

			this.accessPopupMenu.show();

			BX.addCustomEvent(this.accessPopupMenu.popupWindow, 'onPopupClose', function()
			{
				BX.PopupMenu.destroy(menuId);
			});
		}

	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SettingsSlider = SettingsSlider;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SettingsSlider = SettingsSlider;
		});
	}
})(window);