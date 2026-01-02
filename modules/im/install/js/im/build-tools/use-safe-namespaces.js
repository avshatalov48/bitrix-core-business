module.exports = function useSafeNamespaces() {
	return {
		name: 'use-safe-namespaces',
		renderChunk(code) {
			if (typeof code !== 'string')
			{
				return code;
			}

			const lastLineIndex = code.lastIndexOf('\n');
			const lastLine = code.slice(lastLineIndex + 1);
			const match = /}\(.*\),(.*)\)\)/.exec(lastLine);
			if (Array.isArray(match))
			{
				const sourceNamespaces = match[1];
				const safeNamespaces = sourceNamespaces
					.split(',')
					.map((sourceNamespace) => {
						return `${sourceNamespace.split('.').join('?.')}??{}`;
					})
					.join(',');

				const newLastLine = lastLine.replace(
					sourceNamespaces,
					safeNamespaces,
				);

				return code.slice(0, lastLineIndex + 1) + newLastLine;
			}

			return code;
		},
	};
};
