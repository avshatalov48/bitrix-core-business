const useSafeNamespaces = require('../../../../../build-tools/use-safe-namespaces');

module.exports = {
	input: 'src/openline-container.js',
	output: 'dist/openline-container.bundle.js',
	namespace: 'BX.Messenger.v2.Component.List',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};