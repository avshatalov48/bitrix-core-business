{"version":3,"file":"message-list.bundle.map.js","names":["this","BX","Messenger","v2","exports","im_v2_lib_quote","im_v2_component_animation","im_v2_lib_copilot","im_v2_lib_messageComponentManager","im_public","im_v2_lib_channel","im_v2_lib_menu","im_v2_lib_parser","im_v2_lib_entityCreator","im_v2_provider_service","im_v2_lib_market","im_v2_lib_utils","im_v2_lib_permission","im_v2_lib_confirm","ui_notification","main_polyfill_intersectionobserver","im_v2_component_elements","main_core","main_core_events","im_v2_lib_analytics","im_v2_lib_feature","im_v2_application_core","im_v2_const","im_v2_lib_dateFormatter","im_v2_component_message_file","im_v2_component_message_default","im_v2_component_message_callInvite","im_v2_component_message_deleted","im_v2_component_message_unsupported","im_v2_component_message_smile","im_v2_component_message_system","im_v2_component_message_chatCreation","im_v2_component_message_copilot_creation","im_v2_component_message_copilot_answer","im_v2_component_message_copilot_addedUsers","im_v2_component_message_support_vote","im_v2_component_message_support_sessionNumber","im_v2_component_message_support_chatCreation","im_v2_component_message_conferenceCreation","im_v2_component_message_supervisor_updateFeature","im_v2_component_message_supervisor_enableFeature","im_v2_component_message_sign","im_v2_component_message_checkIn","im_v2_component_message_ownChatCreation","im_v2_component_message_zoomInvite","im_v2_component_message_generalChatCreation","im_v2_component_message_generalChannelCreation","im_v2_component_message_channelCreation","im_v2_component_message_call","DialogLoader","name","props","fullHeight","type","Boolean","default","data","methods","loc","phraseCode","$Bitrix","Loc","getMessage","template","AvatarMenu","BaseMenu","constructor","super","id","permissionManager","PermissionManager","getInstance","getMenuOptions","className","getMenuClassName","angle","offsetLeft","getMenuItems","getMentionItem","getSendItem","getProfileItem","getKickItem","text","onclick","EventEmitter","emit","EventType","textarea","insertMention","mentionText","context","user","mentionReplacement","Utils","getMentionBbCode","dialogId","dialog","isMentionSymbol","menuInstance","close","ChatType","openChat","isBot","href","getProfileLink","canKick","canPerformAction","ChatActionType","kick","async","userChoice","showKickUserConfirm","chatService","ChatService","kickUserFromChat","isUser","store","getters","bot","_isOwnMessage","babelHelpers","classPrivateFieldLooseKey","_isDeletedMessage","_getMessageFile","_isForwardedMessage","_onDelete","_isDeletionCancelled","MessageMenu","Object","defineProperty","value","_isDeletionCancelled2","_onDelete2","_isForwardedMessage2","_getMessageFile2","_isDeletedMessage2","_isOwnMessage2","diskService","DiskService","marketManager","MarketManager","getReplyItem","getCopyItem","getCopyLinkItem","getCopyFileItem","getPinItem","getForwardItem","getDelimiter","getMarkItem","getFavoriteItem","getCreateItem","getDownloadFileItem","getSaveToDisk","getEditItem","getDeleteItem","replyMessage","messageId","classPrivateFieldLooseBase","Type","isString","showForwardPopup","trim","length","textToCopy","Parser","prepareCopy","copyToClipboard","UI","Notification","Center","notify","content","_BX$clipboard","getMessageLink","clipboard","copy","files","_BX$clipboard2","prepareCopyFile","canPin","pinMessage","isPinned","chatId","messageService","MessageService","unpinMessage","isInFavorite","menuItemText","removeMessageFromFavorite","addMessageToFavorite","canUnread","viewed","isMarked","markedId","markMessage","items","getCreateTaskItem","getCreateMeetingItem","getMarketItems","entityCreator","EntityCreator","createTaskForMessage","createMeetingForMessage","editMessage","canDeleteOthersMessage","deleteOthersMessage","bind","placements","getAvailablePlacementsByType","PlacementType","contextMenu","marketMenuItem","push","forEach","placement","title","openSlider","itemLimit","slice","file","html","createDownloadLink","urlDownload","save","then","delimiter","authorId","Core","getUserId","isDeleted","isStringFilled","forward","Analytics","messageDelete","onClickDelete","deleteMessage","ChannelManager","isChannel","confirmResult","showDeleteChannelPostConfirm","onCancel","_dialogId","_observer","_initObserver","_sendVisibleEvent","_sendNotVisibleEvent","_getThreshold","_getMessageIdFromElement","ObserverManager","_getMessageIdFromElement2","_getThreshold2","_sendNotVisibleEvent2","_sendVisibleEvent2","_initObserver2","writable","observeMessage","messageElement","observe","unobserveMessage","unobserve","IntersectionObserver","entries","entry","target","rootBounds","messageIsFullyVisible","isIntersecting","intersectionRatio","messageTakesHalfOfViewport","intersectionRect","height","threshold","onMessageIsVisible","onMessageIsNotVisible","arrayWithZeros","Array","from","fill","map","zero","index","Number","dataset","DateGroupTitle","String","required","DateGroup","components","item","computed","BlockType","DialogBlockType","dateGroup","AuthorGroup","MessageAvatar","contextDialogId","emits","AvatarSize","authorGroup","firstMessageIdInAuthorGroup","messages","onAvatarClick","event","$emit","avatar","avatarId","$event","NewMessagesBlock","MarkedMessagesBlock","defaultMessages","EmptyState","onMessageClick","insertText","HistoryLimitBanner","noMessages","FeatureManager","chatHistory","getLimitTitle","subtitle","getLimitSubtitle","buttonText","getLearnMoreText","mounted","sendAnalytics","onButtonClick","historyLimit","onDialogBannerClick","openFeatureSlider","onDialogLimitExceeded","_getAvatarConfig","_getMessageType","_checkIfAvatarIsNeeded","BlockManager","_checkIfAvatarIsNeeded2","_getMessageType2","_getAvatarConfig2","getAuthorBlock","message","userId","messageType","getMarkedBlock","markedMessages","getNewMessagesBlock","newMessages","isNeeded","toString","MessageType","system","self","opponent","isSystem","isSelf","alignment","getStore","Settings","appearance","DialogAlignment","center","_blockManager","_collection","_currentDateTitles","_markedIndicatorInserted","_lastDateItems","_lastAuthorId","_lastAuthorItems","_clearLastAuthor","Collection","_clearLastAuthor2","Set","get","hasDateTitle","dateTitle","has","addDateGroup","add","addAuthorGroup","addMessage","addMarkedIndicator","addNewMessagesIndicator","getLastAuthorId","_getLocalShortDate","DateManager","_getLocalShortDate2","cachedDateGroups","getDateTitle","date","shortDate","DateFormatter","formatByTemplate","DateTemplate","timestampWithTimezoneOffset","getTime","getTimezoneOffset","localDateInJSON","Date","toJSON","INDEX_BETWEEN_DATE_AND_TIME","_setInitialValues","_handleMarkedMessageId","_getLastReadMessageId","_getDialog","CollectionManager","_getDialog2","_getLastReadMessageId2","_handleMarkedMessageId2","_setInitialValues2","firstIteration","dateManager","formatMessageCollection","messageCollection","collection","markedMessageId","isLastMessage","lastReadMessageId","inited","formatAuthorGroup","blockManager","lastMessageId","lastReadId","MessageComponents","DefaultMessage","FileMessage","SmileMessage","CallInviteMessage","DeletedMessage","SystemMessage","UnsupportedMessage","ChatCreationMessage","OwnChatCreationMessage","ChatCopilotCreationMessage","CopilotMessage","SupportVoteMessage","SupportSessionNumberMessage","SupportChatCreationMessage","ConferenceCreationMessage","ZoomInviteMessage","CheckInMessage","SupervisorUpdateFeatureMessage","SupervisorEnableFeatureMessage","ChatCopilotAddedUsersMessage","SignMessage","GeneralChatCreationMessage","GeneralChannelCreationMessage","ChannelCreationMessage","CallMessage","MessageList","directives","element","binding","instance","observer","beforeUnmount","DialogStatus","FadeAnimation","messageMenuClass","Function","windowFocused","messageMenuIsActiveForId","$store","dialogInited","formattedCollection","getCollectionManager","isHistoryLimitExceeded","isAvailable","tariffRestrictions","showDialogStatus","some","showEmptyState","created","initContextMenu","initCollectionManager","initObserverManager","subscribeToEvents","unsubscribeFromEvents","subscribe","onClickMessageContextMenu","onMessageContextMenuClick","unsubscribe","insertTextQuote","Quote","prepareQuoteText","withNewLine","replace","openReplyPanel","needToShowAvatarMenuFor","isCurrentUser","isBotChat","params","openAvatarMenu","key","isAltOrOption","copilotManager","CopilotManager","isCopilotBot","avatarMenu","openMenu","currentTarget","eventData","openMessageMenu","getData","isCombination","isCmdOrCtrl","messageMenu","browser","waitForSelectionToUpdate","selection","window","getSelection","showQuoteButton","MessageMenuClass","events","onCloseMenu","getMessageComponentName","MessageComponentManager","getName","collectionManager","Component","Lib","Animation","Service","Elements","Event","Application","Const","Message"],"sources":["message-list.bundle.js"],"mappings":"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"}