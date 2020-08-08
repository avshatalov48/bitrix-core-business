this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue = this.BX.UI.Vue || {};
(function (exports,main_core,main_popup,ui_vue) {
	'use strict';

	var Format = {
	  re: /[,.\- :\/\\]/,
	  year: 'YYYY',
	  month: 'MM',
	  day: 'DD',
	  hours: 'HH',
	  hours12: 'H',
	  hoursZeroFree: 'GG',
	  hoursZeroFree12: 'G',
	  minutes: 'MI',
	  seconds: 'SS',
	  ampm: 'TT',
	  ampmLower: 'T',
	  format: function format(date, dateFormat) {
	    var hours12 = date.getHours();

	    if (hours12 === 0) {
	      hours12 = 12;
	    } else if (hours12 > 12) {
	      hours12 -= 12;
	    }

	    var ampm = date.getHours() > 11 ? 'PM' : 'AM';
	    return dateFormat.replace(this.year, function () {
	      return date.getFullYear();
	    }).replace(this.month, function (match) {
	      return paddNum(date.getMonth() + 1, match.length);
	    }).replace(this.day, function (match) {
	      return paddNum(date.getDate(), match.length);
	    }).replace(this.hours, function () {
	      return paddNum(date.getHours(), 2);
	    }).replace(this.hoursZeroFree, function () {
	      return date.getHours();
	    }).replace(this.hours12, function () {
	      return paddNum(hours12, 2);
	    }).replace(this.hoursZeroFree12, function () {
	      return hours12;
	    }).replace(this.minutes, function (match) {
	      return paddNum(date.getMinutes(), match.length);
	    }).replace(this.seconds, function (match) {
	      return paddNum(date.getSeconds(), match.length);
	    }).replace(this.ampm, function () {
	      return ampm;
	    }).replace(this.ampmLower, function () {
	      return ampm.toLowerCase();
	    });
	  },
	  parse: function parse(dateString, dateFormat) {
	    var r = {
	      day: 1,
	      month: 1,
	      year: 1970,
	      hours: 0,
	      minutes: 0,
	      seconds: 0
	    };
	    var dateParts = dateString.split(this.re);
	    var formatParts = dateFormat.split(this.re);
	    var partsSize = formatParts.length;
	    var isPm = false;

	    for (var i = 0; i < partsSize; i++) {
	      var part = dateParts[i];

	      switch (formatParts[i]) {
	        case this.ampm:
	        case this.ampmLower:
	          isPm = part.toUpperCase() === 'PM';
	          break;
	      }
	    }

	    for (var _i = 0; _i < partsSize; _i++) {
	      var _part = dateParts[_i];
	      var partInt = parseInt(_part);

	      switch (formatParts[_i]) {
	        case this.year:
	          r.year = partInt;
	          break;

	        case this.month:
	          r.month = partInt;
	          break;

	        case this.day:
	          r.day = partInt;
	          break;

	        case this.hours:
	        case this.hoursZeroFree:
	          r.hours = partInt;
	          break;

	        case this.hours12:
	        case this.hoursZeroFree12:
	          r.hours = isPm ? (partInt > 11 ? 11 : partInt) + 12 : partInt > 11 ? 0 : partInt;
	          break;

	        case this.minutes:
	          r.minutes = partInt;
	          break;

	        case this.seconds:
	          r.seconds = partInt;
	          break;
	      }
	    }

	    return r;
	  },
	  isAmPm: function isAmPm(dateFormat) {
	    return dateFormat.indexOf(this.ampm) >= 0 || dateFormat.indexOf(this.ampmLower) >= 0;
	  },
	  convertHoursToAmPm: function convertHoursToAmPm(hours, isPm) {
	    return isPm ? (hours > 11 ? 11 : hours) + 12 : hours > 11 ? 0 : hours;
	  }
	};
	var VueDatePick = {
	  props: {
	    show: {
	      type: Boolean,
	      default: true
	    },
	    value: {
	      type: String,
	      default: ''
	    },
	    format: {
	      type: String,
	      default: 'MM/DD/YYYY'
	    },
	    displayFormat: {
	      type: String
	    },
	    editable: {
	      type: Boolean,
	      default: true
	    },
	    hasInputElement: {
	      type: Boolean,
	      default: true
	    },
	    inputAttributes: {
	      type: Object
	    },
	    selectableYearRange: {
	      type: Number,
	      default: 40
	    },
	    parseDate: {
	      type: Function
	    },
	    formatDate: {
	      type: Function
	    },
	    pickTime: {
	      type: Boolean,
	      default: false
	    },
	    pickMinutes: {
	      type: Boolean,
	      default: true
	    },
	    pickSeconds: {
	      type: Boolean,
	      default: false
	    },
	    isDateDisabled: {
	      type: Function,
	      default: function _default() {
	        return false;
	      }
	    },
	    nextMonthCaption: {
	      type: String,
	      default: 'Next month'
	    },
	    prevMonthCaption: {
	      type: String,
	      default: 'Previous month'
	    },
	    setTimeCaption: {
	      type: String,
	      default: 'Set time:'
	    },
	    closeButtonCaption: {
	      type: String,
	      default: 'Close'
	    },
	    mobileBreakpointWidth: {
	      type: Number,
	      default: 530
	    },
	    weekdays: {
	      type: Array,
	      default: function _default() {
	        return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
	      }
	    },
	    months: {
	      type: Array,
	      default: function _default() {
	        return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	      }
	    },
	    startWeekOnSunday: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data: function data() {
	    return {
	      inputValue: this.valueToInputFormat(this.value),
	      currentPeriod: this.getPeriodFromValue(this.value, this.format),
	      direction: undefined,
	      positionClass: undefined,
	      opened: !this.hasInputElement && this.show
	    };
	  },
	  computed: {
	    valueDate: function valueDate() {
	      var value = this.value;
	      var format = this.format;
	      return value ? this.parseDateString(value, format) : undefined;
	    },
	    isReadOnly: function isReadOnly() {
	      return !this.editable || this.inputAttributes && this.inputAttributes.readonly;
	    },
	    isValidValue: function isValidValue() {
	      var valueDate = this.valueDate;
	      return this.value ? Boolean(valueDate) : true;
	    },
	    currentPeriodDates: function currentPeriodDates() {
	      var _this = this;

	      var _this$currentPeriod = this.currentPeriod,
	          year = _this$currentPeriod.year,
	          month = _this$currentPeriod.month;
	      var days = [];
	      var date = new Date(year, month, 1);
	      var today = new Date();
	      var offset = this.startWeekOnSunday ? 1 : 0; // append prev month dates

	      var startDay = date.getDay() || 7;

	      if (startDay > 1 - offset) {
	        for (var i = startDay - (2 - offset); i >= 0; i--) {
	          var prevDate = new Date(date);
	          prevDate.setDate(-i);
	          days.push({
	            outOfRange: true,
	            date: prevDate
	          });
	        }
	      }

	      while (date.getMonth() === month) {
	        days.push({
	          date: new Date(date)
	        });
	        date.setDate(date.getDate() + 1);
	      } // append next month dates


	      var daysLeft = 7 - days.length % 7;

	      for (var _i2 = 1; _i2 <= daysLeft; _i2++) {
	        var nextDate = new Date(date);
	        nextDate.setDate(_i2);
	        days.push({
	          outOfRange: true,
	          date: nextDate
	        });
	      } // define day states


	      days.forEach(function (day) {
	        day.disabled = _this.isDateDisabled(day.date);
	        day.today = areSameDates(day.date, today);
	        day.dateKey = [day.date.getFullYear(), day.date.getMonth() + 1, day.date.getDate()].join('-');
	        day.selected = _this.valueDate ? areSameDates(day.date, _this.valueDate) : false;
	      });
	      return chunkArray(days, 7);
	    },
	    yearRange: function yearRange() {
	      var years = [];
	      var currentYear = this.currentPeriod.year;
	      var startYear = currentYear - this.selectableYearRange;
	      var endYear = currentYear + this.selectableYearRange;

	      for (var i = startYear; i <= endYear; i++) {
	        years.push(i);
	      }

	      return years;
	    },
	    hasCurrentTime: function hasCurrentTime() {
	      return !!this.valueDate;
	    },
	    currentTime: function currentTime() {
	      var currentDate = this.valueDate;
	      var hours = currentDate ? currentDate.getHours() : 12;
	      var minutes = currentDate ? currentDate.getMinutes() : 0;
	      var seconds = currentDate ? currentDate.getSeconds() : 0;
	      return {
	        hours: hours,
	        minutes: minutes,
	        seconds: seconds,
	        hoursPadded: paddNum(hours, 1),
	        minutesPadded: paddNum(minutes, 2),
	        secondsPadded: paddNum(seconds, 2)
	      };
	    },
	    directionClass: function directionClass() {
	      return this.direction ? "vdp".concat(this.direction, "Direction") : undefined;
	    },
	    weekdaysSorted: function weekdaysSorted() {
	      if (this.startWeekOnSunday) {
	        var weekdays = this.weekdays.slice();
	        weekdays.unshift(weekdays.pop());
	        return weekdays;
	      } else {
	        return this.weekdays;
	      }
	    }
	  },
	  watch: {
	    show: function show(value) {
	      this.opened = value;
	    },
	    value: function value(_value) {
	      if (this.isValidValue) {
	        this.inputValue = this.valueToInputFormat(_value);
	        this.currentPeriod = this.getPeriodFromValue(_value, this.format);
	      }
	    },
	    currentPeriod: function currentPeriod(_currentPeriod, oldPeriod) {
	      var currentDate = new Date(_currentPeriod.year, _currentPeriod.month).getTime();
	      var oldDate = new Date(oldPeriod.year, oldPeriod.month).getTime();
	      this.direction = currentDate !== oldDate ? currentDate > oldDate ? 'Next' : 'Prev' : undefined;
	    }
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.removeCloseEvents();
	    this.teardownPosition();
	  },
	  methods: {
	    valueToInputFormat: function valueToInputFormat(value) {
	      return !this.displayFormat ? value : this.formatDateToString(this.parseDateString(value, this.format), this.displayFormat) || value;
	    },
	    getPeriodFromValue: function getPeriodFromValue(dateString, format) {
	      var date = this.parseDateString(dateString, format) || new Date();
	      return {
	        month: date.getMonth(),
	        year: date.getFullYear()
	      };
	    },
	    parseDateString: function parseDateString(dateString, dateFormat) {
	      return !dateString ? undefined : this.parseDate ? this.parseDate(dateString, dateFormat) : this.parseSimpleDateString(dateString, dateFormat);
	    },
	    formatDateToString: function formatDateToString(date, dateFormat) {
	      return !date ? '' : this.formatDate ? this.formatDate(date, dateFormat) : this.formatSimpleDateToString(date, dateFormat);
	    },
	    parseSimpleDateString: function parseSimpleDateString(dateString, dateFormat) {
	      var r = Format.parse(dateString, dateFormat);
	      var day = r.day,
	          month = r.month,
	          year = r.year,
	          hours = r.hours,
	          minutes = r.minutes,
	          seconds = r.seconds;
	      var resolvedDate = new Date([paddNum(year, 4), paddNum(month, 2), paddNum(day, 2)].join('-'));

	      if (isNaN(resolvedDate)) {
	        return undefined;
	      } else {
	        var date = new Date(year, month - 1, day);
	        [[year, 'setFullYear'], [hours, 'setHours'], [minutes, 'setMinutes'], [seconds, 'setSeconds']].forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              value = _ref2[0],
	              method = _ref2[1];

	          typeof value !== 'undefined' && date[method](value);
	        });
	        return date;
	      }
	    },
	    formatSimpleDateToString: function formatSimpleDateToString(date, dateFormat) {
	      return Format.format(date, dateFormat);
	    },
	    getHourList: function getHourList() {
	      var list = [];
	      var isAmPm = Format.isAmPm(this.displayFormat || this.format);

	      for (var hours = 0; hours < 24; hours++) {
	        var hoursDisplay = hours > 12 ? hours - 12 : hours === 0 ? 12 : hours;
	        hoursDisplay += hours > 11 ? ' pm' : ' am';
	        list.push({
	          value: hours,
	          name: isAmPm ? hoursDisplay : hours
	        });
	      }

	      return list;
	    },
	    incrementMonth: function incrementMonth() {
	      var increment = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
	      var refDate = new Date(this.currentPeriod.year, this.currentPeriod.month);
	      var incrementDate = new Date(refDate.getFullYear(), refDate.getMonth() + increment);
	      this.currentPeriod = {
	        month: incrementDate.getMonth(),
	        year: incrementDate.getFullYear()
	      };
	    },
	    processUserInput: function processUserInput(userText) {
	      var userDate = this.parseDateString(userText, this.displayFormat || this.format);
	      this.inputValue = userText;
	      this.$emit('input', userDate ? this.formatDateToString(userDate, this.format) : userText);
	    },
	    open: function open() {
	      if (!this.opened) {
	        this.opened = true;
	        this.currentPeriod = this.getPeriodFromValue(this.value, this.format);
	        this.addCloseEvents();
	        this.setupPosition();
	      }

	      this.direction = undefined;
	    },
	    close: function close() {
	      if (this.opened) {
	        this.opened = false;
	        this.direction = undefined;
	        this.removeCloseEvents();
	        this.teardownPosition();
	      }

	      this.$emit('close');
	    },
	    closeViaOverlay: function closeViaOverlay(e) {
	      if (this.hasInputElement && e.target === this.$refs.outerWrap) {
	        this.close();
	      }
	    },
	    addCloseEvents: function addCloseEvents() {
	      var _this2 = this;

	      if (!this.closeEventListener) {
	        this.closeEventListener = function (e) {
	          return _this2.inspectCloseEvent(e);
	        };

	        ['click', 'keyup', 'focusin'].forEach(function (eventName) {
	          return document.addEventListener(eventName, _this2.closeEventListener);
	        });
	      }
	    },
	    inspectCloseEvent: function inspectCloseEvent(event) {
	      if (event.keyCode) {
	        event.keyCode === 27 && this.close();
	      } else if (!(event.target === this.$el) && !this.$el.contains(event.target)) {
	        this.close();
	      }
	    },
	    removeCloseEvents: function removeCloseEvents() {
	      var _this3 = this;

	      if (this.closeEventListener) {
	        ['click', 'keyup'].forEach(function (eventName) {
	          return document.removeEventListener(eventName, _this3.closeEventListener);
	        });
	        delete this.closeEventListener;
	      }
	    },
	    setupPosition: function setupPosition() {
	      var _this4 = this;

	      if (!this.positionEventListener) {
	        this.positionEventListener = function () {
	          return _this4.positionFloater();
	        };

	        window.addEventListener('resize', this.positionEventListener);
	      }

	      this.positionFloater();
	    },
	    positionFloater: function positionFloater() {
	      var _this5 = this;

	      var inputRect = this.$el.getBoundingClientRect();
	      var verticalClass = 'vdpPositionTop';
	      var horizontalClass = 'vdpPositionLeft';

	      var calculate = function calculate() {
	        var rect = _this5.$refs.outerWrap.getBoundingClientRect();

	        var floaterHeight = rect.height;
	        var floaterWidth = rect.width;

	        if (window.innerWidth > _this5.mobileBreakpointWidth) {
	          // vertical
	          if (inputRect.top + inputRect.height + floaterHeight > window.innerHeight && inputRect.top - floaterHeight > 0) {
	            verticalClass = 'vdpPositionBottom';
	          } // horizontal


	          if (inputRect.left + floaterWidth > window.innerWidth) {
	            horizontalClass = 'vdpPositionRight';
	          }

	          _this5.positionClass = ['vdpPositionReady', verticalClass, horizontalClass].join(' ');
	        } else {
	          _this5.positionClass = 'vdpPositionFixed';
	        }
	      };

	      this.$refs.outerWrap ? calculate() : this.$nextTick(calculate);
	    },
	    teardownPosition: function teardownPosition() {
	      if (this.positionEventListener) {
	        this.positionClass = undefined;
	        window.removeEventListener('resize', this.positionEventListener);
	        delete this.positionEventListener;
	      }
	    },
	    clear: function clear() {
	      this.$emit('input', '');
	    },
	    selectDateItem: function selectDateItem(item) {
	      if (!item.disabled) {
	        var newDate = new Date(item.date);

	        if (this.hasCurrentTime) {
	          newDate.setHours(this.currentTime.hours);
	          newDate.setMinutes(this.currentTime.minutes);
	          newDate.setSeconds(this.currentTime.seconds);
	        }

	        this.$emit('input', this.formatDateToString(newDate, this.format));

	        if (this.hasInputElement && !this.pickTime) {
	          this.close();
	        }
	      }
	    },
	    inputTime: function inputTime(method, event) {
	      var currentDate = this.valueDate || new Date();
	      var maxValues = {
	        setHours: 23,
	        setMinutes: 59,
	        setSeconds: 59
	      };
	      var numValue = parseInt(event.target.value, 10) || 0;

	      if (numValue > maxValues[method]) {
	        numValue = maxValues[method];
	      } else if (numValue < 0) {
	        numValue = 0;
	      }

	      event.target.value = paddNum(numValue, method === 'setHours' ? 1 : 2);
	      currentDate[method](numValue);
	      this.$emit('input', this.formatDateToString(currentDate, this.format), true);
	    }
	  },
	  template: "\n    <div class=\"vdpComponent\" v-bind:class=\"{vdpWithInput: hasInputElement}\">\n        <input\n            v-if=\"hasInputElement\"\n            type=\"text\"\n            v-bind=\"inputAttributes\"\n            v-bind:readonly=\"isReadOnly\"\n            v-bind:value=\"inputValue\"\n            v-on:input=\"editable && processUserInput($event.target.value)\"\n            v-on:focus=\"editable && open()\"\n            v-on:click=\"editable && open()\"\n        >\n        <button\n            v-if=\"editable && hasInputElement && inputValue\"\n            class=\"vdpClearInput\"\n            type=\"button\"\n            v-on:click=\"clear\"\n        ></button>\n            <div\n                v-if=\"opened\"\n                class=\"vdpOuterWrap\"\n                ref=\"outerWrap\"\n                v-on:click=\"closeViaOverlay\"\n                v-bind:class=\"[positionClass, {vdpFloating: hasInputElement}]\"\n            >\n                <div class=\"vdpInnerWrap\">\n                    <header class=\"vdpHeader\">\n                        <button\n                            class=\"vdpArrow vdpArrowPrev\"\n                            v-bind:title=\"prevMonthCaption\"\n                            type=\"button\"\n                            v-on:click=\"incrementMonth(-1)\"\n                        >{{ prevMonthCaption }}</button>\n                        <button\n                            class=\"vdpArrow vdpArrowNext\"\n                            type=\"button\"\n                            v-bind:title=\"nextMonthCaption\"\n                            v-on:click=\"incrementMonth(1)\"\n                        >{{ nextMonthCaption }}</button>\n                        <div class=\"vdpPeriodControls\">\n                            <div class=\"vdpPeriodControl\">\n                                <button v-bind:class=\"directionClass\" v-bind:key=\"currentPeriod.month\" type=\"button\">\n                                    {{ months[currentPeriod.month] }}\n                                </button>\n                                <select v-model=\"currentPeriod.month\">\n                                    <option v-for=\"(month, index) in months\" v-bind:value=\"index\" v-bind:key=\"month\">\n                                        {{ month }}\n                                    </option>\n                                </select>\n                            </div>\n                            <div class=\"vdpPeriodControl\">\n                                <button v-bind:class=\"directionClass\" v-bind:key=\"currentPeriod.year\" type=\"button\">\n                                    {{ currentPeriod.year }}\n                                </button>\n                                <select v-model=\"currentPeriod.year\">\n                                    <option v-for=\"year in yearRange\" v-bind:value=\"year\" v-bind:key=\"year\">\n                                        {{ year }}\n                                    </option>\n                                </select>\n                            </div>\n                        </div>\n                    </header>\n                    <table class=\"vdpTable\">\n                        <thead>\n                            <tr>\n                                <th class=\"vdpHeadCell\" v-for=\"weekday in weekdaysSorted\" v-bind:key=\"weekday\">\n                                    <span class=\"vdpHeadCellContent\">{{weekday}}</span>\n                                </th>\n                            </tr>\n                        </thead>\n                        <tbody\n                            v-bind:key=\"currentPeriod.year + '-' + currentPeriod.month\"\n                            v-bind:class=\"directionClass\"\n                        >\n                            <tr class=\"vdpRow\" v-for=\"(week, weekIndex) in currentPeriodDates\" v-bind:key=\"weekIndex\">\n                                <td\n                                    class=\"vdpCell\"\n                                    v-for=\"item in week\"\n                                    v-bind:class=\"{\n                                        selectable: !item.disabled,\n                                        selected: item.selected,\n                                        disabled: item.disabled,\n                                        today: item.today,\n                                        outOfRange: item.outOfRange\n                                    }\"\n                                    v-bind:data-id=\"item.dateKey\"\n                                    v-bind:key=\"item.dateKey\"\n                                    v-on:click=\"selectDateItem(item)\"\n                                >\n                                    <div\n                                        class=\"vdpCellContent\"\n                                    >{{ item.date.getDate() }}</div>\n                                </td>\n                            </tr>\n                        </tbody>\n                    </table>\n                    <div v-if=\"pickTime\" class=\"vdpTimeControls\">\n                        <span class=\"vdpTimeCaption\">{{ setTimeCaption }}</span>\n                        <div class=\"vdpTimeUnit\">\n                            <select class=\"vdpHoursInput\"\n                                v-if=\"pickMinutes\"\n                                v-on:input=\"inputTime('setHours', $event)\"\n                                v-on:change=\"inputTime('setHours', $event)\"\n                                v-bind:value=\"currentTime.hours\"\n                            >\n                                <option\n                                    v-for=\"item in getHourList()\"\n                                    :value=\"item.value\"\n                                >{{ item.name }}</option>\n                            </select>\n                        </div>\n                        <span v-if=\"pickMinutes\" class=\"vdpTimeSeparator\">:</span>\n                        <div v-if=\"pickMinutes\" class=\"vdpTimeUnit\">\n                            <pre><span>{{ currentTime.minutesPadded }}</span><br></pre>\n                            <input\n                                v-if=\"pickMinutes\"\n                                type=\"number\" pattern=\"\\d*\" class=\"vdpMinutesInput\"\n                                v-on:input=\"inputTime('setMinutes', $event)\"\n                                v-bind:value=\"currentTime.minutesPadded\"\n                            >\n                        </div>\n                        <span v-if=\"pickSeconds\" class=\"vdpTimeSeparator\">:</span>\n                        <div v-if=\"pickSeconds\" class=\"vdpTimeUnit\">\n                            <pre><span>{{ currentTime.secondsPadded }}</span><br></pre>\n                            <input\n                                v-if=\"pickSeconds\"\n                                type=\"number\" pattern=\"\\d*\" class=\"vdpSecondsInput\"\n                                v-on:input=\"inputTime('setSeconds', $event)\"\n                                v-bind:value=\"currentTime.secondsPadded\"\n                            >\n                        </div>\n                        <span class=\"vdpTimeCaption\">\n                            <button type=\"button\" @click=\"$emit('close');\">{{ closeButtonCaption }}</button>\n                        </span>\n                    </div>\n                </div>\n            </div>\n    </div>\n    "
	};

	function paddNum(num, padsize) {
	  return typeof num !== 'undefined' ? num.toString().length > padsize ? num : new Array(padsize - num.toString().length + 1).join('0') + num : undefined;
	}

	function chunkArray(inputArray, chunkSize) {
	  var results = [];

	  while (inputArray.length) {
	    results.push(inputArray.splice(0, chunkSize));
	  }

	  return results;
	}

	function areSameDates(date1, date2) {
	  return date1.getDate() === date2.getDate() && date1.getMonth() === date2.getMonth() && date1.getFullYear() === date2.getFullYear();
	}

	ui_vue.Vue.component('bx-date-pick', {
	  props: ["value", "hasTime", "sundayFirstly", "format"],
	  components: {
	    'date-pick': VueDatePick
	  },
	  data: function data() {
	    return {
	      format: null
	    };
	  },
	  template: "\n\t\t<date-pick \n\t\t\t:value=\"value\"\n\t\t\t:show=\"true\"\n\t\t\t:hasInputElement=\"false\"\n\t\t\t:pickTime=\"hasTime\"\n\t\t\t:startWeekOnSunday=\"sundayFirstly\"\n\t\t\t:format=\"format\"\n\t\t\t:weekdays=\"getWeekdays()\"\n\t\t\t:months=\"getMonths()\"\n\t\t\t:setTimeCaption=\"getMessage('TIME') + ':'\"\n\t\t\t:closeButtonCaption=\"getMessage('CLOSE')\"\n\t\t\t:selectableYearRange=\"120\"\n\t\t\t@input=\"setDate\"\n\t\t\t@close=\"close()\"\n\t\t></date-pick>\n\t",
	  methods: {
	    setDate: function setDate(value, stopClose) {
	      this.value = value;

	      if (!stopClose) {
	        this.close();
	      }

	      this.$emit('input', value);
	    },
	    close: function close() {
	      this.$emit('close');
	    },
	    getMessage: function getMessage(code) {
	      return main_core.Loc.getMessage('UI_VUE_COMPONENT_DATEPICK_' + code);
	    },
	    getWeekdays: function getWeekdays() {
	      var list = [];

	      for (var n = 1; n <= 7; n++) {
	        //Loc.getMessage();
	        list.push(this.getMessage('DAY_' + n));
	      }

	      return list;
	    },
	    getMonths: function getMonths() {
	      var list = [];

	      for (var n = 1; n <= 12; n++) {
	        list.push(this.getMessage('MONTH_' + n));
	      }

	      return list;
	    }
	  }
	});

	var DatePick =
	/*#__PURE__*/
	function () {
	  function DatePick() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, DatePick);

	    _vue.set(this, {
	      writable: true,
	      value: void 0
	    });

	    this.node = options.node;
	    this.popupOptions = options.popupOptions || {};
	    this.value = options.value;
	    this.hasTime = !!options.hasTime;
	    this.sundayFirstly = !!options.sundayFirstly;
	    this.format = options.format || (options.hasTime ? main_core.Loc.getMessage('FORMAT_DATETIME') : main_core.Loc.getMessage('FORMAT_DATE'));
	    this.events = options.events || {
	      change: null
	    };
	  }

	  babelHelpers.createClass(DatePick, [{
	    key: "show",
	    value: function show() {
	      if (!this.popup) {
	        this.popup = new main_popup.PopupWindow(Object.assign({
	          autoHide: true,
	          closeByEsc: true,
	          contentPadding: 0,
	          padding: 0,
	          animation: "fading-slide"
	        }, this.popupOptions, {
	          bindElement: this.node,
	          content: this.render()
	        }));
	      }

	      this.popup.show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.popup) {
	        this.popup.close();
	      }
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      if (this.popup) {
	        this.popup.isShown() ? this.hide() : this.show();
	      } else {
	        this.show();
	      }
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      babelHelpers.classPrivateFieldSet(this, _vue, ui_vue.Vue.create({
	        el: document.createElement('div'),
	        data: {
	          picker: this
	        },
	        template: "\n\t\t\t\t<bx-date-pick\n\t\t\t\t\tv-model=\"picker.value\"\n\t\t\t\t\t:hasTime=\"picker.hasTime\"\n\t\t\t\t\t:sundayFirstly=\"picker.sundayFirstly\"\n\t\t\t\t\t:format=\"picker.format\"\n\t\t\t\t\t@close=\"picker.hide()\"\n\t\t\t\t\t@input=\"onChange()\"\n\t\t\t\t>\n\t\t\t\t</bx-date-pick>\n\t\t\t",
	        methods: {
	          onChange: function onChange() {
	            this.picker.onChange();
	          }
	        }
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _vue).$el;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      if (this.events.change) {
	        this.events.change(this.value);
	      }
	    }
	  }]);
	  return DatePick;
	}();

	var _vue = new WeakMap();

	exports.DatePick = DatePick;

}((this.BX.UI.Vue.Components = this.BX.UI.Vue.Components || {}),BX,BX.Main,BX));
//# sourceMappingURL=datepick.bundle.js.map
