<?php namespace Mohsin\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

/**
 * PercentageChart Report Widget
 */
class PercentageChart extends ReportWidgetBase
{
    use \Mohsin\GoogleAnalytics\Traits\DataTrait;

    /**
     * Define widget properties
     * @return  array
     */
    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.widget_title',
                'default'           => 'mohsin.googleanalytics::lang.percentagechart.name',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.percentagechart.title_required'
            ],
            'dimension' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.dimension',
                'type'              => 'dropdown'
            ],
            'metric' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.metric',
                'type'              => 'dropdown'
            ],
            'dimensionLabel' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.dimension_label',
                'type'              => 'string'
            ],
            'metricLabel' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.metric_label',
                'type'              => 'string'
            ],
            'days' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.days_to_display',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'limit' => [
                'title'             => 'mohsin.googleanalytics::lang.percentagechart.results_to_display',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'description'       => 'mohsin.googleanalytics::lang.percentagechart.zero_displays_all'
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
        $limitedRows = $this->vars['rows'] = $limit > 0
            ? array_slice($rows, 0, $limit)
            : $rows;

        $this->vars['total'] = array_reduce($limitedRows, function ($totalSum, $currentRow) {
            return $totalSum += array_reduce($currentRow->getMetricValues(), function ($sum, $current) {
                return $sum += $current->getValue();
            });
        });
    }
}
