/*
 * Flexigrid for jQuery - New Wave Grid
 *
 * Copyright (c) 2008 Paulo P. Marinas (webplicity.net/flexigrid)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * $Date: 2008-07-14 00:09:43 +0800 (Tue, 14 Jul 2008) $
 */

(function($){

	$.addFlex = function(t,p)
	{

		if (t.grid) return false; //return if already exist

		// apply default properties
		p = $.extend({
			 height: 200, //default height
			 width: 'auto', //auto width
			 striped: true, //apply odd even stripes
			 novstripe: false,
			 minwidth: 30, //min width of columns
			 minheight: 80, //min height of columns
//			 resizable: false, //resizable table
			 url: false, //ajax url
			 method: 'POST', // data sending method
			 dataType: 'xml', // type of data loaded
			 errormsg: 'Connection Error',
			 usepager: false, //
			 nowrap: true, //
			 page: 1, //current page
			 total: 1, //total pages
			 useRp: true, //use the results per page select box
			 rp: 15, // results per page
			 rpOptions: [10,15,20,25,40],
			 title: false,
			 pagestat: 'Displaying {from} to {to} of {total} items',
			 procmsg: 'Processing, please wait ...',
			 query: '',
			 qtype: '',
			 nomsg: 'No items',
			 minColToggle: 1, //minimum allowed column to be hidden
			 showToggleBtn: true, //show or hide column toggle popup
			 hideOnSubmit: true,
			 autoload: true,
			 blockOpacity: 0.5,
			 onToggleCol: false,
			 onChangeSort: false,
			 onSuccess: false,
			 onSubmit: false // using a custom populate function
		  }, p);


		$(t)
		.show() //show if hidden
		.attr({cellPadding: 0, cellSpacing: 0, border: 0})  //remove padding and spacing
		.removeAttr('width') //remove width properties
		;



		//create grid class
		var g = {
			hset : {},
			fixHeight: function (newH) {
					newH = false;
					if (!newH) newH = $(g.bDiv).height();
					var hdHeight = $(this.hDiv).height();

					var nd = parseInt($(g.nDiv).height());

					if (nd>newH)
						$(g.nDiv).height(newH).width(200);
					else
						$(g.nDiv).height('auto').width('auto');

					$(g.block).css({height:newH,marginBottom:(newH * -1)});

					var hrH = g.bDiv.offsetTop + newH;
					$(g.rDiv).css({height: hrH});

			},
			scroll: function() {
					this.hDiv.scrollLeft = this.bDiv.scrollLeft;
			},
			addData: function (data) { //parse data

				if (p.preProcess)
					data = p.preProcess(data);

				$('.pReload',this.pDiv).removeClass('loading');
				this.loading = false;

				if (!data)
					{
					$('.pPageStat',this.pDiv).html(p.errormsg);
					return false;
					}

				if (p.dataType=='xml')
					p.total = +$('rows total',data).text();
				else
					p.total = data.total;

				if (p.total==0)
					{
					$('tr, a, td, div',t).unbind();
					$(t).empty();
					p.pages = 1;
					p.page = 1;
					this.buildpager();
					$('.pPageStat',this.pDiv).html(p.nomsg);
					return false;
					}

				p.pages = Math.ceil(p.total/p.rp);

				if (p.dataType=='xml')
					p.page = +$('rows page',data).text();
				else
					p.page = data.page;

				this.buildpager();

				//build new body
				var tbody = document.createElement('tbody');

				if (p.dataType=='json')
				{
					$.each
					(
					 data.rows,
					 function(i,row)
					 	{
							var tr = document.createElement('tr');
							if (i % 2 && p.striped) tr.className = 'erow';

							if (row.id) tr.id = 'row' + row.id;

							//add cell
							$('thead tr:first th',g.hDiv).each
							(
							 	function ()
									{

										var td = document.createElement('td');
										var idx = $(this).attr('axis').substr(3);
										td.align = this.align;
										td.innerHTML = row.cell[idx];
										$(tr).append(td);
										td = null;
									}
							);


							if ($('thead',this.gDiv).length<1) //handle if grid has no headers
							{

									for (idx=0;idx<cell.length;idx++)
										{
										var td = document.createElement('td');
										td.innerHTML = row.cell[idx];
										$(tr).append(td);
										td = null;
										}
							}

							$(tbody).append(tr);
							tr = null;
						}
					);

				} else if (p.dataType=='xml') {

				i = 1;

				$("rows row",data).each
				(

				 	function ()
						{

							i++;

							var tr = document.createElement('tr');
							if (i % 2 && p.striped) tr.className = 'erow';

							var nid =$(this).attr('id');
							if (nid) tr.id = 'row' + nid;

							nid = null;

							var robj = this;



							$('thead tr:first th',g.hDiv).each
							(
							 	function ()
									{

										var td = document.createElement('td');
										var idx = $(this).attr('axis').substr(3);
										td.align = this.align;
										td.innerHTML = $("cell:eq("+ idx +")",robj).text();
										$(tr).append(td);
										td = null;
									}
							);


							if ($('thead',this.gDiv).length<1) //handle if grid has no headers
							{
								$('cell',this).each
								(
								 	function ()
										{
										var td = document.createElement('td');
										td.innerHTML = $(this).text();
										$(tr).append(td);
										td = null;
										}
								);
							}

							$(tbody).append(tr);
							tr = null;
							robj = null;
						}
				);

				}

				$('tr',t).unbind();
				$(t).empty();

				$(t).append(tbody);
				this.addCellProp();
				this.addRowProp();

				//this.fixHeight($(this.bDiv).height());

				tbody = null; data = null; i = null;

				if (p.onSuccess) p.onSuccess();
				if (p.hideOnSubmit) $(g.block).remove();//$(t).show();

				this.hDiv.scrollLeft = this.bDiv.scrollLeft;
				if ($.browser.opera) $(t).css('visibility','visible');

			},
			changeSort: function(th) { //change sortorder

				if (this.loading) return true;

				$(g.nDiv).hide();$(g.nBtn).hide();

				if (p.sortname == $(th).attr('abbr'))
					{
						if (p.sortorder=='asc') p.sortorder = 'desc';
						else p.sortorder = 'asc';
					}

				$(th).addClass('sorted').siblings().removeClass('sorted');
				$('.sdesc',this.hDiv).removeClass('sdesc');
				$('.sasc',this.hDiv).removeClass('sasc');
				$('div',th).addClass('s'+p.sortorder);
				p.sortname= $(th).attr('abbr');

				if (p.onChangeSort)
					p.onChangeSort(p.sortname,p.sortorder);
				else
					this.populate();

			},
			buildpager: function(){ //rebuild pager based on new properties

			$('.pcontrol input',this.pDiv).val(p.page);
			$('.pcontrol span',this.pDiv).html(p.pages);

			var r1 = (p.page-1) * p.rp + 1;
			var r2 = r1 + p.rp - 1;

			if (p.total<r2) r2 = p.total;

			var stat = p.pagestat;

			stat = stat.replace(/{from}/,r1);
			stat = stat.replace(/{to}/,r2);
			stat = stat.replace(/{total}/,p.total);

			$('.pPageStat',this.pDiv).html(stat);

			},
			populate: function () { //get latest data

				if (this.loading) return true;

				if (p.onSubmit)
					{
						var gh = p.onSubmit();
						if (!gh) return false;
					}

				this.loading = true;
				if (!p.url) return false;

				$('.pPageStat',this.pDiv).html(p.procmsg);

				$('.pReload',this.pDiv).addClass('loading');

				$(g.block).css({top:g.bDiv.offsetTop});

				if (p.hideOnSubmit) $(this.gDiv).prepend(g.block); //$(t).hide();

				if ($.browser.opera) $(t).css('visibility','hidden');

				if (!p.newp) p.newp = 1;

				if (p.page>p.pages) p.page = p.pages;
				//var param = {page:p.newp, rp: p.rp, sortname: p.sortname, sortorder: p.sortorder, query: p.query, qtype: p.qtype};
				var param = [
					 { name : 'page', value : p.newp }
					,{ name : 'rp', value : p.rp }
					,{ name : 'sortname', value : p.sortname}
					,{ name : 'sortorder', value : p.sortorder }
					,{ name : 'query', value : p.query}
					,{ name : 'qtype', value : p.qtype}
				];

				if (p.params)
					{
						for (var pi = 0; pi < p.params.length; pi++) param[param.length] = p.params[pi];
					}

					$.ajax({
					   type: p.method,
					   url: p.url,
					   data: param,
					   dataType: p.dataType,
					   success: function(data){g.addData(data);},
					   error: function(data) { try { if (p.onError) p.onError(data); } catch (e) {} }
					 });
			},
			doSearch: function () {
				p.query = $('input[name=q]',g.sDiv).val();
				p.qtype = $('select[name=qtype]',g.sDiv).val();
				p.newp = 1;

				this.populate();
			},
			changePage: function (ctype){ //change page

				if (this.loading) return true;

				switch(ctype)
				{
					case 'first': p.newp = 1; break;
					case 'prev': if (p.page>1) p.newp = parseInt(p.page) - 1; break;
					case 'next': if (p.page<p.pages) p.newp = parseInt(p.page) + 1; break;
					case 'last': p.newp = p.pages; break;
					case 'input':
							var nv = parseInt($('.pcontrol input',this.pDiv).val());
							if (isNaN(nv)) nv = 1;
							if (nv<1) nv = 1;
							else if (nv > p.pages) nv = p.pages;
							$('.pcontrol input',this.pDiv).val(nv);
							p.newp =nv;
							break;
				}

				if (p.newp==p.page) return false;

				if (p.onChangePage)
					p.onChangePage(p.newp);
				else
					this.populate();

			},
			addCellProp: function ()
			{

					$('tbody tr td',g.bDiv).each
					(
						function ()
							{
									var tdDiv = document.createElement('div');
									var n = $('td',$(this).parent()).index(this);
									var pth = $('th:eq('+n+')',g.hDiv).get(0);

									if (pth!=null)
									{
									if (p.sortname==$(pth).attr('abbr')&&p.sortname)
										{
										this.className = 'sorted';
										}
									 $(tdDiv).css({textAlign:pth.align,width: $('div:first',pth)[0].style.width});

									 if (pth.hide) $(this).css('display','none');

									 }

									 if (p.nowrap==false) $(tdDiv).css('white-space','normal');

									 if (this.innerHTML=='') this.innerHTML = '&nbsp;';

									 //tdDiv.value = this.innerHTML; //store preprocess value
									 tdDiv.innerHTML = this.innerHTML;

									 var prnt = $(this).parent()[0];
									 var pid = false;
									 if (prnt.id) pid = prnt.id.substr(3);

									 if (pth!=null)
									 {
									 if (pth.process) pth.process(tdDiv,pid);
									 }

									$(this).empty().append(tdDiv).removeAttr('width'); //wrap content


									//add editable event here 'dblclick'

							}
					);

			},
			getCellDim: function (obj) // get cell prop for editable event
			{
				var ht = parseInt($(obj).height());
				var pht = parseInt($(obj).parent().height());
				var wt = parseInt(obj.style.width);
				var pwt = parseInt($(obj).parent().width());
				var top = obj.offsetParent.offsetTop;
				var left = obj.offsetParent.offsetLeft;
				var pdl = parseInt($(obj).css('paddingLeft'));
				var pdt = parseInt($(obj).css('paddingTop'));
				return {ht:ht,wt:wt,top:top,left:left,pdl:pdl, pdt:pdt, pht:pht, pwt: pwt};
			},
			addRowProp: function()
			{
					$('tbody tr',g.bDiv).each
					(
						function ()
							{
							$(this)
							.click(
								function (e)
									{
										var obj = (e.target || e.srcElement); if (obj.href || obj.type) return true;
										$(this).toggleClass('trSelected');
										if (p.singleSelect) $(this).siblings().removeClass('trSelected');
									}
							)
							.mousedown(
								function (e)
									{
										if (e.shiftKey)
										{
										$(this).toggleClass('trSelected');
										g.multisel = true;
										this.focus();
										$(g.gDiv).noSelect();
										}
									}
							)
							.mouseup(
								function ()
									{
										if (g.multisel)
										{
										g.multisel = false;
										$(g.gDiv).noSelect(false);
										}
									}
							)
							.hover(
								function (e)
									{
									if (g.multisel)
										{
										$(this).toggleClass('trSelected');
										}
									},
								function () {}
							)
							;

							if ($.browser.msie&&$.browser.version<7.0)
								{
									$(this)
									.hover(
										function () { $(this).addClass('trOver'); },
										function () { $(this).removeClass('trOver'); }
									)
									;
								}
							}
					);


			},
			pager: 0
			};

		//create model if any
		if (p.colModel)
		{
			thead = document.createElement('thead');
			tr = document.createElement('tr');

			for (i=0;i<p.colModel.length;i++)
				{
					var cm = p.colModel[i];
					var th = document.createElement('th');

					th.innerHTML = cm.display;

					if (cm.name&&cm.sortable)
						$(th).attr('abbr',cm.name);

					//th.idx = i;
					$(th).attr('axis','col'+i);

					if (cm.align)
						th.align = cm.align;

					if (cm.width)

						$(th).attr('width',cm.width);



					if (cm.hide)
						{
						th.hide = true;
						}

					if (cm.process)
						{
							th.process = cm.process;
						}

					$(tr).append(th);
				}
			$(thead).append(tr);
			$(t).prepend(thead);
		} // end if p.colmodel

		//init divs
		g.gDiv = document.createElement('div'); //create global container
		g.mDiv = document.createElement('div'); //create title container
		g.hDiv = document.createElement('div'); //create header container
		g.bDiv = document.createElement('div'); //create body container
		g.vDiv = document.createElement('div'); //create grip
		g.rDiv = document.createElement('div'); //create horizontal resizer
		g.block = document.createElement('div'); //creat blocker
		g.nDiv = document.createElement('div'); //create column show/hide popup
		g.nBtn = document.createElement('div'); //create column show/hide button
		g.iDiv = document.createElement('div'); //create editable layer
		g.tDiv = document.createElement('div'); //create toolbar
		g.sDiv = document.createElement('div');

		if (p.usepager) g.pDiv = document.createElement('div'); //create pager container
		g.hTable = document.createElement('table');

		//set gDiv
		g.gDiv.className = 'flexigrid';
		//if (p.width!='auto') g.gDiv.style.width = p.width + 'px';
		if (!isNaN(p.width)) {
			g.gDiv.style.width = p.width + 'px';
		} else if (p.width == '100%') g.gDiv.style.width = p.width;









		//add conditional classes
		if ($.browser.msie)
			$(g.gDiv).addClass('ie');

		if (p.novstripe)
			$(g.gDiv).addClass('novstripe');

		$(t).before(g.gDiv);
		$(g.gDiv)
		.append(t)
		;

		//set toolbar
		if (p.buttons)
		{
			g.tDiv.className = 'tDiv';
			var toolbarList = document.createElement('ul');
            toolbarList.className = 'toolbar';

            for (i=0;i<p.buttons.length;i++)
            {
                var btn = p.buttons[i];
                if (!btn.separator)
                {
                    var btnDiv = document.createElement('li');
                    btnDiv.innerHTML = "<a>"+btn.name+"</a>";
                    if (btn.bclass)
                        $('a',btnDiv)
                        .addClass(btn.bclass)
                        ;
                    btnDiv.onpress = btn.callback;
                    btnDiv.name = btn.name;
                    btnDiv.argonpress = btn.callbackArg;
                    btnDiv.argclass = btn.bclass;
                    if (btn.callback)
                    {
                        $(btnDiv).click
                        (
                                function ()
                                {
                                    this.onpress(this.argclass, this.argonpress);
                                }
                        );
                    }
                    $(toolbarList).append(btnDiv);
                } else {
//                    $(tDiv2).append("<div class='btnseparator'></div>");
                }
            }
            $(g.tDiv).append(toolbarList);
//            $(g.tDiv).append("<div style='clear:both'></div>");
            $(g.gDiv).prepend(g.tDiv);
		}

		//set hDiv
		g.hDiv.className = 'hDiv';

		$(t).before(g.hDiv);

		//set hTable
			g.hTable.cellPadding = 0;
			g.hTable.cellSpacing = 0;

			$(g.hDiv).append('<div class="hDivBox"></div>');
			$('div',g.hDiv).append(g.hTable);
			var thead = $("thead:first",t).get(0);
			if (thead) $(g.hTable).append(thead);
			thead = null;

		if (!p.colmodel) var ci = 0;

		//setup thead
			$('thead tr:first th',g.hDiv).each
			(
			 	function ()
					{
						var thdiv = document.createElement('div');



						if ($(this).attr('abbr'))
							{
							$(this).click(
								function (e)
									{

										if (!$(this).hasClass('thOver')) return false;
										var obj = (e.target || e.srcElement);
										if (obj.href || obj.type) return true;
										g.changeSort(this);
									}
							)
							;

							if ($(this).attr('abbr')==p.sortname)
								{
								this.className = 'sorted';
								thdiv.className = 's'+p.sortorder;
								}
							}

							if (this.hide) $(this).hide();

							if (!p.colmodel)
							{
								$(this).attr('axis','col' + ci++);
							}


						 $(thdiv).css({textAlign:this.align, width: this.width + 'px'});

						 thdiv.innerHTML = this.innerHTML;

						$(this).empty().append(thdiv).removeAttr('width')

						.hover(
							function(){
								if (!g.colresize&&!$(this).hasClass('thMove')&&!g.colCopy) $(this).addClass('thOver');

								if ($(this).attr('abbr')!=p.sortname&&!g.colCopy&&!g.colresize&&$(this).attr('abbr')) $('div',this).addClass('s'+p.sortorder);
								else if ($(this).attr('abbr')==p.sortname&&!g.colCopy&&!g.colresize&&$(this).attr('abbr'))
									{
										var no = '';
										if (p.sortorder=='asc') no = 'desc';
										else no = 'asc';
										$('div',this).removeClass('s'+p.sortorder).addClass('s'+no);
									}

								if (g.colCopy)
									{
									var n = $('th',g.hDiv).index(this);

									if (n==g.dcoln) return false;



									if (n<g.dcoln) $(this).append(g.cdropleft);
									else $(this).append(g.cdropright);

									g.dcolt = n;

									} else if (!g.colresize) {

									var nv = $('th:visible',g.hDiv).index(this);
									var nw = parseInt($(g.nBtn).width()) + parseInt($(g.nBtn).css('borderLeftWidth'));

									$(g.nDiv).hide();$(g.nBtn).hide();


									var ndw = parseInt($(g.nDiv).width());

									$(g.nDiv).css({top:g.bDiv.offsetTop});

									if ($(this).hasClass('sorted'))
										$(g.nBtn).addClass('srtd');
									else
										$(g.nBtn).removeClass('srtd');

									}

							},
							function(){
								$(this).removeClass('thOver');
								if ($(this).attr('abbr')!=p.sortname) $('div',this).removeClass('s'+p.sortorder);
								else if ($(this).attr('abbr')==p.sortname)
									{
										var no = '';
										if (p.sortorder=='asc') no = 'desc';
										else no = 'asc';

										$('div',this).addClass('s'+p.sortorder).removeClass('s'+no);
									}
								if (g.colCopy)
									{
									$(g.cdropleft).remove();
									$(g.cdropright).remove();
									g.dcolt = null;
									}
							})
						; //wrap content
					}
			);

		//set bDiv
		g.bDiv.className = 'bDiv';
		$(t).before(g.bDiv);
		$(g.bDiv)
		.css({ height: (p.height=='auto') ? 'auto' : ((parseInt(p.height, 10) == p.height) ? p.height+"px" : p.height)})
		.scroll(function (e) {g.scroll()})
		.append(t)
		;

		if (p.height == 'auto')
			{
			$('table',g.bDiv).addClass('autoht');
			}


		//add td properties
		g.addCellProp();

		//add row properties
		g.addRowProp();

		


		//add strip
		if (p.striped)
			$('tbody tr:odd',g.bDiv).addClass('erow');


//		if (p.resizable && p.height !='auto')
//		{
//		g.vDiv.className = 'vGrip';
//		$(g.vDiv)
//		.mousedown(function (e) { g.dragStart('vresize',e)})
//		.html('<span></span>');
//		$(g.bDiv).after(g.vDiv);
//		}

//		if (p.resizable && p.width !='auto' && !p.nohresize)
//		{
//		g.rDiv.className = 'hGrip';
//		$(g.rDiv)
//		.mousedown(function (e) {g.dragStart('vresize',e,true);})
//		.html('<span></span>')
//		.css('height',$(g.gDiv).height())
//		;
//		if ($.browser.msie&&$.browser.version<7.0)
//		{
//			$(g.rDiv).hover(function(){$(this).addClass('hgOver');},function(){$(this).removeClass('hgOver');});
//		}
//		$(g.gDiv).append(g.rDiv);
//		}

		// add pager
		if (p.usepager)
		{
		g.pDiv.className = 'pDiv';
		g.pDiv.innerHTML = '<div class="pDiv2"></div>';
		$(g.bDiv).after(g.pDiv);
		var html = ' <div class="pGroup"> <div class="pFirst pButton"><span></span></div><div class="pPrev pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pcontrol">Página <input type="text" size="4" value="1" /> de <span> 1 </span></span></div> <div class="btnseparator"></div> <div class="pGroup"> <div class="pNext pButton"><span></span></div><div class="pLast pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"> <div class="pReload pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pPageStat"></span></div>';
		$('div',g.pDiv).html(html);

		$('.pReload',g.pDiv).click(function(){g.populate()});
		$('.pFirst',g.pDiv).click(function(){g.changePage('first')});
		$('.pPrev',g.pDiv).click(function(){g.changePage('prev')});
		$('.pNext',g.pDiv).click(function(){g.changePage('next')});
		$('.pLast',g.pDiv).click(function(){g.changePage('last')});
		$('.pcontrol input',g.pDiv).keydown(function(e){if(e.keyCode==13) g.changePage('input')});
		if ($.browser.msie&&$.browser.version<7) $('.pButton',g.pDiv).hover(function(){$(this).addClass('pBtnOver');},function(){$(this).removeClass('pBtnOver');});

			if (p.useRp)
			{
			var opt = "";
			for (var nx=0;nx<p.rpOptions.length;nx++)
			{
				if (p.rp == p.rpOptions[nx]) sel = 'selected="selected"'; else sel = '';
				 opt += "<option value='" + p.rpOptions[nx] + "' " + sel + " >" + p.rpOptions[nx] + "&nbsp;&nbsp;</option>";
			};
			$('.pDiv2',g.pDiv).prepend("<div class='pGroup'><select name='rp'>"+opt+"</select></div> <div class='btnseparator'></div>");
			$('select',g.pDiv).change(
					function ()
					{
						if (p.onRpChange)
							p.onRpChange(+this.value);
						else
							{
							p.newp = 1;
							p.rp = +this.value;
							g.populate();
							}
					}
				);
			}

		//add search button
		if (p.searchitems)
			{
				$('.pDiv2',g.pDiv).prepend("<div class='pGroup'> <div class='pSearch pButton'><span></span></div> </div>  <div class='btnseparator'></div>");
                //$('.pSearch',g.pDiv).click(function(){$(g.sDiv).slideToggle('fast',function(){$('.sDiv:visible input:first',g.gDiv).trigger('focus');});});
                $('.pSearch',g.pDiv).click(function(){$('.flexigrid').toggleClass('withSearch'); $('.sDiv:visible input:first',g.gDiv).trigger('focus');});

				//add search box
				g.sDiv.className = 'sDiv';

				sitems = p.searchitems;

				var sopt = "";
				for (var s = 0; s < sitems.length; s++)
				{
					if (p.qtype=='' && sitems[s].isdefault==true)
					{
					p.qtype = sitems[s].name;
					sel = 'selected="selected"';
					} else sel = '';
					sopt += "<option value='" + sitems[s].name + "' " + sel + " >" + sitems[s].display + "&nbsp;&nbsp;</option>";
				}

				if (p.qtype=='') p.qtype = sitems[0].name;

				$(g.sDiv).append("<div class='sDiv2'>Procurar <input type='text' size='30' name='q' class='qsbox' /> em <select name='qtype'>"+sopt+"</select> <input type='button' value='Limpar' /></div>");

				$('input[name=q],select[name=qtype]',g.sDiv).keydown(function(e){if(e.keyCode==13) g.doSearch()});
                $('select[name=qtype]',g.sDiv).keydown(function(e){if(e.keyCode==13) g.doSearch()});

				$('input[value=Limpar]',g.sDiv).click(function(){$('input[name=q]',g.sDiv).val(''); p.query = ''; g.doSearch(); });
				$(g.bDiv).after(g.sDiv);

			}

		}
		$(g.pDiv,g.sDiv).append("<div style='clear:both'></div>");

		// add title
		if (p.title)
		{
			g.mDiv.className = 'mDiv';
			g.mDiv.innerHTML = '<div class="ftitle">'+p.title+'</div>';
			$(g.gDiv).prepend(g.mDiv);
			if (p.showTableToggleBtn)
				{
					$(g.mDiv).append('<div class="ptogtitle" title="Minimize/Maximize Table"><span></span></div>');
					$('div.ptogtitle',g.mDiv).click
					(
					 	function ()
							{
								$(g.gDiv).toggleClass('hideBody');
								$(this).toggleClass('vsble');
							}
					);
				}
			//g.rePosDrag();
		}

		//setup cdrops
		g.cdropleft = document.createElement('span');
		g.cdropleft.className = 'cdropleft';
		g.cdropright = document.createElement('span');
		g.cdropright.className = 'cdropright';

		//add block
		g.block.className = 'gBlock';
		var gh = $(g.bDiv).height();
		var gtop = g.bDiv.offsetTop;
		$(g.block).css(
		{
			width: g.bDiv.style.width,
			height: gh,
			background: 'white',
			position: 'relative',
			marginBottom: (gh * -1),
			zIndex: 1,
			top: gtop,
			left: '0px'
		}
		);
		$(g.block).fadeTo(0,p.blockOpacity);

		// add column control
		if ($('th',g.hDiv).length)
		{

			g.nDiv.className = 'nDiv';
			g.nDiv.innerHTML = "<table cellpadding='0' cellspacing='0'><tbody></tbody></table>";
			$(g.nDiv).css(
			{
				marginBottom: (gh * -1),
				display: 'none',
				top: gtop
			}
			).noSelect()
			;

			var cn = 0;


			$('th div',g.hDiv).each
			(
			 	function ()
					{
						var kcol = $("th[axis='col" + cn + "']",g.hDiv)[0];
						var chk = 'checked="checked"';
						if (kcol.style.display=='none') chk = '';

						$('tbody',g.nDiv).append('<tr><td class="ndcol1"><input type="checkbox" '+ chk +' class="togCol" value="'+ cn +'" /></td><td class="ndcol2">'+this.innerHTML+'</td></tr>');
						cn++;
					}
			);

			if ($.browser.msie&&$.browser.version<7.0)
				$('tr',g.nDiv).hover
				(
				 	function () {$(this).addClass('ndcolover');},
					function () {$(this).removeClass('ndcolover');}
				);

			$('td.ndcol2',g.nDiv).click
			(
			 	function ()
					{
						if ($('input:checked',g.nDiv).length<=p.minColToggle&&$(this).prev().find('input')[0].checked) return false;
						return g.toggleCol($(this).prev().find('input').val());
					}
			);

			$('input.togCol',g.nDiv).click
			(
			 	function ()
					{

						if ($('input:checked',g.nDiv).length<p.minColToggle&&this.checked==false) return false;
						$(this).parent().next().trigger('click');
						//return false;
					}
			);


			$(g.gDiv).prepend(g.nDiv);

			$(g.nBtn).addClass('nBtn')
			.html('<div></div>')
			.attr('title','Hide/Show Columns')
			.click
			(
			 	function ()
				{
			 	$(g.nDiv).toggle(); return true;
				}
			);

			if (p.showToggleBtn) $(g.gDiv).prepend(g.nBtn);

		}

		// add date edit layer
		$(g.iDiv)
		.addClass('iDiv')
		.css({display:'none'})
		;
		$(g.bDiv).append(g.iDiv);

		// add flexigrid events
		$(g.bDiv)
		.hover(function(){$(g.nDiv).hide();$(g.nBtn).hide();},function(){if (g.multisel) g.multisel = false;})
		;
		$(g.gDiv)
		.hover(function(){},function(){$(g.nDiv).hide();$(g.nBtn).hide();})
		;

		//browser adjustments
		if ($.browser.msie&&$.browser.version<7.0)
		{
			$('.hDiv,.bDiv,.mDiv,.pDiv,.vGrip,.tDiv, .sDiv',g.gDiv)
			.css({width: '100%'});
			$(g.gDiv).addClass('ie6');
			if (p.width!='auto') $(g.gDiv).addClass('ie6fullwidthbug');
		}

		g.fixHeight();

		//make grid functions accessible
		t.p = p;
		t.grid = g;

		// load data
		if (p.url&&p.autoload)
			{
			g.populate();
			}

		return t;

	};

	var docloaded = false;

	$(document).ready(function () {docloaded = true} );

	$.fn.flexigrid = function(p) {

		return this.each( function() {
				if (!docloaded)
				{
					$(this).hide();
					var t = this;
					$(document).ready
					(
						function ()
						{
						$.addFlex(t,p);
						}
					);
				} else {
					$.addFlex(this,p);
				}
			});

	}; //end flexigrid

	$.fn.flexReload = function(p) { // function to reload grid

		return this.each( function() {
				if (this.grid&&this.p.url) this.grid.populate();
			});

	}; //end flexReload

	$.fn.flexOptions = function(p) { //function to update general options

		return this.each( function() {
				if (this.grid) $.extend(this.p,p);
			});

	}; //end flexOptions

	$.fn.flexToggleCol = function(cid,visible) { // function to reload grid

		return this.each( function() {
				if (this.grid) this.grid.toggleCol(cid,visible);
			});

	}; //end flexToggleCol

	$.fn.flexAddData = function(data) { // function to add data to grid

		return this.each( function() {
				if (this.grid) this.grid.addData(data);
			});

	};

	$.fn.noSelect = function(p) { //no select plugin by me :-)

		if (p == null)
			prevent = true;
		else
			prevent = p;

		if (prevent) {

		return this.each(function ()
			{
				if ($.browser.msie||$.browser.safari) $(this).bind('selectstart',function(){return false;});
				else if ($.browser.mozilla)
					{
						$(this).css('MozUserSelect','none');
						$('body').trigger('focus');
					}
				else if ($.browser.opera) $(this).bind('mousedown',function(){return false;});
				else $(this).attr('unselectable','on');
			});

		} else {


		return this.each(function ()
			{
				if ($.browser.msie||$.browser.safari) $(this).unbind('selectstart');
				else if ($.browser.mozilla) $(this).css('MozUserSelect','inherit');
				else if ($.browser.opera) $(this).unbind('mousedown');
				else $(this).removeAttr('unselectable','on');
			});

		}

	}; //end noSelect

    $.fn.flexGetSelectedRows = function()
    {
        if($(this).get(0).grid) return $('tbody tr.trSelected',$(this).get(0).grid.bDiv);
    };//end flexGetSelectRows

    $.fn.deleteSelectedRows = function(){
        var obj = $(this).get(0);
        var grid = obj.grid;
        var params = obj.p;
        var colModel = params.colModel;
        var colData = params.colData;

        //Get Select Rows
        var rows = $(this).flexGetSelectedRows();

        if (rows.length > 0) {

            var ids = new Array();

            //Get Selected Row Ids
            rows.each(function(){
                ids.push(parseInt($(this).attr("id").split("row").join("")));
            });

            //Remove rows with selected ids from ColData
            var temp = new Array();
            $.each(colData.rows,function(idx){
                if(($.inArray(this.id,ids)<0)) temp.push(colData.rows[idx]);
            });
            colData.rows = temp;

            //Update total and rp props @ colData
            colData.total = colData.rows.length;
            colData.rp = colData.total;

            //Pass data to the grid API
            $.extend(params,{dataType:'json',colData: colData});

            //Reload grid
            grid.addData(params.colData);
        }
    }//end flexUnselectAll
})(jQuery);