import { Loc } from 'main.core';

const fs = require('fs');
const path = require('path');

export default function loadMessages(lang = 'en')
{
	const langFile = path.join(path.normalize(__dirname + '../../lang/'), lang, 'config.php');
	const contents = fs.readFileSync(langFile, 'ascii');

	const regex = /\$MESS\[['"](?<code>.+?)['"]]\s*=\s*['"](?<phrase>.*?)['"]/gm;
	let match;

	while ((match = regex.exec(contents)) !== null)
	{
		if (match.index === regex.lastIndex)
		{
			regex.lastIndex++;
		}

		const { code, phrase } = match.groups;
		Loc.setMessage(code, phrase);
	}
}