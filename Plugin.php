<?php namespace Mohsin\GoogleAnalytics;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Google Analytics',
            'description' => 'mohsin.googleanalytics::lang.strings.plugin_desc',
            'author'      => 'Saifur Rahman Mohsin',
            'icon'        => 'icon-bar-chart-o',
            'homepage'    => 'https://github.com/mohsin/googleanalytics-plugin'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Mohsin\GoogleAnalytics\Components\Tracker' => 'googleTracker'
        ];
    }

    public function registerPermissions()
    {
        return [
            'mohsin.googleanalytics.access_settings' => [
                'tab'   => 'mohsin.googleanalytics::lang.permissions.tab',
                'label' => 'mohsin.googleanalytics::lang.permissions.settings'
            ],
            'mohsin.googleanalytics.view_widgets' => [
                'tab'   => 'mohsin.googleanalytics::lang.permissions.tab',
                'label' => 'mohsin.googleanalytics::lang.permissions.widgets'
            ]
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'Mohsin\GoogleAnalytics\ReportWidgets\LineChart' => [
                'label'   => 'mohsin.googleanalytics::lang.linechart.label',
                'context' => 'dashboard'
            ],
            'Mohsin\GoogleAnalytics\ReportWidgets\BarChart' => [
                'label'   => 'mohsin.googleanalytics::lang.barchart.label',
                'context' => 'dashboard'
            ],
            'Mohsin\GoogleAnalytics\ReportWidgets\PieChart' => [
                'label'   => 'mohsin.googleanalytics::lang.piechart.label',
                'context' => 'dashboard'
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'Google Analytics 4',
                'icon'        => 'icon-bar-chart-o',
                'description' => 'mohsin.googleanalytics::lang.strings.settings_desc',
                'class'       => 'Mohsin\GoogleAnalytics\Models\Settings',
                'permissions' => ['mohsin.googleanalytics.access_settings'],
                'order'       => 601
            ]
        ];
    }
}
