{"version":3,"file":"copilot-list.bundle.map.js","names":["this","BX","Messenger","v2","Component","exports","im_v2_lib_draft","main_date","im_v2_lib_utils","im_v2_lib_parser","im_v2_component_elements","im_v2_lib_dateFormatter","im_v2_application_core","im_v2_const","im_v2_lib_logger","im_v2_provider_service","main_core","im_public","im_v2_lib_menu","MessageText","name","components","MessageAvatar","props","item","type","Object","required","data","computed","AvatarSize","recentItem","dialog","$store","getters","dialogId","user","message","showLastMessage","Settings","recent","hiddenMessageText","$Bitrix","Loc","getMessage","isLastMessageAuthor","authorId","Core","getUserId","lastMessageAuthorAvatar","authorDialog","avatar","lastMessageAuthorAvatarStyle","backgroundImage","messageText","formattedText","Parser","purifyRecent","formattedMessageText","SPLIT_INDEX","Utils","text","insertUnseenWhitespace","preparedDraftContent","phrase","loc","PLACEHOLDER_LENGTH","length","prefix","slice","formattedDraftText","purify","draft","showIconIfEmptyText","methods","phraseCode","replacements","template","CopilotItem","ChatAvatar","ChatTitle","formattedDate","formatDate","date","formattedCounter","counter","toString","layout","isChatSelected","Layout","copilot","entityId","isChatMuted","isMuted","muteList","find","element","Boolean","isSomeoneTyping","writingList","showPinnedIcon","pinned","unread","showCounter","wrapClasses","itemClasses","DateFormatter","formatByTemplate","DateTemplate","CopilotRecentService","RecentService","getQueryParams","firstPage","ONLY_COPILOT","LIMIT","itemsPerPage","LAST_MESSAGE_DATE","lastMessageDate","GET_ORIGINAL_TEXT","PARSE_TEXT","getModelSaveMethod","getCollection","getStore","getExtractorOptions","withBirthdays","hideChat","Logger","warn","dispatch","id","chatIsOpened","openCopilot","getRestClient","callMethod","RestMethod","imRecentHide","DIALOG_ID","catch","error","console","CopilotRecentMenu","RecentMenu","getMenuItems","getPinMessageItem","getHideItem","getLeaveItem","getOpenItem","onclick","context","menuInstance","close","getRecentService","service","CopilotList","LoadingState","ListLoadingState","emits","isLoading","isLoadingNextPage","collection","sortedItems","sort","a","b","firstDate","secondDate","pinnedItems","filter","generalItems","isEmptyCollection","async","contextMenuManager","loadFirstPage","CopilotDraftManager","getInstance","initDraftHistory","beforeUnmount","destroy","event","dom","isOneScreenRemaining","target","hasMoreItemsToLoad","loadNextPage","onClick","$emit","onRightClick","preventDefault","openMenu","currentTarget","List","Lib","Main","Elements","Application","Const","Service"],"sources":["copilot-list.bundle.js"],"mappings":"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"}