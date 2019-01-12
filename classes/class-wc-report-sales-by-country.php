<?php
/**
 * WC_Report_Sales_By_Location
 *
 * @author      ChuckMac Development (chuckmacdev.com)
 * @category    Admin
 * @package     WooCommerce/Admin/Reports
 * @version     1.1
 */

class WC_Report_Sales_By_Country extends WC_Admin_Report {

	public $chart_colours = array();

	public $location_data;
	public $location_by;
	public $totals_by;
	public $show_countries = array();
	public $show_region = array();
	private $report_data;	
	public function __construct() {
		if ( isset( $_GET['show_countries'] ) ) {			
			$this->show_countries = wp_unslash($_GET['show_countries']);			
		}
		if ( isset( $_GET['show_region'] ) ) {			
			$this->show_region = wp_unslash($_GET['show_region']);
		}		
	}

	/**
	 * Get report data
	 * @return array
	 */
	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class
	 */
	private function query_report_data() {

		$this->report_data = new stdClass;

		$this->report_data->orders = (array) $this->get_order_report_data(
			array(
				'data' => array(
					'_' . $this->location_by . '_country' => array(
						'type'     => 'meta',
						'name'     => 'countries_data',
						'function' => null,
					),
					'_order_total' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),				
				'group_by'            => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), meta__' . $this->location_by . '_country.meta_value',				
				'order_by'            => 'total_sales DESC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => array_merge( array( 'shop_order_refund' ), wc_get_order_types( 'sales-reports' ) ),
				'order_status'        => array( 'completed', 'processing', 'on-hold' ),
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
				)
		);
		
		$this->report_data->or_orders = (array) $this->get_order_report_data(
			array(
				'data' => array(
					'_' . $this->location_by . '_country' => array(
						'type'     => 'meta',
						'name'     => 'countries_data',
						'function' => null,
					),
					'_order_total' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'_order_total' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),				
				'group_by'            => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), meta__' . $this->location_by . '_country.meta_value',				
				'order_by'            => 'total_sales DESC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => array_merge( array( 'shop_order_refund' ), wc_get_order_types( 'sales-reports' ) ),
				'order_status'        => array( 'completed', 'processing', 'on-hold' ),
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
				)
		);	
		
		$this->report_data->order_counts = (array) $this->get_order_report_data(
			array(
				'data' => array(
					'_' . $this->location_by . '_country' => array(
						'type'     => 'meta',
						'name'     => 'countries_data',
						'function' => null,
					),
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'            => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), meta__' . $this->location_by . '_country.meta_value',
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => wc_get_order_types( 'order-count' ),
				'order_status'        => array( 'completed', 'processing', 'on-hold' ),
			)
		);

	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array Array of report legend data
	 * @since 1.0
	 */
	public function get_chart_legend() {

		$this->location_by   = ( isset( $_REQUEST['location_filter'] ) ? sanitize_text_field($_REQUEST['location_filter']) : 'shipping' );
		$this->totals_by     = ( isset( $_REQUEST['report_by'] ) ? sanitize_text_field($_REQUEST['report_by']) : 'order-total' );
		$this->report_type     = ( isset( $_REQUEST['report_type'] ) ? sanitize_text_field($_REQUEST['report_type']) : 'chart' );
		
		$data = $this->get_report_data();
		
		if($this->show_region){
			global $wpdb;		
			$woo_sales_country_table_name = $wpdb->prefix . 'woo_sales_country_region';			
			$region_country = $wpdb->get_results( "SELECT country FROM $woo_sales_country_table_name WHERE region in ('" . implode("','", $this->show_region) . "')",ARRAY_A  );
			$singleArray = []; 
			foreach ($region_country as $childArray) 
			{ 
				foreach ($childArray as $value) 
				{ 
				$single_region_country[] = $value; 
				} 
			}
			$this->show_countries = $single_region_country;
		}		
		if($this->show_countries){
			foreach($data->orders as $key=>$value){
				
				if(!in_array($value->countries_data, $this->show_countries)){
					 unset($data->orders[$key]);
				}
			}
			foreach($data->or_orders as $key=>$value){
				
				if(!in_array($value->countries_data, $this->show_countries)){
					 unset($data->or_orders[$key]);
				}
			}
			foreach($data->order_counts as $key=>$value){
				
				if(!in_array($value->countries_data, $this->show_countries)){
					 unset($data->order_counts[$key]);
				}
			}
		}		
		
		add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'location_report_add_count' ) );

		//Loop through the returned data and set depending on sales or order totals
		$country_data = array();
		$country_count_data = array();
		$export_data = array();

		foreach ( $data->orders as $location_values ) {

			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}

			$country_data[ $location_values->countries_data ] = ( isset( $country_data[ $location_values->countries_data ] ) ) ? $location_values->total_sales + $country_data[ $location_values->countries_data ] : $location_values->total_sales;
			
			$export_data[ $location_values->countries_data ][] = $location_values;
		}

		$placeholder = __( 'This is the sum of the order totals after any refunds and including shipping and taxes.', 'woo-sales-country-reports' );
		
		foreach ( $data->order_counts as $location_values ) {
			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}

			$country_count_data[ $location_values->countries_data ] = ( isset( $country_count_data[ $location_values->countries_data ] ) ) ? $location_values->count + $country_count_data[ $location_values->countries_data ] : $location_values->count;

			$export_data[ $location_values->countries_data ][] = $location_values;
		}			
		$count_placeholder = __( 'This is the count of orders during this period.', 'woo-sales-country-reports' );
		
		//Pass the data to the screen.
		$this->location_data = $country_data;		
		$sales_data = $this->location_data;
		array_walk( $sales_data, function( &$value, $index ) {
			$value = strip_tags( wc_price( $value ) );
		} );		

		$legend = array();

		$count_total = array_sum( $country_count_data );
		$total = array_sum( $country_data );
		$this->total = $total;
		if ( 'order-total' == $this->totals_by ) {
			$total = wc_price( $total );
		}
		
		$legend[] = array(
			'title' => sprintf( __( '%s orders in this period', 'woo-sales-country-reports' ), '<strong>' . $total . '</strong>' ),
			'placeholder' => $placeholder,
			'color' => $this->chart_colours['order_total'],
			'highlight_series' => 1,
		);
		
		$legend[] = array(
			'title' => sprintf( __( '%s orders in this period', 'woo-sales-country-reports' ), '<strong>' . $count_total . '</strong>' ),
			'placeholder' => $count_placeholder,
			'color' => $this->chart_colours['order_total'],
			'highlight_series' => 1,
		);

		$legend[] = array(
			'title' => sprintf( __( '%s countries in this period', 'woo-sales-country-reports' ), '<strong>' . ( isset( $country_data['UNDEFINED'] ) ? count( $country_data ) - 1 :count( $country_data ) ) . '</strong>' ),
			'placeholder' => __( 'This is the total number of countries represented in this report.', 'woo-sales-country-reports' ),
			'color' => $this->chart_colours['individual_total'],
			'highlight_series' => 2,
		);

		/* Export Code */
		$export_array = array();
		$report_type = ( 'number-orders' == $this->totals_by ) ? 'count' : 'total_sales';

		foreach ( $export_data as $country => $data ) {
			
			$export_prep = $this->prepare_chart_data( $data, 'post_date', $report_type, $this->chart_interval, $this->start_date, $this->chart_groupby );
			$export_array[ $country ] = array_values( $export_prep );
		}

		// Move undefined to the end of the data
		if ( isset( $export_array['UNDEFINED'] ) ) {
			$temp = $export_array['UNDEFINED'];
			unset( $export_array['UNDEFINED'] );
			$export_array['UNDEFINED'] = $temp;
		}
		
		// Encode in json format
		$chart_data = json_encode( $export_array );	
		$report_type = $this->report_type;
		
		if($report_type == 'chart'){
		?>
		<script type="text/javascript">
			var main_chart;

			jQuery(function(){
				var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
				var series = [
					<?php
						$index = 0;
						foreach ( $export_array as $country => $data ) {
							
							$color  = isset( $this->chart_colours[ $index ] ) ? $this->chart_colours[ $index ] : $this->chart_colours[0];
							$width  = $this->barwidth / sizeof( $export_array );
							$offset = ( $width * $index );
							$series = $export_array[$country];
							
							foreach ( $series as $key => $series_data ) {
								$series[ $key ][0] = $series_data[0] + $offset;
								$count = $series[ $key ][2];								
							}								
							$country_name = WC()->countries->countries[ $country ];
							echo '{
									label: "' . esc_js( $country_name ) . '",
									data: jQuery.parseJSON( "' . json_encode( $series ) . '" ),
									color: "' . $color . '",
									bars: {
										fillColor: "' . $color . '",
										fill: true,
										show: true,
										lineWidth: 1,
										align: "center",
										barWidth: ' . $width * 0.75 . ',
										stack: false
									},
									' . $this->get_currency_tooltip() . ',
									append_tooltip: "",
									enable_tooltip: true,
									prepend_label: true
								},';
							$index++;
						}
					?>
				];
				main_chart = jQuery.plot(
					jQuery('.chart-placeholder.main'),
						series,
					{
						legend: {
							show: true
						},
						grid: {
							color: '#aaa',
							borderColor: 'transparent',
							borderWidth: 0,
							hoverable: true
						},
						xaxes: [ {
							color: '#aaa',
							reserveSpace: true,
							position: "bottom",
							tickColor: 'transparent',
							mode: "time",
							timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
							monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ); ?>,
							tickLength: 1,
							minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
							tickSize: [1, "<?php echo $this->chart_groupby; ?>"],
							font: {
								color: "#aaa"
							}
						} ],
						yaxes: [
							{
								min: 0,
								tickDecimals: 2,
								color: 'transparent',
								font: { color: "#aaa" }
							}
						],
					}
					);
			});

		</script>
		<?php 
		} else{
		?>
		<script type="text/javascript">
			var main_chart;

			jQuery(function(){
				var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

				var series = [
					<?php
					$index = 0;
					foreach ( $export_array as $country => $data ) {
						$country_name = WC()->countries->countries[ $country ];
						$color  = isset( $this->chart_colours[ $index ] ) ? $this->chart_colours[ $index ] : $this->chart_colours[0];
						echo "{\n     label: \"$country_name\",\n     data: order_data.$country,\n  color: \"$color\",\n   enable_tooltip: true,\n  prepend_label: true\n },";
						$index++;
					}
					?>
				];				
				main_chart = jQuery.plot(
					jQuery('.chart-placeholder.main'),
						series,
					{
						legend: {
							show: true
						},
						grid: {
							color: '#aaa',
							borderColor: 'transparent',
							borderWidth: 0,
							hoverable: true
						},
						xaxes: [ {
							color: '#aaa',
							reserveSpace: true,
							position: "bottom",
							tickColor: 'transparent',
							mode: "time",
							timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
							monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ); ?>,
							tickLength: 1,
							minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
							tickSize: [1, "<?php echo $this->chart_groupby; ?>"],
							font: {
								color: "#aaa"
							}
						} ],
						yaxes: [
							{
								min: 0,
								tickDecimals: 2,
								color: 'transparent',
								font: { color: "#aaa" }
							}
						],
					}
					);
			});

		</script>		
		<?php
		}	
		/* / Export Code */

		return $legend;
	}
	/**
	 * Put data with post_date's into an array of times.
	 *
	 * @param  array  $data array of your data
	 * @param  string $date_key key for the 'date' field. e.g. 'post_date'
	 * @param  string $data_key key for the data you are charting
	 * @param  int    $interval
	 * @param  string $start_date
	 * @param  string $group_by
	 * @return array
	 */
	public function prepare_chart_data( $data, $date_key, $data_key, $interval, $start_date, $group_by ) {
		$prepared_data = array();
			
		// Ensure all days (or months) have values in this range.
		if ( 'day' === $group_by ) {
			for ( $i = 0; $i <= $interval; $i ++ ) {
				$time = strtotime( date( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}
			}
		} else {
			$current_yearnum  = date( 'Y', $start_date );
			$current_monthnum = date( 'm', $start_date );

			for ( $i = 0; $i <= $interval; $i ++ ) {
				$time = strtotime( $current_yearnum . str_pad( $current_monthnum, 2, '0', STR_PAD_LEFT ) . '01' ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}

				$current_monthnum ++;

				if ( $current_monthnum > 12 ) {
					$current_monthnum = 1;
					$current_yearnum  ++;
				}
			}
		}

		foreach ( $data as $d ) {
			switch ( $group_by ) {
				case 'day':
					$time = strtotime( date( 'Ymd', strtotime( $d->$date_key ) ) ) . '000';
					break;
				case 'month':
				default:
					$time = strtotime( date( 'Ym', strtotime( $d->$date_key ) ) . '01' ) . '000';
					break;
			}

			if ( ! isset( $prepared_data[ $time ] ) ) {
				continue;
			}
			
			if ( $data_key ) {
				$prepared_data[ $time ][1] += $d->$data_key;
			} else {
				$prepared_data[ $time ][1] ++;
			}
		}		
		return $prepared_data;
	}
	/**
	 * Add our map widgets to the report screen
	 *
	 * @return array Array of location report widgets
	 * @since 1.0
	 */
	public function get_chart_widgets() {

		$widgets = array();		
		
		$widgets[] = array(
			'title'    => __( 'Top Countries', 'woo-sales-country-reports' ),
			'callback' => array( $this, 'top_country_widget' ),
		);
		
		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'country_region_widget' ),
		);				

		return $widgets;
	}
	
	
	public function top_country_widget() {
		$data = $this->get_report_data();
		foreach ( $data->orders as $location_values ) {
			
			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}
		
			$country_data[ $location_values->countries_data ] = ( isset( $country_data[ $location_values->countries_data ] ) ) ? $location_values->total_sales + $country_data[ $location_values->countries_data ] : $location_values->total_sales;
			
			$country_order_count[ $location_values->countries_data ] = $country_order_count[ $location_values->countries_data ] + $location_values->count;		
			$export_data[ $location_values->countries_data ][] = $location_values;
		}		
		?>
			<table class="sales-country-table widefat fixed posts">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Sales</th>
						<th># of orders </th>
                    </tr>
                </thead>

                <tbody>
                <?php 
				$index = 0;
				foreach ( $country_data as $key=>$value ) :				
				$percentage = ( round( $value, 2 ) / $this->total ) * 100;				
				$color  = isset( $this->chart_colours[ $index ] ) ? $this->chart_colours[ $index ] : $this->chart_colours[0];
				?>
                    <tr>
                        <td><?php echo WC()->countries->countries[ $key ]; ?></td>
                        <td><?php echo get_woocommerce_currency_symbol() . round( $value, 2 ); ?> (<?php echo round( $percentage ); ?>%)</td>
						<td style="border-right: 5px solid <?php echo $color; ?>;text-align:center;"><?php echo $country_order_count[$key]; ?></td>	                 
					</tr>
                <?php 
				$index++;
				endforeach; ?>
                </tbody>
            </table>
	<?php }
	
	public function country_region_widget(){
		$data = $this->get_report_data();
		foreach ( $data->orders as $location_values ) {
		
			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}
		
			$country_data[ $location_values->countries_data ] = ( isset( $country_data[ $location_values->countries_data ] ) ) ? $location_values->total_sales + $country_data[ $location_values->countries_data ] : $location_values->total_sales;
		
			$export_data[ $location_values->countries_data ][] = $location_values;
		}
		$country = WC()->countries->countries[ 'IN' ];
		if(!($this->show_countries)){			
			foreach($data->orders as $key=>$value){
				$this->show_countries[$key] = $value->countries_data;
			}
		}
		
		?>
		<h4 class="section_title"><span><?php esc_html_e( 'Sales by country', 'woo-sales-country-reports' ); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select country&hellip;', 'woo-sales-country-reports' ); ?>" class="wc-enhanced-select" id="show_countries" name="show_countries[]" style="width: 205px;">
						<?php foreach($country_data as $key=>$value){ ?>
							<option value="<?php echo $key; ?>" <?php if (in_array($key, $this->show_countries)) {echo 'selected'; } ?>><?php echo WC()->countries->countries[ $key ]; ?></option>
						<?php } ?>
					</select>
					<?php // @codingStandardsIgnoreStart ?>
					<a href="#" class="select_none"><?php esc_html_e( 'None', 'woo-sales-country-reports' ); ?></a>
					<a href="#" class="select_all"><?php esc_html_e( 'All', 'woo-sales-country-reports' ); ?></a>
					<button type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'woo-sales-country-reports' ); ?>"><?php esc_html_e( 'Show', 'woo-sales-country-reports' ); ?></button>
					<input type="hidden" name="range" value="<?php echo ( ! empty( $_GET['range'] ) ) ? esc_attr( wp_unslash( $_GET['range'] ) ) : ''; ?>" />
					<input type="hidden" name="start_date" value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" />
					<input type="hidden" name="end_date" value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" />
					<input type="hidden" name="page" value="<?php echo ( ! empty( $_GET['page'] ) ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; ?>" />
					<input type="hidden" name="tab" value="<?php echo ( ! empty( $_GET['tab'] ) ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : ''; ?>" />
					<input type="hidden" name="report" value="<?php echo ( ! empty( $_GET['report'] ) ) ? esc_attr( wp_unslash( $_GET['report'] ) ) : ''; ?>" />
					<?php // @codingStandardsIgnoreEnd ?>
				</div>
				<script type="text/javascript">
					jQuery(function(){
						// Select all/None
						jQuery( '.chart-widget' ).on( 'click', '.select_all', function() {
							jQuery(this).closest( 'div' ).find( 'select option' ).attr( 'selected', 'selected' );
							jQuery(this).closest( 'div' ).find('select').change();
							return false;
						});
	
						jQuery( '.chart-widget').on( 'click', '.select_none', function() {
							jQuery(this).closest( 'div' ).find( 'select option' ).removeAttr( 'selected' );
							jQuery(this).closest( 'div' ).find('select').change();
							return false;
						});
					});
				</script>
			</form>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Sales by region', 'woo-sales-country-reports' ); ?></span></h4>
		<div class="section">
			<?php
			$data = $this->get_report_data();
			
			global $wpdb;
			$woo_sales_country_table_name = $wpdb->prefix . 'woo_sales_country_region';
			$region = $wpdb->get_results( "SELECT region FROM $woo_sales_country_table_name GROUP BY region" );				
			
			?>
			<form method="GET">
				<div>
					<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select region&hellip;', 'woo-sales-country-reports' ); ?>" class="wc-enhanced-select" id="show_region" name="show_region[]" style="width: 205px;">
						<?php foreach($region as $rg){ ?>
							<option value="<?php echo $rg->region; ?>" <?php if (in_array($rg->region, $this->show_region)) {echo 'selected'; } ?>><?php echo $rg->region; ?></option>
						<?php } ?>
					</select>
					<?php // @codingStandardsIgnoreStart ?>
					<a href="#" class="select_none"><?php esc_html_e( 'None', 'woo-sales-country-reports' ); ?></a>
					<a href="#" class="select_all"><?php esc_html_e( 'All', 'woo-sales-country-reports' ); ?></a>
					<button type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'woo-sales-country-reports' ); ?>"><?php esc_html_e( 'Show', 'woo-sales-country-reports' ); ?></button>
					<input type="hidden" name="range" value="<?php echo ( ! empty( $_GET['range'] ) ) ? esc_attr( wp_unslash( $_GET['range'] ) ) : ''; ?>" />
					<input type="hidden" name="start_date" value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" />
					<input type="hidden" name="end_date" value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" />
					<input type="hidden" name="page" value="<?php echo ( ! empty( $_GET['page'] ) ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; ?>" />
					<input type="hidden" name="tab" value="<?php echo ( ! empty( $_GET['tab'] ) ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : ''; ?>" />
					<input type="hidden" name="report" value="<?php echo ( ! empty( $_GET['report'] ) ) ? esc_attr( wp_unslash( $_GET['report'] ) ) : ''; ?>" />
					<?php // @codingStandardsIgnoreEnd ?>
				</div>
				<script type="text/javascript">
					jQuery(function(){
						// Select all/None
						jQuery( '.chart-widget' ).on( 'click', '.select_all', function() {
							jQuery(this).closest( 'div' ).find( 'select option' ).attr( 'selected', 'selected' );
							jQuery(this).closest( 'div' ).find('select').change();
							return false;
						});
	
						jQuery( '.chart-widget').on( 'click', '.select_none', function() {
							jQuery(this).closest( 'div' ).find( 'select option' ).removeAttr( 'selected' );
							jQuery(this).closest( 'div' ).find('select').change();
							return false;
						});
					});
				</script>
			</form>	
		</div>
		<script type="text/javascript">
			jQuery('.section_title').click(function(){
				var next_section = jQuery(this).next('.section');

				if ( jQuery(next_section).is(':visible') )
					return false;

				jQuery('.section:visible').slideUp();
				jQuery('.section_title').removeClass('open');
				jQuery(this).addClass('open').next('.section').slideDown();

				return false;
			});
			jQuery('.section').slideUp( 100, function() {
				<?php if ( !empty( $this->show_country ) ) {?>
					jQuery('.section_title:eq(0)').click();
				<?php } elseif(!empty( $this->show_region )){ ?>
					jQuery('.section_title:eq(1)').click();
				<?php } else{ ?>
					jQuery('.section_title:eq(0)').click();	
				<?php } ?>				
			});
		</script>
		<?php
	}	

	/**
	 * Output the report
	 *
	 * @since 1.0
	 */
	public function output_report() {

		$ranges = array(
			'year'         => __( 'Year', 'woo-sales-country-reports' ),
			'last_month'   => __( 'Last Month', 'woo-sales-country-reports' ),
			'month'        => __( 'This Month', 'woo-sales-country-reports' ),
			'7day'         => __( 'Last 7 Days', 'woo-sales-country-reports' ),
		);
		$this->chart_colours = array( '#3498db', '#34495e', '#1abc9c', '#ff0000', '#f1c40f', '#e67e22', '#e74c3c', '#2980b9', '#8e44ad', '#2c3e50', '#16a085', '#27ae60', '#f39c12', '#d35400', '#c0392b','#AF2460','#E761BD','#7E05A3','#91EFF7','#C0CE13','#102992','#EF0FD0','#916B7B','#94C52D','#C41D18','#5DF12B','#1D90FC','#C68656','#6DE821','#11CADA','#FA17F0','#CBDD3C');

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->calculate_current_range( $current_range );

		include( WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php' );

	}

	/**
	 * Output an export link
	 *
	 * @since 1.0
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';
		$report_type = ! empty( $_GET['report_type'] ) ? sanitize_text_field( $_GET['report_type'] ) : 'chart';		
		?>			
		<a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time( 'timestamp' ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php _e( 'Date', 'woo-sales-country-reports' ); ?>"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'woo-sales-country-reports' ); ?>
		</a>
		<a href="<?php echo add_query_arg( 'report_type', 'chart' ); ?>" class="report_type_link dashicons-before dashicons-chart-bar <?php if($report_type == 'chart'){ echo 'selected'; }?>"></a>
		<a href="<?php echo add_query_arg( 'report_type', 'graph' ); ?>" class="report_type_link dashicons-before dashicons-chart-line <?php if($report_type == 'graph'){ echo 'selected'; }?>"></a>
		<?php
	}

	/**
	 * Main Chart : Add the placeholder javascript /div for the location report
	 *
	 * @since 1.0
	 */
	public function get_main_chart() {
		global $wp_locale;			
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>			
		</div>
		<?php
	}

	/**
	 * Add the address count to the sql query
	 *
	 * @return string sql query data
	 * @since 1.0
	 */
	public function location_report_add_count( $query ) {

		$sql = preg_replace( '/^SELECT /', 'SELECT COUNT(meta__' . $this->location_by . '_country.meta_value) as countries_data_count, ', $query );
		return $sql;

	}
}