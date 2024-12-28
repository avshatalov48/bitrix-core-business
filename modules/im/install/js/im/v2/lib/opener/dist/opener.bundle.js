/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_feature,main_core_events,im_v2_const,im_v2_lib_call,im_v2_lib_desktopApi,im_v2_lib_layout,im_v2_lib_logger,im_v2_lib_phone,im_v2_lib_utils,im_v2_lib_slider,im_v2_provider_service) {
	'use strict';

	const Opener = {
	  async openChat(dialogId = '', messageId = 0) {
	    const preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      return this.openLines(preparedDialogId);
	    }
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.chat.name,
	      entityId: preparedDialogId
	    };
	    if (messageId > 0) {
	      layoutParams.contextId = messageId;
	    }
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenChat, {
	      dialogId: preparedDialogId
	    });
	    return Promise.resolve();
	  },
	  async forwardEntityToChat(dialogId, entityConfig) {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.chat.name,
	      entityId: preparedDialogId
	    };
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout(layoutParams);
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenChat, {
	      dialogId: preparedDialogId
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.forwardEntity, {
	      dialogId,
	      entityConfig
	    });
	    return Promise.resolve();
	  },
	  async openLines(dialogId = '') {
	    let preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      const linesService = new im_v2_provider_service.LinesService();
	      preparedDialogId = await linesService.getDialogIdByUserCode(preparedDialogId);
	    }
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const optionOpenLinesV2Activated = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.openLinesV2);
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: optionOpenLinesV2Activated ? im_v2_const.Layout.openlinesV2.name : im_v2_const.Layout.openlines.name,
	      entityId: preparedDialogId
	    });
	  },
	  async openCopilot(dialogId = '', contextId = 0) {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.copilot.name,
	      entityId: preparedDialogId,
	      contextId
	    });
	  },
	  async openCollab(dialogId = '') {
	    const preparedDialogId = dialogId.toString();
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    return im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.collab.name,
	      entityId: preparedDialogId
	    });
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
	      name: im_v2_const.Layout.notification.name
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenNotifications);
	    return Promise.resolve();
	  },
	  async openRecentSearch() {
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.chat.name
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.recent.openSearch);
	    return Promise.resolve();
	  },
	  async openSettings(sectionName) {
	    im_v2_lib_logger.Logger.warn('Slider: openSettings', sectionName);
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    await im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.settings.name,
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
	  async openChatCreation(chatType) {
	    im_v2_lib_logger.Logger.warn('Slider: openChatCreation', chatType);
	    await im_v2_lib_slider.MessengerSlider.getInstance().openSlider();
	    const layoutParams = {
	      name: im_v2_const.Layout.createChat.name,
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
	  }
	};
	const checkHistoryDialogId = dialogId => {
	  return im_v2_lib_utils.Utils.dialog.isLinesHistoryId(dialogId) || im_v2_lib_utils.Utils.dialog.isLinesExternalId(dialogId);
	};
	const prepareHistorySliderLink = dialogId => {
	  const getParams = new URLSearchParams({
	    [im_v2_const.GetParameter.openHistory]: dialogId,
	    [im_v2_const.GetParameter.backgroundType]: 'light'
	  });
	  return `/desktop_app/history.php?${getParams.toString()}`;
	};

	exports.Opener = Opener;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service));
//# sourceMappingURL=opener.bundle.js.map
