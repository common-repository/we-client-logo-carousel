jQuery(document).ready(function(){
	jQuery('.customer-logos').slick({
		slidesToShow: Number(weclient.items),					
		slidesToScroll: 1,
		autoplay: Boolean('1' == weclient.auto_play),
		autoplaySpeed: Number(weclient.slide_speed),
		arrows: Boolean('1' == weclient.navigation),
		dots: true,
		pauseOnHover: Boolean('1' == weclient.stop_on_hover),
		nextArrow: '<div class="wec-next-arr">&nbsp;</div>',
		prevArrow: '<div class="wec-prev-arr">&nbsp;</div>',
	});
});