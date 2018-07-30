$(function () {

    /*$(".toggle-dropdown").on("mouseenter", function() {
        $(".dropdown-abst").show();
    }).on("mouseleave", function() {
        $(".dropdown-abst").hide();
    });*/

    $('.slide-show-abstract').click(function( e ){
        var SH = this.SH^=1; // "Simple toggler"
        $(this).text(SH?'Hide abstract':'Show abstract')
            .css({backgroundPosition:'0 '+ (SH?-18:0) +'px'})
            .next(".toggle-down").slideToggle();
    });

    $('.slide-show-citation').click(function( e ){
        var SH = this.SH^=1; // "Simple toggler"
        $(this).text(SH?'Hide citation':'Show citation')
            .css({backgroundPosition:'0 '+ (SH?-18:0) +'px'})
            .next(".toggle-down").slideToggle();
    });

    $('.slide-show-authors').click(function( e ){
        var SH = this.SH^=1; // "Simple toggler"
        $(this).text(SH?'Hide authors':'Show all authors')
            .css({backgroundPosition:'0 '+ (SH?-18:0) +'px'})
            .next(".toggle-down").slideToggle();
    });

});