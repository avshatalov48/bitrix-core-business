/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,ui_infoHelper,im_v2_const,im_v2_application_core) {
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
	  inviteByLinkAvailable: 'inviteByLinkAvailable',
	  inviteByPhoneAvailable: 'inviteByPhoneAvailable',
	  documentSignAvailable: 'documentSignAvailable',
	  intranetInviteAvailable: 'intranetInviteAvailable',
	  voteCreationAvailable: 'voteCreationAvailable'
	};
	const FeatureManager = {
	  chatHistory: ChatHistoryManager,
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.UI,BX.Messenger.v2.Const,BX.Messenger.v2.Application));
//# sourceMappingURL=feature.bundle.js.map
