/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	  BXMobileApp = window.BXMobileApp;
	var nodeDatetime = function () {
	  var nodeDatetime = function nodeDatetime(node, type, container, formats) {
	    this.type = type;
	    this.node = node;
	    this.container = container;
	    //debugger;
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    BX.bind(this.container, 'click', this.click);
	    BX.bind(this.container.parentNode, 'click', this.click);
	    //this.type = 'datetime'; // 'datetime', 'date', 'time'
	    this.format = {
	      inner: {
	        datetime: 'dd.MM.yyyy H:mm',
	        time: 'H:mm',
	        date: 'dd.MM.yyyy'
	      },
	      bitrix: {
	        datetime: null,
	        time: null,
	        date: null
	      },
	      visible: {
	        datetime: null,
	        time: null,
	        date: null
	      }
	    };
	    this.init(formats);
	  };
	  nodeDatetime.prototype = {
	    click: function click(e) {
	      BX.eventCancelBubble(e);
	      this.show();
	      return BX.PreventDefault(e);
	    },
	    show: function show() {
	      var res = {
	        type: this.type,
	        start_date: this.getStrDate(this.node.value),
	        format: this.format.inner[this.type],
	        callback: this.callback
	      };
	      if (res['start_date'] == '') {
	        delete res['start_date'];
	      }
	      BXMobileApp.UI.DatePicker.setParams(res);
	      BXMobileApp.UI.DatePicker.show();
	    },
	    callback: function callback(data) {
	      var d = this.makeDate(data);
	      this.node.value = BX.date.format(this.format.bitrix[this.type], d);
	      var text = BX.date.format(BX.clone(this.format.visible[this.type]), d);
	      if (!BX.type.isNotEmptyString(text)) {
	        text = this.container.getAttribute('placeholder') || ' ';
	      }
	      this.container.innerHTML = text;
	      if (this.delButton) {
	        this.delButton.style.display = 'inline-block';
	      }
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    },
	    makeDate: function makeDate(str) {
	      //Format: 'day.month.year hour:minute'
	      var d = new Date();
	      if (BX.type.isNotEmptyString(str)) {
	        var dateR = new RegExp('(\\d{2}).(\\d{2}).(\\d{4})'),
	          timeR = new RegExp('(\\d{1,2}):(\\d{1,2})'),
	          m;
	        if (dateR.test(str) && (m = dateR.exec(str)) && m) {
	          d.setDate(m[1]);
	          d.setMonth(m[2] - 1);
	          d.setFullYear(m[3]);
	        }
	        if (timeR.test(str) && (m = timeR.exec(str)) && m) {
	          d.setHours(m[1]);
	          d.setMinutes(m[2]);
	          d.setSeconds(0);
	        }
	      }
	      return d;
	    },
	    getStrDate: function getStrDate(value) {
	      var d = BX.parseDate(value),
	        res = '';
	      if (d !== null) {
	        if (this.type == 'date' || this.type == 'datetime') {
	          res = BX.util.str_pad_left(d.getDate().toString(), 2, '0') + '.' + BX.util.str_pad_left((d.getMonth() + 1).toString(), 2, '0') + '.' + d.getFullYear().toString();
	        }
	        if (this.type == 'datetime') {
	          res += ' ';
	        }
	        if (this.type == 'time' || this.type == 'datetime') {
	          res += BX.util.str_pad_left(d.getHours().toString(), 2, '0') + ':' + d.getMinutes().toString();
	        }
	      }
	      return res;
	    },
	    init: function init(formats) {
	      var DATETIME_FORMAT = BX.date.convertBitrixFormat(main_core.Loc.getMessage('FORMAT_DATETIME')),
	        DATE_FORMAT = BX.date.convertBitrixFormat(main_core.Loc.getMessage('FORMAT_DATE')),
	        TIME_FORMAT;
	      if (DATETIME_FORMAT.substr(0, DATE_FORMAT.length) == DATE_FORMAT) {
	        TIME_FORMAT = BX.util.trim(DATETIME_FORMAT.substr(DATE_FORMAT.length));
	      } else {
	        TIME_FORMAT = BX.date.convertBitrixFormat(DATETIME_FORMAT.indexOf('T') >= 0 ? 'H:MI:SS T' : 'HH:MI:SS');
	      }
	      this.format.bitrix.datetime = DATETIME_FORMAT;
	      this.format.bitrix.date = DATE_FORMAT;
	      this.format.bitrix.time = TIME_FORMAT;
	      formats = formats || {};
	      this.format.visible.datetime = formats['datetime'] || DATETIME_FORMAT.replace(':s', '');
	      this.format.visible.date = formats['date'] || DATE_FORMAT;
	      this.format.visible.time = formats['time'] || TIME_FORMAT.replace(':s', '');
	      this.format.visible.datetime = [['today', 'today, ' + this.format.visible.time], ['tommorow', 'tommorow, ' + this.format.visible.time], ['yesterday', 'yesterday, ' + this.format.visible.time], ['', this.format.visible.datetime]];
	      this.format.visible.date = [['today', 'today'], ['tommorow', 'tommorow'], ['yesterday', 'yesterday'], ['', this.format.visible.date]];
	      this.delButton = BX("".concat(this.node.id, "_del"));
	      if (this.delButton) {
	        BX.bind(this.delButton, 'click', BX.proxy(this.drop, this));
	      }
	    },
	    drop: function drop(e) {
	      if (e) {
	        BX.eventCancelBubble(e);
	        BX.PreventDefault(e);
	      }
	      this.node.value = '';
	      this.container.innerHTML = this.container.getAttribute('placeholder');
	      if (this.delButton) {
	        this.delButton.style.display = 'none';
	      }
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	      return false;
	    }
	  };
	  return nodeDatetime;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.Datetime = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.Datetime.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      var type = node.hasAttribute('data-bx-type') ? node.getAttribute('data-bx-type').toLowerCase() : '';
	      result = new nodeDatetime(node, type, BX("".concat(node.id, "_container")), this.format);
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.Datetime = this.BX.Mobile.Field.Datetime || {}),BX));
//# sourceMappingURL=mobile.js.map
