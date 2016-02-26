<?php
defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'mod/tab:addinstance' => array(
        'riskbitmask'  => RISK_XSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    'mod/tab:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'guest' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),
);
?>
