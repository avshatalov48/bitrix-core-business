this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_core_events,main_popup,main_core) {
	'use strict';

	var Toolbar = /*#__PURE__*/function () {
	  function Toolbar(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Toolbar);
	    this.id = params.id;
	    this.menuItems = params.menuItems;
	    this.componentName = params.componentName;

	    if (main_core.Type.isStringFilled(params.menuButtonId)) {
	      var menuButton = document.getElementById(params.menuButtonId);

	      if (menuButton) {
	        menuButton.addEventListener('click', function (e) {
	          _this.menuButtonClick(e.currentTarget);
	        });
	      }
	    }
	  }

	  babelHelpers.createClass(Toolbar, [{
	    key: "getId",
	    value: function getId() {
	      return this._id;
	    }
	  }, {
	    key: "getSetting",
	    value: function getSetting(name, defaultval) {
	      return this._settings.getParam(name, defaultval);
	    }
	  }, {
	    key: "menuButtonClick",
	    value: function menuButtonClick(bindNode) {
	      this.openMenu(bindNode);
	    }
	  }, {
	    key: "openMenu",
	    value: function openMenu(bindNode) {
	      if (this.menuOpened) {
	        this.closeMenu();
	        return;
	      }

	      if (!main_core.Type.isArray(this.menuItems)) {
	        return;
	      }

	      var menuItems = [];
	      this.menuItems.forEach(function (item) {
	        if (!main_core.Type.isUndefined(item.SEPARATOR) && item.SEPARATOR) {
	          menuItems.push({
	            SEPARATOR: true
	          });
	          return;
	        }

	        if (!main_core.Type.isStringFilled(item.TYPE)) {
	          return;
	        }

	        menuItems.push({
	          text: main_core.Type.isStringFilled(item.TITLE) ? item.TITLE : '',
	          onclick: main_core.Type.isStringFilled(item.LINK) ? "window.location.href = \"".concat(item.LINK, "\"; return false;") : ''
	        });
	      });
	      this.menuId = "".concat(this.id.toLowerCase(), "_menu");
	      main_popup.Popup.show(this.menuId, bindNode, menuItems, {
	        autoHide: true,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: 0,
	        events: {
	          onPopupShow: this.onPopupShow.bind(this),
	          onPopupClose: this.onPopupClose.bind(this),
	          onPopupDestroy: this.onPopupDestroy.bind(this)
	        }
	      });
	      this.menuPopup = main_popup.MenuManager.currentItem;
	    }
	  }, {
	    key: "closeMenu",
	    value: function closeMenu() {
	      if (!this.menuPopup || !this.menuPopup.popupWindow) {
	        return;
	      }

	      this.menuPopup.popupWindow.destroy();
	    }
	  }, {
	    key: "onPopupShow",
	    value: function onPopupShow() {
	      this.menuOpened = true;
	    }
	  }, {
	    key: "onPopupClose",
	    value: function onPopupClose() {
	      this.closeMenu();
	    }
	  }, {
	    key: "onPopupDestroy",
	    value: function onPopupDestroy() {
	      this.menuOpened = false;
	      this.menuPopup = null;

	      if (!main_core.Type.isUndefined(main_popup.MenuManager.Data[this.menuId])) {
	        delete main_popup.MenuManager.Data[this.menuId];
	      }
	    }
	  }]);
	  return Toolbar;
	}();

	var ActionManager = /*#__PURE__*/function () {
	  function ActionManager(params) {
	    babelHelpers.classCallCheck(this, ActionManager);
	    this.componentName = params.componentName;
	    this.signedParameters = params.signedParameters;
	    this.gridId = params.gridId;
	  }

	  babelHelpers.createClass(ActionManager, [{
	    key: "viewProfile",
	    value: function viewProfile(params) {
	      var userId = parseInt(!main_core.Type.isUndefined(params.userId) ? params.userId : 0);
	      var pathToUser = main_core.Type.isStringFilled(params.pathToUser) ? params.pathToUser : '';

	      if (userId <= 0 || !main_core.Type.isStringFilled(pathToUser)) {
	        return;
	      }

	      BX.SidePanel.Instance.open(pathToUser.replace('#ID#', userId).replace('#USER_ID#', userId).replace('#user_id#', userId), {
	        cacheable: false,
	        allowChangeHistory: true,
	        contentClassName: 'bitrix24-profile-slider-content',
	        loader: 'intranet:profile',
	        width: 1100
	      });
	    }
	  }, {
	    key: "act",
	    value: function act(action, userId) {
	      var _this = this;

	      main_core.ajax.runComponentAction(this.componentName, 'act', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          action: action,
	          fields: {
	            userId: userId
	          }
	        }
	      }).then(function (response) {
	        if (response.data.success) {
	          BX.Main.gridManager.reload(_this.gridId);
	        }
	      });
	    }
	  }, {
	    key: "disconnectDepartment",
	    value: function disconnectDepartment(params) {
	      var _this2 = this;

	      var id = parseInt(!main_core.Type.isUndefined(params.id) ? params.id : 0);

	      if (id <= 0) {
	        return;
	      }

	      main_core.ajax.runComponentAction(this.componentName, 'disconnectDepartment', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          fields: {
	            id: id
	          }
	        }
	      }).then(function (response) {
	        if (response.data.success) {
	          BX.Main.gridManager.reload(_this2.gridId);
	        }
	      });
	    }
	  }]);
	  return ActionManager;
	}();

	var Manager = /*#__PURE__*/function () {
	  babelHelpers.createClass(Manager, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return Manager.repo.get(id);
	    }
	  }]);

	  function Manager(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Manager);
	    this.componentName = params.componentName;
	    this.signedParameters = params.signedParameters;
	    this.gridId = params.gridId;
	    this.filterId = main_core.Type.isStringFilled(params.filterId) ? params.filterId : null;
	    this.gridContainer = main_core.Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null;
	    params.toolbar.componentName = this.componentName;
	    this.toolbarInstance = new Toolbar(params.toolbar);
	    this.actionManagerInstance = new ActionManager({
	      componentName: this.componentName,
	      signedParameters: this.signedParameters,
	      gridId: this.gridId
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          sliderEvent = _event$getCompatData2[0];

	      if (sliderEvent.getEventId() === 'sonetGroupEvent' && !main_core.Type.isUndefined(sliderEvent.data) && main_core.Type.isStringFilled(sliderEvent.data.code) && sliderEvent.data.code === 'afterInvite') {
	        BX.Main.gridManager.reload(_this.gridId);
	      }
	    });
	    Manager.repo.set(this.id, this);
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "getActionManager",
	    value: function getActionManager() {
	      return this.actionManagerInstance;
	    }
	  }]);
	  return Manager;
	}();

	babelHelpers.defineProperty(Manager, "repo", new Map());

	exports.Manager = Manager;
	exports.Toolbar = Toolbar;

}((this.BX.Socialnetwork.WorkgroupUserList = this.BX.Socialnetwork.WorkgroupUserList || {}),BX.Event,BX.Main,BX));
//# sourceMappingURL=script.js.map
