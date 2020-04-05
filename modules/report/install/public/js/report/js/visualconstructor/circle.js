;(function ()
{
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Graph");


	BX.Report.VisualConstructor.Graph.Circle = function(domNode, perimetr, progressBar) {
		this.domNode = domNode;
		this.perimetr = perimetr;
		this.radius = perimetr / 2;
		this.progressBar = progressBar;
		this.progressBg = null;
		this.number = null;
		this.waves = null;
	};

	BX.Report.VisualConstructor.Graph.Circle.prototype =	{
		getCircumFerence: function() {
			return (this.radius - 10) * 2 * 3.14;
		},

		getCircumProgress: function() {
			return this.getCircumFerence() - (this.getCircumFerence() / 100 * this.progressBar);
		},

		createCircle: function() {
			this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
			this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar');
			this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.radius + ' ' + this.radius);
			this.svg.setAttributeNS(null, 'width', this.perimetr);
			this.svg.setAttributeNS(null, 'height', this.perimetr);

			this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			this.progressBg.setAttributeNS(null, 'r', this.radius - 10);
			this.progressBg.setAttributeNS(null, 'cx', this.radius);
			this.progressBg.setAttributeNS(null, 'cy', this.radius);
			this.progressBg.setAttributeNS(null, 'class', 'task-report-circle-bar-bg');

			this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			this.progressMove.setAttributeNS(null, 'r', this.radius - 10);
			this.progressMove.setAttributeNS(null, 'cx', this.radius);
			this.progressMove.setAttributeNS(null, 'cy', this.radius);
			this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircumFerence());
			this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumFerence());
			this.progressMove.setAttributeNS(null, 'class', 'task-report-circle-bar-progress');

			this.svg.appendChild(this.progressBg);
			this.svg.appendChild(this.progressMove);

			return this.svg;
		},

		animateProgressBar: function() {
			this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar task-report-circle-bar-animate');
			this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
		},

		createNumberBlock: function() {
			this.number = BX.create('div', {
				attrs: {
					className: 'task-report-circle-number',
					'data-progress': this.progressBar
				}
			});

			return this.number;
		},

		createWavesBlock: function() {
			return BX.create('div', {
				attrs: {
					className: 'task-report-circle-waves-wrapper'
				},
				children: [
					this.waves = BX.create('div', {
						attrs: {
							className: 'task-report-circle-waves'
						}
					})
				]
			})
		},

		animateWavesBlock: function() {
			var progress = this.progressBar;
			this.progressBar <= 25 ? progress = 25 : null;
			this.waves.style.transform = 'translateY(-' + progress + '%)';
		},

		createWrapper: function() {
			this.graph = BX.create('div', {
				attrs: {
					className: 'task-report-circle-wrapper'
				}
			});

			this.graph.appendChild(this.createCircle());
			this.graph.appendChild(this.createNumberBlock());
			this.graph.appendChild(this.createWavesBlock());

			return this.graph;
		},

		addWrapperClass: function() {
			this.graph.classList.add('task-report-circle-wrapper-animate')
		},

		animateNumber: function() {
			var i = 0;
			var time = 1000 / this.progressBar;
			var interval = setInterval(function() {
				i++;
				this.number.innerHTML = i;
				i === this.progressBar ? clearInterval(interval) : null;
			}.bind(this), time);
		},

		updateCounter: function(counter)
		{
			this.progressBar = counter;

			this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
			this.animateNumber();
			this.animateWavesBlock();
		},

		show: function() {
			this.domNode.appendChild(this.createWrapper());

			setTimeout(function() {
				this.addWrapperClass();
				this.animateNumber();
				this.animateProgressBar();
				this.animateWavesBlock();
			}.bind(this), 500)
		}
	};


})();