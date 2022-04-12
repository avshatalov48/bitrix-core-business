;(function() {

	if (typeof this.BX !== 'undefined' && typeof this.BX.Vue3 !== 'undefined')
	{
		var currentVersion = '3.2.31';

		if (this.BX.Vue3.version !== currentVersion)
		{
			console.warn('BX.Vue3 already loaded. Loaded: ' + this.BX.Vue3.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}