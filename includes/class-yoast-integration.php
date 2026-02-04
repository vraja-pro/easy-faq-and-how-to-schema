<?php
/**
 * Yoast SEO integration for FAQ and HowTo schema
 */

class Easy_FAQ_HowTo_Yoast_Integration {

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
     * Initialize Yoast SEO integration
     */
    private function __construct() {
        // Hook into Yoast SEO schema graph
        add_filter( 'wpseo_schema_graph_pieces', array( $this, 'add_schema_pieces' ), 11, 2 );
    }

    /**
     * Add schema pieces to Yoast SEO graph
     *
     * @param array $pieces Graph pieces.
     * @param \WPSEO_Schema_Context $context Context object.
     * @return array
     */
    public function add_schema_pieces( $pieces, $context ) {
        $pieces[] = new Easy_FAQ_Schema_Piece( $context );
        $pieces[] = new Easy_HowTo_Schema_Piece( $context );

        return $pieces;
    }

    /**
     * Generate FAQ schema
     *
     * @param array $faq_data FAQ data.
     * @param \WP_Post $post Post object.
     * @return array|null
     */
    public function generate_faq_schema( $faq_data, $post ) {
        $main_entity = array();

        foreach ( $faq_data as $item ) {
            if ( empty( $item['question'] ) ) {
                continue;
            }

            $main_entity[] = array(
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => ! empty( $item['answer'] ) ? wp_strip_all_tags( $item['answer'] ) : ''
                )
            );
        }

        if ( empty( $main_entity ) ) {
            return null;
        }

        return array(
            '@type' => 'FAQPage',
            '@id' => get_permalink( $post->ID ) . '#faqpage',
            'mainEntity' => $main_entity
        );
    }

    /**
     * Generate HowTo schema
     *
     * @param array $howto_data HowTo data.
     * @param \WP_Post $post Post object.
     * @return array|null
     */
    public function generate_howto_schema( $howto_data, $post ) {
        $steps = array();

        foreach ( $howto_data['steps'] as $index => $step ) {
            if ( empty( $step['name'] ) && empty( $step['text'] ) ) {
                continue;
            }

            $step_data = array(
                '@type' => 'HowToStep',
                'position' => $index + 1
            );

            if ( ! empty( $step['name'] ) ) {
                $step_data['name'] = $step['name'];
            }

            if ( ! empty( $step['text'] ) ) {
                $step_data['text'] = wp_strip_all_tags( $step['text'] );
            }

            $steps[] = $step_data;
        }

        if ( empty( $steps ) ) {
            return null;
        }

        $schema = array(
            '@type' => 'HowTo',
            '@id' => get_permalink( $post->ID ) . '#howto',
            'step' => $steps
        );

        if ( ! empty( $howto_data['name'] ) ) {
            $schema['name'] = $howto_data['name'];
        }

        if ( ! empty( $howto_data['description'] ) ) {
            $schema['description'] = $howto_data['description'];
        }

        if ( ! empty( $howto_data['total_time'] ) ) {
            $schema['totalTime'] = $howto_data['total_time'];
        }

        return $schema;
    }
}

/**
 * FAQ Schema Piece for Yoast SEO
 */
class Easy_FAQ_Schema_Piece {

    /**
     * Context object.
     *
     * @var WPSEO_Schema_Context
     */
    public $context;

    /**
     * Constructor.
     *
     * @param WPSEO_Schema_Context $context Context object.
     */
    public function __construct( $context ) {
        $this->context = $context;
    }

    /**
     * Determine if piece should be added to graph.
     *
     * @return bool
     */
    public function is_needed() {
        if ( ! is_singular() ) {
            return false;
        }

        $post_id = get_the_ID();
        
        // Check if using Elementor accordion
        $use_elementor = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::FAQ_USE_ELEMENTOR_KEY, true );
        if ( $use_elementor ) {
            $faq_data = Easy_FAQ_HowTo_Metabox::get_elementor_accordion_data( $post_id );
        } else {
            $faq_data = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::FAQ_META_KEY, true );
        }

        if ( empty( $faq_data ) || ! is_array( $faq_data ) ) {
            return false;
        }

        // Add FAQPage to the schema page type
        if ( ! is_array( $this->context->schema_page_type ) ) {
            $this->context->schema_page_type = array( $this->context->schema_page_type );
        }
        $this->context->schema_page_type[] = 'FAQPage';

        // Set main entity of page to the question IDs
        $this->context->main_entity_of_page = $this->generate_faq_ids( $faq_data );

        return true;
    }

    /**
     * Generate IDs for FAQ questions.
     *
     * @param array $faq_data FAQ data.
     * @return array Array of IDs.
     */
    private function generate_faq_ids( $faq_data ) {
        $ids = array();
        foreach ( $faq_data as $index => $item ) {
            if ( ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) {
                $ids[] = array( '@id' => $this->context->canonical . '#faq-question-' . $index );
            }
        }
        return $ids;
    }

    /**
     * Generate FAQ schema - returns array of Question pieces.
     *
     * @return array Array of Question schema pieces.
     */
    public function generate() {
        $post_id = get_the_ID();
        
        // Check if using Elementor accordion
        $use_elementor = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::FAQ_USE_ELEMENTOR_KEY, true );
        if ( $use_elementor ) {
            $faq_data = Easy_FAQ_HowTo_Metabox::get_elementor_accordion_data( $post_id );
        } else {
            $faq_data = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::FAQ_META_KEY, true );
        }

        if ( empty( $faq_data ) ) {
            return array();
        }

        $graph = array();
        $position = 1;

        foreach ( $faq_data as $index => $item ) {
            if ( empty( $item['question'] ) ) {
                continue;
            }

            $question_id = $this->context->canonical . '#faq-question-' . $index;

            $question_data = array(
                '@type'          => 'Question',
                '@id'            => $question_id,
                'position'       => $position,
                'url'            => $question_id,
                'name'           => wp_strip_all_tags( $item['question'] ),
                'answerCount'    => 1,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags( $item['answer'] )
                )
            );

            $graph[] = $question_data;
            $position++;
        }

        return $graph;
    }
}

/**
 * HowTo Schema Piece for Yoast SEO
 */
class Easy_HowTo_Schema_Piece {

    /**
     * Context object.
     *
     * @var WPSEO_Schema_Context
     */
    public $context;

    /**
     * Constructor.
     *
     * @param WPSEO_Schema_Context $context Context object.
     */
    public function __construct( $context ) {
        $this->context = $context;
    }

    /**
     * Determine if piece should be added to graph.
     *
     * @return bool
     */
    public function is_needed() {
        if ( ! is_singular() ) {
            return false;
        }

        $post_id = get_the_ID();
        $howto_data = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::HOWTO_META_KEY, true );

        return ! empty( $howto_data ) && is_array( $howto_data ) && ! empty( $howto_data['steps'] );
    }

    /**
     * Generate HowTo schema.
     *
     * @return array Array with single HowTo piece.
     */
    public function generate() {
        $post_id = get_the_ID();
        $howto_data = get_post_meta( $post_id, Easy_FAQ_HowTo_Metabox::HOWTO_META_KEY, true );

        if ( empty( $howto_data ) || empty( $howto_data['steps'] ) ) {
            return array();
        }

        $data = array(
            '@type'            => 'HowTo',
            '@id'              => $this->context->canonical . '#howto',
            'mainEntityOfPage' => array( '@id' => $this->context->main_schema_id )
        );

        // Add name if provided
        if ( ! empty( $howto_data['name'] ) ) {
            $data['name'] = wp_strip_all_tags( $howto_data['name'] );
        }

        // Add description if provided
        if ( ! empty( $howto_data['description'] ) ) {
            $data['description'] = wp_strip_all_tags( $howto_data['description'] );
        }

        // Add total time if provided
        if ( ! empty( $howto_data['total_time'] ) ) {
            $data['totalTime'] = $howto_data['total_time'];
        }

        // Add steps
        $steps = array();
        foreach ( $howto_data['steps'] as $index => $step ) {
            if ( empty( $step['name'] ) && empty( $step['text'] ) ) {
                continue;
            }

            $step_id = $this->context->canonical . '#howto-step-' . ( $index + 1 );
            $step_data = array(
                '@type' => 'HowToStep',
                'url'   => $step_id
            );

            if ( ! empty( $step['name'] ) ) {
                $step_data['name'] = wp_strip_all_tags( $step['name'] );
            }

            if ( ! empty( $step['text'] ) ) {
                $step_data['text'] = wp_strip_all_tags( $step['text'] );
            }

            $steps[] = $step_data;
        }

        if ( ! empty( $steps ) ) {
            $data['step'] = $steps;
        }

        return array( $data );
    }
}
