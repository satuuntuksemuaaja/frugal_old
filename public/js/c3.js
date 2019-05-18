/*
		 * New AJAX Handler written for GET and POST methods
		 * Copyright 2012 Core 3 Networks, Inc.
		 * If you steal this, and you probably will because it's awesome, just mention @core3net or like me on facebook www.facebook.com/core3net
		 * I say this, because it's my first real Javascript routine and I'm proud of it :)
		 *
		 * Get Example: <a id='3' href='/get/' class='get'>
		 *
		 *
		 * POST Example: <a id='{formname}' class='post'>
		 *  This will post the form with id formname
		 *
		 *  The result is going to be the bulk of this. Should return the following statuses
		 *
		 *  status: [success, error]
		 *  gtitle: [growltitle] (if null no growl)
		 *  gbody : [growlbody]
		 *  one   : [one line status text by default this will be gbody]
		 *  action :
		 *  			reload : url
		 *  			fade : null // Just fade the modal out, and reset the form if post.
		 *  			prepend : element (which element are we prepending to), content (content to prepend w/ animation)
		 *  			default : error, do nothing but show status message and re-enable submit button
		 *
		 * 	modal : modalid if it's a modal.
		 * 	button : need the button class to re-enable if we need to.
		 *  form : need the form id to manipulate on callback
		 *  hide (opt) - Hide an element on callback?
		 */


function notify()
	{
		$.ajax({url: '/notify', datatype: 'json', success: function (data)
			{
			$('.count-n').html("<b>"+data.count+"</b>");
			$('#notifyContent').html(data.dropdata);
			if (data.alerts)
					{
						jQuery.each(data.alerts, function(id, alert)
						{
							console.log(alert.title);
								$.pnotify({
									type: 'info',
									title: alert.title,
									text: alert.text
								});
						})
					}
				// Do count
				//.count-n is the number.
			}});


	}


jQuery(document).ready(function ($) {


function c3Responder(data)
			{

              switch (data.action)
              {
                case 'reloadmodal' :
                    $(data.element).load(data.url);
                    $(data.button).html(data.oldval);
                    $(data.button).removeAttr('disabled');

                break;
                   case 'alert' :
                               $(data.button).html(data.oldval);
                               $(data.button).removeAttr('disabled');
                                 alert(data.message);
                   break;
                   case 'reload' :
                                    if (data.modal)
                                    	$(data.modal).modal('hide');
                                    	window.location.assign(data.url);
                   break;
                   case 'selfreload' :
                	   					window.location.reload();
                	   					break;

                   case 'inline' : $(data.element).html(data.content);
				                   if (data.modal && !data.nofade)
				                   	$(data.modal).modal('hide');
                   break;

                   case 'fade' :

                	   				$(data.modal).find('.modal-body').html(data.oldcontent);
                	   				$(data.button).html(data.oldval);
                                    $(data.button).removeAttr('disabled');
	                                if (data.form)
	                                	$(data.form)[0].reset();
	                                if (data.modal)
                   						$(data.modal).modal('hide');
	               break;
	               /**
	                * Chain Method into another Ajax Call that updates a status
	                * before beginning.
	                *
	                * Input: url, statusElement, statusMessage
	                *
	                */
                   case 'chain' :
                	   				$(data.statusElement).html(data.statusMessage);

                	   				$.ajax({url: data.url, datatype: 'json', success: function (datax)
   	    							{
                	   					$(datax.statusElement).html(datax.statusMessage);
                	   					console.log(datax.statusMessage);
                	   					c3Responder(datax);


   	    							}});

                   break;
                   case 'fadesource' : // This fades the source element calling the action. Like a delete button that you don't want pressed again.
                	   				$(data.button).fadeOut('slow');
	              break;

                   case 'reassign' : $(data.button).html(data.message);
                   					$(data.button).fadeIn('slow');
                                    $(data.button).removeAttr('disabled');


                   break;

                   case 'prepend' :
                      $(data.content).hide().prependTo(data.element).slideDown('slow');

                   break;

                   case 'append' :   if (data.modal)
										{
                	   						$(data.button).html(data.oldval);
                	   						$(data.button).removeAttr('disabled');
                	   						$(data.modal).find('.modal-body').html(data.oldcontent);
						   	   				$(data.modal).modal('hide');
										}
						              $(data.content).hide().appendTo(data.element).slideDown('slow');
                      break;

                   case 'areload' :	// Ajax reload - from a callback send output of target to targetted element.
                	   if (data.modal)
                	   {
                		   $(data.button).html(data.oldval);
                		   $(data.button).removeAttr('disabled');
                		   $(data.modal).find('.modal-body').html(data.oldcontent);
                		   $(data.modal).modal('hide');
                	   }

                	  target = $(data.target);
       				  url = data.url;
       				  $(target).html("<center><br/><br/><br/><br/><i class='fa fa-stack-2x fa-spinner fa-spin'></i><br/><br/><br/><br/></center>");
       				  $(target).load(url);
                	  if (data.finished)
                		  $(data.button).html(data.finished);
       				  break;


                   default :
                	   	var fid = '.' + data.fid + '_msg';
               if (data.modal)
					   {
     						$(data.button).html(data.oldval);
     						$(data.button).removeAttr('disabled');
     						$(data.button).fadeIn();
     						$(data.modal).find('.modal-body').html(data.oldcontent);
					   }
							if (data.status == 'error')
								data.status = 'danger';

                 $(fid).html("<div class='alert alert-" + data.status + "'><i class='fa fa-exclamation'></i> <strong>"+data.gtitle+"</strong> "+data.gbody+"</div>");
                 $(data.button).html(data.oldval);
                 $(data.button).removeAttr('disabled');
    					   $(data.button).fadeIn();
             break;
              } // switch

	            if (data.hide)
	                $(data.hide).hide();

	            if (data.ev)
	            {
	             eval(data.ev);
            	}


			} //c3responder


			$('.content-wrapper').on('click', '.get', function(event) {
					var xid = $(this).attr('id');
					var href = $(this).attr('href');
					var button = $(this);
          var oldval = $(this).html();
					event.preventDefault();
					var stext = button.attr('data-title');
					if (stext == null)
						stext = 'Saving..';
					$(button).html('<i class="fa fa-clock-o"></i> ' + stext);
				$.ajax({url: href, datatype: 'json', success: function (data)
    	    	{
					     data.button = button;
              data.oldval = oldval;

    	        	c3Responder(data);

				}}); // success
			}); // lq


			$('.content-wrapper').on('click', '.post', function(event)
					{
						event.preventDefault();
						var button = $(this);
						var postvar = button.attr('rel');
						var stext = button.attr('data-title');
						var form = $(button.attr('data-content'));
						var id = form.attr('id');
						var action = form.attr('action');
						if (stext == null)
							stext = 'Saving..';
						var bid = button.attr('id');
						$(button).attr('disabled', 'disabled');
				    	var oldval = $(button).html();
						$(button).html('<i class="fa fa-clock-o"></i> ' + stext);
				    	$("#" + id).append("<input type='hidden' name='" + postvar + "' value='" + bid + "'>");
						var serial = $(form).serialize();
            $('.dataTable').each(function()
            {
               serial = serial + "&" + jQuery('input', $(this).dataTable().fnGetNodes()).serialize();
            });

            $.ajax({type: 'post', url: action, data: serial, datatype: 'json', success: function (data)
			    	    {
				    		data.button = button;
			    	        data.oldval = oldval;
			    	        data.fid = id;
			    	        c3Responder(data);
			    	    }}); // success
						}); // lq

			$('.content-wrapper').on('click', '.mpost', function(event)
			{
				// For Modal forms we have to delve into the modal from the button pressed.
				event.preventDefault();
				var button = $(this);

				if (button.attr('data-content'))
				{
					var form = button.attr('data-content');
					form = $(form);
				}
				else
					var form = $(this).parent().parent().parent().find('.modal-body').find('form');
				var modal = $(this).parent().parent();
				var id = form.attr('id');
				var postvar = button.attr('rel');
				var bid = button.attr('id');
				$("#" + id).append("<input type='hidden' name='" + postvar + "' value='" + bid + "'>");
				fdata = $(form).serialize();
				var action = form.attr('action');
				var stext = button.attr('data-title');
				if (stext == null)
					stext = 'Saving..';
				$(button).attr('disabled', 'disabled');
		    	var oldval = $(button).html();
		    	var oldcontent = $(modal).find('.modal-body').html();
		    	$(modal).find('.modal-body').html("<br/><br/><div class='well'><center><img src='/assets/img/loadsmall.gif'> " + stext + "</center>");
				$(button).html('<i class="fa fa-clock-o"></i> ' + stext);

				$.ajax({type: 'post', url: action, data: fdata, datatype: 'json', success: function (data)
	    	    {
		    		    data.modal = modal;
	    	        data.button = button;
	    	        data.oldval = oldval;
	    	        data.oldcontent = oldcontent;
	    	        data.fid = id;
	    	        c3Responder(data);
	    	    }}); // success
				}); // lq


			$('.content-wrapper').on('click', '.mget', function(event)
					{
						// For Modal forms we have to delve into the modal from the button pressed.
						event.preventDefault();
						var button = $(this);
						var modal = $(this).parent().parent();
						var xid = $(button).attr('id');
						var url = $(button).attr('href');
						if (xid)
							var xurl = url + xid + "/";
						else
							var xurl = url;

						$(button).attr('disabled', 'disabled');

						var stext = button.attr('data-title');
						if (stext == null)
							stext = 'Saving..';

				    	var oldval = $(button).html();
						$(button).html('<i class="fa fa-clock-o"></i> ' + stext);
						$.ajax({url: xurl, data: {}, datatype: 'json', success: function (data)
			    	    {
			    	        data.modal = modal;
			    	        data.button = button;
			    	        data.oldval = oldval;
			    	        c3Responder(data);
			    	    }}); // success
						}); // lq

			// Create confirm with

			$(".content-wrapper").on('click', '.mjax', function(e)
			{
        e.preventDefault();
				target = $(this).attr('data-target');
				url = 	$(this).attr('href');
				$(target).html("<center><br/><br/><br/><br/><i class='fa fa-stack-2x fa-spinner fa-spin'></i><br/><br/><br/><br/></center>");
				$(target).load(url);
				$(target).modal({show: true , backdrop : true , keyboard: true});
			});

			$(".content-wrapper").on('click', '.pjax', function(e)
					{
						e.preventDefault();
						var url = $(this).attr('data-target');
						var target = $(this).attr('data-ride');

						$(target).html("<center><br/><br/><br/><br/><i class='fa fa-stack-2x fa-spinner fa-spin'></i><br/><br/><br/><br/></center>");
						$(target).load(url);
					});
 $('[rel="popover"]').popover().click(function(e) {
            $(this).popover('toggle');
        });

$('.mask').inputmask();
$(".pulse-blue").pulsate({color:"#09f"});
$(".pulse-red").pulsate({color:"#d9534f"});
$('.select2').select2({width: '300px'});
$(".content-wrapper").on('click', '.editable', function(e)
{
	e.preventDefault();
	var that = $(this);
	that.editable({
			ajaxOptions : {
				type: 'POST',
				dataType: 'json'
			},
			send: 'always'});
});

});
