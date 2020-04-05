;(function(window) {

	function Util(calendar, config, additionalParams)
	{
		this.calendar = calendar;
		this.config = config || {};
		this.additionalParams = additionalParams;
		this.dayLength = 86400000;

		this.type = this.config.type;
		this.userId = parseInt(this.config.userId);
		this.ownerId = parseInt(this.config.ownerId);

		this.accessNames = {};
		if (this.config.accessNames)
			this.handleAccessNames(this.config.accessNames);

		this.DATE_FORMAT_BX = BX.message("FORMAT_DATE");
		this.DATETIME_FORMAT_BX = BX.message("FORMAT_DATETIME");
		if ((this.DATETIME_FORMAT_BX.substr(0, this.DATE_FORMAT_BX.length) == this.DATE_FORMAT_BX))
			this.TIME_FORMAT_BX = BX.util.trim(this.DATETIME_FORMAT_BX.substr(this.DATE_FORMAT_BX.length));
		else
			this.TIME_FORMAT_BX = BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS';
		this.TIME_FORMAT_SHORT_BX = this.TIME_FORMAT_BX.replace(':SS', '');

		this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
		this.DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));
		if ((this.DATETIME_FORMAT.substr(0, this.DATE_FORMAT.length) == this.DATE_FORMAT))
			this.TIME_FORMAT = BX.util.trim(this.DATETIME_FORMAT.substr(this.DATE_FORMAT.length));
		else
			this.TIME_FORMAT = BX.date.convertBitrixFormat(BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
		this.TIME_FORMAT_SHORT = this.TIME_FORMAT.replace(':s', '');

		this.KEY_CODES = {
			'backspace': 8,
			'enter': 13,
			'escape': 27,
			'space': 32,
			'delete': 46,
			'left': 37,
			'right': 39,
			'up': 38,
			'down': 40,
			'z': 90,
			'y': 89,
			'shift': 16,
			'ctrl': 17,
			'alt': 18,
			'cmd': 91, // 93, 224, 17 Browser dependent
			'cmdRight': 93, // 93, 224, 17 Browser dependent?
			'pageUp': 33,
			'pageDown': 34
		};
	}

	Util.prototype = {
		getEventPath: function(entry)
		{
			var url = this.config.path;
			url += (url.indexOf('?') === -1 ? '?' : '&') + 'EVENT_ID=' + entry.id + '&EVENT_DATE=' + this.formatDate(entry.from);
			return url;
		},

		getWeekDays: function()
		{
			return this.config.weekDays;
		},

		getWeekStart: function()
		{
			return this.config.weekStart;
		},
		getWeekEnd: function()
		{
			return {'MO':'SU','TU':'MO','WE':'TU','TH':'WE','FR':'TH','SA':'FR', 'SU':'SA'}[this.config.weekStart];
		},

		getWeekDayOffset: function(day)
		{
			if (!this.weekDayOffsetIndex)
			{
				var i, weekDays = this.getWeekDays();
				this.weekDayOffsetIndex = {};
				for(i = 0; i < weekDays.length; i++)
					this.weekDayOffsetIndex[weekDays[i][2]] = i;
			}
			return this.weekDayOffsetIndex[day];
		},

		getWeekDayByInd: function(index)
		{
			return ['SU','MO','TU','WE','TH','FR','SA'][index];
		},

		isHoliday: function(date)
		{
			var i;
			if (!this.weekHolidays)
			{
				this.weekHolidays = {};
				for (i in this.config.week_holidays)
				{
					if (this.config.week_holidays.hasOwnProperty(i))
					{
						this.weekHolidays[this.config.week_holidays[i]] = true;
					}
				}

				this.yearHolidays = {};
				for (i in this.config.year_holidays)
				{
					if (this.config.year_holidays.hasOwnProperty(i))
					{
						this.yearHolidays[this.config.year_holidays[i]] = true;
					}
				}

				this.yearWorkdays = {};
				for (i in this.config.year_workdays)
				{
					if (this.config.year_workdays.hasOwnProperty(i))
					{
						this.yearWorkdays[this.config.year_workdays[i]] = true;
					}
				}
			}

			var
				day = [6,0,1,2,3,4,5][date.getDay()],
				monthDate = date.getDate(),
				month = date.getMonth();
			return (this.weekHolidays[day] || this.yearHolidays[monthDate + '.' + month]) && !this.yearWorkdays[monthDate + '.' + month];
		},

		isToday: function(date)
		{
			var curDate = new Date();
			return curDate.getDate() == date.getDate() && curDate.getMonth() == date.getMonth() && curDate.getFullYear() == date.getFullYear();
		},

		getWorkTime: function()
		{
			this.config.userWorkTime = this.config.userWorkTime || [];

			if (this.config.userSettings.work_time_start && this.config.userSettings.work_time_end)
			{
				this.workTime = {
					start: Math.floor(parseFloat(this.config.userSettings.work_time_start || 9)),
					end: Math.ceil(parseFloat(this.config.userSettings.work_time_end || 18))
				};
			}
			else
			{
				this.workTime = {
					start: Math.floor(parseFloat(this.config.userWorkTime[0] || 9)),
					end: Math.ceil(parseFloat(this.config.userWorkTime[1] || 18))
				};
			}

			this.getWorkTime = BX.proxy(function(){return this.workTime;}, this);
			return this.workTime;
		},

		setWorkTime: function(workTime)
		{
			this.workTime = {
				start: Math.min(Math.max(workTime.start, 0), 24),
				end: Math.min(Math.max(workTime.end, workTime.start), 24)
			};

			BX.userOptions.save('calendar', 'workTime', 'start', this.workTime.start);
			BX.userOptions.save('calendar', 'workTime', 'end', this.workTime.end);
			return this.workTime;
		},

		formatTime: function(h, m, skipMinutes)
		{
			if (BX.type.isDate(h))
			{
				m = h.getMinutes();
				h = h.getHours();
			}
			var res = '';
			if (skipMinutes !== true || !BX.isAmPmMode())
				skipMinutes = false;
			if (m == undefined)
			{
				m = '00';
			}
			else
			{
				m = parseInt(m, 10);
				if (isNaN(m))
				{
					m = '00';
				}
				else
				{
					if (m > 59)
						m = 59;
					m = (m < 10) ? '0' + m.toString() : m.toString();
				}
			}

			h = parseInt(h, 10);
			if (h > 24)
			{
				h = 24;
			}
			if (isNaN(h))
			{
				h = 0;
			}

			if (BX.isAmPmMode())
			{
				var ampm = 'am';

				if (h == 0)
				{
					h = 12;
				}
				else if (h == 12)
				{
					ampm = 'pm';
				}
				else if (h > 12)
				{
					ampm = 'pm';
					h -= 12;
				}

				if (skipMinutes)
				{
					res = h.toString() + ' ' + ampm;
				}
				else
				{
					res = h.toString() + ':' + m.toString() + ' ' + ampm;
				}
			}
			else
			{
				res = h.toString() + ':' + m.toString();
			}
			return res;
		},

		formatDate: function(timestamp)
		{
			if (BX.type.isDate(timestamp))
				timestamp = timestamp.getTime();
			return BX.date.format(this.DATE_FORMAT, timestamp / 1000);
		},

		formatDateTime: function(timestamp)
		{
			if (BX.type.isDate(timestamp))
				timestamp = timestamp.getTime();
			return BX.date.format(this.DATETIME_FORMAT, timestamp / 1000);
		},

		formatDateUsable: function(date, showYear)
		{
			var format = BX.date.convertBitrixFormat(BX.message('FORMAT_DATE'));
			if (BX.message('LANGUAGE_ID') == 'ru' || BX.message('LANGUAGE_ID')  == 'ua')
			{
				format = 'j F';
				if (date.getFullYear
					&& date.getFullYear() != new Date().getFullYear()
					&& showYear !== false
				)
				{
					format += ' Y';
				}
			}

			return BX.date.format([
				["today", "today"],
				["tommorow", "tommorow"],
				["yesterday", "yesterday"],
				["" , format]
			], date);
		},

		parseTime: function(str)
		{
			var date = this.parseDate(BX.date.format(this.DATE_FORMAT, new Date()) + ' ' + str, false);
			return date ? {
				h: date.getHours(),
				m: date.getMinutes()
			} : date;
		},

		parseDate: function(str, format, trimSeconds)
		{
			var
				i, cnt, k,
				regMonths,
				bUTC = false;

			if (!format)
				format = BX.message('FORMAT_DATETIME');

			str = BX.util.trim(str);

			if (trimSeconds !== false)
				format = format.replace(':SS', '');

			if (BX.type.isNotEmptyString(str))
			{
				regMonths = '';
				for (i = 1; i <= 12; i++)
				{
					regMonths = regMonths + '|' + BX.message('MON_'+i);
				}

				var
					expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig'),
					aDate = str.match(expr),
					aFormat = BX.message('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
					aDateArgs = [],
					aFormatArgs = [],
					aResult = {};

				if (!aDate)
				{
					return null;
				}

				if(aDate.length > aFormat.length)
				{
					aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
				}

				for(i = 0, cnt = aDate.length; i < cnt; i++)
				{
					if(BX.util.trim(aDate[i]) != '')
					{
						aDateArgs[aDateArgs.length] = aDate[i];
					}
				}

				for(i = 0, cnt = aFormat.length; i < cnt; i++)
				{
					if(BX.util.trim(aFormat[i]) != '')
					{
						aFormatArgs[aFormatArgs.length] = aFormat[i];
					}
				}

				var m = BX.util.array_search('MMMM', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
				else
				{
					m = BX.util.array_search('M', aFormatArgs);
					if (m > 0)
					{
						aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
						aFormatArgs[m] = "MM";
					}
				}

				for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
				{
					k = aFormatArgs[i].toUpperCase();
					aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
				}

				if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
				{
					var d = new Date();

					if(bUTC)
					{
						d.setUTCDate(1);
						d.setUTCFullYear(aResult['YYYY']);
						d.setUTCMonth(aResult['MM'] - 1);
						d.setUTCDate(aResult['DD']);
						d.setUTCHours(0, 0, 0);
					}
					else
					{
						d.setDate(1);
						d.setFullYear(aResult['YYYY']);
						d.setMonth(aResult['MM'] - 1);
						d.setDate(aResult['DD']);
						d.setHours(0, 0, 0);
					}

					if(
						(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
						&& !isNaN(aResult['MI'])
					)
					{
						if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
						{
							var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
							var h = parseInt(aResult['H']||aResult['G']||0, 10);
							if(bPM)
							{
								aResult['HH'] = h + (h == 12 ? 0 : 12);
							}
							else
							{
								aResult['HH'] = h < 12 ? h : 0;
							}
						}
						else
						{
							aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
						}

						if (isNaN(aResult['SS']))
							aResult['SS'] = 0;

						if(bUTC)
						{
							d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
						else
						{
							d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
					}

					return d;
				}
			}

			return null;
		},

		findTargetNode: function(node, parentCont)
		{
			if (node)
			{
				var
					res = false,
					prefix = 'data-bx-calendar', i;

				if (!parentCont)
					parentCont = this.calendar.viewsCont;

				if (node.attributes && node.attributes.length)
				{
					for (i = 0; i < node.attributes.length; i++)
					{
						if (node.attributes[i].name && node.attributes[i].name.substr(0, prefix.length) == prefix)
						{
							res = node;
							break;
						}
					}
				}

				if (!res)
				{
					res = BX.findParent(node, function(n)
					{
						var j;
						if (n.attributes && n.attributes.length)
						{
							for (j = 0; j < n.attributes.length; j++)
							{
								if (n.attributes[j].name && n.attributes[j].name.substr(0, prefix.length) == prefix)
									return true;
							}
						}
						return false;
					}, parentCont);
				}

			}

			return res;
		},

		getViewHeight: function()
		{
			var
				minHeight = 756,
				height = BX.GetWindowInnerSize(document).innerHeight - 300;
			return Math.max(minHeight, height);
		},

		showWeekNumber: function()
		{
			return this.getUserOption('showWeekNumbers', 'N') == 'Y';
		},

		getScrollbarWidth: function()
		{
			// add outer div
			var
				outer = this.calendar.mainCont.appendChild(BX.create('DIV', {props: {className: 'calendar-tmp-outer'}})),
				widthNoScroll = outer.offsetWidth;

			// force scrollbars
			outer.style.overflow = "scroll";

			// add inner div
			var
				inner = outer.appendChild(BX.create('DIV', {props: {className: 'calendar-tmp-inner'}})),
				widthWithScroll = inner.offsetWidth,
				result = widthNoScroll - widthWithScroll;

			BX.cleanNode(outer, true);

			this.getScrollbarWidth = function(){return result;};
			return result;
		},

		getMessagePlural: function(messageId, number)
		{
			var pluralForm, langId;

			langId = BX.message('LANGUAGE_ID') || 'en';
			number = parseInt(number);

			if (number < 0)
			{
				number = -1*number;
			}

			if (langId)
			{
				switch (langId)
				{
					case 'de':
					case 'en':
						pluralForm = ((number !== 1) ? 1 : 0);
						break;

					case 'ru':
					case 'ua':
						pluralForm = (((number%10 === 1) && (number%100 !== 11)) ? 0 : (((number%10 >= 2) && (number%10 <= 4) && ((number%100 < 10) || (number%100 >= 20))) ? 1 : 2));
						break;

					default:
						pluralForm = 1;
						break;
				}
			}
			else
			{
				pluralForm = 1;
			}

			return BX.message(messageId + '_PLURAL_' + pluralForm);
		},

		getUserOption: function(name, defaultValue)
		{
			if (this.config.userSettings[name] === undefined)
				return defaultValue;
			return this.config.userSettings[name];
		},

		setUserOption: function(name, value)
		{
			if (this.config.userSettings[name] !== value)
			{
				BX.userOptions.save('calendar', 'user_settings', name, value);
				this.config.userSettings[name] = value;
			}
		},

		getKeyCodes: function()
		{
			return this.KEY_CODES;
		},

		getMousePos: function(e)
		{
			if (!e)
				e = window.event;

			var x = 0, y = 0;
			if (e.pageX || e.pageY)
			{
				x = e.pageX;
				y = e.pageY;
			}
			else if (e.clientX || e.clientY)
			{
				x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
				y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
			}

			return {x: x, y: y};
		},

		getDayCode: function(date)
		{
			return date.getFullYear() + '-' + ("0"+(~~(date.getMonth() + 1))).substr(-2,2) + '-' + ("0"+(~~(date.getDate()))).substr(-2,2);
			//return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
		},

		getTextColor: function(color)
		{
			if (!color)
				return false;

			if (color.charAt(0) == "#")
				color = color.substring(1, 7);
			var
				r = parseInt(color.substring(0, 2), 16),
				g = parseInt(color.substring(2, 4), 16),
				b = parseInt(color.substring(4, 6), 16),
				light = (r * 0.8 + g + b * 0.2) / 510 * 100;
			return light < 50;
		},

		getTimeValue: function(date)
		{
			return date.getHours() + Math.round(date.getMinutes() * 100 / 60) / 100;
		},

		getTimeEx: function(date)
		{
			return Math.round(date.getTime() / 60000) * 60000;
		},

		getTimeByFraction: function(val, round)
		{
			round = round || 5;
			val = Math.min(Math.max(val, 0), 24);

			var
				useFloor = true,
				h = Math.floor(val),
				m = useFloor ? (Math.floor((val - h) * 60 / round) * round) : (Math.round((val - h) * 60 / round) * round);

			if (m == 60)
			{
				m = 0;
				h++;
			}
			if (h == 24 && m == 0)
			{
				h = 23;
				m = 59;
			}

			return {h: h, m: m};
		},

		getWeekNumberInMonthByDate: function(origDate)
		{
			var date = new Date();
			date.setFullYear(origDate.getFullYear(), origDate.getMonth(), 1);
			return parseInt(BX.date.format('W', origDate.getTime() / 1000)) - parseInt(BX.date.format('W', date.getTime() / 1000));
		},

		getRemindersList: function()
		{
			if (!this.reminderList)
			{
				this.reminderList = [
					{value: 0, label: BX.message("EC_REMIND_0"), shortLabel: BX.message("EC_REMIND_SHORT_0")},
					{value: 5, label: BX.message("EC_REMIND_5"), shortLabel: BX.message("EC_REMIND_SHORT_5")},
					{value: 10, label: BX.message("EC_REMIND_10"), shortLabel: BX.message("EC_REMIND_SHORT_10")},
					{value: 15, label: BX.message("EC_REMIND_15"), shortLabel: BX.message("EC_REMIND_SHORT_15")},
					{value: 30, label: BX.message("EC_REMIND_30"), shortLabel: BX.message("EC_REMIND_SHORT_30")},
					{value: 60, label: BX.message("EC_REMIND_60"), shortLabel: BX.message("EC_REMIND_SHORT_60")},
					{value: 120, label: BX.message("EC_REMIND_120"), shortLabel: BX.message("EC_REMIND_SHORT_120")},
					{value: 1440, label: BX.message("EC_REMIND_1440"), shortLabel: BX.message("EC_REMIND_SHORT_1440")},
					{value: 2880, label: BX.message("EC_REMIND_2880"), shortLabel: BX.message("EC_REMIND_SHORT_2880")}
				];
			}

			return this.reminderList;
		},

		getSimpleTimeList: function()
		{
			var i, res = [];
			for (i = 0; i < 24; i++)
			{
				res.push({value: i * 60, label: this.formatTime(i, 0)});
				res.push({value: i * 60 + 30, label: this.formatTime(i, 30)});
			}
			this.getSimpleTimeList = function(){return res;};
			return res;
		},

		adaptTimeValue: function(timeValue)
		{
			timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
			var
				timeList = this.getSimpleTimeList(),
				diff = 24 * 60,
				ind = false,
				i;

			for (i = 0; i < timeList.length; i++)
			{
				if (Math.abs(timeList[i].value - timeValue) < diff)
				{
					diff = Math.abs(timeList[i].value - timeValue);
					ind = i;
					if (diff <= 15)
						break;
				}
			}

			return timeList[ind || 0];
		},

		getMeetingRoomList: function()
		{
			return this.config.meetingRooms || [];
		},

		getLocationList: function()
		{
			return this.additionalParams.locationList || [];
		},

		setLocationList: function(locationList)
		{
			this.additionalParams.locationList = locationList;
		},

		mergeSocnetDestinationConfig: function(socnetDestination)
		{
			if (socnetDestination.USERS)
			{
				for (var code in socnetDestination.USERS)
				{
					if (socnetDestination.USERS.hasOwnProperty(code) && !this.additionalParams['socnetDestination'].USERS[code])
					{
						this.additionalParams['socnetDestination'].USERS[code] = socnetDestination.USERS[code];
					}
				}
			}
		},

		getSocnetDestinationConfig: function(key)
		{
			var
				res,
				socnetDestination = this.additionalParams['socnetDestination'] || {};

			if (key == 'items')
			{
				res = {
					users: socnetDestination.USERS || {},
					groups: socnetDestination.EXTRANET_USER == 'Y' || socnetDestination.DENY_TOALL ? {} :
					{
						UA: {id: 'UA', name: socnetDestination.DEPARTMENT ? BX.message('EC_SOCNET_DESTINATION_4') : BX.message('EC_SOCNET_DESTINATION_3')}},
					sonetgroups: socnetDestination.SONETGROUPS || {},
					department: socnetDestination.DEPARTMENT || {},
					departmentRelation: socnetDestination.DEPARTMENT_RELATION || {}
				};
			}
			else if (key == 'itemsLast' && socnetDestination.LAST)
			{
				res = {
					users: socnetDestination.LAST.USERS || {},
					groups: socnetDestination.EXTRANET_USER == 'Y' ? {} : {UA: true},
					sonetgroups: socnetDestination.LAST.SONETGROUPS || {},
					department: socnetDestination.LAST.DEPARTMENT || {}
				};
			}
			else if (key == 'itemsSelected')
			{
				res = socnetDestination.SELECTED || {};
			}
			return res || {};
		},

		getActionUrl: function()
		{
			return this.config.actionUrl;
		},

		getTimezoneList: function()
		{
			return this.additionalParams.timezoneList || [];
		},

		getDefaultColors: function()
		{
			return this.additionalParams.defaultColorsList;
		},

		getFormSettings: function(formType)
		{
			return this.additionalParams.formSettings && this.additionalParams.formSettings[formType] ? this.additionalParams.formSettings[formType] : {};
		},

		saveFormSettings: function(formType, settings)
		{
			if (formType)
			{
				BX.userOptions.save('calendar', formType, 'pinnedFields', settings.pinnedFields);
			}
		},

		randomInt: function (min, max)
		{
			return Math.round(min - 0.5 + Math.random() * (max - min + 1));
		},

		handleAccessNames: function(accessNames)
		{
			for (var code in accessNames)
			{
				if (accessNames.hasOwnProperty(code))
				{
					this.accessNames[code] = accessNames[code];
				}
			}
		},

		getAccessName: function(code)
		{
			return this.accessNames[code] || code;
		},

		getSectionAccessTasks: function()
		{
			return this.config.sectionAccessTasks;
		},

		getTypeAccessTasks: function()
		{
			return this.config.typeAccessTasks;
		},

		getDefaultTypeAccessTask: function()
		{
			var taskId, accessTasks = this.getTypeAccessTasks();
			for(taskId in accessTasks)
			{
				if (accessTasks.hasOwnProperty(taskId) && accessTasks[taskId].name == 'calendar_type_view')
				{
					break;
				}
			}

			this.getDefaultTypeAccessTask = function(){return taskId;};
			return taskId;
		},

		getDefaultSectionAccessTask: function()
		{
			var taskId, accessTasks = this.getSectionAccessTasks();
			for(taskId in accessTasks)
			{
				if (accessTasks.hasOwnProperty(taskId) && accessTasks[taskId].name == 'calendar_view')
				{
					break;
				}
			}

			this.getDefaultSectionAccessTask = function(){return taskId;};
			return taskId;
		},

		getSuperposedTrackedUsers: function()
		{
			return (this.config.trackingUsersList || []).sort(function(a, b)
			{
				if (!a.LAST_NAME)
					a.LAST_NAME = '';
				if (!b.LAST_NAME)
					b.LAST_NAME = '';
				return a.LAST_NAME.localeCompare(b.LAST_NAME);
			});
		},

		getSuperposedTrackedGroups: function()
		{
			return this.config.trackingGroupList || [];
		},

		isUserCalendar: function()
		{
			return this.type === 'user';
		},

		isGroupCalendar: function()
		{
			return this.type === 'group';
		},

		userIsOwner: function()
		{
			return this.isUserCalendar() && this.userId === this.ownerId;
		},

		hexToRgb: function(hex)
		{
			var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
			return result ? {
				r: parseInt(result[1], 16),
				g: parseInt(result[2], 16),
				b: parseInt(result[3], 16)
			} : null;
		},

		hexToRgba: function(hex, opacity)
		{
			var color = this.hexToRgb(hex);
			if (!color)
				color = this.hexToRgb('#9dcf00');
			return 'rgba(' + color.r + ', ' + color.g + ', ' + color.b + ', ' + opacity + ')';
		},

		parseLocation : function(str)
		{
			if (!str)
				str = '';

			var
				ar,
				res = {
				type : false,
				value : false,
				str : str
			};

			if (str.length > 5 && str.substr(0, 5) == 'ECMR_')
			{
				res.type = 'mr';
				ar = str.split('_');
				if (ar.length >= 2)
				{
					if (!isNaN(parseInt(ar[1])) && parseInt(ar[1]) > 0)
					{
						res.value = res.mrid = parseInt(ar[1]);
					}
					if (!isNaN(parseInt(ar[2])) && parseInt(ar[2]) > 0)
					{
						res.mrevid = parseInt(ar[2]);
					}
				}
			}
			else if (str.length > 5 && str.substr(0, 9) == 'calendar_')
			{
				res.type = 'calendar';
				ar = str.split('_');
				if (ar.length >= 2)
				{
					if (!isNaN(parseInt(ar[1])) && parseInt(ar[1]) > 0)
					{
						res.value = res.room_id = parseInt(ar[1]);
					}
					if (!isNaN(parseInt(ar[2])) && parseInt(ar[2]) > 0)
					{
						res.room_event_id = parseInt(ar[2]);
					}
				}
			}

			return res;
		},

		getTextLocation: function(location)
		{
			var
				value = typeof location === 'object' ? location : this.parseLocation(location),
				i, str = value.str;

			if (value.type == 'mr')
			{
				var meetingRooms = this.calendar.util.getMeetingRoomList();
				for (i = 0; i < meetingRooms.length; i++)
				{
					if (value.value == meetingRooms[i].ID)
					{
						str = meetingRooms[i].NAME;
						break;
					}
				}
			}

			if (value.type == 'calendar')
			{
				var locationList = this.calendar.util.getLocationList();

				for (i = 0; i < locationList.length; i++)
				{
					if (value.value == locationList[i].ID)
					{
						str = locationList[i].NAME;
						break;
					}
				}
			}

			return str;
		},

		getTextReminder: function(min)
		{
			if (BX.util.in_array(min, [0,5,10,15,30,60,120,1440,2880]))
			{
				return BX.message('EC_REMIND_SHORT_' + min);
			}
			return '';
		},

		getEditTaskPath: function()
		{
			return this.config.editTaskPath;
		},

		getViewTaskPath: function(taskId)
		{
			return this.config.viewTaskPath.replace('#task_id#', taskId);
		},

		readOnlyMode: function()
		{
			this.readOnly = this.config.readOnly;

			if (!this.readOnly)
			{
				var sectionList = this.calendar.sectionController.getSectionListForEdit();
				if (!sectionList || !sectionList.length)
					this.readOnly = true;
			}

			this.readOnlyMode = BX.proxy(function(){return this.readOnly;}, this);
			return this.readOnly;
		},

		getLoader: function(size)
		{
			var style = size ? 'style="width: '+ parseInt(size) +'px; height: '+ parseInt(size) +'px;"' : '';
			return BX.create('DIV', {props:{className: 'calendar-loader'},html: '<svg class="calendar-loader-circular"' +
			style + ' viewBox="25 25 50 50">' +
				'<circle class="calendar-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>' +
				'<circle class="calendar-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>' +
			'</svg>'});
		},

		applyHacksForPopupzIndex: function()
		{
			var zIndex = 3200;
			if (BX.PopupMenu && BX.PopupMenu.Data)
			{
				for(var id in BX.PopupMenu.Data)
				{
					if (BX.PopupMenu.Data.hasOwnProperty(id)
						&& BX.PopupMenu.Data[id].popupWindow
						&& BX.PopupMenu.Data[id].popupWindow.isShown()
					)
					{
						BX.PopupMenu.Data[id].popupWindow.params.zIndex = zIndex;
						BX.PopupMenu.Data[id].popupWindow.popupContainer.style.zIndex = zIndex;
					}
				}
			}
		},

		applyHacksHandlersForPopupzIndex: function()
		{
			var
				_this = this,
				zIndex = 3200;
			BX.addCustomEvent('onPopupShow', function(popupWindow){
				if (_this.calendar.viewSlider && _this.calendar.viewSlider.isOpened()
					|| _this.calendar.editSlider && _this.calendar.editSlider.isOpened())
				{
					popupWindow.params.zIndex = zIndex;
					popupWindow.popupContainer.style.zIndex = zIndex;
				}
			});
		},

		isFilterEnabled: function()
		{
			return this.userIsOwner() && this.config.counters;
		},

		getCalDavConnections: function()
		{
			return this.config.connections || [];
		},

		isRichLocationEnabled: function()
		{
			return !!this.config.locationFeatureEnabled;
		},

		isDarkColor: function(color)
		{
			color = color.toLowerCase();
			if ({'#9dcf00':true,'#2FC6F6':true,'#56D1E0':true,'#FFA900':true,'#47E4C2':true,'#F87396':true,'#9985DD':true,'#A8ADB4':true,'#AF7E00':true}[color])
				return false;

			if (!color)
				return false;

			if (color.charAt(0) == "#")
				color = color.substring(1, 7);
			var
				r = parseInt(color.substring(0, 2), 16),
				g = parseInt(color.substring(2, 4), 16),
				b = parseInt(color.substring(4, 6), 16),
				light = (r * 0.8 + g + b * 0.2) / 510 * 100;
			return light < 50;
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.Util = Util;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.Util = Util;
		});
	}
})(window);