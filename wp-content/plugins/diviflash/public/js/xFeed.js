(function () {
    'use strict';

    function DFXFeed(moduleEl) {
        this.container = moduleEl.querySelector('.df_x_widget');
        if (!this.container) return;

        this._init();
    }

    DFXFeed.prototype._init = function () {
        var self = this;
        this._loadSDK().then(function () {
            self._parseWidgets();
        });
    };

    DFXFeed.prototype._loadSDK = function () {
        return new Promise(function (resolve, reject) {
            if (window.twttr && window.twttr.widgets) {
                resolve();
                return;
            }

            if (!document.getElementById('twitter-wjs')) {
                var js = document.createElement('script');
                js.id = 'twitter-wjs';
                js.src = 'https://platform.twitter.com/widgets.js';
                js.async = true;
                js.onerror = function () {
                    reject(new Error('Failed to load X SDK'));
                };
                document.body.appendChild(js);
            }

            var attempts = 0;
            var check = setInterval(function () {
                attempts++;
                if (window.twttr && window.twttr.widgets) {
                    clearInterval(check);
                    resolve();
                }
                if (attempts > 100) {
                    clearInterval(check);
                    reject(new Error('X SDK load timeout'));
                }
            }, 100);
        });
    };

    DFXFeed.prototype._parseWidgets = function () {
        if (!window.twttr || !window.twttr.widgets || !this.container) return;
        window.twttr.widgets.load(this.container);
    };

    function init() {
        var modules = document.querySelectorAll('.difl_xfeed');
        [].forEach.call(modules, function (moduleEl) {
            new DFXFeed(moduleEl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
