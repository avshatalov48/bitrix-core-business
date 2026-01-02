/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_elements_popup,im_v2_component_elements_loader,im_v2_application_core,im_v2_const,im_v2_lib_user,im_public,im_v2_model,im_v2_component_elements_avatar,im_v2_component_elements_chatTitle) {
	'use strict';

	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserListService {
	  constructor() {
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  async loadUsers(userIds) {
	    const result = await babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imUserListGet, {
	      ID: userIds
	    }).catch(errorResult => {
	      console.error('UserListService: loadUsers error', errorResult.error());
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(Object.values(result.data()));
	  }
	}

	// @vue/component
	const UserItem = {
	  name: 'UserItem',
	  components: {
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.userId, true);
	    },
	    userDialogId() {
	      return this.userId.toString();
	    }
	  },
	  methods: {
	    onUserClick() {
	      void im_public.Messenger.openChat(this.userDialogId);
	    }
	  },
	  template: `
		<div class="bx-im-user-list-content__user-container" @click="onUserClick">
			<div class="bx-im-user-list-content__avatar-container">
				<ChatAvatar
					:avatarDialogId="userDialogId"
					:contextDialogId="contextDialogId"
					:size="AvatarSize.XS"
				/>
			</div>
			<ChatTitle 
				:dialogId="userDialogId" 
				:showItsYou="false" 
				class="bx-im-user-list-content__chat-title-container" 
			/>
		</div>
	`
	};

	// @vue/component
	const UserListContent = {
	  components: {
	    UserItem,
	    Loader: im_v2_component_elements_loader.Loader
	  },
	  props: {
	    userIds: {
	      type: Array,
	      required: true
	    },
	    adjustPopupFunction: {
	      type: Function,
	      required: true
	    },
	    loading: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      hasError: false,
	      isLoadingUsers: false
	    };
	  },
	  computed: {
	    isLoading() {
	      return this.loading || this.isLoadingUsers;
	    }
	  },
	  watch: {
	    userIds() {
	      this.$nextTick(() => {
	        this.adjustPopupFunction();
	      });
	    }
	  },
	  created() {
	    if (this.needUserRequest()) {
	      this.requestUserData();
	    }
	  },
	  methods: {
	    getUserListService() {
	      if (!this.userListService) {
	        this.userListService = new UserListService();
	      }
	      return this.userListService;
	    },
	    getUser(userId) {
	      return this.$store.getters['users/get'](userId);
	    },
	    needUserRequest() {
	      return this.userIds.some(userId => !this.getUser(userId));
	    },
	    async requestUserData() {
	      this.isLoadingUsers = true;
	      await this.getUserListService().loadUsers(this.userIds).catch(() => {
	        this.hasError = true;
	      });
	      this.isLoadingUsers = false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-user-list-content__container bx-im-user-list-content__scope">
			<template v-if="!isLoading && !hasError">
				<UserItem v-for="userId in userIds" :userId="userId" :contextDialogId="contextDialogId" />
			</template>
			<div v-else-if="isLoading" class="bx-im-user-list-content__loader-container">
				<Loader />
			</div>
			<div v-else-if="hasError">
				{{ loc('IM_ELEMENTS_CHAT_INFO_POPUP_NO_ACCESS') }}
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-user-list-popup';

	// @vue/component
	const UserListPopup = {
	  name: 'UserListPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup,
	    UserListContent
	  },
	  props: {
	    showPopup: {
	      type: Boolean,
	      required: true
	    },
	    id: {
	      type: String,
	      required: false,
	      default: POPUP_ID
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    userIds: {
	      type: Array,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    withAngle: {
	      type: Boolean,
	      required: false,
	      default: true
	    },
	    loading: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    forceTop: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    offsetLeft: {
	      type: Number,
	      required: false,
	      default: 0
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      const config = {
	        bindElement: this.bindElement,
	        targetContainer: document.body,
	        offsetTop: 4,
	        offsetLeft: this.offsetLeft,
	        padding: 0,
	        angle: this.withAngle
	      };
	      if (this.forceTop) {
	        config.bindOptions = {
	          position: 'top'
	        };
	      }
	      return config;
	    }
	  },
	  template: `
		<MessengerPopup
			v-if="showPopup"
			v-slot="{adjustPosition}"
			:config="config"
			@close="$emit('close')"
			:id="id"
		>
			<UserListContent 
				:userIds="userIds"
				:contextDialogId="contextDialogId"
				:loading="loading" 
				:adjustPopupFunction="adjustPosition"
			/>
		</MessengerPopup>
	`
	};

	exports.UserListPopup = UserListPopup;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=user-list-popup.bundle.js.map
