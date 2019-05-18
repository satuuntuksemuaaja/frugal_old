<?php
namespace vl\libraries\bootstrap;
class Modal
{
	public function __construct()
	{
		$this->header = "Modal header";
		$this->content = "Modal Content";
		$this->footer = "<a href='#' data-dismiss='modal' class='btn'><i class='fa fa-times-circle-o'></i> Close</a> ";
		$this->isInline = false;   //Used when sending in ajax data.
		$this->onlyConstruct = false; // Used when building the modal struct but no data.
		$this->autoLoad = false; // Send back some js for autoloading this modal.
		$this->styles = ['modal', 'fade']; 	// Modal init style
		$this->fade = true;
		$this->backdrop = true;
		$this->id = 'modal';
		$this->dynamic = false;
		$this->width = false;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function backdrop($state = true)
	{
		if (!$state)
			array_push($this->styles, 'static');
		return $this;
	}
	static public function init()
	{
		return new Modal();

	}
	public function withClose($closeText = 'Close')
	{
	    $this->footer .= "<a href='#' data-dismiss='modal' class='btn'><i class='fa fa-times-circle-o'></i> {$closeText}</a>";
	    return $this;

	}
	public function header($header)
	{
		$this->header = $header;
		return $this;
	}

	public function width($width)
	{
		// sm md lg
		$this->width = $width;
		return $this;
	}

	public function content($content)
	{
		$this->content = $content;
		return $this;
	}

	public function footer($footer = null)
	{
		$this->footer = $footer;
		return $this;
	}

	public function id($id)
	{
		$this->id = $id;
		return $this;
	}

	public function fade($state = true)
	{
		if ($state)
			array_push($this->styles, 'fade');
		else
		{
			$new = [];
			foreach ($this->styles AS $id => $style)
				if ($style == 'fade')
					unset($this->styles[$id]);
		}

		return $this;
	}


	public function hide($state = true)
	{
		if ($state)
			array_push($this->styles, 'hide');
		else
		{
			$new = [];
			foreach ($this->styles AS $id => $style)
				if ($style == 'hide')
				unset($this->styles[$id]);
		}

		return $this;
	}

	public function autoLoad()
	{
		$this->autoLoad = true;
		return $this;
	}

	public function dynamic ($state = true)
	{
		if ($state)
			array_push($this->styles, 'container');
		else
		{
			$new = [];
			foreach ($this->styles AS $id => $style)
				if ($style == 'container')
				unset($id);
		}
		$this->styles = $style;
		return $this;
	}



	public function isInline()
	{
		$this->isInline = true;
		return $this;
	}

	public function onlyConstruct()
	{
		$this->onlyConstruct = true;
		return $this;

	}

	public function render()
	{
		$styles = implode(" ", $this->styles);
		$width = ($this->width) ? $this->width : "modal-lg";
		if ($this->onlyConstruct)
					return "<div class='{$styles}' id='{$this->id}' $width></div>";

		if ($this->isInline)
			return "
			         <div class='modal-dialog {$width}' style='$width'>
                        <div class='modal-content'>
                             <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>x</button>
                            <h3 class='modal-title' id='{$this->id}Label'>{$this->header}</h3>
													</div>
                        <div class='modal-body'>
                                {$this->content}
                        </div>
                        <div class='modal-footer'>
                            {$this->footer}
                        </div>
                    </div><!-- /.modal-content -->
                </div>";
	     // If we haven't returned yet, we need the entire modal init.
			$data = "
				<div id='{$this->id}' class='$styles'>
                <div class='modal-dialog {$width}' style='$width'>
                        <div class='modal-content'>
                             <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>x</button>
                            <h3 class='modal-title' id='{$this->id}Label'>{$this->header}</h3>
													</div>
                        <div class='modal-body'>
                        {$this->content}
                    </div>
                    <div class='modal-footer'>
                        {$this->footer}
                    </div>
                </div><!-- /.modal-content -->
            </div>
            </div>";
        return $data;
	}
}