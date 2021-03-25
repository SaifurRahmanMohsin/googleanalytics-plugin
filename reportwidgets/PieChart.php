<?php namespace Mohsin\GoogleAnalytics\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

/**
 * PieChart Report Widget
 */
class PieChart extends ReportWidgetBase
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
                'title'             => 'mohsin.googleanalytics::lang.piechart.widget_title',
                'default'           => 'mohsin.googleanalytics::lang.piechart.name',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.piechart.title_required'
            ],
            'dimension' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.dimension',
                'type'              => 'dropdown'
            ],
            'metric' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.metric',
                'type'              => 'dropdown'
            ],
            'reportSize' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.chart_radius',
                'default'           => '150',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.piechart.chart_size_invalid'
            ],
            'center' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.center_chart',
                'type'              => 'checkbox'
            ],
            'legendAsTable' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.legend_as_table',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'displayTotal' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.display_total',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'days' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.days_to_display',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
            'limit' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.results_to_display',
                'default'           => '10',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'description'       => 'mohsin.googleanalytics::lang.piechart.zero_displays_all'
            ],
            'description' => [
                'title'             => 'mohsin.googleanalytics::lang.piechart.report_description'
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
