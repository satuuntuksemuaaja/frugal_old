<?php
namespace vl\libraries\bootstrap;

class Table
{
    public $id;
    static public $rowColor;

    public function __construct()
    {
        $this->tableClasses = ['table', 'table-striped', 'table-bordered', 'table-hover'];
        $this->footer = null;
        $this->dataTables = false;
        $this->responsive = false;
        $this->cellWidth = null;
        $this->width = null;     // Manual Override for Width for raw output (pdf?)
        $this->datatablesURL = null;
    }

    public function __toString()
    {
        return $this->render();
    }

    static public function init()
    {
        return new Table();
    }

    public function width($width)
    {
        $this->width = $width;
        return $this;
    }


    public function color($color)
    {
        $this->tableClasses[] = "table-$color";
        return $this;
    }

    public function noStriping()
    {
        unset($this->tableClasses[1]);
        return $this;

    }

    public function dataTables($url = null)
    {
        if ($url)
        {
            $this->datatablesURL = $url;
        }
        $this->dataTables = true;
        return $this;
    }

    public function footer($footer)
    {
        $this->footer = $footer;
        return $this;
    }

    public function responsive()
    {
        $this->responsive = true;
        return $this;
    }

    public function headers($headers)
    {
        $this->headers = $headers;
        return $this;
    }


    public function id($id)
    {
        $this->id = $id;
        return $this;

    }

    public function rows($rows)
    {
        $this->rows = $rows;
        return $this;
    }


    /*
     * .table	default table style

        .table-striped	Adds zebra-striping to any table row within the tbody
        .table-bordered	Add borders and rounded corners to the table.
        .table-hover	Enable a hover state on table rows within a tbody

     */

    public function addStyle($style)
    {
        array_push($this->tableClasses, $style);
        return $this;
    }

    public function clearStyles()
    {
        $this->tableClasses = ['table'];
        return $this;

    }

    static public function setRowColor($color)
    {
        self::$rowColor = $color;
    }

    public function render()
    {
        if ($this->dataTables)
        {
            $this->addStyle('dataTable');
        }
        $tableClasses = implode(" ", $this->tableClasses);
        $data = null;
        $dturl = null;
        if ($this->datatablesURL)
        {
            $dturl = "data-url='$this->datatablesURL'";
        }

        if ($this->responsive)
        {
            $data .= "<div class='table-responsive'>";
        }
        $width = ($this->width) ? "width='{$this->width}%' " : null;
        $data .= "<table id='{$this->id}' class='{$tableClasses}' {$dturl} {$width}><thead><tr>";
        if (isset($this->headers))
        {
            foreach ($this->headers AS $header)
                $data .= "<th style='height: 30px'>{$header}</th>";
            $data .= "</tr></thead>";
        }
        if ($this->footer)
        {
            $data .= $this->footer;
        }
        $data .= "<tbody>";
        foreach ($this->rows AS $row)
        {
            if (isset($row[0]) && preg_match('/color-/', $row[0]))
            {
                $color = preg_match('/color-(.*?) /', $row[0], $matches);

                if (isset($matches[1]))
                {
                    $data .= "<tr class='" . $matches[1] . "'>";
                }
                $row[0] = str_replace("color-{$matches[1]}", null, $row[0]);
            }
            else
            {
                $data .= "<tr>";
            }
            foreach ($row AS $rowdata)
            {

                if (is_array($rowdata))
                {
                    foreach ($rowdata AS $d)
                    $data .= "<td>$d</td>";
                }
                else
                {
                    $data .= "<td>$rowdata</td>";
                }
            }

            $data .= "</tr>";
        }
        $data .= "</tbody></table>";
        if ($this->responsive)
        {
            $data .= "</div>";
        }
        return $data;
    }
}