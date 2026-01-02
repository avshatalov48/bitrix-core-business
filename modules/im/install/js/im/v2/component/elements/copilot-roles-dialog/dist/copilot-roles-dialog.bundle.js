/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ai_rolesDialog) {
	'use strict';

	// @vue/component
	const CopilotRolesDialog = {
	  name: 'CopilotRolesDialog',
	  props: {
	    title: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['selectRole', 'close'],
	  computed: {
	    titleText() {
	      return this.title || this.loc('IM_ELEMENTS_COPILOT_ROLES_DIALOG_DEFAULT_TITLE');
	    }
	  },
	  created() {
	    this.roleDialog = new ai_rolesDialog.RolesDialog({
	      moduleId: 'im',
	      contextId: 'im-copilot-create-chat',
	      title: this.titleText
	    });
	    this.subscribeToEvents();
	  },
	  mounted() {
	    void this.roleDialog.show();
	  },
	  beforeUnmount() {
	    if (!this.roleDialog) {
	      return;
	    }
	    this.roleDialog.hide();
	    this.unsubscribeFromEvents();
	  },
	  methods: {
	    subscribeToEvents() {
	      this.roleDialog.subscribe(ai_rolesDialog.RolesDialogEvents.SELECT_ROLE, this.onSelectRole);
	      this.roleDialog.subscribe(ai_rolesDialog.RolesDialogEvents.HIDE, this.onHide);
	    },
	    unsubscribeFromEvents() {
	      this.roleDialog.unsubscribe(ai_rolesDialog.RolesDialogEvents.SELECT_ROLE, this.onSelectRole);
	      this.roleDialog.unsubscribe(ai_rolesDialog.RolesDialogEvents.HIDE, this.onHide);
	    },
	    onSelectRole(event) {
	      const {
	        role
	      } = event.getData();
	      if (!role) {
	        return;
	      }
	      this.$emit('selectRole', role);
	    },
	    onHide() {
	      this.$emit('close');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: '<template></template>'
	};

	exports.CopilotRolesDialog = CopilotRolesDialog;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.AI));
//# sourceMappingURL=copilot-roles-dialog.bundle.js.map
