this.BX = this.BX || {};
(function (exports) {
	'use strict';

	class ThemePicker {
	  static init(userId, userTheme) {
	    this.userId = userId;
	    this.userTheme = userTheme;
	  }
	  static applyGroupTheme(groupId, theme) {
	    this.getIntranetPicker().entityId = groupId;
	    this.getIntranetPicker().entityType = this.ENTITY_TYPE_SONET_GROUP;
	    this.getIntranetPicker().themes.push(theme);
	    this.getIntranetPicker().applyTheme(theme.id);
	    this.getIntranetPicker().setThemeId(theme.id);
	  }
	  static applyUserTheme() {
	    this.getIntranetPicker().entityId = this.userId;
	    this.getIntranetPicker().entityType = this.ENTITY_TYPE_USER;
	    this.getIntranetPicker().themes.push(this.userTheme);
	    this.getIntranetPicker().applyTheme(this.userTheme.id);
	    this.getIntranetPicker().setThemeId(this.userTheme.id);
	  }
	  static getIntranetPicker() {
	    return top.BX.Intranet.Bitrix24.ThemePicker.Singleton;
	  }
	}
	ThemePicker.ENTITY_TYPE_USER = 'USER';
	ThemePicker.ENTITY_TYPE_SONET_GROUP = 'SONET_GROUP';

	exports.ThemePicker = ThemePicker;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {})));
//# sourceMappingURL=theme-picker.bundle.js.map
