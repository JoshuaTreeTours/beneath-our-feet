<?php
/**
 * Add lightweight inline SVG icons to the three primary homepage category cards.
 * No Gutenberg content or card structure is changed.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bof_home_category_icon_svg( $title ) {
    $icons = array(
        'Places' => '<svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><path d="M6 49 23 26l9 12 8-10 18 21H6Z" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="m18 34 5-8 5 7m8 2 4-7 5 6" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>',
        'Deep Time' => '<svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><path d="M34 52c-13 0-23-9-23-21S21 10 34 10c11 0 19 7 19 16 0 8-6 14-14 14-7 0-12-4-12-10 0-5 4-9 9-9 4 0 7 3 7 6 0 3-2 5-5 5-2 0-4-1-4-3" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M20 47c6 4 14 6 22 5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>',
        'How We Know' => '<svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><path d="m16 14 18 18m-7-23 13 13-8 8-13-13 8-8Z" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/><path d="m31 31 17 17m-4-4 7-7m-9 9-7 7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>',
    );

    return isset( $icons[ $title ] ) ? $icons[ $title ] : '';
}

function bof_add_home_category_icons( $block_content, $block ) {
    if ( ! is_front_page() || empty( $block['blockName'] ) || 'core/heading' !== $block['blockName'] ) {
        return $block_content;
    }

    foreach ( array( 'Places', 'Deep Time', 'How We Know' ) as $title ) {
        if ( false === strpos( wp_strip_all_tags( $block_content ), $title ) ) {
            continue;
        }

        $svg = bof_home_category_icon_svg( $title );
        if ( ! $svg || false !== strpos( $block_content, 'bof-category-icon' ) ) {
            return $block_content;
        }

        return preg_replace(
            '/(<h3\\b[^>]*>)(.*?)(<\\/h3>)/is',
            '$1<span class="bof-category-heading-inner">$2<span class="bof-category-icon">' . $svg . '</span></span>$3',
            $block_content,
            1
        );
    }

    return $block_content;
}
add_filter( 'render_block', 'bof_add_home_category_icons', 35, 2 );

function bof_home_category_icon_styles() {
    if ( ! is_front_page() ) {
        return;
    }

    $css = '
    .bof-category-heading-inner{position:relative;display:block;padding-right:4.25rem}
    .bof-category-icon{position:absolute;right:.05rem;top:50%;width:3.4rem;height:3.4rem;transform:translateY(-50%);color:var(--bof-gold);opacity:.34;pointer-events:none}
    .bof-category-icon svg{display:block;width:100%;height:100%}
    @media(max-width:600px){.bof-category-heading-inner{padding-right:3.7rem}.bof-category-icon{width:3rem;height:3rem;opacity:.3}}
    ';

    wp_add_inline_style( 'beneath-our-feet-style', $css );
}
add_action( 'wp_enqueue_scripts', 'bof_home_category_icon_styles', 31 );
