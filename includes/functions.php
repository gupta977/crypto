<?php
function crypto_get_option($field_name, $section = 'flexi_icon_settings', $default = '')
{
    //Example
    //flexi_get_option('field_name', 'setting_name', 'default_value');

    $options = (array) get_option($section);

    if (isset($options[$field_name])) {
        return $options[$field_name];
    } else {
        //Set the default value if not found
        crypto_set_option($field_name, $section, $default);
    }

    return $default;
}

//Set options in settings
function crypto_set_option($field_name, $section = 'flexi_icon_settings', $default = '')
{
    //Example
    //flexi_set_option('field_name', 'setting_name', 'default_value');
    $options              = (array) get_option($section);
    $options[$field_name] = $default;
    update_option($section, $options);

    return;
}
