( function( $ ) {
    // Check jQuery ( Required )
    if( !$ ) { return; }

    $( "#ed_toolbar" ).each( function() {
        var button       = document.createElement( "input" );
        button.type      = "button";
        button.value     = "nicodo";
        button.onclick   = function(){ edInsertContent( edCanvas, "[nicodo][/nicodo]" ); };
        button.className = "ed_button";
        button.title     = "niconico";
        button.id        = "ed_nicodo";

        $( this ).append( button );
    } );

} )( jQuery );
