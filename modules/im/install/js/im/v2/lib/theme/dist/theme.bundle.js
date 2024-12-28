/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const ThemeType = Object.freeze({
	  light: 'light',
	  dark: 'dark'
	});
	const SelectableBackground = Object.freeze({
	  // dark ones
	  1: {
	    color: '#9fcfff',
	    type: ThemeType.dark
	  },
	  2: {
	    color: '#81d8bf',
	    type: ThemeType.dark
	  },
	  3: {
	    color: '#7fadd1',
	    type: ThemeType.dark
	  },
	  4: {
	    color: '#7a90b6',
	    type: ThemeType.dark
	  },
	  5: {
	    color: '#5f9498',
	    type: ThemeType.dark
	  },
	  6: {
	    color: '#799fe1',
	    type: ThemeType.dark
	  },
	  // light ones
	  7: {
	    color: '#cfeefa',
	    type: ThemeType.light
	  },
	  9: {
	    color: '#efded3',
	    type: ThemeType.light
	  },
	  11: {
	    color: '#eff4f6',
	    type: ThemeType.light
	  }
	});
	const SpecialBackgroundId = {
	  collab: 'collab-v2'
	};
	const SpecialBackground = {
	  [SpecialBackgroundId.collab]: {
	    color: '#76c68b',
	    type: ThemeType.dark
	  }
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
	  getCurrentBackgroundStyle() {
	    const selectedBackgroundId = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.appearance.background);
	    return this.getBackgroundStyleById(selectedBackgroundId);
	  },
	  getBackgroundStyleById(backgroundId) {
	    const backgroundsList = {
	      ...SelectableBackground,
	      ...SpecialBackground
	    };
	    const colorScheme = backgroundsList[backgroundId];
	    if (!colorScheme) {
	      return {};
	    }
	    const patternColor = colorScheme.type === ThemeType.light ? BackgroundPatternColor.gray : BackgroundPatternColor.white;
	    const patternImage = `url('${IMAGE_FOLDER_PATH}/pattern-${patternColor}.svg')`;
	    const highlightImage = `url('${IMAGE_FOLDER_PATH}/${backgroundId}.png')`;
	    return {
	      backgroundColor: colorScheme.color,
	      backgroundImage: `${patternImage}, ${highlightImage}`,
	      backgroundPosition: 'top right, center',
	      backgroundRepeat: 'repeat, no-repeat',
	      backgroundSize: 'auto, cover'
	    };
	  }
	};

	exports.SelectableBackground = SelectableBackground;
	exports.SpecialBackground = SpecialBackgroundId;
	exports.ThemeType = ThemeType;
	exports.ThemeManager = ThemeManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=theme.bundle.js.map
