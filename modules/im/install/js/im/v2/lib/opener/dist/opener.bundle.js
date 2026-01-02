/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_call,im_v2_lib_desktopApi,im_v2_lib_logger,im_v2_lib_phone,im_v2_lib_slider,im_v2_lib_feature,im_v2_provider_service_bot,im_v2_lib_createChat,im_v2_lib_navigation,im_v2_application_core,main_core,main_sidepanel,im_v2_const,im_v2_lib_layout,im_v2_lib_utils) {
	'use strict';

	class LinesService {
	  async getDialogIdByUserCode(userCode) {
	    const result = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.linesDialogGet, {
	      USER_CODE: userCode
	    }).catch(errorResult => {
	      console.error('LinesService: error getting dialog id', errorResult.error());
	    });
	    const {
	      dialog_id: dialogId
	    } = result.data();
	    return dialogId;
	  }
	}

	const checkHistoryDialogId = dialogId => {
	  return im_v2_lib_utils.Utils.dialog.isLinesHistoryId(dialogId) || im_v2_lib_utils.Utils.dialog.isLinesExternalId(dialogId);
	};
	const prepareHistorySliderLink = dialogId => {
	  const getParams = new URLSearchParams({
	    [im_v2_const.GetParameter.openHistory]: dialogId,
	    [im_v2_const.GetParameter.backgroundType]: 'light',
	    [im_v2_const.GetParameter.legacyMode]: 'Y'
	  });
	  return `${im_v2_const.Path.history}?${getParams.toString()}`;
	};
	const normalizeEntityId = entityId => {
	  if (main_core.Type.isString(entityId)) {
	    return entityId;
	  }
	  if (main_core.Type.isNumber(entityId)) {
	    return entityId.toString();
	  }
	  return '';
	};
	const isEmbeddedModeWithActiveSlider = () => {
	  const sidePanelManager = main_sidepanel.SidePanel.Instance;
	  return im_v2_lib_layout.LayoutManager.getInstance().isEmbeddedMode() && sidePanelManager.getOpenSlidersCount() > 0;
	};
	const openChatInNewTab = ({
	  navigationItem,
	  dialogId,
	  messageId
	}) => {
	  const getParams = new URLSearchParams();
	  const urlParameter = getUrlParameterForNavigation(navigationItem);
	  if (main_core.Type.isStringFilled(dialogId)) {
	    getParams.append(urlParameter, dialogId);
	  }
	  if (messageId > 0) {
	    getParams.append(im_v2_const.GetParameter.openMessage, messageId);
	  }
	  im_v2_lib_utils.Utils.browser.openLink(`${im_v2_const.Path.online}?${getParams.toString()}`);
	};
	const getUrlParameterForNavigation = navigationItem => {
	  var _navigationToGetParam;
	  const navigationToGetParameterMap = {
	    [im_v2_const.NavigationMenuItem.chat]: im_v2_const.GetParameter.openChat,
	    [im_v2_const.NavigationMenuItem.openlines]: im_v2_const.GetParameter.openLines
	  };
	  return (_navigationToGetParam = navigationToGetParameterMap[navigationItem]) != null ? _navigationToGetParam : im_v2_const.GetParameter.openChat;
	};

	const Opener = {
	  async openChat(dialogId = '', messageId = 0) {
	    const preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      return this.openLines(preparedDialogId);
	    }
	    if (isEmbeddedModeWithActiveSlider()) {
	      openChatInNewTab({
	        navigationItem: im_v2_const.NavigationMenuItem.chat,
	        dialogId: preparedDialogId,
	        messageId
	      });
	      return Promise.resolve();
	    }
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.chat,
	      entityId: preparedDialogId
	    };
	    if (messageId > 0) {
	      layoutParams.contextId = messageId;
	    }
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	    return Promise.resolve();
	  },
	  async openChatWithBotContext(dialogId, context) {
	    const preparedDialogId = dialogId.toString();
	    const botContextService = new im_v2_provider_service_bot.BotContextService();
	    botContextService.scheduleContextRequest(preparedDialogId, context);
	    return this.openChat(preparedDialogId);
	  },
	  async forwardEntityToChat(dialogId, entityConfig) {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.chat,
	      entityId: preparedDialogId
	    };
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.forwardEntity, {
	      dialogId,
	      entityConfig
	    });
	    return Promise.resolve();
	  },
	  async openLines(dialogId = '') {
	    let preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      const linesService = new LinesService();
	      preparedDialogId = await linesService.getDialogIdByUserCode(preparedDialogId);
	    }
	    if (isEmbeddedModeWithActiveSlider()) {
	      openChatInNewTab({
	        navigationItem: im_v2_const.NavigationMenuItem.openlines,
	        dialogId: preparedDialogId
	      });
	      return Promise.resolve();
	    }
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const optionOpenLinesV2Activated = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.openLinesV2);
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: optionOpenLinesV2Activated ? im_v2_const.Layout.openlinesV2 : im_v2_const.Layout.openlines,
	      entityId: preparedDialogId
	    });
	  },
	  async openCopilot(dialogId = '', contextId = 0) {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.copilot,
	      entityId: preparedDialogId,
	      contextId
	    });
	  },
	  async openCollab(dialogId = '') {
	    const preparedDialogId = dialogId.toString();
	    if (!im_v2_lib_feature.FeatureManager.collab.isAvailable()) {
	      im_v2_lib_feature.FeatureManager.collab.openFeatureSlider();
	      return null;
	    }
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.collab,
	      entityId: preparedDialogId
	    });
	  },
	  async openChannel(dialogId = '') {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.channel,
	      entityId: preparedDialogId
	    });
	  },
	  async openTaskComments(dialogId = '', messageId = 0) {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.taskComments,
	      entityId: preparedDialogId
	    };
	    if (messageId > 0) {
	      layoutParams.contextId = messageId;
	    }
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	  },
	  openHistory(dialogId = '') {
	    if (im_v2_lib_utils.Utils.dialog.isDialogId(dialogId)) {
	      return this.openChat(dialogId);
	    }
	    if (!checkHistoryDialogId(dialogId)) {
	      return Promise.reject();
	    }
	    const sliderLink = prepareHistorySliderLink(dialogId);
	    BX.SidePanel.Instance.open(sliderLink, {
	      width: im_v2_lib_utils.Utils.dialog.isLinesExternalId(dialogId) ? 700 : 1000,
	      allowChangeHistory: false,
	      allowChangeTitle: false,
	      cacheable: false
	    });
	    return Promise.resolve();
	  },
	  async openNotifications() {
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.notification
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenNotifications);
	    return Promise.resolve();
	  },
	  async openRecentSearch() {
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.chat
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.recent.openSearch);
	    return Promise.resolve();
	  },
	  async openSettings(sectionName) {
	    im_v2_lib_logger.Logger.warn('Slider: openSettings', sectionName);
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.settings,
	      entityId: sectionName
	    });
	    return Promise.resolve();
	  },
	  openConference(code = '') {
	    im_v2_lib_logger.Logger.warn('Slider: openConference', code);
	    if (!im_v2_lib_utils.Utils.conference.isValidCode(code)) {
	      return Promise.reject();
	    }
	    const url = im_v2_lib_utils.Utils.conference.getUrlByCode(code);
	    im_v2_lib_utils.Utils.browser.openLink(url, im_v2_lib_utils.Utils.conference.getWindowNameByCode(code));
	    return Promise.resolve();
	  },
	  async openChatCreation(chatType, params = {}) {
	    var _params$preselectedMe, _params$includeCurren, _params$ownerId;
	    im_v2_lib_logger.Logger.warn('Slider: openChatCreation', chatType);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setPreselectedMembers((_params$preselectedMe = params.preselectedMembers) != null ? _params$preselectedMe : []);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setIncludeCurrentUser((_params$includeCurren = params.includeCurrentUser) != null ? _params$includeCurren : true);
	    im_v2_lib_createChat.CreateChatManager.getInstance().setOwnerId((_params$ownerId = params.ownerId) != null ? _params$ownerId : null);
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.createChat,
	      entityId: chatType
	    };
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	  },
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Slider: onStartVideoCall', dialogId, withVideo);
	    if (!im_v2_lib_utils.Utils.dialog.isDialogId(dialogId)) {
	      im_v2_lib_logger.Logger.error('Slider: onStartVideoCall - dialogId is not correct', dialogId);
	      return false;
	    }
	    im_v2_lib_call.CallManager.getInstance().startCall(dialogId, withVideo);
	    return Promise.resolve();
	  },
	  startPhoneCall(number, params) {
	    im_v2_lib_logger.Logger.warn('Slider: startPhoneCall', number, params);
	    void im_v2_lib_phone.PhoneManager.getInstance().startCall(number, params);
	    return Promise.resolve();
	  },
	  startCallList(callListId, params) {
	    im_v2_lib_logger.Logger.warn('Slider: startCallList', callListId, params);
	    im_v2_lib_phone.PhoneManager.getInstance().startCallList(callListId, params);
	    return Promise.resolve();
	  },
	  openNewTab(path) {
	    if (im_v2_lib_desktopApi.DesktopApi.isChatTab() && im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.openNewTab.id)) {
	      im_v2_lib_desktopApi.DesktopApi.createImTab(`${path}&${im_v2_const.GetParameter.desktopChatTabMode}=Y`);
	    } else {
	      im_v2_lib_utils.Utils.browser.openLink(path);
	    }
	  },
	  async openNavigationItem(payload) {
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    im_v2_lib_navigation.NavigationManager.open({
	      id: payload.id.toString(),
	      entityId: normalizeEntityId(payload.entityId),
	      target: payload.target
	    });
	  },
	  isChatOpened(dialogId) {
	    const currentLayout = im_v2_lib_layout.LayoutManager.getInstance().getLayout();
	    if (!im_v2_lib_layout.LayoutManager.getInstance().isChatLayout(currentLayout.name)) {
	      return false;
	    }
	    return currentLayout.entityId === dialogId;
	  }
	};

	exports.Opener = Opener;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX,BX.SidePanel,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=opener.bundle.js.map
