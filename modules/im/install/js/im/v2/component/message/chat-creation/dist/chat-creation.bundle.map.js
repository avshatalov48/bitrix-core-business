{"version":3,"file":"chat-creation.bundle.map.js","names":["this","BX","Messenger","v2","Component","exports","im_public","im_v2_component_elements","im_v2_component_entitySelector","im_v2_component_message_base","im_v2_lib_analytics","im_v2_lib_call","ui_vue3_directives_hint","BUTTON_COLOR","ChatCreationMessage","name","directives","hint","components","ButtonComponent","Button","AddToChat","BaseMessage","props","item","type","Object","required","dialogId","String","data","showAddToChatPopup","computed","ButtonSize","ButtonIcon","buttonColorScheme","backgroundColor","borderColor","iconColor","textColor","hoverColor","message","chatId","dialog","$store","getters","hasActiveCurrentCall","CallManager","getInstance","hasActiveAnotherCall","isActive","chatCanBeCalled","userLimit","getCallUserLimit","isChatUserLimitExceeded","hintContent","text","loc","popupOptions","bindOptions","position","angle","targetContainer","document","body","offsetLeft","offsetTop","methods","phraseCode","replacements","$Bitrix","Loc","getMessage","onCallButtonClick","Analytics","onStartCallClick","AnalyticsType","groupCall","section","AnalyticsSection","chatWindow","subSection","AnalyticsSubSection","window","element","AnalyticsElement","initialBanner","startVideoCall","onInviteButtonClick","template","Message","Lib","Elements","EntitySelector","Vue3","Directives"],"sources":["chat-creation.bundle.js"],"mappings":"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"}