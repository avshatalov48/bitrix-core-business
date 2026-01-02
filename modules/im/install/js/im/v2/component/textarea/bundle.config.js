const useSafeNamespaces = require('../../../build-tools/use-safe-namespaces');

module.exports = {
	input: 'src/textarea.js',
	output: 'dist/textarea.bundle.js',
	namespace: 'BX.Messenger.v2.Component',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};