<?php
namespace vl\libraries\bootstrap;
class Panel
{
	public $header = null;			// By default there is no header block
	public $footer = null;			// By default there is no footer
	public $content = null;
	public $panelFeatures = null;
	public $tabs = false;       // By default we aren't tabbing a panel
	public $salt = null;
	public $tabsAreLeft = true;
	static public function init($with = null)
	{
		$panel = new Panel;
		$panel->panelFeatures = $with;
		return $panel;
	}

	public function tabs($tabs)
	{
	    $this->salt = str_random(5);
	    $this->tabs = $tabs;
	    return $this;
	}
	public function makeTabsRight()
	{
		$this->tabsAreLeft = false;
		return $this;
	}

	public function header($content, $more = null)
	{
	   $this->header = "<div class='panel-heading'><h3 class='panel-title'>{$content}{$more}</h3></div>";
			return $this;
	}

	public function footer($content, $alignRight = false)
	{
		$content = ($alignRight) ? "<span class='pull-right'>$content</span>" : $content;
		$this->footer = "<footer class='panel-footer'>{$content}</footer>";
		return $this;
	}

	public function content($content, $withPanelBody = true)
	{
		if ($this->tabs)
		{
			$right = (!$this->tabsAreLeft) ? "pull-right" : null;
 		    $tabAdd = "<ul class='nav nav-tabs $right'>";

		    foreach ($this->tabs AS $idx => $tab)
		          {
		          	if (!isset($tab['class'])) $tab['class'] = null;
		          	if (!isset($tab['dropdown']))
		          	{
		          	    if (isset($tab['ajax']))
		          	    {
		          	       $ajaxInject = "data-target='$tab[ajax]' data-ride='#{$this->salt}-$idx' ";
		          	       $tab['class'] .= " pjax";
		          	    }
		          	    else $ajaxInject = null;
		          	    $tabAdd .= "<li $ajaxInject class='$tab[class]'><a href='#{$this->salt}-$idx' data-toggle='tab'>$tab[title]</a></li>";
		          	}
		          	else
		          	{
		          		$tabAdd .= "<li class='dropdown $tab[class]'>";
                      // Use 0 for the dropdown info.
                      	$tabAdd .= "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>$tab[title] <span class='caret'></span></a>
                      	<ul class='dropdown-menu'>";
                        foreach ($tab['dropdown'] AS $i => $dtab)
                        			if (isset($dtab['content']))
                        				$tabAdd .= "<li><a href='#{$this->salt}-$idx-$i' data-toggle='tab'>$dtab[title]</a></li>";
                        			elseif (isset($dtab['modal']))
                        				$tabAdd .= "<li><a href='$dtab[modal]' data-toggle='modal'>$dtab[title]</a></li>";
                        			elseif (isset($dtab['ajax']))
                        				$tabAdd .= "<li class='pjax' data-target='$dtab[ajax]' data-ride='#{$this->salt}-$idx-$i'><a href='#{$this->salt}-$idx-$i' data-toggle='tab'>$dtab[title]</a></li>";
                                    elseif (isset($dtab['sep']))
                                        $tabAdd .= "<li class='divider'></li>";
                        $tabAdd .= "</ul></li>";
		          	} // tab has a drop down son!
		          }
		    $tabAdd .= "</ul>";
        $withPanelBody = true;
		    $content = "{$tabAdd}<div class='tab-content'>";
		    foreach ($this->tabs AS $idx => $tab)
		        {
		            if (!isset($tab['class'])) $tab['class'] = null;
		        	$pullout = (isset($tab['pullout'])) ? "<div class='panel-body'>" : null;
		            $pulloutE = (isset($tab['pullout'])) ? "</div>" : null;
		        	if (!isset($tab['dropdown']))
		        		$content .= "<div class='tab-pane $tab[class]' id='{$this->salt}-$idx'>{$pullout}$tab[content]{$pulloutE}</div>";
		        	else
		        		foreach ($tab['dropdown'] AS $i => $dtab)
		        		{
		        		    if (isset($dtab['content']))
		        				$content .= "<div class='tab-pane' id='{$this->salt}-$idx-$i'>$dtab[content]</div>";
		        		    else
		        		    	$content .= "<div class='tab-pane' id='{$this->salt}-$idx-$i'></div>";
		        		}
		        }
		    $content .= "</div>";
		}


	    if ($withPanelBody)
			$this->content = "<div class='panel-body'>$content</div>";
		else
			$this->content = $content;
		return $this;
	}

	public  function render()
	{
		$data = "<div class='panel panel-$this->panelFeatures'>";
		$data .= ($this->header) ? $this->header : null;
		$data .= $this->content;
		$data .= $this->footer;
		$data .= "</div>";
		return $data;
	}



}