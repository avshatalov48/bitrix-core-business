;(function()
{
	if (BX.PhoneNumber)
		return;

	var parserInstance;

	var metadataPromise = null;
	var metadataLoaded = false;
	var metadataUrl = '/bitrix/js/main/phonenumber/metadata.json';
	var ajaxUrl = '/bitrix/tools/phone_number.php';

	var metadata = {};
	var codeToCountries;

	var MAX_LENGTH_COUNTRY_CODE = 3; // The maximum length of the country calling code.
	var MIN_LENGTH_FOR_NSN = 2; // The minimum length of the national significant number.
	var MAX_LENGTH_FOR_NSN = 17; // The ITU says the maximum length should be 15, but one can find longer numbers in Germany.

	/* We don't allow input strings for parsing to be longer than 250 chars. This prevents malicious input from consuming CPU.*/
	var MAX_INPUT_STRING_LENGTH = 250;

	var plusChar = '+';

	/* Digits accepted in phone numbers (ascii, fullwidth, arabic-indic, and eastern arabic digits). */
	var validDigits = '0-9';
	var dashes = '-';
	var slashes = '\/';
	var dot = '.';
	var whitespace = '\\s';
	var brackets = '()\\[\\]';
	var tildes = '~';
	var extensionSeparators = ';#';
	var extensionSymbols = ',';

	var phoneNumberStartPattern = '[' + plusChar + validDigits + ']';
	var afterPhoneNumberEndPattern = '[^' + validDigits + extensionSeparators + extensionSymbols + ']+$';
	var minLengthPhoneNumberPattern = '[' + validDigits + ']{' + MIN_LENGTH_FOR_NSN + '}';
	var validPunctuation = dashes + slashes + dot + whitespace + brackets + tildes + extensionSeparators + extensionSymbols;
	var significantChars = validDigits + plusChar + extensionSeparators + extensionSymbols;

	var validPhoneNumber =
		'[' + plusChar + ']{0,1}' +
		'(?:' +
		'[' + validPunctuation + ']*' +
		'[' + validDigits + ']' +
		'){3,}' +
		'[' +
		validPunctuation +
		validDigits +
		']*';

	var validPhoneNumberPattern =
		'^(?:'+
		// Either a short two-digit-only phone number
			'^' + minLengthPhoneNumberPattern +'$' +
		// Or a longer fully parsed phone number (min 3 characters)
		'|' + '^' + validPhoneNumber + '$' +
		')$';

	var loadMetadata = function()
	{
		if(metadataLoaded)
		{
			var result = new BX.Promise();
			result.fulfill({
				codeToCountries: codeToCountries,
				metadata: metadata
			});
			return result;
		}
		else if(metadataPromise)
		{
			return metadataPromise;
		}
		else
		{
			metadataPromise = new BX.Promise();

			BX.ajax.load({
				'url': metadataUrl,
				'type': 'json',
				'callback': function(data)
				{
					codeToCountries = data.codeToCountries;
					metadata = data.metadata;
					data.metadata.forEach(function(metadataRecord)
					{
						metadata[metadataRecord['id']] = metadataRecord;
					});
					metadataLoaded = true;
					metadataPromise.fulfill({
						codeToCountries: codeToCountries,
						metadata: metadata
					});
				}
			});
			return metadataPromise;
		}
	};

	BX.PhoneNumber = function()
	{
		this.rawNumber = null;
		this.country = null;

		this.valid = false;
		this.countryCode = null;
		this.nationalNumber = null;
		this.numberType = null;
		this.extension = null;
		this.extensionSeparator = null;

		this.international = false;
		this.nationalPrefix = null;
		this.hasPlusChar = false;
	};

	BX.PhoneNumber.Format = {
		'E164': 'E.164',
		'INTERNATIONAL': 'International',
		'NATIONAL': 'National'
	};

	BX.PhoneNumber.getDefaultCountry  = function()
	{
		return BX.message('phone_number_default_country');
	};

	BX.PhoneNumber.getUserDefaultCountry = function()
	{
		return BX.message('user_default_country');
	};

	BX.PhoneNumber.getIncompleteFormatter = function(defaultCountry)
	{
		var result = new BX.Promise();

		if(metadataLoaded)
		{
			result.fulfill(new BX.PhoneNumber.IncompleteFormatter(defaultCountry));
		}
		else
		{
			loadMetadata().then(function()
			{
				result.fulfill(new BX.PhoneNumber.IncompleteFormatter(defaultCountry));
			});
		}

		return result;
	};

	BX.PhoneNumber.getValidNumberPattern = function()
	{
		return validPhoneNumber;
	};

	BX.PhoneNumber.getValidNumberRegex = function()
	{
		return new RegExp(validPhoneNumber);
	};

	BX.PhoneNumber.prototype.format = function(formatType)
	{
		if(this.valid)
		{
			if(!formatType)
			{
				return BX.PhoneNumberFormatter.formatOriginal(this);
			}
			else
			{
				return BX.PhoneNumberFormatter.format(this, formatType)
			}
		}
		else
		{
			if(ShortNumberFormatter.isApplicable(this.getRawNumber()))
			{
				return ShortNumberFormatter.format(this.getRawNumber());
			}
			else
			{
				return this.rawNumber;
			}
		}
	};

	BX.PhoneNumber.prototype.getRawNumber = function()
	{
		return this.rawNumber;
	};

	BX.PhoneNumber.prototype.setRawNumber = function(rawNumber)
	{
		this.rawNumber = rawNumber;
	};

	BX.PhoneNumber.prototype.getCountry = function()
	{
		return this.country;
	};

	BX.PhoneNumber.prototype.setCountry = function(country)
	{
		this.country = country;
	};

	BX.PhoneNumber.prototype.isValid = function()
	{
		return this.valid;
	};

	BX.PhoneNumber.prototype.setValid = function(valid)
	{
		this.valid = valid;
	};

	BX.PhoneNumber.prototype.getCountryCode = function()
	{
		return this.countryCode;
	};

	BX.PhoneNumber.prototype.setCountryCode = function(countryCode)
	{
		this.countryCode = countryCode;
	};

	BX.PhoneNumber.prototype.getNationalNumber = function()
	{
		return this.nationalNumber;
	};

	BX.PhoneNumber.prototype.setNationalNumber = function(nationalNumber)
	{
		this.nationalNumber = nationalNumber;
	};

	BX.PhoneNumber.prototype.getNumberType = function()
	{
		return this.numberType;
	};

	BX.PhoneNumber.prototype.setNumberType = function(numberType)
	{
		this.numberType = numberType;
	};

	BX.PhoneNumber.prototype.hasExtension = function()
	{
		return !!this.extension;
	};

	BX.PhoneNumber.prototype.getExtension = function()
	{
		return this.extension;
	};

	BX.PhoneNumber.prototype.setExtension = function(extension)
	{
		this.extension = extension;
	};

	BX.PhoneNumber.prototype.getExtensionSeparator = function()
	{
		return this.extensionSeparator;
	};

	BX.PhoneNumber.prototype.setExtensionSeparator = function(extensionSeparator)
	{
		this.extensionSeparator = extensionSeparator;
	};

	BX.PhoneNumber.prototype.isInternational = function()
	{
		return this.international;
	};

	BX.PhoneNumber.prototype.setInternational = function(international)
	{
		this.international = international;
	};

	BX.PhoneNumber.prototype.getNationalPrefix = function()
	{
		return this.nationalPrefix;
	};

	BX.PhoneNumber.prototype.setNationalPrefix = function(nationalPrefix)
	{
		this.nationalPrefix = nationalPrefix;
	};

	BX.PhoneNumber.prototype.hasPlus = function()
	{
		return this.hasPlusChar;
	};

	BX.PhoneNumber.prototype.setHasPlus = function(hasPlus)
	{
		this.hasPlusChar = hasPlus;
	};

	BX.PhoneNumberParser = function()
	{

	};

	BX.PhoneNumberParser.getInstance = function()
	{
		if(!(parserInstance instanceof BX.PhoneNumberParser))
			parserInstance = new BX.PhoneNumberParser();

		return parserInstance;
	};

	BX.PhoneNumberParser.prototype.parse = function(phoneNumber, defaultCountry)
	{
		var self = this;
		var result = new BX.Promise();

		if(!defaultCountry)
			defaultCountry = BX.PhoneNumber.getDefaultCountry();

		if(metadataLoaded)
		{
			result.fulfill(self._realParse(phoneNumber, defaultCountry));
		}
		else
		{
			loadMetadata().then(function()
			{
				result.fulfill(self._realParse(phoneNumber, defaultCountry));
			});
		}

		return result;
	};

	BX.PhoneNumberParser.prototype._realParse = function(phoneNumber, defaultCountry)
	{
		var result = new BX.PhoneNumber();
		result.setRawNumber(phoneNumber);

		var formattedPhoneNumber = _extractFormattedPhoneNumber(phoneNumber);
		if(!_isViablePhoneNumber(formattedPhoneNumber))
		{
			return result;
		}

		var extensionParseResult = _stripExtension(formattedPhoneNumber);
		var extension = extensionParseResult.extension;
		var extensionSeparator = extensionParseResult.extensionSeparator;

		formattedPhoneNumber = extensionParseResult.phoneNumber;

		var parseResult = _parsePhoneNumberAndCountryPhoneCode(formattedPhoneNumber);
		if(parseResult === false)
		{
			return result;
		}

		var country;
		var countryCode = parseResult['countryCode'];
		var localNumber = parseResult['localNumber'];
		var isInternational;
		var countryMetadata;
		var hasPlusChar = false;

		if(countryCode)
		{
			// Number in international format, thus we ignore $country parameter
			isInternational = true;
			hasPlusChar = true;
			countryMetadata = _getMetadataByCountryCode(countryCode);

			/*
			 $country will be set later, because, for example, for NANPA countries
			 there are several countries corresponding to the same `1` country phone code.
			 Therefore, to reliably determine the exact country, national number should be parsed first.
			 */
			country = null;
		}
		else if(!defaultCountry)
		{
			return result;
		}
		else
		{
			// Number in national format or in international format without + sign.
			country = defaultCountry;
			countryMetadata = _getCountryMetadata(country);
			if(!countryMetadata)
				return result;

			countryCode = countryMetadata['countryCode'];
			var numberWithoutCountryCode = _stripCountryCode(localNumber, countryMetadata);
			isInternational = (numberWithoutCountryCode !== localNumber);

			localNumber = numberWithoutCountryCode;
		}

		if(!countryMetadata)
		{
			return result;
		}

		var numberWithoutNationalPrefix = _stripNationalPrefix(localNumber, countryMetadata);

		var hadNationalPrefix = false;
		var nationalPrefix = '';
		if (numberWithoutNationalPrefix !== localNumber)
		{
			hadNationalPrefix = _isNumberValid(numberWithoutNationalPrefix, countryMetadata);
			if(hadNationalPrefix)
			{
				nationalPrefix = localNumber.substr(0, localNumber.length - numberWithoutNationalPrefix.length);
				localNumber = numberWithoutNationalPrefix;
			}
		}

		// Sometimes there are several countries corresponding to the same country phone code (e.g. NANPA countries all
		// having `1` country phone code). Therefore, to reliably determine the exact country, national (significant)
		// number should have been parsed first.
		if(country === null)
		{
			country = _findCountry(countryCode, localNumber);
			if(!country)
			{
				return result;
			}

			countryMetadata = _getCountryMetadata(country);
		}

		// Validate local (significant) number length
		if(localNumber.length > MAX_LENGTH_FOR_NSN)
		{
			return result;
		}

		var nationalNumberRegex = new RegExp('^(?:' + countryMetadata['generalDesc']['nationalNumberPattern'] + ')$');
		if(!localNumber.match(nationalNumberRegex))
		{
			return result;
		}

		var numberType = _getNumberType(localNumber, country);
		result.setCountry(country);
		result.setCountryCode(countryCode);
		result.setNationalNumber(localNumber);
		result.setNumberType(numberType);
		result.setInternational(isInternational);
		result.setHasPlus(hasPlusChar);
		result.setNationalPrefix(nationalPrefix);
		result.setExtension(extension);
		result.setExtensionSeparator(extensionSeparator);
		result.setValid(numberType !== false);

		return result;
	};

	BX.PhoneNumberFormatter = {};

	BX.PhoneNumberFormatter.format = function(number, formatType)
	{
		if(!(number instanceof BX.PhoneNumber))
		{
			throw new Error("number should be instance of BX.PhoneNumber");
		}

		if(!metadataLoaded)
		{
			throw new Error("Metadata should be loaded prior to calling format");
		}

		if(!number.isValid())
			return number.getRawNumber();

		if(formatType === BX.PhoneNumber.Format.E164)
		{
			var result = '+' + number.getCountryCode()
				+ number.getNationalNumber()
				+ (number.hasExtension() ? number.getExtensionSeparator() + " " + number.getExtension() : "");

			return result;
		}

		var countryMetadata = _getCountryMetadata(number.getCountry());
		var isInternational = formatType === BX.PhoneNumber.Format.INTERNATIONAL;
		var format = this.selectFormatForNumber(number.getNationalNumber(), isInternational, countryMetadata);

		if(format)
		{
			var formattedNationalNumber = this.formatNationalNumber(
				number.getNationalNumber(),
				isInternational,
				countryMetadata,
				format
			);
		}
		else
		{
			formattedNationalNumber = number.getNationalNumber();
		}

		if(number.hasExtension())
		{
			formattedNationalNumber += number.getExtensionSeparator() + " " + number.getExtension();
		}

		if(formatType === BX.PhoneNumber.Format.INTERNATIONAL)
		{
			return '+' + number.getCountryCode() + ' ' + formattedNationalNumber;
		}
		else if(formatType === BX.PhoneNumber.Format.NATIONAL)
		{
			return formattedNationalNumber;
		}

		return number.getRawNumber();
	};

	BX.PhoneNumberFormatter.formatOriginal = function(number)
	{
		if(!number.isValid())
			return number.getRawNumber();

		var format = this.selectOriginalFormatForNumber(number);
		if(!format)
			return number.getRawNumber();

		var formattedNationalNumber = this.formatNationalNumberWithOriginalFormat(number, format);

		if(number.hasExtension())
		{
			formattedNationalNumber += number.getExtensionSeparator() + " " + number.getExtension();
		}

		if(number.isInternational())
		{
			var formattedNumber = (number.hasPlus() ? '+' : '') + number.getCountryCode() + ' ' + formattedNationalNumber;
		}
		else
		{
			formattedNumber = formattedNationalNumber;
		}

		// If no digit was inserted/removed/altered in a process of formatting, return the formatted number;
		var normalizedFormattedNumber = _stripLetters(formattedNumber);
		var normalizedRawInput = _stripLetters(number.getRawNumber());
		if (normalizedFormattedNumber !== normalizedRawInput)
		{
			formattedNumber = number.getRawNumber();
		}

		return formattedNumber;
	};

	BX.PhoneNumberFormatter.selectFormatForNumber = function(nationalNumber, isInternational, countryMetadata)
	{
		var availableFormats = _getAvailableFormats(countryMetadata);

		for (var i = 0; i < availableFormats.length; i++)
		{
			var format = availableFormats[i];
			if(isInternational &&  format.hasOwnProperty('intlFormat') && format['intlFormat'] === 'NA')
				continue;

			if(format.hasOwnProperty('leadingDigits') && !_matchLeadingDigits(nationalNumber, format['leadingDigits']))
			{
				continue;
			}

			var formatPatternRegex = new RegExp('^' + format['pattern'] + '$');
			if(nationalNumber.match(formatPatternRegex))
			{
				return format;
			}
		}
		return false;
	};

	BX.PhoneNumberFormatter.selectOriginalFormatForNumber = function(number)
	{
		var nationalNumber = number.getNationalNumber();
		var isInternational = number.isInternational();
		var hasNationalPrefix = number.getNationalPrefix() != '';
		var countryMetadata = _getCountryMetadata(number.getCountry());
		var availableFormats = _getAvailableFormats(countryMetadata);

		for (var i = 0; i < availableFormats.length; i++)
		{
			var format = availableFormats[i];
			if(isInternational)
			{
				if(format.hasOwnProperty('intlFormat') && format['intlFormat'] === 'NA')
				{
					continue;
				}
			}
			else
			{
				if(hasNationalPrefix && !_isNationalPrefixSupported(format, countryMetadata))
				{
					continue;
				}
			}


			if(format.hasOwnProperty('leadingDigits') && !_matchLeadingDigits(nationalNumber, format['leadingDigits']))
			{
				continue;
			}

			var formatPatternRegex = new RegExp('^' + format['pattern'] + '$');
			if(nationalNumber.match(formatPatternRegex))
			{
				return format;
			}
		}
		return false;
	}

	BX.PhoneNumberFormatter.formatNationalNumber = function(nationalNumber, isInternational, countryMetadata, format)
	{
		var replaceFormat = (format.hasOwnProperty('intlFormat') && isInternational) ? format['intlFormat'] : format['format'];
		var patternRegex = new RegExp(format['pattern']);

		if(!isInternational)
		{
			var nationalPrefixFormattingRule = _getNationalPrefixFormattingRule(format, countryMetadata);
			if(nationalPrefixFormattingRule != '')
			{
				nationalPrefixFormattingRule = nationalPrefixFormattingRule.replace('$NP', countryMetadata['nationalPrefix']).replace('$FG', '$1');
				replaceFormat = replaceFormat.replace(new RegExp('(\\$\\d)'), nationalPrefixFormattingRule);
			}
			else
			{
				replaceFormat = countryMetadata['nationalPrefix'] + ' ' + replaceFormat;
			}
		}

		return nationalNumber.replace(patternRegex, replaceFormat);
	};

	BX.PhoneNumberFormatter.formatNationalNumberWithOriginalFormat = function(number, format)
	{
		var isInternational = number.isInternational();
		var replaceFormat = (format.hasOwnProperty('intlFormat') && isInternational) ? format['intlFormat'] : format['format'];
		var patternRegex =  new RegExp(format['pattern']);
		var nationalNumber = number.getNationalNumber();
		var countryMetadata = _getCountryMetadata(number.getCountry());
		var nationalPrefix = _getNationalPrefix(countryMetadata, true);
		var hasNationalPrefix = _numberContainsNationalPrefix(number.getRawNumber(), nationalPrefix, countryMetadata);

		if(!isInternational && hasNationalPrefix)
		{
			var nationalPrefixFormattingRule = _getNationalPrefixFormattingRule(format, countryMetadata);
			if(nationalPrefixFormattingRule != '')
			{
				nationalPrefixFormattingRule = nationalPrefixFormattingRule.replace('$NP', nationalPrefix).replace('$FG', '$1');
				replaceFormat = replaceFormat.replace(new RegExp('(\\$\\d)'), nationalPrefixFormattingRule);
			}
			else
			{
				replaceFormat = nationalPrefix + ' ' + replaceFormat;
			}
		}

		return nationalNumber.replace(patternRegex, replaceFormat);
	};

	BX.PhoneNumberFormatter.getNationalPrefixFormattingRule = function (countryMetadata, format)
	{
		var result = _getNationalPrefixFormattingRule(format, countryMetadata);

		return result.replace('$NP', countryMetadata['nationalPrefix']).replace('$FG', '$1');
	};

	BX.PhoneNumberFormatter.getNationalPrefixOptional = function(countryMetadata, format)
	{
		if(BX.type.isPlainObject(format) && format.hasOwnProperty('nationalPrefixOptionalWhenFormatting'))
			return format['nationalPrefixOptionalWhenFormatting'];
		else if(countryMetadata.hasOwnProperty('nationalPrefixOptionalWhenFormatting'))
			return countryMetadata['nationalPrefixOptionalWhenFormatting'];
		else
			return false;
	};


	/* Partial number formatter (used in phone input control */

	var DUMMY_DIGIT = '9';
	var DUMMY_DIGIT_MATCHER = new RegExp(DUMMY_DIGIT, 'g');
	var LONGEST_NATIONAL_PHONE_NUMBER_LENGTH = 15;
	var LONGEST_DUMMY_PHONE_NUMBER = _repeat(DUMMY_DIGIT, LONGEST_NATIONAL_PHONE_NUMBER_LENGTH);
	var DIGIT_PLACEHOLDER = 'x';
	var DIGIT_PLACEHOLDER_MATCHER = new RegExp(DIGIT_PLACEHOLDER);
	var DIGIT_PLACEHOLDER_MATCHER_GLOBAL = new RegExp(DIGIT_PLACEHOLDER, 'g');
	var CHARACTER_CLASS_PATTERN = new RegExp('\\[([^\\[\\]])*\\]', 'g'); //matches character class in some other regexp

	// Any digit in a regular expression that actually denotes a digit. For
	// example, in the regular expression "80[0-2]\d{6,10}", the first 2 digits
	// (8 and 0) are standalone digits, but the rest are not.
	// Two look-aheads are needed because the number following \\d could be a
	// two-digit number, since the phone number can be as long as 15 digits.
	var STANDALONE_DIGIT_PATTERN = new RegExp('\\d(?=[^,}][^,}])', 'g');

	// A pattern that is used to determine if a `format` is eligible
	// to be used by the "as you type formatter".
	// It is eligible when the `format` contains groups of the dollar sign
	// followed by a single digit, separated by valid phone number punctuation.
	// This prevents invalid punctuation (such as the star sign in Israeli star numbers)
	// getting into the output of the "as you type formatter".
	var ELIGIBLE_FORMAT_MATCHER = new RegExp('^' + '[' + validPunctuation + ']*' + '(\\$\\d[' + validPunctuation + ']*)+' + '$');

	// This is the minimum length of the leading digits of a phone number
	// to guarantee the first "leading digits pattern" for a phone number format
	// to be preemptive.
	var MIN_LEADING_DIGITS_LENGTH = 3;

	var VALID_INCOMPLETE_PHONE_NUMBER = '[' + plusChar + ']{0,1}' + '[' + validPunctuation + validDigits + ']*';
	var VALID_INCOMPLETE_PHONE_NUMBER_PATTERN = new RegExp('^' + VALID_INCOMPLETE_PHONE_NUMBER + '$', 'i');

	BX.PhoneNumber.IncompleteFormatter = function(defaultCountry)
	{
		if(!metadataLoaded)
		{
			throw new Error("Metadata is not loaded yet. Do not construct this class directly, use BX.PhoneNumber.getIncompleteFormatter instead");
		}

		this.defaultCountry = defaultCountry || BX.PhoneNumber.getDefaultCountry();

		this.rawInput = '';

		this.country = '';
		this.countryCode = '';
		this.countryMetadata = null;
		this.nationalPrefix = '';
		this.nationalNumber = '';
		this.isInternational = false;
		this.hasNationalPrefix = false;
		this.hasPlusChar = false;
		this.formattedNumber = null;
		this.extension = '';
		this.extensionSeparator = '';
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.format = function(incompleteNumber)
	{
		this.resetState();

		var extractedNumber = _extractFormattedPhoneNumber(incompleteNumber);

		if(!extractedNumber && incompleteNumber[0] === plusChar)
		{
			this.rawInput = incompleteNumber;
			this.formattedNumber = incompleteNumber;
			return incompleteNumber;
		}

		this.isInternational = extractedNumber[0] === plusChar;

		var stripResult = _stripExtension(extractedNumber);
		extractedNumber = stripResult.phoneNumber;
		this.extension = stripResult.extension;
		this.extensionSeparator = stripResult.extensionSeparator;

		extractedNumber = _stripLetters(extractedNumber);
		this.rawInput = extractedNumber;
		if(this.isInternational)
		{
			this.hasPlusChar = true;
			this.rawInput = plusChar + extractedNumber;
		}

		if(this.isInternational)
		{
			this.extractCountryCode();
			if(!this.countryCode)
			{
				return this.rawInput;
			}

			this.findSuitableCountry();
		}
		else if(!this.defaultCountry)
		{
			return this.rawInput;
		}
		else
		{
			this.country = this.defaultCountry;
			this.countryMetadata = _getCountryMetadata(this.country);
			if(!this.countryMetadata)
			{
				return this.rawInput;
			}
			this.nationalNumber = this.rawInput;
			this.extractNationalPrefix();

			if(!this.hasNationalPrefix)
			{
				this.tryToStripCountryCode();
			}
		}

		return this.getFormattedNumber();
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.getFormattedNumber = function()
	{
		var formattedNationalNumber = this.formatNationalNumber();
		var result = formattedNationalNumber ? formattedNationalNumber : this.rawInput;

		if(this.extensionSeparator)
		{
			result += this.extensionSeparator + " " + this.extension;
		}

		return result;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.extractCountryCode = function()
	{
		var parseResult = _parsePhoneNumberAndCountryPhoneCode(this.rawInput);
		if(parseResult && parseResult['countryCode'])
		{
			this.countryCode = parseResult['countryCode'];
			this.nationalNumber = parseResult['localNumber'];
		}
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.tryToStripCountryCode = function()
	{
		var possibleCountryCode = this.countryMetadata['countryCode'];
		var possibleNationalNumber;
		if(this.nationalNumber.indexOf(possibleCountryCode) === 0)
		{
			possibleNationalNumber = this.nationalNumber.substr(possibleCountryCode.length);
			if(_isNumberPossible(possibleNationalNumber, this.countryMetadata, true, false))
			{
				this.isInternational = true;
				this.countryCode = possibleCountryCode;
				this.nationalNumber = possibleNationalNumber;
			}
		}
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.extractNationalPrefix = function()
	{
		var possibleNationalNumber = _stripNationalPrefix(this.nationalNumber, this.countryMetadata);

		if(possibleNationalNumber !== this.nationalNumber)
		{
			if(!_isNumberPossible(possibleNationalNumber, this.countryMetadata, false, true))
			{
				return false;
			}
			this.hasNationalPrefix = true;
			//this.nationalPrefix = this.nationalNumber.substr(0, this.nationalNumber.length - possibleNationalNumber.length);
			this.nationalPrefix = this.countryMetadata['nationalPrefix'];
			this.nationalNumber = possibleNationalNumber;
			return true;
		}
		return false;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.resetState = function()
	{
		this.country = null;
		this.countryCode = '';
		this.nationalPrefix = '';
		this.nationalNumber = null;
		this.isInternational = false;
		this.hasNationalPrefix = false;
		this.hasPlusChar = false;
		this.selectedFormat = null;
		this.formattedNumber = null;
		this.formattingTemplate = null;
		this.extension = '';
		this.extensionSeparator = '';
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.findSuitableCountry = function()
	{
		var possibleCountry = _findCountry(this.countryCode, this.nationalNumber);

		if(possibleCountry)
			this.country = possibleCountry;
		else
			this.country = _getMainCountryForCode(this.countryCode);

		this.countryMetadata = _getCountryMetadata(this.country);
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.formatNationalNumber = function()
	{
		if(this.isCompleteNumber())
		{
			return this.formatCompleteNumber(this.nationalNumber);
		}

		if(!this.isInternational && this.countryCode === '' && this.nationalPrefix === '' && ShortNumberFormatter.isApplicable(this.rawInput))
		{
			return ShortNumberFormatter.format(this.rawInput);
		}

		if(this.selectFormat())
		{
			this.formattedNumber = this.formatUsingTemplate();

			if(this.isInternational)
			{
				var formattedNumber = (this.hasPlusChar ? plusChar : '') + this.countryCode + ' ' + this.formattedNumber;
			}
			else
			{
				formattedNumber = this.formattedNumber;
			}

			// If no digit was inserted/removed/altered in a process of formatting, return the formatted number;
			var normalizedFormattedNumber = _stripLetters(formattedNumber);
			var normalizedRawInput = _stripLetters(this.rawInput);
			if (normalizedFormattedNumber !== normalizedRawInput)
			{
				formattedNumber = this.rawInput;
			}

			return formattedNumber;
		}
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.isCompleteNumber = function()
	{
		return _getNumberType(this.nationalNumber, this.country) ? true : false;
	};

	/**
	 * Take first format with suitable leading digits
	 * @returns boolean
	 */
	BX.PhoneNumber.IncompleteFormatter.prototype.selectFormat = function()
	{
		var availableFormats = _getAvailableFormats(this.countryMetadata);

		for (var i = 0; i < availableFormats.length; i++)
		{
			var format = availableFormats[i];

			if(!this.isFormatSuitable(format))
				continue;

			if(format.hasOwnProperty('leadingDigits') && !_matchLeadingDigits(this.nationalNumber, format['leadingDigits']))
				continue;

			if(!this.createFormattingTemplate(format))
				continue;

			this.selectedFormat = format;
			return true;

		}
		return false;

	};

	BX.PhoneNumber.IncompleteFormatter.prototype.createFormattingTemplate = function(format)
	{
		var pattern = format['pattern'];

		// The IncompleteFormatter doesn't format numbers when pattern contains "|", e.g. (20|3)\d{4}.
		if(pattern.indexOf('|') !== -1)
			return false;

		this.formattingTemplate = "";
		var possibleTemplate = this.getFormattingTemplate(pattern, format);
		if(possibleTemplate)
		{
			this.formattingTemplate = possibleTemplate;
			return true;
		}
		return false;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.getFormattingTemplate = function(numberPattern, format)
	{
		var numberFormat = _getFormatFormat(format, this.isInternational);

		// replace anything in the form of [..] with \d
		var modifiedPattern = numberPattern.replace(CHARACTER_CLASS_PATTERN, "\\d");

		// Replace any standalone digit (not the one in d{}) with \d
		modifiedPattern = modifiedPattern.replace(STANDALONE_DIGIT_PATTERN, "\\d");

		var longestNumberForPattern = LONGEST_DUMMY_PHONE_NUMBER.match(new RegExp(modifiedPattern))[0];

		// formatting template can not be applied right now if current number length > longest number length

		if(this.nationalNumber.length > longestNumberForPattern.length)
			return false;

		if(this.hasNationalPrefix)
		{
			var nationalPrefixFormattingRule = _getNationalPrefixFormattingRule(format, this.countryMetadata);
			if(nationalPrefixFormattingRule)
			{
				nationalPrefixFormattingRule = nationalPrefixFormattingRule.replace('$NP', this.nationalPrefix).replace('$FG', '$1');
				numberFormat = numberFormat.replace(new RegExp('(\\$\\d)'), nationalPrefixFormattingRule);
			}
			else
			{
				numberFormat = this.nationalPrefix + ' ' + numberFormat;
			}
		}

		// format the longest number according to the numberFormat
		var template = longestNumberForPattern.replace(new RegExp(modifiedPattern, 'g'), numberFormat);
		// replace each digit with the placeholder
		template = template.replace(DUMMY_DIGIT_MATCHER, DIGIT_PLACEHOLDER);
		return template;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.formatUsingTemplate = function()
	{
		if(!this.formattingTemplate)
			return false;

		var result = this.formattingTemplate;
		var lastMatchPosition;

		for(var i = 0; i< this.nationalNumber.length; i++)
		{
			lastMatchPosition = result.search(DIGIT_PLACEHOLDER_MATCHER);
			if(lastMatchPosition === -1)
				return false;

			result = result.replace(DIGIT_PLACEHOLDER_MATCHER, this.nationalNumber[i]);
		}

		result = this.closeLastBracket(result, lastMatchPosition + 1);
		return result;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.closeLastBracket = function(partiallyPopulatedTemplate, cutAfter)
	{
		var remainingTemplatePart = partiallyPopulatedTemplate.substr(cutAfter);

		var openingBracketPosition = remainingTemplatePart.indexOf('(');
		var closingBracketPosition = remainingTemplatePart.indexOf(')');

		if(closingBracketPosition !== -1 && (openingBracketPosition === -1 || openingBracketPosition > closingBracketPosition))
		{
			cutAfter = cutAfter + closingBracketPosition + 1;
		}


		return partiallyPopulatedTemplate.substr(0, cutAfter).replace(DIGIT_PLACEHOLDER_MATCHER_GLOBAL, ' ');
	};

	 BX.PhoneNumber.IncompleteFormatter.prototype.formatCompleteNumber = function()
	{
		var phoneNumber = new BX.PhoneNumber();
		phoneNumber.setRawNumber(this.rawInput);
		phoneNumber.setHasPlus(this.hasPlusChar);
		phoneNumber.setInternational(this.isInternational);
		phoneNumber.setNationalPrefix(this.nationalPrefix);
		phoneNumber.setNationalNumber(this.nationalNumber);
		phoneNumber.setCountry(this.country);
		phoneNumber.setCountryCode(this.countryCode);

		var format = BX.PhoneNumberFormatter.selectOriginalFormatForNumber(phoneNumber);

		if(!format)
			return false;

		var formattedNumber = BX.PhoneNumberFormatter.formatNationalNumberWithOriginalFormat(phoneNumber, format);

		if(this.isInternational)
		{
			formattedNumber = (this.hasPlusChar ? plusChar : '') + this.countryCode + ' ' + formattedNumber;
		}

		this.selectedFormat = format;
		return formattedNumber;
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.isFormatSuitable = function(format)
	{
		if(this.isInternational)
		{
			return _getInternationalFormat(format) ? true : false;
		}
		else
		{
			return !this.hasNationalPrefix || _isNationalPrefixSupported(format, this.countryMetadata);
		}
	};

	BX.PhoneNumber.IncompleteFormatter.prototype.replaceCountry = function (country)
	{
		this.isInternational = true;
		this.hasPlusChar = true;
		this.country = country;
		this.countryMetadata = _getCountryMetadata(this.country);
		this.countryCode = this.countryMetadata['countryCode'];
		this.rawInput = '+' + this.countryCode + this.nationalNumber;
		this.nationalPrefix = '';
	};

	/**
	 *
	 * @param {object} params
	 * @param {Element} params.node
	 * @param {Element} [params.flagNode]
	 * @param {int} [params.flagSize] 16, 24 or 32
	 * @param {string} [params.defaultCountry]
	 * @param {string} [params.userDefaultCountry]
	 * @param {string} [params.savedCountryCode]
	 * @param {int} [params.countryPopupHeight] 180
	 * @param {string} [params.countryPopupClassName] ''
	 * @param {Array} [params.countryTopList]
	 * @param {boolean} [params.forceLeadingPlus]
	 * @param {Function} [params.onInitialize]
	 * @param {Function} [params.onChange]
	 * @param {Function} [params.onCountryChange]
	 * @constructor
	 */
	BX.PhoneNumber.Input = function(params)
	{
		if(!BX.type.isDomNode(params.node) || params.node.nodeName !== 'INPUT' || params.node.type !== 'text')
		{
			throw new Error("params.node should be text input node");
		}

		this.inputNode = params.node;
		this.defaultCountry = params.defaultCountry || BX.PhoneNumber.getDefaultCountry();
		this.userDefaultCountry = params.userDefaultCountry || BX.PhoneNumber.getUserDefaultCountry();
		this.savedCountryCode = params.savedCountryCode || '';
		this.forceLeadingPlus = params.forceLeadingPlus === true;
		this.flagNode = BX.type.isDomNode(params.flagNode) ? params.flagNode : null;
		this.flagSize = ([16, 24, 32].indexOf(params.flagSize) !== -1) ? params.flagSize : 16;
		this.countryPopupHeight = params.countryPopupHeight || 180;
		this.countryPopupClassName = params.countryPopupClassName || '';
		this.countryTopList = params.countryTopList || [];
		this.flagNodeInitialClass = '';

		this.countries = null;

		this.callbacks = {
			initialize: BX.type.isFunction(params.onInitialize) ? params.onInitialize : BX.DoNothing,
			change: BX.type.isFunction(params.onChange) ? params.onChange : BX.DoNothing,
			countryChange: BX.type.isFunction(params.onCountryChange) ? params.onCountryChange : BX.DoNothing
		};

		this.formatter = null;
		this.countrySelectPopup = null;

		this._lastCaretPosition = null;
		this._digitsToTheLeft = 0;
		this._digitsToTheRight = 0;
		this._digitsCount = 0;
		this._selectedDigitsBeforeAction = 0;
		this._countryBefore = '';

		this.initialized = false;
		this.initializationPromises = [];

		this.init();
		this.bindEvents();
	};

	BX.PhoneNumber.Input.prototype.init = function()
	{
		var self = this;

		if(this.flagNode)
		{
			this.flagNodeInitialClass = this.flagNode.className;
			BX.adjust(this.flagNode, {style: {
				cursor: "pointer",
				display: "inline-block"
			}});
		}

		BX.PhoneNumber.getIncompleteFormatter(this.defaultCountry).then(function(formatter)
		{
			self.formatter = formatter;

			if(self.inputNode.value)
			{
				self.inputNode.value = self.formatter.format(self.inputNode.value);

				if (
					BX.Type.isStringFilled(self.savedCountryCode)
					&& self.formatter.country !== self.savedCountryCode
				)
				{
					self.formatter.replaceCountry(self.savedCountryCode);
				}
			}
			else if(self.userDefaultCountry != '')
			{
				self.formatter.replaceCountry(self.userDefaultCountry);
				self.inputNode.value = self.userDefaultCountry === 'XX' ? '' : self.formatter.getFormattedNumber();
			}
			self.drawCountryFlag();
			self.initialized = true;
			self.initializationPromises.forEach(function(promise)
			{
				promise.resolve();
			});
			self.callbacks.initialize();
		});
	};

	BX.PhoneNumber.Input.prototype.bindEvents = function ()
	{
		this.inputNode.addEventListener('keydown', this._onKeyDown.bind(this));
		this.inputNode.addEventListener('input', this._onInput.bind(this));
		if(this.flagNode)
		{
			this.flagNode.addEventListener('click', this._onFlagClick.bind(this));
		}
	};

	BX.PhoneNumber.Input.prototype.setValue = function (newValue)
	{
		this.waitForInitialization().then(function()
		{
			this.inputNode.value = this.formatter.format(newValue.toString());
			this.callbacks.change({
				value: this.getValue(),
				formattedValue: this.getFormattedValue(),
				country: this.getCountry(),
				countryCode: this.getCountryCode()
			});

			if(this._countryBefore !== this.getCountry())
			{
				this.drawCountryFlag();
				this.callbacks.countryChange({
					country: this.getCountry(),
					countryCode: this.getCountryCode()
				});
			}
		}.bind(this));
	};

	BX.PhoneNumber.Input.prototype.waitForInitialization = function()
	{
		var result = new BX.Promise();

		if(this.initialized)
		{
			result.resolve();
			return result;
		}

		this.initializationPromises.push(result);
		return result;
	};

	BX.PhoneNumber.Input.prototype.getValue = function()
	{
		return _stripNonSignificantChars(this.inputNode.value);
	};

	BX.PhoneNumber.Input.prototype.getFormattedValue = function()
	{
		return this.inputNode.value;
	};

	BX.PhoneNumber.Input.prototype.getCountry = function()
	{
		return this.formatter.country || this.formatter.defaultCountry;
	};

	BX.PhoneNumber.Input.prototype.getCountryCode = function()
	{
		var countryMetadata = _getCountryMetadata(this.getCountry());
		return (countryMetadata ? countryMetadata['countryCode'] : false);
	};

	BX.PhoneNumber.Input.prototype.drawCountryFlag = function ()
	{
		if (!this.flagNode)
			return;

		var country = this.getCountry();
		if (!BX.type.isNotEmptyString(country))
			return;

		country = country.toLowerCase();
		BX.adjust(this.flagNode, {props: {className: this.flagNodeInitialClass + " bx-flag-" + this.flagSize + " " + country}});
	};

	BX.PhoneNumber.Input.prototype.tryRedrawCountryFlag = function()
	{
		if (this._countryBefore !== this.getCountry())
		{
			this.drawCountryFlag();

			this.callbacks.countryChange({
				country: this.getCountry(),
				countryCode: this.getCountryCode()
			});
		}
	};

	BX.PhoneNumber.Input.prototype._onKeyDown = function (e)
	{
		if(!e.key)
			return;
		var selectedCount = this.inputNode.selectionEnd - this.inputNode.selectionStart;
		//skip letters
		if(e.key === plusChar)
		{
			//allow + char only in the beginning
			if(this.inputNode.selectionStart !== 0)
			{
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		}
		else if(e.key.length === 1 && e.key.search(/[\d,;#]/) !== 0 && !e.ctrlKey && !e.metaKey)
		{
			e.preventDefault();
			e.stopPropagation();
			return;
		}

		var digitsPositions = _getDigitPositions(this.inputNode.value);

		//remember cursor position before keyboard input
		this._lastCaretPosition = this.inputNode.selectionStart;
		this._digitsToTheLeft = _countMatches(significantChars, this.inputNode.value.substr(0, this._lastCaretPosition));
		this._digitsToTheRight = _countMatches(significantChars, this.inputNode.value.substr(this._lastCaretPosition));
		this._digitsCount = _countMatches(significantChars, this.inputNode.value);
		this._countryBefore = this.getCountry();

		if(selectedCount > 0)
		{
			var selectedFragment = this.inputNode.value.substr(this.inputNode.selectionStart, selectedCount);
			this._selectedDigitsBeforeAction = _countMatches(significantChars, selectedFragment);
		}
		else
		{
			this._selectedDigitsBeforeAction = 0;
		}

		//move cursor so it would delete digit instead of formatting
		var newCaretPosition = null;
		if(e.key === 'Backspace' && selectedCount === 0)
		{
			newCaretPosition = digitsPositions[this._digitsToTheLeft - 1] + 1;
		}

		if(e.key === 'Delete' && selectedCount === 0 && this._digitsToTheRight > 0)
		{
			newCaretPosition = digitsPositions[this._digitsToTheLeft];
		}

		if(newCaretPosition !== null)
		{
			this.inputNode.setSelectionRange(newCaretPosition, newCaretPosition);
		}
	};

	BX.PhoneNumber.Input.prototype._onInput = function(e)
	{
		var caretPosition = null;

		if(this.formatter)
		{
			var formattedValue = this.formatter.format(this.inputNode.value);
			var digitsPositions = _getDigitPositions(formattedValue);
			var digitsBefore = this._digitsCount;
			var digitsDeleted = this._selectedDigitsBeforeAction;
			var digitsAfter = _countMatches(significantChars, formattedValue);
			var digitsDelta = digitsAfter - digitsBefore;
			var digitsInserted = digitsDelta + digitsDeleted;

			// restore caret position
			if(this._lastCaretPosition !== null)
			{
				switch (e.inputType)
				{
					case 'deleteContentBackward':
						//backspace is pressed, need to move cursor one position left if one symbol was deleted
						if(digitsDelta == -1)
							caretPosition = digitsPositions[this._digitsToTheLeft + digitsDelta - 1] + 1;
						else
							caretPosition = digitsPositions[this._digitsToTheLeft];
						break;
					case 'deleteContentForward':
						//delete is pressed, don't move cursor
						if(this._digitsToTheLeft === 0)
						{
							caretPosition = digitsPositions[0];
						}
						else
						{
							caretPosition = digitsPositions[this._digitsToTheLeft - 1] + 1;
						}
						break;
					case 'insertText':
					case 'insertFromPaste':
						//move caret after inserted digits
						caretPosition = digitsPositions[this._digitsToTheLeft - 1 + digitsInserted] + 1;

						break;
				}
			}

			this.inputNode.value = formattedValue;
			if(caretPosition !== null)
			{
				this.inputNode.setSelectionRange(caretPosition, caretPosition);
			}

			this.callbacks.change({
				value: this.getValue(),
				formattedValue: this.getFormattedValue(),
				country: this.getCountry(),
				countryCode: this.getCountryCode()
			});

			this.tryRedrawCountryFlag();
		}
		this._lastCaretPosition = null;
	};

	BX.PhoneNumber.Input.prototype._onFlagClick = function (e)
	{
		/*if(this.formatter.nationalNumber != '')
			return;*/

		this.selectCountry({
			node: this.flagNode,
			countryPopupHeight: this.countryPopupHeight,
			countryPopupClassName: this.countryPopupClassName,
			countryTopList: this.countryTopList,
			onSelect: this._onCountrySelect.bind(this)
		});
	};

	BX.PhoneNumber.Input.prototype._onCountrySelect = function(e)
	{
		var country = e.country;
		if(country === this.getCountry())
			return false;

		this.formatter.replaceCountry(country);
		this.inputNode.value = this.formatter.getFormattedNumber();
		this.drawCountryFlag();
		this.callbacks.change({
			value: this.getValue(),
			formattedValue: this.getFormattedValue(),
			country: this.getCountry(),
			countryCode: this.getCountryCode()
		});
		this.callbacks.countryChange({
			country: this.getCountry(),
			countryCode: this.getCountryCode()
		});
		BX.userOptions.save('main', 'phone_number', 'default_country', country);
	};

	BX.PhoneNumber.Input.prototype.loadCountries = function()
	{
		var result = new BX.Promise();
		if(this.countries)
		{
			result.fulfill();
			return result;
		}

		BX.ajax.runAction("main.phonenumber.getCountries").then(function(response)
		{
			this.countries = response.data;
			result.fulfill();
		}.bind(this)).catch(function(response)
		{
			if(response.errors)
			{
				response.errors.map(function(error)
				{
					console.error(error.message);
				});
			}
			else
			{
				console.error(response);
			}
		});
		return result;
	};

	BX.PhoneNumber.Input.prototype.selectCountry = function (params)
	{
		var self = this;
		var onSelect  = BX.type.isFunction(params.onSelect) ? params.onSelect : BX.DoNothing;
		var popupContent = BX.create("span", {
			events: {
				click: BX.delegateEvent(
					{
						attribute: 'data-country',
					},
					function()
					{
						self.countrySelectPopup.close();
						onSelect({
							country: this.getAttribute('data-country')
						});
					}
				)
			}
		});

		var separator = null;
		var topList = {};
		if(params.countryTopList && params.countryTopList.length > 0)
		{
			separator = popupContent.appendChild(
				BX.create('span', {props: {className: 'main-phonenumber-country-separator'}})
			);
		}

		this.loadCountries().then(function()
		{
			self.countries.forEach(function(countryDescriptor)
			{
				var country = countryDescriptor.CODE;
				var countryCode = _getCountryCode(country);

				if(!countryCode)
					return;


				var countryNode = popupContent.appendChild(BX.create("div", {
					props: {className: "main-phonenumber-country"},
					attrs: {'data-country': countryDescriptor.CODE},
					children: [
						BX.create("span", {
							props: {className: "main-phonenumber-country-flag bx-flag-16 " + country.toLowerCase()}
						}),
						BX.create("span", {
							props: {className: "main-phonenumber-country-name"},
							text: countryDescriptor.NAME + ' (+' + countryCode + ')'
						})
					]
				}));

				if(params.countryTopList.indexOf(countryDescriptor.CODE) >= 0)
				{
					topList[countryDescriptor.CODE] = countryNode.cloneNode(true);
				}
			});

			if(params.countryTopList && params.countryTopList.length > 0)
			{
				params.countryTopList.forEach(function(countryCode)
				{
					if(typeof topList[countryCode] !== 'undefined')
					{
						popupContent.insertBefore(topList[countryCode], separator);
					}
				});

				if (popupContent.firstChild === separator)
				{
					popupContent.removeChild(separator);
				}
			}

			self.countrySelectPopup = new BX.PopupWindow(
				'phoneNumberInputSelectCountry',
				params.node,
				{
					className: params.countryPopupClassName || '',
					autoHide: true,
					closeByEsc: true,
					bindOptions: {
						position: 'top'
					},
					height: params.countryPopupHeight,
					offsetRight: 35,
					padding: 0,
					contentPadding: 10,
					angle: {
						offset: 33
					},
					overlay: {
						backgroundColor: 'white',
						opacity: 0
					},
					content: popupContent,
					events: {
						onPopupClose : function()
						{
							self.countrySelectPopup.destroy();
						},
						onPopupDestroy: function ()
						{
							self.countrySelectPopup = null;
						}
					}
				}
			);
			self.countrySelectPopup.show();
		});
	};

// Internal functions

	var ShortNumberFormatter = {
		templates: {
			3: 'x-xx',
			4: 'xx-xx',
			5: 'x-xx-xx',
			6: 'xx-xx-xx',
			7: 'xxx-xx-xx'
		},

		/**
		 *
		 * @param {string} rawNumber
		 * @return string
		 */
		format: function(rawNumber)
		{
			var template = this.templates[rawNumber.length];
			if(!template)
			{
				return rawNumber;
			}

			var i = 0;
			var pattern = new RegExp(template.replace(/[^x]/g, "").replace(/x/g, "(\\d)"));
			var format = template.replace(/x/g, function (){ return "$" + ++i;});

			return rawNumber.replace(pattern, format);
		},

		/**
		 * Return true if the phone number could be formatted using this formatter and false otherwise.
		 * @param {string} rawNumber
		 * @return boolean
		 */
		isApplicable: function(rawNumber)
		{
			return /^\d{3,7}$/.test(rawNumber);
		}
	};

	/**
	 * Extracts phone number from the input string.
	 * @param {string} phoneNumber Phone number.
	 * @return string
	 */
	var _extractFormattedPhoneNumber = function(phoneNumber)
	{
		if (!phoneNumber || phoneNumber.length > MAX_INPUT_STRING_LENGTH)
		{
			return '';
		}

		var startsAt = phoneNumber.search(new RegExp(phoneNumberStartPattern));

		// Attempt to extract a possible number from the string passed in
		if (startsAt < 0)
		{
			return '';
		}

		var result = phoneNumber.substr(startsAt);
		result = result.replace(new RegExp(afterPhoneNumberEndPattern), '');
		return result;
	};

	/**
	 * Returns country code and local number for the provided international phone number.
	 * @param {string} phoneNumber Phone number in international format.
	 * @return object|boolean
	 */
	var _parsePhoneNumberAndCountryPhoneCode = function(phoneNumber)
	{
		phoneNumber = _stripNonSignificantChars(phoneNumber);
		if(!phoneNumber)
			return false;

		// If this is not an international phone number,
		// then don't extract country phone code.
		if (phoneNumber[0] !== plusChar)
		{
			return {
				'countryCode': '',
				'localNumber': phoneNumber
			};
		}

		// Strip the leading '+' sign
		phoneNumber = phoneNumber.substr(1);

		// Fast abortion: country codes do not begin with a '0'
		if (phoneNumber[0] === '0')
		{
			return false;
		}

		for (var i = MAX_LENGTH_COUNTRY_CODE; i > 0; i--)
		{
			var countryCode = phoneNumber.substr(0, i);
			if(_isValidCountryCode(countryCode))
			{
				return {
					'countryCode': countryCode,
					'localNumber': phoneNumber.substr(i)
				};
			}
		}
		return false;
	};

	/**
	 * Returns true if the specified string matches general phone number pattern.
	 * @param {string} phoneNumber Phone number.
	 * @return boolean
	 */
	var _isViablePhoneNumber = function(phoneNumber)
	{
		return phoneNumber.length >= MIN_LENGTH_FOR_NSN && (phoneNumber.search(new RegExp(validPhoneNumberPattern)) !== -1);
	};

	/**
	 *
	 * @param {string} phoneNumber
	 * @private
	 */
	var _stripExtension = function(phoneNumber)
	{
		var extension = "";
		var extensionSeparator = "";
		var separatorPosition = phoneNumber.search(new RegExp("[" + extensionSeparators + "]"));

		if(separatorPosition >= 0)
		{
			extensionSeparator = phoneNumber[separatorPosition];
			extension = phoneNumber.substr(separatorPosition);
			phoneNumber = phoneNumber.substr(0, separatorPosition);
		}

		return {
			extensionSeparator: extensionSeparator,
			extension: _stripEverythingElse(extension, extensionSymbols + validDigits),
			phoneNumber: phoneNumber
		};
	};

	/**
	 * Returns metadata for the first country with specified countryCode.
	 * @param {string} countryCode Phone code of the country
	 * @return object | false
	 */
	var _getMetadataByCountryCode = function(countryCode)
	{
		if(!_isValidCountryCode(countryCode))
		{
			return false;
		}

		var countries = _getCountriesByCode(countryCode);
		return _getCountryMetadata(countries[0]);
	};

	/**
	 * Returns 2-symbol country code by localNumber.
	 * @param {string} countryCode Phone code of the country.
	 * @param {string} localNumber Local phone number.
	 * @return string|boolean
	 */
	var _findCountry = function(countryCode, localNumber)
	{
		if(!countryCode || !localNumber)
			return false;

		var possibleCountries = _getCountriesByCode(countryCode);
		var possibleCountry;
		var countryMetadata;
		if(possibleCountries.length === 1)
		{
			return possibleCountries[0];
		}

		for (var i = 0; i < possibleCountries.length; i++)
		{
			possibleCountry = possibleCountries[i];
			countryMetadata = _getCountryMetadata(possibleCountry);

			// Check leading digits first
			if(countryMetadata.hasOwnProperty('leadingDigits'))
			{
				var leadingDigitsRegex = '^(' + countryMetadata['leadingDigits'] + ')';
				if(localNumber.match(new RegExp(leadingDigitsRegex)))
				{
					return possibleCountry;
				}
			}
			// Else perform full validation with all of those bulky fixed-line/mobile/etc regular expressions.
			else if(_getNumberType(localNumber, possibleCountry))
			{
				return possibleCountry;
			}
		}

		return false;
	};

	/**
	 * Returns type of the specified number.
	 * @param {string} localNumber Local phone number.
	 * @param {string} country 2-symbol country code.
	 * @return string|boolean
	 */
	var _getNumberType = function(localNumber, country)
	{
		// Check that the number is valid for this country
		var countryMetadata = _getCountryMetadata(country);
		var possibleType;
		if(!countryMetadata)
			return false;

		if(!BX.type.isNotEmptyString(localNumber))
			return false;

		if((countryMetadata['generalDesc'] && countryMetadata['generalDesc']['nationalNumberPattern']))
		{
			if(!localNumber.match(new RegExp('^(?:' + countryMetadata['generalDesc']['nationalNumberPattern'] + ')$')))
				return false;
		}

		var possibleTypes = ['noInternationalDialling', 'areaCodeOptional', 'fixedLine', 'mobile', 'pager', 'tollFree', 'premiumRate', 'sharedCost', 'personalNumber', 'voip', 'uan', 'voicemail'];
		for(var i = 0; i < possibleTypes.length; i++)
		{
			possibleType = possibleTypes[i];
			if((countryMetadata[possibleType] && countryMetadata[possibleType]['nationalNumberPattern']))
			{
				// skip checking possible lengths for now

				if(localNumber.match(new RegExp('^' + countryMetadata[possibleType]['nationalNumberPattern'] + '$')))
				{
					return possibleType;
				}
			}
		}
		return false;
	};

	/**
	 * Strips national prefix from the specified phone number. Returns true if national prefix
	 * was stripped and false otherwise.
	 * @param {string} phoneNumber Local phone number.
	 * @param {object} countryMetadata Country metadata.
	 * @return string
	 */
	var _stripNationalPrefix = function(phoneNumber, countryMetadata)
	{
		var nationalPrefixForParsing = countryMetadata.hasOwnProperty('nationalPrefixForParsing') ? countryMetadata['nationalPrefixForParsing']: countryMetadata['nationalPrefix'];

		if(phoneNumber == '' || nationalPrefixForParsing == '')
			return phoneNumber;

		var nationalPrefixRegex = '^(?:' + nationalPrefixForParsing + ')';
		var nationalPrefixMatches = phoneNumber.match(new RegExp(nationalPrefixRegex));
		if(!nationalPrefixMatches)
		{
			//if national prefix is omitted, nothing to strip
			return phoneNumber;
		}

		var nationalPrefixTransformRule = countryMetadata['nationalPrefixTransformRule'];
		var nationalSignificantNumber;
		if(nationalPrefixTransformRule && nationalPrefixMatches.length > 1)
		{
			nationalSignificantNumber = phoneNumber.replace(nationalPrefixRegex, nationalPrefixTransformRule);
		}
		else
		{
			// No transformation is required, just strip the prefix
			nationalSignificantNumber = phoneNumber.substr(nationalPrefixMatches[0].length);
		}

		return nationalSignificantNumber;
	};

	var _isNumberValid = function(phoneNumber, countryMetadata)
	{
		var nationalNumberRegex = new RegExp('^(?:' + countryMetadata['generalDesc']['nationalNumberPattern'] + ')$');
		if(phoneNumber.match(nationalNumberRegex, phoneNumber))
			return true;
		else
			return false;
	};

	/**
	 * Phone number is possible if there is at least one format, suitable for formatting this number.
	 * @param phoneNumber
	 * @param countryMetadata
	 * @param isInternational
	 * @param hasNationalPrefix
	 * @private
	 */
	var _isNumberPossible = function(phoneNumber, countryMetadata, isInternational, hasNationalPrefix)
	{
		if(!countryMetadata['availableFormats'])
			return true;

		for(var i = 0; i < countryMetadata.availableFormats.length; i++)
		{
			var format = countryMetadata.availableFormats[i];
			if(isInternational && format['intlFormat'] === 'NA')
				continue;

			if(hasNationalPrefix)
			{
				var nationalPrefixFormattingRule = _getNationalPrefixFormattingRule(format, countryMetadata);
				if(nationalPrefixFormattingRule && nationalPrefixFormattingRule.search(/\$NP/) === -1)
					continue;
			}

			if(format['leadingDigits'] && !_matchLeadingDigits(phoneNumber, format['leadingDigits']))
				continue

			return true;
		}

		return false;
	};

	/**
	 * Strips country code from the number. Returns true if country code was stripped or false otherwise.
	 * @param {string} phoneNumber Phone number.
	 * @param {object} countryMetadata Country metadata.
	 * @return string
	 */
	var _stripCountryCode = function(phoneNumber, countryMetadata)
	{
		var countryCode = countryMetadata['countryCode'];
		if(phoneNumber.search(countryCode) !== 0)
			return phoneNumber;

		var possibleLocalNumber = phoneNumber.substr(countryCode.length);
		var nationalNumberRegex = new RegExp('^(?:' + countryMetadata['generalDesc']['nationalNumberPattern'] + ')$');

		if(phoneNumber.match(nationalNumberRegex) && !possibleLocalNumber.match(nationalNumberRegex))
		{
			/*
			 If the original number (before stripping national prefix) was viable, and the resultant number is not,
			 then prefer the original phone number. This is because for some countries (e.g. Russia) the same digit
			 could be both a national prefix and a leading digit of a valid national phone number, like `8` is the
			 national prefix for Russia and both `8 800 555 35 35` and `800 555 35 35` are valid numbers.
			 */
			return phoneNumber;
		}

		return possibleLocalNumber;
	};

	var _isValidCountryCode = function(countryCode)
	{
		countryCode = countryCode.toString();
		return codeToCountries.hasOwnProperty(countryCode);
	};

	var _getCountriesByCode = function(countryCode)
	{
		countryCode = countryCode.toString();
		return codeToCountries.hasOwnProperty(countryCode) ? codeToCountries[countryCode] : [];
	};

	var _getMainCountryForCode = function(countryCode)
	{
		countryCode = countryCode.toString();
		return codeToCountries.hasOwnProperty(countryCode) ? codeToCountries[countryCode][0] : false;
	};

	var _getCountryMetadata = function(country)
	{
		country = country.toUpperCase();
		return metadata.hasOwnProperty(country) ? metadata[country] : false;
	};

	var _getCountryCode = function(country)
	{
		country = country.toUpperCase();
		return metadata.hasOwnProperty(country) ? metadata[country]['countryCode'] : false;
	};

	var _getInternationalFormat = function(format)
	{
		if(format.hasOwnProperty('intlFormat'))
		{
			if(format['intlFormat'] === 'NA')
				return false;
			else
				return format['intlFormat'];
		}
		return format['format'];
	};

	var _getAvailableFormats = function(countryMetadata)
	{
		if(BX.type.isArray(countryMetadata['availableFormats']))
			return countryMetadata['availableFormats'];

		var countryCode = countryMetadata['countryCode'];
		var countriesForCode = _getCountriesByCode(countryCode);
		var mainCountry = countriesForCode[0];
		var mainCountryMetadata = _getCountryMetadata(mainCountry);
		return BX.type.isArray(mainCountryMetadata['availableFormats']) ? mainCountryMetadata['availableFormats'] : [];

	};

	var _getNationalPrefix = function(countryMetadata, stripNonDigits)
	{
		if(!countryMetadata.hasOwnProperty('nationalPrefix'))
		{
			return '';
		}

		var nationalPrefix = countryMetadata['nationalPrefix'];
		if (stripNonDigits)
		{
			nationalPrefix = _stripLetters(nationalPrefix);
		}
		return nationalPrefix;
	};

	var _getNationalPrefixFormattingRule = function (format, countryMetadata)
	{
		if(format.hasOwnProperty('nationalPrefixFormattingRule'))
		{
			return format['nationalPrefixFormattingRule'];
		}
		else
		{
			var countryCode = countryMetadata['countryCode'];
			var countriesForCode = _getCountriesByCode(countryCode);
			var mainCountry = countriesForCode[0];
			var mainCountryMetadata = _getCountryMetadata(mainCountry);

			return mainCountryMetadata['nationalPrefixFormattingRule'] || '';
		}
	};

	var _numberContainsNationalPrefix = function(phoneNumber, nationalPrefix, countryMetadata)
	{
		if (phoneNumber.indexOf(nationalPrefix) === 0)
		{
			// Some Japanese numbers (e.g. 00777123) might be mistaken to contain the national prefix
			// when written without it (e.g. 0777123) if we just do prefix matching. To tackle that, we
			// check the validity of the number if the assumed national prefix is removed (777123 won't
			// be valid in Japan).
			var numberWithoutPrefix = phoneNumber.substr(nationalPrefix.length);
			return BX.PhoneNumberParser.getInstance()._realParse(numberWithoutPrefix, countryMetadata['id']).isValid();
		}
		else
		{
			return false;
		}
	};

	var _isNationalPrefixOptional = function(format, countryMetadata)
	{
		if(format.hasOwnProperty('nationalPrefixOptionalWhenFormatting'))
			return format['nationalPrefixOptionalWhenFormatting'];
		else if(countryMetadata.hasOwnProperty('nationalPrefixOptionalWhenFormatting'))
			return countryMetadata['nationalPrefixOptionalWhenFormatting'];
		else
			return false;
	};

	/**
	 * National prefix is supported by the format if:
	 * 1.    Format and country metadata do not have nationalPrefixFormattingRule.
	 * 2. OR Format or country metadata contains nationalPrefixFormattingRule and this formatting rule contains "$NP"
	 * @param format
	 * @param countryMetadata
	 * @returns {boolean}
	 * @private
	 */
	var _isNationalPrefixSupported = function(format, countryMetadata)
	{
		var nationalPrefixFormattingRule = _getNationalPrefixFormattingRule(format, countryMetadata);

		return (!nationalPrefixFormattingRule || nationalPrefixFormattingRule.search(/\$NP/) !== -1);
	};

	var _matchLeadingDigits = function(phoneNumber, leadingDigits)
	{
		var re;
		var matches;
		if(BX.type.isArray(leadingDigits))
		{
			for (var i = 0; i < leadingDigits.length; i++)
			{
				re = new RegExp('^(' + leadingDigits[i] + ')');
				matches = phoneNumber.match(re);
				if(matches)
				{
					return matches;
				}
			}
		}
		else
		{
			re = new RegExp('^(' + leadingDigits + ')');
			matches = phoneNumber.match(re);
			if(matches)
			{
				return matches;
			}
		}
		return false;
	};

	var _getFormatFormat = function(format, international)
	{
		if(international && format.hasOwnProperty('intlFormat'))
			return format['intlFormat'];
		else
			return format['format'];
	};

	/**
	 * Strips all letters from the string.
	 * @param {string} str Input string.
	 * @return string
	 */
	var _stripLetters = function(str)
	{
		return _stripEverythingElse(str, validDigits)
	};

	var _stripNonSignificantChars = function(str)
	{
		return _stripEverythingElse(str, significantChars);
	};

	var _stripEverythingElse = function(str, allowedSymbols)
	{
		return str.replace(new RegExp("[^" + allowedSymbols + "]", "g"), "");
	};

	var _countMatches = function(needle, haystack)
	{
		var matches = haystack.match(needle instanceof RegExp ? needle : new RegExp("[" + needle + "]", 'g'));
		return matches ? matches.length : 0;
	};

	var _getDigitPositions = function(str)
	{
		var re = new RegExp("[" + significantChars + "]", "g");
		var result = [];
		var match;

		while((match = re.exec(str)) !== null)
		{

			result.push(match.index)
		}
		return result;
	};

	function _repeat(str, times)
	{
		var result = '';

		if(times <= 0)
			return '';

		for(var i = 0; i < times; i++) result += str;
		return result;
	}
})();
