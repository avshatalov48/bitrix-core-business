/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_list_items_collab,im_v2_component_elements,im_v2_const,im_v2_lib_analytics,im_v2_lib_feature,im_v2_lib_logger,im_v2_lib_createChat,im_v2_lib_permission) {
	'use strict';

	// @vue/component
	const CollabListContainer = {
	  name: 'CollabListContainer',
	  components: {
	    CollabList: im_v2_component_list_items_collab.CollabList,
	    CreateChatPromo: im_v2_component_elements.CreateChatPromo
	  },
	  emits: ['selectEntity'],
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    canCreate() {
	      const creationAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabCreationAvailable);
	      const hasAccess = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createCollab);
	      return creationAvailable && hasAccess;
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Collab container created');
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.collab.name,
	        entityId: dialogId
	      });
	    },
	    onCreateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onStartClick(im_v2_const.ChatType.collab);
	      this.startCollabCreation();
	    },
	    startCollabCreation() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().startChatCreation(im_v2_const.ChatType.collab);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-container-collab__container">
			<div class="bx-im-list-container-collab__header_container">
				<div class="bx-im-list-container-collab__header_title">
					{{ loc('IM_LIST_CONTAINER_COLLAB_HEADER_TITLE') }}
				</div>
				<div
					v-if="canCreate"
					@click="onCreateClick" 
					class="bx-im-list-container-collab__header_create-collab"
				></div>
			</div>
			<div class="bx-im-list-container-collab__elements_container">
				<div class="bx-im-list-container-collab__elements">
					<CollabList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`
	};

	exports.CollabListContainer = CollabListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Component.List,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=collab-container.bundle.js.map
