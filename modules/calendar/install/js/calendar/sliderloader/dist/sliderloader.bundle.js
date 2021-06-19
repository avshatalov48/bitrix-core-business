this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var SliderLoader = /*#__PURE__*/function () {
	  function SliderLoader(entryId) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, SliderLoader);
	    this.extensionName = main_core.Type.isString(entryId) && (entryId === 'NEW' || entryId.substr(0, 4) === 'EDIT') || !parseInt(entryId) ? 'EventEditForm' : 'EventViewForm';
	    this.sliderId = options.sliderId || "calendar:slider-" + Math.random();
	    entryId = main_core.Type.isString(entryId) && entryId.substr(0, 4) === 'EDIT' ? parseInt(entryId.substr(4)) : parseInt(entryId);
	    this.extensionParams = {
	      entryId: entryId,
	      entry: options.entry || null,
	      type: options.type || null,
	      ownerId: parseInt(options.ownerId) || null,
	      userId: parseInt(options.userId) || null
	    };

	    if (parseInt(options.organizerId)) {
	      this.extensionParams.organizerId = parseInt(options.organizerId);
	    }

	    if (main_core.Type.isArray(options.participantsEntityList)) {
	      this.extensionParams.participantsEntityList = options.participantsEntityList;
	    }

	    if (options.formDataValue) {
	      this.extensionParams.formDataValue = options.formDataValue;
	    }

	    if (main_core.Type.isDate(options.entryDateFrom)) {
	      this.extensionParams.entryDateFrom = options.entryDateFrom;
	    }

	    if (options.timezoneOffset) {
	      this.extensionParams.timezoneOffset = options.timezoneOffset;
	    }

	    if (main_core.Type.isString(options.entryName)) {
	      this.extensionParams.entryName = options.entryName;
	    }

	    if (main_core.Type.isString(options.entryDescription)) {
	      this.extensionParams.entryDescription = options.entryDescription;
	    }
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
	        var extensionName = 'calendar.' + _this.extensionName.toLowerCase();

	        main_core.Runtime.loadExtension(extensionName).then(function (exports) {
	          if (exports && exports[_this.extensionName]) {
	            var calendarForm = new exports[_this.extensionName](_this.extensionParams);

	            if (babelHelpers.typeof(calendarForm.initInSlider)) {
	              calendarForm.initInSlider(slider, resolve);
	            }
	          } else {
	            console.error("Extension \"calendar.".concat(extensionName, "\" not found"));
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
