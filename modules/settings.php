<?php 
	
add_action('admin_menu', 'ji_item_menu');

function ji_item_menu() {
	add_options_page(  'WP Batch Jobs Upload', 'WP Batch Jobs Upload', 'manage_options', 'ji_config', 'ji_config');
}

function ji_config(){

?>
<div class="wrap">
<h2>WP Batch Jobs Upload</h2>
<br><br>
 <?php if( ($_POST['posted'] == 1) && (is_admin() ) && wp_verify_nonce($_POST['_wpnonce']) ): ?>
  
  
  <?php 
$cnt = 1;
  if (($handle = fopen( $_FILES['scv_list']["tmp_name"] , "r")) !== FALSE) {
  include 'Classes/PHPExcel/IOFactory.php';
	$inputFileName = $_FILES['scv_list']["tmp_name"];  // File to read
try {
	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
} catch(Exception $e) {
	die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,false,true);
foreach( $sheetData as $single_line){
	//   Sr.	Title	Description	Location	Catgory	Type	Company Name	Company Website	How to Apply	Salary	Job Tags	Publish Date

	 
	 if( $cnt <= 2 ){ $cnt++;  continue; }
	  
	
		// items
		$title = $single_line[B];
		if( !$title ){  continue; }
		//var_dump( $title );
		$descr = $single_line[C];
		//var_dump( $descr );
		$location = $single_line[D];
		//var_dump( $location );
		$cat = explode(",", $single_line[E] );
		//var_dump( $cat );
		$type = $single_line[F];
		//var_dump( $type );
		$company = $single_line[G];
		//var_dump( $company );
		$website = $single_line[H];
		//var_dump( $website );
		$how = $single_line[I];
		//var_dump( $how );
		$salary = $single_line[J];
		//var_dump( $salary );
		$tags = explode(",", $single_line[K]);
		//var_dump( $tags );
		$date = $single_line[L];    
		$date = PHPExcel_Style_NumberFormat::toFormattedString($date, "D/M/YYYY H:I:S");	

		$date_arr = explode( ' ', $date );
		
		$date_new = explode('/', $date_arr[0] );
    if( strlen( $date_new[0] ) == 1 ){
    $date_new[0] = '0'.$date_new[0];
    }
    if( strlen( $date_new[1] ) == 1 ){
    $date_new[1] = '0'.$date_new[1];
    }
    
    if( strlen( $date_new[2] ) == 2 ){
    $date_new[2] = '20'.$date_new[2];
    }
		$date = $date_new[2].'-'.$date_new[1].'-'.$date_new[0].' 00:00:00';
	
		//var_dump( $date );
		//var_dump( $type );
		$type_out = array();
		$type = get_term_by('name', $type, 'job_type');
		//var_dump( $type );
		$type_out[] = $type->term_id;
		//var_dump( (int)$salary );
		/*
		if( (int)$salary < 20000  ){
			$out_salary = 'Less than 20,000';
		}
		if( (int)$salary >= 20000 && (int)$salary <= 40000 ){
			$out_salary = '20,000 - 40,000';
		}
		if( (int)$salary >= 40000 && (int)$salary <= 60000 ){
			$out_salary = '40,000 - 60,000';
		}
		if( (int)$salary >= 60000 && (int)$salary <= 80000 ){
			$out_salary = '60,000 - 80,000';
		}
		if( (int)$salary >= 80000 && (int)$salary <= 100000 ){
			$out_salary = '80,000 - 100,000';
		}
		if( (int)$salary >= 100000  ){
			$out_salary = '100,000 and above';
		}
		$salary_out = array();
		$salary = get_term_by('name', $out_salary, 'job_salary');
		$salary_out[] = $salary->term_id;
		*/
		// processing post credentials
		
		$salary_arr = explode('.', $salary);
		$sal_arr = array();
		foreach( $salary_arr as $single_sal ){		
			if( !$single_sal ) continue;
				if( term_exists( trim( $single_sal ), 'job_salary') ){
					$tmp = term_exists( trim( $single_sal ), 'job_salary');	
					$sal_arr[] = (int)$tmp['term_id'];
				}else{
					
					$tmp = wp_insert_term( trim( $single_sal ), 'job_salary'  ) ;
					$sal_arr[] = (int)$tmp["term_id"];
				}
				
			}	
		
		
		// processing post credentials
		$cat_arr = array();
		foreach( $cat as $single_cat ){		
			if( !$single_cat ) continue;
				if( term_exists( trim( $single_cat ), 'job_cat') ){
					$tmp = term_exists( trim( $single_cat ), 'job_cat');	
					$cat_arr[] = (int)$tmp['term_id'];
				}else{
					
					$tmp = wp_insert_term( trim( $single_cat ), 'job_cat'  ) ;
					$cat_arr[] = (int)$tmp["term_id"];
				}
				
			}	

		
		//var_dump( $cat_arr );
		global $wpdb;
		$res = $wpdb->get_var("SELECT COUNT(post_title) FROM ".$wpdb->prefix."posts WHERE post_title = '".$title."' ");
		    
		if(  $title ){

			// process date
			//var_dump( $date_fixed );
			$date_fixed = str_replace( ' ', '', $date );
			$date_fixed = str_replace( ':', '', $date_fixed );
			$date_fixed = str_replace( '-', '', $date_fixed );

			if( $date_fixed > date( 'YmdHis' ) ){
				$status = 'future';
			}else{
				$status = 'draft';
			}
			
			$my_post = array(
			  'post_title'    => $title,
			  'post_date' => $date ,
			  'post_date_gmt' =>  $date ,
			  'post_content' => $descr,
			  'post_status'   => 'draft',
			  'post_author'   => 1,
			  'post_type' => 'job_listing',
			  'post_category'  => $cat_arr,
			  'tags_input'  => $tags
			  //
			);
	  // var_dump( $my_post );
			// Insert the post into the database
			$post_id = wp_insert_post( $my_post );
			//var_dump( $post_id );
			if( $post_id ){
				$big_cnt++;
				}
		//var_dump( $post_id );
			
			wp_set_post_terms( $post_id, $sal_arr, 'job_salary', true );
			wp_set_post_terms( $post_id, $cat_arr, 'job_cat', true );
			wp_set_post_terms( $post_id, $tags, 'job_tag', true );			
			wp_set_post_terms( $post_id, $type_out, 'job_type', true );	

	
			$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode( $location ).'&sensor=false');
			$output= json_decode($geocode);
     
			$lat = $output->results[0]->geometry->location->lat;
			$long = $output->results[0]->geometry->location->lng;
			update_post_meta( $post_id, 'geo_address', $location );
			update_post_meta( $post_id, 'geo_country', $location );
			update_post_meta( $post_id, 'geo_short_address', $location );
			update_post_meta( $post_id, '_jr_geo_longitude', $long );
			update_post_meta( $post_id, '_jr_geo_latitude', $lat );
			
			
			################################

			update_post_meta( $post_id, '_how_to_apply', $how );
			update_post_meta( $post_id, '_CompanyURL', $website );
			update_post_meta( $post_id, '_Company', $company );
			
			update_post_meta( $post_id, '_Company', $company );
			update_post_meta( $post_id, '_Company', $company );

			

		}
		
		
	
    
}
  
  
    
}

  ?>
 <div id="message" class="updated" > <?php echo $big_cnt; ?> jobs are successfully uploaded, <a href="<?php echo get_option('home'); ?>/wp-admin/edit.php?post_status=draft&post_type=job_listing">morderate the uploaded jobs</a></div> 
  
  <?php else:  ?>
  
  <?php //exit; ?>
  
  <?php endif; ?> 
  
  <div class="container">  
<div class="col_container">
	<div class="col_single">
		<div class="inner_block">
		<div class="new_title">Upload XLS template file.</div>
		<form method="post" action="" enctype="multipart/form-data" >
		<?php wp_nonce_field();  
		?>  
		
		<input type="file" name="scv_list" /><br/><br/>
		<input type="hidden" value="1" name="posted" />
		<input type="Submit" value="Submit" class="button-secondary" />
		</form>
	  </div>
	  </div>
</div>
<div class="col_container">
  <div class="col_single">
	<div class="inner_block">
		<div class="new_title">Download template</div>
		<div class="">
			<a href="<?php echo plugins_url('/jobs-upload-template-v1.xls', __FILE__ ); ?>"><img src="<?php echo plugins_url('/images/logo_4.png', __FILE__ ); ?>" class=" " /></a>
			
			<div class="clearfix"></div>
		</div>
	</div>
  </div>
</div>
<div class="col_container">
	<div class="col_single">
		<div class="inner_block third_col">
			<div class="new_title">Axiaer Solutions</div>
			<img src="<?php echo plugins_url('/images/logo_1.png', __FILE__ ); ?>" class="" />
			<br/>
			<div >
			Plugin Developed by <br/><a href="http://www.axiaer.com">Axiaer Solutions</a> and <a href="http://www.mbitsol.com">MbitSol</a>		
			</div>
			<br/>
			<div>
			<a href="https://www.facebook.com/axiaer"><img src="<?php echo plugins_url('/images/logo_2.png', __FILE__ ); ?>" class="fixed_image" /></a>
			</div>
			<br/>
			<div>
			<a href="https://twitter.com/axiaer"><img src="<?php echo plugins_url('/images/logo_3.png', __FILE__ ); ?>" class="fixed_image" /></a>
			</div>
	  </div>
	  </div>
 </div>
  <div class="clearfix"></div>
  

  
  </div>
  
  

  
</div>


<?php 
}
?>