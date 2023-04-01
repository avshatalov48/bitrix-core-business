(function (exports,main_core,main_core_events,main_popup,ui_dialogs_messagebox,im_lib_clipboard) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Messenger.PhpComponent');
	var ConferenceList = /*#__PURE__*/function () {
	  function ConferenceList(params) {
	    babelHelpers.classCallCheck(this, ConferenceList);
	    this.pathToAdd = params.pathToAdd;
	    this.pathToEdit = params.pathToEdit;
	    this.pathToList = params.pathToList;
	    this.sliderWidth = params.sliderWidth || 800;
	    this.gridId = params.gridId;
	    this.gridManager = main_core.Reflection.getClass('top.BX.Main.gridManager');
	    this.init();
	  }
	  babelHelpers.createClass(ConferenceList, [{
	    key: "init",
	    value: function init() {
	      this.bindEvents();
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;
	      main_core_events.EventEmitter.subscribe('Grid::updated', function () {
	        _this.bindGridEvents();
	      });
	      this.bindCreateButtonEvents();
	      this.bindGridEvents();
	    }
	  }, {
	    key: "bindCreateButtonEvents",
	    value: function bindCreateButtonEvents() {
	      var _this2 = this;
	      var emptyListCreateButton = document.querySelector('.im-conference-list-empty-button');
	      if (emptyListCreateButton) {
	        main_core.Event.bind(emptyListCreateButton, 'click', function () {
	          _this2.openCreateSlider();
	        });
	      }
	      var panelCreateButton = document.querySelector('.im-conference-list-panel-button-create');
	      main_core.Event.bind(panelCreateButton, 'click', function () {
	        _this2.openCreateSlider();
	      });
	    }
	  }, {
	    key: "bindGridEvents",
	    value: function bindGridEvents() {
	      var _this3 = this;
	      //grid rows
	      this.rows = document.querySelectorAll('.main-grid-row');
	      this.rows.forEach(function (row) {
	        var conferenceId = row.getAttribute('data-conference-id');
	        var chatId = row.getAttribute('data-chat-id');
	        var publicLink = row.getAttribute('data-public-link');
	        var conferenceIsFinished = !!row.getAttribute('data-conference-finished');

	        //more button
	        var moreButton = row.querySelector('.im-conference-list-controls-button-more');
	        main_core.Event.bind(moreButton, 'click', function (event) {
	          event.preventDefault();
	          _this3.openContextMenu({
	            buttonNode: moreButton,
	            conferenceId: conferenceId,
	            chatId: chatId
	          });
	        });

	        //copy link button
	        var copyButton = row.querySelector('.im-conference-list-controls-button-copy');
	        main_core.Event.bind(copyButton, 'click', function (event) {
	          event.preventDefault();
	          _this3.copyLink(publicLink);
	        });

	        //chat name link
	        var chatNameLink = row.querySelector('.im-conference-list-chat-name-link');
	        main_core.Event.bind(chatNameLink, 'click', function (event) {
	          event.preventDefault();
	          _this3.openEditSlider(conferenceId);
	        });
	      });
	    }
	  }, {
	    key: "openCreateSlider",
	    value: function openCreateSlider() {
	      this.openSlider(this.pathToAdd);
	    }
	  }, {
	    key: "openEditSlider",
	    value: function openEditSlider(conferenceId) {
	      var pathToEdit = this.pathToEdit.replace('#id#', conferenceId);
	      this.openSlider(pathToEdit);
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(path) {
	      this.closeContextMenu();
	      if (main_core.Reflection.getClass('BX.SidePanel')) {
	        BX.SidePanel.Instance.open(path, {
	          width: this.sliderWidth,
	          cacheable: false
	        });
	      }
	    }
	  }, {
	    key: "copyLink",
	    value: function copyLink(link) {
	      im_lib_clipboard.Clipboard.copy(link);
	      if (main_core.Reflection.getClass('BX.UI.Notification.Center')) {
	        BX.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('CONFERENCE_LIST_NOTIFICATION_LINK_COPIED')
	        });
	      }
	    }
	  }, {
	    key: "openContextMenu",
	    value: function openContextMenu(_ref) {
	      var _this4 = this;
	      var buttonNode = _ref.buttonNode,
	        conferenceId = _ref.conferenceId,
	        chatId = _ref.chatId;
	      main_core.ajax.runComponentAction('bitrix:im.conference.list', "getAllowedOperations", {
	        mode: 'ajax',
	        data: {
	          conferenceId: conferenceId
	        }
	      }).then(function (_ref2) {
	        var _ref2$data = _ref2.data,
	          canDelete = _ref2$data["delete"],
	          canEdit = _ref2$data.edit;
	        if (main_core.Type.isDomNode(buttonNode)) {
	          var menuItems = [{
	            text: main_core.Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_CHAT'),
	            onclick: function onclick() {
	              _this4.openChat(chatId);
	            }
	          }];
	          if (canEdit) {
	            menuItems.push({
	              text: main_core.Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_EDIT'),
	              onclick: function onclick() {
	                _this4.openEditSlider(conferenceId);
	              }
	            });
	          }
	          if (canDelete) {
	            menuItems.push({
	              text: main_core.Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_DELETE'),
	              className: 'im-conference-list-context-menu-item-delete menu-popup-no-icon',
	              onclick: function onclick() {
	                _this4.deleteAction(conferenceId);
	              }
	            });
	          }
	          _this4.menu = new main_popup.Menu({
	            bindElement: buttonNode,
	            items: menuItems,
	            events: {
	              onPopupClose: function onPopupClose() {
	                this.destroy();
	              }
	            }
	          });
	          _this4.menu.show();
	        }
	      })["catch"](function (response) {
	        console.error(response);
	      });
	    }
	  }, {
	    key: "closeContextMenu",
	    value: function closeContextMenu() {
	      if (this.menu) {
	        this.menu.close();
	      }
	    }
	  }, {
	    key: "openChat",
	    value: function openChat(chatId) {
	      this.closeContextMenu();
	      if (main_core.Reflection.getClass('BXIM.openMessenger')) {
	        BXIM.openMessenger('chat' + chatId);
	      }
	    }
	  }, {
	    key: "deleteAction",
	    value: function deleteAction(conferenceId) {
	      var _this5 = this;
	      this.closeContextMenu();
	      main_core.ajax.runComponentAction('bitrix:im.conference.list', "deleteConference", {
	        mode: 'ajax',
	        data: {
	          conferenceId: conferenceId
	        }
	      }).then(function (response) {
	        _this5.onSuccessfulDelete(response);
	      })["catch"](function (response) {
	        _this5.onFailedDelete(response);
	      });
	    }
	  }, {
	    key: "onSuccessfulDelete",
	    value: function onSuccessfulDelete(response) {
	      if (response.data['LAST_ROW'] === true) {
	        top.window.location = this.pathToList;
	        return true;
	      }
	      if (this.gridManager) {
	        this.gridManager.reload(this.gridId);
	      }
	    }
	  }, {
	    key: "onFailedDelete",
	    value: function onFailedDelete(response) {
	      ui_dialogs_messagebox.MessageBox.alert(response["errors"][0].message);
	    }
	  }]);
	  return ConferenceList;
	}();
	namespace.ConferenceList = ConferenceList;

}((this.window = this.window || {}),BX,BX.Event,BX.Main,BX.UI.Dialogs,BX.Messenger.Lib));
//# sourceMappingURL=script.js.map
