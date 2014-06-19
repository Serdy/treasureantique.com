<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */
if (!class_exists('SC_FieldControls')) {
    class SC_FieldControls
    {
        public function __construct()
        {
            add_action("SC_create_field_input", array($this, 'input'), 1, 2);
            add_action("SC_create_field_datetime", array($this, 'datetime'), 1, 2);
            add_action("SC_create_field_money", array($this, 'money'), 1, 2);
            add_action("SC_create_field_select", array($this, 'select'), 1, 2);
            add_action("SC_create_field_checkboxes", array($this, 'checkboxes'), 1, 2);
            add_action("SC_create_field_radios", array($this, 'radios'), 1, 2);
            add_action("SC_create_field_textarea", array($this, 'textarea'), 1, 2);
            add_action("SC_create_field_editor", array($this, 'editor'), 1, 2);
            add_action("SC_create_field_image", array($this, 'image'), 1, 2);
            add_action("SC_create_field_uploader", array($this, 'uploader'), 1, 2);
            add_action("SC_create_field_custom", array($this, 'custom'), 1, 2);
            add_action("SC_create_field_group", array($this, 'group'), 1, 2);
        }

        public static function render($field)
        {
            extract($field);
            $value = isset($value) ? $value : '';

            if (isset($type) && isset($name)) {
                $value = apply_filters('NSPB_format_value_' . $type, $value);
                $required = isset($required) ? $required : false;
                $requiredLabel = $required ? ' <span class="required">*</span>' : '';
                $class = isset($class) ? $class : "";
                $id = "SC_" . $name;
                $label = isset($label) ? $label : $name;

                $symbol = $type == "money" ? "&nbsp;" . get_option('dam_currency_symbol', '&euro;') : '';

                echo "<tr id='".$id."_wrap' >";
                echo "<th scope='row' class='label'>";
                echo '<label for="' . $id . '">' . $label . $requiredLabel . $symbol . '</label>';
                echo '</th>';

                if ($required)
                    $class .= " required";

                if(empty($value) && isset($default))
                {
                    $value = $default;
                }

                $entry = compact("id", "name", "value", "class", "title", "style", "options", "description", "multiple", "items");
                array_filter($entry);

                echo "<td>";
                do_action('SC_create_field_' . $type, $entry);
                echo '</td></tr>';
            }
        }

        public function input($field, $hidden = false)
        {
            $type = $hidden ? "hidden" : "text";
            $name = $field['name'];
            $value = esc_attr( stripslashes($field['value']));
            $id = $field['id'];
            $class =  $field['class'];
            $style = isset($field['style'])?$field['style']:false;
            $style = empty($style)?"":"style='$style'";
            $html = "<input id='$id' class='$class' $style type='$type' name='$name' value='$value' />";
            if(isset($field['description']))
            {
                $description = $field['description'];
                $html .= "<p class='description'>$description</p>";
            }
            echo $html;
        }

        public function custom($field)
        {
            $this->input($field, true);
            if (isset($field['value']) && !empty($field['value'])) {
                $value = stripslashes($field['value']);
            } else {
                $value = "[]";
            }
            $tags = SC_functions::get_property_keys();
            $expression = " var properties = $value; var availableTags=[$tags];";
            echo "<script type='text/javascript'>$expression</script>";

            ?>
            <table id='custom_properties' class="table table-bordered" style="width: inherit;">
                <tr>
                    <th style="width: 150px;"><?php _e("Property name", DutchAuctionMasters::TEXT_DOMAIN) ?></th>
                    <th style="width: 150px;"><?php _e("Property value", DutchAuctionMasters::TEXT_DOMAIN) ?></th>
                    <th colspan="3" style="width: 85px; vertical-align: middle;">
                        <a style="top:inherit; background:none;" href="javascript:;" class="add-new-h2"
                           data-bind="click:$root.addProperty">
                            <?php _e("add new", DutchAuctionMasters::TEXT_DOMAIN); ?> </a>
                    </th>
                </tr>
                <!-- ko foreach:properties -->
                <tr class="auction-props">
                    <td><input class="auto-complete"
                               data-bind="attr: { name: 'Properties['+ $index() +']' }, value: key"
                               type="text"/></td>
                    <td><input
                            data-bind="attr: { name: 'Property_value['+ $index() +']' },value: value"
                            type="text"/></td>
                    <td style="text-align: center;"><span
                            data-bind="click:$root.removeProperty" class="icon-remove btn"></span>
                    </td>
                    <td><span data-bind="click:$root.moveUp,css:{disable:$index()==0}" class="icon-arrow-up btn"></span>
                    </td>
                    <td><span data-bind="click:$root.moveDown,css:{disable:$root.length() == $index()}"
                              class="icon-arrow-down btn"></span></td>
                </tr>
                <!-- /ko -->
            </table>
        <?php
        }

        public function datetime($field)
        {
            $field['class'] = $field['class'] . " datetime";
            $this->input($field);
        }

        public function money($field)
        {
            $this->input($field);
        }

        public function image($field)
        {
            extract($field);
            $value = isset($value) ? $value : "";
            $id = isset($name)?"SC_" . $name: '';
            $src =  SC_functions::getMainPictureUrl($value);
            $value = esc_attr($value);
            $html ="<img class='imgsrc' src='$src' style='width: 128px;'/>";
            $html .="<input name='$name' id='$id' type='hidden' value='$value' />";
            $html .= "<a class='upload_image_button' href='javascript:;' title='Add Media'>Upload/Insert <img src='/wp-admin/images/media-button.png?ver=20111005' width='15' height='15'/>
            </a>";

            $html = apply_filters("SC_render_image",$html, $field);
            echo $html;
        }

        public function uploader($field)
        {

        }

        public function select($field)
        {
            $options = $field['options'];
            $options = $this->applyFilterOption($options);
            $name = $field['name'];
            $id = 'SC_'.$field['name'];
            $selected = $field['value'];
            $multiple = isset($field['multiple'])? "multiple='multiple'":'';

            if(!empty($multiple))
                $name = $name.'[]';

            $html = " <select name='$name' $multiple id='$id'>";

            foreach ($options as $key => $value) {
                if(empty($multiple))
                    $checked = ($selected == $key) ? "selected='selected'" : "";
                else if(is_array($selected))
                    $checked = (in_array($key, $selected )) ? "selected='selected'" : "";
                else
                {
                    $checked = ($selected == $key) ? "selected='selected'" : "";
                }
                $html .= "<option value='$key' $checked /> $value </option>";
            }
            $html .= "</select>";
            if(isset($field['description']))
            {
                $description = $field['description'];
                $html .= "<p class='description'>$description</p>";
            }
            echo $html;
        }

        public function group($field)
        {
            if(isset($field['items']))
            {
                foreach($field['items'] as $key => $value)
                {
                    $value['name'] = $key;
                    $value['id'] = 'SC_'.$key;
                    $label = isset($value['label'])?$value['label']:'';
                    $value['value'] = isset($value['value'])?$value['value']:'';

                    if(empty($value['value']) && isset($value['default']))
                        $value['value'] = $value['default'];

                    $value['class'] = isset($value['class'])?$value['class']:'';
                    echo "<div style='margin-bottom:5px;'><span>$label</span> : ";
                    do_action('SC_create_field_' . $value['type'], $value);
                    echo "</div>";
                }
            }
        }

        public function checkboxes($field)
        {

        }

        public function radios($field)
        {
            $options = $field['options'];
            $name = $field['name'];
            $selected = $field['value'];
            $html = "";
            foreach ($options as $key => $value) {
                $checked = ($selected == $key) ? "checked" : "";
                $html .= "<label ><input type='radio' name='$name' value='$key' $checked /> $value </label> &nbsp;&nbsp;";
            }

           if(isset($field['description']))
           {
               $description = $field['description'];
               $html .= "<p class='description'>$description</p>";
           }
           echo $html;
        }

        public function textarea($field)
        {
            $name = $field['name'];
            $value = esc_attr( stripslashes($field['value']));
            $id = $field['id'];
            $class =  $field['class'];
            $style = isset($field['style'])?$field['style']:false;
            $style = empty($style)?"":"style='$style'";
            $html = "<div class='nspb-input-wrap' ><textarea id='$id' class='$class' $style name='$name'  />$value</textarea>";
            if(isset($field['description']))
            {
                $description = $field['description'];
                $html .= "<p class='description'>$description</p>";
            }
            $html .="</div>";
            echo $html;
        }

        public function editor($field)
        {
            echo "<div>";
            wp_editor($field['value'], $field['id'], array('media_buttons' => true, 'wpautop' => true));
            if(isset($field['description']))
            {
                $description = $field['description'];
                echo "<p class='description'>$description</p>";
            }
            echo "</div>";
        }

        public function applyFilterOption($options)
        {
            if (isset($options))
            {
                if ( is_array($options))
                {
                    $instance = reset($options);
                    if(count($options)>0 && is_object($instance))
                    {
                        return call_user_func($options);
                    }
                }
            }
            return $options;
        }
    }

    new SC_FieldControls();
}