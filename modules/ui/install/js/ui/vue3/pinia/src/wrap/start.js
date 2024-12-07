;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue3 !== 'undefined'
		&& typeof this.BX.Vue3.Pinia !== 'undefined'
	)
	{
		var currentVersion = '2.2.2';

		if (this.BX.Vue3.Pinia.version !== currentVersion)
		{
			console.warn('BX.Vue3.Pinia already loaded. Loaded: ' + this.BX.Vue3.Pinia.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}