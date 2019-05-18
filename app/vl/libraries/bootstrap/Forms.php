<?php namespace vl\libraries\bootstrap;
class Forms {
	public $hasFile = false; // Are we uploading a file? Need to set type if so.
	public $id = "myform"; // Default ID for addressing the form.
	public $classes = "form-horizontal"; // Default class assignments
	public $method = "POST"; // default method.
	public $url = "#"; // default url
	public $masterElements = null; // All methods build on this.
	public $validate = false; // Validate with parsley
	public $labelSpan = 2; // Set the default label span
	public $letterSize = null; // Default letter sizing.
	static public function init()
	{
		return new Forms;
	}

	public function labelSpan($lspan)
	{
		$this->labelSpan = $lspan;
		return $this;

	}
	public function validate()
	{
		$this->validate = true;
		return $this;
	}

	public function border()
	{
		$this->classes .= " row-border ";
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

	/**
	 * Sets the text input size. Values are lg or sm (md by default)
	 * @param string $size
	 * @return Forms
	 */
	public function setInputSize($size = 'lg')
	{
		$this->letterSize = $size;
		return $this;
	}

	/**
	 * Create a wizard based on form elements.
	 * @param array $steps
	 */
	public function wizard(array $steps, $span = false)
	{
		$this->classes .= " wizard";
		foreach ($steps as $step)
		{
			$this->masterElements .= "<fieldset title='$step[title]'><legend>$step[desc]</legend>";
			if (isset($step['span']))
			{
				$this->span($step['elements']);
			}
			else
			{
				$this->masterElements .= $this->elements($step['elements'], true);
			}

			$this->masterElements .= "</fieldset>";
		}
		return $this;
	}

	public function method($method)
	{
		$this->method = $method;
		return $this;
	}

	public function span($spans)
	{
		$this->classes = str_replace("row-border", null, $this->classes);
		$this->masterElements .= "<div class='row'>";
		foreach ($spans AS $span)
		{
			$this->masterElements .= "<div class='col-sm-$span[span]'>".$this->elements($span['elements'], true)."</div>";
		}
		$this->masterElements .= "</div>";
		return $this;
	}

	public function elements(array $elements, $span = false)
	{
		if ( ! $span)
		{
			foreach ($elements AS $element)
			{
				$type = $element['type'];
				$this->masterElements .= $this->HDecorator($element, $this->{$type}($element));
			}
			return $this;
		}
		else
		{
			$data = null;
			foreach ($elements AS $element)
			{
				$type = $element['type'];
				$data .= $this->HDecorator($element, $this->{$type}($element));
			}
			return $data;
		}
	}
	/**
	 * Decorate the form with a horizontal style. Use VDecorator for vertical forms.
	 *
	 * @param unknown $element
	 * @param unknown $fromMethod
	 * @return string
	 */
	public function HDecorator($element, $fromMethod)
	{
		// Spans are generated based on span (label span)
		$fspan = (isset($element['span'])) ? $element['span'] : 3;
		$var  = (isset($element['var'])) ? $element['var'] : str_random(5);
		$text = (isset($element['text'])) ? $element['text'] : null;
		if (isset($element['req']))
		{
			$text .= "<font style='color:#aa0000;font-weight:bold;font-size:18px;'>*</font>";
		}

		//$text .= ($element['type'] == 'checkbox' && !isset($element['nohelp'])) ? "<br/><small>(Check all that apply)</small>" : null;
		$addDecorator = null;
		$status = (isset($element['status'])) ? "has-{$element['status']}" : null;
		if (isset($element['pre']) || isset($element['post']))
		{
			$addDecorator = "<div class='input-group'>";
		}

		$preDecorator = (isset($element['pre'])) ? "<span class='input-group-addon'>$element[pre]</span>" : null;
		$postDecorator = (isset($element['post'])) ? "<span class='input-group-addon'>$element[post]</span>" : null;
		$addTerminator = ($addDecorator) ? "</div>" : null;
		$commentBlock = (isset($element['comment'])) ? "<p class='help-block'>$element[comment]</p>" : null;
		$l = (isset($element['var'])) ? $element['var']."_l" : null;
		if ( ! isset($element['raw']))
		{
			$data = "<div class='form-group $status {$l}'>
                    <label for='$var' class='col-sm-{$this->labelSpan} control-label'>$text</label>
                    <div class='col-sm-$fspan'>
                    {$addDecorator}
                    {$preDecorator}
                    {$fromMethod}
                    {$postDecorator}
                    {$commentBlock}
                    {$addTerminator}
                    </div>
                </div>";
		}
		else
		{
			$data = "<div class='form-group'>$element[raw]</div>";
		}

		return $data;
	}

	/**
	 * Render a password field using Input.
	 *
	 * @param unknown $element
	 * @return string
	 */
	public function password(&$element)
	{
		return $this->input($element);
	}

	public function input(&$element)
	{
		$id        = (isset($element['id'])) ? $element['id'] : str_random(5);
		$class     = (isset($element['class'])) ? $element['class'] : null;
		$var       = (isset($element['var'])) ? $element['var'] : str_random(5);
		$val       = (isset($element['val'])) ? " value='{$element['val']}'" : null;
		$holder    = (isset($element['placeholder'])) ? $element['placeholder'] : null;
		$dsbl      = (isset($element['disabled'])) ? "disabled" : null;
		$type      = ($element['type']      == 'password') ? "password" : "text";
		$read      = (isset($element['readonly'])) ? "readonly='readonly'" : null;
		$max       = (isset($element['max'])) ? "maxlength='$element[max]'" : null;
		$mask      = (isset($element['mask'])) ? "data-inputmask=\"'mask':'$element[mask]'\"" : null;
		$typeahead = (isset($element['auto'])) ? "data-target='$element[auto]' " : null;
		$select2   = (isset($element['select2'])) ? " ajaxselect " : null;
		if ($mask)
		{
			$class .= " mask ";
		}

		if ($typeahead &&  ! $select2)
		{
			$class .= " auto";
		}

		if ($select2)
		{
			$class .= $select2;
		}

		$class .= ($this->letterSize) ? "input-{$this->letterSize}" : null;
		$class .= (isset($element['dp'])) ? " hasDatepicker" : null;
		$base = ( ! $select2) ? "form-control {$class}" : $class;
		/*
		 * Data validators
		*/
		$req  = (isset($element['req'])) ? "required='required'" : null;
		$min  = (isset($element['min'])) ? "data-minlength='$element[min]'" : null;
		$rlen = (isset($element['rlen'])) ? "data-rangelength='$element[rlen]'" : null; // [5,10]
		$regex = (isset($element['regex'])) ? "data-regexp='$element[regex]'" : null; //
		$dtype = (isset($element['dtype'])) ? "data-type='$element[dtype]'" : null; // email,url,digits,alphanum,dateIso,phone,
		$data = "<input type='{$type}'
                        name='{$var}'
                        placeholder='{$holder}'
                        {$val}
                        class='{$base}'
                        id='$id'
                        {$dsbl} {$read} {$max} {$req} {$min} {$rlen} {$regex} {$dtype} {$mask} {$typeahead}
                        >";
		return $data;
	}

	public function select(&$element)
	{
		// Select boxes now have option group functionality.
		$id = (isset($element['id'])) ? $element['id'] : str_random(5);
		$class  = (isset($element['class'])) ? $element['class'] : null;
		$var    = (isset($element['var'])) ? $element['var'] : str_random(5);
		$dsbl   = (isset($element['disabled'])) ? "disabled" : null;
		$holder = (isset($element['placeholder'])) ? $element['placeholder'] : null;
		$multi  = (isset($element['multi'])) ? "multiple" : null;
		$sData  = null;
		$base   = (isset($element['2'])) ? "select2" : "form-control {$class}";

		if (isset($element['groups']))
		{
			foreach ($element['groups'] AS $group)
			{
				// $group['title'] and ['options']
				$sData .= "<optgroup label='$group[title]'>";
				foreach ($group['options'] AS $option)
				{
					$sData .= "<option value='$option[val]'>$option[text]</option>";
				}

				$sData .= "</optgroup>";
			}
		}
		else
		if (isset($element['opts']))
		{
			foreach ($element['opts'] AS $option)
			{
				if (is_array($option) && array_key_exists('val', $option))
				{
					$sData .= "<option value='$option[val]'>$option[text]</option>";
				}
				else
				{
					$sData .= "<option value='$option'>$option</option>";
				}

			}
		}

		$data = "<select class='$base' {$multi}
                        id='{$id}'
                        name='{$var}'
                        placeholder='{$holder}'
                        {$dsbl}
                        >{$sData}</select>";
		return $data;
	}

	public function select2(&$element)
	{
		$element['2'] = true;
		return $this->select($element);
	}

	public function raw(&$element)
	{
		return $element['raw'];
	}

	public function radio(&$element)
	{
		$id     = (isset($element['id'])) ? $element['id'] : str_random(5);
		$class  = (isset($element['class'])) ? $element['class'] : null;
		$var    = (isset($element['var'])) ? $element['var'] : str_random(5);
		$inline = (isset($element['inline'])) ? true : false;
		$type   = ($element['type']   == 'radio') ? "radio" : "checkbox";
		$data   = null;
		if ($inline)
		{
			foreach ($element['opts'] AS $option)
			{
				$checked = (isset($option['checked']) && $option['checked']) ? "checked" : null;
				$data .= "<label class='{$type}-inline'><input type='{$type}' name='$var' value='$option[val]' $checked> $option[text]</label>";
			}
		}
		else
		{
			foreach ($element['opts'] AS $option)
			{
				$checked = (isset($option['checked']) && $option['checked']) ? "checked" : null;
				$var     = (isset($option['var'])) ? $option['var'] : $var;
				$data .= "<div class='radio'><label><input type='{$type}' name='$var' value='$option[val]' $checked> $option[text]</label></div>";
			}
		}

		return $data;
	}

	public function checkbox(&$element)
	{
		return $this->radio($element);
	}

	public function textarea(&$element)
	{
		$id     = (isset($element['id'])) ? $element['id'] : str_random(5);
		$class  = (isset($element['class'])) ? $element['class'] : null;
		$var    = (isset($element['var'])) ? $element['var'] : str_random(5);
		$dsbl   = (isset($element['disabled'])) ? "disabled" : null;
		$holder = (isset($element['placeholder'])) ? $element['placeholder'] : null;
		$auto   = (isset($element['auto'])) ? "autosize" : null;
		$val    = (isset($element['val'])) ? $element['val'] : null;
		$rows   = (isset($element['rows'])) ? $element['rows'] : 4;
		$class .= ($this->letterSize) ? "input-{$this->letterSize}" : null;
		if ( ! isset($element['ck']))
		{
			$data = "<textarea class='form-control $class'
                        name='$var'
                        rows='$rows'
                        placeholder='$holder'
                        id='$id'
                        $auto>$val</textarea>";
		}

		return $data;
	}

	public function submit(&$element)
	{
		return "<input type='submit' class='btn $element[class]' name='$element[var]' value='$element[val]'>";

	}

	public function summernote(&$element)
	{
		return "<div id='summernote'>$element[val]</div>";
	}

	public function file(&$element)
	{
		$this->hasFile = true;
		return "<input type='file' name='$element[var]'>";
	}

	public function hidden(&$element)
	{
		return "<input type='hidden' name='$element[var]' value='$element[val]'>";
	}
	public function datetime(&$element)
	{
		return "<input for='$element[var]' type='text' class='form-control drp3' name='$element[var]' id='$element[var]'>";
	}

	public function time(&$element, $timeVal = null)
	{

		$select = [];
		$select['var'] = $element['var']."_ampm";
		$select['opts'] = [];
		if ($timeVal)
		{
			$select['opts'][] = ['val' => $timeVal, 'text' => $timeVal];
		}

		$select['opts'][] = ['val' => 'AM', 'text' => 'AM'];
		$select['opts'][] = ['val' => 'PM', 'text' => 'PM'];
		$select['text'] = null;
		$select['span'] = 2;
		$select = $this->select($select);
		$element['post'] = $select;
		$input = $this->input($element);
		return $input.$select;
	}
	public function render()
	{
		$enctype   = ($this->hasFile) ? "enctype='multipart/form-data'" : null;
		$validator = ($this->validate) ? "data-validate='parsley'" : null;
		return "<form
                        id='{$this->id}'
                        {$validator}
                        class='{$this->classes}'
                        method='{$this->method}'
                        action='{$this->url}'
                        {$enctype}>
               <div class='{$this->id}_msg'></div>
                     <fieldset>
                         {$this->masterElements}
                    </fieldset>
               </form>";
	}

}