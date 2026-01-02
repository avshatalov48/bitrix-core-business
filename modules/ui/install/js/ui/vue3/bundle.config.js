module.exports = {
	input: (() => {
		if (global?.process?.argv?.includes?.('test'))
		{
			return './vue-for-node-env/src/index.js';
		}

		return './vue/dev/src/vue-dev.js';
	})(),
	namespace: 'BX.Vue3',
	protected: true,
	browserslist: true,
};
