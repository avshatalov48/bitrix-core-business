this.BX = this.BX || {};
(function (exports,landing_backend,landing_loc,main_popup,ui_dialogs_messagebox,main_core) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<li style=\"padding-left: ", "px\" class=\"landing-site-selector-item landing-site-selector-item-lower\" data-explorer-depth=\"", "\" data-explorer-folderId=\"", "\" onclick=\"", "\">\n\t\t\t\t<span class=\"ui-icon ui-icon-file-folder\"><i></i></span>\n\t\t\t\t<span class=\"landing-site-selector-item-value\"> \n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</li>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<li class=\"landing-site-selector-item\" data-explorer-depth=\"0\" data-explorer-siteId=\"", "\" onclick=\"", "\">\n\t\t\t\t\t\t\t<span class=\"ui-icon ui-icon-file-folder\"><i></i></span>\n\t\t\t\t\t\t\t<span class=\"landing-site-selector-item-value\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<ul class=\"landing-site-selector-list\">\n\t\t\t\t", "\n\t\t\t</ul>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-explorer-loader\">\n\t\t\t<div class=\"main-ui-loader\">\n\t\t\t\t<svg class=\"main-ui-loader-svg\" viewBox=\"25 25 50 50\">\n\t\t\t\t\t<circle class=\"main-ui-loader-svg-circle\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t</svg>\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ExplorerUI = /*#__PURE__*/function () {
	  function ExplorerUI() {
	    babelHelpers.classCallCheck(this, ExplorerUI);
	  }

	  babelHelpers.createClass(ExplorerUI, null, [{
	    key: "getLoader",
	    value: function getLoader() {
	      return main_core.Tag.render(_templateObject());
	    }
	  }, {
	    key: "getActionButton",
	    value: function getActionButton(title, hadnler) {
	      return new BX.UI.Button({
	        id: 'landing-explorer-action',
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.SUCCESS,
	        text: title,
	        events: {
	          click: hadnler
	        }
	      });
	    }
	  }, {
	    key: "getCancelButton",
	    value: function getCancelButton(hadnler) {
	      return new BX.UI.Button({
	        id: 'landing-explorer-cancel',
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.LINK,
	        text: main_core.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_CANCEL'),
	        events: {
	          click: hadnler
	        }
	      });
	    }
	  }, {
	    key: "getSiteList",
	    value: function getSiteList(data, onClick) {
	      return main_core.Tag.render(_templateObject2(), data.map(function (item) {
	        return main_core.Tag.render(_templateObject3(), item.ID, function () {
	          return onClick(item.ID);
	        }, main_core.Text.encode(item.TITLE));
	      }));
	    }
	  }, {
	    key: "getFolderItem",
	    value: function getFolderItem(item, depth, onClick) {
	      return main_core.Tag.render(_templateObject4(), 30 * depth, depth, item.ID, function () {
	        return onClick(item.ID);
	      }, main_core.Text.encode(item.TITLE));
	    }
	  }]);
	  return ExplorerUI;
	}();

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _loadBreadCrumbs = new WeakSet();

	var _loadSites = new WeakSet();

	var _loadFolders = new WeakSet();

	var _clickSite = new WeakSet();

	var _clickFolder = new WeakSet();

	var _selectSite = new WeakSet();

	var _selectFolder = new WeakSet();

	var _selectItem = new WeakSet();

	var _scrollToSite = new WeakSet();

	var Explorer = /*#__PURE__*/function () {
	  /** @var {Popup} */
	  function Explorer(options) {
	    babelHelpers.classCallCheck(this, Explorer);

	    _scrollToSite.add(this);

	    _selectItem.add(this);

	    _selectFolder.add(this);

	    _selectSite.add(this);

	    _clickFolder.add(this);

	    _clickSite.add(this);

	    _loadFolders.add(this);

	    _loadSites.add(this);

	    _loadBreadCrumbs.add(this);

	    babelHelpers.defineProperty(this, "popupWindow", null);
	    this.type = options.type;
	    this.currentSiteId = options.siteId;
	    this.currentFolderId = options.folderId;

	    if (options.startBreadCrumbs) {
	      this.startBreadCrumbs = options.startBreadCrumbs;
	    }

	    this.popupWindow = this.getPopupWindow();
	  }

	  babelHelpers.createClass(Explorer, [{
	    key: "getPopupWindow",
	    value: function getPopupWindow() {
	      if (this.popupWindow === null) {
	        this.popupWindow = new main_popup.Popup({
	          bindElement: null,
	          className: 'ui-message-box landing-explorer--copy-page',
	          content: null,
	          titleBar: '&nbsp;',
	          overlay: {
	            opacity: 30
	          },
	          closeIcon: false,
	          contentBackground: 'transparent',
	          padding: 0
	        });
	      }

	      return this.popupWindow;
	    }
	  }, {
	    key: "open",
	    value: function open() {
	      this.popupWindow.setContent(ExplorerUI.getLoader());
	      this.popupWindow.show();
	    }
	  }, {
	    key: "errorAlert",
	    value: function errorAlert(errors) {
	      ui_dialogs_messagebox.MessageBox.alert(errors[0].error_description, landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_ALERT_TITLE'));
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(type, title) {
	      this.popupWindow.setTitleBar(landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_TITLE_' + type.toUpperCase()).replace('#title#', title));
	    }
	  }, {
	    key: "setButtons",
	    value: function setButtons(entityId, type) {
	      var _this = this;

	      var typeUpper = type.toUpperCase();
	      var action = null;
	      var data = null;
	      this.popupWindow.setButtons([ExplorerUI.getActionButton(type === 'moveFolder' ? landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_MOVE') : landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_' + typeUpper), function () {
	        switch (type) {
	          case 'copy':
	            action = 'Landing::copy';
	            data = {
	              lid: entityId,
	              toSiteId: _this.currentSiteId,
	              toFolderId: _this.currentFolderId
	            };
	            break;

	          case 'move':
	            action = 'Landing::move';
	            data = {
	              lid: entityId,
	              toSiteId: _this.currentSiteId,
	              toFolderId: _this.currentFolderId
	            };
	            break;

	          case 'moveFolder':
	            action = 'Site::moveFolder';
	            data = {
	              folderId: entityId,
	              toFolderId: _this.currentFolderId
	            };
	            break;
	        }

	        landing_backend.Backend.getInstance().action(action, data, {
	          site_id: _this.currentSiteId,
	          type: _this.type
	        }, _this.popupWindow.setContent(ExplorerUI.getLoader())).then(function () {
	          _this.popupWindow.setContent(ExplorerUI.getLoader());

	          window.location.reload();
	        }).catch(function (reason) {
	          _this.errorAlert(reason.result);

	          return Promise.reject(reason);
	        });
	      }), ExplorerUI.getCancelButton(function () {
	        _this.popupWindow.close();
	      })]);
	    }
	  }, {
	    key: "copy",
	    value: function copy(landing) {
	      this.setTitle('copy', landing.TITLE);
	      this.setButtons(landing.ID, 'copy');
	      this.open();

	      _classPrivateMethodGet(this, _loadSites, _loadSites2).call(this);
	    }
	  }, {
	    key: "move",
	    value: function move(landing) {
	      this.setTitle('move', landing.TITLE);
	      this.setButtons(landing.ID, 'move');
	      this.open();

	      _classPrivateMethodGet(this, _loadSites, _loadSites2).call(this);
	    }
	  }, {
	    key: "moveFolder",
	    value: function moveFolder(folder) {
	      this.setTitle('move', folder.TITLE);
	      this.setButtons(folder.ID, 'moveFolder');
	      this.open();

	      _classPrivateMethodGet(this, _loadSites, _loadSites2).call(this);
	    }
	  }]);
	  return Explorer;
	}();

	var _loadBreadCrumbs2 = function _loadBreadCrumbs2(pos) {
	  var _this2 = this;

	  if (this.startBreadCrumbs[pos]) {
	    _classPrivateMethodGet(this, _loadFolders, _loadFolders2).call(this, this.currentSiteId, this.startBreadCrumbs[pos].PARENT_ID, function () {
	      if (_this2.startBreadCrumbs[pos + 1]) {
	        _classPrivateMethodGet(_this2, _loadBreadCrumbs, _loadBreadCrumbs2).call(_this2, pos + 1);
	      } else {
	        _classPrivateMethodGet(_this2, _clickFolder, _clickFolder2).call(_this2, _this2.startBreadCrumbs[pos].ID);
	      }
	    });
	  }
	};

	var _loadSites2 = function _loadSites2() {
	  var _this3 = this;

	  landing_backend.Backend.getInstance().action('Site::getList', {
	    params: {
	      filter: {
	        '=TYPE': this.type,
	        '=SPECIAL': 'N'
	      },
	      order: {
	        DATE_MODIFY: 'desc'
	      }
	    }
	  }, {
	    type: this.type
	  }).then(function (result) {
	    _this3.popupWindow.setContent(ExplorerUI.getSiteList(result, _classPrivateMethodGet(_this3, _clickSite, _clickSite2).bind(_this3)));

	    _this3.popupWindow.adjustPosition();

	    _classPrivateMethodGet(_this3, _scrollToSite, _scrollToSite2).call(_this3, _this3.currentSiteId);

	    if (_this3.startBreadCrumbs.length > 0) {
	      _classPrivateMethodGet(_this3, _selectSite, _selectSite2).call(_this3, _this3.currentSiteId);

	      _classPrivateMethodGet(_this3, _loadBreadCrumbs, _loadBreadCrumbs2).call(_this3, 0);
	    } else {
	      _classPrivateMethodGet(_this3, _clickSite, _clickSite2).call(_this3, _this3.currentSiteId);
	    }
	  });
	};

	var _loadFolders2 = function _loadFolders2(siteId, parentId, onLoad) {
	  var _this4 = this;

	  landing_backend.Backend.getInstance().action('Site::getFolders', {
	    siteId: siteId,
	    filter: {
	      PARENT_ID: parentId ? parentId : 0
	    }
	  }, {
	    site_id: siteId,
	    type: this.type
	  }).then(function (result) {
	    if (result.length <= 0) {
	      return;
	    }

	    var selectedItem = parentId > 0 ? _classPrivateMethodGet(_this4, _selectFolder, _selectFolder2).call(_this4, parentId) : _classPrivateMethodGet(_this4, _selectSite, _selectSite2).call(_this4, siteId);
	    result.reverse().map(function (item) {
	      var folderExist = document.querySelector('.landing-site-selector-item[data-explorer-folderId="' + item.ID + '"]');

	      if (!folderExist) {
	        var depth = parseInt(main_core.Dom.attr(selectedItem, 'data-explorer-depth')) + 1;
	        main_core.Dom.insertAfter(ExplorerUI.getFolderItem(item, depth, _classPrivateMethodGet(_this4, _clickFolder, _clickFolder2).bind(_this4)), selectedItem);
	      }
	    });

	    if (onLoad) {
	      onLoad();
	    }
	  });
	};

	var _clickSite2 = function _clickSite2(siteId) {
	  this.currentFolderId = 0;

	  _classPrivateMethodGet(this, _selectSite, _selectSite2).call(this, siteId);

	  _classPrivateMethodGet(this, _loadFolders, _loadFolders2).call(this, siteId);
	};

	var _clickFolder2 = function _clickFolder2(folderId) {
	  _classPrivateMethodGet(this, _selectFolder, _selectFolder2).call(this, folderId);

	  _classPrivateMethodGet(this, _loadFolders, _loadFolders2).call(this, this.currentSiteId, folderId);
	};

	var _selectSite2 = function _selectSite2(siteId) {
	  this.currentSiteId = siteId;
	  return _classPrivateMethodGet(this, _selectItem, _selectItem2).call(this, siteId, 'siteId');
	};

	var _selectFolder2 = function _selectFolder2(folderId) {
	  this.currentFolderId = folderId;
	  return _classPrivateMethodGet(this, _selectItem, _selectItem2).call(this, folderId, 'folderId');
	};

	var _selectItem2 = function _selectItem2(itemId, dataType) {
	  var currentSelect = document.querySelector('.landing-site-selector-item-selected');
	  var newSelect = document.querySelector('.landing-site-selector-item[data-explorer-' + dataType + '="' + itemId + '"]');

	  if (currentSelect) {
	    main_core.Dom.removeClass(currentSelect, 'landing-site-selector-item-selected');
	  }

	  if (newSelect) {
	    main_core.Dom.addClass(newSelect, 'landing-site-selector-item-selected');
	  }

	  return newSelect;
	};

	var _scrollToSite2 = function _scrollToSite2(siteId) {
	  var siteNode = document.querySelector('[data-explorer-siteId="' + siteId + '"]');

	  if (siteNode) {
	    // const posY = siteNode.getBoundingClientRect().y;
	    // document.querySelector('.landing-site-selector-list').scrollTo(0, posY);
	    siteNode.scrollIntoView({
	      behavior: 'smooth',
	      block: 'nearest',
	      inline: 'start'
	    });
	  }
	};

	exports.Explorer = Explorer;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX.Landing,BX.Main,BX.UI.Dialogs,BX));
//# sourceMappingURL=explorer.bundle.js.map
