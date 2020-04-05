var JCSecurityScanner = (function () {

	var scanner = function(lastResult, options)
	{
		var defaults = {
			actionUrl: '/bitrix/admin/security_scanner.php?lang=' + BX.message('LANGUAGE_ID'),
			checkingInterval: 0
		};

		options = options || {};
		this._options = mergeObjects(defaults, options);
		this._results = lastResult || [];
		this._problemsCount = 0;
		this._started = false;
	};

	scanner.prototype.initialize = function() {
		BX.bind(BX('start_button'), 'click', BX.delegate(this.startStopChecking, this));
		BX.bind(BX('stop_button'), 'click', BX.delegate(this.startStopChecking, this));
		this.onTestingComplete();
	};

	scanner.prototype.getCriticalErrorsContainer = function() {
		var errorsContainerParent = BX.findChild(
			BX('error_container'), {
				tagName: 'div',
				className: 'adm-info-message'
			},
			true
		);
		var errorsContainer = BX.findChild(
			errorsContainerParent, {
				tagName: 'div',
				className: 'adm-info-message-errors'
			}
		);
		if(!errorsContainer) {
			errorsContainer = BX.create('div', {
				props: {
					className: 'adm-info-message-errors'
				}
			});
			errorsContainerParent.appendChild(errorsContainer);
		}
		return errorsContainer;
	};

	scanner.prototype.showCriticalError = function(message, testName) {
		var formatedMessage = '';
		if(!!testName)
			formatedMessage = testName + ': ';
		formatedMessage += message;

		BX.show(BX('error_container'));
		var newError = BX.create('div', {
			html: formatedMessage
		});
		this.getCriticalErrorsContainer().appendChild(newError);
	};

	scanner.prototype.setProblemCount = function(count, criticals) {
		var messageHtml = BX.message('SEC_SCANNER_PROBLEMS_COUNT');
		messageHtml += count || 0;
		messageHtml += BX.message('SEC_SCANNER_CRITICAL_PROBLEMS_COUNT');
		messageHtml += criticals || 0;
		BX('problems_count').innerHTML = messageHtml;
	};

	scanner.prototype.calculateCriticalErrors = function(results) {
		results = results || [];

		return results.filter(function filterHigh(result) {
			return (!!result.critical && result.critical == 'HIGHT');
		}).length;
	};

	scanner.prototype.isStarted = function() {
		return this._started;
	};

	scanner.prototype.initializeTesting = function() {
		this._results = [];
		this._problemsCount = 0;
		this._started = true;
		this.setProgress(0);
		this.setProblemCount(0);
	};

	scanner.prototype.onTestingStart = function() {
		BX.show(BX('results_info'));
		BX.show(BX('status_bar'));
		BX('current_test').innerHTML = BX.message('SEC_SCANNER_INIT');
		BX.hide(BX('last_activity'));
		BX.hide(BX('error_container'));
		BX.hide(BX('start_container'));
		BX.hide(BX('results'));
		BX.hide(BX('first_start'));

		BX.cleanNode(BX('results'));
		BX.cleanNode(this.getCriticalErrorsContainer());
	};

	scanner.prototype.onTestingComplete = function() {
		BX.show(BX('start_container'));
		BX.show(BX('results'));
		this.showTestingResults();
		BX.hide(BX('status_bar'));
	};

	scanner.prototype.createTitleElement = function(title, index) {
		return BX.create('div', {
			props: {
				className: 'adm-security-block-title'
			},
			children: [
				BX.create('span', {
					props: {
						className: 'adm-security-block-num'
					},
					text: index + '.'
				}),
				BX.create('span', {
					props: {
						className: 'adm-security-block-title-name'
					},
					text: title
				}),
				BX.create('span', {
					props: {
						className: 'adm-security-block-status'
					},
					text: BX.message('SEC_SCANNER_CRITICAL_ERROR')
				})
			]
		});
	};

	scanner.prototype.createDetailTextElement = function(text) {
		return BX.create('div', {
			props: {
				className: 'adm-security-block-text'},
			html: text
		});
	};

	scanner.prototype.createAdditionalInfoElement = function(explain) {
		return BX.create('div', {
			props: {
				className: 'adm-security-tip'
			},
			style: {//ToDo: move to css
				'cursor': 'default',
				'margin': '3px 14px'
			},
			children: [
				BX.create('div', {
					props: {
						className: 'adm-security-tip-text'
					},
					children: [
						BX.create('div', {
							style: {//ToDo: move to css
								'font-weight': 'bold',
								'color': '#4a515a',
								'margin-bottom': '3px'
							},
							children: [
								BX.create('span', {
									text: BX.message('SEC_SCANNER_ADDITIONAL_INFO_TITLE')
								})
							]
						}),
						BX.create('div', {
							html: explain
						})
					]
				}),
				BX.create('span', {
					props: {
						className: 'adm-security-tip-link'
					},
					events: {
						'click': function() {
							BX.toggleClass(this.parentNode, 'adm-security-tip-open');
							if(BX.hasClass(this.parentNode, 'adm-security-tip-open')) {
								this.innerHTML = BX.message('SEC_SCANNER_ADDITIONAL_INFO_BUTTON_ON');
							} else {
								this.innerHTML = BX.message('SEC_SCANNER_ADDITIONAL_INFO_TITLE');
							}
						}
					},
					style: {//ToDo: move to css
						cursor: 'pointer'
					},
					text: BX.message('SEC_SCANNER_ADDITIONAL_INFO_TITLE')
				})
			]
		});
	};

	scanner.prototype.createRecommendationElement = function(recommendation) {
		return BX.create('div', {
			props: {
				className: 'adm-security-tip'
			},
			style: {//ToDo: move to css
				'cursor': 'default',
				'margin': '3px 14px'
			},
			children: [
				BX.create('div', {
					props: {
						className: 'adm-security-tip-text'
					},
					children: [
						BX.create('div', {
							style: {//ToDo: move to css
								'font-weight': 'bold',
								'color': '#4a515a',
								'margin-bottom': '3px'
							},
							children: [
								BX.create('span', {
									text: BX.message('SEC_SCANNER_TIP_TITLE')
								})
							]
						}),
						BX.create('div', {
							html: recommendation
						})
					]
				}),
				BX.create('span', {
					props: {
						className: 'adm-security-tip-link'
					},
					events: {
						'click': function() {
							BX.toggleClass(this.parentNode, 'adm-security-tip-open');
							if(BX.hasClass(this.parentNode, 'adm-security-tip-open')) {
								this.innerHTML = BX.message('SEC_SCANNER_TIP_BUTTON_ON');
							} else {
								this.innerHTML = BX.message('SEC_SCANNER_TIP_TITLE');
							}
						}
					},
					style: {//ToDo: move to css
						cursor: 'pointer'
					},
					text: BX.message('SEC_SCANNER_TIP_TITLE')
				})
			]
		});
	};

	scanner.prototype.showTestResult = function(result, index) {
		var container = BX.create('div', {
			props: {
				className: result.critical == 'HIGHT' ? 'adm-security-block adm-security-block-important' : 'adm-security-block'
			}
		});

		container.appendChild(this.createTitleElement(result.title, index));
		var detailText = this.createDetailTextElement(result.detail);
		container.appendChild(detailText);

		if (!!result.additional_info)
		{
			container.appendChild(this.createAdditionalInfoElement(result.additional_info));
		}
		container.appendChild(this.createRecommendationElement(result.recommendation));

		BX('results').appendChild(container);
	};

	scanner.prototype.setProgress = function(progress) {
		BX('progress_text').innerHTML = progress + '%';
		BX('progress_bar_inner').style.width = 500 * progress / 100 + 'px';
	};

	scanner.prototype.setCurrentTest = function(testName) {
		BX('current_test').innerHTML = BX.message('SEC_SCANNER_CURRENT_TEST') + testName;
	};

	scanner.prototype.showTestingResults = function() {
		var results = sortByCritical(this._results);

		for (var i = 0; i < results.length; i++) {
			this.showTestResult(results[i], i + 1);
		}
	};

	scanner.prototype.sendCheckRequest = function(action, data, onSuccess, onFailure) {
		data = data || {};
		data.action = action || 'check';
		data.sessid = BX.bitrix_sessid();
		data = BX.ajax.prepareData(data);

		return BX.ajax({
			'method': 'POST',
			'dataType': 'json',
			'url': this._options.actionUrl,
			'data':  data,
			'onsuccess': onSuccess || BX.delegate(this.processCheckingResults, this),
			'onfailure': onFailure || BX.delegate(this.onRequestFailure, this)
		});
	};

	scanner.prototype.startStopChecking = function() {
		if(this.isStarted()) {
			this._started = false;
			this.onTestingComplete();
		} else {
			this.initializeTesting();
			this.sendCheckRequest('check', {'first_start': 'Y'});
			this.onTestingStart();
		}
	};

	scanner.prototype.retrieveResults = function(results) {
		if(!!results.errors) {
			for (var i = 0; i < results.errors.length; i++) {
				this._results.push(results.errors[i]);
			}
		}

		if(!!results.problem_count) {
			this._problemsCount += parseInt(results.problem_count, 10);
			this.setProblemCount(
				this._problemsCount,
				this.calculateCriticalErrors(this._results)
			);
		}
	};

	scanner.prototype.completeTesting = function() {
		this.onTestingComplete();
		this._started = false;
		this.sendCheckRequest('save', {'results' : this._results});
	};

	scanner.prototype.onRequestFailure = function(reason)
	{
		var message = BX.message('SEC_SCANNER_TESTING_FAILURE');
		if (!!reason)
			message += BX.message('SEC_SCANNER_TESTING_FAILURE_CODE').replace('#CODE#', reason);

		this.showCriticalError(message);
		this.onTestingComplete();
		this._started = false;
	};

	scanner.prototype.processCheckingResults = function(responce) {
		if(!this.isStarted())
			return;

		if(responce == 'ok' || responce == 'error')
			return;

		if(!responce.status) {
			this.retrieveResults(responce);
		}

		if(responce.fatal_error_text) {
			this.showCriticalError(responce.fatal_error_text, responce.name);
		}

		if(responce.all_done === 'Y') {
			this.completeTesting();
		} else {
			var timeOut = this._options.checkingInterval;
			if(responce.timeout) {
				timeOut = responce.timeout;
			}

			setTimeout(
				BX.delegate(
					function nextStep() {
						this.sendCheckRequest();
					},
					this
				),
				timeOut * 1000
			);
		}

		if(!!responce.percent) {
			this.setProgress(responce.percent);
		}

		if(!!responce.name) {
			this.setCurrentTest(responce.name);
		}
	};

	function getCriticalSortValue(key) {
		switch (key) {
			case 'LOW':
				return 3;
			case 'MIDDLE':
				return 2;
			case 'HIGHT':
				return 1;
		}
		return 0;
	}

	function sortByCritical(results) {
		return results.sort(function(a,b) {
			return getCriticalSortValue(a.critical) - getCriticalSortValue(b.critical);
		});
	}

	function mergeObjects(origin, add) {
		for (var p in add) {
			if (!add.hasOwnProperty(p))
				continue;

			if (add[p] && add[p].constructor === Object) {
				if (origin[p] && origin[p].constructor === Object) {
					origin[p] = mergeObjects(origin[p], add[p]);
				} else {
					origin[p] = clone(add[p]);
				}
			} else {
				origin[p] = add[p];
			}
		}
		return origin;
	}

	function clone(o) {
		return JSON.parse(JSON.stringify(o));
	}

	return scanner;
}());

(function fixIE8() {
	if (!BX.browser.IsIE() && !BX.browser.IsIE9())
		return;


	if (!Array.prototype.map) {
		Array.prototype.map = function(fun) {
			var res = [];
			for(var i = 0; i < this.length; i++) {
				res.push(fun(this[i]));
			}

			return res;
		};
	}

	if (!Array.prototype.filter) {
		//https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/filter
		Array.prototype.filter = function(fun /*, context */) {
			if (this === void 0 || this === null)
				throw new TypeError();

			var t = Object(this);
			var len = t.length >>> 0;
			if (typeof fun !== "function")
				throw new TypeError();

			var res = [];
			var context = arguments[1];
			for (var i = 0; i < len; i++) {
				if (i in t) {
					var val = t[i]; // in case fun mutates this
					if (fun.call(context, val, i, t))
						res.push(val);
				}
			}

			return res;
		};
	}
}());

(function initialize() {
	function initScanner() {
		var messages = BX('scanner_messages');
		var results = BX('scanner_results');
		if (!messages || !results)
			return;

		BX.message(JSON.parse(messages.innerHTML));
		results = JSON.parse(results.innerHTML);
		var securityScanner = new JCSecurityScanner(results);
		securityScanner.initialize();
	}

	BX.ready(initScanner);
})();