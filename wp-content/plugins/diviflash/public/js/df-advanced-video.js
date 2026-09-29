/**
 * Walk every ancestor between the sticky target and <body>.
 *
 * Two separate problems must be solved:
 *
 * A) "Containment-breaking" properties (transform, filter, perspective,
 *    backdrop-filter, will-change, contain) create a **new containing block**
 *    for position:fixed, making it relative to the ancestor instead of the
 *    viewport. → We neutralise these to `none`/`auto`.
 *
 * B) "Stacking-context" properties (position + z-index, opacity < 1,
 *    isolation:isolate, mix-blend-mode) trap the element's z-index inside
 *    the ancestor's context, so sibling sections with their own z-index
 *    paint on top. → We boost the ancestor's z-index to our target value
 *    so the whole subtree paints above everything else.
 */
function neutralizeAncestors(target, zIndex) {
    var overridden = [];
    var el = target.parentElement;

    while (el && el !== document.body && el !== document.documentElement) {
        var cs = getComputedStyle(el);
        var entry = { el: el, containment: false, zBoost: false, origZ: '' };

        // --- A) Containment-breaking (breaks position:fixed) ---
        if (cs.transform !== 'none' ||
            cs.filter !== 'none' ||
            cs.perspective !== 'none' ||
            (cs.backdropFilter && cs.backdropFilter !== 'none') ||
            (cs.willChange && cs.willChange !== 'auto' &&
                /transform|perspective|filter/.test(cs.willChange)) ||
            (cs.contain && cs.contain !== 'none')) {
            el.classList.add('difl-sticky-ancestor-override');
            entry.containment = true;
        }

        // --- B) Stacking-context (traps z-index) ---
        var pos = cs.position;
        var isPositioned = (pos === 'relative' || pos === 'absolute' ||
                            pos === 'fixed' || pos === 'sticky');
        var hasZIndex = cs.zIndex !== 'auto';
        var createsStackingCtx =
            (isPositioned && hasZIndex) ||
            parseFloat(cs.opacity) < 1 ||
            cs.isolation === 'isolate' ||
            (cs.mixBlendMode && cs.mixBlendMode !== 'normal');

        if (createsStackingCtx) {
            entry.origZ = el.style.zIndex || '';
            if (!isPositioned) {
                el.style.position = 'relative';
            }
            el.style.setProperty('z-index', String(zIndex), 'important');
            entry.zBoost = true;
        }

        if (entry.containment || entry.zBoost) {
            overridden.push(entry);
        }

        el = el.parentElement;
    }
    return overridden;
}

function restoreAncestors(list) {
    list.forEach(function (item) {
        if (item.containment) {
            item.el.classList.remove('difl-sticky-ancestor-override');
        }
        if (item.zBoost) {
            if (item.origZ) {
                item.el.style.zIndex = item.origZ;
            } else {
                item.el.style.removeProperty('z-index');
            }
        }
    });
}

// Divi breakpoints: phone <768, tablet 768-980, desktop >=981.
function currentStickyBp() {
    var w = window.innerWidth;
    if (w < 768) return 'phone';
    if (w < 981) return 'tablet';
    return 'desktop';
}

// Parse a single CSS length string ("16px", "10%", "16") into { value, unit }.
// Bare numbers and unknown units default to px. Falls back to fallbackStr if input is empty/unparseable.
function parseStickyLength(input, fallbackStr) {
    var lengthRegex = /^\s*(-?\d+(?:\.\d+)?)\s*(px|%)?\s*$/;
    var str = (input == null || input === '') ? String(fallbackStr) : String(input);
    var m = str.match(lengthRegex);
    if (!m) {
        var fm = String(fallbackStr).match(lengthRegex);
        return {
            value: fm ? parseFloat(fm[1]) : 0,
            unit:  (fm && fm[2] === '%') ? '%' : 'px',
        };
    }
    return {
        value: parseFloat(m[1]),
        unit:  m[2] === '%' ? '%' : 'px',
    };
}

// Build per-breakpoint { value, unit } with tablet→desktop and phone→tablet→desktop fallback chain.
function parseStickyResponsiveLength(raw, desktopKey, tabletKey, phoneKey, fallback) {
    var rawD = raw[desktopKey];
    var rawT = (raw[tabletKey] != null && raw[tabletKey] !== '') ? raw[tabletKey] : rawD;
    var rawP = (raw[phoneKey]  != null && raw[phoneKey]  !== '') ? raw[phoneKey]  : rawT;
    return {
        desktop: parseStickyLength(rawD, fallback),
        tablet:  parseStickyLength(rawT, fallback),
        phone:   parseStickyLength(rawP, fallback),
    };
}

// Render a { value, unit } length back to a CSS string ("16px" / "10%").
function lengthToCss(len) {
    return len.value + len.unit;
}

function pickStickyBp(responsive, bp) {
    return responsive[bp];
}

function parseStickySettings(raw) {
    var z = parseInt(String(raw.stickyVideoZIndex || '99999999'), 10);
    return {
        position: raw.stickyVideoPosition || 'bottom_right',
        width:   parseStickyResponsiveLength(raw, 'stickyVideoWidth',   'stickyVideoWidthTablet',   'stickyVideoWidthPhone',   '320'),
        offsetX: parseStickyResponsiveLength(raw, 'stickyVideoOffsetX', 'stickyVideoOffsetXTablet', 'stickyVideoOffsetXPhone', '16'),
        offsetY: parseStickyResponsiveLength(raw, 'stickyVideoOffsetY', 'stickyVideoOffsetYTablet', 'stickyVideoOffsetYPhone', '16'),
        zIndex: Number.isFinite(z) ? z : 99999999,
        shadow: !!raw.stickyVideoShadow,
        close: !!raw.stickyVideoCloseButton,
    };
}

function applyStickyLayout(target, cfg) {
    var bp = currentStickyBp();
    var width   = pickStickyBp(cfg.width,   bp);
    var offsetX = pickStickyBp(cfg.offsetX, bp);
    var offsetY = pickStickyBp(cfg.offsetY, bp);

    target.style.setProperty('z-index', String(cfg.zIndex), 'important');
    // Width and offsets emit exactly as the user authored them — no clamp, no maxWidth.
    target.style.width = lengthToCss(width);

    ['top', 'right', 'bottom', 'left'].forEach(function (p) {
        target.style[p] = 'auto';
    });
    var xCss = lengthToCss(offsetX);
    var yCss = lengthToCss(offsetY);
    if (cfg.position === 'bottom_right') {
        target.style.right = xCss;
        target.style.bottom = yCss;
    } else if (cfg.position === 'bottom_left') {
        target.style.left = xCss;
        target.style.bottom = yCss;
    } else if (cfg.position === 'top_right') {
        target.style.right = xCss;
        target.style.top = yCss;
    } else if (cfg.position === 'top_left') {
        target.style.left = xCss;
        target.style.top = yCss;
    }
    if (cfg.shadow) {
        target.classList.add('difl-sticky-video--shadow');
    } else {
        target.classList.remove('difl-sticky-video--shadow');
    }
}

function clearStickyLayout(target) {
    ['top', 'right', 'bottom', 'left', 'width'].forEach(function (p) {
        target.style[p] = '';
    });
    target.style.removeProperty('z-index');
    target.classList.remove('difl-sticky-video--shadow');
}

function initStickyVideoSlot(slot) {
    var container = slot.closest('.difl-advanced-video-container');
    if (!container) return;
    var target = slot.querySelector('.df-sticky-video');
    if (!target) return;

    var settings = {};
    try {
        settings = JSON.parse(container.dataset.settings || '{}');
    } catch (e) {
        return;
    }
    if (!settings.stickyVideo) return;
    if (!settings.stickyVideoOnMobile && window.innerWidth < 768) return;

    var cfg = parseStickySettings(settings);
    var dismissed = false;
    var ancestorList = [];

    var closeBtn = null;
    if (cfg.close) {
        closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'difl-sticky-video-close';
        closeBtn.setAttribute('aria-label', 'Close floating video');

        var iconData = settings.stickyCloseBtnIcon;
        if (iconData && iconData.unicode) {
            var span = document.createElement('span');
            var isFa = iconData.type === 'fa';
            span.className = 'et-pb-icon' + (isFa ? ' et-pb-icon-fa' : '');
            // Mirrors get_font_style() in utils.js: FA without explicit weight defaults to 900.
            var weight = iconData.weight || (isFa ? 900 : 400);
            span.style.fontWeight = weight;
            var tmp = document.createElement('div');
            tmp.innerHTML = iconData.unicode;
            span.textContent = tmp.textContent || tmp.innerText;
            closeBtn.appendChild(span);
        } else {
            closeBtn.innerHTML = '\u00d7';
        }

        var btnPos = settings.stickyCloseBtnPosition || 'top_right';
        closeBtn.style.top    = btnPos.startsWith('top')    ? '4px' : 'auto';
        closeBtn.style.bottom = btnPos.startsWith('bottom') ? '4px' : 'auto';
        closeBtn.style.right  = btnPos.endsWith('right')    ? '4px' : 'auto';
        closeBtn.style.left   = btnPos.endsWith('left')     ? '4px' : 'auto';

        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dismissed = true;
            setStickyState(false);
        });
        target.appendChild(closeBtn);
        closeBtn.style.display = 'none';
    }

    function setStickyState(active) {
        if (dismissed && active) return;

        if (active) {
            slot.style.minHeight = target.offsetHeight + 'px';
            ancestorList = neutralizeAncestors(target, cfg.zIndex);
            target.classList.add('is-difl-sticky-active');
            applyStickyLayout(target, cfg);
            if (closeBtn) closeBtn.style.display = 'flex';
        } else {
            target.classList.remove('is-difl-sticky-active');
            clearStickyLayout(target);
            restoreAncestors(ancestorList);
            ancestorList = [];
            slot.style.minHeight = '';
            if (closeBtn) closeBtn.style.display = 'none';
        }
    }

    function tick() {
        if (dismissed) return;
        var rect = slot.getBoundingClientRect();
        var shouldStick = rect.bottom < 0;
        var isActive = target.classList.contains('is-difl-sticky-active');

        if (isActive && !shouldStick) {
            setStickyState(false);
        } else if (!isActive && shouldStick) {
            setStickyState(true);
        }
    }

    window.addEventListener('scroll', function () { tick(); }, { passive: true });
    window.addEventListener('resize', function () {
        if (target.classList.contains('is-difl-sticky-active')) {
            applyStickyLayout(target, cfg);
        }
        tick();
    });
    tick();
}

function updateCustomController(controller, isPlaying, settings) {
    const iconSpan = controller.querySelector('.et-pb-icon');
    if (iconSpan) {
        iconSpan.innerHTML = isPlaying ?
            (settings.pauseIcon || '&#x24;') :
            (settings.playIcon || '&#x23;');
        iconSpan.classList.toggle('play', !isPlaying);
        iconSpan.classList.toggle('pause', isPlaying);
    }
}
function handleCustomPlayPauseClick(e) {
    e.stopPropagation();
    const controller = e.target.closest('.difl-video-custom-controls');
    const container = controller
        ? controller.closest('.difl-advanced-video-container') || controller.closest('.difl-video-lightbox-wrapper')
        : null;
    if (!container) return;

    const video = container.querySelector('video');
    if (!video) return;

    const settings = JSON.parse(container.dataset.settings || '{}');

    if (!video.paused) {
        video.pause();
    } else {
        video.play();
    }

    updateCustomController(controller, !video.paused, settings);
}

function renderVideo(wrapperContainer, settings) {
    wrapperContainer.innerHTML = '';

    if (settings.source === 'selfhosted') {
        const video = document.createElement('video');
        video.className = 'difl-video-selfhosted';
        video.controls = settings.show_controls;
        video.autoplay = settings.autoplay;
        video.muted = settings.mute || settings.autoplay;
        video.loop = settings.loop;
        video.playsInline = true;
        if (!settings.allowDownload) {
            video.setAttribute('controlsList', 'nodownload');
        }
        if (settings.playbackSpeed) {
            const rate = parseFloat(settings.playbackSpeed) || 1;
            video.addEventListener('loadedmetadata', () => { video.playbackRate = rate; });
            video.playbackRate = rate;
        }

        // Resolve video source: external URL takes priority over local files
        if (settings.useExternalLink && settings.externalVideoLink) {
            const ext = settings.externalVideoLink.split('.').pop().split('?')[0] || 'mp4';
            const extSource = document.createElement('source');
            extSource.src = settings.externalVideoLink;
            extSource.type = 'video/' + ext;
            video.appendChild(extSource);
        } else {
            const source = document.createElement('source');
            source.src = settings.videoMP4;
            source.type = 'video/mp4';
            video.appendChild(source);
            if (settings.videoWebM) {
                const webmSource = document.createElement('source');
                webmSource.src = settings.videoWebM;
                webmSource.type = 'video/webm';
                video.appendChild(webmSource);
            }
        }

        if (settings.enablePlayPause) {
            const controller = document.createElement('div');
            controller.className = 'difl-video-custom-controls';
            controller.innerHTML = `
        <div class="${settings.custom_controller_icon_animation_type || ''}">
            <span class="et-pb-icon play">${settings.playIcon || '&#x23;'}</span>
        </div>
    `;

            video.addEventListener('play', () => {
                updateCustomController(controller, true, settings);
            });
            video.addEventListener('pause', () => {
                updateCustomController(controller, false, settings);
            });

            controller.addEventListener('click', handleCustomPlayPauseClick);

            wrapperContainer.appendChild(video);
            wrapperContainer.appendChild(controller);
        } else {
            wrapperContainer.appendChild(video);
        }
    } else {
        // Create iframe for embedded videos
        const iframe = document.createElement('iframe');
        iframe.src = settings.iframeSrc;
        iframe.className = (settings.useThumbnail ? 'difl-video-iframe-with-thumbnail' : 'difl-video-iframe-without-thumbnail') + ' difl-video-iframe';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;

        if (settings.playbackSpeed) {
            const rate = parseFloat(settings.playbackSpeed) || 1;
            iframe.addEventListener('load', () => {
                applyIframePlaybackRate(iframe, settings.source, rate);
            });
        }

        wrapperContainer.appendChild(iframe);
    }
}

function handlePlayButtonClick(e) {
    e.stopPropagation();

    const container = e.target.closest('.difl-advanced-video-container');
    if (!container) return;

    const settings = container ? JSON.parse(container.dataset.settings || '{}') : {};
    const wrapperContainer = container.querySelector('.difl-video-wrapper-with-thumbnail');
    if (!wrapperContainer) return;

    renderVideo(wrapperContainer, settings);
}

function triggerLightboxClose(lightbox, settings) {
    const durationMs = parseInt(settings['lightboxRevealAnimationDuration'] || 300) || 300;
    const content = lightbox.querySelector('.difl-video-lightbox-content');
    // overlay: fade out
    lightbox.style.animationDuration = durationMs + 'ms';
    lightbox.classList.remove('lightbox-fade-in');
    lightbox.classList.add('lightbox-fade-out');
    // content: user close animation
    if (content) {
        content.style.animationDuration = durationMs + 'ms';
        content.classList.remove(settings['lightboxOpenAnimation']);
        content.classList.add(settings['lightboxCloseAnimation']);
    }
    closeLightbox(settings);
}

function createLightbox(settings) {
    let lightbox = document.getElementById('difl-video-lightbox');
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.id = 'difl-video-lightbox';
        lightbox.className = 'difl-video-lightbox';
        lightbox.innerHTML = `
            <div class="difl-video-lightbox-content">
                <div class="difl-video-lightbox-header">
                    <h3 class="difl-video-lightbox-title"></h3>
                    <button class="difl-video-lightbox-close">×</button>
                </div>
                <div class="difl-video-lightbox-body"></div>
            </div>
        `;
        document.body.appendChild(lightbox);

        const closeBtn = lightbox.querySelector('.difl-video-lightbox-close');
        closeBtn.addEventListener('click', () => triggerLightboxClose(lightbox, settings));
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) triggerLightboxClose(lightbox, settings);
        });
    }
    return lightbox;
}

function closeLightbox(settings = {}) {
    const lightbox = document.getElementById('difl-video-lightbox');
    if (!lightbox) return;

    const videoContainer = lightbox.querySelector('.difl-video-lightbox-body');
    const video = videoContainer.querySelector('video');
    const iframe = videoContainer.querySelector('iframe');
    const durationMs = parseInt(settings['lightboxRevealAnimationDuration'] || 300) || 300;

    if (video) {
        video.pause();
        video.currentTime = 0;
    }
    if (iframe) {
        iframe.src = iframe.src;
    }

    lightbox.style.animationDuration = durationMs + 'ms';
    setTimeout(() => {
        lightbox.style.display = 'none';
        lightbox.className = 'difl-video-lightbox';
        const content = lightbox.querySelector('.difl-video-lightbox-content');
        if (content) content.className = 'difl-video-lightbox-content';
        videoContainer.innerHTML = '';
        lightbox.remove();
    }, durationMs);
}

const getVideoTitle = async (url, source) => {
    if (!url) return '';

    try {
        switch (source) {
            case 'youtube':
                const videoId = url.includes('youtu.be') ? url.split('youtu.be/')[1]?.split('?')[0] : url.split('v=')[1]?.split('&')[0];
                const response = await fetch(`https://noembed.com/embed?url=${url}`);
                const data = await response.json();
                return data.title || '';

            case 'vimeo':
                const vimeoResponse = await fetch(`https://vimeo.com/api/oembed.json?url=${url}`);
                const vimeoData = await vimeoResponse.json();
                return vimeoData.title || '';

            case 'dailymotion':
                const dmResponse = await fetch(`https://api.dailymotion.com/video/${url.split('/').pop()}?fields=title`);
                const dmData = await dmResponse.json();
                return dmData.title || '';

            default:
                return '';
        }
    } catch (error) {
        console.error('Error fetching video title:', error);
        return '';
    }
};

function handleLightBoxTriggerButtonClick(e) {
    e.stopPropagation();

    const container = e.target.closest('.difl-advanced-video-container');
    if (!container) return;

    const settings = container ? JSON.parse(container.dataset.settings || '{}') : {};

    const lightbox = createLightbox(settings);
    lightbox.classList.add(`${settings['orderClass']}_lightbox`);
    const lightboxBody = lightbox.querySelector('.difl-video-lightbox-body');
    const lightboxContent = lightbox.querySelector('.difl-video-lightbox-content');

    // Create video wrapper
    const videoWrapper = document.createElement('div');
    videoWrapper.className = 'difl-video-lightbox-wrapper';

    // Force autoplay in lightbox
    const lightboxSettings = {
        ...settings,
        autoplay: true,
        mute: true,
        show_controls: true,
        enablePlayPause: false
    };

    renderVideo(videoWrapper, lightboxSettings);
    lightboxBody.appendChild(videoWrapper);

    const durationMs = parseInt(settings['lightboxRevealAnimationDuration'] || 300) || 300;

    // overlay: always fade-in
    lightbox.style.animationDuration = durationMs + 'ms';
    lightbox.classList.add('lightbox-fade-in');

    // content: user-selected open animation
    if (lightboxContent) {
        lightboxContent.style.animationDuration = durationMs + 'ms';
        lightboxContent.classList.add(settings['lightboxOpenAnimation']);
    }

    lightbox.style.display = 'flex';
    lightbox.style.opacity = '1';

    const escHandler = (e) => {
        if (e.key === 'Escape') {
            triggerLightboxClose(lightbox, settings);
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}



function handlePlayOnViewport(wrapperContainer, settings) {
    if (!wrapperContainer || !settings) return;

    const parseOffset = (val) => {
        if (!val) return { num: 0, unit: 'px' };
        const unit = String(val).includes('%') ? '%' : 'px';
        return { num: Math.abs(parseFloat(val)) || 0, unit };
    };
    const top = parseOffset(settings.viewportOffset);
    const bottom = parseOffset(settings.viewportOffsetBottom);

    const rootMargin = `${top.num > 0 ? '-' : ''}${top.num}${top.unit} 0px ${bottom.num > 0 ? '-' : ''}${bottom.num}${bottom.unit} 0px`;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    // Selfhosted: PHP already rendered the video, skip re-render
                    if (settings.source !== 'selfhosted') {
                        renderVideo(wrapperContainer, settings);
                    }

                    const video = wrapperContainer.querySelector('video');
                    const iframe = wrapperContainer.querySelector('iframe');

                    if (video) {
                        video.play().catch((err) => {
                            console.error('Error playing video:', err);
                        });
                    } else if (iframe) {
                        iframe.addEventListener('load', () => {
                            if (settings.source === 'youtube') {
                                iframe.contentWindow.postMessage(
                                    '{"event":"command","func":"playVideo","args":""}', '*'
                                );
                            } else if (settings.source === 'vimeo') {
                                iframe.contentWindow.postMessage(
                                    '{"method":"play"}', iframe.src.split('?')[0]
                                );
                            } else if (settings.source === 'dailymotion') {
                                iframe.contentWindow.postMessage(
                                    '{"command":"play"}', '*'
                                );
                            }
                        }, { once: true });
                    }
                } else {
                    const video = wrapperContainer.querySelector('video');
                    const iframe = wrapperContainer.querySelector('iframe');
                    if (video) {
                        video.pause();
                    } else if (iframe) {
                        // Pause iframe videos
                        if (settings.source === 'youtube') {
                            iframe.contentWindow.postMessage(
                                '{"event":"command","func":"pauseVideo","args":""}',
                                '*'
                            );
                        } else if (settings.source === 'vimeo') {
                            iframe.contentWindow.postMessage(
                                '{"method":"pause"}',
                                iframe.src.split('?')[0]
                            );
                        } else if (settings.source === 'dailymotion') {
                            iframe.contentWindow.postMessage(
                                '{"command":"pause"}',
                                '*'
                            );
                        }
                    }
                }
            });
        },
        { rootMargin }
    );

    // Observe the wrapper container
    observer.observe(wrapperContainer);

    // Cleanup function to disconnect the observer
    return () => {
        observer.disconnect();
    };
}

function applyIframePlaybackRate(iframe, source, rate) {
    if (source === 'youtube') {
        iframe.contentWindow.postMessage(JSON.stringify({
            event: 'command',
            func: 'setPlaybackRate',
            args: [rate]
        }), '*');
    } else if (source === 'vimeo') {
        iframe.contentWindow.postMessage(JSON.stringify({
            method: 'setPlaybackRate',
            value: rate
        }), iframe.src.split('?')[0]);
    }
}

window.addEventListener('load', () => {
    // Skip all frontend-only behaviors in Divi visual builder
    if (document.body.classList.contains('et-fb-enabled')) return;

    const viewportModules = document.querySelectorAll('.difl-advanced-video.df-play-on-viewport');

    viewportModules.forEach((module) => {
        const container = module.closest('.difl-advanced-video-container');
        if (!container) {
            return;
        }

        const settings = JSON.parse(container.dataset.settings || '{}');

        const wrapperContainer = module.querySelector('.difl-video-wrapper');
        if (!wrapperContainer) {
            return;
        }
        const videoContainer = module.querySelector('.difl-video-wrapper');

        handlePlayOnViewport(videoContainer, settings);
    });

    document.querySelectorAll('.difl-sticky-video-slot').forEach((slot) => {
        initStickyVideoSlot(slot);
    });

    // Attach play/pause listeners to PHP-rendered selfhosted videos with custom controller
    document.querySelectorAll('.difl-advanced-video-container').forEach((container) => {
        const settings = JSON.parse(container.dataset.settings || '{}');
        if (settings.source !== 'selfhosted' || !settings.enablePlayPause) return;
        const video = container.querySelector('video.difl-video-selfhosted');
        const controller = container.querySelector('.difl-video-custom-controls');
        if (!video || !controller) return;
        video.addEventListener('play', () => updateCustomController(controller, true, settings));
        video.addEventListener('pause', () => updateCustomController(controller, false, settings));
    });

    // Apply playback speed to PHP-rendered videos
    document.querySelectorAll('.difl-advanced-video-container').forEach((container) => {
        const settings = JSON.parse(container.dataset.settings || '{}');
        if (!settings.playbackSpeed || parseFloat(settings.playbackSpeed) === 1) return;
        const rate = parseFloat(settings.playbackSpeed);

        if (settings.source === 'selfhosted') {
            const video = container.querySelector('video.difl-video-selfhosted');
            if (video) {
                video.playbackRate = rate;
                video.addEventListener('loadedmetadata', () => { video.playbackRate = rate; });
            }
        } else {
            const iframe = container.querySelector('iframe');
            if (iframe) {
                iframe.addEventListener('load', () => {
                    applyIframePlaybackRate(iframe, settings.source, rate);
                });
            }
        }
    });
});
