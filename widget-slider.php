<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Elementor_Sawah_Slider extends \Elementor\Widget_Base {

    public function get_name() { return 'sawah_slider'; }
    public function get_title() { return 'Sawah Slider'; }
    public function get_icon() { return 'eicon-slides'; }
    
    // CHANGED: This assigns it to your new section
    public function get_categories() { return [ 'sawah_blocks' ]; } 
    
    public function get_script_depends() { return [ 'swiper' ]; }
    public function get_style_depends() { return [ 'swiper' ]; }

    protected function register_controls() {
        // --- SECTION: CONTENT SOURCE ---
        $this->start_controls_section('section_query', [ 'label' => 'Content Source' ]);
        $this->add_control('posts_count', [ 'label' => 'Total Posts', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 5 ]);
        
        $categories = get_terms( 'category', [ 'hide_empty' => false ] );
        $cat_options = [];
        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) $cat_options[ $category->term_id ] = $category->name;
        }
        $this->add_control('cat_ids', [ 'label' => 'Filter by Category', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $cat_options, 'multiple' => true, 'label_block' => true ]);
        $this->add_control('priority_tag', [ 'label' => 'Priority Tag (Slug)', 'type' => \Elementor\Controls_Manager::TEXT ]);
        $this->add_control('priority_days', [ 'label' => 'Priority Freshness (Days)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 2 ]);
        $this->end_controls_section();

        // --- SECTION: VIDEO SETTINGS ---
        $this->start_controls_section('section_video', [ 'label' => 'Video Support' ]);
        
        $this->add_control('enable_video', [ 'label' => 'Enable Featured Video', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ]);
        $this->add_control('video_meta_key', [ 'label' => 'Video Custom Field Key', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '_bunyad_featured_video', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_autoplay', [ 'label' => 'Autoplay', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_mute', [ 'label' => 'Mute', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no', 'description' => 'Required for Autoplay.', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_loop', [ 'label' => 'Loop Video', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'no', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_modest', [ 'label' => 'Hide Related/Branding', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_controls', [ 'label' => 'Player Controls', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => [ 'enable_video' => 'yes' ] ]);
        $this->add_control('video_playsinline', [ 'label' => 'Play Inline (Mobile)', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => [ 'enable_video' => 'yes' ] ]);

        $this->end_controls_section();

        // --- SECTION: LAYOUT & TEXT ---
        $this->start_controls_section('section_layout', [ 'label' => 'Layout & Text' ]);
        $this->add_control('heading_meta', [ 'label' => 'Meta Header', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ]);
        $this->add_control('show_date', [ 'label' => 'Show Date', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ]);
        $this->add_control('show_time', [ 'label' => 'Show Time', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ]);
        $this->add_control('show_comments', [ 'label' => 'Show Comments', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ]);
        $this->add_control('show_views', [ 'label' => 'Show Views', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ]);
        $this->add_control('views_meta_key', [ 'label' => 'Views Meta Key', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'post_views_count', 'default' => 'post_views_count', 'condition' => [ 'show_views' => 'yes' ] ]);

        $this->add_control('heading_limits', [ 'label' => 'Line Limits', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ]);
        $this->add_control('main_title_lines', [ 'label' => 'Main Title Lines', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'selectors' => [ '{{WRAPPER}} .ptm-title' => '-webkit-line-clamp: {{VALUE}}' ] ]);
        $this->add_control('excerpt_lines', [ 'label' => 'Excerpt Lines', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 2, 'selectors' => [ '{{WRAPPER}} .ptm-excerpt' => '-webkit-line-clamp: {{VALUE}}' ] ]);
        $this->add_control('related_title_lines', [ 'label' => 'Related Title Lines', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 2, 'selectors' => [ '{{WRAPPER}} .ptm-related a' => '-webkit-line-clamp: {{VALUE}}' ] ]);
        $this->add_control('thumb_title_lines', [ 'label' => 'Thumbnail Title Lines', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'selectors' => [ '{{WRAPPER}} .ptm-thumb-title' => '-webkit-line-clamp: {{VALUE}}' ] ]);

        $this->add_control('heading_related', [ 'label' => 'Related Section', 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ]);
        $this->add_control('related_count', [ 'label' => 'Related Posts Count', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 2 ]);
        $this->add_control('related_header_text', [ 'label' => 'Related Header Text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'ΣΧΕΤΙΚΗ ΕΙΔΗΣΕΟΓΡΑΦΙΑ' ]);
        $this->add_control('autoplay_speed', [ 'label' => 'Slider Autoplay Speed (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 9000, 'separator' => 'before' ]);
        $this->end_controls_section();

        // --- SECTION: STYLE ---
        $this->start_controls_section('section_style', [ 'label' => 'Style & Typography', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]);
        $this->add_control('ptm_red', [ 'label' => 'Primary Color (Red)', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#d60000', 'selectors' => [ '{{WRAPPER}} .ptm-mainslider' => '--ptm-red: {{VALUE}}' ] ]);
        $this->add_control('ptm_orange', [ 'label' => 'Comments/Accent Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f47b23', 'selectors' => [ '{{WRAPPER}} .ptm-mainslider' => '--ptm-orange: {{VALUE}}' ] ]);
        $this->add_control('ptm_bg', [ 'label' => 'Card Background', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f3f6f9', 'selectors' => [ '{{WRAPPER}} .ptm-mainslider' => '--ptm-bg: {{VALUE}}' ] ]);
        $this->add_control('ptm_text', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#111111', 'selectors' => [ '{{WRAPPER}} .ptm-mainslider' => '--ptm-text: {{VALUE}}' ] ]);

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'title_typography', 'label' => 'Main Title', 'selector' => '{{WRAPPER}} .ptm-title' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'meta_typography', 'label' => 'Meta Data', 'selector' => '{{WRAPPER}} .ptm-meta, {{WRAPPER}} .ptm-meta a, {{WRAPPER}} .ptm-meta span' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'excerpt_typography', 'label' => 'Excerpt', 'selector' => '{{WRAPPER}} .ptm-excerpt' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'related_head_typography', 'label' => 'Related Header', 'selector' => '{{WRAPPER}} .ptm-related h4' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'related_link_typography', 'label' => 'Related Links', 'selector' => '{{WRAPPER}} .ptm-related a' ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'thumb_typography', 'label' => 'Thumbnails', 'selector' => '{{WRAPPER}} .ptm-thumb-title' ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // --- BUILD VIDEO PARAMS ---
        $vid_params = [ 'enablejsapi=1' ]; // Critical for JS control
        if('yes' === $settings['video_autoplay']) $vid_params[] = 'autoplay=1';
        if('yes' === $settings['video_mute']) $vid_params[] = 'mute=1&muted=1';
        // Loop is tricky for YT, handled inside loop logic below
        if('yes' !== $settings['video_controls']) $vid_params[] = 'controls=0';
        if('yes' === $settings['video_playsinline']) $vid_params[] = 'playsinline=1';
        if('yes' === $settings['video_modest']) { $vid_params[] = 'modestbranding=1'; $vid_params[] = 'rel=0'; }
        
        $param_str = !empty($vid_params) ? implode('&', $vid_params) : '';

        $limit = (int)$settings['posts_count'];
        $priority_days = (int)$settings['priority_days'];
        $final_posts = [];
        $exclude_ids = [];

        // PRIORITY
        if ( ! empty( $settings['priority_tag'] ) ) {
            $priority_args = [
                'post_type' => 'post', 'posts_per_page' => $limit, 'ignore_sticky_posts' => true,
                'tax_query' => [[ 'taxonomy' => 'post_tag', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $settings['priority_tag'] ) ) ]],
                'date_query' => [[ 'after' => $priority_days . ' days ago', 'column' => 'post_date' ]],
                'orderby' => 'date', 'order' => 'DESC'
            ];
            $priority_posts = get_posts( $priority_args );
            foreach ( $priority_posts as $p ) { $final_posts[] = $p; $exclude_ids[] = $p->ID; }
        }

        // FILLER
        $remaining = $limit - count( $final_posts );
        if ( $remaining > 0 ) {
            $filler_tax_query = [];
            if ( ! empty( $settings['cat_ids'] ) ) {
                $filler_tax_query[] = [ 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $settings['cat_ids'] ];
            }
            $filler_args = [
                'post_type' => 'post', 'posts_per_page' => $remaining, 'post__not_in' => $exclude_ids,
                'ignore_sticky_posts' => true, 'tax_query' => $filler_tax_query ?: null,
                'orderby' => 'date', 'order' => 'DESC'
            ];
            $filler_posts = get_posts( $filler_args );
            $final_posts = array_merge( $final_posts, $filler_posts );
        }

        if ( empty( $final_posts ) ) { echo '<div class="elementor-alert elementor-alert-warning">No posts found.</div>'; return; }

        $uid = 'ptm-' . $this->get_id(); 
        ?>
        
        <div id="<?php echo esc_attr($uid); ?>" class="ptm-mainslider" style="--ptm-delay: <?php echo (int)$settings['autoplay_speed']; ?>ms;">
            <div class="swiper ptm-main">
                <div class="swiper-wrapper">
                    <?php 
                    global $post;
                    foreach ( $final_posts as $post ) : setup_postdata( $post );
                        $pid = get_the_ID();
                        $link = get_permalink($pid);
                        $tit = get_the_title($pid);
                        $exc = wp_strip_all_tags(get_the_excerpt($pid));
                        $img = get_the_post_thumbnail_url($pid, 'full') ?: '';
                        $date = get_the_date('d.m.Y', $pid);
                        $time = get_the_date('H:i', $pid);
                        $com_count = get_comments_number($pid);
                        $views = function_exists('pvc_get_post_views') ? pvc_get_post_views($pid) : (int)get_post_meta($pid, $settings['views_meta_key'], true);

                        // --- VIDEO LOGIC ---
                        $video_html = '';
                        $is_video = false;
                        if ( 'yes' === $settings['enable_video'] ) {
                            $video_url = get_post_meta($pid, $settings['video_meta_key'], true);
                            if ( ! empty($video_url) ) {
                                $is_video = true;
                                // 1. Raw MP4
                                if(strpos($video_url, '.mp4') !== false) {
                                    $attrs = 'style="width:100%;height:100%;object-fit:cover;"';
                                    if('yes'===$settings['video_autoplay']) $attrs .= ' autoplay';
                                    if('yes'===$settings['video_mute']) $attrs .= ' muted';
                                    if('yes'===$settings['video_loop']) $attrs .= ' loop';
                                    if('yes'===$settings['video_controls']) $attrs .= ' controls';
                                    if('yes'===$settings['video_playsinline']) $attrs .= ' playsinline';
                                    $video_html = '<video class="ptm-raw-video" src="'.esc_url($video_url).'" '.$attrs.'></video>';
                                } else {
                                    // 2. oEmbed (YouTube/Vimeo)
                                    $oembed = wp_oembed_get($video_url, ['width' => 800, 'height' => 450]);
                                    if($oembed) {
                                        // Extract ID for YouTube Loop fix
                                        $yt_id = '';
                                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $video_url, $matches)) {
                                            $yt_id = $matches[1];
                                        }

                                        // Inject Params
                                        if ( preg_match('/src="([^"]+)"/', $oembed, $match) ) {
                                            $src = $match[1];
                                            $sep = (strpos($src, '?') === false) ? '?' : '&';
                                            
                                            // Specific YouTube Loop Fix: Must include playlist=ID
                                            $extra_loop = '';
                                            if('yes' === $settings['video_loop'] && $yt_id) {
                                                $extra_loop = '&loop=1&playlist=' . $yt_id;
                                            }

                                            $new_src = $src . $sep . $param_str . $extra_loop;
                                            $video_html = str_replace($src, $new_src, $oembed);
                                            
                                            // Add class for JS targeting
                                            $video_html = str_replace('<iframe', '<iframe class="ptm-embed-video"', $video_html);
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                    <div class="swiper-slide <?php echo $is_video ? 'ptm-has-video' : ''; ?>">
                        <div class="ptm-slide">
                            <?php if ( ! empty($video_html) ) : ?>
                                <div class="ptm-media ptm-video-wrapper swiper-no-swiping">
                                    <?php echo $video_html; ?>
                                </div>
                            <?php else : ?>
                                <a href="<?php echo esc_url($link); ?>" class="ptm-media">
                                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($tit); ?>" loading="lazy">
                                    <div class="ptm-main-progress"><i></i></div>
                                </a>
                            <?php endif; ?>

                            <div class="ptm-panel">
                                <div class="ptm-top">
                                    <div class="ptm-meta">
                                        <div class="ptm-meta-left">
                                            <?php if($settings['show_date']): ?> <span class="ptm-date"><?php echo $date; ?></span> <?php endif; ?>
                                            <?php if($settings['show_date'] && $settings['show_time']) echo ', '; ?>
                                            <?php if($settings['show_time']): ?> <span class="ptm-time"><?php echo $time; ?></span> <?php endif; ?>
                                        </div>
                                        <div class="ptm-meta-right">
                                            <?php if($settings['show_views']): ?> <span class="ptm-views"><i class="eicon-preview-medium"></i> <?php echo $views; ?></span> <?php endif; ?>
                                            <?php if($settings['show_comments']): ?> <a href="<?php echo esc_url($link); ?>#comments" class="ptm-comments"><i class="tsi tsi-comment-o"></i> <?php echo $com_count; ?></a> <?php endif; ?>
                                        </div>
                                    </div>

                                    <h3 class="ptm-title"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($tit); ?></a></h3>
                                    <div class="ptm-excerpt"><?php echo $exc; ?></div>
                                </div>

                                <?php 
                                $rels = [];
                                if ((int)$settings['related_count'] > 0) {
                                    $cats = wp_get_post_terms($pid, 'category', ['fields' => 'ids']);
                                    if ($cats && !is_wp_error($cats)) {
                                        $rq = new WP_Query([
                                            'post_type' => 'post', 'posts_per_page' => (int)$settings['related_count'],
                                            'post__not_in' => [$pid], 'ignore_sticky_posts' => 1,
                                            'tax_query' => [['taxonomy'=>'category', 'field'=>'term_id', 'terms'=>$cats]]
                                        ]);
                                        while($rq->have_posts()){ $rq->the_post();
                                            $rels[] = [ 't'=> get_the_title(), 'l'=>get_permalink(), 'd'=>get_the_date('d.m.Y, H:i') ];
                                        }
                                        wp_reset_postdata();
                                    }
                                }
                                if($rels): ?>
                                <div class="ptm-related">
                                    <h4><?php echo esc_html($settings['related_header_text']); ?></h4>
                                    <ul>
                                        <?php foreach($rels as $r): ?>
                                        <li><a href="<?php echo esc_url($r['l']); ?>"><?php echo esc_html($r['t']); ?></a><time><?php echo $r['d']; ?></time></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; wp_reset_postdata(); ?>
                </div>
            </div>

            <div class="ptm-thumbs-wrap">
                <div class="swiper ptm-thumbs">
                    <div class="swiper-wrapper">
                        <?php 
                        foreach ($final_posts as $post) : setup_postdata($post);
                            $img = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        ?>
                        <div class="swiper-slide">
                            <div class="ptm-thumb">
                                <img src="<?php echo esc_url($img); ?>" loading="lazy">
                                <div class="ptm-thumb-title"><?php the_title(); ?></div>
                            </div>
                        </div>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function($){
            var initSawahSlider = function() {
                var root = document.getElementById('<?php echo $uid; ?>');
                if(!root || root.classList.contains('ptm-init-done')) return;
                if(typeof Swiper === 'undefined') { setTimeout(initSawahSlider, 100); return; }

                try {
                    var thumbs = new Swiper(root.querySelector('.ptm-thumbs'), {
                        slidesPerView: 5, spaceBetween: 5, watchSlidesProgress: true,
                        breakpoints: { 0:{slidesPerView:2.5}, 600:{slidesPerView:3.5}, 1000:{slidesPerView:5} }
                    });

                    var main = new Swiper(root.querySelector('.ptm-main'), {
                        effect: 'fade', fadeEffect: {crossFade:true},
                        loop: true, speed: 600, autoHeight: true,
                        autoplay: { delay: <?php echo (int)$settings['autoplay_speed']; ?>, disableOnInteraction: false },
                        thumbs: { swiper: thumbs },
                        on: {
                            init: function() { root.classList.add('ptm-loaded'); },
                            slideChangeTransitionStart: function() {
                                // 1. Reset Progress Bars
                                var bars = root.querySelectorAll('.ptm-main-progress i');
                                bars.forEach(b => { b.style.animation = 'none'; b.offsetHeight; b.style.animation = ''; });

                                // 2. VIDEO LOGIC: Stop ALL, Play ACTIVE
                                var slides = root.querySelectorAll('.swiper-slide');
                                slides.forEach(function(slide){
                                    // Stop HTML5
                                    var raw = slide.querySelector('video');
                                    if(raw) raw.pause();
                                    
                                    // Stop YouTube/Vimeo (via postMessage)
                                    var frame = slide.querySelector('iframe');
                                    if(frame) {
                                        frame.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); // YT
                                        frame.contentWindow.postMessage('{"method":"pause"}', '*'); // Vimeo
                                    }
                                });

                                // Play Active
                                var active = root.querySelector('.swiper-slide-active');
                                if(active) {
                                    var activeRaw = active.querySelector('video');
                                    if(activeRaw) { 
                                        activeRaw.currentTime = 0; 
                                        activeRaw.play(); 
                                    }

                                    var activeFrame = active.querySelector('iframe');
                                    if(activeFrame) {
                                        // Reset YT to 0 and Play
                                        activeFrame.contentWindow.postMessage('{"event":"command","func":"seekTo","args":[0, true]}', '*');
                                        activeFrame.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                                        // Vimeo
                                        activeFrame.contentWindow.postMessage('{"method":"setCurrentTime", "value":0}', '*');
                                        activeFrame.contentWindow.postMessage('{"method":"play"}', '*');
                                    }
                                }
                            }
                        }
                    });
                    root.classList.add('ptm-init-done');
                } catch(e) { root.style.opacity = 1; }
            };
            $(window).on('load', initSawahSlider);
            $(document).ready(initSawahSlider);
            if ( window.elementorFrontend ) {
                elementorFrontend.hooks.addAction( 'frontend/element_ready/sawah_slider.default', initSawahSlider );
            }
        })(jQuery);
        </script>
        <?php
    }
}

// We add the CSS here only once to prevent it loading multiple times if multiple widgets are used
add_action('wp_head', function () {
    if ( did_action('sawah_slider_css_loaded') ) return;
    do_action('sawah_slider_css_loaded');
    ?>
    <style>
    /* CORE */
    .ptm-mainslider { 
        --ptm-red: #d60000; --ptm-orange: #f47b23; --ptm-bg: #f3f6f9; 
        --ptm-text: #111; --ptm-meta: #666; --ptm-card-inset: 24px; 
        max-width: 100%; margin: 0 auto; position: relative; font-family: inherit;
        opacity: 0; transition: opacity 0.4s ease;
    }
    .ptm-mainslider { animation: ptmFadeInFallback 0.5s ease 2s forwards; }
    @keyframes ptmFadeInFallback { to { opacity: 1; } }
    .ptm-mainslider.ptm-loaded { opacity: 1; animation: none; }
    .ptm-mainslider a { text-decoration: none; color: inherit; transition: color 0.2s; }

    /* LAYOUT */
    .ptm-slide { position: relative; width: 100%; height: 600px; overflow: hidden; background: none; }
    .ptm-media { position: absolute; top: 0; left: 0; bottom: 0; width: 80%; height: 100%; z-index: 1; }
    .ptm-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ptm-video-wrapper iframe, .ptm-video-wrapper video { position: absolute; top:0; left:0; width: 100%; height: 100%; object-fit: cover; border: none; }

    /* PROGRESS */
    .ptm-main-progress { position: absolute; left: 0; bottom: 0; width: 100%; height: 4px; background: rgba(255,255,255,0.2); z-index: 5; pointer-events: none; }
    .ptm-main-progress i { display: block; height: 100%; width: 100%; background: var(--ptm-red); transform: translateX(-100%); }
    .swiper-slide-active .ptm-main-progress i { animation: ptmMainProgress var(--ptm-delay) linear forwards; }
    @keyframes ptmMainProgress { to { transform: translateX(0); } }

    /* CARD */
    .ptm-panel { 
        position: absolute; z-index: 10; top: var(--ptm-card-inset); bottom: var(--ptm-card-inset); right: 0;      
        width: 40%; max-width: 500px; min-width: 340px;
        background: var(--ptm-bg); color: var(--ptm-text);
        padding: 30px; display: flex; flex-direction: column; justify-content: space-between; 
        box-shadow: -10px 0 30px rgba(0,0,0,0.15);
    }

    /* META */
    .ptm-meta { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--ptm-meta); margin-bottom: 12px; font-weight: 600; flex-shrink: 0; }
    .ptm-meta-left, .ptm-meta-right { display: flex; align-items: center; gap: 8px; }
    .ptm-comments, .ptm-views { display: inline-flex; align-items: center; gap: 4px; }
    .ptm-comments { color: var(--ptm-orange); }
    .ptm-views i, .ptm-comments i { font-size: 14px; }

    /* TEXT CLAMPING */
    .ptm-title, .ptm-excerpt, .ptm-related a, .ptm-thumb-title { 
        display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; 
    }
    .ptm-title { margin: 0 0 12px; letter-spacing: -0.5px; line-height: 1.15; font-size: 32px; font-weight: 900; -webkit-line-clamp: 3; }
    .ptm-title a:hover { color: var(--ptm-red); }
    .ptm-excerpt { line-height: 1.5; color: inherit; margin: 0 0 15px; font-size: 16px; -webkit-line-clamp: 2; }

    /* RELATED */
    .ptm-related { border-top: 1px solid #dcdcdc; padding-top: 15px; margin-top: auto; }
    .ptm-related h4 { margin: 0 0 12px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: #000; }
    .ptm-related ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
    .ptm-related a { 
        font-weight: 700; font-size: 15px; line-height: 1.3; 
        display: -webkit-box; -webkit-line-clamp: 2;
    }
    .ptm-related a:hover { color: var(--ptm-red); }
    .ptm-related time { display: block; font-size: 12px; color: #888; margin-top: 2px; }

    /* THUMBS */
    .ptm-thumbs-wrap { margin-top: 6px; }
    .ptm-thumb { position: relative; height: 150px; background: #000; cursor: pointer; overflow: hidden; }
    .ptm-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
    .ptm-thumb::after { content: ""; position: absolute; inset: 0; background-color: var(--ptm-bg); opacity: 0.55; transition: opacity 0.3s ease; z-index: 2; pointer-events: none; }
    .swiper-slide-thumb-active .ptm-thumb::after { opacity: 0; }
    .ptm-thumb-title { position: absolute; left: 8px; bottom: 8px; right: 8px; font-size: 16px; font-weight: 700; color: #fff; line-height: 1.2; text-shadow: 0 1px 3px rgba(0,0,0,0.8); z-index: 4; -webkit-line-clamp: 3; }
    .ptm-thumb:before { content:""; position:absolute; inset:0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.9) 100%); z-index: 3; }

    @media (max-width: 1024px) {
        .ptm-panel { width: 50%; padding: 20px; }
        .ptm-slide { height: 500px; }
    }
    @media (max-width: 768px) {
        .ptm-slide { height: auto; display: flex; flex-direction: column; }
        .ptm-media { position: relative; height: 260px; width: 100%; }
        .ptm-panel { position: relative; top: 0; bottom: 0; right: 0; width: 100%; max-width: none; box-shadow: none; border-bottom: 1px solid #eee; }
        .ptm-main-progress { bottom: auto; top: 0; }
        .ptm-thumb::after { opacity: 0.4; }
        .swiper-slide-thumb-active .ptm-thumb::after { opacity: 0; }
    }
    </style>
    <?php
});