this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var WorkgroupSliderMenu = /*#__PURE__*/function () {
	  function WorkgroupSliderMenu() {
	    babelHelpers.classCallCheck(this, WorkgroupSliderMenu);
	    this.menuNode = null;
	    this.pageBodyStyles = {};
	    this.signedParameters = '';
	  }

	  babelHelpers.createClass(WorkgroupSliderMenu, [{
	    key: "init",
	    value: function init(params) {
	      var _this = this;

	      this.menuNode = main_core.Type.isStringFilled(params.menuNodeId) ? document.getElementById(params.menuNodeId) : null;
	      this.pageBodyStyles = main_core.Type.isPlainObject(params.pageBodyStyles) ? params.pageBodyStyles : {};
	      this.signedParameters = main_core.Type.isStringFilled(params.signedParameters) ? params.signedParameters : '';

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
	          case 'card':
	          case 'edit':
	          case 'copy':
	          case 'delete':
	          case 'leave':
	            this.changePage(action);
	            break;

	          case 'theme':
	            BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
	            break;

	          case 'join':
	            break;

	          default:
	        }
	      }
	    }
	  }, {
	    key: "changePage",
	    value: function changePage(action) {
	      var _this2 = this;

	      var componentName = '';
	      var componentParams = {
	        componentTemplate: ''
	      };

	      switch (action) {
	        case 'card':
	          componentName = 'bitrix:socialnetwork.group';
	          componentParams.componentTemplate = 'card';
	          break;

	        case 'edit':
	          componentName = 'bitrix:socialnetwork.group_create.ex';
	          componentParams.TAB = 'edit';
	          break;

	        case 'copy':
	          componentName = 'bitrix:socialnetwork.group_copy';
	          break;

	        case 'delete':
	          componentName = 'bitrix:socialnetwork.group_delete';
	          break;

	        case 'leave':
	          componentName = 'bitrix:socialnetwork.user_leave_group';
	          break;

	        default:
	      }

	      if (!main_core.Type.isStringFilled(componentName)) {
	        return;
	      }

	      main_core.ajax.runComponentAction(componentName, 'getComponent', {
	        mode: 'ajax',
	        signedParameters: this.signedParameters,
	        data: {
	          params: componentParams
	        }
	      }).then(function (response) {
	        if (!main_core.Type.isPlainObject(response.data) || !main_core.Type.isStringFilled(response.data.html)) {
	          return;
	        } // change location address


	        if (document.getElementById('workarea-content')) {
	          main_core.Runtime.html(document.getElementById('workarea-content'), response.data.html).then(function () {
	            Object.entries(_this2.pageBodyStyles).forEach(function (_ref) {
	              var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                  key = _ref2[0],
	                  style = _ref2[1];

	              document.body.classList.remove(style);
	            });

	            if (main_core.Type.isStringFilled(_this2.pageBodyStyles[action])) {
	              document.body.classList.add(_this2.pageBodyStyles[action]);
	            }
	          });
	        }

	        if (main_core.Type.isPlainObject(response.data.componentResult) && main_core.Type.isStringFilled(response.data.componentResult.PageTitle) && document.getElementById('pagetitle')) {
	          var titleContainer = document.getElementById('pagetitle').querySelector('.ui-side-panel-wrap-title-name');

	          if (titleContainer) {
	            main_core.Runtime.html(titleContainer, response.data.componentResult.PageTitle);
	          }
	        } // change Body class

	      }).catch(function (response) {
	        console.log('failed');
	        console.dir(response); // process error
	      });
	    }
	  }]);
	  return WorkgroupSliderMenu;
	}();

	exports.WorkgroupSliderMenu = WorkgroupSliderMenu;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX));
//# sourceMappingURL=script.js.map
