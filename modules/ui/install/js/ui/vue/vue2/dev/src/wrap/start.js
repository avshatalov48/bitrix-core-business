;(function() {

	if (typeof this.BX !== 'undefined' && typeof this.BX.Vue !== 'undefined')
	{
		var currentVersion = '2.6.14';

		if (this.BX.Vue.version() !== currentVersion)
		{
			console.warn('BX.Vue already loaded. Loaded: ' + this.BX.Vue.version() + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}