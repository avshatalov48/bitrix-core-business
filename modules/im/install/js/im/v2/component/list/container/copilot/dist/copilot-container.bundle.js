/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_component_list_elementList_copilot,im_v2_const,im_v2_lib_logger,im_v2_provider_service) {
	'use strict';

	// @vue/component
	const CopilotListContainer = {
	  name: 'CopilotListContainer',
	  components: {
	    CopilotList: im_v2_component_list_elementList_copilot.CopilotList
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      isCreatingChat: false
	    };
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Copilot container created');
	  },
	  methods: {
	    async onCreateChatClick() {
	      this.isCreatingChat = true;
	      const newDialogId = await this.getCopilotService().createChat().catch(() => {
	        this.isCreatingChat = false;
	        this.showCreateChatError();
	      });
	      this.isCreatingChat = false;
	      void im_public.Messenger.openCopilot(newDialogId);
	    },
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.copilot.name,
	        entityId: dialogId
	      });
	    },
	    showCreateChatError() {
	      BX.UI.Notification.Center.notify({
	        content: this.loc('IM_LIST_CONTAINER_COPILOT_CREATE_CHAT_ERROR')
	      });
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service.CopilotService();
	      }
	      return this.copilotService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-container-copilot__scope bx-im-list-container-copilot__container">
			<div class="bx-im-list-container-copilot__header_container">
				<div class="bx-im-list-container-copilot__header_title">CoPilot</div>
				<div
					class="bx-im-list-container-copilot__create-chat"
					:class="{'--loading': isCreatingChat}"
					@click="onCreateChatClick"
				>
					<div class="bx-im-list-container-copilot__create-chat_icon"></div>
				</div>
			</div>
			<div class="bx-im-list-container-copilot__elements_container">
				<div class="bx-im-list-container-copilot__elements">
					<CopilotList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`
	};

	exports.CopilotListContainer = CopilotListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.List,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=copilot-container.bundle.js.map
