import {Reflection} from 'main.core';

const Public = Reflection.getClass('top.BX.Messenger.Public');
if (!Public)
{
	console.error('The BX.Messenger.Public class cannot be accessed from this location.');
}

// pretty export
const namespace = Reflection.namespace('BX.Messenger.Public');
if (namespace)
{
	namespace.Iframe = Public;
}

export {Public as Messenger};