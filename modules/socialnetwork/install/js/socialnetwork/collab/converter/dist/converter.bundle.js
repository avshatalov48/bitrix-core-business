/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_sidepanel,ui_buttons,main_popup,ui_vue3,ui_vue3_mixins_locMixin,main_core,ui_loader,ui_avatar,ui_iconSet_api_vue,ui_iconSet_api_core,ui_vue3_components_button) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	var _groupId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _errors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errors");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _prepareErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareErrors");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _renderPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPopupContent");
	var _renderHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHeader");
	var _renderDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescription");
	var _renderLinkMore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLinkMore");
	var _renderButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtons");
	var _getContentConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContentConfig");
	var _getMultipleErrorsPopupConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMultipleErrorsPopupConfig");
	var _getOnlyFlowErrorPopupConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOnlyFlowErrorPopupConfig");
	var _hasMultipleErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasMultipleErrors");
	var _hasOnlyFlowError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOnlyFlowError");
	class ErrorPopup {
	  constructor(params) {
	    Object.defineProperty(this, _hasOnlyFlowError, {
	      value: _hasOnlyFlowError2
	    });
	    Object.defineProperty(this, _hasMultipleErrors, {
	      value: _hasMultipleErrors2
	    });
	    Object.defineProperty(this, _getOnlyFlowErrorPopupConfig, {
	      value: _getOnlyFlowErrorPopupConfig2
	    });
	    Object.defineProperty(this, _getMultipleErrorsPopupConfig, {
	      value: _getMultipleErrorsPopupConfig2
	    });
	    Object.defineProperty(this, _getContentConfig, {
	      value: _getContentConfig2
	    });
	    Object.defineProperty(this, _renderButtons, {
	      value: _renderButtons2
	    });
	    Object.defineProperty(this, _renderLinkMore, {
	      value: _renderLinkMore2
	    });
	    Object.defineProperty(this, _renderDescription, {
	      value: _renderDescription2
	    });
	    Object.defineProperty(this, _renderHeader, {
	      value: _renderHeader2
	    });
	    Object.defineProperty(this, _renderPopupContent, {
	      value: _renderPopupContent2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _prepareErrors, {
	      value: _prepareErrors2
	    });
	    this.ERROR_CODE_HAS_FLOWS = 10002;
	    Object.defineProperty(this, _groupId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errors, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId] = params.groupId;
	    babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors] = babelHelpers.classPrivateFieldLooseBase(this, _prepareErrors)[_prepareErrors](params.errors);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	  }
	}
	function _prepareErrors2(errors) {
	  const supportedCodes = new Set([this.ERROR_CODE_HAS_FLOWS]);
	  const supportedErrors = errors.filter(error => supportedCodes.has(error.code));
	  if (errors.length > supportedErrors.length) {
	    const unsupportedErrors = errors.filter(error => !supportedCodes.has(error.code));
	    console.error('Unexpected errors', unsupportedErrors);
	  }
	  return supportedErrors;
	}
	function _getPopup2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	      cacheable: false,
	      width: 400,
	      borderRadius: 'var(--ui-border-radius-3xl)',
	      angle: false,
	      closeIcon: false,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _renderPopupContent)[_renderPopupContent](),
	      closeByEsc: false,
	      autoHide: false,
	      padding: 18,
	      contentPadding: 0,
	      overlay: true,
	      className: 'socialnetwork-collab-converter-error-popup'
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	}
	function _renderPopupContent2() {
	  const config = babelHelpers.classPrivateFieldLooseBase(this, _getContentConfig)[_getContentConfig]();
	  return main_core.Tag.render(_t || (_t = _`
			<div class="socialnetwork-collab-converter-error-popup-content">
				${0}
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderHeader)[_renderHeader](config), babelHelpers.classPrivateFieldLooseBase(this, _renderDescription)[_renderDescription](config), babelHelpers.classPrivateFieldLooseBase(this, _renderLinkMore)[_renderLinkMore](config), babelHelpers.classPrivateFieldLooseBase(this, _renderButtons)[_renderButtons](config));
	}
	function _renderHeader2(config) {
	  const headerText = config.header.length > 0 ? main_core.Tag.render(_t2 || (_t2 = _`<div class="socialnetwork-collab-converter-error-popup-header-text">${0}</div>`), config.header) : '';
	  const closeIcon = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-icon-set --${0} socialnetwork-collab-converter-error-popup-close-icon"/>
		`), ui_iconSet_api_core.Outline.CROSS_L);
	  main_core.Event.bind(closeIcon, 'click', () => {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr.close();
	  });
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="socialnetwork-collab-converter-error-popup-header">
				${0}
				${0}
			</div>
		`), headerText, closeIcon);
	}
	function _renderDescription2(config) {
	  const nodes = [];
	  config.descriptionParagraphs.forEach(paragraph => {
	    const node = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="socialnetwork-collab-converter-error-popup-description-paragraph">
					${0}
				</div>
			`), paragraph);
	    nodes.push(node);
	  });
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="socialnetwork-collab-converter-error-popup-description">
				${0}
			</div>
		`), nodes);
	}
	function _renderLinkMore2(config) {
	  if (!config.helperCode) {
	    return null;
	  }
	  const node = main_core.Tag.render(_t7 || (_t7 = _`
			<div class="socialnetwork-collab-converter-error-popup-link-more">
				<div class="ui-icon-set --${0} socialnetwork-collab-converter-error-popup-link-more-icon"></div>
				<div class="socialnetwork-collab-converter-error-popup-link-more-text">
					${0}
				</div>
			</div>
		`), ui_iconSet_api_core.Outline.KNOWLEDGE_BASE, main_core.Loc.getMessage('SN_COLLAB_CONVERTER_LINK_MORE'));
	  main_core.Event.bind(node, 'click', () => BX.Helper.show(`redirect=detail&code=${config.helperCode}`));
	  return node;
	}
	function _renderButtons2(config) {
	  return main_core.Tag.render(_t8 || (_t8 = _`
			<div class="socialnetwork-collab-converter-error-popup-buttons">
				${0}
			</div>
		`), config.buttons.map(button => button.render()));
	}
	function _getContentConfig2() {
	  let config = null;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasMultipleErrors)[_hasMultipleErrors]()) {
	    config = babelHelpers.classPrivateFieldLooseBase(this, _getMultipleErrorsPopupConfig)[_getMultipleErrorsPopupConfig]();
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyFlowError)[_hasOnlyFlowError]()) {
	    config = babelHelpers.classPrivateFieldLooseBase(this, _getOnlyFlowErrorPopupConfig)[_getOnlyFlowErrorPopupConfig]();
	  }
	  return config;
	}
	function _getMultipleErrorsPopupConfig2() {
	  const closeButton = new ui_buttons.Button({
	    useAirDesign: true,
	    noCaps: true,
	    size: ui_buttons.ButtonSize.LARGE,
	    text: main_core.Loc.getMessage('SN_COLLAB_CONVERTER_GET_IT'),
	    onclick: () => {
	      var _babelHelpers$classPr2;
	      return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr2.close();
	    }
	  });
	  return {
	    header: '',
	    descriptionParagraphs: [main_core.Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_CONTENT_MULTIPLE')],
	    helperCode: '25356654#int',
	    buttons: [closeButton]
	  };
	}
	function _getOnlyFlowErrorPopupConfig2() {
	  const flowsLink = main_core.Loc.getMessage('SN_COLLAB_CONVERTER_FLOW_URL_TEMPLATE', {
	    '#groupId#': babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId]
	  });
	  const clickHandler = event => {
	    var _babelHelpers$classPr3;
	    main_sidepanel.SidePanel.Instance.open(flowsLink);
	    (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr3.close();
	  };
	  const openFlowsButton = new ui_buttons.Button({
	    useAirDesign: true,
	    noCaps: true,
	    size: ui_buttons.ButtonSize.LARGE,
	    text: main_core.Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_BUTTON_CONFIRM_FLOWS'),
	    onclick: clickHandler
	  });
	  const cancelButton = new ui_buttons.Button({
	    useAirDesign: true,
	    noCaps: true,
	    style: ui_buttons.AirButtonStyle.OUTLINE,
	    size: ui_buttons.ButtonSize.LARGE,
	    text: main_core.Loc.getMessage('SN_COLLAB_CONVERTER_CANCEL'),
	    onclick: () => {
	      var _babelHelpers$classPr4;
	      return (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr4.close();
	    }
	  });
	  return {
	    header: main_core.Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_TITLE_FLOWS'),
	    descriptionParagraphs: [main_core.Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_CONTENT_FLOWS')],
	    helperCode: 21307012,
	    buttons: [openFlowsButton, cancelButton]
	  };
	}
	function _hasMultipleErrors2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors].length > 1;
	}
	function _hasOnlyFlowError2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors].length === 1 && babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors][0].code === this.ERROR_CODE_HAS_FLOWS;
	}

	function mapMemberDtoToModel(memberDto) {
	  const role = {
	    true: 'member',
	    [memberDto.isModerator || memberDto.isScrumMaster]: 'moderator',
	    [memberDto.isOwner]: 'owner'
	  }.true;
	  return {
	    id: memberDto.id,
	    avatar: memberDto.photo,
	    role
	  };
	}
	function mapFeatureDtoToModel(featureDto) {
	  return {
	    name: featureDto.featureName,
	    isActive: featureDto.active
	  };
	}
	function mapGroupDtoToModel(groupDto) {
	  var _avatarTypes$avatarTy;
	  const avatarType = groupDto.AVATAR_TYPE;
	  const avatarTypes = groupDto.AVATAR_TYPES;
	  const image = groupDto.AVATAR || (avatarTypes == null ? void 0 : (_avatarTypes$avatarTy = avatarTypes[avatarType]) == null ? void 0 : _avatarTypes$avatarTy.entitySelectorUrl) || '';
	  return {
	    id: groupDto.ID,
	    name: groupDto.NAME,
	    image,
	    members: groupDto.LIST_OF_MEMBERS.map(memberDto => mapMemberDtoToModel(memberDto)),
	    features: groupDto.FEATURES.map(featureDto => mapFeatureDtoToModel(featureDto))
	  };
	}

	class Api {
	  async validateGroup(groupId) {
	    const result = await main_core.ajax.runAction('socialnetwork.collab.Converter.validateGroup', {
	      data: {
	        id: groupId
	      }
	    });
	    return {
	      isValid: result.data.isValid,
	      errors: result.errors
	    };
	  }
	  async getGroup(groupId) {
	    const result = await main_core.ajax.runAction('socialnetwork.api.workgroup.get', {
	      data: {
	        params: {
	          groupId,
	          select: ['LIST_OF_MEMBERS', 'AVATAR', 'AVATAR_TYPES', 'FEATURES']
	        }
	      }
	    });
	    return mapGroupDtoToModel(result.data);
	  }
	  async convertToCollab(groupId) {
	    const result = await main_core.ajax.runAction('socialnetwork.collab.Converter.convertToCollab', {
	      data: {
	        id: groupId
	      }
	    });
	    return result.data;
	  }
	}
	const api = new Api();

	//TODO: move to ui

	// @vue/component
	const Loader = {
	  props: {
	    options: {
	      type: Object,
	      default: null
	    }
	  },
	  mounted() {
	    this.loader = new ui_loader.Loader(this.getOptions());
	    this.loader.render();
	    this.loader.show();
	  },
	  beforeUnmount() {
	    var _this$loader;
	    (_this$loader = this.loader) == null ? void 0 : _this$loader.hide == null ? void 0 : _this$loader.hide();
	    this.loader = null;
	  },
	  methods: {
	    getOptions() {
	      return {
	        ...this.getDefaultOptions(),
	        ...this.options
	      };
	    },
	    getDefaultOptions() {
	      return {
	        target: this.$refs.loader,
	        type: 'BULLET',
	        size: 'xl'
	      };
	    }
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-loader-container" ref="loader"></div>
	`
	};

	// @vue/component
	const GroupAvatar = {
	  props: {
	    group: {
	      type: Object,
	      required: true
	    },
	    size: {
	      type: Number,
	      default: 48
	    }
	  },
	  mounted() {
	    const group = this.group;
	    const avatar = new ui_avatar.AvatarHexagonGuest({
	      size: this.size,
	      userName: group.name,
	      userpicPath: group.image
	    });
	    return avatar.renderTo(this.$refs.container);
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-group-avatar" ref="container"/>
	`
	};

	const Advantage = {
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    icon: {
	      type: String,
	      required: true
	    },
	    iconColor: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    description: {
	      type: String,
	      default: ''
	    }
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-advantage --slide">
			<BIcon :name="icon" :color="iconColor" :size="28"/>
			<div class="socialnetwork-collab-converter-wizard-advantage-content">
				<div class="socialnetwork-collab-converter-wizard-advantage-content-title" v-html="title"/>
				<div
					v-if="description.length > 0"
					class="socialnetwork-collab-converter-wizard-advantage-content-description"
					v-html="description"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const UserAvatar = {
	  props: {
	    src: {
	      type: String,
	      default: ''
	    },
	    size: {
	      type: Number,
	      default: 14
	    }
	  },
	  created() {
	    this.avatar = new ui_avatar.AvatarBase({
	      size: this.size,
	      picPath: this.src,
	      baseColor: '#858D95'
	    });
	  },
	  mounted() {
	    if (this.avatar && this.$refs.container) {
	      this.avatar.renderTo(this.$refs.container);
	    }
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-user-list-avatar" ref="container"/>
	`
	};

	// @vue/component
	const AvatarsRow = {
	  components: {
	    UserAvatar
	  },
	  props: {
	    users: {
	      type: Array,
	      default: () => []
	    },
	    maxToShow: {
	      type: Number,
	      default: 5
	    },
	    borderColor: {
	      type: String,
	      default: 'transparent'
	    }
	  },
	  computed: {
	    rowUsers() {
	      return this.users.slice(0, this.maxToShow);
	    },
	    notShownAmount() {
	      return this.users.length - this.rowUsers.length;
	    }
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-user-list-pill" :style="{'border-color': borderColor}">
			<UserAvatar v-for="user in rowUsers" :src="user.avatar"/>
			<div v-if="notShownAmount" class="socialnetwork-collab-converter-wizard-user-list-not-shown">+{{notShownAmount}}</div>
		</div>
	`
	};

	// @vue/component
	const UserList = {
	  components: {
	    UserAvatar,
	    AvatarsRow
	  },
	  props: {
	    hostUsers: {
	      type: Array,
	      default: () => []
	    },
	    commonUsers: {
	      type: Array,
	      default: () => []
	    },
	    maxToShow: {
	      type: Number,
	      default: 5
	    }
	  },
	  template: `
		<div class="socialnetwork-collab-converter-wizard-user-list">
			<AvatarsRow v-if="hostUsers.length > 0" :users="hostUsers" :borderColor="'var(--ui-color-accent-main-success, #1BCE7B)'"/>
			<AvatarsRow v-if="commonUsers.length > 0" :users="commonUsers" :borderColor="'var(--ui-color-divider-default, #F0F0F0)'"/>
		</div>
	`
	};

	const Step = {
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    UiButton: ui_vue3_components_button.Button
	  },
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    primaryButtonText: {
	      type: String,
	      required: true
	    },
	    alertText: {
	      type: String,
	      default: ''
	    }
	  },
	  setup() {
	    return {
	      AirButtonStyle: ui_vue3_components_button.AirButtonStyle,
	      ButtonSize: ui_vue3_components_button.ButtonSize,
	      Outline: ui_iconSet_api_core.Outline
	    };
	  },
	  emits: ['continue', 'close'],
	  template: `
		<div class="socialnetwork-collab-converter-wizard-step">
			<div class="socialnetwork-collab-converter-wizard-step-content">
				<div class="socialnetwork-collab-converter-wizard-step-title">
					{{ title }}
				</div>
				<slot/>
			</div>
			<div class="socialnetwork-collab-converter-wizard-step-footer">
				<div v-if="alertText.length > 0" class="socialnetwork-collab-converter-wizard-step-footer-alert">
					<BIcon :name="Outline.ALERT" :size="16" :color="'var(--ui-color-accent-main-warning, #FAA72C)'"/>
					<div class="socialnetwork-collab-converter-wizard-step-footer-alert-text" v-html="alertText"/>
				</div>
				<div class="socialnetwork-collab-converter-wizard-step-footer-buttons">
					<UiButton
						:text="primaryButtonText"
						:size="ButtonSize.LARGE"
						@click="$emit('continue')"
						data-test-id="socialnetwork-collab-converter-continue-button"
					/>
					<UiButton
						:text="loc('SN_COLLAB_CONVERTER_CANCEL')"
						:size="ButtonSize.LARGE"
						:style="AirButtonStyle.OUTLINE"
						@click="$emit('close')"
						data-test-id="socialnetwork-collab-converter-cancel-button"
					/>
				</div>
			</div>
		</div>
	`
	};

	const StepAdvantages = {
	  components: {
	    UiButton: ui_vue3_components_button.Button,
	    Advantage,
	    Step
	  },
	  setup() {
	    return {
	      AirButtonStyle: ui_vue3_components_button.AirButtonStyle,
	      ButtonSize: ui_vue3_components_button.ButtonSize
	    };
	  },
	  emits: ['continue', 'close'],
	  computed: {
	    advantages() {
	      return [{
	        icon: ui_iconSet_api_core.Outline.MESSAGES,
	        iconColor: 'var(--ui-color-accent-main-primary, #0075FF)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_CHAT_TITLE'),
	        description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_CHAT_DESCRIPTION')
	      }, {
	        icon: ui_iconSet_api_core.Outline.SUITCASE,
	        iconColor: 'var(--ui-color-accent-extra-aqua, #37C5D8)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_DISCUSSION_TITLE'),
	        description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_DISCUSSION_DESCRIPTION')
	      }, {
	        icon: ui_iconSet_api_core.Outline.WINDOW_FLAG,
	        iconColor: 'var(--ui-color-accent-soft-element-green, #02BB9A)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_FAST_TITLE'),
	        description: this.loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_ADVANTAGE_FAST_DESCRIPTION')
	      }];
	    }
	  },
	  template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_ADVANTAGES_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_CONTINUE')"
			@continue="$emit('continue')"
			@close="$emit('close')"
		>
			<div class="socialnetwork-collab-converter-wizard-advantages">
				<Advantage
					v-for="(advantage, index) in advantages"
					:icon="advantage.icon"
					:iconColor="advantage.iconColor"
					:title="advantage.title"
					:description="advantage.description"
					:style="{'--i': index}"
				/>
			</div>
		</Step>
	`
	};

	const StepAfter = {
	  components: {
	    UiButton: ui_vue3_components_button.Button,
	    Advantage,
	    Step
	  },
	  setup() {
	    return {
	      AirButtonStyle: ui_vue3_components_button.AirButtonStyle,
	      ButtonSize: ui_vue3_components_button.ButtonSize,
	      Outline: ui_iconSet_api_core.Outline
	    };
	  },
	  emits: ['continue', 'close'],
	  computed: {
	    advantages() {
	      return [{
	        icon: ui_iconSet_api_core.Outline.SHUFFLE,
	        iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_COLLABORATION_TITLE')
	      }, {
	        icon: ui_iconSet_api_core.Outline.TASK_LIST,
	        iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_DISCUSSION_TITLE')
	      }, {
	        icon: ui_iconSet_api_core.Outline.CHATS_WITH_CHECK,
	        iconColor: 'var(--ui-color-collab-accent-primary-alt, #00A94E)',
	        title: this.loc('SN_COLLAB_CONVERTER_STEP_AFTER_ADVANTAGE_FAST_TITLE')
	      }];
	    }
	  },
	  template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_AFTER_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_START')"
			:alertText="loc('SN_COLLAB_CONVERTER_ALERT')"
			@continue="$emit('continue')"
			@close="$emit('close')"
		>
			<div class="socialnetwork-collab-converter-wizard-advantages">
				<Advantage
					v-for="(advantage, index) in advantages"
					:icon="advantage.icon"
					:iconColor="advantage.iconColor"
					:title="advantage.title"
					:description="advantage.description"
					:style="{'--i': index}"
				/>
			</div>
		</Step>
	`
	};

	const StepFeatures = {
	  components: {
	    UiButton: ui_vue3_components_button.Button,
	    Advantage,
	    Step,
	    BIcon: ui_iconSet_api_core.BIcon
	  },
	  props: {
	    features: {
	      /** @type Array<Feature> */
	      type: Array,
	      required: true
	    }
	  },
	  setup() {
	    return {
	      AirButtonStyle: ui_vue3_components_button.AirButtonStyle,
	      ButtonSize: ui_vue3_components_button.ButtonSize,
	      Outline: ui_iconSet_api_core.Outline
	    };
	  },
	  emits: ['continue', 'close'],
	  methods: {
	    getFeatureName(featureCode) {
	      const phrase = {
	        [featureCode === 'group_lists']: 'SN_COLLAB_CONVERTER_FEATURE_GROUP_LISTS',
	        [featureCode === 'forum']: 'SN_COLLAB_CONVERTER_FEATURE_FORUM',
	        [featureCode === 'photo']: 'SN_COLLAB_CONVERTER_FEATURE_PHOTO',
	        [featureCode === 'wiki']: 'SN_COLLAB_CONVERTER_FEATURE_WIKI',
	        [featureCode === 'marketplace']: 'SN_COLLAB_CONVERTER_FEATURE_MARKETPLACE',
	        [featureCode === 'landing_knowledge']: 'SN_COLLAB_CONVERTER_FEATURE_LANDING_KNOWLEDGE'
	      }.true;
	      return this.loc(phrase);
	    },
	    handleLinkMoreClick() {
	      BX.Helper.show('redirect=detail&code=25356654#tool');
	    }
	  },
	  template: `
		<Step
			:title="loc('SN_COLLAB_CONVERTER_STEP_FEATURES_TITLE')"
			:primaryButtonText="loc('SN_COLLAB_CONVERTER_START')"
			:alertText="loc('SN_COLLAB_CONVERTER_ALERT')"
			class="--features"
			@continue="$emit('continue')"
			@close="$emit('close')"
		>
			<div class="socialnetwork-collab-converter-wizard-step-subtitle">
				<div class="socialnetwork-collab-converter-wizard-step-subtitle-text" v-html="loc('SN_COLLAB_CONVERTER_STEP_FEATURES_SUBTITLE')"/>
				<div class="socialnetwork-collab-converter-wizard-step-subtitle-link" @click="handleLinkMoreClick">
					{{ loc('SN_COLLAB_CONVERTER_LINK_MORE') }}
				</div>
			</div>
			<div class="socialnetwork-collab-converter-wizard-features">
				<div v-for="feature in features" class="socialnetwork-collab-converter-wizard-feature">
					<BIcon :name="Outline.CIRCLE_MINUS" :color="'var(--ui-color-base-6, #DFE0E3)'" :size="20"/>
					<div class="socialnetwork-collab-converter-wizard-feature-name">
						{{ getFeatureName(feature.name) }}
					</div>
				</div>
			</div>
		</Step>
	`
	};

	// @vue/component
	const App = {
	  name: 'SocialnetworkCollabConverter',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    Loader,
	    GroupAvatar,
	    UserList,
	    Advantage,
	    UiButton: ui_vue3_components_button.Button,
	    StepAdvantages,
	    StepAfter,
	    StepFeatures
	  },
	  props: {
	    groupId: {
	      type: Number,
	      required: true
	    },
	    redirectAfterSuccess: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup() {
	    return {
	      ButtonSize: ui_vue3_components_button.ButtonSize,
	      AirButtonStyle: ui_vue3_components_button.AirButtonStyle,
	      Outline: ui_iconSet_api_core.Outline
	    };
	  },
	  data() {
	    return {
	      collab: null,
	      isConversionStarted: false,
	      group: null,
	      step: 1
	    };
	  },
	  computed: {
	    leftTopTitle() {
	      return this.loc('SN_COLLAB_CONVERTER_TITLE', {
	        '#ACCENT_START#': '<span class="socialnetwork-collab-converter-wizard-left-top-title-accent">',
	        '#ACCENT_END#': '</span>'
	      });
	    },
	    groupManagers() {
	      return this.group.members.filter(member => ['owner', 'moderator'].includes(member.role));
	    },
	    groupCommonMembers() {
	      return this.group.members.filter(member => ['member'].includes(member.role));
	    },
	    featuresToDisable() {
	      const featuresToDisable = new Set(['forum', 'photo', 'group_lists', 'wiki', 'landing_knowledge', 'marketplace']);
	      return this.group.features.filter(feature => feature.isActive && featuresToDisable.has(feature.name));
	    },
	    fullScaleTitle() {
	      if (this.collab) {
	        return this.loc('SN_COLLAB_CONVERTER_DONE');
	      }
	      return this.loc('SN_COLLAB_CONVERTER_TITLE_CONVERTING');
	    },
	    fullScaleSubtitle() {
	      return this.collab ? '' : this.loc('SN_COLLAB_CONVERTER_SUBTITLE_CONVERTING');
	    }
	  },
	  async created() {
	    try {
	      this.group = await api.getGroup(this.groupId);
	    } catch (error) {
	      console.error('Fetch group error', error);
	      this.close();
	    }
	  },
	  methods: {
	    handleStepAdvantagesContinue() {
	      this.step++;
	    },
	    handleStepAfterContinue() {
	      if (this.featuresToDisable.length === 0) {
	        void this.convert();
	        return;
	      }
	      this.step++;
	    },
	    handleStepFeaturesContinue() {
	      void this.convert();
	    },
	    async convert() {
	      this.isConversionStarted = true;
	      try {
	        this.collab = await api.convertToCollab(this.groupId);
	        main_core.Event.EventEmitter.emit('socialnetwork:collab:converter:success', {
	          collab: this.collab
	        });
	      } catch (error) {
	        console.error('Conversion error', error);
	        this.close();
	      }
	    },
	    close() {
	      main_core.Event.EventEmitter.emit('socialnetwork:collab:converter:close');
	    },
	    openCollab() {
	      if (top.BX.Messenger.Public) {
	        top.BX.Messenger.Public.openChat(this.collab.dialogId);
	      }
	      this.close();
	    }
	  },
	  template: `
		<div
			v-if="group"
			class="socialnetwork-collab-converter-wizard"
		>
			<template v-if="isConversionStarted">
				<div class="socialnetwork-collab-converter-wizard-full-scale">
					<div class="socialnetwork-collab-converter-wizard-full-scale-content">
						<div class="socialnetwork-collab-converter-wizard-full-scale-header">
							<div class="socialnetwork-collab-converter-wizard-full-scale-title">
								{{ fullScaleTitle }}
							</div>
							<div class="socialnetwork-collab-converter-wizard-full-scale-subtitle">
								{{ fullScaleSubtitle }}
							</div>
						</div>
						<div class="socialnetwork-collab-converter-wizard-full-scale-group">
							<GroupAvatar :group="group" :size="72"/>
							<div class="socialnetwork-collab-converter-wizard-group-title">
								{{ group.name }}
							</div>
						</div>
					</div>
					<div v-if="collab && !redirectAfterSuccess" class="socialnetwork-collab-converter-wizard-full-scale-buttons">
						<UiButton
							:text="loc('SN_COLLAB_CONVERTER_OPEN_COLLAB')"
							:size="ButtonSize.LARGE"
							@click="openCollab"
							data-test-id="socialnetwork-collab-converter-open-collab-button"
						/>
						<UiButton
							:text="loc('SN_COLLAB_CONVERTER_GET_IT')"
							:size="ButtonSize.LARGE"
							:style="AirButtonStyle.OUTLINE"
							@click="close"
							data-test-id="socialnetwork-collab-converter-close-button"
						/>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="socialnetwork-collab-converter-wizard-left">
					<div class="socialnetwork-collab-converter-wizard-left-top">
						<div class="socialnetwork-collab-converter-wizard-left-top-title" v-html="leftTopTitle"/>
						<div class="socialnetwork-collab-converter-wizard-group-info">
							<div class="socialnetwork-collab-converter-wizard-group-info-main">
								<GroupAvatar :group="group"/>
								<div class="socialnetwork-collab-converter-wizard-group-title">
									{{ group.name }}
								</div>
							</div>
							<UserList :hostUsers="groupManagers" :commonUsers="groupCommonMembers"/>
						</div>
					</div>
					<div class="socialnetwork-collab-converter-wizard-left-link">
						<BIcon :name="Outline.QUESTION" :size="16"/>
						<div class="socialnetwork-collab-converter-wizard-left-link-text">
							{{ loc('SN_COLLAB_CONVERTER_LINK_MORE') }}
						</div>
					</div>
				</div>
				<div class="socialnetwork-collab-converter-wizard-right">
					<StepAdvantages v-if="step === 1" @continue="handleStepAdvantagesContinue" @close="close"/>
					<StepAfter v-if="step === 2" @continue="handleStepAfterContinue" @close="close"/>
					<StepFeatures v-if="step === 3" :features="featuresToDisable" @continue="handleStepFeaturesContinue" @close="close"/>
				</div>
			</template>
		</div>
		<Loader v-else/>
	`
	};

	let _$1 = t => t,
	  _t$1;
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _wizardPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wizardPopup");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _showPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showPopup");
	var _renderPopupContent$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPopupContent");
	var _mountApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mountApplication");
	var _unmountApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unmountApplication");
	var _subscribe = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribe");
	var _unsubscribe = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribe");
	var _close = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("close");
	var _handleSuccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSuccess");
	class Wizard {
	  constructor(params) {
	    Object.defineProperty(this, _unsubscribe, {
	      value: _unsubscribe2
	    });
	    Object.defineProperty(this, _subscribe, {
	      value: _subscribe2
	    });
	    Object.defineProperty(this, _unmountApplication, {
	      value: _unmountApplication2
	    });
	    Object.defineProperty(this, _mountApplication, {
	      value: _mountApplication2
	    });
	    Object.defineProperty(this, _renderPopupContent$1, {
	      value: _renderPopupContent2$1
	    });
	    Object.defineProperty(this, _showPopup, {
	      value: _showPopup2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _wizardPopup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _close, {
	      writable: true,
	      value: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _wizardPopup)[_wizardPopup].close();
	      }
	    });
	    Object.defineProperty(this, _handleSuccess, {
	      writable: true,
	      value: event => {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].redirectAfterSuccess) {
	          const collab = event.data.collab;
	          top.document.location.href = collab.url;
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	  }
	  async show() {
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _showPopup)[_showPopup](resolve).then(() => {
	        resolve();
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }
	}
	function _showPopup2() {
	  const windowScrollHandler = () => babelHelpers.classPrivateFieldLooseBase(this, _wizardPopup)[_wizardPopup].adjustPosition();
	  return new Promise(resolve => {
	    babelHelpers.classPrivateFieldLooseBase(this, _wizardPopup)[_wizardPopup] = new main_popup.Popup({
	      cacheable: false,
	      width: 665,
	      height: 439,
	      borderRadius: 'var(--ui-border-radius-3xl)',
	      angle: false,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _renderPopupContent$1)[_renderPopupContent$1](),
	      closeByEsc: false,
	      autoHide: false,
	      closeIcon: false,
	      padding: 0,
	      contentPadding: 0,
	      contentBorderRadius: '18px',
	      animation: 'fading-slide',
	      overlay: true,
	      className: 'socialnetwork-collab-converter-wizard-popup',
	      events: {
	        onAfterPopupShow: async popup => {
	          main_core.Event.bind(window, 'scroll', windowScrollHandler);
	          babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].popupContainer = popup.getPopupContainer();
	          await babelHelpers.classPrivateFieldLooseBase(this, _mountApplication)[_mountApplication](babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wizardContainer);
	          babelHelpers.classPrivateFieldLooseBase(this, _subscribe)[_subscribe]();
	          resolve();
	        },
	        onPopupAfterClose: popup => {
	          main_core.Event.unbind(window, 'scroll', windowScrollHandler);
	          babelHelpers.classPrivateFieldLooseBase(this, _unmountApplication)[_unmountApplication]();
	          babelHelpers.classPrivateFieldLooseBase(this, _unsubscribe)[_unsubscribe]();
	          popup.destroy();
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _wizardPopup)[_wizardPopup].show();
	  });
	}
	function _renderPopupContent2$1() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wizardContainer = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="socialnetwork-collab-converter-wizard-popup-content"></div>
		`));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wizardContainer;
	}
	async function _mountApplication2(container) {
	  const application = ui_vue3.BitrixVue.createApp(App, babelHelpers.classPrivateFieldLooseBase(this, _params)[_params]);
	  application.mixin(ui_vue3_mixins_locMixin.locMixin);
	  application.mount(container);
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = application;
	}
	function _unmountApplication2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount();
	}
	function _subscribe2() {
	  main_core.Event.EventEmitter.subscribe('socialnetwork:collab:converter:close', babelHelpers.classPrivateFieldLooseBase(this, _close)[_close]);
	  main_core.Event.EventEmitter.subscribe('socialnetwork:collab:converter:success', babelHelpers.classPrivateFieldLooseBase(this, _handleSuccess)[_handleSuccess]);
	}
	function _unsubscribe2() {
	  main_core.Event.EventEmitter.unsubscribe('socialnetwork:collab:converter:close', babelHelpers.classPrivateFieldLooseBase(this, _close)[_close]);
	  main_core.Event.EventEmitter.unsubscribe('socialnetwork:collab:converter:success', babelHelpers.classPrivateFieldLooseBase(this, _handleSuccess)[_handleSuccess]);
	}

	var _params$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	class Converter {
	  constructor(params) {
	    Object.defineProperty(this, _params$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1] = params;
	  }
	  convertToCollab(groupId) {
	    api.validateGroup(groupId).then(result => {
	      if (!result.isValid) {
	        new ErrorPopup({
	          groupId,
	          errors: result.errors
	        }).show();
	        return;
	      }
	      void new Wizard({
	        groupId,
	        redirectAfterSuccess: babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].redirectAfterSuccess
	      }).show();
	    }).catch(result => {
	      new ErrorPopup({
	        groupId,
	        errors: result.errors
	      }).show();
	    });
	  }
	}

	exports.Converter = Converter;

}((this.BX.Socialnetwork.Collab = this.BX.Socialnetwork.Collab || {}),BX.SidePanel,BX.UI,BX.Main,BX.Vue3,BX.Vue3.Mixins,BX,BX.UI,BX.UI,BX.UI.IconSet,BX.UI.IconSet,BX.Vue3.Components));
//# sourceMappingURL=converter.bundle.js.map
