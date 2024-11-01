<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require plugin_dir_path( __FILE__ ) .'/Classes/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImueUsers{

	public $numberOfRows=1;
	public $posts_per_page='';
	public $role='';
	public $offset='';
	
	public function importUsersDisplay(){?>
		<h2>
		<?php __("IMPORT / UPDATE Users","imue");?>
		</h2>	
			
		<p>
			<?php _e("Download the sample excel file, save it and add your Users. Upload it using the form below.","imue");?> 
			<a href='<?php echo plugins_url( '/example_excel/import-users.xlsx', __FILE__ ); ?>'>
				<?php _e("Users Excel Sample","imue");?>
			</a>		
		</p>
								  
		<div>			
			<form method="post" id='user_import' enctype="multipart/form-data" action= "<?php echo admin_url( 'admin.php?page=imue&tab=main' ); ?>">

				<table class="form-table">
						<tr valign="top">
							<td>
								<?php wp_nonce_field('excel_upload'); ?>
								<input type="hidden"   name="importUsers" value="1" />
								<div class="uploader">
									<img src="" class='userSelected'/>
									<input type="file"  required name="file" class="imueFile"  accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
								</div>						
							</td>
						</tr>
				</table>
				<?php submit_button( __( 'Upload', 'imue' ) ,'primary','upload'); ?>
			</form>	
			<div class='result'>
				<?php  $this->importUsers(); ?>
			</div>					
		</div>
	<?php
	}

	public function exportUsersDisplay(){?>
		<h2>
			<?php _e( 'EXPORT Users', 'imue' ) ?>
		</h2>
		<p>
			<i><?php _e( 'Important Note: always save the generated export file in xlsx format to a new excel for import use.', 'imue' ) ?></i>
		</p>
	   <div>	
			<?php  print "<div class='result'>". $this->exportUsersForm()."</div>"; ?>
	   </div>
	   <?php
	}
	

	public function imue_meta_keys(){
		$meta_keys =  array_keys( get_user_meta( get_current_user_id() ) );
		set_transient('meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
		if(!empty($meta_keys)){		
			return 	$meta_keys;
		}
	}	

	public function importUsers(){
		
		if($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('administrator')  && isset($_POST['importUsers']) ){
		
			check_admin_referer( 'excel_upload' );
			check_ajax_referer( 'excel_upload' );	
			
			$filename=$_FILES["file"]["tmp_name"];
							
			if($_FILES["file"]["size"] > 0 ){
				if($_FILES["file"]["type"] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
					
					try {
						$objPHPExcel = IOFactory::load($filename);
					} catch(Exception $e) {
						die('Error loading file "'.pathinfo($filename,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					
					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$data = count($allDataInSheet);  // Here get total count of row in that Excel sheet
					$total =  $data;
					$totals =  $total-1;

					 $rownumber=1;
					$row = $objPHPExcel->getActiveSheet()->getRowIterator($rownumber)->current();
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
										
					$titleArray = array();	
					
					?>
					<span class='thisNum'></span>
					<div class='ajaxResponse'></div>
						
					<div class='woo-form-wrapper'>
						<form method='POST' id ='user_process' action= "<?php print admin_url( 'admin.php?page=imue' );?>">

							<p>
								<?php _e( 'DATA MAPPING: Drag and drop excel columns on the right to duct perties on the left.', 'imue' ); ?>
							</p>
							
							<div class='columns3 border'>	

								<p class=''>
									<input type='checkbox' readonly name='send_email' class='premium' id='send_email' value=''  /> 
									<b class='premium'> <?php esc_html_e( 'Send Email to newly created Users - PRO VERSION', 'imue' ) ?> </b>
								</p>
								<p class=''>
									<input type='checkbox' name='update_users' id='update_users' value='yes'  /> <b> <?php esc_html_e( 'Update Existing Users', 'imue' ) ?> </b>
								</p>							
								<h2>
									<?php _e( 'EXCEL COLUMNS', 'imue' ) ?>
								</h2>
								
								<p>
									<?php foreach ($cellIterator as $cell) {
										echo "<input type='button' class='draggable' style='min-width:100px;background:#000;color:#fff' key ='".sanitize_text_field($cell->getColumn())."' value='". sanitize_text_field($cell->getValue()) ."' />  <br/>";
									} ?>				
								</p>
								
		   
							   <input type='hidden' name='finalupload' value='<?php print $total;?>' />
							   <input type='hidden' name='start' value='2' />
							   <input type='hidden' name='action' value='imue_process' />
							   <?php 
									wp_nonce_field('excel_process','secNonce');
									submit_button(__( 'Upload', 'imue' ),'primary','check'); 
								?>								
								
							</div>
							
							<?php
								$dontUse = array('syntax_highlighting','rich_editing','comment_shortcuts','admin_color','wp_user_level','dismissed_wp_pointers','show_welcome_panel','wp_dashboard_quick_press_last_post_id','session_tokens','use_ssl','show_admin_bar_front','_woocommerce_persistent_cart_1','closedpostboxes_dashboard','dismissed_store_notice_setting_moved_notice','dismissed_no_secure_connection_notice','jetpack_tracks_anon_id','last_update','_woocommerce_tracks_anon_id','tgmpa_dismissed_notice_tgm_foody_','dismissed_wc_admin_notice','paying_customer','erp_hr_disable_notification','erp_hr_disable_notification','wp_user-settings-time','wp_user-settings','wc_last_active','metaboxhidden_dashboard','wp_capabilities','locale','first_name','last_name','user_login','nickname','user_url','company','description');
							?>
							<div class='columns2'>
							
								<h2>
									<?php _e( 'USER FIELDS', 'imue' ) ?>
								</h2>
								
								<p class=''>
									<b><?php _e( 'EMAIL ', 'imue' ) ?></b><input type='text'  name='user_email' required readonly class='droppable' placeholder='email'  />
								</p>
								<p class=''>
									<b><?php _e( 'USERNAME ', 'imue' ) ?></b><input type='text'  name='user_login' required readonly class='droppable' placeholder='username'  />
								</p>
								<p class=''>
									<b><?php _e( 'NICKNAME ', 'imue' ) ?></b><input type='text'  name='nickname' required readonly class='droppable' placeholder='nickame'  />
								</p>	
								<p class=''>
									<b><?php _e( 'DISPLAY NAME ', 'imue' ) ?></b><input type='text'  name='display_name' required readonly class='droppable' placeholder='display_name'  />
								</p>								
								<p class=''>
									<b><?php _e( 'FIRST NAME ', 'imue' ) ?></b><input type='text'  name='first_name' required readonly class='droppable' placeholder='first_name'  />
								</p>	
								<p class=''>
									<b><?php _e( 'LAST NAME ', 'imue' ) ?></b><input type='text'  name='last_name' required readonly class='droppable' placeholder='last_name'  />
								</p>								
								<p class=''>
									<b><?php _e( 'PASSWORD ', 'imue' ) ?></b><input type='text'  name='user_pass' required readonly class='droppable' placeholder='password'  />
								</p>	
								<p class=''>
									<b><?php _e( 'URL - WEBSITE ', 'imue' ) ?></b><input type='text'  name='user_url' required readonly class='droppable' placeholder='url'  />
								</p>
								<p class=''>
									<b><?php _e( 'DESCRIPTION', 'imue' ) ?></b><input type='text'  name='description' required readonly class='droppable' placeholder='description'  />
								</p>								
								<p class=''>
									<b><?php _e( 'ROLE ', 'imue' ) ?></b><input type='text'  name='role' required readonly class='droppable' placeholder='role'  />
								</p>								
								<?php 
								if(!empty($this->imue_meta_keys())){
									foreach($this->imue_meta_keys() as $meta){
										if(!in_array($meta,$dontUse) ){
										echo "<p class='premium'>
												<b>".strtoupper(str_replace('_',' ',esc_attr($meta))) . __( ' - PRO Version', 'imue' ) ."</b>
										</p>";
										}
									}
								}
								?>


								
							</div>	
							
							
			
						</form>
					</div>
					
					<?php move_uploaded_file($_FILES["file"]["tmp_name"], plugin_dir_path( __FILE__ ).'import.xlsx');
				
				}else   "<h3>". _e('Invalid File:Please Upload Excel File', 'imue')."</h3>";				
				
			}
		}
	
	}

	
	public function exportUsersForm(){
		?>
				<p class='exportToggler button button-secondary warning   btn btn-danger'><i class='fa fa-eye '></i> 
					<?php _e('Filter & Fields to Show', 'imue');?>
				</p>
				
				<form name='exportUsersForm' id='exportUsersForm' method='post' action= "<?php echo admin_url( 'admin.php?page=imue&tab=exportUsers'); ?>" >	
					<table class='wp-list-table widefat fixed table table-bordered'>

					<?php global $wp_roles; ?>
					<tr>
							<td>
							<?php _e('User Role - PRO VERSION', 'imue');?>
							</td>					
							<td>
								<select name="role">
								<?php foreach ( $wp_roles->roles as $key=>$value ): ?>
								<option  <?php if($key=='administrator' || $key=='editor' || $key=='author' || $key=='contributor' || $key=='subscriber'){?> 
									value="<?php echo $key; ?>"
								<?php }else{ ?>
									class='premium' readonly value=''
								<?php } ?>
									><?php echo esc_html($value['name']); ?></option>
								<?php endforeach; ?>
								</select>
							</td>
							
							<td class='premium'>
							<?php _e('From Creation Date - PRO VERSION', 'imue');?>
							</td>	
							<td>
							<input type='date' style='width:100%;'  class='premium' readonly name='fromDate' id='fromDate' placeholder='<?php _e('registration date', 'imue');?>' />
							</td>							
					</tr>
						<tr>
							<td>
							<?php _e('Limit Results', 'imue');?>
							</td>
							<td>
								<input type='number' min="1" max="100000" style='width:100%;'   name='posts_per_page' id='posts_per_page' placeholder='<?php _e('Number to display..', 'imue');?>' />
							</td>
							
							<td class='premium'>
							<?php _e('To Creation Date - PRO VERSION', 'imue');?>
							</td>	
							<td>
							<input type='date' style='width:100%;'  name='toDate' id='toDate' class='premium'  readonly placeholder='<?php _e('registration date', 'imue');?>' />
							</td>							
							
							<input type='hidden' name='offset' style='width:100%;' id='offset' placeholder='<?php _e('Start from..', 'imue');?>' />
							<input type='hidden' name='start' /><input type='hidden' name='total' />
							
							<td></td><td></td>
						</tr>
						
					</table>

					<table class='wp-list-table widefat fixed table table-bordered tax_checks'>
						<legend>
							<h2>
								<?php _e('FIELDS TO SHOW', 'imue');?> - <span class='premium'><?php _e('More in PRO Version', 'imue');?></span>
							</h2>
						</legend>
					
						<tr>
							<?php $cols = array();
							
							$checked = 'checked';
							
							$cols = array('first_name','last_name',"nickname",'user_pass','user_login','user_url','user_email','role','description','company');
							
							print "<tr>";
							$checked = 'checked';
							foreach( $cols as $col){					
								print "<td style='float:left'><input type='checkbox' class='fieldsToShow' checked name='toShow".$col."' value='1'/><label for='".$col."'>". $col. "</label></td>";
							}
							?>
							</tr>
							<tr>
								<td>
									<span class='premium'><?php _e('More in PRO Version', 'imue');?></span>
								</td>
							</tr>
							<tr>
							<?php
							if(!empty($this->imue_meta_keys())){

								$dontUse = array('syntax_highlighting','rich_editing','comment_shortcuts','admin_color','wp_user_level','dismissed_wp_pointers','show_welcome_panel','wp_dashboard_quick_press_last_post_id','session_tokens','use_ssl','show_admin_bar_front','_woocommerce_persistent_cart_1','closedpostboxes_dashboard','dismissed_store_notice_setting_moved_notice','dismissed_no_secure_connection_notice','jetpack_tracks_anon_id','last_update','_woocommerce_tracks_anon_id','tgmpa_dismissed_notice_tgm_foody_','dismissed_wc_admin_notice','paying_customer','erp_hr_disable_notification','erp_hr_disable_notification','wp_user-settings-time','wp_user-settings','wc_last_active','metaboxhidden_dashboard','wp_capabilities','locale','first_name','last_name',"nickname",'user_pass','user_login','user_url','user_email','role','desciption','company');							
								foreach($this->imue_meta_keys() as $meta){
									if(!in_array($meta , $dontUse) ){
										
											echo "<td style='float:left'>
												<input type='checkbox' class='fieldsToShow premium ' readonly name='toShow".$meta."' value='1'/>
												<label for='".$meta."'>". $meta. "</label>
												</td>";										
									}

								}
							}?>
						</tr>
					</table>
		
							
					<input type='hidden' name='columnsToShow' value='1'  />
					<input type='hidden' id='action' name='action' value='imue_exportUsers' />
					<?php wp_nonce_field('columnsToShow'); ?>

					<?php submit_button(__( 'Search', 'imue' ),'primary','Search'); ?>

				</form>
			
			<div class='resultExport'>
				<?php $this->exportUsers(); ?>
			</div>
		<?php			
	}	
	
	public function exportUsers(){

		if($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('administrator') && $_REQUEST['columnsToShow'] ){
			
			check_admin_referer( 'columnsToShow' );
			check_ajax_referer( 'columnsToShow' );	

			if(!empty($_POST['role'])){
				$this->role = sanitize_text_field($_POST['role']);
			}else $this->role='';

			$args = array(
				'role' => $this->role,
			);				
			
			
			// Custom query.
			$my_user_query = new WP_User_Query( $args );
			 
			// Get query results.
			$users = $my_user_query->get_results();
			 
			// Check for users
			if ( ! empty( $users ) ) {	

				$i=0;
				?>
				<p class='message error'>
					<?php esc_html_e( 'Wait... Download is loading...', 'imue' );?>
					<b class='totalPosts'  >
						<?php print esc_html(count($users) );?>
					</b>					
				</p>

				<?php		
				if(count($users) <= 500){
					$start=0;
				}else $start=500;
				print " <b class='startPosts'>".esc_html($start)."</b>";

				$column_name =  array('first_name','last_name',"nickname",'user_pass','user_login','user_url','user_email','role','description','company');
				
				?>
				
				<div id="mygress">
					 <div id="myBar"></div>
				</div>
				<div class='exportTableWrapper'>
					<table id='toExport'>
						<thead>
							<tr> 
								<th>
									<?php esc_html_e('ID', 'imue');?>
								</th>
								<?php
									foreach($column_name as $d){
										if(isset($_REQUEST["toShow".strtolower(str_replace(" ","_",$d))] ) ){
											$d = strtoupper(str_replace("_"," ",$d));
											print "<th>".esc_html($d)."</th>";										
										}
									}								
								
								?>
							</tr>
						</thead>
						<tbody class='tableExportAjax'>
						</tbody>	
					</table>
				</div>	
			
			<?php	
			}else{?>
				<p class='message error'>
					<?php esc_html_e( 'No Users found.', 'imue' );?>
				</p>
					<?php			
			}
		}//check request						
	}	
}


function imue_meta_keys(){
		$meta_keys =  array_keys( get_user_meta( get_current_user_id() ) );

		set_transient('meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
		if(!empty($meta_keys)){
			return 	$meta_keys;
		}
}	

function imue_process(){
	
	if(isset($_POST['finalupload']) && current_user_can('administrator')){
		
		$time_start = microtime(true);

		check_admin_referer( 'excel_process','secNonce' );
		check_ajax_referer( 'excel_process' ,'secNonce');
				
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		
		
		$filename = plugin_dir_path( __FILE__ ).'import.xlsx';
			
		$objPHPExcel = IOFactory::load($filename);
		$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$data = count($allDataInSheet);  // Here get total count of row in that Excel sheet	

		
		//parameters for running with ajax - no php timeouts
		$i=sanitize_text_field($_POST['start']);
		$start = $i -1;

		//SANITIZE AND VALIDATE title and description	
		$email = sanitize_text_field($allDataInSheet[$i][$_POST['user_email']]);
		$display_name = sanitize_text_field($allDataInSheet[$i][$_POST['display_name']]);
		$first_name = sanitize_text_field($allDataInSheet[$i][$_POST['first_name']]);
		$last_name = sanitize_text_field($allDataInSheet[$i][$_POST['last_name']]);
		$nickname = sanitize_text_field($allDataInSheet[$i][$_POST['nickname']]);
		$username = sanitize_text_field($allDataInSheet[$i][$_POST['user_login']]);
		$roles = sanitize_text_field($allDataInSheet[$i][$_POST['role']]);
		$url = sanitize_text_field($allDataInSheet[$i][$_POST['user_url']]);
		
		
		if(!empty($allDataInSheet[$i][$_POST['user_pass']])){
			$password = sanitize_text_field($allDataInSheet[$i][$_POST['user_pass']]);
		}else $password = wp_generate_password( 12, false );
				
		
		$x=0;
		
			if( null == email_exists( $email ) && null == username_exists( $username ) ) {
				
				//if( !strstr($roles,"customer")){
					
				  $id = wp_create_user( $username, $password, $email );
				  //rest fields
				  wp_update_user(
					array(
					  'ID'          =>    $id,
					  'first_name'	=> $first_name,
					  'last_name'	=> $last_name,
					  'user_nicename'    =>    $nickname,
					  'display_name'	=> $display_name,
					  'user_url'	=> $url,
					  'description'	=> $description,
					)
				  );
				//role
					if(!empty($roles)  /* && !strstr($roles,"customer")*/ ){
						// Set the rol
						$user = new WP_User( $id );
						$roles = explode(",",$roles);
						
						foreach($roles as $role){
							$user->add_role( $role );
						}
						if(!strstr( sanitize_text_field($allDataInSheet[$i][$_POST['role']]) ,"subscriber")){
							$user->remove_role( "subscriber" );
						}	
						if(!strstr( sanitize_text_field($allDataInSheet[$i][$_POST['role']]) ,"erp_ac_manager")){
							$user->remove_role( "erp_ac_manager" );
						}							
					}
				print "<p class='success'>".$username . esc_html__( ' created', 'imue' ).".</p>";
				//}
				
			}else{

				if(!empty($_POST['update_users']) ){
					
					//if( !strstr($roles,"customer")){
						
					$user = get_user_by( 'email', $email );
					$id = $user->ID;
					
					if(!empty($roles)){
						// Set the rol
						$user = new WP_User( $id );
						$roles = explode(",",$roles);
						
						foreach($roles as $role){
							$user->add_role( $role );
						}
						if(!strstr( sanitize_text_field($allDataInSheet[$i][$_POST['role']]) ,"subscriber")){
							$user->remove_role( "subscriber" );
						}
						if(!strstr( sanitize_text_field($allDataInSheet[$i][$_POST['role']]) ,"erp_ac_manager")){
							$user->remove_role( "erp_ac_manager" );
						}						
					}
				  
					$user_meta = array('user_pass','user_login','user_url','display_name','first_name','last_name','nickname','description');
					foreach($user_meta  as $meta){
						if(!empty($allDataInSheet[$i][$_POST[$meta]])){
							update_user_meta($id, $meta, sanitize_text_field( $allDataInSheet[$i][$_POST[$meta]] ) );
						}			
					}
					  
					print "<p class='warning'>".esc_html__( 'User with ', 'imue' ) . $email . esc_html__( ' already exists. Updated', 'imue' ).".</p>";
					
					//}
				}else 	print "<p class='warning'>".esc_html__( 'User with ', 'imue' ) . $email . esc_html__( ' already exists. Wont update as chosen.', 'imue' ).".</p>";
				
			}
		
				
		if($i === $_REQUEST['finalupload']){						
			$tota = $_REQUEST['finalupload']-1;
			print "<div class='importMessageSussess'><h2>".esc_html($i)." / ".esc_html($_REQUEST['finalupload'])." ".esc_html__('- JOB DONE!', 'imue'). " <a href='".esc_url(admin_url( 'users.php' ))."' target='_blank'><i class='fa fa-eye'></i> ".esc_html__('View Users', 'imue')."</a></h2></div>";
				
			unlink($filename);
		}else{
			
			print "<div class='importMessage'>
						<h2>".esc_html($i)." / ".esc_html($_REQUEST['finalupload'])." ".esc_html__('Please dont close this page... Loading...', 'imue')."</h2>
							<p>
								<img  src='".esc_url(plugins_url( 'images/loading.gif', __FILE__ ))."' />
							</p>
					</div>";
		}
		die;										
	}
}

function imue_exportUsers(){

	if($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('administrator') ){
				
		check_admin_referer( 'columnsToShow' );
		check_ajax_referer( 'columnsToShow' );
		
			if(!empty($_POST['role'])){
				$role = sanitize_text_field($_POST['role']);
			}else $role='';
			
			$args = array(
				'role' => $role,
			);				
		 
		// Custom query.
		$my_user_query = new WP_User_Query( $args );
		 
		// Get query results.
		$users = $my_user_query->get_results();
		 
		// Check for users
		if ( ! empty( $users ) ) {	
			
			$cols = array('first_name','last_name',"nickname",'user_pass','user_login','user_url','user_email','role','description','company');
			 foreach ( $users as $user ) {				 
				 $user = get_userdata( $user->ID );
				 $user_roles = $user->roles;
				 ?>						
					<tr>
						<td><?php print esc_attr($user->ID) ;?></td>					
						<?php foreach($cols as $meta){
							if(isset($_REQUEST["toShow".$meta]) ){
									if($meta =='role'){?>
										<td><?php print esc_html(implode(',',$user->roles) ); ?></td>
									<?php }
									?>
									<td><?php print esc_html($user->$meta); ?></td>									
							<?php }
						}										
					print "</tr>";		

			}//end while
			die;												
		}else print "<p class='warning' >".esc_html__('No Users Found', 'imue')."</p>";//end if						
	}//check request						
}
?>