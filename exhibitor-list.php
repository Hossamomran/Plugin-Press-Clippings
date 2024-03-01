<?php
/*
Plugin Name:  Exhibitor-list plugin
Description:  A plugin created to read excelsheet , and show it's data with a filter functionality.
Version:      2.0
Author:       HossamOmran
Author URI:   https://github.com/Hossamomran/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/
require_once(plugin_dir_path(__FILE__) . 'settings-page.php');
function exhibitor_clippings_activate() {
    // Delete previously saved option names for exhibitor Clippings
    delete_option('exhibitor_list_option_name'); // Update this line
    delete_option('your_unique_exhibitor_list_option_name');
}
register_activation_hook(__FILE__, 'exhibitor_clippings_activate');

// Function to get the CSV file path
function get_csv_file_path() {
    // Read CSV file name option
    $csv_file_name = get_option('your_unique_exhibitor_list_option_name', 'default-csv-file-name');

    // Path to your CSV file using the CSV file name from options
    $csv_file_path = plugin_dir_path(__FILE__) . 'excel-sheets/' . $csv_file_name;

    return $csv_file_path;
}
//Add css and js to the page if only shortcode is used.
function enqueue_scripts() {
    if (has_shortcode(get_post()->post_content, 'excel_data_display')) {
        wp_enqueue_script('jquery');
    wp_enqueue_script('excel-data-script', plugin_dir_url(__FILE__) . 'assets/js/excel-data-script-min.js', array('jquery'), '1.0', true);
    wp_localize_script('excel-data-script', 'excel_data_script_params', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
    wp_enqueue_style('excel-data-style', plugin_dir_url(__FILE__) . 'assets/css/excel-data-min.css');
    }
   
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');

//Register First Ajax call to read Excelsheet.
add_action('wp_ajax_get_excel_data', 'get_excel_data_callback');
add_action('wp_ajax_nopriv_get_excel_data', 'get_excel_data_callback');

function get_excel_data_callback() {
    // Path to your CSV file
    $csv_file_path = get_csv_file_path();
    // Check if the file exists
    if (file_exists($csv_file_path)) {
        $csv_data = array();
        // Open the CSV file for reading
        if (($handle = fopen($csv_file_path, 'r')) !== false) {
            // Read each row from the CSV file
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Create an associative array using the header as keys
                $row = array_combine(
                    array('CompanyName', 'Venue', 'HallNo', 'BoothNo', 'CountryName', 'Website', 'Email', 'ProductType'),
                    $data
                );
                // Add the row data to the result array
                $csv_data[] = $row;
            }
            fclose($handle);
        }
        // Send the JSON response
        wp_send_json_success($csv_data);
    } else {
        wp_send_json_error('CSV file not found.');
    }
    // Make sure to exit after sending the JSON response
    wp_die();
}

//form Ajax callback
add_action('wp_ajax_search_excel_data', 'search_excel_data_callback');
add_action('wp_ajax_nopriv_search_excel_data', 'search_excel_data_callback');
function search_excel_data_callback() {
    // Retrieve form data from AJAX request
    $form_data = $_POST['form_data'];
    parse_str($form_data, $form_args);
    $csv_file_path = get_csv_file_path();
    if (file_exists($csv_file_path)) {
        $csv_data = array();
        // Open the CSV file for reading
        if (($handle = fopen($csv_file_path, 'r')) !== false) {
            // Read each row from the CSV file
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Create an associative array using the header as keys
                $row = array_combine(
                    array('CompanyName', 'Venue', 'HallNo', 'BoothNo', 'CountryName', 'Website', 'Email', 'ProductType'),
                    $data
                );
                // Check if the row matches the search criteria
                $matches_search = true;
                if (!empty($form_args['company_name'])) {
                    $matches_search = strpos($row['CompanyName'], $form_args['company_name']) !== false;
                }

                if ($matches_search && !empty($form_args['country'])) {
                    $matches_search = $row['CountryName'] == $form_args['country'];
                }

                if ($matches_search && !empty($form_args['venue'])) {
                    $matches_search = $row['Venue'] == $form_args['venue'];
                }
                // Add the row data to the result array if it matches the search criteria
                if ($matches_search) {
                    $csv_data[] = $row;
                }
            }
            fclose($handle);
        }
        // Send the HTML response
        if (!empty($csv_data)) {
            ob_start(); // Start output buffering
            foreach ($csv_data as $row) {
                echo '<tr>';
                echo '<td>' . $row['CompanyName'] . '</td>';
                echo '<td>' . $row['Venue'] . '</td>';
                echo '<td>' . $row['HallNo'] . '</td>';
                echo '<td>' . $row['BoothNo'] . '</td>';
                echo '<td class="accordion-icon chevron-down">&#9660;</td>';
                echo '</tr>';
                echo '<tr class="accordion-content" style="display: none;">';
                echo '<td colspan="5">' . getAccordionContent($row) . '</td>';
                echo '</tr>';
            }
            $html_output = ob_get_clean(); // Get the buffered content
            wp_send_json_success($html_output);
        } else {
            wp_send_json_error('No data found.');
        }
    } else {
        wp_send_json_error('CSV file not found.');
    }
    // Make sure to exit after sending the JSON response
    wp_die();
}


//Register shortcode to show search form and table
add_shortcode('excel_data_display', 'display_excel_data_shortcode');
function display_excel_data_shortcode() {
    ob_start(); // Start output buffering
    // Display the form where the data will be filtered
    echo'<div id="excel-search-form">
                <form id="search-form">
                    <input type="text" id="company-name" name="company_name" placeholder="Company Name">
                    <select id="country" name="country">
                    </select>
                    <select id="venue" name="venue">
                    </select>
                    <img src="/wp-content/plugins/exhibitor-list/assets/images/search.png" class="search_icon">    
                    <input type="submit" value="Search">
                </form>
        </div>'; 

    // Display the table where the data will be populated
    echo '<div class="main_div">';
    echo '<div class="content_intro">
    <div class="content_notice">*Please note that the exhibitor list is being continuously updated.</div>
    <div class="content_counter">
    <span class="counter_exibit"></span>
    <span>| Sort by: <a id="sort-btn"> A-Z </a></span>
    </div>   
    </div>';
    echo '<div class="table_container">';
    echo '<table id="excel-data-table" >
            <thead>
                <tr>
                    <th class="heading">Exhibitor</th>
                    <th class="heading">Venue</th>
                    <th class="heading">Hall</th>
                    <th class="heading">Booth</th>
                    <th class="heading"></th>
                </tr>
            </thead>
            <tbody></tbody>
          </table>';
    echo '</div>';
    echo '</div>';

    return ob_get_clean(); // Return the buffered content
}


//show setting page in the plugins page 
function add_exhibitor_list_settings_link($links) {
    $settings_link = '<a href="admin.php?page=exhibitor_list_settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin_basename = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin_basename", 'add_exhibitor_list_settings_link');