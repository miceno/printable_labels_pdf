<?php
// Namespace for PSR-4 autoloading
namespace PrintableLabelsPdf;

// Require MPDF composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Class for printable labels in pdf format
class PrintableLabelsPdf
{
    private $page_format;
    private $page_orientation;
    private $margin_left_page;
    private $margin_top_page;
    private $margin_left_label;
    private $margin_bottom_label;
    private $width_label;
    private $height_label;
    private $padding_left_label;
    private $padding_top_label;
    private $num_cols;
    private $num_rows;
    private $skip_first_row;
    private $skip_last_row;
    private $default_font_size;
    private $default_font;
    private $begin_at_label_num;
    private $label_style;
    private $skipped_label_style;
    private $arr_labels_html;
    private $draw_border;
    private $flux_page_num;
    private $flux_col_num;
    private $flux_row_num;
    private $flux_id_label;
    private $flux_start_at_label;
    private $mpdf;

    public function __construct($labels_config)
    {
        $this->width_label = $labels_config['width_label'];
        $this->height_label = $labels_config['height_label'];
        $this->num_rows = $labels_config['num_rows'];
        $this->num_cols = ((isset($labels_config['num_cols'])) ? $labels_config['num_cols'] : 2);                        // If not set, 2 cols by default
        $this->margin_left_page = ((isset($labels_config['margin_left_page'])) ? $labels_config['margin_left_page'] : 0);        // If not set, 0 by default
        $this->margin_top_page = ((isset($labels_config['margin_top_page'])) ? $labels_config['margin_top_page'] : 0);        // If not set, 0 by default
        $this->margin_left_label = ((isset($labels_config['margin_left_label'])) ? $labels_config['margin_left_label'] : 0);    // If not set, 0 by default
        $this->margin_bottom_label = ((isset($labels_config['margin_bottom_label'])) ? $labels_config['margin_bottom_label'] : 0); // If not set, 0 by default
        $this->padding_left_label = ((isset($labels_config['padding_left_label'])) ? $labels_config['padding_left_label'] : 0);    // If not set, 0 by default
        $this->padding_top_label = ((isset($labels_config['padding_top_label'])) ? $labels_config['padding_top_label'] : 0);    // If not set, 0 by default
        $this->skip_first_row = ((isset($labels_config['skip_first_row'])) ? $labels_config['skip_first_row'] : false);        // If not set, false by default
        $this->skip_last_row = ((isset($labels_config['skip_last_row'])) ? $labels_config['skip_last_row'] : false);        // If not set, false by default
        $this->page_format = ((isset($labels_config['page_format'])) ? $labels_config['page_format'] : 'A4');                // If not set, 'A4' by default
        $this->page_orientation = ((isset($labels_config['page_orientation'])) ? $labels_config['page_orientation'] : 'P');    // If not set, 'P'ortrait by default
        $this->default_font = ((isset($labels_config['default_font'])) ? $labels_config['default_font'] : 'Times');        // If not set, 'Times'
        $this->default_font_size = ((isset($labels_config['default_font_size'])) ? $labels_config['default_font_size'] : 0);    // If not set, 0 that means CSS default
        $this->draw_border = ((isset($labels_config['draw_border'])) ? $labels_config['draw_border'] : false);    // If not set, false that means without borders
        $this->flux_start_at_label = ((isset($labels_config['start_at_label'])) ? $labels_config['start_at_label'] : 1); // If not set, 1 that means at first label


        $this->margin_left_page -= $this->margin_left_label;


        $this->begin_at_label_num = $labels_config['begin_at_label_num'];
        $this->arr_labels_html = array();

        $this->flux_page_num = 1;
        $this->flux_col_num = 1;
        $this->flux_row_num = 1;
        $this->flux_id_label = 0;

        // Create array values of mpdf config
        $mpdf_config = [];

        $mpdf_config['format'] = $this->page_format;
        $mpdf_config['orientation'] = $this->page_orientation;
        $mpdf_config['default_font_size'] = $this->default_font_size;
        $mpdf_config['default_font'] = $this->default_font;
        $mpdf_config['margin_left'] = $this->margin_left_page * 10; // * 10 = cm.
        $mpdf_config['margin_top'] = $this->margin_top_page * 10; // * 10 = cm.
        $mpdf_config['margin_right'] = 0;
        $mpdf_config['margin_bottom'] = 0;

        // Create an instance of the mpdf class:
        $this->mpdf = new \Mpdf\Mpdf($mpdf_config);

        $this->mpdf->SetTitle('Printable Labels Pdf');
        $this->mpdf->SetAuthor('Rafael Martin Soto - https://www.inatica.com');
        $this->mpdf->SetSubject('Printable Lables in pdf format with https://github.com/vivesweb/printable_labels_pdf library');
        $this->mpdf->SetKeywords('Labels, pdf, Rafael Martin Soto, php');

        $this->set_label_style($labels_config);
        // Free mem
        unset($mpdf_config);
    }

    public function get_labels_pdf($file_name_pdf = null, $output_format = 'I')
    {
        $DateTimeBegin = new \DateTime();

        // mpdf is a little slow. Then we need to control the response time of the server
        // By default, we have unlimited time, but if you prefer limit less time, you can use set_time_limit( int $seconds);

        set_time_limit(0); // unlimited time

        $labels_html = '';
        $count_labels = count($this->arr_labels_html);

        $this->flux_page_num = 1;
        $this->flux_col_num = 1;
        $this->flux_row_num = 1;
        $this->flux_id_label = 0;

        do {
            $num_next_label_in_page = ($this->flux_col_num + ($this->flux_row_num - 1) * $this->num_cols);

            if ($this->flux_row_num == 1 && $this->flux_col_num == 1 && $this->skip_first_row) {
                // if need to skip first row
                for ($i = 0; $i < $this->num_cols; $i++) {
                    $labels_html .= $this->write_skipped_label();
                }
                $this->flux_row_num = 2;
                $this->flux_col_num = 1;
            } else if ($this->flux_col_num > $this->num_cols) {
                // if need to go next row
                $this->flux_col_num = 1;
                $this->flux_row_num++;
            } else if ($this->flux_row_num > $this->num_rows ||
                $this->flux_col_num == 1 && $this->flux_row_num == $this->num_rows && $this->skip_last_row) {
                // If we need to make new page
                // or need to skip last row

                $this->flux_col_num = 1;
                $this->flux_row_num = 1;
                $this->flux_page_num++;

                $this->mpdf->WriteHTML('<div>' . $labels_html . '</div>');
                $this->mpdf->AddPage();
                $labels_html = '';
            } else if ($this->flux_page_num == 1 && $this->begin_at_label_num > $num_next_label_in_page) {
                // Skip label becouse we want to begin at later label
                $labels_html .= $this->write_skipped_label();

                $this->flux_col_num++;

            } else {
                $labels_html .= $this->arr_labels_html[$this->flux_id_label];

                $this->flux_col_num++;
                $this->flux_id_label++;
            }
        } while ($this->flux_id_label < $count_labels);

        if ($labels_html != '') {
            $this->mpdf->WriteHTML('<div>' . $labels_html . '</div>');
        }


        if ($file_name_pdf == null) {
            $file_name_pdf = 'labels.pdf';
        }

        // Calculate Time Diff
        $DateTimeEnd = new \DateTime();

        $DateBeginStr = $DateTimeBegin->format('Y-m-d H:i:s');
        $DateEndStr = $DateTimeEnd->format('Y-m-d H:i:s');

        $differenceFormat = '%i Minutes & %s Seconds';
        $datetime1 = date_create($DateEndStr);
        $datetime2 = date_create($DateBeginStr);

        $interval = date_diff($datetime1, $datetime2);

        $this->mpdf->SetCreator('printable labels pdf class, created in ' . $interval->format($differenceFormat));

        unset($labels_html);
        unset($label_html);
        unset($interval);
        unset($differenceFormat);
        unset($datetime1);
        unset($datetime2);
        unset($DateBeginStr);
        unset($DateEndStr);
        unset($DateTimeEnd);
        unset($DateTimeBegin);

        return $this->mpdf->Output($file_name_pdf, $output_format);
    }

    public function draw_border($draw_border = true)
    {
        $this->draw_border = $draw_border;

        if ($this->draw_border) {
            $this->label_style .= 'border-width:1px;border-color:#000;border-style:solid;';
            $this->skipped_label_style .= 'border-width:1px;border-color:#FFF;border-style:solid;';
        } else {
            $this->label_style .= 'border-width:0px;';
            $this->skipped_label_style .= 'border-width:0px;';
        }
    }

    public function set_label_style()
    {
        $this->label_style = ''; // init style to ''

        $this->label_style .= 'float: left;';
        $this->label_style .= 'width:' . $this->width_label . 'cm;';
        $this->label_style .= 'height:' . $this->height_label . 'cm;';
        $this->label_style .= 'margin-left:' . $this->margin_left_label . 'cm;';
        $this->label_style .= 'margin-bottom:' . $this->margin_bottom_label . 'cm;';
        $this->label_style .= 'padding-left:' . $this->padding_left_label . 'cm;';
        $this->label_style .= 'padding-top:' . $this->padding_top_label . 'cm;';

        $this->skipped_label_style = $this->label_style;

        if ($this->draw_border) {
            $this->label_style .= 'border-width:1px;border-color:#000;border-style:solid;';
            $this->skipped_label_style .= 'border-width:1px;border-color:#FFF;border-style:solid;';
        }

    }

    // Write a label in pdf
    public function write_label($html_content = '')
    {
        // Build the label HTML using the current style
        $label_html = '<div style="' . $this->label_style . '">';
        $label_html .= $html_content;
        $label_html .= '</div>';

        // Add the label HTML to the array
        $this->arr_labels_html[] = $label_html;
    }

    public function write_skipped_label()
    {
        $label_html = '';

        $label_html .= '<div style="' . $this->skipped_label_style . '">';
        $label_html .= '&nbsp;';
        $label_html .= '</div>';

        return $label_html;

    }
}
