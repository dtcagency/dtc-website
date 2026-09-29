<?php
/**
 * VideoModule Module class.
 *
 * @package DIVIFLASH5\Modules\VideoModule;
 */

namespace DIFL\Server\Modules\VideoModule;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use DIFL\Server\Traits\ElementsRenderTrait;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class VideoModule
 *
 * @package DIVIFLASH5\Modules\VideoModule
 */
class AdvancedVideo implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use ElementsRenderTrait;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/advanced-video')->customCssFields;
    }

    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs = $args['attrs'];

        // Text Options.
        $classnames_instance->add(
            TextClassnames::text_options_classnames(
                $attrs['module']['advanced']['text'] ?? [],
                [
                    'orientation' => false,
                ]
            ),
            true
        );

        // Module.
        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => $attrs['module']['decoration'] ?? [],
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? [],
                        [
                            'link' => $attrs['module']['advanced']['link'] ?? [],
                        ]
                    ),
                ]
            )
        );
    }
    public static function font_style($font) {
        return static::get_font_style($font);
    }
    public static function module_script_data($args)
    {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }

    protected function getDefaultKeys($args)
    {
        $attrs = $args['attrs'];

        $metaData = $args['elements']->module_metadata->attributes;
        foreach ($metaData as $key => $value) {
            if (empty($attrs[$key])) {
                $attrs[$key] = [
                    'innerContent' => [
                        'desktop' => [
                            'value' => []
                        ]
                    ],
                ];
            }
        }

        return $attrs;
    }

    public static function module_styles($args)
    {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);
        ;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/advanced-video');
        $fn_alignment = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
            'justified' => 'space-between',
        ];
        $styles = [];
        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .difl-video-play-button span.et-pb-icon, ' . $args['orderClass'] . ' .difl-video-lightbox-trigger-button span.et-pb-icon',
            'attr' => $attrs['thumbnail_play_media_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) use ($instance) {
                return self::font_style( $props['attrValue']['custom_play_icon'] ?? $instance->props['custom_play_icon'] );
            },
        ]);
        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .difl-video-custom-controls .et-pb-icon.play',
            'attr' => $attrs['custom_controller_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) use ($instance) {
                return self::font_style( $props['attrValue']['custom_play_controller_icon'] ?? $instance->props['custom_play_controller_icon'] );
            },
        ]);
        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .difl-video-custom-controls .et-pb-icon.pause',
            'attr' => $attrs['custom_controller_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) use ($instance) {
                return self::font_style( $props['attrValue']['custom_pause_controller_icon'] ?? $instance->props['custom_pause_controller_icon'] );
            },
        ]);
        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => [
                    // Element: Module.
                    $elements->style(
                        [
                            'attrName' => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                    CssStyle::style(
                        [
                            'selector' => $args['orderClass'],
                            'attr' => $attrs['css'] ?? [],
                            'cssFields' => self::custom_css(),
                        ]
                    ),

                    ...$instance->styles,
                    ...$styles,
                ],
            ]
        );
    }

    protected function getVideoEmbedUrl($url, $source)
    {
        if (!$url) {
            return '';
        }

        switch ($source) {
            case 'youtube':
                // Handle YouTube URLs
                if (strpos($url, 'youtu.be') !== false) {
                    $parts = explode('youtu.be/', $url);
                    $videoId = explode('?', $parts[1])[0] ?? '';

                    return "https://www.youtube.com/embed/$videoId";
                }
                if (strpos($url, 'youtube.com/watch') !== false) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $videoId = $query['v'] ?? '';

                    return "https://www.youtube.com/embed/$videoId";
                }

                return $url;

            case 'vimeo':
                // Handle Vimeo URLs
                $vimeoId = basename($url);

                return "https://player.vimeo.com/video/$vimeoId";

            case 'dailymotion':
                // Handle Dailymotion URLs
                $parts = explode('/', $url);
                $dailymotionId = explode('?', end($parts))[0];

                return "https://www.dailymotion.com/embed/video/$dailymotionId";

            default:
                return $url;
        }
    }

    protected function getVideoParams($settings)
    {
        $source = $settings['source'];
        $autoplay = $settings['autoplay'];
        $show_controls = $settings['show_controls'];
        $mute = $settings['mute'];
        $loop = $settings['loop'];
        $showCaption = $settings['showCaption'];
        $captionLanguage = $settings['captionLanguage'];
        $startTime = $settings['startTime'];
        $endTime = $settings['endTime'];
        $params = [];
        switch ($source) {
            case 'youtube':
                if ($autoplay) {
                    $params['autoplay'] = 1;
                }
                $params['controls'] = $show_controls ? 1 : 0;
                $params['mute'] = $autoplay ? 1 : (int) $mute;
                $params['loop'] = $loop ? 1 : 0;
                if ($loop) {
                    // YouTube loop only works when playlist equals the video ID
                    $videoId = basename(parse_url($settings['embedUrl'], PHP_URL_PATH));
                    $params['playlist'] = $videoId;
                }
                $params['enablejsapi'] = 1;
                $params['modestbranding'] = 1;
                if ($showCaption) {
                    $params['cc_load_policy'] = 1;
                    if ($captionLanguage && $captionLanguage !== 'auto') {
                        $params['cc_lang_pref'] = $captionLanguage;
                        $params['hl'] = $captionLanguage;
                    }
                } else {
                    $params['cc_load_policy'] = 0;
                }
                if ($startTime > 0) {
                    $params['start'] = $startTime;
                }
                if ($endTime > 0) {
                    $params['end'] = $endTime;
                }

                return '?' . http_build_query($params);

            case 'vimeo':
                $params['autoplay'] = $autoplay ? 1 : 0;
                $params['muted'] = ($autoplay || $mute) ? 1 : 0;
                $params['loop'] = $loop ? 1 : 0;
                if ($showCaption && $captionLanguage && $captionLanguage !== 'auto') {
                    $params['texttrack'] = $captionLanguage;
                }
                $query = '?' . http_build_query($params);

                $timeFragment = "#t={$startTime}" . ($endTime > 0 ? ",{$endTime}" : '');

                return $query . $timeFragment;

            case 'dailymotion':
                if ($autoplay) {
                    $params['autoplay'] = 1;
                }
                $params['controls'] = $show_controls ? 1 : 0;
                $params['mute'] = ($autoplay || $mute) ? 1 : 0;
                if ($showCaption) {
                    $params['subtitles-default'] = $captionLanguage;
                }

                if ($startTime > 0) {
                    $params['start'] = $startTime;
                }
                if ($endTime > 0) {
                    $params['end'] = $endTime;
                }

                return '?' . http_build_query($params);

            case 'selfhosted':
                return '';

            default:
                return '';
        }
    }

    protected function getVideoThumbnail($url, $source)
    {
        if (!$url) {
            return '';
        }

        try {
            switch ($source) {
                case 'youtube':
                    // Extract YouTube video ID
                    if (strpos($url, 'youtu.be') !== false) {
                        $parts = explode('youtu.be/', $url);
                        $videoId = explode('?', $parts[1])[0] ?? '';
                    } else {
                        parse_str(parse_url($url, PHP_URL_QUERY), $query);
                        $videoId = $query['v'] ?? '';
                    }

                    return "https://img.youtube.com/vi/$videoId/maxresdefault.jpg";

                case 'vimeo':
                    // Extract Vimeo ID
                    $vimeoId = basename(parse_url($url, PHP_URL_PATH));
                    $apiUrl = "https://vimeo.com/api/v2/video/$vimeoId.json";

                    $response = @file_get_contents($apiUrl);
                    if ($response !== false) {
                        $data = json_decode($response, true);

                        return $data[0]['thumbnail_large'] ?? '';
                    }

                    return '';

                case 'dailymotion':
                    // Extract Dailymotion ID
                    $parts = explode('/', $url);
                    $dmId = explode('?', end($parts))[0];

                    return "https://www.dailymotion.com/thumbnail/video/$dmId";

                default:
                    return '';
            }
        } catch (Exception $e) {
            // error_log('Error fetching thumbnail: ' . $e->getMessage());

            return '';
        }
    }

    protected function getVideoTitle($url, $source)
    {
        if (!$url) {
            return '';
        }

        try {
            switch ($source) {
                case 'youtube':
                    // Use noembed API for YouTube
                    $apiUrl = "https://noembed.com/embed?url=" . urlencode($url);
                    $response = @file_get_contents($apiUrl);
                    if ($response !== false) {
                        $data = json_decode($response, true);

                        return $data['title'] ?? '';
                    }

                    return '';

                case 'vimeo':
                    // Vimeo oEmbed API
                    $apiUrl = "https://vimeo.com/api/oembed.json?url=" . urlencode($url);
                    $response = @file_get_contents($apiUrl);
                    if ($response !== false) {
                        $data = json_decode($response, true);

                        return $data['title'] ?? '';
                    }

                    return '';

                case 'dailymotion':
                    // Extract video ID from URL
                    $parts = explode('/', parse_url($url, PHP_URL_PATH));
                    $videoId = end($parts);
                    $apiUrl = "https://api.dailymotion.com/video/" . $videoId . "?fields=title";
                    $response = @file_get_contents($apiUrl);
                    if ($response !== false) {
                        $data = json_decode($response, true);

                        return $data['title'] ?? '';
                    }

                    return '';

                default:
                    return '';
            }
        } catch (Exception $e) {
            // error_log('Error fetching video title: ' . $e->getMessage());

            return '';
        }
    }

    protected function renderThumbnail($settings = [])
    {

        $useThumbnail = $settings['useThumbnail'] ?? false;
        $thumbnailImage = trim((string) ($settings['thumbnailImage'] ?? ''));
        $videoTitle = $settings['videoTitle'] ?? '';
        $videoLightBox = $settings['videoLightBox'] ?? false;

        if (!$useThumbnail) {
            return '';
        }

        $imgSrc = $thumbnailImage;
        $altText = !empty($videoTitle) ? $videoTitle : 'Video thumbnail';

        $html = '<div class="difl-video-thumbnail-wrapper">';

        if (!empty($imgSrc)) {
            $html .= '<img src="' . esc_attr($imgSrc) . '" alt="' . esc_attr($altText) . '" class="difl-video-thumbnail" />';
        }

        if ($videoLightBox) {
            $html .= $this->renderLightBoxButton($settings);
        } else {
            $html .= $this->renderPlayButton($settings);
        }

        $html .= '</div>';

        return $html;
    }


    protected function renderPlayButton($settings)
    {
//        error_log(print_r($settings['customPlayIcon'], true));
        $icon = $settings['customPlayIcon'];
        $image = $settings['thumbnailPlayButtonIconImage'];
        $useCustomThumbnailPlayIcon = $settings['useCustomThumbnailPlayIcon'];
        $animation = $this->props['thumbnail_play_button_animation_type'];
        $playButtonClass = ! $useCustomThumbnailPlayIcon && empty($image)
            ? 'difl-video-play-button difl-fallback-play-button'
            : 'difl-video-play-button';
        $style = "position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            display:flex;align-items:center;justify-content:center;";
        $content = ! $useCustomThumbnailPlayIcon && ! empty($image)
            ? sprintf('<img src="%1$s" alt="Play Button">', esc_url($image))
            : sprintf('<span class="et-pb-icon">%1$s</span>', $icon);

        return sprintf(
            '<div class="%4$s" style="%1$s" onclick="handlePlayButtonClick(event)">
            <div class="%3$s">%2$s</div>
        </div>',
            $style,
            $content,
            $animation,
            $playButtonClass
        );
    }

    protected function renderLightBoxButton($settings)
    {
        $icon = $settings['customPlayIcon'];
        $anim = $this->props['thumbnail_play_button_animation_type'];
        $style = "display:flex;align-items:center;justify-content:center;";
        $className = $settings['useThumbnail'] ? 'with-thumbnail' : '';
        $className .= ! ($settings['useCustomThumbnailPlayIcon'] ?? false) ? ' difl-fallback-play-button' : '';
        if ($settings['videoLightBox'] === true) {
            return sprintf(
                '<div class="difl-video-lightbox-trigger-button %3$s" style="%1$s" onclick="handleLightBoxTriggerButtonClick(event)">
            <div class="%4$s"><span class="et-pb-icon">%2$s</span></div>
        </div>',
                $style,
                $icon,
                $className,
                $anim
            );
        }
        return '';

    }

    protected function renderSelfHostedVideo($args = [])
    {
        $defaults = [
            'autoplay' => false,
            'mute' => false,
            'loop' => false,
            'show_controls' => true,
            'enablePlayPause' => false,
            'allowDownload' => false,
            'videoPoster' => '',
            'videoMp4' => '',
            'videoWebM' => '',
            'preload' => 'metadata',
            'playIcon' => '▶',
            'pauseIcon' => '⏸',
            'animationType' => '',
        ];

        $settings = array_merge($defaults, $args);

        // Attributes
        $controlsAttr = (!$settings['enablePlayPause'] && $settings['show_controls']) ? 'controls' : '';
        $autoplayAttr = $settings['autoplay'] ? 'autoplay' : '';
        // Browsers require muted for self-hosted autoplay to work
        $mutedAttr = ($settings['mute'] || $settings['autoplay']) ? 'muted' : '';
        $loopAttr = $settings['loop'] ? 'loop' : '';
        $posterAttr = ($settings['useThumbnail'] && $settings['videoPoster']) ? 'poster="' . esc_url($settings['videoPoster']) . '"' : '';
        $preloadAttr = 'preload="' . esc_attr($settings['preload']) . '"';
        $controlsList = $settings['allowDownload'] ? '' : 'controlsList="nodownload"';

        // Video sources
        $mp4Source = '';
        $webmSource = '';
        $externalSource = '';

        if ($settings['useExternalLink'] && !empty($settings['externalVideoLink'])) {
            // Use external video link
            $videoUrl = $settings['externalVideoLink'];
            $videoType = pathinfo($videoUrl, PATHINFO_EXTENSION) ?: 'mp4';
            $externalSource = sprintf('<source src="%s" type="video/%s"/>', esc_url($videoUrl), esc_attr($videoType));
        } else {
            // Use local video files
            $mp4Source = $settings['videoMP4'] ? sprintf('<source src="%s" type="video/mp4"/>', esc_url($settings['videoMP4'])) : '';
            $webmSource = $settings['videoWebM'] ? sprintf('<source src="%s" type="video/webm"/>', esc_url($settings['videoWebM'])) : '';
        }

        $customControls = '';
        $script = '';
        if ($settings['enablePlayPause']) {
            $isAutoplay = !empty($settings['autoplay']);
            $initialClass   = $isAutoplay ? 'pause' : 'play';
            $initialContent = $isAutoplay ? $settings['pauseIcon'] : $settings['playIcon'];
            $customControls = sprintf(
                '<div class="difl-video-custom-controls" onclick="handleCustomPlayPauseClick(event)">
                <div class="%1$s">
                    <span class="et-pb-icon %4$s" data-play-icon="%2$s" data-pause-icon="%3$s">%5$s</span>
                </div>
            </div>',
                esc_attr($settings['animationType']),
                esc_html($settings['playIcon']),
                esc_html($settings['pauseIcon']),
                esc_attr($initialClass),
                esc_html($initialContent)
            );
        }

        return sprintf(
            '<div class="difl-video-wrapper">
            <div class="difl-video-aspect-ratio" style="position:relative;width:100%%;display:flex;">
                <video class="difl-video-selfhosted"
                    %1$s %2$s %3$s %4$s %5$s %6$s %7$s
                    style="width:100%%;height:100%%;object-fit:cover;"
                    playsinline>
                    %8$s
                    %9$s
                    %10$s
                    Your browser does not support the video tag.
                </video>
                %11$s
            </div>
        </div>',
            $controlsAttr,
            $autoplayAttr,
            $mutedAttr,
            $loopAttr,
            $posterAttr,
            $preloadAttr,
            $controlsList,
            $mp4Source,
            $webmSource,
            $externalSource,
            $customControls
        );
    }

    protected function renderVideoTitle($showTitle, $videoTitle)
    {
        if (($this->props['video_lightbox'] === "off") && $showTitle && $videoTitle !== '0' && !empty($videoTitle)) {
            return sprintf('<h3 class="difl-video-title">%s</h3>', esc_html($videoTitle));
        }

        return '';
    }
    protected function renderVideoPoster($settings)
    {

        $videoPoster = isset($settings['videoPoster']) ? $settings['videoPoster'] : '';
        return '
        <div class="difl-video-poster-wrapper difl-video-wrapper-with-thumbnail">
            <img
                src="' . htmlspecialchars($videoPoster) . '"
                class="difl-video-poster-image difl-video-thumbnail"
            />
            ' . $this->renderLightBoxButton($settings) . '
        </div>
    ';
    }
    protected function renderVideoPlayer($args = [])
    {
        // Merge settings with defaults
        $settings = array_merge([
            'useThumbnail' => false,
            'autoplay' => false,
            'videoLightBox' => false,
            'videoSource' => 'youtube',
            'videoLink' => '#',
            'embedUrl' => '',
            'videoTitle' => 'Video Player',
            'enablePlayPause' => false,
            'playIcon' => '▶',
            'pauseIcon' => '⏸',
            'animationType' => '',
            'showTitle' => false,
            'playOnViewport' => false
        ], $args);


        $videoTitle = $this->getVideoTitle($settings['videoLink'], $settings['videoSource'] ?? $settings['source'] ?? 'youtube');
        $iframeWrapperClass = ($settings['useThumbnail'] && !$settings['autoplay'])
            ? 'difl-video-wrapper-with-thumbnail'
            : 'difl-video-wrapper-without-thumbnail';


        $content = '';

        if ($settings['videoLightBox']) {
            // Lightbox mode: show thumbnail or lightbox button
            $content = $settings['useThumbnail']
                ? $this->renderThumbnail($settings)
                : $this->renderLightBoxButton($settings);

        } elseif (($settings['useThumbnail'] && !$settings['autoplay'])) {
            // Thumbnail mode: show thumbnail (only if not autoplay)
            $content = !$settings['autoplay'] ? $this->renderThumbnail($settings) : '';

        } elseif (!$settings['playOnViewport']) {

            // Direct embed mode: show iframe
            $iframeClass = ($settings['useThumbnail'] && !$settings['autoplay'])
                ? 'difl-video-iframe-with-thumbnail'
                : 'difl-video-iframe-without-thumbnail';


            $autoplayParam = $settings['useThumbnail'] ? '&autoplay=1' : '';
            $iframeSrc = $settings['embedUrl'] . $this->getVideoParams($settings) . $autoplayParam;

            $lazyLoading = ($this->props['lazy_loading'] === "on") ? 'loading="lazy"' : '';

            $content = sprintf(
                '<iframe src="%1$s" %4$s  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="%2$s  difl-video-iframe" title="%3$s"></iframe>',
                esc_url($iframeSrc),
                esc_attr($iframeClass),
                esc_attr($settings['videoTitle']),
                $lazyLoading
            );
        }

        // When play-on-viewport is active, wrapper is initially empty — give it height so IntersectionObserver fires
        $wrapperStyle = !empty($settings['playOnViewport']) ? 'aspect-ratio:16/9;' : '';

        // Build and return the complete video player HTML
        return sprintf(
            '<div class="difl-video-container">%s<div class="%s difl-video-wrapper" style="%s">%s</div></div>',
            $this->renderVideoTitle($settings['showTitle'], $videoTitle),
            esc_attr($iframeWrapperClass),
            $wrapperStyle,
            $content
        );
    }


    // protected function difl_video_enqueue_scripts()
    // {
    //     // lightGallery core
    //     wp_enqueue_style(
    //         'lightgallery-css',
    //         'https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lightgallery.min.css',
    //         array(),
    //         '2.7.2'
    //     );

    //     wp_enqueue_style(
    //         'lg-video-css',
    //         'https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lg-video.min.css',
    //         array('lightgallery-css'),
    //         '2.7.2'
    //     );

    //     wp_enqueue_script(
    //         'lightgallery-js',
    //         'https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.min.js',
    //         array(),
    //         '2.7.2',
    //         true
    //     );

    //     wp_enqueue_script(
    //         'lg-video-js',
    //         'https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/video/lg-video.min.js',
    //         array('lightgallery-js'),
    //         '2.7.2',
    //         true
    //     );

    //     // Your custom script
    //     wp_enqueue_script(
    //         'difl-video-script',
    //         plugin_dir_url(__FILE__) . 'js/your-script.js',
    //         array('lightgallery-js', 'lg-video-js'),
    //         '1.0.0',
    //         true
    //     );
    // }

    public function render($orderClass, $settings)
    {

        wp_enqueue_script('df_advanced_video');


        // Debug the settings and conditions
        // error_log('Video Module Settings: ' . print_r($settings, true));

        $html = '';

        if (!empty($settings['source'])) {
            if ($settings['source'] === "selfhosted") {
                if ($settings['videoLightBox']) {
                    if ($settings['useThumbnail'] && !empty($this->props['thumbnail_image'])) {
                        $html = $this->renderVideoPoster($settings);
                    } else {
                        $html = $this->renderLightBoxButton($settings);
                    }
                } else {
                    $html = $this->renderSelfHostedVideo($settings);
                }
            } else {
                if (!empty($settings['embedUrl'])) {
                    $html = $this->renderVideoPlayer($settings);
                } else {
                    $html = '<p>Please add a valid video URL</p>';
                }
            }
        } else {
            $html = '<p>Please select a video source</p>';
        }

        $innerClasses = ['difl-advanced-video'];
        if (!empty($settings['playOnViewport']) && !empty($settings['autoplay'])) {
            $innerClasses[] = 'df-play-on-viewport';
        }
        if (!empty($settings['stickyVideo'])) {
            $innerClasses[] = 'df-sticky-video';
        }

        $inner = sprintf(
            '<div class="%s">%s</div>',
            esc_attr(implode(' ', $innerClasses)),
            $html
        );

        if (!empty($settings['stickyVideo'])) {
            return sprintf('<div class="difl-sticky-video-slot">%s</div>', $inner);
        }

        return $inner;
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {


        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        $orderClass = str_replace(['/', '-'], '_', $block->parsed_block['id']);
        $startTime = $instance->props['start_time'] ?? '0';
        $endTime = $instance->props['end_time'] ?? '0';
        $customThumbnail = ($instance->props['use_custom_thumbnail'] ?? '') === 'on';
        $useThumbnail = ($instance->props['use_thumbnail'] ?? '') === 'on';
        $source = $instance->props['source'] ?? 'youtube';
        $videoLink = $instance->props['video_link'] ?? '';
        $embedUrl = $instance->getVideoEmbedUrl($videoLink, $source);
        $autoplay = ($instance->props['autoplay'] ?? '') === 'on';
        $show_controls = ($instance->props['show_controls'] ?? '') === 'on';
        $thumbnailImage = '';
        if ($source !== 'selfhosted') {
            $thumbnailImage = $customThumbnail
                ? ($instance->props['thumbnail_image'] ?? '')
                : $instance->getVideoThumbnail($videoLink, $source);
        }
        $mute = ($instance->props['mute'] ?? '') === 'on';
        $loop = ($instance->props['loop'] ?? '') === 'on';
        $showTitle = ($instance->props['show_title'] ?? '') === 'on';
        $playbackSpeed = $instance->props['playback_speed'] ?? '';
        $showCaption = ($instance->props['show_caption'] ?? '') === 'on';
        $videoLightBox = $instance->props['video_lightbox'] === 'on';
        $captionLanguage = $instance->props['caption_language'] ?? '';
        $videoMP4 = $instance->props['video_mp4'] ?? '';
        $videoWebM = $instance->props['video_webm'] ?? '';
        $preload = $instance->props['preload'] ?? 'metadata';
        $allowDownload = ($instance->props['allow_download'] ?? '') === 'on';
        $videoPoster = $instance->props['thumbnail_image'] ?? '';
        $useExternalLink = ($instance->props['use_external_link'] ?? '') === 'on';
        $externalVideoLink = $instance->props['external_video_link'] ?? '';
        $useCustomThumbnailPlayIcon = ($instance->props['use_custom_thumbnail_play_icon'] ?? '') === 'on';
        $customPlayIcon = $useCustomThumbnailPlayIcon?Utils::process_font_icon($instance->props['custom_play_icon']) :'';
        $thumbnailPlayButtonIconImage = $instance->props['thumbnail_play_button_icon_image'] ?? '';
        $enablePlayPause = ($instance->props['use_custom_playpause_controller'] ?? '') === 'on';
        $playIcon = Utils::process_font_icon($instance->props['custom_play_controller_icon']) ?? '';
        $pauseIcon = Utils::process_font_icon($instance->props['custom_pause_controller_icon']) ?? '';
        $animationType = $instance->props['custom_controller_icon_animation_type'] ?? '';
        $playOnViewport = ($instance->props['play_on_viewport'] === 'on');
        $viewportOffset = ($instance->props['viewport_offset']);
        $viewportOffsetBottom = ($instance->props['viewport_offset_bottom']);
        $stickyVideo = ($instance->props['sticky_video'] ?? '') === 'on';
        $stickyVideoPosition = $instance->props['sticky_video_position'] ?? 'bottom_right';

        // Responsive sticky values must be read straight from $attrs — $instance->props
        // only resolves the first match (desktop) via searchObject.
        $stickyInner = $attrs['video_obj_settings']['innerContent'] ?? [];

        $stickyVideoWidth        = $stickyInner['desktop']['value']['sticky_video_width']    ?? '320';
        $stickyVideoWidthTablet  = $stickyInner['tablet']['value']['sticky_video_width']     ?? $stickyVideoWidth;
        $stickyVideoWidthPhone   = $stickyInner['phone']['value']['sticky_video_width']      ?? $stickyVideoWidthTablet;

        $stickyVideoOffsetX        = $stickyInner['desktop']['value']['sticky_video_offset_x'] ?? '16';
        $stickyVideoOffsetXTablet  = $stickyInner['tablet']['value']['sticky_video_offset_x']  ?? $stickyVideoOffsetX;
        $stickyVideoOffsetXPhone   = $stickyInner['phone']['value']['sticky_video_offset_x']   ?? $stickyVideoOffsetXTablet;

        $stickyVideoOffsetY        = $stickyInner['desktop']['value']['sticky_video_offset_y'] ?? '16';
        $stickyVideoOffsetYTablet  = $stickyInner['tablet']['value']['sticky_video_offset_y']  ?? $stickyVideoOffsetY;
        $stickyVideoOffsetYPhone   = $stickyInner['phone']['value']['sticky_video_offset_y']   ?? $stickyVideoOffsetYTablet;

        $stickyVideoZIndex = $instance->props['sticky_video_z_index'] ?? '99999999';
        $stickyVideoShadow = ($instance->props['sticky_video_shadow'] ?? '') === 'on';
        $stickyVideoCloseButton = ($instance->props['sticky_video_close_button'] ?? '') === 'on';
        $stickyVideoOnMobile = ($instance->props['sticky_video_on_mobile'] ?? '') === 'on';
        $rawStickyCloseBtnIcon = $instance->props['sticky_close_btn_icon'] ?? '';
        $stickyCloseBtnIcon = is_array($rawStickyCloseBtnIcon) ? $rawStickyCloseBtnIcon : (json_decode($rawStickyCloseBtnIcon, true) ?: null);
        $stickyCloseBtnPosition = $instance->props['sticky_close_btn_position'] ?? 'top_right';
        $lightboxOpenAnimation = $instance->props['lightbox_open_animation'];
        $lightboxCloseAnimation = $instance->props['lightbox_close_animation'];
        $lightboxRevealAnimationDuration = $instance->props['lightbox_reveal_animation_duration'] ?? 500;

        $settings = compact(
            'orderClass',
            'startTime',
            'endTime',
            'customThumbnail',
            'useThumbnail',
            'thumbnailImage',
            'source',
            'videoLink',
            'embedUrl',
            'autoplay',
            'show_controls',
            'mute',
            'loop',
            'showTitle',
            'playbackSpeed',
            'showCaption',
            'videoLightBox',
            'captionLanguage',
            'videoMP4',
            'videoWebM',
            'preload',
            'allowDownload',
            'videoPoster',
            'useExternalLink',
            'externalVideoLink',
            'customPlayIcon',
            'useCustomThumbnailPlayIcon',
            'thumbnailPlayButtonIconImage',
            'enablePlayPause',
            'playIcon',
            'pauseIcon',
            'animationType',
            'playOnViewport',
            'viewportOffset',
            'viewportOffsetBottom',
            'stickyVideo',
            'stickyVideoPosition',
            'stickyVideoWidth',
            'stickyVideoWidthTablet',
            'stickyVideoWidthPhone',
            'stickyVideoOffsetX',
            'stickyVideoOffsetXTablet',
            'stickyVideoOffsetXPhone',
            'stickyVideoOffsetY',
            'stickyVideoOffsetYTablet',
            'stickyVideoOffsetYPhone',
            'stickyVideoZIndex',
            'stickyVideoShadow',
            'stickyVideoCloseButton',
            'stickyVideoOnMobile',
            'stickyCloseBtnIcon',
            'stickyCloseBtnPosition',
            'lightboxOpenAnimation',
            'lightboxCloseAnimation',
            'lightboxRevealAnimationDuration'
        );
        if ($settings['videoLightBox']) {
            $captionParams = '';
            $timeParams    = '';
            $timeFragment  = '';
            $startTime     = (int) $settings['startTime'];
            $endTime       = (int) $settings['endTime'];

            if ($settings['showCaption']) {
                switch ($settings['source']) {
                    case 'youtube':
                        $captionParams .= '&cc_load_policy=1';
                        if ($settings['captionLanguage'] && $settings['captionLanguage'] !== 'auto') {
                            $captionParams .= '&cc_lang_pref=' . urlencode($settings['captionLanguage']);
                            $captionParams .= '&hl=' . urlencode($settings['captionLanguage']);
                        }
                        break;
                    case 'vimeo':
                        if ($settings['captionLanguage'] && $settings['captionLanguage'] !== 'auto') {
                            $captionParams .= '&texttrack=' . urlencode($settings['captionLanguage']);
                        }
                        break;
                    case 'dailymotion':
                        if ($settings['captionLanguage'] && $settings['captionLanguage'] !== 'auto') {
                            $captionParams .= '&subtitles-default=' . urlencode($settings['captionLanguage']);
                        }
                        break;
                }
            }

            switch ($settings['source']) {
                case 'youtube':
                    if ($startTime > 0) $timeParams .= '&start=' . $startTime;
                    if ($endTime > 0)   $timeParams .= '&end=' . $endTime;
                    break;
                case 'vimeo':
                    $timeFragment = '#t=' . $startTime . ($endTime > 0 ? ',' . $endTime : '');
                    break;
                case 'dailymotion':
                    if ($startTime > 0) $timeParams .= '&start=' . $startTime;
                    if ($endTime > 0)   $timeParams .= '&end=' . $endTime;
                    break;
            }

            $settings['iframeSrc'] = sprintf('%1$s?&autoplay=1%2$s%3$s%4$s', $settings['embedUrl'], $captionParams, $timeParams, $timeFragment);
        } elseif ($settings['useThumbnail']) {
            $settings['iframeSrc'] = sprintf('%1$s%2$s&autoplay=1', $settings['embedUrl'], $instance->getVideoParams($settings));
        } else {
            $settings['iframeSrc'] = sprintf('%1$s%2$s', $settings['embedUrl'], $instance->getVideoParams($settings));
        }



        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'moduleClassName' => '',
                'name' => $block->block_type->name,
                'classnamesFunction' => [AdvancedVideo::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [AdvancedVideo::class, 'module_styles'],
                'scriptDataComponent' => [AdvancedVideo::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                    [
                        'attrName' => 'module',
                    ]
                ) . "<div class='difl-advanced-video-container' data-settings=" . json_encode($settings) . ">" . $instance->render(sprintf("difl_video_module_%1s", $block->parsed_block['orderIndex']), $settings) . "</div>". self::getElementsHtml($content),
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/advanced-video';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [AdvancedVideo::class, 'render_callback'],
                    ]
                );
            }
        );
    }

}
