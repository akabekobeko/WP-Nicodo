( function( $ ) {
// Check jQuery ( Required )
if( !$ ) { return; }

$( document ).ready(function($) {
    edButtons[ edButtons.length ] = new edButton( "ed_nicodo", "nicodo", "[nicodo]", "[/nicodo]", "n" );
});

} )( jQuery );
