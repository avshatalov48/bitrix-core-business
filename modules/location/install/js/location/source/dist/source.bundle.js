this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,location_core,location_google,location_osm) {
	'use strict';

	class Factory {
	  static create(code, languageId, sourceLanguageId, sourceProps = {}) {
	    const props = sourceProps;
	    props.languageId = languageId;
	    props.sourceLanguageId = sourceLanguageId;
	    if (code === location_google.Google.code) {
	      return new location_google.Google(props);
	    } else if (code === location_osm.OSM.code) {
	      return location_osm.OSMFactory.createOSMSource(props);
	    }
	    return null;
	  }
	}

	exports.Factory = Factory;

}((this.BX.Location.Source = this.BX.Location.Source || {}),BX.Location.Core,BX.Location.Google,BX.Location.OSM));
//# sourceMappingURL=source.bundle.js.map
