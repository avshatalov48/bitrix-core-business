;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue3 !== 'undefined'
		&& typeof this.BX.Vue3.BitrixVue !== 'undefined'
	)
	{
		console.warn('BX.Vue3.BitrixVue already loaded.');
		return;
	}