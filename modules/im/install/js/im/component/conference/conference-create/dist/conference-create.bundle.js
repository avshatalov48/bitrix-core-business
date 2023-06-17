this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_fonts_opensans,main_core,ui_vue,im_lib_logger,im_lib_clipboard) {
	'use strict';

	ui_vue.BitrixVue.component('bx-im-component-conference-create', {
	  props: ['userId', 'darkTheme', 'broadcastingEnabled'],
	  data: function data() {
	    return {
	      title: '',
	      defaultTitle: '',
	      broadcastMode: false,
	      linkGenerated: false,
	      isCreatingConference: false,
	      conferenceCreated: false,
	      aliasData: null,
	      userSelectorLoaded: false,
	      userSelector: null,
	      selectedUsers: [],
	      selectedPresenters: [],
	      chatId: null,
	      errors: []
	    };
	  },
	  created: function created() {
	    this.checkRequirements();
	    this.selectedUsers.push(this.userId);
	    this.selectedPresenters.push(this.userId);
	    this.generateLink();
	  },
	  mounted: function mounted() {
	    var _this = this;
	    this.initUserSelector().then(function () {
	      _this.userSelector.renderTo(_this.$refs['userSelector']);
	      _this.initPresenterSelector();
	      _this.presenterSelector.renderTo(_this.$refs['presenterSelector']);
	      _this.$nextTick(function () {
	        _this.$refs['titleInput'].focus();
	      });
	    });
	  },
	  computed: {
	    conferenceLink: function conferenceLink() {
	      if (this.linkGenerated) {
	        return this.aliasData['LINK'];
	      }
	      return this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LINK_LOADING'];
	    },
	    defaultTitlePlaceholder: function defaultTitlePlaceholder() {
	      if (this.linkGenerated) {
	        return this.defaultTitle;
	      }
	      return this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PLACEHOLDER_TITLE_2'];
	    },
	    containerClasses: function containerClasses() {
	      var classes = ['bx-conference-quick-create-wrap'];
	      if (this.darkTheme) {
	        classes.push('bx-conference-quick-create-wrap-dark');
	      }
	      return classes;
	    },
	    startButtonClasses: function startButtonClasses() {
	      var classes = ['ui-btn', 'ui-btn-primary'];
	      if (!this.userSelectorLoaded) {
	        classes.push('ui-btn-disabled');
	      }
	      if (this.errors.length > 0) {
	        classes.push('ui-btn-disabled', 'ui-btn-icon-lock');
	      }
	      if (this.isCreatingConference) {
	        classes.push('ui-btn-wait');
	      }
	      return classes;
	    },
	    localize: function localize() {
	      return BX.message;
	    }
	  },
	  methods: {
	    generateLink: function generateLink() {
	      var _this2 = this;
	      main_core.ajax.runAction('im.conference.prepare', {
	        json: {},
	        analyticsLabel: {
	          creationType: 'chat'
	        }
	      }).then(function (response) {
	        _this2.aliasData = response.data['ALIAS_DATA'];
	        _this2.defaultTitle = response.data['DEFAULT_TITLE'];
	        _this2.linkGenerated = true;
	      })["catch"](function (response) {
	        im_lib_logger.Logger.warn('error', response["errors"][0].message);
	      });
	    },
	    copyLink: function copyLink() {
	      if (this.linkGenerated && main_core.Reflection.getClass('BX.UI.Notification.Center')) {
	        im_lib_clipboard.Clipboard.copy(this.aliasData['LINK']);
	        top.BX.UI.Notification.Center.notify({
	          content: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_COPY_CONFIRMATION']
	        });
	      }
	    },
	    saveConference: function saveConference() {
	      var _this3 = this;
	      if (!this.linkGenerated) {
	        return false;
	      }
	      var fieldsToSubmit = {
	        id: 0,
	        title: this.title,
	        password_needed: false,
	        users: this.selectedUsers,
	        broadcast_mode: this.broadcastMode,
	        presenters: this.selectedPresenters
	      };
	      this.clearErrors();
	      this.isCreatingConference = true;
	      main_core.ajax.runAction('im.conference.create', {
	        json: {
	          fields: fieldsToSubmit,
	          aliasData: this.aliasData
	        },
	        analyticsLabel: {
	          creationType: 'chat'
	        }
	      }).then(function (response) {
	        _this3.chatId = response.data['CHAT_ID'];
	        _this3.isCreatingConference = false;
	        _this3.conferenceCreated = true;
	        _this3.copyLink();
	      })["catch"](function (response) {
	        _this3.isCreatingConference = false;
	        _this3.onFailedSubmit(response);
	      });
	    },
	    startConference: function startConference() {
	      this.openChat();
	      if (BXIM) {
	        BXIM.openVideoconf(this.aliasData['ALIAS']);
	      }
	    },
	    cancelCreation: function cancelCreation() {
	      if (BXIM && BXIM.messenger) {
	        BXIM.messenger.extraClose();
	      }
	    },
	    openChat: function openChat() {
	      if (window.top["BXIM"] && this.chatId) {
	        window.top["BXIM"].openMessenger('chat' + this.chatId);
	      }
	    },
	    initUserSelector: function initUserSelector() {
	      var _this4 = this;
	      return main_core.Runtime.loadExtension('ui.entity-selector').then(function (exports) {
	        _this4.TagSelector = exports.TagSelector;
	        _this4.userSelectorLoaded = true;
	        _this4.userSelector = new _this4.TagSelector({
	          id: 'user-tag-selector',
	          dialogOptions: {
	            id: 'user-tag-selector',
	            preselectedItems: [['user', _this4.userId]],
	            undeselectedItems: [['user', _this4.userId]],
	            events: {
	              'Item:onSelect': function ItemOnSelect(event) {
	                _this4.onUserSelect(event);
	              },
	              'Item:onDeselect': function ItemOnDeselect(event) {
	                _this4.onUserDeselect(event);
	              }
	            },
	            entities: [{
	              id: 'user',
	              options: {
	                inviteEmployeeLink: false
	              }
	            }, {
	              id: 'department'
	            }],
	            zIndex: 4000
	          },
	          addButtonCaption: _this4.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS'],
	          addButtonCaptionMore: _this4.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS']
	        });
	      });
	    },
	    initPresenterSelector: function initPresenterSelector() {
	      var _this5 = this;
	      this.presenterSelector = new this.TagSelector({
	        id: 'presenter-tag-selector',
	        dialogOptions: {
	          id: 'presenter-tag-selector',
	          preselectedItems: [['user', this.userId]],
	          events: {
	            'Item:onSelect': function ItemOnSelect(event) {
	              _this5.onPresenterSelect(event);
	            },
	            'Item:onDeselect': function ItemOnDeselect(event) {
	              _this5.onPresenterDeselect(event);
	            }
	          },
	          entities: [{
	            id: 'user',
	            options: {
	              inviteEmployeeLink: false
	            }
	          }, {
	            id: 'department'
	          }],
	          zIndex: 4000
	        },
	        addButtonCaption: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS'],
	        addButtonCaptionMore: this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_ADD_USERS']
	      });
	    },
	    checkRequirements: function checkRequirements() {
	      if (!BX.PULL.isPublishingEnabled()) {
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PUSH_ERROR']);
	      }
	      if (!BX.Call.Util.isCallServerAllowed()) {
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_VOXIMPLANT_ERROR_WITH_LINK']);
	      }
	    },
	    addError: function addError(errorText) {
	      this.errors.push(errorText);
	    },
	    clearErrors: function clearErrors() {
	      this.errors = [];
	    },
	    onUserSelect: function onUserSelect(event) {
	      var index = this.selectedUsers.findIndex(function (userId) {
	        return userId === event.data.item.id;
	      });
	      if (index === -1) {
	        this.selectedUsers.push(event.data.item.id);
	      }
	    },
	    onUserDeselect: function onUserDeselect(event) {
	      var index = this.selectedUsers.findIndex(function (userId) {
	        return userId === event.data.item.id;
	      });
	      if (index > -1) {
	        this.selectedUsers.splice(index, 1);
	      }
	    },
	    onPresenterSelect: function onPresenterSelect(event) {
	      var index = this.selectedPresenters.findIndex(function (userId) {
	        return userId === event.data.item.id;
	      });
	      if (index === -1) {
	        this.selectedPresenters.push(event.data.item.id);
	      }
	    },
	    onPresenterDeselect: function onPresenterDeselect(event) {
	      var index = this.selectedPresenters.findIndex(function (userId) {
	        return userId === event.data.item.id;
	      });
	      if (index > -1) {
	        this.selectedPresenters.splice(index, 1);
	      }
	    },
	    onFailedSubmit: function onFailedSubmit(response) {
	      var errorMessage = response["errors"][0].message;
	      if (response["errors"][0].code === 'NETWORK_ERROR') {
	        errorMessage = this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_NETWORK_ERROR'];
	      }
	      this.addError(errorMessage);
	    }
	  },
	  template: "\n\t\t<div :class=\"containerClasses\">\n\t\t\t<div class=\"bx-conference-quick-create-content\">\n\t\t\t\t<!-- Fields -->\n\t\t\t\t<template v-if=\"!conferenceCreated\">\n\t\t\t\t\t<!-- Title -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-title\">\n\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_HEADER_TITLE'] }}\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Errors -->\n\t\t\t\t\t<template v-if=\"errors.length > 0\">\n\t\t\t\t\t\t<div class=\"ui-alert ui-alert-danger bx-conference-quick-create-error-wrap\">\n\t\t\t\t\t\t\t<span v-for=\"error in errors\" class=\"ui-alert-message\" v-html=\"error\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<!-- Title field -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_TITLE'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\tv-model=\"title\"\n\t\t\t\t\t\t\t:placeholder=\"defaultTitlePlaceholder\"\n\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\tclass=\"bx-conference-quick-create-field-input\"\n\t\t\t\t\t\t\tref=\"titleInput\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- User selector field -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_USERS'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<template v-if=\"userSelectorLoaded\">\n\t\t\t\t\t\t\t<div class=\"bx-conference-quick-create-selector-wrap\" ref=\"userSelector\"></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<input type=\"text\" class=\"bx-conference-quick-create-field-input\" :placeholder=\"localize['BX_IM_COMPONENT_CONFERENCE_CREATE_USERS_LOADING']\" disabled>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Broadcast mode field -->\n\t\t\t\t\t<template v-if=\"broadcastingEnabled\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block-inline\">\n\t\t\t\t\t\t\t<input type=\"checkbox\" id=\"bx-conference-quick-create-field-broadcast-mode\" v-model=\"broadcastMode\">\n\t\t\t\t\t\t\t<label class=\"bx-conference-quick-create-field-label bx-conference-quick-create-broadcast-mode-label\" for=\"bx-conference-quick-create-field-broadcast-mode\">{{ localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BROADCAST_MODE'] }}</label>\n\t\t\t\t\t\t\t<bx-hint :text=\"localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BROADCAST_MODE_HINT']\"/>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<!-- Presenter selector field -->\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\" v-show=\"broadcastMode\">\n\t\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PRESENTERS'] }}\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"bx-conference-quick-create-selector-wrap\" ref=\"presenterSelector\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</template>\n\t\t\t\t<!-- Confirmation -->\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div class=\"bx-conference-quick-create-success-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-success-title\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_SUCCESS'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Link field -->\n\t\t\t\t<div v-if=\"conferenceCreated\" class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_LINK'] }}\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-conference-quick-create-link-wrap\">\n\t\t\t\t\t\t<input type=\"text\" class=\"bx-conference-quick-create-field-input\" :placeholder=\"conferenceLink\" disabled>\n\t\t\t\t\t\t<div @click=\"copyLink\" class=\"bx-conference-quick-create-link-copy\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<!-- Create button -->\n\t\t\t\t<div class=\"bx-conference-quick-create-button-wrap\">\n\t\t\t\t\t<template v-if=\"!conferenceCreated\">\n\t\t\t\t\t\t<button @click=\"saveConference\" class=\"bx-conference-quick-create-button-save\" :class=\"startButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_SAVE'] }}</button>\n\t\t\t\t\t\t<button @click=\"cancelCreation\" class=\"ui-btn ui-btn-link bx-conference-quick-create-button-cancel\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_CANCEL'] }}</button>\n\t\t\t\t\t</template>\n\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t<button @click=\"startConference\" class=\"bx-conference-quick-create-button-start\" :class=\"startButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_START'] }}</button>\n\t\t\t\t\t\t<button @click=\"openChat\" class=\"ui-btn ui-btn-link bx-conference-quick-create-button-cancel\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_OPEN_CHAT'] }}</button>\n\t\t\t\t\t</template>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX,BX,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=conference-create.bundle.js.map
