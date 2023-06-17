this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const ThemeType = Object.freeze({
	  light: 'light',
	  dark: 'dark'
	});
	const ThemeFontColor = Object.freeze({
	  white: '#fff',
	  gray: 'gray'
	});
	const BackgroundColorScheme = Object.freeze({
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
	  8: {
	    color: '#c5ecde',
	    type: ThemeType.light
	  },
	  9: {
	    color: '#efded3',
	    type: ThemeType.light
	  },
	  10: {
	    color: '#dff0bc',
	    type: ThemeType.light
	  },
	  11: {
	    color: '#eff4f6',
	    type: ThemeType.light
	  },
	  12: {
	    color: '#f5f3e1',
	    type: ThemeType.light
	  }
	});

	const IMAGE_FOLDER_PATH = '/bitrix/js/im/images/chat-v2-background';
	const BackgroundManager = {
	  getCurrentBackgroundStyle() {
	    const selectedBackgroundId = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.dialog.background);
	    return this.getBackgroundStyleById(selectedBackgroundId);
	  },
	  getBackgroundStyleById(backgroundId) {
	    const colorScheme = BackgroundColorScheme[backgroundId];
	    if (!colorScheme) {
	      return {};
	    }
	    const patternImage = `url('${IMAGE_FOLDER_PATH}/pattern-${colorScheme.patternColor}.svg')`;
	    const highlightImage = `url('${IMAGE_FOLDER_PATH}/${backgroundId}.png')`;
	    return {
	      backgroundColor: colorScheme.color,
	      backgroundImage: `${highlightImage}, ${patternImage}`,
	      backgroundPosition: 'center, top right',
	      backgroundRepeat: 'no-repeat, repeat',
	      backgroundSize: 'cover, auto'
	    };
	  }
	};

	exports.BackgroundColorScheme = BackgroundColorScheme;
	exports.ThemeType = ThemeType;
	exports.ThemeFontColor = ThemeFontColor;
	exports.BackgroundManager = BackgroundManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=background.bundle.js.map
