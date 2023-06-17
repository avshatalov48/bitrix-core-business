this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_lib_logger,im_v2_const,im_v2_provider_service,im_v2_application_core,ui_forms,im_v2_component_elements,im_v2_component_animation,ui_entitySelector) {
	'use strict';

	// @vue/component
	const ChatMembersSelector = {
	  emits: ['membersChange'],
	  data() {
	    return {};
	  },
	  computed: {
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    }
	  },
	  created() {
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      maxHeight: 99,
	      placeholder: '',
	      addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
	      addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
	      showCreateButton: false,
	      dialogOptions: {
	        enableSearch: false,
	        context: 'IM_CHAT_CREATE',
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department'
	        }],
	        preselectedItems: [['user', this.currentUserId]],
	        undeselectedItems: [['user', this.currentUserId]]
	      },
	      events: {
	        onAfterTagAdd: event => {
	          const selector = event.getTarget();
	          this.$emit('membersChange', selector.getTags().map(tag => tag.id));
	        },
	        onAfterTagRemove: event => {
	          const selector = event.getTarget();
	          this.$emit('membersChange', selector.getTags().map(tag => tag.id));
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs['members']);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__members" ref="members"></div>
	`
	};

	// @vue/component
	const Section = {
	  components: {
	    ExpandAnimation: im_v2_component_animation.ExpandAnimation
	  },
	  props: {
	    name: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isFolded: true
	    };
	  },
	  computed: {
	    containerClasses() {
	      return [`--${this.name}`, {
	        '--active': !this.isFolded
	      }];
	    }
	  },
	  methods: {
	    onContainerClick() {
	      if (this.isFolded) {
	        this.isFolded = false;
	      }
	    },
	    onHeaderClick() {
	      if (!this.isFolded) {
	        this.isFolded = true;
	      }
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope">
			<div @click="isFolded = !isFolded" class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div class="bx-im-content-create-chat__section_right"></div>	
			</div>
			<ExpandAnimation>
				<div v-if="!isFolded" class="bx-im-content-create-chat__section_content_container">
					<div class="bx-im-content-create-chat__section_content">
						<slot></slot>
					</div>
				</div>
			</ExpandAnimation>
		</div>
	`
	};

	// @vue/component
	const OwnerSelector = {
	  name: 'OwnerSelector',
	  props: {
	    ownerId: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['ownerChange'],
	  data() {
	    return {};
	  },
	  created() {
	    this.membersSelector = new ui_entitySelector.TagSelector({
	      multiple: false,
	      maxHeight: 33,
	      placeholder: '',
	      addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
	      addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_CHANGE_OWNER'),
	      showCreateButton: false,
	      dialogOptions: {
	        enableSearch: false,
	        context: 'IM_CHAT_CREATE',
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department'
	        }],
	        preselectedItems: [['user', this.ownerId]]
	      },
	      events: {
	        onBeforeTagAdd: event => {
	          const {
	            tag
	          } = event.getData();
	          tag.setDeselectable(false);
	        },
	        onAfterTagAdd: event => {
	          const {
	            tag
	          } = event.getData();
	          this.$emit('ownerChange', tag.id);
	        }
	      }
	    });
	  },
	  mounted() {
	    this.membersSelector.renderTo(this.$refs['owner']);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__owner" ref="owner"></div>
	`
	};

	const MANAGE_TYPES = [{
	  value: 'ALL',
	  text: 'All members',
	  default: true
	}, {
	  value: 'OWNERS',
	  text: 'Owners only'
	}, {
	  value: 'ADMINS_AND_OWNERS',
	  text: 'Admins and owners'
	}];

	// @vue/component
	const SettingsSection = {
	  components: {
	    Section,
	    Dropdown: im_v2_component_elements.Dropdown,
	    Toggle: im_v2_component_elements.Toggle,
	    OwnerSelector
	  },
	  props: {
	    isAvailableInSearch: {
	      type: Boolean,
	      required: true
	    },
	    ownerId: {
	      type: Number,
	      required: true
	    },
	    description: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['ownerChange', 'manageTypeChange', 'chatTypeChange', 'descriptionChange'],
	  data() {
	    return {
	      manageType: ''
	    };
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements.ToggleSize,
	    MANAGE_TYPES: () => MANAGE_TYPES,
	    descriptionPlaceholderText() {
	      return this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION_PLACEHOLDER', {
	        '#BR#': '\n',
	        '#QUOTE_START#': '"',
	        '#QUOTE_END#': '"'
	      });
	    }
	  },
	  methods: {
	    onManageTypeChange(value) {
	      this.$emit('manageTypeChange', value);
	    },
	    onOwnerChange(ownerId) {
	      this.$emit('ownerChange', ownerId);
	    },
	    onTypeChange(isAvailableInSearch) {
	      this.$emit('chatTypeChange', isAvailableInSearch);
	    },
	    onDescriptionChange(event) {
	      this.$emit('descriptionChange', event.target.value);
	    },
	    onToggleLabelClick() {
	      this.$refs['toggle'].toggle();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<Section name="settings" :title="loc('IM_CREATE_CHAT_SETTINGS_SECTION')">
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_OWNER') }}
				</div>
				<OwnerSelector :ownerId="ownerId" @ownerChange="onOwnerChange" />
				<!--<div class="bx-im-content-create-chat__heading">Who can manage the participants</div>-->
				<!--<div class="bx-im-content-create-chat-settings__manage-select">-->
				<!--<Dropdown :items="MANAGE_TYPES" id="im-content-create-chat-manage-menu" @itemChange="onManageTypeChange" />-->
				<!--</div>-->
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_PRIVACY') }}
				</div>
				<div class="bx-im-content-create-chat-settings__type-select">
					<Toggle :size="ToggleSize.S" :isEnabled="isAvailableInSearch" @change="onTypeChange" ref="toggle" />
					<div @click="onToggleLabelClick" class="bx-im-content-create-chat-settings__type-select_label">
						{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_AVAILABLE_FOR_SEARCH') }}
					</div>
				</div>	
			</div>
			<div class="bx-im-content-create-chat__section_block">
				<div class="bx-im-content-create-chat__heading">{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_DESCRIPTION') }}</div>
				<div class="bx-im-content-create-chat-settings__description_container">
					<div class="ui-ctl ui-ctl-textarea ui-ctl-w100 ui-ctl-no-resize">
						<textarea
							@input="onDescriptionChange"
							:value="description"
							:placeholder="descriptionPlaceholderText"
							placeholder="1&#10;2&#10;3&#10;4&#10;5"
							class="bx-im-content-create-chat-settings__description ui-ctl-element"
						></textarea>
					</div>
				</div>
			</div>
		</Section>
	`
	};

	// @vue/component
	const AppearanceSection = {
	  components: {
	    Section
	  },
	  data() {
	    return {};
	  },
	  template: `
		<Section name="appearance" title="Appearance" />
	`
	};

	// @vue/component
	const CreateChatContent = {
	  name: 'CreateChatContent',
	  components: {
	    ChatMembersSelector,
	    SettingsSection,
	    AppearanceSection,
	    Button: im_v2_component_elements.Button
	  },
	  data() {
	    return {
	      isCreating: false,
	      chatTitle: '',
	      chatMembers: [],
	      settings: {
	        ownerId: 0,
	        manageType: '',
	        isAvailableInSearch: false,
	        description: ''
	      }
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Content: CreateChat created');
	    this.chatService = new im_v2_provider_service.ChatService();
	    this.settings.ownerId = im_v2_application_core.Core.getUserId();
	  },
	  mounted() {
	    this.$refs['titleInput'].focus();
	  },
	  methods: {
	    onMembersChange(currentTags) {
	      this.chatMembers = currentTags;
	    },
	    onOwnerChange(ownerId) {
	      this.settings.ownerId = ownerId;
	    },
	    onManageTypeChange(value) {
	      this.settings.manageType = value;
	    },
	    onChatTypeChange(isAvailableInSearch) {
	      this.settings.isAvailableInSearch = isAvailableInSearch;
	    },
	    onDescriptionChange(description) {
	      this.settings.description = description;
	    },
	    onCreateClick() {
	      this.isCreating = true;
	      this.chatService.createChat({
	        title: this.chatTitle,
	        members: this.chatMembers,
	        ownerId: this.settings.ownerId,
	        manageType: this.settings.manageType,
	        isAvailableInSearch: this.settings.isAvailableInSearch,
	        description: this.settings.description
	      }).then(newDialogId => {
	        this.isCreating = false;
	        im_public.Messenger.openChat(newDialogId);
	      }).catch(() => {
	        this.isCreating = false;
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CREATE_CHAT_ERROR')
	        });
	      });
	    },
	    onCancelClick() {
	      im_public.Messenger.openChat();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-create-chat__container bx-im-content-create-chat__scope">
			<div class="bx-im-content-create-chat__content">
				<div class="bx-im-content-create-chat__header">
					<!--<div class="bx-im-content-create-chat__avatar"></div>-->
					<div class="bx-im-content-create-chat__title_container">
						<input
							v-model="chatTitle"
							:placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')"
							class="bx-im-content-create-chat__title_input"
							ref="titleInput"
						/>
					</div>
				</div>
				<ChatMembersSelector @membersChange="onMembersChange" />
				<SettingsSection
					:isAvailableInSearch="settings.isAvailableInSearch"
					:ownerId="settings.ownerId"
					:description="settings.description"
					@ownerChange="onOwnerChange"
					@manageTypeChange="onManageTypeChange"
					@chatTypeChange="onChatTypeChange"
					@descriptionChange="onDescriptionChange"
				/>
				<!--<AppearanceSection />-->
			</div>
			<div class="bx-im-content-create-chat__buttons">
				<div class="bx-im-content-create-chat__buttons_create">
					<Button
						:size="ButtonSize.XL"
						:color="ButtonColor.Success"
						:text="loc('IM_CREATE_CHAT_CONFIRM')"
						:isLoading="isCreating"
						:isDisabled="isCreating"
						@click="onCreateClick"
					/>
				</div>
				<div class="bx-im-content-create-chat__buttons_cancel">
					<Button
						:size="ButtonSize.XL"
						:color="ButtonColor.Link"
						:text="loc('IM_CREATE_CHAT_CANCEL')"
						:isDisabled="isCreating"
						@click="onCancelClick"
					/>
				</div>
			</div>
		</div>
	`
	};

	exports.CreateChatContent = CreateChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Application,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Animation,BX.UI.EntitySelector));
//# sourceMappingURL=create-chat-content.bundle.js.map
