<?php
/**
 * Shortcode functionality for displaying FAQ and HowTo
 */

class Easy_FAQ_HowTo_Shortcodes {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize shortcodes
     */
    private function __construct() {
        add_shortcode( 'easy_faq', array( $this, 'faq_shortcode' ) );
        add_shortcode( 'easy_howto', array( $this, 'howto_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_assets() {
        if ( has_shortcode( get_post()->post_content, 'easy_faq' ) || has_shortcode( get_post()->post_content, 'easy_howto' ) ) {
            wp_enqueue_style(
                'easy-faq-howto-frontend',
                EASY_FAQ_HOWTO_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                EASY_FAQ_HOWTO_VERSION
            );
        }
    }

    /**
     * FAQ shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function faq_shortcode( $atts ) {
        global $post;

        if ( ! $post ) {
            return '';
        }

        // Check if using Elementor accordion
        $use_elementor = get_post_meta( $post->ID, Easy_FAQ_HowTo_Metabox::FAQ_USE_ELEMENTOR_KEY, true );
        if ( $use_elementor ) {
            // Don't display via shortcode if using Elementor accordion (it's already visible on page)
            return '<!-- FAQ Schema: Using Elementor Accordion -->';
        }

        $faq_data = get_post_meta( $post->ID, Easy_FAQ_HowTo_Metabox::FAQ_META_KEY, true );

        if ( empty( $faq_data ) || ! is_array( $faq_data ) ) {
            return '';
        }

        ob_start();
        ?>
        <div class="easy-faq-container">
            <?php foreach ( $faq_data as $index => $item ) : ?>
                <?php if ( ! empty( $item['question'] ) ) : ?>
                    <div class="easy-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                        <h3 class="easy-faq-question" itemprop="name"><?php echo esc_html( $item['question'] ); ?></h3>
                        <?php if ( ! empty( $item['answer'] ) ) : ?>
                            <div class="easy-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                <div itemprop="text"><?php echo wpautop( wp_kses_post( $item['answer'] ) ); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * HowTo shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function howto_shortcode( $atts ) {
        global $post;

        if ( ! $post ) {
            return '';
        }

        $howto_data = get_post_meta( $post->ID, Easy_FAQ_HowTo_Metabox::HOWTO_META_KEY, true );

        if ( empty( $howto_data ) || ! is_array( $howto_data ) ) {
            return '';
        }

        ob_start();
        ?>
        <div class="easy-howto-container" itemscope itemtype="https://schema.org/HowTo">
            <?php if ( ! empty( $howto_data['name'] ) ) : ?>
                <h2 class="easy-howto-title" itemprop="name"><?php echo esc_html( $howto_data['name'] ); ?></h2>
            <?php endif; ?>

            <?php if ( ! empty( $howto_data['description'] ) ) : ?>
                <div class="easy-howto-description" itemprop="description">
                    <?php echo wpautop( esc_html( $howto_data['description'] ) ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $howto_data['total_time'] ) ) : ?>
                <meta itemprop="totalTime" content="<?php echo esc_attr( $howto_data['total_time'] ); ?>">
                <p class="easy-howto-time">
                    <strong><?php _e( 'Total Time:', 'easy-faq-howto-schema' ); ?></strong>
                    <?php echo esc_html( $this->format_duration( $howto_data['total_time'] ) ); ?>
                </p>
            <?php endif; ?>

            <?php if ( ! empty( $howto_data['steps'] ) ) : ?>
                <ol class="easy-howto-steps">
                    <?php foreach ( $howto_data['steps'] as $index => $step ) : ?>
                        <?php if ( ! empty( $step['name'] ) || ! empty( $step['text'] ) ) : ?>
                            <li class="easy-howto-step" itemprop="step" itemscope itemtype="https://schema.org/HowToStep">
                                <?php if ( ! empty( $step['name'] ) ) : ?>
                                    <h4 class="easy-howto-step-name" itemprop="name"><?php echo esc_html( $step['name'] ); ?></h4>
                                <?php endif; ?>
                                <?php if ( ! empty( $step['text'] ) ) : ?>
                                    <div class="easy-howto-step-text" itemprop="text">
                                        <?php echo wpautop( wp_kses_post( $step['text'] ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Format ISO 8601 duration to human readable format
     *
     * @param string $duration ISO 8601 duration string
     * @return string Human readable duration
     */
    private function format_duration( $duration ) {
        if ( empty( $duration ) ) {
            return '';
        }

        // Simple parser for common durations
        $duration = strtoupper( $duration );
        $output = array();

        // Extract hours
        if ( preg_match( '/(\d+)H/', $duration, $matches ) ) {
            $hours = intval( $matches[1] );
            $output[] = sprintf( _n( '%d hour', '%d hours', $hours, 'easy-faq-howto-schema' ), $hours );
        }

        // Extract minutes
        if ( preg_match( '/(\d+)M/', $duration, $matches ) ) {
            $minutes = intval( $matches[1] );
            $output[] = sprintf( _n( '%d minute', '%d minutes', $minutes, 'easy-faq-howto-schema' ), $minutes );
        }

        // Extract seconds
        if ( preg_match( '/(\d+)S/', $duration, $matches ) ) {
            $seconds = intval( $matches[1] );
            $output[] = sprintf( _n( '%d second', '%d seconds', $seconds, 'easy-faq-howto-schema' ), $seconds );
        }

        return ! empty( $output ) ? implode( ' ', $output ) : $duration;
    }
}
