<?php

/**
 * A workaround for the is_front_page() check inside pre_get_posts and later hooks.
 *
 * Based on the patch from @mattonomics in #27015
 *
 * @see http://wordpress.stackexchange.com/a/188320/26350
 */

add_action( 'parse_query', function( $q )
{
    if( is_null( $q->queried_object ) && $q->get( 'page_id' ) )
    {
        $q->queried_object    = get_post( $q->get( 'page_id' ) );
        $q->queried_object_id = (int) $q->get( 'page_id' );
    }
} );
