(function () {
    'use strict';

    // ---------------------------------------------------------------------------
    // Utility helpers
    // ---------------------------------------------------------------------------
    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }


    // ---------------------------------------------------------------------------
    // DFCircleInfo – one instance per module on the page
    // ---------------------------------------------------------------------------
    function DFCircleInfo(moduleEl) {
        this.wrapper = moduleEl.querySelector('.df_circle_info_wrapper');
        if (!this.wrapper) return;

        this.settings = this._parseSettings();
        if (!this.settings) return;

        this.circle = this.wrapper.querySelector('.df_circle_info_circle');
        this.center = this.wrapper.querySelector('.df_circle_info_center');
        this.items = this.wrapper.querySelectorAll('.difl_circleinfoitem');
        this.totalItems = this.items.length;

        if (!this.circle || !this.center || this.totalItems === 0) return;

        // State
        this.activeIndex = -1;
        this.autoplayTimer = null;
        this.isPaused = false;
        this.isInViewport = true;
        this.isTabVisible = true;

        // Rotation state
        this.rotationAngle = 0;
        this.angleStep = 360 / this.totalItems;
        this.autoplayRotation = this.settings.autoplay_rotation === 'on';
        this.rotationDirection = this.settings.rotation_direction === 'ccw' ? -1 : 1;
        this.rotationSpeed = parseInt(this.settings.rotation_speed) || 600;

        // Content sections moved from children to center
        this.contentSections = [];

        // Kick off
        this._init();
    }

    // -- Bootstrap --------------------------------------------------------------
    DFCircleInfo.prototype._parseSettings = function () {
        try {
            return JSON.parse(this.wrapper.dataset.settings || '{}');
        } catch (e) {
            return null;
        }
    };

    DFCircleInfo.prototype._init = function () {
        this.positionItems();
        this._moveContentToCenter();
        this._applyChildClassToContentSections();
        this._bindItemEvents();
        this._activateDefaultItem();
        if (this.autoplayRotation && !this._isMobileListView()) {
            this.wrapper.classList.add('autoplay_rotation');
            this._setRotationTransitionSpeed();
        }
        this._setupAutoplay();
        this._bindVisibility();
        this._bindResize();
    };

    // -- Move content from children to center -----------------------------------
    DFCircleInfo.prototype._moveContentToCenter = function () {
        var self = this;
        this.contentSections = [];

        [].forEach.call(this.items, function (item, index) {
            var contentEl = item.querySelector('.df_ci_center_content');
            if (contentEl) {
                contentEl.setAttribute('data-child-index', String(index));
                self.center.appendChild(contentEl);
                self.contentSections.push(contentEl);
            } else {
                self.contentSections.push(null);
            }
        });
    };

    DFCircleInfo.prototype._activateDefaultItem = function () {
        var itemNumber = parseInt(this.settings.default_active_item) || 1;
        var index = Math.max(0, Math.min(itemNumber - 1, this.totalItems - 1));

        this._clearActiveItems();
        this.items[index].classList.add('active');
        this.activeIndex = index;

        this._activateContentSection(index);
    };

    // -- Positioning ------------------------------------------------------------
    DFCircleInfo.prototype.positionItems = function () {
        if (this._isMobileListView()) {
            this._applyMobileLayout();
        } else {
            this._applyCircularLayout();
        }
    };

    DFCircleInfo.prototype._isMobileListView = function () {
        var layout = this.settings.mobile_layout || 'list';
        var breakpoint = parseInt(this.settings.mobile_breakpoint) || 768;
        return window.innerWidth <= breakpoint && layout === 'list';
    };

    DFCircleInfo.prototype._applyMobileLayout = function () {
        this.wrapper.classList.add('mobile_list');

        [].forEach.call(this.items, function (item) {
            item.style.position = '';
            item.style.left = '';
            item.style.top = '';
            item.style.transform = '';
            item.style.margin = '';
            item.style.padding = '';
        });
    };

    DFCircleInfo.prototype._getResponsiveValue = function (key) {
        var width = window.innerWidth;
        var responsive = this.settings[key + '_responsive'] || {};
        var desktopVal = this.settings[key];

        // Breakpoints sorted by max-width ascending (smallest first)
        var maxWidthBreakpoints = [
            { name: 'phone',     maxWidth: 767 },
            { name: 'phoneWide', maxWidth: 860 },
            { name: 'tablet',    maxWidth: 980 },
            { name: 'tabletWide', maxWidth: 1024 },
        ];
        // Min-width breakpoints (largest first)
        var minWidthBreakpoints = [
            { name: 'ultraWide', minWidth: 1440 },
            { name: 'widescreen', minWidth: 1280 },
        ];

        // Check min-width breakpoints first (largest to smallest)
        for (var i = 0; i < minWidthBreakpoints.length; i++) {
            var bp = minWidthBreakpoints[i];
            if (width >= bp.minWidth && responsive[bp.name]) {
                return responsive[bp.name];
            }
        }

        // Check max-width breakpoints (smallest to largest, find the matching one)
        var matched = desktopVal;
        for (var j = maxWidthBreakpoints.length - 1; j >= 0; j--) {
            var bp2 = maxWidthBreakpoints[j];
            if (width <= bp2.maxWidth && responsive[bp2.name]) {
                matched = responsive[bp2.name];
            }
        }

        return matched || desktopVal;
    };

    DFCircleInfo.prototype._applyCircularLayout = function () {
        this.wrapper.classList.remove('mobile_list');

        var startAngle = parseFloat(this.settings.circle_start_angle) || -90;
        var radius = parseFloat(this._getResponsiveValue('item_distance')) || 50;
        var angleStep = 360 / this.totalItems;

        [].forEach.call(this.items, function (item, i) {
            var angle = startAngle + i * angleStep;
            var rad = (angle * Math.PI) / 180;

            item.style.position = 'absolute';
            item.style.left = (50 + radius * Math.cos(rad)) + '%';
            item.style.top = (50 + radius * Math.sin(rad)) + '%';
            item.style.margin = '0';
            item.style.padding = '0';
        });
    };

    // -- Rotation ---------------------------------------------------------------
    DFCircleInfo.prototype._setRotationTransitionSpeed = function () {
        var duration = (this.rotationSpeed / 1000) + 's';
        var itemsContainer = this.wrapper.querySelector('.df_circle_info_items');
        if (itemsContainer) {
            itemsContainer.style.transitionDuration = duration;
        }

        [].forEach.call(this.items, function (item) {
            item.style.transitionDuration = duration;
        });
    };

    DFCircleInfo.prototype._applyRotation = function (angle) {
        var itemsContainer = this.wrapper.querySelector('.df_circle_info_items');
        if (!itemsContainer) return;

        itemsContainer.style.transform = 'rotate(' + angle + 'deg)';

        [].forEach.call(this.items, function (item) {
            item.style.transform = 'rotate(' + (-angle) + 'deg)';
        });
    };

    // -- Content ----------------------------------------------------------------
    DFCircleInfo.prototype.activateItem = function (index) {
        if (index < 0 || index >= this.totalItems) return;

        this._clearActiveItems();
        this.items[index].classList.add('active');
        this.activeIndex = index;

        this._activateContentSection(index);
    };

    DFCircleInfo.prototype._clearActiveItems = function () {
        [].forEach.call(this.items, function (item) {
            item.classList.remove('active');
        });
    };

    // -- Add child module class to each content section --------------------------
    DFCircleInfo.prototype._applyChildClassToContentSections = function () {
        var self = this;

        [].forEach.call(this.items, function (item, index) {
            var section = self.contentSections[index];
            if (!section) return;

            var itemClasses = item.className.split(' ');
            for (var j = 0; j < itemClasses.length; j++) {
                if (itemClasses[j].indexOf('difl_circleinfoitem_') === 0) {
                    section.classList.add(itemClasses[j]);
                    break;
                }
            }
        });
    };

    // -- Content section activation (cross-fade only) ----------------------------
    DFCircleInfo.prototype._activateContentSection = function (index) {
        // Hide default content
        var defaultContent = this.center.querySelector('.df_ci_center_content[data-source="default"]');
        if (defaultContent) {
            defaultContent.classList.remove('active');
            defaultContent.style.display = 'none';
        }

        // Toggle active class — CSS handles the cross-fade via opacity transition
        this.contentSections.forEach(function (s) {
            if (s) s.classList.remove('active');
        });

        var section = this.contentSections[index];
        if (section) section.classList.add('active');
    };

    // -- Events -----------------------------------------------------------------
    DFCircleInfo.prototype._bindItemEvents = function () {
        var triggerMode = this.settings.trigger_mode || 'click';

        if (triggerMode === 'hover') {
            this._bindHoverEvents();
        } else {
            this._bindClickEvents();
        }
    };

    DFCircleInfo.prototype._bindClickEvents = function () {
        var self = this;

        [].forEach.call(this.items, function (item, i) {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                if (self.activeIndex !== i) {
                    self.activateItem(i);
                }
            });
        });
    };

    DFCircleInfo.prototype._bindHoverEvents = function () {
        var self = this;

        [].forEach.call(this.items, function (item, i) {
            item.addEventListener('mouseenter', function () {
                if (self.activeIndex !== i) {
                    self.activateItem(i);
                }
            });
        });
    };

    // -- Autoplay ---------------------------------------------------------------
    DFCircleInfo.prototype._setupAutoplay = function () {
        if (this.settings.enable_autoplay !== 'on') return;

        var pauseOnHover = this.settings.autoplay_pause_on_hover !== 'off';
        var interval = parseInt(this.settings.autoplay_interval) || 0;

        if (pauseOnHover) {
            this._bindAutoplayPause();
        }

        if (interval > 0) {
            var self = this;
            setTimeout(function () { self.startAutoplay(); }, interval);
        } else {
            this.startAutoplay();
        }
    };

    DFCircleInfo.prototype.startAutoplay = function () {
        this.stopAutoplay();

        var self = this;
        var speed = parseInt(this.settings.autoplay_speed) || 3000;

        this.autoplayTimer = setInterval(function () {
            if (self.isPaused || !self.isInViewport || !self.isTabVisible) return;
            var next;
            if (self.autoplayRotation && self.rotationDirection === -1) {
                next = (self.activeIndex - 1 + self.totalItems) % self.totalItems;
            } else {
                next = (self.activeIndex + 1) % self.totalItems;
            }
            self.activateItem(next);

            if (self.autoplayRotation && !self._isMobileListView()) {
                self.rotationAngle -= self.angleStep * self.rotationDirection;
                self._applyRotation(self.rotationAngle);
            }
        }, speed);
    };

    DFCircleInfo.prototype.stopAutoplay = function () {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }
    };

    DFCircleInfo.prototype._bindAutoplayPause = function () {
        var self = this;

        this.wrapper.addEventListener('mouseenter', function () {
            self.isPaused = true;
        });

        this.wrapper.addEventListener('mouseleave', function () {
            self.isPaused = false;
        });
    };

    // -- Visibility (viewport + tab) --------------------------------------------
    DFCircleInfo.prototype._bindVisibility = function () {
        if (this.settings.enable_autoplay !== 'on') return;

        var self = this;

        // Pause when module scrolls out of viewport
        if (typeof IntersectionObserver !== 'undefined') {
            var observer = new IntersectionObserver(function (entries) {
                self.isInViewport = entries[0].isIntersecting;
            });
            observer.observe(self.wrapper);
        }

        // Pause when user switches browser tab
        document.addEventListener('visibilitychange', function () {
            self.isTabVisible = !document.hidden;
        });
    };

    // -- Responsive -------------------------------------------------------------
    DFCircleInfo.prototype._bindResize = function () {
        var self = this;
        window.addEventListener('resize', debounce(function () {
            self.positionItems();
            if (self._isMobileListView()) {
                self.wrapper.classList.remove('autoplay_rotation');
                self.rotationAngle = 0;
                self._applyRotation(0);
            } else if (self.autoplayRotation) {
                self.wrapper.classList.add('autoplay_rotation');
                self._applyRotation(self.rotationAngle);
            }
        }, 150));
    };

    // ---------------------------------------------------------------------------
    // Bootstrap – create one instance per module
    // ---------------------------------------------------------------------------
    function init() {
        var modules = document.querySelectorAll('.difl_circleinfo');

        [].forEach.call(modules, function (moduleEl) {
            new DFCircleInfo(moduleEl);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
}());
