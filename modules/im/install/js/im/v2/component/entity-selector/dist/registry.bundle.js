/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_provider_service,ui_entitySelector,im_v2_lib_channel,main_core,intranet_invitationInput,im_v2_application_core,im_v2_lib_helpdesk,ui_vue3_directives_hint,im_v2_lib_permission,im_v2_lib_feature,im_v2_lib_rest,im_v2_lib_utils,main_popup,im_v2_component_elements,main_core_events,im_public,im_v2_const,im_v2_component_search_chatSearchInput,im_v2_component_search_chatSearch) {
	'use strict';

	const searchConfig = Object.freeze({
	  chats: false,
	  users: true
	});
	const SEARCH_ENTITY_ID = 'user';
	const DEFAULT_CONTAINER_HEIGHT = 600;

	// @vue/component
	const AddToChatContent = {
	  name: 'AddToChatContent',
	  components: {
	    ChatSearch: im_v2_component_search_chatSearch.ChatSearch,
	    MessengerButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      required: false
	    },
	    height: {
	      type: Number,
	      default: DEFAULT_CONTAINER_HEIGHT
	    }
	  },
	  emits: ['inviteMembers'],
	  data() {
	    return {
	      searchQuery: '',
	      showHistory: true,
	      selectedItems: new Set()
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    searchConfig: () => searchConfig,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isChat() {
	      return this.dialog.type !== im_v2_const.ChatType.user;
	    },
	    isCollab() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    isOpenLines() {
	      return this.dialog.type === im_v2_const.ChatType.lines;
	    },
	    isChannel() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    showHistoryOption() {
	      return !this.isCollab && this.isChat && !this.isChannel && !this.isOpenLines;
	    },
	    containerStyles() {
	      return {
	        height: `${this.height}px`
	      };
	    }
	  },
	  created() {
	    this.membersSelector = this.getTagSelector();
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs['tag-selector']);
	    this.membersSelector.focusTextBox();
	  },
	  activated() {
	    this.membersSelector.hideAddButton();
	    this.membersSelector.showTextBox();
	    this.membersSelector.focusTextBox();
	  },
	  methods: {
	    getTagSelector() {
	      let timeoutId = null;
	      return new ui_entitySelector.TagSelector({
	        maxHeight: 111,
	        showAddButton: false,
	        showTextBox: true,
	        addButtonCaption: this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MSGVER_1'),
	        addButtonCaptionMore: this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MORE'),
	        showCreateButton: false,
	        events: {
	          onBeforeTagAdd: () => {
	            clearTimeout(timeoutId);
	          },
	          onAfterTagAdd: event => {
	            const {
	              tag
	            } = event.getData();
	            this.selectedItems.add(tag.id);
	            this.focusSelector();
	          },
	          onKeyUp: event => {
	            const {
	              event: keyboardEvent
	            } = event.getData();
	            main_core_events.EventEmitter.emit(im_v2_const.EventType.search.keyPressed, {
	              keyboardEvent
	            });
	          },
	          onBeforeTagRemove: () => {
	            clearTimeout(timeoutId);
	          },
	          onAfterTagRemove: event => {
	            const {
	              tag
	            } = event.getData();
	            this.selectedItems.delete(tag.id);
	            this.focusSelector();
	          },
	          onInput: () => {
	            this.searchQuery = this.membersSelector.getTextBoxValue();
	          },
	          onBlur: () => {
	            const inputText = this.membersSelector.getTextBoxValue();
	            if (inputText.length > 0) {
	              return;
	            }
	            timeoutId = setTimeout(() => {
	              this.membersSelector.hideTextBox();
	              this.membersSelector.showAddButton();
	            }, 200);
	          },
	          onContainerClick: () => {
	            this.focusSelector();
	          }
	        }
	      });
	    },
	    focusSelector() {
	      this.membersSelector.hideAddButton();
	      this.membersSelector.showTextBox();
	      this.membersSelector.focusTextBox();
	    },
	    onSelectItem(event) {
	      const {
	        dialogId,
	        nativeEvent
	      } = event;
	      if (this.selectedItems.has(dialogId)) {
	        const tag = {
	          id: dialogId,
	          entityId: SEARCH_ENTITY_ID
	        };
	        this.membersSelector.removeTag(tag);
	      } else {
	        const tag = this.getTagByDialogId(dialogId);
	        this.membersSelector.addTag(tag);
	      }
	      this.membersSelector.clearTextBox();
	      if (!nativeEvent.altKey) {
	        this.searchQuery = '';
	      }
	    },
	    getTagByDialogId(dialogId) {
	      const user = this.$store.getters['users/get'](dialogId, true);
	      const isExtranet = user.type === im_v2_const.UserType.extranet;
	      const entityType = isExtranet ? 'extranet' : 'employee';
	      return {
	        id: dialogId,
	        entityId: SEARCH_ENTITY_ID,
	        entityType,
	        title: user.name,
	        avatar: user.avatar.length > 0 ? user.avatar : null
	      };
	    },
	    onInviteClick() {
	      const members = [...this.selectedItems];
	      this.$emit('inviteMembers', {
	        members,
	        showHistory: this.showHistory
	      });
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<div class="bx-im-entity-selector-add-to-chat__container" :style="containerStyles">
			<div class="bx-im-entity-selector-add-to-chat__input" ref="tag-selector"></div>
			<div v-if="showHistoryOption" class="bx-im-entity-selector-add-to-chat__show-history">
				<input type="checkbox" id="bx-im-entity-selector-add-to-chat-show-history" v-model="showHistory">
				<label for="bx-im-entity-selector-add-to-chat-show-history">
					{{ loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_SHOW_HISTORY')}}
				</label>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__search-result-container">
				<ChatSearch
					:searchMode="true"
					:searchQuery="searchQuery"
					:selectMode="true"
					:searchConfig="searchConfig"
					:selectedItems="[...selectedItems]"
					:showMyNotes="false"
					@clickItem="onSelectItem"
				/>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__buttons">
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.Primary"
					:isRounded="true"
					:isLoading="isLoading"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="selectedItems.size === 0"
					@click="onInviteClick"
				/>
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_CANCEL_BUTTON')"
					@click="$emit('close')"
				/>
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-add-to-chat-popup';

	// @vue/component
	const AddToChat = {
	  name: 'AddToChat',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    AddToChatContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    popupConfig: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MEMBERS_TITLE'),
	        closeIcon: true,
	        bindElement: this.bindElement,
	        offsetTop: this.popupConfig.offsetTop,
	        offsetLeft: this.popupConfig.offsetLeft,
	        padding: 0,
	        contentPadding: 0,
	        contentBackground: '#fff',
	        className: 'bx-im-entity-selector-add-to-chat__scope'
	      };
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isChat() {
	      return this.dialog.type !== im_v2_const.ChatType.user;
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  created() {
	    this.chatService = new im_v2_provider_service.ChatService();
	  },
	  methods: {
	    inviteMembers(event) {
	      const {
	        members,
	        showHistory
	      } = event;
	      if (this.isChat) {
	        this.extendChat(members, showHistory);
	      } else {
	        members.push(this.dialogId, im_v2_application_core.Core.getUserId());
	        void this.createChat(members);
	      }
	    },
	    extendChat(members, showHistory) {
	      this.isLoading = true;
	      this.chatService.addToChat({
	        chatId: this.chatId,
	        members,
	        showHistory
	      }).then(() => {
	        this.isLoading = false;
	        this.$emit('close');
	      }).catch(error => {
	        console.error(error);
	        this.isLoading = false;
	        this.$emit('close');
	      });
	    },
	    async createChat(members) {
	      this.isLoading = true;
	      const {
	        newDialogId
	      } = await this.chatService.createChat({
	        title: null,
	        description: null,
	        members,
	        ownerId: im_v2_application_core.Core.getUserId(),
	        isPrivate: true
	      }).catch(error => {
	        console.error(error);
	        this.isLoading = false;
	      });
	      this.isLoading = false;
	      this.$emit('close');
	      void im_public.Messenger.openChat(newDialogId);
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<AddToChatContent 
				:dialogId="dialogId" 
				:isLoading="isLoading"
				@close="$emit('close')"
				@inviteMembers="inviteMembers"
			/>
		</MessengerPopup>
	`
	};

	class CollabInvitationService {
	  addEmployees({
	    dialogId,
	    members
	  }) {
	    const payload = {
	      data: {
	        dialogId,
	        members: im_v2_lib_utils.Utils.user.prepareSelectorIds(members)
	      }
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkMemberAdd, payload).catch(error => {
	      console.error('CollabInvitationService: add employee error', error);
	    });
	  }
	  copyLink(collabId) {
	    const payload = {
	      data: {
	        collabId
	      }
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.intranetInviteGetLinkByCollabId, payload).catch(errors => {
	      console.error('CollabInvitationService: getting invite link error', errors);
	      throw errors;
	    });
	  }
	  updateLink(collabId) {
	    const payload = {
	      data: {
	        collabId
	      }
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.intranetInviteRegenerateLinkByCollabId, payload).catch(errors => {
	      console.error('CollabInvitationService: updating invite link error', errors);
	      throw errors;
	    });
	  }
	}

	// @vue/component
	const CopyInviteLink = {
	  name: 'CopyInviteLink',
	  components: {
	    MessengerButton: im_v2_component_elements.Button,
	    ChatHint: im_v2_component_elements.ChatHint
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    collabId: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isCopyingInviteLink: false,
	      isUpdatingLink: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    isInviteLinkAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.inviteByLinkAvailable);
	    },
	    updateLinkHint() {
	      return {
	        text: this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATE_HINT'),
	        popupOptions: {
	          width: 278,
	          bindOptions: {
	            position: 'top'
	          },
	          angle: {
	            offset: 36,
	            position: 'top'
	          },
	          targetContainer: document.body,
	          offsetTop: -8
	        }
	      };
	    },
	    canUpdateLink() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.updateInviteLink, this.dialogId);
	    }
	  },
	  methods: {
	    async copyInviteLink() {
	      try {
	        this.isCopyingInviteLink = true;
	        const link = await new CollabInvitationService().copyLink(this.collabId);
	        await im_v2_lib_utils.Utils.text.copyToClipboard(link);
	        this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_COPIED'));
	      } catch {
	        this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_NOT_COPIED'));
	      } finally {
	        this.isCopyingInviteLink = false;
	      }
	    },
	    async updateLink() {
	      try {
	        this.isUpdatingLink = true;
	        await new CollabInvitationService().updateLink(this.collabId);
	        this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATED'));
	      } catch {
	        this.showNotification(this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LINK_UPDATED_ERROR'));
	      } finally {
	        this.isUpdatingLink = false;
	      }
	    },
	    showNotification(content) {
	      BX.UI.Notification.Center.notify({
	        content
	      });
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div v-if="isInviteLinkAvailable" class="bx-im-add-to-collab__invite-block --link">
			<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_LINK') }}
			</span>
			<MessengerButton
				:size="ButtonSize.M"
				:color="ButtonColor.Primary"
				:isRounded="true"
				:isUppercase="false"
				:isLoading="isCopyingInviteLink"
				:isDisabled="isUpdatingLink"
				:text="loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_COPY_LINK')"
				@click="copyInviteLink"
			/>
			<button
				v-if="canUpdateLink"
				v-hint="updateLinkHint"
				:class="{'--loading': isUpdatingLink}"
				class="bx-im-add-to-collab__update-link_button"
				@click="updateLink"
			>
				<span class="bx-im-add-to-collab__update-link_icon"></span>
			</button>
		</div>
	`
	};

	const HELPDESK_SLIDER_CLOSE_EVENT = 'SidePanel.Slider:onClose';
	const HELPDESK_SLIDER_ID = 'main:helper';

	// @vue/component
	const AddGuestsTab = {
	  name: 'AddGuestsTab',
	  components: {
	    MessengerButton: im_v2_component_elements.Button,
	    ScrollWithGradient: im_v2_component_elements.ScrollWithGradient,
	    CopyInviteLink
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    height: {
	      type: Number,
	      default: 0
	    }
	  },
	  emits: ['close', 'closeHelpdeskSlider', 'openHelpdeskSlider'],
	  data() {
	    return {
	      isAddButtonDisabled: true,
	      isInvitingGuests: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    isCurrentUserCollaber() {
	      const currentUser = this.$store.getters['users/get'](im_v2_application_core.Core.getUserId(), true);
	      return currentUser.type === im_v2_const.UserType.collaber;
	    },
	    preparedDescription() {
	      if (this.isCurrentUserCollaber) {
	        return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TEXT_GUEST');
	      }
	      return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TEXT_EMPLOYEE');
	    },
	    preparedDescriptionTitle() {
	      if (this.isCurrentUserCollaber) {
	        return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TITLE_GUEST');
	      }
	      return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DESCRIPTION_TITLE_EMPLOYEE');
	    },
	    chatId() {
	      const chat = this.$store.getters['chats/get'](this.dialogId, true);
	      return chat.chatId;
	    },
	    collabId() {
	      const collab = this.$store.getters['chats/collabs/getByChatId'](this.chatId);
	      return collab.collabId;
	    },
	    containerStyles() {
	      return {
	        height: `${this.height}px`
	      };
	    },
	    isPhoneInviteAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.inviteByPhoneAvailable);
	    },
	    preparedInvitationTitle() {
	      if (this.isPhoneInviteAvailable) {
	        return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_PHONE_OR_EMAIL');
	      }
	      return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_EMAIL');
	    },
	    isCollaber() {
	      const currentUser = this.$store.getters['users/get'](im_v2_application_core.Core.getUserId());
	      return currentUser.type === im_v2_const.UserType.collaber;
	    }
	  },
	  created() {
	    this.initInvitationInput();
	    main_core_events.EventEmitter.subscribe(HELPDESK_SLIDER_CLOSE_EVENT, this.onCloseOpenHelpdeskSlider);
	  },
	  mounted() {
	    this.invitationGuests.renderTo(this.$refs['im-collab-invitation-guests-input']);
	  },
	  beforeUnmount() {
	    this.invitationGuests.unsubscribe('readyState', this.onReadySaveInputHandler);
	    this.invitationGuests.unsubscribe('onUnreadySave', this.onUnreadySaveInputHandler);
	    main_core_events.EventEmitter.unsubscribe(HELPDESK_SLIDER_CLOSE_EVENT, this.onCloseOpenHelpdeskSlider);
	  },
	  methods: {
	    openHelpdesk() {
	      this.$emit('openHelpdeskSlider');
	      const ARTICLE_CODE = '22706836';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    },
	    initInvitationInput() {
	      this.invitationGuests = new intranet_invitationInput.InvitationInput();
	      this.invitationGuests.subscribe('onReadySave', this.onReadySaveInputHandler);
	      this.invitationGuests.subscribe('onUnreadySave', this.onUnreadySaveInputHandler);
	    },
	    onReadySaveInputHandler() {
	      this.isAddButtonDisabled = false;
	    },
	    onUnreadySaveInputHandler() {
	      this.isAddButtonDisabled = true;
	      this.isInvitingGuests = false;
	    },
	    async addGuestToCollab() {
	      this.isInvitingGuests = true;
	      await this.invitationGuests.inviteToGroup(this.collabId);
	      this.isInvitingGuests = false;
	      this.$emit('close');
	    },
	    onCloseOpenHelpdeskSlider({
	      data
	    }) {
	      const [event] = data;
	      const sliderId = event.getSlider().getUrl().toString();
	      if (sliderId === HELPDESK_SLIDER_ID) {
	        this.$emit('closeHelpdeskSlider');
	      }
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-add-to-collab__container" :style="containerStyles">
			<div class="bx-im-add-to-collab__invite-section">
				<ScrollWithGradient :gradientHeight="28" :withShadow="true">
					<div class="bx-im-add-to-collab__content">
						<div class="bx-im-add-to-collab__description">
							<div class="bx-im-add-to-collab__description_icon"></div>
							<div class="bx-im-add-to-collab__description_content">
								<div class="bx-im-add-to-collab__description_title">{{ preparedDescriptionTitle }}</div>
								<div class="bx-im-add-to-collab__description_text">{{ preparedDescription }}</div>
								<a class="bx-im-add-to-collab__helpdesk-link" @click.prevent="openHelpdesk">
									{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_HELPDESK_LINK') }}
								</a>
							</div>
						</div>
						<CopyInviteLink :collabId="collabId" :dialogId="dialogId" />
						<div class="bx-im-add-to-collab__invite-block">
							<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
								{{ preparedInvitationTitle }}
							</span>
							<div 
								ref="im-collab-invitation-guests-input" 
								class="bx-im-add-to-collab__invite-block-input"
							></div>
						</div>
					</div>
				</ScrollWithGradient>
			</div>
			<div class="bx-im-add-to-collab__buttons">
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.Collab"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="isAddButtonDisabled || isInvitingGuests"
					:isLoading="isInvitingGuests"
					@click="addGuestToCollab"
				/>
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_CANCEL_BUTTON')"
					@click="$emit('close')"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const AddEmployeesTab = {
	  name: 'AddEmployeesTab',
	  components: {
	    AddToChatContent
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    height: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['close'],
	  methods: {
	    inviteMembers({
	      members
	    }) {
	      void new CollabInvitationService().addEmployees({
	        dialogId: this.dialogId,
	        members
	      });
	      this.$emit('close');
	    }
	  },
	  template: `
		<div class="bx-im-add-to-collab__employees-tab-container">
			<AddToChatContent
				:dialogId="dialogId"
				:height="height"
				@inviteMembers="inviteMembers"
				@close="$emit('close')"
			/>
		</div>
	`
	};

	const TabId = Object.freeze({
	  guests: 'guests',
	  employees: 'employees'
	});
	const Tabs = [{
	  id: TabId.guests,
	  title: main_core.Loc.getMessage('IM_ENTITY_SELECTOR_GUESTS_TAB')
	}, {
	  id: TabId.employees,
	  title: main_core.Loc.getMessage('IM_ENTITY_SELECTOR_EMPLOYEES_TAB')
	}];
	const POPUP_ID$1 = 'im-add-to-collab-popup';
	const TAB_CONTENT_HEIGHT = 498;

	// @vue/component
	const AddToCollab = {
	  name: 'AddToCollab',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    SegmentButton: im_v2_component_elements.SegmentButton,
	    AddGuestsTab,
	    AddEmployeesTab
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    popupConfig: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      activeTabId: TabId.guests
	    };
	  },
	  computed: {
	    POPUP_ID: () => POPUP_ID$1,
	    Tabs: () => Tabs,
	    config() {
	      return {
	        titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_TITLE'),
	        closeIcon: true,
	        bindElement: this.bindElement,
	        offsetTop: this.popupConfig.offsetTop,
	        offsetLeft: this.popupConfig.offsetLeft,
	        padding: 0,
	        contentPadding: 0,
	        contentBackground: '#fff',
	        className: 'bx-im-add-to-collab__scope'
	      };
	    },
	    tabComponent() {
	      return this.activeTabId === TabId.guests ? AddGuestsTab : AddEmployeesTab;
	    },
	    isCollaber() {
	      const currentUser = this.$store.getters['users/get'](im_v2_application_core.Core.getUserId());
	      return currentUser.type === im_v2_const.UserType.collaber;
	    },
	    isInviteLinkAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.inviteByLinkAvailable);
	    },
	    finalHeight() {
	      const inviteLinkBlockHeight = 58 + 12;
	      const tabsBlockHeight = 38;
	      let finalHeight = TAB_CONTENT_HEIGHT;
	      if (this.isCollaber) {
	        finalHeight -= tabsBlockHeight;
	      }
	      if (!this.isInviteLinkAvailable) {
	        finalHeight -= inviteLinkBlockHeight;
	      }
	      return finalHeight;
	    }
	  },
	  methods: {
	    onTabSwitch(tabId) {
	      this.activeTabId = tabId;
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			v-slot="{ enableAutoHide, disableAutoHide }"
			@close="$emit('close')"
		>
			<div class="bx-im-add-to-collab__tabs">
				<SegmentButton :tabs="Tabs" @segmentSelected="onTabSwitch" />
			</div>
			<KeepAlive>
				<component
					:is="tabComponent"
					:dialogId="dialogId" 
					:height="finalHeight"
					@close="$emit('close')"
					@openHelpdeskSlider="disableAutoHide"
					@closeHelpdeskSlider="enableAutoHide"
				/>
			</KeepAlive>
		</MessengerPopup>
	`
	};

	const searchConfig$1 = Object.freeze({
	  chats: true,
	  users: true
	});

	// @vue/component
	const ForwardContent = {
	  name: 'ForwardContent',
	  components: {
	    ChatSearch: im_v2_component_search_chatSearch.ChatSearch,
	    ChatSearchInput: im_v2_component_search_chatSearchInput.ChatSearchInput
	  },
	  props: {
	    messagesIds: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      searchQuery: '',
	      isLoading: false
	    };
	  },
	  computed: {
	    searchConfig: () => searchConfig$1
	  },
	  methods: {
	    onLoading(value) {
	      this.isLoading = value;
	    },
	    onUpdateSearch(query) {
	      this.searchQuery = query;
	    },
	    async onSelectItem(event) {
	      const {
	        dialogId
	      } = event;
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeBulkActionsMode);
	      await im_public.Messenger.openChat(dialogId);
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertForward, {
	        messagesIds: this.messagesIds,
	        dialogId
	      });
	      this.$emit('close');
	    }
	  },
	  template: `
		<div class="bx-im-entity-selector-forward__container">
			<div class="bx-im-entity-selector-forward__input">
				<ChatSearchInput 
					:searchMode="true" 
					:isLoading="isLoading" 
					:withIcon="false" 
					:delayForFocusOnStart="1"
					@updateSearch="onUpdateSearch"
				/>
			</div>
			<div class="bx-im-entity-selector-forward__search-result-container">
				<ChatSearch
					:searchMode="true"
					:searchQuery="searchQuery"
					:searchConfig="searchConfig"
					@clickItem="onSelectItem"
					@loading="onLoading"
				/>
			</div>
		</div>
	`
	};

	const POPUP_ID$2 = 'im-forward-popup';

	// @vue/component
	const ForwardPopup = {
	  name: 'ForwardPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    ForwardContent
	  },
	  props: {
	    messagesIds: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID$2,
	    config() {
	      return {
	        titleBar: this.popupTitle,
	        closeIcon: true,
	        targetContainer: document.body,
	        fixed: true,
	        draggable: true,
	        padding: 0,
	        autoHide: false,
	        contentPadding: 0,
	        contentBackground: '#fff',
	        className: 'bx-im-entity-selector-forward__scope'
	      };
	    },
	    popupTitle() {
	      return this.messagesIds.length > 1 ? this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_FORWARD_TITLE_SEVERAL_MESSAGES') : this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_FORWARD_TITLE');
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<MessengerPopup
			:id="POPUP_ID"
			:config="config"
			@close="$emit('close')"
		>
			<ForwardContent :messagesIds="messagesIds" @close="$emit('close')" />
		</MessengerPopup>
	`
	};

	exports.AddToChat = AddToChat;
	exports.AddToCollab = AddToCollab;
	exports.ForwardPopup = ForwardPopup;

}((this.BX.Messenger.v2.Component.EntitySelector = this.BX.Messenger.v2.Component.EntitySelector || {}),BX.Messenger.v2.Service,BX.UI.EntitySelector,BX.Messenger.v2.Lib,BX,BX.Intranet,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Messenger.v2.Component.Elements,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Component,BX.Messenger.v2.Component));
//# sourceMappingURL=registry.bundle.js.map
