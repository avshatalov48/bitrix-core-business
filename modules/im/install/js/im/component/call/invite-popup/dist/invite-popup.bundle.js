this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,main_core,main_popup) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _renderUser = /*#__PURE__*/new WeakSet();
	var InvitePopup = /*#__PURE__*/function () {
	  function InvitePopup(config) {
	    babelHelpers.classCallCheck(this, InvitePopup);
	    _classPrivateMethodInitSpec(this, _renderUser);
	    if (!main_core.Type.isPlainObject(config)) {
	      config = {};
	    }
	    this.idleUsers = config.idleUsers || [];
	    this.recentUsers = [];
	    this.bindElement = config.bindElement;
	    this.viewElement = config.viewElement || document.body;
	    this.allowNewUsers = config.allowNewUsers;
	    this.elements = {
	      root: null,
	      inputBox: null,
	      input: null,
	      destinationContainer: null,
	      contactList: null,
	      moreButton: null
	    };
	    this.popup = null;
	    this.zIndex = config.zIndex || 0;
	    this.darkMode = config.darkMode;
	    this.searchPhrase = '';
	    this.searchNext = 0;
	    this.searchResult = [];
	    this.searchTotalCount = 0;
	    this.searchTimeout = 0;
	    this.fetching = false;
	    this.callbacks = {
	      onSelect: main_core.Type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing,
	      onClose: main_core.Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
	      onDestroy: main_core.Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing
	    };
	  }
	  babelHelpers.createClass(InvitePopup, [{
	    key: "show",
	    value: function show() {
	      if (!this.elements.root) {
	        this.render();
	      }
	      this.createPopup();
	      this.popup.show();
	      if (this.allowNewUsers) {
	        this.showLoader();
	        this.getRecent().then(this.updateContactList.bind(this));
	      } else {
	        this.updateContactList();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.popup) {
	        this.popup.close();
	      }
	      clearTimeout(this.searchTimeout);
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup() {
	      var _this = this;
	      this.popup = new main_popup.Popup({
	        id: 'bx-call-popup-invite',
	        bindElement: this.bindElement,
	        targetContainer: this.viewElement,
	        zIndex: this.zIndex,
	        lightShadow: true,
	        darkMode: this.darkMode,
	        autoHide: true,
	        closeByEsc: true,
	        content: this.elements.root,
	        bindOptions: {
	          position: "top"
	        },
	        angle: {
	          position: "bottom",
	          offset: 49
	        },
	        cacheable: false,
	        buttons: [new BX.PopupWindowButton({
	          text: BX.message("IM_CALL_INVITE_INVITE"),
	          className: "popup-window-button-accept",
	          events: {
	            click: function click() {
	              if (_this.selectedUser) {
	                _this.callbacks.onSelect({
	                  user: _this.selectedUser
	                });
	              }
	            }
	          }
	        }), new BX.PopupWindowButton({
	          text: BX.message("IM_CALL_INVITE_CANCEL"),
	          events: {
	            click: function click() {
	              return _this.popup.close();
	            }
	          }
	        })],
	        events: {
	          onPopupDestroy: function onPopupDestroy() {
	            _this.popup = null;
	            _this.elements.contactList = null;
	            clearTimeout(_this.searchTimeout);
	            _this.callbacks.onDestroy();
	          }
	        }
	      });
	      main_core.Dom.addClass(this.popup.popupContainer, "bx-messenger-mark");
	    }
	  }, {
	    key: "getRecent",
	    value: function getRecent() {
	      var _this2 = this;
	      return new Promise(function (resolve) {
	        BX.rest.callMethod("im.recent.get", {
	          "SKIP_OPENLINES": "Y",
	          "SKIP_CHAT": "Y"
	        }).then(function (response) {
	          var answer = response.answer;
	          _this2.recentUsers = Object.values(answer.result).map(function (r) {
	            return r.user;
	          }).filter(function (user) {
	            return !user.bot && !user.network;
	          });
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "search",
	    value: function search() {
	      var _this3 = this;
	      return new Promise(function (resolve) {
	        _this3.searchResult = _this3.recentUsers.filter(function (element) {
	          return element.name.toString().toLowerCase().includes(_this3.searchPhrase.toLowerCase());
	        });
	        _this3.searchTotalCount = _this3.searchResult.length;
	        _this3.searchNext = 0;
	        if (_this3.searchPhrase.length < 3) {
	          resolve();
	          return;
	        }
	        BX.rest.callMethod("im.search.user.list", {
	          "FIND": _this3.searchPhrase
	        }).then(function (response) {
	          var answer = response.answer;
	          _this3.searchTotalCount = answer.total;
	          _this3.searchNext = answer.next;
	          var existsUserId = _this3.searchResult.map(function (element) {
	            return parseInt(element.id);
	          });
	          var result = Object.values(answer.result).filter(function (element) {
	            if (element.bot || element.network) {
	              return false;
	            }
	            return !existsUserId.includes(parseInt(element.id));
	          });
	          _this3.searchResult = _this3.searchResult.concat(result);
	          _this3.searchTotalCount = _this3.searchResult.length;
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "fetchMoreSearchResults",
	    value: function fetchMoreSearchResults() {
	      var _this4 = this;
	      return new Promise(function (resolve) {
	        BX.rest.callMethod("im.search.user.list", {
	          "FIND": _this4.searchPhrase,
	          "OFFSET": _this4.searchNext
	        }).then(function (response) {
	          var answer = response.answer;
	          _this4.searchTotalCount = answer.total;
	          _this4.searchNext = answer.next;
	          var existsUserId = _this4.searchResult.map(function (element) {
	            return parseInt(element.id);
	          });
	          var result = Object.values(answer.result).filter(function (element) {
	            if (element.bot || element.network) {
	              return false;
	            }
	            return !existsUserId.includes(parseInt(element.id));
	          });
	          _this4.searchResult = _this4.searchResult.concat(result);
	          _this4.searchTotalCount = _this4.searchResult.length;
	          resolve(result);
	        });
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-popup-newchat-wrap"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-popup-newchat-caption"
	          },
	          text: BX.message("IM_CALL_INVITE_INVITE_USER")
	        })]
	      });
	      this.elements.inputBox = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-dest bx-messenger-popup-newchat-dest-even"
	        },
	        children: [this.elements.destinationContainer = main_core.Dom.create("span", {
	          props: {
	            className: "bx-messenger-dest-items"
	          }
	        }), this.elements.input = main_core.Dom.create("input", {
	          props: {
	            className: "bx-messenger-input"
	          },
	          attrs: {
	            type: "text",
	            placeholder: this.allowNewUsers ? BX.message('IM_M_SEARCH_PLACEHOLDER') : BX.message('IM_M_CALL_REINVITE_PLACEHOLDER'),
	            value: '',
	            disabled: !this.allowNewUsers
	          },
	          events: {
	            keyup: this._onInputKeyUp.bind(this)
	          }
	        })]
	      });
	      this.elements.root.appendChild(this.elements.inputBox);
	      this.elements.contactList = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-cl bx-messenger-recent-wrap"
	        },
	        children: []
	      });
	      this.elements.root.appendChild(this.elements.contactList);
	    }
	  }, {
	    key: "updateDestination",
	    value: function updateDestination() {
	      if (!this.elements.inputBox) {
	        return;
	      }
	      main_core.Dom.clean(this.elements.destinationContainer);
	      if (this.selectedUser) {
	        this.elements.destinationContainer.appendChild(this.renderDestinationUser(this.selectedUser));
	        this.elements.input.style.display = "none";
	      } else {
	        this.elements.input.style.removeProperty("display");
	        this.elements.input.focus();
	      }
	    }
	  }, {
	    key: "updateContactList",
	    value: function updateContactList() {
	      main_core.Dom.clean(this.elements.contactList);
	      if (this.elements.contactList) {
	        this.elements.contactList.appendChild(this.renderContactList());
	      }
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      main_core.Dom.clean(this.elements.contactList);
	      this.elements.contactList.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-cl-item-load"
	        },
	        text: BX.message('IM_CL_LOAD')
	      }));
	    }
	  }, {
	    key: "renderContactList",
	    value: function renderContactList() {
	      var result = document.createDocumentFragment();
	      if (this.idleUsers.length > 0) {
	        result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_CALL_PARTICIPANTS")));
	        for (var i = 0; i < this.idleUsers.length; i++) {
	          result.appendChild(_classPrivateMethodGet(this, _renderUser, _renderUser2).call(this, this.idleUsers[i]));
	        }
	      }
	      if (main_core.Type.isStringFilled(this.searchPhrase)) {
	        if (this.searchResult.length > 0) {
	          result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_SEARCH_RESULTS")));
	          for (var _i = 0; _i < this.searchResult.length; _i++) {
	            result.appendChild(_classPrivateMethodGet(this, _renderUser, _renderUser2).call(this, this.searchResult[_i]));
	          }
	          if (this.searchTotalCount > this.searchResult.length) {
	            this.elements.moreButton = this.renderMoreButton();
	            result.appendChild(this.elements.moreButton);
	          }
	        }
	      } else if (this.recentUsers.length > 0) {
	        result.appendChild(this.renderSeparator(BX.message("IM_CALL_INVITE_RECENT")));
	        for (var _i2 = 0; _i2 < this.recentUsers.length; _i2++) {
	          result.appendChild(_classPrivateMethodGet(this, _renderUser, _renderUser2).call(this, this.recentUsers[_i2]));
	        }
	      }
	      return result;
	    }
	    /**
	     * @param {string} text
	     * @return {Element}
	     */
	  }, {
	    key: "renderSeparator",
	    value: function renderSeparator(text) {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-chatlist-group"
	        },
	        children: [main_core.Dom.create("span", {
	          props: {
	            className: "bx-messenger-chatlist-group-title"
	          },
	          text: text
	        })]
	      });
	    }
	  }, {
	    key: "renderDestinationUser",
	    value: function renderDestinationUser(userData) {
	      return main_core.Dom.create("span", {
	        props: {
	          className: "bx-messenger-dest-block"
	        },
	        children: [main_core.Dom.create("span", {
	          props: {
	            className: "bx-messenger-dest-text"
	          },
	          text: main_core.Text.decode(userData.name)
	        }), main_core.Dom.create("span", {
	          props: {
	            className: "bx-messenger-dest-del"
	          },
	          events: {
	            click: this.removeSelectedUser.bind(this)
	          }
	        })]
	      });
	    }
	  }, {
	    key: "renderMoreButton",
	    value: function renderMoreButton() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-chatlist-more-wrap"
	        },
	        events: {
	          click: this._onMoreButtonClick.bind(this)
	        },
	        children: [main_core.Dom.create("span", {
	          props: {
	            className: "bx-messenger-chatlist-more"
	          },
	          text: BX.message("IM_CALL_INVITE_MORE") + " " + (this.searchTotalCount - this.searchResult.length)
	        })]
	      });
	    }
	  }, {
	    key: "setSelectedUser",
	    value: function setSelectedUser(userData) {
	      this.selectedUser = userData;
	      this.updateDestination();
	    }
	  }, {
	    key: "removeSelectedUser",
	    value: function removeSelectedUser() {
	      this.selectedUser = null;
	      this.updateDestination();
	    }
	  }, {
	    key: "escapeUserData",
	    value: function escapeUserData(userData) {
	      return _objectSpread(_objectSpread({}, userData), {}, {
	        name: main_core.Text.encode(userData.name),
	        first_name: main_core.Text.encode(userData.first_name),
	        last_name: main_core.Text.encode(userData.last_name),
	        work_position: main_core.Text.encode(userData.work_position),
	        external_auth_id: main_core.Text.encode(userData.external_auth_id),
	        status: main_core.Text.encode(userData.status)
	      });
	    }
	  }, {
	    key: "_onMoreButtonClick",
	    value: function _onMoreButtonClick() {
	      var _this5 = this;
	      if (this.fetching) {
	        return;
	      }
	      this.fetching = true;
	      this.fetchMoreSearchResults().then(function (moreUsers) {
	        var df = document.createDocumentFragment();
	        var newMoreButton = null;
	        for (var i = 0; i < moreUsers.length; i++) {
	          df.appendChild(_classPrivateMethodGet(_this5, _renderUser, _renderUser2).call(_this5, moreUsers[i]));
	        }
	        if (_this5.searchTotalCount > _this5.searchResult.length) {
	          newMoreButton = _this5.renderMoreButton();
	          df.appendChild(newMoreButton);
	        }
	        BX.replace(_this5.elements.moreButton, df);
	        _this5.elements.moreButton = newMoreButton;
	        _this5.fetching = false;
	      });
	    }
	  }, {
	    key: "_onInputKeyUp",
	    value: function _onInputKeyUp(event) {
	      var _this6 = this;
	      if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91) {
	        return false;
	      }
	      if (event.keyCode == 27 && this.elements.input.value !== '') {
	        this.elements.input.value = '';
	        event.stopPropagation();
	      }
	      if (this.searchTimeout) {
	        clearTimeout(this.searchTimeout);
	      }
	      this.searchTimeout = setTimeout(function () {
	        _this6.searchPhrase = _this6.elements.input.value;
	        if (!main_core.Type.isStringFilled(_this6.searchPhrase)) {
	          _this6.updateContactList();
	        } else {
	          _this6.search().then(function () {
	            return _this6.updateContactList();
	          });
	        }
	      }, 300);
	    }
	  }]);
	  return InvitePopup;
	}();
	function _renderUser2(userData) {
	  var _this7 = this;
	  var element = BX.MessengerCommon.drawContactListElement({
	    'id': userData.id,
	    'data': this.escapeUserData(userData),
	    'showUserLastActivityDate': true,
	    'showLastMessage': false,
	    'showCounter': false
	  });
	  BX.bind(element, 'click', function () {
	    return _this7.setSelectedUser(userData);
	  });
	  return element;
	}

	exports.InvitePopup = InvitePopup;

}((this.BX.Messenger.Call = this.BX.Messenger.Call || {}),BX,BX.Main));
//# sourceMappingURL=invite-popup.bundle.js.map
