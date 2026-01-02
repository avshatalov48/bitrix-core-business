const useSafeNamespaces = require('../../../../build-tools/use-safe-namespaces');

module.exports = {
	input: './src/chat.js',
	output: './dist/chat.bundle.js',
	namespace: 'BX.Messenger.v2.Service',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};
