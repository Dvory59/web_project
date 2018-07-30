$(function(){ // DOM READY shorthand

    //$(".profile-detail-block").hide();

    $('.slide-show').click(function( e ){
        var SH = this.SH^=1; // "Simple toggler"
        $(this).text(SH?'Hide profile details':'Show profile details')
            .css({backgroundPosition:'0 '+ (SH?-18:0) +'px'})
            .next(".profile-detail-block").slideToggle();
    });

});