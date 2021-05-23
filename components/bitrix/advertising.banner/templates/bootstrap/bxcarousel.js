/* ========================================================================
 * Bootstrap: carousel.js v3.3.5
 * http://getbootstrap.com/javascript/#carousel
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function () {
    'use strict';

    if (BX.browser.IsIE() || BX.browser.IsIE11() || !window.CustomEvent)
    {
        (function () {
            function CustomEvent ( event, params ) {
                params = params || { bubbles: false, cancelable: false, detail: undefined };
                var evt = document.createEvent( 'CustomEvent' );
                evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
                return evt;
            }

            CustomEvent.prototype = window.Event.prototype;
            window.CustomEvent = CustomEvent;
        })();
    }
    // CAROUSEL CLASS DEFINITION
    // =========================
    var Carousel = function (element, options) {
        options.pause = options.pause != 'false' ? true : false,
            options.wrap =  options.wrap != 'false' ? true : false,
            options.interval = options.interval != 'false' ? parseInt(options.interval) : false,
            options.keyboard = options.keyboard != 'false' ? true : false,
            this.$element    = BX(element)
        this.$indicators = this.$element.querySelectorAll('.carousel-indicators')
        this.options     = options
        this.paused      = null
        this.sliding     = null
        this.interval    = null
        this.$active     = null
        this.$items      = null
        this.slidEvent   = null
        this.slideEvent  = null

        this.options.keyboard && BX.bind(this.$element, 'keydown', BX.proxy(this.keydown, this))

        if (this.options.pause && !(BX.hasClass(document.documentElement, 'bx-touch')))
        {
            BX.bind(this.$element, 'mouseover', BX.proxy(this.pause, this))
            BX.bind(this.$element, 'mouseout', BX.proxy(this.cycle, this))
        }
    }

    Carousel.VERSION  = '3.3.5'

    Carousel.TRANSITION_DURATION = 600

    Carousel.DEFAULTS = {
        interval: 5000,
        pause: true,
        wrap: true,
        keyboard: true
    }

    Carousel.prototype.keydown = function (e) {
        if (/input|textarea/i.test(e.target.tagName)) return
        switch (e.which) {
            case 37: this.prev(); break
            case 39: this.next(); break
            default: return
        }

        e.preventDefault()
    }

    Carousel.prototype.cycle = function (e) {
        e || (this.paused = false)

        this.interval && clearInterval(this.interval)

        this.options.interval
        && !this.paused
        && (this.interval = setInterval(BX.proxy(this.next, this), this.options.interval))

        return this
    }

    Carousel.prototype.getItemIndex = function (item) {
        this.$items = BX.findChildren(item.parentNode, {className: 'item'}, true)
        return this.$items.indexOf(item || this.$active)
    }

    Carousel.prototype.eq = function (obj, i) {
        var len = obj.length,
            j = +i + (i < 0 ? len : 0);
        return j >= 0 && j < len ? obj[j] : {};
    }

    Carousel.prototype.getItemForDirection = function (direction, active) {
        var activeIndex = this.getItemIndex(active)
        var willWrap = (direction == 'prev' && activeIndex === 0)
            || (direction == 'next' && activeIndex == (this.$items.length - 1))
        if (willWrap && !this.options.wrap) return active
        var delta = direction == 'prev' ? -1 : 1
        var itemIndex = (activeIndex + delta) % this.$items.length
        return this.eq(this.$items, itemIndex)
    }

    Carousel.prototype.to = function (pos) {
        var activeIndex = this.getItemIndex(this.$active = BX.findChild(this.$element.parentNode, {className: 'item active'}, true, false))

        if (pos > (this.$items.length - 1) || pos < 0) return


        //TODO click on indicator when still sliding
        if (this.sliding)
        {
            return false;
        }
        if (activeIndex == pos) return this.pause().cycle()

        return this.slide(pos > activeIndex ? 'next' : 'prev', this.eq(this.$items, pos))
    }

    Carousel.prototype.pause = function (e) {
        e || (this.paused = true)

        if (BX.findChildren(this.$element, {className: 'carousel-control'}, true, true).length) {
            this.cycle(true)
        }

        this.interval = clearInterval(this.interval)

        return this
    }

    Carousel.prototype.next = function () {
        if (this.sliding) return
        return this.slide('next')
    }

    Carousel.prototype.prev = function () {
        if (this.sliding) return
        return this.slide('prev')
    }

    Carousel.prototype.loadEvents = function () {
        var $active   = BX.findChild(this.$element, {"class" : "item active"}, true, false) || BX.findChild(this.$element, {"class" : "item  active"}, true, false)
        var startSlid = new CustomEvent("slid.bs.carousel", {detail: {curSlide: $active}});
        var startSlide = new CustomEvent("slide.bs.carousel", {detail: {curSlide: $active}});
        this.$element.dispatchEvent(startSlid)
        this.$element.dispatchEvent(startSlide)
    }

    Carousel.prototype.slide = function (type, next) {
        var $active   = BX.findChild(this.$element, {"class" : "item active"}, true, false) || BX.findChild(this.$element, {"class" : "item  active"}, true, false)
        var $next     = next || this.getItemForDirection(type, $active)
        var isCycling = this.interval
        var direction = type == 'next' ? 'left' : 'right'
        var videoActive = BX.findChild($active, {tagName: 'video', className: 'embed-responsive-item'}, true, false)
        var videoNext = BX.findChild($next, {tagName: 'video', className: 'embed-responsive-item'}, true, false)
        var ytActive = BX.findChild($active, {tagName: 'iframe', className: 'embed-responsive-item'}, true, false)
        var ytNext = BX.findChild($next, {tagName: 'iframe', className: 'embed-responsive-item'}, true, false)

        if (BX.hasClass($next, 'active')) return (this.sliding = false)
        //register events
        this.slidEvent = new CustomEvent("slid.bs.carousel", {detail: {curSlide: $next}});
        this.slideEvent = new CustomEvent("slide.bs.carousel", {detail: {curSlide: $next}});
        this.$element.dispatchEvent(this.slidEvent)
        this.sliding = true

        isCycling && this.pause()

        if (this.$indicators.length) {
            BX.removeClass(BX.findChild(this.$indicators[0], {className: 'active'}, true, false), 'active')
            var $nextIndicator = BX.findChildren(this.$indicators[0], {tagName: 'li'}, false, true)[this.getItemIndex($next)]
            $nextIndicator && BX.addClass($nextIndicator, 'active')
        }

        if (BX.hasClass(this.$element, 'slide') && !BX.browser.IsIE()) {
            var self = this
            BX.addClass($next, type)
            $next.offsetWidth // force reflow
            BX.addClass($active, direction)
            BX.addClass($next, direction)
            setTimeout(function() {
                BX.addClass($next, 'active')
                BX.removeClass($active, 'active')
                BX.removeClass($active, direction)
                BX.removeClass($next, type)
                BX.removeClass($next, direction)

                if (self.options.interval && !self.paused){
                    clearInterval(self.interval);
                    self.cycle();
                }
                self.$element.dispatchEvent(self.slideEvent) //here we go with the slide
                self.sliding = false
            }, Carousel.TRANSITION_DURATION + 100 );
        } else {
            BX.removeClass($active, 'active')
            BX.addClass($next, 'active')
            this.$element.dispatchEvent(this.slideEvent)
            this.sliding = false
        }

        isCycling && this.cycle()

        videoActive && videoActive.pause()
        videoNext && videoNext.play()
        ytActive && window[ytActive.id] && window[ytActive.id].pauseVideo && window[ytActive.id].pauseVideo()
        ytNext && window[ytNext.id] && window[ytNext.id].playVideo && window[ytNext.id].playVideo()
        return this
    }

    // CAROUSEL PLUGIN DEFINITION
    // ==========================
    function Plugin(option) {
        function func() {
            function extend(){
                for(var i=1; i<arguments.length; i++)
                    for(var key in arguments[i])
                        if(arguments[i].hasOwnProperty(key))
                            arguments[0][key] = arguments[i][key];
                return arguments[0];
            }
            var data    = this.data ? this.data : undefined
            var carousel = this.carousel ? this.carousel : undefined
            var options = extend({}, Carousel.DEFAULTS, data, typeof option == 'object' && option)
            var action  = typeof option == 'string' ? option : options.slide
            var active
            if (!carousel)
            {
                carousel = this.carousel = new Carousel(this, options)
                active = BX.findChild(carousel.$element, {"class" : "item active"}, true, false)
                if (active) carousel.loadEvents()
            }
            var vid = BX.findChild(BX.findChild(carousel.$element, {"class" : "item active"}, true, false), {tagName: 'video', className: 'embed-responsive-item'}, true, false)
            active = active || BX.findChild(carousel.$element, {"class" : "item active"}, true, false)
            if (vid) vid.play()
            if (typeof option == 'number')  carousel.to(option)
            else if (action)  carousel[action]()
            else if (options.interval)  carousel.pause().cycle()
        }
        return func.call(this)
    }

    // CAROUSEL DATA-API
    // =================
    var clickHandler = function (e) {
        var href
        var id = this.getAttribute('data-target') || (href = this.getAttribute('href')) && href.replace(/.*(?=#[^\s]+$)/, '')
        if(id && id.length > 1) id = id.substr(1)
        var $target = BX(id)
        if (!BX.hasClass($target, 'carousel')) return
        var options = {slide: this.getAttribute('data-slide')}
        var slideIndex = this.getAttribute('data-slide-to')
        if (slideIndex) options.interval = false

        Plugin.call($target, options)

        if (slideIndex) {
            $target.carousel.to(slideIndex)
        }

        e.preventDefault()
    }

    var initThis = function () {
        var dataSlide = document.querySelectorAll('[data-slide]'), s
        for (s in dataSlide)
        {
            BX.bind(dataSlide[s], 'click', clickHandler)
        }
        var dataSlideTo = document.querySelectorAll('[data-slide-to]'), ss
        for (ss in dataSlideTo)
        {
            BX.bind(dataSlideTo[ss], 'click', clickHandler)
        }
        var i, value;
        function carouselInit() {
            var $carousel = this
            $carousel.data = {
                ride: this.getAttribute('data-ride'),
                pause: this.getAttribute('data-pause'),
                wrap: this.getAttribute('data-wrap'),
                interval: this.getAttribute('data-interval'),
                keyboard: this.getAttribute('data-keyboard')
            }
            Plugin.call($carousel, $carousel.data)
        }
        var obj = document.querySelectorAll('[data-ride="carousel"]')
        for (i in obj)
        {
            if (obj.hasOwnProperty(i))
            {
                value = carouselInit.call(obj[i]);
            }
        }
    }

    if (window.frameCacheVars !== undefined)
    {
        BX.addCustomEvent("onFrameDataReceived" , function() {
            initThis();
        });
    }
    else
    {
        BX.ready(function() {
            initThis();
        });
    }
}();