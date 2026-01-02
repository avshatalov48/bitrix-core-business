const useSafeNamespaces = require('../../../build-tools/use-safe-namespaces');

module.exports = {
	input: './src/core.js',
	output: './dist/core.bundle.js',
	namespace: 'BX.Messenger.v2.Application',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};