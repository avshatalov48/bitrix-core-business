/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_const) {
	'use strict';

	const ThemeType = Object.freeze({
	  light: 'light',
	  dark: 'dark'
	});
	const ThemePattern = Object.freeze({
	  default: 'default',
	  aiAssistant: 'ai-assistant'
	});
	const SelectableBackground = Object.freeze({
	  // dark ones
	  1: {
	    color: '#9fcfff',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  2: {
	    color: '#81d8bf',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  3: {
	    color: '#7fadd1',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  4: {
	    color: '#7a90b6',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  5: {
	    color: '#5f9498',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  6: {
	    color: '#799fe1',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  // light ones
	  7: {
	    color: '#cfeefa',
	    type: ThemeType.light,
	    pattern: ThemePattern.default
	  },
	  9: {
	    color: '#efded3',
	    type: ThemeType.light,
	    pattern: ThemePattern.default
	  },
	  11: {
	    color: '#eff4f6',
	    type: ThemeType.light,
	    pattern: ThemePattern.default
	  }
	});

	// should be synced with \Bitrix\Im\V2\Chat\Background\BackgroundId, can potentially be used externally
	const SpecialBackgroundId = {
	  collab: 'collab',
	  martaAI: 'martaAI',
	  copilot: 'copilot'
	};
	const COPILOT_BACKGROUND_ID = 4;
	const SpecialBackground = {
	  [SpecialBackgroundId.collab]: {
	    color: '#76c68b',
	    type: ThemeType.dark,
	    pattern: ThemePattern.default
	  },
	  [SpecialBackgroundId.martaAI]: {
	    color: '#0277ff',
	    type: ThemeType.dark,
	    pattern: ThemePattern.aiAssistant
	  },
	  [SpecialBackgroundId.copilot]: SelectableBackground[COPILOT_BACKGROUND_ID]
	};
	const ImageFileByBackgroundId = {
	  [SpecialBackgroundId.collab]: 'collab-v2',
	  [SpecialBackgroundId.martaAI]: 'ai-assistant',
	  [SpecialBackgroundId.copilot]: COPILOT_BACKGROUND_ID.toString()
	};

	const IMAGE_FOLDER_PATH = '/bitrix/js/im/images/chat-v2-background';
	const BackgroundPatternColor = Object.freeze({
	  white: 'white',
	  gray: 'gray'
	});
	const ThemeManager = {
	  isLightTheme() {
	    const selectedBackgroundId = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.appearance.background);
	    const selectedColorScheme = SelectableBackground[selectedBackgroundId];
	    return (selectedColorScheme == null ? void 0 : selectedColorScheme.type) === ThemeType.light;
	  },
	  isDarkTheme() {
	    const selectedBackgroundId = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.appearance.background);
	    const selectedColorScheme = SelectableBackground[selectedBackgroundId];
	    return (selectedColorScheme == null ? void 0 : selectedColorScheme.type) === ThemeType.dark;
	  },
	  getCurrentBackgroundStyle(dialogId) {
	    const backgroundId = resolveBackgroundId(dialogId);
	    return this.getBackgroundStyleById(backgroundId);
	  },
	  getBackgroundStyleById(backgroundId) {
	    var _ImageFileByBackgroun;
	    const backgroundsList = {
	      ...SelectableBackground,
	      ...SpecialBackground
	    };
	    const colorScheme = backgroundsList[backgroundId];
	    if (!colorScheme) {
	      return {};
	    }
	    const patternColor = colorScheme.type === ThemeType.light ? BackgroundPatternColor.gray : BackgroundPatternColor.white;
	    const patternType = colorScheme.pattern;
	    const fileName = (_ImageFileByBackgroun = ImageFileByBackgroundId[backgroundId]) != null ? _ImageFileByBackgroun : backgroundId;
	    const patternImage = `url('${IMAGE_FOLDER_PATH}/pattern-${patternColor}-${patternType}.svg')`;
	    const highlightImage = `url('${IMAGE_FOLDER_PATH}/${fileName}.png')`;
	    return {
	      backgroundColor: colorScheme.color,
	      backgroundImage: `${patternImage}, ${highlightImage}`,
	      backgroundPosition: 'top right, center',
	      backgroundRepeat: 'repeat, no-repeat',
	      backgroundSize: 'auto, cover'
	    };
	  }
	};

	/** Background selection priority:
	 * 1. If there is no dialog context: user selected background (from user settings)
	 * 2. Background by chat type (collab/copilot/aiAssistant)
	 * 3. Chat background (from chat fields)
	 * 4. Bot background (from bot fields)
	 * 5. User selected background (from user settings)
	 */
	const resolveBackgroundId = dialogId => {
	  const userBackground = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.appearance.background);
	  if (!main_core.Type.isStringFilled(dialogId)) {
	    return userBackground;
	  }
	  const chatType = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true).type;
	  if (chatType === im_v2_const.ChatType.collab) {
	    return SpecialBackgroundId.collab;
	  }
	  if (chatType === im_v2_const.ChatType.copilot) {
	    return SpecialBackgroundId.copilot;
	  }
	  const isAiAssistant = im_v2_application_core.Core.getStore().getters['users/bots/isAiAssistant'](dialogId);
	  if (isAiAssistant) {
	    return SpecialBackgroundId.martaAI;
	  }
	  const chatBackground = im_v2_application_core.Core.getStore().getters['chats/getBackgroundId'](dialogId);
	  const botBackground = im_v2_application_core.Core.getStore().getters['users/bots/getBackgroundId'](dialogId);
	  if (SpecialBackgroundId[chatBackground]) {
	    return SpecialBackgroundId[chatBackground];
	  }
	  if (SpecialBackgroundId[botBackground]) {
	    return SpecialBackgroundId[botBackground];
	  }
	  return userBackground;
	};

	exports.SelectableBackground = SelectableBackground;
	exports.SpecialBackground = SpecialBackgroundId;
	exports.ThemeType = ThemeType;
	exports.ThemeManager = ThemeManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=theme.bundle.js.map
