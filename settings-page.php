<?php
function your_unique_exhibitor_list_settings_page() {
    ?>
    <div class="wrap">
        <h1> Exhibitor list Settings</h1>
        <p><strong>Please enter CSV file name<strong></p>
        <p><strong>Example: excelsheet.csv<strong></p>
        <p> <strong>Default: Exhibitor-List.csv<strong></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('your_unique_exhibitor_list_settings'); // Update this line
            do_settings_sections('your_unique_exhibitor_list_settings'); // Update this line
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">CSV File Name:</th>
                    <td>
                        <input type="text" name="your_unique_exhibitor_list_option_name" value="<?php echo esc_attr(get_option('your_unique_exhibitor_list_option_name')); ?>" />
                    </td>
                </tr>
            </table>
            <?php
            submit_button();
            ?>
        </form>
        <h2>How to use the plugin?<h2>
        <ol>
            <li>Create a new page using any page builder</li>
            <li>Add a shortcode widget </li>
            <li>Use the following shortcode <span id="shortcode">[excel_data_display]</span>
                <button type="button" id="copy-shortcode" style="cursor:pointer;margin-left:10px;">Copy Shortcode</button>
                <span id="copy-message" style="color: green; display: none;">Copied!</span>
            </li>
        </ol>
    </div>
    <?php
}

// Function to enqueue styles and scripts for the settings page
function your_unique_enqueue_settings_scripts() {
    wp_enqueue_script('excel-data-script', plugin_dir_url(__FILE__) . 'assets/js/settings-page.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'your_unique_enqueue_settings_scripts');

// Function to add the settings page
function add_your_unique_exhibitor_list_settings_page() {
    add_menu_page(
        'Exhibitor list',
        'Exhibitor list',
        'manage_options',
        'your_unique_exhibitor_list_settings', // Update this line
        'your_unique_exhibitor_list_settings_page', // Update this line
        'dashicons-media-spreadsheet',
        30
    );
}
add_action('admin_menu', 'add_your_unique_exhibitor_list_settings_page'); // Update this line

// Function to register settings
function your_unique_register_exhibitor_list_settings() {
    register_setting('your_unique_exhibitor_list_settings', 'your_unique_exhibitor_list_option_name'); // Update this line
    // Add more settings as needed
}
add_action('admin_init', 'your_unique_register_exhibitor_list_settings'); 
