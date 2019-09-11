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
		
		$this->report_data->c_orders = (array) $this->get_order_report_data(
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
		global $wp_locale;
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
		arsort($country_data);
		
		$index = 0;
		$country_sort_order = array();
		foreach($country_data as $country=>$sales){
			$country_sort_order[$index] = $country;
			$index++;
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
		$export_data = array_merge(array_flip((array)$country_sort_order), $export_data);
		
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
			'title' => sprintf( __( '%s sales in this period', 'woo-sales-country-reports' ), '<strong>' . $total . '</strong>' ),
			'placeholder' => $placeholder,			
			'highlight_series' => 1,
		);
		
		$legend[] = array(
			'title' => sprintf( __( '%s orders in this period', 'woo-sales-country-reports' ), '<strong>' . $count_total . '</strong>' ),
			'placeholder' => $count_placeholder,			
			'highlight_series' => 1,
		);

		$legend[] = array(
			'title' => sprintf( __( '%s countries in this period', 'woo-sales-country-reports' ), '<strong>' . ( isset( $country_data['UNDEFINED'] ) ? count( $country_data ) - 1 :count( $country_data ) ) . '</strong>' ),
			'placeholder' => __( 'This is the total number of countries represented in this report.', 'woo-sales-country-reports' ),			
			'highlight_series' => 2,
		);

		/* Export Code */
		$export_array = array();
		$report_type = ( 'number-orders' == $this->totals_by ) ? 'count' : '';
		
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
		
		
		?>				
		<script type="text/javascript">
			 AmCharts.makeChart("chartdiv",
				{
					"type": "serial",
					"categoryField": "country",
					"startDuration": 1,
					"handDrawScatter": 4,
					"theme": "light",
					"categoryAxis": {
						"autoRotateAngle": 61.2,
						"autoRotateCount": 0,
						"autoWrap": true,
						"gridPosition": "start",
						"minHorizontalGap": 78,
						"offset": 1
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": " [[country]] : <?php echo get_woocommerce_currency_symbol(); ?>[[value]]",
							"bulletBorderThickness": 7,
							"colorField": "color",
							"fillAlphas": 1,
							"id": "AmGraph-1",
							"lineColorField": "color",
							"title": "graph 1",
							"type": "column",
							"valueField": "sales"
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": ""
						}
					],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": ""
						}
					],
					"dataProvider": [
						<?php
												
						$index = 0;
						$max_data = 10;
						foreach($country_data as $key=>$value){ ?>
							{
								"country": "<?php echo trim(preg_replace('/\s*\([^)]*\)/', '', WC()->countries->countries[ $key ]));  ?>",
								"sales": <?php echo $value; ?>,
								"color": "<?php echo $this->chart_colours[$index]; ?>"
							},
						<?php $index++;
								if($index==$max_data) break;
						} ?>
					]					
				}
			);
		</script>						
		<!-- amCharts javascript code -->
		<script type="text/javascript">
			var country_chart = AmCharts.makeChart("graph_chartdiv",
				{
					"type": "serial",
					"categoryField": "country",
					"startDuration": 1,
					"fontSize": 13,
					"theme": "light",
					"categoryAxis": {
						"autoRotateAngle": 61.2,
						"autoRotateCount": 0,
						"autoWrap": true,
						"gridPosition": "start",
						"minHorizontalGap": 78,
						"offset": 1
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": "[[country]]:<?php echo get_woocommerce_currency_symbol(); ?>[[value]]",
							"bullet": "round",
							"id": "AmGraph-1",
							"title": "graph 1",
							"valueField": "sales",
							"visibleInLegend": false
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": ""
						}
					],
					"allLabels": [],
					"balloon": {},
					"legend": {
						"enabled": true,
						"useGraphSettings": true
					},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": ""
						}
					],
					"dataProvider": [
						<?php
												
						$index = 0;
						$max_data = 10;
						foreach($country_data as $key=>$value){ ?>
							{
								"country": "<?php echo trim(preg_replace('/\s*\([^)]*\)/', '', WC()->countries->countries[ $key ]));  ?>",
								"sales": <?php echo $value; ?>
							},
						<?php $index++;
								if($index==$max_data) break;
						} ?>					
					],
					"export": {
						"enabled": true,
						"menu": []
					}
				}
			);
		</script>		
		
		<script type="text/javascript">
			AmCharts.makeChart("pie_chartdiv",
				{
					"type": "pie",
					"angle": 16.2,
					"balloonText": "[[title]]<br><span style='font-size:14px'><b><?php echo get_woocommerce_currency_symbol(); ?>[[value]]</b> ([[percents]]%)</span>",
					"depth3D": 15,
					"colors": [
						"#3498db",
						"#34495e",
						"#1abc9c",
						"#ff0000",
						"#f1c40f",
						"#e67e22",
						"#e74c3c",
						"#2980b9",
						"#8e44ad",
						"#2c3e50",
						"#16a085",
						"#27ae60",
						"#f39c12",
						"#d35400",
						"#c0392b",
						"#AF2460",
						"#E761BD",
						"#7E05A3",
						"#91EFF7",
						"#C0CE13",
						"#102992",
						"#EF0FD0",
						"#916B7B",
						"#94C52D",
						"#C41D18",
						"#5DF12B",
						"#1D90FC",
						"#C68656",
						"#6DE821",
						"#11CADA",
						"#FA17F0",
						"#CBDD3C"
					],
					"titleField": "category",
					"valueField": "column-1",
					"theme": "light",
					"allLabels": [],
					"balloon": {},
					"titles": [],
					"dataProvider": [
						<?php
												
						$index = 0;
						$max_data = 10;
						foreach($country_data as $key=>$value){ ?>
							{
								"category": "<?php echo $key; ?>",
								"column-1": <?php echo $value; ?>
							},
						<?php $index++;
								if($index==$max_data) break;
						} ?>
					]
				}
			);
			function exportCSV() {
				country_chart.export.toCSV({}, function(data) {
					this.download(data, this.defaults.formats.CSV.mimeType, "country_report.csv");
				});
			}
		</script>
		<?php 
		/* / Export Code */

		return $legend;
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
			'title'    => __( 'Top 10 Countries', 'woo-sales-country-reports' ),
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
		$country_order_count = array();
		$country_data = array();
	
		foreach ( $data->orders as $location_values ) {			
			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}
			$countries_data = $location_values->countries_data;	
			$total_sales = $location_values->total_sales;
			$country_data[ $countries_data ] = ( isset( $country_data[ $countries_data ] ) ) ? $total_sales + $country_data[ $countries_data ] : $total_sales;		

			$country_order_count[ $countries_data ] = ( isset( $country_order_count[ $countries_data ] ) ) ? $location_values->count + $country_order_count[ $countries_data ] : $location_values->count;	
			$export_data[ $countries_data ][] = $location_values;			
		}
		arsort($country_data);		
		?>
			<table class="sales-country-table widefat fixed posts">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Country', 'woo-sales-country-reports' ); ?></th>
                        <th><?php esc_html_e( 'Sales', 'woo-sales-country-reports' ); ?></th>
						<th><?php esc_html_e( '# of orders', 'woo-sales-country-reports' ); ?></th>
						<th><?php esc_html_e( 'Avg orders amount', 'woo-sales-country-reports' ); ?></th>
                    </tr>
                </thead>

                <tbody>
                <?php 
				$index = 0;
				$max_data=10;
				foreach ( $country_data as $key=>$value ) :				
				$percentage = ( round( $value, 2 ) / $this->total ) * 100;				
				$color  = isset( $this->chart_colours[ $index ] ) ? $this->chart_colours[ $index ] : $this->chart_colours[0];
				?>
                    <tr>
                        <td><?php echo trim(preg_replace('/\s*\([^)]*\)/', '', WC()->countries->countries[ $key ])); ?></td>
                        <td><?php echo get_woocommerce_currency_symbol() . number_format(round( $value )); ?> (<?php echo round( $percentage,1 ); ?>%)</td>
						<td style=""><?php echo $country_order_count[$key]; ?></td>	
						<td style="border-right: 5px solid <?php echo $color; ?>;"><?php echo round($value/$country_order_count[$key]); ?></td>							
					</tr>
                <?php 
				$index++;
				if($index==$max_data) break; 
				endforeach; ?>
                </tbody>
            </table>
	<?php }
	
	public function country_region_widget(){
		$data = $this->get_report_data();
		$country_data = array();
		foreach ( $data->c_orders as $location_values ) {
		
			if ( '' == $location_values->countries_data ) {
				$location_values->countries_data = 'UNDEFINED';
			}
		
			$country_data[ $location_values->countries_data ] = ( isset( $country_data[ $location_values->countries_data ] ) ) ? $location_values->total_sales + $country_data[ $location_values->countries_data ] : $location_values->total_sales;
		
			$export_data[ $location_values->countries_data ][] = $location_values;
		}
		if(isset($country_data)){
			arsort($country_data);	
		}		
		?>
		<h4 class="section_title"><span><?php esc_html_e( 'Sales by country', 'woo-sales-country-reports' ); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
                        <select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select country&hellip;', 'woo-sales-country-reports' ); ?>" class="wc-enhanced-select" id="show_countries" name="show_countries[]" style="width: 205px;">
                            <?php 
                            $index = 0;
                            $max_data=10;
                            foreach($country_data as $key=>$value){ ?>
                                <option value="<?php echo $key; ?>" <?php if (in_array($key, $this->show_countries)) {echo 'selected'; } ?>><?php echo WC()->countries->countries[ $key ]; ?></option>
                            <?php 
                            $index++;
                            if($index==$max_data) break; 
                            }					
                            ?>
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
				<?php /*if ( !empty( $this->show_country ) ) {?>
					jQuery('.section_title:eq(0)').click();
				<?php } elseif(!empty( $this->show_region )){ ?>
					jQuery('.section_title:eq(1)').click();
				<?php } else{ ?>
					jQuery('.section_title:eq(0)').click();	
				<?php }*/ ?>				
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

		//include( WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php' );
		include 'html/html-report-by-date.php';

	}

	/**
	 * Output an export link
	 *
	 * @since 1.0
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';			
		?>			
		<!--a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time( 'timestamp' ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php _e( 'Date', 'woo-sales-country-reports' ); ?>"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'woo-sales-country-reports' ); ?>
		</a-->
		<a href="JavaScript:Void(0);" class="export_csv" onclick="exportCSV();">
			<?php _e( 'Export CSV', 'woo-sales-country-reports' ); ?>
		</a>		
		<a href="JavaScript:Void(0);" class="report_type_tab report_type_link dashicons-before dashicons-chart-bar active" data-type="chartdiv"></a>
		<a href="JavaScript:Void(0);" class="report_type_tab inactive report_type_link dashicons-before dashicons-chart-line inactive" data-type="graph_chartdiv"></a>
		<a href="JavaScript:Void(0);" class="report_type_tab inactive report_type_link dashicons-before dashicons-chart-pie inactive" data-type="pie_chartdiv"></a>
		<?php
	}

	/**
	 * Main Chart : Add the placeholder javascript /div for the location report
	 *
	 * @since 1.0
	 */
	public function get_main_chart() { ?>		
		
		<div class="chart-container">			
			<div id="chartdiv" class="bar_chart" style="width: 100%;height: 448px;"></div>
			<div id="graph_chartdiv" class="bar_chart" style="width: 100%;height: 448px;display:none;"></div>
			<div id="pie_chartdiv" class="bar_chart" style="width: 100%;height: 448px;display:none;"></div>	
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