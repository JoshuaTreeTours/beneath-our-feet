<?php
/**
 * Beneath Our Feet structured data (JSON-LD only; no visible markup changes).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_schema_social_profiles() {
    /**
     * Add only verified organization/profile URLs here.
     * Google supports social profiles through Organization.sameAs.
     */
    return apply_filters( 'bof_schema_social_profiles', array() );
}

function bof_schema_breadcrumb_items( $page_id ) {
    $items = array(
        array(
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Home',
            'item'     => home_url( '/' ),
        ),
    );

    $ancestors = array_reverse( get_post_ancestors( $page_id ) );
    foreach ( $ancestors as $ancestor_id ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => count( $items ) + 1,
            'name'     => get_the_title( $ancestor_id ),
            'item'     => get_permalink( $ancestor_id ),
        );
    }

    $items[] = array(
        '@type'    => 'ListItem',
        'position' => count( $items ) + 1,
        'name'     => get_the_title( $page_id ),
    );

    return $items;
}

function bof_output_structured_data() {
    if ( is_admin() || is_feed() || is_404() ) {
        return;
    }

    $site_url = home_url( '/' );
    $org_id   = $site_url . '#organization';
    $site_id  = $site_url . '#website';
    $logo_url = get_stylesheet_directory_uri() . '/assets/bof-site-icon.png';

    $organization = array(
        '@type'       => 'Organization',
        '@id'         => $org_id,
        'name'        => 'Beneath Our Feet',
        'url'         => $site_url,
        'description' => 'A visual geology project exploring landscapes, rocks, fossils, tectonics, deep time, and the evidence preserved beneath our feet.',
        'logo'        => array(
            '@type' => 'ImageObject',
            'url'   => $logo_url,
        ),
    );

    $same_as = array_values( array_filter( array_map( 'esc_url_raw', bof_schema_social_profiles() ) ) );
    if ( $same_as ) {
        $organization['sameAs'] = $same_as;
    }

    $graph = array( $organization );

    $graph[] = array(
        '@type'      => 'WebSite',
        '@id'        => $site_id,
        'url'        => $site_url,
        'name'       => 'Beneath Our Feet',
        'publisher'  => array( '@id' => $org_id ),
        'inLanguage' => 'en-US',
    );

    if ( is_front_page() ) {
        $graph[] = array(
            '@type'      => 'WebPage',
            '@id'        => $site_url . '#webpage',
            'url'        => $site_url,
            'name'       => 'Beneath Our Feet',
            'isPartOf'   => array( '@id' => $site_id ),
            'about'      => array( '@id' => $org_id ),
            'inLanguage' => 'en-US',
        );
    } elseif ( is_page() ) {
        $page_id       = get_queried_object_id();
        $page_url      = get_permalink( $page_id );
        $page_title    = get_the_title( $page_id );
        $is_topic      = (bool) get_post_meta( $page_id, '_bof_topic_index', true );
        $is_panel      = (bool) get_post_meta( $page_id, '_bof_topic_panel', true );
        $description   = '';

        if ( $is_topic ) {
            $description = (string) get_post_meta( $page_id, '_bof_topic_description', true );
        } elseif ( has_excerpt( $page_id ) ) {
            $description = get_the_excerpt( $page_id );
        }

        $page = array(
            '@type'      => $is_topic ? 'CollectionPage' : 'WebPage',
            '@id'        => $page_url . '#webpage',
            'url'        => $page_url,
            'name'       => $page_title,
            'isPartOf'   => array( '@id' => $site_id ),
            'inLanguage' => 'en-US',
        );
        if ( $is_topic || $is_panel ) {
            $page['breadcrumb'] = array( '@id' => $page_url . '#breadcrumb' );
        }
        if ( $description ) {
            $page['description'] = wp_strip_all_tags( $description );
        }
        $graph[] = $page;

        if ( $is_panel ) {
            $attachment_id = (int) get_post_meta( $page_id, '_bof_topic_attachment_id', true );
            $topic_title   = (string) get_post_meta( $page_id, '_bof_topic_title', true );
            $parent_id     = wp_get_post_parent_id( $page_id );
            $image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';

            $creative = array(
                '@type'      => 'CreativeWork',
                '@id'        => $page_url . '#creativework',
                'name'       => $page_title,
                'url'        => $page_url,
                'mainEntityOfPage' => array( '@id' => $page_url . '#webpage' ),
                'publisher'  => array( '@id' => $org_id ),
                'inLanguage' => 'en-US',
            );
            if ( $image_url ) {
                $creative['image'] = $image_url;
            }
            if ( $parent_id ) {
                $creative['isPartOf'] = array(
                    '@type' => 'CollectionPage',
                    '@id'   => get_permalink( $parent_id ) . '#webpage',
                    'name'  => $topic_title ? $topic_title : get_the_title( $parent_id ),
                    'url'   => get_permalink( $parent_id ),
                );
            }
            $graph[] = $creative;
        }

        if ( $is_topic ) {
            $children = get_posts(
                array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_parent'    => $page_id,
                    'posts_per_page' => -1,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                )
            );

            if ( $children ) {
                $list_items = array();
                foreach ( $children as $index => $child_id ) {
                    $list_items[] = array(
                        '@type'    => 'ListItem',
                        'position' => $index + 1,
                        'name'     => get_the_title( $child_id ),
                        'url'      => get_permalink( $child_id ),
                    );
                }
                $graph[] = array(
                    '@type'           => 'ItemList',
                    '@id'             => $page_url . '#itemlist',
                    'name'            => $page_title . ' panels',
                    'itemListElement' => $list_items,
                );
            }
        }

        $graph[] = array(
            '@type'           => 'BreadcrumbList',
            '@id'             => $page_url . '#breadcrumb',
            'itemListElement' => bof_schema_breadcrumb_items( $page_id ),
        );
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    );

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'bof_output_structured_data', 25 );
