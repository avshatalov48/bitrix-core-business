;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var defaultOptions = {
		quality: .80
	};
	var urlToBlob = BX.Landing.Utils.urlToBlob;
	var fileSize = BX.Landing.Utils.fileSize;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isNumber = BX.Landing.Utils.isNumber;

	/**
	 * Implements image compressor interface
	 * @param {Blob|File} file
	 * @param {Object} [options]
	 * @constructor
	 */
	BX.Landing.ImageCompressor = function(file, options)
	{
		this.file = file;
		this.options = Object.assign({}, defaultOptions, filterOptions(options) || {});

		if (this.options.retina)
		{
			void (isNumber(this.options.width) && (this.options.width *= 2));
			void (isNumber(this.options.heigth) && (this.options.heigth *= 2));
			void (isNumber(this.options.maxWidth) && (this.options.maxWidth *= 2));
			void (isNumber(this.options.maxHeight) && (this.options.maxHeight *= 2));
			void (isNumber(this.options.minWidth) && (this.options.minWidth *= 2));
			void (isNumber(this.options.minHeight) && (this.options.minHeight *= 2));
		}
	};

	/**
	 * Removes unsupported options
	 * @param options
	 * @return {{}}
	 */
	function filterOptions(options) {
		options = isPlainObject(options) ? options : {};
		return Object.keys(options).reduce(function(acc, key) {
			return (typeof options[key] !== "undefined" && (acc[key] = options[key])), acc;
		}, {});
	}

	/**
	 * Makes console report
	 * @param {{before, after, quality, retina}} options
	 */
	function report(options) {
		var before = fileSize(options.before);
		var after = fileSize(options.after);
		var quality = options.quality;
		var retina = options.retina ? '@2x' : '';
		console.info('Image compressed', before, '—>', after, '(quality '+(quality*100)+'%)', retina);
	}

	/**
	 * Compress image
	 * @param {Blob|File|String} file
	 * @param {Object} [options]
	 * @return {Promise<Blob, String>}
	 */
	BX.Landing.ImageCompressor.compress = function(file, options)
	{
		return urlToBlob(file)
			.then(function(blob) {
				var compressor = new BX.Landing.ImageCompressor(blob, options);

				return compressor.compress()
					.then(function(outFile) {
						report({
							before: blob.size,
							after: outFile.size,
							quality: compressor.options.quality,
							retina: compressor.options.retina
						});
						return outFile;
					});
			});
	};

	/**
	 * Tests file
	 * @param {Blob|File|String} file
	 * @param {Object} [options]
	 * @return {Promise<{before: number, after: number, printable: {before: string, after: string}} | never>}
	 */
	BX.Landing.ImageCompressor.test = function(file, options)
	{
		var result = {
			before: 0,
			after: 0,
			printable: {
				before: "0 B",
				after: "0 B"
			}
		};

		return urlToBlob(file)
			.then(function(blob) {
				return (new BX.Landing.ImageCompressor(blob, options))
					.compress()
					.then(function(outFile) {
						result.before = blob.size;
						result.after = outFile.size;
						result.printable.before = fileSize(blob.size);
						result.printable.after = fileSize(outFile.size);

						return result;
					});
			});
	};


	BX.Landing.ImageCompressor.prototype = {
		/**
		 * Compress this image
		 * @return {Promise}
		 */
		compress: function() {
			var file = this.file;
			var options = this.options;

			return new Promise(function(resolve, reject) {
				void new ImageCompressor(file, Object.assign({}, options, {success: resolve, error: reject}));
			});
		}
	};
})();