{"version":3,"file":"layout.bundle.map.js","names":["this","BX","Messenger","v2","exports","main_core_events","im_v2_application_core","im_v2_lib_analytics","im_v2_lib_localStorage","im_v2_const","im_v2_lib_logger","im_v2_lib_channel","im_v2_lib_access","im_v2_lib_feature","TypesWithoutContext","Set","ChatType","comment","LayoutsWithoutLastOpenedElement","Layout","channel","name","market","_instance","babelHelpers","classPrivateFieldLooseKey","_lastOpenedElement","_deleteLastOpenedElement","_onGoToMessageContext","_onDesktopReload","_sendAnalytics","_isSameChat","_handleSameChatReopen","_handleContextAccess","_handleLayoutLeave","_getChat","LayoutManager","static","classPrivateFieldLooseBase","getInstance","constructor","Object","defineProperty","value","_getChat2","_handleLayoutLeave2","_handleContextAccess2","_handleSameChatReopen2","_isSameChat2","_sendAnalytics2","_onDesktopReload2","_onGoToMessageContext2","_deleteLastOpenedElement2","writable","EventEmitter","subscribe","EventType","dialog","goToMessageContext","bind","desktop","onReload","async","config","contextId","hasAccess","Promise","resolve","entityId","setLastOpenedElement","Core","getStore","dispatch","getLayout","getters","saveCurrentLayout","currentLayout","LocalStorageManager","set","LocalStorageKey","layoutConfig","restoreLastLayout","get","Logger","warn","remove","setLayout","getLastOpenedElement","layoutName","_babelHelpers$classPr","has","clearLayoutEntityId","currentLayoutName","isChatContextAvailable","dialogId","type","destroy","unsubscribe","event","messageId","getData","isCopilotLayout","copilot","chat","Analytics","onOpenCopilotTab","onOpenTab","sameLayout","sameEntityId","emit","errorCode","AccessManager","checkMessageAccess","AccessErrorCode","messageAccessDeniedByTariff","historyLimit","onGoToContextLimitExceeded","FeatureManager","chatHistory","openFeatureSlider","isChannelOpened","ChannelManager","isChannel","closeComments","Lib","Event","Application","Const"],"sources":["layout.bundle.js"],"mappings":"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"}