</div><!-- .site-content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
    <?php if ( is_active_sidebar( 'widget-area-footer' ) ) : ?>
			<div id="widget-area" class="widget-area" role="complementary">
				<?php dynamic_sidebar( 'widget-area-footer' ); ?>
			</div><!-- .widget-area -->
		<?php endif; ?>
    
    
		<div class="site-info">
			<?php
				/**
				 * Fires before the footer text for footer customization.
				 *
				 */
				do_action( 'setlr_credits' );
			?>
            <?php if ( has_nav_menu( 'social' ) ) : ?>
			<nav id="social-navigation" class="social-navigation" role="navigation">
				<?php
					// Social links navigation menu.
					wp_nav_menu( array(
						'theme_location' => 'social',
						'depth'          => 1,
						'link_before'    => '<span class="screen-reader-text">',
						'link_after'     => '</span>',
					) );
				?>
			</nav><!-- .social-navigation -->
		<?php endif; ?>
			
		</div><!-- .site-info -->
		
	</footer><!-- .site-footer -->

</div><!-- .site -->

<!-- begin tabs-->
<script type="text/javascript">
(function($){

	$('.tab1').click(function(){
		tabClicked(1);

		$('.tabNav li').removeClass('activeTab');
		$(this).addClass('activeTab');

	})

	$('.tab2').click(function(){
		tabClicked(2);

		$('.tabNav li').removeClass('activeTab');
		$(this).addClass('activeTab');

	})

	$('.tab3').click(function(){
		tabClicked(3);

		$('.tabNav li').removeClass('activeTab');
		$(this).addClass('activeTab');

	})

	
	$('.tab4').click(function(){
		tabClicked(4);

		$('.tabNav li').removeClass('activeTab');
		$(this).addClass('activeTab');

	})


	$('.tab5').click(function(){
		tabClicked(5);

		$('.tabNav li').removeClass('activeTab');
		$(this).addClass('activeTab');

	})


	function tabClicked(contentNumber){
		
		$('.content').hide();
		$('.content--'+contentNumber).show();

	}

})(jQuery);
</script>
<!--/end tabs -->

<?php wp_footer(); ?>
<!-- begin olark code -->
<script data-cfasync="false" type='text/javascript'>/*<![CDATA[*/window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){
f[z]=function(){
(a.s=a.s||[]).push(arguments)};var a=f[z]._={
},q=c.methods.length;while(q--){(function(n){f[z][n]=function(){
f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={
0:+new Date};a.P=function(u){
a.p[u]=new Date-a.p[0]};function s(){
a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){
hd="head";return["<",hd,"></",hd,"><",i,' onl' + 'oad="var d=',g,";d.getElementsByTagName('head')[0].",j,"(d.",h,"('script')).",k,"='",l,"//",a.l,"'",'"',"></",i,">"].join("")}var i="body",m=d[i];if(!m){
return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){
b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{
b.contentWindow[g].open()}catch(w){
c[e]=d[e];o="javascript:var d="+g+".open();d.domain='"+d.domain+"';";b[k]=o+"void(0);"}try{
var t=b.contentWindow[g];t.write(p());t.close()}catch(x){
b[k]=o+'d.write("'+p().replace(/"/g,String.fromCharCode(92)+'"')+'");d.close();'}a.P(2)};ld()};nt()})({
loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
/* custom configuration goes here (www.olark.com/documentation) */
olark.identify('1790-562-10-2580');/*]]>*/</script><noscript><a href="https://www.olark.com/site/1790-562-10-2580/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a></noscript>
<!-- end olark code -->
<!-- begin Facebook Developer code -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=863664443705058";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- end Facebook Developer code -->
</body>
</html>
