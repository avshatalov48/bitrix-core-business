this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var RequestSender =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(RequestSender, _Event$EventEmitter);

	  function RequestSender(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RequestSender);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RequestSender).call(this, options));
	    options = babelHelpers.objectSpread({}, {
	      signedParameters: ""
	    }, options);
	    _this.signedParameters = options.signedParameters;
	    return _this;
	  }

	  babelHelpers.createClass(RequestSender, [{
	    key: "deleteErrorOption",
	    value: function deleteErrorOption(requestData) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runComponentAction("bitrix:socialnetwork.copy.checker", "deleteErrorOption", {
	          mode: "class",
	          signedParameters: _this2.signedParameters,
	          data: requestData
	        }).then(resolve, reject);
	      });
	    }
	  }]);
	  return RequestSender;
	}(main_core.Event.EventEmitter);

	var CopyChecker =
	/*#__PURE__*/
	function () {
	  function CopyChecker(options) {
	    babelHelpers.classCallCheck(this, CopyChecker);
	    options = babelHelpers.objectSpread({}, {
	      signedParameters: "",
	      moduleId: "",
	      errorOption: "",
	      errorAlertContainerId: "",
	      errorAlertCloseButtonId: ""
	    }, options);
	    this.signedParameters = options.signedParameters;
	    this.moduleId = options.moduleId;
	    this.errorOption = options.errorOption;
	    this.errorAlertContainerId = options.errorAlertContainerId;
	    this.errorAlertCloseButtonId = options.errorAlertCloseButtonId;
	    this.init();
	  }

	  babelHelpers.createClass(CopyChecker, [{
	    key: "init",
	    value: function init() {
	      this.requestSender = new RequestSender({
	        signedParameters: this.signedParameters
	      });
	      this.errorAlertContainer = document.getElementById(this.errorAlertContainerId);
	      this.errorAlertCloseButton = document.getElementById(this.errorAlertCloseButtonId);

	      if (this.errorAlertCloseButton) {
	        main_core.Event.bind(this.errorAlertCloseButton, "click", this.onCloseAlert.bind(this));
	      }
	    }
	  }, {
	    key: "onCloseAlert",
	    value: function onCloseAlert() {
	      var _this = this;

	      this.requestSender.deleteErrorOption({
	        "moduleId": this.moduleId,
	        "errorOption": this.errorOption
	      }).then(function (response) {
	        _this.errorAlertContainer.remove();
	      }).catch(function (response) {});
	    }
	  }]);
	  return CopyChecker;
	}();

	exports.CopyChecker = CopyChecker;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX));
//# sourceMappingURL=script.js.map
