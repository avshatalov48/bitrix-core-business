module.exports = {
	input: 'src/screenshoter.js',
	output: 'dist/screenshoter.bundle.js',
	namespace: 'BX.Landing',
	plugins: {
		resolve: true,
		custom: [
			{
				name: 'post-build-hacks',
				transform(code, id) {
					if (String(id).includes('html-to-image'))
					{
						code = String(code)
							.replace(/(fetch\(.*?)\)/ig, '$1, {mode: \'no-cors\'})')
							.replace(
								/let href = (.*)?;/,
								`
									var href = url;
	
									if (
										BX.Type.isStringFilled(url)
										&& url.startsWith('http')
										&& !url.startsWith(window.location.origin)
										&& (
											url.endsWith('.svg')
											|| url.endsWith('.png')
											|| url.endsWith('.jpg')
											|| url.endsWith('.gif')
										)
									)
									{
										url = BX.Uri.addParam('/bitrix/tools/landing/proxy.php', {
											sessid: BX.bitrix_sessid(),
											url: url,
										});
									}
								`
							);
					}

					return {code};
				},
			},
		],
	},
	protected: true,
};