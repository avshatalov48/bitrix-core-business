{"version":3,"file":"widgetvueloader.bundle.map.js","names":["this","BX","exports","ui_vue3","main_core","landing_backend","main_loader","_rootNode","babelHelpers","classPrivateFieldLooseKey","_data","_lang","_blockId","_appId","_fetchable","_clickable","_allowedByTariff","_getFrameContent","_getFrameHead","_getAssetsConfigs","_getFrameBody","WidgetVueLoader","constructor","options","Object","defineProperty","value","_getFrameBody2","_getAssetsConfigs2","_getFrameHead2","_getFrameContent2","writable","console","log","classPrivateFieldLooseBase","Type","isString","rootNode","document","querySelector","isObject","data","lang","blockId","Text","toNumber","appId","fetchable","isEditMode","isFunction","Landing","getMode","isBoolean","allowedByTariff","mount","then","srcDoc","frame","createElement","sandbox","srcdoc","runningAppNodes","has","Dom","clean","append","window","addEventListener","event","origin","frameHead","assets","domain","location","protocol","host","head","js","forEach","css","JSON","stringify","lang_additional","extCodes","Backend","getInstance","action","frameContent","Set","Vue3"],"sources":["widgetvueloader.bundle.js"],"mappings":"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"}