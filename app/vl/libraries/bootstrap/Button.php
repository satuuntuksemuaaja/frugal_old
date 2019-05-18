<?php
namespace vl\libraries\bootstrap;
class Button
{
    protected $modalLauncher  = false;            // By default this button doesn't launch a modal.
    protected $dropdown       = false;            // By default this button doesn't have a dropdown
    protected $color          = 'primary';        // By Default this button has default color
    protected $classes        = null;             // Any additional classes that should be appeneded.
    protected $url            = '#';              // Url to Where this button goes if not modal launcher.
    protected $type           = 'a';              // A or Button for type of button rendering.
    protected $extras         = null;             // Used for data-toggles, etc to append to the renderer.
    protected $id             = null;             // By default no ID is required.
    protected $text           = 'button';         // What does the button say by default?
    protected $icon           = "<i class='fa fa-frown-o'></i> ";        // Default Icon
    protected $postVar        = null;             // for Ajax requests
    protected $formid         = null;             // for Ajax requests
    protected $message        = null;             // Processing message when pressed and waiting.
    protected $caret          = null;             // Enables a caret for dropdown
    protected $group          = true;             // Wrap buttons in a button group.
    protected $validates      = null;             // Use parsley to validate a form.
    protected $centered		  = false;			  // Should we center the button?
    protected $confirm        = null;             // For Bootstrap Popover Confirm.
    protected $popover        = null;             // For a normal popoverTitle

    public function modal($id, $ajax = false)
    {
        $this->modalID = $id;
        if ($ajax)
        {
            $this->extras .= "data-target='#{$id}' ";
            $this->classes .= "mjax ";
            return $this;
        }
        $this->modalLauncher = true;
        return $this;
    }

    public function popover($title, $content, $location = 'right')
    {
        $this->classes .= ' popovered ';
        $this->popover = "title='$title' data-container='body' rel='popover' data-html='true' data-trigger='hover' data-toggle='popover' data-placement='$location' data-content=\"$content\" ";
        return $this;
    }

	/**
	 * Use the pjax class to make an ajax call to the targetted url
	 * and send the output to the element specified.
	 *
	 * @param unknown $element
	 * @return Button
	 */
    public function target($element)
    {
    	$this->classes .= "pjax ";
    	$this->extras .= "data-target='{$this->url}' data-ride='{$element}' ";
    	return $this;
    }

    public function confirm($confirmUrl, $confirmText = "OK", $confirmClass='primary',
                            $title = 'Are you Sure?', $placement = 'right')
    {
        $this->confirm = "data-toggle='confirmation' data-title='$title'
        data-href='$confirmUrl' data-placement='$placement' data-btnOkLabel='$confirmText'
        data-btnOkLabel='btn btn-$confirmClass' ";
        return $this;
    }

    public function centered()
    {
    	$this->centered = true;
    	return $this;
    }
    public function withoutGroup()
    {
        $this->group = false;
        return $this;
    }

    public function formid($formid)
    {
        $this->formid = '#' . $formid;
        return $this;
    }

    public function caret()
    {
        $this->caret = " <span class='caret'></span>";
        return $this;
    }

    public function postVar($postVar)
    {
        $this->postVar = $postVar;
        return $this;
    }

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function extras($extras)
    {
        $this->extras .= $extras;
        return $this;
    }

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function text($text)
    {
        $this->text = $text;
        return $this;
    }

    public function classes($classes)
    {
        $this->classes .= $classes;
        return $this;
    }

    /**
     * Use parsely to validate the form. Currently not compatible with get/post/mget/post classes.
     * @param unknown $formid
     * @return Button
     */
    public function validates($formid)
    {
        $this->validates = "onclick=\"javascript:$('#$formid').parsley( 'validate' );\" ";
        return $this;
    }

    public function click($code)
    {
        $this->validates = "onclick=\"$code;\" ";
        return $this;
    }
    public function color($color)
    {
       $this->color = $color;
       return $this;
    }

    /**
     * Create a dropdown menu on the button. Items contains an array of:
     * class : modal = id : url (if not modal) : text : icon
     * @param unknown $items
     * @return Button
     */
    public function dropdown($items)
    {
        $this->extras .= "data-toggle='dropdown' ";
        $this->classes .= ' dropdown-toggle';
        $data = "<ul class='dropdown-menu'>";
        foreach ($items AS $item)
        {
            $class = isset($item['class']) ? $item['class'] : null;
            $dt =  (isset($item['modal'])) ? "data-toggle='modal'" : null;
            $icon = (isset($item['icon'])) ? "<i class='fa fa-$item[icon]'></i> ": null;
            $url = (isset($item['modal'])) ? "#{$item['modal']}" : $item['url'];
            if ($item['text'] == 'sep')
                $data .= "<li class='divider'></li>";
            else
                $data .= "<li><a class='$class' $dt href='$url'>$icon $item[text]</a></li>";
        }
        $data .= "</ul>";
        $this->dropdown = $data;
        return $this;

    }

    public function icon($icon, $effect = null)
    {
        if (!$icon)
            $this->icon = null;
        else
            $this->icon = "<i class='fa fa-{$icon} {$effect}'></i> ";
        return $this;
    }

    static public function init()
    {
        return new Button;
    }

    public function render()
    {
        $message = ($this->message) ? $this->extras .= "data-title='$this->message' " : null;
        $postvar = ($this->postVar) ? $this->extras .= "rel='$this->postVar' " : null;
        $formid = ($this->formid)   ? $this->extras .= "data-content='$this->formid' " : null;
        $dropDown = ($this->dropdown) ? $this->dropdown : null;
        $groupStart = ($this->group) ? "<div class='btn-group'>" : null;
        $groupEnd = ($this->group) ? "</div>" : null;
       	$centeredStart = ($this->centered) ? "<center>" : null;
       	$centeredEnd = ($this->centered) ? "</center>" : null;
       if ($this->modalLauncher)
       {
           $this->url = "#{$this->modalID}";
           $this->extras .= "data-toggle='modal' ";
       }

            return "{$centeredStart}
            		{$groupStart}
                    <{$this->type}
                    href='{$this->url}'
                    {$this->extras}
                    class='btn btn-{$this->color} {$this->classes} {$this->confirm}' {$this->validates}
                    id='{$this->id}' {$this->popover}>
                    {$this->icon}{$this->text} {$this->caret}</{$this->type}>{$dropDown}
                    {$groupEnd}
                    {$centeredEnd}";

       // At this point it has a dropdown.




    }
}