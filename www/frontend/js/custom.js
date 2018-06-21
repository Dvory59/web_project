(function($) {
	'use strict';	

	//============================== NAVBAR =========================

	$(window).on('load', function(){
		$('#body').each(function(){
			var header_area = $('.header');
			var main_area = header_area.find('.navbar');
			var logo = main_area.find('.site-logo');
			var navigation = main_area.find('.navigation');
			var original = {
				nav_top: navigation.css('margin-top')
			};

			$(window).scroll(function(){
				if( main_area.hasClass('navbar-sticky') && ($(this).scrollTop() <= 100 || $(this).width() <= 750)){
					main_area.removeClass('navbar-sticky').appendTo(header_area);
					navigation.animate({'margin-top': original.nav_top}, {duration: 100, queue: false, complete: function(){
						header_area.css('height', 'auto');

					}});
				}else if( !main_area.hasClass('navbar-sticky') && $(this).width() > 750 && $(this).scrollTop() > 150 ){

					header_area.css('height', header_area.height());
					main_area.css({'opacity': '0'}).addClass('navbar-sticky');
					main_area.appendTo($('body')).animate({'opacity': 1});

					navigation.css({'margin-top': '0px'});
				}
			});
		});

		$(window).trigger('resize');
		$(window).trigger('scroll');
	});

	//============================== ICON TOGGLER ANIMATION =========================
	$('.btn-cart').on('click', function(e){
		e.preventDefault();
		$('.cart_item-box').toggleClass('visible');
	});

	$('.btn-search').on('click', function(e){
		e.preventDefault();
		$('.search_form').toggleClass('visible');
	});

	var shadowClass = $('.mobile-sticky-header-overlay');
	var collapsibleDiv = $('.navbar-collapse');
	var toolbarToggle = $('.icon-toggle');

	$('.navbar-toggler').on('click', function(e){
		$(this).css('visibility', 'hidden').toggleClass('clicked');
		$('.navbar-brand').toggleClass('shade');
		toolbarToggle.toggleClass('active');
		shadowClass.toggleClass('active');
		collapsibleDiv.toggleClass('show');
		$(this).css('visibility', 'visible');
	});

	$('.navbar a.dropdown-toggle').on('click', function(e) {
		var elmnt = $(this).parent().parent();
		if (!elmnt.hasClass('navbar-nav')) {
			var li = $(this).parent();
			var heightParent = parseInt(elmnt.css('height').replace('px', ''),10) / 2;
			var widthParent = parseInt(elmnt.css('width').replace('px', ''),10) - 10;

			if(!li.hasClass('show')){li.addClass('show');}
			else{ li.removeClass('show');}
			$(this).next().css('top', heightParent + 'px');
			$(this).next().css('left', widthParent + 'px');

			return false;
		}
	});

	//============================== ALL DROPDOWN ON HOVER =========================
	if($('.navbar').width() > 768){
		$('.navbar-nav .dropdown').hover(function() {
			$(this).addClass('show');
		},
		function() {
			$(this).removeClass('show');
		});
	}

	//============================== MAIN SLIDER =========================
	function doAnimations(elements) {
		var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
		elements.each(function() {
			var $this = $(this);
			var $animationDelay = $this.data('delay');
			var $animationDuration = $this.data('duration');
			var $animationType = 'animated ' + $this.data('animation');
			$this.css({
				'animation-delay': $animationDelay,
				'-webkit-animation-delay': $animationDelay,
				'animation-duration': $animationDuration,
				'-webkit-animation-duration': $animationDuration
			});
			$this.addClass($animationType).one(animationEndEvents, function() {
				$this.removeClass($animationType);
			});
		});
	}

	var $myCarousel = $('#carousel-example-generic, #carousel-example-two'),
	$firstAnimatingElems = $myCarousel.find('.carousel-item:first').find('[data-animation ^= "animated"]');

	$myCarousel.carousel();
	doAnimations($firstAnimatingElems);
	$myCarousel.carousel('pause');

	$myCarousel.on('slide.bs.carousel', function (e) {
		var $animatingElems = $(e.relatedTarget).find('[data-animation ^= "animated"]');
		doAnimations($animatingElems);
	});

	$('#banner-slider').on('init', function(e, slick) {
        var $firstAnimatingElements = $('div.item:first-child').find('[data-animation]');
        doAnimations($firstAnimatingElements);    
    });
    $('#banner-slider').on('beforeChange', function(e, slick, currentSlide, nextSlide) {
		var $animatingElements = $('div.item[data-slick-index="' + nextSlide + '"]').find('[data-animation]');
		doAnimations($animatingElements);    
    });

	   
	$('#banner-slider').slick({
		autoplay: true,
		autoplaySpeed: 3000,
		dots: true,
		arrows: false,
		fade: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					dots: true
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					autoplay: true
				}
			},
			{
				breakpoint: 480,
				settings: {
					dots: false
				}
			}
		]
	});

	$('#home-testimonial').slick({
		autoplay: false,
		autoplaySpeed: 3000,
		dots: false,
		arrows: false,
		slidesToShow: 1,
		slidesToScroll: 1
	});

	/* Slick Slider for Element carousels */
	$('#brands').slick({
		autoplay: true,
		infinite: true,
		arrows: false,
		slidesToShow: 3,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 2,
					autoplay: true,
					infinite: true,
					autoplaySpeed: 2000,
					dots: false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: false,
					autoplay: true,
					autoplaySpeed: 3000
				}
			}
		]
	});

	$('#image-carousel').slick({
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		arrows: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 2,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					arrows: true,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					autoplay: true,
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: false,
					autoplay: true,
					autoplaySpeed: 3000
				}
			}
		]
	});

	$('#card').slick({
		slidesToShow: 3,
		slidesToScroll: 3,
		arrows: false,
		dots: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					autoplay: true,
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: false,
					autoplay: true,
					autoplaySpeed: 3000
				}
			}
		]
	});

	$('#testimonial').slick({
		slidesToShow: 2,
		slidesToScroll: 2,
		arrows: false,
		dots: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					autoplay: true,
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: false,
					autoplay: true,
					autoplaySpeed: 3000
				}
			}
		]
	});
	/* Slick Slider for Main pages carousels */
	$('.slick_brands').slick({
		infinite: true,
		slidesToShow: 4,
		autoplay: true,
		slidesToScroll: 1,
		dots: false,
		arrows: false,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 2,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					dots: false,
					arrows: false,
					autoplay: true,
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: false,
					autoplay: true,
					autoplaySpeed: 3000
				}
			}
		]
	});

	$('.event_carousel').slick({
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		autoplay: true,
		autoplaySpeed: 3000
	});
	//============================== CIRCLE PROGRESS =========================

	var el = $('.circle1'),
	inited = false;

	el.appear({ force_process: true });

	el.on('appear', function() {
		if (!inited) {
			el.circleProgress({
				size : 150
			});
			inited = true;
		}
	});

	//============================== VIDEOBOX =========================
	var videoBox = $('.video-box i');
	videoBox.on('click', function(){
		var video = '<iframe class="embed-responsive-item"  allowfullscreen src="'+ $(this).attr('data-video') +'"></iframe>';
		$(this).replaceWith(video);
	});


	//============================ LAZY LOAD =========================
	var myLazyLoad = new LazyLoad();
	//============================== CHARTJS =========================
	var ctx = $('#myChart');
	if (ctx.length) {
		var myChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: ['2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016'],
				datasets: [{
					label: 'Yearly Static',
					data: [{}, 4.4, 2.6, 2.95, 3.9, 2.5, 3.1, 5.6],
					backgroundColor: [
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)',
						'rgba(74,200,237,0.6)'
					],
					borderColor: [
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)',
						'rgba(74,200,237,0.8)'
					],
					borderWidth: 0,
					hoverBackgroundColor: 'rgba(74,200,237,1)'
				}]
			},
			options: {
				scales: {
					xAxes: [{
						barPercentage: 1,
						gridLines: {
							offsetGridLines: true
						}
					}],
					yAxes: [{
						gridLines: {
							offsetGridLines: false
						},
						stacked: true
					}]
				}
			}
		});
	}
	//============================== SELECT BOX =========================
	$('.select-drop').selectbox();

	//============================== VIDEO BOX =========================
	$('.box-video').click(function(){
		$('iframe',this)[0].src += '&amp;autoplay=1';
		$(this).addClass('open');
	});

	//============================== TIMER =========================
	$('.simple_timer').syotimer({
		year: 2018,
		month: 5,
		day: 9,
		hour: 20,
		minute: 30
	});

	//============================== COUNTER-UP =========================

	$('.counter').counterUp({
		delay: 10,
		time: 2000
	});

	//============================== fANCYMORPH =========================
	$('[data-morphing]').fancyMorph({
	  hash : 'morphing'
	});


	//============================== MAP =========================

	function initialize() {
		var styleArray = [
    {
        'featureType': 'administrative',
        'elementType': 'labels.text.fill',
        'stylers': [
            {
                'color': '#444444'
            }
        ]
    },
    {
        'featureType': 'landscape',
        'elementType': 'all',
        'stylers': [
            {
                'color': '#f2f2f2'
            }
        ]
    },
    {
        'featureType': 'poi',
        'elementType': 'all',
        'stylers': [
            {
                'visibility': 'off'
            }
        ]
    },
    {
        'featureType': 'road',
        'elementType': 'all',
        'stylers': [
            {
                'saturation': -100
            },
            {
                'lightness': 45
            }
        ]
    },
    {
        'featureType': 'road.highway',
        'elementType': 'all',
        'stylers': [
            {
                'visibility': 'simplified'
            }
        ]
    },
    {
        'featureType': 'road.arterial',
        'elementType': 'labels.icon',
        'stylers': [
            {
                'visibility': 'off'
            }
        ]
    },
    {
        'featureType': 'transit',
        'elementType': 'all',
        'stylers': [
            {
                'visibility': 'off'
            }
        ]
    },
    {
        'featureType': 'water',
        'elementType': 'all',
        'stylers': [
            {
                'color': '#46bcec'
            },
            {
                'visibility': 'on'
            }
        ]
    },
    {
        'featureType': 'water',
        'elementType': 'geometry.fill',
        'stylers': [
            {
                'color': '#bfd3e4'
            }
        ]
    }
];

		var myLatLng = {lat: 40.6094957, lng: -73.7300059};

		var mapOptions = {
			zoom: 14,
			scrollwheel: false,
			center: new google.maps.LatLng(40.6094957, -73.7300059),
			styles: styleArray
		};

		var map = new google.maps.Map(document.getElementById('googleMap'), mapOptions);
		var image = 'img/marker.png';
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			icon : image
		});
	}
	var mapId = $('#googleMap');
	if (mapId.length) {
		google.maps.event.addDomListener(window, 'load', initialize);
	}

	//============================== ISOTOP =========================
	var grid = $('.gallery_grid');
	if(grid.length) {
		grid.isotope({
			itemSelector : '.element',
			layoutMode : 'fitRows'
		});

		$('#filters .button').on('click', function() {
			$('#filters .button').removeClass('active');
			$(this).addClass('active');

			var selector = $(this).attr('data-filter');
			$('.gallery_grid').isotope({
				filter : selector
			});
			return false;
		});
	}

	//=========================== FANCYBOX ========================== 

	$('.quick_view').fancybox({
		baseClass : 'quick-view-container',
		infobar   : false,
		buttons   : false,
		thumbs    : false,
		margin    : 0,
		touch     : {
			vertical : false
		},
		animationEffect    : false,
		transitionEffect   : 'slide',
		transitionDuration : 500,
		baseTpl :
		'<div class="fancybox-container" role="dialog">' +
		'<div class="quick-view-content">' +
		'<div class="quick-view-carousel">' +
		'<div class="fancybox-stage"></div>' +
		'</div>' +
		'<div class="quick-view-aside"></div>' +
		'<button data-fancybox-close class="quick-view-close">X</button>' +
		'</div>' +
		'</div>',

		onInit : function( instance ) {
			/*
			#1 Create bullet navigation links
			=================================
			*/
			var bullets = '<ul class="quick-view-bullets">';

			for ( var i = 0; i < instance.group.length; i++ ) {
				bullets += '<li><a data-index="' + i + '" href="javascript:;"><span>' + ( i + 1 ) + '</span></a></li>';
			}

			bullets += '</ul>';

			$( bullets ).on('click touchstart', 'a', function() {
				var index = $(this).data('index');

				$.fancybox.getInstance(function() {
					this.jumpTo( index );
				});

			})
			.appendTo( instance.$refs.container.find('.quick-view-carousel') );
			/*
			#2 Add product form
			===================
			*/
			var $element = instance.group[ instance.currIndex ].opts.$orig;
			var form_id  = $element.data('qw-form');

			instance.$refs.container.find('.quick-view-aside').append(

				// In this example, this element contains the form
				$( '#' + form_id ).clone( true ).removeClass('hidden')
			);
		},
		beforeShow : function( instance ) {
			/*
			Mark current bullet navigation link as active
			*/
			instance.$refs.container.find('.quick-view-bullets')
			.children()
			.removeClass('active')
			.eq( instance.currIndex )
			.addClass('active');
		}

	});

})(jQuery);
