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
        
        $this->add_control('priority_hours', [ 'label' => 'Priority Freshness (Hours)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 48 ]);
        
        $this->end_controls_section();

        // --- SECTION: LAYOUT ---
        $this->start_controls_section('section_layout', [ 'label' => 'Layout & Meta' ]);
        
        $this->add_responsive_control('image_ratio', [
            'label' => 'Image Ratio',
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range' => [ '%' => [ 'min' => 30, 'max' => 150 ] ],
            'default' => [ 'unit' => '%', 'size' => 100 ], // 100% = 1:1 Square
            'selectors' => [ '{{WRAPPER}} .smf-ratio' => 'padding-bottom: {{SIZE}}%;' ],
            'description' => '100% = Square, 75% = 4:3 Landscape, 125% = Portrait'
        ]);

        $this->add_control('show_category', [ 'label' => 'Show Category Label', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        $this->add_control('show_views', [ 'label' => 'Show Views', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        $this->add_control('show_comments', [ 'label' => 'Show Comments', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        
        $this->add_control('views_meta_key', [ 'label' => 'Views Meta Key', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'post_views_count', 'default' => 'post_views_count', 'condition' => [ 'show_views' => 'yes' ] ]);
        
        $this->add_control('title_lines', [ 'label' => 'Title Lines Limit', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'selectors' => [ '{{WRAPPER}} .post-title a' => '-webkit-line-clamp: {{VALUE}}' ] ]);
        
        $this->end_controls_section();

        // --- SECTION: STYLE ---
        $this->start_controls_section('section_style', [ 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]);
        
        $this->add_control('content_padding', [
            'label' => 'Content Padding',
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'default' => [ 'top' => 15, 'right' => 15, 'bottom' => 0, 'left' => 15, 'unit' => 'px', 'isLinked' => false ],
            'selectors' => [ '{{WRAPPER}} .smf-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ]);

        $this->add_control('color_cat', [ 'label' => 'Category Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#d60000', 'selectors' => [ '{{WRAPPER}} .post-cat a' => 'color: {{VALUE}}' ] ]);
        $this->add_control('color_title', [ 'label' => 'Title Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => [ '{{WRAPPER}} .post-title a' => 'color: {{VALUE}}' ] ]);
        $this->add_control('color_meta', [ 'label' => 'Meta/Icon Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#999999', 'selectors' => [ '{{WRAPPER}} .post-meta-items, {{WRAPPER}} .post-meta-items i' => 'color: {{VALUE}}' ] ]);

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'title_typography', 'label' => 'Title Typography', 'selector' => '{{WRAPPER}} .post-title' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'cat_typography', 'label' => 'Category Typography', 'selector' => '{{WRAPPER}} .post-cat' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'meta_typography', 'label' => 'Meta Typography', 'selector' => '{{WRAPPER}} .post-meta-items' ]);

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
        $img_url = get_the_post_thumbnail_url($pid, 'large') ?: '';
        $com_count = get_comments_number($pid);
        $views = function_exists('pvc_get_post_views') ? pvc_get_post_views($pid) : (int)get_post_meta($pid, $settings['views_meta_key'], true);
        
        // Formatted Views (e.g., 4.4k)
        if ($views > 1000) {
            $views = round($views / 1000, 1) . 'k';
        }

        // Get primary category
        $cat_label = '';
        $cats = get_the_category($pid);
        if(!empty($cats)) $cat_label = $cats[0]->name;
        
        ?>
        <div class="sawah-mobile-feature block-wrap">
            <article class="l-post grid-post grid-base-post">
                
                <div class="media">
                    <a href="<?php echo esc_url($link); ?>" class="image-link media-ratio smf-ratio" title="<?php echo esc_attr($tit); ?>">
                        <span class="img bg-cover" style="background-image: url('<?php echo esc_url($img_url); ?>');"></span>
                    </a>
                </div>

                <div class="content smf-content">
                    <div class="post-meta post-meta-a">
                        <div class="post-meta-items meta-above">
                            
                            <?php if('yes' === $settings['show_category'] && $cat_label): ?>
                            <span class="meta-item has-next-icon post-cat">
                                <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>" class="category"><?php echo esc_html($cat_label); ?></a>
                            </span>
                            <?php endif; ?>

                            <?php if('yes' === $settings['show_comments']): ?> 
                            <span class="has-next-icon meta-item comments has-icon">
                                <a href="<?php echo esc_url($link); ?>#comments">
                                    <i class="tsi tsi-comment-o"></i><?php echo $com_count; ?>
                                </a>
                            </span>
                            <?php endif; ?>
                            
                            <?php if('yes' === $settings['show_views']): ?> 
                            <span class="meta-item post-views has-icon">
                                <i class="tsi tsi-bar-chart-2"></i><?php echo $views; ?> <span>Views</span>
                            </span>
                            <?php endif; ?>

                        </div>
                        
                        <h2 class="is-title post-title">
                            <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($tit); ?></a>
                        </h2>
                    </div>
                </div>

            </article>
        </div>
        <?php
        wp_reset_postdata();
    }
}

// Styles to replicate the look exactly
add_action('wp_head', function () {
    if ( did_action('sawah_mobile_feature_css_loaded') ) return;
    do_action('sawah_mobile_feature_css_loaded');
    ?>
    <style>
    /* RESET / LAYOUT */
    .sawah-mobile-feature { width: 100%; max-width: 100%; box-sizing: border-box; }
    .sawah-mobile-feature .l-post { display: flex; flex-direction: column; width: 100%; margin: 0; padding: 0; border: none; }
    
    /* MEDIA: Full Width, No Radius, Ratio Control */
    .sawah-mobile-feature .media { width: 100%; margin: 0; padding: 0; border-radius: 0; overflow: hidden; position: relative; }
    .sawah-mobile-feature .image-link { display: block; position: relative; width: 100%; overflow: hidden; }
    
    /* RATIO & IMAGE */
    /* .smf-ratio padding-bottom is handled by inline style in PHP for control */
    .sawah-mobile-feature .img.bg-cover { 
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
        background-position: center; background-size: cover; background-repeat: no-repeat;
        transition: transform 0.4s ease;
    }
    .sawah-mobile-feature .media:hover .img.bg-cover { transform: scale(1.05); }

    /* CONTENT BOX */
    /* .smf-content padding is handled by inline style in PHP for control */
    .sawah-mobile-feature .smf-content { width: 100%; box-sizing: border-box; }

    /* META STRIP */
    .sawah-mobile-feature .post-meta-items { 
        display: flex; align-items: center; flex-wrap: wrap; 
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
        margin-bottom: 8px; color: #999;
    }
    .sawah-mobile-feature .meta-item { display: inline-flex; align-items: center; margin-right: 12px; }
    .sawah-mobile-feature .meta-item:last-child { margin-right: 0; }
    
    /* ICONS */
    .sawah-mobile-feature .meta-item i { margin-right: 4px; font-size: 13px; position: relative; top: -1px; }

    /* CATEGORY */
    .sawah-mobile-feature .post-cat a { font-weight: 700; text-decoration: none; transition: color 0.2s; }

    /* TITLE */
    .sawah-mobile-feature .post-title { 
        margin: 0; padding: 0; font-size: 18px; line-height: 1.35; font-weight: 700; 
    }
    .sawah-mobile-feature .post-title a { 
        display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; 
        text-decoration: none; color: inherit; transition: color 0.2s;
    }
    .sawah-mobile-feature .post-title a:hover { color: #d60000; /* Fallback highlight */ }

    </style>
    <?php
});