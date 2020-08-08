this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var SliderLoader =
	/*#__PURE__*/
	function () {
	  function SliderLoader(eventId) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, SliderLoader);
	    this.extensionName = main_core.Type.isString(eventId) && (eventId === 'NEW' || eventId.substr(0, 4) === 'EDIT') ? 'EventEditForm' : 'EventViewForm';
	    this.entryId = main_core.Type.isString(eventId) && eventId.substr(0, 4) === 'EDIT' ? parseInt(eventId.substr(4)) : parseInt(eventId);
	    this.entry = options.entry || null;
	    this.entryDateFrom = main_core.Type.isDate(options.entryDateFrom) ? options.entryDateFrom : null;
	    this.timezoneOffset = options.timezoneOffset;
	    this.type = options.type;
	    this.ownerId = options.ownerId;
	    this.userId = options.userId;
	    this.sliderId = "calendar:slider-" + Math.random();
	  }

	  babelHelpers.createClass(SliderLoader, [{
	    key: "show",
	    value: function show() {
	      BX.SidePanel.Instance.open(this.sliderId, {
	        contentCallback: this.loadExtension.bind(this),
	        label: {
	          text: main_core.Loc.getMessage('CALENDAR_EVENT'),
	          bgColor: "#55D0E0"
	        }
	      });
	    }
	  }, {
	    key: "loadExtension",
	    value: function loadExtension(slider) {
	      var _this = this;

	      return new Promise(function (resolve) {
	        main_core.Runtime.loadExtension('calendar.' + _this.extensionName.toLowerCase()).then(function (exports) {
	          if (exports && exports[_this.extensionName]) {
	            var calendarForm = new exports[_this.extensionName]({
	              entryId: _this.entryId,
	              entry: _this.entry,
	              entryDateFrom: _this.entryDateFrom,
	              timezoneOffset: _this.timezoneOffset,
	              type: _this.type,
	              ownerId: _this.ownerId,
	              userId: _this.userId
	            });

	            if (babelHelpers.typeof(calendarForm.initInSlider)) {
	              calendarForm.initInSlider(slider, resolve);
	            }
	          }
	        });
	      });
	    }
	  }]);
	  return SliderLoader;
	}();

	exports.SliderLoader = SliderLoader;

}((this.BX.Calendar = this.BX.Calendar || {}),BX));
//# sourceMappingURL=sliderloader.bundle.js.map
