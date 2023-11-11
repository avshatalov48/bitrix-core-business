/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */

let crypto = window.crypto || window.msCrypto;
if (!crypto && typeof (process) === 'object')
{
	// eslint-disable-next-line no-undef
	crypto = require('crypto').webcrypto;
}

const createUniqueId = (): string => {
	return (`${1e7}-${1e3}-${4e3}-${8e3}-${1e11}`).replaceAll(
		/[018]/g,
		(part: string) => (part ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (part / 4)))).toString(16),
	);
};

export default createUniqueId;
