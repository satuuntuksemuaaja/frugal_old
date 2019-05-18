<?php
namespace vl\libraries\bootstrap;
class Editable
{

    public $type = 'text';    // Initial Datatype
    public $id = 'editable_id'; // initial id
    public $source = null;    // Where to get data from ajax.
    public $pk = 1;       // Primary Key of Record
    public $placeholder = null; // placeholder
    public $title = 'Title'; // initial popover title
    public $linkText = "Editable Link"; // Initial anchor text
    public $placement = 'right'; // Default placement
    public $added = null;
    public $hasDate = false;
    public $hasTime = false;

    static public function init()
    {
        return new self;
    }

    /**
     * Types can be text, select, combodate,checklist,select2,typeaheadjs, textarea
     *
     * @param  [type] $type [description]
     * @return $this [type]       [description]
     */
    public function type($type)
    {
        $this->type = $type;
        if ($type == 'combodate')
        {
            $this->dateOptions();
        }
        return $this;
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function dateOptions($showTime = true, $showDate = true)
    {
        $template = null;
        $dataFormat = null;
        if ($showDate)
        {
            $template .= "MM D YYYY ";
            $dataFormat .= "MM/DD/YY ";
        }
        if ($showTime)
        {
            $template .= "hh:mm a";
            $dataFormat .= "hh:mm a";
        }
        $this->added = " data-template='$template' data-format='$dataFormat'";
        $this->hasDate = $showDate;
        $this->hasTime = $showTime;
        return $this;
    }

    public function source($source)
    {
        if (is_array($source))
        {
            $this->source = json_encode($source);
        }
        else
        {
            $this->source = $source;
        }
        return $this;
    }

    public function pk($pk)
    {
        $this->pk = $pk;
        return $this;
    }

    public function placeholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    public function linkText($text)
    {
        $this->linkText = $text;
        return $this;
    }

    public function placement($placement)
    {
        $this->placement = $placement;
        return $this;
    }

    public function render()
    {
        $source = ($this->source) ? "data-source='{$this->source}'" : null;
        $addClass = ($this->hasTime && $this->hasDate) ? "datetime" : null;
        $data = "<a class='editable {$addClass}' href='#' id='{$this->id}' data-type='{$this->type}' data-title='{$this->title}'
    data-placement='{$this->placement}' data-url='{$this->url}' $source {$this->added}> {$this->linkText}</a>";
        return $data;
    }
}