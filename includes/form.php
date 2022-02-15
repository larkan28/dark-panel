<?php

class PanelForm
{
    public $g_formPrefix;

    public function __construct ($form_prefix)
	{
		$this->g_formPrefix = $form_prefix;
	}

    public function formStart ($form_action, $form_method = "post")
    {
        echo '<form action="' . $form_action . '" method=' . $form_method . '>';
    }

    public function formField ($input_type, $input_name, $input_title, $input_value = "", $input_extra = "")
    {
        echo '<div class="form-group">';
        echo '<label>' . $input_title . '</label>';
        echo '<input type="' . $input_type . '" class="form-control" name="form' . $this->g_formPrefix . '_' . $input_name . '" placeholder="' . $input_title . '" value="' . $input_value . '" ' . $input_extra . '>';
        echo '</div>';
    }

    public function formCheck ($input_name, $input_title, $input_extra = "", $input_class = "")
    {
        $check_id = "formCheck_" . $input_name;

        echo '<div class="custom-control custom-checkbox">';
        echo '<input type="checkbox" class="custom-control-input ' . $input_class . '" id="' . $check_id . '" name="form' . $this->g_formPrefix . '_' . $input_name . '" ' . $input_extra . '>';
        echo '<label class="custom-control-label" for="' . $check_id . '">' . $input_title . '</label>';
        echo '</div>';
    }

    public function formFieldIcon ($input_type, $input_name, $input_holder, $input_icon, $input_value = "")
    {
        echo '<div class="form-group">';
        echo '<div class="input-group">';
        echo '<div class="input-group-prepend">';
        echo '<span class="input-group-text"><i class="fa fa-' . $input_icon . '"></i></span>';
        echo '</div>';
        echo '<input type="' . $input_type . '" class="form-control" name="form' . $this->g_formPrefix . '_' . $input_name . '" placeholder="' . $input_holder . '" value="' . $input_value . '">';
        echo '</div>';
        echo '</div>';
    }

    public function formFieldHidden ($input_name, $input_value)
    {
        echo '<input type="hidden" name="form' . $this->g_formPrefix . '-' . $input_name . '" value="' . $input_value . '">';
    }
    
    public function formFieldSelect ($select_name, $select_title, $option_values, $option_names)
    {
        echo '<div class="form-group">';
        echo '<label>' . $select_title . '</label>';
        echo '<select multiple class="form-control" name="form' . $this->g_formPrefix . '_' . $select_name . '">';

        for ($i = 0; $i < count($option_values); $i++)
            echo '<option value="' . $option_values[$i] . '">' . $option_names[$i] . '</option>';

        echo '</select>';
        echo '</div>';
    }

    public function formSelectStartIcon ($select_name, $select_icon, $select_class = "", $select_extra = "")
    {
        echo '<div class="form-group">';
        echo '<div class="input-group">';
        echo '<div class="input-group-prepend">';
        echo '<span class="input-group-text"><i class="fa fa-' . $select_icon . '"></i></span>';
        echo '</div>';
        echo '<select class="form-control ' . $select_class . '" name="form' . $this->g_formPrefix . '_' . $select_name . '" ' . $select_extra . '>';
    }

    public function formSelectStart ($select_name, $select_title, $select_class = "", $select_extra = "")
    {
        echo '<div class="form-group">';
        echo '<label>' . $select_title . '</label>';
        echo '<select class="form-control ' . $select_class . '" name="form' . $this->g_formPrefix . '_' . $select_name . '" ' . $select_extra . '>';
    }

    public function formSelectOption ($option_name, $option_value, $option_extra = "")
    {
        echo '<option value="' . $option_value . '" ' . $option_extra . '>' . $option_name . '</option>';
    }

    public function formSelectEndIcon ()
    {
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public function formSelectEnd ()
    {
        echo '</select>';
        echo '</div>';
    }

    public function formFieldAlert ($alert_class, $alert_message)
    {
        echo '<div class="alert alert-' . $alert_class .  '" role="alert">';
        echo $alert_message;
        echo '</div>';
    }

    public function formButton ($btn_class, $btn_name, $btn_label, $btn_end = "")
    {
        echo '<button type="submit" class="btn ' . $btn_class . '" name="btn-' . $btn_name .  '">' . $btn_label . '</button>' . $btn_end;
    }

    public function formEnd ()
    {
        echo '</form>';
    }
}

?>