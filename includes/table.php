<?php

class PanelTable
{
    public $g_columSize;

    public function __construct ($columSize)
	{
        $this->g_columSize = $columSize;
    }
    
    public function tableStart ($table_class = "")
    {
        echo '<table class="table ' . $table_class . '">';
    }

    public function tableHead ($col_titles)
    {
        echo '<thead class="thead-dark">';
        echo '<tr>';

        for ($i = 0; $i < $this->g_columSize; $i++)
            echo '<th scope="col">' . $col_titles[$i] . '</th>';

        echo '</tr>';
        echo '</thead>';
    }

    public function tableBody ($row_values)
    {
        for ($i = 0; $i < count($row_values); $i++)
        {
            echo '<tr>';

            for ($j = 0; $j < $this->g_columSize; $j++)
            {
                if ($j == 0)
                    echo '<th scope="row">' . $row_values[$i][$j] . '</th>';
                else
                    echo '<td>' . $row_values[$i][$j] . '</td>';
            }

            echo '</tr>';
        }
    }

    public function tableRowStart ()
    {
        echo '<tr>';
    }

    public function tableRowValue ($row_value, $is_first = FALSE, $row_width = 0)
    {
        if ($is_first)
            echo '<th scope="row">' . $row_value. '</th>';
        else
        {
            if ($row_width != 0)
                echo '<td width="' . $row_width . '%">' . $row_value . '</td>';
            else
                echo '<td>' . $row_value . '</td>';
        }
    }

    public function tableRowEnd ()
    {
        echo '</tr>';
    }

    public function tableEnd ()
    {
        echo '</table>';
    }
}

?>