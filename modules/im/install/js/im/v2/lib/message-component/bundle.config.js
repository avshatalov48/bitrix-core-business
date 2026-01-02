const useSafeNamespaces = require('../../../build-tools/use-safe-namespaces');

module.exports = {
	input: 'src/message-component.js',
	output: 'dist/message-component.bundle.js',
	namespace: 'BX.Messenger.v2.Lib',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};
