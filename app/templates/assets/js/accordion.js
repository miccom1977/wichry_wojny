/* =========================================================

// jquery.simpleAccordion.js

// author: Andrzej Kluczny
// website: www.DesignEnd.net
// email: kontakt@designend.net
// date: 2010-11-18

 *
 *  <div id="simple-accordion"> 
 * 	<a href="#">Item 1</a>
*	<div>Content 1</div>
 * 	<a href="#">Item 2</a>
*	<div>Content 2</div>
 * 	<a href="#">Item 3</a>
*	<div>Content 3</div>
 *  </div>
 *  
 *  $('#simple-accordion').simpleAccordion({ 
 *	duration: Sliding duration time in ms (Default: 300),
 *	collapsed: Shall all items be collapsed on load (Default: true),
 *	expanded: Index of element to expand on load(Default: null)
 *  }); 
 *

// ========================================================= */
(function($){
	$.fn.simpleAccordion = function(options){
		var opts = $.extend({
			duration: 300,
			collapsed: false,
			expanded: null
		},options);
		
		var $that = $(this);
		
		$that.children('h1').addClass('accordion-link');
		$that.children('div').addClass('accordion-content');
		if(opts.collapsed) {
			$that.children('div').hide();
		}
		if(opts.expanded != null) {
			var $count = 0;
			$that.children('h1').each(function(){
				if($count == opts.expanded) {
					$(this).addClass('toggled').next().addClass('toggled').slideDown(opts.duration);
				}
				$count++;
			});
		}
		$('h1.accordion-link').click(function(){
			if($(this).hasClass('toggled')) {
				$that.children('h1.toggled').removeClass('toggled');
				$that.children('div.toggled').stop(true,true).slideUp(opts.duration);
			} else {
				$that.children('h1.toggled').removeClass('toggled');
				$that.children('div.toggled').stop(true,true).slideUp(opts.duration);
				$(this).addClass('toggled').next().addClass('toggled').stop(true,true).slideDown(opts.duration);
			}
			return false;
		});
	}
})(jQuery);