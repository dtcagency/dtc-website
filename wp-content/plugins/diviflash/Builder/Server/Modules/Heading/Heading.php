<?php
namespace DIFL\Server\Modules\Heading;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;


class Heading implements DependencyInterface {
  
  use Styles;

	public static function module_script_data( $args ) {
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}
	 
	public static function render_callback( $attrs, $content, $block, $elements ) {
    
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

    $useDualText = isset($attrs['use_dual_text']['innerContent']['desktop']['value']) ? $attrs['use_dual_text']['innerContent']['desktop']['value'] : 'off';
    $useDualCustom = isset($attrs['use_dual_text_custom']['innerContent']['desktop']['value']) ? $attrs['use_dual_text_custom']['innerContent']['desktop']['value'] : 'off';
    $dualCustomText = isset($attrs['custom_text_input']['innerContent']['desktop']['value']) ? $attrs['custom_text_input']['innerContent']['desktop']['value'] : '';
    $headingTag = isset($attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] : 'h3';
    $titlePrefixDf = $attrs['title_prefix']['innerContent']['desktop']['value'] ?? '';
    $titleInfixDf  = $attrs['title_infix']['innerContent']['desktop']['value'] ?? '';
    $titleSuffixDf = $attrs['title_suffix']['innerContent']['desktop']['value'] ?? '';

    $eClipPrefix = ($attrs['df_prefix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on' ? " difl_e_clip_pre" : "";
    $eClipSuffix = ($attrs['df_suffix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on' ? " difl_e_clip_suf" : "";
    $eClipInfix = ($attrs['df_infix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on' ? " difl_e_clip_in" : "";


		$title_prefix = $titlePrefixDf ? '<span class="prefix '.$eClipPrefix.'">' . esc_html($titlePrefixDf) . '</span>' : '';
		$title_infix = $titleInfixDf ? '<span class="infix '.$eClipInfix.'">' . esc_html($titleInfixDf) . '</span>' : '';
		$title_suffix = $titleSuffixDf ? '<span class="suffix '.$eClipSuffix.'">' . esc_html($titleSuffixDf) . '</span>' : '';

    // Title.
    $title = sprintf('<%4$s class="df-heading">%1$s %2$s %3$s</%4$s>', $title_prefix, $title_infix, $title_suffix, $headingTag);
    $hasDualText = '';
    $heading_dual_text = '';
    if ($useDualText === 'on') {
      $dual_title = sprintf('%1$s %2$s %3$s', $title_prefix, $title_infix, $title_suffix);

      if ($useDualCustom !== 'on') {
        $heading_dual_text = sprintf(
          '<div class="df-heading-dual_text" data-title="%1$s"></div>',
          wp_strip_all_tags(trim($dual_title))
        );
      } else {
        $heading_dual_text = sprintf(
          '<div class="df-heading-dual_text" data-title="%1$s"></div>',
          wp_strip_all_tags($dualCustomText)
        );
      }

      $hasDualText = ' has-dual-text';
    }

    $imageValue = isset($attrs['divider_image']['innerContent']['desktop']['value']) ? $attrs['divider_image']['innerContent']['desktop']['value'] : [];
    $imageAlt = isset($attrs['divider_image_alt_text']['innerContent']['desktop']['value']) ? $attrs['divider_image_alt_text']['innerContent']['desktop']['value'] : '';
    $imgAltText = $imageAlt ?:(isset($imageValue['alt']) ? $imageValue['alt'] : '');

    $iconValue = isset($attrs['divider_icon']['decoration']['icon']['desktop']['value']['icon']) ? $attrs['divider_icon']['decoration']['icon']['desktop']['value']['icon'] : '';
    $useDivider = isset($attrs['use_divider']['innerContent']['desktop']['value']) ? $attrs['use_divider']['innerContent']['desktop']['value'] : 'off';
    $useDividerIcon = isset($attrs['use_divider_icon']['innerContent']['desktop']['value']) ? $attrs['use_divider_icon']['innerContent']['desktop']['value'] : 'off';
    $useDividerImg = isset($attrs['use_divider_image']['innerContent']['desktop']['value']) ? $attrs['use_divider_image']['innerContent']['desktop']['value'] : 'off';
    $dividerPosition = $attrs['divider_position']['innerContent']['desktop']['value'] ?? 'bottom';

    if ($useDivider === 'on') {
      
      $divider_icon = '';
      if ($useDividerIcon === 'on') {
        $processIcon  = Utils::process_font_icon( $iconValue ) ?? '1';
        $divider_icon = '<span class="et-pb-icon">'.$processIcon.'</span>';
      }

      if ($useDividerImg === 'on' && isset($imageValue['src'])) {
        $divider_icon = '<img src="'.$imageValue['src'].'" class="divider-image" alt="'.$imgAltText.'" />';
      }

      $divider = '
        <div class="df-heading-divider">
          <div class="df-divider-line"></div>
          '.$divider_icon.'
        </div>';
      $heading_divider = ($dividerPosition !== 'top') ? $heading_dual_text.$title.$divider : $divider.$heading_dual_text.$title;
    } else {
      $heading_divider = $heading_dual_text.$title;
    }

		$difl_advanced_heading_item = '
			<div class="df-heading-container '.$hasDualText.'">
				'.$heading_divider.'
			</div>
		';

		self::register_divi_assets();

		return Module::render(
			[
				// FE only.
				'orderIndex'         => $block->parsed_block['orderIndex'],
				'storeInstance'      => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                 => $block->parsed_block['id'],
				'name'               => $block->block_type->name,
				'moduleCategory'     => $block->block_type->category,
				'attrs'              => $attrs,
				'elements'           => $elements,
				'classnamesFunction' => [ self::class, 'classnames' ],
				'stylesComponent'    => [ self::class, 'styles' ],
				'moduleCategory'      => $block->block_type->category,
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'parentAttrs'        => $parent_attrs,
				'parentId'           => $parent->id ?? '',
				'parentName'         => $parent->blockName ?? '',
				'children'           => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) .$difl_advanced_heading_item,
			]
		);
	}
  
	public static function difl_load_required_divi_assets( $assets_list, $assets_args, $instance  ) {
	   
		$temp_url	    = get_template_directory_uri();
 		$icons_all 	  = $temp_url."/includes/builder/feature/dynamic-assets/assets/css/icons_all.css";
 		$icons_fa_all = $temp_url."/includes/builder/feature/dynamic-assets/assets/css/icons_fa_all.css";
 	
 		if ( ! isset( $assets_list['et_icons_all'] ) ) {
 			$assets_list['et_icons_all'] = [
 				'css' => $icons_all,
 			];
 		}
 
 		if ( ! isset( $assets_list['et_icons_fa'] ) ) {
 			$assets_list['et_icons_fa'] = [
 				'css' => $icons_fa_all,
 			];
 		}

		return $assets_list;
	}

	// Register the required Divi assets dynamically.
	public static function register_divi_assets() {
		
		add_filter('divi_frontend_assets_dynamic_assets_global_assets_list',[ self::class, 'difl_load_required_divi_assets' ], 10, 3);
		add_filter('divi_frontend_assets_dynamic_assets_late_global_assets_list', [ self::class, 'difl_load_required_divi_assets' ], 10, 3);

	}

  public static function classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$text_options_classnames = TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] );

		if ( $text_options_classnames ) {
			$classnames_instance->add( $text_options_classnames, true );
		}
	}

  public function load() {
    $module_json_folder_path = DIFL_MODULES_JSON_PATH . 'Heading/';

    add_action('init', function () use ($module_json_folder_path) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [self::class, 'render_callback'],
          ]
        );
      }
    );
  }
}