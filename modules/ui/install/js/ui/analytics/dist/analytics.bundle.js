this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	function isValidAnalyticsData(analytics) {
	  if (!main_core.Type.isPlainObject(analytics)) {
	    console.error('BX.UI.Analytics: {analytics} must be an object.');
	    return false;
	  }
	  const requiredFields = ['event', 'tool', 'category'];
	  for (const field of requiredFields) {
	    if (!main_core.Type.isStringFilled(analytics[field])) {
	      console.error(`BX.UI.Analytics: The "${field}" property in the "analytics" object must be a non-empty string.`);
	      return false;
	    }
	  }
	  const additionalFields = ['p1', 'p2', 'p3', 'p4', 'p5'];
	  for (const field of additionalFields) {
	    const value = analytics[field];
	    if (!main_core.Type.isStringFilled(value)) {
	      continue;
	    }
	    if (value.split('_').length > 2) {
	      console.error(`BX.UI.Analytics: The "${field}" property (${value}) in the "analytics" object must be a string containing a single underscore.`);
	      return false;
	    }
	  }
	  return true;
	}
	function buildUrlByData(data) {
	  const url = new URL('/_analytics/', window.location.origin);
	  const queryParams = [];
	  for (const [key, value] of Object.entries(data)) {
	    queryParams.push(`st[${key}]=${encodeURIComponent(value)}`);
	  }
	  url.search = queryParams.join('&');
	  return url.toString();
	}
	function sendAnalyticsData(analytics) {
	  if (!isValidAnalyticsData(analytics)) {
	    return;
	  }
	  const settings = main_core.Extension.getSettings('ui.analytics');
	  const collectData = settings.get('collectData', false);
	  if (!collectData) {
	    return;
	  }
	  void main_core.ajax({
	    method: 'GET',
	    url: buildUrlByData(analytics)
	  });
	}

	function sendData(data) {
	  /** @see BX.ajax.runAction */
	  /** @see processAnalyticsDataToGetParameters() */
	  sendAnalyticsData(data);
	}

	exports.sendData = sendData;

}((this.BX.UI.Analytics = this.BX.UI.Analytics || {}),BX));
//# sourceMappingURL=analytics.bundle.js.map
