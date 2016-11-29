/**
  * jQuery Maxlength plugin 1.0.2
  *
  * http://www.anon-design.se
  *
  * Copyright (c) 2008 Emil Stjerneman <emil@anon-design.se>
  * 
  * Dual licensed under the MIT and GPL licenses:
  * http://www.opensource.org/licenses/mit-license.php
  * http://www.gnu.org/licenses/gpl.html
  */

(function(A){A.fn.maxlength=function(B){var C=jQuery.extend({events:[],maxCharacters:10,status:true,statusClass:"status",statusText:"character left",notificationClass:"notification",showAlert:false,alertText:"You have typed too many characters."},B);A.merge(C.events,["keyup"]);return this.each(function(){var G=A(this);var J=A(this).val().length;if(!F()){return false}A.each(C.events,function(K,L){G.bind(L,function(M){J=G.val().length;E()})});if(C.status){G.after(A("<div/>").addClass(C.statusClass).html("-"));D()}if(!C.status){var I=G.next("div");if(I){I.remove()}}function E(){var K=true;if(J>=C.maxCharacters){K=false;G.addClass(C.notificationClass);G.val(G.val().substr(0,C.maxCharacters));H()}else{if(G.hasClass(C.notificationClass)){G.removeClass(C.notificationClass)}}if(C.status){D()}}function D(){var K=C.maxCharacters-J;if(K<0){K=0}G.next("div").html(K+" "+C.statusText)}function H(){if(C.showAlert){alert(C.alertText)}}function F(){var K=false;if(G.is("textarea")){K=true}else{if(G.filter("input[type=text]")){K=true}else{if(G.filter("input[type=password]")){K=true}}}return K}})}})(jQuery);