(function () {
    'use strict';

    var FB_VERSION = 'v21.0';
    var sdkInjected = false;

    function readFirstSettings() {
        var first = document.querySelector('.difl_facebookfeed .df_fb_feed_wrapper');
        if (!first) return {};
        try {
            return JSON.parse(first.dataset.settings || '{}');
        } catch (e) {
            return {};
        }
    }

    function ensureSDK() {
        if (sdkInjected || document.getElementById('facebook-jssdk')) return;
        sdkInjected = true;

        var settings = readFirstSettings();
        var language = settings.language || 'en_US';

        if (!document.getElementById('fb-root')) {
            var root = document.createElement('div');
            root.id = 'fb-root';
            document.body.insertBefore(root, document.body.firstChild);
        }

        var js = document.createElement('script');
        js.id = 'facebook-jssdk';
        js.async = true;
        js.defer = true;
        js.crossOrigin = 'anonymous';
        js.src = 'https://connect.facebook.net/' + language + '/sdk.js#xfbml=1&version=' + FB_VERSION;
        document.body.appendChild(js);
    }

    function init() {
        if (!document.querySelector('.difl_facebookfeed')) return;
        ensureSDK();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
