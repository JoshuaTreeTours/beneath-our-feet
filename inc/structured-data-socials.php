<?php
/** Verified Beneath Our Feet social profiles for Organization.sameAs. */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter(
    'bof_schema_social_profiles',
    function () {
        return array(
            'https://www.facebook.com/BeneathOurFeetEarth/',
            'https://www.instagram.com/beneathourfeet.earth/',
        );
    }
);
