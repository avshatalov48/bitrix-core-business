import {Loc} from "./resourcebooking";

export class Translit
{
	static replacementCharTable = null;

	static run (str)
	{
		let
			replaceChar = '_',
			regexpEnChars = /[A-Z0-9]/i,
			regexpSpace = /\s/,
			maxLength = 100,
			len = str.length,
			result = '',
			lastNewChar = '',
			i;

		for (i = 0; i < len; i++)
		{
			let
				newChar,
				chr = str.charAt(i);

			if (regexpEnChars.test(chr))
			{
				newChar = chr;
			}
			else if (regexpSpace.test(chr))
			{
				if (i > 0 && lastNewChar !== replaceChar)
				{
					newChar = replaceChar;
				}
				else
				{
					newChar = '';
				}
			}
			else
			{
				newChar = Translit.getChar(chr);

				if (newChar === null)
				{
					if (i > 0 && i !== len - 1 && lastNewChar !== replaceChar)
					{
						newChar = replaceChar;
					}
					else
					{
						newChar = '';
					}
				}
			}

			if (null != newChar && newChar.length > 0)
			{
				newChar = newChar.toLowerCase();
				result += newChar;
				lastNewChar = newChar;
			}

			if (result.length >= maxLength)
			{
				break;
			}
		}

		return result;
	}

	static generateReplacementCharTable()
	{
		let
			separator = ',',
			charTableFrom = (Loc.getMessage('TRANSLIT_FROM') || '').split(separator),
			charTableTo = (Loc.getMessage('TRANSLIT_TO') || '').split(separator),
			i, len;

		Translit.replacementCharTable = [];
		for (i = 0, len = charTableFrom.length; i < len; i++)
		{
			Translit.replacementCharTable[i] = [charTableFrom[i], charTableTo[i]];
		}
	}

	static getChar(chr)
	{
		if (Translit.replacementCharTable === null)
		{
			Translit.generateReplacementCharTable();
		}

		for (let i = 0, len = Translit.replacementCharTable.length; i < len; i++)
		{
			if (chr === Translit.replacementCharTable[i][0])
			{
				return Translit.replacementCharTable[i][1];
			}
		}

		return null;
	}
}