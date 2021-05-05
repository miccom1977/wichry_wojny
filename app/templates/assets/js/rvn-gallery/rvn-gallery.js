/*
 * jQuery rvnGallery v2.3
 
 * Copyright 2013, Krukowski Bartłomiej
 * http://rvn-gallery.krukowski.me
 * Date: 2013.03.25

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
(function($) {
	
	//domyślne ustawienia
	var rvnGalleryDefaults = {
		effect: 'random', //efekt przejść
		verticalSlices: 10, //ilość pasków pionowych
		horizontalSlices: 8, //ilość pasków poziomych
		boxCols: 6, //ilość kolumn
		boxRows: 4, // ilość wierszy
		delay: 5000, //czas opóźnień pomiędzy kolejnymi animacjami
		animSpeed: 600, //czas animacji
		sliceDelay: 50, //czas opóźnień między animacją kolejnych pasków
		backgroundColor: '#ffffff', //kolor tła
		slider: true, //automatyczny pokaz slajdów
		captionOpacity: 1, //przezroczystość paska z tekstem
		navButtons: true
	}
	
	//stała definiująca klucz dostępu do zmiennych danej galerii
	var RVN_DATA_KEY = 'http://rvn-gallery.krukowski.me';	
	
	//tablica animacji
	var rvnAnim = {
		alternatelyRight: function(slices, settings){
			var s = rvnFilter(slices, '.vertical');
			s.filter(':even').each(function(){
				$(this).css({
					top: 0,
					left: 0,
					opacity: 0,
					width: $(this).parent().width()
				});
			});
			s.filter(':odd').each(function(){
				$(this).css({
					bottom: 0,
					left: 0,
					opacity: 0,
					width: $(this).parent().width()
				});
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({height: self.parent().height(), opacity: 1}, settings.animSpeed); }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + (settings.verticalSlices-1)*settings.sliceDelay;
		},
		alternatelyLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.alternatelyRight(slices, settings);
		},
		alternatelyBottom: function(slices, settings){
			var s = rvnFilter(slices, '.horizontal');
			s.filter(':even').each(function(){
				$(this).css({
					left: 0,
					top: 0,
					opacity: 0,
					height: $(this).parent().height()
				});
			});
			s.filter(':odd').each(function(){
				$(this).css({
					right: 0,
					top: 0,
					opacity: 0,
					height: $(this).parent().height()
				});
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({width: self.parent().width(), opacity: 1}, settings.animSpeed); }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + (settings.horizontalSlices-1)*settings.sliceDelay;
		},
		alternatelyTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.alternatelyBottom(slices, settings);
		},
		blindsRight: function(slices, settings, back){
			var c = (back)?'right':'left';
			var s = rvnFilter(slices, '.vertical');
			s.each(function(){
				$(this).css({
					top: 0,
					height: $(this).parent().height()
				}).css(c, 0);
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({width: self.parent().width()}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.verticalSlices-1);
		},
		blindsLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.blindsRight(slices, settings, true);
		},
		blindsBottom: function(slices, settings, back){
			var c = (back)?'bottom':'top';
			var s = rvnFilter(slices, '.horizontal');
			s.each(function(){
				$(this).css({
					left: 0,
					width: $(this).parent().width()
				}).css(c, 0)
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({height: self.parent().height()}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.horizontalSlices-1);
		},
		blindsTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.blindsBottom(slices, settings, true);
		},
		tiersBottomRight: function(slices, settings, t){
			var c = (t)?'bottom':'top';
			var s = rvnFilter(slices, '.vertical');
			s.each(function(){
				$(this).css({
					left: 0,
					opacity: 0,
					width: $(this).parent().width()
				}).css(c, 0);
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({height: self.parent().height(), opacity: 1}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.verticalSlices-1);
		},
		tiersBottomLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.tiersBottomRight(slices, settings);
		},
		tiersTopRight: function(slices, settings){
			return rvnAnim.tiersBottomRight(slices, settings, true);
		},
		tiersTopLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.tiersTopRight(slices, settings);
		},
		tiersRightBottom: function(slices, settings, t){
			var c = (t)?'right':'left';
			var s = rvnFilter(slices, '.horizontal');
			s.each(function(){
				$(this).css({
					top: 0,
					opacity: 0,
					height: $(this).parent().height()
				}).css(c, 0);
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({width: self.parent().width(), opacity: 1}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.horizontalSlices-1);
		},
		tiersRightTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.tiersRightBottom(slices, settings);
		},
		tiersLeftBottom: function(slices, settings){
			return rvnAnim.tiersRightBottom(slices, settings, true);
		},
		tiersLeftTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.tiersLeftBottom(slices, settings);
		},
		alphaRectRight: function(slices, settings){
			var s = rvnFilter(slices, '.vertical');
			s.each(function(){
				$(this).css({
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0,
					opacity: 0
				});
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed), settings.sliceDelay*i }, settings.sliceDelay*i);
				i++
			});
			return settings.animSpeed + settings.sliceDelay*(settings.verticalSlices-1);
		},
		alphaRectLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.alphaRectRight(slices, settings);
		},
		alphaRectBottom: function(slices, settings){
			var s = rvnFilter(slices, '.horizontal');
			s.each(function(){
				$(this).css({
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0,
					opacity: 0
				});
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed), settings.sliceDelay*i }, settings.sliceDelay*i);
				i++
			});
			return settings.animSpeed + settings.sliceDelay*(settings.horizontalSlices-1);
		},
		alphaRectTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.alphaRectBottom(slices, settings);
		},
		fade: function(slices, settings){
			var s = rvnFilter(slices, '.allArea');
			s.each(function(){
				$(this).css({
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0,
					opacity: 0
				});
			});
			s.animate({opacity: 1}, settings.animSpeed);
			return settings.animSpeed;
		},
		teethVertical: function(slices, settings){
			var s = rvnFilter(slices, '.vertical');
			s.filter(':even').each(function(){
				$(this).css({
					width: $(this).parent().width(),
					left: 0,
					top: 0
				});
			});
			s.filter(':odd').each(function(){
				$(this).css({
					width: $(this).parent().width(),
					left: 0,
					bottom: 0
				});
			});
			s.each(function(){
				$(this).animate({height: $(this).parent().height()}, settings.animSpeed);
			});
			return settings.animSpeed;
		},
		teethHorizontal: function(slices, settings){
			var s = rvnFilter(slices, '.horizontal');
			s.filter(':even').each(function(){
				$(this).css({
					height: $(this).parent().height(),
					left: 0,
					top: 0
				});
			});
			s.filter(':odd').each(function(){
				$(this).css({
					height: $(this).parent().height(),
					right: 0,
					top: 0
				});
			});
			s.each(function(){
				$(this).animate({width: $(this).parent().width()}, settings.animSpeed);
			});
			return settings.animSpeed;
		},
		newTiersBottomRight: function(slices, settings, t, u){
			var c = (t)?'bottom':'top';
			var d = (u)?'right':'left';
			var s = rvnFilter(slices, '.vertical');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: 0
				}).css(c, 0).css(d, 0);
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({width: self.parent().width(), height: self.parent().height(), opacity: 1}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.verticalSlices-1);
		},
		newTiersBottomLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.newTiersBottomRight(slices, settings, false, true);
		},
		newTiersTopRight: function(slices, settings){
			return rvnAnim.newTiersBottomRight(slices, settings, true);
		},
		newTiersTopLeft: function(slices, settings){
			slices.reverse();
			return rvnAnim.newTiersBottomRight(slices, settings, true, true);
		},
		newTiersRightBottom: function(slices, settings, t, u){			
			var c = (t)?'right':'left';
			var d = (u)?'bottom':'top';
			var s = rvnFilter(slices, '.horizontal');
			s.each(function(){
				$(this).css({
					opacity: 0,
					height: 0
				}).css(c, 0).css(d, 0);
			});
			var i = 0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({width: self.parent().width(), height: self.parent().height(), opacity: 1}, settings.animSpeed) }, settings.sliceDelay*i);
				i++;
			});
			return settings.animSpeed + settings.sliceDelay*(settings.horizontalSlices-1);
		},
		newTiersRightTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.newTiersRightBottom(slices, settings, false, true);
		},
		newTiersLeftBottom: function(slices, settings){
			return rvnAnim.newTiersRightBottom(slices, settings, true);
		},
		newTiersLeftTop: function(slices, settings){
			slices.reverse();
			return rvnAnim.newTiersRightBottom(slices, settings, true, true);
		},
		snake: function(slices, settings){
			var s = rvnFilter(slices, '.box');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0
				});
			});
			var i = 0;
			for (var row=0; row<settings.boxRows; row++)
			{
				var r = s.filter('[data-rvn-row="'+row+'"]');
				if (row%2)
				{
					r.reverse();
				}
				r.each(function(){
					var self = $(this);
					setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed); }, settings.sliceDelay*i);
					i++;
				});
			}
			return settings.animSpeed + settings.sliceDelay*(settings.boxRows*settings.boxCols-1);
		},
		gridLeftTop: function(slices, settings, p){
			var pos = p;
			if (typeof(p) == 'undefined')
			{
				pos = 'lt';
			}
			
			var s = rvnFilter(slices, '.box');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0
				});
			});
			s.each(function(){
				var self = $(this);
				var t = settings.sliceDelay;
				switch (pos)
				{
					case 'lt':
						t *= parseInt(self.attr('data-rvn-row')) + parseInt(self.attr('data-rvn-col'));
					break;
					
					case 'lb':
						t *= settings.boxRows - 1 - parseInt(self.attr('data-rvn-row')) + parseInt(self.attr('data-rvn-col'));
					break;
					
					case 'rt':
						t *= settings.boxCols - 1 - parseInt(self.attr('data-rvn-col')) + parseInt(self.attr('data-rvn-row'));
					break;
					
					case 'rb':
						t *= settings.boxCols + settings.boxRows - 2 - parseInt(self.attr('data-rvn-col')) - parseInt(self.attr('data-rvn-row'));
					break;
				}
				setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed); }, t );
			});
			return settings.animSpeed + (settings.boxRows+settings.boxCols-2-1)*settings.sliceDelay;
		},
		gridLeftBottom: function(slices, settings){
			return rvnAnim.gridLeftTop(slices, settings, 'lb');
		},
		gridRightTop: function(slices, settings){
			return rvnAnim.gridLeftTop(slices, settings, 'rt');
		},
		gridRightBottom: function(slices, settings){
			return rvnAnim.gridLeftTop(slices, settings, 'rb');
		},
		chessboard: function(slices, settings){
			var s = rvnFilter(slices, '.box');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0
				});
			});
			s.each(function(){
				var self = $(this);
				var t = 0;
				var c = parseInt(self.attr('data-rvn-col'));
				var r = parseInt(self.attr('data-rvn-row'));
				if ((c%2==1 && r%2==1) || (c%2==0 && r%2==0))
				{
					t = settings.sliceDelay;
				}
				setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed); }, t);
			});
			return settings.animSpeed+settings.sliceDelay;
		},
		randomBoxes: function(slices, settings){
			var s = rvnFilter(slices, '.box');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: $(this).parent().width(),
					height: $(this).parent().height(),
					left: 0,
					top: 0
				});
			});
			
			for (var i=s.length-1; i>0; i--){
				var j = Math.floor(Math.random() * (i+1));
				var temp = s[i];
				s[i] = s[j];
				s[j] = temp;
			}
			
			var i=0;
			s.each(function(){
				var self = $(this);
				setTimeout(function(){ self.animate({opacity: 1}, settings.animSpeed) }, settings.sliceDelay*i);
				i++
			});
			
			return settings.animSpeed + settings.sliceDelay*(settings.boxRows*settings.boxCols-1);
		},
		tilesLeftTop: function(slices, settings, p){
			var pos = p;
			if (typeof(p) == 'undefined')
			{
				pos = 'lt';
			}
			
			var initCss;
			switch (pos){
				case 'lt':
					initCss = {left: 0, top: 0};
				break;
				
				case 'rt':
					initCss = {right: 0, top: 0};
				break;
				
				case 'lb':
					initCss = {left: 0, bottom: 0};
				break;
				
				case 'rb':
					initCss = {right: 0, bottom: 0};
				break;
			}
			
			var s = rvnFilter(slices, '.box');
			s.each(function(){
				$(this).css({
					opacity: 0,
					width: 0,
					height: 0
				});
			}).css(initCss);
			
			s.each(function(){
				var self = $(this);
				var t = settings.sliceDelay;
				switch (pos){
					case 'lt':
						t *= parseInt(self.attr('data-rvn-row')) + parseInt(self.attr('data-rvn-col'));
					break;
					
					case 'lb':
						t *= settings.boxRows - 1 - parseInt(self.attr('data-rvn-row')) + parseInt(self.attr('data-rvn-col'));
					break;
					
					case 'rt':
						t *= settings.boxCols - 1 - parseInt(self.attr('data-rvn-col')) + parseInt(self.attr('data-rvn-row'));
					break;
					
					case 'rb':
						t *= settings.boxCols + settings.boxRows - 2 - parseInt(self.attr('data-rvn-col')) - parseInt(self.attr('data-rvn-row'));
					break;
				}
				setTimeout(function(){ self.animate({opacity: 1, width: self.parent().width(), height: self.parent().height()}, settings.animSpeed) }, t);
			});
			return settings.animSpeed + (settings.boxRows+settings.boxCols-2-1)*settings.sliceDelay;
		},
		tilesRightTop: function(slices, settings){
			return rvnAnim.tilesLeftTop(slices, settings, 'rt');
		},
		tilesLeftBottom: function(slices, settings){
			return rvnAnim.tilesLeftTop(slices, settings, 'lb');
		},
		tilesRightBottom: function(slices, settings){
			return rvnAnim.tilesLeftTop(slices, settings, 'rb');
		}
	}

	function rvnFilter(elements, filterPattern)
	{
		var result = elements.filter(filterPattern);
		result.parent().css('display', 'block');
		return result;
	}
	
	var rvnAnimPack = {
		alternately: ['alternatelyLeft', 'alternatelyRight', 'alternatelyBottom', 'alternatelyTop'],
		blinds: ['blindsRight', 'blindsLeft', 'blindsBottom', 'blindsTop'],
		tiers: ['tiersBottomRight', 'tiersBottomLeft', 'tiersTopRight', 'tiersTopLeft', 'tiersRightBottom', 'tiersRightTop', 'tiersLeftBottom', 'tiersLeftTop'],
		alphaRect: ['alphaRectRight', 'alphaRectLeft', 'alphaRectTop', 'alphaRectBottom'],
		teeth: ['teethVertical', 'teethHorizontal'],
		newTiers: ['newTiersBottomRight', 'newTiersBottomLeft', 'newTiersTopRight', 'newTiersTopLeft', 'newTiersRightBottom', 'newTiersRightTop', 'newTiersLeftBottom', 'newTiersLeftTop'],
		grid: ['gridLeftTop', 'gridLeftBottom', 'gridRightTop', 'gridRightBottom'],
		tiles: ['tilesLeftTop', 'tilesLeftBottom', 'tilesRightTop', 'tilesRightBottom']
	};
	
	//pomocnicza tablica nazw animacji (jest generowana automatycznie)
	var rvnAnimName = [];
	for(var i in rvnAnim){
		rvnAnimName[rvnAnimName.length] = i;
	}
	
	//zwraca wybraną animację
	function rvnGetAnimName(name){
		var result;
		if (name instanceof Array){
			result = name[Math.floor(Math.random()*name.length)];
		} else if (name == 'random') {
			result = rvnAnimName[Math.floor(Math.random()*rvnAnimName.length)];
		} else {
			result = name;
		}
		
		if (rvnAnimPack[result] instanceof Array) {
			return rvnGetAnimName( rvnAnimPack[result] );
		}
		else
		{
			return result;
		}
	}
	
	function rvnSetLink(gallery, li){
		gallery.find('li').removeClass( 'rvnVisibleLink' );
		li.addClass('rvnVisibleLink');
	}
	
	function rvnSetCaption(gallery, caption){
		$('.rvnCaption', gallery).css({display: (caption!=''&&caption!=null)?'block':'none'}).html(caption);
	}
	
	function rvnGalleryRunning(gallery){
		return $('.rvnSlice:animated', gallery).length>0;
	}
	
	function rvnSetImage(gallery, index, leftMov, topMov){
		var settings = gallery.data(RVN_DATA_KEY);
		$('.rvnImg', gallery).css({
			backgroundImage: settings.images[index].src,
			'background-position': leftMov+'px '+topMov+'px'
		});
	}
	
	function prepareVertical(gallery, index, leftMov, topMov){
		var settings = gallery.data(RVN_DATA_KEY);
		var i = 0;
		var s_width = Math.round(settings.width/settings.verticalSlices);
		$('.rvnSlice.vertical').each(function(){
			$(this).css({
				backgroundImage: settings.images[index].src,
				backgroundPosition: (leftMov-s_width*i)+'px '+topMov+'px'
			});
			i++;
		});
	}
	
	function prepareHorizontal(gallery, index, leftMov, topMov){
		var settings = gallery.data(RVN_DATA_KEY);
		var i = 0;
		var s_height = Math.round(settings.height/settings.horizontalSlices);
		$('.rvnSlice.horizontal', gallery).each(function(){
			$(this).css({
				backgroundImage: settings.images[index].src,
				backgroundPosition: leftMov+'px '+(topMov-s_height*i)+'px'
			});
			i++;
		});
	}
	
	function prepareAllArea(gallery, index, leftMov, topMov){
		var settings = gallery.data(RVN_DATA_KEY);
		$('.rvnSlice.allArea', gallery).css({
			backgroundImage: settings.images[index].src,
			'background-position': leftMov+'px '+topMov+'px'
		});
	}
	
	function prepareBox(gallery, index, leftMov, topMov){
		var settings = gallery.data(RVN_DATA_KEY);
		var s_height = Math.round(settings.height/settings.boxRows);
		var s_width = Math.round(settings.width/settings.boxCols);
		var row = 0;
		var col = 0;
		$('.rvnSlice.box', gallery).each(function(){
			$(this).css({
				backgroundImage: settings.images[index].src,
				backgroundPosition: (leftMov-(s_width*col))+'px '+(topMov-(s_height*row))+'px'
			});
			col++;
			if (col >= settings.boxCols)
			{
				row++;
				col=0;
			}
		});
	}
	
	//animuj!
	function rvnAnimate(gallery, n, auto){
		//zabezpieczenie dla ie - jeśli animacja nie zdążyła się skończyć a idzie następna, to zatrzymaj aktualną
		if (auto) $('.rvnSlice', gallery).stop();
		
		//jeśli już animuje to zakończ funkcję
		if (rvnGalleryRunning(gallery)) return;
	
		var settings = gallery.data(RVN_DATA_KEY);
		
		//pobranie indexu
		var index;
		if ((typeof(n) === 'undefined') || (n == 'next'))
			index = settings.index + 1;
		else if (n == 'prev')
			index = settings.index - 1;
		else
			index = n;
			
		//poprawienie wartości indexu jeśli nie mieści się w przedziale
		if (index >= settings.images.length)
			index = 0;
		else if (index < 0)
			index = settings.images.length - 1;
		
		//jeśli ten sam obrazek (dla galerii składającej się z jednego) to zakończ
		if (index == settings.index) return;
		
		clearTimeout(settings.timer);
		
		//ustawienie aktywnego obrazka w tle
		var leftMov = Math.round((settings.width-settings.images[settings.index].width)/2);
		var topMov = Math.round((settings.height-settings.images[settings.index].height)/2);
		rvnSetImage(gallery, settings.index, leftMov, topMov);
		
		//przesunięcia dla aktualnego
		leftMov = Math.round((settings.width-settings.images[index].width)/2);
		topMov = Math.round((settings.height-settings.images[index].height)/2);
		
		//wyzerowanie właściwości pasków
		var slices = $('.rvnSlice', gallery);
		slices.css({
			width: 0,
			height: 0,
			left: 'auto',
			top: 'auto',
			right: 'auto',
			bottom: 'auto',
			opacity: 1
		});
		
		/* usunięcie widoczności dla nadrzędnych */
		slices.parent().css('display', 'none');
		
		//ustawienie/usunięcie odsyłacza
		rvnSetLink(gallery, settings.images[index].li);

		//podpis obrazka
		rvnSetCaption(gallery, settings.images[index].alt);
		
		//przygotowanie pasków pionowych
		prepareVertical(gallery, index, leftMov, topMov);
			
		//przygotowanie pasków poziomych
		prepareHorizontal(gallery, index, leftMov, topMov);
		
		//przygotowanie dużego prostokąta
		prepareAllArea(gallery, index, leftMov, topMov);
		
		//przygotowanie prostokącików
		prepareBox(gallery, index, leftMov, topMov);
			
		var s = {
			animSpeed: settings.animSpeed,
			sliceDelay: settings.sliceDelay,
			verticalSlices: settings.verticalSlices,
			horizontalSlices: settings.horizontalSlices,
			boxCols: settings.boxCols,
			boxRows: settings.boxRows
		}
		var animName = rvnGetAnimName(settings.effect);
		var animTime = rvnAnim[animName](slices, s);
		
		//automatyczny pokaz slajdów
		if (settings.slider)
			settings.timer = setTimeout(function(){rvnAnimate(gallery, 'next', true);}, animTime+settings.delay );
		
		settings.index = index;
		gallery.data(RVN_DATA_KEY, settings);
	}
	
	//tworzenie pasków poziomych
	function rvnCreateVerticalLayout(gallery){
		var settings = gallery.data(RVN_DATA_KEY);
		var s_width = Math.round(settings.width/settings.verticalSlices);
		for (i=0; i<settings.verticalSlices; i++){
			if (i < settings.verticalSlices - 1){
				gallery.append('<div class="rvnVerticalLayout"><div class="rvnSlice vertical"></div></div>').
				children('.rvnVerticalLayout:last').css({
					left: s_width*i+'px',
					width: s_width,
					height: settings.height
				});
			} else {
				gallery.append('<div class="rvnVerticalLayout"><div class="rvnSlice vertical"></div></div>').
				children('.rvnVerticalLayout:last').css({
					left: s_width*i+'px',
					width: settings.width - s_width*i,
					height: settings.height
				});
			}
		}
	}
	
	//tworzenie pasków pionowych
	function rvnCreateHorizontalLayout(gallery){
		var settings = gallery.data(RVN_DATA_KEY);
		var s_height = Math.round(settings.height/settings.horizontalSlices);
		for (i=0; i<settings.horizontalSlices; i++){
			if (i < settings.horizontalSlices - 1){
				gallery.append('<div class="rvnHorizontalLayout"><div class="rvnSlice horizontal"></div></div>').
				children('.rvnHorizontalLayout:last').css({
					top: s_height*i+'px',
					height: s_height,
					width: settings.width
				});
			} else {
				gallery.append('<div class="rvnHorizontalLayout"><div class="rvnSlice horizontal"></div></div>').
				children('.rvnHorizontalLayout:last').css({
					top: s_height*i+'px',
					height: settings.height - s_height*i,
					width: settings.width
				});
			}
		}
	}
	
	//tworzenie prostokąta na całym obszarze
	function rvnCreateAllAreaLayout(gallery){
		gallery.append('<div class="rvnAllAreaLayout"><div class="rvnSlice allArea"></div></div>');
	}
	
	//tworzenie małych prostokątów
	function rvnCreateBoxLayout(gallery){
		var settings = gallery.data(RVN_DATA_KEY);
		var s_height = Math.round(settings.height/settings.boxRows);
		var s_width = Math.round(settings.width/settings.boxCols);
		for (var row=0; row<settings.boxRows; row++){
			for (var col=0; col<settings.boxCols; col++){
				gallery.append('<div class="rvnBoxLayout"><div class="rvnSlice box"></div></div>').
				children('.rvnBoxLayout:last').css({
					left: s_width*col + 'px',
					top: s_height*row + 'px',
					width:  ((col<settings.boxCols-1)?s_width:settings.width-s_width*col) + 'px',
					height: ((row<settings.boxRows-1)?s_height:settings.height-s_height*row) + 'px'
				}).children().attr('data-rvn-col', col).attr('data-rvn-row', row);
			}
		}
	}
	
	//inicjowanie galerii (funkcja wywoływana z funkcji wtyczki)
	function rvnGalleryInit(gallery){
		//pobranie ustawień
		var settings = gallery.data(RVN_DATA_KEY);
		settings.width = gallery.width();
		settings.height = gallery.height();
		
		//pobranie informacji o obrazkach (wysokość, szerokość, tytuł, opis)
		var images = [];
		gallery.children('li').each(function(){
			var image = {};
			
			var child = $(this);
			var imageEl = $('img', child);
			image['href'] = $('a', child).attr('href');
			image['src'] = 'url(' + imageEl.attr('src') + ')';
			image['width'] = imageEl.width();
			image['height'] = imageEl.height();
			image['alt'] = $('p', child).html();	
			image['li'] = child;
			
			images[images.length] = image;
		});
		
		//wygenerowanie pasków i prostokątów
		rvnCreateVerticalLayout(gallery);
		rvnCreateHorizontalLayout(gallery);
		rvnCreateAllAreaLayout(gallery);
		rvnCreateBoxLayout(gallery);
		
		//ustawienie wartości do zapisania
		settings.index = 0;
		settings.images = images;
		settings.running = false;
		settings.timer = false;
		
		//warstwa odpowiadająca za obrazek znajdujący się pod paskami
		gallery.prepend('<div class="rvnImg"></div>');
		
		//dodanie przycisków nawigacyjnych
		if (settings.navButtons){
			gallery.append('<div class="btnPrev"></div>');
			gallery.append('<div class="btnNext"></div>');
			var btnTop = Math.round((settings.height - $('.btnPrev', gallery).height())/2);
			$('.btnPrev, .btnNext', gallery).css({top: btnTop});
			gallery.hover(
				function(){
					$('.btnPrev, .btnNext', gallery).css({visibility: 'visible'});
				}, function(){
					$('.btnPrev, .btnNext', gallery).css({visibility: 'hidden'});
				}
			);
			$('.btnPrev', gallery).click(function(){ rvnAnimate(gallery, 'prev'); });
			$('.btnNext', gallery).click(function(){ rvnAnimate(gallery, 'next'); });
		}
		
		//warstwa odpowiedzialna za wyświetlanie podpisów obrazków
		gallery.append('<div class="rvnCaption"></div>');
		$('.rvnCaption', gallery).css({opacity: settings.captionOpacity});
		
		//ustawienie koloru i obrazka
		$('.rvnImg', gallery).css({backgroundColor: settings.backgroundColor});
		$('.rvnSlice', gallery).css({backgroundColor: settings.backgroundColor});
		rvnSetImage(gallery, 0);
		
		//ustawienie/usunięcie odsyłacza
		rvnSetLink(gallery, settings.images[settings.index].li);
		//podpis obrazka
		rvnSetCaption(gallery, settings.images[settings.index].alt);
		
		gallery.data(RVN_DATA_KEY, settings);
		
		if (settings.slider)
			settings.timer = setTimeout(function(){rvnAnimate(gallery);}, settings.delay);
			
		gallery.data(RVN_DATA_KEY, settings);
	}
	
	//inicjowanie galerii (funkcja wtyczka do jQuery)
	$.fn.rvnGallery = function(options){
		return this.each(function(){
			var gallery = $(this);
			gallery.data(RVN_DATA_KEY, $.extend({}, rvnGalleryDefaults, options));
			rvnGalleryInit(gallery);
		});
	}
	
	$.fn.rvnGalleryNext = function(){
		return this.rvnGalleryGoTo('next');
	}
	
	$.fn.rvnGalleryPrev = function(){
		return this.rvnGalleryGoTo('prev');
	}
	
	$.fn.rvnGalleryGoTo = function(index){
		return this.each(function(){
			rvnAnimate( $(this), index );
		});
	}
	
	//odwraca kolejność elementów tablicy
	$.fn.reverse = Array.prototype.reverse;
	
})(jQuery);