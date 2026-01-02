/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_provider_service_chat,ui_entitySelector,im_v2_lib_channel,intranet_invitationInput,im_v2_application_core,im_v2_lib_helpdesk,im_v2_component_elements_scrollWithGradient,ui_infoHelper,im_v2_lib_permission,im_v2_lib_feature,im_v2_component_elements_button,im_v2_lib_rest,im_v2_lib_utils,intranet_languages,main_core,ui_vue3_directives_hint,main_popup,im_v2_component_elements_popup,main_core_events,im_v2_lib_soundNotification,im_v2_provider_service_sending,im_public,im_v2_const,im_v2_lib_analytics,im_v2_component_search,im_v2_lib_notifier) {
	'use strict';

	const SEARCH_ENTITY_ID = 'user';
	const DEFAULT_CONTAINER_HEIGHT = 600;

	// @vue/component
	const AddToChatContent = {
	  name: 'AddToChatContent',
	  components: {
	    AddToChat: im_v2_component_search.AddToChat,
	    ChatButton: im_v2_component_elements_button.ChatButton
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
	  emits: ['inviteMembers', 'close'],
	  data() {
	    return {
	      searchQuery: '',
	      showHistory: true,
	      selectedItems: new Set()
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor,
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
	  beforeUnmount() {
	    im_v2_lib_analytics.Analytics.getInstance().userAdd.onClosePopup();
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
	            im_v2_lib_analytics.Analytics.getInstance().userAdd.onStartSearch({
	              dialogId: this.dialogId
	            });
	            this.searchQuery = this.membersSelector.getTextBoxValue().trim().toLowerCase();
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
				<AddToChat
					:query="searchQuery"
					:dialogId="dialogId"
					:selectedItems="[...selectedItems]"
					@clickItem="onSelectItem"
				/>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__buttons">
				<ChatButton
					:size="ButtonSize.L"
					:color="ButtonColor.Primary"
					:isRounded="true"
					:isLoading="isLoading"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="selectedItems.size === 0"
					@click="onInviteClick"
				/>
				<ChatButton
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
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup,
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
	    this.chatService = new im_v2_provider_service_chat.ChatService();
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
	      }).catch(() => {
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
	      }).catch(() => {
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
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkMemberAdd, payload).catch(([error]) => {
	      console.error('CollabInvitationService: add employee error', error);
	    });
	  }
	  copyLink(collabId, userLang) {
	    const payload = {
	      data: {
	        collabId,
	        userLang
	      }
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.intranetInviteGetLinkByCollabId, payload).catch(([error]) => {
	      console.error('CollabInvitationService: getting invite link error', error);
	      throw error;
	    });
	  }
	  updateLink(collabId) {
	    const payload = {
	      data: {
	        collabId
	      }
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.intranetInviteRegenerateLinkByCollabId, payload).catch(([error]) => {
	      console.error('CollabInvitationService: updating invite link error', error);
	      throw error;
	    });
	  }
	}

	// @vue/component
	const CopyInviteLink = {
	  name: 'CopyInviteLink',
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton
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
	    },
	    langCode: {
	      type: String,
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
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor,
	    isInviteLinkAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.inviteByLinkAvailable);
	    },
	    isEnabledCollabersInvitation() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.enabledCollabersInvitation);
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
	      if (!this.isEnabledCollabersInvitation) {
	        this.showHelper();
	        return;
	      }
	      try {
	        this.isCopyingInviteLink = true;
	        const link = await new CollabInvitationService().copyLink(this.collabId, this.langCode);
	        await im_v2_lib_utils.Utils.text.copyToClipboard(link);
	        im_v2_lib_notifier.Notifier.onCopyLinkComplete();
	      } catch {
	        im_v2_lib_notifier.Notifier.collab.onCopyLinkError();
	      } finally {
	        this.isCopyingInviteLink = false;
	      }
	    },
	    async updateLink() {
	      if (!this.isEnabledCollabersInvitation) {
	        this.showHelper();
	        return;
	      }
	      try {
	        this.isUpdatingLink = true;
	        await new CollabInvitationService().updateLink(this.collabId);
	        im_v2_lib_notifier.Notifier.collab.onUpdateLinkComplete();
	      } catch {
	        im_v2_lib_notifier.Notifier.onDefaultError();
	      } finally {
	        this.isUpdatingLink = false;
	      }
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    showHelper() {
	      new ui_infoHelper.FeaturePromoter({
	        code: im_v2_const.SliderCode.collabInviteOff
	      }).show();
	    }
	  },
	  template: `
		<div v-if="isInviteLinkAvailable" class="bx-im-add-to-collab__invite-block --link">
			<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_BY_LINK') }}
			</span>
			<ChatButton
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

	const ITEM_CLASS = 'bx-im-add-to-collab__language-selector_item';
	const ACCEPTED_ITEM_CLASS = 'menu-popup-item-accept';

	// @vue/component
	const InviteLanguageSelector = {
	  name: 'InviteLanguageSelector',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  props: {
	    defaultLanguageCode: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['selectLanguage', 'openLanguageSelector', 'closeLanguageSelector'],
	  data() {
	    return {
	      selectedLanguageCode: this.defaultLanguageCode,
	      isSelectorShown: false
	    };
	  },
	  computed: {
	    selectedLanguageName() {
	      const lang = this.availableLanguages[this.selectedLanguageCode];
	      return lang ? lang.NAME : '';
	    }
	  },
	  created() {
	    this.availableLanguages = new intranet_languages.Languages().getLanguages();
	  },
	  methods: {
	    languageSelectorHint() {
	      return {
	        text: this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_LANGUAGE_SELECTOR_HINT'),
	        popupOptions: {
	          className: 'im-add-to-collab__invite-language-section_hint',
	          width: 270,
	          bindOptions: {
	            position: 'top',
	            forceBindPosition: true
	          },
	          angle: {
	            offset: 37,
	            position: 'bottom'
	          },
	          offsetTop: -8
	        }
	      };
	    },
	    onLanguageSelected(langCode) {
	      this.toggleSelector();
	      this.selectedLanguageCode = langCode;
	      this.$emit('selectLanguage', this.selectedLanguageCode);
	    },
	    toggleSelector() {
	      if (this.isSelectorShown) {
	        this.closeSelector();
	      } else {
	        this.showSelector();
	      }
	    },
	    showSelector() {
	      this.selector = this.createSelector();
	      this.selector.show();
	      this.isSelectorShown = true;
	      this.$emit('openLanguageSelector');
	    },
	    closeSelector() {
	      this.selector.close();
	      this.isSelectorShown = false;
	      this.$emit('closeLanguageSelector');
	    },
	    createSelector() {
	      return main_popup.PopupMenu.create({
	        id: main_core.Text.getRandom().toLowerCase(),
	        bindElement: this.$refs.inviteLanguageSection,
	        className: 'bx-im-messenger__scope bx-im-add-to-collab__invite-language-section_language-selector',
	        items: this.getMenuItems(),
	        angle: false,
	        autoHide: true,
	        closeByEsc: true,
	        maxHeight: 207,
	        contentPadding: 10,
	        padding: 10,
	        bindOptions: {
	          forceBindPosition: true
	        },
	        events: {
	          onPopupClose: () => {
	            this.isSelectorShown = false;
	            this.$emit('closeLanguageSelector');
	          }
	        }
	      });
	    },
	    getMenuItems() {
	      return Object.keys(this.availableLanguages).map(langCode => {
	        return new main_popup.MenuItem({
	          langCode,
	          className: this.getMenuItemClass(langCode),
	          text: this.getMenuItemText(langCode),
	          onclick: () => this.onLanguageSelected(langCode)
	        });
	      });
	    },
	    getMenuItemClass(langCode) {
	      return langCode === this.selectedLanguageCode ? `${ITEM_CLASS} ${ACCEPTED_ITEM_CLASS}` : ITEM_CLASS;
	    },
	    getMenuItemText(langCode) {
	      const lang = this.availableLanguages[langCode];
	      const langName = lang ? lang.NAME : '';
	      if (langCode === this.defaultLanguageCode) {
	        return this.loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_DEFAULT_LANGUAGE_TITLE', {
	          '#LANG_NAME#': langName
	        });
	      }
	      return langName;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<section
			class="bx-im-add-to-collab__invite-language-section"
			v-hint="languageSelectorHint"
			ref="inviteLanguageSection"
		>
			<span class="bx-im-add-to-collab__invite-language-section_title">
				{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_INVITE_LANGUAGE_TITLE') }}
			</span>
			<div
				class="bx-im-add-to-collab__invite-language-section_selected-language-block"
				@click="toggleSelector"
			>
				<span class="bx-im-add-to-collab__selected-language-block_title">
					{{ selectedLanguageName }}
				</span>
				<span
					class="bx-im-add-to-collab__selected-language-block_arrow"
					:class="{ '--open': isSelectorShown }"
				></span>
			</div>
		</section>
	`
	};

	const HELPDESK_SLIDER_CLOSE_EVENT = 'SidePanel.Slider:onClose';
	const HELPDESK_SLIDER_ID = 'main:helper';

	// @vue/component
	const AddGuestsTab = {
	  name: 'AddGuestsTab',
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton,
	    ScrollWithGradient: im_v2_component_elements_scrollWithGradient.ScrollWithGradient,
	    CopyInviteLink,
	    InviteLanguageSelector
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
	  emits: ['close', 'closeHelpdeskSlider', 'openHelpdeskSlider', 'closeLanguageSelector', 'openLanguageSelector'],
	  data() {
	    return {
	      isAddButtonDisabled: true,
	      isInvitingGuests: false,
	      invitationLangCode: ''
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor,
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
	    },
	    isEnabledCollabersInvitation() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.enabledCollabersInvitation);
	    },
	    isChangeInviteLanguageAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.changeInviteLanguageAvailable);
	    },
	    defaultLanguageCode() {
	      return im_v2_application_core.Core.getLanguageId();
	    }
	  },
	  created() {
	    this.initInvitationInput();
	    main_core_events.EventEmitter.subscribe(HELPDESK_SLIDER_CLOSE_EVENT, this.onCloseOpenHelpdeskSlider);
	  },
	  mounted() {
	    this.invitationLangCode = this.defaultLanguageCode;
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
	    onInvitationGuests() {
	      if (!this.isEnabledCollabersInvitation) {
	        this.showHelper();
	      }
	    },
	    showHelper() {
	      new ui_infoHelper.FeaturePromoter({
	        code: im_v2_const.SliderCode.collabInviteOff
	      }).show();
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
	    onInviteLanguageSelected(langCode) {
	      this.invitationLangCode = langCode;
	      this.invitationGuests.changeLanguage(langCode);
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
							<div class="bx-im-add-to-collab__description_content">
								<div class="bx-im-add-to-collab__description_title">{{ preparedDescriptionTitle }}</div>
								<div class="bx-im-add-to-collab__description_text">{{ preparedDescription }}</div>
								<a class="bx-im-add-to-collab__helpdesk-link" @click.prevent="openHelpdesk">
									{{ loc('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_HELPDESK_LINK') }}
								</a>
							</div>
							<div class="bx-im-add-to-collab__description_icon"></div>
						</div>
						<InviteLanguageSelector
							v-if="isChangeInviteLanguageAvailable"
							:defaultLanguageCode="defaultLanguageCode"
							@selectLanguage="onInviteLanguageSelected"
							@openLanguageSelector="$emit('openLanguageSelector')"
							@closeLanguageSelector="$emit('closeLanguageSelector')"
						/>
						<CopyInviteLink 
							:collabId="collabId"
							:dialogId="dialogId"
							:langCode="invitationLangCode"
						/>
						<div class="bx-im-add-to-collab__invite-block">
							<span class="bx-im-add-to-collab__invite-block-title --ellipsis">
								{{ preparedInvitationTitle }}
							</span>
							<div 
								ref="im-collab-invitation-guests-input" 
								class="bx-im-add-to-collab__invite-block-input"
								@click="onInvitationGuests"
							></div>
						</div>
					</div>
				</ScrollWithGradient>
			</div>
			<div class="bx-im-add-to-collab__buttons">
				<ChatButton
					:size="ButtonSize.L"
					:color="ButtonColor.Collab"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="isAddButtonDisabled || isInvitingGuests"
					:isLoading="isInvitingGuests"
					@click="addGuestToCollab"
				/>
				<ChatButton
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
	      new CollabInvitationService().addEmployees({
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
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup,
	    SegmentButton: im_v2_component_elements_button.SegmentButton,
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
	    isEnabledCollabersInvitation() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.enabledCollabersInvitation);
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
	  created() {
	    this.activeTabId = this.isEnabledCollabersInvitation ? TabId.guests : TabId.employees;
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
				<SegmentButton 
					:tabs="Tabs" 
					:activeTabId="activeTabId" 
					@segmentSelected="onTabSwitch"
				/>
			</div>
			<KeepAlive>
				<component
					:is="tabComponent"
					:dialogId="dialogId" 
					:height="finalHeight"
					@close="$emit('close')"
					@openHelpdeskSlider="disableAutoHide"
					@closeHelpdeskSlider="enableAutoHide"
					@openLanguageSelector="disableAutoHide"
					@closeLanguageSelector="enableAutoHide"
				/>
			</KeepAlive>
		</MessengerPopup>
	`
	};

	// @vue/component
	const ForwardContent = {
	  name: 'ForwardContent',
	  components: {
	    ForwardSearch: im_v2_component_search.ForwardSearch,
	    ChatSearchInput: im_v2_component_search.ChatSearchInput
	  },
	  props: {
	    messagesIds: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
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
	  beforeUnmount() {
	    im_v2_lib_analytics.Analytics.getInstance().messageForward.onClosePopup();
	  },
	  methods: {
	    onLoading(value) {
	      this.isLoading = value;
	    },
	    onUpdateSearch(query) {
	      im_v2_lib_analytics.Analytics.getInstance().messageForward.onStartSearch({
	        dialogId: this.dialogId
	      });
	      this.searchQuery = query.trim().toLowerCase();
	    },
	    isNotes(dialogId) {
	      return this.$store.getters['chats/isNotes'](dialogId);
	    },
	    async forwardToNotes(forwardDialogId) {
	      await im_v2_provider_service_sending.SendingService.getInstance().forwardMessages({
	        forwardIds: this.messagesIds,
	        dialogId: forwardDialogId
	      });
	      im_v2_lib_notifier.Notifier.message.onForwardNotesComplete(this.messagesIds);
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.send);
	    },
	    async onSelectItem(event) {
	      const {
	        dialogId: forwardDialogId
	      } = event;
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeBulkActionsMode, {
	        dialogId: this.dialogId
	      });
	      const isNotesForward = this.isNotes(forwardDialogId);
	      const isNotesOpen = this.isNotes(this.dialogId);
	      if (isNotesForward && !isNotesOpen) {
	        void this.forwardToNotes(forwardDialogId);
	      } else {
	        await im_public.Messenger.openChat(forwardDialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertForward, {
	          messagesIds: this.messagesIds,
	          dialogId: forwardDialogId
	        });
	      }
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
				<ForwardSearch
					:query="searchQuery"
					:dialogId="dialogId"
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
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup,
	    ForwardContent
	  },
	  props: {
	    messagesIds: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
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
	        overlay: true,
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
			<ForwardContent
				:dialogId="dialogId"
				:messagesIds="messagesIds" 
				@close="$emit('close')"
			/>
		</MessengerPopup>
	`
	};

	exports.AddToChat = AddToChat;
	exports.AddToCollab = AddToCollab;
	exports.ForwardPopup = ForwardPopup;

}((this.BX.Messenger.v2.Component.EntitySelector = this.BX.Messenger.v2.Component.EntitySelector || {}),BX.Messenger.v2.Service,BX.UI.EntitySelector,BX.Messenger.v2.Lib,BX.Intranet,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Intranet,BX,BX.Vue3.Directives,BX.Main,BX.Messenger.v2.Component.Elements,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
