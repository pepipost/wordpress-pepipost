<?php
/**
 * @author Pepipost
 * @copyright Pepipost, 2015, All Rights Reserved
 * This code is released under the GPL licence version 3 or later, available here
 * http://www.gnu.org/licenses/gpl.txt
 */

if ( ! class_exists( 'WP_List_Table_Custom' ) ) {
	//require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'class-wp-list-table-custom.php' );
}
class Logs extends WP_List_Table_Custom {

    /** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Log', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Logs', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		));
		
		/** api key of pepipost **/
		define('API_KEY', get_option('wpp_api_key'));
		
	
	}
	
// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

static function CallAPI($method, $url, $data = array())
{
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => $method,
            'content' => http_build_query($data),
        ),
    );
	//echo $url;echo "<pre>";print_r($options);die;
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}
/**
 * Retrieve logs data
 *
 * @param int $per_page
 * @param int $page_number
 *
 * @return mixed
 */
public static function get_logs( $per_page = 10, $page_number = 1, $email = '' ) {

    $result = array();

    $start = !empty($_REQUEST['paged']) ? (($_REQUEST['paged']-1)*10) : 0;
    $end = $per_page;
    if ( !empty($email) ) {
        $start = 0;
        //$end = 100;
    }

    $url = 'http://report.pepipost.com/logs.php';
    
    $data = array( 
        'start' => $start, 
        'limit' => $end, 
        'api_key' => API_KEY,
    );
    if ( !empty($email) ) {
        $data += array( 'email' => $email );
    }
    // Api call to getch log records
    $result = self::CallAPI('POST', $url, $data);
//echo "<pre>";print_r($result);die;
    $result = json_decode($result, true);

    return $result;
}

/** Text displayed when no data is available */
public function no_items() {
    _e( 'No Data Avaliable.', 'sp' );
}

/**
 * Render a column when no column specific method exists.
 *
 * @param array $item
 * @param string $column_name
 *
 * @return mixed
 */
public function column_default( $item, $column_name ) {
    switch ( $column_name ) {
        case 'email':
        case 'fromaddress':
            return $item[$column_name];
        default:
            return print_r( $item, true ); //Show the whole array for troubleshooting purposes
 
 }
}

/**
 * Returns the count of records
 *
 * @return null|string
 */
public static function record_count() {
    $email = isset($_REQUEST['search_by_email']) ? trim($_REQUEST['search_by_email']) : '';
    $url = 'http://report.pepipost.com/logs.php';
    $data = array( 
        'start' => '', 
        'limit' => '', 
        'api_key' => API_KEY,
    );
    if ( !empty($email) ) {
        $data += array( 'email' => $email );
    }
    // Api call to getch log records
    $result = self::CallAPI('POST', $url, $data);
    $count = json_decode($result, true);
    
    return $count['count'];
}

/**
 *  Associative array of columns
 *
 * @return array
 */
public function get_columns() {
    $columns = array(
        'adate'      => __( 'Date' ),
        'fromaddress'    => __( 'FromAddress' ),
        'email'    => __( 'Email' ),
        'msize'    => __( 'Size' ),
        'arrivaltime'    => __( 'ModifiedTime' ),
        'status'    => __( 'Status' ),
        'remarks'	=> __( 'Remarks' ),
    );

  return $columns;
}

/**
 * Columns to make sortable.
 *
 * @return array
 */
public function get_sortable_columns() {
    $sortable_columns = array(
        'email'       => array( 'Email', true ),
        'fromaddress' => array( 'FromAddress', false )
    );

    return $sortable_columns;
}

/**
 * Handles data query and filter, sorting, and pagination.
 */
public function prepare_items() {

    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    //$this->process_bulk_action();

    $per_page     = 10; //$this->get_items_per_page( 'logs_per_page', 10 );
    $current_page = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;//$this->get_pagenum();
    $total_items  = self::record_count();

    $this->set_pagination_args( array(
        'total_items' => $total_items, //WE have to calculate the total number of items
        'per_page'    => $per_page //WE have to determine how many items to show on a page
    ));

    /* -- Register the Columns -- */
    $columns = $this->get_columns();
    $_wp_column_headers[$screen->id]=$columns;
    $email = isset($_REQUEST['search_by_email']) ? trim( $_REQUEST['search_by_email'] ) : '';

    // Api call to getch log records
    $records = self::get_logs( $per_page, $current_page, $email );
    $this->items = $records['data'];
}

/**
 * Display the rows of records in the table
 * @return string, echo the markup of the rows
 */
public function display_rows() {

    //Get the records registered in the prepare_items method
    $records = $this->items;

    //Get the columns registered in the get_columns and get_sortable_columns methods
    $columns = $this->get_columns();

    foreach ( $columns as $column_name => $column_display_name ) {
	    echo '<th>';
	    echo $column_display_name;
	    echo '</th>';
    }
    //Loop for each record
    if( !empty($records) ) {
        $r = 1;
        foreach($records as $rec) {

            //Open the line
            echo '<tr id="record_'.$r.'">';
            foreach ( $columns as $column_name => $column_display_name ) {
	
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                //if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;

                //Display the cell
                switch ( $column_name ) {
                    case "adate":  echo '<td '.$attributes.'>'.stripslashes($rec['adate']).'</td>';   break;
                    case "fromaddress": echo '<td '.$attributes.'>'.stripslashes($rec['fromaddress']).'</td>'; break;
                    case "email": echo '<td '.$attributes.'>'.stripslashes($rec['email']).'</td>'; break;
                    case "msize": echo '<td '.$attributes.'>'.$rec['msize'].'</td>'; break;
                    case "arrivaltime": echo '<td '.$attributes.'>'.$rec['arrivaltime'].'</td>'; break;
                    case "remarks": echo '<td '.$attributes.'>'.$rec['remarks'].'</td>'; break;
                    case "status": echo '<td '.$attributes.'>'.$rec['status'].'</td>'; break;
                }
            }

            //Close the line
            echo'</tr>';
            $r++;
        }
    }
   
}

/**
 * common html for header
 * @return: html content
 */
public function common_html() {
    $html = '<div class="common_str">';
    
    $html .= '</div><div class="clear"></div>';
    $html = '';
    return $html;

}

/**
 * Retrieve stats data
 *
 * @param int $start date
 * @param int $end date
 *
 * @return mixed
 */
public static function get_stats( $start = '', $end = '' ) {

    $result = array();
    $url = 'http://report.pepipost.com/stats.php';
    $data = array( 
        'start_date' => $start, 
        'end_date'   => $end, 
        'api_key'    => API_KEY 
    );

    // Api call to getch stats
    $result = self::CallAPI('POST', $url, $data);
    $result = json_decode($result, true);

    return $result;
}




} //end of class
