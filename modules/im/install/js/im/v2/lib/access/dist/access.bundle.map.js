{"version":3,"file":"access.bundle.map.js","names":["this","BX","Messenger","v2","exports","im_v2_lib_rest","main_core","main_popup","im_v2_const","im_v2_lib_feature","AccessErrorCode","accessDenied","chatNotFound","messageNotFound","messageAccessDenied","messageAccessDeniedByTariff","AccessService","async","messageId","payload","data","runAction","RestMethod","imV2AccessCheck","errors","handleAccessError","Promise","resolve","hasAccess","error","availableCodes","Object","values","includes","code","console","errorCode","_","t","_t","_t2","_popupInstance","babelHelpers","classPrivateFieldLooseKey","_cache","_getPopupConfig","_getContainer","_getButtonContainer","_bindEvents","_unbindEvents","HistoryLimitPopup","constructor","defineProperty","value","_unbindEvents2","_bindEvents2","_getButtonContainer2","_getContainer2","_getPopupConfig2","writable","Cache","MemoryCache","classPrivateFieldLooseBase","Popup","show","close","destroy","id","PopupType","messageHistoryLimit","className","closeIcon","autoHide","closeByEsc","animation","overlay","padding","content","events","onPopupDestroy","remember","container","Tag","render","FeatureManager","chatHistory","getLimitTitle","getLimitSubtitle","Dom","append","getLearnMoreText","Event","bind","openFeatureSlider","unbindAll","AccessManager","checkMessageAccess","showHistoryLimitPopup","limitPopup","Lib","Main","Const"],"sources":["access.bundle.js"],"mappings":"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"}