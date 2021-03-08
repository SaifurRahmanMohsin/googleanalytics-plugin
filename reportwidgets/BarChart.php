<?php namespace Mohsin\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

/**
 * BarChart Report Widget
 */
class BarChart extends ReportWidgetBase
{
    use \Mohsin\GoogleAnalytics\Traits\DataTrait;

    /**
     * Defines the widget's properties
     * @return array
     */
    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.widget_title',
                'default'           => 'Bar Chart',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.barchart.widget_title_required'
            ],
            'dimension' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.dimension',
                'type'              => 'dropdown'
            ],
            'orderByDimension' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.orderby_dimension',
                'type'              => 'dropdown'
            ],
            'metric' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.metric',
                'default'           => 'ga:visits',
                'type'              => 'dropdown'
            ],
            'reportHeight' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.chart_height',
                'default'           => '200',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.barchart.invalid_chart_height'
            ],
            'legendAsTable' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.legend_as_table',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'days' => [
                'title'             => 'mohsin.googleanalytics::lang.widgets.days_to_display',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'limit' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.results_to_display',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'description'       => 'mohsin.googleanalytics::lang.barchart.zero_displays_all'
            ],
            'hideNotSet' => [
                'title'             => 'mohsin.googleanalytics::lang.settings.hide_not_set',
                'type'              => 'checkbox',
                'default'           => 0
            ]
        ];
    }

    /**
     * Fetch the analytics metadata
     */
    public function init()
    {
        $this->loadMeta();
    }
    
    /**
     * Render the widget data
     */
    protected function renderData()
    {
        $data = $this->loadData();

        $rows = $data->getRows() ?: [];
        $limit = $this->property('limit') ?: 0;
        $this->vars['rows'] = $limit > 0
            ? array_slice($rows, 0, $limit)
            : $data->getRows();

        $this->vars['total'] = $data->getTotals();
    }
}
