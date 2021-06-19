
;(function(window) {
	function SettingsSlider(params)
	{
		this.calendar = params.calendar;
		this.id = this.calendar.id + '_settings_slider';
		this.uid = this.id + '_' + Math.round(Math.random() * 1000000);
		this.zIndex = params.zIndex || 3100;
		this.sliderId = "calendar:settings-slider";

		this.inPersonal = this.calendar.util.userIsOwner();
		this.showGeneralSettings = !!(this.calendar.util.config.perm && this.calendar.util.config.perm.access);
		this.settings = this.calendar.util.config.settings;
		this.DOM = {};

		this.SLIDER_WIDTH = 500;
		this.SLIDER_DURATION = 80;
	}

	SettingsSlider.prototype = {
		show: function ()
		{
			this.calendar.util.doBxContextFix();

			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: this.createContent.bind(this),
				width: this.SLIDER_WIDTH,
				animationDuration: this.SLIDER_DURATION,
				events: {
					onClose: BX.proxy(this.hide, this),
					onCloseComplete: BX.proxy(this.destroy, this),
					onLoad: this.onLoadSlider.bind(this)
				}
			});

			this.calendar.disableKeyHandler();
			this.isOpenedState = true;
		},

		close: function ()
		{
			this.isOpenedState = false;
			BX.SidePanel.Instance.close();
		},

		isOpened: function()
		{
			return this.isOpenedState;
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

				this.calendar.util.restoreBxContextFix();
			}
		},

		createContent: function (slider)
		{
			return new Promise(function(resolve){
				top.BX.ajax.runAction('calendar.api.calendarajax.getSettingsSlider', {
					data: {
						isPersonal: this.inPersonal ? 'Y' : 'N',
						showGeneralSettings: this.showGeneralSettings ? 'Y' : 'N',
						uid: this.uid
					}
				}).then(
					function(response)
					{
						var html = response.data.html;
						slider.getData().set("sliderContent", html);
						var params = response.data.additionalParams;
						this.mailboxList = params.mailboxList;
						this.uid = params.uid;

						resolve(html);
					}.bind(this),
					function (response)
					{
						//Dom.remove(loader);
					}.bind(this)
				);
			}.bind(this));
		},

		onLoadSlider: function(event)
		{
			var slider = event.getSlider();
			this.DOM.content = slider.layout.content;
			this.sliderId = slider.getUrl();
			// Used to execute javasctipt and attach CSS from ajax responce
			BX.html(slider.layout.content, slider.getData().get("sliderContent"));
			this.initControls(this.uid);
		},

		initControls: function ()
		{
			BX.bind(top.BX(this.uid + '_save'), 'click', this.save.bind(this));
			BX.bind(top.BX(this.uid + '_close'), 'click', this.close.bind(this));

			this.DOM.buttonsWrap = this.DOM.content.querySelector('.calendar-form-buttons-fixed');
			if (this.DOM.buttonsWrap)
			{
				BX.ZIndexManager.register(this.DOM.buttonsWrap);
			}

			this.DOM.denyBusyInvitation = top.BX(this.uid + '_deny_busy_invitation');
			this.DOM.showWeekNumbers = top.BX(this.uid + '_show_week_numbers');

			if (this.inPersonal)
			{
				this.DOM.sectionSelect = top.BX(this.uid + '_meet_section');
				this.DOM.crmSelect = top.BX(this.uid + '_crm_section');
				this.DOM.showDeclined = top.BX(this.uid + '_show_declined');
				this.DOM.showTasks = top.BX(this.uid + '_show_tasks');
				this.DOM.syncTasks = top.BX(this.uid + '_sync_tasks');
				this.DOM.showCompletedTasks = top.BX(this.uid + '_show_completed_tasks');
				this.DOM.timezoneSelect = top.BX(this.uid + '_set_tz_sel');

				this.DOM.syncPeriodPast = top.BX(this.uid + '_sync_period_past');
				this.DOM.syncPeriodFuture = top.BX(this.uid + '_sync_period_future');

				this.DOM.sendFromEmailSelect = top.BX(this.uid + '_send_from_email');
			}

			if (BX.Type.isElementNode(this.DOM.sendFromEmailSelect))
			{
				this.emailSelectorControl = new BX.Calendar.Controls.EmailSelectorControl({
					selectNode: this.DOM.sendFromEmailSelect,
					allowAddNewEmail: true,
					mailboxList: this.mailboxList
				});

				this.DOM.emailHelpIcon = this.DOM.content.querySelector('.calendar-settings-question');

				if(this.DOM.emailHelpIcon && BX.Helper)
				{
					BX.Event.bind(this.DOM.emailHelpIcon, 'click', function(){BX.Helper.show("redirect=detail&code=12070142")});
					BX.UI.Hint.initNode(this.DOM.emailHelpIcon);
				}

				this.emailSelectorControl.setValue(this.calendar.util.getUserOption('sendFromEmail'));

				var emailWrap = this.DOM.content.querySelector('.calendar-settings-email-wrap')
				if (BX.Calendar.Util.isEventWithEmailGuestAllowed())
				{
					BX.Dom.removeClass(emailWrap, 'lock');
					this.DOM.sendFromEmailSelect.disabled = false;
				}
				else
				{
					BX.Dom.addClass(emailWrap, 'lock');
					this.DOM.sendFromEmailSelect.disabled = true;
					BX.Event.bind(this.DOM.sendFromEmailSelect.parentNode, 'click', function(){
						BX.UI.InfoHelper.show('limit_calendar_invitation_by_mail');
					});
				}
			}

			// General settings
			this.DOM.workTimeStart = top.BX(this.uid + 'work_time_start');
			this.DOM.workTimeEnd = top.BX(this.uid + 'work_time_end');
			this.DOM.weekHolidays = top.BX(this.uid + 'week_holidays');
			this.DOM.yearHolidays = top.BX(this.uid + 'year_holidays');
			this.DOM.yearWorkdays = top.BX(this.uid + 'year_workdays');

			// Access
			this.typeAccess = false;
			if (this.calendar.util.config.TYPE_ACCESS)
			{
				this.accessWrap = top.BX(this.uid + 'type-access-values-cont');
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

			// Set personal user settings
			if (this.inPersonal)
			{
				this.DOM.sectionSelect.options.length = 0;

				var sections = this.calendar.sectionManager.getSectionListForEdit();
				var meetSection = parseInt(this.calendar.util.getUserOption('meetSection'));
				var crmSection = parseInt(this.calendar.util.getUserOption('crmSection'));
				var section;
				var selected;

				for (var i = 0; i < sections.length; i++)
				{
					section = sections[i];
					if (section.belongsToOwner())
					{
						if (!meetSection)
						{
							meetSection = section.id;
						}
						selected = meetSection === parseInt(section.id);
						this.DOM.sectionSelect.options.add(new Option(section.name, section.id, selected, selected));

						if (!crmSection)
						{
							crmSection = section.id;
						}
						selected = crmSection === parseInt(section.id);
						this.DOM.crmSelect.options.add(new Option(section.name, section.id, selected, selected));
					}
				}
			}

			if(this.DOM.showDeclined)
			{
				this.DOM.showDeclined.checked = this.calendar.util.getUserOption('showDeclined');
			}

			var showTasks = this.calendar.util.getUserOption('showTasks') === 'Y';
			if(this.DOM.showTasks)
			{
				this.DOM.showTasks.checked = showTasks;
				BX.Event.bind(this.DOM.showTasks, 'click', function(){
					if(this.DOM.showCompletedTasks)
					{
						this.DOM.showCompletedTasks.disabled = !this.DOM.showTasks.checked;
						this.DOM.showCompletedTasks.checked = this.DOM.showCompletedTasks.checked && this.DOM.showTasks.checked;
					}
					if(this.DOM.syncTasks)
					{
						this.DOM.syncTasks.disabled = !this.DOM.showTasks.checked;
						this.DOM.syncTasks.checked = this.DOM.syncTasks.checked && this.DOM.showTasks.checked;
					}
				}.bind(this));
			}
			if(this.DOM.showCompletedTasks)
			{
				this.DOM.showCompletedTasks.checked = this.calendar.util.getUserOption('showCompletedTasks') === 'Y' && this.DOM.showTasks.checked;
				this.DOM.showCompletedTasks.disabled = !showTasks;
			}
			if(this.DOM.syncTasks)
			{
				this.DOM.syncTasks.checked = this.calendar.util.getUserOption('syncTasks') === 'Y' && this.DOM.showTasks.checked;
				this.DOM.syncTasks.disabled = !showTasks;
			}

			if (this.DOM.denyBusyInvitation)
			{
				this.DOM.denyBusyInvitation.checked = this.calendar.util.getUserOption('denyBusyInvitation');
			}

			if (this.DOM.showWeekNumbers)
			{
				this.DOM.showWeekNumbers.checked = this.calendar.util.showWeekNumber();
			}

			if(this.DOM.timezoneSelect)
			{
				this.DOM.timezoneSelect.value = this.calendar.util.getUserOption('timezoneName') || '';
			}

			if(this.DOM.syncPeriodPast)
			{
				this.DOM.syncPeriodPast.value = this.calendar.util.getUserOption('syncPeriodPast') || 3;
			}
			if(this.DOM.syncPeriodFuture)
			{
				this.DOM.syncPeriodFuture.value = this.calendar.util.getUserOption('syncPeriodFuture') || 12;
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
			if (this.DOM.syncTasks)
			{
				userSettings.syncTasks = this.DOM.syncTasks.checked ? 'Y' : 'N';
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

			if(this.DOM.syncPeriodPast)
			{
				userSettings.syncPeriodPast = this.DOM.syncPeriodPast.value;
			}

			if(this.DOM.syncPeriodFuture)
			{
				userSettings.syncPeriodFuture = this.DOM.syncPeriodFuture.value;
			}

			if(this.emailSelectorControl)
			{
				userSettings.sendFromEmail = this.emailSelectorControl.getValue();
			}

			var data = {
				action: 'save_settings',
				user_settings: userSettings,
				user_timezone_name: userSettings.userTimezoneName,
				userSettings: userSettings.sendFromEmail
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
				handler: BX.delegate(function()
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

			top.BX.Access.Init();

			this.accessWrapInner = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-access-inner-wrap'}}));
			this.accessTable = this.accessWrapInner.appendChild(BX.create("TABLE", {props: {className: "calendar-section-slider-access-table"}}));
			this.accessButtonWrap = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-options-container'}}));
			this.accessButton = this.accessButtonWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-list-slider-new-calendar-option-add'}, html: BX.message('EC_SEC_SLIDER_ACCESS_ADD')}));

			BX.bind(this.accessButton, 'click', BX.proxy(function()
			{
				top.BX.Access.ShowForm({
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
										this.insertAccessRow(top.BX.Access.GetProviderName(provider) + ' ' + selected[provider][code].name, code);
									}
								}
							}
						}
					}, this),
					bind: this.accessButton
				});
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
					props: {className: 'calendar-section-slider-access-container'}
				})),
				valueNode = selectNode.appendChild(BX.create('SPAN', {
					text: this.accessTasks[value] ? this.accessTasks[value].title : '',
					props: {className: 'calendar-section-slider-access-value'}
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

			this.accessPopupMenu = top.BX.PopupMenu.create(
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

			top.BX.addCustomEvent(this.accessPopupMenu.popupWindow, 'onPopupClose', function()
			{
				top.BX.PopupMenu.destroy(menuId);
				_this.accessPopupMenu = null;
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