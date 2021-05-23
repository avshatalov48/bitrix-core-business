;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue !== 'undefined'
		&& typeof this.BX.Vue.PortalVue !== 'undefined'
	)
	{
		var currentVersion = '2.1.7';

		if (this.BX.Vue.PortalVue.version !== currentVersion)
		{
			console.warn('BX.Vuex already loaded. Loaded: ' + this.BX.Vue.PortalVue.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}