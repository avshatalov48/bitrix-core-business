;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vuex !== 'undefined'
	)
	{
		var currentVersion = '3.6.2';

		if (this.BX.Vuex.version !== currentVersion)
		{
			console.warn('BX.Vuex already loaded. Loaded: ' + this.BX.Vuex.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}