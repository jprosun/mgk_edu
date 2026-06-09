<?php
class Password_Reset_Removed
{

    function __construct()
    {
        add_filter('show_password_fields', array($this, 'disable_change_password'));
        add_filter('allow_password_reset', array($this, 'disable_change_password'));
        add_filter('gettext', array($this, 'remove_change_password'));

        add_filter('wp_is_application_passwords_available', array($this, 'disable_application_password'));
        add_action('show_user_profile', array($this, 'disable_application_password_front'));
        add_action('edit_user_profile', array($this, 'disable_application_password_front'));
    }

    function disable_change_password()
    {
        return false;
    }

    function disable_application_password()
    {
        return false;
    }

    function disable_application_password_front()
    {
        echo '<script>jQuery(".application-passwords").remove();</script>';
    }

    function remove_change_password($text)
    {
        return str_replace(array('Lost your password?', 'Lost your password'), '', trim($text, '?'));
    }
}


