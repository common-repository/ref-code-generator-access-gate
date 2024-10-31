<?php
    /*
    Plugin Name: Zedna Ref Code Generator & Access Gate
    Plugin URI: https://profiles.wordpress.org/zedna#content-plugins
    Description: Import existing ref codes from user meta data and generate new random reference codes. Can protect website content by generated codes.
    Version: 1.5
    Author: Radek Mezulanik
    Author URI: https://www.mezulanik.cz
    License: GPL2
    */

$styles = "<style>
    #wpwrap { background-color: #007db8; }
    #wpcontent { background-color: #007db8; }
    #wpfooter { background-color: #23282d; }
    body, * { font-family: 'Noto Sans', sans-serif; font-size: 14px; }
    input[type=text], input[type=submit] { border: 1px solid #CCC; padding: 3px; border-radius: 3px; }
    input[type=submit]:hover { cursor: pointer; border: 1px solid #272d32; background-color: #272d32; color: #fff; }
    .drcg-code-list-header { text-align: left;}
    #wrapper { color: #fff; }
    .form-table th { color: #fff; }
    .form-table td { color: #fff; }
    #wrapper h1 { font-size: 20px; color: #fff; }
    #wrapper h2 { color: #fff; }
    #results {  margin-top: 50px; }
    .orange { color: #ff6310; display: initial;}
    .green { color: #4fff30; display: initial;}
    .code { background-color: cadetblue; display: initial;}
</style>";

add_action( 'wp_loaded','drcg_zedna_open_gate' );
function drcg_zedna_open_gate() {
$cookienonce = wp_create_nonce( 'cookie' );
//Get user input and compare with ref codes in database
if(isset($_POST['ref_code_input'])){
if (!isset( $_POST['drcg_zedna_nonce_field'] ) || !wp_verify_nonce( $_POST['drcg_zedna_nonce_field'], 'drcg_zedna_nonce_action' ) ) {

   print 'Sorry, your nonce did not verify.';
   exit;

} else {
$ref_code_input = sanitize_text_field( $_POST['ref_code_input'] );

    if($ref_code_input){
        echo $ref_code_input;
        global $wpdb;
        $table_name = $wpdb->prefix . "refcode";
        $ref_code_list = array();

        $query = $wpdb->get_results("SELECT code FROM $table_name GROUP BY code", ARRAY_N);
        $i = 0;
        foreach($query as $list){
            $ref_code_list[$i++] = $list[0];
        }
        if (in_array($ref_code_input, $ref_code_list)){
            setcookie( $cookienonce, $ref_code_input, 1 * 'DAYS_IN_SECONDS', COOKIEPATH, COOKIE_DOMAIN );
            $redirect = get_option('drcg_zedna_gate_form_redirect');
            $url = get_option('drcg_zedna_gate_form_redirect_custom');
            if($redirect == 'homepage'){
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: ".get_bloginfo('url'));
            }else if($redirect == 'current_page'){
                header("Refresh:0");
            }else{
                header('Location: '.$url);
            }
        exit();
        }

}
}
}
$gate_form_on = get_option('drcg_zedna_gate_form');

//If ref code is not in
if(!isset($_COOKIE[$cookienonce]) && $gate_form_on == "yes") {
    add_filter( 'template_redirect', 'drcg_zedna_gate_template', 99 );
}
}
function drcg_zedna_gate_template( $template ) {
   // If neither the child nor parent theme have overridden the template,
   load_template( dirname( __FILE__ ) . '/template/gate.php' );

    return $template;
}

//Add Ref Code field to user profile
add_filter('user_contactmethods', 'drcg_zedna_add_user_profile_field');
function drcg_zedna_add_user_profile_field($profile_fields) {

    // Add new fields
    $profile_fields['ref_code'] = 'Ref Code';

    return $profile_fields;
}

//Create DB table on activation
register_activation_hook(__FILE__,'drcg_zedna_install_db');
function drcg_zedna_install_db () {
    global $wpdb;
    $table_name = $wpdb->prefix . "refcode";

    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

    $sql2 = "CREATE TABLE `$table_name` (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    code varchar(10) NOT NULL,
    used smallint(1) DEFAULT '0' NOT NULL,
    UNIQUE KEY (code),
    PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql2);
    }
}

//Add admin page
add_action('admin_menu', 'drcg_zedna_setttings_menu');
function drcg_zedna_setttings_menu(){
    global $lmpImagePath;
        add_menu_page( 'Ref Code Generator', 'Ref Code Generator', 'manage_options', 'drcg', 'drcg_zedna_settings_init', 'dashicons-tickets-alt');
        add_submenu_page('drcg', 'FAQ', 'FAQ', 'manage_options', 'drcg_zedna_faq', 'drcg_zedna_faq');
        add_submenu_page('drcg', 'Code list', 'Code list', 'manage_options', 'drcg_zedna_list', 'drcg_zedna_code_list');
        add_submenu_page('drcg', 'Used codes', 'Used codes', 'manage_options', 'drcg_zedna_list_used', 'drcg_zedna_code_list_used');
        add_submenu_page('drcg', 'Free codes', 'Free codes', 'manage_options', 'drcg_zedna_list_free', 'drcg_zedna_code_list_free');
        add_submenu_page('drcg', 'Options', 'Options', 'manage_options', 'drcg_zedna_options', 'drcg_zedna_options');
}

//Open plugin page
function drcg_zedna_settings_init(){
    global $styles;
?>
    <?php print $styles;?>
        <div id="wrapper">
            <H1>Reference code generator</H1>
            <h5>How many numbers you want to generate?</h5>
            <p>Every string will be 10 character long.</p>
            <form action="<?php echo admin_url( 'admin.php?page=drcg'); ?>" method="post" name="random_string_generator">
                <?php wp_nonce_field( 'drcg_zedna_nonce_action', 'drcg_zedna_nonce_field' ); ?>
                    <input type="text" name="quantity" id="quantity">
                    <input type="submit" value="Generate" name="generate">
            </form>
            <div id="results">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . "refcode";
                $table_name_existing = $wpdb->prefix . "usermeta";

                if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                    print '<b>Database table <font color="orange">'.$table_name.'</font> is not exist</b>';
                }

                $allusers = get_users();
                    foreach ($allusers as $user) {
                        if(get_user_meta($user->ID, 'Ref Code', true) == ''){
                            add_user_meta( $user->ID, 'Ref Code', '', true );
                        }
                    }

if( isset($_POST['quantity']) and $_POST['quantity'] != '' ){
    if (
    ! isset( $_POST['drcg_zedna_nonce_field'] )
    || ! wp_verify_nonce( $_POST['drcg_zedna_nonce_field'], 'drcg_zedna_nonce_action' )
) {

   print 'Sorry, your nonce did not verify.';
   exit;

} else {

   // process form data


$time = microtime(TRUE);

                //DB query to get existing codes
                $query = $wpdb->get_results("SELECT code FROM $table_name GROUP BY code", ARRAY_N);
                $ref_codes_existing = $wpdb->get_results("SELECT * FROM $table_name_existing WHERE meta_key = 'ref_code'");
                $ref_codes_existing_free = $wpdb->get_results("SELECT code FROM $table_name WHERE used = '0'");

                //set arrays for existing codes
                $ref_codes_existing_array = array();
                $ref_code_list = array();
                $ref_code_listexisting_free = array();

                //load existing codes from our list
                $i = 0;
                foreach($query as $list){
                    $ref_code_list[$i++] = $list[0];
                }

                foreach($ref_code_listexisting_free as $code_existing_free){
                    $ref_code_listexisting_free[] = $code_existing_free;
                }

                //load, update or insert existing user codes from User Meta to our custom table
                foreach ( $ref_codes_existing as $code_existing ){
                    $ref_codes_existing_array[] = $code_existing->meta_value;
                   if (in_array($code_existing->meta_value, $ref_code_list)){
                       $wpdb->update( $table_name, array( 'used' => '1'), array( 'code' => $code_existing->meta_value ));
                    }
                }

                //generate new codes
                $counter = 0;
                for ($i=0; $i<$_POST['quantity']; $i++){

                    $random_string = substr(md5(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 10)).time()), 0, 10);

                    if (in_array($random_string, $ref_code_list) || in_array($random_string, $ref_codes_existing_array)) {
                        echo $random_string.' Exists! <br>';
                    } else {
                        $wpdb->insert($table_name, array('code' => $random_string,'used' => '0'));
                        echo $random_string.'<br>';
                        array_push($ref_code_list, $random_string);
                        $counter ++;
                    }
                }

                echo '<h2>Total '.$counter.' strings generated in '.(microtime(TRUE)-$time).' seconds.</h2>';

            }
}
            ?>
            </div>
        </div>
        <?php
}

//Show FAQ page
function drcg_zedna_faq() {
    global $styles;
    global $wpdb;
    $table_name = $wpdb->prefix . "refcode";

    $ref_codes = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
            <?php print $styles;?>
                <div id="wrapper">
                    <div class="drcg-code-list-header">
                        <H1>Frequently Asked Questions</H1>
                        <hr>
                        <p><b>Generator is getting <u>WordPress database error</u></b></p>
                        <p>You might see during plugin activation warning message: <i>The plugin generated 500 characters of unexpected output during activation. If you notice “headers already sent” messages, problems with syndication feeds or other issues, try deactivating or removing this plugin.</i> This means, that your website can´t access to database or create new database table.</p>
                        <p><b>Solutions:</b></p>
                        <p>1. Check database user if has enough right to create Tables.</p>
                        <p>2. If you cannot change user´s permission, use this SQL query in phpMyAdmin:</p>
                        <p class="code">
                            CREATE TABLE `
                            <?php print $table_name;?>` (
                                <br> id mediumint(9) NOT NULL AUTO_INCREMENT,
                                <br> code varchar(10) NOT NULL,
                                <br> used smallint(1) DEFAULT '0' NOT NULL,
                                <br> UNIQUE KEY (code),
                                <br> PRIMARY KEY (`id`)
                                <br> ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;
                        </p>
                        <hr>
                        <p><b>Code list is showing 0 results, but there are some codes already</b></p>
                        <p>1. Check if there is user meta key "ref_code" called "Ref Code" in user profile.</p>
                        <p>2. Existing codes are imported during first generating new code.</p>
                        <hr>
                        <p><b>How can i change text in Access Gate?</b></p>
                        <p>1. There is a custom post type called <b>Ref Code Gate post</b> and here you should insert/edit your text.</p>
                        <p>2. Don´t forget to add this shortcode to your text editor to show Ref Code form:</p>
                        <p class="code">[ref_code_gate_form]</p>

                        <p>You can modify a button text by adding a parameter to this shortcode (default text is <b>Submit</b>).</p>
                        <p>You can also add a label text (default text is <b>Code</b>):</p>
                        <p class="code">[ref_code_gate_form button_text="My custom submit text" label_text="My custom label"]</p>
                    </div>
                </div>
                <?php
}

//Show all reference codes
function drcg_zedna_code_list() {
    global $styles;
    global $wpdb;
    $table_name = $wpdb->prefix . "refcode";

    $ref_codes = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
                    <?php print $styles;?>
                        <div id="wrapper">
                            <div class="drcg-code-list-header">
                                <H1>Reference code list</H1>
                                <p>Here are all
                                    <?php echo $wpdb->num_rows; ?> reference codes stored in database.</p>
                                <p>
                                    <div class='green'><strong>Green</strong></div> codes are free.</p>
                                <p>
                                    <div class='orange'><strong>Orange</strong></div> codes have been already used.</p>
                            </div>
                            <?php
foreach ( $ref_codes as $code )
{
    echo "<div style='float:left; padding: 5px;'>";
    if($code->used == 1){
        echo "<div class='orange'><strong>".$code->code."</strong></div>";
    }else{
        echo "<div class='green'><strong>".$code->code."</strong></div>";
    }
    echo "</div>";
}
echo "</div>";
}


//Show all used codes
function drcg_zedna_code_list_used() {
    global $styles;
    global $wpdb;
    $table_name = $wpdb->prefix . "refcode";
    $ref_codes = $wpdb->get_results("SELECT * FROM $table_name WHERE used = 1");
    ?>
                                <?php print $styles;?>
                                    <div id="wrapper">
                                        <div class="drcg-code-list-header">
                                            <H1>Used reference code list</H1>
                                            <p>Here are all
                                                <?php echo $wpdb->num_rows; ?> used reference codes stored in database.</p>
                                        </div>
                                        <?php
foreach ( $ref_codes as $code )
{
    echo "<div style='float:left; padding: 5px;'>";
        echo $code->code;
    echo "</div>";
}
echo "</div>";
}


//Show all free codes
function drcg_zedna_code_list_free() {
    global $styles;
    global $wpdb;
    $table_name = $wpdb->prefix . "refcode";

    $ref_codes = $wpdb->get_results("SELECT * FROM $table_name WHERE used = 0");
    ?>
                                            <?php print $styles;?>
                                                <div id="wrapper">
                                                    <div class="drcg-code-list-header">
                                                        <H1>Free reference code list</H1>
                                                        <p>Here are all
                                                            <?php echo $wpdb->num_rows; ?> free reference codes stored in database.</p>
                                                    </div>
                                                    <?php
foreach ( $ref_codes as $code )
{
    echo "<div style='float:left; padding: 5px;'>";
        echo $code->code;
    echo "</div>";
}
echo "</div>";
}

//Register settings for plugin
function drcg_zedna_register_drcg_zedna_setting() {
    register_setting( 'gate-form-settings', 'drcg_zedna_gate_form' );
    register_setting( 'gate-form-settings', 'drcg_zedna_gate_form_redirect' );
    register_setting( 'gate-form-settings', 'drcg_zedna_gate_form_redirect_custom' );
}
add_action( 'admin_init', 'drcg_zedna_register_drcg_zedna_setting' );

// Create WordPress plugin page
if( !function_exists("drcg_zedna_options") )
{
function drcg_zedna_options(){
    global $styles;
?>
                                                        <?php print $styles;?>
                                                            <div id="wrapper">
                                                                <h1>Ref Code Gate Settings</h1>
                                                                <form method="post" action="options.php">
                                                                    <?php wp_nonce_field( 'drcg_zedna_nonce_action', 'drcg_zedna_nonce_field' ); ?>
                                                                        <?php settings_fields( 'gate-form-settings' ); ?>
                                                                            <?php do_settings_sections( 'gate-form-settings' ); ?>
                                                                                <table class="form-table">
                                                                                    <tr valign="top">
                                                                                        <th scope="row">Enable access GATE:</th>
                                                                                        <td>
                                                                                            <select name='drcg_zedna_gate_form'>
                                                                                                <?php $drcg_zedna_gate_form = get_option('drcg_zedna_gate_form');
      if ($drcg_zedna_gate_form == "yes"){
       echo "<option value='yes' selected=selected>Yes</option>
             <option value='no'>No</option>";
      }else{
      echo "<option value='yes'>Yes</option>
            <option value='no' selected=selected>No</option>";
      }
      ?>
                                                                                            </select>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr valign="top">
                                                                                        <th scope="row">Redirect GATE to:</th>
                                                                                        <td>
                                                                                            <select name='drcg_zedna_gate_form_redirect'>
                                                                                                <?php $drcg_zedna_gate_form_redirect = get_option('drcg_zedna_gate_form_redirect');
      if ($drcg_zedna_gate_form_redirect == "homepage"){
       echo "<option value='homepage' selected=selected>Homepage</option>
             <option value='current_page'>Current page</option>
             <option value='custom_url'>Custom url</option>";
      }else if ($drcg_zedna_gate_form_redirect == "current_page"){
      echo "<option value='homepage'>Homepage</option>
            <option value='current_page' selected=selected>Current page</option>
            <option value='custom_url'>Custom url</option>";
      }else{
      echo "<option value='homepage'>Homepage</option>
            <option value='current_page'>Current page</option>
            <option value='custom_url' selected=selected>Custom url</option>";
      }
      ?>
                                                                                            </select>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr valign="top">
                                                                                        <th scope="row">Redirect to custom URL:</th>
                                                                                        <td>
                                                                                            <input type="text" name="drcg_zedna_gate_form_redirect_custom" id="drcg_zedna_gate_form_redirect_custom" value="<?php print get_option('drcg_zedna_gate_form_redirect_custom');?>">
                                                                                            <br> 1. example: <i>/precision/</i>
                                                                                            <br> 2. example: <i>http://example.com/precision/</i>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                                <?php submit_button(); ?>
                                                                </form>
                                                            </div>
                                                            <?php
}
}

/* Custom post types */
add_action( 'init', 'drcg_zedna_create_post_type_ref_code_gate_post' );
function drcg_zedna_create_post_type_ref_code_gate_post() {
  register_post_type( 'refcodegatepost',
    array(
      'labels' => array(
        'name' => __( 'Ref Code Gate post' ),
        'singular_name' => __( 'Ref Code Gate post' )
      ),
      'supports' => array( 'title', 'editor'),
      'public' => true,
      'has_archive' => true,
      'taxonomies' => array('category'),
      'rewrite' => array( 'slug' => 'refcodegateposts', 'with_front' => true)
    )
  );
}
/* // Custom post types */

//shortcode for gate form in content
function drcg_zedna_ref_code_gate_form( $atts ) {
    extract(
        shortcode_atts(
            array(
                'label_text' => 'Code',
                'button_text' => 'Submit',
            ),
        $atts )
    );
ob_start();?>
                                                                <styles>
                                                                </styles>
                                                                <form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" class="ref-code-gate-form">
                                                                    <?php wp_nonce_field( 'drcg_zedna_nonce_action', 'drcg_zedna_nonce_field' ); ?>
                                                                        <fieldset>
                                                                            <p class="form-element form-left">
                                                                                <label for="ref-code-input" class="ref-form-label">
                                                                                    <?php echo $label_text;?> <abbr class="required" title="required">*</abbr></label>
                                                                                <input name="ref_code_input" class="ref-code-input" type="text" id="ref-code-input" value="">
                                                                            </p>
                                                                            <p class="form-element form-right">
                                                                                <label for="ref-code-input" class="ref-form-label">&nbsp;</label>
                                                                                <input type="submit" value="<?php echo $button_text;?>" class="ref-code-button" data-sending-label="Sending">
                                                                            </p>
                                                                        </fieldset>
                                                                </form>
                                                                <?php
    $output = ob_get_clean();
     return $output;
}
add_shortcode( 'ref_code_gate_form', 'drcg_zedna_ref_code_gate_form' );
?>
