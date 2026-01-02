const useSafeNamespaces = require('../../../build-tools/use-safe-namespaces');

module.exports = {
	input: 'src/message-list.js',
	output: 'dist/message-list.bundle.js',
	namespace: 'BX.Messenger.v2.Component',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};
