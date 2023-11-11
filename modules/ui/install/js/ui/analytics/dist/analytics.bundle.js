/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var settings = main_core.Extension.getSettings('ui.analytics');
	function isValidAnalyticsData(analytics) {
	  if (!main_core.Type.isPlainObject(analytics)) {
	    console.error('BX.UI.Analytics: {analytics} must be an object.');
	    return false;
	  }
	  var requiredFields = ['event', 'tool', 'category'];
	  for (var _i = 0, _requiredFields = requiredFields; _i < _requiredFields.length; _i++) {
	    var field = _requiredFields[_i];
	    if (!main_core.Type.isStringFilled(analytics[field])) {
	      console.error("BX.UI.Analytics: The \"".concat(field, "\" property in the \"analytics\" object must be a non-empty string."));
	      return false;
	    }
	  }
	  var additionalFields = ['p1', 'p2', 'p3', 'p4', 'p5'];
	  for (var _i2 = 0, _additionalFields = additionalFields; _i2 < _additionalFields.length; _i2++) {
	    var _field = _additionalFields[_i2];
	    var value = analytics[_field];
	    if (!main_core.Type.isStringFilled(value)) {
	      continue;
	    }
	    if (value.split('_').length > 2) {
	      console.error("BX.UI.Analytics: The \"".concat(_field, "\" property (").concat(value, ") in the \"analytics\" object must be a string containing a single underscore."));
	      return false;
	    }
	  }
	  return true;
	}
	function buildUrlByData(data) {
	  var url = new URL('/_analytics/', window.location.origin);
	  for (var _i3 = 0, _Object$entries = Object.entries(data); _i3 < _Object$entries.length; _i3++) {
	    var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i3], 2),
	      key = _Object$entries$_i[0],
	      value = _Object$entries$_i[1];
	    url.searchParams.append(key, value);
	  }
	  return url.toString();
	}
	function sendAnalyticsData(analytics) {
	  if (!isValidAnalyticsData(analytics)) {
	    return;
	  }
	  var collectData = settings.get('collectData', false);
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
