<?php
namespace vl\libraries\bootstrap;
class Tile
{
    public $large = true;           // By default the tile is large
    public $header = null;          // Header if large
    public $footer = null;          // Footer
    public $url = "#";              // Url to wrap anchor to
    public $color = "primary";      // BS3 color definition
    public $content = null;         // Content

    /**
     * Instantiate a new Tile Class
     *
     * @return Tile
     */
    static public function init()
    {
        return new Tile;
    }

    /**
     * Set color for tile. Use
     * @param unknown $color
     * @return Tile
     */
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    public function header($header)
    {
        if (is_array($header))
        {
            $this->header = "<div class='tiles-heading'>
                        <div class='pull-left'>$header[0]</div>
                        <div class='pull-right'>$header[1]</div>
                        </div>";
        }
        else
            $this->header = "<div class='tiles-heading'>
                        $header
                        </div>";

        return $this;
    }

    public function content($content, $extendContent = false)
    {
        $alt = ($extendContent) ? "-alt" : null;
    	if (is_array($content))
        {
            $this->content = "
            			<div class='tiles-body{$alt}'>
            				<div class='pull-left'>$content[0]</div>
				            <div class='pull-right'>$content[1]</div>
            			</div>";
        }
        else
            $this->content = "<div class='tiles-body{$alt}'>$content</div>";
        return $this;
    }

    public function footer($footer)
    {
        $this->footer = "<div class='tiles-footer'>{$footer}</div>";
        return $this;
    }

    public function small()
    {
        $this->large = false;
        return $this;
    }

    public function url($url)
    {
    	$this->url = $url;
    	return $this;
    }

    public function render()
    {
        $style = ($this->large) ? "info-tiles" : "shortcut-tiles";
        $data = "<a class='{$style} tiles-{$this->color}' href='{$this->url}'>";
        if ($this->header)
            $data .= $this->header;
        if ($this->content)
            $data .= $this->content;
        if ($this->footer)
            $data .= $this->footer;
        $data .= "</a>";
        return $data;
    }














}