(function (exports,main_core,ui_vue) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Messenger.PhpComponent');
	var ConferenceEdit = /*#__PURE__*/function () {
	  function ConferenceEdit(params) {
	    babelHelpers.classCallCheck(this, ConferenceEdit);
	    babelHelpers.defineProperty(this, "gridId", 'CONFERENCE_LIST_GRID');
	    this.id = params.id || 0;
	    this.pathToList = params.pathToList;
	    this.fieldsData = params.fieldsData;
	    this.mode = params.mode;
	    this.chatHost = params.chatHost;
	    if (main_core.Type.isPlainObject(params.chatUsers)) {
	      params.chatUsers = Object.values(params.chatUsers);
	    }
	    this.chatUsers = params.chatUsers;
	    this.presenters = params.presenters;
	    this.publicLink = params.publicLink;
	    this.chatId = params.chatId;
	    this.invitation = params.invitation;
	    this.broadcastingEnabled = params.broadcastingEnabled || false;
	    this.formContainer = document.getElementById("im-conference-create-fields");
	    this.init();
	  }
	  babelHelpers.createClass(ConferenceEdit, [{
	    key: "init",
	    value: function init() {
	      this.initComponent();
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this = this;
	      ui_vue.Vue.create({
	        el: this.formContainer,
	        data: function data() {
	          return {
	            conferenceId: _this.id,
	            fieldsData: _this.fieldsData,
	            mode: _this.mode,
	            chatHost: _this.chatHost,
	            chatUsers: _this.chatUsers,
	            presenters: _this.presenters,
	            publicLink: _this.publicLink,
	            chatId: _this.chatId,
	            invitation: _this.invitation,
	            gridId: _this.gridId,
	            pathToList: _this.pathToList,
	            broadcastingEnabled: _this.broadcastingEnabled
	          };
	        },
	        template: "\n\t\t\t\t<bx-im-component-conference-edit\n\t\t\t\t\t:conferenceId=\"conferenceId\"\n\t\t\t\t\t:fieldsData=\"fieldsData\"\n\t\t\t\t\t:mode=\"mode\"\n\t\t\t\t\t:chatHost=\"chatHost\"\n\t\t\t\t\t:chatUsers=\"chatUsers\"\n\t\t\t\t\t:presenters=\"presenters\"\n\t\t\t\t\t:publicLink=\"publicLink\"\n\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t:invitationText=\"invitation\"\n\t\t\t\t\t:gridId=\"gridId\"\n\t\t\t\t\t:pathToList=\"pathToList\"\n\t\t\t\t\t:broadcastingEnabled=\"broadcastingEnabled\"\n\t\t\t\t/>\n\t\t\t"
	      });
	    }
	  }]);
	  return ConferenceEdit;
	}();
	namespace.ConferenceEdit = ConferenceEdit;

}((this.window = this.window || {}),BX,BX));
//# sourceMappingURL=script.js.map
