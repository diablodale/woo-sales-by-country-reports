<?php
/**
 * @wordpress-plugin
 * Plugin Name: Sales Report By Country for WooCommerce
 * Plugin URI:  https://www.zorem.com/shop/woocommerce-sales-report-by-country/
 * Description: This plugin simply adds a report tab to display sales report by country WooCommerce Reports. The plugin adds an additional report tab which display sales report by country. You’ll find this report available in WooCommerce reports section.
 * Version: 1.6.2
 * Author:      zorem
 * Author URI:  http://www.zorem.com/
 * License:     GPL-2.0+
 * License URI: http://www.zorem.com/
 * Text Domain: woo-sales-country-reports
 * WC tested up to: 4.0
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
 * # WooCommerce Location Report Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds a new section in the WooCommerce Reports -> Orders area called 'Sales by location'.
 * The report visualizes the customer purchases by location into a Choropleth map to show where the orders
 * are being placed.
 *
 * This plugin utilizes jVectorMap (http://jvectormap.com) for its map functions.
 *
 */
class WC_Country_Report {

	/** plugin version number */
	public static $version = '1.6.2';

	/** @var string the plugin file */
	public static $plugin_file = __FILE__;

	/** @var string the plugin file */
	public static $plugin_dir;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public static function init() {

		global $wpdb;

		self::$plugin_dir = dirname( __FILE__ );			

		// Add the reports layout to the WooCommerce -> Reports admin section
		add_filter( 'woocommerce_admin_reports',  __CLASS__ . '::initialize_country_admin_report', 12, 1 );

		// Add the path to the report class so WooCommerce can parse it
		add_filter( 'wc_admin_reports_path',  __CLASS__ . '::initialize_country_admin_reports_path', 12, 3 );

		// Load translation files
		add_action( 'plugins_loaded', __CLASS__ . '::load_plugin_textdomain' );
		
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_styles', 4 );
		
		register_activation_hook( __FILE__, __CLASS__ . '::woo_sales_country_report_install');

	}


	/**
	 * Add our location report to the WooCommerce order reports array.
	 *
	 * @param array Array of All Report types & their labels
	 * @return array Array of All Report types & their labels, including the 'Sales by location' report.
	 * @since 1.0
	 */
	public static function initialize_country_admin_report( $report ) {

		$report['orders']['reports']['sales_by_country'] = array(
			'title'       => __( 'Sales by country', 'woo-sales-country-reports' ),
			'description' => '',
			'hide_title'  => true,
			'callback'    => array( 'WC_Admin_Reports', 'get_report' ),
			);

		return $report;

	}


	/**
	 * If we hit one of our reports in the WC get_report function, change the path to our dir.
	 *
	 * @param array Array of Report types & their labels
	 * @return array Array of Report types & their labels, including the Subscription product type.
	 * @since 1.0
	 */
	public static function initialize_country_admin_reports_path( $report_path, $name, $class ) {		
		if ( 'WC_Report_sales_by_country' == $class ) {
			$report_path = self::$plugin_dir . '/classes/class-wc-report-' . $name . '.php';
		}

		return $report_path;

	}


	/**
	 * Load our language settings for internationalization
	 *
	 * @since 1.0
	 */
	public static function load_plugin_textdomain() {

		load_plugin_textdomain( 'woocommerce-sales-country-reports', false, basename( self::$plugin_dir ) . '/lang' );

	}
	
	/**
	 * Load admin styles.
	 */
	public static function admin_styles() {		
		wp_enqueue_style( 'country_report_style', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );

		//amcharts js	
		wp_enqueue_script( 'amcharts', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/amcharts.js' );
		wp_enqueue_script( 'amcharts-light-theme', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/light.js' );
		wp_enqueue_script( 'amcharts-pie', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/pie.js' );
		wp_enqueue_script( 'amcharts-serial', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/serial.js' );
		wp_enqueue_script( 'amcharts-export', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/export.js' );
		
		//Main js
		wp_enqueue_script( 'sales-by-country-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/script.js' );		
	}

	/**
	 * Define plugin activation function
	 *
	 * Create Table
	 *
	 * Insert data 
	 *
	 * 
	*/	
	 public static function woo_sales_country_report_install(){
		
		global $wpdb;	
		
		$woo_sales_country_table_name = $wpdb->prefix . 'woo_sales_country_region';

		// create the ECPT metabox database table
		if($wpdb->get_var("show tables like '$woo_sales_country_table_name'") != $woo_sales_country_table_name) 
		{
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $woo_sales_country_table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				country varchar(500) DEFAULT '' NOT NULL,
				region varchar(500) DEFAULT '' NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			
			$country_list = array(
				array(
					"Region" => "Asia", 
					"Country" => "AF"), 
				array(
					"Region" => "East Europe", 
					"Country" => "AL"), 
				array(
					"Region" => "Africa", 
					"Country" => "DZ"), 
				array(
					"Region" => "Asia", 
					"Country" => "AS"), 
				array(
					"Region" => "West Europe", 
					"Country" => "AD"), 
				array(
					"Region" => "Africa", 
					"Country" => "AO"), 
				array(
					"Region" => "South America", 
					"Country" => "AI"), 
				array(
					"Region" => "South America", 
					"Country" => "AG"), 
				array(
					"Region" => "South America", 
					"Country" => "AR"), 
				array(
					"Region" => "East Europe", 
					"Country" => "AM"), 
				array(
					"Region" => "South America", 
					"Country" => "AW"), 
				array(
					"Region" => "Asia", 
					"Country" => "AU"), 
				array(
					"Region" => "West Europe", 
					"Country" => "AT"), 
				array(
					"Region" => "East Europe", 
					"Country" => "AZ"), 
				array(
					"Region" => "South America", 
					"Country" => "BS"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "BH"), 
				array(
					"Region" => "Asia", 
					"Country" => "BD"), 
				array(
					"Region" => "South America", 
					"Country" => "BB"), 
				array(
					"Region" => "East Europe", 
					"Country" => "BY"), 
				array(
					"Region" => "West Europe", 
					"Country" => "BE"), 
				array(
					"Region" => "South America", 
					"Country" => "BZ"), 
				array(
					"Region" => "Africa", 
					"Country" => "BJ"), 
				array(
					"Region" => "North America", 
					"Country" => "BM"), 
				array(
					"Region" => "Asia", 
					"Country" => "BT"), 
				array(
					"Region" => "South America", 
					"Country" => "BO"), 
				array(
					"Region" => "East Europe", 
					"Country" => "BA"), 
				array(
					"Region" => "Africa", 
					"Country" => "BW"), 
				array(
					"Region" => "South America", 
					"Country" => "BR"), 
				array(
					"Region" => "South America", 
					"Country" => "IO"), 
				array(
					"Region" => "Asia", 
					"Country" => "BN"), 
				array(
					"Region" => "East Europe", 
					"Country" => "BG"), 
				array(
					"Region" => "Africa", 
					"Country" => "BF"), 
				array(
					"Region" => "Asia", 
					"Country" => "MM"), 
				array(
					"Region" => "Africa", 
					"Country" => "BI"), 
				array(
					"Region" => "Asia", 
					"Country" => "KH"), 
				array(
					"Region" => "Africa", 
					"Country" => "CM"), 
				array(
					"Region" => "North America", 
					"Country" => "CA"), 
				array(
					"Region" => "Africa", 
					"Country" => "CV"), 
				array(
					"Region" => "South America", 
					"Country" => "KY"), 
				array(
					"Region" => "Africa", 
					"Country" => "CF"), 
				array(
					"Region" => "Africa", 
					"Country" => "TD"), 
				array(
					"Region" => "South America", 
					"Country" => "CL"), 
				array(
					"Region" => "Asia", 
					"Country" => "CN"), 
				array(
					"Region" => "South America", 
					"Country" => "CO"), 
				array(
					"Region" => "Africa", 
					"Country" => "KM"), 
				array(
					"Region" => "Africa", 
					"Country" => "CG"), 
				array(
					"Region" => "Africa", 
					"Country" => "CD"), 
				array(
					"Region" => "Asia", 
					"Country" => "CK"), 
				array(
					"Region" => "South America", 
					"Country" => "CR"), 
				array(
					"Region" => "Africa", 
					"Country" => "CI"), 
				array(
					"Region" => "East Europe", 
					"Country" => "HR"), 
				array(
					"Region" => "South America", 
					"Country" => "CU"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "CY"), 
				array(
					"Region" => "East Europe", 
					"Country" => "CZ"), 
				array(
					"Region" => "West Europe", 
					"Country" => "DK"), 
				array(
					"Region" => "Africa", 
					"Country" => "DJ"), 
				array(
					"Region" => "South America", 
					"Country" => "DM"), 
				array(
					"Region" => "South America", 
					"Country" => "DO"), 
				array(
					"Region" => "Asia", 
					"Country" => "TL"), 
				array(
					"Region" => "South America", 
					"Country" => "EC"), 
				array(
					"Region" => "Africa", 
					"Country" => "EG"), 
				array(
					"Region" => "South America", 
					"Country" => "SV"), 
				array(
					"Region" => "Africa", 
					"Country" => "GQ"), 
				array(
					"Region" => "Africa", 
					"Country" => "ER"), 
				array(
					"Region" => "East Europe", 
					"Country" => "EE"), 
				array(
					"Region" => "Africa", 
					"Country" => "ET"), 
				array(
					"Region" => "West Europe", 
					"Country" => "FO"), 
				array(
					"Region" => "Asia", 
					"Country" => "FJ"), 
				array(
					"Region" => "West Europe", 
					"Country" => "FI"), 
				array(
					"Region" => "West Europe", 
					"Country" => "FR"), 
				array(
					"Region" => "South America", 
					"Country" => "GF"), 
				array(
					"Region" => "Asia", 
					"Country" => "PF"), 
				array(
					"Region" => "Africa", 
					"Country" => "GA"), 
				array(
					"Region" => "Africa", 
					"Country" => "GM"), 
				array(
					"Region" => "East Europe", 
					"Country" => "GE"), 
				array(
					"Region" => "West Europe", 
					"Country" => "DE"), 
				array(
					"Region" => "Africa", 
					"Country" => "GH"), 
				array(
					"Region" => "West Europe", 
					"Country" => "GI"), 
				array(
					"Region" => "West Europe", 
					"Country" => "GR"), 
				array(
					"Region" => "North America", 
					"Country" => "GL"), 
				array(
					"Region" => "South America", 
					"Country" => "GD"), 
				array(
					"Region" => "South America", 
					"Country" => "GP"), 
				array(
					"Region" => "Asia", 
					"Country" => "GU"), 
				array(
					"Region" => "South America", 
					"Country" => "GT"), 
				array(
					"Region" => "West Europe", 
					"Country" => "GG"), 
				array(
					"Region" => "Africa", 
					"Country" => "GN"), 
				array(
					"Region" => "Africa", 
					"Country" => "GW"), 
				array(
					"Region" => "South America", 
					"Country" => "GY"), 
				array(
					"Region" => "South America", 
					"Country" => "HT"), 
				array(
					"Region" => "South America", 
					"Country" => "HN"), 
				array(
					"Region" => "Asia", 
					"Country" => "HK"), 
				array(
					"Region" => "East Europe", 
					"Country" => "HU"), 
				array(
					"Region" => "West Europe", 
					"Country" => "IS"), 
				array(
					"Region" => "Asia", 
					"Country" => "IN"), 
				array(
					"Region" => "Asia", 
					"Country" => "ID"), 
				array(
					"Region" => "Asia", 
					"Country" => "IR"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "IQ"), 
				array(
					"Region" => "West Europe", 
					"Country" => "IE"), 
				array(
					"Region" => "West Europe", 
					"Country" => "IM"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "IL"), 
				array(
					"Region" => "West Europe", 
					"Country" => "IT"), 
				array(
					"Region" => "South America", 
					"Country" => "JM"), 
				array(
					"Region" => "Asia", 
					"Country" => "JP"), 
				array(
					"Region" => "West Europe", 
					"Country" => "JE"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "JO"), 
				array(
					"Region" => "East Europe", 
					"Country" => "KZ"), 
				array(
					"Region" => "Africa", 
					"Country" => "KE"), 
				array(
					"Region" => "Asia", 
					"Country" => "KI"), 
				array(
					"Region" => "Asia", 
					"Country" => "KP"), 
				array(
					"Region" => "Asia", 
					"Country" => "KR"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "KW"), 
				array(
					"Region" => "East Europe", 
					"Country" => "KG"), 
				array(
					"Region" => "Asia", 
					"Country" => "LA"), 
				array(
					"Region" => "East Europe", 
					"Country" => "LV"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "LB"), 
				array(
					"Region" => "Africa", 
					"Country" => "LS"), 
				array(
					"Region" => "Africa", 
					"Country" => "LS"), 
				array(
					"Region" => "Africa", 
					"Country" => "LS"), 
				array(
					"Region" => "West Europe", 
					"Country" => "LI"), 
				array(
					"Region" => "East Europe", 
					"Country" => "LT"), 
				array(
					"Region" => "West Europe", 
					"Country" => "LU"), 
				array(
					"Region" => "Asia", 
					"Country" => "MO"), 
				array(
					"Region" => "East Europe", 
					"Country" => "MK"), 
				array(
					"Region" => "Africa", 
					"Country" => "MG"), 
				array(
					"Region" => "Africa", 
					"Country" => "MW"), 
				array(
					"Region" => "Asia", 
					"Country" => "MY"), 
				array(
					"Region" => "Asia", 
					"Country" => "MV"), 
				array(
					"Region" => "Africa", 
					"Country" => "ML"), 
				array(
					"Region" => "West Europe", 
					"Country" => "MT"), 
				array(
					"Region" => "Asia", 
					"Country" => "MH"), 
				array(
					"Region" => "South America", 
					"Country" => "MQ"), 
				array(
					"Region" => "Africa", 
					"Country" => "MR"), 
				array(
					"Region" => "Africa", 
					"Country" => "MU"), 
				array(
					"Region" => "Africa", 
					"Country" => "YT"), 
				array(
					"Region" => "South America", 
					"Country" => "MX"), 
				array(
					"Region" => "Asia", 
					"Country" => "FM"), 
				array(
					"Region" => "East Europe", 
					"Country" => "MD"), 
				array(
					"Region" => "West Europe", 
					"Country" => "MC"), 
				array(
					"Region" => "Asia", 
					"Country" => "MN"), 
				array(
					"Region" => "South America", 
					"Country" => "MS"), 
				array(
					"Region" => "Africa", 
					"Country" => "MA"), 
				array(
					"Region" => "Africa", 
					"Country" => "MZ"), 
				array(
					"Region" => "Africa", 
					"Country" => "NA"), 
				array(
					"Region" => "Asia", 
					"Country" => "NR"), 
				array(
					"Region" => "Asia", 
					"Country" => "NP"), 
				array(
					"Region" => "West Europe", 
					"Country" => "NL"),
				array(
					"Region" => "Asia", 
					"Country" => "NC"), 
				array(
					"Region" => "Asia", 
					"Country" => "NZ"), 
				array(
					"Region" => "South America", 
					"Country" => "NI"), 
				array(
					"Region" => "Africa", 
					"Country" => "NE"), 
				array(
					"Region" => "Africa", 
					"Country" => "NG"), 
				array(
					"Region" => "Asia", 
					"Country" => "MP"), 
				array(
					"Region" => "West Europe", 
					"Country" => "NO"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "OM"), 
				array(
					"Region" => "Asia", 
					"Country" => "PK"), 
				array(
					"Region" => "Asia", 
					"Country" => "PW"), 
				array(
					"Region" => "South America", 
					"Country" => "PA"), 
				array(
					"Region" => "Asia", 
					"Country" => "PG"), 
				array(
					"Region" => "South America", 
					"Country" => "PY"), 
				array(
					"Region" => "South America", 
					"Country" => "PE"), 
				array(
					"Region" => "Asia", 
					"Country" => "PH"), 
				array(
					"Region" => "East Europe", 
					"Country" => "PL"), 
				array(
					"Region" => "West Europe", 
					"Country" => "PT"), 
				array(
					"Region" => "South America", 
					"Country" => "PR"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "QA"), 
				array(
					"Region" => "Africa", 
					"Country" => "RE"), 
				array(
					"Region" => "East Europe", 
					"Country" => "RO"), 
				array(
					"Region" => "East Europe", 
					"Country" => "RU"), 
				array(
					"Region" => "Africa", 
					"Country" => "RW"), 
				array(
					"Region" => "Africa", 
					"Country" => "SH"), 
				array(
					"Region" => "South America", 
					"Country" => "KN"), 
				array(
					"Region" => "South America", 
					"Country" => "LC"), 
				array(
					"Region" => "North America", 
					"Country" => "PM"), 
				array(
					"Region" => "South America", 
					"Country" => "VC"), 
				array(
					"Region" => "Asia", 
					"Country" => "WS"), 
				array(
					"Region" => "West Europe", 
					"Country" => "SM"), 
				array(
					"Region" => "Africa", 
					"Country" => "ST"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "SA"), 
				array(
					"Region" => "Africa", 
					"Country" => "SN"), 
				array(
					"Region" => "East Europe", 
					"Country" => "RS"), 
				array(
					"Region" => "Africa", 
					"Country" => "SC"), 
				array(
					"Region" => "Africa", 
					"Country" => "SL"), 
				array(
					"Region" => "Asia", 
					"Country" => "SG"), 
				array(
					"Region" => "East Europe", 
					"Country" => "SK"), 
				array(
					"Region" => "East Europe", 
					"Country" => "SI"), 
				array(
					"Region" => "Asia", 
					"Country" => "SB"), 
				array(
					"Region" => "Africa", 
					"Country" => "SO"), 
				array(
					"Region" => "Africa", 
					"Country" => "ZA"), 
				array(
					"Region" => "West Europe", 
					"Country" => "ES"), 
				array(
					"Region" => "Asia", 
					"Country" => "LK"), 
				array(
					"Region" => "Africa", 
					"Country" => "SD"), 
				array(
					"Region" => "South America", 
					"Country" => "SR"), 
				array(
					"Region" => "Africa", 
					"Country" => "SZ"), 
				array(
					"Region" => "West Europe", 
					"Country" => "SE"), 
				array(
					"Region" => "West Europe", 
					"Country" => "CH"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "SY"), 
				array(
					"Region" => "Asia", 
					"Country" => "TW"), 
				array(
					"Region" => "East Europe", 
					"Country" => "TJ"), 
				array(
					"Region" => "Africa", 
					"Country" => "TZ"), 
				array(
					"Region" => "Asia", 
					"Country" => "TH"), 
				array(
					"Region" => "Africa", 
					"Country" => "TG"), 
				array(
					"Region" => "Asia", 
					"Country" => "TO"), 
				array(
					"Region" => "South America", 
					"Country" => "TT"), 
				array(
					"Region" => "Africa", 
					"Country" => "TN"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "TR"), 
				array(
					"Region" => "East Europe", 
					"Country" => "TM"), 
				array(
					"Region" => "South America", 
					"Country" => "TC"), 
				array(
					"Region" => "Asia", 
					"Country" => "TV"), 
				array(
					"Region" => "Africa", 
					"Country" => "UG"), 
				array(
					"Region" => "East Europe", 
					"Country" => "UA"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "AE"), 
				array(
					"Region" => "West Europe", 
					"Country" => "AE"), 
				array(
					"Region" => "North America", 
					"Country" => "US"), 
				array(
					"Region" => "South America", 
					"Country" => "UY"), 
				array(
					"Region" => "East Europe", 
					"Country" => "UZ"), 
				array(
					"Region" => "Asia", 
					"Country" => "VU"), 
				array(
					"Region" => "South America", 
					"Country" => "VE"), 
				array(
					"Region" => "Asia", 
					"Country" => "VN"), 
				array(
					"Region" => "South America", 
					"Country" => "VG"), 
				array(
					"Region" => "Asia", 
					"Country" => "WF"), 
				array(
					"Region" => "Africa", 
					"Country" => "EH"), 
				array(
					"Region" => "Arab Countries", 
					"Country" => "YE"), 
				array(
					"Region" => "Africa", 
					"Country" => "ZM"), 
				array(
					"Region" => "Africa", 
					"Country" => "ZW")
			);

			foreach($country_list as $country){								
				$success = $wpdb->insert($woo_sales_country_table_name, array(
					"country" => $country['Country'],
					"region" => $country['Region'],
				));
			}
		}		
		
	}	
} // end \WC_Location_Report class


WC_Country_Report::init();