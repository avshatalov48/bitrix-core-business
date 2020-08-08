(function() {
	'use strict';

	BX.namespace('BX.UI.Graph');

	BX.UI.Graph.Circle = function(domNode, perimetr, progressBar, fixCounter) {
		this.domNode = domNode;
		this.perimetr = perimetr;
		this.radius = perimetr / 2;
		this.progressBar = Number(progressBar) > 100 ? 100 : progressBar;
		this.progressBg = null;
		this.number = null;
		this.waves = null;
		this.fixCounter = fixCounter ? fixCounter : null;
	};

	BX.UI.Graph.Circle.prototype =
		{
			getCircumFerence: function() {
				return (this.radius - 10) * 2 * 3.14;
			},

			getCircumProgress: function() {
				return this.getCircumFerence() - (this.getCircumFerence() / 100 * this.progressBar);
			},

			createCircle: function() {
				this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar');
				this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.radius + ' ' + this.radius);
				this.svg.setAttributeNS(null, 'width', this.perimetr);
				this.svg.setAttributeNS(null, 'height', this.perimetr);

				this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressBg.setAttributeNS(null, 'r', this.radius - 10);
				this.progressBg.setAttributeNS(null, 'cx', this.radius);
				this.progressBg.setAttributeNS(null, 'cy', this.radius);
				this.progressBg.setAttributeNS(null, 'class', 'ui-graph-circle-bar-bg');

				this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressMove.setAttributeNS(null, 'r', this.radius - 10);
				this.progressMove.setAttributeNS(null, 'cx', this.radius);
				this.progressMove.setAttributeNS(null, 'cy', this.radius);
				this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircumFerence());
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumFerence());
				this.progressMove.setAttributeNS(null, 'class', 'ui-graph-circle-bar-progress');

				this.svg.appendChild(this.progressBg);
				this.svg.appendChild(this.progressMove);

				return this.svg;
			},

			animateFixedBar: function() {
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate ui-graph-circle-bar-without-animate');
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
			},

			animateProgressBar: function() {
				this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate');
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
			},

			createNumberBlock: function() {
				this.number = BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-number',
						'data-progress': this.progressBar
					}
				});

				return this.number;
			},

			createWavesBlock: function() {
				return BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-waves-wrapper'
					},
					children: [
						this.waves = BX.create('div', {
							attrs: {
								className: 'ui-graph-circle-waves'
							}
						})
					]
				})
			},

			animateWavesBlock: function(fixCounter) {
				var progress = this.progressBar;

				if(fixCounter)
				{
					if (progress <= 50)
					{
						progress = 45;
					}

					if (progress > 50)
					{
						progress = 85;
					}

					this.progressBar <= 25 ? progress = 25 : null;
					this.waves.style.transform = 'translateY(-' + progress + '%)';
				}

				this.progressBar <= 25 ? progress = 25 : null;
				this.waves.style.transform = 'translateY(-' + progress + '%)';
			},

			createWrapper: function() {
				this.graph = BX.create('div', {
					attrs: {
						className: 'ui-graph-circle-wrapper'
					}
				});

				this.graph.appendChild(this.createCircle());
				this.graph.appendChild(this.createNumberBlock());
				this.graph.appendChild(this.createWavesBlock());

				return this.graph;
			},

			addWrapperClass: function() {
				this.graph.classList.add('ui-graph-circle-wrapper-animate');

				if (this.fixCounter)
				{
					this.graph.classList.add('ui-graph-circle-counter');
				}
			},

			animateNumber: function(fixCounter) {
				var progress = this.progressBar;

				if(fixCounter)
				{
					progress = fixCounter;
				}

				if (Number(progress) <= 0)
				{
					if(fixCounter)
					{
						this.number.innerHTML = '0';
					}
					else
					{
						this.number.innerHTML = '0' + ' <span>%</span>';
					}

					return;
				}

				if(fixCounter)
				{
					this.number.innerHTML = fixCounter;
					this.number.innerHTML.length >= 4 ? this.graph.classList.add('ui-graph-font-sm') : null;
				}
				else
				{
					var i = 0;
					var time = 1000 / progress;
					var interval = setInterval(function()
					{
						i++;
						this.number.innerHTML = i + ' <span>%</span>';
						i === Number(progress) ? clearInterval(interval) : null;
					}.bind(this), time);
				}
			},

			updateCounter: function(counter, fixCounter)
			{
				this.progressBar = counter;

				if (fixCounter)
				{
					this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
				}
				else
				{
					this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
				}
				this.animateNumber(fixCounter);
				this.animateWavesBlock(fixCounter);
			},

			show: function() {
				this.domNode.appendChild(this.createWrapper());

				setTimeout(function() {
					this.addWrapperClass();
					this.animateNumber(this.fixCounter);
					if (this.fixCounter)
					{
						this.animateFixedBar();
					}
					else
					{
						this.animateProgressBar();
					}
					this.animateWavesBlock(this.fixCounter);
				}.bind(this), 500);
			}
		};

})();
