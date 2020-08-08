/**
 * Bitrix Messenger
 * Clipboard manager
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export class Clipboard
{
	static copy(text = '')
	{
		const store = Clipboard.getStore();

		if (text)
		{
			store.focus();
			store.value = text;
			store.selectionStart = 0;
			document.execCommand("copy");
		}
		else
		{
			document.execCommand("copy");

			store.focus();

			document.execCommand("paste");
			text = store.value;
		}

		Clipboard.removeStore();

		return text;
	}

	static getStore()
	{
		if (Clipboard.store)
		{
			return Clipboard.store;
		}

		Clipboard.store = document.createElement('textarea');
		Clipboard.store.style = "position: absolute; opacity: 0; top: -1000px; left: -1000px;";
		document.body.insertBefore(Clipboard.store, document.body.firstChild);

		return Clipboard.store;
	}

	static removeStore()
	{
		if (!Clipboard.store)
		{
			return true;
		}

		document.body.removeChild(Clipboard.store);

		Clipboard.store = null;

		return true;
	}
}

Clipboard.store = null;