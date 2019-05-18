<?php
namespace vl\libraries\bootstrap;

use \Carbon\Carbon;
class BS
{
    /**
     * Use Bootstrap 3 Span Tags to organize page cells. Send in array for optimizing for mobile/tablet use or
     * use integer to use the default large class.
     *
     * <code>$span = BS::span(
     *                          ['md' => 6, 'xs' => 2, 'lg' => 12],
     *                          $content
     *                       );</code>
     *
     * @param array/int $span
     * @param string $content
     * @return string
     */
    static public function span($span = null, $content = null, $offset = null)
    {
    	$class = "<div class='";

    	// Determine main span
        if (is_array($span))
        {
            foreach ($span AS $size => $s)
                $class .= "col-$size-$s ";
        }
        else
            $class .= "col-lg-$span ";

		// Determine any offset?
        if ($offset)
        {
        	if (is_array($offset))
        	{
        		foreach ($offset AS $size => $s)
        			$class .= "col-$size-offset-$s ";
        	}
        	else
        		$class .= "col-lg-offset-$offset";
        }

        $class .= "'>$content</div>";
        return $class;
    }

    /**
     * Create a bootstrap row to encapsulate spanned content
     *
     * @param string $data
     * @return string
     */
    static public function row($data = null)
    {
        return "<div class='row'>$data</div>";

    }

    /**
     * Create a bootstrap 3 progress bar.
     *
     * @param string $color
     * @param integer $perc
     * @param boolean $striped
     * @param boolean $animate
     * @return string
     */

    static public function progress($color = 'success', $perc = '50', $striped = false, $animate = false)
    {
        $s = ($striped) ? "progress-striped" : null;
        $a = ($animate) ? "active" : null;
        return "<div class='progress {$s} {$a}'>
                    <div class='progress-bar progress-bar-{$color}' style='width: {$perc}%'></div>
               </div>";
    }

    /**
     * Create a label or a badge. Set badge to true to convert to badge.
     *
     * @param string $color
     * @param string $content
     * @param string $badge
     * @return string
     */

    static public function label($color = "success", $content = null, $badge = false)
    {
       $type = ($badge) ? "badge" : "label";
       return "<span class='{$type} {$color}'>$content</span>";
    }

    static public function Buttons(Array $buttons)
    {
      $data = "<div class='big-icons-buttons clearfix margin-bottom'><div class='btn-group'>";
      foreach ($buttons AS $button)
        {
          $modal = (isset($button['modal'])) ? "data-toggle='modal'" : null;
          $target = $modal ? "#{$button['modal']}" : $button['url'];
          $class = (isset($button['class'])) ? $button['class'] : null;
          $data .= "<a {$class} href='$target' class='btn btn-sm btn-$button[color]' $modal><i class='fa fa-$button[icon]'></i>$button[text]</a>";
        }
      $data .= "</div></div>";
      return $data;
    }

    /**
     * Create an Alert. Buttons is an array. If buttons is set then a large alert box will be rendered
     * with the buttons specified. Buttons should be rendered through Button:: and injected into the
     * array.
     *
     * @param string $type
     * @param string $header
     * @param string $content
     * @param array $buttons
     */

    static public function alert($type = "success", $header = null, $content = null, $buttons = null)
    {
    	$icon = null;
    	switch ($type)
    	{
    		case 'danger' : $icon = 'fa fa-times-circle-o'; break;
    		case 'warning' : $icon =  'fa fa-exclamation'; break;
    		case 'success' : $icon = 'fa fa-check-square-o'; break;
    		case 'info' : $icon = 'fa fa-comment'; break;
    	}
        $data = "<div class='alert alert-dismissable alert-{$type}'>";
        if (!is_array($buttons))
        {
            $data .= "<strong><i class='icon $icon'></i> $header</strong>: $content";
        }
        else
        {
            $data .= "<h3><i class='icon $icon'></i> $header</h3><p>$content</p><br/>";
            foreach ($buttons AS $button)
                $data .= $button;

        }
        $data .= "</div>";
        return $data;
    }

    static public function callout($type = "success", $text = "Callout text")
    {
      return "<div class='callout callout-{$type}'>{$text}</div>";
    }


    static public function title($title = "Title", $sub = null)
    {
      $data = "<div class='col-md-12 bootstrap-grid'>
            <div class='page-header'>";
      $data .= ($sub) ? "<h1>{$title}<small>{$sub}</small></h1>" : "<h1>{$title}</h1>";
      $data .= "</div>";
      return $data;
    }

    /**
     * Javascript used with encap to scroll to the bottom of the page onload.
     * @return string
     */

    static public function scrollBottom()
    {
        return "$('html, body').animate({ scrollTop: $(document).height() }, 500);";
    }

    /**
     * Encapsulates javascript in an onload wrapper.
     * @param unknown $data
     * @return string
     */

    static public function encap($data)
    {
        return "<script type='text/javascript'>
        		$(document).ready(function() {
        			$data
		    }); </script>";
   	}

   	static public function popover($title, $content, $loc = 'top', $method = 'hover', $delay = null)
   	{
   		if ($delay)
   			$delay = "data-delay='$delay'";
   		$content = str_replace('"', "'", $content);
   		$title = str_replace('"', "'", $title);
      $data = "title='$title' data-container='body' rel='popover' data-html='true' data-trigger='hover' data-toggle='popover' data-placement='$loc' data-content=\"$content\" ";
   		return $data;
   	}

    static public function helpOver($title, $content, $right = true, $loc = 'left')
    {
      if ($right)
      return "<span class='pull-right'><a href='#' ". BS::popover("<p style='font-size:14px'>$title</p>", "<p style='font-size:14px;line-height:18px;color:#000;'>{$content}</p>", $loc).
      "><i class='fa fa-question-circle' class='text-success' style='font-size:22px'></i></a></span>";
      else
        return "<a href='#' ". BS::popover("<p style='font-size:14px'>$title</p>", "<p style='font-size:14px;line-height:18px;color:#000;'>{$content}</p>", $loc).
      "><i class='fa fa-question-circle' class='text-success' style='font-size:22px'></i></a>";
    }


   	static public function tooltip($content, $loc = 'top')
   	{
   		$content = str_replace('"', "'", $content);
   		$data = "data-toggle='tooltip' data-placement='$loc' title=\"$content\"";
   		return $data;
   	}



   	/**
   	 * Render a gallery item list
   	 *
   	 * @param unknown $items
   	 * @return string
   	 */

   	static public function gallery($items)
   	{
   		$data = "<ul class='gallery list-unstyled'>";
   		foreach ($items AS $item)
   			$data .= "<li class='mix $item[class]' data-name='$item[title]'><a href='/talent/photo/$item[id]/show'>
                                        <img src='$item[thumb]'></a>
                                        <h4>$item[title]</h4>
                                    </li>";
   		$data .= "</ul>";
   		return $data;
   	}

	static public function listGroup($items)
	{
		$data = "<div class='list-group'>";
		foreach ($items AS $item)
		{
			$span = (isset($item['badge'])) ? "<span class='badge badge-$item[color]'>$item[badge]</span>" : null;
			$class = (isset($item['class'])) ? $item['class'] : null;
			$target = (isset($item['target'])) ? "data-target='$item[url]' data-ride='$item[target]'" : null;
			$data .= "<a href='$item[url]' {$target} class='list-group-item {$class}' style='color: #261D89; font-weight:bold'>{$span}{$item['text']}</a>";
		}
		$data .= "</div>";
		return $data;
	}

	static public function accordions(array $items)
	{
		$data = "<div class='panel-group panel-info' id='accordion'>";

		foreach ($items AS $item)
		{
			if (!isset($item['color'])) $item['color'] = 'default';
			$id = \Str::random(5);
			$data .= "<div class='panel panel-{$item['color']}'>
							<a data-toggle='collapse' data-parent='#accordion' href='#{$id}'>
								<div class='panel-heading'><h4>{$item['title']}</h4></div>
							</a>
							<div id='{$id}' class='panel-collapse collapse'>
								<div class='panel-body'>
									{$item['content']}
								</div>
							</div>
						</div>";
		}
		$data .= "</div>";
		return $data;
	}
	/**
	 * Create a set of tabs w/o the use of a panel.
	 *
	 * @param array $tabs An array of title and content
	 * @param string $orientation null (top), left, right, bottom
	 * @param string $color null (no color), success, alert, magenta, etc.
	 */
	static public function tabs($tabs, $orientation = null, $color = null)
	{
		$orientation = ($orientation) ? "tab-{$orientation}" : null;
		$color = ($color) ? "tab-{$color}" : null;
		$key = \Str::random(5);
		$data = "<div class='tab-container {$orientation} {$color}'>
					<ul class='nav nav-tabs'>";
		foreach ($tabs AS $idx => $tab)
		{
			$a = (isset($tab['active'])) ? "class='active'" : null;

			$data .= "<li {$a}><a href='#$key-$idx' data-toggle='tab'>$tab[title]</a></li>";
		}
		$data .= "</ul>";
		$data .= "<div class='tab-content'>";

		foreach ($tabs AS $idx => $tab)
		{
			$a = (isset($tab['active'])) ? "active" : null;
			$data .= "<div class='tab-pane {$a}' id='$key-$idx'>$tab[content]</div>";
		}
		$data .= "</div></div>";
		return $data;
	}

	static public function calendar(array $events, $id = 'calendar')
	{
		$eventsData = null;
		foreach ($events AS $event)
		{
			//new Date("October 13, 1975 11:13:00")
			$start = Carbon::parse($event['start'])->format('F d, Y H:i:s');
			$end = Carbon::parse($event['end'])->format('F d, Y H:i:s');
			$start = "new Date('$start')";
			$end = "new Date('$end')";


			$eventsData .= "{
						title: '$event[title]',
						start: $start,
						end: $end,
						url: '$event[url]',
						backgroundColor: '$event[color]'
						},

					";
		}

		$data = "


	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	var calendar = $('#{$id}').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		selectable: true,
		selectHelper: true,
		editable: false,
		events: [
			$eventsData
		],
		buttonText: {
			prev: '<i class=\"fa fa-angle-left\"></i>',
			next: '<i class=\"fa fa-angle-right\"></i>',
			prevYear: '<i class=\"fa fa-angle-double-left\"></i>',  // <<
			nextYear: '<i class=\"fa fa-angle-double-right\"></i>',  // >>
			today:    'Today',
			month:    'Month',
			week:     'Week',
			day:      'Day'
		}
		});

		";

	return BS::encap($data);
	}



	static public function dataTablesInit()
	{
		return "<script type='text/javascript'>
		$(document).ready(function() {
	    $('.datatables').dataTable({
	        \"sDom\": \"<'row'<'col-xs-6'l><'col-xs-6'f>r>t<'row'<'col-xs-6'i><'col-xs-6'p>>\",
	        \"sPaginationType\": \"bootstrap\",
	        \"oLanguage\": {
	            \"sLengthMenu\": \"_MENU_ records per page\",
	            \"sSearch\": \"\"
	        }
	    });
	    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search...');
	    $('.dataTables_length select').addClass('form-control');
		});
		</script>";
	}


	static public function selectInit($width = 300)
	{
		return "<script type='text/javascript'>
		$(document).ready(function() {
		 $(\".select2\").select2({width: '{$width}px'});
      $('.ajaxselect').each( function()
          {
          var method = $(this);
          method.select2({
             initSelection: function(method, callback) {
                    data = {\"id\" : \"00\", \"text\" : method.val()};
                    callback(data);
                },
            placeholder: method.attr('placeholder'),
            minimumInputLength: 4,
            width: \"{$width}px\",
              ajax: {
                url: method.attr('data-target'),
                dataType: 'json',
                data: function (term, page) {
                  return {
                    q: term
                  };
                },
                results: function (data, page) {
                  return { results: data };
                }
              }
            });
          });

	    });</script>";
	}

	static public function popoverInit()
	{
		return self::encap('
				$("[data-toggle=popover]").popover();
      $(document).on(\'click\', \'.popover-title .close\', function(e){
        var target = $(this).parent().parent(); $(target).hide();

    });

				');
	}

	static public function tabsInit()
	{
		return self::encap('
				$(\'.tab-container\').tab();
				');

	}



}

