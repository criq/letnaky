window.fbAsyncInit = function() {
FB.init({
	appId      : _config.facebook.appId,
	status     : true,
	xfbml      : true
});
};

(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/" + (typeof _config.facebook.locale == 'string' ? _config.facebook.locale : 'en_US') + "/all.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
