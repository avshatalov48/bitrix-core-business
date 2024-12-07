;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue3 !== 'undefined'
		&& typeof this.BX.Vue3.Vuex !== 'undefined'
	)
	{
		var currentVersion = '4.1.0';

		if (this.BX.Vue3.Vuex.version !== currentVersion)
		{
			console.warn('BX.Vue3.Vuex already loaded. Loaded: ' + this.BX.Vue3.Vuex.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}