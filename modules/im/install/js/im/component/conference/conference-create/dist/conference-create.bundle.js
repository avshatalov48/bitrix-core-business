this.BX = this.BX || {};
(function (exports,main_core,ui_vue,im_lib_logger,im_lib_clipboard) {
	'use strict';

	ui_vue.Vue.component('bx-im-component-conference-create', {
	  props: ['userId', 'darkTheme'],
	  data: function data() {
	    return {
	      title: '',
	      defaultTitle: '',
	      linkGenerated: false,
	      aliasData: null,
	      userSelectorLoaded: false,
	      userSelector: null,
	      selectedUsers: [],
	      chatId: null,
	      errors: []
	    };
	  },
	  created: function created() {
	    this.checkRequirements();
	    this.selectedUsers.push(this.userId);
	    this.generateLink();
	  },
	  mounted: function mounted() {
	    var _this = this;

	    this.initUserSelector().then(function () {
	      _this.userSelector.renderTo(_this.$refs['userSelector']);

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
	      var classes = ['ui-btn', 'ui-btn-primary', 'bx-conference-quick-create-button-start'];

	      if (!this.userSelectorLoaded) {
	        classes.push('ui-btn-disabled');
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
	      }).catch(function (response) {
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
	    startConference: function startConference() {
	      var _this3 = this;

	      if (this.linkGenerated) {
	        var fieldsToSubmit = {};
	        fieldsToSubmit['title'] = this.title;
	        fieldsToSubmit['id'] = 0;
	        fieldsToSubmit['password_needed'] = false;
	        fieldsToSubmit['users'] = this.selectedUsers;
	        this.clearErrors();
	        main_core.ajax.runAction('im.conference.create', {
	          json: {
	            fields: fieldsToSubmit,
	            aliasData: this.aliasData
	          },
	          analyticsLabel: {
	            creationType: 'chat'
	          }
	        }).then(function (response) {
	          _this3.onSuccessfulSubmit(response);
	        }).catch(function (response) {
	          _this3.onFailedSubmit(response);
	        });
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
	        var TagSelector = exports.TagSelector;
	        _this4.userSelectorLoaded = true;
	        _this4.userSelector = new TagSelector({
	          id: 'tag-selector',
	          dialogOptions: {
	            id: 'tag-selector',
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
	    checkRequirements: function checkRequirements() {
	      if (!BX.PULL.isPublishingEnabled()) {
	        this.disableButton();
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_PUSH_ERROR']);
	      }

	      if (!BX.Call.Util.isCallServerAllowed()) {
	        this.disableButton();
	        this.addError(this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_VOXIMPLANT_ERROR']);
	      }
	    },
	    addError: function addError(errorText) {
	      this.errors.push(errorText);
	    },
	    clearErrors: function clearErrors() {
	      this.errors = [];
	    },
	    disableButton: function disableButton() {
	      this.startButtonClasses.push('ui-btn-disabled', 'ui-btn-icon-lock');
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
	    onSuccessfulSubmit: function onSuccessfulSubmit(response) {
	      this.chatId = response.data['CHAT_ID'];
	      this.openChat();

	      if (BXIM) {
	        BXIM.openVideoconf(this.aliasData['ALIAS']);
	      }
	    },
	    onFailedSubmit: function onFailedSubmit(response) {
	      this.addError(response["errors"][0].message);
	    }
	  },
	  template: "\n\t\t\t<div :class=\"containerClasses\">\n\t\t\t\t<div class=\"bx-conference-quick-create-content\">\n\t\t\t\t\t<!-- Title -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-title\">\n\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_HEADER_TITLE'] }}\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Errors -->\n\t\t\t\t\t<template v-if=\"errors.length > 0\">\n\t\t\t\t\t\t<div class=\"ui-alert ui-alert-danger bx-conference-quick-create-error-wrap\">\n\t\t\t\t\t\t\t<span v-for=\"error in errors\" class=\"ui-alert-message\">{{ error }}</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<!-- Title field -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_TITLE'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\tv-model=\"title\"\n\t\t\t\t\t\t\t:placeholder=\"defaultTitlePlaceholder\"\n\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\tclass=\"bx-conference-quick-create-field-input\"\n\t\t\t\t\t\t\tref=\"titleInput\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- User selector field -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_USERS'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<template v-if=\"userSelectorLoaded\">\n\t\t\t\t\t\t\t<div class=\"bx-conference-quick-create-selector-wrap\" ref=\"userSelector\"></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<input type=\"text\" class=\"bx-conference-quick-create-field-input\" :placeholder=\"localize['BX_IM_COMPONENT_CONFERENCE_CREATE_USERS_LOADING']\" disabled>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Link field -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-field-block\">\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-field-label\">\n\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_LABEL_LINK'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-conference-quick-create-link-wrap\">\n\t\t\t\t\t\t\t<input type=\"text\" class=\"bx-conference-quick-create-field-input\" :placeholder=\"conferenceLink\" disabled>\n\t\t\t\t\t\t\t<div @click=\"copyLink\" class=\"bx-conference-quick-create-link-copy\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Create button -->\n\t\t\t\t\t<div class=\"bx-conference-quick-create-button\">\n\t\t\t\t\t\t<button @click=\"startConference\" :class=\"startButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_START'] }}</button>\n\t\t\t\t\t\t<button @click=\"cancelCreation\" class=\"ui-btn ui-btn-link bx-conference-quick-create-button-cancel\">{{ this.localize['BX_IM_COMPONENT_CONFERENCE_CREATE_BUTTON_CANCEL'] }}</button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=conference-create.bundle.js.map
