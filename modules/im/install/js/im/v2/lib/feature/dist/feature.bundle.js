/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_const,im_v2_lib_feature,ui_infoHelper) {
	'use strict';

	const ChatHistoryManager = {
	  isAvailable() {
	    const {
	      fullChatHistory
	    } = this.getTariffRestrictions();
	    return fullChatHistory.isAvailable;
	  },
	  getDaysLimit() {
	    const {
	      fullChatHistory
	    } = this.getTariffRestrictions();
	    return fullChatHistory.limitDays;
	  },
	  openFeatureSlider() {
	    const promoter = new ui_infoHelper.FeaturePromoter({
	      code: im_v2_const.SliderCode.historyLimited
	    });
	    promoter.show();
	  },
	  getLimitTitle() {
	    return main_core.Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_TITLE');
	  },
	  getLimitSubtitle(withEmphasis = false) {
	    if (withEmphasis) {
	      return main_core.Loc.getMessagePlural('IM_LIB_FEATURE_HISTORY_LIMIT_SUBTITLE', this.getDaysLimit(), {
	        '#DAY_LIMIT#': this.getDaysLimit()
	      });
	    }
	    return main_core.Loc.getMessagePlural('IM_LIB_FEATURE_HISTORY_LIMIT_SUBTITLE', this.getDaysLimit(), {
	      '#DAY_LIMIT#': this.getDaysLimit(),
	      '[action_emphasis]': '',
	      '[/action_emphasis]': ''
	    });
	  },
	  getLearnMoreText() {
	    return main_core.Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_LEARN_MORE');
	  },
	  getTooltipText() {
	    return main_core.Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_TOOLTIP');
	  },
	  getTariffRestrictions() {
	    return im_v2_application_core.Core.getStore().getters['application/tariffRestrictions/get'];
	  }
	};

	const MessagesAutoDelete = {
	  openFeatureSlider() {
	    const promoter = new ui_infoHelper.FeaturePromoter({
	      code: im_v2_const.SliderCode.autoDeleteDisabled
	    });
	    promoter.show();
	  }
	};

	const CollabManager = {
	  isAvailable() {
	    return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabAvailable);
	  },
	  openFeatureSlider() {
	    const promoter = new ui_infoHelper.FeaturePromoter({
	      featureId: im_v2_const.SliderCode.collabDisabled
	    });
	    promoter.show();
	  }
	};

	const Feature = {
	  chatV2: 'chatV2',
	  openLinesV2: 'openLinesV2',
	  chatDepartments: 'chatDepartments',
	  copilotActive: 'copilotActive',
	  copilotAvailable: 'copilotAvailable',
	  sidebarLinks: 'sidebarLinks',
	  sidebarFiles: 'sidebarFiles',
	  sidebarBriefs: 'sidebarBriefs',
	  zoomActive: 'zoomActive',
	  zoomAvailable: 'zoomAvailable',
	  giphyAvailable: 'giphyAvailable',
	  collabAvailable: 'collabAvailable',
	  collabCreationAvailable: 'collabCreationAvailable',
	  enabledCollabersInvitation: 'enabledCollabersInvitation',
	  changeInviteLanguageAvailable: 'changeInviteLanguageAvailable',
	  inviteByLinkAvailable: 'inviteByLinkAvailable',
	  inviteByPhoneAvailable: 'inviteByPhoneAvailable',
	  documentSignAvailable: 'documentSignAvailable',
	  intranetInviteAvailable: 'intranetInviteAvailable',
	  voteCreationAvailable: 'voteCreationAvailable',
	  defaultTabCopilotAvailable: 'defaultTabCopilotAvailable',
	  messagesAutoDeleteEnabled: 'messagesAutoDeleteEnabled',
	  isNotificationsStandalone: 'isNotificationsStandalone',
	  isAIModelChangeAllowed: 'isCopilotSelectModelAvailable',
	  teamsInStructureAvailable: 'teamsInStructureAvailable',
	  isDesktopRedirectAvailable: 'isDesktopRedirectAvailable',
	  aiAssistantBotAvailable: 'aiAssistantAvailable',
	  aiAssistantChatAvailable: 'aiAssistantChatCreationAvailable',
	  aiFileTranscriptionAvailable: 'aiFileTranscriptionAvailable',
	  isTasksRecentListAvailable: 'isTasksRecentListAvailable'
	};
	const FeatureManager = {
	  chatHistory: ChatHistoryManager,
	  messagesAutoDelete: MessagesAutoDelete,
	  collab: CollabManager,
	  isFeatureAvailable(featureName) {
	    var _featureOptions$featu;
	    const {
	      featureOptions = {}
	    } = im_v2_application_core.Core.getApplicationData();
	    return (_featureOptions$featu = featureOptions[featureName]) != null ? _featureOptions$featu : false;
	  }
	};

	exports.Feature = Feature;
	exports.FeatureManager = FeatureManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.UI));
//# sourceMappingURL=feature.bundle.js.map
