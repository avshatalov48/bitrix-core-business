this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var WorkgroupSliderMenu = /*#__PURE__*/function () {
	  function WorkgroupSliderMenu() {
	    babelHelpers.classCallCheck(this, WorkgroupSliderMenu);
	    this.menuNode = null;
	  }

	  babelHelpers.createClass(WorkgroupSliderMenu, [{
	    key: "init",
	    value: function init(params) {
	      var _this = this;

	      this.menuNode = main_core.Type.isStringFilled(params.menuNodeId) ? document.getElementById(params.menuNodeId) : null;

	      if (main_core.Type.isDomNode(this.menuNode)) {
	        this.menuItems = Array.prototype.slice.call(this.menuNode.querySelectorAll('a'));
	        (this.menuItems || []).forEach(function (item) {
	          main_core.Event.bind(item, 'click', function (event) {
	            _this.processClick(item, event);

	            return event.preventDefault();
	          });
	        });
	      }
	    }
	  }, {
	    key: "processClick",
	    value: function processClick(item) {
	      var url = item.getAttribute('data-url');
	      var action = item.getAttribute('data-action');

	      if (main_core.Type.isStringFilled(url)) {
	        window.location.href = url;
	      } else if (main_core.Type.isStringFilled(action)) {
	        switch (action) {
	          case 'theme':
	            BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
	            break;

	          case 'join':
	            break;

	          default:
	        }
	      }
	    }
	  }]);
	  return WorkgroupSliderMenu;
	}();

	exports.WorkgroupSliderMenu = WorkgroupSliderMenu;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX));
//# sourceMappingURL=script.js.map
