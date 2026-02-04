<?php
/**
 * Metabox functionality for FAQ and HowTo
 */

class Easy_FAQ_HowTo_Metabox {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Meta keys
     */
    const FAQ_META_KEY = '_easy_faq_data';
    const HOWTO_META_KEY = '_easy_howto_data';
    const FAQ_USE_ELEMENTOR_KEY = '_easy_faq_use_elementor';

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
     * Initialize the metabox
     */
    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'easy-faq-howto-admin',
            EASY_FAQ_HOWTO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EASY_FAQ_HOWTO_VERSION
        );

        wp_enqueue_script(
            'easy-faq-howto-admin',
            EASY_FAQ_HOWTO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            EASY_FAQ_HOWTO_VERSION,
            true
        );

        wp_localize_script(
            'easy-faq-howto-admin',
            'easyFaqHowtoAdmin',
            array(
                'confirmDelete' => __( 'Are you sure you want to delete this item?', 'easy-faq-howto-schema' ),
            )
        );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        $post_types = apply_filters( 'easy_faq_howto_post_types', array( 'post', 'page' ) );

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'easy-faq-schema',
                __( 'FAQ Schema', 'easy-faq-howto-schema' ),
                array( $this, 'render_faq_metabox' ),
                $post_type,
                'normal',
                'default'
            );

            add_meta_box(
                'easy-howto-schema',
                __( 'HowTo Schema', 'easy-faq-howto-schema' ),
                array( $this, 'render_howto_metabox' ),
                $post_type,
                'normal',
                'default'
            );
        }
    }

    /**
     * Render FAQ metabox
     */
    public function render_faq_metabox( $post ) {
        wp_nonce_field( 'easy_faq_meta_box', 'easy_faq_meta_box_nonce' );

        $faq_data = get_post_meta( $post->ID, self::FAQ_META_KEY, true );
        if ( ! is_array( $faq_data ) ) {
            $faq_data = array();
        }
        
        $use_elementor = get_post_meta( $post->ID, self::FAQ_USE_ELEMENTOR_KEY, true );
        $is_elementor = $this->is_elementor_page( $post->ID );
        ?>
        <div class="easy-faq-howto-metabox">
            <div class="metabox-description">
                <p><?php _e( 'Add your FAQ questions and answers below. To display them in your post, use the shortcode:', 'easy-faq-howto-schema' ); ?></p>
                <p><code>[easy_faq]</code></p>
                <p><?php _e( 'This will add the FAQ content to your post and inject the appropriate schema.org structured data via Yoast SEO.', 'easy-faq-howto-schema' ); ?></p>
            </div>

            <?php if ( $is_elementor ) : ?>
            <div class="elementor-integration" style="background: #e8f4f8; padding: 15px; margin-bottom: 20px; border-left: 4px solid #00a0d2; border-radius: 4px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="easy_faq_use_elementor" value="1" <?php checked( $use_elementor, '1' ); ?> style="margin-right: 10px;" />
                    <span>
                        <strong><?php _e( 'Use Elementor Accordion for FAQ Schema', 'easy-faq-howto-schema' ); ?></strong><br>
                        <small style="color: #666;"><?php _e( 'Automatically extract FAQ data from Elementor Accordion widgets on this page. The accordion items will be used for schema.org structured data.', 'easy-faq-howto-schema' ); ?></small>
                    </span>
                </label>
            </div>
            <?php endif; ?>

            <div class="faq-items-container" data-type="faq">
                <?php
                if ( ! empty( $faq_data ) ) {
                    foreach ( $faq_data as $index => $item ) {
                        $this->render_faq_item( $index, $item );
                    }
                }
                ?>
            </div>

            <button type="button" class="button add-faq-item">
                <?php _e( 'Add FAQ Item', 'easy-faq-howto-schema' ); ?>
            </button>

            <!-- Template for new FAQ items -->
            <script type="text/template" id="faq-item-template">
                <?php $this->render_faq_item( '{{INDEX}}', array( 'question' => '', 'answer' => '' ) ); ?>
            </script>
        </div>
        <?php
    }

    /**
     * Render a single FAQ item
     */
    private function render_faq_item( $index, $item ) {
        $question = isset( $item['question'] ) ? esc_attr( $item['question'] ) : '';
        $answer = isset( $item['answer'] ) ? esc_textarea( $item['answer'] ) : '';
        ?>
        <div class="faq-item" data-index="<?php echo $index; ?>">
            <div class="faq-item-header">
                <span class="faq-item-number"><?php echo esc_html( is_numeric( $index ) ? $index + 1 : '' ); ?></span>
                <button type="button" class="button-link remove-item" title="<?php esc_attr_e( 'Remove', 'easy-faq-howto-schema' ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="faq-item-content">
                <label>
                    <strong><?php _e( 'Question:', 'easy-faq-howto-schema' ); ?></strong>
                    <input type="text" name="easy_faq[<?php echo $index; ?>][question]" value="<?php echo $question; ?>" class="widefat" />
                </label>
                <label>
                    <strong><?php _e( 'Answer:', 'easy-faq-howto-schema' ); ?></strong>
                    <textarea name="easy_faq[<?php echo $index; ?>][answer]" rows="4" class="widefat"><?php echo $answer; ?></textarea>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Render HowTo metabox
     */
    public function render_howto_metabox( $post ) {
        wp_nonce_field( 'easy_howto_meta_box', 'easy_howto_meta_box_nonce' );

        $howto_data = get_post_meta( $post->ID, self::HOWTO_META_KEY, true );
        if ( ! is_array( $howto_data ) ) {
            $howto_data = array(
                'name' => '',
                'description' => '',
                'total_time' => '',
                'steps' => array()
            );
        }
        ?>
        <div class="easy-faq-howto-metabox">
            <div class="metabox-description">
                <p><?php _e( 'Add your HowTo guide below. To display it in your post, use the shortcode:', 'easy-faq-howto-schema' ); ?></p>
                <p><code>[easy_howto]</code></p>
                <p><?php _e( 'This will add the HowTo content to your post and inject the appropriate schema.org structured data via Yoast SEO.', 'easy-faq-howto-schema' ); ?></p>
            </div>

            <div class="howto-metadata">
                <label>
                    <strong><?php _e( 'HowTo Title:', 'easy-faq-howto-schema' ); ?></strong>
                    <input type="text" name="easy_howto[name]" value="<?php echo esc_attr( $howto_data['name'] ); ?>" class="widefat" />
                </label>
                <label>
                    <strong><?php _e( 'Description:', 'easy-faq-howto-schema' ); ?></strong>
                    <textarea name="easy_howto[description]" rows="3" class="widefat"><?php echo esc_textarea( $howto_data['description'] ); ?></textarea>
                </label>
                <label>
                    <strong><?php _e( 'Total Time (e.g., PT30M for 30 minutes, PT1H for 1 hour):', 'easy-faq-howto-schema' ); ?></strong>
                    <input type="text" name="easy_howto[total_time]" value="<?php echo esc_attr( $howto_data['total_time'] ); ?>" class="regular-text" placeholder="PT30M" />
                    <span class="description"><?php _e( 'ISO 8601 duration format', 'easy-faq-howto-schema' ); ?></span>
                </label>
            </div>

            <h4><?php _e( 'Steps:', 'easy-faq-howto-schema' ); ?></h4>

            <div class="howto-steps-container" data-type="howto">
                <?php
                if ( ! empty( $howto_data['steps'] ) ) {
                    foreach ( $howto_data['steps'] as $index => $step ) {
                        $this->render_howto_step( $index, $step );
                    }
                }
                ?>
            </div>

            <button type="button" class="button add-howto-step">
                <?php _e( 'Add Step', 'easy-faq-howto-schema' ); ?>
            </button>

            <!-- Template for new HowTo steps -->
            <script type="text/template" id="howto-step-template">
                <?php $this->render_howto_step( '{{INDEX}}', array( 'name' => '', 'text' => '' ) ); ?>
            </script>
        </div>
        <?php
    }

    /**
     * Render a single HowTo step
     */
    private function render_howto_step( $index, $step ) {
        $name = isset( $step['name'] ) ? esc_attr( $step['name'] ) : '';
        $text = isset( $step['text'] ) ? esc_textarea( $step['text'] ) : '';
        ?>
        <div class="howto-step" data-index="<?php echo $index; ?>">
            <div class="howto-step-header">
                <span class="howto-step-number"><?php echo esc_html( is_numeric( $index ) ? $index + 1 : '' ); ?></span>
                <button type="button" class="button-link remove-item" title="<?php esc_attr_e( 'Remove', 'easy-faq-howto-schema' ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="howto-step-content">
                <label>
                    <strong><?php _e( 'Step Name:', 'easy-faq-howto-schema' ); ?></strong>
                    <input type="text" name="easy_howto[steps][<?php echo $index; ?>][name]" value="<?php echo $name; ?>" class="widefat" />
                </label>
                <label>
                    <strong><?php _e( 'Step Instructions:', 'easy-faq-howto-schema' ); ?></strong>
                    <textarea name="easy_howto[steps][<?php echo $index; ?>][text]" rows="4" class="widefat"><?php echo $text; ?></textarea>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes( $post_id, $post ) {
        // Save FAQ data
        if ( isset( $_POST['easy_faq_meta_box_nonce'] ) && wp_verify_nonce( $_POST['easy_faq_meta_box_nonce'], 'easy_faq_meta_box' ) ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            $faq_data = array();
            if ( isset( $_POST['easy_faq'] ) && is_array( $_POST['easy_faq'] ) ) {
                foreach ( $_POST['easy_faq'] as $item ) {
                    if ( ! empty( $item['question'] ) || ! empty( $item['answer'] ) ) {
                        $faq_data[] = array(
                            'question' => sanitize_text_field( $item['question'] ),
                            'answer' => wp_kses_post( $item['answer'] )
                        );
                    }
                }
            }

            if ( ! empty( $faq_data ) ) {
                update_post_meta( $post_id, self::FAQ_META_KEY, $faq_data );
            } else {
                delete_post_meta( $post_id, self::FAQ_META_KEY );
            }

            // Save Elementor integration flag
            if ( isset( $_POST['easy_faq_use_elementor'] ) ) {
                update_post_meta( $post_id, self::FAQ_USE_ELEMENTOR_KEY, '1' );
            } else {
                delete_post_meta( $post_id, self::FAQ_USE_ELEMENTOR_KEY );
            }
        }

        // Save HowTo data
        if ( isset( $_POST['easy_howto_meta_box_nonce'] ) && wp_verify_nonce( $_POST['easy_howto_meta_box_nonce'], 'easy_howto_meta_box' ) ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            $howto_data = array(
                'name' => '',
                'description' => '',
                'total_time' => '',
                'steps' => array()
            );

            if ( isset( $_POST['easy_howto'] ) && is_array( $_POST['easy_howto'] ) ) {
                $howto_data['name'] = sanitize_text_field( $_POST['easy_howto']['name'] );
                $howto_data['description'] = sanitize_textarea_field( $_POST['easy_howto']['description'] );
                $howto_data['total_time'] = sanitize_text_field( $_POST['easy_howto']['total_time'] );

                if ( isset( $_POST['easy_howto']['steps'] ) && is_array( $_POST['easy_howto']['steps'] ) ) {
                    foreach ( $_POST['easy_howto']['steps'] as $step ) {
                        if ( ! empty( $step['name'] ) || ! empty( $step['text'] ) ) {
                            $howto_data['steps'][] = array(
                                'name' => sanitize_text_field( $step['name'] ),
                                'text' => wp_kses_post( $step['text'] )
                            );
                        }
                    }
                }
            }

            if ( ! empty( $howto_data['name'] ) || ! empty( $howto_data['steps'] ) ) {
                update_post_meta( $post_id, self::HOWTO_META_KEY, $howto_data );
            } else {
                delete_post_meta( $post_id, self::HOWTO_META_KEY );
            }
        }
    }

    /**
     * Check if post is built with Elementor
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    private function is_elementor_page( $post_id ) {
        if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
            return false;
        }

        $document = get_post_meta( $post_id, '_elementor_edit_mode', true );
        return ! empty( $document );
    }

    /**
     * Extract FAQ data from Elementor accordion widgets
     *
     * @param int $post_id Post ID.
     * @return array FAQ data extracted from Elementor.
     */
    public static function get_elementor_accordion_data( $post_id ) {
        $elementor_data = get_post_meta( $post_id, '_elementor_data', true );
        if ( empty( $elementor_data ) ) {
            return array();
        }

        // Elementor data might be a string or already an array
        if ( is_string( $elementor_data ) ) {
            $elements = json_decode( $elementor_data, true );
        } else {
            $elements = $elementor_data;
        }

        if ( ! is_array( $elements ) ) {
            return array();
        }

        $faq_data = array();
        self::parse_elementor_elements( $elements, $faq_data );

        return $faq_data;
    }

    /**
     * Recursively parse Elementor elements to find accordion widgets
     *
     * @param array $elements Elementor elements.
     * @param array &$faq_data FAQ data array to populate.
     */
    private static function parse_elementor_elements( $elements, &$faq_data ) {
        if ( ! is_array( $elements ) ) {
            return;
        }

        foreach ( $elements as $element ) {
            if ( ! is_array( $element ) ) {
                continue;
            }

            // Check for nested-accordion widget (Elementor Pro)
            if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'nested-accordion' ) {
                if ( isset( $element['settings']['items'] ) && is_array( $element['settings']['items'] ) ) {
                    $titles = $element['settings']['items'];
                    $contents = array();
                    
                    // Extract content from child elements
                    if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                        foreach ( $element['elements'] as $child ) {
                            if ( isset( $child['elements'] ) && is_array( $child['elements'] ) ) {
                                foreach ( $child['elements'] as $inner ) {
                                    if ( isset( $inner['widgetType'] ) && $inner['widgetType'] === 'text-editor' ) {
                                        if ( isset( $inner['settings']['editor'] ) ) {
                                            $contents[] = $inner['settings']['editor'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Match titles with contents
                    foreach ( $titles as $index => $title_data ) {
                        if ( ! empty( $title_data['item_title'] ) ) {
                            $faq_data[] = array(
                                'question' => wp_strip_all_tags( $title_data['item_title'] ),
                                'answer' => isset( $contents[ $index ] ) ? wp_kses_post( $contents[ $index ] ) : ''
                            );
                        }
                    }
                }
            }

            // Check for classic accordion widget
            if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'accordion' ) {
                if ( isset( $element['settings']['tabs'] ) && is_array( $element['settings']['tabs'] ) ) {
                    foreach ( $element['settings']['tabs'] as $tab ) {
                        if ( ! empty( $tab['tab_title'] ) ) {
                            $faq_data[] = array(
                                'question' => wp_strip_all_tags( $tab['tab_title'] ),
                                'answer' => isset( $tab['tab_content'] ) ? wp_kses_post( $tab['tab_content'] ) : ''
                            );
                        }
                    }
                }
            }

            // Check for toggle widget (similar to accordion)
            if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'toggle' ) {
                if ( isset( $element['settings']['tabs'] ) && is_array( $element['settings']['tabs'] ) ) {
                    foreach ( $element['settings']['tabs'] as $tab ) {
                        if ( ! empty( $tab['tab_title'] ) ) {
                            $faq_data[] = array(
                                'question' => wp_strip_all_tags( $tab['tab_title'] ),
                                'answer' => isset( $tab['tab_content'] ) ? wp_kses_post( $tab['tab_content'] ) : ''
                            );
                        }
                    }
                }
            }

            // Recursively check child elements
            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                self::parse_elementor_elements( $element['elements'], $faq_data );
            }
        }
    }
}

