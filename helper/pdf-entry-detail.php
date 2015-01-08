<?php
if(!class_exists('GFPDFEntryDetail'))
{

	add_filter('gform_field_content', array('GFPDFEntryDetail', 'encode_tags'), 10, 2); /* encode shortcodes in user's response so they aren't converted later by do_shortcode */
	class GFPDFEntryDetail {

		/* NEED THIS FUNCTION - BLD */
		public static function lead_detail_grid($form, $lead, $allow_display_empty_fields=false, $show_html=false, $show_page_name=false, $return=false, $show_section_breaks=false){
			$form_id = $form['id'];
			$results = array();

			if($return === true)
			{
				$results['title'] = '<h2 id="details" class="default">'. $form['title'] .'</h2>';
			}
			else
			{
				?>
			<div id='container'>
				<h2 id='details' class='default'><?php echo $form['title']?></h2>
                <?php
			}


					$count = 0;
					$field_count = sizeof($form['fields']);

					$has_product_fields = false;

					$page_number = 0;
					foreach($form['fields'] as $field) {

						/*
						 * Check if this field has been excluded from the list
						 */
						if(isset($field['cssClass']) && strpos($field['cssClass'], 'exclude') !== false)
						{
							/* skip this field */
							continue;
						}

						/*
						 * Check if we are to show the page names
						 */
						 if($show_page_name === true)
						 {
							if((int) $field['pageNumber'] !== $page_number)
							{
								/*
								 * Display the page number
								 */
								 if($return === true)
								 {
									$results['field'][] = '<h2 id="field-'. $field['id'].'" class="default entry-view-page-break">'. $form['pagination']['pages'][$page_number] .'</h2>';
								 }
								 else
								 {
								?>
                                	<h2 id='field-<?php echo $field['id']; ?>' class='default entry-view-page-break'><?php echo $form['pagination']['pages'][$page_number]; ?></h2>
                                <?php
								 }
								/*
								 * Increment the page number
								 */
								$page_number++;
							}
						 }

						$even = $odd = '';

						switch(RGFormsModel::get_input_type($field)){
						   case 'section' :

								if(!GFCommon::is_section_empty($field, $form, $lead) || $allow_display_empty_fields){
									$count++;

									if($return === true)
									{
										$results['field'][] = '<h2 id="field-'.$field['id'].'" class="default entry-view-section-break">'. esc_html(GFCommon::get_label($field)) .'</h2>';
									}
									else
									{
									?>
										<h2 id="field-<?php echo $field['id']; ?>" class="default entry-view-section-break"><?php echo esc_html(GFCommon::get_label($field))?></h2>
									<?php
									}
								}
							break;

							case 'captcha':
							case 'password':
							case 'page':
								//ignore captcha, html, password, page field
							break;
							case 'html':
								if($show_html == true)
								{

									$count++;
									$is_last = $count >= $field_count && !$has_product_fields ? true : false;
									$last_row = $is_last ? ' lastrow' : '';
									$even = ($count%2) ? ' odd' : ' even';

									$display_value = $field['content'];

									$content = '<div id="field-'. $field['id'] .'" class="entry-view-html-value' . $last_row . $even . '"><div class="value">' . $display_value . '</div></div>';
									$content = apply_filters('gform_field_content', $content, $field, $value, $lead['id'], $form['id']);

									if($return === true)
									{
										$results['field'][] = $content;
									}
									else
									{
										echo $content;
									}
								}
							break;
							case 'signature':
								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$public_folder = RGFormsModel::get_upload_url_root() . 'signatures/';
								$server_folder = RGFormsModel::get_upload_root() . 'signatures/';
								$display_value = '<img src="'. $server_folder.$value .'" alt="Signature" width="100" height="60" />';
								$is_last = $count >= $field_count ? true : false;
								$last_row = $is_last ? ' lastrow' : '';

								if(strlen($value) > 0 && (file_exists($server_folder.$value)) && (is_dir($server_folder.$value) !== true) )
								{
									 $content = '<div id="field-'. $field['id'] .'" class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div> <div class="value">' . $display_value . '</div></div>	';

									if($return === true)
									{
										$results['field'][] = $content;
									}
									else
									{
										echo $content;
									}
								}
								elseif($allow_display_empty_fields)
								{
									if($return === true)
									{
										$results['field'][] = '<div id="field-'. $field['id'] .'" class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div></div>';
									}
									else
									{
										print '<div id="field-'. $field['id'] .'" class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div></div>';
									}

								}
							break;

							default:

								//ignore product fields as they will be grouped together at the end of the grid
								if(GFCommon::is_product_field($field['type'])){
									$has_product_fields = true;
									continue;
								}

								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$display_value = self::pdf_get_lead_field_display($field, $value, $lead['currency']);


								$display_value = apply_filters('gform_entry_field_value', $display_value, $field, $lead, $form);

								if( !empty($display_value) || $display_value === '0' || $allow_display_empty_fields){
									$count++;
									$is_last = $count >= $field_count && !$has_product_fields ? true : false;
									$last_row = $is_last ? ' lastrow' : '';
									$even = ($count%2) ? ' odd' : ' even';

									$display_value =  empty($display_value) && $display_value !== '0' ? '&nbsp;' : $display_value;

									$content = '<div id="field-'. $field['id'] .'" class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div> <div class="value">' . $display_value . '</div></div>';

									$content = apply_filters('gform_field_content', $content, $field, $value, $lead['id'], $form['id']);

									if($return === true)
									{
										$results['field'][] = $content;
									}
									else
									{
										echo $content;
									}

								}
							break;
						}

					}
					$products = array();
					if($has_product_fields){
						if($return === true)
						{
							ob_start();
							self::product_table($form, $lead);
							$results['field'][] = ob_get_contents();
							ob_end_clean();
						}
						else
						{
						   self::product_table($form, $lead);
						}

					}

					if($return === true)
					{
						return $results;
					}
					?>
				</div>
			<?php
		}

		public static function product_table($form, $lead)
		{
			$products = GFCommon::get_product_fields($form, $lead, true);

			$form_id = $form['id'];
						if(!empty($products['products'])){
							?>

								<h2 class='default entry-view-field-name'><?php echo apply_filters("gform_order_label_{$form['id']}", apply_filters('gform_order_label', __('Order', 'gravityforms'), $form['id']), $form['id']) ?></h2>

									<table class='entry-products' autosize='1' cellspacing='0' width='97%'>
									  <colgroup>
											  <col class='entry-products-col1' />
											  <col class='entry-products-col2' />
											  <col class='entry-products-col3' />
											  <col class='entry-products-col4' />
										</colgroup>
										<thead>
										  <tr>
											<th scope='col'><?php echo apply_filters('gform_product_{$form_id}', apply_filters('gform_product', __('Product', 'gravityforms'), $form_id), $form_id) ?></th>
											<th scope='col' class='textcenter'><?php echo apply_filters('gform_product_qty_{$form_id}', apply_filters('gform_product_qty', __('Qty', 'gravityforms'), $form_id), $form_id) ?></th>
											<th scope='col'><?php echo apply_filters('gform_product_unitprice_{$form_id}', apply_filters('gform_product_unitprice', __('Unit Price', 'gravityforms'), $form_id), $form_id) ?></th>
											<th scope='col'><?php echo apply_filters('gform_product_price_{$form_id}', apply_filters('gform_product_price', __('Price', 'gravityforms'), $form_id), $form_id) ?></th>
										  </tr>
										</thead>
										<tbody>
										<?php

											$total = 0;
											foreach($products['products'] as $product){
												?>
												<tr>
													<td>
														<div class='product_name'><?php echo esc_html($product['name'])?></div>

															<?php
															$price = GFCommon::to_number($product['price']);
															if(is_array(rgar($product,'options'))){
																echo '<ul class="product_options">';
																$count = sizeof($product['options']);
																$index = 1;
																foreach($product['options'] as $option){
																	$price += GFCommon::to_number($option['price']);
																	$class = $index == $count ? ' class="lastitem"' : '';
																	$index++;
																	?>
																	<li<?php echo $class?>><?php echo $option['option_label']?></li>
																	<?php
																}
																echo '</ul>';
															}
															$subtotal = floatval($product['quantity']) * $price;
															$total += $subtotal;
															?>

													</td>
													<td class='textcenter'><?php echo $product['quantity'] ?></td>
													<td><?php echo GFCommon::to_money($price, $lead['currency']) ?></td>
													<td><?php echo GFCommon::to_money($subtotal, $lead['currency']) ?></td>
												</tr>
												<?php
											}
											$total += floatval($products['shipping']['price']);
										?>


											<?php
											if(!empty($products['shipping']['name'])){
											?>
												<tr>
													<td colspan='2' rowspan='2' class='emptycell'>&nbsp;</td>
													<td class='textright shipping'><?php echo $products['shipping']['name'] ?></td>
													<td class='shipping_amount'><?php echo GFCommon::to_money($products['shipping']['price'], $lead['currency'])?>&nbsp;</td>
												</tr>
											<?php
											}
											?>
											<tr>
												<?php
												if(empty($products['shipping']['name'])){
												?>
													<td colspan='2' class='emptycell'>&nbsp;</td>
												<?php
												}
												?>
												<td class='textright grandtotal'><?php _e('Total', 'gravityforms') ?></td>
												<td class='grandtotal_amount'><?php echo GFCommon::to_money($total, $lead['currency'])?></td>
											</tr>
                                            </tbody>

									</table>

			<?php
			}
		}

		public static function format_date($date, $usa = false)
		{
			$timestamp = strtotime($date);
			$new_date = (!$usa) ? date('j/n/Y', $timestamp) : date('n/j/Y', $timestamp);
			return $new_date;
		}


		/* returns the form values as an array instead of pre-formated html */
		public static function lead_detail_grid_array($form, $lead, $allow_display_empty_fields=false){

			$form_id = $form['id'];
			$form_array = self::set_form_array_common($form, $lead, $form_id);

			$has_product_fields = false;

					foreach($form['fields'] as $field) {

						switch(RGFormsModel::get_input_type($field)){
							case 'section' :
							case 'html':
								$form_array = self::get_html($field, $form_array);
							break;

							case 'captcha':
							case 'password':
							case 'page':
								//ignore captcha, password and page
							break;
							case 'signature':
								$form_array = self::get_signature($form, $lead, $field, $form_array);

							break;
							case 'fileupload':
								$form_array = self::get_fileupload($form, $lead, $field, $form_array);
							break;
							case 'list':
								/*
								 * We want list to run both this and the deafult so don't call break.
								 * Get the list array and store it outside of [field] in a new key called ['list']
								 */
								 $form_array = self::get_default_list($lead, $field, $form_array);

							case 'select':
							case 'multiselect':
							case 'radio':
								if($field['type'] == 'quiz')
								{
									$form_array = self::get_quiz_radios($form, $lead, $field, $form_array);									
								}
								else
								{
								 	/* store values in regular [field] array */
								 	$form_array = self::get_the_list($lead, $field, $form_array);
								}
							break;

							case 'likert':
								$form_array['survey']['likert'][$field['id']] = self::get_the_likert($form, $lead, $field, $form_array);
							break;

							case 'rank':								
								$form_array['survey']['rank'][$field['id']] = self::get_the_rank($form, $lead, $field, $form_array);
							break;

							default:
								//ignore product fields as they will be grouped together at the end of the grid
								if(GFCommon::is_product_field($field['type'])){
									$has_product_fields = true;
									continue;
								}

								$form_array = self::get_defaults($lead, $field, $form_array);

							break;
						}

					}

					$form_array = self::get_product_array($form, $lead, $has_product_fields, $form_array);

			return $form_array;
		}

		private static function get_quiz_radios($form, $lead, $field, $form_array)
		{
			$id = $field['id'];
			$results = $lead[$id];
			$return = array();

			foreach($field['choices'] as $choice)
			{
				if(trim($choice['value']) == trim($results))
				{
					$return[] = array('text' => $choice['text'], 'isCorrect' => $choice['gquizIsCorrect']);
					break;
				}
			}

			/* add data to field tag correctly */
			$form_array['field'][$field['id'].'.'.$field['label'].'_name'] = $return;

			/* add ID incase want to use template on multiple duplicate forms with different field names */
			$form_array['field'][$field['id']] = $return;

			/* keep backwards compatibility */
			$form_array['field'][$field['label']] = $return;

			return $form_array;
	
		}		

		private static function get_the_rank($form, $lead, $field, $form_array)
		{
			$id = $field['id'];
			$results = explode(',', $lead[$id]);
			$return = array();

			foreach($results as $rank)
			{
				foreach($field['choices'] as $choice)
				{
					if(trim($choice['value']) == trim($rank))
					{
						$return[] = $choice['text'];
						break;
					}
				}
			}

			return $return;			
		}

		private static function get_the_likert($form, $lead, $field, $form_array)
		{
			$id 		   = $field['id'];
			$results       = $lead[$id];
			$multiple_rows = rgar($field, "gsurveyLikertEnableMultipleRows") ? true : false;		
			$likert = array();

			/* store the column names */
			foreach($field['choices'] as $col)
			{
				$likert['col'][$col['value']] = $col['text'];
			}

			
			if(sizeof($field['inputs']) > 0)
			{
				/* do our multi-row likert */
				foreach($field['inputs'] as $row)
				{
					/* pad the array with the number of columns */

					foreach($likert['col'] as $col_id => $text)
					{		
						/* 
						 * Results for multi row is stored with ID.ROW# (eg $lead['1.2']) 
						 * The number is stored in $row['id']
						 */							
						$results = $lead[$row['id']];

						/* user data in the $lead comes in as ROW ID:COL ID */
						$comparison = $row['name'] . ':' . $col_id;

						/* do our comparison and update the output */
						$output = ($comparison == $results) ? 'selected' : '';										

						/* assign our results to the array */
						$likert['rows'][$row['label']][] = $output;

					}
				}
			}
			else
			{
				/* do our single row likert */
				foreach($likert['col'] as $col_id => $text)
				{
					/* single row data comes in with col value */
					/* do our comparison and update the output */
					$output = ($col_id == $results) ? 'selected' : '';										

					$likert['row'][] = $output;

				}									
			}

			return $likert;

		}

		private static function set_form_array_common($form, $lead, $form_id)
		{
			$form_array = array();

			/*
			 * Add form_id and lead_id
			 */
			$form_array['form_id'] = $form_id;
			$form_array['entry_id'] = $lead['id'];

			/*
			 * Set title and dates (both US and international)
			 */
			$form_array['form_title'] = $form['title'];
			$form_array['date_created'] = self::format_date($lead['date_created']);
			$form_array['date_created_usa'] = self::format_date($lead['date_created'], true);

			/*
			 * Include page names
			 */
			$form_array['pages'] = $form['pagination']['pages'];

			/*
			 * Add misc fields
			 */
			 $form_array['misc']['date_time'] = $lead['date_created'];
			 $form_array['misc']['time_24hr'] = date('H:i', strtotime($lead['date_created']));
			 $form_array['misc']['time_12hr'] = date('g:ia', strtotime($lead['date_created']));
			 $form_array['misc']['is_starred'] = $lead['is_starred'];
			 $form_array['misc']['is_read'] = $lead['is_read'];
			 $form_array['misc']['ip'] = $lead['ip'];
			 $form_array['misc']['source_url'] = $lead['source_url'];
			 $form_array['misc']['post_id'] = $lead['post_id'];
			 $form_array['misc']['currency'] = $lead['currency'];
			 $form_array['misc']['payment_status'] = $lead['payment_status'];
			 $form_array['misc']['payment_date'] = $lead['payment_date'];
			 $form_array['misc']['transaction_id'] = $lead['transaction_id'];
			 $form_array['misc']['payment_amount'] = $lead['payment_amount'];
			 $form_array['misc']['is_fulfilled'] = $lead['is_fulfilled'];
			 $form_array['misc']['created_by'] = $lead['created_by'];
			 $form_array['misc']['transaction_type'] = $lead['transaction_type'];
			 $form_array['misc']['user_agent'] = $lead['user_agent'];
			 $form_array['misc']['status'] = $lead['status'];

			/*
			 * Add quiz results
			 */
			$form_array = self::get_quiz_results($form, $form_array, $lead);
			$form_array = self::get_survey_results($form, $form_array, $lead);
			$form_array = self::get_poll_results($form, $form_array, $lead);

			return $form_array;
		}

		/* check if there is a poll field in the form */
		private static function is_poll($form)
		{					
			foreach($form['fields'] as $field)
			{
				if($field['type'] == 'poll')
				{
					return true;
				}
			}			
			return false;
		}

		private static function get_poll_results($form, $form_array, $lead)
		{
			if(self::is_poll($form))
			{
				$form_array['poll'] = self::get_addon_global_data($form, array());
			}

			return $form_array;
		}

		private static function get_survey_results($form, $form_array, $lead)
		{
			 /*
			  * If there are any survey results
			  * add them to the 'survey' key
			  */
			$fields            = GFCommon::get_fields_by_type($form, array('survey'));
	        $count_survey_fields = count($fields);

	        if ($count_survey_fields > 0)
	        {
	        	$form_array['survey']['score'] = $lead['gsurvey_score'];
	        }

	        return $form_array;

		}

		private static function get_quiz_results($form, $form_array, $lead)
		{

			 /*
			  * If there are any quiz results
			  * add them to the 'quiz' key
			  */
			$fields            = GFCommon::get_fields_by_type($form, array('quiz'));
	        $count_quiz_fields = count($fields);

	        if ($count_quiz_fields > 0)
	        {
				$form_array['quiz']['config']['grading']     = $form['gravityformsquiz']['grading'];
				$form_array['quiz']['config']['passPercent'] = $form['gravityformsquiz']['passPercent'];
				$form_array['quiz']['config']['grades']      = json_decode($form['gravityformsquiz']['grades']);
				
				$form_array['quiz']['results']['score']      = rgar($lead, 'gquiz_score');
				$form_array['quiz']['results']['percent']    = rgar($lead, 'gquiz_percent');
				$form_array['quiz']['results']['is_pass']    = rgar($lead, 'gquiz_is_pass');
				$form_array['quiz']['results']['grade']      = rgar($lead, 'gquiz_grade');

				/*
				 * Get the overall results
				 */
				$form_array['quiz']['global'] = self::get_quiz_overalls($form);
				
	        }

	        return $form_array;
		}

		private static function get_addon_global_data($form, $options)
		{
				/* if the results class isn't loaded, load it */
				if (!class_exists("GFResults"))
				{
				    require_once(GFCommon::get_base_path() . "/includes/addon/class-gf-results.php");
				}

				$fields = $form["fields"];
	            
	            /* initiat the results class */
				$gf_results = new GFResults('', $options);				
				
				/* ensure that only active leads are queried */
				$search = array(
					'field_filters' => array('mode' => ''),
					'status'        => 'active'
				);			

				/* get the quiz results */
				$data = $gf_results->get_results_data($form, $fields, $search);	

				/* unset some array keys we don't need */
				unset($data['status']);
				unset($data['timestamp']);

				return $data;

		}

		private static function get_quiz_overalls($form)
		{
			/* we need to tap into functions GF Quiz has so only run if it is active */
			if(class_exists('GFQuiz'))
			{
				/* GFQuiz is a singleton. Get the instance */
				$quiz   = GFQuiz::get_instance();

	            /* create our callback to add additional data to the array specific to the quiz plugin */
	            $options['callbacks']['calculation'] = array(
	            		$quiz, 
	            		'results_calculation'
	            );

				return self::get_addon_global_data($form, $options);		
			}

			return array(__('Activate Gravity Forms Quiz Add On to see global quiz statistics for this form', 'pdfextended'));
		}

		private static function get_html($field, $form_array)
		{
			if(isset($field['content']))
			{
				$form_array['html'][] = wpautop($field['content']);
			}

			return $form_array;
		}

		private static function get_signature($form, $lead, $field, $form_array)
		{
			$value = RGFormsModel::get_lead_field_value($lead, $field);
			$http_folder = RGFormsModel::get_upload_url_root(). 'signatures/';;
			$folder = RGFormsModel::get_upload_root() . 'signatures/';

			if(file_exists($folder.$value) !== false && is_dir($folder.$value) !== true)
			{
				$sig_html = '<img src="'. $folder.$value .'" alt="Signature" width="100" height="60" />';

				$form_array['signature'][] 			= $sig_html;
				$form_array['signature_details'][] 	= array(
																	'img'  => $sig_html,
																	'path' => $folder.$value,
																	'url'  => $http_folder.$value
												   			);

				$form_array['signature_details_id'][$field['id']] = array(
																			'img'  => $sig_html,
																			'path' => $folder.$value,
																			'url'  => $http_folder.$value
																		  );
			}

			return $form_array;
		}

		private static function get_fileupload($form, $lead, $field, $form_array)
		{
			$value = RGFormsModel::get_lead_field_value($lead, $field);
			$display = self::get_lead_field_display($field, $value, $lead['currency']);

			/*
			 * Get the absolute path to the upload
			 */
			 $path = str_replace(home_url().'/', ABSPATH, $display);

			 /* add path */
			 $form_array['field'][$field['id'].'_path'] = $path;
			 $form_array['field'][$field['id'].'.'.$field['label'].'_path'] = $path;

			 return self::assign_form($field, $display, $form_array);
		}

		private static function get_default_list($lead, $field, $form_array)
		{
			$value = self::remove_empty_list_rows(unserialize(RGFormsModel::get_lead_field_value($lead, $field)));
			$form_array['list'][$field['id']] = $value;

			return $form_array;
		}

		private static function remove_empty_list_rows($list)
		{
			/*
			 * Check if there are any values in the list 
			 */
			if(sizeof($list) == 0)
			{
				return $list;
			}


			/*
			 * Check if it's a multi column list
			 */
			if(!is_array($list[0]))
			{
				$list = array_filter($list);
			}
			else
			{
				$list = self::remove_empty_multi_col_list($list);
			}

			return $list;
		}

		private static function remove_empty_multi_col_list($gfield_list)
		{
			foreach($list as $id => $row)			
			{

				$empty = true;
				foreach($row as &$col)
				{
					/* check if there is data and if so break the loop */
					if(trim(strlen($col) > 0))
					{
						$empty = false;
						break;
					}
				}

				/* remove row from list */
				if($empty)
				{	
					unset($list[$id]);
				}				
			}	
			
			return $list;		
		}

		private static function get_the_list($lead, $field, $form_array)
		{
			$value = RGFormsModel::get_lead_field_value($lead, $field);
			$display = self::get_lead_field_display($field, $value, $lead['currency']);

			$form_array = self::assign_form($field, $display, $form_array);

			/*
			 * Include these items correct names
			 */
			$display = self::get_lead_field_display($field, $value, $lead['currency'], true);
			/* add data to field tag correctly */
			$form_array['field'][$field['id'].'.'.$field['label'].'_name'] = $display;

			/* add ID incase want to use template on multiple duplicate forms with different field names */
			$form_array['field'][$field['id'].'_name'] = $display;

			/* keep backwards compatibility */
			$form_array['field'][$field['label'].'_name'] = $display;

			return $form_array;
		}

		private static function get_defaults($lead, $field, $form_array)
		{
			$value = RGFormsModel::get_lead_field_value($lead, $field);
			$display = self::get_lead_field_display($field, $value, $lead['currency']);

			return self::assign_form($field, $display, $form_array);
		}

		private static function assign_form($field, $display, $form_array)
		{
				/* add data to field tag correctly */
				$form_array['field'][$field['id'].'.'.$field['label']] = $display;

				/* add ID incase want to use template on multiple duplicate forms with different field names */
				$form_array['field'][$field['id']] = $display;

				/* keep backwards compatibility */
				$form_array['field'][$field['label']] = $display;

				return $form_array;
		}

		private static function get_product_array($form, $lead, $has_product_fields, $form_array)
		{


			if($has_product_fields) {
				$products = GFCommon::get_product_fields($form, $lead, true);

				/* check that there are actual product fields */
				if(!empty($products['products'])){

					/*
					 * Set up our variables
					 */
					$products = array();
					$total = 0;
					$subtotal = 0;

					foreach($products['products'] as $product) {
						$price = GFCommon::to_number($product['price']);

						/* add all options to total price */
						if(is_array(rgar($product,'options')))
						{
							foreach($product['options'] as $option){
								$price += GFCommon::to_number($option['price']);
							}
						}

						/* calculate subtotal */
						$subtotal = floatval($product['quantity']) * $price;
						$total += $subtotal;

						/*
						 * Check if we should include options
						 */
						$options = isset($product['options']) ? $product['options'] : '';

						/*
						 * Store product in $form_array array
						 */
						$form_array['products'][] = array(
								'name' => esc_html($product['name']),
								'price' => esc_html($product['price']),
								'options' => $options,
								'quantity' => $product['quantity'],
								'subtotal' => $subtotal);
					}

					/* Increment total */
					$total += floatval($products['shipping']['price']);

					/* add totals to form data */
					$form_array['products_totals'] = array(
							'shipping' => $products['shipping']['price'],
							'total'	   => $total
					);
				}
			}

			return $form_array;
		}

		public static function get_lead_field_display($field, $value, $currency='', $use_text=false, $format='html', $media='screen'){

			if($field['type'] == 'post_category')
				$value = GFCommon::prepare_post_category_value($value, $field);

			switch(RGFormsModel::get_input_type($field)){
				case 'name' :
					if(is_array($value)){
						$prefix = trim(rgget($field['id'] . '.2', $value));
						$first  = trim(rgget($field['id'] . '.3', $value));
						$last   = trim(rgget($field['id'] . '.6', $value));
						$suffix = trim(rgget($field['id'] . '.8', $value));

						return array('prefix' => $prefix, 'first' => $first, 'last' => $last, 'suffix' => $suffix);
					}
					else{
						return $value;
					}

				break;
				case 'creditcard' :
					if(is_array($value)){
						$card_number = trim(rgget($field['id'] . '.1', $value));
						$card_type   = trim(rgget($field['id'] . '.4', $value));
						$separator   = $format == 'html' ? '<br/>' : '\n';
						return empty($card_number) ? '' : $card_type . $separator . $card_number;
					}
					else{
						return '';
					}
				break;

				case 'address' :
					if(is_array($value)){
						$street_value           = trim(rgget($field['id'] . '.1', $value));
						$street2_value          = trim(rgget($field['id'] . '.2', $value));
						$city_value             = trim(rgget($field['id'] . '.3', $value));
						$state_value            = trim(rgget($field['id'] . '.4', $value));
						$zip_value              = trim(rgget($field['id'] . '.5', $value));
						$country_value          = trim(rgget($field['id'] . '.6', $value));

						$line_break             = $format == 'html' ? '<br />' : '\n';

						$address_display_format = apply_filters('gform_address_display_format', 'default');

						$address['street']      = $street_value;
						$address['street2']     = $street2_value;
						$address['city']        =  $city_value;
						$address['state']       =  $state_value;
						$address['zip']         = $zip_value;
						$address['country']     = $country_value;

						return $address;
					}
					else{
						return '';
					}
				break;

				case 'email' :
					return GFCommon::is_valid_email($value) && $format == 'html' ? $value : $value;
				break;

				case 'website' :
					return GFCommon::is_valid_url($value) && $format == 'html' ? $value : $value;
				break;

				case 'checkbox' :
					if(is_array($value)){

						$items = array();

						foreach($value as $key => $item){
							if(!empty($item)){
								switch($format){
									case 'text' :
										$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
									break;

									default:
										$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
									break;
								}
							}
						}
						if(empty($items)){
							return '';
						}
						else if($format == 'text'){
							/*return substr($items, 0, strlen($items)-2); //removing last comma*/
						}
						else{
							return $items;
						}
					}
					else{
						return $value;
					}
				break;

				case 'post_image' :
					$ary         = explode('|:|', $value);
					$url         = count($ary) > 0 ? $ary[0] : '';
					$title       = count($ary) > 1 ? $ary[1] : '';
					$caption     = count($ary) > 2 ? $ary[2] : '';
					$description = count($ary) > 3 ? $ary[3] : '';

					if(!empty($url)){
						$url 	= str_replace(' ', '%20', $url);

						$value 	= array('url' => $url,
									   'path' => str_replace(site_url().'/', ABSPATH, $url),
									   'title' => $title,
									   'caption' => $caption,
									   'description' => $description);
					}
					return $value;

				case 'fileupload' :
						$output_arr = array();
						if(!empty($value)){
							$file_paths = rgar($field,'multipleFiles') ? json_decode($value) : array($value);
							foreach($file_paths as $file_path){
								$info         = pathinfo($file_path);
								$file_path    = esc_attr(str_replace(' ', '%20', $file_path));
								$output_arr[] = $file_path;
							}
							$output = join(PHP_EOL, $output_arr);
						  }
						return $output_arr;
				break;

				case 'date' :
					return GFCommon::date_display($value, rgar($field, 'dateFormat'));
				break;

				case 'radio' :
				case 'select' :
					return GFCommon::selection_display($value, $field, $currency, $use_text);
				break;

				case 'multiselect' :
					if(empty($value) || $format == 'text')
						return $value;

					$value = explode(',', $value);

					$items = '';
					foreach($value as $item){
						$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
					}

					if(sizeof($items) == 1)
					{
						return $items[0];
					}
					return $items;


				break;

				case 'calculation' :
				case 'singleproduct' :
					if(is_array($value)){
						$product_name = trim($value[$field['id'] . '.1']);
						$price        = trim($value[$field['id'] . '.2']);
						$quantity     = trim($value[$field['id'] . '.3']);

						$product      = $product_name . ', ' . __('Qty: ', 'gravityforms') . $quantity . ', ' . __('Price: ', 'gravityforms') . $price;

						return $product;
					}
					else{
						return '';
					}
				break;

				case 'number' :
					return self::format_number($value, rgar($field, 'numberFormat'));
				break;

				case 'singleshipping' :
				case 'donation' :
				case 'total' :
				case 'price' :
					return GFCommon::to_money($value, $currency);

				case 'list' :
					if(empty($value))
						return '';
					$value = unserialize($value);

					$has_columns = is_array($value[0]);

					if(!$has_columns){
						$items = array();
						foreach($value as $key => $item){
							if(!empty($item)){
								switch($format){
									case 'text' :
										$items[] = $item;
									break;
									case 'url' :
										$items[] = $item;
									break;
									default :
										if($media == 'email'){
											$items[] = $item;
										}
										else{
											$items[] = $item;
										}
									break;
								}
							}
						}

						if(empty($items)){
							return '';
						}
						else if($format == 'text'){
						   /* return substr($items, 0, strlen($items)-2); //removing last comma*/
							return $items;
						}
						else if($format == 'url'){
							/*return substr($items, 0, strlen($items)-1); //removing last comma*/
							return $items;
						}
						else if($media == 'email'){
							return $items;
						}
						else{
							return $items;
						}
					}
					else if(is_array($value)){
						$columns = array_keys($value[0]);

						$list = '';

						switch($format){
							case 'text' :
								$is_first_row = true;
								foreach($value as $item){
									if(!$is_first_row)
										$list .= '\n\n' . $field['label'] . ': ';
									$list .= implode(',', array_values($item));

									$is_first_row = false;
								}
							break;

							case 'url' :
								foreach($value as $item){
									$list .= implode('|', array_values($item)) . ',';
								}
								if(!empty($list))
									$list = substr($list, 0, strlen($list)-1);
							break;

							default :
								if($media == 'email'){
									$list = '<table autosize="1" class="gfield_list" style="border-top: 1px solid #DFDFDF; border-left: 1px solid #DFDFDF; border-spacing: 0; padding: 0; margin: 2px 0 6px; width: 100%"><thead><tr>';

									//reading columns from entry data
									foreach($columns as $column){
										$list .= '<th style="background-image: none; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; padding: 6px 10px; font-family: sans-serif; font-size: 12px; font-weight: bold; background-color: #F1F1F1; color:#333; text-align:left">" . esc_html($column) . "</th>';
									}
									$list .= '</tr></thead>';

									$list .= '<tbody style="background-color: #F9F9F9">';
									foreach($value as $item){
										$list .= '<tr>';
										foreach($columns as $column){
											$val = rgar($item, $column);
											$list .= '<td style="padding: 6px 10px; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; border-top: 1px solid #FFF; font-family: sans-serif; font-size:12px;">{$val}</td>';
										}

										$list .='</tr>';
									}

									$list .='</tbody></table>';
								}
								else{
									$list = '<table class="gfield_list" autosize="1"><thead><tr>';

									//reading columns from entry data
									foreach($columns as $column){
										$list .= '<th>' . esc_html($column) . '</th>';
									}
									$list .= '</tr></thead>';

									$list .= '<tbody>';
									foreach($value as $item){
										$list .= '<tr>';
										foreach($columns as $column){
											$val = rgar($item, $column);
											$list .= '<td>'.htmlspecialchars($val).'</td>';
										}

										$list .='</tr>';
									}

									$list .='</tbody></table>';
								}
							break;
						}

						return $list;
					}
					return '';
				break;

				default :
					if (!is_array($value))
					{
						return nl2br($value);
					}
				break;
			}
		}

		public static function pdf_get_lead_field_display($field, $value, $currency='', $use_text=false, $format='html', $media='screen'){

				if($field['type'] == 'post_category')
					$value = GFCommon::prepare_post_category_value($value, $field);

				switch(RGFormsModel::get_input_type($field)){
					case 'name' :
						if(is_array($value)){
							$prefix = trim(rgget($field['id'] . '.2', $value));
							$first  = trim(rgget($field['id'] . '.3', $value));
							$last   = trim(rgget($field['id'] . '.6', $value));
							$suffix = trim(rgget($field['id'] . '.8', $value));

							$name   = $prefix;
							$name   .= !empty($name) && !empty($first) ? " $first" : $first;
							$name   .= !empty($name) && !empty($last) ? " $last" : $last;
							$name   .= !empty($name) && !empty($suffix) ? " $suffix" : $suffix;

							return $name;
						}
						else{
							return $value;
						}

					break;
					case 'creditcard' :
						if(is_array($value)){
							$card_number = trim(rgget($field['id'] . '.1', $value));
							$card_type   = trim(rgget($field['id'] . '.4', $value));
							$separator   = $format == 'html' ? '<br/>' : '\n';
							return empty($card_number) ? '' : $card_type . $separator . $card_number;
						}
						else{
							return '';
						}
					break;

					case 'address' :
						if(is_array($value)){
							$street_value  = trim(rgget($field['id'] . '.1', $value));
							$street2_value = trim(rgget($field['id'] . '.2', $value));
							$city_value    = trim(rgget($field['id'] . '.3', $value));
							$state_value   = trim(rgget($field['id'] . '.4', $value));
							$zip_value     = trim(rgget($field['id'] . '.5', $value));
							$country_value = trim(rgget($field['id'] . '.6', $value));

							$line_break    = $format == 'html' ? '<br />' : '\n';

							$address_display_format = apply_filters('gform_address_display_format', 'default');
							if($address_display_format == 'zip_before_city'){
								/*
								Sample:
								3333 Some Street
								suite 16
								2344 City, State
								Country
								*/

								$addr_ary = array();
								$addr_ary[] = $street_value;

								if(!empty($street2_value))
									$addr_ary[] = $street2_value;

								$zip_line = trim($zip_value . ' ' . $city_value);
								$zip_line .= !empty($zip_line) && !empty($state_value) ? ", {$state_value}" : $state_value;
								$zip_line = trim($zip_line);
								if(!empty($zip_line))
									$addr_ary[] = $zip_line;

								if(!empty($country_value))
									$addr_ary[] = $country_value;

								$address = implode('<br />', $addr_ary);

							}
							else{
								$address = $street_value;
								$address .= !empty($address) && !empty($street2_value) ? $line_break . $street2_value : $street2_value;
								$address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? $line_break. $city_value : $city_value;
								$address .= !empty($address) && !empty($city_value) && !empty($state_value) ? ", $state_value" : $state_value;
								$address .= !empty($address) && !empty($zip_value) ? " $zip_value" : $zip_value;
								$address .= !empty($address) && !empty($country_value) ? $line_break . $country_value : $country_value;
							}

							return $address;
						}
						else{
							return '';
						}
					break;

					case 'email' :
						return GFCommon::is_valid_email($value) && $format == 'html' ? '<a href="mailto:'. $value .'">'. $value .'</a>' : $value;
					break;

					case 'website' :
						return GFCommon::is_valid_url($value) && $format == 'html' ? '<a href="'. $value .'" target="_blank">'. $value .'</a>' : $value;
					break;

					case 'checkbox' :
						if(is_array($value)){

							$items = '';

							foreach($value as $key => $item){
								if(!empty($item)){
									switch($format){
										case 'text' :
											$items .= GFCommon::selection_display($item, $field, $currency, true) . ', ';
										break;

										default:
											$items .= '<li>' . GFCommon::selection_display($item, $field, $currency, true) . '</li>';
										break;
									}
								}
							}
							if(empty($items)){
								return '';
							}
							else if($format == 'text'){
								return substr($items, 0, strlen($items)-2); //removing last comma
							}
							else{
								return '<ul class="bulleted">' . $items . '</ul>';
							}
						}
						else{
							return $value;
						}
					break;

					case 'post_image' :
						$ary         = explode('|:|', $value);
						$url         = count($ary) > 0 ? $ary[0] : '';
						$title       = count($ary) > 1 ? $ary[1] : '';
						$caption     = count($ary) > 2 ? $ary[2] : '';
						$description = count($ary) > 3 ? $ary[3] : '';

						if(!empty($url)){
							$url = str_replace(' ', '%20', $url);
							switch($format){
								case 'text' :
									$value = $url;
									$value .= !empty($title) ? '\n\n' . $field['label'] . ' (' . __('Title', 'gravityforms') . '): ' . $title : '';
									$value .= !empty($caption) ? '\n\n' . $field['label'] . ' (' . __('Caption', 'gravityforms') . '): ' . $caption : '';
									$value .= !empty($description) ? '\n\n' . $field['label'] . ' (' . __('Description', 'gravityforms') . '): ' . $description : '';
								break;

								default :
									$path  = str_replace(site_url().'/', ABSPATH, $url);
									$value = "<a href='$url' target='_blank' title='" . __("Click to view", "gravityforms") . "'><img src='$path' width='100' /></a>";
									$value .= !empty($title) ? "<div>Title: $title</div>" : "";
									$value .= !empty($caption) ? "<div>Caption: $caption</div>" : "";
									$value .= !empty($description) ? "<div>Description: $description</div>": "";

								break;
							}
						}
						return $value;

					case 'fileupload' :
						$output = '';
						$output_arr = array();
						if(!empty($value)){
							$output .=  '<ul>';
							$file_paths = rgar($field,'multipleFiles') ? json_decode($value) : array($value);
							foreach($file_paths as $file_path){
								$info = pathinfo($file_path);
								$file_path = esc_attr(str_replace(' ', '%20', $file_path));
								$output_arr[] = '<li><a href="'. $file_path .'" target="_blank" title="' . __('Click to view', 'gravityforms') . '">' . $info['basename'] . '</a></li>';
							}
							$output .= join(PHP_EOL, $output_arr);
							$output .=  '</ul>';
						  }

						return $output;
					break;

					case 'date' :
						return GFCommon::date_display($value, rgar($field, 'dateFormat'));
					break;

					case 'radio' :
					case 'select' :
						return GFCommon::selection_display($value, $field, $currency, true);
					break;

					case 'multiselect' :
						if(empty($value) || $format == 'text')
							return $value;

						$value = explode(',', $value);

						$items = '';
						foreach($value as $item){
							$items .= '<li>' . GFCommon::selection_display($item, $field, $currency, true) . '</li>';
						}

						return '<ul class="bulleted">' . $items . '</ul>';

					break;

					case 'calculation' :
					case 'singleproduct' :
						if(is_array($value)){
							$product_name = trim($value[$field['id'] . '.1']);
							$price = trim($value[$field['id'] . '.2']);
							$quantity = trim($value[$field['id'] . '.3']);

							$product = $product_name . ', ' . __('Qty: ', 'gravityforms') . $quantity . ', ' . __('Price: ', 'gravityforms') . $price;
							return $product;
						}
						else{
							return '';
						}
					break;

					case 'number' :
						return self::format_number($value, rgar($field, 'numberFormat'));
					break;

					case 'singleshipping' :
					case 'donation' :
					case 'total' :
					case 'price' :
						return GFCommon::to_money($value, $currency);

					case 'list' :
						if(empty($value))
							return '';
						$value = unserialize($value);

						$has_columns = is_array($value[0]);

						if(!$has_columns){
							$items = '';
							foreach($value as $key => $item){
								if(!empty($item)){
									switch($format){
										case 'text' :
											$items .= $item . ', ';
										break;
										case 'url' :
											$items .= $item . ',';
										break;
										default :
											if($media == 'email'){
												$items .= '<li>'.htmlspecialchars($item).'</li>';
											}
											else{
												$items .= '<li>'.htmlspecialchars($item).'</li>';
											}
										break;
									}
								}
							}

							if(empty($items)){
								return '';
							}
							else if($format == 'text'){
								return substr($items, 0, strlen($items)-2); //removing last comma
							}
							else if($format == 'url'){
								return substr($items, 0, strlen($items)-1); //removing last comma
							}
							else{
								return '<ul class="bulleted">' . $items . '</ul>';
							}
						}
						else if(is_array($value)){
							$columns = array_keys($value[0]);

							$list = '';

							switch($format){
								case 'text' :
									$is_first_row = true;
									foreach($value as $item){
										if(!$is_first_row)
											$list .= '\n\n' . $field['label'] . ': ';
										$list .= implode(',', array_values($item));

										$is_first_row = false;
									}
								break;

								case 'url' :
									foreach($value as $item){
										$list .= implode('|', array_values($item)) . ',';
									}
									if(!empty($list))
										$list = substr($list, 0, strlen($list)-1);
								break;

								default :
									if($media == 'email'){
										$list = '<table autosize="1" class="gfield_list" style="border-top: 1px solid #DFDFDF; border-left: 1px solid #DFDFDF; border-spacing: 0; padding: 0; margin: 2px 0 6px; width: 100%"><thead><tr>';

										//reading columns from entry data
										foreach($columns as $column){
											$list .= '<th style="background-image: none; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; padding: 6px 10px; font-family: sans-serif; font-size: 12px; font-weight: bold; background-color: #F1F1F1; color:#333; text-align:left">' . esc_html($column) . '</th>';
										}
										$list .= '</tr></thead>';

										$list .= '<tbody style="background-color: #F9F9F9">';
										foreach($value as $item){
											$list .= '<tr>';
											foreach($columns as $column){
												$val = rgar($item, $column);
												$list .= '<td style="padding: 6px 10px; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; border-top: 1px solid #FFF; font-family: sans-serif; font-size:12px;">{$val}</td>';
											}

											$list .='</tr>';
										}

										$list .='</tbody></table>';
									}
									else{
										$list = '<table autosize="1" class="gfield_list"><thead><tr>';

										//reading columns from entry data
										foreach($columns as $column){
											$list .= '<th>' . esc_html($column) . '</th>';
										}
										$list .= '</tr></thead>';

										$list .= '<tbody>';
										foreach($value as $item){
											$list .= '<tr>';
											foreach($columns as $column){
												$val = rgar($item, $column);
												$list .= '<td>'.htmlspecialchars($val).'</td>';
											}

											$list .='</tr>';
										}

										$list .='</tbody></table>';
									}
								break;
							}

							return $list;
						}
						return '';
					break;

					default :
						if (!is_array($value))
						{
							return nl2br($value);
						}
					break;
				}
			}

		/**
		 * Format Gravity Form's three number types
		 * @param  float $number        The number field value to process
		 * @param  string $number_format The number type a user selects in the form editor
		 * @return float                Correctly formated number
		 */
	    private static function format_number($number, $number_format){
	        if(!is_numeric($number))
	            return $number;

	        //replacing commas with dots and dots with commas
	        switch($number_format)
	        {
	        	case 'decimal_comma':
	        		$number = self::reformat_number(number_format($number, '2', ',', '.'));
	        	break;

	        	case 'decimal_dot':
	        		$number = self::reformat_number(number_format($number, '2'));

	        	break;

	        	case 'currency':
	        		/* get the GF currency and convert number to money */
	        		$number = GFCommon::to_money($number);
	        	break;
	        }

	        return $number;
	    }

	    /**
	     * Remove the .00 or ,00 decimal from the end of the number format
	     * @param  float $number number to manipulate
	     * @return float         processed number
	     */
	    private static function reformat_number($number)
	    {
	    	if(substr($number, -2) == '00')
	    	{
	    		return substr($number, 0, -3);
	    	}
	    	return $number;
	    }

	    public static function encode_tags($content, $field)
	    {
	    	if(RGFormsModel::get_input_type($field) != 'html')
	    	{
	    		$content = str_replace('[', '&#91;', $content);
	    		$content = str_replace(']', '&#93;', $content);
	    		$content = str_replace('{', '&#123;', $content);
	    		$content = str_replace('}', '&#125;', $content);
	    	}

	    	return $content;
	    }
	}
}