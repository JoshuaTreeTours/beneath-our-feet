<?php
/** Redirect the legacy menu search URLs directly into curated panel collections. */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_menu_topic_redirects() {
    if ( ! is_search() ) {
        return;
    }

    $query = strtolower( trim( get_search_query() ) );
    $map = array(
        'places'                    => 'places',
        'santa barbara'             => 'santa-barbara-geology',
        'colorado plateau'          => 'colorado-plateau',
        'california geology'        => 'california-geology',
        'deep time'                 => 'deep-time',
        'plate tectonics'           => 'plate-tectonics',
        'supercontinents'           => 'supercontinents',
        'mountain building'         => 'mountain-building',
        'geologic time'             => 'geologic-time-scale',
        'rocks minerals'            => 'rocks-minerals',
        'fossils'                   => 'fossils-life',
        'faults earthquakes'        => 'faults-earthquakes',
        'volcanoes magma'           => 'volcanoes-magma',
        'erosion landscapes'        => 'erosion-landscapes',
        'oceans coasts climate'     => 'oceans-coasts-climate',
        'how we know'               => 'how-we-know',
        'maps field guides'         => 'maps-field-guides',
        'moon'                      => 'moon',
        'about beneath our feet'    => 'about',
    );

    if ( isset( $map[ $query ] ) ) {
        wp_safe_redirect( home_url( '/collections/' . $map[ $query ] . '/' ) );
        exit;
    }
}
add_action( 'template_redirect', 'bof_menu_topic_redirects', 1 );
