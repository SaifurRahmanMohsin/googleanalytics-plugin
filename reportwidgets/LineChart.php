<?php namespace Mohsin\GoogleAnalytics\ReportWidgets;

use Lang;
use ApplicationException;
use Backend\Classes\ReportWidgetBase;

/**
 * LineChart Report Widget
 */
class LineChart extends ReportWidgetBase
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
                'default'           => 'mohsin.googleanalytics::lang.linechart.name',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'mohsin.googleanalytics::lang.barchart.widget_title_required'
            ],
            'dimension' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.dimension',
                'type'              => 'dropdown'
            ],
            'metric' => [
                'title'             => 'mohsin.googleanalytics::lang.barchart.metric',
                'default'           => 'sessions',
                'type'              => 'dropdown'
            ],
            'showLegend' => [
                'title'             => 'mohsin.googleanalytics::lang.linechart.show_legend',
                'type'              => 'checkbox',
                'default'           => 1
            ],
            'days' => [
                'title'             => 'mohsin.googleanalytics::lang.widgets.days_to_display',
                'default'           => '30',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
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
        $rows = $limit > 0
            ? array_slice($rows, 0, $limit)
            : $data->getRows();

        $points = [];
        foreach ($rows as $row) {
            $timeValue = $row->getDimensionValues()[0]->getValue();
            $value = $row->getMetricValues()[0]->getValue();

            if (!(bool)strtotime($timeValue)) {
                throw new ApplicationException(Lang::get('mohsin.googleanalytics::lang.linechart.invalid_dimension_value'));
            }
            if (!(is_numeric($value))) {
                throw new ApplicationException(Lang::get('mohsin.googleanalytics::lang.linechart.invalid_metric_value'));
            }
            $point = [
                strtotime($timeValue)*1000,
                $value
            ];
            $points[] = $point;
        }

        $this->vars['metric'] = $this->availableMetricsCache[$this->property('metric')];
        $this->vars['rows'] = str_replace('"', '', substr(substr(json_encode($points), 1), 0, -1));
        $this->vars['total'] = $data->getTotals();
    }
}
