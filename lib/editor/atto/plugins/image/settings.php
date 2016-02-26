<?php
/**
 * Settings that allow configuration of the image plugin for atto.
 *
 * @package    atto_image
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$pluginname = new lang_string('pluginname', 'atto_image');

$ADMIN->add('editoratto', new admin_category('atto_image', $pluginname));

$settings = new admin_settingpage('atto_image_settings', new lang_string('settings', 'atto_image'));
if ($ADMIN->fulltree) {
    // Image resize settings.
    {
        $name = new lang_string('imageresizesettingheading', 'atto_image');
        $settingdescription = 'Settings for Image resizing';
        $settingheading = new admin_setting_heading('atto_image/imageresizesettingheading', $name, $settingdescription);
        $settings->add($settingheading);

        // Available resize handle.
        {
            $name = new lang_string('availableresizehandle', 'atto_image');
            $description =
                '<br/>Sets the available resize handle of <b>' . $pluginname . '</b> plugin.<br/>' .
                '<p>Default is all resize handle are shown: n, s, w, e, ne, nw, se, sw</p>';
            $default = 'n, s, w, e, ne, nw, se, sw';
            $setting = new admin_setting_configtextarea('atto_image/availableresizehandle', $name, $description, $default, PARAM_RAW);
            $settings->add($setting);
        }

        // min/max width/height.
        {
            $name = new lang_string('minmaxwidthheight', 'atto_image');
            $example =
                "{\n".
                "  \"min_width\": 100,\n".
                "  \"min_height\": 100,\n".
                "  \"max_width\": 1000000,\n".
                "  \"max_height\": 1000000\n".
                "}\n";
            $description =
                '<br/>Sets the min/max width/height for <b>' . $pluginname . '</b> plugin.<br/>' .
                '<p>This setting is in JSON format, and example and default would be the following:</p>'.
                '<pre>'.
                $example.
                '</pre>'.
                '<p>Not all of them are required (even an empty brace is valid). <b>max_width</b> of 10000000 is selected '.
                'for a reason. Different browsers have different lower bounds on maximum pixel size on stylesheets.</p>';
            $default =
                '{
                  "min_width": 100,
                  "min_height": 100,
                  "max_width": 1000000,
                  "max_height": 1000000
                }';
            $setting = new admin_setting_configtextarea('atto_image/minmaxwidthheight', $name, $description, $default, PARAM_TEXT);
            $settings->add($setting);
        }

        // preserve constrain key code
        {
            $name = new lang_string('togglekeypreserveaspectratio', 'atto_image');
            $description =
                '<br/>Selects the toggle key for preserving constrain while resizing <b>' . $pluginname . '</b> plugin.<br/>' .
                '<p>meta/windows key is not included to avoid dealing with OS idiosyncrasies.</p>';
            $default = 'ctrl';
            $choices = array(
                'ctrl' => 'ctrl',
                'alt' => 'alt',
                'shift' => 'shift'
            );

            $setting = new admin_setting_configselect('atto_image/togglekeypreserveaspectratio',
                $name, $description, $default, $choices);
            $settings->add($setting);
        }
    }

    // Image styling
    {
        $name = new lang_string('imagestylingsettingheading', 'atto_image');
        $imagestylinginfo = 'Image styling settings';
        $settingheading = new admin_setting_heading('atto_image/imagestylingsettingheading', $name, $imagestylinginfo);
        $settings->add($settingheading);

        // Custom classes.
        {
            $name = new lang_string('disablecustomclasses', 'atto_image');
            $description =
                '<br/>Disables custom classes feature for <b>' . $pluginname . '</b> plugin.<br/>' .
                '<p>Although certain classes are blocked from being shown and added to and from user, there is still '.
                'chance that this is not sufficient. To accommodate unforeseeable future disaster due to some user '.
                'managing to insert some malicious class, this is here just in case.</p>';
            $default = 0;
            $setting = new admin_setting_configcheckbox('atto_image/disablecustomclasses', $name, $description, $default);
            $settings->add($setting);
        }
    }

    // Animation
    {
        $name = new lang_string('resizeanimationheading', 'atto_image');
        $imagestylinginfo = 'Image styling settings';
        $settingheading = new admin_setting_heading('atto_image/resizeanimationheading', $name, $imagestylinginfo);
        $settings->add($settingheading);

        // Animation enable
        {
            $name = new lang_string('resizeanimationenable', 'atto_image');
            $description = '<br/>Enable resize animation for <b>' . $pluginname . '</b> plugin.<br/>';
            $default = 1;  // Enable by default.
            $setting = new admin_setting_configcheckbox('atto_image/resizeanimationenable', $name, $description, $default);
            $settings->add($setting);
        }

        // Animation duration
        {
            $name = new lang_string('resizeanimationduration', 'atto_image');
            $description = '<br/>Duration of resize animation in seconds for <b>' . $pluginname . '</b> plugin.<br/>';
            $default = 0.4;

            // Ensure that input was floating point format.
            $setting = new admin_setting_configtext('atto_image/resizeanimationduration',
                $name, $description, $default, '/[0-9]*\.?[0-9]*/', 99);
            $settings->add($setting);
        }

        // Animation easing
        {
            $name = new lang_string('resizeeasing', 'atto_image');
            $description = '<br/>Resize easing for <b>' . $pluginname . '</b> plugin.<br/>';
            $default = 0.4;
            $default = 'easeBoth';
            $choices = array(
                'backBoth' => 'backBoth',
                'backIn' => 'backIn',
                'backOut' => 'backOut',
                'bounceBoth' => 'bounceBoth',
                'bounceIn' => 'bounceIn',
                'bounceOut' => 'bounceOut',
                'easeBoth' => 'easeBoth',
                'easeBothStrong' => 'easeBothStrong',
                'easeIn' => 'easeIn',
                'easeInStrong' => 'easeInStrong',
                'easeNone' => 'easeNone',
                'easeOut' => 'easeOut',
                'easeOutStrong' => 'easeOutStrong',
                'elasticBoth' => 'elasticBoth',
                'elasticIn' => 'elasticIn',
                'elasticOut' => 'elasticOut'
            );

            $setting = new admin_setting_configselect('atto_image/resizeeasing', $name, $description, $default, $choices);
            $settings->add($setting);
        }
    }
}