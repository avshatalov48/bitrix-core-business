{"version":3,"file":"desktop-api.bundle.map.js","names":["this","BX","Messenger","v2","exports","im_v2_lib_utils","im_v2_lib_logger","main_core","im_v2_const","main_core_events","lifecycleFunctions","isDesktop","Type","isObject","window","BXDesktopSystem","restart","_BXDesktopSystem","getApiVersion","Restart","shutdown","_BXDesktopSystem2","Shutdown","DesktopFeature","mask","id","version","accountManagement","openNewTab","openPage","versionFunctions","majorVersion","minorVersion","buildVersion","apiVersion","GetProperty","isFeatureEnabled","code","_window$BXDesktopSyst","Boolean","FeatureEnabled","isFeatureSupported","isFeatureSupportedInVersion","eventHandlers","eventFunctions","subscribe","eventName","handler","preparedHandler","event","_event$detail","params","detail","apply","push","Event","bind","unsubscribe","isFunction","isArrayFilled","forEach","eventHandler","unbind","emit","mainWindow","opener","top","allWindows","BXWindows","_window$BXDesktopWind","name","BXDesktopWindow","DispatchCustomEvent","emitToMainWindow","_mainWindow$BXDesktop","_mainWindow$BXDesktop2","GetMainWindow","DesktopSettingsKey","smoothing","smoothing_v2","telemetry","sliderBindingsStatus","settingsFunctions","getCameraSmoothingStatus","getCustomSetting","setCameraSmoothingStatus","status","preparedStatus","setCustomSetting","isTwoWindowMode","IsTwoWindowsMode","setTwoWindowMode","flag","_BXDesktopSystem3","V10","V8","getAutostartStatus","_BXDesktopSystem4","setAutostartStatus","_BXDesktopSystem5","SetProperty","getTelemetryStatus","setTelemetryStatus","value","_BXDesktopSystem6","StoreSettings","defaultValue","_BXDesktopSystem7","QuerySettings","windowFunctions","isChatWindow","settings","Extension","getSettings","get","isChatTab","location","href","includes","setActiveTab","target","_target$BXDesktopSyst","SetActiveTab","showWindow","_target$BXDesktopWind","ExecuteCommand","activateWindow","hideWindow","_target$BXDesktopWind2","closeWindow","_target$BXDesktopWind3","hideLoader","Dom","remove","document","getElementById","reloadWindow","BaseEvent","EventEmitter","EventType","desktop","onReload","reload","findWindow","find","url","options","anchorElement","create","tag","attrs","host","setTimeout","Promise","resolve","skipNativeBrowser","Utils","browser","openLink","createTab","path","preparedPath","CreateTab","createImTab","CreateImTab","createWindow","callback","GetWindow","createTopmostWindow","htmlContent","setWindowPosition","rawParams","_BXDesktopWindow","preparedParams","Object","entries","key","preparedKey","toUpperCase","slice","prepareHtml","html","js","plainHtml","isDomNode","outerHTML","plainJs","ready","isStringFilled","head","replaceAll","setWindowSize","width","height","Width","Height","setMinimumWindowSize","iconFunctions","setCounter","counter","important","preparedCounter","toString","SetIconBadge","SetTabBadge","setBrowserIconBadge","SetBrowserIconBadge","setIconStatus","SetIconStatus","setOfflineIcon","flashIcon","Browser","isWin","FlashIcon","commonFunctions","prepareResourcePath","source","result","URL","origin","legacyFunctions","changeTab","tabId","notificationFunctions","removeNativeNotifications","NotificationRemoveAll","loggerFunctions","writeToLogFile","filename","text","console","error","textPrepared","isString","isNumber","JSON","stringify","Log","printWelcomePrompt","join","osName","isMac","isLinux","promptMessage","Loc","getMessage","Logger","setLogInfo","logFunction","LogInfo","callMaskFunctions","getCallMask","setCallMaskLoadHandlers","setCallMask","maskUrl","backgroundUrl","Set3dAvatar","callBackgroundFunctions","isBlur","toLowerCase","getLimitationBackground","limitation","message","defaultLimitation","enable","limitationType","currentLimitation","articleCode","openArticle","infoHelper","UI","InfoHelper","isOpen","close","show","handleLimitationBackground","limitationObj","handle","getBackgroundImage","_this$getLimitationBa","setCallBackground","promise","currentSource","currentId","accountFunctions","openAddAccountTab","AccountAddForm","deleteAccount","login","AccountDelete","connectAccount","protocol","userLang","AccountConnect","disconnectAccount","AccountDisconnect","getAccountList","AccountList","Login","success","async","ajax","runAction","RestMethod","imV2DesktopLogout","Logout","_BXDesktopSystem8","diskFunctions","startDiskSync","_BXFileStorage","BXFileStorage","SyncPause","compatData","onSyncPause","stopDiskSync","_BXFileStorage2","debugFunctions","openDeveloperTools","OpenDeveloperTools","openLogsFolder","OpenLogsFolder","DesktopApi","Lib","Const"],"sources":["desktop-api.bundle.js"],"mappings":"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"}