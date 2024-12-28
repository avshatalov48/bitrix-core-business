/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_component_list_items_copilot,im_v2_const,im_v2_lib_analytics,im_v2_lib_logger,im_v2_provider_service,im_v2_lib_permission,im_v2_component_elements) {
	'use strict';

	// @vue/component
	const RoleItem = {
	  name: 'RoleItem',
	  props: {
	    role: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      imageLoadError: false
	    };
	  },
	  computed: {
	    roleItem() {
	      return this.role;
	    },
	    roleAvatar() {
	      return this.roleItem.avatar.medium;
	    },
	    roleName() {
	      return this.roleItem.name;
	    },
	    roleSDescription() {
	      return this.roleItem.desc;
	    },
	    defaultRole() {
	      return this.$store.getters['copilot/roles/getDefault'];
	    },
	    defaultRoleAvatarUrl() {
	      return this.defaultRole.avatar.medium;
	    }
	  },
	  methods: {
	    onImageLoadError() {
	      this.imageLoadError = true;
	    }
	  },
	  template: `
		<div class="bx-im-role-item__container">
			<div class="bx-im-role-item__avatar">
				<img v-if="!imageLoadError" :src="roleAvatar" :alt="roleName" @error="onImageLoadError">
				<img v-else :src="defaultRoleAvatarUrl" :alt="roleName">
			</div>
			<div class="bx-im-role-item__info">
				<div class="bx-im-role-item__name" :title="roleName">{{ roleName }}</div>
				<div class="bx-im-role-item__description" :title="roleSDescription">{{ roleSDescription }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const RoleSelectorMiniContent = {
	  name: 'RoleSelectorMiniContent',
	  components: {
	    RoleItem
	  },
	  emits: ['selectedRole', 'openMainSelector'],
	  computed: {
	    rolesToShow() {
	      return this.$store.getters['copilot/getRecommendedRoles']();
	    }
	  },
	  methods: {
	    openMainSelector() {
	      this.$emit('openMainSelector');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onRoleClick(role) {
	      this.$emit('selectedRole', role.code);
	    }
	  },
	  template: `
		<div class="bx-im-role-selector-mini-content__container">
			<span class="bx-im-role-selector-mini-content__title">
				{{ loc('IM_LIST_CONTAINER_COPILOT_ROLES_LIST') }}
			</span>
			<div class="bx-im-role-selector-mini-content__items">
				<RoleItem 
					v-for="role in rolesToShow" 
					:role="role"
					@click="onRoleClick(role)"
				/>
				<div class="bx-im-role-selector-mini-content__main-selector" @click="openMainSelector">
					<div class="bx-im-role-selector-mini-content__main-selector-info">
						<div class="bx-im-role-selector-mini-content__main-selector-avatar"></div>
						<div class="bx-im-role-selector-mini-content__main-selector-name">
							{{ loc('IM_LIST_CONTAINER_COPILOT_SELECT_ROLE_FROM_LIST') }}
						</div>
					</div>
					<div class="bx-im-role-selector-mini-content__main-selector-arrow"></div>
				</div>
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-role-selector-mini-popup';

	// @vue/component
	const RoleSelectorMini = {
	  name: 'RoleSelectorMini',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    RoleSelectorMiniContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close', 'selectedRole', 'openMainSelector'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        width: 294,
	        closeIcon: false,
	        bindElement: this.bindElement,
	        offsetTop: 0,
	        offsetLeft: 0,
	        padding: 0,
	        contentPadding: 0,
	        contentBackground: '#fff'
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<RoleSelectorMiniContent
				@selectedRole="$emit('selectedRole', $event)"
				@openMainSelector="$emit('openMainSelector')"
				@close="$emit('close')"
			/>
		</MessengerPopup>
	`
	};

	// @vue/component
	const CopilotListContainer = {
	  name: 'CopilotListContainer',
	  components: {
	    CopilotList: im_v2_component_list_items_copilot.CopilotList,
	    RoleSelectorMini,
	    CopilotRolesDialog: im_v2_component_elements.CopilotRolesDialog
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      showRoleSelector: false,
	      showRolesDialog: false,
	      isCreatingChat: false
	    };
	  },
	  computed: {
	    canCreate() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createCopilot);
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Copilot container created');
	  },
	  deactivated() {
	    this.showRolesDialog = false;
	    this.showRoleSelector = false;
	  },
	  methods: {
	    async onCreateChatClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onStartClick(im_v2_const.ChatType.copilot);
	      this.showRoleSelector = true;
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
	    async createChat(roleCode) {
	      this.showRoleSelector = false;
	      this.showRolesDialog = false;
	      this.isCreatingChat = true;
	      const newDialogId = await this.getCopilotService().createChat({
	        roleCode
	      }).catch(() => {
	        this.isCreatingChat = false;
	        this.showCreateChatError();
	      });
	      this.isCreatingChat = false;
	      void im_public.Messenger.openCopilot(newDialogId);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onCopilotDialogSelectRole(role) {
	      void this.createChat(role.code);
	    },
	    onOpenMainSelector() {
	      this.showRoleSelector = false;
	      this.showRolesDialog = true;
	    }
	  },
	  template: `
		<div class="bx-im-list-container-copilot__scope bx-im-list-container-copilot__container">
			<div class="bx-im-list-container-copilot__header_container">
				<div class="bx-im-list-container-copilot__header_title">CoPilot</div>
				<div
					v-if="canCreate"
					class="bx-im-list-container-copilot__create-chat"
					:class="{'--loading': isCreatingChat}"
					ref="createChatButton"
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
			<RoleSelectorMini
				v-if="showRoleSelector"
				:bindElement="$refs.createChatButton"
				@close="showRoleSelector = false"
				@selectedRole="createChat"
				@openMainSelector="onOpenMainSelector"
			/>
			<CopilotRolesDialog
				v-if="showRolesDialog"
				@selectRole="onCopilotDialogSelectRole"
				@close="showRolesDialog = false"
			/>
		</div>
	`
	};

	exports.CopilotListContainer = CopilotListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.List,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=copilot-container.bundle.js.map
