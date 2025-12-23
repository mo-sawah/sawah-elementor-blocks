<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Elementor_Sawah_Mobile_Feature extends \Elementor\Widget_Base {

    public function get_name() { return 'sawah_mobile_feature'; }
    public function get_title() { return 'Sawah Mobile Feature'; }
    public function get_icon() { return 'eicon-single-post'; }
    public function get_categories() { return [ 'sawah_blocks' ]; } 

    protected function register_controls() {
        // --- SECTION: CONTENT SOURCE ---
        $this->start_controls_section('section_query', [ 'label' => 'Content Source' ]);
        
        $categories = get_terms( 'category', [ 'hide_empty' => false ] );
        $cat_options = [];
        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) $cat_options[ $category->term_id ] = $category->name;
        }
        $this->add_control('cat_ids', [ 'label' => 'Filter by Category (Fallback)', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $cat_options, 'multiple' => true, 'label_block' => true ]);
        
        $this->add_control('priority_tag', [ 'label' => 'Priority Tag (Slug)', 'type' => \Elementor\Controls_Manager::TEXT, 'description' => 'If a post exists with this tag within the freshness time, it will override the category selection.' ]);
        
        $this->add_control('priority_hours', [ 
            'label' => 'Priority Freshness (Hours)', 
            'type' => \Elementor\Controls_Manager::NUMBER, 
            'default' => 48, 
            'description' => 'Only posts published within this many hours will be prioritized.'
        ]);
        
        $this->end_controls_section();

        // --- SECTION: LAYOUT ---
        $this->start_controls_section('section_layout', [ 'label' => 'Layout & Meta' ]);
        $this->add_control('show_category', [ 'label' => 'Show Category Label', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        $this->add_control('show_views', [ 'label' => 'Show Views', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        $this->add_control('show_comments', [ 'label' => 'Show Comments', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        
        $this->add_control('views_meta_key', [ 'label' => 'Views Meta Key', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'post_views_count', 'default' => 'post_views_count', 'condition' => [ 'show_views' => 'yes' ] ]);
        
        $this->add_control('title_lines', [ 'label' => 'Title Lines Limit', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'selectors' => [ '{{WRAPPER}} .smf-title a' => '-webkit-line-clamp: {{VALUE}}' ] ]);
        
        $this->add_responsive_control('image_height', [
            'label' => 'Image Height',
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'vh' ],
            'range' => [ 'px' => [ 'min' => 150, 'max' => 600 ] ],
            'default' => [ 'unit' => 'px', 'size' => 250 ],
            'selectors' => [ '{{WRAPPER}} .smf-media' => 'height: {{SIZE}}{{UNIT}};' ],
        ]);
        
        $this->end_controls_section();

        // --- SECTION: STYLE ---
        $this->start_controls_section('section_style', [ 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]);
        
        $this->add_control('color_accent', [ 'label' => 'Accent Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f47b23', 'selectors' => [ '{{WRAPPER}} .smf-meta-right i' => 'color: {{VALUE}}' ] ]);
        $this->add_control('color_cat', [ 'label' => 'Category Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#666666', 'selectors' => [ '{{WRAPPER}} .smf-cat' => 'color: {{VALUE}}' ] ]);
        $this->add_control('color_title', [ 'label' => 'Title Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => [ '{{WRAPPER}} .smf-title a' => 'color: {{VALUE}}' ] ]);

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'title_typography', 'label' => 'Title Typography', 'selector' => '{{WRAPPER}} .smf-title' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'cat_typography', 'label' => 'Category Typography', 'selector' => '{{WRAPPER}} .smf-cat' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'meta_typography', 'label' => 'Meta Typography', 'selector' => '{{WRAPPER}} .smf-meta-right' ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $priority_hours = (int)$settings['priority_hours'];
        $found_post = null;

        // 1. Try Priority Tag Logic
        if ( ! empty( $settings['priority_tag'] ) ) {
            $priority_args = [
                'post_type' => 'post', 'posts_per_page' => 1, 'ignore_sticky_posts' => true,
                'tax_query' => [[ 'taxonomy' => 'post_tag', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $settings['priority_tag'] ) ) ]],
                'date_query' => [[ 'after' => $priority_hours . ' hours ago', 'column' => 'post_date' ]],
                'orderby' => 'date', 'order' => 'DESC'
            ];
            $posts = get_posts( $priority_args );
            if ( ! empty( $posts ) ) {
                $found_post = $posts[0];
            }
        }

        // 2. Fallback to Category
        if ( ! $found_post ) {
            $filler_tax_query = [];
            if ( ! empty( $settings['cat_ids'] ) ) {
                $filler_tax_query[] = [ 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $settings['cat_ids'] ];
            }
            $filler_args = [
                'post_type' => 'post', 'posts_per_page' => 1, 'ignore_sticky_posts' => true, 
                'tax_query' => $filler_tax_query ?: null,
                'orderby' => 'date', 'order' => 'DESC'
            ];
            $posts = get_posts( $filler_args );
            if ( ! empty( $posts ) ) {
                $found_post = $posts[0];
            }
        }

        if ( ! $found_post ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="elementor-alert elementor-alert-warning">No posts found based on current criteria.</div>';
            }
            return;
        }

        // Setup Data
        global $post;
        $post = $found_post;
        setup_postdata( $post );
        
        $pid = get_the_ID();
        $link = get_permalink($pid);
        $tit = get_the_title($pid);
        $img = get_the_post_thumbnail_url($pid, 'large') ?: '';
        $com_count = get_comments_number($pid);
        $views = function_exists('pvc_get_post_views') ? pvc_get_post_views($pid) : (int)get_post_meta($pid, $settings['views_meta_key'], true);
        
        // Get primary category
        $cat_label = '';
        $cats = get_the_category($pid);
        if(!empty($cats)) $cat_label = $cats[0]->name;
        
        ?>
        <div class="sawah-mobile-feature">
            <a href="<?php echo esc_url($link); ?>" class="smf-media">
                <?php if($img): ?>
                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($tit); ?>" loading="lazy">
                <?php endif; ?>
            </a>
            
            <div class="smf-content">
                <div class="smf-meta-wrap">
                    <?php if('yes' === $settings['show_category'] && $cat_label): ?>
                        <span class="smf-cat"><?php echo esc_html($cat_label); ?></span>
                    <?php endif; ?>

                    <div class="smf-meta-right">
                        <?php if('yes' === $settings['show_views']): ?> 
                            <span class="smf-views"><i class="eicon-preview-medium"></i> <?php echo $views; ?></span> 
                        <?php endif; ?>
                        <?php if('yes' === $settings['show_comments']): ?> 
                            <span class="smf-comments"><i class="eicon-comments"></i> <?php echo $com_count; ?></span> 
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="smf-title">
                    <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($tit); ?></a>
                </h3>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    }
}

// Add Styles specifically for this block
add_action('wp_head', function () {
    if ( did_action('sawah_mobile_feature_css_loaded') ) return;
    do_action('sawah_mobile_feature_css_loaded');
    ?>
    <style>
    .sawah-mobile-feature { position: relative; width: 100%; display: flex; flex-direction: column; gap: 12px; }
    
    .smf-media { 
        display: block; width: 100%; overflow: hidden; position: relative; border-radius: 4px;
        /* Height controlled by Elementor setting */
    }
    .smf-media img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
    .smf-media:hover img { transform: scale(1.05); }

    .smf-content { display: flex; flex-direction: column; gap: 6px; }

    .smf-meta-wrap { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    
    .smf-cat { 
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        color: #666; /* Default, overridden by control */
    }
    
    .smf-meta-right { display: flex; gap: 10px; font-size: 12px; color: #999; }
    .smf-meta-right span { display: flex; align-items: center; gap: 4px; }
    .smf-meta-right i { color: #f47b23; /* Default, overridden by control */ }

    .smf-title { margin: 0; padding: 0; line-height: 1.3; font-size: 20px; font-weight: 700; }
    .smf-title a { 
        color: #111; text-decoration: none; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; 
        transition: color 0.2s;
    }
    .smf-title a:hover { color: #d60000; }
    </style>
    <?php
});